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
$form_gestionobjectifscientifique = "form_gestionobjectifscientifique";

//Modification LIEU

$action_objectifscientifique = '';

if (isset($_POST['codeobjectifscientifique']) && $_POST['codeobjectifscientifique'] == "codeobjectifscientifique")
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
                    if ($codification == "objectifscientifique")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codeobjectifscientifique=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_objectifscientifique=substr($submit,$posdiese+1);
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
                        if ($codification == "objectifscientifique")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_objectifscientifique=substr($submit,$posdiese+1);
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
        if ($action_objectifscientifique == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action_objectifscientifique=='creer' || $action_objectifscientifique=='enregistrer')
        { 
            if(isset($_POST['liblongobjectifscientifique']) && $_POST['liblongobjectifscientifique']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
            // on enregistre d'abord nouveau objectifscientifique
            if($action_objectifscientifique=='creer')
            { 
                //$Matchfound = "";
                $rs=mysql_query("select max(codeobjectifscientifique) as maxcodeobjectifscientifique from sujet_objectifscientifique");
                if (isset ($_POST["liblongobjectifscientifique"]))
                {
                    $query = "SELECT * FROM sujet_objectifscientifique WHERE liblongobjectifscientifique like ".GetSQLValueString($_POST['liblongobjectifscientifique'], "text")."";
                    $result = mysql_query($query) or die(mysql_error());  
                }
                
                if (mysql_num_rows($result) != 0)
                {
                    ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                }
                else
                {
                    $row_rs=mysql_fetch_assoc($rs);
                    $codeobjectifscientifique=str_pad((string)((int)$row_rs['maxcodeobjectifscientifique']+1), 2, "0", STR_PAD_LEFT);  
                    
                    $result = mysql_query("insert into sujet_objectifscientifique (codeobjectifscientifique,codedomainescientifique,liblongobjectifscientifique,libfsdobjectifscientifique,numordre) ".
									" values (".GetSQLValueString($codeobjectifscientifique, "text").","
                                                                                    .GetSQLValueString($_POST['codedomainescientifique'], "text").","
                                                                                    .GetSQLValueString($_POST['liblongobjectifscientifique'], "text").",'','')") or die(mysql_error());
                                        
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

            else if($action_objectifscientifique=='enregistrer')
            {	
                 if (isset ($_POST["liblongobjectifscientifique"]))
                {
                    
                    $query = "SELECT * FROM sujet_objectifscientifique WHERE liblongobjectifscientifique like ".GetSQLValueString($_POST['liblongobjectifscientifique'], "text")."";
                    $result = mysql_query($query) or die(mysql_error());
            
                    if (mysql_num_rows($result) != 0)
                    {
                        ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                    }
                    else
                    {
                        mysql_query("update sujet_objectifscientifique set codedomainescientifique=".GetSQLValueString($_POST['codedomainescientifique'], "text").", "
                                                                                                    . "liblongobjectifscientifique=".GetSQLValueString($_POST['liblongobjectifscientifique'], "text")." where codeobjectifscientifique=".GetSQLValueString($codeobjectifscientifique, "text")) or die(mysql_error());
                    }
                }
            }
            else if($action_objectifscientifique=='supprimer')

            {	
                mysql_query("delete from sujet_objectifscientifique  where codeobjectifscientifique=".GetSQLValueString($codeobjectifscientifique, "text")) or die(mysql_error());

            }
        }
    }
}

if ((empty($_POST['codeobjectifscientifique']) || isset($_POST['codeobjectifscientifique']) == "codeobjectifscientifique") && $action_objectifscientifique !="modifier") 
{
    $query_rs_objectifscientifique = "SELECT codeobjectifscientifique,codedomainescientifique,liblongobjectifscientifique FROM sujet_objectifscientifique ORDER BY numordre";
    $rs_objectifscientifique = mysql_query($query_rs_objectifscientifique) or die(mysql_error());
    
    //$tab_objectifscientifique = array();
    
    while($row_rs_objectifscientifique=mysql_fetch_assoc($rs_objectifscientifique))
    {   
        $table = array();
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%codeobjectifscientifique%' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $codeobjectifscientifique = $row_rs_objectifscientifique['codeobjectifscientifique'];
            if ($codeobjectifscientifique =="")
            {
                break;
            }
            else if ($table =="sujet_objectifscientifique")
            {
                
            }
            else
            {
                $query_rs = "SELECT * FROM $table WHERE $colonne =".GetSQLValueString($codeobjectifscientifique, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);
               
                if ($row_rs > 0)
                {
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]=$row_rs_objectifscientifique;
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]['supprimable']=false;//par defaut 
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]=$row_rs_objectifscientifique;
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]['supprimable']=true;//par defaut 
                    $tab_objectifscientifique[$row_rs_objectifscientifique['codeobjectifscientifique']]['droitmodif']=$droitmodif;
                }
            }
            //$i++;

        }
    }
    $_SESSION['tab_objectifscientifique'] = $tab_objectifscientifique;
}
    //DOMAINE SCIENTIFIQUE
