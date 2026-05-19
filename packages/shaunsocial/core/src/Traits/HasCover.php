<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\StorageFile;

trait HasCover
{
    public function getCoverDefault()
    {
        return property_exists($this, 'coverDefault') ? $this->coverDefault : 'images/default/cover.png';
    }

    public function initializeHasCover()
    {
        $this->fillable[] = 'cover_file_id';
    }

    public function getCover($thumb = '')
    {
        $defaultCover = $this->getCoverDefault();
        if (! $this->cover_file_id) {
            return asset($defaultCover);
        }

        $storageFile = StorageFile::findByField('id', $this->cover_file_id);

        if (! $storageFile) {
            return asset($defaultCover);
        }

        if (! $thumb) {
            return $storageFile->getUrl();
        }

        return $storageFile->getChildUrl($thumb);
    }
}
