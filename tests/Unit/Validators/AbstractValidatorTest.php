<?php

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\RequestInterface;
use LightPortal\Validators\AbstractValidator;
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
    ) extends AbstractValidator {
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

dataset('title sanitization cases', [
    'xss script tag' => [
        'input'            => '<script>alert("XSS")</script>Test Title',
        'shouldNotContain' => ['<script>', '</script>'],
    ],
    'html tags' => [
        'input'            => '<div><b>Bold</b> Title</div>',
        'shouldNotContain' => ['<div>', '</div>', '<b>', '</b>'],
    ],
    'mixed content' => [
        'input'            => 'Normal <strong>text</strong> with <a href="#">link</a>',
        'shouldNotContain' => ['<strong>', '<a href', '</a>'],
    ],
]);

dataset('title validation cases', [
    'empty string' => [
        'title'           => '',
        'shouldHaveError' => true,
    ],
    'null value' => [
        'title'           => null,
        'shouldHaveError' => true,
    ],
    'valid title' => [
        'title'           => 'Valid Title',
        'shouldHaveError' => false,
    ],
    'whitespace only' => [
        'title'           => '   ',
        'shouldHaveError' => false,
    ],
]);

dataset('slug validation cases', [
    'empty slug' => [
        'rawSlug'        => '',
        'validatedSlug'  => null,
        'isUnique'       => true,
        'expectedErrors' => ['no_slug'],
    ],
    'invalid slug' => [
        'rawSlug'        => 'invalid slug with spaces',
        'validatedSlug'  => false,
        'isUnique'       => true,
        'expectedErrors' => ['no_valid_slug'],
    ],
    'non-unique slug' => [
        'rawSlug'        => 'existing-slug',
        'validatedSlug'  => 'existing-slug',
        'isUnique'       => false,
        'expectedErrors' => ['no_unique_slug'],
    ],
    'valid unique slug' => [
        'rawSlug'        => 'valid-slug',
        'validatedSlug'  => 'valid-slug',
        'isUnique'       => true,
        'expectedErrors' => [],
    ],
]);

dataset('recursive filter cases', [
    'flat array with nulls' => [
        'input' => [
            'key1' => 'value1',
            'key2' => null,
            'key3' => 'value3',
        ],
        'expected' => [
            'key1' => 'value1',
            'key3' => 'value3',
        ],
    ],
    'nested arrays' => [
        'input' => [
            'key1'   => 'value1',
            'key2'   => null,
            'nested' => [
                'nested1' => 'value2',
                'nested2' => null,
                'deep'    => [
                    'deep1' => 'value3',
                    'deep2' => null,
                ],
            ],
        ],
        'expected' => [
            'key1'   => 'value1',
            'nested' => [
                'nested1' => 'value2',
                'deep'    => [
                    'deep1' => 'value3',
                ],
            ],
        ],
    ],
    'falsy values preserved' => [
        'input' => [
            'zero'         => 0,
            'empty_string' => '',
            'false'        => false,
            'null'         => null,
        ],
        'expected' => [
            'zero'         => 0,
            'empty_string' => '',
            'false'        => false,
        ],
    ],
    'deeply nested with multiple nulls' => [
        'input' => [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep',
                        'null'  => null,
                    ],
                    'null' => null,
                ],
                'valid' => 'test',
            ],
        ],
        'expected' => [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep',
                    ],
                ],
                'valid' => 'test',
            ],
        ],
    ],
]);

dataset('error handling cases', [
    'no errors' => [
        'errors'               => [],
        'shouldSetPreview'     => false,
        'shouldHavePostErrors' => false,
    ],
    'single error' => [
        'errors'               => ['no_title'],
        'shouldSetPreview'     => true,
        'shouldHavePostErrors' => true,
        'expectedMessages'     => ['Title is required'],
    ],
    'multiple errors' => [
        'errors'               => ['no_title', 'no_slug'],
        'shouldSetPreview'     => true,
        'shouldHavePostErrors' => true,
        'expectedMessages'     => ['Title is required', 'Slug is required'],
    ],
    'error without translation' => [
        'errors'               => ['custom_error'],
        'shouldSetPreview'     => true,
        'shouldHavePostErrors' => true,
        'expectedMessages'     => ['custom_error'],
    ],
]);

