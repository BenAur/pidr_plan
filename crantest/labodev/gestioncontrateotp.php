<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
if($admin_bd)
{  /*foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	} */ 
}
$aujourdhui=date('Ymd');
$nbcol=23;//nb col hormis celles par annee
$tabtheme=array();
$tabcont_type=array();
$tabcont_orggest=array();
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$codecontrat=isset($_GET['codecontrat'])?$_GET['codecontrat']:(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");

// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$peut_etre_admin=estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser);
if(!isset($_SESSION['codecontrat'])) { $_SESSION['codecontrat']=$codecontrat;}
if(!isset($_SESSION['b_contrateotp_voir_ul'])) { $_SESSION['b_contrateotp_voir_ul']=true;}
if(!isset($_SESSION['b_contrateotp_voir_cnrs'])) { $_SESSION['b_contrateotp_voir_cnrs']=true;}
$b_contrat_voir=$_SESSION['b_contrateotp_voir_ul'];
$_SESSION['b_contrateotp_voir_ul']=isset($_POST['b_contrateotp_voir_ul_x'])? !$_SESSION['b_contrateotp_voir_ul']:$_SESSION['b_contrateotp_voir_ul'];
$_SESSION['b_contrateotp_voir_cnrs']=isset($_POST['b_contrateotp_voir_cnrs_x'])? !$_SESSION['b_contrateotp_voir_cnrs']:$_SESSION['b_contrateotp_voir_cnrs'];

//b_champ_tri
if(!isset($_SESSION['b_champ_tri'])){ $_SESSION['b_champ_tri']='datedeb_contrat';}
if(!isset($_SESSION['b_ordre_tri'])){ $_SESSION['b_ordre_tri']='desc';}
foreach($_POST as $postkey=>$val)
{ if(strpos($postkey,'b_champ_tri#')!==false)
	{ $postkey=rtrim($postkey,'_x');
		$postkey=rtrim($postkey,'_y');
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $_SESSION['b_champ_tri']=substr($postkey,strlen('b_champ_tri#'),$posdoublediese-strlen('b_champ_tri#'));
			$_SESSION['b_ordre_tri']=substr($postkey,$posdoublediese+2);
		}
	}
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
	$_SESSION['codecontrat']=$codecontrat;
}
else
{ $codecontrat=$_SESSION['codecontrat'];
}
// ----------------------- liste des contrats a afficher 
$clause_where='';
$chaineorggest='';
if($_SESSION['b_contrateotp_voir_cnrs'] && !$_SESSION['b_contrateotp_voir_ul'])
{ $clause_where.=" and cont_orggesttypecredit.codetypecredit='01'";
}
if($_SESSION['b_contrateotp_voir_ul'] && !$_SESSION['b_contrateotp_voir_cnrs'])
{ $clause_where.=" and cont_orggesttypecredit.codetypecredit='02'";
}

$clause_group_by="";
// rajout d'un 'z' en fin de champ r trier pour avoir les libellés vides aprcs les autres
$clause_order_by="";
if($_SESSION["b_champ_tri"]=="codecontrat")
{ $clause_order_by=" codecontrat"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="libtheme")
{ $clause_order_by=" structure.libcourt_fr"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="respscientifique")
{ $clause_order_by=" nom"." ".$_SESSION["b_ordre_tri"].", prenom"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="liborgfinanceur")
{ $clause_order_by=" cont_orgfinanceur.libcourtorgfinanceur"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="datedeb_contrat")
{ $clause_order_by=" datedeb_contrat"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="acronyme")
{ $clause_order_by="  concat(acronyme,'z')"." ".$_SESSION["b_ordre_tri"];
}
else if($_SESSION["b_champ_tri"]=="libprojet")
{ $clause_order_by=" concat(cont_projet.libcourtprojet,'z')"." ".$_SESSION['b_ordre_tri'].",acronyme desc";
}
if($_SESSION["b_champ_tri"]!="codecontrat")
{ $clause_order_by.=($clause_order_by==""?"":",")."codecontrat desc";
}

