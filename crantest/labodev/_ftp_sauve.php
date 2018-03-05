<?php require_once('_const_fonc.php'); 
$ftp_server = "195.220.155.5";
$ftp_user_name='pgend';
$ftp_user_pass='p1g2e3n4';
$zip = new ZipArchive();
if ($zip->open('rep_upload.zip', ZipArchive::CREATE) === TRUE) 
{ foreach(array('commande','individu','mission','contrat') as $rep)
	{ 
	
		$rs=mysql_query("select ".$rep."pj.* from ".$rep."pj order by code".$rep);
		while($row_rs=mysql_fetch_assoc($rs))
		{ echo $rep;
			foreach($row_rs as $field=>$val)
			{ echo '/'.$val;
			}
			echo '<br>';//$contenu=	'<a href="download.php?codeindividu='.$codeindividu.'&codelibcatpj='.$codelibcatpj.'&numcatpj='.$numcatpj.'&codetypepj='.$codetypepj.'" target="_blank" title="T&eacute;l&eacute;charger '.$row_rs['nomfichier'].' ('.$txt_pj.')">'.
		}
	}
    $zip->addFile('upload1.txt');
    $zip->addFile('upload2.txt');
    $zip->addFile('images/12plus.png');
    $zip->close();
} 
else 
{ echo 'échec';
}

// Mise en place d'une connexion
$conn_id = ftp_connect($ftp_server) or die("Impossible de se connecter au serveur $ftp_server"); 
$file = 'rep_upload.zip';
$remote_file = 'rep_upload.zip';

// Identification avec un nom d'utilisateur et un mot de passe
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// Charge un fichier
if (ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
 echo "Le fichier $file a ete charge avec succes\n";
} else {
 echo "Il y a eu un probleme lors du chargement du fichier $file\n";
}

// Fermeture de la connexion
ftp_close($conn_id);
?>