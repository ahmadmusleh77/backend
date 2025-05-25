<?php



use App\Http\Controllers\BidController;
use App\Http\Controllers\JobFilterController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthApiController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/signup', [AuthApiController::class, 'signUp']);
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/reset-password', [AuthApiController::class, 'sendResetLinkEmail']);
Route::post('/otp-verification', [AuthApiController::class, 'verifyOtp']);
Route::post('/send-otp', [AuthApiController::class, 'sendOtp']);







//Bids
Route::get('artisan/bids',[BidController::class,'getPost']);
Route::post('artisan/bids',[BidController::class,'sendBids']);
Route::get('artsisan/submitted-offers', [BidController::class, 'getSubmittedOffers']);
Route::put('/artisan/{id}', [BidController::class, 'cancelBid']);
Route::get('/bids/accepted', [BidController::class, 'getAcceptedBids']);
Route::put('/bids/update-status/{id}', [BidController::class, 'updateBidStatus']);
Route::get('/offers/accepted', [BidController::class, 'getAcceptedOffers']);
Route::put('/jobposts/status/{jobId}', [BidController::class, 'updateJobCurrentStatus']);

//Message
Route::get('/chat/contacts/{userId}', [MessageController::class,'getChatContacts']);
Route::post('/chat/send',[MessageController::class,'sendMessage']);


//Filter
Route::get('/jobposts/filter',[JobFilterController::class,'filterJobs']);

//swagger
Route::get('/welcome',[SwaggerController::class,'welcome']);
