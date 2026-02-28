<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class MessagePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Communication/Index');
    }
}
