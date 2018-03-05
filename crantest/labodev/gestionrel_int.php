<?php require_once('_const_fonc.php'); 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$tab_contexte=array('prog'=>'gestionrel_int','codeuser'=>$codeuser);
if(!(droit_acces($tab_contexte) ||  $admin_bd))
{?>
Acc&egrave;s restreint
<?php exit;
}
$form_gestionrel_int = "form_gestionrel_int";
if($admin_bd)
{ /* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	} */ 
}
$aujourdhui=date('Ymd');
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$action='';
$codepartenariat='';
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$pos3diese=strpos($postkey,'###');
		if($pos3diese!==false)
		{ $submit=substr($postkey,0,$pos3diese);
			$pos2diese=strpos($submit,'##');
			if($pos2diese!==false)
			{ $codepartenariat=substr($submit,$pos2diese+2);
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
	else if(substr($postkey,0,strlen("codecontact"))=="codecontact" || substr($postkey,0,strlen("codeindividu"))=="codeindividu")
	{ if($postval!='')
		{	$pos2diese=strpos($postkey,'##');
			if($pos2diese!==false)
			{ $num=substr($postkey,0,$pos2diese);
				$posdiese=strpos($num,"#");
				if($posdiese!=false)
				{ $num=substr($num,$posdiese+1);
				}
			}
			if(substr($postkey,0,strlen("codecontact"))=="codecontact")
			{ $tab_contact[$num]=$postval;
			}
			else
			{ $tab_individu[$num]=$postval;
			}
		}
	}
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ if($erreur=='')
	{ $affiche_succes=true;
		if($action=='enregistrer')
		{	if($codepartenariat=='')
			{ $rs=mysql_query("select max(codepartenariat) as maxcodepartenariat from rel_int_partenariat");
				$row_rs=mysql_fetch_assoc($rs);
				$codepartenariat=str_pad((string)((int)$row_rs['maxcodepartenariat']+1), 5, "0", STR_PAD_LEFT);  
				$updateSQL="insert into rel_int_partenariat (codepartenariat,codeorganisme,codetype,detailpartenariat_court_fr,detailpartenariat_long_fr,detailpartenariat_court_en,detailpartenariat_long_en) ".
										" values (".GetSQLValueString($codepartenariat, "text").",".GetSQLValueString($_POST['codeorganisme'], "text").",".
										GetSQLValueString($_POST['codetype'], "text").",".GetSQLValueString($_POST['detailpartenariat_court_fr'], "text").",".
										GetSQLValueString($_POST['detailpartenariat_long_fr'], "text").",".GetSQLValueString($_POST['detailpartenariat_court_en'], "text").",".
										GetSQLValueString($_POST['detailpartenariat_long_en'], "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
			}
			else
			{ $updateSQL="update rel_int_partenariat set codeorganisme=".GetSQLValueString($_POST['codeorganisme'], "text").
									", codetype=".GetSQLValueString($_POST['codetype'], "text").", detailpartenariat_court_fr=".GetSQLValueString($_POST['detailpartenariat_court_fr'], "text").
									", detailpartenariat_long_fr=".GetSQLValueString($_POST['detailpartenariat_long_fr'], "text").", detailpartenariat_court_en=".GetSQLValueString($_POST['detailpartenariat_court_en'], "text").
									", detailpartenariat_long_en=".GetSQLValueString($_POST['detailpartenariat_long_en'], "text").
									" where codepartenariat=".GetSQLValueString($codepartenariat, "text");
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
				$updateSQL="delete from  rel_int_partenariatcontact where codepartenariat=".GetSQLValueString($codepartenariat, "text");
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
				$updateSQL="delete from  rel_int_partenariatindividu where codepartenariat=".GetSQLValueString($codepartenariat, "text");
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
			}
			// contact
			ksort($tab_contact);
			foreach($tab_contact as $numcontact=>$codecontact)
			{ $updateSQL="insert into rel_int_partenariatcontact (codepartenariat,codecontact) values (".GetSQLValueString($codepartenariat, "text").",".GetSQLValueString($codecontact, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
			}
			// individu
			ksort($tab_individu);
			foreach($tab_individu as $numindividu=>$codeindividu)
			{ $updateSQL="insert into rel_int_partenariatindividu (codepartenariat,codeindividu) values (".GetSQLValueString($codepartenariat, "text").",".GetSQLValueString($codeindividu, "text").")";
				mysql_query($updateSQL) or die(mysql_error());
				//echo '<br>'.$updateSQL;
			}
		}
		else if($action=='supprimer')
		{	$updateSQL="delete from rel_int_partenariat  where codepartenariat=".GetSQLValueString($codepartenariat, "text");
			mysql_query($updateSQL) or die(mysql_error());
			//echo '<br>'.$updateSQL;
			$updateSQL="delete from  rel_int_partenariatcontact where codepartenariat=".GetSQLValueString($codepartenariat, "text");
			mysql_query($updateSQL) or die(mysql_error());
			//echo '<br>'.$updateSQL;
			$updateSQL="delete from  rel_int_partenariatindividu where codepartenariat=".GetSQLValueString($codepartenariat, "text");
			mysql_query($updateSQL) or die(mysql_error());
			//echo '<br>'.$updateSQL;
		}
	}
}

// ----------------------- liste des partenariats a afficher 
// partenariat : vide d'abord
$tab_partenariat=array();
$query_rs = "SELECT rel_int_partenariat.*,liborganisme_long,pays.codepays,libpays,libtype_fr".
						"	FROM rel_int_partenariat,rel_int_organisme,pays,rel_int_type".
						" WHERE rel_int_partenariat.codeorganisme=rel_int_organisme.codeorganisme".
						" and rel_int_organisme.codepays=pays.codepays and rel_int_partenariat.codetype=rel_int_type.codetype".
						" and codepartenariat=''";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariat[$row_rs['codepartenariat']]=$row_rs;
}
// partenariat
$query_rs = "SELECT rel_int_partenariat.*,liborganisme_long,pays.codepays,libpays,libtype_fr".
						"	FROM rel_int_partenariat,rel_int_organisme,pays,rel_int_type".
						" WHERE rel_int_partenariat.codeorganisme=rel_int_organisme.codeorganisme".
						" and rel_int_organisme.codepays=pays.codepays and rel_int_partenariat.codetype=rel_int_type.codetype".
						" and codepartenariat<>''".
						" order by libpays,liborganisme_long desc";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariat[$row_rs['codepartenariat']]=$row_rs;
}


// partenariat contact
$query_rs = "SELECT rel_int_partenariatcontact.*,concat(rel_int_contact.titre,' ',rel_int_contact.prenom,' ',rel_int_contact.nom) as contactprenomnom".
						"	FROM rel_int_partenariatcontact,rel_int_contact".
						" WHERE rel_int_partenariatcontact.codecontact=rel_int_contact.codecontact".
						" ORDER BY rel_int_contact.nom";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_partenariatcontact=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariatcontact[$row_rs['codepartenariat']][$row_rs['codecontact']]=$row_rs['contactprenomnom'];
}

