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

namespace LightPortal\Areas;

use Bugo\Compat\Config;
use Bugo\Compat\ErrorHandler;
use Bugo\Compat\Lang;
use Bugo\Compat\Security;
use Bugo\Compat\User;
use Bugo\Compat\Utils;
use LightPortal\Areas\Traits\HasArea;
use LightPortal\Enums\Tab;
use LightPortal\Events\EventDispatcherInterface;
use LightPortal\Models\FactoryInterface;
use LightPortal\Repositories\DataManagerInterface;
use LightPortal\UI\TemplateLoader;
use LightPortal\UI\Fields\CustomField;
use LightPortal\UI\Fields\TextField;
use LightPortal\UI\Partials\SelectFactory;
use LightPortal\UI\Tables\PortalTableBuilder;
use LightPortal\UI\Tables\PortalTableBuilderInterface;
use LightPortal\Utils\Icon;
use LightPortal\Utils\Language;
use LightPortal\Utils\Str;
use LightPortal\Validators\ValidatorInterface;

use function LightPortal\app;

use const LP_NAME;

if (! defined('SMF'))
	die('No direct access...');

abstract class AbstractArea implements AreaInterface
{
	use HasArea;

	protected bool $shouldProcessAddForm = true;

	public function __construct(
		protected readonly DataManagerInterface $repository,
		protected readonly EventDispatcherInterface $dispatcher
	)
	{
		$this->checkPermissions();
	}

	public function main(): void
	{
		$this->beforeMain();
		$this->setupMainContext();
		$this->performActions();
		$this->performMassActions();
		$this->showMainContent();
		$this->afterMain();
	}

	public function add(): void
	{
		$this->setupAddContext();
		$this->initializeCurrentEntity();

		if ($this->shouldPrepareLanguages()) {
			Language::prepareList();
		}

		if ($this->shouldProcessAddForm) {
			$this->validateData();
			$this->prepareFormFields();
			$this->prepareEditor();
			$this->preparePreview();
			$this->repository->setData();
			$this->afterFormProcess();

			TemplateLoader::fromFile($this->getEditTemplateName());
		}
	}

	public function edit(): void
	{
		$item = $this->getItemId();

		$this->setupEditContext($item);

		if ($this->shouldPrepareLanguages()) {
			Language::prepareList();
		}

		$this->loadCurrentEntity($item);

		if ($this->handleRemoveRequest($item))
			return;

		$this->validateData();
		$this->updateEditContextTitle();
		$this->prepareFormFields();
		$this->prepareEditor();
		$this->preparePreview();
		$this->repository->setData($this->getCurrentEntityId());

		TemplateLoader::fromFile($this->getEditTemplateName());
	}

	abstract protected function getEntityName(): string;

	abstract protected function getEntityNamePlural(): string;

	abstract protected function getCustomActionHandlers(): array;

	abstract protected function getValidatorClass(): string;

	abstract protected function getFactoryClass(): string;

	protected function checkPermissions(): void
	{
		User::$me->isAllowedTo('admin_forum');
	}

	protected function beforeMain(): void {}

	protected function setupMainContext(): void
	{
		$entityPlural = $this->getEntityNamePlural();

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' . Lang::$txt["lp_{$entityPlural}_manage"];

		Utils::$context['form_action'] = Config::$scripturl .
			"?action=admin;area=lp_$entityPlural" . $this->getMainFormActionSuffix();

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = $this->getMainTabData();
	}

	protected function getMainTabData(): array
	{
		$entityPlural = $this->getEntityNamePlural();

		return [
			'title'       => LP_NAME,
			'description' => Lang::$txt["lp_{$entityPlural}_manage_description"],
		];
	}

	protected function getMainFormActionSuffix(): string
	{
		return '';
	}

	protected function performActions(): void
	{
		if ($this->request()->hasNot('actions'))
			return;

		$data = $this->request()->json();
		$this->processActions($data);

		$this->clearCache();

		exit;
	}

	protected function processActions(array $data): void
	{
		foreach ($this->getActionHandlers() as $key => $handler) {
			if (isset($data[$key])) {
				$handler($data);
				break;
			}
		}
	}

	final protected function getActionHandlers(): array
	{
		return array_merge([
			'delete_item' => fn($data) => $this->repository->remove($data['delete_item']),
			'toggle_item' => fn($data) => $this->repository->toggleStatus($data['toggle_item']),
		], $this->getCustomActionHandlers());
	}

	protected function performMassActions(): void {}

