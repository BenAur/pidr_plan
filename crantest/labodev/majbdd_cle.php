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
$codecle = $_SESSION['codecle'];
//$codecle_add = $_SESSION['codecle_add'];

if($_POST['submit'] == 'annuler')
{
    header('location:gestioncle.php');
}
else
{
if (!isset($_POST['addPass'][0]))
    $addPass = "";
else
    $addPass = $_POST['addPass'];

if (!isset($_POST['statut1']))
    $_POST['statut1']="";
if (!isset($_POST['statut2']))
    $_POST['statut2']="";
if (!isset($_POST['nbre_dispo']))
    $_POST['nbre_dispo']="";
if (!isset($_POST['nbre_HS']))
    $_POST['nbre_HS']="";
if (!isset($_POST['nom']))
    $_POST['nom']="";
if (!isset($_POST['num_bureau']))
    $_POST['num_bureau']="";
if (isset($_POST['passe'])=="on")
    $_POST['passe']=1;
if (isset($_POST['passe'])=="off")
    $_POST['passe']=0;

if ($_SESSION['action_cle'] == 'creer')
{
    if ($addPass != "")
    {
        foreach ($addPass as $value)
        {
            $result = mysql_query("insert into cle_association (codecle,codepasse)".
                                    "value (".GetSQLValueString($codecle, "text").","
                                                .GetSQLValueString($value, "text").")") or die(mysql_error());
        }
        $sqldata = serialize($addPass);
    }
    else
        $sqldata = "";
    $result = mysql_query("INSERT INTO cle_standard (codecle,num_cle,ouvre_passe,statut1,nbre_dispo,statut2,nbre_HS,nom,num_bureau,passe)".
                                                                " value (".GetSQLValueString($_SESSION['codecle'], "text").","
                                                                            .GetSQLValueString($_SESSION['num_cle'], "text").","
                                                                            .GetSQLValueString($sqldata, "text").","
                                                                            .GetSQLValueString($_POST['statut1'], "text").","
                                                                            .GetSQLValueString($_POST['nbre_dispo'], "text").","
                                                                            .GetSQLValueString($_POST['statut2'], "text").","
                                                                            .GetSQLValueString($_POST['nbre_HS'], "text").","
                                                                            .GetSQLValueString($_POST['nom'], "text").","
                                                                            .GetSQLValueString($_POST['num_bureau'], "text").","
                                                                            .GetSQLValueString($_POST['passe'], "text").")") or die(mysql_error());
header('location:gestioncle.php');
}

else if ($_SESSION['action_cle'] == 'enregistrer')
{
    if ($addPass != "")
    {
        mysql_query("delete from cle_association  where codecle=".$codecle) or die(mysql_error());
        foreach ($addPass as $value)
        {
            $result = mysql_query("insert into cle_association (codecle,codepasse)".
                                    "value (".GetSQLValueString($codecle, "text").","
                                                .GetSQLValueString($value, "text").")") or die(mysql_error());
        }
        $sqldata = serialize($addPass);
    }
    else
        $sqldata = "";
    
    $result = mysql_query("update cle_standard set num_cle=".GetSQLValueString($_SESSION['num_cle'], "text").","
                                                    ."ouvre_passe=".GetSQLValueString($sqldata, "text").","
                                                    . "statut1=".GetSQLValueString($_POST['statut1'], "text").","
                                                    ."nbre_dispo=".GetSQLValueString($_POST['nbre_dispo'], "text").","
                                                    ."statut2=".GetSQLValueString($_POST['statut2'], "text").","
                                                    ."nbre_HS=".GetSQLValueString($_POST['nbre_HS'], "text").","
                                                    ."nom=".GetSQLValueString($_POST['nom'], "text").","
                                                    ."num_bureau=".GetSQLValueString($_POST['num_bureau'], "text").","
                                                    ."passe=".GetSQLValueString($_POST['passe'], "text")." where codecle=".GetSQLValueString($codecle, "text")) or die(mysql_error());
header('location:gestioncle.php');
}
}
                                                                            
?>
                                                                            
