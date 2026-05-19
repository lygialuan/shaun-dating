@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Gateways')}}</h5>
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
                    {{__('Active')}}
                </th>
                <th style="width: 15%;">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="gateway_list">
                @foreach ($gateways as $gateway)
                <tr data-id="{{$gateway->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                       {{$gateway->name}}
                    </td>
                    <td>
                        @if ($gateway->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.gateway.edit',$gateway->id)}}">{{__('Edit')}}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/gateway.js') }}"></script>
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script>
    adminGateway.initListing('{{route('admin.package.store_order')}}')
</script>
@endpush