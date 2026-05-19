@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{ $title }}</h5>
        <div class="d-flex gap-2">
            <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.country.state.import', $countryId)}}" href="javascript:void(0);">{{__('Import States')}}</a>
            <a class="button admin_modal_ajax btn-filled-blue" data-url="{{route('admin.country.state.create', $countryId)}}" href="javascript:void(0);">{{__('Create State')}}</a>
        </div>
    </div>
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th width="40"></th>
                <th>
                    {{__('Name')}}
                </th>
                <th width="100">
                    {{__('Active')}}
                </th>
                <th width="200">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="states_list">
                @foreach ($states as $state)
                <tr data-id="{{$state->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$state->getTranslatedAttributeValue('name')}}
                    </td>
                    <td>
                        @if ($state->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>    
                    <td class="actions-cell" width="150">
                        <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.country.state.create', [$state->country_id, $state->id])}}">
                            {{__('Edit')}}
                        </a>
                        <a href="{{route('admin.country.city.index',$state->id)}}">
                            {{__('Manage Cities')}}
                        </a>
                        <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.country.state.delete',$state->id)}}">
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
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/translation.js') }}"></script>
<script src="{{ asset('admin/js/country.js') }}"></script>
<script>
    adminCountry.initStatesListing('{{route('admin.country.state.store_order')}}', '{{route('admin.country.state.store_active')}}');
</script>
@endpush
