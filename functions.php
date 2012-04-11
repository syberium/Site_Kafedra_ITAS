<?php

// php function library for ITAS web page
// Copyright (C) Daniel S. Kurushin 2003, 2004, 2005
// Developed for the CoLogNET project
// License: GPL

$xml = domxml_open_file('data.xml');
$html = '!NONE!';

// access log
$f = fopen('/home/itas/qlog/alog','a+');
if ($f) {
    $ref = urldecode($_SERVER[HTTP_REFERER]);
    if ($ref == '') $ref = '!NONE!'; // no referer detected
    if (strpos('=='.$ref,'itas.pstu') > 0) $ref = '!SELF!'; // same web page
    if (strpos($ref,'%') > 0) $ref = urldecode($ref); // sometimes yandex has double-encoded URIs
    fwrite($f,$_SERVER[REMOTE_ADDR]."||".urldecode($_SERVER[QUERY_STRING])."||".$_SERVER[HTTP_USER_AGENT]."||".$ref."||\n");
    fclose($f);
}
// end of access log

function mystrtolower($s) {
    return strtr($s,'áâ÷çäå³öúéêëìíîïðòóôõæèãþûýÿùøüàñABCDEFGHIJKLMNOPQRSTUVWXYZ','ÁÂ×ÇÄÅ£ÖÚÉÊËÌÍÎÏÐÒÓÔÕÆÈÃÞÛÝßÙØÜÀÑabcdefghijklmnopqrstuvwxyz');
}

// the search engine main function. Compares two strings using boolean operators in $qwords
function boolsearch($twords,$qwords,$commonwords) {
    $lt = sizeof($twords);
    $lq = sizeof($qwords);
    if ($commonwords!='no') for ($jj=0;$jj<$lt;$jj++) if (binsearch($twords[$jj],$commonwords)!=-1) $twords[$jj]='';
    $resstr='';$searchflag=true;
    for ($ii=0;$ii<$lq;$ii++) {
        $qw = $qwords[$ii]; 
        if ($qw[0]!='%' and $qw!='') {
            for ($jj=0;$jj<$lt;$jj++) {
                $tw = $twords[$jj];
                if ($tw!='' and levenshtein($tw,$qw)/strlen($tw) < 0.2) {
                    $resstr.=" true and ";
                    $searchflag=false;
                    break;
                }
            }
            if ($searchflag) $resstr.=" false and ";
            $searchflag=true;
        } else {
            $resstr.=$qw;
        }
    }
    $trans = array(" and %rbr" => " %rbr"," and %or" => " %or");
    $resstr=strtr($resstr,$trans);
    $trans = array("%not%" => "!","%or%"=>"%||%"," and "=>" && ","%lbr%"=>" ( ","%rbr%"=>" ) ");
    $resstr=strtr($resstr,$trans);
    $trans = array(" && %rbr" => " %rbr"," && %or" => " %or");
    $resstr=strtr('$bool_result='.trim($resstr).';',$trans);
    $trans = array("%" => " ","&&;" => ";");
    $resstr=strtr($resstr,$trans);
    $trans = array("  " => " ");
    $resstr=strtr($resstr,$trans);
    $trans = array(") " => ") && ",") " => ") && ");
    $resstr=strtr($resstr,$trans);
    $trans = array("&& ||" => "||","|| &&" => "||");
    $resstr=strtr($resstr,$trans);
    
    eval($resstr);
    return $bool_result;
}

// checks remote IP and returns true if it's in the list. 
// IP list hardcoded here for security reasons
function checkip() {
//    return true;
    $ip=$_SERVER[REMOTE_ADDR];
    return $ip == '192.168.0.234' || 
        $ip == '195.19.161.226' ||
        $ip == '195.19.161.4' ||
        $ip == '195.19.161.65' ||
        $ip == '195.19.161.1' ||
        $ip == '127.0.0.1';
}

// soft levengstein comparison
// not used
function eqstrings($s1,$s2) {
    $s1 = trim($s1); $s2 = trim($s2);
    $ls1 = strlen($s1);
    $ls2 = strlen($s2);    
    if ($s1 == '' && $s2 == '') return true;
    if ($s1 == '' && $s2 != '') return false;
    if ($s1 != '' && $s2 == '') return false;
    if (max($ls1,$ls2) / min($ls1,$ls2) > 5 / 3) return false;
    if ($s1 == $s2) return true;
    return levenshtein($s1,$s2)/$ls1 < 0.2;
}

//excludes common words from the array of words
function excludecommonwords($array,$commonwords) {
    for ($i=0;$i<sizeof($array);$i++) if (array_search($array[$i],$commonwords)!=false) $array[$i]='';
    return $array;
}

function getallowedtags() {
    return '<p><br><br/><h1><h2><h3><h4><li><ul><ol><img><hr><table><tr><td><th><thead><tbody><a><u><i><b><sup><sub><center><param><iframe><input><select><option><form>';
}
/*
function getchildcontent($object,$childname) {
    $res=$object->get_elements_by_tagname($childname);
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    if (sizeof($res)>0) $res=$res[0]->child_nodes(); else $res='!NONE!';
    if ($res!='' && $res != '!NONE!') $res=$res[0]->node_value(); else $res='!NONE!';
    return shiconv('utf-8','koi8-r',$res);
}*/

