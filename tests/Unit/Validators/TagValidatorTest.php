<?php

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use Laminas\Db\Sql\Expression;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Validators\TagValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->sql        = mock(PortalSqlInterface::class);
    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->request    = mock(RequestInterface::class);
    $this->post       = mock(PostInterface::class);

    Utils::$context = [];

    Lang::$txt['lp_post_error_no_title'] = 'Title is required';
    Lang::$txt['lp_post_error_no_slug'] = 'Slug is required';
    Lang::$txt['lp_post_error_no_valid_slug'] = 'Slug is not valid';
    Lang::$txt['lp_post_error_no_unique_slug'] = 'Slug must be unique';

    $this->validator = new class(
        $this->sql,
        $this->dispatcher
    ) extends TagValidator {
        private ?RequestInterface $mockRequest = null;

        private ?PostInterface $mockPost = null;

        public function setMockRequest(RequestInterface $request): void
        {
            $this->mockRequest = $request;
        }

        public function setMockPost(PostInterface $post): void
        {
            $this->mockPost = $post;
        }

        public function request(): RequestInterface
        {
            return $this->mockRequest ?? parent::request();
        }

        public function post(): PostInterface
        {
            return $this->mockPost ?? parent::post();
        }
    };

    $this->validator->setMockRequest($this->request);
    $this->validator->setMockPost($this->post);

    $this->accessor = new ReflectionAccessor($this->validator);
});

dataset('tag filter fields', [
    'tag_id' => ['field' => 'tag_id', 'filter' => FILTER_VALIDATE_INT],
    'icon'   => ['field' => 'icon', 'filter' => FILTER_DEFAULT],
]);

dataset('slug filter validation', [
    'valid slug lowercase'           => ['slug' => 'valid-tag-123', 'isValid' => true],
    'valid slug with numbers'        => ['slug' => 'tag123', 'isValid' => true],
    'valid slug with hyphens'        => ['slug' => 'my-awesome-tag', 'isValid' => true],
    'invalid slug uppercase'         => ['slug' => 'Invalid-Tag', 'isValid' => false],
    'invalid slug spaces'            => ['slug' => 'invalid tag', 'isValid' => false],
    'invalid slug special chars'     => ['slug' => 'invalid_tag', 'isValid' => false],
    'invalid slug starts with digit' => ['slug' => '123-tag', 'isValid' => false],
]);

dataset('uniqueness scenarios', [
    'unique slug for new tag'      => ['slug' => 'new-tag', 'tagId' => 0, 'dbCount' => 0, 'shouldBeUnique' => true],
    'unique slug for existing tag' => ['slug' => 'existing-tag', 'tagId' => 5, 'dbCount' => 0, 'shouldBeUnique' => true],
    'non-unique slug'              => ['slug' => 'duplicate-tag', 'tagId' => 5, 'dbCount' => 1, 'shouldBeUnique' => false],
]);

describe('TagValidator::__construct', function () {
    it('initializes with default tag filters', function ($field, $filter) {
        $filters = $this->accessor->getProperty('filters');

        expect($filters)->toHaveKey($field);

        if (is_array($filter)) {
            expect($filters[$field])->toBeArray();
        } else {
            expect($filters[$field])->toBe($filter);
        }
    })->with('tag filter fields');

    it('has slug filter with alias pattern regexp', function () {
        $filters = $this->accessor->getProperty('filters');

        expect($filters)->toHaveKey('slug')
            ->and($filters['slug'])->toBeArray()
            ->and($filters['slug'])->toHaveKey('filter')
            ->and($filters['slug'])->toHaveKey('options')
            ->and($filters['slug']['filter'])->toBe(FILTER_VALIDATE_REGEXP)
            ->and($filters['slug']['options'])->toHaveKey('regexp')
            ->and($filters['slug']['options']['regexp'])->toContain(LP_ALIAS_PATTERN);
    });

    it('validates slug pattern correctly', function ($slug, $isValid) {
        $filters = $this->accessor->getProperty('filters');
        $pattern = $filters['slug']['options']['regexp'];

        $result = filter_var($slug, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $pattern]]);

        if ($isValid) {
            expect($result)->toBe($slug);
        } else {
            expect($result)->toBeFalse();
        }
    })->with('slug filter validation');
});

