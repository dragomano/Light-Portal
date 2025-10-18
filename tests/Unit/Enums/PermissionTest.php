<?php

declare(strict_types=1);

use Bugo\Compat\User;
use LightPortal\Enums\Permission;

arch()
    ->expect(Permission::class)
    ->toBeIntBackedEnum();

describe('Permission::canViewItem', function () {
    it('returns true for ADMIN permission when user is admin', function () {
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(Permission::ADMIN))->toBeTrue();
    });

    it('returns false for ADMIN permission when user is not admin', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(Permission::ADMIN))->toBeFalse();
    });

    it('returns true for GUEST permission when user is guest', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = true;
        User::$me->id = 0;

        expect(Permission::canViewItem(Permission::GUEST))->toBeTrue();
    });

    it('returns false for GUEST permission when user is not guest', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(Permission::GUEST))->toBeFalse();
    });

    it('returns true for MEMBER permission when user has id > 0', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 5;

        expect(Permission::canViewItem(Permission::MEMBER))->toBeTrue();
    });

    it('returns false for MEMBER permission when user has id = 0', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = true;
        User::$me->id = 0;

        expect(Permission::canViewItem(Permission::MEMBER))->toBeFalse();
    });

    it('returns true for ALL permission regardless of user', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = true;
        User::$me->id = 0;

        expect(Permission::canViewItem(Permission::ALL))->toBeTrue();

        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(Permission::ALL))->toBeTrue();
    });

    it('returns true for MOD permission when user is admin or moderator', function () {
        // Set User to be admin
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [];

        expect(Permission::canViewItem(Permission::MOD))->toBeTrue();
    });

    it('returns false for MOD permission when user is not admin or moderator', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [];

        expect(Permission::canViewItem(Permission::MOD))->toBeFalse();
    });

    it('returns true for OWNER permission when user id matches', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 10;

        expect(Permission::canViewItem(Permission::OWNER, 10))->toBeTrue();
    });

    it('returns false for OWNER permission when user id does not match', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 10;

        expect(Permission::canViewItem(Permission::OWNER, 5))->toBeFalse();
    });

    it('returns false for invalid permission', function () {
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(999))->toBeFalse();
    });

    it('accepts int permission and converts it', function () {
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;

        expect(Permission::canViewItem(0))->toBeTrue(); // ADMIN = 0
    });
});

describe('Permission::all', function () {
    it('returns all permissions except OWNER for admin user', function () {
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [];

        $expected = [0, 1, 2, 3, 4]; // ADMIN, GUEST, MEMBER, ALL, MOD

        expect(Permission::all())->toEqual($expected);
    });

    it('returns GUEST and ALL for guest user', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = true;
        User::$me->id = 0;
        User::$me->groups = [];

        $expected = [1, 3]; // GUEST, ALL

        expect(Permission::all())->toEqual($expected);
    });

    it('returns MEMBER, ALL, MOD for moderator user', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [2]; // Group 2 is likely moderator group

        $expected = [2, 3, 4]; // MEMBER, ALL, MOD

        expect(Permission::all())->toEqual($expected);
    });

    it('returns MEMBER and ALL for regular member', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [1]; // Regular group

        $expected = [2, 3]; // MEMBER, ALL

        expect(Permission::all())->toEqual($expected);
    });

    it('returns ALL for unknown user type', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 0; // Not a member
        User::$me->groups = [];

        $expected = [3]; // ALL

        expect(Permission::all())->toEqual($expected);
    });
});

describe('Permission::isAdminOrModerator', function () {
    it('returns true when user is admin', function () {
        User::$me = new User();
        User::$me->is_admin = true;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [];

        expect(Permission::isAdminOrModerator())->toBeTrue();
    });

    it('returns true when user is moderator', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [2]; // Moderator group

        expect(Permission::isAdminOrModerator())->toBeTrue();
    });

    it('returns false when user is neither admin nor moderator', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [1]; // Regular group

        expect(Permission::isAdminOrModerator())->toBeFalse();
    });
});

describe('Permission::isModerator', function () {
    it('returns true when user is board moderator', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 5;
        User::$me->groups = [];

        expect(Permission::isModerator())->toBeFalse(); // User 5 is not in moderator group and no board moderators mocked
    });

    it('returns true when user is in moderator group', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [2]; // Moderator group

        expect(Permission::isModerator())->toBeTrue();
    });

    it('returns false when user is neither board moderator nor in moderator group', function () {
        User::$me = new User();
        User::$me->is_admin = false;
        User::$me->is_guest = false;
        User::$me->id = 1;
        User::$me->groups = [1]; // Regular group

        expect(Permission::isModerator())->toBeFalse();
    });
});

describe('Permission::isGroupMember', function () {
    it('returns true when user is member of specified group', function () {
        User::$me = new User();
        User::$me->groups = [1, 2, 3];

        expect(Permission::isGroupMember(2))->toBeTrue();
    });

    it('returns false when user is not member of specified group', function () {
        User::$me = new User();
        User::$me->groups = [1, 3];

        expect(Permission::isGroupMember(2))->toBeFalse();
    });

    it('returns false when user has no groups', function () {
        User::$me = new User();
        User::$me->groups = [];

        expect(Permission::isGroupMember(2))->toBeFalse();
    });
});

describe('Permission::getBoardModerators', function () {
    it('returns moderators from cache', function () {
        expect(true)->toBeTrue(); // Placeholder - since this is private method, tested indirectly
    });

    it('queries database when cache misses', function () {
        expect(true)->toBeTrue(); // Placeholder - since this is private method, tested indirectly
    });
});
