<?php 

function upload_file($fichiers,$rep_upload,$nom_tab_input_file,$key,$codetypepj)
{	// $key clé du tableau pj de $fichiers =codetypepj
  // traite tous les cas d'erreur lors du transfert mais ne peut pas traiter l'erreur du move_uploaded_file traitée à part
	// la doc indique que les navigateurs n'envoient pas tous max_file_size : les verifs sont faites apres la detection de $code_erreur
	$tab_res_upload=array('erreur'=>'','nomfichier'=>'');
	//conversion extension en minuscules : le download ne se fait pas correctement si extension en maj !!!
	$tab_decompose_nomfichier=explode('.', $fichiers[$nom_tab_input_file]['name'][$key]);
	end($tab_decompose_nomfichier);
	$tab_decompose_nomfichier[key($tab_decompose_nomfichier)]=strtolower(current($tab_decompose_nomfichier));
	$tab_res_upload['nomfichier']=implode('.',$tab_decompose_nomfichier);
	$extension = strtolower(end($tab_decompose_nomfichier));
	$codeerreur=$fichiers[$nom_tab_input_file]['error'][$key];
	if($codeerreur!=UPLOAD_ERR_OK && $codeerreur!=UPLOAD_ERR_NO_FILE)//pas d'erreur si OK ou pas de fichier
	{ $tab_res_upload['erreur']=$GLOBALS['tab_erreur_upload'][$codeerreur];
	}
	if($codeerreur==UPLOAD_ERR_OK)//pas de traitement pour UPLOAD_ERR_NO_FILE = pas de fichier
	{	//teste a nouveau les erreurs possibles car la doc indique que les navigateurs n'envoient pas tous les parametres (max_size_file).
		if(in_array($extension, $GLOBALS['file_types_array'])) 
		{ if($fichiers[$nom_tab_input_file]['size'][$key]<=$GLOBALS['max_file_size'])
			{ if(move_uploaded_file($fichiers[$nom_tab_input_file]['tmp_name'][$key],$rep_upload.'/'.$codetypepj))
				{ $codeerreur='';
				}
				else
				{ $tab_res_upload['erreur']='Une erreur est survenue lors du transfert.';
				}
			}
			else
			{ $tab_res_upload['erreur']=$GLOBALS['tab_erreur_upload'][UPLOAD_ERR_FORM_SIZE];
			}
		}
		else
		{ $tab_res_upload['erreur']=$GLOBALS['tab_erreur_upload'][UPLOAD_ERR_EXTENSION];
		}
	}
	if($tab_res_upload['erreur']!='')
	{ $tab_res_upload['erreur']=$tab_res_upload['nomfichier'].' non enregistr&eacute; : '.$tab_res_upload['erreur'];
	}
	return $tab_res_upload;
}

