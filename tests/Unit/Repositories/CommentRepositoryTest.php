<?php

declare(strict_types=1);

use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Database\PortalSql;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\AbstractRepository;
use LightPortal\Repositories\CommentRepository;
use LightPortal\Repositories\CommentRepositoryInterface;
use Tests\PortalTable;
use Tests\Table;
use Tests\TestAdapterFactory;
use Tests\TestExitException;

arch()
    ->expect(CommentRepository::class)
    ->toExtend(AbstractRepository::class)
    ->toImplement(CommentRepositoryInterface::class);

beforeEach(function() {
    $adapter = TestAdapterFactory::create();
    $adapter->query(PortalTable::COMMENTS->value)->execute();
    $adapter->query(PortalTable::PAGES->value)->execute();
    $adapter->query(PortalTable::PARAMS->value)->execute();
    $adapter->query(PortalTable::TRANSLATIONS->value)->execute();
    $adapter->query(Table::MEMBERS->value)->execute();
    $adapter->query(Table::USER_ALERTS->value)->execute();

    $this->sql        = new PortalSql($adapter);
    $this->dispatcher = mock(EventDispatcherInterface::class);
    $this->repository = new CommentRepository($this->sql, $this->dispatcher);
});

it('can get comment data with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES (1, 0, 1, 1, ?, ?)
    ", [time(), 0]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES (1, 'Test Author', 'test_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES (1, 'comment', 'english', 'Test comment content')
    ")->execute();

    $result = $this->repository->getData(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('id')
        ->and($result['id'])->toBe(1)
        ->and($result['message'])->toBe('Test comment content')
        ->and($result)->toHaveKey('poster')
        ->and($result['poster']['name'])->toBe('Test Author');
});

it('can get comments by page id with translations', function () {
    $time = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES
            (1, 0, 1, 1, ?, ?),
            (2, 0, 1, 2, ?, ?)
    ", [$time, 0, $time + 60, 0]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES
            (1, 'Author One', 'author1'),
            (2, 'Author Two', 'author2')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES
            (1, 'comment', 'english', 'First comment'),
            (2, 'comment', 'english', 'Second comment')
    ")->execute();

    $result = $this->repository->getByPageId(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result)->toHaveKey(1)
        ->and($result)->toHaveKey(2)
        ->and($result[1]['message'])->toBe('First comment')
        ->and($result[2]['message'])->toBe('Second comment')
        ->and($result[1]['poster']['name'])->toBe('Author One')
        ->and($result[2]['poster']['name'])->toBe('Author Two');
});

it('can save comment with translations', function () {
    $time = time();

    $data = [
        'parent_id'  => 0,
        'page_id'    => 1,
        'author_id'  => 1,
        'message'    => 'New comment message',
        'created_at' => $time,
    ];

    Utils::$context = [
        'lp_comment' => [
            'title'       => '',
            'content'     => $data['message'],
            'description' => '',
        ]
    ];

    User::$me->language = 'english';

    $result = $this->repository->save($data);

    expect($result)->toBeInt()
        ->and($result)->toBeGreaterThan(0);

    $comments = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT * FROM lp_comments WHERE id = ?', [$result]);
    $comment = $comments->current();

    expect($comment['id'])->toBe($result)
        ->and($comment['page_id'])->toBe(1)
        ->and($comment['author_id'])->toBe(1);

    $translations = $this->sql->getAdapter()->query(
        /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND type = ? AND lang = ?',
        [$result, 'comment', 'english']
    );
    $translation = $translations->current();

    expect($translation['content'])->toBe('New comment message');
});

