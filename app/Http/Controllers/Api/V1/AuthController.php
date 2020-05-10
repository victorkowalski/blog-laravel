<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AccessToken;
use App\Models\User;
use App\Notifications\PasswordResetNotify;
use App\Notifications\VerifyMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function resgisterStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:1', //'required|min:11',
            'passwordConfirm' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $messages = [];

            foreach ($validator->errors()->all() as $message) {
                $messages[] = $message;
            }
            return response()->json(['status' => 'error', 'message' => $messages]);
        }

        $user = User::create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['status' => 'success', 'message' => 'user registered successfully']);
    }

    // Email Verification

    public function verify($token)
    {

        if ($token == null) { // Token Empty
            session()->flash('type', 'danger');
            session()->flash('message', 'Token is empty');
            return redirect()->route('login');
        }

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Token');
            return redirect()->route('login');
        }

        if ($user) {
            $user->update([
                'email_verification_token' => '',
                'email_verified_at' => Carbon::now(),
            ]);

            session()->flash('type', 'success');
            session()->flash('message', 'You are activated now');
            return redirect()->route('login');
        }
    }
    // Email Verification Again

    public function verifyAgain()
    {
        if (auth()->user()) {

            return redirect()->route('dashboard');
        }

        return view('frontend.auth.mail-verify-again');

    }

    // Resend Verification

    public function resendVerification(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = User::where('email', $request->email)->first();

        $token = str_random(20);

        if (!$user) {

            session()->flash('type', 'danger');
            session()->flash('message', 'You entered wrong credential.');
            return redirect()->route('verifyAgain');
        }
        $user->update([
            'email_verification_token' => $token,
        ]);

        $user->notify(new VerifyMail($user));

        session()->flash('type', 'success');
        session()->flash('message', 'Email Verification Token Send Again to Your Mail. Check your mail.');

        return redirect()->route('verifyAgain');
    }

    // Show login page
    /*
    public function loginShow(){
    if(auth()->user()){

    return redirect()->route('dashboard');
    }

    return view('frontend.auth.login');
    }
     */
    // Show login page

    public function loginStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        //with access token
        $user = $this->getUserWithAccessToken($request->email);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found']);
        }

        if (auth()->attempt($credentials)) {

/*
$verified = auth()->user()->email_verified_at;

if ($verified == null) {
session()->flash('type', 'warning');
session()->flash('message', 'Your account is not verified');
auth()->logout();
return redirect()->route('verifyAgain');

}*/
            $validAccessToken = $this->getValidAccessToken($user->id);

            if(!$validAccessToken )
                $this->createNewAccessToken($user->id);

            return response()->json(['status' => 'success', 'user' => $this->getUserWithAccessToken($user->email)]);

        }

        return response()->json(['status' => 'error', 'message' => 'Invalid Credentials']);
    }

    protected function getUserWithAccessToken($email)
    {
        return User::with('accessToken')->where(
            'email', $email)->first();
    }
    protected function createNewAccessToken($userId)
    {
        $this->clearExpiredAccessTokens($userId);

        $accessToken = new AccessToken();
        $accessToken->user_id = $userId;

        $temp = config('params.accessTokenExpireDays');
        //$caclulatedTime = time() + 3600 * 24 * config('params.accessTokenExpireDays');
        $expiredTime = Carbon::now()->addDays(config('params.accessTokenExpireDays'))->toDateTimeString();
        $accessToken->generateToken($expiredTime);

        return $accessToken->save() ? $accessToken : null;
    }

    protected function getValidAccessToken($userId)
    {
        $temp = AccessToken::where('user_id', $userId)
            ->where('expired_at', '>', Carbon::now())
            ->first();
        return $temp;
    }

    protected function clearExpiredAccessTokens($userId)
    {
        AccessToken::where('user_id', $userId)->delete();
    }

    // Password Reset Token

    public function passwordResetToken()
    {
        return view('frontend.auth.password-reset-token');
    }

    //  Password Reset Token Send

    public function passwordResetTokenSend(Request $request)
    {

        // Check User Exists

        $user = User::where('email', $request->email)->first();

        if (!$user) {

            session()->flash('type', 'danger');
            session()->flash('message', 'Email not found');
            return redirect()->route('passwordResetToken');
        }

        $token = str_random(20);

        $tokenExists = DB::table('password_resets')->where('email', $request->email)->first();

        if ($tokenExists) {

            session()->flash('type', 'danger');
            session()->flash('message', 'Token Already sent');
            return redirect()->route('passwordResetToken');

        }

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        $user->notify(new PasswordResetNotify($token));

        session()->flash('type', 'success');
        session()->flash('message', 'Password Reset Token sent , check your email.');
        return redirect()->route('passwordResetToken');

    }

    // Password Reset

    public function passwordReset($token)
    {

        if ($token === null) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Token');
            return redirect()->route('login');
        }

        $tokenExists = DB::table('password_resets')->where('token', $token)->first();

        if ($tokenExists === null) {
            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Token');
            return redirect()->route('login');
        }

        return view('frontend.auth.password-reset', ['token' => $token, 'email' => $tokenExists->email]);
    }

    // Password Reset Update

    public function passwordResetUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator);
        }

        $validToken = DB::table('password_resets')->where('token', $request->token)->first();

        if (!$validToken) {

            session()->flash('type', 'danger');
            session()->flash('message', 'Invalid Token');
            return redirect()->route('login');

        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_resets')->where('email', $request->email)->delete();

        session()->flash('type', 'success');
        session()->flash('message', 'Password Successfully Updated');
        return redirect()->route('login');

    }

    // Logout

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }
}
