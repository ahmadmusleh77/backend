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
                'job_id'      => 'nullable|exists:jobposts,job_id',
                'rating'      => 'required|integer|min:1|max:5',
            ]);

            // منع التقييم الذاتي
            if ($validated['reviewer_id'] === $validated['reviewee_id']) {
                return response()->json([
                    'error' => 'Validation Error',
                    'message' => 'لا يمكنك تقييم نفسك.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // التحقق من التقييم المكرر
            $existing = Review::where('reviewer_id', $validated['reviewer_id'])
                ->where('job_id', $validated['job_id'])
                ->first();

            if ($existing) {
                return response()->json([
                    'error' => 'Duplicate Review',
                    'message' => 'تم التقييم مسبقًا لهذا العمل.',
                ], Response::HTTP_CONFLICT);
            }

            $review = Review::create($validated);

            return response()->json(
                $review->load(['reviewer', 'reviewee', 'jobPost']),
                Response::HTTP_CREATED
            );

        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Validation Error',
                'message' => 'البيانات غير صالحة.',
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
                'message' => 'البيانات غير صالحة.',
                'errors'  => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function averageRating($userId)
{
    try {
        $average = Review::where('reviewee_id', $userId)->avg('rating');
        $count = Review::where('reviewee_id', $userId)->count();

        return response()->json([
            'user_id' => $userId,
            'average_rating' => round($average, 1),
            'total_reviews' => $count
        ], Response::HTTP_OK);
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
                'message' => 'تم حذف التقييم بنجاح.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
