<?php require_once('_const_fonc.php'); ?>
<?php
/* if($admin_bd) 
{ foreach($_GET as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}
}*/
	
$nbdirmax=3;//nb d'encadrants max.
$erreur="";
$warning="";
$erreur_envoimail="";
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$selected="";$checked="";// utilise dans les listes select et checked
$themechecked="";
$nonfintheme=true;
$codestatutsujet="";
$first=false;//utilise dans la construction d'une chaine de caracteres : le dernier d'une liste n'es pas suivi d'une , ou d'un espace dans un while
$liste_champs="";$liste_val="";//liste des champs ou des valeurs construite pour un insert ou update
$updateSQL = ""; //ordre update
$query_individu="";//ordre sql
$rs_fields_sujet=null;//result set des noms de champs issus de show columns
$rs_sujet=null;$rs_structureindividu=null;$rs_typesujet=null;$row_rs=null;$rs_dir=null;$rs_individu=null;$rs_theme=null;$rs_sujettheme=null;//result set
// iniatilisation a array() et pas null car necessaire dans certaines opérations comme array_intersect
$row_rs_sujet=array();$tab_usertheme=array();$row_rs_typesujet=array();$row_rs_individu=array();$row_rs_theme=array();//ligne de resultset
$row_rs_dir=array();$tab_sujettheme=array();
$form_sujet='form_sujet';

$numordre="";//numero d'ordre d'un champ numordre d'une table
$postkey="";$postval="";//pour parcours de tableau : key et val
// le sujet est a affecter si on est passé par 'creer' un sujet avec un choix dans liste deroulante des etudiants ou si on est en modification
// d'un sujet et qu'un etudiant a ete choisi : en fait $sujet_a_affecter=($codeetudiantsejour!='')
$sujet_a_affecter=false;
$sujetaffecte=false;
$tab_champs_date=array( 'datedeb_sujet' =>  array("lib" => "Date d&eacute;but","jj" => "","mm" => "","aaaa" => ""), 'datefin_sujet' =>  array("lib" => "Date fin","jj" => "","mm" => "","aaaa" => "") );

$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estrespthemedusujet=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent, $estrespthemedusujet);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codesujet=isset($_GET['codesujet'])?$_GET['codesujet']:(isset($_POST['codesujet'])?$_POST['codesujet']:"");
$codetypesujet=isset($_GET['codetypesujet'])?$_GET['codetypesujet']:(isset($_POST['codetypesujet'])?$_POST['codetypesujet']:"");
$sujet_ancre=isset($_GET['sujet_ancre'])?$_GET['sujet_ancre']:(isset($_POST['sujet_ancre'])?$_POST['sujet_ancre']:"");
// une creation peut etre faite pour un etudiant choisi
$codeetudiantsejour=isset($_GET['codeetudiantsejour'])?$_GET['codeetudiantsejour']:(isset($_POST['codeetudiantsejour'])?$_POST['codeetudiantsejour']:"");
$sujet_a_affecter=(isset($_GET['sujet_a_affecter'])?$_GET['sujet_a_affecter']:(isset($_POST['sujet_a_affecter'])?$_POST['sujet_a_affecter']:"non"))=="oui";
$sujetaffecte=(isset($_GET['sujetaffecte'])?$_GET['sujetaffecte']:(isset($_POST['sujetaffecte'])?$_POST['sujetaffecte']:"non"))=="oui";
// 20170322
$appel_de_individu=isset($_GET['appel_de_individu'])?$_GET['appel_de_individu']:(isset($_POST['appel_de_individu'])?$_POST['appel_de_individu']:"");
$etat_individu=isset($_GET['etat_individu'])?$_GET['etat_individu']:(isset($_POST['etat_individu'])?$_POST['etat_individu']:"");
$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");
// 20170322 fin
// protection contre une erreur qui modifierait l'enreg. ''
if($action!='creer' && $codesujet=='')
{	$erreur.="Tentative de modification de sujet sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de sujet sans n&deg; '.$msg);
}
//PG 20151117

$codeetudiant='';
$numsejour='';
if($codeetudiantsejour!='')
{ $tab=explode('#',$codeetudiantsejour);
	if(count($tab)>1)
	{ $codeetudiant=$tab[0];
		$numsejour=$tab[1];
	}
	else
	{ $tab=explode('.',$codeetudiantsejour);
		if(count($tab)>1)
		{ $codeetudiant=$tab[0];
			$numsejour=$tab[1];
		}
	}
}


if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "edit_sujet")) 
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date);
	$erreur.=controle_form_sujet($_POST,$tab_controle_et_format);
	//directeurs
	foreach($_POST as $postkey=>$codedir)
	{ if(strpos($postkey,'codedir#')!==false && $codedir!='' )
		{ $numordre=substr($postkey,strlen('codedir#'));
			if(!in_array($codedir,$row_rs_dir))//enleve tout doublon de directeur choisi par erreur
			{ $row_rs_dir[$numordre]=$codedir;
			}
		}
	}
	// affectation themes
	$themechecked=false;
	foreach($_POST as $postkey=>$postval)
	{ if(strpos($postkey,'codetheme#')!==false)
		{ $themechecked=true;
			$tab_sujettheme[substr($postkey,strlen('codetheme#'))]=substr($postkey,strlen('codetheme#'));
		}
	}

	if(!$themechecked)
	{ $erreur.='<br>'.'Aucun(e) '.$GLOBALS['libcourt_theme_fr'].' s&eacute;lectionn&eacute;(e) !<br>'; 
	}
	else if($sujet_a_affecter || $sujetaffecte)
	{	$au_moins_un_theme_commun=false;
		$rs=mysql_query("select codetheme, libcourt_fr ".
										" from individutheme,structure where codeindividu=".GetSQLValueString($codeetudiant,"text").
										" and individutheme.numsejour=".GetSQLValueString($numsejour,"text").
										" and individutheme.codetheme=structure.codestructure".
										" order by codetheme") or die(mysql_error());
		$listetheme_etudiant='';
		while($row_rs=mysql_fetch_assoc($rs))
		{ $listetheme_etudiant.=$row_rs['libcourt_fr'].' ';
			if(isset($tab_sujettheme[$row_rs['codetheme']]))
			{ $au_moins_un_theme_commun=true;
			}
		}
		if(!$au_moins_un_theme_commun)
		{ $erreur.='<br>'.'Aucun(e) '.$GLOBALS['libcourt_theme_fr'].' en commun avec l&rsquo;&eacute;tudiant : '.$listetheme_etudiant.'!<br>'; 
		}
	}

