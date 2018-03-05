<?php
// GET page
$url = 'https://labintel.dsi.cnrs.fr/lab/jsp/cnx/cnxEc1.jsp';
// Initialize session and set URL.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, false);
// Don't return HTTP headers. Do return the contents of the call
curl_setopt($ch, CURLOPT_HEADER, false);

// Set so curl_exec returns the result instead of outputting it.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Get the response and close the channel.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_PORT, 443);
curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
$cert = "certificats/UTN-DATACorpSGC.p7c";
echo "cert loc $cert. File exists? ".(file_exists($cert) && is_readable($cert)?"yes":"no")."<br>";
//curl_setopt($ch, CURLOPT_CAINFO, $cert);

echo curl_exec($ch);
echo curl_getinfo($ch);
echo curl_error($ch);
curl_errno ($ch);
curl_close($ch);
?>


