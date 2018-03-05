<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
//if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}  */ 
}
$afficheduree=false;//true;
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timedeb=microtime(true);
	echo 'usage memoire '.memory_get_usage ();
} 
$aujourdhui=date('Ymd');
$nbcol=26;//nb col hormis celles par annee
$tab_cumul_annee_montant=array();
$tab_cumul_annee_montant_theme=array();
$tab_cumul_montant_theme=array();
$tabtheme=array();
$tab_cumul_annee_montant_cont_type=array();
$tab_cumul_montant_cont_type=array();
$tabcont_type=array();
$tab_cumul_annee_montant_cont_orggest=array();
$tab_cumul_montant_cont_orggest=array();
$tabcont_orggest=array();
if(!isset($_SESSION['colonnes_visibles']))
{ $_SESSION['colonnes_visibles']=array('datedeb_contrat'=>true,'datefin_contrat'=>true,'duree_mois'=>true,'ref_contrat'=>true,'eotp'=>true,
															'eotp'=>true,'ref_prog_long'=>true,'partenaires'=>true,'sujet'=>true,'permanent_mois'=>true,'personnel_mois'=>true,
															'ht_ttc'=>true,'montant_ht'=>true,'liborgfinanceur'=>true,'libtype'=>true,'liborgfinanceur'=>true,
															'libtheme'=>true,'respscientifique'=>true);
}
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";
$erreur_envoimail="";
$form_gestioncontrats="gestioncontrats";
$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$codecontrat_a_dupliquer=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$contrat_ancre=isset($_GET['contrat_ancre'])?$_GET['contrat_ancre']:(isset($_POST['contrat_ancre'])?$_POST['contrat_ancre']:"");

// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
if(!isset($_SESSION['codecontrat'])) { $_SESSION['codecontrat']=$codecontrat;}
// b_voir_.... : on shifte l'affichage
if(!isset($_SESSION['b_contrats_voir_encours'])) { $_SESSION['b_contrats_voir_encours']=true;}
if(!isset($_SESSION['b_contrats_voir_futurs'])) { $_SESSION['b_contrats_voir_futurs']=false;}
if(!isset($_SESSION['b_contrats_voir_passes'])) { $_SESSION['b_contrats_voir_passes']=false;}
if(!isset($_SESSION['b_contrats_voir_signes'])) { $_SESSION['b_contrats_voir_signes']=false;}
if(!isset($_SESSION['b_contrats_voir_projets'])) { $_SESSION['b_contrats_voir_projets']=false;}

$_SESSION['b_contrats_voir_encours']=isset($_POST['b_contrats_voir_encours_x'])? !$_SESSION['b_contrats_voir_encours']:$_SESSION['b_contrats_voir_encours'];
$_SESSION['b_contrats_voir_futurs']=isset($_POST['b_contrats_voir_futurs_x'])? !$_SESSION['b_contrats_voir_futurs']:$_SESSION['b_contrats_voir_futurs'];
$_SESSION['b_contrats_voir_passes']=isset($_POST['b_contrats_voir_passes_x'])? !$_SESSION['b_contrats_voir_passes']:$_SESSION['b_contrats_voir_passes'];
$_SESSION['b_contrats_voir_signes']=isset($_POST['b_contrats_voir_signes_x'])? !$_SESSION['b_contrats_voir_signes']:$_SESSION['b_contrats_voir_signes'];
$_SESSION['b_contrats_voir_projets']=isset($_POST['b_contrats_voir_projets_x'])? !$_SESSION['b_contrats_voir_projets']:$_SESSION['b_contrats_voir_projets'];

//b_champ_tri
if(!isset($_SESSION['b_champ_tri']))
{ $_SESSION['b_champ_tri']='codecontrat';
}
if(!isset($_SESSION['b_ordre_tri']))
{ $_SESSION['b_ordre_tri']='desc';
}
foreach($_POST as $postkey=>$val)
{ if(strpos($postkey,'b_champ_tri#')!==false)
	{ $postkey=rtrim($postkey,'_x');
		$postkey=rtrim($postkey,'_y');
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $champtri=substr($postkey,strlen('b_champ_tri#'),$posdoublediese-strlen('b_champ_tri#'));
			if($champtri!=$_SESSION['b_champ_tri'])
			{ $_SESSION['b_champ_tri']=$champtri;
				if($_SESSION['b_champ_tri']=='codecontrat')
				{ $_SESSION['b_ordre_tri']='desc';
				}
				else
				{ $_SESSION['b_ordre_tri']='asc';
				}
				//($_SESSION['b_champ_tri']?'desc':);
			}
			else
			{ $_SESSION['b_ordre_tri']=substr($postkey,$posdoublediese+2);
			}
		}
	}
}

// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
	if($action=='traite_admingestfin')
	{ $traite_admingestfin=$_POST['traite_admingestfin'];
		mysql_query("update contrat set traite_admingestfin=".GetSQLValueString($traite_admingestfin, "text").
								" where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	}
	else if($action=='valider')
	{ $erreur_envoimail=mail_validation_contrat($codecontrat,$codeuser);
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec d'envoi de mail pour le contrat ".$codecontrat;;
			$affiche_succes=false;
			$erreur="Validation non effectu&eacute;e.";
		}
		else
		{ // suppression de la ligne avec $codeuser pour cette commande $codecommande pour le role si elle existe
			$updateSQL ="update contrat set estvalide='oui' where codecontrat=".GetSQLValueString($codecontrat, "text");
			mysql_query($updateSQL) or die(mysql_error());
			$message_resultat_affiche="Validation effectu&eacute;e.";
		}
	}
	else if(isset($_POST['b_modif_date_am2i_x']))//($action=='modif_date_am2i')
	{ $affiche_succes=true;
		mysql_query("update contrat set date_am2i=".GetSQLValueString(jjmmaaaa2date($_POST['date_am2i_jj'],$_POST['date_am2i_mm'],$_POST['date_am2i_aaaa']), "text").
								" where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	}
	else if($action=='supprimer')
	{ $affiche_succes=true;
		$message_resultat_affiche='Suppression du contrat '.GetSQLValueString($codecontrat, "text").' effectu&eacute;e avec succ&egrave;s';
		// suppression des pieces jointes et du rep les contenant eventuels
		$rs_contratpj=mysql_query("select codetypepj from contratpj".
																" where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		if(mysql_num_rows($rs_contratpj)>=1)
		{ while($row_rs_contratpj=mysql_fetch_assoc($rs_contratpj))
			{	unlink($GLOBALS['path_to_rep_upload'] .'/contrat/'.$codecontrat.'/'.$row_rs_contratpj['codetypepj']);
			}
			//suppression du rep de ce contrat
			suppr_rep($GLOBALS['path_to_rep_upload'] .'/contrat/'.$codecontrat);
			// suppression des enreg. des pj pour ce contrat
			mysql_query("delete from contratpj where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		}
		mysql_query("delete from contratpart where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		mysql_query("delete from contratmontantannee where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		mysql_query("delete from contrat where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	}
	else if($action=='dupliquer')
	{ $affiche_succes=true;
		$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='contrat'") or  die(mysql_error());
		$row_seq_number=mysql_fetch_assoc($rs_seq_number);
		$codecontrat=$row_seq_number['currentnumber'];
		$codecontrat=str_pad((string)((int)$codecontrat+1), 5, "0", STR_PAD_LEFT);  
		mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecontrat, "text")." where nomtable='contrat'") or  die(mysql_error());
		$message_resultat_affiche='Duplication du contrat '.$codecontrat_a_dupliquer.' effectu&eacute;e avec succ&egrave;s : contrat '.$codecontrat;
		foreach(array('contrat','contratpart','contratmontantannee','contratmontantdetail') as $table)
		{ $rs_contrat_a_dupliquer=mysql_query("SELECT * FROM ".$table." WHERE codecontrat=".GetSQLValueString($codecontrat_a_dupliquer,"text")) or  die(mysql_error());
			while($row_rs_contrat_a_dupliquer=mysql_fetch_assoc($rs_contrat_a_dupliquer))
			{ $rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
				$first=true;
				$liste_champs="";$liste_val="";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
					$liste_val.=($first?"":",");
					$first=false;
					if($row_rs_fields['Field']=='codecontrat')
					{ $liste_val.=GetSQLValueString($codecontrat, "text");
					}
					else if($row_rs_fields['Field']=='estvalide')
					{ $liste_val.=GetSQLValueString('non', "text");
					}
					else
					{ $liste_val.=GetSQLValueString($row_rs_contrat_a_dupliquer[$row_rs_fields['Field']], "text");
					}
				}//fin while
				$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
				//echo '<br>'.$updateSQL;//
				mysql_query($updateSQL) or  die(mysql_error());
				$contrat_ancre=$codecontrat;
			}
		}
	}
	$_SESSION['codecontrat']=$codecontrat;
}
else
{ $codecontrat=$_SESSION['codecontrat'];
}
// ----------------------- liste des contrats a afficher 
$clause_from='';
$clause_where='';
if(count($tab_roleuser)==1 && array_key_exists('theme',$tab_roleuser))//un seul role : theme => limitation theme
{ $clause_where=" and FIND_IN_SET(codestructure,'".implode(",",$tab_infouser['codetheme'])."')>0";
}
$clause_group_by="";
// rajout d'un 'z' en fin de champ r trier pour avoir les libellés vides aprcs les autres
$clause_order_by="";
if($_SESSION["b_champ_tri"]=="codecontrat")
{ $clause_order_by=" codecontrat"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="libtheme")
{ $clause_order_by=" structure.libcourt_fr"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="respscientifique")
{ $clause_order_by=" nom"." ".$_SESSION["b_ordre_tri"].", prenom"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="liborgfinanceur")
{ $clause_order_by=" cont_orgfinanceur.libcourtorgfinanceur"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="datedeb_contrat")
{ $clause_order_by=" datedeb_contrat"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="acronyme")
{ $clause_order_by="  concat(acronyme,'z')"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="libprojet")
{ $clause_order_by=" concat(cont_projet.libcourtprojet,'z')"." ".$_SESSION['b_ordre_tri'].",acronyme desc";
}
if($_SESSION["b_champ_tri"]!="codecontrat")
{ $clause_order_by.=($clause_order_by==""?"":",")."codecontrat desc";
}

$query_rs =	"SELECT distinct contrat.*,nom,prenom, libcourtprojet as libprojet,libcourtorggest as liborggest, libcourttypeconvention  as libtypeconvention,".
									" libcourtnivconfident as libnivconfident, libcourttype  as libtype,libcourtsecteur  as libsecteur,".
									" libcourtorgfinanceur as liborgfinanceur, numclassif,libcourtclassif as libclassif,structure.libcourt_fr as libtheme, estvalide ".
									" FROM contrat,individu,cont_orggest,cont_projet, cont_type,cont_typeconvention,cont_nivconfident,cont_secteur, cont_orgfinanceur, cont_classif,structure".
									$clause_from.
									" WHERE contrat.coderespscientifique=individu.codeindividu".
									" and contrat.codeorggest=cont_orggest.codeorggest ". 
									" and contrat.codetypeconvention=cont_typeconvention.codetypeconvention".
									" and contrat.codeprojet=cont_projet.codeprojet".
									" and contrat.codenivconfident=cont_nivconfident.codenivconfident".
									" and contrat.codetype=cont_type.codetype".
									" and contrat.codesecteur=cont_secteur.codesecteur".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and contrat.codeclassif=cont_classif.codeclassif and contrat.codecontrat<>''".
									" and contrat.codetheme=structure.codestructure".
									" and (".($_SESSION['b_contrats_voir_encours']?periodeencours('datedeb_contrat','datefin_contrat')." or ":"").
													 ($_SESSION['b_contrats_voir_futurs']?periodefuture('datedeb_contrat')." or ":"").
													 ($_SESSION['b_contrats_voir_passes']?periodepassee('datefin_contrat')." or ":"")." false)".
									($_SESSION['b_contrats_voir_signes']?" and date_signature_contrat<>'' ":"").
									($_SESSION['b_contrats_voir_projets']?" and estprojet='oui' ":"").
									$clause_where.
									($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
									($clause_order_by==""?" ORDER BY datedeb_contrat":" ORDER BY ".$clause_order_by);
$rs = mysql_query($query_rs) or die(mysql_error());
$nbtablerow=mysql_num_rows($rs);
while($row_rs=mysql_fetch_array($rs))
{ $tab_contrat[$row_rs['codecontrat']]=$row_rs;
}

$query_rs=" select distinct codecontrat from commandeimputationbudget".
					" where codecontrat<>''".
					" UNION".
					" select distinct commandeimputationbudget.codecontrat from commandeimputationbudget,contrateotp".
					" where commandeimputationbudget.codeeotp=contrateotp.codeeotp";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_array($rs))
{ $tab_contrat_estdansbudget_auneotp[$row_rs['codecontrat']]['estdansbudget']=true;
}
$query_rs="select distinct codecontrat from contrateotp";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_array($rs))
{ $tab_contrat_estdansbudget_auneotp[$row_rs['codecontrat']]['auneotp']=true;
}

// cumul montant_ht
$cumul_montant_ht=0;
// complete par annees de ventilation
$rs=mysql_query("SELECT min(annee) as annee_premiere,max(annee) as annee_derniere from contratmontantannee") or die(mysql_error());
$row_rs=mysql_fetch_assoc($rs);
$annee_premiere=$row_rs['annee_premiere'];
$annee_derniere=$row_rs['annee_derniere'];
$rs_theme = mysql_query("select codestructure as codetheme,libcourt_fr as libtheme".
												" from structure".
												" where esttheme='oui' ".
												//" and ".periodeencours('date_deb','date_fin').
												$clause_where." order by codestructure") or die(mysql_error());	
$rs_cont_type=mysql_query("SELECT codetype,libcourttype as libtype from cont_type order by libcourttype") or die(mysql_error());
$rs_cont_orggest=mysql_query("SELECT codeorggest,libcourtorggest as liborggest from cont_orggest order by libcourtorggest") or die(mysql_error());
for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
{ $tab_cumul_annee_montant[$annee]=0;
	// par theme
	mysql_data_seek ($rs_theme,0); 
	while($row_rs_theme=mysql_fetch_assoc($rs_theme))
	{ $tab_cumul_annee_montant_theme[$row_rs_theme['libtheme']][$annee]=0;
	}
	// par type 
	mysql_data_seek ($rs_cont_type,0); 
	while($row_rs_cont_type=mysql_fetch_assoc($rs_cont_type))
	{ $tab_cumul_annee_montant_cont_type[$row_rs_cont_type['libtype']][$annee]=0;
	}
	// par org gest 
	mysql_data_seek ($rs_cont_orggest,0); 
	while($row_rs_cont_orggest=mysql_fetch_assoc($rs_cont_orggest))
	{ $tab_cumul_annee_montant_cont_orggest[$row_rs_cont_orggest['liborggest']][$annee]=0;
	}
}
//theme
mysql_data_seek ($rs_theme,0); 
while($row_rs_theme=mysql_fetch_assoc($rs_theme))
{ $tabtheme[]=$row_rs_theme['libtheme'];
	$tab_cumul_montant_theme[$row_rs_theme['libtheme']]=0;
}
// cont_type 
mysql_data_seek ($rs_cont_type,0); 
while($row_rs_cont_type=mysql_fetch_assoc($rs_cont_type))
{ $tabcont_type[]=$row_rs_cont_type['libtype'];
	$tab_cumul_montant_cont_type[$row_rs_cont_type['libtype']]=0;
}
// cont_orggest 
mysql_data_seek ($rs_cont_orggest,0); 
while($row_rs_cont_orggest=mysql_fetch_assoc($rs_cont_orggest))
{ $tabcont_orggest[]=$row_rs_cont_orggest['liborggest'];
	$tab_cumul_montant_cont_orggest[$row_rs_cont_orggest['liborggest']]=0;
}
//pieces jointes
$tabpjcontrat=array();
$rs=mysql_query("select contratpj.* from contratpj,typepjcontrat where contratpj.codetypepj=typepjcontrat.codetypepj and codelibtypepj=".GetSQLValueString('contrat', "text")) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tabpjcontrat[$row_rs['codecontrat']]=$row_rs;
}

$tab_contrateotp=array();
$query_rs="select contrateotp.codecontrat,contrateotp.codeeotp,eotp.libcourteotp as libeotp".
											" from contrateotp,eotp".
											" where contrateotp.codeeotp=eotp.codeeotp".
											" and contrateotp.codecontrat<>''".
											" order by contrateotp.numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contrateotp[$row_rs['codecontrat']][$row_rs['codeeotp']]=$row_rs['libeotp'];
}

$query_rs = "SELECT contratpart.codecontrat,contratpart.codepart, libcourtpart as libpart from contratpart,cont_part ".
						" where contratpart.codepart=cont_part.codepart and contratpart.codecontrat<>''".
						" ORDER BY contratpart.numordre desc";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_contratpartenaires=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contratpartenaires[$row_rs['codecontrat']][$row_rs['codepart']]=$row_rs['libpart'];
}

$tab_contratmontantdetail=array();
$query_rs="SELECT codecontrat, count(*) as nbmontantdetail".
					" from contratmontantdetail where reel<>'oui' and ".periodepassee('datemontant').
					" group by codecontrat";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contratmontantdetail[$row_rs['codecontrat']]=$row_rs['nbmontantdetail'];
}

