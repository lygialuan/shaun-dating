var adminCountry = (function () {
    var loading = false;
    var initCountriesListing = function (orderCountriesListUrl) {
        new Sortable(document.getElementById("countries_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCountriesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });
    };

    var initCreateCountry = function () {
        loading = false;
        $("#create_country").click(function () {
            var form = $("#country_form");
            var formData = new FormData(form[0]);

            if (loading) {
                return;
            }
            loading = true;

            $.ajax({
                type: "POST",
                url: $("#country_form").attr("action"),
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#country_form #errors").html("");
                        adminCore.setMessageError(
                            $("#country_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initStatesListing = function (orderStatesListUrl, activeStatesUrl) {
        new Sortable(document.getElementById("states_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderStatesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });

        $(".is_active").click(function (e) {
            var active = $(this).prop("checked");
            if (active) {
                $("#status-action-" + $(this).data("id")).html(
                    adminTranslate.__("active")
                );
                $("#status-" + $(this).data("id")).html(
                    adminTranslate.__("active")
                );
            } else {
                $("#status-" + $(this).data("id")).html(
                    adminTranslate.__("inactive")
                );
                $("#status-action-" + $(this).data("id")).html(
                    adminTranslate.__("deactive")
                );
            }
            $.post(activeStatesUrl, {
                id: $(this).data("id"),
                active: active ? 1 : 0,
            });
        });
    };

    var initCreateState = function () {
        loading = false;
        $("#create_state").click(function () {
            var form = $("#state_form");
            var formData = new FormData(form[0]);

            if (loading) {
                return;
            }
            loading = true;

            $.ajax({
                type: "POST",
                url: $("#state_form").attr("action"),
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#state_form #errors").html("");
                        adminCore.setMessageError(
                            $("#state_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initCitiesListing = function (orderCitiesListUrl, activeCitiesUrl) {
        new Sortable(document.getElementById("cities_list"), {
            animation: 150,
            filter: ".admin_modal_ajax",
            onUpdate: function (e) {
                $.ajax({
                    type: "POST",
                    url: orderCitiesListUrl,
                    data: { orders: this.toArray() },
                    dataType: "json",
                });
            },
        });

        $(".is_active").click(function (e) {
            var active = $(this).prop("checked");
            if (active) {
                $("#status-action-" + $(this).data("id")).html(
                    adminTranslate.__("active")
                );
                $("#status-" + $(this).data("id")).html(
                    adminTranslate.__("active")
                );
            } else {
                $("#status-" + $(this).data("id")).html(
                    adminTranslate.__("inactive")
                );
                $("#status-action-" + $(this).data("id")).html(
                    adminTranslate.__("deactive")
                );
            }
            $.post(activeCitiesUrl, {
                id: $(this).data("id"),
                active: active ? 1 : 0,
            });
        });
    };

    var initCreateCity = function () {
        loading = false;
        $("#create_city").click(function () {
            var form = $("#city_form");
            var formData = new FormData(form[0]);

            if (loading) {
                return;
            }
            loading = true;

            $.ajax({
                type: "POST",
                url: $("#city_form").attr("action"),
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        $("#city_form #errors").html("");
                        adminCore.setMessageError(
                            $("#city_form #errors"),
                            data.messages
                        );
                    }
                },
            }).done(function (data) {
                loading = false;
            });
        });
    };

    var initUploadStatesFile = function () {
        $("#import_state_file").click(function () {
            var formData = new FormData($("#import_state_form")[0]);
            $("#import_state_form #errors").html("");
            $.ajax({
                type: "POST",
                url: $("#import_state_form").attr("action"),
                data: formData,
                dataType: "json",
                contentType: false, //this is requireded please see answers above
                processData: false, //this is requireded please see answers above
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        adminCore.setMessageError(
                            $("#import_state_form #errors"),
                            data.messages
                        );
                    }
                },
            });
        });
    };

    var initUploadCitiesFile = function () {
        $("#import_city_file").click(function () {
            var formData = new FormData($("#import_city_form")[0]);
            $("#import_city_form #errors").html("");
            $.ajax({
                type: "POST",
                url: $("#import_city_form").attr("action"),
                data: formData,
                dataType: "json",
                contentType: false, //this is requireded please see answers above
                processData: false, //this is requireded please see answers above
                success: function (data) {
                    if (data.status) {
                        location.reload();
                    } else {
                        adminCore.setMessageError(
                            $("#import_city_form #errors"),
                            data.messages
                        );
                    }
                },
            });
        });
    };

    return {
        initCountriesListing: initCountriesListing,
        initCreateCountry: initCreateCountry,
        initStatesListing: initStatesListing,
        initCreateState: initCreateState,
        initCitiesListing: initCitiesListing,
        initCreateCity: initCreateCity,
        initUploadStatesFile: initUploadStatesFile,
        initUploadCitiesFile: initUploadCitiesFile,
    };
})();