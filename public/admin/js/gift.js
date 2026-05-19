var adminGift = (function () {
    var loading = false;

    var initStoreOrder = function (orderUrl) {
        new Sortable(document.getElementById("gift_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });
    };

    var initCreate = function () {
        loading = false;
        $("#create_gift_submit").unbind("click");
        $("#create_gift_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            var form = $('#create_gift_form');
            var formData = new FormData(form[0]);
            $.ajax({
                type: "POST",
                url: $("#create_gift_form").attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_gift_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_gift_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function () {
                loading = false;
            });
        });
    };

    return {
        initStoreOrder: initStoreOrder,
        initCreate: initCreate,
    };
})();
