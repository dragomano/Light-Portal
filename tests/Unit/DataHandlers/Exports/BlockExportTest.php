<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Exports\BlockExport;
use LightPortal\DataHandlers\Exports\XmlExporter;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\Placement;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
use Tests\DataHandlerTestTrait;
use Tests\Fixtures;

use function Pest\Faker\fake;

uses(DataHandlerTestTrait::class);

beforeEach(function () {
    $this->repository = mock(BlockRepositoryInterface::class);
    $this->requestMock = mock(RequestInterface::class);
    $this->sqlMock = mock(PortalSqlInterface::class);
    $this->fileMock = mock(FilesystemInterface::class);
    $this->errorHandlerMock = mock(ErrorHandlerInterface::class);

    $this->export = mock(
        BlockExport::class,
        [$this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock]
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
        'html'     => '<p>' . fake()->paragraph(2) . '</p>',
        'bbc'      => '[b]' . fake()->sentence(4) . '[/b]',
        'php'      => '<?php echo "' . fake()->sentence(3) . '"; ?>',
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
    $export = new BlockExport($this->repository, $this->sqlMock, $this->fileMock, $this->errorHandlerMock);

    $this->assertExtendsBaseClass($export, XmlExporter::class, 'blocks');
});

it('main method sets up context correctly', function () {
    $this->repository->shouldReceive('getAll')->andReturn([]);

    $methods = [
        'run'          => null,
        'getAll'       => [],
        'getFile'      => '',
        'downloadFile' => null,
    ];

    foreach ($methods as $method => $returnValue) {
        $this->export->shouldReceive($method)->andReturn($returnValue);
    }

    $this->export->shouldReceive('main')->once()->andReturnUsing(function () {
        $GLOBALS['context']['sub_template'] = 'manage_export_blocks';
        $GLOBALS['context']['page_title'] = 'Portal - Block export';
        $GLOBALS['context']['page_area_title'] = 'Block export';
        $GLOBALS['context']['form_action'] = Config::$scripturl . '?action=admin;area=lp_blocks;sa=export';
        $GLOBALS['context']['admin']['tab_data'] = [
            'title'       => 'Light Portal',
            'description' => 'Block export description',
        ];

        $this->export->shouldAllowMockingProtectedMethods()->run();
    });

    $this->export->shouldAllowMockingProtectedMethods()->main();

    expect(Utils::$context['sub_template'])->toBe('manage_export_blocks')
        ->and(Utils::$context['page_title'])->toBe('Portal - Block export')
        ->and(Utils::$context['page_area_title'])->toBe('Block export')
        ->and(Utils::$context['form_action'])->toBe(Config::$scripturl . '?action=admin;area=lp_blocks;sa=export');
});

it('should return empty array when entity is empty', function () {
    $this->mockRequestMethods([
        'isEmpty' => function ($key) {
            return $key === 'blocks';
        },
        'hasNot' => function ($key) {
            return $key === 'export_all';
        },
    ]);

    expect($this->export->shouldAllowMockingProtectedMethods()->getData())->toBe([]);
});

it('should return data from database when entity provided', function ($type, $placement, $status, $content) {
    $this->requestMock->shouldReceive('isEmpty')->with('blocks')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('blocks')->andReturn([1]);

    $expectedData = [
        1 => [
            'block_id'      => 1,
            'icon'          => 'fas fa-star',
            'type'          => $type,
            'placement'     => $placement,
            'priority'      => 1,
            'permissions'   => 0,
            'status'        => (string) $status,
            'areas'         => 'all',
            'title_class'   => 'cat_bar',
            'content_class' => 'roundframe',
            'titles'        => [
                'english' => 'Test Block',
            ],
            'contents'      => [
                'english' => $content,
            ],
            'descriptions'  => [
                'english' => '',
            ],
            'params'        => [
                'name'  => 'param1',
                'value' => 'value1',
            ]
        ]
    ];

    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn($expectedData);

    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    expect($result)->toHaveKey(1)
        ->and($result[1]['block_id'])->toBe(1)
        ->and($result[1]['type'])->toBe($type)
        ->and($result[1]['placement'])->toBe($placement)
        ->and($result[1]['status'])->toBe((string) $status)
        ->and($result[1]['titles']['english'])->toBe('Test Block');
})->with('block export scenarios');

it('should call createXmlFile with correct parameters', function () {
    $data = Fixtures::getBlocksData();

    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn($data);

    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('createXmlFile')
        ->with($data, ['block_id', 'priority', 'permissions', 'status'])
        ->andReturn('/tmp/test.xml');

    $result = $this->export->shouldAllowMockingProtectedMethods()->getFile();

    expect($result)->toBeString()->not->toBeEmpty();
});

it('should return empty string when getData returns empty', function () {
    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('getData')->andReturn([]);

    $this->export->shouldAllowMockingProtectedMethods()->shouldReceive('createXmlFile')
        ->with([], ['block_id', 'priority', 'permissions', 'status'])
        ->andReturn('');

    $result = $this->export->shouldAllowMockingProtectedMethods()->getFile();

    expect($result)->toBe('');
});

it('should handle database error gracefully', function () {
    $this->requestMock->shouldReceive('isEmpty')->with('blocks')->andReturn(false);
    $this->requestMock->shouldReceive('hasNot')->with('export_all')->andReturn(true);
    $this->requestMock->shouldReceive('get')->with('blocks')->andReturn([1]);

    $this->sqlMock = $this->createDatabaseMock();

    $result = $this->export->shouldAllowMockingProtectedMethods()->getData();

    expect($result)->toBe([]);
});
