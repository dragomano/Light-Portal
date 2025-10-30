<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\TopicArticleQuery;
use LightPortal\Articles\Services\TopicArticleService;
use LightPortal\Events\EventDispatcherInterface;
use Tests\ReflectionAccessor;

dataset('topic rules data', [
    'regular_topic' => [
        'input' => [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Test Topic',
            'body'             => 'Test content',
            'id_first_msg'     => 1,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Topic',
            'link'      => 'https://example.com/index.php?topic=1.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=1;topic=1.0',
            'teaser'    => '',
        ]
    ],
    'sticky_topic' => [
        'input' => [
            'id_topic'         => 2,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Sticky Topic',
            'body'             => 'Sticky content',
            'id_first_msg'     => 3,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 1,
            'num_views'        => 100,
            'num_replies'      => 15,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Sticky Topic',
            'link'      => 'https://example.com/index.php?topic=2.0',
            'is_new'    => false,
            'css_class' => ' sticky',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=3;topic=2.0',
            'teaser'    => '',
        ]
    ],
    'new_topic_for_guest' => [
        'input' => [
            'id_topic'         => 3,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'New Topic',
            'body'             => 'New content',
            'id_first_msg'     => 5,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 10,
            'num_replies'      => 0,
            'id_attach'        => null,
            'last_poster_id'   => 123,
            'last_poster_name' => 'Test Author',
            'last_msg_time'    => 3000,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'New Topic',
            'link'      => 'https://example.com/index.php?topic=3.0',
            'is_new'    => false, // guest returns false
            'css_class' => '',
            'image'     => '',
            'can_edit'  => false, // guest cannot edit
            'edit_link' => 'https://example.com/index.php?action=post;msg=5;topic=3.0',
            'teaser'    => '',
        ]
    ],
    'topic_with_image' => [
        'input' => [
            'id_topic'         => 4,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Image',
            'body'             => '[img]https://example.com/image.jpg[/img]',
            'id_first_msg'     => 7,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'Topic with Image',
            'link'      => 'https://example.com/index.php?topic=4.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=7;topic=4.0',
            'teaser'    => '',
        ]
    ]
]);

dataset('admin topic rules data', [
    'regular_topic' => [
        'input' => [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Test Topic',
            'body'             => 'Test content',
            'id_first_msg'     => 1,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Topic',
            'link'      => 'https://example.com/index.php?topic=1.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=1;topic=1.0',
            'teaser'    => 'Test content',
        ]
    ],
    'sticky_topic' => [
        'input' => [
            'id_topic'         => 2,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Sticky Topic',
            'body'             => 'Sticky content',
            'id_first_msg'     => 3,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 1,
            'num_views'        => 100,
            'num_replies'      => 15,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Sticky Topic',
            'link'      => 'https://example.com/index.php?topic=2.0',
            'is_new'    => false,
            'css_class' => ' sticky',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=3;topic=2.0',
            'teaser'    => 'Sticky content',
        ]
    ],
    'new_topic_for_guest' => [
        'input' => [
            'id_topic'         => 3,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'New Topic',
            'body'             => 'New content',
            'id_first_msg'     => 5,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 10,
            'num_replies'      => 0,
            'id_attach'        => null,
            'last_poster_id'   => 123,
            'last_poster_name' => 'Test Author',
            'last_msg_time'    => 3000,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'New Topic',
            'link'      => 'https://example.com/index.php?topic=3.0',
            'is_new'    => true,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=5;topic=3.0',
            'teaser'    => 'New content',
        ]
    ],
    'topic_with_image' => [
        'input' => [
            'id_topic'         => 4,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Image',
            'body'             => '[img]https://example.com/image.jpg[/img]',
            'id_first_msg'     => 7,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'Topic with Image',
            'link'      => 'https://example.com/index.php?topic=4.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=7;topic=4.0',
            'teaser'    => '...',
        ]
    ],
]);

