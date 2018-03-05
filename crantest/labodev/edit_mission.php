<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ /*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}  */
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche="";
$form_mission = "form_mission";
$nbminetape=3;
$tab_missionetape=array();
$tab_missionetape_suffixe=array();// champs d'etapes avec suffixe _jj,_mm,...
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$nbetape=isset($_GET['nbetape'])?$_GET['nbetape']:(isset($_POST['nbetape'])?$_POST['nbetape']:$nbminetape);
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");

//PG 20151117
// protection contre une erreur qui modifierait l'enreg. '' et se repercuterait sur toutes les commandes non liees a une mission !!
if($action!='creer' && $codemission=='')
{ $erreur.="Tentative de modification de mission sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de mission sans n&deg; '.$msg);
}
//PG 20151117

$tab_champs_ouinon=array('estfrance','estetranger','estsansfrais','avecmetro','avectaxi','avecparking','avecautre','avecrepas','avechotel',
													'sanspriseenchargecongres','avecpriseenchargetotalecongres','avecpriseenchargepartiellecongres',
													'avectraincongres','avecavioncongres','avecvehiculeperscongres',
													'avechebergementcongres','avecrepascongres','avecautrecongres','avecpriseenchargeautre_ulcongres',
													'avecpriseenchargetotale','avecpriseenchargepartielle','avecpriseenchargeautre_ul',
													'avectrain','avecavion','avecvehiculeservice','avecvehiculepersonnel','avecvehiculelocation');
$tab_champs_ouinon_defaut=array('estfrance'=>'non','estetranger'=>'non','estsansfrais'=>'non','avecmetro'=>'non','avectaxi'=>'non',
																'avecparking'=>'non','avecautre'=>'non','avecrepas'=>'non','avechotel'=>'non',
																'sanspriseenchargecongres'=>'non','avecpriseenchargetotalecongres'=>'non','avecpriseenchargepartiellecongres'=>'non',
																'avectraincongres'=>'non','avecavioncongres'=>'non','avecvehiculeperscongres'=>'non',
																'avechebergementcongres'=>'non','avecrepascongres'=>'non','avecautrecongres'=>'non','avecpriseenchargeautre_ulcongres'=>'non',
																'avecpriseenchargetotale'=>'non','avecpriseenchargepartielle'=>'non','avecpriseenchargeautre_ul'=>'non',
																'avectrain'=>'non','avecavion'=>'non','avecvehiculeservice'=>'non','avecvehiculepersonnel'=>'non','avecvehiculelocation'=>'non'
																);
														
$tab_champs_date=array(	'date_naiss' =>  array("lib" => "Date de Naissance","jj" => "","mm" => "","aaaa" => ""),
												'dateabonneairfranceexpire' =>  array("lib" => "Date d'expiration abonnement Air France","jj" => "","mm" => "","aaaa" => ""),
												'datedeb_congres' =>  array("lib" => "Date debut congres","jj" => "","mm" => "","aaaa" => ""),												
												'datefin_congres' =>  array("lib" => "Date fin congres","jj" => "","mm" => "","aaaa" => ""),									
												'datedepartcongres' =>  array("lib" => "Date depart congres","jj" => "","mm" => "","aaaa" => ""),											
												'datearriveecongres' =>  array("lib" => "Date arrivee congres","jj" => "","mm" => "","aaaa" => ""),												
												'dateabonnetrainexpire' =>  array("lib" => "Date expiration abonnement train","jj" => "","mm" => "","aaaa" => ""),												
												'dateabonneavionexpire' =>  array("lib" => "Date expiration abonnement avion","jj" => "","mm" => "","aaaa" => "")												
											);
$tab_champs_numerique=array('nbperstransporte' =>  array('lib' => 'Nombre de personnes transport&eacute;es','max_length'=>2),
														'nbrepascharge' =>  array('lib' => 'Nombre de repas','max_length'=>2),
														'nbnuitshotelcharge' =>  array('lib' => 'Nombre de nuit&eacute;es','max_length'=>2),
														'montantestimemission' =>  array('lib' => 'Montant estim&eacute;','string_format'=>'%01.2f','max_length'=>12),
														'forfait' =>  array('lib' => 'Forfait','string_format'=>'%01.2f','max_length'=>12)
														);

$tab_champs_heure_mn=array();
for($i=1;$i<=$nbetape;$i++)
{ $numetape=str_pad($i,2,'0',STR_PAD_LEFT);
	$tab_champs_date['departdate#'.$numetape]=array("lib" => "Date depart ligne ".$numetape,"jj" => "","mm" => "","aaaa" => "");
	$tab_champs_date['arriveedate#'.$numetape]=array("lib" => "Date arrivee ligne ".$numetape,"jj" => "","mm" => "","aaaa" => "");
	$tab_champs_heure_mn['departheure#'.$numetape]=array("lib" => "Heure depart ligne ".$numetape,"hh" => "","mn" => "");
	$tab_champs_heure_mn['arriveeheure#'.$numetape]=array("lib" => "Heure arrivee ligne ".$numetape,"hh" => "","mn" => "");
}
$tab_champs_heure_mn['heuredepartcongres']=array("lib" => "Heure depart congres".$numetape,"hh" => "","mn" => "");
$tab_champs_heure_mn['heurearriveecongres']=array("lib" => "Heure arrivee congres".$numetape,"hh" => "","mn" => "");

$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

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

if($submit=='submit_supprimer_une_pj')
{ $supprimer_une_pj=true;
	$codetypepj=$submit_val;
}
// les champs nom,prenom,date_naiss,email concernant l'agent ont été mis disabled
if(isset($_POST['codeagent']) && $_POST['codeagent']!='')
{ $query_rs_agent="SELECT codeciv,nom,prenom,date_naiss,email".
									" FROM individu".
									" where individu.codeindividu=".GetSQLValueString($_POST['codeagent'], "text");
	$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
	if($row_rs_agent=mysql_fetch_assoc($rs_agent))
	{ $_POST['codeciv']=$row_rs_agent['codeciv'];
		$_POST['nom']=$row_rs_agent['nom'];
		$_POST['prenom']=$row_rs_agent['prenom'];
		$_POST['date_naiss_jj']=substr($row_rs_agent['date_naiss'],8,2);
		$_POST['date_naiss_mm']=substr($row_rs_agent['date_naiss'],5,2);
		$_POST['date_naiss_aaaa']=substr($row_rs_agent['date_naiss'],0,4);
		$_POST['email']=$row_rs_agent['email'];
	}
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_mission)) 
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_heure_mn' =>  $tab_champs_heure_mn,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_mission($_POST,$tab_controle_et_format);
	
	//on attend le retour de controle_form_mission pour avoir des dates correctement formatées dans le POST
	foreach($_POST as $postkey=>$postval)
	{ $posdiese=strpos($postkey,'#');
		if($posdiese!==false)
		{ $numetape=substr($postkey,$posdiese+1);
			$champ=substr($postkey,0,$posdiese);
			if(strpos('codemission#,numetape#,departlieu#,departdate_jj#,departdate_mm#,departdate_aaaa#,departheure_hh#,departheure_mn,arriveelieu#,arriveedate_jj#,arriveedate_mm#,arriveedate_aaaa#,arriveeheure_hh#,arriveeheure_mn#,moyentransport#',$champ)!==false)
			{ // enleve les suffixes _jj,_mm,_aaaa,hh,mn
				$posunderscore=strpos($champ,'_');
				if($posunderscore!==false)
				{ $suffixe=substr($champ,$posunderscore+1);//jj, mm, aaaa
					$champ=substr($champ,0,$posunderscore);
					$tab_missionetape_suffixe[$numetape][$champ][$suffixe]=$postval;
				}
				else
				{ $tab_missionetape[$numetape][$champ]=$postval;
				}
			}
		}
	}
	//reconstitue les suffixes jj, mm, aaaa en aaaa/mm/jj et hh, mn en hhHmn
	foreach($tab_missionetape_suffixe as $numetape=>$une_missionetape_suffixe) 
	{ $tab_missionetape[$numetape]['arriveedate']=jjmmaaaa2date($une_missionetape_suffixe['arriveedate']['jj'],$une_missionetape_suffixe['arriveedate']['mm'],$une_missionetape_suffixe['arriveedate']['aaaa']);
		$tab_missionetape[$numetape]['departdate']=jjmmaaaa2date($une_missionetape_suffixe['departdate']['jj'],$une_missionetape_suffixe['departdate']['mm'],$une_missionetape_suffixe['departdate']['aaaa']);
		$tab_missionetape[$numetape]['arriveeheure']=hhmn2heure($une_missionetape_suffixe['arriveeheure']['hh'],$une_missionetape_suffixe['arriveeheure']['mn']);
		$tab_missionetape[$numetape]['departheure']=hhmn2heure($une_missionetape_suffixe['departheure']['hh'],$une_missionetape_suffixe['departheure']['mn']);
	}
	// les champs departlieu,departdate,departheure,arriveelieu,arriveedate,arriveeheure doivent etre tous renseignes ou pas du tout
	$first=true;
	foreach($tab_missionetape as $numetape=>$une_missionetape)
	{ $nb_champ_vide=0;
		$tab_missionetape[$numetape]['vide']=false;
		foreach($une_missionetape as $champ=>$val)
		{ if(strpos('departlieu,departdate,arriveelieu,arriveedate',$champ)!==false)
			{ if($val=='')
				{ $nb_champ_vide++;
				}
			}
		}
		if($nb_champ_vide==4)//etape vide
		{ $tab_missionetape[$numetape]['vide']=true;
		}
	}