// affiche ligne : icone download+nom pj, remplacer par champ file, Effacer si editable=true
//$codeindividu,$codelibcatpj ('sejour' ou 'emploi'),$numcatpj (numero dans la categorie de pj) : cle individupj ('00001','sejour','01')
//$codelibtypepj : index unique de pjindividu ('cv') pour un couple  (codelibcatpj, codetypepj) cle de typepjindividu
//$txt_pj : texte a afficher comme nom de lien ou devant la zone de téléchargement
//$nom_form : nom du formulaire pour script js
//$editable : pj supprimable/telechargeable ou non
function ligne_txt_upload_pj_individu($codeindividu,$codelibcatpj,$numcatpj,$codelibtypepj,$txt_pj,$nom_form,$editable)
{ $contenu='';
	$rs=mysql_query("select codetypepj from typepjindividu where codelibcatpj=".GetSQLValueString($codelibcatpj, "text")." and codelibtypepj=".GetSQLValueString($codelibtypepj, "text")) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);  
	$codetypepj=$row_rs['codetypepj'];
	
	$rs=mysql_query("select individupj.* from individupj".
										" where codeindividu=".GetSQLValueString($codeindividu, "text").
										" and codelibcatpj=".GetSQLValueString($codelibcatpj, "text").
										" and numcatpj=".GetSQLValueString($numcatpj, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
  { $contenu=	'<a href="download.php?codeindividu='.$codeindividu.'&codelibcatpj='.$codelibcatpj.'&numcatpj='.$numcatpj.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
                                  '<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
		if($editable)															
    { $contenu.='&nbsp;&nbsp;<input type="image" name="submit_supprimer_une_pj#'.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.'##" class="icon" src="images/b_drop.png" title="Supprimer '.$txt_pj.'" onClick="return confirm(\'Supprimer : '.$txt_pj.'\')">'.
                '&nbsp;&nbsp;<span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span><input type="file" name="pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']" class="noircalibri9" id="pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']"/>';
		}
  }
  else
  { if($editable)
		{ $js_controle_extention='';
			if($codelibcatpj=='sejour' && in_array($codelibtypepj,array('cv','piece_identite','fsd_sujet','fsd_financement')))
			{ $js_controle_extention=' onchange="if(!this.value.endsWith(\'.pdf\')){alert(\'il faut un fichier pdf !\')}"';
			}
			$contenu.='<span class="bleugrascalibri10">'.$txt_pj.' :&nbsp;</span><input type="file" name="pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']" class="noircalibri9" id="pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']" '.
								$js_controle_extention.'>';
		}
  }
	/* if($editable)
	{ $liste_file_types=implode(", ",$GLOBALS['file_types_array']);
		$contenu.='&nbsp;<img src="images/b_info.png" border="0" width="16" height="16" id="sprytrigger_info_pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']">'.
            '<div class="tooltipContent_cadre" id="info_pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']">'.
            '<span class="noircalibri10">Le volume de tous les fichiers joints pour un dossier personnel est limit&eacute; &agrave; '.$GLOBALS['max_file_size_Mo'].'Mo'.
						' et doivent porter l&rsquo;une des extensions suivantes : <br>'.$liste_file_types.' (les extensions sont automatiquement transform&eacute;es en minuscules)<br>'.
						'La zone contenant le nom du fichier ne peut &ecirc;tre effac&eacute;e (uniquement modifi&eacute;e avec un autre fichier).<br>'.
						'Si un fichier a &eacute;t&eacute; s&eacute;lectionn&eacute; par erreur, la seule solution consiste &agrave; envoyer le formulaire qui retourne :<br>'.
						'- <img src="images/b_download.png" align="absbottom" border="0" width="16 height="16"> suivi du nom du fichier qui est t&eacute;l&eacute;chargeable ;<br>'.
						'- <img src="images/b_drop.png" border="0" align="absbottom" width="16 height="16"> pour supprimer le fichier (en cas d&rsquo;erreur notamment) ;<br>'.
						'</span>'.
						'- <span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span>'.
						'<span class="noircalibri10">s&eacute;lectionner un nouveau fichier qui &eacute;crasera l&rsquo;ancien.<br>'.
						'Si, de plus, un message d&rsquo;erreur est affich&eacute; lors de l&rsquo;envoi du fichier, il faudra choisir un autre fichier ne provoquant pas<br>'.
						'cette erreur, l&rsquo;envoyer puis le supprimer...'.
						'</span></div>'.
            '<script type="text/javascript">'.
            'var sprytooltip_info_pj_'.$codelibcatpj.'_'.$numcatpj.'_'.$codelibtypepj.' = new Spry.Widget.Tooltip("info_pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']", "#sprytrigger_info_pj['.$codelibcatpj.'_'.$numcatpj.'_'.$codetypepj.']", {offsetX:20, offsetY:20});'.
            '</script>';
	} */
	if(isset($rs)) {mysql_free_result($rs);}
	return $contenu;
}

