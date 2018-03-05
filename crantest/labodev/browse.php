<?php require_once('_const_fonc.php');
// gestion des exceptions pour getimagesize
require_once('conn_const/ma_classe_exception.php');

/* Necessite :
- $GLOBALS['path_to_rep_upload'].'/uploadfichier' et table uploadfichier contenant les enregistrements 00000 et 00001 
- table uploadfichier contenant les enregistrements 00000 et 00001
CREATE TABLE IF NOT EXISTS uploadfichier (
  code_rep_ou_fichier varchar(5) NOT NULL,
  codepere varchar(5) NOT NULL,
  type_rep_ou_fichier varchar(1) NOT NULL,
  codeproprietaire varchar(5) NOT NULL,
  nom varchar(100) NOT NULL,
  descr varchar(1000) NOT NULL,
  PRIMARY KEY (code_rep_ou_fichier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table 'uploadfichier'
--

INSERT INTO uploadfichier (code_rep_ou_fichier, codepere, type_rep_ou_fichier, codeproprietaire, nom, descr) VALUES('00000', '', 'R', '', '/', '');
INSERT INTO uploadfichier (code_rep_ou_fichier, codepere, type_rep_ou_fichier, codeproprietaire, nom, descr) VALUES('00001', '00000', 'R', '', 'users', '');

 */
function arborescence($noeud,$profondeur)
{ global $tab_arborescence,$nbnoeuds,$profondeur,$profondeur_max,$tab_repfichier_trie;
	$liste_enfants=liste_enfants($noeud);
	if(count($liste_enfants)==0)
	{ $profondeur_max=max($profondeur_max,$profondeur);
		return;
	}
	else
	{ $profondeur++;
		foreach($liste_enfants as $noeud)
		{ $nbnoeuds++;
			$tab_arborescence[$nbnoeuds]=array('code_rep_ou_fichier'=>$tab_repfichier_trie[$noeud]['code_rep_ou_fichier'],'profondeur'=>$profondeur,'nom'=>$tab_repfichier_trie[$noeud]['nom'],'type_rep_ou_fichier'=>$tab_repfichier_trie[$noeud]['type_rep_ou_fichier']);
			arborescence($noeud,$profondeur);
		}
		$profondeur--;
	}
}

function copie_arborescence($noeud_a_copier,$noeud_pere)
{ global $nbnoeuds,$tab_repfichier_trie,$rep_upload,$codeuser;
	$rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
	$row_rs=mysql_fetch_array($rs);
	$copie_code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
	$row_rs=$tab_repfichier_trie[$noeud_a_copier];
	$updateSQL=	"insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
								" values (".GetSQLValueString($copie_code_rep_ou_fichier, "text").",".GetSQLValueString($noeud_pere, "text").",".GetSQLValueString($row_rs['type_rep_ou_fichier'], "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($row_rs['nom'], "text").",'')";
	mysql_query($updateSQL) or die(mysql_error());
	// copie physique de fichier (uniquement) dans le dossier $rep_upload
	if($tab_repfichier_trie[$noeud_a_copier]['type_rep_ou_fichier']=='F')	
	{ copy($rep_upload.'/'.$noeud_a_copier,$rep_upload.'/'.$copie_code_rep_ou_fichier);
		//echo '<br>copy('.$rep_upload.'/'.$noeud_a_copier.','.$rep_upload.'/'.$copie_code_rep_ou_fichier.')';
	}
	$liste_enfants=liste_enfants($noeud_a_copier);
	if(count($liste_enfants)==0)
	{ return;
	}
	else
	{ $noeud_pere=$copie_code_rep_ou_fichier;
		foreach($liste_enfants as $noeud_a_copier)
		{ copie_arborescence($noeud_a_copier,$noeud_pere);
		}
	}
}

function liste_enfants($noeud)
{ global $tab_noeuds;
	$liste_enfants=array();
	foreach($tab_noeuds as $un_enfant=>$un_pere)
	{ if($noeud==$un_pere)
		{ $liste_enfants[]=$un_enfant;
		}
	}
	return $liste_enfants;
}

set_error_handler('error2exception');
set_exception_handler('customException');

$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
if(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd'])
{ /*foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}*/
}
$rep_upload=$GLOBALS['path_to_rep_upload'].'/uploadfichier';
$rep_racine_site='c:/users/pgend/Documents/htdocs/'.$GLOBALS['rep_racine_site12+'];
$rep_racine="00000";
$repracine_des_users="00001";
$rep_icone="images/ext_icone";
$erreur="";
$affiche_succes=false;
$form_browse="form_browse";
$tab_noeuds=array();
$tab_arborescence=array();
$tab_rep_ou_fichier_trie=array();
$tab_code_rep_ou_fichier_a_traiter=array();
$tab_code_rep_coller_interdit=array();

$CKEditorFuncNum = isset($_GET['CKEditorFuncNum'])?$_GET['CKEditorFuncNum']:"" ;
$CKEditor = isset($_GET['CKEditor'])?$_GET['CKEditor']:"" ;
$langCode = isset($_GET['langCode'])?$_GET['langCode']:"" ;
$nbcol_max=4;
$largeur_vignette_max=100;

$_SESSION['action']=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:(isset($_SESSION['action'])?$_SESSION['action']:''));
$code_rep_ou_fichier=isset($_GET['code_rep_ou_fichier'])?$_GET['code_rep_ou_fichier']:(isset($_POST['code_rep_ou_fichier'])?$_POST['code_rep_ou_fichier']:'') ;
$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:'');