describe('AbstractValidator::__construct', function () {
    it('initializes with default title filter', function () {
        $filters = $this->accessor->getProtectedProperty('filters');

        expect($filters)->toHaveKey('title')
            ->and($filters['title'])->toHaveKey('filter')
            ->and($filters['title'])->toHaveKey('options')
            ->and($filters['title']['filter'])->toBe(FILTER_CALLBACK);
    });

    it('properly sanitizes title with filter', function ($input, $shouldNotContain) {
        $filters     = $this->accessor->getProtectedProperty('filters');
        $titleFilter = $filters['title']['options'];

        $result = $titleFilter($input);

        foreach ($shouldNotContain as $text) {
            expect($result)->not->toContain($text);
        }
    })->with('title sanitization cases');
});

describe('AbstractValidator::validate', function () {
    it('returns empty array when no save action in request', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(true);

        $result = $this->validator->validate();

        expect($result)->toBe([]);
    });

    it('processes data when save action is present', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn(['title' => 'Test Title']);

        $result = $this->validator->validate();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('title');
    });

    it('filters out null values from result', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title'       => 'Test Title',
                'description' => null,
            ]);

        $result = $this->validator->validate();

        expect($result)->toHaveKey('title')
            ->and($result)->not->toHaveKey('description');
    });

    it('calls extendFilters hook', function () {
        $validator = new class(
            $this->sql,
            $this->dispatcher
        ) extends AbstractValidator {
            public bool $extendFiltersCalled = false;

            private RequestInterface $mockRequest;

            private PostInterface $mockPost;

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
                return $this->mockRequest;
            }

            public function post(): PostInterface
            {
                return $this->mockPost;
            }

            protected function extendFilters(): void
            {
                $this->extendFiltersCalled = true;
            }
        };

        $validator->setMockRequest($this->request);
        $validator->setMockPost($this->post);

        $this->request->shouldReceive('hasNot')
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->andReturn(['title' => 'Test']);

        $validator->validate();

        expect($validator->extendFiltersCalled)->toBeTrue();
    });

    it('calls modifyData hook', function () {
        $validator = new class(
            $this->sql,
            $this->dispatcher
        ) extends AbstractValidator {
            public bool $modifyDataCalled = false;

            private RequestInterface $mockRequest;

            private PostInterface $mockPost;

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
                return $this->mockRequest;
            }

            public function post(): PostInterface
            {
                return $this->mockPost;
            }

            protected function modifyData(): void
            {
                $this->modifyDataCalled = true;
            }
        };

        $validator->setMockRequest($this->request);
        $validator->setMockPost($this->post);

        $this->request->shouldReceive('hasNot')
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->andReturn(['title' => 'Test']);

        $validator->validate();

        expect($validator->modifyDataCalled)->toBeTrue();
    });

    it('calls extendErrors hook', function () {
        $validator = new class(
            $this->sql,
            $this->dispatcher
        ) extends AbstractValidator {
            public bool $extendErrorsCalled = false;

            private RequestInterface $mockRequest;

            private PostInterface $mockPost;

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
                return $this->mockRequest;
            }

            public function post(): PostInterface
            {
                return $this->mockPost;
            }

            protected function extendErrors(): void
            {
                $this->extendErrorsCalled = true;
            }
        };

        $validator->setMockRequest($this->request);
        $validator->setMockPost($this->post);

        $this->request->shouldReceive('hasNot')
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->andReturn(['title' => 'Test']);

        $validator->validate();

        expect($validator->extendErrorsCalled)->toBeTrue();
    });
});

describe('AbstractValidator::checkErrors', function () {
    it('validates title correctly', function ($title, $shouldHaveError) {
        if ($shouldHaveError) {
            $this->request->shouldReceive('put')
                ->once()
                ->with('preview', true);
        }

        $this->accessor->setProtectedProperty('filteredData', ['title' => $title]);

        $this->accessor->callProtectedMethod('checkErrors');

        $errors = $this->accessor->getProtectedProperty('errors');

        if ($shouldHaveError) {
            expect($errors)->toContain('no_title');
        } else {
            expect($errors)->not->toContain('no_title');
        }
    })->with('title validation cases');
});

