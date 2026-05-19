<div class="form-group">
    <label class="control-label">{{__('Allow Access')}}</label>
    <div class="field-body">
        <div class="form-check">
            <input class="form-check-input allow_{{$fieldName}}" name="{{$fieldName}}[]" type="checkbox" id="all" @if (in_array('all',$values)) checked @endif value="all">
            <label class="form-check-label" for="all">{{__('All')}}</label>
        </div>
        @foreach ($roles as $role)           
            <div class="form-check">
                <input class="form-check-input allow_{{$fieldName}}" name="{{$fieldName}}[]" type="checkbox" id="{{$role->id}}" @if (in_array($role->id,$values)) checked @endif value="{{$role->id}}">
                <label class="form-check-label" for="{{$role->id}}">{{$role->name}}</label>
            </div>
        @endforeach
    </div>
</div>

@if (request()->ajax())
<script>
    adminCore.initAllowAccess('{{$fieldName}}');
</script>
@else
@push('scripts-body')
<script>
    adminCore.initAllowAccess('{{$fieldName}}');
</script>
@endpush
@endif