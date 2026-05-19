<?php


namespace Packages\ShaunSocial\Story\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;
use Packages\ShaunSocial\Story\Models\StoryBackground;
use Intervention\Image\ImageManager;

class BackgroundController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.story.background_manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Story Backgrounds'),
            ],
        ];
        $title = __('Story Backgrounds');
        $backgrounds = StoryBackground::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

        return view('shaun_story::admin.background.index', compact('breadcrumbs', 'title', 'backgrounds'));
    }

    public function store_active(Request $request)
    {
        $rules = [
            'id' => 'required',
            'active' => 'required',
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

        $background = StoryBackground::findOrFail($request->id);

        $background->update([
            'is_active' => $request->active,
        ]);

        return response()->json([
            'status' => true,
        ]);
    }

    public function create($id = null)
    {
        return view('shaun_story::admin.background.create');
    }

    public function store(Request $request)
    {
        $request->mergeIfMissing([
            'is_active' => 0,
        ]);

        $rules = [
            'photo' => ['required', new PhotoValidation()]
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'photo.required' => __('The photo is required.'),
                'photo.uploaded' => __('The photo is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $photo = $request->file('photo');

        $manager = new ImageManager(
            new \Intervention\Image\Drivers\Gd\Driver()
        );
        $image = $manager->read($photo);
        $height = $image->height();
        $width = $image->width();
        $ratio = abs($width * 16 - $height * 9);
        if ($ratio > 20) {
            return response()->json([
                'status' => false,
                'messages' => [__('The photo must aspect ratio 9:16.')]
            ]);
        }

        $background = StoryBackground::create([
            'is_active' => $request->is_active
        ]);

        $storageFile = File::store($photo, [
            'parent_id' => $background->id,
            'parent_type' => 'story_background',
            'extension' => $photo->getClientOriginalExtension(),
            'name' => $photo->getClientOriginalName()
        ]);
        $background->update(['photo_id' => $storageFile->id]);

        $request->session()->flash('admin_message_success', __('Background has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $background = StoryBackground::findOrFail($id);

        if ($background->canDelete()) {
            $background->doDeleted();
        }        

        return redirect()->back()->with([
            'admin_message_success' => __('Background has been deleted.'),
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
            $background = StoryBackground::find($id);
            if (! $background) {
                continue;
            }
            $background->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
