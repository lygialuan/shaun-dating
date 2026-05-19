@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Roles')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.role.create')}}">{{__('Create new')}}</a>
    </div>
    <p class="admin-card-help">{{__("Member levels can be used to give certain members more privileges than others. You can grant/restrict a member level's access to features and sections of the community. You can also assign levels that have moderation. One of your member levels must be marked as the 'default level'. When new members sign up, they will automatically belong to this level.")}}</p>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Super Admin')}}
                </th>
                <th>
                    {{__('Moderator')}}
                </th>
                <th>
                    {{__('Default')}}
                </th>
                <th width="200">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                <tr>
                    <td>
                        {{$role->name}}
                    </td>
                    <td>
                        @if ($role->is_supper_admin)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td>
                        @if ($role->is_moderator)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td>
                        @if ($role->canDefault())       
                            <div class="form-check form-switch">
                                <input class="is_default form-check-input" data-id="{{$role->id}}" type="checkbox" {{ $role->is_default ? 'checked' : ''}} value="1">
                            </div>
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.role.create',$role->id)}}">
                            {{__('Edit')}}
                        </a>

                        @if ($role->canPermission())
                            <a href="{{route('admin.role.permission',$role->id)}}">
                                {{__('Permissions')}}
                            </a>
                        @endif

                        @if ($role->canDelete())
                            <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.role.delete',$role->id)}}" href="javascript:void(0);">
                                {{__('Delete')}}
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/role.js') }}"></script>
<script>
    adminRole.initListing('{{route('admin.role.store_default')}}')
</script>
@endpush