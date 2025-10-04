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
use Bugo\LightPortal\Areas\CreditArea;
use Bugo\LightPortal\Areas\PageArea;
use Bugo\LightPortal\Areas\PluginArea;
use Bugo\LightPortal\Areas\TagArea;
use Bugo\LightPortal\Articles\BoardArticle;
use Bugo\LightPortal\Articles\ChosenPageArticle;
use Bugo\LightPortal\Articles\ChosenTopicArticle;
use Bugo\LightPortal\Articles\PageArticle;
use Bugo\LightPortal\Articles\TopicArticle;
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
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Migrations\PortalAdapter;
use Bugo\LightPortal\Migrations\PortalAdapterFactory;
use Bugo\LightPortal\Migrations\PortalAdapterInterface;
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
use Bugo\LightPortal\Utils\Database;
use Bugo\LightPortal\Utils\DatabaseInterface;
use Bugo\LightPortal\Utils\ErrorHandler;
use Bugo\LightPortal\Utils\ErrorHandlerInterface;
use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\FileInterface;
use Bugo\LightPortal\Utils\Filesystem;
use Bugo\LightPortal\Utils\FilesystemInterface;
use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\PostInterface;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\RequestInterface;
use Bugo\LightPortal\Utils\Response;
use Bugo\LightPortal\Utils\Session;
use Bugo\LightPortal\Utils\SessionManager;
use Bugo\LightPortal\Utils\Weaver;
use Bugo\LightPortal\Validators\BlockValidator;
use Bugo\LightPortal\Validators\CategoryValidator;
use Bugo\LightPortal\Validators\PageValidator;
use Bugo\LightPortal\Validators\TagValidator;
use League\Container\ServiceProvider\AbstractServiceProvider;

if (! defined('SMF'))
	die('No direct access...');

class ServiceProvider extends AbstractServiceProvider
{
	private const SERVICES = [
		AssetHandler::class,
		Block::class,
		BlockArea::class,
		BlockExport::class,
		BlockFactory::class,
		BlockImport::class,
		BlockRepository::class,
		BlockValidator::class,
		BoardArticle::class,
		BoardIndex::class,
		BreadcrumbBuilder::class,
		BreadcrumbPresenter::class,
		BreadcrumbRenderer::class,
		BreadcrumbWrapper::class,
		Cache::class,
		CacheInterface::class,
		CardListInterface::class,
		Category::class,
		CategoryArea::class,
		CategoryExport::class,
		CategoryFactory::class,
		CategoryImport::class,
		CategoryList::class,
		CategoryRepository::class,
		CategoryValidator::class,
		ChosenPageArticle::class,
		ChosenTopicArticle::class,
		Comment::class,
		CommentRepository::class,
		ConfigArea::class,
		ConfigHandler::class,
		CreditArea::class,
		Database::class,
		DatabaseInterface::class,
		ErrorHandlerInterface::class,
		EventManager::class,
		EventManagerFactory::class,
		File::class,
		FileInterface::class,
		Filesystem::class,
		FilesystemInterface::class,
		FormPresenter::class,
		FormRenderer::class,
		FrontPage::class,
		IconList::class,
		Integration::class,
		LangHandler::class,
		Page::class,
		PageArea::class,
		PageArticle::class,
		PageExport::class,
		PageFactory::class,
		PageImport::class,
		PageList::class,
		PageRepository::class,
		PageValidator::class,
		PluginArea::class,
		PluginExport::class,
		PluginHandler::class,
		PluginImport::class,
		PluginList::class,
		PluginRepository::class,
		PluginRepositoryInterface::class,
		PortalAdapter::class,
		PortalAdapterInterface::class,
		PortalApp::class,
		Post::class,
		PostInterface::class,
		RendererInterface::class,
		Request::class,
		RequestInterface::class,
		Response::class,
		SelectRenderer::class,
		Session::class,
		SessionManager::class,
		TablePresenter::class,
		TableRenderer::class,
		Tag::class,
		TagArea::class,
		TagExport::class,
		TagFactory::class,
		TagImport::class,
		TagList::class,
		TagRepository::class,
		TagValidator::class,
		TopicArticle::class,
		View::class,
		Weaver::class,
	];

