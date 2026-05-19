<?php


namespace Packages\ShaunSocial\Story\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Packages\ShaunSocial\Story\Models\StorySong;

class SongController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.story.song_manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Story Songs'),
            ],
        ];
        $title = __('Story Songs');
        $builder = StorySong::orderBy('id', 'desc');
        $name = $request->query('name');
        if ($name) {
            $builder->where('name','LIKE', '%'.$name.'%');
        }

        $statusArray = [
            '' => __('All status'),
            '1' => __('Active'),
            '0' => __('InActive')
        ];

        $defaultArray = [
            '' => __('All'),
            '1' => __('Yes'),
            '0' => __('No')
        ];

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status !== '') {
            $builder->where('is_active', $status);
        }

        $default = $request->query('default', '');
        if (! in_array($default, array_keys($defaultArray))) {
            $default = '';
        }

        if ($default !== '') {
            $builder->where('is_default', $default);
        }

        $songs = $builder->paginate(setting('feature.item_per_page'));
        return view('shaun_story::admin.song.index', compact('breadcrumbs', 'title', 'songs', 'statusArray', 'status', 'name', 'defaultArray' ,'default'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $song = new StorySong();
            $song->is_active = true;
        } else {
            $song = StorySong::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_story::admin.song.create', compact('title', 'song'));
    }

    public function store(Request $request)
    {
        $request->mergeIfMissing([
            'is_active' => 0,
            'is_default' => 0
        ]);

        $rules = [
            'name' => 'required|max:255',
            'file' => ['required', new FileValidation(config('shaun_story.song_validate'))]
        ];

        if ($request->id) {
            unset($rules['file']);
        }

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'file.required' => __('The file is required.'),
                'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $data = $request->only(['is_active', 'name', 'is_default']);

        if ($request->id) {
            $song = StorySong::findOrFail($request->id);
            $song->update($data);
        } else {
            $song = StorySong::create($data);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $storageFile = File::store($file, [
                'parent_id' => $song->id,
                'parent_type' => 'story_song',
                'extension' => $file->getClientOriginalExtension(),
                'name' => $file->getClientOriginalName()
            ]);
            $song->update(['file_id' => $storageFile->id]);
        }

        if ($request->id) {
            $request->session()->flash('admin_message_success', __('Song has been updated.'));
        } else {
            $request->session()->flash('admin_message_success', __('Song has been created.'));
        }

        

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $song = StorySong::findOrFail($id);

        $song->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('Song has been deleted.'),
        ]);
    }
}
