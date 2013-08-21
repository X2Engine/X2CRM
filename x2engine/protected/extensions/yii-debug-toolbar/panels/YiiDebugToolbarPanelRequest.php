<?php
/**
 * YiiDebugToolbarPanelRequest class file.
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 */


/**
 * YiiDebugToolbarPanelRequest class
 *
 * Description of YiiDebugToolbarPanelRequest
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 * @author Igor Golovanov <igor.golovanov@gmail.com>
 * @version $Id$
 * @package YiiDebugToolbar
 * @since 1.1.7
 */
class YiiDebugToolbarPanelRequest extends YiiDebugToolbarPanel
{
    /**
     * {@inheritdoc}
     */
    public function getMenuTitle()
    {
        return YiiDebug::t('Request');
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return YiiDebug::t('Request');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {}

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->render('request', array(
            'server' => $_SERVER,
            'cookies' => $_COOKIE,
            'session' => isset($_SESSION) ? $_SESSION : null,
            'post' => $_POST,
            'get' => $_GET,
            'files' => $_FILES,
        ));
    }
}
