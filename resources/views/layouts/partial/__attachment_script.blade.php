<script>
    $('body').on('click', '.old-file-remove', function() {
        let oldFileRemove = $(this);
        let attachmentId = $(this).data('id');
        if (confirm('are you sure delete?')){
            preloaderToggle(true);

            $.ajax({
                method: "post",
                url: "{{ route('attachment-delete') }}",
                data: { id: attachmentId }
            }).done(function( response ) {
                oldFileRemove.closest('.wrapper-list-item').remove();
                preloaderToggle(false);
            });
        }
    });
    $('body').on('focusout', '.old-file-title-update', function() {
        let oldFileTitleItem = $(this);
        let oldFileTitle = $(this).val();

        let attachmentId = oldFileTitleItem.closest('div').find('.attachment_id').val();
        $.ajax({
            method: "post",
            url: "{{ route('attachment-title-update') }}",
            data: { id: attachmentId,title:oldFileTitle }
        }).done(function( response ) {
        });
    });

    function triggerFileInput() {
        const fileInput = document.querySelector('.file-upload');
        fileInput.click();
    }
    function addFileInput() {
        const newInput = document.createElement('input');
        newInput.type = 'file';
        newInput.classList.add('file-upload');
        newInput.multiple = true;
        newInput.onchange = function () {
            displayFilePreviews(newInput);
        };

        const filePreviews = document.getElementById('file-previews');
        filePreviews.appendChild(newInput);
    }
    function displayFilePreviews(input) {
        const fileInput = input;
        const filePreviews = document.getElementById('file-previews');
        const previewItem = document.createElement('div');
        previewItem.classList.add('file-preview');

        Array.from(fileInput.files).forEach(file => {
            // Add input field for file title
            const wrapperListItem = document.createElement('div');
            wrapperListItem.classList.add('wrapper-list-item');
            previewItem.appendChild(wrapperListItem);
            const titleInput = document.createElement('input');
            titleInput.type = 'text';
            titleInput.name = 'attachment_title[]';
            titleInput.placeholder = 'Title';
            titleInput.classList.add('file-title-input');

            wrapperListItem.appendChild(titleInput);


            if (file.type.startsWith('image/')) {
                // Image preview
                const previewImage = document.createElement('img');
                previewImage.classList.add('preview-image');
                previewImage.src = URL.createObjectURL(file);
                wrapperListItem.appendChild(previewImage);
            } else if (file.type === 'application/pdf') {
                // PDF preview
                const previewPdf = document.createElement('div');
                previewPdf.classList.add('preview-pdf');
                previewPdf.textContent = 'PDF';
                wrapperListItem.appendChild(previewPdf);
            } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                // DOC/DOCX preview
                const previewDoc = document.createElement('div');
                previewDoc.classList.add('preview-doc');
                previewDoc.textContent = 'DOC/DOCX';
                wrapperListItem.appendChild(previewDoc);
            }else {
                // Other file types (e.g., documents)
                const previewPdf = document.createElement('div');
                previewPdf.classList.add('preview-pdf');
                previewPdf.textContent = file.name;
                wrapperListItem.appendChild(previewPdf);
            }

            // Add a remove button for each preview
            const removeButton = document.createElement('div');
            removeButton.classList.add('remove-button');
            removeButton.textContent = 'Remove';
            removeButton.onclick = function () {
                fileInput.value = ''; // Clear the input
                wrapperListItem.remove();
            };

            wrapperListItem.appendChild(removeButton);

            filePreviews.appendChild(wrapperListItem);
        });
    }
</script>
