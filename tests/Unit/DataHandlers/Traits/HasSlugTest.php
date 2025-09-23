<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Traits\HasSlug;

if (! defined('LP_ALIAS_PATTERN')) {
    define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
}

beforeEach(function () {
    $this->testClass = new class {
        use HasSlug;

        public mixed $db;

        public function __construct($db = null)
        {
            $this->db = $db;
        }

        public function extractTranslations($item): array
        {
            $translations = [];

            if (isset($item->titles)) {
                foreach ($item->titles as $lang => $title) {
                    $translations[] = [
                        'lang' => $lang,
                        'title' => $title,
                    ];
                }
            }

            if (isset($item->contents)) {
                $index = 0;
                foreach ($item->contents as $lang => $content) {
                    if (isset($translations[$index])) {
                        $translations[$index]['content'] = $content;
                    } else {
                        $translations[] = [
                            'lang' => $lang,
                            'title' => '',
                            'content' => $content,
                        ];
                    }

                    $index++;
                }
            }

            if (isset($item->descriptions)) {
                $index = 0;
                foreach ($item->descriptions as $lang => $description) {
                    if (isset($translations[$index])) {
                        $translations[$index]['description'] = $description;
                    } else {
                        $translations[] = [
                            'lang' => $lang,
                            'title' => '',
                            'description' => $description,
                        ];
                    }

                    $index++;
                }
            }

            return $translations;
        }

        public function generateSlug(array $titles): string
        {
            if (empty($titles)) {
                return 'page-' . substr((string) time(), -6);
            }

            $selectedTitle = $this->selectTitleByPriority($titles);
            $slug = $this->cleanAndFormatSlug($selectedTitle);

            return $slug ?: 'page-' . substr((string) time(), -6);
        }

        public function selectTitleByPriority(array $titles): string
        {
            $priority = ['english', 'russian', 'german'];

            foreach ($priority as $lang) {
                if (isset($titles[$lang]) && ! empty(trim($titles[$lang]))) {
                    return $titles[$lang];
                }
            }

            return reset($titles) ?: '';
        }

        public function cleanAndFormatSlug(string $text): string
        {
            $slug = strtolower(trim($text));
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/\s+/', '-', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');

            if (! preg_match('/' . LP_ALIAS_PATTERN . '/', $slug)) {
                $slug = 'page-' . $slug;
            }

            if (strlen($slug) > 255) {
                $slug = substr($slug, 0, 255);
                $slug = rtrim($slug, '-');
            }

            return $slug;
        }

        public function getPrefixForEntity(bool $full = false): string
        {
            return $full ? 'page' : 'page-';
        }

        public function getShortPrefix(): string
        {
            return 'page';
        }

        public function generateShortId(): string
        {
            return substr((string) time(), -6);
        }

        public function callInitializeSlugAndTranslations($item, int $entityId, array &$titles): string
        {
            return $this->initializeSlugAndTranslations($item, $entityId, $titles);
        }

        public function callUpdateSlugs(array &$items, array $titles, string $idKey): void
        {
            $this->updateSlugs($items, $titles, $idKey);
        }
    };
});

