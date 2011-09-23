<?php

class SmartDataProvider extends CActiveDataProvider {
	private $_pagination;
	public function getPagination() {
		if($this->_pagination===null)
		{
			//$this->_pagination=new CPagination;
			$this->_pagination=new RememberPagination;
			if(($id=$this->getId())!='')
				$this->_pagination->pageVar=$id.'_page';
		}
		return $this->_pagination;
	}
}
?>
