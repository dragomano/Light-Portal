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

use Bugo\LightPortal\Actions\Block;
use Bugo\LightPortal\Actions\BoardIndex;
use Bugo\LightPortal\Actions\CardList;
use Bugo\LightPortal\Actions\Category;
use Bugo\LightPortal\Actions\FrontPage;
use Bugo\LightPortal\Actions\Page;
use Bugo\LightPortal\Actions\Tag;
use Bugo\LightPortal\Compilers\CompilerInterface;
use Bugo\LightPortal\Lists\CategoryList;
use Bugo\LightPortal\Lists\IconList;
use Bugo\LightPortal\Lists\PageList;
use Bugo\LightPortal\Lists\PluginList;
use Bugo\LightPortal\Lists\TagList;
use Bugo\LightPortal\Lists\TitleList;
use Bugo\LightPortal\Plugins\PluginHandler;
use Bugo\LightPortal\Plugins\PluginRegistry;
use Bugo\LightPortal\Renderers\RendererInterface;
use Bugo\LightPortal\Repositories\BlockRepository;
use Bugo\LightPortal\Repositories\CategoryRepository;
use Bugo\LightPortal\Repositories\CommentRepository;
use Bugo\LightPortal\Repositories\PageRepository;
use Bugo\LightPortal\Repositories\PluginRepository;
use Bugo\LightPortal\Repositories\TagRepository;
use Bugo\LightPortal\Utils\Cache;
use Bugo\LightPortal\Utils\ConfigProvider;
use Bugo\LightPortal\Utils\File;
use Bugo\LightPortal\Utils\Post;
use Bugo\LightPortal\Utils\Request;
use Bugo\LightPortal\Utils\Session;
use Bugo\LightPortal\Utils\Weaver;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Nette\Utils\Html;

use function in_array;

class ServiceProvider extends AbstractServiceProvider
{
	public function provides(string $id): bool
	{
		$services = [
			'event_manager',
			'events',
			'config',
			'compiler',
			'renderer',
			'plugin_handler',
			'plugin_registry',
			'category_list',
			'page_list',
			'tag_list',
			'title_list',
			'icon_list',
			'plugin_list',
			'active_blocks',
			'cache',
			'request',
			'post',
			'file',
			'session',
			'block_repo',
			'category_repo',
			'comment_repo',
			'page_repo',
			'plugin_repo',
			'tag_repo',
			'board_index',
			'front_page',
			'block',
			'page',
			'category',
			'tag',
			'card_list',
			'html',
			'weaver',
		];

		return in_array($id, $services);
	}

	public function register(): void
	{
		$this->getContainer()->add('event_manager', EventManager::class);
		$this->getContainer()->add('events', EventManagerFactory::class);
		$this->getContainer()->add('config', fn() => (new ConfigProvider())->get());
		$this->getContainer()->add('compiler', app('config')[CompilerInterface::class]);
		$this->getContainer()->add('renderer', app('config')[RendererInterface::class]);
		$this->getContainer()->add('plugin_handler', PluginHandler::class);
		$this->getContainer()->add('plugin_registry', PluginRegistry::class);

		$this->getContainer()->add('category_list', fn() => (new CategoryList())());
		$this->getContainer()->add('page_list', fn() => (new PageList())());
		$this->getContainer()->add('tag_list', fn() => (new TagList())());
		$this->getContainer()->add('title_list', fn() => (new TitleList())());
		$this->getContainer()->add('icon_list', fn() => (new IconList())());
		$this->getContainer()->add('plugin_list', fn() => (new PluginList())());

		$this->getContainer()->add('active_blocks', fn() => app('block_repo')->getActive());

		$this->getContainer()->add('cache', Cache::class);
		$this->getContainer()->add('request', Request::class);
		$this->getContainer()->add('post', Post::class);
		$this->getContainer()->add('file', File::class);
		$this->getContainer()->add('session', Session::class);

		$this->getContainer()->add('block_repo', BlockRepository::class);
		$this->getContainer()->add('category_repo', CategoryRepository::class);
		$this->getContainer()->add('comment_repo', CommentRepository::class);
		$this->getContainer()->add('page_repo', PageRepository::class);
		$this->getContainer()->add('plugin_repo', PluginRepository::class);
		$this->getContainer()->add('tag_repo', TagRepository::class);

		$this->getContainer()->add('board_index', BoardIndex::class);
		$this->getContainer()->add('front_page', FrontPage::class);
		$this->getContainer()->add('block', Block::class);
		$this->getContainer()->add('page', Page::class);
		$this->getContainer()->add('category', Category::class);
		$this->getContainer()->add('tag', Tag::class);
		$this->getContainer()->add('card_list', CardList::class);

		$this->getContainer()->add('html', Html::class)->setShared(false);
		$this->getContainer()->add('weaver', Weaver::class)->setShared(false);
	}
}
