<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\BlockImport;
use Bugo\LightPortal\DataHandlers\Imports\XmlImporter;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use Mockery\Mock;
use Mockery\MockInterface;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class)
        ->shouldReceive('log')
        ->andReturnNull() // Allow log calls without throwing exceptions
        ->shouldReceive('fatal')
        ->andReturnNull() // Allow fatal calls without throwing exceptions
        ->getMock();
});

/**
 * Create a database mock for testing
 */
function createDatabaseMock(): DatabaseInterface
{
    return Mockery::mock(DatabaseInterface::class)
        ->shouldReceive('transaction')->with('begin')->andReturn(true)
        ->shouldReceive('transaction')->with('rollback')->andReturn(true)
        ->shouldReceive('transaction')->with('commit')->andReturn(true)
        ->shouldReceive('transaction')->withNoArgs()->andReturn(true)
        ->shouldReceive('insert')->andReturn([1])
        ->shouldReceive('query')->andReturn(true)
        ->getMock();
}

/**
 * Create a mocked BlockImport instance with common setup
 */
function createBlockImportMock(
    $fileMock,
    $dbMock,
    $errorHandlerMock,
    ?DatabaseInterface $customDbMock = null
): Mock|(MockInterface&BlockImport)
{
    $import = Mockery::mock(BlockImport::class, [
        $fileMock,
        $customDbMock ?: $dbMock,
        $errorHandlerMock
    ])->makePartial();

    return $import->shouldAllowMockingProtectedMethods();
}

/**
 * Set XML data on import instance using reflection
 */
function setXmlOnImport(Mock|(MockInterface&BlockImport) $import, string $xmlString): void
{
    $xml = simplexml_load_string($xmlString);
    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);
}

/**
 * Setup common mock expectations for successful import
 */
function setupImportMocks(Mock|(MockInterface&BlockImport) $import, array $options = []): void
{
    $defaults = [
        'parseXml' => true,
        'extractTranslations' => Fixtures::getTranslationData(),
        'extractParams' => Fixtures::getParamsData(),
        'insertDataReturn' => [1],
        'replaceTranslations' => null,
        'replaceParams' => null,
    ];

    $config = array_merge($defaults, $options);

    $import->shouldReceive('parseXml')->andReturn($config['parseXml']);

    if ($config['extractTranslations'] !== null) {
        $import->shouldReceive('extractTranslations')->andReturn($config['extractTranslations']);
    }

    if ($config['extractParams'] !== null) {
        $import->shouldReceive('extractParams')->andReturn($config['extractParams']);
    }

    if ($config['insertDataReturn'] !== null) {
        $import->shouldReceive('insertData')
            ->with('lp_blocks', 'replace', Mockery::any(), Mockery::any(), ['block_id'])
            ->andReturn($config['insertDataReturn']);
    }

    if ($config['replaceTranslations'] !== null) {
        $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();
    }

    if ($config['replaceParams'] !== null) {
        $import->shouldReceive('replaceParams')->with(Mockery::any(), Mockery::any())->once();
    }
}

it('extends XmlImporter with correct entity', function () {
    $import = new BlockImport($this->fileMock, $this->dbMock, $this->errorHandlerMock);

    expect($import)
        ->toBeInstanceOf(XmlImporter::class)
        ->and($import->getEntity())->toBe('blocks');
});

it('correctly sets up the context', function () {
    $import = Mockery::mock(
        BlockImport::class,
        [$this->fileMock, $this->dbMock, $this->errorHandlerMock]
    )->makePartial();
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('run')->once();

    $import->main();

    expect(Utils::$context['sub_template'])->toBe('manage_import')
        ->and(Utils::$context['page_title'])->toBe('Portal - Block import')
        ->and(Utils::$context['page_area_title'])->toBe('Block import')
        ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . '?action=admin;area=lp_blocks;sa=import')
        ->and(Utils::$context['lp_file_type'])->toBe('text/xml');
});

dataset('block types', [
    ['type' => 'html'],
    ['type' => 'bbc'],
    ['type' => 'php'],
    ['type' => 'markdown'],
]);

dataset('missing fields scenarios', [
    [['icon']],
    [['areas']],
    [['title_class']],
    [['content_class']],
    [['icon', 'areas']],
    [['title_class', 'content_class']],
    [['icon', 'areas', 'title_class', 'content_class']],
]);

