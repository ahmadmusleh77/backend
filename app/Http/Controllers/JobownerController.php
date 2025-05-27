<?php

namespace App\Http\Controllers;

use App\Models\Jobpost;
use App\Models\Bid;
use Illuminate\Http\Request;

class JobownerController extends Controller
{
    public function newPost(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric|min:0',
            'location' => 'required|string',
            'deadline' => 'required|date',
            'image' => 'nullable|string|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('job_images', 'public');
        }

        $job = Jobpost::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'budget' => $validated['budget'],
            'location' => $validated['location'],
            'deadline' => $validated['deadline'],
            'image' => $imagePath,
            'user_id' => 1, // أو auth()->id()
        ]);

        return response()->json(['message' => 'Job posted successfully', 'job' => $job], 201);
    }

    public function getJobPosts()
    {
        $posts = Jobpost::all();

        return response()->json([
            'message' => 'Job posts retrieved successfully',
            'data' => $posts
        ], 200);
    }

    public function updateJobPost(Request $request, $job_id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'budget' => 'sometimes|required|numeric|min:0',
            'location' => 'sometimes|required|string',
            'deadline' => 'sometimes|required|date',
            'image' => 'nullable|string|max:2048'
        ]);

        $job = Jobpost::findOrFail($job_id);
        $job->update($validated);

        return response()->json([
            'message' => 'Job post updated successfully',
            'data' => $job
        ], 200);
    }

    public function deleteJobPost($id)
    {
        $job = Jobpost::find($id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        $job->delete();

        return response()->json(['message' => 'Job post deleted successfully'], 200);
    }

    public function getJobBids($id)
    {
        $job = Jobpost::find($id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        $bids = $job->bids()->with('user')->get()->map(function ($bid) {
            return [
                'id' => $bid->id,
                'username' => $bid->user->name ?? 'Unknown',
                'amount' => $bid->amount,
                'startDate' => $bid->start_date,
                'status' => $bid->status ?? 'pending'
            ];
        });

        return response()->json([
            'message' => 'Bids retrieved successfully',
            'data' => $bids
        ], 200);
    }

    public function acceptBid($id)
    {
        try {
            $bid = Bid::findOrFail($id);
            $bid->status = 'accepted';
            $bid->save();

            return response()->json([
                'message' => 'Bid accepted successfully',
                'data' => $bid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function rejectBid($id)
    {
        try {
            $bid = Bid::findOrFail($id);
            $bid->status = 'rejected';
            $bid->save();

            return response()->json([
                'message' => 'Bid rejected successfully',
                'data' => $bid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getJobStatuses()
    {
        $offers = [
            [
                'id' => 1,
                'jobTitle' => 'Plumbing Service',
                'clientName' => 'Ali Al-Farsi',
                'price' => 1500,
                'status' => 'completed',
                'rating' => 5,
            ],
            [
                'id' => 2,
                'jobTitle' => 'Electrical Wiring',
                'clientName' => 'Mona Saleh',
                'price' => 2500,
                'status' => 'pending',
                'rating' => 5,
            ],
            [
                'id' => 3,
                'jobTitle' => 'House Painting',
                'clientName' => 'Ahmed Al-Bassam',
                'price' => 3500,
                'status' => 'completed',
                'rating' => 5,
            ],
            [
                'id' => 4,
                'jobTitle' => 'Roof Repair',
                'clientName' => 'Fatima Al-Mutairi',
                'price' => 1800,
                'status' => 'pending',
                'rating' => 5,
            ],
        ];

        return response()->json([
            'message' => 'Job status retrieved successfully',
            'data' => $offers,
        ], 200);
    }



}
