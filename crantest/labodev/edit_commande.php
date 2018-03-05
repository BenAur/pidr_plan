<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
/*if($admin_bd)
{foreach($_REQUEST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}
}*/ 
$aujourdhui=date('Ymd');
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur)
// 20170427
$msg_erreur_objet_mail="";
$erreur_envoimail="";
// 20170427
$tab_contexte['prog']='edit_commande';
$tab_contexte['codeuser']=$codeuser;
$tab_contexte['groupe']='pole_gestion_fst';
$message_resultat_affiche='';
$tab_commandejustifiecontrat=array();
$tab_champs_numerique['rubriquecomptable']=array('lib' => 'Rubrique comptable');
$tab_champs_date=array( 'datecommande' =>  array("lib" => "Date de commande","jj" => "","mm" => "","aaaa" => ""),'dateenvoi_etatfrais' =>  array("lib" => "Date envoi de l&eacute;tat de frais","jj" => "","mm" => "","aaaa" => ""));
$tab_champs_ouinon_defaut=array('estannule'=>'non','estavoir'=>'non');
$tab_champs_ouinon=array();// champs oui non effectivement envoyes lors de la saisie d'une commande

$tab_commandeimputationbudget=array();
$nbimputationbudget['0']=isset($_GET['nbimputationbudget_virtuel'])?$_GET['nbimputationbudget_virtuel']:(isset($_POST['nbimputationbudget_virtuel'])?$_POST['nbimputationbudget_virtuel']:1);
$nbimputationbudget['1']=isset($_GET['nbimputationbudget_reel'])?$_GET['nbimputationbudget_reel']:(isset($_POST['nbimputationbudget_reel'])?$_POST['nbimputationbudget_reel']:1);

foreach(array('0','1') as $virtuel_ou_reel)
{ for($i=1;$i<=$nbimputationbudget[$virtuel_ou_reel];$i++)
	{ $numimputationbudget=str_pad($i,2,'0',STR_PAD_LEFT);
		$tab_champs_numerique['montantengage#'.$virtuel_ou_reel.'##'.$numimputationbudget]=array('lib' => 'Montant engag&eacute;','string_format'=>'%01.2f');
		$tab_champs_numerique['montantpaye#'.$virtuel_ou_reel.'##'.$numimputationbudget]=array('lib' => 'Montant pay&eacute;','string_format'=>'%01.2f');
	}
}
$codecommande=isset($_GET['codecommande'])?$_GET['codecommande']:(isset($_POST['codecommande'])?$_POST['codecommande']:"");
// $action="creer, "modifier", 'valider', 'valider_mail_visa_revalide'
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");
$montantpaye_automatique=(isset($_POST['montantpaye_automatique'])?$_POST['montantpaye_automatique']:"");// champ hidden = valeur qu'a montantpaye_automatique
$estcommandedecongres=isset($_GET['estcommandedecongres'])?$_GET['estcommandedecongres']:(isset($_POST['estcommandedecongres'])?$_POST['estcommandedecongres']:"");
// 20170427
$codevisa_a_apposer=isset($_GET['codevisa_a_apposer'])?$_GET['codevisa_a_apposer']:(isset($_POST['codevisa_a_apposer'])?$_POST['codevisa_a_apposer']:"");
$envoyer_mail_srh=isset($_GET['envoyer_mail_srh'])?true:(isset($_POST['envoyer_mail_srh'])?true:false);
// 20170427
// protection contre une erreur qui modifierait l'enreg. ''
if($action!='creer' && $codecommande=='')
{	$erreur.="Tentative de modification de commande sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de commande sans n&deg; '.$msg);
}
//PG 20151117

$est_cmd_de_miss=false;
if($codemission!='')
{ $est_cmd_de_miss=true;
}

$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=true;//porte la double information 'est un contrat' et 'est resp contrat'
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,$codecommande,$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);

$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
// 20170427
//$peut_etre_admin=estrole('sif',$tab_roleuser) || $_SESSION['b_cmd_etre_admin'];
// 20170427
$visa_theme_ou_contrat_appose=false;

