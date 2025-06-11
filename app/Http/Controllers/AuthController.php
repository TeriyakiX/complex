<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Регистрация успешна',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Неверные данные'], 401);
        }

        return response()->json([
            'message' => 'Успешный вход',
            'data'    => [
                'token' => $token,
            ],
        ]);
    }

    public function me()
    {
        return response()->json([
            'data' => [
                'user' => new UserResource(auth()->user()),
            ],
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Вы успешно вышли из системы']);
    }

    public function refresh()
    {
        return response()->json([
            'message' => 'Токен обновлён',
            'data'    => [
                'token' => auth()->refresh(),
            ],
        ]);
    }
}
