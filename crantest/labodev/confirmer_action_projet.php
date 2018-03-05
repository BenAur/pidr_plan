<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
//if($admin_bd)
{/* foreach($_REQUEST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estadmin=array_key_exists('du',$tab_roleuser) || array_key_exists('admingestfin',$tab_roleuser) || array_key_exists('gestprojet',$tab_roleuser) || $admin_bd ;
if(!$estadmin)
{ ?>User non autorisé
<?php
	exit;
}
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");	
$codeprojet=isset($_GET['codeprojet'])?$_GET['codeprojet']:(isset($_POST['codeprojet'])?$_POST['codeprojet']:"");
$projet_ancre=isset($_GET['projet_ancre'])?$_GET['projet_ancre']:(isset($_POST['projet_ancre'])?$_POST['projet_ancre']:"");
$query_rs_projet="select projet.*, individu.email as emailreferent,individu.nom as nomreferent,individu.prenom as prenomreferent  from projet,individu".
										" where projet.codereferent=individu.codeindividu and projet.codeprojet=".GetSQLValueString($codeprojet, "text");
$rs_projet=mysql_query($query_rs_projet) or die(mysql_error());
$row_rs_projet=mysql_fetch_assoc($rs_projet); /* */

if($action=="mail_accompagnement_projet")
{ $nom_bouton_image="b_mail_accompagnement_projet";
	$message_confirme="Confirmation d&rsquo;accompagnement de projet";
}


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
	<?php echo entete_page(array('image'=>'','titrepage'=>$message_confirme,'lienretour'=>'gestionprojets.php?projet_ancre='.$projet_ancre,'texteretour'=>'Annuler et revenir &agrave; la gestion des projets',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
  <tr>
    <td align="center">
    	<table class="table_gris_encadre" width="80%">
       <tr>
        <td class="rougegrascalibri11" align="center">
    			<?php echo $message_confirme ?>
        </td>
      </tr>
      <tr>
        <td align="center" class="noirgrascalibri11"><?php echo $row_rs_projet['titrecourt']!=''?$row_rs_projet['titrecourt']:$row_rs_projet['titre'] ?>
        </td>
      </tr>
     </table>
    </td>
  </tr>
  <tr>
    <td align="center">
      <table>
        <tr>
          <td align="center">
            <form name="gestionprojets_accompagnement_projet" method="post" action="gestionprojets.php">
              <input type="hidden" name="codeprojet" value="<?php echo $codeprojet; ?>">
              <input type="hidden" name="MM_update" value="<?php echo substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strlen('confirmer_action_projet.php')).'gestionprojets.php' ?>">
 							<input type="hidden" name="projet_ancre" value="<?php echo $projet_ancre; ?>">
              <?php 
							if($action=="mail_accompagnement_projet")
              { ?>
              	<input type="hidden" name="action" value="mail_accompagnement_projet">
                <br>Texte du message envoy&eacute; &agrave; <?php echo $row_rs_projet['prenomreferent'].' '.$row_rs_projet['nomreferent'] ?><br>
                <textarea rows="10" cols="80" name="info_accompagnement_projet"></textarea><br>
              <input type="image" name="<?php echo $nom_bouton_image ?>" class="icon" src="images/b_confirmer.png">
              <?php 
							}
							?>
            </form>
            <form name="gestionprojets_annuler" method="post" action="gestionprojets.php">
 							<input type="hidden" name="projet_ancre" value="<?php echo $projet_ancre; ?>">
              <input type="image" name="annuler" class="icon" src="images/b_annuler.png">
            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<?php 
/* if(isset($rs)) mysql_free_result($rs);
if(isset($rs_projet)) mysql_free_result($rs_projet); */
?>
</body>
</html>
