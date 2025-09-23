<?php

declare(strict_types=1);

use Bugo\LightPortal\Utils\CacheInterface;
use Bugo\LightPortal\Utils\RequestInterface;
use Tests\AppMockRegistry;
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

if (! function_exists('app')) {
    function app(string $service = ''): mixed
    {
        if ($mock = AppMockRegistry::get($service)) {
            return $mock;
        }

        if (str_contains($service, 'TablePresenter')) {
            return new class {
                public function show($table)
                {
                }
            };
        }

        if (str_contains($service, 'PluginList')) {
            return function () {
                return [];
            };
        }

        if (str_contains($service, 'CacheInterface')) {
            return new class implements CacheInterface {
                public function withKey(?string $key): CacheInterface
                {
                    return $this;
                }
                public function setLifeTime(int $lifeTime): CacheInterface
                {
                    return $this;
                }
                public function remember(string $key, callable $callback, int $time = 0): mixed
                {
                    return $callback();
                }
                public function setFallback(callable $callback): null
                {
                    return null;
                }
                public function get(string $key, int $time): null
                {
                    return null;
                }
                public function put(string $key, mixed $value, int $time): void
                {
                }
                public function forget(string $key): void
                {
                }
                public function flush(): void
                {
                }
            };
        }

        if (str_contains($service, 'RequestInterface')) {
            return new class implements RequestInterface {
                public function isEmpty(string $key): bool
                {
                    return true;
                }
                public function hasNot(string $key): bool
                {
                    return true;
                }
                public function get(string $key): null
                {
                    return null;
                }
                public function has(string $key): bool
                {
                    return false;
                }
                public function post(string $key): null
                {
                    return null;
                }

                public function is(string $action, string $type = 'action'): bool
                {
                    return true;
                }

                public function isNot(string $action, string $type = 'action'): bool
                {
                    return true;
                }

                public function json(?string $key = null, mixed $default = null): array
                {
                    return [];
                }

                public function url(): string
                {
                    return '';
                }
            };
        }

        return null;
    }
}
