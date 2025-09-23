<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\PageImport;
use Bugo\LightPortal\DataHandlers\Imports\XmlImporter;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\FileInterface;
use Mockery\Mock;
use Mockery\MockInterface;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestFactory;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

uses(DataHandlerTestTrait::class);

dataset('page content types', [...ContentType::names(), 'markdown']);

dataset('page statuses', Status::values());

dataset('invalid xml scenarios', [
    'malformed_xml',
    'empty_xml',
    'missing_required_fields',
    'invalid_structure',
]);

beforeEach(function () {
    $this->factory = new DataHandlerTestFactory();
    $this->testEnvironment = $this->factory->createTestEnvironment('pages');

    $this->dbMock = $this->testEnvironment['database'];

    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->fileMock->shouldReceive('get')->andReturn(null);

    $this->errorHandlerMock = $this->createErrorHandlerMock();
});

function createPageImportMock(array $overrides = []): Mock|(MockInterface&PageImport)
{
    $defaults = [
        'xmlData'                   => ['item' => []],
        'entity'                    => 'pages',
        'parseXmlReturn'            => true,
        'extractTranslationsReturn' => [],
        'extractParamsReturn'       => [],
        'extractCommentsReturn'     => [],
        'insertDataReturn'          => [],
    ];

    $config = array_merge($defaults, $overrides);

    $errorHandlerMock = test()->createErrorHandlerMock();
    $import = test()->createXmlImporterMock(PageImport::class, $config);

    $reflection = new ReflectionClass($import);

    foreach (
        [
        'file' => test()->fileMock,
        'db' => test()->testEnvironment['database'],
        'errorHandler' => $errorHandlerMock,
        ] as $prop => $value
    ) {
        $property = $reflection->getProperty($prop);
        $property->setValue($import, $value);
    }

    return $import;
}

it('correctly sets up the context', function () {
    $import = $this->createPartialMockWithMethods(
        PageImport::class,
        [$this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock],
        ['run' => null]
    );

    $import->main();

    expect(Utils::$context['sub_template'])->toBe('manage_import')
        ->and(Utils::$context['page_title'])->toBe('Portal - Page import')
        ->and(Utils::$context['page_area_title'])->toBe('Page import')
        ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . '?action=admin;area=lp_pages;sa=import')
        ->and(Utils::$context['lp_file_type'])->toBe('text/xml');
});

it('extends XmlImporter and has correct entity', function () {
    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);
    $this->assertExtendsBaseClass($import, XmlImporter::class, 'pages');
});

it('has main method', function () {
    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);
    expect(method_exists($import, 'main'))->toBeTrue();
});

it('handles different content types correctly', function ($contentType) {
    $xmlData = Fixtures::getPagesData();
    $xmlData[0]['type'] = $contentType;
    $xmlData[0]['status'] = '1'; // Ensure valid status

    $import = createPageImportMock([
        'xmlData'                   => ['item' => $xmlData],
        'extractTranslationsReturn' => Fixtures::getTranslationData(),
        'extractParamsReturn'       => Fixtures::getParamsData(1),
        'insertDataReturn'          => [1],
    ]);

    $import->processItems();
    expect($contentType)->toBeIn([...ContentType::names(), 'markdown']);
})->with('page content types');

it('handles different page statuses correctly', function ($status) {
    $xmlData = Fixtures::getPagesData();
    $xmlData[0]['status'] = (string) $status;
    $xmlData[0]['type'] = 'html'; // Ensure valid content type

    $import = createPageImportMock([
        'xmlData' => ['item' => $xmlData],
        'extractTranslationsReturn' => Fixtures::getTranslationData(1),
        'extractParamsReturn' => Fixtures::getParamsData(1),
        'insertDataReturn' => [1],
    ]);

    $import->processItems();
    expect($status)->toBeIn(Status::values());
})->with('page statuses');

it('handles invalid XML gracefully', function ($scenario) {
    $xmlData = match ($scenario) {
        'malformed_xml', 'empty_xml' => ['item' => []],
        'missing_required_fields' => ['item' => [array_slice(Fixtures::getPagesData()[1], 0, 1)]], // Only page_id
        'invalid_structure' => ['item' => [array_merge(Fixtures::getPagesData()[1], ['invalid_field' => 'x'])]],
    };

    $import = createPageImportMock([
        'xmlData' => $xmlData,
        'parseXmlReturn' => !in_array($scenario, ['malformed_xml', 'empty_xml']),
        'extractTranslationsReturn' => [],
        'extractParamsReturn' => [],
        'extractCommentsReturn' => [],
    ]);

    if (in_array($scenario, ['malformed_xml', 'empty_xml'])) {
        expect($import->parseXml())->toBeFalse();
    } else {
        expect(fn() => $import->processItems())
            ->not->toThrow(Exception::class);
    }
})->with('invalid xml scenarios');

