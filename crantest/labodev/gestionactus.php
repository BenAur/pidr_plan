<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$erreur_envoimail="";
$estmoderateur=false;
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{	foreach($_POST as $key=>$val)
	{ echo $key."=".$val."<br>";
	}
/**/
	$estmoderateur=true;
}
$codeactu=isset($_GET['codeactu'])?$_GET['codeactu']:(isset($_POST['codeactu'])?$_POST['codeactu']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codetypeactuevenementgroupe=isset($_GET['codetypeactuevenementgroupe'])?$_GET['codetypeactuevenementgroupe']:(isset($_POST['codetypeactuevenementgroupe'])?$_POST['codetypeactuevenmentgroupe']:"");
$codelibtypeactuevenementgroupe="";
// b_voir_archives_actus : si ce bouton submit est envoye on shifte l'affichage des actus depasses
if(!isset($_SESSION['b_voir_archives_actus']))
{ $_SESSION['b_voir_archives_actus']=false;
}
else if(isset($_POST['b_voir_archives_actus_x']))
{ $_SESSION['b_voir_archives_actus']=!$_SESSION['b_voir_archives_actus'];
}


if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF']))//methode=post
{ if($action=="supprimer")
	{ mysql_query("delete from actu where codeactu=".GetSQLValueString($codeactu, "text")) or die(mysql_error());
	}
	else if($action=="demander_validation")
	{	mysql_query("update actu set codestatutactu='E' where codeactu=".GetSQLValueString($codeactu, "text")) or die(mysql_error());
		$erreur_envoimail=mail_validation_actu($codeactu,$tab_infouser);
	}
	else if($action=="valider")
	{ mysql_query("update actu set codestatutactu='V' where codeactu=".GetSQLValueString($codeactu, "text")) or die(mysql_error());
		$erreur_envoimail=mail_validation_actu($codeactu,$tab_infouser);
	}
	else if($action=="archiver")
	{	mysql_query("update actu set codestatutactu='A' where codeactu=".GetSQLValueString($codeactu, "text")) or die(mysql_error());
	}
}

// -------------------------- FORMULAIRE D'ENVOI DES DONNES
$query_rs = "SELECT  codelibtypeactuevenementgroupe  FROM typeactuevenementgroupe WHERE codetypeactuevenementgroupe=".GetSQLValueString($codetypeactuevenementgroupe, "text");
$rs = mysql_query($query_rs) or die(mysql_error());
if($row_rs=mysql_fetch_assoc($rs))
{ $codelibtypeactuevenementgroupe=$row_rs['codelibtypeactuevenementgroupe'];
}

$query_rs_actu = "SELECT actu.*,typeactuevenement.libcourt_fr as libtypeactuevenement".
									" FROM actu,typeactuevenement ".
									" WHERE codeactu<>''".
									" and actu.codetypeactuevenement=typeactuevenement.codetypeactuevenement and codetypeactuevenementgroupe=".GetSQLValueString($codetypeactuevenementgroupe, "text").
									" ORDER BY datedeb_actu desc";

$rs_actu = mysql_query($query_rs_actu) or die(mysql_error());

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Actualit&eacute;s <?php echo $GLOBALS['acronymelabo'] ?></title>
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
	<table width="100%" >
	<?php echo entete_page(array('image'=>'images/b_document.png','titrepage'=>'Gestion des actualit&eacute;s','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array(''),
                                'msg_erreur_objet_mail'=>'codeactu='.$codeactu,'erreur_envoimail'=>$erreur_envoimail)) ?>
		<tr>
		  <td>&nbsp;</td>
	  </tr>
		<tr>
			<td>
       <table border="0" cellspacing="0" cellpadding="0">
			  <tr>
			   	<td>
            <form name="edit_actu" method="post" action="edit_actu.php">
							<input type="hidden" name="action" value="creer">
              <input type="image"  name="b_creer" img class="icon" src="images/b_<?php echo $codelibtypeactuevenementgroupe ?>_creer.png" alt="Nouveau" title="Nouveau" />
              <input type="hidden"  name="codetypeactuevenementgroupe" value="<?php echo $codetypeactuevenementgroupe ?>" />

            </form>
            <img src="images/b_info.png" width="16" height="16">&nbsp;<span class="bleugrascalibri11"><a href="aide_gestionactus.php" target="_blank" class="bleugrascalibri11">Aide/Explications</a></span></td>
          </td>
			  </tr>
		  </table>
      </td>
		</tr>
		<tr>
			<td align="center">
				<table width="100%" border="0" cellpadding="2">
					<tr class="head">
						<td nowrap align="center"><span class="bleugrascalibri11">N&deg;</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Date d&eacute;but</span></td> 
						<td nowrap align="center"><span class="bleugrascalibri11">Date fin</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Type</span></td>
						<td nowrap align="center"><span class="bleugrascalibri11">Titre</span></td>
						<!--<td nowrap align="center"><span class="bleugrascalibri11">Description</span></td> -->
						<td nowrap align="center"><span class="bleugrascalibri11">Actions</span></td>
						<td align="center"><span class="bleugrascalibri11">Visa<br>D&eacute;posant</span></td>
						<td align="center"><span class="bleugrascalibri11">Visa<br>Mod&eacute;rateur</span></td>
						<!-- <td nowrap align="center"><span class="bleugrascalibri11">Archiv&eacute;</span></td>-->
					</tr>
					<?php 
					while($row_rs_actu=mysql_fetch_assoc($rs_actu))
          { $codeactu=$row_rs_actu['codeactu'];
						$codestatutactu=$row_rs_actu['codestatutactu'];
						$estcreateur=($row_rs_actu['codecreateur']==$codeuser);
						$droitmodif=false;
						$droitsuppr=false;
						//conditions droit de modif/suppression
						if($estcreateur)
						{ if($codestatutactu=="")
							{ $droitmodif=true;
								$droitsuppr=true;
							}
						}
						if($estmoderateur)
						{	if($codestatutactu=="E")
							{ $droitmodif=true;
								$droitsuppr=true;
							}
						}

						$class="even";?>
            <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
              <td align="center"><span class="noircalibri10"><?php echo $codeactu ?></span>
              </td>
              <td align="center">
                <span class="noircalibri10"><?php echo aaaammjj2jjmmaaaa($row_rs_actu['datedeb_actu'],'/') ?></span>
              </td>
              <td align="center">
                <span class="noircalibri10"><?php echo aaaammjj2jjmmaaaa($row_rs_actu['datefin_actu'],'/') ?></span>
              </td>
              <td align="center"><span class="noircalibri10"><?php echo $row_rs_actu['libtypeactuevenement'] ?>&nbsp;</span>
              </td>
              <td>
                <span class="noircalibri10"><?php echo $row_rs_actu['titre_fr'];?></span>
              </td>
              <!--<td>
                <span class="noircalibri10"><?php //echo $row_rs_actu['descr_fr'];?></span>
              </td> -->
							<td>
                <table border="0" cellspacing="3" cellpadding="2">
                  <tr>
                    <td align="left" nowrap>
                      <a href="javascript:OuvrirVisible('<?php echo "detailactu.php?codeactu=".$codeactu; ?>','actu')"><img class="icon" width="16" height="16" src="images/b_oeil.png"></a>
                    </td>
                    <?php
                    if($droitmodif)
                    { ?>
                    <td align="center">
                      <form name="edit_actu" method="post" action="edit_actu.php">
                        <input type="hidden" name="action" value="modifier">
                        <input type="hidden" name="codeactu" value="<?php echo $codeactu ?>">
                        <input type="image" name="b_modifier" img class="icon" width="16" height="16" src="images/b_edit.png" 
                         alt="Modifier" title="Modifier">
                      </form>
                    </td>
										<?php
                    }
                    else
                    { ?>
                    <td><img class="icon" src="images/espaceur.gif" width="16" height="1"></td>
                    <?php
                    }
                    if($droitsuppr)
                    {?>
                    <td align="center">
                      <form name="gestionactus" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="codeactu" value="<?php echo $row_rs_actu['codeactu'] ?>">
                        <input type="image" name="b_supprimer" img class="icon" width="16" height="16" src="images/b_drop.png" 
                          alt="Supprimer" title="Supprimer"  onClick="return(confirm('Voulez-vous supprimer l\n actu <?php echo $codeactu ?>'))"/>
                      </form>
                    </td>
										<?php
                    }
                    else
                    { ?>
                    <td><img class="icon" src="images/espaceur.gif" width="16" height="1"></td>
                    <?php
                    }
										?>
                  </tr>
                </table>
              </td>
              <td align="center">
							<?php // colonne Visa referent
              if($codestatutactu=="")
              { if($estcreateur)
							 	{ ?>
                <form name="gestionactus" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                  <input type="hidden" name="action" value="demander_validation">
                  <input type="hidden" name="codeactu" value="<?php echo $row_rs_actu['codeactu'] ?>">
                  <input name="b_valider" type="image" img class="icon" src="images/b_valider.png" alt="Valider" title="Valider" 
                  onClick="return confirme('valider','<?php echo popup_validation_actu('demandevalidation',$codeactu,$tab_infouser) ?>')" />
              	</form>
              	<?php
								}
								else
								{?><img class="icon" src="images/b_brancher.png" alt="En attente de validation" title="En attente de validation">
              <?php
								}
              } 
              else // E, V, ...
              {?>
                <img class="icon" src="images/b_visa.png" alt="Valid&eacute;" title="Valid&eacute;">
                <?php 
              }?>
              </td>
              
              <td align="center">
							<?php 
              if($codestatutactu=="")
              { ?>
              <img class="icon" src="images/b_sablier.png" alt="Attente" title="Attente">
              <?php
              }
              else if($codestatutactu=="E")
              { if($estmoderateur)
                {	?>
                  <form name="gestionactus" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="action" value="valider">
                    <input type="hidden" name="codeactu" value="<?php echo $row_rs_actu['codeactu'] ?>">
                   	<input name="b_valider" type="image" 
                    img class="icon" src="images/b_valider.png" alt="Valider" title="Valider" 
                    onClick="return confirme('valider','<?php echo popup_validation_actu('visamoderateur',$codeactu,$tab_infouser) ?>')" />
                  </form>
                  <?php 
                }
                else
                {?><img class="icon" src="images/b_brancher.png" alt="En attente de validation" title="En attente de validation">
                	<?php 
                }
              }
              else//actu validé
              {?>
                <img class="icon" src="images/b_visa.png" alt="Valid&eacute;" title="Valid&eacute;">
              <?php 
              }?>
              </td>
          	</tr>
					<?php 
          }?>
        </table>  
      </td>
		</tr>
  </table>
</body>
</html>
	<?php
if(isset($rs_actu))mysql_free_result($rs_actu);
if(isset($rs_ind))mysql_free_result($rs_ind);
?>





