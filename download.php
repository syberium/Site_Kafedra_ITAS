<?php
$nofile = 'images/passwd.png';
$file=$_GET[file];
$content_type = 'application/octet-stream';
if (strpos($file,'%') > 0) $file = $nofile;
if (strpos($file,'..') > 0) $file = $nofile;
if (strpos($file,'passwd') > 0) $file = $nofile;
if (strpos($file,'shadow') > 0) $file = $nofile;
if (strpos($file,'.php') > 0) $file = $nofile;
if (strpos($file,'.dat') > 0) $file = $nofile;
if (strpos($file,'.zip') > 0) $content_type = 'application/zip';
if (strpos($file,'.gz') > 0) $content_type = 'application/zip';
if (strpos($file,'.rar') > 0) $content_type = 'application/zip';
if (strpos($file,'.tgz') > 0) $content_type = 'application/zip';
if (strpos($file,'.pdf') > 0) $content_type = 'application/pdf';
header('Content-type: '.$content_type);
header('Content-length: '.filesize($file));
header('Content-disposition: attachment; filename='.basename($file));
readFile($file);
// download log
$f = fopen('/home/itas/qlog/dlog','a+');
if ($f) {
    fwrite($f,date("Y-m-d\TH:i:s").";".$_SERVER[REMOTE_ADDR].";".$file.";".strtr($_SERVER[HTTP_USER_AGENT],';',':')."\n");
    fclose($f);
}
// end
?> 
