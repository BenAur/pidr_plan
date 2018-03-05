<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$user_non_restreint=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $user_non_restreint) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
//if(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd'])
{/* foreach($_REQUEST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
 // définis par $estreferent et $estresptheme
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,false,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser']; 
/* if(!array_key_exists('admingestfin',$tab_roleuser) && !array_key_exists('du',$tab_roleuser))
{ ?>User non autorisé
<?php
	exit;
} */
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");	
$codecommande=isset($_GET['codecommande'])?$_GET['codecommande']:(isset($_POST['codecommande'])?$_POST['codecommande']:"");
$codevisa_a_apposer=isset($_GET['codevisa_a_apposer'])?$_GET['codevisa_a_apposer']:(isset($_POST['codevisa_a_apposer'])?$_POST['codevisa_a_apposer']:"");
$prog_appel=isset($_GET['prog_appel'])?$_GET['prog_appel']:(isset($_POST['prog_appel'])?$_POST['prog_appel']:"gestioncommandes");// 20170427
$cmd_ou_miss=isset($_GET['cmd_ou_miss'])?$_GET['cmd_ou_miss']:(isset($_POST['cmd_ou_miss'])?$_POST['cmd_ou_miss']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");
// suppression commande
$supprcommande=false;
if($action=="supprimer")
{ $message_confirme="Confirmation de suppression";
	$nom_bouton_image="b_supprimer"; 
}
// invalidation visas
else if($action=="invalider_visa")
{ $message_confirme="Confirmation d&rsquo;invalidation de visa";
	$nom_bouton_image="b_invalider_visa";
}
// mail srh si salaire
else if($action=="valider_mail_srh")
{ $message_confirme="Confirmation de message SRH";
	$nom_bouton_image="b_valider_mail_srh";
}
//visa revalidé apres une invalidation
else if($action=="valider_mail_visa_revalide")
{ $nom_bouton_image="b_valider_mail_visa_revalide";
	$query_rs="select liblong as libstatutvisa from cmd_statutvisa where codestatutvisa=".GetSQLValueString($codevisa_a_apposer,"text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);
	$message_confirme="Confirmation de validation de visa ".$row_rs['libstatutvisa'];
}
else if($action=="mail_relance_missionnaire")
{ $nom_bouton_image="b_mail_relance_missionnaire";
	$message_confirme="Confirmation de relance missionnaire : factures";
}

if($cmd_ou_miss=='commande')
{ $query_rs_commande =	"SELECT numcommande,objet,libfournisseur FROM commande WHERE codecommande=".GetSQLValueString($codecommande,"text");
}
else
{ //$query_rs_commande =	"SELECT email,motif as objet,concat(nom,' ',prenom) as libfournisseur FROM mission WHERE codemission=".GetSQLValueString($codecommande,"text");
	$query_rs_commande="select email,motif as objet,concat(nom,' ',prenom) as libfournisseur, departdate from mission,missionetape".
										" where mission.codemission=".GetSQLValueString($codecommande, "text")." and  mission.codemission=missionetape.codemission and numetape='01'";
}
$rs_commande=mysql_query($query_rs_commande) or die(mysql_error());
$row_rs_commande=mysql_fetch_assoc($rs_commande);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des comandes <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
</head>
<body>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>$message_confirme,'lienretour'=>$prog_appel.'.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Annuler',// 20170427
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
  <tr>
    <td align="center">
    	<table class="table_gris_encadre" width="80%">
       <tr>
        <td class="rougegrascalibri11" align="center">
    			<?php echo $message_confirme ?> de la <?php echo $cmd_ou_miss ?> n&deg; <?php echo $codecommande; ?>
        </td>
      </tr>
     </table>
    </td>
  </tr>
	<tr>
    <td>
      <?php if($cmd_ou_miss=='mission')
      {?>Objet : <?php echo $row_rs_commande['objet'] ?>
        </td>
        </tr>
       <tr>
        <td>
          Fournisseur : <?php echo $row_rs_commande['libfournisseur'].($action=="mail_relance_missionnaire"?' ('.($row_rs_commande['email']==''?"<b>Pas d&rsquo;adesse mail</b>":$row_rs_commande['email']).')':'') ?>
			<?php 
      }
      else
      { echo detailcommande($codecommande, $codeuser);
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
      <table align="center">
        <tr>
          <td align="center">
            <form name="<?php echo $prog_appel ?>" method="post" action="<?php echo $prog_appel ?>.php"><?php // 20170427?>
              <input type="hidden" name="codecommande" value="<?php echo $codecommande; ?>">
              <input type="hidden" name="cmd_ou_miss" value="<?php echo $cmd_ou_miss ?>">
              <?php if($prog_appel=='gestioncommandes')
              {?> <input type="hidden" name="MM_update" value="<?php echo substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strlen('confirmer_action_commande.php')).$prog_appel.'.php' ?>"><?php // 20170427?>
 							<?php }?>
              <input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
              <?php 
							if($action=="valider_mail_srh")
              {?> <input type="hidden" name="codevisa_a_apposer" value="<?php echo $codevisa_a_apposer ?>">
              		<input type="hidden" name="action" value="valider">
              		<span class="bleugrascalibri11">Envoyer un mail au SRH</span><input name="envoyer_mail_srh" type="checkbox">
              <?php 
							}
							else if($action=="valider_mail_visa_revalide")
              {?> <input type="hidden" name="codevisa_a_apposer" value="<?php echo $codevisa_a_apposer ?>">
              		<input type="hidden" name="action" value="valider_mail_visa_revalide">
              		<span class="bleugrascalibri11">Envoyer un mail de validation</span>
                  <input name="envoyer_mail_validation" type="checkbox">
              <?php 
							}
							else if($action=="mail_relance_missionnaire")
              { 
								$info_comp_relance_missionnaire="Bonjour,";

								$info_comp_relance_missionnaire.="<br><br>vous avez effectu&eacute; une mission le ".aaaammjj2jjmmaaaa($row_rs_commande['departdate'],'/')." : ".$row_rs_commande['objet'];
								$info_comp_relance_missionnaire.="<br>Afin de proc&eacute;der au remboursement des d&eacute;penses engag&eacute;es, je vous remercie de me communiquer le d&eacute;tail des".
													" frais et de me retourner les originaux de vos factures.";
								$info_comp_relance_missionnaire.="<br><br>Cordialement,";
								$info_comp_relance_missionnaire.="<br><br>".$tab_infouser['prenom'].' '.$tab_infouser['nom'];?>

              	<input type="hidden" name="action" value="mail_relance_missionnaire">
                Texte du message<br>
                <textarea rows="10" cols="80" name="info_comp_relance_missionnaire"><?php echo str_replace('<br>',chr(13),$info_comp_relance_missionnaire) ?></textarea><br>
              <?php 
							}
							if($action!="mail_relance_missionnaire" || $action=="mail_relance_missionnaire" && $row_rs_commande['email']!='')
							{?>
              <input type="image" name="<?php echo $nom_bouton_image ?>" class="icon" src="images/b_confirmer.png">
              <?php 
							}?>
            </form>
            <form name="<?php echo $prog_appel ?>" method="post" action="<?php echo $prog_appel ?>.php"><?php // 20170427?>
              <?php if($prog_appel=='edit_commande') // 20170427?
              {?> 
              <input type="hidden" name="codecommande" value="<?php echo $codecommande; ?>">
              <input type="hidden" name="action" value="modifier">
              <?php 
							}?>
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
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_commande)) mysql_free_result($rs_commande);
?>
</body>
</html>
