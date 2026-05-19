<?php

namespace Packages\ShaunSocial\Dating\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttribute;
use Packages\ShaunSocial\Dating\Models\DatingInterestAttributeValue;

class DatingInterestAttributeValueController extends Controller
{
    public function __construct() {
        $this->middleware('has.permission:dating.manage_interest_attributes');
    }

    public function index(Request $request)
    {
        $attributeId = $request->attribute_id;

        $attribute = DatingInterestAttribute::findOrFail($attributeId);
        
        $title = __("Manage Values of ':attributeName'", ['attributeName' => $attribute->getTranslatedAttributeValue('name')]);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __("Manage Interests"),
                'route' => 'admin.dating.interest_attribute',
            ],
            [
                'title' => $title,
            ]
        ];

        $statusArray = [
            '' => __('All status'),
            '1' => __('Active'),
            '0' => __('InActive')
        ];

        $pendings = [
            '' => __('All'),
            '1' => __('Added by members'),
            '0' => __('Added by admin')
        ];

        $builder = DatingInterestAttributeValue::where('attribute_id', $attributeId);

        $status = $request->query('status', '');
        if (in_array($status, [0,1])) {
            $builder->where('is_active', $request->query('status'));
        }
        
        $builder ->orderBy('name','asc');

        $attributeValues = $builder->paginate(setting('feature.item_per_page'));
        
        return view(
            'shaun_dating::admin.interest_attribute_value.index', 
            compact(
                'breadcrumbs', 
                'title', 
                'attributeValues', 
                'status', 
                'statusArray',
                'attributeId',
                'attribute'
            )
        );
    }

    public function create(Request $request)
    {
        $attributeId = $request->attribute_id;
        $id = $request->id;
        if ($id) {
            $value = DatingInterestAttributeValue::findByField('id', $id);
        }
        else {
            $value = new DatingInterestAttributeValue();
            $value->is_active = true;
        }
        $attribute = DatingInterestAttribute::findByField('id', $attributeId);
        $moreAttributes = DatingInterestAttribute::where('category_id', $attribute->category_id)
            ->whereNot('id', $attributeId)
            ->get();

        return view('shaun_dating::admin.interest_attribute_value.create', compact('value', 'attributeId', 'moreAttributes'));
    }

    public function store(Request $request)
    {
        $attribute = null;
        $id = $request->id;
        $value = null;
        if ($id) {
            $value = DatingInterestAttributeValue::findOrFail($id);
        }
       
        $rules = [
            'name' => [
                'required', 
                'max:255',
            ],
            'attribute_id' => [
                'required', 
                'alpha_num',
                    function ($_, $attributeId, $fail) use (&$attribute) {
                        $attribute = DatingInterestAttribute::findByField('id', $attributeId);
                        if (! $attribute || ! $attribute->is_active) {
                            return $fail(__('The attribute is not found.'));
                        }
                    },
            ],
            'add_other_attribute' => [
            ],
            'more_attribute' => [
                function ($_, $moreAttribute, $fail) {
                    if ($moreAttribute) {
                        $otherAttributeCount = DatingInterestAttribute::whereIn('id', $moreAttribute)->count();
                        if ($otherAttributeCount != count($moreAttribute)) {
                            return $fail(__('The more attribute is not found'));
                        }
                    }
                }
            ]
        ];
        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name length must not be greater than 255 characters.'),
                'attribute_id.required' => __('The attribute id is required.'),
            ],
        );
        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        if ($value) {
            $request->mergeIfMissing(['is_active' => 0]);
            $data = $request->except('id', '_token');
            $value->update($data);

            $request->session()->flash('admin_message_success', __('The attribute value has been updated.'));
        }
        else {
            $request->mergeIfMissing(['is_active' => 0]);
            $data = $request->except('id', '_token');

            if (DatingInterestAttributeValue::checkAttributeValueExists($data['name'], $data['attribute_id'])) {
                return response()->json([
                    'status' => false,
                    'messages' => [__('The attribute value is existed')]
                ]);
            }
            DatingInterestAttributeValue::create($data);

            if (!empty($data['add_other_attribute'])) {
                $moreAttributes = !empty($data['more_attribute']) ? $data['more_attribute'] : [];
                foreach ($moreAttributes as $moreAttribute) {
                    $checkTag = DatingInterestAttributeValue::checkAttributeValueExists($data['name'], $moreAttribute);

                    if (!$checkTag) {
                        DatingInterestAttributeValue::create([
                            'is_active' => 1,
                            'name' => $data['name'],
                            'attribute_id' => $moreAttribute
                        ]);
                    }
                }
            }

            $request->session()->flash('admin_message_success', __('The attribute value has been created.'));
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete(Request $request) {
        $id = $request->id;
        $attributeId = $request->attribute_id;

        $attributeValue = DatingInterestAttributeValue::findOrFail($id);

        $attributeValue->delete();

        return redirect()
            ->route('admin.dating.interest_attribute.value', ['attribute_id' => $attributeId])
            ->with([
                'admin_message_success' => __('The attribute value has been deleted.'),
            ]);
    }

    public function store_manage(Request $request) {
        $ids = $request->get('ids');       
        if (! is_array($ids)) {
            abort(404);
        }
        $message = '';
        $action = $request->get('action');
        
        switch ($action) {
            case 'delete':
                foreach ($ids as $id) {
                    $value = DatingInterestAttributeValue::findByField('id', $id);
                    $value->delete();
                }
                $message = __('The selected attribute value(s) have been deleted successfully.');
                break;
            case 'active':
                foreach ($ids as $id) {
                    $value = DatingInterestAttributeValue::findByField('id', $id);
                    $value->update([
                        'is_active' => true
                    ]);
                }
                $message = __('The selected attribute value(s) have been activated successfully.');
                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }
}
