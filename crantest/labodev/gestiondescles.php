<?php

require_once('_const_fonc.php');

//include 'fonctions_base.php';

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

$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$form_gestiondescles = "form_gestiondescles";
//Modification Gestion des CLES

$action_cles = '';

$query_rs_cles = "SELECT nom,prenom,num_bureau,num_cle FROM individu ORDER BY nom";
$rs_cles = mysql_query($query_rs_cles) or die(mysql_error());

while($row_rs_cles=mysql_fetch_assoc($rs_cles))
{
    $tab_cles[$row_rs_cles['nom']] = $row_rs_cles;
                 
}

$_SESSION['tab_cles'] = $tab_cles;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>" />
<title>Gestion des cles
</title>
<link href="styles/normal.css" rel="stylesheet" type="text/css" />
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css" />
</head>
  
<body>
                    <!--GESTION DES CLES-->
<table border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des cles','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
        
    <tr>
        <td>
            <form name="<?php echo $form_gestiondescles ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codecles" value="codecles">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">GESTION des CLES</span></td>
                </tr>
            </table>
                
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
            <tr class="head">
                <td align="center"><table border="0" align="center" cellpadding="3" cellspacing="1">
                <td class="bleugrascalibri10">NOM</td>
                <td class="bleugrascalibri10">PRENOM</td>
                <td class="bleugrascalibri10">Num&#233;ro de bureau</td>
                <td class="bleugrascalibri10">Num&#233;ro des cl&eacute;s</td>
                
            </tr>
            <?php 
            $class="even";
            foreach($_SESSION['tab_cles'] as $un_tab_cles=> $row_rs_cles)
            { ?>
                <tr id="keyModify" ondblclick="window.location.href='edit_cles.php?nom=<?php echo $row_rs_cles['nom'];?>'" class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <td class="noircalibri10"><?php echo $row_rs_cles['nom']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_cles['prenom']; ?></td>
                    <td align="center" class="noircalibri10"><?php echo $row_rs_cles['num_bureau']; ?></td>
                    <td align="center" class="noircalibri10"><?php echo $row_rs_cles['num_cle']; ?></td>
                </tr>
            <?php 
            } ; ?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
    
<script type="text/javascript">
    function mofifyValueField(v)
    {
        location.href="edit_cles.php?nom="+v;
    }
    </script>  
    
</html>