<?php

namespace App\Console\Commands;

use App\Services\D365AccessTokenService;
use Illuminate\Console\Command;
use Throwable;

class EnsureD365TokenCommand extends Command
{
    protected $signature = 'd365:ensure-token';

    protected $description = 'Refresh the cached D365/Azure token when it is missing or expiring within 10 minutes';

    public function handle(D365AccessTokenService $tokens): int
    {
        try {
            $tokens->refreshIfExpiringWithin(600);
            $this->info('D365 token is up to date (refreshed if it was missing or near expiry).');
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
