<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<title>Untitled Document</title>
</head>

<body>
<?php
// local duplique _const_fonc.php en _const_fonc_avant_compile.php
// transfert de _const_fonc_avant_compile.php de local sur serveur
// lance _bcompile.php => _const_fonc.phb
// supprime _const_fonc_avant_compile.php sur serveur
/* _const_fonc.php ne contient que <?php include("_const_fonc.phb");?>sur serveur */

$fh = fopen("_const_fonc.phb", "w");
bcompiler_write_header($fh);
bcompiler_write_file($fh, "_const_fonc_avant_compile.php");
bcompiler_write_footer($fh);
fclose($fh);
?>
</body>
</html>