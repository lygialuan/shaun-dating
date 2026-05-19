<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Install;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseManager
{
    public function install()
    {
        $outputLog = new BufferedOutput;

        $result = $this->migrateInstallFirst($outputLog);
        if ($result['status'] != 'success') {
            return $result;
        }

        $result = $this->migrate($outputLog);
        if ($result['status'] != 'success') {
            return $result;
        }

        //run sql install
        $path = base_path('packages/shaunsocial');
        $directories = scandir($path);
        $packages = ['core', 'wallet'];
        foreach ($directories as $name) {
            if ($name != '.' && $name != '..' && ! in_array($name, $packages)) {
                $packages[] = $name;
            }
        }

        try {
            foreach ($packages as $name) {
                $sqlFile = $path.'/'.$name.'/database/sql/install.sql';
                runSqlFile($sqlFile);

                //run install
                $packageName = getPackageName($name);
                $packageName = 'Packages\ShaunSocial\\'.$packageName.'\Repositories\Helpers\Package';
                if (class_exists($packageName)) {
                    $package = app($packageName);
                    if (method_exists($package, 'install')) {
                        $package->install();
                    }
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'dbOutputLog' => $e->getTraceAsString(),
            ];
        }
        
        return $result;
    }

    public function update()
    {
        $outputLog = new BufferedOutput;
        $result = $this->response('', 'success', $outputLog);

        $result = $this->migrateUpdateFirst($outputLog);
        if ($result['status'] != 'success') {
            return $result;
        }

        $result = $this->migrate($outputLog);
        return $result;
    }

    private function migrateInstallFirst(BufferedOutput $outputLog)
    {
        try {
            $params = ['--force' => true, '--path' => '/database/migrations'];
            Artisan::call('migrate', $params, $outputLog);

            $params = ['--force' => true, '--path' => '/packages/shaunsocial/core/database/migrations'];
            Artisan::call('migrate', $params, $outputLog);

        } catch (Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->response('', 'success', $outputLog);
    }

    private function migrateUpdateFirst(BufferedOutput $outputLog)
    {
        try {
            $params = ['--force' => true, '--path' => '/packages/shaunsocial/core/database/migrations'];
            Artisan::call('migrate', $params, $outputLog);

            $params = ['--force' => true, '--path' => '/packages/shaunsocial/wallet/database/migrations'];
            Artisan::call('migrate', $params, $outputLog);
        } catch (Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->response('', 'success', $outputLog);
    }

    private function migrate(BufferedOutput $outputLog)
    {
        try {
            $params = ['--force' => true];
            Artisan::call('migrate', $params, $outputLog);
        } catch (Exception $e) {
            return $this->response($e->getMessage(), 'error', $outputLog);
        }

        return $this->response('', 'success', $outputLog);
    }

    private function response($message, $status, BufferedOutput $outputLog)
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch(),
        ];
    }
}
