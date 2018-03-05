<?php 
/*
// les departements sont des equipes, le cran est l'equipe des refs sans departement
// 
//import HAL par collection/tampon
pour chaque collection {"CRAN","CRAN-CID","CRAN-ISET","CRAN-SBS"}
  url = 'http://api.archives-ouvertes.fr/search/'.collection.'/?wt=bibtex&rows=10000&q=producedDateY_i:'.date("Y");
	resultat_bib=GET(url)
	enregistre resultat_bib dans fichier collection+".bib"

// numeros de categorie et libelles correspondant aux types de publis bibcat 
// supprimer une ligne de ce tableau n'est pas genant si une bibcat n'existe plus
tab_corresp_bibcat=array(
"article"=>{"category"=>"1","liblongcat"=>"Articles dans des revues avec comité de lecture"},
"book"=>{"category"=>"3","liblongcat"=>"Ouvrages scientifiques"},
"incollection"=>{"category"=>"4","liblongcat"=>"Chapitres d'ouvrages scientifiques"},
"OE"=>{"category"=>"5","liblongcat"=>"Directions d'ouvrages"},
"inproceedings"=>{"category"=>"6","liblongcat"=>"Communications avec actes"},
"CD"=>{"category"=>"7","liblongcat"=>"Communications sans actes"},
"HD"=>{"category"=>"8","liblongcat"=>"HDR"},
"phdthesis"=>{"category"=>"9","liblongcat"=>"Theses"},
"misc"=>{"category"=>"10","liblongcat"=>"Autres publications"},
"CONF_INV"=>{"category"=>"11","liblongcat"=>"Conférences invitées"},
"proceedings"=>{"category"=>"12","liblongcat"=>"Actes de conférences"},
"patent"=>{"category"=>"13","liblongcat"=>"Brevets"},
"techreport"=>{"category"=>"14","liblongcat"=>"Rapports de recherche"},
"unpublished"=>{"category"=>"15","liblongcat"=>"Non publiés"}
);

//tableau des bibkey du ou des departements associes : tab_bibkey['postoyan:hal-01108957']='CID', tab_bibkey['weber:hal-01359420']='CID,ISET'
tab_bibkey=array()

// parcours des fichier_collection_bib des departements afin d'associer chaque ref a un ou des departements
pour chaque dept {"CID","ISET","SBS"}
	liste_refs= contenu du fichier de la collection "CRAN-"+dept+".bib" du dept
	pour chaque ref dans liste_refs
		bibkey=bibkey de ref
		si tab_bibkey[bibkey] existe
		alors  tab_bibkey[bibkey]=tab_bibkey[bibkey]+','+dept
		sinon tab_bibkey[bibkey]=dept
	fin pour
// les fichiers des collections des departements ne seront plus utilises par la suite


// parcours du fichier CRAN complet et insertion des champs :
// - EQUIPE={'+tab_bibkey[bibkey]+'} ou par defaut 'EQUIPE={CRAN}'
// - CATEGORY, LIBCAT, LIBLONGCAT, DIFFUSION, ISIWOK
liste_refs = contenu de la collection "CRAN.bib"
pour chaque ref dans liste_refs
	bibkey=bibkey de ref
	si tab_bibkey[bibkey] n'existe pas
	alors tab_bibkey[bibkey]="CRAN"
	bibcat=type publi de ref
	inserer dans la ref "EQUIPE={'+tab_bibkey[bibkey]+'}, CATEGORY ={"+tab_corresp_bibcat[bibcat]["category"]+"},LIBCAT ={"+bibcat+"},LIBLONGCAT ={"+tab_corresp_bibcat[bibcat]["liblongcat"]+"},"DIFFUSION = {INT}, ISIWOK = {NON}"
enregistrer liste_refs dans fichier "cran_avec_dept_cat_diffusion.bib"
*/

