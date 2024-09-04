<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    //
    public function register(Request $request) {
        try {
           $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string', 'max:13'],
                'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()]
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went error',
                    'error' => $e->getMessage(),
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function login(Request $request) {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => 'required',
            ]);

            $credential = request(['email', 'password']);

            if(!Auth::attempt($credential)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',   
                ], 'Aunthentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if(!Hash::check($request->password, $user->password, [])){
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (\Exception $e) {
            //throw $th;
            return ResponseFormatter::error(
                [
                    'message' => 'Something went error',
                    'error' => $e->getMessage(),
                ],
                'Authentication Failed',
                500
            );
        }
    }

    public function fetch(Request $request) {
        return ResponseFormatter::success($request->user(), 'Data Profile User Berhasil Diambil');
    }

    public function updateProfile(Request $request) {
        try {
            $request->validate([
                "email" => ['email'],
            ]);
    
            $data = $request->all();
            $user = Auth::user();
    
            $user->update($data);
    
            return ResponseFormatter::success($user, 'Profile Update Success');
        
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                [
                    'message' => 'Something went error',
                    'error' => $e->getMessage(),
                ],
                'Authentication Failed',
                500
            );
        }
        
    }

    public function logout(Request $request) {
        $token = Auth::user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
}
