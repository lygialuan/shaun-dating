@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('OpenID Providers')}}</h5>
        <a class="btn-filled-blue" href="{{route('admin.openid.create')}}">{{__('Create new')}}</a>
    </div>    
    <div class="admin-card-body table-responsive">
        <table class="admin-table table table-hover">
            <thead>
                <th></th>
                <th>
                    {{__('Name')}}
                </th>
                <th>
                    {{__('Logo')}}
                </th>
                <th>
                    {{__('Active')}}
                </th>
                <th width="150">
                    {{__('Action')}}
                </th>
            </thead>
            <tbody id="provider_list">
                @foreach ($providers as $provider)
                <tr data-id="{{$provider->id}}">
                    <td>
                        <span role="button" class="material-symbols-outlined notranslate admin-menu-table-col-icon"> open_with </span>
                    </td>
                    <td>
                        {{$provider->name}}
                    </td>
                    <td>
                        @php 
                            $photo = $provider->getPhoto();
                        @endphp
                        @if ($photo)
                            <img width="32" src="{{ $photo }}">
                        @endif
                    </td>
                    <td>
                        @if ($provider->is_active)
                            {{__('Yes')}}
                        @else
                            {{__('No')}}
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a href="{{route('admin.openid.create',$provider->id)}}">
                            {{__('Edit')}}
                        </a>
                        @if ($provider->canDelete())
                        <a class="button-red admin_modal_confirm_delete" data-url="{{route('admin.openid.delete',$provider->id)}}" href="javascript:void(0);">
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
<script src="{{ asset('admin/js/lib/Sortable.min.js') }}"></script>
<script src="{{ asset('admin/js/open_provider.js') }}"></script>
<script>
    adminOpenProvider.initListing('{{route('admin.openid.store_order')}}')
</script>
@endpush