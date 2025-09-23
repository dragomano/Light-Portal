<?php

declare(strict_types=1);

namespace Tests;

use Bugo\FontAwesome\Icon;
use Bugo\LightPortal\Enums\ContentClass;
use Bugo\LightPortal\Enums\ContentType;
use Bugo\LightPortal\Enums\EntryType;
use Bugo\LightPortal\Enums\Placement;
use Bugo\LightPortal\Enums\TitleClass;
use Faker\Factory;
use Faker\Generator;

class Fixtures
{
    protected static Generator $faker;

    public const AREAS = ['all', 'portal', 'forum', 'pages', 'boards', 'topics'];

    public const LANGUAGES = ['english', 'russian', 'german', 'french', 'spanish'];

    public const TYPES = ['block', 'page', 'category', 'tag'];

    public const BLOCK_PARAMS = ['hide_header', 'no_content_class', 'link_in_title'];

    public const PAGE_PARAMS = ['show_author_and_date', 'show_related_pages', 'allow_comments'];

    public static function getRandomIcon(): string
    {
        return Icon::random(useOldStyle: true);
    }

    /**
     * Generate random parameters from given array of parameter names
     */
    private static function generateRandomParams(array $paramNames): array
    {
        $params = [];

        foreach ($paramNames as $paramName) {
            $params[$paramName] = static::faker()->randomElement(['0', '1']);
        }

        return $params;
    }

    protected static function faker(string $locale = 'en_US'): Generator
    {
        return static::$faker ??= Factory::create($locale);
    }

    /**
     * Generate localized content for titles, contents, descriptions
     */
    private static function generateLocalizedContent(callable $contentGenerator): array
    {
        $result = [];

        foreach (static::LANGUAGES as $lang) {
            $faker = $lang === 'russian' ? static::faker('ru_RU') : static::faker();
            $result[$lang] = $contentGenerator($faker);
        }

        return $result;
    }

    /**
     * Generate base data structure for entities
     */
    private static function generateBaseData(int $count, array $fields): array
    {
        $data = [];
        for ($i = 1; $i <= $count; $i++) {
            $item = array_map(function ($generator) use ($i) {
                return is_callable($generator) ? $generator($i) : $generator;
            }, $fields);

            $data[$i] = $item;
        }

        return $data;
    }

    public static function getBlocksData(int $count = 1): array
    {
        return static::generateBaseData($count, [
            'block_id' => fn($i) => (string) ($i - 1),
            'icon' => fn() => static::getRandomIcon(),
            'type' => fn() => static::faker()->randomElement([...ContentType::names(), 'markdown']),
            'placement' => fn() => static::faker()->randomElement(Placement::names()),
            'priority' => fn() => (string) static::faker()->numberBetween(1, 10),
            'permissions' => fn() => (string) static::faker()->numberBetween(0, 3),
            'status' => fn() => (string) static::faker()->numberBetween(0, 1),
            'areas' => fn() => static::faker()->randomElement(self::AREAS),
            'title_class' => fn() => static::faker()->randomElement(TitleClass::values()),
            'content_class' => fn() => static::faker()->randomElement(ContentClass::values()),
            'titles' => fn() => static::generateLocalizedContent(fn($faker) => $faker->sentence(3)),
            'contents' => fn() => static::generateLocalizedContent(fn($faker) => $faker->paragraph(2)),
            'params' => fn() => static::generateRandomParams(self::BLOCK_PARAMS),
            'created_at' => strtotime('-5 months'),
            'updated_at' => strtotime('-1 month'),
        ]);
    }

    public static function getPagesData(int $count = 1): array
    {
        return static::generateBaseData($count, [
            'page_id' => fn($i) => (string) $i,
            'category_id' => fn() => (string) static::faker()->numberBetween(1, 5),
            'author_id' => fn() => (string) static::faker()->numberBetween(1, 100),
            'slug' => fn() => static::faker()->slug(3),
            'type' => fn() => static::faker()->randomElement([...ContentType::names(), 'markdown']),
            'entry_type' => fn() => static::faker()->randomElement(EntryType::names()),
            'permissions' => fn() => (string) static::faker()->numberBetween(0, 3),
            'status' => fn() => (string) static::faker()->numberBetween(0, 1),
            'num_views' => fn() => (string) static::faker()->numberBetween(0, 1000),
            'num_comments' => fn() => (string) static::faker()->numberBetween(0, 50),
            'created_at' => fn() => (string) static::faker()->dateTimeBetween('-1 year', '-1 month')->getTimestamp(),
            'updated_at' => fn() => (string) static::faker()->dateTimeBetween('-1 month')->getTimestamp(),
            'deleted_at' => '0',
            'titles' => fn() => static::generateLocalizedContent(fn($faker) => $faker->sentence(4)),
            'contents' => fn() => static::generateLocalizedContent(fn($faker) => $faker->paragraph(3)),
            'descriptions' => fn() => static::generateLocalizedContent(fn($faker) => $faker->sentence(10)),
            'params' => fn() => static::generateRandomParams(self::PAGE_PARAMS),
            'comments' => fn($i) => static::generateComments($i, static::faker()->numberBetween(0, 5)),
        ]);
    }

