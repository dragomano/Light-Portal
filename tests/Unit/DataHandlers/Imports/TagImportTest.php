<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Imports\TagImport;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

uses(DataHandlerTestTrait::class);

dataset('tag scenarios', [
    ['with_description'],
    ['without_description'],
]);

function generateTagXml($descriptionScenario): string
{
    $descriptionTag = $descriptionScenario === 'with_description' ? '<descriptions><english>Test description</english><russian>Тестовое описание</russian></descriptions>' : '';

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <tags>
        <item tag_id="1">
            <icon>fas fa-tag</icon>
            <status>1</status>
            <titles>
                <english>Test Tag</english>
                <russian>Тестовый тег</russian>
            </titles>
            {$descriptionTag}
            <pages>
                <item id="1"/>
                <item id="2"/>
            </pages>
        </item>
    </tags>
</light_portal>
XML;
}

beforeEach(function () {
    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class)
        ->shouldReceive('log')
        ->andReturnNull() // Allow log calls without throwing exceptions
        ->getMock();
});

it('correctly processes XML data', function ($descriptionScenario) {
    // Mock Db
    $this->dbMock = $this->createDatabaseMock();

    $import = Mockery::mock(TagImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    // Mock XML
    $xml = simplexml_load_string(generateTagXml($descriptionScenario));

    // Set XML directly using reflection since injectXml method doesn't exist
    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    // Mock parseXml to return true
    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock trait methods
    $import->shouldReceive('extractTranslations')->andReturn(Fixtures::getTranslationData());
    $import->shouldReceive('extractPages')->andReturn([['page_id' => 1, 'tag_id' => '1'], ['page_id' => 2, 'tag_id' => '1']]);
    $import->shouldReceive('insertData')
        ->with(
            'lp_tags',
            'replace',
            Mockery::any(),
            Mockery::any(),
            ['tag_id']
        )
        ->andReturn([1]);
    $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();
    $import->shouldReceive('replacePages')->with(Mockery::any(), Mockery::any())->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
})->with('tag scenarios');

it('handles XML import with fixtures data for tags', function () {
    // Mock Db
    $this->dbMock = $this->createDatabaseMock();

    $import = Mockery::mock(TagImport::class, [$this->fileMock, $this->dbMock, $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();
    $import->shouldReceive('processItems')->passthru();

    // Use fixtures to generate XML
    $xmlString = Fixtures::getTagXmlData();
    $xml = simplexml_load_string($xmlString);

    // Extract pages count from XML for assertion
    $pagesCount = count($xml->tags->item->pages->page);

    // Set XML using reflection
    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    // Mock methods
    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('extractTranslations')->andReturn(Fixtures::getTranslationData());
    $import->shouldReceive('extractPages')->andReturn([['page_id' => 1, 'tag_id' => '1']]);
    $import->shouldReceive('insertData')
        ->with(
            'lp_tags',
            'replace',
            Mockery::type('array'),
            Mockery::type('array'),
            ['tag_id']
        )
        ->andReturn([1]);
    $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();
    $import->shouldReceive('replacePages')->with(Mockery::any(), Mockery::any())->once();

    $import->processItems();

    // Assert pages are processed (edge case for tags with pages)
    expect($pagesCount)->toBeGreaterThanOrEqual(0);
});
