<?php require_once('../_const_fonc.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/cran_base_php.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-latin-1" />
<meta name="description" content="<?php echo meta_description() ?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CRAN - <?php echo $codelangue=='FR'?'Actualit&eacute;s':'News' ?></title>
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
      <div class="titre_page_dept">Actualit&eacute;s</div>
    	<div>
				<div align="justify">
          <TABLE width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#ECE9D8">
          <TR>
            <TD valign="top" nowrap="nowrap">14/06/2017</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">Animation du <a href="http://univ-lorraine-events.fr/userfiles/file/Brunch_Fab_Grd_Est/Invitation_Brunch_Fabrique_Grand_Est.pdf" target="_blank">BRUNCH... Fabrique Grand Est Durable R&eacute;siliente</a> par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00135&amp;codelangue=FR">Beno&icirc;t Iung</a>, Professeur au CRAN. Au coeur du Salon Industrie du Futur qui ouvrira ses portes les 14 et 15 juin prochains au Parc Expo de Mulhouse.</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">28/03/2017</TD>
            <TD valign="top" class="noircalibri11">Prix des meilleures th&egrave;ses 2017 du GdR MACS attribu&eacute; &agrave;  Kais Mekki pour ses travaux intitul&eacute;s &quot;<a href="https://hal.archives-ouvertes.fr/CRAN/tel-01391769v1" target="_blank">Gestion de l'information embarquée dans des matériaux communicants a l'aide de protocoles de réseaux de capteurs sans fil</a>&quot;</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07/03/2017</TD>
            <TD valign="top" class="noircalibri11">&quot;<a href="../imageactu/Award.jpg" target="_blank">Best Paper Award</a>&quot; &agrave; la conf&eacute;rence 2016 ISPE, &quot;International Conference on Transdisciplinary Engineering&quot; pour <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00878&amp;codelangue=FR" target="_self">Gabriel Leal Serapi&atilde;o</a>, doctorant en&nbsp;d&eacute;but de 2&egrave;me ann&eacute;e de th&egrave;se.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">12/12/2016</TD>
            <TD valign="top" class="noircalibri11">Remise du prix de th&egrave;se de l'Universit&eacute; de Lorraine &agrave; Claire Barbieux ! <a href="../imageactu/prix_these_barbieux.pdf" target="_blank">En savoir plus...</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">21/10/2016</TD>
            <TD valign="top" class="noircalibri11"><a href="http://factuel.univ-lorraine.fr/node/5472" target="_blank">La&nbsp;th&eacute;rapie photodynamique ou comment gu&eacute;rir par la lumi&egrave;re</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">29/08/2016</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">&laquo; <b>Prix de la Recherche Clinique</b> du CHR Metz-Thionville <b>2016</b>&nbsp;&raquo; attribu&eacute; au projet &laquo;&nbsp;<b>SPECTROLIVE : Spectroscopie optique pour la carcinologie cutan&eacute;e</b>&nbsp;&raquo; port&eacute; conjointement par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00298&amp;codelangue=FR" target="_self">Marine Amouroux</a> (CRAN)&nbsp;et Gr&eacute;goire Khairallah (PH au CHR).</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">14/07/2016</TD>
            <TD valign="top" class="noircalibri11">Prix de th&egrave;se 2015 de l'universit&eacute; de Lorraine :&nbsp;<br />
              <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00425&amp;codelangue=FR">Claire BARBIEUX</a>&nbsp;est laur&eacute;ate pour ses travaux&nbsp; intitul&eacute;s &laquo;&nbsp;R&ocirc;les de   la prot&eacute;ine Damaged-DNA Binding 2 sur l'adh&eacute;rence, les propri&eacute;t&eacute;s   nanom&eacute;caniques et la voie du TGFb1 dans les cellules tumorales mammaires&nbsp; &raquo;&nbsp; dirig&eacute;s par <a href="http://www.cran.uhp-nancy.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00395&amp;codelangue=FR" data-mce-href="http://www.cran.uhp-nancy.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00217&amp;codelangue=FR">Philippe BECUWE</a> et <a href="http://www.cran.uhp-nancy.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00403&amp;codelangue=FR">St&eacute;phanie GRANDEMANGE</a>.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01/04/2016</TD>
            <TD valign="top" class="noircalibri11">Les activit&eacute;s sant&eacute; du CRAN &agrave; l'honneur dans CNRS Innovation, rubrique Start-up : &quot;Un nouveau dispositif m&eacute;dical pour &eacute;valuer                          l'&eacute;volution de pr&eacute;-cancers de la peau&quot;. <a href="../imageactu/Article_CNRS_Innovation_SpectroLive_CRAN_SBS_Amouroux.pdf" target="_blank">En savoir plus... </a><br />
Contact CRAN : <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00298&amp;codelangue=FR">Marine AMOUROUX</a><br /><div align="center"></div></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07/03/2016</TD>
            <TD valign="top" class="noircalibri11">La <a href="../imageactu/semaineducerveau2016/Programme_SDC-2016_Lorraine_VF.pdf" target="_blank">Semaine du Cerveau</a> en Lorraine se d&eacute;roulera du 07 au 23 mars 2015.<br />
              ENTREE LIBRE ET GRATUITE<br />
              Coordinateur r&eacute;gional : <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00144&amp;codelangue=FR" target="_self">Laurent Koessler</a>, chercheur CNRS au CRAN. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">15/02/2016</TD>
            <TD valign="top" class="noircalibri11">Le projet &quot;Mod&eacute;lisation et Analyse des R&eacute;seaux d'Echanges de Semences -   MADRES&quot; port&eacute; par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00560&amp;codelangue=FR">Samuel Martin</a>&nbsp;en bonne place&nbsp;dans le <a href="http://www.cnrs.fr/mi/spip.php?article855" target="_blank">Rapport   &quot;L'interdisciplinarit&eacute; au CNRS. Illustrations et t&eacute;moignages&quot; &nbsp;(page 13)</a>.<br />
              <a href="../imageactu/PEPS_MADRES.pdf" target="_blank">L'extrait de la page 13 du rapport</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">15/12/2015</TD>
            <TD valign="top" class="noircalibri11">Prix de la meilleure communication orale aux journ&eacute;es de l'Ecole Doctorale BioSE attribu&eacute; &agrave; <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00594&amp;codelangue=FR" target="_self">Marine GEOFFROY</a> pour ses travaux intitul&eacute;s &quot;Implication de la prot&eacute;ine claudine 1 induite par les thiazolidinediones dans les cellules canc&eacute;reuses mammaires&quot;.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">27/11/2015</TD>
            <TD valign="top" class="noircalibri11"><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00107&amp;codelangue=FR">Thierry Divoux</a>, Professeur au CRAN et Directeur adjoint a &eacute;t&eacute; &eacute;lu le 26 novembre 2015 &agrave; la pr&eacute;sidence de la section &quot;G&eacute;nie informatique, automatique et traitement du signal&quot; du Conseil National des Universit&eacute;s. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">27/11/2015</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">Le prix du meilleur papier &eacute;tudiant &agrave; la conf&eacute;rence CSDM'15 (Complex Systems Design &amp; Management) a &eacute;t&eacute; attribu&eacute; &agrave; <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00676&amp;codelangue=FR">Laetitia Diez</a> (deuxi&egrave;me ann&eacute;e de th&egrave;se dans l'unit&eacute;) pour ses travaux intitul&eacute;s &laquo;  Maintenance as a cornerstone for the application of r&eacute;g&eacute;n&eacute;ration paradigme in systems lifecycle &raquo; .</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01/10/2015</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">Le <b>CRAN</b> et la soci&eacute;t&eacute; <b><a href="http://www.predict.fr" target="_blank">PREDICT</a></b> cr&eacute;ent un nouveau partenariat structur&eacute; sous la forme  d'un &quot;Laboratoire Commun&quot; <b>LabCom</b> soutenu par l'Agence Nationale de la Recherche - ANR. <a href="../imageactu/LABCOM%20PHM_en_20_lignes.pdf" target="_blank">En savoir plus</a>.</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">22/07/2015</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">Les travaux de <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00463&amp;codelangue=FR">P. Retif</a>, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00409&amp;codelangue=FR">S.&nbsp;Pinel</a>, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00302&amp;codelangue=FR">M. Toussaint</a>, C. Frochot, R.   Chouikrat, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00074&amp;codelangue=FR">T. Bastogne</a> et <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00217&amp;codelangue=FR">M. Barberi-Heyob</a> intitul&eacute;s &quot;Nanoparticles for   radiation therapy enhancement: the key parameters&quot;&nbsp;font la une du num&eacute;ro   de septembre 2015 de l'excellente revue Theranostics (IF : 8.022), <a href="http://thno.org/cover/v5i9.pdf" target="_blank">http://thno.org/cover/v5i9.pdf</a>.</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">13/07/2015</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11">Le CRAN &agrave; l'honneur dans le <a href="http://www.cnrs.fr/fr/pdf/RA2014/#/1/" target="_blank">rapport d'activit&eacute; 2014 du CNRS</a> pour les travaux de <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00560&amp;codelangue=FR">Samuel Martin</a> sur la cartographie dynamique de la population (page 46).</span></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">06/07/2015</TD>
            <TD valign="top" class="noircalibri11">&quot;Best Research Paper Award&quot; obtenu par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00523&amp;codelangue=FR" target="_self">Hoa Tran-Dang</a>, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00145&amp;codelangue=FR" target="_self">Nicolas Krommenacker</a> et <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00096&amp;codelangue=FR" target="_self">Patrick Charpentier</a> &agrave; la conf&eacute;rence &quot;2nd International Physical Internet Conference,   6th-8th july 2015, Paris&quot; pour une communication intitul&eacute;e &quot;Enhancing   the functionality of Physical Internet Containers by Wireless Sensor   Networks&quot; </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">25/06/2015</TD>
            <TD valign="top" class="noircalibri11">La 4&egrave;me   Edition du Colloque Inter-r&eacute;gional Grand Est de Recherche   Translationnelle en Oncologie a eu lieu les 25 et 26 juin &agrave; Dijon, co-   organis&eacute;e par le &nbsp;CGFL, l'EPHE, l'INSERM, l'Universit&eacute; de Bourgogne et   le CHU de Dijon, sous l'&eacute;gide du Canc&eacute;rop&ocirc;le Grand-Est. <br />
A   cette occasion, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00423&amp;codelangue=FR" target="_self">Alexandre Harl&eacute;</a>&nbsp;a remport&eacute; le prix du meilleur Poster,   pour ses travaux sur la d&eacute;tection de mutations rares dans le cancer   colorectal m&eacute;tastatique gr&acirc;ce &agrave; la technique du NGS (Next Generation   Sequencing).</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-07-2015</TD>
            <TD valign="top" class="noircalibri11"><a href="http://www.snclf.net/JOURNEES-SNCLF-2015-GRENOBLE-du-1er-au-3-JUILLET-2015_a105.html" target="_blank">Le cerveau a la < Loop                          ></a> lors de la Journ&eacute;e                            de Traitement du Signal et d'Image en                            Neurosciences (JTSIN), Grenoble</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">12-06-2015</TD>
            <TD valign="top" class="noircalibri11">Les travaux du projet scientifique ESPaCE intitul&eacute;s&nbsp;&quot;<a href="http://www.cnrs.fr/ins2i/spip.php?article1269" data-mce-href="http://www.cnrs.fr/ins2i/spip.php?article1269">&Eacute;tudier les signaux &eacute;lectriques c&eacute;r&eacute;braux pour comprendre et soigner l'&eacute;pilepsie</a>&quot;,&nbsp;mis   &agrave; l'honneur&nbsp;dans la rubrique actualit&eacute;s scientifiques de l'Institut des   Sciences de l'Information et de leurs interactions   (http://www.cnrs.fr/ins2i/)</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-05-2015</TD>
            <TD valign="top" class="noircalibri11"><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00135&amp;codelangue=FR">Beno&icirc;t Iung</a> est laur&eacute;at du &quot;prix de service du French National Member Organisation&quot;. Ce prix r&eacute;compense des chercheurs m&eacute;ritants, ayant servi au sein de l'IFAC.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">10-04-2015</TD>
            <TD valign="top" class="noircalibri11">Projet ESPaCE : <a href="http://eureka.lorraine.eu/jahia/Jahia/fr/actualites?actu=23247" target="_blank">explorer le cerveau pour mieux comprendre l'&eacute;pilepsie</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">26-03-2015</TD>
            <TD valign="top" class="noircalibri11"><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00425&amp;codelangue=FR" target="_self">Claire Barbieux</a>, doctorante du CRAN en troisi&egrave;me ann&eacute;e a re&ccedil;u le 1er prix de l'Ecole Doctorale BioSE (500&euro;) pour ses travaux de recherche intitul&eacute;s &laquo; R&ocirc;le de la prot&eacute;ine DDB2 dans les m&eacute;canismes d'adh&eacute;rence des cellules tumorales mammaires &raquo;</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap"><span class="noircalibri11">16-03-2015</span></TD>
            <TD valign="top" class="noircalibri11">La <a href="../imageactu/semaineducerveau2015/SDC-2015_Programme_VF.pdf" target="_blank">Semaine du Cerveau</a> en Lorraine se d&eacute;roulera du 16 au 25 mars 2015.<br />
              <b>ENTREE LIBRE ET GRATUITE</b><br />
Coordinateur r&eacute;gional : <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00144&amp;codelangue=FR" target="_self">Laurent Koessler</a>, chercheur CNRS au CRAN. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">21-01-2015</TD>
            <TD valign="top" class="noircalibri11"><a href="../imageactu/PEPIT%202014.pdf">&laquo; PEPIT 2014 &raquo;</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">20-12-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="../imageactu/Hipermed_ER-20-12-2014.pdf" target="_blank">Un article de presse sur le projet Hipermed</a> paru dans l'Est-R&eacute;publicain</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">17-12-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="http://videos.univ-lorraine.fr/index.php?act=view&amp;id=1772" target="_blank"><img src="../images/b_video.jpg" alt="" width="19" height="20" border="0" />Les syst&egrave;mes de demain, autonomes et &eacute;conomes</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">14-12-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="../imageactu/Drone-projectile_RL-14-12-2014.pdf" target="_blank">Un article de presse sur le projet &laquo;&nbsp;Drone projectile&nbsp;&raquo;</a> paru dans le R&eacute;publicain Lorrain</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">11-12-2014</TD>
            <TD valign="top" class="noircalibri11">Au <b>Forum <a href="http://www.afis.fr" target="_blank">AFIS</a></b> des 11 et 12 d&eacute;cembre, ont &eacute;t&eacute; d&eacute;cern&eacute;s :
              <p> <b>2&egrave;me Prix de th&egrave;se AFIS</b> : <br />
                <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00152&amp;codelangue=FR" target="_self">Romain Lieber</a>, Sp&eacute;cification d'exigences physicophysiologiques en ing&eacute;nierie d'un syst&egrave;me support de maintenance a&eacute;ronautique, Th&egrave;se CIFRE Airbus, Directeurs de th&egrave;se: <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00168&amp;codelangue=FR" target="_self">G&eacute;rard Morel</a> et Dominique Mery </p>
              <p><b>2&egrave;me Prix du meilleur poster</b> :<br />
              Anderson Luis Szejka, A method for formalizing requirements interoperation in complex systems engineering, Th&egrave;se en cotutelle avec PUC-PR (Br&eacute;sil), Directeurs de th&egrave;se : <a href="../appel=annuaire&amp;codetheme=&amp;codeindividu=00175&amp;codelangue=FR" target="_self">Herv&eacute; Panetto</a>, Osiris Cangiglieri Jr., Co-Directeurs : <a href="../appel=annuaire&amp;codetheme=&amp;codeindividu=00072&amp;codelangue=FR" target="_self">Alexis Aubry</a>, Eduardo Rocha Loures</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">19-11-2014</TD>
            <TD valign="top" class="noircalibri11"><span class="noircalibri11"><a name="hipermed19112014" id="hipermed19112014"></a>Les chercheurs du CRAN r&eacute;compens&eacute;s pour leurs travaux en compression d'images m&eacute;dicales :<br />
                <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00228&amp;codelangue=FR">Jean-Marie Moureaux</a> et ses coll&egrave;gues, chercheurs et membres du consortium <a href="http://www.hipermed.org/" target="_blank">HIPERMED</a> (HIgh PERformance teleMEDicine platform), ont re&ccedil;u le 19 novembre dernier &agrave; B&acirc;le, <b>l'&laquo; award &raquo; d'or de l'innovation des projets europ&eacute;ens EUREKA</b>, parmi plus de 1 000 projets sur les quatre derni&egrave;res ann&eacute;es.<br />
            </span>
              <p>HIPERMED   est une plate-forme libre de t&eacute;l&eacute;m&eacute;decine &agrave; haute performance int&eacute;grant   sur un m&ecirc;me &eacute;cran plusieurs vid&eacute;os haute d&eacute;finition (instruments   chirurgicaux, capteurs, ambiance), images radiologiques ou encore   fichiers textes (historique des patients par exemple), issus de sites   distants.</p>
              <p>D&eacute;di&eacute;e   aux professionnels de sant&eacute; ainsi qu'aux patients, elle a &eacute;t&eacute; con&ccedil;ue et   r&eacute;alis&eacute;e par un consortium europ&eacute;en d'acteurs espagnols, fran&ccedil;ais (la   soci&eacute;t&eacute; VITEC Multim&eacute;dia et le CRAN), polonais, su&eacute;dois et turcs. Le   CRAN a apport&eacute; une contribution significative sur la compression des   images et vid&eacute;os, en partenariat avec TELECOM Nancy et son &laquo;&nbsp;living   lab&nbsp;&raquo; PROMETEE, ainsi qu'avec le service ORL du CHRU de Nancy.</p>
              <p>Outre les 20 000 &euro; de prix qui seront investis dans la diffusion d'HIPERMED, l'award   obtenu apporte une reconnaissance internationale qui se traduit d&eacute;j&agrave;   par des projets de d&eacute;ploiement &agrave; grande &eacute;chelle, en particulier en   Lorraine. Ce succ&egrave;s, renforce les deux enjeux majeurs vis&eacute;s&nbsp;:</p>
              <ul type="disc">
                <li>faire progresser la connaissance par le partage d'exp&eacute;rience (en particulier en canc&eacute;rologie),</li>
                <li>maintenir une m&eacute;decine de qualit&eacute; sur tous les territoires par l'expertise &agrave; distance de centres hospitaliers de r&eacute;f&eacute;rence.</li>
              </ul>
              <p>Il   ouvre de nombreuses perspectives de recherche et de d&eacute;veloppement, qui   feront l'objet du projet E3 (E-health services Everywhere and for   Everybody), la suite d'HIPERMED.</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">23-10-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="http://factuel.univ-lorraine.fr/node/1785" target="_blank">Paru dans Factuel : </a><a href="http://factuel.univ-lorraine.fr/node/1856#overlay-context=node/1856" target="_blank">Le CRAN a organis&eacute; l'&eacute;dition 2014 de IEEE Multi-conference on Systems and Control</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07-10-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="http://factuel.univ-lorraine.fr/node/1785" target="_blank">Paru dans Factuel : SpectroLive : aider au diagnostic des l&eacute;sions canc&eacute;reuses sans pr&eacute;l&egrave;vement, gr&acirc;ce &agrave; la spectroscopie optique</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07-10-2014</TD>
            <TD valign="top" class="noircalibri11">Le premier &quot;Cr&eacute;a d&egrave;j&quot; de l'Incubateur Lorrain s'est tenu le mardi 7 octobre de 8h30 &agrave; 9h30, dans la salle de conf&eacute;rences de l'AIP-Primeca avec le t&eacute;moignage de <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00074&amp;codelangue=FR">Thierry BASTOGNE</a>, enseignant chercheur au CRAN, cofondateur de la <a href="http://www.cybernano.eu/" target="_blank">soci&eacute;t&eacute; CYBERNANO</a>, soci&eacute;t&eacute; sp&eacute;cialis&eacute;e dans l'aide au d&eacute;veloppement pr&eacute;clinique des nanom&eacute;dicaments en canc&eacute;rologie.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">30-09-2014</TD>
            <TD valign="top" class="noircalibri11">Publication dans la prestigieuse revue multidisciplinaire PNAS d'un article intitul&eacute; &laquo; Dynamicpopulation mapping using mobile phone data &raquo; co-&eacute;crit par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00569&amp;codelangue=FR">Samuel MARTIN</a> du CRAN. Les travaux de Samuel ont &eacute;t&eacute; men&eacute;s en collaboration avec le &laquo; Department of Applied Mathematics &raquo; de l'Universit&eacute; Catholique de Louvain et les Universit&eacute;s de Bruxelles, Louisville, Southampton et Stockholm.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">15-09-2014</TD>
            <TD valign="top" class="noircalibri11">Christophe Fonte, Ing&eacute;nieur de recherche CNRS au CRAN &agrave; &eacute;t&eacute; r&eacute;&eacute;lu au Conseil Scientifique de l'INSIS pour un mandat de 5 ans</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">15-09-2014</TD>
            <TD valign="top" class="noircalibri11"><p>&laquo;&nbsp;Best paper award&nbsp;&raquo; pour Kais Mekki, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00105&amp;codelangue=FR">William Derigent</a>, Ahmed Zouinkhi, <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00190&amp;codelangue=FR">Eric Rondeau</a> et Naceur Abdelkrim &agrave; la conf&eacute;rence IEEE FiCloud 2014. Titre des travaux&nbsp;: Data Dissemination Algorithms for Communicating Materials using Wireless Sensor Networks</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-07-2014</TD>
            <TD valign="top" class="noircalibri11">Parution: Applications of Continuous-Time Model Identification and Estimation - International Journal of Control - July 2014.<br />
              The aim of this special issue is both to assess the current state-of-the-art in the field of continuous-time model identification and reveal the capabilities of current methods when applied to real and challenging applications. Three papers consider the application of dynamical systems and control theory in the development of innovative interventions in health care and medicine. <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00114&amp;codelangue=FR" target="_self">Hugues Garnier</a> from CRAN is the lead editor of this special issue which contains 9 articles: <a href="http://www.tandfonline.com/toc/tcon20/87/7#.U3zi2C995QY" target="_blank">IJC special issue</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">24-06-2014</TD>
            <TD valign="top" class="noircalibri11">Le CRAN participe au 1er forum des innovations R&amp;D (projet CONNEXION), Paris, Centre Pompidou - 24 juin 2014.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07-06-2014</TD>
            <TD valign="top" class="noircalibri11">Prix de la &laquo; Technologie offrant les meilleures opportunit&eacute;s de d&eacute;veloppement &eacute;conomique &raquo;  des bourses et technologies de l'institut Mines Telecom
              pour l'innovation propos&eacute;e par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00074&amp;codelangue=FR" target="_self">Thierry Bastogne</a>, enseignant-chercheur, et <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00463&amp;codelangue=FR" target="_self">Paul Retif</a>, doctorant : <b>&laquo; Nano-iD &raquo; un outil de conception assist&eacute;e par ordinateur de mat&eacute;riaux nanostructur&eacute;s en nanom&eacute;decine par rayonnements</b> (juin 2014).</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">04-06-2014</TD>
            <TD valign="top" class="noircalibri11">Premier prix &laquo; Pr&eacute;sentation orale &raquo; &agrave; l'&laquo; International Congress on  Photodynamics Applications &raquo; pour <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00302&amp;codelangue=FR">Magalie TOUSSAINT</a>, doctorante du CRAN. Titre des travaux : <b>&quot; Optimization of illumination in interstitial photodynamique therapy for high-grade brain tumor&quot;</b></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-05-2014</TD>
            <TD valign="top" class="noircalibri11"><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00298&amp;codelangue=FR" target="_self">Marine Amouroux</a>, ing&eacute;nieure de recherche, laur&eacute;ate du concours d'aide &agrave; la cr&eacute;ation d'entreprise de technologies innovantes organis&eacute; par le minist&egrave;re de l'Enseignement Sup&eacute;rieur et de la Recherche en partenariat avec Bpifrance Financement pour son projet intitul&eacute; <b>&laquo; SpectroLive &raquo; : Spectroscopie optique fibr&eacute;e multimodalit&eacute;s pour la caract&eacute;risation de mat&eacute;riaux biologiques</b>, cat&eacute;gorie &quot;projet en &eacute;mergence&quot; (mai 2014).</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">23-04-2014</TD>
            <TD valign="top" class="noircalibri11"><b><a name="award_celtic_plus" id="award_celtic_plus"></a><a href="../imageactu/Project_Award_winners_2014.pdf" target="_blank">Award d'excellence Celtic Plus</a></b><br />
              Le projet europ&eacute;en Hipermed port&eacute; pour le CRAN par <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00228&amp;codelangue=FR" target="_self">Jean-Marie Moureaux</a> a re&ccedil;u un award d'excellence lors de l'&eacute;v&egrave;nement Celtic Plus &agrave; Monaco les 23 et 24 avril 2014.<br />
              <p align="justify">HIPERMED (High PERformance teleMEDicine platform &ndash; <a href="http://www.hipermed.org" target="_blank">www.hipermed.org</a>)   est un projet europ&eacute;en de type Celtic dont l'objectif est le   d&eacute;veloppement d'une plate-forme ouverte de t&eacute;l&eacute;m&eacute;decine orient&eacute;e   services. <br />
              HIPERMED fournit sur <b>une seule et m&ecirc;me plate-forme</b>, des services tels que la <b>consultation &agrave; distance</b>, le <b>t&eacute;l&eacute;-enseignement</b>, la <b>t&eacute;l&eacute;-expertise</b> et l'<b>aide &agrave; la d&eacute;cision</b> et au <b>suivi de patients</b> que ce soit chez eux ou dans des &eacute;tablissements de soin. Pour ce faire,   HIPERMED int&egrave;gre la transmission et la manipulation simultan&eacute;e de   plusieurs <b>flux vid&eacute;o haute d&eacute;finition</b>, (visioconf&eacute;rence, vid&eacute;os issus d'endoscopes ou d'autres mat&eacute;riels m&eacute;dicaux), de <b>donn&eacute;es issues de capteurs</b> &eacute;quipant des patients et le partage d'<b>images radiologiques haute r&eacute;solution</b> (scanner, IRM, etc). La transmission de l'ensemble de ces donn&eacute;es s'effectue &agrave; l'aide de <b>solutions r&eacute;seau &agrave; hautes performances et faible co&ucirc;t</b>, bas&eacute;es sur des logiciels libres.</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">24-04-2014</TD>
            <TD valign="top" class="noircalibri11">La finale du concours &quot;Ma Th&egrave;se en 180 secondes&quot; en Lorraine se d&eacute;roulera jeudi 24 avril &agrave; 19h &agrave; Nancy, Salle d'Honneur des Universit&eacute;s. <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00425&amp;codelangue=FR" target="_self">Claire Barbieux</a>, doctorante au CRAN, a &eacute;t&eacute; s&eacute;lectionn&eacute;e &agrave; l'issue des pr&eacute;s&eacute;lections et participera &agrave; cette finale. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">25-03-2014</TD>
            <TD valign="top" class="noircalibri11">Deux prix &agrave; la 9&egrave;me journ&eacute;e de la recherche biom&eacute;dicale organis&eacute;e par la facult&eacute; de m&eacute;decine de Nancy</p>
              <p><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00237&amp;codelangue=FR" target="_self">Aur&eacute;lie Reinhard</a> (&eacute;tudiante de 3&eacute;me   ann&eacute;e de th&egrave;se) a obtenu le premier prix des communications orales en   recherche exp&eacute;rimentale pour son travail intitul&eacute;&nbsp;: &quot;&Eacute;valuation de la   th&eacute;rapie photodynamique dans le traitement de maladies inflammatoires   chroniques de l'intestin et la pr&eacute;vention des cancers colorectaux   associ&eacute;s sur model&eacute;s murins&quot; (Dir. <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00082&amp;codelangue=FR" target="_self">L.&nbsp;Bolotine</a>, Co-dir. L   Peyrin-Biroulet)</p>
              <p><a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00301&amp;codelangue=FR">Estelle Bastien</a> (&eacute;tudiante de 2&eacute;me   ann&eacute;e de th&egrave;se) a obtenu le deuxi&egrave;me prix des communications &eacute;crites en   recherche exp&eacute;rimentale pour son travail intitul&eacute; : &quot;Caract&eacute;risation   photobiologique in vitro d'une nouvelle nanoparticule photoactivable&quot; (Dir. <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00082&amp;codelangue=FR" target="_self">L. Bolotine</a>, co-directeur <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00225&amp;codelangue=FR" target="_self">HP. Lassalle</a>)</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">25-03-2014</TD>
            <TD valign="top" class="noircalibri11">Mardi 25 mars et mercredi 26 mars 2014&nbsp;: journ&eacute;es communes des groupes de travail MOSAR et SdH du GDR MACS, Vandoeuvre - Pr&eacute;sidence du collegium L INP. <br />
              <a href="http://w3.onera.fr/mosar/actualite" target="_blank">Informations et programme</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">10-03-2014</TD>
            <TD valign="top" class="noircalibri11">Pour la premi&egrave;re fois en Lorraine ! La <a href="../imageactu/semaineducerveau2014.html" target="_blank">Semaine du Cerveau</a> se d&eacute;roulera (Nancy et Lun&eacute;ville) du lundi 10 au mardi 18 mars 2014.<br />
              <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00144&amp;codelangue=FR" target="_self">Laurent Koessler</a>, Charg&eacute; de Recherche CNRS au CRAN, en est le Responsable scientifique et coordinateur local <br />
ENTREE LIBRE ET GRATUITE</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">31-01-2014</TD>
            <TD valign="top" class="noircalibri11"><div align="justify"><b><a href="http://www.innovation.public.lu/fr/actualites/2014/01/nano-cancer/index.html" target="_blank">Le Portail luxembourgeois de l'innovation et de la recherche</a></b> pr&eacute;sente la journ&eacute;e europ&eacute;enne des conf&eacute;rences de presse de vulgarisation de la Nanom&eacute;decine, <b>&laquo; <a href="http://www.nanoworldcancerday.eu/" target="_blank">Nano World Cancer Day 2014</a> &raquo;</b>, qui s'est tenue le 31/01/2014 &agrave; Belvaux, ainsi que dans 11 autres &eacute;tats membres de l'UE.<br>
Les Professeurs <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=themes_rech&amp;codetheme=08&amp;codeindividu=00226&amp;codelangue=FR" target="_self">Muriel Barberi-Heyob</a> et <a href="http://www.cran.univ-lorraine.fr/detailindividupublic.php?appel=themes_rech&amp;codetheme=08&amp;codeindividu=00074&amp;codelangue=FR" target="_self">Thierry Bastogne</a>, CRAN, Universit&eacute; de Lorraine, CNRS, Nancy, &eacute;galement co-fondateurs de la soci&eacute;t&eacute; CYBERnano, y ont respectivement abord&eacute; l'avenir de la Nanom&eacute;decine en canc&eacute;rologie pour les th&eacute;rapies par rayonnements et les objectifs de la Nanom&eacute;decine d'aujourd'hui et de demain en Europe en soulignant l'importance des tests pr&eacute;-cliniques.<br />
<a href="../imageactu/Nano%20World%20Cancer%20Day%202014.pdf" target="_blank">La fiche de pr&eacute;sentation de la journ&eacute;e</a></div></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">13-12-2013</TD>
            <TD valign="top"><b><a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codelangue=FR&amp;codeindividu=00052&amp;navig=O">Fabien Bouffaron</a></b>, doctorant encadr&eacute; par <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codelangue=FR&amp;codeindividu=00168&amp;navig=O" target="_self">G&eacute;rard MOREL</a> et <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codelangue=FR&amp;codeindividu=00119&amp;navig=O" target="_self">David GOUYON</a>, a re&ccedil;u le prix &quot;Best Student Paper&quot; pour son papier  &laquo;<b><a href="http://hal.archives-ouvertes.fr/hal-00916014" target="_blank">Specifying some key SE training artifacts</a></b>&raquo; &agrave; la conf&eacute;rence internationale <a href="http://www.csdm2013.csdm.fr/" target="_blank">CSDM 2013</a>, Paris, 4-6 d&eacute;cembre 2013.                       Cette conf&eacute;rence, organis&eacute;e par l'&Eacute;cole Polytechnique, rassemble des chercheurs et des industriels de tous les continents dans le domaine de l'Ing&eacute;nierie de Syst&egrave;mes Complexes. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">10-12-2013</TD>
            <TD valign="top">La Commission Permanente du Conseil R&eacute;gional de Lorraine a d&eacute;cid&eacute; de d&eacute;cerner le 2<sup>&egrave;me</sup> prix de la th&egrave;se, <i>fili&egrave;re scientifique</i> &agrave; Marion HELLE.
              <p class="MsoNormal" style="text-autospace:none"><b>Marion HELLE</b> est Docteur en Sciences de la vie &amp; de la Sant&eacute;. Elle est laur&eacute;ate pour ses travaux intitul&eacute;s &laquo;<i>Localisation   du ganglion sentinelle au moyen de nanoparticules fluorescentes   &eacute;mettant dans le proche infrarouge : application au cancer du sein</i>&raquo; dirig&eacute;s par&nbsp;<a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00160&amp;codelangue=FR">Fr&eacute;d&eacute;ric MARCHAL</a> et <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00082&amp;codelangue=FR" target="_self">Lina Bolotine</a>. </p>
              <p class="MsoNormal">Le   sujet de th&egrave;se portait sur la visualisation du ganglion sentinelle au   moyen de nanoparticules fluorescentes avec une application au cancer du   sein. Cette &eacute;tude a montr&eacute; dans un premier temps, l'int&eacute;r&ecirc;t de   l'utilisation des Quantum Dots pour la cartographie du ganglion   sentinelle chez les souris porteuses de tumeur. Dans un second temps,   les avantages de l'encapsulation d'un fluorophore organique dans des   nanoparticules de silice ont &eacute;t&eacute; d&eacute;montr&eacute;s. Les r&eacute;sultats de ces travaux   ont permis de prouver que ces nanoparticules ne sont pas toxiques et   qu'elles pourraient &ecirc;tre utilis&eacute;es dans des protocoles cliniques dans un   futur proche. </p>
              <p class="MsoNormal">Marion HELLE   a publi&eacute; quatre articles dans des revues internationales dont un dans   la &nbsp;prestigieuse revue &laquo;&nbsp;PLoS One&raquo;. Elle est actuellement en stage   post-doctoral <b>&nbsp;</b>pour deux ans<b> </b>au CEA de Saclay.</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">30-09-2013</TD>
            <TD valign="top"><b>Prix de th&egrave;se 2013 de l&rsquo;universit&eacute; de Lorraine d&eacute;cern&eacute;s &agrave; de jeunes docteurs du CRAN lors de la c&eacute;r&eacute;monie de rentr&eacute;e solennelle de l'Universit&eacute; de Lorraine du lundi 30 septembre 2013 &agrave; 15 heures &agrave; la salle Poirel de Nancy: </b><br>
              Marie ENNEN est laur&eacute;ate de l'&eacute;cole doctorale Biologie, Sant&eacute;, Environnement (BioSE) pour ses travaux intitul&eacute;s &laquo; Mise en &eacute;vidence d'une relation entre la prot&eacute;ine Damaged DNA Binding 2 et le facteur de transcription NF-kB : cons&eacute;quences sur les capacit&eacute;s migratrices et invasives des tumeurs mammaires &raquo; dirig&eacute;s par <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00395&amp;codelangue=FR" target="_self">Philippe BECUWE</a> et <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00403&amp;codelangue=FR" target="_self">St&eacute;phanie GRANDEMANGE</a> et financ&eacute;s par un contrat doctoral. Les r&eacute;sultats de la th&egrave;se permettent de mieux comprendre comment les cellules canc&eacute;reuses mammaires progressent vers un &eacute;tat m&eacute;tastatique et montrent ainsi le r&ocirc;le &eacute;mergent de la prot&eacute;ine DDB2 en recherche clinique, en tant que marqueur pr&eacute;dictif de la progression tumorale mammaire. Marie a publi&eacute; deux articles dans des revues internationales dont un dans la  prestigieuse revue &laquo; Cancer Research &raquo; et deux communications dans des colloques internationaux. La qualit&eacute; de ses communications orales lui a permis l'attribution en 2012 du prix de la journ&eacute;e Claude Huriet organis&eacute;e conjointement par le CHU et la facult&eacute; de m&eacute;decine de Nancy et le prix Merck-Serono attribu&eacute; en 2011 lors des journ&eacute;es ONCOTRANS. Elle est actuellement en stage post-doctoral  &agrave; l'institut de g&eacute;n&eacute;tique et de biologie mol&eacute;culaire et cellulaire (IGBMC), Strasbourg financ&eacute; par une bourse INSERM.
              <p><a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00177&amp;codelangue=FR" target="_self">J&eacute;r&eacute;my PARRIAUX</a> est laur&eacute;at de l'&eacute;cole doctorale Informatique, Automatique, &Eacute;lectronique - &Eacute;lectrotechnique, Math&eacute;matiques (IAEM) pour ses travaux intitul&eacute;s &laquo; Contr&ocirc;le, synchronisation et chiffrement &raquo; dirig&eacute;s par <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00165&amp;codelangue=FR" target="_self">Gilles MILLERIOUX</a> et Philippe GUILLOT (Universit&eacute; de Paris VIII) et financ&eacute;s par un contrat doctoral. Des solutions &agrave; des probl&egrave;mes ouverts en th&eacute;orie du contr&ocirc;le tels que la caract&eacute;risation de la platitude pour des classes de syst&egrave;mes dynamiques hybrides ont &eacute;t&eacute; propos&eacute;es avec succ&egrave;s. Fort de ces r&eacute;sultats, il a &eacute;t&eacute; montr&eacute; que les structures autosynchronisantes de chiffreurs connus &agrave; ce jour ne sont en r&eacute;alit&eacute; qu'un cas particulier d'une classe beaucoup plus large d&eacute;finie par des syst&egrave;mes plats dans le cadre bool&eacute;en. Ceci ouvre des perspectives prometteuses pour proposer de nouveaux chiffreurs. Les travaux de J&eacute;r&eacute;my, &agrave; caract&egrave;re th&eacute;orique, ont &eacute;t&eacute; diffus&eacute;s dans des revues d'automatique et de cryptographie : trois articles de revue internationale et six communications internationales. Il est actuellement ing&eacute;nieur de d&eacute;veloppement en &eacute;lectronique num&eacute;rique dans la soci&eacute;t&eacute; Elsys-Design.</p></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">27-09-2013</TD>
            <TD valign="top"><b>La nuit des chercheurs, 1ere &eacute;dition</b>
          &quot;<b>Physiciens et neurologues main dans la main</b>&quot; interview de <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00144&amp;codelangue=FR">L.&nbsp;Koessler</a> - CRAN. <a href="../imageactu/Nuit%20des%20chercheurs-pdf.pdf" target="_blank">L'article dans l'Est R&eacute;publicain</a> (pdf)</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">26-06-2013</TD>
            <TD valign="top"><a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00243&amp;codelangue=FR">Vincent Laurain</a> et <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00138&amp;codelangue=FR">C&eacute;dric Join</a>, invit&eacute;s de l'&eacute;mission libre-&eacute;change sur Radio Cara&iuml;bes Nancy <a href="http://www.rcn-radio.org/nos-podcasts/">http://www.rcn-radio.org/nos-podcasts/</a> du 26/06/13</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">08-06-2013</TD>
            <TD valign="top">Le 8 juin 2013, dans les Grands Salons de l'H&ocirc;tel de Ville, sous la pr&eacute;sidence de Monsieur le Maire et ancien Ministre Andr&eacute; ROSSINOT et en pr&eacute;sence de monsieur le Pr&eacute;fet Rapha&euml;l BARTOLD, <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00248&amp;codelangue=FR" target="_self">Louis Maillard</a> a  re&ccedil;u, au titre du CRAN, une aide de 171 000 &euro; pour l' &quot;&Eacute;tude des m&eacute;canismes c&eacute;r&eacute;braux de la perception des visages chez l'Homme &agrave; partir d'enregistrements intra-c&eacute;r&eacute;braux de neurones individuels et de populations de neurones&quot;. Ce projet est l'un des six s&eacute;lectionn&eacute;s au plan national par la F&eacute;d&eacute;ration de Recherche sur le Cerveau. Les fonds ont &eacute;t&eacute; collect&eacute;s par une action nationale du Rotary.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">12-04-2013</TD>
            <TD valign="top"><b>Prix des meilleures th&egrave;ses du GdR MACS (Mod&eacute;lisation, Analyse et Conduite des Syst&egrave;mes dynamiques) :</b><br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00015&amp;codelangue=FR">Carlos Alberto Cavichioli Gonzaga</a>, pour ses travaux intitul&eacute;s &laquo; Analyse de stabilit&eacute; et de performances d'une classe de syst&egrave;mes non-lin&eacute;aires &agrave; commutations en temps discret &raquo; Directeur de th&egrave;se : <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00100&amp;codelangue=FR">Jamal Daafouz</a>, co-directeur : <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00139&amp;codelangue=FR">Marc Jungers</a><br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00146&amp;codelangue=FR">Sylvain Kubler</a>, pour ses travaux intitul&eacute;s &laquo; Premiers travaux relatifs au concept de mati&egrave;re communicante : processus de diss&eacute;mination des informations relatives au produit &raquo; Directeur de th&egrave;se : <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00199&amp;codelangue=FR">Andr&eacute; Thomas</a>, co-directeur : <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00190&amp;codelangue=FR">Eric Rondeau</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">12-04-2013</TD>
            <TD valign="top"><b>Attribution du prix 2013 de l'IFAC France &agrave; <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00175&amp;codelangue=FR" target="_self">Herv&eacute; Panetto</a>, professeur au CRAN,</b> dans le cadre de sa pr&eacute;sidence du Comit&eacute; Technique 5.3 T Enterprise Integration and Networking t de l&rsquo;IFAC (International Federation of Automatic Control)
              </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-03-2013</TD>
            <TD valign="top">Edition prochaine de l'ouvrage &quot;Service Orientation in Holonic and Multi Agent Manufacturing and Robotics&quot;. Cet ouvrage &eacute;dit&eacute; par Theodor Borangiu, Andr&eacute; Thomas du CRAN et Damien Trentesaux et publi&eacute;&nbsp;chez Springer-Verlag contient une s&eacute;lection de communications pr&eacute;sent&eacute;es lors du 2nd International&nbsp;Workshop SOHOMA'12 qui s'est tenu &agrave; Bucarest, Roumanie les 23-24 mai 2012. Sa sortie est pr&eacute;vue en mars 2013. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">06-03-2013</TD>
            <TD valign="top"><b>"Cerveau : les avanc&eacute;es de la recherche" : travaux issus d&rsquo;une &eacute;quipe du CRAN</b> pr&eacute;sent&eacute;s dans l&rsquo;&eacute;mission <a href="http://www.vosgestelevision.tv/societe/la-thematique" target="_blank">"La Th&eacute;matique" de la Web-TV Vosges T&eacute;l&eacute;vision</a>.<br>
              Ces travaux sont r&eacute;alis&eacute;s dans le cadre du projet ESPaCE du d&eacute;partement "Sant&eacute; - Biologie - Signal" (SBS) du CRAN, en collaboration avec le CNRS et le CHU de Nancy.<br>
              Pour la cognition comme pour soigner certaines pathologies (&eacute;pilepsie, alzheimer), les recherches men&eacute;es sur le fonctionnement du cerveau, et ses dysfonctionnements, passent par des &eacute;tudes de son activit&eacute; &eacute;lectrique et magn&eacute;tique, un des travaux du CRAN.
              Avec :<br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00093&amp;codelangue=FR" target="_self">Thierry Cecchin</a>, professeur des universit&eacute;s en traitement du signal &agrave; l&rsquo;<a href="http://www.iutsd.uhp-nancy.fr" target="_blank">IUT de Saint-Di&eacute;-des-Vosges</a><br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00144&amp;codelangue=FR" target="_self">Laurent Koessler</a>, charg&eacute; de recherche en neurosciences au <a href="http://www.dr6.cnrs.fr" target="_blank">CNRS</a><br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00263&amp;codelangue=FR" target="_self">Jacques Jonas</a>, assistant-chef de clinique en neurologie au <a href="http://www.chu-nancy.fr" target="_blank">CHU de Nancy</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">03-12-2012</TD>
            <TD valign="top"><b>Prix de th&egrave;se 2012 de l&rsquo;universit&eacute; de Lorraine : 
              </b><br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00075&amp;codelangue=FR">Denise Bechet</a> est laur&eacute;ate pour ses travaux&nbsp;
              sur le &laquo; Traitement photodynamique interstitiel vasculaire st&eacute;r&eacute;otaxique des tumeurs c&eacute;r&eacute;brales guid&eacute;&nbsp;
              par imagerie &quot;Int&eacute;r&ecirc;t des nanoparticules multifonctionnelles ciblant neuropiline-1 &raquo;&nbsp;
              dirig&eacute;s par <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00217&amp;codelangue=FR">Muriel Barberi-Heyob</a> et C&eacute;line Frochot.<br>
              - <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00209&amp;codelangue=FR">Esma Yahia</a> est laur&eacute;ate pour ses travaux intitul&eacute;s &laquo;Contribution &agrave; l&rsquo;&eacute;valuation de l&rsquo;interop&eacute;rabilit&eacute; s&eacute;mantique entre syst&egrave;mes d&rsquo;information d&rsquo;entreprises : Application aux syst&egrave;mes d&rsquo;information de pilotage de la production&raquo; dirig&eacute;s par <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00175&amp;codelangue=FR">Herv&eacute; Panetto</a> et <a href="<?php echo $GLOBALS['../racine_site'] ?>detailindividupublic.php?appel=annuaire&amp;codetheme=&amp;codeindividu=00072&amp;codelangue=FR">Alexis Aubry</a>. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">04-12-2012</TD>
            <TD valign="top"><B>Remise du titre de Docteur Honoris Causa de l&#700;UL et conf&eacute;rence du Professeur Panos J. Antsaklis</B><BR>A l&#700;occasion de sa venue &agrave; Nancy pour recevoir le titre de Docteur Honoris Causa de l&#700;Universit&eacute; de Lorraine, le Professeur Panos Antsaklis donnera une conf&eacute;rence le Mardi 4 d&eacute;cembre 2012 &agrave; 10h
              Campus scientifique Victor Grignard Grand Amphith&eacute;atre du LORIA / INRIA 
              "On the Control of Cyber-Physical Systems"<br><a href="../imageactu/Affiche_P_Antsaklis.pdf" target="_blank" class="noircalibri11"> Affiche de la pr&eacute;sentation</a>
              </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">04-06-2012</TD>
            <TD valign="top"><B>Mini-workshop du projet ANR ArHyCo, 4 - 5 juin 2012, Institut Henri Poincar&eacute;, Paris</B><BR>
              Le projet ANR ArHyCo organise un mini-workshop le 4 et 5 juin 2012 &agrave; l'Institut Henri Poincar&eacute; &agrave; Paris. Le programme est disponible en suivant le lien <a href ="http://arhyco.cran.uhp-nancy.fr/" target="_blank">http://arhyco.cran.uhp-nancy.fr/</a>, rubrique WORKSHOP ArHyCo.
              
              En raison d'un nombre de places limit&eacute;, l'inscription est obligatoire (mais gratuite) avant le 15 mai 2012 aupr&egrave;s de Jamal.Daafouz(at)univ-lorraine.fr en indiquant les demi-journ&eacute;es de participation (4 juin apr&egrave;s-midi ou 5 juin matin).</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">04-05-2012</TD>
            <TD valign="top"><B>Un projet, CYBERnano, laur&eacute;at du concours national d'aide &agrave; la cr&eacute;ation d'entreprises</B><BR>Le projet de cr&eacute;ation d'entreprise CYBERnano, <a href="http://www.cybernano.eu"  target="_blank">www.cybernano.eu</a>, portant sur la " BioCybernetics for Cancerology & Nanomedicine ", port&eacute; par Muriel Barberi-Heyob (CRAN-CAV), Thierry Bastogne (CRAN) & Sophie Pinel (SiGReTO), a &eacute;t&eacute; retenu comme laur&eacute;at 2012 du concours national d'aide &agrave; la cr&eacute;ation d'entreprises de technologies innovantes dans la cat&eacute;gorie "&eacute;mergence".</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">28-03-2012</TD>
            <TD valign="top"><B>D&eacute;crypter le fonctionnement du cerveau : mythe ou r&eacute;alit&eacute; ? 28/03/2012</B><BR>
              Peut-on lire dans les pens&eacute;es et les r&ecirc;ves ? Comment est crypt&eacute;e l'information dans le cerveau ? Pourquoi un m&ecirc;me tableau peut-il &ecirc;tre interpr&eacute;t&eacute; quasi simultan&eacute;ment comme une nature morte ou un visage ?
              Autant de questions sur lesquelles scientifiques et m&eacute;decins se penchent pour mieux comprendre le fonctionnement du cerveau. A long terme, ces connaissances devraient aider &agrave; l'&eacute;laboration de traitements efficaces contre les maladies neurologiques.
              Quel est l'&eacute;tat de nos connaissances actuelles ? Comment explore t-on un organe aussi complexe ? Le cerveau nous livrera t-il tous ses secrets ? 
              <a href="http://www.univ-lorraine.fr/content/d%C3%A9crypter-le-fonctionnement-du-cerveau-mythe-ou-r%C3%A9alit%C3%A9" target="_blank"><b>Venez en d&eacute;battre avec nos 3 sp&eacute;cialistes.</b></a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">02-03-2012</TD>
            <TD valign="top"><B>Prix de la 7&egrave;me Journ&eacute;e Claude HURIET de Recherche Biom&eacute;dicale du CHU & Facult&eacute; de M&eacute;decine de Nancy</B><BR>Denise Bechet, qui a pr&eacute;par&eacute; sa th&egrave;se au sein du groupe IPS, s'est vue d&eacute;cerner le prix de la meilleure communication affich&eacute;e, lors de la 7&egrave;me Journ&eacute;e Claude HURIET de la Recherche Biom&eacute;dicale du CHU et de Facult&eacute; de M&eacute;decine de Nancy qui s'est tenue le 2 mars 2012 pour ses travaux sur les nanoplateformes multifonctionnelles appliqu&eacute;es au traitement PDT des tumeurs c&eacute;r&eacute;brales.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">03-11-2011</TD>
            <TD valign="top"><B>Prix du Jeune Chercheur de la meilleure communication orale, 5&egrave;me Forum du Canc&eacute;rop&ocirc;le du Grand Est</B><BR>
              Julie Garrier, qui a pr&eacute;par&eacute; sa th&egrave;se au sein du groupe IPS, s'est vue d&eacute;cerner le Prix du Jeune Chercheur pour la meilleure communication orale, lors du 5&egrave;me Forum du Canc&eacute;rop&ocirc;le du Grand Est  
              qui s'est d&eacute;roul&eacute; les 2 et 3 novembre 2011 au palais des Congr&egrave;s &agrave; Strasbourg, pour sa pr&eacute;sentation sur l'effet tumoricide photoinduit par le ciblage cellulaire et vasculaire de tumeurs &agrave; lire sur HAL : <a href="http://hal.archives-ouvertes.fr/view_by_stamp.php?&label=CRAN&langue=fr&action_todo=view&id=hal-00643469&version=1" target="_blank">Acc&eacute;der &agrave; la communication</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">27-05-2011</TD>
            <TD valign="top"><B>Des nanom&eacute;dicaments &agrave; l'assaut du cancer : &agrave; lire dans le journal du CNRS</B><BR>Le num&eacute;ro de juin 2011 du journal du CNRS met en avant les travaux conduits par Muriel Barberi-Heyob sur les nanoparticules destin&eacute;es au traitement des tumeurs c&eacute;r&eacute;brales par phototh&eacute;rapie dynamique. Ces nanoparticules de l'ordre de 10 nm ciblent s&eacute;lectivement les vaisseaux de la tumeur, permettent de rep&eacute;rer la zone tumorale par IRM et traitent celle-ci par th&eacute;rapie photodynamique.
              
              Ces travaux sont conduits en partenariat avec le Laboratoire R&eacute;actions et G&eacute;nie des Proc&eacute;d&eacute;s (LRGP) et le Laboratoire de Chimie-Physique Macromol&eacute;culaire (LCPM) &agrave; Nancy, le Laboratoire de Physico-Chimie des Mat&eacute;riaux Luminescents (LPCML) et le Centre d'Etude et de Recherche Multimodal Et Pluridisciplinaire en imagerie du vivant (CERMEP) &agrave; Lyon, et l'unit&eacute; INSERM TH&eacute;rapies Interventionnelles Assist&eacute;es par l'Image et la Stimulation (THIAIS U703) de Lille.
              
              Pour consulter l'article : http://www.cnrs.fr/fr/pdf/jdc/257/index.html#/12/</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">04-05-2011</TD>
            <TD valign="top"><B>Un projet laur&eacute;at du concours national d'aide &agrave; la cr&eacute;ation d'entreprises innovantes</B><BR>
              Le projet de cr&eacute;ation d'entreprise sur la "Commande sans mod&egrave;le des syst&egrave;mes complexes", port&eacute; par C&eacute;dric Join (CRAN) et Michel Fliess (LIX), a &eacute;t&eacute; retenu comme laur&eacute;at 2011 du concours national d'aide &agrave; la cr&eacute;ation d'entreprises de technologies innovante dans la cat&eacute;gorie "&eacute;mergence".</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">31-03-2011</TD>
            <TD valign="top"><B>Vincent Laurain laur&eacute;at 2011 du prix des meilleures th&egrave;ses du GdR MACS</B><BR>
              Vincent Laurain s'est vu attribuer le prix des meilleures th&egrave;ses du GdR Mod&eacute;lisation, Analyse et Commande des syst&egrave;mes pour ses travaux effectu&eacute;s au CRAN sous la direction conjointe de Marion Gilson-Bagrel et d'Hugues Garnier. Il a soutenu sa th&egrave;se intitul&eacute;e "Contributions &agrave; l'identification de mod&egrave;les param&eacute;triques non lin&eacute;aires. Application &agrave; la mod&eacute;lisation de bassins versants ruraux" le 19 octobre 2010. C'est la troisi&egrave;me fois qu'une th&egrave;se pr&eacute;par&eacute;e au CRAN est distingu&eacute;e au niveau national, la th&egrave;se de C&eacute;dric Join ayant &eacute;t&eacute; r&eacute;compens&eacute;e en 2003 et celle de Jean-Philippe Georges en 2007</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">16-03-2011</TD>
            <TD valign="top"><B>Une premi&egrave;re place au palmar&egrave;s des articles Journal of Process Control les plus t&eacute;l&eacute;charg&eacute;s</B><BR>
              L'article "Fault tolerant control for a dearomatisation process" figure en premi&egrave;re place du palmar&egrave;s "Top 25 Hottest Articles - Journal of Process Control - October to December 2010". Il est issu d'une collaboration entre des chercheurs de l'Universit&eacute; de Helsinki, de la soci&eacute;t&eacute; Neste Jacobs Oy et Dominique Sauter. L'ensemble du palmar&egrave;s peut &ecirc;tre consult&eacute; ici : http://top25.sciencedirect.com/subject/engineering/12/journal/journal-of-process-control/09591524/archive/30/ 
              
              R&eacute;f&eacute;rences de l'article : Fault tolerant control for a dearomatisation process, Journal of Process Control, Volume 19, Issue 7, July 2009, Pages 1091-1102, Sourander, M.; Vermasvuori, M.; Sauter, D.; Liikala, T.; Jamsa-Jounela, S.L. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">08-03-2011</TD>
            <TD valign="top"><B>Une seconde place au palmarcs des articles AUTOMATICA les plus t&eacute;l&eacute;charg&eacute;s</B><BR>L'article "Controller synthesis for networked control systems" figure en bonne place du palmarcs "Top 25 Hottest Articles - Automatica - October to December 2010". Il est issu d'une collaboration entre des chercheurs de l'Universit&eacute; de Technologie d'Eindhoven, Jamal Daafouz du CRAN et Laurentiu Hetel, chercheur CNRS au LAGIS &agrave; Lille, qui avait pr&eacute;par&eacute; sa th&egrave;se au CRAN.
              
              L'ensemble du palmarcs peut etre consult&eacute; ici : 
              http://top25.sciencedirect.com/subject/engineering/12/journal/automatica/00051098/archive/30/
              
              R&eacute;f&eacute;rences de l'article :
              Controller synthesis for networked control systems
              Automatica, Volume 46, Issue 10, October 2010, Pages 1584-1594
              Cloosterman, M.B.G.; Hetel, L.; van de Wouw, N.; Heemels, W.P.M.H.; Daafouz, J.; Nijmeijer, H. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">20-02-2011</TD>
            <TD valign="top"><B>Le CRAN &agrave; l'honneur sur France 3 Lorraine dans l'&eacute;mission scientifique </B><BR>L'&eacute;mission "C &agrave; savoir" diffus&eacute;e dimanche 20 f&eacute;vrier 2011 &eacute;tait consacr&eacute;e aux nanotechnologies. Un focus &eacute;tait consacr&eacute; &agrave; l'utilisation, par les chercheurs du CRAN install&eacute;s au centre r&eacute;gional de lutte contre le cancer (Centre Alexis Vautrin), des nanoparticules pour des recherches sur le diagnostic et le traitement du cancer. Les chercheuses, doctorantes et post-doc ont pris plaisir &agrave; communiquer au grand public leurs avanc&eacute;es les plus r&eacute;centes dans ce domaine. 
              
              A revoir sur : <a href="http://lorraine.france3.fr/c-a-savoir-sciences/index.php?page=article&numsite=6542&id_article=16034&id_rubrique=6545" target="_blank">http://lorraine.france3.fr/c-a-savoir-sciences/index.php?page=article&numsite=6542&id_article=16034&id_rubrique=6545</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">15-02-2011</TD>
            <TD valign="top"><B>Vid&eacute;o Canal-U du Caf&eacute; des Sciences </B><BR>la <a href="http://www.canal-u.tv/canalu/producteurs/canal_u_medecine/dossier_programmes/informatique_medicale/colloque_et_evenement/cafes_des_sciences_nancy_2011_puces_rfid_identifier_pour_mieux_surveiller" target=_"blank">vid&eacute;o Canal-U</a> du Caf&eacute; des Sciences "Puces RFID : identifier pour mieux surveiller ?", qui s'est d&eacute;roul&eacute; le 15 f&eacute;vrier dans le cadre des <a href="http://20ans.esial.uhp-nancy.fr/news.php?an=1" target=_"blank">20 ans de l'ESIAL</a>.
              
              
              </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">17-12-2010</TD>
            <TD valign="top"><B>Prix Bernard Fortier pour la meilleure communication aux 6eme journ&eacute;es de recherche biom&eacute;dicale</B><BR>Julie Garrier, doctorante au sein du groupe IPS, s'est vu remettre le Prix Bernard Fortier de la meilleure communication orale en Recherche Exp&eacute;rimentale lors de la 6&egrave;me Journ&eacute;e Claude Huriet de la Recherche Biom&eacute;dicale pour sa pr&eacute;sentation sur "l'effet tumoricide photoinduit par le ciblage vasculaire et cellulaire de tumeurs".</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">02-12-2010</TD>
            <TD valign="top"><B>Premier prix du chercheur du Conseil R&eacute;gional 2010 attribu&eacute; &agrave; Jamal Daafouz</B><BR>La R&eacute;gion Lorraine a d&eacute;cern&eacute; le premier prix du chercheur 2010 &agrave; Jamal Daafouz pour ses travaux sur la stabilisation des syst&egrave;mes lin&eacute;aires &agrave; param&egrave;tres variants (LPV) et des syst&egrave;mes lin&eacute;aires &agrave; commutation. Les r&eacute;sultats portent &agrave; la fois sur des contributions fondamentales et sur des applications sid&eacute;rurgiques d&eacute;velopp&eacute;es en partenariat avec ArcelorMittal R&D. </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-10-2010</TD>
            <TD valign="top"><B>Nomination &agrave; l'Institut Universitaire de France</B><BR>Jamal Daafouz est nomm&eacute; membre junior de l'IUF pour une p&eacute;riode de cinq ans &agrave; compter du 1er octobre 2010.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">18-05-2010</TD>
            <TD valign="top"><B>SIRASAS : Prix de la meilleure publication scientifique de la FRAE</B><BR>Le projet SIRASAS "Strat&eacute;gies innovantes et robustes pour l'autonomie des syst&egrave;mes a&eacute;ronautiques et spatiaux" a re&ccedil;u le prix de la meilleure publication scientifique d&eacute;cern&eacute; par la Fondation de Recherche pour l'A&eacute;ronautique et l'Espace. <a href="http://www.fnrae.org/" target="_blank">En savoir plus</a></TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">07-05-2010</TD>
            <TD valign="top"><B>Mohamed Darouach, docteur honoris causa de l'Universit&eacute; technique de Iasi (Roumanie)</B><BR>Mohamed Darouach, professeur des universit&eacute;s en automatique &agrave; l'Universit&eacute; Henri Poincar&eacute;, IUT  Longwy, et directeur adjoint du CRAN a &eacute;t&eacute; distingu&eacute; du titre de docteur honoris causa de l'Universit&eacute; technique de Iasi (Roumanie). Cette prestigieuse distinction vient couronner ses nombreux apports scientifiques dans le domaine de la validation des donn&eacute;es et du diagnostic de l'&eacute;tat des syst&egrave;mes de grande dimension et dans celui des capteurs logiciels (observateurs) et leurs applications dans le contr&ocirc;le des syst&egrave;mes complexes. Ses travaux ont donn&eacute; lieu &agrave; de nombreuses publications dans des revues internationales  de premier plan dans le domaine de l'automatique. Il a &eacute;t&eacute; professeur invit&eacute; &agrave; l'universit&eacute; de Toronto et &agrave; l'universit&eacute; d'Alberta au Canada.</TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-12-2009</TD>
            <TD valign="top"><B>Le CRAN contribue au projet de recherche SIRASAS</B><BR>Le CRAN contribue au projet de recherche  <a href="https://extranet.ims-bordeaux.fr/External/SIRASAS" target="_blank">SIRASAS</a>, "Strat&eacute;gies innovantes et robustes pour l'autonomie des syst&egrave;mes a&eacute;ronautiques et spatiaux", pr&eacute;sent&eacute; dans le num&eacute;ro 239 du journal du CNRS de d&eacute;cembre 2009. <a href="http://www2.cnrs.fr/presse/journal/4617.htm" target="_blank">En savoir plus</a>
              </TD>
          </TR>
          <TR>
            <TD valign="top" nowrap="nowrap">01-11-2009</TD>
            <TD valign="top"><B>Les travaux en canc&eacute;rologie du CRAN sont mis &agrave; l'honneur dans le Journal du CNRS de novembre 2009</B><BR><a href="http://www2.cnrs.fr/presse/journal/4587.htm" target="_blank">En savoir plus...</a><br>
            </TR>
          </TABLE>
          </TD></TR>
          </TABLE>
				</div>

		<!-- InstanceEndEditable -->
    	</div>
    </div>
	</div>
</div>  
</body>
<!-- InstanceEnd --></html>