<?php

namespace Packages\ShaunSocial\Gift\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Gift\Models\Gift;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Illuminate\Support\Facades\DB;

class GiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:gift.manage_gifts');
    }

    public function index(Request $request)
    {
        $title = __("Manage Gifts");

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => $title
            ]
        ];
        
        $builder = Gift::query()->orderBy('gifts.order', 'DESC')->select('gifts.*');

        $name = $request->query('name');
        if ($name) {
            $builder->whereExists(function ($q) use ($name) {
                $q->select(DB::raw(1))
                    ->from('translations')
                    ->whereColumn('translations.foreign_key', 'gifts.id')
                    ->where('translations.table_name', 'gifts')
                    ->where('translations.column_name', 'name')
                    ->where('translations.value', 'LIKE', '%' . $name . '%');
            });
        }

        $gifts = $builder->get();

        return view('shaun_gift::admin.gift.index', compact('breadcrumbs', 'title', 'gifts', 'name'));
    }

    public function create(Request $request)
    {
        $id = $request->id;

        if (empty($id)) {
            $gift = new Gift();
            $gift->is_active = true;
        } else {
            $gift = Gift::findByField('id', $id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view( 'shaun_gift::admin.gift.create', compact('title', 'gift'));
    }

    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    'max:255'
                ],
                'price' => [
                    'required',
                    'integer'
                ],
                'icon' => [
                    $request->id ? 'nullable' : 'required',
                    new FileValidation(config('shaun_core.validation.photo'))
                ],
            ],
            [
                'icon.uploaded' => __('The icon is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize() . 'Kb']) . '.',
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false,
        ]);

        $id = $request->id;
        $data = $request->except('id', '_token', 'icon');

        if ($request->id) {
            $gift = Gift::findOrFail($request->id);
            $gift->update($data);
        } else {
            $gift = Gift::create($data);
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');

            $storageFile = File::store($file, [
                'parent_id' => $request->id ?? 0,
                'parent_type' => 'gift',
                'extension' => $file->getClientOriginalExtension(),
                'name' => $file->getClientOriginalName()
            ]);

            $gift->update(['icon' => $storageFile->id]);
        }

        $request->session()->flash(
            'admin_message_success',
            $id
                ? __('Gift has been successfully updated.')
                : __('Gift has been created.')
        );

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $gift = Gift::findOrFail($id);

        $gift->doDeleted();

        return redirect()->route('admin.gift.index')->with([
            'admin_message_success' => __('Gift has been deleted.'),
        ]);
    }

    public function store_order(Request $request)
    {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $tag = Gift::findByField('id', $id);
            if (! $tag) {
                continue;
            }
            $tag->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
