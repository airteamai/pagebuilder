<?php
class ui_weui extends uiBuilder{
	public $uiName="weui@1.1.3";
	public $uiClassName="ui_weui";
	static public function uiLib_reg($pagebuilder){
		$ins=new self;
		$ins->pagebuilder=$pagebuilder;
		$pagebuilder->uilib[$ins->uiName]=$ins;
		$ins->init();
		return $ins;
	}
	public function init(){
		$obj=DOMPage_PluginNode::create();
		$obj->setConf("type","HTMLTag");
		$obj->setConf("tag","link");
		$obj->setConf("attr",array(
			"rel"=>"stylesheet",
			"href"=>"//res.wx.qq.com/open/libs/weui/1.1.3/weui.min.css"
		));
		$this->pagebuilder->appendHead($obj);
	}
	public function createButton($text,$type="primary",$buttonType="a",$buttonAttr=array(),$disenabled=false){
		$obj=$this->uilib_createObject();
		$obj->setConf("data",array(
			"type"=>"button",
			"styleType"=>$type,
			"disenable"=>$disenabled,
			"text"=>$text,
			"attr"=>$buttonAttr,
			"btype"=>$buttonType,
		));
		return $obj;
	}
	public function createMsgPage($icon="success",$title="操作成功",$content="",$buttons=array(),$copyright=""){
		$obj=$this->uilib_createObject();
		$obj->setConf("data",array(
			"type"=>"msgPage",
			"icon"=>$icon,
			"title"=>$title,
			"content"=>$content,
			"buttons"=>$buttons,
			"copyright"=>$copyright
		));
		return $obj;
	}
	public function parse_plugin_object(DOMPage_PluginNode &$obj){
		if($obj->getConf("data")['type']=="button")$this->parse_button($obj);
		if($obj->getConf("data")['type']=="msgPage")$this->parse_msgpage($obj);
	}
	protected function parse_button(DOMPage_PluginNode &$obj){
		$confs=$obj->getConf("data");
		$obj->setConf("type","HTMLTag");
		$classVal="weui-btn ".($confs['disenable']?"weui-btn_disenable ":"")."weui-btn_".$confs["styleType"];
		$obj->setConf("attr",array_merge(array("class"=>$classVal,"create_time"=>time(NULL)),$confs["attr"]));
		$obj->setConf("inner",$confs['text']);
		$obj->setConf("tag",$confs['btype']);
	}
	protected function parse_msgpage(DOMPage_PluginNode &$obj){
		$confs=$obj->getConf("data");
		$butttonData="";
		foreach($confs['buttons'] as $v){
			if($v instanceof DOMPage_PluginNode){
				$this->parse_button($v);
				$butttonData.=$this->TreeToDom($v);
			}
		}
		$obj->setConf("type","HTMLTag");
		$obj->setConf("tag","pagebuilder-weui_page");
		$obj->setConf("inner",'<div class="weui-msg">
    <div class="weui-msg__icon-area"><i class="weui-icon-'.$confs['icon'].' weui-icon_msg"></i></div>
    <div class="weui-msg__text-area">
        <h2 class="weui-msg__title">'.$confs['title'].'</h2>
        <p class="weui-msg__desc">'.$confs['content'].'</p>
    </div>
    <div class="weui-msg__opr-area">
        <p class="weui-btn-area">
            '.$butttonData.'
        </p>
    </div>
    <div class="weui-msg__extra-area">
        <div class="weui-footer">
            <p class="weui-footer__links">
                <a href="https://github.com/xieyi1393/pagebuilder" class="weui-footer__link">本页面建立于 PageBuilder 库</a> 
            </p>
            <p class="weui-footer__text">Designed by <a href="https://www.cnitai.com/">AirTeamAi Team</a><br>'.$confs['copyright'].'</p>
        </div>
    </div>
</div>');
	}
}
