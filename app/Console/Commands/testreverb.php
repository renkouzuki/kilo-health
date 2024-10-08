<?php

namespace App\Console\Commands;

use App\Events\testing;
use Illuminate\Console\Command;

class testreverb extends Command
{
    protected $signature = 'test:event {message?}';
    protected $description = 'Trigger a test event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->argument('message') ?? 'Test message';
        event(new testing($message));
        $this->info("Test event triggered with message: $message");
    }
}
