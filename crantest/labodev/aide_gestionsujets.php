<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Aide/Explications sujets</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
</head>

<body>
<table width="100%" border="0">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Aide gestion sujets','lienretour'=>'gestionsujets.php','texteretour'=>'Retour &agrave; la gestion des sujets',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array())) ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><span class="noircalibri10">Le tableau ci-dessous r&eacute;capitule les divers statuts d'une proposition 
  de sujet dans le temps, les actions possibles et les affichages selon les r&ocirc;les, &agrave; savoir, Membre permanent </span><span class="bleugrascalibri10">(MP)</span><span class="noircalibri10">  d&eacute;posant et Responsable(s) 
  de <?php echo $GLOBALS['liblong_theme_fr'] ?> </span><span class="bleugrascalibri10">(<?php echo $GLOBALS['libcourt_theme_fr'] ?>)</span><span class="noircalibri10"> d'affectation du sujet</span><span class="noircalibri10">&nbsp;:</span></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center"><table width="80%" border="0">
        <tr>
          <td width="798"><table border="0" align="left">
            <tr class="head">
              <td align="center" valign="bottom" nowrap class="noirgrascalibri10"><div align="right"><br>
                R&ocirc;le</div></td>
              <td bgcolor="#FFFFFF"><img src="images/espaceur.gif" alt="" width="1" height="1">
              </td>
              <td colspan="3" align="center" nowrap class="noircalibri10"><span class="bleugrascalibri10"><strong>(MP)<br>
                Affichage pour</strong></span><strong><br>
                <span class="mauvegrascalibri10">Membre permanent</span></strong></td>
              <td bgcolor="#FFFFFF"><img src="images/espaceur.gif" width="1" height="1">
              </td>
              <td colspan="3" align="center" class="noirgrascalibri10"><span class="bleugrascalibri10"><strong>(<?php echo $GLOBALS['libcourt_theme_fr'] ?>)<br>
                Affichage</strong> pour</span><br>
                <span class="mauvegrascalibri10">Responsable <?php echo $GLOBALS['libcourt_theme_fr'] ?> d&rsquo;affectation</span></td>
              </tr>
            <tr class="odd">
              <td width="102" nowrap><span class="noirgrascalibri10">Statut</span></td>
              <td align="center" nowrap bgcolor="#FFFFFF" class="bleugrascalibri10">&nbsp;</td>
              <td width="64" align="center" nowrap class="bleugrascalibri10">Actions</td>
              <td width="59" align="center" class="bleugrascalibri10">Visa D&eacute;posant</td>
              <td width="59" align="center" class="bleugrascalibri10">Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?></td>
              <td align="center" bgcolor="#FFFFFF" class="bleugrascalibri10">&nbsp;</td>
              <td width="86" align="center" class="bleugrascalibri10">Actions</td>
              <td width="86" align="center" class="bleugrascalibri10">Visa D&eacute;posant</td>
              <td width="86" align="center" class="bleugrascalibri10">Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?></td>
              </tr>
            <tr class="even">
              <td nowrap class="mauvegrascalibri10">Modifiable</td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/b_edit.png" alt="" width="16" height="16"></td>
                  <td><span class="noircalibri10"><img src="images/b_drop.png" alt="" width="16" height="16"></span></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_valider.png" alt="" width="48" height="15"></span></td>
              <td align="center" nowrap><img src="images/b_sablier.png" alt="" width="9" height="16"></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/b_edit.png" alt="" width="16" height="16"></td>
                  <td><span class="noircalibri10"><img src="images/b_drop.png" alt="" width="16" height="16"></span></td>
                  <td><img src="images/espaceur.gif" alt="" width="65" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_valider.png" alt="" width="48" height="15"></span></td>
              <td align="center" nowrap><img src="images/b_sablier.png" alt="" width="9" height="16"></td>
              </tr>
            <tr class="odd">
              <td nowrap class="mauvegrascalibri10">En cours de validation</span></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_brancher.png" alt="" width="14" height="16"></span></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/b_edit.png" alt="" width="16" height="16"></td>
                  <td><span class="noircalibri10"><img src="images/b_drop.png" alt="" width="16" height="16"></span></td>
                  <td><img src="images/espaceur.gif" alt="" width="65" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_valider.png" alt="" width="48" height="15"></span></td>
              </tr>
            <tr class="even">
              <td nowrap class="mauvegrascalibri10">En cours de validation + affect&eacute;</td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_brancher.png" alt="" width="14" height="16"></span></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/b_edit.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="65" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_valider.png" alt="" width="48" height="15"></span></td>
            </tr>
            <tr class="even">
              <td nowrap class="mauvegrascalibri10">Valid&eacute;</td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><span class="noircalibri10"><img src="images/b_drop.png" alt="" width="16" height="16"></span></td>
                  <td><img src="images/b_afficher_sujet_web_oui.png" width="65" height="15"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              </tr>
            <tr class="even">
              <td nowrap class="mauvegrascalibri10">Valid&eacute; + affect&eacute; = Pourvu<sup>(1)</sup></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap bgcolor="#FFFFFF">&nbsp;</td>
              <td align="center" nowrap><table border="0">
                <tr>
                  <td><img src="images/b_oeil.png" alt="" width="16" height="16"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/espaceur.gif" alt="" width="16" height="1"></td>
                  <td><img src="images/b_afficher_sujet_web_oui.png" alt="" width="65" height="15"></td>
                </tr>
              </table></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              <td align="center" nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt="" width="16" height="16"></span></td>
              </tr>
            <tr bgcolor="#FFFFFF">
              <td height="25" colspan="9" align="center" nowrap class="mauvegrascalibri10"><table class="table_rectangle_bleu">
                  <tr>
                    <td bgcolor="#FFFFFF" class="noircalibri10" scope="row">Pour les sujets de th&egrave;se, une option permet de laisser un sujet &agrave; l'&eacute;tat propos&eacute;</td>
                    <td bgcolor="#FFFFFF"><img src="images/b_afficher_sujet_propose_non.png" width="71" height="30"></td>
                  </tr>
                  <tr>
                    <td bgcolor="#FFFFFF" class="noircalibri10" scope="row">Si un dossier &eacute;tudiant est en attente d'affectation d'un sujet, il appara&icirc;t dans une liste d&eacute;roulante</td>
                    <td bgcolor="#FFFFFF">&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td><p class="noircalibri10"><img src="images/b_oeil.png" alt="" width="16" height="16"> voir, <img src="images/b_edit.png" alt="" width="16" height="16"> modifier, <img src="images/b_drop.png" alt="" width="16" height="16"> supprimer<br>
              <img src="images/b_valider.png" alt="" width="48" height="15"> apposer le visa : apr&egrave;s validation,  passage au niveau hi&eacute;rarchique sup&eacute;rieur et suppression des possibilit&eacute;s de <img src="images/b_edit.png" alt="" width="16" height="16"> et <img src="images/b_drop.png" alt="" width="16" height="16"><br>
              <br>
              <img src="images/b_visa.png" alt="" width="16" height="16"> visa appos&eacute;, <img src="images/b_brancher.png" alt="" width="14" height="16"> en attente du visa du r&ocirc;le concern&eacute;, <img src="images/b_sablier.png" alt="" width="9" height="16"> en attente de validation du niveau hi&eacute;rarchique inf&eacute;rieur</p>
            <p class="noircalibri10">NB : l'ic&ocirc;ne <img src="images/attention.png" alt="" width="16" height="14"> peut appara&icirc;tre &agrave; c&ocirc;t&eacute; du bouton <img src="images/b_valider.png" alt="" width="48" height="15"> dans la colonne &quot;Visa&quot;
