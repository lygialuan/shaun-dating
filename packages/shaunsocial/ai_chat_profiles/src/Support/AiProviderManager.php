<?php

namespace Packages\ShaunSocial\AiChatProfiles\Support;

use Closure;
use Illuminate\Contracts\Container\Container;
use Packages\ShaunSocial\AiChatProfiles\Contracts\AiProviderInterface;
use Packages\ShaunSocial\AiChatProfiles\Exceptions\AiProviderException;
use Packages\ShaunSocial\AiChatProfiles\Providers\AiProviderKeyBridge;
use Packages\ShaunSocial\AiChatProfiles\Providers\NullProvider;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;

class AiProviderManager
{
    /** @var array<string, AiProviderInterface> */
    protected array $resolved = [];

    /** @var array<string, Closure(Container, array<string, mixed>): AiProviderInterface> */
    protected array $customCreators = [];

    public function __construct(
        protected Container $container,
    ) {
    }

    public function driver(?string $name = null): AiProviderInterface
    {
        if ($name !== null) {
            return $this->resolved[$name] ??= $this->resolve($name);
        }

        return $this->driverFromSetting();
    }

    /**
     * @param  Closure(Container, array<string, mixed>): AiProviderInterface  $creator
     */
    public function extend(string $name, Closure $creator): void
    {
        $this->customCreators[$name] = $creator;
        unset($this->resolved[$name]);
    }

    public function driverFromKey(AiProviderKey $key): AiProviderInterface
    {
        return new AiProviderKeyBridge($key, $key->provider->getProviderInstance());
    }

    public function driverFromSetting(): AiProviderInterface
    {
        $keyId = (int) setting('ai_chat_profiles.chat_provider_key_id', 0);

        if ($keyId > 0) {
            $key = AiProviderKey::with('provider')->find($keyId);

            if ($key && $key->isUsable()) {
                return $this->driverFromKey($key);
            }
        }

        return new NullProvider;
    }

    protected function resolve(string $name): AiProviderInterface
    {
        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($this->container, []);
        }

        $method = 'create'.str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name))).'Provider';

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw AiProviderException::notConfigured($name, "AI provider [{$name}] is not registered.");
    }

    protected function createNullProvider(): AiProviderInterface
    {
        return new NullProvider;
    }
}
