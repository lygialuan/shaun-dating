<?php

namespace Packages\ShaunSocial\AiProvider\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\ShaunSocial\AiProvider\Providers\AiProviderInterface;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class AiProvider extends Model
{
    use HasCacheQueryFields;
    use HasTranslations;

    /**
     * Columns that can be used for cached query lookups.
     *
     * @var array<int, string>
     */
    protected array $cacheQueryFields = [
        'class',
        'id',
    ];

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'class',
    ];

    /**
     * Attributes that support translation.
     *
     * @var array<int, string>
     */
    protected array $translatable = [
        'description',
    ];

    /**
     * Attribute casting definitions.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Retrieve service class instance for this provider.
     */
    public function getClass(): mixed
    {
        return app($this->class);
    }

    /**
     * Resolve provider interface implementation.
     */
    public function getProviderInstance(): AiProviderInterface
    {
        $instance = $this->getClass();

        if (! $instance instanceof AiProviderInterface) {
            throw new \RuntimeException(sprintf('Class %s must implement %s.', $this->class, AiProviderInterface::class));
        }

        return $instance;
    }

    /**
     * Helper to retrieve provider key/slug for view resolution.
     */
    public function getProviderKey(): string
    {
        return $this->getProviderInstance()->getKey();
    }

    /**
     * Accessor for default configuration key fallback.
     *
     * @return array<string, mixed>
     */
    public function getDefaultConfig(): array
    {
        return $this->getProviderInstance()->getDefaultConfig();
    }

    /**
     * Relation: provider has many keys.
     */
    public function keys(): HasMany
    {
        return $this->hasMany(AiProviderKey::class);
    }
}
