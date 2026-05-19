<?php


namespace Packages\ShaunSocial\Core\Notification\Utility;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class UserDownloadNotification extends BaseNotification
{
    protected $type = 'user_download';
    protected $has_group = false;

    public function getHref()
    {
        return route('web.user.download_copy');
    }

    public function getMessage($count)
    {
        return __('Your data is ready for download.');
    }
}
