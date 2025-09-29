<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\Db;
use Bugo\Compat\Lang;
use Bugo\Compat\User;
use Bugo\LightPortal\Articles\PageArticle;
use Bugo\LightPortal\Articles\ArticleInterface;
use Bugo\LightPortal\Enums\PortalHook;
use Bugo\LightPortal\Enums\PortalSubAction;
use Bugo\LightPortal\Enums\Status;
use Bugo\LightPortal\Utils\Avatar;
use Bugo\LightPortal\Utils\Content;
use Bugo\LightPortal\Utils\Icon;
use Bugo\LightPortal\Utils\Setting;
use Bugo\LightPortal\Events\EventManager;
use Bugo\Compat\Permission;

it('initializes parameters with all_pages frontpage mode', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('query')->andReturn('mock_result');
    $dbMock->shouldReceive('fetch_all')->andReturn([]);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $permissionMock = Mockery::mock('overload:' . Permission::class);
    $permissionMock->shouldReceive('all')->andReturn([1, 2, 3]);

    $userMock = Mockery::mock('Bugo\Compat\User');
    $userReflection = new \ReflectionClass('Bugo\Compat\User');
    $userReflection->setStaticPropertyValue('me', $userMock);
    $userMock->language = 'english';
    $userMock->groups = [1, 2, 3];
    $userMock->is_admin = false;
    $userMock->id = 1;
    $userMock->shouldReceive('allowedTo')->andReturn(false);

    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('language', 'english');
    $configReflection->setStaticPropertyValue('modSettings', [
        'lp_frontpage_mode' => 'all_pages'
    ]);

    $eventMock = Mockery::mock(EventManager::class);
    $eventMock->shouldReceive('dispatch')->andReturn(null);

    // Set the event manager mock in global variable for namespace_functions.php
    $GLOBALS['event_manager_mock'] = $eventMock;

    $article = new PageArticle();
    $article->init();

    $paramsReflection = new \ReflectionProperty(PageArticle::class, 'params');
    $params = $paramsReflection->getValue($article);

    expect($params['selected_categories'])->toBe([0]);
});

it('initializes parameters and dispatches hook', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('query')->andReturn('mock_result');
    $dbMock->shouldReceive('fetch_all')->andReturn([]);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $permissionMock = Mockery::mock('overload:' . Permission::class);
    $permissionMock->shouldReceive('all')->andReturn([1, 2, 3]);

    // Use a simpler approach - create a mock that doesn't conflict with existing classes
    $settingMock = Mockery::mock('SettingMock');
    $settingMock->shouldReceive('get')->with('lp_frontpage_categories', 'array', [])->andReturn([]);
    $settingMock->shouldReceive('isFrontpageMode')->with('all_pages')->andReturn(false);

    // Set up the mock in the global namespace to intercept calls
    $GLOBALS['SettingMock'] = $settingMock;

    $userMock = Mockery::mock('Bugo\Compat\User');
    $userReflection = new \ReflectionClass('Bugo\Compat\User');
    $userReflection->setStaticPropertyValue('me', $userMock);
    $userMock->language = 'russian';
    $userMock->groups = [1, 2, 3];
    $userMock->is_admin = false;
    $userMock->id = 1;
    $userMock->shouldReceive('allowedTo')->andReturn(false);

    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('language', 'english');

    $article = new PageArticle();

    $eventMock = Mockery::mock(EventManager::class);
    $eventMock->shouldReceive('dispatch')
        ->andReturn(null);

    // Set the event manager mock in global variable for namespace_functions.php
    $GLOBALS['event_manager_mock'] = $eventMock;

    $article->init();

    $paramsReflection = new \ReflectionProperty(PageArticle::class, 'params');
    $params = $paramsReflection->getValue($article);

    $ordersReflection = new \ReflectionProperty(PageArticle::class, 'orders');
    $orders = $ordersReflection->getValue($article);

    expect($params)->toHaveKey('empty_string');
    expect($params)->toHaveKey('lang');
    expect($params)->toHaveKey('selected_categories');
    expect($orders)->toHaveKey('created;desc');
});

