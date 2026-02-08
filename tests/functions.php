<?php

declare(strict_types=1);

use Bugo\Compat\Config;
use Bugo\Compat\User;
use Bugo\Compat\Utils;

if (! function_exists('memoryReturnBytes')) {
    function memoryReturnBytes(string $val): int
    {
        return (int) $val;
    }
}

if (! function_exists('fatal_error')) {
    function fatal_error(string $error, string|bool $log = 'general', int $status = 500): void
    {
    }
}

if (! function_exists('fatal_lang_error')) {
    function fatal_lang_error(string $error, string|bool $log = 'general', array $sprintf = [], int $status = 403): void
    {
    }
}

if (! function_exists('log_error')) {
    $GLOBALS['log_error_calls'] = [];

    function log_error(
        string $error_message,
        string|bool $error_type = 'general',
        string $file = '',
        int $line = 0,
        ?array $backtrace = null
    ): string
    {
        $GLOBALS['log_error_calls'][] = [
            'message'   => $error_message,
            'type'      => $error_type,
            'file'      => $file,
            'line'      => $line,
            'backtrace' => $backtrace,
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
    function addInlineCss(string $css): bool
    {
        if (empty($css)) {
            return false;
        }

        Utils::$context['css_header'] ??= [];

        Utils::$context['css_header'][] = $css;

        return true;
    }
}

if (! function_exists('addInlineJavaScript')) {
    function addInlineJavaScript(string $javascript, $defer = false): bool
    {
        if (empty($javascript)) {
            return false;
        }

        Utils::$context['javascript_inline'] ??= [];

        Utils::$context['javascript_inline'][($defer === true ? 'defer' : 'standard')][] = $javascript;

        return true;
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
        return in_array($permission, User::$me->permissions ?? []);
    }
}

if (! function_exists('boardsAllowedTo')) {
    function boardsAllowedTo(...$params): array
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
    function add_integration_function(...$parms): void
    {
    }
}

if (! function_exists('call_integration_hook')) {
    function call_integration_hook(...$parms): array
    {
        return [];
    }
}

if (! function_exists('remove_integration_function')) {
    function remove_integration_function(...$params): void
    {
    }
}

if (! function_exists('clean_cache')) {
    $GLOBALS['clean_cache_calls'] = [];

    function clean_cache(string $type = ''): void
    {
        $GLOBALS['clean_cache_calls'][] = [
            'type' => $type,
        ];
    }
}

if (! function_exists('cache_get_data')) {
    $GLOBALS['cache_get_data_calls'] = [];

    function cache_get_data(string $key, int $ttl = 120): mixed
    {
        $GLOBALS['cache_get_data_calls'][] = [
            'key' => $key,
            'ttl' => $ttl,
        ];

        return $GLOBALS['cache_get_data_return'] ?? null;
    }
}

if (! function_exists('cache_put_data')) {
    $GLOBALS['cache_put_data_calls'] = [];

    function cache_put_data(string $key, mixed $value, int $ttl = 120): void
    {
        $GLOBALS['cache_put_data_calls'][] = [
            'key' => $key,
            'value' => $value,
            'ttl' => $ttl,
        ];
    }
}

if (! function_exists('updateSettings')) {
    function updateSettings(array $settings): void
    {
        Config::$modSettings = array_merge(Config::$modSettings, $settings);
    }
}

if (! function_exists('smf_chmod')) {
    function smf_chmod(string $file): bool
    {
        return true;
    }
}

if (! function_exists('censorText')) {
    function censorText(&$text) {}
}

if (! function_exists('parse_bbc')) {
    function parse_bbc(
        string $message,
        bool $smileys = true,
        string|int $cache_id = '',
        array $parse_tags = []
    ): array|string {
        $pattern     = '/\[img](.*?)\[\/img]/i';
        $replacement = '<img src="$1" alt="">';
        $message     = preg_replace($pattern, $replacement, $message);

        $pattern_attr     = '/\[img\s+width=(\d+)\s+height=(\d+)](.*?)\[\/img]/i';
        $replacement_attr = '<img src="$3" alt="" width="$1" height="$2">';
        $message          = preg_replace($pattern_attr, $replacement_attr, $message);

        $pattern_short     = '/\[img=(.*?)]/i';
        $replacement_short = '<img src="$1" alt="">';

        return preg_replace($pattern_short, $replacement_short, $message);
    }
}

if (! function_exists('loadMemberData')) {
    function loadMemberData($users, $type = false, $set = 'normal'): array
    {
        return [];
    }
}

if (! function_exists('loadMemberContext')) {
    function loadMemberContext($user, $display_custom_fields = false): array
    {
        return [];
    }
}

if (! function_exists('shorten_subject')) {
    function shorten_subject(string $text, int $length = 150): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
    }
}

if (! function_exists('constructPageIndex')) {
    function constructPageIndex(...$params): string
    {
        return '<div>Mocked Page Index</div>';
    }
}

if (! function_exists('obExit')) {
    function obExit($header = null): void
    {
    }
}

if (! function_exists('redirectexit')) {
    $GLOBALS['redirectexit_calls'] = [];

    function redirectexit(string $url = ''): void
    {
        $GLOBALS['redirectexit_calls'][] = $url;
    }
}

if (! function_exists('un_htmlspecialchars')) {
    function un_htmlspecialchars(string $string): string
    {
        return $string;
    }
}

if (! function_exists('un_preparsecode')) {
    function un_preparsecode(string $string): string
    {
        return $string;
    }
}

if (! function_exists('theme_inline_permissions')) {
    function theme_inline_permissions($permission): void
    {
    }
}

if (! function_exists('checkSubmitOnce')) {
    function checkSubmitOnce($action): bool
    {
        return true;
    }
}

if (! function_exists('preparsecode')) {
    function preparsecode(&$message): void
    {
    }
}

if (! function_exists('getBoardList')) {
    function getBoardList(array $options = []): array
    {
        return $options;
    }
}

if (! function_exists('getLanguages')) {
    function getLanguages(): array
    {
        return [
            'english' => ['name' => 'English'],
            'russian' => ['name' => 'Russian'],
            'german'  => ['name' => 'German'],
        ];
    }
}

if (! function_exists('loadLanguage')) {
    function loadLanguage($filename, $lang = '', $fatal = true, $force_reload = false)
    {
    }
}

if (! function_exists('fetch_web_data')) {
    function fetch_web_data($url, $post_data = [], $keep_alive = false)
    {
        return json_encode(['donate' => [], 'download' => []]);
    }
}

if (! function_exists('smf_json_decode')) {
    function smf_json_decode($json, $returnAsArray = null)
    {
        return json_decode($json, $returnAsArray ?? true);
    }
}
if (! function_exists('checkSession')) {
    function checkSession($type = 'post', $from_action = '', $is_fatal = true): string
    {
        return '';
    }
}

if (! function_exists('logAction')) {
    function logAction(string $action, array $extra = []): int
    {
        return 1;
    }
}

if (! function_exists('create_control_richedit')) {
    function create_control_richedit(array $options): void
    {
    }
}

if (! function_exists('template_control_richedit')) {
    function template_control_richedit($editor_id, $smiley_container, $bbc_container): void
    {
    }
}

if (! function_exists('createList')) {
    function createList(array $options): void
    {
    }
}

if (! class_exists('SMF_BackgroundTask')) {
    class SMF_BackgroundTask
    {
        public const RECEIVE_NOTIFY_ALERT = 0x01;
        public const RECEIVE_NOTIFY_EMAIL = 0x02;

        protected array $_details = [];

        public function __construct(array $details = [])
        {
            $this->_details = $details;
        }
    }
}
