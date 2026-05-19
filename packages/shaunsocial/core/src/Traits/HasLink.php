<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\LinkIcon;

trait HasLink
{
    public function getLinkWithField($field)
    {
        $value = $this->{$field};
        if (! $value) {
            return [];
        }
        $links = [];
        if (validateJson($value)) {
            $links = json_decode($value, true);
            $tmp = [];
            foreach ($links as $link) {
                $tmp[] = [
                    'title' => ! empty($link['title']) ? $link['title'] : '',
                    'link' => $link['link']
                ];
            }
            $links = $tmp;
        } else {
            $links = explode(' ', $value);
            $tmp = [];
            foreach ($links as $link) {
                $tmp[] = [
                    'title' => '',
                    'link' => $link
                ];
            }

            $links = $tmp;
        }

        $icons = LinkIcon::getAll();

        foreach ($links as &$link) {
            $linkTmp = $link['link'];
            $linkTmp = str_replace('https://','', $linkTmp);
            $linkTmp = str_replace('http://','', $linkTmp);

            $icon = $icons->first(function ($value, $key) use ($linkTmp) {
                return strpos($linkTmp, $value->domain) === 0;
            });

            if ($icon) {
                $link['icon'] = $icon->getIcon();
            } else {
                $link['icon'] = asset('images/default/link_icon/website.svg');
            }
        }

        return $links;
    }
}
