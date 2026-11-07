<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReleaseExpiredTickets extends Command
{
    protected $signature = 'tickets:release';

    protected $description = 'Deprecated alias for tickets:expire-reservations';

    public function handle(): int
    {
        $this->warn('Command tickets:release sudah usang. Gunakan tickets:expire-reservations.');

        return $this->call('tickets:expire-reservations');
    }
}
