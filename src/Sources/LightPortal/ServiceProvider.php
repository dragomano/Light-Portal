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
use Bugo\LightPortal\Areas\Exports\BlockExport;
use Bugo\LightPortal\Areas\Exports\CategoryExport;
use Bugo\LightPortal\Areas\Exports\PageExport;
use Bugo\LightPortal\Areas\Exports\PluginExport;
use Bugo\LightPortal\Areas\Exports\TagExport;
use Bugo\LightPortal\Areas\Imports\BlockImport;
use Bugo\LightPortal\Areas\Imports\CategoryImport;
use Bugo\LightPortal\Areas\Imports\PageImport;
use Bugo\LightPortal\Areas\Imports\PluginImport;
use Bugo\LightPortal\Areas\Imports\TagImport;
use Bugo\LightPortal\Areas\PageArea;
use Bugo\LightPortal\Areas\PluginArea;
use Bugo\LightPortal\Areas\TagArea;
use Bugo\LightPortal\Events\EventManager;
use Bugo\LightPortal\Events\EventManagerFactory;
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
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Repositories\CommentRepository;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\UI\Breadcrumbs\BreadcrumbRenderer;
use Bugo\LightPortal\UI\Breadcrumbs\BreadcrumbWrapper;
use Bugo\LightPortal\UI\Tables\TableRenderer;
use Bugo\LightPortal\Utils\Cache;
use Bugo\LightPortal\Utils\CacheInterface;
use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Response;
use Bugo\LightPortal\Utils\Session;
use Bugo\LightPortal\Utils\SessionManager;
use Bugo\LightPortal\Utils\Weaver;
use Bugo\LightPortal\Validators\BlockValidator;
use Bugo\LightPortal\Validators\CategoryValidator;
use Bugo\LightPortal\Validators\PageValidator;
use Bugo\LightPortal\Validators\TagValidator;
use League\Container\ServiceProvider\AbstractServiceProvider;

use function in_array;

class ServiceProvider extends AbstractServiceProvider
{
	private array $services = [
		AssetHandler::class,
		Block::class,
		BlockArea::class,
		BlockExport::class,
		BlockFactory::class,
		BlockImport::class,
		BlockRepository::class,
		BlockValidator::class,
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
		Comment::class,
		CommentRepository::class,
		ConfigArea::class,
		ConfigHandler::class,
		CreditArea::class,
		EventManager::class,
		EventManagerFactory::class,
		File::class,
		FormPresenter::class,
		FormRenderer::class,
		FrontPage::class,
		IconList::class,
		Integration::class,
		LangHandler::class,
		Page::class,
		PageArea::class,
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
		PortalApp::class,
		Post::class,
		RendererInterface::class,
		Request::class,
		Response::class,
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
		Weaver::class,
	];

	public function provides(string $id): bool
	{
		return in_array($id, $this->services);
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$container->add(PortalApp::class);
		$container->add(Integration::class);
		$container->add(ConfigArea::class);
		$container->add(CreditArea::class);

		$container->add(RendererInterface::class, Blade::class);
		$container->add(TablePresenter::class)->addArgument(TableRenderer::class);
		$container->add(TableRenderer::class);
		$container->add(FormPresenter::class)->addArgument(FormRenderer::class);
		$container->add(FormRenderer::class);

		$container->add(EventManager::class);
		$container->add(AssetHandler::class);
		$container->add(ConfigHandler::class);
		$container->add(LangHandler::class);
		$container->add(EventManagerFactory::class);
		$container->add(PluginHandler::class, fn() => fn(array $plugins = []) => new PluginHandler($plugins));

		$container->add(IconList::class);
		$container->add(CategoryList::class);
		$container->add(PageList::class)->addArgument(PageRepository::class);
		$container->add(TagList::class);
		$container->add(PluginList::class);
		$container->add(SessionManager::class);

		$container->add(CacheInterface::class, Cache::class);
		$container->add(Request::class);
		$container->add(Response::class);
		$container->add(Post::class);
		$container->add(File::class);
		$container->add(Session::class);

		$container->add(BlockRepository::class);
		$container->add(CategoryRepository::class);
		$container->add(CommentRepository::class);
		$container->add(PageRepository::class);
		$container->add(PluginRepository::class);
		$container->add(TagRepository::class);

		$container->add(BlockArea::class)->addArgument(BlockRepository::class);
		$container->add(BlockExport::class)->addArgument(BlockRepository::class);
		$container->add(BlockImport::class);
		$container->add(PageArea::class)->addArgument(PageRepository::class);
		$container->add(PageExport::class)->addArgument(PageRepository::class);
		$container->add(PageImport::class);
		$container->add(CategoryArea::class)->addArgument(CategoryRepository::class);
		$container->add(CategoryExport::class)->addArgument(CategoryRepository::class);
		$container->add(CategoryImport::class);
		$container->add(TagArea::class)->addArgument(TagRepository::class);
		$container->add(TagExport::class)->addArgument(TagRepository::class);
		$container->add(TagImport::class);
		$container->add(PluginArea::class)->addArgument(PluginRepository::class);
		$container->add(PluginExport::class);
		$container->add(PluginImport::class);

		$container->add(BoardIndex::class);
		$container->add(FrontPage::class)->addArgument(RendererInterface::class);
		$container->add(Block::class);
		$container->add(Page::class)->addArgument(PageRepository::class);
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
