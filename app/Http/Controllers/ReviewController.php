<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['reviewer', 'reviewee', 'jobPost'])->get();
        return response()->json($reviews, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'reviewer_id' => 'required|exists:users,user_id',
                'reviewee_id' => 'required|exists:users,user_id',
                'job_id'      => 'nullable|exists:jobposts,job_id', // Made nullable for testing
                'rating'      => 'required|integer|min:1|max:5',
            ]);

            $review = Review::create($validated);

            return response()->json(
                $review->load(['reviewer', 'reviewee', 'jobPost']),
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Validation Error',
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Server Error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $review = Review::with(['reviewer', 'reviewee', 'jobPost'])->findOrFail($id);
            return response()->json($review, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Review not found',
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $review = Review::findOrFail($id);

            $validated = $request->validate([
                'rating' => 'sometimes|integer|min:1|max:5',
            ]);

            $review->update($validated);

            return response()->json(
                $review->load(['reviewer', 'reviewee', 'jobPost']),
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Validation Error',
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();

            return response()->json([
                'message' => 'Review deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}