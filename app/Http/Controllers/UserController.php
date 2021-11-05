<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\User as UserResource;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json(
            [
                'status'    => 'success',
                'users'     => $users->toArray()
            ], 200);
    }

    public function show($id)
    {
        $user = User::find($id);
        
        return response()->json(
            [
                'status'    => 'success',
                'user'      => $user->toArray()
            ], 200);
    }

    public function update(UserUpdateRequest $req, $id)
    {
        $user = User::find($id);

        $user->name = $req->name;
        $user->email = $req->email;
        $user->planet = $req->planet;

        $user->save();

        return new UserResource($user);
    }
}
