<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
//if($admin_bd)
{/* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if(!array_key_exists('admingestfin',$tab_roleuser) && !array_key_exists('du',$tab_roleuser) && !$admin_bd)
{ ?>User non autorisé
<?php
	exit;
}
$action="";		
$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
if($action=='supprimer')
{ $message_confirme="Confirmation de suppression";
	$name="b_supprimer"; 
}
$query_contrat =	"SELECT contrat.*,libcourtorggest as liborggest, libcourttype  as libtype,".
									" libcourtorgfinanceur as liborgfinanceur, numclassif,libcourtclassif as libclassif,structure.libcourt_fr as libtheme".
									" FROM contrat, cont_orggest, cont_type, cont_orgfinanceur, cont_classif,structure".
									" WHERE contrat.codeorggest=cont_orggest.codeorggest ". 
									" and contrat.codetype=cont_type.codetype".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and contrat.codeclassif=cont_classif.codeclassif and contrat.codecontrat<>''".
									" and contrat.codetheme=structure.codestructure".
									" and contrat.codecontrat=".GetSQLValueString($codecontrat,"text");
$rs_contrat=mysql_query($query_contrat) or die(mysql_error());
$row_rs_contrat=mysql_fetch_assoc($rs_contrat);

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
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des contrats','lienretour'=>'gestioncontrats.php','texteretour'=>'Annuler et revenir &agrave; la gestion des contrats',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
	<tr>
    <td align="center">
    	<table class="table_gris_encadre" width="80%">
      <tr>
        <td align="center" class="noircalibri10">
        Suppression du contrat n&deg; <?php echo $codecontrat; ?>
        </td>
      </tr>
      <tr>
        <td align="center" class="noircalibri10">
          <?php echo $row_rs_contrat['sujet'] ?>
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
          <td>
            <form name="gestioncontrats" method="post" action="gestioncontrats.php">
              <input type="hidden" name="codecontrat" value="<?php echo $codecontrat; ?>">
              <input type="hidden" name="MM_update" value="<?php echo substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-strlen('confirmer_action_contrat.php')).'gestioncontrats.php' ?>">
              <input type="image" name="<?php echo $name ?>" class="icon" src="images/b_confirmer.png">
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
if(isset($rs_contrat)) mysql_free_result($rs_contrat);
?>
</body>
</html>
