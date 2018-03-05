<?php
require_once ('_const_fonc.php');

#include 'fonctions_base.php';

#$bdd = "labodev";

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
$form_gestioned = "form_gestioned";

//Modification ECOLE DOCTORALE

$action = '';

if (isset($_POST['codeed']) && $_POST['codeed'] == "codeed")
{
    foreach($_POST as $postkey=>$postval)
    { 
        if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
        { 
            $submit=$postkey;
            $pos4diese=strpos($postkey,'####');
            if ($pos4diese!==false)
            {
                $submit=  substr($postkey,0,$pos4diese);
                $pos3diese=strpos($submit,'###');
                if($pos3diese!==false)
                { 
                    $codification = substr($submit,$pos3diese+3);
                    if ($codification == "ed")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codeed=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action=substr($submit,$posdiese+1);
                            }
                        }
                    }
                    else
                        break;   
                }
            }

            else
            { 
                $pos3diese=strpos($postkey,'###');
                if($pos3diese!==false)
                { 
                    $submit=substr($postkey,0,$pos3diese);
                    $pos2diese=strpos($submit,"##");
                    if($pos2diese!=false)
                    { 
                        $codification = substr($submit,$pos2diese+2);
                        if ($codification == "ed")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action=substr($submit,$posdiese+1);
                            }
                        }
                        else
                            break;
                    }
                }
            }
        }
    }
          
    // Traitement de l'action demandee dans le POST
    if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
    {   
        if ($action == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action=='creer' || $action=='enregistrer')
        { 
            if(isset($_POST['libcourted_fr']) && $_POST['libcourted_fr']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
            // on enregistre d'abord nouveau ed
            if($action=='creer')
            { 
                //$Matchfound = "";
                $rs=mysql_query("select max(codeed) as maxcodeed from ed");
                if (isset ($_POST["libcourted_fr"]))
                {
                    $query = "SELECT * FROM ed WHERE libcourted_fr = ".GetSQLValueString($_POST['libcourted_fr'], "text");
                    $result = mysql_query($query) or die(mysql_error());    
                }
                
                if (mysql_num_rows($result) != 0)
                {
                    ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                }
                else
                {
                    $row_rs=mysql_fetch_assoc($rs);
                    $codeed=str_pad((string)((int)$row_rs['maxcodeed']+1), 2, "0", STR_PAD_LEFT);  
                    
                    $result = mysql_query("insert into ed (codeed,date_deb,date_fin,libcourted_fr,libcourted_en,liblonged_fr,liblonged_en,lienpghttped) ".
									" values (".GetSQLValueString($codeed, "text").",'','',"
                                                                                    .GetSQLValueString($_POST['libcourted_fr'], "text").","
                                                                                    .GetSQLValueString($_POST['libcourted_en'], "text").","
                                                                                    .GetSQLValueString($_POST['liblonged_fr'], "text").","
                                                                                    .GetSQLValueString($_POST['liblonged_en'], "text").",'')") or die(mysql_error());
                                        
                    $result = mysql_query($query);
                    // Vérification du résultat
                    // Ceci montre la requête envoyée à MySQL ainsi que l'erreur. Utile pour déboguer.
                    if (!$result) 
                    {
                        $message  = 'Requête invalide : ' . mysql_error() . "\n";
                        $message .= 'Requête complète : ' . $query;
                        die($message);
                    }
                }

            }

            else if($action=='enregistrer')
            {
                if (isset ($_POST["libcourted_fr"]))
                {
                    $query = "SELECT * FROM ed WHERE libcourted_fr = ".GetSQLValueString($_POST['libcourted_fr'], "text");
                    $result = mysql_query($query) or die(mysql_error());
                    
                    if (mysql_num_rows($result) != 0)
                    {
                        ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                    }
                    else
                    {
                        mysql_query("update ed set libcourted_fr=".GetSQLValueString($_POST['libcourted_fr'], "text").", "
                                            . "libcourted_en=".GetSQLValueString($_POST['libcourted_en'], "text").", "
                                            . "liblonged_fr=".GetSQLValueString($_POST['liblonged_fr'], "text").","
                                            . "liblonged_en=".GetSQLValueString($_POST['liblonged_en'], "text")." where codeed=".GetSQLValueString($codeed, "text")) or die(mysql_error());
                    }
                }
            }
            else if($action=='supprimer')

            {	
                mysql_query("delete from ed  where codeed=".GetSQLValueString($codeed, "text")) or die(mysql_error());

            }
        }
    }
}