describe('TagValidator::extendErrors', function () {
    it('calls checkSlug method', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-tag');

        $this->validator->setMockPost($post);
        $this->accessor->setProperty('filteredData', ['slug' => 'test-tag', 'tag_id' => 1]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->andReturn($select);

        $select->shouldReceive('where')
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->andReturn(['count' => 0]);

        $this->accessor->callMethod('extendErrors');

        $errors = $this->accessor->getProperty('errors');

        expect($errors)->toBeArray();
    });
});

describe('TagValidator::isUnique', function () {
    it('checks uniqueness correctly', function ($slug, $tagId, $dbCount, $shouldBeUnique) {
        $this->accessor->setProperty('filteredData', [
            'slug'   => $slug,
            'tag_id' => $tagId,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->once()
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->once()
            ->with(Mockery::on(function ($cols) {
                return isset($cols['count']) && $cols['count'] instanceof Expression;
            }))
            ->andReturn($select);

        $select->shouldReceive('where')
            ->once()
            ->with([
                'slug = ?'    => $slug,
                'tag_id != ?' => $tagId,
            ])
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->once()
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->once()
            ->andReturn(['count' => $dbCount]);

        $isUnique = $this->accessor->callMethod('isUnique');

        expect($isUnique)->toBe($shouldBeUnique);
    })->with('uniqueness scenarios');

    it('uses Expression for COUNT query', function () {
        $this->accessor->setProperty('filteredData', [
            'slug'   => 'test-tag',
            'tag_id' => 1,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $capturedExpression = null;

        $this->sql->shouldReceive('select')
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->andReturnUsing(function ($cols) use (&$capturedExpression, $select) {
                if (isset($cols['count'])) {
                    $capturedExpression = $cols['count'];
                }
                return $select;
            });

        $select->shouldReceive('where')
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->andReturn(['count' => 0]);

        $this->accessor->callMethod('isUnique');

        expect($capturedExpression)->toBeInstanceOf(Expression::class)
            ->and($capturedExpression->getExpression())->toBe('COUNT(tag_id)');
    });
});

describe('TagValidator::integration', function () {
    it('performs full validation with valid unique tag', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'  => 'Test Tag',
                'tag_id' => '1',
                'slug'   => 'test-tag',
                'icon'   => 'fas fa-tag',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-tag');

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->andReturn($select);

        $select->shouldReceive('where')
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->andReturn(['count' => 0]);

        $validationResult = $this->validator->validate();

        expect($validationResult)->toHaveKey('title')
            ->and($validationResult)->toHaveKey('tag_id')
            ->and($validationResult)->toHaveKey('slug')
            ->and($validationResult)->toHaveKey('icon')
            ->and(Utils::$context)->not->toHaveKey('post_errors');
    });

    it('performs full validation with non-unique slug', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'  => 'Test Tag',
                'tag_id' => '1',
                'slug'   => 'duplicate-tag',
                'icon'   => 'fas fa-tag',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('duplicate-tag');

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->andReturn($select);

        $select->shouldReceive('where')
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->andReturn(['count' => 1]);

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Slug must be unique');
    });

    it('performs full validation with invalid slug pattern', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'  => 'Test Tag',
                'tag_id' => '1',
                'slug'   => 'Invalid-Tag',
                'icon'   => 'fas fa-tag',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('Invalid-Tag');

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_tags')
            ->andReturn($select);

        $select->shouldReceive('columns')
            ->andReturn($select);

        $select->shouldReceive('where')
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->andReturn(['count' => 0]);

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Slug is not valid');
    });
});
