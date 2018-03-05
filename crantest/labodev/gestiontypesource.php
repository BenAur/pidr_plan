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
$tab_contexte=array('prog'=>'gestiontypesource','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestiontypesource = "form_gestiontypesource";
if($admin_bd)
{ /* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	} */ 
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$action='';
$codetypesource='';
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$pos3diese=strpos($postkey,'###');
		if($pos3diese!==false)
		{ $submit=substr($postkey,0,$pos3diese);
			$pos2diese=strpos($submit,'##');
			if($pos2diese!==false)
			{ $codetypesource=substr($submit,$pos2diese+2);
				$submit=substr($submit,0,$pos2diese);
				$posdiese=strpos($submit,"#");
				if($posdiese!=false)
				{ $action=substr($submit,$posdiese+1);
				}
			}
		}
		else
		{ $pos2diese=strpos($postkey,'##');
			if($pos2diese!==false)
			{ $submit=substr($postkey,0,$pos2diese);
				$posdiese=strpos($submit,"#");
				if($posdiese!=false)
				{ $action=substr($submit,$posdiese+1);
				}
			}
		}
	}
}

if($action=='creer' || $action=='enregistrer')
{ if(isset($_POST['libcourt']) && $_POST['libcourt']=='')
	{ $erreur='Libell&eacute; vide';
	}
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ if($erreur=='')
	{ $affiche_succes=true;
		// on enregistre d'abord nouveaucf
		if($action=='creer')
		{ $rs=mysql_query("select max(codetypesource) as maxcodetypesource from cmd_typesource");
			$row_rs=mysql_fetch_assoc($rs);
			$codetypesource=str_pad((string)((int)$row_rs['maxcodetypesource']+1), 2, "0", STR_PAD_LEFT);  
			// modifiable est un champ positionne en dur dans la table : vaut oui par defaut, non si ne doit pas etre modifie par l'admin bd
			mysql_query("insert into cmd_typesource (codetypesource,modifiable,libcourt,liblong) ".
									" values (".GetSQLValueString($codetypesource, "text").",'oui',".GetSQLValueString($_POST['libcourt'], "text").",'')") or die(mysql_error());
		}
		else if($action=='enregistrer')
		{	mysql_query("update cmd_typesource set libcourt=".GetSQLValueString($_POST['libcourt'], "text").
									" where codetypesource=".GetSQLValueString($codetypesource, "text")) or die(mysql_error());

		}
		else if($action=='supprimer')
		{	mysql_query("delete from cmd_typesource  where codetypesource=".GetSQLValueString($codetypesource, "text")) or die(mysql_error());

		}
		/* else if($action=='ordonner_haut' || $action=='ordonner_bas')
		{	//mysql_query("delete from cmd_typesource  where codetypesource=".GetSQLValueString($codetypesource, "text")) or die(mysql_error());

		} */
	}
}
else
{ 
}

// ----------------------- liste des typesource a afficher 
$query_rs_cmd_typesource ="SELECT distinct cmd_typesource.codetypesource,cmd_typesource.libcourt,modifiable,IF(budg_eotp_source_vue.codetypesource IS NULL,'non','oui') as estdanssource".
													" FROM cmd_typesource".
													" left join budg_eotp_source_vue on cmd_typesource.codetypesource=budg_eotp_source_vue.codetypesource".
													" WHERE cmd_typesource.codetypesource<>''".
													" ORDER BY cmd_typesource.libcourt";
$rs_cmd_typesource = mysql_query($query_rs_cmd_typesource) or die(mysql_error());

 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion du budget <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace("<br>","\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'<br>':'').str_replace("<br>","\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Types de sources','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
	<tr>
		<td>&nbsp;
   </td>
	</tr>
	<tr>
		<td align="center">      
    	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_supprcc">
      <div class="tooltipContent_cadre" id="info_supprcc">
          <span class="noircalibri10">
            Les types de sources permettent de regrouper, cumuler les sources de cr&eacute;dits non affect&eacute;s : ACI, Missions,... ind&eacute;pendamment de leurs enveloppes<br>
            La suppression d'un type source n'est possible que si aucune source n'en d&eacute;pend.<br>
          </span>
      </div>
      <script type="text/javascript">
          var sprytooltip_info_supprcc = new Spry.Widget.Tooltip("info_supprcc", "#sprytrigger_info_supprcc", {offsetX:0, offsetY:20});
        </script>
		</td>
  </tr>
  <tr>
		<td>
      <form name="<?php echo $form_gestiontypesource ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
      <!--<input type="hidden" name="codetypesource" value="">
      <input type="image" src="images/espaceur.gif" width="0" height="0"> -->
			<table align="center" border="0" class="data" id="table_results">
				<tr class="head" align="center">
					<td nowrap class="bleugrascalibri10">Type source
					</td>
					<td nowrap class="bleugrascalibri10">Action</td>
	      </tr>
				<?php 	
				$class="even";
				while($row_rs_cmd_typesource=mysql_fetch_assoc($rs_cmd_typesource))
				{
				?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<?php 
					if($action=='modifier')
					{ ?>
            <td nowrap>
            <?php 
						if($row_rs_cmd_typesource['codetypesource']==$codetypesource) 
						{ ?><input type="text" name="libcourt" value="<?php echo $row_rs_cmd_typesource['libcourt'] ?>" class="noircalibri10" size="20" maxsize="20">
            <?php
            }
            else
            {  echo $row_rs_cmd_typesource['libcourt'];
            }?>
						</td>
						<td>
            <?php 
						if($row_rs_cmd_typesource['codetypesource']==$codetypesource)
						{?> <input type="image" name="submit#enregistrer##<?php echo $row_rs_cmd_typesource['codetypesource'] ?>###" src="images/b_enregistrer.png">
            <?php 
						}
						else
						{ echo '&nbsp';
						}?> 
            </td>
            <?php
					}
					else
					{?>
            <td><?php echo $row_rs_cmd_typesource['libcourt']?>
            </td>
            <td nowrap align="left">
              <?php if($row_rs_cmd_typesource['modifiable']=='oui')
              {?> <table>
                <tr>
                  <td><input type="image" name="submit#modifier##<?php echo $row_rs_cmd_typesource['codetypesource'] ?>###" src="images/b_edit.png"></td>
                  <td>
									<?php 
									if($row_rs_cmd_typesource['estdanssource']=='non')
              		{?><input type="image" name="submit#supprimer##<?php echo $row_rs_cmd_typesource['codetypesource'] ?>###" src="images/b_drop.png" onClick="confirm('Supprimer ?');">
                	 <?php 
									}
									else
									{?><img src="images/b_dropgrise.png" border="0">
                	 <?php 
									}?>
                   </td>
                </tr>
              </table>
              <?php 
							}
              else
              {?> &nbsp;
              <?php }?>
            </td>
          <?php 
					}?>
          </tr>
          <?php
				}?>
    		</form>
				<?php 
				if($action!='modifier')
     	 	{?><form name="<?php echo $form_gestiontypesource ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
          <input type="hidden" name="codetypesource" value="">
        <tr>
          <td><input name="libcourt" type="text" id="libcourt" class="noircalibri10" size="20" maxsize="20"></td>
          <td><input type="image" name="submit#creer##" id="submit#creer##" class="noircalibri10" src="images/b_add.png"></td>
        </tr>
        </form>
        <?php 
				}?>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
