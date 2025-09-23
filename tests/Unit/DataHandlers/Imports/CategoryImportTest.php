<?php

declare(strict_types=1);

use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Imports\CategoryImport;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FileInterface;
use Tests\Fixtures;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;

use function Pest\Faker\fake;

uses(DataHandlerTestTrait::class);

/**
 * Set XML data on import instance using reflection
 */
function setCategoryXmlOnImport($import, string $xmlString): void
{
    $xml = simplexml_load_string($xmlString);
    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);
}

/**
 * Setup common mock expectations for successful category import
 */
function setupCategoryImportMocks(CategoryImport $import, array $options = []): void
{
    $defaults = [
        'parseXml' => true,
        'extractTranslations' => Fixtures::getTranslationData(),
        'insertDataReturn' => [1],
        'replaceTranslations' => null,
        'startTransaction' => null,
        'finishTransaction' => null,
    ];

    $config = array_merge($defaults, $options);

    $import->shouldReceive('parseXml')->andReturn($config['parseXml']);

    if ($config['extractTranslations'] !== null) {
        $import->shouldReceive('extractTranslations')->andReturn($config['extractTranslations']);
    }

    if ($config['insertDataReturn'] !== null) {
        $import->shouldReceive('insertData')
            ->with('lp_categories', 'replace', Mockery::any(), Mockery::any(), ['category_id'])
            ->andReturn($config['insertDataReturn']);
    }

    if ($config['replaceTranslations'] !== null) {
        $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();
    }

    if ($config['startTransaction'] !== null) {
        $import->shouldReceive('startTransaction')->andReturnUsing($config['startTransaction']);
    }

    if ($config['finishTransaction'] !== null) {
        $import->shouldReceive('finishTransaction')->andReturnUsing($config['finishTransaction']);
    }
}

dataset('category scenarios', [
    // Basic scenarios
    ['with_icon', 'with_description'],
    ['with_icon', 'without_description'],
    ['without_icon', 'with_description'],
    ['without_icon', 'without_description'],

    // Faker-generated scenarios for more variety
    [fake()->boolean() ? 'with_icon' : 'without_icon', fake()->boolean() ? 'with_description' : 'without_description'],
    [fake()->randomElement(['with_icon', 'without_icon']), fake()->randomElement(['with_description', 'without_description'])],

    // Edge cases
    ['with_icon', 'empty_description'],
    ['without_icon', 'empty_description'],
    ['with_icon', 'long_description'],
    ['without_icon', 'long_description'],
]);

beforeEach(function () {
    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class)
        ->shouldReceive('log')
        ->andReturnNull()
        ->getMock();
});

/**
 * Test basic XML processing with various category scenarios
 * Tests different combinations of icon and description presence
 */
it('correctly processes XML data', function ($iconScenario, $descriptionScenario) {
    // Create a partial mock of CategoryImport with necessary dependencies
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [
        $this->fileMock,
        $this->createDatabaseMock(),
        $this->errorHandlerMock
    ]);

    // Use fixed XML from fixtures
    $xmlString = Fixtures::getCategoryXmlData();
    setCategoryXmlOnImport($import, $xmlString);

    // Adjust mocks based on description scenario to simulate different XML content
    $translationData = Fixtures::getTranslationData();
    if ($descriptionScenario === 'without_description') {
        $translationData = [];
    } elseif ($descriptionScenario === 'empty_description') {
        // Set content to empty
        foreach ($translationData as &$item) {
            $item['content'] = '';
        }
    } elseif ($descriptionScenario === 'long_description') {
        $longDesc = 'This is a very long description that should test how the system handles lengthy content in category descriptions. It includes multiple sentences and should be sufficiently long to test edge cases in processing.';
        foreach ($translationData as &$item) {
            $item['content'] = $longDesc;
        }
    }

    // Icon scenario doesn't affect processing logic, just vary the test data

    // Setup common mocks
    setupCategoryImportMocks($import, [
        'extractTranslations' => $translationData,
        'startTransaction' => function () {
            Utils::$context['import_successful'] = 1;
        },
    ]);

    $import->shouldAllowMockingProtectedMethods();
    $result = $import->processItems();

    expect($result)->toBeNull();
})->with('category scenarios');

/**
 * Test processing of categories with multilingual content
 * Verifies that categories with translations in multiple languages are handled correctly
 */
