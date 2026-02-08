<?php

declare(strict_types=1);

use LightPortal\DataHandlers\Imports\Database\AbstractDatabaseImport;
use LightPortal\DataHandlers\Imports\Database\DatabaseImportInterface;
use LightPortal\Utils\Traits\HasRequest;

arch()->expect(AbstractDatabaseImport::class)
    ->toBeAbstract()
    ->toImplement(DatabaseImportInterface::class)
    ->toUseTrait(HasRequest::class);