it('returns sorting options', function () {
    $article = new PageArticle();

    $options = $article->getSortingOptions();

    expect($options)->toBeArray();
    expect($options)->toHaveKey('created;desc');
    expect($options)->toHaveKey('title');
});

it('skips rows with empty title in getData', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('query')->andReturn('perm_result');
    $dbMock->shouldReceive('fetch_all')->andReturn([]);
    $dbMock->shouldReceive('free_result')->once();
    // For getData
    $dbMock->shouldReceive('query')->andReturn('data_result');
    $dbMock->shouldReceive('fetch_assoc')->andReturn([
        'page_id' => 1,
        'title' => '', // Empty title should be skipped
        'content' => 'Test content',
        'description' => '',
        'author_id' => 1,
        'author_name' => 'Test Author',
        'created_at' => 1234567890,
        'updated_at' => 1234567890,
        'num_views' => 10,
        'num_comments' => 5,
        'comment_date' => 0,
        'date' => 1234567890,
        'cat_icon' => 'fas fa-folder',
        'cat_title' => 'Test Category',
        'category_id' => 1,
        'comment_author_id' => 0,
        'comment_author_name' => '',
        'comment_message' => '',
        'type' => 'bbc',
        'slug' => 'test-page',
    ], false);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $permissionMock = Mockery::mock('overload:' . Permission::class);
    $permissionMock->shouldReceive('all')->andReturn([1, 2, 3]);

    $article = new PageArticle();
    $article->init();

    $reflection = new \ReflectionMethod($article, 'getData');
    $reflection->setAccessible(true);

    $result = $reflection->invoke($article, 0, 10, 'created;desc');

    // Convert iterator to array - should be empty since title is empty
    $data = iterator_to_array($result);
    expect($data)->toBeEmpty();
});

it('retrieves data with mocked Db', function () {
    $dbMock = Mockery::mock();
    // For Permission in init()
    $dbMock->shouldReceive('query')->andReturn('perm_result');
    $dbMock->shouldReceive('fetch_all')->andReturn([]);
    $dbMock->shouldReceive('free_result')->once();
    // For getData
    $dbMock->shouldReceive('query')->andReturn('data_result');
    $dbMock->shouldReceive('fetch_assoc')->andReturn([
        'page_id' => 1,
        'title' => 'Test Page',
        'content' => 'Test content',
        'description' => '',
        'author_id' => 1,
        'author_name' => 'Test Author',
        'created_at' => 1234567890,
        'updated_at' => 1234567890,
        'num_views' => 10,
        'num_comments' => 5,
        'comment_date' => 0,
        'date' => 1234567890,
        'cat_icon' => 'fas fa-folder',
        'cat_title' => 'Test Category',
        'category_id' => 1,
        'comment_author_id' => 0,
        'comment_author_name' => '',
        'comment_message' => '',
        'type' => 'bbc',
        'slug' => 'test-page',
    ], false);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $permissionMock = Mockery::mock('overload:' . Permission::class);
    $permissionMock->shouldReceive('all')->andReturn([1, 2, 3]);

    Config::$modSettings['avatar_url'] = '';

    // Mock User::$loaded to provide proper data structure for Avatar
    $userLoadedMock = Mockery::mock();
    $userLoadedMock->shouldReceive('format')->andReturn([
        'name' => 'Test Author',
        'avatar' => ['image' => 'avatar_image']
    ]);

    // Use reflection to set User::$loaded
    $userReflection = new \ReflectionClass('Bugo\Compat\User');
    $loadedProperty = $userReflection->getProperty('loaded');
    $loadedProperty->setAccessible(true);
    $loadedProperty->setValue([1 => $userLoadedMock]);

    $article = new PageArticle();
    $article->init(); // Initialize params

    // Test getData method directly, mocking the yield behavior
    $reflection = new \ReflectionMethod($article, 'getData');
    $reflection->setAccessible(true);

    // Test the data processing
    $result = $reflection->invoke($article, 0, 10, 'created;desc');

    // Convert iterator to array for testing
    $data = [];
    foreach ($result as $key => $value) {
        $data[$key] = $value;
    }

    expect($data)->toBeArray();
    expect($data)->toHaveKey(1); // Should have the test page we mocked

    // Check that the test page has the expected structure
    expect($data[1])->toHaveKey('id');
    expect($data[1])->toHaveKey('title');
    expect($data[1])->toHaveKey('author');
    expect($data[1]['title'])->toBe('Test Page');
});