if(array_key_exists('theme',get_cmd_visas($codecommande,$tab_cmd_statutvisa)) || array_key_exists('contrat',get_cmd_visas($codecommande,$tab_cmd_statutvisa)))
{ $visa_theme_ou_contrat_appose=true;
}
// 20170412
$visa_secrsite_appose=false;
if(array_key_exists('secrsite',get_cmd_visas($codecommande,$tab_cmd_statutvisa)))
{ $visa_secrsite_appose=true;
}
// 20170412
$visa_sif_appose=false;
if(array_key_exists('sif#1',get_cmd_visas($codecommande,$tab_cmd_statutvisa)))
{ $visa_sif_appose=true;
}
$form_commande = "form_commande";
$supprimer_une_pj=false;// $supprimer_une_pj=true si $submit='submit_supprimer_une_pj'
$submit="";$submit_val="";
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $submit=substr($postkey,0,$posdoublediese);
			$posdiese=strpos($submit,"#");
			if($posdiese!=false)
			{ $submit_val=substr($submit,$posdiese+1);//peut etre un numero ou autre (oui/non)
				$submit=substr($submit,0,$posdiese);
			}
		}
	}
}
//case a cocher submit_montantpaye_automatique
if(isset($_POST['submit_montantpaye_automatique_x']))
{ $montantpaye_automatique=($montantpaye_automatique=='non'?'oui':'non');//le bouton=submit_montantpaye_automatique#oui ou non a la reception.
}
if($submit=='submit_supprimer_une_pj')
{ $supprimer_une_pj=true;
	$codetypepj=$submit_val;
}
$time=isset($_GET['time'])?$_GET['time']:(isset($_POST['time'])?$_POST['time']:"");
$dejaenvoye=(isset($_SESSION['time']) && $time!=$_SESSION['time'])?true:false;
$_SESSION['time']=time();
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF']) /* && !$dejaenvoye */) 
{ foreach($_POST as $postkey=>$postval)
	{ $posdiese=strpos($postkey,'#');
		if($posdiese!==false)
		{ $champ=substr($postkey,0,$posdiese);
			if($champ=='nummigo')
			{ $codemigo=substr($postkey,$posdiese+1);
				$tab_commandemigo[$codemigo][$champ]=$postval;
			}
			else if($champ=='datemigo_jj')
			{ $tab_commandemigo[$codemigo]['datemigo']['jj']=$postval;
				$tab_champs_date['datemigo#'.$codemigo]=array("lib" => "Date serv. fait (MIGO) ligne ".$codemigo,"jj" => "","mm" => "","aaaa" => "");
			}
			else if($champ=='datemigo_mm')
			{ $tab_commandemigo[$codemigo]['datemigo']['mm']=$postval;
			}
			else if($champ=='datemigo_aaaa')
			{ $tab_commandemigo[$codemigo]['datemigo']['aaaa']=$postval;
			}
			else if($champ=='codetypecredit')
			{ $virtuel_ou_reel=substr($postkey,$posdiese+1);
				if($virtuel_ou_reel=='1')//en reel il n'y a qu'un codetypecredit
				{ $tab_commandeimputationbudget_codetypecredit[$virtuel_ou_reel]['codetypecredit']=$postval;
				}
			}
			else if(strpos('numinventaire,codedestinataire,codelieu,num_bureau,objetinventaire',$champ)!==false)
			{	$codeinventaire=substr($postkey,$posdiese+1);
				$tab_commandeinventaire[$codeinventaire][$champ]=$postval;
			}

			$posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{	if(strpos('numliquidation, montantliquidation, dateliquidation_jj,dateliquidation_mm,dateliquidation_aaaa,numfacture, datefacture_jj,datefacture_mm,datefacture_aaaa',$champ)!==false)
				{ $codemigo=substr($postkey,$posdiese+1,$posdoublediese-$posdiese-1);
					$codeliquidation=substr($postkey,$posdoublediese+2);
					if($champ=='numliquidation')
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation][$champ]=$postval;
					}
					else if($champ=='montantliquidation')
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation][$champ]=$postval;
						$tab_champs_numerique[$postkey]=array('lib' => 'Montant liquidation','string_format'=>'%01.2f');
					}
					else if(strpos($postkey,'dateliquidation_jj#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['dateliquidation']['jj']=$postval;
						$tab_champs_date['dateliquidation#'.$codemigo.'##'.$codeliquidation]=array("lib" => "Date liquidation ligne ".$codemigo.' colonne '.$codeliquidation,"jj" => "","mm" => "","aaaa" => "");
					}
					else if(strpos($postkey,'dateliquidation_mm#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['dateliquidation']['mm']=$postval;
					}
					else if(strpos($postkey,'dateliquidation_aaaa#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['dateliquidation']['aaaa']=$postval;
					}
					else if($champ=='numfacture')
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation][$champ]=$postval;
					}
					else if(strpos($postkey,'datefacture_jj#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['datefacture']['jj']=$postval;
						$tab_champs_date['datefacture#'.$codemigo.'##'.$codeliquidation]=array("lib" => "Date facture ligne ".$codemigo.' colonne '.$codeliquidation,"jj" => "","mm" => "","aaaa" => "");
					}
					else if(strpos($postkey,'datefacture_mm#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['datefacture']['mm']=$postval;
					}
					else if(strpos($postkey,'datefacture_aaaa#')!==false)
					{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['datefacture']['aaaa']=$postval;
					}
				}
				else if(strpos('codetypecredit, codecentrefinancier, codecentrecout, codecontrat, codeeotp, montantengage,montantpaye',$champ)!==false)
				{ $virtuel_ou_reel=substr($postkey,$posdiese+1,$posdoublediese-$posdiese-1);
					// si imputation virtuelle CNRS/UL, l'imputation est faite sur UL
					if($champ=='codetypecredit' && $virtuel_ou_reel=='0' && $postval=='00')//Dotation UL ou CNRS
					{ $postval='02';
					}
					$numordre=substr($postkey,$posdoublediese+2);
					$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre][$champ]=$postval;
					if($champ=='montantengage' || $champ=='montantpaye')
					{ $tab_champs_numerique[$postkey]= array('lib' => 'Montant','string_format'=>'%01.2f');
					}
				}
			}
		}
	}
	//
	$aumoinsuneimputationreelle=false;
	$nbimputationreelle=0;
	$cumulmontantpaye_reel=0;//pour montant paye virtuel
	$cumulmontantengage_virtuel=0;// 20160323 pour montant engage reel si pas d'imputation reelle ou pas de visa appose 
	foreach($tab_commandeimputationbudget as $virtuel_ou_reel=>$tab_commandeimputationbudget_virtuel_ou_reel)
	{ foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
		{ if($virtuel_ou_reel=='1')//le meme codetype credit pour toutes imputations en reel
			{ $tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codetypecredit']=$tab_commandeimputationbudget_codetypecredit[$virtuel_ou_reel]['codetypecredit'];
			}
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['numordre']=$numordre;
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['virtuel_ou_reel']=$virtuel_ou_reel;
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codecommande']=$codecommande;
			if($virtuel_ou_reel=='0')
			{ $tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['montantpaye']='';//le champ n'est pas envoye dans le post
				$cumulmontantengage_virtuel+=$une_commandeimputationbudget['montantengage'];// 20160323
			}
			if($virtuel_ou_reel=='1')
			{ if($une_commandeimputationbudget['montantengage']!='')
				{ $aumoinsuneimputationreelle=true;
					$nbimputationreelle+=1;
					if(isset($une_commandeimputationbudget['montantpaye']))
					{ $cumulmontantpaye_reel+=$une_commandeimputationbudget['montantpaye'];
					}
				}
				else
				{ $tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['montantpaye']='';
				}
			}
		}
	}

	// initialise une imputation reelle avec la premiere virtuelle si montant=0 ou ''
	// l'imputation virtuelle UL/CNRS a déja ete transformee en UL ci-dessus 
	if(!$aumoinsuneimputationreelle)
	{ $tab_commandeimputationbudget['1']['01']=$tab_commandeimputationbudget['0']['01'];
		// 20160323 on reporte le montant engage virtuel en reel y compris avec le role sif
		$tab_commandeimputationbudget['1']['01']['montantengage']=$cumulmontantengage_virtuel;
		// 20160323
		$query_rs = "SELECT codeeotp from contrateotp where codecontrat=".GetSQLValueString($tab_commandeimputationbudget['0']['01']['codecontrat'], "text");
		$rs=mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandeimputationbudget['1']['01']['codeeotp']=$row_rs['codeeotp'];
		}
		else
		{ $tab_commandeimputationbudget['1']['01']['codeeotp']=$tab_commandeimputationbudget['0']['01']['codecontrat'];
		}
		$tab_commandeimputationbudget['1']['01']['montantpaye']='';
		$nbimputationreelle=1;
	}
	
	// 20160323 on reporte le montant engage virtuel en reel y compris sauf avec le role sif
	// si le nb d'imputations reelles = 1 et si aucun visa appose pour la commande 
	if($nbimputationreelle==1 && !estrole('sif',$tab_roleuser)) 
	{ $query_rs="SELECT * from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text");
		$rs=mysql_query($query_rs) or  die(mysql_error());
		if(mysql_num_rows($rs)==0)
		{ $tab_commandeimputationbudget['1']['01']['montantengage']=$cumulmontantengage_virtuel;
		}
	}
	// 20160323
	// suite bug, dans les donnees envoyées dans le post mauvais positionnement (surement du a javascript) de listes select de codecredit, codecentrefinancier, codecentrecout et source
	// on verifie que tout est conforme en se basant sur la source et on rend coherent par ecrasement des valeurs envoyees credit, type credit, enveloppe
	$query_rs="SELECT typecredit.codetypecredit,centrefinancier.codecentrefinancier,centrecout.codecentrecout,codecontrat as codesource,'0' as virtuel_ou_reel".
						" from typecredit,centrefinancier,centrecout,budg_contrat_source_vue".
						" where typecredit.codetypecredit=centrefinancier.codetypecredit".
						" and centrefinancier.codecentrefinancier=centrecout.codecentrefinancier".
						" and centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout".
						" UNION".
						" SELECT typecredit.codetypecredit,centrefinancier.codecentrefinancier,centrecout.codecentrecout,codeeotp as codesource,'1' as virtuel_ou_reel".
						" from typecredit,centrefinancier,centrecout,budg_eotp_source_vue".
						" where typecredit.codetypecredit=centrefinancier.codetypecredit".
						" and centrefinancier.codecentrefinancier=centrecout.codecentrefinancier".
						" and centrecout.codecentrecout=budg_eotp_source_vue.codecentrecout";
	$rs=mysql_query($query_rs) or  die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_creditfinanciercoutcontrateotp[$row_rs['virtuel_ou_reel']][$row_rs['codesource']]['codetypecredit']=$row_rs['codetypecredit'];
		$tab_creditfinanciercoutcontrateotp[$row_rs['virtuel_ou_reel']][$row_rs['codesource']]['codecentrefinancier']=$row_rs['codecentrefinancier'];
		$tab_creditfinanciercoutcontrateotp[$row_rs['virtuel_ou_reel']][$row_rs['codesource']]['codecentrecout']=$row_rs['codecentrecout'];
	}
	$coherence=true;
	foreach($tab_commandeimputationbudget as $virtuel_ou_reel=>$tab_commandeimputationbudget_virtuel_ou_reel)
	{ foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
		{ $codesource=$virtuel_ou_reel=='0'?$une_commandeimputationbudget['codecontrat']:$une_commandeimputationbudget['codeeotp'];
			if($tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codetypecredit']!=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codetypecredit']
			 || $tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codecentrefinancier']!=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codecentrefinancier']
				|| $tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codecentrecout']!=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codecentrecout']
				)
			{ $coherence=false;
			  $erreur.='Incoh&eacute;rence enveloppe/source ligne '.$numordre.' : v&eacute;rifiez et enregistrez &agrave; nouveau.<br>Le d&eacute;veloppeur a &eacute;t&eacute; pr&eacute;venu par mail';
			}
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codetypecredit']=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codetypecredit'];
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codecentrefinancier']=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codecentrefinancier'];
			$tab_commandeimputationbudget[$virtuel_ou_reel][$numordre]['codecentrecout']=$tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$codesource]['codecentrecout'];
		}
	}
	if(!$coherence)
	{ $msg="";
		foreach($_POST as $key => $val)
		{ $msg.='<br>'.$key.'='.$val;
		}
		mail_adminbd('12+ Warning',$_SERVER['PHP_SELF'],'codecontrat, codecentrefinancier ou codecentrecout de la commande '.$msg);
	}
	// fin mise en coherence
	
	foreach($_POST as $postkey=>$codecontrat)
	{ if(strpos($postkey,'codecontrat_a_justifier#')!==false && $codecontrat!='' )
		{ $numordre=substr($postkey,strlen('codecontrat_a_justifier#'));
			if(!in_array($codecontrat,$tab_commandejustifiecontrat))//enleve tout doublon choisi par erreur
			{ $tab_commandejustifiecontrat[$numordre]=$codecontrat;
			}
		}
	}
	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_commande($_POST,$tab_controle_et_format);
	// migo et liquidations : recupere les dates formatees du traitement controle_form_commande
	foreach($tab_commandemigo as $codemigo=>$une_commandemigo)
	{ $tab_commandemigo[$codemigo]['datemigo']=jjmmaaaa2date($_POST['datemigo#'.$codemigo]['jj'],$_POST['datemigo#'.$codemigo]['mm'],$_POST['datemigo#'.$codemigo]['aaaa']);
		$tab_commandemigoliquidation=$une_commandemigo['tab_commandemigoliquidation'];
		foreach($tab_commandemigoliquidation as $codeliquidation=>$une_commandemigoliquidation)
		{ $tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['dateliquidation']=jjmmaaaa2date($_POST['dateliquidation#'.$codemigo.'##'.$codeliquidation]['jj'],$_POST['dateliquidation#'.$codemigo.'##'.$codeliquidation]['mm'],$_POST['dateliquidation#'.$codemigo.'##'.$codeliquidation]['aaaa']);
			$tab_commandemigo[$codemigo]['tab_commandemigoliquidation'][$codeliquidation]['datefacture']=jjmmaaaa2date($_POST['datefacture#'.$codemigo.'##'.$codeliquidation]['jj'],$_POST['datefacture#'.$codemigo.'##'.$codeliquidation]['mm'],$_POST['datefacture#'.$codemigo.'##'.$codeliquidation]['aaaa']);
		}
	}

	//$erreur='erreur forcée';
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		if($action=="creer")//creation
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='commande'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codecommande=$row_seq_number['currentnumber'];
			$codecommande=str_pad((string)((int)$codecommande+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecommande, "text")." where nomtable='commande'") or  die(mysql_error());
			mysql_query("COMMIT") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM commande');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codecommande')
				{ $liste_val.=GetSQLValueString($codecommande, "text");
				}
				else if($row_rs_fields['Field']=='codecreateur' || $row_rs_fields['Field']=='codemodifieur')
				{ $liste_val.=GetSQLValueString($codeuser, "text");
				}
				else if($row_rs_fields['Field']=='date_creation' || $row_rs_fields['Field']=='date_modif')
				{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
				}
				else if(array_key_exists($row_rs_fields['Field'],$tab_champs_ouinon_defaut))
				{ $liste_val.=GetSQLValueString($tab_champs_ouinon_defaut[$row_rs_fields['Field']], "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into commande (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			$action="modifier";
		}//fin if creation
		$updateSQL = "UPDATE commande SET ";
		$rs_fields = mysql_query('SHOW COLUMNS FROM commande');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $Field=$row_rs_fields['Field'];
			if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'])))
			{ if(!in_array($Field,array("codecommande","codecreateur","date_creation")))//pas de mise a jour de ces champs
				{ $updateSQL.=$Field."=";
					if(array_key_exists($Field,$tab_champs_date)!==false)
					{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
					}
					else if($Field=='montantpaye_automatique')
					{ $updateSQL.=GetSQLValueString($montantpaye_automatique, "text");
					}
					else
					{ $updateSQL.=GetSQLValueString($_POST[$Field], "text");
					}
					$updateSQL.=",";
				}
			}
			else if($Field=='codemodifieur')
			{ $updateSQL.=$Field."=".GetSQLValueString($codeuser, "text").",";
			}
			else if($Field=='date_modif')
			{ $updateSQL.=$Field."=".GetSQLValueString(date("Y/m/d"), "text").",";
			}
		}
		$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
		$updateSQL.=" WHERE codecommande=".GetSQLValueString($codecommande, "text");
		mysql_query($updateSQL) or die(mysql_error());
		
		// ----------------------------- MIGO et liquidations
		// suppression des MIGO existants
		
		mysql_query("delete from commandemigoliquidation where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		mysql_query("delete from commandemigo where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		$cumulmontantliquidation=0;
		$i=0;
		foreach($tab_commandemigo as $codemigo=>$une_commandemigo)
		{ if($une_commandemigo['nummigo']!='')
			{ $i++;
				$datemigo=$tab_commandemigo[$codemigo]['datemigo'];
				$nouveaucodemigo=str_pad($i,2,"0",STR_PAD_LEFT);
				$updateSQL ="INSERT into commandemigo (codecommande,codemigo,nummigo,datemigo) ".
										" values (".GetSQLValueString($codecommande, "text").",".
																GetSQLValueString($nouveaucodemigo, "text").",".
																GetSQLValueString($une_commandemigo['nummigo'], "text").",".
																GetSQLValueString($datemigo, "text").
														")";
				mysql_query($updateSQL) or die(mysql_error());
				$tab_commandemigoliquidation=$une_commandemigo['tab_commandemigoliquidation'];
				$j=0;
				foreach($tab_commandemigoliquidation as $codeliquidation =>$une_commandemigoliquidation)
				{ if($une_commandemigoliquidation['numfacture']!='')
					{ $j++;
						$nouveaucodeliquidation=str_pad($j,2,"0",STR_PAD_LEFT);
						// recuperation des dates migo-liquidation de $_POST suite a passage dans controle_form_commande
						$updateSQL ="INSERT into commandemigoliquidation (codecommande,codemigo,codeliquidation,numliquidation,dateliquidation,montantliquidation,numfacture,datefacture) ".
												" values (".GetSQLValueString($codecommande, "text").",".
																		GetSQLValueString($nouveaucodemigo, "text").",".
																		GetSQLValueString($nouveaucodeliquidation, "text").",".
																		GetSQLValueString($une_commandemigoliquidation['numliquidation'], "text").",".
																		GetSQLValueString($une_commandemigoliquidation['dateliquidation'], "text").",".
																		GetSQLValueString($une_commandemigoliquidation['montantliquidation'], "text").",".
																		GetSQLValueString($une_commandemigoliquidation['numfacture'], "text").",".
																		GetSQLValueString($une_commandemigoliquidation['datefacture'], "text").
																	")";
						mysql_query($updateSQL) or die(mysql_error());
						$cumulmontantliquidation+=$une_commandemigoliquidation['montantliquidation'];
					}
				}
			}
		}
		// ----------------------------- Inventaire
		// suppression des inventaires existants puis insertion
		mysql_query("delete from commandeinventaire where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		$i=0;
		foreach($tab_commandeinventaire as $codeinventaire=>$une_commandeinventaire)
		{ if($une_commandeinventaire['numinventaire']!='')
			{ $i++;
				$nouveaucodeinventaire=str_pad($i,2,"0",STR_PAD_LEFT);
				$updateSQL ="INSERT into commandeinventaire (codecommande,codeinventaire,codedestinataire,codelieu,num_bureau,numinventaire,objetinventaire) ".
										" values (".GetSQLValueString($codecommande, "text").",".
																GetSQLValueString($nouveaucodeinventaire, "text").",".
																GetSQLValueString($une_commandeinventaire['codedestinataire'], "text").",".
																GetSQLValueString($une_commandeinventaire['codelieu'], "text").",".
																GetSQLValueString($une_commandeinventaire['num_bureau'], "text").",".
																GetSQLValueString($une_commandeinventaire['numinventaire'], "text").",".
																GetSQLValueString($une_commandeinventaire['objetinventaire'], "text").
														")";
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		// ----------------------------- ventilation imputations
		// suppression des montants existants
		mysql_query("delete from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		// le montant paye virtuel n'est pas saisi=>on lui affecte le cumul des montants payes reels ou le cumul des montants liquidations
		$montantpaye_virtuel=$cumulmontantpaye_reel;
		if($nbimputationreelle==1 && $montantpaye_automatique=='oui')
		{ $montantpaye_virtuel=$cumulmontantliquidation;
		}
		// insertion des montants
		foreach($tab_commandeimputationbudget as $virtuel_ou_reel=>$tab_commandeimputationbudget_virtuel_ou_reel)
		{ $i=0;//rend les numordre des imputations consecutifs
			foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
			{ // 08/10/2016 0=='' est vrai => test avec strval()
				//if(isset($une_commandeimputationbudget['montantengage']) && $une_commandeimputationbudget['montantengage']!='')
				if(isset($une_commandeimputationbudget['montantengage']) && strval($une_commandeimputationbudget['montantengage'])!='')
				// 08/10/2016
				{ $i++;
				  // ce bloc de verification date de 2013 : il n'est plus possible d'avoir ce cas de figure a priori
					if(!isset($une_commandeimputationbudget['codetypecredit']))
					{ $une_commandeimputationbudget['codetypecredit']='';
					}
					if(!isset($une_commandeimputationbudget['centrefinancier']))
					{ $une_commandeimputationbudget['centrefinancier']='';
					}
					if(!isset($une_commandeimputationbudget['codecentrecout']))
					{ $une_commandeimputationbudget['codecentrecout']='';
					}
					// fin ce bloc de verification date de 2013
					  
					if(!isset($une_commandeimputationbudget['codecontrat']))
					{ $une_commandeimputationbudget['codecontrat']='';
					}
					if(!isset($une_commandeimputationbudget['codeeotp']))
					{ $une_commandeimputationbudget['codeeotp']='';
					} 
					if($virtuel_ou_reel=='0')//en virtuel la valeur n'existe pas : on la renseigne avec le cumulpaye reel
					{ $une_commandeimputationbudget['montantpaye']=$montantpaye_virtuel;
					}
					else if($nbimputationreelle==1 && $montantpaye_automatique=='oui')//une seule imputation et automatique
					{ $une_commandeimputationbudget['montantpaye']=$cumulmontantliquidation;
					}
					$updateSQL ="INSERT into commandeimputationbudget (codecommande,codetypecredit,codecentrefinancier,codecentrecout,codecontrat,codeeotp,virtuel_ou_reel,montantengage,montantpaye,numordre) ".//,
											" values (".GetSQLValueString($codecommande, "text").",".
																	GetSQLValueString($une_commandeimputationbudget['codetypecredit'], "text").",".
																	GetSQLValueString($une_commandeimputationbudget['codecentrefinancier'], "text").",".
																	GetSQLValueString($une_commandeimputationbudget['codecentrecout'], "text").",".
																	// 01/2014 : enregistre codecontrat sinon '' (ou codeeotp sinon '') si virtuel_ou_reel=0 (ou 1) 
																	GetSQLValueString($virtuel_ou_reel=='0'?$une_commandeimputationbudget['codecontrat']:'', "text").",".
																	GetSQLValueString($virtuel_ou_reel=='1'?$une_commandeimputationbudget['codeeotp']:'', "text").",".
																	GetSQLValueString($virtuel_ou_reel, "text").",".
																	GetSQLValueString($une_commandeimputationbudget['montantengage'], "text").",".
																	GetSQLValueString($une_commandeimputationbudget['montantpaye'], "text").",".
																	GetSQLValueString(str_pad($i,2,"0",STR_PAD_LEFT), "text").
															")";
					//echo '<br>'.$updateSQL;
					mysql_query($updateSQL) or die(mysql_error());
				}
			}
		}
		// ----------------------------- justification contrats
		// suppression des contrats existants
		mysql_query("delete from commandejustifiecontrat where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		$i=0;
		foreach($tab_commandejustifiecontrat as $numordre=>$codecontrat)
		{ $i++;
			$updateSQL ="INSERT into commandejustifiecontrat (codecommande,codecontrat,numordre) ".
										" values (".GetSQLValueString($codecommande, "text").",".GetSQLValueString($codecontrat, "text").",".GetSQLValueString(str_pad($i,2,'0',STR_PAD_LEFT), "text").")";
			mysql_query($updateSQL) or die(mysql_error());
		}

		// ------------------------------ suppression pj (et rep(s) si vide)
		if($supprimer_une_pj)
		{ $updateSQL ="delete from commandepj ".
									" where codecommande=".GetSQLValueString($codecommande, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text");
			mysql_query($updateSQL) or die(mysql_error());
			unlink($GLOBALS['path_to_rep_upload'] .'/commande/'.$codecommande.'/'.$codetypepj);
		}
		
		//ajout pj et reps eventuels
		$tab_commandepj=array();
		//liste de toutes les pj commande 
		$rs_pj=mysql_query("select codetypepj,codelibtypepj from typepjcommande");
		while($row_rs_pj=mysql_fetch_assoc($rs_pj))
		{ $tab_pj[$row_rs_pj['codelibtypepj']]=$row_rs_pj['codetypepj'];
		}
		if(isset($_FILES["pj"]["name"]))// 20170412
		{ foreach ($_FILES["pj"]["name"] as $key => $nomfichier)//$key=codetypepj 
			{ $codetypepj=$key;
				if($nomfichier!='')
				{ clearstatcache();//efface le cache relatif a l'existence des repertoires
					$rep_upload=$GLOBALS['path_to_rep_upload'].'/commande/'.$codecommande ;
					if(!is_dir($rep_upload))//teste si existe 
					{ mkdir ($rep_upload);
					}
					$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$codetypepj);//$key=$codetypepj pour commande
					if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
					{	// si existe deja
						$updateSQL= "delete from commandepj where codecommande=".GetSQLValueString($codecommande, "text").
												" and codetypepj=".GetSQLValueString($codetypepj, "text");
						mysql_query($updateSQL) or die(mysql_error());
						$updateSQL="insert into commandepj (codecommande,codetypepj,nomfichier)". 
												" values (".GetSQLValueString($codecommande, "text").",".GetSQLValueString($codetypepj,"text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").")";
						mysql_query($updateSQL) or die(mysql_error());
					}
					else if($tab_res_upload['nomfichier']!='')
					{ $warning.='<br>'.$tab_res_upload['erreur'];
					}
				}
			}
		}
		if(isset($rs_pj)) {mysql_free_result($rs_pj);}
		if(isset($rs_commandepj)) {mysql_free_result($rs_commandepj);}
	}
}// Fin de traitement des donnees
// visa 
else if($action=='valider' || $action=='valider_mail_visa_revalide' || isset($_POST['b_invalider_visa_x']))		
{	// ------------------------------ validation, invalidation,...
	// 20170427
	$query_rs_cmd="select commande.*".
								" from commande".
								" where codecommande=".GetSQLValueString($codecommande, "text");
	$rs_cmd=mysql_query($query_rs_cmd) or die(mysql_error());
	$row_rs_cmd=mysql_fetch_assoc($rs_cmd);
	if($action=='valider' || $action=='valider_mail_visa_revalide')// && !isset($_POST['annuler_x']) 28/01/2014 : $_POST['annuler_x'] peut etre positionne si passage par confirmer_action_commande
	{ if($action=='valider' || ($action=='valider_mail_visa_revalide' && isset($_POST['envoyer_mail_validation'])))// si validation normale (sans annulation de visa anterieure) ou si validation avec annulation de visa anterieure et envoyer_mail_postionne dans confirme_action_commande 
		{ $erreur_envoimail=mail_validation_commande($row_rs_cmd,$codeuser,$codevisa_a_apposer,$envoyer_mail_srh);
		}
		
		$rs=mysql_query("select coderole from cmd_statutvisa where codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text"));
		$row_rs=mysql_fetch_assoc($rs);
		$codevisa_a_apposer_lib=$row_rs['coderole'];
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : Visa '".$codevisa_a_apposer_lib."' pour la commande ".$row_rs_cmd['codecommande'];
			$affiche_succes=false;
			// 20170427 
			$message_resultat_affiche="Validation non effectu&eacute;e.";
			// $erreur="Validation non effectu&eacute;e.";
			// 20170427 
		}
		else
		{ //Dans tous les cas
			$message_resultat_affiche="Validation effectu&eacute;e.";
			// 20/01/2014 plus d'une imput. virt.
			$viser_la_commande_totalement=true;
			// seuls resp. credits et ceux qui $_SESSION['b_cmd_etre_admin'] ont pu valider theme ou contrat
			// s'il n'y a qu'un visa a apposer ou si le dernier visa est apposé par resp. credits
			if(($codevisa_a_apposer_lib=='theme' || $codevisa_a_apposer_lib=='contrat') && !$_SESSION['b_cmd_etre_admin'])
			{	//imputations de la commande
				$query_rs="select numordre,codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'";
				$rs=mysql_query($query_rs);
				if(mysql_num_rows($rs)>1)// plus d'une imputation	virtuelle	
				{	$viser_la_commande_totalement=false;
					while($row_rs=mysql_fetch_assoc($rs))
					{ $tab_commandeimputationbudget_de_la_commande[$row_rs['codecontrat']]=$row_rs['numordre'];
					}
					// imputations dont le codeuser est resp.
					$tab_commandeimputationbudget_du_codeuser_de_la_commande=array();
					$query_rs="select distinct commandeimputationbudget.codecontrat from  commandeimputationbudget,budg_contrat_source_vue,individu".
										" where commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
										" and commandeimputationbudget.codecontrat<>''".
										" and (			(individu.codeindividu = budg_contrat_source_vue.coderespscientifique and individu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
										"				or  (budg_contrat_source_vue.coderespscientifique='' ".
																" and budg_contrat_source_vue.codecentrecout in (select codecentrecout from centrecouttheme,structureindividu".
																																								" where centrecouttheme.codestructure=structureindividu.codestructure".
																																								" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
																")".
										"			)".
										" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text");
					$rs=mysql_query($query_rs);
					while($row_rs=mysql_fetch_assoc($rs))
					{ $tab_commandeimputationbudget_du_codeuser_de_la_commande[$row_rs['codecontrat']]=$row_rs['codecontrat'];
					}
					$tab_commandeimputationbudget_statutvisa_de_la_commande=array();
					$query_rs="select codecontrat,codestatutvisa".
										" from commandeimputationbudget_statutvisa".
										" where codecommande=".GetSQLValueString($codecommande, "text")."and (codestatutvisa='02' or codestatutvisa='03')";
					$rs=mysql_query($query_rs);
					while($row_rs=mysql_fetch_assoc($rs))
					{ $tab_commandeimputationbudget_statutvisa_de_la_commande[$row_rs['codecontrat']]=$row_rs['codestatutvisa'];
					}
					foreach($tab_commandeimputationbudget_de_la_commande as $uncodecontrat=>$numordre)
					{ if(array_key_exists($uncodecontrat,$tab_commandeimputationbudget_du_codeuser_de_la_commande) && !array_key_exists($uncodecontrat,$tab_commandeimputationbudget_statutvisa_de_la_commande))
						{ $updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text").
													" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text")." and codecontrat=".GetSQLValueString($uncodecontrat, "text");
							mysql_query($updateSQL) or die(mysql_error());
							$updateSQL = "INSERT into commandeimputationbudget_statutvisa (codecommande,codestatutvisa,codecontrat,codeacteur,datevisa) values (".
														GetSQLValueString($codecommande, "text").",".
														GetSQLValueString($codevisa_a_apposer, "text").",".
														GetSQLValueString($uncodecontrat, "text").",".
														GetSQLValueString($codeuser, "text").",".
														GetSQLValueString($aujourdhui, "text").						
														")"; 
							mysql_query($updateSQL) or die(mysql_error());
						}
					}
					$query_rs="select codecontrat". 
										" from commandeimputationbudget".
										" where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'".
										" and codecontrat not in (select codecontrat from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text").
																							" and (codestatutvisa='02' or codestatutvisa='03')".")";
					$rs=mysql_query($query_rs);
					if(mysql_num_rows($rs)==0)//toutes les imputations ont le visa 'theme' ou 'contrat'
					{ $viser_la_commande_totalement=true;
					}
				}
			}
			// fin 20/01/2014 plus d'une imput. virt.			
			if($viser_la_commande_totalement)
			{ // la suppression dans commandeimputationbudget_statutvisa est faite ici car si peut_etre_admin, le visa est appose pour toutes les imputations
				$updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text");
				mysql_query($updateSQL) or die(mysql_error()); 
				// suppression de la ligne avec $codeuser pour cette commande $codecommande pour le role si elle existe
				$updateSQL ="delete from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text").
										" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
				
				$updateSQL = "INSERT into commandestatutvisa (codecommande,codestatutvisa,codeacteur,datevisa) values (".
											GetSQLValueString($codecommande, "text").",".
											GetSQLValueString($codevisa_a_apposer, "text").",".
											GetSQLValueString($codeuser, "text").",".
											GetSQLValueString($aujourdhui, "text").						
											")";
				mysql_query($updateSQL) or die(mysql_error());
			}
			// modif 20/09/2014 cas d'une commande dupliquee non modifiee :on ne tient compte que de la 1ere imputation virtuelle dont on reporte le montantengage 
			// dans la 1ere reelle si elle est vide. Suppression dans ce cas des autres imputations reelles. Uniquement pour visa resp pour mettre un montant reel pour IEB
			if(($codevisa_a_apposer_lib=='referent'))
			{	$query_rs="select montantengage, virtuel_ou_reel from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and numordre='01' order by virtuel_ou_reel asc";
				$rs=mysql_query($query_rs);
				if($row_rs=mysql_fetch_assoc($rs))
				{ $montantengage_virtuel=$row_rs['montantengage'];
					if($row_rs=mysql_fetch_assoc($rs))
					{ $montantengage_reel=$row_rs['montantengage'];
						if($montantengage_reel=='')
						{ $updateSQL ="update commandeimputationbudget set montantengage=".GetSQLValueString($montantengage_virtuel, "text").
													" where codecommande=".GetSQLValueString($codecommande, "text").
													" and virtuel_ou_reel='1' and numordre='01'";
							mysql_query($updateSQL) or die(mysql_error());
							$updateSQL ="delete from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text").
													" and virtuel_ou_reel='1' and numordre<>'01'";
							mysql_query($updateSQL) or die(mysql_error());
						}
					}
				}
			}
			// fin modif 20/09/2014
		}
	}
	else if(isset($_POST['b_invalider_visa_x']))
	{	$message_resultat_affiche="Invalidation effectu&eacute;e.";
		// suppression de tous les visas pour cette commande $codecommande pour le role apres mail_invalidation_commande qui a besoin des statutsvisas de la commande
		$query_rs="select codestatutvisa from  cmd_statutvisa ".
							" where codestatutvisa in (select max(codestatutvisa) from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text").")";
		$rs=mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))//max de codestatutvisa
		{ $codestatutvisa_a_annuler=$row_rs['codestatutvisa'];
			$updateSQL ="delete from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text")." and codestatutvisa=".GetSQLValueString($codestatutvisa_a_annuler, "text");
			mysql_query($updateSQL) or die(mysql_error());
			if($row_rs['codestatutvisa']=='02' || $row_rs['codestatutvisa']=='03')//theme ou contrat : supprime visas partiels eventuels
			{ $updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
			}
			// on met a jour codevisaannulemax si le visa annule est plus grand : s'il est plus petit, il faut pouvoir atteindre codevisaannulemax
			// si codevisaannulemax='',codestatutvisa_a_annuler>codevisaannulemax  
			if($codestatutvisa_a_annuler>$row_rs_cmd['codevisaannulemax'])
			{ $updateSQL ="update commande set codevisaannulemax=".GetSQLValueString($codestatutvisa_a_annuler, "text")." where  codecommande=".GetSQLValueString($codecommande, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
	}
	$action="modifier"; //20170427 : decale apres traitement visa, traite_ieb, invalider,...
	// 20170427 fin
}
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//En creation, l'ancre est connue maintenant
$cmd_ancre="C".$codecommande;
//Informations de la commande (un enreg. vide dans commande pour "creer")
$query_commande =	"SELECT commande.*".
									" FROM commande".
									" WHERE commande.codecommande=".GetSQLValueString($codecommande,"text");
$rs_commande=mysql_query($query_commande) or die(mysql_error());
$row_rs_commande=mysql_fetch_assoc($rs_commande);//il y a forcement une ligne. S'il y a eu erreur, $codecommande=''

// 20170427
$query_rs="select commande.codecommande,coderole,commandestatutvisa.codestatutvisa ".
					" from commande left join commandestatutvisa  on commande.codecommande=commandestatutvisa.codecommande".
					" left join cmd_statutvisa on commandestatutvisa.codestatutvisa=cmd_statutvisa.codestatutvisa".
					" where  commande.codecommande=".GetSQLValueString($codecommande,"text").
					" order by commande.codecommande,cmd_statutvisa.codestatutvisa";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $row_rs_commande['tab_commandestatutvisa'][$row_rs['coderole']]=$row_rs['codestatutvisa'];
}

// plus d'une imput. virt.	
$tab_commandeimputationbudget_statutvisa=array();
$query_rs="select codecommande,codecontrat,codestatutvisa".
					" from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande,"text");
$rs=mysql_query($query_rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commandeimputationbudget_statutvisa[$codecommande][$row_rs['codecontrat']]=$row_rs['codestatutvisa'];
}
// 20170427