dataset('guest topic rules data', [
    'regular_topic' => [
        'input' => [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Test Topic',
            'body'             => 'Test content',
            'id_first_msg'     => 1,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Topic',
            'link'      => 'https://example.com/index.php?topic=1.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=1;topic=1.0',
            'teaser'    => 'Test content',
        ]
    ],
    'sticky_topic' => [
        'input' => [
            'id_topic'         => 2,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Sticky Topic',
            'body'             => 'Sticky content',
            'id_first_msg'     => 3,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 1,
            'num_views'        => 100,
            'num_replies'      => 15,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Sticky Topic',
            'link'      => 'https://example.com/index.php?topic=2.0',
            'is_new'    => false,
            'css_class' => ' sticky',
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=3;topic=2.0',
            'teaser'    => 'Sticky content',
        ]
    ],
    'new_topic_for_guest' => [
        'input' => [
            'id_topic'         => 3,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'New Topic',
            'body'             => 'New content',
            'id_first_msg'     => 5,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 10,
            'num_replies'      => 0,
            'id_attach'        => null,
            'last_poster_id'   => 123,
            'last_poster_name' => 'Test Author',
            'last_msg_time'    => 3000,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'New Topic',
            'link'      => 'https://example.com/index.php?topic=3.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=5;topic=3.0',
            'teaser'    => 'New content',
        ]
    ],
    'topic_with_image' => [
        'input' => [
            'id_topic'         => 4,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Image',
            'body'             => '[img]https://example.com/image.jpg[/img]',
            'id_first_msg'     => 7,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'Topic with Image',
            'link'      => 'https://example.com/index.php?topic=4.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=7;topic=4.0',
            'teaser'    => '...',
        ]
    ]
]);

dataset('author topic rules data', [
    'regular_topic' => [
        'input' => [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 1,
            'subject'          => 'Test Topic',
            'body'             => 'Test content',
            'id_first_msg'     => 1,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Topic',
            'link'      => 'https://example.com/index.php?topic=1.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=1;topic=1.0',
            'teaser'    => 'Test content',
        ]
    ],
    'sticky_topic' => [
        'input' => [
            'id_topic'         => 2,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 1,
            'subject'          => 'Sticky Topic',
            'body'             => 'Sticky content',
            'id_first_msg'     => 3,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 1,
            'num_views'        => 100,
            'num_replies'      => 15,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Sticky Topic',
            'link'      => 'https://example.com/index.php?topic=2.0',
            'is_new'    => false,
            'css_class' => ' sticky',
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=post;msg=3;topic=2.0',
            'teaser'    => 'Sticky content',
        ]
    ],
    'new_topic_for_guest' => [
        'input' => [
            'id_topic'         => 3,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'New Topic',
            'body'             => 'New content',
            'id_first_msg'     => 5,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 10,
            'num_replies'      => 0,
            'id_attach'        => null,
            'last_poster_id'   => 123,
            'last_poster_name' => 'Test Author',
            'last_msg_time'    => 3000,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'New Topic',
            'link'      => 'https://example.com/index.php?topic=3.0',
            'is_new'    => true,
            'css_class' => '',
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=5;topic=3.0',
            'teaser'    => 'New content',
        ]
    ],
    'topic_with_image' => [
        'input' => [
            'id_topic'         => 4,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Image',
            'body'             => '[img]https://example.com/image.jpg[/img]',
            'id_first_msg'     => 7,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'Topic with Image',
            'link'      => 'https://example.com/index.php?topic=4.0',
            'is_new'    => false,
            'css_class' => '',
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=post;msg=7;topic=4.0',
            'teaser'    => '...',
        ]
    ]
]);

beforeEach(function() {
    Config::$modSettings['lp_show_images_in_articles'] = 1;
    Config::$modSettings['lp_show_teaser'] = 1;

    $this->queryMock = mock(TopicArticleQuery::class);
    $this->queryMock->allows()->shouldReceive('getSorting')->andReturn('created;desc');

    $this->events = mock(EventDispatcherInterface::class);

    $this->service = new TopicArticleService($this->queryMock, $this->events);
});

it('returns total count', function () {
    $this->queryMock = mock(TopicArticleQuery::class);
    $this->queryMock->shouldReceive('getTotalCount')->andReturn(5);

    $this->service = new TopicArticleService($this->queryMock, $this->events);

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
        ->and($options)->toHaveKey('author_name;desc')
        ->and($options)->toHaveKey('num_views;desc')
        ->and($options)->toHaveKey('num_replies;desc');
});

it('returns params for topic articles', function () {
    Config::$modSettings['recycle_board'] = '7';
    Config::$modSettings['lp_frontpage_boards'] = '3,4,5';

    $params = $this->service->getParams();

    expect($params)->toBeArray()
        ->and($params)->toHaveKey('current_member')
        ->and($params)->toHaveKey('is_approved')
        ->and($params)->toHaveKey('id_poll')
        ->and($params)->toHaveKey('id_redirect_topic')
        ->and($params)->toHaveKey('attachment_type')
        ->and($params)->toHaveKey('recycle_board')
        ->and($params)->toHaveKey('selected_boards')
        ->and($params['is_approved'])->toBe(1)
        ->and($params['id_poll'])->toBe(0)
        ->and($params['attachment_type'])->toBe(0)
        ->and($params['recycle_board'])->toBe(7)
        ->and($params['selected_boards'])->toBe(['3', '4', '5']);
});

