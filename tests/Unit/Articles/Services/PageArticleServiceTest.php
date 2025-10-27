<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use LightPortal\Articles\Queries\PageArticleQuery;
use LightPortal\Articles\Services\PageArticleService;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use Tests\ReflectionAccessor;

dataset('page rules data', [
    'regular_page' => [
        'input' => [
            'page_id'      => 1,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 50,
            'num_comments' => 5,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Test content',
            'description'  => 'Test description',
            'title'        => 'Test Page',
            'type'         => 'bbc',
            'slug'         => 'test-page',
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Page',
            'link'      => 'https://example.com/index.php?page=test-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=1',
            'teaser'    => 'Test content',
        ]
    ],
    'page_with_image' => [
        'input' => [
            'page_id'      => 2,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 2000,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => '[img]https://example.com/image.jpg[/img]',
            'description'  => 'Page with Image',
            'title'        => 'Page with Image',
            'type'         => 'bbc',
            'slug'         => 'page-with-image',
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Page with Image',
            'link'      => 'https://example.com/index.php?page=page-with-image',
            'is_new'    => false,
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=2',
            'teaser'    => '...',
        ]
    ],
    'page_with_teaser' => [
        'input' => [
            'page_id'      => 3,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 15,
            'num_comments' => 2,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Long content that should be truncated in teaser view for the article preview display',
            'description'  => 'Short teaser description',
            'title'        => 'Page with Teaser',
            'type'         => 'bbc',
            'slug'         => 'page-with-teaser',
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'Page with Teaser',
            'link'      => 'https://example.com/index.php?page=page-with-teaser',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=3',
            'teaser'    => 'Short teaser description',
        ]
    ]
]);

dataset('admin page rules data', [
    'regular_page' => [
        'input' => [
            'page_id'      => 1,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 50,
            'num_comments' => 5,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Test content',
            'description'  => 'Test description',
            'title'        => 'Test Page',
            'type'         => 'bbc',
            'slug'         => 'test-page',
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Page',
            'link'      => 'https://example.com/index.php?page=test-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=1',
            'teaser'    => 'Test description',
        ]
    ],
    'page_with_image' => [
        'input' => [
            'page_id'      => 2,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 2000,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => '[img]https://example.com/image.jpg[/img]',
            'description'  => 'Page with Image',
            'title'        => 'Page with Image',
            'type'         => 'bbc',
            'slug'         => 'page-with-image',
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Page with Image',
            'link'      => 'https://example.com/index.php?page=page-with-image',
            'is_new'    => false,
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=2',
            'teaser'    => 'Page with Image',
        ]
    ],
    'page_with_teaser' => [
        'input' => [
            'page_id'      => 3,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 15,
            'num_comments' => 2,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Long content that should be truncated in teaser view for the article preview display',
            'description'  => 'Short teaser description',
            'title'        => 'Page with Teaser',
            'type'         => 'bbc',
            'slug'         => 'page-with-teaser',
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'Page with Teaser',
            'link'      => 'https://example.com/index.php?page=page-with-teaser',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=3',
            'teaser'    => 'Short teaser description',
        ]
    ]
]);

