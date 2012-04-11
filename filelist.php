<?php

error_reporting(0);
if ($_GET[errors]) error_reporting($_GET[errors]);

//function glob($mask) {
    // will appear in php5. In this case should be commented out
    // works on UNIX system ONLY.
//    $dir = trim(shell_exec('echo '.$mask));
//    $dir = explode(" ",$dir);
//    return $dir;
//}

function human($size) {
    for($si = 0; $size >= 1024; $size /= 1024, $si++);
    return round($size, 1).substr(' kMG', $si, 1);
}

function printfilelist($files,$mime) {
    if (sizeof($files) == 0) return;
    ?><table border='0' cellspacing='5' cellpadding='0' align='center'><tr><?php
    $colcount = 0;
    for ($i=0;$i<sizeof($files);$i++) {
        ?><td><center><?php
        if ($mime == 'html' || $mime == pdf || $mime == write || $mime == calc) {
            ?><a style='text-decoration: none; color: black;' target='_blank' href='<?php echo $files[$i]; ?>'><img src='images/<?php echo $mime; ?>.png' border='0' alt='<?php echo $mime; ?>'/><br /><?php echo basename($files[$i]); ?><br /><?php echo human(filesize($files[$i])); ?></a><?php
        } else {
            ?><a style='text-decoration: none; color: black;' href='download.php?file=<?php echo $files[$i]; ?>'><img src='images/<?php echo $mime; ?>.png' border='0' alt='<?php echo $mime; ?>'/><br /><?php echo basename($files[$i]); ?><br /><?php echo human(filesize($files[$i])); ?></a><?php
        }
        ?></center></td><?php
        $colcount++;
        if ($colcount == 4) { $colcount = 0; ?></tr><tr><?php }
    }
    ?></tr></table><?php
}

chdir('data');
if (is_dir($_GET[section])) {
    chdir($_GET[section]);
    $dir = glob('*');
    foreach ($dir as $dirname) if (is_dir($dirname)) {
        chdir($dirname); 
        $data[$dirname][description] = implode(' ',file('.description.html'));
        $i = 0; foreach (glob('*.htm*') as $filename) if ($filename != '.description.html' && is_file($filename)) {
            $data[$dirname][html][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        $i = 0; foreach (glob('*.pdf') as $filename) if (is_file($filename)) {
            $data[$dirname][pdf][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        $i = 0; foreach (glob('*.zip') as $filename) if (is_file($filename)) {
            $data[$dirname][tar][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.rar') as $filename) if (is_file($filename)) {
            $data[$dirname][tar][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.tar.gz') as $filename) if (is_file($filename)) {
            $data[$dirname][tar][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        $i = 0;foreach (glob('*.doc*') as $filename) if (is_file($filename)) {
            $data[$dirname][write][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.txt') as $filename) if (is_file($filename)) {
            $data[$dirname][write][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.sxw') as $filename) if (is_file($filename)) {
            $data[$dirname][write][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.odt') as $filename) if (is_file($filename)) {
            $data[$dirname][write][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        $i = 0;foreach (glob('*.xls') as $filename) if (is_file($filename)) {
            $data[$dirname][calc][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.ppt') as $filename) if (is_file($filename)) {
            $data[$dirname][calc][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        foreach (glob('*.odp') as $filename) if (is_file($filename)) {
            $data[$dirname][calc][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        $i=0;foreach (glob('*.sxc') as $filename) if (is_file($filename)) {
            $data[$dirname][calc][$i] = 'data/'.$_GET[section].'/'.$dirname.'/'.$filename; $i++;
        }
        chdir('..');
    }
    echo implode(' ',file('.description.html'));
    chdir('../..');
    ?><table bgcolor='black' cellspacing='0' cellpadding='0' border='0' width='99%'><tr><td><?php
    ?><table cellspacing='1' cellpadding='3' width='100%' border='0'><?php
    ?><thead><?php
    ?><tr><?php
    ?><th bgcolor='white' width='5%'>&#8470</th><?php
    ?><th bgcolor='white' width='85%'>Наименование</th><?php
    ?><th bgcolor='white' width='10%'>Файлы</th><?php
    ?></tr><?php
    ?></thead><?php
    $n = 1; foreach ($data as $record) if (trim($record[description]) != '') {
        ?><tr><?php
        ?><td bgcolor='white'><center><?php echo $n; ?>.</center></td><?php
        ?><td bgcolor='white'><?php echo $record[description]; ?></td><?php
        ?><td bgcolor='white'><table align='center'><tr><?php
          ?><td><?php printfilelist($record[html],html); ?></td><?php
          ?><td><?php printfilelist($record[pdf],pdf); ?></td><?php
          ?><td><?php printfilelist($record[tar],tar); ?></td><?php
          ?><td><?php printfilelist($record[write],write); ?></td><?php
          ?><td><?php printfilelist($record[calc],calc); ?></td></tr></table><?php
        ?></td><?php
        ?></tr><?php
        $n++;
    }
    ?></table><?php
    ?></td></tr></table><?php
}
?>