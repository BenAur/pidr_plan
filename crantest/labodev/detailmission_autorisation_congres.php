<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
//if($admin_bd)
{ /*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}  */
}
$aujourdhui=date('Ymd');
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations du mission (un enreg. vide dans mission pour "creer")
$query_mission =	"SELECT mission.*".
									" FROM mission".
									" WHERE codemission=".GetSQLValueString($codemission,"text");
$rs_mission=mysql_query($query_mission) or die(mysql_error());
$row_rs_mission=mysql_fetch_assoc($rs_mission);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Impression mission</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<style>
.noirtimes9
{
	font-family: "Times New Roman", Times, serif;
	font-size: 9pt;
}
.noirtimes10
{
	font-family: "Times New Roman", Times, serif;
	font-size: 10pt;
}
.noirgrastimes10
{
	font-family: "Times New Roman", Times, serif;
	font-size: 9.5pt;
	font-weight: bold;
}
.noirgrastimes11
{
	font-family: "Times New Roman", Times, serif;
	font-size: 10pt;
	font-weight: bold;
}
.noirtimes12
{
	font-family: "Times New Roman", Times, serif;
	font-size: 12pt;
}
.noirgrastimes12
{
	font-family: "Times New Roman", Times, serif;
	font-size: 11.5pt;
	font-weight: bold;
}
.noirgrastimes13
{
	font-family: "Times New Roman", Times, serif;
	font-size: 13pt;
	font-weight: bold;
}
.noirgrastimes16
{
	font-family: "Times New Roman", Times, serif;
	font-size: 16pt;
	font-weight: bold;
}
.rougetimes10
{
	font-family: "Times New Roman", Times, serif;
	font-size: 10pt;
	color:#FF0000
}
.rougegrastimes12
{
	font-family: "Times New Roman", Times, serif;
	font-size: 12pt;
	color:#FF0000;
	font-weight: bold;
}

.table_bord_bleu_fond_jaune {
	border: 1pt solid #D5D5D5;
	border-color: #00FFFF;
	background-color: #FF0;
}
.table_bord_bleu_fond_bleu {
	border :  1pt solid #00FFFF;
	border-color: #00FFFF;
	background-color:#CFF;
	}
.table_bord_rouge_fond_rose {
	border: 1pt solid #D5D5D5;
	border-color: #FF0000;
	background-color:#FCF;
}


.table_cadre_bleu {
	border: 2pt solid  #00FFFF;
	border-color: #00FFFF;
}

</style>
</head>

