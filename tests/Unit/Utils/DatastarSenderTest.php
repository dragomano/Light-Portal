<?php

declare(strict_types=1);

use LightPortal\Utils\DatastarSender;
use starfederation\datastar\ServerSentEventGenerator;
use starfederation\datastar\enums\ElementPatchMode;
describe('DatastarSender', function () {
    beforeEach(function () {
        $this->serverBackup = $_SERVER;

        unset($_SERVER['HTTP_DATASTAR_REQUEST']);
        unset($_GET['datastar']);
    });

    afterEach(function () {
        $_SERVER = $this->serverBackup;

        unset($_GET['datastar']);
    });

    describe('isDatastarRequest()', function () {
        it('returns true when Datastar-Request header is set', function () {
            $_SERVER['HTTP_DATASTAR_REQUEST'] = 'true';

            expect(DatastarSender::isDatastarRequest())->toBeTrue();
        });

        it('is case insensitive', function () {
            $_SERVER['HTTP_DATASTAR_REQUEST'] = 'TRUE';

            expect(DatastarSender::isDatastarRequest())->toBeTrue();
        });

        it('returns false when header is not true', function () {
            $_SERVER['HTTP_DATASTAR_REQUEST'] = 'false';

            expect(DatastarSender::isDatastarRequest())->toBeFalse();
        });

        it('returns false when header is not set', function () {
            expect(DatastarSender::isDatastarRequest())->toBeFalse();
        });
    });

    describe('readSignals()', function () {
        it('reads signals from GET datastar parameter', function () {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_GET['datastar']          = json_encode(['toggle_item' => 7]);

            expect(DatastarSender::readSignals())->toBe(['toggle_item' => 7]);
        });

        it('reads signals from GET datastar parameter on DELETE requests', function () {
            $_SERVER['REQUEST_METHOD'] = 'DELETE';
            $_GET['datastar']          = json_encode(['toggle_item' => 15]);

            expect(DatastarSender::readSignals())->toBe(['toggle_item' => 15]);
        });

        it('returns empty array for empty or invalid datastar input', function () {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_GET['datastar']          = '';

            expect(DatastarSender::readSignals())->toBe([]);

            $_GET['datastar'] = 'not-json{';

            expect(DatastarSender::readSignals())->toBe([]);
        });
    });

    describe('sendHeaders()', function () {
        it('is idempotent', function () {
            $sse = mock(ServerSentEventGenerator::class);
            $sse->shouldReceive('sendHeaders')->once();

            $sender = new DatastarSender($sse);
            $sender->sendHeaders();
            $sender->sendHeaders();

            expect(true)->toBeTrue();
        });
    });

    describe('patchElements()', function () {
        it('passes selector and mode to the generator', function () {
            $sse = mock(ServerSentEventGenerator::class);
            $sse->shouldReceive('sendHeaders')->once();
            $sse->shouldReceive('patchElements')
                ->with('<tr data-id="42"></tr>', [
                    'selector' => 'tr[data-id="7"]',
                    'mode'     => ElementPatchMode::After,
                ]);

            $sender = new DatastarSender($sse);
            $sender->patchElements('<tr data-id="42"></tr>', 'tr[data-id="7"]', ElementPatchMode::After);

            expect(true)->toBeTrue();
        });

        it('omits empty selector and mode options', function () {
            $sse = mock(ServerSentEventGenerator::class);
            $sse->shouldReceive('sendHeaders')->once();
            $sse->shouldReceive('patchElements')->with('<tr data-id="42"></tr>', []);

            $sender = new DatastarSender($sse);
            $sender->patchElements('<tr data-id="42"></tr>', '', '');

            expect(true)->toBeTrue();
        });
    });

    describe('removeElements()', function () {
        it('passes the selector to the generator', function () {
            $sse = mock(ServerSentEventGenerator::class);
            $sse->shouldReceive('sendHeaders')->once();
            $sse->shouldReceive('removeElements')->with('tr[data-id="7"]');

            $sender = new DatastarSender($sse);
            $sender->removeElements('tr[data-id="7"]');

            expect(true)->toBeTrue();
        });
    });

    it('emits a valid patch-elements SSE stream', function () {
        $sender = new DatastarSender();
        $sender->sendHeaders();

        ob_start();
        ob_start();
        $sender->patchElements('<tr data-id="42"></tr>', 'tr[data-id="7"]', ElementPatchMode::After);
        $output = ob_get_clean();

        expect($output)
            ->toContain("event: datastar-patch-elements")
            ->toContain("data: selector tr[data-id=\"7\"]")
            ->toContain('data: mode after')
            ->toContain('data: elements <tr data-id="42"></tr>');
    });

    it('emits a remove-mode SSE stream on removeElements', function () {
        $sender = new DatastarSender();
        $sender->sendHeaders();

        ob_start();
        ob_start();
        $sender->removeElements('tr[data-id="7"]');
        $output = ob_get_clean();

        expect($output)
            ->toContain('event: datastar-patch-elements')
            ->toContain("data: selector tr[data-id=\"7\"]")
            ->toContain('data: mode remove');
    });
});
