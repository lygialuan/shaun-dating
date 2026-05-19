<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Container;

use Packages\ShaunSocial\Core\Models\Page;

class PageDetailContainer extends BaseContainer
{
    public function getData($params = [])
    {
        $request = $this->getRequest();

        $page = Page::findByField('slug', $request->slug);
        $pageLayout = $page->getLayout();

        return [
            'title' => $pageLayout->getTranslatedAttributeValue('title'),
            'content' => $page->getTranslatedAttributeValue('content'),
        ];
    }
}
