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
$tab_contexte=array('prog'=>'gestioncentrefinancier','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestioncentrefinancier = "form_gestioncentrefinancier";
if($admin_bd)
{ /* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/  
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$action='';
$codecentrefinancier='';
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$pos3diese=strpos($postkey,'###');
		if($pos3diese!==false)
		{ $submit=substr($postkey,0,$pos3diese);
			$pos2diese=strpos($submit,'##');
			if($pos2diese!==false)
			{ $codecentrefinancier=substr($submit,$pos2diese+2);
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
		{ $rs=mysql_query("select max(codecentrefinancier) as maxcodecentrefinancier from centrefinancier");
			$row_rs=mysql_fetch_assoc($rs);
			$codecentrefinancier=str_pad((string)((int)$row_rs['maxcodecentrefinancier']+1), 2, "0", STR_PAD_LEFT);  
			mysql_query("insert into centrefinancier (codecentrefinancier,codetypecredit,date_deb,date_fin,libcourt,liblong,numordre) ".
									" values (".GetSQLValueString($codecentrefinancier, "text").",".GetSQLValueString($_POST['codetypecredit'], "text").",'','',".
									GetSQLValueString($_POST['libcourt'], "text").",'',".GetSQLValueString($codecentrefinancier, "text").")") or die(mysql_error());
		}
		else if($action=='enregistrer')
		{	mysql_query("update centrefinancier set codetypecredit=".GetSQLValueString($_POST['codetypecredit'], "text").", libcourt=".GetSQLValueString($_POST['libcourt'], "text").
									" where codecentrefinancier=".GetSQLValueString($codecentrefinancier, "text")) or die(mysql_error());

		}
		else if($action=='supprimer')
		{	mysql_query("delete from centrefinancier  where codecentrefinancier=".GetSQLValueString($codecentrefinancier, "text")) or die(mysql_error());

		}
	}
}

// ----------------------- liste des centrefinancier a afficher 
// centrefinancier
$query_rs = "SELECT codecentrefinancier,codetypecredit,libcourt".
						" FROM centrefinancier where codecentrefinancier<>'' ORDER BY codetypecredit,centrefinancier.numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_centrefinancier=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrefinancier']]=$row_rs;
	$tab_centrefinancier[$row_rs['codecentrefinancier']]['estutilise']=false;//par defaut
}
// centrefinancier non supprimable s'il est utilise par un eotp/source ou si dans budget.
// si utilise, les credits UL ou CNRS ne peuvent etre modifies 
$query_rs = "SELECT distinct codecentrefinancier from centrecout where codecentrefinancier<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrefinancier']]['estutilise']=true;
}

 
// typecredit
$query_rs = "SELECT codetypecredit, libcourt FROM typecredit where codetypecredit<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typecredit[$row_rs['codetypecredit']]=$row_rs['libcourt'];
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
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Types de cr&eacute;dits','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
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
            La suppression d'un "type de cr&eacute;dit" n'est possible que si aucune enveloppe n'en d&eacute;pend.
          </span>
      </div>
      <script type="text/javascript">
          var sprytooltip_info_supprcc = new Spry.Widget.Tooltip("info_supprcc", "#sprytrigger_info_supprcc", {offsetX:0, offsetY:20});
        </script>
		</td>
  </tr>
  <tr>
		<td>
      <form name="<?php echo $form_gestioncentrefinancier ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
      <!--<input type="hidden" name="codecentrefinancier" value="">
      <input type="image" src="images/espaceur.gif" width="0" height="0"> -->
			<table align="center" border="0" class="data" id="table_results">
				<tr class="head" align="center">
					<td nowrap><span class="bleugrascalibri10">Cr&eacute;dits</span>
				  </td>
					<td nowrap class="bleugrascalibri10">Type cr&eacute;dits
					</td>
					<td nowrap class="bleugrascalibri10">Action</td>
          <!--<td nowrap class="bleugrascalibri10">Ordonner</td> -->
	      </tr>
				<?php 	
				$class="even";
				foreach($tab_centrefinancier as $un_codecentrefinancier=> $row_rs_centrefinancier)
				{
				?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<?php 
					if($action=='modifier')
					{ ?>
          <td nowrap align="left">
						<?php 
              if($row_rs_centrefinancier['codecentrefinancier']==$codecentrefinancier) 
              { if($tab_centrefinancier[$codecentrefinancier]['estutilise'])
                { ?><input type="hidden" name="codetypecredit" value="<?php echo $row_rs_centrefinancier['codetypecredit'] ?>"><?php echo $tab_typecredit[$row_rs_centrefinancier['codetypecredit']] ?>
                <?php 
                }
                else
                {?> <select name="codetypecredit" type="text">
                  <?php foreach($tab_typecredit as $codetypecredit=>$libtypecredit)
                  {?> <option value="<?php echo $codetypecredit ?>" <?php echo $codetypecredit==$row_rs_centrefinancier['codetypecredit']?'selected':'' ?>><?php echo $libtypecredit ?></option>
                  <?php 
                  }?>
                  </select>
              <?php 
                } 
              }
              else
              { echo $tab_typecredit[$row_rs_centrefinancier['codetypecredit']]; 
              }?>          
            </td>
            <td nowrap>
							<?php 
              if($row_rs_centrefinancier['codecentrefinancier']==$codecentrefinancier) 
              { ?><input type="text" name="libcourt" value="<?php echo $row_rs_centrefinancier['libcourt'] ?>" class="noircalibri10" size="20" maxsize="20">
              <?php
              }
              else
              {  echo $row_rs_centrefinancier['libcourt'];
              }?>
						</td>
						<td>
							<?php 
              if($row_rs_centrefinancier['codecentrefinancier']==$codecentrefinancier)
              {?> <input type="image" name="submit#enregistrer##<?php echo $row_rs_centrefinancier['codecentrefinancier'] ?>###" src="images/b_enregistrer.png">
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
            <td nowrap><?php echo $tab_typecredit[$row_rs_centrefinancier['codetypecredit']]; ?>
            </td>
            <td><?php echo $row_rs_centrefinancier['libcourt']?>
            </td>
            <td nowrap align="left">
              <table>
                <tr>
                  <td><input type="image" name="submit#modifier##<?php echo $row_rs_centrefinancier['codecentrefinancier'] ?>###" src="images/b_edit.png"></td>
                  <td><?php 
										if($tab_centrefinancier[$row_rs_centrefinancier['codecentrefinancier']]['estutilise'])
                    {?><img src="images/b_dropgrise.png" border="0">
                  	<?php 
										}
                    else
                    {?><input type="image" name="submit#supprimer##<?php echo $row_rs_centrefinancier['codecentrefinancier'] ?>###" src="images/b_drop.png" onClick="confirm('Supprimer ?');">
                    <?php 
										}?>
                  </td>
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
     		</form>
				<?php /* if($action!='modifier')
     	 	{?><form name="<?php echo $form_gestioncentrefinancier ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
          <tr>
            <td><select name="codetypecredit" type="text">
            <?php foreach($tab_typecredit as $codetypecredit=>$libtypecredit)
            {?> <option value="<?php echo $codetypecredit ?>"><?php echo $libtypecredit ?></option>
            <?php 
            }?>
            </select>
            </td>
            <td><input name="libcourt" type="text" id="libcourt" class="noircalibri10" size="20" maxsize="20"></td>
            <td><input type="image" name="submit#creer##" class="noircalibri10" src="images/b_add.png"></td>
          </tr>
       	</form>
        <?php 
				} */?>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
