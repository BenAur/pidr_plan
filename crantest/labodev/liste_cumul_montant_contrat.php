<?php require_once('_const_fonc.php'); ?>
<?php 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_cmd_statutvisa=get_cmd_statutvisa();
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,false,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$peut_etre_admin=estrole('sif',$tab_roleuser) || estrole('admingestfin',$tab_roleuser) || estrole('du',$tab_roleuser);
// un eotp peut couvrir plus d'un contrat
$query_rs=	"SELECT budg_eotp_source_vue.codeeotp as codeeotp, distinguermasse, sum(montantfonctionnement) as montantfonctionnement,".
					" sum(montantinvestissement) as montantinvestissement, sum(montantsalaire) as montantsalaire, eotp_ou_source,".
					" centrecout.libcourt as libcentrecout".
					" FROM centrecout, budg_eotp_source_vue".
					" left join eotp_source_montant on budg_eotp_source_vue.codeeotp=eotp_source_montant.codeeotp".
					" where budg_eotp_source_vue.codecentrecout=centrecout.codecentrecout".
					" and budg_eotp_source_vue.codeeotp<>''".
					" group by budg_eotp_source_vue.codeeotp";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))				
{ $tab_eotp_source[$row_rs['codeeotp']]=$row_rs;
	$tab_eotp_source[$row_rs['codeeotp']]['noterecette']='';
}
$query_rs="SELECT codeeotp,note from eotp_source_montant";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))				
{ $tab_eotp_source[$row_rs['codeeotp']]['noterecette'].=$row_rs['note'].'<br>';
}

$query_rs="SELECT budg_contrat_source_vue.codecontrat,budg_eotp_source_vue.codeeotp".
					" from  budg_contrateotp_source_vue, budg_eotp_source_vue,budg_contrat_source_vue".
					" where budg_contrat_source_vue.codecontrat=budg_contrateotp_source_vue.codecontrat and budg_contrateotp_source_vue.codeeotp=budg_eotp_source_vue.codeeotp".
					" order by budg_contrat_source_vue.codecontrat"; 
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contrateotp[$row_rs['codecontrat']][$row_rs['codeeotp']]=$row_rs['codeeotp'];
}
// montants par contrat/commandeimputationbudget (par numordre au cas ou un contrat aurait eu 2 imputations sur une commande par erreur)
// nb d'imputations de chaque commande
$query_rs = "SELECT budg_contrat_source_vue.codecontrat,commande.codecommande,commandeimputationbudget.numordre, commandeimputationbudget.codecentrecout, montantengage, montantpaye,commandestatutvisa.codestatutvisa, estavoir".
						" from budg_contrat_source_vue, commande, commandeimputationbudget".
						" left join commandestatutvisa on (commandeimputationbudget.codecommande=commandestatutvisa.codecommande and commandestatutvisa.codestatutvisa='06')".
						" where budg_contrat_source_vue.codecontrat=commandeimputationbudget.codecontrat and commandeimputationbudget.virtuel_ou_reel='0'".
						" and commandeimputationbudget.codecommande=commande.codecommande and estannule='non'".
						" and budg_contrat_source_vue.codecontrat<>''".
						" order by budg_contrat_source_vue.codecontrat,commandeimputationbudget.codecommande,commandeimputationbudget.numordre";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_commande_nb_imputation=array();
while($row_rs=mysql_fetch_assoc($rs))				
{ $tab_commandeimputationbudget[$row_rs['codecontrat']][$row_rs['codecommande']][$row_rs['numordre']]=$row_rs;
	if(isset($tab_commande_nb_imputation[$row_rs['codecommande']]))
	{ $tab_commande_nb_imputation[$row_rs['codecommande']]++;
	}
	else
	{ $tab_commande_nb_imputation[$row_rs['codecommande']]=1;
	}
}

$tab_contrat=array();
// liste des contrats
// un contrat peut couvrir plusieurs eotp
$query_rs_contrat = "SELECT budg_contrat_source_vue.codecontrat,budg_contrat_source_vue.codecentrecout,budg_contrat_source_vue.total_depense_anterieur,budg_contrat_source_vue.montant_ht,".
										" centrecout.libcourt as libcentrecout, centrefinancier.libcourt as libcentrefinancier,typecredit.codetypecredit,typecredit.libcourt as libtypecredit,".
										" cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
										" individu.nom, individu.prenom, acronyme as libcontrat,coderespscientifique,contrat_ou_source".
										" from centrecout,centrefinancier,typecredit,individu,cmd_typesource, budg_contrat_source_vue".
										" where budg_contrat_source_vue.codecontrat<>''".
										" and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource".
										" and budg_contrat_source_vue.coderespscientifique=individu.codeindividu".
										" and centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout".
										" and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
										" and centrefinancier.codetypecredit=typecredit.codetypecredit".
										" and ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$GLOBALS['date_deb_exercice_comptable']."'","'".$GLOBALS['date_fin_exercice_comptable']."'").
										" and ((contrat_ou_source='contrat' and acronyme<>'') or contrat_ou_source='source')".
										" and (budg_contrat_source_vue.coderespscientifique=".GetSQLValueString($codeuser, "text").// Resp contrat ou resp theme ou peut_etre_edmin (sif, du)
										"			 or (budg_contrat_source_vue.codecentrecout in (select codecentrecout from centrecouttheme,structureindividu where centrecouttheme.codestructure=structureindividu.codestructure
																												 and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").
																												")".
															")".
										"				or ".($peut_etre_admin?'1':'0').
													")".
										" order by typecredit.libcourt asc, centrefinancier.libcourt, centrecout.libcourt,individu.nom";
