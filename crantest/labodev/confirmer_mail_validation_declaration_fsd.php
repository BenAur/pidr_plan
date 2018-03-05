<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_destinataires=array();
$tab_mail_unique=array();
//if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/
}
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");
$erreur="";
$warning="";
$erreur_envoimail="";
$message_resultat_affiche="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$form_confirmer_mail_validation_declaration_fsd="confirmer_mail_validation_declaration_fsd";
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
// changement du nom cv en nom prenom - CV - numdossierzrr avant tout : uniquement si MM_update n'est pas envoye = aa 1er appel du programme
if(!isset($_POST["MM_update"]))
{ $query_rs="select nomfichier from individupj, typepjindividu".
					" where individupj.codeindividu=".GetSQLValueString($codeindividu,"text")." and individupj.numcatpj=".GetSQLValueString($numsejour, "text").
					" and individupj.codelibcatpj='sejour' and typepjindividu.codelibtypepj='cv' and typepjindividu.codetypepj=individupj.codetypepj";
	$rs=mysql_query($query_rs);
	if($row_rs=mysql_fetch_assoc($rs))
	{ $tab_decompose_nomfichier=explode('.', $row_rs['nomfichier']);
		$extension="";
		if(count($tab_decompose_nomfichier)>1)
		{ end($tab_decompose_nomfichier);
			$tab_decompose_nomfichier[key($tab_decompose_nomfichier)]=strtolower(current($tab_decompose_nomfichier));
			$extension = '.'.strtolower(end($tab_decompose_nomfichier));
		}
		$query_rs=	"select nom, prenom, numdossierzrr".
								" from individu,individusejour".
								" where individu.codeindividu=individusejour.codeindividu".
								" and individu.codeindividu=".GetSQLValueString($codeindividu,"text").
								" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
		$rs=mysql_query($query_rs) or die(mysql_error());
		$row_rs=mysql_fetch_assoc($rs);
		foreach(array('cv'=>'CV','piece_identite'=>'identite','fsd_sujet'=>'programme de travail','fsd_financement'=>'financement') as $codelibpj=>$libelle)
		{ $updateSQL ="update individupj, typepjindividu set nomfichier=".GetSQLValueString($row_rs['numdossierzrr'].' - '.$libelle.$extension, "text").
								" where individupj.codeindividu=".GetSQLValueString($codeindividu,"text")." and individupj.numcatpj=".GetSQLValueString($numsejour, "text").
								" and individupj.codelibcatpj='sejour' and typepjindividu.codelibtypepj=".GetSQLValueString($codelibpj, "text")." and typepjindividu.codetypepj=individupj.codetypepj";
		 mysql_query($updateSQL);
		}
	}
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_confirmer_mail_validation_declaration_fsd)) 
{	$tab_champs_date=array('date_demande_fsd'  =>  array("lib" => "Date demande acc&egrave;s","jj" => "","mm" => "","aaaa" => ""));
	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date);
	$erreur=controle_form_confirmer_mail_validation_demande_fsd($_POST,$tab_controle_et_format);
	if($erreur=="")
	{	$affiche_succes=true;
		if(isset($_POST['b_valider_x']))
		{	$erreur_envoimail=mail_validation_declaration_fsd($codeindividu,$numsejour,$codeuser,$_POST);
			if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
			{ $warning="Echec d&rsquo;envoi du mail pour la demande fsd du dossier ".$codeindividu.".".$numsejour;
				$erreur="Validation non effectu&eacute;e.";
				$affiche_succes=false;
			}
			else
			{ $updateSQL ="update individusejour set date_demande_fsd=".GetSQLValueString(jjmmaaaa2date($_POST['date_demande_fsd_jj'],$_POST['date_demande_fsd_mm'],$_POST['date_demande_fsd_aaaa']), "text").
										" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
				
				$message_resultat_affiche="Envoi effectu&eacute;.";
				http_redirect('gestionindividus.php?ind_ancre='.$ind_ancre);
			}
		}
	}
}

//destinataires hors FSD
$tab_destinataires['expediteur'][1]=array('prenom'=>$tab_infouser['prenom'],'nom'=>$tab_infouser['nom'],'email'=>$tab_infouser['email']);
$query_rs_user= "select tel,fax,email,lieu.liblonglieu as liblieu from individu,lieu ".
								" where codeindividu=".GetSQLValueString($codeuser, "text").
								" and individu.codelieu=lieu.codelieu";
