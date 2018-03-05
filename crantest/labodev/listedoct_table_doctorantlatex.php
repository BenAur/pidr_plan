<?php include_once('_const_fonc.php');?>
<?php 						 
$type_fichier='tex';
$car_debut_ligne_entete='\\hline ';
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
";
}
$query_rs_individu= "select nom,prenom,date,sujet,financement,dir,ed from _doctorantlatex";

$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$pair=true;
$ed="-1";
$firsttab=true;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ if($row_rs_individu["ed"]!=$ed)
	{ if(!$firsttab)
		{ echo "\\hline\end{longtable}";
		}
		$firsttab=false;
		echo "\begin{longtable}{|p{2.5cm}|p{2.5cm}|p{2.5cm}|p{7cm}|p{7cm}|p{4cm}|}\n";
		echo $car_debut_ligne_entete."Nom".$car_cellule."Pr\\'enom".$car_cellule."D\\'ebut de th\`ese".$car_cellule."Sujet".$car_cellule."Financement".$car_cellule."Directeur \\& co-directeur".$car_fin_ligne;
		$ed=$row_rs_individu["ed"];
	}
	echo txt2type($row_rs_individu["nom"],$type_fichier).$car_cellule.txt2type($row_rs_individu["prenom"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["date"],$type_fichier).$car_cellule.txt2type($row_rs_individu["sujet"],$type_fichier).$car_cellule.
				txt2type($row_rs_individu["financement"],$type_fichier).$car_cellule.txt2type($row_rs_individu["dir"],$type_fichier);
	echo $car_fin_ligne;
}
echo "\\hline\end{longtable}";
echo "\end{document}";
?>
<?php 
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
?>




