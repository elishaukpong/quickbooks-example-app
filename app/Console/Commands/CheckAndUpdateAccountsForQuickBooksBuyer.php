<?php

namespace App\Console\Commands;

use App\Contracts\AccountingService;
use App\Models\User;
use Illuminate\Console\Command;

class CheckAndUpdateAccountsForQuickBooksBuyer extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quickbooks:check-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected AccountingService $accountingService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::role('Customer')
            ->whereHas('quickbooks')
            ->get()
            ->each(function(User $user){

            });
    }
}
