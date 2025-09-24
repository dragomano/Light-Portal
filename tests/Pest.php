<?php

declare(strict_types=1);

use Tests\CustomTestCase;

require_once __DIR__ . '/../src/Sources/LightPortal/Libs/autoload.php';

pest()->extends(CustomTestCase::class);

if (! isset($GLOBALS['txt'])) {
    $txt['custom_profile_icon'] = 'Icon';

    require_once __DIR__ . '/../src/Themes/default/languages/LightPortal/LightPortal.english.php';

    $GLOBALS['txt'] = $txt;
}

if (! isset($GLOBALS['context'])) {
    $GLOBALS['context'] = [];
}

if (! isset($GLOBALS['smcFunc'])) {
    $GLOBALS['smcFunc'] = [];
}

if (! defined('LP_NAME')) {
    define('LP_NAME', 'Light Portal');
}

if (! defined('LP_ADDON_DIR')) {
    define('LP_ADDON_DIR', __DIR__ . '/addons');
}

if (! defined('LP_ALIAS_PATTERN')) {
    define('LP_ALIAS_PATTERN', '^[a-z][a-z0-9\-]+$');
}

if (! function_exists('memoryReturnBytes')) {
    function memoryReturnBytes(string $val): int
    {
        return (int) $val;
    }
}

if (! function_exists('fatal_error')) {
    function fatal_error(string $error, string|bool $log = 'general', int $status = 500): void
    {
        throw new Exception("Fatal error: $error");
    }
}

if (! function_exists('fatal_lang_error')) {
    function fatal_lang_error(string $error, string|bool $log = 'general', array $sprintf = [], int $status = 403): void
    {
        throw new Exception("Fatal lang error: $error");
    }
}

if (! function_exists('log_error')) {
    $GLOBALS['log_error_calls'] = [];

    function log_error(string $error_message, string|bool $error_type = 'general', string $file = '', int $line = 0, ?array $backtrace = null): string
    {
        $GLOBALS['log_error_calls'][] = [
            'message' => $error_message,
            'type' => $error_type,
            'file' => $file,
            'line' => $line,
            'backtrace' => $backtrace
        ];

        return 'logged';
    }
}

if (! function_exists('display_db_error')) {
    function display_db_error(): void
    {
        throw new Exception("DB error");
    }
}

if (! function_exists('loadTemplate')) {
    function loadTemplate(string $template): void
    {
    }
}

if (! function_exists('setupThemeContext')) {
    function setupThemeContext(bool $forceload = false): void
    {
    }
}

if (! function_exists('addJavaScriptVar')) {
    function addJavaScriptVar(string $key, string $value, bool $escape = false): void
    {
    }
}

if (! function_exists('addInlineCss')) {
    function addInlineCss(string $css): void
    {
    }
}

if (! function_exists('addInlineJavaScript')) {
    function addInlineJavaScript(string $javascript, $defer = false): void
    {
    }
}

if (! function_exists('loadCSSFile')) {
    function loadCSSFile(string $fileName, array $params = [], string $id = ''): void
    {
    }
}

if (! function_exists('loadJavaScriptFile')) {
    function loadJavaScriptFile(string $fileName, array $params = [], string $id = ''): void
    {
    }
}

if (! function_exists('loadEssentialThemeData')) {
    function loadEssentialThemeData(): void
    {
    }
}

if (! function_exists('httpsOn')) {
    function httpsOn(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }
}

if (! function_exists('set_time_limit')) {
    function set_time_limit(int $limit = 30): void
    {
        if (function_exists('set_time_limit')) {
            set_time_limit($limit);
        }
    }
}

if (! function_exists('sm_temp_dir')) {
    function sm_temp_dir(): string
    {
        return sys_get_temp_dir();
    }
}

if (! function_exists('isAllowedTo')) {
    function isAllowedTo($permission, $boards = null, $any = false): bool
    {
        return true;
    }
}

if (! function_exists('allowedTo')) {
    function allowedTo($permission): bool
    {
        return true;
    }
}

if (! function_exists('boardsAllowedTo')) {
    function boardsAllowedTo($permissions, $check_access = true, $simple = true): array
    {
        return [];
    }
}

if (! function_exists('membersAllowedTo')) {
    function membersAllowedTo($permission, $board_id = null): array
    {
        return [];
    }
}

// Define app function for dependency injection
if (! function_exists('add_integration_function')) {
    function add_integration_function(string $name, string $function, bool $permanent = true, string $file = '', bool $object = false): void
    {
    }
}

if (! function_exists('call_integration_hook')) {
    function call_integration_hook(string $name, array $parameters = []): array
    {
        return [];
    }
}

if (! function_exists('remove_integration_function')) {
    function remove_integration_function(string $name, string $function, bool $permanent = true, string $file = '', bool $object = false): void
    {
    }
}

require_once __DIR__ . '/functions.php';