$tab_contratmontantannee=array();
$rs=mysql_query("SELECT codecontrat,annee,montant from contratmontantannee".
								" order by codecontrat,numordre") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contratmontantannee[$row_rs['codecontrat']][$row_rs['annee']]=$row_rs['montant'];
}

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree avant html : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des contrats <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
td { white-space:nowrap
}

table tr.m {
    background-color: #9F0;
}
</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

<SCRIPT language="javascript">
var w;
function OuvrirVisible(codecontrat)
{ w=window.open("detailcontrat.php?codecontrat="+codecontrat,'detailcontrat',"scrollbars = yes,width=700,height=700,location=no,mebubar=no,status=no,directories=no");
	w.document.close();
	w.focus();
}
function OV(codecontrat)
{ return OuvrirVisible(codecontrat);
}

function Fermer() 
{ if (w.document) { w.close(); }
}
var tab_note=new Array()
<?php foreach($tab_contrat as $codecontrat=>$row_rs_contrat)
{ if($row_rs_contrat['note']!='')
	{ ?> tab_note['<?php echo $codecontrat ?>']="<?php echo js_tab_val(nl2br($row_rs_contrat['note'])) ?>";
<?php
	}
}?>

function affiche_note(objet,event)
{ tab=objet.id.split("_");
	codecontrat=tab[1]
	//Coordonnees de la souris
  var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
  var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonnées de l'élément
  var eX = 0;
  var eY = 0;
	var offsetWidth=0;
  var element = objet;
  do
  { eX += element.offsetLeft;
    eY += element.offsetTop;
		offsetWidth=element.offsetWidth;
    element = element.offsetParent;
  } while( element && element.style.position != 'absolute');

	document.getElementById('note').style.left = x + 'px';
	document.getElementById('note').style.top = y + 'px';
	document.getElementById('note').style.display="block";
	document.getElementById('note').style.position = 'absolute';
	document.getElementById('note').innerHTML=tab_note[codecontrat];
}

