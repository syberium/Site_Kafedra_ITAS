<?php
   session_start('ses');

/*
  $PHP_AUTH_USER=$_SERVER["PHP_AUTH_USER"];
  $PHP_AUTH_PW=$_SERVER["PHP_AUTH_PW"];;

  if(isset($PHP_AUTH_USER))
  {
    $user = "$PHP_AUTH_USER";
    $passwd = "$PHP_AUTH_PW";
  }
                                                                                              
  $unauth=0;
  $r=@mysql_pconnect("localhost",$user,$passwd) && @mysql_select_db("pppoe");

  if (!$r || $unauth)
  {
    @session_destroy('ses');
    Header("WWW-Authenticate: Basic realm=\"Billing\"");
    Header("HTTP/1.0 401 Unauthorized");
    die ("You may not connect to database without user name/password!");
  }
*/

require("vars.php");

mysql_connect($sql_host, $sql_user, $sql_pass);
$r=mysql_select_db($sql_base);
//echo"<meta http-equiv=\"Content-Type\" content=\"text/html\"; charset=koi8-r>";
        $r=mysql_query("select value from vars where name=\"admin\"");
        $adm_name=mysql_fetch_array($r); $adm_name=$adm_name[0];
        $r=mysql_query("select value from vars where name=\"password\"");
        $adm_pass=mysql_fetch_array($r); $adm_pass=$adm_pass[0];

$r=mysql_query("select value from vars where name=\"stat_db\"");
$stat_db=mysql_fetch_array($r);$stat_db=$stat_db[0];
$r=mysql_query("select value from vars where name=\"stat_table\"");
$stat_table=mysql_fetch_array($r);$stat_table=$stat_table[0];
$r=mysql_query("select value from vars where name=\"stat_col_ip\"");
$stat_col_ip=mysql_fetch_array($r);$stat_col_ip=$stat_col_ip[0];
$r=mysql_query("select value from vars where name=\"stat_col_time\"");
$stat_col_time=mysql_fetch_array($r);$stat_col_time=$stat_col_time[0];
$r=mysql_query("select value from vars where name=\"stat_col_traf\"");
$stat_col_traf=mysql_fetch_array($r);$stat_col_traf=$stat_col_traf[0];

//echo "ERROR ".mysql_errno()." ".mysql_error()."\n";

if ($HTTP_GET_VARS['action'] == "quit")
{
        session_start();
        session_destroy();
        Header("Location: index.php");
}

if ($HTTP_POST_VARS['action'] == "after_user_mod" && $_SESSION['user'] == 'yes')
{
        $user_item=$HTTP_POST_VARS['user_item'];
        $new_user_pass=$HTTP_POST_VARS['new_user_pass'];
        $new_user_pass_confirm=$HTTP_POST_VARS['new_user_pass_confirm'];
        $login=$HTTP_POST_VARS['login'];
      if ($new_user_pass == $new_user_pass_confirm)
      {
        mysql_query("update users set users.password='$new_user_pass' where users.id = $user_item");
        Header("Location: bill.php?login=$login");
      }
      else
      {
        echo "<html><body>";
        echo "<center>Пароль и подтверждение не совпадают, попробуйте снова.</center>";
        echo "<center><a href=\"bill.php?action=mod_user_pass&user_item=$user_item\">Изменить пользователя<br></a></center>";
        echo "</body></html>";
        exit();
      }
}

