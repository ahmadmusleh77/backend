<?php

namespace App\Http\Controllers;


use App\Models\Bid;
use App\Models\Jobpost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BidController extends Controller
{

    public function getPost()
    {
        $jobPost = Jobpost::with('user')->get();
        return response()->json($jobPost, 200);
    }

    public function sendBids(Request $request)
    {
        $artisanId = Auth::id();

        $validated = $request->validate([
            'job_id'        => 'required|integer|exists:jobposts,job_id',
            'user_name'     => 'required|string|max:255',
            'price_estimate'=> 'required|numeric|min:0',
            'timeline'      => 'required|string|max:255',
            'status'        => 'nullable|string|in:Pending,Accepted,Rejected',
        ]);

        $validated['artisan_id'] = $artisanId;

        if (!isset($validated['status'])) {
            $validated['status'] = 'Pending';
        }

        $bid = Bid::create($validated);

        //Notification
        $job=Jobpost::where('job_id',$validated['job_id'])->first();
        \Log::info('🔍 job user_id: ' . $job->user_id);
        \Log::info('🔍 user_type: ' . User::find($job->user_id)?->user_type);

        if($job){
            $jobHolder=User::find($job->user_id);
            $craftsman =User::find(Auth::id());
            $jobTitle=$job->title;

            if (!$jobHolder) {
                \Log::error(' لم يتم العثور على jobHolder');
            }
            if (!$craftsman) {
                \Log::error(' لم يتم العثور على craftsman');
            }
            if ($job && $jobHolder && $craftsman) {
                app(NotificationController::class)->notifyTenderRequest($jobHolder, $craftsman, $jobTitle);
                \Log::info(' سيتم استدعاء notifyTenderRequest');
            }else {
                \Log::warning(' أحد الكائنات مفقودة: ', [
                    'jobHolder' => $jobHolder,
                    'craftsman' => $craftsman,
                    'job' => $job
                ]);
            }
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Bid sent successfully.',
            'data'    => $bid
        ]);
    }


    public function getSubmittedOffers()
    {
        $artisanId = Auth::id();

        $bids = Bid::with(['jobPost.user'])
            ->where('artisan_id', $artisanId)
            ->get()
            ->map(function ($bid) {
                return [
                    'bid_id'          => $bid->bids_id,
                    'job_title'       => $bid->jobPost->title ?? 'N/A',
                    'client_name'     => $bid->jobPost->user->name ?? 'N/A',
                    'price'           => $bid->price_estimate,
                    'submission_date' => $bid->created_at->toDateString(),
                    'status'          => $bid->status,
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
        $bid->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Bid deleted successfully'
        ]);
    }


    public function getAcceptedOffers()
    {
        $artisanId = Auth::id();

        $bids = Bid::with(['jobPost.user'])
            ->where('status', 'Accepted')
            ->where('artisan_id', $artisanId)
            ->get()
            ->map(function ($bid) {
                return [
                    'bid_id'         => $bid->bids_id,
                    'job_id'         => $bid->job_id,
                    'job_title'      => $bid->jobPost->title ?? 'N/A',
                    'client_name'    => $bid->jobPost->user->name ?? 'N/A',
                    'price'          => $bid->price_estimate,
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
        $status = $request->input('current_status');

        $validated = validator(['current_status' => $status], [
            'current_status' => 'required|string|in:Pending,In Progress,Completed'
        ])->validate();

        $job = Jobpost::find($jobId);

        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Job not found'
            ]);
        }

        $job->current_status = $validated['current_status'];
        $job->save();

        return response()->json([
            'status' => 200,
            'message' => 'Job status updated successfully',
            'current_status' => $job->current_status
        ]);
    }
}
