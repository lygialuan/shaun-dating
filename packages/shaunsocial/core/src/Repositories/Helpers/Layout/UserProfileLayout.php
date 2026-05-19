<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Models\User;

class UserProfileLayout extends BaseLayout
{
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'user.profile'
        ]);

        $userName = $request->get('user_name', $request->user_name);

        if (!$userName || ! $user = User::findByField('user_name', $userName)) {
            return $this->renderNotFound(__('User is not found'));
        }
        $keywords = '';
        $description = '';
        if ($user->isPage()) {
            $hashtags = $user->getPageHashtags();
            if ($hashtags) {
                $keywords = $hashtags->pluck('name')->join(', ');
            }
            
            $pageInfo = $user->getPageInfo();
            $description = $pageInfo->description;
        } else {
            $description = $user->about;
            $keywords = $user->bio;
        }
        
        return $this->renderData($request, null, [
            'title' => $user->getName(),
            'ogImage' => $user->getAvatar(),
            'keywords' => $keywords,
            'description' => $description
        ]);
    }
}