$query_rs_domainescientifique = "SELECT codedomainescientifique,liblongdomainescientifique FROM sujet_domainescientifique ORDER BY codedomainescientifique";
$rs_domainescientifique = mysql_query($query_rs_domainescientifique) or die(mysql_error());
while ($row_rs_domainescientifique = mysql_fetch_array($rs_domainescientifique))
{
    $tab_domainescientifique[$row_rs_domainescientifique['codedomainescientifique']]=$row_rs_domainescientifique['codedomainescientifique'];

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
            <form name="<?php echo $form_gestionobjectifscientifique ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codeobjectifscientifique" value="codeobjectifscientifique">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr class="head">
                    <td colspan="7" align="center"><span class="mauvegrascalibri11">Activit&#233;s scientifiques</span></td>
                </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr>
                    <td></td>
                </tr>
                <tr class="head">
                  <td class="bleugrascalibri11">Code activit&#233;</td>
                  <td class="bleugrascalibri11">Code domaine</td>
                  <td class="bleugrascalibri11">Lib. activit&#233;</td>
                  <td nowrap class="bleugrascalibri10">Action</td
                ></tr>
                <?php 
                $class="even";
                foreach($_SESSION['tab_objectifscientifique'] as $un_codeobjectifscientifique=> $row_rs_objectifscientifique)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <?php

                    if ($action_objectifscientifique=='modifier')
                    {
//                        codeobjectifscientifique
                        ?>
                        <td nowrap align="left">
                        <?php

                        echo $row_rs_objectifscientifique['codeobjectifscientifique'];
                        ?>
                        </td>
<!--                        codedomaineescientifique-->
                        <td nowrap>
                            <?php
                        if ($row_rs_objectifscientifique['codeobjectifscientifique']==$codeobjectifscientifique)
                        {
                            ?>
                            <select name="codedomainescientifique" type="text">
                                <?php foreach ($tab_domainescientifique as $un_codedomainescientifique=>$codedomainescientifique)
                                {
                                    ?><option value="<?php echo $codedomainescientifique ?>" <?php echo $codedomainescientifique == $row_rs_objectifscientifique['codedomainescientifique']?'selected':'' ?>> <?php echo $codedomainescientifique ?></option>
                                    <?php
                                }?>
                            </select>
                            <?php
                        }
                        else
                            echo $row_rs_objectifscientifique['codedomainescientifique'];
                        ?>                        
                        </td>
<!--                        liblongobjectifscientifique-->
                        <td nowrap>
                        <?php
                        if ($row_rs_objectifscientifique['codeobjectifscientifique']==$codeobjectifscientifique)
                        {
                            ?>
                            <input type="text" name="liblongobjectifscientifique" value="<?php echo $row_rs_objectifscientifique['liblongobjectifscientifique'] ?>" class="noircalibri10" size="100" maxsize="120">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_objectifscientifique['liblongobjectifscientifique'];
                        }?>                                
                        </td>
<!--                        
                        <!-- Action -->
                        
                        <?php
                        if($row_rs_objectifscientifique['codeobjectifscientifique']==$codeobjectifscientifique)
                        {
                            ?>
                            <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_objectifscientifique['codeobjectifscientifique'] ?>###objectifscientifique####" src="images/b_enregistrer.png">
                            </td>
                            <td><input type="image" name="submit#annuler##<?php echo $row_rs_objectifscientifique['codeobjectifscientifique'] ?>###objectifscientifique####" src="images/b_drop.png">
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
                        <td class="noircalibri10"><?php echo $row_rs_objectifscientifique['codeobjectifscientifique']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_objectifscientifique['codedomainescientifique']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_objectifscientifique['liblongobjectifscientifique']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr><?php
                                    if ($row_rs_objectifscientifique['droitmodif'])
                                    {
                                            ?>
                                        <td><input type="image" name="submit#modifier##<?php echo $row_rs_objectifscientifique['codeobjectifscientifique'] ?>###objectifscientifique####" src="images/b_edit.png">
                                        </td>
                                        <td>
                                            <?php
                                            if ($row_rs_objectifscientifique['supprimable'])
                                            {
                                                ?>
    <!--                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_objectifscientifique['codeobjectifscientifique'] ?>###" src="images/b_drop.png" onClick="alert('Pas opeacute;rationel');return false;">-->
                                                <input type="image" name="submit#supprimer##<?php echo $row_rs_objectifscientifique['codeobjectifscientifique'] ?>###objectifscientifique####" src="images/b_drop.png">
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
                if ($action_objectifscientifique!='modifier')
                {
                    if ($row_rs_objectifscientifique['droitmodif'])
                    {?>
                        <tr>
                        <td></td>
                        <td>
                            <select name="codedomainescientifique" type="text">
                                <?php foreach ($tab_domainescientifique as $un_codedomainescientifique=>$codedomainescientifique)
                                {
                                    ?><option value="<?php echo $codedomainescientifique ?>" <?php echo $codedomainescientifique == $row_rs_objectifscientifique['codedomainescientifique']?'selected':'' ?>> <?php echo $codedomainescientifique ?></option>
                                    <?php
                                }?>
                            </select>
                        </td>
<!--                        <td><input type="text" name="codedomainescientifique" value="" class="noircalibri10" size="20" maxsize="20"></td>        -->
                        <td><input type="text" name="liblongobjectifscientifique" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="image" name="submit#creer##objectifscientifique###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()"></td>
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