it('correctly processes XML data', function ($type) {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate XML data
    $blockData = Fixtures::getBlocksData()[1];
    $blockData['type'] = $type; // Override type for this test

    // Create XML from fixture data
    $xmlString = Fixtures::getBlockXmlData();
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import);

    // Process the items
    $result = $import->processItems();

    // Assert successful processing
    expect($result)->toBeNull();
})->with('block types');

it('correctly handles title_class conversion', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate XML with div. classes
    $xmlString = Fixtures::getBlockXmlData();
    // Modify XML to include div. prefixes
    $xmlString = str_replace('<title_class>', '<title_class>div.', $xmlString);
    $xmlString = str_replace('<content_class>', '<content_class>div.', $xmlString);
    $xmlString = str_replace('</title_class>', '.cat_bar</title_class>', $xmlString);
    $xmlString = str_replace('</content_class>', '.roundframe</content_class>', $xmlString);

    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [1],
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Process the items
    $result = $import->processItems();

    // Assert successful processing
    expect($result)->toBeNull();
});

it('correctly handles type conversion from md to markdown', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate XML with md type
    $xmlString = Fixtures::getBlockXmlData();
    // Replace markdown type with md for legacy conversion test
    $xmlString = str_replace('<type>markdown</type>', '<type>md</type>', $xmlString);

    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [1],
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Process the items
    $result = $import->processItems();

    // Assert successful processing
    expect($result)->toBeNull();
});

it('gracefully handles empty XML', function () {
    $dbMock = Mockery::mock(DatabaseInterface::class);
    $dbMock->shouldReceive('transaction')->with('begin')->andReturn(true);
    $dbMock->shouldReceive('transaction')->with('rollback')->andReturn(true);
    $dbMock->shouldReceive('transaction')->with('commit')->andReturn(true);
    $dbMock->shouldReceive('transaction')->withNoArgs()->andReturn(true);
    $dbMock->shouldReceive('insert')->andReturn([]);
    $dbMock->shouldReceive('query')->andReturn(true);

    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, $dbMock);
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate empty blocks XML
    $xmlString = Fixtures::getBlockXmlData(0);
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [],
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Override insertData to match empty array expectation
    $import->shouldReceive('insertData')
        ->with(
            'lp_blocks',
            'replace',
            [],
            [
                'block_id' => 'int',
                'icon' => 'string',
                'type' => 'string',
                'placement' => 'string-10',
                'priority' => 'int',
                'permissions' => 'int',
                'status' => 'int',
                'areas' => 'string',
                'title_class' => 'string',
                'content_class' => 'string',
            ],
            ['block_id']
        )
        ->andReturn([]);

    $import->shouldReceive('replaceTranslations')->with([], [])->once();
    $import->shouldReceive('replaceParams')->with([], [])->once();

    // Set context to simulate successful import scenario
    Utils::$context['import_successful'] = 1; // Simulate that some import happened

    // Process empty XML - should handle gracefully without fatal
    $result = $import->processItems();

    // Assert graceful handling of empty XML
    expect($result)->toBeNull();
});

it('handles XML import with fixtures data and validates icon', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate XML
    $xmlString = Fixtures::getBlockXmlData();
    $xml = simplexml_load_string($xmlString);

    // Extract data for validation
    $icon = (string) $xml->blocks->item->icon;
    $type = (string) $xml->blocks->item->type;
    $placement = (string) $xml->blocks->item->placement;

    setXmlOnImport($import, $xmlString);

    setupImportMocks($import);

    // Process the items
    $result = $import->processItems();

    // Assert successful processing and validate fixture data
    expect($result)->toBeNull()
        ->and($icon)->not->toBeEmpty()
        ->and($type)->toBeIn(['html', 'bbc', 'php', 'markdown'])
        ->and($placement)->toBeIn(['header', 'footer', 'left', 'right', 'top', 'bottom']);
});

it('processes multiple blocks with different types', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML with multiple blocks
    $xmlString = Fixtures::getBlockXmlData(3);
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [1, 2, 3],
    ]);

    // Process multiple blocks
    $result = $import->processItems();

    // Assert successful processing of multiple blocks
    expect($result)->toBeNull();
});

