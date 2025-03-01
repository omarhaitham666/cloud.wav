<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;


class AuthController extends Controller
{
    //
    public function register(Request $request){
        $request->validate([
            'name'=>'required|string|max:100',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:8|confirmed'
        ]);

        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $refreshToken = Str::random(64);
        $refreshTokenExpiresAt = Carbon::now()->addWeeks(2);
        
        $user->refresh_token = $refreshToken;
        $user->refresh_token_expires_at = $refreshTokenExpiresAt;
        $user->save();
            

        return response()->json([

            'message'=>'user created successfully',
            'user'=>$user,
            'access_token' => $token,
          'token_type' => 'Bearer'
        ]);

    }

    public function login(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|string|min:8',
        ]);

        $user=User::where('email',$request->email)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            throw ValidationException::withMessages([
                'email'=>['this email isnt correct '],
            ]);
        }

        $token=$user->createToken('auth_token')->plainTextToken;

        $refreshToken=Str::random(64);
        $refreshTokenExpiresAt=Carbon::now()->addYear();
        $user->refresh_token = $refreshToken;
        $user->refresh_token_expires_at = $refreshTokenExpiresAt;
        $user->save();

        return response()->json([
            'message' => 'Logged in successfully',
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_in' => 60 * 15, 
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request){
        $user = $request->user(); 
        $user->tokens()->delete(); 
        $user->refresh_token=null;
        $user->refresh_token_expires_at=null;
        $user->save();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}