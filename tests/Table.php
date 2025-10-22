<?php

declare(strict_types=1);

namespace Tests;

enum Table: string
{
    case ATTACHMENTS = /** @lang text */ '
        CREATE TABLE attachments (
            id_attach INTEGER PRIMARY KEY AUTOINCREMENT,
            id_thumb INTEGER NOT NULL DEFAULT 0,
            id_msg INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL DEFAULT 0,
            attachment_type INTEGER NOT NULL DEFAULT 0,
            filename TEXT NOT NULL DEFAULT \'\',
            file_hash TEXT NOT NULL DEFAULT \'\',
            fileext TEXT NOT NULL DEFAULT \'\',
            size INTEGER NOT NULL DEFAULT 0,
            downloads INTEGER NOT NULL DEFAULT 0,
            width INTEGER NOT NULL DEFAULT 0,
            height INTEGER NOT NULL DEFAULT 0,
            mime_type TEXT NOT NULL DEFAULT \'\',
            approved INTEGER NOT NULL DEFAULT 1
        )';

    case BOARD_PERMISSIONS_VIEW = /** @lang text */ '
        CREATE TABLE board_permissions_view (
            id_group INTEGER NOT NULL DEFAULT 0,
            id_board INTEGER NOT NULL,
            deny INTEGER NOT NULL,
            PRIMARY KEY (id_group, id_board, deny)
        )';

    case BOARDS = /** @lang text */ '
        CREATE TABLE boards (
            id_board INTEGER PRIMARY KEY AUTOINCREMENT,
            id_cat INTEGER NOT NULL DEFAULT 0,
            child_level INTEGER NOT NULL DEFAULT 0,
            id_parent INTEGER NOT NULL DEFAULT 0,
            board_order INTEGER NOT NULL DEFAULT 0,
            id_last_msg INTEGER DEFAULT 0,
            id_msg_updated INTEGER NOT NULL DEFAULT 0,
            member_groups TEXT NOT NULL DEFAULT \'-1,0\',
            id_profile INTEGER NOT NULL DEFAULT 1,
            name TEXT NOT NULL DEFAULT \'\',
            description TEXT NOT NULL DEFAULT \'\',
            num_topics INTEGER NOT NULL DEFAULT 0,
            num_posts INTEGER NOT NULL DEFAULT 0,
            count_posts INTEGER NOT NULL DEFAULT 0,
            id_theme INTEGER NOT NULL DEFAULT 0,
            override_theme INTEGER NOT NULL DEFAULT 0,
            id_moderator INTEGER NOT NULL DEFAULT 0,
            id_moderator_group INTEGER NOT NULL DEFAULT 0,
            member_groups_moderator TEXT NOT NULL DEFAULT \'\',
            redirect TEXT NOT NULL DEFAULT \'\',
            deny_member_groups TEXT NOT NULL DEFAULT \'\',
            board_type TEXT NOT NULL DEFAULT \'default\'
        )';

    case CATEGORIES = /** @lang text */ '
        CREATE TABLE categories (
            id_cat INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT \'\',
            cat_order INTEGER NOT NULL DEFAULT 0
        )';

    case LOG_BOARDS = /** @lang text */ '
        CREATE TABLE log_boards (
            id_member INTEGER NOT NULL,
            id_board INTEGER NOT NULL,
            id_msg INTEGER NOT NULL,
            PRIMARY KEY (id_member, id_board)
        )';

    case LOG_MARK_READ = /** @lang text */ '
        CREATE TABLE log_mark_read (
            id_member INTEGER NOT NULL,
            id_board INTEGER NOT NULL,
            id_msg INTEGER NOT NULL,
            PRIMARY KEY (id_member, id_board)
        )';

    case LOG_TOPICS = /** @lang text */ '
        CREATE TABLE log_topics (
            id_member INTEGER NOT NULL,
            id_topic INTEGER NOT NULL,
            id_msg INTEGER NOT NULL,
            PRIMARY KEY (id_member, id_topic)
        )';

    case MEMBERS = /** @lang text */ '
        CREATE TABLE members (
            id_member INTEGER PRIMARY KEY AUTOINCREMENT,
            real_name TEXT NOT NULL,
            member_name TEXT NOT NULL,
            id_group INTEGER,
            avatar TEXT
        )';

    case MESSAGES = /** @lang text */ '
        CREATE TABLE messages (
            id_msg INTEGER PRIMARY KEY AUTOINCREMENT,
            id_topic INTEGER NOT NULL,
            id_board INTEGER NOT NULL,
            poster_time INTEGER NOT NULL DEFAULT 0,
            id_member INTEGER NOT NULL,
            id_msg_modified INTEGER NOT NULL DEFAULT 0,
            subject TEXT NOT NULL,
            poster_name TEXT NOT NULL,
            poster_email TEXT NOT NULL,
            poster_ip TEXT NOT NULL,
            smileys_enabled INTEGER NOT NULL DEFAULT 1,
            modified_time INTEGER NOT NULL DEFAULT 0,
            modified_name TEXT NOT NULL DEFAULT \'\',
            modified_reason TEXT NOT NULL DEFAULT \'\',
            body TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT \'xx\',
            approved INTEGER NOT NULL DEFAULT 1
        )';

    case TOPICS = /** @lang text */ '
        CREATE TABLE topics (
            id_topic INTEGER PRIMARY KEY AUTOINCREMENT,
            id_board INTEGER NOT NULL,
            id_first_msg INTEGER NOT NULL,
            id_last_msg INTEGER NOT NULL,
            id_member_started INTEGER NOT NULL,
            id_member_updated INTEGER NOT NULL DEFAULT 0,
            num_replies INTEGER NOT NULL DEFAULT 0,
            num_views INTEGER NOT NULL DEFAULT 0,
            is_sticky INTEGER NOT NULL DEFAULT 0,
            approved INTEGER NOT NULL DEFAULT 1,
            id_poll INTEGER DEFAULT 0,
            id_redirect_topic INTEGER DEFAULT 0
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
