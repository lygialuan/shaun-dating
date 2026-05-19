@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Plans of')}}: {{$package->name}}</h5>
        <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.user_subscription.plan.create', ['package_id' => $packageId])}}" href="javascript:void(0);">{{__('Create new plan')}}</a>
    </div>    
   
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="50">

                </th>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Price')}}
                </th>
                <th>
                    {{__('Trial day(s)')}}
                </th>
                <th>
                    {{__('Enable')}}
                </th>
                <th>
                    {{__('Actions')}}
                </th>
            </thead>
            <tbody id="plan_list">
                @foreach ($plans as $plan)
                <tr data-id="{{$plan->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$plan->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        {{$plan->getDescription()}}
                    </td> 
                    <td>
                        {{$plan->trial_day}}
                    </td>
                    <td>
                        {{$plan->is_active ? __('Yes') : __('No')}}
                    </td>                          
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.user_subscription.plan.create', ['package_id' => $packageId, 'id' => $plan->id])}}">
                            {{__('Edit')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.user_subscription.plan.delete',['id' => $plan->id])}}">
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
<script src="{{ asset('admin/js/user_subscription.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminUserSubscription.initPackagePlanListing('{{route('admin.user_subscription.store_plan_order')}}')
</script>
@endpush