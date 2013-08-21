<?php
/**
 * YiiDebugViewRenderer class file.
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 */

/**
 * YiiDebugViewRenderer represents an ...
 *
 * Description of YiiDebugViewRenderer
 *
 * @author Sergey Malyshev <malyshev.php@gmail.com>
 * @version $Id$
 * @package
 * @since 1.1.7
 */
class YiiDebugViewRenderer extends ProxyComponent
{

    //private $_fileExtension = '.php';

    protected $abstract = array(
        'fileExtension' => '.php',
    );

    protected $_debugStackTrace = array();
//
//    public function getFileExtension()
//    {
//        return $this->_fileExtension;
//    }

    public function getDebugStackTrace()
    {
        return $this->_debugStackTrace;
    }

    public function renderFile($context, $sourceFile, $data, $return)
    {
        $this->collectDebugInfo($context, $sourceFile, $data);

        if (false !== $this->getIsProxy())
        {
            return $this->instance->renderFile($context,$sourceFile,$data,$return);
        }
         return $context->renderInternal($sourceFile,$data,$return);
    }

    public function generateViewFile($sourceFile, $viewFile)
    {
        if (false !== $this->getIsProxy())
        {
            return $this->instance->generateViewFile($sourceFile, $viewFile);
        }
    }
    
    protected function getDebugBacktrace()
    {
      // @see "http://www.php.net/manual/en/function.debug-backtrace.php"
      // 
      // debug_backtrace Changelog
      // 
      // Version  Description
      // 5.4.0    Added the optional parameter limit.
      // 5.3.6    The parameter provide_object changed to options and
      //          additional option DEBUG_BACKTRACE_IGNORE_ARGS is added.
      // 5.2.5    Added the optional parameter provide_object.
      // 5.1.1    Added the current object as a possible return element.
      if (version_compare(PHP_VERSION, '5.4.0', '>='))
      {
        // signature is:
        // array debug_backtrace ([ int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT [, int $limit = 0 ]] )
        // 
        // possible values for $options:
        // - DEBUG_BACKTRACE_PROVIDE_OBJECT
        // - DEBUG_BACKTRACE_IGNORE_ARGS
        // - DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
        $debugBacktrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
      }
      elseif (version_compare(PHP_VERSION, '5.3.6', '>='))
      {
        // signature is:
        // array debug_backtrace ([ int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT ] )
        // 
        // possible values for $options:
        // - DEBUG_BACKTRACE_PROVIDE_OBJECT
        // - DEBUG_BACKTRACE_IGNORE_ARGS
        // - DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS
        $debugBacktrace = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT );
      }
      elseif (version_compare(PHP_VERSION, '5.2.5', '>='))
      {
        // signature is:
        // array debug_backtrace ([ bool $provide_object = TRUE ] )
        $debugBacktrace = debug_backtrace( true );
      }
      else /* version < 5.2.5 */
      {
        // signature is:
        // array debug_backtrace ( )
        $debugBacktrace = debug_backtrace();
      }
      
      return $debugBacktrace;
    }
    
    protected function collectDebugInfo($context, $sourceFile, $data)
    {
        if($context instanceof YiiDebugToolbar || false !== ($context instanceof YiiDebugToolbarPanel))
            return;

        $backTrace = $this->getDebugBacktrace();
        $backTraceItem = null;

        while($backTraceItem = array_shift($backTrace))
        {
            if(isset($backTraceItem['object']) && $backTraceItem['object'] && ($backTraceItem['object'] instanceof $context) && in_array($backTraceItem['function'], array(
                'render',
                'renderPartial'
            )) )
            {
                break;
            }
        }

        array_push($this->_debugStackTrace, array(
            'context'=>$context,
            'contextProperties'=>  get_object_vars($context),
            'action'=> $context instanceof CController ? $context->action : null,
            'actionParams'=> ($context instanceof CController && method_exists($context, 'getActionParams'))
                ? $context->actionParams
                : null,
            'route'=> $context instanceof CController ? $context->route : null,
            'sourceFile'=>$sourceFile,
            'data'=>$data,
            'backTrace'=>$backTraceItem,
            'reflection' => new ReflectionObject($context)
        ));
    }
    
}