function cache_note()
{	document.getElementById('note').style.display="none";
}

function e(codecontrat)
{ document.location.href="edit_contrat.php?codecontrat="+codecontrat+"&action=modifier&ind_ancre="+codecontrat;
}

function p(action,codecontrat,val_champ_a_renseigner)//post val du formulaire
{ frm=document.forms["<?php echo $form_gestioncontrats ?>"];
	txt_confirm='';
	if (action=='v')
	{ action='valider';
		champ_a_renseigner='valider';
		txt_confirm='Valider le contrat '+codecontrat+' ?';
	}
	else if(action=='ta')
	{ action='traite_admingestfin';
		champ_a_renseigner='traite_admingestfin';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='non';
			txt_confirm='Cocher le contrat '+codecontrat+' ?';
		}
		else 
		{ val_champ_a_renseigner='oui';
			txt_confirm='Décocher le contrat '+codecontrat+' ?';
		}
	}
	else if(action=='s')
	{ action='supprimer';
		champ_a_renseigner='supprimer';
		txt_confirm='Supprimer le contrat '+codecontrat+' ?';
	}
	else if(action=='d')
	{ action='dupliquer';
		champ_a_renseigner='dupliquer';
		txt_confirm='Dupliquer le contrat '+codecontrat+' ?';
	}
	else if(action=='am2i')
	{ action='modif_date_am2i';
		champ_a_renseigner='modif_date_am2i';
		txt_confirm='Modifier la date du contrat '+codecontrat+' ?';
	}
	frm.elements['action'].value=action;
	frm.elements['codecontrat'].value=codecontrat;
	frm.elements['contrat_ancre'].value=codecontrat;
/* 	confirm(champ_a_renseigner)
 */	frm.elements[champ_a_renseigner].value=val_champ_a_renseigner;
	if(confirm(txt_confirm))
	{ frm.submit();
	}
}

// marquage ligne tr
var nbtablerow=<?php echo $nbtablerow ?>;
function m(tablerow)
{ even_ou_odd='even';
	for(numrow=1;numrow<=nbtablerow;numrow++)
	{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
		document.getElementById('tr_'+numrow).className=even_ou_odd;
	}
	document.getElementById(tablerow.id).className='m';
}
</SCRIPT>
</head>
<body <?php 
			if($erreur!='' || $warning!='')
			{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
			<?php 
			}
			else
      {?> onLoad="window.location.hash='<?php echo $contrat_ancre ?>';"
      <?php 
			}?>
