<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$nb_fichier_ok=0;
$nb_fichier_pas_ok=0;
$rs=mysql_query("select * from individupj") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(file_exists ($GLOBALS['path_to_rep_upload'].'/individu/'.$row_rs['codeindividu'].'/'.$row_rs['codelibcatpj'].'/'.$row_rs['numcatpj'].'/'.$row_rs['codetypepj']))
	{ $nb_fichier_ok++;
	}
	else
	{ echo '<br><b>Pas OK</b>';
		$nb_fichier_pas_ok++;
	}
}
echo '<br>individu : nb_fichier_ok='.$nb_fichier_ok.' nb_fichier_pas_ok='.$nb_fichier_pas_ok;
$nb_fichier_ok=0;
$nb_fichier_pas_ok=0;
$rs=mysql_query("select * from contratpj") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(file_exists ($GLOBALS['path_to_rep_upload'].'/contrat/'.$row_rs['codecontrat'].'/'.$row_rs['codetypepj']))
	{ $nb_fichier_ok++;
	}
	else
	{ echo '<br><b>Pas OK</b>';
		$nb_fichier_pas_ok++;
	}
}
echo '<br>contrat : nb_fichier_ok='.$nb_fichier_ok.' nb_fichier_pas_ok='.$nb_fichier_pas_ok;
$nb_fichier_ok=0;
$nb_fichier_pas_ok=0;
$rs=mysql_query("select * from projetpj") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(file_exists ($GLOBALS['path_to_rep_upload'].'/projet/'.$row_rs['codeprojet'].'/'.$row_rs['codetypepj']))
	{ $nb_fichier_ok++;
	}
	else
	{ echo '<br><b>Pas OK</b>';
		$nb_fichier_pas_ok++;
	}
}
echo '<br>projet : nb_fichier_ok='.$nb_fichier_ok.' nb_fichier_pas_ok='.$nb_fichier_pas_ok;
$nb_fichier_ok=0;
$nb_fichier_pas_ok=0;
$rs=mysql_query("select * from commandepj") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(file_exists ($GLOBALS['path_to_rep_upload'].'/commande/'.$row_rs['codecommande'].'/'.$row_rs['codetypepj']))
	{ $nb_fichier_ok++;
	}
	else
	{ echo '<br><b>Pas OK</b>';
		$nb_fichier_pas_ok++;
	}
}
echo '<br>commande : nb_fichier_ok='.$nb_fichier_ok.' nb_fichier_pas_ok='.$nb_fichier_pas_ok;
$nb_fichier_ok=0;
$nb_fichier_pas_ok=0;
$rs=mysql_query("select * from missionpj") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(file_exists ($GLOBALS['path_to_rep_upload'].'/mission/'.$row_rs['codemission'].'/'.$row_rs['codetypepj']))
	{ $nb_fichier_ok++;
	}
	else
	{ echo '<br><b>Pas OK</b>';
		$nb_fichier_pas_ok++;
	}
}
echo '<br>mission : nb_fichier_ok='.$nb_fichier_ok.' nb_fichier_pas_ok='.$nb_fichier_pas_ok;

if(isset($rs)) {mysql_free_result($rs);}
?>