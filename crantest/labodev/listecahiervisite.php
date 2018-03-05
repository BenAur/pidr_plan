<?php include_once('_const_fonc.php');?>
<?php 						 
$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");
$car_debut_ligne_entete="";$car_debut_ligne_paire="";$car_debut_ligne_impaire="";$car_cellule="";
if($type_fichier=="html")
{ $car_debut_ligne_entete='<tr class="head"><td nowrap>';
	$car_debut_ligne_paire='<tr class="even"><td nowrap>';
	$car_debut_ligne_impaire='<tr class="odd"><td nowrap>';
	$car_fin_ligne="</td></tr>";
	$car_cellule="</td><td nowrap>";
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
echo $car_debut_ligne_entete."num interne".$car_cellule."debut".$car_cellule."fin".$car_cellule."Nom Prenom".$car_cellule."pourquoi_pas_de_demande_fsd".$car_cellule."Categorie".$car_cellule.
			"Lieu".$car_cellule."date naiss.".$car_cellule."ville naiss.".$car_cellule."pays naiss.".$car_cellule."nationalite".$car_cellule.
			"objet".$car_cellule."etab orig".$car_cellule."adresse etab orig".$car_cellule."ville etab orig".$car_cellule."pays etab orig".$car_cellule."accueillant".$car_fin_ligne;
$query_rs="select codeindividu, nom, prenom from individu";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_referent=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_referent[$row_rs["codeindividu"]]=$row_rs;
}
// tous les visas de tous les individus,sejours
$query_rs="select codeindividu,numsejour,coderole".
					" from individustatutvisa,statutvisa".
					" where  individustatutvisa.codestatutvisa=statutvisa.codestatutvisa order by codeindividu,numsejour";
$rs= mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_tout_individustatutvisa[$row_rs['codeindividu']][$row_rs['numsejour']][$row_rs['coderole']]=$row_rs['coderole'];
}
// liste ordonnee des individus,sejours dans le temps avec dates de sejour, date autorisation de la bd
$query_rs="select codeindividu, numsejour, datedeb_sejour, datefin_sejour, date_autorisation from individusejour order by codeindividu,datedeb_sejour";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_dates_sejour[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
}

$codeindividu="";
$query_rs_individu="select individu.*,individusejour.*, liblongcorps_fr, libcat_fr,libcourtlieu,".
										" pays_naiss.libpayszrr as libpays_naiss,pays_naiss.libnat, pays_etab_orig.libpayszrr as libpays_etab_orig, codelibcat ".
										" from individu,corps,cat,lieu,individusejour,pays as pays_naiss,pays as pays_etab_orig".
										" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
										" and individu.codelieu=lieu.codelieu ".
										" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
										" and individu.codepays_naiss=pays_naiss.codepays".
										" and individusejour.codepays_etab_orig=pays_etab_orig.codepays".
										//" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
										" order by individusejour.datedeb_sejour desc";
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$pair=true;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ $codeindividu=$row_rs_individu["codeindividu"];
	$numsejour=$row_rs_individu["numsejour"];
	$demander_autorisation=false;
	$pourquoi_pas_de_demande_fsd='';
	if(!isset($tab_tous_individustatutvisa[$row_rs_individu["codeindividu"]][$row_rs_individu["numsejour"]]['srhue']) && $row_rs_individu["date_autorisation"]=='')
	{	$tab_demander_autorisation=demander_autorisation($row_rs_individu,$tab_dates_sejour[$codeindividu]);
		$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
		$pourquoi_pas_de_demande_fsd=$tab_demander_autorisation['pourquoi_pas_de_demande_fsd'];
		if($row_rs_individu['datefin_sejour']!='')
		{ //$tab_duree_sejour=duree_aaaammjj($row_rs_individu['datedeb_sejour'], $row_rs_individu['datefin_sejour']);
			if(!$demander_autorisation)
			{ if($pair)
				{ echo $car_debut_ligne_impaire;
				}
				else
				{ echo $car_debut_ligne_paire;
				}
				$pair=!$pair;
				echo $codeindividu.'.'.$numsejour.$car_cellule.
				txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],'-'),$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour"],'-'),$type_fichier).$car_cellule.
				txt2type($row_rs_individu["nom"],$type_fichier)." ".txt2type($row_rs_individu["prenom"],$type_fichier).$car_cellule.
				txt2type($pourquoi_pas_de_demande_fsd,$type_fichier).$car_cellule.
				txt2type($row_rs_individu["liblongcorps_fr"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["libcourtlieu"],$type_fichier).$car_cellule.
				txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_naiss"],'-'),$type_fichier).$car_cellule.txt2type($row_rs_individu["ville_naiss"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["libpays_naiss"],$type_fichier).$car_cellule.txt2type($row_rs_individu["libnat"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["intituleposte"],$type_fichier).$car_cellule.txt2type($row_rs_individu["etab_orig"],$type_fichier).$car_cellule.txt2type($row_rs_individu["adresse_etab_orig"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["ville_etab_orig"],$type_fichier).$car_cellule.txt2type($row_rs_individu["libpays_etab_orig"],$type_fichier).$car_cellule.
				txt2type($tab_referent[$row_rs_individu["codereferent"]]["prenom"],$type_fichier)." ".txt2type($tab_referent[$row_rs_individu["codereferent"]]["nom"],$type_fichier).$car_fin_ligne;
			}
		}
	}
}
if(isset($rs)) {mysql_free_result($rs);}
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_individutheme)) {mysql_free_result($rs_individutheme);}
?>




