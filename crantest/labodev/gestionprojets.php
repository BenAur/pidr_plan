<?php require_once('_const_fonc.php'); ?>
<?php
$erreur="";
$erreur_envoimail="";
$affiche_succes=false;
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if($admin_bd)
{/*foreach($_POST as $key=>$val)
	{ echo $key."=".$val."<br>";
	}
*/}

$estadmin=array_key_exists('du',$tab_roleuser) || array_key_exists('admingestfin',$tab_roleuser) || array_key_exists('gestprojet',$tab_roleuser) || $admin_bd ;

$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codeprojet=isset($_GET['codeprojet'])?$_GET['codeprojet']:(isset($_POST['codeprojet'])?$_POST['codeprojet']:"");
$projet_ancre=isset($_GET['projet_ancre'])?$_GET['projet_ancre']:(isset($_POST['projet_ancre'])?$_POST['projet_ancre']:"");
$b_voir_archives_projets=isset($_GET['b_voir_archives_projets_x'])?$_GET['b_voir_archives_projets_x']:(isset($_POST['b_voir_archives_projets_x'])?$_POST['b_voir_archives_projets_x']:"");
$b_voir_tous_themes=isset($_GET['b_voir_tous_themes_x'])?$_GET['b_voir_tous_themes_x']:(isset($_POST['b_voir_tous_themes_x'])?$_POST['b_voir_tous_themes_x']:"");
$info_accompagnement_projet=isset($_POST['info_accompagnement_projet'])?$_POST['info_accompagnement_projet']:"";

// b_voir_archives_projets : si ce bouton submit est envoye on shifte l'affichage des projets depasses
if(!isset($_SESSION['b_voir_archives_projets']))
{ $_SESSION['b_voir_archives_projets']=false;
}
else if(isset($_POST['b_voir_archives_projets_x']))
{ $_SESSION['b_voir_archives_projets']=!$_SESSION['b_voir_archives_projets'];
}

