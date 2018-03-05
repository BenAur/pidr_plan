<?php require_once('_const_fonc.php'); 
//~~~~Crypte le fichier
/* function crypte_fichier($chemin_fichier,$chaine_crypt,$chemin1_fichier)
{
  $lignecripte="";
  $bytes = 65536;//nombre de bytes par ligne de cryptage
  //remplit une ligne de cryptage de longueur 65536 bites
  for ($i = 0; $i <= floor($bytes/strlen($chaine_crypt)); $i++) 
	{ $lignecripte.= $chaine_crypt;
	}
  //ouvre le fichier a crypter en lecture
  //cree le nouveau fichier
	if (file_exists($chemin_fichier))
	{//verifie presence du fichier
	//chmod($chemin_fichier,0777);//attribue tous droits
	$ancien = fopen($chemin_fichier, "rb");
	$nouveau = fopen($chemin1_fichier, "wb");
	// crypt le fichier et ecrie dans le nouveau fichier par ligne de 65536 bites
	while($line = fread($ancien, $bytes)){
	//while($line = fread($ancien)){
		$line2 = $line ^ $lignecripte;//effectue un OU EXCLUSIF (XOR) sur les bits 10011s^ 10110=00101 
		fputs($nouveau, $line2);}
	// ferme les fichiers
	fclose($ancien);fclose($nouveau);
	//unlink($chemin_fichier);//suprimme l'ancien fichier
	}
}

//~~~~declare les entetes de fichier~~~~//
function telecharge($nomfichier,$chemin1_fichier){
	//entete de header precise au navigateur l'envoi d'un fichier
	header("Content-disposition: attachment; filename=$nomfichier");
	header("Content-Type: application/force-download");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($chemin1_fichier));
	header("Pragma: no-cache");
	header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
	header("Expires: 0");
}

//~~~~Decrypte le fichier~~~~//
//fonction a appler apres avoir declarer les header ouvrira une boite de telechargement
function decrypte_file($chemin1_fichier,$chaine_crypt,$chemin2_fichier){
	$contenu="";
	$bytes = 65536; //bite par ligne
	$lignecripte="";
	$chainecrypte=$chaine_crypt;
	//remplit une ligne de cryptage de longueur 65536 bites
	for ($i = 0; $i <= floor($bytes/strlen($chainecrypte)); $i++) 
	{ $lignecripte.= $chainecrypte;
	}
  	// ouvre le fichier
  	$file = fopen($chemin1_fichier, "rb");
	$nouveau = fopen($chemin2_fichier, "wb");
  	while($line = fread($file, $bytes))
		{
    	$line2 = $line ^ $lignecripte;//effectue un OU EXCLUSIF (XOR) sur les bits 10011s^ 10110=00101 
    	// affichage du fichier
    	fputs($nouveau, $line2);
  	}
} */

///~~~~~progrmamme ~~~~~~~////
$fichier="temp/ag_cran_2012_temp.JPG";
$cle="macle";

chiffre_fichier($fichier,$cle);


//decrypt le fichier "fichier1.zip" qui se trouve dans le repertoire "/rep_crypt/"
//chiffre_fichier($fichier,$cle);
/*telecharge($nomfichier,$chemin1_fichier);*/

?>






