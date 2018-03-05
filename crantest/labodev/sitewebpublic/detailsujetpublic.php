<?php require_once('../_const_fonc.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/cran_base_php.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-latin-1" />
<meta name="description" content="<?php echo meta_description() ?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CRAN- sujet</title>
<!-- InstanceEndEditable -->
<link rel="shortcut icon" href="/cranicon.ico" >
<link href="../styles/normal.css" rel="stylesheet" type="text/css" />
<link href="../styles/tableau_bd.css" rel="stylesheet" type="text/css" />
<link href="../styles/div.css" rel="stylesheet" type="text/css" />
<script src="../_java_script/jquery-min.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css" />
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body style="margin:0; color:#000;background:#FBFBFB;font-family: Calibri;font-size: 11pt;" >
<div style="width:1200px;margin-left: auto;margin-right: auto;position:relative;"><!-- positionnement de l'ensemble -->
  <div class="container" style="width:1200px;heigth:10px;"><img src="../images/espaceur.gif" height="5" border="0"/>
  </div>
  <div class="container">
    <div class="div_gauche" id="logo_cran"><img src="../images/logo_cran_200.png" border="0"/></div>
    <div class="div_gauche" style="width:20px">&nbsp;</div>
    <div class="div_gauche"><a href="<?php echo $racine_site.'../listesujetpublic.php?codelangue='.$codelangue ?>"><img src="images/annonce_sujets_<?php echo strtolower($codelangue) ?>.gif" width="100" height="100"  border="0"/></a></div>
    <div class="div_gauche" style="width:30px">&nbsp;</div>
    <div class="div_gauche"><img src="../images/bandeau_cran.jpg" border="0"/></div>
    <div class="div_gauche" style="width:120px"><a href="../imageactu/Plaquette_Cran.pdf" target="_blank"><img src="images/plaquette_cran_<?php echo strtolower($codelangue) ?>.jpg" /></a></div>
    <div class="div_gauche" align="center" style="width:280px; vertical-align:text-bottom; padding:5pt;text-shadow: 2px 2px 2px #999; font-family:Calibri; color:#0000CC; font-size:13px">CRAN - Campus Sciences<br />
        BP 70239 - 54506 VANDOEUVRE Cedex<br />
        T&eacute;l : +33 (0)3 72 74 52 90 Fax : +33 (0)3 72 74 53 08<br />
    <a href="mailto:cran-secretariat@univ-lorraine.fr">cran-secretariat@univ-lorraine.fr</a>
    </div>
  </div>
  <div class="container">
    <div class="div_gauche" style="width:230px;"><img src="../images/espaceur.gif" height="5" border="0"/>
    </div>
    <div class="div_gauche">
    <a href="<?php echo $racine_site.(($codelangue=='FR')?'anglais/':'').'index.php?codelangue='.($codelangue=='FR'?'EN':'FR'); ?>"><img src="../images/drapeau_fr_en.jpg" border="0"/></a>
    </div>
  </div>
  <div class="container">
    <div class="div_gauche" style="width:200px;text-align:center">&nbsp;</div>
    <div class="div_gauche" style="width:40px">&nbsp;</div>
    <div class="div_gauche"><?php echo affiche_chemin_page($codelangue,$liblangue); ?>
    </div>
  </div>
  <div class="container" id="main">
    <div class="div_gauche" style="width:200px" id="menu_cran">
    <img src="../images/espaceur.gif" height="20" width="200"/>
      <?php
      foreach($tab_menu as $id_menu=>$un_menu )
      { $chemin=$racine_site;
				$target=' target="_self"';
				$lgmaxlib=30;//pour avoir des libelles de meme longueur dans les hrefs des items
				if($id_menu=='1')
				{ if($codelangue=='FR')
					{ $chemin.='';
					}
					else
					{ $chemin.='anglais/';
					}
				}
				else if($id_menu=='3' || $id_menu=='8')
				{ $chemin.='';
				}
				else if($id_menu=='7' || $id_menu=='11')
				{ $chemin='';$target=' target="_blank"';
				}
				else
				{ if($codelangue=='FR')
					{ $chemin.='francais/';
					}
					else
					{ $chemin.='anglais/';
					} 
				}
				if(!($codelangue=='EN' && $id_menu=='11'))
				{?>
        <div class="menuppal"><a href="<?php echo $chemin.$un_menu['../lienmenu'] ?>" class="lienbleucalibri12" <?php echo $target ?>><?php echo $un_menu['libmenu_'.strtolower($codelangue)].str_repeat("&nbsp;", $lgmaxlib-strlen($un_menu['libmenu_'.strtolower($codelangue)]));?></a></div>
        <?php 
				}
      }
      
    /* if($codelangue=='FR')
    {?><div><img src="images/espaceur.gif" width="40" height="40" border="0"/><img src="../images/b_intranet.png" border="0"/></div>
    <?php 
		} */?>
    </div>
		<?php // saut ligne
		if($_SERVER['PHP_SELF']!=$GLOBALS['racine_site'] && $_SERVER['PHP_SELF']!=$GLOBALS['racine_site'].'anglais')
    {?> 
      <div class="div_gauche" style="width:1000px;text-align:center"><img src="../images/espaceur.gif" width="1" heigth="5" border="0"/>
      </div>
    <?php 
    }?>
    <div class="div_gauche" style="width:20px"><img src="../images/espaceur.gif" height="5" border="0"/></div>
    <div class="div_centre" style="width:980px">
    <?php $bandeau=bandeau_theme($codelangue,$liblangue);
			if($bandeau!='') 
			{?><div class="div_gauche" style="width:980px">
				<?php echo $bandeau;?>
				</div>
			 <?php
			}?>
    	<div class="div_gauche" style="width:980px">
    <!-- InstanceBeginEditable name="contenu" -->
<?php     $hashLib=Array();

$appel=(isset($_REQUEST['appel'])?$_REQUEST['appel']:"form_rech");
$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
$codesujet=(isset($_REQUEST['codesujet'])?$_REQUEST['codesujet']:"");
$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");

// recup des libelles des zones de texte de la page
$query_rslib= "select nomzone,contenuzone from libpagehtml ".
							" where (codepagehtml='detailsujetpublic' or codepagehtml='TOUTES') and codelangue=".GetSQLValueString(strtolower($codelangue), "text").
							" order by nomzone";
$rslib=mysql_query($query_rslib, $cran) or die(mysql_error());
while($row_rslib=mysql_fetch_assoc($rslib))
{ $hashLib[$row_rslib['nomzone']]=$row_rslib['contenuzone'];
}?>
<?php
// si pas de codelangue: FR par defaut
if($codelangue=="")
{ $codelangue="FR";
}
// selection de l'enregistrement de ce sujet
$query_rs_sujet= " select typesujet.codetypesujet,codecreateur,titre_".strtolower($codelangue)." as titresujet,codestatutsujet,sujet.codetypesujet,".
								 " typesujet.libcourt_".strtolower($codelangue)." as libtypesujet,".
								 " conditions_".strtolower($codelangue)." as conditions,".
								 " motscles_".strtolower($codelangue)." as motscles,descr_".strtolower($codelangue)." as descr,".
								 " autredir1,autredir1mail,autredir2,autredir2mail,datedeb_sujet,datefin_sujet,".
								 " financement_".strtolower($codelangue)." as financement,ref_publis,ref_publis_ext".
								 " from sujet,typesujet ".
								 " where sujet.codetypesujet=typesujet.codetypesujet and codesujet=".GetSQLValueString($codesujet, "text");
$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
{ $query_rs= "select individu.codeindividu,nom, prenom,datedeb_sejour,datefin_sejour".
						 " from individusujet,individusejour,individu,individustatutvisa,statutvisa". 
						 " where individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
						 " and individusejour.codeindividu=individu.codeindividu".
						 " and individusujet.codesujet =".GetSQLValueString($codesujet, "text").
						 " and individusejour.codeindividu=individustatutvisa.codeindividu".
						 " and individusejour.numsejour=individustatutvisa.numsejour".
						 " and individustatutvisa.codestatutvisa=statutvisa.codestatutvisa".
						 " and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
						 " and statutvisa.coderole='theme'";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$affecte=false;
	if($row_rs=mysql_fetch_assoc($rs))
	{ $affecte=true;
		$row_rs_sujet=array_merge($row_rs_sujet,$row_rs);
	}
	$libtypesujet=($row_rs_sujet['codetypesujet']=='02'?(strtolower($codelangue)=='fr'?'stage':'Trainee'):$row_rs_sujet['libtypesujet']) ?>
  <div class="titre_page_dept"><?php echo $codelangue=='FR'?'Sujet de '.$libtypesujet:$libtypesujet.' Project' ?> : <?php echo $row_rs_sujet['titresujet'] ?></div>
  <div align="justify" style="border-left: solid 1px #CCC;border-right: solid 1px #CCC;border-bottom: solid 1px #CCC">
  <table width="100%" border="0">
    <tr>
      <td valign="top" nowrap class="bleucalibri11">
      	Dates&nbsp;:
      </td>
      <td>
        <?php
            $datedeb_sujet=$row_rs_sujet['datedeb_sujet']; 
						$datefin_sujet=$row_rs_sujet['datefin_sujet'];
						if($affecte)
						{ $datedeb_sejour=$row_rs_sujet['datedeb_sejour'];
							$datefin_sejour=$row_rs_sujet['datefin_sejour'];
							if($row_rs_sujet['codetypesujet']=='04' && $datefin_sujet!='')//postdoc
							{ $duree_sujet=mktime(0,0,0,substr($datefin_sujet,5,2),substr($datefin_sujet,8,2),substr($datefin_sujet,0,4))-mktime(0,0,0,substr($datedeb_sujet,5,2),substr($datedeb_sujet,8,2),substr($datedeb_sujet,0,4));
								$datefin_sujet=min($datefin_sejour,date("Y/m/d",mktime(0,0,0,substr($datedeb_sejour,5,2),substr($datedeb_sejour,8,2),substr($datedeb_sejour,0,4))+$duree_sujet));
							}
							else
							{ $datefin_sujet=$row_rs_sujet['datefin_sejour'];
							}
							$datedeb_sujet=$datedeb_sejour;
						}
				echo $datedeb_sujet?> - <?php echo $datefin_sujet ?>
      </td>
    </tr>
		<?php 		
		if($affecte)
    {
		?>
     <tr>
    	<td nowrap class="bleucalibri11">
      	<?php echo $hashLib['etudiant'] ?>
			</td>
			<td>
				
        	<a href="../publicdetailindividu.php?appel=<?php echo $appel ?>&amp;codetheme=<?php echo $codetheme ?>&amp;codelangue=<?php echo $codelangue ?>&amp;codeindividu=<?php echo $row_rs_sujet['codeindividu'] ?>">
					 <?php echo $row_rs_sujet['prenom'] ?> <?php echo $row_rs_sujet['nom'] ?></a>
        
			</td>
    </tr>
    <?php 
		}
		//Directeurs ou contacts
		$query_rs="select codedir,titrepers_".strtolower($codelangue)." as titredir,nom as nomdir,prenom as prenomdir".
							" from sujetdir,individu where codesujet=".GetSQLValueString($codesujet, "text").
							" and sujetdir.codedir=individu.codeindividu ".
							" order by numordre";
		$rs=mysql_query($query_rs) or die(mysql_error());
		?>
		<tr>
    	<td valign="top" nowrap class="bleucalibri11">
 					<?php echo ($row_rs_sujet['codetypesujet']=="02" || $row_rs_sujet['codetypesujet']=="03")?$hashLib['directeurscran']:$hashLib['contacts'];?>
				
			</td>
			<td>
					<?php 
					$first=true;
          while($row_rs=mysql_fetch_assoc($rs))
          { echo $first?'':', ';
						$first=false; ?>
          	<a href="../publicdetailindividu.php?appel=<?php echo $appel ?>&amp;codetheme=<?php echo $codetheme ?>&amp;codelangue=<?php echo $codelangue ?>&amp;codeindividu=<?php echo $row_rs['codedir'] ?>">
              <?php echo $row_rs['prenomdir'].' '.$row_rs['nomdir'] ?></a>
          <?php 
          } ?>
      </td>
    </tr>
		<tr>
    	<td valign="top" class="bleucalibri10">
				<?php echo (($row_rs_sujet['autredir1']!="" || $row_rs_sujet['autredir2']!="")?(
															($row_rs_sujet['codetypesujet']=="02"||$row_rs_sujet['codetypesujet']=="03")?
																															$hashLib['directeursautre']:$hashLib['contactsautre']):"");
				?>
        
			</td>
			<td>
				<?php 
        if($row_rs_sujet['autredir1']!="")
        {  echo $row_rs_sujet['autredir1']; 
           if($row_rs_sujet['autredir1mail']!="")
           { ?>&nbsp;(<a href="mailto:<?php echo $row_rs_sujet['autredir1mail'] ?>"><?php echo $row_rs_sujet['autredir1mail'] ?></a>)
          <?php 
           } 
        } 
				if($row_rs_sujet['autredir2']!="")
				{ echo ', '.$row_rs_sujet['autredir2']; 
					if($row_rs_sujet['autredir2mail']!="")
					{ ?>&nbsp;(<a href="mailto:<?php echo $row_rs_sujet['autredir2mail'] ?>"><?php echo $row_rs_sujet['autredir2mail'] ?></a>)
					<?php 
					} 				
				} ?>
      </td>
		</tr>
		<tr>
    	<td valign="top" class="bleucalibri10"><?php echo $hashLib['descr'] ?>
      </td>
			<td align="justify"><?php echo nl2br($row_rs_sujet['descr']) ?>
      </td>
    </tr>
		<tr>
    	<td valign="top" class="bleucalibri10"><?php echo $hashLib['motscles'] ?>
    	</td>
			<td><?php echo $row_rs_sujet['motscles'] ?>
      </td>
    </tr>
		<?php 
		if($row_rs_sujet['conditions']!='')
		{ ?>
    <tr>
    	<td valign="top" class="bleucalibri10"><?php echo $hashLib['conditions'] ?>
			</td>
			<td align="justify"><?php echo nl2br($row_rs_sujet['conditions']) ?>
      </td>
    </tr>
		<?php 
		}
		$query_rs ="select liblong_".strtolower($codelangue)." as libtheme".
							 " from sujettheme,structure where codesujet=".GetSQLValueString($codesujet, "text").
							 " and ".periodeencours('structure.date_deb','structure.date_fin').
							 " and sujettheme.codetheme=structure.codestructure";
		$rs=mysql_query($query_rs) or die(mysql_error());
		 ?>
		<tr>
    	<td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['themes'] ?>
      </td>
      <td>
      	<table border="0" cellspacing="0" cellpadding="0">
					<?php 
          while($row_rs=mysql_fetch_assoc($rs))
          { ?>
          <tr>
          	<td><?php echo $row_rs['libtheme'] ?>
            </td>
          </tr>
          <?php
          } ?>
        </table>
      </td>
    </tr>
    <?php 
		if($row_rs_sujet['financement']!="")
    { ?>
    <tr>
    	<td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['financement'] ?>
      </td>
      <td align="justify"><?php echo $row_rs_sujet['financement'] ?>
      </td>
    </tr>
    <?php 	
		}
		if($row_rs_sujet['ref_publis']!="")
    { $tab_publis= explode (',',$row_rs_sujet['ref_publis']);
			$row_rs_sujet['ref_publis']="";
			$first=true;
			foreach($tab_publis as $une_ref)
			{ if($first)
				{ $first=false;
				}
				else
				{ $row_rs_sujet['ref_publis'].=',';
				}
				$une_ref_lower=trim(strtolower($une_ref));
				if($une_ref_lower!='')
				{ if(strpos($une_ref_lower,'hal-')!==false || strpos($une_ref_lower,'tel-')!==false || strpos($une_ref_lower,'inria-'))
					{ $row_rs_sujet['ref_publis'].='<a href="http://hal.archives-ouvertes.fr/'.$une_ref_lower.'" target="_blank">'.$une_ref.'</a>';
					}
					else
					{ $row_rs_sujet['ref_publis'].=$une_ref;
					}
				}
			}
		?>
    <tr>
    	<td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['ref_publis'] ?>
      </td>
      <td><?php echo $row_rs_sujet['ref_publis'] ?>&nbsp;&nbsp;&nbsp;&nbsp;+ <a href="http://hal.archives-ouvertes.fr/CRAN/<?php echo strtolower($codelangue) ?>" target="_blank">CRAN - Publications</a>
      </td>
    </tr>
    <?php 	
		}
}
 ?>
</table>
</div>
<?php 
if(isset($rslib)) mysql_free_result($rslib);
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_sujet)) mysql_free_result($rs_sujet);
?>

		<!-- InstanceEndEditable -->
    	</div>
    </div>
	</div>
</div>  
</body>
<!-- InstanceEnd --></html>