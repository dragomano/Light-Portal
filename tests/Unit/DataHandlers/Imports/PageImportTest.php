<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\DataHandlers\Imports\PageImport;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Status;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use Tests\ReflectionAccessor;
use Tests\Table;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $this->fileMock = Mockery::mock(FileInterface::class);
    $this->errorHandlerMock = Mockery::mock(ErrorHandlerInterface::class)->shouldIgnoreMissing();

    $adapter = TestAdapterFactory::create();
    $adapter->query(Table::PAGES->value)->execute();
    $adapter->query(Table::COMMENTS->value)->execute();
    $adapter->query(Table::PARAMS->value)->execute();
    $adapter->query(Table::TRANSLATIONS->value)->execute();

    $this->sql = new PortalSql($adapter);
});

function generatePageXml(array $pages): string
{
    $items = '';
    foreach ($pages as $page) {
        $titles = '';
        foreach ($page['titles'] ?? [] as $lang => $title) {
            $titles .= "<$lang>$title</$lang>";
        }

        $contents = '';
        foreach ($page['contents'] ?? [] as $lang => $content) {
            $contents .= "<$lang><![CDATA[$content]]></$lang>";
        }

        $descriptions = '';
        foreach ($page['descriptions'] ?? [] as $lang => $desc) {
            $descriptions .= "<$lang><![CDATA[$desc]]></$lang>";
        }

        $params = '';
        foreach ($page['params'] ?? [] as $key => $value) {
            $params .= "<$key>$value</$key>";
        }
        $params = $params ? "<params>$params</params>" : '';

        $comments = '';
        foreach ($page['comments'] ?? [] as $comment) {
            $comments .= <<<XML
<comment
    id="{$comment['id']}"
    parent_id="{$comment['parent_id']}"
    author_id="{$comment['author_id']}"
    created_at="{$comment['created_at']}"
>
    <message><![CDATA[{$comment['message']}]]></message>
</comment>
XML;
        }
        $comments = $comments ? "<comments>$comments</comments>" : '';

        $items .= <<<XML
<item
    page_id="{$page['page_id']}"
    category_id="{$page['category_id']}"
    author_id="{$page['author_id']}"
    permissions="{$page['permissions']}"
    status="{$page['status']}"
    num_views="{$page['num_views']}"
    num_comments="{$page['num_comments']}"
    created_at="{$page['created_at']}"
    updated_at="{$page['updated_at']}"
    deleted_at="{$page['deleted_at']}"
    last_comment_id="{$page['last_comment_id']}"
>
    <slug>{$page['slug']}</slug>
    <type>{$page['type']}</type>
    <entry_type>{$page['entry_type']}</entry_type>
    <titles>$titles</titles>
    <contents>$contents</contents>
    <descriptions>$descriptions</descriptions>
    $params
    $comments
</item>
XML;
    }

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<light_portal>
    <pages>$items</pages>
</light_portal>
XML;
}

it('imports pages with comments and params correctly', function () {
    $pages = [
        [
            'page_id'         => 1,
            'category_id'     => 1,
            'author_id'       => 1,
            'slug'            => 'page-1',
            'type'            => ContentType::HTML->name(),
            'entry_type'      => EntryType::DEFAULT->name(),
            'permissions'     => 3,
            'status'          => Status::ACTIVE->value,
            'num_views'       => 0,
            'num_comments'    => 1,
            'created_at'      => time(),
            'updated_at'      => 0,
            'deleted_at'      => 0,
            'last_comment_id' => 1,
            'titles'          => ['english' => 'Test Page', 'russian' => 'Тестовая страница'],
            'contents'        => ['english' => 'Page content here', 'russian' => 'Содержимое страницы'],
            'descriptions'    => ['english' => 'English description', 'russian' => 'Русское описание'],
            'comments'        => [
                ['id' => 1, 'parent_id' => 0, 'author_id' => 2, 'message' => 'Comment 1', 'created_at' => time()],
            ],
            'params'          => ['show_title' => '1', 'show_author_and_date' => '1'],
        ],
    ];

    $xml = simplexml_load_string(generatePageXml($pages));

    $import = new ReflectionAccessor(new PageImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProtectedProperty('xml', $xml);
    $import->callProtectedMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages')->execute()
    );
    expect(count($rows))->toBe(1);

    $translations = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_translations')->execute()
    );
    expect(count($translations))->toBe(2); // english + russian

    $params = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_params')->execute()
    );
    expect(count($params))->toBe(2);

    $comments = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_comments')->execute()
    );
    expect(count($comments))->toBe(1);
});

it('imports pages without comments or params gracefully', function () {
    $pages = [
        [
            'page_id'         => 2,
            'category_id'     => 1,
            'author_id'       => 2,
            'slug'            => 'page-2',
            'type'            => ContentType::BBC->name(),
            'entry_type'      => EntryType::DRAFT->name(),
            'permissions'     => 3,
            'status'          => Status::ACTIVE->value,
            'num_views'       => 0,
            'num_comments'    => 0,
            'created_at'      => time(),
            'updated_at'      => 0,
            'deleted_at'      => 0,
            'last_comment_id' => 0,
            'titles'          => ['english' => 'Page 2'],
            'contents'        => ['english' => 'Content 2'],
        ],
    ];

    $xml = simplexml_load_string(generatePageXml($pages));

    $import = new ReflectionAccessor(new PageImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProtectedProperty('xml', $xml);
    $import->callProtectedMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages')->execute()
    );
    expect(count($rows))->toBe(1);

    $translations = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_translations')->execute()
    );
    expect(count($translations))->toBe(1);

    $params = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_params')->execute()
    );
    expect(count($params))->toBe(0);

    $comments = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_comments')->execute()
    );
    expect(count($comments))->toBe(0);
});
