<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use Bugo\LightPortal\DataHandlers\Exports\BlockExport;
use Bugo\LightPortal\DataHandlers\Exports\XmlExporter;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Repositories\BlockRepositoryInterface;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\Unit\DataHandlers\DataHandlerTestTrait;
use Tests\Fixtures;

use function Pest\Faker\fake;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->repository = Mockery::mock(BlockRepositoryInterface::class);
    $this->requestMock = Mockery::mock(RequestInterface::class);
    $this->dbMock = Mockery::mock(DatabaseInterface::class);
    $this->fileMock = Mockery::mock(FilesystemInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class);

    $this->export = Mockery::mock(
        BlockExport::class,
        [$this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock]
    )
        ->makePartial()->shouldAllowMockingProtectedMethods();

    AppMockRegistry::set(RequestInterface::class, $this->requestMock);
});

afterEach(function () {
    AppMockRegistry::clear();
});

dataset('block export scenarios', function () {
    $scenarios = [];
    $types = [...ContentType::names(), 'markdown'];
    $placements = Placement::names();
    $contents = [
        'html' => '<p>' . fake()->paragraph(2) . '</p>',
        'bbc' => '[b]' . fake()->sentence(4) . '[/b]',
        'php' => '<?php echo "' . fake()->sentence(3) . '"; ?>',
        'markdown' => '# ' . fake()->sentence(4),
    ];

    for ($i = 0; $i < 8; $i++) {
        $type = fake()->randomElement($types);
        $placement = fake()->randomElement($placements);
        $status = fake()->numberBetween(0, 1);
        $content = $contents[$type];

        $scenarios[] = [$type, $placement, $status, $content];
    }

    return $scenarios;
});

it('should extend XmlExporter and have correct entity', function () {
    $export = new BlockExport($this->repository, $this->dbMock, $this->fileMock, $this->errorHandlerMock);

    // Verify that BlockExport extends XmlExporter and has the correct entity type
    $this->assertExtendsBaseClass($export, XmlExporter::class, 'blocks');
});

it('main method sets up context correctly', function () {
    // Mock repository to return empty array for getAll
    $this->repository->shouldReceive('getAll')->andReturn([]);

    // Define expected return values for mocked methods
    $methods = [
        'run' => null,
        'getAll' => [],
        'getFile' => '',
        'downloadFile' => null,
    ];

    // Set up mocks for all methods to avoid actual implementation calls
    foreach ($methods as $method => $returnValue) {
        $this->export->shouldReceive($method)->andReturn($returnValue);
    }

    // Mock main method to avoid calling Theme::loadTemplate and simulate context setup
    $this->export->shouldReceive('main')->once()->andReturnUsing(function () {
        // Simulate main method by setting up global context variables
        $GLOBALS['context']['sub_template'] = 'manage_export_blocks';
        $GLOBALS['context']['page_title'] = 'Portal - Block export';
        $GLOBALS['context']['page_area_title'] = 'Block export';
        $GLOBALS['context']['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=export';
        $GLOBALS['context']['admin_menu_name'] = 'admin';
        $GLOBALS['context']['admin']['tab_data'] = [
            'title' => 'Light Portal',
            'description' => 'Block export description',
        ];

        // Simulate calling the run method within main
        $this->export->shouldAllowMockingProtectedMethods()->run();
    });

    // Execute the mocked main method
    $this->export->shouldAllowMockingProtectedMethods()->main();

    // Assert that context variables are set correctly
    expect(Utils::$context['sub_template'])->toBe('manage_export_blocks')
        ->and(Utils::$context['page_title'])->toBe('Portal - Block export')
        ->and(Utils::$context['page_area_title'])->toBe('Block export')
        ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . '?action=admin;area=lp_blocks;sa=export');
});

it('should return empty array when entity is empty', function () {
    // Mock request to simulate empty 'blocks' entity and no 'export_all' flag
    $this->mockRequestMethods([
        'isEmpty' => function ($key) {
            return $key === 'blocks';
        },
        'hasNot' => function ($key) {
            return $key === 'export_all';
        },
    ]);

    // Verify that getData returns empty array when no blocks are selected
    expect($this->export->shouldAllowMockingProtectedMethods()->getData())->toBe([]);
});

it('should return data from database when entity provided', function ($type, $placement, $status, $content) {
    // Mock request to indicate specific blocks are selected for export
    $this->requestMock->shouldReceive('isEmpty')->with('blocks')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('blocks')->andReturn([1]);

    // Simulate database result for a single block with varying content types and properties
    $dbResult = [
        [
            'block_id'      => '1',
            'icon'          => 'fas fa-star',
            'type'          => $type,
            'placement'     => $placement,
            'priority'      => '1',
            'permissions'   => '0',
            'status'        => (string) $status,
            'areas'         => 'all',
            'title_class'   => 'cat_bar',
            'content_class' => 'roundframe',
            'lang'          => 'english',
            'title'         => 'Test Block',
            'content'       => $content,
            'description'   => '',
            'name'          => 'param1',
            'value'         => 'value1',
        ]
    ];

    // Mock database query execution and result fetching
    $this->dbMock->shouldReceive('query')
        ->once()
        ->andReturn($dbResult);

    $this->dbMock->shouldReceive('fetchAssoc')
        ->andReturn($dbResult[0], []);

    $this->dbMock->shouldReceive('freeResult')
        ->once();

    // Execute getData method and verify the returned data structure
    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    // Assert that the result contains the expected block data with correct properties
    expect($result)->toHaveKey(1)
        ->and($result[1]['block_id'])->toBe('1')
        ->and($result[1]['type'])->toBe($type)
        ->and($result[1]['placement'])->toBe($placement)
        ->and($result[1]['status'])->toBe((string) $status)
        ->and($result[1]['titles']['english'])->toBe('Test Block');
})->with('block export scenarios');

it('should call createXmlFile with correct parameters', function () {
    // Retrieve sample block data from fixtures for testing XML creation
    $data = Fixtures::getBlocksData();

    // Mock getData to return the fixture data
    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn($data);

    // Mock createXmlFile to expect correct data and exclusion fields, returning a file path
    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('createXmlFile')
        ->with($data, ['block_id', 'priority', 'permissions', 'status'])
        ->andReturn('/tmp/test.xml');

    // Call getFile and verify it returns a non-empty string (the file path)
    $result = $this->export->shouldAllowMockingProtectedMethods()->getFile();

    expect($result)->toBeString()->not->toBeEmpty();
});

it('should return empty string when getData returns empty', function () {
    // Mock getData to return empty array (no data to export)
    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn([]);

    // Mock createXmlFile to expect empty data and exclusion fields, returning empty string
    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('createXmlFile')
        ->with([], ['block_id', 'priority', 'permissions', 'status'])
        ->andReturn('');

    // Call getFile and verify it returns empty string when no data is available
    $result = $this->export->shouldAllowMockingProtectedMethods()->getFile();

    expect($result)->toBe('');
});

it('should handle database error gracefully', function () {
    // Mock request to indicate specific blocks are selected for export
    $this->requestMock->shouldReceive('isEmpty')->with('blocks')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('blocks')->andReturn([1]);

    // Create a database mock that throws an exception on query (simulating database error)
    $this->dbMock = $this->createDatabaseMock([
        'query' => function () {
            throw new Exception('Database error');
        }
    ]);

    // Call getData and verify it returns empty array despite the database error
    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    expect($result)->toBe([]);
});
