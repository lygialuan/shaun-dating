<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\LayoutPage;
use Packages\ShaunSocial\Core\Models\LayoutBlock;
use Packages\ShaunSocial\Core\Models\LayoutContent;
use Packages\ShaunSocial\Core\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;

class LayoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.layout.manage');
    }

    public function index($id = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' =>  'admin.dashboard.index'
            ],
            [
                'title' => __('Layout Editor')
            ]
        ];
        $title = __('Layout Editor');
        $pages = LayoutPage::all();
        if (!$id) {
            $id = config('shaun_core.layout.default_id');
        }

        $viewTypes = LayoutContent::getViewTypes();

        $hasOneColumn = false;
        if ($id == config('shaun_core.layout.header_footer_id')) {
            $hasOneColumn = true;
            $viewTypes = ['header' => __('Header'), 'footer' => __('Footer')];
        }

        $builder = LayoutBlock::where('enable',1);
        if ($hasOneColumn) {
            $builder->where('support_header_footer', 1);
        }
        $blocks = $builder->get();

        $page = LayoutPage::findOrFail($id);
        foreach ($viewTypes as $key => $value) {
            $contents[$key] = $page->getContents($key);
        }
        
        return view('shaun_core::admin.layout.index', compact('breadcrumbs', 'title', 'pages', 'blocks', 'pages', 'page', 'id', 'contents', 'viewTypes', 'hasOneColumn'));
    }

    public function edit_block($component = null, $id = null)
    {
        $content = null;
        $block = null;

        if ($id) {
            $content = LayoutContent::findOrFail($id);
            $block = LayoutBlock::where('component',$content->component)->first();
        } else {
            $block = LayoutBlock::where('component',$component)->first();            
        }

        if (!$block) {
            abort(404);
        }

        $roles = Role::all();

        return view('shaun_core::admin.layout.edit_block', compact('content', 'block', 'roles'));
    }

    public function store_blocks(Request $request)
    {
        $data = $request->all();

        $page = LayoutPage::findOrFail($data['page_id']);
        
        $contentRemoveIds = array_filter(explode(',',$data['block_remove_ids']));
        if (count($contentRemoveIds)) {
            LayoutContent::whereIn('id', $contentRemoveIds)->get()->each(function($content) {
                $content->delete();
            });
        }
        if (!empty($data['blocks'])) {
            foreach ($data['blocks'] as $viewType => $blocks) {
                if ($viewType == 'all') {
                    $viewType = '';
                }
                $order = 1;
                foreach ($blocks as $block) {
                    if ($block['id']) {
                        $content = LayoutContent::findOrFail($block['id']);
                        $paramsOld = $content->getParams();
                        $content->update([
                            'position' => $block['position'],
                            'enable_title' => $block['enable_title'] ? $block['enable_title'] : false,
                            'role_access' => $block['role_access'],
                            'params' => $block['params'],
                            'order' => $order
                        ]);

                        if ($content->class) {
                            $class = app($content->class);
                            $class->saveData($content->id, $paramsOld, $block['params'] ? json_decode($block['params'],true) : []);
                        }
                    } else {
                        $content = LayoutContent::create([
                            'page_id' => $page->id,
                            'type' => 'component',
                            'component' => $block['component'],
                            'view_type' => $viewType,
                            'title' => $block['title'],
                            'content' => $block['content'],
                            'position' => $block['position'],
                            'enable_title' => $block['enable_title'] ? $block['enable_title'] : false,
                            'role_access' => $block['role_access'],
                            'params' => $block['params'],
                            'class' => $block['class'],
                            'package' => $block['package'],
                            'order' => $order,
                        ]);

                        if ($content->class) {
                            $class = app($content->class);
                            $class->saveData($content->id, [], $block['params'] ? json_decode($block['params'],true) : []);
                        }
                    }
            
                    $order++;
                }
                
                Cache::forget('layout_page_contents_'.$page->id.'_'.$viewType);
            }
        }

        $request->session()->flash('admin_message_success', __('Layout has been successfully updated.'));

        return response()->json([
            'status' => true
        ]);
    }

    public function edit($id) 
    {
        $page = LayoutPage::findOrFail($id);
        
        return view('shaun_core::admin.layout.edit', compact('page'));
    }

    public function store(Request $request)
    {
        $page = LayoutPage::findOrFail($request->id);
        $data = $request->except('id', '_token');

        $page->update($data);

        $request->session()->flash('admin_message_success', __('Page info has been successfully updated.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function get_data_block(Request $request)
    {
        $result = [];
        $component = $request->post('component');
        if ($component) {
            $block = LayoutBlock::where('component', $component)->first();
            if ($block && $block->class) {
                $class = app($block->class);
                $result = $class->getData($request, $request->all());
            }
        }

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function upload_file_block(Request $request)
    {
        $extensions = $request->post('extension');
        $fileValidate = new PhotoValidation;
        if ($extensions) {
            $fileValidate = new FileValidation($extensions);
        }

        $rules = [
            'file' => ['required', $fileValidate],
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

        $file = $request->file('file');

        $storageFile = File::store($file, [
            'parent_type' => 'layout_content',
            'extension' => $file->getClientOriginalExtension(),
            'name' => $file->getClientOriginalName()
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'file_id' => $storageFile->id,
                'file_url' => $storageFile->getUrl(),
                'extension' => $storageFile->extension
            ],
        ]);
    }
}
