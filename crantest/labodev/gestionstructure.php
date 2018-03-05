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
$form_gestionstructure = "form_gestionstructure";
//Modification STRUCTURE

$action_structure = '';

if (isset($_POST['codestructure']) && $_POST['codestructure'] == "codestructure")
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
                    if ($codification == "structure")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codestructure=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_structure=substr($submit,$posdiese+1);
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
                        if ($codification == "structure")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_structure=substr($submit,$posdiese+1);
                            }
                        }
                        else
                            break;
                    }
                }
            }
        }
    }

    
    // Traitement de l'action demand�e dans le POST
    if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
    {    
        if ($action_structure == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action_structure=='creer' || $action_structure=='enregistrer')
        { 
            if(isset($_POST['codelib']) && $_POST['codelib']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
                // on enregistre d'abord une nouvelle structure
            if($action_structure=='creer')
            { 
                $rs=mysql_query("select max(codestructure) as maxcodestructure from structure");
                if (isset ($_POST["codelib"]))
                {
                    
                    $query = "SELECT * FROM structure WHERE codelib = ".GetSQLValueString($_POST['codelib'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                }
                                
                if (mysql_num_rows($result) != 0)
                {
                    ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                }
                else
                {

                    $row_rs=mysql_fetch_assoc($rs);
                    $codestructure=str_pad((string)((int)$row_rs['maxcodestructure']+1), 2, "0", STR_PAD_LEFT);  
                    
                    $result = mysql_query("insert into structure (codestructure,codepere,date_deb,date_fin,codelib,libcourt_fr,liblong_fr,libcourt_en,liblong_en,esttheme,estequipe) ".
									" values (".GetSQLValueString($codestructure, "text").",'',"
                                                                                    .GetSQLValueString($_POST['date_deb'], "text").","
                                                                                    .GetSQLValueString($_POST['date_fin'], "text").","
                                                                                    .GetSQLValueString($_POST['codelib'], "text").","
                                                                                    .GetSQLValueString($_POST['libcourt_fr'], "text").","
                                                                                    .GetSQLValueString($_POST['liblong_fr'], "text").","
                                                                                    .GetSQLValueString($_POST['libcourt_en'], "text").","
                                                                                    .GetSQLValueString($_POST['liblong_en'], "text").","
                                                                                    .GetSQLValueString($_POST['esttheme'], "text").","
                                                                                    .GetSQLValueString($_POST['estequipe'], "text").")") or die(mysql_error());
                    // Vérification du résultat
                    // Ceci montre la requête envoyée à MySQL ainsi que l'erreur. Utile pour déboguer.
                    if (!$result) {
                        $message  = 'Requete invalide : ' . mysql_error() . "\n";
                        $message .= 'Requete complete : ' . $query;
                        die($message);
                    }                                                 
                }
            }
            else if($action_structure=='enregistrer')
            {	
                if (isset ($_POST["libcourt_fr"]))
                {
                    
                    $query = "SELECT * FROM structure WHERE codelib = ".GetSQLValueString($_POST['codelib'], "text");
                    $result = mysql_query($query) or die(mysql_error());
                    
                    $result_fetch_assoc = mysql_fetch_assoc($result);
                    if ($result_fetch_assoc['codestructure'] == $codestructure)
                    {
                        mysql_query("update structure set date_deb=".GetSQLValueString($_POST['date_deb'], "text").", "
                                                            . "date_fin=".GetSQLValueString($_POST['date_fin'], "text").", "
                                                            . "codelib=".GetSQLValueString($_POST['codelib'], "text").", "
                                                            . "date_fin=".GetSQLValueString($_POST['date_fin'], "text").", "
                                                            . "codelib=".GetSQLValueString($_POST['codelib'], "text").", "
                                                            . "libcourt_fr=".GetSQLValueString($_POST['libcourt_fr'], "text").", "
                                                            . "liblong_fr=".GetSQLValueString($_POST['liblong_fr'], "text").", "
                                                            . "libcourt_en=".GetSQLValueString($_POST['libcourt_en'], "text").", "
                                                            . "liblong_en=".GetSQLValueString($_POST['liblong_en'], "text").", "
                                                            . "esttheme=".GetSQLValueString($_POST['esttheme'], "text").", "
                                                            . "estequipe=".GetSQLValueString($_POST['estequipe'], "text")."
                                                            where codestructure=".GetSQLValueString($codestructure, "text")) or die(mysql_error());
                    }
                    else
                    {
                        if (mysql_num_rows($result) != 0)
                        {
                            ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                        }
                        else
                        {
                            mysql_query("update structure set date_deb=".GetSQLValueString($_POST['date_deb'], "text").", "
                                                                . "date_fin=".GetSQLValueString($_POST['date_fin'], "text").", "
                                                                . "codelib=".GetSQLValueString($_POST['codelib'], "text").", "
                                                                . "date_fin=".GetSQLValueString($_POST['date_fin'], "text").", "
                                                                . "codelib=".GetSQLValueString($_POST['codelib'], "text").", "
                                                                . "libcourt_fr=".GetSQLValueString($_POST['libcourt_fr'], "text").", "
                                                                . "liblong_fr=".GetSQLValueString($_POST['liblong_fr'], "text").", "
                                                                . "libcourt_en=".GetSQLValueString($_POST['libcourt_en'], "text").", "
                                                                . "liblong_en=".GetSQLValueString($_POST['liblong_en'], "text").", "
                                                                . "esttheme=".GetSQLValueString($_POST['esttheme'], "text").", "
                                                                . "estequipe=".GetSQLValueString($_POST['estequipe'], "text")."
                                                                where codestructure=".GetSQLValueString($codestructure, "text")) or die(mysql_error());

                        }
                    }
                }
            }
            else if($action_structure=='supprimer')
            {
                mysql_query("delete from structure  where codestructure=".GetSQLValueString($codestructure, "text")) or die(mysql_error());
            }
        }
    }
}

if ((empty($_POST['codestructure']) || isset($_POST['codestructure']) == "codestructure")&& $action_structure !="modifier")
{
    $query_rs_structure = "SELECT codestructure,date_deb,date_fin,codelib,libcourt_fr,liblong_fr,libcourt_en,liblong_en,esttheme,estequipe FROM structure ORDER BY codestructure";
    $rs_structure = mysql_query($query_rs_structure) or die(mysql_error());

    //$tab_structure=array();
    while($row_rs_structure=mysql_fetch_assoc($rs_structure))
    {   
        $table = array();
        $colonne = array();
        //$request = "SELECT COLUMN_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = 'labodev' AND COLUMN_NAME LIKE '%structure%' OR COLUMN_NAME LIKE '%theme%' OR COLUMN_NAME LIKE '%equipe%'";
        $request = "SELECT DISTINCT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name LIKE '%theme' AND TABLE_SCHEMA='labodev' OR column_name LIKE '%equipe' AND TABLE_SCHEMA='labodev' OR column_name LIKE '%structure' AND TABLE_SCHEMA='labodev'";
        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['TABLE_NAME'];
            $colonne = $row_rs_request['COLUMN_NAME'];
            $codestructure = $row_rs_structure['codestructure'];
            if ($codestructure =="")
            {
                break;
            }
            
            else if ($table =="structure")
            {
                
            }
            
            else
            {
                
                $query_rs = "SELECT * FROM $table WHERE $colonne =".GetSQLValueString($codestructure, "text");
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);

                if ($row_rs > 0)
                {
                    $tab_structure[$row_rs_structure['codestructure']] = $row_rs_structure;
                    $tab_structure[$row_rs_structure['codestructure']]['supprimable']=false;//par defaut 
                    $tab_structure[$row_rs_structure['codestructure']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_structure[$row_rs_structure['codestructure']] = $row_rs_structure;
                    $tab_structure[$row_rs_structure['codestructure']]['supprimable']=true;//par defaut
                    $tab_structure[$row_rs_structure['codestructure']]['droitmodif']=$droitmodif;
                }
            }
        }       
    }
    $_SESSION['tab_structure'] = $tab_structure;
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
                    <!--GESTION STRUCTURE-->
<tables border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
        
    <tr>
        <td>
            <form name="<?php echo $form_gestionstructure ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codestructure" value="codestructure">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">Structure</span></td>
                </tr>
            </table>
                
            <table border="0" align="center" cellpadding="3" cellspacing="1">
            <tr>
                <td></td>
            </tr>
            
                <tr class="head">
                <td class="bleugrascalibri10">Code structure</td>
                <td class="bleugrascalibri10">Code libell&eacute;</td>
                <td class="bleugrascalibri10">Lib. court fran&ccedil;ais</td>
                <td class="bleugrascalibri10">Lib. long fran&ccedil;ais</td>
                <td class="bleugrascalibri10">Lib. court anglais</td>
                <td class="bleugrascalibri10">Lib. long anglais</td>
                <td class="bleugrascalibri10">Theme</td>
                <td class="bleugrascalibri10">Equipe</td>
<!--                    <td class="bleugrascalibri10">Nom</td>
                <td class="bleugrascalibri10">Responsable</td>-->
                <td class="bleugrascalibri10">Date deb</td>
                <td class="bleugrascalibri10">Date fin</td>
                <td nowrap class="bleugrascalibri10">Action</td
           ></tr>
            <?php $class="even"; 
            foreach($_SESSION['tab_structure'] as $un_codestructure=> $row_rs_structure)
            {
                ?>
                <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                <?php

                if ($action_structure=='modifier')
                {
//                        codestructure
                    ?>
                    <td nowrap align="left">
                    <?php

                    echo $row_rs_structure['codestructure'];
                    ?>
                    </td>
<!--                        codelib-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="codelib" value="<?php echo $row_rs_structure['codelib'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['codelib'];
                    }?>                                
                    </td>
<!--                        libcourt_fr-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="libcourt_fr" value="<?php echo $row_rs_structure['libcourt_fr'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['libcourt_fr'];
                    }?>                                
                    </td>
<!--                        liblong_fr-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="liblong_fr" value="<?php echo $row_rs_structure['liblong_fr'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['liblong_fr'];
                    }?>                                
                    </td>
<!--                        libcourt_en-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="libcourt_en" value="<?php echo $row_rs_structure['libcourt_en'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['libcourt_en'];
                    }?>                                
                    </td>
<!--                        liblong_en-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="liblong_en" value="<?php echo $row_rs_structure['liblong_en'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['liblong_en'];
                    }?>                                
                    </td>
<!--                        esttheme-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="esttheme" value="<?php echo $row_rs_structure['esttheme'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['esttheme'];
                    }?>                                
                    </td>