$query_rs_contrat =	"SELECT distinct contrat.*,nom,prenom,cont_projet.libcourtprojet as libprojet, libcourtorggest as liborggest,libcourttypeconvention  as libtypeconvention,".
									" libcourtnivconfident as libnivconfident, libcourttype  as libtype,libcourtsecteur  as libsecteur,".
									" libcourtorgfinanceur as liborgfinanceur, numclassif,libcourtclassif as libclassif,structure.libcourt_fr as libtheme".
									" FROM contrat,individu,cont_projet,cont_orggest,cont_orggesttypecredit, cont_type,cont_typeconvention,cont_nivconfident,cont_secteur, cont_orgfinanceur, cont_classif,structure".
									" WHERE contrat.coderespscientifique=individu.codeindividu".
									" and contrat.codeorggest=cont_orggest.codeorggest ". 
									" and contrat.codeprojet=cont_projet.codeprojet".
									" and contrat.codetypeconvention=cont_typeconvention.codetypeconvention".
									" and contrat.codenivconfident=cont_nivconfident.codenivconfident".
									" and contrat.codetype=cont_type.codetype".
									" and contrat.codesecteur=cont_secteur.codesecteur".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and contrat.codeclassif=cont_classif.codeclassif and contrat.codecontrat<>''".
									" and contrat.codetheme=structure.codestructure".
									//" and ".periodeencours('datedeb_contrat','datefin_contrat').
									" and contrat.codeorggest=cont_orggesttypecredit.codeorggest ". 
									$clause_where.
									" ORDER BY ".$clause_order_by;
$rs_contrat = mysql_query($query_rs_contrat) or die(mysql_error());
$nbcontrat=mysql_num_rows($rs_contrat);
$rs_theme = mysql_query("select codestructure as codetheme,libcourt_fr as libtheme from structure where esttheme='oui' ".
												" order by codestructure") or die(mysql_error());	
$rs_cont_type=mysql_query("SELECT codetype,libcourttype as libtype from cont_type order by libcourttype") or die(mysql_error());
$rs_cont_orggest=mysql_query("SELECT codeorggest,libcourtorggest as liborggest from cont_orggest order by libcourtorggest") or die(mysql_error());

//pieces jointes
$tabpjcontrat=array();
$rs=mysql_query("select contratpj.* from contratpj,typepjcontrat where contratpj.codetypepj=typepjcontrat.codetypepj and codelibtypepj=".GetSQLValueString('contrat', "text")) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tabpjcontrat[$row_rs['codecontrat']]=$row_rs;
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des contrats <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

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
	// marqueligne
	var nbtablerow=<?php echo $nbcontrat; ?>;
	function m(tablerow)
	{ even_ou_odd='even';
		for(numrow=1;numrow<=nbtablerow;numrow++)
		{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
			document.getElementById('t'+numrow).className=even_ou_odd;
		}
		document.getElementById(tablerow.id).className='marked';
	}