if ($HTTP_GET_VARS['action'] == "mod_user_pass" && $_SESSION['user'] == 'yes')
{
        echo "<html>";
        echo "<head><title>Изменить пароль пользователя</title></head>";
        echo "<form action=\"bill.php\" method=\"POST\">";
        $login=$HTTP_GET_VARS['login'];
        $user_item=$HTTP_GET_VARS['user_item'];
        $r=mysql_query("select users.user from users where users.id=$user_item");
        while($myrow=mysql_fetch_array($r))
        $login=$myrow[0];

        echo "<table border=0 align=center width=30%>";
        $r=mysql_query("select users.password from users where users.id=$user_item");
        while($myrow=mysql_fetch_array($r))
        echo "<tr><td>Пароль: </td><td><input type=password name=new_user_pass value='$myrow[0]'></td></tr>";

        $r=mysql_query("select users.password from users where users.id=$user_item");
        while($myrow=mysql_fetch_array($r))
        echo "<tr><td>Подтверждение: </td><td><input type=password name=new_user_pass_confirm value='$myrow[0]'></td></tr>";

        echo "<tr><input type=\"hidden\" name=\"action\" value=\"after_user_mod\"></tr>";
        echo "<input type=\"hidden\" name=\"user_item\" value=$user_item>";
        echo "<input type=\"hidden\" name=\"login\" value=$login>";
        echo "<tr><td><input type=submit value=Изменить></td></tr>";
        echo "<tr><td><a href=\"bill.php?login=$login\">Отмена</a></td></tr>";
        echo "</body></html>";
exit();
}

