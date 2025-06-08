<?php

namespace App\Http\Controllers;

use App\Models\Jobpost;
use App\Models\Bid;
use App\Models\User;
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

            $imagePath = "https://www.ikea.com/us/en/images/products/vedhamn-drawer-front-oak__1023055_pe833063_s5.jpg?f=xl";
            // if ($request->hasFile('image')) {
            //     $imagePath = $request->file('image')->store('job_images', 'public');
            // }

            $job = Jobpost::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'budget' => $validated['budget'],
                'location' => $validated['location'],
                'deadline' => $validated['deadline'],
                'image' => $imagePath,
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

    public function updateJobPost(Request $request, $job_id)
    {
        $this->authorizeRequest();
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
        $job = Jobpost::with('bids.user')->find($id);

        if (!$job) {
            return response()->json(['message' => 'Job post not found'], 404);
        }

        if (!method_exists($job, 'bids')) {
            return response()->json(['message' => 'This job does not have a bids relationship defined'], 500);
        }

        $bids = $job->bids->map(function ($bid) {
            return [
                'id' => $bid->bids_id,
                'user_name' => $bid->user_name ?? 'Unknown',
                'price_estimate' => $bid->price_estimate,
                'timeline' => $bid->timeline,
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
        $this->authorizeRequest();
        try {
            $bid = Bid::findOrFail($id);
            $bid->status = 'accepted';
            $bid->save();

            //Notification
            $job=Jobpost::where('job_id',$bid->job_id)->first();
            $jobHolder=User::find($job->user_id);
            $craftsman =User::find($bid->artisan_id);
            $jobTitle=$job->title;
            $notificationController = app(NotificationController::class);
            $notificationController->notifyTenderApprovalToJobHolder($jobHolder,$craftsman,$jobTitle);
            $notificationController->notifyTenderApprovalToCraftsman($craftsman, $jobTitle);


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
