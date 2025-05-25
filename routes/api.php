<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Bids
Route::post('/offers',[BidController::class,'submitOffer']);
Route::get('/artisan/bids', [  BidController::class, 'getArtisanBids']);
Route::delete('/bids/{bidId}/cancel',[BidController::class, 'cancelBid']);
Route::get('/artisan/bids/accepted',[BidController::class, 'getAcceptedOffers']);
Route::put('/bids/{bidId}/status' , [BidController::class,'updateOfferStatus']);
Route::get('/job/{jobId}/bids', [BidController::class, 'getJobBids']);
Route::post('/bids/{bidId}/respond', [BidController::class, 'respondToBid']);


//Message
Route::get('/chat/{sender_id}/{receiver_id}',[MessageController::class,'getMessages']);
Route::post('/chat/send',[MessageController::class,'sendMessage']);
Route::get('/chat/contacts/{user_id}', [MessageController::class, 'getContacts']);

//swagger
Route::get('/welcome',[\App\Http\Controllers\SwaggerController::class,'welcome']);

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
