<?php require_once('_const_fonc.php'); ?>
<?php
$rep_upload=$GLOBALS['path_to_rep_upload'].'/uploadfichier';
$erreur=false;
if(isset($_REQUEST['codeuploadfichier']))
{	$codeuploadfichier=$_REQUEST['codeuploadfichier'];
	$rs=mysql_query("select nom from uploadfichier".
									" where code_rep_ou_fichier=".GetSQLValueString($codeuploadfichier, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ 
		$filename = explode('.', $row_rs['nom']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		//header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codeuploadfichier);
	}
	else
	{ $erreur=true;
	}
}

if($erreur)
{?> 
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Fichiers</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
</head>
<body>
<p align="center">
<span class="orangecalibri11">Le fichier demand&eacute; n&rsquo;existe pas
</span>
</p>
</body>
</html>
<?php 
}
if(isset($rs)) {mysql_free_result($rs);}
?>