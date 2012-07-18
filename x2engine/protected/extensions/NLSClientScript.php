<?php
/**
 * NLSClientScript v5.0
 * 
 * CClientScript extension for preventing multiple loading of javascript files.
 * Important! This extension does not prevent to load the same script content from different paths. 
 * So eg. if you published the same js file into different asset directories, this extension won't prevent to load both.
 * 
 * 
 * 
 * Usage: simply set the class for the clientScript component in /protected/config/main.php, like
 *  ...
 *   'components'=>array(
 *     ...
 *     'clientScript' => array(
 *       'class'=>'your.path.to.NLSClientScript', 
 *       //'includePattern'=>'/\/scripts/', //javacsript regexp, if set, only the matched urls will be filtered
 *       //'excludePattern'=>'/\/raw/'      //javacsript regexp, if set, the matched urls won't be filtered
 *     )
 *     ...
 *   )
 *  ...
 * 
 * The extension is based on the great idea of Eirik Hoem, see
 * http://www.eirikhoem.net/blog/2011/08/29/yii-framework-preventing-duplicate-jscss-includes-for-ajax-requests/
 * 
 */
class NLSClientScript extends CClientScript {

/**
 * Applying global ajax post-filtering
 * original source: http://www.eirikhoem.net/blog/2011/08/29/yii-framework-preventing-duplicate-jscss-includes-for-ajax-requests/
*/
	public $includePattern = 'null';//default: 'null'
	public $excludePattern = 'null';//default: 'null'
	
	//if someone needs to access the scriptFiles member, this can be useful
	public function getScriptFiles() {
		return $this->scriptFiles;
	}

	public function renderHead(&$output) {

		$this->_putnlscode();

		parent::renderHead($output);
	}

	protected function _putnlscode() {

		if (Yii::app()->request->isAjaxRequest)
			return;

		//we need jquery
		$this->registerCoreScript('jquery');

		//Minified code
		$this->registerScript('fixDuplicateResources',
';(function($){var cont=($.browser.msie&&parseInt($.browser.version)<=7)?document.createElement("div"):null,excludePattern='.$this->excludePattern.',includePattern='.$this->includePattern.';$.nlsc={resMap:{},normUrl:function(url){if(!url)return null;if(cont){cont.innerHTML="<a href=\""+url+"\"></a>";url=cont.firstChild.href;}if(excludePattern&&url.match(excludePattern))return null;if(includePattern&&!url.match(includePattern))return null;return url.replace(/\?*(_=\d+)?$/g,"");},fetchMap:function(){for(var url,i=0,res=$(document).find("script[src]");i<res.length;i++){if(!(url=this.normUrl(res[i].src?res[i].src:res[i].href)))continue;this.resMap[url]=1;}}};var c={global:true,beforeSend:function(xhr,opt){if(opt.dataType!="script")return true;if(!$.nlsc.fetched){$.nlsc.fetched=1;$.nlsc.fetchMap();}var url=$.nlsc.normUrl(opt.url);if(!url)return true;if($.nlsc.resMap[url])return false;$.nlsc.resMap[url]=1;return true;}};if($.browser.msie)c.dataFilter=function(data,type){if(type&&type!="html"&&type!="text")return data;return data.replace(/(<script[^>]+)defer(=[^\s>]*)?/ig,"$1");};$.ajaxSetup(c);})(jQuery);'
, CClientScript::POS_HEAD);


//Source code:
/*

$this->registerScript('fixDuplicateResources', '

;(function($){

//some closures
var cont = ($.browser.msie && parseInt($.browser.version)<=7) ? document.createElement("div") : null,
excludePattern = null,//'.$this->excludePattern.'
includePattern = null;//'.$this->includePattern.'

$.nlsc = {
	resMap : {},
	normUrl : function(url) {
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
		return url.replace(/\?*(_=\d+)?$/g,"");
	},
	fetchMap : function() {
		//fetching scripts from the DOM
		for(var url,i=0,res=$(document).find("script[src]"); i<res.length; i++) {
			if (!(url = this.normUrl(res[i].src ? res[i].src : res[i].href))) continue;
			this.resMap[url] = 1;
		}//i
	}
};

var c = {
	global:true,
	beforeSend: function(xhr, opt) {
		if (opt.dataType!="script")
			return true;

		if (!$.nlsc.fetched) {
			$.nlsc.fetched=1;
			$.nlsc.fetchMap();
		}//if
		
		var url = $.nlsc.normUrl(opt.url);
		if (!url) return true;
		if ($.nlsc.resMap[url]) return false;
		$.nlsc.resMap[url] = 1;
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

})(jQuery);

',	CClientScript::POS_HEAD);


*/
	}

}
