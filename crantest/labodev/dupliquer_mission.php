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
$message_resultat_affiche='';
$form_dupliquer_mission = "dupliquer_mission";
$_SESSION['codemission_a_dupliquer']=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:"");
$codeagent=isset($_GET['codeagent'])?$_GET['codeagent']:(isset($_POST['codeagent'])?$_POST['codeagent']:"");
//PG 20151117
// protection contre une erreur qui dupliquerait l'enreg. ''
if($_SESSION['codemission_a_dupliquer']=='')
{ $erreur.="Tentative de duplication de mission sans n&deg; : quittez cet &eacute;cran et recommencez";
	$msg="";
	foreach($_REQUEST as $key => $val)
	{ $msg.='<br>'.$key.'='.$val;
	}
	mail_adminbd('12+ Erreur',$_SERVER['PHP_SELF'],'Tentative de modification de mission sans n&deg; '.$msg);
}
//PG 20151117


$tab_commandes_a_dupliquer=array();//commandes a dupliquer
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

if(isset($_POST['codeagent']) && $_POST['codeagent']!='')//les champs concernant l'agent ont été mis disabled donc non envoyes
{ $query_rs_agent="SELECT nom,prenom,date_naiss,email,telport, codeetab,codecatmissionnaire,if(cat.codecat='04' or cat.codecat='05' or cat.codecat='07','oui','non') as estetudiant". 
									" FROM individu,individusejour,individuemploi,corps,cat".
									" where individu.codeindividu=individusejour.codeindividu and individu.codeindividu=individuemploi.codeindividu".
									" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat". 
									" and individu.codeindividu=".GetSQLValueString($_POST['codeagent'], "text").
									" order by datedeb_sejour" ; //le tri par datedeb_sejour permet d'avoir le dernier sejour en cas de doublon !!
									//" and ".periodeencours('datedeb_sejour','datefin_sejour').
									//" and ".periodeencours('datedeb_emploi','datefin_emploi');
	$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
	while($row_rs_agent=mysql_fetch_assoc($rs_agent))// 
	{ $_POST['nom']=$row_rs_agent['nom'];
		$_POST['prenom']=$row_rs_agent['prenom'];
		$_POST['date_naiss_jj']=substr($row_rs_agent['date_naiss'],8,2);
		$_POST['date_naiss_mm']=substr($row_rs_agent['date_naiss'],5,2);
		$_POST['date_naiss_aaaa']=substr($row_rs_agent['date_naiss'],0,4);
		$_POST['email']=$row_rs_agent['email'];
		$_POST['codecatmissionnaire']=$row_rs_agent['codecatmissionnaire'];
		if($row_rs_agent['codeetab']!="07" && $row_rs_agent['estetudiant']=='non')
		{ $_POST['codecatmissionnaire']='05';//codecatmissionnaire=exterieur invite
		}
	}
}

