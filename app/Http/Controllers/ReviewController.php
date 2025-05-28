<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReviewController extends Controller
{
  
    public function index()
    {
        $reviews = Review::with(['reviewer', 'reviewee', 'jobPost'])->get();
        return response()->json($reviews, Response::HTTP_OK);
    }

   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'nullable|string',
            'reviewer_id' => 'required|exists:users,user_id',
            'reviewee_id' => 'required|exists:users,user_id|different:reviewer_id',
            'job_id'      => 'required|exists:jobposts,job_id',
        ]);

        $review = Review::create($validated);

        return response()->json($review->load(['reviewer', 'reviewee', 'jobPost']), Response::HTTP_CREATED);
    }

  
    public function show($id)
    {
        $review = Review::with(['reviewer', 'reviewee', 'jobPost'])
                         ->findOrFail($id);

        return response()->json($review, Response::HTTP_OK);
    }

  
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|nullable|string',
        
        ]);

        $review->update($validated);

        return response()->json($review->load(['reviewer', 'reviewee', 'jobPost']), Response::HTTP_OK);
    }

  
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Review deleted'], Response::HTTP_NO_CONTENT);
    }
}
