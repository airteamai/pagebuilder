<?php

$web = 'pagebuilder.php';

if (in_array('phar', stream_get_wrappers()) && class_exists('Phar', 0)) {
Phar::interceptFileFuncs();
set_include_path('phar://' . __FILE__ . PATH_SEPARATOR . get_include_path());
Phar::webPhar(null, $web);
include 'phar://' . __FILE__ . '/' . Extract_Phar::START;
return;
}

if (@(isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST'))) {
Extract_Phar::go(true);
$mimes = array(
'phps' => 2,
'c' => 'text/plain',
'cc' => 'text/plain',
'cpp' => 'text/plain',
'c++' => 'text/plain',
'dtd' => 'text/plain',
'h' => 'text/plain',
'log' => 'text/plain',
'rng' => 'text/plain',
'txt' => 'text/plain',
'xsd' => 'text/plain',
'php' => 1,
'inc' => 1,
'avi' => 'video/avi',
'bmp' => 'image/bmp',
'css' => 'text/css',
'gif' => 'image/gif',
'htm' => 'text/html',
'html' => 'text/html',
'htmls' => 'text/html',
'ico' => 'image/x-ico',
'jpe' => 'image/jpeg',
'jpg' => 'image/jpeg',
'jpeg' => 'image/jpeg',
'js' => 'application/x-javascript',
'midi' => 'audio/midi',
'mid' => 'audio/midi',
'mod' => 'audio/mod',
'mov' => 'movie/quicktime',
'mp3' => 'audio/mp3',
'mpg' => 'video/mpeg',
'mpeg' => 'video/mpeg',
'pdf' => 'application/pdf',
'png' => 'image/png',
'swf' => 'application/shockwave-flash',
'tif' => 'image/tiff',
'tiff' => 'image/tiff',
'wav' => 'audio/wav',
'xbm' => 'image/xbm',
'xml' => 'text/xml',
);

header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$basename = basename(__FILE__);
if (!strpos($_SERVER['REQUEST_URI'], $basename)) {
chdir(Extract_Phar::$temp);
include $web;
return;
}
$pt = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $basename) + strlen($basename));
if (!$pt || $pt == '/') {
$pt = $web;
header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $_SERVER['REQUEST_URI'] . '/' . $pt);
exit;
}
$a = realpath(Extract_Phar::$temp . DIRECTORY_SEPARATOR . $pt);
if (!$a || strlen(dirname($a)) < strlen(Extract_Phar::$temp)) {
header('HTTP/1.0 404 Not Found');
echo "<html>\n <head>\n  <title>File Not Found<title>\n </head>\n <body>\n  <h1>404 - File ", $pt, " Not Found</h1>\n </body>\n</html>";
exit;
}
$b = pathinfo($a);
if (!isset($b['extension'])) {
header('Content-Type: text/plain');
header('Content-Length: ' . filesize($a));
readfile($a);
exit;
}
if (isset($mimes[$b['extension']])) {
if ($mimes[$b['extension']] === 1) {
include $a;
exit;
}
if ($mimes[$b['extension']] === 2) {
highlight_file($a);
exit;
}
header('Content-Type: ' .$mimes[$b['extension']]);
header('Content-Length: ' . filesize($a));
readfile($a);
exit;
}
}

