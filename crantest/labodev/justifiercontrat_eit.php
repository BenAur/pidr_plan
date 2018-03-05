<?php require_once('_const_fonc.php'); ?>
<?php
$labo = mysql_connect($hostname_labo, $username_labo, $password_labo) or trigger_error(mysql_error(),E_USER_ERROR); 

$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if(!estrole('sif',$tab_roleuser) && !estrole('du',$tab_roleuser))
{?>
Acc&egrave;s restreint
<?php exit;
}
$tab_infouser=get_info_user($codeuser);
{ /*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}  */
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$form_justfiercontrat = "justfiercontrat";
$tab_champs_date=array( 'datedeb' =>  array("lib" => "Date de d&eacute;but de contrat","jj" => "","mm" => "","aaaa" => ""));
$codecontrat='00397';
//$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$car_debut_ligne_entete='';
$car_debut_ligne_paire='';
$car_debut_ligne_impaire='';
$car_fin_ligne="";
$car_cellule="";

$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");
$car_debut_ligne_entete="";$car_debut_ligne_paire="";$car_fin_ligne_impaire="";$car_cellule="";

$tab_cmdjustifie=array();
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
<title>Justification contrats</title>
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
echo 	$car_debut_ligne_entete."date commande".$car_cellule."nature".$car_cellule."objet".$car_cellule."fournisseur".$car_cellule."montant payé".$car_cellule."montant engagé".$car_fin_ligne;
$query_rs="select commande.codecommande,commande.libfournisseur,commande.datecommande,cmd_nature.libcourt as libnature,objet,libfournisseur,montantengage,montantpaye".
					" from commandeimputationbudget,commande, cmd_nature".
					" where commande.codecommande=commandeimputationbudget.codecommande and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecontrat=".GetSQLValueString($codecontrat, "text").
					" and commande.codenature=cmd_nature.codenature".
					" and commande.codecommande<>''";
$rs=mysql_query($query_rs);
$pair=true;
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmdjustifie[]=$row_rs;	
}
mysql_close($labo);
$database_labo = $database_labo."2013";
$labo = mysql_connect($hostname_labo, $username_labo, $password_labo) or trigger_error(mysql_error(),E_USER_ERROR);
mysql_select_db($database_labo,$labo);
$query_rs="select commande.codecommande,commande.datecommande,cmd_nature.libcourt as libnature,objet,libfournisseur,montantengage,montantpaye".
					" from commandeimputationbudget,commande, cmd_nature".
					" where commande.codecommande=commandeimputationbudget.codecommande and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecontrat=".GetSQLValueString($codecontrat, "text").
					" and commande.codenature=cmd_nature.codenature".
					" and commande.codecommande<>''";

$rs=mysql_query($query_rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmdjustifie[]=$row_rs;	
}
$pair=true;
foreach($tab_cmdjustifie as $key=>$row_rs)
{ if($pair)
	{ echo $car_debut_ligne_impaire;
	}
	else
	{ echo $car_debut_ligne_paire;
	}
	$pair=!$pair;
	echo 	$car_debut_ligne_entete;	
	echo aaaammjj2jjmmaaaa($row_rs["datecommande"],'/').$car_cellule.$row_rs["libnature"].$car_cellule.$row_rs["objet"].$car_cellule.$row_rs["libfournisseur"].
				$car_cellule.' '.str_replace('.',',',$row_rs["montantpaye"]).$car_cellule.' '.str_replace('.',',',$row_rs["montantengage"]);
	echo $car_fin_ligne;
}
			
if($type_fichier=="html")
{?>
</table>
</body>
</html>
<?php 
}
if(isset($rs))mysql_free_result($rs);
?>