it('initializes slug and translations with provided slug', function () {
    $item = (object) [
        'slug' => 'test-page',
        'titles' => [
            'english' => 'Test Page',
            'russian' => 'Тестовая страница',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 1, $titles);

    expect($result)->toBe('test-page')
        ->and($titles)->toHaveKey(1)
        ->and($titles[1])->toHaveKey('english')
        ->and($titles[1]['english'])->toBe('Test Page')
        ->and($titles[1])->toHaveKey('russian')
        ->and($titles[1]['russian'])->toBe('Тестовая страница');
});

it('generates temp slug when slug is empty', function () {
    $item = (object) [
        'slug' => '',
        'titles' => [
            'english' => 'Test Page',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 123, $titles);

    expect($result)->toBe('temp-123')
        ->and($titles)->toHaveKey(123)
        ->and($titles[123])->toHaveKey('english')
        ->and($titles[123]['english'])->toBe('Test Page');
});

it('handles null slug correctly', function () {
    $item = (object) [
        'slug' => null,
        'titles' => [
            'english' => 'Test Page',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 456, $titles);

    expect($result)->toBe('temp-456')
        ->and($titles)->toHaveKey(456);
});

it('handles missing slug property', function () {
    $item = (object) [
        'titles' => [
            'english' => 'Test Page',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 789, $titles);

    expect($result)->toBe('temp-789')
        ->and($titles)->toHaveKey(789);
});

it('handles item without translations', function () {
    $item = (object) [
        'slug' => 'test-page',
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 1, $titles);

    expect($result)->toBe('test-page')
        ->and($titles)->toBeEmpty();
});

it('processes multiple translation fields', function () {
    $item = (object) [
        'slug' => 'test-page',
        'titles' => [
            'english' => 'Test Page',
            'russian' => 'Тестовая страница',
        ],
        'contents' => [
            'english' => 'Test content',
            'russian' => 'Тестовый контент',
        ],
        'descriptions' => [
            'english' => 'Test description',
            'russian' => 'Тестовое описание',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 1, $titles);

    expect($result)->toBe('test-page')
        ->and($titles[1]['english'])->toBe('Test Page')
        ->and($titles[1]['russian'])->toBe('Тестовая страница');
});

it('handles unicode characters in translations', function () {
    $item = (object) [
        'slug' => 'unicode-test',
        'titles' => [
            'russian' => 'Страница с юникодом: 中文 русский עברית',
            'chinese' => '中文页面',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 1, $titles);

    expect($result)->toBe('unicode-test')
        ->and($titles[1]['russian'])->toBe('Страница с юникодом: 中文 русский עברית')
        ->and($titles[1]['chinese'])->toBe('中文页面');
});

it('handles emoji in translations', function () {
    $item = (object) [
        'slug' => 'emoji-test',
        'titles' => [
            'english' => 'Page with emoji 🚀 🌟',
        ],
    ];

    $titles = [];
    $result = $this->testClass->callInitializeSlugAndTranslations($item, 1, $titles);

    expect($result)->toBe('emoji-test')
        ->and($titles[1]['english'])->toBe('Page with emoji 🚀 🌟');
});

it('updates slugs for items with temp prefix', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Test Page 1',
        ],
        [
            'id' => 2,
            'slug' => 'temp-2',
            'title' => 'Test Page 2',
        ],
    ];

    $titles = [
        1 => ['english' => 'Test Page 1'],
        2 => ['english' => 'Test Page 2'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('test-page-1')
        ->and($items[1]['slug'])->toBe('test-page-2');
});

it('leaves non-temp slugs unchanged', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'existing-slug',
            'title' => 'Test Page 1',
        ],
        [
            'id' => 2,
            'slug' => 'temp-2',
            'title' => 'Test Page 2',
        ],
    ];

    $titles = [
        1 => ['english' => 'Test Page 1'],
        2 => ['english' => 'Test Page 2'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('existing-slug')
        ->and($items[1]['slug'])->toBe('test-page-2');
});

it('handles missing titles gracefully', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Test Page 1',
        ],
    ];

    $titles = []; // Нет переводов

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('page-' . substr((string) time(), -6));
});

it('handles empty titles array', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => '',
        ],
    ];

    $titles = [1 => []]; // Пустой массив переводов

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('page-' . substr((string) time(), -6));
});

it('processes multiple items with different id keys', function () {
    $items = [
        [
            'page_id' => 1,
            'slug' => 'temp-1',
            'title' => 'Test Page 1',
        ],
        [
            'page_id' => 2,
            'slug' => 'temp-2',
            'title' => 'Test Page 2',
        ],
    ];

    $titles = [
        1 => ['english' => 'Test Page 1'],
        2 => ['english' => 'Test Page 2'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'page_id');

    expect($items[0]['slug'])->toBe('test-page-1')
        ->and($items[1]['slug'])->toBe('test-page-2');
});

it('handles unicode titles in slug generation', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Страница с unicode 中文 русский',
        ],
    ];

    $titles = [
        1 => ['russian' => 'Страница с unicode 中文 русский'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('unicode');
});

it('handles emoji in titles', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Page with emoji 🚀 🌟',
        ],
    ];

    $titles = [
        1 => ['english' => 'Page with emoji 🚀 🌟'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('page-with-emoji');
});

it('handles special characters in titles', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Page with special chars: éñünicôdé!',
        ],
    ];

    $titles = [
        1 => ['english' => 'Page with special chars: éñünicôdé!'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('page-with-special-chars-nicd');
});

it('handles multilingual titles with priority', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Test Page',
        ],
    ];

    $titles = [
        1 => [
            'english' => 'English Title',
            'russian' => 'Русское название',
            'german' => 'Deutscher Titel',
        ],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('english-title');
});

it('handles very long titles', function () {
    $longTitle = str_repeat('Very long title with many words ', 10);

    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => $longTitle,
        ],
    ];

    $titles = [
        1 => ['english' => $longTitle],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect(strlen($items[0]['slug']))->toBeLessThanOrEqual(255)
        ->and($items[0]['slug'])->toStartWith('very-long-title-with-many-words');
});

it('handles titles with numbers and mixed case', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'temp-1',
            'title' => 'Page 123 Test CASE',
        ],
    ];

    $titles = [
        1 => ['english' => 'Page 123 Test CASE'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('page-123-test-case');
});

it('handles empty items array', function () {
    $items = [];
    $titles = [];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items)->toBeEmpty();
});

it('handles items with mixed temp and non-temp slugs', function () {
    $items = [
        [
            'id' => 1,
            'slug' => 'existing-slug',
            'title' => 'Existing Page',
        ],
        [
            'id' => 2,
            'slug' => 'temp-2',
            'title' => 'Temp Page',
        ],
        [
            'id' => 3,
            'slug' => 'another-existing',
            'title' => 'Another Page',
        ],
    ];

    $titles = [
        2 => ['english' => 'Temp Page'],
    ];

    $this->testClass->callUpdateSlugs($items, $titles, 'id');

    expect($items[0]['slug'])->toBe('existing-slug')
        ->and($items[1]['slug'])->toBe('temp-page')
        ->and($items[2]['slug'])->toBe('another-existing');
});
