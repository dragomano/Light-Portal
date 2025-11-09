<?php

declare(strict_types=1);

use Bugo\Compat\Editor as BaseEditor;
use LightPortal\Utils\Editor;

arch()
    ->expect(Editor::class)
    ->toExtend(BaseEditor::class);
