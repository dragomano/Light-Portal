<?php declare(strict_types=1);

/**
 * @phpVersion >= 8.0
 */

namespace Tests\Utils;

use Bugo\LightPortal\Helper;
use Tester\Assert;

require_once dirname(__DIR__) . '/bootstrap.php';

setUp(function () {
	$_REQUEST = [];
});

test('get method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test'] = 1;

	Assert::same($_REQUEST['test'], $class->request('test'));
	Assert::same($class->request('test'), $class->request()->get('test'));
});

test('put method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 1;

	$class->request()->put('test2', 1);
	Assert::same($_REQUEST['test1'], $class->request('test2'));
	Assert::same(1, $class->request()->get('test2'));

	$class->request()->put('test2', 2);
	Assert::notSame($_REQUEST['test1'], $class->request('test2'));
});

test('all method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 1;
	$_REQUEST['test2'] = 2;

	Assert::same([
		'test1' => 1,
		'test2' => 2,
	], $class->request()->all());
});

test('only method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 1;
	$_REQUEST['test2'] = 2;
	$_REQUEST['test3'] = 3;

	Assert::same(['test2' => 2, 'test3' => 3], $class->request()->only(['test2', 'test3']));
	Assert::same([], $class->request()->only(['test4', 'test5']));
	Assert::same(array_intersect_key($class->request()->all(), array_flip(['test2', 'test3'])), $class->request()->only(['test2', 'test3']));
});

test('has method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 1;
	$_REQUEST['test2'] = 1;

	Assert::true($class->request()->has('test1'));
	Assert::false($class->request()->has('test3'));
	Assert::true($class->request()->has(['test1', 'test2']));
	Assert::false($class->request()->has(['test1', 'test3']));
});

test('hasNot method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 1;
	$_REQUEST['test2'] = 1;

	Assert::false($class->request()->hasNot('test1'));
	Assert::true($class->request()->hasNot('test3'));
	Assert::false($class->request()->hasNot(['test1', 'test2']));
	Assert::true($class->request()->hasNot(['test1', 'test3']));
});

test('isEmpty method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 0;
	$_REQUEST['test2'] = 1;

	Assert::true($class->request()->isEmpty('test1'));
	Assert::false($class->request()->isEmpty('test2'));
});

test('isNotEmpty method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['test1'] = 0;
	$_REQUEST['test2'] = 1;

	Assert::false($class->request()->isNotEmpty('test1'));
	Assert::true($class->request()->isNotEmpty('test2'));
});

test('is method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['action'] = 'portal';
	Assert::true($class->request()->is('portal'));

	unset($_REQUEST['action']);
	Assert::false($class->request()->is('portal'));
});

test('isNot method', function () {
	$class = new class {
		use Helper;
	};

	$_REQUEST['action'] = 'portal';
	Assert::false($class->request()->isNot('portal'));

	unset($_REQUEST['action']);
	Assert::true($class->request()->isNot('portal'));
});

test('url method', function () {
	$class = new class {
		use Helper;
	};

	$_SERVER['REQUEST_URL'] = '/test';
	Assert::same('/test', $class->request()->url());

	unset($_SERVER['REQUEST_URL']);
	Assert::same('', $class->request()->url());
});

test('json method', function () {
	$class = new class {
		use Helper;

		public function json($input = null, ?string $key = null, mixed $default = null)
		{
			if ($input) {
				$data = json_decode($input, true) ?? [];
				return $key ? ($data[$key] ?? $default) : $data;
			}

			return $this->request()->json($key, $default);
		}
	};

	// Test json with key
	$input = json_encode(['key' => 'value']);
	file_put_contents('php://memory', $input);
	Assert::same('value', $class->json($input, 'key'));

	// Test json without key
	$input = json_encode(['key' => 'value']);
	file_put_contents('php://memory', $input);
	Assert::same(['key' => 'value'], $class->json($input));

	// Test json with default value
	$input = json_encode(['key' => 'value']);
	file_put_contents('php://memory', $input);
	Assert::same('default', $class->json($input, 'another_key', 'default'));
});
