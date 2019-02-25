<?php
class DOMPage_PluginNode{
	public $child=array();
	protected $nodeConf=array();
	public function appendChild(DOMPage_PluginNode $child){
		$this->child[]=$child;
	}
	public function setConf($id,$value){
		$this->nodeConf[$id]=$value;
	}
	public function getConf($id){
		if(!isset($this->nodeConf[$id]))return "";
		return $this->nodeConf[$id];
	}
	static public function create(){
		return new DOMPage_PluginNode;
	}
}