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
        return Auth::user()->notifications;
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'The notification has been marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    // إرسال إشعار عام لمستخدم معيّن (تُستخدم داخليًا حسب نوع المستخدم)
    private function sendNotification(User $user, string $type, array $data)
    {
        $user->notifications()->create([
            'type' => $type,
            'data' => json_encode($data)
        ]);
    }

    //for admin
    public function notifyAdminNewRegistration(User $admin,User $newUser): void
    {
        if ($admin->user_type==='admin'){
            $this->sendNotification($admin,'new_registration_request',[
                'message'=>'craftsman' . $newUser->name .'has requested registration and is waiting for review']);

        }

    }

    public function notifyAdminActivityReport(User $admin , array $reportData)
    {
        if($admin->user_type==='admin'){
            $this->sendNotification($admin,'reportData',[
                'message'=>'New activity report',
                'details'=> $reportData
            ]);
        }

    }

    //for Job Holders

    public function notifyTenderRequest(User $jobHolder,User $craftsman, $jobTitle)
    {
        if ($jobHolder->user_type==='jobHolder'){
            $this->sendNotification($jobHolder,'tender_request',[
                'message'=>'the craftsman' .$craftsman->name . 'submitted an offer for a' .$jobTitle .'job'
            ]);
        }

    }

    public function notifyTenderApprovalToJobHolder(User $jobHolder, User $craftsman, $jobTitle)
    {
        if ($jobHolder->user_type === 'job_holder') {
            $this->sendNotification($jobHolder, 'tender_approved', [
                'message' => $craftsman->name . 'is offer for a '. $jobTitle .'job has been accepted'
            ]);
        }
    }

    public function notifyJobExpiration(User $jobHolder, $jobTitle, $deadline)
    {
        if ($jobHolder->user_type === 'job_holder') {
            $this->sendNotification($jobHolder, 'job_expiration', [
                'message' => 'Alert: The application deadline for the'.$jobTitle  .'position is approaching. ',
                'deadline' => $deadline
            ]);
        }
    }

    public function notifyNewMessageToJobHolder(User $jobHolder, User $craftsman, $messageContent)
    {
        if ($jobHolder->user_type === 'job_holder') {
            $this->sendNotification($jobHolder, 'new_message', [
                'message' => 'New message from' . $craftsman->name,
                'content' => $messageContent
            ]);
        }
    }


    //for Craftsmen

    public function notifyNewJob(User $craftsman, $jobTitle)
    {
        if ($craftsman->user_type === 'craftsman') {
            $this->sendNotification($craftsman, 'new_job_request', [
                'message' => 'New job posted: ' . $jobTitle
            ]);
        }
    }

    public function notifyTenderApprovalToCraftsman(User $craftsman, $jobTitle)
    {
        if ($craftsman->user_type === 'craftsman') {
            $this->sendNotification($craftsman, 'tender_approved', [
                'message' => 'Your job offer has been accepted:' . $jobTitle
            ]);
        }
    }

    public function notifyCraftsmanDeadline(User $craftsman, $jobTitle, $deadline)
    {
        if ($craftsman->user_type === 'craftsman') {
            $this->sendNotification($craftsman, 'deadline_reminder', [
                'message' => 'Deadline Reminder for ' . $jobTitle . 'Job',
                'deadline' => $deadline
            ]);
        }
    }

    public function notifyNewMessageToCraftsman(User $craftsman, User $jobHolder, $messageContent)
    {
        if ($craftsman->user_type === 'craftsman') {
            $this->sendNotification($craftsman, 'new_message', [
                'message' => 'New message from' . $jobHolder->name,
                'content' => $messageContent
            ]);
        }
    }

}
