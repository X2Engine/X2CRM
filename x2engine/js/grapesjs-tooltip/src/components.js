export default (editor, opts = {}) => {
  const dc = editor.DomComponents;
  const defaultType = dc.getType('default');
  const defaultModel = defaultType.model;
  const defaultView = defaultType.view;
  const cssc = editor.CssComposer;
  const {
    id,
    labelTooltip,
    propsTooltip,
    attrTooltip,
    classTooltip,
    style,
    styleAdditional,
    privateClasses,
    stylableTooltip,
    showTooltipOnStyle,
    extendTraits,
  } = opts;
  const classTooltipBody = `${classTooltip}__body`;
  const classTooltipEmpty = `${classTooltip}--empty`;
  const attrTooltipVis = `${attrTooltip}-visible`;
  const attrTooltipPos = `${attrTooltip}-pos`;
  const attrTooltipLen = `${attrTooltip}-length`;
  const traitIdStyleTooltip = 'style-tooltip';

  if (privateClasses) {
    editor.SelectorManager.getAll().add([
      { private: 1, name: classTooltip },
      { private: 1, name: classTooltipBody },
      { private: 1, name: classTooltipEmpty },
    ])
  }

  if (showTooltipOnStyle) {
    editor.on('styleManager:update:target', target => {
      const selected = editor.getSelected();

      if (selected) {
        const el = selected.getEl();

        if (selected.is(id) &&
            !selected.getTrait(attrTooltipVis).getTargetValue() &&
            target.getSelectors().getFullString().trim() == `.${classTooltipBody}`) {
              el.setAttribute(attrTooltipVis, 'true');

              editor.once('styleManager:update:target', () => {
                el.removeAttribute(attrTooltipVis);
              })
        }
      }
    })
  }

  const createCssStyles = () => {
    let css = style || `
      .${classTooltip} {
        position: relative;
        display: inline-block;
        vertical-align: top;
      }

      .${classTooltipEmpty} {
        width: 50px;
        height: 50px;
      }

      .${classTooltipBody},
      [${attrTooltip}]::after {
        font-family: Helvetica, sans-serif;
        background: rgba(55, 61, 73, 0.95);
        border-radius: 3px;
        bottom: 100%;
        color: #fff;
        content: attr(${attrTooltip});
        display: block;
        font-size: 12px;
        left: 50%;
        line-height: normal;
        max-width: 32rem;
        opacity: 0;
        overflow: hidden;
        padding: 8px 16px;
        pointer-events: none;
        position: absolute;
        text-overflow: ellipsis;
        transform: translate(-50%, 0);
        transition: opacity 0.25s, transform 0.25s;
        white-space: nowrap;
        box-sizing: border-box;
        z-index: 10;
      }

      [${attrTooltipVis}=true]::after,
      [${attrTooltip}]:focus::after,
      [${attrTooltip}]:hover::after {
        opacity: 1;
        transform: translate(-50%, -0.5rem);
      }

      [${attrTooltipPos}=right]::after {
        bottom: 50%;
        left: 100%;
        transform: translate(0, 50%);
      }

      [${attrTooltipPos}=right]:focus::after,
      [${attrTooltipPos}=right]:hover::after,
      [${attrTooltipVis}=true][${attrTooltipPos}=right]::after {
        transform: translate(0.5rem, 50%);
      }

      [${attrTooltipPos}=bottom]::after {
        bottom: auto;
        top: 100%;
        transform: translate(-50%, 0);
      }

      [${attrTooltipPos}=bottom]:focus::after,
      [${attrTooltipPos}=bottom]:hover::after,
      [${attrTooltipVis}=true][${attrTooltipPos}=bottom]::after {
        transform: translate(-50%, 0.5rem);
      }

      [${attrTooltipPos}=left]::after {
        bottom: 50%;
        left: auto;
        right: 100%;
        transform: translate(0, 50%);
      }

      [${attrTooltipPos}=left]:focus::after,
      [${attrTooltipPos}=left]:hover::after,
      [${attrTooltipVis}=true][${attrTooltipPos}=left]::after {
        transform: translate(-0.5rem, 50%);
      }

      [${attrTooltipLen}=small]::after {
        white-space: normal;
        width: 80px;
      }

      [${attrTooltipLen}=medium]::after {
        white-space: normal;
        width: 150px;
      }

      [${attrTooltipLen}=large]::after {
        white-space: normal;
        width: 300px;
      }

      [${attrTooltipLen}=fit]::after {
        white-space: normal;
        width: 100%;
      }

      // IE 11 bugfix
      button[${attrTooltip}] {
        overflow: visible;
      }
    `;
    cssc.getAll().add(css + styleAdditional);
  }

  dc.addType(id, {
    model: defaultModel.extend({
      defaults: {
        ...defaultModel.prototype.defaults,
        name: labelTooltip,
        classes: [classTooltip],
        attributes: {
          [attrTooltip]: labelTooltip,
        },
        'style-signature': [`[${attrTooltip}`, `.${classTooltip}`],
        traits: extendTraits([
          {
            name: attrTooltip,
            label: 'Text',
          }, {
            name: attrTooltipPos,
            label: 'Position',
            type: 'select',
            options: [
              { value: 'top', name: 'Top' },
              { value: 'right', name: 'Right' },
              { value: 'bottom', name: 'Bottom' },
              { value: 'left', name: 'Left' },
            ]
          }, {
            name: attrTooltipLen,
            label: 'Length',
            type: 'select',
            options: [
              { value: '', name: 'One line' },
              { value: 'small', name: 'Small' },
              { value: 'medium', name: 'Medium' },
              { value: 'large', name: 'Large' },
              { value: 'fit', name: 'Fit' },
            ]
          }, {
            name: attrTooltipVis,
            label: 'Visible',
            type: 'checkbox',
            valueTrue: 'true',
          }, {
            name: traitIdStyleTooltip,
            labelButton: 'Style tooltip',
            type: 'button',
            full: 1,
            command: (editor, trait) => {
              const openSm = editor.Panels.getButton('views', 'open-sm');
              openSm && openSm.set('active', 1);
              editor.StyleManager.setTarget(`.${classTooltipBody}`, {
                targetIsClass: 1,
                stylable: stylableTooltip,
              })
            },
          },
        ]),
        ...propsTooltip,
      },

      init() {
        this.listenTo(this.components(), 'add remove', this.checkEmpty);
        this.checkEmpty();
      },

      checkEmpty() {
        const empty = !this.components().length;
        this[empty ? 'addClass' : 'removeClass'](`${classTooltipEmpty}`);
      }
    }, {
      isComponent(el) {
        if (el.hasAttribute && el.hasAttribute(attrTooltip)) {
          return { type: id };
        }
      }
    }),
    view: defaultView.extend({
      init() {
        !cssc.getClassRule(classTooltip) && createCssStyles();
      }
    }),
  });
}
