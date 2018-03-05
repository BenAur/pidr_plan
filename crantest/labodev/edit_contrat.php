<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{/* foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	} */
}
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$peut_etre_admin=estrole('sif',$tab_roleuser) || estrole('admingestfin',$tab_roleuser) || estrole('du',$tab_roleuser) || $admin_bd;
if($peut_etre_admin==false)
{?> acc&eacute;s restreint !!!
<?php
exit;
}
$nbcolmontantdetail=20;
$nblignemontantdetail=4;
$aujourdhui=date('Ymd');
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche='';
$tab_champs_ouinon_defaut=array('estprojet'=>'non');
$tab_champs_ouinon=array('estprojet');
$tab_champs_date=array( 'datedeb_contrat' =>  array("lib" => "Date de d&eacute;but de contrat","jj" => "","mm" => "","aaaa" => ""),
												'datefin_contrat' =>  array("lib" => "Date de fin de contrat","jj" => "","mm" => "","aaaa" => ""),
												'date_signature_contrat' =>  array("lib" => "Date de signature de contrat","jj" => "","mm" => "","aaaa" => ""),
												'date_limite_justification' =>  array("lib" => "Date limite de justification","jj" => "","mm" => "","aaaa" => ""),
												'date_am2i' =>  array("lib" => "Date transmission AM2I","jj" => "","mm" => "","aaaa" => ""));
$tab_champs_numerique=array('duree_mois' =>  array('lib' => 'Dur&eacute;e en mois','max_length'=>5),
														'montant_ht' =>  array('lib' => 'montant ht','string_format'=>'%01.2f','max_length'=>12),
														'permanent_mois' =>  array('lib' => 'Permanent.mois','max_length'=>5),
														'personnel_mois' =>  array('lib' => 'Personnel.mois','max_length'=>5)
														);
for($col=1;$col<=$nbcolmontantdetail;$col++)
{ $tab_champs_numerique['montant#'.$col]=array('lib'=>'montant colonne '.$col,'string_format'=>'%01.2f');
	for($ligne=1;$ligne<=$nblignemontantdetail;$ligne++)
	{ $tab_champs_numerique['montantdetail#'.$col.'##'.$ligne]=array('lib'=>'montant colonne '.$col.' ligne '.$ligne,'string_format'=>'%01.2f','max_length'=>12);
		$tab_champs_date['datemontant#'.$col.'##'.$ligne]=array("lib" => "Date colonne ".$col.' ligne '.$ligne,"jj" => "","mm" => "","aaaa" => "");
	}
}

$tab_doctorant_sujet=array();// table des sujets et des doctorants
$tab_part=array();// table des partenaires utilisee plusieurs fois en liste select
$tab_contratpart=array();//table des partenaires du contrat
$tab_contratmontantannee=array();//table de ventilation des montants par annee du contrat
$cumulmontant=0;
$form_contrat = "form_contrat";
$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$contrat_ancre=isset($_GET['contrat_ancre'])?$_GET['contrat_ancre']:(isset($_POST['contrat_ancre'])?$_POST['contrat_ancre']:"");
$calculdate_automatique=(isset($_POST['calculdate_automatique'])?$_POST['calculdate_automatique']:"oui");// champ hidden = valeur qu'a calculdate_automatique

//PG 20151117
// protection contre une erreur qui modifierait l'enreg. ''
if($action!='creer' && $codecontrat=='')
{	$erreur.="Tentative de modification de contrat sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de contrat sans n&deg; '.$msg);
}
//PG 20151117

$supprimer_une_pj=false;// $supprimer_une_pj=true si $submit='submit_supprimer_une_pj'
$envoyer_mail_creation_contrat=false;
//case a cocher calculdate_automatique
if(isset($_POST['submit_calculdate_automatique_x']))
{ $calculdate_automatique=($calculdate_automatique=='non'?'oui':'non');//le bouton=calculdate_automatique a la reception.
}

$submit="";$submit_val="";
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $submit=substr($postkey,0,$posdoublediese);
			$posdiese=strpos($submit,"#");
			if($posdiese!=false)
			{ $submit_val=substr($submit,$posdiese+1);//peut etre un numero ou autre (oui/non pour submit_verrouiller_sujet)
				$submit=substr($submit,0,$posdiese);
			}
		}
	}
}

