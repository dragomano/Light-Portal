<?php

declare(strict_types=1);

use LightPortal\Utils\GlobalArray;
use LightPortal\Utils\Post;
use LightPortal\Utils\PostInterface;

arch()
    ->expect(Post::class)
    ->toExtend(GlobalArray::class)
    ->toImplement(PostInterface::class);

describe('Post', function () {
    beforeEach(function () {
        $GLOBALS['_POST'] = [];

        $this->post = new Post();
    });

    afterEach(function () {
        $GLOBALS['_POST'] = [];
    });

    describe('__construct()', function () {
        it('initializes storage from $_POST', function () {
            $GLOBALS['_POST'] = ['test' => 'value'];

            expect($this->post->get('test'))->toBe('value');
        });

        it('works with empty $_POST', function () {
            $GLOBALS['_POST'] = [];

            expect($this->post->all())->toBeEmpty();
        });

        it('stores multiple values from $_POST', function () {
            $GLOBALS['_POST'] = [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3'
            ];

            expect($this->post->get('key1'))->toBe('value1')
                ->and($this->post->get('key2'))->toBe('value2')
                ->and($this->post->get('key3'))->toBe('value3');
        });
    });
});
