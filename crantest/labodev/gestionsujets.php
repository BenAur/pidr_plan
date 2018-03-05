<?php require_once('_const_fonc.php'); ?>
<?php
// avant de mettre liste etudiants dans colonne affecter a et limiter a sujets possibles pour etudiant sans sujet : Mes documents20120708.zip
$erreur_envoimail="";
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$tab_contexte=array('prog'=>'gestionsujets','codeuser'=>$codeuser);
$est_admin=array_key_exists('du',$tab_roleuser)|| $admin_bd || droit_acces($tab_contexte);
/*if($admin_bd)
{ foreach($_POST as $key=>$val)
	{ echo $key."=".$val."<br>";
	}
}*/
$affiche_succes=false;
$message_resultat_affiche="";

$codesujet=isset($_GET['codesujet'])?$_GET['codesujet']:(isset($_POST['codesujet'])?$_POST['codesujet']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$sujet_ancre=isset($_GET['sujet_ancre'])?$_GET['sujet_ancre']:(isset($_POST['sujet_ancre'])?$_POST['sujet_ancre']:"");
if(!isset($_SESSION['b_voir_archives_sujets'])){ $_SESSION['b_voir_archives_sujets']=false;}
if(!isset($_SESSION['b_voir_tous_themes'])){ $_SESSION['b_voir_tous_themes']=false;}
if(!isset($_SESSION['b_voir_mes_sujets'])){ $_SESSION['b_voir_mes_sujets']=true;}
$_SESSION['b_voir_archives_sujets']=isset($_POST['b_voir_archives_sujets_x'])? !$_SESSION['b_voir_archives_sujets']:$_SESSION['b_voir_archives_sujets'];
$_SESSION['b_voir_tous_themes']=isset($_POST['b_voir_tous_themes_x'])? !$_SESSION['b_voir_tous_themes']:$_SESSION['b_voir_tous_themes'];
$_SESSION['b_voir_mes_sujets']=isset($_POST['b_voir_mes_sujets_x'])? !$_SESSION['b_voir_mes_sujets']:$_SESSION['b_voir_mes_sujets'];
if(!isset($_SESSION['sujet_texterecherche'])){ $_SESSION['sujet_texterecherche']='';}
$_SESSION['sujet_texterecherche']=isset($_POST['sujet_texterecherche'])?$_POST['sujet_texterecherche']:"";

if(isset($_POST['b_corbeille_x']))
{ $_SESSION['sujet_texterecherche']='';
}
if($_SESSION['sujet_texterecherche']!='')
{ $_SESSION['b_voir_archives_sujets']=true;
	$_SESSION['b_voir_tous_themes']=true;
	$_SESSION['b_voir_mes_sujets']=false;
}

$codetypesujet="";
$estresptheme=false;
$tab_sujetetudiant=array();
$tab_etudiant_sans_sujet=array();
$etudiant_stagiaire_sans_sujet=false;
$etudiant_doctorant_sans_sujet=false;
$etudiant_postdoc_sans_sujet=false;
$etudiant_collaboration_sans_sujet=false;//invite, visiteur, exterieur = categorie EXTERIEUR
$etudiant_sans_sujet=false;
$redirectProg="";
// le sujet est a affecter a un etudiant
$sujet_a_affecter=false;
$codeetudiantsejour=isset($_GET['codeetudiantsejour'])?$_GET['codeetudiantsejour']:isset($_POST['codeetudiantsejour'])?$_POST['codeetudiantsejour']:"";
$sujet_a_affecter=(isset($_GET['sujet_a_affecter'])?$_GET['sujet_a_affecter']:isset($_POST['sujet_a_affecter'])?$_POST['sujet_a_affecter']:"non")=="oui";
$codeetudiant='';
$numsejour='';
if($codeetudiantsejour!='')
{ $tab=explode('#',$codeetudiantsejour);
	if(count($tab)>=1)
	{ $codeetudiant=$tab[0];
		$numsejour=$tab[1];
	}
	//$sujet_a_affecter=true;
}


if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF']))//methode=post
{ $affiche_succes=true;
	$message_resultat_affiche="Validation effectu&eacute;e";
	if($action=="supprimer")
	{ mysql_query("delete from sujet where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	  mysql_query("delete from sujettheme where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	  mysql_query("delete from sujetdir where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
		mysql_query("delete from individusujet where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	}
	else if($action=="demander_validation" || $action=="valider" || $action=="affecter")
	{	if($action=="demander_validation" || $action=="valider" || $action=="affecter")
		{	if($GLOBALS['avecvisathemesujet'])
			{ // ---------------------------- modification du statut de la proposition si demande de validation et envoi de message aux acteurs concernés
				//l'individu connecte (login) est-il responsable de l'un des themes de la proposition de sujet?
				$rs_structureindividu = mysql_query("SELECT * from sujettheme,structure,structureindividu ".
																						" where codesujet=".GetSQLValueString($codesujet, "text").
																						" and sujettheme.codetheme=structureindividu.codestructure".
																						" and structureindividu.codeindividu=".GetSQLValueString($codeuser, "text").
																						" and structureindividu.estresp='oui'") or die(mysql_error());
				// Changement du statut de la proposition selon les cas
				/*										n'est pas resptheme 	est resptheme 
					submit_enregistrer    	''											'E'
					submit_valider					'E'											'V'
				
				*/
				if(mysql_fetch_assoc($rs_structureindividu)) 
				{ $codestatutsujet="V";
				}
				else
				{ $codestatutsujet="E";
				}
			}
			else//pas de visa theme a apposer
			{ $codestatutsujet="V";
			}
			$updateSQL="update sujet set codestatutsujet=".GetSQLValueString($codestatutsujet, "text")." where codesujet=".GetSQLValueString($codesujet, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
		if($sujet_a_affecter)
		{ mysql_query("delete from individusujet where codeindividu=".GetSQLValueString($codeetudiant, "text")." and numsejour=".GetSQLValueString($numsejour, "text")." and codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());			
			mysql_query("insert into individusujet (codeindividu,numsejour,codesujet) values(".GetSQLValueString($codeetudiant, "text").",".GetSQLValueString($numsejour, "text").",".GetSQLValueString($codesujet, "text").")") or die(mysql_error());			
		}
		// dernier visa (assimile a un role) appose au dossier etudiant
		$coderole_a_prevenir='';
		$row_rs_individustatutvisa=max_individustatutvisa($codeetudiant,$numsejour);
		if($row_rs_individustatutvisa['coderole']=='referent' || $row_rs_individustatutvisa['coderole']=='srhue')
		{ $coderole_a_prevenir='theme';
		}
		$erreur_envoimail=mail_validation_sujet($codesujet,$tab_infouser,$coderole_a_prevenir);
	}
	else if($action=="afficher_sujet_web")
	{ if(isset($_POST['b_afficher_sujet_web_oui_x']))
		{ $afficher_sujet_web='non';
		}
		else
		{ $afficher_sujet_web='oui';
		}
		mysql_query("update sujet set afficher_sujet_web=".GetSQLValueString($afficher_sujet_web, "text")." where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	} 
	else if($action=="afficher_sujet_propose")
	{ if(isset($_POST['b_afficher_sujet_propose_oui_x']))
		{ $afficher_sujet_propose='non';
		}
		else
		{ $afficher_sujet_propose='oui';
		}
		mysql_query("update sujet set afficher_sujet_propose=".GetSQLValueString($afficher_sujet_propose, "text")." where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	} 
	/* else if($action=="archiver")
	{	mysql_query("update sujet set codestatutsujet='A' where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
	}*/
 
}

// -------------------------- FORMULAIRE D'ENVOI DES DONNES
// encadrants des sujets
$query_rs_dir= "select sujetdir.*,nom,prenom from sujetdir,individu where sujetdir.codedir=individu.codeindividu";
$rs_dir = mysql_query($query_rs_dir) or die(mysql_error());
$tab_dir=array();
while($row_rs_dir =mysql_fetch_assoc($rs_dir))
{ $tab_dir[$row_rs_dir['codesujet']][$row_rs_dir['codedir']]=$row_rs_dir;
	//$tab_dir[$row_rs_dir['codesujet']]['sujet_texterecherche']=false;
	if($_SESSION['sujet_texterecherche']!='')
	{ if(strpos(strtolower($row_rs_dir['nom'].$row_rs_dir['prenom']),strtolower($_SESSION['sujet_texterecherche']))!==false)
		{  $tab_dir[$row_rs_dir['codesujet']]['sujet_texterecherche']=true;
		}
	}
}
// themes des sujets
$query_rs_sujettheme= "select sujettheme.codesujet,sujettheme.codetheme,libcourt_fr as libtheme from sujettheme,structure ".
											" where sujettheme.codetheme=structure.codestructure";
$rs_sujettheme = mysql_query($query_rs_sujettheme) or die(mysql_error());
$tab_sujetcodetheme=array();// tableau des codetheme du sujet
$tab_sujetlibtheme=array();

while($row_rs_sujettheme=mysql_fetch_assoc($rs_sujettheme))
{ $tab_sujetcodetheme[$row_rs_sujettheme['codesujet']][]=$row_rs_sujettheme['codetheme'];
	$tab_sujetlibtheme[$row_rs_sujettheme['codesujet']][]=$row_rs_sujettheme['libtheme'];
}

//l'individu connecte $codeuser est-il responsable de l'un des themes du sujet? 
$rs_structureindidividu=mysql_query("select * from sujettheme,structureindividu,structure " .
																		" where sujettheme.codetheme=structureindividu.codestructure ".
																		" and structure.codestructure=structureindividu.codestructure".
																		" and ".periodeencours('structure.date_deb','structure.date_fin').
																		" and structureindividu.codeindividu=".GetSQLValueString($codeuser,"text")." and estresp='oui'") or die(mysql_error());
$tab_estresptheme_sujet=array();
while($row_structureindidividu=mysql_fetch_assoc($rs_structureindidividu))
{ $tab_estresptheme_sujet[$row_structureindidividu['codesujet']]=true;
}
									

$query_rs_sujet = "SELECT distinct sujet.*,typesujet.libcourt_fr as libtypesujet,libstatutsujet, codelibtypestage".
									" FROM sujet,typesujet,statutsujet, typestage".
									" WHERE codesujet<>'' AND sujet.codestatutsujet<>'A'".
									" AND sujet.codetypesujet=typesujet.codetypesujet".
									" AND sujet.codestatutsujet=statutsujet.codestatutsujet".
									" AND sujet.codetypestage=typestage.codetypestage".
									($_SESSION['b_voir_archives_sujets']?"":" AND (".periodeencours('datedeb_sujet','datefin_sujet')." OR ".periodefuture('datedeb_sujet').")").
									" ORDER BY sujet.codetypesujet,sujet.codetypestage, datedeb_sujet desc";

$rs_sujet = mysql_query($query_rs_sujet) or die(mysql_error());

// type de stages necessitant un sujet pour au moins un stagiaire de ce type
// ce tableau, ainsi que le champ codettypestage de la table sujet, ne devrait plus etre utilise car seuls les stagiaire = BAC+5 sont encore concernes pas la declaration FSD : il est plus simple de le conserver pour 
// eviter des modifications trop importantes en developpement et ... on ne sait jamais, il pourrait servir a nouveau...
$tab_typestage_etudiant_sans_sujet=array();
// liste des etudiants presents ou futurs sans sujet pour ce referent
// STAGIAIRE dont le user est referent
$query_ind ="select civilite.libcourt_fr as libciv,individu.*,individusejour.*,".
						" corps.liblongcorps_fr as libcorps, cat.codelibcat,sujetstageobligatoire,codelibtypestage,gesttheme.nom as gestthemenom,gesttheme.prenom as gestthemeprenom".
						" from civilite,individu,individu as gesttheme, corps,cat,individusejour".
						" left join typestage on individusejour.codetypestage=typestage.codetypestage".
						" where individu.codeciv=civilite.codeciv".
						" and individu.codeindividu=individusejour.codeindividu".
						" and gesttheme.codeindividu=individusejour.codegesttheme".
						" and individusejour.codecorps=corps.codecorps". 
						" and corps.codecat=cat.codecat". 
						" and codelibcat='STAGIAIRE' and sujetstageobligatoire='oui'".
						" and (individusejour.codeindividu,individusejour.numsejour) not in (select codeindividu,numsejour from individusujet,sujet 
																																								 where individusujet.codesujet=sujet.codesujet
																																								 and codetypesujet='02')".
						" and (".periodeencours('datedeb_sejour','datefin_sejour')." OR ".periodefuture('datedeb_sejour').")".																																
						" and individusejour.codereferent=".GetSQLValueString($codeuser, "text").
						" order by individu.nom,individu.prenom";
$rs_ind = mysql_query($query_ind) or die(mysql_error());
while($row_rs_ind=mysql_fetch_assoc($rs_ind))
{ if($row_rs_ind['codelibtypestage']=='MASTER' /* || ($row_rs_ind['codelibtypestage']!='MASTER' && $row_rs_ind['datedeb_sejour']>$GLOBALS['date_zrr_obligatoire'] && $row_rs_ind['sujetstageobligatoire']=='oui' )*/)
	{ $tab_etudiant_sans_sujet['STAGIAIRE'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
		$tab_typestage_etudiant_sans_sujet[$row_rs_ind['codetypestage']]=$row_rs_ind['codetypestage'];
		$etudiant_stagiaire_sans_sujet=true;
		$etudiant_sans_sujet=true;
	}
}
// DOCTORANT dont le user est referent
$query_ind ="select civilite.libcourt_fr as libciv,individu.*,individusejour.*,".
						" corps.liblongcorps_fr as libcorps, cat.codelibcat,codelibtypestage,gesttheme.nom as gestthemenom,gesttheme.prenom as gestthemeprenom".
						" from civilite,individu,individu as gesttheme,corps,cat,individusejour".
						" left join typestage on individusejour.codetypestage=typestage.codetypestage".
						" where individu.codeciv=civilite.codeciv".
						" and individu.codeindividu=individusejour.codeindividu".
						" and gesttheme.codeindividu=individusejour.codegesttheme".
						" and individusejour.codecorps=corps.codecorps". 
						" and corps.codecat=cat.codecat". 
						" and codelibcat='DOCTORANT'".
						" and (individusejour.codeindividu,individusejour.numsejour) not in (select codeindividu,numsejour from individusujet,sujet 
																																								 where individusujet.codesujet=sujet.codesujet
																																								 and codetypesujet='03')".
						" and (".periodeencours('datedeb_sejour','datefin_sejour')." OR ".periodefuture('datedeb_sejour').")".																																
						" and individusejour.codereferent=".GetSQLValueString($codeuser, "text").
						" order by individu.nom,individu.prenom";
$rs_ind = mysql_query($query_ind) or die(mysql_error());
while($row_rs_ind=mysql_fetch_assoc($rs_ind))
{ $tab_etudiant_sans_sujet['DOCTORANT'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
	$etudiant_doctorant_sans_sujet=true;
	$etudiant_sans_sujet=true;
}

// EXTERIEUR : invite, visiteur, exterieur acceuilli
// contrairement a STAGIAIRE  et DOCTORANT, un EXTERIEUR ne necessite un sujet que s'il faut demander une autorisation pour fsd
// on a besoin de la liste ordonnee des sejours dans le temps avec dates de sejour, date autorisation de la bd 
$query_rs="select codeindividu, numsejour, datedeb_sejour, datedeb_sejour_prevu, datefin_sejour, datefin_sejour_prevu, date_autorisation from individusejour order by codeindividu,datedeb_sejour_prevu";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_dates_sejour[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
} 
$query_ind ="select civilite.libcourt_fr as libciv,individu.*,individusejour.*,".
						" corps.liblongcorps_fr as libcorps, cat.codelibcat,codelibtypestage,gesttheme.nom as gestthemenom,gesttheme.prenom as gestthemeprenom".
						" from civilite,individu,individu as gesttheme,corps,cat,individusejour".
						" left join typestage on individusejour.codetypestage=typestage.codetypestage".
						" where individu.codeciv=civilite.codeciv".
						" and individu.codeindividu=individusejour.codeindividu".
						" and gesttheme.codeindividu=individusejour.codegesttheme".
						" and individusejour.codecorps=corps.codecorps". 
						" and corps.codecat=cat.codecat". 
						" and codelibcat='EXTERIEUR'".
						" and (individusejour.codeindividu,individusejour.numsejour) not in (select codeindividu,numsejour from individusujet,sujet 
																																								 where individusujet.codesujet=sujet.codesujet
																																								 and codetypesujet='05')".
						" and (".periodeencours('datedeb_sejour','datefin_sejour')." OR ".periodefuture('datedeb_sejour').")".																																
						" and individusejour.codereferent=".GetSQLValueString($codeuser, "text").
						" order by individu.nom,individu.prenom";
$rs_ind = mysql_query($query_ind) or die(mysql_error());
while($row_rs_ind=mysql_fetch_assoc($rs_ind))
{ if($row_rs_ind['date_autorisation']=='')
	{ $tab_demander_autorisation=demander_autorisation($row_rs_ind,$tab_dates_sejour[$row_rs_ind['codeindividu']]);
		if($tab_demander_autorisation['demander_autorisation'])
		{ $tab_etudiant_sans_sujet['EXTERIEUR'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
			$etudiant_collaboration_sans_sujet=true;
			$etudiant_sans_sujet=true;
		}
	}
}

// liste des etudiants associes aux sujets
$query_ind ="SELECT individusujet.codesujet,individusujet.codeindividu,individusujet.numsejour,if(".periodepassee('datefin_sejour').",'oui','non') as estparti,".
						" individu.nom,individu.prenom,civilite.libcourt_fr as libciv,gesttheme.nom as gestthemenom,gesttheme.prenom as gestthemeprenom".
						" FROM individu,individu as gesttheme,civilite,individusujet,individusejour".
						" WHERE individu.codeindividu=individusujet.codeindividu ".
						" and individu.codeciv=civilite.codeciv".
						" and gesttheme.codeindividu=individusejour.codegesttheme".
						" and individusujet.codeindividu=individusejour.codeindividu and individusujet.numsejour=individusejour.numsejour";

$rs_ind = mysql_query($query_ind) or die(mysql_error());
while($row_rs_ind=mysql_fetch_assoc($rs_ind))
{ $tab_sujetetudiant[$row_rs_ind['codesujet']][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
	$tab_sujetetudiant[$row_rs_ind['codesujet']][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]['coderole']='';
	//dernier visa appose au dossier etudiant
	$row_rs_individustatutvisa=max_individustatutvisa($row_rs_ind['codeindividu'],$row_rs_ind['numsejour']);
	if($row_rs_individustatutvisa)
	{ $tab_sujetetudiant[$row_rs_ind['codesujet']][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]['coderole']=$row_rs_individustatutvisa['coderole'];
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des sujets <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
	
<SCRIPT language="javascript">
	var w;
	function OuvrirVisible(url,nom)
	{ w=window.open(url,nom,"scrollbars = yes,width=800,height=350,location=no,mebubar=no,status=no,directories=no");
		w.document.close();
		w.focus();
	}
	function Fermer() 
	{ if (w.document) { w.close(); }
	}

// Liste des codeetudiant, gesttheme pour message popup validation
var tab_etudiant_gesttheme=new Array();
<?php 
foreach($tab_etudiant_sans_sujet as $libtypeetudiant=>$tab_un_typeetudiant_sans_sujet)
{	foreach($tab_un_typeetudiant_sans_sujet as $codeetudiant=>$tab_un_etudiant_sans_sujet)
	{ foreach($tab_un_etudiant_sans_sujet as $numsejour=>$tab_un_etudiant_sejour_sans_sujet)
		{?> 
		tab_etudiant_gesttheme['<?php echo $codeetudiant ?>#<?php echo $numsejour ?>']="<?php echo js_tab_val($tab_un_etudiant_sejour_sans_sujet['gestthemeprenom']) ?> <?php echo js_tab_val($tab_un_etudiant_sejour_sans_sujet['gestthemenom']); ?>"
		<?php 
		}
	}
}

?>

function gesttheme(codeetudiantsejour)
{ return tab_etudiant_gesttheme[codeetudiantsejour];
}
</script>
</head>
<body onLoad="window.location.hash='<?php echo $sujet_ancre ?>'">
<table width="100%" >
	<?php echo entete_page(array('image'=>'images/b_document.png','titrepage'=>'Gestion des sujets','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'affiche_succes'=>$affiche_succes,'message_resultat_affiche'=>$message_resultat_affiche,
																'msg_erreur_objet_mail'=>'codesujet='.$codesujet,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
		<?php if($GLOBALS['bloque_saisie_these'])
    {?> <tr>
			<td><span class="rougecalibri10"><?php echo $GLOBALS['bloque_saisie_these_motif'] ?></span>
			</td>
			</tr>
    <?php 
		} ?>
		<tr>
			<td>
      	<table width="100%">
        	<tr>
          	<td align="left">
            	<table>
              	<tr>
                	<td><form name="form_voir_sujet_texterecherche" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
											<span class="bleugrascalibri10">Rechercher les sujets d'encadrant dont nompr&eacute;nom (pas d'espace entre nom et pr&eacute;nom) contient :&nbsp;</span>
                  <td><input type="text" class="noircalibri10" name="sujet_texterecherche" id="sujet_texterecherche" value="<?php echo htmlspecialchars($_SESSION['sujet_texterecherche']==''?'':$_SESSION['sujet_texterecherche'])  ?>" size="20">
                  </td>
                  <td>
                    <input type="image"  name="b_rechercher" img class="icon" src="images/b_rechercher.png" alt="Rechercher" title="Rechercher">
                  </td>
                  <td><input type="image" name="b_corbeille" src="images/b_corbeille.png" width="16" height="16" title="Vider la zone de recherche"
                      >
                      </form>
                  </td>
                </tr>
              </table>
            </td>
          	<td align="right">
              <form name="form_voir_tous_themes" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <input type="image"  name="b_voir_tous_themes" img class="icon" src="images/b_voir_tous_themes_<?php echo ($_SESSION['b_voir_tous_themes']?"oui":"non") ?>.png" alt="Afficher tous les sujets" title="Afficher tous les sujets" />
              </form>
              <?php if($admin_bd)
              {?> <form name="form_voir_archives_sujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <input type="image"  name="b_voir_archives_sujets" img class="icon" src="images/b_voir_archives_sujets_<?php echo ($_SESSION['b_voir_archives_sujets']?"oui":"non") ?>.png" alt="Afficher les archives" title="Afficher les archives" />
              </form>
              <?php 
							}?>
              <form name="form_voir_mes_sujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                <input type="image"  name="b_voir_mes_sujets" img class="icon" src="images/b_voir_mes_sujets_<?php echo ($_SESSION['b_voir_mes_sujets']?"oui":"non") ?>.png" alt="Afficher les archives" title="Afficher les archives" />
              </form>
        		</td>
          	<td><img src="images/espaceur.gif" width="100" height="1" border="0">
        		</td>
          </tr>
      	</table>
			</td>
		</tr>
		<tr>
			<td><img src="images/espaceur.gif" width="1" height="20">
			</td>
		</tr>
    <?php 
		if($etudiant_sans_sujet)
		{?> 
    <tr>
			<td>
      	<table cellspacing="4">
        	<tr>
          	<td valign="top">
              <img src="images/b_attente_validation_sujet.png">
        		</td>
          	<td>
              <span class="rougecalibri10">
              Vous devez affecter, en tant que r&eacute;f&eacute;rent, un sujet &agrave; (aux) l&rsquo;&eacute;tudiant mentionn&eacute; ci-dessous : la proc&eacute;dure d&rsquo;accueil est bloqu&eacute;e en attente de validation.
							<br>Vous pouvez cr&eacute;er un nouveau sujet et l&rsquo;affecter en validant ou valider l'un des sujets d&eacute;j&agrave; saisis et pour lesquels le nom de l&rsquo;&eacute;tudiant est propos&eacute; dans la colonne "Affecter &agrave;".</span>  
        		</td>
          </tr>
      	</table>
      </td>
		</tr>
		 <?php 
		}?>
		<tr>
			<td>
       <table border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td><span class="bleugrascalibri11">Nouveau sujet </span>&nbsp;</td>
			   	<td>
						<?php
						/*  Blocage par limitation si au moins un etudiant sans sujet dont le user est referent :
						    - pour chaque type d'etudiant necesitant un sujet, on affiche la liste select des etudiants concernes suivie du bouton de creation de sujet.
								- on procede de meme pour chaque ligne de sujet adapte : liste select des etudiants sans sujet de ce type, le bouton "Valider" affectera le sujet a l'etudiant choisi.
								  si visa(s) appose(s), un bouton "Affecter" est affiche pour permettre cette affectation   
						    - creation, visualisation, modification ne sont pas possibles pour des sujets qui ne relevent pas de ce(s) type(s) d'etudiant
						*/
            if($etudiant_stagiaire_sans_sujet || !$etudiant_sans_sujet)
            { ?>
            <form name="edit_sujet" method="post" action="edit_sujet.php">
							<input type="hidden" name="action" value="creer">
							<input type="hidden" name="codesujet" value="">
							<input type="hidden" name="codetypesujet" value="02">
							<?php 
              if($etudiant_stagiaire_sans_sujet)
              { ?>
              <input type="hidden" name="sujet_a_affecter" value="oui"> 
                <span class="bleugrascalibri11">&nbsp;pour :&nbsp;</span> 
                <select name="codeetudiantsejour" >
                  <?php
                  foreach($tab_etudiant_sans_sujet['STAGIAIRE'] as $tab_un_etudiant_sans_sujet)
                  { foreach($tab_un_etudiant_sans_sujet as $row_un_etudiant_sans_sujet)
                    {?> 
                    <option value="<?php echo $row_un_etudiant_sans_sujet['codeindividu'] ?>#<?php echo $row_un_etudiant_sans_sujet['numsejour'] ?>"><?php echo $row_un_etudiant_sans_sujet['nom'] ?> <?php echo $row_un_etudiant_sans_sujet['prenom'] ?></option>
                    <?php 
                    }
                  }?>
                </select>
              <?php  
              }	?>
              <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_nouveau_stagiaire">
              <div class="tooltipContent_cadre" id="info_nouveau_stagiaire">
                <span class="noircalibri10">
                	Seuls les sujets de stages qui feront l'objet d'une <b>convention sign&eacute;e par le Directeur du laboratoire</b><br>
                  doivent &ecirc;tre saisis (les sujets des projets de Master2 ne doivent donc pas &ecirc;tre saisis).
                </span>
              </div>
              <script type="text/javascript">
              var sprytooltip_nouveau_stagiaire = new Spry.Widget.Tooltip("info_nouveau_stagiaire", "#sprytrigger_info_nouveau_stagiaire", {useEffect:"blind", offsetX:20, offsetY:20});
              </script>
              <input type="image"  name="b_creer" img class="icon" src="images/b_sujet_stage_creer.png" alt="Cr&eacute;er un sujet de stage" title="Cr&eacute;er un sujet de stage" />
            </form>
            <?php 
            }
						if($etudiant_doctorant_sans_sujet || !$etudiant_sans_sujet)
						{ ?>
            <form name="edit_sujet" method="post" action="edit_sujet.php">
							<input type="hidden" name="action" value="creer">
							<input type="hidden" name="codesujet" value="">
							<input type="hidden" name="codetypesujet" value="03">
							<?php  
							if($etudiant_doctorant_sans_sujet)
							{ ?>
              <input type="hidden" name="sujet_a_affecter" value="oui"> 
                <span class="bleugrascalibri11">&nbsp;pour :&nbsp;</span> 
                <select name="codeetudiantsejour">
                  <?php
                  foreach($tab_etudiant_sans_sujet['DOCTORANT'] as $tab_un_etudiant_sans_sujet)
                  { foreach($tab_un_etudiant_sans_sujet as $row_un_etudiant_sans_sujet)
                    {?> 
                    <option value="<?php echo $row_un_etudiant_sans_sujet['codeindividu'] ?>#<?php echo $row_un_etudiant_sans_sujet['numsejour'] ?>"><?php echo $row_un_etudiant_sans_sujet['nom'] ?> <?php echo $row_un_etudiant_sans_sujet['prenom'] ?></option>
                    <?php 
                    }
                  }?>
                </select>
							 <?php  
              }
							if($GLOBALS['bloque_saisie_these'])
			      	{  ?><span class="rougecalibri10">Th&egrave;ses</span>						
							<?php 
							}
							else
							{ ?><input type="image"  name="b_creer" img class="icon" src="images/b_sujet_these_creer.png" alt="Cr&eacute;er un sujet de th&egrave;se" title="Cr&eacute;er un sujet de th&egrave;se" />
 							<?php 
							} ?>

            </form>
						<?php
						}
            if(!$etudiant_sans_sujet )//postdoc : sujets non obligatoires
            { ?>
            <form name="edit_sujet" method="post" action="edit_sujet.php">
							<input type="hidden" name="action" value="creer">
							<input type="hidden" name="codesujet" value="">
							<input type="hidden" name="codetypesujet" value="04">
			      	<input type="image"  name="b_creer" img class="icon" src="images/b_sujet_postdoc_creer.png" alt="Cr&eacute;er un sujet de postdoc" title="Cr&eacute;er un sujet de postdoc" />
						</form>
						 <?php  
            }
						if($etudiant_collaboration_sans_sujet || !$etudiant_sans_sujet)
						{ ?>
            <form name="edit_sujet" method="post" action="edit_sujet.php">
							<input type="hidden" name="action" value="creer">
							<input type="hidden" name="codesujet" value="">
							<input type="hidden" name="codetypesujet" value="05">
							<?php  
							if($etudiant_collaboration_sans_sujet)
							{ ?>
              <input type="hidden" name="sujet_a_affecter" value="oui"> 
                <span class="bleugrascalibri11">&nbsp;pour :&nbsp;</span> 
                <select name="codeetudiantsejour">
                  <?php
                  foreach($tab_etudiant_sans_sujet['EXTERIEUR'] as $tab_un_etudiant_sans_sujet)
                  { foreach($tab_un_etudiant_sans_sujet as $row_un_etudiant_sans_sujet)
                    {?> 
                    <option value="<?php echo $row_un_etudiant_sans_sujet['codeindividu'] ?>#<?php echo $row_un_etudiant_sans_sujet['numsejour'] ?>"><?php echo $row_un_etudiant_sans_sujet['nom'] ?> <?php echo $row_un_etudiant_sans_sujet['prenom'] ?></option>
                    <?php 
                    }
                  }?>
                </select>
							 <?php  
              }?>
			      	<input type="image"  name="b_creer" img class="icon" src="images/b_sujet_exterieur_invite_visiteur_creer.png" />
						</form>
						<?php
						}?>
            <a href="aide_gestionsujets.php">Aide gestion sujets</a>
          </td>
			  </tr>
		  </table>
      </td>
		</tr>
		<tr>
			<td align="center">
				<table width="100%" border="0" cellpadding="2">
					<tr class="head">
						<td nowrap align="center"><span class="bleugrascalibri11">N&deg;</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Type</span></td> 
						<td nowrap align="center"><span class="bleugrascalibri11">Th&egrave;me(s)</span></td> 
						<td nowrap align="center"><span class="bleugrascalibri11">Dates</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Titre</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11"><?php if($etudiant_sans_sujet){?>Affecter<?php }else{?>Affect&eacute;<?php }?> &agrave;</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Actions</span></td>
						<td align="center"><span class="bleugrascalibri11">Visa<br>D&eacute;posant</span></td>
            <?php if($GLOBALS['avecvisathemesujet'])
						{?> <td align="center"><span class="bleugrascalibri11">Visa<br><?php echo $GLOBALS['libcourt_theme_fr'] ?></span></td>
            <?php 
						}?>
					</tr>
					<?php
					$class="even"; 
					$numrow=0;
					while($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
          { $codesujet=$row_rs_sujet['codesujet'];
						$codeindividu='';
						$numsejour='';
						$estcreateur_ou_encadrant=($row_rs_sujet['codecreateur']==$codeuser || isset($tab_dir[$codesujet][$codeuser]));
						$codestatutsujet=$row_rs_sujet['codestatutsujet'];
						// application des filtres 
						$afficher=true;
						if(!$_SESSION['b_voir_tous_themes'])//limite a theme du codeuser : intersection entre les codetheme du sujet et du codeuser
						{ // liste des themes auxquels appartient $codeuser a ce jour
							$tab_intersect=array();
							$tab_intersect=array_intersect($tab_sujetcodetheme[$codesujet],$tab_infouser['codetheme']);
							if(count($tab_intersect)==0)
							{ $afficher=false;
							}
						}
						
						if($_SESSION['b_voir_mes_sujets'] && !$estcreateur_ou_encadrant)
						{ $afficher=false;
						}
						
						if($_SESSION['sujet_texterecherche']!='' && !isset($tab_dir[$codesujet]['sujet_texterecherche']))
						{ $afficher=false;
						}
						
						// un individu est-il associe ?
						// si oui affichage nom, prenom
						$sujetaffecte=false;
						$estparti=false;
						$civnomprenometudiant='';
						$coderole_en_cours='';// le role correspondant au dernier statutvisa apposé
						if(array_key_exists($codesujet, $tab_sujetetudiant))
						{ $sujetaffecte=true;
							foreach($tab_sujetetudiant[$codesujet] as $codeindividu=>$tab_sejour)
							{ foreach($tab_sejour as $numsejour=>$tab)
								{ $civnomprenometudiant=$tab_sujetetudiant[$codesujet][$codeindividu][$numsejour]['libciv']." ".$tab_sujetetudiant[$codesujet][$codeindividu][$numsejour]['nom']." ".$tab_sujetetudiant[$codesujet][$codeindividu][$numsejour]['prenom'];
									$coderole_en_cours=$tab['coderole'];
									$estparti=$tab_sujetetudiant[$codesujet][$codeindividu][$numsejour]['estparti']=='oui';
								}
							}?>
						<?php 
						}
						?>
						<?php 
						//l'individu connecte $codeuser est-il responsable de l'un des themes du sujet? 
						$estresptheme=isset($tab_estresptheme_sujet[$codesujet]);
						$droitmodif=false;
						$droitsuppr=false;
						//conditions droit de modif/suppression
						if($estcreateur_ou_encadrant)
						{ if($codestatutsujet=="")
							{ $droitmodif=true;
								$droitsuppr=true;
							}
						}
						if($estresptheme)
						{	if($codestatutsujet=="E")
							{ $droitmodif=true;
								if( !$sujetaffecte )
								{ $droitsuppr=true;
								}
							}
							else if($codestatutsujet=="V" && !$sujetaffecte)
							{ $droitsuppr=true;
							}
						}
						
						// s'il existe des etudiants sans sujet, la ligne de ce sujet n'est affichee que si ce dernier est du type de l'une ou l'autre des
						// listes d'etudiants sans sujet 'STAGIAIRE', 'DOCTORANT', 'COLLABORATION'
						// contient la valeur 'STAGIAIRE', 'DOCTORANT', 'COLLABORATION' et est utilisee pour savoir si une liste select etudiant doit etre affichee
						// Remarque : $libtypeetudiant=libelle de la categorie d'etudiant correspondant a ce type de sujet. 
						//            Un champ de correspondance aurait pu etre rajoute dans la table cat depuis qu'un sujet de type stage ne peus etre que master
						$libtypeetudiant=''; 
						//$coderole_a_prevenir=''; 
						if($etudiant_sans_sujet)//s'il existe au moins un etudiant sans sujet, le sujet sera affiche s'il est du meme type
						{ $afficher=false;
							if(!$sujetaffecte)
							{	if($estcreateur_ou_encadrant)/*$estresptheme   && $codestatutsujet=="E" *//*  && $codestatutsujet=="V" */
								{	//$coderole_a_prevenir='theme';
									if($row_rs_sujet['codetypesujet']=='02' && $etudiant_stagiaire_sans_sujet)
									{ $libtypeetudiant='STAGIAIRE';
										if(in_array($row_rs_sujet['codetypestage'],$tab_typestage_etudiant_sans_sujet))
										{ $afficher=true;
										}
									}
									else if($row_rs_sujet['codetypesujet']=='03' && $etudiant_doctorant_sans_sujet)
									{	$libtypeetudiant='DOCTORANT';
										$afficher=true;
									}
									else if($row_rs_sujet['codetypesujet']=='04' && $etudiant_postdoc_sans_sujet)
									{	$libtypeetudiant='POSTDOC';
										$afficher=true;
									}
									else if($row_rs_sujet['codetypesujet']=='05' && $etudiant_collaboration_sans_sujet)
									{	$libtypeetudiant='EXTERIEUR';
										$afficher=true;
									}
								}
							}
						} 
						if(!isset($tab_sujetetudiant[$codesujet]) && !$_SESSION['b_voir_archives_sujets'] )
						{ if($row_rs_sujet['datedeb_sujet']<(date("Y")-2).'/'.date("m").'/'.date("j"))
							{ $afficher=false;
							}
						}
						if($afficher && !$estparti)
						{ $numrow++;?>
            	<tr class="<?php echo (($sujet_ancre==$codesujet)?'marked':($class=="even"?$class="odd":$class="even"))?>" id="t<?php echo $numrow ?>" onClick="m(this)">
              <td align="center"><a name="<?php echo $codesujet?>"></a><span class="noircalibri10"><?php echo $codesujet ?></span>
              </td>
              <td nowrap align="center"><span class="noircalibri10"><?php echo htmlspecialchars($row_rs_sujet['libtypesujet'].($row_rs_sujet['codetypesujet']=='02'?' - '.strtolower($row_rs_sujet['codelibtypestage']):''))  ?>&nbsp;</span>
              </td>
              <td align="center">
              <?php	
							$first=true;    
							foreach($tab_sujetlibtheme[$codesujet] as $libtheme)
							{ ?>
              	<span class="noircalibri10">
                <?php
									if(!$first) {echo '<br>';}
                	echo htmlspecialchars($libtheme);
									$first=false;
									?>
								</span>
							<?php
              }?>
              </td>
              <td align="center">
                <span class="noircalibri10"><?php echo $row_rs_sujet['datedeb_sujet'] ?><br><?php echo $row_rs_sujet['datefin_sujet']?></span>
              </td>
              <td>
              	<table>
                	<tr>
                  	<td nowrap>
                      <span class="infomauve">FR : </span><span class="noircalibri10"><?php echo htmlspecialchars(substr($row_rs_sujet['titre_fr'],0,80)); echo strlen($row_rs_sujet['titre_fr'])>=80?"...":""?></span><br>
                			<?php 
											if($row_rs_sujet['codetypesujet']!="02" || ($row_rs_sujet['codetypesujet']=="02" && $row_rs_sujet['codetypestage']=="01"))
        							{ ?><span class="infomauve">EN : </span><span class="noircalibri10"><?php echo htmlspecialchars(substr($row_rs_sujet['titre_en'],0,80)); echo strlen($row_rs_sujet['titre_en'])>=80?"...":""?></span>
											<?php
                      }?>
										</td>
                  </tr>
                </table>
              </td>
              <td align="center" nowrap>
							<?php 
							$first=true;
							if($sujetaffecte)
              { foreach($tab_sujetetudiant[$codesujet] as $un_codeindividu=>$un_tab_sujetetudiant)
								{ foreach($un_tab_sujetetudiant as $un_numsejour=>$un_tab_sujetetudiantsejour)
									{ echo $first?'':'<br>';
										$first=false;?><a href="javascript:OuvrirVisible('<?php echo "detailindividu.php?codeindividu=".$un_codeindividu.'&numsejour='.$un_numsejour; ?>','Individu')"><img class="icon" width="16" height="16" src="images/b_oeil.png"></a>
                    <span class="infomauve"><?php echo $un_tab_sujetetudiantsejour['libciv'].' '.htmlspecialchars($un_tab_sujetetudiantsejour['nom'].' '.$un_tab_sujetetudiantsejour['prenom']) ?></span>
              		<?php 
									}
								}
              }

							//liste des etudiants du type correspond au type du sujet courant 
							if($libtypeetudiant!='')
							{ // les boutons valider ne sont plus affiches dans les cas suivants : on propose un bouton 'Affecter'
								if($codestatutsujet!="" && $estcreateur_ou_encadrant || $est_admin)
                { $action='affecter'?>
                  <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="action" value="<?php echo $action ?>">
                    <input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                    <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
                    <input type="hidden" name="sujet_a_affecter" value="oui"> 
                 <?php
								}
								// cette liste select est utilisee dans tous les cas : pour ce formulaire avec bouton affecter s'il est affiche ou par les formulaires de validation asoocies aux visas
								// l'id est unique (codesujet en prefixe) = utilise sur validation "deposant", dept. si affection etudiant necessaire
                ?>
                <select name="codeetudiantsejour" id='<?php echo $row_rs_sujet['codesujet'] ?>#codeetudiantsejour'>
                <?php
                foreach($tab_etudiant_sans_sujet[$libtypeetudiant] as $tab_un_etudiant_sans_sujet)
                { foreach($tab_un_etudiant_sans_sujet as $row_un_etudiant_sans_sujet)
                  { if($row_rs_sujet['codetypestage']==$row_un_etudiant_sans_sujet['codetypestage']) 
                  	{?> <option value="<?php echo $row_un_etudiant_sans_sujet['codeindividu'] ?>#<?php echo $row_un_etudiant_sans_sujet['numsejour'] ?>"><?php echo $row_un_etudiant_sans_sujet['nom'] ?> <?php echo $row_un_etudiant_sans_sujet['prenom'] ?></option>
                  <?php
										}
                  }
                }?>
                </select>
                 
              <?php
								// fin du formulaire associe au bouton 'Affecter'. Les boutons valider ne sont plus affiches dans les cas suivants : on propose un bouton 'Affecter'
								if($codestatutsujet!="" && $estcreateur_ou_encadrant || $est_admin)
								{ ?><input name="b_affecter" type="image" img class="icon" src="images/b_affecter.png" alt="Affecter" title="Affecter" 
                  onClick="return confirme('affecter','<?php echo popup_validation_sujet($action,$codesujet,$tab_infouser) ?>'+'\n- Aux gestionnaires')" />
									</form>

								<?php 
								}
              }
              ?>
              </td>
							<td>
                <table border="0" cellspacing="3" cellpadding="2">
                  <tr>
                    <td align="left" nowrap>
                      <a href="javascript:OuvrirVisible('<?php echo "detailsujet.php?codesujet=".$codesujet; ?>','Sujet')"><img class="icon" width="16" height="16" src="images/b_oeil.png"></a>
                    </td>
                    <?php
                    //encore modifiable par tout le monde ou directeur de theme de ce sujet
                    //if(!array_key_exists($codesujet, $tab_sujetetudiant))// actions uniquement si pas affecté a quelqu'un
                    if($droitmodif || $est_admin)
                    { ?>
                    <td align="center">
                      <form name="edit_sujet" method="post" action="edit_sujet.php">
                        <input type="hidden" name="action" value="modifier">
              					<input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                        <input type="hidden" name="codesujet" value="<?php echo $codesujet ?>">
                        <input type="hidden" name="codetypesujet" value="<?php echo $row_rs_sujet['codetypesujet'] ?>">
                        <?php
												$onclick=""; 
												if($libtypeetudiant!='')
                        {?> 
              					<input type="hidden" name="sujet_a_affecter" value="oui"> 
                        <input type="hidden" name="codeetudiantsejour" value="">
                        <?php 
													$onclick=	"codeetudiantsejour.value=document.getElementById('".$row_rs_sujet['codesujet']."#codeetudiantsejour').value;".
																		"return confirm('Cette action affecte le sujet &agrave; l\'&eacute;tudiant s&eacute;lectionn&eacute;.\\n'+
                                  							 'Vous devrez valider le sujet ainsi affect&eacute; pour quitter le formulaire de modification\\n'+
                                                 'qui va vous &ecirc;tre pr&eacute;sent&eacute; si vous confirmez.')";
												}
												else
												{ ?>
													<input type="hidden" name="sujet_a_affecter" value="non"> 
													<input type="hidden" name="codeetudiantsejour" value="<?php echo $codeindividu ?>#<?php echo $numsejour ?>">
												<?php 
												}
												if($GLOBALS['bloque_saisie_these'] && $row_rs_sujet['codetypesujet']=='03')
												{ ?>
												<?php 
												}
												else
												{?>
                        <input type="image" name="b_modifier" img class="icon" width="16" height="16" src="images/b_edit.png" 
                         alt="Modifier" title="Modifier"  
                        onclick="<?php echo $onclick ?>"
                        >
                        <?php 
												}?>
                      </form>
                    </td>
										<?php
                    }
                    else
                    { ?>
                    <td><img class="icon" src="images/espaceur.gif" width="16" height="1"></td>
                    <?php
                    }
                    if($droitsuppr || $est_admin)
                    {?>
                    <td align="center">
                      <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
                        <input type="image" name="b_supprimer" img class="icon" width="16" height="16" src="images/b_drop.png" 
                          alt="Supprimer" title="Supprimer"  onClick="return(confirm('Voulez-vous supprimer le sujet <?php echo $codesujet ?>'))"/>
                      </form>
                    </td>
										<?php
                    }
                    else
                    { ?>
                    <td><img class="icon" src="images/espaceur.gif" width="16" height="1"></td>
                    <?php
                    }
										// bouton afficher_sujet_propose : le sujet reste a l'etat propose meme si affecte
										if(($estcreateur_ou_encadrant || $est_admin) && $row_rs_sujet['codetypesujet']=="03")
										{ ?>
                      <td align="center">
                      <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="action" value="afficher_sujet_propose">
              				<input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                      <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
                      <input type="image" name="b_afficher_sujet_propose_<?php echo $row_rs_sujet['afficher_sujet_propose'] ?>" img class="icon" width="60" height="25" src="images/b_afficher_sujet_propose_<?php echo $row_rs_sujet['afficher_sujet_propose']=='oui'?'oui':'non' ?>.png" 
                            alt="Laisser en propos&eacute; : <?php echo $row_rs_sujet['afficher_sujet_propose'] ?>" title="Laisser en propos&eacute; : <?php echo $row_rs_sujet['afficher_sujet_propose'] ?>"  onClick="return(confirm('Laisser le sujet <?php echo $codesujet ?> propose sur le web meme s\'il est deja affecte a un candidat : <?php echo $row_rs_sujet['afficher_sujet_propose']=='oui'?'non':'oui' ?> ?'))"/>
                      </form>
                      </td>
										<?php
										}
										else
										{ ?>
                    <td>&nbsp;</td>
                    <?php
                    } 

										// bouton afficher_sujet_web
										if((($estresptheme && $GLOBALS['avecvisathemesujet']) || ($estcreateur_ou_encadrant && !$GLOBALS['avecvisathemesujet'])) 
												&& ($row_rs_sujet['codetypesujet']=="03" || $row_rs_sujet['codetypesujet']=="04" || ($row_rs_sujet['codetypesujet']=="02" && $row_rs_sujet['codetypestage']=="01")))
										{ ?>
                      <td align="center">
                      <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="action" value="afficher_sujet_web">
              				<input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                      <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
                      <input type="image" name="b_afficher_sujet_web_<?php echo $row_rs_sujet['afficher_sujet_web'] ?>" img class="icon" width="50" height="13" src="images/b_afficher_sujet_web_<?php echo $row_rs_sujet['afficher_sujet_web'] ?>.png" 
                            alt="Afficher sur le web : <?php echo $row_rs_sujet['afficher_sujet_web'] ?>" title="Afficher sur le web : <?php echo $row_rs_sujet['afficher_sujet_web'] ?>"  onClick="return(confirm('Afficher le sujet <?php echo $codesujet ?> sur le web : <?php echo $row_rs_sujet['afficher_sujet_web']=='oui'?'non':'oui' ?> ?'))"/>
                      </form>
                      </td>
										<?php
										}
										else
										{ ?>
                    <td>&nbsp;</td>
                    <?php
                    } 
										?>
                  </tr>
                </table>
              </td>
              
              <td align="center">
							<?php // colonne Visa referent
              if($codestatutsujet=="")
              { $coderole_a_prevenir='';  
                if($estcreateur_ou_encadrant || $est_admin)
							 	{ $action=$GLOBALS['avecvisathemesujet']?'demander_validation':'valider'?>
                <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="action" value="<?php echo $action ?>">
              		<input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                  <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
									<?php
                  //$onclick=""; 
                  if($libtypeetudiant!='')// Si etudiant(s) attente sujet
                  {?> 
                  <input type="hidden" name="sujet_a_affecter" value="oui"> 
                  <input type="hidden" name="codeetudiantsejour" value="">
                  <?php 
                    //$onclick="codeetudiantsejour.value=document.getElementById('".$row_rs_sujet['codesujet']."#codeetudiantsejour').value";
                  }
									else
									{ ?>
                  	<input type="hidden" name="sujet_a_affecter" value="non"> 
                  	<input type="hidden" name="codeetudiantsejour" value="<?php echo $codeindividu ?>#<?php echo $numsejour ?>">
                  <?php 
									}
									if(!($GLOBALS['bloque_saisie_these'] && $row_rs_sujet['codetypesujet']=='03'))
                  { ?>
                  	
                  <input name="b_valider" type="image" img class="icon" src="images/b_valider.png" alt="Valider" title="Valider" 
                   onClick="<?php if($libtypeetudiant!=''){?>codeetudiantsejour.value=document.getElementById('<?php echo $row_rs_sujet['codesujet'] ?>#codeetudiantsejour').value <?php }?>;
                  					return confirme('valider','<?php echo popup_validation_sujet($action,$codesujet,$tab_infouser) ?>'
																						<?php if($libtypeetudiant!=''){?>+'\n- Aux gestionnaires'<?php }?>)" />
              		<?php 
									}?>
                </form>
              	<?php
								}
								else
								{?><img class="icon" src="images/b_brancher.png" alt="En attente de validation" title="En attente de validation">
              <?php
								}
              } 
              else // E, V, ...
              {?>
                <img class="icon" src="images/b_visa.png" alt="Valid&eacute;" title="Valid&eacute;">
                <?php 
              }?>
              </td>
              <?php 
							if($GLOBALS['avecvisathemesujet']) //colonne visa theme
              {?> <td align="center">
								<?php
                if($codestatutsujet=="")
                { ?>
                <img class="icon" src="images/b_sablier.png" alt="Attente" title="Attente">
                <?php
                }
                else if($codestatutsujet=="E")
                { if($estresptheme)// seul le resp theme peut valider
                  {	?>
                    <form name="gestionsujets" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                      <input type="hidden" name="action" value="valider">
                      <input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>">
                      <input type="hidden" name="codesujet" value="<?php echo $row_rs_sujet['codesujet'] ?>">
                    <?php
                    if($libtypeetudiant!='')
                    {?>
                      <input type="hidden" name="sujet_a_affecter" value="oui"> 
                      <input type="hidden" name="codeetudiantsejour" value="">
                      <?php 
                    }
                    else
                    { ?>
                      <input type="hidden" name="sujet_a_affecter" value="non"> 
                      <input type="hidden" name="codeetudiantsejour" value="<?php echo $codeindividu ?>#<?php echo $numsejour ?>">
                    <?php 
                    }
                    
                    if( !($GLOBALS['bloque_saisie_these'] && $row_rs_sujet['codetypesujet']=='03'))
                    {?>
                     <input name="b_valider" type="image" 
                      img class="icon" src="images/b_valider.png" alt="Valider" title="Valider" 
                      onClick="<?php if($libtypeetudiant!=''){?>codeetudiantsejour.value=document.getElementById('<?php echo $row_rs_sujet['codesujet'] ?>#codeetudiantsejour').value <?php }?>;
                                return confirme('valider','<?php echo popup_validation_sujet('valider',$codesujet,$tab_infouser) ?>'
																								<?php if($libtypeetudiant!=''){?>+'\n- Aux gestionnaires'<?php }?>)" />
                    <?php
                    }
                    ?>
                    </form>
                    <?php 
                  }
                  else
                  {?><img class="icon" src="images/b_brancher.png" alt="En attente de validation" title="En attente de validation">
                    <?php 
                  }
                }
                else//sujet validé
                {?>
                  <img class="icon" src="images/b_visa.png" alt="Valid&eacute;" title="Valid&eacute;">
                <?php 
                }?>
                </td>
              <?php 
							}?>
          	</tr>
					<?php 
          }?>
				<?php
        }?>
        </table>  
      </td>
		</tr>
  </table>
  <script>
	// ce script est en fin de programme car $numrow n'est connu qu'apres le parcours de tous les sujets 
	var nbtablerow=<?php echo $numrow ?>;
	function m(tablerow)// marque ligne en vert
	{ even_ou_odd='even';
		for(numrow=1;numrow<=nbtablerow;numrow++)
		{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
			document.getElementById('t'+numrow).className=even_ou_odd;
		}
		document.getElementById(tablerow.id).className='marked';
	}
	</script>
</body>
</html>
	<?php
if(isset($rs_individustatutvisa)) {mysql_free_result($rs_individustatutvisa);}
if($rs_structureindidividu) {mysql_free_result($rs_structureindidividu);}
if(isset($rs_sujettheme)) {mysql_free_result($rs_sujettheme);}
if(isset($rs_sujet))mysql_free_result($rs_sujet);
if(isset($rs_ind))mysql_free_result($rs_ind);
?>