if($submit=='submit_supprimer_une_pj')
{ $supprimer_une_pj=true;
	$codetypepj=$submit_val;
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "edit_contrat")) 
{ //partenaires
	foreach($_POST as $postkey=>$codepart)
	{ if(strpos($postkey,'codepart#')!==false && $codepart!='' )
		{ $numordre=substr($postkey,strlen('codepart#'));
			if(!in_array($codepart,$tab_contratpart))//enleve tout doublon choisi par erreur
			{ $tab_contratpart[$numordre]=$codepart;
			}
		}
	}
 	//annees montants
	foreach($_POST as $postkey=>$postval)
	{ if(strpos($postkey,'annee#')!==false && $postval!='')
		{ $numordre=substr($postkey,strlen('annee#'));
			$tab_contratmontantannee[$numordre]['annee']=$postval;
		}
		else if(strpos($postkey,'montant#')!==false)
		{ $numordre=substr($postkey,strlen('montant#'));
			if(isset($tab_contratmontantannee[$numordre]['annee']))
			{ $tab_contratmontantannee[$numordre]['montant']=$postval;
				//$tab_champs_numerique[$postkey]= array('lib' => 'montant annee','string_format'=>'%01.2f');
			}
		}
		else if(strpos($postkey,'montantdetail#')!==false)
		{ $posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{ $numordre=substr($postkey,strlen('montantdetail#'),$posdoublediese-strlen('montantdetail#'));
				$nummontant=substr($postkey,$posdoublediese+2);
				$tab_contratmontantdetail[$numordre][$nummontant]['montant']=$postval;
				//$tab_champs_numerique[$postkey]= array('lib' => 'montant annee detail','string_format'=>'%01.2f');
			}
		}
		else if(strpos($postkey,'datemontant_jj#')!==false)
		{ $posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{ $numordre=substr($postkey,strlen('datemontant_jj#'),$posdoublediese-strlen('datemontant_jj#'));
				$nummontant=substr($postkey,$posdoublediese+2);
				$tab_contratmontantdetail[$numordre][$nummontant]['jj']=$postval;
			}
		}
		else if(strpos($postkey,'datemontant_mm#')!==false)
		{ $posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{ $numordre=substr($postkey,strlen('datemontant_mm#'),$posdoublediese-strlen('datemontant_mm#'));
				$nummontant=substr($postkey,$posdoublediese+2);
				$tab_contratmontantdetail[$numordre][$nummontant]['mm']=$postval;
			}
		}
		else if(strpos($postkey,'datemontant_aaaa#')!==false)
		{ $posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{ $numordre=substr($postkey,strlen('datemontant_aaaa#'),$posdoublediese-strlen('datemontant_aaaa#'));
				$nummontant=substr($postkey,$posdoublediese+2);
				$tab_contratmontantdetail[$numordre][$nummontant]['aaaa']=$postval;
			}
		}
		else if(strpos($postkey,'reel#')!==false)
		{ $posdoublediese=strpos($postkey,'##');
			if($posdoublediese!==false)
			{ $numordre=substr($postkey,strlen('reel#'),$posdoublediese-strlen('reel#'));
				$nummontant=substr($postkey,$posdoublediese+2);
				$tab_contratmontantdetail[$numordre][$nummontant]['reel']='oui';
			}
		}
	}
	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_contrat(&$_POST,$tab_controle_et_format);

	
	//$erreur='erreur forcée';$warning='warning';
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		// on enregistre d'abord les nouveaux gest, financeur ou partenaire
		// org gest
		if(isset($_POST['nouveau_libcourtorggest']) && $_POST['nouveau_libcourtorggest']!='')
		{ $rs=mysql_query("select max(codeorggest) as maxcodeorggest from cont_orggest");
			if($row_rs=mysql_fetch_assoc($rs))
			{ $codeorggest=str_pad((string)((int)$row_rs['maxcodeorggest']+1), 3, "0", STR_PAD_LEFT);  
				$updateSQL ="INSERT into cont_orggest (codeorggest,date_deb,date_fin,libcourtorggest,liblongorggest,numordre) ".
											" values (".GetSQLValueString($codeorggest, "text").",'','',".GetSQLValueString($_POST['nouveau_libcourtorggest'], "text").",".GetSQLValueString($_POST['nouveau_liblongorggest'], "text").",".GetSQLValueString($codeorggest, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				$_POST['codeorggest']=$codeorggest;
			}
		}
		// orgfinanceur
		if(isset($_POST['nouveau_libcourtorgfinanceur']) && $_POST['nouveau_libcourtorgfinanceur']!='')
		{ $rs=mysql_query("select max(codeorgfinanceur) as maxcodeorgfinanceur from cont_orgfinanceur");
			if($row_rs=mysql_fetch_assoc($rs))
			{ $codeorgfinanceur=str_pad((string)((int)$row_rs['maxcodeorgfinanceur']+1), 3, "0", STR_PAD_LEFT);  
				$updateSQL ="INSERT into cont_orgfinanceur (codeorgfinanceur,date_deb,date_fin,libcourtorgfinanceur,liblongorgfinanceur,numordre) ".
											" values (".GetSQLValueString($codeorgfinanceur, "text").",'','',".GetSQLValueString($_POST['nouveau_libcourtorgfinanceur'], "text").",".GetSQLValueString($_POST['nouveau_liblongorgfinanceur'], "text").",".GetSQLValueString($codeorgfinanceur, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				$_POST['codeorgfinanceur']=$codeorgfinanceur;
			}
		}
		// projet
		if(isset($_POST['nouveau_libcourtprojet']) && $_POST['nouveau_libcourtprojet']!='')
		{ $rs=mysql_query("select max(codeprojet) as maxcodeprojet from cont_projet");
			if($row_rs=mysql_fetch_assoc($rs))
			{ $codeprojet=str_pad((string)((int)$row_rs['maxcodeprojet']+1), 3, "0", STR_PAD_LEFT);  
				$updateSQL ="INSERT into cont_projet (codeprojet,date_deb,date_fin,libcourtprojet,liblongprojet,numordre) ".
											" values (".GetSQLValueString($codeprojet, "text").",'','',".GetSQLValueString($_POST['nouveau_libcourtprojet'], "text").",".GetSQLValueString($_POST['nouveau_liblongprojet'], "text").",".GetSQLValueString($codeprojet, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				$_POST['codeprojet']=$codeprojet;
			}
		}
		// partenaire : si nouveau => ajoute aux partenaires du contrat
		if(isset($_POST['libcourtpart']) && $_POST['libcourtpart']!='')
		{ if($_POST['codepart_a_creer_ou_modifier']=='')
			{	$rs=mysql_query("select max(codepart) as maxcodepart from cont_part");
				if($row_rs=mysql_fetch_assoc($rs))
				{ $codepart=str_pad((string)((int)$row_rs['maxcodepart']+1), 5, "0", STR_PAD_LEFT);  
					$rs_fields = mysql_query('SHOW COLUMNS FROM cont_part');
					$first=true;
					$liste_champs="";$liste_val="";
					while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
					{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
						$liste_val.=($first?"":",");
						$first=false;
						if($row_rs_fields['Field']=='codepart')
						{ $liste_val.=GetSQLValueString($codepart, "text");
						}
						else if(isset($_POST[$row_rs_fields['Field']]))
						{ $liste_val.=GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
						}
						else
						{ $liste_val.="''";
						}
					}//fin while
					$updateSQL = "insert into cont_part (".$liste_champs.") values (".$liste_val.")";
					mysql_query($updateSQL) or die(mysql_error());
					$tab_contratpart[]=$codepart;
				}
			}
			else
			{	$codepart=$_POST['codepart_a_creer_ou_modifier'];
				$rs_fields = mysql_query('SHOW COLUMNS FROM cont_part');
				$updateSQL = "UPDATE cont_part SET ";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ if(isset($_POST[$row_rs_fields['Field']]))
					{ if($row_rs_fields['Field']!='codepart')
						{ $updateSQL.=$row_rs_fields['Field']."=".GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
						}
						$updateSQL.=",";
					}
				}//fin while
				$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
				$updateSQL.=" WHERE codepart=".GetSQLValueString($codepart, "text");
				mysql_query($updateSQL) or die(mysql_error());
				
			}
		}
		if($action=="creer")
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='contrat'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codecontrat=$row_seq_number['currentnumber'];
			$codecontrat=str_pad((string)((int)$codecontrat+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecontrat, "text")." where nomtable='contrat'") or  die(mysql_error());
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM contrat');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codecontrat')
				{ $liste_val.=GetSQLValueString($codecontrat, "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into contrat (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			$envoyer_mail_creation_contrat=true;
			$action="modifier";
		}//fin if creation
		$updateSQL = "UPDATE contrat SET ";
		$rs_fields = mysql_query('SHOW COLUMNS FROM contrat');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $Field=$row_rs_fields['Field'];
			if(!in_array($Field,array("codecontrat")))//pas de mise a jour de ces champs
			{ if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'])))
				{ if(in_array($Field, $tab_champs_ouinon)===false)
					{ $updateSQL.=$Field."=";
						if(array_key_exists($Field,$tab_champs_date)!==false)
						{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
						}
						else if($Field=='calculdate_automatique')
						{ $updateSQL.=GetSQLValueString($calculdate_automatique, "text");
						}
						else
						{ $updateSQL.=GetSQLValueString($_POST[$Field], "text");
						}
					}
					else
					{ $updateSQL.=$Field."='oui'";
					}
					$updateSQL.=",";
				}
				else//mis dans le formulaire mais non renvoye dans le POST (=>Off=non coche)
				{ if(in_array($Field, $tab_champs_ouinon)!==false)
					{ $updateSQL.=$Field."='non'".",";
					}
				}
			}
		}
		$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
		$updateSQL.=" WHERE codecontrat=".GetSQLValueString($codecontrat, "text");
		//echo $updateSQL;
		mysql_query($updateSQL) or die(mysql_error());
	
		// ----------------------------- affectation partenaires
		// suppression des partenaires existants
		mysql_query("delete from contratpart where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		// insertion des respscientifiques
		$numordre_consecutif=0;
		foreach($tab_contratpart as $numordre=>$codepart)
		{ $numordre_consecutif++;
			$nouveau_numordre=str_pad((string)($numordre_consecutif), 2, "0", STR_PAD_LEFT);
			$updateSQL ="INSERT into contratpart (codecontrat,codepart,numordre) ".
										" values (".GetSQLValueString($codecontrat, "text").",".GetSQLValueString($codepart, "text").",".GetSQLValueString($nouveau_numordre, "text").")";
			mysql_query($updateSQL) or die(mysql_error());
		}
		// ----------------------------- ventilation montants
		// suppression des annees-montants existants
		mysql_query("delete from contratmontantannee where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		// insertion des annees-montants existants
		foreach($tab_contratmontantannee as $numordre=>$contratmontantannee)
		{ if($contratmontantannee['montant']!='')
			{ $updateSQL ="INSERT into contratmontantannee (codecontrat,annee,montant,numordre) ".
										" values (".GetSQLValueString($codecontrat, "text").",".GetSQLValueString($contratmontantannee['annee'], "text").",".GetSQLValueString($contratmontantannee['montant'], "text").",".GetSQLValueString($numordre, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		// ----------------------------- ventilation montants par date
		// suppression des montants par date existants
		mysql_query("delete from contratmontantdetail where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
		// insertion des annees-montants existants
		$nummontantdetail=0;
		foreach($tab_contratmontantdetail as $numordre=>$tab_unecolonne)
		{ foreach($tab_unecolonne as $nummontant=>$un_montantdetail)
			{ $montant=$un_montantdetail['montant'];
			  if($montant!='')
			  { $nummontantdetail++;
					$reel=isset($tab_contratmontantdetail[$numordre][$nummontant]['reel'])?'oui':'non';
					$datemontant='';
					if($un_montantdetail['jj']!='')
					{ $datemontant=$un_montantdetail['aaaa'].'/'.$un_montantdetail['mm'].'/'.$un_montantdetail['jj'];
					}
					$updateSQL ="INSERT into contratmontantdetail (codecontrat,nummontantdetail,datemontant,montant,reel) ".
											" values (".GetSQLValueString($codecontrat, "text").",".GetSQLValueString($nummontantdetail, "text").",".GetSQLValueString($datemontant, "text").",".GetSQLValueString($montant, "text").",".GetSQLValueString($reel, "text").")";
					mysql_query($updateSQL) or die(mysql_error());
				}
			}
		}
	}// Fin de traitement des données si pas d'erreur

	// suppression pj (et rep(s) si vide)
	if($supprimer_une_pj)
	{ $updateSQL ="delete from contratpj ".
								" where codecontrat=".GetSQLValueString($codecontrat, "text").
								" and codetypepj=".GetSQLValueString($codetypepj, "text");
		mysql_query($updateSQL) or die(mysql_error());
		unlink($GLOBALS['path_to_rep_upload'] .'/contrat/'.$codecontrat.'/'.$codetypepj);
	}
	
	foreach ($_FILES["pj"]["name"] as $key => $nomfichier)//$key=codetypepj 
	{ $codetypepj=$key;
		if($nomfichier!='')
		{ clearstatcache();//efface le cache relatif a l'existence des repertoires
			$rep_upload=$GLOBALS['path_to_rep_upload'].'/contrat/'.$codecontrat ;
			if(!is_dir($rep_upload))//teste si existe 
			{ mkdir ($rep_upload);
			}
			$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$codetypepj);//$key=$codetypepj pour contrat
			if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
			{	// si existe deja
				$updateSQL= "delete from contratpj where codecontrat=".GetSQLValueString($codecontrat, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text");
				mysql_query($updateSQL) or die(mysql_error());
				$updateSQL="insert into contratpj (codecontrat,codetypepj,nomfichier)". 
										" values (".GetSQLValueString($codecontrat, "text").",".GetSQLValueString($codetypepj,"text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}
			else if($tab_res_upload['nomfichier']!='')
			{ $warning.='<br>'.$tab_res_upload['erreur'];
			}
		}
	}
	//fin ajout pj et reps eventuels

}
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//En creation, l'ancre est connue maintenant
$contrat_ancre=$codecontrat;
//Informations du contrat (un enreg. vide dans contrat pour "creer")
$query_contrat =	"SELECT contrat.*".
									" FROM contrat".
									" WHERE contrat.codecontrat=".GetSQLValueString($codecontrat,"text");
$rs_contrat=mysql_query($query_contrat) or die(mysql_error());
$row_rs_contrat=mysql_fetch_assoc($rs_contrat);
if($action=='creer')
{ $calculdate_automatique='oui';
}
else
{ $calculdate_automatique=($row_rs_contrat['calculdate_automatique']!='oui'?'non':$row_rs_contrat['calculdate_automatique']);
}
if($erreur=='')
{	// Liste des part du contrat 
	$rs=mysql_query("SELECT numordre,codepart from contratpart".
									" where contratpart.codecontrat=".GetSQLValueString($codecontrat, "text").
									" order by numordre") or die(mysql_error());
	$nbpart=mysql_num_rows($rs);
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_contratpart[$row_rs['numordre']]=$row_rs['codepart'];
	}
	
	// Liste des codecontratmontantannee du contrat 
	$rs=mysql_query("SELECT * from contratmontantannee where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_contratmontantannee[$row_rs['numordre']]=$row_rs;
	}
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_contrat = mysql_query('SHOW COLUMNS FROM contrat');
	while($row_rs_fields_contrat = mysql_fetch_assoc($rs_fields_contrat)) 
	{ $Field=$row_rs_fields_contrat['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_contrat[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_contrat['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_contrat[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
	//partenaires
	$nbpart=count($tab_contratpart);
}
// civilite
$query_rs_civilite = "SELECT codeciv, libcourt_fr as libciv FROM civilite WHERE codeciv<>'' ORDER BY codeciv ASC";
$rs_civilite = mysql_query($query_rs_civilite) or die(mysql_error());
// statutpart
$query_rs_statutpart = "SELECT codestatutpart, libcourtstatutpart as libstatutpart FROM cont_statutpart ORDER BY numordre ASC";
$rs_statutpart = mysql_query($query_rs_statutpart) or die(mysql_error());
// Liste des types de contrats
$rs_type=mysql_query("SELECT codetype,libcourttype as libtype".
										 " from cont_type".
										 " where (codetype in (select codetype from contrat where codecontrat=".GetSQLValueString($codecontrat, "text").")".
										 " 			or ".intersectionperiodes('date_deb','date_fin',"'".$row_rs_contrat['datedeb_contrat']."'","'".$row_rs_contrat['datefin_contrat']."'").
									 				")".
										 " order by libcourttype") or die(mysql_error());
// Liste des classif
$rs_classif = mysql_query("select codeclassif,numclassif,libcourtclassif as libclassif".
													" from cont_classif".
												  " where (codeclassif in (select codeclassif from contrat where codecontrat=".GetSQLValueString($codecontrat, "text").")".
												  " 			or ".intersectionperiodes('date_deb','date_fin',"'".$row_rs_contrat['datedeb_contrat']."'","'".$row_rs_contrat['datefin_contrat']."'").
																	")".
													" order by numclassif") or die(mysql_error());	
// Liste des orggest 
$rs_orggest=mysql_query("SELECT codeorggest,libcourtorggest as liborggest from cont_orggest order by libcourtorggest") or die(mysql_error());
// Liste des financeur 
$rs_orgfinanceur=mysql_query("SELECT codeorgfinanceur,libcourtorgfinanceur as liborgfinanceur from cont_orgfinanceur order by libcourtorgfinanceur") or die(mysql_error());
// Liste des projet 
$rs_projet=mysql_query("SELECT codeprojet,libcourtprojet as libprojet from cont_projet order by libcourtprojet") or die(mysql_error());
// Liste des secteurs
$rs_secteur=mysql_query("SELECT codesecteur,libcourtsecteur as libsecteur from cont_secteur order by libcourtsecteur") or die(mysql_error());
// Liste des nivconfident
$rs_nivconfident=mysql_query("SELECT codenivconfident,libcourtnivconfident as libnivconfident from cont_nivconfident  order by numordre") or die(mysql_error());
// Liste des types de convention
$rs_typeconvention=mysql_query("SELECT codetypeconvention,libcourttypeconvention as libtypeconvention from cont_typeconvention order by libcourttypeconvention") or die(mysql_error());
// Liste des themes
$rs_theme = mysql_query("select codestructure as codetheme,libcourt_fr as libtheme from structure where codestructure<>'00' and esttheme='oui' order by codestructure") or die(mysql_error());	
//  Liste des responsables scientifiques
// distinct ne devrait pas etre utilise mais pour l'instant un individu peut avoir deux s&eacute;jours en cours
$query_respscientifique= "SELECT distinct individu.codeindividu as coderespscientifique,concat(nom,' ',prenom) as nomprenom". 
												 " FROM individu,individusejour,corps,statutpers".
												 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
												 " and corps.codestatutpers=statutpers.codestatutpers".
												 " and corps.codestatutpers='01'".
												 " and individu.codeindividu<>'' and (corps.codecat='01' or corps.codecat='03' or corps.codecat='06')".
												 " UNION SELECT codeindividu,concat(nom,' ',prenom) as nomprenom from individu WHERE individu.codeindividu=''".
												 " ORDER BY nomprenom";
$rs_respscientifique=mysql_query($query_respscientifique) or die(mysql_error());
// distinct ne devrait pas etre utilis&eacute; mais pour l'instant un individu peut avoir deux s&eacute;jours en cours
$query_doctorant_sujet="SELECT distinct '1' as ordre, individu.codeindividu as codedoctorant,concat(nom,' ',prenom) as nomprenom,datedeb_sejour,sujet.titre_fr as titre_these". 
											 " FROM individu,corps,statutpers,individusejour ".
											 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
											 " left join sujet on individusujet.codesujet=sujet.codesujet".
											 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
											 " and corps.codecat='04'".
											 " UNION SELECT '0' as ordre,codeindividu as codedoctorant,'' as nomprenom,'' as datedeb_sejour,'' as titre_these from individu WHERE individu.codeindividu=''".
											 " ORDER BY ordre asc,substr(datedeb_sejour,1,4) desc,nomprenom asc";
$rs_doctorant_sujet=mysql_query($query_doctorant_sujet) or die(mysql_error());
while($row_rs_doctorant_sujet=mysql_fetch_assoc($rs_doctorant_sujet))
{ $tab_doctorant_sujet[$row_rs_doctorant_sujet['codedoctorant']]=$row_rs_doctorant_sujet;
}
// Liste des partenaires possibles
$query_rs= "SELECT cont_part.*,libcourtstatutpart FROM cont_part,cont_statutpart".
						" WHERE cont_part.codestatutpart=cont_statutpart.codestatutpart ORDER BY libcourtpart";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_part[$row_rs['codepart']]=$row_rs;
}
// initialisation de champs en creation
if($action=="creer")
{ $row_rs_contrat['datedeb_contrat']=date("Y").'/'.date("m").'/01';
	$row_rs_contrat['datefin_contrat']=date("Y/m/d",mktime(0,0,0,date("m"),0,(date("Y")+1)));
	$row_rs_contrat['duree_mois']=12;
	$tab_contratmontantannee['1']['annee']=date("Y");
	if(substr($row_rs_contrat['datedeb_contrat'],0,4)!=substr($row_rs_contrat['datefin_contrat'],0,4))
	{ $tab_contratmontantannee['2']['annee']=date("Y")+1;
	}
}
$nbannee=substr($row_rs_contrat['datefin_contrat'],0,4)-substr($row_rs_contrat['datedeb_contrat'],0,4)+1;

// utilise en budget : pas de modif de date possible
$estdansbudget=false;
$droitmodif_champ_budget=true;
$class_cache_ou_affiche_champ_budget='affiche';
if($action=='modifier')
{ $query_rs=" select codecontrat from commandeimputationbudget".
					" where codecontrat=".GetSQLValueString($codecontrat, "text").
					" UNION".
					" select commandeimputationbudget.codecontrat from commandeimputationbudget,contrateotp".
					" where commandeimputationbudget.codeeotp=contrateotp.codeeotp".
					" and contrateotp.codecontrat=".GetSQLValueString($codecontrat, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	if($row_rs=mysql_fetch_array($rs))
	{ $estdansbudget=true;
		$droitmodif_champ_budget=false;
		$class_cache_ou_affiche_champ_budget='cache';
	}
}

$estcontrateoptverrouille=false;
if($action=='modifier')
{ $query_rs=" select * from contrateotp".
					" where codecontrat=".GetSQLValueString($codecontrat, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	if($row_rs=mysql_fetch_array($rs))
	{ $estcontrateoptverrouille=true;
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des contrats <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<script src="_java_script/alerts.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
var frm=document.forms["<?php echo $form_contrat ?>"];
var tab_doctorant_sujet=new Array();
var tab_part=new Array();
var val_champ_modifie='aucun';
<?php
foreach($tab_doctorant_sujet as $codedoctorant=>$un_doctorant_sujet)
{?>
	tab_doctorant_sujet["<?php echo $codedoctorant ?>"]="<?php echo js_tab_val($un_doctorant_sujet['titre_these']); ?>";
<?php 
}?>

function affiche_sujet_ou_objet(codedoctorant)
{ return tab_doctorant_sujet[codedoctorant];
}
<?php
foreach($tab_part as $codepart=>$un_part)
{?>
	tab_part["<?php echo $codepart ?>"]=new Array();
	<?php
	foreach($un_part as $champ=>$valchamp)
	{?>
	tab_part["<?php echo $codepart ?>"]["<?php echo $champ ?>"]="<?php echo js_tab_val($valchamp);?>";
	<?php 
	}
}?>
function recherche_codepart(lib)
{ var codeparttrouve='';
	for (var codepart in tab_part)
	{ if(tab_part[codepart]['liblongpart']==lib) 
		{ codeparttrouve=codepart}
	}
	return codeparttrouve;
}
function detailpart(codepart)
{ var frm=document.forms["<?php echo $form_contrat ?>"];
	var partlist=frm.elements["codestatutpart"];
	for(indexpart=0;indexpart<=partlist.options.length-1;indexpart++)
	{ if(partlist.options[indexpart].value==tab_part[codepart]["codestatutpart"])
		{ partlist.selectedIndex=indexpart;
		}
	}
	frm.elements["libcourtpart"].value=tab_part[codepart]["libcourtpart"];
	frm.elements["liblongpart"].value=tab_part[codepart]["liblongpart"];
	frm.elements["nomcontactpart"].value=tab_part[codepart]["nomcontactpart"];
	frm.elements["prenomcontactpart"].value=tab_part[codepart]["prenomcontactpart"];
	frm.elements["telcontactpart"].value=tab_part[codepart]["telcontactpart"];
	frm.elements["telportcontactpart"].value=tab_part[codepart]["telportcontactpart"];
	frm.elements["fonctioncontactpart"].value=tab_part[codepart]["fonctioncontactpart"];
	frm.elements["emailcontactpart"].value=tab_part[codepart]["emailcontactpart"];
	frm.elements["adressecontactpart"].value=tab_part[codepart]["adressecontactpart"];
	frm.elements["notepart"].value=tab_part[codepart]["notepart"];
}
function avant_update_contrat_annee(champ)
{ val_champ_modifie=champ.value;//variable globale
}

function update_contrat_annee(champ)
{ var frm=document.forms["<?php echo $form_contrat ?>"];
	datedeb_contrat_aaaa=parseInt(frm.elements['datedeb_contrat_aaaa'].value,10);
	datedeb_contrat_mm=parseInt(frm.elements['datedeb_contrat_mm'].value,10);
	datedeb_contrat_jj=parseInt(frm.elements['datedeb_contrat_jj'].value,10);
	duree_mois=parseInt(frm.elements['duree_mois'].value,10);
	datefin_contrat_aaaa=parseInt(frm.elements['datefin_contrat_aaaa'].value,10);
	datefin_contrat_mm=parseInt(frm.elements['datefin_contrat_mm'].value,10);
	datefin_contrat_jj=parseInt(frm.elements['datefin_contrat_jj'].value,10);
	cumulmontant=0;
	if(isNaN(champ.value))
	{ alert('valeur invalide');
		champ.value=val_champ_modifie;
		return; 
	}
	/* if(frm.elements['datefin_contrat_aaaa'].value+frm.elements['datefin_contrat_mm'].value+frm.elements['datefin_contrat_jj'].value < frm.elements['datedeb_contrat_aaaa'].value+frm.elements['datedeb_contrat_mm'].value+frm.elements['datedeb_contrat_jj'].value || duree_mois<0)
	{ alert("date fin contrat < date d'effet ou durée en mois < 0 !");
		champ.value=val_champ_modifie;
		return;
	} */
	<?php if($calculdate_automatique=='oui')
	{?> 
		if((champ.name=='duree_mois' && frm.elements['duree_mois'].value!='') || (champ.name=='datedeb_contrat_mm' && frm.elements['datedeb_contrat_mm'].value!='') || (champ.name=='datedeb_contrat_aaaa' && frm.elements['datedeb_contrat_aaaa'].value!=''))
		{ datefin=new Date(datedeb_contrat_aaaa,datedeb_contrat_mm+duree_mois-1,datedeb_contrat_jj-1)
			datefin_contrat_aaaa=parseInt(datefin.getFullYear(),10);
		}
		/* else if((champ.name=='datefin_contrat_mm' && frm.elements['datefin_contrat_mm'].value!='') || (champ.name=='datefin_contrat_aaaa'  && frm.elements['datefin_contrat_aaaa'].value!=''))
		{ frm.elements['duree_mois'].value=(datefin_contrat_aaaa-datedeb_contrat_aaaa-1)*12+(12-datedeb_contrat_mm+1)+datefin_contrat_mm;
		} */
		num_derniere_col=<?php echo $nbcolmontantdetail ?>;
		trouve=false;
		while(num_derniere_col>=1 && !trouve)
		{ if(document.getElementById('annee#'+new String(num_derniere_col)).value!='')
			{ derniere_annee_avant=parseInt(document.getElementById('annee#'+new String(num_derniere_col)).value,10);
				trouve=true
			}
			else
			{ num_derniere_col--;
			}
		}	
		// si duree_mois ou datefin_contrat_mm ou datefin_contrat_aaaa : la datedeb_contrat ne change pas
		if(champ.name=='duree_mois' || champ.name=='datefin_contrat_mm' || champ.name=='datefin_contrat_aaaa' || champ.name=='datedeb_contrat_mm') 
		{ if(trouve)
			{ decalage=derniere_annee_avant-datefin_contrat_aaaa;
				if(decalage<=0)//ajout de colonnes ou aucune (0)
				{ for(i=1;i<-decalage+1;i++)
					{ document.getElementById('divcolannee#'+new String(num_derniere_col+i)).className='affiche';
						document.getElementById('annee#'+new String(num_derniere_col+i)).value=new String(derniere_annee_avant+i,10);
					}
				}
				else//vide colonnes
				{ confirme_efface=confirm('Des colonnes de détails seront effacées : confirmez-vous cet effacement ?')
					if(confirme_efface)
					{	for(i=0;i<decalage;i++)
						{	document.getElementById('annee#'+new String(num_derniere_col-i)).value='';
							document.getElementById('montant#'+new String(num_derniere_col-i)).value='';
							for(j=1;j<=<?php echo $nblignemontantdetail ?>;j++)
							{ //alert('montantdetail#'+new String(i)+'##'+new String(j)+' '+'montantdetail#'+new String(i-decalage)+'##'+new String(j))
								document.getElementById('montantdetail#'+new String(num_derniere_col-i)+'##'+new String(j)).value='';
								document.getElementById('datemontant_jj#'+new String(num_derniere_col-i)+'##'+new String(j)).value='';
								document.getElementById('datemontant_mm#'+new String(num_derniere_col-i)+'##'+new String(j)).value='';
								document.getElementById('datemontant_aaaa#'+new String(num_derniere_col-i)+'##'+new String(j)).value='';
							}
							document.getElementById('divcolannee#'+new String(num_derniere_col-i)).className='cache';
						}
					}
					else
					{ champ.value=val_champ_modifie;
						return;
					}
				}
			}
		}
		else//la datedeb a change (mois et/ou annee=> la date fin aussi)
		{	decalage=datedeb_contrat_aaaa-document.getElementById('annee#1').value;
			for(i=1;i<=num_derniere_col;i++)
			{	document.getElementById('annee#'+new String(i)).value=parseInt(document.getElementById('annee#'+new String(i)).value,10)+decalage;
				//document.getElementById('montant#'+new String(i)).value=document.getElementById('montant#'+new String(i-decalage)).value
				for(j=1;j<=<?php echo $nblignemontantdetail ?>;j++)
				{ if(document.getElementById('datemontant_aaaa#'+new String(i)+'##'+new String(j)).value!='')
					{ document.getElementById('datemontant_aaaa#'+new String(i)+'##'+new String(j)).value=parseInt(document.getElementById('datemontant_aaaa#'+new String(i)+'##'+new String(j)).value,10)+decalage;
					}
				}
			}
		}
		if(cumulmontant>frm.elements['montant_ht'].value)
		{ document.getElementById("cumulmontant").innerHTML='<span class="rougecalibri10">' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontant),'.',2,'') + '</span>';
		}
		else
		{ document.getElementById("cumulmontant").innerHTML='<span class="bleucalibri10">' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontant),'.',2,'') + '</span>';
		}
		if((champ.name=='duree_mois' && frm.elements['duree_mois'].value!='') || (champ.name=='datedeb_contrat_jj' && frm.elements['datedeb_contrat_jj'].value!='') || (champ.name=='datedeb_contrat_mm' && frm.elements['datedeb_contrat_mm'].value!='') || (champ.name=='datedeb_contrat_aaaa' && frm.elements['datedeb_contrat_aaaa'].value!=''))
		{ datefin=new Date(datedeb_contrat_aaaa,datedeb_contrat_mm+duree_mois-1,datedeb_contrat_jj-1)
			frm.elements['datefin_contrat_aaaa'].value=datefin.getFullYear();
			frm.elements['datefin_contrat_mm'].value=datefin.getMonth()+1;
			if(frm.elements['datefin_contrat_mm'].value.length==1) frm.elements['datefin_contrat_mm'].value='0'+frm.elements['datefin_contrat_mm'].value;
			frm.elements['datefin_contrat_jj'].value=datefin.getDate();
			if(frm.elements['datefin_contrat_jj'].value.length==1) frm.elements['datefin_contrat_jj'].value='0'+frm.elements['datefin_contrat_jj'].value;
		}
		else if((champ.name=='datefin_contrat_mm' && frm.elements['datefin_contrat_mm'].value!='') || (champ.name=='datefin_contrat_aaaa'  && frm.elements['datefin_contrat_aaaa'].value!=''))
		{ frm.elements['duree_mois'].value=(datefin_contrat_aaaa-datedeb_contrat_aaaa-1)*12+(12-datedeb_contrat_mm+1)+datefin_contrat_mm;
		}
	<?php 
	}?>
	return;
}

function calculcumul()
{ var frm=document.forms["<?php echo $form_contrat ?>"];
	cumulmontant=0;
	for(i=0;i<frm.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('montant#')).length) =='montant#')
		{ if(!isNaN(frm.elements[i].value) && frm.elements[i].value!='')
			{ cumulmontant+=parseFloat(frm.elements[i].value);
			}
		}
	}
	if(cumulmontant>frm.elements['montant_ht'].value)
	{ document.getElementById("cumulmontant").innerHTML='<span class="rougecalibri10">Cumul saisi : ' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontant),'.',2,' ') + '</span>';
	}
	else
	{ document.getElementById("cumulmontant").innerHTML='<span class="bleucalibri10">Cumul saisi : ' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontant),'.',2,' ') + '</span>';
	}
}

function calculcumuldetail(montantdetail)
{ //montantdetail est de la forme montantdetail#numordre##nummontant : numordre = nieme annee (colonne), nummontant = nieme ligne 
	var frm=document.forms["<?php echo $form_contrat ?>"];
	cumulmontantannee=0;
	posdiese=montantdetail.indexOf("#");
	posdoublediese=montantdetail.indexOf("##");
	numordre=montantdetail.substring(posdiese+1,posdoublediese);
	for(i=0;i<frm.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('montantdetail#'+numordre)).length)=='montantdetail#'+numordre)
		{ if(!isNaN(frm.elements[i].value) && frm.elements[i].value!='')
			{ cumulmontantannee+=parseFloat(frm.elements[i].value);
			}
		}
	}
	
	if(cumulmontantannee>frm.elements['montant#'+numordre].value)
	{ document.getElementById('cumulmontantannee#'+numordre).innerHTML='<span class="rougecalibri10">Cumul saisi : ' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontantannee),'.',2,' ') + '</span>';
	}
	else
	{ document.getElementById('cumulmontantannee#'+numordre).innerHTML='<span class="bleucalibri10">Cumul saisi : ' + formate_nombre('<?php echo $form_contrat ?>',new String(cumulmontantannee),'.',2,' ') + '</span>';
	}
}

