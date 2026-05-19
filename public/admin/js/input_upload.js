var adminInputUpload = function () {
    let fileStore = new DataTransfer(); 

    function readURL(input) {
        if (input.files && input.files[0]) {

            var reader = new FileReader();

            reader.onload = function (e) {
                $('#uploadedImage').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    var initInputUpload = function (elementInput) {
        $(elementInput).change(function () {
            readURL(this);
        });
    }

    function readMultiple(input) {
        const preview = $('#previewImages');
        const newFiles = Array.from(input.files);

        if (fileStore.files.length + newFiles.length > 10) {
            alert(adminTranslate.__('error_limit_photos'));
            input.files = fileStore.files;
            return;
        }

        newFiles.forEach(file => fileStore.items.add(file));

        input.files = fileStore.files;

        preview.html('');
        Array.from(fileStore.files).forEach((file, index) => {
            renderPreview(file, index);
        });
    }

    function initMultiUpload(elementInput) {
        const input = document.querySelector(elementInput);

        $(elementInput).on('change', function () {
            readMultiple(this);
        });

        $(document).on('click', '.remove-img', function () {

            const item = $(this).closest('.img-item');
            const index = item.data('index');

            let newFileStore = new DataTransfer();
            Array.from(fileStore.files).forEach((file, i) => {
                if (i !== index) {
                    newFileStore.items.add(file);
                }
            });

            fileStore = newFileStore;

            input.files = fileStore.files;

            $('#previewImages').html('');
            Array.from(fileStore.files).forEach((file, i) => {
                renderPreview(file, i);
            });
        });
    }

    function renderPreview(file, index) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const html = `
                <div class="img-item" data-index="${index}" style="display:inline-block;position:relative;margin:5px;">
                    <img src="${e.target.result}" 
                         style="width:60px;height:60px;object-fit:cover;border-radius:6px;" />
                    <span class="remove-img" 
                          style="position:absolute;top:-5px;right:-5px;background:red;color:#fff;border-radius:50%;cursor:pointer;padding:2px 6px;font-size:12px;">
                        ×
                    </span>
                </div>
            `;
            $('#previewImages').append(html);
        };

        reader.readAsDataURL(file);
    }

    return {
        initInputUpload: initInputUpload,
        initMultiUpload: initMultiUpload
    };
}();