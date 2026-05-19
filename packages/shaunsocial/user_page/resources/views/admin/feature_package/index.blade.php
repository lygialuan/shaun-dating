@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Profile Feature Packages')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.user_page.feature_package.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
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
                    {{__('Amount'). ' (' .getWalletTokenName().')'}}
                </th>
                <th>
                    {{__('Type')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="package_list">
                @foreach ($packages as $package)
                <tr data-id="{{$package->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$package->name}}
                    </td>
                    <td>
                        {{formatNumber($package->amount)}}
                    </td>
                    <td>
                        {{$package->getTypeText()}}
                    </td>
                    <td>
                        @if ($package->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.user_page.feature_package.create',$package->id)}}">
                            {{__('Edit')}}
                        </a>
                        
                        <a class="button-red admin_modal_confirm_delete package_delete" data-url="{{route('admin.user_page.feature_package.delete',$package->id)}}" href="javascript:void(0);">
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
<script src="{{ asset('admin/js/user_page.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminUserPage.initFeaturePackageListing('{{route('admin.user_page.feature_package.store_order')}}')
</script>
@endpush