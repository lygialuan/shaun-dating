<div class="form-group" data-is_support_guest="{{$permission->is_support_guest}}" data-is_support_admin="{{$permission->is_support_admin}}">
    <label class="control-label control-label-{{ __($permission->type) }}">{{__($permission->name)}}</label>
    @if ($permission->type == "text")
        <input class="form-control" name="{{$permission->id}}" value="{{old('_token') ? old($permission->id) : (isset($permissionValues[$permission->id]) ? $permissionValues[$permission->id]->value : '') }}" type="text">
    @elseif ($permission->type == "number")
        <input class="form-control" name="{{ $permission->id }}" value="{{old('_token') ? old($permission->id) : (isset($permissionValues[$permission->id]) ? $permissionValues[$permission->id]->value : '') }}" onkeypress="return event.charCode >= 48 && event.charCode <= 57" step="1" min="0" type="number" >
    @elseif($permission->type == "checkbox")
        <div class="form-check form-switch form-switch-lg form-switch-center">
            <input name="{{$permission->id}}" type="checkbox" {{ (old('_token') ? old($permission->id) : isset($permissionValues[$permission->id])) && $permissionValues[$permission->id]->value ? 'checked' : ''}} value="1" class="form-check-input">
        </div>
    @endif
    <p class="control-help control-help-{{ __($permission->type) }}">
        {{$permission->description}}
    </p>
</div>