$query_rs_individu ="SELECT  createur.nom as createurnom,createur.prenom as createurprenom, modifieur.nom as modifieurnom, modifieur.prenom as modifieurprenom ".
												" FROM commande, individu as createur, individu as modifieur ".
												" WHERE createur.codeindividu=commande.codecreateur and modifieur.codeindividu=commande.codemodifieur".
												" and commande.codecommande = ".GetSQLValueString($codecommande, "text");
$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_commande=array_merge($row_rs_commande,mysql_fetch_assoc($rs_individu));
if($row_rs_commande['codemission']!='')
{ $est_cmd_de_miss=true;
	$query_mission ="SELECT mission.* FROM mission WHERE codemission=".GetSQLValueString($row_rs_commande['codemission'],"text");
	$rs_mission=mysql_query($query_mission) or die(mysql_error());
	$row_rs_mission=mysql_fetch_assoc($rs_mission);
}

$une_seule_ligne_a_imputer_reel=true;
$montantpaye_automatique=($row_rs_commande['montantpaye_automatique']!='oui'?'non':$row_rs_commande['montantpaye_automatique']);
$cumulmontantliquidation=0;//montant cumule des liquidations
if($erreur=='')
{	$tab_commandejustifiecontrat=array();
	if($action=='creer')
	{ $row_rs_commande['createurnom']=$tab_infouser['nom'];
		$row_rs_commande['createurprenom']=$tab_infouser['prenom'];
		$row_rs_commande['date_creation']=date("Y/m/d");
		$row_rs_commande['modifieurnom']=$tab_infouser['nom'];
		$row_rs_commande['modifieurprenom']=$tab_infouser['prenom'];
		$row_rs_commande['date_modif']=date("Y/m/d");
		$row_rs_commande['datecommande']=date("Y").'/'.date("m").'/'.date("d");
		$row_rs_commande['codenature']='';
		$row_rs_commande['codedialoguegestion']='';
		$row_rs_commande['montantpaye_automatique']='oui';
		foreach(array('0','1') as $virtuel_ou_reel)
		{ foreach(array('codetypecredit','codecentrefinancier','codecentrecout','codeeotp','codecontrat','montantengage','montantpaye') as $field)
			{ $tab_commandeimputationbudget[$virtuel_ou_reel]['01'][$field]='';
			}
			$tab_commandeimputationbudget[$virtuel_ou_reel]['01']['numordre']='01';
			$nbcommandejustifiecontrat=1;
		}
		//mission liee
		if($est_cmd_de_miss)
		{ $query_mission ="SELECT mission.*".
											" FROM mission".
											" WHERE codemission=".GetSQLValueString($codemission,"text");
			$rs_mission=mysql_query($query_mission) or die(mysql_error());
			$row_rs_mission=mysql_fetch_assoc($rs_mission);
			$row_rs_commande['codereferent']=$row_rs_mission['codeagent'];
			$row_rs_commande['codesecrsite']=$row_rs_mission['codesecrsite'];
			$row_rs_commande['codemission']=$row_rs_mission['codemission'];
		}
		else//par defaut si le codeuser est secrsite, elle sera selectionnee
		{ $row_rs_commande['codesecrsite']=$codeuser;
		}
		//creation commande de congres
		if($estcommandedecongres=='oui')
		{ $row_rs_commande['objet']=html_entity_decode('Frais de congr&egrave;s');
		}
		// une MIGO vide
		$tab_commandemigo=array();
		$tab_commandemigo['01']=array('codecommande'=>$codecommande,'codemigo'=>'01','nummigo'=>'','datemigo'=>'');
		// avec une liquidation vide
		$tab_commandemigo['01']['tab_commandemigoliquidation']['01']=array('codecommande'=>$codecommande,'codemigo'=>'01','codeliquidation'=>'01','numliquidation'=>'','dateliquidation'=>'','montantliquidation'=>'','numfacture'=>'','datefacture'=>'');
		// un contrat a justifier vide
		$tab_commandejustifiecontrat['01']='';
		
		// 20170405
		// une ligne inventaire vide
		$tab_commandeinventaire=array();
		$tab_commandeinventaire['01']=array('codecommande'=>$codecommande,'codeinventaire'=>'01','codedestinataire'=>'','codelieu'=>'','num_bureau'=>'','numinventaire'=>'','objetinventaire'=>'');
		// 20170405

	}
	else if($action=='modifier')
	{ // Liste des MIGO
		$nb_commandemigo=0;
		$tab_commandemigo=array();
		$rs_commandemigo=mysql_query("SELECT * from commandemigo where codecommande=".GetSQLValueString($codecommande, "text")." order by codemigo") or die(mysql_error());
		while($row_rs_commandemigo=mysql_fetch_assoc($rs_commandemigo))
		{ $nb_commandemigo++;
			$codemigo=$row_rs_commandemigo['codemigo'];
			$tab_commandemigo[$codemigo]=$row_rs_commandemigo;
			// liquidations par MIGO
			$rs_commandemigoliquidation=mysql_query("SELECT * from commandemigoliquidation".
																							" where codecommande=".GetSQLValueString($codecommande, "text").
																							" and codemigo=".GetSQLValueString($codemigo, "text").
																							" order by codeliquidation") or die(mysql_error());
			$nb_liquidation=0;
			$tab_commandemigoliquidation=array();
			while($row_rs_commandemigoliquidation=mysql_fetch_assoc($rs_commandemigoliquidation))
			{ $nb_liquidation++;
				$tab_commandemigoliquidation[$row_rs_commandemigoliquidation['codeliquidation']]=$row_rs_commandemigoliquidation;
				$cumulmontantliquidation+=$row_rs_commandemigoliquidation['montantliquidation'];
			}
			//une nouvelle liquidation pour la MIGO
			$nouveaucodeliquidation=str_pad($nb_liquidation+1,2,'0',STR_PAD_LEFT);
			$tab_commandemigoliquidation[$nouveaucodeliquidation]=array('codecommande'=>$codecommande,'codemigo'=>$row_rs_commandemigo['codemigo'],'codeliquidation'=>$nouveaucodeliquidation,'numliquidation'=>'','dateliquidation'=>'','montantliquidation'=>'','numfacture'=>'','datefacture'=>'');
			$tab_commandemigo[$row_rs_commandemigo['codemigo']]['tab_commandemigoliquidation']=$tab_commandemigoliquidation;
		}
		// une nouvelle MIGO vide
		$nouveaucodemigo=str_pad($nb_commandemigo+1,2,'0',STR_PAD_LEFT);
		$tab_commandemigo[$nouveaucodemigo]=array('codecommande'=>$codecommande,'codemigo'=>$nouveaucodemigo,'nummigo'=>'','datemigo'=>'');
		$tab_commandemigo[$nouveaucodemigo]['tab_commandemigoliquidation']['01']=array('codecommande'=>$codecommande,'codemigo'=>$nouveaucodemigo,'codeliquidation'=>'01','numliquidation'=>'','dateliquidation'=>'','montantliquidation'=>'','numfacture'=>'','datefacture'=>'');
		
		// 20170405
		// inventaire
		$nb_inventaire=0;
		$tab_commandeinventaire=array();
		$rs=mysql_query("SELECT * from commandeinventaire where codecommande=".GetSQLValueString($codecommande, "text")." order by codeinventaire") or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $nb_inventaire++;
			$tab_commandeinventaire[$row_rs['codeinventaire']]=$row_rs;
		}
		$nouveaucodeinventaire=str_pad($nb_inventaire+1,2,'0',STR_PAD_LEFT);
		$tab_commandeinventaire[$nouveaucodeinventaire]=array('codecommande'=>$codecommande,'codeinventaire'=>$nouveaucodeinventaire,'codedestinataire'=>'','codelieu'=>'','num_bureau'=>'','numinventaire'=>'','objetinventaire'=>'');
		// 20170405

		// Liste des imputations budgetaires de la commande 
		// initialise virtuel et reel si aucune imputation saisie : sera ecrase si imputation existe dans commandeimputationbudget
		$tab_commandeimputationbudget=array();
		$numordrevirtuel=0;
		$numordrereel=0;
		//20/01/2014 plus d'une imput.virt.
		$codetypecredit_virtuel='';
		$codetypecredit_reel='';
		$rs=mysql_query("SELECT * from commandeimputationbudget".
										" where codecommande=".GetSQLValueString($codecommande, "text").
										" order by virtuel_ou_reel, numordre") or die(mysql_error());
		$une_seule_ligne_a_imputer_reel=false;//si aucune ou une seule, on pourra affecter le $cumulmontantliquidation a la premiere ligne d'imputation affichée
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandeimputationbudget[$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
			if($row_rs['virtuel_ou_reel']=='0')
			{ $numordrevirtuel=(int)$row_rs['numordre'];
				//20/01/2014 plus d'une imput.virt.
				$codetypecredit_virtuel=$row_rs['codetypecredit'];				
			}
			if($row_rs['virtuel_ou_reel']=='1')
			{ $numordrereel=(int)$row_rs['numordre'];
				$codetypecredit_reel=$row_rs['codetypecredit'];
			}
		}
		if($numordrereel==1)
		{ $une_seule_ligne_a_imputer_reel=true;
		}
		//ajout d'une ligne virtuel et reel si pas d'enr et ajout systematique d'une ligne vide reel
		$rs_fields = mysql_query('SHOW COLUMNS FROM commandeimputationbudget');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields))
		{ if($numordrevirtuel==0)//aucune ligne imputation virtuelle
			{ $numordre=str_pad($numordrevirtuel+1,2,'0',STR_PAD_LEFT);
				$tab_commandeimputationbudget['0'][$numordre][$row_rs_fields['Field']]=($row_rs_fields['Field']=='virtuel_ou_reel'?'0':$row_rs_fields['Field']=='numordre'?$numordre:'');
			}
			if($numordrereel==0)//aucune ligne imputation reelle
			{ $numordre=str_pad($numordrereel+1,2,'0',STR_PAD_LEFT);
				$tab_commandeimputationbudget['0'][$numordre][$row_rs_fields['Field']]=($row_rs_fields['Field']=='virtuel_ou_reel'?'0':$row_rs_fields['Field']=='numordre'?$numordre:'');
			}
			//dans tous les cas ajout d'une ligne vide virtuelle=copie de la virtuelle existante
			//20/01/2014 plus d'une imput.virt.
			$numordre=str_pad($numordrevirtuel+($numordrevirtuel==0?2:1),2,'0',STR_PAD_LEFT);
			if($row_rs_fields['Field']=='montantengage' || $row_rs_fields['Field']=='montantpaye')			
			{ $tab_commandeimputationbudget['0'][$numordre][$row_rs_fields['Field']]='';
			}
			else if($row_rs_fields['Field']=='codetypecredit')
			{	$tab_commandeimputationbudget['0'][$numordre]['codetypecredit']=$codetypecredit_virtuel;
			}
			else
			{ $tab_commandeimputationbudget['0'][$numordre][$row_rs_fields['Field']]=$tab_commandeimputationbudget['0'][str_pad($numordrevirtuel,2,'0',STR_PAD_LEFT)][$row_rs_fields['Field']];
			}
			// fin 20/01/2014 plus d'une imput.virt.
			//dans tous les cas ajout d'une ligne vide reelle=copie de la reelle existante
			$numordre=str_pad($numordrereel+($numordrereel==0?2:1),2,'0',STR_PAD_LEFT);
			if($row_rs_fields['Field']=='montantengage' || $row_rs_fields['Field']=='montantpaye')			
			{ $tab_commandeimputationbudget['1'][$numordre][$row_rs_fields['Field']]='';
			}
			else if($row_rs_fields['Field']=='codetypecredit')
			{	$tab_commandeimputationbudget['1'][$numordre]['codetypecredit']=$codetypecredit_reel;
			}
			else
			{ $tab_commandeimputationbudget['1'][$numordre][$row_rs_fields['Field']]=$tab_commandeimputationbudget['1'][str_pad($numordrereel,2,'0',STR_PAD_LEFT)][$row_rs_fields['Field']];
			}
		}

		//on affecte le cumul des liquidations uniquement si il y a (une ligne réelle ET une réelle vierge) OU (une seule réelle vierge) 
		if($une_seule_ligne_a_imputer_reel && $montantpaye_automatique=='oui')
		{ $tab_commandeimputationbudget['1']['01']['montantpaye']=$cumulmontantliquidation;
		}
		else
		{ $montantpaye_automatique='non';
		}
		// Liste des contrats justifies
		$query_rs="SELECT distinct codecontrat,numordre".
							" from commandejustifiecontrat".
							" where codecommande=".GetSQLValueString($codecommande, "text").
							" order by numordre asc";
		$rs=mysql_query($query_rs) or die(mysql_error());									
		$nbcommandejustifiecontrat=mysql_num_rows($rs);
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandejustifiecontrat[$row_rs['numordre']]=$row_rs['codecontrat'];
		}
		//un nouveau contrat a justifier
		$nbcommandejustifiecontrat++;
		$tab_commandejustifiecontrat[str_pad((string)$nbcommandejustifiecontrat,2,'0',STR_PAD_LEFT)]='';
	}
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_commande = mysql_query('SHOW COLUMNS FROM commande');
	while($row_rs_fields_commande = mysql_fetch_assoc($rs_fields_commande)) 
	{ $Field=$row_rs_fields_commande['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_commande[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_commande['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_commande[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
	$nbcommandejustifiecontrat=count($tab_commandejustifiecontrat);
}

// si demandeur est parti, on previent qu'il n'apparaitra plus dans la liste si deselectionne 
$row_rs_commande['estreferentpresent']='';
if($row_rs_commande['codereferent']!='')
{ $row_rs_commande['estreferentpresent']='non';
	$query_rs= "SELECT * ". 
						 " FROM individu,individusejour".
						 " WHERE individu.codeindividu=individusejour.codeindividu".
						 " and individu.codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text").
						 " and ".periodeencours('datedeb_sejour','datefin_sejour');
	$rs=mysql_query($query_rs) or die(mysql_error());
	if(mysql_num_rows($rs)>=1)
	{ $row_rs_commande['estreferentpresent']='oui';
	}
}
$query_rs_individu ="SELECT  nom as referentnom,prenom as referentprenom".
										" FROM individu ".
										" WHERE codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text");
$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
$row_rs_commande=array_merge($row_rs_commande,mysql_fetch_assoc($rs_individu));

// credit, centre financier et de cout avec filtrage de ceux qui sont dans budg_contrat_source_vue ou budg_eotp_source_vue
// typecredit
$tab_typecredit=array();
// 17122014
$tab_typecredit['00']=array('codetypecredit'=>'00', 'libtypecredit'=>'CNRS-UL', 'codelibtypecredit'=>'cnrsul');
// 17122014 fin
$query_rs = "SELECT codetypecredit, libcourt as libtypecredit, codelibtypecredit FROM typecredit where codetypecredit<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typecredit[$row_rs['codetypecredit']]=$row_rs;
}
// centrefinancier virtuel limites a ceux qui ont au moins un CC qui ont au moins un contrat en cours ou impute a la commande
// le tri par cf_numordre semble necessire pour eviter le bug qui arrive lors du choix de la premiere imputation virtuelle dans une commande ???
$query_rs = "(SELECT centrefinancier.codecentrefinancier,centrefinancier.codetypecredit, centrefinancier.libcourt as libcentrefinancier, centrefinancier.numordre as cf_numordre,'0' as virtuel_ou_reel".
						" FROM centrefinancier,centrecout,budg_contrat_source_vue".
						" WHERE centrefinancier.codecentrefinancier=centrecout.codecentrefinancier and centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout".
						" and centrefinancier.codecentrefinancier<>'' and centrecout.codecentrecout<>''".
						" and (budg_contrat_source_vue.codecontrat in (select codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0')".
						"  			or ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").")".
						" )".
						" UNION".
						" (SELECT centrefinancier.codecentrefinancier,centrefinancier.codetypecredit, centrefinancier.libcourt as libcentrefinancier, centrefinancier.numordre as cf_numordre,'1' as virtuel_ou_reel".
						" FROM centrefinancier,centrecout,budg_eotp_source_vue".
						" WHERE centrefinancier.codecentrefinancier=centrecout.codecentrefinancier and centrecout.codecentrecout=budg_eotp_source_vue.codecentrecout".
						" and centrefinancier.codecentrefinancier<>'' and centrecout.codecentrecout<>''".
						" and (budg_eotp_source_vue.codeeotp in (select codeeotp from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='1')".
						"  			or ".intersectionperiodes('datedeb_eotp','datefin_eotp',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").")".
						" )".
						" ORDER BY virtuel_ou_reel,cf_numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrefinancier']]=$row_rs;
	$tab_centrefinancier[$row_rs['codecentrefinancier']]['source']='non';
	$tab_centrefinancier[$row_rs['codecentrefinancier']]['contrat']='non';
}
	
// centrecout qui ont au moins un contrat ou eotp en cours
// si un contrat C1 d'un eotp E1 est fini, C2 de E1 fera apparaitre E1
// mettre condition C1=C1E1=E1 sur les vues ?
$tab_centrecout=array();
$query_rs =	"(SELECT distinct centrecout.codecentrecout,centrecout.codecentrefinancier, centrecout.libcourt as libcentrecout,centrecout.numordre as cc_numordre,'0' as virtuel_ou_reel".
					 	" FROM centrecout,budg_contrat_source_vue".
					 	" WHERE centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout and centrecout.codecentrecout<>'' and budg_contrat_source_vue.codecontrat<>''".
						" and (budg_contrat_source_vue.codecontrat in (select codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0')".
						"  			or ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").")".
						" )".
						" UNION".
						" (SELECT distinct centrecout.codecentrecout,centrecout.codecentrefinancier, centrecout.libcourt as libcentrecout,centrecout.numordre as cc_numordre,'1' as virtuel_ou_reel".
					 	" FROM centrecout,budg_eotp_source_vue".
					 	" WHERE centrecout.codecentrecout=budg_eotp_source_vue.codecentrecout and centrecout.codecentrecout<>'' and budg_eotp_source_vue.codeeotp<>''".
						" and (budg_eotp_source_vue.codeeotp in (select codeeotp from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='1')".
						"  			or ".intersectionperiodes('datedeb_eotp','datefin_eotp',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").")".
						" )".
					 	" ORDER BY virtuel_ou_reel,cc_numordre";//centrecout.numordre,
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_centrecout=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrecout[$row_rs['codecentrecout']]=$row_rs;
	$tab_centrecout[$row_rs['codecentrecout']]['source']='non';
	$tab_centrecout[$row_rs['codecentrecout']]['contrat']='non';
	$tab_centrecout[$row_rs['codecentrecout']]['codetypecredit']=$tab_centrefinancier[$row_rs['codecentrefinancier']]['codetypecredit'];	
}

$tab_contrateotp=array();
$query_rs = "SELECT codecontrat,codeeotp from budg_contrateotp_source_vue";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contrateotp[$row_rs['codecontrat']][$row_rs['codeeotp']]=$row_rs;
}

$tab_eotp=array();
// si doublon dans eotp car deux contrats, il est ecrase car cle de tab_eotp=codeeotp
// les eotp non associes a un contrat ne sont pas affiches
$query_rs = "SELECT budg_eotp_source_vue.codeeotp,typecredit.codetypecredit, centrecout.codecentrecout,centrecout_reel.libcourt as libcentrecout_reel,".
						" budg_eotp_source_vue.coderespscientifique,individu.nom as nomrespscientifique,individu.prenom as prenomrespscientifique,libeotp,eotp_ou_source,".
						" cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
						"	if(".intersectionperiodes('datedeb_eotp','datefin_eotp',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").", 'oui','non') as esteotpencours".
						" from cmd_typesource,typecredit,centrefinancier,centrecout,budg_eotp_source_vue,individu,centrecout_reel".
						" where budg_eotp_source_vue.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=typecredit.codetypecredit".
						" and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource and budg_eotp_source_vue.codeeotp<>''".
						" and budg_eotp_source_vue.coderespscientifique=individu.codeindividu".
						" and (budg_eotp_source_vue.codeeotp in (select codeeotp from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='1')".
						"  			or ".intersectionperiodes('datedeb_eotp','datefin_eotp',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
									")".
						// uniquement les eotp lies a un contrat
						"	and budg_eotp_source_vue.codeeotp in (select codeeotp from budg_contrateotp_source_vue)".
						" and budg_eotp_source_vue.codecentrecout_reel=centrecout_reel.codecentrecout_reel".
						" order by eotp_ou_source desc,typecredit.codetypecredit, centrefinancier.libcourt, centrecout.libcourt, individu.nom";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_eotp[$row_rs['codeeotp']]=$row_rs;
	$query_rs_solde = "SELECT sum(montantengage) as sommemontantengage from commandeimputationbudget where codeeotp=".GetSQLValueString($row_rs['codeeotp'], "text")." and virtuel_ou_reel='1'";
	$rs_solde=mysql_query($query_rs_solde) or die(mysql_error());
	$row_rs_solde=mysql_fetch_assoc($rs_solde);
	$tab_eotp[$row_rs['codeeotp']]['solde']=$row_rs_solde['sommemontantengage'];
	// mise en forme des libelles des eotp/source
	if($row_rs['eotp_ou_source']=='eotp') //eotp
	{ $tab_eotp[$row_rs['codeeotp']]['libeotp']=$row_rs['nomrespscientifique'].' '.substr($row_rs['prenomrespscientifique'],0,1).'. - '.$row_rs['libeotp'];
	}
	else // source
	{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																'libsource'=>$row_rs['libeotp'], 'libcentrecout_reel'=>$row_rs['libcentrecout_reel'],
																'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nomrespscientifique'],
																'prenomrespscientifique'=>$row_rs['prenomrespscientifique'],'codetypecredit'=>$row_rs['codetypecredit']);
		$tab_eotp[$row_rs['codeeotp']]['libeotp']=construitlibsource($tab_construitsource);
	}
}