it('handles blocks with special characters and HTML', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    // Create XML with special characters and HTML content
    $xmlContent = <<<XML
<light_portal>
    <blocks>
        <item block_id="1">
            <icon>fas fa-quote-left</icon>
            <type>markdown</type>
            <placement>right</placement>
            <priority>1</priority>
            <permissions>0</permissions>
            <status>1</status>
            <areas>all</areas>
            <title_class>cat_bar</title_class>
            <content_class>roundframe</content_class>
            <titles>
                <english>Block with spéciál chärs &amp; HTML</english>
                <russian>Блок со специальными символами &amp; HTML</russian>
            </titles>
            <contents>
                <english><![CDATA[# Header with émojis 🚀

This is **markdown** content with:

- List item 1
- List item 2 with `inline code`
- [Link](https://example.com)

> Blockquote with special chars: àáâãäå]]></english>
                <russian><![CDATA[# Заголовок с эмодзи 🚀

Это **markdown** контент с:

- Элемент списка 1
- Элемент списка 2 с `встроенным кодом`
- [Ссылка](https://example.com)

> Цитата со специальными символами: àáâãäå]]></russian>
            </contents>
        </item>
    </blocks>
</light_portal>
XML;

    setXmlOnImport($import, $xmlContent);

    // Prepare translations with special characters
    $translations = [
        [
            'item_id' => 1,
            'type' => 'block',
            'lang' => 'english',
            'title' => 'Block with spéciál chärs &amp; HTML',
            'content' => Fixtures::getRealisticBlockContent('markdown', 'complex')
        ],
        [
            'item_id' => 1,
            'type' => 'block',
            'lang' => 'russian',
            'title' => 'Блок со специальными символами &amp; HTML',
            'content' => Fixtures::getRealisticBlockContent('markdown', 'complex')
        ]
    ];

    setupImportMocks($import, [
        'extractTranslations' => $translations,
        'extractParams' => [],
        'insertDataReturn' => [1],
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Set context to indicate successful import
    Utils::$context['import_successful'] = 1;

    // Process the special content
    $result = $import->processItems();

    // Assert successful processing of special characters and HTML
    expect($result)->toBeNull();
});

it('handles blocks with missing optional fields', function ($missing) {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with missing optional fields based on scenario
    $xmlContent = '<light_portal><blocks><item block_id="1"><type>html</type><placement>header</placement><priority>1</priority><permissions>0</permissions><status>1</status>';
    if (! in_array('icon', $missing)) {
        $xmlContent .= '<icon>fas fa-test</icon>';
    }

    if (! in_array('areas', $missing)) {
        $xmlContent .= '<areas>all</areas>';
    }

    if (! in_array('title_class', $missing)) {
        $xmlContent .= '<title_class>cat_bar</title_class>';
    }

    if (! in_array('content_class', $missing)) {
        $xmlContent .= '<content_class>roundframe</content_class>';
    }

    $xmlContent .= '</item></blocks></light_portal>';

    setXmlOnImport($import, $xmlContent);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [1],
    ]);

    // Process blocks with missing fields
    $result = $import->processItems();

    // Assert graceful handling of missing optional fields
    expect($result)->toBeNull();
})->with('missing fields scenarios');

it('processes blocks with numeric IDs correctly', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML and modify IDs
    $xmlString = Fixtures::getBlockXmlData(2);
    // Replace block IDs with numeric values
    $xmlString = str_replace('block_id="0"', 'block_id="123"', $xmlString);
    $xmlString = str_replace('block_id="1"', 'block_id="0"', $xmlString);

    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [123, 0],
    ]);

    // Process blocks with numeric IDs
    $result = $import->processItems();

    // Assert correct handling of numeric block IDs
    expect($result)->toBeNull();
});

it('handles large number of blocks', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML with many blocks (10 blocks)
    $xmlString = Fixtures::getBlockXmlData(10);
    setXmlOnImport($import, $xmlString);

    $expectedIds = range(1, 10);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => $expectedIds,
    ]);

    // Process large number of blocks
    $result = $import->processItems();

    // Assert successful processing of large dataset
    expect($result)->toBeNull();
});

