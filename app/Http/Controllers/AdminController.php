<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Jobpost;
use App\Models\Bid;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{


    public function countCraftsmen()
    {
        $count = User::where('user_type', 'artisan')->count();
        return $count;
    }


    public function countAdmins()
    {
        $count = User::where('user_type', 'admin')->count();
        return $count;
    }

    public function countTotalUsers()
    {
        return User::count();
    }

    public function countCompletedJobs()
    {
        $count = Jobpost::where('status', 'completed')->count();

        //Notification
        $admin=User::where('user_type','admin')->first();
        if ($admin){
            app(NotificationController::class)->notifyAdminActivityReport($admin,['count completed jobs is'=>$count]);
        }

        return response($count, 200);
    }


    public function countAnnouncedJobs()
    {
        $count = Jobpost::where('status', 'open')->count();
        //Notification
        $admin=User::where('user_type','admin')->first();
        if ($admin){
            app(NotificationController::class)->notifyAdminActivityReport($admin,['count announced jobs is'=>$count]);
        }
        return $count;
    }

    public function countDailyJobs()
    {
        $count = Jobpost::whereDate('created_at', Carbon::today())->count();
        return $count;
    }

    public function Posts()
    {
        $jobPost = Jobpost::all();
        return response()->json($jobPost, 200);

    }
//    public function bidds()
//    {
//        $bids = Bid::with(['jobPost.user'])
//            ->get()
//            ->map(function ($bid) {
//                return [
//                    'client_name' => $bid->jobPost->user->name ?? 'N/A',
//                    'email' => $bid->jobPost->user->email ?? 'N/A',
//                    'bid' => $bid->price_estimate, // ← التعديل هنا
//                    'start_date' => $bid->jobPost->start_date ?? 'N/A', // تأكد أن عمود start_date موجود فعلًا
//                ];
//            });
//
//        return response()->json([
////            'status' => 200,
////            'offers' => $bids
//        ]);
//    }
//    public function top5Publishers() {
//        $topUsers = User::withCount('jobPosts')
//        ->orderBy('job_posts_count', 'desc')
//        ->get()
//            ->map(function ($user) {
//                return [
//                    'name' => $user->name,
//                    'email' => $user->email,
//                    'posts_count' => $user->job_posts_count,
//                ];
//            });
//
//        return response()->json([
//
//        ]);
//    }

    public function deletePost($id)
    {
        $post = Jobpost::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully'], 200);
    }

    public function deleteUser($id)
    {
        User::find($id)->delete();
    }



    public function Accept($id)
    {

        $user = User::find($id);
        $user->is_approved = 1;
        $user->save();

    }


//    public function ManageUses()
//    {
//        $artisans = User::with('joppost')
//            ->where('user_type', 'Artisan')
//            ->get();
//
//        $result = [];
//
//        foreach ($artisans as $user) {
//            $result[] = [
//                'name' => $user->name,
//                'Title' => $user->joppost->Title ?? 'N/A',
//                'email' => $user->email,
//
//            ];
//        }
//
//        return response()->json($result);
//    }
    public function mostUsersPost()
    {
        $clients = User::where('user_type', 'client')
        ->withCount('jobposts')
        ->orderBy('jobposts_count', 'desc')
            ->get();

        return response()->json($clients);
    }
    public function getJobpostBids($id)
    {
        $jobpost = Jobpost::with(['bids.artisan'])->findOrFail($id);

        return response()->json([
            'jobpost' => $jobpost->title,
            'bids' => $jobpost->bids->map(function ($bid) {
                return [
                    'user_name' => $bid->artisan->name ?? 'N/A',
                    'email' => $bid->artisan->email ?? 'N/A',
                    'bid_amount' => $bid->price_estimate ?? 'N/A',
                    'start_date' => $bid->created_at ? $bid->created_at->format('d/m/Y') : 'N/A',
                ];
            }),
        ]);
    }
}
