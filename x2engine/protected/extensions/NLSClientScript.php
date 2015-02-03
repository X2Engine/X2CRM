<?php
/**
 * NLSClientScript v6.4
 * 
 * 6.4 Changes:
 *  - followed the change of the registerScriptFile() arguments in yii 1.1.14
 *  - removed/added some comments
 * 
 * 6.3 Changes:
 * - serious bug fixed: filtering duplications (usually) failed when 
 * js merging applied for xhr-requested views
 * - new params: mergeIfXhr, mergeJsExcludePattern, mergeJsIncludePattern, 
 * mergeCssExcludePattern, mergeCssIncludePattern, resMap2Request
 * - appended an extra ; to the js files when merging, for safety
 * - some other small improvements
 * 
 * 
 * 
 * a single-file Yii CClientScript extension for 
 * - preventing multiple loading of javascript files
 * - merging, caching registered javascript and css files
 * 
 * The extension is based on the great idea of Eirik Hoem, see
 * http://www.eirikhoem.net/blog/2011/08/29/yii-framework-preventing-duplicate-jscss-includes-for-ajax-requests/
 * 
 * This extension embeds a vendor: JSMin.php in a minified format.
 * 
 * 
 * 
 * Basic usage: set the class for the clientScript component in /protected/config/main.php, like
 *  ...
 *   'components'=>array(
 *     ...
 *     'clientScript' => array(
 *       'class'=>'your.path.to.NLSClientScript',
 *       [parameters]
 *     )
 *     ...
 *   )
 *  ...
 * 
 * Parameters: see the phpdoc comments 
 * 
 * Important notes - before you ask:
 * 
 * - The extension does NOT prevent the multiple loading of CSS files.
 * I simply couldn't find a way how that would be managed fine (too long to explain here). 
 * 
 * - This extension does not prevent to load the same script content from different paths. 
 * So eg. if you published the same js file into different asset directories, this extension won't prevent to load both.
 * 
 * - When merging files, the files are loaded by CURL so remote files can also be merged and cached.
 * 
 * - The extension caches the merged files into the root of the application assets root, usually APPDIR/assets/.
 * 
 * - The extension doesn't watch wether a js/css file has been changed. If you set the merge funtionality and some file changed, you need to delete the cached merged file manually, otherwise you'll get the old merged one.
 * 
 * - The merged files contain the list of the url of the merged files as a starting comment.
 */










 
 
/**
 * JSMin.php - modified PHP implementation of Douglas Crockford's JSMin.
 *
 * <code>
 * $minifiedJs = JSMin::minify($js);
 * </code>
 *
 * This is a modified port of jsmin.c. Improvements:
 *
 * Does not choke on some regexp literals containing quote characters. E.g. /'/
 *
 * Spaces are preserved after some add/sub operators, so they are not mistakenly
 * converted to post-inc/dec. E.g. a + ++b -> a+ ++b
 *
 * Preserves multi-line comments that begin with /*!
 *
 * PHP 5 or higher is required.
 *
 * Permission is hereby granted to use this version of the library under the
 * same terms as jsmin.c, which has the following license:
 *
 * --
 * Copyright (c) 2002 Douglas Crockford  (www.crockford.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * --
 *
 *
 * @package JSMin
 * @author Ryan Grove <ryan@wonko.com>
 * @copyright 2002 Douglas Crockford <douglas@crockford.com> (jsmin.c)
 * @copyright 2008 Ryan Grove <ryan@wonko.com> (PHP port)
 * @copyright 2012 Adam Goforth <aag@adamgoforth.com> (Updates)
 * @copyright 2012 Erik Amaru Ortiz <aortiz.erik@gmail.com> (Updates)
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @version ${version}
 * @link https://github.com/rgrove/jsmin-php
 */