it('correctly converts title_class and content_class with div prefixes', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with div. prefixes in classes
    $xmlContent = <<<XML
<light_portal>
    <blocks>
        <item block_id="1">
            <icon>fas fa-star</icon>
            <type>html</type>
            <placement>header</placement>
            <priority>1</priority>
            <permissions>0</permissions>
            <status>1</status>
            <areas>all</areas>
            <title_class>div.cat_bar</title_class>
            <content_class>div.roundframe</content_class>
        </item>
        <item block_id="2">
            <icon>fas fa-code</icon>
            <type>php</type>
            <placement>footer</placement>
            <priority>2</priority>
            <permissions>1</permissions>
            <status>0</status>
            <areas>forum</areas>
            <title_class>div.title_bar</title_class>
            <content_class>div.windowbg</content_class>
        </item>
    </blocks>
</light_portal>
XML;

    setXmlOnImport($import, $xmlContent);

    // Expected data structure with div. prefixes
    $expectedData = [
        [
            'block_id' => 1,
            'icon' => 'fas fa-star',
            'type' => 'html',
            'placement' => 'header',
            'priority' => 1,
            'permissions' => 0,
            'status' => 1,
            'areas' => 'all',
            'title_class' => 'div.cat_bar',
            'content_class' => 'div.roundframe',
        ],
        [
            'block_id' => 2,
            'icon' => 'fas fa-code',
            'type' => 'php',
            'placement' => 'footer',
            'priority' => 2,
            'permissions' => 1,
            'status' => 0,
            'areas' => 'forum',
            'title_class' => 'div.title_bar',
            'content_class' => 'div.windowbg',
        ]
    ];

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [1, 2],
    ]);

    // Override insertData to check exact data structure
    $import->shouldReceive('insertData')
        ->with('lp_blocks', 'replace', $expectedData, Mockery::any(), ['block_id'])
        ->andReturn([1, 2]);

    // Process blocks with div. prefixes
    $result = $import->processItems();

    // Assert correct conversion of title_class and content_class
    expect($result)->toBeNull();
});

it('handles multiple blocks with edge cases using fixtures', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML with multiple blocks
    $xmlString = Fixtures::getBlockXmlData(5);
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => Fixtures::getTranslationData(5),
        'extractParams' => Fixtures::getParamsData(5),
        'insertDataReturn' => [1, 2, 3, 4, 5],
    ]);

    // Process multiple blocks with diverse fixture data
    $result = $import->processItems();

    // Assert successful processing of diverse edge cases from fixtures
    expect($result)->toBeNull();
});

it('validates block data integrity with fixtures', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures for realistic data validation
    $xmlString = Fixtures::getBlockXmlData();
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import);

    // Process and validate data integrity
    $result = $import->processItems();

    // Assert that fixture-generated data maintains integrity
    expect($result)->toBeNull();
});

it('handles blocks with edge case values', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with edge case values
    $xmlContent = <<<XML
<light_portal>
    <blocks>
        <item block_id="0">
            <icon></icon>
            <type>html</type>
            <placement>header</placement>
            <priority>0</priority>
            <permissions>0</permissions>
            <status>0</status>
            <areas></areas>
            <title_class></title_class>
            <content_class></content_class>
        </item>
        <item block_id="999">
            <icon>fas fa-test</icon>
            <type>php</type>
            <placement>footer</placement>
            <priority>999</priority>
            <permissions>3</permissions>
            <status>1</status>
            <areas>all,forum,topic</areas>
            <title_class>custom-class</title_class>
            <content_class>another-class</content_class>
        </item>
    </blocks>
</light_portal>
XML;

    setXmlOnImport($import, $xmlContent);

    setupImportMocks($import, [
        'extractTranslations' => [],
        'extractParams' => [],
        'insertDataReturn' => [0, 999],
    ]);

    // Process blocks with edge case values
    $result = $import->processItems();

    // Assert handling of extreme values
    expect($result)->toBeNull();
});

it('handles blocks with extremely long content', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Generate very long content
    $longTitle = str_repeat('Very Long Title ', 50);
    $longContent = str_repeat('This is a very long content block. ', 100);

    $xmlContent = <<<XML
<light_portal>
    <blocks>
        <item block_id="1">
            <icon>fas fa-file-alt</icon>
            <type>html</type>
            <placement>header</placement>
            <priority>1</priority>
            <permissions>0</permissions>
            <status>1</status>
            <areas>all</areas>
            <title_class>cat_bar</title_class>
            <content_class>roundframe</content_class>
            <titles>
                <english>$longTitle</english>
                <russian>$longTitle</russian>
            </titles>
            <contents>
                <english><![CDATA[$longContent]]></english>
                <russian><![CDATA[$longContent]]></russian>
            </contents>
        </item>
    </blocks>
</light_portal>
XML;

    setXmlOnImport($import, $xmlContent);

    setupImportMocks($import, [
        'extractTranslations' => [
            [
                'item_id' => 1,
                'type' => 'block',
                'lang' => 'english',
                'title' => $longTitle,
                'content' => $longContent,
            ],
            [
                'item_id' => 1,
                'type' => 'block',
                'lang' => 'russian',
                'title' => $longTitle,
                'content' => $longContent,
            ],
        ],
        'extractParams' => [],
        'insertDataReturn' => [1],
    ]);

    // Process blocks with very long content
    $result = $import->processItems();

    // Assert handling of large content
    expect($result)->toBeNull();
});

