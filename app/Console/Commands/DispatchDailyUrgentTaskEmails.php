<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Jobs\SendUrgentTaskEmailsForCompany;

class DispatchDailyUrgentTaskEmails extends Command
{
    protected $signature = 'urgent:dispatch-daily';
    protected $description = 'Dispatch urgent task emails for all companies';

    public function handle(): void
    {
        $companies = Company::whereHas('users')->get();

        foreach ($companies as $company) {
            SendUrgentTaskEmailsForCompany::dispatch($company);
        }

        $this->info('Dispatched ' . $companies->count() . ' jobs.');
    }
}