<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Company;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyUrgentTasksSummary;

class SendUrgentTaskEmailsForCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    public function __construct(public Company $company) {}

    public function handle(): void
    {
        $users = User::where('company_id', $this->company->id)
                     ->where('is_deleted', false)
                     ->whereNotNull('email')
                     ->get();

        foreach ($users as $user) {
            $urgentCount = Task::urgent()
                ->where('company_id', $this->company->id)
                ->where(function ($query) use ($user) {
                    $query->where('creator_user_id', $user->id)
                          ->orWhere('supervisor_user_id', $user->id)
                          ->orWhereHas('users', fn($q) =>
                              $q->where('user_id', $user->id)
                                ->whereIn('role', ['assigned', 'consult', 'informer'])
                          );
                })
                ->count();

            if ($urgentCount > 0) {
                Mail::to($user->email)->send(
                    new DailyUrgentTasksSummary($user->name, $urgentCount, $this->company->name)
                );
            }
        }
    }
}