if (!class_exists('JSMin', false)) {
class JSMin{const ORD_LF=10;const ORD_SPACE=32;const ACTION_KEEP_A=1;const ACTION_DELETE_A=2;const ACTION_DELETE_A_B=3;protected$a="\n";protected$b='';protected$input='';protected$inputIndex=0;protected$inputLength=0;protected$lookAhead=null;protected$output='';protected$lastByteOut='';static function minify($L){$J=new JSMin($L);return$J->min();}function __construct($K){$this->input=str_replace("\r\n","\n",$K);$this->inputLength=strlen($this->input);}protected function action($D){if($D===self::ACTION_DELETE_A_B&&$this->b===' '&&($this->a==='+'||$this->a==='-')){if($this->input[$this->inputIndex]===$this->a){$D=self::ACTION_KEEP_A;}}switch($D){case self::ACTION_KEEP_A:$this->output.=$this->a;$this->lastByteOut=$this->a;case self::ACTION_DELETE_A:$this->a=$this->b;if($this->a==="'"||$this->a==='"'){$G=$this->a;while(true){$this->output.=$this->a;$this->lastByteOut=$this->a;$this->a=$this->get();if($this->a===$this->b){break;}if(ord($this->a)<=self::ORD_LF){throw new JSMin_UnterminatedStringException('Unterminated string literal.'.$this->inputIndex.": {$G}");}$G.=$this->a;if($this->a==='\\'){$this->output.=$this->a;$this->lastByteOut=$this->a;$this->a=$this->get();$G.=$this->a;}}}case self::ACTION_DELETE_A_B:$this->b=$this->next();if($this->b==='/'&&$this->isRegexpLiteral()){$this->output.=$this->a.$this->b;$E='/';while(true){$this->a=$this->get();$E.=$this->a;if($this->a==='['){while(true){$this->output.=$this->a;$this->a=$this->get();if($this->a===']'){break;}elseif($this->a==='\\'){$this->output.=$this->a;$this->a=$this->get();$E.=$this->a;}elseif(ord($this->a)<=self::ORD_LF){throw new JSMin_UnterminatedRegExpException('Unterminated regular expression set in regex literal.'.$this->inputIndex.": {$E}");}}}elseif($this->a==='/'){break;}elseif($this->a==='\\'){$this->output.=$this->a;$this->a=$this->get();$E.=$this->a;}elseif(ord($this->a)<=self::ORD_LF){throw new JSMin_UnterminatedRegExpException('Unterminated regular expression literal.'.$this->inputIndex.": {$E}");}$this->output.=$this->a;$this->lastByteOut=$this->a;}$this->b=$this->next();}}}protected function isRegexpLiteral(){if(false!==strpos("\n{;(,=:[!&|?",$this->a)){return true;}if(' '===$this->a){$H=strlen($this->output);if($H<2){return true;}if(preg_match('/(?:case|else|in|return|typeof)$/',$this->output,$I)){if($this->output===$I[0]){return true;}$M=substr($this->output,$H-strlen($I[0])-1,1);if(!$this->isAlphaNum($M)){return true;}}}return false;}protected function get(){$C=$this->lookAhead;$this->lookAhead=null;if($C===null){if($this->inputIndex<$this->inputLength){$C=$this->input[$this->inputIndex];$this->inputIndex+=1;}else{return null;}}if($C==="\r"||$C==="\n"){return"\n";}if(ord($C)<self::ORD_SPACE){return' ';}return$C;}protected function isAlphaNum($C){return(preg_match('/^[0-9a-zA-Z_\\$\\\\]$/',$C)||ord($C)>126);}protected function singleLineComment(){$B='';while(true){$A=$this->get();$B.=$A;if(ord($A)<=self::ORD_LF){if(preg_match('/^\\/@(?:cc_on|if|elif|else|end)\\b/',$B)){return"/{$B}";}return$A;}}}protected function multipleLineComment(){$this->get();$B='';while(true){$A=$this->get();if($A==='*'){if($this->peek()==='/'){$this->get();if(0===strpos($B,'!')){return"\n/*!".substr($B,1)."*/\n";}if(preg_match('/^@(?:cc_on|if|elif|else|end)\\b/',$B)){return"/*{$B}*/";}return' ';}}elseif($A===null){throw new JSMin_UnterminatedCommentException("JSMin: Unterminated comment at byte ".$this->inputIndex.": /*{$B}");}$B.=$A;}}protected function min(){if($this->output!==''){return$this->output;}if(0==strncmp($this->peek(),"\xef",1)){$this->get();$this->get();$this->get();}$F=null;if(function_exists('mb_strlen')&&((int)ini_get('mbstring.func_overload')&2)){$F=mb_internal_encoding();mb_internal_encoding('8bit');}$this->input=str_replace("\r\n","\n",$this->input);$this->inputLength=strlen($this->input);$this->action(self::ACTION_DELETE_A_B);while($this->a!==null){$D=self::ACTION_KEEP_A;if($this->a===' '){if(($this->lastByteOut==='+'||$this->lastByteOut==='-')&&($this->b===$this->lastByteOut)){}elseif(!$this->isAlphaNum($this->b)){$D=self::ACTION_DELETE_A;}}elseif($this->a==="\n"){if($this->b===' '){$D=self::ACTION_DELETE_A_B;}elseif($this->b===null||(false===strpos('{[(+-!~',$this->b)&&!$this->isAlphaNum($this->b))){$D=self::ACTION_DELETE_A;}}elseif(!$this->isAlphaNum($this->a)){if($this->b===' '||($this->b==="\n"&&(false===strpos('}])+-"\'',$this->a)))){$D=self::ACTION_DELETE_A_B;}}$this->action($D);}$this->output=trim($this->output);if($F!==null){mb_internal_encoding($F);}return$this->output;}protected function next(){$A=$this->get();if($A!=='/'){return$A;}switch($this->peek()){case'/':return$this->singleLineComment();case'*':return$this->multipleLineComment();default:return$A;}}protected function peek(){$this->lookAhead=$this->get();return$this->lookAhead;}}class JSMin_UnterminatedStringException extends Exception{}class JSMin_UnterminatedCommentException extends Exception{}class JSMin_UnterminatedRegExpException extends Exception{}
}