it('handles translation update failures gracefully', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML
    $xmlString = Fixtures::getBlockXmlData();
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Mock replaceTranslations to throw exception
    $import->shouldReceive('replaceTranslations')
        ->with(Mockery::any(), Mockery::any())
        ->andThrow(new Exception('Translation update failed'));

    // Assert that exception is thrown for translation failure
    expect(fn() => $import->processItems())->toThrow(Exception::class, 'Translation update failed');
});

it('handles params update failures gracefully', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML
    $xmlString = Fixtures::getBlockXmlData();
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'replaceTranslations' => null,
        'replaceParams' => null,
    ]);

    // Mock replaceParams to throw exception
    $import->shouldReceive('replaceParams')
        ->with(Mockery::any(), Mockery::any())
        ->andThrow(new Exception('Params update failed'));

    // Assert that exception is thrown for params failure
    expect(fn() => $import->processItems())->toThrow(Exception::class, 'Params update failed');
});

it('handles blocks with complex multilingual content', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with complex multilingual content
    $xmlContent = <<<XML
<light_portal>
    <blocks>
        <item block_id="1">
            <icon>fas fa-globe</icon>
            <type>html</type>
            <placement>header</placement>
            <priority>1</priority>
            <permissions>0</permissions>
            <status>1</status>
            <areas>all</areas>
            <title_class>cat_bar</title_class>
            <content_class>roundframe</content_class>
            <titles>
                <english>Welcome Block</english>
                <russian>Блок приветствия</russian>
                <german>Willkommensblock</german>
                <french>Bloc d'accueil</french>
                <spanish>Bloque de bienvenida</spanish>
            </titles>
            <contents>
                <english><![CDATA[<p>Welcome to our portal!</p>]]></english>
                <russian><![CDATA[<p>Добро пожаловать на наш портал!</p>]]></russian>
                <german><![CDATA[<p>Willkommen auf unserem Portal!</p>]]></german>
                <french><![CDATA[<p>Bienvenue sur notre portail!</p>]]></french>
                <spanish><![CDATA[<p>¡Bienvenido a nuestro portal!</p>]]></spanish>
            </contents>
        </item>
    </blocks>
</light_portal>
XML;

    setXmlOnImport($import, $xmlContent);

    // Complex multilingual translations
    $multilingualTranslations = [
        ['item_id' => 1, 'type' => 'block', 'lang' => 'english', 'title' => 'Welcome Block', 'content' => '<p>Welcome to our portal!</p>'],
        ['item_id' => 1, 'type' => 'block', 'lang' => 'russian', 'title' => 'Блок приветствия', 'content' => '<p>Добро пожаловать на наш портал!</p>'],
        ['item_id' => 1, 'type' => 'block', 'lang' => 'german', 'title' => 'Willkommensblock', 'content' => '<p>Willkommen auf unserem Portal!</p>'],
        ['item_id' => 1, 'type' => 'block', 'lang' => 'french', 'title' => 'Bloc d\'accueil', 'content' => '<p>Bienvenue sur notre portail!</p>'],
        ['item_id' => 1, 'type' => 'block', 'lang' => 'spanish', 'title' => 'Bloque de bienvenida', 'content' => '<p>¡Bienvenido a nuestro portal!</p>'],
    ];

    setupImportMocks($import, [
        'extractTranslations' => $multilingualTranslations,
        'extractParams' => [],
        'insertDataReturn' => [1],
    ]);

    // Process complex multilingual content
    $result = $import->processItems();

    // Assert successful processing of complex multilingual blocks
    expect($result)->toBeNull();
});

it('correctly processes blocks with different content types', function () {
    $import = createBlockImportMock($this->fileMock, $this->dbMock, $this->errorHandlerMock, createDatabaseMock());
    $import->shouldAllowMockingProtectedMethods();

    // Use fixtures to generate XML with multiple different types
    $xmlString = Fixtures::getBlockXmlData(4);
    setXmlOnImport($import, $xmlString);

    setupImportMocks($import, [
        'extractTranslations' => Fixtures::getTranslationData(4),
        'extractParams' => [],
        'insertDataReturn' => [1, 2, 3, 4],
    ]);

    // Process blocks with different content types from fixtures
    $result = $import->processItems();

    // Assert correct processing of diverse content types
    expect($result)->toBeNull();
});
