<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$tab_contexte=array('prog'=>'gestioneotp_source_masse','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestioneotp_source_masse="gestioneotp_source_masse";
$form_gestioneotp_source_masse_voir="gestioneotp_source_masse_voir";
if($admin_bd)
{ /* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	} */ 
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codeeotp=isset($_GET['codeeotp'])?$_GET['codeeotp']:(isset($_POST['codeeotp'])?$_POST['codeeotp']:"");
$eotp_ou_source=isset($_GET['eotp_ou_source'])?$_GET['eotp_ou_source']:(isset($_POST['eotp_ou_source'])?$_POST['eotp_ou_source']:"");
$eotp_ancre=isset($_GET['eotp_ancre'])?$_GET['eotp_ancre']:(isset($_POST['eotp_ancre'])?$_POST['eotp_ancre']:"");

if(!isset($_SESSION['b_cmd_voir_typecredit_ul'])) { $_SESSION['b_cmd_voir_typecredit_ul']=true;}
if(!isset($_SESSION['b_cmd_voir_typecredit_cnrs'])) { $_SESSION['b_cmd_voir_typecredit_cnrs']=true;}
if(!isset($_SESSION['b_cmd_voir_eotp'])) { $_SESSION['b_cmd_voir_eotp']=true;}
if(!isset($_SESSION['b_cmd_voir_dotation'])) { $_SESSION['b_cmd_voir_dotation']=true;}
$_SESSION['b_cmd_voir_typecredit_ul']=isset($_POST['b_cmd_voir_typecredit_ul_x'])? !$_SESSION['b_cmd_voir_typecredit_ul']:$_SESSION['b_cmd_voir_typecredit_ul'];
$_SESSION['b_cmd_voir_typecredit_cnrs']=isset($_POST['b_cmd_voir_typecredit_cnrs_x'])? !$_SESSION['b_cmd_voir_typecredit_cnrs']:$_SESSION['b_cmd_voir_typecredit_cnrs'];
$_SESSION['b_cmd_voir_eotp']=isset($_POST['b_cmd_voir_eotp_x'])? !$_SESSION['b_cmd_voir_eotp']:$_SESSION['b_cmd_voir_eotp'];
$_SESSION['b_cmd_voir_dotation']=isset($_POST['b_cmd_voir_dotation_x'])? !$_SESSION['b_cmd_voir_dotation']:$_SESSION['b_cmd_voir_dotation'];


// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
	if($erreur=='')
	{ if($action=="supprimer")
		{ if($eotp_ou_source=='eotp')
			{ $updateSQL = "delete from contrateotp where codeeotp=".GetSQLValueString($codeeotp, "text");
				mysql_query($updateSQL) or die(mysql_error());
				$updateSQL = "delete from eotp where codeeotp=".GetSQLValueString($codeeotp, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
			else
			{ $updateSQL = "delete from budg_aci where codeaci=".GetSQLValueString($codeeotp, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
			$updateSQL = "delete from eotp_source_montant where codeeotp=".GetSQLValueString($codeeotp, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}
}

// ----------------------- Formulaire de donnees 

$clause_where_cmd=($_SESSION['b_cmd_voir_typecredit_cnrs'] && $_SESSION['b_cmd_voir_typecredit_ul']?"":
									" and typecredit.codetypecredit=".GetSQLValueString($_SESSION['b_cmd_voir_typecredit_cnrs']?'01':'02', "text"));
$clause_where_cmd.=($_SESSION['b_cmd_voir_dotation'] && $_SESSION['b_cmd_voir_eotp']?"":
									" and budg_eotp_source_vue.eotp_ou_source=".GetSQLValueString($_SESSION['b_cmd_voir_dotation']?'source':'eotp', "text"));

$tab_eotp_source=array();
// modif du 29/12/2013 utilise vues sans faire deux requetes sur eotp puis sources
$query_rs="select distinct codeeotp from commandeimputationbudget";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commandeimputationbudget[$row_rs['codeeotp']]=$row_rs['codeeotp'];
}
$query_rs="select codeeotp,sum(montantfonctionnement) as montantfonctionnement,sum(montantinvestissement) as montantinvestissement, sum(montantsalaire) as montantsalaire ".
					" from eotp_source_montant ".
					" group by codeeotp";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_eotpmassemontant[$row_rs['codeeotp']]=$row_rs;
}
// modif 29122014
$query_rs="select codeaci, supprimable from budg_aci where supprimable='non'";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_source_supprimable=array();
while($row_rs=mysql_fetch_assoc($rs))				
{ $tab_source_non_supprimable[$row_rs['codeaci']]=$row_rs['supprimable'];
}
// modif 29122014 fin


// nouvelle version avec dates eotp
$query_rs=	"SELECT distinct budg_eotp_source_vue.codeeotp,budg_eotp_source_vue.libeotp,datedeb_eotp, datefin_eotp,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource, ".
						" distinguermasse,'' as montantfonctionnement,'' as montantinvestissement,'' as montantsalaire,".//
						" centrecout_reel.libcourt as libcentrecout_reel, centrefinancier_reel.libcourt as libcentrefinancier_reel,typecredit.codetypecredit, typecredit.libcourt as libtypecredit,eotp_ou_source,".
						" centrecout.codecentrecout,centrecout.libcourt as libcentrecout, centrefinancier.libcourt as libcentrefinancier,".
						" budg_eotp_source_vue.coderespscientifique,individu.nom as nomrespscientifique, individu.prenom as prenomrespscientifique,".
						" 'non' as estdansbudget".
						" FROM centrecout,centrefinancier,typecredit,centrecout_reel,centrefinancier_reel,cmd_typesource, budg_eotp_source_vue".
						" left join budg_contrateotp_source_vue on budg_eotp_source_vue.codeeotp=budg_contrateotp_source_vue.codeeotp".
						" left join budg_contrat_source_vue on  budg_contrateotp_source_vue.codecontrat=budg_contrat_source_vue.codecontrat".
						" left join individu on  budg_contrat_source_vue.coderespscientifique=individu.codeindividu".
						" where budg_eotp_source_vue.codecentrecout_reel=centrecout_reel.codecentrecout_reel and centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel".
						" and budg_eotp_source_vue.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=typecredit.codetypecredit".
						" and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource".
						$clause_where_cmd.
						//" and ".intersectionperiodes('budg_eotp_source_vue.datedeb_eotp','budg_eotp_source_vue.datefin_eotp',"'".$GLOBALS['date_deb_exercice_comptable']."'","'".$GLOBALS['date_fin_exercice_comptable']."'").
						" and budg_eotp_source_vue.codeeotp<>''".
						" order by eotp_ou_source desc,typecredit.codetypecredit, centrefinancier.numordre, centrecout.numordre, individu.nom";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))				
{ $codeeotp=$row_rs['codeeotp'];
	$tab_eotp_source[$codeeotp]=$row_rs;
	if(isset($tab_commandeimputationbudget[$row_rs['codeeotp']]))
	{ $tab_eotp_source[$codeeotp]['estdansbudget']='oui';
	}
	if(isset($tab_eotpmassemontant[$row_rs['codeeotp']]))
	{ $tab_eotp_source[$codeeotp]['montantfonctionnement']=$tab_eotpmassemontant[$row_rs['codeeotp']]['montantfonctionnement'];
		$tab_eotp_source[$codeeotp]['montantinvestissement']=$tab_eotpmassemontant[$row_rs['codeeotp']]['montantinvestissement'];
		$tab_eotp_source[$codeeotp]['montantsalaire']=$tab_eotpmassemontant[$row_rs['codeeotp']]['montantsalaire'];
	}
	//par defaut le ou les contrat de cet eotp ne sont pas dans budget : modifie dans le parcours des contrats ci dessous
	if($row_rs['coderespscientifique']=='')
	{	// resptheme(s) ou direction
		$query_rs1="select if(structure.codestructure='00','oui','non') as estdirection,structure.libcourt_fr".
								" from structure,centrecouttheme".
								" where centrecouttheme.codestructure=structure.codestructure".
								" and centrecouttheme.codecentrecout=".GetSQLValueString($row_rs['codecentrecout'], 'text');
		$rs1=mysql_query($query_rs1) or die(mysql_error());
		if($row_rs1=mysql_fetch_assoc($rs1))
		{  $tab_eotp_source[$codeeotp]['nomrespscientifique']=$row_rs1['estdirection']=='oui'?"Directeur":"Resp. ".$row_rs1['libcourt_fr'];
		}
		// par defaut de respcontrat ET de resptheme : le directeur
		if($tab_eotp_source[$codeeotp]['nomrespscientifique']=='')
		{ $tab_eotp_source[$codeeotp]['nomrespscientifique']="Directeur";
		}
	}
	$tab_eotp_source[$codeeotp]['uncontratestdansbudget']=false;
	$query_rs1="select count(*) as nblignerecette from eotp_source_montant where codeeotp=".GetSQLValueString($codeeotp, "text");
	$rs1=mysql_query($query_rs1) or die(mysql_error());
	$row_rs1=mysql_fetch_assoc($rs1);
	$tab_eotp_source[$codeeotp]['nblignerecette']=$row_rs1['nblignerecette'];
	// modif 29122014
	if($row_rs['eotp_ou_source']=='source' && isset($tab_source_non_supprimable[$codeeotp]))
	{ $tab_eotp_source[$codeeotp]['sourcesupprimable']=false;
	}
	else
	{ $tab_eotp_source[$codeeotp]['sourcesupprimable']=true;
	}
	// modif 29122014 fin
}
  
// liste des contrats associes aux EOTPs qu'ils soient ou non dans les imputations
$query_rs = "SELECT distinct budg_contrat_source_vue.codecontrat,budg_contrateotp_source_vue.codeeotp, budg_contrat_source_vue.libcontrat, budg_contrat_source_vue.contrat_ou_source,".
						" cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource, if(commandeimputationbudget.codecontrat IS NULL,'non','oui') as estdansbudget,".
						" budg_contrat_source_vue.coderespscientifique,nomrespscientifique,prenomrespscientifique,typecredit.codetypecredit,budg_contrat_source_vue.datedeb_contrat,budg_contrat_source_vue.datefin_ieb".
						" from centrecout,centrefinancier,typecredit,cmd_typesource,budg_eotp_source_vue, budg_contrateotp_source_vue, budg_contrat_source_vue".
						" left join commandeimputationbudget on (budg_contrat_source_vue.codecontrat=commandeimputationbudget.codecontrat and commandeimputationbudget.virtuel_ou_reel='0')".
						" where  budg_contrat_source_vue.codecontrat=budg_contrateotp_source_vue.codecontrat and budg_contrateotp_source_vue.codeeotp=budg_eotp_source_vue.codeeotp".
						" and budg_eotp_source_vue.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=typecredit.codetypecredit".
						" and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource and budg_contrat_source_vue.codecontrat<>''".
						" order by libcontrat,datedeb_contrat asc";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(isset($tab_eotp_source[$row_rs['codeeotp']]))
	{ $tab_eotp_source[$row_rs['codeeotp']]['tab_contrat_source'][$row_rs['codecontrat']]=$row_rs;
		if($row_rs['estdansbudget']=='oui')
		{ $tab_eotp_source[$row_rs['codeeotp']]['uncontratestdansbudget']=true;
		}
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion du budget <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
<SCRIPT language="javascript">
	// marqueligne
	var nbtablerow=<?php echo count($tab_eotp_source)?>;
	function m(tablerow)
	{ even_ou_odd='even';
		for(numrow=1;numrow<=nbtablerow;numrow++)
		{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
			document.getElementById('t'+numrow).className=even_ou_odd;
		}
		document.getElementById(tablerow.id).className='marked';
	}
	var w;
	function OuvrirVisible(url)
	{ w=window.open(url,'detailcontrat',"scrollbars = yes,width=700,height=700,location=no,mebubar=no,status=no,directories=no");
		w.document.close();
		w.focus();
	}
	function Fermer() 
	{ if (w.document) { w.close(); }
	}
</SCRIPT>
</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"<?php 
						}
						else
						{?> onLoad="window.location.hash='<?php echo $eotp_ancre ?>'"
						<?php 
						}?>
						>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<tr>
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'EOTP/Sources-masses','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
	</tr>
	<tr>
		<td>&nbsp;
   </td>
	</tr>
	<tr>
		<td>
    	<table>
        <tr>
          <td>
            <table>
              <tr>
                <td>
                <form name="edit_eotp_source" method="post" action="edit_eotp_source_masse.php">
                  <input type="image" name="creer" src="images/b_source_creer.png">
                  <input type="hidden" name="action" value="creer">
                  <input type="hidden" name="codeeotp" value="">
                  <input type="hidden" name="eotp_ou_source" value="source">
                </form>
                </td>
                <td>
                  <form name="<?php echo $form_gestioneotp_source_masse_voir ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <table>
                      <tr>
                        <td nowrap>
                         <span class="bleucalibri10">UL&nbsp;</span><input  name="b_cmd_voir_typecredit_ul" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_typecredit_ul']?"oui":"non") ?>.png" alt="Afficher les cr&eacute;dits : <?php echo ($_SESSION['b_cmd_voir_typecredit_ul']?"oui":"non") ?>">
                        </td>
                        <td nowrap>
                          <span class="bleucalibri10">CNRS&nbsp;</span><input  name="b_cmd_voir_typecredit_cnrs" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_typecredit_cnrs']?"oui":"non") ?>.png" alt="Afficher les cr&eacute;dits : <?php echo ($_SESSION['b_cmd_voir_typecredit_cnrs']?"oui":"non") ?>" >
                        </td>
                        <td nowrap>
                         <span class="bleucalibri10">EOTP&nbsp;</span><input  name="b_cmd_voir_eotp" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_eotp']?"oui":"non") ?>.png" alt="Afficher les eotp : <?php echo ($_SESSION['b_cmd_voir_eotp']?"oui":"non") ?>">
                        </td>
                        <td nowrap>
                          <span class="bleucalibri10">Source&nbsp;</span><input  name="b_cmd_voir_dotation" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_dotation']?"oui":"non") ?>.png" alt="Afficher la dotation : <?php echo ($_SESSION['b_cmd_voir_dotation']?"oui":"non") ?>" >
                        </td>
                         <td nowrap>
                          <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_eotp_source_masse">
                          <div class="tooltipContent_cadre" id="info_eotp_source_masse">
                            <span class="noircalibri10">
                            	La liste ci-dessous est construite &agrave; partir des Sources/EOTP.<br>
                              Les acronymes des contrats sont ceux qui sont associ&eacute;s dans la gestion des Contrats/eotp.<br>
                              Pour la 'Dotation', Sources/EOTP et Sources/contrats sont strictement identiques.<br>
                              A noter qu'un EOTP qui n'est pas associ&eacute; &agrave; un contrat n'est pas pr&eacute;sent&eacute; dans la liste des Sources/EOTP d'une commande.<br><br> 
                              La suppression d'un EOTP n'est pas propos&eacute;e si :<br>
                              &nbsp;&nbsp;- l'EOTP figure dans une imputation de commande<br>
                              &nbsp;&nbsp;- le (ou les) contrat associ&eacute; figure dans une imputation de commande<br>
                              La suppression d'une source (hors contrat/eotp)  n'est pas propos&eacute;e si celle-ci figure dans une imputation de commande
                            </span>
                          </div>
                          <script type="text/javascript">
                            var sprytooltip_info_eotp_source_masse = new Spry.Widget.Tooltip("info_eotp_source_masse", "#sprytrigger_info_eotp_source_masse", { offsetX:-200, offsetY:20});
                          </script>
                        </td>
                     </tr>
                    </table>
                    </form>
                  </td>
                </tr>
              </table>
          </td>
        </tr>
      	<tr>
        	<td>
            <table border="0" class="data" id="table_results">
              <tr class="head">
                <td nowrap class="bleugrascalibri10">Cr&eacute;dits
                </td>
                <td nowrap class="bleugrascalibri10">Centre financier
                </td>
                <td nowrap class="bleugrascalibri10">Centre de co&ucirc;t
                </td>
                <td nowrap class="bleugrascalibri10">Type de cr&eacute;dits
                </td>
                <td nowrap class="bleugrascalibri10">Enveloppe
                </td>
                <td nowrap class="bleugrascalibri10">Source/EOTP
                </td>
                <td nowrap class="bleugrascalibri10">Source/Contrat(s)
                </td>
                <td nowrap class="bleugrascalibri10">Responsable de cr&eacute;dits
                </td>
                <td nowrap class="bleugrascalibri10">Date deb. eotp<br>Date fin eotp
                </td>
                <td nowrap class="bleugrascalibri10">Date deb. contrat<br>Date fin ieb
                </td>
                <td nowrap class="bleugrascalibri10">Fonctionnement
                </td>
                <td nowrap class="bleugrascalibri10">Salaires
                </td>
                <td nowrap class="bleugrascalibri10">Investissement
                </td>
                <td nowrap class="bleugrascalibri10">Distinguer<br>masses
                </td>
                <td nowrap class="bleugrascalibri10">Action</td>
              </tr>
              <?php $class='even';?>
              <tr class="<?php echo $class=='even'?'odd':'even' ?>">
              <?php
							$numrow=0; 	
              $class="even";
              foreach($tab_eotp_source as $codeeotp=>$un_tab_eotp_source)
              { $numrow++;
								$class=="even"?$class="odd":$class="even"; ?> 
							<tr class="<?php echo $eotp_ancre==$codeeotp?'marked':$class?>" id="t<?php echo $numrow ?>" onClick="m(this)" onDblClick="document.forms['edit_eotp_source_masse<?php echo $codeeotp ?>'].submit()">
                <td nowrap align="left"><a name="<?php echo $codeeotp ?>"></a><?php //echo $codeeotp ?> <?php echo $un_tab_eotp_source['libtypecredit'] ?></td>
                <td nowrap align="left"><?php echo $un_tab_eotp_source['libcentrefinancier_reel'] ?></td>
                <td nowrap align="left"><?php echo $un_tab_eotp_source['libcentrecout_reel'] ?></td>
                <td nowrap align="left"><?php echo $un_tab_eotp_source['libcentrefinancier'] ?></td>
                <td nowrap align="left"><?php //echo $un_tab_eotp_source['codecentrecout'] ?><?php echo $un_tab_eotp_source['libcentrecout'] ?></td>
                <td nowrap align="left">
								<?php if($un_tab_eotp_source['eotp_ou_source']=='eotp')
											{ echo $un_tab_eotp_source['nomrespscientifique'].' '.substr($un_tab_eotp_source['prenomrespscientifique'],0,1).'. <b>-</b> '.$un_tab_eotp_source['libeotp'];
											}
											else
											{ $tab_construitsource=array(	'codetypesource'=>$un_tab_eotp_source['codetypesource'],'libtypesource'=>$un_tab_eotp_source['libtypesource'],
																										'libsource'=>$un_tab_eotp_source['libeotp'],'libcentrecout_reel'=>$un_tab_eotp_source['libcentrecout_reel'],
																										'coderespscientifique'=>$un_tab_eotp_source['coderespscientifique'],'nomrespscientifique'=>$un_tab_eotp_source['nomrespscientifique'],
																										'prenomrespscientifique'=>$un_tab_eotp_source['prenomrespscientifique'],'codetypecredit'=>$un_tab_eotp_source['codetypecredit']);
  											echo construitlibsource($tab_construitsource);
											}
 											?>
               </td>
               <td nowrap align="left">
									<?php 
										if(isset($un_tab_eotp_source['tab_contrat_source'])) 
										{ $first=true;
											foreach($un_tab_eotp_source['tab_contrat_source'] as $un_codecontrat=>$un_tab_contrat_source)
											{ echo ($first?'':'<br>');
												$first=false;
												if($un_tab_contrat_source['contrat_ou_source']=='contrat')
												{ echo $un_tab_contrat_source['nomrespscientifique'].' '.substr($un_tab_contrat_source['prenomrespscientifique'],0,1).'. <b>-</b> '.$un_tab_contrat_source['libcontrat'];
												}
												else
												{ $tab_construitsource=array(	'codetypesource'=>$un_tab_contrat_source['codetypesource'],'libtypesource'=>$un_tab_contrat_source['libtypesource'],
																											'libsource'=>$un_tab_contrat_source['libcontrat'],'libcentrecout_reel'=>$un_tab_eotp_source['libcentrecout_reel'],
																											'coderespscientifique'=>$un_tab_contrat_source['coderespscientifique'],'nomrespscientifique'=>$un_tab_contrat_source['nomrespscientifique'],
																											'prenomrespscientifique'=>$un_tab_contrat_source['prenomrespscientifique'],'codetypecredit'=>$un_tab_contrat_source['codetypecredit']);
  												echo construitlibsource($tab_construitsource);
												}
											}
										}
									?>
                </td>
                <td nowrap align="left"><?php echo $un_tab_eotp_source['nomrespscientifique'].' '.($un_tab_eotp_source['prenomrespscientifique']==''?'':substr($un_tab_eotp_source['prenomrespscientifique'],0,1).'.') ?></td>
                <td nowrap>
									<?php 
									if($un_tab_eotp_source['eotp_ou_source']=='eotp') 
									{ echo aaaammjj2jjmmaaaa($un_tab_eotp_source['datedeb_eotp'],'/').' - '.aaaammjj2jjmmaaaa($un_tab_eotp_source['datefin_eotp'] ,'/');
									}
									else
									{ echo aaaammjj2jjmmaaaa($GLOBALS['date_deb_exercice_comptable'],'/').' - '.aaaammjj2jjmmaaaa($GLOBALS['date_fin_exercice_comptable'],'/');

									}?>
                </td>
                <td nowrap>
									<?php 
									if(isset($un_tab_eotp_source['tab_contrat_source'])) 
									{ $first=true;
										foreach($un_tab_eotp_source['tab_contrat_source'] as $un_codecontrat=>$un_tab_contrat_source)
										{ echo ($first?'':'<br>');
											$first=false;
											if($un_tab_contrat_source['contrat_ou_source']=='contrat') 
											{ echo aaaammjj2jjmmaaaa($un_tab_contrat_source['datedeb_contrat'],'/').' - '.aaaammjj2jjmmaaaa($un_tab_contrat_source['datefin_ieb'] ,'/');
											}
											else
											{ echo aaaammjj2jjmmaaaa($GLOBALS['date_deb_exercice_comptable'],'/').' - '.aaaammjj2jjmmaaaa($GLOBALS['date_fin_exercice_comptable'],'/');
		
											}
										}
									}?>
                </td>
                <td nowrap align="right"><?php echo $un_tab_eotp_source['montantfonctionnement']!=''?sprintf('%01.2f',$un_tab_eotp_source['montantfonctionnement']):'' ?></td>
                <td nowrap align="right"><?php echo $un_tab_eotp_source['montantsalaire']!=''?sprintf('%01.2f',$un_tab_eotp_source['montantsalaire']):'' ?></td>
                <td nowrap align="right"><?php echo $un_tab_eotp_source['montantinvestissement']!=''?sprintf('%01.2f',$un_tab_eotp_source['montantinvestissement']):'' ?></td>
                <td nowrap align="center"><img src="images/b_checked_<?php echo $un_tab_eotp_source['distinguermasse']=='oui'?'oui':'non' ?>.png"></td>
                <td>
                  <table>
                    <tr>
                      <td><form name="edit_eotp_source_masse<?php echo $codeeotp ?>" method="post" action="edit_eotp_source_masse.php">
                            <input type="hidden" name="action" value="modifier">
                            <input type="hidden" name="codeeotp" value="<?php echo $codeeotp ?>">
      											<input type="hidden" name="eotp_ancre" value="<?php echo $codeeotp; ?>">
                            <input type="hidden" name="eotp_ou_source" value="<?php echo $un_tab_eotp_source['eotp_ou_source'] ?>">
                            <input type="image" name="modifier" src="images/b_edit.png">
                          </form>
                      </td>
                      <td><?php
                          if( $un_tab_eotp_source['estdansbudget']=='non' && !$un_tab_eotp_source['uncontratestdansbudget'] && $un_tab_eotp_source['sourcesupprimable'])
                          {?> <form name="<?php echo $form_gestioneotp_source_masse ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF']?>">
                            <input type="hidden" name="codeeotp" value="<?php echo $codeeotp ?>">
                            <input type="hidden" name="eotp_ou_source" value="<?php echo $un_tab_eotp_source['eotp_ou_source'] ?>">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="image" name="supprimer" src="images/b_drop.png" 
                            onClick="javascript: return confirm('<?php echo (((int)$un_tab_eotp_source['nblignerecette']==0)?'':'Cette source a '.$un_tab_eotp_source['nblignerecette'].' ligne(s) de recette(s).'.'\n') ?>Confirmez-vous la suppression ?')">
                          </form>
                          <?php 
                          }
													else if ($un_tab_eotp_source['sourcesupprimable'])
													{ ?><img src="images/b_dropgrise.png" border="0">
													<?php 
													}
													?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php
              }?>
            </table>
          </td>
         </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php
if(isset($rs))mysql_free_result($rs);
if(isset($rs1))mysql_free_result($rs1);
?>
