<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
if(!estrole('sif',$tab_roleuser) && !estrole('du',$tab_roleuser))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_edit_eotp_source_masse = "form_edit_eotp_source_masse";
 if($admin_bd)
{ /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/  
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codeeotp=isset($_GET['codeeotp'])?$_GET['codeeotp']:(isset($_POST['codeeotp'])?$_POST['codeeotp']:"");
$eotp_ancre=isset($_GET['eotp_ancre'])?$_GET['eotp_ancre']:(isset($_POST['eotp_ancre'])?$_POST['eotp_ancre']:"");
$eotp_ou_source=isset($_GET['eotp_ou_source'])?$_GET['eotp_ou_source']:(isset($_POST['eotp_ou_source'])?$_POST['eotp_ou_source']:"");
$codecentrecout_reel=isset($_GET['codecentrecout_reel'])?$_GET['codecentrecout_reel']:(isset($_POST['codecentrecout_reel'])?$_POST['codecentrecout_reel']:"");
$tab_champs_ouinon_defaut=array('distinguermasse'=>'non');
$tab_champs_ouinon=array('distinguermasse');
$tab_champs_numerique=array();
$tab_champs_date=array();
if($eotp_ou_source=='eotp')
{ $tab_champs_date=array('datedeb_eotp'=>array("lib" => "Date debut eotp ","jj" => "","mm" => "","aaaa" => ""),'datefin_eotp'=>array("lib" => "Date fin eotp ","jj" => "","mm" => "","aaaa" => ""));
}
$tab_eotp_source_montant=array();
$codeoperation_a_supprimer="";


foreach($_POST as $postkey=>$postval)
{ $posdiese=strpos($postkey,'#');
	if($posdiese!==false)
	{ if(substr($postkey,0,$posdiese)=='supprimer')
		{ $codeoperation_a_supprimer=substr($postkey,$posdiese+1);
			$pos2diese=strpos($codeoperation_a_supprimer,'##');
			if($pos2diese!==false)
			{ $codeoperation_a_supprimer=substr($codeoperation_a_supprimer,0,$pos2diese);
			}
		}
	}
}

foreach($_POST as $postkey=>$postval)
{ $posdiese=strpos($postkey,'#');
	if($posdiese!==false)
	{ $champ=substr($postkey,0,$posdiese);
		$codeoperation=substr($postkey,$posdiese+1);
		if($codeoperation!=$codeoperation_a_supprimer)
		{	if($champ=='montantfonctionnement')
			{ $tab_eotp_source_montant[$codeoperation][$champ]=$postval;
				$tab_champs_numerique[$postkey]= array('lib' => 'Montant fonctionnement','string_format'=>'%01.2f');
			}
			else if($champ=='montantsalaire')
			{ $tab_eotp_source_montant[$codeoperation][$champ]=$postval;
				$tab_champs_numerique[$postkey]= array('lib' => 'Montant salaire','string_format'=>'%01.2f');
			}
			else if($champ=='montantinvestissement')
			{ $tab_eotp_source_montant[$codeoperation][$champ]=$postval;
				$tab_champs_numerique[$postkey]= array('lib' => 'Montant investissement','string_format'=>'%01.2f');
			}
			else if($champ=='dateoperation_jj')
			{ $tab_eotp_source_montant[$codeoperation]['dateoperation']['jj']=$postval;
				$tab_champs_date['dateoperation#'.$codeoperation]=array("lib" => "Date ligne ".$codeoperation,"jj" => "","mm" => "","aaaa" => "");
			}
			else if($champ=='dateoperation_mm')
			{ $tab_eotp_source_montant[$codeoperation]['dateoperation']['mm']=$postval;
			}
			else if($champ=='dateoperation_aaaa')
			{ $tab_eotp_source_montant[$codeoperation]['dateoperation']['aaaa']=$postval;
			}
			else if($champ=='note')
			{ $tab_eotp_source_montant[$codeoperation]['note']=$postval;
			}
		}
		else
		{ unset($_POST[$champ.'#'.$codeoperation_a_supprimer]);
		}
	}
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur=controle_form_eotp_source_masse(&$_POST,$tab_controle_et_format);
	// si modif dates eotp : verif que les commandes associees au contrat sont dans la nouvelle periode
	if($eotp_ou_source=='eotp')
	{ $datedeb_eotp=jjmmaaaa2date($_POST['datedeb_eotp_jj'],$_POST['datedeb_eotp_mm'],$_POST['datedeb_eotp_aaaa']);
		$datefin_eotp=jjmmaaaa2date($_POST['datefin_eotp_jj'],$_POST['datefin_eotp_mm'],$_POST['datefin_eotp_aaaa']);
		$query_rs="select commande.*".
							" from commande,commandeimputationbudget".
							" where commande.codecommande=commandeimputationbudget.codecommande and codeeotp=".GetSQLValueString($codeeotp, "text").
							" and not (".intersectionperiodes(GetSQLValueString($datedeb_eotp, "text"),GetSQLValueString($datefin_eotp, "text"),'datecommande','datecommande').")";
		$rs=mysql_query($query_rs);
		if($row_rs=mysql_fetch_assoc($rs))
		{ $erreur.="Des commandes portant sur cet EOTP sont en dehors des dates d&eacute;but-fin de l'EOTP";
		}
	}

	foreach($tab_eotp_source_montant as $codeoperation=>$un_tab_eotp_source_montant)
	{ $tab_eotp_source_montant[$codeoperation]['dateoperation']=jjmmaaaa2date($_POST['dateoperation#'.$codeoperation]['jj'],$_POST['dateoperation#'.$codeoperation]['mm'],$_POST['dateoperation#'.$codeoperation]['aaaa']);
	}
	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		if($action=="creer")//creation uniquement pour eotp_ou_source=source
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='budg_aci'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$codeeotp=$row_seq_number['currentnumber'];
			$codeeotp=str_pad((string)((int)$codeeotp+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codeeotp, "text")." where nomtable='budg_aci'") or  die(mysql_error());
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			// insertion d'enregistrement avec champs remplis et les autres=""
			$rs_fields = mysql_query('SHOW COLUMNS FROM budg_aci');
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
				$liste_val.=($first?"":",");
				$first=false;
				if($row_rs_fields['Field']=='codeaci')
				{ $liste_val.=GetSQLValueString($codeeotp, "text");
				}
				else if($row_rs_fields['Field']=='date_deb' || $row_rs_fields['Field']=='date_fin' )
				{	$liste_val.=GetSQLValueString($GLOBALS[$row_rs_fields['Field'].'_exercice_comptable'], "text");
				}
				else
				{ $liste_val.="''";
				}
			}//fin while
			$updateSQL = "insert into budg_aci (".$liste_champs.") values (".$liste_val.")";
			//echo $updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
			$action="modifier";//
		}//fin if creation
		// Modif.
		if($eotp_ou_source=='eotp')
		{ $updateSQL = "UPDATE eotp SET ";
			$rs_fields = mysql_query('SHOW COLUMNS FROM eotp');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ if(!in_array($row_rs_fields['Field'],array("codeeotp")))//pas de mise a jour de ces champs
				{ if(isset($_POST[$row_rs_fields['Field']]) || (isset($_POST[$row_rs_fields['Field'].'_jj']) && isset($_POST[$row_rs_fields['Field'].'_mm']) && isset($_POST[$row_rs_fields['Field'].'_aaaa'])))
					{ if(in_array($row_rs_fields['Field'], $tab_champs_ouinon)===false)
						{ $updateSQL.=$row_rs_fields['Field']."=";
							if(array_key_exists($row_rs_fields['Field'],$tab_champs_date)!==false)
							{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$row_rs_fields['Field'].'_jj'],$_POST[$row_rs_fields['Field'].'_mm'],$_POST[$row_rs_fields['Field'].'_aaaa']), "text");
							}
							else if($row_rs_fields['Field']=='codecentrecout_reel')
							{ $updateSQL.=GetSQLValueString($codecentrecout_reel, "text");
							}
							else if(isset($_POST[$row_rs_fields['Field'].'_jj']))
							{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$row_rs_fields['Field'].'_jj'],$_POST[$row_rs_fields['Field'].'_mm'],$_POST[$row_rs_fields['Field'].'_aaaa']), "text");
							}
							else
							{ $updateSQL.=GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
							}
							$updateSQL.=",";
						}
						else
						{ $updateSQL.=$row_rs_fields['Field']."='oui',";
						}
					}
					else//non envoye dans le POST
					{ if(in_array($row_rs_fields['Field'], $tab_champs_ouinon)!==false)
						{ $updateSQL.=$row_rs_fields['Field']."='non'".",";
						}
					}
				}
			}
			$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
			$updateSQL.=" WHERE codeeotp=".GetSQLValueString($codeeotp, "text");
			mysql_query($updateSQL) or die(mysql_error());
			//echo $updateSQL;
			/* if($row_rs_fields['Field']=='datedeb_eotp' || $row_rs_fields['Field']=='datefin_eotp' )
			{	$liste_val.=GetSQLValueString($GLOBALS[$row_rs_fields['Field'].'_exercice_comptable'], "text");
			}
			$updateSQL = "UPDATE eotp SET codecentrecout_reel=".GetSQLValueString($codecentrecout_reel, "text").", distinguermasse=".GetSQLValueString($distinguermasse, "text").
										" datedeb_eotp="." datefin_eotp=".
										" where  codeeotp=".GetSQLValueString($codeeotp, "text");; */
			mysql_query($updateSQL) or die(mysql_error());
		}
		else
		{ $updateSQL = "UPDATE budg_aci SET ";
			$rs_fields = mysql_query('SHOW COLUMNS FROM budg_aci');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ if(!in_array($row_rs_fields['Field'],array("codeaci")))//pas de mise a jour de ces champs
				{ if(isset($_POST[$row_rs_fields['Field']]) || (isset($_POST[$row_rs_fields['Field'].'_jj']) && isset($_POST[$row_rs_fields['Field'].'_mm']) && isset($_POST[$row_rs_fields['Field'].'_aaaa'])))
					{ if(in_array($row_rs_fields['Field'], $tab_champs_ouinon)===false)
						{ $updateSQL.=$row_rs_fields['Field']."=";
							/* if(array_key_exists($row_rs_fields['Field'],$tab_champs_date)!==false)
							{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$row_rs_fields['Field'].'_jj'],$_POST[$row_rs_fields['Field'].'_mm'],$_POST[$row_rs_fields['Field'].'_aaaa']), "text");
							}
							else */ 
							if($row_rs_fields['Field']=='codecentrecout_reel')
							{ $updateSQL.=GetSQLValueString($codecentrecout_reel, "text");
							}
							else
							{ $updateSQL.=GetSQLValueString($_POST[$row_rs_fields['Field']], "text");
							}
							$updateSQL.=",";
						}
						else
						{ $updateSQL.=$row_rs_fields['Field']."='oui',";
						}
					}
					else//non envoye dans le POST
					{ if(in_array($row_rs_fields['Field'], $tab_champs_ouinon)!==false)
						{ $updateSQL.=$row_rs_fields['Field']."='non'".",";
						}
					}
				}
			}
			$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
			$updateSQL.=" WHERE codeaci=".GetSQLValueString($codeeotp, "text");
			//echo $updateSQL;
			mysql_query($updateSQL) or die(mysql_error());
		}
		// lignes des montants
		mysql_query("delete from eotp_source_montant where codeeotp=".GetSQLValueString($codeeotp, "text"));
		$i=0;//renumerotation de codeoperation pour eviter les trous
		foreach($tab_eotp_source_montant as $codeoperation => $un_tab_eotp_source_montant)
		{ if($un_tab_eotp_source_montant['montantfonctionnement']!=0 || $un_tab_eotp_source_montant['montantsalaire']!=0 || $un_tab_eotp_source_montant['montantinvestissement']!=0)
			{	$i++;
				$nouveaucodeoperation=str_pad($i,2,"0",STR_PAD_LEFT);
				$updateSQL ="INSERT into eotp_source_montant (codeeotp,codeoperation,montantfonctionnement,montantsalaire,montantinvestissement,dateoperation,note)".
										" values (".GetSQLValueString($codeeotp, "text").",".GetSQLValueString($nouveaucodeoperation, "text").",".GetSQLValueString($_POST['montantfonctionnement#'.$codeoperation], "text").
										",".GetSQLValueString($_POST['montantsalaire#'.$codeoperation], "text").",".GetSQLValueString($_POST['montantinvestissement#'.$codeoperation], "text").
										",".GetSQLValueString($tab_eotp_source_montant[$codeoperation]['dateoperation'],"text").
										",".GetSQLValueString($_POST['note#'.$codeoperation], "text").")";
										mysql_query($updateSQL) or die(mysql_error());
			}
		}
	}
}

