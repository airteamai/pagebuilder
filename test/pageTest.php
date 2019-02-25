<?php 
include("../pagebuilder.php");
$builder=PageBuilder::create();
$weui=ui_weui::uiLib_reg($builder);
$testChild=DOMPage_PluginNode::create();
$btn=$weui->createButton("HelloWorld","primary","a",array("href"=>"https://www.baidu.com/"));
$btn1=$weui->createMsgPage("success","操作成功","",array($btn));
$builder->appendBody($btn1);
$builder->prebuild();

$builder->build();
//var_dump($builder->getDOMTree());
//
echo $builder->getDOMData();