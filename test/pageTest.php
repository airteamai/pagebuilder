<?php 
include("../dist/pagebuilder.phar");
$builder=PageBuilder::create();
$weui=ui_weui::uiLib_reg($builder);
$testChild=DOMPage_PluginNode::create();
$btn=$weui->createButton("HelloWorld","primary","a",array("href"=>"https://www.baidu.com/"));
$btn1=$weui->createMsgPage("success","操作成功","",array($btn));
$builder->appendBody($btn1);
$builder->prebuild();
var_dump($builder->getDOMTree());
$builder->build();

//
//echo $builder->getDOMData();