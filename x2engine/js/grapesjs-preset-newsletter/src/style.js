define(function() {
  return (opt = {}) => {
    let editor = opt.editor;
    let sectors = editor.StyleManager.getSectors();
    editor.on('load', function() {
      sectors.reset();
      sectors.add(opt.styleManagerSectors);
    });
  };
});