<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        try {
            $type = $request->query('type');
            if ($type && in_array($type, ['admin', 'artisan', 'job_owner'])) {
                return response()->json(Setting::where('user_type', $type)->get());
            }
            return response()->json(Setting::all());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch settings'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $existing = Setting::where('user_id', Auth::id())->first();
            if ($existing) {
                return response()->json(['message' => 'Settings already exist for this user'], 400);
            }

            $validated = Validator::make($request->all(), [
                'user_type' => 'required|in:admin,artisan,job_owner',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:settings,email',
                'password' => 'required|string|min:8',
                'country' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'birthday' => 'nullable|date',
                'gender' => 'nullable|in:Male,Female',
                'languages' => 'nullable|array',
                'languages.*' => 'string',
                'skills' => 'nullable|array',
                'skills.*' => 'string',
                'experience' => 'nullable|string',
                'education' => 'nullable|string'
            ])->validate();

            $validated['user_id'] = Auth::id();
            $validated['password'] = bcrypt($validated['password']);

            $setting = Setting::create($validated);

            return response()->json($setting, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
    return response()->json([
        'error' => 'Failed to create setting',
        'message' => $e->getMessage(),   // هذه تطبع الخطأ الحقيقي
        'trace' => $e->getTraceAsString() // اختياري، لمزيد من التفاصيل
    ], 500);
}

    }

    public function show(string $id)
    {
        try {
            $setting = Setting::where('user_id', $id)->firstOrFail();
            return response()->json($setting);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Setting not found'], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);

        if ($setting->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:settings,email,' . $id,
            'password' => 'nullable|string|min:8',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'languages' => 'nullable|array',
            'languages.*' => 'string',
            'skills' => 'nullable|array',
            'skills.*' => 'string',
            'experience' => 'nullable|string',
            'education' => 'nullable|string'
        ];

        $validated = $request->validate($rules);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $setting->update($validated);

        return response()->json($setting);
    }

    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json(['message' => 'Setting deleted']);
    }
}
