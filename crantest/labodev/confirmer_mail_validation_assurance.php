<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
//if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");
$erreur="";
$warning="";
$erreur_envoimail="";
$message_resultat_affiche="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$form_confirmer_mail_validation_assurance="confirmer_mail_validation_assurance";
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,false,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];


if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_confirmer_mail_validation_assurance)) 
{	$tab_controle_et_format=array(); 
	//$erreur=controle_form_confirmer_mail_validation_assurance($_POST,$tab_controle_et_format);
	if($erreur=="")
	{	$affiche_succes=true;
		$query_rs_mission="select * from mission where codemission=".GetSQLValueString($codemission, "text"); 
		$rs_mission=mysql_query($query_rs_mission) or die(mysql_error());
		$row_rs_mission=mysql_fetch_assoc($rs_mission);
		if(isset($_POST['b_valider_x']))
		{ $erreur_envoimail=mail_validation_mission_assurance($row_rs_mission,$codeuser,$_POST);
			if($erreur_envoimail!="")
			{ $warning="Echec d&rsquo;envoi du mail d&rsquo;assurance pour la mission ".$codemission;
				$erreur="Validation non effectu&eacute;e.";
				$affiche_succes=false;
			}
			else
			{ $updateSQL ="update mission set assureetranger='E' where codemission=".GetSQLValueString($codemission, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
				$message_resultat_affiche="Validation effectu&eacute;e.";
				http_redirect('gestioncommandes.php?cmd_ancre='.$cmd_ancre);
			}
		}
	}
}
// -- Formulaire d'envoi des donnees
//Informations de mission (un enreg. vide dans mission pour "creer")
$query_mission =	"SELECT mission.*".
									" FROM mission".
									" WHERE codemission=".GetSQLValueString($codemission,"text");
$rs_mission=mysql_query($query_mission) or die(mysql_error());
$row_rs_mission=mysql_fetch_assoc($rs_mission);

if($row_rs_mission['codeagent']!='')//si '' alors les données sont dans table mission
{ $query_rs_agent="SELECT distinct individu.codeindividu as codeagent,nom,prenom,date_naiss,adresse_pers,adresse_admin,".
								" telport as tel,email,datedeb_sejour,codeetab,if(cat.codecat='04' or cat.codecat='05' or cat.codecat='07','oui','non') as estetudiant,miss_catmissionnaire.codecatmissionnaire as codecatmissionnaire,".
								" miss_catmissionnaire.libcourt as libcatmissionnaire". 
								" FROM individu,individusejour,individuemploi,corps,cat,miss_catmissionnaire".
								" where individu.codeindividu=individusejour.codeindividu and individu.codeindividu=individuemploi.codeindividu".
								" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat". 
								" and corps.codecatmissionnaire=miss_catmissionnaire.codecatmissionnaire and corps.codecatmissionnaire<>'' and ".periodeencours('datedeb_sejour','datefin_sejour').
								" and ".periodeencours('datedeb_emploi','datefin_emploi')." and individu.codeindividu=".GetSQLValueString($row_rs_mission['codeagent'], "text");
	$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
	$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs_agent));
}
// Liste des etapes
$tab_missionetape=array();//raz du tableau ou creation 
$rs=mysql_query("SELECT * from missionetape".
								" where missionetape.codemission=".GetSQLValueString($codemission, "text").
								" order by departdate,departheure") or die(mysql_error());
$nbetape=mysql_num_rows($rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_missionetape[$row_rs['numetape']]=$row_rs;
}
//infos du user
$query_rs_user= "select tel,fax,email,lieu.liblonglieu as liblieu from individu,lieu ".
								" where codeindividu=".GetSQLValueString($codeuser, "text").
								" and individu.codelieu=lieu.codelieu";
