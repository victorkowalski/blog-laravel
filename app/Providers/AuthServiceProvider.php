<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Providers\ApiTokenGuard;
use App\Models\User;
use App\Http\Controllers\AuthenticateController;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
/*
        Auth::viaRequest('api-token', function ($request) {
            $token = $request->bearerToken();
            //return User::where('remember_token', $token)->first();        
            if ($token && strlen($token) > 0) {
                try {
                    $user = User::where('remember_token', $token)->first();
                    if (!$user) throw new \Exception;
                } catch (\Exception $e) {
                    return null;
                }
        
                return $user;
            }
        
            return null;
        });
        */
/*
        Auth::viaRequest('custom-token', function ($request) {
            $token = $request->bearerToken();
            $user = User::where('remember_token', $token)->first();
            if(Auth::login($user)){
                return $user;
            }

            return null;            
        });
*/
        //
  /*
        Auth::viaRequest('api-token', function($request){
            return AuthenticateController::authenticate($request);            
        });
*/

        /*
        Auth::viaRequest('custom-jwt', function ($request) {
            $token = $request->bearerToken();
            $secret = config('auth.auth_jwt_secret_key');
        
            if ($token && strlen($token) > 0) {
                try {
                    $user = JWT::decode($token, $secret, array("HS256"));
                    if (!$user) throw new \Exception;
                } catch (\Exception $e) {
                    return null;
                }
        
                return DB::table("users")->where("id", $user->user->id)->first();
            }
        
            return null;
        });*/
    }
}
