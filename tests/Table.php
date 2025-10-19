<?php

declare(strict_types=1);

namespace Tests;

enum Table: string
{
    case BLOCKS = /** @lang text */ '
        CREATE TABLE lp_blocks (
            block_id INTEGER PRIMARY KEY AUTOINCREMENT,
            icon TEXT NULL,
            type TEXT NOT NULL,
            placement TEXT NOT NULL,
            priority INTEGER NOT NULL DEFAULT 0,
            permissions INTEGER NOT NULL DEFAULT 0,
            status INTEGER NOT NULL DEFAULT 1,
            areas TEXT NOT NULL DEFAULT \'all\',
            title_class TEXT NULL,
            content_class TEXT NULL
        )';

    case CATEGORIES = /** @lang text */ '
        CREATE TABLE lp_categories (
            category_id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER NOT NULL DEFAULT 0,
            slug TEXT NOT NULL UNIQUE,
            icon TEXT,
            priority INTEGER NOT NULL DEFAULT 0,
            status INTEGER NOT NULL DEFAULT 1
        )';

    case COMMENTS = /** @lang text */ '
        CREATE TABLE lp_comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER NOT NULL DEFAULT 0,
            page_id INTEGER NOT NULL DEFAULT 0,
            author_id INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0,
            updated_at INTEGER NOT NULL DEFAULT 0
        )';

    case PAGE_TAG = /** @lang text */ '
        CREATE TABLE lp_page_tag (
            page_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            PRIMARY KEY (page_id, tag_id)
        )';

    case PAGES = /** @lang text */ '
        CREATE TABLE lp_pages (
            page_id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL DEFAULT 0,
            author_id INTEGER NOT NULL DEFAULT 0,
            slug TEXT NOT NULL,
            type TEXT NOT NULL DEFAULT \'bbc\',
            entry_type TEXT NOT NULL DEFAULT \'default\',
            permissions INTEGER NOT NULL DEFAULT 0,
            status INTEGER NOT NULL DEFAULT 1,
            num_views INTEGER NOT NULL DEFAULT 0,
            num_comments INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL DEFAULT 0,
            deleted_at INTEGER NOT NULL DEFAULT 0,
            last_comment_id INTEGER NOT NULL DEFAULT 0
        )';

    case PARAMS = /** @lang text */ '
        CREATE TABLE lp_params (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_id INTEGER NOT NULL,
            type TEXT NOT NULL DEFAULT \'block\',
            name TEXT NOT NULL,
            value TEXT NOT NULL,
            UNIQUE(item_id, type, name)
        )';

    case PLUGINS = /** @lang text */ '
        CREATE TABLE lp_plugins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            config VARCHAR(100) NOT NULL,
            value TEXT NULL,
            UNIQUE(name, config)
        )';

    case TAGS = /** @lang text */ '
        CREATE TABLE lp_tags (
            tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL,
            icon TEXT NULL,
            status INTEGER DEFAULT 1,
            UNIQUE(slug)
        )';

    case TRANSLATIONS = /** @lang text */ '
        CREATE TABLE lp_translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_id INTEGER NOT NULL,
            type TEXT DEFAULT \'block\',
            lang TEXT NOT NULL,
            title TEXT,
            content TEXT,
            description TEXT,
            UNIQUE(item_id, type, lang)
        )';

    case MEMBERS = /** @lang text */ '
        CREATE TABLE members (
            id_member INTEGER PRIMARY KEY AUTOINCREMENT,
            real_name TEXT NOT NULL,
            member_name TEXT NOT NULL
        )';

    case USER_ALERTS = /** @lang text */ '
        CREATE TABLE user_alerts (
            id_alert INTEGER PRIMARY KEY AUTOINCREMENT,
            alert_time INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL DEFAULT 0,
            id_member_started INTEGER NOT NULL DEFAULT 0,
            member_name TEXT NOT NULL DEFAULT "",
            content_type TEXT NOT NULL DEFAULT "",
            content_id INTEGER NOT NULL DEFAULT 0,
            content_action TEXT NOT NULL DEFAULT "",
            is_read INTEGER NOT NULL DEFAULT 0,
            extra TEXT NOT NULL DEFAULT ""
        )';
}
