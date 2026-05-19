@foreach($settings as $setting)
    <div class="form-group" id="setting_id_{{$setting->id}}">
        <?php 
            $groupSub = $setting->getGroupSub();
            $includeTitle = $groupSub->package.'::admin.partial.setting.title.'.$setting->key;
        ?>
        <label class="control-label control-label-{{ __($setting->type) }}">
            @if ($setting->name)
                {{ __($setting->name) }}
            @endif
            @includeIf($includeTitle)
        </label>
        <div class="control-field">
            @include('shaun_core::admin.partial.setting.field')
        </div>
        @if ($setting->description)
            <p class="control-help control-help-{{ __($setting->type) }}">
                {!! __($setting->description) !!}
            </p>
        @endif
        <?php
            $includeDescription = $groupSub->package.'::admin.partial.setting.description.'.$setting->key;            
        ?>
        @includeIf($includeDescription)
    </div>
@endforeach