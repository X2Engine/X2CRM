<?php
/**
 * YiiDebugToolbarRouter class file.
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 */

/**
 * YiiDebugToolbarRouter represents an ...
 *
 * Description of YiiDebugToolbarRouter
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 * @version $Id$
 * @package YiiDebugToolbar
 * @since 1.1.7
 */
class YiiDebugToolbarRoute extends CLogRoute
{

    private $_panels = array(
        'YiiDebugToolbarPanelServer',
        'YiiDebugToolbarPanelRequest',
        'YiiDebugToolbarPanelSettings',
        'YiiDebugToolbarPanelViewsRendering',
        'YiiDebugToolbarPanelSql',
        'YiiDebugToolbarPanelLogging',
    );

    /**
     * The filters are given in an array, each filter being:
     * - a normal IP (192.168.0.10 or '::1')
     * - an incomplete IP (192.168.0.* or 192.168.0.)
     * - a CIDR mask (192.168.0.0/24)
     * - "*" for everything.
     */
    public $ipFilters=array('127.0.0.1','::1');

    /**
     * If true, then after reloading the page will open the current panel.
     * @var bool
     */
    public $openLastPanel = true;

    /**
     * Whitelist for response content types. DebugToolbarRoute won't write any
     * output if the server generates output that isn't listed here (json, xml,
     * files, ...)
     * @var array of content type strings (in lower case)
     */
    public $contentTypeWhitelist = array(
      // Yii framework doesn't seem to send content-type header by default.
      '',
      'text/html',
      'application/xhtml+xml',
    );

    private $_toolbarWidget,
            $_startTime,
            $_endTime;


    private $_proxyMap = array(
        'viewRenderer' => 'YiiDebugViewRenderer'
    );

    public function setPanels(array $pannels)
    {
        $selfPanels = array_fill_keys($this->_panels, array());
        $this->_panels = array_merge($selfPanels, $pannels);
    }

    public function getPanels()
    {
        return $this->_panels;
    }

    public function getStartTime()
    {
        return $this->_startTime;
    }

    public function getEndTime()
    {
        return $this->_endTime;
    }

    public function getLoadTime()
    {
        return ($this->endTime-$this->startTime);
    }

    protected function getToolbarWidget()
    {
        if (null === $this->_toolbarWidget)
        {
            $this->_toolbarWidget = Yii::createComponent(array(
                'class'=>'YiiDebugToolbar',
                'panels'=> $this->panels
            ), $this);
        }
        return $this->_toolbarWidget;
    }

    public function init()
    {
        $this->_startTime=microtime(true);

        parent::init();

        $this->enabled && $this->enabled = ($this->allowIp(Yii::app()->request->userHostAddress)
                && !Yii::app()->getRequest()->getIsAjaxRequest() && (Yii::app() instanceof CWebApplication));

        if ($this->enabled)
        {
            Yii::app()->attachEventHandler('onBeginRequest', array($this, 'onBeginRequest'));
            Yii::app()->attachEventHandler('onEndRequest', array($this, 'onEndRequest'));
            Yii::setPathOfAlias('yii-debug-toolbar', dirname(__FILE__));
            Yii::app()->setImport(array(
                'yii-debug-toolbar.*',
                'yii-debug-toolbar.components.*'
            ));
            $this->categories = '';
            $this->levels='';
        }
    }

    protected function onBeginRequest(CEvent $event)
    {
        $this->initComponents();

        $this->getToolbarWidget()
             ->init();
    }

    protected function initComponents()
    {
        foreach ($this->_proxyMap as $name=>$class)
        {
            $instance = Yii::app()->getComponent($name);
            if (null !== ($instance))
            {
                Yii::app()->setComponent($name, null);
            }
            $this->_proxyMap[$name] = array(
                'class'=>$class,
                'instance' => $instance
            );
        }
        Yii::app()->setComponents($this->_proxyMap, false);
    }


    /**
     * Processes the current request.
     * It first resolves the request into controller and action,
     * and then creates the controller to perform the action.
     */
    private function processRequest()
    {
        if(is_array(Yii::app()->catchAllRequest) && isset(Yii::app()->catchAllRequest[0]))
        {
            $route=Yii::app()->catchAllRequest[0];
            foreach(array_splice(Yii::app()->catchAllRequest,1) as $name=>$value)
                $_GET[$name]=$value;
        }
        else
            $route=Yii::app()->getUrlManager()->parseUrl(Yii::app()->getRequest());
        Yii::app()->runController($route);
    }

    protected function onEndRequest(CEvent $event)
    {

    }

    public function collectLogs($logger, $processLogs=false)
    {
        parent::collectLogs($logger, $processLogs);
    }

    protected function processLogs($logs)
    {
        $this->_endTime = microtime(true);
        // disable log route based on white list
        $this->enabled = $this->enabled && $this->checkContentTypeWhitelist();
        $this->enabled && $this->getToolbarWidget()->run();
    }

    private function checkContentTypeWhitelist()
    {
      $contentType = '';

      foreach (headers_list() as $header) {
        list($key, $value) = explode(':', $header);
        $value = ltrim($value, ' ');
        if (strtolower($key) === 'content-type') {
          // Split encoding if exists
          $contentType = explode(";", strtolower($value));
          $contentType = current($contentType);
          break;
        }
      }

      return in_array( $contentType, $this->contentTypeWhitelist );
    }

    /**
     * Checks to see if the user IP is allowed by {@link ipFilters}.
     * @param string $ip the user IP
     * @return boolean whether the user IP is allowed by {@link ipFilters}.
     */
    protected function allowIp($ip)
    {
        foreach ($this->ipFilters as $filter)
        {
            $filter = trim($filter);
            // normal or incomplete IPv4
            if (preg_match('/^[\d\.]*\*?$/', $filter)) {
                $filter = rtrim($filter, '*');
                if (strncmp($ip, $filter, strlen($filter)) === 0)
                {
                    return true;
                }
            }
            // CIDR
            else if (preg_match('/^([\d\.]+)\/(\d+)$/', $filter, $match))
            {
                if (self::matchIpMask($ip, $match[1], $match[2]))
                {
                    return true;
                }
            }
            // IPv6
            else if ($ip === $filter)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if an IP matches a CIDR mask.
     *
     * @param integer|string $ip IP to check.
     * @param integer|string $matchIp Radical of the mask (e.g. 192.168.0.0).
     * @param integer $maskBits Size of the mask (e.g. 24).
     */
    protected static function matchIpMask($ip, $maskIp, $maskBits)
    {
        $mask =~ (pow(2, 32-$maskBits)-1);
        if (false === is_int($ip))
        {
            $ip = ip2long($ip);
        }
        if (false === is_int($maskIp))
        {
            $maskIp = ip2long($maskIp);
        }
        if (($ip & $mask) === ($maskIp & $mask))
        {
            return true;
        } else {
            return false;
        }
    }
}