function ligne_txt_upload_pj_contrat($codecontrat,$codelibtypepj,$txt_pj,$nom_form,$editable)
{ $contenu='';
	$rs=mysql_query("select codetypepj from typepjcontrat where codelibtypepj=".GetSQLValueString($codelibtypepj, "text")) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);  
	$codetypepj=$row_rs['codetypepj'];
	
	$rs=mysql_query("select contratpj.* from contratpj".
										" where codecontrat=".GetSQLValueString($codecontrat, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
  { $contenu=	'<a href="download.php?codecontrat='.$codecontrat.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
                                  '<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
		if($editable)															
    { $contenu.='&nbsp;&nbsp;<input type="image" name="submit_supprimer_une_pj#'.$codetypepj.'##" class="icon" src="images/b_drop.png" title="Supprimer '.$txt_pj.'" onClick="return confirm(\'Supprimer : '.$txt_pj.'\')">'.
                '&nbsp;&nbsp;<span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']"/>';
		}
  }
  else
  { if($editable)
		{ $contenu.='<span class="bleugrascalibri10">'.$txt_pj.' :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']">';
		}
  }
	if($editable)
	{ $contenu.=ligne_txt_upload_pj_info($codetypepj,$codelibtypepj);
	}
	if(isset($rs)) {mysql_free_result($rs);}
	return $contenu;
}

function ligne_txt_upload_pj_projet($codeprojet,$codelibtypepj,$txt_pj,$nom_form,$editable)
{ $contenu='';
	$rs=mysql_query("select codetypepj from typepjprojet where codelibtypepj=".GetSQLValueString($codelibtypepj, "text")) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);  
	$codetypepj=$row_rs['codetypepj'];
	
	$rs=mysql_query("select projetpj.* from projetpj".
									" where codeprojet=".GetSQLValueString($codeprojet, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
  { $contenu=	'<a href="download.php?codeprojet='.$codeprojet.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
                                  '<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
		if($editable)															
    { $contenu.='&nbsp;&nbsp;<input type="image" name="submit_supprimer_une_pj#'.$codetypepj.'##" class="icon" src="images/b_drop.png" title="Supprimer '.$txt_pj.'" onClick="return confirm(\'Supprimer : '.$txt_pj.'\')">'.
                '&nbsp;&nbsp;<span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']"/>';
		}
  }
  else
  { if($editable)
		{ $contenu.='<span class="bleugrascalibri10">'.$txt_pj.' :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']">';
		}
  }
	if($editable)
	{ $contenu.=ligne_txt_upload_pj_info($codetypepj,$codelibtypepj);
	}
	if(isset($rs)) {mysql_free_result($rs);}
	return $contenu;
}


function ligne_txt_upload_pj_commande($codecommande,$codelibtypepj,$txt_pj,$nom_form,$editable)
{ $contenu='';
	$rs=mysql_query("select codetypepj from typepjcommande where codelibtypepj=".GetSQLValueString($codelibtypepj, "text")) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);  
	$codetypepj=$row_rs['codetypepj'];
	
	$rs=mysql_query("select commandepj.* from commandepj".
										" where codecommande=".GetSQLValueString($codecommande, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
  { $contenu=	'<a href="download.php?codecommande='.$codecommande.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
                                  '<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
		if($editable)															
    { $contenu.='&nbsp;&nbsp;<input type="image" name="submit_supprimer_une_pj#'.$codetypepj.'##" class="icon" src="images/b_drop.png" title="Supprimer '.$txt_pj.'" onClick="return confirm(\'Supprimer : '.$txt_pj.'\')">'.
                '&nbsp;&nbsp;<span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']"/>';
		}
  }
  else
  { if($editable)
		{ $contenu.='<span class="bleugrascalibri10">'.$txt_pj.' :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']">';
		}
  }
	if($editable)
	{ $contenu.=ligne_txt_upload_pj_info($codetypepj,$codelibtypepj);
	}
	if(isset($rs)) {mysql_free_result($rs);}
	return $contenu;
}

