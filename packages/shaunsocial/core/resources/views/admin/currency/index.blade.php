@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">    
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Currencies')}}</h5>
        <a class="btn-filled-blue admin_modal_ajax dark" data-url="{{route('admin.currency.create')}}" href="javascript:void(0);">{{__('Create new')}}</a>
    </div>    
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Code')}}
                </th>
                <th>
                    {{__('Symbol')}}
                </th>
                <th>
                    {{__('Default')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody>
                @foreach ($currencies as $currency)
                <tr>
                    <td>
                        {{$currency->name}}
                    </td>
                    <td>
                        {{$currency->code}}
                    </td>
                    <td>
                        {{$currency->symbol}}
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="is_default form-check-input" data-id="{{$currency->id}}" type="checkbox" {{ $currency->is_default ? 'checked' : ''}} value="1">
                        </div>
                    </td>
                    <td class="actions-cell">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.currency.create',$currency->id)}}">
                            {{__('Edit')}}
                        </a>
                        
                        <a (@if (! $currency->canDelete())) style="display:none;" @endif class="button-red admin_modal_confirm_delete currency_delete" data-url="{{route('admin.currency.delete',$currency->id)}}" href="javascript:void(0);">
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
<script src="{{ asset('admin/js/currency.js') }}"></script>
<script>
    adminCurrency.initListing('{{route('admin.currency.store_default')}}')
</script>
@endpush