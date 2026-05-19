<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.log.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('System Log'),
            ],
        ];

        $logFiles = File::files(storage_path('logs'));
        $files = [];
        foreach ($logFiles as $logFile) {
            $fileName = $logFile->getFileName();

            if ($logFile == '.gitignore') {
                continue;
            }

            $files[] = $fileName;
        }

        $lines = config('shaun_core.log.lines');
        $fileName = $request->input('file');
        $line = $request->input('line', $lines[0]);

        if (! in_array($fileName, $files)) {
            $fileName = null;
        }

        $log = null;

        if ($fileName) {
            $pathFile = storage_path('logs/'.$fileName);
            $log = $this->getLogFromFile($pathFile, $line, filesize($pathFile));
        }

        $title = __('Logs');

        return view('shaun_core::admin.log.index', compact('breadcrumbs', 'fileName', 'files', 'line', 'lines', 'title', 'log'));
    }

    public function download($file = null)
    {
        $pathFile = storage_path('logs/'.$file);
        if (! is_file($pathFile)) {
            return redirect()->route('admin.dashboard.index');
        }

        return response()->download($pathFile);
    }

    protected function getLogFromFile($file, $length = 10, $offset = 0)
    {
        $fh = fopen($file, 'r');
        $size = filesize($file);

        // Seek to requested position
        fseek($fh, $offset, SEEK_SET);

        // Read in chunks of 512 bytes
        $position = $offset;
        $break = false;
        $lines = [];
        $chunkSize = 512;
        $buffer = '';

        do {

            // Get next position
            $position -= $chunkSize;
            fseek($fh, $position, SEEK_SET);

            // Whoops we ran out of stuff
            if ($position < 0 || $position > $size) {
                $break = true;
                break;
            }

            // Read a chunk
            $chunk = fread($fh, $chunkSize);
            $buffer = $chunk.$buffer;

            // Parse chunk into lines
            $bufferLines = preg_split('/\r\n?|\n/', $buffer);

            $buffer = array_shift($bufferLines);

            $lines = array_merge($bufferLines, $lines);

            // Are we done?
            if (count($lines) >= $length) {
                $break = true;
            }
        } while (! $break);

        return trim(implode(PHP_EOL, $lines), "\n\r");
    }
}