$rs_user=mysql_query($query_rs_user) or die(mysql_error());
$row_rs_user=mysql_fetch_assoc($rs_user);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Validation mission &eacute;tranger</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace("<br>","\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?"\\n":'').str_replace("<br>","\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>

<table border="0" align="center" cellpadding="0" cellspacing="1">
 <form name="<?php echo $form_confirmer_mail_validation_assurance ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="//return controle_form_confirmer_mail_validation_assurance('<?php echo $form_confirmer_mail_validation_assurance ?>')">
  <input type="hidden" name="MM_update" value="<?php echo $form_confirmer_mail_validation_assurance ?>">
  <input type="hidden" name="codemission" value="<?php echo $codemission; ?>">
  <input type="hidden" name="cmd_ou_miss" value="mission">
 	<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
	<?php echo entete_page(array('image'=>'images/b_avion.png','titrepage'=>'Assurance Mission','lienretour'=>'gestioncommandes.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Annuler et revenir &agrave; la gestion des commandes',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>$warning,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
	<tr>
    <td align="center"><table>
      <tr>
        <td align="left" nowrap class="noircalibri10"><table class="table_rectangle_bleu" width="80%">
          <tr>
            <td colspan="2" align="center" class="noircalibri10"><b>Mail pour l'assurance</b></td>
          </tr>
          <tr>
            <td colspan="2" align="center" class="mauvecalibri10">
            	Modifiez les valeurs des champs si n&eacute;cessaire : 
              les valeurs modifi&eacute;es ne sont ni contr&ocirc;l&eacute;es ni conserv&eacute;es et servent uniquement &agrave; l&rsquo;envoi de ce mail</td>
          </tr>
          <tr>
            <td align="left" class="noircalibri10">&nbsp;</td>
            <td align="left" class="noircalibri10">&nbsp;</td>
          <tr>
            <td align="left" class="noircalibri10"><b>Destinataire : </b><?php echo $GLOBALS['assurancemission_contact_ul_1']['prenomnom'] ?> (<?php echo $GLOBALS['assurancemission_contact_ul_1']['email'] ?>)</td>
            <td align="left" class="noircalibri10"></td>
          </tr>
          </tr>
          <tr>
            <td align="left" class="noircalibri10"><b>Objet</b> : Demande d'attestation d'assurance (<?php echo $row_rs_mission['prenom'] ?> <?php echo $row_rs_mission['nom'] ?>)</td>
            <td align="left" class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="center" class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="left" class="noircalibri10">Madame, Monsieur,</td>
          </tr>
          <tr>
            <td colspan="2" align="left" class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="left" class="noircalibri10">par le pr&eacute;sent mail, je souhaite vous informer du d&eacute;placement de :</td>
          </tr>
          <tr>
            <td colspan="2" align="left" class="noircalibri10"><table width="100%" border="0">
              <tr>
                <td nowrap class="noircalibri10"><b>Pr&eacute;nom - Nom :</b></td>
                <td nowrap class="noircalibri10"><?php echo $row_rs_mission['prenom'] ?> <?php echo $row_rs_mission['nom'] ?></td>
              </tr>
              <tr>
                <td nowrap class="noircalibri10"><b>Mail :</b></td>
                <td nowrap class="noircalibri10"><?php echo $row_rs_mission['email'] ?></td>
              </tr>
              <tr>
                <td nowrap class="noircalibri10"><b>Ville - Pays<sup><span class="champoblig">*</span></sup> :</b></td>
                <td nowrap><input name="arriveelieu" class="noircalibri10" value="<?php echo $tab_missionetape['01']['arriveelieu'] ?>" type="text" id="departlieu" size="100" maxlength="100"></td>
              </tr>
              <tr>
                <td nowrap class="noircalibri10"><b>D&eacute;placement effectu&eacute;<sup><span class="champoblig">*</span></sup> :</b></td>
                <td nowrap><input name="deplacement" class="noircalibri10" value="<?php echo $tab_missionetape['01']['arriveelieu'] ?>" type="text" id="deplacement" size="100" maxlength="100"></td>
              </tr>
              <tr>
                <td nowrap class="noircalibri10"><b>Date de d&eacute;part<sup><span class="champoblig">*</span></sup> :</b></td>
                <td nowrap><input name="departdate" class="noircalibri10" value="<?php echo aaaammjj2jjmmaaaa($tab_missionetape['01']['departdate'],'/') ?>" type="text" id="departdate" size="10" maxlength="10"></td>
              </tr>
              <tr>
              	<?php $row_missionetape=end($tab_missionetape);?>
                <td nowrap class="noircalibri10"><b>Date de retour<sup><span class="champoblig">*</span></sup> :</b></td>
                <td nowrap><input name="arriveedate" class="noircalibri10" type="text" id="arriveedate" value="<?php echo aaaammjj2jjmmaaaa($row_missionetape['arriveedate'],'/'); ?>" size="10" maxlength="10"></td>
              </tr>
          </table></td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">Je vous remercie de bien vouloir m&rsquo;adresser l&rsquo;attestation d&rsquo;assurance par retour de messagerie.</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">Cordialement,</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10"><?php echo $tab_infouser['prenom'].' '.$tab_infouser['nom']?></td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">--</td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10"><?php echo $GLOBALS['acronymelabo'] ?></td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10"><?php echo $row_rs_user['liblieu'] ?></td>
          </tr>
         <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">T&eacute;l. : <?php echo $row_rs_user['tel'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax : <?php echo $row_rs_user['fax'] ?></td>
          </tr>
          <tr>
            <td colspan="2" align="left" nowrap class="noircalibri10">Mail : <?php echo $row_rs_user['email'] ?></td>
          </tr>
         </table>
        </td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center">
      <table>
        <tr>
          <td>
        <input type="image" name="b_valider" class="icon" src="images/b_confirmer.png">
        </form>
        
        <form method="get" action="gestioncommandes.php">
 					<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
          <input type="image" name="annuler" class="icon" src="images/b_annuler.png">
        </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php
if(isset($rs_user)) mysql_free_result($rs_user);
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_mission)) mysql_free_result($rs_mission);
?>
</body>
</html>
