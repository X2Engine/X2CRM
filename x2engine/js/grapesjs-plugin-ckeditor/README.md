# GrapesJS CKEditor

This plugin replaces the default Rich Text Editor with the one from CKEditor

<p align="center"><img src="http://grapesjs.com/img/screen-ckeditor.jpg" alt="GrapesJS" align="center"/></p>
<br/>



## Summary

* Plugin
  * Name: `gjs-plugin-ckeditor`
  * Options:
      * `options` CKEditor's configuration object, eg. `{ language: 'en', toolbar: [...], ...}`
      * `position` Position side of the toolbar, default: `left`, options: `left|center|right`



## Download

* `npm i grapesjs-plugin-ckeditor` or `yarn add grapesjs-plugin-ckeditor`
* Latest release link https://github.com/artf/grapesjs-plugin-ckeditor/releases/latest



## Usage

```html
<link href="path/to/grapes.min.css" rel="stylesheet"/>
<script src="path/to/grapes.min.js"></script>
<script src="path/to/grapesjs-plugin-ckeditor.min.js"></script>

<div id="gjs"></div>

<script type="text/javascript">
  var editor = grapesjs.init({
      container : '#gjs',
      plugins: ['gjs-plugin-ckeditor'],
      pluginsOpts: {
        'gjs-plugin-ckeditor': {/* ...options */}
      }
  });
</script>
```



## Development

Clone the repository

```sh
$ git clone https://github.com/artf/grapesjs-plugin-ckeditor.git
$ cd grapesjs-plugin-ckeditor
```

Install dependencies

```sh
$ npm i
```

The plugin relies on GrapesJS and CKEditor via `peerDependencies` so you have to install them manually

```sh
$ npm i grapesjs ckeditor --no-save
```

Start the dev server

```sh
$ npm start
```


## License

BSD 3-Clause
