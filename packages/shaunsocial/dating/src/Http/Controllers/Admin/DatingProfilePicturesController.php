<?php

namespace Packages\ShaunSocial\Dating\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\PhotoVerifyItem;
use Packages\ShaunSocial\Dating\Notification\DatingAdminUpdateProfileNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;

class DatingProfilePicturesController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:dating.manage_profile_pictures');
    }

    public function index(Request $request)
    {
        $title = __("Manage Profile Pictures");

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => $title
            ]
        ];

        $builder = User::query()->where('has_reviewed_photos', false)->whereHas('photoVerifyItems', function ($q) {$q->where('status', 'pending');})->orderBy('photos_uploaded_at', 'desc');

        $users = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_dating::admin.profile_pictures.index', compact('breadcrumbs', 'title', 'users'));
    }

    public function view_detail(Request $request)
    {
        $id = $request->id;
        
        $user = User::findOrFail($request->id);

        if ($user->has_reviewed_photos) {
            abort(400);
        }

        $title = __('View detail'). " ($user->name)";

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Manage Profile Pictures'),
                'route' => 'admin.dating.profile_pictures',
            ],
            [
                'title' => $title
            ]
        ];

        return view( 'shaun_dating::admin.profile_pictures.view_detail', compact('breadcrumbs', 'title', 'user'));
    }

    public function store(Request $request)
    {
        $user = User::findOrFail($request->id);

        $photos = $request->input('photos', []);

        foreach ($photos as $photoId => $status) {
            $photo = PhotoVerifyItem::find($photoId);

            if ($photo) {
                $photo->status = $status;
                $photo->save();
            }
        }

        $user->update([
            'has_reviewed_photos' => true
        ]);

        Notification::send($user, $user, DatingAdminUpdateProfileNotification::class, '', ['is_system' => true], 'shaun_dating', false);

        return redirect()->route('admin.dating.profile_pictures')->with([
            'admin_message_success' => __('Photos updated successfully.'),
        ]);
    }
}
