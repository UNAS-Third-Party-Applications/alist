/**
 * Alist App
 * Defined an App to manage alist
 */
var AlistApp = AlistApp || {} //Define alist App namespace.
/**
 * Constructor UNAS App
 */
AlistApp.App = function () {
  this.id = 'Alist'
  this.name = 'Alist'
  this.version = '6.0.2'
  this.active = false
  this.menuIcon = '/apps/alist/images/logo.png?v=6.0.2&'
  this.shortcutIcon = '/apps/alist/images/logo.png?v=6.0.2&'
  this.entryUrl = '/apps/alist/index.html?v=6.0.2&'
  var self = this
  this.AlistAppWindow = function () {
    if (UNAS.CheckAppState('Alist')) {
      return false
    }
    self.window = new MUI.Window({
      id: 'AlistAppWindow',
      title: UNAS._('Alist'),
      icon: '/apps/alist/images/logo_small.png?v=6.0.2&',
      loadMethod: 'xhr',
      width: 750,
      height: 480,
      maximizable: false,
      resizable: true,
      scrollbars: false,
      resizeLimit: { x: [200, 2000], y: [150, 1500] },
      contentURL: '/apps/alist/index.html?v=6.0.2&',
      require: { css: ['/apps/alist/css/index.css'] },
      onBeforeBuild: function () {
        UNAS.SetAppOpenedWindow('Alist', 'AlistAppWindow')
      },
    })
  }
  this.AlistUninstall = function () {
    UNAS.RemoveDesktopShortcut('Alist')
    UNAS.RemoveMenu('Alist')
    UNAS.RemoveAppFromGroups('Alist', 'ControlPanel')
    UNAS.RemoveAppFromApps('Alist')
  }
  new UNAS.Menu(
    'UNAS_App_Internet_Menu',
    this.name,
    this.menuIcon,
    'Alist',
    '',
    this.AlistAppWindow
  )
  new UNAS.RegisterToAppGroup(
    this.name,
    'ControlPanel',
    {
      Type: 'Internet',
      Location: 1,
      Icon: this.shortcutIcon,
      Url: this.entryUrl,
    },
    {}
  )
  var OnChangeLanguage = function (e) {
    UNAS.SetMenuTitle('Alist', UNAS._('Alist')) //translate menu
    //UNAS.SetShortcutTitle('Alist', UNAS._('Alist'));
    if (typeof self.window !== 'undefined') {
      UNAS.SetWindowTitle('AlistAppWindow', UNAS._('Alist'))
    }
  }
  UNAS.LoadTranslation(
    '/apps/alist/languages/Translation?v=' + this.version,
    OnChangeLanguage
  )
  UNAS.Event.addEvent('ChangeLanguage', OnChangeLanguage)
  UNAS.CreateApp(
    this.name,
    this.shortcutIcon,
    this.AlistAppWindow,
    this.AlistUninstall
  )
}

new AlistApp.App()
