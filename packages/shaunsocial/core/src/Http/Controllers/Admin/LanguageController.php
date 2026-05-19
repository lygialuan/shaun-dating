<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Translation;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class LanguageController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.language.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Languages'),
            ],
        ];
        $title = __('Languages');
        $languages = Language::all();

        return view('shaun_core::admin.language.index', compact('breadcrumbs', 'title', 'languages'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $language = new Language();
        } else {
            $language = Language::findOrFail($id);
        }

        $countActive = Language::getCountActive();

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.language.create', compact('title', 'language', 'countActive'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'key' => 'required|string|size:2|unique:Packages\ShaunSocial\Core\Models\Language,key',
        ];
        if (! empty($request->id)) {
            unset($rules['key']);
        }
        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'key.required' => __('The key is required.'),
                'name.size' => __('The name must be 2 characters.'),
                'key.unique' => __('The key already exists.'),
            ]
        );
        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing(['is_rtl' => 0]);
        $request->mergeIfMissing(['is_active' => 0]);
        $request->mergeIfMissing(['is_default' => 0]);
        $data = $request->except('id', '_token');

        $countActive = Language::getCountActive();
        if ($request->id) {
            $language = Language::findOrFail($request->id);
            if ($countActive == 1 && ! $data['is_active']) {
                unset($data['is_active']);
            }

            if ($countActive == 1 && ! $data['is_default']) {
                unset($data['is_default']);
            }

            if ($language->is_default) {
                unset($data['is_default']);
            }

            if (isset($data['is_default']) && $language->is_default != $data['is_default']) {                
                if ($data['is_default']) {
                    Language::query()->update(['is_default' => 0]);
                    $data['is_active'] = true;
                } else {
                    Language::where('id', config('shaun_core.language.en_id'))->update(['is_default' => 1, 'is_active' => 1]);
                }
            }

            unset($data['key']);
            $language->update($data);
        } else {           
            if ($data['is_default']) {
                Language::query()->update(['is_default' => 0]);
            }
            Language::create($data);

            $translations = Translation::where('locale', config('shaun_core.language.system_default'))->get();
            $translationsNew = $translations->map(function ($item, $key) use ($data) {
                // if ($item->table_name == 'languages') {
                //     return null;
                // }
                $item->locale = $data['key'];
                $item->id = 0;

                return $item->toArray();
            })->filter()->toArray();
            Translation::insert($translationsNew);

            //create file json
            $serverPath = getServerLanguagePath($data['key']);
            $clientPath = getClientLanguagePath($data['key']);
            if (! is_file($serverPath)) {
                copy(getServerLanguagePath('install'), $serverPath);
            }

            if (! is_file($clientPath)) {
                copy(getClientLanguagePath('install'), $clientPath);
            }
            
            clearAllCache();
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Language has been successfully updated.') : __('Language has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $language = Language::findOrFail($id);

        if (! $language->canDelete()) {
            abort(400);
        }

        $language->delete();

        return redirect()->route('admin.language.index')->with([
            'admin_message_success' => __('Language has been deleted.'),
        ]);
    }

    public function edit_phrase($id)
    {
        $language = Language::findOrFail($id);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Languages'),
                'route' => 'admin.language.index',
            ],
            [
                'title' => $language->name,
            ],
        ];
        $title = __('Edit phrase');
        $phrases = getLanguageArray($language->key);

        return view('shaun_core::admin.language.edit_phrase', compact('breadcrumbs', 'title', 'language', 'phrases'));
    }

    public function store_phrase(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        $rules = [
            'key' => 'required',
        ];
        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'key.required' => __('Key is required.'),
            ]
        );

        $key = $request->get('key');
        $value = $request->get('value', '');

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $serverPhrases = getServerLanguageArray($language->key);
        if (isset($serverPhrases[$key])) {
            $serverPhrases[$key] = htmlspecialchars($value);
            writeFileLanguageJson(getServerLanguagePath($language->key), $serverPhrases);
        }

        $clientPhrases = getClientLanguageArray($language->key);
        if (isset($clientPhrases[$key])) {
            $clientPhrases[$key] = htmlspecialchars($value);
            writeFileLanguageJson(getClientLanguagePath($language->key), $clientPhrases);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function download_phrase($id, Request $request)
    {
        $language = Language::findOrFail($id);

        $phrases = getLanguageArray($language->key);

        $phrasePath = storage_path('tmp').DIRECTORY_SEPARATOR.'phrase_'.$language->key.'.json';
        
        writeFileLanguageJson($phrasePath, $phrases);

        return response()->download($phrasePath);
    }

    public function upload_phrase($id)
    {
        $language = Language::findOrFail($id);

        return view('shaun_core::admin.language.upload_phrase', compact('language'));
    }

    public function store_upload_phrase($id, Request $request)
    {
        $language = Language::findOrFail($id);

        $rules = [
            'file' => ['required', new FileValidation('json')],
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        
        $content = $request->file->get();
        if (! validateJson($content)) {
            return response()->json([
                'status' => false,
                'messages' => [__('The file is in an incorrect format.')],
            ]);
        }

        $phrases = json_decode($content, true);

        $serverPhrases = getServerLanguageArray($language->key);
        $clientPhrases = getClientLanguageArray($language->key);

        foreach ($phrases as $key => $value) {
            if (isset($serverPhrases[$key])) {
                $serverPhrases[$key] = $value;
                writeFileLanguageJson(getServerLanguagePath($language->key), $serverPhrases);
            }
            if (isset($clientPhrases[$key])) {
                $clientPhrases[$key] = $value;                        
            }
        }

        writeFileLanguageJson(getServerLanguagePath($language->key), $serverPhrases);
        writeFileLanguageJson(getClientLanguagePath($language->key), $clientPhrases);

        clearClientCache();

        $request->session()->flash('admin_message_success', __('Phrase has been successfully updated.'));

        return response()->json([
            'status' => true,
        ]);
    }
}