<!--                        Equipe-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="estequipe" value="<?php echo $row_rs_structure['estequipe'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['estequipe'];
                    }?>                                
                    </td>
<!--                        date_deb-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="date_deb" value="<?php echo $row_rs_structure['date_deb'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['date_deb'];
                    }?>                                
                    </td>
<!--                        date_fin-->
                    <td nowrap>
                    <?php
                    if ($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <input type="text" name="date_fin" value="<?php echo $row_rs_structure['date_fin'] ?>" class="noircalibri10" size="20" maxsize="20">
                        <?php
                    }
                    else
                    {
                        echo $row_rs_structure['date_fin'];
                    }?>                                
                    </td>
                    <!-- Action -->
                    <td>
                    <?php
                    if($row_rs_structure['codestructure']==$codestructure)
                    {
                        ?>
                        <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_structure['codestructure'] ?>###structure####" src="images/b_enregistrer.png">
                        </td>
                        <td><input type="image" name="submit#annuler##<?php echo $row_rs_structure['codestructure'] ?>###structure####" src="images/b_drop.png">
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
                    <td class="noircalibri10"><?php echo $row_rs_structure['codestructure']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['codelib']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['libcourt_fr']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['liblong_fr']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['libcourt_en']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['liblong_en']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['esttheme']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['estequipe']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['date_deb']; ?></td>
                    <td class="noircalibri10"><?php echo $row_rs_structure['date_fin']; ?></td>
                    <td nowrap align="left">
                        <table>
                            <tr>
                                <td><input type="image" name="submit#modifier##<?php echo $row_rs_structure['codestructure'] ?>###structure####" src="images/b_edit.png">
                                </td>
                                <td>
                                    <?php
                                    if ($row_rs_structure['supprimable'])
                                    {
                                        ?>
<!--                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_structure['codestructure'] ?>###" src="images/b_drop.png" onClick="alert('Pas opeacute;rationel');return false;">-->
                                        <input type="image" name="submit#supprimer##<?php echo $row_rs_structure['codestructure'] ?>###structure####" src="images/b_drop.png">
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
            if ($action_structure!='modifier')
            {?>

                <tr>
                    <?php $codelieu = $row_rs_structure['codestructure'] + 1;
                    ?>
                    <td></td>
                    <td><input type="text" name="codelib" value="" class="noircalibri10" size="20" maxsize="20"></td>        
                    <td><input type="text" name="libcourt_fr" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                    <td><input type="text" name="liblong_fr" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                    <td><input type="text" name="libcourt_en" value="" class="noircalibri10" size="20" maxsize="20"></td>
                    <td><input type="text" name="liblong_en" value="" class="noircalibri10" size="20" maxsize="20"></td>        
                    <td><input type="text" name="esttheme" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                    <td><input type="text" name="estequipe" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                    <td><input type="text" name="date_deb" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                    <td><input type="text" name="date_fin" value="" class="noircalibri10" size="20" maxsize="20"></td>
                    <td><input type="image" name="submit#creer##structure###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()"></td>
                </tr>
                <?php
            }?>
            </table>
            </form>
	  </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
</table>
</body>
</html>
