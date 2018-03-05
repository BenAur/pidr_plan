<?php 
//require_once('_const_fonc.php');
//$nomrep='\\\\172.17.42.40\\pgend$';
$location = '\\172.17.42.40\\pgend';
$user = "pgend";
$pass = "p1g2e3n4";
$letter = "Z";
 
// Map the drive
system("net use ".$letter.": ".$location." /user:".$user." ".$pass." /persistent:no");
 
// Open the directory
$dir = opendir("\\".$location);
 
 while ($file = readdir($dir)) {
        // Affichage du nom du fichier (ou sous-répertoire)
        echo "$file<br/>";
    }
     
    // C'est fini. On ferme !
    closedir($dir);
		
/* $entry = $contenurep->read();
$nb_fichiers=0;
copierep($GLOBALS['path_to_rep_upload'],$GLOBALS['path_to_rep_upload_sauve']);
$message_webmaster.="<br>".$nb_fichiers.' copies';

function copierep($nomreporig,$nomrepdest)
{ global $nb_fichiers,$message_webmaster;
	$contenurep = dir($nomreporig);
	while (false !== ($entry = $contenurep->read()))  
	{	clearstatcache();
		if(!is_dir($nomreporig.'/'.$entry))
		{	$nb_fichiers++;
			$message_webmaster.="<br>". date ("F d Y H:i:s.", filemtime($nomreporig.'/'.$entry)).' '.$nomreporig.'/'.$entry.' -> '.$nomrepdest.'/'.$entry;
			copy($nomreporig.'/'.$entry, $nomrepdest.'/'.$entry);
		}
		else 
		{ if($entry!="." && $entry!="..")
			{ if(!is_dir($nomrepdest.'/'.$entry))
				{ mkdir($nomrepdest.'/'.$entry);
				}
				copierep($nomreporig.'/'.$entry,$nomrepdest.'/'.$entry);
			}
		}
	} 
}


 */
?>