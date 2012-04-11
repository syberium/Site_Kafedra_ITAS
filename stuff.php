<?php

include 'lang.php';


// replacement for "iconv()" function
// based on unix shell command "iconv"
function shiconv($from,$to,$str) {
    return exec(' echo "'.$str.'" | iconv -f '.$from.' -t '.$to);
//    echo ' echo "'.$str.'" | iconv -f '.$from.' -t '.$to;
}

function get_language($ip) {
    if ($_GET[lang] != '') return $_GET[lang];
    if (strpos('=='.$ip,'192.168') == 2) return rus;
    return exec('[ `whois '.$ip.' | grep -c -i russia` -gt 0 ] && echo rus || echo eng');
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

function getchildcontent($object,$childname) {
    $res=$object->get_elements_by_tagname($childname);
    if (sizeof($res)>0) $res=$res[0]->child_nodes(); else $res='!NONE!';
    if (sizeof($res)==0) $res='';
    if ($res!='' && $res != '!NONE!') $res=$res[0]->node_value(); else $res='!NONE!';
    return shiconv('utf-8','koi8-r',$res);
}

$lang = lixgetlang();

$xml = domxml_open_file('data.xml');
$constime = domxml_open_file('constime.xml');
$xpc_constime = $constime->xpath_new_context();

$html = '!NONE!';

if ($_GET[person] != '') {
    $person = $_GET[person];
    $xpc = $xml->xpath_new_context();

    $titles = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/nobody');
    $titles = $titles->nodeset;
    
    foreach (array(time, science, position, grade, addinfo) as $title) {
        $a[$title] = getchildcontent($titles[0],$title);
    }
    $titles = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/nobody/contact');
    $titles = $titles->nodeset;
    foreach (array(title, email, phone, room) as $title) {
        $b[$title] = getchildcontent($titles[0],$title);
    }
    
    $titles = array_merge($a,$b);
    
    $data = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/'.$person);
    $data = $data->nodeset;
    
    $fio = getchildcontent($data[0],fullname);
    $time = getchildcontent($data[0],time);
    $photo = getchildcontent($data[0],photo);
    $position = getchildcontent($data[0],position);
    $grade = getchildcontent($data[0],grade);
    $addinfo = getchildcontent($data[0],addinfo);
    
    $contacts = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/'.$person.'/contact');
    $contacts = $contacts->nodeset;
    
    $datacons = $xpc_constime->xpath_eval('/document/person[fullname = "'.shiconv('koi8-r','utf-8',$fio).'"]');
    $datacons = $datacons->nodeset;
    
    if (sizeof($datacons) > 0) {
        $time = $datacons[0]->get_elements_by_tagname('time');
        if (sizeof($time) > 0) for ($i=0;$i<sizeof($time);$i++) {
            $a = $time[$i]->child_nodes();
            if ($a[0]) { $timetext .= $a[0]->node_value().'<br/>'; }
        }
        $timetext = shiconv('utf-8','koi8-r',$timetext);
    } else {
        $timetext = '!NONE!';
    }
    
    foreach (array(email, phone, room) as $con) {
        $contact[$con] = '';
        for ($i=0; $i<sizeof($contacts); $i++) {
            $c = getchildcontent($contacts[$i],$con);
            if ($c != '!NONE!') $contact[$con] .= $c.'; <br />';
        }
        
        if ($contact[$con] != '') $contact[$con] = substr($contact[$con],0,strlen($contact[$con])-8).'.';
    }
    
    $science = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/'.$person.'/science/topic');
    $science = $science->nodeset;
    
    $stopics = '';
    for ($i=0;$i<sizeof($science);$i++) {
        $a = $science[$i]->child_nodes();
        $a = $a[0]->node_value();
        $stopics .= '<li>'.shiconv('utf-8','koi8-r',$a).'</li>';
    }
    
    ?><h1><?php echo $fio; ?></h1><?php
    if ($photo != '!NONE!') { ?><img src='photos/<?php echo $photo; ?>' border='1px' align='right' alt='<?php echo $fio; ?>'/><?php }
    ?><table><?php
    if ($position != '!NONE!') { ?><tr><td style='text-align:right;font-weight:bold;'><?php echo $titles[position]?></td><td><?php echo $position; ?></td></tr><?php }
    if ($grade != '!NONE!') { ?><tr><td style='text-align:right;font-weight:bold;'><?php echo $titles[grade]?></td><td><?php echo $grade; ?></td></tr><?php }
    if (sizeof($contact) != 0) { ?><tr><td style='text-align:right;font-weight:bold;'>&nbsp;</td><td><?php
        foreach (array(email, phone, room) as $con) if ($contact[$con] != '') {
            echo '<b>'.$titles[$con].'</b>'.$contact[$con].'<br/>';
        }
        ?><?php
        ?></td></tr><?php 
    }
    if ($stopics != '') { ?><tr><td valign='top' style='text-align:right;font-weight:bold;'><br /><?php echo $titles[science]; ?></td><td><ul><?php echo $stopics; ?></ul></td></tr><?php }
    if ($addinfo != '!NONE!') { ?><tr><td valign='top' style='text-align:right;font-weight:bold;'><?php echo $titles[addinfo]; ?></td><td><?php echo $addinfo; ?></td></tr><?php }
    if ($timetext != '!NONE!') { ?><tr><td valign='top' style='text-align:right;font-weight:bold;'><?php echo $titles[time]; ?></td><td><?php echo $timetext; ?></td></tr><?php }
    ?></table><?php

} else {
    $xpc = $xml->xpath_new_context();

    $h1 = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers');
    $h1 = $h1->nodeset;
    
    $h1 = getchildcontent($h1[0],link);
    
    $titles = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/nobody');
    $titles = $titles->nodeset;
    
    foreach (array(fullname, time, stepdol) as $title) { $a[$title] = getchildcontent($titles[0],$title); }
    $titles = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/nobody/contact');
    $titles = $titles->nodeset;
    foreach (array(title, email, phone, room) as $title) { $b[$title] = getchildcontent($titles[0],$title); }
    
    $titles = array_merge($a,$b);
    
    $data = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/*[fullname != ""]');
    $data = $data->nodeset;
    
    ?><h1><?php echo $h1; ?></h1><?php
    ?><table cellspacing="0" cellpadding="3" width="100%" style="border: 2px solid black" frame="box" rules="all"><?php
    ?><thead><tr><?php
    ?><th bgcolor='white' width='5%'>&#8470</th><?php
    ?><th bgcolor='white' width='20%'><?php echo $titles[fullname]; ?></th><?php
    ?><th bgcolor='white' width='45%'><?php echo $titles[stepdol];  ?></th><?php
    ?><th bgcolor='white' width='30%'><?php echo $titles[title]; ?></th><?php
    ?></tr></thead><tbody><?php
    $n = 1; foreach ($data as $record) if ($record->tagname != nobody) {
        $fullname = getchildcontent($record,fullname);
        $time = getchildcontent($record,time);
		$position = getchildcontent($record,position);
		$grade = getchildcontent($record,grade);
        if ($_SERVER[HTTP_USER_AGENT] == '') {
            $link = '?mode=dep/staff/teachers/'.$record->tagname.'&amp;lang='.$lang;
        } else {
            $link = '?person='.$record->tagname;
        }
        $person = $record->tagname;

        $contacts = $xpc->xpath_eval('/document/'.$lang.'/dep/staff/teachers/'.$person.'/contact');
        $contacts = $contacts->nodeset;
    
        foreach (array(email, phone, room) as $con) {
            $contact[$con] = '';
            for ($i=0; $i<sizeof($contacts); $i++) {
                $c = getchildcontent($contacts[$i],$con);
                if ($c != '!NONE!') $contact[$con] .= $c.'; <br />';
            }
        
            if ($contact[$con] != '') $contact[$con] = substr($contact[$con],0,strlen($contact[$con])-8).'.';
        }
        
        $datacons = $xpc_constime->xpath_eval('/document/person[fullname = "'.shiconv('koi8-r','utf-8',$fullname).'"]');
        $datacons = $datacons->nodeset;
    
        if (sizeof($datacons) > 0) {
            $time = $datacons[0]->get_elements_by_tagname('time');
            $timetext = '';
            if (sizeof($time) > 0) for ($i=0;$i<sizeof($time);$i++) {
                $a = $time[$i]->child_nodes();
                if ($a[0]) { $timetext .= $a[0]->node_value().'<br/>'; }
            }
            $timetext = shiconv('utf-8','koi8-r',$timetext);
        } else {
            $timetext = $time;
        }
    
        ?><tr><?php 
        ?><td bgcolor='white' class='stuff-num'><center><?php echo $n; ?>.</center></td><?php
        ?><td bgcolor='white' class='stuff-fio'><a href='<?php echo $link; ?>'><?php echo $fullname; ?></a></td><?php
        ?><td bgcolor='white' class='stuff-time'><?php if ($grade!='!NONE!') {echo $grade; echo ', '; echo strtoupper($position);}else{echo $position;} ?></td><?php
        ?><td bgcolor='white' class='stuff-contact'>&nbsp;<?php
        foreach (array(email, phone/*, room*/) as $con) if ($contact[$con] != '') {
            echo '<b>'.$titles[$con].'</b>'.$contact[$con].'<br/>';
        }
        ?></td><?php
        ?></tr><?php
        $n++;
    }
    ?></tbody></table><?php
}
?>