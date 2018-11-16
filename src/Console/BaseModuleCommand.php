<?php

namespace Caffeinated\Modules\Console;

use Illuminate\Console\Command;

abstract class BaseModuleCommand extends Command
{
    use LocationConfig;
}
