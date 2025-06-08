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
        return response($count, 200);
    }


    public function countAnnouncedJobs()
    {
        $count = Jobpost::where('status', 'open')->count();
        return $count;
    }

    public function countDailyJobs()
    {
        $count = Jobpost::whereDate('created_at', Carbon::today())->count();
        return $count;
    }

    public function Posts()
    {
        $jobPosts = Jobpost::with('user')->get();
        return response()->json($jobPosts, 200);
    }



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
        if (!$user) {
            return response()->json(['message' => 'User not found!'], 404);
        }

        $user->is_approved = 1;
        $user->save();

        return response()->json(['message' => 'User accepted successfully!'], 200);
    }

    public function UnapprovedUsers()
    {

        $users = User::where('is_approved', 0)->get();

        return response()->json($users);
    }

    public function mostUsersPost()
    {
        $clients = User::where('user_type', 'jobowner')
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
