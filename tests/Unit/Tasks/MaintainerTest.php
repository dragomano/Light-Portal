<?php

declare(strict_types=1);

use Bugo\Compat\Tasks\BackgroundTask;
use LightPortal\Database\Operations\PortalDelete;
use LightPortal\Database\Operations\PortalInsert;
use LightPortal\Database\Operations\PortalSelect;
use LightPortal\Database\Operations\PortalUpdate;
use LightPortal\Database\PortalAdapterInterface;
use LightPortal\Database\PortalResultInterface;
use LightPortal\Database\PortalSqlInterface;
use LightPortal\Repositories\CommentRepositoryInterface;
use LightPortal\Tasks\Maintainer;
use Tests\AppMockRegistry;

arch()
    ->expect(Maintainer::class)
    ->toExtend(BackgroundTask::class);

describe('Maintainer::execute', function () {
    beforeEach(function () {
        $this->sql = mock(PortalSqlInterface::class);
        $this->commentRepository = mock(CommentRepositoryInterface::class);
        AppMockRegistry::set(PortalSqlInterface::class, $this->sql);
        AppMockRegistry::set(CommentRepositoryInterface::class, $this->commentRepository);

        $this->makeResult = function (array $rows): PortalResultInterface {
            $iterator = new ArrayIterator($rows);
            $result = mock(PortalResultInterface::class);

            $result->shouldReceive('current')->andReturnUsing(function () use ($iterator) {
                return $iterator->current();
            });
            $result->shouldReceive('valid')->andReturnUsing(function () use ($iterator) {
                return $iterator->valid();
            });
            $result->shouldReceive('next')->andReturnUsing(function () use ($iterator) {
                $iterator->next();
            });
            $result->shouldReceive('key')->andReturnUsing(function () use ($iterator) {
                return $iterator->key();
            });
            $result->shouldReceive('rewind')->andReturnUsing(function () use ($iterator) {
                $iterator->rewind();
            });

            return $result;
        };
    });

    afterEach(function () {
        AppMockRegistry::clear();
    });

    it('runs maintenance steps and schedules next task', function () {
        $delete = mock(PortalDelete::class);
        $delete->shouldReceive('where')->andReturnSelf();

        $select1 = mock(PortalSelect::class);
        $select1->shouldReceive('from')->andReturnSelf();
        $select1->shouldReceive('columns')->andReturnSelf();
        $select1->shouldReceive('join')->andReturnSelf();
        $select1->shouldReceive('where')->andReturnSelf();

        $select2 = mock(PortalSelect::class);
        $select2->shouldReceive('from')->andReturnSelf();
        $select2->shouldReceive('columns')->andReturnSelf();
        $select2->shouldReceive('join')->andReturnSelf();
        $select2->shouldReceive('group')->andReturnSelf();
        $select2->shouldReceive('order')->andReturnSelf();

        $select3 = mock(PortalSelect::class);
        $select3->shouldReceive('from')->andReturnSelf();
        $select3->shouldReceive('columns')->andReturnSelf();
        $select3->shouldReceive('join')->andReturnSelf();
        $select3->shouldReceive('group')->andReturnSelf();
        $select3->shouldReceive('order')->andReturnSelf();

        $update = mock(PortalUpdate::class);
        $update->shouldReceive('set')->andReturnSelf();
        $update->shouldReceive('where')->andReturnSelf();

        $insert = mock(PortalInsert::class);
        $insert->shouldReceive('values')->andReturnSelf();

        $adapter = mock(PortalAdapterInterface::class);
        $adapter->shouldReceive('query')->times(9)->andReturnNull();

        $this->sql->shouldReceive('delete')->with('lp_params')->andReturn($delete);
        $this->sql->shouldReceive('select')->andReturn($select1, $select2, $select3);
        $this->sql->shouldReceive('update')->with('lp_pages')->andReturn($update);
        $this->sql->shouldReceive('insert')->with('background_tasks')->andReturn($insert);
        $this->sql->shouldReceive('getPrefix')->andReturn('smf_');
        $this->sql->shouldReceive('getAdapter')->andReturn($adapter);

        $this->sql->shouldReceive('execute')->with($delete)->once()->andReturn(($this->makeResult)([]));
        $this->sql->shouldReceive('execute')->with($select1)->once()->andReturn(($this->makeResult)([
            ['id' => 1],
            ['id' => 2],
        ]));
        $this->sql->shouldReceive('execute')->with($select2)->once()->andReturn(($this->makeResult)([
            ['page_id' => 1, 'amount' => 3],
        ]));
        $this->sql->shouldReceive('execute')->with($select3)->once()->andReturn(($this->makeResult)([
            ['page_id' => 1, 'last_comment_id' => 9],
        ]));
        $this->sql->shouldReceive('execute')->with($update)->twice()->andReturn(($this->makeResult)([]));
        $this->sql->shouldReceive('execute')->with($insert)->once()->andReturn(($this->makeResult)([]));

        $this->commentRepository->shouldReceive('remove')->once()->with([1, 2])->andReturnNull();

        $task = new Maintainer([]);

        expect($task->execute())->toBeTrue();
    });
});