// returns contents of a child of the given object
function getchildcontent($object,$childname) {
    $res = $object->child_nodes();
    if (sizeof($res) > 0) {
        for ($i=0; $i<sizeof($res); $i++) {
            if ($res[$i]->node_name() == $childname) {
                $res = $res[$i]->child_nodes(); 
                break;
            }
        } 
    } else $res='!NONE!';
    if (sizeof($res)==0) $res = '!NONE!';
    if ($res!='' && $res != '!NONE!') $res = $res[0]->node_value(); else $res='!NONE!';
    return shiconv('utf-8','koi8-r',$res);
}

// returns 'common' (stop) words from the $file
function getcommonwords($file) {
    $res = file($file);
    for ($i=0;$i<sizeof($res);$i++) $res[$i] = trim($res[$i]);
    return $res;
}

// returns //$lang/contents 
// used for printing the title on the top of third-level menu
function getconttitle($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang);
    $data = $data->nodeset;
    
    return getchildcontent($data[0],contents);
}

// returns copyright info
// //copyright/c$lang
function getcopyright($lang,$mode) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//copyright');
    $data = $data->nodeset;
    
    return getchildcontent($data[0],'c'.$lang);
}

// used for 404 error generation
function getemptypage($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/empty');
    $data = $data->nodeset;
    
    return $data;
}

// returns html from //lang/mode/.../submode/file
// may return empty page if fails
// may also detect file or URL by 'http://' 
function gethtml($lang,$mode) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$mode);
    $data = $data->nodeset;
    
    if (sizeof($data) == 0) { $data = getemptypage($lang); }

    $html = getchildcontent($data[0],file);
    
    if ($html == '' or $html == '!NONE!' or filesize('html/'.$html) < 30 && strpos('=='.$html,'http://') == 0) {
        $data = $xpc->xpath_eval('//'.$lang.'/empty');
        $data = $data->nodeset;
        $html = getchildcontent($data[0],file);
    }
    if (strpos('=='.$html,'http://') > 0) { $html = file($html); } 
    else { $html = file('html/'.$html); }
    $html = implode(' ',$html);
    
    return $html;
}

// old language detection function
function get_language($ip) {
    if ($_GET[lang] != '') return $_GET[lang];
    return 'rus';
    if (strpos('=='.$ip,'192.168') == 2) return rus;
    return exec('[ `whois '.$ip.' | grep -c -i russia` -gt 0 ] && echo rus || echo eng');
}

// returns //lang/mode/.../submode/link
// used for menu generation
function getlinkname($lang,$name) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$name);
    $data = $data->nodeset;
    
    if (sizeof($data) == 0) { $data = getemptypage($lang); }

    return getchildcontent($data[0],link);
}