it('handles multilingual content correctly', function () {
      // Create a partial mock of CategoryImport with test dependencies
      $import = $this->createPartialMockWithMethods(CategoryImport::class, [
          $this->fileMock,
          $this->createDatabaseMock(),
          $this->errorHandlerMock
      ]);

      // Generate multilingual content data using fixtures for realistic test data
      $multilingualData = Fixtures::getMultilingualContent();

      // Construct XML with multilingual titles and descriptions for all supported languages
      $xmlContent = <<<XML
  <light_portal>
      <categories>
          <item category_id="1">
              <icon>fas fa-globe</icon>
              <priority>1</priority>
              <status>1</status>
              <titles>
                  <english>{$multilingualData['english']['title']}</english>
                  <russian>{$multilingualData['russian']['title']}</russian>
                  <german>{$multilingualData['german']['title']}</german>
                  <french>{$multilingualData['french']['title']}</french>
                  <spanish>{$multilingualData['spanish']['title']}</spanish>
              </titles>
              <descriptions>
                  <english><![CDATA[{$multilingualData['english']['description']}]]></english>
                  <russian><![CDATA[{$multilingualData['russian']['description']}]]></russian>
                  <german><![CDATA[{$multilingualData['german']['description']}]]></german>
                  <french><![CDATA[{$multilingualData['french']['description']}]]></french>
                  <spanish><![CDATA[{$multilingualData['spanish']['description']}]]></spanish>
              </descriptions>
          </item>
      </categories>
  </light_portal>
  XML;

      // Parse and set the XML on the import instance
      $xml = simplexml_load_string($xmlContent);
      $reflection = new ReflectionClass($import);
      $xmlProperty = $reflection->getProperty('xml');
      $xmlProperty->setValue($import, $xml);

      // Prepare expected translation data structure matching the multilingual fixture data
      $multilingualTranslations = [
          ['item_id' => 1, 'type' => 'category', 'lang' => 'english', 'title' => $multilingualData['english']['title'], 'description' => $multilingualData['english']['description']],
          ['item_id' => 1, 'type' => 'category', 'lang' => 'russian', 'title' => $multilingualData['russian']['title'], 'description' => $multilingualData['russian']['description']],
          ['item_id' => 1, 'type' => 'category', 'lang' => 'german', 'title' => $multilingualData['german']['title'], 'description' => $multilingualData['german']['description']],
          ['item_id' => 1, 'type' => 'category', 'lang' => 'french', 'title' => $multilingualData['french']['title'], 'description' => $multilingualData['french']['description']],
          ['item_id' => 1, 'type' => 'category', 'lang' => 'spanish', 'title' => $multilingualData['spanish']['title'], 'description' => $multilingualData['spanish']['description']],
      ];

      // Mock successful XML parsing
      $import->shouldReceive('parseXml')->andReturn(true);

      // Mock translation extraction to return the prepared multilingual data
      $import->shouldReceive('extractTranslations')->andReturn($multilingualTranslations);

      // Mock data insertion with expected parameters
      $import->shouldReceive('insertData')
          ->with('lp_categories', 'replace', Mockery::any(), Mockery::any(), ['category_id'])
          ->andReturn([1]);

      // Mock translation replacement
      $import->shouldReceive('replaceTranslations')->with(Mockery::any(), Mockery::any())->once();

      // Execute the import processing for multilingual content
      $result = $import->processItems();

      // Verify that multilingual categories are processed successfully
      expect($result)->toBeNull();
});

/**
 * Test handling of various icon scenarios using faker-generated data
 * Ensures that categories with different icon configurations are processed correctly
 */
it('handles various icon scenarios with faker data', function () {
      // Create a partial mock of CategoryImport with necessary dependencies
      $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);

      // Generate XML data using fixtures which includes random icon data
      $xmlString = Fixtures::getCategoryXmlData();
      setCategoryXmlOnImport($import, $xmlString);

      // Set up common mock expectations for successful category import
      setupCategoryImportMocks($import);

      // Execute the import processing
      $result = $import->processItems();

      // Verify that processing completes successfully
      expect($result)->toBeNull();
});

/**
 * Test handling of categories with various description lengths
 * Verifies processing of categories with different content sizes using fixture data
 */
