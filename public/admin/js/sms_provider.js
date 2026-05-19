var adminSmsProvider = function () {
    var loading = false
    var initTest = function() {
        loading = false
        $('#sms_provider_test_submit').unbind('click');
        $('#sms_provider_test_submit').click(function () {
        if (loading) {
            return
        }
        loading = true
        var data = {
            'id' : jQuery('#sms_provider_test_form input[name="id"]').val(),
            '_token' : jQuery('#sms_provider_test_form input[name="_token"]').val(),
            'phone_number' : jQuery('#sms_provider_test_form #phoneCode').val() + jQuery('#sms_provider_test_form #phoneNumber').val(),
        };
        $.ajax({ 
            type: 'POST', 
            url: $('#sms_provider_test_form').attr('action'), 
            data: data, 
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if (data.status){
                    location.reload();
                }
                else{
                $('#sms_provider_test_form #errors').html('');
                adminCore.setMessageError($('#sms_provider_test_form #errors'),data.messages);
                }
            }
        }).done(function( data ) {
            loading = false
        });
        });
    } 

    var initCountryPhoneCode = function(itemId, itemValueId) {
        if($(itemValueId).parent().find('.iti__flag-container').length > 0){
            return;
        }
        $(document).ready(function(){
            $.get("https://ipinfo.io", function() {}, "json").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "";
                
                var input = document.querySelector(itemId);
                var iti = intlTelInput(input, {
                    separateDialCode: true,
                    initialCountry: "auto"
                });
                iti.setCountry(countryCode);
                $(itemValueId).val("+" + iti.getSelectedCountryData().dialCode);
                input.addEventListener("countrychange", function() {
                    $(itemValueId).val("+" + iti.getSelectedCountryData().dialCode);
                });
                input.addEventListener("change", function() {
                    var number = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                    console.log(number);
                    if(typeof number != 'undefined'){
                        var dialCode = "+" + iti.getSelectedCountryData().dialCode;
                        number = number.replace(dialCode, '');
                        $(itemId).val(number);
                    }
                });
            });
        })
    }

    return {
        initTest: initTest,
        initCountryPhoneCode: initCountryPhoneCode
    };
}();