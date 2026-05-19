<?php


namespace Packages\ShaunSocial\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Packages\ShaunSocial\Core\Models\MailTemplate;
use Symfony\Component\Process\Process;

class TranslateExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shaun_core:translate_export {type?}';

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
        $type = $this->argument('type');
        $key = 'install';
        //server
        $templates = MailTemplate::all();
        $templates = $templates->mapWithKeys(function ($item, $key) {
            return [$item->getKeyNameTranslate() => $item->name];
        });
        file_put_contents(getServerLanguagePath($key),'{}');
        Artisan::call('translatable:export '.$key);
        $data = array_unique(getServerLanguageArray($key));
        $data = collect($data);
        $data = $data->filter(function ($value, $key) {
            return (strpos($key,'".$') !== 0);
        });

        $data = $data->map(function ($value, $key) use ($templates){
            if (!empty($templates[$key])) {
                return $templates[$key];
            }

            return $value;
        });

        $dataAll = $data;

        if ($type == 'test') {
            $data = $data->map(function ($value, $key) use ($templates){
                return '*'.$value.'*';
            });
        }

        writeFileLanguageJson(getServerLanguagePath($key), $data->toArray());

        //client 
        file_put_contents(getClientLanguagePath($key),'{}');
        Process::fromShellCommandline('node export-i18n.js')->run();
        $data = collect(array_unique(getClientLanguageArray($key)));

        $dataAll = $dataAll->merge($data);

        if ($type == 'test') {
            $data = $data->map(function ($value, $key) use ($templates){
                return '*'.$value.'*';
            });
        }
        writeFileLanguageJson(getClientLanguagePath($key), $data->toArray());

        if ($type == 'csv') {
            $fp = fopen(base_path().'/translate.csv', 'w');

            foreach ($dataAll->toArray() as $fields) {                
                fputcsv($fp, [$fields]);
            }

            fclose($fp);            
        }
    }
}