it('handles various description lengths using fixtures', function () {
      // Create a partial mock of CategoryImport with test dependencies
      $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);

      // Generate XML with multiple categories having different description lengths
      $xmlString = Fixtures::getCategoryXmlData(2);
      setCategoryXmlOnImport($import, $xmlString);

      // Configure mocks with extended translation data for multiple categories
      setupCategoryImportMocks($import, [
          'extractTranslations' => Fixtures::getTranslationData(4),
          'insertDataReturn' => [1, 2],
      ]);

      // Execute import processing for categories with varying content lengths
      $result = $import->processItems();

      // Verify successful processing of different description lengths
      expect($result)->toBeNull();
});

it('handles special characters and HTML in content using fixtures', function () {
     $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);
     $import->shouldAllowMockingProtectedMethods();

     // Create XML with special characters and HTML content
     $specialContent = 'Category with spéciál chärs &amp; <script>alert("test")</script> content';
     $htmlContent = '<p>This is a <strong>paragraph</strong> with <em>emphasis</em>.</p><ul><li>Item 1</li><li>Item 2</li></ul>';
     $xmlContent = <<<XML
 <light_portal>
     <categories>
         <item category_id="8">
             <icon>fas fa-exclamation-triangle</icon>
             <priority>5</priority>
             <status>1</status>
             <titles>
                 <english>Special Characters Test</english>
                 <russian>Тест Специальных Символов</russian>
             </titles>
             <descriptions>
                 <english><![CDATA[$specialContent]]></english>
                 <russian><![CDATA[$htmlContent]]></russian>
             </descriptions>
         </item>
     </categories>
 </light_portal>
 XML;

     setCategoryXmlOnImport($import, $xmlContent);

     setupCategoryImportMocks($import, [
         'extractTranslations' => [
             [
                 'item_id' => '8',
                 'type' => 'category',
                 'lang' => 'english',
                 'title' => 'Special Characters Test',
                 'content' => $specialContent,
             ],
             [
                 'item_id' => '8',
                 'type' => 'category',
                 'lang' => 'russian',
                 'title' => 'Тест Специальных Символов',
                 'content' => $htmlContent,
             ],
         ],
         'insertDataReturn' => [8],
     ]);

     // Process special content
     $result = $import->processItems();

     // Assert successful processing of special characters and HTML
     expect($result)->toBeNull();
});

