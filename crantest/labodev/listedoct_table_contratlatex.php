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
\begin{longtable}{|p{2cm}|r|p{2.5cm}|p{4cm}|p{4cm}|p{8cm}|r|}\n";
}
$query_rs_contrat= "select * from _contratlatex";

$rs_contrat=mysql_query($query_rs_contrat) or die(mysql_error());
$pair=true;
$firsttab=true;
echo $car_debut_ligne_entete."Date d'effet".$car_cellule."Dur\\'ee mois".$car_cellule."Type".$car_cellule."Financement".$car_cellule."Responsable scientifique".$car_cellule."R\\'ef\\'erence programme".$car_cellule."Montant euro".$car_fin_ligne;
while($row_rs_contrat=mysql_fetch_assoc($rs_contrat))
{ echo txt2type($row_rs_contrat["dateffet"],$type_fichier).$car_cellule.txt2type($row_rs_contrat["duree"],$type_fichier).$car_cellule.
				txt2type($row_rs_contrat["type"],$type_fichier).$car_cellule.txt2type($row_rs_contrat["financement"],$type_fichier).$car_cellule.
				txt2type($row_rs_contrat["resp"],$type_fichier).$car_cellule.txt2type($row_rs_contrat["ref"],$type_fichier).$car_cellule.txt2type($row_rs_contrat["montant"],$type_fichier);
	echo $car_fin_ligne;
}
echo "\\hline\end{longtable}";
echo "\end{document}";
?>
<?php 
if(isset($rs_contrat)) {mysql_free_result($rs_contrat);}
?>