// si doublon dans contrat car deux eotp, il est ecrase car cle de tab_contrat=codecontrat
// les contrat non associes a un eotp ne sont pas affiches
$tab_contrat=array();
$tab_eotp_deja_utilise=array();// pour eviter deux lib contrat identiques d'un meme eotp
$query_rs = "SELECT budg_contrat_source_vue.codecontrat,typecredit.codetypecredit, centrecout.codecentrecout,".
						" budg_contrat_source_vue.coderespscientifique,individu.nom as nomrespscientifique,individu.prenom as prenomrespscientifique,acronyme as libcontrat,contrat_ou_source,".
						" cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
						"	if(".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").", 'oui','non') as estcontratencours".
						" from cmd_typesource,typecredit,centrefinancier,centrecout,budg_contrat_source_vue,individu".
						" where budg_contrat_source_vue.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=typecredit.codetypecredit".
						" and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource and budg_contrat_source_vue.codecontrat<>''".
						" and budg_contrat_source_vue.coderespscientifique=individu.codeindividu".
						" and (budg_contrat_source_vue.codecontrat in (select codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0')".
						"  			or ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
						"			)".
						// uniquement les contrats associes a un eotp
						"	and budg_contrat_source_vue.codecontrat in (select codecontrat from budg_contrateotp_source_vue)".
						" order by contrat_ou_source desc,typecredit.codetypecredit, centrefinancier.libcourt, centrecout.libcourt, individu.nom,individu.prenom";
$rs=mysql_query($query_rs) or die(mysql_error());
//
while($row_rs=mysql_fetch_assoc($rs))
{ $codecontrat=$row_rs['codecontrat'];
	// il y a forcement un codeeotp du fait du select avec la condition budg_contrat_source_vue.codecontrat in (select codecontrat from budg_contrateotp_source_vue)
	// il peut y avoir plus d'un eotp pour le contrat
	list($codeeotp,) = each($tab_contrateotp[$codecontrat]);
	if(isset($tab_eotp_deja_utilise[$codeeotp]))
	{ // contrat dans meme eotp qu'un autre contrat : il ne sera pas liste. Les eotp d'un meme contrat restent
		foreach($tab_commandeimputationbudget['0'] as $un_numordre=>$un_commandeimputationbudget_virtuel)
		{ if($un_commandeimputationbudget_virtuel['codecontrat']==$codecontrat)
			{ unset($tab_contrat[$tab_eotp_deja_utilise[$codeeotp]]);
				$tab_contrat[$codecontrat]=$row_rs;
			}
		}
	} 
	else
	{ $tab_eotp_deja_utilise[$codeeotp]=$codecontrat;
		$tab_contrat[$codecontrat]=$row_rs;
	}
}
foreach($tab_contrat as $codecontrat=>$row_rs)
{	// 17122014 marque les centrecout (enveloppes) comme contrat ou source (dotations ulcnrs)
	$codecentrecout=$row_rs['codecentrecout'];
	$codecentrefinancier=$tab_centrecout[$codecentrecout]['codecentrefinancier'];
	if(isset($tab_centrecout[$codecentrecout]))
	{	// un centre financier ou de cout est de type source s'il contient au moins une source et de type contrat s'il contient au moins un contrat : permettra de categoriser les credits CNRS-UL en demande
		if($row_rs['contrat_ou_source']=='source')
		{ $tab_centrefinancier[$codecentrefinancier]['source']='oui';
			$tab_centrecout[$codecentrecout]['source']='oui';
		}
		else
		{	$tab_centrefinancier[$codecentrefinancier]['contrat']='oui';
			$tab_centrecout[$codecentrecout]['contrat']='oui';
		}
	}
	// 17122014 fin
	$query_rs_solde = "SELECT sum(montantengage) as sommemontantengage from commandeimputationbudget where codecontrat=".GetSQLValueString($codecontrat, "text")." and virtuel_ou_reel='0'";
	$rs_solde=mysql_query($query_rs_solde) or die(mysql_error());
	$row_rs_solde=mysql_fetch_assoc($rs_solde);
	$tab_contrat[$codecontrat]['solde']=$row_rs_solde['sommemontantengage'];
	if($row_rs['contrat_ou_source']=='contrat') //contrat
	{ $tab_contrat[$codecontrat]['libcontrat']=$row_rs['nomrespscientifique'].' '.substr($row_rs['prenomrespscientifique'],0,1).'. - '.$row_rs['libcontrat'];
	}
	else // source
	{ reset($tab_contrateotp[$codecontrat]);
		list($codeeotp,) = each($tab_contrateotp[$codecontrat]);
		$tab_contrat[$codecontrat]['libcentrecout_reel']='';
		if(isset($tab_eotp[$codeeotp]))
		{ $libcentrecout_reel=$tab_eotp[$codeeotp]['libcentrecout_reel'];
		}
		//enleve l'affichage du libcentrecout_reel en virtuel
		$tab_construitsource=array('codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																'libsource'=>$row_rs['libcontrat'],'libcentrecout_reel'=>''/* $libcentrecout_reel */,
																'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nomrespscientifique'],
																'prenomrespscientifique'=>$row_rs['prenomrespscientifique'],'codetypecredit'=>$row_rs['codetypecredit']);
																									
		$tab_contrat[$codecontrat]['libcontrat']=construitlibsource($tab_construitsource);
	}

}
// JUSTIFICATION : liste des contrats (pas les sources) selon le type de credit qui peuvent etre justifies par cette commande
$query_rs = "SELECT contrat.codecontrat,codeeotp,".
						" concat(respscientifique.nom,' ',substring(respscientifique.prenom,1,1),'. - ',acronyme) as libcontrat".
						" from contrateotp,cont_orggesttypecredit,contrat".
						" left join individu as respscientifique on contrat.coderespscientifique=respscientifique.codeindividu".
						" where  ((cont_orggesttypecredit.codeorggest=contrat.codeorggest and cont_orggesttypecredit.codetypecredit=".GetSQLValueString($tab_commandeimputationbudget['0']['01']['codetypecredit'], "text").
						" 				and ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
						"					)".
						"  				or  contrat.codecontrat in (select codecontrat from commandejustifiecontrat where codecommande=".GetSQLValueString($codecommande, "text").")".
						"				)".
						" and contrateotp.codecontrat=contrat.codecontrat".
						" UNION select '' as codecontrat,'' codeeotp,'' as libcontrat from contrat".
						" order by libcontrat asc";
$tab=array();						
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(isset($tab[$row_rs['codeeotp']]))
	{ // contrat dans meme eotp qu'un autre contrat
	}
	else
	{ $tab_contrat_a_justifier[$row_rs['codecontrat']]=$row_rs;
		$tab[$row_rs['codeeotp']]=$row_rs['codeeotp'];
	}
}
// Liste des themes
$rs_theme = mysql_query("select codestructure as codetheme,libcourt_fr as libtheme".
												" from structure where codestructure<>'00' and esttheme='oui'".
												" and ".intersectionperiodes('date_deb','date_fin',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
												" order by codestructure") or die(mysql_error());	

// Liste des membres y compris le demandeur si parti
$query_rs_referent="SELECT distinct individu.codeindividu as codereferent,nom,prenom,concat(nom,'. ',substr(prenom,1,1)) as nomprenom".
									 " FROM individu,individusejour".
									 " WHERE individu.codeindividu=individusejour.codeindividu".
									 " and (individu.codeindividu in (select codereferent from commande where codecommande=".GetSQLValueString($codecommande, "text").")".
									 " 			or ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
									 ($est_cmd_de_miss?
									 "       or individu.codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text"):
									 "").
									 			")".
									 " UNION select '' as codereferent,'' as nom,'' as prenom, '' as nomprenom from individu where codeindividu=''".
									 " ORDER BY nom,prenom";
$rs_referent=mysql_query($query_rs_referent) or die(mysql_error());

// Liste des natures
// modif 01/2014 pour pouvoir avoir la nature '' pour les missions
$rs_nature = mysql_query("select codenature,codetypecredit,libcourt as libnature from cmd_nature where codenature<>'' order by codetypecredit, libnature") or die(mysql_error());	
$tab_nature=array();
while($row_rs_nature=mysql_fetch_assoc($rs_nature))
{ $tab_nature[$row_rs_nature['codenature']]=$row_rs_nature;
}
// Liste des natures dialogue gestion
$rs_dialoguegestion = mysql_query("select codedialoguegestion,codetypecredit,libcourt as libdialoguegestion from cmd_dialoguegestion order by codetypecredit, libdialoguegestion") or die(mysql_error());	
$tab_dialoguegestion=array();
while($row_rs_dialoguegestion=mysql_fetch_assoc($rs_dialoguegestion))
{ $tab_dialoguegestion[$row_rs_dialoguegestion['codedialoguegestion']]=$row_rs_dialoguegestion;
}
// secr site : tous ou celui qui a cree la mission => tout le monde peut ajouter une commande de mission mais la secr de site de la mission est secr site de la commande
$query_rs_secrsite="SELECT distinct codesecrsite,concat(substr(prenom,1,1),'. ',nom) as nomprenom". 
									 " FROM individu,secrsite".
									 " WHERE individu.codeindividu=secrsite.codesecrsite".
									 " ORDER BY prenom,nom";
$rs_secrsite=mysql_query($query_rs_secrsite) or die(mysql_error());
// gestsif
$query_rs_gestsif="SELECT individu.codeindividu,concat(substr(prenom,1,1),'. ',nom) as nomprenom". 
									 " FROM individu,structureindividu,structure".
									 " WHERE individu.codeindividu=structureindividu.codeindividu".
									 " and structureindividu.codestructure=structure.codestructure and structure.codelib='sif'".
									 " ORDER BY prenom,nom";
$rs_gestsif=mysql_query($query_rs_gestsif) or die(mysql_error());
$tab_contexte['codesecrsite']=$row_rs_commande['codesecrsite'];
// 20170405
// Liste des membres y compris les destinataires si partis
$query_rs="SELECT distinct individu.codeindividu as codedestinataire,nom,prenom,concat(nom,'. ',substr(prenom,1,1)) as nomprenom,codelieu, num_bureau".
									 " FROM individu,individusejour".
									 " WHERE individu.codeindividu=individusejour.codeindividu".
									 " and (individu.codeindividu in (select codedestinataire from commandeinventaire where codecommande=".GetSQLValueString($codecommande, "text").")".
									 " 			or ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
									 "			or individu.codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text").// 20170412
												")".
									 " UNION select '' as codedestinataire,'' as nom,'' as prenom, '' as nomprenom, '' as codelieu, '' as num_bureau from individu where codeindividu=''".
									 " ORDER BY nom,prenom";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_destinataire=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_destinataire[$row_rs['codedestinataire']]=$row_rs;
}
$query_rs = "SELECT distinct codelieu,libcourtlieu as liblieu FROM lieu".
								" WHERE (codelieu in (select codelieu from commandeinventaire where codecommande=".GetSQLValueString($codecommande, "text").")".
									 " 			or ".intersectionperiodes('date_deb','date_fin',"'".$row_rs_commande['datecommande']."'","'".$row_rs_commande['datecommande']."'").
									 			")".
								" order by codelieu";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_lieu=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_lieu[$row_rs['codelieu']]=$row_rs;
}// 20170405
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Commande</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script language="javascript">
var nbtypecredit=0;nbcentrefinancier=0;nbcentrecout=0;nbeotp=0;nbnature=0;nbdialoguegestion=0;
tab=new Array();
var tab_typecredit=new Array();
var tab_centrefinancier=new Array();
var tab_centrecout=new Array();
tab_cc=new Array();
var tab_contrat=new Array();
var tab_eotp=new Array();
//var tab_contrateotp=new Array();
var tab_nature=new Array();
var tab_dialoguegestion=new Array();
// 20170405
var tab_destinataire=new Array();
<?php
$nb=0;
foreach($tab_destinataire as $codedestinataire=>$un_destinataire)
{ ?>var o=new Object();
	o["nomprenom"]="<?php echo js_tab_val($un_destinataire['nomprenom']); ?>";
	o["codelieu"]="<?php echo js_tab_val($un_destinataire['codelieu']) ?>";
	o["num_bureau"]="<?php echo js_tab_val($un_destinataire['num_bureau']) ?>";
	tab_destinataire["<?php echo $codedestinataire ?>"]=o;
<?php 
	$nb++; 
}
?>
nbdestinataire=<?php echo $nb; ?>;

var tab_lieu=new Array();
<?php // le codelieu est associe a l'index de la liste select 
$nb=0;
foreach($tab_lieu as $codelieu=>$un_lieu)// nb est l'index dans la liste select des lieux
{ ?>
	tab_lieu["<?php echo $codelieu; ?>"]=<?php echo $nb; ?>;
<?php 
	$nb++; 
}
?>
nblieu=<?php echo $nb; ?>;

function detaildestinataire(champ)
{ var frm=document.forms["<?php echo $form_commande ?>"];
	posdiese=champ.name.indexOf('#');
	codeinventaire=champ.name.substr(posdiese+1,champ.name.length-(posdiese+1));
	codedestinataire=champ.value
	un_destinataire=tab_destinataire[codedestinataire];
	if(frm.elements["codelieu#"+codeinventaire]){frm.elements["codelieu#"+codeinventaire].selectedIndex=tab_lieu[un_destinataire.codelieu];}
	if(frm.elements["num_bureau#"+codeinventaire]){frm.elements["num_bureau#"+codeinventaire].value=un_destinataire.num_bureau;}
}
// 20170405

<?php 
$nb=0;
foreach($tab_typecredit as $codetypecredit=>$row_rs_typecredit)
{?> var o=new Object();
	o["codetypecredit"]="<?php echo $row_rs_typecredit['codetypecredit']; ?>";
	o["libtypecredit"]="<?php echo js_tab_val($row_rs_typecredit['libtypecredit']) ?>";
	tab_typecredit[<?php echo $nb ?>]=o;
<?php 
	$nb++; 
}
?>
nbtypecredit=<?php echo $nb; ?>;
<?php 
$nb=0;
foreach($tab_centrefinancier as $codecentrefinancier=>$row_rs_centrefinancier)
{?> 
	var o=new Object();
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_centrefinancier['codetypecredit']) ?>";
	o["codecentrefinancier"]="<?php echo js_tab_val($row_rs_centrefinancier['codecentrefinancier']); ?>";
	o["libcentrefinancier"]="<?php echo js_tab_val($row_rs_centrefinancier['libcentrefinancier']) ?>";
	o["virtuel_ou_reel"]="<?php echo js_tab_val($row_rs_centrefinancier['virtuel_ou_reel']) ?>";
	// 17122014
	o["source"]="<?php echo js_tab_val($row_rs_centrefinancier['source']) ?>";
	o["contrat"]="<?php echo js_tab_val($row_rs_centrefinancier['contrat']) ?>";
	// 17122014 fin
	tab_centrefinancier[<?php echo $nb ?>]=o;
<?php 
	$nb++; 
}
?>
nbcentrefinancier=<?php echo $nb; ?>;
<?php
$nb=0;
foreach($tab_centrecout as $codecentrecout=>$row_rs_centrecout)
{ ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_centrecout['codecentrecout']); ?>";
	o["codecentrefinancier"]="<?php echo js_tab_val($row_rs_centrecout['codecentrefinancier']); ?>";
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_centrecout['codetypecredit']) ?>";
	o["libcentrecout"]="<?php echo js_tab_val($row_rs_centrecout['libcentrecout']); ?>";
	o["virtuel_ou_reel"]="<?php echo js_tab_val($row_rs_centrecout['virtuel_ou_reel']) ?>";
	// 17122014
	o["source"]="<?php echo js_tab_val($row_rs_centrecout['source']) ?>";
	o["contrat"]="<?php echo js_tab_val($row_rs_centrecout['contrat']) ?>";
	// 17122014 fin
	tab_centrecout[<?php echo $nb ?>]=o;
	<?php
	$nb++; 
}?>
nbcentrecout=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_contrat as $codecontrat=>$row_rs_contrat)
{  ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_contrat['codecentrecout']); ?>";
	o["codecontrat"]="<?php echo js_tab_val($row_rs_contrat['codecontrat']); ?>";
	o["libcontrat"]="<?php echo js_tab_val($row_rs_contrat['libcontrat']);?>"; 
	o["solde"]="<?php echo js_tab_val($row_rs_contrat['solde']);?>";
	o["contrat_ou_source"]="<?php echo js_tab_val($row_rs_contrat['contrat_ou_source']);?>";
	tab_contrat[<?php echo $nb ?>]=o;
	
<?php
	$nb++;
}?>
nbcontrat=<?php echo $nb; ?>;


<?php 
$nb=0;
foreach($tab_eotp as $codeeotp=>$row_rs_eotp)
{  ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_eotp['codecentrecout']); ?>";
	o["codeeotp"]="<?php echo js_tab_val($row_rs_eotp['codeeotp']); ?>";
	o["libeotp"]="<?php echo js_tab_val($row_rs_eotp['libeotp']);?>"; 
	o["solde"]="<?php echo js_tab_val($row_rs_eotp['solde']);?>";
	o["eotp_ou_source"]="<?php echo js_tab_val($row_rs_eotp['eotp_ou_source']);?>";
	tab_eotp[<?php echo $nb ?>]=o;

<?php 
	$nb++;
}?>
nbeotp=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_nature as $codenature=>$row_rs_nature)
{  ?> 
	var o=new Object();
	o["codenature"]="<?php echo js_tab_val($row_rs_nature['codenature']); ?>";
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_nature['codetypecredit']); ?>";
	o["libnature"]="<?php echo js_tab_val($row_rs_nature['libnature']); ?>";
	tab_nature[<?php echo $nb ?>]=o;
<?php 
	$nb++;
}?>
nbnature=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_dialoguegestion as $codedialoguegestion=>$row_rs_dialoguegestion)
{  ?> 
	var o=new Object();
	o["codedialoguegestion"]="<?php echo js_tab_val($row_rs_dialoguegestion['codedialoguegestion']); ?>";
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_dialoguegestion['codetypecredit']); ?>";
	o["libdialoguegestion"]="<?php echo js_tab_val($row_rs_dialoguegestion['libdialoguegestion']); ?>";
	tab_dialoguegestion[<?php echo $nb ?>]=o;
<?php 
	$nb++;
}?>
nbdialoguegestion=<?php echo $nb; ?>;

