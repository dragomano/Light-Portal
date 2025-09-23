<?php

declare(strict_types=1);

use Bugo\LightPortal\DataHandlers\Imports\Custom\AbstractCustomImport;
use Bugo\LightPortal\DataHandlers\Imports\Custom\CustomImportInterface;
use Bugo\LightPortal\DataHandlers\Traits\HasDataOperations;
use Bugo\LightPortal\Utils\Traits\HasRequest;

arch()->expect(AbstractCustomImport::class)
    ->toBeAbstract()
    ->toImplement(CustomImportInterface::class)
    ->toUseTraits([
        HasDataOperations::class,
        HasRequest::class,
    ])
    ->toHaveMethods(['importItems', 'run']);
