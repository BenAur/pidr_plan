<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$type='tex';
if(!array_key_exists('admingestfin',$tab_roleuser) && !array_key_exists('du',$tab_roleuser)  && !array_key_exists('sif',$tab_roleuser) /*20170420 && !array_key_exists('gestul',$tab_roleuser) && !array_key_exists('gestcnrs',$tab_roleuser) */)
{ echo "Acces restreint";
	exit;
}
$car_debut_ligne='\\hline ';
$car_fin_ligne="\\\\"."\n";
$car_cellule=" & ";
header('Content-type: text/plain');
{ echo "\documentclass[a4paper,oneside, landscape,10pt]{article}\n
\usepackage[top=0pt, bottom=0pt, left=0pt , right=0pt]{geometry}\n
\usepackage{mathtools}
\usepackage[table]{xcolor}\n
\usepackage{longtable}\n
\begin{document}\n
\\rowcolors{2}{lightgray}{white}\n
\begin{longtable}{|p{1.5cm}|p{1cm}|p{3cm}|p{4cm}|p{5cm}|p{4cm}|p{5cm}|p{3cm}|}\n";
}


echo 	$car_debut_ligne."Date effet"." & "."Dur\\'ee\\newline mois".$car_cellule."Type".$car_cellule."Financement".$car_cellule."Responsable scientifique".$car_cellule."Partenaires".$car_cellule."Intitul\\'e".$car_cellule."Montant".$car_fin_ligne;
// ----------------------- liste des contrats
$query_contrat =	"SELECT distinct contrat.codecontrat,datedeb_contrat, duree_mois, montant_ht as montant,ref_contrat, libcourttype  as libtype,".
									" libcourtorgfinanceur as liborgfinanceur,".
									" respscientifique.prenom as prenomrespscientifique,respscientifique.nom as nomrespscientifique".
									" FROM contrat, cont_type, cont_orgfinanceur, individu as respscientifique".
									" WHERE contrat.codetype=cont_type.codetype".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and (contrat.codetheme='05' or contrat.codetheme='08')".
									" and contrat.coderespscientifique=respscientifique.codeindividu".
 									" and ".periodeencours('datedeb_contrat','datefin_contrat').
									" ORDER BY datedeb_contrat asc";
$rs_contrat = mysql_query($query_contrat) or die(mysql_error());
while($row_rs_contrat=mysql_fetch_assoc($rs_contrat))
{	$codecontrat=$row_rs_contrat['codecontrat'];
	$rs=mysql_query("SELECT libcourtpart as libpart".
									" from contratpart,cont_part". 
									" where contratpart.codepart=cont_part.codepart and contratpart.codepart=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	$listepartenaire='';
	while($row_rs=mysql_fetch_assoc($rs))
	{ $listepartenaire.=$row_rs['libpart'].'\\newline ';
	}
	echo 	$car_debut_ligne.txt2type(aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/'),$type).$car_cellule.txt2type($row_rs_contrat['duree_mois'],$type).$car_cellule.txt2type($row_rs_contrat['libtype'],$type).
				$car_cellule.txt2type($row_rs_contrat['liborgfinanceur'],$type).$car_cellule.txt2type($row_rs_contrat['nomrespscientifique'],$type).' '.txt2type($row_rs_contrat['prenomrespscientifique'],$type).
				$car_cellule.txt2type($listepartenaire,$type).$car_cellule.txt2type($row_rs_contrat['ref_contrat'],$type).$car_cellule.txt2type(str_replace('.',',',$row_rs_contrat['montant']),$type);
	echo $car_fin_ligne;
}
echo "\\hline\end{longtable}";
echo "\end{document}";

if(isset($rs)) mysql_free_result($rs);
if(isset($rs_contrat))mysql_free_result($rs_contrat);
?>
