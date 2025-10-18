<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Custom\AbstractCustomImport;
use LightPortal\DataHandlers\Imports\Custom\CustomImportInterface;
use LightPortal\DataHandlers\Traits\HasDataOperations;
use LightPortal\Utils\Traits\HasRequest;

arch()->expect(AbstractCustomImport::class)
    ->toBeAbstract()
    ->toImplement(CustomImportInterface::class)
    ->toUseTraits([
        HasDataOperations::class,
        HasRequest::class,
    ])
    ->toHaveMethods(['importItems', 'run']);
