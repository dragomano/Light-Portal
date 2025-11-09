<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\DataHandlers\Imports\TagImport;
use LightPortal\Enums\Status;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $this->fileMock = mock(FileInterface::class);
    $this->errorHandlerMock = mock(ErrorHandlerInterface::class)->shouldIgnoreMissing();

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::TAGS->value)->execute();
    $adapter->query(PortalTable::PAGE_TAG->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);
});

function generateTagXml(array $scenario): string
{
    $titlesXml = '';
    if (! empty($scenario['titles'])) {
        $titlesXml = '<titles>';
        foreach ($scenario['titles'] as $lang => $title) {
            $titlesXml .= "<$lang>$title</$lang>";
        }

        $titlesXml .= '</titles>';
    }

    $icon = $scenario['icon'] ?? '';

    $pagesXml = '';
    if (! empty($scenario['pages'])) {
        $pagesXml = '<pages>';
        foreach ($scenario['pages'] as $pageId) {
            $pagesXml .= "<page id=\"$pageId\"/>";
        }

        $pagesXml .= '</pages>';
    }

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <tags>
        <item tag_id="{$scenario['tag_id']}" status="{$scenario['status']}">
            <slug>{$scenario['slug']}</slug>
            <icon>$icon</icon>
            {$titlesXml}
            {$pagesXml}
        </item>
    </tags>
</light_portal>
XML;
}

dataset('tag scenarios', [
    'full' => [[
        'tag_id' => 1,
        'slug'   => 'full-tag',
        'icon'   => 'fas fa-tag',
        'status' => Status::ACTIVE->value,
        'titles' => ['english' => 'Test Tag', 'russian' => 'Тестовый тег'],
        'pages'  => [1, 2],
    ]],
    'no_pages' => [[
        'tag_id' => 2,
        'slug'   => 'no-pages-tag',
        'icon'   => 'fas fa-minus',
        'status' => Status::INACTIVE->value,
        'titles' => ['english' => 'No Pages'],
        'pages'  => [],
    ]],
    'no_icon' => [[
        'tag_id' => 3,
        'slug'   => 'no-icon-tag',
        'status' => Status::ACTIVE->value,
        'titles' => ['english' => 'No Icon Tag'],
        'pages'  => [3],
    ]],
    'english_only' => [[
        'tag_id' => 4,
        'slug'   => 'english-only-tag',
        'icon'   => 'fas fa-star',
        'status' => Status::ACTIVE->value,
        'titles' => ['english' => 'English Only'],
        'pages'  => [],
    ]],
]);

it('imports tags correctly for all scenarios', function (array $scenario) {
    $xml = simplexml_load_string(generateTagXml($scenario));

    $import = new ReflectionAccessor(new TagImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProtectedProperty('xml', $xml);
    $import->callProtectedMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_tags')->execute()
    );

    $tag = array_filter($rows, fn($r) => $r['slug'] === $scenario['slug']);
    $tag = reset($tag);

    expect($tag)->not->toBeNull()
        ->and($tag['slug'])->toBe($scenario['slug'])
        ->and($tag['icon'] ?? '')->toBe($scenario['icon'] ?? '')
        ->and($tag['status'])->toBe($scenario['status']);

    $pages = iterator_to_array(
        $this->sql->getAdapter()
            ->query(/** @lang text */ 'SELECT * FROM lp_page_tag WHERE tag_id = ?')
            ->execute([$scenario['tag_id']])
    );
    expect(count($pages))->toBe(count($scenario['pages']));

    foreach ($scenario['titles'] ?? [] as $lang => $title) {
        $transRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? AND item_id = ? AND lang = ?')
                ->execute(['tag', $scenario['tag_id'], $lang])
        );
        expect(reset($transRow)['title'])->toBe($title);
    }
})->with('tag scenarios');
