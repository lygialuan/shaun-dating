@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Link Icons')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.link_icon.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>    
    <p class="admin-card-help">{{__('When people add social links to their profiles, the system will automatically attach the link icon based on the settings here.')}}</p>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Domain')}}
                </th>
                <th>
                    {{__('Icon')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="genders_list">
                @foreach ($icons as $icon)
                <tr>
                    <td>
                        {{$icon->domain}}
                    </td>
                    <td>
                        <img src="{{$icon->getIcon()}}" width="32" />
                    </td>
                    <td>
                        @if ($icon->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.link_icon.create',$icon->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.link_icon.delete',$icon->id)}}" href="javascript:void(0);">
                            {{__('Delete')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/link_icon.js') }}"></script>
@endpush