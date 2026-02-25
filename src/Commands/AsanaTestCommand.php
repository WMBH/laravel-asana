<?php

namespace WMBH\Asana\Commands;

use Illuminate\Console\Command;
use WMBH\Asana\Asana;

class AsanaTestCommand extends Command
{
    public $signature = 'asana:test';

    public $description = 'Test the Asana API connection';

    public function handle(Asana $asana): int
    {
        $this->info('Testing Asana API connection...');

        if (! config('asana.token')) {
            $this->error('No Asana token configured. Set ASANA_TOKEN in your .env file.');

            return self::FAILURE;
        }

        if ($asana->testConnection()) {
            $this->info('Successfully connected to Asana API!');

            return self::SUCCESS;
        }

        $this->error('Failed to connect to Asana API. Please check your token.');

        return self::FAILURE;
    }
}
