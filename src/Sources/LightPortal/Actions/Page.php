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

namespace LightPortal\Actions;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Theme;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Enums\EntryType;
use LightPortal\Enums\FrontPageMode;
use LightPortal\Enums\PortalHook;
use LightPortal\Enums\PortalSubAction;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Repositories\PageRepositoryInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\Utils\Content;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Setting;
use LightPortal\Utils\Traits\HasBreadcrumbs;
use LightPortal\Utils\Traits\HasCache;
use LightPortal\Utils\Traits\HasRequest;
use LightPortal\Utils\Traits\HasResponse;
use LightPortal\Utils\Traits\HasSorting;
use SimpleSEF;

use function LightPortal\app;

use const LP_ACTION;
use const LP_PAGE_PARAM;
use const LP_PAGE_URL;

if (! defined('SMF'))
	die('No direct access...');

final readonly class Page implements ActionInterface
{
	use HasCache;
	use HasBreadcrumbs;
	use HasRequest;
	use HasResponse;
	use HasSorting;

	public function __construct(
		private PageRepositoryInterface $repository,
		private EventDispatcherInterface $dispatcher
	) {}

	public function show(): void
	{
		User::$me->isAllowedTo('light_portal_view');

		$this->prepareSorting('frontpage_sorting');

		$slug = $this->request()->get(LP_PAGE_PARAM);

		if (empty($slug)) {
			$this->handleEmptySlug();
		} else {
			$slug = explode(';', (string) $slug)[0];
			$this->handleNonEmptySlug($slug);
		}

		$this->handlePageNotFound();
		$this->handlePagePermissions();

		if (Utils::$context['lp_page']['created_at'] > time()) {
			Utils::sendHttpStatus(404);
		}

		Utils::$context['lp_page']['errors'] = [];
		if (empty(Utils::$context['lp_page']['status']) && Utils::$context['lp_page']['can_edit']) {
			Utils::$context['lp_page']['errors'][] = Lang::$txt['lp_page_visible_but_disabled'];
		}

		Utils::$context['lp_page']['content'] = Content::parse(
			Utils::$context['lp_page']['content'], Utils::$context['lp_page']['type']
		);

		$this->setPageTitleAndCanonicalUrl($slug);

		Utils::$context['lp_page_edit_link'] = Config::$scripturl . '?action=admin;area=lp_pages;sa=edit;id=' . Utils::$context['lp_page']['id'];
		Utils::$context['lp_comments_api_endpoint'] = Utils::$context['canonical_url'] . ';fetch_data';

		Utils::$context['lp_page']['url'] = Utils::$context['canonical_url'] . (
			$this->request()->has(LP_PAGE_PARAM) ? ';' : '?'
		);

		$this->handlePromoteAction();
		$this->prepareMetadata();
		$this->prepareNavigationLinks();
		$this->prepareRelatedPages();
		$this->prepareComments();
		$this->updateNumViews();

		TemplateLoader::fromFile('page_view');
	}

	public function getDataBySlug(string $slug): array
	{
		if (empty($slug)) {
			return [];
		}

		$data = $this->langCache('page_' . $slug)->setFallback(fn() => $this->repository->getData($slug));

		$this->repository->prepareData($data);

		return $data;
	}

	private function handleEmptySlug(): void
	{
		if (
			Setting::isFrontpageMode(FrontPageMode::CHOSEN_PAGE->value)
			&& isset(Config::$modSettings['lp_frontpage_chosen_page'])
		) {
			Utils::$context['lp_page'] = $this->getDataBySlug(Config::$modSettings['lp_frontpage_chosen_page']);
		} else {
			Config::updateModSettings(['lp_frontpage_mode' => 0]);
		}
	}

	private function handleNonEmptySlug(string $slug): void
	{
		if (Setting::isFrontpage($slug)) {
			$this->response()->redirect('action=' . LP_ACTION);
		}

		Utils::$context['lp_page'] = $this->getDataBySlug($slug);
	}

	private function handlePageNotFound(): void
	{
		if (empty(Utils::$context['lp_page'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('lp_page_not_found', false, status: 404);
		}
	}

	private function handlePagePermissions(): void
	{
		$page = Utils::$context['lp_page'];

		if (empty($page['can_view'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('cannot_light_portal_view_page', false);
		}

		if ($page['entry_type'] === EntryType::DRAFT->name() && $page['author_id'] !== User::$me->id) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('cannot_light_portal_view_page', false);
		}

		if (empty($page['status']) && empty($page['can_edit'])) {
			$this->changeErrorPage();
			ErrorHandler::fatalLang('lp_page_not_activated', false);
		}
	}

	private function setPageTitleAndCanonicalUrl(?string $slug): void
	{
		if (empty($slug)) {
			Utils::$context['page_title'] = Utils::$context['lp_page']['title'] ?: Lang::$txt['lp_portal'];

			Utils::$context['canonical_url'] = Config::$scripturl;

			$this->breadcrumbs()->add(Lang::$txt['lp_portal']);
		} else {
			Utils::$context['page_title'] = Utils::$context['lp_page']['title'] ?: Lang::$txt['lp_post_error_no_title'];

			Utils::$context['canonical_url'] = LP_PAGE_URL . $slug;

			if (! empty(Utils::$context['lp_page']['cat_title'])) {
				$this->breadcrumbs()->add(
					Utils::$context['lp_page']['cat_title'],
					PortalSubAction::CATEGORIES->url() . ';id=' . Utils::$context['lp_page']['category_id'],
					Utils::$context['lp_page']['cat_icon']
				);
			}

			$this->breadcrumbs()->add(Utils::$context['page_title']);
		}
	}

	private function changeErrorPage(): void
	{
		Utils::$context['error_link'] = Config::$scripturl;
		Lang::$txt['back'] = empty(Config::$modSettings['lp_frontpage_mode'])
			? Lang::$txt['lp_forum']
			: Lang::$txt['lp_portal'];

		if (Lang::$txt['back'] === Lang::$txt['lp_portal']) {
			Lang::$txt['back'] = Lang::$txt['lp_forum'];
			Utils::$context['error_link'] .= '">'
				. Lang::$txt['lp_portal']
				. '</a> <a class="button floatnone" href="' . Config::$scripturl . '?action=forum';
		}
	}

	private function handlePromoteAction(): void
	{
		if (empty(User::$me->is_admin) || $this->request()->hasNot(PortalSubAction::PROMOTE->name()))
			return;

		$page = Utils::$context['lp_page']['id'];

		$frontPages = Setting::getFrontpagePages();

		if (($key = array_search($page, $frontPages)) !== false) {
			unset($frontPages[$key]);
		} else {
			$frontPages[] = $page;
		}

		Config::updateModSettings([
			'lp_frontpage_pages' => implode(',', $frontPages)
		]);

		$this->cache()->flush();

		$this->response()->redirect(LP_PAGE_PARAM . '=' . Utils::$context['lp_page']['slug']);
	}

	private function prepareMetadata(): void
	{
		if (empty(Utils::$context['lp_page']))
			return;

		Utils::$context['meta_description'] = Utils::$context['lp_page']['description'];

		$keywords = [];
		if (isset(Utils::$context['lp_page']['tags'])) {
			$keywords = array_column(Utils::$context['lp_page']['tags'], 'title');

			Config::$modSettings['meta_keywords'] = implode(', ', $keywords);
		}

		Utils::$context['meta_tags'][] = [
			'prefix'   => 'article: https://ogp.me/ns/article#',
			'property' => 'og:type',
			'content'  => 'article',
		];

		Utils::$context['meta_tags'][] = [
			'prefix'   => 'article: https://ogp.me/ns/article#',
			'property' => 'article:author',
			'content'  => Utils::$context['lp_page']['author'],
		];

		Utils::$context['meta_tags'][] = [
			'prefix'   => 'article: https://ogp.me/ns/article#',
			'property' => 'article:published_time',
			'content'  => date('Y-m-d\TH:i:s', (int) Utils::$context['lp_page']['created_at']),
		];

		if (Utils::$context['lp_page']['updated_at']) {
			Utils::$context['meta_tags'][] = [
				'prefix'   => 'article: https://ogp.me/ns/article#',
				'property' => 'article:modified_time',
				'content'  => date('Y-m-d\TH:i:s', (int) Utils::$context['lp_page']['updated_at']),
			];
		}

		if (! empty(Utils::$context['lp_page']['cat_title'])) {
			Utils::$context['meta_tags'][] = [
				'prefix'   => 'article: https://ogp.me/ns/article#',
				'property' => 'article:section',
				'content'  => Utils::$context['lp_page']['cat_title'],
			];
		}

		foreach ($keywords as $value) {
			Utils::$context['meta_tags'][] = [
				'prefix'   => 'article: https://ogp.me/ns/article#',
				'property' => 'article:tag',
				'content'  => $value,
			];
		}

		if (! (empty(Config::$modSettings['lp_page_og_image']) || empty(Utils::$context['lp_page']['image']))) {
			Theme::$current->settings['og_image'] = Utils::$context['lp_page']['image'];
		}
	}

	private function prepareNavigationLinks(): void
	{
		if (empty($page = Utils::$context['lp_page']) || empty(Config::$modSettings['lp_show_prev_next_links']))
			return;

		$withinCategory = str_contains(
			filter_input(INPUT_SERVER, 'HTTP_REFERER') ?? '',
			'action=' . LP_ACTION . ';sa=' . PortalSubAction::CATEGORIES->name() . ';id'
		);
		$withinCategory = $this->request()->has('from_category') ? true : $withinCategory;

		[$prevTitle, $prevSlug, $nextTitle, $nextSlug] = $this->langCache('page_' . $page['slug'] . '_prev_next')
			->setFallback(fn() => $this->repository->getPrevNextLinks($page, $withinCategory));

		$suffix = $withinCategory ? ';from_category' : '';

		if (! empty($prevSlug)) {
			Utils::$context['lp_page']['prev'] = [
				'link'  => LP_PAGE_URL . $prevSlug . $suffix,
				'title' => $prevTitle,
			];
		}

		if (! empty($nextSlug)) {
			Utils::$context['lp_page']['next'] = [
				'link'  => LP_PAGE_URL . $nextSlug . $suffix,
				'title' => $nextTitle,
			];
		}
	}

	private function prepareRelatedPages(): void
	{
		if (empty($page = Utils::$context['lp_page']) || empty(Setting::showRelatedPages()))
			return;

		if (empty(Utils::$context['lp_page']['options']['show_related_pages']))
			return;

		Utils::$context['lp_page']['related_pages'] = $this->langCache('page_' . $page['slug'] . '_related')
			->setFallback(fn() => $this->repository->getRelatedPages($page));
	}

	private function prepareComments(): void
	{
		if (Setting::getCommentBlock() === '' || Setting::getCommentBlock() === 'none')
			return;

		if (empty(Utils::$context['lp_page']['options']['allow_comments']))
			return;

		Lang::load('Editor');

		$this->dispatcher->dispatch(PortalHook::comments);

		if (isset(Utils::$context['lp_' . Setting::getCommentBlock() . '_comment_block']))
			return;

		$this->handleApi();

		app(Comment::class)->show();
	}

	private function handleApi(): void
	{
		if ($this->request()->hasNot('fetch_data'))
			return;

		$this->response()->exit($this->preparedData());
	}

	private function preparedData(): array
	{
		$txtData = [
			'pages'         => Lang::$txt['pages'],
			'author'        => Lang::$txt['author'],
			'reply'         => Lang::$txt['reply'],
			'modify'        => Lang::$txt['modify'],
			'modify_cancel' => Lang::$txt['modify_cancel'],
			'remove'        => Lang::$txt['remove'],
			'add_comment'   => Lang::$txt['lp_comment_placeholder'],
			'post'          => Lang::$txt['post'],
			'save'          => Lang::$txt['save'],
			'title'         => Lang::$txt['lp_comments_title'],
			'prev'          => Lang::$txt['prev'],
			'next'          => Lang::$txt['next'],
			'bold'          => Lang::$editortxt['bold'],
			'italic'        => Lang::$editortxt['italic'],
			'quote'         => Lang::$editortxt['insert_quote'],
			'code'          => Lang::$editortxt['code'],
			'link'          => Lang::$editortxt['insert_link'],
			'image'         => Lang::$editortxt['insert_image'],
			'list'          => Lang::$editortxt['bullet_list'],
			'task_list'     => Lang::$txt['lp_task_list'],
			'updated'       => Lang::$txt['lp_updated'],
		];

		$pageUrl = Utils::$context['lp_page']['url'];

		if (class_exists('\SimpleSEF', false)) {
			$pageUrl = (new SimpleSEF())->getSefUrl($pageUrl);
		}

		$contextData = [
			'locale'  => Lang::$txt['lang_dictionary'],
			'pageUrl' => $pageUrl,
			'charset' => Utils::$context['character_set'],
		];

		$settingsData = [
			'lp_comment_sorting' => Config::$modSettings['lp_comment_sorting'] ?? '0',
		];

		return [
			'txt'      => $txtData,
			'context'  => $contextData,
			'settings' => $settingsData,
			'icons'    => Icon::all(),
			'user'     => Utils::$context['user'],
		];
	}

	private function updateNumViews(): void
	{
		if (empty(Utils::$context['lp_page']['id']) || User::$me->possibly_robot)
			return;

		if (
			$this->session('lp')->isEmpty('last_page_viewed')
			|| $this->session('lp')->get('last_page_viewed') !== Utils::$context['lp_page']['id']
		) {
			$this->repository->updateNumViews(Utils::$context['lp_page']['id']);

			$this->session('lp')->put('last_page_viewed', Utils::$context['lp_page']['id']);
		}
	}
}