//$erreur='erreur forcée';
	if($erreur=='')
	{ $affiche_succes=true;
		if($action=="creer")//creation
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='sujet'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codesujet=$row_seq_number['currentnumber'];
			$codesujet=str_pad((string)((int)$codesujet+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codesujet, "text")." where nomtable='sujet'") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields_sujet = mysql_query('SHOW COLUMNS FROM sujet');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields_sujet = mysql_fetch_assoc($rs_fields_sujet)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields_sujet['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields_sujet['Field']=='codesujet')
				{ $liste_val.=GetSQLValueString($codesujet, "text");
				}
				else if($row_rs_fields_sujet['Field']=='codecreateur')
				{ $liste_val.=GetSQLValueString($codeuser, "text");
				}
				else if($row_rs_fields_sujet['Field']=='codetypesujet')
				{ $liste_val.=GetSQLValueString($codetypesujet, "text");
				}
				// modif 20151013
				else if($codetypesujet=='02' && $row_rs_fields_sujet['Field']=='codetypestage')
				{ $liste_val.=GetSQLValueString('01', "text");
				}
				// fin modif 20151013
				else if($row_rs_fields_sujet['Field']=='afficher_sujet_web')
				{ $liste_val.="'oui'";
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into sujet (".$liste_champs.") values (".$liste_val.")";
			mysql_query($updateSQL) or  die(mysql_error());
			
			$action="modifier";
		}//fin if creation
		$updateSQL = "UPDATE sujet SET ";
		$rs_fields_sujet = mysql_query('SHOW COLUMNS FROM sujet');
		while($row_rs_fields_sujet = mysql_fetch_assoc($rs_fields_sujet)) 
		{ if(isset($_POST[$row_rs_fields_sujet['Field']]) || (isset($_POST[$row_rs_fields_sujet['Field'].'_jj']) && isset($_POST[$row_rs_fields_sujet['Field'].'_mm']) && isset($_POST[$row_rs_fields_sujet['Field'].'_aaaa'])))
			{ //les donnees codepropsujet codecreateur date_creation ne sont plus jamais modifiees : uniquement en creation en insert
				if(strpos("codesujet codecreateur codetypesujet",$row_rs_fields_sujet['Field'])===false)//pas de mise a jour de ces champs
				{ $updateSQL.=$row_rs_fields_sujet['Field']."=";
					if(in_array($row_rs_fields_sujet['Field'],array("datedeb_sujet","datefin_sujet"))!==false)
					{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$row_rs_fields_sujet['Field'].'_jj'],$_POST[$row_rs_fields_sujet['Field'].'_mm'],$_POST[$row_rs_fields_sujet['Field'].'_aaaa']), "text");
					}
					else
					{ $updateSQL.=GetSQLValueString(trim($_POST[$row_rs_fields_sujet['Field']]), "text");
					}
					$updateSQL.=",";
				}
			}
		}
		$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
		$updateSQL.=" WHERE codesujet=".GetSQLValueString($codesujet, "text");
		mysql_query($updateSQL) or die(mysql_error());
	
		// ----------------------------- affectation directeur(s)
		// suppression des directeurs existants
		mysql_query("delete from sujetdir where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
		// insertion des directeurs
		
		foreach($row_rs_dir as $numordre=>$codedir)
		{ $updateSQL ="INSERT into sujetdir (codesujet,codedir,taux_encadrement,numordre) ".
										" values (".GetSQLValueString($codesujet, "text").",".GetSQLValueString($codedir, "text").",'',".GetSQLValueString($numordre, "text").")";
			mysql_query($updateSQL) or die(mysql_error());
		}
		
		// ----------------------------- affectation theme(s)
		// suppression des affectations existantes
		$updateSQL = "delete from sujettheme where codesujet=".GetSQLValueString($codesujet, "text");
		mysql_query($updateSQL) or die(mysql_error());
		
		foreach($tab_sujettheme as $codetheme)
		{ $updateSQL ="INSERT into sujettheme (codesujet,codetheme) values (".
										GetSQLValueString($codesujet, "text").",".
										GetSQLValueString($codetheme, "text").")";
			mysql_query($updateSQL) or die(mysql_error());
		}
		// 20170322
		if($appel_de_individu=='oui')
		{ $updateSQL="update sujet set codestatutsujet='V' where codesujet=".GetSQLValueString($codesujet, "text");
			mysql_query($updateSQL) or die(mysql_error());
			mysql_query("delete from individusujet where codeindividu=".GetSQLValueString($codeetudiant, "text")." and numsejour=".GetSQLValueString($numsejour, "text")." and codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());			
			mysql_query("insert into individusujet (codeindividu,numsejour,codesujet) values(".GetSQLValueString($codeetudiant, "text").",".GetSQLValueString($numsejour, "text").",".GetSQLValueString($codesujet, "text").")") or die(mysql_error());;

		}
		// 20170322 fin
		// demande de validation ou validation
		else if(isset($_POST['submit_valider']))
		{ // affectation sujet a l'etudiant
			if($GLOBALS['avecvisathemesujet'])
			{ // ---------------------------- modification du statut de la proposition si demande de validation et envoi de message aux acteurs concernés
				//l'individu connecte (login) est-il responsable de l'un des themes de la proposition de sujet?
				$rs_structureindividu = mysql_query("SELECT * from sujettheme,structure,structureindividu ".
																						" where codesujet=".GetSQLValueString($codesujet, "text").
																						" and sujettheme.codetheme=structureindividu.codestructure".
																						" and structureindividu.codeindividu=".GetSQLValueString($codeuser, "text").
																						" and structureindividu.estresp='oui'") or die(mysql_error());
				// Changement du statut de la proposition selon les cas
				/*										n'est pas resptheme 	est resptheme 
					submit_enregistrer    	''											'E'
					submit_valider					'E'											'V'
				
				*/
				if(mysql_fetch_assoc($rs_structureindividu)) 
				{ $codestatutsujet="V";
				}
				else
				{ $codestatutsujet="E";
				}
			}
			else//pas de visa theme a apposer
			{ $codestatutsujet="V";
			}
			$updateSQL="update sujet set codestatutsujet=".GetSQLValueString($codestatutsujet, "text")." where codesujet=".GetSQLValueString($codesujet, "text");
			mysql_query($updateSQL) or die(mysql_error());

			if($sujet_a_affecter || $sujetaffecte)
			{ mysql_query("delete from individusujet where codeindividu=".GetSQLValueString($codeetudiant, "text")." and numsejour=".GetSQLValueString($numsejour, "text")." and codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());			
				mysql_query("insert into individusujet (codeindividu,numsejour,codesujet) values(".GetSQLValueString($codeetudiant, "text").",".GetSQLValueString($numsejour, "text").",".GetSQLValueString($codesujet, "text").")") or die(mysql_error());;
			}
			// dernier visa (role) apposé au dossier etudiant
			if($codestatutsujet=='E')
			{ $coderole_a_prevenir='theme';
			}
			else if($codestatutsujet=='V')
			{ $coderole_a_prevenir='srh';
			}
			// dernier visa (role) apposé
			$coderole_a_prevenir='';
			$row_rs_individustatutvisa=max_individustatutvisa($codeetudiant,$numsejour);
			//if($row_rs_individustatutvisa['coderole']!='')
			{ if($row_rs_individustatutvisa['coderole']=='referent' || $row_rs_individustatutvisa['coderole']=='srhue')
				{ $coderole_a_prevenir='theme';
				}
				/* else if($row_rs_individustatutvisa['coderole']=='theme')
				{ $coderole_a_prevenir='srh';
				} */
			}
			//$erreur_envoimail='erreur forc&eacute;e';
			$erreur_envoimail=mail_validation_sujet($codesujet,$tab_infouser,$coderole_a_prevenir);
			if($erreur_envoimail=="" || !$GLOBALS['mode_avec_envoi_mail'])
			{ http_redirect("gestionsujets.php");
			}
			/*else
			{ mysql_query("update tracelogin set erreur=".GetSQLValueString($erreur_envoimail, "text").",typeerreur='envoi mail' where sessionid=".GetSQLValueString(session_id(), "text")." and heuredeconnexion IS NULL") or die(mysql_error()); 
			}*/
		}
	}// Fin de traitement des données si pas d'erreur
}
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations de la proposition (un enreg. vide dans sujet pour "creer")
$rs_sujet=mysql_query("select sujet.*,libstatutsujet, typesujet.libcourt_fr as libtypesujet, libstatutsujet, individu.nom, individu.prenom ".
											" from sujet, statutsujet, typesujet, individu ".
											" where sujet.codesujet=".GetSQLValueString($codesujet,"text").
											" and sujet.codestatutsujet=statutsujet.codestatutsujet".
											" and sujet.codecreateur=individu.codeindividu".
											" and sujet.codetypesujet=typesujet.codetypesujet") or die(mysql_error());
$row_rs_sujet=mysql_fetch_assoc($rs_sujet);

if($action=="creer")
{ // theme du sujet = ceux du codeuser par defaut en creation
	/* $rs=mysql_query( "select codetheme from individutheme ".
									 " where codeindividu=".GetSQLValueString($codeuser,"text").
									 " order by codetheme") or die(mysql_error()); */
	$rs=mysql_query( "select codetheme from individutheme ".
									 " where codeindividu=".GetSQLValueString($codeuser,"text").
									 " and ".periodeencours('datedeb_theme','datefin_theme').
									 " order by codetheme") or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_sujettheme[$row_rs['codetheme']]=$row_rs['codetheme'];
	}
	// $codeuser = directeur sujet par defaut
	$row_rs_dir['1']=$codeuser;
	// date debut et fin d'une these par defaut
	if($codetypesujet=='03')//these
	{ $row_rs_sujet['datedeb_sujet']=date("Y").'/10/01';
		$row_rs_sujet['datefin_sujet']=((int)date("Y")+3).'/09/30';
	}
}
else//$action=="modifier"
{ $codetypesujet=$row_rs_sujet['codetypesujet'];
	$rs_etudiant=mysql_query(	"select codeindividu,numsejour from individusujet ".
														" where  individusujet.codesujet=".GetSQLValueString($codesujet,"text"));
	if($row_rs_etudiant=mysql_fetch_assoc($rs_etudiant))
	{ $sujetaffecte=true;
		$codeetudiant=$row_rs_etudiant['codeindividu'];
		$numsejour=$row_rs_etudiant['numsejour'];
	}
}

