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

    /**
     * @OA\Post(
     *     path="/api/offers",
     *     summary="submitOffer",
     *     tags={"Bid"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful submitOffer"
     *     )
     * )
     */
    //تقديم عرض جديد
    public function submitOffer(Request $request)
    {
        $validated = $request->validate([
              'user_name' => 'required|string|max:255',
              'amount' => 'required|numeric|min:0',
              'startDate' => 'required|date',
        ]);


        $bid =  Bid::create([
            'artisan_name' => $validated['user_name'],
            'price_estimate' =>$validated['amount'],
            'timeline' => $validated['startDate'],
            'status' => 'Pending'
]);

     //201: successfully
     return response()->json([
      'message' => 'Your request has been submitted successfully'],201);
      //'bid' => new BidResource($bid->load('jobPost'))],201);
    }

    //الحصول على عروض الحرفي
public function getArtisanBids()
{
    $bids = Bid::where('artisan_id',Auth::id())
        ->with(['jobPost.user'])
        ->get()
        ->map(function ($bid){
            return [
                'id' => $bid->bids_id,
                'jobTitle' => $bid->jobPost->title,
                'clientName' => $bid->jobPost->user->name,
                'price' => $bid->price_estimate,
                'date' => $bid->created_at->format('Y-m-d'),
                'status' => strtolower($bid->status)
            ];
        });
    return response()->json($bids,201);
}

//الغاء العرض المقدم من قبل الحرفي
    public function cancelBid($bidId)
    {
        $bid = Bid::where('artisan_id', Auth::id())
            ->where('bids_id', $bidId)
            ->where('status', 'Pending')
            ->firstOrFail();

        $bid->delete();

        return response()->json(['message' => 'The offer has been successfully cancelled']);
    }

    //كل العروض المقبول من قبل صاحب الوظيفة
    public function getAcceptedOffers()
    {
        $bids = Bid::where('artisan_id', Auth::id())
            ->where('status', 'Accepted')
            ->with(['jobPost.user'])
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->bids_id,
                    'jobTitle' => $bid->jobPost->title,
                    'clientName' => $bid->jobPost->user->name,
                    'price' => $bid->price_estimate,
                    'status' => str_replace(' ', '_', strtolower($bid->jobPost->status))
                ];
            });
        return response()->json($bids,201);

    }

    // تحديث حالة العرض
    public function updateOfferStatus(Request $request, $bidId)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,pending'
        ]);

        $bid = Bid::where('artisan_id', Auth::id())
            ->where('bids_id', $bidId)
            ->where('status', 'Accepted')
            ->firstOrFail();

        $statusMap = [
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'pending' => 'Pending'
        ];

        $bid->jobPost()->update([
            'status' => $statusMap[$validated['status']]
        ]);

        return response()->json(['message' => 'Status updated successfully']);
    }


    // عروض وظيفة معينة
    public function getJobBids($jobId)
    {
        $bids = Bid::where('job_id', $jobId)
            ->whereHas('jobPost', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['artisan'])
            ->get()
            ->map(function ($bid) {
                return [
                    'id' => $bid->bids_id,
                    'userName' => $bid->artisan->name,
                    'price' => $bid->price_estimate,
                    'startDate' => $bid->timeline,
                    'status' => $bid->status
                ];
            });

        return response()->json($bids,201);
    }

    // الرد على العرض اما القبول او الرفض
    public function respondToBid(Request $request, $bidId)
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        $bid = Bid::where('bids_id', $bidId)
            ->whereHas('jobPost', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->firstOrFail();

        $bid->update([
            'status' => $validated['action'] === 'accept' ? 'Accepted' : 'Rejected'
        ]);

        if ($validated['action'] === 'accept') {
            $bid->jobPost()->update(['status' => 'In Progress']);
        }

        return response()->json(['message' => 'Display status updated']);
    }
}
