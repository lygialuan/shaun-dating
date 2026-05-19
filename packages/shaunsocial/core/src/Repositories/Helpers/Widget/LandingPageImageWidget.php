<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Models\StorageFile;

class LandingPageImageWidget extends BaseWidget
{
    public function getPhotoDefault()
    {
        return asset('images/default/bg_landing.png');
    }

    public function getPhoto($params)
    {
        if (! empty($params['photo_id'])) {
            $storageFile = StorageFile::findByField('id', $params['photo_id']);

            return $storageFile;
        }
    }

    public function getData($request, $params = [])
    {
        $photo = $this->getPhoto($params);
        return [
            'file_url' => $photo ? $photo->getUrl() : $this->getPhotoDefault(),
            'extension' => $photo ? $photo->extension : 'png',
        ];
    }

    public function saveData($contentId, $paramsOld = [], $params = [])
    {
        if (! empty($paramsOld['photo_id'])) {
            if (empty($params['photo_id']) || $params['photo_id'] != $paramsOld['photo_id']) { 
                $storageFile = StorageFile::findByField('id', $paramsOld['photo_id']);

                if ($storageFile) {
                    $storageFile->delete();
                }
            }
        }

        if (! empty($params['photo_id'])) {
            $storageFile = StorageFile::findByField('id', $params['photo_id']);

            if ($storageFile && ! $storageFile->parent_id) {
                $storageFile->update([
                    'parent_id' => $contentId
                ]);
            }
        }
    }
}
