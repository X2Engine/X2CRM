

(function ($) {

    var galleryDefaults = {
        csrfToken: null,
        csrfTokenName: null,

        nameLabel: 'Name',
        descriptionLabel: 'Description',

        hasName: true,
        hasDesc: true,

        uploadUrl: '',
        deleteUrl: '',
        updateUrl: '',
        arrangeUrl: '',

        photos: []
    };

    function galleryManager(el, options) {
        //Extending options:
        var opts = $.extend({}, galleryDefaults, options);
        //code
        var csrfParams = opts.csrfToken ? '&' + opts.csrfTokenName + '=' + opts.csrfToken : '';
        var photos = {}; // photo elements by id
        var $gallery = $(el);
        if (!opts.hasName) {
            if (!opts.hasDesc) {
                $gallery.addClass('no-name-no-desc');
                $('.edit_selected',$gallery).hide();
            }
            else $gallery.addClass('no-name');

        } else if (!opts.hasDesc)
            $gallery.addClass('no-desc');

        var $sorter = $('.sorter', $gallery);
        var $images = $('.images', $sorter);
        var $editorModal = $('.editor-dialog', $gallery);
        var $previewModal = $('.preview-dialog', $gallery);
        var $progressOverlay = $('.progress-overlay', $gallery);
        var $uploadProgress = $('.upload-progress', $progressOverlay);
        var $editorForm = $('.edit-form', $editorModal);

        function htmlEscape(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function createEditorElement(id, src, name, description) {
            var html = '<div class="photo-editor">' +
                '<div class="preview"><img src="' + htmlEscape(src) + '" alt=""/></div>' +
                '<div>' +
                (opts.hasName
                    ? '<label for="photo_name_' + id + '">' + opts.nameLabel + ':</label>' +
                    '<input type="text" name="photo[' + id + '][name]" class="input-xlarge" value="' + htmlEscape(name) + '" id="photo_name_' + id + '"/>'
                    : '') +
                (opts.hasDesc
                    ? '<br/><label for="photo_description_' + id + '">' + opts.descriptionLabel + ':</label>' +
                    '<textarea name="photo[' + id + '][description]" rows="3" cols="40" class="gallery-widget input-xlarge" id="photo_description_' + id + '">' + htmlEscape(description) + '</textarea>'
                    : '') +
                '</div>' +
                '</div>';
            return $(html);
        }

        var photoTemplate = '<div class="photo">' + '<div class="image-preview"><img src=""/></div><div class="caption">';
        if (opts.hasName)photoTemplate += '<h5></h5>';
        if (opts.hasDesc)photoTemplate += '<p></p>';
        photoTemplate += '</div><div class="actions">';
        if (opts.hasName || opts.hasDesc)photoTemplate += '<span class="editPhoto btn btn-primary btn-mini"><i class="icon-pencil icon-white"></i></span> ';
        photoTemplate += '<span class="deletePhoto btn btn-danger btn-mini"><i class="icon-remove icon-white"></i></span>' +
            '</div><input type="checkbox" class="photo-select"/></div>';


        function addPhoto(id, src, name, description, rank) {
            var photo = $(photoTemplate);
            photos[id] = photo;
            photo.data('id', id);
            photo.data('rank', rank);

            $('img', photo).attr('src', src);
            if (opts.hasName) $('.caption h5', photo).text(name);
            if (opts.hasDesc) $('.caption p', photo).text(description);

            $images.append(photo);
            return photo;
        }


        function editPhotos(ids) {
            var l = ids.length;
            var form = $editorForm.empty();
            for (var i = 0; i < l; i++) {
                var id = ids[i];
                var photo = photos[id],
                    src = $('img', photo).attr('src'),
                    name = $('.caption h5', photo).text(),
                    description = $('.caption p', photo).text();
                form.append(createEditorElement(id, src, name, description));
            }
            if (l > 0)$editorModal.dialog ('open');
        }

        function removePhotos(ids) {
            $.ajax({
                type: 'POST',
                url: opts.deleteUrl,
                data: 'id[]=' + ids.join('&id[]=') + csrfParams,
                success: function (t) {
                    if (t == 'OK') {
                        for (var i = 0, l = ids.length; i < l; i++) {
                            photos[ids[i]].remove();
                            delete photos[ids[i]];
                        }
                    } else alert(t);
                }});
        }


        function deleteClick(e) {
            e.preventDefault();
            var photo = $(this).closest('.photo');
            var id = photo.data('id');
            // here can be question to confirm delete
            // if (!confirm(deleteConfirmation)) return false;
            removePhotos([id]);
            return false;
        }

        function editClick(e) {
            e.preventDefault();
            var photo = $(this).closest('.photo');
            var id = photo.data('id');
            editPhotos([id]);
            return false;
        }

        function updateButtons() {
            var selectedCount = $('.photo.selected', $sorter).length;
            $('.select_all', $gallery).prop('checked', $('.photo', $sorter).length == selectedCount);
            if (selectedCount == 0) {
                $('.edit_selected, .remove_selected', $gallery).addClass('disabled');
            } else {
                $('.edit_selected, .remove_selected', $gallery).removeClass('disabled');
            }
        }

        function selectChanged() {
            var $this = $(this);
            if ($this.is(':checked'))
                $this.closest('.photo').addClass('selected');
            else
                $this.closest('.photo').removeClass('selected');
            updateButtons();
        }

        function imageClick(e){
            function calculateDimensions(img){
                function gcd (a, b) {
                    return (b == 0) ? a : gcd (b, a%b);
                }
                var denom=gcd(img.width, img.height);
                var ratio=((img.width/denom)/(img.height/denom));
                var data={"Source":src, "Height":img.height,"Width":img.width,"GCD":denom,"Aspect Ratio":(img.width/denom)+":"+(img.height/denom),"Ratio":((img.width/denom)/(img.height/denom))};
                //console.log(data);
                if(ratio < 2){
                    var width=50/ratio;
                    var height=50;

                }
            }

            e.preventDefault();
            var photo = $(this).closest('.photo');
            var id = photo.data('id');
            var src = yii.baseUrl+'/uploads/gallery/'+id+'.jpg';
            var img = new Image();

            img.src = src;
            $(img).on('load', calculateDimensions(img));
			$(img).addClass ('gallery-widget-image');
            $('.preview-dialog .dialog-body').html (img);
            $previewModal.dialog ('open');
        }

        $images
            .on('click', '.photo .deletePhoto', deleteClick)
            .on('click', '.photo .editPhoto', editClick)
            .on('click', '.photo .photo-select', selectChanged)
            .on('click', '.photo .image-preview', imageClick);


        $('.images', $sorter).sortable({ tolerance: "pointer" }).disableSelection().bind("sortstop", function () {
            var data = [];
            $('.photo', $sorter).each(function () {
                var t = $(this);
                data.push('order[' + t.data('id') + ']=' + t.data('rank'));
            });
            $.ajax({
                type: 'POST',
                url: opts.arrangeUrl,
                data: data.join('&') + csrfParams,
                dataType: "json"
            }).done(function (data) {
                    for (var id in data[id]) {
                        photos[id].data('rank', data[id]);
                    }
                    // order saved!
                    // we can inform user that order saved
                });
        });

        if (window.FormData !== undefined) { // if XHR2 available
            var uploadFileName = $('.afile', $gallery).attr('name');

            function multiUpload(files) {
                if (files.length == 0) return;
                $progressOverlay.show();
                $uploadProgress.css('width', '5%');
                var filesCount = files.length;
                var uploadedCount = 0;
                var ids = [];
                for (var i = 0; i < filesCount; i++) {
                    var fd = new FormData();

                    fd.append(uploadFileName, files[i]);
                    if (opts.csrfToken) {
                        fd.append(opts.csrfTokenName, opts.csrfToken);
                    }
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', opts.uploadUrl, true);
                    xhr.onload = function () {
                        uploadedCount++;
                        if (this.status == 200) {
                            var resp = JSON.parse(this.response);
                            if(resp['error']){
                                alert(resp['message']);
                            }else{
                                addPhoto(resp['id'], resp['preview'], resp['name'], resp['description'], resp['rank']);
                                ids.push(resp['id']);
                            }
                        } else {
                            // exception !!!
                        }
                        $uploadProgress.css('width', '' + (5 + 95 * uploadedCount / filesCount) + '%');
                        //console.log(uploadedCount);
                        if (uploadedCount === filesCount) {
                            $uploadProgress.css('width', '100%');
                            $progressOverlay.hide();
                            if (opts.hasName || opts.hasDesc) editPhotos(ids);
                        }
                    };
                    xhr.send(fd);
                }

            }

            (function () { // add drag and drop
                var el = $gallery[0];
                var isOver = false;
                var lastIsOver = false;

                setInterval(function () {
                    if (isOver != lastIsOver) {
                        if (isOver) el.classList.add('over');
                        else el.classList.remove('over');
                        lastIsOver = isOver
                    }
                }, 30);

                function handleDragOver(e) {
                    e.preventDefault();
                    isOver = true;
                    return false;
                }

                function handleDragLeave() {
                    isOver = false;
                    return false;
                }

                function handleDrop(e) {
                    e.preventDefault();
                    e.stopPropagation();


                    var files = e.dataTransfer.files;
                    multiUpload(files);

                    isOver = false;
                    return false;
                }

                function handleDragEnd() {
                    isOver = false;
                }


                el.addEventListener('dragover', handleDragOver, false);
                el.addEventListener('dragleave', handleDragLeave, false);
                el.addEventListener('drop', handleDrop, false);
                el.addEventListener('dragend', handleDragEnd, false);
            })();

            $('.afile', $gallery).attr('multiple', 'true').on('change', function (e) {
                e.preventDefault();
                multiUpload(this.files);
            });
        } else {
            $('.afile', $gallery).on('change', function (e) {
                e.preventDefault();
                var ids = [];
                $progressOverlay.show();
                $uploadProgress.css('width', '5%');

                var data = {};
                if (opts.csrfToken)
                    data[opts.csrfTokenName] = opts.csrfToken;
                $.ajax({
                    type: 'POST',
                    url: opts.uploadUrl,
                    data: data,
                    files: $(this),
                    iframe: true,
                    processData: false,
                    dataType: "json"
                }).done(function (resp) {
                        if(resp['error']){
                            alert(resp['message']);
                        }else{
                            addPhoto(resp['id'], resp['preview'], resp['name'], resp['description'], resp['rank']);
                            ids.push(resp['id']);
                            $uploadProgress.css('width', '100%');
                            $progressOverlay.hide();
                            if (opts.hasName || opts.hasDesc) editPhotos(ids);
                        }
                    });
            });
        }

		function saveChanges (e) {
            e.preventDefault();
            $.post(opts.updateUrl, $('input, textarea', $editorForm).serialize() + csrfParams, function (data) {
                var count = data.length;
                for (var key = 0; key < count; key++) {
                    var p = data[key];
                    var photo = photos[p.id];
                    $('img', photo).attr('src', p['src']);
                    if (opts.hasName)
                        $('.caption h5', photo).text(p['name']);
                    if (opts.hasDesc)
                        $('.caption p', photo).text(p['description']);
                }
                $editorModal.dialog ('close');
                //deselect all items after editing
                $('.photo.selected', $sorter).each(function () {
                    $('.photo-select', this).prop('checked', false)
                }).removeClass('selected');
                $('.select_all', $gallery).prop('checked', false);
                updateButtons();
            }, 'json');
        };

        $('.edit_selected', $gallery).click(function (e) {
            e.preventDefault();
            var ids = [];
            $('.photo.selected', $sorter).each(function () {
                ids.push($(this).data('id'));
            });
            editPhotos(ids);
            return false;
        });

        $('.remove_selected', $gallery).click(function (e) {
            e.preventDefault();
            var ids = [];
            $('.photo.selected', $sorter).each(function () {
                ids.push($(this).data('id'));
            });
            removePhotos(ids);

        });

        $('.select_all', $gallery).change(function () {
            if ($(this).prop('checked')) {
                $('.photo', $sorter).each(function () {
                    $('.photo-select', this).prop('checked', true)
                }).addClass('selected');
            } else {
                $('.photo.selected', $sorter).each(function () {
                    $('.photo-select', this).prop('checked', false)
                }).removeClass('selected');
            }
            updateButtons();
        });

        for (var i = 0, l = opts.photos.length; i < l; i++) {
            var resp = opts.photos[i];
            addPhoto(resp['id'], resp['preview'], resp['name'], resp['description'], resp['rank']);
        }

		setUpDialogs (saveChanges, {
            'editDialogTitle': opts['editDialogTitle'],
            'editDialogSaveButtonLabel': opts['editDialogSaveButtonLabel'],
            'editDialogCloseButtonLabel': opts['editDialogCloseButtonLabel'],
            'viewDialogTitle': opts['viewDialogTitle'],
            'viewDialogCloseButtonLabel': opts['viewDialogCloseButtonLabel']
		});

    }

    // The actual plugin
    $.fn.galleryManager = function (options) {
        if (this.length) {
            this.each(function () {
                galleryManager(this, options);
            });
        }
    };
})(jQuery);