if (($HTTP_POST_VARS['login'] === $adm_name && $HTTP_POST_VARS['pswd'] === $adm_pass) || ($_SESSION['admin'] == 'yes'))
{                                                                  
//        echo session_id();
//        session_start();

        $_SESSION['admin'] = 'yes';
        mysql_select_db("pppoe");

        if ($HTTP_GET_VARS['action'] == "view")
        {   //просмотр статистики для админа
                echo "<html>";
                echo "<head><title>Статистика</title></head>";
                echo "<body>";
                echo "<center>";
                echo "<h1>Статистика:</h1>";
                echo "</center>";
                echo "<table border=1 width=100% bgcolor=cyan>";
                echo "<tr bgcolor=#00FF00><td><b>Пользователь</td><td><b>Описание</td><td><b>IP</td><td><b>На линии</td><td><b>Лимит (Мб)</td><td><b>Использовано (Мб)</td><td><b>Осталось (Мб)</td><td><b>Действие</td></b></tr>";

                $r = mysql_query("select * from users order by id");

                for ($i=0; $i<mysql_num_rows($r); $i++)
                {
                                echo"<tr>";
                                $f = mysql_fetch_array($r);
                          echo "<td>$f[user]</td><td>$f[desc]</td><td>$f[ip]</td><td>".($f[ison]?"да":"нет")."</td><td>".round($f["limit"]/1024/1024,2)."</td>";
                                $ip_temp=$f[ip];
                                mysql_select_db("$stat_db"); 
                                $tm=time();
                                $current_month=date('Y-m-d H:i:s',$tm);
                              $month_begin=date('Y-m-01 00:00:01',$tm);
                              $month_end=date('Y-m-31 23:59:59',$tm);
//                                  $month_begin=date('Y-10-01 00:00:01',$tm);
//                                  $month_end=date('Y-10-31 23:59:59',$tm);
//                                echo"$tm ;$current_month; $month_begin; $month_end";
//                                  echo"$stat_col_time";
//                                $r1=mysql_query("select sum($stat_col_traf) from traffic where $stat_col_ip='$f[ip]' and $stat_col_time>='$month_beg' and $stat_col_time<='$month_end'");
    $r1=mysql_query("select sum($stat_col_traf) from traffic where $stat_col_ip='$f[ip]' and $stat_col_time>='$month_begin'");
                                $ip_sum=mysql_fetch_array($r1); $ip_sum=$ip_sum[0];
                          echo "<td>".round($ip_sum/1024/1024,2)."</td>";
                              $ip_sum_all=$ip_sum_all+$ip_sum;
                                $last=$f[limit]-$ip_sum;
                          echo "<td>".round($last/1024/1024,2)."</td>";
                                mysql_select_db("pppoe");
                          echo "<td><a href=bill.php?action=mod&user_item=$f[id]>Изменить</a>&nbsp&nbsp&nbsp<a href=bill.php?action=delll&user_item=$f[id]>Удалить</a></td>";

                          echo"</tr>";
                }

           
                $r2=mysql_query("select sum(users.limit/1024/1024) from users");
                $sum_traf_given=mysql_fetch_array($r2); $sum_traf_given=$sum_traf_given[0];
                $ip_sum_all=round($ip_sum_all/1024/1024,2);
                $last_all=round($last_all/1024/1024,2);
                $last_all=$sum_traf_given-$ip_sum_all;
                echo "<td><b>Всего:</b></td><td>-</td><td>-</td><td>-</td><td><b>".round($sum_traf_given,2)."</b></td><td><b>$ip_sum_all</b></td><td><b>$last_all</b></td><td>-</td>";
           //     mysql_select_db("pppoe");


                echo "</table><br><br>";
                echo "<table border=1 width=30% align=center bgcolor=#C0C0C0>";
                echo "<tr><td><center><a href=\"bill.php?action=add\">Добавить пользователя...</a></center></td></tr>";
                echo "<tr><td><center><a href=\"bill.php?action=mod_admin\">Изменить имя и пароль администратора...</a></center></td></tr>";
                echo "<tr><td><center><a href=\"bill.php?action=quit\">Завершить работу</a></center></td></tr>";
                echo "</table>";
                echo "</body></html>";
        } //просмотр статистики для админа

        if ($HTTP_POST_VARS['action'] == "after_mod_admin")
        { 
                        $new_adm_login=$HTTP_POST_VARS['new_adm_login'];
                        $new_adm_pass=$HTTP_POST_VARS['new_adm_pass'];
                        $new_adm_pass_confirm=$HTTP_POST_VARS['new_adm_pass_confirm'];
                if ($new_adm_pass == $new_adm_pass_confirm)
                {
                        mysql_query("update vars set value='$new_adm_login' where name='admin'"); //!!!!
                        mysql_query("update vars set value='$new_adm_pass' where name='password'");
                        Header("Location: bill.php?action=view");
                }
                else
                {
                        echo "<center>Пароль и подтверждение не совпадают, попробуйте снова.</center>";
                        echo "<center><a href=\"bill.php?action=mod_admin\">Изменить имя и пароль администратора<br></a></center>";
                }
        }

        if ($HTTP_GET_VARS['action'] == "mod_admin")
        { //меняем пароль админа
                echo "<html>";
                echo "<head><title>Редактировать имя и пароль администратора</title></head>";
                echo "<body><form action=bill.php method=\"POST\">";
                $r=mysql_query("select value from vars where name=\"admin\"");
                $adm_name=mysql_fetch_array($r); $adm_name=$adm_name[0];
                $r=mysql_query("select value from vars where name=\"password\"");
                $adm_pass=mysql_fetch_array($r); $adm_pass=$adm_pass[0];

                echo "<table border=0 align=center width=30%>";
                echo "<tr><td>Имя: </td><td><input type=text name=\"new_adm_login\" value=$adm_name maxlen=30></td></tr>";
                echo "<tr><td>Пароль: </td><td><input type=password name=\"new_adm_pass\" value=$adm_pass maxlen=30></td></tr>";
                echo "<tr><td>Подтверждение: </td><td><input type=password name=\"new_adm_pass_confirm\" value=$adm_pass maxlen=30></td></tr>";

                echo "<input type=\"hidden\" name=\"action\" value=\"after_mod_admin\">";
                echo "<tr><td><input type=submit value=Изменить></td></tr>";
                echo "<tr><td><a href=\"bill.php?action=view\">Отмена</a></td></tr>";
                echo "</table>";
                echo "</body></html>";
        } //меняем пароль админа

        if ($HTTP_POST_VARS['action'] == "after_add")
        { //занесение пользователя в таблицу
                $new_user_login=$HTTP_POST_VARS['new_user_login'];
                $new_user_pass=$HTTP_POST_VARS['new_user_pass'];
                $new_user_pass_confirm=$HTTP_POST_VARS['new_user_pass_confirm'];
                $new_user_description=$HTTP_POST_VARS['new_user_description'];
                $new_user_limit=$HTTP_POST_VARS['new_user_limit'];
                $new_user_ip=$HTTP_POST_VARS['new_user_ip'];
        if ($new_user_pass == $new_user_pass_confirm)
                {
                $r3 = mysql_query("insert into users (`user`,`password`,`limit`,`desc`,`ip`) values ('$new_user_login','$new_user_pass',$new_user_limit*1024*1024,'$new_user_description','$new_user_ip')");
                Header("Location: bill.php?action=view");
                }
        else
                {
                echo "<center>Пароль и подтверждение не совпадают, попробуйте снова.</center>";
                echo "<center><a href=\"bill.php?action=add\">Добавить пользователя<br></a></center>";
                }
        } //занесение пользователя в таблицу

        if ($HTTP_GET_VARS['action'] == "add")
        { //добавить пользователя
                echo "<html>";
                echo "<head><title>Добавить пользователя</title></head>";
                echo "<body><form action=\"bill.php\" method=\"POST\">";
                echo "<table border=0 align=center width=50%>";
                echo "<tr><td>Имя: </td><td><input type=text name=\"new_user_login\" maxlen=30></td></tr>";
                echo "<tr><td>Пароль: </td><td><input type=password name=\"new_user_pass\" maxlen=30></td></tr>";
                echo "<tr><td>Подтверждение: </td><td><input type=password name=\"new_user_pass_confirm\" maxlen=30></td></tr>";
                echo "<tr><td>Описание: </td><td><input type=text name=\"new_user_description\" maxlen=200 size=50></td></tr>";
                echo "<tr><td>Лимит: </td><td><input type=text name=\"new_user_limit\"></td></tr>";
                echo "<tr><td>IP: </td><td><input type=text name=\"new_user_ip\" maxlen=15></td></tr>";
                echo "<input type=\"hidden\" name=\"action\" value=\"after_add\">";

                echo "<tr><td><input type=\"submit\" value=\"Добавить\"></td></tr>";
                echo "<tr><td><a href=\"bill.php?action=view\"><br>Отменить</a></td></tr>";
                echo "</table>";
                echo "</body></html>";
        } //добавить пользователя

        if ($HTTP_GET_VARS['action'] == "delll")
        { //Удалить пользователя
                $user_item=$HTTP_GET_VARS['user_item'];
                mysql_query("delete from users where id=$user_item");
                Header("Location: bill.php?action=view");        
        } //Удалить пользователя

        if ($HTTP_GET_VARS['action'] == "mod")
        { //Изменить пользователя
                echo "<html>";
                echo "<head><title>Редактировать пользователя</title></head>";
                echo "<body><form action=bill.php method=\"POST\">";
                $user_item=$HTTP_GET_VARS['user_item'];
                echo "<table border=0 align=center width=30%>";
                $r=mysql_query("select users.user from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>Имя: </td><td><input type=text name=new_user_login value='$myrow[0]'></td></tr>";

                $r=mysql_query("select users.password from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>Пароль: </td><td><input type=password name=new_user_pass value='$myrow[0]'></td></tr>";

                $r=mysql_query("select users.password from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>Подтверждение: </td><td><input type=password name=new_user_pass_confirm value='$myrow[0]'></td></tr>";

                $r=mysql_query("select users.desc from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>Описание: </td><td><input type=text size=50 name=new_user_description value='$myrow[0]'></td></tr>";

                $r=mysql_query("select round(users.limit/1024/1024,2) from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>Лимит: </td><td><input type=text name=new_user_limit value='$myrow[0]'></td></tr>";

                $r=mysql_query("select users.ip from users where users.id=$user_item");
                while($myrow=mysql_fetch_array($r))
                echo "<tr><td>IP: </td><td><input type=text name=new_user_ip value='$myrow[0]'></td></tr>";

                echo "<input type=\"hidden\" name=\"action\" value=\"after_mod\">";
                echo "<input type=\"hidden\" name=\"user_item\" value=$user_item>";
                echo "<tr><td><input type=submit value=Изменить></td></tr>";

                echo "<tr><td><a href=\"bill.php?action=view\">Отмена</a></td></tr>";
                echo "</table>";
                echo "</body></html>";
        } //Изменить пользователя

        if ($HTTP_POST_VARS['action'] == "after_mod")
        { //занесение пользователя в таблицу
                $user_item=$HTTP_POST_VARS['user_item'];
                $new_user_login=$HTTP_POST_VARS['new_user_login'];
                $new_user_pass=$HTTP_POST_VARS['new_user_pass'];
                $new_user_pass_confirm=$HTTP_POST_VARS['new_user_pass_confirm'];
                $new_user_description=$HTTP_POST_VARS['new_user_description'];
                $new_user_limit=$HTTP_POST_VARS['new_user_limit'];
                $new_user_ip=$HTTP_POST_VARS['new_user_ip'];

           if ($new_user_pass == $new_user_pass_confirm)
           {
                mysql_query("update users set users.user='$new_user_login' where users.id = $user_item"); //!!!!
                mysql_query("update users set users.password='$new_user_pass' where users.id = $user_item");
                mysql_query("update users set users.desc='$new_user_description' where users.id = $user_item");
                mysql_query("update users set users.limit=$new_user_limit*1024*1024 where users.id = $user_item");
                mysql_query("update users set users.ip='$new_user_ip' where users.id = $user_item");
                Header("Location: bill.php?action=view");
           }
           else
           {
                echo "<center>Пароль и подтверждение не совпадают, попробуйте снова.</center>";
                echo "<center><a href=\"bill.php?action=mod&user_item=$user_item\">Изменить пользователя<br></a></center>";
           }

        } //занесение пользователя в таблицу

}//end of admin part. beginning user part
else 
{
        $log=$HTTP_POST_VARS['login'];
        if ($_SESSION['user'] == 'yes') {$log=$HTTP_GET_VARS['login'];}
        $ps=$HTTP_POST_VARS['pswd'];
        $r2=mysql_query("select 1 from users where user='$log' and password='$ps'");
        if (mysql_num_rows($r2) || $_SESSION['user'] == 'yes')
                { //logon as user
                             echo "<html><head><title>Статистика пользователя</title></head><body>";
                             $_SESSION['user'] = 'yes';
                if (session_is_registered(log1)) {$log=$_SESSION['log1'];} else {$_SESSION['log1']=$log;}
                        $r = mysql_query("select * from users where user='$log'");
                        echo "<center><h1>Статистика:</h1></center>";
                        echo "<table border=1 width=100% bgcolor=cyan>";
                        echo "<tr><td><b>Пользователь</td><td><b>Описание</td><td><b>IP</td><td><b>На линии</td><td><b>Лимит (Мб)</td><td><b>Использовано (Мб)</td><td><b>Осталось (Мб)</td><td><b>Действие</td></b></tr>";
        
                        for ($i=0; $i<mysql_num_rows($r); $i++)
                        {
                                echo"<tr>";
                                $f = mysql_fetch_array($r);
                                echo "<td>$f[user]</td><td>$f[desc]</td><td>$f[ip]</td><td>".($f[ison]?"да":"нет")."</td><td>".round($f["limit"]/1024/1024,2)."</td>";
                                $ip_temp=$f[ip];
                                mysql_select_db("$stat_db"); 
                                $tm=time();
                                $current_month=date('Y-m-d H:i:s',$tm);
                                $month_begin=date('Y-m-01 00:00:00',$tm);
                                $month_end=date('Y-m-31 23:59:59',$tm);
//                                $r1=mysql_query("select sum($stat_col_traf) from traffic where $stat_col_ip='$f[ip]' and $stat_col_time>='$month_beg' and time<='$month_end'");
                                $r1=mysql_query("select sum($stat_col_traf) from traffic where $stat_col_ip='$f[ip]' and $stat_col_time>='$month_begin'");
                                $ip_sum=mysql_fetch_array($r1); $ip_sum=$ip_sum[0];
                            echo "<td>".round($ip_sum/1024/1024,2)."</td>";
                                $last=$f[limit]-$ip_sum;
                            echo "<td>".round($last/1024/1024,2)."</td>";
                                mysql_select_db("pppoe");
                            echo "<td><a href=bill.php?action=mod_user_pass&user_item=$f[id]&login=$log>Изменить пароль</a>";
                            echo"</tr>";
                        }
                    echo "</table><br><br>";
                    echo "<center><a href=\"bill.php?action=quit\">Завершить работу<br></a></center>";
                    echo "</body></html>";
                } //logon as user
        else
                {
                echo "<html><body><b>Неправильное имя и пароль.</b><br>";
                echo "Нажмите <a href=\"index.php\">сюда</a> для повтора.</body></html>";
                }
}
?>
