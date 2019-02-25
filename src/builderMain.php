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
class PageBuilder{
	protected $pluginNode_head;
	protected $pluginNode_body;
	protected $pluginNode_html;
	public $uilib;
	
	protected function __construct(){
		$this->pluginNode_head=DOMPage_PluginNode::create();
		$this->pluginNode_body=DOMPage_PluginNode::create();
		$this->pluginNode_html=DOMPage_PluginNode::create();
		$this->pluginNode_head->setConf("type","HTMLTag");
		$this->pluginNode_head->setConf("tag","head");
		$this->pluginNode_body->setConf("type","HTMLTag");
		$this->pluginNode_body->setConf("tag","body");
		$this->pluginNode_html->setConf("type","HTMLTag");
		$this->pluginNode_html->setConf("tag","html");
	}
	public function appendHead(DOMPage_PluginNode $domchild){
		$this->pluginNode_head->appendChild($domchild);
	}
	public function appendBody(DOMPage_PluginNode $domchild){
		$this->pluginNode_body->appendChild($domchild);
	}
	static public function create(){
		return new PageBuilder;
	}
	protected $domdata;
	public function prebuild(){
		$this->pluginNode_html=DOMPage_PluginNode::create();
		$this->pluginNode_html->setConf("type","HTMLTag");
		$this->pluginNode_html->setConf("tag","html");
		$this->pluginNode_html->appendChild($this->pluginNode_head);
		$this->pluginNode_html->appendChild($this->pluginNode_body);
	}
	public function getDOMTree(){
		return $this->pluginNode_html;
	}
	public function build(){
		$this->domdata="";
		$this->build_step2_buildDOMData($this->pluginNode_html);
	}
	protected function build_step2_buildDOMData(DOMPage_PluginNode $node){
		$conf=$this->parseConf($node);
		$this->domdata.=$conf['startTag'];
		$this->domdata.=$conf['inner'];
		foreach($node->child as $v){
			$this->build_step2_buildDOMData($v);
		}
		$this->domdata.=$conf['endTag'];
	}
	public function getDOMData(){
		return $this->domdata;
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