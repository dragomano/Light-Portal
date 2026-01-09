<?php

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Enums\PortalHook;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Validators\BlockValidator;
use Tests\ReflectionAccessor;

beforeEach(function () {
    $this->sql        = mock(PortalSqlInterface::class);
    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->request    = mock(RequestInterface::class);
    $this->post       = mock(PostInterface::class);

    Utils::$context = [];
    Utils::$context['lp_current_block'] = ['type' => 'test_block'];

    Lang::$txt['lp_post_error_no_title'] = 'Title is required';
    Lang::$txt['lp_post_error_no_areas'] = 'Areas are required';
    Lang::$txt['lp_post_error_no_valid_areas'] = 'Areas are not valid';

    $this->validator = new class(
        $this->sql,
        $this->dispatcher
    ) extends BlockValidator {
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

dataset('block filter fields', [
    'block_id'      => ['field' => 'block_id', 'filter' => FILTER_VALIDATE_INT],
    'icon'          => ['field' => 'icon', 'filter' => FILTER_DEFAULT],
    'type'          => ['field' => 'type', 'filter' => FILTER_DEFAULT],
    'description'   => ['field' => 'description', 'filter' => FILTER_UNSAFE_RAW],
    'content'       => ['field' => 'content', 'filter' => FILTER_UNSAFE_RAW],
    'placement'     => ['field' => 'placement', 'filter' => FILTER_DEFAULT],
    'priority'      => ['field' => 'priority', 'filter' => FILTER_VALIDATE_INT],
    'permissions'   => ['field' => 'permissions', 'filter' => FILTER_VALIDATE_INT],
    'title_class'   => ['field' => 'title_class', 'filter' => FILTER_DEFAULT],
    'content_class' => ['field' => 'content_class', 'filter' => FILTER_DEFAULT],
]);

dataset('custom filter fields', [
    'hide_header'   => ['field' => 'hide_header', 'filter' => FILTER_VALIDATE_BOOLEAN],
    'link_in_title' => ['field' => 'link_in_title', 'filter' => FILTER_VALIDATE_URL],
]);

dataset('areas validation cases', [
    'empty areas' => [
        'areasValue'     => '',
        'validatedAreas' => null,
        'expectedErrors' => ['no_areas'],
    ],
    'null areas' => [
        'areasValue'     => null,
        'validatedAreas' => null,
        'expectedErrors' => ['no_areas'],
    ],
    'invalid areas format' => [
        'areasValue'     => 'invalid-format',
        'validatedAreas' => false,
        'expectedErrors' => ['no_valid_areas'],
    ],
    'valid areas' => [
        'areasValue'     => 'all',
        'validatedAreas' => 'all',
        'expectedErrors' => [],
    ],
]);

describe('BlockValidator::__construct', function () {
    it('initializes with default block filters', function ($field, $filter) {
        $filters = $this->accessor->getProperty('filters');

        expect($filters)->toHaveKey($field);

        if (is_array($filter)) {
            expect($filters[$field])->toBeArray();
        } else {
            expect($filters[$field])->toBe($filter);
        }
    })->with('block filter fields');

    it('initializes with custom filters', function ($field, $filter) {
        $customFilters = $this->accessor->getProperty('customFilters');

        expect($customFilters)->toHaveKey($field)
            ->and($customFilters[$field])->toBe($filter);
    })->with('custom filter fields');

    it('has areas filter with callback', function () {
        $filters = $this->accessor->getProperty('filters');

        expect($filters)->toHaveKey('areas')
            ->and($filters['areas'])->toBeArray()
            ->and($filters['areas'])->toHaveKey('filter')
            ->and($filters['areas'])->toHaveKey('options')
            ->and($filters['areas']['filter'])->toBe(FILTER_CALLBACK)
            ->and($filters['areas']['options'])->toBeCallable();
    });

    it('areas filter validates correctly', function () {
        $filters      = $this->accessor->getProperty('filters');
        $areasFilter  = $filters['areas']['options'];

        expect($areasFilter('all'))->toBe('all')
            ->and($areasFilter('header'))->toBe('header')
            ->and($areasFilter(''))->toBe('')
            ->and($areasFilter(null))->toBe('')
            ->and($areasFilter('invalid format'))->toBe('');
    });
});

describe('BlockValidator::extendFilters', function () {
    it('dispatches validateBlockParams event', function () {
        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(
                PortalHook::validateBlockParams,
                Mockery::on(function ($args) {
                    return isset($args['baseParams'])
                        && isset($args['params'])
                        && isset($args['type'])
                        && $args['type'] === 'test_block';
                })
            );

        $this->accessor->callMethod('extendFilters');
    });

    it('preserves base custom filters', function () {
        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::validateBlockParams, Mockery::any());

        $originalFilters = $this->accessor->getProperty('customFilters');

        $this->accessor->callMethod('extendFilters');

        $customFilters = $this->accessor->getProperty('customFilters');

        expect($customFilters['hide_header'])->toBe($originalFilters['hide_header'])
            ->and($customFilters['link_in_title'])->toBe($originalFilters['link_in_title']);
    });
});

describe('BlockValidator::modifyData', function () {
    it('filters custom options from post data', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([
                'hide_header'   => '1',
                'link_in_title' => 'https://example.com',
            ]);

        $this->accessor->callMethod('modifyData');

        $filteredData = $this->accessor->getProperty('filteredData');

        expect($filteredData)->toHaveKey('options')
            ->and($filteredData['options'])->toHaveKey('hide_header')
            ->and($filteredData['options'])->toHaveKey('link_in_title');
    });

    it('validates boolean field correctly', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([
                'hide_header'   => 'true',
                'link_in_title' => 'https://example.com',
            ]);

        $this->accessor->callMethod('modifyData');

        $filteredData = $this->accessor->getProperty('filteredData');

        expect($filteredData['options']['hide_header'])->toBeTrue();
    });

    it('validates URL field correctly', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([
                'hide_header'   => '0',
                'link_in_title' => 'https://example.com',
            ]);

        $this->accessor->callMethod('modifyData');

        $filteredData = $this->accessor->getProperty('filteredData');

        expect($filteredData['options']['link_in_title'])->toBe('https://example.com');
    });

    it('invalidates malformed URL', function () {
        $this->post->shouldReceive('only')
            ->once()
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([
                'hide_header'   => '0',
                'link_in_title' => 'not-a-url',
            ]);

        $this->accessor->callMethod('modifyData');

        $filteredData = $this->accessor->getProperty('filteredData');

        expect($filteredData['options']['link_in_title'])->toBeFalse();
    });
});