// main menu constructor
// uses menuid for sorting items
function getmainmenu($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/*[menuid > 0]');
    $data = $data->nodeset;
    
    $res = '';
    for ($i=0;$i<sizeof($data);$i++) {
        $id = getchildcontent($data[$i],menuid) - 1;
        // $id = $i + 1;
        // this also may work.
        // need to be discussed
        // produces a problem with //lang/search
        $res[$id][link] = getchildcontent($data[$i],link);
        $res[$id][mode] = $data[$i]->tagname;
    }
    return $res;
}

// recursive //lang/mode/title detection
// takes title of a previous mode if current is empty
// may fail in case of problems with getchildcontent
function getmodetitle($lang,$mode) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$mode);
    $data = $data->nodeset;
    
    if (sizeof($data) == 0) { $data = getemptypage($lang); }
    
    $title = getchildcontent($data[0],title);
    
    if (($title == '!NONE!' || $title == '') && trim($mode) != '') $title = getmodetitle($lang,getprevmode($mode));
    
    return $title;
}

// returns second level menu
// uses menuid for sorting items
function getsubmenu($lang,$mode) {
    
    $tm = gettopmode($mode);
    
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$tm.'/*[menuid > 0]');
    $data = $data->nodeset;
    for ($i=0;$i<sizeof($data);$i++) {
        $id = getchildcontent($data[$i],menuid);
        // $id = $i + 1;
        // this also may work.
        // need to be discussed
        $res[$id][link] = getchildcontent($data[$i],link);
        $res[$id][mode] = $tm.'/'.$data[$i]->tagname;
    }
    return $res;
}

// returns third level menu
// uses menuid for sorting items
function getsubsubmenu($lang,$mode) {
    
    $tm = gettopmode($mode);
    $sm = getonlysecmode($mode);
    
    if (!$sm) return;
    
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$tm.'/'.$sm.'/*[menuid > 0]');
    $data = $data->nodeset;
    for ($i=0;$i<sizeof($data);$i++) {
        $id = getchildcontent($data[$i],menuid);
        // $id = $i + 1;
        // this also may work.
        // need to be discussed
        $res[$id][link] = getchildcontent($data[$i],link);
        $res[$id][mode] = $tm.'/'.$sm.'/'.$data[$i]->tagname;
    }
    return $res;
}

// returns main page title
// from //lang/title
function gettitle($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang);
    $data = $data->nodeset;
    
    if (sizeof($data) == 0) { $data = getemptypage($lang); }

    return getchildcontent($data[0],title);
}

// returns top-level mode
// mode/submode => mode
function gettopmode($mode) {
    $a = split('/',$mode.'/');
    return $a[0];
}

// returns previous mode
// mode/submode/subsubmode => mode/submode
function getprevmode($mode) {
    $a = split('/',$mode);
    $res = '';
    
    for ($i=0;$i<sizeof($a)-1;$i++) {
        $res .= $a[$i].'/';
    }
    
    return substr($res,0,strlen($res)-1);
}

// returns second level mode
// mode/submode/subsubmode => mode/submode
// a special case of getprevmode
function getsecmode($mode) {
    $a = split('/',$mode.'/');
    return $a[0].'/'.$a[1];
}

// returns second level mode
// mode/submode/subsubmode => submode
// a special case of getonlysecmode
function getonlysecmode($mode) {
    $a = split('/',$mode.'/');
    return $a[1];
}

// not used. supposed to return text on a 'search' button
function getsearchbutton($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/search');
    $data = $data->nodeset;
    return getchildcontent($data[0],button);
}

// Return text on a 'search' panel
function getsearchtext($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/search');
    $data = $data->nodeset;
    return getchildcontent($data[0],qtext);
}

// returns array of words from a string
function getwords($str) {
    $wordendings = ' ,./\\|@^[](){};:<>?!+*=~`\"\'@#$&'."\n";
    $lengthofwordendings = strlen($wordendings);
    $str = mystrtolower(trim(strtr($str,$wordendings,str_repeat(' ',$lengthofwordendings)))).' ';
    $result[0] = "empty";
    $n = 0;
    $c = 0;
    $l = strlen($str);
    while ($l > 2) {
        $p = strpos($str,' '); if ($p=='') $p=1;
        if ($p > 2 and $p < 20) {
            $result[$n] = trim(substr($str,0,$p));
            $str = ltrim(substr($str,$p));
            $n++;
        } else {
            $str = ltrim(substr($str,$p));
        }
        $l = strlen($str);
        if ($c > 1500) break;
        $c++;
    }
    return $result;
}

// returns ".ie" if browser is MSIE or Opera
// required because of lack of 'position:fixed' support in 
// these browsers.
function isie() {
// Opera 8 is a 'bit' different browser
// forms (at least) are drawn differently
// so the main.op8.*.css were created
// this why we need this
// older Opera (7.x and older)
    if (strpos('--'.$_SERVER[HTTP_USER_AGENT],'Opera') > 0 &&
	strpos($_SERVER[HTTP_USER_AGENT],'8.') < 0
       ) return '.ie';
// Opera 8.0 and newer
    if (strpos('--'.$_SERVER[HTTP_USER_AGENT],'Opera') > 0 &&
	strpos($_SERVER[HTTP_USER_AGENT],'8.') > 0
       ) return '.op8';
// all MSIE clones
    if (strpos($_SERVER[HTTP_USER_AGENT],'MSIE') > 0
       ) return '.ie';
    return '';
}

// creates an index string for a text
// basing on a dictionary
function makeindex($lang) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('/document/'.$lang.'//*[file != ""]');
    $data = $data->nodeset;

    for ($i=0;$i<sizeof($data);$i++) {
        $id = $data[$i]->tagname;
        
        $element = $xpc->xpath_eval('/document/'.$lang.'//'.$id);
        $element = $element->nodeset;	
        $children = $element[0]->child_nodes();        
        for ($j=0;$j<sizeof($children);$j++) if ($children[$j]->tagname == 'voc') $element[0]->remove_child($children[$j]);

        $file = getchildcontent($data[$i],file);
        if (strpos('=='.$file,'http://') > 0) { $file = file($file); } 
        else { $file = file('html/'.$file); }
        
        if (strlen(implode(' ',$file)) > 10) {
            $voctext = '';
            $voctext = array('');
            $voctext = excludecommonwords(my_array_unique(getwords(strip_tags(strtr(implode(' ',$file),array('>' => '> '))))),getcommonwords('commonwords.'.$lang.'.dat'));
            sort($voctext);

            $voc = $xml->create_element('voc');
            $voc->set_content(shiconv('koi8-r','utf-8',mystrtolower(trim(implode(' ',$voctext))))); //shiconv(implode(' ',$voctext))
            $element[0]->append_child($voc);
            
        }
    }
}

// html adaptation for the web site
// may work with SO, OO and probably MSWord HTML's
function markup($html) {
    //return $html;
    $dist = 0;
    //$html = strtr($html,"\n",' ');
    $html = preg_replace("/(<\/?)(\w+)([^>]*>)/e","'\\1'.mystrtolower('\\2').mystrtolower('\\3')",$html);
    $html = preg_replace('/<p align=.{0,1}center.{0,1}>/','<center>',$html);
    $html = preg_replace('/<td.*>\s+<p>/','<td>',$html);
    $html = preg_replace('/<th.*>\s+<p>/','<th>',$html);
    $html = preg_replace('/<\/p>\s+<\/td>/','</td>',$html);
    $html = preg_replace('/<\/p>\s+<\/th>/','</th>',$html);
    $html = preg_replace('/<style.*style>/','<!-- removed style -->',$html);
    $html = preg_replace('/<script.*script>/','<!-- removed style -->',$html);
    $html = stripslashes($html);
    $html = strip_tags($html,getallowedtags());
    
//    $html = preg_replace('/<th.*>\b+<p.*>/','<th>',$html);
    $a = split('<h',$html); 
    $res = $a[0];
    for ($i=1;$i<sizeof($a);$i++) {
        $res .= '<a name="'.$i.'"></a><h'.$a[$i];
#        if (strlen(strip_tags($res)) > 1500) { $res .= '<a href=#><img align="right" src="images/top-button.png" alt="top" border="0"/></a>'; }
    }
    return $res;
}

// inserts <b> </b> tags around words from $q into $text, tryes to shorter string down to $len
function markupstring($text,$q,$length) {
    $maxn = 4 / sizeof($q);
    $res = shorterstring($text,round($length/4),20);
    $text = getwords($text);
    $found = 0;
    for ($i=0;$i<sizeof($q);$i++) { 
        $n = 0;
        for ($j=0;$j<sizeof($text);$j++) {
            if ($text[$j] != '' && levenshtein($text[$j],$q[$i])/strlen($text[$j]) < 0.2) {
                $res = $res.' ';
                if ($found == 0) for ($k=$j-5;$k<$j+5;$k++) {
                    $found = 0;
                    for ($l=0;$l<sizeof($q);$l++) if ($text[$k] != '' && levenshtein($text[$k],$q[$l])/strlen($text[$k]) < 0.2) { $found = 1; break; }
                    if ($found == 1) {
                        $res = $res.' <b>'.$text[$k].'</b>';
                    } else {
                        $res = $res.' '.$text[$k];
                    }
                }
                $res = $res.' ... ';
                $n++;
            }
            if ($n > $maxn) { break; }
        }
        if (strlen($res) > $length) { break; }
        if ($found = 1) { break; }
    }
    return $res;
}

// checks for mode validity
// required fields:
//                   file
//                   link
//                   title
function modeexists($lang,$mode) {
    global $xml;
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('//'.$lang.'/'.$mode);
    $data = $data->nodeset;
    if (sizeof($data) > 0) {
        $file  = getchildcontent($data[0],file);
        $link  = getchildcontent($data[0],link);
        $title = getchildcontent($data[0],title);
        return true;
    }
    return false;
}

// replacement of the array_unique() function which understands the %% seq
function my_array_unique($a) {
    $n=0;
    $l=sizeof($a);
    for ($i=0;$i<$l-1;$i++) {
        $isdup=false;
        if ($a[$i][0] != '%' && $a[$i][$l-1] != '%') for ($j=$i+1;$j<$l;$j++) if (eqstrings($a[$i],$a[$j])) { $isdup=true; break; }
        if (!$isdup) { $res[$n] = $a[$i]; $n++; }
    }
    $res[$n]=$a[$l-1];
    return $res;
}

// not used
function print_title_page($lang) { ?>
<!DOCTYPE / public "-//w3c//dtd xhtml 1.0 transitional//en"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo gettitle($lang); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=KOI8-R"/>
  <link rel="stylesheet" href="index.<?php echo $lang;?>.css" type="text/css"/>
</head>
<script type="text/javascript"><!--

function hideall() {
  this.thediv = document.getElementById('dep');  this.thediv.style.visibility = 'hidden';
  this.thediv = document.getElementById('enr');  this.thediv.style.visibility = 'hidden';
  this.thediv = document.getElementById('prt');  this.thediv.style.visibility = 'hidden';
  this.thediv = document.getElementById('pstu');  this.thediv.style.visibility = 'hidden';
  this.thediv = document.getElementById('sci');  this.thediv.style.visibility = 'hidden';
  this.thediv = document.getElementById('std');  this.thediv.style.visibility = 'hidden';
}

function animate(id) {
  hideall();

  this.thediv = document.getElementById(id);
  this.thediv.style.visibility = 'visible';
}
--></script>
 <body bgcolor="white" text="black" link="#444777" alink="#444777" vlink="#444777">
<table width="100%" height="100%" border="0px" cellspacing="0px" cellpadding="0px">
<tr><td align="center"><div class="background-lines">
<a href="?mode=main"><img alt="" class="logo" src="images/logo.jpeg" border="0px" /></a>
<div class="line-sci">&nbsp;</div>
<div class="line-std">&nbsp;</div>
<div class="line-prt">&nbsp;</div>
<div class="line-enr">&nbsp;</div>
<div id="dep" class="sel-dep">&nbsp;</div>
<div id="enr" class="sel-enr">&nbsp;</div>
<div id="prt" class="sel-prt">&nbsp;</div>
<div id="pstu" class="sel-pstu">&nbsp;</div>
<div id="sci" class="sel-sci">&nbsp;</div>
<div id="std" class="sel-std">&nbsp;</div>
<a href="?mode=dep"><img alt="" onMouseOver='javascript:animate("dep");' onMouseOut='javascript:hideall()' src="images/text-dep.png" class="text-dpt" border="0px" /></a>
<a href="?mode=enr"><img alt="" onMouseOver='javascript:animate("enr");' onMouseOut='javascript:hideall()' src="images/text-enr.png" class="text-enr" border="0px" /></a>
<a href="?mode=prt"><img alt="" onMouseOver='javascript:animate("prt");' onMouseOut='javascript:hideall()' src="images/text-prt.png" class="text-prt" border="0px" /></a>
<a href="http://www.pstu.ac.ru"><img alt="" onMouseOver='javascript:animate("pstu");' onMouseOut='javascript:hideall()' src="images/text-pstu.png" class="text-pstu" border="0px" /></a>
<a href="?mode=sci"><img alt="" onMouseOver='javascript:animate("sci");' onMouseOut='javascript:hideall()' src="images/text-sci.png" class="text-sci" border="0px" /></a>
<a href="?mode=std"><img alt="" onMouseOver='javascript:animate("std");' onMouseOut='javascript:hideall()' src="images/text-std.png" class="text-std" border="0px" /></a>
</div></td></tr>
</table>
<noscript><center>
[<a href="?mode=main"><?php echo getlinkname($lang,main); ?></a
>]&nbsp;[<a href="?mode=dep"><?php echo getlinkname($lang,dep); ?></a
>]&nbsp;[<a href="?mode=enr"><?php echo getlinkname($lang,enr); ?></a
>]&nbsp;[<a href="?mode=prt"><?php echo getlinkname($lang,prt); ?></a
>]&nbsp;[<a href="?mode=sci"><?php echo getlinkname($lang,sci); ?></a
>]&nbsp;[<a href="?mode=std"><?php echo getlinkname($lang,std); ?></a
>]&nbsp;[<a href="http://www.pstu.ac.ru"><?php echo getlinkname($lang,pstu); ?></a
>]
</center></noscript>
</body>
</html>

<?php }

// Prepares query string
// $res[query]   -- query string
// $res[qwords]  -- words from query string
// $res[lq]      -- length of the query
// $res[rlq]     -- "real" length of the query
// $res[parsedq] -- processed query string for output
function prepareqstring($query,$commonwords) {
    $query = strtr($query,'!@#$%^&*-_=+":;,.[]{}\|/?<>~`??',' '); 
    $query = strtr($query,'áâ÷çäå³öúéêëìíîïðòóôõæèãþûýÿùøüàñABCDEFGHIJKLMNOPQRSTUVWXYZ','ÁÂ×ÇÄÅ£ÖÚÉÊËÌÍÎÏÐÒÓÔÕÆÈÃÞÛÝßÙØÜÀÑabcdefghijklmnopqrstuvwxyz');
    $trans = array(" " => "  ");
    $query = strtr(" ".$query." ",$trans); 
    $trans = array(" not " => " %not% ", " or " => " %or% ", "(" => " %lbr% ", ")" => " %rbr% ", " and " => " ");
    $query = strtr(" ".$query." ",$trans); 
    $qwords = getwords($query); 
    $lq = min(sizeof($qwords),100);
    $qwords = my_array_unique($qwords);
    $rlq=min(sizeof($qwords),100);
    if ($lq!=0) for ($i=0;$i<$lq;$i++) if (!(array_search($qwords[$i],$commonwords)===FALSE)) { $qwords[$i]=''; $rlq--; }
    $parsedq=implode(' ',$qwords);
    $trans = array("%or%" => "<b>or</b> ","%not%"=>"<b>not</b>","%rbr%"=>")","%lbr%"=>"(");
    $parsedq=strtr($parsedq,$trans);
    $res[query]   = $query;
    $res[qwords]  = $qwords;
    $res[lq]      = $lq;
    $res[rlq]     = $rlq;
    $res[parsedq] = $parsedq;
    return $res;
}

// prints //$lang/contents 
// used for printing the title on the top of third-level menu
function printconttitle($lang) {
    echo getconttitle($lang);
}

// print out a prepared html code
function printhtml($lang,$mode,$query) {
    global $html;
    if ($query != '') {
        $html = querysearch($lang,$query);
    } else {
        $html = gethtml($lang,$mode);
    }
    $html = markup($html);
    echo $html;
    ?><p style="border-top: 1px solid black;">&copy; <?php echo getcopyright($lang,$mode); ?> <a href="mailto:webadmin@itas.pstu.ru">webadmin@itas.pstu.ru</a></p><?php
}

// not used
function printhtmlshiconv($lang,$mode,$query,$f,$t) {
    global $html;
    
    if ($query != '') {
        $html = querysearch($lang,$query);
    } else {
        $html = gethtml($lang,$mode);
    }
    $html = markup($html);
    echo shiconv($f,$t,$html);
    ?><p style="border-top: 1px solid black;">&copy; <?php echo getcopyright($lang,$mode); ?></p><?php
}

// prints contents of top-level <h1> in the panel
function printheader() {
    global $html;
    if ($query != '') {
        // do query        
    } else {
        $a = split('<h1>',$html); 
        if (sizeof($a) > 1) {
            $a = split('</h1>',$a[1]);
            ?><table class="header"><tr><td><?php
            echo $a[0];
            ?></td></tr></table><?php
        } else {
        }
    }
}

// prints all other '<h2+> headers as 
// third-level menu
function printheaders() {
    global $html;
    
    $bt = '';
    if ($query != '') {
        // do query        
    } else {
        $a = split('<h',$html); 
        ?><table border="0" cellspacing="0" cellpadding="1" class="headers"><tr><td><img alt="" src="images/empty.png" height="2px"/></td></tr><?php
        if (sizeof($a) > 2) for ($i=0;$i<sizeof($a);$i++) {
            $b = split('</h.>',$a[$i]);
            $b = explode('>',$b[0]);
            $pl = ($b[0]-1) * 7 + 3;
            $text = substr(strip_tags($b[1]),0,50);
            if ($b[0] / 2 == round($b[0] / 2)) { $bt = '-'; } else { $bt = ''; }
            if ($b[0] > 0) echo '<tr><td onClick="javascript:document.location ='."'#".$i."'".';" class="headers" style="padding-left:'.$pl.'px"><img src="images/bullet'.$bt.'.png" align="middle" alt="" /><a class="headers" href="#'.$i.'">'.str_repeat('&nbsp;&nbsp;',0).' '.$text.'</a></td></tr>';
        }?></table><img alt="" src="images/headers-bottom.png"/><?php
    }
}

// prints lang buttons
function printlang($curlang,$lang,$mode) {
    if ($curlang == $lang) { $pr = "sel-btn-sml"; $a=''; $ca=''; } 
    else {$pr = 'btn-sml'; 
        $a = "<a class='menu' href='index.php?mode=".$mode."&amp;lang=".$lang."' style=''>";
        $ca = "</a>";
    } 
    echo $a;
    echo "<img alt='' src='images/".$pr."-l.png' border='0'/>";
    echo "<img alt='".$lang."' src='images/".$pr."-".$lang.".png' border='0'/>";
    echo "<img alt='' src='images/".$pr."-r.png' border='0'/>".$ca;
}

// prints a menu
function printmenu($lang,$mode) {
    $menu = getmainmenu($lang);
    ?><table border='0' cellpadding='0' cellspacing='0'><tr><?php
    for ($i=0;$i<sizeof($menu);$i++) {
        $menu[$i][link] = preg_replace('/ /','&nbsp;',$menu[$i][link]);
        if (gettopmode($mode) == $menu[$i][mode]) { $pr = "sel-btn-main"; $a='b'; $href=''; } else {$pr = 'btn-main'; $a='a'; $href=' href="index.php?mode='.$menu[$i][mode].'&amp;lang='.$lang.'"'; } 
        echo '<td background="images/'.$pr.'-l.png" height="23px" width="13px"><img src="images/empty.png" width="0px" height="23px" alt="" /></td>';
        echo '<td background="images/'.$pr.'-m.png"><'.$a.' class="menu" style="font-size: 13px;"'.$href.'>'.$menu[$i][link].'</'.$a.'></td>';
        echo '<td background="images/'.$pr.'-r.png" width="13px">&nbsp;</td>';
    }
    echo '</tr></table>';
}

// prints "print" buttons
function printprintmode($lang) {
    if ($_SERVER[QUERY_STRING] == '') { $_SERVER[QUERY_STRING] = 'mode=dep&lang='.$lang; }
    $pr = 'btn-sml'; 
    echo "<a class='menu' target='_blank' href='index.php?".strtr($_SERVER[QUERY_STRING],array('&' => '&amp;'))."&amp;print=true' style=''>";
    echo "<img alt='' src='images/".$pr."-l.png' border='0'/>";
    echo "<img alt='print' src='images/".$pr."-print.png' border='0'/>";
    echo "<img alt='' src='images/".$pr."-r.png' border='0'/></a>";
}

// prints search panel
function printsearchform($lang,$query) {
    ?><form class='search' name='search'><div style="position:absolute"><input type='hidden' name='mode' value='search' /><input type='hidden' name='lang' value='<?php echo $lang; ?>' /></div><?php
    ?><table border='0' cellpadding='0' cellspacing='0'><tbody><tr><?php    
    ?><td background='images/search-panel-l.png' height="27px" width="13px"><img src="images/empty.png" width="13px" height="1px" alt="" /></td><?php
    ?><td background='images/search-panel-m.png' class='search'><?php echo getsearchtext($lang); ?></td><?php
    ?><td background='images/search-panel-m.png'>&nbsp;</td><?php
    ?><td background='images/search-panel-m.png'><input type='text' class='text' name='query' value='<?php echo $query; ?>' /></td><?php
    ?><td background='images/search-panel-m.png'>&nbsp;</td><?php
    ?><td background='images/search-panel-m.png'><input class='button' type='submit' value=' ' /></td><?php
    ?><td background='images/search-panel-r.png' width="14px"><img src="images/empty.png" width="14px" height="1px" alt="" /></td><?php
    ?></tr></tbody></table></form><?php
}

// prints a submenu
function printsubmenu($lang,$mode,$query) {
    $menu = getsubmenu($lang,$mode);
    
    $menu[0][mode] = gettopmode($mode);
    $menu[0][link] = getlinkname($lang,gettopmode($mode));
    ?><table border='0' cellpadding='0' cellspacing='0'><tr style='height:23px'><?php
    for ($i=0;$i<sizeof($menu);$i++) {
        $menu[$i][link] = preg_replace('/ /','&nbsp;',$menu[$i][link]);
        if (getsecmode($mode) == $menu[$i][mode] || $mode == $menu[$i][mode]) { $pr = "sel-btn-main"; $a='b'; $href=''; } else {$pr = 'btn-main'; $a='a'; $href=' href="index.php?mode='.$menu[$i][mode].'&amp;lang='.$lang.'"'; } 
        echo '<td background="images/'.$pr.'-l.png" height="23px" width="13px">&nbsp;</td>';
        echo '<td background="images/'.$pr.'-m.png"><'.$a.' class="menu" style="font-size: 13px;"'.$href.'>'.$menu[$i][link].'</'.$a.'></td>';
        echo '<td background="images/'.$pr.'-r.png" width="13px">&nbsp;</td>';
    }
    ?></tr></table><?php
}

// prints a third-level menu (NOT headers)
function printsubsubmenu($lang,$mode,$query) {
    if (strlen($query) > 2) {
        global $searchtopics;

        $bt = '';
        if (!$searchtopics) return;
    
        $menu = $searchtopics;
        $add = 1;
    } else {
        $menu = getsubsubmenu($lang,$mode);
        $bt = '';
        if (!$menu) return;
    
        $menu[0][mode] = getsecmode($mode);
        $menu[0][link] = getlinkname($lang,getsecmode($mode));
        $add = 0;
    }
    
    ?><table border="0" cellspacing="0" cellpadding="1" class="subsubmenu"><tr><td><img alt="" src="images/empty.png" height="2px"/></td></tr><?php
    for ($i=1;$i<min(sizeof($menu)+$add,30);$i++) { 
        echo '<tr><td onClick="javascript:document.location='."'".'index.php?mode='.$menu[$i][mode].'&amp;lang='.$lang."';".'" class="subsubmenu"><img src="images/bullet'.$bt.'.png" align="middle" />';
        if ($menu[$i][mode] != $mode) {
            echo '<noscript><a class="subsubmenu" href="index.php?mode='.$menu[$i][mode].'&amp;lang='.$lang.'"></noscript>'.$menu[$i][link].'<noscript></a></noscript>';
        } else {
            echo '<b class="subsubmenu">'.$menu[$i][link].'</b>';
        }
        echo '</td></tr>';
	}?></table><?php
}

// prints a web page. 
// Main function of a web page
// layout defined here.
function print_normal_page($lang,$mode,$query) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title><?php echo getmodetitle($lang,$mode); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=KOI8-R"/>
  <link rel="stylesheet" href="main<?php echo isie(); ?>.<?php echo $lang;?>.css" type="text/css"/><?php 
    if (isie() != '') { ?><link rel="SHORTCUT ICON" href="/images/favicon.ico" type="image/x-icon" /><?php }
    else { ?><link rel="icon" href="/images/icon.png" type="image/png" /><?php } ?>
</head>
<body>
  <div class="content" style="z-index:0"><?php if ($mode == 'std/exams') { printhtmlshiconv($lang,$mode,'','windows-1251','koi8-r'); } else { printhtml($lang,$mode,$query); } ?></div>
  <div class="title">
  <div class="logo" onclick="javascript:document.location.replace('index.php?mode=dep&amp;lang=<?php echo $lang; ?>')">&nbsp;</div>
  <div class="top-title-left">&nbsp;</div>
  <div class="top-title-main"><img alt="" class="title-img" src="images/top-title-main.png" /></div>
  <div class="top-title-text"><?php echo gettitle($lang,$mode); ?></div>
    <div class="print"><?php printprintmode($lang); ?></div>
    <div class="lang"><?php if (modeexists(rus,$mode)) printlang($lang,rus,$mode); ?><?php if (modeexists(eng,$mode)) printlang($lang,eng,$mode); ?></div>
    <div class="menu"><?php printmenu($lang,$mode); ?><?php printsearchform($lang,$query); ?></div>
    <div class="submenu">
      <div class="submenu-line"><div class="contents"><?php printconttitle($lang); ?></div><?php printheader(); ?></div>
      <div class="submenu-items"><?php printsubmenu($lang,$mode,$query); ?></div>
    </div>
  </div>
  <div class="headers"><?php printsubsubmenu($lang,$mode,$query);?><?php printheaders();?></div>
<!-- Yandex.Metrika counter -->
<div style="display:none;"><script type="text/javascript">
(function(w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter2572939 = new Ya.Metrika(2572939);
             yaCounter2572939.clickmap(true);
             yaCounter2572939.trackLinks(true);
        
        } catch(e) {}
    });
})(window, 'yandex_metrika_callbacks');
</script></div>
<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
<noscript><div style="position:absolute"><img src="//mc.yandex.ru/watch/2572939" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</body>
</html>
<?php }

// prints a web page in print mode. 
function print_print_page($lang,$mode,$query) { 
    global $ref;
// This is intended to fix indexing of 'print mode' pages.    
// If referer is NOT same web page we will display 
// The standard page no matter if print=true is specified
// Also it will prevent the 'Print' dialog if one has
// bookmarked the print version of the page.
    if ($ref == '!SELF!') { ?>        
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><noindex>
<head>
    <title><?php echo getmodetitle($lang,$mode); ?> - PRINTING</title>
    <meta http-equiv="Content-Type" content="text/html; charset=KOI8-R"/><?php    
    if (isie() != '') { ?><link rel="SHORTCUT ICON" href="/images/favicon.ico" type="image/x-icon" /><?php }
    else { ?><link rel="icon" href="/images/icon.png" type="image/png" /><?php } ?>
</head>
<body>
<?php printhtml($lang,$mode,$query); ?>
</body>    
<script>window.print();</script>
</noindex></html><?php } else {
// So, the referer is NOT itas.pstu.ru, so let's print normal page...
    print_normal_page($lang,$mode,$query);
    }
}

// search engine caller 
function querysearch($lang,$query) {
    global $xml;
    global $searchtopics;
    
    $xpc = $xml->xpath_new_context();

    $data = $xpc->xpath_eval('/document/'.$lang.'//*[voc != ""]');
    $data = $data->nodeset;

    $res = $xpc->xpath_eval('/document/'.$lang.'/search');
    $res = $res->nodeset;
    $resh1 = getchildcontent($res[0],'resh1');
    $yourq = getchildcontent($res[0],'yourq');
    $sorry = getchildcontent($res[0],'sorry');
    
    $nn = 1; $res = 'no';
    $commonwords = getcommonwords('commonwords.'.$lang.'.dat');
    $q = prepareqstring($query,$commonwords);
    
    $result = '<h1>'.$resh1.'</h1>';
    $result .= "<p>".$yourq." <u>".$q[parsedq]."</u></p>";
    for ($i=0;$i<sizeof($data);$i++) {
        $voc = getchildcontent($data[$i],voc);
        if (boolsearch(explode(' ',$voc),$q[qwords],'no')) {
            $mode = $data[$i]->tagname;
            $parentname = ''; $node = $data[$i];
            while ($parentname != $lang) {
                $parent = $node->parent_node();$node = $parent;
                $parentname = $parent->tagname;
                if ($parentname != $lang) $mode = $parentname.'/'.$mode;
            }
            $file = getchildcontent($data[$i],file);
            $title = getchildcontent($data[$i],title); $node = $data[$i]; $addlink = false; $addppp = false;
            while ($title == '!NONE!' || $title == '') {
                $parent = $node->parent_node();$node = $parent;
                $title = getchildcontent($node,title);
                if ($addlink) $addppp = true;
                $addlink = true;
            }
            $link = getchildcontent($data[$i],link);
            if ($addppp)  $title .= ' / ...';
            if ($addlink) $title .= ' / '.$link;
            if (strlen($title)<10) $title = $link;
            
            $searchtopics[$nn][mode] = $mode;
            $searchtopics[$nn][link] = $link;

            $res = 'yes';
            
            if (strpos('=='.$file,'http://') > 0) { $file = file($file); } 
            else { $file = file('html/'.$file); }
            
            $result .= '<p>'.$nn.'.&nbsp;<a style="color:black;" href="index.php?mode='.$mode.'">'.$title.'</a><br />';
            $nn++;
            $result .= markupstring(strip_tags(implode(' ',$file)),$q[qwords],1000);
            $result .= '</p>';
        }
    }
    if ($res == 'no') $result = '<h1>'.$resh1.'</h1><p>'.$sorry.'</p>';
// query log
    $f = fopen('/home/itas/qlog/qlog','a');
    if ($f) {
        fwrite($f,$_SERVER[REMOTE_ADDR].";".$q[parsedq].";".$i.";".$nn.";\n");
        fclose($f);
    }
// end of query log
    return $result;
}

// replacement for "iconv()" function
// based on unix shell command "iconv"
function shiconv($from,$to,$str) {
    return exec(' echo "'.$str.'" | iconv -f '.$from.' -t '.$to);
//    echo ' echo "'.$str.'" | iconv -f '.$from.' -t '.$to;
}

// returns shorter version of the string
function shorterstring($str,$len,$var){
    $l = strlen($str);
    if ($l>$len) {
        if ($var>=$len || $var<5) $var=15;
        $delimeters='.!?';
        $s=substr($str,$len-$var,$var);
        for ($i=0;$i<strlen($delimeters);$i++) $p[$i]=strpos($s,$delimeters[$i]);
        $p1=max($p);
        if (!$p1) {
            $delimeters=', ';
            $s=substr($str,$len-$var,$var);
            for ($i=0;$i<strlen($delimeters);$i++) $p[$i]=strpos($s,$delimeters[$i]);
            $p1=max($p);
        }
        if ($p1>0) { $result=substr($str,0,$len-$var+$p1); } else { $result=substr($str,0,$len); }
        $result = trim($result)."...";
    } else $result = $str;
    return $result;
}

?>
