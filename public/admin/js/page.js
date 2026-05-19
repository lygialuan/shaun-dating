var adminPage = function () {
    var loading = false
    var initCreate = function (url, detailUrl) {
        tinymce.init({
            selector: "textarea#content",
            plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime media nonbreaking save table directionality",
                "emoticons template paste textcolor"
            ],
            toolbar1: "styleselect | bold italic | bullist numlist outdent indent | forecolor backcolor emoticons | link unlink anchor image | preview fullscreen code",
            image_advtab: true,
            image_dimensions: false,
            height: 500,
            relative_urls : false,
            remove_script_host : true
        });

        $('#language').change(function () {
            window.location.href = url + '/' + $('#id').val() + '/' + $(this).val();
        });

        $('#page_form').on( "submit", function( event ) {
            if (loading) {
                event.preventDefault();
            }
            loading = true
        });

        $('#slug').on( "keypress", function() {
            $('#slug_link').html(detailUrl.replace('123', $(this).val()))
        });

        $('#slug').trigger('keypress')
    }
    return {
      initCreate: initCreate
    };
}();