describe('BlockValidator::checkAreas', function () {
    it('validates areas scenarios', function ($areasValue, $validatedAreas, $expectedErrors) {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('areas')
            ->andReturn($areasValue);

        $this->validator->setMockPost($post);
        $this->accessor->setProperty('filteredData', ['areas' => $validatedAreas]);

        $this->accessor->callMethod('checkAreas');

        $errors = $this->accessor->getProperty('errors');

        foreach ($expectedErrors as $expectedError) {
            expect($errors)->toContain($expectedError);
        }

        if (empty($expectedErrors)) {
            expect($errors)->not->toContain('no_areas')
                ->and($errors)->not->toContain('no_valid_areas');
        }

        if (in_array('no_valid_areas', $expectedErrors)) {
            $filteredData = $this->accessor->getProperty('filteredData');
            expect($filteredData['areas'])->toBe($areasValue);
        }
    })->with('areas validation cases');
});

describe('BlockValidator::extendErrors', function () {
    it('resets errors before validation', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('areas')
            ->andReturn('all');

        $this->validator->setMockPost($post);
        $this->accessor->setProperty('errors', ['old_error']);
        $this->accessor->setProperty('filteredData', ['areas' => 'all']);

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(PortalHook::findBlockErrors, Mockery::any());

        $this->accessor->callMethod('extendErrors');

        $errors = $this->accessor->getProperty('errors');

        expect($errors)->not->toContain('old_error');
    });

    it('dispatches findBlockErrors event with correct parameters', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('areas')
            ->andReturn('all');

        $this->validator->setMockPost($post);
        $this->accessor->setProperty('filteredData', ['areas' => 'all', 'title' => 'Test']);

        $wasDispatched = false;

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with(
                PortalHook::findBlockErrors,
                Mockery::on(function ($args) use (&$wasDispatched) {
                    $wasDispatched = isset($args['errors']) && isset($args['data']) && is_array($args['data']);

                    return isset($args['errors']) && isset($args['data']) && is_array($args['data']);
                })
            );

        $this->accessor->callMethod('extendErrors');

        expect($wasDispatched)->toBeTrue();
    });
});

describe('BlockValidator::integration', function () {
    it('performs full validation with valid data', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'       => 'Test Block',
                'block_id'    => '1',
                'icon'        => 'fas fa-test',
                'type'        => 'html',
                'placement'   => 'left',
                'priority'    => '5',
                'permissions' => '0',
                'areas'       => 'all',
            ]);

        $this->post->shouldReceive('get')
            ->with('areas')
            ->andReturn('all');

        $this->post->shouldReceive('only')
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([]);

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validateBlockParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findBlockErrors, Mockery::any());

        $result = $this->validator->validate();

        expect($result)->toHaveKey('title')
            ->and($result)->toHaveKey('block_id')
            ->and($result)->toHaveKey('areas')
            ->and(Utils::$context)->not->toHaveKey('post_errors');
    });

    it('performs full validation with errors', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title' => '',
                'areas' => '',
            ]);

        $this->post->shouldReceive('get')
            ->with('areas')
            ->andReturn('');

        $this->post->shouldReceive('only')
            ->with(['hide_header', 'link_in_title'])
            ->andReturn([]);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::validateBlockParams, Mockery::any());

        $this->dispatcher->shouldReceive('dispatch')
            ->with(PortalHook::findBlockErrors, Mockery::any());

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->not->toContain('Title is required')
            ->and(Utils::$context['post_errors'])->toContain('Areas are required');
    });
});
