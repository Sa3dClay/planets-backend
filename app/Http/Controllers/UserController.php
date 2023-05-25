<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\FriendResource;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\User as UserResource;
use Kutia\Larafirebase\Facades\Larafirebase;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json(
            [
                'status'    => 'success',
                'users'     => $users->toArray()
            ],
            200
        );
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json(
            [
                'status'    => 'success',
                'user'      => $user->toArray()
            ],
            200
        );
    }

    public function update(UserUpdateRequest $req, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $req->name;
        $user->email = $req->email;
        $user->planet = $req->planet;
        $user->save();

        return new UserResource($user);
    }

    public function sendFriendRequest(User $user)
    {
        auth()->user()->befriend($user);

        if ($user->fcm_token) $this->sendFriendRequestNotification($user->fcm_token);

        return response([
            'message' => 'Your request has been sent to ' . $user->name
        ]);
    }

    public function sendFriendRequestNotification($token)
    {
        Larafirebase::withTitle(auth()->user()->name . ' أرسل لك طلب صداقة')
            ->withBody(' اتقل عليه شوية xD')
            ->withSound('default')
            ->withPriority('high')
            ->withClickAction(env('FRONT_END_URL') . '/friends')
            ->sendNotification($token);
    }

    public function acceptFriendRequest(User $user)
    {
        auth()->user()->acceptFriendRequest($user);

        if ($user->fcm_token) $this->sendAcceptFriendRequestNotification($user->fcm_token);

        return response([
            'message' => 'You have accepted ' . $user->name . ' friendship request :)'
        ]);
    }

    public function sendAcceptFriendRequestNotification($token)
    {
        Larafirebase::withTitle(auth()->user()->name . ' أضافك الى الأصدقاء')
            ->withBody('يمكنك محادثته الآن!')
            ->withSound('default')
            ->withPriority('high')
            ->withClickAction(env('FRONT_END_URL') . '/chat')
            ->sendNotification($token);
    }

    public function denyFriendRequest(User $user)
    {
        auth()->user()->denyFriendRequest($user);

        return response([
            'message' => 'You have denied ' . $user->name . ' friendship request :)'
        ]);
    }

    public function removeFriend(User $user)
    {
        auth()->user()->unfriend($user);

        return response([
            'message' => 'You have removed ' . $user->name . ' from your friends list :)'
        ]);
    }

    public function blockFriend(User $user)
    {
        auth()->user()->blockFriend($user);

        return response([
            'message' => 'You have blocked ' . $user->name . ', he can not bother you anymore 😉'
        ]);
    }

    public function unblockFriend(User $user)
    {
        auth()->user()->unblockFriend($user);

        return response([
            'message' => 'You have unblocked ' . $user->name . ', he can see you in his friends list now!'
        ]);
    }

    public function getFriends()
    {
        $friends = auth()->user()->getFriends();

        return FriendResource::collection($friends);
    }

    public function getFriendsRequests()
    {
        $friendsRequests = auth()->user()->getFriendRequests();
        $usersRequestedFriendship = User::whereIn('id', $friendsRequests->pluck('sender_id'))->get();

        return UserResource::collection($usersRequestedFriendship);
    }

    public function getPendingFriendsRequests()
    {
        $pendingRequests = auth()->user()->getPendingFriendships();
        $pendingUsersInvited = User::notSelf()->whereIn('id', $pendingRequests->pluck('recipient_id'))->get();

        return UserResource::collection($pendingUsersInvited);
    }

    public function getAvailableFriends()
    {
        $friendships = auth()->user()->getAllFriendships();
        $users = User::notAdmin()
            ->notSelf()
            ->whereNotIn('id', $friendships->pluck('sender_id'))
            ->whereNotIn('id', $friendships->pluck('recipient_id'))
            ->get();

        return UserResource::collection($users);
    }

    public function getBlockedFriends()
    {
        $blockedFriendships = auth()->user()->getBlockedFriendships();
        $users = User::notSelf()->whereIn('id', $blockedFriendships->pluck('recipient_id'))->get();

        return UserResource::collection($users);
    }

    public function getDeniedFriends()
    {
        $deniedFriendships = auth()->user()->getDeniedFriendships();
        $users = User::notSelf()->whereIn('id', $deniedFriendships->pluck('sender_id'))->get();

        return UserResource::collection($users);
    }

    public function setFcmToken(Request $request)
    {
        try {
            auth()->user()->update([
                'fcm_token' => $request->token
            ]);

            return response([
                'success' => true,
                'message' => 'Done!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 500);
        }
    }

    public function deleteFcmToken()
    {
        auth()->user()->update(['fcm_token' => null]);
    }
}