function ligne_txt_upload_pj_mission($codemission,$codelibtypepj,$txt_pj,$nom_form,$editable)
{ $contenu='';
	$rs=mysql_query("select codetypepj from typepjmission where codelibtypepj=".GetSQLValueString($codelibtypepj, "text")) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);  
	$codetypepj=$row_rs['codetypepj'];
	
	$rs=mysql_query("select missionpj.* from missionpj".
										" where codemission=".GetSQLValueString($codemission, "text").
										" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
  { $contenu=	'<a href="download.php?codemission='.$codemission.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
                                  '<img src="images/b_download.png" border="0">&nbsp;<span class="vertgrascalibri10">'.$txt_pj.'</span></a>';
		if($editable)															
    { $contenu.='&nbsp;&nbsp;<input type="image" name="submit_supprimer_une_pj#'.$codetypepj.'##" class="icon" src="images/b_drop.png" title="Supprimer '.$txt_pj.'" onClick="return confirm(\'Supprimer : '.$txt_pj.'\')">'.
                '&nbsp;&nbsp;<span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']"/>';
		}
  }
  else
  { if($editable)
		{ $contenu.='<span class="bleugrascalibri10">'.$txt_pj.' :&nbsp;</span><input type="file" name="pj['.$codetypepj.']" class="noircalibri9" id="pj['.$codetypepj.']">';
		}
  }
	if($editable)
	{ $contenu.=ligne_txt_upload_pj_info($codetypepj,$codelibtypepj);
	}
	if(isset($rs)) {mysql_free_result($rs);}
	return $contenu;
}

function ligne_txt_upload_pj_info($codetypepj,$codelibtypepj)
{ $liste_file_types=implode(", ",$GLOBALS['file_types_array']);
	$txt_upload_pj_info='&nbsp;<img src="images/b_info.png" border="0" width="16" height="16" id="sprytrigger_info_pj['.$codetypepj.']">'.
					'<div class="tooltipContent_cadre" id="info_pj['.$codetypepj.']">'.
					'<span class="noircalibri10">Les fichiers accept&eacute;s sont limit&eacute;s &agrave; '.$GLOBALS['max_file_size_Mo'].'Mo'.
					' et doivent porter l&rsquo;une des extensions suivantes : <br>'.$liste_file_types.' (les extensions sont automatiquement transform&eacute;es en minuscules)<br>'.
					'La zone contenant le nom du fichier ne peut &ecirc;tre effac&eacute;e (uniquement modifi&eacute;e avec un autre fichier).<br>'.
					'Si un fichier a &eacute;t&eacute; s&eacute;lectionn&eacute; par erreur, la seule solution consiste &agrave; envoyer le formulaire qui retourne :<br>'.
					'- <img src="images/b_download.png" align="absbottom" border="0" width="16 height="16"> suivi du nom du fichier qui est t&eacute;l&eacute;chargeable ;<br>'.
					'- <img src="images/b_drop.png" border="0" align="absbottom" width="16 height="16"> pour supprimer le fichier (en cas d&rsquo;erreur notamment) ;<br>'.
					'</span>'.
					'- <span style="color:#FF9900; font-family:Calibri; font-size:10pt; font-weight:bold">remplacer par :&nbsp;</span>'.
					'<span class="noircalibri10">s&eacute;lectionner un nouveau fichier qui &eacute;crasera l&rsquo;ancien.<br>'.
					'Si, de plus, un message d&rsquo;erreur est affich&eacute; lors de l&rsquo;envoi du fichier, il faudra choisir un autre fichier ne provoquant pas<br>'.
					'cette erreur, l&rsquo;envoyer puis le supprimer...'.
					'</span></div>'.
					'<script type="text/javascript">'.
					'var sprytooltip_info_pj_'.$codelibtypepj.' = new Spry.Widget.Tooltip("info_pj['.$codetypepj.']", "#sprytrigger_info_pj['.$codetypepj.']", {offsetX:20, offsetY:20});'.
					'</script>';
	return $txt_upload_pj_info;
}