$rs_user=mysql_query($query_rs_user) or die(mysql_error());
$row_rs_user=mysql_fetch_assoc($rs_user);

// roles srh, admingestfin (provenant de) structure
$rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
								" where structureindividu.codestructure=structure.codestructure".
								" and (codelib=".GetSQLValueString('srh', "text")." or codelib=".GetSQLValueString('admingestfin', "text").")".
								" and structureindividu.estresp='oui'") or die(mysql_error());
$i=0;
while($row_rs = mysql_fetch_assoc($rs))
{ $i++;
	$tab_destinataires[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
	// 4/10/2016
	if($row_rs['codelib']=='srh')
	{ $tab_destinataires['srhue'][$i]=$tab_destinataires['srh'][$i];
	}
	// 4/10/2016
}
//4/10/2016 role srhue pour le role srh
/* if(array_key_exists('srh',$tab_destinataires))
{ list($i,$tab)=each($tab_destinataires['srh']);
	$tab_destinataires['srhue'][1]=$tab_destinataires['srh'][$i];
} */

$query_rs_individu=	"select civilite.libcourt_fr as libciv,if(nomjf='',nom,nomjf) as nompatronymique, prenom, date_demande_fsd,numdossierzrr,codegesttheme".
										" from individu,individusejour,civilite".
										" where individu.codeciv=civilite.codeciv and individu.codeindividu=individusejour.codeindividu".
										" and individu.codeindividu=".GetSQLValueString($codeindividu,"text").
										" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
//echo $query_rs_individu;										
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_individu=mysql_fetch_assoc($rs_individu);

$tab_destinataires['codegesttheme'][1]=get_info_user($row_rs_individu['codegesttheme']);

if($row_rs_individu['date_demande_fsd']=='')
{ $row_rs_individu['date_demande_fsd']=$aujourdhui;
}
$query_rs_individupj=	"select individupj.*,typepjindividu.* from individupj,typepjindividu".
											" where individupj.codetypepj=typepjindividu.codetypepj".
											" and individupj.codelibcatpj=typepjindividu.codelibcatpj".
											" and individupj.codelibcatpj=".GetSQLValueString('sejour', "text").
											" and (typepjindividu.codelibtypepj=".GetSQLValueString('cv', "text").
											"			 or typepjindividu.codelibtypepj=".GetSQLValueString('piece_identite', "text").
											"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_sujet', "text").
											"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_financement', "text").
											"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd', "text").")".
											" and individupj.numcatpj=".GetSQLValueString($numsejour, "text").
											" and codeindividu=".GetSQLValueString($codeindividu, "text") or die(mysql_error());
