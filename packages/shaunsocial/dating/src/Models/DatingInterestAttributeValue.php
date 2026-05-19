<?php

namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\Translation;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasCacheSearch;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class DatingInterestAttributeValue extends Model
{
    use HasCacheQueryFields, HasCacheSearch, HasTranslations;

    protected $translatable = [
        'name',
    ];

    protected $cacheQueryFields = [
        'id',
        'name',
    ];

    protected $fillable = [
        'name',
        'attribute_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function canUse()
    {
        return $this->is_active;
    }

    protected static function booted()
    {
        parent::booted();

        static::saved(function ($attributeValue) {
            $attributeValue->clearCache();
        });
    }

    public static function getAttributeValuesByIds($ids)
    {
        $strIds = implode('_', $ids);
        return Cache::remember(
            "cache_interest_attribute_values_by_ids_{md5($strIds)}",
            config('shaun_dating.cache.time.1_min'),
            function () use ($ids) {
                return self::whereIn('id', $ids)->get();
            }
        );
    }

    public static function checkAttributeValueExists($name, $attributeId)
    {
        return Cache::remember(
            "cache_interest_check_tag_exists_{$name}_id_{$attributeId}",
            config('shaun_dating.cache.time.1_day'),
            function () use ($name, $attributeId) {
                return self::where('name', $name)
                    ->where('attribute_id', $attributeId)
                    ->exists();
            }
        );
    }

    public function cloneValue($attributeId) {
        $newValue = DatingInterestAttributeValue::create([
            'name' => $this->name,
            'attribute_id' => $attributeId,
            'is_active' => $this->is_active
        ]);
        $languages = Language::getAll();
        foreach ($languages as $language) {
            $oldTranslation = Translation::where('table_name', $this->getTable())
                ->where('column_name', 'name')
                ->where('foreign_key', $this->id)
                ->where('locale', $language->key)
                ->first();

            $translation = Translation::where('table_name', $this->getTable())
                ->where('column_name', 'name')
                ->where('foreign_key', $newValue->id)
                ->where('locale', $language->key)
                ->first();

            if ($translation) {
                $translation->update([
                    'value' => $oldTranslation->value,
                ]);
            }
        }
    }

    public function clearCacheTranslate()
    {
        $this->clearCacheQueryFields();
        $this->clearCache();
    }

    public function clearCache()
    {
        $attribute = DatingAttribute::findByField('id', $this->attribute_id);
        if ($attribute) {
            Cache::forget(DatingAttribute::getCacheAttributeByCategoryIdName($attribute->category_id));
            Cache::forget(DatingAttribute::$cachePermanentKey);
            Cache::forget(DatingAttribute::getCacheAttributeValuesName($this->attribute_id));
        }
    }
}
