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

namespace Bugo\LightPortal;

use Bugo\Bricks\Breadcrumbs\BreadcrumbBuilder;
use Bugo\Bricks\Breadcrumbs\BreadcrumbPresenter;
use Bugo\Bricks\Forms\FormPresenter;
use Bugo\Bricks\Forms\FormRenderer;
use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Bricks\Tables\TablePresenter;
use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\CardList;
use Bugo\LightPortal\Actions\CardListInterface;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Actions\Comment;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Areas\BlockArea;
use Bugo\LightPortal\Areas\CategoryArea;
use Bugo\LightPortal\Areas\ConfigArea;
use Bugo\LightPortal\Areas\Configs\BasicConfig;
use Bugo\LightPortal\Areas\Configs\ExtraConfig;
use Bugo\LightPortal\Areas\Configs\FeedbackConfig;
use Bugo\LightPortal\Areas\Configs\MiscConfig;
use Bugo\LightPortal\Areas\Configs\PanelConfig;
use Bugo\LightPortal\Areas\CreditArea;
use Bugo\LightPortal\Areas\PageArea;
use Bugo\LightPortal\Areas\PluginArea;
use Bugo\LightPortal\Areas\TagArea;
use Bugo\LightPortal\Articles\BoardArticle;
use Bugo\LightPortal\Articles\ChosenPageArticle;
use Bugo\LightPortal\Articles\ChosenTopicArticle;
use Bugo\LightPortal\Articles\PageArticle;
use Bugo\LightPortal\Articles\TopicArticle;
use Bugo\LightPortal\Database\PortalAdapterFactory;
use Bugo\LightPortal\Database\PortalSql;
use Bugo\LightPortal\Database\PortalSqlInterface;
use Bugo\LightPortal\DataHandlers\Exports\BlockExport;
use Bugo\LightPortal\DataHandlers\Exports\CategoryExport;
use Bugo\LightPortal\DataHandlers\Exports\PageExport;
use Bugo\LightPortal\DataHandlers\Exports\PluginExport;
use Bugo\LightPortal\DataHandlers\Exports\TagExport;
use Bugo\LightPortal\DataHandlers\Imports\BlockImport;
use Bugo\LightPortal\DataHandlers\Imports\CategoryImport;
use Bugo\LightPortal\DataHandlers\Imports\PageImport;
use Bugo\LightPortal\DataHandlers\Imports\PluginImport;
use Bugo\LightPortal\DataHandlers\Imports\TagImport;
use Bugo\LightPortal\Events\EventManager;
use Bugo\LightPortal\Events\EventManagerFactory;
use Bugo\LightPortal\Lists\BlockList;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Models\BlockFactory;
use Bugo\LightPortal\Models\CategoryFactory;
use Bugo\LightPortal\Models\PageFactory;
use Bugo\LightPortal\Models\TagFactory;
use Bugo\LightPortal\Plugins\AssetHandler;
use Bugo\LightPortal\Plugins\ConfigHandler;
use Bugo\LightPortal\Plugins\LangHandler;
use Bugo\LightPortal\Plugins\PluginHandler;
use Bugo\LightPortal\Renderers\Blade;
use Bugo\LightPortal\Renderers\RendererInterface;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Repositories\BlockRepositoryInterface;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Repositories\CategoryRepositoryInterface;
use Bugo\LightPortal\Repositories\CommentRepository;
use Bugo\LightPortal\Repositories\CommentRepositoryInterface;
use Bugo\LightPortal\Repositories\PageListRepository;
use Bugo\LightPortal\Repositories\PageListRepositoryInterface;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Repositories\PageRepositoryInterface;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Repositories\PluginRepositoryInterface;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\Repositories\TagRepositoryInterface;
use Bugo\LightPortal\UI\Breadcrumbs\BreadcrumbRenderer;
use Bugo\LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use Bugo\LightPortal\UI\Partials\SelectRenderer;
use Bugo\LightPortal\UI\Tables\TableRenderer;
use Bugo\LightPortal\UI\View;
use Bugo\LightPortal\Utils\Cache;
use Bugo\LightPortal\Utils\CacheInterface;
use Bugo\LightPortal\Utils\ErrorHandler;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\FileInterface;
use Bugo\LightPortal\Utils\Filesystem;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\Notifier;
use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\PostInterface;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\RequestInterface;
use Bugo\LightPortal\Utils\Response;
use Bugo\LightPortal\Utils\Session;
use Bugo\LightPortal\Utils\SessionManager;
use Bugo\LightPortal\Validators\BlockValidator;
use Bugo\LightPortal\Validators\CategoryValidator;
use Bugo\LightPortal\Validators\PageValidator;
use Bugo\LightPortal\Validators\TagValidator;
use League\Container\ServiceProvider\AbstractServiceProvider;

