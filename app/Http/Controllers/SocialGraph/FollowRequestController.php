<?php

namespace App\Http\Controllers\SocialGraph;

use App\Domains\SocialGraph\Actions\AcceptFollowRequestAction;
use App\Domains\SocialGraph\Actions\RejectFollowRequestAction;
use App\Domains\SocialGraph\Exceptions\FollowRequestNotFoundException;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FollowRequestController extends Controller
{
    public function __construct(
        private readonly FollowRequestRepositoryInterface $followRequestRepository,
    ) {}

    /**
     * List pending follow requests for the authenticated user.
     */
    public function index(): Response
    {
        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $requests = $this->followRequestRepository->getPendingForUser($user->id);

        return Inertia::render('SocialGraph/FollowRequests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Accept a follow request.
     */
    public function accept(
        string $id,
        AcceptFollowRequestAction $action,
    ): RedirectResponse {
        try {
            $action($id);

            return back()->with('success', 'Follow request accepted.');
        } catch (FollowRequestNotFoundException $e) {
            return back()->withErrors(['request' => $e->getMessage()]);
        }
    }

    /**
     * Reject a follow request.
     */
    public function reject(
        string $id,
        RejectFollowRequestAction $action,
    ): RedirectResponse {
        try {
            $action($id);

            return back()->with('success', 'Follow request rejected.');
        } catch (FollowRequestNotFoundException $e) {
            return back()->withErrors(['request' => $e->getMessage()]);
        }
    }
}
