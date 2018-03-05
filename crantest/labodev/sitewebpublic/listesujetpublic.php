<?php require_once('../_const_fonc.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/cran_base_php.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-latin-1" />
<meta name="description" content="<?php echo meta_description() ?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CRAN - <?php echo $codelangue=='FR'?'Formation par la recherche':'Training via research' ?></title>
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
/*
* @(#)listesujetpublic.php
Realise un get ou un post 
Le premier get fait apparaitre le bloc search et positionne codelangue dans session.putValue("codelangue",codelangue)
Le post positionne req dans session.putValue("derniererequetesujet",req), fait apparaitre le bloc search et affiche les resultats du search
Le get suivant un post reutilise session["derniererequetesujet"]: cela permet de changer de codelangue
A FAIRE: si parametre codelangue pas present: renvoyer a la page de garde

*/
$listetypesujet_a_afficher='02,03,04';//stage master, these post-doc
$tab_typesujet=array();
$methode=strtolower($_SERVER['REQUEST_METHOD']);//get ou post
$codelangue=""; 
$motcle="";$codetheme="";//interrogation sur un theme
$codetypesujet="";
$blocsearch="O";//on affiche le bloc search par defaut
$hashLib=Array();
$clausewhere="";//par defaut la recherche se fera sans critere
$selected="";
$appel=(isset($_REQUEST['appel'])?$_REQUEST['appel']:"form_rech");
$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");

$submit=(isset($_REQUEST['rechercher'])?$_REQUEST['rechercher']:"reset");
//si methode get
if($methode=="get")
{ // si pas de codelangue: FR par defaut
	$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue']:"FR"); 
	$blocsearch=(isset($_REQUEST['blocsearch'])?$_REQUEST['blocsearch']:"O");
	// si un post a deja ete fait: on recupere les donnees du dernier post
	if(isset($_SESSION['derniererequetesujet']) && ($appel==""||$appel=="form_rech"))
	{ $requete=$_SESSION['derniererequetesujet'];
	}
	else//il n'y a pas eu de post: la requete est un get qui n'affichera que le bloc search
	{ $requete=$_REQUEST;
	}
	$_SESSION['codelangue']=$codelangue;
	$motcle=(isset($requete['motcle'])?$requete['motcle']:"");
	$codetheme=(isset($requete['codetheme'])?$requete['codetheme']:"");
	$codetypesujet=(isset($requete['codetypesujet'])?$requete['codetypesujet']:"");
}
else//methode=post
{ // si un get a deja ete fait, un codelangue devrait deja exister
	$requete=$_REQUEST;
	$_SESSION['derniererequetesujet']=$requete;
	if(isset($_SESSION['codelangue']))
	{ $codelangue=$_SESSION['codelangue'];
	}
	else//codelangue=FR par defaut
	{ $codelangue="FR";
	}
	if($submit=="reset")
	{ $motcle="";
		$codetheme="";
		$codetype="";
	}  
	else
	{ $motcle=(isset($requete['motcle'])?$requete['motcle']:"");
		$codetheme=(isset($requete['codetheme'])?$requete['codetheme']:"");
		$codetypesujet=(isset($requete['codetypesujet'])?$requete['codetypesujet']:"");
	}
}
//get ou post
$clausewhere="";
$clausewhere.=" and lower(motscles_".strtolower($codelangue).") like ".GetSQLValueString('%'.strtolower($motcle).'%', "text");
$clausewhere.=" and codetheme like '".$codetheme."%'";
$clausewhere.=" and sujet.codetypesujet like '".$codetypesujet."%'";
$clausewhere.=" and titre_".strtolower($codelangue)."<>''";
// recup des libelles des zones de texte de la page

$query_rslib= "select nomzone,contenuzone from libpagehtml where (codepagehtml='listesujetpublic' ".
							"or codepagehtml='TOUTES') and codelangue='".strtolower($codelangue)."' order by nomzone";
$rslib=mysql_query($query_rslib, $cran) or die(mysql_error());
while($row_rslib=mysql_fetch_assoc($rslib))
{ $hashLib[$row_rslib['nomzone']]=$row_rslib['contenuzone'];
}

// hyperliens vers master proposes, pourvus, theses proposees,pourvues
$txt_propose=($codelangue=='FR'?'propos&eacute;s':'proposed');
$txt_pourvu=($codelangue=='FR'?'pourvus':'assigned');
$txt_titre_page_dept="";

// hyperliens vers sujets proposes, pourvus
$query_rs="select * from typesujet where find_in_set(codetypesujet,'".$listetypesujet_a_afficher."') order by numordre";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typesujet[$row_rs['codetypesujet']]=$row_rs;
}
$txt_sujet=($codelangue=='FR'?'Sujets de':'Projects');
$txt_titre_page_dept.=($codelangue=='FR'?$txt_sujet:'');// en EN : apres le texte

