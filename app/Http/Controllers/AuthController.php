<?php

namespace App\Http\Controllers;

use App\Mail\SendOtp;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ResponseTrait;

    public function register(Request $request)
    {
        // validate request
        $validator = Validator::make($request->all(), [
            'name' => "required|string|max:255",
            'email' => "required|string|email|max:255|unique:users",
            'password' => "required|string|min:8|confirmed",
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        // save data
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // generate ans send otp
        $otp = $user->generateOtp();
        // Mail::to($user->email)->send(new SendOtp($otp));

        // return response
        return $this->returnSuccess('User registered successfully. OTP sent to email.');
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => 'required|string|max:255|exists:users,email'
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        // get user
        $user = User::where('email', $request->email)->first();

        // check if user has otp
        if($user->hasValidOtp()) {
            return $this->returnError(['otp' => __('words.otp_exists')]);
        }

        // generate ans send otp
        $otp = $user->generateOtp();
        Mail::to($user->email)->send(new SendOtp($otp));

        return $this->returnSuccess('OTP sent successfully.');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        // get user
        $user = User::where('email', $request->email)->first();

        if(!$user->hasValidOtp()) {
            return $this->returnError(['otp' => [__('words.otp_expired')]], 400);
        }

        if ($request->otp !== $user->otp) {
            return $this->returnError(['otp' => [__('words.otp_invalid')]], 401);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // return $this->returnSuccess('Email verified successfully.');
        return $this->returnData([
            'user' => $user,
            'token' => $token,
        ], 'Email verified successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->returnError(['password' => ['Invalid credentials.']], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->email_verified_at) {
            return $this->returnError(['password' => [__('words.email_not_verified')]], 401);
        }

        // Determine token expiration based on remember_me
        $expiration = $request->remember_me 
            ? now()->addDays(30) // Long expiration for "remember me"
            : null; // Session-based expiration (until browser closes)

        $token = $user->createToken(
            'auth_token',
            ['*'], // All abilities
            $expiration
        )->plainTextToken;

        return $this->returnData([
            'user' => $user,
            'token' => $token,
            'expires_at' => $expiration?->toDateTimeString(),
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        //$request->user()->currentAccessToken()->delete(); // Delete the current token
        $request->user()->tokens()->delete(); // Delete all tokens
        return $this->returnSuccess('Logged out successfully.');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->only('email'), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasValidOtp()) {
            return $this->returnError(['otp' => __('words.otp_exists')]);
        }

        $otp = $user->generateOtp();
        Mail::to($user->email)->send(new SendOtp($otp));

        return $this->returnSuccess('OTP sent successfully for password reset.');
    }

    public function verifyPasswordResetOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->hasValidOtp()) {
            return $this->returnError(['otp' => [__('words.otp_expired')]], 400);
        }

        if ($user->otp != $request->otp) {
            return $this->returnError(['otp' => [__('words.otp_invalid')]], 400);
        }

        // Generate a temporary token for password reset
        $token = $user->createToken('password_reset')->plainTextToken;

        return $this->returnData([
            'token' => $token,
        ], 'OTP verified successfully. Proceed to reset password.');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors());
        }

        $user = $request->user();
        $user->password = bcrypt($request->password);
        $user->save();

        // Revoke the password reset token
        $user->tokens()->where('name', 'password_reset')->delete();

        return $this->returnSuccess('Password reset successfully.');
    }

    public function getUser(Request $request)
    {
        return $this->returnData(['user' => $request->user()], 'User retrieved successfully.');
    }

    public function getAllUsers(Request $request)
    {
        $users = User::all();
        return $this->returnData($users, 'Users retrieved successfully.');
    }
}
