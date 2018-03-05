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
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche='';
$form_dupliquer_commande = "dupliquer_commande";
$_SESSION['codecommande_a_dupliquer']=isset($_GET['codecommande'])?$_GET['codecommande']:(isset($_POST['codecommande'])?$_POST['codecommande']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");
$tab_champs_date=array( 'datecommande' =>  array("lib" => "Date de commande","jj" => "","mm" => "","aaaa" => ""));

$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$radiomissionliee=isset($_POST['radiomissionliee'])?$_POST['radiomissionliee']:"nonliee";
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

//PG 20151117
// protection contre une erreur qui modifierait l'enreg. ''
if($_SESSION['codecommande_a_dupliquer']=='')
{ $erreur.="Tentative de duplication de commande sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de commande sans n&deg; '.$msg);
}
//PG 20151117

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_dupliquer_commande)) 
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date);
	$erreur.=controle_form_dupliquer_commande($_POST,$tab_controle_et_format);
	//$erreur='erreur forcée';
 	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Duplication effectu&eacute;e avec succ&egrave;s.';
		mysql_query("START TRANSACTION") or  die(mysql_error());
		$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='commande'") or  die(mysql_error());
		$row_seq_number=mysql_fetch_assoc($rs_seq_number);
		$codecommande=$row_seq_number['currentnumber'];
		$codecommande=str_pad((string)((int)$codecommande+1), 5, "0", STR_PAD_LEFT);  
		$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecommande, "text")." where nomtable='commande'") or  die(mysql_error());
		mysql_query("COMMIT") or  die(mysql_error());
		mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
		foreach(array('commande','commandeimputationbudget') as $table)
		{ $rs_commande_a_dupliquer=mysql_query("SELECT * FROM ".$table." WHERE codecommande=".GetSQLValueString($_SESSION['codecommande_a_dupliquer'],"text")) or  die(mysql_error());
			while($row_rs_commande_a_dupliquer=mysql_fetch_assoc($rs_commande_a_dupliquer))
			{ $rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
				$first=true;
				$liste_champs="";$liste_val="";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
					$liste_val.=($first?"":",");
					$first=false;
					if($row_rs_fields['Field']=='codecommande')
					{ $liste_val.=GetSQLValueString($codecommande, "text");
					}
					else if($row_rs_fields['Field']=='codereferent')
					{ $liste_val.=GetSQLValueString($_POST['codereferent'], "text");
					}
					else if($row_rs_fields['Field']=='libfournisseur')
					{ $liste_val.=GetSQLValueString($_POST['libfournisseur'], "text");
					}
					else if($row_rs_fields['Field']=='datecommande')
					{ $liste_val.=GetSQLValueString(jjmmaaaa2date($_POST['datecommande_jj'],$_POST['datecommande_mm'],$_POST['datecommande_aaaa']), "text");
					}
					else if($row_rs_fields['Field']=='codemission')
					{ if($radiomissionliee=='nonliee')
						{ $liste_val.="''";
						}
						else if($radiomissionliee=='lieealamememission')
						{ $liste_val.=GetSQLValueString($row_rs_commande_a_dupliquer['codemission'], "text");
						}
						else
						{ $liste_val.=GetSQLValueString($codemission, "text");
						}
					}
					/* else if($row_rs_fields['Field']=='numcommande')
					{ $liste_val.="''";
					} */
					/* else if($row_rs_fields['Field']=='codevisaannulemax')
					{ $liste_val.="''";
					} */
					else if(in_array($row_rs_fields['Field'],array('numcommande','codevisaannulemax','dateenvoi_etatfrais')))
					{ $liste_val.="''";
					}
					else if(($row_rs_fields['Field']=='montantengage' || $row_rs_fields['Field']=='montantpaye') && $row_rs_commande_a_dupliquer['virtuel_ou_reel']=='1')
					{ $liste_val.="''";
					} 
					else
					{ $liste_val.=GetSQLValueString($row_rs_commande_a_dupliquer[$row_rs_fields['Field']], "text");
					}
				}//fin while
				$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
				//echo '<br>'.$updateSQL;
				mysql_query($updateSQL) or  die(mysql_error());
				$codereferent='';//en cas de succes le referent est remis a ''
				$cmd_ancre='C'.$codecommande;
			}
		}
	}
}
 
 
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations du mission (un enreg. vide dans mission pour "creer")
$query_commande="select * from commande,individu".
								" where codecommande=".GetSQLValueString($_SESSION['codecommande_a_dupliquer'],"text").
								" and commande.codereferent=individu.codeindividu";
