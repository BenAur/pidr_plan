<?php include_once('_const_fonc.php');
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if(!array_key_exists('sii',$tab_roleuser))
{ echo "Acces restreint";
	exit;
}
?>
<html>
<head>
<title>ldiff</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<?php 
$query_rs="select distinct login, passwd from individu,individusejour ".
					" where individu.codeindividu=individusejour.codeindividu".
					" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
					" and individu.codeindividu<>'' ".
					" order by login";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ 
  print ("dn: cn=".$row_rs['login'].", dc=info-sys, dc=fr<BR>".
         "userPassword: ".$row_rs['passwd']."<BR>".
				 "objectClass: top<br>".
				 "objectClass: person<br>".
				 "sn: ".$row_rs['login']."<br>".
				 "cn: ".$row_rs['login']."<br><br>" );
}
?>
</body>
</html>
