<?php
$plugins=array(
	"builderMain.php",
	"uibuilder.php",
	"ui_weui.php",
	"DOMNode.php"
);
foreach($plugins as $v)require(PAGEBUILDER_ROOT."/src/".$v);
?>