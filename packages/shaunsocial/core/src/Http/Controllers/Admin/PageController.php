<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Page;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Traits\Utility;

class PageController extends Controller
{
    use Utility;

    public function __construct()
    {
        $this->middleware('has.permission:admin.page.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Pages'),
            ],
        ];
        $title = __('Pages');
        $pages = Page::paginate(setting('feature.item_per_page'));

        return view('shaun_core::admin.page.index', compact('breadcrumbs', 'title', 'pages'));
    }

    public function create($id = null, $language = null)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Pages'),
                'route' => 'admin.page.index',
            ],
        ];

        $languages = Language::getAll();
        $roles = Role::all();
        $layoutPage = null;

        if (empty($id)) {
            $page = new Page();
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $page = Page::findOrFail($id);
            $layoutPage = $page->getLayout();

            if (! $language) {
                $language = config('shaun_core.language.system_default');
            }

            if (! in_array($language, $languages->pluck('key')->all())) {
                abort(404);
            }
            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }
        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.page.create', compact('title', 'languages', 'language', 'page', 'breadcrumbs', 'roles', 'layoutPage'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'title' => 'required|max:255',
                'slug' => 'required|max:255|alpha_dash|unique:Packages\ShaunSocial\Core\Models\Page,slug,'.$request->id,
                'meta_keywords' => 'max:255',
                'meta_description' => 'max:255'
            ],
            [
                'title.required' => __('The title is required.'),
                'title.max' => __('The title must not be greater than 255 characters.'),
                'slug.required' => __('The slug is required.'),
                'slug.max' => __('The slug must not be greater than 255 characters.'),
                'slug.unique' => __('The slug already exists.'),
                'slug.alpha_dash' => __('The slug must only contain letters, numbers, dashes and underscores.'),
                'meta_keywords.max' => __('The meta keywords must not be greater than 255 characters.'),
                'meta_description.max' => __('The meta description must not be greater than 255 characters.'),
            ]
        );
        $request->mergeIfMissing([
            'role_access' => [],
        ]);

        $data = $request->except('id', '_token');
        $data['role_access'] = json_encode($data['role_access']);

        if ($request->id) {
            $page = Page::findOrFail($request->id);
            $page->update($data);

            $page->updateTranslations($data['language']);
            $layoutPage = $page->getLayout();
            $layoutPage->update($data);
            $layoutPage->updateTranslations($data['language']);
        } else {
            $page = Page::create($data);
            $data['type'] = 'page';
            $data['page_id'] = $page->id;
            $this->createLayoutPage($data, 
                [
                    'center' => [
                        [
                            'view_type' => 'desktop',
                            'type' => 'container',
                            'component' => 'PageDetail',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'class' => 'Packages\ShaunSocial\Core\Repositories\Helpers\Container\PageDetailContainer'
                        ],
                        [
                            'view_type' => 'mobile',
                            'type' => 'container',
                            'component' => 'PageDetail',
                            'title' => 'Container',
                            'enable_title' => false,
                            'role_access' => '["all"]',
                            'order' => 1,
                            'class' => 'Packages\ShaunSocial\Core\Repositories\Helpers\Container\PageDetailContainer'
                        ]
                    ]
                ]
            );
        }

        return redirect()->route('admin.page.index')->with([
            'admin_message_success' => $request->id ? __('Page has been successfully updated.') : __('Page has been created.'),
        ]);
    }

    public function delete($id)
    {
        $page = Page::findOrFail($id);

        $page->delete();

        return redirect()->route('admin.page.index')->with([
            'admin_message_success' => __('Page has been deleted.'),
        ]);
    }
}