    private static function generateComments(int $pageId, int $count): array
    {
        return array_fill(1, $count, [
            'id' => (string) $pageId,
            'parent_id' => '0',
            'author_id' => (string) static::faker()->numberBetween(1, 100),
            'message' => static::faker()->paragraph(2),
            'created_at' => (string) strtotime('-3 months'),
        ]);
    }

    public static function getCategoriesData(int $count = 1): array
    {
        return static::generateBaseData($count, [
            'category_id' => fn($i) => (string) ($i - 1),
            'slug' => fn() => static::faker()->unique()->slug(2),
            'icon' => fn() => static::getRandomIcon(),
            'priority' => fn() => (string) static::faker()->numberBetween(1, 10),
            'status' => fn() => (string) static::faker()->numberBetween(0, 1),
            'titles' => fn() => static::generateLocalizedContent(fn($faker) => $faker->words(2, true)),
            'descriptions' => fn() => static::generateLocalizedContent(fn($faker) => $faker->sentence()),
            'created_at' => strtotime('-7 months'),
            'updated_at' => strtotime('-2 weeks'),
        ]);
    }

    public static function getTagsData(int $count = 1): array
    {
        return static::generateBaseData($count, [
            'tag_id' => fn($i) => (string) ($i - 1),
            'slug' => fn() => static::faker()->unique()->slug(2),
            'icon' => fn() => static::getRandomIcon(),
            'status' => fn() => (string) static::faker()->numberBetween(0, 1),
            'titles' => fn() => static::generateLocalizedContent(fn($faker) => $faker->word()),
            'pages' => fn() => static::generateTagPages(static::faker()->numberBetween(1, 5)),
        ]);
    }

    private static function generateTagPages(int $count): array
    {
        return array_fill(0, $count, ['id' => (string) static::faker()->numberBetween(1, 100)]);
    }

    public static function getTranslationData(int $count = 2): array
    {
        $data = [];
        $languageLocales = [
            'english' => 'en_US',
            'russian' => 'ru_RU',
            'german' => 'de_DE',
            'french' => 'fr_FR',
            'spanish' => 'es_ES'
        ];

        $languages = array_keys($languageLocales);

        for ($i = 1; $i <= $count; $i++) {
            $type = static::faker()->randomElement(static::TYPES);
            $lang = static::faker()->randomElement($languages);

            $data[] = [
                'item_id'     => (string) static::faker()->numberBetween(1, 100),
                'type'        => $type,
                'lang'        => $lang,
                'title'       => static::faker($languageLocales[$lang])->sentence(3),
                'content'     => static::faker($languageLocales[$lang])->paragraph(2),
                'description' => static::faker($languageLocales[$lang])->optional(0.7)->sentence(5),
            ];
        }

        return $data;
    }

