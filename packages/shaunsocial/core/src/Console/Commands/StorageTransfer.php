<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\StorageFile;

class StorageTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:storage_transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task Storage Transfer.';

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
        $transfer = Key::getValue('storage_service_transfer');
        if ($transfer) {
            $files = StorageFile::where('service_key', 'public')->limit(config('shaun_core.file.transfer.limit'))->get();
            if (count($files)) {
                foreach ($files as $file) {
                    Storage::disk($transfer)->put($file->storage_path, file_get_contents(storage_path('app/public/').$file->storage_path));
                    $file->update([
                        'service_key' => $transfer,
                    ]);

                    @unlink(storage_path('app/public/').$file->storage_path);
                }
            } else {
                Key::removeKey('storage_service_transfer');
            }
        }

        return Command::SUCCESS;
    }
}
