@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <div class="d-flex gap-2">
            <a class="btn-filled-blue" href="{{route('admin.user_subscription.pricing_table.preview')}}">{{__('Preview')}}</a>
            <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.user_subscription.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
        </div>
    </div>    
   
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="200">
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Description')}}
                </th>
                <th width="150">
                    {{__('Role')}}
                </th>
                <th width="200">
                    {{__('Downgrade user role')}}
                </th>
                <th width="100">
                    {{__('Enable')}}
                </th>
                <th width="150">
                    {{__('Badge')}}
                </th>
                <th width="120">
                    {{__('Show Badge')}}
                </th>
                <th width="150">
                    {{__('Actions')}}
                </th>
            </thead>
            <tbody>
                @foreach ($packages as $package)
                <tr>
                    <td>
                        {{$package->name}}
                    </td>
                    <td>
                        {{$package->getTranslatedAttributeValue('description')}}
                    </td>
                    <td width="150">
                        {{$package->getRole()->name}}
                    </td>   
                    <td width="150">
                        {{$package->getRoleDowngrade()->name}}
                    </td>   
                    <td width="100">
                        {{$package->is_active ? __('Yes') : __('No')}}
                    </td>                          
                    <td width="150">
                        <div class="user_subscriptions_badge" style="background:{{$package->badge_background_color}}; color:{{$package->badge_text_color}}; border:1px solid {{$package->badge_border_color}}">
                            {{$package->badge_name ? $package->badge_name : $package->name}}
                        </div>
                    </td>
                    <td width="120">
                        {{$package->is_show_badge ? __('Yes') : __('No')}}
                    </td>  
                    <td class="actions-cell" width="150">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.user_subscription.create',$package->id)}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.user_subscription.delete',$package->id)}}">
                            {{__('Delete')}}
                        </a>
                        <a class="button-blue" href="{{route('admin.user_subscription.plan.index', $package->id)}}">
                            {{__('Plans')}}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>            
    </div>
</div>
@stop