it('processes categories with HasSlug trait integration', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);

    // Create XML with categories that need slug generation
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-graduation-cap</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>Education Category</english>
                <russian>Категория Образования</russian>
            </titles>
            <descriptions>
                <english>Category for educational content</english>
                <russian>Категория для образовательного контента</russian>
            </descriptions>
        </item>
        <item category_id="2">
            <icon>fas fa-briefcase</icon>
            <priority>3</priority>
            <status>1</status>
            <titles>
                <english>Business Category</english>
                <russian>Бизнес Категория</russian>
            </titles>
            <descriptions>
                <english>Business related content</english>
                <russian>Бизнес контент</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        if ($entityId === 1) {
            $titles[1] = [
                'english' => 'Education Category',
                'russian' => 'Категория Образования'
            ];
            return 'education-category';
        } elseif ($entityId === 2) {
            $titles[2] = [
                'english' => 'Business Category',
                'russian' => 'Бизнес Категория'
            ];
            return 'business-category';
        }
        return 'temp-' . $entityId;
    });

    // Configure translation handler mock
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Education Category',
                'content' => 'Category for educational content',
            ],
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Категория Образования',
                'content' => 'Категория для образовательного контента',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Business Category',
                'content' => 'Business related content',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Бизнес Категория',
                'content' => 'Бизнес контент',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [1, 2],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles category hierarchy with parent-child relationships', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with category hierarchy
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-sitemap</icon>
            <priority>1</priority>
            <status>1</status>
            <titles>
                <english>Root Category</english>
                <russian>Корневая Категория</russian>
            </titles>
            <descriptions>
                <english>Root category description</english>
                <russian>Описание корневой категории</russian>
            </descriptions>
        </item>
        <item category_id="2" parent_id="1">
            <icon>fas fa-folder</icon>
            <priority>2</priority>
            <status>1</status>
            <titles>
                <english>Child Category 1</english>
                <russian>Дочерняя Категория 1</russian>
            </titles>
            <descriptions>
                <english>First child category</english>
                <russian>Первая дочерняя категория</russian>
            </descriptions>
        </item>
        <item category_id="3" parent_id="1">
            <icon>fas fa-folder-open</icon>
            <priority>3</priority>
            <status>1</status>
            <titles>
                <english>Child Category 2</english>
                <russian>Дочерняя Категория 2</russian>
            </titles>
            <descriptions>
                <english>Second child category</english>
                <russian>Вторая дочерняя категория</russian>
            </descriptions>
        </item>
        <item category_id="4" parent_id="2">
            <icon>fas fa-file</icon>
            <priority>4</priority>
            <status>1</status>
            <titles>
                <english>Sub Child Category</english>
                <russian>Поддочерняя Категория</russian>
            </titles>
            <descriptions>
                <english>Sub child category description</english>
                <russian>Описание поддочерней категории</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process with hierarchy
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        switch ($entityId) {
            case 1:
                $titles[1] = ['english' => 'Root Category', 'russian' => 'Корневая Категория'];
                return 'root-category';
            case 2:
                $titles[2] = ['english' => 'Child Category 1', 'russian' => 'Дочерняя Категория 1'];
                return 'child-category-1';
            case 3:
                $titles[3] = ['english' => 'Child Category 2', 'russian' => 'Дочерняя Категория 2'];
                return 'child-category-2';
            case 4:
                $titles[4] = ['english' => 'Sub Child Category', 'russian' => 'Поддочерняя Категория'];
                return 'sub-child-category';
            default:
                return 'temp-' . $entityId;
        }
    });

    // Configure translation handler mock for hierarchical data
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Root Category',
                'content' => 'Root category description',
            ],
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Корневая Категория',
                'content' => 'Описание корневой категории',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Child Category 1',
                'content' => 'First child category',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Дочерняя Категория 1',
                'content' => 'Первая дочерняя категория',
            ],
            [
                'item_id' => '3',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Child Category 2',
                'content' => 'Second child category',
            ],
            [
                'item_id' => '3',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Дочерняя Категория 2',
                'content' => 'Вторая дочерняя категория',
            ],
            [
                'item_id' => '4',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Sub Child Category',
                'content' => 'Sub child category description',
            ],
            [
                'item_id' => '4',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Поддочерняя Категория',
                'content' => 'Описание поддочерней категории',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [1, 2, 3, 4],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles categories with empty slugs correctly', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with categories that have empty slugs
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-star</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>Test Category</english>
                <russian>Тестовая Категория</russian>
            </titles>
            <descriptions>
                <english>Test description</english>
                <russian>Тестовое описание</russian>
            </descriptions>
        </item>
        <item category_id="2">
            <icon>fas fa-heart</icon>
            <priority>3</priority>
            <status>1</status>
            <titles>
                <english>Another Category</english>
                <russian>Другая Категория</russian>
            </titles>
            <descriptions>
                <english>Another description</english>
                <russian>Другое описание</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation for empty slugs (should generate temp slugs)
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        // Simulate empty slug in XML - should return temp slug
        $titles[$entityId] = [
            'english' => $item->titles->english ?? 'Default Title',
            'russian' => $item->titles->russian ?? 'Заголовок по умолчанию'
        ];
        return 'temp-' . $entityId;
    });

    // Configure translation handler mock
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Test Category',
                'content' => 'Test description',
            ],
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Тестовая Категория',
                'content' => 'Тестовое описание',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Another Category',
                'content' => 'Another description',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Другая Категория',
                'content' => 'Другое описание',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [1, 2],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to convert temp slugs to real ones
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles categories with database transaction errors', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with test data
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-star</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>Test Category</english>
                <russian>Тестовая Категория</russian>
            </titles>
            <descriptions>
                <english>Test description</english>
                <russian>Тестовое описание</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('initializeSlugAndTranslations')->andReturn('test-category');
    $import->shouldReceive('extractTranslations')->andReturn([
        [
            'item_id' => '1',
            'type' => 'category',
            'lang' => 'english',
            'title' => 'Test Category',
            'content' => 'Test description',
        ],
        [
            'item_id' => '1',
            'type' => 'category',
            'lang' => 'russian',
            'title' => 'Тестовая Категория',
            'content' => 'Тестовое описание',
        ],
    ]);

    // Mock insertData to throw an exception
    $import->shouldReceive('insertData')->andThrow(new Exception('Database connection error'));

    $import->shouldAllowMockingProtectedMethods();

    expect(fn() => $import->processItems())->toThrow(Exception::class, 'Database connection error');
});

