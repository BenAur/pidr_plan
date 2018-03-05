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

if (isset($_GET['codecle']))
{
    $codecle = $_GET['codecle'];
    $query_rs_passe = "select codecle,codepasse from cle_association where codecle=$codecle";
    $rs_passe=mysql_query($query_rs_passe) or die(mysql_error());
    $row_sql = mysql_num_rows($rs_passe);
    
    if ($row_sql > 0)
    {
        $i=0;
        $tab_passe = array();
        while ($row_rs_passe=  mysql_fetch_assoc($rs_passe))
        {
            $tab_data_passe = $row_rs_passe['codepasse'];
            $query = mysql_query("select num_passe from cle_passe where codepasse=$tab_data_passe") or die(mysql_error());
            $row_query = mysql_fetch_assoc($query);
            $tab_passe[$i] = $row_query['num_passe'];
            $i++;
        }
    }
    else
        $tab_passe="";
    
    $query_rs_cle = "SELECT codecle,num_cle,statut1,nbre_dispo,statut2,nbre_HS,nom,num_bureau,passe FROM cle_standard where codecle='$codecle'";
    $rs_cle = mysql_query($query_rs_cle) or die(mysql_error());
    $row_rs_cle=  mysql_fetch_assoc($rs_cle);
    
}

?>
<table border="0" align="center" cellpadding="3" cellspacing="1">
    <tr>
        <?php
        if ($row_rs_cle['passe'] == "1")
        {
            ?>
            <th align="center">
            C'est un passe : <?php echo $row_rs_cle['num_cle'];?>
            </th>
            <?php
        }
        else
        {
            ?>
            <th align="center">
            Passes qui ouvrent cette cl&eacute : <?php echo $row_rs_cle['num_cle'];?>
            </th>
            <?php
        }
        ?>
    </tr>
    <?php 
        if ($tab_passe <> "")
        {
            foreach($tab_passe as $key=>$value)
            {?>
                <tr>
                    <td>
                        <?php echo $value;?>
                    </td>
                </tr> 
            <?php

            }
        }
        else if ($row_rs_cle['passe'] == "0")
        {?>
            <tr>
                <td>
                    Pas de passes trouv&eacute;s pour cette cl&eacute; !
                </td>
            </tr> 
        <?php
        }
        if ($row_rs_cle['statut1'] <> "")
        {
        ?>
            <tr>
                <td>
                    Nombre de cl&eacute;s disponible : <?php echo $row_rs_cle['nbre_dispo'];?>
                </td>
            </tr>
        <?php
        }
        if ($row_rs_cle['statut2'] <> "")
        {
        ?>
            <tr>
                <td>
                    Nombre de cl&eacute;s HS : <?php echo $row_rs_cle['nbre_HS'];?>
                </td>
            </tr>
        <?php
        }
        if ($row_rs_cle['nom'] <> "")
        {
        ?>
            <tr>
                <td>
                    Nom : <?php echo $row_rs_cle['nom'];?>
                </td>
            </tr>
        <?php
        }
        if ($row_rs_cle['num_bureau'] <> "")
        {
        ?>
            <tr>
                <td>
                    Bureau : <?php echo $row_rs_cle['num_bureau'];?>
                </td>
            </tr>
        <?php
        }
        ?>
             
</table>

