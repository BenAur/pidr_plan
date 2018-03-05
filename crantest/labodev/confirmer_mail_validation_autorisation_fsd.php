<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_destinataires=array();
$tab_mail_unique=array();
if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$action='valider';
$codevisa_a_apposer=isset($_GET['codevisa_a_apposer'])?$_GET['codevisa_a_apposer']:(isset($_POST['codevisa_a_apposer'])?$_POST['codevisa_a_apposer']:"");
$demander_autorisation=isset($_GET['demander_autorisation'])?$_GET['demander_autorisation']:(isset($_POST['demander_autorisation'])?$_POST['demander_autorisation']:"");

$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");

$erreur="";
$warning="";
$erreur_envoimail="";
$message_resultat_affiche="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$form="confirmer_mail_validation_autorisation_fsd";
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

$tab_champs_date=array('date_autorisation'  =>  array("lib" => "Date autorisation","jj" => "","mm" => "","aaaa" => ""));
if((isset($_POST["MM_update"])) && $_POST["MM_update"] == $_SERVER['PHP_SELF']) 
{	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date);
	$erreur=controle_form_confirmer_mail_validation_autorisation_fsd($_POST,$tab_controle_et_format);
	if($erreur=="")
	{	$affiche_succes=true;
		if(isset($_POST['b_valider_x']))
		{ $query_rs_ind="select civilite.libcourt_fr as libciv,individu.*,individusejour.*,".
										" corps.liblongcorps_fr as libcorps, cat.codelibcat,codelibtypestage,sujetstageobligatoire,lieu.libcourtlieu as liblieu,sujet.codesujet,sujet.codestatutsujet,sujet.titre_fr as titresujet".
										" from civilite,lieu,individu,corps,cat,typestage,individusejour".
										" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
										" left join sujet on individusujet.codesujet=sujet.codesujet". 
										" where individu.codeciv=civilite.codeciv".
										" and individu.codeindividu=individusejour.codeindividu".
										" and individu.codelieu=lieu.codelieu". 
										" and individusejour.codecorps=corps.codecorps". 
										" and corps.codecat=cat.codecat". 
										" and individusejour.codetypestage=typestage.codetypestage".
										" and individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
										" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
			$rs_ind=mysql_query($query_rs_ind) or die(mysql_error());
			$row_rs_ind=mysql_fetch_assoc($rs_ind);
			$rs=mysql_query("SELECT libcourt_fr from individusejour,individutheme,structure".
											" WHERE individusejour.codeindividu=individutheme.codeindividu AND individusejour.numsejour=individutheme.numsejour".
											" AND individutheme.codetheme=structure.codestructure".
											" AND individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
											" AND individutheme.numsejour=".GetSQLValueString($numsejour, "text"));
			$row_rs_ind['theme']="";
			$first=true;
			while($row_rs = mysql_fetch_assoc($rs))
			{ $row_rs_ind['theme'].=($first?"":",").$row_rs['libcourt_fr'];
				$first=false;
			}
			$row_rs_ind['demander_autorisation']=(isset($_POST['demander_autorisation']) && $_POST['demander_autorisation']=='oui');

			$erreur_envoimail=mail_validation_individu($row_rs_ind,$codeuser,$codevisa_a_apposer);
			mysql_free_result($rs_ind);
			
			if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
			{ $warning="Echec d&rsquo;envoi du mail pour l'autorisation du dossier ".$codeindividu.".".$numsejour;
				$erreur="Validation non effectu&eacute;e.";
				$affiche_succes=false;
			}
			else
			{ // suppression de la ligne avec $codeuser pour cet individu $codeindividu pour le role si elle existe
				$updateSQL ="delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text").
										" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text").
										" and numsejour=".GetSQLValueString($numsejour, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
			
				$updateSQL = "INSERT into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) values (".
											GetSQLValueString($codeindividu, "text").",".
											GetSQLValueString($numsejour, "text").",".
											GetSQLValueString($codevisa_a_apposer, "text").",".
											GetSQLValueString($codeuser, "text").",".
											GetSQLValueString($aujourdhui, "text").						
											")";
				mysql_query($updateSQL) or die(mysql_error());
				$updateSQL ="update individusejour set date_autorisation=".GetSQLValueString($_POST['date_autorisation'], "text").
										" where codeindividu=".GetSQLValueString($codeindividu, "text").
										" and numsejour=".GetSQLValueString($numsejour, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
				http_redirect('gestionindividus.php?ind_ancre='.$ind_ancre);
			}
		}
	}
}
$query_rs_individu=	"select civilite.libcourt_fr as libciv_fr,individu.*,individusejour.* from individu,individusejour,civilite".
										" where individu.codeciv=civilite.codeciv".
										" and individu.codeindividu=individusejour.codeindividu".
										" and individu.codeindividu=".GetSQLValueString($codeindividu,"text").
										" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_individu=mysql_fetch_assoc($rs_individu);
if($row_rs_individu['date_autorisation']=='')
{ $row_rs_individu['date_autorisation']=$aujourdhui;
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Validation autorisation FSD</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>
>
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Demande FSD','lienretour'=>'gestionindividus.php?ind_ancre='.$ind_ancre,'texteretour'=>'Retour &agrave; la gestion des dossiers des personnels',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'dossier '.$codeindividu.'.'.$numsejour,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
		<td>&nbsp;
    </td>
	</tr>
	<tr>
    <td align="center">
    	<table class="table_gris_encadre" width="80%">
      	<tr>
        	<td>
      			<?php echo message_action_individu($row_rs_individu,$codeuser,$tab_roleuser,$action);
						?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="center">
     <form name="<?php echo $form ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="return controle_form_confirmer_mail_validation_autorisation_fsd('<?php echo $form ?>')">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
       <input type="hidden" name="codeindividu" value="<?php echo $codeindividu; ?>">
      <input type="hidden" name="numsejour" value="<?php echo $numsejour; ?>">
      <input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre; ?>">
      <input type="hidden" name="action" value="<?php echo $action ?>">
    	<input type="hidden" name="codevisa_a_apposer" value="<?php echo $codevisa_a_apposer; ?>">
      <input type="hidden" name="demander_autorisation" value="<?php echo $demander_autorisation; ?>">
      <span class="bleugrascalibri11">Date d'autorisation </span><input name="date_autorisation_jj" type="text" class="noircalibri10" id="date_autorisation_jj" value="<?php echo substr($row_rs_individu['date_autorisation'],8,2); ?>" size="2" maxlength="2">
      <input name="date_autorisation_mm" type="text" class="noircalibri10" id="date_autorisation_mm" value="<?php echo substr($row_rs_individu['date_autorisation'],5,2); ?>" size="2" maxlength="2">
      <input name="date_autorisation_aaaa" type="text" class="noircalibri10" id="date_autorisation_aaaa" value="<?php echo substr($row_rs_individu['date_autorisation'],0,4); ?>" size="4" maxlength="4">
      <input type="image" name="b_valider" class="icon" src="images/b_confirmer.png">
      </form>
      
      <form method="get" action="gestionindividus.php">
        <input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre; ?>">
        <input type="image" name="annuler" class="icon" src="images/b_annuler.png">
      </form>
      
		</td>
  </tr>
  <tr>
    <td align="center">
      <table>
        <tr>
          <td>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php
?>
</body>
</html>