$rs_commande=mysql_query($query_commande) or die(mysql_error());
$row_rs_commande=mysql_fetch_assoc($rs_commande);
if($erreur=='')
{	$row_rs_commande['codereferent']='';
	$row_rs_commande['libfournisseur']='';
	$row_rs_commande['datecommande']=date("Y").'/'.date("m").'/'.date("d");
}
else//valeurs du POST a la place de certaines données de mission qui n'ont pas été mises a jour.
{ $row_rs_commande['codereferent']=$_POST['codereferent'];
	$row_rs_commande['libfournisseur']=$_POST['libfournisseur'];
	$row_rs_commande['datecommande']=$_POST['datecommande_aaaa'].'/'.$_POST['datecommande_mm'].'/'.$_POST['datecommande_jj'];
	if($radiomissionliee=='nonliee')
	{ $row_rs_commande['codemission']='';
	}
	/* else if($radiomissionliee=='lieeautremission')
	{ $row_rs_commande['codemission']=$codemission;
	} */
}
// Liste des membres
$query_rs_referent="SELECT distinct individu.codeindividu as codereferent,nom,prenom,concat(nom,' ',substr(prenom,1,1)) as nomprenom". 
									 " FROM individu,individusejour,corps".
									 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
									 " UNION select '' as codereferent,'' as nom,'' as prenom, '' as nomprenom from individu where codeindividu=''".
									 " ORDER BY nom,prenom";
$rs_referent=mysql_query($query_rs_referent) or die(mysql_error());
// Liste missions
$rs=mysql_query("SELECT codemission,nom,prenom,motif from mission".
								" order by codemission") or die(mysql_error());
$nbmission=mysql_num_rows($rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_mission[$row_rs['codemission']]=$row_rs;
}
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des commandes <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script language="javascript">
var frm=document.forms["<?php echo $form_dupliquer_commande ?>"];
var tab_mission=new Array();
<?php
foreach($tab_mission as $codemission=>$une_mission)
{?>
	tab_mission["<?php echo $codemission ?>"]=new Array();
	tab_mission["<?php echo $codemission ?>"]["motif"]="<?php echo js_tab_val($codemission.' '.$une_mission["motif"]);?>";
	<?php 
}?>
function recherche_codemission(lib)
{ var codemissiontrouve='';
	for (var codemission in tab_mission)
	{ if(tab_mission[codemission]['motif']==lib) 
		{ codemissiontrouve=codemission
		}
	}
	return codemissiontrouve;
}

