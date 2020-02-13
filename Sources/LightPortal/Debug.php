<?php

namespace Bugo\LightPortal;

/**
 * Debug.php
 *
 * @package Light Portal
 * @link https://dragomano.ru/mods/light-portal
 * @author Bugo <bugo@dragomano.ru>
 * @copyright 2019-2020 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 1.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Debug
{
	/**
	 * Start time of script execution
	 *
	 * Время начала выполнения скрипта
	 *
	 * @var float
	 */
	private static $time_start = .0;

	/**
	 * Initial amount of allocated memory
	 *
	 * Первоначальный объём выделенной памяти
	 *
	 * @var int
	 */
	private static $memory_value = 0;

	/**
	 * Start of execution time, the initial amount of memory
	 *
	 * Начало выполнения скрипта, первоначальный объём памяти
	 *
	 * @return void
	 */
	public static function start()
	{
		self::$time_start   = microtime(true);
		self::$memory_value = memory_get_usage(true);
	}

	/**
	 * Get the difference between the current timestamp and the self::$start timestamp
	 *
	 * Получаем разницу между текущей меткой времени и меткой self::$start
	 *
	 * @return float
	 */
	public static function getScriptExecutionTime()
	{
		return round(microtime(true) - self::$time_start, 3);
	}

	/**
	 * Get the amount of memory allocated to the script, in megabytes
	 *
	 * Получаем объем памяти, выделенной скрипту, в мегабайтах
	 *
	 * @return int
	 */
	public static function getUsageMemory()
	{
		return (memory_get_usage(true) - self::$memory_value) / 1024 / 1024;
	}
}
