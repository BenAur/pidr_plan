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

//Modification Gestion des CLES

$action_cle = '';
?>

<?php
if (isset($_POST['codecle']) && $_POST['codecle'] == "codecle")
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
                    if ($codification == "cle")
                    {
                        $submit=substr($postkey,0,$pos3diese);
                        $pos2diese=strpos($submit,'##');
                        if($pos2diese!==false)
                        { 
                            $codecle=substr($submit,$pos2diese+2);
                            $submit=substr($submit,0,$pos2diese);
                            $posdiese=strpos($submit,"#");
                            if($posdiese!=false)
                            { 
                                $action_cle=substr($submit,$posdiese+1);
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
                        if ($codification == "cle")
                        {
                            $submit=substr($postkey,0,$pos2diese);
                            $posdiese=strpos($submit,'#');
                            if($posdiese!==false)
                            { 
                                $action_cle=substr($submit,$posdiese+1);
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
    if((isset($_POST["MM_update"])) && ( ($_POST["MM_update"] == "/labodev/gestioncle.php") || ($_POST["MM_update"] == "/labodev/ajout_passe.php"))) 
    {   
        if ($action_cle == 'annuler')
            entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'gestioncodifications.php','texteretour'=>'Retour au gestions de codifications',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser));
        if($action_cle=='creer' || $action_cle=='enregistrer')
        { 
            if(isset($_POST['num_cle']) && $_POST['num_cle']=='')
            { 
                $erreur='Libell&eacute; vide';
                ?>
                <script>
                history.go(-1);
                </script>
                <?php
            }
        }
        if($erreur=='')
        { 
            $affiche_succes=true;
            // on enregistre d'abord nouveau lieu
            if($action_cle=='creer')
            {
                $query_rs_passe = "SELECT codepasse,num_passe FROM cle_passe ORDER BY codepasse";
                $rs_passe = mysql_query($query_rs_passe) or die(mysql_error());
                $rs=mysql_query("select max(codecle) as maxcodecle from cle_standard");
                $resultat = 0;
                if (isset ($_POST['num_cle']) && $action_cle=='creer')
                {
                    $query = "SELECT * FROM cle_standard WHERE num_cle = ".GetSQLValueString($_POST['num_cle'], "text");
                    $result = mysql_query($query) or die(mysql_error());  
                    $resultat = mysql_num_rows($result);
                }
                if ($resultat != 0)
                {?>
                    <script>
                        alert("<?php echo 'Cet enregistrement existe d\351j\340 !!!'; ?>");
                        history.go(-1);
                    </script><?php
                }
                
                else
                {
                    $row_rs=mysql_fetch_assoc($rs);
                    $codecle=str_pad((string)((int)$row_rs['maxcodecle']+1), 2, "0", STR_PAD_LEFT);
                    $_SESSION['codecle'] = $codecle;
                    $_SESSION['num_cle'] = $_POST['num_cle'];
                    $_SESSION['action_cle'] = $action_cle;
                    ?>
                    <form method="post" action="majbdd_cle.php">
                    <table align="center">
                        <tr>
                            <th>
                                Ajout de passes qui ouvre cette cl&eacute; : <?php echo $_POST['num_cle']; ?>
                            </th>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="radio" name="passe" value="on" > C'est un Passe</td>
                        </tr>
                        <tr>
                            <td>
                                <input type="radio" name="passe" value="off" checked> Ce n'est pas un Passe
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>

                    <?php
                    while ($row_rs_passe = mysql_fetch_assoc($rs_passe)) 
                    {?>
                        <tr>
                            <td>
                                <input type="checkbox" name="addPass[]" value='<?php echo $row_rs_passe['codepasse']; ?>' class="noircalibri10" size="20" maxsize="20"><?php echo $row_rs_passe['num_passe']; ?>
                            </td>
                        </tr>

                    <?php

                    }
                    ?>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        
                        <tr>
                            <td>
                                <input type="button" value="Tout cocher/D&eacute;cocher" class="noircalibri10" size="20" maxsize="20" onclick="cocherTout(this);" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="statut1" value="Disponible" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreDispo(this.checked);">Disponible
                            </td>
                            
                            <td>
                                <div id="dispo">
                                
                                </div>
                            </td>
                            
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="statut2" value="HS" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreHS(this.checked)">HS
                            </td>
                            <td>
                                <div id="HS">
                                
                                </div>
                                
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <?php
                        $query_rs_cles = "SELECT distinct nom,prenom,num_bureau,num_cle FROM individu ORDER BY nom";
                        $rs_cles = mysql_query($query_rs_cles) or die(mysql_error());
                        ?>
                        <tr>
                            <td>
                                Nom de la personne :
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select name="nom">
                                <?php
                                while($row_rs_cles=mysql_fetch_assoc($rs_cles))
                                {
                                    ?>
                                    
                                        <!--<option value="<?php echo $row_rs_cles['nom']; ?>" onclick="afficheBureau('<?php echo $row_rs_cles['num_bureau']; ?>')"><?php echo $row_rs_cles['nom']; ?></option>-->
                                        <option value="<?php echo $row_rs_cles['nom']; ?>" ><?php echo $row_rs_cles['nom']; ?></option>
                                    

                                <?php
                                }
                                ?>
                                </select>
                            </td>
                            <td>
                                <!--<div id="bureau">
                                
                                </div>-->
                                Bureau : <input id="myInput" type="text" value="" name="num_bureau" class="noircalibri10" size="20" maxsize="20">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                    </table>
                    <table align="center">
                        <tr>
                            <td>
                                <input type="submit" name="submit" value="valider">
                            </td>
                            <td>
                                <input type="submit" name="submit" value="annuler"
                            </td>
                        </tr>
                    </table>
                    </form>
                    
                <?php
                }
            
            }
            
            else if($action_cle=='enregistrer')
            {
                $tab_cle = $_SESSION['tab_cle'];
                $_SESSION['num_cle'] = $_POST['num_cle'];
                $_SESSION['action_cle'] = $action_cle;
                $_SESSION['codecle'] = $codecle;
                
                $query_rs_passe = "SELECT codepasse,num_passe FROM cle_passe";
                $rs_passe = mysql_query($query_rs_passe) or die(mysql_error());
                    
                $query_rs_passe_assoc = "SELECT codecle,codepasse FROM cle_association where codecle=".GetSQLValueString($codecle, "text");
                $rs_passe_assoc = mysql_query($query_rs_passe_assoc) or die(mysql_error());
                $i=0;
                while ($row_rs_passe_assoc = mysql_fetch_assoc($rs_passe_assoc))
                {
                    $tab_cle_assoc[$i] = $row_rs_passe_assoc;
                    $i++;
                }
                $num_row_rs_passe_assoc = mysql_num_rows($rs_passe_assoc);
                ?>
                <form method="post" action="majbdd_cle.php">
                <table align="center">
                    <tr>
                        <th>
                            Ajout de passes qui ouvre cette cl&eacute; : <?php echo $_POST['num_cle']; ?>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <!--Vérification si c'est un passe-->
                    <?php
                    if ($tab_cle[$codecle]['passe'] == "1")
                    {
                        ?>
                        <tr>
                            <td>
                                <input type="radio" name="passe" value="on" checked="checked"> C'est un Passe</td>
                            </tr>
                        <tr>
                            <td>
                                <input type="radio" name="passe" value="off" > Ce n'est pas un Passe
                            </td>
                        </tr>
                        <?php
                    }
                    else if ($tab_cle[$codecle]['passe'] == "0")
                    {
                    ?>

                        <tr>
                            <td>
                                <input type="radio" name="passe" value="on" > C'est un Passe</td>
                        </tr>
                        <tr>
                            <td>
                                <input type="radio" name="passe" value="off" checked="checked"> Ce n'est pas un Passe
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <!--Verification si il y a des passes qui ouvrent cette cle-->
                    <?php
                    if ($num_row_rs_passe_assoc > 0)
                    {
                        while ($row_rs_passe = mysql_fetch_assoc($rs_passe)) 
                        {
                            $test = false;
                            foreach ($tab_cle_assoc as $value)
                            {
                                if ($value['codepasse'] == $row_rs_passe['codepasse'])
                                {
                                ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="addPass[]" value='<?php echo $row_rs_passe['codepasse']; ?>' checked="checked" class="noircalibri10" size="20" maxsize="20"><?php echo $row_rs_passe['num_passe']; ?>
                                        </td>
                                    </tr>

                                    <?php
                                    $test = true;
                                    break;
                                }
                            }
                            
                            if (!$test)
                            {
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="addPass[]" value='<?php echo $row_rs_passe['codepasse']; ?>' class="noircalibri10" size="20" maxsize="20"><?php echo $row_rs_passe['num_passe']; ?>
                                    </td>
                                </tr>

                            <?php
                            }
                        }

                    }
                    else
                    {
                        while ($row_rs_passe = mysql_fetch_assoc($rs_passe)) 
                        {
                            
                        ?>

                            <tr>
                                <td>
                                    <input type="checkbox" name="addPass[]" value='<?php echo $row_rs_passe['codepasse']; ?>' class="noircalibri10" size="20" maxsize="20"><?php echo $row_rs_passe['num_passe']; ?>
                                </td>
                            </tr>

                        <?php
                        }
                    }
                    ?>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <input type="button" value="Tout cocher/D&eacute;cocher" class="noircalibri10" size="20" maxsize="20" onclick="cocherTout(this);" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <!--Vérification statut disponible-->
                        <tr>
                            <?php
                            if ($tab_cle[$codecle]['statut1'] == "Disponible")
                            {
                                ?>
                                <td>
                                    <input type="checkbox" name="statut1" value="Disponible" checked="checked" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreDispo(this.checked);">Disponible
                                </td>
                                <td>
                                    Nombre de cl&eacute;s dispo : <input type="text" name="nbre_dispo" value="<?php echo $tab_cle[$codecle]['nbre_dispo']; ?>" class="noircalibri10" size="20" maxsize="20">
                                </td>
                                <?php
                            }
                            else
                            {
                                ?>
                                <td>
                                    <input type="checkbox" name="statut1" value="Disponible" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreDispo(this.checked);">Disponible
                                </td>

                                <td>
                                    <div id="dispo">
                                    </div>
                                </td>
                                <?php
                            }
                            ?>
                            
                        </tr>
                        <!--Vérification statut HS-->
                        <tr>
                            <?php
                            if ($tab_cle[$codecle]['statut2'] == "HS")
                            {
                                ?>
                                <td>
                                    <input type="checkbox" name="statut2" value="HS" checked="checked" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreHS(this.checked)">HS
                                </td>
                                <td>
                                    Nombre de cl&eacute;s HS : <input type="text" name="nbre_HS" value="<?php echo $tab_cle[$codecle]['nbre_HS']; ?>" class="noircalibri10" size="20" maxsize="20">
                                </td>
                                <?php
                            }
                            else
                            {
                                ?>
                                <td>
                                    <input type="checkbox" name="statut2" value="HS" class="noircalibri10" size="20" maxsize="20" onclick="afficheNbreHS(this.checked)">HS
                                </td>
                                <td>
                                    <div id="HS">
                                    </div>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <!--Vérification du nom-->
                        <tr>
                            <td>
                                Nom de la personne :
                            </td>
                        </tr>
                            <?php
                            if (isset($tab_cle[$codecle]['nom']))
                            {
                                $query_rs_cles = "SELECT distinct nom,prenom,num_bureau,num_cle FROM individu ORDER BY nom";
                                $rs_cles = mysql_query($query_rs_cles) or die(mysql_error());
                                ?>
                                <tr>
                                    <td>
                                        <select name="nom">
                                        <?php
                                        while($row_rs_cles=mysql_fetch_assoc($rs_cles))
                                        {
                                            if ($tab_cle[$codecle]['nom'] ==$row_rs_cles['nom'])
                                            {?>
                                                <option value="<?php echo $row_rs_cles['nom']; ?>" selected><?php echo $row_rs_cles['nom']; ?></option>
                                            <?php
                                            }
                                            else
                                            {?>
                                                <option value="<?php echo $row_rs_cles['nom']; ?>" ><?php echo $row_rs_cles['nom']; ?></option>
                                            <?php
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <?php
                                    if (isset($tab_cle[$codecle]['num_bureau']))
                                    {?>
                                        <td>
        <!--                                <div id="bureau">

                                            </div>-->
                                            Bureau : <input id="myInput" type="text" value="<?php echo $tab_cle[$codecle]['num_bureau']; ?> " name="num_bureau" class="noircalibri10" size="20" maxsize="20">  
                                        </td>
                                        <?php
                                    }
                                    else
                                        {?>
                                        <td>
        <!--                                <div id="bureau">

                                            </div>-->
                                            Bureau : <input id="myInput" type="text" value="" name="num_bureau" class="noircalibri10" size="20" maxsize="20">   
                                        </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                            else
                            {
                                $query_rs_cles = "SELECT distinct nom,prenom,num_bureau,num_cle FROM individu ORDER BY nom";
                                $rs_cles = mysql_query($query_rs_cles) or die(mysql_error());
                                ?>
                                <tr>
                                    <td>
                                        <select name="nom">
                                        <?php
                                        while($row_rs_cles=mysql_fetch_assoc($rs_cles))
                                        {
                                            if ($tab_cle[$codecle]['nom'] ==$row_rs_cles['nom'])
                                            {?>
                                                <option value="<?php echo $row_rs_cles['nom']; ?>" ><?php echo $row_rs_cles['nom']; ?></option>
                                            <?php
                                            }
                                            else
                                            {?>
                                                <option value="<?php echo $row_rs_cles['nom']; ?>" ><?php echo $row_rs_cles['nom']; ?></option>
                                            <?php
                                            }
                                        }
                                        ?>
                                        </select>
                                    </td>
                                    <?php
                                    if (isset($tab_cle[$codecle]['num_bureau']))
                                    {?>
                                        <td>
        <!--                                <div id="bureau">

                                            </div>-->
                                            Bureau : <input id="myInput" type="text" value="<?php echo $tab_cle[$codecle]['num_bureau']; ?>" name="num_bureau" class="noircalibri10" size="20" maxsize="20">   
                                        </td>
                                        <?php
                                    }
                                    else
                                        {?>
                                        <td>
        <!--                                <div id="bureau">

                                            </div>-->
                                            Bureau : <input id="myInput" type="text" value="" name="num_bureau" class="noircalibri10" size="20" maxsize="20">   
                                        </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                            }?>
                    </table>
                    <table align="center">
                        <tr>
                            <td>
                                <input type="submit" name="submit" value="valider">
                            </td>
                            <td>
                                <input type="submit" name="submit" value="annuler"
                            </td>
                        </tr>
                    </table>
                    </form>
                
                <?php
                
            }
                
            else if($action_cle=='supprimer')

            {	
                mysql_query("delete from cle_association  where codecle=".$codecle) or die(mysql_error());
                mysql_query("delete from cle_standard  where codecle=".GetSQLValueString($codecle, "text")) or die(mysql_error());
                header('Location: gestioncle.php');
            }
            else if ($action_cle=='modifier')
            {
                $query_rs_cle = "SELECT codecle,num_cle,ouvre_passe FROM cle_standard where codecle=".GetSQLValueString($codecle, "text");
                $rs_cle = mysql_query($query_rs_cle) or die(mysql_error());
                $row_rs_cle = mysql_fetch_assoc($rs_cle);
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
                <table border="0" align="center" cellpadding="0" cellspacing="1">    
                <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des cles','lienretour'=>'newgestiondescles.php','texteretour'=>'Retour a la gestion des cl&eacute;s',
                    'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>    
                <tr>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <td>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
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
                            foreach($_SESSION['tab_cle'] as $un_cle=> $row_rs_cle)
                            {
                                ?>
                                <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
                                    <td nowrap align="left">
                                    <?php

                                    echo $row_rs_cle['codecle'];
                                    ?>
                                    </td>
                                    <td nowrap>
                                    <?php
                                    if ($row_rs_cle['codecle']==$codecle)
                                    {?>
                                        <input type="text" name="num_cle" value="<?php echo $row_rs_cle['num_cle'] ?>" class="noircalibri10" size="20" maxsize="20">
                                    <?php
                                    }
                                    else
                                    {
                                        echo $row_rs_cle['num_cle'];
                                    }
                                    ?>
                                    </td>

                                    <!-- Action -->

                                    <?php
                                    if ($row_rs_cle['codecle']==$codecle)
                                    {
                                        ?>
                                        <td><input type="image" name="submit#enregistrer##<?php echo $row_rs_cle['codecle'] ?>###cle####" src="images/b_enregistrer.png">
                                        </td>
                                        <td><input type="image" name="submit#annuler##<?php echo $row_rs_cle['codecle'] ?>###cle####" src="images/b_drop.png">
                                        </td>
                                        <?php
                                    }
                                     else
                                    { 
                                        echo '&nbsp';
                                    }?>

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
            <?php
            }    
        }
    }
}

if ((empty($_POST['codecle']) || isset($_POST['codecle']) == "codecle") && $action_cle !="modifier") 
{
    $query_rs_cle = "SELECT codecle,num_cle,ouvre_passe FROM cle_standard ORDER BY codecle";
    $rs_cle = mysql_query($query_rs_cle) or die(mysql_error());
    
    //$tab_lieu = array();
    
    while($row_rs_cle=mysql_fetch_assoc($rs_cle))
    {   
        //$i=0;
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
<script>
        function cocherTout(etat)
        {
            var cases = document.getElementsByName('addPass[]');   // on recupere tous les INPUT
            var j=0;
            var k=0;
            for(var i=1; i<cases.length; i++)
            {
                if(cases[i].checked === true)
                    j++;
                else
                    k++;
            }
            for(var i=1; i<cases.length; i++)
            {
                if (j>=k)
                    cases[i].checked = false;
                else
                    cases[i].checked = true;
            }
       
//        for(var i=1; i<cases.length; i++)     // on les parcourt
//             if(cases[i].type == 'checkbox')     // si on a une checkbox...
//                 {cases[i].checked = etat;}
        }

        function afficheNbreDispo(caseACocher)
        {
            if (caseACocher)
            {
                document.getElementById('dispo').innerHTML='Nombre de cl&eacute;s dispo : <input type="text" name="nbre_dispo" value="" class="noircalibri10" size="20" maxsize="20">';
            }

            else
                document.getElementById('dispo').innerHTML='';

        }

        function afficheNbreHS(caseACocher)
        {
            if (caseACocher)
            {
                document.getElementById('HS').innerHTML='Nombre de cl&eacute;s HS : <input type="text" name="nbre_HS" value="" class="noircalibri10" size="20" maxsize="20">';
            }

            else
                document.getElementById('HS').innerHTML='';

        }

        function afficheBureau(v)
        {
            if (v)
            {
                document.getElementById('bureau').innerHTML='Bureau : <input id="myInput" type="text" value="" name="num_bureau" class="noircalibri10" size="20" maxsize="20">';
                document.getElementById('myInput').value=v;
            }
            else
                document.getElementById('bureau').innerHTML='Bureau : <input type="text" value="" name="num_bureau" class="noircalibri10" size="20" maxsize="20">';
        }
</script>