it('returns total count with mocked Db', function () {
    $dbMock = Mockery::mock();
    // For Permission in init()
    $dbMock->shouldReceive('query')->andReturn('perm_result');
    $dbMock->shouldReceive('fetch_all')->andReturn([]);
    $dbMock->shouldReceive('free_result')->once();
    // For getTotalCount
    $dbMock->shouldReceive('query')->andReturn('count_result');
    $dbMock->shouldReceive('fetch_row')->andReturn([42]);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $permissionMock = Mockery::mock('overload:' . Permission::class);
    $permissionMock->shouldReceive('all')->andReturn([1, 2, 3]);

    $article = new PageArticle();
    $article->init(); // Initialize params

    $count = $article->getTotalCount();

    expect($count)->toBe(42);
});

it('skips empty pages array in prepareTags', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'prepareTags');
    $reflection->setAccessible(true);

    $pages = [];
    $reflection->invokeArgs($article, [&$pages]);

    // Should not modify empty array
    expect($pages)->toBeEmpty();
});

it('skips tags with empty title in prepareTags', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('query')->andReturn('tag_result');
    $dbMock->shouldReceive('fetch_assoc')->andReturn([
        'page_id' => 1,
        'tag_id' => 1,
        'slug' => 'test-tag',
        'icon' => 'fas fa-tag',
        'title' => '', // Empty title should be skipped
    ], false);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $article = new PageArticle();

    $pages = [
        1 => ['id' => 1],
    ];

    $article->prepareTags($pages);

    // Should not add tags when title is empty
    expect($pages[1])->not->toHaveKey('tags');
});

it('prepares tags with mocked Db', function () {
    $dbMock = Mockery::mock();
    $dbMock->shouldReceive('query')->andReturn('tag_result');
    $dbMock->shouldReceive('fetch_assoc')->andReturn([
        'page_id' => 1,
        'tag_id' => 1,
        'slug' => 'test-tag',
        'icon' => 'fas fa-tag',
        'title' => 'Test Tag',
    ], false);
    $dbMock->shouldReceive('free_result');

    $reflection = new \ReflectionClass('Bugo\Compat\Db');
    $reflection->setStaticPropertyValue('db', $dbMock);

    $article = new PageArticle();

    $pages = [
        1 => ['id' => 1],
        2 => ['id' => 2],
    ];

    $article->prepareTags($pages);

    expect($pages[1])->toHaveKey('tags');
    expect($pages[1]['tags'])->toBeArray();
});

it('returns section data', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'getSectionData');
    $reflection->setAccessible(true);

    $row = [
        'cat_icon' => 'fas fa-folder',
        'category_id' => 1,
        'cat_title' => 'Test Category'
    ];

    $result = $reflection->invoke($article, $row);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('icon');
    expect($result)->toHaveKey('name');
    expect($result)->toHaveKey('link');
    expect($result['icon'])->toBe('<i class="fas fa-folder" aria-hidden="true"></i> ');
    expect($result['name'])->toBe('Test Category');
});

