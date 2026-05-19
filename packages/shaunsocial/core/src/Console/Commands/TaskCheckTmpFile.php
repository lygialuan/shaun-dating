<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TaskCheckTmpFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:task_check_tmp_file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task check tmp file.';

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
        $tmpFiles = File::files(storage_path('tmp'));
        foreach ($tmpFiles as $tmpFile) {
            if ($tmpFile->isFile() && $tmpFile->getATime() < now()->subDays(1)->getTimestamp()) {
                unlink($tmpFile->getPathname());
            }
        }

        return Command::SUCCESS;
    }
}
