<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Aide/Explications individus</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
</head>

<body>
<table width="100%" border="0">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Aide gestion individus','lienretour'=>'gestionindividus.php','texteretour'=>'Retour &agrave; la gestion des individus',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array())) ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><span class="noircalibri10">Cette page pr&eacute;sente la liste de tous les individus qui peuvent &ecirc;tre filtr&eacute;s selon crit&egrave;res (case &agrave; cocher, liste d&eacute;roulante, nom pr&eacute;nom)</span></td>
  </tr>
  <tr>
    <td><span class="noircalibri10"><b>Elle est d&eacute;coup&eacute;e en plusieurs blocs</b> (visibles si au moins un s&eacute;jour d'un individu y figure) :</span></td>
  </tr>
  <tr>
    <td><p><span class="noircalibri10">- <b>Pr&eacute;accueil</b> : les individus qui devraient arriver</span></p>
    <p><span class="noircalibri10">- <b>Accueil</b> : les indivus arriv&eacute;s mais qui n'ont pas tous les visas (<img src="images/b_valider_petite_taille.png" alt="" width="16" height="16">)</span></p>
    <p><span class="noircalibri10">- <b>Non valid&eacute;</b> : les individus dont le s&eacute;jour est termin&eacute; mais dont au moins un visa manque</span></p>
    <p><span class="noircalibri10">- <b>Pr&eacute;sent</b> : les individus dont le s&eacute;jour est en cours et disposant de tous les visas</span></p>
    <p><span class="noircalibri10">- <b>Partis</b> : les s&eacute;jours termin&eacute;s (le plus r&eacute;cent pour chaque individu)</span></p></td>
  </tr>
  <tr>
    <td><p class="noircalibri10"><b>Les ic&ocirc;nes par dossier d'un s&eacute;jour d'un individu :</b></p>
      <table border="1">
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_oeil.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Voir la fiche pour le s&eacute;jour d'un individu (cet ic&ocirc;ne permet aussi de visualiser le d&eacute;tail d'un sujet s'il y en a un)</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="i/op.png" alt="" width="20" height="16"></span></td>
          <td nowrap class="noircalibri10">Voir la fiche pour le s&eacute;jour de cet individu dont le dossier comporte des notes associ&eacute;es</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_liste_puce.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Liste par s&eacute;jour des fiches pour un individu</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_add.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Cr&eacute;er un nouveau s&eacute;jour pour un individu existant (gris&eacute; si la date de fin de s&eacute;jour n'est pas renseign&eacute;e)</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_edit.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Editer la fiche (un double click sur la ligne s&eacute;lectionn&eacute;e a le m&ecirc;me effet)</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_edit_partiel.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Editer la fiche partiellement (limitation suivant le r&ocirc;le de l'utilisateur)</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_drop.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Suppression de la fiche de l'individu pour un s&eacute;jour donn&eacute; (gris&eacute; si le s&eacute;jour ne peut &ecirc;tre supprim&eacute;, non supprimable si pr&eacute;sent ou parti)</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_valider_petite_taille.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Viser le s&eacute;jour. Le visa peut &ecirc;tre accompagn&eacute; d'un message envoy&eacute; aux acteurs concern&eacute;s</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_sablier.png" alt="" width="9" height="16"></span></td>
          <td nowrap class="noircalibri10">En attente du visa pr&eacute;c&eacute;dent qui n'a pas &eacute;t&eacute; appos&eacute;</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_brancher.png" alt="" width="14" height="16"></span></td>
          <td nowrap class="noircalibri10">Le visa n'a pas encore &eacute;t&eacute; appos&eacute; par l'acteur qui peut viser</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_visa.png" alt=""></span></td>
          <td nowrap class="noircalibri10">visa appos&eacute;</td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_attente_validation_sujet.png" alt=""></span></td>
          <td nowrap class="noircalibri10">Le sujet de l'individu n'as &eacute;t&eacute; saisi par le r&eacute;f&eacute;rent qui en est responsable (sauf pour un EXTERIEUR dont la date autorisation est renseign&eacute;e). Visa arriv&eacute;e non apposable. </td>
        </tr>
        <tr>
          <td nowrap><span class="noircalibri10"><img src="images/b_info.png" alt="" width="16" height="16"></span></td>
          <td nowrap class="noircalibri10">Information : le contenu de la colonne FSD, notamment, est expliqu&eacute; &agrave; l'aide de cet ic&ocirc;ne</td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><span class="noircalibri10"><b>Droits d'acc&egrave;s</b> (par d&eacute;faut, param&eacute;trables) : tout membre permanent peut consulter cette liste.</span></td>
  </tr>
  <tr>
    <td><p class="noircalibri10"><span class="noircalibri10">- un <strong>membre permanent</strong> du laboratoire  peut cr&eacute;er une nouvel individu et modifier un s&eacute;jour tant qu'il n'a pas appos&eacute; le visa 'R&eacute;f&eacute;rent'. Par la suite il peut modifier partiellement la fiche<br>
      - un <b>secr&eacute;tariat gestionnaire</b> peut cr&eacute;er, modifier et supprimer un s&eacute;jour<br>
    - <b>SRH, responsable administratif et directeur</b>&nbsp;ont ces droits et peuvent aussi envoyer la d&eacute;claration FSD et apposer le visa</span></p></tr>
  <tr>
    <td>  
  </tr>
  <tr>
    <td><span class="noircalibri10"><b>Visas et</b></span><b><span class="noircalibri10"> envoi de mails aux acteurs</span></b><span class="noircalibri10"> :</span>    <table border="1">
      <tr>
        <td valign="top" nowrap>      
        <td align="center" valign="top" nowrap class="noircalibri10">r&eacute;f&eacute;rent</td>
        <td align="center" valign="top" nowrap class="noircalibri10">secr&eacute;tariat en charge <br>
          du suivi de l'individu</td>
        <td align="center" valign="top" nowrap class="noircalibri10">responsable du groupe<br>
          d'appartenance du r&eacute;f&eacute;rent</td>
        <td align="center" valign="top" nowrap class="noircalibri10">SRH<br>
          (en charge de la d&eacute;claration FSD)</td>
        <td align="center" valign="top" nowrap class="noircalibri10">responsable<br>
          administratif</td>
        <td align="center" valign="top" nowrap class="noircalibri10">Secr&eacute;tariat<br>
          de direction</td>
        <td align="center" valign="top" nowrap class="noircalibri10">Agent de<br>
pr&eacute;vention</td>
        <td align="center" valign="top" nowrap class="noircalibri10">Service<br>
          informatique</td>
        <td align="center" valign="top" nowrap class="noircalibri10">Note</td>
      </tr>
      <tr>
        <td valign="top" nowrap><span class="noircalibri10">R&eacute;f&eacute;rent 
          </span>
          <td align="center" valign="top" nowrap class="noircalibri10">x</td>
          <td align="center" valign="top" nowrap class="noircalibri10">x</td>
          <td align="center" valign="top" nowrap class="noircalibri10">x</td>
          <td align="center" valign="top" nowrap class="noircalibri10">x</td>
          <td align="center" valign="top" nowrap class="noircalibri10">&nbsp;</td>
          <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
          <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
          <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
          <td valign="top" nowrap class="noircalibri10"> <p> Si un sujet est obligatoire mais non renseign&eacute;, le mail pr&eacute;cise que le r&eacute;f&eacute;rent doit le saisir<br>
            Le SRH n'est destinataire que si une d&eacute;claration FSD est n&eacute;cessaire
          </p></td>
      </tr>
      <tr>
        <td valign="top" nowrap class="noircalibri10">FSD
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
        <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
        <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
        <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
      </tr>
      <tr>
        <td valign="top" nowrap class="noircalibri10">Arriv&eacute;e
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">&nbsp;</td>
        <td align="center" valign="top" nowrap class="noircalibri10">&nbsp;</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td align="center" valign="top" nowrap class="noircalibri10">x</td>
        <td valign="top" nowrap class="noircalibri10">&nbsp;</td>
      </tr>
    </table>
      <p class="noircalibri10">La suppression d'un s&eacute;jour, apr&egrave;s que le visa r&eacute;f&eacute;rent ait &eacute;t&eacute; appos&eacute;, occasionne l'envoi d'un mail acteurs concern&eacute;s (colonnes 1 &agrave; 4)<br>
        En plus de ces messages, chaque nuit le syst&egrave;me avertit les secr&eacute;tariat en charge 
du suivi du personnel, le SRH et le responsable
administratif des d&eacute;parts (passage de pr&eacute;sent &agrave; parti)</p>
    </tr>
  <tr>
    <td>  
  </tr>
  <tr>
    <td><span class="noircalibri10"><b>FSD et d&eacute;claration</b> : </span></tr>
  <tr>
    <td><p class="noircalibri10"><b>Si</b> la demande FSD n'est pas n&eacute;cessaire (raison indiqu&eacute;e dans la colonne) si la dur&eacute;e du s&eacute;jour est inf&eacute;rieure ou &eacute;gale &agrave; 5 jours ou si la derni&egrave;re autorisation a moins de 5 ans et les s&eacute;jours contig&uuml;s.<br>
        <b>Sinon</b> :<br>
      - 
      <b>si</b> le visa FSD est appos&eacute; : <img src="images/b_visa.png" alt=""> (<br>
        - <b>sinon</b> les ic&ocirc;nes de cette colonne varient en fonction des dates, des conditions et des pi&egrave;ces jointes figurant dans la fiche et des <b>droits de l'acteur </b>:<br>
    </p>
      <table>
        <tr>
          <td><span class="noircalibri10"><b>1/ Vu par un acteur qui peut faire la demande FSD :<br>
          </b>&nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> une date de demande a &eacute;t&eacute; faite, en attente d'autorisation  : date de la demande </span><span class="orangegrascalibri11">jj/mm/aaa </span><span class="noircalibri10">suivi de <img src="i/v1.png"><br>
&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> pi&egrave;ce manquante : <img src="images/b_attente_cv.png" align="absbottom"> ou <img src="images/b_attente_classeur_excel.png" align="absbottom"><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b> <img src="images/b_fsd_dwnl.png" align="absbottom"> et <img src="images/b_cv_dwnl.png" align="absbottom"> : <img src="images/b_mail_fsd.gif" align="absbottom"> (mail pour le FSD)<br>
<br>
<img src="images/b_attention.png" align="absbottom"> affiche les informations manquantes dans le dossier. <br>
Les dates de demande et d'autorisation sont renseign&eacute;es automatiquement dans la fiche de l'individu sur envoi du mail ou validation.<br>
Il appartient au gestionnaire de modifier ces dates dans la fiche s'il le juge n&eacute;cessaire. Par exemple :<br>
- si le visa n'est pas appos&eacute;, la suppression de la date de demande par mail FSD et de la date d'autorisation permettra de refaire une demande par mail au FSD<br>
- la date  de demande peut &ecirc;tre saisie si le mail a d&eacute;j&agrave; &eacute;t&eacute; envoy&eacute;<br>
A noter : si la date d'autorisation est remplie, le syst&egrave;me ne v&eacute;rifie pas que la date d'envoi au FSD est renseign&eacute;e </span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10"><b>2/ Vu par un acteur qui n'a pas les droits pour faire la d&eacute;claration :<br>
          </b>- <b>si</b> le CV n'est pas joint au dossier de l'individu : <img src="images/b_attente_cv.png"><br>
&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> la demande a &eacute;t&eacute; faite, en attente d'autorisation : date de la demande </span><span class="orangegrascalibri11">jj/mm/aaaa</span><span class="noircalibri10"><br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b> le gestionnaire FSD du labo. doit constituer le dossier et faire la demande : <img src="i/b1.png"> <br>
<img src="images/b_attention.png" align="absbottom"> affiche les informations manquantes dans le dossier. </span></td>
        </tr>
        <tr>
          <td><p class="noircalibri10"><b><br>
            Prolongation/ Renouvellement / Modification (Vu par tous les acteurs) :</b><br>
              - le syst&egrave;me indique qu&rsquo;une prolongation doit &ecirc;tre faite par le texte "expire le ..." pour ... dur&eacute;e &eacute;gale &agrave; datefin - datefin pr&eacute;vue (si &gt; 0)<br>
              &nbsp;&nbsp;la couleur est mauve si la date d&rsquo;expiration est comprise entre 2 et 6 mois, rouge entre 0 et 2 mois<br>
          - la demande faite, "(Nelle demande) date de demande" est affich&eacute; </p></td>
        </tr>
      </table>
</td></tr>
</table>
</body>
</html>
