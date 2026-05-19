<?php


namespace Packages\ShaunSocial\Core\Models\Sanctum;

use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\UserPage\Models\UserPageToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use Prunable;

    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            return Cache::remember(self::getCacheKey(hash('sha256', $token)), config('shaun_core.cache.time.model_query'), function () use ($token) {
                $accessToken = static::where('token', hash('sha256', $token))->first();
    
                return is_null($accessToken) ? false : $accessToken;
            });
        }

        [$id, $token] = explode('|', $token, 2);
        $instance = Cache::remember(self::getCacheKey($id), config('shaun_core.cache.time.model_query'), function () use ($id) {
            $accessToken = static::find($id);
        
            return is_null($accessToken) ? false : $accessToken;
        });

        return $instance && hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
    }

    public function getTokenableAttribute()
    {
        $model = app($this->tokenable_type);

        return $model::findByField('id', $this->tokenable_id);
    }

    protected static function booted()
    {
        parent::booted();

        static::updating(function (self $personalAccessToken) {
            Cache::remember('personal_access_token_last_update_'.$personalAccessToken->id, config('shaun_core.cache.time.access_token_update'), function () use ($personalAccessToken) {
                DB::table($personalAccessToken->getTable())
                ->where('id', $personalAccessToken->id)
                ->update($personalAccessToken->getDirty());

                return now();
            });

            return false;
        });

        static::deleting(function ($personalAccessToken) {
            Cache::forget(self::getCacheKey($personalAccessToken->token));
            Cache::forget(self::getCacheKey($personalAccessToken->id));
            
            //update active user when token delete
            $item = $personalAccessToken->getTokenableAttribute();
            if ($item && $item instanceof User) {
                if ($item->isPage()) {
                    $pageToken = UserPageToken::findByField('token', $personalAccessToken->token);
                    if ($pageToken) {
                        $pageToken->delete();
                    }
                }
                $token = static::where('tokenable_id', $item->id)->where('tokenable_type', $personalAccessToken->tokenable_type)->where('id', '!=', $personalAccessToken->id)->first();
                if (! $token) {
                    $item->update([
                        'has_active' => false
                    ]);
                }
            }
        });

        static::creating(function ($personalAccessToken) {
            $personalAccessToken->last_used_at = now();
            if ($personalAccessToken->tokenable_type == 'Packages\ShaunSocial\Core\Models\User') {
                $user = User::findByField('id', $personalAccessToken->tokenable_id);
                if ($user) {
                    $personalAccessToken->is_page = $user->isPage();
                }
            }
        });

        static::created(function ($personalAccessToken) {
            $item = $personalAccessToken->getTokenableAttribute();
            Cache::forget(self::getCacheKey($personalAccessToken->token));
            Cache::forget(self::getCacheKey($personalAccessToken->id));
            if ($item && $item instanceof User && !$item->has_active) {
                $item->update([
                    'has_active' => true
                ]);
            }
        });
    }

    public static function getCacheKey($token)
    {
        return 'personal_access_token_'.md5($token);
    }

    public function prunable()
    {
        return self::where('last_used_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'))->distinct('tokenable_id');
    }
}
