<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Articles\Queries\BoardArticleQuery;
use LightPortal\Articles\Services\BoardArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Tests\ReflectionAccessor;

dataset('board rules data', [
    'regular_board' => [
        'input' => [
            'id_board'     => 1,
            'name'         => 'Test Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Test description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 1,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'num_posts'    => 10,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 1,
            'title'      => 'Test Board',
            'link'       => 'https://example.com/index.php?board=1.0',
            'is_new'     => true,
            'image'      => '',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=1',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Test description',
            'replies'    => [
                'num'   => 10,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'redirect_board' => [
        'input' => [
            'id_board'     => 2,
            'name'         => 'Redirect Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Redirect description',
            'is_redirect'  => 1,
            'redirect'     => 'https://external.com',
            'id_topic'     => 2,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 2,
            'num_posts'    => 5,
            'is_read'      => 1,
        ],
        'expected' => [
            'id'         => 2,
            'title'      => 'Redirect Board',
            'link'       => 'https://external.com" rel="nofollow noopener',
            'is_new'     => false,
            'image'      => 'https://image.thum.io/get/https://external.com',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=2',
            'category'   => 'Test Category',
            'is_redirect' => 1,
            'teaser'     => 'Redirect description',
            'replies'    => [
                'num'   => 5,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'board_with_attachment' => [
        'input' => [
            'id_board'     => 3,
            'name'         => 'Board with Attachment',
            'cat_name'     => 'Test Category',
            'description'  => 'Attachment description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 3,
            'attach_id'    => 123,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 3,
            'num_posts'    => 8,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 3,
            'title'      => 'Board with Attachment',
            'link'       => 'https://example.com/index.php?board=3.0',
            'is_new'     => true,
            'image'      => 'https://example.com/index.php?action=dlattach;topic=3;attach=123;image',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=3',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Attachment description',
            'replies'    => [
                'num'   => 8,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ]
]);

dataset('admin board rules data', [
    'regular_board' => [
        'input' => [
            'id_board'     => 1,
            'name'         => 'Test Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Test description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 1,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'num_posts'    => 10,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 1,
            'title'      => 'Test Board',
            'link'       => 'https://example.com/index.php?board=1.0',
            'is_new'     => true,
            'image'      => '',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=1',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Test description',
            'replies'    => [
                'num'   => 10,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'redirect_board' => [
        'input' => [
            'id_board'     => 2,
            'name'         => 'Redirect Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Redirect description',
            'is_redirect'  => 1,
            'redirect'     => 'https://external.com',
            'id_topic'     => 2,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 2,
            'num_posts'    => 5,
            'is_read'      => 1,
        ],
        'expected' => [
            'id'         => 2,
            'title'      => 'Redirect Board',
            'link'       => 'https://external.com" rel="nofollow noopener',
            'is_new'     => false,
            'image'      => 'https://image.thum.io/get/https://external.com',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=2',
            'category'   => 'Test Category',
            'is_redirect' => 1,
            'teaser'     => 'Redirect description',
            'replies'    => [
                'num'   => 5,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'board_with_attachment' => [
        'input' => [
            'id_board'     => 3,
            'name'         => 'Board with Attachment',
            'cat_name'     => 'Test Category',
            'description'  => 'Attachment description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 3,
            'attach_id'    => 123,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 3,
            'num_posts'    => 8,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 3,
            'title'      => 'Board with Attachment',
            'link'       => 'https://example.com/index.php?board=3.0',
            'is_new'     => true,
            'image'      => 'https://example.com/index.php?action=dlattach;topic=3;attach=123;image',
            'can_edit'   => true,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=3',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Attachment description',
            'replies'    => [
                'num'   => 8,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ]
]);

dataset('guest board rules data', [
    'regular_board' => [
        'input' => [
            'id_board'     => 1,
            'name'         => 'Test Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Test description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 1,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'num_posts'    => 10,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 1,
            'title'      => 'Test Board',
            'link'       => 'https://example.com/index.php?board=1.0',
            'is_new'     => true,
            'image'      => '',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=1',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Test description',
            'replies'    => [
                'num'   => 10,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'redirect_board' => [
        'input' => [
            'id_board'     => 2,
            'name'         => 'Redirect Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Redirect description',
            'is_redirect'  => 1,
            'redirect'     => 'https://external.com',
            'id_topic'     => 2,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 2,
            'num_posts'    => 5,
            'is_read'      => 1,
        ],
        'expected' => [
            'id'         => 2,
            'title'      => 'Redirect Board',
            'link'       => 'https://external.com" rel="nofollow noopener',
            'is_new'     => false,
            'image'      => 'https://image.thum.io/get/https://external.com',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=2',
            'category'   => 'Test Category',
            'is_redirect' => 1,
            'teaser'     => 'Redirect description',
            'replies'    => [
                'num'   => 5,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'board_with_attachment' => [
        'input' => [
            'id_board'     => 3,
            'name'         => 'Board with Attachment',
            'cat_name'     => 'Test Category',
            'description'  => 'Attachment description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 3,
            'attach_id'    => 123,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 3,
            'num_posts'    => 8,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 3,
            'title'      => 'Board with Attachment',
            'link'       => 'https://example.com/index.php?board=3.0',
            'is_new'     => true,
            'image'      => 'https://example.com/index.php?action=dlattach;topic=3;attach=123;image',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=3',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Attachment description',
            'replies'    => [
                'num'   => 8,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ]
]);

dataset('author board rules data', [
    'regular_board' => [
        'input' => [
            'id_board'     => 1,
            'name'         => 'Test Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Test description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 1,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'num_posts'    => 10,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 1,
            'title'      => 'Test Board',
            'link'       => 'https://example.com/index.php?board=1.0',
            'is_new'     => true,
            'image'      => '',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=1',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Test description',
            'replies'    => [
                'num'   => 10,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'redirect_board' => [
        'input' => [
            'id_board'     => 2,
            'name'         => 'Redirect Board',
            'cat_name'     => 'Test Category',
            'description'  => 'Redirect description',
            'is_redirect'  => 1,
            'redirect'     => 'https://external.com',
            'id_topic'     => 2,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 2,
            'num_posts'    => 5,
            'is_read'      => 1,
        ],
        'expected' => [
            'id'         => 2,
            'title'      => 'Redirect Board',
            'link'       => 'https://external.com" rel="nofollow noopener',
            'is_new'     => false,
            'image'      => 'https://image.thum.io/get/https://external.com',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=2',
            'category'   => 'Test Category',
            'is_redirect' => 1,
            'teaser'     => 'Redirect description',
            'replies'    => [
                'num'   => 5,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ],
    'board_with_attachment' => [
        'input' => [
            'id_board'     => 3,
            'name'         => 'Board with Attachment',
            'cat_name'     => 'Test Category',
            'description'  => 'Attachment description',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 3,
            'attach_id'    => 123,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 3,
            'num_posts'    => 8,
            'is_read'      => 0,
        ],
        'expected' => [
            'id'         => 3,
            'title'      => 'Board with Attachment',
            'link'       => 'https://example.com/index.php?board=3.0',
            'is_new'     => true,
            'image'      => 'https://example.com/index.php?action=dlattach;topic=3;attach=123;image',
            'can_edit'   => false,
            'edit_link'  => 'https://example.com/index.php?action=admin;area=manageboards;sa=board;boardid=3',
            'category'   => 'Test Category',
            'is_redirect' => 0,
            'teaser'     => 'Attachment description',
            'replies'    => [
                'num'   => 8,
                'title' => 'Replies',
                'after' => '',
            ],
        ]
    ]
]);

beforeEach(function() {
    Config::$modSettings['lp_show_images_in_articles'] = 1;
    Config::$modSettings['lp_show_teaser'] = 1;

    Utils::$context['description_allowed_tags'] = [];

    $this->queryMock = mock(BoardArticleQuery::class);
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc', 'updated;desc');

    $this->events = mock(EventDispatcherInterface::class);

    $this->service = new BoardArticleService($this->queryMock, $this->events);
});

it('returns data iterator', function () {
    $rows = [
        [
            'id_board'     => 1,
            'id_topic'     => 1,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 1,
            'name'         => 'Test Board',
            'description'  => 'Test description',
            'is_read'      => 0,
            'num_posts'    => 10,
            'cat_name'     => 'Test Category',
            'is_redirect'  => 0,
            'redirect'     => '',
            'attach_id'    => null,
        ]
    ];

    $this->queryMock->shouldReceive('setSorting')->with('created;desc')->once();
    $this->queryMock->shouldReceive('prepareParams')->with(0, 10)->once();
    $this->queryMock->shouldReceive('getRawData')->andReturn($rows);

    $this->events->shouldReceive('dispatch');

    $data = iterator_to_array($this->service->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray()->and($data)->toHaveKey(1);
});

it('returns total count', function () {
    $this->queryMock->shouldReceive('getTotalCount')->andReturn(5);

    $count = $this->service->getTotalCount();

    expect($count)->toBe(5);
});

it('returns sorting options', function () {
    $options = $this->service->getSortingOptions();

    expect($options)->toBeArray()
        ->and($options)->toHaveKey('created;desc')
        ->and($options)->toHaveKey('updated;desc')
        ->and($options)->toHaveKey('last_comment;desc')
        ->and($options)->toHaveKey('title;desc')
        ->and($options)->toHaveKey('num_replies;desc');
});

it('returns params for board articles', function () {
    Config::$modSettings['recycle_board'] = '5';
    Config::$modSettings['lp_frontpage_boards'] = '1,2,3';

    $params = $this->service->getParams();

    expect($params)->toBeArray()
        ->and($params)->toHaveKey('current_member')
        ->and($params)->toHaveKey('recycle_board')
        ->and($params)->toHaveKey('selected_boards')
        ->and($params['recycle_board'])->toBe(5)
        ->and($params['selected_boards'])->toBe(['1', '2', '3']);
});

it('calls init method', function () {
    $this->queryMock->shouldReceive('init')
        ->with(Mockery::on(function ($params) {
            return isset($params['current_member']) && isset($params['selected_boards']) && isset($params['recycle_board']);
        }))
        ->once();

    $this->service->init();

    expect(true)->toBeTrue(); // Add assertion to avoid risky test warning
});

it('returns rules array from getRules method', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'id_board'     => 1,
        'name'         => 'Test Board',
        'cat_name'     => 'Test Category',
        'description'  => 'Test description',
        'is_redirect'  => 0,
        'redirect'     => '',
        'id_topic'     => 1,
        'attach_id'    => null,
        'poster_time'  => 1000,
        'last_updated' => 0,
        'id_last_msg'  => 1,
        'num_posts'    => 10,
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    expect($rules)->toBeArray()
        ->and($rules)->toHaveKey('id')
        ->and($rules)->toHaveKey('date')
        ->and($rules)->toHaveKey('last_comment')
        ->and($rules)->toHaveKey('title')
        ->and($rules)->toHaveKey('link')
        ->and($rules)->toHaveKey('is_new')
        ->and($rules)->toHaveKey('replies')
        ->and($rules)->toHaveKey('image')
        ->and($rules)->toHaveKey('can_edit')
        ->and($rules)->toHaveKey('edit_link')
        ->and($rules)->toHaveKey('category')
        ->and($rules)->toHaveKey('is_redirect')
        ->and($rules)->toHaveKey('teaser')
        ->and($rules['id']($row))->toBe(1)
        ->and($rules['title']($row))->toBe('Test Board')
        ->and($rules['category']($row))->toBe('Test Category')
        ->and($rules['is_redirect']($row))->toBe(0)
        ->and($rules['can_edit']($row))->toBeTrue();

    $replies = $rules['replies']($row);
    expect($replies)->toBeArray()
        ->and($replies)->toHaveKey('num')
        ->and($replies)->toHaveKey('title')
        ->and($replies)->toHaveKey('after')
        ->and($replies['num'])->toBe(10)
        ->and($replies['title'])->toBeTruthy();
});

it('returns redirect board link correctly', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'id_board'     => 1,
        'name'         => 'Test Board',
        'cat_name'     => 'Test Category',
        'description'  => 'Test description',
        'is_redirect'  => 1,
        'redirect'     => 'https://external.com',
        'id_topic'     => 1,
        'attach_id'    => null,
        'poster_time'  => 1000,
        'last_updated' => 0,
        'id_last_msg'  => 1,
        'num_posts'    => 10,
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    expect($rules['link']($row))->toBe('https://external.com" rel="nofollow noopener');
});

it('returns regular board link correctly', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'id_board'     => 5,
        'name'         => 'Test Board',
        'cat_name'     => 'Test Category',
        'description'  => 'Test description',
        'is_redirect'  => 0,
        'redirect'     => '',
        'id_topic'     => 1,
        'attach_id'    => null,
        'poster_time'  => 1000,
        'last_updated' => 0,
        'id_last_msg'  => 1,
        'num_posts'    => 10,
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    expect($rules['link']($row))->toBe('https://example.com/index.php?board=5.0');
});

it('returns date based on sorting type', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'id_board'     => 1,
        'name'         => 'Test Board',
        'cat_name'     => 'Test Category',
        'description'  => 'Test description',
        'is_redirect'  => 0,
        'redirect'     => '',
        'id_topic'     => 1,
        'attach_id'    => null,
        'poster_time'  => 1000,
        'last_updated' => 2000,
        'id_last_msg'  => 1,
        'num_posts'    => 10,
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    // Test with created sorting
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc');
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc');
    expect($rules['date']($row))->toBe(1000);

    // Test with updated sorting
    $this->queryMock->shouldReceive('getSorting')->andReturn('updated;desc');
    expect($rules['date']($row))->toBe(2000);
});

it('finalizes item without modification', function () {
    $accessor = new ReflectionAccessor($this->service);

    $item = ['id' => 1, 'title' => 'Test'];

    $result = $accessor->callProtectedMethod('finalizeItem', [$item]);

    expect($result)->toBe($item);
});

describe('guest role', function () {
    it('returns expected values from board rules', function (array $input, array $expected) {
        User::$me = new User(0);
        User::$me->is_guest = true;
        User::$me->permissions = [];
        User::$me->allowedTo = fn($permission) => false;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('guest board rules data');
});

describe('author role', function () {
    it('returns expected values from board rules', function (array $input, array $expected) {
        User::$me = new User(1);
        User::$me->permissions = [];
        User::$me->allowedTo = fn($permission) => false;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('author board rules data');
});

describe('admin role', function () {
    it('returns expected values from board rules', function (array $input, array $expected) {
        User::$me = new User(1);
        User::$me->is_admin = true;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('admin board rules data');
});

describe('admin role with images disabled', function () {
    it('returns empty image when lp_show_images_in_articles is disabled', function () {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 0;
        Config::$modSettings['lp_show_teaser'] = 1;

        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'id_board'     => 5,
            'name'         => 'Board with Image',
            'cat_name'     => 'Test Category',
            'description'  => '[img]https://example.com/image.jpg[/img]',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 5,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 5,
            'num_posts'    => 3,
            'is_read'      => 0,
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['image']($input))->toBe('');
    });
});

describe('admin role with teaser disabled', function () {
    it('returns empty teaser when lp_show_teaser is disabled', function () {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 1;
        Config::$modSettings['lp_show_teaser'] = 0;

        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'id_board'     => 7,
            'name'         => 'Board with Content',
            'cat_name'     => 'Test Category',
            'description'  => 'Long description that would normally show as teaser text here in the board article preview',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 7,
            'attach_id'    => null,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 7,
            'num_posts'    => 3,
            'is_read'      => 0,
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['teaser']())->toBe('');
    });
});

describe('admin role with attachment fallback', function () {
    it('returns attachment URL as image fallback when no image in text', function () {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 1;
        Config::$modSettings['lp_show_teaser'] = 1;

        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'id_board'     => 6,
            'name'         => 'Board with Attachment',
            'cat_name'     => 'Test Category',
            'description'  => 'Content without image',
            'is_redirect'  => 0,
            'redirect'     => '',
            'id_topic'     => 6,
            'attach_id'    => 456,
            'poster_time'  => 1000,
            'last_updated' => 2000,
            'id_last_msg'  => 6,
            'num_posts'    => 3,
            'is_read'      => 0,
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['image']($input))->toBe('https://example.com/index.php?action=dlattach;topic=6;attach=456;image');
    });
});
