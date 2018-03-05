<?php  require_once('_const_fonc.php'); /**/ ?>
<?php
/* error_reporting(0);
try
{ */
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey.'='.$postval."<br>";
	} */  
}
$form_fiche_dossier_pers='form_fiche_dossier_pers_partiel';
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$etat_individu=isset($_GET['etat_individu'])?$_GET['etat_individu']:(isset($_POST['etat_individu'])?$_POST['etat_individu']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");

$tab_statutvisa=get_statutvisa();
$estreferent=false;// user a le role referent mais n'est pas forcément referent
$estresptheme=false;// user a le role theme mais pas forcément resptheme : peut etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,$codeindividu,'',$tab_statutvisa,$estreferent,$estresptheme);//renvoie table des roles
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

$tab_champs_date =  array();
$tab_champs_ouinon=array();
$tab_champs_numerique=array();
$tab_champs_heure_mn=	array();

$tab_controle_et_format=array('tab_champs_date' =>  array(),'tab_champs_heure_mn' =>  array(),'tab_champs_numerique' =>  array());

// Par défaut, codeindividu est initialise 

$editFormAction = $_SERVER['PHP_SELF'];
// Vérification puis si pas d'erreur, Enregistrement des données du formulaire : si création dossier codeindividu="" sinon codeindividu existe déja
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_fiche_dossier_pers)) 
{ $erreur.=controle_form_fiche_dossier_pers($_POST,$tab_controle_et_format);	

	// login unique si pas création : le login est ou non envoyé dans le POST. S'il est envoyé, il doit etre <>'' et ne pas exister
	if(isset($_POST['login']))
	{ $query_rs_individu ="SELECT nom,prenom from individu".
												" WHERE codeindividu <>".GetSQLValueString($codeindividu, "text").
												" AND login=".GetSQLValueString($_POST['login'], "text");
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		if($row_rs_individu =mysql_fetch_assoc($rs_individu))
		{ $erreur.="<br>Login ".$_POST['login']." d&eacute;j&agrave; utilis&eacute; pour ".$row_rs_individu['prenom'].' '.$row_rs_individu['nom'];
		}
	}
//$erreur='erreur forcée';
	if($erreur=="")
	{ // par défaut, actuellement sans édition de datedeb_theme, datedeb_theme=datedeb_sejour
	  $affiche_succes=true;// pas d'erreur : affichage du message d'enregistrement effectué
		// En modif. ou en creation (l'enregistrement vient d'etre créé ci-dessus) : maj de l'enregistrement et des enregistrements des tables associees
		// traitement de l'ensemble des champs : un test ecarte les champs a ne pas modifier ou ils ne sont pas traites car pas envoyes dans le post
		// si certains champs ne sont pas renseignes par le post : 
		// - le champ a ete propose au user mais n'est pas recu pour une case a cocher : valeur='non'
		// - le champ n'a pas ete propose au user : pas d'update de ce champ
		// En modification ou apres creation d'un enregistrement pour les tables concernees, toutes les tables sont mises a jour avec les données envoyées
		$tables=array('individu','individusejour');
		foreach($tables as $table)
		{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
			$updateSQL = "UPDATE ".$table." SET ";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ // on update que les champs envoyés dans le POST
				$Field=$row_rs_fields['Field'];
				if(!in_array($Field,array("codeindividu","numsejour")))
				{	if(isset($_POST[$Field]) 
						|| (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'] )) 
						|| (isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
					{ if(in_array($Field, $tab_champs_ouinon)===false)
						{ $updateSQL.=$Field."=";
							// le champ login doit etre renseigne et unique
							if($Field=='login')
							{ $updateSQL.=GetSQLValueString($_POST['login']==''?$codeindividu."#sans login":$_POST['login'], "text");
							}
							else if(array_key_exists($Field, $tab_champs_date)!==false)
							{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
							}
							else if(array_key_exists($Field, $tab_champs_heure_mn)!==false)
							{ $updateSQL.=GetSQLValueString(hhmn2heure($_POST[$Field.'_hh'],$_POST[$Field.'_mn']), "text");
							}
							else
							{ $updateSQL.=GetSQLValueString($_POST[$Field], "text");
							}
						}
						else
						{ $updateSQL.=$Field."='oui'";
						}
						$updateSQL.=",";
					}
					else if(in_array($Field, $tab_champs_ouinon)!==false)//non envoye dans le POST : pas selectionne
					{ $updateSQL.=$Field."='non'".",";
					}
				}
			}
			$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
			$updateSQL.=" WHERE codeindividu=".GetSQLValueString($codeindividu, "text");
			if($table!="individu")// individusejour et individuemploi
			{ if($table=="individusejour")
				{ $updateSQL.=" and numsejour=".GetSQLValueString($numsejour, "text");
				}
 			}
			//echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or die(mysql_error());
		}
		// post-it perso.
		if(isset($_POST['postit']) && $_POST['postit']!='')
		{ $updateSQL ="DELETE from individupostit". 
									" where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and numsejour=".GetSQLValueString($numsejour, "text")." and codeacteur =".GetSQLValueString($codeuser, "text");
			mysql_query($updateSQL) or die(mysql_error());
			$updateSQL = "INSERT into individupostit (codeindividu,numsejour,codeacteur,postit) values (".
										GetSQLValueString($codeindividu, "text").",".
										GetSQLValueString($numsejour, "text").",".
										GetSQLValueString($codeuser, "text").",".
										GetSQLValueString($_POST['postit'], "text").")";
			mysql_query($updateSQL) or die(mysql_error());
		}
	}//fin if($erreur=="")
}

/* }
catch(Exception $e)
{ ?> Erreur systeme durant l'enregistrement de donnees : <?php echo $e ?>
<?php 
} */

// ------------------------------  TRAITEMENT DE L'ECRAN D'AFFICHAGE DU FORMULAIRE DE SAISIE

$query_rs_individu ="SELECT individu.*,individusejour.*,civilite.libcourt_fr as libciv".
										" FROM civilite,individu,individusejour".
										" WHERE individu.codeciv=civilite.codeciv".
										" and individu.codeindividu=individusejour.codeindividu".
										" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text").
										" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");

