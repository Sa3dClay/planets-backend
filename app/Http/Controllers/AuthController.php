<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\User as UserResource;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $req) {
        $role = 1;
        if(isset($req->role)) {
            $role = $req->role;
        }

        $user = User::create([
            'name'      =>  $req->name,
            'email'     =>  $req->email,
            'password'  =>  bcrypt($req->password),
            'role'      =>  $role,
            'planet'    =>  $req->planet,
        ]);
        $token = JWTAuth::fromUser($user);

        return (new UserResource($user))
            ->additional(['meta' => [
                'token' => $token,
            ]]);
    }

    public function login(UserLoginRequest $req) {
        $credentials = $req->only(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return (new UserResource(auth()->user()))
            ->additional(['meta' => [
                'token' => $token,
            ]]);
    }

    public function logout() {
        auth()->logout();
    }

    public function user(Request $req) {
        return new UserResource($req->user());
    }
}
