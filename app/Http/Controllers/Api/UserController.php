<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth:sanctum')->only(['authorizeUser', 'logout']);
    }

    public function register(Request $request) {
        $validator = Validator::make(
            $request->only([
                'first_name', 'last_name', 'email', 'password', 'password_confirmation',
            ]),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'password' => 'required|confirmed',
            ]
        );
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (null !== User::where($request->only('email'))->first()) {
            return response()->json(['email' => 'User with that email already exists'], 400);
        }

        $user = new User(array_merge(
            $request->only(['first_name', 'last_name', 'email',],),
            ['password' => Hash::make($request->input('password'))]
        ));
        $user->save();
        $token = $user->createToken('token')->plainTextToken;
     
        return response()->json(['data' => array_merge(
            $user->only(['first_name', 'last_name', 'email', 'created_at', 'updated_at',]), 
            compact('token')),
        ], 201);
    }

    function login(Request $request) {
        $validation = Validator::make(
            $request->only(['email', 'password',]),
            ['email' => 'required|email', 'password' => 'required',],
        );
        if($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'Invalid email and password combination',
            ], 400);
        }
        $user = User::where($request->only('email'))->firstOrFail();
        $token = $user->createToken('token')->plainTextToken;
        return ['data' => array_merge(
            $user->only(['first_name', 'last_name', 'email', 'created_at', 'updated_at',]), 
            compact('token')),
        ];
    }

    function authorizeUser(Request $request) {
        $user = User::where('email', $request->user()->email)->firstOrFail();
        $token = $user->createToken('token')->plainTextToken;

        return ['data' => array_merge(
            $user->only(['first_name', 'last_name', 'email', 'created_at', 'updated_at',]), 
            compact('token')),
        ];
    }

    function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return ['message' => 'Success'];
    }
}