it('finalizes item with avatar', function () {
    $accessor = new ReflectionAccessor($this->service);
    $item = [
        'id' => 1,
        'title' => 'Test',
        'author' => [
            'id' => 123,
            'name' => 'Test Author',
        ]
    ];

    $result = $accessor->callProtectedMethod('finalizeItem', [$item]);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('author')
        ->and($result['author'])->toHaveKey('avatar');
});

it('returns data iterator', function () {
    $rows = [
        [
            'id_topic'         => 1,
            'id_board'         => 1,
            'name'             => 'Test Board',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'poster_time'      => 1000,
            'date'             => 2000,
            'last_msg_time'    => 3000,
            'subject'          => 'Test Topic',
            'is_sticky'        => 0,
            'num_views'        => 42,
            'num_replies'      => 7,
            'body'             => 'Test content',
            'id_attach'        => null,
            'new_from'         => 1,
            'id_msg_modified'  => 2,
            'id_first_msg'     => 1,
            'last_body'        => 'Last comment content',
            'smileys_enabled'  => 1,
        ]
    ];

    $this->queryMock->shouldReceive('setSorting')->with('created;desc');
    $this->queryMock->shouldReceive('prepareParams')->with(0, 10);
    $this->queryMock->shouldReceive('getRawData')->andReturn($rows);
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc');

    $this->events->shouldReceive('dispatch');

    $data = iterator_to_array($this->service->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray()->and($data)->toHaveKey(1);
});

it('returns date based on sorting type for topics', function () {
    $row = [
        'id_topic'         => 1,
        'id_board'         => 1,
        'name'             => 'Board Name',
        'poster_name'      => 'Test Author',
        'id_member'        => 123,
        'subject'          => 'Test Topic',
        'body'             => 'Test content',
        'id_first_msg'     => 1,
        'smileys_enabled'  => 1,
        'poster_time'      => 1000,
        'date'             => 2000,
        'is_sticky'        => 0,
        'num_views'        => 42,
        'num_replies'      => 7,
        'id_attach'        => null,
        'last_poster_id'   => 456,
        'last_poster_name' => 'Last Poster',
        'last_msg_time'    => 3000,
    ];

    $queryMock = mock(TopicArticleQuery::class);
    $queryMock->shouldReceive('getSorting')->andReturn('created;desc');
    $service = new TopicArticleService($queryMock, $this->events);
    $accessor = new ReflectionAccessor($service);
    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    // Test with created sorting
    expect($rules['date']($row))->toBe(1000);

    // Test with updated sorting
    $queryMock2 = mock(TopicArticleQuery::class);
    $queryMock2->shouldReceive('getSorting')->andReturn('updated;desc');
    $service2 = new TopicArticleService($queryMock2, $this->events);
    $accessor2 = new ReflectionAccessor($service2);
    $rules2 = $accessor2->callProtectedMethod('getRules', [$row]);
    expect($rules2['date']($row))->toBe(2000);
});

describe('guest role', function () {
    it('returns expected values from topic rules', function (array $input, array $expected) {
        User::$me = new User(0);
        User::$me->is_guest = true;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('guest topic rules data');
});

describe('author role', function () {
    it('returns expected values from topic rules', function (array $input, array $expected) {
        User::$me = new User(1);

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('author topic rules data');
});

describe('admin role', function () {
    it('returns expected values from topic rules', function (array $input, array $expected) {
        User::$me = new User(1);
        User::$me->is_admin = true;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('admin topic rules data');
});

describe('admin role with images disabled', function () {
    it('returns empty image when lp_show_images_in_articles is disabled', function () {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 0;
        Config::$modSettings['lp_show_teaser'] = 1;

        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'id_topic'         => 5,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Image',
            'body'             => '[img]https://example.com/image.jpg[/img]',
            'id_first_msg'     => 9,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
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
            'id_topic'         => 7,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Content',
            'body'             => 'Long content that would normally show as teaser text here in the article preview',
            'id_first_msg'     => 13,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => null,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
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
            'id_topic'         => 6,
            'id_board'         => 1,
            'name'             => 'Board Name',
            'poster_name'      => 'Test Author',
            'id_member'        => 123,
            'subject'          => 'Topic with Attachment',
            'body'             => 'Content without image',
            'id_first_msg'     => 11,
            'smileys_enabled'  => 1,
            'poster_time'      => 1000,
            'date'             => 2000,
            'is_sticky'        => 0,
            'num_views'        => 25,
            'num_replies'      => 3,
            'id_attach'        => 123,
            'last_poster_id'   => 456,
            'last_poster_name' => 'Last Poster',
            'last_msg_time'    => 3000,
            'new_from'         => 0,
            'id_msg_modified'  => 2,
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['image']($input))->toBe('https://example.com/index.php?topic=6;attach=123;image');
    });
});
