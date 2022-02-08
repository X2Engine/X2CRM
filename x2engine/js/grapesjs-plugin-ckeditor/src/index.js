import grapesjs from 'grapesjs';

const stopPropagation = e => e.stopPropagation();

export default grapesjs.plugins.add('gjs-plugin-ckeditor', (editor, opts = {}) => {
  let c = opts;

  let defaults = {
    // CKEditor options
    options: {},

    // On which side of the element to position the toolbar
    // Available options: 'left|center|right'
    position: 'left',
  };

  // Load defaults
  for (let name in defaults) {
    if (!(name in c))
      c[name] = defaults[name];
  }

  if (!CKEDITOR) {
    throw new Error('CKEDITOR instance not found');
  }

  editor.setCustomRte({
    enable(el, rte) {
      // If already exists I'll just focus on it
      if(rte && rte.status != 'destroyed') {
        this.focus(el, rte);
      	return rte;
      }

      el.contentEditable = true;

      // Seems like 'sharedspace' plugin doesn't work exactly as expected
      // so will help hiding other toolbars already created
      let rteToolbar = editor.RichTextEditor.getToolbarEl();
      [].forEach.call(rteToolbar.children, (child) => {
      	child.style.display = 'none';
      });

      // Check for the mandatory options
      var opt = c.options;
      var plgName = 'sharedspace';

      if (opt.extraPlugins) {
        if (typeof opt.extraPlugins === 'string')
          opt.extraPlugins += ',' + plgName;
        else
          opt.extraPlugins.push(plgName);
      } else {
        opt.extraPlugins = plgName;
      }

      if(!c.options.sharedSpaces) {
        c.options.sharedSpaces = {top: rteToolbar};
      }

      // Init CkEditors
      rte = CKEDITOR.inline(el, c.options);

      // Make click event propogate
      rte.on('contentDom', () => {
        var editable = rte.editable();
        editable.attachListener(editable, 'click', () => {
          el.click();
        });
      });

      // The toolbar is not immediatly loaded so will be wrong positioned.
      // With this trick we trigger an event which updates the toolbar position
      rte.on('instanceReady', e => {
        var toolbar = rteToolbar.querySelector('#cke_' + rte.name);
        if (toolbar) {
          toolbar.style.display = 'block';
        }
        editor.trigger('canvasScroll')
      });

      // Prevent blur when some of CKEditor's element is clicked
      rte.on('dialogShow', e => {
        const editorEls = grapesjs.$('.cke_dialog_background_cover, .cke_dialog');
        ['off', 'on'].forEach(m => editorEls[m]('mousedown', stopPropagation));
      });

      this.focus(el, rte);


      return rte;
    },

    disable(el, rte) {
      el.contentEditable = false;
      if(rte && rte.focusManager)
        rte.focusManager.blur(true);
    },

    focus(el, rte) {
      // Do nothing if already focused
      if (rte && rte.focusManager.hasFocus) {
        return;
      }
      el.contentEditable = true;
      rte && rte.focus();
    },
  });

  // Update RTE toolbar position
  editor.on('rteToolbarPosUpdate', (pos) => {
    // Update by position
    switch (c.position) {
      case 'center':
        let diff = (pos.elementWidth / 2) - (pos.targetWidth / 2);
        pos.left = pos.elementLeft + diff;
        break;
      case 'right':
        let width = pos.targetWidth;
        pos.left = pos.elementLeft + pos.elementWidth - width;
        break;
    }

    if (pos.top <= pos.canvasTop) {
      pos.top = pos.elementTop + pos.elementHeight;
    }

    // Check if not outside of the canvas
    if (pos.left < pos.canvasLeft) {
      pos.left = pos.canvasLeft;
    }
  });

});
