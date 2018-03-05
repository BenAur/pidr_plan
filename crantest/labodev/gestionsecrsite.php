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
$form_secrsite = "form_secrsite";
//Modification LIEU

$action_secrsite = '';

if (isset($_POST['codesecrsite']) && $_POST['codesecrsite'] == "codesecrsite")
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
                    if ($codification == "secrsite")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codesecrsite=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_secrsite=substr($submit,$posdiese+1);
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
                        if ($codification == "secrsite")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_secrsite=substr($submit,$posdiese+1);
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
    {    if($action_secrsite=='creer' || $action_secrsite=='enregistrer')
        { 
            if(isset($_POST['codesecrsite']) && $_POST['codesecrsite']=='')
            { 
                $erreur='Libell&eacute; vide';
            }
        }
        if($erreur=='')
        { $affiche_succes=true;
                // on enregistre d'abord nouveau lieu
                if($action_secrsite=='creer')
                { 
                    $rs=mysql_query("select max(codesecrsite) as maxcodesecrsite from secrsite");
                    if (isset ($_POST["libcourtlieu"]))
                    {
                        $query = "SELECT * FROM lieu WHERE libcourtlieu = ".GetSQLValueString($_POST['libcourtlieu'], "text")."";
                        $result = mysql_query($query) or die(mysql_error());   
                    }
                    if (mysql_num_rows($result) != 0)
                    {
                        ?><script>alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>")</script><?php
                    }
                    else
                    {
                        $row_rs=mysql_fetch_assoc($rs);
                        $codesecrsite=str_pad((string)((int)$row_rs['maxcodesecrsite']+1), 2, "0", STR_PAD_LEFT);  

                        $result = mysql_query("insert into secrsite (codesite,codesecrsite) ".
                                                                            " values (".GetSQLValueString($codesite, "text").","
                                                                                        .GetSQLValueString($_POST['codesecrsite'], "text").")") or die(mysql_error());

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
                else if($action_secrsite=='enregistrer')
                {	
                mysql_query("update secresite set codesecrsite=". GetSQLValueString($_POST['codesecrsite'], "text")." where codesecresite=".GetSQLValueString($codesecrsite, "text")) or die(mysql_error());
                }
                else if($action_secrsite=='supprimer')

                {	
                    mysql_query("delete from secresite  where codesecrsite=".GetSQLValueString($codesecrsite, "text")) or die(mysql_error());

                }
            }                  
        }
    }


if ((empty($_POST['codesecrsite']) || isset($_POST['codesecrsite']) == "codesecrsite") && $action_secrsite !="modifier")
{
    $query_rs_secrsite = "SELECT codesecrsite,lieu.codelieu,libcourtlieu,nom,prenom FROM secrsite,lieu,individu".
										" WHERE secrsite.codesecrsite=individu.codeindividu and secrsite.codesite=lieu.codelieu".
										"  order by lieu.codelieu,nom";
    $rs_secrsite = mysql_query($query_rs_secrsite) or die(mysql_error());

    //$tab_structure=array();
    while($row_rs_secrsite=mysql_fetch_assoc($rs_secrsite))
    {   
        $table = array();
        $request = "select table_name from information_schema.columns where table_schema='labodev' and column_name = 'codesecrsite'";

        $rs_request = mysql_query($request) or die(mysql_error());
        while($row_rs_request=mysql_fetch_assoc($rs_request)) 
        {
            $table = $row_rs_request['table_name'];
            $codesecrsite = $row_rs_secrsite['codesecrsite'];
            if ($codesecrsite =="")
            {
                break;
            }
            
            else if ($table =="secrsite")
            {
                
            }
            
            else
            {
                $query_rs = "SELECT * FROM $table WHERE codesecrsite = $codesecrsite";
                $rs = mysql_query($query_rs) or die(mysql_error());
                $row_rs = mysql_num_rows($rs);

                if ($row_rs > 0)
                {
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']] = $row_rs_secrsite;
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']]['supprimable']=false;//par defaut 
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']]['droitmodif']=$droitmodif;
                    break;
                }
                else 
                {
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']] = $row_rs_secrsite;
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']]['supprimable']=true;//par defaut
                    $tab_secrsite[$row_rs_secrsite['codesecrsite']]['droitmodif']=$droitmodif;
                }
            }
        }       
    }
    $_SESSION['tab_secrsite'] = $tab_secrsite;
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
                        <!--GESTION DES SECRETAIRES DE SITE-->
<tables border="0" align="center" cellpadding="0" cellspacing="1">    
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
    <tr>
        <td>&nbsp;</td>
    </tr>
    
    <tr>
        <td>
            <form name="<?php echo $form_secrsite ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="codesecrsite" value="codesecrsite">
            <input type="image" src="images/espaceur.gif" width="0" height="0">  
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr class="head">
                    <td align="center"><span class="mauvegrascalibri11">Secr&eacute;tariat de site (lieu)</span></td>
                </tr>
            </table>
            <table border="0" align="center" cellpadding="3" cellspacing="1">
                <tr <tr class="head">
                    <td class="bleugrascalibri10">Code secr site</td>
                    <td class="bleugrascalibri10">Code site (lieu)</td>
                    <td class="bleugrascalibri10">Lib. court fran&ccedil;ais</td>
                    <td class="bleugrascalibri10">Nom</td>
                    <td class="bleugrascalibri10">Prenom</td>
               </tr>
                <?php $class="even"; 
                foreach($_SESSION['tab_secrsite'] as $un_codegestheme=> $row_rs_secrsite)
                {
                    ?>
                    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                    <?php

                    if ($action_secrsite=='modifier')
                    {
//                        codestructure
                        ?>
                        <td nowrap align="left">
                        <?php

                        echo $row_rs_secrsite['codesecrsite'];
                        ?>
                        </td>
<!--                        codelib-->
                        <td nowrap>
                        <?php
                        if ($row_rs_secrsite['codesecrsite']==$codesecrsite)
                        {
                            ?>
                            <input type="text" name="codelieu" value="<?php echo $row_rs_secrsite['codelieu'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_secrsite['codelieu'];
                        }?>                                
                        </td>
<!--                        libcourt_fr-->
                        <td nowrap>
                        <?php
                        if ($row_rs_secrsite['codesecrsite']==$codesecrsite)
                        {
                            ?>
                            <input type="text" name="libcourtlieu" value="<?php echo $row_rs_secrsite['libcourtlieu'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_secrsite['libcourtlieu'];
                        }?>                                
                        </td>
<!--                        liblong_fr-->
                        <td nowrap>
                        <?php
                        if ($row_rs_secrsite['codesecrsite']==$codesecrsite)
                        {
                            ?>
                            <input type="text" name="nom" value="<?php echo $row_rs_secrsite['nom'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_secrsite['nom'];
                        }?>                                
                        </td>
<!--                        libcourt_en-->
                        <td nowrap>
                        <?php
                        if ($row_rs_secrsite['codesecrsite']==$codesecrsite)
                        {
                            ?>
                            <input type="text" name="prenom" value="<?php echo $row_rs_secrsite['prenom'] ?>" class="noircalibri10" size="20" maxsize="20">
                            <?php
                        }
                        else
                        {
                            echo $row_rs_secrsite['prenom'];
                        }?>                                
                        </td>

                        <!-- Action -->
                        <td>
                        <?php
                        if($row_rs_secrsite['codesecrsite']==$codesecrsite)
                        {
                            ?>
                            <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_secrsite['codesecrsite'] ?>###secrsite####" src="images/b_enregistrer.png">
                            </td>
                            <td><input type="image" name="submit#annuler##<?php echo $row_rs_secrsite['codesecrsite'] ?>###secrsite####" src="images/b_drop.png">
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
                        <td class="noircalibri10"><?php echo $row_rs_secrsite['codesecrsite']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_secrsite['codelieu']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_secrsite['libcourtlieu']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_secrsite['nom']; ?></td>
                        <td class="noircalibri10"><?php echo $row_rs_secrsite['prenom']; ?></td>
                        <td nowrap align="left">
                            <table>
                                <tr>
                                    <td><input type="image" name="submit#modifier##<?php echo $row_rs_secrsite['codesecrsite'] ?>###secrsite####" src="images/b_edit.png">
                                    </td>
                                    <td>
                                        <?php
                                        if ($row_rs_secrsite['supprimable'])
                                        {
                                            ?>
<!--                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_secrsite['codesecrsite'] ?>###" src="images/b_drop.png" onClick="alert('Pas opeacute;rationel');return false;">-->
                                            <input type="image" name="submit#supprimer##<?php echo $row_rs_secrsite['codesecrsite'] ?>###secrsite####" src="images/b_drop.png">
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
                if ($action_secrsite!='modifier')
                {?>
                    
                    <tr>
                        <?php $codesecrsite = $row_rs_secrsite['codesecrsite'] + 1;
                        ?>
                        <td></td>
                        <td><input type="text" name="codelieu" value="" class="noircalibri10" size="20" maxsize="20"></td>        
                        <td><input type="text" name="libcourtlieu" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="nom" value="" class="noircalibri10" size="20" maxsize="20"></td> 
                        <td><input type="text" name="prenom" value="" class="noircalibri10" size="20" maxsize="20"></td>
                        <td><input type="image" name="submit#creer##secrsite###" class="noircalibri10" src="images/b_add.png" onclick="transfertTableau()"></td>
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