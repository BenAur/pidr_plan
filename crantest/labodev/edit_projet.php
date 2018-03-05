<?php require_once('_const_fonc.php'); ?>
<?php
$erreur="";
$warning='';
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche="";
$selected="";$checked="";// utilise dans les listes select et checked
$first=false;//utilise dans la construction d'une chaine de caracteres : le dernier d'une liste n'es pas suivi d'une , ou d'un espace dans un while
$form_projet='form_projet';
$tab_champs_numerique=array('montant_total'=>array('lib' => 'Montant total','string_format'=>'%01.2f','max_length'=>12),
														 'montant_labo'=>array('lib' => 'Montant laboratoire','string_format'=>'%01.2f','max_length'=>12),
														 'duree_mois' =>  array('lib' => 'Dur&eacute;e en mois','max_length'=>5));
$tab_champs_date=array( 'datedeb_projet' =>  array("lib" => "Date d&eacute;but projet","jj" => "","mm" => "","aaaa" => ""),
												'datelimite_depot_projet'  =>  array("lib" => "Date limite de d&eacute;p&ocirc;t du projet","jj" => "","mm" => "","aaaa" => ""),
												'datedepot_projet'  =>  array("lib" => "Date de d&eacute;p&ocirc;t du projet","jj" => "","mm" => "","aaaa" => ""));

$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estrespthemeduprojet=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent, $estrespthemeduprojet);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

/*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}*/
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codeprojet=isset($_GET['codeprojet'])?$_GET['codeprojet']:(isset($_POST['codeprojet'])?$_POST['codeprojet']:"");
$projet_ancre=isset($_GET['projet_ancre'])?$_GET['projet_ancre']:(isset($_POST['projet_ancre'])?$_POST['projet_ancre']:"");
$codetheme=isset($_GET['codetheme'])?$_GET['codetheme']:(isset($_POST['codetheme'])?$_POST['codetheme']:"");
$supprimer_une_pj=false;// $supprimer_une_pj=true si $submit='submit_supprimer_une_pj'
$submit="";$submit_val="";

foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $submit=substr($postkey,0,$posdoublediese);
			$posdiese=strpos($submit,"#");
			if($posdiese!=false)
			{ $submit_val=substr($submit,$posdiese+1);//peut etre un numero ou autre (oui/non pour submit_verrouiller_sujet)
				$submit=substr($submit,0,$posdiese);
			}
		}
	}
}

if($submit=='submit_supprimer_une_pj')
{ $supprimer_une_pj=true;
	$codetypepj=$submit_val;
}
// si le user est dans plus d'un theme, il doit en choisir un avant d'accéder r la fiche
// une fois choisi, le codetheme sera !=''
$choix_theme_a_faire=false;
if($action=='creer')
{ if($codetheme=='')
	{ $query_rs = "select codetheme from individutheme where codeindividu=".GetSQLValueString($codeuser, "text").
								" and codetheme in (select codestructure from structure where codestructure<>'00' and esttheme='oui' ".
								"										and  ".intersectionperiodes('structure.date_deb','structure.date_fin','"'.date('Y/m/d').'"','"'.date('Y/m/d').'"').")";
		$rs=mysql_query($query_rs);
		if(mysql_num_rows($rs)>1)
		{ $choix_theme_a_faire=true;
		}
		else if(mysql_num_rows($rs)==1)
		{ $row_rs=mysql_fetch_assoc($rs);
			$codetheme=$row_rs['codetheme'];// le codetheme du user qui soit un dept
		}
	}
	
	if((isset($_POST["MM_choix"])) && ($_POST["MM_choix"] == "edit_projet_choix_theme"))
	{ $choix_theme_a_faire=false;
	}
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "edit_projet")) 
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_projet($_POST,$tab_controle_et_format);

