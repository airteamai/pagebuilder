<?php
define("DIRETORY_SEPRATOR","\\");
function is_cli(){
    return preg_match("/cli/i", php_sapi_name()) ? 1 : 0;
}
function d_eol(){
	return is_cli()?"\n":"<br>\n";
}
echo "PageBuilder PHAR Builder".d_eol();
echo "Building phar....".d_eol();
$phar = new Phar('pagebuilder.phar', 0, 'pagebuilder.phar');
$phar->buildFromDirectory(__DIR__  , '/\.php$/');
$phar->setStub($phar->createDefaultStub('pagebuilder.php', 'pagebuilder.php'));
$phar->compressFiles(Phar::GZ);
unset($phar);
echo "Cleaning files.....".d_eol();
copy("./pagebuilder.phar","./dist/pagebuilder.phar");
unlink("./pagebuilder.phar");
echo "Build successful.File saved on ".__DIR__.DIRETORY_SEPRATOR."dist".DIRETORY_SEPRATOR."pagebuilder.phar".d_eol();