$_SESSION['tab_code_rep_ou_fichier_a_traiter']=isset($_SESSION['tab_code_rep_ou_fichier_a_traiter'])?$_SESSION['tab_code_rep_ou_fichier_a_traiter']:array();
$_SESSION['codepere_origine']=isset($_SESSION['codepere_origine'])?$_SESSION['codepere_origine']:'';
$_SESSION['coderep_courant']=isset($_GET['coderep_courant'])?$_GET['coderep_courant']:(isset($_POST['coderep_courant'])?$_POST['coderep_courant']:'');
if($_SESSION['action']!='coller')
{ foreach($_POST as $key=>$value)
	{ $tab=explode('#',$key);
		if($tab[0]=='code_rep_ou_fichier_a_traiter')
		{ $tab_code_rep_ou_fichier_a_traiter[$tab[1]]=$tab[1];
		}
	}
	$_SESSION['tab_code_rep_ou_fichier_a_traiter']=$tab_code_rep_ou_fichier_a_traiter;
}

// racine pour ce codeuser : il existe ou on le crée automatiquement
if($_SESSION['coderep_courant']=='')
{ $rs_individu=mysql_query("select code_rep_ou_fichier,login,nom,prenom from individu where codeindividu=".GetSQLValueString($codeuser, "text"));
	$row_rs_individu=mysql_fetch_assoc($rs_individu);
	$coderep_user=$row_rs_individu['code_rep_ou_fichier'];
	if($coderep_user=='')// creation d'un rep pour ce user
	{ $rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
		$row_rs=mysql_fetch_array($rs);
		$code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
		$type_rep_ou_fichier='R';
		$updateSQL="insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
								" values (".GetSQLValueString($code_rep_ou_fichier, "text").",".GetSQLValueString($repracine_des_users, "text").",".GetSQLValueString($type_rep_ou_fichier, "text").",".
								GetSQLValueString($codeuser, "text").",".GetSQLValueString($row_rs_individu["login"], "text").",".
								GetSQLValueString($row_rs_individu["prenom"]." ".$row_rs_individu["nom"], "text").")" or die(mysql_error());
								//echo $updateSQL;
		mysql_query($updateSQL) or die(mysql_error());
		mysql_query("update individu set code_rep_ou_fichier=".GetSQLValueString($code_rep_ou_fichier, "text")." where codeindividu=".GetSQLValueString($codeuser, "text")) or die(mysql_error());
		$coderep_user=$code_rep_ou_fichier;
	}
	$_SESSION['coderep_user']=$coderep_user;
	$_SESSION['coderep_courant']=$coderep_user;
}
else if(isset($_SESSION['coderep_user']))
{ $_SESSION['coderep_courant']=isset($_GET['coderep_courant'])?$_GET['coderep_courant']:(isset($_POST['coderep_courant'])?$_POST['coderep_courant']:$_SESSION['coderep_user']) ;
}
else // pas de coderep_user, pas de coderep_courant !!!
{ http_redirect("formintranetpasswd.php?reconnexion=deconnexion");
}
$liste_enfants=array();
$nbnoeuds=0;
if(isset($_POST["MM_update"]) && $_POST["MM_update"] == $_SERVER['PHP_SELF']) 
{	if(isset($_POST["submit_enregistrer_ficher"]))
	{ foreach ($_FILES["pj"]["name"] as $key => $nom) 
		{ if($nom!='')
			{ $rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
				$row_rs=mysql_fetch_array($rs);
				$code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
				$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$code_rep_ou_fichier);
				$codepere=$_SESSION['coderep_courant'];
				$type_rep_ou_fichier='F';
				if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
				{	$updateSQL="insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
											" values (".GetSQLValueString($code_rep_ou_fichier, "text").",".GetSQLValueString($codepere, "text").",".GetSQLValueString($type_rep_ou_fichier, "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").",'')";
					mysql_query($updateSQL) or die(mysql_error());
				}
				else if($tab_res_upload['nomfichier']!='')
				{ $erreur.='<br>'.$tab_res_upload['erreur'];
				}
			}
		}
	}
	else if(isset($_POST["submit_nouveau_repertoire"]))
	{	$rs=mysql_query("select max(code_rep_ou_fichier) as maxcode_rep_ou_fichier from uploadfichier");
		$row_rs=mysql_fetch_array($rs);
		$code_rep_ou_fichier=str_pad((string)((int)$row_rs['maxcode_rep_ou_fichier']+1),5,'0',STR_PAD_LEFT);
		$type_rep_ou_fichier='R';
		$updateSQL="insert into uploadfichier (code_rep_ou_fichier,codepere,type_rep_ou_fichier,codeproprietaire,nom,descr)". 
								" values (".GetSQLValueString($code_rep_ou_fichier, "text").",".GetSQLValueString($_SESSION['coderep_courant'], "text").",".GetSQLValueString($type_rep_ou_fichier, "text").",".GetSQLValueString($codeuser, "text").",".GetSQLValueString($_POST["nouveau_rep_nom"], "text").",'')";
		mysql_query($updateSQL) or die(mysql_error());
		$_SESSION['coderep_courant']=$code_rep_ou_fichier;
	}
	else if($_SESSION['action']=='renommer')
	{ $updateSQL="update uploadfichier set nom=".GetSQLValueString($_POST["nouveau_nom"], "text")." where code_rep_ou_fichier=".GetSQLValueString($code_rep_ou_fichier, "text");
		mysql_query($updateSQL) or die(mysql_error());
	}
	else if($_SESSION['action']=='supprimer')
	{ $tab_repfichier_trie=array();
		$rs_uploadfichier=mysql_query("select * from uploadfichier order by type_rep_ou_fichier desc, LOWER(nom) asc");//d'abord les rep puis les fichiers
		$rs_noeuds=mysql_query("select code_rep_ou_fichier,codepere from uploadfichier order by codepere,type_rep_ou_fichier desc");
		while($row_rs_noeuds=mysql_fetch_assoc($rs_noeuds))
		{ $tab_noeuds[$row_rs_noeuds['code_rep_ou_fichier']]=$row_rs_noeuds['codepere'];
		}
		while($row_rs_uploadfichier=mysql_fetch_assoc($rs_uploadfichier))
		{ $tab_repfichier_trie[$row_rs_uploadfichier['code_rep_ou_fichier']]=$row_rs_uploadfichier;
		}
		foreach($_SESSION['tab_code_rep_ou_fichier_a_traiter'] as $key=>$code_rep_ou_fichier_a_traiter)
		{ /* $tab_noeuds=array(); */
			$tab_arborescence=array(); 
			arborescence($code_rep_ou_fichier_a_traiter,0);
			// ajout du rep en cours
			$tab_arborescence[]=array('code_rep_ou_fichier'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['code_rep_ou_fichier'],'profondeur'=>0,'nom'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['nom'],'type_rep_ou_fichier'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['type_rep_ou_fichier']);
			foreach($tab_arborescence as $num=>$une_ligne)
			{ if($une_ligne['type_rep_ou_fichier']=='F')
				{ unlink($rep_upload.'/'.$une_ligne['code_rep_ou_fichier']);
				}
				$updateSQL="delete from uploadfichier where code_rep_ou_fichier=".GetSQLValueString($une_ligne['code_rep_ou_fichier'], "text");
				mysql_query($updateSQL);
			}
		}
		$_SESSION['tab_code_rep_ou_fichier_a_traiter']=array();
	}
	else if($_SESSION['action']=='copier' || $_SESSION['action']=='couper')
	{ if($_SESSION['action']=='copier')
		{ $_SESSION['codepere_origine']=$_SESSION['coderep_courant'];
		}
		else
		{ $_SESSION['codepere_origine']='';
		}
		$_SESSION['action']='coller';//on force le collage a la prochaine action utilisateur
	}
	else if($_SESSION['action']=='coller')
	{ if($_SESSION['codepere_origine']=='')//couper => change le pere des elements
		{ foreach($_SESSION['tab_code_rep_ou_fichier_a_traiter'] as $key=>$code_rep_ou_fichier_a_traiter)
			{ $updateSQL="update uploadfichier set codepere=".GetSQLValueString($_SESSION['coderep_courant'], "text")." where code_rep_ou_fichier=".GetSQLValueString($code_rep_ou_fichier_a_traiter, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		else//copier=>il faut l'arborescence complete pour chaque rep de $_SESSION['tab_code_rep_ou_fichier_a_traiter']
		{	$tab_copie_arborescence=array();
			$tab_repfichier_trie=array();
			$rs_uploadfichier=mysql_query("select * from uploadfichier order by type_rep_ou_fichier desc, LOWER(nom) asc");//d'abord les rep puis les fichiers
			while($row_rs_uploadfichier=mysql_fetch_assoc($rs_uploadfichier))
			{ $tab_repfichier_trie[$row_rs_uploadfichier['code_rep_ou_fichier']]=$row_rs_uploadfichier;
			}
			$rs_noeuds=mysql_query("select code_rep_ou_fichier,codepere from uploadfichier order by codepere,type_rep_ou_fichier desc");
			while($row_rs_noeuds=mysql_fetch_assoc($rs_noeuds))
			{ $tab_noeuds[$row_rs_noeuds['code_rep_ou_fichier']]=$row_rs_noeuds['codepere'];
			}
			foreach($_SESSION['tab_code_rep_ou_fichier_a_traiter'] as $key=>$code_rep_ou_fichier_a_traiter)
			{	copie_arborescence($code_rep_ou_fichier_a_traiter,$_SESSION['coderep_courant']);
			}
		}
		$_SESSION['codepere_origine']='';
		$_SESSION['tab_code_rep_ou_fichier_a_traiter']=array();
		$_SESSION['action']='';
	}
	else if($_SESSION['action']=='annulercoller')
	{ $_SESSION['tab_code_rep_ou_fichier_a_traiter']=array();
		$_SESSION['codepere_origine']='';
		$_SESSION['action']='';
	}
}
$tab_repfichier_trie=array();
$tab_noeuds=array();
$tab_arborescence=array();
// données du formulaire
$rs_uploadfichier=mysql_query("select * from uploadfichier order by type_rep_ou_fichier desc, LOWER(nom) asc");//d'abord les rep puis les fichiers
$tab_rep_ou_fichier_trie=array();
while($row_rs_uploadfichier=mysql_fetch_assoc($rs_uploadfichier))
{ $tab_repfichier_trie[$row_rs_uploadfichier['code_rep_ou_fichier']]=$row_rs_uploadfichier;
}
 
$rs_noeuds=mysql_query("select code_rep_ou_fichier,codepere from uploadfichier order by codepere,type_rep_ou_fichier desc, LOWER(nom) asc");
while($row_rs_noeuds=mysql_fetch_assoc($rs_noeuds))
{ $tab_noeuds[$row_rs_noeuds['code_rep_ou_fichier']]=$row_rs_noeuds['codepere'];
}

// noeud rep dans lesquels on ne peut pas coller les elements qui viennent d'etre coupes/colles
$tab_code_rep_coller_interdit=array();
$tab_arborescence=array(); 
$nbnoeuds=0;
foreach($_SESSION['tab_code_rep_ou_fichier_a_traiter'] as $key=>$code_rep_ou_fichier_a_traiter)
{ if($tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['type_rep_ou_fichier']=='R')
	{ // ajout du rep lui meme
		$tab_arborescence[$nbnoeuds]=array('code_rep_ou_fichier'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['code_rep_ou_fichier'],'profondeur'=>0,'nom'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['nom'],'type_rep_ou_fichier'=>$tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['type_rep_ou_fichier']);
		arborescence($code_rep_ou_fichier_a_traiter,0);
	}
} /**/
foreach($tab_arborescence as $num=>$une_ligne)
{ if($une_ligne['type_rep_ou_fichier']=='R')
	{ $tab_code_rep_coller_interdit[$une_ligne['code_rep_ou_fichier']]=$une_ligne['code_rep_ou_fichier'];
	}
}

?>

<!--<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> -->
<html>
<head>
<title>Syst&egrave;me de fichiers</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<style type="text/css">
#menu_rep_fichier,#renommer,#supprimer {
	position: absolute;
	background-color: #FFF;
	border: thin solid #999;
	width: auto;
	height: auto;
	color: #006;
	border-radius: 3px;
	padding: 2px;
	font-family: Calibri;
	font-size: 12px;
	margin: 1px;
	text-align: justify;
}
a:link {text-decoration:underline;}
a:visited {text-decoration:none;}
a:hover {text-decoration:underline;}
a:active {text-decoration:underline;}
</style>

<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
window.outerWidth = screen.width/2;
window.outerHeight = screen.height/2;
var tab_repfichier_trie=new Array();
var nbtab_repfichier_trie=<?php echo count($tab_repfichier_trie); ?>;
var tab_repfichier_selectionne=new Array();//elements selectionnes par choix utilisateur (ajout pour chaque checkbox on, enleve pour chaque checkbox off)  
var tab_code_rep_coller_interdit=new Array();//table des elements et leurs enfants a coller
 
var code_rep_ou_fichier_courant='';
var action_en_cours='';
<?php
foreach($tab_repfichier_trie as $un_code_rep_ou_fichier=>$row_rs_uploadfichier)
{ ?> tab_repfichier_trie["<?php echo $un_code_rep_ou_fichier ?>"]="<?php echo js_tab_val( $row_rs_uploadfichier['nom']); ?>";
<?php
}
?>

function size(objet)
{ nbelem=0;
	for(key in objet)
	{ nbelem++;
	}
	return nbelem;
}

function position_objet(objet,position,event)
{	event.returnValue = false;
	if( event.preventDefault ) event.preventDefault();
	//Coordonnees de la souris
	var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
	var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonnées de l'élément
	var eX = 0;
	var eY = 0;
	var element = objet;
	i=0;
	do
	{ i++;
		eX += element.offsetLeft;
		eY += element.offsetTop;
		element = element.offsetParent;
	} while( element && element.style.position != position);
	return new Array(eX,eY);
}

function menu_rep_fichier(objet,type_rep_ou_fichier,code_rep_ou_fichier,event)
{ // pas de selection d'element possible pour les cas suivants
	if(document.forms['<?php echo $form_browse ?>'].elements['action'].value=='coller' || action_en_cours=='renommer' || action_en_cours=='supprimer')
	{ objet.checked=!objet.checked;
	}
	else
	{	if(objet.checked)//si case cochee
		{ tab_repfichier_selectionne[code_rep_ou_fichier]=tab_repfichier_trie[code_rep_ou_fichier];
			if(action_en_cours=='')
			{	document.forms['<?php echo $form_browse ?>'].elements['code_rep_ou_fichier'].value=code_rep_ou_fichier;
				code_rep_ou_fichier_courant=code_rep_ou_fichier;
				tab_coord=position_objet(objet,'absolute',event);
				document.getElementById('menu_rep_fichier').className="affiche";
				document.getElementById('menu_rep_fichier').style.position = 'absolute';
				document.getElementById('menu_rep_fichier').style.left = (tab_coord[0]+20) + 'px';
				document.getElementById('menu_rep_fichier').style.top = tab_coord[1] + 'px';
			}
		}
		else // sinon on l'enleve de la liste des fichiers selectionnes
		{ delete tab_repfichier_selectionne[code_rep_ou_fichier];
		}
		if(size(tab_repfichier_selectionne)==0)// aucun fichier selectionne
		{ document.getElementById('menu_rep_fichier').className="cache";
		}
		if(size(tab_repfichier_selectionne)!=1)//changer la couleur de l'item renommer
		{ document.getElementById('menu_rep_fichier_td#2').className="blanccalibri10";
			document.getElementById('menu_rep_fichier_td#5').className="blanccalibri10";
		}
		else
		{ document.getElementById('menu_rep_fichier_td#2').className="noircalibri10";
			document.getElementById('menu_rep_fichier_td#5').className="noircalibri10";
		}
	}
	return;
}

function choix_menu_rep_fichier(objet,action,event)
{ if(action_en_cours!='')//ferme la boite de dialogue en cours
	{ document.getElementById(action_en_cours).className="cache";
	}
	action_en_cours=action;
	document.forms['<?php echo $form_browse ?>'].elements['action'].value=action;
	if(action=='renommer' && size(tab_repfichier_selectionne)==1)
	{ tab_coord=position_objet(objet,'relative',event);
		document.getElementById('menu_rep_fichier').className="cache";
		document.getElementById('message_'+action).innerHTML='<span class="bleugrascalibri10">Renommer&nbsp;</span>'+tab_repfichier_trie[code_rep_ou_fichier_courant];
		tab_suffixe=tab_repfichier_trie[code_rep_ou_fichier_courant].split(".");
		if(tab_suffixe.length>1)//precomplete le suffixe
		{ document.forms['<?php echo $form_browse ?>'].elements['nouveau_nom'].value='.'+tab_suffixe[1];
		}
		document.getElementById(action).className="affiche";
		document.getElementById(action).style.position = 'absolute';
		document.getElementById(action).style.left = (tab_coord[0]+20) + 'px';
		document.getElementById(action).style.top = tab_coord[1] + 'px';
		document.forms['<?php echo $form_browse ?>'].elements['nouveau_nom'].focus();
	}
	else if(action=='supprimer')
	{ tab_coord=position_objet(objet,'relative',event);
		document.getElementById('menu_rep_fichier').className="cache";
		liste_fichier='';
		for(key in tab_repfichier_selectionne)
		{ liste_fichier+='<br>'+tab_repfichier_selectionne[key];
		}
		document.getElementById('message_'+action).innerHTML='<span class="bleugrascalibri10">Supprimer&nbsp;</span>'+liste_fichier;
		document.getElementById(action).className="affiche";
		document.getElementById(action).style.position = 'absolute';
		document.getElementById(action).style.left = (tab_coord[0]+20) + 'px';
		document.getElementById(action).style.top = tab_coord[1] + 'px';
	}
	else if(action=='selectionner' && size(tab_repfichier_selectionne)==1)
	{ window.opener.CKEDITOR.tools.callFunction('<?php echo $CKEditorFuncNum ?>','/<?php echo $GLOBALS['rep_racine_site12+'] ?>/download_public.php?codeuploadfichier='+code_rep_ou_fichier_courant);
		window.close();
	}
	else
	{ document.forms['<?php echo $form_browse ?>'].elements['action'].value=action;
		//document.forms['<?php echo $form_browse ?>'].elements['coderep_courant'].value=objet;
		document.forms['<?php echo $form_browse ?>'].submit();
	}
	return;
}

</script>
</head>

<body>
<form name="<?php echo $form_browse ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="coderep_courant" value="<?php echo $_SESSION['coderep_courant'] ?>" />
<input type="hidden" name="CKEditorFuncNum" value="<?php echo $CKEditorFuncNum ?>" />
<input type="hidden" name="CKEditor" value="<?php echo $CKEditor ?>" />
<input type="hidden" name="langCode" value="<?php echo $langCode ?>" />
<input type="hidden" name="type_fichier" value="<?php echo $type_fichier ?>">
<input type="hidden" name="code_rep_ou_fichier" value="">
<input type="hidden" name="action" value="<?php echo $_SESSION['action'] ?>">
<table>
	<tr>
		<td>
			<div id="menu_rep_fichier" class="cache"><!-- menu_rep_fichier -->
			<table cellpadding="1">
				<tr id="menu_rep_fichier_entete"><td align="right"><img src="images/b_annuler_rouge.png" onClick="document.getElementById('menu_rep_fichier').className='cache';action_en_cours=''"></td></tr>
				<tr class="odd" id="menu_rep_fichier#5"  onClick="choix_menu_rep_fichier(this,'selectionner',event)"><td id="menu_rep_fichier_td#5" class="noircalibri10">S&eacute;lectionner</td></tr>
				<tr class="even" id="menu_rep_fichier#1" onClick="choix_menu_rep_fichier(this,'supprimer',event)"><td id="menu_rep_fichier_td#1" class="noircalibri10">Supprimer</td></tr>
				<tr class="odd"  id="menu_rep_fichier#2" onClick="choix_menu_rep_fichier(this,'renommer',event)"><td id="menu_rep_fichier_td#2" class="noircalibri10">Renommer</td></tr>
				<tr class="even" id="menu_rep_fichier#3" onClick="choix_menu_rep_fichier(this,'copier',event)"><td id="menu_rep_fichier_td#3" class="noircalibri10">Copier</td></tr>
				<tr class="odd" id="menu_rep_fichier#4"  onClick="choix_menu_rep_fichier(this,'couper',event)"><td id="menu_rep_fichier_td#4" class="noircalibri10">Couper</td></tr>
			</table>
			</div>
			<div id="renommer" class="cache">
			<table>
				<tr>
					<td nowrap>
					<div name="message_renommer" id="message_renommer">
					</div>
					<span class="bleugrascalibri10">&nbsp;en&nbsp;</span><input type="text" class="bleucalibri10" name="nouveau_nom" id="nouveau_nom">
					<input type="image" src="images/b_confirmer.png" width="58" heigth="17" name="confirme_renomme" id="confirme_renomme" onClick="return check_renomme()">
					<script>
					function check_renomme()
					{ if(document.forms['<?php echo $form_browse ?>'].elements['nouveau_nom'].value.length==0)
						{ alert('Nom vide !');
							return false;
						}
						else
						{ document.forms['<?php echo $form_browse ?>'].submit();
							return true;
						}
					}
					</script>
          <img src="images/b_annuler.png" width="58" heigth="17" onClick="document.getElementById('renommer').className='cache';document.getElementById('menu_rep_fichier').className='cache';action_en_cours=''">
					</td>
				</tr>
			</table>
			</div>
			<div id="supprimer" class="cache">
			<table>
				<tr>
					<td nowrap>
					<div name="message_supprimer" id="message_supprimer">
					</div>
					</td>
				</tr>
				<tr>
					<td align="center">
					<input type="image" src="images/b_confirmer.png" width="58" heigth="17" name="confirme_supprime" id="confirme_supprime" onClick="document.forms['<?php echo $form_browse ?>'].submit()">
					<img src="images/b_annuler.png" width="58" heigth="17" onClick="document.getElementById('supprimer').className='cache';document.getElementById('menu_rep_fichier').className='cache';action_en_cours=''">
					</td>
				</tr>
			</table>
		</div>
		<?php 
		if($_SESSION['action']=='coller')//coller a faire
		{	// collage interdit dans les sous dossiers du dossier a coller
			$coller_interdit=false;
			if(array_key_exists($_SESSION['coderep_courant'],$tab_code_rep_coller_interdit))
			{ $coller_interdit=true;
			}?>
			<div id="tab_code_rep_ou_fichier_a_traiter">
				<table class="table_cadre_arrondi">
				 <tr>
					<td class="bleucalibri10">El&eacute;ments &agrave; traiter :</td>
						<td><input type="image" name="b_coller" src="images/menufichier/b_coller<?php echo $coller_interdit?'_interdit':'' ?>.png" 
								onClick="document.forms['<?php echo $form_browse ?>'].elements['action'].value='coller';document.forms['<?php echo $form_browse ?>'].submit()">
						</td>
						<td><input type="image" name="b_annulercoller" src="images/b_annulercoller.png" 
								onClick="document.forms['<?php echo $form_browse ?>'].elements['action'].value='annulercoller';document.forms['<?php echo $form_browse ?>'].submit()">
						</td>
					</tr>
				<?php foreach($_SESSION['tab_code_rep_ou_fichier_a_traiter'] as $code_rep_ou_fichier_a_traiter)
				{?> <tr><td><?php echo $tab_repfichier_trie[$code_rep_ou_fichier_a_traiter]['nom'] ?></td></tr>
				<?php
				}?>
				</table>
			</div>
		<?php 
		}
		?>
		</td>
	</tr>
	<tr>
		<td>
			<table>
				<tr>
					<td>
						<?php 
						if($erreur!='' || $affiche_succes)
						{ ?>
						<table align="center" cellspacing="5" >
							<tr>
								<?php 
								if($erreur!='')
								{ ?>
								<td valign="top"><img src="images/attention.png" align="top"></td>
								<td valign="top">
									<span class="rougecalibri9">L&rsquo;enregistrement n&rsquo;a pas &eacute;t&eacute; effectu&eacute;. Veuillez corriger les erreurs.</span>
									<span class="rougecalibri9"><?php echo $erreur ?></span>
									</td>
								<?php 
								} 
								else if($affiche_succes)
								{ ?>
								<td valign="top"><img src="images/succes.png" align="top"></td>
								<td valign="top">
									<span class="vertcalibri11">Enregistrement effectu&eacute; avec succ&egrave;s.</span>
									</td>
								<?php 
								} ?> 
								</tr>
							</table>
						<?php 
					}?>
					</td>
				</tr>
					<?php
					// ajout des ascendants (et) du coderep_courant jusqu'a $_SESSION['coderep_user']
					$profondeur=0;
					$tab_arborescence=array();
					$nbnoeuds=0;
					$tab_ascendants=array();
					$numascendants=0;
					$coderep_non_affiche=$tab_noeuds[$_SESSION['coderep_user']];//pere du rep_user non affiche
					$coderep_pere=$_SESSION['coderep_courant'];//noeud courant non compris dans arborescence et ascendants
					while($coderep_pere!=$coderep_non_affiche)
					{ $tab_ascendants[$numascendants]=$coderep_pere;
						$coderep_pere=$tab_noeuds[$coderep_pere];
						$numascendants++;
					}
					$numascendants--;
					// remise en ordre inverse de $tab_ascendants : remplit le debut de $tab_arborescence
					for($num=$numascendants;$num>=0;$num--)
					{ $tab_arborescence[$num]=array('code_rep_ou_fichier'=>$tab_repfichier_trie[$tab_ascendants[$num]]['code_rep_ou_fichier'],'profondeur'=>$numascendants-$num,'nom'=>$tab_repfichier_trie[$tab_ascendants[$num]]['nom'],'type_rep_ou_fichier'=>$tab_repfichier_trie[$tab_ascendants[$num]]['type_rep_ou_fichier']);
					}
					$nbnoeuds=$numascendants;
					$profondeur=$numascendants;
					?>
					<tr>
						<td>
							<table>
								<tr>
									<td>
										<table cellpadding="4">
											<tr>
												<td>
												</td>
												<td align="left" bgcolor="#F4FE7E"><!-- chemin dossier courant en haut-->
												<?php
												$first=true; 
												foreach($tab_arborescence as $num=>$une_ligne)
												{ $un_code_rep_ou_fichier=$une_ligne['code_rep_ou_fichier'];
													echo ($first?"":" > ");
													$first=false;?>
													<span class="bleucalibri10"><a href="browse.php?type_fichier=<?php echo $type_fichier ?>&CKEditor=<?php echo $CKEditor ?>&CKEditorFuncNum=<?php echo $CKEditorFuncNum ?>&langCode=<?php echo $langCode ?>&coderep_courant=<?php echo $un_code_rep_ou_fichier ?>"/><?php echo $tab_repfichier_trie[$tab_ascendants[$num]]['nom'] ?></a></span>
												<?php 
												}?>
												</td>
											</tr>
											<tr>
												<td valign="top" bgcolor="#F4FE7E"><!-- arborescence a gauche -->
													<?php 
                          // liste des enfants du repertoire en cours
                          $profondeur_max=0;
                          arborescence($_SESSION['coderep_courant'],$profondeur);
                          ?>
                          <?php 
                          foreach($tab_arborescence as $num=>$une_ligne)
                          { $un_code_rep_ou_fichier=$une_ligne['code_rep_ou_fichier'];
                            if($une_ligne['type_rep_ou_fichier']=='R')
                            { $icone='rep';
                            }
                            else
                            { $tab=explode('.',$une_ligne['nom']);
                              $icone=count($tab)>1?strtolower($tab[count($tab)-1]):'unknown';
                            }?>
                            <img src="images/espaceur.gif" width="<?php echo 14*$une_ligne['profondeur'] ?>" height="1" /><img src="<?php echo $rep_icone ?>/<?php echo $icone ?>.png" width="20" height="20" align="absbottom"/>&nbsp;<?php if ($une_ligne['type_rep_ou_fichier']=='R') {?><a href="browse.php?type_fichier=<?php echo $type_fichier ?>&CKEditor=<?php echo $CKEditor ?>&CKEditorFuncNum=<?php echo $CKEditorFuncNum ?>&langCode=<?php echo $langCode ?>&coderep_courant=<?php echo $un_code_rep_ou_fichier ?>"/><?php }?><?php echo $une_ligne['nom']?><?php if ($une_ligne['type_rep_ou_fichier']=='R') {?></a><?php }?><br>
                          <?php 
                          }
                          // liste des enfants du rep courant a afficher dans la cellule
                          $liste_enfants_courants=liste_enfants($_SESSION['coderep_courant']);
                          $tab_repfichier_courants_a_afficher=array();
                          foreach($liste_enfants_courants as $noeud)
                          { $tab_repfichier_courants_a_afficher[$noeud]=$tab_repfichier_trie[$noeud];
                          }?>
												</td>
												<td bgcolor="#F3F3F3" valign="top"><!-- contenu du rep -->
													<?php
													$cellule_contenu_rep='';
													$cellule_contenu_rep.='<table>';
													$cellule_contenu_rep.='<tr>';//ligne pour largeur minimale de cellule 
													for($nbcol=0;$nbcol<$nbcol_max;$nbcol++)
													{ $cellule_contenu_rep.='<td><img src="images/espaceur.gif" width="'.$largeur_vignette_max.'" height="0"></td>';
													}
													$cellule_contenu_rep.='</tr>';
													$nbcol=0;
													$sautligne=false;
													$first_ligne=true;
													$nb_rep_ou_fichier_a_afficher=0;
													foreach ($tab_repfichier_courants_a_afficher as $un_code_rep_ou_fichier=>$un_rep_ou_fichier) 
													{ $affiche=false;
														$nbcol++;
														$nom= $un_rep_ou_fichier['nom'];
														$descr= $un_rep_ou_fichier['descr'];
														$type_rep_ou_fichier=$un_rep_ou_fichier['type_rep_ou_fichier'];
														$estimage=false;
														$cellule_rep_ou_fichier='';
														$cellule_rep_ou_fichier.='<td class="bleucalibri10" valign="bottom"><!-- un rep ou fichier -->';
														$cellule_rep_ou_fichier.='<table>';
														$cellule_rep_ou_fichier.='<tr>';
														$cellule_rep_ou_fichier.='<td align="center"><!-- vignette -->';
														if(file_exists($rep_upload.'/'.$un_code_rep_ou_fichier))
														{ try
															{ $estimage= getimagesize($rep_upload.'/'.$un_code_rep_ou_fichier);
															}
															catch(Exception $e)
															{ $estimage=false; 
															}
														}
														if(!$estimage)
														{ if($type_rep_ou_fichier=='R')
															{ $affiche=true;
																$cellule_rep_ou_fichier.='<img src="'.$rep_icone.'/rep.png"';
																$cellule_rep_ou_fichier.=' onClick="menu_rep_fichier(this,\''.$type_rep_ou_fichier.'\',\''.$un_code_rep_ou_fichier.'\',event)"';
																$cellule_rep_ou_fichier.=' ondblclick="document.location.href=\'browse.php?type_fichier='.$type_fichier.'&CKEditor='.$CKEditor.'&CKEditorFuncNum='.$CKEditorFuncNum.'&langCode='.$langCode.'&coderep_courant='.$un_code_rep_ou_fichier.'\'"/>';
															}
															else
															{ if($type_fichier!='image')
																{ $affiche=true;
																	$tab=explode('.',$nom);
																	$icone=count($tab)>1?strtolower($tab[count($tab)-1]):'unknown';
																	$cellule_rep_ou_fichier.='<img src="'.$rep_icone.'/'.$icone.'.png"';
																	$cellule_rep_ou_fichier.=' onClick="menu_rep_fichier(this,\''.$type_rep_ou_fichier.'\',\''.$un_code_rep_ou_fichier.'\',event)"';
																	$cellule_rep_ou_fichier.=' ondblclick="window.opener.CKEDITOR.tools.callFunction('.$CKEditorFuncNum.', \'/'.$GLOBALS['rep_racine_site12+'].'/download_public.php?codeuploadfichier='.$un_code_rep_ou_fichier.'\');window.close()">';
																}
															}
														}
														else
														{ $affiche=true;
															if($estimage)//if($type_fichier=='image')
															{ $tab=getimagesize($rep_upload.'/'.$un_code_rep_ou_fichier);
																$width=$tab[0];
																$height=$tab[1];
																$largeur_vignette=$width;
																$hauteur_vignette=$height;
																if($width>$largeur_vignette_max)
																{ $hauteur_vignette=$height*($largeur_vignette_max/$width);
																	$largeur_vignette=$largeur_vignette_max;
																}
																$cellule_rep_ou_fichier.='<img src="download.php?codefichier='.$un_code_rep_ou_fichier.'"';
																$cellule_rep_ou_fichier.='  width="'.$largeur_vignette.'"'; 
																$cellule_rep_ou_fichier.='  height="'.$hauteur_vignette.'"';
																$cellule_rep_ou_fichier.='  id="sprytrigger_image'.$un_code_rep_ou_fichier.'"';
																$cellule_rep_ou_fichier.='  onClick="menu_rep_fichier(this,\''.$type_rep_ou_fichier.'\',\''.$un_code_rep_ou_fichier.'\',event)"';
																$cellule_rep_ou_fichier.='  ondblclick="window.opener.CKEDITOR.tools.callFunction('.$CKEditorFuncNum.', \'/'.$GLOBALS['rep_racine_site12+'].'/download_public.php?codeuploadfichier='.$un_code_rep_ou_fichier.'\');window.close()"';
																$cellule_rep_ou_fichier.='>	';													
																$cellule_rep_ou_fichier.='  <div class="tooltipContent_cadre" id="image'.$un_code_rep_ou_fichier.'">';
																$cellule_rep_ou_fichier.='    <img src="download.php?codefichier='.$un_code_rep_ou_fichier.'"'; 
																$cellule_rep_ou_fichier.='    width="'.($largeur_vignette*2).'"'; 
																$cellule_rep_ou_fichier.='    height="'.($hauteur_vignette*2).'"';
																$cellule_rep_ou_fichier.='  </div>';
																$cellule_rep_ou_fichier.='  <script type="text/javascript">';
																$cellule_rep_ou_fichier.='    var sprytooltip_image'.$un_code_rep_ou_fichier.' = new Spry.Widget.Tooltip("image'.$un_code_rep_ou_fichier.'", "#sprytrigger_image'.$un_code_rep_ou_fichier.'", {offsetX:-100, offsetY:20});';
																$cellule_rep_ou_fichier.='   </script>';
																$cellule_rep_ou_fichier.='  <br>';
															}
															else
															{	$tab=explode('.',$nom);
																$icone=count($tab)>1?strtolower($tab[count($tab)-1]):'unknown';
																$cellule_rep_ou_fichier.='<img src="'.$rep_icone.'/'.$icone.'.png"'; 
																$cellule_rep_ou_fichier.=' onClick="menu_rep_fichier(this,\''.$type_rep_ou_fichier.'\',\''.$un_code_rep_ou_fichier.'\',event)"';
																$cellule_rep_ou_fichier.=' ondblclick="window.opener.CKEDITOR.tools.callFunction('.$CKEditorFuncNum.', \'/'.$GLOBALS['rep_racine_site12+'].'/download_public.php?codeuploadfichier='.$un_code_rep_ou_fichier.'\');window.close()">';
															}
														} 
														$cellule_rep_ou_fichier.='</td>';
														$cellule_rep_ou_fichier.='</tr>';
														$cellule_rep_ou_fichier.='<tr>';
														$cellule_rep_ou_fichier.='  <td align="center" nowrap><!-- nom rep ou fichier -->';
														$cellule_rep_ou_fichier.='<input type="checkbox" name="code_rep_ou_fichier_a_traiter#'.$un_code_rep_ou_fichier.'" onChange="menu_rep_fichier(this,\''.$type_rep_ou_fichier.'\',\''.$un_code_rep_ou_fichier.'\',event)">';
														$cellule_rep_ou_fichier.=$nom; 
														$cellule_rep_ou_fichier.='  </td>';
														$cellule_rep_ou_fichier.='</tr>';
														$cellule_rep_ou_fichier.='</table>';
														$cellule_rep_ou_fichier.='</td><!-- fin un element rep ou fichier -->';
														if($affiche)
														{ $cellule_contenu_rep.=$cellule_rep_ou_fichier;
															$nb_rep_ou_fichier_a_afficher++;
														}
														if($nbcol==$nbcol_max)
														{ $nbcol=0;
															$cellule_contenu_rep.='</tr>';
															$cellule_contenu_rep.='<tr>';
														}
													}
													// Complete la derniere ligne y compris s'il n'y a pas de fichier
													if($nbcol!=0 || $nb_rep_ou_fichier_a_afficher==0)
													{	while($nbcol<$nbcol_max)
														{ $nbcol++;
															$cellule_contenu_rep.='<td><img src="images/espaceur.gif" width="'.$largeur_vignette_max.'" height="0"></td>';
														}
													}
													$cellule_contenu_rep.='</tr>';
													$cellule_contenu_rep.='</table>';
													{ echo $cellule_contenu_rep;
													}
														?>
												</td><!-- fin contenu du rep -->
											</tr>
											<tr>
												<td colspan="2"><!-- creation rep, upload fichiers -->
													<table>
														<tr>
															<td><img src="images/b_nouveau_fichier.png" name="image_nouveau_fichier" id="image_nouveau_fichier" 
																	onClick="javascript:
																						nouveau_fichier=window.document.getElementById('nouveau_fichier');
																						if(nouveau_fichier.className=='affiche')
																						{ nouveau_fichier.className='cache';
																						}
																						else 
																						{ nouveau_fichier.className='affiche';
																						}
																					">
															 </td>
															 <td>
																<div name="nouveau_fichier" id="nouveau_fichier" class="cache">
																	<table>
																		<tr>
																			<td><input type="file" name="pj[]" class="noircalibri10" id="pj[]">
																			</td>
																			<td colspan="<?php echo $nbcol ?>">
																				<input name="submit_enregistrer_ficher" type="submit" class="noircalibri10" value="Enregistrer le fichier" />
																			</td>
																		</tr>
																	 </table>
																 </div>
															</td>
														</tr>
														<tr>
															<td><img src="images/b_nouveau_dossier.png" name="image_nouveau_dossier" id="image_nouveau_dossier" 
																	 onClick="javascript:
																						nouveau_dossier=window.document.getElementById('nouveau_dossier');
																						if(nouveau_dossier.className=='affiche')
																						{ nouveau_dossier.className='cache';
																							//window.document.getElementById('image_nouveau_dossier').src='images/b_plus.png';
																						}
																						else 
																						{ nouveau_dossier.className='affiche';
																							//window.document.getElementById('image_nouveau_dossier').src='images/b_moins.png';
																						}
																					">
															 </td>
															 <td>
																	<div name="nouveau_dossier" id="nouveau_dossier" class="cache">
																	<table>
																		<tr>
																			<td><span class="bleucalibri10">Nom :&nbsp;</span><input type="text" name="nouveau_rep_nom" id="nouveau_rep_nom" class="noircalibri8">
																			</td>
																			<td colspan="<?php echo $nbcol ?>">
																				<input name="submit_nouveau_repertoire" type="submit" class="noircalibri10" value="Cr&eacute;er le dossier" onClick="return check_nouveau_repertoire()"/>
																				<script>
                                        function check_nouveau_repertoire()
                                        { if(document.forms['<?php echo $form_browse ?>'].elements['nouveau_rep_nom'].value.length==0)
                                          { alert('Nom vide !');
                                            return false;
                                          }
                                          else
                                          { document.forms['<?php echo $form_browse ?>'].submit();
                                            return true;
                                          }
                                        }
                                        </script>
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
if(isset($rs_uploadfichier)) {mysql_free_result($rs_uploadfichier);}
?>