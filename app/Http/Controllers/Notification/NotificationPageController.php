<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Notification/Index');
    }
}
