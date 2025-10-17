<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace Bugo\LightPortal\Database\Migrations;

use Bugo\Compat\Cache\CacheApi;
use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Database\Migrations\Creators\BlocksTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\CategoriesTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\CommentsTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\PagesTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\PageTagTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\ParamsTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\PluginsTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\TableCreatorInterface;
use Bugo\LightPortal\Database\Migrations\Creators\TagsTableCreator;
use Bugo\LightPortal\Database\Migrations\Creators\TranslationsTableCreator;
use Bugo\LightPortal\Database\Migrations\Upgraders\BlocksTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\CategoriesTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\CommentsTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\PagesTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\TableUpgraderInterface;
use Bugo\LightPortal\Database\Migrations\Upgraders\TagsTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\TitlesTableUpgrader;
use Bugo\LightPortal\Database\Migrations\Upgraders\TranslationsTableUpgrader;
use Bugo\LightPortal\Database\PortalAdapterFactory;
use Bugo\LightPortal\Database\PortalSql;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\Utils\Traits\HasRequest;

if (! defined('SMF'))
	die('No direct access...');

class Installer implements InstallerInterface
{
	use HasRequest;

	public function __construct(protected ?PortalSqlInterface $sql = null)
	{
		$this->sql ??= new PortalSql(PortalAdapterFactory::create());
	}

	public function install(): bool
	{
		$this->processTables('install');
		$this->cleanBackgroundTasks();
		$this->setDefaultSettings();
		$this->setDirectoryPermissions();

		return true;
	}

	public function uninstall(): bool
	{
		$this->cleanBackgroundTasks();
		$this->updateSettings();

		if ($this->post()->hasNot('do_db_changes'))
			return true;

		$this->processTables('uninstall');
		$this->removePortalSettings();
		$this->removePortalPermissions();
		$this->updateSettings();

		return true;
	}

	public function upgrade(): bool
	{
		$this->processUpgradeTasks();

		CacheApi::clean();

		return true;
	}

	protected function processTables(string $mode): void
	{
		$creators = $this->getCreators();

		foreach ($creators as $creatorClass) {
			$creator = new $creatorClass($this->sql);
			if (! $creator instanceof TableCreatorInterface) {
				continue;
			}

			if ($mode === 'install') {
				$creator->createTable();
				$creator->insertDefaultData();
			} elseif ($mode === 'uninstall') {
				$creator->dropTable();
			}
		}
	}

	protected function processUpgradeTasks(): void
	{
		$upgraders = $this->getUpgraders();

		foreach ($upgraders as $upgraderClass) {
			$upgrader = new $upgraderClass($this->sql);
			if (! $upgrader instanceof TableUpgraderInterface) {
				continue;
			}

			$upgrader->updateTable();
		}
	}

	private function getCreators(): array
	{
		return [
			BlocksTableCreator::class,
			CategoriesTableCreator::class,
			CommentsTableCreator::class,
			PageTagTableCreator::class,
			PagesTableCreator::class,
			ParamsTableCreator::class,
			PluginsTableCreator::class,
			TagsTableCreator::class,
			TranslationsTableCreator::class,
		];
	}

	private function getUpgraders(): array
	{
		return [
			TitlesTableUpgrader::class,
			PagesTableUpgrader::class,
			BlocksTableUpgrader::class,
			CategoriesTableUpgrader::class,
			TagsTableUpgrader::class,
			TranslationsTableUpgrader::class,
			CommentsTableUpgrader::class,
		];
	}

	protected function cleanBackgroundTasks(): void
	{
		$delete = $this->sql->delete('background_tasks');
		$delete->where->like('task_file', '%$sourcedir/LightPortal%');

		$this->sql->execute($delete);
	}

	protected function setDefaultSettings(): void
	{
		$defaultSettings = [
			'lp_weekly_cleaning'     => '0',
			'lp_enabled_plugins'     => 'CodeMirror,HelloPortal,ThemeSwitcher,UserInfo',
			'lp_frontpage_layout'    => 'default.blade.php',
			'lp_comment_block'       => 'none',
			'lp_permissions_default' => '0',
			'lp_fa_source'           => 'css_cdn',
		];

		$settings = array_filter(
			$defaultSettings,
			fn($key) => ! isset(Config::$modSettings[$key]),
			ARRAY_FILTER_USE_KEY
		);

		Config::updateModSettings($settings);
	}

	protected function setDirectoryPermissions(): void
	{
		$directories = [
			'/LightPortal',
			'/languages/LightPortal',
			'/css/light_portal',
			'/scripts/light_portal',
		];

		foreach ($directories as $dir) {
			$path = Theme::$current->settings['default_theme_dir'] . $dir;
			if (! @is_writable($path)) {
				Utils::makeWritable($path);
			}
		}
	}

	protected function removePortalSettings(): void
	{
		$delete = $this->sql->delete('settings');
		$delete->where->like('variable', 'lp_%');

		$this->sql->execute($delete);
	}

	protected function removePortalPermissions(): void
	{
		$delete = $this->sql->delete('permissions');
		$delete->where->like('permission', '%light_portal%');

		$this->sql->execute($delete);
	}

	protected function updateSettings(): void
	{
		$update = $this->sql->update('settings');
		$update->set(['value' => (string) time()]);
		$update->where(['variable' => 'settings_updated']);

		$this->sql->execute($update);
	}
}