// Libelle de codetypesujet
$rs_typesujet=mysql_query("select libcourt_fr as libtypesujet from typesujet where codetypesujet=".GetSQLValueString($codetypesujet,"text"));
$row_rs_typesujet=mysql_fetch_assoc($rs_typesujet);

// Tous les themes a afficher
//$rs_theme=mysql_query("select codestructure as codetheme,libcourt_fr from structure where codestructure<>'' and esttheme='oui' order by codestructure") or die(mysql_error());
$query_rs_theme = "select codestructure as codetheme,libcourt_fr".
									" from structure where codestructure<>'' and esttheme='oui'".
									"	and  ".($action=='creer'?periodeencours('date_deb','date_fin'):intersectionperiodes('structure.date_deb','structure.date_fin','"'.$row_rs_sujet['datedeb_sujet'].'"','"'.$row_rs_sujet['datefin_sujet'].'"')).
									" order by codestructure";
$rs_theme=mysql_query($query_rs_theme);

// themes de tous les encadrants pour verifier qu'ils figurent tous dans ceux du sujet
$tab_individutheme=array();
$query_rs_individutheme = "select distinct individutheme.codeindividu,codetheme".
													" from individutheme,individusejour,structure".
													" where individutheme.codeindividu=individusejour.codeindividu and individutheme.numsejour=individusejour.numsejour".
													" and individutheme.codetheme=structure.codestructure and codestructure<>'' and esttheme='oui'".
													" and ".($action=='creer'?periodeencours('structure.date_deb','structure.date_fin'):intersectionperiodes('structure.date_deb','structure.date_fin','"'.$row_rs_sujet['datedeb_sujet'].'"','"'.$row_rs_sujet['datefin_sujet'].'"')).
													" and ".($action=='creer'?periodeencours('datedeb_sejour','datefin_sejour'):intersectionperiodes('datedeb_sejour','datefin_sejour','"'.$row_rs_sujet['datedeb_sujet'].'"','"'.$row_rs_sujet['datefin_sujet'].'"')).
													" order by individutheme.codeindividu,codetheme";
$rs_individutheme=mysql_query($query_rs_individutheme);
while($row_rs_individutheme=mysql_fetch_assoc($rs_individutheme))
{ $tab_individutheme[$row_rs_individutheme['codeindividu']][$row_rs_individutheme['codetheme']]=$row_rs_individutheme['codetheme'];
}

