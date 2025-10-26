<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\DataHandlers\Imports\BlockImport;
use LightPortal\Enums\ContentClass;
use LightPortal\Enums\Placement;
use LightPortal\Enums\Status;
use LightPortal\Enums\TitleClass;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $this->fileMock = mock(FileInterface::class);
    $this->errorHandlerMock = mock(ErrorHandlerInterface::class)->shouldIgnoreMissing();

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::BLOCKS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();

    $this->sql = new PortalSql($adapter);
});

function generateBlockXml(array $scenario): string
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
        foreach ($scenario['descriptions'] as $lang => $desc) {
            $descriptionsXml .= "<$lang><![CDATA[$desc]]></$lang>";
        }

        $descriptionsXml .= '</descriptions>';
    }

    $paramsXml = '';
    if (! empty($scenario['params'])) {
        $paramsXml = '<params>';
        foreach ($scenario['params'] as $key => $value) {
            $paramsXml .= "<$key>$value</$key>";
        }

        $paramsXml .= '</params>';
    }

    $icon = $scenario['icon'] ?? '';

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <blocks>
        <item
            block_id="{$scenario['block_id']}"
            priority="{$scenario['priority']}"
            permissions="{$scenario['permissions']}"
            status="{$scenario['status']}"
        >
            <type>{$scenario['type']}</type>
            <placement>{$scenario['placement']}</placement>
            <areas>{$scenario['areas']}</areas>
            <icon>$icon</icon>
            <title_class>{$scenario['title_class']}</title_class>
            <content_class>{$scenario['content_class']}</content_class>
            {$titlesXml}
            {$descriptionsXml}
            {$paramsXml}
        </item>
    </blocks>
</light_portal>
XML;
}

dataset('block scenarios', [
    'full_with_params' => [[
        'block_id'      => 1,
        'type'          => 'simple_chat',
        'placement'     => 'header',
        'priority'      => 0,
        'permissions'   => 3,
        'status'        => Status::INACTIVE->value,
        'areas'         => 'all',
        'icon'          => 'fas fa-user',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
        'titles'        => ['english' => 'Note'],
        'descriptions'  => ['russian' => 'Примечание'],
        'params'        => [
            'refresh_interval' => 2,
            'hide_header'      => 1,
        ],
    ]],
    'no_icon' => [[
        'block_id'      => 2,
        'type'          => 'bbc',
        'placement'     => 'footer',
        'priority'      => 1,
        'permissions'   => 1,
        'status'        => Status::ACTIVE->value,
        'areas'         => 'all',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
        'titles'        => ['english' => 'Announcement'],
        'descriptions'  => ['english' => 'Simple announcement'],
    ]],
    'no_titles' => [[
        'block_id'      => 3,
        'type'          => 'html',
        'placement'     => 'left',
        'priority'      => 2,
        'permissions'   => 0,
        'status'        => Status::ACTIVE->value,
        'icon'          => 'fas fa-html5',
        'areas'         => 'forum',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
        'descriptions'  => ['english' => 'HTML block'],
    ]],
    'no_descriptions' => [[
        'block_id'      => 4,
        'type'          => 'php',
        'placement'     => 'right',
        'priority'      => 3,
        'permissions'   => 2,
        'status'        => Status::ACTIVE->value,
        'icon'          => 'fas fa-code',
        'areas'         => 'all',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
        'titles'        => ['english' => 'PHP Block'],
    ]],
    'english_only' => [[
        'block_id'      => 5,
        'type'          => 'markdown',
        'placement'     => 'top',
        'priority'      => 4,
        'permissions'   => 1,
        'status'        => Status::ACTIVE->value,
        'icon'          => 'fas fa-markdown',
        'areas'         => 'all',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
        'titles'        => ['english' => 'Markdown Block'],
    ]],
    'minimal' => [[
        'block_id'      => 6,
        'type'          => 'user_info',
        'placement'     => Placement::BOTTOM->name(),
        'priority'      => 5,
        'permissions'   => 0,
        'status'        => Status::ACTIVE->value,
        'areas'         => 'all',
        'title_class'   => TitleClass::CAT_BAR->value,
        'content_class' => ContentClass::WINDOWBG->value,
    ]],
]);

it('imports blocks correctly for all scenarios', function (array $scenario) {
    $xml = simplexml_load_string(generateBlockXml($scenario));

    $import = new ReflectionAccessor(new BlockImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProtectedProperty('xml', $xml);
    $import->callProtectedMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_blocks')->execute()
    );

    $block = array_filter($rows, fn($r) => $r['block_id'] === $scenario['block_id']);
    $block = reset($block);

    expect($block)->not->toBeNull()
        ->and($block['type'])->toBe($scenario['type'])
        ->and($block['placement'])->toBe($scenario['placement'])
        ->and($block['priority'])->toBe($scenario['priority'])
        ->and($block['permissions'])->toBe($scenario['permissions'])
        ->and($block['status'])->toBe($scenario['status'])
        ->and($block['areas'])->toBe($scenario['areas'])
        ->and($block['icon'] ?? '')->toBe($scenario['icon'] ?? '')
        ->and($block['title_class'])->toBe($scenario['title_class'])
        ->and($block['content_class'])->toBe($scenario['content_class']);

    foreach ($scenario['titles'] ?? [] as $lang => $title) {
        $transRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? AND item_id = ? AND lang = ?')
                ->execute(['block', $scenario['block_id'], $lang])
        );
        expect(reset($transRow)['title'])->toBe($title);
    }

    foreach ($scenario['descriptions'] ?? [] as $lang => $desc) {
        $transRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? AND item_id = ? AND lang = ?')
                ->execute(['block', $scenario['block_id'], $lang])
        );
        expect(reset($transRow)['description'])->toBe($desc);
    }

    foreach ($scenario['params'] ?? [] as $key => $value) {
        $paramRow = iterator_to_array(
            $this->sql->getAdapter()
                ->query(/** @lang text */ 'SELECT * FROM lp_params WHERE type = ? AND item_id = ? AND name = ?')
                ->execute(['block', $scenario['block_id'], $key])
        );
        expect(reset($paramRow)['value'])->toBe((string) $value);
    }
})->with('block scenarios');
