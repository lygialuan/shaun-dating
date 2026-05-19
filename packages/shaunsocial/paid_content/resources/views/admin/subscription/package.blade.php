@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{$title}}</h5>
        <div class="admin-card-top-action">
            <a class="btn-filled-blue admin_modal_ajax dark" href="javascript:void(0);" data-url="{{route('admin.paid_content.subscription.create_package')}}">{{__('Create new')}}</a>
        </div>
    </div>
    <p class="admin-card-help">{{__('Creators are not allowed to define their own prices for subscription packages and must use the packages specified here.')}}</p>
    <form method="get">
        <div class="admin-card-bar">
            <select name="type" class="form-select">
                <option value="">{{__('All')}}</option>
                @foreach ($typeArray as $value=> $typeName)
                    <option @if ($value == $type) selected @endif value="{{$value}}">{{$typeName}}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-filled-blue" href="javascript:void(0);" id="searchPaidContent">{{__('Search')}}</button>
        </div>
    </form>
    <form method="post" id="user_form" action="{{route('admin.user.store_manage')}}">
        {{ csrf_field() }}
        <input type="hidden" id="action" name="action">
        <div class="admin-card-body table-responsive">
            <table class="admin-table table table-hover">
                <thead>
                    <th>
                        {{__('Price')}}
                    </th>
                    <th>
                        {{__('Enable')}}
                    </th>
                    <th>
                        {{__('Order')}}
                    </th>
                    <th>
                        {{__('Actions')}}
                    </th>
                </thead>
                <tbody>
                    @foreach ($packages as $package)
                    <tr>
                        <td>
                            {{$package->getDescription()}}
                        </td> 
                        <td>
                            @if ($package->is_active)
                                {{__('Yes')}}
                            @else
                                {{__('No')}}
                            @endif
                        </td>
                        <td>
                            {{$package->order}}
                        </td>
                        <td class="actions-cell">
                            <a class="admin_modal_ajax" href="javascript:void(0);" data-url="{{route('admin.paid_content.subscription.create_package', ['id' => $package->id])}}">
                                {{__('Edit')}}
                            </a>
                            <a class="button-red admin_modal_confirm_delete" href="javascript:void(0);" data-url="{{route('admin.paid_content.subscription.delete_package',['id' => $package->id])}}">
                                {{__('Delete')}}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
            {{ $packages->withQueryString()->links('shaun_core::admin.partial.paginate') }}
        </div>
    </form>
</div>
@stop

@push('scripts-body')
<script src="{{ asset('admin/js/paid_content.js') }}"></script>
@endpush