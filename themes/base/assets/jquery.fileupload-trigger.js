(function ($) {
    var FileUploadTrigger = function (element, options) {
        var $element = $(element);

        this.options = $.extend({}, this.DEFAULTS, options);
        this.input = $element.parent().find('input[type="hidden"]');
        this.dropZone = $element.find('.dropzone');
        this.button = $element.find('.fileupload-button');

        if (this.button.length > 0) {
            this.init();
        }
    };

    FileUploadTrigger.DEFAULTS = {
        config: {},
    };

    FileUploadTrigger.prototype.init = function () {
        // Init jquery file uploader
        this.button.fileupload(this.configure());

        // Run methods
        this.defaultEvents();

        // this.customEvents();
        this.dragAndDrop();
    };

    FileUploadTrigger.prototype.configure = function () {
        if (Array.isArray(this.options.config.acceptFileTypes)) {
            var pattern = '(\.|\/)({types})$'.replace('{types}', this.options.config.acceptFileTypes.join("|"));
            this.options.config.acceptFileTypes = new RegExp(pattern, 'i');
        }

        return this.options.config;
    };

    FileUploadTrigger.prototype.defaultEvents = function () {
        this.button
            .on('fileuploadprocessalways', $.proxy(function (e, data) {
                if (data.files[data.index].error) {
                    this.input.empty();
                    this.button.trigger('fileuploadprocessalways:error', data.files[data.index].error);
                }
            }, this))
            .on('fileuploadprogressall', $.proxy(function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);

                this.button.trigger('fileuploadprogressall:progress', progress);
            }, this))
            .on('fileuploaddone', $.proxy(function (e, data) {
                $.each(data.result.files, $.proxy(function (index, file) {
                    if (file.error) {
                        this.button.trigger('fileuploaddone:error', file);
                        this.input.empty();
                    } else {
                        this.button.trigger('fileuploaddone:success', file);
                        this.input.val(file.name);
                    }
                }, this));
            }, this))
            .on('fileuploadfail', $.proxy(function (e, data) {
                $.each(data.files, $.proxy(function (index, file) {
                    this.input.empty();
                }, this));
            }, this))
            .prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    };

    FileUploadTrigger.prototype.dragAndDrop = function () {
        $(document)
            .on('dragover', $.proxy(function (e) {
                var foundDropzone,
                    timeout = window.dropZoneTimeout;
                if (!timeout) {
                    this.dropZone.addClass('in');
                    // this.dropZone.children('.button-wrap').text(this.options.messages['drop']);
                    this.dropZone.trigger('fileuploaddone:drag-and-drop:drop');
                } else {
                    clearTimeout(timeout);
                }
                var found = false,
                    node = e.target;
                do {
                    if ($(node).hasClass('dropzone')) {
                        found = true;
                        foundDropzone = $(node);
                        break;
                    }
                    node = node.parentNode;
                } while (node != null);
                this.dropZone.removeClass('in hover');
                // this.dropZone.children('.button-wrap').text(this.options.messages['select']);
                this.dropZone.trigger('fileuploaddone:drag-and-drop:select');
                if (found) {
                    foundDropzone.addClass('hover');
                    // foundDropzone.children('.button-wrap').text(this.options.messages['drop-text']);
                    this.dropZone.trigger('fileuploaddone:drag-and-drop:drop-here');
                }
            }, this))
            .on('drop', this.dropZone, $.proxy(function (e, data) {
                this.dropZone.removeClass('in hover');
                // this.dropZone.children('.button-wrap').text(this.options.messages['select']);
                this.dropZone.trigger('fileuploaddone:drag-and-drop:select', data);
            }, this));
    };

    $('[uploader-params]').each(function () {
        if (!$(this).data('file-uploader-trigger')) {
            $(this).data('file-uploader-trigger', new FileUploadTrigger(this, JSON.parse(this.getAttribute('uploader-params'))));
        }
    });

    window.FileUploadTrigger = FileUploadTrigger;
}(window.jQuery));