if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_dupliquer_mission)) 
{ // on ne positionne pas erreur pour ne pas obliger le user a mettre une adresse (om pas sur d'etre fait)
	$tab_controle_et_format=array();
	$erreur.=controle_form_dupliquer_mission($_POST,$tab_controle_et_format);
	//$erreur.='<br>erreur forcée';
 	if($erreur=='')
	{ $affiche_succes=true;
		$message_resultat_affiche='Duplication effectu&eacute;e avec succ&egrave;s.';
		mysql_query("START TRANSACTION") or  die(mysql_error());
		$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='mission'") or  die(mysql_error());
		$row_seq_number=mysql_fetch_assoc($rs_seq_number);
		$codemission=$row_seq_number['currentnumber'];
		$codemission=str_pad((string)((int)$codemission+1), 5, "0", STR_PAD_LEFT);  
		$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codemission, "text")." where nomtable='mission'") or  die(mysql_error());
		mysql_query("COMMIT") or  die(mysql_error());
		mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
		foreach(array('mission','missionetape') as $table)
		{ $rs_mission_a_dupliquer=mysql_query("SELECT * FROM ".$table." WHERE codemission=".GetSQLValueString($_SESSION['codemission_a_dupliquer'],"text")) or  die(mysql_error());
			while($row_rs_mission_a_dupliquer=mysql_fetch_assoc($rs_mission_a_dupliquer))
			{ $rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
				$first=true;
				$liste_champs="";$liste_val="";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ $Field=$row_rs_fields['Field'];
					$liste_champs.=($first?"":",").$row_rs_fields['Field'];
					$liste_val.=($first?"":",");
					$first=false;
					if($Field=='codemission')
					{ $liste_val.=GetSQLValueString($codemission, "text");
					}
					else if($Field=='codeagent')
					{ $liste_val.=GetSQLValueString($codeagent, "text");
					}
					else if($table=='mission' && in_array($Field,array('nom','prenom','telport','email','adresse_pers','codepostal_pers','ville_pers','codepays_pers','adresse_admin','codecatmissionnaire')))
					{ $liste_val.=GetSQLValueString($_POST[$Field], "text");
					}
					else if($table=='mission' && in_array($Field,array('nummission','codeabonnementtrain','numcarteabonneairfrance','dateabonneairfranceexpire',
																														'numcartefideliteavion','compagniecartefideliteavion','numimmatriculation','assureetranger',
																														'numcarteabonnetrain','numcarteabonneavion','dateabonnetrainexpire', 'dateabonneavionexpire',
																														'numcartefidelitetrain','zonegeoabonnetrain','zonegeoabonneavion')))
					{ $liste_val.="''";
					}
					else if($table=='mission' && $row_rs_fields['Field']=='date_naiss')
					{ $liste_val.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
					}
					else
					{ $liste_val.=GetSQLValueString($row_rs_mission_a_dupliquer[$Field], "text");
					}
				}//fin while
				$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
				mysql_query($updateSQL) or  die(mysql_error());
				$cmd_ancre='M'.$codemission;
			}
		}
		// commandes eventuelles associees a la mission : 
		$tab_commandes_a_dupliquer=$_POST['tab_commandes_a_dupliquer'];
		foreach($tab_commandes_a_dupliquer as $codecommande_a_dupliquer=>$une_commande_a_dupliquer)
		{	if(isset($une_commande_a_dupliquer['codecommande']))
			{ mysql_query("START TRANSACTION") or  die(mysql_error());
				$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='commande'") or  die(mysql_error());
				$row_seq_number=mysql_fetch_assoc($rs_seq_number);
				$codecommande=$row_seq_number['currentnumber'];
				$codecommande=str_pad((string)((int)$codecommande+1), 5, "0", STR_PAD_LEFT);  
				$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecommande, "text")." where nomtable='commande'") or  die(mysql_error());
				mysql_query("COMMIT") or  die(mysql_error());
				mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
				foreach(array('commande','commandeimputationbudget') as $table)
				{ $rs_commande_a_dupliquer=mysql_query("SELECT * FROM ".$table." WHERE codecommande=".GetSQLValueString($codecommande_a_dupliquer,"text")) or  die(mysql_error());
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
							else if($row_rs_fields['Field']=='codereferent')//pas de codeagent pour cmd => codeuser
							{ $liste_val.=GetSQLValueString($codeagent==''?$codeuser:$codeagent, "text");
							}
							else if($row_rs_fields['Field']=='codemission')
							{ $liste_val.=GetSQLValueString($codemission, "text");
							}
							/* else if($row_rs_fields['Field']=='numcommande')
							{ $liste_val.="''";
							}
							else if($row_rs_fields['Field']=='codevisaannulemax')
							{ $liste_val.="''";
							} */
							else if(in_array($row_rs_fields['Field'],array('numcommande','codevisaannulemax','dateenvoi_etatfrais')))
							{ $liste_val.="''";
							}
							else if(($row_rs_fields['Field']=='montantengage' || $row_rs_fields['Field']=='montantpaye') && $row_rs_commande_a_dupliquer['virtuel_ou_reel']=='1')
							{ $liste_val.="''";
							}  
							else if($row_rs_fields['Field']=='libfournisseur')
							{ $liste_val.=GetSQLValueString($une_commande_a_dupliquer['libfournisseur'], "text");
							}
							else
							{ $liste_val.=GetSQLValueString($row_rs_commande_a_dupliquer[$row_rs_fields['Field']], "text");
							}
						}//fin while
						$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
						mysql_query($updateSQL) or  die(mysql_error());
					}
				}
			}
		}
		//Maj individu si agent labo
		//certains champs concernant l'agent ont été mis disabled donc non envoyes
		// c'est le champ telport de individu qui prend la valeur de tel
		if(isset($_POST['codeagent']) && $_POST['codeagent']!='')
		{ mysql_query("update individu set telport=".GetSQLValueString($_POST["telport"],"text").",".
									($_POST["adresse_pers"]==""?"":" adresse_pers=".GetSQLValueString($_POST["adresse_pers"],"text").",").
									($_POST["codepostal_pers"]==""?"":" codepostal_pers=".GetSQLValueString($_POST["codepostal_pers"],"text").",").
									($_POST["ville_pers"]==""?"":" ville_pers=".GetSQLValueString($_POST["ville_pers"],"text").",").
									($_POST["codepays_pers"]==""?"":" codepays_pers=".GetSQLValueString($_POST["codepays_pers"],"text").",").
									" adresse_admin=".GetSQLValueString($_POST["adresse_admin"],"text").
									" where codeindividu=".GetSQLValueString($_POST["codeagent"],"text")) or die(mysql_error());
		}
	}
}
 
 
// ------------------------------------------ FORMULAIRE D'ENVOI DES DONNES ---------------------------------------------------------------//
//Informations de mission (un enreg. vide dans mission pour "creer")
$query_mission =	"SELECT mission.*".
									" FROM mission".
									" WHERE codemission=".GetSQLValueString($_SESSION['codemission_a_dupliquer'],"text");
