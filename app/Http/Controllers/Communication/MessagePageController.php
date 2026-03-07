<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessagePageController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Communication/Index', [
            'targetUserId' => $request->string('target_user_id')->toString() ?: null,
            'conversationId' => $request->string('conversation_id')->toString() ?: null,
        ]);
    }
}
