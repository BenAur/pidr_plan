<?php
require_once ('_const_fonc.php');

include 'fonctions_base.php';

$bdd = "labodev";

$codeuser=deconnecte_ou_connecte();

// Par d�faut, codeindividu est initialise 
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppl�ant"
// d�finis par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forc�ment referent
$estresptheme=false;// user a le role theme mais pas forc�ment r�f�rent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);//renvoie table des roles de $codeuser et modifie $estreferent+$estresptheme
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$tab_infouser=get_info_user($codeuser);// nom, prenom, gt(s)...

$estadmin=  in_array('du',$tab_roleuser) || in_array('admingestfin',$tab_roleuser) || in_array('gestprojet',$tab_roleuser) || in_array($admin_bd,$tab_infouser) ;

$droitmodif=false;
$droitsuppr=false;
$afficher=false;
  
if($estadmin)
{ 
    $droitmodif=true;
    $droitsuppr=true;
    $afficher=true;
}

else
    $afficher=true;
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$codification='';
//CENTRE COUT CENTRE FINANCIER
$query_rs_typecreditcentrefinanciercentrecout="select typecredit.codetypecredit,typecredit.libcourt as libtypecredit,centrefinancier.codecentrefinancier,centrefinancier.libcourt as libcentrefinancier,centrecout.codecentrecout,centrecout.libcourt as libcentrecout".
																							" from typecredit,centrefinancier,centrecout".
																							" where typecredit.codetypecredit=centrefinancier.codetypecredit".
																							" and centrefinancier.codecentrefinancier=centrecout.codecentrefinancier".
																							" and typecredit.codetypecredit<>''".
																							" order by typecredit.codetypecredit,centrefinancier.codecentrefinancier,centrecout.codecentrecout";
$rs_typecreditcentrefinanciercentrecout = mysql_query($query_rs_typecreditcentrefinanciercentrecout) or die(mysql_error());
$row_rs_typecreditcentrefinanciercentrecout = mysql_fetch_assoc($rs_typecreditcentrefinanciercentrecout);

$form_typecreditcentrefinanciercentrecout = "form_typecreditcentrefinanciercentrecout";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>" />
<title>Nomenclatures et structures
</title>
<link href="styles/normal.css" rel="stylesheet" type="text/css" />
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css" />
</head>
 
<body>
                    <!--GESTION CORPS MODE DE FINANCEMENT-->
<table border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>
            <form name="<?php echo $form_typecreditcentrefinanciercentrecout ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codetypecreditcentrefinanciercentrecout" value="codetypecreditcentrefinanciercentrecout">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            <tr class="head">
                <td align="center" class="mauvegrascalibri11">Credit - Type credit - Enveloppe</td>
            </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
            <tr class="head">
                <td align="center"><table border="0" align="center" cellpadding="3" cellspacing="1">
                <td class="bleugrascalibri10" colspan="2">Credit</td>
                <td class="bleugrascalibri10" colspan="2">Type credit</td>
                <td class="bleugrascalibri10" colspan="2">Enveloppe</td>
            </tr>
            <?php 
            $class="even";
            do
            { ?>
            <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                <td align="center" class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['codetypecredit']; ?></td>
                <td class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['libtypecredit']; ?></td>
                <td align="center" class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['codecentrefinancier']; ?></td>
                <td class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['libcentrefinancier']; ?></td>
                <td align="center" class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['codecentrecout']; ?></td>
                <td class="noircalibri10"><?php echo $row_rs_typecreditcentrefinanciercentrecout['libcentrecout']; ?></td>
            </tr>
            <?php 
            } while ($row_rs_typecreditcentrefinanciercentrecout = mysql_fetch_assoc($rs_typecreditcentrefinanciercentrecout)); ?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>
