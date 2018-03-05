<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte(); 
$tab_infouser=get_info_user($codeuser);
$codecontrat=isset($_GET['codecontrat'])?($_GET['codecontrat']):(isset($_POST['codecontrat'])?$_POST['codecontrat']:"");
?>	
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Gestion des contrats <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
</head>
<body>
<?php echo detailcontrat($codecontrat,$codeuser) ?>
</body>
</html>
