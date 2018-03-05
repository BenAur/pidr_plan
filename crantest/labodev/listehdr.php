<?php include_once('_const_fonc.php');?>
<?php header('Content-type: text/plain');?>
<?php 						 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$droisacces_individuprog=false;
$query_rs="select *".
					" from individuprog,prog".
					" where individuprog.codeprog=prog.codeprog".
					" and prog.refprog='listehdr.php' and individuprog.codeindividu=".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
if($row_rs=mysql_fetch_assoc($rs))
{ $droisacces_individuprog=true;
}
if(!$droisacces_individuprog && !array_key_exists('srh',$tab_roleuser) && !array_key_exists('du',$tab_roleuser)  && !array_key_exists('admingestfin',$tab_roleuser))
{ echo "Acces restreint";
	exit;
}
$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");
echo "Nom"."\t"."Prenom"."\t"."Mail"."\t"."Dept"."\t"."ED"."\n";

$codeindividu="";
$query_rs_individu="select *,ed.libcourted_fr as libed from individu, ed WHERE individu.codeed=ed.codeed and individu.codeed<>'' order by nom,prenom";
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$nb_membres=mysql_num_rows($rs_individu);
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ $codeindividu=$row_rs_individu["codeindividu"];
	//$numsejour=$row_rs_individu["numsejour"];
	
  // theme(s) pour un codeuser
	$query_rs_individutheme= "SELECT distinct individutheme.codetheme,libcourt_fr".
														" FROM individutheme,structure".
														" WHERE individutheme.codetheme=structure.codestructure AND structure.esttheme='oui'".
														" AND individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
														//" AND individutheme.numsejour=".GetSQLValueString($numsejour, "text").
														" AND ".periodeencours('structure.date_deb','structure.date_fin') or die(mysql_error());
  $rs_individutheme=mysql_query($query_rs_individutheme);
	$listetheme="";//pas de theme par defaut
  while($row_rs_individutheme = mysql_fetch_assoc($rs_individutheme))
  { $listetheme.=$row_rs_individutheme['libcourt_fr'].", ";
  }
	$listetheme=rtrim($listetheme,', ');
	echo txt2type($row_rs_individu["nom"],$type_fichier)."\t".txt2type($row_rs_individu["prenom"],$type_fichier)."\t".txt2type($row_rs_individu["email"],$type_fichier)."\t".$listetheme."\t".txt2type($row_rs_individu["libed"],$type_fichier)."\n";	
}?>
<?php 
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_individutheme)) {mysql_free_result($rs_individutheme);}
?>




