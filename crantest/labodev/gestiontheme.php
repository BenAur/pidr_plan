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
//THEMES
$query_rs_gesttheme = "SELECT * FROM gesttheme,structure,individu WHERE gesttheme.codetheme=structure.codestructure and gesttheme.codegesttheme= individu.codeindividu order by gesttheme.codetheme";
$rs_gesttheme = mysql_query($query_rs_gesttheme) or die(mysql_error());
$row_rs_gesttheme = mysql_fetch_assoc($rs_gesttheme);
$totalRows_rs_gesttheme = mysql_num_rows($rs_gesttheme);
$form_gesttheme = "form_gesttheme";

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
                    <!--GESTION DES THEMES-->
<table border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>
            <form name="<?php echo $form_gesttheme ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codetheme" value="codetheme">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">Gestionnaires de th&egrave;mes</span></td>
                </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr <tr class="head">
                    <td class="bleugrascalibri10">Code Gest theme</td>
                    <td class="bleugrascalibri10">Code structure (theme)</td>
                    <td class="bleugrascalibri10">Code libell&eacute;</td>
                    <td class="bleugrascalibri10">Lib. court fran&ccedil;ais</td>
                    <td class="bleugrascalibri10">Nom</td>
                    <td class="bleugrascalibri10">Prenom</td>
                </tr>
                <?php 
                $class="even";
                do
                    { ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                        <td align="center" class="noircalibri10"><?php echo $row_rs_gesttheme['codegesttheme']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_gesttheme['codetheme']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_gesttheme['codelib']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_gesttheme['libcourt_fr']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_gesttheme['nom']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_gesttheme['prenom']; ?></td>
                    </tr>
                    <?php 
                    } while ($row_rs_gesttheme = mysql_fetch_assoc($rs_gesttheme)); ?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>