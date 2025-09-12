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

namespace Bugo\LightPortal\Migrations;

use Bugo\Compat\Cache\CacheApi;
use Bugo\Compat\Config;
use Bugo\Compat\Theme;
use Bugo\Compat\Utils;
use Bugo\LightPortal\Migrations\Creators\BlocksTableCreator;
use Bugo\LightPortal\Migrations\Creators\CategoriesTableCreator;
use Bugo\LightPortal\Migrations\Creators\CommentsTableCreator;
use Bugo\LightPortal\Migrations\Creators\PageTagTableCreator;
use Bugo\LightPortal\Migrations\Creators\PagesTableCreator;
use Bugo\LightPortal\Migrations\Creators\ParamsTableCreator;
use Bugo\LightPortal\Migrations\Creators\PluginsTableCreator;
use Bugo\LightPortal\Migrations\Creators\TableCreatorInterface;
use Bugo\LightPortal\Migrations\Creators\TagsTableCreator;
use Bugo\LightPortal\Migrations\Creators\TranslationsTableCreator;
use Bugo\LightPortal\Migrations\Upgraders\TitlesTableUpgrader;
use Bugo\LightPortal\Migrations\Upgraders\CategoriesUpgradeTask;
use Bugo\LightPortal\Migrations\Upgraders\TableUpgraderInterface;
use Bugo\LightPortal\Utils\Traits\HasRequest;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;

use function array_filter;
use function is_writable;

class Installer implements InstallerInterface
{
	use HasRequest;

	public function __construct(private ?PortalAdapter $adapter = null, private ?Sql $sql = null)
	{
		$this->adapter = $this->adapter ?? PortalAdapterFactory::create();
		$this->sql = $sql ?? new Sql($this->adapter);
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

	private function processTables(string $mode): void
	{
		$creators = $this->getCreators();

		/* @var TableCreatorInterface[] $creators */
		foreach ($creators as $creatorClass) {
			$creator = new $creatorClass($this->adapter, $this->sql);

			if ($mode === 'install') {
				$creator->createTable();
				$creator->insertDefaultData();
			} elseif ($mode === 'uninstall') {
				$creator->dropTable();
			}
		}
	}

	private function processUpgradeTasks(): void
	{
		$upgradeTasks = $this->getUpgradeTasks();

		/* @var TableUpgraderInterface[] $upgradeTasks */
		foreach ($upgradeTasks as $taskClass) {
			$task = new $taskClass($this->adapter, $this->sql);
			$task->upgrade();
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

	private function getUpgradeTasks(): array
	{
		return [
			TitlesTableUpgrader::class,
			CategoriesUpgradeTask::class,
		];
	}

	private function cleanBackgroundTasks(): void
	{
		$delete = new Delete($this->adapter->getPrefix() . 'background_tasks');
		$delete->where(['task_file LIKE ?' => '%$sourcedir/LightPortal%']);

		$statement = $this->sql->prepareStatementForSqlObject($delete);
		$statement->execute();
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

	private function removePortalSettings(): void
	{
		$select = new Select($this->adapter->getPrefix() . 'settings');
		$select->where->like('variable', 'lp_%');

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute();

		$settingsToRemove = [];
		foreach ($result as $row) {
			$settingsToRemove[] = $row['variable'];
		}

		if (! empty($settingsToRemove)) {
			$delete = new Delete($this->adapter->getPrefix() . 'settings');
			$delete->where->in('variable', $settingsToRemove);

			$statement = $this->sql->prepareStatementForSqlObject($delete);
			$statement->execute();
		}
	}

	private function removePortalPermissions(): void
	{
		$delete = new Delete($this->adapter->getPrefix() . 'permissions');
		$delete->where->like('permission', '%light_portal%');

		$statement = $this->sql->prepareStatementForSqlObject($delete);
		$statement->execute();
	}

	private function updateSettings(): void
	{
		$update = new Update($this->adapter->getPrefix() . 'settings');
		$update->set(['value' => (string) time()]);
		$update->where(['variable' => 'settings_updated']);

		$statement = $this->sql->prepareStatementForSqlObject($update);
		$statement->execute();
	}
}
