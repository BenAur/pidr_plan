<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ /*foreach($_POST as $key=>$val)
  { echo $key.'=>'.$val.'<br>';
	}*/
}
$form_actu="edit_actu";
$tab_champs_date=array( 'datedeb_actu' =>  array("lib" => "Date d&eacute;but","jj" => "","mm" => "","aaaa" => ""), 'datefin_actu' =>  array("lib" => "Date fin","jj" => "","mm" => "","aaaa" => ""),
												 'datedeb_actu_affiche' =>  array("lib" => "Date d&eacute;but affiche","jj" => "","mm" => "","aaaa" => ""), 'datefin_actu_affiche' =>  array("lib" => "Date fin affiche","jj" => "","mm" => "","aaaa" => ""));
$tab_champs_heure_mn['heuredeb_actu']=array("lib" => "Heure d&eacute;but","hh" => "","mn" => "");
$tab_champs_heure_mn['heurefin_actu']=array("lib" => "Heure fin","hh" => "","mn" => "");
$affiche_succes=false;
$message_resultat_affiche='';
$erreur="";	$erreur_envoimail="";  
$codeactu=isset($_GET['codeactu'])?$_GET['codeactu']:(isset($_POST['codeactu'])?$_POST['codeactu']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$actu_ancre=isset($_GET['actu_ancre'])?$_GET['actu_ancre']:(isset($_POST['actu_ancre'])?$_POST['actu_ancre']:"");
$codetypeactuevenementgroupe=isset($_GET['codetypeactuevenementgroupe'])?$_GET['codetypeactuevenementgroupe']:(isset($_POST['codetypeactuevenementgroupe'])?$_POST['codetypeactuevenementgroupe']:"");
$codelibtypeactuevenementgroupe="";

if((isset($_POST["MM_update"])) && $_POST["MM_update"] == $_SERVER['PHP_SELF'])
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_heure_mn' => $tab_champs_heure_mn);
	$erreur=controle_form_actu($_POST,$tab_controle_et_format);
	//$erreur="erreur forc&eacute;e";
	if($erreur=="")//Pas d'erreur lors des verifications
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute;e avec succ&egrave;s.';
		if($action=="creer")
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='actu'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codeactu=$row_seq_number['currentnumber'];
			$codeactu=str_pad((string)((int)$codeactu+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codeactu, "text")." where nomtable='actu'") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM actu');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codeactu')
				{ $liste_val.=GetSQLValueString($codeactu, "text");
				}
				else if($row_rs_fields['Field']=='codecreateur' || $row_rs_fields['Field']=='codemodifieur')
				{ $liste_val.=GetSQLValueString($codeuser, "text");
				}
				else if($row_rs_fields['Field']=='date_creation' || $row_rs_fields['Field']=='date_modif')
				{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into actu (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			$action="modifier";
		}
		$updateSQL = "UPDATE actu SET ";
		$rs_fields = mysql_query('SHOW COLUMNS FROM actu');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $Field=$row_rs_fields['Field'];
			if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'])) || 
					(isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
			{ if(!in_array($Field,array("codeactu","codecreateur","date_creation")))//pas de mise a jour de ces champs
				{ $updateSQL.=$Field."=";
					if(array_key_exists($Field,$tab_champs_date)!==false)
					{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
					}
					else if(array_key_exists($Field, $tab_champs_heure_mn)!==false)
					{ $updateSQL.=GetSQLValueString(hhmn2heure($_POST[$Field.'_hh'],$_POST[$Field.'_mn']), "text");
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
		$updateSQL.=" WHERE codeactu=".GetSQLValueString($codeactu, "text");
		
		mysql_query($updateSQL) or die(mysql_error());
	}
}
// ------------------ Envoi des données
$query_rs_actu="select * from actu where codeactu=".GetSQLValueString($codeactu, "text");
$rs_actu=mysql_query($query_rs_actu);
$row_rs_actu=mysql_fetch_assoc($rs_actu);
if($erreur=='')
{	if($action=='creer')
	{ $row_rs_actu['createurnom']=$tab_infouser['nom'];
		$row_rs_actu['createurprenom']=$tab_infouser['prenom'];
		$row_rs_actu['date_creation']=date("Y/m/d");
		$row_rs_actu['modifieurnom']=$tab_infouser['nom'];
		$row_rs_actu['modifieurprenom']=$tab_infouser['prenom'];
		$row_rs_actu['date_modif']=date("Y/m/d");
		$row_rs_actu['datedeb_actu']=date("Y/m/d");
	}
	else if($action=='modifier')
	{ 
	}
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_actu = mysql_query('SHOW COLUMNS FROM actu');
	while($row_rs_fields_actu = mysql_fetch_assoc($rs_fields_actu)) 
	{ $Field=$row_rs_fields_actu['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_actu[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_actu['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_actu[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
}
$query_rs = "SELECT  codelibtypeactuevenementgroupe  FROM typeactuevenementgroupe WHERE codetypeactuevenementgroupe=".GetSQLValueString($codetypeactuevenementgroupe, "text");
$rs = mysql_query($query_rs) or die(mysql_error());
if($row_rs=mysql_fetch_assoc($rs))
{ $codelibtypeactuevenementgroupe=$row_rs['codelibtypeactuevenementgroupe'];
}

$query_rs_individu ="SELECT  createur.nom as createurnom,createur.prenom as createurprenom, modifieur.nom as modifieurnom, modifieur.prenom as modifieurprenom ".
										" FROM actu, individu as createur, individu as modifieur ".
										" WHERE createur.codeindividu=actu.codecreateur and modifieur.codeindividu=actu.codemodifieur".
										" and actu.codeactu = ".GetSQLValueString($codeactu, "text");
$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_actu=array_merge($row_rs_actu,mysql_fetch_assoc($rs_individu));
		
$query_rs_typeactudiffusion = "SELECT codetypeactudiffusion,libcourt as libtypeactudiffusion FROM typeactudiffusion order by libtypeactudiffusion";
$rs_typeactudiffusion = mysql_query($query_rs_typeactudiffusion) or die(mysql_error());


$query_rs_typeactuevenement = "SELECT codetypeactuevenement,libcourt_fr as libtypeactuevenement FROM typeactuevenement where codetypeactuevenement=codetypeactuevenement<>''".
															" and codetypeactuevenementgroupe=".GetSQLValueString($codetypeactuevenementgroupe, "text")." order by libtypeactuevenement";
$rs_typeactuevenement = mysql_query($query_rs_typeactuevenement) or die(mysql_error());

// Liste des themes
$rs_theme = mysql_query("select codestructure as codetheme,libcourt_fr as libtheme from structure".
												" where (esttheme='oui' and ".intersectionperiodes('date_deb','date_fin',"'".$row_rs_actu['datedeb_actu']."'","'".$row_rs_actu['datefin_actu']."'").
												") or codestructure=''".
												" order by codestructure") or die(mysql_error());	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Actualit&eacute; <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="ckeditor/ckeditor.js"></script>
</head>
<body>
<form name="<?php echo $form_actu ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="return controle_form_actu('<?php echo $form_actu ?>')"><!-- -->
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="codeactu" value="<?php echo $codeactu ?>" >
<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="actu_ancre" value="<?php echo $actu_ancre; ?>">
<input type="hidden"  name="codetypeactuevenementgroupe" value="<?php echo $codetypeactuevenementgroupe ?>" />
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_actu.png','titrepage'=>'Actualit&eacute;s','lienretour'=>'gestionactus.php?codetypeactuevenementgroupe='.$codetypeactuevenementgroupe.'&actu_ancre='.$actu_ancre,'texteretour'=>'Retour &agrave; la gestion des actualit&eacute;s',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array(),'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;
      
    </td>
  </tr>
  <tr>
    <td>
      <span class="bleucalibri9">N&deg; interne : </span>
      <span class="mauvegrascalibri9"><?php echo $action=='creer'?'A cr&eacute;er':$row_rs_actu['codeactu'] ?></span>
    </td>
  </tr>
  <tr>
    <td nowrap>
      <span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
      <span class="infomauve"><?php echo ($row_rs_actu['codecreateur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_actu['createurprenom']." ".$row_rs_actu['createurnom']); ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_actu['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_actu['date_creation'],"/")) ?></span>
      <img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
      <span class="infomauve"><?php echo ($row_rs_actu['codemodifieur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_actu['modifieurprenom']." ".$row_rs_actu['modifieurnom']); ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_actu['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_actu['date_modif'],"/")) ?></span>
    </td>
  </tr>
    <tr>
    	<td align="center">
      	<table width="100%" border="0" cellspacing="2" cellpadding="2">
          <?php if($codelibtypeactuevenementgroupe=='actu')
                  { ?><tr>
          	<td colspan="2">
            	<table>
              	<tr>
                	<td class="bleucalibri10">Classement</td>
                  <td>
                    <select name="codetypeactuevenement">
                    <?php while($row_rs_typeactuevenement=mysql_fetch_assoc($rs_typeactuevenement))
                    {?> <option value="<?php echo $row_rs_typeactuevenement['codetypeactuevenement']?>" <?php if($row_rs_typeactuevenement['codetypeactuevenement']==$row_rs_actu['codetypeactuevenement']){?> selected <?php }?>><?php echo $row_rs_typeactuevenement['libtypeactuevenement']?></option>
                    <?php 
                    }?>
                    </select>
                  </td> 
									<td class="bleucalibri10" nowrap>Date d&eacute;but</td>
                  <td nowrap>
                  <input name="datedeb_actu_jj" type="text" class="noircalibri10" id="datedeb_actu_jj" value="<?php echo substr($row_rs_actu['datedeb_actu'],8,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_actu_mm" type="text" class="noircalibri10" id="datedeb_actu_mm" value="<?php echo substr($row_rs_actu['datedeb_actu'],5,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_actu_aaaa" type="text" class="noircalibri10" id="datedeb_actu_aaaa" value="<?php echo substr($row_rs_actu['datedeb_actu'],0,4); ?>" size="4" maxlength="4">
                  </td>
                  <td class="bleucalibri10" nowrap>Heure d&eacute;but <input type="text" class="noircalibri10" name="heuredeb_actu_hh" id="heuredeb_actu_hh" value="<?php echo substr($row_rs_actu['heuredeb_actu'],0,2); ?>" size="2" maxlength="2">
                    h <input type="text" class="noircalibri10" name="heuredeb_actu_mn" id="heuredeb_actu_mn" value="<?php echo substr($row_rs_actu['heuredeb_actu'],3,2); ?>" size="2" maxlength="2">
                  </td>
                  <td class="bleucalibri10" nowrap>Date fin</td>
                  <td nowrap nowrap>
                  <input name="datefin_actu_jj" type="text" class="noircalibri10" id="datefin_actu_jj" value="<?php echo substr($row_rs_actu['datefin_actu'],8,2); ?>" size="2" maxlength="2">
                    <input name="datefin_actu_mm" type="text" class="noircalibri10" id="datefin_actu_mm" value="<?php echo substr($row_rs_actu['datefin_actu'],5,2); ?>" size="2" maxlength="2">
                    <input name="datefin_actu_aaaa" type="text" class="noircalibri10" id="datefin_actu_aaaa" value="<?php echo substr($row_rs_actu['datefin_actu'],0,4); ?>" size="4" maxlength="4">
                  </td>
                  <td class="bleucalibri10" nowrap>Heure  fin<input type="text" class="noircalibri10" name="heurefin_actu_hh" id="heurefin_actu_hh" value="<?php echo substr($row_rs_actu['heurefin_actu'],0,2); ?>" size="2" maxlength="2">
                    h <input type="text" class="noircalibri10" name="heurefin_actu_mn" id="heurefin_actu_mn" value="<?php echo substr($row_rs_actu['heurefin_actu'],3,2); ?>" size="2" maxlength="2">
                  </td>
                  <td class="bleucalibri10" nowrap>Affichage page accueil d&eacute;but</td>
                  <td nowrap>
                  <input name="datedeb_actu_affiche_jj" type="text" class="noircalibri10" id="datedeb_actu_affiche_jj" value="<?php echo substr($row_rs_actu['datedeb_actu_affiche'],8,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_actu_affiche_mm" type="text" class="noircalibri10" id="datedeb_actu_affiche_mm" value="<?php echo substr($row_rs_actu['datedeb_actu_affiche'],5,2); ?>" size="2" maxlength="2">
                    <input name="datedeb_actu_affiche_aaaa" type="text" class="noircalibri10" id="datedeb_actu_affiche_aaaa" value="<?php echo substr($row_rs_actu['datedeb_actu_affiche'],0,4); ?>" size="4" maxlength="4">
                  </td>
                  <td class="bleucalibri10" nowrap>fin</td>
                  <td nowrap>
                  <input name="datefin_actu_affiche_jj" type="text" class="noircalibri10" id="datefin_actu_affiche_jj" value="<?php echo substr($row_rs_actu['datefin_actu_affiche'],8,2); ?>" size="2" maxlength="2">
                    <input name="datefin_actu_affiche_mm" type="text" class="noircalibri10" id="datefin_actu_affiche_mm" value="<?php echo substr($row_rs_actu['datefin_actu_affiche'],5,2); ?>" size="2" maxlength="2">
                    <input name="datefin_actu_affiche_aaaa" type="text" class="noircalibri10" id="datefin_actu_affiche_aaaa" value="<?php echo substr($row_rs_actu['datefin_actu_affiche'],0,4); ?>" size="4" maxlength="4">
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
          	<td class="bleucalibri10">Lieu</td>
            <td>
              <input name="lieu" type="text" class="noircalibri10" id="lieu" value="<?php echo $row_rs_actu['lieu']; ?>" size="100" maxlength="100">
            </td>
          </tr>
          <tr>
          	<td class="bleucalibri10">Intervenants</td>
            <td>
              <input name="lieu" type="text" class="noircalibri10" id="intervenants" value="<?php echo $row_rs_actu['intervenants']; ?>" size="100" maxlength="500">
            </td>
          </tr>
          <tr>
          	<td colspan="2">
            	<table>
              	<tr>
                  <td class="bleucalibri10">Visibilit&eacute;</td>
                  <td>
                    <select name="codetypeactudiffusion">
                    <?php while($row_rs_typeactudiffusion=mysql_fetch_assoc($rs_typeactudiffusion))
                    {?> <option value="<?php echo $row_rs_typeactudiffusion['codetypeactudiffusion']?>" <?php if($row_rs_typeactudiffusion['codetypeactudiffusion']==$row_rs_actu['codetypeactudiffusion']){?> selected <?php }?>><?php echo $row_rs_typeactudiffusion['libtypeactudiffusion']?></option>
                    <?php 
                    }?>
                    </select>
                  </td> 
                  <td class="bleucalibri10">Type manifestation</td>
                  <td>
              			<input name="typemanifestation" type="text" class="noircalibri10" id="typemanifestation" value="<?php echo $row_rs_actu['typemanifestation']; ?>" size="30" maxlength="30">
                  </td> 
                  <td class="bleucalibri10">D&eacute;pt.
                  </td>
                  <td>
                    <select name="codetheme" class="noircalibri10" id="codetheme" >
										<?php
                    while($row_rs_theme=mysql_fetch_assoc($rs_theme))
                    { ?>
                      <option value="<?php echo $row_rs_theme['codetheme'] ?>" <?php echo ($row_rs_actu['codetheme']==$row_rs_theme['codetheme']?'selected':'') ?>><?php echo $row_rs_theme['codetheme']=='00'?$GLOBALS['acronymelabo']:$row_rs_theme['libtheme'] ?></option>
                      <?php
                    } ?>
                    </select>
									</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td class="bleugrascalibri10" valign="top" >Intitul&eacute;
							<!--<br/><span id="titre_fr_nbcar_js" class="noircalibri9"><?php //echo strlen($row_rs_actu['titre_fr']) ?></span><span class="noircalibri9">/200 car. max</span> -->
            </td>
            <td>
            	<textarea name="titre_fr"  class="ckeditor" name="editor1" cols="100" rows="2" <?php echo affiche_longueur_js("this","200","'titre_fr_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_actu['titre_fr'] ?></textarea>
            </td> 
          </tr>
                  <?php }?>
          <tr>
            <td class="bleugrascalibri10" valign="top">Description
							<!--<br/><span id="descr_fr_nbcar_js" class="noircalibri9"><?php //echo strlen($row_rs_actu['descr_fr']) ?></span><span class="noircalibri9">/2000 car. max</span> -->
            </td>
            <td><textarea name="descr_fr"  class="ckeditor" name="editor2" cols="200" rows="20" <?php //echo affiche_longueur_js("this","2000","'descr_fr_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_actu['descr_fr'] ?></textarea> 
            </td> 
          </tr>

          </tr>
          <tr>
            <td>&nbsp;
              </td>
            <td align="right"><input type="submit" name="submit" value="Enregistrer" class="noircalibri10"></td>
          </tr>
				</table>
      </td>
    </tr>	  
  </table>
</form>
	<?php 
  if(isset($rs_theme))mysql_free_result($rs_theme);
  if(isset($rs_typeactudiffusion))mysql_free_result($rs_typeactudiffusion);
  if(isset($rs_typeactuevenement))mysql_free_result($rs_typeactuevenement);
  if(isset($rs_actu))mysql_free_result($rs_actu);
?>
</body>
</html>