// partenariat individu
$query_rs = "SELECT rel_int_partenariatindividu.*,concat(individu.nom,' ',individu.prenom) as individunomprenom".
						"	FROM rel_int_partenariatindividu,individu".
						" WHERE rel_int_partenariatindividu.codeindividu=individu.codeindividu".
						" ORDER BY individu.nom";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_partenariatindividu=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariatindividu[$row_rs['codepartenariat']][$row_rs['codeindividu']]=$row_rs['individunomprenom'];
}

// contact
$query_rs = "SELECT codecontact,nom,prenom	FROM rel_int_contact order by codecontact";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_contact=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_contact[$row_rs['codecontact']]=$row_rs['prenom'].' '.$row_rs['nom'].'.';
}

// individu
$query_rs = "SELECT individu.codeindividu,nom,prenom	FROM individu, individusejour,corps,cat".
						" WHERE individu.codeindividu=individusejour.codeindividu".
						" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
						" and".periodeencours('datedeb_sejour','datefin_sejour')." and (cat.codelibcat='EC' or cat.codelibcat='CHERCHEUR')".
						" UNION".
						" SELECT individu.codeindividu,nom,prenom	FROM individu where codeindividu=''". 
						" order by nom, prenom";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_individu=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individu[$row_rs['codeindividu']]=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'.';
}
// pays
$query_rs = "SELECT codepays,libpays FROM pays order by numordre,libpays";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_pays=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_pays[$row_rs['codepays']]=$row_rs['libpays'];
}

// type rel_int
$query_rs = "SELECT codetype,libtype_fr as libtype FROM rel_int_type";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_type=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_type[$row_rs['codetype']]=$row_rs['libtype'];
}

// organismes
$query_rs = "SELECT  codeorganisme,pays.codepays, liblieu, liborganisme_court,liborganisme_long".
						" FROM rel_int_organisme,pays where rel_int_organisme.codepays=pays.codepays".
						" order by liborganisme_long";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_organisme=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_organisme[$row_rs['codeorganisme']]=$row_rs;
}
?>
<head>
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
</head>
<body>

