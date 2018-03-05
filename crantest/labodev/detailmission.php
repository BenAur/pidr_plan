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
$aujourdhui=date('Ymd');
$tab_missionetape=array();
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations du mission (un enreg. vide dans mission pour "creer")
$query_mission =	"SELECT mission.*,pays.libpays as libpays_pers".
									" FROM mission left join pays on mission.codepays_pers=pays.codepays".
									" WHERE codemission=".GetSQLValueString($codemission,"text");
$rs_mission=mysql_query($query_mission) or die(mysql_error());
$row_rs_mission=mysql_fetch_assoc($rs_mission);

// Liste des etapes
$tab_missionetape=array();//raz du tableau ou creation 
$rs=mysql_query("SELECT * from missionetape".
								" where missionetape.codemission=".GetSQLValueString($codemission, "text").
								" order by departdate,departheure") or die(mysql_error());
$nbetape=mysql_num_rows($rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_missionetape[$row_rs['numetape']]=$row_rs;
}
/* for($i=1;$i<=4-$nbetape;$i++)
{ $rs_fields=mysql_query("show columns from missionetape") or die(mysql_error());
	while($row_rs_fields = mysql_fetch_assoc($rs_fields))
	{ $une_etape[$row_rs_fields['Field']]='';
	}
	$tab_missionetape[str_pad($i+$nbetape,2,'0',STR_PAD_LEFT)]=$une_etape;
} */
//$nbetape=min(4,$nbetape);

$query_rs_miss_lieudepart= "SELECT codelieudepart, liblong as liblieudepart FROM miss_lieudepart WHERE codelieudepart!='' ORDER BY numordre";
$rs_miss_lieudepart = mysql_query($query_rs_miss_lieudepart) or die(mysql_error());

$query_rs = "SELECT codeclassetrain, liblong as libclassetrain FROM miss_classetrain WHERE codeclassetrain=".GetSQLValueString($row_rs_mission['codeclassetrain'], "text");
$rs = mysql_query($query_rs) or die(mysql_error());
$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs));

$query_rs = "SELECT codeabonnementtrain, liblong as libabonnementtrain FROM miss_abonnementtrain WHERE codeabonnementtrain=".GetSQLValueString($row_rs_mission['codeabonnementtrain'], "text");
$rs= mysql_query($query_rs) or die(mysql_error());
$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs));

$query_rs = "SELECT codecatmissionnaire, liblong as libcatmissionnaire FROM miss_catmissionnaire WHERE codecatmissionnaire=".GetSQLValueString($row_rs_mission['codecatmissionnaire'], "text");
$rs = mysql_query($query_rs) or die(mysql_error());
$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs));

$query_rs= "SELECT codepuissfiscale, liblong as libpuissfiscale FROM miss_puissfiscale WHERE codepuissfiscale=".GetSQLValueString($row_rs_mission['codepuissfiscale'], "text");
$rs = mysql_query($query_rs) or die(mysql_error());
$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs));

$query_rs_theme="select codestructure as codetheme,libcourt_fr as libtheme".
												" from structure where codestructure<>'00' and esttheme='oui' order by codestructure";
$rs_theme = mysql_query($query_rs_theme) or die(mysql_error());	

$estexterieur=($row_rs_mission['codecatmissionnaire']=='05');

$rs_ouinon=mysql_query("SELECT codeouinon,libcourt as libouinon FROM ouinon WHERE codeouinon<>'' order by numordre desc");
while($row_rs_ouinon=mysql_fetch_assoc($rs_ouinon))
{ $tab_ouinon[$row_rs_ouinon['codeouinon']]=$row_rs_ouinon['libouinon'];
}
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
.monlisting,
.stx table {
    /* The default table for document listings. Contains name, document types, modification times etc in a file-browser-like fashion */
    border-collapse: collapse;
}
.monlisting td{
    border-right: 1px solid #000000;border-top: 1px solid #000000; border-bottom: 1px solid #000000; border-left: 1px solid #000000;
		 padding: 1px;
}


</style>
</head>