/**
 * @author nlac
 */
class NLSClientScript extends CClientScript {

/**
 * Public properties
**/

/**
 * @param string $includePattern
 * a javascript regex eg. '/\/scripts/' - if set, only the matched URLs will be filtered, defaults to null
 * (can be set to string 'null' also to ignore it)
**/
	public $includePattern = null;

/**
 * @param string $excludePattern
 * a javascript regex eg. '/\/raw/' - if set, the matched URLs won't be filtered, defaults to null
 * (can be set to string 'null' also to ignore it)
**/
	public $excludePattern = null;

/**
 * @param boolean $mergeJs
 * merge or not the registered script files, defaults to false
**/
	public $mergeJs = true;

/**
 * @param boolean $compressMergedJs
 * minify or not the merged js file, defaults to false
**/
	public $compressMergedJs = false;

/**
 * @param boolean $mergeCss
 * merge or not the registered css files, defaults to false
**/
	public $mergeCss = true;

/**
 * @param boolean $compressMergeCss
 * minify or not the merged css file, defaults to false
**/
	public $compressMergedCss = false;

/**
 * @param int $mergeAbove
 * only merges if there are more than mergeAbove file registered to be included at a position
 **/
	public $mergeAbove = 1;

/**
 * @param string $mergeJsExcludePattern
 * regex for php. the matched URLs won't be filtered
 **/
	public $mergeJsExcludePattern = null;

/**
 * @param string $mergeJsIncludePattern
 * regex for php. the matched URLs will be filtered
 **/
	public $mergeJsIncludePattern = null;

/**
 * @param string $mergeCssExcludePattern
 * regex for php. the matched URLs won't be filtered
 **/
	public $mergeCssExcludePattern = null;

/**
 * @param string $mergeCssIncludePattern
 * regex for php. the matched URLs will be filtered
 **/
	public $mergeCssIncludePattern = null;

/**
 * @param boolean $mergeIfXhr
 * if true then js files will be merged even if the request rendering the view is ajax
 * (if $mergeJs and $mergeAbove conds are satisfied)
 * defaults to false - no js merging if the view is requested by ajax
 **/
	public $mergeIfXhr = false;
	
/**
 * @param string $resMap2Request
 * code of a js function, prepares a get url by adding the script url hashes already in the dom
 * (has effect only if mergeIfXhr is true)
 */
	public $resMap2Request = 'function(url){
		if (!url.match(/\?/))
			url += "?";
		return url + "&nlsc_map=" + $.nlsc.smap();
	};';

