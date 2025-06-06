<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jobpost;
class JobFilterController extends Controller
{
    public function filterJobs(Request $request)
    {
        $jobTitle = $request->input('jobTitle', null);
        $location = $request->input('location', null);
        $minPrice = $request->input('minPrice', null);
        $maxPrice = $request->input('maxPrice', null);

        $query = Jobpost::query();

        if ($jobTitle) {
            $query->where('title', 'LIKE', '%' . $jobTitle . '%');
        }

        if ($location) {
            $query->where('location', 'LIKE', '%' . $location . '%');
        }

        if ($minPrice !== null) {
            $query->where('budget', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('budget', '<=', $maxPrice);
        }

        $filteredJobs = $query->get();

        return response()->json($filteredJobs);
    }
}
