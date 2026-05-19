<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Distinct;

trait HasDistinct
{
    public function getDistinctValue($search = false)
    {
        return null;
    }

    public function getLastDistinct()
    {
        return null;
    }

    public static function bootHasDistinct()
    {
        static::created(function ($model) {
            $data = $model->getDistinctValue();
            if (! $data) {
                return ;
            }

            $distinct = Distinct::getDistinct($data);
            if ($distinct) {
                $distinct->update([
                    'updated_at' => now()
                ]);
            } else {
                Distinct::create($data);
            }
        });

        static::deleted(function ($model) {
            $data = $model->getDistinctValue();

            if (! $data) {
                return ;
            }

            $item = $model->getLastDistinct();

            $distinct = Distinct::getDistinct($data);
            if ($item) {
                if ($distinct) {
                    $distinct->update([
                        'updated_at' => $item->created_at
                    ]);
                }                
            } else {
                if ($distinct) {
                    $distinct->delete();
                }
            }
        });
    }
}
