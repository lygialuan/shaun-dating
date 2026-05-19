<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use App\Console\Kernel;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Key;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.task.manage');
    }

    public function index(Request $request)
    {
        new Kernel(app(), new Dispatcher());
        $schedule = app(Schedule::class);
        $commands = Artisan::all();
        $tasks = collect($schedule->events())->map(function ($event) use ($commands) {
            $command = Str::of($event->command)->explode(' ')->get(2);
            return [
                'command' => $command,
                'expression' => $event->expression,
                'description' => $command == 'model:prune' ? __('Delete data that are no longer needed.') : __($commands[$command]->getDescription()),
            ];
        });

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Tasks'),
            ],
        ];
        $title = __('Tasks');
        $time = Key::getValue('task_run_time');
        if ($time) {
            $timezone = $request->user()->timezone;
            $carbon = new Carbon($time);
            $time = $carbon->setTimezone($timezone)->diffForHumans();
        }

        return view('shaun_core::admin.task.index', compact('breadcrumbs', 'title', 'tasks', 'time'));
    }
}
