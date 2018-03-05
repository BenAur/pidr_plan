<?php require_once('_const_fonc.php'); ?>
<?php
/* total_depense_anterieur =
 pour 2014, total 2013 non calculable automatiquement et saisi a la main par IEB.
 pour les annees suivantes, depenses des annees precedentes => 2015 = total_depense_anterieur 2014 (depenses 2013) + depenses 2014 valide par IEB lors du passage 2014 -> 2015 
	// Les EOTP  associes qui sont dans une imputation ne peuvent pas etre dissocies du contrat : l'ecran de saisie les presente mais on ne peut pas les selectionner 	
 */
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$peut_etre_admin=/* estrole('gestul',$tab_roleuser)|| estrole('gestcnrs',$tab_roleuser) */ estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser);// 20170420
if($peut_etre_admin==false)
{?> acc&eacute;s restreint !!!
<?php
exit;
}
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ /* foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	} */
}
$nbcolmontantdetail=7;
$nblignemontantdetail=4;
$aujourdhui=date('Ymd');
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche='';
// 12/2013
$tab_champs_date=array( 'datefin_ieb' =>  array("lib" => "Date de fin IEB","jj" => "","mm" => "","aaaa" => ""),
												'date_limite_justification' =>  array("lib" => "Date limite de justification","jj" => "","mm" => "","aaaa" => ""));
// fin 12/2013
$tab_champs_numerique=array('total_depense_anterieur'=>array('lib' => 'Depenses passees','string_format'=>'%01.2f'));

$tab_eotp=array();// table des eotp utilisee plusieurs fois en liste select
$tab_contrateotp=array();//table des eotp du contrat
$tab_contratpart=array();//table des partenaires du contrat
$form_contrateotp = "form_contrateotp";

$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$contrateotp_ancre=isset($_GET['contrateotp_ancre'])?$_GET['contrateotp_ancre']:(isset($_POST['contrateotp_ancre'])?$_POST['contrateotp_ancre']:"");

