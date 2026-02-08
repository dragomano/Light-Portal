<?php

declare(strict_types=1);

use LightPortal\Database\PortalSql;
use LightPortal\DataHandlers\Imports\PageImport;
use LightPortal\Enums\ContentType;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\Status;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\FileInterface;
use Tests\PortalTable;
use Tests\ReflectionAccessor;
use Tests\TestAdapterFactory;

beforeEach(function () {
    $this->fileMock = mock(FileInterface::class);
    $this->errorHandlerMock = mock(ErrorHandlerInterface::class)->shouldIgnoreMissing();

    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();

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
            $messageElement = '';

            if (isset($comment['messages'])) {
                // New format
                $messages = '';
                foreach ($comment['messages'] as $lang => $message) {
                    $messages .= "<$lang><![CDATA[$message]]></$lang>";
                }

                $messageElement = "<messages>$messages</messages>";
            } elseif (isset($comment['message'])) {
                // Old format
                $messageElement = "<message><![CDATA[{$comment['message']}]]></message>";
            }

            $comments .= <<<XML
<comment
    id="{$comment['id']}"
    parent_id="{$comment['parent_id']}"
    author_id="{$comment['author_id']}"
    created_at="{$comment['created_at']}"
>
    $messageElement
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
            'params'          => ['show_title' => '1', 'show_author_and_date' => '1'],
            'comments'        => [
                [
                    'id'         => 1,
                    'parent_id'  => 0,
                    'author_id'  => 2,
                    'messages'   => ['english' => 'Comment 1', 'russian' => 'Комментарий 1'],
                    'created_at' => time(),
                ],
            ],
        ],
    ];

    $xml = simplexml_load_string(generatePageXml($pages));

    $import = new ReflectionAccessor(new PageImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProperty('xml', $xml);
    $import->callMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages')->execute()
    );
    expect(count($rows))->toBe(1);

    $translations = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_translations')->execute()
    );
    expect(count($translations))->toBe(4); // english + russian for title + english + russian for comment

    $params = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_params')->execute()
    );
    expect(count($params))->toBe(2);

    $comments = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_comments')->execute()
    );
    expect(count($comments))->toBe(1);
});

it('imports pages with multilingual comments correctly', function () {
    $pages = [
        [
            'page_id'         => 3,
            'category_id'     => 1,
            'author_id'       => 1,
            'slug'            => 'page-3',
            'type'            => ContentType::HTML->name(),
            'entry_type'      => EntryType::DEFAULT->name(),
            'permissions'     => 3,
            'status'          => Status::ACTIVE->value,
            'num_views'       => 0,
            'num_comments'    => 2,
            'created_at'      => time(),
            'updated_at'      => 0,
            'deleted_at'      => 0,
            'last_comment_id' => 2,
            'titles'          => ['english' => 'Page 3', 'russian' => 'Страница 3'],
            'contents'        => ['english' => 'Content 3', 'russian' => 'Содержимое 3'],
            'comments'        => [
                [
                    'id'         => 1,
                    'parent_id'  => 0,
                    'author_id'  => 2,
                    'messages'   => ['english' => 'First comment', 'russian' => 'Первый комментарий'],
                    'created_at' => time(),
                ],
                [
                    'id'         => 2,
                    'parent_id'  => 1,
                    'author_id'  => 3,
                    'messages'   => ['english' => 'Reply comment', 'russian' => 'Ответ на комментарий'],
                    'created_at' => time(),
                ],
            ],
        ],
    ];

    $xml = simplexml_load_string(generatePageXml($pages));

    $import = new ReflectionAccessor(new PageImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProperty('xml', $xml);
    $import->callMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages')->execute()
    );
    expect(count($rows))->toBe(1);

    $comments = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_comments ORDER BY id')->execute()
    );
    expect(count($comments))->toBe(2)
        ->and($comments[0]['id'])->toBe(1)
        ->and($comments[0]['parent_id'])->toBe(0)
        ->and($comments[0]['page_id'])->toBe(3)
        ->and($comments[0]['author_id'])->toBe(2)
        ->and($comments[1]['id'])->toBe(2)
        ->and($comments[1]['parent_id'])->toBe(1)
        ->and($comments[1]['page_id'])->toBe(3)
        ->and($comments[1]['author_id'])->toBe(3);

    $commentTranslations = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? ORDER BY item_id, lang', ['comment'])
    );
    expect(count($commentTranslations))->toBe(4)
        ->and($commentTranslations[0]['item_id'])->toBe(1)
        ->and($commentTranslations[0]['type'])->toBe('comment')
        ->and($commentTranslations[0]['lang'])->toBe('english')
        ->and($commentTranslations[0]['content'])->toBe('First comment')
        ->and($commentTranslations[1]['item_id'])->toBe(1)
        ->and($commentTranslations[1]['type'])->toBe('comment')
        ->and($commentTranslations[1]['lang'])->toBe('russian')
        ->and($commentTranslations[1]['content'])->toBe('Первый комментарий')
        ->and($commentTranslations[2]['item_id'])->toBe(2)
        ->and($commentTranslations[2]['type'])->toBe('comment')
        ->and($commentTranslations[2]['lang'])->toBe('english')
        ->and($commentTranslations[2]['content'])->toBe('Reply comment')
        ->and($commentTranslations[3]['item_id'])->toBe(2)
        ->and($commentTranslations[3]['type'])->toBe('comment')
        ->and($commentTranslations[3]['lang'])->toBe('russian')
        ->and($commentTranslations[3]['content'])->toBe('Ответ на комментарий'); // 2 comments * 2 languages
});

it('imports pages with old format comments correctly', function () {
    $pages = [
        [
            'page_id'         => 4,
            'category_id'     => 1,
            'author_id'       => 1,
            'slug'            => 'page-4',
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
            'titles'          => ['english' => 'Page 4'],
            'contents'        => ['english' => 'Content 4'],
            'comments'        => [
                [
                    'id'         => 1,
                    'parent_id'  => 0,
                    'author_id'  => 2,
                    'message'    => 'Old format comment',
                    'created_at' => time(),
                ],
            ],
        ],
    ];

    $xml = simplexml_load_string(generatePageXml($pages));

    $import = new ReflectionAccessor(new PageImport($this->sql, $this->fileMock, $this->errorHandlerMock));
    $import->setProperty('xml', $xml);
    $import->callMethod('processItems');

    $rows = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages')->execute()
    );
    expect(count($rows))->toBe(1);

    $comments = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_comments')->execute()
    );
    expect(count($comments))->toBe(1)
        ->and($comments[0]['id'])->toBe(1)
        ->and($comments[0]['page_id'])->toBe(4)
        ->and($comments[0]['author_id'])->toBe(2);

    $commentTranslations = iterator_to_array(
        $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_translations WHERE type = ? ORDER BY item_id, lang', ['comment'])
    );
    expect(count($commentTranslations))->toBe(1)
        ->and($commentTranslations[0]['item_id'])->toBe(1)
        ->and($commentTranslations[0]['type'])->toBe('comment')
        ->and($commentTranslations[0]['lang'])->toBe('english')
        ->and($commentTranslations[0]['content'])->toBe('Old format comment');
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
    $import->setProperty('xml', $xml);
    $import->callMethod('processItems');

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