//$erreur='erreur forcée';
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		if($action=="creer")//creation
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='mission'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codemission=$row_seq_number['currentnumber'];
			$codemission=str_pad((string)((int)$codemission+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codemission, "text")." where nomtable='mission'") or  die(mysql_error());
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			
			// insertion d'un enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM mission');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codemission')
				{ $liste_val.=GetSQLValueString($codemission, "text");
				}
				else if($row_rs_fields['Field']=='codecreateur')
				{ $liste_val.=GetSQLValueString($codeuser, "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into mission (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			$action="modifier";
		}//fin if creation
		$rs_fields = mysql_query('SHOW COLUMNS FROM mission');
		$updateSQL = "UPDATE mission SET ";
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ // on update que les champs envoyés dans le POST
			$Field=$row_rs_fields['Field'];
			if(!in_array($Field,array("codemission")))
			{	if(isset($_POST[$Field]) ||
					(isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'] )) || 
					(isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
				{ //les donnees codemission codecreateur date_creation ne sont plus jamais modifiees : uniquement en creation en insert
					if(in_array($Field, $tab_champs_ouinon)===false)
					{ $updateSQL.=$Field."=";
						if(array_key_exists($Field, $tab_champs_date)!==false)
						{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
						}
						else if(array_key_exists($Field, $tab_champs_heure_mn)!==false)
						{ $updateSQL.=GetSQLValueString(hhmn2heure($_POST[$Field.'_hh'],$_POST[$Field.'_mn']), "text");
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
				else//non envoye dans le POST
				{ if(in_array($Field, $tab_champs_ouinon)!==false)
					{ $updateSQL.=$Field."='non'".",";
					}
					else if($Field=='codemodifieur')
					{ $updateSQL.=$Field."=".GetSQLValueString($codeuser, "text").",";
					}
					else if($Field=='date_modif')
					{ $updateSQL.=$Field."=".GetSQLValueString(date("Y/m/d"), "text").",";
					}
				}
			}
		}
		$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
		$updateSQL.=" WHERE codemission=".GetSQLValueString($codemission, "text");
		mysql_query($updateSQL) or die(mysql_error());
	
		// ----------------------------- etapes
		// suppression des etapes
		mysql_query("delete from missionetape where codemission=".GetSQLValueString($codemission, "text")) or die(mysql_error());
		// insertion des etapes
		$nbetape=0;
		foreach($tab_missionetape as $numetape=>$une_missionetape)//renumeration des etapes non vides de 01 a nb etapes non vides
		{ if(!$tab_missionetape[$numetape]['vide'])
			{ $nbetape++;
				$numetape=str_pad($nbetape,2,'0',STR_PAD_LEFT);
				$updateSQL ="INSERT into missionetape (codemission,numetape,departlieu,departdate,departheure,arriveelieu,arriveedate,arriveeheure,moyentransport) ".
										" values (".GetSQLValueString($codemission, "text").",".GetSQLValueString($numetape, "text").",".
																GetSQLValueString($une_missionetape['departlieu'], "text").",".GetSQLValueString($une_missionetape['departdate'], "text").",".GetSQLValueString($une_missionetape['departheure'], "text").",".
																GetSQLValueString($une_missionetape['arriveelieu'], "text").",".GetSQLValueString($une_missionetape['arriveedate'], "text").",".GetSQLValueString($une_missionetape['arriveeheure'], "text").",".
																GetSQLValueString($une_missionetape['moyentransport'], "text").
															")";
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
				
		// Maj individu si agent labo. et referent des commandes associees
		// c'est le champ telport de individu qui prend la valeur de tel
		if(isset($_POST['codeagent']) && $_POST['codeagent']!='')
		{ $query ="update individu set telport=".GetSQLValueString($_POST["telport"],"text").",".
							($_POST["adresse_pers"]==""?"":" adresse_pers=".GetSQLValueString($_POST["adresse_pers"],"text").",").
							($_POST["codepostal_pers"]==""?"":" codepostal_pers=".GetSQLValueString($_POST["codepostal_pers"],"text").",").
							($_POST["ville_pers"]==""?"":" ville_pers=".GetSQLValueString($_POST["ville_pers"],"text").",").
							($_POST["codepays_pers"]==""?"":" codepays_pers=".GetSQLValueString($_POST["codepays_pers"],"text").",").
							" adresse_admin=".GetSQLValueString($_POST["adresse_admin"],"text").",".
							" composanteenseignement=".GetSQLValueString($_POST["composanteenseignement"],"text").",".
							" numcarteabonnetrain=".GetSQLValueString($_POST["numcarteabonnetrain"],"text").",".
							" numcartefidelitetrain=".GetSQLValueString($_POST["numcartefidelitetrain"],"text").",".
							" numcarteabonneavion=".GetSQLValueString($_POST["numcarteabonneavion"],"text").",".
							" numcartefideliteavion=".GetSQLValueString($_POST["numcartefideliteavion"],"text").",".
							" dateabonnetrainexpire=".GetSQLValueString(jjmmaaaa2date($_POST['dateabonnetrainexpire_jj'],$_POST['dateabonnetrainexpire_mm'],$_POST['dateabonnetrainexpire_aaaa']), "text").",".
							" dateabonneavionexpire=".GetSQLValueString(jjmmaaaa2date($_POST['dateabonneavionexpire_jj'],$_POST['dateabonneavionexpire_mm'],$_POST['dateabonneavionexpire_aaaa']), "text").",".
							" zonegeoabonnetrain=".GetSQLValueString($_POST["zonegeoabonnetrain"],"text").",".
							" zonegeoabonneavion=".GetSQLValueString($_POST["zonegeoabonneavion"],"text").",".
							" numimmatriculation=".GetSQLValueString($_POST["numimmatriculation"],"text").
							" where codeindividu=".GetSQLValueString($_POST["codeagent"],"text");
			mysql_query($query) or die(mysql_error());
		}
		if(isset($_POST['codeagent']))
		{ if($_POST['codeagent']!='')//Change codereferent des commandes associees avec codeagent
			{	$query="update commande set codereferent=".GetSQLValueString($_POST["codeagent"],"text")."where codemission=".GetSQLValueString($codemission, "text");
			}
			else //Change codereferent des commandes associees avec codesecrsite
			{	$query="update commande set codereferent=".GetSQLValueString($_POST["codesecrsite"],"text")."where codemission=".GetSQLValueString($codemission, "text");
				mysql_query($query) or die(mysql_error());
			}
			mysql_query($query) or die(mysql_error());
		}
		// creation d'une commande
		if($submit=='submit_creer_commande_congres')
		{ http_redirect('edit_commande.php?codecommande=&action=creer&codemission='.$codemission.'&estcommandedecongres=oui&cmd_ancre='.$cmd_ancre);
		}
	} 

}

// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//En creation, l'ancre est definie a ce moment
$cmd_ancre="M".$codemission;
//Informations du mission (un enreg. vide dans mission pour "creer")
$query_mission =	"SELECT mission.*".
									" FROM mission".
									" WHERE codemission=".GetSQLValueString($codemission,"text");
$rs_mission=mysql_query($query_mission) or die(mysql_error());
$row_rs_mission=mysql_fetch_assoc($rs_mission);
/*// type mission
$rs=mysql_query("select libcourt as libtypemission from cmd_typemission where codelibtypemission=".GetSQLValueString($codelibtypemission,"text")) or die(mysql_error());
$row_rs=mysql_fetch_assoc($rs);
$libtypemission=$row_rs['libtypemission'];
*/
if($action=='creer')
{ $row_rs_mission['createurnom']=$tab_infouser['nom'];
	$row_rs_mission['createurprenom']=$tab_infouser['prenom'];
	$row_rs_mission['date_creation']=date("Y/m/d");
	$row_rs_mission['modifieurnom']=$tab_infouser['nom'];
	$row_rs_mission['modifieurprenom']=$tab_infouser['prenom'];	
	$row_rs_mission['codesecrsite']=$codeuser;
	$row_rs_mission['date_modif']=date("Y/m/d");
}
else if($action=='modifier')
{ $query_rs_individu ="SELECT  createur.nom as createurnom,createur.prenom as createurprenom, modifieur.nom as modifieurnom, modifieur.prenom as modifieurprenom ".
											" FROM mission, individu as createur, individu as modifieur ".
											" WHERE createur.codeindividu=mission.codecreateur and modifieur.codeindividu=mission.codemodifieur".
											" and mission.codemission = ".GetSQLValueString($codemission, "text");
	$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
	$row_rs_mission=array_merge($row_rs_mission,mysql_fetch_assoc($rs_individu));
}

if($erreur=='')
{	// Liste des etapes
	$tab_missionetape=array();//raz du tableau ou creation 
	$rs=mysql_query("SELECT * from missionetape".
									" where missionetape.codemission=".GetSQLValueString($codemission, "text").
									" order by departdate,departheure") or die(mysql_error());
	$nbetape=mysql_num_rows($rs);
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_missionetape[$row_rs['numetape']]=$row_rs;
	}
	//complete  de $nbetapes+1 (1 si action=creer) a max($nbminetape,$nbetape+1) les etapes a afficher
	$tab_fields=array();
	$rs_fields = mysql_query('SHOW COLUMNS FROM missionetape');
	while($row_rs_fields = mysql_fetch_assoc($rs_fields))
	{ $tab_fields[]=$row_rs_fields['Field'];
	}
	for($i=$nbetape+1;$i<=max($nbminetape,$nbetape+1);$i++)//max($nbminetape,$nbetape+1)=ajout d'une ligne si necessaire 
	{	$numetape=str_pad($i,2,'0',STR_PAD_LEFT);
		foreach($tab_fields as $Field) 
		{ if($Field=='codemission')
			{ $tab_missionetape[$numetape][$Field]=$codemission;
			}
			else if($Field=='numetape')
			{ $tab_missionetape[$numetape][$Field]=$numetape;
			}
			else
			{ $tab_missionetape[$numetape][$Field]='';
			}
		}
	}
	$nbetape=max($nbminetape,$nbetape+1);
}
else//valeurs du POST a la place de certaines données de mission qui n'ont pas été mises a jour. $tab_missionetape a deja ete renseigne avant controle d'erreur
{ $rs_fields = mysql_query('SHOW COLUMNS FROM mission');
	while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
	{ $Field=$row_rs_fields['Field'];
		if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa']))
			|| (isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
		{ if(in_array($Field, $tab_champs_ouinon)===false)
			{ if(array_key_exists($Field,$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
				{ $row_rs_mission[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
				}
				else if(array_key_exists($Field,$tab_champs_heure_mn)!==false && isset($_POST[$Field.'_hh']))
				{ $row_rs_mission[$Field]=$_POST[$Field.'_hh'].'H'.$_POST[$Field.'_mn'];
				}
				else
				{ $row_rs_mission[$Field]=$_POST[$Field];
				}
			}
			else
			{ $row_rs_mission[$Field]='oui';
			}
		}
		else//non envoye dans le POST
		{	if(in_array($Field, $tab_champs_ouinon)!==false)
			{ $row_rs_mission[$Field]=$tab_champs_ouinon_defaut[$Field];
			}
		}
	}
}

$premierdepartdate=$tab_missionetape['01']['departdate'];
// on verifie que l'agent sera dans la liste selon les criteres de sejour et d'emploi : pour une raison ou une autre, il peut ne pas etre selectionne (mission anticipee d'une personne qui sera consideree partie d'ici la par ex.) 
$estagentpresent=true;
if($row_rs_mission['codeagent'])
{	$query_rs_agent="SELECT * FROM individu,individusejour,individuemploi,corps,cat,miss_catmissionnaire".
								" where individu.codeindividu=individusejour.codeindividu and individu.codeindividu=individuemploi.codeindividu".
								" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat". 
								" and corps.codecatmissionnaire=miss_catmissionnaire.codecatmissionnaire". //and corps.codecatmissionnaire<>''
								" and  individu.codeindividu=".GetSQLValueString($row_rs_mission['codeagent'], "text").
													 " and ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$premierdepartdate."'","'".$premierdepartdate."'"). 
													 " and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$premierdepartdate."'","'".$premierdepartdate."'");
	$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
	if(mysql_num_rows($rs_agent)==0)
	{ $estagentpresent=false;
	}
}
// liste des agents y compris l'agent missionnaire meme parti ou sans emploi
$query_rs_agent="SELECT distinct individu.codeindividu as codeagent,codeciv,nom,prenom,date_naiss,adresse_pers, codepostal_pers, ville_pers, codepays_pers,adresse_admin, telport,email,".
								" numcarteabonnetrain, numcarteabonneavion,dateabonnetrainexpire, dateabonneavionexpire,numcartefidelitetrain, numcartefideliteavion,".
								" zonegeoabonnetrain, zonegeoabonneavion, numimmatriculation,".
								"	datedeb_sejour,codeetab,codemodefinancement, miss_catmissionnaire.codecatmissionnaire,miss_catmissionnaire.libcourt as libcatmissionnaire,".
								" liblongcorps_fr as libcorps,composanteenseignement,".
								" if(cat.codelibcat='DOCTORANT' or cat.codelibcat='STAGIAIRE','oui','non') as estetudiant,".
								" if(cat.codelibcat='POSTDOC','oui','non') as estpostdoc,".
								" if(cat.codelibcat='EXTERIEUR',intituleposte,'') as motif".
								" FROM individu,individusejour,individuemploi,corps,cat,miss_catmissionnaire".
								" where individu.codeindividu=individusejour.codeindividu and individu.codeindividu=individuemploi.codeindividu".
								" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat". 
								" and corps.codecatmissionnaire=miss_catmissionnaire.codecatmissionnaire".// and corps.codecatmissionnaire<>''
								" and ((".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$premierdepartdate."'","'".$premierdepartdate."'").
								" 			and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$premierdepartdate."'","'".$premierdepartdate."'").")".
								($estagentpresent?
									" 			or (individu.codeindividu=".GetSQLValueString($row_rs_mission['codeagent'], "text").
														 " and ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$premierdepartdate."'","'".$premierdepartdate."'"). 
														 " and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$premierdepartdate."'","'".$premierdepartdate."'").")"
									:""
								).
								")".
								" UNION".
								" SELECT individu.codeindividu as codeagent,codeciv,nom,prenom,date_naiss,adresse_pers, codepostal_pers, ville_pers, codepays_pers,adresse_admin,telport,email,".
								" '' as numcarteabonnetrain, '' as numcarteabonneavion, '' as dateabonnetrainexpire, '' as dateabonneavionexpire, '' as numcartefidelitetrain, '' as numcartefideliteavion,".
								" '' as zonegeoabonnetrain,'' as  zonegeoabonneavion,'' as numimmatriculation,".
								" '' as datedeb_sejour,'' as codeetab,'' as codemodefinancement, '' as codecatmissionnaire,'' as libcatmissionnaire,".
								" '' as libcorps,'' as composanteenseignement, '' as estetudiant,'' as estpostdoc,'' as motif". 
								" FROM individu".
								" WHERE codeindividu=''".(!$estagentpresent?" or codeindividu=".GetSQLValueString($row_rs_mission['codeagent'], "text"):"").
								" ORDER BY nom,prenom,datedeb_sejour asc";
//le tri asc par datedeb_sejour permet d'avoir le dernier sejour en cas de doublon
//le choix de codecatmissionnaire est laissé r l'appréciation de l'utilisateur
$tab_agent=array();
$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
while($row_rs_agent=mysql_fetch_assoc($rs_agent))
{ $tab_agent[$row_rs_agent['codeagent']]=$row_rs_agent;
	if($row_rs_agent['codeetab']!="07" && $row_rs_agent['estetudiant']=='non')//les membres labo. non UL non etudiant sont extérieurs
	{ $tab_agent[$row_rs_agent['codeagent']]['codecatmissionnaire']="05";
	}
	else if($row_rs_agent['estpostdoc']=='oui')
	{ if($row_rs_agent['codemodefinancement']!='01')//non ATER=>
		{ $tab_agent[$row_rs_agent['codeagent']]['codecatmissionnaire']="02";//BIATSS
		}
	}
	$tab_agent[$row_rs_agent['codeagent']]['estexterieur']=($tab_agent[$row_rs_agent['codeagent']]['codecatmissionnaire']=='05');
}
$query_rs_civilite = "SELECT * FROM civilite WHERE codeciv<>'' ORDER BY codeciv ASC";
$rs_civilite = mysql_query($query_rs_civilite) or die(mysql_error());

$query_rs_miss_abonnementtrain = "SELECT codeabonnementtrain, liblong as libabonnementtrain FROM miss_abonnementtrain ORDER BY numordre ASC";
$rs_miss_abonnementtrain = mysql_query($query_rs_miss_abonnementtrain) or die(mysql_error());

$query_rs_miss_catmissionnaire = "SELECT codecatmissionnaire, liblong as libcatmissionnaire FROM miss_catmissionnaire ORDER BY numordre ASC";
$rs_miss_catmissionnaire = mysql_query($query_rs_miss_catmissionnaire) or die(mysql_error());

$query_rs_miss_classetrain= "SELECT codeclassetrain, liblong as libclassetrain FROM miss_classetrain ORDER BY numordre ASC";
$rs_miss_classetrain = mysql_query($query_rs_miss_classetrain) or die(mysql_error());

$query_rs_miss_lieudepart= "SELECT codelieudepart, liblong as liblieudepart FROM miss_lieudepart ORDER BY numordre ASC";
$rs_miss_lieudepart = mysql_query($query_rs_miss_lieudepart) or die(mysql_error());

$query_rs_miss_puissfiscale= "SELECT codepuissfiscale, liblong as libpuissfiscale FROM miss_puissfiscale ORDER BY numordre ASC";
$rs_miss_puissfiscale = mysql_query($query_rs_miss_puissfiscale) or die(mysql_error());

$query_rs_theme="select codestructure as codetheme,libcourt_fr as libtheme".
												" from structure where codestructure<>'00' and esttheme='oui' order by codestructure";
$rs_theme = mysql_query($query_rs_theme) or die(mysql_error());	

// secr site
$query_rs_secrsite="SELECT distinct codesecrsite,concat(prenom,' ',nom) as nomprenom". 
									 " FROM individu,secrsite".
									 " WHERE individu.codeindividu=secrsite.codesecrsite".
									 " ORDER BY prenom,nom";
$rs_secrsite=mysql_query($query_rs_secrsite) or die(mysql_error());

$rs_ouinon=mysql_query("SELECT codeouinon,libcourt as libouinon FROM ouinon WHERE codeouinon<>'' order by numordre desc");
while($row_rs_ouinon=mysql_fetch_assoc($rs_ouinon))
{ $tab_ouinon[$row_rs_ouinon['codeouinon']]=$row_rs_ouinon;
}
$query_rs_pays =" SELECT codepays,libpays,numordre FROM pays where codepays<>'' ".
								" UNION".
								" SELECT codepays,'[ Choix obligatoire ]', numordre as libpays ".
								" FROM pays where codepays=''". 
								" order by numordre asc,codepays asc";
$rs_pays = mysql_query($query_rs_pays) or die(mysql_error());
	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des missions <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script language="javascript">
var frm=document.forms["<?php echo $form_mission ?>"];
var tab_agent=new Array();
<?php
foreach($tab_agent as $codeagent=>$un_agent)
{?>
	tab_agent["<?php echo $codeagent ?>"]=new Array();
	<?php
	foreach($un_agent as $champ=>$valchamp)
	{ if($champ=="date_naiss" || $champ=="dateabonnetrainexpire" || $champ=="dateabonneavionexpire" )
		{?>
			tab_agent["<?php echo $codeagent ?>"]["<?php echo $champ ?>_jj"]="<?php echo substr($valchamp,8,2); ?>";
			tab_agent["<?php echo $codeagent ?>"]["<?php echo $champ ?>_mm"]="<?php echo substr($valchamp,5,2); ?>";
			tab_agent["<?php echo $codeagent ?>"]["<?php echo $champ ?>_aaaa"]="<?php echo substr($valchamp,0,4); ?>";
		<?php 
		}
		else
		{?>
			tab_agent["<?php echo $codeagent ?>"]["<?php echo $champ ?>"]="<?php echo js_tab_val($valchamp);?>";
		<?php 
		}
	}
}?>
function detailagent(codeagent)
{ var frm=document.forms["<?php echo $form_mission ?>"];
	var catmissionnairelist=frm.elements["codecatmissionnaire"];
	frm.elements["nom"].value=tab_agent[codeagent]["nom"];
	frm.elements["prenom"].value=tab_agent[codeagent]["prenom"];
	frm.elements["date_naiss_jj"].value=tab_agent[codeagent]["date_naiss_jj"];
	if(codeagent!='' && tab_agent[codeagent]["date_naiss_jj"]=='')
	{ alert("Pas de date naissance : la fiche de l'agent est incorrecte."+"\n"+"Il faut la modifier");
	}
	frm.elements["date_naiss_mm"].value=tab_agent[codeagent]["date_naiss_mm"];
	frm.elements["date_naiss_aaaa"].value=tab_agent[codeagent]["date_naiss_aaaa"];
	frm.elements["telport"].value=tab_agent[codeagent]["telport"];
	frm.elements["email"].value=tab_agent[codeagent]["email"];
	frm.elements["adresse_pers"].value=tab_agent[codeagent]["adresse_pers"];
	frm.elements["codepostal_pers"].value=tab_agent[codeagent]["codepostal_pers"];
	frm.elements["ville_pers"].value=tab_agent[codeagent]["ville_pers"];
	frm.elements["codepays_pers"].value=tab_agent[codeagent]["codepays_pers"];
	frm.elements["adresse_admin"].value=tab_agent[codeagent]["adresse_admin"];
	frm.elements["codecatmissionnaire"].value=tab_agent[codeagent]["codecatmissionnaire"];
	frm.elements['gradecongres'].value=tab_agent[codeagent]["libcorps"];
	frm.elements['composanteenseignement'].value=tab_agent[codeagent]["composanteenseignement"];
	if(frm.elements['motif'] && frm.elements['motif'].value=='')
	{ frm.elements['motif'].value=tab_agent[codeagent]["motif"];
	}
	//positionne la categorie missionnaire
	for(indexcatmissionnaire=0;indexcatmissionnaire<=catmissionnairelist.options.length-1;indexcatmissionnaire++)
	{ if(catmissionnairelist.options[indexcatmissionnaire].value==frm.elements["codecatmissionnaire"].value)
		{ catmissionnairelist.selectedIndex=indexcatmissionnaire;
		}		
	}
	changedetail('avectrain');
	changedetail('avecavion');
	changedetail('avecvehiculepersonnel');
			
	if(frm.elements["codeagent"].value=='')
	{ frm.elements["codeciv"].disabled=false;
		frm.elements["nom"].disabled=false;
		frm.elements["prenom"].disabled=false;
		frm.elements["date_naiss_jj"].disabled=false;
		frm.elements["date_naiss_mm"].disabled=false;
		frm.elements["date_naiss_aaaa"].disabled=false;
		frm.elements["email"].disabled=false;
	}
	else
	{ frm.elements["codeciv"].disabled=true;
		frm.elements["nom"].disabled=true;
		frm.elements["prenom"].disabled=true;
		frm.elements["date_naiss_jj"].disabled=true;
		frm.elements["date_naiss_mm"].disabled=true;
		frm.elements["date_naiss_aaaa"].disabled=true;
		frm.elements["email"].disabled=true;
	}
	if(frm.elements["codecatmissionnaire"].value=='05')//exterieur
	{ document.getElementById('textetypeom').innerHTML="DEMANDE D&rsquo;ORDRE DE MISSION&nbsp;pour les intervenants ext&eacute;rieurs &agrave; l&rsquo;UL";
		document.getElementById('celluleestsansfrais').className="cache";
		frm.elements["estsansfrais"].checked=false;
		
	}
	else
	{ document.getElementById('textetypeom').innerHTML="DEMANDE D&rsquo;ORDRE DE MISSION&nbsp;valant autorisation d&rsquo;absence";
		document.getElementById('celluleestsansfrais').className="affiche";
	}
}

function changedetail(champ)
{ var frm=document.forms["<?php echo $form_mission ?>"];
	codeagent=frm.elements["codeagent"].value;
	if(champ=='avectrain')
	{ frm.elements["numcarteabonnetrain"].value=tab_agent[codeagent]["numcarteabonnetrain"];
		frm.elements["dateabonnetrainexpire_jj"].value=tab_agent[codeagent]["dateabonnetrainexpire_jj"];
		frm.elements["dateabonnetrainexpire_mm"].value=tab_agent[codeagent]["dateabonnetrainexpire_mm"];
		frm.elements["dateabonnetrainexpire_aaaa"].value=tab_agent[codeagent]["dateabonnetrainexpire_aaaa"];
		frm.elements["numcartefidelitetrain"].value=tab_agent[codeagent]["numcartefidelitetrain"];
		frm.elements["zonegeoabonnetrain"].value=tab_agent[codeagent]["zonegeoabonnetrain"];
	}
	else if(champ=='avecavion')
	{ frm.elements["numcarteabonneavion"].value=tab_agent[codeagent]["numcarteabonneavion"];
		frm.elements["dateabonneavionexpire_jj"].value=tab_agent[codeagent]["dateabonneavionexpire_jj"];
		frm.elements["dateabonneavionexpire_mm"].value=tab_agent[codeagent]["dateabonneavionexpire_mm"];
		frm.elements["dateabonneavionexpire_aaaa"].value=tab_agent[codeagent]["dateabonneavionexpire_aaaa"];
		frm.elements["numcartefideliteavion"].value=tab_agent[codeagent]["numcartefideliteavion"];
		frm.elements["zonegeoabonneavion"].value=tab_agent[codeagent]["zonegeoabonneavion"];
	}
	else if(champ=='avecvehiculepersonnel')
	{ frm.elements["numimmatriculation"].value=tab_agent[codeagent]["numimmatriculation"];
	}
}

function copie_champs_om2autorisation()
{ var frm=document.forms["<?php echo $form_mission ?>"];
	frm.elements['intitulecongres'].value=frm.elements['motif'].value;
	frm.elements['villecongres'].value=frm.elements['arriveelieu#01'].value;
	frm.elements['datedepartcongres_jj'].value=frm.elements['departdate_jj#01'].value; frm.elements['datedepartcongres_mm'].value=frm.elements['departdate_mm#01'].value;	frm.elements['datedepartcongres_aaaa'].value=frm.elements['departdate_aaaa#01'].value;
	frm.elements['datearriveecongres_jj'].value=frm.elements['arriveedate_jj#01'].value; frm.elements['datearriveecongres_mm'].value=frm.elements['arriveedate_mm#01'].value; frm.elements['datearriveecongres_aaaa'].value=frm.elements['arriveedate_aaaa#01'].value;
	frm.elements['heuredepartcongres_hh'].value=frm.elements['departheure_hh#01'].value; frm.elements['heuredepartcongres_mn'].value=frm.elements['departheure_mn#01'].value
	frm.elements['heurearriveecongres_hh'].value=frm.elements['arriveeheure_hh#01'].value; frm.elements['heurearriveecongres_mn'].value=frm.elements['arriveeheure_mn#01'].value
	frm.elements['avectraincongres'].checked=frm.elements['avectrain'].checked;
	frm.elements['avecavioncongres'].checked=frm.elements['avecavion'].checked;
	frm.elements['avecvehiculeperscongres'].checked=frm.elements['avecvehiculepersonnel'].checked;

	frm.elements['avechebergementcongres'].checked=frm.elements['avechotel'].checked;
	frm.elements['avecpriseenchargeautre_ulcongres'].checked=frm.elements['avecpriseenchargeautre_ul'].checked;
	frm.elements['organismepriseenchargeautrecongres'].value=frm.elements['organismepriseencharge'].value;
	frm.elements['centrecoutcongres'].value=frm.elements['centrecout'].value;
	frm.elements['eotpcongres'].value=frm.elements['eotp'].value;
	if(frm.elements['libpays'] && frm.elements['payscongres'] && frm.elements['libpays'].value.toLowerCase()!='france')
	{ frm.elements['payscongres'].value=frm.elements['libpays'].value
	}
	frm.elements['eotpcongres'].value=frm.elements['eotp'].value;

	if(frm.elements['codeagent'].value!='')
	{ frm.elements['gradecongres'].value=tab_agent[frm.elements['codeagent'].value]["libcorps"];
		frm.elements['composanteenseignement'].value=tab_agent[frm.elements['codeagent'].value]["composanteenseignement"];
	}
	else
	{	frm.elements['gradecongres'].value='';
		frm.elements['composanteenseignement'].value='';
	}
}
</script>
<style type="text/css">
.monlisting {    /* The default table for document listings. Contains name, document types, modification times etc in a file-browser-like fashion */
    border-collapse: collapse;
}
</style>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?"\\n":'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;"," Attention.".$warning)) ?>')"<?php }?>>
<form name="<?php echo $form_mission ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="return controle_form_mission('<?php echo $form_mission ?>')">
<input type="hidden" name="action" value="<?php echo $action ?>">
<input type="hidden" name="codemission" value="<?php echo $codemission ?>" >
<input type="hidden" name="MM_update" value="<?php echo $form_mission ?>">
<input type="hidden" name="nbetape" value="<?php echo $nbetape ?>">
<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_avion.png','titrepage'=>'Mission',
																'lienretour'=>'gestioncommandes.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Retour &agrave; la gestion des missions',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;
    </td>
  </tr>
  <tr>
    <td>
      <span class="bleucalibri9">N&deg; interne : </span>
      <span class="mauvegrascalibri9"><?php echo $action=='creer'?'A cr&eacute;er':$row_rs_mission['codemission'] ?></span>
    </td>
  </tr>
  <tr>
    <td nowrap>
      <span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
      <span class="infomauve"><?php echo ($row_rs_mission['codecreateur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_mission['createurprenom']." ".$row_rs_mission['createurnom']); ?></span>
      <span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_mission['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_mission['date_creation'],"/")) ?></span>
      <img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
      <span class="infomauve"><?php echo ($row_rs_mission['codemodifieur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_mission['modifieurprenom']." ".$row_rs_mission['modifieurnom']); ?></span>
      <span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_mission['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_mission['date_modif'],"/")) ?></span>
    </td>
  </tr>
  <tr>
   	<td>&nbsp;
    </td>
 </tr>
  <tr>
    <td class="noircourier9">
    	<table>
      	<tr>
          <td><span class="bleugrascalibri10"><span class="<?php echo $estagentpresent?'bleugrascalibri10':'rougegrascalibri10' ?>">Demandeur :</span></td>
          <td>
          <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_coordonnees">
          <div class="tooltipContent_cadre" id="info_coordonnees">
            <span class="noircalibri10">
            Si vous s&eacute;lectionnez un "membre" <?php echo $GLOBALS['acronymelabo'] ?> dans la liste d&eacute;roulante, quelques champs sont automatiquement renseign&eacute;s :<br>
            &nbsp;&nbsp;- nom, pr&eacute;nom, date de naissance et adresse mail : <b>non modifiables mais obligatoires</b> ;<br>
            &nbsp;&nbsp;- t&eacute;l, adresses personnelle et administrative : <b>modifiables, l&rsquo;une des deux adresses est obligatoire</b><br>
            &nbsp;&nbsp;- carte d&rsquo;abonnement train<br>
            &nbsp;&nbsp;- carte d&rsquo;abonn&eacute; Air France, date d&rsquo;expiration, carte de fid&eacute;lit&eacute;, compagnie<br>
            &nbsp;&nbsp;- puissance fiscale et immatriculation<br>
            Ces informations modifiables sont report&eacute;es automatiquement dans le dossier du "membre" <?php echo $GLOBALS['acronymelabo'] ?> lors de l'enregistrement.<br><br>
            D&rsquo;une mission &agrave; l&rsquo;autre les coordonn&eacute;es du missionnaire peuvent varier : elles sont aussi enregistr&eacute;es au niveau de la mission <br>
            et ne seront plus remplac&eacute;es par les coordonn&eacute;es inscrites dans le dossier "membre" <?php echo $GLOBALS['acronymelabo'] ?>.<br><br>
            <b>Attention</b> : si vous d&eacute;selectionnez un missionnaire (selection d&rsquo;un autre missionnaire) pour lequel vous avez entr&eacute;<br>
            des coordonn&eacute;es, celles-ci seront remplac&eacute;es par celles de son dossier "membre" <?php echo $GLOBALS['acronymelabo'] ?> si vous le s&eacute;lectionnez &agrave; nouveau<br><br>
            Pour saisir les informations d'un missionnaire non "membre" <?php echo $GLOBALS['acronymelabo'] ?>, il suffit de laisser la liste de s&eacute;lection de personne "vide" : vous pouvez saisir<br>
            les informations le concernant. Elles ne peuvent pas &ecirc;tre report&eacute;es dans la base des membres <?php echo $GLOBALS['acronymelabo'] ?> et sont propres &agrave; la mission : la prochaine<br>
            mission pour cette personne n&eacute;cessitera de saisir l'ensemble des informations. Dans le futur, nous proposerons la liste de toutes les personnes ayant<br>
            d&eacute;j&agrave; effectu&eacute; une mission afin de ne pas avoir &agrave; les saisir &agrave; nouveau, si cela s'av&egrave;re utile.
            </span>
          </div>
          <script type="text/javascript">
            var sprytooltip_coordonnees = new Spry.Widget.Tooltip("info_coordonnees", "#sprytrigger_info_coordonnees", {offsetX:-200, offsetY:20, closeOnTooltipLeave:true});
          </script>
          </td>
          <td><?php 
              if(!$estagentpresent)
              { ?><img src="images/b_attention.png" width="16" height="16" id="sprytrigger_info_agent_parti">
                  <div class="tooltipContent_cadre" id="info_agent_parti">
                    <span class="noircalibri10">
                      <b>Attention :</b> <?php echo $tab_agent[$row_rs_mission['codeagent']]['prenom'] ?> <?php echo $tab_agent[$row_rs_mission['codeagent']]['nom'] ?> a quitt&eacute; l&rsquo;unit&eacute; ou n&rsquo;a pas d&rsquo;emploi
                      &agrave; la date de la mission.<br>
                      Si vous s&eacute;lectionnez un autre missionnaire et que vous enregistrez,
                     <?php echo $tab_agent[$row_rs_mission['codeagent']]['prenom'] ?> <?php echo $tab_agent[$row_rs_mission['codeagent']]['nom'] ?> ne vous sera plus propos&eacute; dans cette liste.<br>
                    </span>
                  </div>
                  <script type="text/javascript">
                    var sprytooltip_info_agent_parti = new Spry.Widget.Tooltip("info_agent_parti", "#sprytrigger_info_agent_parti", {useEffect:"blind", offsetX:-600, offsetY:20});
                  </script>
              <?php
              } 
              $disableddetailagent='';
              if($row_rs_mission['codeagent']!='')
              { $disableddetailagent='disabled';
              }?>
            <select name="codeagent" class="noircalibri10" id="codeagent" 
            onChange="detailagent(this.value);<?php if(!$estagentpresent) {?>alert('Voir les informations du panneau rouge')<?php }?>">
            <?php
              foreach($tab_agent as $codeagent=>$row_rs_agent)
              { ?>
              <option value="<?php echo $row_rs_agent['codeagent'] ?>"<?php echo $row_rs_mission['codeagent']==$row_rs_agent['codeagent']?' selected  ':'' ?>><?php echo $row_rs_agent['nom'].' '.$row_rs_agent['prenom'] ?></option>
            <?php
              } ?>
            </select>
          </td>
          <td><span class="bleugrascalibri10">Secr&eacute;taire de site :</span></td>
          <td><select name="codesecrsite" class="noircalibri10" id="codesecrsite" >
            <?php
              while($row_rs_secrsite=mysql_fetch_assoc($rs_secrsite))
              { ?>
            <option value="<?php echo $row_rs_secrsite['codesecrsite'] ?>" <?php echo $row_rs_mission['codesecrsite']==$row_rs_secrsite['codesecrsite']?' selected':'' ?>><?php echo $row_rs_secrsite['nomprenom'] ?></option>
            <?php
              } ?>
          </select>
          </td>
          <td align="right"><?php 
              if($codemission!='')
              { $url="detailmission.php?codemission=".$codemission;?>
                  <img src="images/espaceur.gif" width="50" height="1">
                    <a href="<?php echo $url ?>" target="_new" ><img class="icon" width="16" height="16" src="images/b_imprimer.png">&nbsp;Impression de la demande d&rsquo;OM
                    </a>&nbsp;<span class="noircalibri9">(Enregistrer avant d&rsquo;imprimer)</span>
              <?php 
              }?>
          </td>
      	</tr>
    	</table>
    </td>
  </tr>
  <tr>
   	<td>&nbsp;
    </td>
 </tr>
  <tr>
		<td>
			<input type="radio" name="avecautorisationcongres" id="avecautorisationcongres" value="" <?php echo $row_rs_mission['avecautorisationcongres']==''?'checked':''; ?>
      onclick="document.getElementById('autorisation_congres').className='cache'"><span class="bleugrascalibri10">Pas d&rsquo;autorisation d&rsquo;absence</span>
      <input type="radio" name="avecautorisationcongres" id="avecautorisationcongres" value="mono" <?php echo $row_rs_mission['avecautorisationcongres']=='mono'?'checked':''; ?>
      onclick="document.getElementById('autorisation_congres').className='affiche'"><span class="bleugrascalibri10">Autorisation d&rsquo;absence</span>
      <input type="radio" name="avecautorisationcongres" id="avecautorisationcongres" value="bi" <?php echo $row_rs_mission['avecautorisationcongres']=='bi'?'checked':''; ?>
      onclick="document.getElementById('autorisation_congres').className='affiche'"><span class="bleugrascalibri10">Autorisation d&rsquo;absence bi-appartenant</span>
    </td>
  </tr>
  <tr>
  	<td>
      <table width="100%">
        <tr>
          <td>
          </td>
          <td align="center" class="noirgrascalibri11" bgcolor="#EEEEEE">
          <div id='textetypeom'>
          <?php 
          if($tab_agent[$row_rs_mission['codeagent']]['estexterieur'])
          {?>DEMANDE D'ORDRE DE MISSION&nbsp;pour les intervenants ext&eacute;rieurs &agrave; l&rsquo;UL
          <?php 
          }
          else
          {?>DEMANDE D'ORDRE DE MISSION&nbsp;valant autorisation d&rsquo;absence
          <?php 
          }?>
          </div>
          </td>
        </tr>
        <tr><td></td><td align="center"><span class="noircalibri9">D&eacute;cret n&deg; 2006-781 du 03/07/2006</span></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
    <!-- <div id='ordre_de_mission' class="affiche"> -->
      <table>
        <tr>
          <td class="noircourier9">
            <table width="100%" border="1" align="center" class="vertical listing">
              <tr>
                <td align="center" bgcolor="#FFFFFF"><input name="avecpriseenchargetotale" type="checkbox" id="avecpriseenchargetotale" <?php echo ($row_rs_mission['avecpriseenchargetotale']=="oui"?'checked':''); ?>>Prise en charge totale UL</td>
                <td align="center" bgcolor="#FFFFFF"><input name="avecpriseenchargepartielle" type="checkbox" id="avecpriseenchargepartielle" <?php echo ($row_rs_mission['avecpriseenchargepartielle']=="oui"?'checked':''); ?>>Prise en charge partielle UL</td>
                <td align="center" bgcolor="#FFFFFF"><input name="avecpriseenchargeautre_ul" type="checkbox" id="avecpriseenchargeautre_ul" <?php echo ($row_rs_mission['avecpriseenchargeautre_ul']=="oui"?'checked':''); ?>>Prise en charge par</td>
                <td><input name="organismepriseencharge" type="text" class="noircalibri10" id="organismepriseencharge" value="<?php echo htmlspecialchars($row_rs_mission['organismepriseencharge']) ; ?>" size="50" maxlength="50">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="noircourier9">&nbsp;</td>
        </tr>
        <tr>
          <td bgcolor="#EEEEEE" class="noirgrascalibri10">INFORMATIONS SUR LE &quot;MISSIONNAIRE&quot;</td>
        </tr>
        <tr>
          <td>
           <table width="100%" border="0" class="table_cadre_arrondi">
            <tr>
              <td>
                <table width="100%" border="0">
                  <tr>
                    <td width="31%" valign="top" nowrap class="bleugrascalibri10">Nom Pr&eacute;nom<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                    <td width="38%" valign="top" nowrap>
                      <select name="codeciv" id="codeciv" class="noircalibri10" <?php echo $disableddetailagent ?>>
												<?php
                        while ($row_rs_civilite = mysql_fetch_assoc($rs_civilite)) 
                        { ?>
                        <option value="<?php echo $row_rs_civilite['codeciv']?>" <?php if ($row_rs_civilite['codeciv']==$row_rs_mission['codeciv']) {echo "SELECTED";} ?> ><?php echo $row_rs_civilite['libcourt_fr']?></option>
                        <?php
                        } 
                        ?>
    	            		</select>
                      <input name="nom" type="text" class="noircalibri10" id="nom" value="<?php echo htmlspecialchars($row_rs_mission['nom']); ?>" size="20" maxlength="30" <?php echo $disableddetailagent ?>>
                      <input name="prenom" type="text" class="noircalibri10" id="prenom" value="<?php echo htmlspecialchars($row_rs_mission['prenom']); ?>" size="20" maxlength="20" <?php echo $disableddetailagent  ?>>
                    </td>
                    <td width="12%" valign="top" nowrap class="bleugrascalibri10">Type d'agent<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                    <td width="19%" valign="top" nowrap>
                      <select name="codecatmissionnaire" class="noircalibri10" id="codecatmissionnaire">
                        <?php
                        while($row_rs_miss_catmissionnaire=mysql_fetch_assoc($rs_miss_catmissionnaire))
                        { ?>
                        <option value="<?php echo $row_rs_miss_catmissionnaire['codecatmissionnaire'] ?>" <?php echo ($row_rs_mission['codecatmissionnaire']==$row_rs_miss_catmissionnaire['codecatmissionnaire']?'selected':'') ?>><?php echo $row_rs_miss_catmissionnaire['libcatmissionnaire'] ?></option>
                        <?php
                        } ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td valign="top" nowrap class="bleugrascalibri10">Date de naissance<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                    <td valign="top" nowrap>
                      <input name="date_naiss_jj" type="text"  class="noircalibri10" id="date_naiss_jj" value="<?php echo substr($row_rs_mission['date_naiss'],8,2); ?>" size="2" maxlength="2" <?php echo $disableddetailagent  ?>>
                      <input name="date_naiss_mm" type="text"  class="noircalibri10" id="date_naiss_mm" value="<?php echo substr($row_rs_mission['date_naiss'],5,2); ?>" size="2" maxlength="2" <?php echo $disableddetailagent  ?>>
                      <input name="date_naiss_aaaa" type="text"  class="noircalibri10" id="date_naiss_aaaa" value="<?php echo substr($row_rs_mission['date_naiss'],0,4); ?>" size="4" maxlength="4" <?php echo $disableddetailagent  ?>>
                    </td>
                    <td valign="top" nowrap class="bleugrascalibri10">Courriel :&nbsp;</td>
                    <td valign="top" nowrap><input name="email" type="text" class="noircalibri10" id="email" value="<?php echo $row_rs_mission['email']; ?>" size="40" maxlength="100" <?php echo $disableddetailagent  ?>></td>
                  </tr>
                  <tr>
                    <td valign="top" nowrap>
                      <span class="bleugrascalibri10">Adresse personnelle :</span>&nbsp;<br>
                      <span class="bleucalibri9">(</span><span id="adresse_pers#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_mission['adresse_pers']) ?></span><span class="bleucalibri9">/200 car. max.)</span>
                    </td>
                    <td valign="top" nowrap><textarea name="adresse_pers" id="adresse_pers" cols="40" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_pers#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_mission['adresse_pers']; ?></textarea></td>
                    <td valign="top" nowrap class="bleugrascalibri10">T&eacute;l. port. :&nbsp;</td>
                    <td valign="top" nowrap><input name="telport" type="text" class="noircalibri10" id="telport" value="<?php echo $row_rs_mission['telport']; ?>" size="20" maxlength="20"></td>
                  </tr>
                  <tr>
                  	<td></td>
                    <td nowrap>
                    <span class="bleugrascalibri10">Code postal</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                    <input name="codepostal_pers" type="text" class="noircalibri10" id="codepostal_pers" value="<?php echo htmlspecialchars($row_rs_mission['codepostal_pers']); ?>" size="6" maxlength="20">
                    <span class="bleugrascalibri10">Ville</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                    <input name="ville_pers" type="text" class="noircalibri10" id="ville_pers" value="<?php echo htmlspecialchars($row_rs_mission['ville_pers']); ?>" size="30" maxlength="50">
                    <span class="bleugrascalibri10">Pays</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span>
                      <select name="codepays_pers" class="noircalibri10" id="codepays_pers">
                        <?php
                        
                        while ($row_rs_pays = mysql_fetch_assoc($rs_pays)) 
                        {	?>
                          <option value="<?php echo $row_rs_pays['codepays']?>" <?php echo $row_rs_pays['codepays']==$row_rs_mission['codepays_pers']?'selected':''?>><?php echo substr($row_rs_pays['libpays'],0,20)?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </td>
                  </tr>

                  <tr><td colspan="4"><span class="rougecalibri9"><sup>Au moins une adresse obligatoire</sup></span></td>
                  </tr>
                  <tr>
                    <td valign="top" nowrap>
                      <span class="bleugrascalibri10">Adresse administrative :&nbsp;</span><br>
                      <span class="bleucalibri9">(</span><span id="adresse_admin#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_mission['adresse_admin']) ?></span><span class="bleucalibri9">/200 car. max.)</span>
                    </td>
                    <td valign="top" nowrap><textarea name="adresse_admin" id="adresse_admin" cols="40" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_admin#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_mission['adresse_admin']; ?></textarea></td>
                    <td valign="top" nowrap>&nbsp;</td>
                    <td valign="top" nowrap></td>
                  </tr>
                  <tr>
                    <td valign="top" nowrap>&nbsp;</td>
                    <td valign="top" nowrap>&nbsp;</td>
                    <td valign="top" nowrap>&nbsp;</td>
                    <td valign="top" nowrap></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
         </td>
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
        <td style="border-bottom:none; border-top:none">
        	<table width="100%" border="0">
          <tr>
            <td width="50%" nowrap class="noircalibri11">N&deg; de carte : <input name="numcarteabonnetrain" type="text" class="noircalibri10" id="numcarteabonnetrain" value="<?php echo $row_rs_mission['numcarteabonnetrain']; ?>" size="20" maxlength="20"></td>
            <td nowrap class="noircalibri11">&nbsp;Date d'expiration : 
                      <input name="dateabonnetrainexpire_jj" type="text"  class="noircalibri10" id="dateabonnetrainexpire_jj" value="<?php echo substr($row_rs_mission['dateabonnetrainexpire'],8,2); ?>" size="2" maxlength="2">
                      <input name="dateabonnetrainexpire_mm" type="text"  class="noircalibri10" id="dateabonnetrainexpire_mm" value="<?php echo substr($row_rs_mission['dateabonnetrainexpire'],5,2); ?>" size="2" maxlength="2">
                      <input name="dateabonnetrainexpire_aaaa" type="text"  class="noircalibri10" id="dateabonnetrainexpire_aaaa" value="<?php echo substr($row_rs_mission['dateabonnetrainexpire'],0,4); ?>" size="4" maxlength="4">
						</td>
          </tr>
        </table></td>
        <td style="border-bottom:none; border-top:none"><table width="100%" border="0">
          <tr>
            <td width="50%" nowrap class="noircalibri11" style="border-bottom:none; border-top:none">N&deg; de carte : <input name="numcarteabonneavion" type="text" class="noircalibri10" id="numcarteabonneavion" value="<?php echo $row_rs_mission['numcarteabonneavion']; ?>" size="20" maxlength="20"></td>
            <td nowrap class="noircalibri11" style="border-bottom:none; border-top:none">&nbsp;Date d'expiration : 
                      <input name="dateabonneavionexpire_jj" type="text"  class="noircalibri10" id="dateabonneavionexpire_jj" value="<?php echo substr($row_rs_mission['dateabonneavionexpire'],8,2); ?>" size="2" maxlength="2">
                      <input name="dateabonneavionexpire_mm" type="text"  class="noircalibri10" id="dateabonneavionexpire_mm" value="<?php echo substr($row_rs_mission['dateabonneavionexpire'],5,2); ?>" size="2" maxlength="2">
                      <input name="dateabonneavionexpire_aaaa" type="text"  class="noircalibri10" id="dateabonneavionexpire_aaaa" value="<?php echo substr($row_rs_mission['dateabonneavionexpire'],0,4); ?>" size="4" maxlength="4"
          	</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="noircalibri11" style="border-bottom:none; border-top:none">Zone g&eacute;ographique : <input name="zonegeoabonnetrain" type="text" class="noircalibri10" id="zonegeoabonnetrain" value="<?php echo htmlspecialchars($row_rs_mission['zonegeoabonnetrain']); ?>" size="20" maxlength="50"></td>
        <td class="noircalibri11" style="border-bottom:none; border-top:none">Zone g&eacute;ographique : <input name="zonegeoabonneavion" type="text" class="noircalibri10" id="zonegeoabonneavion" value="<?php echo htmlspecialchars($row_rs_mission['zonegeoabonneavion']); ?>" size="20" maxlength="50"></td>
      </tr>
      <tr>
        <td class="noirgrascalibri11" style="border-bottom:none; border-top:none">TYPE DE  PROGRAMME DE FIDELITE : </td>
        <td class="noirgrascalibri11" style="border-bottom:none; border-top:none">TYPE DE  PROGRAMME DE FIDELITE : </td>
      </tr>
      <tr>
        <td class="noircalibri11" style="border-bottom:none; border-top:none">N&deg; de carte : <input name="numcartefidelitetrain" type="text" class="noircalibri10" id="numcartefidelitetrain" value="<?php echo $row_rs_mission['numcartefidelitetrain']; ?>" size="20" maxlength="20"></td>
        <td class="noircalibri11" style="border-bottom:none; border-top:none">N&deg; de carte : <input name="numcartefideliteavion" type="text" class="noircalibri10" id="numcartefideliteavion" value="<?php echo $row_rs_mission['numcartefideliteavion']; ?>" size="20" maxlength="20"></td>
      </tr>
    </table></td>
  </tr>
        <tr>
          <td bgcolor="#EEEEEE" class="noirgrascalibri10">INFORMATIONS SUR LE D&Eacute;PLACEMENT</td>
        </tr>
        <tr>
          <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
              <tr>
                <td>
                  <table width="100%" border="0" cellpadding="0" cellspacing="3">
                    <tr>
                      <td nowrap>
                       <table border="0" cellspacing="2" cellpadding="0">
                        <tr>
                          <td valign="top"><span class="bleugrascalibri10">Motif<span class="rougecalibri9"><sup>*</sup></span> :</span></td>
                          <td><input name="motif"  type="text" class="noircalibri10" id="motif" value="<?php echo htmlspecialchars($row_rs_mission['motif']) ?>" size="100" maxlength="200"></td>
                          <td valign="top"><span class="bleugrascalibri10">Pays :</span></td>
                          <td><input name="libpays"  type="text" class="noircalibri10" id="libpays" value="<?php echo $row_rs_mission['libpays'] ?>" size="20" maxlength="50"></td>
                        </tr>
                       </table>
                      </td>
                    </tr>
                    <tr>
                      <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td width="10%" rowspan="2" bgcolor="#CCCCCC" class="noirgrascalibri12" nowrap>A commander&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                          <td width="12%" nowrap bgcolor="#CCCCCC" class="noircalibri10"><input name="avectrain" type="checkbox" id="avectrain" <?php echo ($row_rs_mission['avectrain']=="oui"?'checked':''); ?>>&nbsp;Train</td>
                          <td width="7%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
                          <td width="24%" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><input name="avecavion" type="checkbox" id="avecavion" <?php echo ($row_rs_mission['avecavion']=="oui"?'checked':''); ?>>&nbsp;Avion</span></td>
                          <td width="15%" align="center" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><input name="avecvehiculelocation" type="checkbox" id="avecvehiculelocation" <?php echo ($row_rs_mission['avecvehiculelocation']=="oui"?'checked':''); ?>>
                            &nbsp;V&eacute;hicule de location</span></td>
                          <td width="32%" nowrap bgcolor="#CCCCCC" class="noircalibri10"><input name="avecautre" type="checkbox" id="avecautre" <?php echo ($row_rs_mission['avecautre']=="oui"?'checked':''); ?>>
                          &nbsp;Autre &agrave; pr&eacute;ciser :<input name="avecautredetail"  type="text" class="noircalibri10" id="avecautredetail" value="<?php echo $row_rs_mission['avecautredetail'] ?>" size="20" maxlength="50"></td>
                        </tr>
                        <tr>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri10"><input name="avechotel" type="checkbox" id="avechotel" <?php echo ($row_rs_mission['avechotel']=="oui"?'checked':''); ?>>&nbsp;H&ocirc;tel&nbsp; </td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11">Dates :</td>
                          <td nowrap bgcolor="#CCCCCC"><span class="noircalibri11"><input name="hoteldates"  type="text" class="noircalibri10" id="hoteldates" value="<?php echo $row_rs_mission['hoteldates'] ?>" size="30" maxlength="30">
                          </span></td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11">Nombre de nuit(s) &agrave; r&eacute;server :</td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><span class="bleugrascalibri10">
                            <input name="nbnuitshotelcharge" type="text" class="noircalibri10" id="nbnuitshotelcharge" value="<?php echo $row_rs_mission['nbnuitshotelcharge']; ?>" size="2" maxlength="2">
                          </span></td>
                        </tr>
                        <tr>
                          <td nowrap bgcolor="#CCCCCC">&nbsp;</td>
                          <td nowrap bgcolor="#CCCCCC">&nbsp;</td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11">Choix 1 :</td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><span class="bleugrascalibri10">
                            <input name="hotelmarchechoix1" type="text" class="noircalibri10" id="hotelmarchechoix1" value="<?php echo htmlspecialchars($row_rs_mission['hotelmarchechoix1']); ?>" size="30" maxlength="50">
                          </span></td>
                          <td align="center" nowrap bgcolor="#CCCCCC" class="noircalibri11">Choix 2 :</td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11"><span class="bleugrascalibri10">
                            <input name="hotelmarchechoix2" type="text" class="noircalibri10" id="hotelmarchechoix2" value="<?php echo htmlspecialchars($row_rs_mission['hotelmarchechoix2']); ?>" size="30" maxlength="50">
                          </span></td>
                        </tr>
                      </table></td>
                    </tr>
                    <tr>
                      <td valign="top" class="bleugrascalibri10">
                        <table width="100%" border="1" class="vertical listing" >
                          <tr>
                          <td colspan="2" align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">D&eacute;part<span class="rougecalibri9"><sup>*</sup></span></td>
                          <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">&nbsp;</td>
                          <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">&nbsp;</td>
                          <td colspan="2" align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Arriv&eacute;e<span class="rougecalibri9"><sup>*</sup></span></td>
                          <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">&nbsp;</td>
                         </tr>
                          <tr class="bleugrascalibri10">
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Date<span class="rougecalibri9"><sup>*</sup></span></td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Heure</td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Ville D&eacute;part<span class="rougecalibri9"><sup></sup></span><span class="rougecalibri9"><sup>*</sup></span></td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Ville Destination<span class="rougecalibri9"><sup>*</sup></span></td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Date<span class="rougecalibri9"><sup>*</sup></span></td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Heure</td>
                            <td align="center" class="bleugrascalibri10" bgcolor="#EEEEEE">Transport utilis&eacute;</td>
                          </tr>
                          <?php 
                          foreach($tab_missionetape as $numetape=>$une_missionetape)
                          {?>
                          <tr>
                            <td nowrap><input name="departdate_jj#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="departdate_jj#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['departdate'],8,2); ?>" size="2" maxlength="2">
                              <input name="departdate_mm#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="departdate_mm#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['departdate'],5,2); ?>" size="2" maxlength="2">
                              <input name="departdate_aaaa#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="departdate_aaaa#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['departdate'],0,4); ?>" size="4" maxlength="4"></td>
                            <td nowrap><input type="text" class="noircalibri10" name="departheure_hh#<?php echo $numetape ?>" id="departheure_hh#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['departheure'],0,2); ?>" size="2" maxlength="2">
                              <span class="bleucalibri10">h</span>
                              <input type="text" class="noircalibri10" name="departheure_mn#<?php echo $numetape ?>" id="departheure_mn#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['departheure'],3,2); ?>" size="2" maxlength="2"></td>
                            <td nowrap><input name="departlieu#<?php echo $numetape ?>" type="text" class="noircalibri10" id="departlieu#<?php echo $numetape ?>" value="<?php echo htmlspecialchars($une_missionetape['departlieu']); ?>" size="40" maxlength="100"></td>
                            <td nowrap><input name="arriveelieu#<?php echo $numetape ?>" type="text" class="noircalibri10" id="arriveelieu#<?php echo $numetape ?>" value="<?php echo htmlspecialchars($une_missionetape['arriveelieu']); ?>" size="40" maxlength="100"></td>
                            <td nowrap>
                              <input name="arriveedate_jj#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="arriveedate_jj#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['arriveedate'],8,2); ?>" size="2" maxlength="2">
                              <input name="arriveedate_mm#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="arriveedate_mm#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['arriveedate'],5,2); ?>" size="2" maxlength="2">
                              <input name="arriveedate_aaaa#<?php echo $numetape ?>" type="text"  class="noircalibri10" id="arriveedate_aaaa#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['arriveedate'],0,4); ?>" size="4" maxlength="4">
                            </td>
                            <td nowrap>
                              <input type="text" class="noircalibri10" name="arriveeheure_hh#<?php echo $numetape ?>" id="arriveeheure_hh#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['arriveeheure'],0,2); ?>" size="2" maxlength="2">
                               <span class="bleucalibri10">h</span>
                              <input type="text" class="noircalibri10" name="arriveeheure_mn#<?php echo $numetape ?>" id="arriveeheure_mn#<?php echo $numetape ?>" value="<?php echo substr($une_missionetape['arriveeheure'],3,2); ?>" size="2" maxlength="2">
                            </td>
                            <td nowrap><input name="moyentransport#<?php echo $numetape ?>" type="text" class="noircalibri10" id="moyentransport#<?php echo $numetape ?>" value="<?php echo htmlspecialchars($une_missionetape['moyentransport']); ?>" size="20" maxlength="30">
                            </td>
                          </tr>
                          <?php 
													}?>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td valign="top" class="bleugrascalibri10">Informations compl&eacute;mentaires sur le d&eacute;roulement de la mission :<input name="infocompmission" type="text" class="noircalibri10" id="infocompmission" value="<?php echo htmlspecialchars($row_rs_mission['infocompmission']); ?>" size="100" maxlength="100">
                      </td>
                    </tr>
                    <tr>
                      <td valign="top"><table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td width="6%" rowspan="2" nowrap bgcolor="#CCCCCC" class="noirgrascalibri12">J'utilise&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                          <td width="13%" nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><input name="avecvehiculeservice" type="checkbox" id="avecvehiculeservice" <?php echo ($row_rs_mission['avecvehiculeservice']=="oui"?'checked':''); ?>> Un v&eacute;hicule administratif</span></td>
                          <td width="55%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
                          <td width="26%" nowrap bgcolor="#CCCCCC">&nbsp;</td>
                        </tr>
                        <tr>
                          <td nowrap bgcolor="#CCCCCC"><span class="noircalibri10"><input name="avecvehiculepersonnel" type="checkbox" id="avecvehiculepersonnel" <?php echo ($row_rs_mission['avecvehiculepersonnel']=="oui"?'checked':''); ?>> Mon v&eacute;hicule personnel</span></td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri10">Noms des personnes transport&eacute;es : <input name="vehiculepersonnelnomspersonnes" type="text" class="noircalibri10" id="vehiculepersonnelnomspersonnes" value="<?php echo htmlspecialchars($row_rs_mission['vehiculepersonnelnomspersonnes']); ?>" size="50" maxlength="100"></td>
                          <td nowrap bgcolor="#CCCCCC" class="noircalibri11">Immatriculation : <span class="bleugrascalibri10">
                            <input name="numimmatriculation" type="text" class="noircalibri10" id="numimmatriculation" value="<?php echo $row_rs_mission['numimmatriculation']; ?>" size="20" maxlength="20">
                          </span></td>
                        </tr>
                      </table></td>
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
          <td nowrap bgcolor="#EEEEEE"><span class="noirgrascalibri10">INFORMATIONS FINANCI&Egrave;RES :&nbsp;</span></td>
        </tr>
        <tr>
          <td>
            <table width="100%" border="0" class="table_cadre_arrondi">
              <tr>
                <td>
                  <table width="100%" border="0">
                    <tr>
                      <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td width="29%" nowrap><span class="noirgrascalibri10">CO&Ucirc;T TOTAL DE LA MISSION ESTIM&Eacute; A :</span> <b><span class="bleugrascalibri10">
                            <input name="montantestimemission" type="text" class="noircalibri10" id="montantestimemission" value="<?php echo $row_rs_mission['montantestimemission']; ?>" size="12" maxlength="12">
                            </span></b><span class="noircalibri11">&#8364;</span></td>
                          <td width="7%"><img src="images/espaceur.gif" alt="" width="100" height="1"></td>
                          <td width="64%" nowrap><span class="noirgrascalibri10">FORFAIT ACCORD&Eacute;</span> <span class="noircalibri9">(tout compris)</span> <b>: <span class="bleugrascalibri10">
                            <input name="forfait" type="text" class="noircalibri10" id="forfait" value="<?php echo $row_rs_mission['forfait']; ?>" size="12" maxlength="12">
                            </span></b><span class="noircalibri11">&#8364;</span></td>
                          </tr>
                        </table></td>
                      </tr>
                    <tr>
                      <td><span class="noirgrascalibri11">DEMANDE D'AVANCE</span> <span class="noircalibri10">(limit&eacute;e &agrave; 75 % des frais de la mission)</span><span class="bleugrascalibri10"><b class="bleugrascalibri10">
                        <input type="radio" name="avecavance" id="avecavance" value="oui" <?php echo $row_rs_mission['avecavance']=='oui'?'checked':''; ?>>
                        </b></span> <span class="noircalibri11"> OUI&nbsp;&nbsp;&nbsp;<span class="bleugrascalibri10"><b class="bleugrascalibri10">
                          <input type="radio" name="avecavance" id="avecavance" value="non" <?php echo $row_rs_mission['avecavance']=='non'?'checked':''; ?>>
                        </b></span>  NON</span></td>
                      </tr>
                    <tr>
                      <td><table border="1" cellpadding="0" cellspacing="0" class="monlisting">
                        <tr>
                          <td  bgcolor="#CCCCCC">&eacute;OTP :</td><td><input name="eotp" type="text"  class="noircalibri10" id="eotp" value="<?php echo htmlspecialchars($row_rs_mission['eotp']) ?>" size="30" maxlength="100"></td>
                        </tr>
                        <tr>
                          <td bgcolor="#CCCCCC">Centre de co&ucirc;t :</td><td><input name="centrecout" type="text"  class="noircalibri10" id="centrecout" value="<?php echo htmlspecialchars($row_rs_mission['centrecout']) ?>" size="30" maxlength="100"> </td>
                          </tr>
                        </table></td>
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
                        <table border="0" cellspacing="2" cellpadding="0">
                          <tr>
                            <td valign="top"><span class="bleugrascalibri10">Note :&nbsp;</span><br>
                              <span class="bleucalibri9">(</span><span id="note#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_mission['note']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                            <td><textarea name="note" cols="80" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'note#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_mission['note']; ?></textarea></td>
                            <td><img src="images/espaceur.gif" width="20"></td>
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
  <!--</div>
 --></td>
 	</tr>
  <tr>
    <td><!-- ligne dessous OM-->&nbsp;</td>
  </tr>
  <tr>
    <td></td>
  </tr>
  <tr>
  	<td>
    	<div id='autorisation_congres' class="<?php echo $row_rs_mission['avecautorisationcongres']==''?'cache':'affiche'?> ">
    	<table width="100%">
        <tr>
          <td bgcolor="#EEEEEE" class="noirgrascalibri11" align="center"><a name='autorisation'></a>
            <img src="images/b_fleche_bas.png" onClick="if(confirm('Remplacer le contenu des champs ?')){copie_champs_om2autorisation()}" id="sprytrigger_info_copie_autorisation_congres">
            <img src="images/espaceur.gif" width="20">
          <div class="tooltipContent_cadre" id="info_copie_autorisation_congres">
            <span class="noircalibri10">
						Recopie les champs concern&eacute;s de la demande d&rsquo;OM, en &eacute;crasant &eacute;ventuellement<br>
            les contenus des champs de l&rsquo;autorisation d&rsquo;absence.
            </span>
          </div>
          <script type="text/javascript">
            var sprytooltip_info_copie_autorisation_congres = new Spry.Widget.Tooltip("info_copie_autorisation_congres", "#sprytrigger_info_copie_autorisation_congres", {offsetX:-200, offsetY:20, closeOnTooltipLeave:true});
          </script>
            AUTORISATION D'ABSENCE en France ou &agrave; l'&eacute;tranger
          </td>
        </tr>
        <tr>
          <td align="right"><?php 
              if($codemission!='')
              { $url="detailmission_autorisation_congres.php?codemission=".$codemission;?>
                  <img src="images/espaceur.gif" width="50" height="1">
                    <a href="<?php echo $url ?>" target="_new" ><img class="icon" width="16" height="16" src="images/b_imprimer.png">&nbsp;Impression de la demande d&rsquo;autorisation
                    </a>&nbsp;<span class="noircalibri9">(Enregistrer avant d&rsquo;imprimer)</span>
              <?php 
              }?>
          </td>
        </tr>
        <tr>
          <td bgcolor="#EEEEEE" class="noirgrascalibri11">- I Cadre &agrave; remplir par l&rsquo;agent</td>
        </tr>
        <tr>
          <td>
            <table class="table_cadre_arrondi" width="100%">
              <tr>
                <td>
                  <table>
                    <tr>
                      <td valign="top" nowrap class="bleugrascalibri10">Composante d&rsquo;affectation
                      </td>
                      <td><input name="composanteenseignement" type="text" class="noircalibri10" id="composanteenseignement" value="<?php echo htmlspecialchars($row_rs_mission['composanteenseignement']) ?>" size="50" maxlength="100">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td valign="top" nowrap class="bleugrascalibri10">Grade
                      </td>
                      <td>
                        <input name="gradecongres" type="text" class="noircalibri10" id="gradecongres" value="<?php echo htmlspecialchars($row_rs_mission['gradecongres']) ?>">
                      </td>
                      <td valign="top" nowrap class="bleugrascalibri10">Emploi
                      </td>
                      <td><input name="emploicongres" type="text" class="noircalibri10" id="emploicongres" value="<?php echo htmlspecialchars($row_rs_mission['emploicongres']) ?>" size="50" maxlength="100">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td>
                        <table>
                          <tr>
                            <td valign="top" nowrap class="bleugrascalibri10">Intitul&eacute; de la manifestation
                            </td>
                            <td><input name="intitulecongres" type="text" class="noircalibri10" id="intitulecongres" value="<?php echo htmlspecialchars($row_rs_mission['intitulecongres']) ?>" size="50" maxlength="200">
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
                  <table>
                    <tr>
                      <td>
                        <table>
                          <tr>
                            <td valign="top" nowrap class="bleugrascalibri10">Devant se dérouler du
                            </td>
                            <td>
                              <input name="datedeb_congres_jj" type="text"  class="noircalibri10" id="datedeb_congres_jj" value="<?php echo substr($row_rs_mission['datedeb_congres'],8,2); ?>" size="2" maxlength="2">
                              <input name="datedeb_congres_mm" type="text"  class="noircalibri10" id="datedeb_congres_mm" value="<?php echo substr($row_rs_mission['datedeb_congres'],5,2); ?>" size="2" maxlength="2">
                              <input name="datedeb_congres_aaaa" type="text"  class="noircalibri10" id="datedeb_congres_aaaa" value="<?php echo substr($row_rs_mission['datedeb_congres'],0,4); ?>" size="4" maxlength="4">
                            </td>
                            <td valign="top" nowrap class="bleugrascalibri10">au
                            </td>
                            <td>
                              <input name="datefin_congres_jj" type="text"  class="noircalibri10" id="datefin_congres_jj" value="<?php echo substr($row_rs_mission['datefin_congres'],8,2); ?>" size="2" maxlength="2">
                              <input name="datefin_congres_mm" type="text"  class="noircalibri10" id="datefin_congres_mm" value="<?php echo substr($row_rs_mission['datefin_congres'],5,2); ?>" size="2" maxlength="2">
                              <input name="datefin_congres_aaaa" type="text"  class="noircalibri10" id="datefin_congres_aaaa" value="<?php echo substr($row_rs_mission['datefin_congres'],0,4); ?>" size="4" maxlength="4">
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
                  <table>
                    <tr>
                      <td valign="top" nowrap class="bleugrascalibri10">&agrave; (ville)
                      </td>
                      <td><input name="villecongres" type="text"  class="noircalibri10" id="congresville" value="<?php echo htmlspecialchars($row_rs_mission['villecongres']) ?>" size="50" maxlength="50">
                      </td>
                      <td valign="top" nowrap class="bleugrascalibri10">Pays (si &agrave; l'&eacute;tranger)
                      </td>
                      <td>
                        <input name="payscongres" type="text"  class="noircalibri10" id="payscongres" value="<?php echo htmlspecialchars($row_rs_mission['payscongres']) ?>" size="50" maxlength="50">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td>
                        <table>
                          <tr>
                            <td valign="top" nowrap class="bleugrascalibri10">Organisateur de la manifestation
                            </td>
                            <td><input name="organisateurcongres" type="text"  class="noircalibri10" id="organisateurcongres" value="<?php echo htmlspecialchars($row_rs_mission['organisateurcongres']) ?>" size="100" maxlength="100">
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
                  <table>
                    <tr>
                      <td valign="top" nowrap class="bleugrascalibri10">D&eacute;part (date)
                      </td>
                      <td><input name="datedepartcongres_jj" type="text"  class="noircalibri10" id="datedepartcongres_jj" value="<?php echo substr($row_rs_mission['datedepartcongres'],8,2); ?>" size="2" maxlength="2">
                        <input name="datedepartcongres_mm" type="text"  class="noircalibri10" id="datedepartcongres_mm" value="<?php echo substr($row_rs_mission['datedepartcongres'],5,2); ?>" size="2" maxlength="2">
                        <input name="datedepartcongres_aaaa" type="text"  class="noircalibri10" id="datedepartcongres_aaaa" value="<?php echo substr($row_rs_mission['datedepartcongres'],0,4); ?>" size="4" maxlength="4">
                      </td>
                      <td><input type="text" class="noircalibri10" name="heuredepartcongres_hh" id="heuredepartcongres_hh" value="<?php echo substr($row_rs_mission['heuredepartcongres'],0,2); ?>" size="2" maxlength="2">
                        <span class="bleucalibri10">h</span> <input type="text" class="noircalibri10" name="heuredepartcongres_mn" id="heuredepartcongres_mn" value="<?php echo substr($row_rs_mission['heuredepartcongres'],3,2); ?>" size="2" maxlength="2">
                      </td>
                      <td valign="top" nowrap class="bleugrascalibri10">Arriv&eacute;e
                      </td>
                      <td>
                        <input name="datearriveecongres_jj" type="text"  class="noircalibri10" id="datearriveecongres_jj" value="<?php echo substr($row_rs_mission['datearriveecongres'],8,2); ?>" size="2" maxlength="2">
                        <input name="datearriveecongres_mm" type="text"  class="noircalibri10" id="datearriveecongres_mm" value="<?php echo substr($row_rs_mission['datearriveecongres'],5,2); ?>" size="2" maxlength="2">
                        <input name="datearriveecongres_aaaa" type="text"  class="noircalibri10" id="datearriveecongres_aaaa" value="<?php echo substr($row_rs_mission['datearriveecongres'],0,4); ?>" size="4" maxlength="4">
                      </td>
                      <td><input type="text" class="noircalibri10" name="heurearriveecongres_hh" id="heurearriveecongres_hh" value="<?php echo substr($row_rs_mission['heurearriveecongres'],0,2); ?>" size="2" maxlength="2">
                        <span class="bleucalibri10">h</span> <input type="text" class="noircalibri10" name="heurearriveecongres_mn" id="heurearriveecongres_mn" value="<?php echo substr($row_rs_mission['heurearriveecongres'],3,2); ?>" size="2" maxlength="2">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td valign="top" nowrap class="bleugrascalibri10">H&ocirc;tel
                      </td>
                      <td><input name="hotelcongres" type="text"  class="noircalibri10" id="hotelcongres" value="<?php echo htmlspecialchars($row_rs_mission['hotelcongres']) ?>" size="100" maxlength="100">
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
          <td bgcolor="#EEEEEE" class="noirgrascalibri11">- II Modalit&eacute;s financi&egrave;res du d&eacute;placement arr&ecirc;t&eacute;es par l&rsquo;ordonnateur</td>
        </tr>
        <tr>
          <td>
            <table width="100%" class="table_cadre_arrondi">
              <tr>
                <td align="center" class="noirgrascalibri11">Adresse budg&eacute;taire</td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td class="bleugrascalibri10">C.C.
                      </td>
                      <td><input name="centrecoutcongres" type="text"  class="noircalibri10" id="centrecoutcongres" value="<?php echo htmlspecialchars($row_rs_mission['centrecoutcongres']) ?>" size="30" maxlength="100">
                      </td>
                      <td class="bleugrascalibri10">EOTP
                      </td>
                      <td><input name="eotpcongres" type="text"  class="noircalibri10" id="eotpcongres" value="<?php echo htmlspecialchars($row_rs_mission['eotpcongres']) ?>" size="30" maxlength="100">
                      </td>
                      <td class="bleugrascalibri10">Dest.
                      </td>
                      <td><input name="destinationcongres" type="text"  class="noircalibri10" id="destinationcongres" value="<?php echo htmlspecialchars($row_rs_mission['destinationcongres']) ?>" size="20" maxlength="100">
                      </td>
                      <td class="bleugrascalibri10">Rub.
                      </td>
                      <td><input name="rubriquecongres" type="text"  class="noircalibri10" id="rubriquecongres" value="<?php echo $row_rs_mission['rubriquecongres'] ?>" size="6" maxlength="6">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td><input type="checkbox" name="sanspriseenchargecongres" <?php echo ($row_rs_mission['sanspriseenchargecongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Aucune prise en charge autoris&eacute;e des frais inh&eacute;rents au d&eacute;placement
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td>
                        <input type="checkbox" name="avecpriseenchargetotalecongres" <?php echo ($row_rs_mission['avecpriseenchargetotalecongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Prise en charge de la facture de l&rsquo;organisateur du colloque, couvrant tous les d&eacute;bours pas de remboursement &agrave; l&rsquo;agent 
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td><input type="checkbox" name="avecpriseenchargepartiellecongres" <?php echo ($row_rs_mission['avecpriseenchargepartiellecongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Prise en charge de la facture de l&rsquo;organisateur du colloque ne couvrant pas tous les d&eacute;bours<br>autoris&eacute;e sur portail march&eacute;, &agrave; d&eacute;faut remboursement &agrave; l&rsquo;agent selon bar&egrave;me r&eacute;glementaire des frais suivants :
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td class="bleugrascalibri10">TRANSPORT
                      </td>
                      <td class="bleugrascalibri10">SNCF
                      </td>
                      <td><input type="checkbox" name="avectraincongres" <?php echo ($row_rs_mission['avectraincongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">avion
                      </td>
                      <td><input type="checkbox" name="avecavioncongres" <?php echo ($row_rs_mission['avecavioncongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">V&eacute;hicule pers
                      </td>
                      <td><input type="checkbox" name="avecvehiculeperscongres" <?php echo ($row_rs_mission['avecvehiculeperscongres']=="oui"?'checked':''); ?>>
                      </td>
                       <td class="bleugrascalibri10">H&eacute;bergement
                      </td>
                      <td><input type="checkbox" name="avechebergementcongres" <?php echo ($row_rs_mission['avechebergementcongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Repas
                      </td>
                      <td><input type="checkbox" name="avecrepascongres" <?php echo ($row_rs_mission['avecrepascongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Autre
                      </td>
                      <td><input type="checkbox" name="avecautrecongres" <?php echo ($row_rs_mission['avecautrecongres']=="oui"?'checked':''); ?>>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                      <td><input type="checkbox" name="avecpriseenchargeautre_ulcongres" <?php echo ($row_rs_mission['avecpriseenchargeautre_ulcongres']=="oui"?'checked':''); ?>>
                      </td>
                      <td class="bleugrascalibri10">Prise en charge par un organisme autre que l&rsquo;UL (nom)
                      </td>
                      <td><input name="organismepriseenchargeautrecongres" type="text"  class="noircalibri10" id="organismepriseenchargeautrecongres" value="<?php echo htmlspecialchars($row_rs_mission['organismepriseenchargeautrecongres']) ?>" size="80" maxlength="100">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                    <tr>
                			<td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer">
                      </td>
                      <td>
											<?php 
											if($row_rs_mission['centrecoutcongres']=='')// on ne peut creer la commande que si centre de cout='' (non positionné par creation de commande)
											{?> <input name="submit_creer_commande_congres" type="submit" class="noircalibri10" id="submit_creer_commande_congres" value="Cr&eacute;er la commande de congr&egrave;s">
											<?php 
											}?>
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
</form>
</body>
</html>
    <?php

if(isset($rs_miss_corpscongres)) mysql_free_result($rs_miss_corpscongres);
if(isset($rs_miss_abonnementtrain))mysql_free_result($rs_miss_abonnementtrain);
if(isset($rs_miss_catmissionnaire))mysql_free_result($rs_miss_catmissionnaire);
if(isset($rs_miss_classetrain))mysql_free_result($rs_miss_classetrain);
if(isset($rs_miss_lieudepart))mysql_free_result($rs_miss_lieudepart);
if(isset($rs_miss_puissfiscale))mysql_free_result($rs_miss_puissfiscale);
if(isset($rs_agent))mysql_free_result($rs_agent);
if(isset($rs_theme))mysql_free_result($rs_theme);
if(isset($rs_secrsite))mysql_free_result($rs_secrsite);
if(isset($rs_mission))mysql_free_result($rs_mission);
if(isset($rs_missionetape))mysql_free_result($rs_missionetape);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
