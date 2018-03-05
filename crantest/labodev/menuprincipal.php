<?php require_once('_const_fonc.php');

//$_SESSION['codeuser']=$codeuser;
$codeuser=deconnecte_ou_connecte();
// table des nom, prenom et roles+resp de $codeuser
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
// roles personnel
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
// roles commandes budget
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$tab_cmd_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,false,false,false);
$tab_cmd_roleuser=$tab_cmd_resp_roleuser['tab_roleuser'];
$tab_prog=array();
/* $rs = mysql_query("SELECT * FROM prog WHERE estutilise='oui' order by numordre") or die(mysql_error());
while($row_rs = mysql_fetch_assoc($rs))
{ $tab_prog[$row_rs['codeprog']]=$row_rs;
} */
$query_rs="(select distinct prog.* from individusejour, corps, progacces,prog".
					" where progacces.codeprog=prog.codeprog and estutilise='oui'".
					" and ".periodeencours('datedeb_sejour','datefin_sejour').
					" and individusejour.codeindividu=".GetSQLValueString($codeuser, "text").
					" and (progacces.critere='codecat' and progacces.valeur=corps.codecat and individusejour.codecorps=corps.codecorps)".
					"	)".
					" UNION".
					" (select distinct prog.* from individusejour,structureindividu,structure, progacces,prog".
					" where progacces.codeprog=prog.codeprog and estutilise='oui'".
					" and ".periodeencours('datedeb_sejour','datefin_sejour').
					" and individusejour.codeindividu=".GetSQLValueString($codeuser, "text").
					" and (progacces.critere='structure.codelib' and progacces.valeur=structure.codelib and individusejour.codeindividu=structureindividu.codeindividu and structureindividu.codestructure=structure.codestructure)".
					"	)".
					" UNION".
					" (select distinct prog.* from individusejour,progacces,prog".
					" where progacces.codeprog=prog.codeprog and estutilise='oui'".
					" and ".periodeencours('datedeb_sejour','datefin_sejour').
					" and individusejour.codeindividu=".GetSQLValueString($codeuser, "text").
					" and (progacces.critere='codeindividu' and progacces.valeur=individusejour.codeindividu)".
					"	)".
					" UNION".
					" (select distinct prog.* from progacces,prog".
					" where progacces.codeprog=prog.codeprog and estutilise='oui'".
					" and (progacces.critere='')".
					"	)".
					" UNION".
					" (select distinct prog.* from prog,structureindividu,structure".
					" where structureindividu.codestructure=structure.codestructure and codelib='admin_bd' and structureindividu.codeindividu=".GetSQLValueString($codeuser, "text").
					" and estutilise='oui'".
					"	)".
					" order by numordre";
					
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_prog[$row_rs['codeprog']]=$row_rs;	
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Menu principal</title>
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
	<?php echo entete_page(array('image'=>'','titrepage'=>'Gestion du laboratoire','lienretour'=>'','texteretour'=>'',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td>&nbsp;</td>
  </tr>
	<tr>
		<td>
    	<table border="0" align="center" cellpadding="0" cellspacing="1">
			<?php 
			if(isset($GLOBALS['menuprincipal_ligne_sous_entete']) && $GLOBALS['menuprincipal_ligne_sous_entete']!='')// 20170326
			{ ?>
      <tr>
				<td colspan="3" align="center"><?php echo $GLOBALS['menuprincipal_ligne_sous_entete'];?>
        </td>
      </tr>
      <?php 
			}
			foreach($tab_prog as $codeprog=>$un_tab_prog)
      { ?><tr>
				<td colspan="3">&nbsp;</td>
      </tr>
		  <tr>
		    <td><img src="images/<?php echo $un_tab_prog['image_icone'] ?>" alt="" width="16" height="16"></td>
		    <td>&nbsp;</td>
		    <td><a href="<?php echo $un_tab_prog['refprog'] ?>" target="_self"><span class="bleugrascalibri11"><?php echo $un_tab_prog['libprog'] ?></span></a> <?php if($un_tab_prog['refprog']=='gestioncommandes.php' && $GLOBALS['gestioncommandes_passees']!=''){echo $GLOBALS['gestioncommandes_passees'];}?></td>
	    </tr>
			<?php 
			}
			?>
	    </table>
     </td>
	</tr>
</table>
</body>
</html>
<?php
if(isset($rs))mysql_free_result($rs);
?>