// ----------------------- Formulaire de donnees 
//eotp
if($eotp_ou_source=='eotp')
{ $query_rs_eotp_source=	"SELECT eotp.codeeotp as codeeotp,eotp.libcourteotp as libeotp,distinguermasse,datedeb_eotp,datefin_eotp,".
													" centrecout_reel.codecentrecout_reel,centrefinancier_reel.codecentrefinancier_reel, typecredit.codetypecredit".
													" FROM centrecout,centrefinancier,typecredit,centrefinancier_reel,centrecout_reel,eotp".
													" where eotp.codecentrecout_reel=centrecout_reel.codecentrecout_reel and centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel".
													" and eotp.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and centrefinancier.codetypecredit=typecredit.codetypecredit".
													" and eotp.codeeotp=".GetSQLValueString($codeeotp, "text");
}
else//source
{ /* avant modif. 29092014 
	$query_rs_eotp_source = "SELECT budg_aci.codeaci, budg_aci.libcourt,distinguermasse,codetypesource,".
													" centrecout.codecentrecout, centrefinancier.codecentrefinancier, typecredit.codetypecredit,".
													" centrecout_reel.codecentrecout_reel, centrefinancier_reel.codecentrefinancier_reel,".
													" budg_aci.coderespaci".
													" FROM centrecout,centrefinancier,centrefinancier_reel,centrecout_reel, typecredit,budg_aci".
													" where budg_aci.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
													" and centrefinancier.codetypecredit=typecredit.codetypecredit".
													" and budg_aci.codecentrecout_reel=centrecout_reel.codecentrecout_reel and centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel".
													" and budg_aci.codeaci=".GetSQLValueString($codeeotp, "text"); */
// modif. 29092014
$query_rs_eotp_source = "SELECT budg_aci.codeaci, budg_aci.libcourt,distinguermasse,codetypesource,".
													" centrecout.codecentrecout, centrefinancier.codecentrefinancier, typecredit.codetypecredit,".
													" centrecout_reel.codecentrecout_reel, centrecout_reel.libcourt as libcentrecout_reel, centrefinancier_reel.codecentrefinancier_reel,".
													" budg_aci.coderespaci,".
													" budg_aci.supprimable".
													" FROM centrecout,centrefinancier,centrefinancier_reel,centrecout_reel, typecredit,budg_aci".
													" where budg_aci.codecentrecout=centrecout.codecentrecout and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
													" and centrefinancier.codetypecredit=typecredit.codetypecredit".
													" and budg_aci.codecentrecout_reel=centrecout_reel.codecentrecout_reel and centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel".
													" and budg_aci.codeaci=".GetSQLValueString($codeeotp, "text");

// fin modif. 29092014 
}
$rs_eotp_source=mysql_query($query_rs_eotp_source) or die(mysql_error());
$row_rs_eotp_source=mysql_fetch_assoc($rs_eotp_source);