/*
// tableau des libelles des categories 
tab_cat=array("1"=>"Articles dans des revues avec comité de lecture",
							 "1int"=>"Articles dans des revues avec comité de lecture a diffusion internationale",
							 "1nat"=>"Articles dans des revues avec comité de lecture a diffusion nationale",
			 "2"=>"Articles dans des revues sans comité de lecture ou a vocation pédagogique",
			 "3"=>"Ouvrages scientifiques",
			 "4"=>"Chapitres d'ouvrages scientifiques",
			 "5"=>"Directions et éditions d'ouvrages",
			 "6"=>"Communications dans des conférences avec comité de lecture et actes",
				 "6int"=>"Communications dans des conférences internationales avec comité de lecture et actes",
				 "6nat"=>"Communications dans des conférences nationales avec comité de lecture et actes",
			 "7"=>"Communications dans des colloques sans comité de lecture ou a diffusion restreinte",
			 "8"=>"Habilitations a diriger des recherches",
			 "9"=>"Theses",
			 "10"=>"Autres publications",
			 "11"=>"Conférences invitées",
			 "12"=>"Actes de conférences",
			 "13"=>"Brevets",
			 "14"=>"Rapports de recherche",
			 "15"=>"Non publiés"
			 );
// tab_cat_ref[dept][categorie)=les refs de chaque (dept,categorie)  
tab_cat_ref=array("CID"=>array(),"ISET"=>array(),"SBS"=>array(),"CRAN"=>array())

// associe a chaque (dept, categorie) les refs correspondantes	 
pour chaque ref du fichier "cran_avec_dept_cat_diffusion.bib" // obtenu par traite_cran_bib_hal puis modifié manuellement par DM (aucune ref. a pour equipe CRAN) 
  bibkey=bibkey de la ref
	numcat=category de la ref //"1", "2",...
	diffusion=diffusion de la ref // "INT" ou "NAT"
	si numcat="1" ou numcat="6" alors numcat=numcat+diffusion //1nat, 1int, 6nat, 6int
	// toutes les refs figureront dans la biblio CRAN 
	tab_cat_ref['CRAN'][numcat]=tab_cat_ref['CRAN'][numcat]+ref
  equipes=equipes de la ref //equipes = departement(s)
	pour chaque equipe/dept de equipes
	  tab_cat_ref[dept][numcat]=tab_cat_ref[dept][numcat]+ref;//ajout de cette ref a la liste de cette catégorie pour ce dept

// genere les fichiers "script_biblio"+dept+".bat" (executables Windows), "biblio"+dept+".tex" et "biblio"+dept+numordre+".bib"
// Un numordre correspond a un numcat. Les numeros d'ordre sont necessairement consecutifs pour la compilation tex
pour chaque dept de ("CID","ISET","SBS","CRAN")//ici on considere que CRAN est un dept pour simplifier l'algorithme
	fichier_script_bat="script_biblio"+dept+".bat"
	ecrire "pdflatex biblio"+dept dans fichier_script_bat
	numordre=0;
	// partie_bibtopic du fichier a creer "biblio"+dept+".tex"
	style="\\bibliographystyle{dm"+lower(dept)+"}"//fichier style preexistant
	partie_bibtopic="\begin{btUnit}";
	pour chaque numcat de tab_cat //une section par categorie
		libcat=tab_cat[numcat]
		si tab_cat_ref[numcat]=""
			"Pas de réf. pour la categorie : "+libcat
		sinon
			numordre=numordre+1 
			// ajout de la commande de compil. bibtex
			ecrire "bibtex biblio"+dept+numordre dans fichier_script_bat
			fichier_dept_categorie_bib="biblio"+dept+numordre+".bib"
			ecrire les refs de tab_cat_ref[dept][numcat] dans fichier_dept_categorie_bib
			enregistrer fichier_dept_categorie_bib
			// ajout des bt sections dans partie_bibtopic du .tex
			partie_bibtopic=partie_bibtopic+"\begin{btSect}{biblio"+dept+numordre+"}"//une section par fichier "biblio"+dept+numordre+".bib"
			partie_bibtopic=partie_bibtopic+"\section*{"+libcat+"}"
			partie_bibtopic=partie_bibtopic+"\btPrintAll"
			partie_bibtopic=partie_bibtopic+"\end{btSect}"
	partie_bibtopic=partie_bibtopic+"\end{btUnit}"
	// creation des fichiers .tex a partir du fichier "modele_categorie_biblio.tex" existant : insertion de partie_bibtopic entre les marqueurs %##PG :debut et %##PG
	fichier_categorie_tex=contenu de "modele_categorie_biblio.tex";
	inserer style+partie_bibtopic dans fichier_categorie_tex entre "%##PG :debut insertion du contenu de la partie bibtopic##" et "%##PG :fin insertion du contenu de la partie bibtopic##");
	enregistrer fichier_categorie_tex sous le nom "biblio"+dept.tex
	// ordres de compil pdflatex
	ecrire  "pdflatex biblio"+dept dans fichier_script_bat
	ecrire  "pdflatex biblio"+dept dans fichier_script_bat
	enregistrer fichier_script_bat

*/

require_once('_const_fonc.php');
ini_set('include_path', ini_get('include_path').';../bibtexParse/');
include 'PARSEENTRIES.php';
 ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_contexte=array('prog'=>'ist','codeuser'=>$codeuser);
