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

namespace LightPortal;

use Bugo\Bricks\Breadcrumbs\BreadcrumbBuilder;
use Bugo\Bricks\Breadcrumbs\BreadcrumbPresenter;
use Bugo\Bricks\Forms\FormPresenter;
use Bugo\Bricks\Forms\FormRenderer;
use Bugo\Bricks\Tables\Interfaces\TablePresenterInterface;
use Bugo\Bricks\Tables\TablePresenter;
use League\Container\ServiceProvider\AbstractServiceProvider;
use LightPortal\Actions\Block;
use LightPortal\Actions\BoardIndex;
use LightPortal\Actions\CardList;
use LightPortal\Actions\CardListInterface;
use LightPortal\Actions\Category;
use LightPortal\Actions\Comment;
use LightPortal\Actions\FrontPage;
use LightPortal\Actions\Page;
use LightPortal\Actions\Tag;
use LightPortal\Areas\BlockArea;
use LightPortal\Areas\CategoryArea;
use LightPortal\Areas\ConfigArea;
use LightPortal\Areas\Configs\BasicConfig;
use LightPortal\Areas\Configs\ExtraConfig;
use LightPortal\Areas\Configs\FeedbackConfig;
use LightPortal\Areas\Configs\MiscConfig;
use LightPortal\Areas\Configs\PanelConfig;
use LightPortal\Areas\CreditArea;
use LightPortal\Areas\PageArea;
use LightPortal\Areas\PluginArea;
use LightPortal\Areas\TagArea;
use LightPortal\Articles\BoardArticle;
use LightPortal\Articles\ChosenPageArticle;
use LightPortal\Articles\ChosenTopicArticle;
use LightPortal\Articles\PageArticle;
use LightPortal\Articles\TopicArticle;
use LightPortal\Database\PortalAdapterFactory;
use LightPortal\Database\PortalSql;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\DataHandlers\Exports\BlockExport;
use LightPortal\DataHandlers\Exports\CategoryExport;
use LightPortal\DataHandlers\Exports\PageExport;
use LightPortal\DataHandlers\Exports\PluginExport;
use LightPortal\DataHandlers\Exports\TagExport;
use LightPortal\DataHandlers\Imports\BlockImport;
use LightPortal\DataHandlers\Imports\CategoryImport;
use LightPortal\DataHandlers\Imports\PageImport;
use LightPortal\DataHandlers\Imports\PluginImport;
use LightPortal\DataHandlers\Imports\TagImport;
use LightPortal\Events\EventManager;
use LightPortal\Events\EventManagerFactory;
use LightPortal\Lists\BlockList;
use LightPortal\Lists\CategoryList;
use LightPortal\Lists\IconList;
use LightPortal\Lists\PageList;
use LightPortal\Lists\PluginList;
use LightPortal\Lists\TagList;
use LightPortal\Models\BlockFactory;
use LightPortal\Models\CategoryFactory;
use LightPortal\Models\PageFactory;
use LightPortal\Models\TagFactory;
use LightPortal\Plugins\AssetHandler;
use LightPortal\Plugins\ConfigHandler;
use LightPortal\Plugins\LangHandler;
use LightPortal\Plugins\PluginHandler;
use LightPortal\Renderers\Blade;
use LightPortal\Renderers\RendererInterface;
use LightPortal\Repositories\BlockRepository;
use LightPortal\Repositories\BlockRepositoryInterface;
use LightPortal\Repositories\CategoryRepository;
use LightPortal\Repositories\CategoryRepositoryInterface;
use LightPortal\Repositories\CommentRepository;
use LightPortal\Repositories\CommentRepositoryInterface;
use LightPortal\Repositories\PageListRepository;
use LightPortal\Repositories\PageListRepositoryInterface;
use LightPortal\Repositories\PageRepository;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\Repositories\PluginRepository;
use LightPortal\Repositories\PluginRepositoryInterface;
use LightPortal\Repositories\TagRepository;
use LightPortal\Repositories\TagRepositoryInterface;
use LightPortal\UI\Breadcrumbs\BreadcrumbRenderer;
use LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\Tables\TableRenderer;
use LightPortal\UI\View;
use LightPortal\Utils\Cache;
use LightPortal\Utils\CacheInterface;
use LightPortal\Utils\ErrorHandler;
use LightPortal\Utils\ErrorHandlerInterface;
use LightPortal\Utils\File;
use LightPortal\Utils\FileInterface;
use LightPortal\Utils\Filesystem;
use LightPortal\Utils\FilesystemInterface;
use LightPortal\Utils\Notifier;
use LightPortal\Utils\Post;
use LightPortal\Utils\PostInterface;
use LightPortal\Utils\Request;
use LightPortal\Utils\RequestInterface;
use LightPortal\Utils\Response;
use LightPortal\Utils\ResponseInterface;
use LightPortal\Utils\Session;
use LightPortal\Utils\SessionManager;
use LightPortal\Validators\BlockValidator;
use LightPortal\Validators\CategoryValidator;
use LightPortal\Validators\PageValidator;
use LightPortal\Validators\TagValidator;

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
				[
					'id' => ResponseInterface::class,
					'concrete' => Response::class,
				],
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
