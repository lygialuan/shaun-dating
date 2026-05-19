@if ($setting->type == "text")
    <input type="text" id="{{ $setting->key }}" class="input form-control setting_input_text" name="{{ $setting->key }}" value="{{ $setting->value }}">
@elseif ($setting->type == "number")
    <input type="number" id="{{ $setting->key }}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" step="1" class="input form-control setting_input_text" name="{{ $setting->key }}" value="{{ $setting->value }}" min="0">
@elseif ($setting->type == "amount")
    <input type="number" id="{{ $setting->key }}" inputmode="decimal" step="0.01" class="input form-control setting_input_text" name="{{ $setting->key }}" value="{{ $setting->value }}">
@elseif($setting->type == "password")
    <input type="password" readonly="readonly" onfocus="this.removeAttribute('readonly');" id="{{ $setting->key }}" class="input form-control setting_input_text allow_readonly" name="{{ $setting->key }}" value="{{ $setting->value }}">
@elseif($setting->type == "textarea")
    <textarea id="{{ $setting->key }}" class="form-control setting_textarea" name="{{ $setting->key }}" rows="4">{{ $setting->value ?? '' }}</textarea>
@elseif($setting->type == "image")
    <?php 
        $params = $setting->getParams();
        $styleWidth = isset($params['style_width']) ? $params['style_width'] : '';
        $class = isset($params['class']) ? $params['class'] : '';
    ?>
    @if($setting->value)
        <div class="img_settings_container mb-3">
            <div class="img_settings_container_wrapper mb-2">
                <img width="{{$styleWidth}}" class="img-fluid {{$class}}" src="{{ $setting->getFile('value')->getUrl() }}">
            </div>
            <a class="btn-filled-red admin_modal_confirm_delete" data-url="{{route('admin.setting.delete_image',$setting->id)}}" href="javascript:void(0);">
                {{__('Delete')}}
            </a>
        </div>
    @elseif(isset($params['default']))
        <div class="img_settings_container mb-3">            
            <img width="{{$styleWidth}}" class="img-fluid {{$class}}" src="{{asset($params['default'])}}">
        </div>
    @endif
    <input type="file" id="{{ $setting->key }}" name="{{ $setting->key }}" class="form-control setting_photo">
@elseif($setting->type == "select")
    <?php $options = $setting->getParams();?>
    <?php $selected_value = (isset($setting->value) && !empty($setting->value)) ? $setting->value : NULL; ?>
    <select id="{{ $setting->key }}" name="{{ $setting->key }}" class="form-select setting_select">
        @if(!empty($options))
            @foreach($options as $index => $option)
                <option value="{{ $index }}" @if($selected_value == $index) selected="selected" @endif>{{ __($option) }}</option>
            @endforeach
        @endif
    </select>
@elseif($setting->type == "radio")
    <?php $options = $setting->getParams();?>
    <?php $selected_value = (isset($setting->value) && !empty($setting->value)) ? $setting->value : NULL; ?>
    @if(!empty($options))
        @foreach($options as $index => $option)
            <div class="form-check">
                <input class="form-check-input setting_radio" type="radio" name="{{ $setting->key }}" id="option-{{ $index }}" value="{{ $index }}" @if($index == key($options) && $selected_value === NULL) checked @endif @if($selected_value == $index) checked @endif>
                <label class="form-check-label" for="option-{{ $index }}">
                    {{ __($option) }}
                </label>
            </div>
        @endforeach
    @endif
@elseif($setting->type == "checkbox")
    <?php $checked = (isset($setting->value) && $setting->value == 1) ? true : false; ?>
    <div class="form-check form-switch form-switch-lg form-switch-center">
        <input name="{{$setting->key}}" type="checkbox" {{ ($checked) ? 'checked' : ''}} value="1" id="{{$setting->key}}" class="form-check-input setting_checkbox">
    </div>
@elseif($setting->type == "blade")
    <?php $params = $setting->getParams();?>
    <?php $path = $params['path']?>
    @includeIf($path,['params'=> $params,'setting' => $setting])
@elseif ($setting->type == "color")
    <input type="text" id="{{ $setting->key }}" class="input form-control setting_input_color" name="{{ $setting->key }}" value="{{ $setting->value }}">
@endif

