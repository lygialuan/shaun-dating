<?php


namespace Packages\ShaunSocial\AiProvider\Repositories\Helpers;

use Packages\ShaunSocial\AiProvider\Models\AiProvider;
use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;
use Packages\ShaunSocial\Core\Traits\Utility;

class Package
{
    use Utility;

    public function install()
    {
        //translate
        $aiProviders = AiProvider::all();
        $aiProviders->each(function($provider) {
            $provider->createTranslations();
        });
        $aiProviderKeys = AiProviderKey::all();
        $aiProviderKeys->each(function($key) {
            $key->createTranslations();
        });

    }
}
