<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Models\PhotoVerifyItem;

trait HasAvatar
{
    public function getAvatarDefault()
    {
        return property_exists($this, 'avatarDefault') ? $this->avatarDefault : 'images/default/avatar.png';
    }

    public function initializeHasAvatar()
    {
        $this->fillable[] = 'avatar_file_id';
    }

    public function getAvatar($thumb = '')
    {
        if ($this->fake_user) {
            $avatarFake = $this->getFakePhotoByUserId();
            if($avatarFake){
                return asset('images/default/photo/'. $avatarFake);
            }
        }

        $defaultAvatar = $this->getAvatarDefault();
        if (! $this->avatar_file_id) {
            return asset($defaultAvatar);
        }

        $storageFile = StorageFile::findByField('id', $this->avatar_file_id);
        $getAvatar = PhotoVerifyItem::getAvatar($this->id);

        if (! $storageFile || !$getAvatar) {
            return asset($defaultAvatar);
        }

        if (! $thumb) {
            return $storageFile->getUrl();
        }

        return $storageFile->getChildUrl($thumb);
    }
}
