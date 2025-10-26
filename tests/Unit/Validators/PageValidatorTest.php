<?php

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use Laminas\Db\Sql\Expression;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Validators\PageValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->sql        = mock(PortalSqlInterface::class);
    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->request    = mock(RequestInterface::class);
    $this->post       = mock(PostInterface::class);

    User::$me           = new User(1);
    User::$me->name     = 'TestUser';
    User::$me->groups   = [0];
    User::$me->is_guest = false;
    User::$me->is_admin = false;
    User::$me->language = 'english';
    Config::$language   = 'english';

    Utils::$context = [
        'lp_current_page' => [
            'type' => 'html',
        ],
    ];

    Lang::$txt = [
        'lp_post_error_no_title'       => 'Title is required',
        'lp_post_error_no_content'     => 'Content is required',
        'lp_post_error_no_slug'        => 'Slug is required',
        'lp_post_error_no_valid_slug'  => 'Slug is not valid',
        'lp_post_error_no_unique_slug' => 'Slug must be unique',
    ];

    $this->validator = new class(
        $this->sql,
        $this->dispatcher
    ) extends PageValidator {
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

dataset('page filter fields', [
    'page_id'     => ['field' => 'page_id', 'filter' => FILTER_VALIDATE_INT],
    'category_id' => ['field' => 'category_id', 'filter' => FILTER_VALIDATE_INT],
    'author_id'   => ['field' => 'author_id', 'filter' => FILTER_VALIDATE_INT],
    'description' => ['field' => 'description', 'filter' => FILTER_UNSAFE_RAW],
    'content'     => ['field' => 'content', 'filter' => FILTER_UNSAFE_RAW],
    'type'        => ['field' => 'type', 'filter' => FILTER_DEFAULT],
    'entry_type'  => ['field' => 'entry_type', 'filter' => FILTER_DEFAULT],
    'permissions' => ['field' => 'permissions', 'filter' => FILTER_VALIDATE_INT],
    'status'      => ['field' => 'status', 'filter' => FILTER_VALIDATE_INT],
    'date'        => ['field' => 'date', 'filter' => FILTER_DEFAULT],
    'time'        => ['field' => 'time', 'filter' => FILTER_DEFAULT],
    'tags'        => ['field' => 'tags', 'filter' => FILTER_DEFAULT],
]);

dataset('custom filter fields', [
    'page_icon'            => ['field' => 'page_icon', 'filter' => FILTER_DEFAULT],
    'show_in_menu'         => ['field' => 'show_in_menu', 'filter' => FILTER_VALIDATE_BOOLEAN],
    'show_title'           => ['field' => 'show_title', 'filter' => FILTER_VALIDATE_BOOLEAN],
    'show_author_and_date' => ['field' => 'show_author_and_date', 'filter' => FILTER_VALIDATE_BOOLEAN],
    'show_related_pages'   => ['field' => 'show_related_pages', 'filter' => FILTER_VALIDATE_BOOLEAN],
    'allow_comments'       => ['field' => 'allow_comments', 'filter' => FILTER_VALIDATE_BOOLEAN],
]);

dataset('slug filter validation', [
    'valid slug lowercase'           => ['slug' => 'valid-page-123', 'isValid' => true],
    'valid slug with numbers'        => ['slug' => 'page123', 'isValid' => true],
    'valid slug with hyphens'        => ['slug' => 'my-awesome-page', 'isValid' => true],
    'invalid slug uppercase'         => ['slug' => 'Invalid-Page', 'isValid' => false],
    'invalid slug spaces'            => ['slug' => 'invalid page', 'isValid' => false],
    'invalid slug special chars'     => ['slug' => 'invalid_page', 'isValid' => false],
]);

dataset('uniqueness scenarios', [
    'unique slug for new page'      => ['slug' => 'new-page', 'pageId' => 0, 'dbCount' => 0, 'shouldBeUnique' => true],
    'unique slug for existing page' => ['slug' => 'existing-page', 'pageId' => 5, 'dbCount' => 0, 'shouldBeUnique' => true],
    'non-unique slug'               => ['slug' => 'duplicate-page', 'pageId' => 5, 'dbCount' => 1, 'shouldBeUnique' => false],
]);

describe('PageValidator::__construct', function () {
    it('initializes with default page filters', function ($field, $filter) {
        $filters = $this->accessor->getProtectedProperty('filters');

        expect($filters)->toHaveKey($field);

        if (is_array($filter)) {
            expect($filters[$field])->toBeArray();
        } else {
            expect($filters[$field])->toBe($filter);
        }
    })->with('page filter fields');

    it('initializes with custom filters', function ($field, $filter) {
        $customFilters = $this->accessor->getProtectedProperty('customFilters');

        expect($customFilters)->toHaveKey($field)
            ->and($customFilters[$field])->toBe($filter);
    })->with('custom filter fields');

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

describe('PageValidator::extendFilters', function () {
    it('dispatches validatePageParams event', function () {
        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(
                PortalHook::validatePageParams,
                Mockery::on(function ($args) {
                    return isset($args['params'])
                        && isset($args['type'])
                        && $args['type'] === 'html';
                })
            );

        $this->accessor->callProtectedMethod('extendFilters');
    });

    it('preserves base custom filters', function () {
        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::validatePageParams, Mockery::any());

        $originalFilters = $this->accessor->getProtectedProperty('customFilters');

        $this->accessor->callProtectedMethod('extendFilters');

        $customFilters = $this->accessor->getProtectedProperty('customFilters');

        expect($customFilters['page_icon'])->toBe($originalFilters['page_icon'])
            ->and($customFilters['show_in_menu'])->toBe($originalFilters['show_in_menu']);
    });
});

describe('PageValidator::modifyData', function () {
    it('filters custom options from post data', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'page_icon'            => 'fas fa-page',
                'show_in_menu'         => '1',
                'show_title'           => '1',
                'show_author_and_date' => '1',
                'show_related_pages'   => '1',
                'allow_comments'       => '1',
            ]);

        $this->accessor->callProtectedMethod('modifyData');

        $filteredData = $this->accessor->getProtectedProperty('filteredData');

        expect($filteredData)->toHaveKey('options')
            ->and($filteredData['options'])->toHaveKey('page_icon')
            ->and($filteredData['options'])->toHaveKey('show_in_menu');
    });

    it('validates boolean fields correctly', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'show_in_menu'       => 'true',
                'show_title'         => 'false',
                'allow_comments'     => '1',
                'show_related_pages' => '0',
            ]);

        $this->accessor->callProtectedMethod('modifyData');

        $filteredData = $this->accessor->getProtectedProperty('filteredData');

        expect($filteredData['options']['show_in_menu'])->toBeTrue()
            ->and($filteredData['options']['show_title'])->toBeFalse()
            ->and($filteredData['options']['allow_comments'])->toBeTrue()
            ->and($filteredData['options']['show_related_pages'])->toBeFalse();
    });
});