if($erreur=='')
{	if($action=="creer")//source
	{ $rs_fields_eotp_source = array('codeaci','libcourt','distinguermasse','codetypecredit','codetypesource','codecentrefinancier','codecentrecout','codecentrefinancier_reel','codecentrecout_reel','coderespaci');
		$row_rs_eotp_source['codeaci']='';
		$row_rs_eotp_source['libcourt']='';
		$row_rs_eotp_source['distinguermasse']='non';
		$row_rs_eotp_source['codetypecredit']=isset($tab_roleuser['gestcnrs'])?'01':'02';
		$row_rs_eotp_source['codetypesource']='';
		$row_rs_eotp_source['codecentrefinancier']='';
		$row_rs_eotp_source['codecentrecout']='';
		$query_rs = "SELECT centrefinancier.codecentrefinancier,codecentrecout FROM centrecout,centrefinancier where centrecout.codecentrefinancier=centrefinancier.codecentrefinancier and codetypecredit=".GetSQLValueString($row_rs_eotp_source['codetypecredit'], "text");
		$rs = mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_eotp_source['codecentrefinancier']=$row_rs['codecentrefinancier'];
			$row_rs_eotp_source['codecentrecout']=$row_rs['codecentrecout'];
		}
		$row_rs_eotp_source['codecentrefinancier_reel']='';
		$row_rs_eotp_source['codecentrecout_reel']='';
		$query_rs = "SELECT centrefinancier_reel.codecentrefinancier_reel,codecentrecout_reel, centrecout_reel.libcourt as libcentrecout_reel FROM centrecout_reel,centrefinancier_reel where centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel and codetypecredit=".GetSQLValueString($row_rs_eotp_source['codetypecredit'], "text");
		$rs = mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_eotp_source['codecentrefinancier_reel']=$row_rs['codecentrefinancier_reel'];
			$row_rs_eotp_source['codecentrecout_reel']=$row_rs['codecentrecout_reel'];
			$row_rs_eotp_source['libcentrecout_reel']=$row_rs['libcentrecout_reel'];
		}
		$row_rs_eotp_source['coderespaci']='';
	}
	$tab_eotp_source_montant=array();
	$query_rs="select * from eotp_source_montant where codeeotp=".GetSQLValueString($codeeotp, "text")." order by dateoperation ";
	$rs=mysql_query($query_rs) or die(mysql_error());
	// ajout nouvelle operation
	$maxcodeoperation=0;
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_eotp_source_montant[$row_rs['codeoperation']]=$row_rs;
		$maxcodeoperation=max($maxcodeoperation,(int)$row_rs['codeoperation']);
	}
	$nouveaucodeoperation=str_pad($maxcodeoperation+1,2,'0',STR_PAD_LEFT);
	$tab_eotp_source_montant[$nouveaucodeoperation]=array('codeeotp'=>$codeeotp,'codeoperation'=>$nouveaucodeoperation,'montantfonctionnement'=>'','montantinvestissement'=>'','montantsalaire'=>'',
																								'dateoperation'=>date("Y/m/d"),'note'=>'');
 
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ if($eotp_ou_source=='eotp')
	{ $row_rs_fields_eotp_source = array('codeeotp','distinguermasse','codetypecredit','codecentrecout_reel','codecentrefinancier_reel','datedeb_eotp','datefin_eotp');
	}
	else
	{ $row_rs_fields_eotp_source = array('codeaci','libcourt','distinguermasse','codecentrecout','codecentrefinancier','codetypecredit','codetypesource','codecentrecout_reel','codecentrefinancier_reel','coderespaci');
	}

	foreach($row_rs_fields_eotp_source as $Field)
	{ if($Field=='distinguermasse')
		{ $row_rs_eotp_source[$Field]=isset($_POST[$Field])?'oui':'';
		}
		else if(array_key_exists($Field,$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_eotp_source[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
		else if(isset($_POST[$Field]))
		{ $row_rs_eotp_source[$Field]=$_POST[$Field];
		}
	}
}
	

// typecredit
$query_rs = "SELECT codetypecredit, libcourt as libtypecredit, codelibtypecredit FROM typecredit where codetypecredit<>''";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_typecredit=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typecredit[$row_rs['codetypecredit']]=$row_rs;
}
// centrefinancier=administration/recherche
$query_rs = "SELECT codetypecredit, codecentrefinancier, libcourt as libcentrefinancier FROM centrefinancier where codecentrefinancier<>'' ORDER BY centrefinancier.numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_centrefinancier=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier[$row_rs['codecentrefinancier']]=$row_rs;
} 
// centrecout=enveloppe
$query_rs =	"SELECT codecentrefinancier, centrecout.codecentrecout,libcourt as libcentrecout, if(centrecouttheme.codecentrecout IS NULL,'non','oui') as esttheme, if(centrecouttheme.codestructure='00','oui','non') as estdirection".
						" FROM centrecout".
						" left join centrecouttheme on centrecout.codecentrecout=centrecouttheme.codecentrecout".
						" where centrecout.codecentrecout<>''";
					 	//" and centrecout.date_deb='2013/01/01'".
						" ORDER BY centrecout.numordre";
						//echo $query_rs;
$rs= mysql_query($query_rs) or die(mysql_error());
$tab_centrecout=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrecout[$row_rs['codecentrecout']]=$row_rs;
}
// centrefinancier reel
$query_rs =	"SELECT codecentrefinancier_reel,codetypecredit,libcourt as libcentrefinancier_reel".
						" FROM centrefinancier_reel".
						" where codecentrefinancier_reel<>''".
						" ORDER BY codetypecredit,codecentrefinancier_reel";
$rs= mysql_query($query_rs) or die(mysql_error());
$tab_centrefinancier_reel=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrefinancier_reel[$row_rs['codecentrefinancier_reel']]=$row_rs;
}
// centrecout reel
$query_rs = "SELECT codecentrecout_reel,codecentrefinancier_reel, libcourt as libcentrecout_reel".
					 " FROM centrecout_reel".
					 " WHERE codecentrecout_reel<>''".
					 //" WHERE ".periodeencours('date_deb','date_fin').
					 " ORDER BY numordre ASC";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_centrecout_reel=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_centrecout_reel[$row_rs['codecentrecout_reel']]=$row_rs;
}
//type source
$query_rs ="SELECT cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource".
													" FROM cmd_typesource".
													" ORDER BY cmd_typesource.libcourt";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_typesource=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typesource[$row_rs['codetypesource']]=$row_rs;
}