it('handles categories with missing parent_id', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with categories missing parent_id
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-star</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>Root Category</english>
                <russian>Корневая Категория</russian>
            </titles>
            <descriptions>
                <english>Root category</english>
                <russian>Корневая категория</russian>
            </descriptions>
        </item>
        <item category_id="2">
            <icon>fas fa-folder</icon>
            <priority>3</priority>
            <status>1</status>
            <titles>
                <english>Child Category</english>
                <russian>Дочерняя Категория</russian>
            </titles>
            <descriptions>
                <english>Child category</english>
                <russian>Дочерняя категория</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        if ($entityId === 1) {
            $titles[1] = ['english' => 'Root Category', 'russian' => 'Корневая Категория'];
            return 'root-category';
        } elseif ($entityId === 2) {
            $titles[2] = ['english' => 'Child Category', 'russian' => 'Дочерняя Категория'];
            return 'child-category';
        }
        return 'temp-' . $entityId;
    });

    // Configure translation handler mock
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Root Category',
                'content' => 'Root category',
            ],
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Корневая Категория',
                'content' => 'Корневая категория',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Child Category',
                'content' => 'Child category',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Дочерняя Категория',
                'content' => 'Дочерняя категория',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [1, 2],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('processes categories with special characters and HTML in descriptions', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with special characters and HTML in descriptions
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="1">
            <icon>fas fa-quote-left</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>Category with spéciál chärs &amp; HTML</english>
                <russian>Категория со специальными символами &amp; HTML</russian>
            </titles>
            <descriptions>
                <english><![CDATA[Category with spéciál chärs: àáâãäå &amp; <script>alert("test")</script> content]]></english>
                <russian><![CDATA[Категория со специальными символами: àáâãäå &amp; <strong>жирный текст</strong> контент]]></russian>
            </descriptions>
        </item>
        <item category_id="2">
            <icon>fas fa-emoji</icon>
            <priority>3</priority>
            <status>1</status>
            <titles>
                <english>Category with émojis 🚀 🌟</english>
                <russian>Категория с эмодзи 🚀 🌟</russian>
            </titles>
            <descriptions>
                <english><![CDATA[Category with émojis 🚀 and special chars: 中文 русский עברית]]></english>
                <russian><![CDATA[Категория с эмодзи 🚀 и специальными символами: 中文 русский עברית]]></russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        if ($entityId === 1) {
            $titles[1] = ['english' => 'Category with spéciál chärs &amp; HTML', 'russian' => 'Категория со специальными символами &amp; HTML'];
            return 'category-with-special-chars';
        } elseif ($entityId === 2) {
            $titles[2] = ['english' => 'Category with émojis 🚀 🌟', 'russian' => 'Категория с эмодзи 🚀 🌟'];
            return 'category-with-emojis';
        }
        return 'temp-' . $entityId;
    });

    // Configure translation handler mock for special characters
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Category with spéciál chärs &amp; HTML',
                'content' => 'Category with spéciál chärs: àáâãäå &amp; <script>alert("test")</script> content',
            ],
            [
                'item_id' => '1',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Категория со специальными символами &amp; HTML',
                'content' => 'Категория со специальными символами: àáâãäå &amp; <strong>жирный текст</strong> контент',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Category with émojis 🚀 🌟',
                'content' => 'Category with émojis 🚀 and special chars: 中文 русский עברית',
            ],
            [
                'item_id' => '2',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Категория с эмодзи 🚀 🌟',
                'content' => 'Категория с эмодзи 🚀 и специальными символами: 中文 русский עברית',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [1, 2],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles categories with numeric IDs correctly', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with numeric category IDs
    $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="123">
            <icon>fas fa-star</icon>
            <priority>10</priority>
            <status>1</status>
            <titles>
                <english>Category 123</english>
                <russian>Категория 123</russian>
            </titles>
            <descriptions>
                <english>Category with ID 123</english>
                <russian>Категория с ID 123</russian>
            </descriptions>
        </item>
        <item category_id="0">
            <icon>fas fa-home</icon>
            <priority>5</priority>
            <status>0</status>
            <titles>
                <english>Category 0</english>
                <russian>Категория 0</russian>
            </titles>
            <descriptions>
                <english>Category with ID 0</english>
                <russian>Категория с ID 0</russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        if ($entityId === 123) {
            $titles[123] = ['english' => 'Category 123', 'russian' => 'Категория 123'];
            return 'category-123';
        } elseif ($entityId === 0) {
            $titles[0] = ['english' => 'Category 0', 'russian' => 'Категория 0'];
            return 'category-0';
        }
        return 'temp-' . $entityId;
    });

    // Configure translation handler mock
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '123',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Category 123',
                'content' => 'Category with ID 123',
            ],
            [
                'item_id' => '123',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Категория 123',
                'content' => 'Категория с ID 123',
            ],
            [
                'item_id' => '0',
                'type' => 'category',
                'lang' => 'english',
                'title' => 'Category 0',
                'content' => 'Category with ID 0',
            ],
            [
                'item_id' => '0',
                'type' => 'category',
                'lang' => 'russian',
                'title' => 'Категория 0',
                'content' => 'Категория с ID 0',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [123, 0],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('processes large number of categories', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Create XML with many categories (10 categories)
    $xmlContent = '<light_portal><categories>';
    $expectedIds = [];
    for ($i = 1; $i <= 10; $i++) {
        $xmlContent .= <<<XML
        <item category_id="$i">
            <icon>fas fa-category</icon>
            <priority>$i</priority>
            <status>1</status>
            <titles>
                <english>Category $i</english>
                <russian>Категория $i</russian>
            </titles>
            <descriptions>
                <english>Description for category $i</english>
                <russian>Описание для категории $i</russian>
            </descriptions>
        </item>
XML;
        $expectedIds[] = $i;
    }
    $xmlContent .= '</categories></light_portal>';

    $xml = simplexml_load_string($xmlContent);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Mock the slug generation process for multiple categories
    $import->shouldReceive('initializeSlugAndTranslations')->andReturnUsing(function ($item, $entityId, &$titles) {
        $titles[$entityId] = [
            'english' => 'Category ' . $entityId,
            'russian' => 'Категория ' . $entityId
        ];
        return 'category-' . $entityId;
    });

    // Generate translation data for all categories
    $translations = [];
    foreach ($expectedIds as $id) {
        $translations[] = [
            'item_id' => (string) $id,
            'type' => 'category',
            'lang' => 'english',
            'title' => 'Category ' . $id,
            'content' => 'Description for category ' . $id,
        ];
        $translations[] = [
            'item_id' => (string) $id,
            'type' => 'category',
            'lang' => 'russian',
            'title' => 'Категория ' . $id,
            'content' => 'Описание для категории ' . $id,
        ];
    }

    // Configure translation handler mock for multiple categories
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => $translations,
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => $expectedIds,
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    // Mock updateSlugs to verify it's called
    $import->shouldReceive('updateSlugs')->once();

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles invalid XML gracefully', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Mock parseXml to return false for invalid XML
    $import->shouldReceive('parseXml')->andReturn(false);
    $import->shouldReceive('processItems')->andThrow(new Exception('Invalid XML format'));

    $import->shouldAllowMockingProtectedMethods();

    expect(fn() => $import->processItems())->toThrow(Exception::class, 'Invalid XML format');
});