describe('PageValidator::extendErrors', function () {
    it('adds error for empty content in default language', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-page');

        $this->validator->setMockPost($post);
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'    => 'test-page',
            'page_id' => 1,
            'content' => '',
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::findPageErrors, Mockery::any());

        $this->accessor->callProtectedMethod('extendErrors');

        $errors = $this->accessor->getProtectedProperty('errors');

        expect($errors)->toContain('no_content');
    });

    it('does not add error for empty content in non-default language', function () {
        User::$me->language = 'russian';

        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-page');

        $this->validator->setMockPost($post);
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'    => 'test-page',
            'page_id' => 1,
            'content' => '',
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::findPageErrors, Mockery::any());

        $this->accessor->callProtectedMethod('extendErrors');

        $errors = $this->accessor->getProtectedProperty('errors');

        expect($errors)->not->toContain('no_content');

        User::$me->language = 'english';
    });

    it('calls checkSlug method', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-page');

        $this->validator->setMockPost($post);
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'    => 'test-page',
            'page_id' => 1,
            'content' => 'Test content',
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::findPageErrors, Mockery::any());

        $this->accessor->callProtectedMethod('extendErrors');

        $errors = $this->accessor->getProtectedProperty('errors');

        expect($errors)->toBeArray();
    });
});

describe('PageValidator::isUnique', function () {
    it('checks uniqueness correctly', function ($slug, $pageId, $dbCount, $shouldBeUnique) {
        $this->accessor->setProtectedProperty('filteredData', [
            'slug'    => $slug,
            'page_id' => $pageId,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->once()
            ->with('lp_pages')
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
                'slug = ?'     => $slug,
                'page_id != ?' => $pageId,
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
            'slug'    => 'test-page',
            'page_id' => 1,
        ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $capturedExpression = null;

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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
            ->and($capturedExpression->getExpression())->toBe('COUNT(page_id)');
    });
});

describe('PageValidator::integration', function () {
    it('performs full validation with valid unique page', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'       => 'Test Page',
                'page_id'     => '1',
                'category_id' => '1',
                'author_id'   => '42',
                'slug'        => 'test-page',
                'description' => 'Test description',
                'content'     => 'Test content',
                'type'        => 'html',
                'entry_type'  => 'default',
                'permissions' => '3',
                'status'      => '1',
                'date'        => '2025-10-25',
                'time'        => '12:00',
                'tags'        => '1,2,3',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-page');

        $this->post->shouldReceive('only')
            ->with(Mockery::type('array'))
            ->andReturn([
                'page_icon'            => 'fas fa-page',
                'show_in_menu'         => '1',
                'show_title'           => '1',
                'show_author_and_date' => '1',
                'show_related_pages'   => '1',
                'allow_comments'       => '1',
            ]);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validatePageParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findPageErrors, Mockery::any());

        $validationResult = $this->validator->validate();

        expect($validationResult)->toHaveKey('title')
            ->and($validationResult)->toHaveKey('page_id')
            ->and($validationResult)->toHaveKey('slug')
            ->and($validationResult)->toHaveKey('content')
            ->and($validationResult)->toHaveKey('options')
            ->and(Utils::$context)->not->toHaveKey('post_errors');
    });

    it('performs full validation with empty content in default language', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'   => 'Test Page',
                'page_id' => '1',
                'slug'    => 'test-page',
                'content' => '',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('test-page');

        $this->post->shouldReceive('only')
            ->with(Mockery::type('array'))
            ->andReturn([]);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validatePageParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findPageErrors, Mockery::any());

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Content is required');
    });

    it('performs full validation with non-unique slug', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'   => 'Test Page',
                'page_id' => '1',
                'slug'    => 'duplicate-page',
                'content' => 'Test content',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('duplicate-page');

        $this->post->shouldReceive('only')
            ->with(Mockery::type('array'))
            ->andReturn([]);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validatePageParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findPageErrors, Mockery::any());

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
                'title'   => 'Test Page',
                'page_id' => '1',
                'slug'    => 'Invalid-Page',
                'content' => 'Test content',
            ]);

        $this->post->shouldReceive('get')
            ->with('slug')
            ->andReturn('Invalid-Page');

        $this->post->shouldReceive('only')
            ->with(Mockery::type('array'))
            ->andReturn([]);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $select = mock(PortalSelect::class);
        $result = mock(PortalResultInterface::class);

        $this->sql->shouldReceive('select')
            ->with('lp_pages')
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

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validatePageParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findPageErrors, Mockery::any());

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Slug is not valid');
    });
});