//$erreur='erreur forcée';
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		if($action=="creer")//creation
		{ $rs=mysql_query("select max(codeprojet) as currentnumber from projet") or  die(mysql_error());
			$row_rs=mysql_fetch_assoc($rs);
			$codeprojet=$row_rs['currentnumber'];
			$codeprojet=str_pad((string)((int)$codeprojet+1), 5, "0", STR_PAD_LEFT);  
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM projet');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codeprojet')
				{ $liste_val.=GetSQLValueString($codeprojet, "text");
				}
				else if($row_rs_fields['Field']=='codecreateur')
				{ $liste_val.=GetSQLValueString($codeuser, "text");
				}
				else if($row_rs_fields['Field']=='date_creation')
				{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into projet (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			
			$action="modifier";
		}//fin if creation
		$updateSQL = "UPDATE projet SET ";
		$rs_fields = mysql_query('SHOW COLUMNS FROM projet');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $Field=$row_rs_fields['Field'];
			if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'])))
			{ //les donnees codepropprojet codecreateur date_creation ne sont plus jamais modifiees : uniquement en creation en insert
				if(!in_array($Field,array("codeprojet","codecreateur","date_creation")))//pas de mise a jour de ces champs
				{ $updateSQL.=$Field."=";
					if(array_key_exists($Field,$tab_champs_date)!==false)
					{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
					}
					else
					{ $updateSQL.=GetSQLValueString($_POST[$Field], "text");
					}
					$updateSQL.=",";
				}
			}
			else if($Field=='codemodifieur')
			{ $updateSQL.=$Field."=".GetSQLValueString($codeuser, "text").",";
			}
			else if($Field=='date_modif')
			{ $updateSQL.=$Field."=".GetSQLValueString(date("Y/m/d"), "text").",";
			}
		}
		$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
		$updateSQL.=" WHERE codeprojet=".GetSQLValueString($codeprojet, "text");
		mysql_query($updateSQL) or die(mysql_error());
	}// Fin de traitement des données si pas d'erreur
	
	// suppression pj (et rep(s) si vide)
	if($supprimer_une_pj)
	{ $updateSQL ="delete from projetpj ".
								" where codeprojet=".GetSQLValueString($codeprojet, "text").
								" and codetypepj=".GetSQLValueString($codetypepj, "text");
		mysql_query($updateSQL) or die(mysql_error());
		unlink($GLOBALS['path_to_rep_upload'] .'/projet/'.$codeprojet.'/'.$codetypepj);
	}
	
	
	foreach ($_FILES["pj"]["name"] as $key => $nomfichier)//$key=codetypepj 
	{ $codetypepj=$key;
		if($nomfichier!='')
		{ clearstatcache();//efface le cache relatif a l'existence des repertoires
			$rep_upload=$GLOBALS['path_to_rep_upload'].'/projet/'.$codeprojet ;
			if(!is_dir($rep_upload))//teste si existe 
			{ mkdir ($rep_upload);
			}
			$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$codetypepj);//$key=$codetypepj pour projet
			if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
			{	// si existe deja
				$updateSQL= "delete from projetpj where codeprojet=".GetSQLValueString($codeprojet, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text");
				mysql_query($updateSQL) or die(mysql_error());
				$updateSQL="insert into projetpj (codeprojet,codetypepj,nomfichier)". 
										" values (".GetSQLValueString($codeprojet, "text").",".GetSQLValueString($codetypepj,"text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}
			else if($tab_res_upload['nomfichier']!='')
			{ $warning.='<br>'.$tab_res_upload['erreur'];
			}
		}
	}
}
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations de la proposition (un enreg. vide dans projet pour "creer")
$rs_projet=mysql_query("select projet.*  from projet where projet.codeprojet=".GetSQLValueString($codeprojet,"text")) or die(mysql_error());
$row_rs_projet=mysql_fetch_assoc($rs_projet);

if($action=="creer")
{ $row_rs_projet['codecreateur']=$codeuser;
	$row_rs_projet['codemodifieur']=$codeuser;
	$row_rs_projet['codereferent']=$codeuser;
	$row_rs_projet['codetheme']=$codetheme;
}
if($erreur=='')
{ /*// Liste des codetheme du projet 
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_projettheme[$row_rs['codetheme']]=$row_rs['codetheme'];
	} */
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_projet = mysql_query('SHOW COLUMNS FROM projet');
	while($row_rs_fields_projet = mysql_fetch_assoc($rs_fields_projet)) 
	{ $Field=$row_rs_fields_projet['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_projet[$Field]=$_POST[$Field];
		}
		if(in_array($Field,array("datedeb_projet"))!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_projet[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
}

$rs=mysql_query("select libcourt_fr from structure where codestructure=".GetSQLValueString($row_rs_projet['codetheme'],"text")) or die(mysql_error());
$row_rs= mysql_fetch_assoc($rs);
$row_rs_projet['libtheme']=$row_rs['libcourt_fr'];
$rs=mysql_query("select nom, prenom from individu where codeindividu=".GetSQLValueString($row_rs_projet['codecreateur'],"text")) or die(mysql_error());
$row_rs= mysql_fetch_assoc($rs);
$row_rs_projet['createurnom']=$row_rs['nom'];
$row_rs_projet['createurprenom']=$row_rs['prenom'];
$rs=mysql_query("select nom, prenom from individu where codeindividu=".GetSQLValueString($row_rs_projet['codemodifieur'],"text")) or die(mysql_error());
$row_rs= mysql_fetch_assoc($rs);
$row_rs_projet['modifieurnom']=$row_rs['nom'];
$row_rs_projet['modifieurprenom']=$row_rs['prenom'];

// Liste des membres labo. 'EC,CHERCHEUR,ITARF' y compris le referent si parti
$query_rs_referent="SELECT distinct individu.codeindividu as codereferent,nom,prenom,concat(nom,' ',substr(prenom,1,1),'. ') as nomprenom".
									 " FROM individu,individusejour, individutheme, corps, cat".
									 " WHERE individu.codeindividu=individusejour.codeindividu".
									 " and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat and  FIND_IN_SET(cat.codelibcat,'EC,CHERCHEUR,ITARF')".
									 " and individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour".
									 " and individutheme.codetheme=".GetSQLValueString($row_rs_projet['codetheme'], "text").
									 " and (individu.codeindividu in (select codereferent from projet where codeprojet=".GetSQLValueString($codeprojet, "text").")".
									 " 			or ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_projet['datedeb_projet']."'","'".$row_rs_projet['datedeb_projet']."'").
									 			")".
									 " ORDER BY nom,prenom";
$rs_referent=mysql_query($query_rs_referent) or die(mysql_error());

$rs_classif=mysql_query("select codeclassif,libcourtclassif as libclassif from cont_classif");

$rs_implication=mysql_query("select codeimplication,libimplication from proj_implication order by numordre");

$rs_typeprojet=mysql_query("select codetypeprojet,codelibtypeprojet,libtypeprojet from proj_typeprojet order by numordre");

$query_rs_theme = "select codestructure as codetheme,libcourt_fr as libtheme".
									" from structure where codestructure<>'00' and esttheme='oui' ".
									"	and  ".intersectionperiodes('structure.date_deb','structure.date_fin','"'.$row_rs_projet['datedeb_projet'].'"','"'.$row_rs_projet['datedeb_projet'].'"').
									" order by codestructure";
$rs_theme=mysql_query($query_rs_theme);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
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
</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>
>
  <table width="60%" border="0" align="center" cellpadding="3">
	<?php echo entete_page(array('image'=>'images/b_document.png','titrepage'=>'Fiche projet  <span class="mauvegrascalibri11">'.$row_rs_projet['libtheme'].'</span>',
																'lienretour'=>'gestionprojets.php','texteretour'=>'Retour &agrave; la gestion des propositions de projets',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
			<?php 
      if($choix_theme_a_faire)
      {?>
      <tr>
        <td>
      		</span><span class="bleugrascalibri10">Vous &ecirc;tes bi-appartenant, veuillez choisir :&nbsp;</span>
          <form name="edit_projet_choix_theme"  action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
            <input type="hidden" name="MM_choix" value="edit_projet_choix_theme">
            <input type="hidden" name="action" value="creer">
            <select name="codetheme" class="noircalibri10" id="codetheme" >
            <?php
            while($row_rs_theme=mysql_fetch_assoc($rs_theme))
            { if(in_array($row_rs_theme['codetheme'],$tab_infouser['codetheme']))
              { ?>
              <option value="<?php echo $row_rs_theme['codetheme'] ?>"><?php echo $row_rs_theme['libtheme'] ?></option>
              <?php
              }
            } ?>
            </select>
          <input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Envoyer" >
          </form>
        </td>
      </tr>
      <?php 
			}
			else
			{ ?>
      <tr>
        <td>
        <form name="<?php echo $form_projet ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="return controle_form_projet('<?php echo $form_projet ?>')">
        <input type="hidden" name="action" value="<?php echo $action ?>">
        <input type="hidden" name="codeprojet" value="<?php echo $codeprojet ?>" >
        <input type="hidden" name="MM_update" value="edit_projet">
        <input type="hidden" name="codetheme" value="<?php echo $row_rs_projet['codetheme'] ?>">
        <input type="hidden" name="demandeaccompagnement" value="<?php echo $row_rs_projet['demandeaccompagnement'] ?>">
            <span class="bleucalibri9">N&deg; interne : </span>
            <span class="mauvegrascalibri9"><?php echo $action=='creer'?'A cr&eacute;er':$row_rs_projet['codeprojet'] ?></span>
            <span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
            <span class="infomauve"><?php echo $row_rs_projet['createurprenom']." ".$row_rs_projet['createurnom']; ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_projet['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_projet['date_creation'],"/")) ?></span>
            <img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
            <span class="infomauve"><?php echo $row_rs_projet['modifieurprenom']." ".$row_rs_projet['modifieurnom']; ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_projet['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_projet['date_modif'],"/")) ?></span>
         </td>
       </tr>
      <tr>
        <td>
          <table border="0">
            <tr>
              <td><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Porteur du projet</span><span class="bleugrascalibri10"> :&nbsp;</span>
                <select name="codereferent" class="noircalibri10" id="codereferent" >
                <?php
                while($row_rs_referent=mysql_fetch_assoc($rs_referent))
                { ?>
                  <option value="<?php echo $row_rs_referent['codereferent'] ?>" <?php echo ($row_rs_projet['codereferent']==$row_rs_referent['codereferent']?'selected':'') ?>><?php echo $row_rs_referent['nomprenom'] ?></option>
                  <?php
                }?>
                </select>
              </td>
              <td><img src="images/espaceur.gif" width="20"><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_demandeaccompagnement">
              <div class="tooltipContent_cadre" id="info_demandeaccompagnement">
              <span class="noircalibri10">Les responsables administratif/financier sont destinataires du message lors de la publication du projet<br>
              														et vous contacterons si vous demandez un accompagnement.
              </span>
              </div>
              <script type="text/javascript">
                var sprytooltip_info_demandeaccompagnement = new Spry.Widget.Tooltip("info_demandeaccompagnement", "#sprytrigger_info_demandeaccompagnement", {offsetX:20, offsetY:20});
              </script>
              	<span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Demande d&rsquo;accompagnement de montage financier/juridique</span><span class="bleugrascalibri10"> :&nbsp;</span>
                <input id="checkbox_demandeaccompagnement" type="checkbox" <?php echo $row_rs_projet['demandeaccompagnement']=='oui'?'checked':'' ?>
                onChange="if(this.checked)
                					{ document.forms['<?php echo $form_projet ?>'].elements['demandeaccompagnement'].value='oui'
                          }
                          else
                					{ document.forms['<?php echo $form_projet ?>'].elements['demandeaccompagnement'].value='non'
                          }
                          "
                >
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0">
            <tr>
              <td><span class="rougegrascalibri9"><sup>*</sup><span class="bleugrascalibri10">Contexte du d&eacute;p&ocirc;t</span></span><span class="bleugrascalibri10"> :&nbsp;</span>
              <select name="codeclassif" class="noircalibri10" id="codeclassif" >
              <?php
              while($row_rs_classif=mysql_fetch_assoc($rs_classif))
              { ?>
                <option value="<?php echo $row_rs_classif['codeclassif'] ?>" <?php echo ($row_rs_projet['codeclassif']==$row_rs_classif['codeclassif']?'selected':'') ?>><?php echo $row_rs_classif['libclassif'] ?></option>
                <?php
              }?>
              </select>
              </td>
              <td><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Niveau d'implication</span><span class="bleugrascalibri10"> :&nbsp;</span>
              <select name="codeimplication" class="noircalibri10" id="codeimplication" >
              <?php
              while($row_rs_implication=mysql_fetch_assoc($rs_implication))
              { ?>
                <option value="<?php echo $row_rs_implication['codeimplication'] ?>" <?php echo ($row_rs_projet['codeimplication']==$row_rs_implication['codeimplication']?'selected':'') ?>><?php echo $row_rs_implication['libimplication'] ?></option>
                <?php
              }?>
              </select>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0">
          	<tr>
            	<td nowrap><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Type</span><span class="bleugrascalibri10"> :&nbsp;</span>
                  <select name="codetypeprojet" class="noircalibri10" id="codetypeprojet"
                  onChange="if(this.value=='')
                  					{ document.getElementById('divcodetypeprojet#01').className='cache'
                            	document.getElementById('divcodetypeprojet#02').className='cache'
                              document.getElementById('divdetailtypeprojet').className='cache'
                            }
                            else 
                            { document.getElementById('divdetailtypeprojet').className='affiche'
                            	if(this.value=='01')
                              { document.getElementById('divcodetypeprojet#01').className='affiche'
                                document.getElementById('divdetailtexte#01').className='affiche'
                                document.getElementById('divcodetypeprojet#02').className='cache'
                                document.getElementById('divdetailtexte#02').className='cache'
                              }
                              else if(this.value=='02')
                              { document.getElementById('divcodetypeprojet#01').className='cache'
                                document.getElementById('divdetailtexte#01').className='cache'
                                document.getElementById('divcodetypeprojet#02').className='affiche'
                                document.getElementById('divdetailtexte#02').className='affiche'
                              }
                            }
                            ">
                  <?php
									$divcodelibtypeprojet=='';
                  while($row_rs_typeprojet=mysql_fetch_assoc($rs_typeprojet))
                  { ?>
                    <option value="<?php echo $row_rs_typeprojet['codetypeprojet'] ?>" <?php echo($row_rs_projet['codetypeprojet']==$row_rs_typeprojet['codetypeprojet']?'selected':'') ?>><?php echo $row_rs_typeprojet['libtypeprojet'] ?>
                    </option>
                    <?php
                  }?>
                  </select>
              </td>
              <td nowrap>
              	<div id='divdetailtypeprojet' class="<?php echo $row_rs_projet['codetypeprojet']!=''?'affiche':'cache' ?>">
                  <span class="bleugrascalibri10">D&eacute;tail&nbsp;</span>
                  <span class="bleucalibri10">
                  <div id='divdetailtexte#01' class="<?php echo $row_rs_projet['codetypeprojet']=='01'?'affiche':'cache' ?>">
                  (collaboration, &eacute;tude, CIFRE, .....)
                  </div>
									<div id='divdetailtexte#02' class="<?php echo $row_rs_projet['codetypeprojet']=='02'?'affiche':'cache' ?>">
                  (ANR, R&eacute;gion, PEPS, ...)
                  </div>
                  </span>
                  <span class="bleugrascalibri10">&nbsp;:&nbsp;</span>
                  <input name="detailtypeprojet" id='detailtypeprojet' type="text" class="noircalibri10" value="<?php echo $row_rs_projet['detailtypeprojet'] ?>" size="50" maxlength="100">
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table border="0">
            <tr>
              <td><div id='divcodetypeprojet#01' class="<?php echo $row_rs_projet['codetypeprojet']=='01'?'affiche':'cache' ?>">
                <table>
                  <tr>
                    <td nowrap class="bleugrascalibri10">Date de d&eacute;but pr&eacute;vue :&nbsp;
                    <input name="datedeb_projet_jj" type="text" class="noircalibri10" id="datedeb_projet_jj" value="<?php echo substr($row_rs_projet['datedeb_projet'],8,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_projet_mm" type="text" class="noircalibri10" id="datedeb_projet_mm" value="<?php echo substr($row_rs_projet['datedeb_projet'],5,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_projet_aaaa" type="text" class="noircalibri10" id="datedeb_projet_aaaa" value="<?php echo substr($row_rs_projet['datedeb_projet'],0,4); ?>" size="4" maxlength="4">
                    </td>
                    <td nowrap><span class="bleugrascalibri10">Dur&eacute;e en mois :&nbsp;</span>
                      <input name="duree_mois"  type="text" class="noircalibri10" value="<?php echo $row_rs_projet['duree_mois'] ?>" size="3" maxlength="5" >
                    </td>
                  </tr>
            		</table>
              </td>
              </div>
              <td>
              <div id='divcodetypeprojet#02' class="<?php echo $row_rs_projet['codetypeprojet']=='02'?'affiche':'cache' ?>">
                <table>
                  <tr>
                    <td nowrap class="bleugrascalibri10">Date limite de d&eacute;p&ocirc;t du projet :&nbsp;
                    <input name="datelimite_depot_projet_jj" type="text" class="noircalibri10" id="datelimite_depot_projet_jj" value="<?php echo substr($row_rs_projet['datelimite_depot_projet'],8,2); ?>" size="2" maxlength="2">
                    <input name="datelimite_depot_projet_mm" type="text" class="noircalibri10" id="datelimite_depot_projet_mm" value="<?php echo substr($row_rs_projet['datelimite_depot_projet'],5,2); ?>" size="2" maxlength="2">
                    <input name="datelimite_depot_projet_aaaa" type="text" class="noircalibri10" id="datelimite_depot_projet_aaaa" value="<?php echo substr($row_rs_projet['datelimite_depot_projet'],0,4); ?>" size="4" maxlength="4">
                    </td>
                  </tr>
            		</table>
              </td>
              </div>
              <td>
                <table>
                  <tr>
                    <td nowrap class="bleugrascalibri10">Date de d&eacute;p&ocirc;t du projet si d&eacute;j&agrave; d&eacute;pos&eacute; :&nbsp;
                    <input name="datedepot_projet_jj" type="text" class="noircalibri10" id="datedepot_projet_jj" value="<?php echo substr($row_rs_projet['datedepot_projet'],8,2); ?>" size="2" maxlength="2">
                    <input name="datedepot_projet_mm" type="text" class="noircalibri10" id="datedepot_projet_mm" value="<?php echo substr($row_rs_projet['datedepot_projet'],5,2); ?>" size="2" maxlength="2">
                    <input name="datedepot_projet_aaaa" type="text" class="noircalibri10" id="datedepot_projet_aaaa" value="<?php echo substr($row_rs_projet['datedepot_projet'],0,4); ?>" size="4" maxlength="4">
                    </td>
                  </tr>
            		</table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
        <table border="0">
          <tr>
          <td><span class="bleugrascalibri10">Montant total :&nbsp;
            <input name="montant_total"  type="text" class="noircalibri10" value="<?php echo $row_rs_projet['montant_total'] ?>" size="12" maxlength="12" >
          </span>
          </td>
          <td><span class="bleugrascalibri10">Montant laboratoire :&nbsp;
            <input name="montant_labo"  type="text" class="noircalibri10" value="<?php echo $row_rs_projet['montant_labo'] ?>" size="12" maxlength="12" >
          </span>
          </td>
          </tr>
        </table>
        </td>
      </tr>
      <tr>
        <td><span class="bleugrascalibri10"><span class="rougegrascalibri9"><sup>*</sup></span>Acronyme ou titre court :&nbsp; (50 car. max.)</span>
          <input name="titrecourt"  type="text" class="noircalibri10"  value="<?php echo $row_rs_projet['titrecourt'] ?>" size="50" maxlength="50">
        </td>
      </tr>
      <tr>
        <td><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Intitul&eacute; du projet/contrat/&eacute;tude d&eacute;pos&eacute;</span><span class="bleugrascalibri10"> (</span><span id="titre#nbcar_js" class="bleugrascalibri10"><?php echo strlen($row_rs_projet['titre']) ?> </span><span class="bleugrascalibri10">/200 car. max.) :</span></td>
      </tr>
      <tr>
        <td>
          <textarea name="titre" cols="100" rows="1" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'titre#nbcar_js'","'bleugrascalibri10'","'rougegrascalibri11'") ?>><?php echo $row_rs_projet['titre'] ?></textarea>
        </td>
      </tr>
      <tr>
        <td><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10">Partenaires</span><span class="bleugrascalibri10"> (</span><span id="partenaires#nbcar_js" class="bleugrascalibri10"><?php echo strlen($row_rs_projet['partenaires']) ?> </span><span class="bleugrascalibri10">/500 car. max.) :</span></td>
      </tr>
      <tr>
        <td>
          <textarea name="partenaires" cols="100" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","500","'partenaires#nbcar_js'","'bleugrascalibri10'","'rougegrascalibri11'") ?>><?php echo $row_rs_projet['partenaires'] ?></textarea>
        </td>
      </tr>
      <tr>
        <td><span class="bleugrascalibri10">Description ( </span><span id="descr#nbcar_js" class="bleugrascalibri10"><?php echo strlen($row_rs_projet['descr']) ?></span><span class="bleugrascalibri10">/4000 car. max.) :</span></td>
      </tr>
      <tr>
        <td>
          <textarea name="descr" cols="100" rows="10" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","4000","'descr#nbcar_js'","'bleugrascalibri10'","'rougegrascalibri11'") ?>><?php echo $row_rs_projet['descr'] ?></textarea>
        </td>
      </tr>
      <tr>
        <td><span class="bleugrascalibri10">Sp&eacute;cificit&eacute;s en fran&ccedil;ais ( </span><span id="specificites#nbcar_js" class="bleugrascalibri10"><?php echo strlen($row_rs_projet['specificites']) ?></span><span class="bleugrascalibri10">/1000 car. max.) :</span></td>
      </tr>
      <tr>
        <td><textarea name="specificites" cols="100" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","1000","'specificites#nbcar_js'","'bleugrascalibri10'","'rougegrascalibri11'") ?>><?php echo $row_rs_projet['specificites'] ?></textarea>
				</td>
      </tr>
      <tr>
        <td>
          <table>
            <tr>
              <td>
                <span class="bleugrascalibri10">Notes (</span><span id="note#nbcar_js" class="bleugrascalibri10"><?php echo strlen($row_rs_projet['note']) ?></span><span class="bleugrascalibri10">/1000&nbsp;car.&nbsp;max.) :</span><br>
                <textarea name="note" cols="80" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","1000","'note#nbcar_js'","'bleugrascalibri10'","'rougegrascalibri11'") ?>><?php echo $row_rs_projet['note'] ?></textarea>
              </td>
              <td>
                <table>
                  <tr>
                    <td nowrap><span class="bleugrascalibri10">Pi&egrave;ces jointes :</span>
                    </td>
                  </tr>
                  <tr>
                    <td nowrap>
                      <?php
                      echo ligne_txt_upload_pj_projet($codeprojet,'projet','Projet',$form_projet,true);
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td nowrap>
                      <?php
                      echo ligne_txt_upload_pj_projet($codeprojet,'autre','Autre',$form_projet,true);
                      ?>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>
          <input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer" >
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
    <?php 
		} ?>
  </table>
  </form>
</body>
</html>
    <?php
if(isset($rs_classif))mysql_free_result($rs_classif);
if(isset($rs_implication))mysql_free_result($rs_implication);
if(isset($rs_referent))mysql_free_result($rs_referent);
if(isset($rs_structureindividu))mysql_free_result($rs_structureindividu);
if(isset($rs_projet))mysql_free_result($rs_projet);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields_projet))mysql_free_result($rs_fields_projet);
?>
