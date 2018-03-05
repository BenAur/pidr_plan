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

$form_gestioncle = "form_gestioncle";
//Modification Gestion des CLES

if ((empty($_POST['codecle']) || isset($_POST['codecle']) == "codecle")) 
{
    $query_rs_cle = "SELECT codecle,num_cle,ouvre_passe,statut1,nbre_dispo,statut2,nbre_HS,nom,num_bureau,passe FROM cle_standard ORDER BY codecle";
    $rs_cle = mysql_query($query_rs_cle) or die(mysql_error());
    
    //$tab_lieu = array();
    
    while($row_rs_cle=mysql_fetch_assoc($rs_cle))
    {   
        $table = array();
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%cle%' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $num_cle = $row_rs_cle['num_cle'];
            if (!isset($num_cle))
            {
                break;
            }
            else if ($table =="cle_standard")
            {
                
            }
            else
            {
                $query_rs = "SELECT * FROM $table WHERE $colonne = ".GetSQLValueString($num_cle, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);
               
                if ($row_rs > 0)
                {
                    $tab_cle[$row_rs_cle['codecle']]=$row_rs_cle;
                    $tab_cle[$row_rs_cle['codecle']]['supprimable']=false;//par defaut 
                    $tab_cle[$row_rs_cle['codecle']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_cle[$row_rs_cle['codecle']]=$row_rs_cle;
                    $tab_cle[$row_rs_cle['codecle']]['supprimable']=true;//par defaut 
                    $tab_cle[$row_rs_cle['codecle']]['droitmodif']=$droitmodif;
                }
            }
            //$i++;

        }
    }
    $_SESSION['tab_cle'] = $tab_cle;
}


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
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des cles','lienretour'=>'newgestiondescles.php','texteretour'=>'Retour a la gestion des cl&eacute;s',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
        
    <tr>
        <td>
            <form name="<?php echo $form_gestioncle ?>" method="post" action="ajout_passe.php">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codecle" value="codecle">
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
                <td class="bleugrascalibri11">Code cl&eacute;</td>
                <td class="bleugrascalibri11">Num&#233;ro de la cl&eacute;</td>
            </tr>
            <?php 
                $class="even";
                foreach($_SESSION['tab_cle'] as $un_codecle=> $row_rs_cle)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                        <td class="noircalibri10" onclick="DisplayPassOn('<?php echo $row_rs_cle['codecle'] ?>');"><?php echo $row_rs_cle['codecle']; ?></td>
                        <td id="test" class="noircalibri10" onclick="DisplayPassOn('<?php echo $row_rs_cle['codecle'] ?>');" onMouseOut="DisplayPassOut();"><?php echo $row_rs_cle['num_cle']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr><?php
                                    if ($row_rs_cle['droitmodif'])
                                    {
                                            ?>
                                        <td><input type="image" name="submit#modifier##<?php echo $row_rs_cle['codecle'] ?>###cle####" src="images/b_edit.png">
                                        </td>
                                        <td>
                                            <?php
                                            if ($row_rs_cle['supprimable'])
                                            {
                                                ?>
                                                <input type="image" name="submit#supprimer##<?php echo $row_rs_cle['codecle'] ?>###cle####" src="images/b_drop.png">
                                                </td>
                                                <?php    
                                            }
                                            else
                                            {
                                                ?>
                                                &nbsp;
                                                <?php
                                            }
                                    }
                                    else
                                    {
                                        ?>
                                                &nbsp;
                                                <?php
                                    }?>
                                    
                                                
                                </tr>
                            </table>
                        </td>    
                    </tr>

                <?php
                }
                if ($estadmin)
                {
                    ?>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        <input type="text" name="num_cle" value="" class="noircalibri10" size="20" maxsize="20">                        
                    </td>
                    <td>
                        <input type="image" name="submit#creer##cle###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()">
                    </td>
                </tr>
                <?php
                }
                ?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body> 
<script>
    var My_Popup;
    function DisplayPassOn(lien)
    {

        var link = 'affichepasse.php?codecle='+lien;
        if (!My_Popup)
        {
            My_Popup = My_Popup = window.open(link,'Passes','menubar=no, scrollbars=yes, top=100, left=200, width=300, height=500');
            My_Popup.focus();
        }
        else
         {
             My_Popup = My_Popup = window.open(link,'Passes','menubar=no, scrollbars=yes, top=100, left=200, width=300, height=500');
             My_Popup.focus();
         }
             
    }
        
//        function DisplayPassOut()
//        {
//            if(false == My_Popup.closed)
//            {
//                My_Popup.close();
//            }
//            else
//            {
//                alert('window already closed !');
//            }
//            
//        }
    </script>   
</html>