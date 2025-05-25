<?php

namespace Itstudioat\Vuedata\Commands;

use Illuminate\Console\Command;

class VuedataCommand extends Command
{
    public $signature = 'vuedata';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
