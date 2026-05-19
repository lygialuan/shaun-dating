<?php

namespace Packages\ShaunSocial\Dating\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttribute;

class DatingInterestAttributeController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:dating.manage_interest_attributes');
    }

    public function index(Request $request)
    {
        $title = __("Manage Interests");

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => $title
            ]
        ];

        $attributes = DatingInterestAttribute::getByCategoryId(0);

        return view( 'shaun_dating::admin.interest_attribute.index', compact('breadcrumbs', 'title', 'attributes'));
    }

    public function create(Request $request)
    {
        $id = $request->id;

        $otherAttributes = collect([]);
        if (!$id) {
            $otherAttributes = DatingInterestAttribute::get();
        }

        if (empty($id)) {
            $attribute = new DatingInterestAttribute();
            $attribute->is_active = true;
        } else {
            $attribute = DatingInterestAttribute::findByField('id', $id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view( 'shaun_dating::admin.interest_attribute.create', compact('title', 'attribute', 'otherAttributes'));
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
                'allow_multiple' => [
                    'required'
                ],
                'clone_from' => [
                    'nullable',
                    function ($_, $cloneFrom, $fail) {
                        if ($cloneFrom) {
                            $attribute = DatingInterestAttribute::findByField('id', $cloneFrom);
                            if (!$attribute) {
                                return $fail(__('The attribute to clone from is not found.'));
                            }
                        }
                    },
                ]
            ],
            [
                'name.required' => __('The name is required.'),
                'icon.uploaded' => __('The icon is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize() . 'Kb']) . '.',
                'allow_multiple.required' => __('The allow multiple is required')
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
        $data = $request->except('id', '_token');
        $oldAttribute = DatingInterestAttribute::findByField('id', $request->clone_from);

        if ($request->id) {
            $category = DatingInterestAttribute::findOrFail($request->id);
            $category->update($data);
        } else {
            if ($request->clone_from) {
                $category = DatingInterestAttribute::create([
                    'name' => $request->name,
                    'icon_file_id' => null,
                    'is_active' => $request->is_active ? 1 : 0,
                    'allow_multiple' => $request->allow_multiple
                ]);

                $values = DatingInterestAttribute::getAttributeValues($oldAttribute->id);
                foreach ($values as $value) {
                    $value->cloneValue($category->id);
                } 
            }
            else {
                $category = DatingInterestAttribute::create($data);
            }
        }

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');

            $storageFile = File::store($file, [
                'parent_id' => $request->id ?? 0,
                'parent_type' => 'dating_attribute',
                'extension' => $file->getClientOriginalExtension(),
                'name' => $file->getClientOriginalName()
            ]);

            $category->update(['icon_file_id' => $storageFile->id]);
        }

        $request->session()->flash(
            'admin_message_success',
            $id
                ? __('Attribute has been successfully updated.')
                : __('Attribute has been created.')
        );

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $attribute = DatingInterestAttribute::findOrFail($id);

        $attribute->delete();

        return redirect()->route('admin.dating.interest_attribute', $attribute->category_id)->with([
            'admin_message_success' => __('Attribute has been deleted.'),
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
            $tag = DatingInterestAttribute::findByField('id', $id);
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
    
    public function search(Request $request) {
        $keyword = $request->keyword;
        $attributes = MarketPlaceAttribute::where('name', 'LIKE', '%'.$keyword.'%')
            ->orderBy(DB::raw("LOCATE('".$keyword."', name)"))
            ->limit(5)
            ->get();

        $attributes = $attributes->map(function ($attribute) {
            $attribute->icon = $attribute->getFile('icon_file_id')?->getUrl();
            return $attribute;
        });

        return response()->json([
            'status' => true,
            'data' => $attributes
        ]);
    }
}
