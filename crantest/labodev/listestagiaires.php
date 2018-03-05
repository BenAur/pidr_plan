<?php include_once('_const_fonc.php');?>

<?php 						 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
/* if(!array_key_exists('srh',$tab_roleuser) && !array_key_exists('du',$tab_roleuser) && !array_key_exists('admingestfin',$tab_roleuser))
{ echo "Acces restreint";
	exit;
} */
$car_debut_ligne_entete='';
$car_debut_ligne_paire='';
$car_debut_ligne_impaire='';
$car_fin_ligne="";
$car_cellule="";

$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");
$car_debut_ligne_entete="";$car_debut_ligne_paire="";$car_fin_ligne_impaire="";$car_cellule="";
if($type_fichier=="html")
{ $car_debut_ligne_entete='<tr class="head"><td>';
	$car_debut_ligne_paire='<tr class="even"><td>';
	$car_debut_ligne_impaire='<tr class="odd"><td>';
	$car_fin_ligne="</td></tr>";
	$car_cellule="</td><td>";
}
else
{ $car_fin_ligne="\n";
	$car_cellule="\t";
}

if($type_fichier=="html")
{?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Listes des stagiaires <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css" type="text/css">
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css">
</head>
<body>
<table border="0" align="center" cellpadding="2" cellspacing="1">
<?php 
}
else
{ header('Content-type: text/plain');
}
echo 	$car_debut_ligne_entete."numdossier".$car_cellule."civilite".$car_cellule."Nom".$car_cellule."Prenom".
			$car_cellule."Diplome préparé".$car_cellule."GT + Resp. stage".$car_cellule."Date de début".
			$car_cellule."Date de Fin".$car_cellule."Durée mois".$car_cellule."Durée jours".$car_cellule."Montant gratification mensuelle".$car_cellule."Sujet".$car_fin_ligne;
$nb_membres=0;
$codeindividu="";
$query_rs_individu= "select individu.*,civilite.libcourt_fr as libciv,individusejour.*,diplome.codediplome,diplome.libcourtdiplome_fr as libdiplome,autrediplome_prep as autrediplome,".
										" datedeb_sejour,datefin_sejour,referent.nom as nomreferent,referent.prenom as prenomreferent,sujet.titre_fr as sujet".
										" from individu,civilite,diplome,individu as referent,corps,cat,individusejour".
										" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
										" left join sujet on individusujet.codesujet=sujet.codesujet".
										" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
										" and individu.codeciv=civilite.codeciv".
										" and individusejour.codediplome_prep=diplome.codediplome".
										" and individusejour.codereferent=referent.codeindividu".
										" and individusejour.codecorps=corps.codecorps".
										" and corps.codecat=cat.codecat".
										" and libcat_fr='STAGIAIRE'".
										//" and substring(datedeb_sejour,1,4)=".GetSQLValueString(date("Y"), "text").
										" order by nom,prenom desc";
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$pair=true;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ if($codeindividu!=$row_rs_individu["codeindividu"])
  { $nb_membres++;
	}
	$codeindividu=$row_rs_individu["codeindividu"];
	$numsejour=$row_rs_individu["numsejour"];
	if($pair)
	{ echo $car_debut_ligne_impaire;
	}
	else
	{ echo $car_debut_ligne_paire;
	}
	$pair=!$pair;
	echo $codeindividu.".".$numsejour.$car_cellule.$row_rs_individu["libciv"].$car_cellule.txt2type($row_rs_individu["nom"],$type_fichier).$car_cellule.txt2type($row_rs_individu["prenom"],$type_fichier).
				$car_cellule.($row_rs_individu["codediplome"]==""?txt2type($row_rs_individu["autrediplome"],$type_fichier):txt2type($row_rs_individu["libdiplome"],$type_fichier));
	// themes
	$query_rs_theme="select structure.libcourt_fr as libtheme from individutheme,structure".
									" where individutheme.codetheme=structure.codestructure and individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and individutheme.numsejour=".GetSQLValueString($numsejour, "text");
	$rs_theme=mysql_query($query_rs_theme) or die(mysql_error());
	$listetheme="";
	while($row_rs_theme=mysql_fetch_assoc($rs_theme))
	{ $listetheme.=$row_rs_theme['libtheme'].",";
	}
	$listetheme=rtrim($listetheme,",");
	echo $car_cellule.$listetheme." - ".$row_rs_individu['nomreferent'].' '.substr($row_rs_individu['prenomreferent'],0,1).".";
	echo $car_cellule.aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/").$car_cellule.aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],"/");
	$dureejour="";$dureemois="";
	if($row_rs_individu['datefin_sejour']!="")
	{ $datedeb=mktime(0,0,0,substr($row_rs_individu['datedeb_sejour'],5,2),substr($row_rs_individu['datedeb_sejour'],8,2),substr($row_rs_individu['datedeb_sejour'],0,4));
		$datefin=mktime(0,0,0,substr($row_rs_individu['datefin_sejour'],5,2),substr($row_rs_individu['datefin_sejour'],8,2),substr($row_rs_individu['datefin_sejour'],0,4));
		$dureejour=round(($datefin-$datedeb+1)/(60*60*24));
		$dureemois=(substr($row_rs_individu['datefin_sejour'],0,4)-substr($row_rs_individu['datedeb_sejour'],0,4))*12+substr($row_rs_individu['datefin_sejour'],5,2)-substr($row_rs_individu['datedeb_sejour'],5,2)+1;
	}
	echo $car_cellule.$dureemois.$car_cellule.$dureejour;
	$montant_mensuel_brut="";
	$query_rs_emploi="select montant_mensuel_brut from individuemploi".
									" where individuemploi.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individu["datedeb_sejour"]."'","'".$row_rs_individu["datefin_sejour"]."'");
	$rs_emploi=mysql_query($query_rs_emploi) or die(mysql_error());
	if($row_rs_emploi=mysql_fetch_assoc($rs_emploi))
	{ $montant_mensuel_brut=$row_rs_emploi['montant_mensuel_brut'];
	}
	echo 	$car_cellule.$montant_mensuel_brut;
	echo 	$car_cellule.txt2type($row_rs_individu["sujet"],$type_fichier);
	echo $car_fin_ligne;
}
if($type_fichier=="html")
{?>
</table>
</body>
</html>
<?php 
}
if(isset($rs_theme)) {mysql_free_result($rs_theme);}
if(isset($rs_emploi)) {mysql_free_result($rs_emploi);}
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_dir)) {mysql_free_result($rs_dir);}
?>