// resp scientifique
$query_rs="select individu.codeindividu,nom,prenom from individu, individusejour,corps,cat".
					" where individu.codeindividu=individusejour.codeindividu".
					" and ".periodeencours('datedeb_sejour','datefin_sejour').
					" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat and (codelibcat='EC' or codelibcat='CHERCHEUR' or codelibcat='ITARF')".
					" UNION select '' as codeindividu,'' as nom, '' as prenom from individu".
					" order by nom,prenom";
$rs= mysql_query($query_rs) or die(mysql_error());
$tab_respscientifique=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_respscientifique[$row_rs['codeindividu']]=$row_rs;
}

// contrats
$tab_contrat=array();
if($eotp_ou_source=='eotp')
{ $query_rs=	"SELECT contrat.codecontrat, acronyme,individu.nom as nomrespscientifique,individu.prenom as prenomrespscientifique,structure.libcourt_fr as libtheme,".
							" typecredit.codetypecredit, typecredit.libcourt as libtypecredit".
							" FROM eotp,contrateotp,cont_orggesttypecredit,typecredit,contrat".
							" left join structure on contrat.codetheme=structure.codestructure".
							" left join individu on contrat.coderespscientifique=individu.codeindividu".
							" where eotp.codeeotp=contrateotp.codeeotp and contrateotp.codecontrat=contrat.codecontrat".
							" and cont_orggesttypecredit.codeorggest=contrat.codeorggest".
							" and cont_orggesttypecredit.codetypecredit=typecredit.codetypecredit".
							" and eotp.codeeotp=".GetSQLValueString($codeeotp, "text").
							" order by individu.nom";
	$rs= mysql_query($query_rs) or die(mysql_error());
	$codetypecredit='';
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_contrat[$row_rs['codecontrat']]=$row_rs;
		$libtypecredit=$row_rs['libtypecredit'];
		$codetypecredit=$row_rs['codetypecredit'];
	}
	/* if($row_rs_eotp_source['codetypecredit']=='')
	{ $row_rs_eotp_source['codetypecredit']=$codetypecredit;
	} */
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion du budget <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link rel="stylesheet" href="SpryAssets/SpryTooltip.css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<SCRIPT language="javascript">
	var w;
	function OuvrirVisible(url)
	{ w=window.open(url,'detailcontrat',"scrollbars = yes,width=700,height=700,location=no,mebubar=no,status=no,directories=no");
		w.document.close();
		w.focus();
	}
	function Fermer() 
	{ if (w.document) { w.close(); }
	}
var nbtypecredit=0;nbcentrefinancier=0;nbcentrecout=0;nbcentrefinancier_reel=0;nbcentrecout_reel=0;
tab=new Array();
var tab_typecredit=new Array();
var tab_centrefinancier=new Array();
var tab_centrecout=new Array();
var tab_centrefinancier_reel=new Array();
var tab_centrecout_reel=new Array();
var tab_respscientifique=new Array();
var tab_typesource=new Array();
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
{?> var o=new Object();
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_centrefinancier['codetypecredit']) ?>";
	o["codecentrefinancier"]="<?php echo js_tab_val($row_rs_centrefinancier['codecentrefinancier']); ?>";
	o["libcentrefinancier"]="<?php echo js_tab_val($row_rs_centrefinancier['libcentrefinancier']) ?>";
	tab_centrefinancier[<?php echo $nb ?>]=o;
<?php 
	$nb++; 
}
?>
nbcentrefinancier=<?php echo $nb; ?>;
<?php
$nb=0;
foreach($tab_centrecout as $codecentrecout=>$row_rs_centrecout)
{  ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_centrecout['codecentrecout']); ?>";
	o["codecentrefinancier"]="<?php echo js_tab_val($row_rs_centrecout['codecentrefinancier']); ?>";
	o["libcentrecout"]="<?php echo js_tab_val($row_rs_centrecout['libcentrecout']); ?>";
	o["esttheme"]="<?php echo js_tab_val($row_rs_centrecout['esttheme']); ?>";
	o["estdirection"]="<?php echo js_tab_val($row_rs_centrecout['estdirection']); ?>";
	tab_centrecout[<?php echo $nb ?>]=o;
	<?php
	$nb++; 
}?>
nbcentrecout=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_centrefinancier_reel as $codecentrefinancier_reel=>$row_rs_centrefinancier_reel)
{?> var o=new Object();
	o["codetypecredit"]="<?php echo js_tab_val($row_rs_centrefinancier_reel['codetypecredit']) ?>";
	o["codecentrefinancier_reel"]="<?php echo js_tab_val($row_rs_centrefinancier_reel['codecentrefinancier_reel']); ?>";
	o["libcentrefinancier_reel"]="<?php echo js_tab_val($row_rs_centrefinancier_reel['libcentrefinancier_reel']) ?>";
	tab_centrefinancier_reel[<?php echo $nb ?>]=o;
<?php 
	$nb++; 
}
?>
nbcentrefinancier_reel=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_centrecout_reel as $codecentrecout_reel=>$row_rs_centrecout_reel)
{  ?> 
	var o=new Object();
	o["codecentrecout_reel"]="<?php echo js_tab_val($row_rs_centrecout_reel['codecentrecout_reel']); ?>";
	o["codecentrefinancier_reel"]="<?php echo js_tab_val($row_rs_centrecout_reel['codecentrefinancier_reel']); ?>";
	o["libcentrecout_reel"]="<?php echo js_tab_val($row_rs_centrecout_reel['libcentrecout_reel']); ?>";
	tab_centrecout_reel[<?php echo $nb ?>]=o;
	<?php
	$nb++; 
}?>
nbcentrecout_reel=<?php echo $nb; ?>;

<?php
 
foreach($tab_respscientifique as $coderespscientifique=>$row_rs_respscientifique)
{  ?> 
	tab_respscientifique["<?php echo $coderespscientifique ?>"]="<?php echo js_tab_val($row_rs_respscientifique['nom']).' '.js_tab_val(substr($row_rs_respscientifique['prenom'],0,1)).'.'; ?>";
	<?php
}
 
foreach($tab_typesource as $codetypesource=>$row_rs_typesource)
{  ?> 
	tab_typesource["<?php echo $codetypesource ?>"]="<?php echo js_tab_val($row_rs_typesource['libtypesource']) ?>";
	<?php
}?>

