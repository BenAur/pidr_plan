<?php 
function detailindividu($codeindividu,$numsejour,$codeuser)
{ $tab_infouser=get_info_user($codeuser);
	$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
	$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
	$estreferent=false;// user a le role referent mais n'est pas forcément le referent
	$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
	// table des nom, prenom et roles+resp de $codeuser
	$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
	$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
	$estreferent=$tab_resp_roleuser['estreferent'];
	$estresptheme=$tab_resp_roleuser['estresptheme'];
	$estgesttheme=array_key_exists('theme',$tab_roleuser) && !$estresptheme;// 20161213
	$est_admin=array_key_exists('du',$tab_roleuser)||array_key_exists('admingestfin',$tab_roleuser)||$admin_bd;// 20161213
	$acces_nonrestreint=($est_admin || array_key_exists('srh',$tab_roleuser) || $estgesttheme);
	$query_rs =" SELECT codepays,libpays,libnat FROM pays";
	$rs = mysql_query($query_rs) or die(mysql_error());
	$tab_pays=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_pays[$row_rs['codepays']]=$row_rs;
	}
	$listetheme="";
	$query_rs_individu = "SELECT individuthese.*,civilite.libcourt_fr as libciv_fr,individu.*,individusejour.*,".
								" individusujet.codesujet, sujet.titre_fr as titresujet_fr, ".
								" corps.liblongcorps_fr as libcorps_fr, cat.codelibcat,cat.codelibcat,libcat_fr,".
								" ed.libcourted_fr as libed, statutpers.codestatutpers,statutpers.libstatutpers_fr,".
								" typestage.libcourttypestage as libtypestage,libsituationprofessionnelle,".
								" diplomeprep.libcourtdiplome_fr as diplomecourt_prep_lib, diplomeprep.liblongdiplome_fr as diplome_prep_lib, diplomeobtenu.liblongdiplome_fr as master_obtenu_lib".
								" FROM civilite,individu,corps,statutpers,cat,ed,typestage,diplome as diplomeprep,diplome as diplomeobtenu,situationprofessionnelle, individusejour ".
								" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
								" left join individuthese on individusejour.codeindividu=individuthese.codeindividu and individusejour.numsejour=individuthese.numsejour".
								" left join sujet on individusujet.codesujet=sujet.codesujet". 
								" WHERE individu.codeindividu=individusejour.codeindividu".
								" and individu.codeciv=civilite.codeciv".
								" and individusejour.codecorps=corps.codecorps and corps.codestatutpers=statutpers.codestatutpers and corps.codecat=cat.codecat".
								" and individu.codeed=ed.codeed".
								" and individusejour.codetypestage=typestage.codetypestage and individusejour.codesituationprofessionnelle=situationprofessionnelle.codesituationprofessionnelle".
								" and individusejour.codediplome_prep=diplomeprep.codediplome and individusejour.codemaster_obtenu=diplomeobtenu.codediplome".
								" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text").
								" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");

	$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
	if($row_rs_individu=mysql_fetch_assoc($rs_individu))
	{ // emplois de cet individu concern&eacute;s par ce s&eacute;jour : intersection([datedeb_emploi,datefin_emploi],[datedeb_sejour,datefin_sejour]) 
		$query ="SELECT individuemploi.*,etab.codeetab,libcourtetab_fr as libetab,liblongmodefinancement as libmodefinancement,".
						" centrecout_pers.libcourt as libcentrecout".
						" FROM individuemploi,etab,modefinancement,centrecout_pers".
						" WHERE codeindividu = ".GetSQLValueString($codeindividu, "text").
						" AND individuemploi.codeetab=etab.codeetab".
						" AND individuemploi.codemodefinancement=modefinancement.codemodefinancement".
						" AND individuemploi.codecentrecout=centrecout_pers.codecentrecout".
						" AND ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individu['datedeb_sejour']."'","'".$row_rs_individu['datefin_sejour']."'").
						" ORDER BY datedeb_emploi desc";
		$rs = mysql_query($query) or die(mysql_error());
		$row_rs_individu['tab_emploi']=array();
		while($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_individu['tab_emploi'][$row_rs['numemploi']]=$row_rs;
		}
		// pj 
		$query="select individupj.*,typepjindividu.* from individupj,typepjindividu ".
					 " where codeindividu=".GetSQLValueString($codeindividu, "text")." and individupj.codelibcatpj=typepjindividu.codelibcatpj and individupj.codetypepj=typepjindividu.codetypepj";
		$rs=mysql_query($query) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ if($row_rs['codelibcatpj']=='individu' || ($row_rs['codelibcatpj']=='sejour' && $row_rs['numcatpj']==$numsejour) || ($row_rs['codelibcatpj']=='emploi' && isset($row_rs_individu['tab_emploi'][$row_rs['numcatpj']])))
			{ $row_rs_individu['tab_pj'][$row_rs['codelibcatpj']][$row_rs['numcatpj']][$row_rs['codelibtypepj']]=$row_rs;
			}
		}

		$query="select nom as createurnom, prenom as createurprenom from individu where codeindividu=".GetSQLValueString($row_rs_individu['codecreateur'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query="select nom as modifieurnom, prenom as modifieurprenom from individu where codeindividu=".GetSQLValueString($row_rs_individu['codemodifieur'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query="select nom as referentnom, prenom as referentprenom from individu where codeindividu=".GetSQLValueString($row_rs_individu['codereferent'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query="select nom as gestthemenom,prenom as gestthemeprenom from individu where codeindividu=".GetSQLValueString($row_rs_individu['codegesttheme'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));
		
		$query="select libgrade_fr as libgrade from grade where codecorps=".GetSQLValueString($row_rs_individu['codecorps'], "text")." and codegrade=".GetSQLValueString($row_rs_individu['codegrade'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_individu=array_merge($row_rs_individu,$row_rs);
		}
		else
		{ $row_rs_individu['libgrade']='';
		}

		$query="select libcourtbap as libbap from bap where codebap=".GetSQLValueString($row_rs_individu['codebap'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query = "SELECT libcourtcommission_fr as libcommission FROM commission WHERE codecommission=".GetSQLValueString($row_rs_individu['codecommission'], "text");
		$rs = mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query = "SELECT numsection, liblongsection_fr as libsection FROM commissionsection WHERE codesection=".GetSQLValueString($row_rs_individu['codesection'], "text");
		$rs = mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		$query="select libcourtlieu as liblieu_fr from lieu where codelieu=".GetSQLValueString($row_rs_individu['codelieu'], "text");
		$rs=mysql_query($query) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs));

		// theme(s)
		$query= "SELECT individutheme.codetheme,libcourt_fr from individusejour,individutheme,structure".
						" WHERE individusejour.codeindividu=individutheme.codeindividu AND individusejour.numsejour=individutheme.numsejour".
						" AND individutheme.codetheme=structure.codestructure".
						//" AND ".periodeencours('datedeb_sejour','datefin_sejour')." AND ".periodeencours('datedeb_theme','datefin_theme').
						" AND ".intersectionperiodes('datedeb_sejour','datefin_sejour','datedeb_theme','datefin_theme'). 
						" AND individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
						" AND individutheme.numsejour=".GetSQLValueString($numsejour, "text");
		$rs=mysql_query($query) or die(mysql_error());
		$first=true;
		$listetheme="";
		while($row_rs = mysql_fetch_assoc($rs))
		{ if($first)
			{ $listetheme.=$row_rs['libcourt_fr'];
				$first=false;
			}
			else
			{ $listetheme.=", ".$row_rs['libcourt_fr'];
			}
		}
		
		//postit
		$query ="SELECT postit FROM individupostit ".
						" WHERE codeindividu=".GetSQLValueString($codeindividu, "text").
						" and numsejour=".GetSQLValueString($numsejour, "text").
						" and codeacteur=".GetSQLValueString($codeuser, "text");
		$rs = mysql_query($query) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_individu=array_merge($row_rs_individu,$row_rs);
		}
		else
		{ $row_rs_individu['postit']='';
		}
		mysql_free_result($rs);
	}
	mysql_free_result($rs_individu);
	$permanent=$row_rs_individu['codestatutpers']=='01'?'oui':'non';
	$codelibcat=$row_rs_individu['codelibcat'];
  

$contenu='<!-- debut table detailindividu -->
<table width="100%"  border="0" cellpadding="0" cellspacing="2">
  <tr>
    <td>
      <table width="100%"  border="0" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td class="noirgrascalibri10">
									Dossier n&deg; '.$row_rs_individu['codeindividu'].'.'.$row_rs_individu['numsejour'].'
								</td>
							</tr>
              <tr>
                <td nowrap class="noircalibri10"><b>'.$row_rs_individu['libciv_fr'].' '.$row_rs_individu['nom'].' '.$row_rs_individu['prenom'].'</b>'.
                  ($row_rs_individu['nomjf']!=""?'<span class="bleucalibri10">&nbsp;n&eacute;e </span>'.$row_rs_individu['nomjf']:"").
                  ' - 
                  <span class="mauvecalibri10">'.$row_rs_individu['libstatutpers_fr'].' - '.$row_rs_individu['libcorps_fr'].($row_rs_individu['associe']=='oui'?" Associ&eacute;":"").
									($row_rs_individu['codeed']!=''?' - '.$row_rs_individu['libed']:"").
									'</span>
                  <span class="bleucalibri10"> - </span>'.$listetheme.
									(($codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC' || $codelibcat=='STAGIAIRE' || $codelibcat=='EXTERIEUR')?
									'&nbsp;('.$row_rs_individu['referentprenom']." ".$row_rs_individu['referentnom'].')':'').
									($acces_nonrestreint?'&nbsp;&nbsp;'.ligne_txt_upload_pj_individu($codeindividu,'individu','00','photo','Photo jointe','',false):'').'&nbsp;&nbsp;'.
									($acces_nonrestreint?'&nbsp;&nbsp;'.ligne_txt_upload_pj_individu($codeindividu,'individu','00','carnet_vaccin','Carnet de vaccin.','',false):'').'&nbsp;&nbsp;'.
									// 20170515 pj fiche_arrivant 
									($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fiche_arrivant','Fiche nouvel arrivant','',false):'').
									// 20170515 fin
                '</td>
              </tr>
              <tr>
                <td nowrap>
                  <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td nowrap>
                        <span class="bleucalibri10">N&eacute;(e) le :</span>
                        <span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['date_naiss'],"/").'</span>
                        <span class="bleucalibri10"> A : </span><span class="noircalibri10">'.$row_rs_individu['ville_naiss'].' - '.$tab_pays[$row_rs_individu['codepays_naiss']]['libpays'].'</span>
                      </td>
                    </tr>
										<tr>
                      <td nowrap>'.
												'<span class="bleucalibri10">Nationalit&eacute; :&nbsp;</span><span class="noircalibri10">'.$tab_pays[$row_rs_individu['codenat']]['libnat'].'</span>&nbsp;&nbsp;'.
												//($acces_nonrestreint?'<span class="bleucalibri10">N&deg; INSEE :&nbsp;</span><span class="noircalibri10">'.$row_rs_individu['num_insee'].'</span>':'').
												($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'cv','CV','',false):'').'&nbsp;&nbsp;'.
												($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'piece_identite','Pi&egrave;ce d&rsquo;identit&eacute;','',false):'').
											'</td>
                    </tr>
									</table>
                </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellpadding="0" cellspacing="1">
                    <tr>
                      <td nowrap>
                        <span class="bleucalibri10">Arriv&eacute;e pr&eacute;vue :&nbsp;</span>
                      </td>
                      <td nowrap>
                        <span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour_prevu'],'/').'</span>
                      </td>
                      <td nowrap>
                        <span class="bleucalibri10">&nbsp;D&eacute;part pr&eacute;vu :&nbsp;</span>
                      </td>
                      <td nowrap>
                        <span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour_prevu'],'/').'</span>
                      </td>
                    </tr>
									</table>
								</td>
							</tr>';
							if($GLOBALS['estzrr'] && $row_rs_individu['datefin_sejour_prevu']>$GLOBALS['date_zrr_t0'])
							{ $demander_autorisation=true;
								$pourquoi_pas_de_demande_fsd='';
								// on comparera les sejours passes au sejour en cours
								$query_rs="select codeindividu, numsejour, datedeb_sejour, datedeb_sejour_prevu, datefin_sejour, datefin_sejour_prevu, date_autorisation".
													" from individusejour".
													" where codeindividu=".GetSQLValueString($codeindividu,"text").
													" order by datedeb_sejour_prevu";
								$rs=mysql_query($query_rs) or die(mysql_error());
								$tab_dates_individu_sejours=array();
								while($row_rs=mysql_fetch_assoc($rs))
								{ $tab_dates_individu_sejours[$row_rs['numsejour']]=$row_rs;
								}
								$tab_demander_autorisation=demander_autorisation($row_rs_individu,$tab_dates_individu_sejours);
								$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
								$contenu.='
							<tr>';
								$pourquoi_pas_de_demande_fsd=$tab_demander_autorisation['pourquoi_pas_de_demande_fsd'];
								if($demander_autorisation || $pourquoi_pas_de_demande_fsd=='FSD - de 5 ans')
								{	$contenu.='
								<td>
									<table>
                    <tr>';
											if($pourquoi_pas_de_demande_fsd!='' && $row_rs_individu['date_demande_fsd']=='')
                      { $contenu.='
											<td class="noircalibri10" nowrap>'.$pourquoi_pas_de_demande_fsd.'</td>';
											}
											else
											{ $contenu.='
											<td class="bleucalibri10" nowrap>FSD : demande&nbsp;d&rsquo;acc&egrave;s le&nbsp;</td>
											<td class="noircalibri10" nowrap>'.aaaammjj2jjmmaaaa($row_rs_individu['date_demande_fsd'],'/').'</td>
											<td class="bleucalibri10" nowrap>&nbsp;autorisation le &nbsp;</td>
                      <td class="noircalibri10" nowrap>'.aaaammjj2jjmmaaaa($row_rs_individu['date_autorisation'],'/').'</td>
                      <td nowrap>'.($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd','Demande','',false):'').'</td>
                      <td nowrap>'.($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd_financement','Financement','',false):'').'</td>
											<td nowrap>'.($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd_sujet','Sujet','',false):'').'</td>';
											}
											$contenu.='
                    </tr>
									</table>
								</td>';
								}
								else
								{ $contenu.='
								<td class="noircalibri10" nowrap>Pas de demande FSD ('.$pourquoi_pas_de_demande_fsd.')</td>';
								}
								$contenu.='
							</tr>';
							}//fin estzrr
							$contenu.='
							<tr>
								<td>
									<table>
                    <tr>
                      <td nowrap class="bleucalibri10">Arriv&eacute;e effective :&nbsp;</td>
                      <td nowrap class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],'/').'</td>
                      <td nowrap class="bleucalibri10">&nbsp;D&eacute;part effectif :&nbsp;</td>
                      <td nowrap class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],'/').'</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                  <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                    	<td>
                        <table>'.
                          ($acces_nonrestreint?
													'<tr><td valign="top" nowrap class="bleucalibri10">Adresse - personnelle :&nbsp;</td>
													<td valign="top" class="noircalibri10">'.str_replace('\\n',' ',$row_rs_individu['adresse_pers'].' '.$row_rs_individu['codepostal_pers']
																																								.' '.$row_rs_individu['ville_pers'].' '.$tab_pays[$row_rs_individu['codepays_pers']]['libpays']).
													'</tr>'
													:'').
                          '<tr>
                            <td valign="top" nowrap class="bleucalibri10">Adresse - administrative :&nbsp;</td>
                            <td valign="top" class="noircalibri10">'.str_replace('\\n',' ',$row_rs_individu['adresse_admin']).
                          '</tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                    	<td>
                        <table>
                          <tr>
														<td valign="top" nowrap class="noircalibri10">'.($row_rs_individu['tel']!=''?'<img src="images/b_tel.png">'.$row_rs_individu['tel']:'').'</td>
														<td valign="top" nowrap class="noircalibri10">'.($row_rs_individu['autretel']!=''?'<img src="images/b_tel.png">'.$row_rs_individu['autretel']:'').'</td>
														<td valign="top" nowrap class="noircalibri10">&nbsp;'.(($row_rs_individu['telport']!='' && $acces_nonrestreint)?'<img src="images/b_gsm.png">'.$row_rs_individu['telport']:'').'</td>
														<td valign="top" nowrap class="noircalibri10">&nbsp;'.($row_rs_individu['fax']!=''?'<img src="images/b_fax.png">'.$row_rs_individu['fax']:'').'</td>
														<td valign="top" nowrap class="noircalibri10">&nbsp;'.($row_rs_individu['email']!=''?'<img src="images/b_mail.png">'.$row_rs_individu['email']:'').'</td>
														<td valign="top" nowrap class="noircalibri10">&nbsp;'.($row_rs_individu['email_parti']!=''?'Autre mail <img src="images/b_mail.png">'.$row_rs_individu['email_parti']:'').'</td>
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
      </table>
    </td>
  </tr>
  <tr>
    <td>
			<table width="100%" border="0" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
				<tr>
					<td>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td nowrap><span class="bleucalibri10">Situation et organisme d&rsquo;origine : </span>
									<span class="noircalibri10">'.$row_rs_individu['libsituationprofessionnelle'].' '.$row_rs_individu['etab_orig'].' '
									.$row_rs_individu['codepostal_etab_orig'].' '.$row_rs_individu['ville_etab_orig'].' '.$tab_pays[$row_rs_individu['codepays_etab_orig']]['libpays'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>';
				if($codelibcat=='DOCTORANT')
				{
				$contenu.='
				<tr>
					<td>
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td nowrap><span class="bleucalibri10">Master obtenu : </span>
									<span class="noircalibri10">'.
									($row_rs_individu['codemaster_obtenu']!=''?$row_rs_individu['master_obtenu_lib']:$row_rs_individu['autremaster_obtenu_lib']).
									$row_rs_individu['master_obtenu_etab_pays'].'&nbsp;-&nbsp;'.$row_rs_individu['master_obtenu_annee'].'
									</span>
								</td>
							</tr>'.
							($row_rs_individu['diplome_dernier_lib']==''?'':'
							<tr>
								<td nowrap><span class="bleucalibri10">Dernier dipl&ocirc;me obtenu (hors DEA ou Master) : </span>
									<span class="noircalibri10">'.$row_rs_individu['diplome_dernier_lib'].' - '.$row_rs_individu['diplome_dernier_etab_pays'].' - '.$row_rs_individu['diplome_dernier_annee'].'</span>
								</td>
							</tr>'
							).'
						</table>
					</td>
				</tr>';
				}
				$contenu.='
				<tr>
					<td>
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td nowrap><span class="bleucalibri10">Lieu de travail pour l&rsquo;activit&eacute; de recherche : </span>
									<span class="noircalibri10">'.$row_rs_individu['liblieu_fr'].($row_rs_individu['autrelieu']==""?"":" - ".$row_rs_individu['autrelieu']).'</span>
								</td>
							</tr>'.
							((($codelibcat=='EC' || $codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC') && $row_rs_individu['composante_enseigne']!='')?
							'<tr>
								<td nowrap>
									<span class="bleucalibri10">Composante d&rsquo;enseignement : </span>
									<span class="noircalibri10">'.$row_rs_individu['composante_enseigne'].'</span>
								</td>
							</tr>
							'
							:'').
						'</table>
					</td>
				</tr>
			</table>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="0">';
						if($permanent=='non')
						{ 
						$contenu.='
						<tr>
							<td>
								<table border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td>
										</td>
									</tr>';
									if($row_rs_individu['codesujet']!='')
									{
									$contenu.='
									<tr>
										<td>
											<table border="0" cellspacing="0" cellpadding="0">';
												if($codelibcat=="STAGIAIRE")
												{               		
												$contenu.='
												<tr>
													<td nowrap>
														<span class="bleucalibri10">Dipl&ocirc;me pr&eacute;par&eacute; :&nbsp;</span>
														<span class="noircalibri10">'.($row_rs_individu['codediplome_prep']!=''?$row_rs_individu['diplome_prep_lib']:$row_rs_individu['autrediplome_prep']).'</span>
													</td>
												</tr>';											
												}		
												$contenu.='
												<tr>
													<td valign="top"><span class="bleucalibri10">
														Sujet :&nbsp;</span><span class="noircalibri10">'.$row_rs_individu['titresujet_fr'].'</span>
													</td>
												</tr>';
												if(($codelibcat=='STAGIAIRE' || $row_rs_individu['codenat']!='079') && $acces_nonrestreint)
												{ $contenu.='
													<tr>
														<td>'.
															ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'assurance_stage','Assurance (rapatriement,...)','',false).'
															&nbsp;&nbsp;'.
															ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'carte_etudiant','Carte d&rsquo;&eacute;tudiant','',false).'
															&nbsp;&nbsp;'.
															($codelibcat=='STAGIAIRE'?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_stage','Convention de stage','',false):'').
															'&nbsp;&nbsp;'.($row_rs_individu['resp_civile_inutile']=="oui"?'':ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'resp_civile','Responsabilit&eacute; civile','',false)).
															'&nbsp;&nbsp;'.($row_rs_individu['resp_civile_inutile']=="oui"?'':ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_accueil','Convention d&rsquo;accueil','',false)).
														'</td>
													</tr>';
												}		
												if($codelibcat=='DOCTORANT')
												{ if($row_rs_individu['cotutelle']=='oui')
													{ $contenu.='
													<tr>
														<td nowrap><span class="bleucalibri10">Cotutelle :&nbsp;</span>
														<span class="noircalibri10">'.
														$row_rs_individu['cotutelle_etab'].' - '.$tab_pays[$row_rs_individu['codepays_cotutelle']]['libpays'].'</span>&nbsp;'.
														($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_cotutelle','Convention cotutelle','',false):'').
														'</td>
													</tr>';
													}
													if(isset($row_rs_individu['tab_pj']['sejour'][$numsejour]['fiche_inscr']))
													{ $contenu.='
													<tr>
														<td>'.
														ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fiche_inscr','Fiche d&rsquo;inscription','',false).
														'</td>
													</tr>';
													}
													if($row_rs_individu['date_suivi_comite_selection_12_mois']!='' || $row_rs_individu['date_suivi_comite_selection_30_mois']!='')
													{ $contenu.='
													<tr>
														<td nowrap><span class="bleucalibri10">Dates de suivi comit&eacute; de s&eacute;lection &agrave; 12 mois :</span>
														<span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['date_suivi_comite_selection_12_mois'],'/').'</span>
														<span class="bleucalibri10">&nbsp;&agrave; 30 mois :</span>
														<span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['date_suivi_comite_selection_30_mois'],'/').'</span>
														</td>
													</tr>';
													}
													if($row_rs_individu['date_soutenance']!='')
													{ $contenu.='
													<tr>
														<td nowrap><span class="bleucalibri10">Date de soutenance :&nbsp;</span>
														<span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['date_soutenance'],'/').'</span>
														</td>
													</tr>';
													}
													if($row_rs_individu['date_soutenance']!='')
													{ $contenu.='
													<tr>
														<td nowrap><span class="bleucalibri10">Titre :&nbsp;</span>
														<span class="noircalibri10">'.$row_rs_individu['titre_these'].'</span>
														</td>
													</tr>';
													}
													if($row_rs_individu['date_soutenance']!='')
													{ $contenu.='
													<tr>
														<td><span class="bleucalibri10">R&eacute;sum&eacute; :&nbsp;</span>
														<span class="noircalibri10">'.$row_rs_individu['resume_these'].'</span>
														</td>
													</tr>';
													}
													if($row_rs_individu['date_soutenance']!='')
													{ $contenu.='
													<tr>
														<td>
															<table>
																<tr>
																	<td colspan="2" class="bleucalibri10">Jury :
																	</td>
																</tr>
																<tr>
																	<td class="bleucalibri10">- Rapporteurs : 
																	</td>
																	<td class="noircalibri10">'.$row_rs_individu['jury_rapp1_these'].
																	'</td>
																</tr>
																<tr>
																	<td>
																	</td>
																	<td class="noircalibri10">'.$row_rs_individu['jury_rapp2_these'].
																	'</td>
																</tr>
																<tr>
																	<td valign="top" nowrap class="bleucalibri10">- Autres membres : 
																	</td>
																	<td class="noircalibri10">'.nl2br($row_rs_individu['jury_autres_membres_these']).
																	'</td>
																</tr>
															</table>
														</td>
													</tr>';
													}
												}
												$contenu.='
											</table>
										</td>
									</tr>';
									}
									else
									{
									$contenu.='
									<tr>
										<td>
											<table width="100%" cellpadding="0" cellspacing="0">
												<tr>
													<td>
														<span class="bleucalibri10">Objet du s&eacute;jour : </span><span class="noircalibri10">'.$row_rs_individu['intituleposte'].
														'</span>
													</td>
												</tr>';
												// 20170510
												if(($codelibcat=='STAGIAIRE' || $row_rs_individu['codenat']!='079') && $acces_nonrestreint)
												{ $contenu.='
													<tr>
														<td>'.
															ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'assurance_stage','Assurance (rapatriement,...)','',false).'
															&nbsp;&nbsp;'.
															ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'carte_etudiant','Carte d&rsquo;&eacute;tudiant','',false).'
															&nbsp;&nbsp;'.
															($codelibcat=='STAGIAIRE'?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_stage','Convention de stage','',false):'').
															'&nbsp;&nbsp;'.($row_rs_individu['resp_civile_inutile']=="oui"?'':ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'resp_civile','Responsabilit&eacute; civile','',false)).
															'&nbsp;&nbsp;'.($row_rs_individu['resp_civile_inutile']=="oui"?'':ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_accueil','Convention d&rsquo;accueil','',false)).
														'</td>
													</tr>';
												}
												// 20170510 fin	
											$contenu.='
											</table>
										</td>
									</tr>';
									}
									if(count($row_rs_individu['tab_emploi'])!=0)
									{ $contenu.='
										<tr>
											<td>
												<table>
													<tr>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td><span class="bleucalibri10">Contrat(s)/emploi(s) :&nbsp;</span>
														</td>
													</tr>
													<tr>
														<td>
															<table>';
															foreach($row_rs_individu['tab_emploi'] as $un_numemploi=>$row_rs_individuemploi) 
															{ $contenu.='
																<tr>
																	<td>&nbsp;</td>
																	<td nowrap>
																		<span class="bleucalibri10">Employeur :&nbsp;</span>
																		<span class="noircalibri10">'.($row_rs_individuemploi['codeetab']!=''?$row_rs_individuemploi['libetab']:$row_rs_individuemploi['autreetab']).'</span>
																		<span class="noircalibri10">&nbsp;('.aaaammjj2jjmmaaaa($row_rs_individuemploi['datedeb_emploi'],'/').'</span>
																		<span class="noircalibri10">'.($row_rs_individuemploi['datefin_emploi']!=''?'&nbsp;-&nbsp;'.aaaammjj2jjmmaaaa($row_rs_individuemploi['datefin_emploi'],'/'):'').')</span>
																	</td>
																</tr>
																<tr>
																	<td>&nbsp;</td>
																	<td>
																		<span class="bleucalibri10">Financement :&nbsp;</span>
																		<span class="noircalibri10">'.($row_rs_individuemploi['codemodefinancement']!=''?$row_rs_individuemploi['libmodefinancement']:$row_rs_individuemploi['autremodefinancement']).
																		($row_rs_individuemploi['detailmodefinancement']!=""?" - ".$row_rs_individuemploi['detailmodefinancement']:"").'</span>
																	</td>
																</tr>
																<tr>
																	<td>&nbsp;</td>
																	<td nowrap>
																		<span class="bleucalibri10">Centre de co&ucirc;t :&nbsp;</span>
																		<span class="noircalibri10">'.($row_rs_individuemploi['codecentrecout']!=""?$row_rs_individuemploi['libcentrecout']:$row_rs_individuemploi['autrecentrecout']).'</span>
																		<span class="bleucalibri10">&nbsp;&nbsp;EOTP(s) :&nbsp;</span>
																		<span class="noircalibri10">'.$row_rs_individuemploi['eotp'].'</span>
																		<span class="bleucalibri10">&nbsp;&nbsp;Contrat(s) :&nbsp;</span>
																		<span class="noircalibri10">'.$row_rs_individuemploi['contrat'].'</span>
				
																	</td>
																</tr>';
																if($row_rs_individuemploi['missioncomp']=='oui' || $row_rs_individuemploi['missioncomp_detail']!='')
																{
																$contenu.='
																<tr>
																	<td>&nbsp;</td>
																	<td nowrap><span class="bleucalibri10">Autres missions compl&eacute;mentaires (monitorat,...) :&nbsp;</span>
																		<span class="noircalibri10">'.$row_rs_individuemploi['missioncomp'].'</span>
																	</td>
																</tr>';
																}
																if($row_rs_individuemploi['montant_mensuel_charge'].$row_rs_individuemploi['montant_mensuel_brut'].$row_rs_individuemploi['montant_mensuel_net']!='')
																{
																$contenu.='
																<tr>
																	<td>&nbsp;</td>
																	<td nowrap><span class="bleucalibri10">Montant mensuel</span>';
																		foreach(array('montant_mensuel_charge'=>'charg&eacute;','montant_mensuel_brut'=>'brut','montant_mensuel_net'=>'net') as $champmontant=>$libchampmontant)
																		{ $contenu.='<span class="bleucalibri10">&nbsp;&nbsp;&nbsp;'.$libchampmontant.' :&nbsp;</span><span class="noircalibri10">'.$row_rs_individuemploi[$champmontant].'</span>';
																		}
																$contenu.='
																	</td>
																</tr>';
																}
																if($acces_nonrestreint && (isset($row_rs_individu['tab_pj']['emploi'][$un_numemploi]['contrat_travail']) || isset($row_rs_individu['tab_pj']['emploi'][$un_numemploi]['avenant_contrat'])))
																{ $contenu.='
																	<tr>
																		<td>&nbsp;</td>
																		<td nowrap>'.
																			ligne_txt_upload_pj_individu($codeindividu,'emploi',$un_numemploi,'contrat_travail','Contrat travail','',false).'&nbsp;&nbsp;'.
																			ligne_txt_upload_pj_individu($codeindividu,'emploi',$un_numemploi,'avenant_contrat','Avenant contrat','',false);
																		'</td>
																	</tr>';
																}
																$contenu.='
																<tr>
																	<td>&nbsp;</td>
																	<td><img src="images/trait_gris.gif" width="200" height="1">
																	</td>
																</tr>';
															}
													$contenu.='
															</table>
														</tr>
													</td>
												</table>
											</td>
										</tr>';
										}
									$contenu.='
								</table>
							</td>
						</tr>';
						}// fin non permanent
						if($permanent=='oui')
						{ $row_rs_individuemploi=$row_rs_individu['tab_emploi'][key($row_rs_individu['tab_emploi'])];
						$contenu.='
						<tr>
							<td nowrap>
								<span class="bleucalibri10">Employeur :&nbsp;</span>
                <span class="noircalibri10">'.($row_rs_individuemploi['codeetab']!=''?$row_rs_individuemploi['libetab']:$row_rs_individuemploi['autreetab']).'</span>
							</td>
						</tr>
						<tr>
							<td>
								<table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td>
                    	<span class="bleucalibri10">Date de recrutement :&nbsp;</span>
                    	<span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],'/').'</span>
										</td>
									</tr>
									<tr>
										<td>
											<table width="86%"  border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td nowrap>
                            <span class="bleucalibri10">Corps :&nbsp;</span>
                            <span class="noircalibri10">'.$row_rs_individu['libcorps_fr'].'&nbsp;&nbsp;'.
                            ($row_rs_individu['codegrade']!=""?$row_rs_individu['libgrade']:'')
                            .'</span>
													</td>
												</tr>
												<tr>
													<td nowrap>'.
														($codelibcat=='ITARF'?'<span class="bleucalibri10">BAP :&nbsp;</span><span class="noircalibri10">'.$row_rs_individu['libbap'].'</span>':'').
														($codelibcat=='EC' || $codelibcat=='CHERCHEUR'?'<span class="bleucalibri10">Section '.$row_rs_individu['libcommission'].' :&nbsp;</span>
														<span class="noircalibri10">'.$row_rs_individu['numsection'].' '.$row_rs_individu['libsection'].'</span>':'').'
													</td>
												</tr>
											</table>
										</td>
									</tr>';
									if($row_rs_individu['hdr']=='oui')
                  {
									$contenu.='
                  <tr>
										<td>
											<table border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td nowrap><span class="bleucalibri10">HDR :&nbsp;</span>
                          <span class="noircalibri10">'.$row_rs_individu['hdr'].'</span>
													</td>
													<td nowrap>&nbsp;<span class="bleucalibri10">Ann&eacute;e :&nbsp;</span>
													<span class="noircalibri10">'.$row_rs_individu['hdr_annee'].'</span>
													</td>
												</tr>
											</table>
										</td>
									</tr>';
									}
									if($row_rs_individu['associe']=='oui')
                  {
									$contenu.='
                  <tr>
										<td>
											<table width="87%"  border="0" cellpadding="0" cellspacing="0">
												<tr>
													<td nowrap><span class="bleucalibri10">Associ&eacute;&nbsp;&nbsp;</span>
													</td>
													<td nowrap>'.($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'contrat_associe','Contrat associ&eacute;','',false):'').'&nbsp;&nbsp;'.
													'</td>
													<td nowrap>'.($acces_nonrestreint?ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'avenant_associe','Avenant contrat associ&eacute;','',false):'').
													'</td>
												</tr>
											</table>
										</td>
									</tr>';
									}
								$contenu.='
                </table>
							</td>
						</tr>';
						}// fin estpermanent=oui
						if($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || $codelibcat=='ITARF')
						{ $contenu.='
						<tr>
							<td>
								<table width="87%"  border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td width="7%" nowrap><span class="bleucalibri10">Temps de travail :&nbsp;</span> 
										</td>
										<td width="11%" nowrap><span class="bleucalibri10">Quotit&eacute; administrative % :&nbsp;</span>
										<span class="noircalibri10">'.$row_rs_individu['quotite_admin'].'</span>&nbsp;&nbsp;
										</td>
										<td nowrap><span class="bleucalibri10">ETPT '.$GLOBALS['acronymelabo'].' % :&nbsp;</span>
										<span class="noircalibri10">'.$row_rs_individu['quotite_unite'].'</span>
										</td>
									</tr>
								</table>
							</td>
						</tr>';
						}
						if(($row_rs_individu['note']!="" || $row_rs_individu['postit']!='') && $acces_nonrestreint)
						{ 
						$contenu.='
            <tr>
              <td>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">';
									if($row_rs_individu['note']!='')
                  {
									$contenu.='
                  <tr>
                    <td nowrap valign="top"><span class="bleucalibri10">Notes partag&eacute;es :&nbsp;</span>
                    </td>
                    <td nowrap><span class="mauvecalibri10">'.nl2br($row_rs_individu['note']).'</span>
                    </td>
                  </tr>';
									}
                  if($row_rs_individu['postit']!='')
                  {
									$contenu.='
                  <tr>
                    <td valign="top" nowrap><span class="bleucalibri10">PostIt ('.$tab_infouser['prenom'].') :&nbsp;</span>
                    </td>
                    <td><span class="mauvecalibri10">'.nl2br($row_rs_individu["postit"]).'</span>
                    </td>
                  </tr>';
									}
									$contenu.='
                </table>
              </td>
            </tr>';
						}
						$contenu.='
            <tr>
              <td>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td>
                      <span class="bleucalibri10">R&eacute;f&eacute;rence dossier Labintel :&nbsp;</span>
                      <span class="noircalibri10">'.$row_rs_individu['codelabintel'].'</span>
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
</table><!-- fin table dans detailindividu -->';
return $contenu;
}

