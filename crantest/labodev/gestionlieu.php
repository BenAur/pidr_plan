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
$form_gestionlieu = "form_gestionlieu";

//Modification LIEU

$action_lieu = '';

if (isset($_POST['codelieu']) && $_POST['codelieu'] == "codelieu")
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
                    if ($codification == "lieu")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codelieu=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_lieu=substr($submit,$posdiese+1);
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
                        if ($codification == "lieu")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_lieu=substr($submit,$posdiese+1);
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
        if ($action_lieu == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action_lieu=='creer' || $action_lieu=='enregistrer')
        { 
            if(isset($_POST['libcourtlieu']) && $_POST['libcourtlieu']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
            // on enregistre d'abord nouveau lieu
            if($action_lieu=='creer')
            { 
                //$Matchfound = "";
                $rs=mysql_query("select max(codelieu) as maxcodelieu from lieu");
                if (isset ($_POST["libcourtlieu"]))
                {
                    $query = "SELECT * FROM lieu WHERE libcourtlieu = ".GetSQLValueString($_POST['libcourtlieu'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                }
                
                if (mysql_num_rows($result) != 0)
                {
                    ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                }
                else
                {
                    $row_rs=mysql_fetch_assoc($rs);
                    $codelieu=str_pad((string)((int)$row_rs['maxcodelieu']+1), 2, "0", STR_PAD_LEFT);  
                    
                    $result = mysql_query("insert into lieu (codelieu,date_deb,date_fin,libcourtlieu,liblonglieu,lienlieuhttp,adresselieu) ".
									" values (".GetSQLValueString($codelieu, "text").",'','',"
                                                                                    .GetSQLValueString($_POST['libcourtlieu'], "text").","
                                                                                    .GetSQLValueString($_POST['liblonglieu'], "text").","
                                                                                    .GetSQLValueString($_POST['lienlieuhttp'], "text").","
                                                                                    .GetSQLValueString($_POST['adresselieu'], "text").")") or die(mysql_error());
                                        
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

            else if($action_lieu=='enregistrer')
            {
                if (isset ($_POST["libcourtlieu"]))
                {
                    $query = "SELECT * FROM lieu WHERE libcourtlieu = ".GetSQLValueString($_POST['libcourtlieu'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                
                    $result_fetch_assoc = mysql_fetch_assoc($result);
                    if ($result_fetch_assoc['codelieu'] == $codelieu)
                    {
                        mysql_query("update lieu set libcourtlieu=".GetSQLValueString($_POST['libcourtlieu'], "text").", "
                                                . "liblonglieu=".GetSQLValueString($_POST['liblonglieu'], "text").", "
                                                . "lienlieuhttp=".GetSQLValueString($_POST['lienlieuhttp'], "text")." where codelieu=".GetSQLValueString($codelieu, "text")) or die(mysql_error());

                    }    
                    else
                    {
                        if (mysql_num_rows($result) != 0)
                        {
                            ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                        }
                        else
                        {
                            mysql_query("update lieu set libcourtlieu=".GetSQLValueString($_POST['libcourtlieu'], "text").", "
                                                . "liblonglieu=".GetSQLValueString($_POST['liblonglieu'], "text").", "
                                                . "lienlieuhttp=".GetSQLValueString($_POST['lienlieuhttp'], "text")." where codelieu=".GetSQLValueString($codelieu, "text")) or die(mysql_error());
                        }
                    }
                }
                
            }
            else if($action_lieu=='supprimer')

            {	
                mysql_query("delete from lieu  where codelieu=".GetSQLValueString($codelieu, "text")) or die(mysql_error());

            }
        }
    }
}

if ((empty($_POST['codelieu']) || isset($_POST['codelieu']) == "codelieu") && $action_lieu !="modifier") 
{
    $query_rs_lieu = "SELECT codelieu,libcourtlieu,liblonglieu,lienlieuhttp,adresselieu FROM lieu ORDER BY codelieu";
    $rs_lieu = mysql_query($query_rs_lieu) or die(mysql_error());
    
    //$tab_lieu = array();
    
    while($row_rs_lieu=mysql_fetch_assoc($rs_lieu))
    {   
        $table = array();
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%codelieu%' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $codelieu = $row_rs_lieu['codelieu'];
            if ($codelieu =="")
            {
                break;
            }
            else if ($table =="lieu")
            {
                
            }
            else
            {
                $query_rs = "SELECT * FROM $table WHERE $colonne = ".GetSQLValueString($codelieu, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);
               
                if ($row_rs > 0)
                {
                    $tab_lieu[$row_rs_lieu['codelieu']]=$row_rs_lieu;
                    $tab_lieu[$row_rs_lieu['codelieu']]['supprimable']=false;//par defaut 
                    $tab_lieu[$row_rs_lieu['codelieu']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_lieu[$row_rs_lieu['codelieu']]=$row_rs_lieu;
                    $tab_lieu[$row_rs_lieu['codelieu']]['supprimable']=true;//par defaut 
                    $tab_lieu[$row_rs_lieu['codelieu']]['droitmodif']=$droitmodif;
                }
            }
            //$i++;

        }
    }
    $_SESSION['tab_lieu'] = $tab_lieu;
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
<!--GESTION LIEU-->
<tables border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
    <tr>
        <td>&nbsp;</td>
    </tr>
    
    <tr>
        <td>
            <form name="<?php echo $form_gestionlieu ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codelieu" value="codelieu">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr class="head">
                    <td colspan="7" align="center"><span class="mauvegrascalibri11">Lieu</span></td>
                </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
                <tr class="head">
                  <td class="bleugrascalibri11">Code lieu</td>
                  <td class="bleugrascalibri11">Lib. court lieu</td>
                  <td class="bleugrascalibri11">Lib. long lieu</td>
                  <td class="bleugrascalibri11">lien http</td>
                  <td class="bleugrascalibri11">Adresse</td>
                  <td nowrap class="bleugrascalibri10">Action</td>
                </tr>
                <?php 
                $class="even";
                foreach($_SESSION['tab_lieu'] as $un_codelieu=> $row_rs_lieu)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <?php

                    if ($action_lieu=='modifier')
                    {
//                        codelieu
                        ?>
                        <td nowrap align="left">
                        <?php

                        echo $row_rs_lieu['codelieu'];
                        ?>
                        </td>
<!--                        libcourtlieu-->
                        <td nowrap>
                        <?php
                        if ($row_rs_lieu['codelieu']==$codelieu)
                        {
                            ?>
                            <input type="text" name="libcourtlieu" value="<?php echo $row_rs_lieu['libcourtlieu'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_lieu['libcourtlieu'];
                        }?>                                
                        </td>
<!--                        liblonglieu-->
                        <td nowrap>
                        <?php
                        if ($row_rs_lieu['codelieu']==$codelieu)
                        {
                            ?>
                            <input type="text" name="liblonglieu" value="<?php echo $row_rs_lieu['liblonglieu'] ?>" class="noircalibri10" size="100" maxsize="120">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_lieu['liblonglieu'];
                        }?>                                
                        </td>
<!--                        lien http -->
                        <td nowrap>
                        <?php
                        if ($row_rs_lieu['codelieu']==$codelieu)
                        {
                            ?>
                            <input type="text" name="lienlieuhttp" value="<?php echo $row_rs_lieu['lienlieuhttp'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_lieu['lienlieuhttp'];
                        }?>                                
                        </td>
<!--                        adresselieu-->
                        <td nowrap>
                        <?php
                        if ($row_rs_lieu['codelieu']==$codelieu)
                        {
                            ?>
                            <input type="text" name="adresselieu" value="<?php echo $row_rs_lieu['adresselieu'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_lieu['adresselieu'];
                        }?>                                
                        </td>
                        <!-- Action -->
                        
                        <?php
                        if($row_rs_lieu['codelieu']==$codelieu)
                        {
                            ?>
                            <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_lieu['codelieu'] ?>###lieu####" src="images/b_enregistrer.png">
                            </td>
                            <td><input type="image" name="submit#annuler##<?php echo $row_rs_lieu['codelieu'] ?>###lieu####" src="images/b_drop.png">
                            </td>
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
                        <td class="noircalibri10"><?php echo $row_rs_lieu['codelieu']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_lieu['libcourtlieu']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_lieu['liblonglieu']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_lieu['lienlieuhttp']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_lieu['adresselieu']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr><?php
                                    if ($row_rs_lieu['droitmodif'])
                                    {
                                            ?>
                                        <td><input type="image" name="submit#modifier##<?php echo $row_rs_lieu['codelieu'] ?>###lieu####" src="images/b_edit.png">
                                        </td>
                                        <td>
                                            <?php
                                            if ($row_rs_lieu['supprimable'])
                                            {
                                                ?>
    <!--                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_lieu['codelieu'] ?>###" src="images/b_drop.png" onClick="alert('Pas opeacute;rationel');return false;">-->
                                                <input type="image" name="submit#supprimer##<?php echo $row_rs_lieu['codelieu'] ?>###lieu####" src="images/b_drop.png">
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
                        <?php
                    }?>
                    </tr>

                <?php
                }
                if ($action_lieu!='modifier')
                {
                    if ($row_rs_lieu['droitmodif'])
                    {?>
                        <tr>
                        <td></td>
                        <td><input type="text" name="libcourtlieu" value="" class="noircalibri10" size="20" maxsize="20"></td>        
                        <td><input type="text" name="liblonglieu" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="lienlieuhttp" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="adresselieu" value="" class="noircalibri10" size="20" maxsize="20"></td>
                        <td><input type="image" name="submit#creer##lieu###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()"></td>
                        </tr>
                        <?php
                    }
                    else 
                    {?>
                        &nbsp;
                    <?php
                    }
                }?>
            </table>
            </form>
        </td>
    </tr>
</table>
</body>
</html>
