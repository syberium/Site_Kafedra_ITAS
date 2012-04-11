<?php

$testfile = '/tmp/itasphp.'.$_SERVER['REMOTE_ADDR'].'.test';
$f = fopen($testfile,'w');fwrite($f,'-');fclose($f);

// DOS protection. dissalowing IP from entering page twice a time of execution
// IP must wait for a second if another execution is not finished

$trustedips = array(
    '127.0.0.1',
    '195.19.161.4',
    '77.43.206.87',
    '192.168.0.234'
    );

if (!array_search($_SERVER['REMOTE_ADDR'],$trustedips)) {
    $lockfile = '/tmp/itasphp.'.$_SERVER['REMOTE_ADDR'].'.lock';
    if (file_exists($lockfile) && time()-filectime($lockfile) < 1) {
        sleep(1);
        if (file_exists($lockfile) && time()-filectime($lockfile) < 1) { 
            echo "<pre><b>You are trying to download files too fast</b>.\n\n";
            echo "If you have got this message, it means that:\n";
            echo " - You have clicked on two or more links too fast for the server to operate;\n\n";
            echo " - You are behind the proxy server which is unknown for our software.\n   In this case, mail IP of your proxy to the web masters \n   (dk@perm.ru, kirill@rca.perm.ru) or check your network connection.\n\n";
            echo " - You are trying to perform DOS attack to the web page;\n\n";
            echo " - There was a server error.\n";
            echo "\n";
            echo "<b>Now you can press 'Reload' button to get the page</b>.\n\n";
            echo "<b>Нажмите 'Обновить' для загрузки страницы</b>.\n\n";
            echo 'Process terminated.'; 
            die;  
        }
    }

    if (file_exists($lockfile)) { unlink($lockfile); }
    $f = fopen($lockfile,'w');fwrite($f,'-');fclose($f);
}
// end of DOS protection

// debug option errors=yes
if (isset($_GET['errors'])) {
} else { error_reporting(0); }
// end of debug

include 'functions.php';
include 'lang.php';

if ($_GET[mode] == 'makeindex') {
    
    if (!checkip()) {
	echo "<h1>Access denied!</h1>";
	echo $_SERVER[REMOTE_ADDR];
	die;
    }	

    makeindex('rus');
    makeindex('eng');
    $xml->dump_file('/tmp/indexed.xml');
    echo 'index created';
    die;
}

$lang = lixgetlang();

$mode = $_GET[mode];
$query = $_GET[query];

if ($_GET['print'] == 'true') { print_print_page($lang,$mode,$query); }
else {
    if ($mode == '') {
        #    print_title_page($lang);
	/*if ($lang == 'rus')
	  print_normal_page('rus','anews','');
	else*/
          print_normal_page($lang,'dep/news','');
    } else {
        print_normal_page($lang,$mode,$query); 
    }
}

if (file_exists($lockfile)) { unlink($lockfile); }
if (file_exists($testfile)) { unlink($testfile); }

?>