/**
 * @param string $serverBaseUrl
 * used to transform relative urls to absolute (for CURL)
 * you may define the url of the DOCROOT on the server (defaults to a composed value from the $_SERVER members) 
 **/
	public $serverBaseUrl = '';

/**
 * @param string $appVersion
 * Optional, version of the application.
 * If set to not empty, will be appended to the merged js/css urls (helps to handle cached resources).
 **/
	public $appVersion = '';

/**
 * @param int $curlTimeOut
 * see http://php.net/manual/en/function.curl-setopt.php
 **/
	public $curlTimeOut = 10;

/**
 * @param int $curlConnectionTimeOut
 * see http://php.net/manual/en/function.curl-setopt.php
 **/
	public $curlConnectionTimeOut = 10;




/**
 * @param object $ch
 * CURL resuurce
 */
	protected $ch = null;

	protected function toAbsUrl($relUrl) {
		return preg_match('&^http(s?)://&',$relUrl) ? $relUrl : rtrim($this->serverBaseUrl,'/') . '/' . ltrim($relUrl,'/');
	}
	
	protected function hashedName($name, $ext = 'js') {
		return 'nls' . crc32($name) . ( ($ext=='js'&&$this->compressMergedJs)||($ext=='css'&&$this->compressMergedCss) ? '-min':'') . '.' . $ext .
			($this->appVersion ? ('?' . $this->appVersion) : '');
	}

	//Simple css minifier script
	//code based on: http://www.lateralcode.com/css-minifier/
	protected static function minifyCss($css) {
		return trim(
			str_replace(
				array('; ', ': ', ' {', '{ ', ', ', '} ', ';}'), 
				array(';',  ':',  '{',  '{',  ',',  '}',  '}' ), 
				preg_replace('/\s+/', ' ', $css)
			)
		);
	}

	protected function initCurlHandler() {
		$this->ch = curl_init();

		curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($this->ch, CURLOPT_HEADER, false);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->curlConnectionTimeOut);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->curlTimeOut);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);

		return $this->ch;
	}
	
