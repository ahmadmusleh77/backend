<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\JobFilterController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SwaggerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\JobownerController;

// Testing user retrieval (optional)
Route::get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes
Route::post('/signup', [AuthApiController::class, 'signUp']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/reset-password', [AuthApiController::class, 'sendResetLinkEmail']);
Route::post('/otp-verification', [AuthApiController::class, 'verifyOtp']);
Route::post('/send-otp', [AuthApiController::class, 'sendOtp']);

// Bid routes (no auth for now)
Route::get('artisan/bids', [BidController::class, 'getPost']);
Route::post('artisan/bids', [BidController::class, 'sendBids']);
Route::get('artsisan/submitted-offers', [BidController::class, 'getSubmittedOffers']);
Route::delete('/artisan/{id}', [BidController::class, 'cancelBid']);
Route::get('/bids/accepted', [BidController::class, 'getAcceptedBids']);
Route::put('/bids/update-status/{id}', [BidController::class, 'updateBidStatus']);
Route::get('/offers/accepted', [BidController::class, 'getAcceptedOffers']);
Route::put('/jobposts/status/{jobId}', [BidController::class, 'updateJobCurrentStatus']);

// Message routes
Route::get('/chat/contacts/{userId}', [MessageController::class, 'getChatContacts']);
Route::post('/chat/send', [MessageController::class, 'sendMessage']);

// Job filter
Route::get('/jobposts/filter', [JobFilterController::class, 'filterJobs']);

// Swagger welcome
Route::get('/welcome', [SwaggerController::class, 'welcome']);

// Admin routes
Route::get('/artisans/count', [AdminController::class, 'countCraftsmen']);
Route::get('/users/count', [AdminController::class, 'countTotalUsers']);
Route::get('/Admins/count', [AdminController::class, 'countAdmins']);
Route::get('/CompletedJobs', [AdminController::class, 'countCompletedJobs']);
Route::get('/countAnnouncedJobs', [AdminController::class, 'countAnnouncedJobs']);
Route::get('/countDailyJobs', [AdminController::class, 'countDailyJobs']);
Route::get('/most/users', [AdminController::class, 'mostUsersPost']);
Route::get('/Posts', [AdminController::class, 'Posts']);
Route::delete('/deletePost/{id}', [AdminController::class, 'deletePost']);
Route::get('/jobpost/{id}/bids', [AdminController::class, 'getJobpostBids']);
Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
Route::put('/Accept/{id}', [AdminController::class, 'Accept']);

// Password reset
Route::get('password/reset/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('password/reset', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:6',
    ]);

    $status = \Illuminate\Support\Facades\Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = \Illuminate\Support\Facades\Hash::make($password);
            $user->save();
        }
    );

    return $status == \Illuminate\Support\Facades\Password::PASSWORD_RESET
        ? redirect('/')->with('status', 'تم إعادة تعيين كلمة المرور بنجاح!')
        : back()->withErrors(['email' => [__($status)]]);
})->name('password.update');

// Job owner routes
Route::post('/newpost', [JobownerController::class, 'newPost']);
Route::get('/newpost', [JobownerController::class, 'getJobPosts']);
Route::put('/updatepost/{id}', [JobownerController::class, 'updateJobPost']);
Route::delete('/deletepost/{id}', [JobownerController::class, 'deleteJobPost']);
Route::get('/jobposts/{id}/bids', [JobownerController::class, 'getJobBids']);
Route::post('/bids/{id}/accept', [JobownerController::class, 'acceptBid']);
Route::post('/bids/{id}/reject', [JobownerController::class, 'rejectBid']);
Route::get('/job-statuses', [JobownerController::class, 'getJobStatuses']);

// ✅ Settings (no auth for testing)
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{id}', [SettingController::class, 'show']);
Route::post('/settings', [SettingController::class, 'store']);
Route::put('/settings/{id}', [SettingController::class, 'update']);
Route::delete('/settings/{id}', [SettingController::class, 'destroy']);

// ✅ Testing reviews without auth
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);
Route::post('/reviews', [ReviewController::class, 'store']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
