<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\JobFilterController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SwaggerController;
use App\Http\Controllers\JobownerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/signup', [AuthApiController::class, 'signUp']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/reset-password', [AuthApiController::class, 'sendResetLinkEmail']);
Route::post('/otp-verification', [AuthApiController::class, 'verifyOtp']);
Route::post('/send-otp', [AuthApiController::class, 'sendOtp']);


//ahmad musleh
//Message
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/chat/contacts', [MessageController::class, 'getChatContacts']);
    Route::post('/chat/send', [MessageController::class, 'sendMessage']);
    Route::get('/chat/contact/{id}', [MessageController::class, 'getSingleContact']);
});



//ahmad musleh
Route::middleware(['auth:sanctum','is.artisan'])->group(function () {
    //Bids
    Route::get('artisan/bids', [BidController::class, 'getPost']);
    Route::post('artisan/bids', [BidController::class, 'sendBids']);
    Route::get('artisan/submitted-offers', [BidController::class, 'getSubmittedOffers']);
    Route::delete('/artisan/{id}', [BidController::class, 'cancelBid']);
    Route::get('/offers/accepted', [BidController::class, 'getAcceptedOffers']);
    Route::put('/jobposts/status/{jobId}', [BidController::class, 'updateJobCurrentStatus']);

//Filter
    Route::get('/jobposts/filter', [JobFilterController::class, 'filterJobs']);
});
//swagger

Route::get('/welcome',[SwaggerController::class,'welcome']);

/////admin rep
/// home
Route::get('/artisans/count', [App\Http\Controllers\AdminController::class, 'countCraftsmen']);
Route::get('/users/count',[App\Http\Controllers\AdminController::class ,'countTotalUsers']);
Route::get('/Admins/count', [App\Http\Controllers\AdminController::class, 'countAdmins']);
Route::get('/CompletedJobs', [App\Http\Controllers\AdminController::class, 'countCompletedJobs']);
Route::get('/countAnnouncedJobs', [App\Http\Controllers\AdminController::class, 'countAnnouncedJobs']);
Route::get('/countDailyJobs', [App\Http\Controllers\AdminController::class, 'countDailyJobs']);
//
Route::get('/most/users', [App\Http\Controllers\AdminController::class, 'mostUsersPost']);
//all post admin
Route::get('/Posts', [App\Http\Controllers\AdminController::class, 'Posts']);
Route::delete('/deletePost/{id}', [App\Http\Controllers\AdminController::class, 'deletePost']);
//manage bids
//Route::get('/bidds', [App\Http\Controllers\AdminController::class, 'bidds']);
Route::get('/jobpost/{id}/bids', [AdminController::class, 'getJobpostBids']);

//Manage Users
Route::delete('/users/{id}', [App\Http\Controllers\AdminController::class, 'deleteUser']);
Route::put('/Accept/{id}', [App\Http\Controllers\AdminController::class, 'Accept']);
//



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

// Password Reset Routes for API
Route::get('password/reset/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('password/reset', function (\Illuminate\Http\Request $request) {
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

    if ($status == \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
        return redirect('/')->with('status', 'تم إعادة تعيين كلمة المرور بنجاح!');
    } else {
        return back()->withErrors(['email' => [($status)]]);
    }
})->name('password.update');

//karam
// karam
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/newpost', [\App\Http\Controllers\JobownerController::class, 'newPost']);
    Route::get('/posts', [\App\Http\Controllers\JobownerController::class, 'getJobPosts']);
    Route::get('/posts/{id}', [\App\Http\Controllers\JobownerController::class, 'getJobPostById']);
    Route::post('/posts/{id}', [\App\Http\Controllers\JobownerController::class, 'updateJobPost']);
    Route::delete('/posts/{id}', [\App\Http\Controllers\JobownerController::class, 'deleteJobPost']);
    Route::get('/jobposts/{id}/bids', [\App\Http\Controllers\JobownerController::class, 'getJobBids']);
    Route::post('/bids/{id}/accept', [\App\Http\Controllers\JobownerController::class, 'acceptBid']);
    Route::post('/bids/{id}/reject', [\App\Http\Controllers\JobownerController::class, 'rejectBid']);
    Route::get('/job-statuses', [\App\Http\Controllers\JobownerController::class, 'getJobStatuses']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});


//shahd
//Notification
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/notifications',[\App\Http\Controllers\NotificationController::class,'getNotifications']);
    Route::put('/notifications/{id}/read',[\App\Http\Controllers\NotificationController::class,'markAsRead']);
});


//  Settings routes 
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{id}', [SettingController::class, 'show']);
Route::post('/settings', [SettingController::class, 'store']);
Route::put('/settings/{id}', [SettingController::class, 'update']);
Route::delete('/settings/{id}', [SettingController::class, 'destroy']);

//  Reviews routes 
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);
Route::post('/reviews', [ReviewController::class, 'store']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
Route::get('/reviews/average/{userId}', [ReviewController::class, 'averageRating']);

