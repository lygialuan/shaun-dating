<?php 
    $options = [
        '1' => "1 photo",
        '2' => "2 photos",
        '3' => "3 photos",
        '4' => "4 photos",
        '5' => "5 photos",
        '6' => "6 photos",
        '7' => "7 photos",
        '8' => "8 photos"
    ]
?>
<div class="control-field">
    <select id="{{ $setting->key }}" name="{{ $setting->key }}" class="input form-select setting_select">
        @if(!empty($options))
            @foreach($options as $index => $option)
                <option value="{{ $index }}" @if($setting->value == $index) selected="selected" @endif>{{ __($option) }}</option>
            @endforeach
        @endif
    </select>
</div>