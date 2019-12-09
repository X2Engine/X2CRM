define(function() {
  const tltAttr = 'title';
  const tltPosAttr = 'data-tooltip-pos';
  let updateTooltip = (coll) => {
    coll.each((item) => {
      var attrs = item.get('attributes');
      attrs[tltPosAttr] = 'bottom';
      item.set('attributes', attrs);
    });
  }
  return (opt = {}) => {
    let editor = opt.editor;
    let pnm = editor.Panels;
    let optPanel = pnm.getPanel('options');
    pnm.addButton('options', {
      id: opt.cmdOpenImport,
      className: 'fa fa-download',
      command: opt.cmdOpenImport,
      attributes: {[tltAttr]: opt.modalTitleImport},
    });
    pnm.addButton('options', {
      id: opt.cmdTglImages,
      className: 'fa fa-warning',
      command: opt.cmdTglImages,
      attributes: {[tltAttr]: opt.cmtTglImagesLabel},
    });
    if(optPanel){
      // Fix tooltip position
      var cmdBtns = optPanel.get('buttons');
      cmdBtns.each((btn) => {
        var attrs = btn.get('attributes');
        attrs[tltPosAttr] = 'bottom';
        btn.set('attributes', attrs);
      });
      // Remove preview
      let prvBtn = pnm.addButton('options', 'preview');
      prvBtn && cmdBtns.remove(prvBtn);
    }
    // Clean commands panel
    let cmdPanel = pnm.getPanel('commands');
    if(cmdPanel){
      let cmdBtns = cmdPanel.get('buttons');
      cmdBtns.reset();
      cmdBtns.add([{
        id: 'undo',
        className: 'fa fa-undo',
        command: 'undo',
        attributes: {[tltAttr]: opt.cmdBtnUndoLabel}
      }, {
        id: 'redo',
        className: 'fa fa-repeat',
        command: 'redo',
        attributes: {[tltAttr]: opt.cmdBtnRedoLabel}
      }]);
      updateTooltip(cmdBtns);
    }
    // Turn off default devices select and create new one
    editor.getConfig().showDevices = 0;
    let devicePanel = pnm.addPanel({
      id: 'devices-c'
    });
    let deviceBtns = devicePanel.get('buttons');
    devicePanel.get('buttons').add([{
      id: 'deviceDesktop',
      command: 'set-device-desktop',
      className: 'fa fa-desktop',
      attributes: {[tltAttr]: opt.cmdBtnDesktopLabel},
      active: 1,
    }, {
      id: 'deviceTablet',
      command: 'set-device-tablet',
      className: 'fa fa-tablet',
      attributes: {[tltAttr]: opt.cmdBtnTabletLabel},
    }, {
      id: 'deviceMobile',
      command: 'set-device-mobile',
      className: 'fa fa-mobile',
      attributes: {[tltAttr]: opt.cmdBtnMobileLabel},
    }])
    updateTooltip(deviceBtns);
    //let viewPanel = pnm.getPanel('views');
    //viewPanel && updateTooltip(viewPanel.get('buttons'));
  };
})
