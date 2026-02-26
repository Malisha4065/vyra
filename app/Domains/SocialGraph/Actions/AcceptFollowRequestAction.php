<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\SocialGraph\Data\AcceptFollowRequestData;
use App\Domains\SocialGraph\Events\FollowRequestAccepted;
use App\Domains\SocialGraph\Events\UserFollowed;
use App\Domains\SocialGraph\Exceptions\FollowRequestNotFoundException;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;

class AcceptFollowRequestAction
{
    public function __construct(
        private readonly FollowRequestRepositoryInterface $followRequestRepository,
        private readonly FollowRepositoryInterface $followRepository,
    ) {}

    /**
     * @throws FollowRequestNotFoundException
     */
    public function __invoke(AcceptFollowRequestData $data): void
    {
        $request = $this->followRequestRepository->findById($data->request_id);

        if (! $request || ! $request->isPending()) {
            throw new FollowRequestNotFoundException();
        }

        // Create the follow relationship
        $this->followRepository->create($request->requester_id, $request->requestee_id);

        // Mark request as accepted
        $this->followRequestRepository->updateStatus($request, FollowRequest::STATUS_ACCEPTED);

        event(new FollowRequestAccepted($request));
        event(new UserFollowed($request->requester, $request->requestee));
    }
}