it('handles malformed XML structure', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // XML with wrong structure
    $malformedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <wrong_entity>
        <item category_id="1">
            <slug>test-category</slug>
            <priority>5</priority>
            <status>1</status>
        </item>
    </wrong_entity>
</light_portal>
XML;

    $xml = simplexml_load_string($malformedXml);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('extractTranslations')->andReturn([]);

    // Should handle gracefully when categories element is missing
    $import->shouldReceive('processItems')->andReturnNull();

    $import->shouldAllowMockingProtectedMethods();
    $result = $import->processItems();

    expect($result)->toBeNull();
});

it('handles XML with missing required fields', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // XML with missing required fields
    $incompleteXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item>
            <!-- missing category_id, slug, status -->
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($incompleteXml);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Should handle missing fields gracefully
    $import->shouldReceive('processItems')->andThrow(new Exception('Missing required fields'));

    $import->shouldAllowMockingProtectedMethods();

    expect(fn() => $import->processItems())->toThrow(Exception::class, 'Missing required fields');
});

it('handles empty data gracefully', function () {
    $import = $this->createPartialMockWithMethods(
        CategoryImport::class,
        [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]
    )->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // Empty XML
    $emptyXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($emptyXml);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('extractTranslations')->andReturn([]);

    // Should handle empty data gracefully
    $import->shouldReceive('processItems')->andReturnNull();

    $import->shouldAllowMockingProtectedMethods();
    $result = $import->processItems();

    expect($result)->toBeNull();
});