foreach($tab_typesujet as $uncodetypesujet=>$tab_uncodetypesujet)
{	$aumoinsunsujet_uncodetypesujet=false;
	if($uncodetypesujet=='02')
	{ $libtypesujet='Master';
	}
	else
	{ $libtypesujet=$tab_uncodetypesujet['libcourt_'.strtolower($codelangue)];
	}
	$txt_typesujet='&nbsp;<span class="mauvecalibri11">'.$libtypesujet.'</span>';
	// Hyperlien vers les sujets proposes
	$query_rs="select * from sujet,sujettheme where sujet.codesujet<>'' ".
						" and codestatutsujet='V' and afficher_sujet_web='oui'".
						" and sujet.codesujet=sujettheme.codesujet ".
						" and (".periodeencours('sujet.datedeb_sujet','sujet.datefin_sujet').' or '.periodefuture('sujet.datedeb_sujet').')'.
						" and sujet.datedeb_sujet>=".GetSQLValueString((date("Y")-2).'/'.date("m").'/'.date("j"), "text").
						" and sujet.codetypesujet=".GetSQLValueString($uncodetypesujet, "text").
						" and (sujet.codesujet not in (select codesujet from individusujet) or sujet.afficher_sujet_propose='oui')". 
						$clausewhere;
	$rs=mysql_query($query_rs) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ // s'il y a des sujets proposes
		$aumoinsunsujet_uncodetypesujet=true;
		$txt_typesujet.='&nbsp;[<a href="#sujet'.$uncodetypesujet.'proposes">'.$txt_propose.'</a>]';
	}
	// Hyperlien vers les sujets pourvus 
	$query_rs="select * from sujet,individusujet,individusejour,sujettheme".
						" where sujet.codesujet<>'' ".
						" and (codestatutsujet='V' || codestatutsujet='P') and afficher_sujet_web='oui'".
						" and sujet.codesujet=sujettheme.codesujet and sujet.codesujet=individusujet.codesujet".
						" and individusujet.codeindividu=individusejour.codeindividu".
						" and individusujet.numsejour=individusejour.numsejour".
						" and ".periodeencours('datedeb_sejour','datefin_sejour').
						" and sujet.codetypesujet=".GetSQLValueString($uncodetypesujet, "text")." and sujet.afficher_sujet_propose<>'oui'".
						$clausewhere;
	$rs=mysql_query($query_rs, $cran) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $txt_typesujet.='&nbsp;[<a href="#sujet'.$uncodetypesujet.'pourvus">'.$txt_pourvu.'</a>]';
		$aumoinsunsujet_uncodetypesujet=true; 
	}
	if($aumoinsunsujet_uncodetypesujet)
	{ $txt_titre_page_dept.=$txt_typesujet;
	}
}
$txt_titre_page_dept.=($codelangue=='EN'?'&nbsp;'.$txt_sujet:'');
?><div class="titre_page_dept">
    <table width="100%">
    	<tr>
      <td><?php echo $txt_titre_page_dept ?>
      </td>
			<?php if($codelangue=='FR')
      {?> <td>
        <span id="sprytrigger1"><img src="../images/b_info.png">Ecoles Doctorales, Masters</span>
        <div class="tooltipContent_cadre_rose_dd04b1_fond_fff" id="sprytooltip1">
        <span  class="noircalibri10">
        <div align="center">Le laboratoire est reconnu par les &eacute;coles doctorales</div>
        </span>
        <p class="noircalibri10"><a href="http://www.adum.fr/as/ed/IAEM/page.pl?page=accueil" target="_blank">Informatique, Automatique, Electronique, Math&eacute;matiques - IAEM</a><br>
          <a href="http://www.biose.uhp-nancy.fr" target="_blank">Biologie Sant&eacute; Environnement - BioSE</a><br>
          <br />
          A consulter : 
          <a href="http://www.campusfrance.org/fr/d-catalogue/campusbourse/cfbourse/index.html" target="_blank"> Site de l' &quot;Annuaire des programmes de bourses&quot;</a><span  class="noircalibri10">
          </span></p>
        <span  class="noircalibri10">
        <div align="center">Les masters</div>
        <p class="noircalibri10">
          <a href="http://formations.univ-lorraine.fr/fr-FR/fiche/presentation/UL-PROG2677?__1=__domaine&__2=AND&__3=__diplome&__4=AND&__5=__modalite&__6=AND&__7=__acces&__8=AND&__9=lieu&__10=AND&__11=__deroulement&__12=AND&__13=__intitule_pt_AND&__domaine=&__diplome=TI-ma&__modalite=&__acces=&__lieu=&__deroulement=&__intitule_pt_AND=#debut" target="_blank">
            Ing&eacute;nierie de Syst&egrave;mes Complexes - ISC</a><br>
          <a href="http://formations.univ-lorraine.fr/fr-FR/fiche/presentation/UL-PROG2053?__1=__domaine&__2=AND&__3=__diplome&__4=AND&__5=__modalite&__6=AND&__7=__acces&__8=AND&__9=lieu&__10=AND&__11=__deroulement&__12=AND&__13=__intitule_pt_AND&__domaine=&__diplome=TI-ma&__modalite=&__acces=&__lieu=&__deroulement=&__intitule_pt_AND=#debut" target="_blank">
            BioSciences et Ing&eacute;nierie de la Sant&eacute; - BSIS</a></p>
        </span>
        </div>
        <script type="text/javascript">
      var sprytooltip1 = new Spry.Widget.Tooltip("sprytooltip1", "#sprytrigger1", {offsetX:-250, offsetY:0, closeOnTooltipLeave:true});
        </script>
        </td>
        <?php 
				}?>
      </tr>
      </table>
    </div>
	<?php // bloc search avec ou sans les parametres entres par l'utilisateur
  if($blocsearch!="N")
  { ?>
	<div style="border-left: solid 1px #CCC;border-right: solid 1px #CCC;border-bottom: solid 1px #CCC">
  <form name="formsearch" action="<?php echo $_SERVER['../PHP_SELF'] ?>" method="post">
  <input type="hidden" name="appel" value="<?php echo $appel ?>">
  <table>
  <tr>
  	<td>
    	<table>
  			<tr>
        	<td align="left" nowrap><?php echo $hashLib['motcle'] ?>&nbsp;
          <input type="text" name="motcle" class="noircalibri10" value="<?php echo $motcle ?>">
       		</td>
          <td align="left" nowrap><?php echo $codelangue=='FR'?'D&eacute;partement':'Department' ?>&nbsp;
            <select name="codetheme" class="noircalibri10" >
            <?php // theme
            $query_rs="select codestructure as codetheme,liblong_".strtolower($codelangue)." as libtheme ".
                          " from structure where (esttheme='oui' or codestructure='') and ".periodeencours('date_deb','date_fin')." order by codestructure";
            $rs=mysql_query($query_rs, $cran) or die(mysql_error());
            while($row_rs=mysql_fetch_assoc($rs))
            { ?>
              <option class="noircalibri10" <?php echo ($row_rs['codetheme']==$codetheme?" selected ":"") ?> value="<?php echo $row_rs['codetheme'] ?>" ><?php echo $row_rs['libtheme'] ?></option>
            <?php
            }?>
            </select>
          </td>
          <td align="left" nowrap>Type :&nbsp;
            <select name="codetypesujet" >
            <?php // type de sujet
            $query_rs="select codetypesujet,libcourt_".strtolower($codelangue)." as libtypesujet from typesujet order by codetypesujet";
            $rs=mysql_query($query_rs, $cran) or die(mysql_error());
            while($row_rs=mysql_fetch_assoc($rs))
            { ?>
              <option class="noircalibri10" <?php echo ($codetypesujet==$row_rs['codetypesujet']?' selected ':'') ?> value="<?php echo $row_rs['codetypesujet'] ?>"><?php echo ($row_rs['codetypesujet']=='02'?'Master':$row_rs['libtypesujet']) ?></option>
            <?php 
						}?>
            </select>
          </td>
          <td align="left"><input type="submit"  name="rechercher" class="noircalibri10" value="<?php echo $hashLib['rechercher'] ?>"></td>
          <td align="left"><input type="submit"  name="reset" class="noircalibri10" value="<?php echo strtolower($codelangue)=='fr'?'Effacer les crit&egrave;res':'Reset'  ?>" 
          									onClick="formsearch.motcle.value='';formsearch.codetheme.options[0].selected=true;formsearch.codetypesujet.options[0].selected=true;form.submit;">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
  </tr>
  </table>
	</form>
  </div>
  <?php 
	}//fin bloc search
	?>
	<div>
	<?php 
  $query_rs="select *  from sujet,sujettheme where sujet.codesujet<>'' ".
             " and (codestatutsujet='V' or codestatutsujet='P')  and afficher_sujet_web='oui'".
             " and sujet.codesujet=sujettheme.codesujet ".
             $clausewhere;
  $rs=mysql_query($query_rs, $cran) or die(mysql_error());
  if($row_rs=mysql_fetch_assoc($rs))// s'il y a des sujets a afficher
  { ?>
  <table width="100%" cellpadding="3">
  <?php $query_rs_sujet_theme="select codesujet,codetheme,liblong_".strtolower($codelangue)." as libtheme".
															" from sujettheme,structure ".
															" where ".periodeencours('structure.date_deb','structure.date_fin').
															//" and sujettheme.codesujet=".GetSQLValueString($row_rs_sujet['codesujet'], "text").
															" and sujettheme.codetheme=structure.codestructure ". 
															" order by codesujet,sujettheme.codetheme";
				$rs_sujet_theme=mysql_query($query_rs_sujet_theme, $cran) or die(mysql_error());
				$tab_sujet_libtheme=array();
				while($row_rs_sujet_theme=mysql_fetch_assoc($rs_sujet_theme))
				{ $tab_sujet_libtheme[$row_rs_sujet_theme['codesujet']][$row_rs_sujet_theme['codetheme']]=$row_rs_sujet_theme;
				}
				mysql_free_result($rs_sujet_theme);
				
				// on affiche un individu que s'il est autorise
				// tous les visas de tous les individus,sejours
				$query_rs="select codeindividu,numsejour,coderole".
									" from individustatutvisa,statutvisa".
									" where  individustatutvisa.codestatutvisa=statutvisa.codestatutvisa order by codeindividu,numsejour";
				$rs= mysql_query($query_rs) or die(mysql_error());
				while($row_rs=mysql_fetch_assoc($rs))
				{ $tab_tout_individustatutvisa[$row_rs['codeindividu']][$row_rs['numsejour']][$row_rs['coderole']]=$row_rs['coderole'];
				}

				// liste ordonnee des individus,sejours dans le temps avec dates de sejour, date autorisation 
				$query_rs="select codeindividu, numsejour, datedeb_sejour, datefin_sejour, date_autorisation from individusejour order by codeindividu,datedeb_sejour";
				$rs=mysql_query($query_rs) or die(mysql_error());
				while($row_rs=mysql_fetch_assoc($rs))
				{ $tab_dates_sejour[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
				}

        // pour les 'V' : dates sujet (les date séjour '' permettent de réaliser l'UNION). Doivent etre en cours ou futurs
        // pour les 'P' : datedeb_sejour en tant que datedeb_sujet, datefin_sujet sera ensuite calculé en fonction des dates de séjour
        // ne traite pas le cas de plusieurs individus associés a un sujet
	      $query_rs= 	"(select distinct sujet.codesujet,sujet.titre_".strtolower($codelangue)." as titresujet,".
                    " typesujet.libcourt_".strtolower($codelangue)." as libtypesujet,sujet.codetypesujet,sujet.codestatutsujet,typesujet.numordre,".
                    " datedeb_sujet as datedebtri,datedeb_sujet,datefin_sujet,'' as codeindividu,'' as numsejour,'' as nom,'' as prenom,'' as datedeb_sejour,'' as datefin_sejour, ".
										" '' as date_autorisation,'' as datedeb_sejour_prevu,'' as datefin_sejour_prevu,'propose' as statut".
                    " from sujet,sujettheme,typesujet,structure".
                    " where sujet.codesujet<>''  and afficher_sujet_web='oui'".
                    " and (sujet.codestatutsujet='V' or sujet.codestatutsujet='P')".
                    " and (".periodeencours('datedeb_sujet','datefin_sujet').' or '.periodefuture('sujet.datedeb_sujet').')'.
										" and sujet.datedeb_sujet>=".GetSQLValueString((date("Y")-1).'/'.date("m").'/'.date("j"), "text").
                    " and sujet.codesujet=sujettheme.codesujet ".
                    " and sujet.codetypesujet=typesujet.codetypesujet ".
                    " and sujettheme.codetheme=structure.codestructure".
                    " and (structure.esttheme='oui' or structure.codestructure='') and ".periodeencours('structure.date_deb','structure.date_fin').
                    " and find_in_set(sujet.codetypesujet,'".$listetypesujet_a_afficher."')".
                    " and (sujet.codesujet not in (select codesujet from individusujet) or sujet.afficher_sujet_propose='oui')". 
                    $clausewhere.
                    " GROUP BY sujet.codesujet)".
                    " UNION".
                    " (select distinct sujet.codesujet,sujet.titre_".strtolower($codelangue)." as titresujet,".
                    " typesujet.libcourt_".strtolower($codelangue)." as libtypesujet,sujet.codetypesujet,sujet.codestatutsujet ,typesujet.numordre,".
                    " datedeb_sejour as datedebtri,datedeb_sujet,datefin_sujet,individu.codeindividu,individusujet.numsejour,nom,prenom, datedeb_sejour,datefin_sejour,".
										" date_autorisation, datedeb_sejour_prevu,datefin_sejour_prevu,'pourvu' as statut".
                    " from sujet,sujettheme,typesujet,individusujet,structure,individusejour,individu ".
                    " where sujet.codesujet<>'' and afficher_sujet_web='oui' and (sujet.codestatutsujet='V' || sujet.codestatutsujet='P')".
                    " and sujet.codesujet=sujettheme.codesujet ".
                    " and sujet.codetypesujet=typesujet.codetypesujet ".
                    " and sujettheme.codetheme=structure.codestructure".
                    " and (structure.esttheme='oui' or structure.codestructure='') and ".periodeencours('structure.date_deb','structure.date_fin').
                    " and find_in_set(sujet.codetypesujet,'".$listetypesujet_a_afficher."')".
                    " and individusujet.codesujet=sujet.codesujet".
                    " and individusujet.codeindividu=individusejour.codeindividu and individusujet.numsejour=individusejour.numsejour".
										" and individusejour.codeindividu=individu.codeindividu".
                    " and (".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').' or '.periodefuture('individusejour.datedeb_sejour').')'.
                    " and individusejour.codeindividu<>'' and sujet.afficher_sujet_propose<>'oui'".
										$clausewhere.
                    " GROUP BY sujet.codesujet)". 
                    " order by numordre,statut DESC,datedebtri DESC";
	//echo $query_rs;
  $rs_sujet=mysql_query($query_rs, $cran) or die(mysql_error());
  foreach($tab_typesujet as $uncodetypesujet=>$tab_uncodetypesujet)
  { $first['sujet'.$uncodetypesujet.'proposes']=false;//indicateur permettant d'afficher une ligne "sujets de typesujet proposes"
    $first['sujet'.$uncodetypesujet.'pourvus']=false;//indicateur permettant d'afficher une ligne "sujets de typesujet pourvus"
  }
  $class="even";
  while($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
  { foreach($tab_typesujet as $uncodetypesujet=>$tab_uncodetypesujet)// lignes d'entete pour chaque type de sujet
    { if($uncodetypesujet=='02')
			{ $libtypesujet='Master';
			}
			else
			{ $libtypesujet=$tab_uncodetypesujet['libcourt_'.strtolower($codelangue)];
			}
			
			if($row_rs_sujet['codetypesujet']==$uncodetypesujet && $row_rs_sujet['statut']=="propose" && !$first['sujet'.$uncodetypesujet.'proposes'])
      { $first['sujet'.$uncodetypesujet.'proposes']=true;
      ?>
        <tr class="head" align="center">
          <td colspan="4"><a name="sujet<?php echo $uncodetypesujet ?>proposes"><?php echo $libtypesujet.'&nbsp;['.$txt_propose.']' ?></a>
          </td>
        </tr>
      <?php 
      }
      if($row_rs_sujet['codetypesujet']==$uncodetypesujet && $row_rs_sujet['statut']=="pourvu" && !$first['sujet'.$uncodetypesujet.'pourvus'])
      { $first['sujet'.$uncodetypesujet.'pourvus']=true;
      ?>
        <tr class="head" align="center">
          <td colspan="4"><a name="sujet<?php echo $uncodetypesujet ?>pourvus"><?php echo $libtypesujet.'&nbsp;['.$txt_pourvu.']' ?></a>
          </td>
        </tr>
      <?php
      }
    }?>
    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
      <td align="center" nowrap><?php echo ($row_rs_sujet['codetypesujet']=='02'?'Master':$row_rs_sujet['libtypesujet']) ?><br>
        (<?php echo $row_rs_sujet['statut']=="propose"?$hashLib['propose']:$hashLib['pourvu'] ?>)
      </td>
      <td>
        <?php
        // dates affichées de sujets= dates séjour. 
        // Calage de dates pour postdoc sur datedeb_sejour en tant que postdoc (sejour peut etre plus long) 
        $datedeb_sujet=$row_rs_sujet['datedeb_sujet']; 
        $datefin_sujet=$row_rs_sujet['datefin_sujet'];
        if($row_rs_sujet['datedeb_sejour']!='')
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
        echo $datedeb_sujet?>
        <br>
        <?php 
        echo $datefin_sujet ?>
        
      </td>
      <td>
        <?php 
      foreach($tab_sujet_libtheme[$row_rs_sujet['codesujet']] as $codetheme=>$row_rs)
      { ?>
        <table width="100%" border="0">
          <tr>
            <td nowrap><?php echo $row_rs['libtheme'] ?>
            </td>
          </tr>
        </table>
      <?php 
      }?>
      </td>
      <td>
        <a href="detailsujetpublic.php?appel=<?php echo $appel ?>&amp;codetheme=<?php echo $codetheme ?>&amp;codelangue=<?php echo $codelangue ?>&amp;codesujet=<?php echo $row_rs_sujet['codesujet'] ?>">
        <?php echo $row_rs_sujet['titresujet'] ?></a>
         <?php
					if($row_rs_sujet['statut']=='pourvu')
					{ $demander_autorisation=false;
						if($row_rs_sujet['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$row_rs_sujet['codeindividu']]['numsejour']['srhue']))
						{ $tab_demander_autorisation=demander_autorisation($row_rs_sujet,$tab_dates_sejour[$row_rs_sujet['codeindividu']]);
							$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
						}
				 		if(!$demander_autorisation)
						{ if($row_rs_sujet['datedeb_sejour']<=date('Y/m/d'))
            	{?> &nbsp;(<a href="../publicdetailindividu.php?appel=<?php echo $appel ?>&amp;codetheme=<?php echo $codetheme ?>&amp;codelangue=<?php echo $codelangue ?>&amp;codeindividu=<?php echo $row_rs_sujet['codeindividu'] ?>"><?php echo str_replace(' ','&nbsp;',$row_rs_sujet['prenom'].' '.$row_rs_sujet['nom']) ?></a>)
         			<?php
							}
							/* else
							{?>  &nbsp;(<?php echo str_replace(' ','&nbsp;',$row_rs_sujet['prenom'].' '.$row_rs_sujet['nom']) ?>)
            <?php
							} */
						}
         }?>
      </td>
    </tr>
    <?php 
    }?>
  </table>
<?php
}?>
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