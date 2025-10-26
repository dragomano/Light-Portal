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
use LightPortal\Validators\CategoryValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->sql        = mock(PortalSqlInterface::class);
    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->request    = mock(RequestInterface::class);
    $this->post       = mock(PostInterface::class);

    Utils::$context = [];

    Lang::$txt = [
        'lp_post_error_no_title'       => 'Title is required',
        'lp_post_error_no_slug'        => 'Slug is required',
        'lp_post_error_no_valid_slug'  => 'Slug is not valid',
        'lp_post_error_no_unique_slug' => 'Slug must be unique',
    ];

    $this->validator = new class(
        $this->sql,
        $this->dispatcher
    ) extends CategoryValidator {
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

dataset('category filter fields', [
    'category_id' => ['field' => 'category_id', 'filter' => FILTER_VALIDATE_INT],
    'icon'        => ['field' => 'icon', 'filter' => FILTER_DEFAULT],
    'description' => ['field' => 'description', 'filter' => FILTER_UNSAFE_RAW],
]);

dataset('slug filter validation', [
    'valid slug lowercase'           => ['slug' => 'valid-category-123', 'isValid' => true],
    'valid slug with numbers'        => ['slug' => 'category123', 'isValid' => true],
    'valid slug with hyphens'        => ['slug' => 'my-awesome-category', 'isValid' => true],
    'invalid slug uppercase'         => ['slug' => 'Invalid-Category', 'isValid' => false],
    'invalid slug spaces'            => ['slug' => 'invalid category', 'isValid' => false],
    'invalid slug special chars'     => ['slug' => 'invalid_category', 'isValid' => false],
    'invalid slug starts with digit' => ['slug' => '123-category', 'isValid' => false],
]);

dataset('uniqueness scenarios', [
    'unique slug for new category'      => ['slug' => 'new-category', 'categoryId' => 0, 'dbCount' => 0, 'shouldBeUnique' => true],
    'unique slug for existing category' => ['slug' => 'existing-category', 'categoryId' => 5, 'dbCount' => 0, 'shouldBeUnique' => true],
    'non-unique slug'                   => ['slug' => 'duplicate-category', 'categoryId' => 5, 'dbCount' => 1, 'shouldBeUnique' => false],
]);

describe('CategoryValidator::__construct', function () {
    it('initializes with default category filters', function ($field, $filter) {
        $filters = $this->accessor->getProtectedProperty('filters');

        expect($filters)->toHaveKey($field);

        if (is_array($filter)) {
            expect($filters[$field])->toBeArray();
        } else {
            expect($filters[$field])->toBe($filter);
        }
    })->with('category filter fields');

    it('has slug filter with alias pattern regexp', function () {
        $filters = $this->accessor->getProtectedProperty('filters');

        expect($filters)->toHaveKey('slug')
            ->and($filters['slug'])->toBeArray()
            ->and($filters['slug'])->toHaveKey('filter')
            ->and($filters['slug'])->toHaveKey('options')
            ->and($filters['slug']['filter'])->toBe(FILTER_VALIDATE_REGEXP)
            ->and($filters['slug']['options'])->toHaveKey('regexp')
            ->and($filters['slug']['options']['regexp'])->toContain(LP_ALIAS_PATTERN);
    });

    it('validates slug pattern correctly', function ($slug, $isValid) {
        $filters = $this->accessor->getProtectedProperty('filters');
        $pattern = $filters['slug']['options']['regexp'];

        $result = filter_var($slug, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $pattern]]);

        if ($isValid) {
            expect($result)->toBe($slug);
        } else {
            expect($result)->toBeFalse();
        }
    })->with('slug filter validation');
});

describe('CategoryValidator::extendErrors', function () {
    it('calls checkSlug method', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-category');

        $this->validator->setMockPost($post);
        $this->accessor->setProtectedProperty('filteredData', ['slug' => 'test-category', 'category_id' => 1]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_categories')
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

        $this->accessor->callProtectedMethod('extendErrors');

        $errors = $this->accessor->getProtectedProperty('errors');

        expect($errors)->toBeArray();
    });
});

describe('CategoryValidator::isUnique', function () {
    it('checks uniqueness correctly', function ($slug, $categoryId, $dbCount, $shouldBeUnique) {
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'        => $slug,
            'category_id' => $categoryId,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->once()
            ->with('lp_categories')
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
                'slug = ?'         => $slug,
                'category_id != ?' => $categoryId,
            ])
            ->andReturn($select);

        $this->sql->shouldReceive('execute')
            ->once()
            ->with($select)
            ->andReturn($result);

        $result->shouldReceive('current')
            ->once()
            ->andReturn(['count' => $dbCount]);

        $isUnique = $this->accessor->callProtectedMethod('isUnique');

        expect($isUnique)->toBe($shouldBeUnique);
    })->with('uniqueness scenarios');

    it('uses Expression for COUNT query', function () {
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'        => 'test-category',
            'category_id' => 1,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $capturedExpression = null;

        $this->sql->shouldReceive('select')
            ->with('lp_categories')
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

        $this->accessor->callProtectedMethod('isUnique');

        expect($capturedExpression)->toBeInstanceOf(Expression::class)
            ->and($capturedExpression->getExpression())->toBe('COUNT(category_id)');
    });
});

describe('CategoryValidator::integration', function () {
    it('performs full validation with valid unique category', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'       => 'Test Category',
                'category_id' => '1',
                'slug'        => 'test-category',
                'icon'        => 'fas fa-folder',
                'description' => 'Test description',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-category');

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_categories')
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
            ->and($validationResult)->toHaveKey('category_id')
            ->and($validationResult)->toHaveKey('slug')
            ->and($validationResult)->toHaveKey('icon')
            ->and($validationResult)->toHaveKey('description')
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
                'title'       => 'Test Category',
                'category_id' => '1',
                'slug'        => 'duplicate-category',
                'icon'        => 'fas fa-folder',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('duplicate-category');

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_categories')
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
                'title'       => 'Test Category',
                'category_id' => '1',
                'slug'        => 'Invalid-Category',
                'icon'        => 'fas fa-folder',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('Invalid-Category');

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_categories')
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
