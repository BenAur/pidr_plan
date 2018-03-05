<?php 

function construitliblabo($tabappel)
{ $liblabo="";
	if(isset($tabappel['appel']))
	{ if($tabappel['appel']=='mail_validation_declaration_fsd' || $tabappel['appel']=='confirmer_mail_validation_declaration_fsd')
		{ $liblabo=$GLOBALS['acronymelabo']." - ".$GLOBALS['acronymetutelle']['recherche']['cnrs']['acronyme']." - UMR ".$GLOBALS['num_umr'];
		}
		else if($tabappel['appel']=='fsd_detailindividu')
		{ $liblabo=$GLOBALS['liblonglabo']." - ".$GLOBALS['acronymetutelle']['recherche']['cnrs']['acronyme']." - UMR ".$GLOBALS['num_umr'];
		}
	}
	return $liblabo;
}

function farce($tab_param)
{ $tab_infouser=$tab_param['tab_info_user'];
	$txt='';
	if(strtolower($tab_infouser['nom'])=='gend' && strtolower(substr($tab_infouser['prenom'],0,1))=='p' && date("Y/m/d")=='2015/05/29' && date("H")>='99')
	{ if(isset($tab_param['prog']) && $tab_param['prog']=='menuprincipal')
		{ if(isset($tab_param['quoi']) && $tab_param['quoi']=='image') 
			{ $txt='<img src="images/farce/0.gif">';
			}
			else if(isset($tab_param['quoi']) && $tab_param['quoi']=='curseur') 
			{ $txt='document.body.style.cursor=\'url(\\\'images/farce/3.png\\\'), auto\';';
			}
		}
		else if(isset($tab_param['prog'])/*  && ($tab_param['prog']=='gestionindividus' || $tab_param['prog']=='gestioncontrats') */)
		{ if(isset($tab_param['quoi']) && $tab_param['quoi']=='curseur') 
			{ $txt='document.body.style.cursor=\'url(\\\'images/farce/1.png\\\'), auto\';';
			}
		}
		else if(isset($tab_param['quoi']) && $tab_param['quoi']=='stylecurseurhref')
		{ $txt='<style>body a:hover { cursor : url("images/farce/a.png"), auto;}</style>';
		}
	}
	return $txt;
}

