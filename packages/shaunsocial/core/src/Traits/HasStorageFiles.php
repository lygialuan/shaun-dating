<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\StorageFile;

trait HasStorageFiles
{
    protected $fileFields = [];

    public static function bootHasStorageFiles()
    {
        static::updating(function ($model) {
            $storageFields = $model->getStorageFieldsAttributes();

            foreach ($storageFields as $field) {
                if ($model->{$field} != $model->getOriginal($field) && $model->getOriginal($field)) {
                    $id = $model->getOriginal($field);
                    StorageFile::find($id)->delete();
                }
            }
        });

        static::deleted(function ($model) {
            $model->deleteStorage();
        });
    }

    public function getStorageFieldsAttributes()
    {
        $storageFields = property_exists($this, 'storageFields') ? $this->storageFields : [];
        $result = [];

        foreach ($storageFields as $key => $field) {
            if (is_string($field)) {
                $result[] = $field;
            } elseif (is_array($field)) {
                $check = true;
                foreach ($field as $name => $value) {
                    if (is_string($value) && $this->{$name} != $value) {
                        $check = false;
                        break;
                    }

                    if (is_array($value) && ! in_array($this->{$name}, $value)) {
                        $check = false;
                        break;
                    }
                }

                if ($check) {
                    $result[] = $key;
                }
            }
        }

        return $result;
    }

    public function getStorageTypesAttributes()
    {
        return property_exists($this, 'storageTypes') ? $this->storageTypes : [];
    }

    public function deleteStorage()
    {
        $storageTypes = $this->getStorageTypesAttributes();
        if (count($storageTypes)) {
            StorageFile::whereIn('parent_type', $storageTypes)->where('parent_id', $this->id)->get()->each(function ($file) {
                $file->delete();
            });
        }

        $storageFields = $this->getStorageFieldsAttributes();
        if (count($storageFields)) {
            $ids = [];
            foreach ($storageFields as $field) {
                if ($this->{$field}) {
                    $ids[] = $this->{$field};
                }
            }
            if ($ids) {
                StorageFile::whereIn('id', $ids)->get()->each(function ($file) {
                    $file->delete();
                });
            }
        }
    }

    public function getFile($field)
    {
        if (! $this->{$field}) {
            return null;
        }

        if (! isset($this->fileFields[$field])) {
            $this->fileFields[$field] = StorageFile::findByField('id', $this->{$field});
        }

        return $this->fileFields[$field];
    }

    public function setFile($field, $file)
    {
        $this->fileFields[$field] = $file;
    }

    public function getFiles($type)
    {
        $files = StorageFile::getByParentType($type, $this->id);
        if ($files) {
            $files = $files->sortBy(function ($file) {
                return $file->order;
            });
        }

        return $files;
    }
}