$rs_contrat=mysql_query($query_rs_contrat) or die(mysql_error());
$au_moins_un_distinguermasse=false;
while($row_rs_contrat=mysql_fetch_assoc($rs_contrat))
{ $codecontrat=$row_rs_contrat['codecontrat'];
	$tab_contrat[$codecontrat]=$row_rs_contrat;
	$sommemontantengage=0;
	$sommemontantpaye=0;
	if(isset($tab_commandeimputationbudget[$codecontrat]))
	{ $tab_contratcommandeimputation=$tab_commandeimputationbudget[$codecontrat];
		foreach($tab_contratcommandeimputation as $codecommande=>$un_tab_contratcommandeimputation)
		{ foreach($un_tab_contratcommandeimputation as $numordre=>$un_tab_numordrecontratcommandeimputation)
			{ // on prend le montant payé si visa 06 (IEB2) et s'il n'y a q'une imputation virtuelle
				if($un_tab_numordrecontratcommandeimputation['codestatutvisa']=='06' && $tab_commande_nb_imputation[$codecommande]==1/* count($un_tab_contratcommandeimputation)==1 */)
				{ $sommemontantengage+=$un_tab_numordrecontratcommandeimputation['montantpaye'];
				}
				else
				{ $sommemontantengage+=$un_tab_numordrecontratcommandeimputation['montantengage'];
				}
			}
		}
	}
	$tab_contrat[$codecontrat]['depense']=sprintf('%01.2f',round($sommemontantengage,2));
	$tab_contrat[$codecontrat]['total_depense_anterieur']=($row_rs_contrat['total_depense_anterieur']==''?'':sprintf('%01.2f',$row_rs_contrat['total_depense_anterieur']));
	$tab_contrat[$codecontrat]['montant_ht']=$row_rs_contrat['montant_ht']==''?'':sprintf('%01.2f',$row_rs_contrat['montant_ht']);
	$montantfonctionnement=0;
	$montantinvestissement=0;
	$montantsalaire=0;
	$recette=0;
	// cumul des montants des eotp
	// si plus d'un eotp dans un contrat
	$tab_contrat[$codecontrat]['distinguermasse']='non'; 
	$tab=isset($tab_contrateotp[$codecontrat])?$tab_contrateotp[$codecontrat]:array();
	$tab_contrat[$codecontrat]['noterecette']='';			
	foreach($tab as $codeeotp)
	{ if(isset($tab_eotp_source[$codeeotp]))
		{ $montantfonctionnement+=$tab_eotp_source[$codeeotp]['montantfonctionnement'];
			$montantinvestissement+=$tab_eotp_source[$codeeotp]['montantinvestissement'];
			$montantsalaire+=$tab_eotp_source[$codeeotp]['montantsalaire'];
			//if($codecontrat=='00346'){echo '<br>'.$codecontrat.' '. $row_rs_contrat['libcontrat'].' eotp : '.$codeeotp.' fonctionnement: '.$tab_eotp_source[$codeeotp]['montantfonctionnement'].' '.$montantfonctionnement.' recette : '.$recette;}
			if($tab_eotp_source[$codeeotp]['distinguermasse']=='oui')
			{ $au_moins_un_distinguermasse=true;
			}
			$tab_contrat[$codecontrat]['distinguermasse']=$tab_eotp_source[$codeeotp]['distinguermasse'];
			$tab_contrat[$codecontrat]['noterecette']=$tab_eotp_source[$codeeotp]['noterecette'];			
		}
	}
	$recette=$montantfonctionnement+$montantinvestissement+$montantsalaire;
	$tab_contrat[$codecontrat]['montantfonctionnement']=sprintf('%01.2f',round($montantfonctionnement,2));
	$tab_contrat[$codecontrat]['montantinvestissement']=sprintf('%01.2f',round($montantinvestissement,2));
	$tab_contrat[$codecontrat]['montantsalaire']=sprintf('%01.2f',round($montantsalaire,2));
	$tab_contrat[$codecontrat]['recette']=sprintf('%01.2f',round($recette,2));
	$tab_contrat[$codecontrat]['solde']=sprintf('%01.2f',round($tab_contrat[$codecontrat]['recette']-$tab_contrat[$codecontrat]['depense'],2));
	if($row_rs_contrat['contrat_ou_source']=='contrat')
	{ $tab_contrat[$codecontrat]['libcontrat']=$row_rs_contrat['nom'].' '.substr($row_rs_contrat['prenom'],0,1).'. - '.$row_rs_contrat['libcontrat'];
	}
	else // source
	{ $tab_construitsource=array(	'codetypesource'=>$row_rs_contrat['codetypesource'],'libtypesource'=>$row_rs_contrat['libtypesource'],'libsource'=>$row_rs_contrat['libcontrat'],
																										'coderespscientifique'=>$row_rs_contrat['coderespscientifique'],'nomrespscientifique'=>$row_rs_contrat['nom'],
																										'prenomrespscientifique'=>$row_rs_contrat['prenom'],'codetypecredit'=>$row_rs_contrat['codetypecredit']);
		$tab_contrat[$codecontrat]['libcontrat']=construitlibsource($tab_construitsource);
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>D&eacute;penses/Recettes/solde par contrat</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
</head>
<body class="noircalibri11">
<table align="center" border="0" cellpadding="3">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Gestion budg&eacute;taire','lienretour'=>'','texteretour'=>'',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
	<tr>
		<td align="left">&nbsp;</td>
	</tr>
	<tr>
  	<td>
    	<table align="center" cellspacing="2" cellpadding="3">
        <tr class="head">
        <td colspan="<?php echo $au_moins_un_distinguermasse?9:6?>" class="bleugrascalibri11">D&eacute;penses/recettes/solde <?php echo substr($GLOBALS['date_deb_exercice_comptable'],0,4); ?> 
        </td>
        <td colspan="2" align="center" class="noirgrascalibri11">Pour M&eacute;moire
        </td>
        <td></td>
        </tr>
        <tr class="head">
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Cr&eacute;dits
          </td>
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Enveloppe
          </td>
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Source/Contrat
          </td>
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Engag&eacute;/Pay&eacute;
          </td>
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Solde exercice <?php echo substr($GLOBALS['date_deb_exercice_comptable'],0,4); ?>
          </td>
          <td align="center" valign="top" class="bleugrascalibri11" nowrap>Recettes exercice en cours +<br>Reste &agrave; d&eacute;penser<br>ann&eacute;e(s) ant&eacute;rieure(s)
          </td>
          <?php 
					if($au_moins_un_distinguermasse)
          {?><td align="center" valign="top" class="bleugrascalibri11" nowrap>Fonctionnement
						</td>
						<td align="center" valign="top" class="bleugrascalibri11" nowrap>Investissement
						</td>
						<td align="center" valign="top" class="bleugrascalibri11" nowrap>Salaires
						</td>
          <?php 
					}?>
          <td align="center" valign="top" class="noirgrascalibri11" nowrap>Total d&eacute;penses<br>ann&eacute;es ant&eacute;rieures<br>(depuis <?php echo substr($GLOBALS['date_deb_12plus_budget'],0,4); ?>) 
          </td>
          <td align="center" valign="top" class="noirgrascalibri11" nowrap>Total recettes<br>pr&eacute;vues du contrat
          </td>
          <td align="center" valign="top" class="noirgrascalibri11" nowrap>Notes recettes
          </td>
        </tr>
        <?php 
				foreach($tab_contrat as $codecontrat=>$un_tab_contrat)
        {?> <tr class="<?php echo $class=="even"?$class="odd":$class="even" ?>">
            <td align="center"><?php echo $un_tab_contrat['libtypecredit'] ?>
            </td>
            <td align="left"><?php echo $un_tab_contrat['libcentrecout'] ?>
            </td>
            <td align="left"><?php echo $un_tab_contrat['libcontrat'] ?>
            </td>
            <td align="right"><?php echo $un_tab_contrat['depense'] ?>
            </td>
						<td align="right"><?php echo $un_tab_contrat['solde'] ?>
            </td>          
            <td align="right"><?php echo $un_tab_contrat['recette'] ?>
            </td>            
            <?php 
						if($au_moins_un_distinguermasse)
						{?>
            <td align="right" valign="top" nowrap><?php echo $tab_contrat[$codecontrat]['distinguermasse']=='oui'?$tab_contrat[$codecontrat]['montantfonctionnement']:''?>
            </td>
            <td align="right" valign="top" nowrap><?php echo $tab_contrat[$codecontrat]['distinguermasse']=='oui'?$tab_contrat[$codecontrat]['montantinvestissement']:''?>
            </td>
            <td align="right" valign="top" nowrap><?php echo $tab_contrat[$codecontrat]['distinguermasse']=='oui'?$tab_contrat[$codecontrat]['montantsalaire']:''?>
            </td>
						<?php 
						}?>
            <td align="right"><?php echo $tab_contrat[$codecontrat]['total_depense_anterieur'] ?>
          	</td>
          	<td align="right"><?php echo $tab_contrat[$codecontrat]['montant_ht'] ?>
            </td>          
          	<td align="left" nowrap><?php echo $tab_contrat[$codecontrat]['noterecette'] ?>
            </td>          

          </tr>
        <?php 
        }?>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php 
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_contrat)) mysql_free_result($rs_contrat);
if(isset($rs_resptheme)) mysql_free_result($rs_resptheme);

?>