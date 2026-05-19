<p class="control-help control-help-text">
    {!!__('Ads will appear after every :1 activity feeds. If you enter a value that is not valid, the system will use the default value of 3.',['1' => '<span id="advertising_feed_number_show"></span>'])!!}
</p>

@push('scripts-body')
<script>
function advertisingNumberShow() {
    value = $("#shaun_advertising\\.feed_number_show").val();
    if (value == '' || parseInt(value) < {{config('shaun_advertising.post_number_show')}} || parseInt(value) > {{setting('feature.item_per_page')}}) {
        value = {{config('shaun_advertising.post_number_show')}};
    }
    console.log(value)
    $('#advertising_feed_number_show').html(value);
}
$("#shaun_advertising\\.feed_number_show").keyup(function() {
    advertisingNumberShow();
});
$("#shaun_advertising\\.feed_number_show").keyup(function() {
    advertisingNumberShow();
});
advertisingNumberShow();
</script>
@endpush