function affichestructure(champ)
{ // liste(s) des sous-structures d'une structure
	// le nom du champ, hors codetypecredit, indique les listes select concernées
	var frm=document.forms["<?php echo $form_commande ?>"];
	var valchamp=champ.value;
	firstcentrefinancier=true;
	firstcentrecout=true;
	firstcontrat=true;
	firsteotp=true;
	numeotp=-1;
	// si champ = type de credit (UL ou CNRS)
 	if(champ.name.substring(0,(new String('codetypecredit#')).length) =='codetypecredit#')
	{ // si le typecredit='', c'est qu'il s'agit du premier choix de type de credit en creation : on enleve l'element vide et on décale l'index de l'élement choisi
		posdiese=champ.name.indexOf('#');
		typecredit_virtuel_ou_reel=champ.name.substr(posdiese+1,1);
		listetypecredit=frm.elements[champ.name];
		if(listetypecredit.options[0].value=='')
		{ index=listetypecredit.selectedIndex;
			listetypecredit.options.length=0;
			for(i=0;i<nbtypecredit;i++)
			{ if(typecredit_virtuel_ou_reel=='0' || (typecredit_virtuel_ou_reel=='1' && tab_typecredit[i].codetypecredit!='00'))
				{ listetypecredit.options[listetypecredit.options.length]=new Option(tab_typecredit[i].libtypecredit, tab_typecredit[i].codetypecredit);
				}
			}
			listetypecredit.selectedIndex=index-1;
		}
		// en demande, on recupere le numordre de credit choisi numordre_virtuel
		if(typecredit_virtuel_ou_reel=='0')
		{ posdoublediese=champ.name.indexOf('##');
			numordre_virtuel=champ.name.substr(posdoublediese+2,champ.name.length-(posdoublediese+2));
		}
		else // en reel on initialise les listes nature et dialogue gestion
		{ listenature=frm.elements['codenature'];
			listenature.options.length=0;
			firstnature=true;
			for(i=0;i<nbnature;i++)
			{ if(tab_nature[i].codetypecredit==valchamp)
				{ listenature.options[listenature.options.length]=new Option(tab_nature[i].libnature, tab_nature[i].codenature);
				} 
			}
			listedialoguegestion=frm.elements['codedialoguegestion'];
			listedialoguegestion.options.length=0;
			listedialoguegestion.options[0]=new Option("","");
			firstdialoguegestion=true;
			for(i=0;i<nbdialoguegestion;i++)
			{ if(tab_dialoguegestion[i].codetypecredit==valchamp)
				{ listedialoguegestion.options[listedialoguegestion.options.length]=new Option(tab_dialoguegestion[i].libdialoguegestion, tab_dialoguegestion[i].codedialoguegestion);
				} 
			}
		}
		// parcours de tous les elements du formulaire pour acceder aux listes centre financier (types credits), centre cout (enveloppe), contrats/sources ou eotp/sources 
		for(numelement=0;numelement<frm.elements.length;numelement++)
		{ // liste des centres financiers 
			firstcentrefinancier=true;
			firstcentrecout=true;
			if(frm.elements[numelement].name.substring(0,(new String('codecentrefinancier#')).length) =='codecentrefinancier#')
			{ listecentrefinancier=frm.elements[numelement];
				posdiese=listecentrefinancier.name.indexOf('#');
				posdoublediese=listecentrefinancier.name.indexOf('##');
				virtuel_ou_reel=listecentrefinancier.name.substr(posdiese+1,posdoublediese-posdiese-1);
				// s'il s'agit des centres financiers du type de credit selectionne
				if(virtuel_ou_reel==typecredit_virtuel_ou_reel)
				{ numordre=listecentrefinancier.name.substr(posdoublediese+2,listecentrefinancier.name.length-(posdoublediese+2));
					// en demande, uniquement pour la ligne numordre_virtuel d'imputation du credit selectionne. Les autres lignes ne sont pas affectees
					if(virtuel_ou_reel==0 && numordre==numordre_virtuel)
					{ listecentrefinancier.options.length=0;
						listecentrecout=frm.elements["codecentrecout#"+virtuel_ou_reel+"##"+numordre];
						listecentrecout.options.length=0;//listecentrecout.options[0]=new Option("","");
						listecontrat=frm.elements["codecontrat#0##"+numordre];
						listecontrat.options.length=0;
						if(frm.elements["codetypecredit#0##"+numordre].value=='00')// CNRS-UL
						{	for(i=0;i<nbcentrefinancier;i++)
							{ if(tab_centrefinancier[i].source=='oui' && tab_centrefinancier[i].codetypecredit=='02')//affichage types credits UL
								{	if(firstcentrefinancier)
									{ valcentrefinancier=tab_centrefinancier[i].codecentrefinancier;
										firstcentrefinancier=false;
									}
									listecentrefinancier.options[listecentrefinancier.options.length]=new Option(tab_centrefinancier[i].libcentrefinancier, tab_centrefinancier[i].codecentrefinancier);
								}
							}
							for(i=0;i<nbcentrecout;i++)
							{ if(tab_centrecout[i].source=='oui')
								{ if(tab_centrecout[i].codecentrefinancier==valcentrefinancier)
									{ if(firstcentrecout)// bug edit_commande car etait en commentaire !!!
										{ valcentrecout=tab_centrecout[i].codecentrecout;
											firstcentrecout=false;
										}
										listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
									}
								}
							}
							for(i=0;i<nbcontrat;i++)
							{ if(tab_contrat[i].codecentrecout==valcentrecout && tab_contrat[i].contrat_ou_source=='source')// seuls les sources, pas les contrats
								{ if(firstcontrat)
									{ valcontrat=tab_contrat[i].codecontrat;
										firstcontrat=false;
									}
									listecontrat.options[listecontrat.options.length]=new Option(tab_contrat[i].libcontrat, tab_contrat[i].codecontrat);
								}
							}
						}
						else//codetypecredit!='00' <=> UL ou CNRS (pas CNRS+UL)
						{	for(i=0;i<nbcentrefinancier;i++)
							{ if(tab_centrefinancier[i].contrat=='oui')
								{	if(tab_centrefinancier[i].codetypecredit==valchamp)
									{ if(firstcentrefinancier)
										{ valcentrefinancier=tab_centrefinancier[i].codecentrefinancier;
											firstcentrefinancier=false;
										}
										listecentrefinancier.options[listecentrefinancier.options.length]=new Option(tab_centrefinancier[i].libcentrefinancier, tab_centrefinancier[i].codecentrefinancier);
									}
								}
							}
							for(i=0;i<nbcentrecout;i++)
							{ if(tab_centrecout[i].contrat=='oui')
								{ if(tab_centrecout[i].codecentrefinancier==valcentrefinancier)
									{ if(firstcentrecout)
										{ valcentrecout=tab_centrecout[i].codecentrecout;
											firstcentrecout=false;
										}
										listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
									}
								}
							}
							for(i=0;i<nbcontrat;i++)// seuls les contrats, pas les sources
							{ if(tab_contrat[i].codecentrecout==valcentrecout && tab_contrat[i].contrat_ou_source=='contrat')
								{ if(firstcontrat)
									{ valcontrat=tab_contrat[i].codecontrat;
										firstcontrat=false;
									}
									listecontrat.options[listecontrat.options.length]=new Option(tab_contrat[i].libcontrat, tab_contrat[i].codecontrat);
								}
							}
						}
					}
					else if(virtuel_ou_reel==1)//reel
					{	listecentrefinancier.options.length=0;//listecentrefinancier.options[0]=new Option("","");
						listecentrecout=frm.elements["codecentrecout#"+virtuel_ou_reel+"##"+numordre];
						listecentrecout.options.length=0;//listecentrecout.options[0]=new Option("","");
						listeeotp=frm.elements["codeeotp#1##"+numordre];
						listeeotp.options.length=0;
						for(i=0;i<nbcentrefinancier;i++)
						{ if(tab_centrefinancier[i].codetypecredit==valchamp)
							{ if(firstcentrefinancier)
								{ valcentrefinancier=tab_centrefinancier[i].codecentrefinancier;
									firstcentrefinancier=false;
								}
								listecentrefinancier.options[listecentrefinancier.options.length]=new Option(tab_centrefinancier[i].libcentrefinancier, tab_centrefinancier[i].codecentrefinancier);
							}
						}
						for(i=0;i<nbcentrecout;i++)
						{ if(tab_centrecout[i].virtuel_ou_reel==virtuel_ou_reel)
							{ if(tab_centrecout[i].codecentrefinancier==valcentrefinancier)
								{ if(firstcentrecout)
									{ valcentrecout=tab_centrecout[i].codecentrecout;
										firstcentrecout=false;
									}
									listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
								}
							}
						}
						for(i=0;i<nbeotp;i++)
						{ if(tab_eotp[i].codecentrecout==valcentrecout)
							{ if(firsteotp)
								{ valeotp=tab_eotp[i].codeeotp;
									firsteotp=false;
								}
								listeeotp.options[listeeotp.options.length]=new Option(tab_eotp[i].libeotp, tab_eotp[i].codeeotp);
								numeotp=i-1;
							}
						} 
					}
				} 
			}
		}
	}
 	else
	{	posdiese=champ.name.indexOf('#');
		nomchamp=champ.name.substr(0,posdiese);
		posdoublediese=champ.name.indexOf('##');
		virtuel_ou_reel=champ.name.substr(posdiese+1,posdoublediese-posdiese-1);
		numordre=champ.name.substr(posdoublediese+2,champ.name.length-(posdoublediese+2));
		listecentrecout=frm.elements["codecentrecout#"+virtuel_ou_reel+"##"+numordre];
		if(virtuel_ou_reel==0)// en demande
		{	listecontrat=frm.elements["codecontrat#0##"+numordre];
			listecontrat.options.length=0;
			if(frm.elements["codetypecredit#0##"+numordre].value=='00')//on vient de selectionnner un centre financier ou un centre de cout CNRS+UL
			{	if(nomchamp=="codecentrefinancier")// selection d'un centre financier
				{ listecentrecout.options.length=0;
					for(i=0;i<nbcentrecout;i++)
					{ if(tab_centrecout[i].source=='oui' && tab_centrecout[i].codetypecredit=='02')//affichage enveloppes du codetypecredit UL
						{ 
							if(tab_centrecout[i].codecentrefinancier==valchamp /* && !(tab_centrecout[i].codecentrefinancier=='02' && tab_centrecout[i].codecentrecout=='00021') */)
							{ if(firstcentrecout)
								{ valcentrecout=tab_centrecout[i].codecentrecout;
									firstcentrecout=false;
								}
								listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
							}
						}
					}
					nomchamp="codecentrecout";
					valchamp=valcentrecout;
				}
			}
			else //valchamp!='00'
			{	if(nomchamp=="codecentrefinancier")
				{ listecentrecout.options.length=0;
					for(i=0;i<nbcentrecout;i++)
					{ if(tab_centrecout[i].contrat=='oui')
						{ if(tab_centrecout[i].codecentrefinancier==valchamp)
							{ if(firstcentrecout)
								{ valcentrecout=tab_centrecout[i].codecentrecout;
									firstcentrecout=false;
								}
								listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
							}
						}
					}
					nomchamp="codecentrecout";
					valchamp=valcentrecout;
				}
			}
			
			if(nomchamp=="codecentrecout")// si on vient de traiter le centre financier ou si on a selectionne un centre de cout
			{ if(frm.elements["codetypecredit#0##"+numordre].value=='00')//CNRS+UL
				{ contrat_ou_source='source'
				}
				else
				{ contrat_ou_source='contrat'
				}
				for(i=0;i<nbcontrat;i++)
				{ if(tab_contrat[i].codecentrecout==valchamp && tab_contrat[i].contrat_ou_source==contrat_ou_source)
					{ if(firstcontrat)
						{ valcontrat=tab_contrat[i].contrat;
							firstcontrat=false;
						}
						listecontrat.options[listecontrat.options.length]=new Option(tab_contrat[i].libcontrat, tab_contrat[i].codecontrat);
					} 
				}
			}
		}
		else//reel
		{	listeeotp=frm.elements["codeeotp#1##"+numordre];
			listeeotp.options.length=0;
			if(nomchamp=="codecentrefinancier")
			{ listecentrecout.options.length=0;
				for(i=0;i<nbcentrecout;i++)
				{ if(tab_centrecout[i].virtuel_ou_reel==virtuel_ou_reel)
					{ if(tab_centrecout[i].codecentrefinancier==valchamp)
						{ if(firstcentrecout)
							{ valcentrecout=tab_centrecout[i].codecentrecout;
								firstcentrecout=false;
							}
							listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
						}
					}
				}
				nomchamp="codecentrecout";
				valchamp=valcentrecout;
			} 
			if(nomchamp=="codecentrecout")
			{ for(i=0;i<nbeotp;i++)
				{ if(tab_eotp[i].codecentrecout==valchamp)
					{ if(firsteotp)
						{ valeotp=tab_eotp[i].eotp;
							firsteotp=false;
						}
						listeeotp.options[listeeotp.options.length]=new Option(tab_eotp[i].libeotp, tab_eotp[i].codeeotp);
						numeotp=i-1;
					} 
				}
			}
		}
	}
}
// 20170427
function c(codecmdmiss,action,codevisa_a_apposer)//confirmer action commande/mission
{ txt_confirm='';
	if(action=='s')
	{ action='supprimer';
		txt_confirm='Supprimer ?';
	}
	else if(action=='i')
	{ action='invalider_visa';
		txt_confirm='Invalider ?';	
	}
	else if(action=='msrh')
	{ action='valider_mail_srh';
		txt_confirm='Valider ?';
		codevisa_a_apposer="04";	
	}
	else if(action=='confirmmail')
	{ action='valider_mail_visa_revalide';
		txt_confirm='Valider ?';	
	}

	if(confirm(txt_confirm)) 	
	{ if(codecmdmiss.substring(0,1)=='C')//C=commande uniquement
		{ document.location.href="confirmer_action_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&action="+action+"&cmd_ou_miss=commande&cmd_ancre="+codecmdmiss+"&codevisa_a_apposer="+codevisa_a_apposer+"&prog_appel=<?php echo $tab_contexte['prog'] ?>";
		}
		else
		{ document.location.href="confirmer_action_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&action="+action+"&cmd_ou_miss=mission&cmd_ancre="+codecmdmiss+"&prog_appel=<?php echo $tab_contexte['prog'] ?>";
		}
	}
}

function p(action,codecmdmiss,val_champ_a_renseigner)//post val du formulaire
{ frm=document.forms["<?php echo $form_commande ?>"];
	txt_confirm='';
	if(codecmdmiss.substring(0,1)=='C')
	{ cmd_ou_miss='commande';
	}
	else
	{ cmd_ou_miss='mission';
	}
	if(action=='a')
	{ action='annuler';
		champ_a_renseigner='estannule';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Annuler la '+cmd_ou_miss+' ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Retablir la '+cmd_ou_miss+' ?';
		}
	}
	else if (action=='av')
	{ action='avoir';
		champ_a_renseigner='estavoir';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Creer un avoir ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Retablir cette commande sans avoir ?';
		}
	}
	else if (action=='v')
	{ action='valider';
		champ_a_renseigner='codevisa_a_apposer';
		txt_confirm='Valider ?'
	}
	else if(action=='ti')
	{ action='traite_ieb';
		champ_a_renseigner='traite_ieb';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Cocher fin de traitement IEB  ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Annuler fin de traitement IEB ?';
		}
	}
	// 20170412
	else if(action=='inv')
	{ action='inventorier';
		champ_a_renseigner='estinventorie';
		val_champ_a_renseigner='oui'
		txt_confirm='Fin d\'inventaire pour cette commande ?';
	}
	// 20170412
	frm.elements['cmd_ou_miss'].value=cmd_ou_miss;
	frm.elements['code'+cmd_ou_miss].value=codecmdmiss.substring(1,codecmdmiss.length);
	frm.elements['action'].value=action;
	// 20170427 enleve car ces parametres existent deja dans edit_commande
	frm.elements['MM_update'].value='';
	//frm.elements['cmd_ancre'].value=codecmdmiss;
	// 20170427
	frm.elements[champ_a_renseigner].value=val_champ_a_renseigner;
	if(confirm(txt_confirm))
	{ frm.submit();
	}
}
// 20170427

</script>
</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>>
<form name="<?php echo $form_commande ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data"  onSubmit="return controle_form_commande('<?php echo $form_commande ?>')"><!-- -->
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="codecommande" value="<?php echo $codecommande ?>" >
<input type="hidden" name="nbimputationbudget_virtuel" value="<?php echo count($tab_commandeimputationbudget['0']); ?>" >
<input type="hidden" name="nbimputationbudget_reel" value="<?php echo count($tab_commandeimputationbudget['1']); ?>" >
<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="time" value="<?php echo $_SESSION['time'] ?>">
<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
<?php // 20170417?>
           <!-- <input type="hidden" name="action" id="action" value="">
            <input type="hidden" name="codecommande" id="codecommande" value="">
            <input type="hidden" name="codemission" id="codemission" value=""> -->
            <input type="hidden" name="codevisa_a_apposer" id="codevisa_a_apposer" value="">
            <!--<input type="hidden" name="cmd_ancre" id="cmd_ancre" value=""> -->
            <input type="hidden" name="cmd_ou_miss" id="cmd_ou_miss" value="">
            <input type="hidden" name="estannule" id="estannule" value="">
            <input type="hidden" name="estavoir" id="estavoir" value="">
           <input type="hidden" name="traite_assurance" id="traite_assurance" value="">
           	<input type="hidden" name="traite_ieb" id="traite_ieb" value="">
           	<input type="hidden" name="estinventorie" id="estinventorie" value="">
