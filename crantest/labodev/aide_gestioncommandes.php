<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>" />
<title>Aide 12+ commandes</title>
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
</head>
<body>
<table width="100%" border="0">
  <?php echo entete_page(array('image'=>'','titrepage'=>'Aide gestion commandes','lienretour'=>'gestioncommandes.php','texteretour'=>'Retour &agrave; la gestion des commandes',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array())) ?>
  <tr>
    <td>Cette page pr&eacute;sente la liste de toutes les commandes/missions restreinte selon  des droits (demandeur, responsable de cr&eacute;dits, secr&eacute;tariat, gestionnaire de cr&eacute;dits (IEB),...). Elle peut &ecirc;tre filtr&eacute;e selon crit&egrave;res (case &agrave; cocher, liste d&eacute;roulante, ...). Certaines colonnes peuvent &ecirc;tre masqu&eacute;es avec <img src="images/b_checked_oui.png" width="16" height="16" /> et la liste tri&eacute;e avec <img src="images/b_tri_fleche_croissant.png" width="14" height="10" />.</td>
  </tr>
  <tr>
    <td><b>Elle est d&eacute;coup&eacute;e en plusieurs blocs</b> (visibles si au moins une commande/mission y figure) :</td>
  </tr>
  <tr>
    <td><p>- <b>Demande</b> : Commandes (et mission/commandes associ&eacute;es) dont 1er visa IEB* non appos&eacute; ou n&deg; commande non saisi / Mission sans commande non encore effectu&eacute;e</p>
      <p>- <b>Engag&eacute;</b> : Commande (et mission/commandes associ&eacute;es) dont 1er visa IEB appos&eacute; et n&deg; commande saisi</p>
      <p>- <b>Service fait</b> : Commande (et mission/commandes associ&eacute;es) dont visa secr. appos&eacute; et Service fait (MIGO)</p>
      <p>- <b>Pay&eacute; </b> : Commande (et mission/commandes associ&eacute;es) dont date de paiement renseign&eacute;e / Mission sans commande effectu&eacute;e</p>
    <p>- <b>Annul&eacute;</b> : les s&eacute;jours termin&eacute;s (le plus r&eacute;cent pour chaque individu)</p>
    <p><b>Pour une mission</b>, le passage d'un bloc &agrave; l'autre est effectif lorsque toutes ses commandes respectent les conditions ci-dessus</p></td>
  </tr>
  <tr>
    <td><p>Les commandes sont symbolis&eacute;es par <img src="images/b_euro.png" width="16" height="16" />, les missions par  <img src="images/b_avion.png" width="16" height="16" />. Les commandes sont ind&eacute;pendantes ou d&eacute;pendantes d'une mission, auquel cas elles figurent sous cette derni&egrave;re.<br />
      Les commandes d'&eacute;quipement sont symbolis&eacute;es par <img src="images/b_cmd_equipement.png" width="20" height="16" />
    (ou <img src="images/b_cmd_inventorie.png" width="20" height="16" /> si l'&eacute;quipement a &eacute;t&eacute; inventori&eacute;)</p></td>
  </tr>
</table>
<p>*IEB : service charg&eacute; de l'ex&eacute;cution budg&eacute;taire</p>
<p></p>
<p><b>Signification des ic&ocirc;nes :</b></p>
<table border="0">
  <tr>
    <td><img src="images/b_taille_colonnes_plus.png" width="35" height="16" /></td>
    <td>Voir le contenu du texte complet de chaque colonne </td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_voir_equipement_seul_non.png" width="34" height="18" /></td>
    <td>Voir toutes les commandes</td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_voir_equipement_seul_oui.png" width="30" height="16" /></td>
    <td>Voir  les commandes d'&eacute;quipement</td>
  </tr>
  <tr>
    <td><img src="images/b_euro.png" alt="" width="16" height="16" /></td>
    <td>Commande</td>
  </tr>
  <tr>
    <td><img src="images/b_oeil.png" width="16" height="16" /></td>
    <td>Afficher une fen&ecirc;tre du d&eacute;tail d'une commande</td>
  </tr>
  <tr>
    <td><img src="images/b_imprimer.png" width="16" height="16" /></td>
    <td>Imprimer la mission</td>
  </tr>
  <tr>
    <td><img src="images/b_edit.png" width="16" height="16" /></td>
    <td>Modifier une commande/mission</td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_miss_plus.png" width="16" height="16" /></td>
    <td>Ajouter une commande associ&eacute;e &agrave; une mission</td>
  </tr>
  <tr>
    <td><img src="images/b_drop.png" width="16" height="16" /></td>
    <td>Supprimer une commande/mission</td>
  </tr>
  <tr>
    <td><img src="images/b_dupliquer.png" width="16" height="15" /></td>
    <td>Dupliquer une mission/commande</td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_miss_annuler_oui.png" width="16" height="16" /></td>
    <td>Annuler une mission/commande</td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_miss_annuler_non.png" width="16" height="16" /></td>
    <td>R&eacute;tablir  une mission/commande</td>
  </tr>
  <tr>
    <td><img src="images/tirelire.png" width="20" height="17" /></td>
    <td>Cr&eacute;er un avoir</td>
  </tr>
  <tr>
    <td><img src="images/b_cmd_annule_avoir.png" width="20" height="16" /></td>
    <td>Annuler un avoir</td>
  </tr>
  <tr>
    <td><img src="images/b_invalider.png" width="16" height="16" /></td>
    <td>Invalider un visa (IEB* uniquement)</td>
  </tr>
  <tr>
    <td><img src="images/b_mail.gif" width="16" height="16" /></td>
    <td>(Rappel de ) demande de justificatifs pour frais de missions</td>
  </tr>
</table>
<p>Commandes &agrave; imputations multiples : exemple de 3 imputations avec un montant engag&eacute; de 1.00 chacune<br />
  - les 3 imputations apparaissent dans la ligne de la commande concern&eacute;e :<br />
  - 
  Suite au visa 'Demande', les responsables de cr&eacute;dits concern&eacute;s re&ccedil;oivent chacun un mail.<br />
  La suite ne concerne que l'IEB :<br />
  - au fur et &agrave; mesure que les responsables de cr&eacute;dits valident :<br />
  &nbsp; - l'IEB re&ccedil;oit des messages dont la (les) ligne qui vient d'&ecirc;tre      valid&eacute;e par le resp. de cr&eacute;dits est mauve, celles qui le sont d&eacute;j&agrave;      vertes et les autres noires. La mention &quot;Visa partiel Resp.        cr&eacute;dits&quot; est pr&eacute;cis&eacute;e dans le message (<span class="bleucalibri10">voir *Exemple de message re&ccedil;u : ci-dessous</span>)<br />
  &nbsp; - un <b>(X)</b> pr&eacute;fixe les imputations 'virtuelles' qui ont &eacute;t&eacute;      valid&eacute;es dans la liste des commandes.<br />
  <b>	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(X)</b> UL-CID-DUPONT J. - IUF J. D<br />
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UL-CID-ACI - PETIT M. - RSA<br />
  <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(X)</b> UL-Direction-2013 - CNEL M. <br />
- la validation de la derni&egrave;re imputation apposera le visa 'resp' pour      toute la commande. Le message pr&eacute;cisera&nbsp; &quot;Visa resp. cr&eacute;dits&quot; et      les (X) disparaissent</p>
<table border="0" align="center">
  <tr>
    <td><p><span class="bleucalibri10">*Exemple de message re&ccedil;u :</span><br />
      - Dupont vient de valider : le message fait suite &agrave; sa validation      et sa ligne est mauve <br />
      - Petit n'a pas encore valid&eacute;<br />
- La Direction a d&eacute;j&agrave; valid&eacute; : ligne verte <br />
      <br />
      <span class="bleucalibri10">Le message g&eacute;n&eacute;r&eacute; pour cet exemple :</span><br />
      Bonjour, </p>
      <p>La commande n&deg; 02645 a &eacute;t&eacute; valid&eacute;e sur le serveur 12+ par DUPONT J. </p>
      <p>(<strong>Visa&nbsp;partiel&nbsp;Resp. cr&eacute;dits</strong>) </p>
      <p><strong>Demandeur : </strong>F. DURAND <br />
        <strong>Objet : </strong>x <br />
        <strong>Fournisseur : </strong>y <strong><br />
      Imputation &nbsp;&nbsp;&nbsp;Cr&eacute;dits : </strong>UL<font color="#993399"><strong>&nbsp;&nbsp;&nbsp;</strong></font></p>
      <p><font color="#993399"><strong>Enveloppe          : </strong>CID<strong>&nbsp;&nbsp;&nbsp;Source/contrat : </strong>DUPONT J. - IUF J.        DUPONT<strong>&nbsp;&nbsp;&nbsp;Montant : </strong>1</font> <font color="#00CC00"><strong>&nbsp;&nbsp;&nbsp;<br />
        </strong></font><font color="#000000"><strong>Enveloppe : </strong>CID<strong>&nbsp;&nbsp;&nbsp;Source/contrat          : </strong>ACI - PETIT M. - RSA -- LDDIR<strong>&nbsp;&nbsp;&nbsp;Montant : </strong>1</font><br />
        <font color="#00CC00"><strong>Enveloppe : </strong>Direction<strong>&nbsp;&nbsp;&nbsp;Source/contrat          : </strong>2013 - CNEL M.- RSA -- LDDIR<strong>&nbsp;&nbsp;&nbsp;Montant : </strong>1</font> <font color="#000000"><strong>&nbsp;&nbsp;&nbsp;<br />
          </strong></font><br />
          cordialement, <br />
        <br />
    Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+</p></td>
  </tr>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
</body>
</html>