if($erreur=='')
{// Liste des codedir
	$rs=mysql_query(" select numordre,codedir from sujetdir".
									" where sujetdir.codesujet=".GetSQLValueString($codesujet,"text").
									" order by numordre") or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $row_rs_dir[$row_rs['numordre']]=$row_rs['codedir'];
	}
	// Liste des codetheme du sujet 
	$rs=mysql_query("select codetheme ".
									" from sujettheme where codesujet=".GetSQLValueString($codesujet,"text").
									" order by codetheme") or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_sujettheme[$row_rs['codetheme']]=$row_rs['codetheme'];
	}
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ $rs_fields_sujet = mysql_query('SHOW COLUMNS FROM sujet');
	while($row_rs_fields_sujet = mysql_fetch_assoc($rs_fields_sujet)) 
	{ $Field=$row_rs_fields_sujet['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_sujet[$Field]=$_POST[$Field];
		}
		if(in_array($Field,array("datedeb_sujet","datefin_sujet"))!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_sujet[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	}
}

if($sujet_a_affecter || $sujetaffecte)
{ // 20170322 recupere codereferent
	$query_rs="select nom,prenom,datedeb_sejour,datefin_sejour,typestage.codetypestage,libcourttypestage as libtypestage,codereferent from individu,individusejour,typestage ".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codeindividu=".GetSQLValueString($codeetudiant,"text").
														" and individusejour.numsejour=".GetSQLValueString($numsejour,"text").
														" and individusejour.codetypestage=typestage.codetypestage";
	$rs_etudiant=mysql_query($query_rs);
	$row_rs_etudiant=mysql_fetch_assoc($rs_etudiant);
	$row_rs_sujet['datedeb_sujet']=$row_rs_etudiant['datedeb_sejour'];
	$row_rs_sujet['datefin_sujet']=$row_rs_etudiant['datefin_sejour'];
	if($row_rs_etudiant['codetypestage']!='')
	{ $row_rs_sujet['codetypesujet']="02";//stage
	}
	$row_rs_sujet['codetypestage']=$row_rs_etudiant['codetypestage'];

	// Liste des codetheme de l'etudiant : vide le tableau renseigne ci-dessus
	if($action=='creer')
	{ array_splice($tab_sujettheme,0,count($tab_sujettheme));
		$rs=mysql_query("select codetheme ".
										" from individutheme where codeindividu=".GetSQLValueString($codeetudiant,"text").
										" and individutheme.numsejour=".GetSQLValueString($numsejour,"text").
										" order by codetheme") or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_sujettheme[$row_rs['codetheme']]=$row_rs['codetheme'];
		}
		// 20170322
		if($appel_de_individu=='oui')
		{ $row_rs_dir=array();
			$row_rs_dir['1']=$row_rs_etudiant['codereferent'];
		}
		// 20170322 fin
	}
}
//$codeuser est-il responsable de l'un des themes du sujet ?
//Les themes du sujet sont dans $tab_sujettheme : soit par select soit par affectation des valeurs du $POST en cas d'erreur
$estrespthemedusujet=false;
$rs=mysql_query("select structureindividu.codestructure as codetheme from structureindividu,structure " .
								" where structureindividu.codestructure=structure.codestructure".
								" and structureindividu.codeindividu=".GetSQLValueString($codeuser,"text").
								" and structureindividu.estresp='oui' and structure.esttheme='oui'") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))																
{ $tab_usertheme[$row_rs['codetheme']]=$row_rs['codetheme'];
}
$estrespthemedusujet=(count(array_intersect_key($tab_usertheme,$tab_sujettheme))!=0);


