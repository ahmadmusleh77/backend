<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Tests\Integration\Auth\Fixtures\AuthenticationTestUser;


class NotificationController extends Controller
{
    //

    public function getNotifications()
    {
        return Auth::user()->notifications()->latest()->get();
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return response()->json(['message' => ' The notification has been marked as read']);
        }

        return response()->json(['message' => ' Notification not found'], 404);
    }

    // إرسال إشعار عام لمستخدم معيّن (تُستخدم داخليًا حسب نوع المستخدم)
    private function sendNotification(User $user, string $type, array $data): void
    {

        \Log::info('📨 بدء إرسال إشعار', [
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data
        ]);
        try {
            \App\Models\Notification::create([
                'user_id'=>$user->user_id,
                'type' => $type,
                'data' => json_encode($data)
            ]);
            \Log::info('✅ تم تخزين الإشعار في قاعدة البيانات بنجاح', [
                'user_id' => $user->id,
                'type' => $type
            ]);
        }catch (\Exception $e) {
            \Log::error('❌ فشل تخزين الإشعار', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    }

    //for admin
    public function notifyAdminNewRegistration(User $admin,User $newUser): void
    {
        if ($admin->user_type==='admin'){
            $this->sendNotification($admin,'new_registration_request',[
                'message'=>'craftsman' . $newUser->name .' has requested registration and is waiting for review']);

        }

    }

    public function notifyAdminActivityReport(User $admin , array $reportData)
    {
        if($admin->user_type==='admin'){
            $this->sendNotification($admin,'reportData',[
                'message'=>' New activity report',
                'details'=> $reportData
            ]);
        }

    }

    //for Job Holders

    public function notifyTenderRequest(User $jobowner,User $artisan, $jobTitle)
    {
        \Log::info('📬 داخل notifyTenderRequest');
        if ($jobowner->user_type==='jobowner'){
            \Log::info('📢 إرسال إشعار tender_request...');
            $this->sendNotification($jobowner,'tender_request',[
                'message'=>' the artisan ' .$artisan->name . ' submitted an offer for a' .$jobTitle .'job'
            ]);
        }else {
            \Log::warning('⚠️ jobHolder ليس jobHolder بل: ' . $jobowner->user_type);
        }

    }

    public function notifyTenderApprovalToJobHolder(User $jobowner, User $artisan, $jobTitle)
    {
        if ($jobowner->user_type === '$jobowner') {
            $this->sendNotification($jobowner, 'tender_approved', [
                'message' => $artisan->name . 'is offer for a '. $jobTitle .'job has been accepted'
            ]);
        }
    }

    public function notifyJobExpiration(User $jobowner, $jobTitle, $deadline)
    {
        if ($jobowner->user_type === 'jobowner') {
            $this->sendNotification($jobowner, 'job_expiration', [
                'message' => ' Alert: The application deadline for the '.$jobTitle  .' position is approaching. ',
                'deadline' => $deadline
            ]);
        }
    }

    public function notifyNewMessageToJobHolder(User $jobowner, User $artisan, $messageContent)
    {
        if ($jobowner->user_type === 'jobowner') {
            $this->sendNotification($jobowner, 'new_message', [
                'message' => ' New message from ' . $artisan->name,
                'content' => $messageContent
            ]);
        }
    }


    //for Craftsmen

    public function notifyNewJob(User $artisan, $jobTitle)
    {
        if ($artisan->user_type === 'artisan') {
            $this->sendNotification($artisan, 'new_job_request', [
                'message' => ' New job posted: ' . $jobTitle
            ]);
        }
    }

    public function notifyTenderApprovalToCraftsman(User $artisan, $jobTitle)
    {
        if ($artisan->user_type === 'artisan') {
            $this->sendNotification($artisan, 'tender_approved', [
                'message' => ' Your job offer has been accepted: ' . $jobTitle
            ]);
        }
    }

    public function notifyCraftsmanDeadline(User $artisan, $jobTitle, $deadline)
    {
        if ($artisan->user_type === 'artisan') {
            $this->sendNotification($artisan, 'deadline_reminder', [
                'message' => ' Deadline Reminder for ' . $jobTitle . 'Job',
                'deadline' => $deadline
            ]);
        }
    }

    public function notifyNewMessageToCraftsman(User $artisan, User $jobHolder, $messageContent)
    {
        if ($artisan->user_type === 'artisan') {
            $this->sendNotification($artisan, 'new_message', [
                'message' => ' New message from ' . $jobHolder->name,
                'content' => $messageContent
            ]);
        }
    }

}
