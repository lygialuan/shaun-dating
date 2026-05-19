var adminDating = (function () {
    var loading = false;

    var initRadio = function () {
        $('input.form-radio').on('change', function () {
            const $this = $(this);
            const group = $this.data('group');

            if (!$this.is(':checked')) {
                $this.prop('checked', true);
                return;
            }

            $('input.form-radio[data-group="' + group + '"]').not($this).prop('checked', false);
        });
    }

    var initAttributesList = function (orderCategoryTagUrl) {
        new Sortable(document.getElementById("dating_attributes_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCategoryTagUrl,
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
                        url: orderCategoryTagUrl,
                        data: { orders: this.toArray() },
                        dataType: "json",
                    });
                },
            });
        });
    };

    var initCreateCategory = function (ajaxCategoryUrl) {
        loading = false;
        $("#create_category_submit").unbind("click");
        $("#create_category_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            var form = $('#create_category_form');
            var formData = new FormData(form[0]);
            $.ajax({
                type: "POST",
                url: $("#create_category_form").attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
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

        $('#parent_id').unbind('click');
        $('#parent_id').click(function () {
            const val = $(this).val();
            if (val != 0) {
                $('#moreOptionBlock').show(100);
                $.ajax({
                    type: "POST",
                    url: ajaxCategoryUrl,
                    data: { category_id: val, id: $('input[name=id]').val() },
                    dataType: "html",
                    success: function (data) {
                        if (data) {
                            $('#parent_id_1').html(data);
                            $('#parent_content_1').show(100)
                        }
                    },
                }).done(function (data) {
                    loading = false;
                });
            }
            else {
                $('#moreOptionBlock').hide(100);
                $('#parent_content_1').hide(100);
            }
        })

        $('#parent_id_1').unbind('click');
        $('#parent_id_1').click(function () {
            const val = $(this).val();
            if (val != 0) {
                $('#moreOptionBlock').hide(100);
            }
            else {
                $('#moreOptionBlock').show(100);
            }
        })

        if (!$('input[name=enable_price]').is(':checked')) {
            $('.radio-box').hide(100);
            $('.priceHelp').hide(100);
        }

        $('input[name=enable_price]').unbind('click')
        $('input[name=enable_price]').click(function () {
            if ($(this).is(':checked')) {
                $('.radio-box').show(100);
                $('.priceHelp').show(100);
            }
            else {
                $('.radio-box').hide(100);
                $('.priceHelp').hide(100);
            }
        });
    };

    var initCreateUnit = function () {
        loading = false;
        $("#create_unit_submit").unbind("click");
        $("#create_unit_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            var form = $('#create_unit_form');
            var formData = new FormData(form[0]);
            $.ajax({
                type: "POST",
                url: $("#create_unit_form").attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#create_unit_form #errors").html("");
                        adminCore.setMessageError(
                            $("#create_unit_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initCreateAttribute = function (searchAttributeUrl) {
        loading = false;
        $("#create_category_submit").unbind("click");
        $("#create_category_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            var form = $('#create_category_form');
            var formData = new FormData(form[0]);
            $.ajax({
                type: "POST",
                url: $("#create_category_form").attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
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

    var initCreateFeaturePackage = function () {
        loading = false
        $('#package_submit').click(function () {
            var form = $('#package_form');
            var formData = new FormData(form[0]);

            if (loading) {
                return
            }
            loading = true

            $.ajax({
                type: 'POST',
                url: $('#package_form').attr('action'),
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    }
                    else {
                        $('#package_form #errors').html('');
                        adminCore.setMessageError($('#package_form #errors'), data.messages);
                    }
                }
            }).done(function (data) {
                loading = false
            });
        });
    }
    var initFeaturePackageListing = function (storeOrderUrl) {
        new Sortable(document.getElementById('package_list'), {
            animation: 150,
            filter: '.active_action',
            onUpdate: function (e) {
                $.ajax({
                    type: 'POST',
                    url: storeOrderUrl,
                    data: { orders: this.toArray() },
                    dataType: 'json',
                    success: function (data) {
                        
                    }
                });
            }
        });
    }
    var initListing = function () {
        $('#delete_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_listing'), function () {
                $('#action').val('delete');
                $('#listing_form').submit();
            });
        });
    }

    var initEditListing = function (getStateUrl) {
        $('select[name="country_id"]').unbind('change');
        $('select[name="country_id"]').change(function () {
            $.ajax({
                type: 'POST',
                url: getStateUrl,
                data: { country_id: $('select[name="country_id"]').val() },
                cache: false,
                success: function (data) {
                    $('#state_block').replaceWith(data);
                }
            }).done(function (data) {
                // loading = false
            });
        });
    }

    var initAttributeValueListing = function () {
        $('#delete_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_delete_attribute_value'), function () {
                $('#action').val('delete');
                $('#listing_tag_form').submit();
            });
        });

        $('#active_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_active_attribute_value'), function () {
                $('#action').val('active');
                $('#listing_tag_form').submit();
            });
        });

        $('#pending_button').click(function (e) {
            if ($('.check_item:checked').length == 0) {
                return;
            }

            adminCore.showConfirmModal(adminTranslate.__('confirm'), adminTranslate.__('confirm_pending_attribute_value'), function () {
                $('#action').val('pending');
                $('#listing_tag_form').submit();
            });
        });
    }

    var initCreateAttributeValue = function () {
        var inputsCheckBox = $('.option-box-input');

        $('.selectOpenAddMore').unbind('change');
        $('.selectOpenAddMore').change(function () {
            var _self = $(this);
            if (_self.is(':checked')) {
                $('.more-option').slideDown(300)
            }
            else {
                $('.more-option').slideUp(300)
            }
        });

        $('.checkAll').unbind('change');
        $('.checkAll').change(function () {
            var _self = $(this);
            inputsCheckBox.removeAttr('checked');
            if (_self.is(':checked')) {
                inputsCheckBox.attr('checked', true);
            }
            else {
                inputsCheckBox.attr('checked', false);
            }
        })
    }

    var initUpdateAvailableUnit = function () {
        loading = false;
        $("#update_available_unit_submit").unbind("click");
        $("#update_available_unit_submit").click(function () {
            if (loading) {
                return;
            }
            loading = true;
            var form = $('#update_available_unit_form');
            var formData = new FormData(form[0]);
            $.ajax({
                type: "POST",
                url: $("#update_available_unit_form").attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#update_available_unit_form #errors").html("");
                        adminCore.setMessageError(
                            $("#update_available_unit_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initSaveProfileSettings = function () {
        loading = false;
        $("#profile_completion_settings_submit").unbind("click");
        $("#profile_completion_settings_submit").click(function (e) {
            e.preventDefault();

            if (loading) return;
            loading = true;

            var form = $('#profile_completion_settings_form');
            var formData = new FormData(form[0]);

            $.ajax({
                type: "POST",
                url: form.attr("action"),
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#errors").html("");
                        adminCore.setMessageError(
                            $("#errors"),
                            data.messages
                        );
                    }
                },
                complete: function () {
                    loading = false;
                }
            });
        });
    }

    return {
        initCreateCategory: initCreateCategory,
        initCreateFeaturePackage: initCreateFeaturePackage,
        initFeaturePackageListing: initFeaturePackageListing,
        initListing: initListing,
        initAttributesList: initAttributesList,
        initCreateAttribute: initCreateAttribute,
        initCreateUnit: initCreateUnit,
        initEditListing: initEditListing,
        initAttributeValueListing: initAttributeValueListing,
        initCreateAttributeValue: initCreateAttributeValue,
        initUpdateAvailableUnit: initUpdateAvailableUnit,
        initRadio: initRadio,
        initSaveProfileSettings: initSaveProfileSettings
    };
})();
