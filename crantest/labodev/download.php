<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$erreur=false;
if(isset($_REQUEST['codeindividu']))
{	$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
	$codelibcatpj=isset($_GET['codelibcatpj'])?$_GET['codelibcatpj']:(isset($_POST['codelibcatpj'])?$_POST['codelibcatpj']:"");
	$numcatpj=isset($_GET['numcatpj'])?$_GET['numcatpj']:(isset($_POST['numcatpj'])?$_POST['numcatpj']:"");
	$codetypepj=isset($_GET['codetypepj'])?$_GET['codetypepj']:(isset($_POST['codetypepj'])?$_POST['codetypepj']:"");
	$rs=mysql_query("select nomfichier from individupj".
									" where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and codelibcatpj=".GetSQLValueString($codelibcatpj, "text").
									" and numcatpj=".GetSQLValueString($numcatpj, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/'.$codelibcatpj.'/'.$numcatpj ;
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codetypepj);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['codecontrat']))
{	$codecontrat=isset($_REQUEST['codecontrat'])?$_REQUEST['codecontrat']:"";
	$codetypepj=isset($_REQUEST['codetypepj'])?$_REQUEST['codetypepj']:"";
	$rs=mysql_query("select nomfichier from contratpj".
									" where codecontrat=".GetSQLValueString($codecontrat, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/contrat/'.$codecontrat ;
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codetypepj);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['codeprojet']))
{	$codeprojet=isset($_REQUEST['codeprojet'])?$_REQUEST['codeprojet']:"";
	$codetypepj=isset($_REQUEST['codetypepj'])?$_REQUEST['codetypepj']:"";
	$rs=mysql_query("select nomfichier from projetpj".
									" where codeprojet=".GetSQLValueString($codeprojet, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/projet/'.$codeprojet ;
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codetypepj);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['codecommande']))
{	$codecommande=isset($_REQUEST['codecommande'])?$_REQUEST['codecommande']:"";
	$codetypepj=isset($_REQUEST['codetypepj'])?$_REQUEST['codetypepj']:"";
	$rs=mysql_query("select nomfichier from commandepj".
									" where codecommande=".GetSQLValueString($codecommande, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/commande/'.$codecommande ;
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codetypepj);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['codemission']))
{	$codemission=isset($_REQUEST['codemission'])?$_REQUEST['codemission']:"";
	$codetypepj=isset($_REQUEST['codetypepj'])?$_REQUEST['codetypepj']:"";
	$rs=mysql_query("select nomfichier from missionpj".
									" where codemission=".GetSQLValueString($codemission, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/mission/'.$codemission ;
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codetypepj);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['codefichier']))
{	$codefichier=$_REQUEST['codefichier'];
	$rs=mysql_query("select nom as nomfichier from uploadfichier".
									" where code_rep_ou_fichier=".GetSQLValueString($codefichier, "text")) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $rep_upload=$GLOBALS['path_to_rep_upload'].'/uploadfichier';
		$filename = explode('.', $row_rs['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		
		header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
		header('Content-Disposition: attachment; filename="'.$row_rs['nomfichier'].'"');
		readfile($rep_upload.'/'.$codefichier);
	}
	else
	{ $erreur=true;
	}
}
else if(isset($_REQUEST['fichierist']))
{	$nomfichier=$_REQUEST['fichierist'];
	$rep_upload=$GLOBALS['path_to_rep_upload'].'/ist/echange';
	$filename = explode('.', $nomfichier);
	$filenameext = $filename[count($filename)-1];
	header('Content-type: '.$GLOBALS['file_types_mime_array'][$filenameext]);
	header('Content-Disposition: attachment; filename="'.$nomfichier.'"');
	readfile($rep_upload.'/'.$nomfichier);
}

if($erreur)
{?> 
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Pi&egrave;ces jointes</title>
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