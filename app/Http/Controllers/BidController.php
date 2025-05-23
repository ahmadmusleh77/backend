<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Jobpost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\String\b;

class BidController extends Controller
{

    public function getPost ()
    {
        $jobPost=Jobpost::all();
        return response()->json($jobPost,200);

    }
    public function sendBids(Request $request)
    {

        $validated = $request->validate([
            'artisan_id' => 'required|integer|exists:users,user_id',
            'job_id' => 'required|integer|exists:jobposts,job_id',
            'user_name' => 'required|string|max:255',
            'price_estimate' => 'required|numeric|min:0',
            'timeline' => 'required|string|max:255',
            'status' => 'nullable|string|in:Pending,Accepted,Rejected',
        ]);


        if (!$request->has('status')) {
            $validated['status'] = 'Pending';
        }


        $bid = Bid::create($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Bid sent successfully.',
            'data' => $bid
        ]);
    }

    public function getSubmittedOffers()
    {
        $bids = Bid::with(['jobPost.user'])
        ->get()
            ->map(function ($bid) {
                return [
                    'job_title' => $bid->jobPost->title ?? 'N/A',
                    'client_name' => $bid->jobPost->user->name ?? 'N/A',
                    'price' => $bid->price_estimate,
                    'submission_date' => $bid->created_at->toDateString(),
                    'status' => $bid->status,
                ];
            });

        return response()->json([
            'status' => 200,
            'offers' => $bids
        ]);
    }





    public function cancelBid($id)
    {
        $bid = Bid::find($id);

        if (!$bid) {
            return response()->json([
                'status' => 404,
                'message' => 'Bid not found'
            ]);
        }


        $bid->status = 'Cancelled';
        $bid->save();

        return response()->json([
            'status' => 200,
            'message' => 'Bid cancelled successfully',
            'data' => $bid
        ]);
    }

    public function getAcceptedOffers()
    {
        $bids = Bid::with(['jobPost.user'])
            ->where('status', 'Accepted')
            ->get()
            ->map(function ($bid) {
                return [
                    'bid_id' => $bid->bids_id,
                    'job_title' => $bid->jobPost->title ?? 'N/A',
                    'client_name' => $bid->jobPost->user->name ?? 'N/A',
                    'price' => $bid->price_estimate,
                    'current_status' => $bid->jobPost->current_status ?? 'Pending',
                ];
            });

        return response()->json([
            'status' => 200,
            'offers' => $bids
        ]);
    }
    public function updateJobCurrentStatus(Request $request, $jobId)
    {
        $request->validate([
            'current_status' => 'required|string|in:Pending,In Progress,Completed'
        ]);

        $job = Jobpost::find($jobId);

        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Job not found'
            ]);
        }

        $job->current_status = $request->current_status;
        $job->save();

        return response()->json([
            'status' => 200,
            'message' => 'Job status updated successfully',
            'current_status' => $job->current_status
        ]);
    }





    //FILTER POST




}