// roles personnel
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,false,false);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$peut_etre_admin=array_key_exists('ist',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) || $admin_bd;
$erreur="";
$warning="";
$affiche_succes="";
if($peut_etre_admin==false)
{?> acc&eacute;s restreint !!!
<?php
exit;
}
if($admin_bd)
{/* foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	} */
}
$rep_original=$GLOBALS['path_to_rep_upload']."/ist/original/";
$rep_resultat=$GLOBALS['path_to_rep_upload']."/ist/resultat/";
$rep_echange=$GLOBALS['path_to_rep_upload']."/ist/echange/";
$res['import_hal_ajout_champs']="";
$res['split_dept_cat_diffusion']="";
$res_affiche="";
$tab_gt=array("CID","ISET","SBS");
$annee=isset($_POST["annee"])?$_POST["annee"]:date("Y");	
if(isset($_POST["import_hal_ajout_champs"])) 
{ 
	$date_extr_hal=date("Ymd");
  $res_affiche.="<b>--------- import HAL par collection</b><br>";
	$tab_tampon=array("CRAN");
	foreach($tab_gt as $gt)
	{ $tab_tampon[]="CRAN-".$gt;
	}
	foreach($tab_tampon as $tampon)
	{ $url = 'http://api.archives-ouvertes.fr/search/'.$tampon.'/?wt=bibtex&rows=10000&q=producedDateY_i:'.$annee;
		// Open the Curl session
		$session = curl_init($url);
		curl_setopt ($session, CURLOPT_POST, false);
		// Don't return HTTP headers. Do return the contents of the call
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		
		$bib = curl_exec($session);
		// Verification du nombre d'accolades { et }
		if(substr_count($bib,"{")!==substr_count($bib,"}"))
		{ $res_affiche.="<b><font color='red'>Le nombre total d'accolades ouvrantes et fermantes est diff&eacute;rent pour : </font></b>".$tampon."<br>";
		}

		curl_close($session);
		if (!($fp_bib = @fopen($rep_echange.$tampon."_".$annee.".bib", "w"))) 
		{ die("Impossible d'ouvrir le document ".$tampon."_".$annee.".bib"); 
		}
		else
		{ fwrite($fp_bib,$bib);
		}
		fclose($fp_bib);
		/*$parse = NEW PARSEENTRIES();
		$parse->expandMacro = TRUE;
		$parse->openBib($rep_echange.$tampon."_".$annee.".bib");
		$parse->extractEntries();
		$parse->closeBib();
		list(,,$tab_ref,) = $parse->returnArrays();
		 foreach($tab_ref as $une_ref)
		{ if(substr_count($une_ref,"{")!==substr_count($une_ref,"}"))
			{ $res_affiche.="<b><font color='red'>Le nombre total d'accolades ouvrantes et fermantes est diff&eacute;rent pour : </font></b>".$une_ref."<br>";
			}
		} */
	}
		
	$tab_bibkey=array();
	$nb_ref_gt=0; // nombre de r&eacute;f. d'un gt
	$nb_total_ref=0; // Nombre total de r&eacute;fs
	$ref_multi_gt="";
	foreach($tab_gt as $gt)
	{ $nb_ref_gt=0;
		
		$fichier_gt=$rep_echange.'CRAN-'.$gt."_".$annee.".bib";
		$res_affiche.='Traitement du fichier '.$gt.' : '; 
		if (!($fp_gt = @fopen($fichier_gt, "r"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_gt); 
		}
		$contenu_gt="";
		while ($data = fread($fp_gt, 4096)) 
		{ $contenu_gt=$contenu_gt . $data;
		}
		$pos_deb_gt=strpos($contenu_gt,"{",strpos($contenu_gt,"@"));
		while($pos_deb_gt !== false)
		{ $nb_ref_gt++;
			$pos_fin_gt=strpos($contenu_gt,",",$pos_deb_gt);
			$bibkey=substr($contenu_gt,$pos_deb_gt+1,$pos_fin_gt-$pos_deb_gt-1);
			if(!array_key_exists($bibkey, $tab_bibkey)) 
			{ $tab_bibkey[$bibkey]= $gt;
			}
			else
			{ $tab_bibkey[$bibkey]= $tab_bibkey[$bibkey].",".$gt;
				$ref_multi_gt.="<br>&nbsp;- ".$bibkey." ".$tab_bibkey[$bibkey];
			}
			$pos_deb_gt=strpos($contenu_gt,"@",$pos_fin_gt);
			if($pos_deb_gt!==false)
			{ $pos_deb_gt=strpos($contenu_gt,"{",$pos_deb_gt);
			}
		}
		$res_affiche.=$nb_ref_gt." r&eacute;fs <br>";
	}
	if($ref_multi_gt!='')
	{ $res_affiche.='<span class="bleucalibri10">R&eacute;f. avec plus d&rsquo;un '.$GLOBALS['libcourt_theme_fr']." : </span>".$ref_multi_gt;
	}
	// --------------- fichier complet HAL CRAN
	$fichier_gt=$rep_echange."CRAN"."_".$annee.".bib"; 
	if (!($fp_gt = @fopen($fichier_gt, "r"))) 
	{ die("Impossible d'ouvrir le document ".$fichier_gt); 
	}
	$contenu_hal_cran_bib="";
	while ($data = fread($fp_gt, 4096)) 
	{ $contenu_hal_cran_bib=$contenu_hal_cran_bib . $data;
	}
	$ref_sans_equipe="";
	$nb_total_ref=0;
	$pos_arobase=strpos($contenu_hal_cran_bib,"@");
	$pos_deb_gt=strpos($contenu_hal_cran_bib,"{",$pos_arobase);
	while($pos_deb_gt !== false)
	{ $nb_total_ref++; 
		$pos_fin_gt=strpos($contenu_hal_cran_bib,",",$pos_deb_gt);
		$bibkey=substr($contenu_hal_cran_bib,$pos_deb_gt+1,$pos_fin_gt-$pos_deb_gt-1);
		if(!array_key_exists($bibkey, $tab_bibkey)) 
		{ $tab_bibkey[$bibkey]= "CRAN";
			$ref_sans_equipe.="<br>&nbsp;- ".$bibkey;
		}
		$pos_deb_gt=strpos($contenu_hal_cran_bib,chr(10)."@",$pos_fin_gt);
		if($pos_deb_gt!==false)
		{ $pos_deb_gt=strpos($contenu_hal_cran_bib,"{",$pos_deb_gt);
		}
	}
	$res_affiche.='<br><b>Traitement du fichier complet CRAN :</b> '.$nb_total_ref." r&eacute;fs";
	$res_affiche.="<br><b>--------- Ajout EQUIPE</b>";
	if($ref_sans_equipe!='')
	{ $res_affiche.='<br><span class="bleucalibri10">R&eacute;f. sans &eacute;quipe ( => EQUIPE = {CRAN})</span>'.$ref_sans_equipe;
	}
	// -------------------- parcours du fichier cran, insertion de l'equipe et cr&eacute;ation d'un nouveau fichier
	$pos_arobase=strpos($contenu_hal_cran_bib,"@");
	$pos_deb_tout_cran=strpos($contenu_hal_cran_bib,"{",$pos_arobase);
	while($pos_deb_tout_cran !== false)
	{ $pos_fin_tout_cran=strpos($contenu_hal_cran_bib,",",$pos_deb_tout_cran);
		$bibkey=substr($contenu_hal_cran_bib,$pos_deb_tout_cran+1,$pos_fin_tout_cran-$pos_deb_tout_cran-1);
		if(!array_key_exists($bibkey, $tab_bibkey)) 
		{ $res_affiche.="Attention : ". $bibkey . " inexistante dans extraction HAL<BR>";
		}
		else
		{ $contenu_hal_cran_bib=substr($contenu_hal_cran_bib,0,$pos_fin_tout_cran+1)." EQUIPE={".$tab_bibkey[$bibkey]."},\n".substr($contenu_hal_cran_bib,$pos_fin_tout_cran+2);
		}
		$pos_deb_tout_cran=strpos($contenu_hal_cran_bib,chr(10)."@",$pos_fin_tout_cran);
		if($pos_deb_tout_cran!==false)
		{ $pos_deb_tout_cran=strpos($contenu_hal_cran_bib,"{",$pos_deb_tout_cran);
		}
	}
	
	$res_affiche.="<br><b>--------- Ajout CATEGORY, LIBCAT, LIBLONGCAT, DIFFUSION, ISIWOK dans refs. bib</b>";
	// libell&eacute;s des cat&eacute;gories a rajouter : l'index &eacute;tablit la correspondance corresp_cat_HAL->cat 
	$tab_cat=array("1"=>"Articles dans des revues avec comit&eacute; de lecture",
	"2"=>"Articles dans des revues sans comit&eacute; de lecture","3"=>"Ouvrages scientifiques","4"=>"Chapitres d'ouvrages scientifiques",
	"5"=>"Directions d'ouvrages","6"=>"Communications avec actes","7"=>"Communications sans actes","8"=>"HDR",
	"9"=>"Th&egrave;ses","10"=>"Autres publications","11"=>"Conf&eacute;rences invit&eacute;es","12"=>"Actes de conf&eacute;rences","13"=>"Brevets",
	"14"=>"Rapports de recherche","15"=>"Non publi&eacute;s","16"=>"","17"=>"","18"=>"","19"=>"","20"=>"Rapports interne (HAL : pas d'&eacute;quivalent)","21"=>"Rapport de stage (HAL : pas d'&eacute;quivalent)");
	// Libelles des cat&eacute;gories HAL
	$tab_corresp_cat_HAL=array("1"=>"article","2"=>"","3"=>"book","4"=>"incollection","5"=>"OE","6"=>"inproceedings","7"=>"CD","8"=>"HD","9"=>"phdthesis","10"=>"misc","11"=>"CONF_INV",
	"12"=>"proceedings",13=>"patent","14"=>"techreport",15=>"unpublished");
	
	$res_affiche.='<table border="1"><tr><td align="center"><b>cl&eacute;</b></td><td align="center"><b>cat&eacute;gorie HAL</b></td><td align="center"><b>libell&eacute; cat&eacute;gorie CRAN</b></td></tr>';
	foreach($tab_corresp_cat_HAL as $key=>$libcat)
	{ $res_affiche.='<tr><td>'.$key.'</td><td>'.$libcat.'</td><td>'.$tab_cat[$key].'</td></tr>';
	}
	$res_affiche.='</table>';
	
	$contenu_hal_cran_bib_new="";// nouveau contenu du nouveau fichier 
	$nb_refs_hal=0; //comptage du nombre total de ref HAL
	
	// 1ere ref HAL  du fichier bib CRAN
	$pos_deb_ref=strpos($contenu_hal_cran_bib,"@");
	
	$nonfinfichier=true;
	while($nonfinfichier) 
	{ $nb_refs_hal++;//compteur nb refs
		$pos_deb_cat=$pos_deb_ref;
		$pos_fin_cat=strpos($contenu_hal_cran_bib,'{',$pos_deb_cat);
		$libcat=substr($contenu_hal_cran_bib,$pos_deb_cat+1,$pos_fin_cat-$pos_deb_cat-1);
		$pos_deb_bibkey=$pos_fin_cat;
		$pos_fin_bibkey=strpos($contenu_hal_cran_bib,",",$pos_deb_bibkey);
		$bibkey=substr($contenu_hal_cran_bib,$pos_deb_bibkey+1,$pos_fin_bibkey-$pos_deb_bibkey-1);
		$category=array_search (utf8_decode($libcat),$tab_corresp_cat_HAL);
		if($category===false)
		{ $res_affiche.="<br>"." <B> CATEGORIE INEXISTANTE DANS LA LISTE : ".$libcat ." </b>(".$bibkey.")<br>";
		}
		else
		{ $liblongcat=$tab_cat[$category];
			$contenu_hal_cran_bib=substr($contenu_hal_cran_bib,0,$pos_fin_bibkey+1).
																	 " CATEGORY={".$category."},LIBCAT={".$libcat."},LIBLONGCAT={".$liblongcat."},"."DIFFUSION={INT},ISIWOK={NON},".
																		substr($contenu_hal_cran_bib,$pos_fin_bibkey+1);
			//echo "BIBKEY : ".$bibkey." CATEGORY : ".$category. " LIBCAT : ".$libcat. " LIBLONGCAT : ".$liblongcat."<br>";
		} 
		$pos_fin_ref=strpos($contenu_hal_cran_bib,chr(10)."@",$pos_deb_ref+1);//la prochaine ref commence par @ avec un chr(10) avant
		// Verification du nombre d'accolades { et }
		$ref=substr($contenu_hal_cran_bib,$pos_deb_ref,$pos_fin_ref-$pos_deb_ref);
	
		if($pos_fin_ref!==false)//prochaine ref
		{ $pos_deb_ref=$pos_fin_ref+1;
		}
		else
		{ $nonfinfichier=false;
		}
	}
	// ---------------- nouveau fichier complet ecrit dans rep resultat
	$fichier_hal_cran_bib_new="cran_avec_dept_cat_diffusion.bib";//nouvelle version avec categories et diffusion (initialis&eacute; a INT
	if (!($fp_hal_cran_bib_new = @fopen($rep_echange.$fichier_hal_cran_bib_new, "w"))) 
	{ die("Impossible d'ouvrir le document "); 
	}
	else
	{ fwrite($fp_hal_cran_bib_new,$contenu_hal_cran_bib);
	}
	fclose($fp_hal_cran_bib_new);
	$res['import_hal_ajout_champs']='T&eacute;l&eacute;charger le fichier r&eacute;sultat : <img src="images/b_download.png" width="16" height="16"> <a href="download.php?fichierist='.$fichier_hal_cran_bib_new.'">'.$fichier_hal_cran_bib_new.'</a><br>';
}
else if(isset($_POST["split_dept_cat_diffusion"]))
{ $tab_cat=array("1"=>"Articles dans des revues avec comité de lecture",
							 "1int"=>"Articles dans des revues avec comité de lecture a diffusion internationale",
							 "1nat"=>"Articles dans des revues avec comité de lecture a diffusion nationale",
			 "2"=>"Articles dans des revues sans comité de lecture ou a vocation pédagogique",
			 "3"=>"Ouvrages scientifiques",
			 "4"=>"Chapitres d'ouvrages scientifiques",
			 "5"=>"Directions et éditions d'ouvrages",
			 "6"=>"Communications dans des conférences avec comité de lecture et actes",
				 "6int"=>"Communications dans des conférences internationales avec comité de lecture et actes",
				 "6nat"=>"Communications dans des conférences nationales avec comité de lecture et actes",
			 "7"=>"Communications dans des colloques sans comité de lecture ou a diffusion restreinte",
			 "8"=>"Habilitations a diriger des recherches",
			 "9"=>"Theses",
			 "10"=>"Autres publications",
			 "11"=>"Conférences invitées",
			 "12"=>"Actes de conférences",
			 "13"=>"Brevets",
			 "14"=>"Rapports de recherche",
			 "15"=>"Non publiés"//,"16"=>"","17"=>"","18"=>"","19"=>"","20"=>"Rapport interne (HAL pas d'équivalent)","21"=>"Rapport de stage (HAL pas d'équivalent)"
			 );
	//toutes les réfs par catégorie
	$tab_cat_ref=array("1"=>"","1int"=>"","1nat"=>"","2"=>"","3"=>"","4"=>"","5"=>"","6"=>"","6int"=>"","6nat"=>"","7"=>"","8"=>"","9"=>"","10"=>"",
	"11"=>"","12"=>"","13"=>"","14"=>"","15"=>"","16"=>"","17"=>"","18"=>"","19"=>"","20"=>"","21"=>"");
	$tab_cat_nb_ref=array();// compteur de ref par categorie
	$tab_gt=array("CID","ISET","SBS");
	$tab_gt_cat_ref=array("CID"=>$tab_cat_ref,"ISET"=>$tab_cat_ref,"SBS"=>$tab_cat_ref,"CRAN"=>$tab_cat_ref);// ref par gt et categorie
	$tab_gt_cat_nb_ref=array("CID"=>$tab_cat_nb_ref,"ISET"=>$tab_cat_nb_ref,"SBS"=>$tab_cat_nb_ref,"CRAN"=>$tab_cat_nb_ref);
	foreach($tab_gt_cat_nb_ref as $gt=>$v)
	{ foreach($tab_cat as $cat=>$v1)
		{ $tab_gt_cat_nb_ref[$gt][$cat]=0;
		}
	}
	$tab_fichier_a_zipper=array();
	$erreur="";
	
	$nb_refs=0; //comptage du nombre total de refs
	$ref_traitee=true; //traitement ou non de ref dans le résultat
	$nb_refs_non_traitees=0;
	$fichier_tout_cran="cran_avec_dept_cat_diffusion.bib";
	$nomfichier="cran_avec_dept_cat_diffusion.bib";	
	list($key,$nomfichier)=each ($_FILES["pj"]["name"]);
	if($nomfichier!='')
	{ clearstatcache();
		$tab_res_upload=upload_file($_FILES,$rep_echange,"pj",$key,$fichier_tout_cran);
		$erreur.=$tab_res_upload['erreur'];
		$affiche_succes=($erreur=='');
		$msg_succes="Enregistrement effectu&eacute;.";
	}
	if($erreur!='')
	{ $res_affiche.='Erreur : '.$erreur;
	}
	else
	{ // -------------------- fichier traite
		$contenu_hal_cran_bib="";//contenu du fichier original (le dernier modifi&eacute; manuellement)
		if (!($fp_tout_cran = @fopen($rep_echange.$fichier_tout_cran, "r"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_tout_cran); 
		}
		while ($data = fread($fp_tout_cran, 4096)) 
		{ $contenu_hal_cran_bib=$contenu_hal_cran_bib . $data;
		}
		fclose($fp_tout_cran);
		if(substr_count($contenu_hal_cran_bib,"{")!==substr_count($contenu_hal_cran_bib,"}"))
		{ $res_affiche.="<b><font color='red'>Le nombre total d'accolades ouvrantes et fermantes est diff&eacute;rent</font>";
		}

		// ---------------- nouveau fichier complet ecrit dans rep resultat
		
		// classement par categories
		// 1ere ref de la partie serveur de publis
		$pos_deb_ref=strpos($contenu_hal_cran_bib,"@");
		//$contenu_hal_cran_bib_new=substr($contenu_hal_cran_bib,0,$pos_deb_ref);// partie du fichier avant la 1ere ref serveur publis
		
		$nonfinfichier=(($pos_deb_ref!==false)? true : false);
		while($nonfinfichier) // pour chaque ref
		{ $ref_traitee=true;//par d&eacute;faut la r&eacute;f. est trait&eacute;e
			$anomalie="";
			// ---- extraction de la ref : chr(10)@ = d&eacute;but d'une nouvelle ref
			$pos_fin_ref=strpos($contenu_hal_cran_bib,chr(10)."@",$pos_deb_ref+1);
			if($pos_fin_ref!==false)
			{ $ref=substr($contenu_hal_cran_bib,$pos_deb_ref,$pos_fin_ref-$pos_deb_ref);
				$pos_deb_ref=$pos_fin_ref;
			}
			else
			{ $ref=substr($contenu_hal_cran_bib,$pos_deb_ref);
				$nonfinfichier=false;
			}
			$pos_deb_bibkey=strpos($ref,"{");
			$pos_fin_bibkey=strpos($ref,",",$pos_deb_bibkey);
			$bibkey=substr($ref,$pos_deb_bibkey+1,$pos_fin_bibkey-$pos_deb_bibkey-1);
			$pos_deb_categorie=strpos(strtolower($ref),"category={");
			$pos_deb_categorie=strpos($ref,"{",$pos_deb_categorie);
			$pos_fin_categorie=strpos($ref,"}",$pos_deb_categorie);
			$categorie=substr($ref,$pos_deb_categorie+1,$pos_fin_categorie-$pos_deb_categorie-1);
			if($categorie=="11")//v&eacute;rif. que plus de r&eacute;f en cat&eacute;gorie 11 (CONF_INV) trait&eacute;es en Conf. avec actes
			{ $categorie="6";
				$res_affiche.="<font color='green'>".$bibkey." : CONF_INV trait&eacute;e en CA=Conf. avec actes pour la sortie pdf</font><br>";
			}
			if($categorie=="20" || $categorie=="21")// v&eacute;rif. que plus de cat&eacute;gorie  20, 21
			{ $anomalie.=" champ de cat&eacute;gorie ".$categorie;
				$ref_traitee=false;
				$nb_refs_non_traitees++;
				//echo "<font color='green'>".$bibkey." : pas de prise en compte de r&eacute;f de cette cat&eacute;gorie : ".$categorie." (".$tab_cat[$categorie].")</font><br>";
			}
			if($categorie=="1" || $categorie=="6" )//les articles sont distingu&eacute;s en int ou nat
			{ $pos_deb_diffusion=strpos(strtolower($ref),"diffusion=");
				if($pos_deb_diffusion!==false)//concatene champ DIFFUSION = int ou nat au code categorie trouve
				{ $pos_deb_diffusion=strpos($ref,"{",$pos_deb_diffusion);
					$pos_fin_diffusion=strpos($ref,"}",$pos_deb_diffusion);
					$categorie=$categorie.strtolower(substr($ref,$pos_deb_diffusion+1,$pos_fin_diffusion-$pos_deb_diffusion-1));
				}
			}
			// --------- v&eacute;rification existence du champ EQUIPE et qu'il est bien l'un des GT du CRAN
			// --------- si dans un GT du CRAN, ajout de la ref dans la categorie pour cette equipe (=GT) sinon dans CRAN/AUTRE
			$pos_deb_equipe=strpos(strtolower($ref),"equipe=");// Verifie que le champ EQUIPE existe
			if($pos_deb_equipe===false)// si le champ EQUIPE n'existe pas : WARNING
			{ $anomalie.=" champ EQUIPE inexistant";
			}
			else
			{ $pos_deb_equipe=strpos($ref,"{",$pos_deb_equipe);
				$pos_fin_equipe=strpos($ref,"}", $pos_deb_equipe);
				$equipe=substr($ref,$pos_deb_equipe+1,$pos_fin_equipe-$pos_deb_equipe-1);
				if(!in_array($equipe, $tab_gt))
				{ $anomalie.="Champ EQUIPE = ".$equipe;
				}
			} 
			// liste des anomalies de la ref
			if($anomalie!="")
			{ $res_affiche.="<b>".$bibkey." : ".$anomalie."</b><br>".$ref."<br>";
			}
		
			// Fin de traitement de cette r&eacute;f. : ajout de la ref modifi&eacute;e a cette cat&eacute;gorie pour cette &eacute;quipe/cat&eacute;gorie et cette cat&eacute;gorie 
			$nb_refs++;
			if($ref_traitee)
			{ // ajout de la ref. dans la liste des ref de cette categorie
				$tab_cat_ref[$categorie].=$ref;
			// ajout de la ref dans le fichier bib final contenant les nouveaux libelles de categorie
				//comptage du nombre de refs par categorie
				if(array_key_exists($categorie, $tab_cat_nb_ref)) 
				{ $tab_cat_nb_ref[$categorie]++;
				}
				else
				{ $tab_cat_nb_ref[$categorie]=1;
				}
				foreach(split(",",$equipe) as $une_equipe)//pour chaque &eacute;quipe (gt) de cette ref 
				{ $tab_gt_cat_ref[$une_equipe][$categorie].=$ref;//ajout de cette ref a la liste de cette cat&eacute;gorie pour cette &eacute;quipe
					if(array_key_exists($categorie, $tab_cat_nb_ref)) 
					{ $tab_gt_cat_nb_ref[$une_equipe][$categorie]++;
					}
					else
					{ $tab_gt_cat_nb_ref[$une_equipe][$categorie]=1;
					}
				}
			}
			// --------- ajout de la ref dans $contenu_hal_cran_bib_new
			//$contenu_hal_cran_bib_new.=$ref; 
		}
		// ----- Affichage rapport apr&egrave;s traitement : nombre de refs par categorie
		$res_affiche.="<b>Nombre total de refs : ".$nb_refs."</b><br><br>";
		$res_affiche.="<b>Nombre de r&eacute;fs non trait&eacute;es (".$tab_cat["12"].") : ".$nb_refs_non_traitees."</b><br>";
		$res_affiche.="<b>Nombre de r&eacute;fs dans le fichier pdf (a v&eacute;rifier) : ".($nb_refs-$nb_refs_non_traitees)."</b><br>";
		$res_affiche.='<table border="1">';
		$res_affiche.="<tr><td>Code cat&eacute;gorie</td><td>Cat&eacute;gorie</td><td>nombre de refs</td>";
		reset($tab_gt);
		foreach($tab_gt as $gt)// liste des gt en ligne
		{ $res_affiche.="<td>".$gt."</td>";
		}
		$res_affiche.="</tr>";
		reset($tab_cat_nb_ref);
		ksort ( $tab_cat_nb_ref );
		reset($tab_cat_nb_ref);
		foreach($tab_cat_nb_ref as $categorie=>$nb)// nombre de r&eacute;fs par cat&eacute;gorie par gt
		{ $res_affiche.="<tr><td>".$categorie."<td>".$tab_cat[$categorie]."</td><td>".$nb."</td>";
			reset($tab_gt);
			foreach($tab_gt as $gt)// nombre de r&eacute;fs par cat&eacute;gorie par gt
			{ $res_affiche.="<td>".($tab_gt_cat_nb_ref[$gt][$categorie]==0?"":$tab_gt_cat_nb_ref[$gt][$categorie])."</td>";
			}
			$res_affiche.="</tr>";
		}
		$res_affiche.="</table>";
		/* !--------------------------------------------------------------------------------------------------------------------------!
			 !                                            TRAITEMENT DE LA PARTIE TEX                                                   !
			 !--------------------------------------------------------------------------------------------------------------------------!
		*/
		//$res_affiche.="CREATION FICHIERS TEX DES REFS PAR CATEGORIE ET EQUIPE/CATEGORIE<br><br>";
		
		reset($tab_cat);
		$i=0;//numero d'ordre du fichier pour la cat&eacute;gorie en cours
		$fichier_script_bat="_script_bibliocran.bat";
		if(!($fp_script_bat = @fopen($rep_resultat.$fichier_script_bat, "w"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_script_bat); 
		}
		fwrite($fp_script_bat, "pdflatex biblioCRAN".chr(13).chr(10));
		$partie_bibtopic="\begin{btUnit}".chr(13).chr(10);
		foreach($tab_cat as $categorie=>$libcat)
		{ if($tab_cat_ref[$categorie]!=="") //avoir les memes noms de fichiers num&eacute;rot&eacute;s de 1 a n que ce que produit bibtopic.sty=num&eacute;rotation auto
			{ $i++;//numero d'ordre du fichier pour la cat&eacute;gorie en cours
				$fichier_categorie_i_bib="biblioCRAN".$i.".bib";
				if (!($fp_categorie_i_bib = @fopen($rep_resultat.$fichier_categorie_i_bib, "w"))) 
				{ die("Impossible d'ouvrir le document ".$fichier_categorie_i_bib); 
				}
				fwrite($fp_categorie_i_bib, $tab_cat_ref[$categorie]);
				//$res_affiche.="<br><b>Ecriture " .$categorie." ". $libcat." <a href='".str_replace('\\','/',$fichier_categorie_i_bib)."'>".str_replace('\\','/',$fichier_categorie_i_bib)."</a></b><br>";
				fclose($fp_categorie_i_bib);
				$tab_fichier_a_zipper[]=$fichier_categorie_i_bib;
				// ajout de la commande de compil. bibtex
				fwrite($fp_script_bat, "bibtex "."biblioCRAN".$i.chr(13).chr(10));
				// ajout des bt sections dans parte_bibtopic
				$partie_bibtopic.="\begin{btSect}{biblioCRAN".$i."}".chr(13).chr(10);
				$partie_bibtopic.="\section*{".$libcat."}".chr(13).chr(10);
				$partie_bibtopic.="\btPrintAll".chr(13).chr(10);
				$partie_bibtopic.="\end{btSect}".chr(13).chr(10);
			}    
		}
		$partie_bibtopic.="\end{btUnit}".chr(13).chr(10);
		$fichier_categorie_tex="modele_categorie_biblio.tex";//ouverture de l'original en lecture et ecriture du r&eacute;sultat dans resultat/CRAN.tex
		if(!($fp_categorie_tex = @fopen($rep_original.$fichier_categorie_tex, "r"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_categorie_tex);
		}
		$contenu_categorie_tex="";
		while ($data = fread($fp_categorie_tex, 4096)) 
		{ $contenu_categorie_tex=$contenu_categorie_tex . $data;
		}
		$pos_deb_categorie=strpos($contenu_categorie_tex,"%##PG :debut insertion du contenu de la partie bibtopic##");
		$pos_fin_categorie=strpos($contenu_categorie_tex,"%##PG :fin insertion du contenu de la partie bibtopic##");
		$contenu_categorie_tex_new=substr($contenu_categorie_tex,0,$pos_deb_categorie);
		$contenu_categorie_tex_new.="%##PG :debut insertion du contenu de la partie bibtopic##".chr(13).chr(10);
		$contenu_categorie_tex_new.=$partie_bibtopic;
		$contenu_categorie_tex_new.=substr($contenu_categorie_tex,$pos_fin_categorie);
		
		$fichier_categorie_tex_new="biblioCRAN.tex";//ouverture du nouveau en ecriture dans resultat/
		if(!($fp_categorie_tex_new = @fopen($rep_resultat.$fichier_categorie_tex_new, "w"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_categorie_tex_new);
		}
		fwrite($fp_categorie_tex_new,$contenu_categorie_tex_new);
		// ordres de compil pdflatex
		fwrite($fp_script_bat, "pdflatex biblioCRAN".chr(13).chr(10));
		fwrite($fp_script_bat, "pdflatex biblioCRAN".chr(13).chr(10));
		fclose($fp_script_bat);
		$tab_fichier_a_zipper[]=$fichier_script_bat;
		fclose($fp_categorie_tex);
		fclose($fp_categorie_tex_new);
		$tab_fichier_a_zipper[]=$fichier_categorie_tex_new;
		
		// Pour chaque gt : creation des fichiers pour les cat&eacute;gories
		$fichier_gt_tex="modele_categorie_biblio.tex";//ouverture de l'original en lecture et ecriture du r&eacute;sultat dans resultat/$gt.tex
		if(!($fp_gt_tex = @fopen($rep_original.$fichier_gt_tex, "r"))) 
		{ die("Impossible d'ouvrir le document ".$fichier_gt_tex);
		}
		$contenu_gt_tex="";
		while ($data = fread($fp_gt_tex, 4096)) 
		{ $contenu_gt_tex=$contenu_gt_tex . $data;
		}
		foreach($tab_gt as $gt)
		{ reset($tab_cat);
			$i=0;//numero d'ordre du fichier de ce GT pour la cat&eacute;gorie en cours
			$tab_gt_ref=$tab_gt_cat_ref[$gt];//tableau associatif des refs class&eacute;es par cat&eacute;gories de $gt(=&eacute;quipe)
			$fichier_script_gt_bat="_script_biblio".$gt.".bat";
			if(!($fp_script_gt_bat = @fopen($rep_resultat.$fichier_script_gt_bat, "w"))) 
			{ die("Impossible d'ouvrir le document script ".$fichier_script_gt_bat); 
			}
			fwrite($fp_script_gt_bat, "pdflatex biblio".$gt.chr(13).chr(10));
			$partie_bibtopic="\begin{btUnit}".chr(13).chr(10);
			// Ecriture d'un fichier bib par gt et par cat&eacute;gorie
			foreach($tab_cat as $categorie=>$libcat)
			{ if($tab_gt_ref[$categorie]!=="") //avoir les memes noms de fichiers num&eacute;rot&eacute;s de 1 a n que ce que produit bibtopic.sty=num&eacute;rotation auto
				{ $i++;
					$fichier_gt_categorie_i_bib="biblio".$gt.$i.".bib";
					if (!($fp_gt_categorie_i_bib = @fopen($rep_resultat.$fichier_gt_categorie_i_bib, "w"))) 
					{ die("Impossible d'ouvrir le document ".$fichier_gt_categorie_i_bib); 
					}
					//$res_affiche.="<br><b>Ecriture categorie " . $categorie.' '.$libcat." de ".count($tab_gt_cat_nb_ref[$gt][$categorie])." refs  <a href='".str_replace('\\','/',$fichier_gt_categorie_i_bib)."'>".str_replace('\\','/',$fichier_gt_categorie_i_bib)."</a></b><br>";
					fwrite($fp_gt_categorie_i_bib, $tab_gt_ref[$categorie]);
					// fermeture du i&egrave;me fichier de ce GT
					fclose($fp_gt_categorie_i_bib);
					$tab_fichier_a_zipper[]=$fichier_gt_categorie_i_bib;
					// ajout de la commande de compil. bibtex
					fwrite($fp_script_gt_bat, "bibtex biblio".$gt.$i.chr(13).chr(10));
					// ajout des bt sections dans parte_bibtopic
					$partie_bibtopic.="\begin{btSect}{biblio".$gt.$i."}".chr(13).chr(10);
					$partie_bibtopic.="\section*{".$libcat."}".chr(13).chr(10);
					$partie_bibtopic.="\btPrintAll".chr(13).chr(10);
					$partie_bibtopic.="\end{btSect}".chr(13).chr(10);
				}    
			}
			$partie_bibtopic.="\end{btUnit}".chr(13).chr(10);
			$pos_deb_gt=strpos($contenu_gt_tex,"%##PG :debut insertion du contenu de la partie bibtopic##");
			$pos_fin_gt=strpos($contenu_gt_tex,"%##PG :fin insertion du contenu de la partie bibtopic##");
			$contenu_gt_tex_new=substr($contenu_gt_tex,0,$pos_deb_gt);
			$contenu_gt_tex_new.="%##PG :debut insertion du contenu de la partie bibtopic##".chr(13).chr(10);
			$contenu_gt_tex_new.=("\\bibliographystyle{dm".strtolower($gt)."}".chr(13).chr(10));
			//$contenu_gt_tex_new.=("\\bibliographystyle{dm".strtolower($gt)."}".chr(13).chr(10));
			$contenu_gt_tex_new.=$partie_bibtopic;
			$contenu_gt_tex_new.=substr($contenu_gt_tex,$pos_fin_gt);
		
			$fichier_gt_tex_new="biblio".$gt.".tex";//ouverture du nouveau en ecriture dans resultat/categorie.tex
			if(!($fp_gt_tex_new = @fopen($rep_resultat.$fichier_gt_tex_new, "w"))) 
			{ die("Impossible d'ouvrir le document ".$rep_resultat.$fichier_gt_tex_new);
			}
			fwrite($fp_gt_tex_new,$contenu_gt_tex_new);
			// 2 ordres de compil pdflatex apr&egrave;s bibtex
			fwrite($fp_script_gt_bat, "pdflatex biblio".$gt.chr(13).chr(10));
			fwrite($fp_script_gt_bat, "pdflatex biblio".$gt.chr(13).chr(10));
			// fermeture de tous les fichiers
			fclose($fp_script_gt_bat);
			$tab_fichier_a_zipper[]=$fichier_script_gt_bat;
			fclose($fp_gt_tex_new);
			$tab_fichier_a_zipper[]=$fichier_gt_tex_new;
			// copie des fichiers de style
			$nomfichier="dm".strtolower($gt).".bst";
			copy($rep_original.$nomfichier, $rep_resultat.$nomfichier);
			$tab_fichier_a_zipper[]=$nomfichier;
		}
		$nomfichier_zip='biblioCRAN'.$annee.'.zip';
		if (file_exists($rep_echange.$nomfichier_zip)) 
		{ unlink($rep_echange.$nomfichier_zip);
		}
		$zip = new ZipArchive;
		if ($zip->open($rep_echange.$nomfichier_zip,ZipArchive::CREATE) === TRUE) 
		{ foreach($tab_fichier_a_zipper as $nomfichier)
			{ $zip->addFile($rep_resultat.$nomfichier,'biblio/'.$nomfichier);
			}
			$zip->close();
			$res['split_dept_cat_diffusion']='Fichier zipp&eacute;  : <img src="images/b_download.png" width="16" height="16"> <a href="download.php?fichierist='.$nomfichier_zip.'">'.$nomfichier_zip.'</a><br>';

		} 
		else 
		{ $res_affiche.='Echec de cr&eacute;ation du fichier zip';
		}
	}
//fclose($fp_tout_cran_new);
}
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Menu principal</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'\\n':'').str_replace(array("<br>","<BR>"),"\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>
<form name="ist" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="MM_update" value="ist">
<table>
	<tr>
  	<td>
      <table border="0" align="center" cellpadding="0" cellspacing="1">
        <?php echo entete_page(array('image'=>'images/b_document.png','titrepage'=>'IST','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                      'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,'message_resultat_affiche'=>'')) ?>
        <tr>
          <td>&nbsp;
          </td>
        </tr>
        <tr>
          <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
              <tr>
                <td>Production scientifique <input type="text" class="noircalibri10" name = "annee" value="<?php echo $annee ?>">
                </td>
              </tr>
              <tr>
                <td><b>Etape 1 :</b> import de la bibliographie du serveur HAL et ajout des champs equipe, category, libcat, liblongcat, diffusion, isiwork&nbsp;
                  <input type="submit" class="noircalibri10" name="import_hal_ajout_champs" id="import_hal_ajout_champs" value="Ex&eacute;cuter">&nbsp;&nbsp;<?php echo $res['import_hal_ajout_champs'] ?>
                </td>
              </tr>
              <tr>
              	<td align="center" height="10"><img src="images/trait_gris.gif" width="600" height="1">
                </td>
              </tr>
              <tr>
                <td><b>Etape 2 :</b> cr&eacute;ation d&rsquo;une archive zip des fichiers (.bat, .bib, .tex, .bst) par &eacute;quipe, par cat&eacute;gorie &agrave; partir du fichier bib de l&rsquo;&eacute;tape 1 corrig&eacute; manuellement<br>
                  Fichier &agrave; traiter <input type="file" class="noircalibri10" name="pj['fichierbib']" id="pj['fichierbib']"/>
                  <input type="submit" class="noircalibri10" name="split_dept_cat_diffusion" id="split_dept_cat_diffusion" value="Soumettre">&nbsp;&nbsp;<?php echo $res['split_dept_cat_diffusion'] ?>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
<?php if($res_affiche!='')
{ ?>
	<tr>
    <td align="center" class="noirgrascalibri11">D&eacute;tails du d&eacute;roulement de l'&eacute;x&eacute;cution
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" align="center" cellpadding="0" cellspacing="1">
        <tr>
          <td><?php echo $res_affiche;?>
          </td>
        </tr>
      </table>
  	</td>
	</tr>
<?php 
}?>
</table>
</form>            
</body>
</html>