dataset('guest page rules data', [
    'regular_page' => [
        'input' => [
            'page_id'      => 1,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1000,
            'created_at'   => 1000,
            'updated_at'   => 0,
            'comment_date' => 2500,
            'num_views'    => 50,
            'num_comments' => 5,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Test content',
            'description'  => 'Test description',
            'title'        => 'Test Page',
            'type'         => 'bbc',
            'slug'         => 'test-page',
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Page',
            'link'      => 'https://example.com/index.php?page=test-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=1',
            'teaser'    => 'Test description',
        ]
    ],
    'page_with_image' => [
        'input' => [
            'page_id'      => 2,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => '[img]https://example.com/image.jpg[/img]',
            'description'  => 'Page with Image',
            'title'        => 'Page with Image',
            'type'         => 'bbc',
            'slug'         => 'page-with-image',
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Page with Image',
            'link'      => 'https://example.com/index.php?page=page-with-image',
            'is_new'    => false,
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=2',
            'teaser'    => 'Page with Image',
        ]
    ],
    'page_with_teaser' => [
        'input' => [
            'page_id'      => 3,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 15,
            'num_comments' => 2,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Long content that should be truncated in teaser view for the article preview display',
            'description'  => 'Short teaser description',
            'title'        => 'Page with Teaser',
            'type'         => 'bbc',
            'slug'         => 'page-with-teaser',
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'Page with Teaser',
            'link'      => 'https://example.com/index.php?page=page-with-teaser',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=3',
            'teaser'    => 'Short teaser description',
        ]
    ],
    'new_guest_page' => [
        'input' => [
            'page_id'      => 4,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 3500,
            'created_at'   => 3500,
            'updated_at'   => 3500,
            'comment_date' => 3500,
            'num_views'    => 5,
            'num_comments' => 0,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'New page content',
            'description'  => 'New page description',
            'title'        => 'New Guest Page',
            'type'         => 'bbc',
            'slug'         => 'new-guest-page',
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'New Guest Page',
            'link'      => 'https://example.com/index.php?page=new-guest-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=4',
            'teaser'    => 'New page description',
        ]
    ],
    'another_new_guest_page' => [
        'input' => [
            'page_id'      => 5,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 3600,
            'created_at'   => 3600,
            'updated_at'   => 3600,
            'comment_date' => 3600,
            'num_views'    => 1,
            'num_comments' => 0,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Another new page content',
            'description'  => 'Another new page description',
            'title'        => 'Another New Guest Page',
            'type'         => 'bbc',
            'slug'         => 'another-new-guest-page',
        ],
        'expected' => [
            'id'        => 5,
            'title'     => 'Another New Guest Page',
            'link'      => 'https://example.com/index.php?page=another-new-guest-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=5',
            'teaser'    => 'Another new page description',
        ]
    ]
]);

dataset('author page rules data', [
    'regular_page' => [
        'input' => [
            'page_id'      => 1,
            'author_id'    => 1,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 50,
            'num_comments' => 5,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Test content',
            'description'  => '',
            'title'        => 'Test Page',
            'type'         => 'bbc',
            'slug'         => 'test-page',
        ],
        'expected' => [
            'id'        => 1,
            'title'     => 'Test Page',
            'link'      => 'https://example.com/index.php?page=test-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=1',
            'teaser'    => 'Test content',
        ]
    ],
    'page_with_image' => [
        'input' => [
            'page_id'      => 2,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 1500,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => '[img]https://example.com/image.jpg[/img]',
            'description'  => 'Page with Image',
            'title'        => 'Page with Image',
            'type'         => 'bbc',
            'slug'         => 'page-with-image',
        ],
        'expected' => [
            'id'        => 2,
            'title'     => 'Page with Image',
            'link'      => 'https://example.com/index.php?page=page-with-image',
            'is_new'    => false,
            'image'     => 'https://example.com/image.jpg',
            'can_edit'  => false,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=2',
            'teaser'    => 'Page with Image',
        ]
    ],
    'page_with_teaser' => [
        'input' => [
            'page_id'      => 3,
            'author_id'    => 1,
            'author_name'  => 'Test Author',
            'date'         => 3500,
            'created_at'   => 3500,
            'updated_at'   => 0,
            'comment_date' => 7500,
            'num_views'    => 15,
            'num_comments' => 2,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Long content that should be truncated in teaser view for the article preview display',
            'description'  => 'Short teaser description',
            'title'        => 'Page with Teaser',
            'type'         => 'bbc',
            'slug'         => 'page-with-teaser',
        ],
        'expected' => [
            'id'        => 3,
            'title'     => 'Page with Teaser',
            'link'      => 'https://example.com/index.php?page=page-with-teaser',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=3',
            'teaser'    => 'Short teaser description',
        ]
    ],
    'another_author_page' => [
        'input' => [
            'page_id'      => 4,
            'author_id'    => 1,
            'author_name'  => 'Test Author',
            'date'         => 3500,
            'created_at'   => 3500,
            'updated_at'   => 0,
            'comment_date' => 0,
            'num_views'    => 10,
            'num_comments' => 0,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Another author page content',
            'description'  => 'Another author page description',
            'title'        => 'Another Author Page',
            'type'         => 'bbc',
            'slug'         => 'another-author-page',
        ],
        'expected' => [
            'id'        => 4,
            'title'     => 'Another Author Page',
            'link'      => 'https://example.com/index.php?page=another-author-page',
            'is_new'    => false,
            'image'     => '',
            'can_edit'  => true,
            'edit_link' => 'https://example.com/index.php?action=admin;area=lp_pages;sa=edit;id=4',
            'teaser'    => 'Another author page description',
        ]
    ]
]);

