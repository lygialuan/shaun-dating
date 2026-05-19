<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\LinkIcon;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class LinkIconController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.link_icon.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Link Icons'),
            ],
        ];
        $title = __('Link Icons');
        $icons = LinkIcon::all();

        return view('shaun_core::admin.link_icon.index', compact('breadcrumbs', 'title', 'icons'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $icon = new LinkIcon();
            $icon->is_active = true;
        } else {
            $icon = LinkIcon::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.link_icon.create', compact('icon', 'title'));
    }

    public function store(Request $request)
    {
        $rules = [
            'is_active' => 'boolean',
            'domain' => 'required|max:255',
            'icon' => ['required',new FileValidation('svg')]
        ];

        $icon = null;

        if ($request->id) {
            $icon = LinkIcon::findOrFail($request->id);
            $rules['icon'] = new FileValidation('svg');
        }

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'domain.required' => __('The domain is required.'),
                'domain.max' => __('The domain must not be greater than 255 characters.'),
                'icon.required' => __('The icon is required.'),
                'icon.uploaded' => __('The icon is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false
        ]);
        $data = $request->except('id', '_token');

        if ($icon) {
            $icon->update($data);
        } else {
            $icon = LinkIcon::create($data);
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');

            $storageFile = File::store($file, [
                'parent_id' => $icon->id,
                'parent_type' => 'link_icon',
                'extension' => $file->getClientOriginalExtension(),
                'name' => $file->getClientOriginalName()
            ]);
            $icon->update(['icon_file_id' => $storageFile->id]);
        } 

        $request->session()->flash('admin_message_success', $icon ? __('Link icon has been successfully updated.') : __('Link icon has been successfully created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $icon = LinkIcon::findOrFail($id);
        $icon->delete();

        return redirect()->route('admin.link_icon.index')->with([
            'admin_message_success' => __('Link icon has been deleted.'),
        ]);
    }
}
