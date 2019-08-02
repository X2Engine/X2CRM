<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



/**
 * Custom URL parsing class
 *
 * The only change which has been made is the modification of a regex flag in URL
 * parsing. Ctrl + F for "X2CHANGE" to see the location of this change.
 *
 * @package application.components
 */
class X2UrlRule extends CUrlRule
{
	/**
	 * @var string the URL suffix used for this rule.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * Defaults to null, meaning using the value of {@link CUrlManager::urlSuffix}.
	 */
	public $urlSuffix;
	/**
	 * @var boolean whether the rule is case sensitive. Defaults to null, meaning
	 * using the value of {@link CUrlManager::caseSensitive}.
	 */
	public $caseSensitive;
	/**
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 */
	public $defaultParams=array();
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in the rule when creating a URL. Defaults to null, meaning using the value
	 * of {@link CUrlManager::matchValue}. When this property is false, it means
	 * a rule will be used for creating a URL if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	public $matchValue;
	/**
	 * @var string the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
	 * If this rule can match multiple verbs, please separate them with commas.
	 * If this property is not set, the rule can match any verb.
	 * Note that this property is only used when parsing a request. It is ignored for URL creation.
	 * @since 1.1.7
	 */
	public $verb;
	/**
	 * @var boolean whether this rule is only used for request parsing.
	 * Defaults to false, meaning the rule is used for both URL parsing and creation.
	 * @since 1.1.7
	 */
	public $parsingOnly=false;
	/**
	 * @var string the controller/action pair
	 */
	public $route;
	/**
	 * @var array the mapping from route param name to token name (e.g. _r1=><1>)
	 */
	public $references=array();
	/**
	 * @var string the pattern used to match route
	 */
	public $routePattern;
	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;
	/**
	 * @var string template used to construct a URL
	 */
	public $template;
	/**
	 * @var array list of parameters (name=>regular expression)
	 */
	public $params=array();
	/**
	 * @var boolean whether the URL allows additional parameters at the end of the path info.
	 */
	public $append;
	/**
	 * @var boolean whether host info should be considered for this rule
	 */
	public $hasHostInfo;

	/**
	 * Constructor.
	 * @param string $route the route of the URL (controller/action)
	 * @param string $pattern the pattern for matching the URL
	 */
	public function __construct($route,$pattern)
	{
		if(is_array($route))
		{
			foreach(array('urlSuffix', 'caseSensitive', 'defaultParams', 'matchValue', 'verb', 'parsingOnly') as $name)
			{
				if(isset($route[$name]))
					$this->$name=$route[$name];
			}
			if(isset($route['pattern']))
				$pattern=$route['pattern'];
			$route=$route[0];
		}
		$this->route=trim($route,'/');

		$tr2['/']=$tr['/']='\\/';

		if(strpos($route,'<')!==false && preg_match_all('/<(\w+)>/',$route,$matches2))
		{
			foreach($matches2[1] as $name)
				$this->references[$name]="<$name>";
		}

		$this->hasHostInfo=!strncasecmp($pattern,'http://',7) || !strncasecmp($pattern,'https://',8);

		if($this->verb!==null)
			$this->verb=preg_split('/[\s,]+/',strtoupper($this->verb),-1,PREG_SPLIT_NO_EMPTY);

		if(preg_match_all('/<(\w+):?(.*?)?>/',$pattern,$matches))
		{
			$tokens=array_combine($matches[1],$matches[2]);
			foreach($tokens as $name=>$value)
			{
				if($value==='')
					$value='[^\/]+';
				$tr["<$name>"]="(?P<$name>$value)";
				if(isset($this->references[$name]))
					$tr2["<$name>"]=$tr["<$name>"];
				else
					$this->params[$name]=$value;
			}
		}
		$p=rtrim($pattern,'*');
		$this->append=$p!==$pattern;
		$p=trim($p,'/');
		$this->template=preg_replace('/<(\w+):?.*?>/','<$1>',$p);
		$this->pattern='/^'.strtr($this->template,$tr).'\/';
		if($this->append)
			$this->pattern.='/u';
		else
			$this->pattern.='$/u';

        // X2CHANGE
        /*
         * Because of the way our URLs work that we want to hide module/controller
         * names from our path so that we see "contacts/index" instead of
         * "contacts/contacts/index" we need to allow for multiple named subpatterns
         * in a regex with the same name. The only way to do this is to add the
         * "?J" modifier to the start of the pattern, which has been added in the
         * routePattern property below.
         */
		if($this->references!==array())
			$this->routePattern='/^(?J)'.strtr($this->route,$tr2).'$/u';

		if(YII_DEBUG && @preg_match($this->pattern,'test')===false)
			throw new CException(Yii::t('yii','The URL pattern "{pattern}" for route "{route}" is not a valid regular expression.',
				array('{route}'=>$route,'{pattern}'=>$pattern)));
	}