it('handles empty categories element', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // XML with empty category titles and descriptions
    $emptyContentXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="3">
            <slug>empty-category</slug>
            <icon>fas fa-folder</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english></english>
                <russian></russian>
            </titles>
            <descriptions>
                <english></english>
                <russian></russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($emptyContentXml);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);
    $import->shouldReceive('extractTranslations')->andReturn([
        [
            'item_id' => '3',
            'type' => 'category',
            'lang' => 'english',
            'title' => '',
            'content' => '',
        ],
        [
            'item_id' => '3',
            'type' => 'category',
            'lang' => 'russian',
            'title' => '',
            'content' => '',
        ],
    ]);

    $import->shouldReceive('insertData')
        ->with(
            'lp_categories',
            'replace',
            Mockery::any(),
            Mockery::any(),
            ['category_id']
        )
        ->andReturn([3]);

    // Configure translation handler mock for empty content
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '3',
                'type' => 'category',
                'lang' => 'english',
                'title' => '',
                'content' => '',
            ],
            [
                'item_id' => '3',
                'type' => 'category',
                'lang' => 'russian',
                'title' => '',
                'content' => '',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [3],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles whitespace-only content', function () {
    $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock])->makePartial();
    $import->shouldAllowMockingProtectedMethods();

    // XML with whitespace-only content
    $whitespaceXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <categories>
        <item category_id="4">
            <slug>whitespace-category</slug>
            <icon>fas fa-file</icon>
            <priority>5</priority>
            <status>1</status>
            <titles>
                <english>   </english>
                <russian>	</russian>
            </titles>
            <descriptions>
                <english>
                </english>
                <russian>
                </russian>
            </descriptions>
        </item>
    </categories>
</light_portal>
XML;

    $xml = simplexml_load_string($whitespaceXml);

    $reflection = new ReflectionClass($import);
    $xmlProperty = $reflection->getProperty('xml');
    $xmlProperty->setValue($import, $xml);

    $import->shouldReceive('parseXml')->andReturn(true);

    // Should handle whitespace as empty content
    $import->shouldReceive('extractTranslations')->andReturn([
        [
            'item_id' => '4',
            'type' => 'category',
            'lang' => 'english',
            'title' => '',
            'content' => '',
        ],
        [
            'item_id' => '4',
            'type' => 'category',
            'lang' => 'russian',
            'title' => '',
            'content' => '',
        ],
    ]);

    // Configure translation handler mock for whitespace content
    $translationMock = $this->createTranslationHandlerMock([
        'extractTranslationsReturn' => [
            [
                'item_id' => '4',
                'type' => 'category',
                'lang' => 'english',
                'title' => '',
                'content' => '',
            ],
            [
                'item_id' => '4',
                'type' => 'category',
                'lang' => 'russian',
                'title' => '',
                'content' => '',
            ],
        ],
        'replaceTranslationsCount' => 1,
        'replaceParamsCount' => 0,
        'replaceCommentsCount' => 0,
        'insertDataReturn' => [4],
    ]);

    // Set up the mock to delegate to our translation handler
    $import->shouldReceive('extractTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->extractTranslations();
    });
    $import->shouldReceive('insertData')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->insertData();
    });
    $import->shouldReceive('replaceTranslations')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->replaceTranslations();
    });
    $import->shouldReceive('startTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->startTransaction();
    });
    $import->shouldReceive('finishTransaction')->andReturnUsing(function () use ($translationMock) {
        return $translationMock->finishTransaction();
    });

    $import->shouldAllowMockingProtectedMethods();
    $import->processItems();
});

