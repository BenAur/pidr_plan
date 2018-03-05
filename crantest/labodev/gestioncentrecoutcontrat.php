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
$tab_contexte=array('prog'=>'gestioncentrecoutcontrat','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_budget = "form_budget";
//if($admin_bd)
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

// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
}
else
{ 
}

// ----------------------- Formulaire de donnees 

// typecredit
$query_rs = "SELECT codetypecredit, libcourt, codelibtypecredit FROM typecredit where codetypecredit<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typecredit[$row_rs['codetypecredit']]=$row_rs['libcourt'];
}
// centrefinancier
$query_rs = "SELECT codetypecredit, codecentrefinancier, libcourt FROM centrefinancier where codecentrefinancier<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrefinancier']]=$row_rs['libcourt'];
}
// centrecout
$query_rs ="SELECT codetypecredit,centrefinancier.codecentrefinancier,codecentrecout,centrecout.libcourt as libcourt,centrecout.liblong as liblong".
											" FROM centrecout,centrefinancier".
											" where centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and codecentrecout<>'' and centrefinancier.codecentrefinancier<>''".
											" ORDER BY codetypecredit,codecentrefinancier,centrecout.numordre";
$rs= mysql_query($query_rs_centrecout) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrecout']]=$row_rs['libcourt'];
}
//contrats

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion du budget <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<SCRIPT language="javascript">
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
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace("<br>","\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'<br>':'').str_replace("<br>","\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Centres financiers','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
	<tr>
		<td>&nbsp;
   </td>
	</tr>
	<tr>
		<td>
			<table align="center" border="0" class="data" id="table_results">
				<tr class="head">
					<td nowrap><span class="bleugrascalibri10">Type cr&eacute;dits</span>
				  </td>
					<td nowrap><span class="bleugrascalibri10">Centre financier</span>
				  </td>
					<td nowrap class="bleugrascalibri10">Centre de co&ucirc;t<br>Libell&eacute; court
					</td>
					<td nowrap class="bleugrascalibri10">Centre de co&ucirc;t<br>Libell&eacute; long
					</td>
					<td nowrap class="bleugrascalibri10">Action</td>
          <td nowrap class="bleugrascalibri10">Ordonner</td>
	      </tr>
				<?php 	
				$class="even";
				while($row_rs_centrecout=mysql_fetch_assoc($rs_centrecout))
				{
				?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<td nowrap align="left"><?php echo $tab_typecredit[$row_rs_centrecout['codetypecredit']] ?></td>
					<td nowrap align="left"><?php echo $tab_centrefinancier[$row_rs_centrecout['codecentrefinancier']] ?></td>
					<td nowrap><?php echo $row_rs_centrecout['libcourt'] ?>
					</td>
					<td nowrap><?php echo $row_rs_centrecout['liblong'] ?>
					</td>
          <td nowrap>
            <table>
              <tr>
                <td><input type="image" src="images/b_edit.png"></td>
                <td><input type="image" src="images/b_drop.png"></td>
              </tr>
            </table>
          </td>
          <td>
            <table>
              <tr>
                <td><input type="image" src="images/b_vers_haut.png" width="8" height="8"></td>
                <td><input type="image" src="images/b_vers_bas.png" width="8" height="8"></td>
              </tr>
            </table>
          </td>
				</tr>
				<?php
				}?>
        <tr>
        	<td><select name="nouveaucc_codetypecredit" type="text">
          <?php foreach($tab_typecredit as $codetypecredit=>$libcourt)
          {?> <option value="<?php echo $codetypecredit ?>"><?php echo $libcourt ?>
          <?php 
					}?>
          </select>
          </td>
        	<td><select name="nouveaucc_codecentrefinancier" type="text">
          <?php foreach($tab_centrefinancier as $codecentrefinancier=>$libcourt)
          {?> <option value="<?php echo $codecentrefinancier ?>"><?php echo $libcourt ?>
          <?php 
					}?>
          </select>
          </td>
          <td><input name="nouveaucc_libcourt" type="text" id="nouveaucc_libcourt" class="noircalibri10" size="20"></td>
          <td><input name="nouveaucc_liblong" type="text" id="nouveaucc_liblong" class="noircalibri10" size="100"></td>
          <td><input type="submit" name="nouveaucc" class="noircalibri10" value="Ajouter"></td>
          <td></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