$tab_domainescientifique=array();
$query_rs =	"SELECT codedomainescientifique,liblongdomainescientifique as libdomainescientifique".
						" FROM sujet_domainescientifique ".
						" order by numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_domainescientifique[$row_rs['codedomainescientifique']]=$row_rs;
}
$tab_disciplinescientifique=array();
$query_rs =	"SELECT codedisciplinescientifique,codedomainescientifique,liblongdisciplinescientifique as libdisciplinescientifique".
						" FROM sujet_disciplinescientifique". //where codedisciplinescientifique<>''
						" order by numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_disciplinescientifique[$row_rs['codedisciplinescientifique']]=$row_rs;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des sujets <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
	//  themes de tous les encadrants pour verifier qu'ils figurent tous dans ceux du sujet
	var tab_individutheme=new Array();
	<?php 
	foreach($tab_individutheme as $un_codeindividu=>$tab_theme)
	{ $numtheme=0 ?>
		tab_theme=new Array();
		<?php 
		foreach($tab_theme as $un_codetheme)
		{?>tab_theme[<?php echo $numtheme ?>]="<?php echo $un_codetheme ?>"
			<?php 
			$numtheme++; 
		}?>
		tab_individutheme["<?php echo $un_codeindividu ?>"]=tab_theme;
	<?php 
	}?>
	function verif_themes_sujet_dir()
	{ var frm=document.forms["<?php echo $form_sujet ?>"];
		txt_retour="";
		for(numdir=1;numdir<=<?php echo $nbdirmax ?>;numdir++)
		{ if(frm.elements['codedir#'+new String(numdir)] && frm.elements['codedir#'+new String(numdir)].value!='')
			{ codedir=frm.elements['codedir#'+new String(numdir)].value;
				tab_theme_dir=tab_individutheme[codedir];
				i=0;
				manque_theme=false;
				while(i<tab_theme_dir.length && !manque_theme)
				{ if(frm.elements['codetheme#'+tab_theme_dir[i]] && !frm.elements['codetheme#'+tab_theme_dir[i]].checked)
					{ manque_theme=true;
						txt_retour="Pour information : il manque au moins un <?php echo $GLOBALS['libcourt_theme_fr'] ?> d\'encadrant"; 
					}
					i++;
				}
			}
		}
		return txt_retour;
	}
	
	var tab_domainescientifique=new Array();
	var tab_disciplinescientifique=new Array();
	<?php 
	$nb=0;
	foreach($tab_domainescientifique as $codedomainescientifique=>$row_rs)
	{?> var o=new Object();
		o["codedomainescientifique"]="<?php echo $row_rs['codedomainescientifique']; ?>";
		o["libdomainescientifique"]="<?php echo js_tab_val($row_rs['libdomainescientifique']) ?>";
		tab_domainescientifique[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	?>
	nbdomainescientifique=<?php echo $nb; ?>;
	<?php 
	$nb=0;
	foreach($tab_disciplinescientifique as $codedisciplinescientifique=>$row_rs)
	{?> 
		var o=new Object();
		o["codedisciplinescientifique"]="<?php echo js_tab_val($row_rs['codedisciplinescientifique']) ?>";
		o["codedomainescientifique"]="<?php echo js_tab_val($row_rs['codedomainescientifique']); ?>";
		o["libdisciplinescientifique"]="<?php echo js_tab_val($row_rs['libdisciplinescientifique']) ?>";
		tab_disciplinescientifique[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	 ?>
	nbdisciplinescientifique=<?php echo $nb; ?>;
	
	function affichedisciplinescientifique(champ)
	{ var frm=document.forms["<?php echo $form_sujet ?>"];
		var valchamp=champ.value;
		var firstdisciplinescientifique=true;
		numordre=champ.name.substr((new String('codedomainescientifique')).length);
		listedisciplinescientifique=frm.elements['codedisciplinescientifique'+new String(numordre)];
		listedisciplinescientifique.options.length=0;
		if(numordre==1)
		{ listedisciplinescientifique.options[0]=new Option("[choix obligatoire]","");
		}
		else
		{ listedisciplinescientifique.options[0]=new Option("","");
		}
		for(i=0;i<nbdisciplinescientifique;i++)
		{ if(tab_disciplinescientifique[i].codedomainescientifique==valchamp)
			{ if(firstdisciplinescientifique)
				{ valdisciplinescientifique=tab_disciplinescientifique[i].codedisciplinescientifique;
					firstdisciplinescientifique=false;
				}
				listedisciplinescientifique.options[listedisciplinescientifique.options.length]=new Option(tab_disciplinescientifique[i].libdisciplinescientifique, tab_disciplinescientifique[i].codedisciplinescientifique);
			}
		}
	}
</script>
</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>
>
  <form name="<?php echo $form_sujet ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="return controle_form_sujet('form_sujet')">
  <input type="hidden" name="action" value="<?php echo $action ?>">
  <input type="hidden" name="codesujet" value="<?php echo $codesujet ?>" >
  <input type="hidden" name="sujet_ancre" value="<?php echo $codesujet ?>" >
  <input type="hidden" name="codetypesujet" value="<?php echo $codetypesujet ?>" >
  <input type="hidden" name="MM_update" value="edit_sujet">
  <?php 
	if($sujet_a_affecter || $sujetaffecte)
  {?> <input type="hidden" name="codeetudiantsejour" value="<?php echo $codeetudiantsejour ?>">
  <?php 
	}
	?> 
  <input type="hidden" name="sujet_a_affecter" value="<?php if($sujet_a_affecter){?>oui<?php }else{?>non<?php }?>">
  <input type="hidden" name="sujetaffecte" value="<?php if($sujetaffecte){?>oui<?php }else{?>non<?php }?>">
  <?php // 20170322
	if($appel_de_individu=='oui')
  {?> 
  	<input type="hidden" name="appel_de_individu" value="<?php echo $appel_de_individu ?>">
  	<input type="hidden" name="etat_individu" value="<?php echo $etat_individu ?>">
		<input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre ?>">	
  <?php 
	}?>
  <table width="60%" border="0" align="center" cellpadding="0" cellspacing="1">
	<?php	
	if($appel_de_individu=='oui')// 20170322
	{	$lienretour="edit_individu.php?action=modifier&codeindividu=".$codeetudiant."&numsejour=".$numsejour."&etat_individu=".$etat_individu."&ind_ancre=".$ind_ancre;
		$texteretour="Retour &agrave; la fiche individu";
	}
	else
	{ if($action=='creer' || !$sujet_a_affecter)
		{ $lienretour="gestionsujets.php?sujet_ancre=".$sujet_ancre;
			$texteretour="Retour &agrave; la gestion des propositions de sujets";
		}
		else
		{ $lienretour="gestionsujets.php?sujet_ancre=".$sujet_ancre;
			$texteretour='<img src="images/b_attention.png" width="15" height="15">Vous devrez quitter ce formulaire en ayant valid&eacute; ce sujet';
		}
	}
	 echo entete_page(array('image'=>'images/b_document.png','titrepage'=>'Proposition de sujet de <span class="mauvegrascalibri10">'.$row_rs_typesujet['libtypesujet'].'</span>',
																'lienretour'=>$lienretour,'texteretour'=>$texteretour,
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
    <tr>
      <td>&nbsp;</td>
    </tr>
		<?php 
    if($erreur_envoimail=="" || !$GLOBALS['mode_avec_envoi_mail'])
    { ?>
      <?php
			if($sujet_a_affecter || $sujetaffecte)
      { ?>
      <tr>
        <td>
        	<span class="bleugrascalibri11">Sujet <?php if($sujet_a_affecter) {?>&agrave; affecter <?php } else {?> affect&eacute; <?php }?> &agrave; </span>
        	<span class="orangegrascalibri11"><?php echo $row_rs_etudiant['prenom'] ?> <?php echo $row_rs_etudiant['nom'] ?></span>
          <?php 
					if($sujet_a_affecter && $appel_de_individu!='oui')
					{?><span class="rougegrascalibri10">&nbsp;&nbsp;(Cette affectation ne sera effective qu&rsquo;apr&egrave;s validation)</span>
        	<?php 
					}?>
        </td>
      </tr>
      <tr>
        <td>
        </td>
      </tr>
      <?php 
			}?>
    <tr>
      <td>
        <table border="0">
          <tr>
            <td>
              <span class="bleucalibri9">Enregistr&eacute; par : </span>
              <span class="mauvegrascalibri9"><?php echo $row_rs_sujet['prenom'] ?> <?php echo $row_rs_sujet['nom'] ?></span>
            </td>
            <td><span class="bleucalibri9">Statut de la proposition : </span></td>
            <td>
              <span class="mauvegrascalibri9"><?php echo $row_rs_sujet['libstatutsujet'] ?></span>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <!--<tr>
    	<td>
       --><?php /* 
			if($codetypesujet=="02")//stagiaire
      {?> <span class="bleugrascalibri11">Stage de : &nbsp;</span> 
				<?php 
				if($sujet_a_affecter || $sujetaffecte)
				{?> <span class="orangegrascalibri11"><?php echo $row_rs_etudiant['libtypestage'] ?></span>
        	<input name="codetypestage" type="hidden" value="<?php echo $row_rs_etudiant['codetypestage'] ?>">
				<?php 
				}
        else
        {?> <select name="codetypestage" id="codetypestage" class="noircalibri10"
        		onChange="javascript:
            					if(this.value!='01')
                      { document.getElementById('champs_master_doct_postdoc').className='cache'
                      }
                      else
                      { document.getElementById('champs_master_doct_postdoc').className='affiche'
                      }
                      "
            >
          <?php 
          while($row_rs_typestage=mysql_fetch_assoc($rs_typestage))
          {?> 
            <option value="<?php echo $row_rs_typestage['codetypestage']?>"<?php if($row_rs_typestage['codetypestage']==$row_rs_sujet['codetypestage']){?> selected<?php }?>><?php echo $row_rs_typestage['libtypestage'] ?></option>
          <?php 
          }?>
          </select>
      <?php
				}
			} */?>	
      <!--</td>
    </tr> -->
    <tr>
      <td>
      	<?php 
				$inputtype=($sujet_a_affecter || $sujetaffecte?'hidden':'text');
				if($sujet_a_affecter || $sujetaffecte)
				{?>
        <span class="bleugrascalibri11">Date d&eacute;but : </span><span class="orangegrascalibri11"><?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datedeb_sujet'],'/') ?></span>
        <?php 
				}
				else
				{?>
				<span class="bleugrascalibri11">Date d&eacute;but</span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri11"> (jj/mm/aaaa) : </span>
        <?php 
				}?>
				<input name="datedeb_sujet_jj" type="<?php echo $inputtype ?>" class="noircalibri10" id="datedeb_sujet_jj" value="<?php echo substr($row_rs_sujet['datedeb_sujet'],8,2); ?>" size="2" maxlength="2">
        <input name="datedeb_sujet_mm" type="<?php echo $inputtype ?>" class="noircalibri10" id="datedeb_sujet_mm" value="<?php echo substr($row_rs_sujet['datedeb_sujet'],5,2); ?>" size="2" maxlength="2">
        <input name="datedeb_sujet_aaaa" type="<?php echo $inputtype ?>" class="noircalibri10" id="datedeb_sujet_aaaa" value="<?php echo substr($row_rs_sujet['datedeb_sujet'],0,4); ?>" size="4" maxlength="4">
      	<?php 
				$inputtype=($sujet_a_affecter || $sujetaffecte?'hidden':'text');
				if($sujet_a_affecter || $sujetaffecte)
				{?>
        <span class="bleugrascalibri11">Date fin : </span><span class="orangegrascalibri11"><?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datefin_sujet'],'/') ?></span>
        <?php 
				}
				else
				{?>
        <span class="bleugrascalibri11">Date fin</span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri11"> (jj/mm/aaaa) : </span>
        <?php 
				}?>
        <input name="datefin_sujet_jj" type="<?php echo $inputtype ?>" class="noircalibri10" id="datefin_sujet_jj" value="<?php echo substr($row_rs_sujet['datefin_sujet'],8,2); ?>" size="2" maxlength="2">
        <input name="datefin_sujet_mm" type="<?php echo $inputtype ?>" class="noircalibri10" id="datefin_sujet_mm" value="<?php echo substr($row_rs_sujet['datefin_sujet'],5,2); ?>" size="2" maxlength="2">
        <input name="datefin_sujet_aaaa" type="<?php echo $inputtype ?>" class="noircalibri10" id="datefin_sujet_aaaa" value="<?php echo substr($row_rs_sujet['datefin_sujet'],0,4); ?>" size="4" maxlength="4">
      </td>
    </tr>
    <tr>
      <td>
        <table border="0">
          <tr>
            <td valign="top">
              <table border="1">
                <tr>
                  <td>
                    <span class="bleugrascalibri11">
                    <?php
                    //Directeurs (master et th&egrave;se), Contacts (postdoc): appartenant au laboratoire et autres 
                    if($codetypesujet=="04")//collaboration
                    { ?>
                    Contact(s)
                      <?php
                    }
                    else
                    { ?>
                    (Co)Directeur(s)
                      <?php
                    }
                    ?>
                    &nbsp;membre(s) <?php echo htmlspecialchars($GLOBALS['acronymelabo'])  ?>
                    </span>
                  </td>
                </tr>
                <?php
								ksort($row_rs_dir);
								reset($row_rs_dir);  
                //list($numordre,$codedir)=each($row_rs_dir);// premier dir du sujet
                for($numdir=1;$numdir<=$nbdirmax;$numdir++)//3 directeurs maxi.
                { list($numordre,$codedir)=each($row_rs_dir);//dir suivant du sujet
									// distinct ne devrait pas etre utilisé mais pour l'instant un individu peut avoir deux séjours en cours
									$query_individu= "SELECT distinct individu.codeindividu,concat(nom,' ',prenom) as nomprenom,if(hdr='oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='10','oui','non') as hdr". 
																	 " FROM individu,individusejour,corps,cat,statutpers".
																	 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
																	 " and corps.codecat=cat.codecat and corps.codestatutpers=statutpers.codestatutpers".
																	 " and (cat.codelibcat='EC' or cat.codelibcat='CHERCHEUR' or cat.codelibcat='ITARF') and ".periodeencours('datedeb_sejour','datefin_sejour').//permanent present
																	 " and individu.codeindividu<>''". 
																	 (($codetypesujet=="03" && $numdir==1)?" and (hdr='oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='03' or corps.codecorps='04' or corps.codecorps='10' or corps.codecorps='11')":"").//uniquement HDR pour 1er dir si these
																	 //force a choisir un encadrant non vide($numdir==1?"":)
																	 " UNION SELECT codeindividu,".($numdir==1?"' [Choix obligatoire] '":"' '")." as nomprenom,hdr from individu WHERE individu.codeindividu=''".
																	 " ORDER BY nomprenom asc";
																	// echo $query_individu;
                  	$rs_individu=mysql_query($query_individu) or die(mysql_error());
										if ($codetypesujet=="03" && $numdir==1)
										{?>
											<script language="javascript">
											tab_hdr=new Array()
											<?php 
											while($row_rs_individu=mysql_fetch_assoc($rs_individu))
											{ if($row_rs_individu['hdr']=='oui')
												{?>tab_hdr['<?php echo $row_rs_individu['codeindividu'] ?>']=true
												<?php
												}
											 
											}?>
											function esthdr(codeindividu)
											{ return tab_hdr[codeindividu]
											}
											</script>
										<?php   
										}?>

                <tr>
                  <td>
                    <select name="codedir#<?php echo $numdir ?>" class="noircalibri10"
										<?php 
										if ($codetypesujet=="03" && $numdir==1)
										{ ?> onChange="javascript:if(!esthdr(this.value)) alert('Pour information : le premier encadrant devrait &ecirc;tre HDR')"
										 <?php 
										}?>   
										>
                    <?php
                    mysql_data_seek($rs_individu,0); 
                    while($row_rs_individu=mysql_fetch_assoc($rs_individu))
                    { $selected="";
											if($row_rs_individu['codeindividu']==$codedir)
											{ $selected=" selected ";
											}
											
                     ?>
                      <option <?php echo $selected ?> value="<?php echo $row_rs_individu['codeindividu'] ?>">
                      <?php echo $row_rs_individu['nomprenom'];
                      ?>
                      </option>
                      <?php 
                    }?>
                    </select>
                    <?php 
                    if($numdir==1)
                    {?> 
                    <span class="rougegrascalibri9"><sup>*</sup></span>
                    <?php
                    }?>
                  </td>
                </tr>
                    <?php
                }?>
              </table>
            </td>
            <td valign="top">
              <table border="1">
                <tr>
                  <td colspan="2" align="center">
                    <span class="bleugrascalibri11">Autre(s)&nbsp;
                      <?php 
                      if($codetypesujet=="04")//collaboration
                      { ?>
                      Contact(s)
                        <?php
                      }
                      else
                      { ?>
                      (Co)Directeur(s)
                        <?php
                      } ?>
                      &nbsp;(non membres <?php echo htmlspecialchars($GLOBALS['acronymelabo']) ?>) :
                    </span>
                  </td>
                </tr>
                <tr>
                  <td align="center"><span class="bleugrascalibri11">Titre nom pr&eacute;nom</span></td>
                  <td align="center"><span class="bleugrascalibri11">Mail</span></td>
                </tr>
                <tr>
                  <td>
                      <input name="autredir1" type="text" class="noircalibri10"  value="<?php echo htmlspecialchars($row_rs_sujet['autredir1']) ?>" size="30" maxlength="50">
                  </td>
                  <td>
                      <input name="autredir1mail" type="text" class="noircalibri10"  value="<?php echo $row_rs_sujet['autredir1mail'] ?>" size="30" maxlength="100">
                  </td>
                </tr>
                <tr>
                  <td>
                      <input name="autredir2" type="text" class="noircalibri10"  value="<?php echo htmlspecialchars($row_rs_sujet['autredir2']) ?>" size="30" maxlength="50">
                  </td>
                  <td>
                      <input name="autredir2mail" type="text" class="noircalibri10"  value="<?php echo $row_rs_sujet['autredir2mail'] ?>" size="30" maxlength="100">
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
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
      	<table>
      		<tr>
      			<td>
              <table border="1">
                <tr>
                  <td nowrap><span class="bleugrascalibri11"><?php echo htmlspecialchars($GLOBALS['liblong_theme_fr']) ?>(s) ou Service</span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri11">&nbsp;:&nbsp;</span>
                  </td>
                  <td>&nbsp;
                  </td>
                </tr>
                  <?php // theme(s)
                  while($row_rs_theme=mysql_fetch_assoc($rs_theme))//pour chaque theme
                  {	$checked="";
										if(array_key_exists($row_rs_theme['codetheme'],$tab_sujettheme))
										{$checked=" checked ";
										}?>
                  <tr>
                    <td>
                     <span class="noircalibri10"><?php echo htmlspecialchars($row_rs_theme['libcourt_fr']) ?>
                     </span>
                    </td>
                    <td align="center">
                      <input type="checkbox" name="codetheme#<?php echo $row_rs_theme['codetheme'] ?>" <?php echo $checked ?> <?php /* echo $sujet_a_affecter?'readonly':''  */?>>
                      <?php /* commente le 19/10/2015
                      if($sujet_a_affecter || $sujetaffecte)
                      {?> onclick="return false;"
                      <?php 
                      }
                      else
                      {?> onclick="document.forms['<?php echo $form_sujet ?>'].elements['submit_valider'].value='Valider et quitter';"
                      <?php 
                      } */?>
                      
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
			<script>
      var frm=document.forms['<?php echo $form_sujet ?>'];
      var tab_usertheme=new Array();
      <?php 
      $i=0;
      foreach($tab_usertheme as $codetheme)
      {?> tab_usertheme[<?php echo $i ?>]='<?php echo $codetheme ?>';
        <?php 
      }?>
			function estrespthemedusujet()
			{ trouve=false;
				i=0;
				while(i<frm.elements.length && !trouve)
				{ if(frm.elements[i].name.substring(0,(new String('codetheme#')).length)=='codetheme#')
          { if(frm.elements[i].checked)
            { codetheme=frm.elements[i].name.substring((new String('codetheme#')).length)
              for(j=0;j<tab_usertheme.length;j++)
              { if(tab_usertheme[j]==codetheme)
                { trouve=true;
                }
              }
            }
					}
					i++;
				}
				return trouve;
      }
      </script>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><span class="bleugrascalibri11">Titre en fran&ccedil;ais</span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri11">( </span><span id="titre_fr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['titre_fr']) ?> </span><span class="bleugrascalibri11">/300 car. max.) :</span></td>
    </tr>
    <tr>
      <td><span class="noircalibri10">
        <textarea name="titre_fr" cols="100" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","300","'titre_fr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['titre_fr'] ?></textarea>
        </span></td>
    </tr>
		 <?php // 20170319
		 if($GLOBALS['estzrr'])
     {?>    
     <tr>
      <td>
        <table>
          <tr>
            <td></td>
            <td><span class="bleugrascalibri10">Domaine, discipline dans lesquels s'exerce l'activit&eacute; principale<sup><span class="champoblig">*</span></sup></span>
            </td>
          </tr>
          <tr>
            <td valign="top"><span class="bleugrascalibri10">Domaine<sup><span class="champoblig">*</span></sup></span>
            </td>
            <td>
              <select name="codedomainescientifique1" id="codedomainescientifique1" onChange="return affichedisciplinescientifique(this)">
              <?php
              foreach($tab_domainescientifique as $codedomainescientifique=>$un_domainescientifique)
              { ?>	
                <option value="<?php echo $codedomainescientifique ?>"  <?php if($codedomainescientifique==$row_rs_sujet['codedomainescientifique1']){?> selected <?php }?>><?php echo ($codedomainescientifique=='')?"[choix obligatoire]":$un_domainescientifique['libdomainescientifique'];?></option>
              <?php 
              }?>
              </select>
            </td>
          </tr>
          <tr>
            <td valign="top"><span class="bleugrascalibri10">Discipline<sup><span class="champoblig">*</span></sup></span>
            </td>
            <td>
              <select name="codedisciplinescientifique1" id="codedisciplinescientifique1" >
              <?php
              foreach($tab_disciplinescientifique as $codedisciplinescientifique=>$un_disciplinescientifique)
              { if($un_disciplinescientifique['codedomainescientifique']==$row_rs_sujet['codedomainescientifique1'] || $un_disciplinescientifique['codedisciplinescientifique']=='')
                { ?> <option value="<?php echo $codedisciplinescientifique ?>"  <?php if($codedisciplinescientifique==$row_rs_sujet['codedisciplinescientifique1']){?> selected <?php }?>><?php echo ($codedisciplinescientifique=='')?"[choix obligatoire]":$un_disciplinescientifique['libdisciplinescientifique']; ?></option>
                <?php 
                }
              }?>
              </select>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><span class="bleugrascalibri11">Description en fran&ccedil;ais<span class="champoblig"><sup>*</sup> 800 car. mini.</span> ( </span><span id="descr_fr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['descr_fr']) ?></span><span class="bleugrascalibri11">/6400 car. max.) :</span></td>
    </tr>
    <tr>
      <td align="left" class="mauvegrascalibri9">La description du sujet doit nettement mettre en &eacute;vidence la nature de la recherche (th&eacute;orique, appliqu&eacute;e, technologique, ...),son positionnement dans le projet dans lequel elle s'inscrit, ainsi que son positionnement au niveau local, national ou international.<br>
      
      </td>
    </tr>
    <tr>
      <td><span class="noircalibri10">
        <textarea name="descr_fr" cols="100" rows="32" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","6400","'descr_fr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['descr_fr'] ?></textarea>
        </span></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
				<?php if($codetypesujet=="03" || $codetypesujet=="04" || $codetypesujet=="02")
        { ?>
        <!--<div id="champs_master_doct_postdoc" class="<?php //if($codetypesujet=="03" || $codetypesujet=="04" || ($codetypesujet=="02" && $row_rs_sujet['codetypestage']=='01')){?>affiche<?php //}else{?>cache<?php //}?>">
 -->        <table>
        <tr>
          <td><span class="bleugrascalibri11">Mots cl&eacute;s fran&ccedil;ais s&eacute;par&eacute;s par des , (</span><span id="motscles_fr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['motscles_fr']) ?></span><span class="bleugrascalibri11">/100 car. max.) :</span></td>
        </tr>
        <tr>
          <td><input name="motscles_fr" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_sujet['motscles_fr']) ?>" size="100" maxlength="100" <?php echo affiche_longueur_js("this","100","'motscles_fr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Conditions en fran&ccedil;ais (</span><span id="conditions_fr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['conditions_fr']) ?></span><span class="bleugrascalibri11">/2000&nbsp;car.&nbsp;max.) :</span></td>
        </tr>
        <tr>
          <td><span class="mauvegrascalibri9">Dur&eacute;e, employeur, lieu, r&eacute;mun&eacute;ration, profil attendu </span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
            <textarea name="conditions_fr" cols="100" rows="10" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","2000","'conditions_fr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['conditions_fr'] ?></textarea>
            </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Financement en fran&ccedil;ais<sup><span class="champoblig">*</span></sup> (</span><span id="financement_fr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['financement_fr']) ?></span><span class="bleugrascalibri11">/100 car. max.) : </span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
            <input name="financement_fr"  type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_sujet['financement_fr']) ?>" size="100" maxlength="100" <?php echo affiche_longueur_js("this","100","'financement_fr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>>
            </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Titre en anglais</span><span class="rougegrascalibri9"><sup>*</sup></span><span class="bleugrascalibri11">( </span><span id="titre_en#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['titre_en']) ?> </span><span class="bleugrascalibri11">/300 car. max.) :</span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
              <textarea name="titre_en" cols="100" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","300","'titre_en#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['titre_en'] ?></textarea>
          </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Description en anglais ( </span><span id="descr_en#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['descr_en']) ?></span><span class="bleugrascalibri11">/6400 car. max.) :</span></td>
        </tr>
        <tr>
          <td class="mauvegrascalibri9">La description du sujet doit nettement mettre en &eacute;vidence la nature de la recherche (th&eacute;orique, appliqu&eacute;e, technologique, ...),son positionnement dans le projet dans lequel elle s'inscrit, ainsi que son positionnement au niveau local, national ou international.</td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
              <textarea name="descr_en" cols="100" rows="32" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","6400","'descr_en#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['descr_en'] ?></textarea>
            </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Mots cl&eacute;s anglais s&eacute;par&eacute;s par des , (</span><span id="motscles_en#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['motscles_en']) ?></span><span class="bleugrascalibri11">/100 car. max.) :</span></td>
        </tr>
        <tr>
          <td><input name="motscles_en" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_sujet['motscles_en']) ?>" size="100" maxlength="100" <?php echo affiche_longueur_js("this","100","'motscles_en#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Conditions en anglais (</span><span id="conditions_en#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['conditions_en']) ?></span><span class="bleugrascalibri11">/2000&nbsp;car.&nbsp;max.) :</span></td>
        </tr>
        <tr>
          <td><span class="mauvegrascalibri9">Dur&eacute;e, employeur, lieu, r&eacute;mun&eacute;ration, profil attendu </span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
            <textarea name="conditions_en" cols="100" rows="10" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","2000","'conditions_en#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['conditions_en'] ?></textarea>
            </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td><span class="bleugrascalibri11">Financement en anglais (</span><span id="financement_en#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['financement_en']) ?></span><span class="bleugrascalibri11">/100 car. max.) : </span></td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
            <input name="financement_en"  type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_sujet['financement_en']) ?>" size="100" maxlength="100" <?php echo affiche_longueur_js("this","100","'financement_en#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>>
            </span></td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
            <td><span class="bleugrascalibri11">R&eacute;f&eacute;rences publis <?php echo htmlspecialchars($GLOBALS['acronymelabo']) ?>  (5 max.) s&eacute;par&eacute;es par des , (</span><span id="ref_publis#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['ref_publis']) ?></span><span class="bleugrascalibri11">/100 car. max.) : </span></td>
        </tr>
        <tr>
          <td class="mauvegrascalibri9">Pour faire pointer vers un lien HAL, TEL, INRIA, &eacute;crire hal-nnnnnnnn. Ex : hal-11534346,tel-10452518,inria-00234654</td>
        </tr>
        <tr>
          <td><span class="noircalibri10">
              <input name="ref_publis"  type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_sujet['ref_publis']) ?>" size="100" maxlength="100" <?php echo affiche_longueur_js("this","100","'ref_publis#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>>
            </span></td>
        </tr>
      </table>
       <!--</div> -->
        <?php 
      }?>
    	</td>
    </tr>
    <tr>
      <td><span class="bleugrascalibri11">L&rsquo;avis motiv&eacute; de l&rsquo;encadrant apportera des &eacute;l&eacute;ments de contexte appr&eacute;ci&eacute;s par le Haut Fonctionnaire D&eacute;fense et S&eacute;curit&eacute;
          <br>( </span><span id="avis_motive_encadrant_zrr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_sujet['avis_motive_encadrant_zrr']) ?> </span><span class="bleugrascalibri11">/300 car. max.) :</span>
      </td>
    </tr>
    <tr>
      <td><span class="noircalibri10">
          <textarea name="avis_motive_encadrant_zrr" cols="100" rows="2" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","300","'avis_motive_encadrant_zrr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo $row_rs_sujet['avis_motive_encadrant_zrr'] ?></textarea>
      </span></td>
    </tr>
    <?php 
		}// 20170319 fin ?>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer">
      	<?php // 20170322
				if($appel_de_individu!='oui')
        {?> <input name="submit_valider" type="submit" class="noircalibri10" value="Valider et quitter">
				<?php
				}
				/*else
				{?><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer">
					<?php  
          if($action=='creer' && $sujet_a_affecter)
					{?> onClick="txt=verif_themes_sujet_dir();return confirm(txt+'\nUne fois enregistr&eacute;, vous devrez quitter ce formulaire en ayant valid&eacute; ce sujet')"
					<?php 
					}
					else
					{?>onClick="txt=verif_themes_sujet_dir();if(txt!=''){return confirm(txt)}"
					<?php 
					} ?>
					
					
						onClick="txt=verif_themes_sujet_dir();return confirm(txt+'\nValider et quitter ?')">
        <?php 
				} // 20170322 fin */?>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <?php 
		} ?>
  </table>
  </form>

</body>
</html>
    <?php
//}
if(isset($rs_disciplinescientifique)) mysql_free_result($rs_disciplinescientifique);
if(isset($rs_domainescientifique)) mysql_free_result($rs_domainescientifique);
if(isset($rs_etudianttheme))mysql_free_result($rs_etudianttheme);
if(isset($rs_etudiant))mysql_free_result($rs_etudiant);
if(isset($rs_structureindividu))mysql_free_result($rs_structureindividu);
if(isset($rs_sujettheme))mysql_free_result($rs_sujettheme);
if(isset($rs_individutheme))mysql_free_result($rs_individutheme);
if(isset($rs_theme))mysql_free_result($rs_theme);
if(isset($rs_individu))mysql_free_result($rs_individu);
if(isset($rs_dir))mysql_free_result($rs_dir);
if(isset($rs_typesujet))mysql_free_result($rs_typesujet);
if(isset($rs_sujet))mysql_free_result($rs_sujet);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields_sujet))mysql_free_result($rs_fields_sujet);
?>
