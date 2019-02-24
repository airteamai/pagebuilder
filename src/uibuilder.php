<?php 
class uiBuilder{
	public $pagebuilder;
	
	public function uilib_createObject(){
		$obj=new DOMPage_PluginNode;
		$obj->setConf("type","pluginNode");
		$obj->setConf("ui",$this->uiName);
		return $obj;
	}
	protected function TreeToDom(DOMPage_PluginNode $node){
		$content="";
		$conf=$this->parseConf($node);
		$content.=$conf['startTag'];
		$content.=$conf['inner'];
		foreach($node->child as $v){
			$this->build_step2_buildDOMData($v,$content);
		}
		$content.=$conf['endTag'];
		return $content;
	}
	protected function parseConf(DOMPage_PluginNode $node){
		if($node->getConf("type")=="HTMLTag"){
		$startTag="<".$node->getConf("tag");
		if(count($node->getConf("attr"))!=0 and ($node->getConf("attr"))!=""){
		foreach($node->getConf("attr") as $k=>$v)$startTag.=" ".$k."=\"".$v.="\"";
		}
		$startTag.=">";
		$endTag="</".$node->getConf("tag").">";
		$inner=$node->getConf("inner");
		}
		if($node->getConf("type")=="pluginNode"){
			$this->uilib[$node->getConf("ui")]->parse_plugin_object($node);
			$startTag="<".$node->getConf("tag");
			if(count($node->getConf("attr"))!=0 and ($node->getConf("attr"))!=""){
			foreach($node->getConf("attr") as $k=>$v)$startTag.=" ".$k."=\"".$v.="\"";
			}
			$startTag.=">";
			$endTag="</".$node->getConf("tag").">";
			$inner=$node->getConf("inner");
		}
		return array(
			"startTag"=>$startTag,
			"endTag"=>$endTag,
			"inner"=>$inner
		);
		}
}