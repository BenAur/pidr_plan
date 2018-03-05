<?php require_once('../_const_fonc.php');

?>
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
    <div class="titre_page_dept">Soutenance</div>
    <div>
    
		<?php 
		$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
		$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");
		$codelibtypeactuevenement=(isset($_REQUEST['codelibtypeactuevenement'])?$_REQUEST['codelibtypeactuevenement']:"");
		$codeindividu=(isset($_REQUEST['codeindividu'])?$_REQUEST['codeindividu']:"");
		$numsejour=(isset($_REQUEST['numsejour'])?$_REQUEST['numsejour']:"");

		if($codelibtypeactuevenement=='these')
		{ $query_rs="select distinct individu.codeindividu,individuthese.numsejour,nom,prenom,".
								" date_soutenance as date, heure_soutenance as heure, lieu_soutenance as lieu, titre_these as titre, resume_these as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement,".
								" jury_rapp1_these, jury_rapp2_these, jury_autres_membres_these".
								" from individu,individuthese,typeactuevenement ".
								" where individu.codeindividu=individuthese.codeindividu".
								" and codelibtypeactuevenement='these' and individu.codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
		}
		else if($codelibtypeactuevenement=='hdr')
		{ $query_rs=" select individu.codeindividu,nom,prenom,".
                " date_hdr as date, heure_hdr as heure, lieu_hdr as lieu, titre_hdr as titre, resume_hdr as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,typeactuevenement".
								" where hdr='oui' and date_hdr<>'' and codelibtypeactuevenement='hdr' and codeindividu=".GetSQLValueString($codeindividu, "text");
		}

    $rs=mysql_query($query_rs) or die(mysql_error()); ?>
		<?php if($row_rs=mysql_fetch_assoc($rs))
		{ ?>
      <table width="100%" border="0" >
        <tr>
          <td nowrap><a href="JavaScript:history.go(-1)"><img src="../images/fleche_haut_page.gif" border="0"></a> <b>Le</b> <?php  echo aaaammjj2jjmmaaaa($row_rs['date'],'/') ?><?php  echo $row_rs['heure']!=''?' - '.$row_rs['heure']:'' ?><?php  echo $row_rs['lieu']!=''?' - '.$row_rs['lieu']:'' ?>
          </td>
        </tr>
        <tr>
        	<td>
          </td>
        </tr>
        <tr>
          <td align="center"><b>"<?php echo $row_rs['titre'] ?>"</b>
          </td>
        </tr>
        <tr>
          <td nowrap align="center">
          	(<?php echo $row_rs['libtypeactuevenement'] ?> <?php echo $row_rs['prenom'] ?>&nbsp;<?php echo $row_rs['nom'] ?>)
          </td>
        </tr>
        <?php if($row_rs['resume']!='')
        {?> 
        <tr>
          <td  align="justify"><br/><b>R&eacute;sum&eacute; :</b>
          <br/><?php echo nl2br($row_rs['resume']) ?>
          </td>
        </tr>
        <?php 
				}
        if($codelibtypeactuevenement=='these')
        {?> 
        <tr>
          <td>
          	<table>
            	<tr>
              	<td colspan="2"><b>Jury :</b>
          			</td>
              </tr>
              <tr>
                <td><b>- Rapporteurs : </b>
                </td>
              	<td><?php echo $row_rs['jury_rapp1_these'] ?>
          			</td>
              </tr>
              <tr>
                <td>
                </td>
              	<td><?php echo $row_rs['jury_rapp2_these'] ?>
          			</td>
              </tr>
              <tr>
                <td valign="top" nowrap><b>- Autres membres : </b>
                </td>
              	<td><?php echo nl2br($row_rs['jury_autres_membres_these']) ?>
          			</td>
              </tr>
            </table>
          </td>
        </tr>
        <?php 
				} /* */
				?>
      </table>
        <?php 
		} 
		if(isset($rs)) mysql_free_result($rs);
		?>
    </div>
		<!-- InstanceEndEditable -->
    	</div>
    </div>
	</div>
</div>  
</body>
<!-- InstanceEnd --></html>