<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    public function index()
    {
        return response()->json(Setting::all());
    }

 
    public function create()
    {
       
    }

   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id|unique:settings,user_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:settings,email',
            'password' => 'required|string|min:8',
           
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $setting = Setting::create($validated);

        return response()->json($setting, 201);
    }

    
    public function show(string $id)
    {
        $setting = Setting::findOrFail($id);
        return response()->json($setting);
    }

    
    public function edit(string $id)
    {
        
    }

    
    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:settings,email,' . $id . ',setting_id',
            'password' => 'sometimes|string|min:8',
          
        ]);

        if (isset($validated['password'])) {
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