</SCRIPT>
</head>
<body <?php 
			if($erreur!='' || $warning!='')
			{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"<?php 
			}
      else
			{?> onLoad="window.location.hash='<?php echo $contrateotp_ancre ?>'"
			<?php 
			}?>
      >

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Gestion des contrats - EOTP','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
	<tr>
		<td>&nbsp;
   </td>
	</tr>
	<tr>
		<td align="left">
		</td>
	</tr>
          <form name="gestioncommandes_voir" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  <tr>
    <td>
      <table>
        <tr>
          <td>
            <img src="images/espaceur.gif" width="20" height="1">
          </td>
          <td>
            <span class="bleugrascalibri10">UL&nbsp;</span><input  name="b_contrateotp_voir_ul" type="image" class="icon" src="images/b_checked_<?php echo ($_SESSION['b_contrateotp_voir_ul']?"oui":"non") ?>.png" alt="Afficher les contrats : <?php echo ($_SESSION['b_contrateotp_voir_ul']?"oui":"non") ?>">
          </td>
          <td>
            <span class="bleugrascalibri10">CNRS&nbsp;</span><input  name="b_contrateotp_voir_cnrs" type="image" class="icon" src="images/b_checked_<?php echo ($_SESSION['b_contrateotp_voir_cnrs']?"oui":"non") ?>.png" alt="Afficher les contrats : <?php echo ($_SESSION['b_contrateotp_voir_cnrs']?"oui":"non") ?>">
          </td>
       	</tr>
      </table>
    </td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" class="data" id="table_results">
				<tr class="head">
					<td nowrap align="center"><span class="bleugrascalibri10">Actions</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#codecontrat##<?php if($_SESSION['b_champ_tri']=='codecontrat') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">N&deg;</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#respscientifique##<?php if($_SESSION['b_champ_tri']=='respscientifique') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Resp. scien.</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#liborgfinanceur##<?php if($_SESSION['b_champ_tri']=='liborgfinanceur') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Financeur</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Montant &euro;</span>
					</td>
          <td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#acronyme##<?php if($_SESSION['b_champ_tri']=='acronyme') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Acronyme</span>
          </td>
					<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#libprojet##<?php if($_SESSION['b_champ_tri']=='libprojet') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">Projet</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10"><span class="bleugrascalibri10">
          <input type="image" name="b_champ_tri#datedeb_contrat##<?php if($_SESSION['b_champ_tri']=='datedeb_contrat') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png"></span>&nbsp;Date<br>d&rsquo;effet</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Fin</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Nb<br>mois</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Fin IEB</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Etablissement<br>Gestionnaire</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">R&eacute;f&eacute;rence<br>du contrat</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">EOTP</span>
					</td>
          <td nowrap align="center"><span class="bleugrascalibri10">Type</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Classification</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">R&eacute;f. programme long</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_champ_tri#libtheme##<?php if($_SESSION['b_champ_tri']=='libtheme') { echo $_SESSION['b_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">&nbsp;D&eacute;pt.</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Partenaires</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri10">Objet</span>
					</td>
      </form>
				</tr>
				<?php 	
				$class="even";
				$numrow=0;
				while($row_rs_contrat=mysql_fetch_assoc($rs_contrat))
				{	$numrow++;
					$codecontrat=$row_rs_contrat['codecontrat'];
					?> 
					<tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>" name='t<?php echo $numrow ?>' id='t<?php echo $numrow ?>'  onClick="m(this)" onDblClick="document.forms['edit_contrateotp<?php echo $codecontrat ?>'].submit()">
						<td>
							<table border="0" cellspacing="0" align="center">
								<tr>
                  <td align="left">
                    <?php 
                    if(array_key_exists($codecontrat,$tabpjcontrat))
                    { ?><a href="download.php?codecontrat=<?php echo $codecontrat?>&codetypepj=<?php echo $tabpjcontrat[$codecontrat]['codetypepj'] ?>" target="_blank" title="T&eacute;l&eacute;charger <?php echo $tabpjcontrat[$codecontrat]['nomfichier'] ?>">
                                <img src="images/b_download.png" border="0" width="16"></a>
                    <?php 
                    }
                    else
                    { ?><img src="images/espaceur.gif" width="16" height="1" border="0">
                    <?php 
                    }
                    ?>
                  </td>
									<td align="left">
										<?php 
                    $url="detailcontrat.php?codecontrat=".$codecontrat; ?>
                    <a href="javascript:OuvrirVisible('<?php echo $url ?>')"><img class="icon" width="16" height="16" src="images/b_oeil.png" <?php if($row_rs_contrat['note']!='') { ?> id="sprytrig_visualiser_<?php echo $row_rs_contrat['codecontrat']; ?>" <?php } ?>></a>
                    <?php 
                    if($row_rs_contrat['note']!='')
                    {?> <div class="tooltipContent_cadre" id="sprytooltip_<?php echo $row_rs_contrat['codecontrat']; ?>">
                      <span class="grisfoncecalibri10">Notes partag&eacute;es&nbsp;:<br></span><span class="bleucalibri10"><?php echo nl2br($row_rs_contrat['note']) ?></span>
                      </div>
                      <script type="text/javascript">
                      var sprytooltip_<?php echo $row_rs_contrat['codecontrat']; ?> = new Spry.Widget.Tooltip("sprytooltip_<?php echo $row_rs_contrat['codecontrat']; ?>", "#sprytrig_visualiser_<?php echo $row_rs_contrat['codecontrat'] ?>", {closeOnTooltipLeave:true,  offsetX:5, offsetY:-30, showDelay:0});
                      </script>
                    <?php
                    } ?>
									</td>
									<?php
									// si droit write
									$droitmodif=(estrole('sif',$tab_roleuser) || /* estrole('gestul',$tab_roleuser)|| estrole('gestcnrs',$tab_roleuser)|| */ estrole('du',$tab_roleuser))?'write':'read';// 20170420
									if($droitmodif=="write")
									{ ?>
									<td align="center">
                    <form name="edit_contrateotp<?php echo $codecontrat; ?>" method="post" action="edit_contrateotp.php">
                      <input type="hidden" name="codecontrat" value="<?php echo $codecontrat; ?>">
                      <input type="hidden" name="action" value="modifier">
                      <input type="image" name="submit_modifier" img class="icon" width="16" height="16" src="images/b_edit.png" title="Modifier"/>
											<input type="hidden" name="contrateotp_ancre" value="<?php echo $codecontrat; ?>">
                    </form>
									</td>
 									<?php	 
									} 
									else // en lecture
									{?>
									<td><img src="images/espaceur.gif" width="16" border="0">
									</td>
                  <td><img src="images/espaceur.gif" width="16" border="0">
                  </td>
									<?php
									}
									?>
                  <td>
										<?php 
                    if($row_rs_contrat['date_signature_contrat']!='' && in_array($row_rs_contrat['codeorggest'],array('004','005','010','014','015','016')))
                    { if($row_rs_contrat['estvalide']=='oui')//a ete communique comme cree 
                      {?> <img src="images/b_visa.png">
                      <?php 
                      }
                      else
                      {?> <img src="images/espaceur.gif" width="16" border="0">
                      <?php 
                      }
                    }
                    else
                    {?><img src="images/espaceur.gif" width="16" border="0">
                    <?php
                    }?>
                  </td>
                  <td>
                  <?php if($row_rs_contrat['estprojet']=='oui')
                  {?> <img src="images/b_contrat_projet.png" width="16" border="0">
                  <?php 
                  }
                  else
                  {?> <img src="images/b_contrat_signe_<?php echo ($row_rs_contrat['date_signature_contrat']==''?'non':'oui') ?>.png" width="16" border="0">
                  <?php 
                  }?>
                  </td>
								</tr>
							</table>
						</td>
						<td nowrap>
						<a name="<?php echo $codecontrat ?>"><?php echo $codecontrat; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['nom'].'.&nbsp;'.substr($row_rs_contrat['prenom'],0,1); ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['liborgfinanceur']; ?>
						</td>
						<td nowrap align="right">
						<?php echo number_format ( $row_rs_contrat['montant_ht'],2,'.',' ' );
						?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['acronyme']; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['libprojet']; ?>
						</td>
						<td nowrap><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/'); ?>
						</td>
						<td nowrap><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/'); ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['duree_mois']; ?>
						</td>
						<td nowrap><?php echo aaaammjj2jjmmaaaa($row_rs_contrat['datefin_ieb'],'/'); ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['liborggest']; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['ref_contrat']; ?>
						</td>
						<td nowrap>
             <?php 
						 $query_rs_contrateotp="select eotp.libcourteotp as libeotp".
																		" from contrateotp,eotp".
																		" where contrateotp.codeeotp=eotp.codeeotp".
																		" and contrateotp.codecontrat=".GetSQLValueString($codecontrat, "text").
																		" order by contrateotp.numordre";
						$rs_contrateotp = mysql_query($query_rs_contrateotp) or die(mysql_error());
            $first=true;
						while($row_rs_contrateotp=mysql_fetch_assoc($rs_contrateotp))
            { echo ($first?"":"<br>").$row_rs_contrateotp['libeotp'];
							$first=false;
						}?>
            </td>
						<td nowrap><?php echo $row_rs_contrat['libtype']; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['codeclassif'].' '.$row_rs_contrat['libclassif']; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['ref_prog_long']; ?>
						</td>
						<td nowrap><?php echo $row_rs_contrat['libtheme']; ?>
						</td>
            <?php 
						$query_rs = "SELECT libcourtpart from contratpart,cont_part ".
                        " where contratpart.codepart=cont_part.codepart and contratpart.codecontrat=".GetSQLValueString($codecontrat, "text").
                        " ORDER BY contratpart.numordre desc";
            $rs = mysql_query($query_rs) or die(mysql_error());
            $partenaires='';
            while($row_rs=mysql_fetch_assoc($rs))
            { $partenaires.=$row_rs['libcourtpart'].', ';
            }
						$partenaires=rtrim($partenaires,', ');
						?>
						<td nowrap><?php echo substr($partenaires,0,50).(strlen($partenaires)>=50?"...":""); ?>
						</td>
						<td nowrap><?php 
								if($row_rs_contrat['codedoctorant']!='')
								{ // distinct ne devrait pas etre utilis&eacute; mais pour l'instant un individu peut avoir deux s&eacute;jours en cours, 0 (et 1) permet de mettre l'enr vide en 1ere pos
									$query_doctorant_sujet="SELECT individu.codeindividu as codedoctorant,concat(nom,' ',prenom) as nomprenom,sujet.titre_fr as titre_these". 
																				 " FROM individu,individusejour ".
																				 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
																				 " left join sujet on individusujet.codesujet=sujet.codesujet".
																				 " WHERE individu.codeindividu=individusejour.codeindividu".
																				 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'], "text");
									$rs_doctorant_sujet=mysql_query($query_doctorant_sujet) or die(mysql_error());
									if($row_rs_doctorant_sujet=mysql_fetch_assoc($rs_doctorant_sujet))
									{ echo $row_rs_doctorant_sujet['titre_these'];
									}
								}
								else
								{ echo $row_rs_contrat['sujet'];
								}?>
						</td>
          </tr>
          <?php
			}// fin while
			// montant cumule par colonne annee
			?>
			<?php
			if(isset($rs)) mysql_free_result($rs);
			if(isset($rs_doctorant_sujet))mysql_free_result($rs_doctorant_sujet);
			if(isset($rs_contrat))mysql_free_result($rs_contrat);
			?>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
