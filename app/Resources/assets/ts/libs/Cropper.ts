/// <reference path="./../types/cropper.d.ts"/>
import 'cropperjs';
import 'cropper';

export class Cropper{

    private options:any;

    private $modal:JQuery<HTMLElement>;

    private $aspectRatio:JQuery<HTMLElement>;

    private $input:JQuery<HTMLElement>;

    private $container:any;

    private $local:any;

    private $remote:any;

    public constructor(private $el:JQuery<HTMLElement>){
        this.options = $.extend({
            scalable: false
        }, $el.data('cropper-options'));

        this
            .initElements()
            .initLocalEvents()
            .initRemoteEvents()
            .initCroppingEvents()
        ;
    }

    private initElements(){
        this.$modal = this.$el.find('.modal');
        this.$modal.find('.modal-dialog').removeClass('modal-lg');
        this.$aspectRatio = this.$modal.find('input[name="cropperAspectRatio"]');
        this.$input = this.$el.find('input.cropper-base64');

        this.$container = {
            $preview: this.$modal.find('.cropper-preview'),
            $canvas: this.$el.find('.cropper-canvas-container')
        };

        this.$local = {
            $btnUpload: this.$el.find('.cropper-local button'),
            $input: this.$el.find('.cropper-local input[type="file"]')
        };

        this.$remote = {
            $btnUpload: this.$el.find('.cropper-remote button'),
            $uploadLoader: this.$el.find('.cropper-remote .remote-loader'),
            $input: this.$el.find('.cropper-remote input[type="url"]')
        };

        this.options = $.extend(this.options, {
            aspectRatio: this.$aspectRatio.val()
        });

        return this;
    }

    private initLocalEvents(){
        let self = this;

        // map virtual upload button to native input file element
        this.$local.$btnUpload.on('click',  () => {
            self.$local.$input.trigger('click');
        });

        // start cropping process on input file "change"
        this.$local.$input.on('change', function () {
            let reader = new FileReader();

            // show a croppable preview image in a modal
            reader.onload = function (e:any) {
                self.prepareCropping(e.target.result);

                // clear input file so that user can select the same image twice and the "change" event keeps being triggered
                self.$local.$input.val('');
            };

            // trigger "reader.onload" with uploaded file
            reader.readAsDataURL(this.files[0]);
        });

        return this;
    }

    private initRemoteEvents(){
        let self = this;

        let $btnUpload = this.$remote.$btnUpload;
        let $uploadLoader = this.$remote.$uploadLoader;

        // handle distant image upload button state
        this.$remote.$input.on('change, input', function () {
            let url:string = $(this).val().toString();

            self.$remote.$btnUpload.prop('disabled', url.length <= 0 || url.indexOf('http') === -1);
        });

        // start cropping process get image's base64 representation from local server to avoid cross-domain issues
        this.$remote.$btnUpload.on('click', function () {
            $btnUpload.hide();
            $uploadLoader.removeClass('hidden');
            $.ajax({
                url: $btnUpload.data('url'),
                data: {
                    url: self.$remote.$input.val()
                },
                method: 'post'
            }).done(function (data) {
                self.prepareCropping(data.base64);
                $btnUpload.show();
                $uploadLoader.addClass('hidden');
            });
        });

        return this;
    }

    private initCroppingEvents(){
        let self = this;

        // handle image cropping
        this.$modal.find('[data-method="getCroppedCanvas"]').on('click', function() {
            self.crop();
        });

        // handle "aspectRatio" switch
        self.$aspectRatio.on('change', function() {
            self.$container.$preview.children('img').cropper('setAspectRatio', $(this).val());
        });

        return this;
    }

    private prepareCropping(base64:string){
        let self = this;

        // clean previous croppable image
        this.$container.$preview.children('img').cropper('destroy').remove();

        // reset "aspectRatio" buttons
        this.$aspectRatio.each(function() {
            let $this = $(this);

            if ($this.val().toString().length <= 0) {
                $this.trigger('click');
            }
        });

        this.$modal
            .one('shown.bs.modal', function() {
                // (re)build croppable image once the modal is shown (required to get proper image width)
                $('<img>')
                    .attr('src', base64)
                    .on('load', function() {
                        $(this).cropper(self.options);
                    })
                    .appendTo(self.$container.$preview);
            })
            .modal('show');
    }

    private crop() {
        let data = this.$container.$preview.children('img').cropper('getData'),
            image_width = Math.min(this.$el.data('max-width'), data.width),
            image_height = Math.min(this.$el.data('max-height'), data.height),
            preview_width = Math.min(this.$container.$canvas.data('preview-width'), data.width),
            preview_height = Math.min(this.$container.$canvas.data('preview-height'), data.height),

            // TODO: getCroppedCanvas seams to only consider one dimension when calculating the maximum size
            // in respect to the aspect ratio and always considers width first, so height is basically ignored!
            // To set a maximum height, no width parameter should be set.
            // Example of current wrong behavior:
            // source of 200x300 with resize to 150x200 results in 150x225 => WRONG (should be: 133x200)
            // source of 200x300 with resize to 200x150 results in 200x300 => WRONG (should be: 100x150)
            // This is an issue with cropper, not this library
            preview_canvas = this.$container.$preview.children('img').cropper('getCroppedCanvas', {
                width: preview_width,
                height: preview_height
            }),
            image_canvas = this.$container.$preview.children('img').cropper('getCroppedCanvas', {
                width: image_width,
                height: image_height
            });

        // fill canvas preview container with cropped image
        this.$container.$canvas.html(preview_canvas);

        // fill input with base64 cropped image
        this.$input.val(image_canvas.toDataURL(this.$el.data('mimetype'), this.$el.data('quality')));

        // hide the modal
        this.$modal.modal('hide');
    }
}