beforeEach(function() {
    Config::$modSettings['lp_show_images_in_articles'] = 1;
    Config::$modSettings['lp_show_teaser'] = 1;
    Config::$scripturl = 'https://example.com/index.php';

    Lang::$txt['lang_locale'] = 'ru_RU';

    $this->queryMock = mock(PageArticleQuery::class);
    $this->queryMock->shouldReceive('getSorting')->andReturn('created;desc');

    $this->events = mock(EventDispatcherInterface::class);
    $this->events->shouldReceive('dispatch')->andReturnNull();
    $this->pageRepository = mock(PageRepositoryInterface::class);

    $this->service = new PageArticleService($this->queryMock, $this->events, $this->pageRepository);
});

it('can get sorting options', function () {
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

it('returns params for page articles', function () {
    Config::$modSettings['lp_frontpage_categories'] = '10,20';

    $params = $this->service->getParams();

    expect($params)->toBeArray()
        ->and($params)->toHaveKey('lang')
        ->and($params)->toHaveKey('fallback_lang')
        ->and($params)->toHaveKey('status')
        ->and($params)->toHaveKey('entry_type')
        ->and($params)->toHaveKey('current_time')
        ->and($params)->toHaveKey('deleted_at')
        ->and($params)->toHaveKey('permissions')
        ->and($params)->toHaveKey('selected_categories')
        ->and($params['status'])->toBe(1)
        ->and($params['entry_type'])->toBe('default')
        ->and($params['selected_categories'])->toBe(['10', '20']);
});

it('returns data iterator', function () {
    $rows = [
        [
            'page_id'             => 1,
            'author_id'           => 1,
            'author_name'         => 'Test Author',
            'date'                => 1000,
            'created_at'          => 1000,
            'updated_at'          => 2000,
            'comment_date'        => 3000,
            'num_views'           => 10,
            'num_comments'        => 5,
            'category_id'         => 0,
            'content'             => 'Test content',
            'description'         => 'Test description',
            'title'               => 'Test Page',
            'type'                => 'bbc',
            'slug'                => 'test-page',
            'cat_icon'            => '',
            'cat_title'           => '',
            'comment_author_id'   => 0,
            'comment_author_name' => '',
        ]
    ];

    $this->queryMock->shouldReceive('setSorting')->with('created;desc');
    $this->queryMock->shouldReceive('prepareParams')->with(0, 10);
    $this->queryMock->shouldReceive('getRawData')->andReturn($rows);

    $this->pageRepository->shouldReceive('fetchTags')->with([1])->andReturn([]);

    $data = iterator_to_array($this->service->getData(0, 10, 'created;desc'));

    expect($data)->toBeArray()->and($data)->toHaveKey(1);
});

it('returns total count', function () {
    $this->queryMock->shouldReceive('getTotalCount')->andReturn(5);

    $count = $this->service->getTotalCount();

    expect($count)->toBe(5);
});

it('prepares tags for pages', function () {
    $pages = [
        1 => ['title' => 'Page 1'],
        2 => ['title' => 'Page 2'],
        3 => ['title' => 'Page 3'],
    ];

    $tag1 = [
        'tag_id' => 10,
        'slug'   => 'tag-1',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=10',
        'name'   => 'Tag 1',
    ];
    $tag2 = [
        'tag_id' => 20,
        'slug'   => 'tag-2',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=20',
        'name'   => 'Tag 2',
    ];
    $tag3 = [
        'tag_id' => 30,
        'slug'   => 'tag-3',
        'icon'   => 'fas fa-tag',
        'href'   => '/tags/id=30',
        'name'   => 'Tag 3',
    ];

    $this->pageRepository->shouldReceive('fetchTags')->with([1, 2, 3])
        ->andReturn((function () use ($tag1, $tag2, $tag3) {
            yield 1 => $tag1;
            yield 1 => $tag3;
            yield 2 => $tag2;
        })());

    $accessor = new ReflectionAccessor($this->service);
    $accessor->callProtectedMethod('enrichArticles', [&$pages]);

    expect($pages[1]['tags'])->toHaveCount(2)
        ->and($pages[1]['tags'])->toContain($tag1)
        ->and($pages[1]['tags'])->toContain($tag3)
        ->and($pages[2]['tags'])->toHaveCount(1)
        ->and($pages[2]['tags'])->toContain($tag2)
        ->and($pages[3])->not()->toHaveKey('tags');
});

it('skips prepare tags when pages array is empty', function () {
    $pages = [];

    $this->pageRepository->shouldReceive('fetchTags')->with([])->never();

    $accessor = new ReflectionAccessor($this->service);
    $accessor->callProtectedMethod('prepareTags', [&$pages]);

    expect($pages)->toBeEmpty();
});

it('returns rules array from getRules method', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'page_id'      => 1,
        'author_id'    => 123,
        'author_name'  => 'Test Author',
        'date'         => 1500,
        'created_at'   => 1000,
        'updated_at'   => 1500,
        'comment_date' => 2500,
        'num_views'    => 50,
        'num_comments' => 5,
        'category_id'  => 10,
        'cat_title'    => 'Test Category',
        'content'      => 'Test content',
        'description'  => 'Test description',
        'title'        => 'Test Page',
        'type'         => 'bbc',
        'slug'         => 'test-page',
        'cat_icon'     => 'fas fa-folder',
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    expect($rules)->toBeArray()
        ->and($rules)->toHaveKey('id')
        ->and($rules)->toHaveKey('section')
        ->and($rules)->toHaveKey('author')
        ->and($rules)->toHaveKey('date')
        ->and($rules)->toHaveKey('created')
        ->and($rules)->toHaveKey('updated')
        ->and($rules)->toHaveKey('last_comment')
        ->and($rules)->toHaveKey('link')
        ->and($rules)->toHaveKey('views')
        ->and($rules)->toHaveKey('replies')
        ->and($rules)->toHaveKey('is_new')
        ->and($rules)->toHaveKey('image')
        ->and($rules)->toHaveKey('can_edit')
        ->and($rules)->toHaveKey('edit_link')
        ->and($rules)->toHaveKey('title')
        ->and($rules)->toHaveKey('teaser')
        ->and($rules['id']($row))->toBe(1)
        ->and($rules['title']($row))->toBe('Test Page')
        ->and($rules['link']($row))->toContain('test-page');

    // Test author structure
    $author = $rules['author']($row);
    expect($author)->toBeArray()
        ->and($author)->toHaveKey('id')
        ->and($author)->toHaveKey('link')
        ->and($author)->toHaveKey('name')
        ->and($author['id'])->toBe(123)
        ->and($author['name'])->toBe('Test Author');

    // Test views structure
    $views = $rules['views']($row);
    expect($views)->toBeArray()
        ->and($views)->toHaveKey('num')
        ->and($views)->toHaveKey('title')
        ->and($views)->toHaveKey('after')
        ->and($views['num'])->toBe('50');
});

it('returns date based on sorting type for pages', function () {
    $row = [
        'page_id'      => 1,
        'author_id'    => 123,
        'author_name'  => 'Test Author',
        'date'         => 2000,
        'created_at'   => 1000,
        'updated_at'   => 1500,
        'comment_date' => 2500,
        'num_views'    => 50,
        'num_comments' => 5,
        'category_id'  => 10,
        'cat_title'    => 'Test Category',
        'content'      => 'Test content',
        'description'  => 'Test description',
        'title'        => 'Test Page',
        'type'         => 'bbc',
        'slug'         => 'test-page',
        'cat_icon'     => 'fas fa-folder',
    ];

    // Test with created sorting - create separate service instance for this test
    $queryMockCreated = mock(PageArticleQuery::class);
    $queryMockCreated->shouldReceive('getSorting')->andReturn('created;desc');
    $serviceCreated = new PageArticleService($queryMockCreated, $this->events, $this->pageRepository);
    $accessorCreated = new ReflectionAccessor($serviceCreated);
    $rulesCreated = $accessorCreated->callProtectedMethod('getRules', [$row]);
    expect($rulesCreated['date']($row))->toBe('1 января 1970 г.');

    // Test with updated sorting - create separate service instance for this test
    $queryMockUpdated = mock(PageArticleQuery::class);
    $queryMockUpdated->shouldReceive('getSorting')->andReturn('updated;desc');
    $serviceUpdated = new PageArticleService($queryMockUpdated, $this->events, $this->pageRepository);
    $accessorUpdated = new ReflectionAccessor($serviceUpdated);
    $rulesUpdated = $accessorUpdated->callProtectedMethod('getRules', [$row]);
    expect($rulesUpdated['date']($row))->toBe('1 января 1970 г.');
});

it('returns unique page fields (created and updated)', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'page_id'      => 1,
        'author_id'    => 123,
        'author_name'  => 'Test Author',
        'date'         => 2000,
        'created_at'   => 1000,
        'updated_at'   => 1500,
        'comment_date' => 2500,
        'num_views'    => 50,
        'num_comments' => 5,
        'category_id'  => 10,
        'cat_title'    => 'Test Category',
        'content'      => 'Test content',
        'description'  => 'Test description',
        'title'        => 'Test Page',
        'type'         => 'bbc',
        'slug'         => 'test-page',
        'cat_icon'     => 'fas fa-folder',
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    expect($rules['created']($row))->toBe(1000)
        ->and($rules['updated']($row))->toBe(1500);
});

