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

$form_gestionpasse = "form_gestionpasse";
//Modification Gestion des CLES

$action_passe = '';

if (isset($_POST['codepasse']) && $_POST['codepasse'] == "codepasse")
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
                    if ($codification == "passe")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codepasse=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_passe=substr($submit,$posdiese+1);
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
                        if ($codification == "passe")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_passe=substr($submit,$posdiese+1);
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
        if ($action_passe == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action_passe=='creer' || $action_passe=='enregistrer')
        { 
            if(isset($_POST['num_passe']) && $_POST['num_passe']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
            // on enregistre d'abord nouveau lieu
            if($action_passe=='creer')
            { 
                //$Matchfound = "";
                $rs=mysql_query("select max(codepasse) as maxcodepasse from cle_passe");
                if (isset ($_POST["num_passe"]))
                {
                    $query = "SELECT * FROM cle_passe WHERE num_passe = ".GetSQLValueString($_POST['num_passe'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                }
                
                if (mysql_num_rows($result) != 0)
                {
                    ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                }
                else
                {
                    $row_rs=mysql_fetch_assoc($rs);
                    $codepasse=str_pad((string)((int)$row_rs['maxcodepasse']+1), 2, "0", STR_PAD_LEFT);  
                    
                    $result = mysql_query("insert into cle_passe (codepasse,num_passe) "." values (".GetSQLValueString($codepasse, "text").","
                                                                                                    .GetSQLValueString($_POST['num_passe'], "text").")") or die(mysql_error());
                                        
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

            else if($action_passe=='enregistrer')
            {
                if (isset ($_POST["num_passe"]))
                {
                    $query = "SELECT * FROM cle_passe WHERE num_passe = ".GetSQLValueString($_POST['num_passe'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                
                    $result_fetch_assoc = mysql_fetch_assoc($result);
                    if ($result_fetch_assoc['codepasse'] == $codepasse)
                    {
                        mysql_query("update cle_passe set num_passe=".GetSQLValueString($_POST['num_passe'], "text")." where codepasse=".GetSQLValueString($codepasse, "text")) or die(mysql_error());

                    }    
                    else
                    {
                        if (mysql_num_rows($result) != 0)
                        {
                            ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                        }
                        else
                        {
                            mysql_query("update cle_passe set num_passe=".GetSQLValueString($_POST['num_passe'], "text")." where codecle=".GetSQLValueString($codecle, "text")) or die(mysql_error());
                        }
                    }
                }
                
            }
            else if($action_passe=='supprimer')

            {	
                mysql_query("delete from cle_passe  where codepasse=".GetSQLValueString($codepasse, "text")) or die(mysql_error());

            }
        }
    }
}

if ((empty($_POST['codepasse']) || isset($_POST['codepasse']) == "codepasse") && $action_passe !="modifier") 
{
    $query_rs_passe = "SELECT codepasse,num_passe FROM cle_passe ORDER BY codepasse";
    $rs_passe = mysql_query($query_rs_passe) or die(mysql_error());
    
    //$tab_lieu = array();
    
    while($row_rs_passe=mysql_fetch_assoc($rs_passe))
    {   
        $table = array();
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%passe%' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $num_passe = $row_rs_passe['num_passe'];
            if (!isset($num_passe))
            {
                break;
            }
            else if ($table =="cle_passe")
            {
                
            }
            else
            {
                $query_rs = "SELECT * FROM $table WHERE $colonne = ".GetSQLValueString($num_passe, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);
               
                if ($row_rs > 0)
                {
                    $tab_passe[$row_rs_passe['codepasse']]=$row_rs_passe;
                    $tab_passe[$row_rs_passe['codepasse']]['supprimable']=false;//par defaut 
                    $tab_passe[$row_rs_passe['codepasse']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_passe[$row_rs_passe['codepasse']]=$row_rs_passe;
                    $tab_passe[$row_rs_passe['codepasse']]['supprimable']=true;//par defaut 
                    $tab_passe[$row_rs_passe['codepasse']]['droitmodif']=$droitmodif;
                }
            }
            //$i++;

        }
    }
    $_SESSION['tab_passe'] = $tab_passe;
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
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des cles','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
        
    <tr>
        <td>
            <form name="<?php echo $form_gestionpasse ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codepasse" value="codepasse">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">GESTION des PASSES</span></td>
                </tr>
            </table>
                
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
            
            <tr class="head">
                <td class="bleugrascalibri11">Code passe</td>
                <td class="bleugrascalibri11">Num&#233;ro du passe</td>
            </tr>
            <?php 
                $class="even";
                foreach($_SESSION['tab_passe'] as $un_codepasse=> $row_rs_passe)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <?php

                    if ($action_passe=='modifier')
                    {
//                        codepasse
                        ?>
                        <td nowrap align="left">
                        <?php

                        echo $row_rs_passe['codepasse'];
                        ?>
                        </td>
<!--                        num passe-->
                        <td nowrap>
                        <?php
                        if ($row_rs_passe['codepasse']==$codepasse)
                        {
                            ?>
                            <input type="text" name="num_passe" value="<?php echo $row_rs_passe['num_passe'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_passe['num_passe'];
                        }?>                                
                        
                        <!-- Action -->
                        
                        <?php
                        if($row_rs_passe['codepasse']==$codepasse)
                        {
                            ?>
                            <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_passe['codepasse'] ?>###passe####" src="images/b_enregistrer.png">
                            </td>
                            <td><input type="image" name="submit#annuler##<?php echo $row_rs_passe['codepasse'] ?>###passe####" src="images/b_drop.png">
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
                        <td class="noircalibri10"><?php echo $row_rs_passe['codepasse']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_passe['num_passe']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr><?php
                                    if ($row_rs_passe['droitmodif'])
                                    {
                                            ?>
                                        <td><input type="image" name="submit#modifier##<?php echo $row_rs_passe['codepasse'] ?>###passe####" src="images/b_edit.png">
                                        </td>
                                        <td>
                                            <?php
                                            if ($row_rs_passe['supprimable'])
                                            {
                                                ?>
                                                <input type="image" name="submit#supprimer##<?php echo $row_rs_passe['codepasse'] ?>###passe####" src="images/b_drop.png">
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
                if ($action_passe!='modifier')
                {
                    if ($row_rs_passe['droitmodif'])
                    {?>
                        <tr>
                        <td></td>
                        <td><input type="text" name="num_passe" value="" class="noircalibri10" size="20" maxsize="20"></td>
                        <td><input type="image" name="submit#creer##passe###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()"></td>
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