	/**
	 * Creates a URL based on this rule.
	 * @param CUrlManager $manager the manager
	 * @param string $route the route
	 * @param array $params list of parameters
	 * @param string $ampersand the token separating name-value pairs in the URL.
	 * @return mixed the constructed URL or false on error
	 */
	public function createUrl($manager,$route,$params,$ampersand)
	{
		if($this->parsingOnly)
			return false;

		if($manager->caseSensitive && $this->caseSensitive===null || $this->caseSensitive)
			$case='';
		else
			$case='i';

		$tr=array();
		if($route!==$this->route)
		{
			if($this->routePattern!==null && preg_match($this->routePattern.$case,$route,$matches))
			{
				foreach($this->references as $key=>$name)
					$tr[$name]=$matches[$key];
			}
			else
				return false;
		}

		foreach($this->defaultParams as $key=>$value)
		{
			if(isset($params[$key]))
			{
				if($params[$key]==$value)
					unset($params[$key]);
				else
					return false;
			}
		}

		foreach($this->params as $key=>$value)
			if(!isset($params[$key]))
				return false;

		if($manager->matchValue && $this->matchValue===null || $this->matchValue)
		{
			foreach($this->params as $key=>$value)
			{
				if(!preg_match('/'.$value.'/'.$case,$params[$key]))
					return false;
			}
		}

		foreach($this->params as $key=>$value)
		{
			$tr["<$key>"]=urlencode($params[$key]);
			unset($params[$key]);
		}

		$suffix=$this->urlSuffix===null ? $manager->urlSuffix : $this->urlSuffix;

		$url=strtr($this->template,$tr);

		if($this->hasHostInfo)
		{
			$hostInfo=Yii::app()->getRequest()->getHostInfo();
			if(stripos($url,$hostInfo)===0)
				$url=substr($url,strlen($hostInfo));
		}

		if(empty($params))
			return $url!=='' ? $url.$suffix : $url;

		if(false)
			$url.='/'.$manager->createPathInfo($params,'/','/').$suffix;
		else
		{
			if($url!=='')
				$url.=$suffix;
			$url.='?'.$manager->createPathInfo($params,'=',$ampersand);
		}

		return $url;
	}

	/**
	 * Parses a URL based on this rule.
	 * @param CUrlManager $manager the URL manager
	 * @param CHttpRequest $request the request object
	 * @param string $pathInfo path info part of the URL
	 * @param string $rawPathInfo path info that contains the potential URL suffix
	 * @return mixed the route that consists of the controller ID and action ID or false on error
	 */
	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if($this->verb!==null && !in_array($request->getRequestType(), $this->verb, true))
			return false;

		if($manager->caseSensitive && $this->caseSensitive===null || $this->caseSensitive)
			$case='';
		else
			$case='i';

		if($this->urlSuffix!==null)
			$pathInfo=$manager->removeUrlSuffix($rawPathInfo,$this->urlSuffix);

		// URL suffix required, but not found in the requested URL
		if($manager->useStrictParsing && $pathInfo===$rawPathInfo)
		{
			$urlSuffix=$this->urlSuffix===null ? $manager->urlSuffix : $this->urlSuffix;
			if($urlSuffix!='' && $urlSuffix!=='/')
				return false;
		}

		if($this->hasHostInfo)
			$pathInfo=strtolower($request->getHostInfo()).rtrim('/'.$pathInfo,'/');

		$pathInfo.='/';

		if(preg_match($this->pattern.$case,$pathInfo,$matches))
		{
			foreach($this->defaultParams as $name=>$value)
			{
				if(!isset($_GET[$name]))
					$_REQUEST[$name]=$_GET[$name]=$value;
			}
			$tr=array();
			foreach($matches as $key=>$value)
			{
				if(isset($this->references[$key]))
					$tr[$this->references[$key]]=$value;
				else if(isset($this->params[$key]))
					$_REQUEST[$key]=$_GET[$key]=$value;
			}
			if($pathInfo!==$matches[0]) // there're additional GET params
				$manager->parsePathInfo(ltrim(substr($pathInfo,strlen($matches[0])),'/'));
			if($this->routePattern!==null)
				return strtr($this->route,$tr);
			else
				return $this->route;
		}
		else
			return false;
	}
}