class Extract_Phar
{
static $temp;
static $origdir;
const GZ = 0x1000;
const BZ2 = 0x2000;
const MASK = 0x3000;
const START = 'pagebuilder.php';
const LEN = 6665;

static function go($return = false)
{
$fp = fopen(__FILE__, 'rb');
fseek($fp, self::LEN);
$L = unpack('V', $a = fread($fp, 4));
$m = '';

do {
$read = 8192;
if ($L[1] - strlen($m) < 8192) {
$read = $L[1] - strlen($m);
}
$last = fread($fp, $read);
$m .= $last;
} while (strlen($last) && strlen($m) < $L[1]);

if (strlen($m) < $L[1]) {
die('ERROR: manifest length read was "' .
strlen($m) .'" should be "' .
$L[1] . '"');
}

$info = self::_unpack($m);
$f = $info['c'];

if ($f & self::GZ) {
if (!function_exists('gzinflate')) {
die('Error: zlib extension is not enabled -' .
' gzinflate() function needed for zlib-compressed .phars');
}
}

if ($f & self::BZ2) {
if (!function_exists('bzdecompress')) {
die('Error: bzip2 extension is not enabled -' .
' bzdecompress() function needed for bz2-compressed .phars');
}
}

$temp = self::tmpdir();

if (!$temp || !is_writable($temp)) {
$sessionpath = session_save_path();
if (strpos ($sessionpath, ";") !== false)
$sessionpath = substr ($sessionpath, strpos ($sessionpath, ";")+1);
if (!file_exists($sessionpath) || !is_dir($sessionpath)) {
die('Could not locate temporary directory to extract phar');
}
$temp = $sessionpath;
}

$temp .= '/pharextract/'.basename(__FILE__, '.phar');
self::$temp = $temp;
self::$origdir = getcwd();
@mkdir($temp, 0777, true);
$temp = realpath($temp);

if (!file_exists($temp . DIRECTORY_SEPARATOR . md5_file(__FILE__))) {
self::_removeTmpFiles($temp, getcwd());
@mkdir($temp, 0777, true);
@file_put_contents($temp . '/' . md5_file(__FILE__), '');

foreach ($info['m'] as $path => $file) {
$a = !file_exists(dirname($temp . '/' . $path));
@mkdir(dirname($temp . '/' . $path), 0777, true);
clearstatcache();

if ($path[strlen($path) - 1] == '/') {
@mkdir($temp . '/' . $path, 0777);
} else {
file_put_contents($temp . '/' . $path, self::extractFile($path, $file, $fp));
@chmod($temp . '/' . $path, 0666);
}
}
}

chdir($temp);

if (!$return) {
include self::START;
}
}

static function tmpdir()
{
if (strpos(PHP_OS, 'WIN') !== false) {
if ($var = getenv('TMP') ? getenv('TMP') : getenv('TEMP')) {
return $var;
}
if (is_dir('/temp') || mkdir('/temp')) {
return realpath('/temp');
}
return false;
}
if ($var = getenv('TMPDIR')) {
return $var;
}
return realpath('/tmp');
}

static function _unpack($m)
{
$info = unpack('V', substr($m, 0, 4));
 $l = unpack('V', substr($m, 10, 4));
$m = substr($m, 14 + $l[1]);
$s = unpack('V', substr($m, 0, 4));
$o = 0;
$start = 4 + $s[1];
$ret['c'] = 0;

for ($i = 0; $i < $info[1]; $i++) {
 $len = unpack('V', substr($m, $start, 4));
$start += 4;
 $savepath = substr($m, $start, $len[1]);
$start += $len[1];
   $ret['m'][$savepath] = array_values(unpack('Va/Vb/Vc/Vd/Ve/Vf', substr($m, $start, 24)));
$ret['m'][$savepath][3] = sprintf('%u', $ret['m'][$savepath][3]
& 0xffffffff);
$ret['m'][$savepath][7] = $o;
$o += $ret['m'][$savepath][2];
$start += 24 + $ret['m'][$savepath][5];
$ret['c'] |= $ret['m'][$savepath][4] & self::MASK;
}
return $ret;
}

static function extractFile($path, $entry, $fp)
{
$data = '';
$c = $entry[2];

while ($c) {
if ($c < 8192) {
$data .= @fread($fp, $c);
$c = 0;
} else {
$c -= 8192;
$data .= @fread($fp, 8192);
}
}

if ($entry[4] & self::GZ) {
$data = gzinflate($data);
} elseif ($entry[4] & self::BZ2) {
$data = bzdecompress($data);
}

if (strlen($data) != $entry[0]) {
die("Invalid internal .phar file (size error " . strlen($data) . " != " .
$stat[7] . ")");
}

if ($entry[3] != sprintf("%u", crc32($data) & 0xffffffff)) {
die("Invalid internal .phar file (checksum error)");
}

return $data;
}

static function _removeTmpFiles($temp, $origdir)
{
chdir($temp);

foreach (glob('*') as $f) {
if (file_exists($f)) {
is_dir($f) ? @rmdir($f) : @unlink($f);
if (file_exists($f) && is_dir($f)) {
self::_removeTmpFiles($f, getcwd());
}
}
}

@rmdir($temp);
clearstatcache();
chdir($origdir);
}
}

Extract_Phar::go();
__HALT_COMPILER(); ?>
|           pagebuilder.phar    	   build.php�  C]s  �K��         pagebuilder.php]   C]N   U(�h�         src/builder.php�   C]�   Q�㾶         src/builderMain.php�
  C]�  �JZ��         src/DOMNode.php�  C]�   ���E�         src/uibuilder.php�  C]�  �s�l�         src/ui_weui.php�  C]s  c��q�         test/pageTest.php�  C]+  g"�ܶ      mRMO1=K��4&t��?�
