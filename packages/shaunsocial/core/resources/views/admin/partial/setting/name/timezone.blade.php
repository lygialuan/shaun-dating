<?php $options = getTimezoneList()?>
<div class="control-field">
    <select id="{{ $setting->key }}" name="{{ $setting->key }}" class="input form-select setting_select">
        @if(!empty($options))
            @foreach($options as $index => $option)
                <option value="{{ $index }}" @if($setting->value == $index) selected="selected" @endif>{{ __($option) }}</option>
            @endforeach
        @endif
    </select>
</div>