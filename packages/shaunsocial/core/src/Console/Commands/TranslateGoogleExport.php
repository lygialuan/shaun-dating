<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;

class TranslateGoogleExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:translate_google_export';

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
        $file = base_path().'/translate.csv';
        $data = [];
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {   
                if (empty($row[1])) {
                    continue;
                }
                $data[$row[0]] = $row[1];
            }
            fclose($handle);
        }
        writeFileLanguageJson(base_path().'/translate.json', $data);
    }
}
