<?php require_once('../Connections/basa.php'); ?>
<?php

     mysql_select_db($database_basa, $basa);  
   
    $query = "SELECT * FROM anketa_result ORDER BY `familia`";
    $result = mysql_query($query) or die("Query failed : " . mysql_error());
	$line = mysql_fetch_array($result, MYSQL_ASSOC);
  
?>
 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
</head>
<body>
  <h1>Информация о выпускниках</h1>
    <h2>Наши выпускники </h2>	
  <?php
     print "<ul>\n";
	
	do
	{
	echo "<li><b>";
	echo $line['familia']; echo " ";
	echo $line['name']; echo " ";
	echo $line['otchestvo']; echo "</b>,  ";
	if ($line['uchen_stepen']!='' && (strcasecmp($line['uchen_stepen'], "нет"))!=0)
	   { echo $line['uchen_stepen']; 
	     echo ",  "; }
	if ($line['doljnost']=='') echo $line['dr_doljnost'];
	    else                   echo $line['doljnost'];
	echo " "; echo $line['nazvanie_organ']; echo ";";
	echo "</li>";
	}    while ($line = mysql_fetch_array($result, MYSQL_ASSOC));
    print "</ul>\n";


    mysql_free_result($result);

//   mysql_close($link);
?>
  <p>Уважаемые выпускники, просим Вас оставить информацию о себе, заполнив <a href="../anketa.php" target="_blank">анкету выпускника</a></p>
  <p>Если Вы обнаружили неточность ваших данных, просьба сообщить <a href="mailto:webadmin@itas.pstu.ru">администратору</a>        
</body>
</html>
