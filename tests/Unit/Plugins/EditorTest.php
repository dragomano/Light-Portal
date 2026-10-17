<?php

declare(strict_types=1);

use Bugo\Compat\Lang;
use Bugo\Compat\Utils;
use LightPortal\Plugins\Editor;
use LightPortal\Plugins\Event;

beforeEach(function () {
    Lang::$txt['lp_test_dummy_editor'] = Lang::$txt['lp_test_dummy_editor'] ?? [];
    Utils::$context['lp_test_dummy_editor_plugin'] = [];
});

it('serves supported content types', function () {
    $editor = new TestDummyEditor();

    expect($editor->servesContent(['type' => 'bbc']))->toBeTrue()
        ->and($editor->servesContent(['type' => 'html']))->toBeTrue()
        ->and($editor->servesContent(['options' => ['content' => 'markdown']]))->toBeTrue()
        ->and($editor->servesContent(['type' => 'unknown']))->toBeFalse()
        ->and($editor->servesContent([]))->toBeFalse();
});

class TestDummyEditor extends Editor
{
    public function prepareEditor(Event $e): void
    {
    }

    public function getSupportedContentTypes(): array
    {
        return ['bbc', 'html', 'markdown'];
    }
}