function affichestructure(champ)
{ // liste(s) des sous-structures d'une structure
	// le nom du champ, hors codetypecredit, indique les listes select concernées
	var frm=document.forms["<?php echo $form_edit_eotp_source_masse ?>"];
	var valchamp=champ.value;
	firstcentrefinancier=true;firstcentrecout=true;firstcentrefinancier_reel=true;firstcentrecout_reel=true;
	numeotp=-1;
 	if(champ.name=='codetypecredit')
	{ // si le typecredit='', c'est qu'il s'agit du premier choix de type de credit en creation : on enleve l'element vide et on décale l'index de l'élement choisi
		listetypecredit=frm.elements[champ.name];
		if(listetypecredit.options[0].value=='')
		{ index=listetypecredit.selectedIndex;
			listetypecredit.options.length=0;
			for(i=0;i<nbtypecredit;i++)
			{ listetypecredit.options[listetypecredit.options.length]=new Option(tab_typecredit[i].libtypecredit, tab_typecredit[i].codetypecredit);
			}
			listetypecredit.selectedIndex=index-1;
		}

		listecentrefinancier=frm.elements["codecentrefinancier"];
		listecentrefinancier.options.length=0;
		listecentrecout=frm.elements["codecentrecout"];
		listecentrecout.options.length=0;//listecentrecout.options[0]=new Option("","");
		listecentrefinancier_reel=frm.elements["codecentrefinancier_reel"];
		listecentrefinancier_reel.options.length=0;
		listecentrecout_reel=frm.elements["codecentrecout_reel"];
		listecentrecout_reel.options.length=0;//listecentrecout.options[0]=new Option("","");
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
		{ if(tab_centrecout[i].codecentrefinancier==valcentrefinancier)
			{ if(firstcentrecout)
				{ valcentrecout=tab_centrecout[i].codecentrecout;
					firstcentrecout=false;
				}
				listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
			} 
		}
		for(i=0;i<nbcentrefinancier_reel;i++)
		{ if(tab_centrefinancier_reel[i].codetypecredit==valchamp)
			{ if(firstcentrefinancier_reel)
				{ valcentrefinancier_reel=tab_centrefinancier_reel[i].codecentrefinancier_reel;
					firstcentrefinancier_reel=false;
				}
				listecentrefinancier_reel.options[listecentrefinancier_reel.options.length]=new Option(tab_centrefinancier_reel[i].libcentrefinancier_reel, tab_centrefinancier_reel[i].codecentrefinancier_reel);
			} 
		}
		for(i=0;i<nbcentrecout_reel;i++)
		{ if(tab_centrecout_reel[i].codecentrefinancier_reel==valcentrefinancier_reel)
			{ if(firstcentrecout_reel)
				{ valcentrecout_reel=tab_centrecout_reel[i].codecentrecout_reel;
					firstcentrecout=false;
				}
				listecentrecout_reel.options[listecentrecout_reel.options.length]=new Option(tab_centrecout_reel[i].libcentrecout_reel, tab_centrecout_reel[i].codecentrecout_reel);
			} 
		}

	}
 	else
	{	listecentrecout=frm.elements["codecentrecout"];
		if(champ.name=="codecentrefinancier")
		{ listecentrecout.options.length=0;//listecentrecout.options[0]=new Option("","");
			for(i=0;i<nbcentrecout;i++)
			{ if(tab_centrecout[i].codecentrefinancier==valchamp)
				{ if(firstcentrecout)
					{ valcentrecout=tab_centrecout[i].codecentrecout;
						firstcentrecout=false;
					}
					listecentrecout.options[listecentrecout.options.length]=new Option(tab_centrecout[i].libcentrecout, tab_centrecout[i].codecentrecout);
				} 
			}
		} 
		listecentrecout_reel=frm.elements["codecentrecout_reel"];
		if(champ.name=="codecentrefinancier_reel")
		{ listecentrecout_reel.options.length=0;//listecentrecout.options[0]=new Option("","");
			for(i=0;i<nbcentrecout_reel;i++)
			{ if(tab_centrecout_reel[i].codecentrefinancier_reel==valchamp)
				{ if(firstcentrecout_reel)
					{ valcentrecout_reel=tab_centrecout_reel[i].codecentrecout_reel;
						firstcentrecout_reel=false;
					}
					listecentrecout_reel.options[listecentrecout_reel.options.length]=new Option(tab_centrecout_reel[i].libcentrecout_reel, tab_centrecout_reel[i].codecentrecout_reel);
				} 
			}
		} 
	}
}

function affiche_libelle_source()
{ var frm=document.forms["<?php echo $form_edit_eotp_source_masse ?>"];
	libelle='';
	if(frm.elements['codetypesource'].value!='')
	{ libelle+=tab_typesource[frm.elements['codetypesource'].value];
	}
	if(frm.elements['libcourt'].value!='')
	{ libelle+=(libelle==''?'':' - ')+frm.elements['libcourt'].value;
	}
	if(frm.elements['coderespaci'].value!='')
	{ libelle+=(libelle==''?'':' - ')+tab_respscientifique[frm.elements['coderespaci'].value];
	}
	if(frm.elements['codetypecredit'].value=='02' && frm.elements['codecentrecout_reel'])//credits UL : ajout suffixe RSA--libcentrecout-reel...
	{	for(i=0;i<nbcentrecout_reel;i++)
		{ if(tab_centrecout_reel[i].codecentrecout_reel==frm.elements['codecentrecout_reel'].value)
			{ libelle+=(libelle==''?'':' - ')+tab_centrecout_reel[i].libcentrecout_reel;
			}
		}
	}
	document.getElementById('libelle_source').innerHTML=libelle;
}
function affiche_resp_credit()
{ if(document.getElementById('coderespaci').value!='')
	{ document.getElementById('resp_credit').className='cache';
	}
	else
	{ codecentrecout=document.getElementById('codecentrecout').value;
		trouve=false;i=0;
		while(i<nbcentrecout && !trouve)
		{ if(tab_centrecout[i]['codecentrecout']==codecentrecout && tab_centrecout[i]['esttheme']=='oui')
			{ document.getElementById('resp_credit').className='affiche';
				if(tab_centrecout[i]['estdirection']=='oui')
				{ document.getElementById('resp_credit').innerHTML='Directeur';
				}
				else
				{ document.getElementById('resp_credit').innerHTML='Resp. '+tab_centrecout[i]['libcentrecout'];
				}
				trouve=true;
			}
			i++;
		}
		if(!trouve)
		{ document.getElementById('resp_credit').innerHTML='Directeur';
		}
	}
}
</SCRIPT>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"<?php }?>>


