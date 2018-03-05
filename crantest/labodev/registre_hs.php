<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
if($admin_bd)
{ /*foreach($_POST as $key=>$val)
  { echo $key.'=>'.$val.'<br>';
	}*/
}
$dateheure=date("d/m/Y H:i");
$erreur="";	$erreur_envoimail="";
  
$lieu="";$fait="";$observation="";$suggestion="";$nom="";$email="";$observationdir="";

$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$numregistre=isset($_GET['numregistre'])?$_GET['numregistre']:(isset($_POST['numregistre'])?$_POST['numregistre']:"");
$lieu=isset($_POST['lieu'])?$_POST['lieu']:"";
$fait=isset($_POST['fait'])?$_POST['fait']:"";
$observation=isset($_POST['observation'])?$_POST['observation']:"";
$suggestion=isset($_POST['suggestion'])?$_POST['suggestion']:"";
$nom=isset($_POST['nom'])?$_POST['nom']:"";
$email=isset($_POST['email'])?$_POST['email']:"";
$observationdir=isset($_POST['observationdir'])?$_POST['observationdir']:"";

if ((isset($_POST["MM_update"])) && $_POST["MM_update"] == "registre_hs")
{ if($action=="soumettre")
	{ if($lieu=="" || $fait=="" || $nom=="")
		{ $erreur.="Les zones lieu, fait constat&eacute; et nom doivent &ecirc;tre renseign&eacute;es";
		}
		$erreur.=checklongueur('lieu',100,'Lieu');
		$erreur.=checklongueur('fait',100,'Fait');
		$erreur.=checklongueur('observation',100,'Observation');
		$erreur.=checklongueur('suggestion',100,'Suggestion');
		$erreur.=checklongueur('nom',50,'Nom');
		$erreur.=checklongueur('email',100,'Email');
	}
	else if($action=="viser")
	{ $erreur.=checklongueur('observationdir',100,'Observation du Directeur');
	}
	//$erreur="erreur forc&eacute;e";
	if($erreur=="")//Pas d'erreur lors des verifications
	{ if($action=="soumettre")
		{ mysql_query("START TRANSACTION") or  die(mysql_error());
			$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='registre_hs'") or  die(mysql_error());
			$row_seq_number=mysql_fetch_assoc($rs_seq_number);
			$numregistre=$row_seq_number['currentnumber'];
			$numregistre=str_pad((string)((int)$numregistre+1), 5, "0", STR_PAD_LEFT);  
			$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($numregistre, "text")." where nomtable='registre_hs'") or  die(mysql_error());
			//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
			mysql_query("COMMIT") or  die(mysql_error());
			mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
			mysql_query("insert into registre_hs (numregistre,dateheuresaisie,lieu,fait,observation,suggestion,nom,email) ".
									" values(".GetSQLValueString($numregistre,"text").",".GetSQLValueString($dateheure,"text").",".GetSQLValueString($_POST['lieu'],"text").",".GetSQLValueString($_POST['fait'],"text").",".
									GetSQLValueString($_POST['observation'],"text").",".GetSQLValueString($_POST['suggestion'],"text").",".GetSQLValueString($nom,"text").",".GetSQLValueString($_POST['email'],"text").")");
			
			$erreur_envoimail=mail_validation_registre($_POST);

			if($erreur_envoimail=="")
			{ http_redirect('menuprincipal.php');
			}
		}
		else if($action=="viser")
		{ mysql_query("update registre_hs set dateheurevisa=".GetSQLValueString($dateheure,"text").",observationdir=".GetSQLValueString($_POST['observationdir'],"text")." where numregistre=".GetSQLValueString($numregistre,"text")) or  die(mysql_error());
		}
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>Registre Hygi&egrave;ne et s&eacute;curit&eacute; <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
</head>
<body>
   <table width="100%" border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_hygsec.png','titrepage'=>'Registre Hygi&egrave;ne et S&eacute;curit&eacute;',
																'lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>array(),'erreur'=>$erreur,
                                'msg_erreur_objet_mail'=>'','erreur_envoimail'=>$erreur_envoimail)) ?>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
    	<td>
      	<a href="regles_hs.htm" target="_blank"><span class="bleugrascalibri11">R&egrave;gle de mise en oeuvre, d&rsquo;utilisation et de suivi du registre d&rsquo;hygi&egrave;ne et de s&eacute;curit&eacute;</span></a>
      </td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
    </tr>
    <tr>
    	<td align="center">
      	<table width="100%" border="0" cellspacing="2" cellpadding="2">
					<tr class="head">
          	<td align="center"><span class="bleugrascalibri11">Date<BR>heure</span></td>
          	<td align="center"><span class="bleugrascalibri11">Lieu<span class="rougegrascalibri9"><sup>*</sup></span></span></td> 
          	<td align="center"><span class="bleugrascalibri11">Fait constat&eacute;<span class="rougegrascalibri9"><sup>*</sup></span></span></td> 
          	<td align="center"><span class="bleugrascalibri11">Observations</span></td>
          	<td align="center"><span class="bleugrascalibri11">Suggestions</span></td>
          	<td align="center"><span class="bleugrascalibri11">Nom<span class="rougegrascalibri9"><sup>*</sup></span><BR>Mail</span></td>
          	<td align="center"><span class="bleugrascalibri11">Visa Directeur</span></td>
          	<td align="center"><span class="bleugrascalibri11">Observations Directeur</span></td>
					</tr>
          <!-- Nouvelle Observation -->
         <?php  
				 $class="even";
				 ?>
          <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
            <td align="center">&nbsp;
            </td>
						<form name="form_registre_hs_soumettre" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return controle_form_registre_hs('form_registre_hs_soumettre')">
            <input type="hidden" name="MM_update" value="registre_hs">
						<input type="hidden" name="action" value="soumettre">
            <td align="center">
            	<textarea name="lieu" class="noircalibri10" cols="20" rows="4" <?php echo affiche_longueur_js("this","100","'lieu_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $lieu ?></textarea>
							<br/><span id="lieu_nbcar_js" class="noircalibri9">0</span><span class="noircalibri9">/100 car. max</span></td> 
            <td align="center"><textarea name="fait" class="noircalibri10" cols="20" rows="4" <?php echo affiche_longueur_js("this","100","'fait_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $fait ?></textarea> 
							<br/><span id="fait_nbcar_js" class="noircalibri9">0</span><span class="noircalibri9">/100 car. max</span></td> 
            <td align="center"><textarea name="observation" class="noircalibri10" cols="20" rows="4" <?php echo affiche_longueur_js("this","100","'observation_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $observation ?></textarea>
            	<br/><span id="observation_nbcar_js" class="noircalibri9">0</span><span class="noircalibri9">/100 car. max</span></td>
            <td align="center"><textarea name="suggestion" class="noircalibri10" cols="20" rows="4" <?php echo affiche_longueur_js("this","100","'suggestion_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $suggestion ?></textarea>
            	<br/><span id="suggestion_nbcar_js" class="noircalibri9">0</span><span class="noircalibri9">/100 car. max</span></td>
            <td align="center"><input type="text" name="nom" class="noircalibri10" length="25" maxlength="50" value="<?php echo $nom ?>"><BR>
              <input type="text" name="email" class="noircalibri10" length="25" maxlength="100" value="<?php echo $email ?>"><BR>
              <input type="submit" name="submit" value="Valider" class="noircalibri10" onClick="return confirm('Cette observation sera d&eacute;finitivement inscrite dans le registe H&S\nValider ? ')"></td>
       			  </form>
            <td align="center">&nbsp;</td>
            <td align="center">&nbsp;</td>
          </tr>
          <!-- Observations passees -->
					<?php 
          //l'individu connecte est-il responsable de labo (codestructure='00')?
          $rs=mysql_query("select * from structureindividu where codestructure='00' and estresp='oui' and codeindividu=".GetSQLValueString($codeuser,"text"));
          $resplabo=mysql_num_rows($rs)==0?false:true;
          
          // Observations deja notees dans le registre
					$rs_registre_hs=mysql_query("select * from registre_hs order by numregistre desc");
          while($row_rs_registre_hs=mysql_fetch_assoc($rs_registre_hs))
          { ?> 
					<tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
            <td valign="top"><span class="noircalibri10"><?php echo $row_rs_registre_hs['dateheuresaisie'] ?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo nl2br($row_rs_registre_hs['lieu']) ?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo nl2br($row_rs_registre_hs['fait']) ?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo nl2br($row_rs_registre_hs['observation']) ?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo nl2br($row_rs_registre_hs['suggestion']) ?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo $row_rs_registre_hs['nom'] ?><BR><?php echo $row_rs_registre_hs['email'] ?></span></td>
        
        	<?php //  affichage different si resp. labo ou non
          if($row_rs_registre_hs['dateheurevisa']=="")// La date du visa "" indique que l'observation n'a pas ete visee par le directeur
          { if($resplabo)
          	{ ?><?php $row_rs_registre_hs['dateheurevisa']?>
              <form name="form_registre_hs_viser<?php echo $row_rs_registre_hs['numregistre'] ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return controle_form_registre_hs('form_registre_hs_viser<?php echo $row_rs_registre_hs['numregistre'] ?>')">
              <input type="hidden" name="MM_update" value="registre_hs">
              <input type="hidden" name="action" value="viser">
              <input type="hidden" name="numregistre" value="<?php echo $row_rs_registre_hs['numregistre'] ?>" >
            <td align="center">
            	<input name="viser" type="submit" class="noircalibri10" onClick="return confirm('Confirmez-vous votre Visa ?')" value="Viser">
            </td>
            <td align="center">
            	<textarea name="observationdir" cols="20" rows="4" <?php echo affiche_longueur_js("this","100","'observationdir_nbcar_js'","'noircalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_registre_hs['numregistre']==$numregistre?$observationdir:"" ?></textarea>
            	<br/><span id="observationdir_nbcar_js" class="noircalibri9">0</span><span class="noircalibri9">/100 car. max</span>
            </td>
              </form>
            <?php 
						}
            else
            { ?>
            <td>&nbsp;</td><td>&nbsp;</td>
            <?php 
						}
          }
          else
          { ?>
            <td valign="top"><span class="noircalibri10">vu le <BR>&nbsp;<?php echo $row_rs_registre_hs['dateheurevisa']?></span></td>
            <td valign="top"><span class="noircalibri10"><?php echo nl2br($row_rs_registre_hs['observationdir']) ?></span></td>
           <?php 
					}?>
        	</tr>
        <?php 
				}?>

				</table>
      </td>
    </tr>	  
  </table>
	<?php 
  if(isset($rs))mysql_free_result($rs);
  if(isset($rs_registre_hs))mysql_free_result($rs_registre_hs);
  if(isset($rs_seq_number))mysql_free_result($rs_seq_number);
?>
</body>
</html>