>
<div id="note" class="tooltipContent_cadre bleucalibri11" style="display:none">
</div>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_contrat.gif','titrepage'=>'Gestion des contrats','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'contrat','erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
  <tr>
    <td>
      <table border="0">
        <tr>
          <td>
            <table border="0">
              <tr>
                <td><span class="bleugrascalibri10">Cr&eacute;ation :&nbsp;</span></td>
                <form name="edit_contrat" method="post" action="edit_contrat.php">
                  <input type="hidden" name="action" value="creer">
                  <td><input name="submit_creer" type="image"  img class="icon" src="images/b_contrat_creer.png" alt="Cr&eacute;er" title="Cr&eacute;er" /></td>
                
                  </form>
                  <td><a href="listecontrats.php" target="_blank">Liste des contrats</a></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0">
              <form name="gestioncontrats_voir" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
              <tr>
                <td><img src="images/espaceur.gif" alt="" width="20" height="1"></td>
                <td><span class="bleugrascalibri10">Afficher :&nbsp;</span></td>
                  <td><input name="b_contrats_voir_encours" type="image" class="icon" src="images/b_contrats_voir_encours_<?php echo ($_SESSION['b_contrats_voir_encours']?"oui":"non") ?>.png" alt="Afficher en cours : <?php echo ($_SESSION['b_contrats_voir_encours']?"oui":"non") ?>" title="Afficher en cours : <?php echo ($_SESSION['b_contrats_voir_encours']?"oui":"non") ?>"></td>
                  <td><input name="b_contrats_voir_futurs" type="image"  img class="icon" src="images/b_contrats_voir_futurs_<?php echo ($_SESSION['b_contrats_voir_futurs']?"oui":"non") ?>.png" alt="Afficher pr&eacute;vus : <?php echo ($_SESSION['b_contrats_voir_futurs']?"oui":"non") ?>" title="Afficher pr&eacute;vus : <?php echo ($_SESSION['b_contrats_voir_futurs']?"oui":"non") ?>"></td>
                  <td><img src="images/espaceur.gif" alt="" width="3" height="1">
                    <input name="b_contrats_voir_passes" type="image"  img class="icon" src="images/b_contrats_voir_passes_<?php echo ($_SESSION['b_contrats_voir_passes']?"oui":"non") ?>.png" alt="Afficher pass&eacute;s : <?php echo ($_SESSION['b_contrats_voir_passes']?"oui":"non") ?>" title="Afficher pass&eacute;s : <?php echo ($_SESSION['b_contrats_voir_passes']?"oui":"non") ?>"></td>
                  <td><img src="images/espaceur.gif" alt="" width="3" height="1">
                    <input name="b_contrats_voir_signes" type="image"  img class="icon" src="images/b_contrats_voir_signes_<?php echo ($_SESSION['b_contrats_voir_signes']?"oui":"non") ?>.png" alt="Afficher sign&eacute;s : <?php echo ($_SESSION['b_contrats_voir_signes']?"oui":"non") ?>" title="Afficher sign&eacute;s : <?php echo ($_SESSION['b_contrats_voir_signes']?"oui":"non") ?>"></td>
                  <td><img src="images/espaceur.gif" alt="" width="3" height="1">
                    <input name="b_contrats_voir_projets" type="image"  img class="icon" src="images/b_contrats_voir_projets_<?php echo ($_SESSION['b_contrats_voir_projets']?"oui":"non") ?>.png" alt="Afficher projets : <?php echo ($_SESSION['b_contrats_voir_projets']?"oui":"non") ?>" title="Afficher projets : <?php echo ($_SESSION['b_contrats_voir_projets']?"oui":"non") ?>"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
	<tr>
		<td align="left">
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" class="data" id="table_results">
				<tr class="head">
					<td align="center" class="bleugrascalibri10">Actions</td>
					<td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#codecontrat##<?php if($_SESSION['b_champ_tri']=='codecontrat') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">N&deg;</td>
					<td align="center" class="bleugrascalibri10">AM2I</td>
					<td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#respscientifique##<?php if($_SESSION['b_champ_tri']=='respscientifique') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Resp. scien.</td>
					<td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#liborgfinanceur##<?php if($_SESSION['b_champ_tri']=='liborgfinanceur') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Financeur</td>
					<td align="center" class="bleugrascalibri10">Montant &euro;</td>
          <td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#acronyme##<?php if($_SESSION['b_champ_tri']=='acronyme') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Acronyme</span>
          </td>
					<td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#libprojet##<?php if($_SESSION['b_champ_tri']=='libprojet') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Projet</td>
					<td align="center" class="bleugrascalibri10"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#datedeb_contrat##<?php if($_SESSION['b_champ_tri']=='datedeb_contrat') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png"></span>Date<br>d&rsquo;effet</td>
					<td align="center" class="bleugrascalibri10">Fin</td>
					<td align="center" class="bleugrascalibri10">Nb<br>mois</td>
					<td align="center" class="bleugrascalibri10">Nb mois<br>calcul&eacute;</td>
					<td align="center" class="bleugrascalibri10">Etablissement<br>Gestionnaire</td>
					<td align="center" class="bleugrascalibri10">R&eacute;f&eacute;rence<br>du contrat</td>
					<td align="center" class="bleugrascalibri10">EOTP</td>
					<td align="center" class="bleugrascalibri10">Type de<br>convention</td>
					<td align="center" class="bleugrascalibri10">Secteur<br>d'activit&eacute;</td>
					<td align="center" class="bleugrascalibri10">Niveau<br>Confident.</td>
          <td align="center" class="bleugrascalibri10">Type</td>
					<td align="center" class="bleugrascalibri10">Classification</td>
					<td align="center" class="bleugrascalibri10">R&eacute;f. programme long</td>
					<td align="center" class="bleugrascalibri10"><input type="image" name="b_champ_tri#libtheme##<?php if($_SESSION['b_champ_tri']=='libtheme') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">&nbsp;D&eacute;pt.</td>
					<td align="center" class="bleugrascalibri10">Partenaires</td>
					<td align="center" class="bleugrascalibri10">Objet</td>
					<td align="center" class="bleugrascalibri10">Perm<br>mois</td>
					<td align="center" class="bleugrascalibri10">Pers<br>mois</td>
					<td align="center" class="bleugrascalibri10">HT<br>TTC</td>
					<td align="center" class="bleugrascalibri10">Montant &euro;</td>
          <?php for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
          {?>
          <td align="center" class="bleugrascalibri10"><?php echo $annee ?></td>
          <?php 
					}?>
      </form>
      <form name="<?php echo $form_gestioncontrats ?>" method="post" action="<?php $_SERVER['PHP_SELF'] ?>">
        <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
        <input type="hidden" name="codecontrat" value="">
        <input type="hidden" name="contrat_ancre" value="">
        <input type="hidden" name="action" value="">
        <input type="hidden" name="valider" value="">
        <input type="hidden" name="supprimer" value="">
        <input type="hidden" name="traite_admingestfin" value="">
        <input type="hidden" name="modif_date_am2i" value="">
        <input type="hidden" name="dupliquer" value="">
        <!--<input name="date_am2i_jj" type="text" class="noircalibri10" id="date_am2i_jj" value="<?php echo substr($row_rs_contrat['date_am2i'],8,2); ?>" size="2" maxlength="2"
        onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                              ">
        <input name="date_am2i_mm" type="text" class="noircalibri10" id="date_am2i_mm" value="<?php echo substr($row_rs_contrat['date_am2i'],5,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                    					">
        <input name="date_am2i_aaaa" type="text" class="noircalibri10" id="date_am2i_aaaa" value="<?php echo substr($row_rs_contrat['date_am2i'],0,4); ?>" size="4" maxlength="4"
                    onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
 -->        </form>
				</tr>
				<?php 	
				$class="even";
				$numrow=0;
				foreach($tab_contrat as $codecontrat=>$row_rs_contrat)
				{	$numrow++;
					$estdansbudget=isset($tab_contrat_estdansbudget_auneotp[$codecontrat]['estdansbudget'])?true:false;
					$auneotp=isset($tab_contrat_estdansbudget_auneotp[$codecontrat]['auneotp'])?true:false;;
					$class=($class=="even"?"odd":"even");?> 
					<tr class="<?php echo $contrat_ancre==$codecontrat?'m':$class;?>" name='tr_<?php echo $numrow ?>' id='tr_<?php echo $numrow ?>' onClick="m(this);" onDblClick="e('<?php echo $row_rs_contrat['codecontrat']; ?>')">
            <td>
              <table border="0" cellspacing="0" align="center">
                <tr>
                  <td align="left">
                    <?php 
                    if(array_key_exists($codecontrat,$tabpjcontrat))
                    { ?><a href="download.php?codecontrat=<?php echo $codecontrat?>&codetypepj=<?php echo $tabpjcontrat[$codecontrat]['codetypepj'] ?>" target="_blank" title="T&eacute;l&eacute;charger <?php echo $tabpjcontrat[$codecontrat]['nomfichier'] ?>">
                                <img src="images/b_download.png" width="20"></a>
                    <?php 
                    }
										else
										{?><img src="i/z.png" width="20">
                    <?php 
										}
                    ?>
                  </td>
                  <td align="left">
                    <a href="javascript:OV('<?php echo $codecontrat ?>')">
                    				<img src="i/o.png" <?php 
																							if($row_rs_contrat['note']!='')
																							 { ?> id="note_<?php echo $row_rs_contrat['codecontrat']; ?>" onMouseOver="affiche_note(this,event)" onMouseOut="cache_note()"
																							 <?php 
																							 } ?>></a>
                  </td>
                  <?php
                  // si droit write
                  $droitmodif=(array_key_exists('admingestfin',$tab_roleuser) || array_key_exists('du',$tab_roleuser) || strtolower($tab_infouser['login'])=='chretien1')?'write':'read';
                  if($droitmodif=="write")
                  { ?>
                  <td align="center" width="16">
                    <a href="javascript:e('<?php echo $codecontrat; ?>')"><img src="i/m1.png"></a>
                  </td>
                  <td align="center" width="16">
                    <?php if(!$estdansbudget && !$auneotp)
                    {?> <a href="javascript:p('s','<?php echo $codecontrat; ?>','')"><img src="i/d1.png"></a>
                    <?php 
                    }?>
                  </td>
                  <td width="16">
                  	<a href="javascript:p('d','<?php echo $codecontrat; ?>','')"><img src="i/c.png"></a>
                  </td>
                  <td width="16">
                    <?php 
                    if($row_rs_contrat['date_signature_contrat']!='' && in_array($row_rs_contrat['codeorggest'],array('004','005','010','014','015','016','019')))
                    { if($row_rs_contrat['estvalide']=='oui')//a ete communique comme cree 
                      {?> <img src="i/y1.png">
                      <?php 
                      }
                      else
                      { ?><a href="javascript:p('v','<?php echo $codecontrat; ?>','')"><img src="i/v1.png"></a>
                        
                      <?php
                      }
                    }?>
                  </td>
                  <td align="center" width="16">
                    <?php             
                    if(isset($tab_contratmontantdetail[$codecontrat]))
                    {?>
                      <img src="images/b_attention.png" alt="Retard" width="16" height="16">
                    <?php
                    }
                    else// affichage normal de la case traite_admingestfin
                    {?> <a href="javascript:p('ta','<?php echo $codecontrat; ?>','<?php echo $row_rs_contrat['traite_admingestfin']=='oui'?'o':'n' ?>')"><img src="i/<?php echo $row_rs_contrat['traite_admingestfin']=='oui'?'oui':'non' ?>.png"></a>
                    <?php             
                    }?>
                  </td>
                  <?php	 
                  } 
                  else // en lecture
                  {?>
                  <td width="16"></td>
                  <td width="16"></td>
                  <td width="16"></td>
                  <td width="16"><?php             
                    // Si au moins une ligne de contratmontantdetail avec retard de recette 
                    if(isset($tab_contratmontantdetail[$codecontrat]))
                    {?>
                      <img src="images/b_attention.png" alt="Retard" width="16" height="16">
                    <?php
                    }
                    ?>
                  </td>
                  <?php
                  }?>
                  <td>
                  <?php if($row_rs_contrat['estprojet']=='oui')
                  {?> <img src="images/b_contrat_projet.png" width="16" border="0">
                  <?php 
                  }
                  else
                  {?> <img src="images/b_contrat_signe_<?php echo ($row_rs_contrat['date_signature_contrat']==''?'non':'oui') ?>.png" width="16" border="0">
                  <?php 
                  }?>
                  </td>
                </tr>
              </table>
						</td>
						<td>
						<a name="<?php echo $codecontrat ?>"><?php echo $codecontrat; ?></a>
						</td>
						<td width="16">
            <?php 
            if($droitmodif=="write")
            { ?><form name="b_modif_date_am2i<?php echo $row_rs_contrat['codecontrat']; ?>" method="post" action="<?php echo $_SERVER['PHP_SELF']  ?>">
                  <input type="hidden" name="codecontrat" value="<?php echo $codecontrat; ?>">
                  <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="contrat_ancre" value="<?php echo $codecontrat; ?>">
                  <input name="date_am2i_jj" type="text" class="noircalibri10" id="date_am2i_jj" value="<?php echo substr($row_rs_contrat['date_am2i'],8,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                              ">
                  <input name="date_am2i_mm" type="text" class="noircalibri10" id="date_am2i_mm" value="<?php echo substr($row_rs_contrat['date_am2i'],5,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                    					">
                  <input name="date_am2i_aaaa" type="text" class="noircalibri10" id="date_am2i_aaaa" value="<?php echo substr($row_rs_contrat['date_am2i'],0,4); ?>" size="4" maxlength="4"
                    onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
                  <input name="b_modif_date_am2i" type="image" img class="icon" width="16" height="16" src="images/b_enregistrer.png"/>
                </form>
						<?php 
            }
						else
						{ echo aaaammjj2jjmmaaaa($row_rs_contrat['date_am2i'],'/');
						}?>
						</td>
						<td><?php echo substr($row_rs_contrat['prenom'],0,1).'.&nbsp;'.$row_rs_contrat['nom']; ?></td>
						<td><?php echo $row_rs_contrat['liborgfinanceur']; ?></td>
						<td align="right"><?php echo number_format ( $row_rs_contrat['montant_ht'],2,'.',' ' ); ?></td>
            <td><?php echo $row_rs_contrat['acronyme']; ?></td>
						<td><?php echo $row_rs_contrat['libprojet']; ?></td>
						<td><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/'); ?></td>
						<td><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/'); ?></td>
						<td><?php echo $row_rs_contrat['duree_mois']; ?></td>
            <td><?php 
								$tab_duree=duree_aaaammjj($row_rs_contrat['datedeb_contrat'], $row_rs_contrat['datefin_contrat']);
								if($row_rs_contrat['duree_mois']!=$tab_duree['a']*12+$tab_duree['m'])
								{ echo '<span class="rougecalibri10">'.($tab_duree['a']*12+$tab_duree['m']).'m '.$tab_duree['j'].'j</span>';
								}
								else
								{ echo $tab_duree['a']*12+$tab_duree['m'];
								}
								?>
            </td>
						<td><?php echo $row_rs_contrat['liborggest']; ?></td>
						<td><?php echo $row_rs_contrat['ref_contrat']; ?></td>
						<td>
             <?php 
            $first=true;
						if(isset($tab_contrateotp[$codecontrat]))
						{ foreach($tab_contrateotp[$codecontrat] as $codeeotp=>$libeotp)
							{ echo ($first?"":"<br>").$libeotp;
								$first=false;
							}
						}?>
            </td>
						<td><?php echo $row_rs_contrat['libtypeconvention']; ?></td>
						<td><?php echo $row_rs_contrat['libsecteur']; ?></td>
						<td><?php echo $row_rs_contrat['libnivconfident']; ?></td>
						<td><?php echo $row_rs_contrat['libtype']; ?></td>
						<td><?php echo $row_rs_contrat['numclassif'].' '.$row_rs_contrat['libclassif']; ?></td>
						<td><?php echo $row_rs_contrat['ref_prog_long']; ?></td>
						<td><?php echo $row_rs_contrat['libtheme']; ?></td>
            <td><?php 
            $first=true;
            if(isset($tab_contratpartenaires[$codecontrat]))
						{ foreach($tab_contratpartenaires[$codecontrat] as $codepart=>$libpart)
							{ echo ($first?"":", ").$libpart;
								$first=false;
							}
						}
						?>
						</td>
						<td><?php 
								if($row_rs_contrat['codedoctorant']!='')
								{ // distinct ne devrait pas etre utilis&eacute; mais pour l'instant un individu peut avoir deux s&eacute;jours en cours, 0 (et 1) permet de mettre l'enr vide en 1ere pos
									$query_doctorant_sujet="SELECT individu.codeindividu as codedoctorant,concat(nom,' ',prenom) as nomprenom,sujet.titre_fr as titre_these". 
																				 " FROM individu,individusejour ".
																				 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
																				 " left join sujet on individusujet.codesujet=sujet.codesujet".
																				 " WHERE individu.codeindividu=individusejour.codeindividu".
																				 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'], "text");
									$rs_doctorant_sujet=mysql_query($query_doctorant_sujet) or die(mysql_error());
									if($row_rs_doctorant_sujet=mysql_fetch_assoc($rs_doctorant_sujet))
									{ echo $row_rs_doctorant_sujet['titre_these'];
									}
								}
								else
								{ echo $row_rs_contrat['sujet'];
								}?>
						</td>
						<td align="right"><?php echo $row_rs_contrat['permanent_mois']; ?></td>
						<td align="right"><?php echo $row_rs_contrat['personnel_mois']; ?></td>
						<td align="center"><?php echo $row_rs_contrat['ht_ttc']; ?></td>
						<td align="right">
						<?php echo number_format ( $row_rs_contrat['montant_ht'],2,'.',' ' );
						$cumul_montant_ht+=	$row_rs_contrat['montant_ht'];
						$tab_cumul_montant_theme[$row_rs_contrat['libtheme']]+=	$row_rs_contrat['montant_ht'];
						$tab_cumul_montant_cont_type[$row_rs_contrat['libtype']]+=	$row_rs_contrat['montant_ht'];
						$tab_cumul_montant_cont_orggest[$row_rs_contrat['liborggest']]+=	$row_rs_contrat['montant_ht'];
						?>
						</td>
            <?php 
            for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
            {	if(isset($tab_contratmontantannee[$codecontrat][$annee]))
              { ?><td align="right">
              	<?php 
								$montant=$tab_contratmontantannee[$codecontrat][$annee];
								echo number_format ( $montant ,2,'.',' ' );
								$tab_cumul_annee_montant[$annee]+=$montant;
								$tab_cumul_annee_montant_theme[$row_rs_contrat['libtheme']][$annee]+=$montant;
 								$tab_cumul_annee_montant_cont_type[$row_rs_contrat['libtype']][$annee]+=$montant;
 								$tab_cumul_annee_montant_cont_orggest[$row_rs_contrat['liborggest']][$annee]+=$montant;
              ?></td>
              <?php 
							}
							else
							{?><td></td>
              	<?php
							}?>
         		<?php
            }
						?>
          </tr>
          <?php
			}// fin while
			// montant cumule par colonne annee
			?>
      <tr class="head">
			<?php
			for($numcol=1;$numcol<=$nbcol;++$numcol)
			{ ?><td></td>
			<?php 
			}?>
      <td align="right"><?php echo number_format ( $cumul_montant_ht ,2,'.',' ' ) ?></td>
      <?php
			
			for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
			{ ?><td align="right"><?php echo $tab_cumul_annee_montant[$annee]==0?'':number_format ( $tab_cumul_annee_montant[$annee] ,2,'.',' ' )?></td>
			<?php 
			}
			?>
			</tr>
      <tr>
			<?php
			$bgcolor='#abcabc';
				for($numcol=1;$numcol<=$nbcol-1;++$numcol)
				{ ?><td></td>
				<?php 
				}?>
				<td bgcolor="<?php echo $bgcolor ?>"></td>
        <td bgcolor="<?php echo $bgcolor ?>"></td>
				<?php 
				for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
				{ ?><td align="center" bgcolor="<?php echo $bgcolor ?>"><?php echo $annee; ?></td>
				<?php 
				}
				?>
				</tr>
			<?php
			// montants cumules par GT par colonne annee
			$odd="#E5E5E5";$even="#D5D5D5";$bgcolor=$even;
			foreach($tabtheme as $libtheme)
			{ $bgcolor=$bgcolor==$odd?$even:$odd;
			?><tr>
			<?php
				for($numcol=1;$numcol<=$nbcol-1;++$numcol)
				{ ?><td></td>
				<?php 
				}?>
				<td class="bleucalibri10" bgcolor="<?php echo $bgcolor ?>"><?php echo $libtheme ?></td>
        <td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_montant_theme[$libtheme]==0?'':number_format ( $tab_cumul_montant_theme[$libtheme] ,2,'.',' ' )?></td>
				<?php 
				for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
				{ ?><td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_annee_montant_theme[$libtheme][$annee]==0?'':number_format ( $tab_cumul_annee_montant_theme[$libtheme][$annee] ,2,'.',' ' )?></td>
				<?php 
				}
				?>
				</tr>
			<?php
			} 
			// montants cumules par type par colonne annee
			$bgcolor=$even;
			foreach($tabcont_type as $libcont_type)
			{  $bgcolor=$bgcolor==$odd?$even:$odd;?>
      <tr>
			<?php
				for($numcol=1;$numcol<=$nbcol-1;++$numcol)
				{ ?><td></td>
				<?php 
				}?>
				<td class="mauvecalibri10" bgcolor="<?php echo $bgcolor ?>"><?php echo $libcont_type ?></td>
        <td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_montant_cont_type[$libcont_type]==0?'':number_format ( $tab_cumul_montant_cont_type[$libcont_type] ,2,'.',' ' )?></td>
				<?php 
				for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
				{ ?><td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_annee_montant_cont_type[$libcont_type][$annee]==0?'':number_format ( $tab_cumul_annee_montant_cont_type[$libcont_type][$annee] ,2,'.',' ' )?></td>
				<?php 
				}
				?>
				</tr>
			<?php
			} 
			// montants cumules par org gestionnaire par colonne annee
			$bgcolor=$even;
			foreach($tabcont_orggest as $liborggest)
			{  $bgcolor=$bgcolor==$odd?$even:$odd;?>
      <tr>
			<?php
				for($numcol=1;$numcol<=$nbcol-1;++$numcol)
				{ ?><td></td>
				<?php 
				}?>
				<td class="bleucalibri10" bgcolor="<?php echo $bgcolor ?>"><?php echo $liborggest ?></td>
        <td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_montant_cont_orggest[$liborggest]==0?'':number_format ( $tab_cumul_montant_cont_orggest[$liborggest] ,2,'.',' ' )?></td>
				<?php 
				for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
				{ ?><td align="right" bgcolor="<?php echo $bgcolor ?>"><?php echo $tab_cumul_annee_montant_cont_orggest[$liborggest][$annee]==0?'':number_format ( $tab_cumul_annee_montant_cont_orggest[$liborggest][$annee] ,2,'.',' ' )?></td>
				<?php 
				}
				?>
				</tr>
			<?php
			} 
			if(isset($rs)) mysql_free_result($rs);
			if(isset($rs_doctorant_sujet))mysql_free_result($rs_doctorant_sujet);
			if(isset($rs_contrat))mysql_free_result($rs_contrat);
			?>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
<?php if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree apres html : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}?>