�'	xѐ4�;�K�iw5��ߝeW%�=M��{of�ˑ۸n'��X�l�8�<?�_�b2��P��l�d�E���V&�`�ԙ��g�t<���<��Vz�YLxlXH[匴j�<�`�0��}(&�l/xҨ�x��Ҳ!�\�k
j�Ml��x[�,A���94&����=`욺Q^�9,9��p?`F!�9^�Z�{}����e�]��S�o�ƣ.r��R�*� j�$�;�)�EQ�xs�U�cLU������zp��@P�[z��&���Q���ת��dV�-�.Ce���U�8^��ݎ3OM�Aĉ	E��2c��%��Rkj7-3Q�A�c��hv'Z�Q�ʚ���~����/�(��JIM��K�P
ptwu
��qq���Q҉�w���״����K�)MI�@W���_\���T����Z�4P	��� ��/�(��R)�)M��+�M,*J�����TJ*��II-�M����Q�	�fB�����SK3.��~�)�`^.Mk^��������-
��
*e�E����E���N��>.�A�A��!zJ��E��Jz@5@��v �VMo�0=��൐�H��z,u*�=����[[E&1$[�D��
���;��AB��l{A�̼�zo���4J��`M�=����u�ğ��,�d�d!��|�I����pf�.�pk�F�m���bh��24˜2N8�� �y Wut6�Q�M�V;������엗�`T2ǝu�T����'�ω�1y���6ex�����t��EA�+���z�6J�QF{g���i��<`4�]��J4M�af�p�h&oA�����3InU�SC�K���'�4��/�I*�d�r)"�Y0��8���U���A��J:�%l�p��9Uϟ���Xf'���W�YŰb�3�EKw)[�
�����@?�,�����AX��P�=�C�fW�.Qs�,ڃ/�%)�Q�1M���0%�t��?鋊9g�Y& � 2�'�>D34ژV�����h�S������u�w\;�/U������r�U�r\B��1,��$�
O@ՍA� ɹ<�K��u��o�������"��iy%l�jmJ0��^��<cxl��\�y�^!sCL��2ִ
�R!� m�{��-�>ܔ�������1v_
���'�_�����&���`z��zCGST��n��eeM��q��ü
��
V�P���o8��u��
� �������	����>�#��lĉ���=m��֥���_�ta+��	ͽ'��z�K8�t(���n�^f�I+A����3���|��F�Pw��)>��wE0Um��\&���Z?OŌb��x���f�(����4�J��� ��i�8��s�U���P(�K�s�!�a��Ե�*z���a�#�&�2.����SMo�0=�@��F�$�Ы+hs�a�8���lӉZW2d9C1�O��|7�^�|$���͢!�gE�ږt���u����٨��$h�s�N�{�:Qh.�I�y�
����F}�Q �*����>�2�C�͹��%&C|���o��B����5�c���8��NҎ߱7t(��S�����UR6Xn���LN�[xȉ�|=�B
�BS�d���o�0բ%�2�팘Z��E���36�x>�B���J����7I��ћ��KGg�;�Ed���2���b�4��xh�謎�DQn�y�	�6�V�W��F�¯��o����:� >H�q��X�N胺LkQ�� L���8��{��د45�������}c,�����jgH��8��T�?+^C��}����O���Vvž���<�t�g���ͤ{�[����|�v�x=N�c��}zE�%1��{h3a���`�]����nW�x����V� �V�n�F=ۀ��X�H$��L�������bĒ\��R$���,�-Z�5E�^�k/��L�"��Bwv�)�vҢ�m�3�3o�����o�?*�ro7�0�VE���E.�c�~\�,&����NY���^E��x,?�w�����~���q���B���#A�\�8�a�Ȭ�+�:� "��hν�,,N��a-�-C��J��#_>ix��i�S�Z�hNE�����Vm��*��������w����<Ϫ�ϊ�F�`A�O����'E���X��������v����_n�c!b����H�<q��O	h�)#	h\��,.��/����EIrWR�](���^�9͝�s�&�Hi����K�!~Jp�t����\<��(�~O�������1[�a/Tډ����Hf��$�^L9�q���Kp�I��*u��=_�Hl�@�j"I�qk���R���A�,����T�T��h��Q�&p��a���%���=�3��~�FE�!^E��DS�INo^��77_�p��OR*��|5T��QQ.����?8�k�5ɀ�g� �V� 6�5�:���Ya�k����[b�IP��
����[��	PdѤ���u���@�=�����TA��H=J⨦��IJK���d���I����{wR@y½�x>|�Sg�8�G�(�����}n7�dO5A#�С�Gg�A�Yٚ��=�e0'L2��)<�9�!ҍ:��G��󓓁�Ly���M�0T�ڰ���7��.���Vue�k�����R$�\ɒBf�M5�(�Ss�w�=����'�<������X�lm������	#dR�!С���S�}hA���;�g�cza��0�*�G�ޮ%�� v��"L;j���^��Ӟ:��(�|�R�'}�:ʄ�f�4}��R���*�@䱛>h�.7ǄG��f�ի��h��m�톓��a?2v�C얃�  �bxºmR�yc�֐j� �K_�U�؂���R!J~�3*�*T��KJ�t����Ս�V�ȿy������/�o�����wW�[0g�6n]_��ط�7�ܓ����Ǆ�Y.w�p�	~�X8���b�@�e��GԂD���#��v�F�̻~���'�]�AK�0��~�<��5�Z턹��;K�<�@ׄ�Ĳo � x� "~�u_��ul�����~I�ۏ��s]�a0�u^9�D�X�y��� �tɑ�����o�,�:��8���p2q2m�8v�Jf)BAw��ɂ����͢E�����V�q����9��㙳VՔ\BU�;�� #�Q�8�}�Ɉ#�5%%�=I���V����i��K�\�	w��1a��A���`�gn^��?�6�/?O����.�����\k��L�^���@��
�)cS�V�������gқ����̹������4I���P��v}   GBMB