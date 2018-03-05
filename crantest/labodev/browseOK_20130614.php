<?php require_once('_const_fonc.php');
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
if(strtolower($tab_infouser['nom'])=='gend')
{ /*foreach($_POST as $key=>$val)
  { echo $key.'=>'.$val.'<br>';
	}*/
}
$rep_upload=$GLOBALS['path_to_rep_upload'].'/uploadfichier';
$erreur="";
$affiche_succes=false;
$form_browse="form_browse";
// Required: anonymous function reference number as explained above.
$funcNum = isset($_GET['CKEditorFuncNum'])?$_GET['CKEditorFuncNum']:"" ;
// Optional: instance name (might be used to load a specific configuration file or anything else).
$CKEditor = isset($_GET['CKEditor'])?$_GET['CKEditor']:"" ;
// Optional: might be used to provide localized messages.
$langCode = isset($_GET['langCode'])?$_GET['langCode']:"" ;
if(isset($_POST["MM_update"]) && $_POST["MM_update"] == $_SERVER['PHP_SELF']) 
{	foreach ($_FILES["pj"]["name"] as $key => $nom) 
	{ if($nom!='')
		{ clearstatcache();//efface le cache relatif a l'existence des repertoires
			if(!is_dir($rep_upload))//teste si existe 
			{ mkdir ($rep_upload);
				$rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
				$row_rs=mysql_fetch_array($rs);
				$code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
				$type_rep_ou_fichier='R';
				$updateSQL="insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
										" values (".GetSQLValueString($code_rep_ou_fichier, "text").",".GetSQLValueString('00000', "text").",".GetSQLValueString($type_rep_ou_fichier, "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($codeuser, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}			
			$rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
			$row_rs=mysql_fetch_array($rs);
			$code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
			$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$code_rep_ou_fichier);
			$codepere=$codeuser;
			$type_rep_ou_fichier='F';
			if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
			{	$updateSQL="insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
										" values (".GetSQLValueString($code_rep_ou_fichier, "text").",".GetSQLValueString($codepere, "text").",".GetSQLValueString($type_rep_ou_fichier, "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").",".GetSQLValueString($_POST['descr'], "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}
			else if($tab_res_upload['nomfichier']!='')
			{ $erreur.='<br>'.$tab_res_upload['erreur'];
			}
		}
	}
}
// données du formulaire
$rs_uploadfichier=mysql_query("select * from uploadfichier order by nom");
$nbfichiers=mysql_num_rows($rs_uploadfichier);
$tab_rep_ou_fichier_trie=array();
while($row_rs_uploadfichier=mysql_fetch_assoc($rs_uploadfichier))
{ $tab_rep_ou_fichier_trie[$row_rs_uploadfichier['code_rep_ou_fichier']]=$row_rs_uploadfichier;
}
 
$rs_uploadfichier=mysql_query("select code_rep_ou_fichier,codepere from uploadfichier order by codepere,nom");
$nbfichiers=mysql_num_rows($rs_uploadfichier);
$tab_noeuds=array();
while($row_rs_uploadfichier=mysql_fetch_assoc($rs_uploadfichier))
{ $tab_noeuds[$row_rs_uploadfichier['code_rep_ou_fichier']]=$row_rs_uploadfichier['codepere'];
} 

$tab_arborescence[0]=array('profondeur'=>0,'nom'=>'/');
function affiche_arborescence($noeud,$profondeur)
{ $liste_enfants=liste_enfants($noeud,'R');
	if($liste_enfants==vide())
	{ $tab_arborescence[$nbnoeuds]=array('profondeur'=>$profondeur,'nom'=>$tab_rep_ou_fichier_trie[$noeud]['nom']);
		return;
	}
	else
	{ $profondeur++;
		foreach($liste_enfants as $noeud)
		{ affiche_arborescence($noeud,$profondeur);
		}
		$profondeur--;
	}
}
$pointeur_noeud=0;
function liste_enfants($noeud)
{ $nbenfants=0;
	$liste_enfants=array();
	while(list($un_noeud,$pere)=each($tab_noeuds))
	{ if($noeud==$pere)
		{ $liste_enfants[]=$un_noeud;
		}
	}
	return $liste_enfants;
}
$nbcol=8;
$nbligne=(int)$nbfichiers/$nbcol+1;
 // rep contenant les fichiers
$tmp=getcwd().'\\tmp';
$largeur_vignette_max=200;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>doc/images</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $charset ?>" />
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
</head>

<body>
<form name="<?php echo $form_browse ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
<table border="1">
  <tr>
    <td>&nbsp;</td>
    <td>
      <?php 
      if($erreur!='' || $affiche_succes)
      { ?>
      <table border="0" align="center" cellspacing="5" >
        <tr>
          <?php 
          if($erreur!='')
          { ?>
          <td valign="top"><img src="images/attention.png" align="top"></td>
          <td valign="top">
            <span class="rougecalibri9">L&rsquo;enregistrement n&rsquo;a pas &eacute;t&eacute; effectu&eacute;. Veuillez corriger les erreurs.</span>
            <span class="rougecalibri9"><?php echo $erreur ?></span>
            </td>
          <?php 
          } 
          else if($affiche_succes)
          { ?>
          <td valign="top"><img src="images/succes.png" align="top"></td>
          <td valign="top">
            <span class="vertcalibri11">Enregistrement effectu&eacute; avec succ&egrave;s.</span>
            </td>
          <?php 
          } ?> 
          </tr>
        </table>
      <?php 
    }?>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
			<span class="bleucalibri10">Cliquez sur un fichier pour le s&eacute;lectionner :</span>
    </td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td>
      <table border="0">
        <?php
				$ligne=0;
				$col=0;
				$sautligne=false;
				$first=true;
        foreach ($tab_rep_ou_fichier_trie as $code_rep_ou_fichier=>$un_rep_ou_fichier) 
        { $nom= $un_rep_ou_fichier['nom'];
          $descr= $un_rep_ou_fichier['descr'];
					/* // copie dans rep tmp
					if (!copy($rep_upload.'/'.$code_rep_ou_fichier, $tmp.'\\'.$code_rep_ou_fichier))
					{ echo "La copie du fichier ".$code_rep_ou_fichier." n'a pas réussi...<br>";
					} */
					$affiche=false;
					if($estimage=getimagesize($rep_upload.'/'.$code_rep_ou_fichier))
					{ $tab=getimagesize($rep_upload.'/'.$code_rep_ou_fichier);
						$width=$tab[0];
						$height=$tab[1];
						$largeur_vignette=$width;
						$hauteur_vignette=$height;
						if($width>$largeur_vignette_max)
						{ $hauteur_vignette=$height*($largeur_vignette_max/$width);
							$largeur_vignette=$largeur_vignette_max;
						}
						$affiche=true;
					}
					if($affiche)
					{ $col++;
						if($col>$nbcol)
						{ $col=1;
							$sautligne=true;
						}
						if($first)
						{?>
            <tr>
            <?php 
						}
						$first=false;
						?>
							<td class="bleucalibri10" align="center">
								<?php 
								if($estimage) 
								{?>	<img src="tmp/<?php echo $code_rep_ou_fichier ?>" 
                		width="<?php echo $largeur_vignette ?>" 
                    height="<?php echo $hauteur_vignette ?>"
                    id="sprytrigger_image<?php echo $code_rep_ou_fichier ?>"
                    >
                    <div class="tooltipContent_cadre" id="image<?php echo $code_rep_ou_fichier ?>">
                      <img src="tmp/<?php echo $code_rep_ou_fichier ?>" 
                      width="<?php echo $largeur_vignette*2 ?>" 
                      height="<?php echo $hauteur_vignette*2 ?>"
                      >														
                    </div>
										<script type="text/javascript">
											var sprytooltip_image<?php echo $code_rep_ou_fichier ?> = new Spry.Widget.Tooltip("image<?php echo $code_rep_ou_fichier ?>", "#sprytrigger_image<?php echo $code_rep_ou_fichier ?>", {offsetX:-100, offsetY:20});
										</script>

                    <br>
								<?php 
								} //echo $tab_rep_ou_fichier['descr'] ?>
							<a href="" onclick="window.opener.CKEDITOR.tools.callFunction(<?php echo $funcNum ?>, '/<?php echo $GLOBALS['rep_racine_monsite'] ?>/download_public.php?codeuploadfichier=<?php echo $code_rep_ou_fichier ?>');window.close()"><?php echo $nom ?></a>
							</td>
						<?php 
						if($sautligne)
						{ ?>
							</tr>
              <tr>
						<?php 
						}
					}
					?>
        <?php
        }
        
        ?>
        <tr>
        	<td colspan="<?php echo $nbcol ?>">
        		<table>
          		<tr>
                <td><span class="bleucalibri10">Description :</span> <input type="text" name="descr" class="noircalibri9" id="descr">
                </td>
                <td><span class="bleucalibri10">Fichier:</span><input type="file" name="pj[]" class="noircalibri8" id="pj[]">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
        	<td colspan="<?php echo $nbcol ?>">
          	<input name="submit_enregistrer" type="submit" class="noircalibri8" id="submit_enregistrer" value="Enregistrer le fichier" />
        	</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
</body>
</html>
<?php 
if(isset($rs_uploadfichier)) {mysql_free_result($rs_uploadfichier);}
?>