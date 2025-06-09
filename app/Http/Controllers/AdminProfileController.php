<?php

namespace App\Http\Controllers;

use App\Models\AdminProfile;
use Illuminate\Http\Request;

class AdminProfileController extends Controller
{
    public function show()
    {
        $profile = AdminProfile::where('user_id', auth()->id())->first();
        return response()->json($profile);
    }

    public function update(Request $request)
    {
        $profile = AdminProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            $request->all()
        );
        return response()->json($profile);
    }

    public function getSkills()
    {
        $profile = AdminProfile::where('user_id', auth()->id())->first();
        return response()->json($profile->skills);
    }

    public function addSkill(Request $request)
    {
        $profile = AdminProfile::where('user_id', auth()->id())->first();
        $skills = $profile->skills;
        $skills[] = $request->skill;
        $profile->skills = $skills;
        $profile->save();
        return response()->json($skills);
    }

    public function removeSkill(Request $request)
    {
        $profile = AdminProfile::where('user_id', auth()->id())->first();
        $skills = $profile->skills;
        $index = array_search($request->skill, $skills);
        if ($index !== false) {
            array_splice($skills, $index, 1);
        }
        $profile->skills = $skills;
        $profile->save();
        return response()->json($skills);
    }
}
