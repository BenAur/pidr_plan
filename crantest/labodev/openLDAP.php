<?php

// Afficher les erreurs � l'�cran
ini_set('display_errors', 1);
// Enregistrer les erreurs dans un fichier de log
ini_set('log_errors', 1);
// Nom du fichier qui enregistre les logs (attention aux droits � l'�criture)
ini_set('error_log', dirname(__file__) . '/log_error_php.txt');
// Afficher les erreurs et les avertissements
error_reporting(e_all);

// LDAP variables
$ldaphost = "193.54.21.17"; // votre serveur LDAP
$ldapport = 389; // votre port de serveur LDAP

// Connexion LDAP
$ldapconn = ldap_connect($ldaphost, $ldapport)
or die("Impossible de se connecter au serveur LDAP $ldaphost");
echo "Le r�sultat de la connexion est " . $ldapconn;
echo "<br>";


// El�ments d'authentification LDAP
$ldaprdn = "cn=Manager,dc=info-sys,dc=fr"; // DN ou RDN LDAP
$ldappass = "secret"; // Mot de passe associ�


 if ($ldapconn) 
{

	// Connexion au serveur LDAP
	$ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);
	//$ldapbind = ldap_bind($ldapconn);

	// V�rification de l'authentification
	if ($ldapbind)
	{
		echo "Connexion LDAP r�ussie...";

		echo "<br>";		

		// Pr�pare les donn�es
		$ldaprecord['mail'] = "toto@univ-lorraine.fr";
		$ldaprecord['userPassword'] = "test";
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "person";
		$ldaprecord['objectclass'][2] = "organizationalPerson";
		$ldaprecord['objectclass'][3] = "inetOrgPerson";
		$ldaprecord['sn'] = "toto";
		$ldaprecord['cn'] = "toto";
		

		// Ajoute les donn�es au dossier
		if(ldap_add($ldapconn, "cn=toto,dc=info-sys,dc=fr", $ldaprecord))
			echo "Inscription r�ussie";  
		else
		{
			echo "echec de l'ajout . <br>";
			echo "User unsuccessful : ".$ldaprecord['cn']."<br><br>";
		}
			

		echo "Fermeture de la connexion";
		ldap_close($ldapconn); 

	} 
	else
	{
		echo "<h4>Impossible de se connecter au serveur LDAP.</h4>";
	}

} 

else 
{
	echo "Impossible de se connecter au serveur $ldaphost"; 
} 

?>