<?php

namespace LightPortal\Database;

use Laminas\Db\Adapter\Driver\ResultInterface;

interface PortalResultInterface extends ResultInterface
{
	public function getGeneratedValues(string $name = 'id'): array;
}
