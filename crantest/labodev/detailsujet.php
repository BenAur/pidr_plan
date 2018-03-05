<?php  require_once('_const_fonc.php'); ?>
<?php 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$codesujet=isset($_GET['codesujet'])?$_GET['codesujet']:(isset($_POST['codesujet'])?$_POST['codesujet']:"");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Fiche Sujet</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css" type="text/css">
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php   
if($codesujet!="")
{ $query_rs_sujet="select sujet.*, typesujet.libcourt_fr as libtypesujet, statutsujet.libstatutsujet as libstatutsujet".
								" from sujet, typesujet, statutsujet  ".
								" where sujet.codetypesujet=typesujet.codetypesujet and sujet.codestatutsujet=statutsujet.codestatutsujet".
								" and sujet.codesujet=".GetSQLValueString($codesujet, "text");
	$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
	$contenu="";
	if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
	{?>
	<table>
		<tr>
		  <td><span class="visu_verdanamauve7"><?php echo $row_rs_sujet['libstatutsujet'] ?></span></td>
		  <td align="center"><b><span class="visu_verdananoir7"><?php echo nl2br($row_rs_sujet['titre_fr']) ?></span></b></td>
	  </tr>
		<tr>
			<td>&nbsp;</td>
			<td align="center"><span class="visu_verdananoir7">(<?php echo $row_rs_sujet['libtypesujet'] ?> du <?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datedeb_sujet'],"/")?> au <?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datefin_sujet'],"/") ?>)</span></td>
		</tr>
		<tr>
			<td nowrap valign="top">
				<span class="visu_verdanableu7">Encadrant(s) <?php echo $GLOBALS['acronymelabo'] ?> :</span>
			</td>
			<td>
				<?php   
				$query_rs_dir="select nom, prenom, email from sujetdir,individu".
											" where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
											" order by sujetdir.numordre";
				$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
				$first=true;
				while($row_rs_dir=mysql_fetch_assoc($rs_dir))
				{ $first?" ":'<span class="visu_verdanableu7">, </span>';
					?>
					<span class="visu_verdananoir7"><?php echo $row_rs_dir['prenom']." ".$row_rs_dir['nom'] ?> - <?php echo $row_rs_dir['email'] ?></span>
				<?php   
					$first=false;
				} ?>
			</td>
		</tr>
			<?php 
    if($row_rs_sujet['autredir1']!="")
    { ?> 
    <tr>
      <td nowrap valign="top">
        <span class="visu_verdanableu7">Autre(s) encadrant(s) : </span>
      </td>
      <td>
        <span class="visu_verdananoir7"><?php echo $row_rs_sujet['autredir1'] ?></span>
        <span class="visu_verdananoir7"><?php echo ($row_rs_sujet['autredir2']!=""?'<span class="visu_verdanableu7">, </span><span class="visu_verdananoir7">'.$row_rs_sujet['autredir2'].'</span>':"") ?></span></td>
    </tr>
    	<?php 
		} 
		$tab_domainescientifique=array();
		$query_rs =	"SELECT codedomainescientifique,liblongdomainescientifique as libdomainescientifique".
								" FROM sujet_domainescientifique ".
								" order by numordre";
		$rs = mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_domainescientifique[$row_rs['codedomainescientifique']]=$row_rs;
		}
		$tab_disciplinescientifique=array();
		$query_rs =	"SELECT codedisciplinescientifique,codedomainescientifique,liblongdisciplinescientifique as libdisciplinescientifique".
								" FROM sujet_disciplinescientifique". //where codedisciplinescientifique<>''
								" order by numordre";
		$rs = mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_disciplinescientifique[$row_rs['codedisciplinescientifique']]=$row_rs;
		}
		$tab_objectifscientifique=array();
		$query_rs =	"SELECT codeobjectifscientifique,codedomainescientifique,liblongobjectifscientifique as libobjectifscientifique".
								" FROM sujet_objectifscientifique ".//where codeobjectifscientifique<>''
								" order by numordre";
		$rs = mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_objectifscientifique[$row_rs['codeobjectifscientifique']]=$row_rs;
		}
		?>
    <tr>
      <td valign="top"><span class="visu_verdanableu7">Domaine
      </td>
        <td class="visu_verdananoir7">
        <?php 
        for($i=1;$i<=2;$i++)
        { echo $tab_domainescientifique[$row_rs_sujet['codedomainescientifique'.$i]]['libdomainescientifique'];
					echo ($i==1 && $row_rs_sujet['codedomainescientifique2']!='')?',':'';
        }?>
      </td>
    </tr>
    <tr>
      <td class="visu_verdanableu7">Discipline
      </td>
      <td class="visu_verdananoir7">
				<?php 
        for($i=1;$i<=2;$i++)
        { echo $tab_disciplinescientifique[$row_rs_sujet['codedisciplinescientifique'.$i]]['libdisciplinescientifique'];
					echo ($i==1 && $row_rs_sujet['codedisciplinescientifique2']!='')?',':'';
        }?>
      </td>
    </tr>
    <tr>
      <td class="visu_verdanableu7">Objectif
      </td>
      <td class="visu_verdananoir7">
				<?php 
        for($i=1;$i<=2;$i++)
        { echo $tab_objectifscientifique[$row_rs_sujet['codeobjectifscientifique'.$i]]['libobjectifscientifique'];
					echo ($i==1 && $row_rs_sujet['codeobjectifscientifique2']!='')?',':'';
        }?>
      </td>
    </tr>
    <tr>
      <td class="visu_verdanableu7">Autre objectif
      </td>
      <td class="visu_verdananoir7">
				<?php 
        for($i=1;$i<=2;$i++)
        { echo $tab_objectifscientifique[$row_rs_sujet['codeobjectifscientifiqueautre'.$i]]['libobjectifscientifique'];
					echo ($i==1 && $row_rs_sujet['codeobjectifscientifiqueautre2']!='')?',':'';
        }?>
      </td>
    </tr>
    <tr>
      <td  class="visu_verdanableu7">Autre Objectif
      </td>
      <td class="visu_verdananoir7">
				<?php 
        for($i=1;$i<=2;$i++)
        { echo $row_rs_sujet['autreobjectifscientifique'.$i];
				 	echo ($i==1 && $row_rs_sujet['autreobjectifscientifique2']!='')?',':'';
        }?>
      </td>
    </tr>
    <tr>
      <td nowrap valign="top">
        <span class="visu_verdanableu7">Description : </span>
      </td>
      <td align="justify">
        <span class="visu_verdananoir7"><?php echo nl2br($row_rs_sujet['descr_fr']) ?></span>
      </td>
    </tr>
    <?php if($row_rs_sujet['codetypesujet']!='05')
    {?> <tr>
      <td nowrap valign="top">
        <span class="visu_verdanableu7">Mots-clés : </span>
      </td>
      <td>
        <span class="visu_verdananoir7"><?php echo nl2br($row_rs_sujet['motscles_fr']) ?></span>
      </td>
    </tr>
    <tr>
      <td valign="top" nowrap>
        <span class="visu_verdanableu7">Conditions : </span>
      </td>
      <td>
        <span class="visu_verdananoir7"><?php echo nl2br($row_rs_sujet['conditions_fr']) ?></span>
      </td>
    </tr>
    <tr>
      <td nowrap>
        <span class="visu_verdanableu7">Financement : </span>
      </td>
      <td>
        <span class="visu_verdananoir7"><?php echo nl2br($row_rs_sujet['financement_fr']) ?></span>
      </td>
    </tr>
    <?php 
		}?>
  </table>
	<?php 
	if(isset($rs_dir))mysql_free_result($rs_dir);
	if(isset($rs_sujet))mysql_free_result($rs_sujet);
  }
}?>

</body>
</html>