it('returns author data for page author', function () {
    $article = new PageArticle();

    // Set default sorting
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'created;desc');

    $reflection = new \ReflectionMethod($article, 'getAuthorData');
    $reflection->setAccessible(true);

    $row = [
        'author_id' => 123,
        'author_name' => 'Test Author',
        'num_comments' => 0,
        'comment_author_id' => 0,
        'comment_author_name' => ''
    ];

    $result = $reflection->invoke($article, $row);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('id');
    expect($result)->toHaveKey('link');
    expect($result)->toHaveKey('name');
    expect($result['id'])->toBe(123);
    expect($result['name'])->toBe('Test Author');
});

it('returns author data for comment author when sorting by last_comment', function () {
    $article = new PageArticle();

    // Set sorting to include last_comment
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'last_comment;desc');

    $reflection = new \ReflectionMethod($article, 'getAuthorData');
    $reflection->setAccessible(true);

    $row = [
        'author_id' => 123,
        'author_name' => 'Page Author',
        'comment_author_id' => 456,
        'comment_author_name' => 'Comment Author',
        'num_comments' => 5
    ];

    $result = $reflection->invoke($article, $row);

    expect($result['id'])->toBe(456);
    expect($result['name'])->toBe('Comment Author');
});

it('returns date based on sorting type', function () {
    $article = new PageArticle();

    // Set default sorting first
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'created;desc');

    $reflection = new \ReflectionMethod($article, 'getDate');
    $reflection->setAccessible(true);

    // Test default (created_at)
    $row = [
        'created_at' => 1000,
        'date' => 2000,
        'comment_date' => 3000
    ];

    $result = $reflection->invoke($article, $row);
    expect($result)->toBe(1000);

    // Test last_comment sorting
    $sortingProperty->setValue($article, 'last_comment;desc');

    $result = $reflection->invoke($article, $row);
    expect($result)->toBe(3000);

    // Test updated sorting
    $sortingProperty->setValue($article, 'updated;desc');
    $result = $reflection->invoke($article, $row);
    expect($result)->toBe(2000);
});

it('returns views data', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'getViewsData');
    $reflection->setAccessible(true);

    $row = ['num_views' => 42];

    $result = $reflection->invoke($article, $row);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('num');
    expect($result)->toHaveKey('title');
    expect($result)->toHaveKey('after');
    expect($result['num'])->toBe(42);
});

it('returns replies data structure', function () {
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', [
        'lp_comment_block' => 'default'
    ]);

    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'getRepliesData');
    $reflection->setAccessible(true);

    $row = ['num_comments' => 7];

    $result = $reflection->invoke($article, $row);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('num');
    expect($result)->toHaveKey('title');
    expect($result)->toHaveKey('after');
    expect($result['num'])->toBeInt();
});

it('checks if page is new', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'isNew');
    $reflection->setAccessible(true);

    // Mock User::$me
    $userMock = Mockery::mock('Bugo\Compat\User');
    $userReflection = new \ReflectionClass('Bugo\Compat\User');
    $userReflection->setStaticPropertyValue('me', $userMock);
    $userMock->last_login = 500;
    $userMock->id = 1;

    $row = [
        'date' => 1000,
        'author_id' => 2
    ];

    $result = $reflection->invoke($article, $row);
    expect($result)->toBeTrue();

    // Test when user is the author
    $row['author_id'] = 1;
    $result = $reflection->invoke($article, $row);
    expect($result)->toBeFalse();

    // Test when page is older than last login
    $row['date'] = 400;
    $row['author_id'] = 2;
    $result = $reflection->invoke($article, $row);
    expect($result)->toBeFalse();
});

it('gets image from content', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'getImage');
    $reflection->setAccessible(true);

    // Mock Config::$modSettings
    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_images_in_articles' => 1, 'smileys_url' => '']);

    $row = ['content' => 'Some content with <img src="test.jpg"> image'];

    $result = $reflection->invoke($article, $row);
    expect($result)->toBeString();

    // Test when images are disabled
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_images_in_articles' => 0, 'smileys_url' => '']);
    $result = $reflection->invoke($article, $row);
    expect($result)->toBe('');
});

