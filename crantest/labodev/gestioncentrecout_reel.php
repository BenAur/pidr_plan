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
$tab_contexte=array('prog'=>'gestioncentrecout_reel','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestioncentrecout_reel = "form_gestioncentrecout_reel";
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

$action='';
$codecentrecout_reel='';
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$pos3diese=strpos($postkey,'###');
		if($pos3diese!==false)
		{ $submit=substr($postkey,0,$pos3diese);
			$pos2diese=strpos($submit,'##');
			if($pos2diese!==false)
			{ $codecentrecout_reel=substr($submit,$pos2diese+2);
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
		{ $rs=mysql_query("select max(codecentrecout_reel) as maxcodecentrecout_reel from centrecout_reel");
			$row_rs=mysql_fetch_assoc($rs);
			$codecentrecout_reel=str_pad((string)((int)$row_rs['maxcodecentrecout_reel']+1), 3, "0", STR_PAD_LEFT);  
			mysql_query("insert into centrecout_reel (codecentrecout_reel,codecentrefinancier_reel,date_deb,date_fin,libcourt,liblong,numordre) ".
									" values (".GetSQLValueString($codecentrecout_reel, "text").",".GetSQLValueString($_POST['codecentrefinancier_reel'], "text").",'','',".
									GetSQLValueString($_POST['libcourt'], "text").",'',".GetSQLValueString($codecentrecout_reel, "text").")") or die(mysql_error());
		}
		else if($action=='enregistrer')
		{	mysql_query("update centrecout_reel set codecentrefinancier_reel=".GetSQLValueString($_POST['codecentrefinancier_reel'], "text").", libcourt=".GetSQLValueString($_POST['libcourt'], "text").
									" where codecentrecout_reel=".GetSQLValueString($codecentrecout_reel, "text")) or die(mysql_error());

		}
		else if($action=='supprimer')
		{	mysql_query("delete from centrecout_reel  where codecentrecout_reel=".GetSQLValueString($codecentrecout_reel, "text")) or die(mysql_error());

		}
	}
}
else
{ 
}

// ----------------------- liste des budgetrecettes a afficher 
// centrecout_reel
$query_rs = "SELECT codecentrecout_reel,codecentrefinancier_reel,libcourt".
																" FROM centrecout_reel where codecentrecout_reel<>'' ORDER BY codecentrefinancier_reel,centrecout_reel.numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_centrecout_reel=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrecout_reel[$row_rs['codecentrecout_reel']]=$row_rs;
	$tab_centrecout_reel[$row_rs['codecentrecout_reel']]['supprimable']=true;//par defaut
}
// centrecout_reel supprimable s'il n'est pas utilise par un eotp/source
$query_rs = "SELECT codecentrecout_reel from budg_eotp_source_vue where codecentrecout_reel<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrecout_reel[$row_rs['codecentrecout_reel']]['supprimable']=false;
}

if($erreur=='')
{	 
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ /* $rs_fields_commande = mysql_query('SHOW COLUMNS FROM budgetrecette');
	while($row_rs_fields_budgetrecette = mysql_fetch_assoc($rs_fields_budgetrecette)) 
	{ $Field=$row_rs_fields_budgetrecette['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_budgetrecette[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_budgetrecette['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_budgetrecette[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	} */
}
 