	public function provides(string $id): bool
	{
		return in_array($id, self::SERVICES, true);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(Database::class);
		$container->add(DatabaseInterface::class, Database::class);

		$container->add(PortalAdapterInterface::class, fn() => PortalAdapterFactory::create());

		$container->add(PortalApp::class);
		$container->add(Integration::class);
		$container->add(ConfigArea::class);
		$container->add(CreditArea::class);

		$container->add(View::class, fn() => new View(realpath(__DIR__ . '/../../Themes/default/LightPortal')));
		$container->add(SelectRenderer::class)->addArgument(View::class);

		$container->add(RendererInterface::class, Blade::class);
		$container->add(TablePresenter::class)->addArgument(TableRenderer::class);
		$container->add(TableRenderer::class);
		$container->add(FormPresenter::class)->addArgument(FormRenderer::class);
		$container->add(FormRenderer::class);

		$container->add(PageArticle::class);
		$container->add(TopicArticle::class);
		$container->add(BoardArticle::class);
		$container->add(ChosenPageArticle::class);
		$container->add(ChosenTopicArticle::class);

		$container->add(EventManager::class);
		$container->add(AssetHandler::class);
		$container->add(ConfigHandler::class);
		$container->add(LangHandler::class);
		$container->add(EventManagerFactory::class);
		$container->add(PluginHandler::class, fn() => fn(array $plugins = []) => new PluginHandler($plugins));

		$container->add(CacheInterface::class, Cache::class);
		$container->add(Request::class);
		$container->add(RequestInterface::class, Request::class);
		$container->add(Response::class);
		$container->add(Post::class);
		$container->add(PostInterface::class, Post::class);
		$container->add(File::class);
		$container->add(FileInterface::class, File::class);
		$container->add(Session::class);

		$container->add(CommentRepository::class);
		$container->add(PluginRepository::class);
		$container->add(BlockRepository::class);
		$container->add(CategoryRepository::class);
		$container->add(TagRepository::class);

		$container->add(BlockRepositoryInterface::class, BlockRepository::class);
		$container->add(PageRepositoryInterface::class, PageRepository::class)
			->addArgument(PortalAdapterInterface::class);
		$container->add(CategoryRepositoryInterface::class, CategoryRepository::class);
		$container->add(TagRepositoryInterface::class, TagRepository::class);
		$container->add(PluginRepositoryInterface::class, PluginRepository::class);

		$container->add(IconList::class);
		$container->add(CategoryList::class);
		$container->add(PageList::class)->addArgument(PageRepositoryInterface::class);
		$container->add(TagList::class);
		$container->add(PluginList::class);
		$container->add(SessionManager::class);

		$container->add(BlockArea::class)->addArgument(BlockRepositoryInterface::class);
		$container->add(BlockExport::class)
			->addArgument(BlockRepositoryInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(FilesystemInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(BlockImport::class)
			->addArgument(FileInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(PageArea::class)->addArgument(PageRepositoryInterface::class);
		$container->add(PageExport::class)
			->addArgument(PageRepositoryInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(FilesystemInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(PageImport::class)
			->addArgument(FileInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(CategoryArea::class)->addArgument(CategoryRepositoryInterface::class);
		$container->add(CategoryExport::class)
			->addArgument(CategoryRepositoryInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(FilesystemInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(CategoryImport::class)
			->addArgument(FileInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(TagArea::class)->addArgument(TagRepositoryInterface::class);
		$container->add(TagExport::class)
			->addArgument(TagRepositoryInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(FilesystemInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(TagImport::class)
			->addArgument(FileInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(PluginArea::class)->addArgument(PluginRepositoryInterface::class);
		$container->add(PluginExport::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(FilesystemInterface::class)
			->addArgument(ErrorHandlerInterface::class);
		$container->add(PluginImport::class)
			->addArgument(FileInterface::class)
			->addArgument(DatabaseInterface::class)
			->addArgument(ErrorHandlerInterface::class);

		$container->add(ErrorHandler::class);
		$container->add(ErrorHandlerInterface::class, ErrorHandler::class);

		$container->add(Filesystem::class);
		$container->add(FilesystemInterface::class, Filesystem::class);

		$container->add(BoardIndex::class);
		$container->add(FrontPage::class)->addArgument(RendererInterface::class);
		$container->add(Block::class);
		$container->add(Page::class)->addArgument(PageRepositoryInterface::class);
		$container->add(Comment::class)->addArgument(CommentRepository::class);
		$container->add(Category::class)->addArgument(CardListInterface::class);
		$container->add(Tag::class)->addArgument(CardListInterface::class);
		$container->add(CardListInterface::class, CardList::class);

		$container->add(BlockValidator::class);
		$container->add(BlockFactory::class);
		$container->add(CategoryValidator::class);
		$container->add(CategoryFactory::class);
		$container->add(PageValidator::class);
		$container->add(PageFactory::class);
		$container->add(TagValidator::class);
		$container->add(TagFactory::class);

		$container->add(BreadcrumbRenderer::class);
		$container->add(BreadcrumbPresenter::class)->addArgument(BreadcrumbRenderer::class);
		$container->add(BreadcrumbBuilder::class, static fn() => BreadcrumbBuilder::make())->setShared(false);
		$container->add(BreadcrumbWrapper::class)
			->addArgument(BreadcrumbBuilder::class)
			->addArgument(BreadcrumbPresenter::class)
			->setShared(false);

		$container->add(Weaver::class, Weaver::class)->setShared(false);
	}
}
