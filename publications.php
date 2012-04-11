<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ($_GET[errors] != '') {
} else { error_reporting(0); }

include 'lang.php';

// replacement for "iconv()" function
// based on unix shell command "iconv"
function shiconv($from,$to,$str) {
    return exec(' echo "'.$str.'" | iconv -f '.$from.' -t '.$to);
    //    echo ' echo "'.$str.'" | iconv -f '.$from.' -t '.$to;
}

$alphabet = array(
    'rus' => 'áâ÷çäå³öúéêëìíîïðòóôõæèãþûýÿùøüàñ',
    'eng' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    );
$l = array('_','_','_','_','_','_');

$lang = lixgetlang();

$a = $alphabet[$lang];

if ($_GET[let] != '') { 
    $ll = explode('-',$_GET[let].'-0-0'); 
    $l1 = max(1,$ll[0]);
    $l2 = min(strlen($a),$ll[1]);
    $l2 = max($l2,$l1+1);

    $l1 = $a[$l1-1];
    $l2 = $a[$l2-1];

    $j=1; 
    for ($i=strpos($a,$l1);$i<=strpos($a,$l2);$i++) {
        $l[$j] = $a[$i];
        $j++;
    }

} 

$xml = domxml_open_file('publications.'.$lang.'.xml');
$xsl = domxml_xslt_stylesheet_file('publications.'.$lang.'.xsl');

$result = $xsl->process($xml, array('l1' => shiconv('koi8-r','utf-8',$l[1]),
                                    'l2' => shiconv('koi8-r','utf-8',$l[2]),
                                    'l3' => shiconv('koi8-r','utf-8',$l[3]),
                                    'l4' => shiconv('koi8-r','utf-8',$l[4]),
                                    'l5' => shiconv('koi8-r','utf-8',$l[5])
                                    ));

print $result->dump_mem();

?>
