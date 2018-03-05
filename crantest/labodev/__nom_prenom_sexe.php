<?php  require_once('_const_fonc.php'); 

$fichier="___divers/nom_prenom_sexe.csv";
if (!($fp_fichier = @fopen($fichier, "r"))) 
{ die("Impossible d'ouvrir le document "); 
}
$contenu='';
while ($data = fread($fp_fichier, 4096)) 
{ $contenu=$contenu. $data;
}
fclose($fp_fichier);
$tab=explode("\n",$contenu);
foreach($tab as $num=>$ligne)
{	$tab_champ=explode(";",$ligne);
  $nom=trim($tab_champ[0]);
  $prenom=trim($tab_champ[1]);
  $sexe=trim($tab_champ[2]);
	echo $sexe.' '.$nom.' '.$prenom.'<br>';
}
?>