$rs_individupj=mysql_query($query_rs_individupj);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Demande autorisation FSD</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>
>
<table border="0" align="center" cellpadding="0" cellspacing="1">
 <form name="<?php echo $form_confirmer_mail_validation_declaration_fsd ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="return controle_form_confirmer_mail_validation_declaration_fsd('<?php echo $form_confirmer_mail_validation_declaration_fsd ?>')">
  <input type="hidden" name="MM_update" value="<?php echo $form_confirmer_mail_validation_declaration_fsd ?>">
   <input type="hidden" name="codeindividu" value="<?php echo $codeindividu; ?>">
  <input type="hidden" name="numsejour" value="<?php echo $numsejour; ?>">
 	<input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre; ?>">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Demande FSD','lienretour'=>'gestionindividus.php?ind_ancre='.$ind_ancre,'texteretour'=>'Retour &agrave; la gestion des dossiers des personnels',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'dossier '.$codeindividu.'.'.$numsejour,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
	<tr>
    <td align="center">
    	<table>
        <tr>
          <td align="left" nowrap class="noircalibri10">
            <table class="table_rectangle_bleu" width="80%">
              <tr>
                <td align="center" class="noircalibri10"><b>Mail FSD</b></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              <tr>
                <td align="left" class="noircalibri10"><b>Destinataire : </b><?php echo $GLOBALS['fsd_contact']['prenomnom'] ?> <?php echo $GLOBALS['fsd_contact']['email'] ?></td>
              </tr>
              <tr>
                <td align="left" class="noircalibri10"><b>Copie : </b>
                <?php $first=true;
                foreach($tab_destinataires as $un_role=>$tab_un_role)
                { foreach($tab_un_role as $i=>$tab_un_destinataire)
                  if(!in_array($tab_un_destinataire['email'],$tab_mail_unique))
                  { $tab_mail_unique[]=$tab_un_destinataire['email'];
                    echo ($first?"":", ").$tab_un_destinataire['prenom'].' '.$tab_un_destinataire['nom'].' '.$tab_un_destinataire['email'];
                  }
                  $first=false;
                }?>
                </td>
              </tr>
              <tr>
                <td align="left" class="noircalibri10"><b>Objet</b> : Protection du Potentiel Scientifique et Technique de la Nation - <?php echo $GLOBALS['acronymelabo'] ?>
                                                                  (<?php echo $row_rs_individu['numdossierzrr']." - ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nompatronymique'] ?>)
                </td>
              </tr>
              <tr>
                <td align="left" class="noircalibri10">Madame, Monsieur,
                <br><br>Je vous prie de bien vouloir trouver ci-joint, le formulaire de demande accompagn&eacute; du CV, de la pi&egrave;ce d&rsquo;identit&eacute;, du sujet et des &eacute;l&eacute;ments de financement concernant <b><?php echo $row_rs_individu['libciv']." ".$row_rs_individu['prenom'].' '.strtoupper($row_rs_individu['nompatronymique']) ?></b>
                <br><br>Restant &agrave; votre disposition pour tout compl&eacute;ment d'information, veuillez agr&eacute;er mes salutations distingu&eacute;es.
                <br><br><?php echo $tab_infouser['prenom'].' '.$tab_infouser['nom'] ?>
                <br>--
                <br><?php echo construitliblabo(array('appel'=>'confirmer_mail_validation_declaration_fsd')) ?>
                <br><?php echo $row_rs_user['liblieu'] ?>
                <br>T&eacute;l. : <?php echo $row_rs_user['tel'] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax : <?php echo $row_rs_user['fax'] ?>
                <br>Mail : <?php echo $row_rs_user['email'] ?>
                </td>
              </tr>
              <?php
							// 19022017
							$rs=mysql_query("select * from _accent_sans_accent");
							$row_rs=mysql_fetch_assoc($rs);
							$liste_accent=$row_rs['accent'];
							$liste_sans_accent=$row_rs['sans_accent'];
              if(mysql_num_rows($rs_individupj)>=1)
              {?> 
              <tr>
                <td>
                  <table>
                    <tr>
                      <td align="left" nowrap class="noircalibri10">Pi&egrave;ces jointes :
                      <?php 
                      while($row_rs_individupj=mysql_fetch_assoc($rs_individupj))
                      { ?><a href="download.php?codeindividu=<?php echo $codeindividu ?>&codelibcatpj=sejour&numcatpj=<?php echo $row_rs_individupj['numcatpj'] ?>&codetypepj=<?php echo $row_rs_individupj['codetypepj'] ?>" target="_blank" title="T&eacute;l&eacute;charger <?php echo strtr($row_rs_individupj['nomfichier'],$liste_accent,$liste_sans_accent) ?> (<?php echo $row_rs_individupj['libtypepj'] ?>)">
                          <img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10"><?php echo $row_rs_individupj['libtypepj'] ?></span></a>
                      <?php 
                      }?>
                      </td>
                    </tr>
                  </table>
                </td>
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
    <td align="center"><span class="bleugrascalibri10">Date d'envoi de la demande :&nbsp;</span>
      <input name="date_demande_fsd_jj" type="text" class="noircalibri10" id="date_demande_fsd_jj" value="<?php echo substr($row_rs_individu['date_demande_fsd'],8,2); ?>" size="2" maxlength="2">
      <input name="date_demande_fsd_mm" type="text" class="noircalibri10" id="date_demande_fsd_mm" value="<?php echo substr($row_rs_individu['date_demande_fsd'],5,2); ?>" size="2" maxlength="2">
      <input name="date_demande_fsd_aaaa" type="text" class="noircalibri10" id="date_demande_fsd_aaaa" value="<?php echo substr($row_rs_individu['date_demande_fsd'],0,4); ?>" size="4" maxlength="4">
		</td>
  </tr>
  <tr>
    <td align="center">
      <table>
        <tr>
          <td>
        <input type="image" name="b_valider" class="icon" src="images/b_confirmer.png">
        </form>
        
        <form method="get" action="gestionindividus.php">
 					<input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre; ?>">
          <input type="image" name="annuler" class="icon" src="images/b_annuler.png">
        </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php
if(isset($rs_individupj)) mysql_free_result($rs_individupj);
if(isset($rs_individu)) mysql_free_result($rs_individu);
if(isset($rs_user)) mysql_free_result($rs_user);
if(isset($rs)) mysql_free_result($rs);
?>
