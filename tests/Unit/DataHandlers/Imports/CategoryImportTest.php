<?php declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\DataHandlers\Imports\CategoryImport;
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
    $adapter->query(PortalTable::CATEGORIES->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);
});

function generateCategoryXml(array $scenario): string
{
    $titlesXml = '';
    if (! empty($scenario['titles'])) {
        $titlesXml = '<titles>';
        foreach ($scenario['titles'] as $lang => $title) {
            $titlesXml .= "<$lang>$title</$lang>";
        }

        $titlesXml .= '</titles>';
    }

    $descriptionsXml = '';
    if (! empty($scenario['descriptions'])) {
        $descriptionsXml = '<descriptions>';
        foreach ($scenario['descriptions'] as $lang => $text) {
            $descriptionsXml .= "<$lang><![CDATA[$text]]></$lang>";
        }

        $descriptionsXml .= '</descriptions>';
    }

    $icon = $scenario['icon'] ?? '';

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item
            category_id="{$scenario['category_id']}"
            parent_id="{$scenario['parent_id']}"
            priority="{$scenario['priority']}"
            status="{$scenario['status']}"
        >
            <slug>{$scenario['slug']}</slug>
            <icon>$icon</icon>
            {$titlesXml}
            {$descriptionsXml}
        </item>
    </categories>
</light_portal>
XML;
}

dataset('category scenarios', [
    'full' => [[
        'category_id'  => 1,
        'parent_id'    => 0,
        'priority'     => 1,
        'status'       => Status::ACTIVE->value,
        'slug'         => 'full-category',
        'icon'         => 'fas fa-folder',
        'titles'       => [
            'english'  => 'Full English Title',
            'russian'  => 'Полный заголовок',
        ],
        'descriptions' => [
            'english' => 'English description text...',
            'russian' => 'Русский текст описания...',
        ],
    ]],
    'no_icon' => [[
        'category_id'  => 2,
        'parent_id'    => 0,
        'priority'     => 2,
        'status'       => Status::ACTIVE->value,
        'slug'         => 'no-icon-category',
        'titles'       => ['english'  => 'No Icon Category'],
        'descriptions' => ['english' => 'English only description'],
    ]],
    'no_description' => [[
        'category_id'  => 3,
        'parent_id'    => 0,
        'priority'     => 3,
        'status'       => Status::ACTIVE->value,
        'slug'         => 'no-description-category',
        'icon'         => 'fas fa-star',
        'titles'       => [
            'english'  => 'No Description Category',
            'russian'  => 'Нет описания',
        ],
        'descriptions' => [],
    ]],
    'english_only_description' => [[
        'category_id'  => 4,
        'parent_id'    => 0,
        'priority'     => 4,
        'status'       => Status::ACTIVE->value,
        'slug'         => 'english-only-category',
        'icon'         => 'fas fa-star-half-alt',
        'titles'       => ['english'  => 'English Only'],
        'descriptions' => ['english' => 'Only English description'],
    ]],
]);

it('imports categories correctly for all scenarios', function (array $scenario) {
    $xml = simplexml_load_string(generateCategoryXml($scenario));

    $import = new ReflectionAccessor(new CategoryImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProtectedProperty('xml', $xml);
    $import->callProtectedMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_categories')->execute()
    );

    $category = array_filter($rows, fn($r) => $r['slug'] === $scenario['slug']);
    $category = reset($category);

    expect($category)->not->toBeNull()
        ->and($category['slug'])->toBe($scenario['slug'])
        ->and($category['priority'])->toBe($scenario['priority'])
        ->and($category['status'])->toBe($scenario['status'])
        ->and($category['parent_id'])->toBe($scenario['parent_id'] ?? 0)
        ->and($category['icon'] ?? '')->toBe($scenario['icon'] ?? '');

    foreach ($scenario['titles'] ?? [] as $lang => $title) {
        $transRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? AND item_id = ? AND lang = ?')
                ->execute(['category', $scenario['category_id'], $lang])
        );
        expect(reset($transRow)['title'])->toBe($title);
    }

    foreach ($scenario['descriptions'] ?? [] as $lang => $desc) {
        $transRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? AND item_id = ? AND lang = ?')
                ->execute(['category', $scenario['category_id'], $lang])
        );
        expect(reset($transRow)['description'])->toBe($desc);
    }
})->with('category scenarios');
