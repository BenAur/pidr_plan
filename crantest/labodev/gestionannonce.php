<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$user_mode_ok=(($GLOBALS['mode_exploit']=="restreint" && $admin_bd) || $GLOBALS['mode_exploit']=="normal" || $GLOBALS['mode_exploit']=="test");//user et/ou mode restreint ou non
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$tab_contexte=array('prog'=>'gestionannonce','codeuser'=>$codeuser);
if(!(estrole('sif',$tab_roleuser) || estrole('du',$tab_roleuser) || droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestionannonce = "form_gestionannonce";
if($admin_bd)
{ /* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/  
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$action='';
$codeannonce='';
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$pos3diese=strpos($postkey,'###');
		if($pos3diese!==false)
		{ $submit=substr($postkey,0,$pos3diese);
			$pos2diese=strpos($submit,'##');
			if($pos2diese!==false)
			{ $codeannonce=substr($submit,$pos2diese+2);
				$submit=substr($submit,0,$pos2diese);
				$posdiese=strpos($submit,"#");
				if($posdiese!=false)
				{ $action=substr($submit,$posdiese+1);
				}
			}
		}
		else
		{ $pos2diese=strpos($postkey,'##');
			if($pos2diese!==false)
			{ $submit=substr($postkey,0,$pos2diese);
				$posdiese=strpos($submit,"#");
				if($posdiese!=false)
				{ $action=substr($submit,$posdiese+1);
				}
			}
		}
	}
}
if(isset($_POST['nouveaucf_libcourt']) && $_POST['nouveaucf_libcourt']=='')
{ $erreur='Libell&eacute; vide';
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ if($erreur=='')
	{ $affiche_succes=true;
		// on enregistre d'abord nouveau
		if($action=='creer')
		{ $rs=mysql_query("select max(codeannonce) as maxcodeannonce from annonce");
			$row_rs=mysql_fetch_assoc($rs);
			$codeannonce=str_pad((string)((int)$row_rs['maxcodeannonce']+1), 5, "0", STR_PAD_LEFT);  
			mysql_query("insert into annonce (codeannonce,libcourt) ".
									" values (".GetSQLValueString($codeannonce, "text").",".GetSQLValueString($_POST['libcourt'], "text").")") or die(mysql_error());
		}
		else if($action=='enregistrer')
		{	mysql_query("update annonce set libcourt=".GetSQLValueString($_POST['libcourt'], "text").
									" where codeannonce=".GetSQLValueString($codeannonce, "text")) or die(mysql_error());

		}
		else if($action=='supprimer')
		{	mysql_query("delete from annonce  where codeannonce=".GetSQLValueString($codeannonce, "text")) or die(mysql_error());

		}
	}
}
else
{ 
}

// ----------------------- liste des budgetrecettes a afficher 
// centrefinancier
$query_rs_annonce = "SELECT codeannonce,libcourt".
																" FROM annonce";
$rs_annonce = mysql_query($query_rs_annonce) or die(mysql_error());

if($erreur=='')
{	 
}
else//valeurs du POST a la place de certaines données des tables qui n'ont pas été mises a jour
{ /* $rs_fields_commande = mysql_query('SHOW COLUMNS FROM budgetrecette');
	while($row_rs_fields_budgetrecette = mysql_fetch_assoc($rs_fields_budgetrecette)) 
	{ $Field=$row_rs_fields_budgetrecette['Field'];
		if(isset($_POST[$Field]))
		{ $row_rs_budgetrecette[$Field]=$_POST[$Field];
		}
		if(array_key_exists($row_rs_fields_budgetrecette['Field'],$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
		{ $row_rs_budgetrecette[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
		}
	} */
}
 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des annonce <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
</head>
<body <?php if($erreur!='' || $warning!=''){?>onLoad="alert('<?php echo str_replace("<br>","\\n", str_replace("'","&rsquo;",$erreur)).
																																	($erreur!='' && $warning!=''?'<br>':'').str_replace("<br>","\\n", str_replace("'","&rsquo;",$warning)) ?>')"<?php }?>>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_bourse.png','titrepage'=>'Annonces','lienretour'=>'menubudget.php','texteretour'=>'Retour au menu budget',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche)) ?>
  <tr>
    <td align="left" nowrap>&nbsp;</td>
  </tr>
  <tr>
  	<td align="center">
    	<table>
      	<tr>
        	<td class="noircalibri10">
    - mettre de la couleur (red, green, blue, grey...) : texte en &lt;font color='red'&gt;rouge&lt;/font&gt; =&gt; texte en <font color='red'>rouge</font><br>
    - changer de ligne &lt;br&gt;<br>
    - mettre en gras : texte en &lt;b&gt;gras&lt;/b&gt; =&gt; texte en <b>gras</b>
    			</td>
        </tr>
       </table>
    </td>
  </tr>
	<tr>
		<td>
      <form name="<?php echo $form_gestionannonce ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
			<table align="center" border="0" class="data" id="table_results">
				<tr class="head" align="center">
					<td nowrap class="bleugrascalibri10">Annonce
					</td>
					<td nowrap class="bleugrascalibri10">Action</td>
	      </tr>
				<?php 	
				$class="even";
				while($row_rs_annonce=mysql_fetch_assoc($rs_annonce))
				{
				?> 
				<tr class="<?php echo $class=='even'?'odd':'even' ?>">
					<?php 
					if($action=='modifier')
					{ ?>
            <td nowrap>
            <?php 
						if($row_rs_annonce['codeannonce']==$codeannonce) 
						{ ?><textarea cols="100" rows="5" name="libcourt"><?php echo $row_rs_annonce['libcourt'] ?></textarea>
            <?php
            }
            else
            {  echo $row_rs_annonce['libcourt'];
            }?>
						</td>
						<td>
            <?php 
						if($row_rs_annonce['codeannonce']==$codeannonce)
						{?> <input type="image" name="submit#enregistrer##<?php echo $row_rs_annonce['codeannonce'] ?>###" src="images/b_enregistrer.png">
            <?php 
						}
						else
						{ echo '&nbsp';
						}?> 
            </td>
            <?php
					}
					else
					{?>
            <td><?php echo $row_rs_annonce['libcourt']?>
            </td>
            <td nowrap align="left">
              <table>
                <tr>
                  <td><input type="image" name="submit#modifier##<?php echo $row_rs_annonce['codeannonce'] ?>###" src="images/b_edit.png"></td>
                  <td><input type="image" name="submit#supprimer##<?php echo $row_rs_annonce['codeannonce'] ?>###" src="images/b_drop.png" onClick="return confirm('Supprimer ?');return false;"></td>
                </tr>
              </table>
            </td>
          <?php 
					}?>
				</tr>
     </form>
				<?php
				}?>
				<?php if($action!='modifier')
     	 	{?>
        <form name="<?php echo $form_gestionannonce ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
      		<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
          <tr>
            <td><textarea cols="100" rows="5" name="libcourt" type="text" id="libcourt"></textarea></td>
            <td><input type="image" name="submit#creer##" class="noircalibri10" src="images/b_add.png"></td>
          </tr>
     		</form>
        <?php }?>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
