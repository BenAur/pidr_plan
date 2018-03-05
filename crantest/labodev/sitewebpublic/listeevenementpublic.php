<?php require_once('../_const_fonc.php');?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/cran_base_php.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-latin-1" />
<meta name="description" content="<?php echo meta_description() ?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CRAN -<?php echo $codelangue=='FR'?'Ev&egrave;nements':'Events' ?></title>
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
    <?php	
    $codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
    ?>
    <div class="titre_page_dept">Congr&egrave;s, colloques</div>
    <div>
    <table width="980" border="0" cellpadding="0" cellspacing="2">
      <tr>
        <td valign="top" nowrap><img src="file:///C|/Documents%20and%20Settings/pgend/Documents/images/fleche_double_droit_sup12x10.gif" alt="" /></td>
        <td nowrap class="noircalibri11"><a href="http://springschool.cran.univ-lorraine.fr/" target="_blank">First Edition of the Spring Doctoral School on &laquo; Data-driven Model Identification of Dynamical Systems &raquo;</a>, Nancy, April 3-6, 2017</td>
      </tr>
      <tr>
        <td valign="top" nowrap><img src="file:///C|/Documents%20and%20Settings/pgend/Documents/images/fleche_double_droit_sup12x10.gif" alt="" /></td>
        <td nowrap class="noircalibri11">Le CRAN organise la <a href="http://www.insp.upmc.fr/webornano/conferences-or-nano/nancy2017/index.html" target="_blank">conférence Or-Nano 2017 du GdR Or-Nano</a> qui se tiendra les 12, 13 et 14 juin &agrave; Nancy.<br>
          <div><span class="Object" id="OBJ_PREFIX_DWT8434_com_zimbra_url"><a href="http://www.insp.upmc.fr/webornano/conferences-or-nano/nancy2017/index.html" target="_blank"></a></span></div>
        <br>
        </td>
      </tr>
    </table>
    </div>    
    <div class="titre_page_dept"><?php if($codelangue=="FR"){?>Calendrier de soutenances de th&egrave;ses et HDR (<a href="../archives.php">Archives</a>)<?php }else{?>Events/Conferences<?php }?></div>
  
    <div>
    
    <table width="100%" cellpadding="2" cellspacing="2">
      <?php 
			$tab_typeevent=array('These'=>array('EN'=>'Phd Thesis','FR'=>'Th&egrave;se'),'HDR'=>array('EN'=>'HDR','FR'=>'HDR'));
      $tab_themeindividu=array();
      $query_rs="(select individusejour.codeindividu as codeindividu, individusejour.numsejour as numsejour, libcourt_fr as libtheme, structure.date_deb as date_deb".
                " from individusejour, individutheme, structure".
								" where individusejour.codeindividu=individutheme.codeindividu".
                " and individusejour.numsejour=individutheme.numsejour and individutheme.codetheme=structure.codestructure".
                " and individusejour.codecorps='51' and structure.esttheme='oui' and structure.codelib<>'du'".
                " and ".intersectionperiodes('datedeb_sejour','datefin_sejour','structure.date_deb','structure.date_fin').
								" )".
								" union".
								" (select individusejour.codeindividu as codeindividu, individusejour.numsejour as numsejour, libcourt_fr as libtheme, structure.date_deb as date_deb".
								" from individu,individusejour, individutheme, structure".
								" where individu.codeindividu=individusejour.codeindividu and individusejour.codeindividu=individutheme.codeindividu".
                " and individusejour.numsejour=individutheme.numsejour and individutheme.codetheme=structure.codestructure".
                " and individu.hdr='oui' and structure.esttheme='oui' and structure.codelib<>'du'".
                " and ".intersectionperiodes('date_hdr','date_hdr','structure.date_deb','structure.date_fin').
								" )".								
                " order by codeindividu, numsejour, date_deb desc,libtheme asc";
                //echo $query_rs;
      $rs=mysql_query($query_rs) or die(mysql_error());
      while($row_rs=mysql_fetch_assoc($rs))
      { if(isset($tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]))
        { if($row_rs['date_deb']==$date_deb_structure)
          { $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']].=", ".$row_rs['libtheme'];
          }
        }
        else
        { $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs['libtheme'];
          $date_deb_structure=$row_rs['date_deb'];
        }
      }
      $query_rs="(select individu.codeindividu,individuthese.numsejour,nom,prenom,".
                " date_soutenance as date, heure_soutenance as heure, lieu_soutenance as lieu, titre_these as titre, resume_these as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,individuthese,typeactuevenement ".
                " where individu.codeindividu=individuthese.codeindividu".
                " and date_soutenance<>'' and titre_these<>'' and codelibtypeactuevenement='these'".
								" and substring(date_soutenance,1,4)>=".GetSQLValueString(date("Y")-3, "text").
								" )".
								" union".
								" (select individu.codeindividu,individusejour.numsejour as numsejour,nom,prenom,".
                " date_hdr as date, heure_hdr as heure, lieu_hdr as lieu, titre_hdr as titre, resume_hdr as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,individusejour,typeactuevenement".
								" where hdr='oui' and date_hdr<>'' and individu.codeindividu=individusejour.codeindividu and codelibtypeactuevenement='hdr'".
								" and ".intersectionperiodes('date_hdr','date_hdr','datedeb_sejour','datefin_sejour').
								" and substring(date_hdr,1,4)>=".GetSQLValueString(date("Y")-3, "text").
								" )".
								" order by date desc, heure desc";
      $rs=mysql_query($query_rs) or die(mysql_error());
			$class="even";
      while($row_rs=mysql_fetch_assoc($rs))
      { ?>
          <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
            <td align="center"><?php  echo aaaammjj2jjmmaaaa($row_rs['date'],'/') ?>
            </td>
             <td align="center"><?php  echo $row_rs['heure'] ?>
            </td>
             <td align="center"><?php  echo $row_rs['lieu'] ?>
            </td>
           <td><?php  echo $row_rs['libtypeactuevenement'] ?>
            </td>
            <td><?php  echo $row_rs['titre'] ?>
            </td>
            <td align="center"><?php  echo $row_rs['prenom'] ?> <?php  echo $row_rs['nom'] ?>
            </td>
            <td align="center">
            <?php  
            if(isset($tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]))
            { echo $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']];
            } ?>
            </td>
            <td align="center"><?php if($row_rs['resume']!='')
            { ?><a href="detailevenementpublic.php?codeindividu=<?php echo $row_rs['codeindividu'] ?>&amp;numsejour=<?php echo $row_rs['numsejour'] ?>&amp;codelangue=<?php echo $codelangue ?>&amp;codelibtypeactuevenement=<?php echo $row_rs['codelibtypeactuevenement'] ?>">R&eacute;sum&eacute;</a>
            <?php 
            }?> 
            </td>
          </tr>
        <?php 
      }?>
    </table>
    <?php 
    if(isset($rs)) {mysql_free_result($rs);}?>
    </div>
			<!-- InstanceEndEditable -->
    	</div>
    </div>
	</div>
</div>  
</body>
<!-- InstanceEnd --></html>