    public static function getParamsData(int $count = 2): array
    {
        $data = [];
        $types = ['block', 'page'];

        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'item_id' => (string) static::faker()->numberBetween(1, 100),
                'type'    => $type = static::faker()->randomElement($types),
                'name'    => static::faker()->randomElement($type === 'block' ? static::BLOCK_PARAMS : static::PAGE_PARAMS),
                'value'   => static::faker()->randomElement([
                    static::faker()->numberBetween(1, 20),
                    static::faker()->randomElement(['grid', 'list', 'compact', 'featured']),
                    static::faker()->randomElement(['asc', 'desc', 'random']),
                    static::faker()->numberBetween(300, 3600),
                    static::faker()->randomElement(['0', '1']),
                    static::faker()->boolean(),
                    static::faker()->hexColor(),
                    static::faker()->word(),
                ]),
            ];
        }

        return $data;
    }

    /**
     * Generates data for validation testing with intentional errors
     */
    public static function getInvalidModelData(string $modelType = 'block'): array
    {
        return match ($modelType) {
            'block' => [
                'id'          => 'invalid_id',
                'icon'        => '', // empty icon
                'type'        => 'invalid_type',
                'placement'   => 'invalid_placement',
                'priority'    => -1, // negative value
                'permissions' => 999, // value out of range
                'status'      => 999, // value out of range
                'title'       => '', // empty title
                'content'     => '', // empty content
            ],
            'page' => [
                'id'           => 'invalid_id',
                'slug'         => '', // empty slug
                'type'         => 'invalid_type',
                'entry_type'   => 'invalid_entry_type',
                'num_views'    => -1, // negative value
                'num_comments' => -1, // negative value
                'title'        => '', // empty title
                'content'      => '', // empty content
            ],
            'category' => [
                'id'          => 'invalid_id',
                'slug'        => '', // empty slug
                'icon'        => '', // empty icon
                'priority'    => -1, // negative value
                'status'      => 999, // value out of range
                'title'       => '', // empty title
                'description' => '', // empty description
            ],
            default => static::getInvalidModelData(),
        };
    }

    /**
     * Generates data for testing boundary values
     */
    public static function getBoundaryTestData(string $modelType = 'block'): array
    {
        return match ($modelType) {
            'block' => [
                'min_values' => [
                    'priority' => 1,
                    'permissions' => 0,
                    'status' => 0,
                ],
                'max_values' => [
                    'priority' => 10,
                    'permissions' => 3,
                    'status' => 1,
                ],
                'edge_cases' => [
                    'very_long_title' => static::faker()->sentence(50),
                    'very_long_content' => static::faker()->paragraph(20),
                    'special_chars_title' => 'Title with spéciál chärs & <script>',
                ],
            ],
            default => static::getBoundaryTestData(),
        };
    }

    /**
     * Generates realistic content for blocks based on their type
     * Useful for testing content rendering and formatting
     */
    public static function getRealisticBlockContent(string $type = 'html', string $complexity = 'medium'): string
    {
        return match ($type) {
            'bbc'      => static::generateBbcContent($complexity),
            'markdown' => static::generateMarkdownContent($complexity),
            'php'      => static::generatePhpContent($complexity),
            default    => static::generateHtmlContent($complexity),
        };
    }

    private static function generateHtmlContent(string $complexity): string
    {
        $paragraphs = match ($complexity) {
            'simple'  => 1,
            'complex' => 4,
            default   => 2,
        };

        $content = '';
        for ($i = 0; $i < $paragraphs; $i++) {
            $content .= '<p>' . static::faker()->paragraph() . '</p>';
        }

        // Add some HTML elements based on complexity
        if ($complexity !== 'simple') {
            $elements = ['<strong>' . static::faker()->sentence(3) . '</strong>'];
            if ($complexity === 'complex') {
                $elements[] = '<ul>';
                for ($i = 0; $i < 3; $i++) {
                    $elements[] = '<li>' . static::faker()->sentence(4) . '</li>';
                }
                $elements[] = '</ul>';
                $elements[] = '<blockquote>' . static::faker()->paragraph() . '</blockquote>';
            }
            $content .= implode('', $elements);
        }

        return $content;
    }

    private static function generateBbcContent(string $complexity): string
    {
        $sentences = match ($complexity) {
            'simple'  => 2,
            'complex' => 8,
            default   => 4,
        };

        $content = '';
        for ($i = 0; $i < $sentences; $i++) {
            $content .= '[p]' . static::faker()->sentence() . '[/p]';
        }

        // Add BBC formatting
        if ($complexity !== 'simple') {
            $content .= '[b]' . static::faker()->sentence(3) . '[/b]';
            if ($complexity === 'complex') {
                $content .= '[list]';
                for ($i = 0; $i < 3; $i++) {
                    $content .= '[li]' . static::faker()->sentence(3) . '[/li]';
                }
                $content .= '[/list]';
                $content .= '[quote]' . static::faker()->paragraph() . '[/quote]';
            }
        }

        return $content;
    }

    private static function generateMarkdownContent(string $complexity): string
    {
        $content = '#' . static::faker()->sentence(3) . "\n\n";

        $paragraphs = match ($complexity) {
            'simple'  => 2,
            'complex' => 6,
            default   => 4,
        };

        for ($i = 0; $i < $paragraphs; $i++) {
            $content .= static::faker()->paragraph() . "\n\n";
        }

        if ($complexity !== 'simple') {
            $content .= '**' . static::faker()->sentence(3) . '**' . "\n\n";
            if ($complexity === 'complex') {
                $content .= '- ' . static::faker()->sentence(3) . "\n";
                $content .= '- ' . static::faker()->sentence(3) . "\n";
                $content .= '- ' . static::faker()->sentence(3) . "\n\n";
                $content .= '> ' . static::faker()->paragraph() . "\n\n";
            }
        }

        return $content;
    }

    private static function generatePhpContent(string $complexity): string
    {
        return match ($complexity) {
            'medium' => '<?php
$variable = "' . static::faker()->word() . '";
echo "<p>" . $variable . "</p>";
?>',
            'complex' => '<?php
$data = [
    "title"   => "' . static::faker()->sentence(3) . '",
    "content" => "' . static::faker()->paragraph() . '",
    "author"  => "' . static::faker()->name() . '"
];

foreach ($data as $key => $value) {
    echo "<div class=\'" . $key . "\'>" . $value . "</div>";
}
?>',
            default => '<?php echo "' . static::faker()->sentence(3) . '"; ?>',
        };
    }

    /**
     * Generates realistic page content with different sections
     * Useful for testing page rendering and layout
     */
    public static function getRealisticPageContent(): array
    {
        return [
            'title'            => static::faker()->sentence(4),
            'content'          => static::generateHtmlContent('complex'),
            'description'      => static::faker()->sentence(8),
            'meta_title'       => static::faker()->sentence(3),
            'meta_description' => static::faker()->sentence(10),
            'meta_keywords'    => static::faker()->words(5, true),
            'introduction'     => '<div class="intro">' . static::faker()->paragraph(2) . '</div>',
            'main_content'     => '<div class="content">' . static::generateHtmlContent('complex') . '</div>',
            'sidebar_content'  => '<div class="sidebar">' . static::faker()->paragraph() . '</div>',
            'conclusion'       => '<div class="conclusion">' . static::faker()->paragraph() . '</div>',
        ];
    }

    /**
     * Generates category-specific content based on category type
     * Useful for testing category-specific templates and styling
     */
    public static function getCategorySpecificContent(string $categoryType = 'general'): array
    {
        $templates = [
            'general'          => [
                'description'  => static::faker()->sentence(),
                'welcome_text' => 'Welcome to our general content section.',
                'icon'         => 'fas fa-folder',
            ],
            'news'             => [
                'description'  => 'Latest news and updates from around the world.',
                'welcome_text' => 'Stay informed with our latest news articles.',
                'icon'         => 'fas fa-newspaper',
            ],
            'tutorials'        => [
                'description'  => 'Step-by-step guides and educational content.',
                'welcome_text' => 'Learn new skills with our comprehensive tutorials.',
                'icon'         => 'fas fa-graduation-cap',
            ],
            'gallery'          => [
                'description'  => 'Visual content and media gallery.',
                'welcome_text' => 'Explore our visual content collection.',
                'icon'         => 'fas fa-images',
            ],
        ];

        $template = $templates[$categoryType] ?? $templates['general'];

        return [
            'title'               => static::faker()->words(2, true),
            'description'         => $template['description'],
            'welcome_text'        => $template['welcome_text'],
            'icon'                => $template['icon'],
            'featured_content'    => static::generateHtmlContent('medium'),
            'category_guidelines' => static::faker()->paragraph(2),
        ];
    }

    /**
     * Generates multilingual content for testing internationalization
     * Useful for testing translation and locale-specific content
     */
    public static function getMultilingualContent(): array
    {
        $languages = ['english', 'russian', 'german', 'french', 'spanish'];
        $content = [];

        foreach ($languages as $lang) {
            $content[$lang] = [
                'title'           => static::faker()->sentence(3),
                'content'         => static::faker()->paragraph(2),
                'description'     => static::faker()->sentence(5),
                'welcome_message' => static::getLocalizedWelcome($lang),
            ];
        }

        return $content;
    }

    private static function getLocalizedWelcome(string $lang): string
    {
        return match ($lang) {
            'russian' => 'Добро пожаловать в наш портал',
            'german'  => 'Willkommen auf unserem Portal',
            'french'  => 'Bienvenue sur notre portail',
            'spanish' => 'Bienvenido a nuestro portal',
            default   => 'Welcome to our portal',
        };
    }

    public static function getBlockXmlData(int $count = 1): string
    {
        return static::generateXml(static::getBlocksData($count), 'blocks', ['id_attribute' => 'block_id']);
    }

    public static function getCategoryXmlData(int $count = 1): string
    {
        return static::generateXml(static::getCategoriesData($count), 'categories', ['id_attribute' => 'category_id']);
    }

    public static function getPageXmlData(int $count = 1): string
    {
        return static::generateXml(static::getPagesData($count), 'pages', ['id_attribute' => 'page_id']);
    }

    public static function getTagXmlData(int $count = 1): string
    {
        return static::generateXml(static::getTagsData($count), 'tags', ['id_attribute' => 'tag_id']);
    }

    private static function generateXml(array $data, string $rootElement, array $itemConfig = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= "<light_portal>\n";
        $xml .= str_repeat(' ', 4) . "<$rootElement>\n";

        foreach ($data as $item) {
            $itemElement = $itemConfig['item_element'] ?? 'item';
            $idAttr = $itemConfig['id_attribute'] ?? null;

            $attributes = '';
            if ($idAttr && isset($item[$idAttr])) {
                $attributes = " $idAttr=\"" . htmlspecialchars($item[$idAttr]) . "\"";
            }

            $xml .= str_repeat(' ', 8) . "<$itemElement$attributes>\n";

            foreach ($item as $key => $value) {
                if ($key === $idAttr) {
                    continue;
                }

                if (is_array($value)) {
                    if (static::isAssocArray($value)) {
                        // Localized content (titles, contents, descriptions)
                        if (in_array($key, ['titles', 'contents', 'descriptions'])) {
                            $xml .= str_repeat(' ', 12) . "<$key>\n";
                            foreach ($value as $lang => $text) {
                                $xml .= str_repeat(' ', 16) . "<$key lang=\"" . htmlspecialchars($lang) . "\">";
                                $xml .= (! str_contains($text, '<')) ? htmlspecialchars($text) : "<![CDATA[$text]]>";
                                $xml .= "</$key>\n";
                            }

                            $xml .= str_repeat(' ', 12) . "</$key>\n";
                        } elseif ($key === 'params') {
                            // Params as attributes
                            $xml .= str_repeat(' ', 12) . "<$key>\n";
                            foreach ($value as $paramName => $paramValue) {
                                $xml .= str_repeat(' ', 16) . "<" . htmlspecialchars($paramName) . ">" . htmlspecialchars($paramValue) . "</" . htmlspecialchars($paramName) . ">\n";
                            }

                            $xml .= str_repeat(' ', 12) . "</$key>\n";
                        } elseif ($key === 'comments') {
                            // Comments
                            $xml .= str_repeat(' ', 12) . "<$key>\n";
                            foreach ($value as $comment) {
                                $xml .= str_repeat(' ', 16) . "<comment>\n";
                                foreach ($comment as $commentKey => $commentValue) {
                                    if (is_array($commentValue)) {
                                        $xml .= str_repeat(' ', 20) . "<$commentKey><![CDATA[" . implode(', ', $commentValue) . "]]></$commentKey>\n";
                                    } else {
                                        $xml .= str_repeat(' ', 20) . "<$commentKey>" . htmlspecialchars((string)$commentValue) . "</$commentKey>\n";
                                    }
                                }

                                $xml .= str_repeat(' ', 16) . "</comment>\n";
                            }

                            $xml .= str_repeat(' ', 12) . "</$key>\n";
                        } elseif ($key === 'pages') {
                            // Tag pages
                            $xml .= str_repeat(' ', 12) . "<$key>\n";
                            foreach ($value as $page) {
                                $xml .= str_repeat(' ', 16) . "<page id=" . htmlspecialchars($page['id']) . " />\n";
                            }

                            $xml .= str_repeat(' ', 12) . "</$key>\n";
                        }
                    } else {
                        // Numeric arrays of assoc (like pages)
                        if ($key === 'pages') {
                            $xml .= str_repeat(' ', 12) . "<$key>\n";
                            foreach ($value as $page) {
                                if (is_array($page) && isset($page['id'])) {
                                    $xml .= str_repeat(' ', 16) . "<page id=\"" . htmlspecialchars($page['id']) . "\"/>\n";
                                }
                            }

                            $xml .= str_repeat(' ', 12) . "</$key>\n";
                        } else {
                            // Other numeric arrays
                            $xml .= str_repeat(' ', 12) . "<$key><![CDATA[" . implode(', ', $value) . "]]></$key>\n";
                        }
                    }
                } else {
                    $xml .= str_repeat(' ', 12) . "<$key>" . htmlspecialchars((string)$value) . "</$key>\n";
                }
            }

            $xml .= str_repeat(' ', 8) . "</$itemElement>\n";
        }

        $xml .= str_repeat(' ', 4) . "</$rootElement>\n";
        $xml .= "</light_portal>\n";

        return $xml;
    }

    private static function isAssocArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
