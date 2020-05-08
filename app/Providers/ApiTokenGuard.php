<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\TokenGuard;
use Illuminate\Http\Request;


class ApiTokenGuard
{
    public static function authenticate_b(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            return redirect()->intended('dashboard');
        }
    }

    public static function authenticate(Request $request)
    {
        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            $user = User::where('email', $request->input('email'))->first();

            return (new UserResource($user))
                    ->additional(['meta' => [
                        'access_token' => $user->api_token
                    ]]);
        }

        return response()->json(['message' => 'This action is unauthorized.'], 401);
    }

    public static function findUser(Request $request)
    {
        if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            $user = User::where('email', $request->input('email'))->first();

            return (new UserResource($user))
                    ->additional(['meta' => [
                        'access_token' => $user->api_token
                    ]]);
        }

        return response()->json(['message' => 'This action is unauthorized.'], 401);
    }

}

/*
public function login(){
       if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
           $user = Auth::user();
           $success['token'] =  $user->createToken('MyApp')->accessToken;
           return response()->json(['success' => $success], $this->successStatus);
       }
       else{
           return response()->json(['error'=>'Unauthorised'], 401);
       }
   }

*/