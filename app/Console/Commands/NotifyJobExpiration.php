<?php

namespace App\Console\Commands;

use App\Http\Controllers\NotificationController;
use App\Models\Jobpost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyJobExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:job-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify job holders about upcoming job expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Jobs expire in 2 days
        $jobs=Jobpost::whereDate('deadline','=',Carbon::now()->addDays(2)->toDateString())->get();
        foreach ($jobs as $job){
            $jobHolder=User::find($job->user_id);

            if($jobHolder){
                app(NotificationController::class)->notifyJobExpiration($jobHolder,$job->title,$job->deadline);
            }
        }
        $this->info('Job expiration notifications sent successfully.');
    }
}