$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_individu = mysql_fetch_assoc($rs_individu);

//postit perso
$query_rs_individupostit ="SELECT codeacteur,postit, nom, prenom FROM individu,individupostit".
													" WHERE individupostit.codeacteur=individu.codeindividu".
													" and individupostit.codeindividu=".GetSQLValueString($codeindividu, "text").
													" and numsejour=".GetSQLValueString($numsejour, "text").
													" and codeacteur= ".GetSQLValueString($codeuser, "text");
$rs_individupostit = mysql_query($query_rs_individupostit) or die(mysql_error());
if(!$row_rs_individupostit=mysql_fetch_assoc($rs_individupostit))//pas de postit : on precomplete les champs affiches
{ $row_rs_individupostit['codeacteur']=$codeuser;
	$row_rs_individupostit['nom']=$tab_infouser['nom'];
	$row_rs_individupostit['prenom']=$tab_infouser['prenom'];
	$row_rs_individupostit['postit']='';
}

if($erreur!="")
{	$rs_fields = mysql_query('SHOW COLUMNS FROM individu');
	while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
	{ $Field=$row_rs_fields['Field'];
		if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'])))
		{ if(in_array($Field, $tab_champs_ouinon)===false)
			{ if(array_key_exists($Field,$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
				{ $row_rs_individu[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
				}
				else
				{ $row_rs_individu[$Field]=$_POST[$Field];
				}
			}
			else
			{ $row_rs_individu[$Field]='oui';
			}
		}
		else
		{ if(in_array($Field, $tab_champs_ouinon)!==false)// champs oui/non pour lesquels $_POST n'est pas défini (reçu)
			{ $row_rs_individu[$Field]='non';
			}
		}
	}
	if(isset($_POST['postit']))
	{ $row_rs_individupostit['postit']=$_POST['postit'];
	}
}

	

// ----------------------- FIN DONNEES DU FORMULAIRE DE SAISIE ------------------------------
$query_rs_lieu = "SELECT * FROM lieu WHERE ".periodeencours('date_deb','date_fin');
$rs_lieu = mysql_query($query_rs_lieu) or die(mysql_error());

$query_rs_pays =" SELECT codepays,libpays,numordre FROM pays where codepays<>''".
								" UNION".
								" SELECT codepays,'[ Choix obligatoire ]' as libpays,numordre".
								" FROM pays where codepays=''". 
								" order by numordre asc,codepays asc";
$rs_pays = mysql_query($query_rs_pays) or die(mysql_error());
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Membre <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
</head>
<body <?php if($erreur!=''){?>onLoad="alert('<?php echo str_replace("<br>","\\n", str_replace("'","&rsquo;",$erreur)) ?>')"<?php }?>>
<form name="<?php echo $form_fiche_dossier_pers ?>" id="<?php echo $form_fiche_dossier_pers ?>" method="POST" action="<?php echo $editFormAction ?>" target="_self" onSubmit="return controle_form_fiche_dossier_pers('<?php echo $form_fiche_dossier_pers ?>','')">
<input type="hidden" name="MM_update" value="<?php echo $form_fiche_dossier_pers ?>">
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre ?>">
<input type="hidden" name="codeindividu" value="<?php echo $row_rs_individu['codeindividu']; ?>">
<input type="hidden" name="numsejour" value="<?php echo $row_rs_individu['numsejour']; ?>">
<input type="hidden" name="etat_individu" value="<?php echo $etat_individu ?>">

<table border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td>
     <table width="100%" border="0" cellpadding="0" cellspacing="1">
      <tr>
        <td width="21%"><img src="<?php echo $GLOBALS['logolabo'] ?>" alt="">
        </td>
        <td width="63%">
     			<table border="1" align="center" cellpadding="10" bordercolor="#0000FF" class="table_entete">
           <tr>
            <td align="center" nowrap>
              <span class="bleugrascalibri11"><img src="images/b_couple.png" width="16" height="14"> FICHE </span><span class="mauvegrascalibri11"><?php echo $etat_individu_entete[$etat_individu] ?></span><br>
              <span class="bleugrascalibri10">(&agrave; usage strictement interne <?php echo $GLOBALS['acronymelabo'] ?>)</span>
            </td>
           </tr>
        	</table>
        </td>
        <td width="16%"><img src="images/espaceur.gif" width="61" height="1">
        </td>
    	</tr>
    </table>
   </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
     <table border="0" cellpadding="0" cellspacing="1">
      <tr>
          <td valign="top"><img src="images/b_precedent.png" width="16" height="16"></a></td>
          <td valign="top" nowrap>
          <a href="gestionindividus.php?ind_ancre=<?php echo $ind_ancre ?>" target="_self" 
          onClick="return confirm('Les dernieres informations non enregistr&eacute;es ne seront pas pas prises compte.\nVoulez-vous vraiment quitter ?')">
          <span class="bleugrascalibri11">Retour &agrave; la gestion des dossiers individuels <?php echo $GLOBALS['acronymelabo'] ?></span>
          </a>
					<span class="rougecalibri9"> (Attention : les donn&eacute;es non enregistr&eacute;es seront perdues ! )</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
			<?php echo tablehtml_info_user($tab_infouser,$tab_roleuser)?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;
    </td>
  </tr>
  <tr>
    <td>
			<?php 
      if($erreur!='' || $affiche_succes)
      { ?>
      <table border="0" align="center" cellspacing="5" >
        <tr>
          <?php 
          if($erreur!='')
          { ?>
          <td valign="top"><img src="images/attention.png" align="top"></td>
          <td valign="top">
            <span class="rougecalibri9">L&rsquo;enregistrement n&rsquo;a pas &eacute;t&eacute; effectu&eacute;. Veuillez corriger les erreurs.</span>
            <span class="rougecalibri9"><?php echo $erreur ?></span>
          </td>
          <?php 
          } 
          else if($affiche_succes)
          { ?>
          <td valign="top"><img src="images/succes.png" align="top"></td>
          <td valign="top">
            <span class="vertcalibri11">Enregistrement effectu&eacute; avec succ&egrave;s.</span>
          	<?php 
						if($warning!="")
            { echo $warning;
            }?>
          </td>
          <?php 
          } ?> 
        </tr>
      </table>
      <?php 
    	}?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
     <table width="100%" border="0" cellpadding="0">
      <tr>
        <td>
          <table width="100%" border="0" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
            <tr>
              <td>
                <table border="0" cellpadding="2" cellspacing="2">
                  <tr>
                    <td nowrap>
                      <span class="mauvegrascalibri11"><?php echo $row_rs_individu['libciv']; ?> <?php echo $row_rs_individu['nom']; ?> <?php echo $row_rs_individu['prenom']; ?><?php if($row_rs_individu['nomjf']!='') { ?>  n&eacute;e <?php echo $row_rs_individu['nomjf'];} ?></span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
        <tr>
          <td>
            <table  border="0" cellpadding="2" cellspacing="2">
              <tr>
              	<td>
                	<table>
                  	<tr>
                      <td valign="top" nowrap>
                        <span class="bleugrascalibri10">T&eacute;l. labo. :</span></td>
                      <td>
                        <input name="tel" type="text" class="noircalibri10" id="tel" value="<?php echo $row_rs_individu['tel']; ?>" size="20" maxlength="20">
                       <img src="images/b_effacer_zone.png" align="absbottom" onClick="document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['tel'].value=''" title="Effacer le n&deg;">
                      </td>
                      <td valign="top" nowrap>
                        <span class="bleugrascalibri10">Autre t&eacute;l. labo. :</span></td>
                      <td>
                        <input name="autretel" type="text" class="noircalibri10" id="autretel" value="<?php echo $row_rs_individu['autretel']; ?>" size="20" maxlength="20">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">T&eacute;l. portable :<span class="bleucalibri9">(opt. missions)</span></span>
                      </td>
                      <td><input name="telport" type="text" class="noircalibri10" id="telport" value="<?php echo $row_rs_individu['telport']; ?>" size="20" maxlength="20">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">Fax :</span>
                      </td>
                      <td><input name="fax" type="text" class="noircalibri10" id="fax" value="<?php echo $row_rs_individu['fax']; ?>" size="20" maxlength="20">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
              	<td>
                	<table>
                  	<tr>
                      <td nowrap><span class="bleugrascalibri10">Mail<sup><span class="champoblig">*</span></sup> :</span></td>
                      <td>
                        <input name="email" type="text" class="noircalibri10" id="email" value="<?php echo $row_rs_individu['email']; ?>" size="40" maxlength="100">
                      </td>
                      <td valign="top"><span class="bleugrascalibri10">Mail&nbsp;:</span><span class="bleucalibri9">(apr&egrave;s le d&eacute;part)</span>
                      </td>
                      <td valign="top"><input name="email_parti" type="text" class="noircalibri10" id="email_parti" value="<?php echo $row_rs_individu['email_parti']; ?>" size="40" maxlength="100">
                      </td>
                      <td colspan="2">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
          	</table>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td valign="top" nowrap><span class="bleugrascalibri10">Adresse pers. :</span><br>
                  <span class="bleucalibri9">(</span><span id="adresse_pers#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['adresse_pers']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td><textarea name="adresse_pers" cols="30" rows="2" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_pers#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['adresse_pers']; ?></textarea></td>
                <td valign="top" colspan="2">
                  <table width="100%">
                    <tr>
                    	<td nowrap>
											<span class="bleugrascalibri10">Code postal</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                      <input name="codepostal_pers" type="text" class="noircalibri10" id="codepostal_pers" value="<?php echo htmlspecialchars($row_rs_individu['codepostal_pers']); ?>" size="6" maxlength="20">
                      </td>
                      <td nowrap>
                      <span class="bleugrascalibri10">Ville</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                      <input name="ville_pers" type="text" class="noircalibri10" id="ville_pers" value="<?php echo htmlspecialchars($row_rs_individu['ville_pers']); ?>" size="30" maxlength="50">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">Pays</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span>
                        <select name="codepays_pers" class="noircalibri10" id="codepays_pers">
                          <?php
                          mysql_data_seek ($rs_pays,0);
                          while ($row_rs_pays = mysql_fetch_assoc($rs_pays)) 
                          {	?>
                            <option value="<?php echo $row_rs_pays['codepays']?>" <?php echo $row_rs_pays['codepays']==$row_rs_individu['codepays_pers']?'selected':''?>><?php echo substr($row_rs_pays['libpays'],0,20)?></option>
                          <?php
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td valign="top" nowrap><span class="bleugrascalibri10">Adresse admin. :</span><br>
                  <span class="bleucalibri9">(</span><span id="adresse_admin#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['adresse_admin']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td><textarea name="adresse_admin" cols="30" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_admin#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['adresse_admin']; ?></textarea></td>
                <td valign="top" colspan="2">
                  <table>
                    <tr>
                      <td class="bleugrascalibri10">Badge :&nbsp;</td>
                      <td><input name="num_badge_acces" type="text" class="noircalibri10" id="num_badge_acces" value="<?php echo $row_rs_individu['num_badge_acces']; ?>" size="20" maxlength="30"> 
                      </td>
                      <td class="bleugrascalibri10">Rendu :&nbsp;
                      </td>
                      <td><input type="checkbox" name="badge_acces_estrendu" id="badge_acces_estrendu" <?php echo ($row_rs_individu['badge_acces_estrendu']=="oui"?"checked":""); ?>>
                      </td>
                    </tr>
                    <tr>
                      <td class="bleugrascalibri10">N° bureau :&nbsp;</td>
                      <td><input name="num_bureau" type="text" class="noircalibri10" id="num_bureau" value="<?php echo $row_rs_individu['num_bureau']; ?>" size="20" maxlength="30">
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td class="bleugrascalibri10">Cl&eacute; :&nbsp;</td>
                      <td><input name="num_cle" type="text" class="noircalibri10" id="num_cle" value="<?php echo $row_rs_individu['num_cle']; ?>" size="20" maxlength="30">
                      </td>
                      <td class="bleugrascalibri10">Rendu :&nbsp;
                      </td>
                      <td><input type="checkbox" name="cle_estrendu" id="cle_estrendu" <?php echo ($row_rs_individu['cle_estrendu']=="oui"?"checked":""); ?>>
                      </td>
                    </tr>
                  </table>
                 </td>
              </tr>
              <tr>
                <td width="11%" valign="top" nowrap><span class="bleugrascalibri10">Notes partag&eacute;es : </span><br>
                  (<span id="note#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['note']) ?></span><span class="bleucalibri9">/6000 car. max.)</span></td>
                <td width="24%" nowrap><textarea name="note" cols="40" rows="3" class="noircalibri10" <?php echo affiche_longueur_js("this","6000","'note#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['note']; ?></textarea></td>
                <td width="5%" valign="top" nowrap><span class="bleucalibri9">PostIt (<?php echo $row_rs_individupostit['prenom']; ?>)&nbsp;:</span><br>
                  (<span id="postit#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individupostit['postit']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td width="60%"><textarea name="postit" cols="40" rows="3" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'postit#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individupostit['postit']; ?></textarea></td>
               </tr>
            </table>
          </td>
        </tr>
			</table>
			</td>
    </tr>
    <tr>
      <td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer" ></td>
    </tr>
	</table>
</form>
</body>
</html>
<?php
if(isset($rs_individupostit)) mysql_free_result($rs_individupostit);
if(isset($rs_individu)) mysql_free_result($rs_individu);
if(isset($rs)) mysql_free_result($rs);
?>
