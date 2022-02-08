let x2grapes = {
    config: {
        urlStore: null,
        urlLoad: null,
        csrfTokenName: null,
        csrfToken: null,
        insertableattributes: null,
        container: '#grapes-div',
        wrapper: '#grapes-wrapper'
    },
    initialize: (config) => {
        var myConfig = (config != undefined) ? config : this.config;
        window.editor = grapesjs.init({
            container: myConfig.container,
            fromElement: true,
            height: '60em',
            width: 'auto',
            layerManager: {
                appendTo: '.layers-container'
            },
            plugins: ['gjs-blocks-basic', 'gjs-preset-newsletter'],
            pluginsOpts: {
                'gjs-preset-newsletter': {
                    modalTitleImport: 'Import template',
                    inlineCss: true
                },
            },
            storageManager: {
                id: 'gjs-',             // Prefix identifier that will be used inside storing and loading
                type: 'remote',         // Type of the storage
                urlStore: config.urlStore,
                urlLoad: config.urlLoad,
                autosave: false,
                autoload: false,
                storeComponents: true,  // Enable/Disable storing of components in JSON format
                storeStyles: true,      // Enable/Disable storing of rules in JSON format
                storeHtml: true,        // Enable/Disable storing of components as HTML string
                storeCss: true,         // Enable/Disable storing of rules as CSS string
                contentTypeJson: true,
            },
            // Avoid any default panel
            panels: {
                defaults: [
                    {
                        id: 'layers',
                        el: '.panel__right',
                        // Make the panel resizable
                        resizable: {
                            maxDim: 350,
                            minDim: 200,
                            tc: 0, // Top handler
                            cl: 1, // Left handler
                            cr: 0, // Right handler
                            bc: 0, // Bottom handler
                            // Being a flex child we need to change `flex-basis` property
                            // instead of the `width` (default)
                            keyWidth: 'flex-basis',
                        },
                    }, {
                        id: 'panel-switcher',
                        el: '.panel__switcher',
                        buttons: [{
                            id: 'undo',
                            className: 'fa fa-undo',
                            command: e => e.runCommand('core:undo'),
                        }, {
                            id: 'show-fullscreen',
                            className: 'fa fa-arrows-alt',
                            command(editor) {
                                if (x2.fullscreen) {
                                    x2.fullscreen = 0;
                                    editor.stopCommand('fullscreen', { target: '#grapes-wrapper' });
                                } else {
                                    x2.fullscreen = 1;
                                    editor.runCommand('fullscreen', { target: '#grapes-wrapper' });
                                }
                            },
                            togglable: false,
                            attributes: { title: 'Fullscreen' },
                        }, {
                            id: 'show-layers',
                            className: 'fa fa-bars',
                            command: 'show-layers',
                            // Once activated disable the possibility to turn it off
                            togglable: false,
                            attributes: { title: 'Layers' },
                        }, {
                            id: 'show-style',
                            className: 'fa fa-paint-brush',
                            command: 'show-styles',
                            togglable: false,
                            attributes: { title: 'Style' },
                        }, {
                            id: 'show-traits',
                            className: 'fa fa-cog',
                            command: 'show-traits',
                            togglable: false,
                            attributes: { title: 'Traits' },
                        }, {
                            id: 'show-blocks',
                            active: false,
                            className: 'fa fa-th-large',
                            command: 'show-blocks',
                            togglable: false,
                            attributes: { title: 'Blocks' },
                        }, {
                            id: 'save-button',
                            className: 'btn-save-template fa fa-floppy-o',
                            attributes: { title: 'Save' },
                            context: 'save-template',
                            command(editor) {
                                const RemoteStorage = editor.StorageManager.get('remote');
                                RemoteStorage.set('params', {
                                    docName: $('#doc-name').val(),
                                    gjsinlineCss: editor.runCommand('gjs-get-inlined-html'),
                                    [config.csrfTokenName]: config.csrfToken,
                                    info: $('#Docs_info').val(),
                                    redirectUrl: $('#Docs_redirectURL').val(),
                                    subject:$('#doc-subject').val()
                                });
                                editor.store(res => {
                                    if (res.url !== undefined) RemoteStorage.set({urlStore: res.url});
                                });
                                if ($('#doc-name').val()) {
                                    alert('Saved!');
                                } else {
                                    alert('Please enter a name for the template.');
                                }
                            },
                        },],
                    }, {
                        id: 'panel-devices',
                        el: '.panel__devices',
                        buttons: [{
                            id: 'device-desktop',
                            className: 'fa fa-desktop',
                            command: 'set-device-desktop',
                            active: true,
                            togglable: false,
                            attributes: {
                                'title': 'Desktop',
                                'data-tooltip-pos': 'bottom',
                                'data-tooltip': 'title',
                            }
                        }, {
                            id: 'device-tablet',
                            className: 'fa fa-tablet',
                            command: 'set-device-tablet',
                            togglable: false,
                            attributes: {
                                'title': 'Tablet',
                                'data-tooltip-pos': 'bottom',
                                'data-tooltip': 'title',
                            }
                        }, {
                            id: 'device-mobile',
                            className: 'fa fa-mobile',
                            command: 'set-device-mobile',
                            togglable: false,
                            attributes: {
                                'title': 'Mobile',
                                'data-tooltip-pos': 'bottom',
                                'data-tooltip': 'title',
                            }
                        }],
                    },

                ]
            },
            deviceManager: {
                devices: [{
                    name: 'Desktop',
                    width: '', // default size
                }, {
                    name: 'Tablet',
                    width: '680px', // this value will be used on canvas width
                    widthMedia: '767px', // this value will be used in CSS @media
                }, {
                    name: 'Mobile',
                    width: '320px', // this value will be used on canvas width
                    widthMedia: '480px', // this value will be used in CSS @media
                }]
            },
            traitManager: {
                appendTo: '.traits-container',
            },
            selectorManager: {
                appendTo: '.styles-container'
            },
            styleManager: {
                appendTo: '.styles-container',
                sectors: [{
                    name: 'Dimension',
                    open: false,
                    // Use built-in properties
                    buildProps: ['width', 'min-height', 'padding'],
                    // Use `properties` to define/override single property
                    properties: [
                        {
                            // Type of the input,
                            // options: integer | radio | select | color | slider | file | composite | stack
                            type: 'integer',
                            name: 'The width', // Label for the property
                            property: 'width', // CSS property (if buildProps contains it will be extended)
                            units: ['px', '%'], // Units, available only for 'integer' types
                            defaults: 'auto', // Default value
                            min: 0, // Min value, available only for 'integer' types
                        }
                    ]
                }, {
                    name: 'Extra',
                    open: false,
                    buildProps: ['background-color', 'box-shadow', 'custom-prop'],
                    properties: [
                        {
                            id: 'custom-prop',
                            name: 'Custom Label',
                            property: 'font-size',
                            type: 'select',
                            defaults: '32px',
                            // List of options, available only for 'select' and 'radio'  types
                            options: [
                                { value: '12px', name: 'Tiny' },
                                { value: '18px', name: 'Medium' },
                                { value: '32px', name: 'Big' },
                            ],
                        }
                    ]
                }]
            },
            blockManager: {
                appendTo: '#blocks',
                blocks: [
                    {
                        id: 'section', // id is mandatory
                        label: '<b>Section</b>', // You can use HTML/SVG inside labels
                        attributes: { class: 'gjs-block-section' },
                        content: `<section>
                <h1>This is a simple title</h1>
                <div>This is just a Lorem text: Lorem ipsum dolor sit amet</div>
              </section>`,
                    }, {
                        id: 'text',
                        label: 'Text',
                        content: '<div data-gjs-type=\"text\">Insert your text here</div>',
                    }, {
                        id: 'image',
                        label: 'Image',
                        // Select the component once it's dropped
                        select: true,
                        // You can pass components as a JSON instead of a simple HTML string,
                        // in this case we also use a defined component type `image`
                        content: { type: 'image' },
                        // This triggers `active` event on dropped components and the `image`
                        // reacts by opening the AssetManager
                        activate: true,
                    }, {
                        id: 'column1',
                        label: 'Column',
                        //content: '<div data-gjs-type=\"text\">Insert your text here</div>',
                    }
                ]
            },
            assetManager: {
                upload: yii.scriptUrl + '/media/addImages',
                uploadName: 'upload',
                params: { [config.csrfTokenName]: config.csrfToken },
                multiUpload: false,
                handleAdd: (url) => {
                    if (!url.includes('://')) url = 'https://' + url; //https default protocol
                    url = url.replace(/(^http:\/\/)/, 'https://'); // change http to https
                    editor.AssetManager.add(url);
                }
            },
        });

        editor.Panels.addPanel({
            id: 'panel-top',
            el: '.panel__top',
        });

        editor.Panels.addPanel({
            id: 'basic-actions',
            el: '.panel__basic-actions',
            buttons: [
                {
                    id: 'visibility',
                    active: false, // active by default
                    className: 'btn-toggle-borders fa fa-object-group',
                    attributes: { title: 'Toggle Borders' },
                    command: 'sw-visibility', // Built-in command
                }, {
                    id: 'show-preview',
                    className: 'btn-show-preview fa fa-eye',
                    attributes: { title: 'Preview' },
                    context: 'show-preview',
                    command: 'preview',
                }, {
                    id: 'show-json',
                    className: 'btn-show-json fa fa-cube',
                    attributes: { title: 'JSON' },
                    context: 'show-json',
                    command(editor) {
                        editor.Modal.setTitle('Components JSON')
                            .setContent(`<textarea style=\"width:100%; height: 250px;\">
                \${JSON.stringify(editor.getComponents())}
                  </textarea>`)
                            .open();
                    },
                }, {
                    id: 'edit',
                    command: 'html-edit',
                    className: 'fa fa-code',
                    attributes: { title: 'Edit HTML' },
                }, {
                    id: 'export',
                    className: 'btn-open-export fa fa-share-square-o',
                    attributes: { title: 'Export Template' },
                    command: 'export-template',
                    context: 'export-template', // For grouping context of buttons from the same panel
                },
            ],
        });
        var pfx = editor.getConfig().stylePrefix;
        var modal = editor.Modal;
        var cmdm = editor.Commands;
        var codeViewer = editor.CodeManager.getViewer('CodeMirror').clone();
        var container = document.createElement('div');
        var btnEdit = document.createElement('div');

        codeViewer.set({
            codeName: 'htmlmixed',
            readOnly: 0,
            theme: 'hopscotch',
            autoBeautify: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            lineWrapping: true,
            styleActiveLine: true,
            smartIndent: true,
            indentWithTabs: true
        });

        btnEdit.innerHTML = 'Edit';
        btnEdit.className = pfx + 'btn-prim ' + pfx + 'btn-import';
        btnEdit.onclick = function () {
            var code = codeViewer.editor.getValue();
            editor.DomComponents.getWrapper().set('content', '');
            editor.setComponents(code.trim());
            modal.close();
        };

        cmdm.add('html-edit', {
            run: function (editor, sender) {
                sender && sender.set('active', 0);
                var viewer = codeViewer.editor;
                modal.setTitle('Edit code');
                if (!viewer) {
                    var txtarea = document.createElement('textarea');
                    container.appendChild(txtarea);
                    container.appendChild(btnEdit);
                    codeViewer.init(txtarea);
                    viewer = codeViewer.editor;
                }
                var InnerHtml = editor.getHtml();
                var Css = editor.getCss();
                modal.setContent('');
                modal.setContent(container);
                codeViewer.setContent(InnerHtml + '<style>' + Css + '</style>');
                modal.open();
                viewer.refresh();
            }
        });

        // Define commands
        editor.Commands.add('show-layers', {
            getRowEl(editor) { return editor.getContainer().closest('.editor-row'); },
            getLayersEl(row) { return row.querySelector('.layers-container') },

            run(editor, sender) {
                const lmEl = this.getLayersEl(this.getRowEl(editor));
                lmEl.style.display = '';
            },
            stop(editor, sender) {
                const lmEl = this.getLayersEl(this.getRowEl(editor));
                lmEl.style.display = 'none';
            },
        });
        editor.Commands.add('show-styles', {
            getRowEl(editor) { return editor.getContainer().closest('.editor-row'); },
            getStyleEl(row) { return row.querySelector('.styles-container') },

            run(editor, sender) {
                const smEl = this.getStyleEl(this.getRowEl(editor));
                smEl.style.display = '';
            },
            stop(editor, sender) {
                const smEl = this.getStyleEl(this.getRowEl(editor));
                smEl.style.display = 'none';
            },
        });
        editor.Commands.add('show-traits', {
            getTraitsEl(editor) {
                const row = editor.getContainer().closest('.editor-row');
                return row.querySelector('.traits-container');
            },
            run(editor, sender) {
                this.getTraitsEl(editor).style.display = '';
            },
            stop(editor, sender) {
                this.getTraitsEl(editor).style.display = 'none';
            },
        });
        editor.Commands.add('show-blocks', {
            getBlocksEl(editor) {
                const row = editor.getContainer().closest('.editor-row');
                return row.querySelector('.blocks-container');
            },
            run(editor, sender) {
                this.getBlocksEl(editor).style.display = '';
            },
            stop(editor, sender) {
                this.getBlocksEl(editor).style.display = 'none';
            },
        });
        editor.Commands.add('set-device-desktop', {
            run: editor => editor.setDevice('Desktop')
        });
        editor.Commands.add('set-device-mobile', {
            run: editor => editor.setDevice('Mobile')
        });

        // populate image gallery
        $.ajax({
            type: 'GET',
            url: yii.scriptUrl + '/media/getImages',
            dataType: 'json',
            success: function (data) {
                window.editor.AssetManager.add(data);
            }
        });

        ['a', 'center'].forEach(function (tag) {
            CKEDITOR.dtd.$editable[tag] = 1;
        });

        // use CKEditor
        // code mimics CKEditor plugin code with some amendments to make it actually work
        editor.setCustomRte({
            enable: function (el, rte) {
                // Escape uneditable blocks
                if (CKEDITOR.dtd.$editable[el.tagName.toLowerCase()] !== 1) return null;
                let rteToolbar = editor.RichTextEditor.getToolbarEl();

                // Hide default RTE
                [].forEach.call(rteToolbar.children, (child) => {
                    child.style.display = 'none';
                });

                //if an RTE does not exist, make one
                if (!rte) {
                    //

                    // Spawn CKE toolbar
                    editor.RichTextEditor.getToolbarEl().id = 'rte-toolbar';
                    rte = CKEDITOR.inline(el, {
                        extraPlugins: 'sharedspace,richcombo,insertattributes',
                        removePlugins: 'magicline,link', //get rid of red lines
                        sharedSpaces: {
                            top: 'rte-toolbar',
                        },
                        toolbar: 'MyGrapesToolbar',
                        insertableAttributes: x2.insertableAttributes
                    });

                    // Prevent blur when some of CKEditor's element is clicked
                    rte.on('dialogShow', e => {
                        const editorEls = grapesjs.$('.cke_dialog_background_cover, .cke_dialog');
                        ['off', 'on'].forEach(m => editorEls[m]('mousedown', stopPropagation));
                    });
                    // Correct toolbar position
                    rte.on('instanceReady', e => {
                        let toolbar = rteToolbar.querySelector('#cke_' + rte.name);
                        if (toolbar) toolbar.style.display = 'block';
                        editor.trigger('canvasScroll');
                    });
                }
                editor.trigger('canvasScroll'); // also to correct toolbar position
                this.focus(el, rte);
                
                // allow mutiple edits of link (bugfix)
                if (el.tagName == 'A' && el.getAttribute('href')) el.removeAttribute('href');

                return rte;
            },
            disable: function (el, rte) {
                el.contentEditable = false;
                if (rte && rte.focusManager) {
                    rte.focusManager.blur(true);
                }
            },
            focus: function (el, rte) {
                // Do nothing if already focused
                if (rte && rte.focusManager.hasFocus) {
                    return;
                }
                el.contentEditable = true;
                rte && rte.focus();
            },
        });

        if (config.urlLoad) editor.load(res => console.log(res));

        // only display show-blocks on start
        editor.Panels.getButton('panel-switcher', 'show-layers').set('active', true)
        editor.Panels.getButton('panel-switcher', 'show-style').set('active', true)
        editor.Panels.getButton('panel-switcher', 'show-traits').set('active', true)
        editor.Panels.getButton('panel-switcher', 'show-blocks').set('active', true)

        // Switch to style panel when a style-able component is clicked
        editor.on('component:selected', () => {
            const openSmBtn = editor.Panels.getButton('panel-switcher', 'show-style');
            const openLayersBtn = editor.Panels.getButton('panel-switcher', 'show-layers');

            // Don't switch when the Layer Manager is on or
            // there is no selected component
            if ((!openLayersBtn || !openLayersBtn.get('active')) && editor.getSelected()) {
                openSmBtn && openSmBtn.set('active', 1);
            }
        });
    },
    destroy: () => {
        $(x2grapes.config.wrapper).html("");
    },
    reinitialize: (config) => {
        $(x2grapes.config.wrapper).html(x2grapes.initElems());
        x2grapes.initialize(config);
    },
    initElems: () => {
      return `
        <div class="panel__top">
            <div class="panel__basic-actions"></div>
            <div class="panel__devices"></div>
            <div class="panel__switcher"></div>
        </div>
        <div class="editor-row">
            <div class="editor-canvas">
                <div id="grapes-div">
                    <h1><center>Start Here!</center></h1>
                    <style>.button{color:#fff;background-color:#68d;margin:5px;padding:5px;border-radius:8px}
                            .button:hover{background-color:#79e;box-shadow: 1px 1px 2px grey}</style>
                </div>
            </div>
            <div class="panel__right">
                <div class="layers-container"></div>
                <div class="styles-container"></div>
                <div class="traits-container"></div>
                <div id="blocks" class="blocks-container"></div>
            </div>
        </div>
      `;
    }
}

