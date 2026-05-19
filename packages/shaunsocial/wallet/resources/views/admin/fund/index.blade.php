@extends('shaun_core::admin.layouts.master')

@section('content')
<div class="admin-card has-table">
    <div class="admin-card-top">
        <h5 class="admin-card-top-title">{{__('Send funds to members')}}</h5>
    </div>
    <p class="admin-card-help">
        {{ __("If you receive cash or transfer money outside the system, you can deposit money into member's wallets here. Enter a negative number if you want to deduct credits from the user's wallet") }}
    </p>
    <div class="admin-card-body">
        @include('shaun_core::admin.partial.error')
        <form id="send_fund_form" method="post" action="{{ route('admin.wallet.fund.send_fund')}}">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="control-label">{{__('Select a member')}}</label>
                <div class="control">
                    <input type="text" style="display: none;" id="{{ md5('admin.wallet.auto_suggest') }}" name="id" value="{{old('id')}}" class="setting_input_text" />
                    @include('shaun_core::admin.partial.utility.autocompplete_user', ['value' => old('id'), 'id' => 'admin.wallet.auto_suggest', 'multiple' => false])
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Number of credits') . ' (' .getWalletTokenName(). ')'}}</label>
                <div class="control">
                    <input class="form-control" name="amount" type="number">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">{{__('Notify user')}}</label>
                <div class="control">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="notify" id="1" value="1" checked>
                        <label class="form-check-label" for="1">{{ __('Yes') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="notify" id="0" value="0">
                        <label class="form-check-label" for="0">{{ __('No') }}</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-filled-blue">
                    {{__('Send Credits')}}
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('scripts-body')
<script src="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('admin/js/lib/jquery-autocomplete/autocomplete.css')}}">
@endpush