<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // هذا هو الصحيح
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class AuthApiController extends Controller

    /**
     * Register a new user.
     */


{
    public function signUp(Request $request)
    {
        try {
            // Validate user input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'user_type' => 'required|string',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role_id' => 'required|exists:roles,role_id',
            ]);

            // Generate OTP
            $otp = rand(100000, 999999);
            $otpExpiresAt = now()->addMinutes(15);

            // Create new user with OTP
            $user = User::create([
                'name' => $validated['name'],
                'user_type' => $validated['user_type'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['role_id'],
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
            ]);

            // Send OTP email
            Mail::to($user->email)->send(new SendOtpMail($otp));

            return response()->json([
                'message' => 'User registered successfully. OTP sent to email.',
                'user' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Login a user.
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'يجب تفعيل الايميل الخاص بك اولا قبل الدخول'
            ], 403);
        }
        if (Auth::attempt($credentials)) {

            $token = $user->createToken('authToken')->plainTextToken;

            // بدون session regenerate لو في api.php
            return response()->json([
                'message' => 'Login successful',
                'user' => Auth::user(),
                'token'=> $token
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid email or password'
        ], 401);
    }


    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate email format
        $request->validate([
            'email' => 'required|email'
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Generate new OTP and expiry
        $otp = rand(100000, 999999);
        $otpExpiresAt = now()->addMinutes(15);

        // Save OTP and expiry in user record
        $user->otp = $otp;
        $user->otp_expires_at = $otpExpiresAt;
        $user->save();

        // Send OTP email
        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json([
            'message' => 'تم إرسال رمز التفعيل على الإيميل',
        ]);
    }

    /**
     * Verify OTP for user registration.
     */
    public function verifyOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:6',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            if ($user->otp !== $request->otp) {
                return response()->json(['message' => 'Invalid OTP.'], 400);
            }
            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['message' => 'OTP expired.'], 400);
            }
            // Mark email as verified
            $user->email_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
            return response()->json(['message' => 'OTP verified successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while verifying OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Send password reset link to user's email with detailed error messages.
     */
    public function sendResetLinkEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'البريد الإلكتروني غير مسجل لدينا.'
            ], 404);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'تعذر إرسال رابط إعادة تعيين كلمة المرور.'
            ], 400);
        }
    }
}




