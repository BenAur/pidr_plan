<?php require_once('_const_fonc.php');// ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
//if($admin_bd)
{ /*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}  */
}
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");

// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
$query_rs_individu =	"SELECT codeciv,nom,prenom,date_naiss,adresse_pers,email, telport, datedeb_sejour, datefin_sejour, etab_orig, if(codediplome_prep='',autrediplome_prep,concat(\"Master \",liblongdiplome_fr)) as libdiplome_prep,".
											" dureestage_mois, dureestage_semaine, montantfinancement".
											" FROM individu,individusejour,diplome ".
											" WHERE individu.codeindividu=individusejour.codeindividu".
											" and individusejour.codediplome_prep=diplome.codediplome".
											" and individu.codeindividu=".GetSQLValueString($codeindividu,"text")." and individusejour.numsejour=".GetSQLValueString($numsejour,"text");
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_individu=mysql_fetch_assoc($rs_individu);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Impression attestation</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<style>
.table_cadre_noir {
	border: 1pt solid #FFF;
	border-color: #000;
	border-collapse: collapse;
	
}


</style>
</head>

<body onLoad="javascript:window.print()">
<table border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
  	<td>
    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		    <tr>
		      <td width="50%" nowrap><?php echo $GLOBALS['table_logo_attestationstage'] ?></td>
		      <td width="52%" align="center" nowrap><table width="100%" border="1" class="table_cadre_noir">
		        <tr>
		          <td width="50%" align="center"><span class="noirgrascalibri14">ATTESTATION DE STAGE</span><br>
	              <i class="noirgrascalibri12">&agrave; remettre au stagiaire &agrave; l'issue du stage</i></td>
		          </tr>
	        </table></td>
	      </tr>
    	</table>
    </td>
	</tr>
  <tr>
    <td><span class="noirgrascalibri12"><img src="images/espaceur.gif" alt="" width="1" height="8"></span>
    </td>
  </tr>
  <tr>
    <td></td>
  </tr>
  <tr>
  	<td><table width="100%" border="1" class="table_cadre_noir">
  	  <tr>
  	    <td class="noircalibri11"><table width="100%" border="0">
  	      <tr>
  	        <td><span class="noirgrascalibri12"><u>ORGANISME D'ACCUEIL</u></span></td>
	        </tr>
  	      <tr>
  	        <td class="noircalibri11">Nom ou D&eacute;nomination sociale : <?php echo $GLOBALS['denominationlabo_attestationstage'] ?></td>
	        </tr>
  	      <tr>
  	        <td class="noircalibri11">Adresse : <?php echo $GLOBALS['adresselabo'] ?></td>
	        </tr>
  	      <tr>
  	        <td><img src="images/telephone_attestation.png" alt="" width="13" height="13"></td>
	        </tr>
	      </table></td>
	    </tr>
	  </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="noirgrascalibri12">Certifie que</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%" border="1" class="table_cadre_noir">
      <tr>
        <td nowrap><table width="100%" border="0">
            <tr>
              <td><u class="noirgrascalibri12">LE STAGIAIRE</u></td>
            </tr>
            <tr>
              <td><span class="noirgrascalibri12"><img src="images/espaceur.gif" alt="" width="1" height="8"></span></td>
            </tr>
            <tr>
              <td><table width="100%" border="0">
                <tr>
                  <td nowrap class="noircalibri11">Nom : <?php echo $row_rs_individu['nom']==''?str_repeat('.',30):$row_rs_individu['nom']; ?></span></td>
                  <td nowrap class="noircalibri11">Pr&eacute;nom : <span class="noircalibri11"><?php echo $row_rs_individu['prenom']==''?str_repeat('.',20):$row_rs_individu['prenom']; ?></span></td>
                  <td nowrap class="noircalibri11">Sexe :&nbsp;M <img src="images/b_checked_attestationstage_<?php echo $row_rs_individu['codeciv']=='1'?'oui':'non' ?>.png" alt="" width="10" height="10"> F <img src="images/b_checked_attestationstage_<?php echo ($row_rs_individu['codeciv']=='2' || $row_rs_individu['codeciv']=='3')?'oui':'non' ?>.png" alt="" width="10" height="10"></td>
                  <td nowrap class="noircalibri11">N&eacute;(e) le : <span class="noircalibri11"><?php echo $row_rs_individu['date_naiss']==''?str_repeat('.',10):aaaammjj2jjmmaaaa($row_rs_individu['date_naiss'],'/') ?></span></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td><table width="100%" border="0">
                <tr>
                  <td nowrap class="noircalibri11">Adresse : <?php echo $row_rs_individu['adresse_pers']; ?></td>
                  </tr>
              </table></td>
            </tr>
            <tr>
              <td><table border="0">
                <tr>
                  <td nowrap class="noircalibri11"><img src="images/telephone_attestation.png" alt="" width="13" height="13">
                  <?php echo $row_rs_individu['telport']==''?str_repeat('.',13):$row_rs_individu['telport']; ?></td>
                  <td nowrap class="noircalibri11">m&eacute;l : <?php echo $row_rs_individu['email']; ?></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td nowrap class="noircalibri11"><span class="noirgrascalibri12">ETUDIANT EN</span> (intitul&eacute; de la formation ou du cursus de l'enseignement sup&eacute;rieur suivi par le ou la stagiaire) :</td>
            </tr>
            <tr>
              <td class="noircalibri11"><?php echo $row_rs_individu['libdiplome_prep']; ?></td>
            </tr>
            <tr>
              <td nowrap class="noircalibri11"><span class="noirgrascalibri12">AU SEIN DE</span> (nom de l'&eacute;tablissement d'enseignement sup&eacute;rieur ou de l'organisme de formation) :</td>
            </tr>
            <tr>
              <td><?php echo $row_rs_individu['etab_orig']; ?></td>
            </tr>
          </table></td>
      </tr>
    </table></td>
  </tr>
  <tr>
   <td>
   </td>
   </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class="noirgrascalibri12">a effectu&eacute; un stage pr&eacute;vu dans le cadre de ses &eacute;tudes</td>
  </tr>
  <tr>
    <td class="noirgrascalibri8">&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellpadding="0" cellspacing="3">
      <tr>
        <td><table width="100%" border="1" class="table_cadre_noir">
          <tr>
            <td><table width="100%" border="0">
              <tr>
                <td class="noirgrascalibri12"><u>DUREE DU STAGE</u></td>
              </tr>
              <tr>
                <td class="noirgrascalibri12"><img src="images/espaceur.gif" alt="" width="1" height="8"></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="8%"><img src="images/espaceur.gif" alt="" width="80" height="1"></td>
                    <td width="92%" nowrap>&nbsp;<span class="noirgrascalibri11">Du</span> <span class="noircalibri11"><?php echo aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],'/') ?></span> <span class="noirgrascalibri11">Au</span> <span class="noircalibri11"><?php echo aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],'/') ?></span></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td nowrap class="noircalibri11">&nbsp;Repr&eacute;sentant une <b>dur&eacute;e totale</b> de&nbsp;
										<?php 
										 $tab_amj=duree_aaaammjj($row_rs_individu['datedeb_sejour'], $row_rs_individu['datefin_sejour']);
										 if($row_rs_individu['dureestage_mois']!='')
										 { echo $row_rs_individu['dureestage_mois'].' Mois';
										 }
										 if($row_rs_individu['dureestage_semaine']!='')
										 { echo ($row_rs_individu['dureestage_mois']!=''?'/':'&nbsp;').$row_rs_individu['dureestage_semaine'].' Semaine'.($row_rs_individu['dureestage_semaine']>1?'s':'');
										 }
										?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="justify"><span class="noircalibri9">La dur&eacute;e totale du stage est appr&eacute;ci&eacute;e en tenant compte de la pr&eacute;sence effective du stagiaire dans l'organisme, sous r&eacute;serve des droits &agrave; cong&eacute;s et 
                  autorisations d'absence pr&eacute;vus &agrave; l'article L.124-13 du code de l'&eacute;ducation (art. L.1 24-18 du code de l'&eacute;ducation). Chaque p&eacute;riode au moins &eacute;gale &agrave; 7 heures 
                  de pr&eacute;sence cons&eacute;cutives ou non est consid&eacute;r&eacute;e comme &eacute;quivalente &agrave; un jour de stage et chaque p&eacute;riode au moins &eacute;gale &agrave; 22 jours de pr&eacute;sence cons&eacute;cutifs 
                  ou non est consid&eacute;r&eacute;e comme &eacute;quivalente &agrave; un mois.</span></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0">
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="noirgrascalibri12"><u>MONTANT DE LA GRATIFICATION VERSEE AU STAGIAIRE</u></td>
              </tr>
              <tr>
                <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td width="8%"><img src="images/espaceur.gif" alt="" width="80" height="1"></td>
                    <td width="92%" nowrap class="noircalibri11">Le stagiaire a per&ccedil;u une gratification de stage pour un <b>montant total</b> de
                     <?php echo $row_rs_individu['montantfinancement']  ?> &euro;<span class="noircalibri11"></span></td>
                  </tr>
                  </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td width="45%" align="justify" class="noircalibri9"><i><b>L'attestation de stage</b> est indispensable pour pouvoir, sous r&eacute;serve 
              du versement d'une cotisation, faire prendre en compte le stage dans
              les droits &agrave; retraite. La l&eacute;gislation sur les retraites (loi n&deg; 2014-40 du 20
              janvier 2014) ouvre aux &eacute;tudiants <b>dont le stage a &eacute;t&eacute; gratifi&eacute;</b> la
              possibilit&eacute; de faire valider celui-ci dans la <b>limite de deux trimestres</b>,
              sous r&eacute;serve du <b>versement d'une cotisation</b>. La <b>demande est &agrave; faire par l'&eacute;tudiant dans les deux ann&eacute;es</b> suivant la fin du stage et
              sur <b>pr&eacute;sentation obligatoire de l'attestation de stage</b> mentionnant
              la dur&eacute;e totale du stage et le montant total de la gratification per&ccedil;ue.
              Les informations pr&eacute;cises sur la cotisation &agrave; verser et sur la proc&eacute;dure
              &agrave; suivre sont &agrave; demander aupr&egrave;s de la s&eacute;curit&eacute; sociale (code de la
            s&eacute;curit&eacute; sociale art. L.351-17 - code de l'&eacute;ducation art . D.124&middot;9).</i></td>
            <td width="7%"><img src="images/espaceur.gif" alt="" width="70" height="1"></td>
            <td width="48%" valign="top" nowrap class="noircalibri11"><span class="noirgrascalibri11"><br>
              <br>
              FAIT A</span> Vandoeuvre-l&egrave;s-Nancy <span class="noirgrascalibri11">LE</span> <?php echo aaaammjj2jjmmaaaa(date('Y/m/d'),'/') ?><br>
              <br>
              <span class="noircalibri9">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nom, fonction et signature du repr&eacute;sentant de l'organisme d'accueil</span></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
</body>
</html>
    <?php

if(isset($rs_individu))mysql_free_result($rs_individu);
?>