<body class="noircalibri9" onLoad="javascript:window.print()">
<table border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
  	<td colspan="3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		    <tr>
		      <td width="18%" rowspan="2" nowrap><img src="images/logo_ul_rvb_mission.jpg"></td>
		      <td colspan="4" align="center" nowrap bgcolor="#FDD3C6"><span class="noirgrascalibri14">DEMANDE D'ORDRE DE MISSION</span></td>
	      </tr>
		    <tr>
		      <td colspan="5" align="center" nowrap class="noircalibri10">En application du d&eacute;cret 2006-781 du 03 juillet 2006 et de la d&eacute;lib&eacute;ration  du CA du 16 d&eacute;cembre 2014 </td>
	      </tr>
		    <tr>
		      <td nowrap class="noirgrascalibri9">Direction du Budget et des Finances (DBF) </td>
		      <td colspan="2" align="center" nowrap bgcolor="#CCCCCC" class="noirgrascalibri10">AVEC FRAIS </td>
		      <td width="7%" nowrap>&nbsp;</td>
		      <td width="37%" align="center" nowrap bgcolor="#CCCCCC" class="noirgrascalibri10">SANS FRAIS</td>
	      </tr>
		    <tr>
		      <td nowrap class="noirgrascalibri8">Sous-Direction de la Gestion Financi&egrave;re </td>
		      <td width="21%" nowrap class="noircalibri9"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecpriseenchargetotale']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Prise en charge totale par l'UL</td>
		      <td width="17%" nowrap class="noircalibri9"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecpriseenchargepartielle']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Prise en charge partielle par l'UL</td>
		      <td nowrap>&nbsp;</td>
		      <td nowrap class="noircalibri9"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecpriseenchargeautre_ul']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Prise en charge par&nbsp;<span class="noircalibri9"><?php echo $row_rs_mission['organismepriseencharge']==''?str_repeat('.',50):$row_rs_mission['organismepriseencharge'].str_repeat('&nbsp;',50-strlen($row_rs_mission['organismepriseencharge'])); ?></span></td>
	      </tr>
    	</table>
    </td>
	</tr>
  <tr>
  <td height="5" colspan="3"><img src="images/espaceur.gif" width="20" height="8"></td></tr>
  <tr>
    <td height="1" colspan="3"></td>
  </tr>
  <tr>
  	<td colspan="3">
  		<table cellpadding="0" cellspacing="0">
        <tr>
          <td nowrap bgcolor="#FDD3C6" class="noirgrascalibri12">INFORMATIONS SUR LE &quot;MISSIONNAIRE&quot; :&nbsp;&nbsp;</td>
          <td nowrap class="noircalibri9">&nbsp;<img src="images/b_checked_mission_<?php echo ($row_rs_mission['codeciv']=='2' || $row_rs_mission['codeciv']=='3')?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Madame</td>
          <td nowrap class="noircalibri9">&nbsp;<img src="images/b_checked_mission_<?php echo $row_rs_mission['codeciv']=='1'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Monsieur</td>
        </tr>
  		</table>
  	</td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="17%" nowrap><i class="noircalibri11">Nom :&nbsp;</i></td>
        <td width="27%" nowrap class="noircalibri11"><?php echo $row_rs_mission['nom']==''?str_repeat('.',30):$row_rs_mission['nom']; ?></td>
        <td width="9%" align="right" nowrap> <i class="noircalibri11">Pr&eacute;nom :&nbsp;</i></td>
        <td width="12%" nowrap class="noircalibri11"><?php echo $row_rs_mission['prenom']==''?str_repeat('.',20):$row_rs_mission['prenom']; ?></td>
        <td width="12%" nowrap><i class="noircalibri11">Date de naissance :&nbsp;</i></td>
        <td width="23%" valign="top" nowrap class="noircalibri11"><?php echo $row_rs_mission['date_naiss']==''?str_repeat('.',10):aaaammjj2jjmmaaaa($row_rs_mission['date_naiss'],'/') ?></td>
      </tr>
      <tr>
        <td nowrap><i class="noircalibri11">Statut :&nbsp;</i></td>
        <td class="noircalibri11"><?php echo $row_rs_mission['libcatmissionnaire']==''?str_repeat('.',15):$row_rs_mission['libcatmissionnaire'] ?></td>
        <td align="right" nowrap class="noircalibri11"><i>T&eacute;l. portable :&nbsp;</i></td>
        <td nowrap class="noircalibri11"><?php echo $row_rs_mission['telport']==''?str_repeat('.',20):$row_rs_mission['telport']; ?></td>
        <td nowrap class="noircalibri11"><i>Adresse mail :&nbsp;</i></td>
        <td class="noircalibri11"><?php echo $row_rs_mission['email']==''?str_repeat('.',50):$row_rs_mission['email']; ?></td>
      </tr>
      <tr>
        <td nowrap valign="top"><i class="noircalibri11">R&eacute;sidence personnelle :</i>&nbsp;</td>
        <td colspan="2" valign="top" nowrap class="noircalibri11"><?php echo $row_rs_mission['adresse_pers']==''?str_repeat('.',50):nl2br($row_rs_mission['adresse_pers']).'<br>'.$row_rs_mission['codepostal_pers'].' '.$row_rs_mission['ville_pers'].' '.$row_rs_mission['libpays_pers']; ?></td>
        <td nowrap valign="top"><i class="noircalibri11">R&eacute;sidence administrative :&nbsp;</i></td>
        <td colspan="2" valign="top" nowrap class="noircalibri11" ><?php echo $row_rs_mission['adresse_admin']==''?str_repeat('.',50):nl2br($row_rs_mission['adresse_admin']); ?></td>
        </tr>
    </table></td>
  </tr>
  <tr>
    <td colspan="3"><img src="images/espaceur.gif" alt="" width="20" height="5"></td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%" border="1" cellpadding="0" cellspacing="0">
      <tr>
        <td width="50%" align="center" bgcolor="#CCCCCC"><span class="noirgrascalibri11">TRAIN</span></td>
        <td width="50%" align="center" bgcolor="#CCCCCC"><span class="noirgrascalibri11">AVION</span></td>
      </tr>
      <tr>
        <td style="border-bottom:none;"><span class="noirgrascalibri11">TYPE D'ABONNEMENT : </span></td>
        <td style="border-bottom:none;"><span class="noirgrascalibri11">TYPE D'ABONNEMENT : </span></td>
      </tr>
      <tr>
        <td style="border-bottom:none; border-top:none"><table width="100%" border="0">
          <tr>
            <td width="50%" nowrap class="noircalibri11"><i>N&deg; de carte : </i><?php echo $row_rs_mission['numcarteabonnetrain']==''?str_repeat('.',20):$row_rs_mission['numcarteabonnetrain'].str_repeat('&nbsp;',20-strlen($row_rs_mission['numcarteabonnetrain'])); ?></td>
            <td nowrap class="noircalibri11"><i>&nbsp;Date d'expiration : </i><?php echo $row_rs_mission['dateabonnetrainexpire']==''?str_repeat('.',10):aaaammjj2jjmmaaaa($row_rs_mission['dateabonnetrainexpire'],'/').str_repeat('&nbsp;',10-strlen($row_rs_mission['dateabonnetrainexpire'])); ?></td>
          </tr>
        </table></td>
        <td style="border-bottom:none; border-top:none"><table width="100%" border="0">
          <tr>
            <td width="50%" nowrap class="noircalibri11" style="border-bottom:none; border-top:none"><i>N&deg; de carte : </i><?php echo $row_rs_mission['numcarteabonneavion']==''?str_repeat('.',20):$row_rs_mission['numcarteabonneavion'].str_repeat('&nbsp;',20-strlen($row_rs_mission['numcarteabonneavion'])); ?></td>
            <td nowrap class="noircalibri11" style="border-bottom:none; border-top:none"><i>&nbsp;Date d'expiration : </i><?php echo $row_rs_mission['dateabonneavionexpire']==''?str_repeat('.',10):aaaammjj2jjmmaaaa($row_rs_mission['dateabonneavionexpire'],'/').str_repeat('&nbsp;',10-strlen($row_rs_mission['dateabonneavionexpire'])); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="noircalibri11" style="border-bottom:none; border-top:none"><i>Zone g&eacute;ographique : </i><?php echo $row_rs_mission['zonegeoabonnetrain']==''?str_repeat('.',50):$row_rs_mission['zonegeoabonnetrain'].str_repeat('&nbsp;',50-strlen($row_rs_mission['zonegeoabonnetrain'])); ?></td>
        <td class="noircalibri11" style="border-bottom:none; border-top:none"><i>Zone g&eacute;ographique : </i><?php echo $row_rs_mission['zonegeoabonneavion']==''?str_repeat('.',50):$row_rs_mission['zonegeoabonneavion'].str_repeat('&nbsp;',50-strlen($row_rs_mission['zonegeoabonneavion'])); ?></td>
      </tr>
      <tr>
        <td class="noirgrascalibri11" style="border-bottom:none; border-top:none">TYPE DE  PROGRAMME DE FIDELITE : </td>
        <td class="noirgrascalibri11" style="border-bottom:none; border-top:none">TYPE DE  PROGRAMME DE FIDELITE : </td>
      </tr>
      <tr>
        <td class="noircalibri11" style="border-bottom:none; border-top:none"><i>N&deg; de carte : </i><?php echo $row_rs_mission['numcartefidelitetrain']==''?str_repeat('.',20):$row_rs_mission['numcartefidelitetrain'].str_repeat('&nbsp;',20-strlen($row_rs_mission['numcartefidelitetrain'])); ?></td>
        <td class="noircalibri11" style="border-bottom:none; border-top:none"><i>N&deg; de carte : </i><?php echo $row_rs_mission['numcartefideliteavion']==''?str_repeat('.',20):$row_rs_mission['numcartefideliteavion'].str_repeat('&nbsp;',20-strlen($row_rs_mission['numcartefideliteavion'])); ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td colspan="3">
     <table width="100%" border="0" cellpadding="0" cellspacing="3">
      <tr>
        <td nowrap><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            	<td colspan="3">
                <table cellpadding="0" cellspacing="0">
                  <tr>
                      <td colspan="2" valign="top" nowrap bgcolor="#FDD3C6"><span class="noirgrascalibri12">INFORMATIONS SUR LE D&Eacute;PLACEMENT :&nbsp;&nbsp;</span></td>
                  </tr>
                </table>
              </td>
          </tr>
          <tr>
            <td width="42%" valign="top" class="noircalibri11"><i><span class="noircalibri11">Motif :</span></i><span class="noircalibri10"></span>&nbsp;<?php echo $row_rs_mission['motif']==''?str_repeat('.',50):nl2br($row_rs_mission['motif']) ?></td>
            <td width="58%" class="noircalibri11"><i>Pays : </i><?php echo $row_rs_mission['libpays']==''?str_repeat('.',50):$row_rs_mission['libpays'] ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="10%" rowspan="2" bgcolor="#CCCCCC" class="noirgrascalibri12" nowrap>A commander&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td width="12%" nowrap bgcolor="#CCCCCC" class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avectrain']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Train</td>
            <td width="7%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
            <td width="24%" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecavion']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Avion</span></td>
            <td width="15%" align="center" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecvehiculelocation']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;V&eacute;hicule de location</span></td>
            <td width="32%" nowrap bgcolor="#CCCCCC" class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecautre']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;Autre &agrave; pr&eacute;ciser :<span class="noircalibri11"><?php echo $row_rs_mission['avecautredetail'].str_repeat('&nbsp;',50-strlen($row_rs_mission['avecautredetail'])); ?></span></td>
          </tr>
          <tr>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avechotel']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10">&nbsp;H&ocirc;tel&nbsp; </td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>Dates :</i></td>
            <td nowrap bgcolor="#CCCCCC"><span class="noircalibri11"><?php echo $row_rs_mission['hoteldates']==''?str_repeat('.',50).'&nbsp;':$row_rs_mission['hoteldates'].str_repeat('&nbsp;',30-strlen($row_rs_mission['hoteldates'])); ?></span></td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>Nombre de nuit(s) &agrave; r&eacute;server :</i></td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><?php echo $row_rs_mission['nbnuitshotelcharge']==''?str_repeat('.',2):$row_rs_mission['nbnuitshotelcharge'].str_repeat('&nbsp;',2-strlen($row_rs_mission['nbnuitshotelcharge'])); ?></td>
          </tr>
          <tr>
            <td nowrap bgcolor="#CCCCCC">&nbsp;</td>
            <td nowrap bgcolor="#CCCCCC">&nbsp;</td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>Choix 1 :</i></td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><?php echo $row_rs_mission['hotelmarchechoix1']==''?str_repeat('.',50):$row_rs_mission['hotelmarchechoix1'].str_repeat('&nbsp;',50-strlen($row_rs_mission['hotelmarchechoix1'])); ?></td>
            <td align="center" nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>Choix 2 :</i></td>
            <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><?php echo $row_rs_mission['hotelmarchechoix2']==''?str_repeat('.',50):$row_rs_mission['hotelmarchechoix2'].str_repeat('&nbsp;',50-strlen($row_rs_mission['hotelmarchechoix2'])); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><img src="images/espaceur.gif" alt="" width="20" height="8"></td>
      </tr>
      <tr>
        <td>
         <table width="100%" cellpadding="0" cellspacing="0" class="monlisting">
          <tr class="noirgrascalibri10">
            <td align="center" class="noirgrascalibri10">Date<br>
              d&eacute;part</td>
            <td align="center" class="noirgrascalibri10">Heure<br>
              d&eacute;part</td>
            <td align="center" nowrap class="noirgrascalibri10">VILLE DE DEPART</td>
            <td align="center" nowrap class="noirgrascalibri10">VILLE DE DESTINATION</td>
            <td align="center" class="noirgrascalibri10">Date<br>
              arriv&eacute;e</td>
            <td align="center" class="noirgrascalibri10">Heure<br>
              arriv&eacute;e</td>
            <td align="center" nowrap class="noirgrascalibri10">Transport utilis&eacute;</td>
          </tr>
          <?php 
          foreach($tab_missionetape as $numetape=>$une_missionetape)
          {?>
          <tr>
            <td align="center" class="noircalibri10"><?php echo $une_missionetape['departdate']==''?'&nbsp;':aaaammjj2jjmmaaaa($une_missionetape['departdate'],'/'); ?></td>
            <td align="center" class="noircalibri10"><?php echo substr($une_missionetape['departheure'],0,2)==''?'&nbsp;':(substr($une_missionetape['departheure'],0,2).'h'.substr($une_missionetape['departheure'],3,2)); ?></td>
            <td class="noircalibri10"><?php echo $une_missionetape['departlieu']==''?'&nbsp;':$une_missionetape['departlieu']; ?></td>
            <td class="noircalibri10"><?php echo $une_missionetape['arriveelieu']==''?'&nbsp;':$une_missionetape['arriveelieu']; ?></td>
            <td align="center" class="noircalibri10"><?php echo $une_missionetape['arriveedate']==''?'&nbsp;':aaaammjj2jjmmaaaa($une_missionetape['arriveedate'],'/'); ?></td>
            <td align="center" class="noircalibri10"><?php echo substr($une_missionetape['arriveeheure'],0,2)==''?'&nbsp;':(substr($une_missionetape['arriveeheure'],0,2).'h'.substr($une_missionetape['arriveeheure'],3,2)); ?></td>
            <td align="center" class="noircalibri10"><?php echo substr($une_missionetape['moyentransport'],0,2)==''?'&nbsp;':$une_missionetape['moyentransport']; ?></td>
          </tr>
          <?php 
          }?>
        </table>
       </td>
      </tr>
     </table>
    </td>
  </tr>
  <tr>
    <td colspan="3" class="noircalibri11"><i>Informations compl&eacute;mentaires sur le d&eacute;roulement de la mission :</i><?php echo $row_rs_mission['infocompmission']==''?str_repeat('.',50):$row_rs_mission['infocompmission']; ?><br>
    <img src="images/flechemission.png" width="22" height="13"> <i><span class="noircalibri9">(Pour des raisons de couverture d'assurance, merci de pr&eacute;ciser la p&eacute;riode prise pour convenance personnelle incluse dans la mission)</span></i></td>
  </tr>
  <tr>
    <td colspan="3" class="noircalibri11"><table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="6%" rowspan="2" nowrap bgcolor="#CCCCCC" class="noirgrascalibri12">J'utilise&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="13%" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecvehiculeservice']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10"> Un v&eacute;hicule administratif</span></td>
        <td width="56%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
        <td width="25%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
      </tr>
      <tr>
        <td nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecvehiculepersonnel']=='oui'?'oui':'non' ?>.png" alt="" width="10" height="10"> Mon v&eacute;hicule personnel</span></td>
        <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>Noms des personnes transport&eacute;es :</i> <?php echo $row_rs_mission['vehiculepersonnelnomspersonnes']==''?str_repeat('.',50):nl2br($row_rs_mission['vehiculepersonnelnomspersonnes']); ?></td>
        <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><i>&nbsp;&nbsp;Immatriculation :</i> <?php echo $row_rs_mission['numimmatriculation']==''?str_repeat('.',20):$row_rs_mission['numimmatriculation'].str_repeat('&nbsp;',20-strlen($row_rs_mission['numimmatriculation'])); ?></td>
      </tr>
    </table></td>
  </tr>
      <tr>
        <td class="noirgrascalibri8">(* *) Je dois  au pr&eacute;alable obtenir l'autorisation via le formulaire &quot;Demande d'autorisation d'utilisation d'un v&eacute;hicule personnel&quot; </td>
      </tr>
      <tr>
        <td nowrap><table cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2" valign="top" nowrap bgcolor="#FDD3C6"><span class="noirgrascalibri12">INFORMATIONS FINANCI&Egrave;RES :&nbsp;&nbsp;</span></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><img src="images/espaceur.gif" alt="" width="20" height="8"></td>
      </tr>
      <tr>
        <td nowrap><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="29%" nowrap><span class="noirgrascalibri11">CO&Ucirc;T TOTAL DE LA MISSION ESTIM&Eacute; A :</span> <span class="noircalibri11"><?php echo $row_rs_mission['montantestimemission']==''?str_repeat('.',12):$row_rs_mission['montantestimemission'].str_repeat('&nbsp;',12-strlen($row_rs_mission['montantestimemission'])); ?></span> <span class="noircalibri11">&#8364;</span></td>
            <td width="7%"><img src="images/espaceur.gif" width="100" height="1"></td>
            <td width="64%" nowrap><span class="noirgrascalibri11">FORFAIT ACCORD&Eacute;</span> <span class="noircalibri9">(tout compris)</span> <b>: </b><span class="noircalibri11"><?php echo $row_rs_mission['forfait']==''?str_repeat('.',12):$row_rs_mission['forfait'].str_repeat('&nbsp;',12-strlen($row_rs_mission['forfait'])); ?> &#8364;</span></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td nowrap><span class="noirgrascalibri11">DEMANDE D'AVANCE</span> <span class="noircalibri10">(limit&eacute;e &agrave; 75 % des frais de la mission)</span> <span class="noircalibri11"><img src="images/b_checked_mission_<?php echo $row_rs_mission['avecavance']=='oui'?'oui':'non'; ?>.png" alt="" width="12" height="12"> OUI&nbsp;&nbsp;&nbsp; <img src="images/b_checked_mission_<?php echo $row_rs_mission['avecavance']!='oui'?'oui':'non'; ?>.png" alt="" width="12" height="12"> NON</span></td>
      </tr>
      <tr>
        <td nowrap><img src="images/espaceur.gif" alt="" width="20" height="8"></td>
      </tr>
      <tr>
        <td nowrap><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="24%" rowspan="2"><table width="100%" border="1" cellpadding="0" cellspacing="0" class="monlisting">
              <tr>
                <td width="60%" bgcolor="#CCCCCC">&eacute;OTP :</td>
                <td width="40%" bgcolor="#CCCCCC">&nbsp;<?php echo $row_rs_mission['eotp'] ?></td>
              </tr>
              <tr>
                <td bgcolor="#CCCCCC">Centre de co&ucirc;t : </td>
                <td bgcolor="#CCCCCC">&nbsp;<?php echo $row_rs_mission['centrecout'] ?></td>
              </tr>
            </table></td>
            <td width="27%" height="22" class="noircalibri11">&nbsp;A
              <?php  echo str_repeat("&nbsp;", 25);?>
            , le </td>
            <td width="19%"><span class="noircalibri11">le
				
            </span></td>
            <td width="30%" class="noircalibri11">A
              <?php  echo str_repeat("&nbsp;", 25);?>, le
						</td>
          </tr>
          <tr>
            <td class="noircalibri9">&nbsp;(signature du missionnaire) </td>
            <td class="noircalibri9"> (signature du sup&eacute;rieur hi&eacute;rarchique)&nbsp;&nbsp;</td>
            <td class="noircalibri9">(signature du Pr&eacute;sident ou personne b&eacute;n&eacute;ficiant d'une d&eacute;l&eacute;gation) </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td nowrap>&nbsp;</td>
      </tr>
    </table>
<tr>
    <td colspan="3"></td>    
</body>
</html>
    <?php

if(isset($rs_miss_lieudepart))mysql_free_result($rs_miss_lieudepart);
if(isset($rs_theme))mysql_free_result($rs_theme);
if(isset($rs_mission))mysql_free_result($rs_mission);
if(isset($rs_missionetape))mysql_free_result($rs_missionetape);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
