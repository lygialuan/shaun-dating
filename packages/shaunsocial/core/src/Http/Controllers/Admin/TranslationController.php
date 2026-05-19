<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Translation;

class TranslationController extends Controller
{
    public function edit_model($model, $column, $id, $empty = null)
    {
        $languages = Language::getAll()->pluck('name', 'key');
        $values = Translation::where('table_name', $model)->where('column_name', $column)->where('foreign_key', $id)->get()->keyBy('locale');

        return view('shaun_core::admin.translation.edit_model', compact('model', 'column', 'id', 'values', 'languages', 'empty'));
    }

    public function store_model(Request $request)
    {
        $request->validate(
            [
                'model' => 'required',
                'column' => 'required',
                'id' => 'required',
                'language' => 'required',
            ],
        );

        //validate
        if (! $request->input('empty')) {
            foreach ($request->language as $key => $value) {
                if (! $value) {
                    return response()->json([
                        'status' => false,
                        'messages' => [
                            __('The field is required.')
                        ],
                    ]);
                }
            }
        }

        foreach ($request->language as $key => $value) {
            $translation = Translation::where('table_name', $request->model)->where('column_name', $request->column)->where('foreign_key', $request->id)->where('locale', $key)->first();
            if ($translation) {
                $translation->update([
                    'value' => $value,
                ]);
            }
        }

        $subject = findByTypeId($request->model, $request->id);
        if ($subject && method_exists($subject, 'clearCacheTranslate')) {
            $subject->clearCacheTranslate();
        }

        $request->session()->flash('admin_message_success', __('Translation has been successfully updated.'));

        return response()->json([
            'status' => true,
        ]);
    }
}