$rs_mission=mysql_query($query_mission) or die(mysql_error());
$row_rs_mission=mysql_fetch_assoc($rs_mission);
$nom=$row_rs_mission['nom'];
$prenom=$row_rs_mission['prenom'];
$row_rs_mission['codeagent']='';
$row_rs_mission['nom']='';
$row_rs_mission['prenom']='';
$row_rs_mission['date_naiss']='';
$row_rs_mission['email']='';
$row_rs_mission['telport']='';
$row_rs_mission['adresse_pers']='';
$row_rs_mission['codepostal_pers']='';
$row_rs_mission['ville_pers']='';
$row_rs_mission['codepays_pers']='';
$row_rs_mission['adresse_admin']='';
$row_rs_mission['codecatmissionnaire']='05';
$tab_commande=array();
$query_commande="select * from commande".
								" where codemission=".GetSQLValueString($_SESSION['codemission_a_dupliquer'],"text");
$rs_commande=mysql_query($query_commande) or die(mysql_error());
while($row_rs_commande=mysql_fetch_assoc($rs_commande))
{ $tab_commande[$row_rs_commande['codecommande']]=$row_rs_commande;
}

if($erreur!='')//valeurs du POST a la place de certaines données de mission qui n'ont pas été mises a jour.
{	foreach(array('codeagent','nom','prenom','email','telport','adresse_pers','codepostal_pers','ville_pers','codepays_pers','adresse_admin','codecatmissionnaire') as $champ)
	{ $row_rs_mission[$champ]=$_POST[$champ];
	}
	$row_rs_mission['date_naiss']=jjmmaaaa2date($_POST['date_naiss_jj'],$_POST['date_naiss_mm'],$_POST['date_naiss_aaaa']);
	$tab_commandes_a_dupliquer=$_POST['tab_commandes_a_dupliquer'];
	foreach($tab_commande as $codecommande=>$row_rs_commande)
	{ $une_commande_a_dupliquer=$tab_commandes_a_dupliquer[$codecommande];
		if(isset($une_commande_a_dupliquer['codecommande']))
		{ $tab_commande[$codecommande]['libfournisseur']=$une_commande_a_dupliquer['libfournisseur'];
		}
	}
}

