<?php

namespace App\Http\Controllers;
use App\Mail\PasswordVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    //
    public function sendVerificationCode(Request $request){
        $request->validate([

            'email'=>'required|email',
        ]);

        $user=User::where('email',$request->email)->first();

        if(!$user){
            return response()->json(['message'=>'user not found with this email'],404);
        }
       

        $verificationCode = rand(100000, 999999); 
        $user->update([
            'verification_code' => $verificationCode,
        ]);

        
        Mail::to($user->email)->send(new PasswordVerificationCode($verificationCode));



        return response()->json(['message' => 'Verification code sent to your email']);

    }



    public function resetPassword(Request $request){
        $request->validate([
            'email'=>'required|email',
            'verification_code'=>'required|size:6',
            'password'=>'required|confirmed|min:8',
        ]);
        $user=User::where('email',$request->email)->first();
        if(!$user){
            return response()->json(['message'=>'user not found with this email'],404);
        }

        if($user->verification_code != $request->verification_code){
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->update([
        'password'=>Hash::make($request->password),
        'verification_code'=>null,
    ]);
    return response()->json(['message' => 'Password has been successfully reset']);
    }


   
}
