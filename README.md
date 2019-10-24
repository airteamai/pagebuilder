# 简述
PageBuilder是一款让不会HTML的新人也能快速构建美观大方的页面(但是需要各位dalao帮忙贡献一下UI库的插件)
# Example
```PHP
<?php 
include("../dist/pagebuilder.phar");//加载PageBuilder包
$builder=PageBuilder::create();//创建PageBuilder对象 
$weui=ui_weui::uiLib_reg($builder);//注册WeUI库对象
$btn=$weui->createButton("HelloWorld","primary","a",array("href"=>"https://www.baidu.com/"));//使用UI库的createButton方法创建一个按钮
$btn1=$weui->createMsgPage("success","操作成功","",array($btn));//创建一个WeUI页面
$builder->appendBody($btn1);//将WeUI页面写入PageBuilder节点树
$builder->prebuild();//对节点进行预编译
$builder->build();//编译成HTML
echo $builder->getDOMData();//返回HTML数据并输出
```
# 工作原理
PageBuilder库使用DOM树来存储HTML页面.
在PreBuild之前,PageBuilder内部的DOMTree是处于未处理阶段,PreBuild方法将会对其中所有涉及到插件处理的统一发送到插件的指定方法中(详情请看dist/ui_weui.php),然后由插件返回一个PageBuilder的DOMNode对象.PageBuilder库使用返回的数据替换之前的插件引用节点
在Build方法中,DOMTree将会被转换成HTML源代码