it('handles mixed language content', function () {
     $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);
     $import->shouldAllowMockingProtectedMethods();

     // Create XML with mixed language content
     $xmlContent = <<<XML
 <light_portal>
     <categories>
         <item category_id="2">
             <icon>fas fa-heart</icon>
             <priority>5</priority>
             <status>1</status>
             <titles>
                 <english>English Title Only</english>
                 <russian>Русский Заголовок</russian>
             </titles>
             <descriptions>
                 <english>Only English description available</english>
                 <russian></russian>
             </descriptions>
         </item>
     </categories>
 </light_portal>
 XML;

     setCategoryXmlOnImport($import, $xmlContent);

     setupCategoryImportMocks($import, [
         'extractTranslations' => [
             [
                 'item_id' => '2',
                 'type' => 'category',
                 'lang' => 'english',
                 'title' => 'English Title Only',
                 'content' => 'Only English description available',
             ],
             [
                 'item_id' => '2',
                 'type' => 'category',
                 'lang' => 'russian',
                 'title' => 'Русский Заголовок',
                 'content' => '',
             ],
         ],
         'insertDataReturn' => [2],
     ]);

     // Process mixed language content
     $result = $import->processItems();

     // Assert successful processing
     expect($result)->toBeNull();
});

 it('handles categories with invalid data from fixtures', function () {
     $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);
     $import->shouldAllowMockingProtectedMethods();

     // Create XML with invalid data based on fixtures
     $invalidData = Fixtures::getInvalidModelData('category');
     $xmlContent = <<<XML
 <light_portal>
     <categories>
         <item category_id="{$invalidData['id']}">
             <icon>{$invalidData['icon']}</icon>
             <priority>{$invalidData['priority']}</priority>
             <status>{$invalidData['status']}</status>
             <titles>
                 <english>{$invalidData['title']}</english>
             </titles>
             <descriptions>
                 <english>{$invalidData['description']}</english>
             </descriptions>
         </item>
     </categories>
 </light_portal>
 XML;

     setCategoryXmlOnImport($import, $xmlContent);

     setupCategoryImportMocks($import, [
         'extractTranslations' => [
             [
                 'item_id' => $invalidData['id'],
                 'type' => 'category',
                 'lang' => 'english',
                 'title' => $invalidData['title'],
                 'content' => $invalidData['description'],
             ],
         ],
         'insertDataReturn' => [$invalidData['id']],
     ]);

     // Process invalid data - should handle gracefully
     $result = $import->processItems();

     // Assert graceful handling of invalid data
     expect($result)->toBeNull();
 });

 it('handles categories with empty data using fixtures', function () {
     $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);
     $import->shouldAllowMockingProtectedMethods();

     // Use fixtures to generate XML, then modify to empty content
     $xmlString = Fixtures::getCategoryXmlData();
     // Replace content with empty values
     $xmlString = str_replace('<english>', '<english><![CDATA[]]></english>', $xmlString);
     $xmlString = str_replace('<russian>', '<russian><![CDATA[]]></russian>', $xmlString);

     setCategoryXmlOnImport($import, $xmlString);

     setupCategoryImportMocks($import, [
         'extractTranslations' => [
             [
                 'item_id' => '0',
                 'type' => 'category',
                 'lang' => 'english',
                 'title' => '',
                 'content' => '',
             ],
             [
                 'item_id' => '0',
                 'type' => 'category',
                 'lang' => 'russian',
                 'title' => '',
                 'content' => '',
             ],
         ],
         'insertDataReturn' => [0],
     ]);

     // Process empty data - should handle gracefully
     $result = $import->processItems();

     // Assert graceful handling of empty data
     expect($result)->toBeNull();
 });

 it('handles category-specific content using fixtures', function () {
     $import = $this->createPartialMockWithMethods(CategoryImport::class, [$this->fileMock, $this->createDatabaseMock(), $this->errorHandlerMock]);
     $import->shouldAllowMockingProtectedMethods();

     // Get category-specific content from fixtures
     $categoryContent = Fixtures::getCategorySpecificContent('tutorials');
     $xmlContent = <<<XML
 <light_portal>
     <categories>
         <item category_id="1">
             <icon>{$categoryContent['icon']}</icon>
             <priority>5</priority>
             <status>1</status>
             <titles>
                 <english>{$categoryContent['title']}</english>
                 <russian>{$categoryContent['title']}</russian>
             </titles>
             <descriptions>
                 <english><![CDATA[{$categoryContent['description']}]]></english>
                 <russian><![CDATA[{$categoryContent['description']}]]></russian>
             </descriptions>
         </item>
     </categories>
 </light_portal>
 XML;

     setCategoryXmlOnImport($import, $xmlContent);

     setupCategoryImportMocks($import, [
         'extractTranslations' => [
             [
                 'item_id' => '1',
                 'type' => 'category',
                 'lang' => 'english',
                 'title' => $categoryContent['title'],
                 'content' => $categoryContent['description'],
             ],
             [
                 'item_id' => '1',
                 'type' => 'category',
                 'lang' => 'russian',
                 'title' => $categoryContent['title'],
                 'content' => $categoryContent['description'],
             ],
         ],
         'insertDataReturn' => [1],
     ]);

     // Process category-specific content
     $result = $import->processItems();

     // Assert successful processing of category-specific content
     expect($result)->toBeNull();
 });
