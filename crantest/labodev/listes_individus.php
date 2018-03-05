<?php include_once('_const_fonc.php');?>
<?php 						 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Listes des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
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
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_couple.png','titrepage'=>'Listes des personnels','lienretour'=>'gestionindividus.php','texteretour'=>'Retour &agrave; la gestion des personnels',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td>&nbsp;
   </td>
	</tr>
	<tr>
	  <td><table align="center" class="table_cadre_arrondi" cellpadding="5" cellspacing="5">
	    <tr class="odd">
	      <td nowrap class="bleucalibri11">&nbsp;</td>
	      <td nowrap class="bleucalibri11">&nbsp;</td>
	      <td nowrap class="bleucalibri11">CTRL/A, CTRL/C,  collage sp&eacute;cial Texte dans Excel</td>
	      <td nowrap class="bleucalibri11">Filtre appliqu&eacute;, Lecture seule : enregistrer sous... pour modifier dans Excel</td>
	      </tr>
	    <tr class="odd">
	      <td nowrap class="bleucalibri11">Cahier de visites</td>
	      <td nowrap class="bleucalibri11"><a href="listecahiervisite.php?type_fichier=html" target="_blank">visu</a></td>
	      <td nowrap class="bleucalibri11"><a href="listecahiervisite.php?type_fichier=csv" target="_blank">export format csv (tab)</a></td>
	      <td nowrap class="bleucalibri11">&nbsp;</td>
	      </tr>
	    <tr class="even">
	      <td nowrap class="bleucalibri11">Pr&eacute;sents, partis, futurs, D&eacute;pt., emploi, coll&egrave;ge...<br>Hors Prestataires et -5j</td>
	      <td nowrap bgcolor="#D6D6D6" class="bleucalibri11"></td>
	      <td nowrap class="bleucalibri11"><a href="listepresentpartifutur_emploi_college.php?type_fichier=csv" target="_blank">export format csv (tab)</a></td>
	      <td nowrap class="bleucalibri11"><a href="listepresentpartifutur_emploi_college_xls.php" target="_blank">export format excel</a> (patienter quelques secondes)</td>
	    </tr>
	    <tr class="odd">
	      <td nowrap class="bleucalibri11">Annuaire pr&eacute;sents</td>
	      <td nowrap class="bleucalibri11"><a href="listeperslabo.php?type_fichier=html" target="_blank">visu</a></td>
	      <td nowrap class="bleucalibri11"><a href="listeperslabo.php?type_fichier=csv" target="_blank">export format csv (tab)</a></td>
	      <td nowrap class="bleucalibri11">&nbsp;</td>
	      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>