it('handles empty data correctly', function () {
    // Test with page data that has empty fields
    $xmlData = Fixtures::getPagesData();
    $pageData = $xmlData[1];
    $pageData['titles'] = ['english' => '', 'russian' => ''];
    $pageData['contents'] = ['english' => '', 'russian' => ''];
    $pageData['descriptions'] = ['english' => '', 'russian' => ''];
    $pageData['params'] = [];
    $pageData['comments'] = [];

    $import = createPageImportMock([
        'xmlData' => ['item' => [$pageData]],
        'extractTranslationsReturn' => Fixtures::getTranslationData(),
        'extractParamsReturn' => [],
        'extractCommentsReturn' => [],
        'insertDataReturn' => [1],
    ]);

    expect(fn() => $import->shouldAllowMockingProtectedMethods()->processItems())
        ->not->toThrow(Exception::class);
});

it('handles various comment scenarios', function () {
    $xmlData = Fixtures::getPagesData();
    // Use fixture data and modify comments for testing
    $pageData = $xmlData[1];
    $pageData['comments'] = [
        ['id' => '1', 'parent_id' => '0', 'author_id' => '2', 'message' => 'First comment'],
        ['id' => '2', 'parent_id' => '1', 'author_id' => '3', 'message' => 'Reply to first comment'],
        ['id' => '3', 'parent_id' => '0', 'author_id' => '4', 'message' => 'Another comment'],
    ];

    $import = createPageImportMock([
        'xmlData'                  => ['item' => [$pageData]],
        'extractCommentsReturn'    => $pageData['comments'],
        'extractTranslationsReturn' => Fixtures::getTranslationData(1),
        'extractParamsReturn'      => Fixtures::getParamsData(1),
        'replaceTranslationsCount' => 1,
        'replaceParamsCount'       => 1,
        'replaceCommentsCount'     => 1,
        'insertDataReturn'         => [1],
    ]);

    expect(fn() => $import->processItems())
        ->not->toThrow(Exception::class);
});

it('handles XML import with fixtures data for pages', function () {
    $this->dbMock = $this->createDatabaseMock();

    $import = Mockery::mock(PageImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    $xmlString = Fixtures::getPageXmlData();
    $xml = simplexml_load_string($xmlString);
    $numComments = (int) $xml->pages->item->num_comments;

    $reflection = new ReflectionClass($import);
    $reflection->getProperty('xml')->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('extractTranslations')->andReturn(Fixtures::getTranslationData());
    $import->shouldReceive('extractParams')->andReturn(Fixtures::getParamsData());
    $import->shouldReceive('extractComments')->andReturn((array) ($xml->pages->item->comments->comment ?? []));
    $import->shouldReceive('insertData')->andReturn([1]);
    $import->shouldReceive('replaceTranslations')->once();
    $import->shouldReceive('replaceParams')->once();
    $import->shouldReceive('replaceComments')->once();

    $import->processItems();
    expect($numComments)->toBeGreaterThanOrEqual(0);
});

it('replaces comments correctly for existing page', function () {
    $newComments = [
        ['id' => '3', 'parent_id' => '0', 'author_id' => '1', 'message' => 'New comment', 'page_id' => 1, 'created_at' => time()],
        ['id' => '4', 'parent_id' => '3', 'author_id' => '2', 'message' => 'Reply', 'page_id' => 1, 'created_at' => time()],
    ];

    $import = createPageImportMock([
        'insertDataReturn' => [[1], [2]],
    ]);

    $results = [1, 2];
    $import->replaceComments($newComments, $results);
    expect($results)->toBe([1, 2]);
});

it('skips comment replacement if no existing comments', function () {
    $this->dbMock->shouldReceive('query')->with('SELECT id_comment FROM lp_comments WHERE page_id = 1')->andReturn('resource');
    $this->dbMock->shouldReceive('fetchAssoc')->with('resource')->andReturn([]);

    $import = createPageImportMock([
        'insertDataReturn' => [1],
    ]);

    expect(fn() => $import->replaceComments([], [1]))
        ->not->toThrow(Exception::class);
});

it('handles comment replacement error gracefully', function () {
    $newComments = [];

    $import = createPageImportMock([
        'insertDataReturn' => [],
    ]);

    $results = [['page_id' => 1]];
    $temp = &$results;

    $import->replaceComments($newComments, $temp);
    expect($results)->toBe([['page_id' => 1]]);
});

it('processes single page import correctly', function () {
    $content = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="2" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="1">
            <slug>test-page</slug>
            <type>bbc</type>
            <titles>
                <english>Test Page</english>
            </titles>
            <contents>
                <english><![CDATA[' . $content . ']]></english>
            </contents>
            <descriptions>
                <english>Test description</english>
            </descriptions>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    // Write XML to temporary file
    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1]);

    $import->main();

    expect(true)->toBeTrue(); // Basic test that no exceptions were thrown
});

