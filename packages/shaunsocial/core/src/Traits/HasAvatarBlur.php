<?php

namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\StorageFile;

trait HasAvatarBlur
{
    public function getAvatarBlurDefault()
    {
        return property_exists($this, 'avatarDefault') ? $this->avatarDefault : 'images/default/avatar_user_blur.png';
    }

    public function initializeHasAvatarBlur()
    {
        $this->fillable[] = 'blur_avatar_file_id';
    }

    public function getAvatarBlur($thumb = '')
    {
        $defaultAvatar = $this->getAvatarBlurDefault();
        if (! $this->blur_avatar_file_id) {
            return asset($defaultAvatar);
        }

        $storageFile = StorageFile::findByField('id', $this->blur_avatar_file_id);

        if (! $storageFile) {
            return asset($defaultAvatar);
        }

        if (! $thumb) {
            return $storageFile->getUrl();
        }

        return $storageFile->getChildUrl($thumb);
    }
}