: le sujet a &eacute;t&eacute; affect&eacute; &agrave; un &eacute;tudiant mais le SRH ne peut pas valider son dossier tant que le &quot;Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?>&quot; n'a pas &eacute;t&eacute; appos&eacute; pour ce sujet.</p></td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td class="mauvecalibri10">&nbsp;</td>
  </tr>
  <tr>
    <td><p class="noircalibri10">un <strong>Membre permanent</strong> du laboratoire peut :<br>
      - cr&eacute;er une nouvelle proposition,  saisir et enregistrer. La proposition passe en statut  &quot;<strong>Modifiable</strong>&quot; si le d&eacute;posant n'est pas Responsable de <?php echo $GLOBALS['liblong_theme_fr'] ?> du sujet   : <img src="images/b_valider.png" alt="" width="48" height="15" align="absbottom"> appara&icirc;t dans <span class="bleugrascalibri10">Visa D&eacute;posant</span>, et <img src="images/b_sablier.png" alt="" width="9" height="16"> dans <span class="bleugrascalibri10">Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?></span>;<br>
- modifier (ou supprimer) la proposition qui est en statut &quot;<strong>Modifiable</strong>&quot; : la proposition est enregistrable tant qu'il n'a pas &quot;Enregistrer 
      et demander validation&quot; ou  <img src="images/b_valider.png" alt="" width="48" height="15" align="absbottom"> ;<br>
      - &quot;Enregistrer 
      et demander validation&quot; ou <img src="images/b_valider.png" alt="" width="48" height="15" align="absbottom"> :<br>
      &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;- un message est envoy&eacute; aux Encadrant(s) et  Responsable(s) 
      de <?php echo $GLOBALS['liblong_theme_fr'] ?>(s) concern&eacute;(s). Si le sujet est affect&eacute; &agrave; un &eacute;tudiant, un message est envoy&eacute; au gestionnaire de <?php echo $GLOBALS['liblong_theme_fr'] ?> ;<br>
  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;- la proposition devient inaccessible 
      en modification (ou suppression) à tous sauf au(x) 
      Responsable(s) de <?php echo $GLOBALS['liblong_theme_fr'] ?> concern&eacute;(s) ;<br>
  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;- le statut de la proposition 
  passe en statut &quot;<strong>En cours de validation</strong>&quot; : un <img src="images/b_visa.png" alt="" width="16" height="16" align="absbottom"> est affich&eacute; dans  <span class="bleugrascalibri10">Visa D&eacute;posant</span> et <img src="images/b_brancher.png" alt="" width="14" height="16" align="absbottom"> dans <span class="bleugrascalibri10">Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?></span>.*<br>
  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;- affecte un sujet &agrave; un &eacute;tudiant choisi dans la liste d&eacute;roulante &eacute;ventuellement affich&eacute;e si un &eacute;tudiant(s) est en attente de sujet <br>
  - Affecter un sujet &agrave; un &eacute;tudiant choisi dans la liste d&eacute;roulante &eacute;ventuellement affich&eacute;e si un &eacute;tudiant(s) est en attente de sujet &agrave; l'aide du bouton <img src="images/b_affecter.png" width="60" height="18" align="bottom"> si le bouton <img src="images/b_valider.png" alt="" width="48" height="15" align="bottom"> n'est plus disponible<br>
  - pr&eacute;ciser qu'un sujet de th&egrave;se, bien qu'affect&eacute; &agrave; au moins un candidat, doit rester &agrave; l'&eacute;tat propos&eacute; sur le site public en cochant la case <img src="images/b_afficher_sujet_propose_non.png" alt="" width="71" height="30" align="absmiddle">, puis, plus tard, d&eacute;cocher cette case. Tant que le sujet porte la mention &quot;Toujours propos&eacute;&quot;, le(s) candidat(s) n'est pas affich&eacute; sur le site web du laboratoire..<br>
    * : si le Membre Permanent est Responsable   <?php echo $GLOBALS['liblong_theme_fr'] ?>, 
      la validation fait passer le sujet au statut &quot;<strong>Valid&eacute;</strong>&quot;.</p>
      <p class="noircalibri10">Le <strong>Responsable  <?php echo $GLOBALS['liblong_theme_fr'] ?></strong> peut :<br>
        - modifier (ou supprimer) la proposition qui est &quot;<strong>En cours de validation</strong>&quot;.<br>
        - <img src="images/b_valider.png" alt="" width="48" height="15" align="absbottom"> la  
        proposition :<br>
        &nbsp; &nbsp; &nbsp; &nbsp; - un message de validation est envoy&eacute; au(x) encadrant(s) (au(x) autres responsable(s) de <?php echo $GLOBALS['libcourt_theme_fr'] ?> concern&eacute;(s) le cas &eacute;ch&eacute;ant). Si le sujet est affect&eacute; &agrave; un &eacute;tudiant, ce message est aussi envoy&eacute; au gestionnaire de <?php echo $GLOBALS['liblong_theme_fr'] ?> ou au SRH ;<br>
        &nbsp; &nbsp; &nbsp; &nbsp; - la proposition appara&icirc;t en zone pubique<sup>(2)</sup> en &quot;<strong>sujet propos&eacute;</strong>&quot; (&agrave; moins qu'il d&eacute;cide de ne pas la faire appara&icirc;tre : <img src="images/b_afficher_sujet_web_non.png" alt="" width="65" height="15" align="absbottom">) ;<br>
        &nbsp; &nbsp; &nbsp; &nbsp; - la proposition passe au statut &quot;<strong>Valid&eacute;</strong>&quot; :  un <img src="images/b_visa.png" alt="" width="16" height="16" align="absbottom"> est affich&eacute; dans<span class="bleugrascalibri10"> Visa <?php echo $GLOBALS['libcourt_theme_fr'] ?></span> ;<br>
        &nbsp; &nbsp; &nbsp; &nbsp; - il peut encore supprimer la proposition apr&egrave;s validation
        &agrave; moins qu'elle n'ait &eacute;t&eacute; affect&eacute;e &agrave; un &eacute;tudiant, auquel cas elle devient un sujet au statut  &quot;<strong>Pourvu</strong>&quot;<sup>(1)</sup>.<br>
        - il peut d&eacute;cider de faire  appara&icirc;tre (<img src="images/b_afficher_sujet_web_oui.png" alt="" width="65" height="15" align="absbottom">) ou non (<img src="images/b_afficher_sujet_web_non.png" width="65" height="15" align="absbottom">) la proposition en zone publique
      quelque soit le statut de la proposition<strong></strong>.</p>
      <table border="0">
        <tr>
          <td><span class="noircalibri10"><img src="images/b_attente_validation_sujet.png"></span></td>
          <td><span class="noircalibri10">: au moins un dossier &eacute;tudiant est en attente d'affectation d'un sujet.</span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td><span class="noircalibri10">Vous pouvez :<br>
          - cr&eacute;er un sujet pour l'&eacute;tudiant et le valider ;<br>
					- ou valider un sujet pr&eacute;existant dont la colonne Visa comporte le nom de l'&eacute;tudiant.<br>
					Tant que cette validation n'a pas &eacute;t&eacute; effectu&eacute;e, il ne vous est plus possible de valider un sujet sans &eacute;tudiant associ&eacute; ou de cr&eacute;er un sujet d'un autre type pour lequel il n'y a pas d'affectation en attente.</span></td>
        </tr>
      </table>
  </tr>
</table>
<p class="noircalibri10"><sup>(1)</sup> Le passage du statut &quot;<strong>Valid&eacute;</strong>&quot; au statut  &quot;<strong>Pourvu</strong>&quot; est r&eacute;alis&eacute; lors de l'association du sujet &agrave; un &eacute;tudiant dans la &quot;Gestion des personnels&quot;.<br> 
<sup>(2)</sup><sup></sup> Sujets de Master2, Doctorat et Post-doc. </p>
</td>
<p class="noircalibri10">&nbsp;</p>
<p></p>
<p class="noircalibri10">&nbsp;</p>
<p class="noircalibri10">&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
