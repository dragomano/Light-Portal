<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2026 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 3.0
 */

namespace LightPortal;

use Bugo\Bricks\Breadcrumbs\{BreadcrumbBuilder, BreadcrumbPresenter};
use Bugo\Bricks\Forms\{FormPresenter, FormRenderer};
use Bugo\Bricks\Tables\{Interfaces\TablePresenterInterface, TablePresenter};
use LightPortal\Actions\{Block, BoardIndex, CardList, CardListInterface, Category, CategoryIndex, CategoryPageList};
use LightPortal\Actions\{Comment, FrontPage, Page, Tag, TagIndex, TagPageList};
use LightPortal\Areas\Configs\{BasicConfig, ExtraConfig, FeedbackConfig, MiscConfig, PanelConfig};
use LightPortal\Areas\{BlockArea, CategoryArea, PageArea, PluginArea, TagArea};
use LightPortal\Articles\Queries\{BoardArticleQuery, ChosenPageArticleQuery, ChosenTopicArticleQuery, PageArticleQuery};
use LightPortal\Articles\Queries\{TagPageArticleQuery, TopicArticleQuery};
use LightPortal\Articles\Services\{BoardArticleService, CategoryPageArticleService, PageArticleService};
use LightPortal\Articles\Services\{TagPageArticleService, TopicArticleService};
use LightPortal\Articles\{BoardArticle, ChosenPageArticle, ChosenTopicArticle, PageArticle, TopicArticle};
use LightPortal\DataHandlers\Exports\{BlockExport, CategoryExport, PageExport, PluginExport, TagExport};
use LightPortal\DataHandlers\Imports\{BlockImport, CategoryImport, PageImport, PluginImport, TagImport};
use LightPortal\Database\{PortalAdapterFactory, PortalSql, PortalSqlInterface};
use LightPortal\Events\{EventDispatcherInterface, EventManager, EventManagerFactory, EventManagerProxy};
use LightPortal\Hooks\Integration;
use LightPortal\Lists\{BlockList, CategoryList, IconList, PageList, PluginList, TagList};
use LightPortal\Models\{BlockFactory, CategoryFactory, PageFactory, TagFactory};
use LightPortal\Plugins\{AssetHandler, ConfigHandler, LangHandler, PluginHandler};
use LightPortal\Renderers\{Blade, PurePHP, RendererInterface};
use LightPortal\Repositories\{BlockRepository, BlockRepositoryInterface, CategoryIndexRepository, CategoryRepository};
use LightPortal\Repositories\{CategoryRepositoryInterface, CommentRepository, CommentRepositoryInterface};
use LightPortal\Repositories\{PageRepository, PageRepositoryInterface, PluginRepository, PluginRepositoryInterface};
use LightPortal\Repositories\{TagIndexRepository, TagRepository, TagRepositoryInterface};
use LightPortal\UI\Breadcrumbs\{BreadcrumbRenderer, BreadcrumbWrapper};
use LightPortal\UI\Partials\SelectRenderer;
use LightPortal\UI\Tables\TableRenderer;
use LightPortal\UI\{View, ViewInterface};
use LightPortal\Utils\{Cache, CacheInterface, ErrorHandler, ErrorHandlerInterface, File, FileInterface, Filesystem};
use LightPortal\Utils\{FilesystemInterface, InputFilter, Notifier, NotifierInterface, Post, PostInterface, Request};
use LightPortal\Utils\{RequestInterface, Response, ResponseInterface, Session, SessionInterface, SessionManager};
use LightPortal\Validators\{BlockValidator, CategoryValidator, PageValidator, TagValidator};

if (! defined('SMF'))
	die('No direct access...');

