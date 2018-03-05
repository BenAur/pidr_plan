<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
if(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd'])
{ $rs=mysql_query("update `individusejour` set date_autorisation='2014/06/16' where datedeb_sejour<='2014/06/16'");
	$rs =	mysql_query("SELECT codeindividu, numsejour, datedeb_sejour".
										" from individusejour".
										" where (codeindividu,numsejour) not in (select codeindividu,numsejour from individustatutvisa where  codestatutvisa='02')".
										" and datedeb_sejour<='2014/06/16'");
	while($row_rs=mysql_fetch_assoc($rs))
	{ $updateSQL="insert into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) ".
																		" values (".GetSQLValueString($row_rs['codeindividu'], "text").",".GetSQLValueString($row_rs['numsejour'], "text").",".
																		"'02','00116','2014/06/16')";
		mysql_query($updateSQL);
	}
}
?>
