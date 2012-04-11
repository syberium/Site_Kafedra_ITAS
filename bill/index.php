<?php
      if(isset($HTTP_SERVER_VARS["HTTPS"]))
      {
              if(strcmp($HTTP_SERVER_VARS["HTTPS"],"on"))
              Header("Location: https://itas.pstu.ru/bill/index.php");
      }
      else Header("Location: https://itas.pstu.ru/bill/index.php");
?>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=KOI8-R">
  <title>Авторизация</title>
 </head>
<body>

<form action="bill.php?action=view" method="POST">
<br><br><br><br>
<center>
<table border=0 width=20%> 
<tr><td>Логин: </td><td><input type="text" name="login"></td></tr>
<tr><td>Пароль: </td><td><input type="password" name="pswd"></td></tr>
<tr><td><input type="submit" name="go" value="Вход"></td></tr>
</table>
</center>

</body></html>