// centrefinancier_reel
$query_rs = "SELECT codecentrefinancier_reel, libcourt FROM centrefinancier_reel where codecentrefinancier_reel<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier_reel[$row_rs['codecentrefinancier_reel']]=$row_rs['libcourt'];
}
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
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
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
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Enveloppes','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
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
            La suppression d'un centre de co&ucirc;t n'est possible que si aucune source n'en d&eacute;pend.
          </span>
      </div>
      <script type="text/javascript">
          var sprytooltip_info_supprcc = new Spry.Widget.Tooltip("info_supprcc", "#sprytrigger_info_supprcc", {offsetX:0, offsetY:20});
        </script>
		</td>
  </tr>
	<tr>
		<td>
      <form name="<?php echo $form_gestioncentrecout_reel ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="codecentrecout_reel" value="">
      <input type="image" src="images/espaceur.gif" width="0" height="0">
      <input type="image" src="images/espaceur.gif" width="0" height="0">
			<table align="center" border="0" class="data" id="table_results">
				<tr class="head" align="center">
					<td nowrap><span class="bleugrascalibri10">Centre financier</span>
				  </td>
					<td nowrap class="bleugrascalibri10">Centres de co&ucirc;t
					</td>
					<td nowrap class="bleugrascalibri10">Action</td>
          <!--<td nowrap class="bleugrascalibri10">Ordonner</td> -->
	      </tr>
				<?php 	
				$class="even";
				foreach($tab_centrecout_reel as $un_codecentrecout_reel=> $row_rs_centrecout_reel)
				{
				?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<?php 
					if($action=='modifier')
					{ ?>
          <td nowrap align="left">
					<?php 
						if($row_rs_centrecout_reel['codecentrecout_reel']==$codecentrecout_reel) 
						{ ?>
							<select name="codecentrefinancier_reel" type="text">
							<?php foreach($tab_centrefinancier_reel as $codecentrefinancier_reel=>$libcentrefinancier_reel)
							{?> <option value="<?php echo $codecentrefinancier_reel ?>"<?php echo $codecentrefinancier_reel==$row_rs_centrecout_reel['codecentrefinancier_reel'] ?>><?php echo $libcentrefinancier_reel ?></option>
							<?php 
							}?>
							</select>
						<?php 
						}
						else
						{ echo $tab_centrefinancier_reel[$row_rs_centrecout_reel['codecentrefinancier_reel']]; 
						}?>          
            </td>
            <td nowrap>
            <?php 
						if($row_rs_centrecout_reel['codecentrecout_reel']==$codecentrecout_reel) 
						{ ?><input type="text" name="libcourt" value="<?php echo $row_rs_centrecout_reel['libcourt'] ?>" class="noircalibri10" size="20" maxsize="20">
            <?php
            }
            else
            {  echo $row_rs_centrecout_reel['libcourt'];
            }?>
						</td>
						<td>
            <?php 
						if($row_rs_centrecout_reel['codecentrecout_reel']==$codecentrecout_reel)
						{?> <input type="image" name="submit#enregistrer##<?php echo $row_rs_centrecout_reel['codecentrecout_reel'] ?>###" src="images/b_enregistrer.png">
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
            <td nowrap><?php echo $tab_centrefinancier_reel[$row_rs_centrecout_reel['codecentrefinancier_reel']]; ?>
            </td>
            <td><?php echo $row_rs_centrecout_reel['libcourt']?>
            </td>
            <td nowrap align="left">
              <table>
                <tr>
                  <td><input type="image" name="submit#modifier##<?php echo $row_rs_centrecout_reel['codecentrecout_reel'] ?>###" src="images/b_edit.png"></td>
                  <td>
										<?php if($row_rs_centrecout_reel['supprimable'])
                    {?> <input type="image" name="submit#supprimer##<?php echo $row_rs_centrecout_reel['codecentrecout_reel'] ?>###" src="images/b_drop.png"  onClick="confirm('Supprimer ?');"></td>
                  	<?php 
										}
                    else
                    {?> &nbsp;
                    <?php 
										}?>
                </tr>
              </table>
            </td>
          <?php 
					}?>
         <!-- <td>
            <table>
              <tr>
                <td><input type="image" src="images/b_vers_haut.png" width="8" height="8"></td>
                <td><input type="image" src="images/b_vers_bas.png" width="8" height="8"></td>
              </tr>
            </table>
          </td> -->
				</tr>
				<?php
				}?>
				<?php if($action!='modifier')
     	 	{?><input type="hidden" name="codecentrecout_reel" value="<?php echo $row_rs_centrecout_reel['codecentrecout_reel'] ?>">
        <tr>
        	<td><select name="codecentrefinancier_reel" type="text">
          <?php foreach($tab_centrefinancier_reel as $codecentrefinancier_reel=>$libcentrefinancier_reel)
          {?> <option value="<?php echo $codecentrefinancier_reel ?>"><?php echo $libcentrefinancier_reel ?></option>
          <?php 
					}?>
          </select>
          </td>
          <td><input name="libcourt" type="text" id="libcourt" class="noircalibri10" size="20" maxsize="20"></td>
          <td><input type="image" name="submit#creer##" class="noircalibri10" src="images/b_add.png"></td>
        </tr>
        <?php }?>
     </form>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