<table border="0" align="center" cellpadding="0" cellspacing="1">
  <tr>
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Masses budg&eacute;taires','lienretour'=>'gestioneotp_source_masse.php','texteretour'=>'Retour &agrave; la gestion des &eacute;OTP/Sources - masses',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
  	<td>
    	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_credit">
      <div class="tooltipContent_cadre" id="info_credit">
          <span class="noircalibri10">
          	Les centres financiers et de co&ucirc;ts sont ceux que les secr. d'appui renseignent dans SIFAC UL (sans int&eacute;r&ecirc;t pour le CNRS).<br>
            Les &laquo; Types de cr&eacute;dits &raquo; (administration/recherche) et les enveloppes (Direction, CID, ISET, SBS et ACI, Missions, Site, Infrastructure) sont utilis&eacute;s<br>
            pour structurer le budget en interne au niveau du laboratoire.<br>
            Les types de source permettent de regrouper les sources provenant des dotations UL et CNRS : ACI, Missions, ...<br>
            Les responsables de cr&eacute;dits des sources associ&eacute;es &agrave; Direction ou un d&eacute;partement sont les responsables de ces derniers par d&eacute;faut.<br>
            La s&eacute;lection d'un responsable de cr&eacute;dits ne doit &ecirc;tre faite que si le responsable n'est pas le Directeur ou les responsables de d&eacute;partements<br>
            Enveloppes :<br>
            - Direction ou d&eacute;partement : une source "dotation" (ou RSA--LDDIR ou....) devrait leur &ecirc;tre associée.<br>
            - ACI (et Missions) : toutes les ACI devraient entrer dans cette enveloppe avec un responsable de cr&eacute;dits.<br>
              &nbsp;&nbsp;&nbsp;Ces sources sont visibles par les responsables de d&eacute;partement du responsable s&eacute;lectionn&eacute;.<br>
            - Site : (par ex. site ENSEM - Carole) les sources n'ont pas de responsable de cr&eacute;dits et ne sont donc pas visibles par les responsables de d&eacute;partements.<br>
              &nbsp;&nbsp;&nbsp;Sans responsable de cr&eacute;dits, et n'entrant pas dans un d&eacute;partement, c'est implicitement le Directeur qui en est responsable.<br>
            - Infrastructure : sans doute identique &agrave; Site<br>
            - Autres : &agrave; pr&eacute;ciser en respectant les r&egrave;gles ci-dessus.<br>
            <font color="#F90">Le libell&eacute; d'une source, tel qu'il appara&icirc;tra en saisie de commande est construit avec : resp. cr&eacute;dits - type de source - libell&eacute; - suffixe RSA</font> 
          </span>
        </div>
      <script type="text/javascript">
          var sprytooltip_info_credit = new Spry.Widget.Tooltip("info_credit", "#sprytrigger_info_credit", {offsetX:0, offsetY:20});
      </script>
    	<img src="images/b_aide_dotation_ul_cnrs_2015.png" height="16" id="sprytrigger_info_dotation">
      <div class="tooltipContent_cadre" id="info_dotation">
          <span class="noircalibri10">
          	A compter de 2015, les sources (autres que EOTP) sont globalis&eacute;es en cr&eacute;dits dotation &laquo; CNRS-UL &raquo; en demande d'une commande.<br>
            Seules les sources d'enveloppes de &laquo; Types de cr&eacute;dits &raquo; UL sont affich&eacute;es dans la partie demande.<br>
            Autrement dit : il est possible de cr&eacute;er des sources CNRS qui appara&icirc;tront en imputation r&eacute;lle mais pas en demande.<br>
            Les sources de dotation suivantes ne sont pas supprimables pour l'UL :<br>
            - &laquo; Types de cr&eacute;dits &raquo; <b>Direction</b>, enveloppe <b>Direction</b> source <b>Dotation</b><br>
            - &laquo; Types de cr&eacute;dits &raquo; <b>Recherche</b>, enveloppes <b>CID ISET SBS</b> source <b>Dotation</b><br>
            Il n'est plus possible de cr&eacute;er de &laquo; Types de cr&eacute;dits &raquo; et d'enveloppes
          </span>
        </div>
      <script type="text/javascript">
          var sprytooltip_info_dotation= new Spry.Widget.Tooltip("info_dotation", "#sprytrigger_info_dotation", {offsetX:0, offsetY:20});
      </script>


      <form name="<?php echo $form_edit_eotp_source_masse ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onSubmit="return controle_form_eotp_source_masse('<?php echo $form_edit_eotp_source_masse ?>')">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="action" value="<?php echo $action ?>">
      <input type="hidden" name="codeeotp" value="<?php echo $codeeotp ?>" >
      <input type="hidden" name="eotp_ou_source" value="<?php echo $eotp_ou_source ?>" >
      <input type="hidden" name="eotp_ancre" value="<?php echo $eotp_ancre; ?>">
			<input type="image" src="images/espaceur.gif" width="0" height="0">
     
      </td>
    </tr>
      <?php 
      if($eotp_ou_source=='eotp')
      { ?>      
     <tr>
      	<td>
          <table>
            <tr>
              <td>EOTP&nbsp;:&nbsp;
              <?php 
							echo $row_rs_eotp_source['libeotp'].'&nbsp;'; 
							if(count($tab_contrat)==0)
							{ ?> (Contrat &agrave; associer)
              <?php 
							}
              else
              {?><span class="bleucalibri10">
              <?php	echo count($tab_contrat)>=2?'des contrats : ':'du contrat : '; ?>
              </span>
              <?php 
							}               
              $first=true;
              foreach($tab_contrat as $codecontrat=>$un_tab_contrat)
              { echo $first?'':', ';
                echo $un_tab_contrat['nomrespscientifique'].' '.$un_tab_contrat['prenomrespscientifique'].' - '.$un_tab_contrat['acronyme'].' - '.$un_tab_contrat['libtheme'];
                $first=false;
              }
              ?>
              </td>
            </tr>
          </table>
        </td>
        </tr>
        <tr>
        <td>
         <table>
        	<tr>
          	<td class="bleucalibri10">
            	Cr&eacute;dits :
            </td>
            <td class="noircalibri10"> 
							<?php echo $tab_typecredit[$row_rs_eotp_source['codetypecredit']]['libtypecredit']?>
            </td>
          	<td class="bleucalibri10">
         			Centre Financier :
            </td>
            <td> 
              <select name="codecentrefinancier_reel" id="codecentrefinancier_reel" type="text" class="noircalibri10" onChange="affichestructure(this)">
              <?php $first=true;
							foreach($tab_centrefinancier_reel as $codecentrefinancier_reel=>$un_tab_centrefinancier_reel)
              { if($row_rs_eotp_source['codetypecredit']==$un_tab_centrefinancier_reel['codetypecredit'])
              	{?> <option value="<?php echo $codecentrefinancier_reel ?>" <?php echo $codecentrefinancier_reel==$row_rs_eotp_source['codecentrefinancier_reel']?'selected':''?>><?php echo $un_tab_centrefinancier_reel['libcentrefinancier_reel'] ?></option>
              	<?php	
									if($first)
									{ $codecentrefinancier_reel_defaut=$codecentrefinancier_reel;
										$first=false;
									}
								}
              }?>
              </select>
            </td>
          	<td class="bleucalibri10">
         			Centre de Co&ucirc;t :
            </td>
            <td> 
              <select name="codecentrecout_reel" id="codecentrecout_reel" type="text" class="noircalibri10" onChange="affichestructure(this)">
              <?php 
							if($row_rs_eotp_source['codecentrefinancier_reel']=='')
							{ $row_rs_eotp_source['codecentrefinancier_reel']=$codecentrefinancier_reel_defaut;
							}
							foreach($tab_centrecout_reel as $codecentrecout_reel=>$un_tab_centrecout_reel)
              {	if($row_rs_eotp_source['codecentrefinancier_reel']==$un_tab_centrecout_reel['codecentrefinancier_reel'])
              	{?> <option value="<?php echo $codecentrecout_reel ?>" <?php echo $codecentrecout_reel==$row_rs_eotp_source['codecentrecout_reel']?'selected':''?>><?php echo $un_tab_centrecout_reel['libcentrecout_reel'] ?></option>
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
        <td>
        	<table>
          	<tr>
              <td class="bleucalibri10">Distinguer masses : <input type="checkbox" name="distinguermasse" <?php echo $row_rs_eotp_source['distinguermasse']=='oui'?'checked':'' ?>>
              </td>
               <td><span class="bleucalibri10">date d&eacute;but eotp :</span>&nbsp;
                <input name="datedeb_eotp_jj" type="text" class="noircalibri10" id="datedeb_eotp_jj" value="<?php echo substr($row_rs_eotp_source['datedeb_eotp'],8,2); ?>" size="2" maxlength="2">
                <input name="datedeb_eotp_mm" type="text" class="noircalibri10" id="datedeb_eotp_mm" value="<?php echo substr($row_rs_eotp_source['datedeb_eotp'],5,2); ?>" size="2" maxlength="2">
                <input name="datedeb_eotp_aaaa" type="text" class="noircalibri10" id="datedeb_eotp_aaaa" value="<?php echo substr($row_rs_eotp_source['datedeb_eotp'],0,4); ?>" size="4" maxlength="4">
               </td>
               <td><span class="bleucalibri10">date fin eotp :</span>&nbsp;
                <input name="datefin_eotp_jj" type="text" class="noircalibri10" id="datefin_eotp_jj" value="<?php echo substr($row_rs_eotp_source['datefin_eotp'],8,2); ?>" size="2" maxlength="2">
                <input name="datefin_eotp_mm" type="text" class="noircalibri10" id="datefin_eotp_mm" value="<?php echo substr($row_rs_eotp_source['datefin_eotp'],5,2); ?>" size="2" maxlength="2">
                <input name="datefin_eotp_aaaa" type="text" class="noircalibri10" id="datefin_eotp_aaaa" value="<?php echo substr($row_rs_eotp_source['datefin_eotp'],0,4); ?>" size="4" maxlength="4">
               </td>
             </tr>
          </table>
        </td>
  		</tr>
      <?php 
      }
      else // source dotation
      { ?><tr>
      	<td>
          <table>
        	<tr>
          	<td>
          	<table>
            	<tr>
                <td class="bleucalibri10">
                  Cr&eacute;dits :
                </td>
                <td> 
									<?php 
                  if(isset($row_rs_eotp_source['supprimable']) && $row_rs_eotp_source['supprimable']=='non')
                  { echo $tab_typecredit[$row_rs_eotp_source['codetypecredit']]['libtypecredit'];
                  }
                  else
                  {?>
                    <select name="codetypecredit" id="codetypecredit" type="text" class="noircalibri10" onChange="affichestructure(this);affiche_resp_credit();affiche_libelle_source()">
                    <?php 
                    foreach($tab_typecredit as $codetypecredit=>$un_tab_typecredit)
                    {?> <option value="<?php echo $codetypecredit ?>" <?php echo $codetypecredit==$row_rs_eotp_source['codetypecredit']?'selected':''?>><?php echo $un_tab_typecredit['libtypecredit'] ?></option>
                    <?php 
                    }?>
                    </select>
                  <?php 
									}?>
                </td>
                <td class="bleucalibri10">
                  Centre Financier :
                </td>
                <td> 
                  <select name="codecentrefinancier_reel" id="codecentrefinancier_reel" type="text" class="noircalibri10" onChange="affichestructure(this);affiche_libelle_source()">
                  <?php $first=true;
                  foreach($tab_centrefinancier_reel as $codecentrefinancier_reel=>$un_tab_centrefinancier_reel)
                  { if($row_rs_eotp_source['codetypecredit']==$un_tab_centrefinancier_reel['codetypecredit'])
                    {?> <option value="<?php echo $codecentrefinancier_reel ?>" <?php echo $codecentrefinancier_reel==$row_rs_eotp_source['codecentrefinancier_reel']?'selected':''?>><?php echo $un_tab_centrefinancier_reel['libcentrefinancier_reel'] ?></option>
                    <?php	
                      if($first)
                      { $codecentrefinancier_reel_defaut=$codecentrefinancier_reel;
                        $first=false;
                      }
                    }
                  }?>
                  </select>
                </td>
                <td class="bleucalibri10">
                  Centre de Co&ucirc;t :
                </td>
                <td> 
                  <select name="codecentrecout_reel" id="codecentrecout_reel" type="text" class="noircalibri10" onChange="affichestructure(this);affiche_libelle_source()">
                  <?php // par defaut de centre financier, on prend le premier de la liste des centres financiers ci-dessus 
									if($row_rs_eotp_source['codecentrefinancier_reel']=='')
									{ $row_rs_eotp_source['codecentrefinancier_reel']=$codecentrefinancier_reel_defaut;
									}
                  foreach($tab_centrecout_reel as $codecentrecout_reel=>$un_tab_centrecout_reel)
                  {	if($row_rs_eotp_source['codecentrefinancier_reel']==$un_tab_centrecout_reel['codecentrefinancier_reel'])
                    {?> <option value="<?php echo $codecentrecout_reel ?>" <?php echo $codecentrecout_reel==$row_rs_eotp_source['codecentrecout_reel']?'selected':''?>><?php echo $un_tab_centrecout_reel['libcentrecout_reel'] ?></option>
                    <?php 
                    }
                  }?>
                  </select>
                </td>
              </tr>
              	<tr>
          	<td colspan="2">
            </td>
          	<td class="bleucalibri10">
            	Type cr&eacute;dits :
            </td>
            <td class="noirgrascalibri10">
            	<?php if(isset($row_rs_eotp_source['supprimable']) && $row_rs_eotp_source['supprimable']=='non')
              { echo $tab_centrefinancier[$row_rs_eotp_source['codecentrefinancier']]['libcentrefinancier'];
              }
              else
              {?>
                <select name="codecentrefinancier" id="codecentrefinancier" type="text" class="noircalibri10" onChange="affichestructure(this);affiche_resp_credit()">
                <?php 
                foreach($tab_centrefinancier as $codecentrefinancier=>$un_tab_centrefinancier)
                {	if($row_rs_eotp_source['codetypecredit']==$un_tab_centrefinancier['codetypecredit'])
                  {?> <option value="<?php echo $codecentrefinancier ?>" <?php echo $codecentrefinancier==$row_rs_eotp_source['codecentrefinancier']?'selected':''?>><?php echo $un_tab_centrefinancier['libcentrefinancier'] ?></option>
                  <?php
                  }
                }?>
                </select>
               <?php
						  }?>
            </td>
          	<td class="bleucalibri10">
            	Enveloppe :
            </td>
            <td class="noirgrascalibri10">
            	<?php 
							if(isset($row_rs_eotp_source['supprimable']) && $row_rs_eotp_source['supprimable']=='non')
              { echo $tab_centrecout[$row_rs_eotp_source['codecentrecout']]['libcentrecout'];
              }
              else
              {?>
                <select name="codecentrecout" id="codecentrecout" type="text" class="noircalibri10" onChange="affichestructure(this);affiche_resp_credit()">
                <?php
                foreach($tab_centrecout as $codecentrecout=>$un_tab_centrecout)
                {	if($row_rs_eotp_source['codecentrefinancier']==$un_tab_centrecout['codecentrefinancier'])
                  {?> <option value="<?php echo $codecentrecout ?>" <?php echo $codecentrecout==$row_rs_eotp_source['codecentrecout']?'selected':''?>><?php echo $un_tab_centrecout['libcentrecout'] ?></option>
                <?php
                  }
                }?>
                </select>
              <?php 
							}?>
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
                   <td class="bleucalibri10">Type source : 
                  </td>
                  <td>
                     <select name="codetypesource" id="codetypesource" type="text" class="noircalibri10" onChange="affiche_libelle_source();">
                    <?php 
                      foreach($tab_typesource as $codetypesource=>$un_tab_typesource)
                      {?> <option value="<?php echo $codetypesource ?>" <?php echo $codetypesource==$row_rs_eotp_source['codetypesource']?'selected':''?> ><?php echo $un_tab_typesource['libtypesource'] ?></option>
                      <?php 
                      }?>
                    </select>
                  </td>
                  <td class="bleucalibri10">Libell&eacute; : 
                  </td>
                  <td>
                    <input type="text" class="noircalibri10" name="libcourt" id="libcourt" value="<?php echo $row_rs_eotp_source['libcourt'] ?>" onChange="affiche_libelle_source()" onKeyUp="affiche_libelle_source()">
                  </td>
                  <td class="bleucalibri10">
                     Responsable de cr&eacute;dits : 
                  </td>
                  <td>
                      <select name="coderespaci" id="coderespaci" type="text" class="noircalibri10" onChange="affiche_libelle_source();affiche_resp_credit();">
                    <?php 
                      foreach($tab_respscientifique as $coderespscientifique=>$un_tab_respscientifique)
                      {?> <option value="<?php echo $coderespscientifique ?>" <?php echo $coderespscientifique==$row_rs_eotp_source['coderespaci']?'selected':''?> ><?php echo $un_tab_respscientifique['nom'] ?>&nbsp;<?php echo $un_tab_respscientifique['prenom'] ?></option>
                      <?php 
                      }?>
                    </select>
                  </td>
                  <td>
                    <div id='resp_credit' class="<?php echo $row_rs_eotp_source['coderespaci']==''?'affiche':'cache' ?>" style="color:#F00">
                      <?php 
                        if($row_rs_eotp_source['coderespaci']=='')
                        { if($tab_centrecout[$row_rs_eotp_source['codecentrecout']]['esttheme']=='oui' && $tab_centrecout[$row_rs_eotp_source['codecentrecout']]['estdirection']=='non')
													{ echo 'Resp. '.$tab_centrecout[$row_rs_eotp_source['codecentrecout']]['libcentrecout'];
													}
													else
													{ echo 'Directeur'; 
													}?>
                      <?php 
                        }
                        ?>
            			</div>
                  </td>
                </tr>
        			</table>
            </td>
          </tr>
          <tr>
          <td>
            <div style="float: left; color:#F90">Libell&eacute; visible lors de la saisie d'une commande (en demande, le suffixe  "Centre de Co&ucirc;t" n'est pas affich&eacute;) :&nbsp;</div> 
            <div id="libelle_source" style="float:left;background-color:#CCC;">
              <?php 
								$tab_construitsource=array('codetypesource'=>$row_rs_eotp_source['codetypesource'],'libtypesource'=>$tab_typesource[$row_rs_eotp_source['codetypesource']]['libtypesource'],
																					'libsource'=>$row_rs_eotp_source['libcourt'],'libcentrecout_reel'=>$row_rs_eotp_source['libcentrecout_reel'],
																					'coderespscientifique'=>$row_rs_eotp_source['coderespaci'],'nomrespscientifique'=>$tab_respscientifique[$row_rs_eotp_source['coderespaci']]['nom'],
																					'prenomrespscientifique'=>$tab_respscientifique[$row_rs_eotp_source['coderespaci']]['prenom'],'codetypecredit'=>$row_rs_eotp_source['codetypecredit']);
  							echo construitlibsource($tab_construitsource);
                ?>
            </div>

          </td>
          </tr>
        </table>
       </td>
	<tr>
  	<td class="bleucalibri10">Distinguer masses : <input type="checkbox" name="distinguermasse" <?php echo $row_rs_eotp_source['distinguermasse']=='oui'?'checked':'' ?>>
    </td>
  </tr>
      <?php 
      }?>

  <tr>
    <td><span class="mauvegrascalibri11">Liste des op&eacute;rations en recettes</span>
    		<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_operation">
        <div class="tooltipContent_cadre" id="info_operation">
            <span class="noircalibri10">
            	Les lignes sont tri&eacute;es par date.<br>
              Une ligne doit contenir au moins un montant diff&eacute;rent de 0, sinon elle est supprim&eacute;e.<br>
              Une nouvelle ligne vierge est propos&eacute;e en permanence, la date est initialis&eacute;e &agrave; &raquo;aujourd&rsquo;hui&laquo;. 
            </span>
          </div>
        <script type="text/javascript">
            var sprytooltip_info_operation = new Spry.Widget.Tooltip("info_operation", "#sprytrigger_info_operation", {offsetX:0, offsetY:20});
          </script>
    </td>
  </tr>
	<tr>
		<td>
			<table border="0" class="data" id="table_results">
				<tr class="head">
					<td nowrap class="bleucalibri10">Fonctionnement
				  </td>
					<td nowrap class="bleucalibri10">Salaire
				  </td>
					<td nowrap class="bleucalibri10">Investissement
					</td>
					<td nowrap class="bleucalibri10">Date
					</td>
					<td nowrap class="bleucalibri10">Note
					</td>
					<td nowrap class="bleucalibri10">Action
          </td>
	      </tr>
				<?php 	
				$class="even";
				foreach($tab_eotp_source_montant as $codeoperation=>$un_eotp_source_montant)
				{ ?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<td nowrap><input type="text" name="montantfonctionnement#<?php echo $codeoperation ?>"  style="text-align:right" class="noircalibri10" value="<?php echo $un_eotp_source_montant['montantfonctionnement']!=''?sprintf('%01.2f',$un_eotp_source_montant['montantfonctionnement']):'' ?>" size="12" maxlength="12"></td>
					<td nowrap><input type="text" name="montantsalaire#<?php echo $codeoperation ?>" style="text-align:right" class="noircalibri10" value="<?php echo $un_eotp_source_montant['montantsalaire']!=''?sprintf('%01.2f',$un_eotp_source_montant['montantsalaire']):'' ?>" size="12" maxlength="12"></td>
					<td nowrap><input type="text" name="montantinvestissement#<?php echo $codeoperation ?>" style="text-align:right" class="noircalibri10" value="<?php echo $un_eotp_source_montant['montantinvestissement']!=''?sprintf('%01.2f',$un_eotp_source_montant['montantinvestissement']):'' ?>" size="12" maxlength="12"></td>
          <td nowrap>
                    	<input type="text" name="dateoperation_jj#<?php echo $codeoperation?>" class="noircalibri10" value="<?php echo substr($un_eotp_source_montant['dateoperation'],8,2) ?>" size="2" maxlength="2">
                    	<input type="text" name="dateoperation_mm#<?php echo $codeoperation?>" class="noircalibri10" value="<?php echo substr($un_eotp_source_montant['dateoperation'],5,2) ?>" size="2" maxlength="2">
                    	<input type="text" name="dateoperation_aaaa#<?php echo $codeoperation?>" class="noircalibri10" value="<?php echo substr($un_eotp_source_montant['dateoperation'],0,4) ?>" size="2" maxlength="4">
          </td>
					<td nowrap><input type="text" name="note#<?php echo $codeoperation ?>" class="noircalibri10" value="<?php echo htmlentities($un_eotp_source_montant['note']) ?>" size="80"></td>
          <td><input type="image" name="supprimer#<?php echo $codeoperation?>##" src="images/b_drop.png" onClick="javascript: return confirm('Supprimer cette ligne ?')"></td>
				</tr>
				<?php
				}?>
        <tr>
        	<td colspan="6"><input type="submit" name="enregistrer" class="noircalibri10" value="Enregistrer"></td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>
</body>
</html>
<?php
if(isset($rs))mysql_free_result($rs);
if(isset($rs_eotp))mysql_free_result($rs_eotp);
?>
