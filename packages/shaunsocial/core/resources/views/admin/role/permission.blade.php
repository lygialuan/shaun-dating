@extends('shaun_core::admin.layouts.master')

@section('content')
@include('shaun_core::admin.partial.error')
<form id="permission_form" method="post" action="{{ route('admin.role.store_permission')}}">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{ $role->id }}" />
    <div class="admin-grid-block-cont">
        <div class="form-body {{count($groups) > 1 ? 'form-body-group' : 'form-body-single'}}">
            @foreach($groups as $group)
            @php 
                $permissions = $group->getPermissions($role)
            @endphp
                @if ($permissions->count())
                    <div class="admin-card has-form-group">
                        <div class="admin-card-top">
                            <h5 class="admin-card-top-title">{{ $group->name }}</h5>
                        </div>
                        <div class="admin-card-body">          
                            <div class="group" id="group_{{$group->id}}">
                                @foreach($permissions as $permission)
                                    @include('shaun_core::admin.partial.permission.field')
                                    <div class="form-group">
                                        @if ($permission->haveMessagePermission())
                                            <p class="help">
                                                {{__("Warning message to user if they don't have this permission")}}: {{$permission->getTranslatedAttributeValue('message_error')}} <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{ route('admin.translation.edit_model',[$permission->getTable(),'message_error',$permission->id])}}"><span class="material-symbols-outlined notranslate"> edit </span></a>
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div> 
                    </div>   
                @endif
            @endforeach
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-filled-blue">
            {{__('Submit')}}
        </button>
    </div>
</form>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/translation.js') }}"></script>
@endpush