$tab_agent=array();
$query_rs_agent="SELECT distinct individu.codeindividu as codeagent,nom,prenom,date_naiss,adresse_pers,codepostal_pers,ville_pers,codepays_pers,adresse_admin,".
								" telport,email,datedeb_sejour,codeetab,if(cat.codecat='04' or cat.codecat='05' or cat.codecat='07','oui','non') as estetudiant,miss_catmissionnaire.codecatmissionnaire as codecatmissionnaire,".
								" miss_catmissionnaire.libcourt as libcatmissionnaire". 
								" FROM individu,individusejour,individuemploi,corps,cat,miss_catmissionnaire".
								" where individu.codeindividu=individusejour.codeindividu and individu.codeindividu=individuemploi.codeindividu".
								" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat". 
								" and corps.codecatmissionnaire=miss_catmissionnaire.codecatmissionnaire and corps.codecatmissionnaire<>''".
								" UNION".
								" select '' as codeagent,'' as nom,'' as prenom,'' as date_naiss, '' as adresse_pers,'' as codepostal_pers,'' as ville_pers,'' as codepays_pers,'' as adresse_admin,".
								" '' as telport,'' as email,'' as datedeb_sejour,'' as codeetab,'non' as estetudiant,'' as codecatmissionnaire,'' as libcatmissionnaire".
								" ORDER BY nom,prenom,datedeb_sejour asc"; //le tri par datedeb_sejour permet d'avoir le dernier sejour en cas de doublon !!

$rs_agent=mysql_query($query_rs_agent) or die(mysql_error());
while($row_rs_agent=mysql_fetch_assoc($rs_agent))
{ $tab_agent[$row_rs_agent['codeagent']]=$row_rs_agent;
}

