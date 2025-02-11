<?php declare(strict_types=1);

/**
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2025 Bugo
 * @license https://spdx.org/licenses/GPL-3.0-or-later.html GPL-3.0-or-later
 *
 * @version 2.9
 */

namespace Bugo\LightPortal;

use Bugo\Bricks\Presenters\TablePresenter;
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
use Bugo\LightPortal\Lists\TitleList;
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
use Bugo\LightPortal\UI\Tables\TableRenderer;
use Bugo\LightPortal\Utils\Cache;
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
		Cache::class,
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
		TitleList::class,
		Weaver::class,
	];

	public function provides(string $id): bool
	{
		return in_array($id, $this->services);
	}

	public function register(): void
	{
		$this->getContainer()->add(PortalApp::class);
		$this->getContainer()->add(Integration::class);
		$this->getContainer()->add(ConfigArea::class);
		$this->getContainer()->add(CreditArea::class);

		$this->getContainer()->add(RendererInterface::class, Blade::class);
		$this->getContainer()->add(TablePresenter::class)->addArgument(TableRenderer::class);
		$this->getContainer()->add(TableRenderer::class);

		$this->getContainer()->add(EventManager::class);
		$this->getContainer()->add(AssetHandler::class);
		$this->getContainer()->add(ConfigHandler::class);
		$this->getContainer()->add(LangHandler::class);
		$this->getContainer()->add(EventManagerFactory::class);
		$this->getContainer()->add(PluginHandler::class, fn() => fn(array $plugins = []) => new PluginHandler($plugins));

		$this->getContainer()->add(CategoryList::class, fn() => (new CategoryList())());
		$this->getContainer()->add(PageList::class, fn() => (new PageList(app(PageRepository::class)))());
		$this->getContainer()->add(TagList::class, fn() => (new TagList())());
		$this->getContainer()->add(TitleList::class, fn() => (new TitleList())());
		$this->getContainer()->add(IconList::class, fn() => (new IconList())());
		$this->getContainer()->add(PluginList::class, fn() => (new PluginList())());
		$this->getContainer()->add(SessionManager::class, fn() => (new SessionManager())());

		$this->getContainer()->add(Request::class);
		$this->getContainer()->add(Response::class);
		$this->getContainer()->add(Post::class);
		$this->getContainer()->add(File::class);
		$this->getContainer()->add(Cache::class, fn() => fn(?string $key = null) => new Cache($key));
		$this->getContainer()->add(Session::class, fn() => fn(?string $key = null) => new Session($key));

		$this->getContainer()->add(BlockRepository::class);
		$this->getContainer()->add(CategoryRepository::class);
		$this->getContainer()->add(CommentRepository::class);
		$this->getContainer()->add(PageRepository::class);
		$this->getContainer()->add(PluginRepository::class);
		$this->getContainer()->add(TagRepository::class);

		$this->getContainer()->add(BlockArea::class)->addArgument(BlockRepository::class);
		$this->getContainer()->add(BlockExport::class)->addArgument(BlockRepository::class);
		$this->getContainer()->add(BlockImport::class);
		$this->getContainer()->add(PageArea::class)->addArgument(PageRepository::class);
		$this->getContainer()->add(PageExport::class)->addArgument(PageRepository::class);
		$this->getContainer()->add(PageImport::class);
		$this->getContainer()->add(CategoryArea::class)->addArgument(CategoryRepository::class);
		$this->getContainer()->add(CategoryExport::class)->addArgument(CategoryRepository::class);
		$this->getContainer()->add(CategoryImport::class);
		$this->getContainer()->add(TagArea::class)->addArgument(TagRepository::class);
		$this->getContainer()->add(TagExport::class)->addArgument(TagRepository::class);
		$this->getContainer()->add(TagImport::class);
		$this->getContainer()->add(PluginArea::class)->addArgument(PluginRepository::class);
		$this->getContainer()->add(PluginExport::class);
		$this->getContainer()->add(PluginImport::class);

		$this->getContainer()->add(BoardIndex::class);
		$this->getContainer()->add(FrontPage::class)->addArgument(RendererInterface::class);
		$this->getContainer()->add(Block::class);
		$this->getContainer()->add(Page::class)->addArgument(PageRepository::class);
		$this->getContainer()->add(Comment::class)->addArgument(CommentRepository::class);
		$this->getContainer()->add(Category::class)->addArgument(CardListInterface::class);
		$this->getContainer()->add(Tag::class)->addArgument(CardListInterface::class);
		$this->getContainer()->add(CardListInterface::class, CardList::class);

		$this->getContainer()->add(BlockValidator::class);
		$this->getContainer()->add(BlockFactory::class);
		$this->getContainer()->add(CategoryValidator::class);
		$this->getContainer()->add(CategoryFactory::class);
		$this->getContainer()->add(PageValidator::class);
		$this->getContainer()->add(PageFactory::class);
		$this->getContainer()->add(TagValidator::class);
		$this->getContainer()->add(TagFactory::class);

		$this->getContainer()->add(Weaver::class, Weaver::class)->setShared(false);
	}
}