class ServiceProvider
{
	public static function getConfig(): array
	{
		return [
			'invokables' => [
				AssetHandler::class,
				Blade::class,
				Block::class,
				BlockFactory::class,
				BoardIndex::class,
				BreadcrumbRenderer::class,
				CategoryFactory::class,
				ConfigHandler::class,
				EventManager::class,
				EventManagerFactory::class,
				FeedbackConfig::class,
				FormRenderer::class,
				InputFilter::class,
				Integration::class,
				LangHandler::class,
				MiscConfig::class,
				PageFactory::class,
				PanelConfig::class,
				PluginList::class,
				PortalApp::class,
				PurePHP::class,
				TableRenderer::class,
				TagFactory::class,
			],
			'factories' => [
				PortalSqlInterface::class => fn() => new PortalSql(
					PortalAdapterFactory::create()
				),

				FormPresenter::class => fn($c) => new FormPresenter(
					$c->get(FormRenderer::class)
				),

				RendererInterface::class => fn($c) => $c->get(Blade::class),

				SelectRenderer::class => fn($c) => new SelectRenderer(
					$c->get(ViewInterface::class)
				),

				ViewInterface::class => fn() => new View(),

				TablePresenterInterface::class => fn($c) => new TablePresenter(
					$c->get(TableRenderer::class)
				),

				CardList::class => fn($c) => new CardList(
					$c->get(PageArticle::class),
					$c->get(FrontPage::class)
				),

				CardListInterface::class => fn($c) => $c->get(CardList::class),

				Category::class => fn($c) => new Category(
					$c->get(CategoryPageList::class),
					$c->get(CategoryIndex::class)
				),

				CategoryIndex::class => fn($c) => new CategoryIndex(
					$c->get(CategoryIndexRepository::class)
				),

				CategoryPageList::class => fn($c) => new CategoryPageList(
					$c->get(CardListInterface::class),
					$c->get(CategoryPageArticleService::class)
				),

				Comment::class => fn($c) => new Comment(
					$c->get(CommentRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(NotifierInterface::class)
				),

				FrontPage::class => fn($c) => new FrontPage(
					$c->get(RendererInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				Page::class => fn($c) => new Page(
					$c->get(PageRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				Tag::class => fn($c) => new Tag(
					$c->get(TagPageList::class),
					$c->get(TagIndex::class)
				),

				TagIndex::class => fn($c) => new TagIndex(
					$c->get(TagIndexRepository::class)
				),

				TagPageList::class => fn($c) => new TagPageList(
					$c->get(CardListInterface::class),
					$c->get(TagPageArticleService::class)
				),

				BoardArticle::class => fn($c) => new BoardArticle(
					$c->get(BoardArticleService::class)
				),

				BoardArticleQuery::class => fn($c) => new BoardArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				BoardArticleService::class => fn($c) => new BoardArticleService(
					$c->get(BoardArticleQuery::class),
					$c->get(EventDispatcherInterface::class)
				),

				CategoryPageArticleService::class => fn($c) => new CategoryPageArticleService(
					$c->get(PageArticleQuery::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(PageRepositoryInterface::class)
				),

				ChosenPageArticle::class => fn($c) => new ChosenPageArticle(
					new PageArticleService(
						$c->get(ChosenPageArticleQuery::class),
						$c->get(EventDispatcherInterface::class),
						$c->get(PageRepositoryInterface::class)
					)
				),

				ChosenPageArticleQuery::class => fn($c) => new ChosenPageArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				ChosenTopicArticle::class => fn($c) => new ChosenTopicArticle(
					new TopicArticleService(
						$c->get(ChosenTopicArticleQuery::class),
						$c->get(EventDispatcherInterface::class)
					)
				),

				ChosenTopicArticleQuery::class => fn($c) => new ChosenTopicArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				PageArticle::class => fn($c) => new PageArticle(
					$c->get(PageArticleService::class)
				),

				PageArticleQuery::class => fn($c) => new PageArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				PageArticleService::class => fn($c) => new PageArticleService(
					$c->get(PageArticleQuery::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(PageRepositoryInterface::class)
				),

				TagPageArticleQuery::class => fn($c) => new TagPageArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				TagPageArticleService::class => fn($c) => new TagPageArticleService(
					$c->get(TagPageArticleQuery::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(PageRepositoryInterface::class)
				),

				TopicArticle::class => fn($c) => new TopicArticle(
					$c->get(TopicArticleService::class)
				),

				TopicArticleQuery::class => fn($c) => new TopicArticleQuery(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				TopicArticleService::class => fn($c) => new TopicArticleService(
					$c->get(TopicArticleQuery::class),
					$c->get(EventDispatcherInterface::class)
				),

				PluginHandler::class => fn($c) => fn(array $plugins = []) => new PluginHandler(
					$c->get(EventManager::class),
					$c->get(AssetHandler::class),
					$c->get(ConfigHandler::class),
					$c->get(LangHandler::class),
					$plugins
				),

				EventDispatcherInterface::class => fn($c) => new EventManagerProxy(
					$c->get(EventManagerFactory::class)
				),

				BlockRepositoryInterface::class => fn($c) => new BlockRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				CategoryIndexRepository::class => fn($c) => new CategoryIndexRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				CategoryRepositoryInterface::class => fn($c) => new CategoryRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				CommentRepositoryInterface::class => fn($c) => new CommentRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				PageRepositoryInterface::class => fn($c) => new PageRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(NotifierInterface::class)
				),

				PluginRepositoryInterface::class => fn($c) => new PluginRepository(
					$c->get(PortalSqlInterface::class)
				),

				TagIndexRepository::class => fn($c) => new TagIndexRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				TagRepositoryInterface::class => fn($c) => new TagRepository(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				BlockList::class => fn($c) => new BlockList(
					$c->get(BlockRepositoryInterface::class)
				),

				CategoryList::class => fn($c) => new CategoryList(
					$c->get(CategoryRepositoryInterface::class)
				),

				IconList::class => fn($c) => new IconList(
					$c->get(EventDispatcherInterface::class)
				),

				PageList::class => fn($c) => new PageList(
					$c->get(PageRepositoryInterface::class)
				),

				TagList::class => fn($c) => new TagList(
					$c->get(TagRepositoryInterface::class)
				),

				CacheInterface::class => fn() => new Cache(),

				ErrorHandlerInterface::class => fn() => new ErrorHandler(),

				FileInterface::class => fn() => new File(),

				FilesystemInterface::class => fn() => new Filesystem(),

				NotifierInterface::class => fn($c) => new Notifier(
					$c->get(PortalSqlInterface::class)
				),

				PostInterface::class => fn() => new Post(),

				RequestInterface::class => fn() => new Request(),

				ResponseInterface::class => fn() => new Response(),

				SessionInterface::class => fn() => new Session(),

				SessionManager::class => fn($c) => new SessionManager(
					$c->get(BlockRepositoryInterface::class),
					$c->get(PageRepositoryInterface::class),
					$c->get(CategoryRepositoryInterface::class),
					$c->get(TagRepositoryInterface::class)
				),

				BasicConfig::class => fn($c) => new BasicConfig(
					$c->get(EventDispatcherInterface::class),
					$c->get(InputFilter::class)
				),

				ExtraConfig::class => fn($c) => new ExtraConfig(
					$c->get(InputFilter::class)
				),

				BlockArea::class => fn($c) => new BlockArea(
					$c->get(BlockRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				BlockExport::class => fn($c) => new BlockExport(
					$c->get(BlockRepositoryInterface::class),
					$c->get(PortalSqlInterface::class),
					$c->get(FilesystemInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				BlockImport::class => fn($c) => new BlockImport(
					$c->get(PortalSqlInterface::class),
					$c->get(FileInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				PageArea::class => fn($c) => new PageArea(
					$c->get(PageRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				PageExport::class => fn($c) => new PageExport(
					$c->get(PageRepositoryInterface::class),
					$c->get(PortalSqlInterface::class),
					$c->get(FilesystemInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				PageImport::class => fn($c) => new PageImport(
					$c->get(PortalSqlInterface::class),
					$c->get(FileInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				CategoryArea::class => fn($c) => new CategoryArea(
					$c->get(CategoryRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				CategoryExport::class => fn($c) => new CategoryExport(
					$c->get(CategoryRepositoryInterface::class),
					$c->get(PortalSqlInterface::class),
					$c->get(FilesystemInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				CategoryImport::class => fn($c) => new CategoryImport(
					$c->get(PortalSqlInterface::class),
					$c->get(FileInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				TagArea::class => fn($c) => new TagArea(
					$c->get(TagRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				TagExport::class => fn($c) => new TagExport(
					$c->get(TagRepositoryInterface::class),
					$c->get(PortalSqlInterface::class),
					$c->get(FilesystemInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				TagImport::class => fn($c) => new TagImport(
					$c->get(PortalSqlInterface::class),
					$c->get(FileInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				PluginArea::class => fn($c) => new PluginArea(
					$c->get(PluginRepositoryInterface::class),
					$c->get(EventDispatcherInterface::class),
					$c->get(InputFilter::class)
				),

				PluginExport::class => fn($c) => new PluginExport(
					$c->get(PortalSqlInterface::class),
					$c->get(FilesystemInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				PluginImport::class => fn($c) => new PluginImport(
					$c->get(PortalSqlInterface::class),
					$c->get(FileInterface::class),
					$c->get(ErrorHandlerInterface::class)
				),

				BlockValidator::class => fn($c) => new BlockValidator(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				CategoryValidator::class => fn($c) => new CategoryValidator(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				PageValidator::class => fn($c) => new PageValidator(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				TagValidator::class => fn($c) => new TagValidator(
					$c->get(PortalSqlInterface::class),
					$c->get(EventDispatcherInterface::class)
				),

				BreadcrumbPresenter::class => fn($c) => new BreadcrumbPresenter(
					$c->get(BreadcrumbRenderer::class)
				),

				BreadcrumbBuilder::class => fn() => BreadcrumbBuilder::make(),

				BreadcrumbWrapper::class => fn($c) => new BreadcrumbWrapper(
					$c->get(BreadcrumbBuilder::class),
					$c->get(BreadcrumbPresenter::class)
				),
			],
			'shared' => [
				BreadcrumbBuilder::class => false,
				BreadcrumbWrapper::class => false,
			],
			'tags' => [],
		];
	}
}