<?php // 20170417?>
<input type="image" src="images/espaceur.gif" width="0" height="0"> <!-- bouton submit par defaut pour ne pas en declencher un autre avec touche ENTREE-->
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Commande','lienretour'=>'gestioncommandes.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Retour &agrave; la gestion des commandes',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'commande','erreur_envoimail'=>$erreur_envoimail)); // 20170417 
	// 20170412
	$droitmodif=true;
	if(!estrole('sif',$tab_roleuser) && !estrole('du',$tab_roleuser))
	{ if($visa_secrsite_appose)
		{ $droitmodif=false;
		}
	}
	// 20170412
	?>
  <tr>
    <td>
    </td>
  </tr>
  <tr>
    <td>
      <span class="bleucalibri9">N&deg; interne : </span>
      <span class="mauvegrascalibri9"><?php echo $action=='creer'?'A cr&eacute;er':$row_rs_commande['codecommande'] ?></span>
    </td>
  </tr>
  <tr>
  	<td>
    	<table>
      	<tr>
          <td nowrap  colspan="4">
            <span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
            <span class="infomauve"><?php echo ($row_rs_commande['codecreateur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_commande['createurprenom']." ".$row_rs_commande['createurnom']); ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_commande['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_commande['date_creation'],"/")) ?></span>
            <img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
            <span class="infomauve"><?php echo ($row_rs_commande['codemodifieur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_commande['modifieurprenom']." ".$row_rs_commande['modifieurnom']); ?></span><span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_commande['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_commande['date_modif'],"/")) ?></span>
          </td>
          <td  colspan="3">
					<?php
					if($row_rs_commande['note']!='')
					{  ?><span class="rougegrascalibri10">Note associ&eacute;e en bas de formulaire</span> 
          <?php 
					}?>
        	</td>
					<?php // 20170427 
          if($_SESSION['b_cmd_etre_admin'] && $action!="creer")
          { ?>
        <td rowspan="2">			
          <table>
          	<tr>
              <td class="bleugrascalibri10">Dem.</td>
              <td class="bleugrascalibri10">Resp.</td>
              <td class="bleugrascalibri10">IEB1</td>
              <td class="bleugrascalibri10">Secr.</td>
              <td class="bleugrascalibri10">IEB2</td>
            </tr>
						<?php
            $row_rs_commande['tab_resp_roleuser']= $tab_resp_roleuser;
            $row_rs_commande['cmd_ou_miss']='commande';
            $row_rs_commande['codecmdmiss']='C'.$row_rs_commande['codecommande'];
            $tab_cmd_statutvisa_texte_visa_title=get_cmd_statutvisa_texte_visa_title();//titres a mettre pour les images de visas title=""
            echo affiche_ligne_cmd_ou_miss($row_rs_commande,$num_ordonne=0,/* $tab_ordonne_commande_par_etat */ array(),/* $tab_commandemigo */ array(),/* $tab_commandeinventaire */ array(), $tab_cmd_statutvisa, $tab_cmd_statutvisa_texte_visa_title, $tab_contexte,$class='odd',$cmd_ancre,$numrow=0,/* $mini= */false,$codeuser,$tab_roleuser) 
            ?>
          </table>
        </td>
          <?php 
					} // 20170427 ?>
       </tr>
       <tr>
        <td nowrap><span class="<?php echo ($row_rs_commande['estreferentpresent']=='non' && $droitmodif)?'rougegrascalibri10':'bleugrascalibri10' ?>">Demandeur :</span>
        </td>
        <td nowrap>
        	<?php
																		/* pas mission liee ?	!
																													!
					liste preselect referent !		sif ou du ?				!
																													!
					liste preselect referent !		visa ieb ?	 			\/
																												NON
					referent !										referent!='' ?

					referent !								liste preselect secrsite !
					 */
					$estvisa_sif1_appose=(array_key_exists("sif#1",get_cmd_visas($codecommande,$tab_cmd_statutvisa)));
					if(!$est_cmd_de_miss || estrole('sif',$tab_roleuser) ||  estrole('du',$tab_roleuser))// si pas commande de mission (ou sif ou du peuvent modifier referent commande de mission)
					{ if($row_rs_commande['estreferentpresent']=='non' && $droitmodif)
						{ ?><img src="images/b_attention.png" width="16" height="16" id="sprytrigger_info_referent_parti">
              <div class="tooltipContent_cadre" id="info_referent_parti">
                  <span class="noircalibri10">
                    Attention : <?php echo $row_rs_commande['referentprenom'] ?> <?php echo $row_rs_commande['referentnom'] ?> a quitt&eacute; l&rsquo;unit&eacute;.<br>
                    Si vous s&eacute;lectionnez un autre demandeur et que vous enregistrez,<br>
                   <?php echo $row_rs_commande['referentprenom'] ?> <?php echo $row_rs_commande['referentnom'] ?> ne vous sera plus propos&eacute; dans cette liste.<br>
                  </span>
                </div>
              <script type="text/javascript">
                  var sprytooltip_info_referent_parti = new Spry.Widget.Tooltip("info_referent_parti", "#sprytrigger_info_referent_parti", {useEffect:"blind", offsetX:-600, offsetY:20});
                </script>
						<?php 
						} ?>
            <select name="codereferent" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="codereferent">
            <?php
						while($row_rs_referent=mysql_fetch_assoc($rs_referent))
						{ if($droitmodif || (!$droitmodif && $row_rs_commande['codereferent']==$row_rs_referent['codereferent']))// 20170412
							{ ?>
							<option value="<?php echo $row_rs_referent['codereferent'] ?>" <?php echo ($row_rs_commande['codereferent']==$row_rs_referent['codereferent']?'selected':'') ?>><?php echo $row_rs_referent['nomprenom'] ?></option>
							<?php
							} 
						}?>
            </select>
            <?php 
          }
					else if($estvisa_sif1_appose || $row_rs_commande['codereferent']!='')// commande de mission : pas de modif possible du referent si visa sif1 appose ou referent selectionne
					{ ?>
            <select name="codereferent" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="codereferent">
            <?php
						while($row_rs_referent=mysql_fetch_assoc($rs_referent))
						{ if($row_rs_commande['codereferent']==$row_rs_referent['codereferent'])
							{ ?><option value="<?php echo $row_rs_referent['codereferent'] ?>" selected><?php echo $row_rs_referent['nomprenom'] ?></option>
							<?php
							}
						}?>
            </select>
            <?php
					}
					else
					{?> 
          	<select name="codereferent" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="codereferent" >
          	<?php
						while($row_rs_secrsite=mysql_fetch_assoc($rs_secrsite))
						{ if($row_rs_secrsite['codesecrsite']==$row_rs_commande['codesecrsite'])
          		{?> <option value="<?php echo $row_rs_secrsite['codesecrsite'] ?>" <?php echo ($row_rs_commande['codesecrsite']==$row_rs_secrsite['codesecrsite']?'selected':'') ?>><?php echo $row_rs_secrsite['nomprenom'] ?></option>
          	<?php
							}
            }  ?>
          	</select>
          <?php 
					}
					?>
        </td>
        <td nowrap><span class="bleugrascalibri10">Secr. d&rsquo;appui d&eacute;pt. :&nbsp;</span>
        </td>
        <td nowrap>
        <select name="codesecrsite" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="codesecrsite" >
          <?php mysql_data_seek($rs_secrsite,0);
						while($row_rs_secrsite=mysql_fetch_assoc($rs_secrsite))
						{ if($droitmodif || (!$droitmodif && $row_rs_commande['codesecrsite']==$row_rs_secrsite['codesecrsite']))// 20170412
							{ ?>
              <option value="<?php echo $row_rs_secrsite['codesecrsite'] ?>" <?php echo ($row_rs_commande['codesecrsite']==$row_rs_secrsite['codesecrsite']?'selected':''/* $codeuser==$row_rs_secrsite['codesecrsite']?'selected': */) ?>><?php echo $row_rs_secrsite['nomprenom'] ?></option>
              <?php
							}
            } ?>
          </select>
        </td>
        <td nowrap><span class="bleugrascalibri10">Gest. cr&eacute;dits :&nbsp;</span>
        </td>
        <td nowrap><select name="codegestsif" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="codegestsif" >
          <?php
						while($row_rs_gestsif=mysql_fetch_assoc($rs_gestsif))
						{ if($droitmodif || (!$droitmodif && $row_rs_commande['codegestsif']==$row_rs_gestsif['codeindividu']))// 20170412
							{ ?>
          		<option value="<?php echo $row_rs_gestsif['codeindividu'] ?>" <?php echo ($row_rs_commande['codegestsif']==$row_rs_gestsif['codeindividu']?'selected':'') ?>><?php echo $row_rs_gestsif['nomprenom'] ?></option>
							<?php
							}
            } ?>
          </select>
        </td>
       </tr>
    </table>
   </td>
  </tr>
  <tr>
  <td nowrap>
   <div id='listeselectmission' class="<?php echo $est_cmd_de_miss?'affiche':'cache' ?>">
    <table>
      <tr>
        <td>
          <span class="bleugrascalibri10">Mission :</span>
          </td>
          <td nowrap>
          <input name="codemission" type="hidden" class="noircalibri10" id="codemission" value="<?php echo $row_rs_commande['codemission'] ?>">
          <?php if($row_rs_commande['codemission']!='')
          { echo htmlspecialchars($row_rs_mission['motif']." (".$row_rs_mission['nom']." ".$row_rs_mission['prenom'].")");
          }?>
        </td>
      </tr>
    </table>
   </div>
  </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="0" cellspacing="2" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="2">
              <tr>
                <td nowrap>
                  <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                      <td valign="top"><span class="bleugrascalibri10">Objet :</span></td>
                      <td><input name="objet"  type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['objet']) ?>" size="80" maxlength="200" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                      </td>
                      <td><span class="bleugrascalibri10">Groupe marchandises :</span>
                          <input name="groupemarchandise"  type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['groupemarchandise']) ?>" size="30" maxlength="100" <?php echo !$droitmodif?'readonly':''; // 20170412 ?>>
                      </td>
                    </tr>
                    <tr>
                      <td valign="top"><span class="bleugrascalibri10">Description d&eacute;taill&eacute;e :</span><br>
                        <span class="bleucalibri9">(</span><span id="description#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_commande['description']) ?></span><span class="bleucalibri9">/400 car. max.)</span></td>
                      <td><textarea name="description" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" cols="80" rows="3" wrap="PHYSICAL" class="noircalibri10" id="description" <?php echo !$droitmodif?'readonly':''; // 20170412?> <?php echo affiche_longueur_js("this","400","'description#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_commande['description']; ?></textarea></td>
                      <td>
                      	<table cellspacing="2">
                    			<tr>
                      			<td>&nbsp;
														<?php echo ligne_txt_upload_pj_commande($codecommande,'devis','Devis joint',$form_commande, $droitmodif); // 20170412?>
                        		</td>
                      		</tr>
                    		</table>
                    	</td>
                    </tr>
                    <tr>
                      <td valign="top"><span class="bleugrascalibri10">Fournisseur :&nbsp;</span></td>
                      <td><input name="libfournisseur"  type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['libfournisseur']) ?>" size="80" maxlength="200" <?php echo !$droitmodif?'readonly':''; // 20170412?>></td>
                      <td nowrap><span class="bleugrascalibri10">N&deg; :&nbsp;</span><input name="<?php echo !$droitmodif?'champ_input_readonly':'' ?> numfournisseur"  type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo $row_rs_commande['numfournisseur'] ?>" size="20" maxlength="50" <?php echo !$droitmodif?'readonly':''; // 20170412?>></td>
											</td>
                    </tr>
                    <tr>
                      <td><span class="bleugrascalibri10">Num&eacute;ro de r&eacute;servation :&nbsp;</span></td>
                      <td><input name="numreservation"  type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['numreservation']) ?>" size="20" maxlength="50" <?php echo !$droitmodif?'readonly':''; // 20170412?>></td>
                      <td nowrap></td>
											</td>
                    </tr>
                  </table>
                </td>
              </tr>
              	<tr>
                	<td valign="top">
                    <table border="0" cellspacing="2">
                     <?php
										 	$droitmodif=true;
											$class_cache_ou_affiche='affiche';
										  if(!estrole('sif',$tab_roleuser) && !estrole('du',$tab_roleuser))
											{if($visa_theme_ou_contrat_appose)
												{ $droitmodif=false;
													$class_cache_ou_affiche='cache';
												}
											}
                      $first=true;
											// plus d'une imput. virt.
											$cptimputation=0;
											$tab_commandeimputationbudget_virtuel=$tab_commandeimputationbudget['0'];
											// a revoir plus d_une_imputation : la derniere est vide au debut en modif.
											$plus_d_une_imputation=(count($tab_commandeimputationbudget['0'])>1);
                      foreach($tab_commandeimputationbudget_virtuel as $numordre=>$une_commandeimputationbudget)
                      { // 20/01/2014 plus d'une imput. virt.
                      	$cptimputation++;
												$estderniereimputation=($cptimputation==count($tab_commandeimputationbudget_virtuel));?>
											<tr>
												<td colspan="2" align="left">
													<?php 
													if($estderniereimputation && $action=='modifier' && $droitmodif)
													{?> 
													<img src="images/b_plus.png" align="top"
													onClick="nouveau=document.getElementById('derniereimputationvirtuel');
																				if(nouveau.className=='affiche')
																				{ nouveau.className='cache';
																					this.src='images/b_plus.png';
																				}
																				else 
																				{ nouveau.className='affiche';
																					this.src='images/b_moins.png';
																				}
																	"> 
													<?php 
													}
													else
													{?>
													<img src="images/espaceur.gif" width="50" height="1"></td>
													<?php 
													} ?>
													</td>
													<?php 
													for($i=1;$i<5;$i++)
													{ ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
													<?php 
													} ?>
                          <?php 
												if($plus_d_une_imputation && $droitmodif)
                        {?> <td></td>
                        <?php 
												}?>
											</tr>
											<?php // fin 20/01/2014 plus d'une imput. virt.?>
                			<tr>
                        <td nowrap>
                        	<?php
													if(!$estderniereimputation && !$droitmodif) 
													{ ?>
													<?php echo htmlspecialchars($tab_typecredit[$une_commandeimputationbudget['codetypecredit']]['libtypecredit']); 
													}?>
                        </td>
                        <?php 	
												$cache_ou_affiche='cache';//cache la nouvelle imputation
												// 20/01/2014 plus d'une imput. virt.
												if($estderniereimputation && $action=='modifier')
												{?><td colspan="<?php echo($plus_d_une_imputation && $droitmodif)?'11':'10' ?>">
													<div id='derniereimputationvirtuel' class='<?php echo $cache_ou_affiche ?>'>
													<table border="0" cellspacing="2">
														<tr><?php for($i=1;$i<5;$i++)
																{ ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
																<?php 
																} ?>
														 </tr>
														 <tr>
												<?php 
												} // fin 20/01/2014 plus d'une imput. virt.?>
                        <td nowrap>
                        <div class='<?php echo $class_cache_ou_affiche ?>'>
                        <select name="codetypecredit#0##<?php echo $numordre?>" class="noircalibri10" id="codetypecredit#0##<?php echo $numordre?>" 
                        onChange="affichestructure(this)">
                          <?php 
                          if($action=='creer')
                          {?><option value=""></option>
                          <?php
                          }
                          $codetypecredit_virtuel=$une_commandeimputationbudget['codetypecredit'];
                          if(isset($tab_contrat[$une_commandeimputationbudget['codecontrat']]['contrat_ou_source']) && $tab_contrat[$une_commandeimputationbudget['codecontrat']]['contrat_ou_source']=='source')
                          { $codetypecredit_virtuel='00';
                          }
                          foreach($tab_typecredit as $codetypecredit=>$row_rs_typecredit)
                          { ?> <option value="<?php echo $codetypecredit ?>" <?php echo ($codetypecredit_virtuel==$codetypecredit?'selected':'') ?>><?php if($codetypecredit==''){ ?>Cr&eacute;dits<?php }else {echo $row_rs_typecredit['libtypecredit'];} ?></option>
                          <?php
                          } ?>
                        </select>
                        </div>
                        </td>
                        <td nowrap><span class="bleugrascalibri10">Cr&eacute;dits :</span></td>
                        <td nowrap>
                        <?php 
												if(!$droitmodif) 
												{ echo htmlspecialchars($tab_centrefinancier[$une_commandeimputationbudget['codecentrefinancier']]['libcentrefinancier']); 
												}?>
                        <div class='<?php echo $class_cache_ou_affiche ?>'>
                        <select name="codecentrefinancier#0##<?php echo $numordre?>" class="noircalibri10" id="codecentrefinancier#0##<?php echo $numordre?>" 
                        onChange="affichestructure(this)">
													<?php if($action=='creer')
                          {?><option value=""></option>
                          <?php
                          }
													else
													{ foreach($tab_centrefinancier as $codecentrefinancier=>$row_rs_centrefinancier)
														{ if(($codetypecredit_virtuel=='00' && $row_rs_centrefinancier['codetypecredit']=='02' && $row_rs_centrefinancier['source']=='oui') || ($codetypecredit_virtuel!='00' && $row_rs_centrefinancier['contrat']=='oui' && $row_rs_centrefinancier['codetypecredit']==$une_commandeimputationbudget['codetypecredit']))
															//if($row_rs_centrefinancier['virtuel_ou_reel']=='0' && $row_rs_centrefinancier['codetypecredit']==$une_commandeimputationbudget['codetypecredit'])
															{?>
														<option value="<?php echo $row_rs_centrefinancier['codecentrefinancier'] ?>" <?php echo ($une_commandeimputationbudget['codecentrefinancier']==$row_rs_centrefinancier['codecentrefinancier']?'selected':'') ?>><?php echo $row_rs_centrefinancier['libcentrefinancier'] ?></option>
															<?php
															}
														}
													}?>
                        </select>
                        </div>
                        </td>
                        <td nowrap><span class="bleugrascalibri10">Enveloppe :</span></td>
                        <td nowrap>
                        <?php 
													if(!$droitmodif) 
													{ echo htmlspecialchars($tab_centrecout[$une_commandeimputationbudget['codecentrecout']]['libcentrecout']); 
													}?>
	                        <div class='<?php echo $class_cache_ou_affiche ?>'>
                            <select name="codecentrecout#0##<?php echo $numordre?>" id="codecentrecout#0##<?php echo $numordre?>" class="noircalibri10"
                            onChange="affichestructure(this)">
														<?php if($action=='creer')
														{?><option value=""></option>
                            <?php
														}
														else
                            { foreach($tab_centrecout as $codecentrecout=>$row_rs_centrecout)
															{ if($row_rs_centrecout['codecentrefinancier']==$une_commandeimputationbudget['codecentrefinancier'] && (($codetypecredit_virtuel=='00' && $row_rs_centrecout['source']=='oui') || ($codetypecredit_virtuel!='00' && $row_rs_centrecout['contrat']=='oui')))
															//if($row_rs_centrecout['virtuel_ou_reel']=='0' && $row_rs_centrecout['codecentrefinancier']==$une_commandeimputationbudget['codecentrefinancier'])
																{?>
																<option value="<?php echo $row_rs_centrecout['codecentrecout'] ?>" <?php echo ($une_commandeimputationbudget['codecentrecout']==$row_rs_centrecout['codecentrecout']?'selected':'') ?>><?php echo $row_rs_centrecout['libcentrecout'] ?></option>
																<?php
																}
															} 
														}?>
                            </select>
                        	</div>
                        </td>
                        <td nowrap>
												<?php 
												$estcontratencours=true;
                        if(isset($tab_contrat[$une_commandeimputationbudget['codecontrat']]['estcontratencours']) && $tab_contrat[$une_commandeimputationbudget['codecontrat']]['estcontratencours']=='non')
                        { $estcontratencours=false;
												}
                         ?>
                       	<span class="<?php echo $estcontratencours?'bleugrascalibri10':'rougegrascalibri10' ?>">Source/Contrat :</span>
												<?php 
                        if(!$estcontratencours)
												{ ?><img src="images/b_attention.png" width="16" height="16" id="sprytrigger_info_contrat_passe">
													<div class="tooltipContent_cadre" id="info_contrat_passe">
															<span class="noircalibri10">
																Attention : le contrat &quot;<?php echo $tab_contrat[$une_commandeimputationbudget['codecontrat']]['libcontrat'] ?>&quot; n&rsquo;a pas cours &agrave; la date de la commande<br>
                            		S&eacute;lectionnez un autre contrat ou changez la date de la commande.
															</span>
														</div>
													<script type="text/javascript">
															var sprytooltip_info_contrat_passe = new Spry.Widget.Tooltip("info_contrat_passe", "#sprytrigger_info_contrat_passe", {offsetX:-100, offsetY:20});
														</script>
						
												<?php 
												} ?>
                        </td>
                        <td>
                        <?php 
													if(!$droitmodif) 
													{ if($une_commandeimputationbudget['codecontrat']=='')
														{ ?>Dotation
														<?php 
														}
                            else
														{ echo htmlspecialchars($tab_contrat[$une_commandeimputationbudget['codecontrat']]['libcontrat']);
														}
													}?>
                        	<div class='<?php echo $class_cache_ou_affiche ?>'>
                            <select name="codecontrat#0##<?php echo $numordre?>" id="codecontrat#0##<?php echo $numordre?>" class="noircalibri10"
                            ><!--onChange="affichestructure(this)" -->
														<?php if($action=='creer')
														{?><option value=""></option>
                            <?php
														}
														else
														{ foreach($tab_contrat as $codecontrat=>$row_rs_contrat)
															{ if($row_rs_contrat['codecentrecout']==$une_commandeimputationbudget['codecentrecout'] && (($codetypecredit_virtuel=='00' && $row_rs_contrat['contrat_ou_source']=='source') || ($codetypecredit_virtuel!='00' && $row_rs_contrat['contrat_ou_source']=='contrat')))
															//if($row_rs_contrat['codecentrecout']==$une_commandeimputationbudget['codecentrecout'])
																{ ?>
															<option value="<?php echo $row_rs_contrat['codecontrat'] ?>" <?php echo ($une_commandeimputationbudget['codecontrat']==$row_rs_contrat['codecontrat']?'selected':'') ?>><?php echo htmlentities($row_rs_contrat['libcontrat']) ?></option>
															<?php
																}
															}
														}?>
                          </select>
                          </div>
                          <!-- utile pour parametres enregistres dans commandeimputationbudget -->
                          <input type="hidden" name="codeeotp#0##<?php echo $numordre?>" id="codeeotp#0##<?php echo $numordre?>" value="">
                        </td>
                        <td nowrap><span class="bleugrascalibri10">Engag&eacute; : </span><sup><span class="rougecalibri9">*</span></sup>
                        </td>
                        <td nowrap>
													<input name="montantengage#0##<?php echo $numordre?>"  type="<?php echo $droitmodif?"text":"hidden" ?>" id="montantengage#0##<?php echo $numordre?>" class="noircalibri10" style="text-align:right" value="<?php echo $une_commandeimputationbudget['montantengage'] ?>" size="8" maxlength="12">
                       		<?php echo $droitmodif?"":$une_commandeimputationbudget['montantengage'] ?>
												</td>
                        <?php 
												if($plus_d_une_imputation && $droitmodif)
                        { if($first)
													{?> 
                          <td>
                          <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_imputation_virt_suppression">
                          <div class="tooltipContent_cadre" id="info_imputation_virt_suppression">
                            <span class="noircalibri10">
                            Il faut au moins une ligne d&rsquo;imputation.<br>
                            Pour supprimer une ligne d'imputation, quand il y en a plus d&rsquo;une,<br>
                            il suffit de ne pas renseigner le montant engag&eacute; de la ligne &agrave; supprimer : effacer le contenu y compris 0
                            </span>
                          </div>
                          <script type="text/javascript">
                            var sprytooltip_imputation_virt_suppression = new Spry.Widget.Tooltip("info_imputation_virt_suppression", "#sprytrigger_info_imputation_virt_suppression", { offsetX:-200, offsetY:0, closeOnTooltipLeave:true});
                          </script>
                        	</td>
                        <?php
													}
													else
													{?> <td></td>
                        <?php 
													}
												}?>
                      
										 <?php // 20/01/2014 plus d'une imput. virt.
                      if($estderniereimputation && $action=='modifier')
                      {?></tr></table></div></td>
                      <?php 
                      }
											// fin 20/01/2014 plus d'une imput. virt.?>
                     </tr>
                      <?php
											$first=false;
                     } ?>
              			</table>
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
    <td>
    <?php
	 	$class_cache_ou_affiche='cache';
		//PG 20151031
	 	if(estrole('secrsite',$tab_roleuser) || estrole('sif',$tab_roleuser) ||  estrole('du',$tab_roleuser) || droit_acces($tab_contexte))
    { //PG 20151031
			$class_cache_ou_affiche='affiche';
			// 20170412
			$droitmodif=true;
			if(!estrole('sif',$tab_roleuser) && !estrole('du',$tab_roleuser))
			{if($visa_secrsite_appose)
				{ $droitmodif=false;
				}
			}
			// 20170412

    }?>
    <div id='blocsecrsite' class="<?php echo $class_cache_ou_affiche ?>">           
    	<table width="100%" border="0" class="table_cadre_arrondi">
      	<tr>
          <td>
          	<table border="0" cellpadding="0" cellspacing="2">
            	<tr>
              	<td width="19%" nowrap><span class="bleugrascalibri10">Date de la commande :&nbsp;</span>
                </td>
              	<td width="81%" nowrap>
                	<input name="datecommande_jj" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="datecommande_jj" value="<?php echo substr($row_rs_commande['datecommande'],8,2); ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                	<input name="datecommande_mm" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="datecommande_mm" value="<?php echo substr($row_rs_commande['datecommande'],5,2); ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                	<input name="datecommande_aaaa" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="datecommande_aaaa" value="<?php echo substr($row_rs_commande['datecommande'],0,4); ?>" size="4" maxlength="4" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                </td>
            	</tr>
            	<tr>
              	<td><span class="bleugrascalibri10">N&deg; de commande :&nbsp;</span>
              	</td>
              	<td nowrap>
                	<table>
                  	<tr>
                    	<td>
                        <input name="numcommande" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo $row_rs_commande['numcommande'] ?>" size="20" maxlength="20" <?php echo !$droitmodif?'readonly':''; // 20170412?>
                        onChange="if((new String('<?php echo $GLOBALS['prefixe_commande_etat_avec_frais'] ?>')).indexOf(new String(this.value).substr(0,2))!=-1)
                                  { document.getElementById('dateenvoi_etatfrais').className='affiche';
                                  }
                                  else
                                  { document.getElementById('dateenvoi_etatfrais').className='cache';
                                  }">
                        </td>
                        <td>
                        <div id="dateenvoi_etatfrais" class="<?php echo est_avecfrais($row_rs_commande['numcommande'])?"affiche":"cache" ?>">
                          <span class="bleugrascalibri10">Date envoi EF &agrave; l&rsquo;AC :&nbsp;</span>
                          <input name="dateenvoi_etatfrais_jj" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="dateenvoi_etatfrais_jj" value="<?php echo substr($row_rs_commande['dateenvoi_etatfrais'],8,2); ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                          <input name="dateenvoi_etatfrais_mm" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="dateenvoi_etatfrais_mm" value="<?php echo substr($row_rs_commande['dateenvoi_etatfrais'],5,2); ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                          <input name="dateenvoi_etatfrais_aaaa" type="text" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" id="dateenvoi_etatfrais_aaaa" value="<?php echo substr($row_rs_commande['dateenvoi_etatfrais'],0,4); ?>" size="4" maxlength="4" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                        </div>
                  		</td>
                    </tr>
                  </table>
                </td>
            	</tr>
            	<tr>
              <td colspan="2">
                <table border="0" cellspacing="2">
                <tr><td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_migo">
                      <div class="tooltipContent_cadre" id="info_migo">
                          <span class="noircalibri10">
                          Une ligne de service fait (MIGO) est enregistr&eacute;e si la case service fait (MIGO) n&rsquo;est pas vide : il est possible par ce biais de supprimer totalement une MIGO.<br>
                          Une facture/liquidation (n&deg;, date, montant) est enregistr&eacute;e si le n&deg; de facture n&rsquo;est  pas vide : il est possible par ce biais de supprimer une ligne facture/liquidation.<br>
													Il vous est toujours pr&eacute;sent&eacute; une facture/liquidation suppl&eacute;mentaire (nouvelle) dans une ligne de service fait (MIGO)<br>
                          ainsi qu&rsquo;une nouvelle ligne de service fait (MIGO) avec une facture/liquidation vierge : icone <img src="images/b_plus.png">
                          </span>
                        </div>
                      <script type="text/javascript">
                          var sprytooltip_migo = new Spry.Widget.Tooltip("info_migo", "#sprytrigger_info_migo", {offsetX:10, offsetY:-30});
                        </script>
                    </td>
                		<td><span class="bleugrascalibri10">Serv. fait (MIGO)</span><sup><span class="rougecalibri9">*</span></sup>
                    </td>
                    <td><span class="bleugrascalibri10">Date Serv. fait (MIGO)</span>
                    </td>
                    <td class="bleugrascalibri10">N&deg; facture<sup><span class="rougecalibri9">*</span></sup></td><td class="bleugrascalibri10">Date facture</td><td class="bleugrascalibri10">N&deg; liquidation</td><td class="bleugrascalibri10">Date paiement</td><td class="bleugrascalibri10">Montant</td>
                 		<td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer"></td>
                    <td nowrap>
														<?php //$first est teste meme si une seule ligne a imputer reel car une vierge est toujours proposee. $first est mis a false plus bas pour l'icone info
														 if(estrole('sif',$tab_roleuser) ||  estrole('du',$tab_roleuser))
															{ if($une_seule_ligne_a_imputer_reel)
															 {?>
																<input type="hidden" name="montantpaye_automatique" value="<?php echo $montantpaye_automatique ?>">
																<input type="image" name="submit_montantpaye_automatique" src="images/b_montantpaye_automatique_<?php echo $montantpaye_automatique ?>.png"
																onClick="	if(document.forms['<?php echo $form_commande ?>'].montantpaye_automatique.value=='oui')
																					{ return confirm('Enregistrer et désactiver le calcul automatique du montant payé ?');
																					}
																					else
																					{ return confirm('Enregistrer et activer le calcul automatique du montant paye ?');
																					}
																				 "
																 onKeyPress="return"><!-- pas de prise en compte de la touche ENTREE, sinon ce champ est modifie sans le vouloir par un user --> 
															 <?php 
															 }
															 }?>
                             
                  </td>
                 </tr>
                  <?php 
                  $first=true;
                  $cptcommandemigo=0;
									$div_nouveaumigo="";
									$div_nouveaumigodate="";
									$div_nouveaunumliquidation="";
									$div_nouveauliquidationdate="";
									$div_nouveauliquidationmontant="";
									$div_nouveaunumfacture="";
									$div_nouveaufacturedate="";
									$findiv="</div>";
                  $cache_ou_affiche='affiche';
                  foreach($tab_commandemigo as $codemigo=>$une_commandemigo)
                  { $cptcommandemigo++;
                    $estdernierecommandemigo=($cptcommandemigo==count($tab_commandemigo));
										?>
                  <tr>
                    <td>
                      <?php 
                      if($estdernierecommandemigo && $action=='modifier')
                      { $cache_ou_affiche='cache';
												$div_nouveaumigo="<div id='nouveaumigo' class='".$cache_ou_affiche."'>"; 
												$div_nouveaumigodate="<div id='nouveaumigodate' class='".$cache_ou_affiche."'>"; 
                      	$div_nouveaunumliquidation="<div id='nouveaunumliquidation' class='".$cache_ou_affiche."'>";
                      	$div_nouveauliquidationdate="<div id='nouveauliquidationdate' class='".$cache_ou_affiche."'>";
                      	$div_nouveauliquidationmontant="<div id='nouveauliquidationmontant' class='".$cache_ou_affiche."'>";
                      	$div_nouveaunumfacture="<div id='nouveaunumfacture' class='".$cache_ou_affiche."'>";
                      	$div_nouveaufacturedate="<div id='nouveaufacturedate' class='".$cache_ou_affiche."'>";
												if($droitmodif) // 20170412
                        { ?><img src="images/b_plus.png" align="top"
                        onClick="nouveaumigo=document.getElementById('nouveaumigo');
                        				 nouveaumigodate=document.getElementById('nouveaumigodate');
                        				 nouveaunumliquidation=document.getElementById('nouveaunumliquidation');
                        				 nouveauliquidationdate=document.getElementById('nouveauliquidationdate');
                         				 nouveauliquidationmontant=document.getElementById('nouveauliquidationmontant');
                        				 nouveaunumfacture=document.getElementById('nouveaunumfacture');
                        				 nouveaufacturedate=document.getElementById('nouveaufacturedate');
                                     if(nouveaumigo.className=='affiche')
                                      { nouveaumigo.className='cache';
                                        nouveaumigodate.className='cache';
                                        nouveaunumliquidation.className='cache';
                                        nouveauliquidationdate.className='cache';
                                        nouveauliquidationmontant.className='cache';
                                        nouveaunumfacture.className='cache';
                                        nouveaufacturedate.className='cache';
                                        this.src='images/b_plus.png';
                                      }
                                      else 
                                      { nouveaumigo.className='affiche';
                                        nouveaumigodate.className='affiche';
                                       	nouveaunumliquidation.className='affiche';
                                        nouveauliquidationdate.className='affiche';
                                        nouveauliquidationmontant.className='affiche';
                                        nouveaunumfacture.className='affiche';
                                        nouveaufacturedate.className='affiche';
                                        this.src='images/b_moins.png';
                                      }
                                "> 
                        <?php }
                      }?>
                    </td>
                    <td><?php echo $div_nouveaumigo ?>
                    	<input name="nummigo#<?php echo $codemigo?>"  type="text" id="nummigo#<?php echo $codemigo?>" class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo $une_commandemigo['nummigo']; ?>" size="15" maxlength="20" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <td><?php echo $div_nouveaumigodate ?>
                    	<input name="datemigo_jj#<?php echo $codemigo?>" type="text" id="datemigo_jj#<?php echo $codemigo?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigo['datemigo'],8,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="datemigo_mm#<?php echo $codemigo?>" type="text" id="datemigo_mm#<?php echo $codemigo?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigo['datemigo'],5,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="datemigo_aaaa#<?php echo $codemigo?>" type="text" id="datemigo_aaaa#<?php echo $codemigo?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigo['datemigo'],0,4) ?>" size="2" maxlength="4" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <?php 
                    $tab_commandemigoliquidation=$une_commandemigo['tab_commandemigoliquidation'];
										//rajout pour bouton copier num facture, date
										$nb_commandemigoliquidation=count($tab_commandemigoliquidation);
										// pour recopie des champs d'une facturation/liquidation dans la suivante avec le bouton dupliquer (CLK seulement)
										$num_ligne_commandemigoliquidation=0;
										$codemigo_prec='';
										$codeliquidation_prec='';
										//
										$first=true;
										foreach($tab_commandemigoliquidation as $codeliquidation=>$une_commandemigoliquidation)
                    { if(!$first)
                      {?><tr><td colspan="3">
                      <?php 
                      }?>
                    <td><?php echo $div_nouveaunumfacture ?>
                    	<input name="numfacture#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="numfacture#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo $une_commandemigoliquidation['numfacture'] ?>" size="15" maxlength="20" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <td nowrap><?php echo $div_nouveaufacturedate ?>
                    	<input name="datefacture_jj#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="datefacture_jj#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['datefacture'],8,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="datefacture_mm#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="datefacture_mm#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['datefacture'],5,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="datefacture_aaaa#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="datefacture_aaaa#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['datefacture'],0,4) ?>" size="2" maxlength="4" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <td><?php echo $div_nouveaunumliquidation ?>
                    	<input name="numliquidation#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="numliquidation#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo $une_commandemigoliquidation['numliquidation'] ?>" size="15" maxlength="20" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <td nowrap><?php echo $div_nouveauliquidationdate ?>
                    	<input name="dateliquidation_jj#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="dateliquidation_jj#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['dateliquidation'],8,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="dateliquidation_mm#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="dateliquidation_mm#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['dateliquidation'],5,2) ?>" size="2" maxlength="2" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
                    	<input name="dateliquidation_aaaa#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="dateliquidation_aaaa#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" value="<?php echo substr($une_commandemigoliquidation['dateliquidation'],0,4) ?>" size="2" maxlength="4" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
											<?php echo $findiv ?>	
                    </td>
                    <td><?php echo $div_nouveauliquidationmontant ?>
                    <input name="montantliquidation#<?php echo $codemigo?>##<?php echo $codeliquidation?>" type="text" id="montantliquidation#<?php echo $codemigo?>##<?php echo $codeliquidation?>"  class="<?php echo !$droitmodif?'champ_input_readonly':'' ?> noircalibri10" style="text-align:right" value="<?php echo $une_commandemigoliquidation['montantliquidation'] ?>" size="8" maxlength="12" <?php echo !$droitmodif?'readonly':''; // 20170412?>>
										</td>
                  	<td>
          					<?php 
										if($codeuser=='00012')//CLK uniquement
                    {?>
											<?php 
											$num_ligne_commandemigoliquidation++;
											if($num_ligne_commandemigoliquidation==$nb_commandemigoliquidation && $num_ligne_commandemigoliquidation!=1)
											{?><img src="images/b_dupliquer_zone.png" 
                      		onClick="tab_elem=new Array('numfacture','datefacture_jj','datefacture_mm','datefacture_aaaa','numliquidation','dateliquidation_jj','dateliquidation_mm','dateliquidation_aaaa','montantliquidation');
                          				for(i=0;i<tab_elem.length;i++)
                                  {document.forms['<?php echo $form_commande ?>'].elements[tab_elem[i]+'#<?php echo $codemigo ?>##<?php echo $codeliquidation?>'].value=document.forms['<?php echo $form_commande ?>'].elements[tab_elem[i]+'#<?php echo $codemigo_prec ?>##<?php echo $codeliquidation_prec?>'].value;
                                  }">
                          <img src="images/b_effacer_zone.png" 
                      		onClick="	if(confirm('Effacer'))
                          					{ tab_elem=new Array('numfacture','datefacture_jj','datefacture_mm','datefacture_aaaa','numliquidation','dateliquidation_jj','dateliquidation_mm','dateliquidation_aaaa','montantliquidation');
                                      for(i=0;i<tab_elem.length;i++)
                                      {document.forms['<?php echo $form_commande ?>'].elements[tab_elem[i]+'#<?php echo $codemigo ?>##<?php echo $codeliquidation?>'].value='';
                                      }
                                    }">
											<?php 
											}	
											$codemigo_prec=$codemigo;
											$codeliquidation_prec=$codeliquidation;
                    }
											?>
                    </td>
                    <?php
										 echo $findiv ?>	
											<?php 
											if(!$first)
                      {?></tr>
                      <?php 
                      } 
                      $first=false;
										}?>
                  </tr>
                  <?php
                  } ?>
                </table>
              </td>
             </tr>
          </table>
        </td>
      </tr>
    </table>
    </div>
  	</td>
 	</tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <!--// 20170405 inventaire -->
 <tr>
  	<td>
		<?php
	 	$class_cache_ou_affiche='cache';
	 	if((estrole('secrsite',$tab_roleuser) ||  estrole('sif',$tab_roleuser) ||  estrole('du',$tab_roleuser) || droit_acces($tab_contexte))
    	&& (($row_rs_commande['codenature']=='05' || $row_rs_commande['codenature']=='08'  || $row_rs_commande['codenature']=='10')
					|| (isset($tab_commandeinventaire) && count($tab_commandeinventaire)>=1 && $tab_commandeinventaire['01']['numinventaire']!='')))
		{ $class_cache_ou_affiche='affiche';
    }?>
		<div id='blocinventaire' class="<?php echo $class_cache_ou_affiche ?>">           
      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
        	<td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_inventaire">
                      <div class="tooltipContent_cadre" id="info_inventaire">
                          <span class="noircalibri10">
                          Le cadre inventaire est affich&eacute; pour une commande d&rsquo;&eacute;quipement.<br>  
                          Une ligne d&rsquo;inventaire est enregistr&eacute;e si le n&deg; n&rsquo;est pas vide : il est possible par ce biais de supprimer totalement une ligne d&rsquo;inventaire.<br>
                          Il vous est toujours possible d&rsquo;ajouter une ligne d&rsquo;inventaire suppl&eacute;mentaire (icone <img src="images/b_plus.png">) dont le destinataire est le demandeur par d&eacute;faut<br>
                          Le choix d&rsquo;un autre destinataire initialise par d&eacute;faut le lieu et le bureau associ&eacute;s dans la fiche personnel. Ils peuvent &ecirc;tre modifi&eacute;s pour chaque n&deg; d&rsquo;inventaire<br>
                          mais ne modifient pas ceux de la fiche personnel.<br>
                          Le champ &eacute;quipement est initialis&eacute; par l&rsquo;objet de la commande par d&eacute;faut pour la premi&egrave;re ligne d&rsquo;inventaire 
                          </span>
                        </div>
                      	<script type="text/javascript">
                          var sprytooltip_inventaire = new Spry.Widget.Tooltip("info_inventaire", "#sprytrigger_info_inventaire", {offsetX:10, offsetY:-30});
                        </script>
          </td>
          <td align="left" class="bleugrascalibri10">N&deg; inventaire
          </td>
          <td align="left" class="bleugrascalibri10">Destinataire
          </td>
          <td align="left" class="bleugrascalibri10">Lieu
          </td>
          <td align="left" class="bleugrascalibri10">N&deg; bureau
          </td>
          <td align="left" class="bleugrascalibri10">Equipement
          </td>
          <td align="right"><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer"></td>
        </tr>
        <?php 
       	$cptcommandeinventaire=0; 
        foreach($tab_commandeinventaire as $codeinventaire=>$une_commandeinventaire)
        { $cptcommandeinventaire++;
          $estdernierecommandeinventaire=($cptcommandeinventaire==count($tab_commandeinventaire));?>
        <tr>
        	<td>
					<?php 
          $cache_ou_affiche='affiche';
					if($estdernierecommandeinventaire)
          {?> 
          <img src="images/b_plus.png" align="top" onClick="cache_ou_affiche=document.getElementById('div_numinventaire#<?php echo $codeinventaire?>').className
                                                            if(cache_ou_affiche=='affiche')
                                                            { cache_ou_affiche='cache';
                                                              this.src='images/b_plus.png';
                                                            }
                                                            else
                                                            { cache_ou_affiche='affiche';
                                                              this.src='images/b_moins.png';
                                                            }
                                                            champs = ['numinventaire', 'codedestinataire', 'codelieu', 'num_bureau', 'objetinventaire'];
                                                            for (i = 0; i < champs.length; i++) 
                                                            { document.getElementById('div_'+champs[i]+'#<?php echo $codeinventaire?>').className=cache_ou_affiche
                                                            }
                                                            "
          >
						<?php 
						$cache_ou_affiche='cache';
					}?>
          </td>
          <td><div id='div_numinventaire#<?php echo $codeinventaire ?>' class='<?php echo $cache_ou_affiche ?>'>
         <input name="numinventaire#<?php echo $codeinventaire?>"  type="text" id="numinventaire#<?php echo $codeinventaire?>" class="noircalibri10" value="<?php echo htmlspecialchars($une_commandeinventaire['numinventaire']); ?>" size="20" maxlength="100">
          	</div>
          </td>
          <td><div id='div_codedestinataire#<?php echo $codeinventaire ?>' class='<?php echo $cache_ou_affiche ?>'>
            <select name="codedestinataire#<?php echo $codeinventaire ?>" id='codedestinataire#<?php echo $codeinventaire ?>' class="noircalibri10" onChange="detaildestinataire(this)">
            <?php
            foreach($tab_destinataire as $codedestinataire=>$un_destinataire)
            { ?>
              <option value="<?php echo $codedestinataire ?>" <?php echo ($une_commandeinventaire['codedestinataire']==$codedestinataire || ($estdernierecommandeinventaire && $row_rs_commande['codereferent']==$codedestinataire))?'selected':''; ?>><?php echo $un_destinataire['nomprenom'] ?></option>
            <?php 
            }?>
            </select>
          	</div>
          </td>
          <td><div id='div_codelieu#<?php echo $codeinventaire ?>' class='<?php echo $cache_ou_affiche ?>'>
            <select name="codelieu#<?php echo $codeinventaire ?>" id='codelieu#<?php echo $codeinventaire ?>' class="noircalibri10">
            <?php
            foreach($tab_lieu as $codelieu=>$un_lieu)
            { ?>
              <option value="<?php echo $codelieu ?>" <?php echo ($une_commandeinventaire['codelieu']==$codelieu || ($estdernierecommandeinventaire && $tab_destinataire[$row_rs_commande['codereferent']]['codelieu']==$codelieu))?'selected':''; ?>><?php echo $un_lieu['liblieu'] ?></option>
            <?php 
            }?>
            </select>
          	</div>
          </td>
          <td><div id='div_num_bureau#<?php echo $codeinventaire ?>' class='<?php echo $cache_ou_affiche ?>'>
            <input name="num_bureau#<?php echo $codeinventaire?>"  type="text" id="num_bureau#<?php echo $codeinventaire?>" class="noircalibri10" value="<?php echo htmlspecialchars($estdernierecommandeinventaire?$tab_destinataire[$row_rs_commande['codereferent']]['num_bureau']:$une_commandeinventaire['num_bureau']); ?>" size="10" maxlength="30">
          	</div>
          </td>
          <td colspan="2"><div id='div_objetinventaire#<?php echo $codeinventaire ?>' class='<?php echo $cache_ou_affiche ?>'>
            <input name="objetinventaire#<?php echo $codeinventaire?>"  type="text" id="objetinventaire#<?php echo $codeinventaire?>" class="noircalibri10"
             value="<?php echo (count($tab_commandeinventaire)==1 && $une_commandeinventaire['objetinventaire']=='')?htmlspecialchars(substr($row_rs_commande['objet'],0,100)):htmlspecialchars($une_commandeinventaire['objetinventaire']); ?>" size="50" maxlength="100">
          	</div>
          </td>
        </tr>
  		<?php 
			}?>
      </table>
     </div>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
	<!--// 20170405 -->
  <tr>
  	<td><?php // imputaions reelles vues par secr site
	 	$class_cache_ou_affiche='cache';
		//PG 20151031 
	 	if((estrole('secrsite',$tab_roleuser) || droit_acces($tab_contexte)) && !(estrole('sif',$tab_roleuser) ||  estrole('du',$tab_roleuser)))
    { //PG 20151031
			$class_cache_ou_affiche='affiche';
    }?>
      <div id='blocsecrvue' class="<?php echo $class_cache_ou_affiche ?>">
        <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
          <tr>
            <td>
              <table border="0" cellspacing="2">
                <tr>
                  <td valign="top">
                    <table border="0" cellspacing="2">
                      <tr>
                        <td><img src="images/espaceur.gif"  width="50" height="1"></td>
                        <?php for($i=1;$i<=6;$i++)
                        { ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
                        <?php 
                        } ?>
                        <td></td>
                      </tr>
                      <?php 
                      $first=true;
                      $tab_commandeimputationbudget_reel=$tab_commandeimputationbudget['1'];
											$cptimputation=0;
                      foreach($tab_commandeimputationbudget_reel as $numordre=>$une_commandeimputationbudget)
                      { $cptimputation++;
												if($cptimputation<count($tab_commandeimputationbudget_reel))
                      	{?>
                        <tr>
                          <?php 
                          if($first)
                          {?>
                          <td nowrap>
                            <?php
                            if($une_commandeimputationbudget['codetypecredit']==''){ ?>Cr&eacute;dits<?php }else {echo htmlspecialchars($tab_typecredit[$une_commandeimputationbudget['codetypecredit']]['libtypecredit']);}
                            $first=false;
                            ?>
                          </td>
                          <?php 	
                          }
                          else
                          {?><td></td>
                          <?php 
                          }?>
                          <td nowrap><span class="bleugrascalibri10">Cr&eacute;dits :</span></td>
                          <td nowrap>
                          <?php echo htmlspecialchars($tab_centrefinancier[$une_commandeimputationbudget['codecentrefinancier']]['libcentrefinancier']);?>
                          </td>
                          <td nowrap><span class="bleugrascalibri10">Centre co&ucirc;t :</span></td>
                          <td nowrap>
                            <?php echo htmlspecialchars($tab_centrecout[$une_commandeimputationbudget['codecentrecout']]['libcentrecout']) 
                            ?>
                          </td>
                          <td nowrap><span class="bleugrascalibri10">EOTP :</span>
                          </td>
                          <td>
                            <?php echo htmlspecialchars($tab_eotp[$une_commandeimputationbudget['codeeotp']]['libeotp']) ?>
                          </td>
                          <td nowrap><span class="bleugrascalibri10">Engag&eacute; : </span></td>
                          <td nowrap><?php echo $une_commandeimputationbudget['montantengage'] ?></td>
                          <td nowrap><span class="bleugrascalibri10">Pay&eacute; : </span></td>
                          <td nowrap><?php echo $tab_commandeimputationbudget['1'][$numordre]['montantpaye'] ?></td>
                          <td nowrap>
                          </td>
                          <td nowrap>	
                          </td>
                        </tr>
                        <?php
												}
                      } ?>
                    </table>
                  </td>
                 </tr>
                </table>
              </td>
            </tr>
          </table>
      </div>
    </td>
  </tr>
  <tr>
    <td>
   <?php
	 	$class_cache_ou_affiche='cache'; 
	 	if(estrole('sif',$tab_roleuser) || /* estrole('admingestfin',$tab_roleuser) ||  */estrole('du',$tab_roleuser))
    { $class_cache_ou_affiche='affiche';
    }?>           
    <div id='blocsif' class="<?php echo $class_cache_ou_affiche ?>">
		<table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
      <tr>
        <td><table border="0" cellspacing="2">
          <tr>
            <td valign="top">
              <table border="0" cellspacing="2">
                <tr>
                  <td><img src="images/espaceur.gif"  width="50" height="1"></td>
                  <?php for($i=1;$i<=6;$i++)
                  { ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
                  <?php 
                  } ?>
                  <td></td>
                </tr>
                <?php 
                $first=true;
								$tab_commandeimputationbudget_reel=$tab_commandeimputationbudget['1'];
								$cptimputation=0;
								foreach($tab_commandeimputationbudget_reel as $numordre=>$une_commandeimputationbudget)
                { $cptimputation++;
									$estderniereimputation=($cptimputation==count($tab_commandeimputationbudget_reel));?>
                <tr>
                	<td colspan="2" align="left">
 										<?php 
										if($estderniereimputation && $action=='modifier')
										{?> 
                    <img src="images/b_plus.png" align="top"
                    onClick="nouveau=document.getElementById('derniereimputationreel');
                                  if(nouveau.className=='affiche')
                                  { nouveau.className='cache';
                                    this.src='images/b_plus.png';
                                  }
                                  else 
                                  { nouveau.className='affiche';
                                    this.src='images/b_moins.png';
                                  }
                    				"> 
                    <?php 
										}
										else
										{?>
										<img src="images/espaceur.gif" width="50" height="1"></td>
										<?php 
										}
										for($i=1;$i<5;$i++)
										{ ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
										<?php 
										} ?>
                </tr>
                <tr>
									<?php 
                  if($first)
                  {?>
                  <td nowrap>
                    <select name="codetypecredit#1" class="noircalibri10" id="codetypecredit#1" 
                    onChange="affichestructure(this)">
											<?php if($action=='creer')
                      {?><option value=""></option>
                      <?php
                      }
                      foreach($tab_typecredit as $codetypecredit=>$row_rs_typecredit)
                      { if($codetypecredit!='00')// 20141217 
												{?> <option value="<?php echo $row_rs_typecredit['codetypecredit'] ?>" <?php echo ($une_commandeimputationbudget['codetypecredit']==$row_rs_typecredit['codetypecredit']?'selected':'') ?>><?php if($row_rs_typecredit['codetypecredit']==''){ ?>Cr&eacute;dits<?php }else {echo $row_rs_typecredit['libtypecredit'];} ?></option>
                      <?php
												}
                      } ?>
                    </select>
                  </td>
                  <?php 	
                  }
                  else
                  {?><td></td>
                  <?php 
                  }?>
                  <?php $cache_ou_affiche='cache';//cache la nouvelle imputation
									if($estderniereimputation && $action=='modifier')
									{?><td colspan="12">
                  	<div id='derniereimputationreel' class='<?php echo $cache_ou_affiche ?>'>
                    <table border="0"  cellspacing="2">
                    	<tr><?php for($i=1;$i<6;$i++)
													{ ?><td colspan="2"><img src="images/espaceur.gif" width="50" height="1"></td>
													<?php 
													} ?>
                       </tr>
                       <tr>
                  <?php 
									}?>
                  <td nowrap><span class="bleugrascalibri10">Cr&eacute;dits :</span></td>
                  <td nowrap>
                  <select name="codecentrefinancier#1##<?php echo $numordre?>" id="codecentrefinancier#1##<?php echo $numordre?>" class="noircalibri10" 
                  onChange="affichestructure(this)">
										<?php if($action=='creer')
                    {?><option value=""></option>
                    <?php
                    }
										else
										{ foreach($tab_centrefinancier as $codecentrefinancier=>$row_rs_centrefinancier)
											{ if($row_rs_centrefinancier['virtuel_ou_reel']=='1' && $row_rs_centrefinancier['codetypecredit']==$une_commandeimputationbudget['codetypecredit'])
												{?>
											<option value="<?php echo $row_rs_centrefinancier['codecentrefinancier'] ?>" <?php echo ($une_commandeimputationbudget['codecentrefinancier']==$row_rs_centrefinancier['codecentrefinancier']?'selected':'') ?>><?php echo $row_rs_centrefinancier['libcentrefinancier'] ?></option>
												<?php
												}
											}
										}?>
                  </select>
                  </td>
                  <td nowrap><span class="bleugrascalibri10">Enveloppe :</span></td>
                  <td nowrap>
                    <select name="codecentrecout#1##<?php echo $numordre?>" id="codecentrecout#1##<?php echo $numordre?>" class="noircalibri10" 
                    onChange="affichestructure(this)">
										<?php if($action=='creer')
                    {?><option value=""></option>
                    <?php
                    }
										else
                    { foreach($tab_centrecout as $codecentrecout=>$row_rs_centrecout)
                      { if($row_rs_centrecout['virtuel_ou_reel']=='1' && $row_rs_centrecout['codecentrefinancier']==$une_commandeimputationbudget['codecentrefinancier'])
                        {?>
                        <option value="<?php echo $row_rs_centrecout['codecentrecout'] ?>" <?php echo ($une_commandeimputationbudget['codecentrecout']==$row_rs_centrecout['codecentrecout']?'selected':'') ?>><?php echo $row_rs_centrecout['libcentrecout'] ?></option>
                        <?php
                        }
                      }
										}?>
                    </select>
                  </td>
                  <td nowrap>
										<?php 
                    $esteotpencours=true;
                    if(isset($tab_eotp[$une_commandeimputationbudget['codeeotp']]['esteotpencours']) && $tab_eotp[$une_commandeimputationbudget['codeeotp']]['esteotpencours']=='non')
                    { $esteotpencours=false;
                    }
                     ?>
                    <span class="<?php echo $esteotpencours?'bleugrascalibri10':'rougegrascalibri10' ?>">Source/EOTP :</span>
                    <?php 
                    if(!$esteotpencours)
                    { ?><img src="images/b_attention.png" width="16" height="16" id="sprytrigger_info_eotp_passe_1_<?php echo $numordre?>">
                        <div class="tooltipContent_cadre" id="info_eotp_passe_1_<?php echo $numordre?>">
                          <span class="noircalibri10">
                            Attention : l&rsquo;eotp &quot;<?php echo $tab_eotp[$une_commandeimputationbudget['codeeotp']]['libeotp'] ?>&quot; n&rsquo;a pas cours &agrave; la date de la commande<br>
                            S&eacute;lectionnez un autre eotp ou changez la date de la commande.</span>
                        </div>
                        <script type="text/javascript">
                          var sprytooltip_info_eotp_passe_1_<?php echo $numordre?> = new Spry.Widget.Tooltip("info_eotp_passe_1_<?php echo $numordre?>", "#sprytrigger_info_eotp_passe_1_<?php echo $numordre?>", {offsetX:-100, offsetY:20});
                        </script>
        
                    <?php 
                    } ?>
									</td>
                  <td>
                    <select name="codeeotp#1##<?php echo $numordre?>" id="codeeotp#1##<?php echo $numordre?>" class="noircalibri10" 
                    ><!--onChange="affichestructure(this)" -->
										<?php if($action=='creer')
                    {?><option value=""></option>
                    <?php
                    }
										else
                    { foreach($tab_eotp as $codeeotp=>$row_rs_eotp)
											{ if($row_rs_eotp['codecentrecout']==$une_commandeimputationbudget['codecentrecout'])
												{ $nbcontrat=0;
													foreach($tab_contrateotp as $uncodecontrat=>$un_contrat)
													{ foreach($un_contrat as $uncodeeotp=>$un_eotp)
														{ if($uncodeeotp==$codeeotp)
															{ $nbcontrat++;
															}
														}
													}
												?>
												 <option value="<?php echo $row_rs_eotp['codeeotp'] ?>" 
													<?php echo ($une_commandeimputationbudget['codeeotp']==$row_rs_eotp['codeeotp']?'selected':'') ?>>
													<?php echo $row_rs_eotp['libeotp'] ?></option>
											<?php
                        }
											}
										} ?>
                    </select>
                    <!-- utile pour parametres enregistres dans commandeimputationbudget -->
                    <input type="hidden" name="codecontrat#1##<?php echo $numordre?>" id="codecontrat#1##<?php echo $numordre?>" value="">
                  </td>
                  <td nowrap><span class="bleugrascalibri10">Engag&eacute; : </span></td>
                  <td nowrap><input name="montantengage#1##<?php echo $numordre?>"  type="text" id="montantengage#1##<?php echo $numordre?>" class="noircalibri10" style="text-align:right" value="<?php echo $une_commandeimputationbudget['montantengage'] ?>" size="8" maxlength="12"></td>
                  <td nowrap><span class="bleugrascalibri10">Pay&eacute; : </span></td>
                  <td nowrap><input name="montantpaye#1##<?php echo $numordre?>" type="text" id="montantpaye#1##<?php echo $numordre?>"  class="noircalibri10" style="text-align:right" 
                  						value="<?php echo $tab_commandeimputationbudget['1'][$numordre]['montantpaye'] ?>" size="8" maxlength="12" 
															<?php echo $montantpaye_automatique=='oui'?'readonly':'' ?>></td>
 									<td nowrap><?php //$first est teste meme si une seule ligne a imputer reel car une vierge est toujours proposee. $first est mis a false plus bas pour l'icone info
														 if($une_seule_ligne_a_imputer_reel && $first)
														 {?><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer">
                             	<input type="hidden" name="montantpaye_automatique" value="<?php echo $montantpaye_automatique ?>">
															<input type="image" name="submit_montantpaye_automatique" src="images/b_montantpaye_automatique_<?php echo $montantpaye_automatique ?>.png"
                              onClick="	if(document.forms['<?php echo $form_commande ?>'].montantpaye_automatique.value=='oui')
                                        { return confirm('Enregistrer et désactiver le calcul automatique du montant payé ?');
                                        }
                                        else
                                        { return confirm('Enregistrer et activer le calcul automatique du montant payé ?');
                                        }
                                       "
                               onKeyPress="return"><!-- pas de prise en compte de la touche ENTREE, sinon ce champ est modifie sans le vouloir par un user --> 
														 <?php 
														 }?>
                  </td>
 									<td nowrap>	
                    <?php /* <div id='soldeeotp'>
										 echo sprintf('%01.2f',$row_rs_eotp['solde']);
                    </div> */?>
                    <?php
                   	if($first)
                    { $first=false;?> 
                    <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_imputation_reelle_suppression">
                    <div class="tooltipContent_cadre" id="info_imputation_reelle_suppression">
                      <span class="noircalibri10">
                      Il faut au moins une ligne d&rsquo;imputation dite "r&eacute;elle". Par d&eacute;faut cette ligne est celle de l'imputation dite "virtuelle".<br>
                      Pour supprimer une ligne d'imputation "r&eacute;elle", quand il y en a plus d&rsquo;une, il suffit de ne pas renseigner le montant engag&eacute; de la ligne &agrave; supprimer : effacer le contenu y compris 0<br>
                      Si la case &rsquo;Auto&rsquo; est coch&eacute;e, le montant pay&eacute; est calcul&eacute; automatiquement par cumul des montants pay&eacute;s des liquidations et il n&rsquo;est<br>
                      pas possible de le modifier<br>
                      Pour pouvoir saisir le montant pay&eacute;, qui ne sera plus calcul&eacute; par cumul, la case &rsquo;Auto&rsquo; doit &ecirc;tre d&eacute;coch&eacute;e.<br>
                      A noter que le calcul automatique n&rsquo;est effectif que s&rsquo;il n&rsquo;y a qu&rsquo;une ligne d&rsquo;imputation r&eacute;elle (un seul montant engag&eacute; donc un seul pay&eacute;).
                      </span>
                    </div>
                    <script type="text/javascript">
                      var sprytooltip_imputation_reelle_suppression = new Spry.Widget.Tooltip("info_imputation_reelle_suppression", "#sprytrigger_info_imputation_reelle_suppression", { offsetX:-600, offsetY:0, closeOnTooltipLeave:true});
                    </script>
                    <?php 
										}?>
                  </td>
                 <?php 
									if($estderniereimputation && $action=='modifier')
									{?></tr></table></div></td>
                  <?php 
									}?>
                </tr>
                <?php
								} ?>
              </table>
            </td>
           </tr>
        </table>
       </td>
      </tr>
      <tr>
        <td>
        	<table border="0" cellpadding="0" cellspacing="2">
          	<tr>
              <td nowrap class="bleugrascalibri10">Nature :
              </td>
              <td nowrap>
                <select name="codenature" class="noircalibri10" id="codenature">
                  <?php
                  foreach($tab_nature as $codenature=>$row_rs_nature)
                  { if($row_rs_nature['codetypecredit']==$tab_commandeimputationbudget['1']['01']['codetypecredit'])
                    {?>
                    <option value="<?php echo $row_rs_nature['codenature'] ?>" <?php echo ($row_rs_commande['codenature']==$row_rs_nature['codenature']?'selected':'') ?>><?php echo $row_rs_nature['libnature'] ?></option>
                    <?php
                    }
                  } ?>
                </select>
              </td>
              <td><span class="bleugrascalibri10">Rubrique Comptable :&nbsp;</span></td>
              <td><span class="bleugrascalibri10">
                <input name="rubriquecomptable" class="noircalibri10" id="rubriquecomptable" value="<?php echo $row_rs_commande['rubriquecomptable']?>" size="4" maxlength="6">
              </span>
              </td>
              <td nowrap class="bleugrascalibri10">Nature dialogue gestion :
              </td>
              <td nowrap>
                <select name="codedialoguegestion" class="noircalibri10" id="codedialoguegestion" >
									<option value=""></option>
                  <?php
                  foreach($tab_dialoguegestion as $codedialoguegestion=>$row_rs_dialoguegestion)
                  { if($row_rs_dialoguegestion['codetypecredit']==$tab_commandeimputationbudget['1']['01']['codetypecredit'])
                    {?>
                    <option value="<?php echo $row_rs_dialoguegestion['codedialoguegestion'] ?>" <?php echo ($row_rs_commande['codedialoguegestion']==$row_rs_dialoguegestion['codedialoguegestion']?'selected':'') ?>><?php echo $row_rs_dialoguegestion['libdialoguegestion'] ?></option>
                    <?php
                    }
                  } ?>
                </select>
              </td>
            </tr>
         </table>
       </td>
     </tr>
     <tr>
       <td> 
         <table cellspacing="2" border="0">
							<?php 
              for($numligne=0;$numligne<=$nbcommandejustifiecontrat/4;$numligne++)
              {?>
              <tr>
                <td nowrap>
								<?php 
								if($numligne==0)
								{ ?><span class="bleugrascalibri10">Justification : </span> 
                  <?php 
								}?>
								</td>
								<?php 
                $numcolonne=0;
                while($numcolonne<=3)
                { list($numordre,$un_codecontratjustifie)=each($tab_commandejustifiecontrat);
                  ?>
                  <td>
                    <?php 
										if($numligne*4+$numcolonne<$nbcommandejustifiecontrat)
                    { ?>
                      <select name="codecontrat_a_justifier#<?php echo $numordre ?>" class="noircalibri10">
                      <?php
                      foreach($tab_contrat_a_justifier as $un_codecontrat_a_justifier=>$un_contrat_a_justifier)
                      { ?>
                        <option value="<?php echo $un_codecontrat_a_justifier ?>" <?php echo ($un_codecontratjustifie==$un_codecontrat_a_justifier)?'selected':''; ?>><?php echo $un_contrat_a_justifier['libcontrat'] ?></option>
                      <?php 
                      }?>
                      </select>
                      <?php
                    }?>
                  </td>
                  <?php 
                  $numcolonne++;
              	}
								?>
							</tr>
							<?php 
							}?>
						</table>
          </td>
        </tr>
  		</table>
    </div>
	</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
    	<table width="100%" border="0" cellpadding="0" class="table_cadre_arrondi">
      	<tr>
        	<td>
            <table border="0" cellspacing="2" cellpadding="0">
              <tr>
                <td valign="top"><span class="bleugrascalibri10">Note :&nbsp;</span><br>
                  <span class="bleucalibri9">(</span><span id="note#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_commande['note']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td><textarea name="note" cols="80" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'note#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_commande['note']; ?></textarea></td>
                <td><img src="images/espaceur.gif" alt="" width="20"></td>
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
    <td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer"></td>
  </tr>
</table>
</form>
</body>
</html>
    <?php
if(isset($rs_theme))mysql_free_result($rs_theme);
if(isset($rs_referent))mysql_free_result($rs_referent);
if(isset($rs_plancomptable))mysql_free_result($rs_plancomptable);
if(isset($rs_nature))mysql_free_result($rs_nature);
if(isset($rs_dialoguegestion))mysql_free_result($rs_dialoguegestion);
if(isset($rs_secrsite))mysql_free_result($rs_secrsite);
if(isset($rs_gestsif))mysql_free_result($rs_gestsif);
if(isset($rs_mission))mysql_free_result($rs_mission);
if(isset($rs_commandemigoliquidation))mysql_free_result($rs_commandemigoliquidation);
if(isset($rs_commandemigo))mysql_free_result($rs_commandemigo);
if(isset($rs_commande))mysql_free_result($rs_commande);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
