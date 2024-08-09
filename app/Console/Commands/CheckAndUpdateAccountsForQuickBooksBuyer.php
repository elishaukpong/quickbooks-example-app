<?php

namespace App\Console\Commands;

use App\Contracts\AccountingService;
use App\Enums\UserTypes;
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
    public function handle(): void
    {
        User::whereRole(UserTypes::BUYER)
            ->whereHas('quickbooks')
            ->get()
            ->each(function(User $user){
                $accounts = $this->accountingService->getAccountsFor($user);

                foreach ($accounts as $account) {

                    try {
                        $user->quickbooks
                            ->accounts()
                            ->whereRef($account->Id)
                            ->firstOrFail();
                    }catch (\Exception $e) {
                        $user->quickbooks
                            ->accounts()
                            ->create([
                                'ref' => $account->Id,
                                'name' => $account->Name,
                                'type' => $account->AccountType,
                                'user_id' => $user->id
                            ]);
                    }

                }
            });
    }
}