$submit="";$submit_val="";
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $submit=substr($postkey,0,$posdoublediese);
			$posdiese=strpos($submit,"#");
			if($posdiese!=false)
			{ $submit_val=substr($submit,$posdiese+1);//peut etre un numero ou autre (oui/non pour submit_verrouiller_sujet)
				$submit=substr($submit,0,$posdiese);
			}
		}
	}
}
$numordre='';
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_contrateotp)) 
{ //eotp
	foreach($_POST as $postkey=>$codeeotp)
	{ if(strpos($postkey,'codeeotp#')!==false && $codeeotp!='' )
		{ $numordre=str_pad(substr($postkey,strlen('codeeotp#')),2,'0',STR_PAD_LEFT);
			if(!in_array($codeeotp,$tab_contrateotp))//enleve tout doublon choisi par erreur
			{ $tab_contrateotp[$numordre]=$codeeotp;
			}
		}
	}
	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_contrateotp(&$_POST,$tab_controle_et_format);
	if(count($tab_contrateotp)==0 && $_POST['libcourteotp']=='')
	{ $warning="Contrat sans EOTP : il ne figurera pas dans la liste des contrats des commandes";
	}
	// centre de cout : celui associe au dept du contrat de l'orggest (CNRS ou UL)
	$codecentrecout='';
	$query_rs="select codetypecredit".
						" from contrat,cont_orggesttypecredit where contrat.codeorggest=cont_orggesttypecredit.codeorggest and contrat.codecontrat=".GetSQLValueString($codecontrat, "text");
	$rs=mysql_query($query_rs);
	$row_rs=mysql_fetch_assoc($rs);
	$codetypecredit=$row_rs['codetypecredit'];
	$query_rs="select centrecouttheme.codecentrecout from contrat,centrecouttheme,centrecout,centrefinancier".
						" where contrat.codetheme=centrecouttheme.codestructure".
						" and centrecouttheme.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
						" and codecontrat=".GetSQLValueString($codecontrat, "text")." and centrefinancier.codetypecredit=".GetSQLValueString($codetypecredit, "text");
	$rs=mysql_query($query_rs);
	if($row_rs=mysql_fetch_assoc($rs))
	{ $codecentrecout=$row_rs['codecentrecout'];
	}
	if($codecentrecout=='')
	{ $erreur.="Pas de centre de cout : le contrat n'est pas rattach&eacute; &agrave; un dept !";
	}
	// si modif date_ieb : verif que les commandes associees au contrat sont dans la nouvelle periode
	$query_rs="select datedeb_contrat,codetheme,coderespscientifique from contrat where contrat.codecontrat=".GetSQLValueString($codecontrat, "text");
	$rs=mysql_query($query_rs);
	$row_rs=mysql_fetch_assoc($rs);
	$codetheme=$row_rs['codetheme'];
	$coderespscientifique=$row_rs['coderespscientifique'];
	$datedeb_contrat=$row_rs['datedeb_contrat'];
	$datefin_ieb=jjmmaaaa2date($_POST['datefin_ieb_jj'],$_POST['datefin_ieb_mm'],$_POST['datefin_ieb_aaaa']);
	$date_limite_justification=jjmmaaaa2date($_POST['date_limite_justification_jj'],$_POST['date_limite_justification_mm'],$_POST['date_limite_justification_aaaa']);
	$query_rs="select commande.*".
						" from commande,commandeimputationbudget".
						" where commande.codecommande=commandeimputationbudget.codecommande and codecontrat=".GetSQLValueString($codecontrat, "text").
						" and not (".intersectionperiodes(GetSQLValueString($datedeb_contrat, "text"),GetSQLValueString($datefin_ieb, "text"),'datecommande','datecommande').")";
	$rs=mysql_query($query_rs);
	if($row_rs=mysql_fetch_assoc($rs))
	{ $erreur.='Des commandes portant sur ce contrat sont en dehors des dates d&eacute;but contrat-date fin IEB !';
	}
	
	/* // pour les eotp existants d'un contrat on verifie que dept et resp sont les memes : sinon probleme dans les eotp, les commandes
	// pour un nouvel eotp; pas la peine de verifier : c'est sa premiere affectation
	foreach($tab_contrateotp as $codeeotp)
	{ $query_rs="select contrat.codetheme,contrat.coderespscientifique from contrat,contrateotp".
							" where contrat.codecontrat=contrateotp.codeeotp and contrat.codecontrat<>".GetSQLValueString($codecontrat, "text")." and contrateotp.codeeotp=".GetSQLValueString($codeeotp, "text");
		$rs=mysql_query($query_rs);
		while($row_rs=mysql_fetch_assoc($rs))
		{ if($row_rs['codetheme']!=$codetheme || $row_rs['coderespscientifique']!=$coderespscientifique)
			{ $erreur.="<br>Les contrats li&eacute;s &agrave; cet EOTP ne sont pas identiques en d&eacute;partement ou en resp. de cr&eacute;dits !";
				break;
			}
		}
	} */
	//$erreur='erreur forcée';$warning='warning';
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		// on enregistre d'abord les eotp
		$nouveau_cree=false;
		if(isset($_POST['libcourteotp']) && $_POST['libcourteotp']!='')
		{ if($_POST['codeeotp_a_creer_ou_modifier']=='')
			{	$rs=mysql_query("select max(codeeotp) as maxcodeeotp from eotp");
				if($row_rs=mysql_fetch_assoc($rs))
				{ $codeeotp=str_pad((string)((int)$row_rs['maxcodeeotp']+1), 5, "0", STR_PAD_LEFT);  
					$rs_fields = mysql_query('SHOW COLUMNS FROM eotp');
					$first=true;
					$liste_champs="";$liste_val="";
					while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
					{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
						$liste_val.=($first?"":",");
						$first=false;
						if($row_rs_fields['Field']=='codeeotp')
						{ $liste_val.=GetSQLValueString($codeeotp, "text");
						}
						else if($row_rs_fields['Field']=='codecentrecout')
						{ $liste_val.=GetSQLValueString($codecentrecout, "text");
						}
						else if($row_rs_fields['Field']=='datedeb_eotp')
						{ $liste_val.=GetSQLValueString($datedeb_contrat, "text");//jjmmaaaa2date()// 20170420
						}
						else if($row_rs_fields['Field']=='datefin_eotp')
						{ $liste_val.=GetSQLValueString($datefin_ieb, "text");//jjmmaaaa2date()// 20170420
						}
						else if(isset($_POST[$row_rs_fields['Field']]))
						{ $liste_val.=GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
						}
						else
						{ $liste_val.="''";
						}
					}//fin while
					$updateSQL = "insert into eotp (".$liste_champs.") values (".$liste_val.")";
					mysql_query($updateSQL) or die(mysql_error());
				}
				$nouveau_cree=true;
				$codeeotp_cree=$codeeotp;
			}
			else // modif. d'un eotp existant
			{	$codeeotp=$_POST['codeeotp_a_creer_ou_modifier'];// pour un nouvel eotp ou un eotp a modifier
				$rs_fields = mysql_query('SHOW COLUMNS FROM eotp');
				$updateSQL = "UPDATE eotp SET ";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ if($row_rs_fields['Field']=='codecentrecout')
					{ $updateSQL.=$row_rs_fields['Field']."=".GetSQLValueString($codecentrecout, "text");
						$updateSQL.=",";
					}
					else if(isset($_POST[$row_rs_fields['Field']]))
					{ $updateSQL.=$row_rs_fields['Field']."=".GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
						$updateSQL.=",";
					}
				}//fin while
				$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
				$updateSQL.=" WHERE codeeotp=".GetSQLValueString($codeeotp, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		// Modif. date fin de contrat pour IEB
		$updateSQL ="UPDATE contrat SET acronyme=".GetSQLValueString($_POST['acronyme'], "text").", datefin_ieb=".GetSQLValueString($datefin_ieb, "text").
								", total_depense_anterieur=".GetSQLValueString($_POST['total_depense_anterieur'], "text").", date_limite_justification=".GetSQLValueString($date_limite_justification, "text").
								" WHERE codecontrat=".GetSQLValueString($codecontrat, "text");
		mysql_query($updateSQL) or die(mysql_error());
	
		// ----------------------------- 
		// suppression des eotp existants
		mysql_query("delete from contrateotp where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		// affectation eotp au contrat s'il y en a
		// si un nouveau cree
		if($nouveau_cree)
		{ $numordre=str_pad((string)(count($tab_contrateotp)+1), 2, "0", STR_PAD_LEFT); 
			$tab_contrateotp[$numordre]=$codeeotp_cree;
		}
		foreach($tab_contrateotp as $numordre=>$codeeotp)
		{ $updateSQL ="INSERT into contrateotp (codecontrat,codeeotp,numordre) ".
										" values (".GetSQLValueString($codecontrat, "text").",".GetSQLValueString($codeeotp, "text").",".GetSQLValueString($numordre, "text").")";
			mysql_query($updateSQL) or die(mysql_error());
			$updateSQL = "UPDATE eotp SET datefin_eotp=".GetSQLValueString($datefin_ieb, "text").", codecentrecout=".GetSQLValueString($codecentrecout, "text")." where codeeotp=".GetSQLValueString($codeeotp, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}// Fin de traitement des données si pas d'erreur

}
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//

$query_contrat =	"SELECT contrat.*, libcourtprojet as libprojet,libcourtsecteur as libsecteur, libcourtnivconfident as libnivconfident,libcourttypeconvention as libtypeconvention,".
									" cont_orggesttypecredit.codetypecredit, libcourtorggest as liborggest, libcourttype  as libtype, codetypecat,".
									" libcourtorgfinanceur as liborgfinanceur, numclassif, libcourtclassif as libclassif,structure.libcourt_fr as libtheme,".
									" concat(substring(respscientifique.prenom,1,1),' ',respscientifique.nom) as nomprenomrespscientifique".
									" FROM contrat,cont_projet, cont_secteur, cont_nivconfident,cont_typeconvention,cont_orggest,cont_orggesttypecredit, cont_type, cont_orgfinanceur,".
									" cont_classif,structure, individu as doctorant, individu as respscientifique".
									" WHERE contrat.codeorggest=cont_orggest.codeorggest ". 
									" and cont_orggest.codeorggest=cont_orggesttypecredit.codeorggest".
									" and contrat.codeprojet=cont_projet.codeprojet".
									" and contrat.codetype=cont_type.codetype".
									" and contrat.codetypeconvention=cont_typeconvention.codetypeconvention".
									" and contrat.codesecteur=cont_secteur.codesecteur".
									" and contrat.codenivconfident=cont_nivconfident.codenivconfident".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and contrat.codeclassif=cont_classif.codeclassif".
									" and contrat.codetheme=structure.codestructure".
									" and contrat.coderespscientifique=respscientifique.codeindividu".
									" and contrat.codecontrat=".GetSQLValueString($codecontrat,"text");
$rs_contrat=mysql_query($query_contrat) or die(mysql_error());
$row_rs_contrat=mysql_fetch_assoc($rs_contrat);

// doctorant+sujet
$row_rs_contrat['nomprenomdoctorant']='';
if($row_rs_contrat['codetypecat']=='01' && $row_rs_contrat['codedoctorant']!='')
{ $query_rs= "SELECT concat(substring(prenom,1,1),' ',nom) as nomprenomdoctorant,sujet.titre_fr as titre_these". 
						 " FROM individu,individusejour".
						 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
						 " left join sujet on individusujet.codesujet=sujet.codesujet".
						 " WHERE individu.codeindividu=individusejour.codeindividu".
						 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'],"text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);
	$row_rs_contrat['nomprenomdoctorant']=$row_rs['nomprenomdoctorant'];
	$row_rs_contrat['sujet']=$row_rs['titre_these'];	
}

// Liste des part du contrat 
$rs=mysql_query("SELECT numordre,libcourtpart as libpart from contratpart,cont_part".
								" where contratpart.codecontrat=".GetSQLValueString($codecontrat, "text").
								" and contratpart.codepart=cont_part.codepart".
								" order by numordre") or die(mysql_error());
$nbpart=mysql_num_rows($rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contratpart[$row_rs['numordre']]=$row_rs['libpart'];
}
// Liste des eotp du contrat : codeeotp, estdansbudget
// en cas d'erreur, les valeurs du POST ne sont pas reprises
$tab_contrateotp=array();
$query_rs="SELECT distinct contrateotp.numordre,contrateotp.codeeotp,".
					" if(commandeimputationbudget.codeeotp IS NULL,'non','oui') as estdansbudget".
					" from contrateotp left join commandeimputationbudget on contrateotp.codeeotp=commandeimputationbudget.codeeotp".
					" where contrateotp.codecontrat=".GetSQLValueString($codecontrat, "text").
					" order by numordre";
$rs=mysql_query($query_rs) or die(mysql_error());									
$nbeotp=mysql_num_rows($rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contrateotp[$row_rs['numordre']]=$row_rs;
}

if($erreur=='')
{	
}
else //valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_contrat = mysql_query('SHOW COLUMNS FROM contrat');
	while($row_rs_fields_contrat = mysql_fetch_assoc($rs_fields_contrat)) 
	{ $Field=$row_rs_fields_contrat['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_contrat[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_contrat['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_contrat[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
}

$query_rs="SELECT distinct eotp.*,'oui' as estdansbudget,'oui' as estassociecontrat FROM contrateotp,contrat,cont_orggesttypecredit,eotp".
					" where eotp.codeeotp=contrateotp.codeeotp and contrateotp.codecontrat=contrat.codecontrat".
					" and eotp.codeeotp in (select codeeotp from commandeimputationbudget)".
					" and contrat.codeorggest=cont_orggesttypecredit.codeorggest and cont_orggesttypecredit.codetypecredit=".GetSQLValueString($row_rs_contrat['codetypecredit'], "text").
					" UNION".
					" SELECT distinct eotp.*,'non' as estdansbudget,'oui' as estassociecontrat FROM contrateotp,contrat,eotp,cont_orggesttypecredit".
					" where eotp.codeeotp=contrateotp.codeeotp and contrateotp.codecontrat=contrat.codecontrat".
					" and eotp.codeeotp not in (select codeeotp from commandeimputationbudget)".
					" and contrat.codeorggest=cont_orggesttypecredit.codeorggest and cont_orggesttypecredit.codetypecredit=".GetSQLValueString($row_rs_contrat['codetypecredit'], "text").
					" UNION".//EOTP orphelins = sans contrat
					" SELECT distinct eotp.*,'oui' as estdansbudget,'non' as estassociecontrat FROM centrecout,centrefinancier,eotp".
					" where eotp.codeeotp in (select codeeotp from commandeimputationbudget)".
					" and eotp.codeeotp not in (select codeeotp from contrateotp)".
					" and  eotp.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=".GetSQLValueString($row_rs_contrat['codetypecredit'], "text").
					" UNION".
					" SELECT distinct eotp.*,'non' as estdansbudget,'non' as estassociecontrat FROM centrecout,centrefinancier,eotp".
					" where eotp.codeeotp not in (select codeeotp from commandeimputationbudget)".
					" and eotp.codeeotp not in (select codeeotp from contrateotp)". 
					" and  eotp.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=".GetSQLValueString($row_rs_contrat['codetypecredit'], "text").
					" UNION".
					" SELECT eotp.*,'non' as estdansbudget,'oui' as estassociecontrat from eotp where eotp.codeeotp=''".
					" ORDER BY libcourteotp";
$tab_eotp=array();
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_eotp[$row_rs['codeeotp']]=$row_rs;
}

//liste des contrats associes dans le(s) EOTP  de ce contrat
$query_rs="select contrat.codecontrat,acronyme,contrateotp.codeeotp from contrat,contrateotp".
					" where contrat.codecontrat=contrateotp.codecontrat";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($row_rs['codecontrat']!=$codecontrat)
	{ foreach($tab_contrateotp as $un_numordre=>$un_tabcontrateotp)
		{ if($un_tabcontrateotp['codeeotp']==$row_rs['codeeotp'])
			{ $tab_contrateotp[$un_numordre]['tab_autrecontrat'][$row_rs['codecontrat']]=$row_rs['acronyme'];
			}
		}
	}
}

					
?>

<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des contrats-EOTP <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/alerts.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
var frm=document.forms["<?php echo $form_contrateotp ?>"];
var tab_eotp=new Array();
var val_champ_modifie='aucun';

<?php  
foreach($tab_eotp as $codeeotp=>$un_eotp)
{?>
	tab_eotp["<?php echo $codeeotp ?>"]=new Array();
	<?php
	foreach($un_eotp as $champ=>$valchamp)
	{?>
	tab_eotp["<?php echo $codeeotp ?>"]["<?php echo $champ ?>"]="<?php echo js_tab_val($valchamp);?>";
	<?php 
	}
}?> 
function eotp_estdansbudget(codeeotp)
{ if(tab_eotp[codeeotp]['estdansbudget']=='oui') 
	{ return true;
	}
	else
	{ return false;
	}
}
function detaileotp(codeeotp)
{ var frm=document.forms["<?php echo $form_contrateotp ?>"];
	
	frm.elements["libcourteotp"].value=tab_eotp[codeeotp]["libcourteotp"];
	frm.elements["liblongeotp"].value=tab_eotp[codeeotp]["liblongeotp"];
	frm.elements["noteeotp"].value=tab_eotp[codeeotp]["noteeotp"];
}

</script>	

</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'\\n':'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>
<form name="<?php echo $form_contrateotp ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="return controle_form_contrateotp('<?php echo $form_contrateotp ?>')">
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="codecontrat" value="<?php echo $codecontrat ?>" >
<input type="hidden" name="MM_update" value="<?php echo $form_contrateotp ?>">

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_contrat.gif','titrepage'=>'Contrat','lienretour'=>'gestioncontrateotp.php?contrateotp_ancre='.$contrateotp_ancre,'texteretour'=>'Retour &agrave; la gestion des contrats - EOTP',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td>
      <table border="0">
        <tr>
          <td>
            <span class="bleucalibri9">N&deg; interne : </span>
            <span class="mauvegrascalibri9"><?php echo $row_rs_contrat['codecontrat'] ?></span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%"  border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
          <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                  <table border="0" cellspacing="2" cellpadding="0" width="100%">
                    <tr>
                      <td>
                        <table>
                          <tr>
                            <td>
                            <span class="bleugrascalibri10">Acronyme :</span>&nbsp;<input type="text" value="<?php echo htmlentities($row_rs_contrat['acronyme']) ?>" name="acronyme" class="bleucalibri10" id="acronyme" size="30" maxlength="50">
                            </td>
                            <td><span class="bleugrascalibri10">date fin ieb </span>&nbsp;
                              <input name="datefin_ieb_jj" type="text" class="noircalibri10" id="datefin_ieb_jj" value="<?php echo substr($row_rs_contrat['datefin_ieb'],8,2); ?>" size="2" maxlength="2">
                              <input name="datefin_ieb_mm" type="text" class="noircalibri10" id="datefin_ieb_mm" value="<?php echo substr($row_rs_contrat['datefin_ieb'],5,2); ?>" size="2" maxlength="2">
                              <input name="datefin_ieb_aaaa" type="text" class="noircalibri10" id="datefin_ieb_aaaa" value="<?php echo substr($row_rs_contrat['datefin_ieb'],0,4); ?>" size="4" maxlength="4">
                            </td>
                            <td>
                              &nbsp;&nbsp;<span class="bleugrascalibri10">Date justification :</span>
                              <input name="date_limite_justification_jj" type="text" class="noircalibri10" id="date_limite_justification_jj" value="<?php echo substr($row_rs_contrat['date_limite_justification'],8,2); ?>" size="2" maxlength="2"
                                onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                                          ">
                              <input name="date_limite_justification_mm" type="text" class="noircalibri10" id="date_limite_justification_mm" value="<?php echo substr($row_rs_contrat['date_limite_justification'],5,2); ?>" size="2" maxlength="2"
                                onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                                          ">
                              <input name="date_limite_justification_aaaa" type="text" class="noircalibri10" id="date_limite_justification_aaaa" value="<?php echo substr($row_rs_contrat['date_limite_justification'],0,4); ?>" size="4" maxlength="4"
                                onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
                            </td>
                            <td>
                            <span class="mauvegrascalibri10">D&eacute;penses 2013:</span>&nbsp;<input type="text" name="total_depense_anterieur" class="mauvecalibri10" id="total_depense_anterieur" value="<?php echo $row_rs_contrat['total_depense_anterieur']==''?'':sprintf('%01.2f',$row_rs_contrat['total_depense_anterieur']) ?>" size="12" maxlength="12">
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      	<span class="bleugrascalibri10">EOTP :&nbsp;</span>
                        	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_ajoute_enleve_eotp">
                            <div class="tooltipContent_cadre" id="info_ajoute_enleve_eotp"> 
                            <span class="noircalibri10">
                            	L'acronyme est obligatoire et au moins un EOTP doit &ecirc;tre associ&eacute;.<br>
                              Pour associer un EOTP au contrat, s&eacute;lectionnez le dans la liste &rsquo;Ajouter un EOTP existant au contrat&rsquo; <br>
                              et enregistrez le formulaire : l&rsquo;EOTP choisi appara&icirc;tra dans une nouvelle liste d&eacute;roulante.<br>
                            	<b>A noter :</b> si un autre contrat est dans le m&ecirc;me EOTP, ils sont li&eacute;s par cet EOTP et ne seront repr&eacute;sent&eacute;s que par une seule ligne<br>
                              dans la liste des Source/contrat d'une commande. Ils apparaitront li&eacute;s dans le cumul d&eacute;penses-recettes.<br>
                              L'acronyme de l'autre contrat est affich&eacute; en <span class="rougecalibri10">rouge</span> sous l'EOTP<br>
                              Pour dissocier un EOTP du contrat qui apparait dans une liste d&eacute;roulante, s&eacute;lectionnez l&rsquo;EOTP 'vide' en haut de cette liste et enregistrez.<br>
                            	<b>Attention :</b> il n&rsquo;est pas possible de dissocier un EOTP d&rsquo;un contrat s&rsquo;il a &eacute;t&eacute; utilis&eacute; dans une imputation budg&eacute;taire d&rsquo;une commande.<br>
                            	La suppression d'un EOTP sans imputation et non li&eacute; &agrave; un contrat peut &ecirc;tre effectu&eacute;e dans la &laquo; gestion des EOTP/Sources-masses &raquo;<br>
                            </span> 
                            </div>
                            <script type="text/javascript">
                              var sprytooltip_ajoute_enleve_eotp = new Spry.Widget.Tooltip("info_ajoute_enleve_eotp", "#sprytrigger_info_ajoute_enleve_eotp", { offsetX:20, offsetY:20});
                            </script>
                            <span class="mauvecalibri10">La date de fin d'EOTP est modifi&eacute;e par la date IEB du contrat</span>
                        </td>
                    </tr>
                    <tr>
                      <td>
                        <table cellspacing="2" cellpadding="2" border="0">
                          <?php 
                          reset($tab_contrateotp);
                          $dernier_codeeotp=0;//utilise pour le codeeotp 
                          for($numligne=0;$numligne<=$nbeotp/4;$numligne++)
                          {?>
                          <tr>
                            <?php 
                            $numcolonne=0;
                            while($numcolonne<=3)
                            { list($numordre,$un_contrateotp)=each($tab_contrateotp);
															?>
                              <td>
                              <?php 
															$droitdissociereotp=true;
                              if($numligne*4+$numcolonne<$nbeotp)
                              { $class_cache_ou_affiche_champ_eotp='affiche';
																if($un_contrateotp['estdansbudget']=='oui')
																{ $droitdissociereotp=false;
																	$class_cache_ou_affiche_champ_eotp='cache';
																}
																if(!$droitdissociereotp)
																{ foreach($tab_eotp as $un_codeeotp=>$un_eotp)
																	{ echo ($un_codeeotp==$un_contrateotp['codeeotp']?$un_eotp['libcourteotp']:'');
																	}
																}
																?>
																<div class="<?php echo $class_cache_ou_affiche_champ_eotp ?>">
																							<select name="codeeotp#<?php echo $numligne*4+$numcolonne+1 ?>" class="noircalibri10">
																							<?php
																							foreach($tab_eotp as $un_codeeotp=>$un_eotp)
																							{ ?>
																								<option value="<?php echo $un_codeeotp ?>" <?php echo ($un_codeeotp==$un_contrateotp['codeeotp'])?'selected':''; ?>><?php echo $un_eotp['libcourteotp'] ?></option>
																							<?php 
																							}?>
																							</select>
																</div>
                                <?php
                                $dernier_codeeotp++; 
                              }
															// autre contrat(s) lie a cet eotp
                              if(isset($un_contrateotp['tab_autrecontrat']))
                              { foreach($un_contrateotp['tab_autrecontrat'] as $un_acronyme)
																{ ?><br>Autre contrat li&eacute; &agrave; cet EOTP : <span class="rougecalibri10"><?php echo $un_acronyme; ?></span>
															<?php	
																}
															}?>
                              </td>
                              <?php 
                              $numcolonne++;
                            }
														?>
                          </tr>
                          <?php 
                          }?>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      <span class="bleugrascalibri10">Ajouter un eotp existant au contrat : </span>
                        <select name="codeeotp#<?php str_pad($dernier_codeeotp+1,2,'0',STR_PAD_LEFT) ?>" id="codeeotp#<?php echo str_pad($dernier_codeeotp+1,2,'0',STR_PAD_LEFT) ?>"
                         onChange="if(eotp_estdansbudget(this.value))
                                   { alert('Cet EOTP figure dans une imputation au moins : '+'\n'+il ne sera plus possible de le dissocier du contrat tant qu\'il sera dans une imputation');
                                   }
                                   ">
                          <?php
                          foreach($tab_eotp as $un_codeeotp=>$un_eotp)
                          { ?>
                          <option value="<?php echo $un_codeeotp==""?"":$un_codeeotp;?>"><?php echo htmlentities($un_eotp['libcourteotp']).($un_eotp['estdansbudget']=='oui'?'  [***]':'').($un_eotp['estassociecontrat']=='oui'?'':'  [NA]') ?></option>
                          <?php 
                          }?>
                        </select>
                        <span class="mauvecalibri9">[***] en suffixe d&rsquo;un EOTP indique qu&rsquo;il fait l&rsquo;objet d&rsquo;au moins une imputation, [NA] indique qu&rsquo;il n'est associi&eacute; &agrave; aucun contrat</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
               <td>
                <table border="0" cellspacing="2" cellpadding="0" width="100%">
									<tr>
                  	<td>
                      <span class="orangecalibri10">&nbsp;Cr&eacute;er ou modifier un eotp
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td valign="top">
                      <img src="images/b_plus.png" name="image_eotp_a_creer_ou_modifier" align="texttop" id="image_eotp_a_creer_ou_modifier" onClick="javascript:
                                    nouveau=document.getElementById('eotp_a_creer_ou_modifier');
                                    if(nouveau.className=='affiche')
                                    { nouveau.className='cache';
                                      this.src='images/b_plus.png';
                                    }
                                    else 
                                    { nouveau.className='affiche';
                                      this.src='images/b_moins.png';
                                    }"
                                    >
											<?php $class_cache_ou_affiche='cache'?>
                      <div id="eotp_a_creer_ou_modifier" class="<?php echo $class_cache_ou_affiche?>">
                      <table width="100%" border="0" cellpadding="0" class="table_cadre_arrondi">
                        <tr>
                          <td>
                            <table border="0" cellpadding="2">
                              <tr>
                                <td>
                                 <span class="orangecalibri10">
                                  S&eacute;lectionnez un eotp pour le modifier (aucun pour cr&eacute;er) :&nbsp;</span>
                                  <select name="codeeotp_a_creer_ou_modifier" class="bleucalibri10" onChange="detaileotp(this.value)">
                                    <?php
                                    foreach($tab_eotp as $un_codeeotp=>$un_eotp)
                                    { ?>
                                    <option value="<?php echo $un_codeeotp ?>"><?php echo htmlentities($un_eotp['libcourteotp']) ?></option>
                                    <?php 
                                    }?>
                                  </select>
                                  <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_creer_modifier_eotp">
                                  <div class="tooltipContent_cadre" id="info_creer_modifier_eotp"> 
                                  <span class="noircalibri10">
                                    Pour modifier un eotp (pour tous les contrats qui le mentionnent), <br>
                                    s&eacute;lectionnez le dans la liste : vous pouvez modifier les d&eacute;tails qui s&rsquo;affichent<br>
                                    <b>Attention :</b> assurez vous que ce nouveau libell&eacute; convient pour tous les contrats auxquels il est associ&eacute;.<br>
                                    <br>
                                    Pour cr&eacute;er, laissez vide cette liste de s&eacute;lection, renseignez les champs de d&eacute;tail.<br>
                                    Enregistrez : l&rsquo;eotp apparait dans la liste des eotp et a &eacute;t&eacute; ajout&eacute; au contrat.<br>
                                    Un eotp ne peut pas, pour l&rsquo;instant, &ecirc;tre supprim&eacute; : l&rsquo;administrateur de la base peut le faire<br>
                                    <b>Attention</b> : l&rsquo;organisme gestionnaire de cr&eacute;dits et le centre de cout de l'eotp sont ceux du contrat <br>
                                    sur lequel vous op&eacute;rez.
                                  </span> 
                                  </div>
                                  <script type="text/javascript">
                                    var sprytooltip_creer_modifier_eotp = new Spry.Widget.Tooltip("info_creer_modifier_eotp", "#sprytrigger_info_creer_modifier_eotp", {useEffect:"blind", offsetX:20, offsetY:20});
                                  </script>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <table border="0" cellspacing="3" cellpadding="0">
                                    <tr>
                                      <td class="mauvecalibri10">libell&eacute; : </td>
                                      <td><input type="text" value="" name="libcourteotp" class="bleucalibri10" id="libcourteotp" size="30" maxlength="30"></td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">libell&eacute; long :</td>
                                      <td><input type="text" value="" name="liblongeotp" class="bleucalibri10" id="liblongeotp" size="50" maxlength="100"></td>
                                    </tr>
                                    <tr>
                                      <td valign="top" class="mauvecalibri10">Notes :<br>
                                        <span class="bleucalibri9">(</span><span id="noteeotp#nbcar_js" class="bleucalibri9">0</span><span class="bleucalibri9">/200 car. max.)</span>
                                      </td>
                                      <td><textarea name="noteeotp" cols="50" rows="3" wrap="PHYSICAL" class="bleucalibri10" <?php echo affiche_longueur_js("this","200","'noteeotp#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>></textarea>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                       </table>
                      </div>
                    </td>
                    <!--<td> cellule vide si la suppression d'un eotp est possible
                    </td> -->
                  </tr>
                  <tr>
                    <td valign="top"><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer"></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
         </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
</form>
    <td><?php echo detailcontrat($codecontrat,$codeuser) ?>

<!--      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
          <td>
            <table width="100%" border="0" cellpadding="3" cellspacing="0">
              <tr>
                <td>
                  <span class="bleugrascalibri10">Date d'effet : </span>
                  <span class="noirgrascalibri10"><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/'); ?></span>
                  <span class="bleugrascalibri10">&nbsp;Dur&eacute;e mois :&nbsp;</span>
                  <span class="noirgrascalibri10"><?php echo $row_rs_contrat['duree_mois'] ?></span>
                  <span class="bleugrascalibri10">&nbsp;Date fin :&nbsp;</span>
                  <span class="noirgrascalibri10"><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/'); ?></span>
                  <span class="bleugrascalibri10">&nbsp;Date de signature :&nbsp;</span>
                  <span class="noirgrascalibri10"><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['date_signature_contrat'],'/'); ?></span></td>
                </tr>
              <tr>
                <td><span class="bleugrascalibri10">Montant :&nbsp;</span> <span class="noirgrascalibri10"><?php echo $row_rs_contrat['montant_ht'] ?> <?php echo $row_rs_contrat['ht_ttc'] ?></span></td>
                </tr>
              </table>                
            </td>
          </tr>
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                  <span class="bleugrascalibri10">Secteur d&rsquo;activit&eacute; : </span>
                  </td>
                <td>
                  <span class="noirgrascalibri10"><?php echo $row_rs_contrat['libsecteur'] ?></span>
                  </td>
                <td>
                  <span class="bleugrascalibri10">Niveau de confidentialit&eacute; :&nbsp;</span>
                  </td>
                <td>
                  <span class="noirgrascalibri10"><?php echo $row_rs_contrat['libnivconfident'] ?></span>
                  </td>
                <td>
                  <span class="bleugrascalibri10">Type de convention :&nbsp;</span>
                  </td>
                <td>
                  <span class="noirgrascalibri10"><?php echo $row_rs_contrat['libtypeconvention'] ?></span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <tr>
          <td nowrap>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td class="bleugrascalibri10">Gestionnaire :&nbsp;
                  </td>
                <td class="noirgrascalibri10"><?php echo $row_rs_contrat['liborggest'] ?>
                  </td>
                <td class="bleugrascalibri10">Financeur :&nbsp;
                  </td>
                <td class="noirgrascalibri10"><?php echo $row_rs_contrat['liborgfinanceur'] ?>
                  </td>
                <td class="bleugrascalibri10">Projet :&nbsp;
                  </td>
                <td class="noirgrascalibri10"><?php echo $row_rs_contrat['libprojet'] ?>
                  </td>
                </tr>
              <tr>
                <td class="bleugrascalibri10">Type :&nbsp;
                  </td>
                <td class="noirgrascalibri10"><?php echo $row_rs_contrat['libtype'] ?>
                  </td>
                <td><span class="bleugrascalibri10">Classification :&nbsp;</span></td>
                <td class="noirgrascalibri10"><?php echo $row_rs_contrat['numclassif'].' '.$row_rs_contrat['libclassif'] ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <?php if($row_rs_contrat['codetype']=='001' || $row_rs_contrat['codetype']=='002' || $row_rs_contrat['codetype']=='020')
        { ?>
        <tr>
          <td><span class="bleugrascalibri10">Doctorant :&nbsp;</span><span class="noirgrascalibri10"><?php echo $row_rs_contrat['nomprenomdoctorant'] ?></span>
            </td>
        </tr>
        <?php 
        }?>
        <tr>
          <td><span class="bleugrascalibri10">R&eacute;f&eacute;rence du contrat :&nbsp;</span><span class="noirgrascalibri10"><?php echo $row_rs_contrat['ref_contrat'] ?></span>
          		&nbsp;&nbsp;<span class="bleugrascalibri10">Acronyme :&nbsp;</span><span class="noirgrascalibri10"><?php echo $row_rs_contrat['acronyme'] ?></span>
          </td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri10">R&eacute;f&eacute;rence programme long :&nbsp;</span> <span class="noirgrascalibri10"><?php echo $row_rs_contrat['ref_prog_long'] ?></span></td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri10">Objet :&nbsp;</span> <span class="noirgrascalibri10"><?php echo $row_rs_contrat['sujet']; ?></span></td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri10"><?php echo $GLOBALS['libcourt_theme_fr'] ?> :&nbsp;</span>
          	<span class="noirgrascalibri10"><?php echo $row_rs_contrat['libtheme'] ?></span>&nbsp;&nbsp;
            <span class="bleugrascalibri10">Responsable scientifique :&nbsp;</span>
          	<span class="noirgrascalibri10"><?php echo $row_rs_contrat['nomprenomrespscientifique'] ?></span>&nbsp;&nbsp;
          </td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri10">Permanents impliqu&eacute;s en personne.mois </span><span class="noirgrascalibri10"><?php echo $row_rs_contrat['permanent_mois'] ?></span><span class="bleugrascalibri10">Total personnels impliqu&eacute;s en personne.mois :&nbsp;&nbsp;</span><span class="noirgrascalibri10"><?php echo $row_rs_contrat['personnel_mois'] ?></span></td>
        </tr>
        <tr>
          <td><table border="0" cellpadding="0" cellspacing="3">
            <tr>
              <td><span class="bleugrascalibri10">Partenaires :</span>
              </td>
              <td>
                <table border="0" cellspacing="0" cellpadding="3">
                  <?php 
                    for($numligne=0;$numligne<=$nbpart/4;$numligne++)
                    {?>
                  <tr>
                    <?php 
                      $numcolonne=0;
                      while($numcolonne<=3)
                      { list($numordre,$libpart)=each($tab_contratpart);
                        ?>
                    <td><?php 
                        if($numligne*4+$numcolonne<$nbpart)
                        {?>
                      <span class="noirgrascalibri10"> <?php echo $libpart;
                        }?></span></td>
                    <?php 
                        $numcolonne++;
                      }?>
                  </tr>
                  <?php 
                    }?>
                </table></td>
            </tr>
        </table>
 -->      </td>
  </tr>
</table>
</body>
</html>
    <?php
if(isset($rs_centrecout))mysql_free_result($rs_centrecout);
if(isset($rs_civilite))mysql_free_result($rs_civilite);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
