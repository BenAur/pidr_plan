<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Aide/Explications individus</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
</head>

<body>
<p class="noircalibri10">
    Les dates de s&eacute;jour pr&eacute;vues ne sont pas visibles par les secr. de site et sont remplac&eacute;es par les
    dates de s&eacute;jour tant que la date de demande d'autorisation et la date d'autorisation sont vides.<br>
    La personne en charge des demandes d'acc&egrave;s peut modifier ces dates.<br>
    Si la <b>dur&eacute;e pr&eacute;vue</b> est inf&eacute;rieure ou &eacute;gale &agrave; 5 jours, s&rsquo;il s&rsquo;agit d&rsquo;un stagiaire &lt; BAC+5 ou si la derni&egrave;re autorisation FSD est fix&eacute;e &agrave; t0 (date de la demande group&eacute;e) inf&eacute;rieure &agrave; 5 ans et les s&eacute;jours sont contig&uuml;s, la demande n&rsquo;a pas lieu d'&ecirc;tre faite.<br>
  <b>Si une date de demande d'autorisation est saisie, l'apposition du visa FSD n'est pas requise.</b></p>
<p class="noircalibri10"><b>Prolongation/modification :</b> utilisation de la date de demande de modification, pas de visa &agrave; apposer<br>
    1/ Une <b>prolongation</b>, sans information suppl&eacute;mentaire, peut &ecirc;tre accord&eacute;e si la dur&eacute;e de la prolongation n'xc&egrave;de pas 
    la dur&eacute;e d&eacute;clar&eacute;e du s&eacute;jour et si aucune modification n&rsquo;est apport&eacute;e &agrave; la demande initiale (statut, sujet, ....).<br>
    Si cette dur&eacute;e est &quot;inf&eacute;rieure &agrave; 2 mois&quot; OU &quot;sup&eacute;rieure &agrave; 2 mois ET d'une dur&eacute;e inf&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale&quot;, l&rsquo;autorisation est donn&eacute;e par le FSD de l&rsquo;&eacute;tablissement, sinon elle est transmise aux services du HFDS.<br>
    Proc&eacute;dure &agrave; suivre :<br>
    - envoyer un mail au FSD, pr&eacute;cisant le n&deg; et la date d&rsquo;autorisation initiale, les nom et pr&eacute;nom de la personne concern&eacute;e, les dates de s&eacute;jour initiales,&nbsp;&nbsp;la nouvelle date de fin et la dur&eacute;e de la prolongation.<br>
    - saisir la date de demande de modification et copier le contenu du mail dans la zone notes, ainsi que les dates de demande d&rsquo;acc&egrave;s et d&rsquo;autorisation actuelles<br>
    - A r&eacute;ception de l&rsquo;autorisation, remplacer la date de demande d&rsquo;acc&egrave;s par la date de demande de modification (effacer cette derni&eacute;re), la date d&rsquo;autorisation par la nouvelle, la date de fin pr&eacute;vue par la nouvelle. Joindre l&rsquo;avis d&rsquo;autorisation en PJ "Retour FSD" le cas &eacute;ch&eacute;ant<br> 
    2/ Une <b>nouvelle demande de modification</b> doit &ecirc;tre faite dans tous les autres cas : sup&eacute;rieure &agrave; 2 mois ET d'une dur&eacute;e sup&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale , ou AVEC un autre changement (change de statut, PAS le m&ecirc;me financement, PAS le m&ecirc;me sujet de travail, PAS le m&ecirc;me lieu de travail ZRR....)<br>
    Proc&eacute;dure &agrave; suivre :<br>
    - sauvegarder le fichier FSD de demande initiale, copier les dates pr&eacute;vues, demande d&rsquo;acc&egrave;s et autorisation et le n&deg; zrr de cette demande dans la zone notes.<br>
    - effacer ce n&deg;, renseigner les nouvelles dates pr&eacute;vues, enregistrer et refaire un nouveau fichier avec un nouveau n&deg; zrr. Faire figurer le n&deg; initial dans ce fichier manuellement.<br>
    - &eacute;craser le fichier initial par ce nouveau fichier.<br>
    - envoyer un mail au FSD, pr&eacute;cisant le n&deg; et la date d&rsquo;autorisation initiale, les nom et pr&eacute;nom de la personne concern&eacute;e, les dates de s&eacute;jour pr&eacute;vues initiales,&nbsp;les nouvelles dates pr&eacute;vues &eacute;ventuelles et la (ou les) raison de cette modifiction de demande en y adjoignant le fichier FSD<br>
    - saisir la date de demande de modification et copier le contenu du mail dans la zone notes  <br>
    - A r&eacute;ception de l&rsquo;autorisation, remplacer la date de demande d&rsquo;acc&egrave;s par la date de demande de modification (effacer cette derni&eacute;re), la date d&rsquo;autorisation
    par la nouvelle, joindre l&rsquo;avis d&rsquo;autorisation en PJ "Retour FSD" le cas &eacute;ch&eacute;ant<br>
  </p>
  <p class="noircalibri10">* Mail D.HUSSON du 08/09/2015 : Pour les demandes de prolongation de + de 2 mois et d'une dur&eacute;e inf&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale , et SANS autre changement (NE change PAS de statut, m&ecirc;me financement, m&ecirc;me sujet, m&ecirc;me lieu de travail ZRR....) : vous n'&ecirc;tes pas  oblig&eacute; de refaire un dossier complet ZRR : cf point pr&eacute;c&eacute;dent.<br>
  Pour les demandes de prolongation de + de 2 mois ou d'une dur&eacute;e sup&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale , ou AVEC un autre changement (change de statut, PAS le m&ecirc;me financement, PAS le m&ecirc;me sujet de travail, PAS le m&ecirc;me lieu de travail ZRR....) : dossier complet (classeur XLS). Vous pouvez l'indiquer dans l'onglet &quot;compl&eacute;ment&quot; dans la case : E03 (Demandeur b&eacute;n&eacute;ficiant d&eacute;j&agrave; d'une autorisation d'acc&egrave;s dans une ZRR) et remettre l'ancien num&eacute;ro ZRR dans la case E03A (R&eacute;f&eacute;rence de l'autorisation) </p>
</body>
</html>
