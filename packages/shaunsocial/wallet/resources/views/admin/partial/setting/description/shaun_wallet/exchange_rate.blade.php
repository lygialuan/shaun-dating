<?php
use Packages\ShaunSocial\Core\Models\Currency;
$currencyDefault = Currency::getDefault();
?>
<p class="control-help control-help-text">
    1 {{$currencyDefault->code}} = <span id="wallet_token_rate">{{getWalletExchangeRate()}}</span> <span id="wallet_token_name">{{getWalletTokenName()}}</span>
</p>

@push('scripts-body')
<script>
function walletCheckExchangeRate() {
    rate = $("#shaun_wallet\\.exchange_rate").val();
    if (rate == '' || rate == 0 ) {
        rate = 1;
    }
    $('#wallet_token_rate').html(rate);

    name = $("#shaun_wallet\\.token_name").val();
    if (name.trim() == '') {
        name = '{{getWalletTokenNameDefault()}}';
    }
    $('#wallet_token_name').html(name)
}
$("#shaun_wallet\\.exchange_rate").keyup(function() {
    walletCheckExchangeRate();
});
$("#shaun_wallet\\.token_name").keyup(function() {
    walletCheckExchangeRate();
});
</script>
@endpush