function detailcontrat($codecontrat,$codeuser)
{ $tab_infouser=get_info_user($codeuser);
	$tab_contratmontantannee=array();
	$nbannee=0;
	$nblignemontantdetail=4;
	$aujourdhui=date('Ymd');
	$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
	$estreferent=false;// user a le role referent mais n'est pas forcément le referent
	$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
	$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
	$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
	
	$query_contrat =	"SELECT contrat.*,libcourtsecteur as libsecteur, libcourtnivconfident as libnivconfident,libcourttypeconvention as libtypeconvention,".
										" libcourtorggest as liborggest, libcourtprojet as libprojet, libcourttype  as libtype, codetypecat,".
										" libcourtorgfinanceur as liborgfinanceur, numclassif, libcourtclassif as libclassif,structure.libcourt_fr as libtheme,".
										" concat(substring(respscientifique.prenom,1,1),' ',respscientifique.nom) as nomprenomrespscientifique".
										" FROM contrat,cont_projet, cont_secteur, cont_nivconfident,cont_typeconvention,cont_orggest, cont_type, cont_orgfinanceur,".
										" cont_classif,structure, individu as doctorant, individu as respscientifique".
										" WHERE contrat.codeorggest=cont_orggest.codeorggest ". 
										" and contrat.codeprojet=cont_projet.codeprojet".
										" and contrat.codetype=cont_type.codetype".
										" and contrat.codetypeconvention=cont_typeconvention.codetypeconvention".
										" and contrat.codesecteur=cont_secteur.codesecteur".
										" and contrat.codenivconfident=cont_nivconfident.codenivconfident".
										" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
										" and contrat.codeclassif=cont_classif.codeclassif".
										" and contrat.codetheme=structure.codestructure".
										" and contrat.coderespscientifique=respscientifique.codeindividu".
										" and contrat.codecontrat=".GetSQLValueString($codecontrat,"text");
	$rs_contrat=mysql_query($query_contrat) or die(mysql_error());
	$row_rs_contrat=mysql_fetch_assoc($rs_contrat);
	//doctorant
	$row_rs_contrat['nomprenomdoctorant']='';
	if($row_rs_contrat['codetypecat']=='01' && $row_rs_contrat['codedoctorant']!='')
	{ $query_rs="SELECT concat(nom,' ',substring(prenom,1,1)) as nomprenomdoctorant,sujet.titre_fr as titre_these". 
												 " FROM individu,individusejour".
												 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
												 " left join sujet on individusujet.codesujet=sujet.codesujet".
												 " WHERE individu.codeindividu=individusejour.codeindividu".
												 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'],"text");
		$rs=mysql_query($query_rs) or die(mysql_error());
		$row_rs=mysql_fetch_assoc($rs);
		$row_rs_contrat['nomprenomdoctorant']=$row_rs['nomprenomdoctorant'];
		$row_rs_contrat['sujet']=$row_rs['titre_these'];	
	}
	 $query_rs_contrateotp="select eotp.libcourteotp as libeotp".
													" from contrateotp,eotp".
													" where contrateotp.codeeotp=eotp.codeeotp".
													" and contrateotp.codecontrat=".GetSQLValueString($codecontrat, "text").
													" order by contrateotp.numordre";
	$rs_contrateotp = mysql_query($query_rs_contrateotp) or die(mysql_error());
	
	// Liste des montant annee du contrat 
	$rs=mysql_query("SELECT annee,montant,numordre from contratmontantannee".
										" where codecontrat=".GetSQLValueString($codecontrat, "text").
										" order by numordre") or die(mysql_error());
	
	while($row_rs=mysql_fetch_assoc($rs))
	{ $row_rs_contratmontantannee[$row_rs['numordre']]=$row_rs;
	}
	$nbannee=substr($row_rs_contrat['datefin_contrat'],0,4)-substr($row_rs_contrat['datedeb_contrat'],0,4)+1;
	
$contenu='<table border="0" align="center" cellpadding="0" cellspacing="1" width="100%">
			<tr>
				<td><table border="0">
					<tr>
						<td><span class="bleucalibri9">N&deg; interne : </span> <span class="mauvegrascalibri9">'.$row_rs_contrat['codecontrat'].'</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
					<tr>
						<td>
						<table width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td><span class="bleucalibri10">Date d&eacute;but : </span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/').'</span>
									<span class="bleucalibri10">Date fin : </span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/').'</span>
									<span class="bleucalibri10">&nbsp;Dur&eacute;e mois :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['duree_mois'].'</span>
								</td>
							</tr>
							<tr>
								<td>&nbsp;
								</td>							
							</tr>
							<tr>
								<td>
									<span class="bleucalibri10">Date signature : </span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_contrat['date_signature_contrat'],'/').'</span>
									<span class="bleucalibri10">Date transmission AM2I : </span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_contrat['date_am2i'],'/').'</span>
								</td>							
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td><span class="bleucalibri10">Secteur :&nbsp;</span></td>
										<td><span class="noircalibri10">'.$row_rs_contrat['libsecteur'].'</span></td>
										<td><span class="bleucalibri10">&nbsp;&nbsp;Confidentialit&eacute; :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libnivconfident'].'
										<td><span class="bleucalibri10">&nbsp;&nbsp;Type convention :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libtypeconvention'].'
										</td>
									</tr>
								</table></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><table border="0" cellpadding="0" cellspacing="2">
									<tr>
										<td><span class="bleucalibri10">Gestionnaire :&nbsp;</span></td>
										<td><span class="noircalibri10">'.$row_rs_contrat['liborggest'].'</span></td>
										</td>
										<td><span class="bleucalibri10">Financeur :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['liborgfinanceur'].'</td>
										<td><span class="bleucalibri10">Projet :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libprojet'].'</td>
									</tr>
								</table></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><table border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td><span class="bleucalibri10">Type :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libtype'].'
										<td><span class="bleucalibri10">&nbsp;&nbsp;&nbsp;Classification :&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libclassif'].'</td>
									</tr>';
										if($row_rs_contrat['codetypecat']=='01'  && $row_rs_contrat['codedoctorant']!='')//doctorant associe
										{
										$contenu.='
										<tr>
											<td><span class="bleucalibri10">Doctorant :&nbsp;</span>
											</td>
											<td class="noircalibri10">'.$row_rs_contrat['nomprenomdoctorant'].'
											</td>
											<td colspan="2">
											</td>
										</tr>'; 
										}
								$contenu.='
								</table></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
					<tr>
						<td>
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td nowrap>
									<span class="bleucalibri10">R&eacute;f&eacute;rence du contrat :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['ref_contrat'].'
									</span>
								</td>
							</tr>
							<tr>
								<td>
									<table>
										<tr>
											<td nowrap>
												<span class="bleucalibri10">Acronyme :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['acronyme'].'
												<span class="bleucalibri10">EOTP :&nbsp;</span><span class="noircalibri10">
											';
												$first=true;
												while($row_rs_contrateotp=mysql_fetch_assoc($rs_contrateotp))
												{ $contenu.= ($first?"":",").$row_rs_contrateotp['libeotp'];
													$first=false;
												}
											$contenu.='</span>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td><span class="bleucalibri10">R&eacute;f&eacute;rence programme long :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['ref_prog_long'].'</span></td>
							</tr>
							<tr>
								<td><table border="0" cellspacing="2" cellpadding="0">
									<tr>
										<td valign="top"><span class="bleucalibri10">Objet :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['sujet'].'</span></td>
									</tr>
								</table></td>
							</tr>
						</table></td>
					</tr>
				</table>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
					<tr>
						<td><table border="0" cellpadding="0">
							<tr>
								<td><table border="0" cellspacing="2">
									<tr>
										<td><span class="bleucalibri10">'.$GLOBALS['libcourt_theme_fr'].' :&nbsp;&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['libtheme'].'</td>
										<td><span class="bleucalibri10">&nbsp;Responsable scientifique :&nbsp; </span></td>
										<td class="noircalibri10">'.$row_rs_contrat['nomprenomrespscientifique'].'
										</td>
									</tr>
								</table></td>
							</tr>
							<tr>
								<td>
										<table border="0">
											<tr>
												<td nowrap valign="top"><span class="bleucalibri10">Partenaires :&nbsp;</span></td>
												<td><span class="noircalibri10">';
										$query_contratpart= "select liblongpart". 
																				" from contratpart,cont_part".
																				" where contratpart.codepart=cont_part.codepart and codecontrat=".GetSQLValueString($codecontrat,"text").
																				" order by numordre";
										$rs_contratpart=mysql_query($query_contratpart) or die(mysql_error());
										while($row_rs_contratpart=mysql_fetch_assoc($rs_contratpart))
										{ $contenu.=nl2br($row_rs_contratpart['liblongpart']).' ; ';
										}
										$contenu.='</span></td>
											</tr>
									</table></td>
							</tr>
							<tr>
								<td><table border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td><span class="bleucalibri10">Permanents impliqu&eacute;s en personne.mois :&nbsp;&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['permanent_mois'].'</td>
										<td><span class="bleucalibri10">&nbsp;&nbsp;Total personnels impliqu&eacute;s en personne.mois :&nbsp;&nbsp;</span></td>
										<td class="noircalibri10">'.$row_rs_contrat['personnel_mois'].'</td>
									</tr>
								</table></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><table width="100%" border="0" cellpadding="0" class="table_cadre_arrondi">
					<tr>
						<td width="19"><table border="0" cellpadding="0">
							<tr>
								<td><span class="bleucalibri10">HT/TTC :&nbsp;</span>
									<span class="noircalibri10">'.$row_rs_contrat['ht_ttc'].'</span>
									<span class="bleucalibri10">Montant :&nbsp;</span><span class="noircalibri10">'.$row_rs_contrat['montant_ht'].'</span></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td>
									 <table border="1" cellspacing="0" cellpadding="0">
											<tr>';
											// Liste des codecontratmontantannee du contrat 
											$rs=mysql_query("SELECT * from contratmontantannee where codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
											while($row_rs=mysql_fetch_assoc($rs))
											{ $tab_contratmontantannee[$row_rs['numordre']]=$row_rs;
											}	
	
											//$cumulmontant=0; 
											for($numordre=1;$numordre<=$nbannee;$numordre++)
											{ //$cumulmontant+=isset($tab_contratmontantannee[$numordre]['montant'])?$tab_contratmontantannee[$numordre]['montant']:0 ;
												$annee=substr($row_rs_contrat['datedeb_contrat'],0,4)+$numordre-1;
                        $contenu.='
												<td align="center">
													<table border="1" cellpadding="2" class="table_cellule_encadre_gris">
														<tr>
															<td align="center" class="noircalibri10">
																'.($nbannee-$numordre>=0?$annee:'').'
															</td>
														</tr>
														<tr>
															<td>
																<table cellspacing="3" border="0">
																	<tr>
																		<td colspan="3" align="center" class="noircalibri10">
																			'.(isset($tab_contratmontantannee[$numordre]['montant'])?$tab_contratmontantannee[$numordre]['montant']:"").'
																		</td>
																		</tr>';
																	// Liste des montants detailles previonnels ou réels du contrat pour l'annee
																	$rs_contratmontantdetail=mysql_query( "SELECT nummontantdetail,datemontant,montant,reel".
																																				" from contratmontantdetail where codecontrat=".GetSQLValueString($codecontrat, "text").
																																				" and substring(datemontant,1,4)=".GetSQLValueString($annee, "text").
																																				" order by datemontant") or die(mysql_error());
																	for($nummontant=1;$nummontant<=$nblignemontantdetail;$nummontant++)
																	{ $datemontant=''; $montant=''; $reel='non';
																		if($row_rs_contratmontantdetail=mysql_fetch_assoc($rs_contratmontantdetail))
																		{ $datemontant=$row_rs_contratmontantdetail['datemontant'];
																			$montant=$row_rs_contratmontantdetail['montant'];
																			$reel=$row_rs_contratmontantdetail['reel'];
																		}
                                    $contenu.='
																		<tr>
																			<td class="noircalibri10">'.$montant.'
																			</td>';
																			$jj=($datemontant==''?'&nbsp;&nbsp;':substr($datemontant,8,2)); 
																			$mm=($datemontant==''?'&nbsp;&nbsp;':substr($datemontant,5,2)); 
																			$aaaa=($datemontant==''?'&nbsp;&nbsp;':substr($datemontant,0,4));
																			$attention=false;
																			if($aaaa.$mm.$jj<$aujourdhui && $datemontant!='' && $reel=='non')
																			{	$attention=true;
																			}
                                    $contenu.='
																			<td nowrap>
																				<input type="text" value="'.$jj.'" style="text-align:right" class="noircalibri10" size="1" maxlength="2">
																				<input type="text" value="'.$mm.'" style="text-align:right" class="noircalibri10" size="1" maxlength="2">
																				<input type="text" value="'.$aaaa.'" style="text-align:right" class="noircalibri10" size="2" maxlength="4">
																			</td>
																			<td nowrap><img src="images/b_checked_'.($reel=='oui'?'oui':'non').'.png">
																			</td>
																			<td><img src="images/'.($attention?'b_attention.png':'espaceur.gif').'" width="12" height="12">
																		</td>
																		</tr>';
																	}
																$contenu.='
																</table>
															</td>
														</tr>
													</table>
												</td>';
											}
										$contenu.='
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>&nbsp;</td>
							</tr>
							<tr>
								<td><table border="0" cellspacing="2" cellpadding="0">
									<tr>
										<td valign="top" nowrap><span class="bleucalibri10">Note :&nbsp;</span></td>
										<td class="noircalibri10">'.nl2br($row_rs_contrat['note']).'</td>
										<td valign="top" nowrap>
											<span class="bleucalibri10">Pi&egrave;ces jointes :&nbsp;</span>'.ligne_txt_upload_pj_contrat($codecontrat,'contrat','Contrat','',false).'&nbsp;'.ligne_txt_upload_pj_contrat($codecontrat,'autre','Autre','',false).'
										</td>
									</tr>
								</table></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table>';
	
	if(isset($rs_contratmontantdetail))mysql_free_result($rs_contratmontantdetail);
	if(isset($rs_contratpart))mysql_free_result($rs_contratpart);
	if(isset($rs_contrat_classif))mysql_free_result($rs_contrat_classif);
	if(isset($rs_contrat))mysql_free_result($rs_contrat);
	if(isset($rs))mysql_free_result($rs);
	
	return $contenu;
} 

