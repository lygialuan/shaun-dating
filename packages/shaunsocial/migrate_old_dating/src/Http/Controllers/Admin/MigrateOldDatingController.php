<?php

namespace Packages\ShaunSocial\MigrateOldDating\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\MigrateOldDating\Models\SyncOldUser;
use Illuminate\Support\Facades\DB;

class MigrateOldDatingController extends Controller
{
    public function create(Request $request)
    {
        $title = __('Import users from your existing mooDating');

        $job = SyncOldUser::first();

        return view('shaun_core::admin.user.sync_data', compact('title', 'job'));
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'database_host' => 'required',
            'port'          => 'required|integer',
            'database_name' => 'required',
            'user_name'     => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $data = $request->except('_token');

        try {
            config([
                'database.connections.old_db' => [
                   'driver'    => 'mysql',
                    'host'      => $data['database_host'],
                    'port'      => $data['port'],
                    'database'  => $data['database_name'],
                    'username'  => $data['user_name'],
                    'password'  => $data['password'] ?? '',
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ]
            ]);

            DB::connection('old_db')->getPdo();
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'messages' => ['Unable to connect.']
            ]);
        }

        SyncOldUser::create($data);

        return response()->json([
            'status' => true
        ]);
    }

    public function import(Request $request)
    {
        $job = SyncOldUser::first();
        $job->status = 'processing';
        $job->save();

        $request->session()->flash(
            'admin_message_success', __('Import is in progress. This may take a few minutes.')
        );

        return response()->json([
            'status' => true
        ]);
    }
    
    public function remove()
    {
        $job = SyncOldUser::first();
        $job->delete();
        return response()->json([
            'status' => true
        ]);
    }
}
