<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Models\HashtagTrending;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Group\Models\GroupHashtagTrending;

class HashtagController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.hashtag.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Hashtags'),
            ],
        ];
        $title = __('Hashtags');
        $builder = Hashtag::orderBy('name','asc');
        $name = $request->query('name');
        if ($name) {
            $builder->where('name','LIKE', '%'.$name.'%');
        }

        $statusArray = [
            '' => __('All status'),
            '1' => __('Active'),
            '0' => __('InActive')
        ];

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status !== '') {
            $builder->where('is_active', $status);
        }

        $hashtags = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_core::admin.hashtag.index', compact('breadcrumbs', 'title', 'hashtags', 'name', 'status', 'statusArray'));
    }

    public function create(Request $request)
    {
        $hashtag = new Hashtag();
        return view('shaun_core::admin.hashtag.create', compact('hashtag'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => [
                'required', 
                'max:255',
                'unique:Packages\ShaunSocial\Core\Models\Hashtag,name',
                function ($attribute, $hashtag, $fail) {
                    if (! checkHashtag($hashtag)) {
                        return $fail(__('The hashtag is not validated.'));
                    }
                }
            ],
        ];
        
        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.unique' => __('The name already exists.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
            ]
        );
        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing(['is_active' => 0]);
        $data = $request->except('id', '_token');

        Hashtag::create($data);

        $request->session()->flash('admin_message_success', __('The hashtag has been created.'));

        return response()->json([
            'status' => true,
        ]);
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

        $hashtag = Hashtag::findOrFail($request->id);

        $hashtag->update([
            'is_active' => $request->active,
        ]);

        //update trending hashtag
        HashtagTrending::where('hashtag_id', $hashtag->id)->update(['is_active' => $request->active]);
        GroupHashtagTrending::where('hashtag_id', $hashtag->id)->update(['is_active' => $request->active]);

        //update use hashtag suggest
        UserHashtagSuggest::where('hashtag_id', $hashtag->id)->update(['is_active' => $request->active]);

        return response()->json([
            'status' => true,
        ]);
    }
}