<body class="noirgrastimes11"><!-- onLoad="javascript:window.print()" -->
<table width="100%" align="center">
  <tr>
    <td align="left"><img src="images/logo_ul_rvb_200x88.jpg"></td>
  </tr>
  <tr>
    <td class="noirgrastimes13" align="right"><i>
    <?php if($row_rs_mission['avecautorisationcongres']=='mono')
    {?> Imprim&eacute; 1 = enseignant de droit commun<br>
    Autres que les enseignants universitaires &agrave; double fonction CHR &amp; U
    <?php 
		}
		else if($row_rs_mission['avecautorisationcongres']=='bi')
    {?> Imprim&eacute; 2 = enseignant bi-appartenant secteur sant&eacute;<br>
    Enseignants universitaires &agrave; double fonction CHR &amp; U 
    <?php 
		}?>
    </i></td>
  </tr>
  <tr>
    <td align="center" nowrap><span class="noirgrastimes16">AUTORISATION D'ABSENCE en France ou &agrave; l&rsquo;&eacute;tranger</span><br>
    <span class="noirgrastimes13">pour manifestation (colloque, s&eacute;minaire, congr&egrave;s) li&eacute;e &agrave; l'activit&eacute; scientifique ou universitaire</span><br>
    <span class="noirtimes12"><i>
    <?php 
		if($row_rs_mission['avecautorisationcongres']=='mono')
    {?> D&eacute;cret n&deg;69-497 du 30/05/1969 - Instruction DGFIP n&deg; 09-013-M9 du 22/06/2009
    <?php 
		}
		else if($row_rs_mission['avecautorisationcongres']=='bi')
    {?> D&eacute;cret n&deg;84-135 du 24/02/1984 - Arr&ecirc;t&eacute; du 03/07/2006 - Instruction DGFIP n&deg; 09-013-M9 du 22/06/2009
    <?php 
		}?> </i></span></td>
  </tr>
  <tr>
    <td><table border="0" class="table_cadre_bleu" width="100%">
        <tr>
          <td colspan="2" bgcolor="#CCCCCC"><span class="noirgrastimes13">- I - Cadre &agrave; remplir par l&rsquo;agent</span><img src="images/espaceur.gif" width="1" height="25"></td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%">
              <tr>
                <td><table border="0">
                    <tr>
                      <td><table class="table_bord_bleu_fond_jaune">
                        <tr>
                          <td><img src="images/croix.png" alt="" width="10" height="10"></td>
                        </tr>
                      </table></td>
                      <td><span class="noirtimes10">Activit&eacute; RECHERCHE</span></td>
                    </tr>
                </table></td>
                <td><table border="0">
                    <tr>
                      <td><table class="table_bord_bleu_fond_jaune">
                        <tr>
                          <td><img src="images/espaceur.gif" alt="" width="10" height="10"></td>
                        </tr>
                      </table></td>
                      <td><span class="noirtimes10">Activit&eacute; ENSEIGNEMENT</span></td>
                    </tr>
                </table></td>
                <td><table border="0">
                    <tr>
                      <td><table class="table_bord_bleu_fond_jaune">
                        <tr>
                          <td><img src="images/espaceur.gif" alt="" width="10" height="10"></td>
                        </tr>
                      </table></td>
                      <td><span class="noirtimes10">Autre activit&eacute;</span></td>
                    </tr>
                </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td class="noirgrastimes11">Mme, Mlle, M.</td>
          <td class="noirgrastimes11"><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td class="noirgrastimes11"><?php echo $row_rs_mission['nom'].'&nbsp;'.$row_rs_mission['prenom'].str_repeat('&nbsp;',max(0,80-strlen($row_rs_mission['nom'].' '.$row_rs_mission['prenom']))); ?></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td class="noirgrastimes11">Composante d&rsquo;affectation</td>
          <td><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td class="noirgrastimes11"><?php echo $row_rs_mission['composanteenseignement'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['composanteenseignement']))); ?></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%" border="0">
              <tr>
                  <td class="noirgrastimes11">Grade</td>
                  <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['gradecongres'] ?></td>
                    </tr>
                </table></td>
                  <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Emploi</td>
                  <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['emploicongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['emploicongres']))) ?></td>
                    </tr>
                  </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2">
            <table width="100%" border="0">
              <tr>
                <td class="noirgrastimes11" width="22%" nowrap>Sollicite une autorisation d'absence pour<br>
                                          se rendre au congr&egrave;s, s&eacute;minaire,<br>
                                          colloque,&hellip; <span class="noirtimes9"><i>(intitul&eacute; de la manifestation)</i></span></td>
                <td width="78%"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11" height="60" align="center"><?php echo nl2br($row_rs_mission['intitulecongres']) ?></td>
                    </tr>
                </table></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%" border="0">
              <tr>
                <td class="noirgrastimes11" width="48" nowrap>Devant se d&eacute;rouler du</td>
                <td width="208"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo aaaammjj2jjmmaaaa($row_rs_mission['datedeb_congres'],'/'); ?></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11" width="53">&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;au</td>
                <td width="217"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo aaaammjj2jjmmaaaa($row_rs_mission['datefin_congres'],'/'); ?></td>
                    </tr>
                </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%" border="0">
              <tr>
                  <td><span class="noirgrastimes11">&agrave;</span><span class="noirtimes10"><i> (ville)</i></span></td>
                  <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['villecongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['villecongres']))); ?></td>
                    </tr>
                  </table></td>
                  <td><span class="rougegrastimes12">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pays</span><span class="rougetimes10"><i> (si &agrave; l'&eacute;tranger)</i></span></td>
                  <td><table width="100%" class="table_bord_rouge_fond_rose">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['payscongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['payscongres']))); ?></td>
                    </tr>
                  </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td><span class="noirgrastimes11">Organis&eacute; par </span><span class="noirgrastimes10"><i> (d&eacute;signation de l&rsquo;organisme<br>organisateur de la manifestation)</i></span></td>
          <td><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td class="noirgrastimes11"><?php echo $row_rs_mission['organisateurcongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['organisateurcongres']))); ?></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%">
              <tr>
                <td nowrap><span class="noirgrastimes11"><span class="noirgrastimes11">D&eacute;part</span><span class="noirgrastimes10"><i> (date)</i></span></td>
                <td><table class="table_bord_bleu_fond_bleu"  border="1">
                    <tr>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],8,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],9,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],5,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],6,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],2,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datedepartcongres'],3,1); ?>&nbsp;&nbsp;</td>
                    </tr>
                </table></td>
                <td><span class="noirgrastimes10"><i>(heure)</i></span></td>
                <td><table class="table_bord_bleu_fond_bleu" border="1">
                    <tr>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['heuredepartcongres'],0,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['heuredepartcongres'],1,1); ?>&nbsp;&nbsp;</td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11">Retour</td>
                <td><table class="table_bord_bleu_fond_bleu" border="1">
                    <tr>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],8,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],9,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],5,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],6,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],2,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['datearriveecongres'],3,1); ?>&nbsp;&nbsp;</td>
                    </tr>
                </table></td>
                <td><span class="noirtimes10"><i>(heure)</i></span></td>
                <td><table border="1" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['heurearriveecongres'],0,1); ?>&nbsp;&nbsp;</td>
                      <td class="noirgrastimes11">&nbsp;&nbsp;<?php echo substr($row_rs_mission['heurearriveecongres'],1,1); ?>&nbsp;&nbsp;</td>
                    </tr>
                </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2"><table border="0">
              <tr>
                <td>
                  <table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avectraincongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;train&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecavioncongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;avion&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['hotelcongres']!=''){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11">H&ocirc;tel <span class="noirtimes10"><i>(nom)</i></span></td>
                <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['hotelcongres'].str_repeat('&nbsp;',max(0,100-strlen($row_rs_mission['hotelcongres']))); ?></td>
                    </tr>
                </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2"><table width="100%" border="0">
              <tr>
                <td class="noirgrastimes11" nowrap>Fait &agrave;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td><?php echo str_repeat('&nbsp;',60); ?></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;le&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td><?php echo str_repeat('&nbsp;',30); ?></td>
                    </tr>
                </table></td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;Signature de l&rsquo;agent</td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
    </table></td>
  </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><table width="100%" class="table_cadre_bleu">
            <tr>
              <td bgcolor="#CCCCCC"><span class="noirgrastimes13">- II Modalit&eacute;s financi&egrave;res du d&eacute;placement arr&ecirc;t&eacute;es par l&rsquo;ordonnateur<img src="images/espaceur.gif" width="1" height="25"></span></td>
            </tr>
            <tr>
              <td align="center" class="noirgrastimes11">Adresse budg&eacute;taire</td>
            </tr>
            <tr>
              <td><table width="100%">
                <tr>
                  <td class="noirgrastimes11">C.C. </td>
                  <td class="noirgrastimes11"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['centrecoutcongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['centrecoutcongres']))) ?></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;EOTP </td>
                  <td class="noirgrastimes11"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['eotpcongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['eotpcongres']))) ?></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes11">&nbsp;&nbsp;Dest. </td>
                  <td class="noirgrastimes11"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['destinationcongres'].str_repeat('&nbsp;',max(0,6-strlen($row_rs_mission['destinationcongres']))) ?></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes11">&nbsp;&nbsp;Rub. </td>
                  <td class="noirgrastimes11"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes11"><?php echo $row_rs_mission['rubriquecongres'].str_repeat('&nbsp;',max(0,6-strlen($row_rs_mission['rubriquecongres']))) ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table>
                <tr>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['sanspriseenchargecongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes10">Aucune prise en charge autoris&eacute;e des frais inh&eacute;rents au d&eacute;placement </td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table>
                <tr>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecpriseenchargetotalecongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" alt="" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes10">Prise en charge de la facture de l&rsquo;organisateur du colloque, couvrant tous les d&eacute;bours pas de remboursement &agrave; l&rsquo;agent </td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table>
                <tr>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecpriseenchargepartiellecongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" alt="" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes10">Prise en charge de la facture de l&rsquo;organisateur du colloque ne couvrant pas tous les d&eacute;bours - commande partielle<br>
                    autoris&eacute;e sur portail march&eacute;, &agrave; d&eacute;faut remboursement &agrave; l&rsquo;agent selon bar&egrave;me r&eacute;glementaire des frais suivants : </td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table>
                <tr class="noirgrastimes10">
                  <td class="noirgrastimes10"><img src="images/espaceur.gif" width="30" height="1"></td>
                  <td class="noirgrastimes10">Transport </td>
                  <td class="noirtimes10"><i class="noirtimes9">&nbsp;&nbsp;SNCF&nbsp;&nbsp;</i></td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avectraincongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td>&nbsp;&nbsp;<i><span class="noirtimes9">avion</span></i><span class="noirtimes9"></span>&nbsp;&nbsp; </td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecavioncongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td>&nbsp;&nbsp;<i><span class="noirtimes9">V&eacute;hicule pers&nbsp;</span></i><span class="noirtimes9"></span>&nbsp; </td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecvehiculeperscongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td>&nbsp;<span class="noirgrastimes10">&nbsp;<i>H&eacute;bergement</i>&nbsp;</span>&nbsp; </td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avechebergementcongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td>&nbsp;<i>&nbsp;<span class="noirgrastimes10">Repas</span>&nbsp;</i>&nbsp; </td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecrepascongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td>&nbsp;&nbsp;<i><span class="noirgrastimes10">Autre</span></i><span class="noirtimes9"></span>&nbsp;&nbsp; </td>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecautrecongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" width="10" height="10"></td>
                    </tr>
                  </table></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><table>
                <tr>
                  <td><table class="table_bord_bleu_fond_jaune">
                    <tr>
                      <td><img src="images/<?php if($row_rs_mission['avecpriseenchargeautre_ulcongres']=='oui'){?>croix.png<?php } else{?>espaceur.gif<?php }?>" alt="" width="10" height="10"></td>
                    </tr>
                  </table></td>
                  <td class="noirgrastimes10">Prise en charge par un organisme autre que l&rsquo;UL<i><span class="noirtimes9"> (nom) </span></i></td>
                  <td class="noirgrastimes10"><table width="100%" class="table_bord_bleu_fond_bleu">
                    <tr>
                      <td class="noirgrastimes10"><?php echo $row_rs_mission['organismepriseenchargeautrecongres'].str_repeat('&nbsp;',max(0,50-strlen($row_rs_mission['organismepriseenchargeautrecongres']))) ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table></td>
            </tr>
          </table></td>
        </tr>
        </table>
 <p style="page-break-before:always;">
</p>        
<table width="100%" border="0" align="center">
  <tr>
    <td><table width="100%" class="table_cadre_bleu">
      <tr>
        <td align="right" class="noirgrastimes11"><table width="100%" border="0">
          <tr>
            <td>&nbsp;</td>
            <td><table border="0" align="right">
              <tr>
                <td><span class="noirgrastimes11">Fait &agrave;</span><span class="noirgrastimes12">&nbsp;&nbsp;&nbsp;</span>&nbsp;</td>
                <td><table width="100%" class="table_bord_bleu_fond_bleu">
                  <tr>
                    <td><?php echo str_repeat('&nbsp;',60); ?></td>
                  </tr>
                </table></td>
                <td><span class="noirgrastimes11">&nbsp;&nbsp;&nbsp;le&nbsp;&nbsp;</span><span class="noirgrastimes12">&nbsp;</span></td>
                <td><table width="100%" class="table_bord_bleu_fond_bleu">
                  <tr>
                    <td><?php echo str_repeat('&nbsp;',30); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="4" align="center"><span class="noirtimes10">L'Ordonnateur</span><br>
                  <span class="noirtimes10">Directeur UFR si d&eacute;pense sur budget p&eacute;dagogie<br>
                    Directeur laboratoire si d&eacute;pense sur budget recherche</span><br>
                  <span class="noirtimes10">(Nom et signature)</span></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><table width="100%" class="table_cadre_bleu">
      <tr>
        <td align="left" bgcolor="#CCCCCC" class="noirgrastimes13">- III - Contr&ocirc;le limite annuelle de 6 semaines au regard de l'obligation d'enseignement<img src="images/espaceur.gif" alt="" width="1" height="25"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="noirgrastimes11">Le directeur de la composante d'affectation p&eacute;dagogique de l'agent,  habilit&eacute; sur le plan administratif &agrave;<br>
          accorder l'autorisation d'absence, certifie que les conditions sont remplies au regard de la r&eacute;glementation</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td width="50%">&nbsp;</td>
            <td align="center" class="noirgrastimes11">Le directeur </td>
            </tr>
          <tr>
            <td>&nbsp;</td>
            <td align="center" class="noirgrastimes11">de la composante p&eacute;dagogique d'affectation de l'agent</td>
            </tr>
          <tr>
            <td>&nbsp;</td>
            <td align="center" class="noirtimes10"><i>(Nom et signature)</i></td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td align="center"><img src="images/imgtext_decompteannuelconges.png" alt="" width="665" height="22"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td width="46%" nowrap class="noirgrastimes11">R&eacute;capitulatif des absences autoris&eacute;es au titre de l'ann&eacute;e civile</td>
            <td width="23%"><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td><?php echo str_repeat('&nbsp;',20); ?></td>
              </tr>
            </table></td>
            <td width="1%" align="center">/</td>
            <td width="30%"><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td><?php echo str_repeat('&nbsp;',20); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td nowrap class="noirgrastimes11">Absences pr&eacute;c&eacute;demment autoris&eacute;es sur l'ann&eacute;e civile</td>
            <td><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td><?php echo str_repeat('&nbsp;',20); ?></td>
              </tr>
            </table></td>
            <td align="center">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="noirgrastimes11">Autorisation sollicit&eacute;e au titre de la pr&eacute;sente demande :</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td width="6%" class="noirgrastimes11">du</td>
            <td width="21%"><table class="table_bord_bleu_fond_bleu"  border="1">
              <tr>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
              </table></td>
            <td width="13%" align="center" class="noirgrastimes11">au</td>
            <td width="60%"><table class="table_bord_bleu_fond_bleu"  border="1">
              <tr>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class="noirgrastimes11">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                </tr>
              </table></td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td width="7%" class="noirgrastimes11">Soit</td>
            <td width="19%"><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td><?php echo str_repeat('&nbsp;',30); ?></td>
                </tr>
              </table></td>
            <td width="14%" align="center" class="noirgrastimes11">jours</td>
            <td width="22%" align="center" class="noirgrastimes11">Nouveau cumul</td>
            <td width="18%"><table width="100%" class="table_bord_bleu_fond_bleu">
              <tr>
                <td><?php echo str_repeat('&nbsp;',30); ?></td>
                </tr>
              </table></td>
            <td width="20%" align="center" class="noirgrastimes11">jours</td>
            </tr>
          <tr>
            <td class="noirgrastimes11">&nbsp;</td>
            <td>&nbsp;</td>
            <td align="center" class="noirgrastimes11">&nbsp;</td>
            <td align="center" class="noirgrastimes11">&nbsp;</td>
            <td align="center" class="noirtimes12">Visa D.R.H. Universit&eacute;</td>
            <td class="noirgrastimes11">&nbsp;</td>
            </tr>
          </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <?php if($row_rs_mission['avecautorisationcongres']=='bi')
  {?><tr>
    <td><table width="100%" class="table_cadre_bleu">
      <tr>
        <td align="right" class="noirgrastimes11">&nbsp;</td>
      </tr>
      <tr>
        <td align="center" class="noirgrastimes12">Les autorit&eacute;s administratives co-habilit&eacute;es</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><table width="100%" border="0">
          <tr>
            <td align="center" class="noirgrastimes12">Le Directeur du CHR ou son d&eacute;l&eacute;gu&eacute;</td>
            <td align="center" class="noirgrastimes12">Le Doyen de la composante sant&eacute; ou son d&eacute;l&eacute;gu&eacute;</td>
          </tr>
          <tr>
            <td align="center" class="noirtimes10"><i>(Nom et signature)</i></td>
            <td align="center"><i class="noirtimes10">(Nom et signature)</i></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
    </table></td>
  </tr>
  <?php 
	}?>
  <tr>
  	<td><table border="0">
  	  <tr>
  	    <td><table class="table_bord_bleu_fond_jaune">
  	      <tr>
  	        <td><img src="images/croix.png" alt="" width="10" height="10"></td>
	        </tr>
	      </table></td>
  	    <td><span class="bleucalibri10"><i>Cocher la ou les cases concern&eacute;e(s)</i></span></td>
	    </tr>
	  </table></td>
  </tr>
</table>
</body>
</html>
    <?php

if(isset($rs_mission))mysql_free_result($rs_mission);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
