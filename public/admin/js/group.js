var adminGroup = (function () {
    var loading = false;

    var initCategoriesList = function (orderCategoriesListUrl) {
        new Sortable(document.getElementById("group_categories_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCategoriesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });

        $(".menu_child").each(function () {
            new Sortable(document.getElementById($(this).attr("id")), {
                animation: 150,
                filter: ".admin_modal_ajax",
                onUpdate: function (e) {
                    $.ajax({
                        type: "POST",
                        url: orderCategoriesListUrl,
                        data: { orders: this.toArray() },
                        dataType: "json",
                    });
                },
            });
        });
    };

    var initCreateCategory = function () {
        loading = false;
        $("#create_category_submit").unbind("click");
        $("#create_category_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            $.ajax({
                type: "POST",
                url: $("#create_category_form").attr("action"),
                data: $("#create_category_form").serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_category_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_category_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initListing = function() {
        $('#delete_button').click(function(e) {
            if ($('.check_item:checked').length == 0) {            
              return;
            }
  
            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_group'), function(){
              $('#action').val('delete');
              $('#user_form').submit();
            });
         });
    }
    return {
        initCategoriesList: initCategoriesList,
        initCreateCategory: initCreateCategory,
        initListing: initListing
    };
})();
