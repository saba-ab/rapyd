<?php

namespace Sabaab\Rapyd\Commands;

use Illuminate\Console\Command;

class RapydCommand extends Command
{
    public $signature = 'rapyd';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
