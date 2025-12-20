<?php declare(strict_types=1);

use Robo\Symfony\ConsoleIO;
use Robo\Tasks;

class RoboFile extends Tasks
{
	private array $baseExclusions = [
		'.idea',
		'src/Sources/index.php',
		'src/Themes/default/css/light_portal/plugins.css',
		'src/Themes/default/scripts/light_portal/plugins.js',
	];

	private array $defaultPlugins = [
		'CodeMirror',
		'HelloPortal',
		'ThemeSwitcher',
		'UserInfo',
	];

	private array $premiumPlugins = [
		'ApexCharts',
		'DiceBear',
		'ImageUpload',
		'IndexNow',
		'Jodit',
		'MediaBlock',
		'PageScroll',
	];

	private array $remotePlugins = [
		'AdsBlock',
		'BootstrapIcons',
		'ChessBoard',
		'Disqus',
		'EasyMarkdownEditor',
		'EhPortalMigration',
		'EzPortalMigration',
		'FaBoardIcons',
		'FacebookComments',
		'Giscus',
		'LatteLayouts',
		'LineAwesomeIcons',
		'Markdown',
		'MaterialDesignIcons',
		'Memory',
		'Optimus',
		'PluginMaker',
		'PrettyUrls',
		'SiteList',
		'Snowflakes',
		'Sudoku',
		'TagList',
		'TinyPortalMigration',
		'TopicRatingBar',
		'TwentyFortyEight',
		'TwigLayouts',
		'Uicons',
		'VkComments',
	];

	private array $partialTranslations = [
		'arabic',
		'czech',
		'danish',
		'dutch',
		'french',
		'german',
		'norwegian',
		'portuguese_pt',
		'spanish_es',
		'spanish_latin',
		'swedish',
		'ukrainian',
	];

	private string $version;

	private array $packageTypes = [
		'default',
		'geek',
		'develop',
	];

	public function __construct()
	{
		$this->version = $this->getVersionFromXml();
	}

	public function build(ConsoleIO $io, $opts = ['type' => false]): void
	{
		$type = $opts['type'];

		if (! $type) {
			$type = $io->choice('What is the package type?', $this->packageTypes, 0);
		}

		$yes = $io->confirm('Do you want to change version?');

		if ($yes) {
			$this->version = $io->ask('What is the package version?');

			$this->changeVersion($this->version);
		}

		switch ($type) {
			case 'geek':
				$this->buildGeek();
				break;
			case 'develop':
				$this->buildDevelop();
				break;
			default:
				$this->buildDefault();
		}

		$io->say('Done!');
	}

	public function buildDefault(): void
	{
		$file = "light_portal_{$this->getNormalizedVersion()}.tgz";

		$this->_remove($file);

		$this->taskPack($file)
			->addDir('.', 'src')
			->addFile('./LICENSE', 'LICENSE')
			->exclude(['.meta-storm.xml', 'composer.json', 'composer.lock', 'create_index.php', 'update_plugins.php', 'debug.blade.php'])
			->exclude([...$this->baseExclusions, ...$this->premiumPlugins, ...$this->remotePlugins])
			->exclude(['*(' . implode('|', $this->partialTranslations) . ').php'])
			->run();
	}

	public function buildGeek(): void
	{
		$file = "light_portal_{$this->getNormalizedVersion()}_geek_edition.tgz";

		$this->_remove($file);

		$this->taskPack($file)
			->addDir('.', 'src')
			->addFile('./LICENSE', 'LICENSE')
			->exclude(['.meta-storm.xml', 'composer.json', 'composer.lock', 'create_index.php', 'update_plugins.php', 'debug.blade.php'])
			->exclude([...$this->baseExclusions, ...$this->premiumPlugins])
			->exclude($this->getChildren())
			->exclude(['langs/(?!index|english).*\.php'])
			->exclude(['languages/LightPortal/(?!index|LightPortal\.english).*\.php'])
			->run();
	}

	public function buildDevelop(): void
	{
		$file = "light_portal_{$this->getNormalizedVersion()}_dev_edition.tgz";

		$this->_remove($file);

		$this->taskPack($file)
			->addDir('.', 'src')
			->addFile('./LICENSE', 'LICENSE')
			->exclude([...$this->baseExclusions, ...$this->premiumPlugins])
			->exclude(['langs/(?!index|english).*\.php'])
			->exclude(['languages/LightPortal/(?!index|LightPortal\.english).*\.php'])
			->run();
	}

	private function getVersionFromXml(): string
	{
		$xml = simplexml_load_file('src/package-info.xml');

		return (string) $xml->version;
	}

	private function getNormalizedVersion(): string
	{
		return str_replace(' ', '_', $this->version);
	}

	private function changeVersion(string $version): void
	{
		$this->taskReplaceInFile('src/package-info.xml')
			->regex('~(<version>)(.*?)(<\/version>)~')
			->to("<version>$version</version>")
			->run();

		$this->taskReplaceInFile('src/Sources/LightPortal/Hooks/Init.php')
			->regex("~'LP_VERSION',\s*'(\d+\.\d+(?:.\d+)?)'~")
			->to("'LP_VERSION', '$version'")
			->run();
	}

	private function getChildren(): array
	{
		$directories = glob(__DIR__ . '/src/Sources/LightPortal/Plugins/*', GLOB_ONLYDIR);

		return array_map(
			fn($path) => 'Plugins/' . basename($path),
			array_filter(
				$directories,
				fn($path) => ! in_array(basename($path), $this->defaultPlugins)
			)
		);
	}
}
