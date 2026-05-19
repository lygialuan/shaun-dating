<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;

class TranslateExportApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:translate_export_app {file_name}';

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
        $fileName = $this->argument('file_name');
        $data = file_get_contents($fileName);
        $data = json_decode($data,true);
        $fp = fopen('file.csv', 'w');

        foreach ($data as $key => $value) {
            fputcsv($fp, [$key, $value]);
        }
        fclose($fp);

        foreach ($data as $key => &$value) {
            $value = '*'.$value.'*';
        }

        writeFileLanguageJson('export_translate_app.json', $data);
    }
}
