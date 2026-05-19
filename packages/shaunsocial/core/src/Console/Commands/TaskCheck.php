<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Packages\ShaunSocial\Core\Models\Key;

class TaskCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:task_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task check status.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Key::setValue('task_run_time', now());

        return Command::SUCCESS;
    }
}
