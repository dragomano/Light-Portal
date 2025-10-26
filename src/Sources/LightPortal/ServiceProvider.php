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

use Bugo\Bricks\Breadcrumbs\{
	BreadcrumbBuilder,
	BreadcrumbPresenter,
};
use Bugo\Bricks\Forms\{
	FormPresenter,
	FormRenderer,
};
use Bugo\Bricks\Tables\{
	Interfaces\TablePresenterInterface,
	TablePresenter,
};
use League\Container\ServiceProvider\AbstractServiceProvider;
use LightPortal\Actions\{
	Block,
	BoardIndex,
	CardList,
	CardListInterface,
	Category,
	CategoryIndex,
	CategoryPageList,
	Comment,
	FrontPage,
	Page,
	Tag,
	TagIndex,
	TagPageList,
};
use LightPortal\Areas\{
	BlockArea,
	CategoryArea,
	PageArea,
	PluginArea,
	TagArea,
};
use LightPortal\Areas\Configs\{
	BasicConfig,
	ExtraConfig,
	FeedbackConfig,
	MiscConfig,
	PanelConfig,
};
use LightPortal\Articles\{
	BoardArticle,
	ChosenPageArticle,
	ChosenTopicArticle,
	PageArticle,
	TopicArticle,
};
use LightPortal\Articles\Queries\{
	BoardArticleQuery,
	ChosenPageArticleQuery,
	ChosenTopicArticleQuery,
	PageArticleQuery,
	TagPageArticleQuery,
	TopicArticleQuery,
};
use LightPortal\Articles\Services\{
	BoardArticleService,
	CategoryPageArticleService,
	PageArticleService,
	TagPageArticleService,
	TopicArticleService,
};
use LightPortal\Database\{
	PortalAdapterFactory,
	PortalSql,
	PortalSqlInterface,
};
use LightPortal\DataHandlers\Exports\{
	BlockExport,
	CategoryExport,
	PageExport,
	PluginExport,
	TagExport,
};
use LightPortal\DataHandlers\Imports\{
	BlockImport,
	CategoryImport,
	PageImport,
	PluginImport,
	TagImport,
};
use LightPortal\Events\{
	EventDispatcherInterface,
	EventManager,
	EventManagerFactory,
	EventManagerProxy,
};
use LightPortal\Hooks\Integration;
use LightPortal\Lists\{
	BlockList,
	CategoryList,
	IconList,
	PageList,
	PluginList,
	TagList,
};
use LightPortal\Models\{
	BlockFactory,
	CategoryFactory,
	PageFactory,
	TagFactory,
};
use LightPortal\Plugins\{
	AssetHandler,
	ConfigHandler,
	LangHandler,
	PluginHandler,
};
use LightPortal\Renderers\{
	Blade,
	RendererInterface,
};
use LightPortal\Repositories\{
	BlockRepository,
	BlockRepositoryInterface,
	CategoryIndexRepository,
	CategoryRepository,
	CategoryRepositoryInterface,
	CommentRepository,
	CommentRepositoryInterface,
	PageRepository,
	PageRepositoryInterface,
	PluginRepository,
	PluginRepositoryInterface,
	TagIndexRepository,
	TagRepository,
	TagRepositoryInterface,
};
use LightPortal\UI\View;
use LightPortal\UI\Breadcrumbs\{
	BreadcrumbRenderer,
	BreadcrumbWrapper,
};
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\Tables\TableRenderer;
use LightPortal\Utils\{
	Cache,
	CacheInterface,
	ErrorHandler,
	ErrorHandlerInterface,
	File,
	FileInterface,
	Filesystem,
	FilesystemInterface,
	Notifier,
	NotifierInterface,
	Post,
	PostInterface,
	Request,
	RequestInterface,
	Response,
	ResponseInterface,
	Session,
	SessionInterface,
	SessionManager,
};
use LightPortal\Validators\{
	BlockValidator,
	CategoryValidator,
	PageValidator,
	TagValidator,
};

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
					'id' => CardListInterface::class,
					'concrete' => CardList::class,
				],
				[
					'id' => Category::class,
					'arguments' => [CategoryPageList::class, CategoryIndex::class],
				],
				[
					'id' => CategoryIndex::class,
					'arguments' => [CategoryIndexRepository::class],
				],
				[
					'id' => CategoryPageList::class,
					'arguments' => [CardListInterface::class, CategoryPageArticleService::class],
				],
				[
					'id' => Comment::class,
					'arguments' => [
						CommentRepositoryInterface::class,
						EventDispatcherInterface::class,
						NotifierInterface::class,
					],
				],
				[
					'id' => FrontPage::class,
					'arguments' => [RendererInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => Page::class,
					'arguments' => [PageRepositoryInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => Tag::class,
					'arguments' => [TagPageList::class, TagIndex::class],
				],
				[
					'id' => TagIndex::class,
					'arguments' => [TagIndexRepository::class],
				],
				[
					'id' => TagPageList::class,
					'arguments' => [CardListInterface::class, TagPageArticleService::class],
				],
			],

			'articles' => [
				[
					'id' => BoardArticle::class,
					'arguments' => [BoardArticleService::class],
				],
				[
					'id' => BoardArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => BoardArticleService::class,
					'arguments' => [BoardArticleQuery::class, EventDispatcherInterface::class],
				],
				[
					'id' => CategoryPageArticleService::class,
					'arguments' => [
						PageArticleQuery::class,
						EventDispatcherInterface::class,
						PageRepositoryInterface::class,
					],
				],
				[
					'id' => ChosenPageArticle::class,
					'concrete' => fn() => new ChosenPageArticle(
						new PageArticleService(
							$this->container->get(ChosenPageArticleQuery::class),
							$this->container->get(EventDispatcherInterface::class),
							$this->container->get(PageRepositoryInterface::class)
						)
					),
				],
				[
					'id' => ChosenPageArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => ChosenTopicArticle::class,
					'concrete' => fn() => new ChosenTopicArticle(
						new TopicArticleService(
							$this->container->get(ChosenTopicArticleQuery::class),
							$this->container->get(EventDispatcherInterface::class)
						)
					),
				],
				[
					'id' => ChosenTopicArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PageArticle::class,
					'arguments' => [PageArticleService::class],
				],
				[
					'id' => PageArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PageArticleService::class,
					'arguments' => [
						PageArticleQuery::class,
						EventDispatcherInterface::class,
						PageRepositoryInterface::class,
					],
				],
				[
					'id' => TagPageArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => TagPageArticleService::class,
					'arguments' => [
						TagPageArticleQuery::class,
						EventDispatcherInterface::class,
						PageRepositoryInterface::class,
					],
				],
				[
					'id' => TopicArticle::class,
					'arguments' => [TopicArticleService::class],
				],
				[
					'id' => TopicArticleQuery::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => TopicArticleService::class,
					'arguments' => [TopicArticleQuery::class, EventDispatcherInterface::class],
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
				[
					'id' => EventDispatcherInterface::class,
					'concrete' => EventManagerProxy::class,
					'arguments' => [EventManagerFactory::class],
				]
			],

			'repositories' => [
				[
					'id' => BlockRepositoryInterface::class,
					'concrete' => BlockRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => CategoryIndexRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => CategoryRepositoryInterface::class,
					'concrete' => CategoryRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => CommentRepositoryInterface::class,
					'concrete' => CommentRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PageRepositoryInterface::class,
					'concrete' => PageRepository::class,
					'arguments' => [
						PortalSqlInterface::class,
						EventDispatcherInterface::class,
						NotifierInterface::class,
					],
				],
				[
					'id' => PluginRepositoryInterface::class,
					'concrete' => PluginRepository::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => TagIndexRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => TagRepositoryInterface::class,
					'concrete' => TagRepository::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
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
				[
					'id' => IconList::class,
					'arguments' => [EventDispatcherInterface::class],
				],
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
				[
					'id' => ErrorHandlerInterface::class,
					'concrete' => ErrorHandler::class,
				],
				[
					'id' => FileInterface::class,
					'concrete' => File::class,
				],
				[
					'id' => FilesystemInterface::class,
					'concrete' => Filesystem::class,
				],
				[
					'id' => NotifierInterface::class,
					'concrete' => Notifier::class,
					'arguments' => [PortalSqlInterface::class],
				],
				[
					'id' => PostInterface::class,
					'concrete' => Post::class,
				],
				[
					'id' => RequestInterface::class,
					'concrete' => Request::class,
				],
				[
					'id' => ResponseInterface::class,
					'concrete' => Response::class,
				],
				[
					'id' => SessionInterface::class,
					'concrete' => Session::class,
				],
				[
					'id' => SessionManager::class,
					'arguments' => [
						BlockRepositoryInterface::class,
						PageRepositoryInterface::class,
						CategoryRepositoryInterface::class,
						TagRepositoryInterface::class,
					],
				],
			],

			'configs' => [
				[
					'id' => BasicConfig::class,
					'arguments' => [EventDispatcherInterface::class],
				],
				['id' => ExtraConfig::class],
				['id' => PanelConfig::class],
				['id' => MiscConfig::class],
				['id' => FeedbackConfig::class],
			],

			'block_area_export_import' => [
				[
					'id' => BlockArea::class,
					'arguments' => [BlockRepositoryInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => BlockExport::class,
					'arguments' => [
						BlockRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => BlockImport::class,
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
					'arguments' => [PageRepositoryInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PageExport::class,
					'arguments' => [
						PageRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => PageImport::class,
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
					'arguments' => [
						CategoryRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => CategoryImport::class,
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
					'arguments' => [
						TagRepositoryInterface::class,
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => TagImport::class,
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
					'arguments' => [PluginRepositoryInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PluginExport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FilesystemInterface::class,
						ErrorHandlerInterface::class,
					],
				],
				[
					'id' => PluginImport::class,
					'arguments' => [
						PortalSqlInterface::class,
						FileInterface::class,
						ErrorHandlerInterface::class,
					],
				],
			],

			'validators_and_factories' => [
				['id' => BlockFactory::class],
				['id' => CategoryFactory::class],
				['id' => PageFactory::class],
				['id' => TagFactory::class],
				[
					'id' => BlockValidator::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => CategoryValidator::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => PageValidator::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
				[
					'id' => TagValidator::class,
					'arguments' => [PortalSqlInterface::class, EventDispatcherInterface::class],
				],
			],

			'other' => [
				['id' => BreadcrumbRenderer::class],
				[
					'id' => BreadcrumbPresenter::class,
					'arguments' => [BreadcrumbRenderer::class],
				],
				[
					'id' => BreadcrumbBuilder::class,
					'concrete' => BreadcrumbBuilder::make(...),
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