if (! defined('SMF'))
	die('No direct access...');

class ServiceProvider extends AbstractServiceProvider
{
	private ?array $serviceIds = null;

	public function provides(string $id): bool
	{
		if ($this->serviceIds === null) {
			$this->serviceIds = array_column($this->getFlattenedDefinitions(), 'id');
		}

		return in_array($id, $this->serviceIds, true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		foreach ($this->getFlattenedDefinitions() as $definition) {
			if (isset($definition['concrete'])) {
				$serviceDefinition = $container->add($definition['id'], $definition['concrete']);
			} else {
				$serviceDefinition = $container->add($definition['id']);
			}

			if (isset($definition['arguments']) && is_array($definition['arguments'])) {
				$serviceDefinition->addArguments($definition['arguments']);
			}

			if (isset($definition['shared'])) {
				$serviceDefinition->setShared($definition['shared']);
			}
		}
	}

	private function getFlattenedDefinitions(): array
	{
		return array_merge(...array_values($this->getDefinitions()));
	}

	private function getDefinitions(): array
	{
		return [
			'core' => [
				[
					'id' => PortalSqlInterface::class,
					'concrete' => fn() => new PortalSql(PortalAdapterFactory::create()),
				],
				['id' => PortalApp::class],
				['id' => Integration::class],
				['id' => ConfigArea::class],
				['id' => CreditArea::class],
			],

			'view_and_renderers' => [
				[
					'id' => View::class,
					'concrete' => fn() => new View(realpath(__DIR__ . '/../../Themes/default/LightPortal')),
				],
				[
					'id' => SelectRenderer::class,
					'arguments' => [View::class],
				],
				[
					'id' => RendererInterface::class,
					'concrete' => Blade::class,
				],
				[
					'id' => TablePresenterInterface::class,
					'concrete' => TablePresenter::class,
					'arguments' => [TableRenderer::class],
				],
				['id' => TableRenderer::class],
				[
					'id' => FormPresenter::class,
					'arguments' => [FormRenderer::class],
				],
				['id' => FormRenderer::class],
			],

			'actions' => [
				['id' => BoardIndex::class],
				['id' => Block::class],
				[
					'id' => Category::class,
					'arguments' => [
						CardListInterface::class,
						PageListRepositoryInterface::class,
					],
				],
				[
					'id' => Comment::class,
					'concrete' => Comment::class,
					'arguments' => [CommentRepositoryInterface::class, Notifier::class],
				],
				[
					'id' => FrontPage::class,
					'arguments' => [RendererInterface::class],
				],
				[
					'id' => Page::class,
					'arguments' => [PageRepositoryInterface::class],
				],
				[
					'id' => Tag::class,
					'arguments' => [
						CardListInterface::class,
						PageListRepositoryInterface::class,
					],
				],
				[
					'id' => CardListInterface::class,
					'concrete' => CardList::class,
				],
			],

			'articles' => [
				[
					'id' => PageArticle::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => TopicArticle::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => BoardArticle::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => ChosenPageArticle::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => ChosenTopicArticle::class,
					'arguments' => [PortalSqlInterface::class],
				],
			],

			'handlers_and_managers' => [
				['id' => EventManager::class],
				['id' => AssetHandler::class],
				['id' => ConfigHandler::class],
				['id' => LangHandler::class],
				['id' => EventManagerFactory::class],
				[
					'id' => PluginHandler::class,
					'concrete' => fn() => fn(array $plugins = []) => new PluginHandler($plugins),
				],
			],

			'repositories' => [
				[
					'id' => BlockRepositoryInterface::class,
					'concrete' => BlockRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => CategoryRepositoryInterface::class,
					'concrete' => CategoryRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => CommentRepositoryInterface::class,
					'concrete' => CommentRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => PageListRepositoryInterface::class,
					'concrete' => PageListRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => PageRepositoryInterface::class,
					'concrete' => PageRepository::class,
					'arguments' => [PortalSqlInterface::class, Notifier::class],
				],
				[
					'id' => PluginRepositoryInterface::class,
					'concrete' => PluginRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => TagRepositoryInterface::class,
					'concrete' => TagRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
			],

			'lists' => [
				[
					'id' => BlockList::class,
					'arguments' => [BlockRepositoryInterface::class],
				],
				[
					'id' => CategoryList::class,
					'arguments' => [CategoryRepositoryInterface::class],
				],
				['id' => IconList::class],
				[
					'id' => PageList::class,
					'arguments' => [PageRepositoryInterface::class],
				],
				['id' => PluginList::class],
				[
					'id' => TagList::class,
					'arguments' => [TagRepositoryInterface::class],
				],
			],

			'utils' => [
				[
					'id' => CacheInterface::class,
					'concrete' => Cache::class,
				],
				['id' => Request::class],
				[
					'id' => RequestInterface::class,
					'concrete' => Request::class,
				],
				['id' => Response::class],
				['id' => Post::class],
				[
					'id' => PostInterface::class,
					'concrete' => Post::class,
				],
				['id' => File::class],
				[
					'id' => FileInterface::class,
					'concrete' => File::class,
				],
				['id' => Session::class],
				[
					'id' => SessionManager::class,
					'arguments' => [
						BlockRepositoryInterface::class,
						PageRepositoryInterface::class,
						CategoryRepositoryInterface::class,
						TagRepositoryInterface::class,
					],
				],
				[
					'id' => Notifier::class,
					'arguments' => [PortalSqlInterface::class],
				],
				['id' => ErrorHandler::class],
				[
					'id' => ErrorHandlerInterface::class,
					'concrete' => ErrorHandler::class,
				],
				['id' => Filesystem::class],
				[
					'id' => FilesystemInterface::class,
					'concrete' => Filesystem::class,
				],
			],

			'configs' => [
				['id' => BasicConfig::class],
				['id' => ExtraConfig::class],
				['id' => PanelConfig::class],
				['id' => MiscConfig::class],
				['id' => FeedbackConfig::class],
			],

			'block_area_export_import' => [
				[
					'id' => BlockArea::class,
					'arguments' => [BlockRepositoryInterface::class],
				],
				[
					'id' => BlockExport::class,
					'concrete' => BlockExport::class,
					'arguments' => [
						BlockRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => BlockImport::class,
					'concrete' => BlockImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'page_area_export_import' => [
				[
					'id' => PageArea::class,
					'arguments' => [PageRepositoryInterface::class],
				],
				[
					'id' => PageExport::class,
					'concrete' => PageExport::class,
					'arguments' => [
						PageRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => PageImport::class,
					'concrete' => PageImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'category_area_export_import' => [
				[
					'id' => CategoryArea::class,
					'arguments' => [CategoryRepositoryInterface::class],
				],
				[
					'id' => CategoryExport::class,
					'concrete' => CategoryExport::class,
					'arguments' => [
						CategoryRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => CategoryImport::class,
					'concrete' => CategoryImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'tag_area_export_import' => [
				[
					'id' => TagArea::class,
					'arguments' => [TagRepositoryInterface::class],
				],
				[
					'id' => TagExport::class,
					'concrete' => TagExport::class,
					'arguments' => [
						TagRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => TagImport::class,
					'concrete' => TagImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'plugin_area_export_import' => [
				[
					'id' => PluginArea::class,
					'arguments' => [PluginRepositoryInterface::class],
				],
				[
					'id' => PluginExport::class,
					'concrete' => PluginExport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => PluginImport::class,
					'concrete' => PluginImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'validators_and_factories' => [
				[
					'id' => BlockValidator::class,
					'arguments' => [PortalSqlInterface::class],
				],
				['id' => BlockFactory::class],
				[
					'id' => CategoryValidator::class,
					'arguments' => [PortalSqlInterface::class],
				],
				['id' => CategoryFactory::class],
				[
					'id' => PageValidator::class,
					'arguments' => [PortalSqlInterface::class],
				],
				['id' => PageFactory::class],
				[
					'id' => TagValidator::class,
					'arguments' => [PortalSqlInterface::class],
				],
				['id' => TagFactory::class],
			],

			'other' => [
				['id' => BreadcrumbRenderer::class],
				[
					'id' => BreadcrumbPresenter::class,
					'arguments' => [BreadcrumbRenderer::class],
				],
				[
					'id' => BreadcrumbBuilder::class,
					'concrete' => static fn() => BreadcrumbBuilder::make(),
					'shared' => false,
				],
				[
					'id' => BreadcrumbWrapper::class,
					'arguments' => [BreadcrumbBuilder::class, BreadcrumbPresenter::class],
					'shared' => false,
				],
			],
		];
	}
}
