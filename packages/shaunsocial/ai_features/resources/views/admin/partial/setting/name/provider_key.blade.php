<?php

use Packages\ShaunSocial\AiProvider\Models\AiProviderKey;

$params = $params ?? [];
$selectedValue = (int) ($setting->value ?? 0);

$activeKeys = AiProviderKey::with('provider')
    ->where('is_active', true)
    ->where('status', 'healthy')
    ->orderBy('ai_provider_id')
    ->orderBy('name')
    ->get();

$selectedKey = $selectedValue
    ? AiProviderKey::with('provider')->find($selectedValue)
    : null;

$isValidSelection = $selectedValue === 0 || ($selectedKey && $selectedKey->is_active && $selectedKey->status === 'healthy');
$emptyLabel = $params['empty_label'] ?? __('No provider selected');
$description = $params['description'] ?? null;
$showManageLink = $params['show_manage_link'] ?? true;
$manageLabel = $params['manage_label'] ?? __('Manage AI Providers');
$manageUrl = $params['manage_url'] ?? route('admin.ai_provider.index');

$groupedKeys = $activeKeys->groupBy(function ($keyItem) {
    return $keyItem->provider?->name ?? __('Unknown Provider');
});
?>
<div class="control-field">
    <select id="{{ $setting->key }}" name="{{ $setting->key }}" class="form-select setting_select">
        <option value="0" {{ $selectedValue === 0 ? 'selected' : '' }}>{{ $emptyLabel }}</option>
        @foreach ($groupedKeys as $providerName => $keys)
            <optgroup label="{{ $providerName }}">
                @foreach ($keys as $keyItem)
                    <option value="{{ $keyItem->id }}" {{ $selectedValue === $keyItem->id ? 'selected' : '' }}>
                        {{ $keyItem->name }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
        @if ($selectedValue !== 0 && $selectedKey && (! $selectedKey->is_active || $selectedKey->status !== 'healthy'))
            <option value="{{ $selectedKey->id }}" selected>
                {{ $selectedKey->provider?->name ?? __('Unknown Provider') }} — {{ $selectedKey->name }} ({{ __('Inactive') }})
            </option>
        @elseif ($selectedValue !== 0 && ! $selectedKey)
            <option value="{{ $selectedValue }}" selected>
                {{ __('Unknown Provider') }}
            </option>
        @endif
    </select>

    @if ($description)
        <p class="control-help control-help-select">
            {!! __($description) !!}
        </p>
    @endif

    @if ($selectedValue !== 0 && ! $isValidSelection)
        <p class="control-help text-danger">
            {{ __("This api key is invalid or can't be used. Please check and reconfigure.") }}
        </p>
    @endif

    @if ($showManageLink && $manageUrl)
        <p class="control-help mt-2">
            <a href="{{ $manageUrl }}">{{ __($manageLabel) }}</a>
        </p>
    @endif
</div>