it('returns section data for pages', function () {
    $accessor = new ReflectionAccessor($this->service);

    $row = [
        'page_id'      => 1,
        'author_id'    => 123,
        'author_name'  => 'Test Author',
        'date'         => 2000,
        'created_at'   => 1000,
        'updated_at'   => 1500,
        'comment_date' => 2500,
        'num_views'    => 50,
        'num_comments' => 5,
        'category_id'  => 10,
        'cat_title'    => 'Test Category',
        'content'      => 'Test content',
        'description'  => 'Test description',
        'title'        => 'Test Page',
        'type'         => 'bbc',
        'slug'         => 'test-page',
        'cat_icon'     => 'fas fa-folder',
    ];

    $rules = $accessor->callProtectedMethod('getRules', [$row]);

    $section = $rules['section']($row);
    expect($section)->toBeArray()
        ->and($section)->toHaveKey('icon')
        ->and($section)->toHaveKey('name')
        ->and($section)->toHaveKey('link')
        ->and($section['name'])->toBe('Test Category');
});

describe('guest role', function () {
    it('returns expected values from page rules', function (array $input, array $expected) {
        User::$me = new User(0);
        User::$me->is_guest = true;
        User::$me->is_admin = false;
        User::$me->last_login = 3000;
        User::$me->permissions = [];
        User::$me->allowedTo = fn($permission) => false;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('guest page rules data');
});

describe('author role', function () {
    it('returns expected values from page rules', function (array $input, array $expected) {
        User::$me = new User(1);
        User::$me->is_admin = false;
        User::$me->last_login = 3000;
        User::$me->permissions = ['light_portal_manage_pages_own'];

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('author page rules data');
});

describe('admin role', function () {
    it('returns expected values from page rules', function (array $input, array $expected) {
        User::$me = new User(1);
        User::$me->is_admin = true;
        User::$me->last_login = 3000;
        User::$me->allowedTo = fn($permission) => true;

        $accessor = new ReflectionAccessor($this->service);
        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        foreach ($expected as $rule => $expectedValue) {
            expect($rules[$rule]($input))->toBe($expectedValue);
        }
    })->with('admin page rules data');
});

describe('admin role with images disabled', function () {
    beforeEach(function() {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 0;
        Config::$modSettings['lp_show_teaser'] = 1;
    });

    it('returns empty image when lp_show_images_in_articles is disabled', function () {
        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'page_id'      => 5,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 2000,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => '[img]https://example.com/image.jpg[/img]',
            'description'  => 'Page with Image',
            'title'        => 'Page with Image',
            'type'         => 'bbc',
            'slug'         => 'page-with-image',
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['image']($input))->toBe('');
    });
});

describe('admin role with teaser disabled', function () {
    beforeEach(function() {
        User::$me = new User(1);
        User::$me->is_admin = true;

        Config::$modSettings['lp_show_images_in_articles'] = 1;
        Config::$modSettings['lp_show_teaser'] = 0;
    });

    it('returns empty teaser when lp_show_teaser is disabled', function () {
        $accessor = new ReflectionAccessor($this->service);
        $input = [
            'page_id'      => 7,
            'author_id'    => 123,
            'author_name'  => 'Test Author',
            'date'         => 2000,
            'created_at'   => 1000,
            'updated_at'   => 1500,
            'comment_date' => 2500,
            'num_views'    => 25,
            'num_comments' => 3,
            'category_id'  => 10,
            'cat_title'    => 'Test Category',
            'cat_icon'     => 'fas fa-folder',
            'content'      => 'Long content that would normally show as teaser text here in the page article preview',
            'description'  => 'Long description that would normally show as teaser text here in the page article preview',
            'title'        => 'Page with Content',
            'type'         => 'bbc',
            'slug'         => 'page-with-content',
        ];

        $rules = $accessor->callProtectedMethod('getRules', [$input]);

        expect($rules['teaser']($input))->toBe('');
    });
});
