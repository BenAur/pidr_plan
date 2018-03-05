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

$form_gestioneditioncles = "form_gestioneditioncles";

//Récupération du nom
if (isset($_GET['nom']))
{
    $nom = $_GET['nom'];
    $query_rs_cles = "SELECT nom,prenom,num_bureau,num_cle FROM individu WHERE nom ='$nom'";
    $rs_cles = mysql_query($query_rs_cles) or die(mysql_error());
    $row_rs_cles=  mysql_fetch_assoc($rs_cles);
    $_SESSION['nom'] = $nom;
}

if (isset($_POST['submit']) && $_POST['submit']=="valider")
{
    mysql_query("update individu set num_bureau=".GetSQLValueString($_POST['num_bureau'], "text").", "
                                                . "num_cle=".GetSQLValueString($_POST['num_cle'], "text")." where nom=".GetSQLValueString($_SESSION['nom'],"text")) or die(mysql_error());

    header('Location: gestiondescles.php');
    exit;
}
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>" />
<title>Gestion des cles
</title>
<link href="styles/normal.css" rel="stylesheet" type="text/css" />
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css" />
</head>
 
<body>
<table border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des cles','lienretour'=>'gestiondescles.php','texteretour'=>'Retour a la gestion des cles',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
    
    <tr>
        <td>
            <form name="form_gestioneditioncles" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codecles" value="codecles">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">Modification N&#176; cl&#233; et bureau pou un individu</span></td>
                </tr>
            </table>
                
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td class="noircalibri12G"><?php echo $row_rs_cles['nom']; ?></td>
                    <td class="noircalibri12G"><?php echo $row_rs_cles['prenom']; ?></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                
                <tr>
                    <td>
                        N&#176; de bureau :
                    </td>
                    <td>
                        <input type='texte' name='num_bureau' value='<?php echo $row_rs_cles['num_bureau'] ?>' class="noircalibri10" size="20" maxsize="20">
                    </td>    
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        N&#176; de cl&#233; :
                    </td>
                    <td>
                        <input type='texte' name='num_cle' value='<?php echo $row_rs_cles['num_cle'] ?>' class="noircalibri10" size="20" maxsize="20">
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="submit" value="valider">
                    </td>
                </tr>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>
