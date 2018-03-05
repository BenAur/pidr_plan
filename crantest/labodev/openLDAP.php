<?php

// Afficher les erreurs à l'écran
ini_set('display_errors', 1);
// Enregistrer les erreurs dans un fichier de log
ini_set('log_errors', 1);
// Nom du fichier qui enregistre les logs (attention aux droits à l'écriture)
ini_set('error_log', dirname(__file__) . '/log_error_php.txt');
// Afficher les erreurs et les avertissements
error_reporting(e_all);

// LDAP variables
$ldaphost = "193.54.21.17"; // votre serveur LDAP
$ldapport = 389; // votre port de serveur LDAP

// Connexion LDAP
$ldapconn = ldap_connect($ldaphost, $ldapport)
or die("Impossible de se connecter au serveur LDAP $ldaphost");
echo "Le résultat de la connexion est " . $ldapconn;
echo "<br>";


// Eléments d'authentification LDAP
$ldaprdn = "cn=Manager,dc=info-sys,dc=fr"; // DN ou RDN LDAP
$ldappass = "secret"; // Mot de passe associé


 if ($ldapconn) 
{

	// Connexion au serveur LDAP
	$ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);
	//$ldapbind = ldap_bind($ldapconn);

	// Vérification de l'authentification
	if ($ldapbind)
	{
		echo "Connexion LDAP réussie...";

		echo "<br>";		

		// Prépare les données
		$ldaprecord['mail'] = "toto@univ-lorraine.fr";
		$ldaprecord['userPassword'] = "test";
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "person";
		$ldaprecord['objectclass'][2] = "organizationalPerson";
		$ldaprecord['objectclass'][3] = "inetOrgPerson";
		$ldaprecord['sn'] = "toto";
		$ldaprecord['cn'] = "toto";
		

		// Ajoute les données au dossier
		if(ldap_add($ldapconn, "cn=toto,dc=info-sys,dc=fr", $ldaprecord))
			echo "Inscription réussie";  
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