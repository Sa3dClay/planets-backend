<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\User as UserResource;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $req) {
        // dd('you hit register');

        $role = 1;
        if(isset($req->role)) {
            $role = $req->role;
        }

        $user = User::create([
            'name'      => $req->name,
            'email'     => $req->email,
            'password'  => bcrypt($req->password),
            'role'      => $role,
        ]);

        // create token method 1
        $token = JWTAuth::fromUser($user);

        // create token method 2
        // $credentials = $req->only(['email', 'password']);
        // if (!$token = auth()->attempt($credentials)) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        // return new UserResource($user);

        // response method 1
        return response()->json(compact('user', 'token'), 201);
        
        // response method 2
        // return response()->json([
        //     'user' => $user,
        //     'token' => $token,
        // ], 201);
    }

    public function login(UserLoginRequest $req) {
        $credentials = $req->only(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'user' => auth()->user(),
            'token' => $token,
        ], 201);
    }

    public function logout() {
        auth()->logout();
    }

    public function user(Request $req) {
        return new UserResource($req->user());
    }
}