function detailcommande($codecommande,$codeuser)
{	$contenu='';
	$tab_commandeimputationbudget=array();
	$tab_infouser=get_info_user($codeuser);

	$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
	$estreferent=false;// user a le role referent mais n'est pas forcément le referent
	$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
	$estrespcontrat=false;
	// table des nom, prenom et roles+resp de $codeuser
	$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
	$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
	$estreferent=$tab_resp_roleuser['estreferent'];
	$estresptheme=$tab_resp_roleuser['estresptheme'];
	
	$query_commande =	"SELECT commande.*,cmd_nature.libcourt as libnature,cmd_dialoguegestion.libcourt as libdialoguegestion".
										" FROM commande,cmd_nature,cmd_dialoguegestion".
										" WHERE commande.codecommande=".GetSQLValueString($codecommande,"text").
										" and commande.codenature=cmd_nature.codenature".
										" and commande.codedialoguegestion=cmd_dialoguegestion.codedialoguegestion";
	$rs_commande=mysql_query($query_commande) or die(mysql_error());
	$row_rs_commande=mysql_fetch_assoc($rs_commande);
	
	$query_rs_individu ="SELECT  createur.nom as createurnom,createur.prenom as createurprenom, modifieur.nom as modifieurnom, modifieur.prenom as modifieurprenom, ".
											" referent.nom as referentnom, referent.prenom as referentprenom, secrsite.nom as secrsitenom, secrsite.prenom as secrsiteprenom, gestsif.nom as gestsifnom, gestsif.prenom as gestsifprenom".
											" FROM commande, individu as createur, individu as modifieur, individu as referent,individu as secrsite, individu as gestsif ".
											" WHERE createur.codeindividu=commande.codecreateur and modifieur.codeindividu=commande.codemodifieur".
											" and referent.codeindividu=commande.codereferent and secrsite.codeindividu=commande.codesecrsite and gestsif.codeindividu=commande.codegestsif".
											" and commande.codecommande = ".GetSQLValueString($codecommande, "text");
	$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
	$row_rs_commande=array_merge($row_rs_commande,mysql_fetch_assoc($rs_individu));
	
	if($row_rs_commande['codemission']!='')
	{ //missions
		$query_rs_mission="SELECT  motif FROM mission where codemission=".GetSQLValueString($row_rs_commande['codemission'],"text");
		$rs_mission=mysql_query($query_rs_mission) or die(mysql_error());
		$row_rs_commande=array_merge($row_rs_commande,mysql_fetch_assoc($rs_mission));
	}
	
$query_rs="(SELECT commandeimputationbudget.codecommande, commandeimputationbudget.codecontrat as codecontrat,commandeimputationbudget.codeeotp as codeeotp,".
					" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
					" centrecout.libcourt as libcentrecout,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
					" budg_contrat_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,acronyme as libcontrat,'' as libeotp,contrat_ou_source,'' as eotp_ou_source,".
					" virtuel_ou_reel,montantengage,montantpaye,commandeimputationbudget.numordre".
					" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_contrat_source_vue,cmd_typesource,individu as respscientifique".
					" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
					" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
					" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
					" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat and budg_contrat_source_vue.coderespscientifique=respscientifique.codeindividu and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource".
					" and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text").
					" order by virtuel_ou_reel,commandeimputationbudget.numordre".')'.
					" UNION".
					" (SELECT commandeimputationbudget.codecommande, commandeimputationbudget.codecontrat as codecontrat,commandeimputationbudget.codeeotp as codeeotp,".
					" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
					" centrecout.libcourt as libcentrecout,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
					" budg_eotp_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,'' as libcontrat,libeotp,'' as contrat_ou_source,eotp_ou_source,".
					" virtuel_ou_reel,montantengage,montantpaye,commandeimputationbudget.numordre".
					" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_eotp_source_vue,cmd_typesource,individu as respscientifique".
					" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
					" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
					" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
					" and commandeimputationbudget.virtuel_ou_reel='1' and commandeimputationbudget.codeeotp=budg_eotp_source_vue.codeeotp and budg_eotp_source_vue.coderespscientifique=respscientifique.codeindividu and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource".
					" and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text").
					" order by virtuel_ou_reel,commandeimputationbudget.numordre".')';
					
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ if($row_rs['virtuel_ou_reel']=='0') //contrat
		{ if($row_rs['contrat_ou_source']=='contrat')
			{ $row_rs['libcontrat']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libcontrat'];
			}
			else // source
			{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],'libsource'=>$row_rs['libcontrat'],
																												'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																												'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
				$row_rs['libcontrat']=construitlibsource($tab_construitsource);
				$row_rs['libtypecredit']='CNRS-UL';
			}
		}
		else // eotp
		{ if($row_rs['eotp_ou_source']=='eotp')
			{ $row_rs['libeotp']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libeotp'];
			}
			else
			{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],'libsource'=>$row_rs['libeotp'],
																											'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																											'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
				$row_rs['libeotp']=construitlibsource($tab_construitsource);
			}
		}
		$tab_commandeimputationbudget[$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
	}
	
  $contenu='
	<table border="0" align="center" cellpadding="0" cellspacing="1">
		<tr>
			<td>
				<span class="bleucalibri9">N&deg; interne : </span>
				<span class="infomauve">'.$row_rs_commande['codecommande'].'</span></td>
		</tr>
		<tr>
			<td nowrap>
				<span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
				<span class="infomauve">'.($row_rs_commande['codecreateur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_commande['createurprenom']." ".$row_rs_commande['createurnom']).'</span>
				<span class="bleucalibri9">, le : </span><span class="infomauve">'.(aaaammjj2jjmmaaaa($row_rs_commande['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_commande['date_creation'],"/")).'</span>
				<img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
				<span class="infomauve">'.($row_rs_commande['codemodifieur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_commande['modifieurprenom']." ".$row_rs_commande['modifieurnom']).'</span>
				<span class="bleucalibri9">, le : </span><span class="infomauve">'.(aaaammjj2jjmmaaaa($row_rs_commande['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_commande['date_modif'],"/")).'</span>
			</td>
		</tr>
		<tr>
			<td nowrap>
				<table>
					<tr>
						<td nowrap><span class="bleugrascalibri10">Demandeur :</span>&nbsp;'.$row_rs_commande['referentprenom'].' '.$row_rs_commande['referentnom'].'
						</td>
						<td nowrap><span class="bleugrascalibri10">Secr&eacute;taire d\'appui :</span>&nbsp;'.$row_rs_commande['secrsiteprenom'].' '.$row_rs_commande['secrsitenom'].'
						</td>
						<td nowrap><span class="bleugrascalibri10">Gestionnaire de cr&eacute;dits :</span>&nbsp;'.$row_rs_commande['gestsifprenom'].' '.$row_rs_commande['gestsifnom'].'
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
							<table border="0" cellpadding="0" cellspacing="5">
								<tr>
									<td nowrap>
										<table border="0" cellspacing="5" cellpadding="0">
											<tr>
												<td valign="top"><span class="bleugrascalibri10">Objet :</span></td>
												<td>'.$row_rs_commande['objet'].'
												</td>
											</tr>
											<tr>
												<td valign="top"><span class="bleugrascalibri10">Description d&eacute;taill&eacute;e :</span></td>
												<td>'.nl2br($row_rs_commande['description']).'</td>
												<td>
													<table border="0" cellspacing="0" cellpadding="0">
														<tr>												
															<td>';
																	$codelibtypepj='devis';$txt_pj='Devis';
																	$query_rs="select codetypepj from typepjcommande where codelibtypepj=".GetSQLValueString($codelibtypepj, "text");
																	$rs=mysql_query($query_rs) or die(mysql_error());
																	$row_rs=mysql_fetch_assoc($rs);  
																	$codetypepj=$row_rs['codetypepj'];
																	$query_rs="select commandepj.* from commandepj".
																										" where codecommande=".GetSQLValueString($codecommande, "text").
																										" and codetypepj=".GetSQLValueString($codetypepj, "text");
																	$rs=mysql_query($query_rs) or die(mysql_error());
																	if($row_rs=mysql_fetch_assoc($rs))
																	{ $contenu.='
																			<a href="download.php?codecommande='.$codecommande.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">
																			<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
																	}
																$contenu.='
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<tr>
												<td valign="top"><span class="bleugrascalibri10">Fournisseur :&nbsp;</span></td>
												<td>'.$row_rs_commande['libfournisseur'].'</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
										<td valign="top">
											<table border="0" cellpadding="0" cellspacing="5">';
												$tab_commandeimputationbudget_virtuel=$tab_commandeimputationbudget['0'];
												foreach($tab_commandeimputationbudget_virtuel as $numordre=>$une_commandeimputationbudget)
												{ 
													$contenu.='
												<tr>
													<td nowrap><span class="bleugrascalibri10">Cr&eacute;dits :</span>&nbsp;'.$une_commandeimputationbudget['libtypecredit'].'
													</td><td><img src="images/espaceur.gif" width="50" height="1"></td>
													<td nowrap><span class="bleugrascalibri10">Type cr&eacute;dits :</span>&nbsp;'.$une_commandeimputationbudget['libcentrefinancier'].'
													</td>
													<td nowrap><span class="bleugrascalibri10">Enveloppe :</span>&nbsp;'.$une_commandeimputationbudget['libcentrecout'].'
													</td>
													<td nowrap><span class="bleugrascalibri10">Source :</span>&nbsp;'.$une_commandeimputationbudget['libcontrat'].'
													</td>
													<td nowrap><span class="bleugrascalibri10">Montant engag&eacute; : </span></td>
													<td nowrap align="right">'.((int)$une_commandeimputationbudget['montantengage']!=0?sprintf('%01.2f',$une_commandeimputationbudget['montantengage']):'').'</td>													
												 </tr>';
											 }
											 $contenu.='
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
			<td>';
			$class_cache_ou_affiche='cache'; 
			if(estrole('secrsite',$tab_roleuser) || estrole('sif',$tab_roleuser) || estrole('admingestfin',$tab_roleuser) || estrole('du',$tab_roleuser))
			{ $class_cache_ou_affiche='affiche';
			}
			$contenu.='
			<div id="blocsecrsite" class="'.$class_cache_ou_affiche.'">           
				<table width="100%" border="0" class="table_cadre_arrondi">
					<tr>
						<td><table border="0" cellpadding="0" cellspacing="5">
							<tr>
								<td nowrap><span class="bleugrascalibri10">Date de la commande : </span></td>
								<td nowrap>'.aaaammjj2jjmmaaaa($row_rs_commande['datecommande'],'/').'</td>
							</tr>
							<tr>
								<td><span class="bleugrascalibri10">N&deg; de commande :&nbsp;</span></td>
								<td nowrap>'.$row_rs_commande['numcommande'].'</td>
							</tr>
							<tr>
								<td><span class="bleugrascalibri10">Serv. fait (MIGO)</span>
								</td>
								<td>';
									$rs_migo=mysql_query("select codemigo,nummigo,datemigo ".
																			" from commandemigo".
																			" where commandemigo.codecommande=".GetSQLValueString($row_rs_commande['codecommande'], "text").
																			" order by codemigo") or die(mysql_error());
									if(mysql_num_rows($rs_migo)>0)
									{ $contenu.='
									<table border="0" cellpadding="5">
										<tr><td><b>N&deg;</b></td><td><b>Date</b></td><td><b>N&deg; facture</b></td><td><b>Date</b></td><td><b>N&deg; liquidation</b></td><td><b>Date</b></td><td><b>Montant</b></td>';
										while($row_rs_migo=mysql_fetch_assoc($rs_migo))
										{ $contenu.='
										<tr>
											<td>'.$row_rs_migo['nummigo'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_migo['datemigo'],'/').'</td>';
											$rs_liquidation=mysql_query("select numfacture,datefacture,numliquidation,dateliquidation,montantliquidation ".
																									" from commandemigoliquidation".
																									" where codemigo=".GetSQLValueString($row_rs_migo['codemigo'], "text").
																									" and codecommande=".GetSQLValueString($row_rs_commande['codecommande'], "text").
																									" order by codeliquidation") or die(mysql_error());
											$first=true;
											while($row_rs_liquidation=mysql_fetch_assoc($rs_liquidation))
											{	$contenu.=($first?'':'<tr><td colspan="2"></td>').
																						 '<td>'.$row_rs_liquidation['numfacture'].'</td>
																						  <td>'.aaaammjj2jjmmaaaa($row_rs_liquidation['datefacture'],'/').'</td>
																						  <td>'.$row_rs_liquidation['numliquidation'].'</td>
																						  <td>'.aaaammjj2jjmmaaaa($row_rs_liquidation['dateliquidation'],'/').'</td>
																						  <td align="right">'.((int)$row_rs_liquidation['montantliquidation']!=0?sprintf('%01.2f',$row_rs_liquidation['montantliquidation']):'').'</td>
																						</tr>';
												$first=false;
											}
										}
										$contenu.='
									</table>';
									}
								$contenu.='
								</td>
							</tr>
						</table></td>
					</tr>
			</table></td>
			</tr>
		<tr>
			<td>&nbsp;</td>
		</tr>
  <!--// 20170405 inventaire -->';
	$query_rs="select commandeinventaire.numinventaire, individu.nom, individu.prenom, lieu.libcourtlieu as liblieu, commandeinventaire.num_bureau, commandeinventaire.objetinventaire".
															 " from commandeinventaire, individu, lieu".
															 " where codecommande=".GetSQLValueString($row_rs_commande['codecommande'], "text").
															 " and commandeinventaire.codedestinataire=individu.codeindividu and commandeinventaire.codelieu=lieu.codelieu".
															 " order by numinventaire";
	$rs=mysql_query($query_rs);
	if(mysql_num_rows($rs)>0)
 	{ $contenu.='
	<tr>
  	<td>
      <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
        <tr>
        	<td>
          </td>
          <td align="left" class="noirgrascalibri10">N&deg; inventaire
          </td>
          <td align="left" class="noirgrascalibri10">Destinataire
          </td>
          <td align="left" class="noirgrascalibri10">Lieu
          </td>
          <td align="left" class="noirgrascalibri10">N&deg; bureau
          </td>
          <td align="left" class="noirgrascalibri10">Equipement
          </td>
        </tr>';
        while($row_rs=mysql_fetch_assoc($rs))
        { $contenu.='
        <tr>
        	<td class="bleugrascalibri10">Inventaire
          </td>
          <td class="noircalibri10">'.$row_rs['numinventaire'].
          '</td>
          <td class="noircalibri10">'.$row_rs['prenom'].' '.$row_rs['nom'].
          '</td>
          <td class="noircalibri10">'.$row_rs['liblieu'].
          '</td>
          <td class="noircalibri10">'.$row_rs['num_bureau'].
          '</td>
          <td class="noircalibri10">'.$row_rs['objetinventaire'].
          '</td>
        </tr>';
			}
      $contenu.=
			'</table>
    </td>
  </tr>
	<tr>
			<td>&nbsp;</td>
		</tr>';
	}
		$contenu.=
			'<tr>
			<td>';
			$class_cache_ou_affiche='cache'; 
			if(estrole('secrsite',$tab_roleuser) || estrole('sif',$tab_roleuser) || estrole('admingestfin',$tab_roleuser) || estrole('du',$tab_roleuser))
			{ $class_cache_ou_affiche='affiche';
			}
			$contenu.='           
			<div id="blocsif" class="'.$class_cache_ou_affiche.'">
				<table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
				<tr>
					<td><table border="0" cellpadding="0" cellspacing="3">
						<tr>
							<td valign="top">
								<table border="0" cellpadding="0" cellspacing="5">';
									$first=true;
									$tab_commandeimputationbudget_reel=$tab_commandeimputationbudget['1'];
									foreach($tab_commandeimputationbudget_reel as $numordre=>$une_commandeimputationbudget)
									{ $contenu.='
										<tr><td nowrap>';
										$contenu.=$first?'<span class="bleugrascalibri10">Cr&eacute;dits :</span>&nbsp;'.$une_commandeimputationbudget['libtypecredit']
																		:'&nbsp;';
										$first=false;
										$contenu.=
										 '</td><td><img src="images/espaceur.gif" width="50" height="1"></td>	
											<td nowrap><span class="bleugrascalibri10">Types cr&eacute;dits :</span>&nbsp;'.$une_commandeimputationbudget['libcentrefinancier'].'
											</td>
											<td nowrap><span class="bleugrascalibri10">Enveloppe :</span>&nbsp;'.$une_commandeimputationbudget['libcentrecout'].'
											</td>
											<td nowrap><span class="bleugrascalibri10">Source :</span>&nbsp;'.$une_commandeimputationbudget['libeotp'].'
											</td>
											<td nowrap><span class="bleugrascalibri10">Montant engag&eacute; :&nbsp;</span></td>
											<td nowrap align="right">'.((int)$une_commandeimputationbudget['montantengage']!=0?sprintf('%01.2f',$une_commandeimputationbudget['montantengage']):'').'</td>
											<td nowrap><span class="bleugrascalibri10">Montant pay&eacute; :&nbsp;</span></td>
											<td nowrap align="right">'.((int)$une_commandeimputationbudget['montantpaye']!=0?sprintf('%01.2f',$une_commandeimputationbudget['montantpaye']):'').'</td>
										</tr>';
									}
									$contenu.='
								 </table>
							</td>
						 </tr>
					</table>
				 </td>
				</tr>
				<tr>
					<td>
					 <table border="0" cellpadding="0" cellspacing="3">
							<tr>
								<td nowrap class="bleugrascalibri10">Nature :</td>
								<td nowrap>'.$row_rs_commande['libnature'].'</td>
								<td><span class="bleugrascalibri10">Rubrique Comptable : </span></td>
								<td>'.$row_rs_commande['rubriquecomptable'].'
								</td>
								<td><span class="bleugrascalibri10">Nature dialogue gestion : </span></td>
								<td>'.$row_rs_commande['libdialoguegestion'].'
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
			<td>'.nl2br($row_rs_commande['note']).'</td>
		</tr>
	</table>';
	
	if(isset($rs_theme))mysql_free_result($rs_theme);
	if(isset($rs_mission))mysql_free_result($rs_mission);
	if(isset($rs_operation))mysql_free_result($rs_operation);
	if(isset($rs_inventaire))mysql_free_result($rs_inventaire);
	if(isset($rs_commande))mysql_free_result($rs_commande);
	if(isset($rs))mysql_free_result($rs);
	if(isset($rs_fields))mysql_free_result($rs_fields);
	
	return $contenu;
}

