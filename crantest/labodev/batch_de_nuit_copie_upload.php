<?php require_once('_const_fonc.php'); ?>
<?php
//Verif. user autorise a lancer le batch
ini_set('display_errors',true);
echo 'batch_de_nuit_copie_upload';
$nb_fichiers=0;

copierep($GLOBALS['path_to_rep_upload'],$GLOBALS['path_to_rep_upload_sauve']);

echo $nb_fichiers.' copies';

function copierep($nomreporig,$nomrepdest)
{ global $nb_fichiers;
	$contenurep = dir($nomreporig);
	while (false !== ($entry = $contenurep->read()))  
	{	clearstatcache();
		if(!is_dir($nomreporig.'/'.$entry))
		{	$nb_fichiers++;
			echo date ("F d Y H:i:s.", filemtime($nomreporig.'/'.$entry)).' '.$nomreporig.'/'.$entry.' -&gt; '.$nomrepdest.'/'.$entry.'<br>';
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
 /* */
?>