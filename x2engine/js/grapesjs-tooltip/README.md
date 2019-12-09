# GrapesJS Tooltip


Simple, CSS only, tooltip component for GrapesJS

[Demo](https://grapesjs.com/demo.html)

## Summary

* Plugin name: `grapesjs-tooltip`
* Components
  * `tooltip`
* Blocks
  * `tooltip`





## Options

| Option | Description | Default |
|-|-|-
| `id` | The ID used to create tooltip block and component | `tooltip` |
| `labelTooltip` | Label of the tooltip. Used for the block and component name | `Tooltip` |
| `blockTooltip` | Object to extend the default tooltip block, eg. { label: 'Tooltip', category: 'Extra', ... }. Pass a falsy value to avoid adding the block | `{}` |
| `propsTooltip` | Object to extend the default tooltip properties, eg. `{ name: 'Tooltip', droppable: false, ... }` | `{}` |
| `extendTraits` | A function which allows to extend default traits by receiving the original array and returning a new one | `traits => traits` |
| `attrTooltip` | Tooltip attribute prefix | `data-tooltip` |
| `classTooltip` | Tooltip class prefix | `tooltip-component` |
| `style` | Custom CSS styles, this will replace the default one | `''` |
| `styleAdditional` | Additional CSS styles | `''` |
| `privateClasses` | Make all tooltip relative classes private | `true` |
| `showTooltipOnStyle` | If true, force the tooltip to be shown when you're styling it | `true` |
| `stylableTooltip` | Indicate if the tooltip can be styled. You can also pass an array of which properties can be styled. Eg. `['color', 'background-color']` | `check the source...` |





## Download

* CDN
  * `https://unpkg.com/grapesjs-tooltip`
* NPM
  * `npm i grapesjs-tooltip`
* GIT
  * `git clone https://github.com/artf/grapesjs-tooltip.git`





## Usage

Directly in the browser
```html
<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
<script src="https://unpkg.com/grapesjs"></script>
<script src="path/to/grapesjs-tooltip.min.js"></script>

<div id="gjs"></div>

<script type="text/javascript">
  var editor = grapesjs.init({
      container : '#gjs',
      // ...
      plugins: ['grapesjs-tooltip'],
      pluginsOpts: {
        'grapesjs-tooltip': { /* options */ }
      }
  });
</script>
```

Modern javascript
```js
import grapesjs from 'grapesjs';
import pluginTooltip from 'grapesjs-tooltip';

const editor = grapesjs.init({
  container : '#gjs',
  // ...
  plugins: [pluginTooltip],
  pluginsOpts: {
    [pluginTooltip]: { /* options */ }
  }
  // or
  plugins: [
    editor => pluginTooltip(editor, { /* options */ }),
  ],
});
```





## Development

Clone the repository

```sh
$ git clone https://github.com/artf/grapesjs-tooltip.git
$ cd grapesjs-tooltip
```

Install dependencies

```sh
$ npm i
```

Start the dev server

```sh
$ npm start
```





## License

BSD 3-Clause