<table align="center" cellspacing="2">
	<tr>
    <td colspan="4" align="left">
      <form name="<?php echo $form_gestionrel_int ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		<?php 
		if($action!='creer' && $action!='modifier')
    {?> <input type="image" name="submit#creer##" src="images/b_add.png">
    <?php 
    }?>
      </form>
		</td>
  </tr>
  <tr class="head" align="center">
  	<td>code<br>part</td>
    <td nowrap><span class="bleugrascalibri10">Pays</span>
    </td>
    <td nowrap class="bleugrascalibri10">Organisme
    </td>
    <td nowrap class="bleugrascalibri10">Contact
    </td>
    <td nowrap class="bleugrascalibri10">Contact labo.
    </td>
    <td nowrap class="bleugrascalibri10">Type
    </td>
    <td nowrap class="bleugrascalibri10">D&eacute;tail court</td>
    <td nowrap class="bleugrascalibri10">D&eacute;tail long</td>
    <td nowrap class="bleugrascalibri10">Action</td>
  </tr>
<form name="<?php echo $form_gestionrel_int ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">

<?php
$class='';
foreach($tab_partenariat as $un_codepartenariat=>$un_tab_partenariat)
{ $class=($class=='even'?'odd':'even');?>
  <tr class="<?php echo $class ?>">
  	<td><?php echo $un_codepartenariat ?></td>
  <?php 
	if(($action=='modifier' || $action=='creer') && $un_codepartenariat==$codepartenariat)
  {?>
    <td nowrap align="left">
      <select name="codepays">
				<?php 
				foreach($tab_pays as $codepays=>$libpays)
        {?> <option value="<?php echo $codepays ?>" <?php echo $codepays==$un_tab_partenariat['codepays']?'selected':'' ?>><?php echo substr($libpays,0,10) ?></option>
        <?php 
        }?>
      </select>
    </td>
    <td>
      <select name="codeorganisme">
        <?php 
        foreach($tab_organisme as $codeorganisme=>$un_tab_organisme)
        {?> <option value="<?php echo $codeorganisme ?>" <?php echo $codeorganisme==$un_tab_partenariat['codeorganisme']?'selected':'' ?>><?php echo substr($un_tab_organisme['liborganisme_long'],0,40) ?></option>
        <?php 
        }?>
      </select>
    </td>
    <td>
    	<table>
				<?php 
        if(isset($tab_partenariatcontact[$un_tab_partenariat['codepartenariat']]))
        { $numcontact=0;
          foreach($tab_partenariatcontact[$un_tab_partenariat['codepartenariat']] as $codepartenariatcontact=>$contact)
          {?>
          <tr>
            <td>
              <select name="codecontact#<?php echo $numcontact ?>##">
            <?php 
            foreach($tab_contact as $codecontact=>$contactprenomnom)
            {?><option value="<?php echo $codecontact ?>" <?php echo $codepartenariatcontact==$codecontact?'selected':'' ?>><?php echo $contactprenomnom ?></option>
            <?php 
            } ?>
              </select>
            </td>
          </tr>    
          <?php 
					$numcontact++;
          }
        }?>
       	<tr>
        	<td>
						<select name="codecontact#<?php echo $numcontact ?>##">
						<?php 
            foreach($tab_contact as $codecontact=>$contactprenomnom)
            {?><option value="<?php echo $codecontact ?>" <?php echo $codecontact==''?'selected':'' ?>><?php echo $contactprenomnom ?></option>
  
            <?php 
            } ?>
            </select>
      		</td>
        </tr>    
      </table>
    </td>
    <td>
    	<table>
				<?php 
        if(isset($tab_partenariatindividu[$un_tab_partenariat['codepartenariat']]))
        { $numindividu=0;
          foreach($tab_partenariatindividu[$un_tab_partenariat['codepartenariat']] as $codepartenariatindividu=>$individu)
          {?>
          <tr>
            <td>
              <select name="codeindividu#<?php echo $numindividu ?>##">
            <?php 
            foreach($tab_individu as $codeindividu=>$individunomprenom)
            {?><option value="<?php echo $codeindividu ?>" <?php echo $codepartenariatindividu==$codeindividu?'selected':'' ?>><?php echo $individunomprenom ?></option>
            <?php 
            } ?>
              </select>
            </td>
          </tr>    
          <?php 
					$numindividu++;
          }
        }?>
       	<tr>
        	<td>
						<select name="codeindividu#<?php echo $numindividu ?>##">
						<?php 
            foreach($tab_individu as $codeindividu=>$individunomprenom)
            {?><option value="<?php echo $codeindividu ?>" <?php echo $codeindividu==''?'selected':'' ?>><?php echo $individunomprenom ?></option>
  
            <?php 
            } ?>
            </select>
      		</td>
        </tr>    
      </table>
    </td>
    <td>
      <select name="codetype">
				<?php 
				foreach($tab_type as $codetype=>$libtype)
        {?> <option value="<?php echo $codetype ?>" <?php echo $codetype==$un_tab_partenariat['codetype']?'selected':'' ?>><?php echo $libtype ?></option>
        <?php 
        }?>
      </select>
    </td>
    <td nowrap>
      <span class="mauvecalibri10">FR</span> <input type="text" name="detailpartenariat_court_fr" value="<?php echo $un_tab_partenariat['detailpartenariat_court_fr'] ?>" size="30" maxlength="50"><br>
      <span class="mauvecalibri10">EN</span> <input type="text" name="detailpartenariat_court_en" value="<?php echo $un_tab_partenariat['detailpartenariat_court_en'] ?>" size="30" maxlength="50">
    </td>
    <td nowrap>
      <span class="mauvecalibri10">FR</span> <input type="text" name="detailpartenariat_long_fr" value="<?php echo $un_tab_partenariat['detailpartenariat_long_fr'] ?>" size="60" maxlength="500"><br>
      <span class="mauvecalibri10">EN</span> <input type="text" name="detailpartenariat_long_en" value="<?php echo $un_tab_partenariat['detailpartenariat_long_en'] ?>" size="60" maxlength="500">
    </td>
    <td>
      <input type="image" name="submit#enregistrer##<?php if($un_tab_partenariat['codepartenariat']!=''){ echo $un_tab_partenariat['codepartenariat'];?>###<?php }?>" src="images/b_enregistrer.png">
    </td>
      <?php
  }
  else if($un_tab_partenariat['codepartenariat']!='')
  {?><td><?php echo substr($un_tab_partenariat['libpays'],0,10) ?></td>
      <td><?php echo substr($un_tab_partenariat['liborganisme_long'],0,40) ?>
      </td>
      <td nowrap>
			<?php 
			if(isset($tab_partenariatcontact[$un_tab_partenariat['codepartenariat']]))
      { $first=true;
        foreach($tab_partenariatcontact[$un_tab_partenariat['codepartenariat']] as $codecontact=>$contactprenomnom)
        { echo $first?'':'<br>';?><span class="mauvecalibri10"><?php echo $contactprenomnom ?></span>
        <?php $first=false;
        }
      }?>
      </td>
       <td nowrap>
			<?php 
			if(isset($tab_partenariatindividu[$un_tab_partenariat['codepartenariat']]))
      { $first=true;
        foreach($tab_partenariatindividu[$un_tab_partenariat['codepartenariat']] as $codeindividu=>$individunomprenom)
        { echo $first?'':'<br>';?><span class="mauvecalibri10"><?php echo $individunomprenom ?></span>
        <?php $first=false;
        }
      }?>
      </td>
     <td><?php echo $un_tab_partenariat['libtype_fr'] ?></td>
      <td><span class="mauvecalibri10">FR : </span><?php echo $un_tab_partenariat['detailpartenariat_court_fr'] ?><br>
      		<span class="mauvecalibri10">EN : </span><?php echo $un_tab_partenariat['detailpartenariat_court_en'] ?>
      </td>
      <td><span class="mauvecalibri10">FR : </span><?php echo $un_tab_partenariat['detailpartenariat_long_fr'] ?><br>
      		<span class="mauvecalibri10">EN : </span><?php echo $un_tab_partenariat['detailpartenariat_long_en'] ?>
      </td>
      <td>
      <?php if($action=='modifier' || $action=='creer')
      {?>&nbsp;
      <?php 
			}
      else
			{?> <table>
          <tr>
            <td><input type="image" name="submit#modifier##<?php echo $un_tab_partenariat['codepartenariat'] ?>###" src="images/b_edit.png"></td>
            <td><input type="image" name="submit#supprimer##<?php echo $un_tab_partenariat['codepartenariat']  ?>###" src="images/b_drop.png" onClick="return confirm('Supprimer ?');"></td>
          </tr>
        </table>
        <?php 
			}?>
      </td>
	<?php
	}?>
  </tr>
<?php
}
?>
</table>
</body>
</html>