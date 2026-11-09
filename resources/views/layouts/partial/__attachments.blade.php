<style>
    .upload-container {
        text-align: center;
        padding: 20px;
        border: 2px dashed #3498db;
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 20px;
    }

    .file-upload {
        display: none;
    }


    .preview-image {
        width: 100%;
        max-height: 100px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        object-fit: contain;
    }

    .preview-pdf,.preview-doc {
        width: 100%;
        height: 100px;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .file-title-input {
        font-size: 14px;
        margin-top: 10px;
        width: 100%;
        padding: 4px;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 14px;
    }

    .remove-button {
        color: #e74c3c;
        cursor: pointer;
        margin-top: 10px;
    }
    .wrapper-list-item {
        display: inline-block;
        width: 18%;
        margin: 1%;
        text-align: center;
    }
</style>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Attachments</label>
    <div class="col-sm-10">
        <div class="upload-container">
            <span class="flow-text" onclick="triggerFileInput()">Click or drag and drop attachments files here</span>
            <input type="file" class="file-upload" name="attachments[]" onchange="displayFilePreviews(this)" multiple>
        </div>

        <div id="file-previews" class="file-preview row">
            @if(isset($attachments))
                @foreach($attachments as $attachment)
                    @php
                        $pathExtension = pathinfo($attachment->file,PATHINFO_EXTENSION);
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp'];
                    @endphp
                    <div class="wrapper-list-item">
                        <input type="text" value="{{ $attachment->title }}" placeholder="Title" class="file-title-input old-file-title-update">
                        @if(in_array($pathExtension,$imageExtensions))
                            <a download href="{{ asset($attachment->file) }}"><img class="preview-image" src="{{ asset($attachment->file) }}"></a>
                        @elseif($pathExtension == 'pdf')
                            <div class="preview-pdf"><a download href="{{ asset($attachment->file) }}">PDF</a></div>
                        @elseif($pathExtension == 'doc' || $pathExtension == 'docx')
                            <div class="preview-doc"><a download href="{{ asset($attachment->file) }}">DOC/DOCX</a></div>
                        @else
                            <div class="preview-pdf"><a download href="{{ asset($attachment->file) }}">{{ strtoupper($pathExtension) }}</a></div>

                        @endif
                        <input type="hidden" value="{{ $attachment->id }}" class="attachment_id">
                        <div data-id="{{ $attachment->id }}" class="remove-button old-file-remove">Remove</div>
                    </div>
                @endforeach
            @endif

        </div>
        <span id="attachments-error" class="help-block error-message"></span>
    </div>
</div>
