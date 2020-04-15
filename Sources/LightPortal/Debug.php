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
	 * Initial amount of queries
	 *
	 * Первоначальное количество запросов
	 *
	 * @var int
	 */
	private static $num_queries = 0;

	/**
	 * Start of execution time, the initial amount of memory
	 *
	 * Начало выполнения скрипта, первоначальный объём памяти
	 *
	 * @return void
	 */
	public static function start()
	{
		self::$time_start  = microtime(true);
		self::$num_queries = 0;
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
	 * Get the number of requests to the database
	 *
	 * Получаем количество запросов к базе данных
	 *
	 * @return void
	 */
	public static function getNumQueries()
	{
		return self::$num_queries;
	}

	/**
	 * Update the number of database requests
	 *
	 * Обновляем количество запросов к базе данных
	 *
	 * @param int $num
	 * @return void
	 */
	public static function updateNumQueries(int $num = 1)
	{
		if (empty($num))
			return;

		self::$num_queries += $num;
	}
}