	protected function clearCache(): void
	{
		$this->langCache($this->getCacheKey())->forget();
	}

	protected function getCacheKey(): string
	{
		return 'active_' . $this->getEntityNamePlural();
	}

	protected function showMainContent(): void
	{
		$this->buildAndShowTable();
	}

	protected function afterMain(): void {}

	protected function buildAndShowTable(): void
	{
		$this->getTablePresenter()->show($this->buildTable());
	}

	protected function buildTable(): PortalTableBuilderInterface
	{
		return PortalTableBuilder::make('lp_' . $this->getEntityNamePlural(), $this->getTableTitle())
			->setDefaultSortColumn($this->getDefaultSortColumn())
			->setScript($this->getTableScript())
			->withCreateButton($this->getEntityNamePlural())
			->setItems($this->repository->getAll(...))
			->setCount($this->repository->getTotalCount(...))
			->addColumns($this->getTableColumns());
	}

	protected function getTableTitle(): string
	{
		$count = Utils::$context['lp_quantities']['active_' . $this->getEntityNamePlural()] ?? 0;
		$count = empty($count) ? '' : " ($count)";

		return Lang::$txt['lp_' . $this->getEntityNamePlural()] . $count;
	}

	protected function getDefaultSortColumn(): string
	{
		return 'title';
	}

	protected function getTableScript(): string
	{
		$entityClass = ucfirst($this->getEntityName());

		return "const entity = new $entityClass();";
	}

	protected function getTableColumns(): array
	{
		return [];
	}

	protected function setupAddContext(): void
	{
		$entityPlural = $this->getEntityNamePlural();

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' .
			Lang::$txt["lp_{$entityPlural}_add_title"];

		Utils::$context['page_area_title'] = Lang::$txt["lp_{$entityPlural}_add_title"];

		Utils::$context['form_action'] = Config::$scripturl .
			"?action=admin;area=lp_$entityPlural;sa=add";

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt["lp_{$entityPlural}_add_description"],
		];

