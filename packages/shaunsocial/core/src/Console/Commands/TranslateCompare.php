<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;

class TranslateCompare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:translate_compare';

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
        $pathServer = base_path('lang');
        $pathClient = base_path('public/locales');
        $data = file_get_contents($pathServer.'/install.json');
        $dataOld = file_get_contents($pathServer.'/install_old.json');
        $data = json_decode($data,true);
        $dataOld = json_decode($dataOld,true);
        $result = array_diff_key($data, $dataOld);

        writeFileLanguageJson('server.json', $result);

        $data = file_get_contents($pathClient.'/install.json');
        $dataOld = file_get_contents($pathClient.'/install_old.json');
        $data = json_decode($data,true);
        $dataOld = json_decode($dataOld,true);
        $result = array_diff_key($data, $dataOld);

        writeFileLanguageJson('client.json', $result);
    }
}
