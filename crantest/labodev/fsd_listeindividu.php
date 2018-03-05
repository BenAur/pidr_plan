<?php include_once('_const_fonc.php');?>
<?php

$passwd=isset($_GET['passwd'])?$_GET['passwd']:(isset($_POST['passwd'])?$_POST['passwd']:"");
if($passwd!='passwd_fsd')
{ exit;
}
$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");
$car_debut_ligne_entete="";$car_debut_ligne_paire="";$car_debut_ligne_impaire="";$car_cellule="";
if($type_fichier=="html")
{ $car_debut_ligne_entete='<tr class="head"><td>';
	$car_debut_ligne_paire='<tr class="even"><td>';
	$car_debut_ligne_impaire='<tr class="odd"><td>';
	$car_fin_ligne="</td></tr>";
	$car_cellule="</td><td>";
	$html=true;
}
else
{ $car_fin_ligne="\n";
	$car_cellule="\t";
	$html=false;
}

if($type_fichier=="html")
{?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Listes des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<style>
table tr.head th, table tr.head {
	background-color: #D3DCE3;
	text-align: center;
}

/* odd table rows 1,3,5,7,... */
table tr.odd th,
table tr.odd {
    background-color: #E5E5E5;
    text-align: left;
}

/* even table rows 2,4,6,8,... */
table tr.even th,
table tr.even {
    background-color: #D5D5D5;
    text-align: left;
}

/* marked tbale rows */
table tr.marked th,
table tr.marked {
    background-color: #FFCC99;
}

/* hovered table rows */
table tr.odd:hover,
table tr.even:hover,
table tr.odd:hover th,
table tr.even:hover th,
table tr.hover th,
table tr.hover {
    background-color: #CCFFCC;
}
</style>
</head>
<body class="noircalibri10">
<table border="0" align="center" cellpadding="2" cellspacing="1">
<?php 
}
else
{ header('Content-type: text/plain');
}
//echo $car_debut_ligne_entete."codeindividu".$car_cellule."numsejour".$car_cellule."Nom".$car_cellule."Prenom".$car_fin_ligne;
$nb_membres=0;
$codeindividu="";
$query_rs_individu="select individu.codeindividu,nom,prenom,numsejour".
       		" from individu,individusejour".
					" where individu.codeindividu=individusejour.codeindividu".
					" and (".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
					"       or ".periodefuture('individusejour.datedeb_sejour').")".
       		" order by nom,prenom,individusejour.datedeb_sejour desc";
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
	echo $codeindividu.$car_cellule.$numsejour.$car_cellule.utf8_encode(txt2type($row_rs_individu["nom"],$type_fichier)).$car_cellule.utf8_encode(txt2type($row_rs_individu["prenom"],$type_fichier)).$car_fin_ligne;
}
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
?>