		$this->setupAdditionalAddContext();
	}

	protected function setupAdditionalAddContext(): void {}

	protected function initializeCurrentEntity(): void
	{
		Utils::$context[$this->getCurrentEntityContextKey()] ??= [];
	}

	protected function getCurrentEntityContextKey(): string
	{
		return 'lp_current_' . $this->getEntityName();
	}

	protected function shouldPrepareLanguages(): bool
	{
		return true;
	}

	protected function validateData(): void
	{
		$this->prepareValidationContext();

		$validatedData = $this->getValidator()->validate();

		$entity = $this->getFactory()->create(
			array_merge($this->getCurrentEntity(), $validatedData)
		);

		$this->setContextEntity($entity->toArray());

		$this->postProcessValidation();
	}

	protected function prepareValidationContext(): void	{}

	protected function getValidator(): ValidatorInterface
	{
		return app($this->getValidatorClass());
	}

	protected function getFactory(): FactoryInterface
	{
		return app($this->getFactoryClass());
	}

	protected function getCurrentEntity(): array
	{
		return Utils::$context[$this->getCurrentEntityContextKey()];
	}

	protected function setContextEntity(array $data): void
	{
		Utils::$context[$this->getEntityContextKey()] = $data;
	}

	protected function getEntityContextKey(): string
	{
		return 'lp_' . $this->getEntityName();
	}

	protected function postProcessValidation(): void {}

	protected function prepareFormFields(): void
	{
		$this->prepareTitleFields($this->shouldRequireTitleFields());
		$this->prepareCommonFields();
		$this->prepareSpecificFields();
		$this->dispatchFieldsEvent();
		$this->preparePostFields();
	}

	protected function prepareCommonFields(): void
	{
		CustomField::make('icon', Lang::$txt['current_icon'])
			->setTab(Tab::APPEARANCE)
			->setValue(fn() => SelectFactory::icon([
				'icon' => $this->getContextEntity()['icon'] ?? '',
			]));

		$this->prepareSlugField();
	}

	protected function prepareSlugField(): void
	{
		TextField::make('slug', Lang::$txt['lp_slug'])
			->setTab(Tab::SEO)
			->setDescription(Lang::$txt['lp_slug_subtext'])
			->required()
			->setAttribute('maxlength', 255)
			->setAttribute('pattern', LP_ALIAS_PATTERN)
			->setAttribute(
				'x-slug.lazy',
				empty($this->getContextEntity()['id']) ? 'title' : '{}'
			)
			->setValue($this->getContextEntity()['slug'] ?? '');
	}

	protected function prepareSpecificFields(): void {}

	protected function prepareEditor(): void {}

	protected function preparePreview(): void
	{
		if ($this->request()->hasNot('preview'))
			return;

		if ($this->shouldFlushCache()) {
			$this->cache()->flush();
		}

		Security::checkSubmitOnce('free');

		$entity = $this->getContextEntity();

		Utils::$context['preview_title'] = Str::decodeHtmlEntities($entity['title'] ?? '');

		$this->preparePreviewContent($entity);

		Str::cleanBbcode(Utils::$context['preview_title']);
		Lang::censorText(Utils::$context['preview_title']);

		Utils::$context['page_title'] = Lang::$txt['preview'] . (
			Utils::$context['preview_title'] ? ' - ' . Utils::$context['preview_title'] : ''
		);

		$this->finalizePreviewTitle($entity);
	}

	protected function shouldFlushCache(): bool
	{
		return false;
	}

	protected function preparePreviewContent(array $entity): void
	{
		Utils::$context['preview_content'] = Utils::htmlspecialchars(
			$entity['content'] ?? $entity['description'] ?? '',
			ENT_QUOTES
		);

		Lang::censorText(Utils::$context['preview_content']);
	}

	protected function afterFormProcess(): void {}

	protected function getItemId(): int
	{
		$entityName = $this->getEntityName();

		return Str::typed('int',
			$this->request()->get("{$entityName}_id") ?: $this->request()->get('id')
		);
	}

	protected function setupEditContext(int $item): void
	{
		$entityPlural = $this->getEntityNamePlural();

		Utils::$context['page_title'] = Lang::$txt['lp_portal'] . ' - ' .
			Lang::$txt["lp_{$entityPlural}_edit_title"];

		Utils::$context['page_area_title'] = Lang::$txt["lp_{$entityPlural}_edit_title"];

		Utils::$context['form_action'] = Config::$scripturl .
			"?action=admin;area=lp_$entityPlural;sa=edit;id=$item";

		Utils::$context[Utils::$context['admin_menu_name']]['tab_data'] = [
			'title'       => LP_NAME,
			'description' => Lang::$txt["lp_{$entityPlural}_edit_description"],
		];
	}

	protected function loadCurrentEntity(int $item): void
	{
		$data = $this->repository->getData($item);

		$this->postProcessLoadedData($data);

		Utils::$context[$this->getCurrentEntityContextKey()] = $data;

		if (empty(Utils::$context[$this->getCurrentEntityContextKey()])) {
			ErrorHandler::fatalLang("lp_{$this->getEntityName()}_not_found", false, status: 404);
		}

		$this->validateEntityPermissions();
	}

	protected function postProcessLoadedData(array &$data): void {}

	protected function validateEntityPermissions(): void {}

	protected function getCurrentEntityId(): int
	{
		return $this->getContextEntity()['id'];
	}

	protected function getEditTemplateName(): string
	{
		return 'admin/' . $this->getEntityName() . '_edit';
	}

	protected function shouldRequireTitleFields(): bool
	{
		return true;
	}

	protected function finalizePreviewTitle(array $entity): void
	{
		Utils::$context['preview_title'] = Icon::parse($entity['icon'] ?? '') .
			Utils::$context['preview_title'];
	}

	protected function handleRemoveRequest(int $item): bool
	{
		if ($this->request()->hasNot('remove')) {
			return false;
		}

		$this->beforeRemove($item);
		$this->repository->remove($item);
		$this->clearCache();

		$this->response()->redirect(
			"action=admin;area=lp_{$this->getEntityNamePlural()}" . $this->getRemoveRedirectSuffix()
		);

		return true;
	}

	protected function beforeRemove(int $item): void {}

	protected function getRemoveRedirectSuffix(): string
	{
		return '';
	}

	protected function updateEditContextTitle(): void
	{
		$entityPlural = $this->getEntityNamePlural();
		$entity = $this->getContextEntity();
		$title = $entity['title'] ?? '';

		Utils::$context['page_area_title'] = Lang::$txt["lp_{$entityPlural}_edit_title"] .
			($title ? ' - ' . $title : '');

		Utils::$context['form_action'] = Config::$scripturl .
			"?action=admin;area=lp_$entityPlural;sa=edit;id={$entity['id']}";
	}

	protected function getContextEntity(): array
	{
		return Utils::$context[$this->getEntityContextKey()];
	}
}
