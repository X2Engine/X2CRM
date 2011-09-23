<?php

class RememberPagination extends CPagination{
	public function createPageUrl($controller,$page)
	{
		$params=$this->params===null ? $_GET : $this->params;
	//  if($page>0) // page 0 is the default
			$params[$this->pageVar]=$page+1;
	//  else
	//      unset($params[$this->pageVar]);
		return $controller->createUrl($this->route,$params);
	}
}

?>
