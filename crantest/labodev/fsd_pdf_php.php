<?php  require_once('_const_fonc.php');
?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
fsd_sujet_pdf_php($codeindividu,$numsejour);

?>