function detailprojet($codeprojet,$codeuser)
{ 
 	$rs_projet=mysql_query("select projet.*  from projet where projet.codeprojet=".GetSQLValueString($codeprojet,"text")) or die(mysql_error());
	$row_rs_projet=mysql_fetch_assoc($rs_projet);
	
	$rs=mysql_query("select libcourt_fr from structure where codestructure=".GetSQLValueString($row_rs_projet['codetheme'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['libtheme']=$row_rs['libcourt_fr'];
	$rs=mysql_query("select nom, prenom from individu where codeindividu=".GetSQLValueString($row_rs_projet['codecreateur'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['createurnom']=$row_rs['nom'];
	$row_rs_projet['createurprenom']=$row_rs['prenom'];
	$rs=mysql_query("select nom, prenom from individu where codeindividu=".GetSQLValueString($row_rs_projet['codemodifieur'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['modifieurnom']=$row_rs['nom'];
	$row_rs_projet['modifieurprenom']=$row_rs['prenom'];
	$rs=mysql_query("select concat(nom,' ',substr(prenom,1,1),'. ') as nomprenom from individu where codeindividu=".GetSQLValueString($row_rs_projet['codereferent'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['referentnomprenom']=$row_rs['nomprenom'];
	$rs=mysql_query("select libcourtclassif as libclassif from cont_classif where codeclassif=".GetSQLValueString($row_rs_projet['codeclassif'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['libclassif']=$row_rs['libclassif'];
	$rs=mysql_query("select libimplication from proj_implication where codeimplication=".GetSQLValueString($row_rs_projet['codeimplication'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['libimplication']=$row_rs['libimplication'];
	$rs=mysql_query("select codelibtypeprojet,libtypeprojet from proj_typeprojet where codetypeprojet=".GetSQLValueString($row_rs_projet['codetypeprojet'],"text")) or die(mysql_error());
	$row_rs= mysql_fetch_assoc($rs);
	$row_rs_projet['codelibtypeprojet']=$row_rs['codelibtypeprojet'];
	$row_rs_projet['libtypeprojet']=$row_rs['libtypeprojet'];

  $contenu='
		<table border="0" align="center" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
				<tr>
					<td>
							<span class="bleucalibri9">N&deg; interne : </span>
							<span class="mauvegrascalibri9">'.$row_rs_projet['codeprojet'].'</span>
					</td>
				</tr>
				<tr>
					<td nowrap>
							<span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
							<span class="infomauve">'.$row_rs_projet['createurprenom']." ".$row_rs_projet['createurnom'].'</span><span class="bleucalibri9">, le : </span><span class="infomauve">'.aaaammjj2jjmmaaaa($row_rs_projet['date_creation'],"/").'</span>
							<img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
							<span class="infomauve">'.$row_rs_projet['modifieurprenom']." ".$row_rs_projet['modifieurnom'].'</span><span class="bleucalibri9">, le : </span><span class="infomauve">'.aaaammjj2jjmmaaaa($row_rs_projet['date_modif'],"/").'</span>
					 </td>
				 </tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
						<table border="0">
							<tr>
								<td><span class="bleugrascalibri10">Porteur  du projet</span><span class="bleugrascalibri10"> :&nbsp;</span>
									<span class="noircalibri10">'.$row_rs_projet['referentnomprenom'].'</span>
								</td>
								<td>
									<span class="bleugrascalibri10">Date d\'&eacute;mission de la fiche projet :&nbsp;</span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_projet['date_publi'],"/").'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table border="0">
							<tr>
								<td><span class="bleugrascalibri10">Contexte du d&eacute;p&ocirc;t :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['libclassif'].'</span>
								</td>
								<td><span class="bleugrascalibri10">Niveau d\'implication :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['libimplication'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table border="0">
							<tr>
								<td><span class="bleugrascalibri10">Type : </span><span class="noircalibri10">'.$row_rs_projet['libtypeprojet'].($row_rs_projet['detailtypeprojet']==''?' - '.$row_rs_projet['detailtypeprojet']:'').'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table>
							<tr>';
								if($row_rs_projet['codelibtypeprojet']=='contratrecherche')
								{ $contenu.='
									<td><span class="bleugrascalibri10">Date de d&eacute;but pr&eacute;vue :&nbsp;</span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_projet['datedeb_projet'],"/").'</span>
									</td>
									<td><span class="bleugrascalibri10">Dur&eacute;e en mois :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['duree_mois'].'</span>
									</td>';
								}
								else if($row_rs_projet['codelibtypeprojet']=='appelprojet')
								{ $contenu.='
									<td><span class="bleugrascalibri10">Date limite de d&eacute;p&ocirc;t du projet :&nbsp;</span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_projet['datelimite_depot_projet'],"/").'</span>
									</td>';
								}
								if($row_rs_projet['datedepot_projet']!='')
								{ $contenu.='
									<td><span class="bleugrascalibri10">Date de d&eacute;p&ocirc;t du projet si d&eacute;j&agrave; d&eacute;pos&eacute; :&nbsp;</span><span class="noircalibri10">'.aaaammjj2jjmmaaaa($row_rs_projet['datedepot_projet'],"/").'</span>
									</td>';
								}
							$contenu.='</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
					<table border="0">
						<tr>
						<td><span class="bleugrascalibri10">Montant total :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['montant_total'].'</span>
						</td>
						<td><span class="bleugrascalibri10">Montant laboratoire :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['montant_labo'].'</span>
						</span>
						</td>
						</tr>
					</table>
					</td>
				</tr>'.
				($row_rs_projet['titrecourt']==''?
				'':
				'<tr>
					<td><span class="bleugrascalibri10">Acronyme ou titre court :&nbsp;</span><span class="noircalibri10">'.$row_rs_projet['titrecourt'].'</span>
					</td>
				</tr>'
				).
				'<tr>
					<td><span class="bleugrascalibri10">Intitul&eacute; du projet/contrat/&eacute;tude d&eacute;pos&eacute; : </span></td>
				</tr>
				<tr>
					<td>
						<span class="noircalibri10">'.$row_rs_projet['titre'].'</span>
					</td>
				</tr>
				<tr>
					<td><span class="bleugrascalibri10">Partenaires : </span></td>
				</tr>
				<tr>
					<td>
						<span class="noircalibri10">'.$row_rs_projet['partenaires'].'</span>
					</td>
				</tr>
				<tr>
					<td><span class="bleugrascalibri10">Description  : </span></td>
				</tr>
				<tr>
					<td><span class="noircalibri10">'.$row_rs_projet['descr'].'</span></td>
				</tr>
				<tr>
					<td><span class="bleugrascalibri10">Sp&eacute;cificit&eacute;s  : </span></td>
				</tr>
				<tr>
					<td><span class="noircalibri10">'.$row_rs_projet['specificites'].'</span></td>
				</tr>
				<tr>
					<td>
						<span class="bleugrascalibri10">Notes  : </span>
					</td>
				</tr>
				<tr>
					<td><span class="noircalibri10">'.$row_rs_projet['note'].'</td>
					<td>
				</tr>
				<tr>
					<td nowrap><span class="bleugrascalibri10">Pi&egrave;ces jointes :</span>
					</td>
				</tr>
				<tr>
					<td nowrap>'.ligne_txt_upload_pj_projet($codeprojet,'projet','Projet','',false).'</td>
				</tr>
				<tr>
					<td nowrap>'.ligne_txt_upload_pj_projet($codeprojet,'autre','Autre','',false).'</td>
				</tr>
		</table>
		</form>
	
	</body>
	</html>'; 
	if(isset($rs_projet))mysql_free_result($rs_projet);
	if(isset($rs))mysql_free_result($rs);
	return $contenu;
	
}