/**
 * Simple string hash, implemented also in the js part
 */
	protected function h($s) {
		$h = 0; $len = strlen($s);
		for ($i = 0; $i < $len; $i++) {
			$h = (($h<<5)-$h)+ord($s[$i]);
			$h &= 1073741823;
		}
		return $h;
	}

	protected function _mergeJs($pos) {
		$smap = null;
		if (Yii::app()->request->isAjaxRequest) {
			//do not merge for ajax requests
			if (!$this->mergeIfXhr)
				return;

			if ($smap = @$_REQUEST['nlsc_map'])
				$smap = @json_decode($smap);
		}
		
		if ($this->mergeJs && !empty($this->scriptFiles[$pos]) && count($this->scriptFiles[$pos]) > $this->mergeAbove) {
			$finalScriptFiles = array();
			$name = '/** Content:
';
			$scriptFiles = array();
			foreach($this->scriptFiles[$pos] as $src=>$scriptFile) {

				$absUrl = $this->toAbsUrl($src);//from yii 1.1.14 $scriptFile can be an array
				
				if ($this->mergeJsExcludePattern && preg_match($this->mergeJsExcludePattern, $absUrl)) {
					$finalScriptFiles[$src] = $scriptFile;
					continue;
				}

				if ($this->mergeJsIncludePattern && !preg_match($this->mergeJsIncludePattern, $absUrl)) {
					$finalScriptFiles[$src] = $scriptFile;
					continue;					
				}

				$h = $this->h($absUrl);
				if ($smap && in_array($h, $smap))
					continue;
				
				//storing hash
				$scriptFiles[$absUrl] = $h;
				
				$name .= $src . '
';
			}

			if (count($scriptFiles) <= $this->mergeAbove)
				return;
			
			$name .= '*/
';
			$hashedName = $this->hashedName($name,'js');
			$path = Yii::app()->assetManager->basePath . '/' . $hashedName;
			$path = preg_replace('#\\?.*$#','',$path);
			$url = Yii::app()->assetManager->baseUrl . '/'. $hashedName;

			if (!file_exists($path)) {
				$merged = '';
				if (!$this->ch)
					$this->initCurlHandler();

				$nlsCode = ';if (!$.nlsc) $.nlsc={resMap:{}};
';
				foreach($scriptFiles as $absUrl=>$h) {
					curl_setopt($this->ch, CURLOPT_URL, $absUrl);
					$ret = curl_exec($this->ch);
					$err = curl_error($this->ch);
					if (!$err) {
						$merged .= ($ret.'
;');
						$nlsCode .= '$.nlsc.resMap["' . $absUrl . '"]="' . $h . '";
';
					} else {
						$merged .= '
/*
error downloading ' . $absUrl . ':' . $err . '
curl_info:' . print_r(curl_getinfo($this->ch), true) . '
*/
';
					}
				}

				curl_close($this->ch);
				$this->ch = null;

				if ($this->compressMergedJs)
					$merged = JSMin::minify($merged);
	
				file_put_contents($path, $name . $merged . $nlsCode);
			}
			
			$finalScriptFiles[$url] = $url;
			$this->scriptFiles[$pos] = $finalScriptFiles;
		}
	}


	protected function _mergeCss() {

		if ($this->mergeCss && !empty($this->cssFiles)) {
			
			$newCssFiles = array();
			$names = array();
			$files = array();
			foreach($this->cssFiles as $url=>$media) {
				$absUrl = $this->toAbsUrl($url);
				
				if ($this->mergeCssExcludePattern && preg_match($this->mergeCssExcludePattern, $absUrl)) {
					$newCssFiles[$url] = $media;
					continue;
				}
					
				if ($this->mergeCssIncludePattern && !preg_match($this->mergeCssIncludePattern, $absUrl)) {
					$newCssFiles[$url] = $media;
					continue;
				}

				if (!isset($names[$media]))
					$names[$media] = '/** Content:
';
				$names[$media] .= $url . '
';

				if (!isset($files[$media]))
					$files[$media] = array();
				$files[$media][$absUrl] = $media;
			}

			//merging css files by "media"
			foreach($names as $media=>$name) {
				
				if (count($files[$media]) <= $this->mergeAbove) {
					$newCssFiles = array_merge($newCssFiles, $files[$media]);
					continue;
				}
				
				$name .= '*/
';	
				$hashedName = $this->hashedName($name,'css');
				$path = Yii::app()->assetManager->basePath . '/' . $hashedName;
				$path = preg_replace('#\\?.*$#','',$path);
				$url = Yii::app()->assetManager->baseUrl . '/'. $hashedName;

				if (!file_exists($path)) {
					$merged = '';
					if (!$this->ch)
						$this->initCurlHandler();
					
					foreach($files[$media] as $absUrl=>$media) {
						curl_setopt($this->ch, CURLOPT_URL, $absUrl);
						$ret = curl_exec($this->ch);
						$err = curl_error($this->ch);
						
						if (!$err) {
							$merged .= ($ret.'
	;');
						} else {
							$merged .= '
	/*
	error downloading ' . $absUrl . ':' . $err . '
	curl_info:' . print_r(curl_getinfo($this->ch), true) . '
	*/
	';
						}						
					}

					curl_close($this->ch);
					$this->ch = null;

					if ($this->compressMergedCss)
						$merged = self::minifyCss($merged);

					file_put_contents($path, $name . $merged);
				}//if
				
				$newCssFiles[$url] = $media;
			}//media
			
			$this->cssFiles = $newCssFiles;
		}
	}


	//If someone needs to access these, can be useful
	public function getScriptFiles() {
		return $this->scriptFiles;
	}
	public function getCssFiles() {
		return $this->cssFiles;
	}

	
	public function init() {
		parent::init();
		
		//we need jquery
		$this->registerCoreScript('jquery');
		
		//getting url of the document root
		if (!$this->serverBaseUrl) {
			$this->serverBaseUrl = strtolower(preg_replace('#/.*$#','',$_SERVER['SERVER_PROTOCOL'])) . '://' . $_SERVER['HTTP_HOST'];
			if ($_SERVER['SERVER_PORT'] != 80)
				$this->serverBaseUrl .= ':' . $_SERVER['SERVER_PORT'];
		}
	}




	public function renderHead(&$output) {

		$this->_putnlscode();
		
		//merging
		if ($this->mergeJs) {
			$this->_mergeJs(self::POS_HEAD);
		}
		if ($this->mergeCss) {
			$this->_mergeCss();
		}

        /* x2modstart */ 
		//parent::renderHead($output);
        /* x2modend */ 
	}

	public function renderBodyBegin(&$output) {
		
		//merging
		if ($this->mergeJs)
			$this->_mergeJs(self::POS_BEGIN);

		parent::renderBodyBegin($output);
	}

	public function renderBodyEnd(&$output) {
		
		//merging
		if ($this->mergeJs)
			$this->_mergeJs(self::POS_END);

		parent::renderBodyEnd($output);
	}





	protected function _putnlscode() {

		if (Yii::app()->request->isAjaxRequest)
			return;
		
		//preparing vars for js generation
		if (!$this->excludePattern)
			$this->excludePattern = 'null';
		if (!$this->includePattern)
			$this->includePattern = 'null';
		$this->mergeIfXhr = ($this->mergeIfXhr ? 1 : 0);
		
		//Minified code
/*		$this->registerScript('fixDuplicateResources',

'(function(a){var e=a.browser.msie&&7>=parseInt(a.browser.version)?document.createElement("div"):null,f=' . $this->excludePattern . ',g=' . $this->includePattern . ',j=' . $this->mergeIfXhr . ',k=' . $this->resMap2Request . ';a.nlsc||(a.nlsc={resMap:{}});a.nlsc.normUrl=function(c){if(!c)return null;e&&(e.innerHTML=\'<a href="\'+c+\'"></a>\',c=e.firstChild.href);return f&&c.match(f)||g&&!c.match(g)?null:c.replace(/\?*(_=\d+)?$/g,"")};a.nlsc.h=function(c){var b=0,a;for(a=0;a<c.length;a++)b=(b<<5)-b+c.charCodeAt(a)&1073741823;return""+b};a.nlsc.fetchMap= function(){for(var c,b=0,d=a(document).find("script[src]");b<d.length;b++)if(c=this.normUrl(d[b].src?d[b].src:d[b].href))this.resMap[c]=a.nlsc.h(c)};a.nlsc.smap=function(){var a="[",b;for(b in this.resMap)a+=\'"\'+this.resMap[b]+\'",\';return a.replace(/,$/,"")+"]"};var h={global:!0,beforeSend:function(c,b){if("script"!=b.dataType)return j&&(b.url=k(b.url)),!0;a.nlsc.fetched||(a.nlsc.fetched=1,a.nlsc.fetchMap());var d=a.nlsc.normUrl(b.url);if(!d)return!0;if(a.nlsc.resMap[d])return!1;a.nlsc.resMap[d]= a.nlsc.h(d);return!0}};a.browser.msie&&(h.dataFilter=function(a,b){return b&&"html"!=b&&"text"!=b?a:a.replace(/(<script[^>]+)defer(=[^\s>]*)?/ig,"$1")});a.ajaxSetup(h)})(jQuery);'

, CClientScript::POS_HEAD);*/


//Uncompressed code:

$this->registerScript('fixDuplicateResources', '

;(function($){

//some closures
var cont = ($.browser.msie && parseInt($.browser.version)<=7) ? document.createElement("div") : null,
excludePattern = '.$this->excludePattern.',
includePattern = '.$this->includePattern.',
mergeIfXhr = '.$this->mergeIfXhr.',
resMap2Request = '.$this->resMap2Request.';

if (!$.nlsc)
	$.nlsc={resMap:{}};

$.nlsc.normUrl=function(url) {
	if (!url) return null;
	if (cont) {
		cont.innerHTML = "<a href=\""+url+"\"></a>";
		//cont.innerHTML = cont.innerHTML;
		url = cont.firstChild.href;
		//console.log(url);
	}
	if (excludePattern && url.match(excludePattern))
		return null;
	if (includePattern && !url.match(includePattern))
		return null;
    /* x2modstart */     
    /*
    After X2 cache busting was introduced, this line broke. It attempts to strip out the jQuery 
    "_" cache busting query parameter from the script url. But it made the assumption that the
    url didn\'t already have get parameters before the jQuery cache busting parameter was added.
    */
	//return url.replace(/\?*(_=\d+)?$/g,"");
	return url.replace(/(\&|\?)?(_=\d+)?$/g,"");
    /* x2modend */ 
}
$.nlsc.h=function(s) {
	var h = 0, i;
	for (i = 0; i < s.length; i++) {
		h = (((h<<5)-h) + s.charCodeAt(i)) & 1073741823;
	}

	return ""+h;
}
$.nlsc.fetchMap=function() {
	//fetching scripts from the DOM
	for(var url,i=0,res=$(document).find("script[src]"); i<res.length; i++) {
		if (!(url = this.normUrl(res[i].src ? res[i].src : res[i].href))) continue;

		this.resMap[url] = $.nlsc.h(url);
	}//i
}
$.nlsc.smap=function() {
	var s="[";
	for(var url in this.resMap)
		s += "\""+this.resMap[url]+"\",";
	return s.replace(/,$/,"")+"]";
}

var c = {
	global:true,
	beforeSend: function(xhr, opt) {
		if (opt.dataType!="script") {
			//hack: letting the server know what is already in the dom...
			if (mergeIfXhr)
				opt.url = resMap2Request(opt.url);
			return true;
		}

        /* x2modstart */     
//		if (!$.nlsc.fetched) {
//			$.nlsc.fetched=1;
//			$.nlsc.fetchMap();
//		}//if
        /* x2modend */ 
		
		var url = $.nlsc.normUrl(opt.url);

		if (!url) return true;
		if ($.nlsc.resMap[url]) return false;
		$.nlsc.resMap[url] = $.nlsc.h(url);
		return true;
	}//beforeSend
};//c

//removing "defer" attribute from IE scripts anyway
if ($.browser.msie)
	c.dataFilter = function(data,type) {
		if (type && type != "html" && type != "text")
			return data;
		return data.replace(/(<script[^>]+)defer(=[^\s>]*)?/ig, "$1");
	};

$.ajaxSetup(c);

/* x2modstart */
$(function () {
    $.nlsc.fetched=1;
    $.nlsc.fetchMap ();
});
/* x2modend */

})(jQuery);

', CClientScript::POS_HEAD);
	}
}
