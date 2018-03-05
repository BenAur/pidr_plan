<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_contexte=array('prog'=>'menubudget','codeuser'=>$codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Menu budget</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
</head>
<body>
<!--Titre de la page et lien de sortie -->
<table width="50%" border="0" align="center" cellpadding="0" cellspacing="1" >
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'MENU BUDGET','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
	<tr>
  	<td>
      <table align="center">
        <tr>
        <td>&nbsp;</td>
        <td><table border="0" align="center" cellpadding="0" cellspacing="1">
          <tr>
            <td><img src="images/fleche_double_droit_sup12x10.gif"></td>
            <td nowrap><a href="gestioncentrefinancier_reel.php" target="_self"><span class="bleugrascalibri11">Centres financiers</span></a></td>
            <td nowrap>&nbsp;</td>
            <td nowrap><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
            <td nowrap><a href="gestioncentrecout_reel.php" target="_self"><span class="bleugrascalibri11">Centres de co&ucirc;ts</span></a></td>
            <td nowrap>&nbsp;</td>
            <td nowrap>&nbsp;</td>
            <td nowrap>&nbsp;</td>
          </tr>
          <tr>
            <td colspan="8">&nbsp;</td>
          </tr>
          <tr>
              <td><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><a href="gestioncentrefinancier.php" target="_self"><span class="bleugrascalibri11">Types de cr&eacute;dits</span></a></td>
              <td nowrap>&nbsp;</td>
              <td nowrap><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><a href="gestioncentrecout.php" target="_self"><span class="bleugrascalibri11">Enveloppes </span></a></td>
              <td nowrap>&nbsp;</td>
              <td nowrap>&nbsp;</td>
              <td nowrap>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <td><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><span class="bleugrascalibri11"><a href="gestioncontrateotp.php" target="_self">Gestion Contrats/eotp</a></span></td>
              <td nowrap>&nbsp;</td>
              <td nowrap><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><span class="bleugrascalibri11"><a href="gestiontypesource.php" target="_self">Types de sources</a></span></td>
              <td nowrap>&nbsp;</td>
              <td nowrap><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><a href="gestioneotp_source_masse.php" target="_self"><span class="bleugrascalibri11">Gestion EOTP/Sources-masses</span></a></td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <td><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><span class="bleugrascalibri11"><a href="gestiondialoguegestion.php" target="_self">Dialogue gestion</a><a href="gestionnature.php" target="_self"></a></span></td>
              <td nowrap>&nbsp;</td>
              <td nowrap>&nbsp;</td>
              <td nowrap><a href="gestiondialoguegestion.php" target="_self"></a></td>
              <td nowrap>&nbsp;</td>
              <td nowrap>&nbsp;</td>
              <td nowrap>&nbsp;</td>
            </tr>
            <tr>
              <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
              <td nowrap><img src="images/fleche_double_droit_sup12x10.gif" alt=""></td>
              <td nowrap><a href="gestionannonce.php" target="_self"><span class="bleugrascalibri11">Annonces IEB</span></a></td>
              <td nowrap colspan="6"></td>
            </tr>
          </table>
         </td>
        </tr>
	    </table>
     </td>
	</tr>
</table>
</body>
</html>
