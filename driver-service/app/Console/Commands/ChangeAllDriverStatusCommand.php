<?php

namespace App\Console\Commands;

use App\Models\Driver;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('change:driver-status {--force}')]
#[Description('Command description')]
class ChangeAllDriverStatusCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Driver::query()->update(['status' => Driver::STATUS_AVAILABLE]);

        $this->info('Driver status changed');
    }
}
