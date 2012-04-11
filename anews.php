<?php
$rel_path='anews/html';
$max_str_len=20;
$news_dir=realpath($rel_path);
$was_err = 0;
@ $section = $_GET[section];
if ( isset($section) && !empty($section) && (strlen($section)<= $max_str_len) )
{
  $fpath = $rel_path."/".$section.".html";
  $fpath = realpath($fpath);
  if (!empty($fpath))
  {
    $fdir = dirname($fpath);
    if ($fdir == $news_dir)
      include($fpath);
    else
      $was_err = 1;
  }
  else
    $was_err = 1;
}
else
    $was_err = 1;

if ($was_err == 1)
  include('html/404.html');
?>