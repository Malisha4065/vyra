<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use App\Support\Ops\GetQueueHealthSnapshotAction;
use Inertia\Inertia;
use Inertia\Response;

class QueueHealthPageController extends Controller
{
    public function index(GetQueueHealthSnapshotAction $action): Response
    {
        return Inertia::render('Ops/Queues', [
            'snapshot' => $action(),
            'horizonUrl' => url(config('horizon.path')),
        ]);
    }
}