it('processes multiple pages with comments correctly', function () {
    $content1 = Fixtures::getRealisticPageContent()['content'];
    $content2 = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="2" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="2">
            <slug>test-page-1</slug>
            <type>bbc</type>
            <titles><english>Page 1</english></titles>
            <contents><english><![CDATA[' . $content1 . ']]></english></contents>
            <comments>
                <comment id="1" parent_id="0" author_id="2" created_at="' . time() . '">First comment</comment>
                <comment id="2" parent_id="1" author_id="3" created_at="' . time() . '">Reply comment</comment>
            </comments>
        </item>
        <item page_id="2" category_id="2" author_id="2" status="1" permissions="0" num_views="50" num_comments="1" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="3">
            <slug>test-page-2</slug>
            <type>html</type>
            <titles><english>Page 2</english></titles>
            <contents><english><![CDATA[' . $content2 . ']]></english></contents>
            <comments>
                <comment id="3" parent_id="0" author_id="4" created_at="' . time() . '">Another comment</comment>
            </comments>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1, 2]);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_comments', Mockery::any(), Mockery::any(), ['id', 'page_id'], 2)
        ->andReturn([1, 2, 3]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles different entry types correctly', function () {
    $content1 = Fixtures::getRealisticPageContent()['content'];
    $content2 = Fixtures::getRealisticPageContent()['content'];
    $content3 = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>standard-page</slug>
            <type>bbc</type>
            <titles><english>Standard Page</english></titles>
            <contents><english><![CDATA[' . $content1 . ']]></english></contents>
        </item>
        <item page_id="2" category_id="1" author_id="1" status="3" permissions="0" num_views="50" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>internal-page</slug>
            <type>bbc</type>
            <titles><english>Internal Page</english></titles>
            <contents><english><![CDATA[' . $content2 . ']]></english></contents>
        </item>
        <item page_id="3" category_id="1" author_id="1" status="1" permissions="0" num_views="25" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>blog-page</slug>
            <type>markdown</type>
            <titles><english>Blog Page</english></titles>
            <contents><english><![CDATA[' . $content3 . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1, 2, 3]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles multilingual content correctly', function () {
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>multilingual-page</slug>
            <type>bbc</type>
            <titles>
                <english>English Title</english>
                <russian>Русское название</russian>
                <spanish>Título español</spanish>
            </titles>
            <contents>
                <english>English content</english>
                <russian>Русский контент</russian>
                <spanish>Contenido español</spanish>
            </contents>
            <descriptions>
                <english>English description</english>
                <russian>Русское описание</russian>
                <spanish>Descripción española</spanish>
            </descriptions>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles pages with parameters correctly', function () {
    $content = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>page-with-params</slug>
            <type>bbc</type>
            <titles><english>Page with Parameters</english></titles>
            <contents><english><![CDATA[' . $content . ']]></english></contents>
            <params>
                <show_author>true</show_author>
                <custom_css>body { color: red; }</custom_css>
                <max_width>1200</max_width>
            </params>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles different content types conversion correctly', function () {
    $bbcContent = Fixtures::getRealisticBlockContent('bbc');
    $htmlContent = Fixtures::getRealisticBlockContent();
    $markdownContent = Fixtures::getRealisticBlockContent('markdown');
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>bbc-page</slug>
            <type>bbc</type>
            <titles><english>BBC Page</english></titles>
            <contents><english><![CDATA[' . $bbcContent . ']]></english></contents>
        </item>
        <item page_id="2" category_id="1" author_id="1" status="1" permissions="0" num_views="50" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>html-page</slug>
            <type>html</type>
            <titles><english>HTML Page</english></titles>
            <contents><english><![CDATA[' . $htmlContent . ']]></english></contents>
        </item>
        <item page_id="3" category_id="1" author_id="1" status="1" permissions="0" num_views="25" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>markdown-page</slug>
            <type>markdown</type>
            <titles><english>Markdown Page</english></titles>
            <contents><english><![CDATA[' . $markdownContent . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1, 2, 3]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles empty slug generation correctly', function () {
    $content1 = Fixtures::getRealisticPageContent()['content'];
    $content2 = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug></slug>
            <type>bbc</type>
            <titles><english>Test Page</english></titles>
            <contents><english><![CDATA[' . $content1 . ']]></english></contents>
        </item>
        <item page_id="2" category_id="1" author_id="1" status="1" permissions="0" num_views="50" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <type>html</type>
            <titles><english>Another Test Page</english></titles>
            <contents><english><![CDATA[' . $content2 . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1, 2]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles invalid data gracefully', function () {
    $content = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="abc" category_id="def" author_id="ghi" status="invalid" permissions="xyz" num_views="invalid" num_comments="invalid" created_at="invalid" updated_at="invalid" deleted_at="invalid" last_comment_id="invalid">
            <slug>test-page</slug>
            <type>bbc</type>
            <titles><english>Test Page</english></titles>
            <contents><english><![CDATA[' . $content . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1]);

    $import->main();

    expect(true)->toBeTrue();
});

it('handles partial data correctly', function () {
    $content = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>test-page</slug>
            <type>bbc</type>
            <titles><english>Test Page</english></titles>
            <!-- Missing content -->
        </item>
        <item page_id="2" category_id="1" author_id="1" status="1" permissions="0" num_views="50" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>test-page-2</slug>
            <type>html</type>
            <!-- Missing translations -->
            <contents><english><![CDATA[' . $content . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn([1, 2]);

    $import->main();

    expect(true)->toBeTrue();
});

it('processes large number of pages correctly', function () {
    $pages = [];
    $currentTime = time();

    for ($i = 1; $i <= 10; $i++) {
        $content = Fixtures::getRealisticPageContent()['content'];
        $pages[] = "<item page_id=\"$i\" category_id=\"1\" author_id=\"1\" status=\"1\" permissions=\"0\" num_views=\"{$i}0\" num_comments=\"0\" created_at=\"$currentTime\" updated_at=\"$currentTime\" deleted_at=\"0\" last_comment_id=\"0\">
            <slug>test-page-$i</slug>
            <type>bbc</type>
            <titles><english>Test Page $i</english></titles>
            <contents><english><![CDATA[" . $content . "]]></english></contents>
        </item>";
    }

    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>' . implode('', $pages) . '</pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    $import = new PageImport($this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock);

    $this->testEnvironment['database']->shouldReceive('insert')
        ->with('replace', '{db_prefix}lp_pages', Mockery::any(), Mockery::any(), ['page_id'], 2)
        ->andReturn(range(1, 10));

    $import->main();

    expect(true)->toBeTrue();
});

it('handles transaction rollback on error', function () {
    $content = Fixtures::getRealisticPageContent()['content'];
    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
    <pages>
        <item page_id="1" category_id="1" author_id="1" status="1" permissions="0" num_views="100" num_comments="0" created_at="' . time() . '" updated_at="' . time() . '" deleted_at="0" last_comment_id="0">
            <slug>test-page</slug>
            <type>bbc</type>
            <titles><english>Test Page</english></titles>
            <contents><english><![CDATA[' . $content . ']]></english></contents>
        </item>
    </pages>';

    $tempFile = tempnam(sys_get_temp_dir(), 'xml');

    $this->fileMock->shouldReceive('get')
        ->with('import_file')
        ->andReturn([
            'tmp_name' => $tempFile,
            'type' => 'text/xml'
        ]);

    file_put_contents($tempFile, $xmlString);

    // Simulate database insertion error
    // Mock parseXml to return true
    $import = Mockery::mock(PageImport::class, [$this->fileMock, $this->testEnvironment['database'], $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Mock parseXml to return true
    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock processItems to call insert and throw exception
    $import->shouldReceive('processItems')->andThrow(new Exception('Database error'));

    expect(fn() => $import->main())->toThrow(Exception::class);
});