function checkradio(radio) 
{ for (var i=0; i<radio.length;i++) 
	{ if(radio[i].value=='lieeautremission')
		{ radio[i].checked=true;
		}
	}
}
</script>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?"\\n":'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>
<form name="<?php echo $form_dupliquer_commande ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="return controle_form_dupliquer_commande('<?php echo $form_dupliquer_commande ?>')">
<input type="hidden" name="codecommande" value="<?php echo $_SESSION['codecommande_a_dupliquer'] ?>" >
<input type="hidden" name="MM_update" value="<?php echo $form_dupliquer_commande ?>">
<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Duplication de Commande','lienretour'=>'gestioncommandes.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Retour &agrave; la gestion des commandes',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;
    </td>
  </tr>
  <tr>
    <td align="center"><span class="bleugrascalibri11">Duplication de la commande n&deg; </span> <span class="noirgrascalibri11"><?php echo $row_rs_commande['codecommande']; ?> : </span><span class="noircalibri11"><?php echo $row_rs_commande['objet']; ?></span></td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td align="left"><span class="mauvecalibri10">Le n&deg; de commande et les informations relatives aux services faits (MIGO) et aux liquidations ne sont pas dupliqu&eacute;s. </span></td>
  </tr>
  <tr>
    <td align="center">&nbsp;</td>
  </tr>
  <tr>
    <td>     
      <table width="100%" border="0" class="table_cadre_arrondi">
        <tr>
          <td valign="top" nowrap>
            <table border="0">
              <tr>
                <td class="bleucalibri11">Demandeur<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                <td><select name="codereferent" class="noircalibri10" id="codereferent" >
                  <?php
                  while($row_rs_referent=mysql_fetch_assoc($rs_referent))
                  { ?>
                  <option value="<?php echo $row_rs_referent['codereferent'] ?>" <?php echo ($row_rs_referent['codereferent']==$row_rs_commande['codereferent'] ?'selected':'') ?>><?php echo $row_rs_referent['nomprenom'] ?></option>
                  <?php
                  } 
                  ?>
                </select>
                </td>
              </tr>
              <tr>
                <td class="bleucalibri11">Fournisseur<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                <td><input name="libfournisseur"  type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['libfournisseur'])  ?>" size="80" maxlength="200"></td>
              </tr>
              <tr>
                <td nowrap><span class="bleucalibri11">Date de la commande : </span>
                </td>
                <td nowrap>
                  <input name="datecommande_jj" type="text" class="noircalibri10" id="datecommande_jj" value="<?php echo substr($row_rs_commande['datecommande'],8,2); ?>" size="2" maxlength="2">
                  <input name="datecommande_mm" type="text" class="noircalibri10" id="datecommande_mm" value="<?php echo substr($row_rs_commande['datecommande'],5,2); ?>" size="2" maxlength="2">
                  <input name="datecommande_aaaa" type="text" class="noircalibri10" id="datecommande_aaaa" value="<?php echo substr($row_rs_commande['datecommande'],0,4); ?>" size="4" maxlength="4">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td valign="top">
            <table border="0">
              <tr>
                <td colspan="2" class="noircalibri11">La commande dupliqu&eacute;e est :
                </td>
              </tr>
              <tr><?php $missionliee=($row_rs_commande['codemission']!='');?>
                <td><input type="radio" name="radiomissionliee" value="nonliee" <?php echo $missionliee?'':'checked' ?>></td>
                <td class="noircalibri11">Non li&eacute;e &agrave; une mission
                </td>
              </tr>
              <?php 
              if($missionliee)
              { $une_mission=$tab_mission[$row_rs_commande['codemission']];?>
              <tr>
                <td><input type="radio" name="radiomissionliee"  value="lieealamememission" checked></td>
                <td class="noircalibri11">Li&eacute;e &agrave; la mission : <?php echo $row_rs_commande['codemission'].' '.htmlspecialchars($tab_mission[$row_rs_commande['codemission']]['motif'])  ?>
                </td>
              </tr>
              <?php 
              }?>
              <tr>
                <td class="noircalibri11"><input type="radio" name="radiomissionliee" value="lieeautremission">Li&eacute;e &agrave; :&nbsp;</td>
                <td class="noircalibri11">
                  <select name="codemission" class="noircalibri10" id="codemission" onChange="checkradio(document.forms['<?php echo $form_dupliquer_commande?>'].elements['radiomissionliee'])" >
                    <?php
                    foreach($tab_mission as $un_codemission=>$une_mission)
                    { ?>
                  <option value="<?php echo $un_codemission;?>"><?php echo $un_codemission==""?"":(int)$un_codemission.' - '.addslashes($une_mission['nom']).' - '.addslashes($une_mission['motif']) ?></option>
                    <?php 
                    }?>
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center">
      <table>
        <tr>
          <td><input name="submit_dupliquer" type="submit" class="noircalibri10" id="submit_dupliquer" value="Dupliquer">
          </td>
              </form>
            <form method="post" action="gestioncommandes.php">
          <td>
            <input name="submit_quitter" type="submit" class="noircalibri10" id="submit_quitter" value="Quitter">
						<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
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
if(isset($rs_commande_a_dupliquer))mysql_free_result($rs_commande_a_dupliquer);
if(isset($rs_commande))mysql_free_result($rs_commande);
if(isset($rs_referent))mysql_free_result($rs_referent);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
