<?php require_once('_const_fonc.php'); 
//$codeuser=deconnecte_ou_connecte();
$codeindividu='01088';
$numsejour='01';
$db_orig = '...20160405';
$db_dest = $database_labo;
$hostname = "localhost";
$username = $username_labo;
$password = $password_labo; /* */
$conn = mysql_connect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR); 
mysql_select_db($db_orig, $conn);

$rs=mysql_query("SELECT * from individu where codeindividu=".GetSQLValueString($codeindividu, "text"));
$tab_individu_origine['individu'][1]=mysql_fetch_assoc($rs);
$rs=mysql_query("SELECT * from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
$tab_individu_origine['individusejour'][1]=mysql_fetch_assoc($rs);
$datedeb_sejour=$tab_individu_origine['individusejour']['datedeb_sejour'];
$datefin_sejour=$tab_individu_origine['individusejour']['datefin_sejour'];
$rs=mysql_query("SELECT * from individuemploi where codeindividu=".GetSQLValueString($codeindividu, "text").
								" and ".intersectionperiodes(GetSQLValueString($datedeb_sejour, "text"),GetSQLValueString($datefin_sejour, "text"),'datedeb_emploi','datefin_emploi')) or die(mysql_error());
$i=0;
while($row_rs=mysql_fetch_assoc($rs))
{ $i++;
	$tab_individu_origine[$nomtable][$i]=$row_rs;
}
$rs=mysql_query("SELECT * from individued where codeindividu=".GetSQLValueString($codeindividu, "text"));
$tab_individu_origine['individued'][1]=mysql_fetch_assoc($rs);
foreach(array('individusujet','individutheme','individupostit' ,'individustatutvisa','individuthese','individuaction') as $nomtable)
{ $rs=mysql_query("SELECT * from ".$nomtable." where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
	$i=0;
	while($row_rs=mysql_fetch_assoc($rs))
	{ $i++;
		$tab_individu_origine[$nomtable][$i]=$row_rs;
	}
}
$rs=mysql_query("SELECT * from individupj where codeindividu=".GetSQLValueString($codeindividu, "text")." and numcatpj=".GetSQLValueString($numsejour, "text")) or die(mysql_error());


mysql_select_db($db_dest, $conn);
?>