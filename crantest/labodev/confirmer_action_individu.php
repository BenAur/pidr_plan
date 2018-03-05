<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}
	/*foreach($_GET as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}

$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");

//$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");

// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$form="gestionindividus";

if($action=="invalider")
{ $message_confirme="Confirmation d&rsquo;invalidation";
	$name="b_invalider";
}
else if($action=="supprimer")
{ //si un seul sejour => suppression de l'individu sinon le sejour concerne
	$rs=mysql_query("select count(*) as nbsejour from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
  $row_rs=mysql_fetch_assoc($rs);
	$nbsejour=$row_rs['nbsejour'];
	$explication_non_suppr="";
	// 20170617 
	$rs=mysql_query("select codeindividu from structureindividu where codeindividu=".GetSQLValueString($codeindividu, "text"))  or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Occupe une fonction dans la structure';
	}
	$rs=mysql_query("select valeur as codeindividu from progacces where critere='codeindividu' and valeur=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Droits sur des programmes particuliers';
	}
	// 20170617 
	// gestionnaire de theme : non supprimable
	$rs=mysql_query("select codegesttheme from gesttheme where codegesttheme=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Gestionnaire';
	}
	// secr site : non supprimable
	$rs=mysql_query("select codesecrsite from secrsite where codesecrsite=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- secr&eacute;tariat d&rsquo;appui';
	}
	// secr site : non supprimable pour mission
	$rs=mysql_query("select codesecrsite from mission where codesecrsite=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- secr&eacute;tariat d&rsquo;appui de mission';
	}
	
	// secr site : non supprimable pour commande
	$rs=mysql_query("select codesecrsite from commande where codesecrsite=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- secr&eacute;tariat d&rsquo;appui de commande';
	}
	// encadrant ou createur de sujet
	$rs=mysql_query("select codedir from sujetdir where codedir=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Encadrant';
	}	
	
	// createur d'au moins un sujet : non supprimable
	$rs=mysql_query("select codecreateur from sujet where codecreateur=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Cr&eacute;ateur de sujet';
	}
	// doctorant d'un contrat : non supprimable
	$rs=mysql_query("select codedoctorant,codecontrat from contrat where codedoctorant=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Li&eacute; au contrat '.$row_rs['codecontrat'];
	}
	// responsable d'un contrat : non supprimable
	$rs=mysql_query("select coderespscientifique from contrat where coderespscientifique=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Responsable de contrat';
	}
	
	// missionnaire : non supprimable sauf si plus d'un sejour. Pas genant si le le missionnaire est deselectionne puis reselectionne dans la mission car ce sont les dates de sejour pendant la mission
	// qui donnent les informations du missionnaire.
	$rs=mysql_query("select codeagent from mission where codeagent=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Agent missionnaire';
	}
	// demandeur de commande : non supprimable sauf si plus d'un sejour.
	$rs=mysql_query("select codereferent from commande where codereferent=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs) && $nbsejour<=1)
	{ $explication_non_suppr.='<br>- Demandeur de commande';
	}
	// sujet verrouille pour un doctorant
	$rs=mysql_query("select codeindividu,numsejour from individuthese where sujet_verrouille='oui' ".
									" and codeindividu=".GetSQLValueString($codeindividu, "text").
									" and numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $explication_non_suppr.='<br>- Sujet de th&egrave;se verrouill&eacute;';
	}

	$message_confirme="Confirmation de suppression";
	$name="b_supprimer"; 
}
$query_individu="select civilite.libcourt_fr as libciv_fr,individu.*,individusejour.*,".
								" corps.liblongcorps_fr as libcorps, cat.codelibcat,lieu.libcourtlieu as liblieu,sujet.titre_fr as titresujet".
								" from civilite,lieu,individu,corps,cat,individusejour".
								" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
								" left join sujet on individusujet.codesujet=sujet.codesujet". 
								" where individu.codeciv=civilite.codeciv".
								" and individu.codeindividu=individusejour.codeindividu".
								" and individu.codelieu=lieu.codelieu". 
								" and individusejour.codecorps=corps.codecorps". 
								" and corps.codecat=cat.codecat". 
								" and individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
								" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
$rs_individu=mysql_query($query_individu) or die(mysql_error());
$row_rs_individu=mysql_fetch_assoc($rs_individu);

if(isset($rs)) mysql_free_result($rs);
if(isset($rs_individu)) mysql_free_result($rs_individu);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_couple.png','titrepage'=>'Gestion des personnels','lienretour'=>'gestionindividus.php'/* ?ind_ancre=.$ind_ancre */,'texteretour'=>'Annuler et revenir &agrave; la gestion des personnels',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
	<tr>
    <td align="center">
    	<table class="table_gris_encadre" width="80%">
      	<tr>
        	<td>
      			<?php 
						if($action!="supprimer" || ($action=="supprimer" && $explication_non_suppr==""))
          	{ echo message_action_individu($row_rs_individu,$codeuser,$tab_roleuser,$action);
						}
						else
						{ ?> La suppression du s&eacute;jour de <?php echo $row_rs_individu['nom']." ".$row_rs_individu['prenom'] ?> n&rsquo;est pas possible : <?php echo $explication_non_suppr ?>
						<?php 
						}?>
						
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
      <table>
        <tr>
          <td align="center">
            <?php 
						if($action!="supprimer" || ($action=="supprimer" && $explication_non_suppr==""))
          	{ ?><form name="<?php echo $form ?>" method="post" action="gestionindividus.php" onSubmit="return controle_form_confirmer_action_individu('<?php echo $form ?>')">
              <input type="hidden" name="MM_update" value="<?php echo substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strlen('confirmer_action_individu.php')).'gestionindividus.php' ?>">
              <input type="hidden" name="codeindividu" value="<?php echo $codeindividu; ?>">
              <input type="hidden" name="numsejour" value="<?php echo $row_rs_individu['numsejour']; ?>">
              <!--<input type="hidden" name="ind_ancre" value="<?php //echo $ind_ancre ?>"> -->
              <input type="hidden" name="action" value="<?php echo $action ?>">
          </td>
        </tr>
        <tr>
          <td align="center">
            	<span class="bleugrascalibri11">Envoyer un mail aux destinataires</span><input name="envoyer_mail" type="checkbox" checked>
              <input type="image" name="<?php echo $name ?>" class="icon" src="images/b_confirmer.png">
            </form>
            <?php 
						}?>
            <form name="<?php echo $form ?>" method="post" action="gestionindividus.php">
              <input type="image" name="annuler" class="icon" src="images/b_annuler.png">
              <!--<input type="hidden" name="ind_ancre" value="<?php //echo $ind_ancre ?>"> -->
          	</form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
	<tr>
    <td align="center">
      <?php echo detailindividu($codeindividu,$numsejour,$codeuser)?>
    </td>
  </tr>
	<tr>
  </tr>
</table>
</body>
</html>
