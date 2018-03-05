<?php require_once('_const_fonc.php'); ?>
<?php

if($_SERVER['REQUEST_METHOD']=='POST')
{ 
	$rs=mysql_query("select * from individu where codeindividu<>''");
	while($row_rs = mysql_fetch_assoc($rs))
	{ if(in_array($row_rs['login'],array('bilon','chakir','colson','huraux','parant','kondratow','lejars','maquin','rumeau','wolf','pierson','gend','sassi')))
		{ $query_rs="update individu set passwd=".GetSQLValueString(crypt($row_rs['passwd'], 'rl'), "text").
							" where codeindividu=".GetSQLValueString($row_rs['codeindividu'], 'text');
			echo $query_rs.'<br>';
			mysql_query($query_rs);
		}
	}
}
  ?>
  <html>
  <style type="text/css">
  .rouge {
	color: #F00;
}
  </style>
  <body>
  <form name="crypt" action="crypt.php" method="post" target="_self">
  <input type="submit" name"submit"> 
  <span class="rouge">Attention crypter les mots de passe</span>
  </form>
<script language="JavaScript" type="text/javascript">
		document.getElementById("login").focus();
</script>

</body>
</html>