it('can update comment with translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES (1, 0, 1, 1, ?, ?)
    ", [time(), 0]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES (1, 'comment', 'english', 'Original message')
    ")->execute();

    Utils::$context = [
        'lp_comment' => [
            'title'       => '',
            'content'     => 'Updated message',
            'description' => '',
        ]
    ];

    User::$me->language = 'english';

    $data = [
        'id'      => 1,
        'message' => 'Updated message',
        'user'    => 1,
    ];

    $this->repository->update($data);

    $translations = $this->sql->getAdapter()->query(
        /** @lang text */ 'SELECT * FROM lp_translations WHERE item_id = ? AND type = ? AND lang = ?',
        [1, 'comment', 'english']
    );
    $translation = $translations->current();

    expect($translation['content'])->toBe('Updated message');
});

it('can remove comment and translations', function () {
    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES (1, 0, 1, 1, ?, ?)
    ", [time(), 0]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES (1, 'comment', 'english', 'Comment to delete')
    ")->execute();

    $commentsBefore = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_comments')->execute();
    $countBefore = $commentsBefore->current()['count'];
    expect($countBefore)->toBe(1);

    $translationsBefore = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_translations WHERE type = ?', ['comment']);
    $transCountBefore = $translationsBefore->current()['count'];
    expect($transCountBefore)->toBe(1);

    $this->repository->remove([1]);

    $commentsAfter = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_comments')->execute();
    $countAfter = $commentsAfter->current()['count'];
    expect($countAfter)->toBe(0);

    $translationsAfter = $this->sql->getAdapter()
        ->query(/** @lang text */ 'SELECT COUNT(*) as count FROM lp_translations WHERE type = ?', ['comment']);
    $transCountAfter = $translationsAfter->current()['count'];
    expect($transCountAfter)->toBe(0);
});

it('returns empty array for non-existent comment', function () {
    $result = $this->repository->getData(999);

    expect($result)->toBeArray()
        ->and($result)->toBeEmpty();
});

it('handles comment tree structure correctly', function () {
    $time = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES
            (1, 0, 1, 1, ?, 0),
            (2, 1, 1, 2, ?, 0)
    ", [$time, $time + 60]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES
            (1, 'Parent Author', 'parent_author'),
            (2, 'Child Author', 'child_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES
            (1, 'comment', 'english', 'Parent comment'),
            (2, 'comment', 'english', 'Child comment')
    ")->execute();

    $result = $this->repository->getByPageId(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[1]['parent_id'])->toBe(0)
        ->and($result[2]['parent_id'])->toBe(1)
        ->and($result[1]['message'])->toBe('Parent comment')
        ->and($result[2]['message'])->toBe('Child comment');
});

it('can update last comment id for page', function () {
    $time = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_pages (page_id, category_id, author_id, slug, type, entry_type, permissions, status, created_at, num_comments, last_comment_id)
        VALUES (1, 0, 1, 'test-page', 'bbc', 'default', 0, 1, ?, 0, 0)
    ", [$time]);

    $this->repository->updateLastCommentId(5, 1);

    $pages = $this->sql->getAdapter()->query(/** @lang text */ 'SELECT * FROM lp_pages WHERE page_id = 1')->execute();
    $page = $pages->current();

    expect($page['last_comment_id'])->toBe(5)
        ->and($page['num_comments'])->toBe(1);
});

it('filters out comments without translations', function () {
    $time = time();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_comments (id, parent_id, page_id, author_id, created_at, updated_at)
        VALUES
            (1, 0, 1, 1, ?, 0),
            (2, 0, 1, 2, ?, 0)
    ", [$time, $time + 60]);

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO members (id_member, real_name, member_name)
        VALUES
            (1, 'English Author', 'english_author'),
            (2, 'Russian Author', 'russian_author')
    ")->execute();

    $this->sql->getAdapter()->query(/** @lang text */ "
        INSERT INTO lp_translations (item_id, type, lang, content)
        VALUES
            (1, 'comment', 'english', 'Comment in English'),
            (2, 'comment', 'russian', 'Комментарий только на русском')
    ")->execute();

    $result = $this->repository->getByPageId(1);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result)->toHaveKey(1)
        ->and($result)->not->toHaveKey(2)
        ->and($result[1]['message'])->toBe('Comment in English');
});