$query_rs_miss_catmissionnaire = "SELECT codecatmissionnaire, liblong as libcatmissionnaire FROM miss_catmissionnaire ORDER BY numordre ASC";
$rs_miss_catmissionnaire = mysql_query($query_rs_miss_catmissionnaire) or die(mysql_error());

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
var frm=document.forms["<?php echo $form_dupliquer_mission ?>"];
var tab_agent=new Array();
<?php
foreach($tab_agent as $codeagent=>$un_agent)
{?>
	tab_agent["<?php echo $codeagent ?>"]=new Array();
	<?php
	foreach($un_agent as $champ=>$valchamp)
	{ if($champ=="date_naiss")
		{?>
			tab_agent["<?php echo $codeagent ?>"]["date_naiss_jj"]="<?php echo substr($valchamp,8,2); ?>";
			tab_agent["<?php echo $codeagent ?>"]["date_naiss_mm"]="<?php echo substr($valchamp,5,2); ?>";
			tab_agent["<?php echo $codeagent ?>"]["date_naiss_aaaa"]="<?php echo substr($valchamp,0,4); ?>";
		<?php 
		}
		else
		{?>
			tab_agent["<?php echo $codeagent ?>"]["<?php echo $champ ?>"]="<?php echo str_replace(array(chr(10),chr(13),'"'),array('\n','\r','\"'),str_replace(chr(92),chr(92).chr(92),$valchamp));?>";
		<?php 
		}
	}
}?>
function detailagent(codeagent)
{ var frm=document.forms["<?php echo $form_dupliquer_mission ?>"];
	var catmissionnairelist=frm.elements["codecatmissionnaire"];
	frm.elements["nom"].value=tab_agent[codeagent]["nom"];
	frm.elements["prenom"].value=tab_agent[codeagent]["prenom"];
	frm.elements["date_naiss_jj"].value=tab_agent[codeagent]["date_naiss_jj"];
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
	//positionne la categorie missionnaire
	for(indexcatmissionnaire=0;indexcatmissionnaire<=catmissionnairelist.options.length-1;indexcatmissionnaire++)
	{ if(catmissionnairelist.options[indexcatmissionnaire].value==frm.elements["codecatmissionnaire"].value)
		{ catmissionnairelist.selectedIndex=indexcatmissionnaire;
		}		
	}
	if(frm.elements["codeagent"].value=='')
	{ frm.elements["nom"].disabled=false;
		frm.elements["prenom"].disabled=false;
		frm.elements["date_naiss_jj"].disabled=false;
		frm.elements["date_naiss_mm"].disabled=false;
		frm.elements["date_naiss_aaaa"].disabled=false;
		frm.elements["email"].disabled=false;
		frm.elements["codecatmissionnaire"].disabled=false;
	}
	else
	{ frm.elements["nom"].disabled=true;
		frm.elements["prenom"].disabled=true;
		frm.elements["date_naiss_jj"].disabled=true;
		frm.elements["date_naiss_mm"].disabled=true;
		frm.elements["date_naiss_aaaa"].disabled=true;
		frm.elements["email"].disabled=true;
		frm.elements["codecatmissionnaire"].disabled=true;
	}
	if(frm.elements["codecatmissionnaire"].value=='05')//exterieur
	{ document.getElementById('textetypeom').innerHTML="DEMANDE D'ORDRE DE MISSION&nbsp;pour les intervenants ext&eacute;rieurs &agrave; l&rsquo;UL";
		document.getElementById('celluleestsansfrais').className="cache";
		frm.elements["estsansfrais"].checked=false;
		
	}
	else
	{ document.getElementById('textetypeom').innerHTML="DEMANDE D'ORDRE DE MISSION&nbsp;valant autorisation d&rsquo;absence";
		document.getElementById('celluleestsansfrais').className="affiche";
	}
}
</script>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?"\\n":'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;","\\n"."Attention.".$warning)) ?>')"<?php }?>>
<form name="<?php echo $form_dupliquer_mission ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data" onSubmit="//return controle_form_dupliquer_mission('<?php echo $form_dupliquer_mission ?>')">
<input type="hidden" name="codemission" value="<?php echo $_SESSION['codemission_a_dupliquer'] ?>" >
<input type="hidden" name="MM_update" value="<?php echo $form_dupliquer_mission ?>">
<input type="hidden" name="cmd_ancre" value="<?php echo $cmd_ancre; ?>">
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Duplication de Commande','lienretour'=>'gestioncommandes.php?cmd_ancre='.$cmd_ancre,'texteretour'=>'Retour &agrave; la gestion des commandes',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center">
      <table border="0">
        <tr>
          <td align="center">
          <span class="bleugrascalibri11">Duplication de la mission de </span>
          <span class="noircalibri11"><?php echo htmlspecialchars($nom)." ".htmlspecialchars($prenom); ?> : <?php echo htmlspecialchars($row_rs_mission['motif']) ; ?></span>
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
            <table border="0">
              <tr>
                <td>
                  <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_mission">
                  <div class="tooltipContent_cadre" id="info_info_mission">
                    <span class="noircalibri10">
                    Champs "transport-h&eacute;bergement" non dupliqu&eacute;s : Abonnements, v&eacute;hicule et champs associ&eacute;s<br>
                    </span>
                  </div>
                  <script type="text/javascript">
                    var sprytooltip_info_mission = new Spry.Widget.Tooltip("info_info_mission", "#sprytrigger_info_mission", {useEffect:"blind", offsetX:0, offsetY:20});
                  </script>
                </td>
                <td class="mauvecalibri10">Les pi&egrave;ces jointes &eacute;ventuelles et les visas ne sont pas dupliqu&eacute;s. </td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="mauvecalibri10">Les informations relatives au "transport-h&eacute;bergement" sont dupliqu&eacute;es&nbsp;sauf les informations propres &agrave; l&rsquo;agent.
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
            <table>
              <tr>
                <td><span class="bleugrascalibri10">Demandeur :</span></td>
                <td>
                <?php 
                $disableddetailagent='';
                if($row_rs_mission['codeagent']!='')
                { $disableddetailagent='disabled';
                }?>
                <select name="codeagent" class="noircalibri10" id="codeagent" onChange="detailagent(this.value)">
                    <?php
                foreach($tab_agent as $codeagent=>$row_rs_agent)
                { ?>
                  <option value="<?php echo $row_rs_agent['codeagent'] ?>"<?php echo $row_rs_mission['codeagent']==$row_rs_agent['codeagent']?' selected  ':'' ?>><?php echo $row_rs_agent['nom'].' '.$row_rs_agent['prenom'] ?></option>
                <?php
                } ?>
                </select>
              </td>
            </tr>
          </table>
     </td>
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
                  <input name="nom" type="text" class="noircalibri10" id="nom" value="<?php echo htmlspecialchars($row_rs_mission['nom']); ?>" size="20" maxlength="30" <?php echo $disableddetailagent ?>>
                  <input name="prenom" type="text" class="noircalibri10" id="prenom" value="<?php echo htmlspecialchars($row_rs_mission['prenom']); ?>" size="20" maxlength="20" <?php echo $disableddetailagent  ?>>
                </td>
                <td width="12%" valign="top" nowrap class="bleugrascalibri10">Type d'agent<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
                <td width="19%" valign="top" nowrap>
                  <select name="codecatmissionnaire" class="noircalibri10" id="codecatmissionnaire" <?php echo $disableddetailagent ?>>
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
              <tr>
                <td colspan="4"><span class="rougecalibri9"><sup>Au moins une adresse obligatoire</sup></span></td>
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
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
		<?php 
    if(count($tab_commande)>0)
    { ?>
    <tr>
      <td class="bleugrascalibri10">S&eacute;lectionnez les commandes &agrave; dupliquer.
      </td>
    </tr>
    <tr>
      <td class="mauvecalibri10">Le n&deg; de commande et les informations relatives aux services faits (MIGO) et aux liquidations ne sont pas dupliqu&eacute;s.
      </td>
    </tr>
    <tr>
      <td>
        <table>
        <?php
        foreach($tab_commande as $codecommande=>$row_rs_commande)							
        { ?>
          <tr>
            <td><input type="checkbox" name="codecommande#<?php echo $row_rs_commande['codecommande']?>" checked></td>
            <td><?php echo $row_rs_commande['codecommande']?> : <?php echo htmlspecialchars($row_rs_commande['objet'])?></td>
            <td class="bleucalibri11">Fournisseur<span class="rougecalibri9"><sup>*</sup></span> :&nbsp;</td>
            <td><input name="libfournisseur#<?php echo $row_rs_commande['codecommande']?>"  type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_commande['libfournisseur']) ?>" size="30" maxlength="200"></td>
          </tr>
          <?php
        }
        ?>
        </table>
      </td>
    </tr>
    <?php 
    }?>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
      	<table>
      	<tr>
        	<td><input name="submit_dupliquer" type="submit" class="noircalibri10" id="submit_dupliquer" value="Dupliquer">
    			</td>
							</form>
          	<form method="get" action="gestioncommandes.php">
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
if(isset($rs_mission_a_dupliquer))mysql_free_result($rs_mission_a_dupliquer);
if(isset($rs_agent))mysql_free_result($rs_agent);
if(isset($rs_mission))mysql_free_result($rs_mission);
if(isset($rs_missionetape))mysql_free_result($rs_missionetape);
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
?>