// b_voir_tous_themes : si ce bouton submit est envoye on shifte l'affichage des projets depasses
if(!isset($_SESSION['b_voir_tous_themes']))
{ $_SESSION['b_voir_tous_themes']=false;
}
if(isset($_POST['b_voir_tous_themes_x']))
{ $_SESSION['b_voir_tous_themes']=!$_SESSION['b_voir_tous_themes'];
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF']))//methode=post
{ $affiche_succes=true;
	if($action=="supprimer")
	{ $rs_projetpj=mysql_query("select codetypepj from projetpj".
																" where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
		if(mysql_num_rows($rs_projetpj)>=1)
		{ while($row_rs_projetpj=mysql_fetch_assoc($rs_projetpj))
			{	unlink($GLOBALS['path_to_rep_upload'] .'/projet/'.$codeprojet.'/'.$row_rs_projetpj['codetypepj']);
			}
			//suppression du rep de ce projet
			suppr_rep($GLOBALS['path_to_rep_upload'] .'/projet/'.$codeprojet);
			// suppression des enreg. des pj pour ce projet
			mysql_query("delete from projetpj where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
		}
		mysql_query("delete from projet where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
	}
	else if($action=="brouillon")
	{	//$erreur_envoimail=mail_validation_projet($codeprojet,$tab_infouser,'theme');
		mysql_query("update projet set codestatutprojet='' where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
	}
	else if($action=="publier")
	{	$erreur_envoimail=mail_validation_projet($codeprojet,$tab_infouser,'theme');
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : Op&eacute;ration non effectu&eacute;e";
			$affiche_succes=false;
			$erreur="Op&eacute;ration d&rsquo;envoi non effectu&eacute;e.";
		}
		else
		{ mysql_query("update projet set codestatutprojet='01', date_publi=".GetSQLValueString(date("Y/m/d"), "text")." where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
		}
	}
	else if($action=="archiver")
	{	mysql_query("update projet set codestatutprojet='99' where codeprojet=".GetSQLValueString($codeprojet, "text")) or die(mysql_error());
	}
	else if($action=='mail_accompagnement_projet')
	{ $erreur_envoimail=mail_accompagnement_projet($codeprojet,$codeuser,$info_accompagnement_projet);
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : envoi non effectu&eacute;e pour le projet ".$codeprojet;
			$affiche_succes=false;
			$erreur="Op&eacute;ration d&rsquo;envoi non effectu&eacute;e.";
		}
		else
		{ $updateSQL ="update projet set mail_accompagnement_projet='oui' where codeprojet=".GetSQLValueString($codeprojet, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}
	else if($action=='traite_accompagnement_projet')
	{ $erreur_envoimail=mail_traite_accompagnement_projet($codeprojet,$codeuser);
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : envoi non effectu&eacute;e pour le projet ".$codeprojet;
			$affiche_succes=false;
			$erreur="Op&eacute;ration non effectu&eacute;e.";
		}
		else
		{ $updateSQL ="update projet set traite_accompagnement_projet='oui' where codeprojet=".GetSQLValueString($codeprojet, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}
}

// -------------------------- FORMULAIRE D'ENVOI DES DONNES
//l'individu connecte $codeuser est-il responsable de theme de projets? 
$rs=mysql_query("select structureindividu.* from structureindividu,structure " .
								" where structure.codestructure=structureindividu.codestructure".
								" and structureindividu.codeindividu=".GetSQLValueString($codeuser,"text")." and esttheme='oui' and estresp='oui'") or die(mysql_error());
$tab_resptheme=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_resptheme[$row_rs['codestructure']]=$row_rs['codeindividu'];
}

$query_rs = "SELECT distinct projet.*,cont_classif.libcourtclassif as libclassif,codelibstatutprojet,libstatutprojet, libtypeprojet, structure.libcourt_fr as libtheme,".
						" referent.nom as referentnom, referent.prenom as referentprenom".
						" FROM projet,cont_classif,proj_typeprojet, statutprojet, structure, individu as referent".
						" WHERE codeprojet<>''".
						" AND projet.codeclassif=cont_classif.codeclassif AND projet.codetypeprojet=proj_typeprojet.codetypeprojet AND projet.codestatutprojet=statutprojet.codestatutprojet".
						" AND projet.codereferent=referent.codeindividu".
						" AND projet.codetheme=structure.codestructure".
						//($_SESSION['b_voir_archives_projets']?"":" AND projet.codestatutprojet<>'99'").
						" ORDER BY datedeb_projet desc";

$rs = mysql_query($query_rs) or die(mysql_error());
$tab_projet=array();
$nb_projet=0;						
while($row_rs=mysql_fetch_assoc($rs))
{ $codeprojet=$row_rs['codeprojet'];
	$droitmodif=false;
	$droitsuppr=false;
	$afficher=false;
	if($row_rs['codelibstatutprojet']=='publie')
	{ $afficher=true;
	}
	$estcreateur_ou_referent=($row_rs['codecreateur']==$codeuser || $row_rs['codereferent']==$codeuser);
	$estresptheme=(isset($tab_resptheme[$row_rs['codetheme']]));
	//conditions droit de modif/suppression
	if($estcreateur_ou_referent || $estresptheme)
	{ $droitmodif=true;
		$droitsuppr=true;
		$afficher=true;
		if($row_rs['codelibstatutprojet']=='publie')
		{ $droitsuppr=false;
		}
	}
	if($estadmin)
	{ $droitmodif=true;
	}
	if($afficher)
	{ $nb_projet++;
		$tab_projet[$codeprojet]=$row_rs;
		$tab_projet[$codeprojet]['droitmodif']=$droitmodif;
		$tab_projet[$codeprojet]['droitsuppr']=$droitsuppr;
		$tab_projet[$codeprojet]['estcreateur_ou_referent']=$estcreateur_ou_referent;
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des projets</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
	
<SCRIPT language="javascript">
	var w;
	function OuvrirVisible(url,nom)
	{ w=window.open(url,nom,"scrollbars = yes,width=800,height=350,location=no,mebubar=no,status=no,directories=no");
		w.document.close();
		w.focus();
	}
	function Fermer() 
	{ if (w.document) { w.close(); }
	}
	var nbtablerow=<?php echo $nb_projet; ?>;
	function m(tablerow)// marque ligne en vert
	{ even_ou_odd='even';
		for(numrow=1;numrow<=nbtablerow;numrow++)
		{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
			document.getElementById('t'+numrow).className=even_ou_odd;
		}
		
		document.getElementById(tablerow.id).className='marked';
	}

	function e(codeprojet)
	{ document.location.href="edit_projet.php?codeprojet="+codeprojet+"&action=modifier&projet_ancre="+codeprojet;
	}
</SCRIPT>

</head>
<body>
	<table width="100%" >
	<?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des projets','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'codeprojet='.$codeprojet,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
		<tr>
			<td><img src="images/espaceur.gif" width="1" height="20">
			</td>
		</tr>
    <?php 
		// affichage du bouton creer si user est dans un dept
		$query_rs = "select codetheme from individutheme where codeindividu=".GetSQLValueString($codeuser, "text").
							" and codetheme in (select codestructure from structure where codestructure<>'00' and esttheme='oui' ".
							"										and  ".intersectionperiodes('structure.date_deb','structure.date_fin','"'.date('Y/m/d').'"','"'.date('Y/m/d').'"').")";
		$rs=mysql_query($query_rs);
		if(mysql_num_rows($rs)>=1)
		{?> 
    <tr>
			<td>
       <table border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td valign="top"><span class="bleugrascalibri11">Nouveau </span>&nbsp;</td>
			   	<td valign="bottom">
            <form name="edit_projet" method="post" action="edit_projet.php">
							<input type="hidden" name="action" value="creer">
							<input type="hidden" name="codeprojet" value="">
			      	<input type="image"  name="b_creer" src="images/b_projet_creer.png" alt="Cr&eacute;er un projet" title="Cr&eacute;er un projet" />
						</form>
          </td>
          <td><img src="images/espaceur.gif" width="20"><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_projet">
            <div class="tooltipContent_cadre" id="info_projet">
          	<span class="noircalibri10">un <strong>Chercheur</strong> peut :<br>
            - d&eacute;poser un nouveau projet qui passe &agrave; l'&eacute;tat &quot;<strong>Brouillon</strong>&quot; et est affect&eacute; au d&eacute;partement du d&eacute;posant (un bi-appartenant doit choisir le d&eacute;partement pour lequel il r&eacute;alise son d&eacute;p&ocirc;t). Il peut mentionner un autre Chercheur en tant que R&eacute;f&eacute;rent.<br>
            - publier son projet quand il le souhaite : un message est envoy&eacute; &agrave; la liste de diffusion des membres du d&eacute;partement, le projet passe &agrave; l'&eacute;tat &quot;<b>Publi&eacute;</b>&quot; et devient consultable par les 
              chercheurs, doctorants et post-doctorants du d&eacute;partement.<br>
              Les responsables administratif/financier sont aussi destinataires de ce message et vous contacterons si vous demandez un accompagnement.<br>
              Si le projet est modifi&eacute; apr&egrave;s publication, la mention &quot;<b>Publi&eacute; (modifi&eacute; depuis)</b>&quot; est affich&eacute;e.<br>
              Le projet n&rsquo;est plus supprimable apr&egrave;s publication.
            </span>
              </div>
            <script type="text/javascript">
                var sprytooltip_info_projet = new Spry.Widget.Tooltip("info_projet", "#sprytrigger_info_projet", {offsetX:20, offsetY:20});
            </script>
          </td>
			  </tr>
		  </table>
      </td>
		</tr>
    <?php 
		}?>
		<tr>
			<td align="center">
				<table width="100%" border="0" cellpadding="2">
					<tr class="head">
						<td nowrap align="center" class="bleugrascalibri11">N&deg;</td>
						<td nowrap align="center" class="bleugrascalibri11">Acronyme/titre court</td>
						<td nowrap align="center" class="bleugrascalibri11">R&eacute;f&eacute;rent</td>
						<td nowrap align="center"><span class="bleugrascalibri11">Type et Contexte<br>du d&eacute;p&ocirc;t</td> 
						<td nowrap align="center" class="bleugrascalibri11">Date de<br>publication</td>
            <td nowrap align="center" class="bleugrascalibri11"><?php echo $GLOBALS['libcourt_theme_fr'] ?></td>
						<td nowrap align="center" class="bleugrascalibri11">Actions</td>
						<td align="center" class="bleugrascalibri11">Statut</td>
            <?php 
						if($estadmin)
						{?> <td align="center" class="bleugrascalibri11">Mail<br>accompagnement</td>
            		<td align="center" class="bleugrascalibri11">Trait&eacute;</td> 
						<?php 
						}?>					
           </tr>
					<?php 
					$class="even";
					$numrow=0;
					foreach($tab_projet as $codeprojet=>$row_rs_projet)
          { $numrow++;
						$droitmodif=$row_rs_projet['droitmodif'];
						$droitsuppr=$row_rs_projet['droitsuppr'];
						$estcreateur_ou_referent=$row_rs_projet['estcreateur_ou_referent'];
						if($droitmodif)
						{ $onDoubleClick='onDblClick="e(\''.$codeprojet.'\')"';
						}
						else
						{ $onDoubleClick='';
						} 
							
							?>
            <tr class="<?php echo (($projet_ancre==$codeprojet)?'marked':($class=="even"?$class="odd":$class="even")) ?>" id="t<?php echo $numrow ?>" onClick="m(this)" <?php echo $onDoubleClick; ?>>
              <td align="center" class="noircalibri10"><?php echo $codeprojet ?>
              </td>
              <td class="noircalibri10"><?php echo $row_rs_projet['titrecourt']; ?>
              </td>
              <td nowrap class="noircalibri10"><?php echo $row_rs_projet['referentnom']; ?> <?php echo substr($row_rs_projet['referentprenom'],0,1); ?>.
              </td>
              <td align="center" class="noircalibri10"><?php echo $row_rs_projet['libtypeprojet'].' - '.$row_rs_projet['detailtypeprojet'].' - '.$row_rs_projet['libclassif'] ?>
              </td>
              <td align="center" class="noircalibri10">
                <?php echo $row_rs_projet['date_modif'] ?>
              </td>
              <td align="center" class="noircalibri10"><?php	echo $row_rs_projet['libtheme'];?>
              </td>
							<td>
                <table border="0" cellspacing="3" cellpadding="3">
                  <tr>
                    <td align="left" nowrap>
                      <a href="javascript:OuvrirVisible('<?php echo "detailprojet.php?codeprojet=".$codeprojet; ?>','projet')"><img class="icon" width="16" height="16" src="images/b_oeil.png"></a>
                    </td>
                    <td align="center">
                    <?php
                    if($droitmodif)
                    { ?>
                      <form name="edit_projet_<?php echo $codeprojet ?>" method="post" action="edit_projet.php">
                        <input type="hidden" name="action" value="modifier">
                        <input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>">
           							<input type="hidden" name="projet_ancre" id="projet_ancre" value="<?php echo $codeprojet ?>">
                        <input type="image" name="b_modifier" img class="icon" width="16" height="16" src="images/b_edit.png" 
                         alt="Modifier" title="Modifier">
                      </form>
										<?php
                    }
                    else
                    { ?>
                    	<img class="icon" src="images/espaceur.gif" width="16" height="1">
                    <?php
                    }?>
                    </td>
                    <td align="center">
                    <?php
                    if($droitsuppr)
                    {?>
                      <form name="gestionprojets_supprimer_<?php echo $codeprojet ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>">
           							<input type="hidden" name="projet_ancre" id="projet_ancre" value="<?php echo $codeprojet ?>">
                        <input type="image" name="b_supprimer" width="16" height="16" src="images/b_drop.png" 
                          alt="Supprimer" title="Supprimer"  onClick="return(confirm('Voulez-vous supprimer le projet <?php echo $codeprojet ?>'))"/>
                      </form>
 										<?php
                    }
                    else
                    { ?>
                    <img class="icon" src="images/espaceur.gif" width="16" height="1">
                    <?php
                    }?>
                    </td>
                    <td align="center">
                    <?php 
                    if($estcreateur_ou_referent)
                    { if($row_rs_projet['codelibstatutprojet']=='brouillon')
                      { $action="publier";?>
                      <form name="gestionprojets_valider_<?php echo $codeprojet ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
                        <input type="hidden" name="action" value="<?php echo $action ?>">
                        <input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>">
                        <input type="image" name="b_valider" src="images/b_<?php echo $action?>.png" alt="Valider" title="Valider" 
                        onClick="return confirme('publier','<?php echo popup_validation_projet($action,$codeprojet,$tab_infouser) ?>')" />
                      </form>
                    <?php
                      }
                    }
                    else
                    {?><img class="icon" src="images/espaceur.gif" width="60" height="1">
                    <?php
                    }?>
                    </td>
                  </tr>
                </table>
              </td>
              <td class="noircalibri10">
							<?php 
              echo $row_rs_projet['libstatutprojet'];
              if($row_rs_projet['codelibstatutprojet']=='publie' && $row_rs_projet['date_modif']>$row_rs_projet['date_publi']) 	
              { ?>&nbsp;(Modifi&eacute; depuis)
              <?php
              }
              ?>
              </td>
              <?php 
							if($estadmin)
              {?><td align="center">
              	<?php 
								if($row_rs_projet['demandeaccompagnement']=='oui')
								{ if($row_rs_projet['mail_accompagnement_projet']!='oui')
									{?>  <form name="confirmer_action_accompagnement_projet_<?php echo $codeprojet ?>" method="post" action="confirmer_action_projet.php">
											<input type="hidden" name="action" value="mail_accompagnement_projet">
											<input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>">
											<input type="image" name="b_mail_accompagnement_projet" src="images/b_checked_non.png"
										onClick="return confirme('Traite','Une fois le mail d\'accompagnement envoy&eacute;, cette case restera coch&eacute;e')">
										</form>
									 <?php 
									}
									else
									{?><img src="images/b_checked_oui.png">
									<?php 
									}
								}
								else
								{ ?>&nbsp;
									<?php 
								}?>
								</td>
                <td align="center">
              	<?php 
								if($row_rs_projet['demandeaccompagnement']=='oui')
								{ if($row_rs_projet['traite_accompagnement_projet']!='oui')
									{?>  <form name="gestionprojets_traite_accompagnement_projet_<?php echo $codeprojet ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
											<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
										<input type="hidden" name="action" value="traite_accompagnement_projet">
										<input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>">
										<input type="image" name="b_traite_accompagnement_projet" src="images/b_checked_non.png"
										onClick="return confirme('Traite','Un mail d&rsquo;information va &ecirc;tre envoy&eacute; &agrave; la direction du laboratoire.'+'\n'+
																											'Une fois valid&eacute;, cette case restera coch&eacute;e')">
										</form>
									 <?php 
									}
									else
									{?><img src="images/b_checked_oui.png">
									<?php 
									}
								}								
								else
								{ ?>&nbsp;
									<?php 
								}?>
								</td>
              <?php 
							}?>
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
if(isset($rs_projet))mysql_free_result($rs_projet);
if(isset($rs))mysql_free_result($rs);
?>