</script>	

</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'\\n':'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>
<form name="<?php echo $form_contrat ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="return controle_form_contrat('<?php echo $form_contrat ?>')">
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="codecontrat" value="<?php echo $codecontrat ?>" >
<input type="hidden" name="MM_update" value="edit_contrat">
<input type="hidden" name="contrat_ancre" value="<?php echo $codecontrat; ?>">

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_contrat.gif','titrepage'=>'Contrat','lienretour'=>'gestioncontrats.php?contrat_ancre='.$contrat_ancre,'texteretour'=>'Retour &agrave; la gestion des contrats',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;
    </td>
  </tr>
  <tr>
    <td>
      <table border="0">
        <tr>
          <td>
            <span class="bleucalibri9">N&deg; interne : </span>
            <span class="mauvegrascalibri9"><?php echo $row_rs_contrat['codecontrat'] ?></span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
          <td>
            <table width="100%" border="0" cellpadding="3" cellspacing="0">
              <tr>
              	<td nowrap><span class="bleugrascalibri10">Projet : </span><input type="checkbox" name="estprojet" <?php echo $row_rs_contrat['estprojet']=='oui'?"checked":"" ?>></td>
                <td nowrap>
                  <span class="bleugrascalibri10">Date d'effet </span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri10"> : </span>
                  <?php 
									if(!$droitmodif_champ_budget)
									{ echo aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/');
									}
									?>
                  <div class="<?php echo $class_cache_ou_affiche_champ_budget ?>">
                    <input name="datedeb_contrat_jj" type="text" class="noircalibri10" id="datedeb_contrat_jj" value="<?php echo substr($row_rs_contrat['datedeb_contrat'],8,2); ?>" size="2" maxlength="2"
                      onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else { if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}; update_contrat_annee(this);calculcumul();}
                                ">
                    <input name="datedeb_contrat_mm" type="text" class="noircalibri10" id="datedeb_contrat_mm" value="<?php echo substr($row_rs_contrat['datedeb_contrat'],5,2); ?>" size="2" maxlength="2"
                      onFocus="avant_update_contrat_annee(this)" 
                      onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else { if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}; update_contrat_annee(this);calculcumul(); }
                                ">
                    <input name="datedeb_contrat_aaaa" type="text" class="noircalibri10" id="datedeb_contrat_aaaa" value="<?php echo substr($row_rs_contrat['datedeb_contrat'],0,4); ?>" size="4" maxlength="4" 
                      onFocus="avant_update_contrat_annee(this)" 
                      onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {  if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}; update_contrat_annee(this);calculcumul(); }
                                ">
                  </div>
                  <span class="bleugrascalibri10">&nbsp;Dur&eacute;e mois :&nbsp;</span>
                    <input name="duree_mois" type="text" style="text-align:right" class="noircalibri10"  value="<?php echo $row_rs_contrat['duree_mois'] ?>" size="5" maxlength="5"
                      onFocus="avant_update_contrat_annee(this)" 
                      onChange="if(isNaN(this.value) || this.value==0) { alert('Durée invalide'); } else { update_contrat_annee(this);calculcumul(); }
                                ">
                  <span class="bleugrascalibri10">Date fin :&nbsp;</span>
                  
                    <input name="datefin_contrat_jj" type="text" class="noircalibri10" id="datefin_contrat_jj" value="<?php echo substr($row_rs_contrat['datefin_contrat'],8,2); ?>" size="2" maxlength="2"
                      onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else { if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}; update_contrat_annee(this);calculcumul();}
                                ">
                    <input name="datefin_contrat_mm" type="text" class="noircalibri10" id="datefin_contrat_mm" value="<?php echo substr($row_rs_contrat['datefin_contrat'],5,2); ?>" size="2" maxlength="2"
                      onFocus="avant_update_contrat_annee(this)"
                      onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else { if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}; update_contrat_annee(this);calculcumul(); }
                                ">
                    <input name="datefin_contrat_aaaa" type="text" class="noircalibri10" id="datefin_contrat_aaaa" value="<?php echo substr($row_rs_contrat['datefin_contrat'],0,4); ?>" size="4" maxlength="4"
                      onFocus="avant_update_contrat_annee(this)"
                      onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else { if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}; update_contrat_annee(this);calculcumul(); }
                                ">
                  
                  <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_date_contrat">
                  <div class="tooltipContent_cadre" id="info_date_contrat">
                    <span class="noircalibri10">
                    <?php 
										if($droitmodif_champ_budget)
										{	?>
                    Si le calcul de date automatique est activ&eacute;, les r&egrave;gles suivantes s&rsquo;appliquent.<br>
                    La <b>date d&rsquo;effet du contrat</b> et la <b>dur&eacute;e en mois</b> conditionnent l'affichage de la <b>date de fin</b> et des ann&eacute;es de ventilation du montant du contrat.<br>
										La <b>date d&rsquo;effet du contrat</b> et la <b>date de fin</b> conditionnent l'affichage de la <b>dur&eacute;e en mois</b> et des ann&eacute;es de ventilation du montant du contrat.<br>
										Le nombre d'années de ventilation est compris entre 1 et <?php echo $nbcolmontantdetail ?>.<br>
										Si le nombre d&rsquo;ann&eacute;es du contrat est inf&eacute;rieur au nombre de colonnes de d&eacute;tail par ann&eacute;e, il vous est demand&eacute; d&rsquo;en confirmer la suppression.<br>
										Si l&rsquo;ann&eacute;e de la <b>date d&rsquo;effet du contrat</b> est modifi&eacute;e, les colonnes de d&eacute;tail sont modifi&eacute;es en cons&eacute;quence : les ann&eacute;es sont d&eacute;cal&eacute;es.<br>
										Apr&eacute;s modification de date ou de dur&eacute;e, un enregistrement est n&eacute;cessaire afin de corriger l'affichage des <img src="images/b_attention.png" alt="Retard" width="12" height="12"> des montants d&eacute;taill&eacute;s.
                    <?php 
										}
                    else
                    {?>
                    Au moins une imputation a &eacute;t&eacute; r&eacute;alis&eacute;e sur ce contrat : la date de d&eacute;but et<br>
                    le <?php echo $GLOBALS['libcourt_theme_fr'] ?> ne peuvent &ecirc;tre modifi&eacute;s.<br>
                    Adressez-vous au service informatique pour faire modifier ces informations.
                    <?php 
										}?>
                    </span>
                  </div>
                  <script type="text/javascript">
                    var sprytooltip_date_contrat = new Spry.Widget.Tooltip("info_date_contrat", "#sprytrigger_info_date_contrat", {useEffect:"blind", offsetX:20, offsetY:20});
                  </script>
                  &nbsp;&nbsp;<span class="bleugrascalibri10">Date de signature :</span>
                  <input name="date_signature_contrat_jj" type="text" class="noircalibri10" id="date_signature_contrat_jj" value="<?php echo substr($row_rs_contrat['date_signature_contrat'],8,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                              ">
                  <input name="date_signature_contrat_mm" type="text" class="noircalibri10" id="date_signature_contrat_mm" value="<?php echo substr($row_rs_contrat['date_signature_contrat'],5,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                    					">
                  <input name="date_signature_contrat_aaaa" type="text" class="noircalibri10" id="date_signature_contrat_aaaa" value="<?php echo substr($row_rs_contrat['date_signature_contrat'],0,4); ?>" size="4" maxlength="4"
                    onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
                  <input type="image" name="image_submit_non_visible" id="image_submit_non_visible" src="images/espaceur.gif">
                  <input type="hidden" name="calculdate_automatique" value="<?php echo $calculdate_automatique ?>">
                  <input type="image" name="submit_calculdate_automatique" id="sprytrigger_info_calculdate_automatique" src="images/b_calculdate_automatique_<?php echo $calculdate_automatique ?>.png"
                  onClick="	if(document.forms['<?php echo $form_contrat ?>'].calculdate_automatique.value=='oui')
                            { return confirm('Enregistrer et désactiver le calcul automatique de dates ?');
                            }
                            else
                            { return confirm('Enregistrer et activer le calcul automatique de dates ?');
                            }
                           ">
                  <div class="tooltipContent_cadre" id="info_calculdate_automatique">
                    <span class="noircalibri10">
                    <?php 
										if($calculdate_automatique=='oui')
										{	?>
                    D&eacute;sactive le calcul automatique des dates, de la dur&eacute;e ainsi que<br>
                    l&rsquo;affichage automatique imm&eacute;diat des cadres de d&eacute;tail par ann&eacute;e.<br>
                    L&rsquo;enregistrement du formulaire :<br>
                    - n&rsquo;affecte pas les dates et dur&eacute;e : l&rsquo;utilisateur doit contr&ocirc;ler ce qu&rsquo;il saisit<br>
                    - modifie le nombre de cadres de d&eacute;tail par ann&eacute;e si n&eacute;cessaire.
                    <?php 
										}
                    else
                    {?>
                    Active le calcul automatique des dates, de la dur&eacute;e ainsi que<br>
                    l&rsquo;affichage automatique imm&eacute;diat des cadres de d&eacute;tail par ann&eacute;e.<br>
                    <?php 
										}?>
                    </span>
                  </div>
                  <script type="text/javascript">
                    var sprytooltip_info_calculdate_automatique = new Spry.Widget.Tooltip("info_calculdate_automatique", "#sprytrigger_info_calculdate_automatique", {useEffect:"blind", offsetX:20, offsetY:20});
                  </script>
                  &nbsp;&nbsp;<span class="bleugrascalibri10">Date justification :</span>
                  <input name="date_limite_justification_jj" type="text" class="noircalibri10" id="date_limite_justification_jj" value="<?php echo substr($row_rs_contrat['date_limite_justification'],8,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                              ">
                  <input name="date_limite_justification_mm" type="text" class="noircalibri10" id="date_limite_justification_mm" value="<?php echo substr($row_rs_contrat['date_limite_justification'],5,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                    					">
                  <input name="date_limite_justification_aaaa" type="text" class="noircalibri10" id="date_limite_justification_aaaa" value="<?php echo substr($row_rs_contrat['date_limite_justification'],0,4); ?>" size="4" maxlength="4"
                    onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
                </td>
              </tr>
              <tr>
              	<td></td>
                <td>&nbsp;&nbsp;<span class="bleugrascalibri10">Date transmission AM2I :</span>
                  <input name="date_am2i_jj" type="text" class="noircalibri10" id="date_am2i_jj" value="<?php echo substr($row_rs_contrat['date_am2i'],8,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                              ">
                  <input name="date_am2i_mm" type="text" class="noircalibri10" id="date_am2i_mm" value="<?php echo substr($row_rs_contrat['date_am2i'],5,2); ?>" size="2" maxlength="2"
                    onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                    					">
                  <input name="date_am2i_aaaa" type="text" class="noircalibri10" id="date_am2i_aaaa" value="<?php echo substr($row_rs_contrat['date_am2i'],0,4); ?>" size="4" maxlength="4"
                    onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                    					">
                </td>
              </tr>
            </table>                
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td><span class="bleugrascalibri10">Secteur d&rsquo;activit&eacute; :
                  <select name="codesecteur" class="noircalibri10" id="codesecteur" >
                    <?php
                      while($row_rs_secteur=mysql_fetch_assoc($rs_secteur))
                      { ?>
                    <option value="<?php echo $row_rs_secteur['codesecteur'] ?>" <?php echo ($row_rs_contrat['codesecteur']==$row_rs_secteur['codesecteur']?'selected':'') ?>><?php echo $row_rs_secteur['libsecteur'] ?></option>
                    <?php
                      } ?>
                  </select>
                </span>
                </td>
                <td><span class="bleugrascalibri10">Niveau de confidentialit&eacute; :&nbsp;</span>
                  <select name="codenivconfident" class="noircalibri10" id="codenivconfident" >
                    <?php
                      while($row_rs_nivconfident=mysql_fetch_assoc($rs_nivconfident))
                      { ?>
                    <option value="<?php echo $row_rs_nivconfident['codenivconfident'] ?>" <?php echo ($row_rs_contrat['codenivconfident']==$row_rs_nivconfident['codenivconfident']?'selected':'') ?>><?php echo $row_rs_nivconfident['libnivconfident'] ?></option>
                    <?php
                      } ?>
                  </select>
                </td>
                <td><span class="bleugrascalibri10">Type de convention :&nbsp;</span>
                  <select name="codetypeconvention" class="noircalibri10" id="codetypeconvention" >
                    <?php
                      while($row_rs_typeconvention=mysql_fetch_assoc($rs_typeconvention))
                      { ?>
                    <option value="<?php echo $row_rs_typeconvention['codetypeconvention'] ?>" <?php echo ($row_rs_contrat['codetypeconvention']==$row_rs_typeconvention['codetypeconvention']?'selected':'') ?>><?php echo $row_rs_typeconvention['libtypeconvention'] ?></option>
                    <?php
                      } ?>
                  </select>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td nowrap>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                	<?php 
									if(!$estcontrateoptverrouille)
                  {?><img src="images/b_plus.png" alt="Nouveau" name="image_nouveau_orggest" align="texttop" id="image_nouveau_orggest" onClick="javascript:
                                  nouveau=document.getElementById('nouveau_orggest');
                                  if(nouveau.className=='affiche')
                                  { nouveau.className='cache';
                                    this.src='images/b_plus.png';
                                  }
                                  else 
                                  { nouveau.className='affiche';
                                    this.src='images/b_moins.png';
                                  }"
                                  > 
                   <?php 
									 }?>
                    <span class="bleugrascalibri10">&nbsp;Gestionnaire :&nbsp;</span>
                </td>
                <td>
									<?php 
									if($estcontrateoptverrouille)
                  { while($row_rs_orggest=mysql_fetch_assoc($rs_orggest))
                    { echo ($row_rs_contrat['codeorggest']==$row_rs_orggest['codeorggest']?$row_rs_orggest['liborggest']:'');
                    }
                    mysql_data_seek($rs_orggest,0);
										?>
                    <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_orggest_verrouille">
                      <div class="tooltipContent_cadre" id="info_orggest_verrouille">
                        <span class="noircalibri10">
                        L&rsquo;organisme gestionnaire est verrouill&eacute; : un EOTP y est associ&eacute;.<br>
                        Pour le d&eacute;verrouiller, il faut dissocier cet EOTP dans la gestion contrat/EOTP
                        </span>
                      </div>
                      <script type="text/javascript">
                        var sprytooltip_info_orggest_verrouille = new Spry.Widget.Tooltip("info_orggest_verrouille", "#sprytrigger_info_orggest_verrouille", {useEffect:"blind", offsetX:20, offsetY:20});
                      </script>
                  <?php 
									}
                  ?>
                  <div class="<?php echo $estcontrateoptverrouille?'cache':'affiche' ?>">
                    <select name="codeorggest" class="noircalibri10" id="codeorggest" >
                      <?php
                      while($row_rs_orggest=mysql_fetch_assoc($rs_orggest))
                      { ?>
                          <option value="<?php echo $row_rs_orggest['codeorggest'] ?>" <?php echo ($row_rs_contrat['codeorggest']==$row_rs_orggest['codeorggest']?'selected':'') ?>><?php echo $row_rs_orggest['liborggest'] ?></option>
                          <?php
                      } ?>
                    </select>
                  </div>
                </td>
                <td><span class="bleugrascalibri10">
                  <img src="images/b_plus.png" alt="Nouveau" name="image_nouveau_orgfinanceur" align="texttop" id="image_nouveau_orgfinanceur" onClick="javascript:
                                  nouveau=document.getElementById('nouveau_orgfinanceur');
                                  if(nouveau.className=='affiche')
                                  { nouveau.className='cache';
                                    this.src='images/b_plus.png';
                                  }
                                  else 
                                  { nouveau.className='affiche';
                                    this.src='images/b_moins.png';
                                  }"
                                  > &nbsp;Financeur :&nbsp;</span>
                </td>
                <td>
                  <select name="codeorgfinanceur" class="noircalibri10" id="codeorgfinanceur" >
                    <?php
                    while($row_rs_orgfinanceur=mysql_fetch_assoc($rs_orgfinanceur))
                    { ?>
                      <option value="<?php echo $row_rs_orgfinanceur['codeorgfinanceur'] ?>" <?php echo ($row_rs_contrat['codeorgfinanceur']==$row_rs_orgfinanceur['codeorgfinanceur']?'selected':'') ?>><?php echo $row_rs_orgfinanceur['liborgfinanceur'] ?></option>
                    <?php
                    } ?>
                    </select>
                </td>
                <td><span class="bleugrascalibri10">
                  <img src="images/b_plus.png" alt="Nouveau" name="image_nouveau_projet" align="texttop" id="image_nouveau_projet" onClick="javascript:
                                  nouveau=document.getElementById('nouveau_projet');
                                  if(nouveau.className=='affiche')
                                  { nouveau.className='cache';
                                    this.src='images/b_plus.png';
                                  }
                                  else 
                                  { nouveau.className='affiche';
                                    this.src='images/b_moins.png';
                                  }"
                                  > &nbsp;Projet :&nbsp;</span>
                  <select name="codeprojet" class="noircalibri10" id="codeprojet" >
                    <?php
                    while($row_rs_projet=mysql_fetch_assoc($rs_projet))
                    { ?>
                    <option value="<?php echo $row_rs_projet['codeprojet'] ?>" <?php echo ($row_rs_contrat['codeprojet']==$row_rs_projet['codeprojet']?'selected':'') ?>><?php echo $row_rs_projet['libprojet'] ?></option>
                    <?php
                    } ?>
                  </select></td>
                </tr>
              <tr>
                <td colspan="2" align="right"><?php $class_cache_ou_affiche="cache";?>
                  <div id="nouveau_orggest" class="<?php echo $class_cache_ou_affiche?>">
                    <table border="0" cellspacing="3" cellpadding="0">
                      <tr>
                        <td class="mauvecalibri10">
                        	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_nouveau_orggest">
                            <div class="tooltipContent_cadre" id="info_nouveau_orggest">
                              <span class="noircalibri10">
																Cr&eacute;ation d&rsquo;un nouvel organisme gestionnaire.<br>
                                Vous pouvez, &agrave; tout moment, cr&eacute;er un nouvel organisme gestionaire apr&egrave;s avoir v&eacute;rifi&eacute; qu&rsquo;il ne figure pas dans la liste propos&eacute;e.<br>
                                Apr&egrave;s avoir rempli les champs, vous devrez enregistrer le formulaire pour le voir apparaître dans la liste des organismes gestionnaires.<br>
                                La modification ou la suppression d&rsquo;un organisme gestionnaire ne peut etre faite que par l&rsquo;administrateur de la base de donn&eacute;es
                              </span>
                            </div>
                            <script type="text/javascript">
                              var sprytooltip_nouveau_orggest = new Spry.Widget.Tooltip("info_nouveau_orggest", "#sprytrigger_info_nouveau_orggest", {useEffect:"blind", offsetX:20, offsetY:20});
                            </script>
          							</td>
                        <td class="mauvecalibri10">nom abr&eacute;g&eacute; : </td>
                        <td><input name="nouveau_libcourtorggest" type="text" id="nouveau_libcourtorggest" size="50"></td>
                        </tr>
                      <tr>
                        <td class="mauvecalibri10">&nbsp;</td>
                        <td class="mauvecalibri10">nom :</td>
                        <td><input name="nouveau_liblongorggest" type="text" id="nouveau_liblongorggest" size="100"></td>
                      </tr>
                    </table>
                  </div>
                </td>
                <td colspan="2">
                  <div id="nouveau_orgfinanceur" class="<?php echo $class_cache_ou_affiche?>">
                    <table border="0" cellspacing="3" cellpadding="0">
                      <tr>
                        <td class="mauvecalibri10">
                        	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_nouveau_orgfinanceur">
                            <div class="tooltipContent_cadre" id="info_nouveau_orgfinanceur">
                              <span class="noircalibri10">
																Cr&eacute;ation d&rsquo;un nouvel organisme financeur.<br>
                                Vous pouvez, &agrave; tout moment, cr&eacute;er un nouvel organisme financeur apr&egrave;s avoir v&eacute;rifi&eacute; qu&rsquo;il ne figure pas dans la liste propos&eacute;e.<br>
                                Apr&egrave;s avoir rempli les champs, vous devrez enregistrer le formulaire pour le voir apparaître dans la liste des organismes financeurs.<br>
                                La modification ou la suppression d&rsquo;un organisme financeur ne peut etre faite que par l&rsquo;administrateur de la base de donn&eacute;es
                              </span>
                            </div>
                            <script type="text/javascript">
                              var sprytooltip_nouveau_orgfinanceur = new Spry.Widget.Tooltip("info_nouveau_orgfinanceur", "#sprytrigger_info_nouveau_orgfinanceur", {useEffect:"blind", offsetX:20, offsetY:20});
                            </script>
												</td>
                        <td class="mauvecalibri10">nom abr&eacute;g&eacute; : </td>
                        <td><input name="nouveau_libcourtorgfinanceur" type="text" id="nouveau_libcourtorgfinanceur" size="50"></td>
                      </tr>
                      <tr>
                        <td class="mauvecalibri10">&nbsp;</td>
                        <td class="mauvecalibri10">nom :</td>
                        <td><input name="nouveau_liblongorgfinanceur" type="text" id="nouveau_liblongorgfinanceur" size="100"></td>
                      </tr>
                    </table>
                  </div>
                </td>
                <td>
                  <div id="nouveau_projet" class="<?php echo $class_cache_ou_affiche?>">
                    <table border="0" cellspacing="3" cellpadding="0">
                      <tr>
                        <td class="mauvecalibri10">
                        	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_nouveau_projet">
                            <div class="tooltipContent_cadre" id="info_nouveau_projet">
                              <p class="noircalibri10">
                                Cr&eacute;ation d&rsquo;un nouveau projet.<br>
                                Vous pouvez, &agrave; tout moment, cr&eacute;er un nouveau projet apr&egrave;s avoir v&eacute;rifi&eacute; qu&rsquo;il ne figure pas dans la liste propos&eacute;e.<br>
                                Apr&egrave;s avoir rempli les champs, vous devrez enregistrer le formulaire pour le voir apparaître dans la liste des projets.<br>
                                La modification ou la suppression d&rsquo;un projet ne peut etre faite que par l&rsquo;administrateur de la base de donn&eacute;es
															</p></div>
                            <script type="text/javascript">
                              var sprytooltip_nouveau_projet = new Spry.Widget.Tooltip("info_nouveau_projet", "#sprytrigger_info_nouveau_projet", {useEffect:"blind", offsetX:20, offsetY:20});
                            </script>
												</td>
                        <td class="mauvecalibri10">nom abr&eacute;g&eacute; : </td>
                        <td><input name="nouveau_libcourtprojet" type="text" id="nouveau_libcourtprojet" size="50" maxlength="50"></td>
                      </tr>
                      <tr>
                        <td class="mauvecalibri10">&nbsp;</td>
                        <td class="mauvecalibri10">nom :</td>
                        <td><input name="nouveau_liblongprojet" type="text" id="nouveau_liblongprojet" size="100" maxlength="100"></td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
              <tr>
                <td><span class="bleugrascalibri10">&nbsp;Type :&nbsp;&nbsp; </span></td>
                <td><select name="codetype" class="noircalibri10" id="codetype" 
                    onChange="if(this.value=='001' || this.value=='002' || this.value=='020')
                              { document.getElementById('div_doctorant').className='affiche';
                              }
                              else
                              { document.getElementById('div_doctorant').className='cache';
                                document.getElementById('div_objet').className='affiche';
                                document.getElementById('div_titre_these').className='cache';
                              }
                              ">
                  <?php
                while($row_rs_type=mysql_fetch_assoc($rs_type))
                { ?>
                  <option value="<?php echo $row_rs_type['codetype'] ?>" <?php echo ($row_rs_contrat['codetype']==$row_rs_type['codetype']?'selected':'') ?>><?php echo $row_rs_type['libtype'] ?></option>
                  <?php
                } ?>
                  </select>
                  <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_these_ou_accomp">
                  <div class="tooltipContent_cadre" id="info_these_ou_accomp">
                    <span class="noircalibri10">
											Le type &rsquo;Accompagnement th&egrave;se&rsquo; ou &rsquo;CIFRE&rsquo; (fonctionnement) et le type &rsquo;th&egrave;se&rsquo; (salaire) sont associ&eacute;s &agrave; une liste de choix d&rsquo;un doctorant.<br>
                      Une fois le doctorant choisi, le champ &rsquo;Objet&rsquo; est remplac&eacute; par le sujet de th&egrave;se de ce dernier.
                    </span>
                  </div>
                  <script type="text/javascript">
                    var sprytooltip_these_ou_accomp = new Spry.Widget.Tooltip("info_these_ou_accomp", "#sprytrigger_info_these_ou_accomp", {useEffect:"blind", offsetX:20, offsetY:20});
                  </script>

                </td>
                <td><span class="bleugrascalibri10">Classification :&nbsp;</span></td>
                <td><select name="codeclassif" class="noircalibri10" id="codeclassif" >
                  <?php
                  while($row_rs_classif=mysql_fetch_assoc($rs_classif))
                  { ?>
                  <option value="<?php echo $row_rs_classif['codeclassif'] ?>" <?php echo ($row_rs_contrat['codeclassif']==$row_rs_classif['codeclassif']?'selected':'') ?>><?php echo $row_rs_classif['numclassif'].' '.$row_rs_classif['libclassif'] ?></option>
                  <?php
                  } ?>
                  </select>
                </td>
                <td>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <div id="div_doctorant" class="<?php echo ($row_rs_contrat['codetype']=='001' || $row_rs_contrat['codetype']=='002' || $row_rs_contrat['codetype']=='020')?'affiche':'cache' ?>">
                    <table border="0" cellpadding="2">
                      <tr>
                        <td><span class="bleugrascalibri10">Doctorant :&nbsp;</span></td>
                        <td><span class="bleugrascalibri10">
                          <select name="codedoctorant" class="noircalibri10" id="codedoctorant" 
                          onChange="var titre_these=affiche_sujet_ou_objet(this.value);
                                    if(titre_these!='')
                                    { document.getElementById('div_objet').className='cache';
                                      document.getElementById('div_titre_these').className='affiche';
                                      document.getElementById('div_titre_these').innerHTML='<span class='+String.fromCharCode(34)+'bleugrascalibri10'+String.fromCharCode(34)+'>Objet : '+'</span>'
                                                                                            +'<span class='+String.fromCharCode(34)+'noirgrascalibri10'+String.fromCharCode(34)+'>'+titre_these+'</span>';
                                    }
                                    else
                                    { document.getElementById('div_objet').className='affiche';
                                      document.getElementById('div_titre_these').className='cache';
                                    }
                                    ">
                            <?php
                            foreach($tab_doctorant_sujet as $codedoctorant=>$un_doctorant_sujet)
                            { ?>
                            <option value="<?php echo $codedoctorant ?>" <?php echo ($row_rs_contrat['codedoctorant']==$codedoctorant?'selected':'') ?>><?php echo ($codedoctorant==''?'':substr($un_doctorant_sujet['datedeb_sejour'],0,4)).' '.$un_doctorant_sujet['nomprenom'] ?> </option>
                            <?php
                            } ?>
                            </select>
                          </span>
                        </td>
                      </tr>
                    </table>
                  </div>
                </td>
                <td colspan="2">
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
      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td nowrap><span class="bleugrascalibri10">R&eacute;f&eacute;rence du contrat :&nbsp;</span>
                  <input name="ref_contrat" type="text" class="noircalibri10"  value="<?php echo htmlentities($row_rs_contrat['ref_contrat']) ?>" size="60" maxlength="100">
                  &nbsp;&nbsp;<span class="bleugrascalibri10">Acronyme :&nbsp;</span>
                  <?php if($estcontrateoptverrouille)
                  { ?><span class="noirgrascalibri10"><?php echo $row_rs_contrat['acronyme'] ?> </span>
                  <?php 
									}?>
                  <div class="<?php echo $estcontrateoptverrouille?'cache':'affiche' ?>">
										<input name="acronyme" type="text" class="noircalibri10"  value="<?php echo htmlentities($row_rs_contrat['acronyme']) ?>" size="30" maxlength="50">
                  </div>
                </td>
              </tr>
              <tr>
                <td nowrap>&nbsp;</td>
              </tr>
              <tr>
                <td nowrap><span class="bleugrascalibri10">R&eacute;f&eacute;rence programme long :&nbsp;</span>
                  <input name="ref_prog_long"  type="text" class="noircalibri10" value="<?php echo htmlentities($row_rs_contrat['ref_prog_long']) ?>" size="80" maxlength="200">
                </td>
              </tr>
              <tr>
                <td nowrap>&nbsp;</td>
              </tr>
              <tr>
                <td nowrap>
                <?php $class_cache_ou_affiche='affiche';
                      if($row_rs_contrat['codetype']=='001' || $row_rs_contrat['codetype']=='002' || $row_rs_contrat['codetype']=='020')
                      { if($row_rs_contrat['codedoctorant']!='')
                        { if(array_key_exists($row_rs_contrat['codedoctorant'],$tab_doctorant_sujet) && $tab_doctorant_sujet[$row_rs_contrat['codedoctorant']]['titre_these']!='')
                          {	$class_cache_ou_affiche='cache';
                          }
                        }
                      }?>
                  <div id='div_objet' class="<?php echo $class_cache_ou_affiche ?>">
                    <table border="0" cellspacing="2" cellpadding="0">
                      <tr>
                        <td valign="top">
                          <span class="bleugrascalibri10">Objet :&nbsp;</span><br>
                          <span class="bleucalibri9">(</span><span id="sujet#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_contrat['sujet']) ?></span><span class="bleucalibri9">/400 car. max.)</span></td>
                        <td><textarea name="sujet" cols="80" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","400","'sujet#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_contrat['sujet']; ?></textarea>
                        </td>
                      </tr>
                    </table>
                  </div>
                  <?php 
                  if($class_cache_ou_affiche=='cache')
                  { $class_cache_ou_affiche='affiche';
                  }
                  else
                  { $class_cache_ou_affiche='cache';
                  }?>
                  <div id='div_titre_these' class="<?php echo $class_cache_ou_affiche ?>">
                    <table border="0" cellspacing="2" cellpadding="0">
                      <tr>
                        <td>
                          <span class="bleugrascalibri10">Objet :&nbsp;</span><span class="noirgrascalibri10"><?php if(array_key_exists($row_rs_contrat['codedoctorant'],$tab_doctorant_sujet)) echo $tab_doctorant_sujet[$row_rs_contrat['codedoctorant']]['titre_these']?></span>
                        </td>
                      </tr>
                    </table>
                  </div>
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
      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                  <table border="0" cellspacing="2">
                    <tr>
                      <td><span class="bleugrascalibri10"><?php echo $GLOBALS['libcourt_theme_fr'] ?> :&nbsp;&nbsp;</span></td>
                      <td>
												<?php 
                        if(!$droitmodif_champ_budget)
                        { while($row_rs_theme=mysql_fetch_assoc($rs_theme))
													{ echo ($row_rs_contrat['codetheme']==$row_rs_theme['codetheme']?$row_rs_theme['libtheme']:'');
													}
													mysql_data_seek($rs_theme,0);
												}
                        ?>
                        <div class="<?php echo $class_cache_ou_affiche_champ_budget ?>">
                        <select name="codetheme" class="noircalibri10" id="codetheme" >
                        <?php
                        while($row_rs_theme=mysql_fetch_assoc($rs_theme))
                        { ?>
                          <option value="<?php echo $row_rs_theme['codetheme'] ?>" <?php echo ($row_rs_contrat['codetheme']==$row_rs_theme['codetheme']?'selected':'') ?>><?php echo $row_rs_theme['libtheme'] ?></option>
                          <?php
                        } ?>
                          </select>
                        </div>
                      </td>
                      <td>
                        <span class="bleugrascalibri10">Responsable scientifique :&nbsp;</span>
                      </td>
                      <td>
                        <select name="coderespscientifique" class="noircalibri10">
                          <?php
                        while($row_rs_respscientifique=mysql_fetch_assoc($rs_respscientifique))
                        { ?>
                          <option value="<?php echo $row_rs_respscientifique['coderespscientifique'] ?>" <?php echo ($row_rs_respscientifique['coderespscientifique']==$row_rs_contrat['coderespscientifique'])?'selected':''; ?>> <?php echo $row_rs_respscientifique['nomprenom']; ?> </option>
                          <?php 
                        }?>
                          </select>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellspacing="2" cellpadding="0" width="100%">
                    <tr>
                      <td>
                      	<span class="bleugrascalibri10">Partenaires :&nbsp;</span>
                        	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_ajoute_enleve_partenaire">
                            <div class="tooltipContent_cadre" id="info_ajoute_enleve_partenaire"> 
                            <span class="noircalibri10">
                              Pour associer un partenaire au contrat, s&eacute;lectionnez le dans la liste &rsquo;Ajouter un partenaire au contrat&rsquo; et enregistrez le formulaire : le partenaire choisi <br>
                              appara&icirc;tra dans une nouvelle liste d&eacute;roulante.<br>
                              Pour dissocier un partenaire du contrat qui apparait dans une liste d&eacute;roulante, s&eacute;lectionnez le partenaire 'vide' en haut de cette liste et enregistrez.<br>
                            </span> 
                            </div>
                            <script type="text/javascript">
                              var sprytooltip_ajoute_enleve_partenaire = new Spry.Widget.Tooltip("info_ajoute_enleve_partenaire", "#sprytrigger_info_ajoute_enleve_partenaire", {useEffect:"blind", offsetX:20, offsetY:20});
                            </script>
                        </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="0" cellspacing="2" cellpadding="0">
                          <?php 
                          reset($tab_contratpart);
                          $dernier_codepart=0;//utilise pour le codepart 
                          for($numligne=1;$numligne<=$nbpart;$numligne++)
                          //for($numligne=0;$numligne<=$nbpart/4;$numligne++)
                          {?>
                          <tr>
                            <?php 
                            $numcolonne=0;
                            while($numcolonne<=0)
                            { list($numordre,$codepart)=each($tab_contratpart);
                              ?>
                              <td>
                              <?php 
                              //if($numligne*4+$numcolonne<$nbpart)
                              {?>
                                <select name="codepart#<?php echo $numligne/* *4+$numcolonne */ ?>" class="noircalibri10">
                                <?php
                                foreach($tab_part as $un_codepart=>$un_part)
                                { ?>
                                  <option value="<?php echo $un_codepart ?>" <?php echo ($un_codepart==$codepart)?'selected':''; ?>><?php echo $un_part['libcourtpart'] ?></option>
                                <?php 
                                }?>
                                </select>
                                <?php
                                $dernier_codepart++; 
                              }?>
                              </td>
                              <?php 
                              $numcolonne++;
                            }?>
                          </tr>
                          <?php 
                          }?>
                          <tr>
                            <td><!-- colspan="3" -->
                            	Ajouter un partenaire existant au contrat :
															<select name="codepart#<?php echo $dernier_codepart+1 ?>" id="codepart#<?php echo $dernier_codepart+1 ?>">
																<?php
																foreach($tab_part as $un_codepart=>$un_part)
																{ ?><option value="<?php echo $un_codepart==""?"":$un_codepart;?>"><?php echo $un_part['libcourtpart'] ?></option>
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
                <td>
                <table border="0" cellspacing="2" cellpadding="0" width="100%">
                  <tr>
                    <td valign="top">
                      <img src="images/b_plus.png" alt="Nouveau" name="image_partenaire_a_creer_ou_modifier" align="texttop" id="image_partenaire_a_creer_ou_modifier" onClick="javascript:
                                    nouveau=document.getElementById('partenaire_a_creer_ou_modifier');
                                    if(nouveau.className=='affiche')
                                    { nouveau.className='cache';
                                      this.src='images/b_plus.png';
                                    }
                                    else 
                                    { nouveau.className='affiche';
                                      this.src='images/b_moins.png';
                                    }"
                                    >
                      <span class="orangecalibri10">&nbsp;Cr&eacute;er ou modifier un partenaire
                      </span>
											<?php $class_cache_ou_affiche='cache'?>
                      <div id="partenaire_a_creer_ou_modifier" class="<?php echo $class_cache_ou_affiche?>">
                      <table width="100%" border="0" cellpadding="0" class="table_cadre_arrondi">
                        <tr>
                          <td>
                            <table border="0" cellpadding="2">
                              <tr>
                                <td>
                                 <span class="orangecalibri10">
                                  S&eacute;lectionnez un partenaire pour le modifier (aucun pour cr&eacute;er) :&nbsp;</span>
                                  <select name="codepart_a_creer_ou_modifier" class="bleucalibri10" onChange="detailpart(this.value)">
                                    <?php
                                    foreach($tab_part as $un_codepart=>$un_part)
                                    { ?>
                                    <option value="<?php echo $un_codepart ?>"><?php echo $un_part['libcourtpart'] ?></option>
                                    <?php 
                                    }?>
                                  </select>
                                  <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_creer_modifier_partenaire">
                                  <div class="tooltipContent_cadre" id="info_creer_modifier_partenaire"> 
                                  <span class="noircalibri10">
                                    Pour modifier un partenaire (pour tous les contrats qui le mentionnent), s&eacute;lectionnez le dans la liste : vous pouvez modifier les d&eacute;tails qui s&rsquo;affichent<br>
                                    Pour cr&eacute;er, laissez vide cette liste de s&eacute;lection, renseignez les champs de d&eacute;tail.<br>
                                    Enregistrez : le partenaire apparait dans la liste des partenaires &agrave; ajouter.
                                  </span> 
                                  </div>
                                  <script type="text/javascript">
                                    var sprytooltip_creer_modifier_partenaire = new Spry.Widget.Tooltip("info_creer_modifier_partenaire", "#sprytrigger_info_creer_modifier_partenaire", {useEffect:"blind", offsetX:20, offsetY:20});
                                  </script>
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <table border="0" cellspacing="3" cellpadding="0">
                                    <tr>
                                      <td class="mauvecalibri10">nom abr&eacute;g&eacute; : </td>
                                      <td><input type="text" value="" name="libcourtpart" class="bleucalibri10" id="libcourtpart" size="30" maxlength="30"></td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">nom :</td>
                                      <td><input type="text" value="" name="liblongpart" class="bleucalibri10" id="liblongpart" size="100" maxlength="100"></td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">Statut :</td>
                                      <td>
                                        <select name="codestatutpart" class="bleucalibri10" id="codestatutpart">
                                          <?php
                                          while ($row_rs_statutpart = mysql_fetch_assoc($rs_statutpart)) 
                                          { ?>
                                          <option value="<?php echo $row_rs_statutpart['codestatutpart']?>"><?php echo $row_rs_statutpart['libstatutpart']?></option>
                                          <?php
                                          } 
                                          ?>
                                        </select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td valign="top" class="mauvecalibri10">Contact :</td>
                                      <td>
                                        <table border="0" cellpadding="2">
                                          <tr>
                                            <td nowrap>
                                              <select name="codecivcontactpart" class="bleucalibri10" id="codecivcontactpart">
                                                <?php
                                                while ($row_rs_civilite = mysql_fetch_assoc($rs_civilite)) 
                                                { ?>
                                                <option value="<?php echo $row_rs_civilite['codeciv']?>"><?php echo $row_rs_civilite['libciv']?></option>
                                                <?php
                                                } 
                                                ?>
                                              </select>
                                            </td>
                                            <td nowrap><span class="mauvecalibri10">Nom :</span></td>
                                            <td nowrap><input type="text" value="" name="nomcontactpart" class="bleucalibri10" id="nomcontactpart" size="20" maxlength="30"></td>
                                            <td nowrap><span class="mauvecalibri10">Pr&eacute;nom :</span></td>
                                            <td nowrap><input type="text" value="" name="prenomcontactpart" class="bleucalibri10" id="prenomcontactpart" size="20" maxlength="20"></td>
                                            <td nowrap><span class="mauvecalibri10">Fonction :</span></td>
                                            <td nowrap><input type="text" value="" name="fonctioncontactpart" class="bleucalibri10" id="fonctioncontactpart" size="20" maxlength="20"></td>
                                          </tr>
                                          <tr>
                                            <td nowrap>&nbsp;</td>
                                            <td nowrap><span class="mauvecalibri10">T&eacute;l.:</span></td>
                                            <td nowrap><input type="text" value="" name="telcontactpart" class="bleucalibri10" id="telcontactpart" size="15" maxlength="20"></td>
                                            <td nowrap><span class="mauvecalibri10">T&eacute;l. port.:</span></td>
                                            <td nowrap><input type="text" value="" name="telportcontactpart" class="bleucalibri10" id="telportcontactpart" size="15" maxlength="20"></td>
                                            <td nowrap>&nbsp;</td>
                                            <td nowrap>&nbsp;</td>
                                          </tr>
                                        </table>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">&nbsp;</td>
                                      <td><span class="mauvecalibri10">Mail :</span>&nbsp;
                                        <input type="text" value="" name="emailcontactpart" class="bleucalibri10" id="emailcontactpart" size="50" maxlength="100"></td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">&nbsp;</td>
                                      <td class="mauvecalibri10">Adresse <span class="bleucalibri9">(</span><span id="adressecontactpart#nbcar_js" class="bleucalibri9">0</span><span class="bleucalibri9">/200 car. max.)</span> :</td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">&nbsp;</td>
                                      <td><textarea name="adressecontactpart" cols="80" rows="3" wrap="PHYSICAL"  class="bleucalibri10" <?php echo affiche_longueur_js("this","200","'adressecontactpart#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>></textarea></td>
                                    </tr>
                                    <tr>
                                      <td class="mauvecalibri10">&nbsp;</td>
                                      <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                      <td valign="top" class="mauvecalibri10">Notes :<br>
                                        <span class="bleucalibri9">(</span><span id="notepart#nbcar_js" class="bleucalibri9">0</span><span class="bleucalibri9">/200 car. max.)</span>
                                      </td>
                                      <td><textarea name="notepart" cols="80" rows="3" wrap="PHYSICAL" class="bleucalibri10" <?php echo affiche_longueur_js("this","200","'notepart#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>></textarea>
                                      </td>
                                    </tr>
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
                </table>
              </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td><span class="bleugrascalibri10">Permanents impliqu&eacute;s en personne.mois </span><span class="bleugrascalibri10"> :&nbsp;&nbsp;</span></td>
                      <td><input name="permanent_mois" type="text" class="noircalibri10" value="<?php echo $row_rs_contrat['permanent_mois'] ?>" size="5" maxlength="5"
                           onChange="if(isNaN(this.value)) { alert('Nombre invalide'); }">
											</td>
                      <td><span class="bleugrascalibri10">&nbsp;&nbsp;Total personnels impliqu&eacute;s en personne.mois :&nbsp;&nbsp;</span></td>
                      <td><input name="personnel_mois" type="text" class="noircalibri10" value="<?php echo $row_rs_contrat['personnel_mois'] ?>" size="5" maxlength="5"
                           onChange="if(isNaN(this.value)) { alert('Nombre invalide'); }">
											</td>
                    </tr>
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
      <table width="100%" border="0" cellpadding="0" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="3">
              <tr>
                <td>
                  <table border="0" cellspacing="3">
                    <tr>
                      <td>
                        <span class="bleugrascalibri10">HT/TTC :&nbsp;</span>
                        <select name="ht_ttc" class="noircalibri10">
                          <option value="HT" <?php echo $row_rs_contrat['ht_ttc']=='HT'?'selected':'' ?>>HT</option>
                          <option value="TTC" <?php echo $row_rs_contrat['ht_ttc']=='TTC'?'selected':'' ?>>TTC</option>
                        </select>
                        </td>
                        <td>
                        <span class="bleugrascalibri10">Montant :&nbsp;</span>
                        <input name="montant_ht"  type="text" style="text-align:right" class="noircalibri10" value="<?php echo $row_rs_contrat['montant_ht'] ?>" size="12" maxlength="12"
                        onChange="javascript: this.value=formate_nombre('<?php echo $form_contrat ?>',this.value,'.',2,'');calculcumul();">
                        </td>
                        <td>
                        <?php 
                        $cumulmontant=0; 
                        for($numordre=1;$numordre<=$nbcolmontantdetail;$numordre++)
                        { $cumulmontant+=isset($tab_contratmontantannee[$numordre]['montant'])?$tab_contratmontantannee[$numordre]['montant']:0 ;
                        }
                        ?>
                      <div id="cumulmontant" class="<?php if($cumulmontant>$row_rs_contrat['montant_ht']) {?>rougecalibri10<?php } else {?>bleucalibri10<?php }?>">Cumul saisi : <?php echo number_format ( $cumulmontant , 2 , '.', ' ' )?>
                      </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                    <?php 
                    //$cumulmontant=0; 
                    for($numordre=1;$numordre<=$nbcolmontantdetail;$numordre++)
                    { //$cumulmontant+=isset($tab_contratmontantannee[$numordre]['montant'])?$tab_contratmontantannee[$numordre]['montant']:0 ;
                      $annee=substr($row_rs_contrat['datedeb_contrat'],0,4)+$numordre-1;
											if($annee>substr($row_rs_contrat['datefin_contrat'],0,4))
											{ $class_cache_ou_affiche='cache';
											}
											else
											{ $class_cache_ou_affiche='affiche';
											}
											?>
                        <td align="center">
                      	 <div id="divcolannee#<?php echo $numordre ?>" class="<?php echo $class_cache_ou_affiche ?>">
                          <table border="1" cellpadding="2" class="table_cellule_encadre_gris">
                            <tr>
                              <td align="center">
                                <input type="text" name="annee#<?php echo $numordre ?>" value="<?php echo ($nbannee-$numordre>=0)?$annee:''; ?>" id="annee#<?php echo $numordre ?>"  style="text-align:center" class="noircalibri10" size="4" maxlength="4" readonly>
                                    <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_montant_annee<?php echo $numordre ?>">
                                    <div class="tooltipContent_cadre" id="info_montant_annee<?php echo $numordre ?>"> 
                                    <span class="noircalibri10">
                                      Ce cadre contient, pour l&rsquo;ann&eacute;e donn&eacute;e :<br>
                                      - le montant total des montants &agrave; percevoir<br>
                                      - une ligne par montant &agrave; percevoir &agrave; une date donn&eacute;e.<br>
                                        Si cette date est d&eacute;pass&eacute;e, un <img src="images/b_attention.png" alt="Retard" width="12" height="12"> est affich&eacute; en vis-&agrave;-vis.<br>
                                        Quand le montant est per&ccedil;u, il faut l&rsquo;indiquer en cochant la case en vis-&agrave;-vis : le <img src="images/b_attention.png" alt="Retard" width="12" height="12"> disparaitra &agrave; l&rsquo;enregistrement.<br>
                                        Un enregistrement est n&eacute;cessaire afin de corriger l'affichage des <img src="images/b_attention.png" alt="Retard" width="12" height="12"> des montants d&eacute;taill&eacute;s. 
                                    </span> 
                                    </div>
                                    <script type="text/javascript">
                                      var sprytooltip_montant_annee<?php echo $numordre ?> = new Spry.Widget.Tooltip("info_montant_annee<?php echo $numordre ?>", "#sprytrigger_info_montant_annee<?php echo $numordre ?>", {useEffect:"blind", offsetX:20, offsetY:20});
                                    </script>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <table cellspacing="3" border="0">
                                  <tr>
                                    <td nowrap>
                                      <input type="text" value="<?php echo isset($tab_contratmontantannee[$numordre]['montant'])?$tab_contratmontantannee[$numordre]['montant']:"" ?>" name="montant#<?php echo $numordre ?>" id="montant#<?php echo $numordre ?>" style="text-align:right" class="noircalibri10" size="8" maxlength="12"
                                    onChange="javascript: this.value=formate_nombre('<?php echo $form_contrat ?>',this.value,'.',2,'');calculcumul();calculcumuldetail('montantdetail#<?php echo $numordre ?>##1');"><?php // appel a calculcumuldetail avec montantdetail#$numordre##1 : 1 est le 1er montantdetail ?>
                                    <?php 
                                    $rs_contratmontantdetail=mysql_query( "SELECT sum(montant) as summontantdetail".
                                                                          " from contratmontantdetail where codecontrat=".GetSQLValueString($codecontrat, "text").
                                                                          " and substring(datemontant,1,4)=".GetSQLValueString($annee, "text")) or die(mysql_error());
                                    $cumulmontantannee=0;
                                    ?>
                                    <?php 
                                    if($row_rs_contratmontantdetail=mysql_fetch_assoc($rs_contratmontantdetail))
                                    { $class="bleucalibri10"; 
                                      if(isset($tab_contratmontantannee[$numordre]['montant']) && $row_rs_contratmontantdetail['summontantdetail']>$tab_contratmontantannee[$numordre]['montant'])
                                      { $class="rougecalibri10";
                                      }
                                      $cumulmontantannee=$row_rs_contratmontantdetail['summontantdetail'];
                                    }?>
                                    </td>
                                    <td colspan="3" nowrap>
                                    <div id="cumulmontantannee#<?php echo $numordre ?>" class="<?php echo $class ?>">Cumul saisi :&nbsp;<?php echo number_format ( $cumulmontantannee , 2 , '.', ' ' ) ?>
                                    </div>
                                    </td>
                                  </tr>
                                  <?php 
                                  // Liste des montants detailles previonnels ou réels du contrat pour l'annee
                                  $rs_contratmontantdetail=mysql_query("SELECT nummontantdetail,datemontant,montant,reel".
																																				" from contratmontantdetail where codecontrat=".GetSQLValueString($codecontrat, "text").
																																				" and substring(datemontant,1,4)=".GetSQLValueString($annee, "text").
																																				" order by datemontant") or die(mysql_error());
                                  $cumulmontantdetail=0;
                                  for($nummontant=1;$nummontant<=$nblignemontantdetail;$nummontant++)
                                  { $datemontant=''; $montant=''; $reel='non';
                                    if($row_rs_contratmontantdetail=mysql_fetch_assoc($rs_contratmontantdetail))
                                    { $datemontant=$row_rs_contratmontantdetail['datemontant'];
                                      $montant=$row_rs_contratmontantdetail['montant'];
                                      $reel=$row_rs_contratmontantdetail['reel'];
                                      $cumulmontantdetail+=$montant;
                                    }?>
                                    <tr>
                                      <td>
                                      <input type="text" value="<?php echo $montant ?>" name="montantdetail#<?php echo $numordre ?>##<?php echo $nummontant ?>" id="montantdetail#<?php echo $numordre ?>##<?php echo $nummontant ?>" style="text-align:right" class="noircalibri10" size="8" maxlength="12"
                                      	onChange="javascript: this.value=formate_nombre('<?php echo $form_contrat ?>',this.value,'.',2,'');calculcumuldetail(this.name);
                                      ">
                                      </td>
                                      <?php
                                      $jj=$datemontant==''?'':substr($datemontant,8,2); 
                                      $mm=$datemontant==''?'':substr($datemontant,5,2); 
                                      $aaaa=$datemontant==''?'':substr($datemontant,0,4);
                                      $attention=false;
                                      if($aaaa.$mm.$jj<$aujourdhui && $datemontant!='' && $reel=='non')
                                      {	$attention=true;
                                      }
                                      ?>
                                      <td nowrap>
                                        <input type="text" value="<?php echo $jj ?>"   name="datemontant_jj#<?php echo $numordre ?>##<?php echo $nummontant ?>" style="text-align:right" class="noircalibri10" id="datemontant_jj#<?php echo $numordre ?>##<?php echo $nummontant ?>" size="1" maxlength="2"
                                          onChange="if(!est_champ_jour(this)){ alert('Jour invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}">
                                        <input type="text" value="<?php echo $mm ?>"   name="datemontant_mm#<?php echo $numordre ?>##<?php echo $nummontant ?>" style="text-align:right" class="noircalibri10" id="datemontant_mm#<?php echo $numordre ?>##<?php echo $nummontant ?>" size="1" maxlength="2"
                                          onChange="if(!est_champ_mois(this)){ alert('Mois invalide'); } else {if(this.value!='' && this.value.length==1) {this.value='0'+this.value;}}
                                          ">
                                        <input type="text" value="<?php echo $aaaa ?>" name="datemontant_aaaa#<?php echo $numordre ?>##<?php echo $nummontant ?>" style="text-align:right" class="noircalibri10" id="datemontant_aaaa#<?php echo $numordre ?>##<?php echo $nummontant ?>" size="2" maxlength="4"
                                          onChange="if(!est_champ_annee(this)){ alert('Année invalide'); } else {if(this.value!='' && this.value.length==2) {this.value='20'+this.value;}}
                                                    ">
                                      </td>
                                      <td nowrap>
                                        <input type="checkbox" <?php if($reel=='oui'){?>checked<?php }?> name="reel#<?php echo $numordre ?>##<?php echo $nummontant ?>">
                                      </td>
                                      <td>
                                    <?php
                                    if($attention)
                                    {?>
                                      <img src="images/b_attention.png" alt="Retard" width="12" height="12">
                                      <?php 
                                    }?>
                                    </td>
                                    </tr>
                                    <?php 
                                  }?>
                                </table>
                              </td>
                            </tr>
                          </table>
                         </div>
                        </td>
                    <?php 
                    }?>
                  </tr>
                </table>
              </td>
            </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellspacing="2" cellpadding="0">
                    <tr>
                      <td valign="top"><span class="bleugrascalibri10">Note :&nbsp;</span><br>
                        <span class="bleucalibri9">(</span><span id="note#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_contrat['note']) ?></span><span class="bleucalibri9">/1000 car. max.)</span></td>
                      <td><textarea name="note" cols="80" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","1000","'note#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_contrat['note']; ?></textarea></td>
											<td><img src="images/espaceur.gif" width="20">
                      </td>
											<td valign="top">
                      	<table>
                        	<tr>
                          	<td><span class="bleugrascalibri10">Pi&egrave;ces jointes :</span>
                            </td>
                          </tr>
                        	<tr>
                          	<td>
															<?php
                              echo ligne_txt_upload_pj_contrat($codecontrat,'contrat','Contrat',$form_contrat,true);
                              ?>
                            </td>
                          </tr>
                        	<tr>
                          	<td>
															<?php
                              echo ligne_txt_upload_pj_contrat($codecontrat,'autre','Autre',$form_contrat,true);
                              ?>
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
                <td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>
</body>
</html>
    <?php

if(isset($rs_classif))mysql_free_result($rs_classif);
if(isset($rs_theme))mysql_free_result($rs_theme);
if(isset($rs_orgfinanceur))mysql_free_result($rs_orgfinanceur);
if(isset($rs_projet))mysql_free_result($rs_projet);
if(isset($rs_orggest))mysql_free_result($rs_orggest);
if(isset($rs_type))mysql_free_result($rs_type);
if(isset($rs_contrat))mysql_free_result($rs_contrat);
if(isset($rs_secteur))mysql_free_result($rs_secteur);
if(isset($rs_nivconfident))mysql_free_result($rs_nivconfident);
if(isset($rs_typeconvention))mysql_free_result($rs_typeconvention);
if(isset($rs_doctorant_sujet))mysql_free_result($rs_doctorant_sujet);
if(isset($rs_respscientifique))mysql_free_result($rs_respscientifique);
if(isset($rs_statutpart))mysql_free_result($rs_statutpart);
if(isset($rs_civilite))mysql_free_result($rs_civilite);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