if ((isset($_POST['codeed']) && $_POST['codeed'] == "codeed") && $action !="modifier") 
{
    $query_rs_ed = "SELECT codeed,libcourted_fr,libcourted_en,liblonged_fr,liblonged_en FROM ed ORDER BY codeed";
    $rs_ed = mysql_query($query_rs_ed) or die(mysql_error());
    
    //$tab_lieu = array();
    
    while($row_rs_ed=mysql_fetch_assoc($rs_ed))
    {   
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%codeed%' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $codeed = $row_rs_ed['codeed'];
            if ($codeed =="")
            {
                break;
            }
            else if ($table =="ed")
            {
                
            }
            else
            {
                $query_rs = "SELECT * FROM $table WHERE $colonne = ".GetSQLValueString($codeed, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);

                if ($row_rs > 0)
                {
                    $tab_ed[$row_rs_ed['codeed']]=$row_rs_ed;
                    $tab_ed[$row_rs_ed['codeed']]['supprimable']=false;//par defaut
                    $tab_ed[$row_rs_ed['codeed']]['droitmodif']=$droitmodif;
                }
                else 
                {
                    $tab_ed[$row_rs_ed['codeed']]=$row_rs_ed;
                    $tab_ed[$row_rs_ed['codeed']]['supprimable']=true;//par defaut 
                    $tab_ed[$row_rs_ed['codeed']]['droitmodif']=$droitmodif;
                }
            }
        }
    }
    $_SESSION['tab_ed'] = $tab_ed;
}
   
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
                    <!--GESTION DES ECOLES DOCTORALES-->
<tables border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>
            <form name="<?php echo $form_gestioned ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codeed" value="codeed">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">Ecoles doctorales</span></td>
                </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
                
                <tr class="head">
                  <td class="bleugrascalibri11">Code ed</td>
                  <td class="bleugrascalibri11">Lib. court ed fr</td>
                  <td class="bleugrascalibri11">Lib. court ed en</td>
                  <td class="bleugrascalibri11">Lib. long ed fr</td>
                  <td class="bleugrascalibri11">Lib. long ed en</td>
                  <td nowrap class="bleugrascalibri10">Action</td
                ></tr>
                <?php 
                $class="even";
                foreach($tab_ed as $un_codeed=> $row_rs_ed)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <?php

                    if ($action=='modifier')
                    {
//                        codeed
                        ?>
                        <td nowrap align="left">
                        <?php

                        echo $row_rs_ed['codeed'];
                        ?>
                        </td>
<!--                        libcourted_fr-->
                        <td nowrap>
                        <?php
                        if ($row_rs_ed['codeed']==$codeed)
                        {
                            ?>
                            <input type="text" name="libcourted_fr" value="<?php echo $row_rs_ed['libcourted_fr'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_ed['libcourted_fr'];
                        }?>                                
                        </td>
<!--                        libcourted_en-->
                        <td nowrap>
                        <?php
                        if ($row_rs_ed['codeed']==$codeed)
                        {
                            ?>
                            <input type="text" name="libcourted_en" value="<?php echo $row_rs_ed['libcourted_en'] ?>" class="noircalibri10" size="100" maxsize="120">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_ed['libcourted_en'];
                        }?>                                
                        </td>
<!--                        liblonged_fr -->
                        <td nowrap>
                        <?php
                        if ($row_rs_ed['codeed']==$codeed)
                        {
                            ?>
                            <input type="text" name="liblonged_fr" value="<?php echo $row_rs_ed['liblonged_fr'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_ed['liblonged_fr'];
                        }?>                                
                        </td>
<!--                        liblonged_en-->
                        <td nowrap>
                        <?php
                        if ($row_rs_ed['codeed']==$codeed)
                        {
                            ?>
                            <input type="text" name="liblonged_en" value="<?php echo $row_rs_ed['liblonged_en'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_ed['liblonged_en'];
                        }?>                                
                        </td>
                        <!-- Action -->
                        
                        <?php
                        if($row_rs_ed['codeed']==$codeed)
                        {
                            ?>
                            <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_ed['codeed'] ?>###ed####" src="images/b_enregistrer.png">
                            </td>
<!--                            <td><input type="image" name="submit#annuler##<?php echo $row_rs_ed['codeed'] ?>###ed####" src="images/b_drop.png">
                            </td>-->
                            <?php    
                        }
                        
                        else
                        { 
                            echo '&nbsp';
                        }?>
                        </td>
                        <?php
                    }
                    else 
                    {
                       ?>
                        <td class="noircalibri10"><?php echo $row_rs_ed['codeed']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_ed['libcourted_fr']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_ed['libcourted_en']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_ed['liblonged_fr']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_ed['liblonged_en']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr>
                                    <td><input type="image" name="submit#modifier##<?php echo $row_rs_ed['codeed'] ?>###ed####" src="images/b_edit.png">
                                    </td>
                                    <td>
                                        <?php
                                        if ($row_rs_ed['supprimable'])
                                        {
                                            ?>
<!--                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_ed['codeed'] ?>###" src="images/b_drop.png" onClick="alert('Pas opeacute;rationel');return false;">-->
                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_ed['codeed'] ?>###ed####" src="images/b_drop.png">
                                            </td>
                                            <?php    
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
                        <?php
                    }?>
                    </tr>

                <?php
                }
                if ($action!='modifier')
                {?>
                    
                    <tr>
                        <td></td>
                        <td><input type="text" name="libcourted_fr" value="" class="noircalibri10" size="20" maxsize="20"></td>        
                        <td><input type="text" name="libcourted_en" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="liblonged_fr" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="liblonged_en" value="" class="noircalibri10" size="20" maxsize="20"></td>
                        <td><input type="image" name="submit#creer##ed###" class="noircalibri10" src="images/b_add.png" ></td>
                    </tr>
                    <?php
                }?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>
    