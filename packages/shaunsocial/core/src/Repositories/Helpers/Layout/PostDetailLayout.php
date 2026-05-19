<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Layout;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\Utility;
use Illuminate\Support\Str;

class PostDetailLayout extends BaseLayout
{
    use Utility;
    
    public function render(Request $request)
    {
        $request->merge([
            'router' => 'post.detail'
        ]);

        $id = $request->get('id', $request->id);

        if (!$id || ! $post = Post::findByField('id', $id)) {
            return $this->renderNotFound(__('Post is not found'));
        }
        
        $data = [
            'title' => $post->getUser()->getName()
        ];
        if ($post->content) {
            $data['description'] = $this->makeContentHtml($post->getMentionContent(null));
            $data['title'] = Str::words($data['description'],15,'');
        }

        $ogImage = $post->getOgImage();
        if ($ogImage) {
            $data['ogImage'] = $ogImage;
        }

        return $this->renderData($request, null, $data);
    }
}
