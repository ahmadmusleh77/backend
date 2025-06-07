<?php

namespace App\Http\Controllers;

use App\Models\Jobpost;
use App\Models\Bid;
use Illuminate\Http\Request;

class JobownerController extends Controller
{
    private function authorizeRequest()
    {
        if (!auth()->check()) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
    }

    public function newPost(Request $request)
    {
        $this->authorizeRequest();
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'budget' => 'required|numeric|min:0',
                'location' => 'required|string',
                'deadline' => 'required|date' ,
                'image' => 'nullable|file|image|max:2048'
            ]);

            if ($request->hasFile('image')) {
                $filename = uniqid() . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = $request->file('image')->storeAs('job_images', $filename, 'public');
            }

            $job = Jobpost::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'budget' => $validated['budget'],
                'location' => $validated['location'],
                'deadline' => $validated['deadline'],
                'image' => $imagePath ? asset('storage/' . $imagePath) : null,
                'user_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Job posted successfully', 'job' => $job], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while posting the job.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateJobPost(Request $request, $job_id)
    {
        $this->authorizeRequest();
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|required|string',
            'budget' => 'sometimes|required|numeric|min:0',
            'location' => 'sometimes|required|string',
            'deadline' => 'sometimes|required|date',
            'image' => 'nullable|file|image|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $filename = uniqid() . '_' . time() . '.' . $request->file('image')->getClientOriginalExtension();
            $imagePath = $request->file('image')->storeAs('job_images', $filename, 'public');
            $validated['image'] = asset('storage/' . $imagePath);
        }

        $job = Jobpost::findOrFail($job_id);
        $job->update($validated);

        return response()->json([
            'message' => 'Job post updated successfully',
            'data' => $job
        ], 200);
    }


    public function getJobPosts()
    {
        $this->authorizeRequest();
        $posts = Jobpost::all();

        return response()->json([
            'message' => 'Job posts retrieved successfully',
            'data' => $posts
        ], 200);
    }

    public function getJobPostById($job_id)
    {
        $this->authorizeRequest();
        $job = Jobpost::find($job_id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        return response()->json([
            'message' => 'Job post retrieved successfully',
            'data' => $job
        ], 200);
    }



    public function deleteJobPost($id)
    {
        $this->authorizeRequest();
        $job = Jobpost::find($id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        $job->delete();

        return response()->json(['message' => 'Job post deleted successfully'], 200);
    }

    public function getJobBids($id)
    {
        $this->authorizeRequest();
        $job = Jobpost::with('bids.artisan')->find($id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        if (!method_exists($job, 'bids')) {
            return response()->json(['message' => 'This job does not have a bids relationship defined'], 500);
        }

        // Filter bids with status "Pending"
        $bids = $job->bids->where('status', 'Pending')->map(function ($bid) {
            return [
                'id' => $bid->bids_id, // or $bid->id if that's what you prefer
                'user_name' => $bid->user_name ?? 'Unknown',
                'price_estimate' => $bid->price_estimate,
                'timeline' => $bid->timeline,
                'status' => $bid->status, // This should be "Pending" for all
            ];
        });

        return response()->json([
            'message' => 'Pending bids retrieved successfully',
            'data' => $bids
        ], 200);
    }

    public function acceptBid($id)
    {
        $this->authorizeRequest();
        try {
            $bid = Bid::findOrFail($id);
            $bid->status = 'Accepted';
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
        $this->authorizeRequest();
        try {
            $bid = Bid::findOrFail($id);
            $bid->status = 'Rejected';
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
        $this->authorizeRequest();

        $userId = auth()->id();

        // Get all jobposts owned by the authenticated user
        $jobPostIds = Jobpost::where('user_id', $userId)->pluck('job_id');

        // Get all accepted bids for those jobposts
        $acceptedBids = Bid::whereIn('job_id', $jobPostIds)
            ->where('status', 'Accepted') // or strtolower() if needed
            ->with(['jobpost', 'artisan']) // make sure 'artisan' and 'jobpost' relations exist
            ->get();

        $formattedOffers = $acceptedBids->map(function ($bid) {
            return [
                'id' => $bid->bids_id,
                'jobTitle' => $bid->jobpost->title ?? 'Unknown Job',
                'clientName' => $bid->artisan->name ?? 'Unknown Artisan',
                'price' => $bid->price_estimate,
                'status' => $bid->jobpost->current_status,
                'rating' => 5, // Static rating for now
            ];
        });

        return response()->json([
            'message' => 'Accepted offers retrieved successfully',
            'data' => $formattedOffers
        ], 200);
    }
}