it('checks edit permissions structure', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'canEdit');
    $reflection->setAccessible(true);

    // Mock User::$me with basic structure
    $userMock = Mockery::mock('Bugo\Compat\User');
    $userReflection = new \ReflectionClass('Bugo\Compat\User');
    $userReflection->setStaticPropertyValue('me', $userMock);
    $userMock->is_admin = true; // Set as admin to avoid permission checks
    $userMock->id = 1;

    $row = ['author_id' => 2];

    // Test as admin
    $result = $reflection->invoke($article, $row);
    expect($result)->toBeTrue();
});

it('gets edit link', function () {
    $article = new PageArticle();

    $reflection = new \ReflectionMethod($article, 'getEditLink');
    $reflection->setAccessible(true);

    $row = ['page_id' => 42];

    $result = $reflection->invoke($article, $row);

    expect($result)->toBeString();
    expect($result)->toContain('action=admin;area=lp_pages;sa=edit;id=42');
});

it('prepares teaser with last comment content', function () {
    $article = new PageArticle();

    // Set sorting to last_comment
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'last_comment;desc');

    $reflection = new \ReflectionMethod($article, 'prepareTeaser');
    $reflection->setAccessible(true);

    // Mock Config::$modSettings
    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_teaser' => 1]);

    $page = [];
    $row = [
        'description' => 'Test description',
        'content' => 'Test content',
        'num_comments' => 5, // Has comments
        'comment_message' => 'Test comment message'
    ];

    $reflection->invokeArgs($article, [&$page, $row]);
    expect($page)->toHaveKey('teaser');
});

it('prepares teaser with description when available', function () {
    $article = new PageArticle();

    // Set default sorting
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'created;desc');

    $reflection = new \ReflectionMethod($article, 'prepareTeaser');
    $reflection->setAccessible(true);

    // Mock Config::$modSettings
    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_teaser' => 1]);

    $page = [];
    $row = [
        'description' => 'Test description', // Has description
        'content' => 'Test content',
        'num_comments' => 0,
        'comment_message' => ''
    ];

    $reflection->invokeArgs($article, [&$page, $row]);
    expect($page)->toHaveKey('teaser');
});

it('prepares teaser with content when no description', function () {
    $article = new PageArticle();

    // Set default sorting
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'created;desc');

    $reflection = new \ReflectionMethod($article, 'prepareTeaser');
    $reflection->setAccessible(true);

    // Mock Config::$modSettings
    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_teaser' => 1]);

    $page = [];
    $row = [
        'description' => '', // No description
        'content' => 'Test content', // Use content
        'num_comments' => 0,
        'comment_message' => ''
    ];

    $reflection->invokeArgs($article, [&$page, $row]);
    expect($page)->toHaveKey('teaser');
});

it('prepares teaser structure', function () {
    // Mock Config::$modSettings
    $configMock = Mockery::mock('Bugo\Compat\Config');
    $configReflection = new \ReflectionClass('Bugo\Compat\Config');
    $configReflection->setStaticPropertyValue('modSettings', ['lp_show_teaser' => 1]);

    $article = new PageArticle();

    // Set default sorting first
    $sortingProperty = new \ReflectionProperty($article, 'sorting');
    $sortingProperty->setAccessible(true);
    $sortingProperty->setValue($article, 'created;desc');

    $reflection = new \ReflectionMethod($article, 'prepareTeaser');
    $reflection->setAccessible(true);

    $page = [];
    $row = [
        'description' => 'Test description',
        'content' => 'Test content',
        'num_comments' => 0,
        'comment_message' => ''
    ];

    // Test that the method doesn't crash and modifies the page array
    $reflection->invokeArgs($article, [&$page, $row]);
    expect($page)->toBeArray();
});
