var adminAiProviderKey = (function () {
    var loading = false;

    var initForm = function () {
        loading = false;
        $('#ai_provider_key_submit').unbind('click').click(function () {
            if (loading) {
                return;
            }
            loading = true;

            $.ajax({
                type: 'POST',
                url: $('#ai_provider_key_form').attr('action'),
                data: $('#ai_provider_key_form').serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $('#ai_provider_key_form #errors').html('');
                        adminCore.setMessageError($('#ai_provider_key_form #errors'), data.messages);
                    }
                }
            }).always(function () {
                loading = false;
            });
        });
    };

    return {
        initForm: initForm
    };
})();
