<?php

declare(strict_types=1);

use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Imports\PageImport;
use LightPortal\DataHandlers\Traits\HasSlug;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;

beforeEach(function () {
    $sql          = mock(PortalSqlInterface::class);
    $file         = mock(FileInterface::class);
    $errorHandler = mock(ErrorHandlerInterface::class);

    $this->testClass = new class($sql, $file, $errorHandler) extends PageImport {
        use HasSlug;

        public function extractTranslations($item, int $id): array
        {
            $translations = [];

            if (isset($item->titles)) {
                foreach ($item->titles as $lang => $title) {
                    $translations[] = [
                        'lang'  => $lang,
                        'title' => $title,
                    ];
                }
            }

            return $translations;
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

it('generates temp slug when slug is empty', function () {
    $item = (object) [
        'slug'   => '',
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

it('updates slugs for items with temp prefix', function () {
    $items = [
        [
            'id'    => 1,
            'slug'  => 'temp-1',
            'title' => 'Test Page 1',
        ],
        [
            'id'    => 2,
            'slug'  => 'temp-2',
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
