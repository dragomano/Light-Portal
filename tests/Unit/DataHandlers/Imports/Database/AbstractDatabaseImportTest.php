<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseImport;
use LightPortal\DataHandlers\Imports\Database\DatabaseImportInterface;
use LightPortal\DataHandlers\Traits\HasDataOperations;
use LightPortal\Utils\Traits\HasRequest;

arch()->expect(AbstractDatabaseImport::class)
    ->toBeAbstract()
    ->toImplement(DatabaseImportInterface::class)
    ->toUseTraits([HasDataOperations::class, HasRequest::class]);