describe('AbstractValidator::checkSlug', function () {
    it('validates slug scenarios', function ($rawSlug, $validatedSlug, $isUnique, $expectedErrors) {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn($rawSlug);

        $validator = new class(
            $this->sql,
            $this->dispatcher
        ) extends AbstractValidator {
            private bool $uniqueResult = true;

            private ?PostInterface $mockPost = null;

            public function setMockPost(PostInterface $post): void
            {
                $this->mockPost = $post;
            }

            public function post(): PostInterface
            {
                return $this->mockPost;
            }

            protected function isUnique(): bool
            {
                return $this->uniqueResult;
            }

            public function setUniqueResult(bool $result): void
            {
                $this->uniqueResult = $result;
            }
        };

        $validator->setMockPost($post);
        $validator->setUniqueResult($isUnique);

        $accessor = new ReflectionAccessor($validator);
        $accessor->setProtectedProperty('filteredData', ['slug' => $validatedSlug]);

        $accessor->callProtectedMethod('checkSlug');

        $errors = $accessor->getProtectedProperty('errors');

        foreach ($expectedErrors as $expectedError) {
            expect($errors)->toContain($expectedError);
        }

        if (empty($expectedErrors)) {
            expect($errors)->toBeEmpty();
        }
    })->with('slug validation cases');
});

describe('AbstractValidator::handleErrors', function () {
    it('handles error scenarios correctly', function (
        $errors, $shouldSetPreview, $shouldHavePostErrors, $expectedMessages = []
    ) {
        $this->accessor->setProtectedProperty('errors', $errors);

        if ($shouldSetPreview) {
            $this->request->shouldReceive('put')
                ->once()
                ->with('preview', true);
        }

        $this->accessor->callProtectedMethod('handleErrors');

        if ($shouldHavePostErrors) {
            expect(Utils::$context)->toHaveKey('post_errors');

            foreach ($expectedMessages as $message) {
                expect(Utils::$context['post_errors'])->toContain($message);
            }
        } else {
            expect(Utils::$context)->not->toHaveKey('post_errors');
        }
    })->with('error handling cases');
});

describe('AbstractValidator::isUnique', function () {
    it('returns true by default', function () {
        $result = $this->accessor->callProtectedMethod('isUnique');

        expect($result)->toBeTrue();
    });
});

describe('AbstractValidator::recursiveArrayFilter', function () {
    it('filters arrays correctly', function ($input, $expected) {
        $result = $this->accessor->callProtectedMethod('recursiveArrayFilter', [$input]);

        expect($result)->toBe($expected);
    })->with('recursive filter cases');
});

describe('AbstractValidator::integration', function () {
    it('performs full validation cycle with valid data', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title' => 'Test <script>Title</script>',
            ]);

        $result = $this->validator->validate();

        expect($result)->toHaveKey('title')
            ->and($result['title'])->not->toContain('<script>')
            ->and(Utils::$context)->not->toHaveKey('post_errors');
    });

    it('performs full validation cycle with errors', function () {
        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title' => '',
            ]);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $this->validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Title is required');
    });

    it('handles multiple validation errors in full cycle', function () {
        $post = mock(PostInterface::class);
        $post->shouldReceive('get')
            ->with('slug')
            ->andReturn('invalid slug');

        $post->shouldReceive('all')
            ->once()
            ->andReturn([
                'title' => '',
                'slug'  => 'invalid slug',
            ]);

        $validator = new class(
            $this->sql,
            $this->dispatcher
        ) extends AbstractValidator {
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
                return $this->mockRequest;
            }

            public function post(): PostInterface
            {
                return $this->mockPost;
            }

            protected function extendFilters(): void
            {
                $this->filters['slug'] = [
                    'filter'  => FILTER_CALLBACK,
                    'options' => fn($slug) => preg_match('/^[a-z0-9-]+$/', $slug) ? $slug : false,
                ];
            }

            protected function extendErrors(): void
            {
                $this->checkSlug();
            }
        };

        $validator->setMockRequest($this->request);
        $validator->setMockPost($post);

        $this->request->shouldReceive('hasNot')
            ->once()
            ->with(['save', 'save_exit', 'preview'])
            ->andReturn(false);

        $this->request->shouldReceive('put')
            ->once()
            ->with('preview', true);

        $validator->validate();

        expect(Utils::$context)->toHaveKey('post_errors')
            ->and(Utils::$context['post_errors'])->toContain('Title is required')
            ->and(Utils::$context['post_errors'])->toContain('Slug is not valid');
    });
});
