<?php require_once('_const_fonc.php'); ?>
<?php 
$codeuser=deconnecte_ou_connecte();
$format=isset($_GET['format'])?$_GET['format']:'html';
if($format=='html')
{?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html>
  <head>
  <title>Sujets <?php echo $GLOBALS['acronymelabo'] ?></title>
  <meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
  <link rel="icon" type="image/png" href="images/12plus.ico">
  <link rel="stylesheet" href="styles/normal.css" type="text/css">
  <link href="styles/tableau_bd.css" rel="stylesheet" type="text/css">
  <link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
  <script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
  </head>
  <body>
  <table border="1" cellpadding="3">
  <tr><td>N°</td><td>Dates</td><td>Dept.</td><td>Financement</td><td>Titre</td><td>Directeurs</td><td>Autres directeurs</td><td>Sujet</td>
  <td>Mots cles</td><td>ref publis</td><td>ref publis ext</td><td>conditions</td></tr>
  <?php
  $class="";
  $query_rs_sujet="select sujet.*,  statutsujet.libstatutsujet as libstatutsujet".
                  " from sujet, statutsujet  ".
                  " where sujet.codetypesujet='03' and sujet.codestatutsujet=statutsujet.codestatutsujet".
                  "  and datedeb_sujet>='2017/09/01'";
  $rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
  while($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
  {	$codesujet=$row_rs_sujet['codesujet'];
    $query_rs_dir="select codeindividu, nom, prenom from sujetdir,individu".
                  " where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
                  " order by sujetdir.numordre";
    $class=($class=='even'?'odd':'even');
    ?>
      <tr class=<?php echo $class ?>>
        <td><?php echo $row_rs_sujet['codesujet']; ?>
        </td>
        <td nowrap><?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datedeb_sujet'],'/'); ?> - <?php echo aaaammjj2jjmmaaaa($row_rs_sujet['datefin_sujet'],'/'); ?>
        </td>
        <td>
        	<?php $query_rs="select structure.libcourt_fr".
                        " from sujettheme,structure ".
                        " where sujettheme.codesujet=".GetSQLValueString($row_rs_sujet['codesujet'], "text").
                        " and sujettheme.codetheme=structure.codestructure order by sujettheme.codetheme";
        $rs=mysql_query($query_rs) or die(mysql_error());
        while($row_rs=mysql_fetch_assoc($rs))
        { echo $row_rs['libcourt_fr'].' ';
        }?>
        </td>
        <td><?php echo $row_rs_sujet['financement_fr']==''?'-':$row_rs_sujet['financement_fr']; ?>
        </td>
        <td><?php echo $row_rs_sujet['titre_fr']; ?>
        </td>
        <td nowrap>
        <?php
        $rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
        while($row_rs_dir=mysql_fetch_assoc($rs_dir))
        { echo $row_rs_dir['prenom']." ".$row_rs_dir['nom']." ";
        } ?>
        </td>
        <td>
        <?php if($row_rs_sujet['autredir1']!="")
        { echo $row_rs_sujet['autredir1'];
          echo ($row_rs_sujet['autredir2']!=""?', '.$row_rs_sujet['autredir2']:"");
        }
				else
				{ echo '-';
				}?>
        </td>
        <td><?php echo $row_rs_sujet['descr_fr']; ?>
        </td>
        <td><?php echo $row_rs_sujet['motscles_fr']==''?'-':$row_rs_sujet['motscles_fr']; ?>
        </td>
        <td><?php echo $row_rs_sujet['ref_publis']==''?'-':$row_rs_sujet['ref_publis']; ?>
        </td>
        <td><?php echo $row_rs_sujet['ref_publis_ext']==''?'-':$row_rs_sujet['ref_publis_ext']; ?>
        </td>
        <td><?php echo $row_rs_sujet['conditions_fr']==''?'-':$row_rs_sujet['conditions_fr']; ?>
        </td>
        <!--<td>
        <?php /* $query_rs="select individu.codeindividu,nom,prenom".
                        " from individusujet,individu,individusejour ".
                        " where individusujet.codesujet=".GetSQLValueString($row_rs_sujet['codesujet'], "text").
                        " and individusujet.codeindividu=individusejour.codeindividu and individusujet.numsejour=individusejour.numsejour".
                        " and individusejour.codeindividu=individu.codeindividu";
        $rs=mysql_query($query_rs) or die(mysql_error());
        while($row_rs=mysql_fetch_assoc($rs))
        { echo str_replace(' ','&nbsp;',$row_rs['prenom'].' '.$row_rs['nom']);
        }  */?>
        </td> -->
      </tr>
  
	<?php
  } ?> 
</table>
</body></html> 
<?php
} 
else
{ header('Content-type: text/plain');
 
echo "N°"."\t"."Titre"."\t"."sujet"."\t"."Directeurs"."\t"."Autres directeurs"."\t"."Etudiant"."\t"."\n";
  $query_rs_sujet="select sujet.*,  statutsujet.libstatutsujet as libstatutsujet".
                  " from sujet, statutsujet,individusujet  ".
                  " where sujet.codetypesujet='03' and sujet.codestatutsujet=statutsujet.codestatutsujet".
                  " and sujet.codesujet=individusujet.codesujet";
  $rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
  while($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
  {	$codesujet=$row_rs_sujet['codesujet'];
    $query_rs_dir="select codeindividu, nom, prenom from sujetdir,individu".
                  " where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
                  " order by sujetdir.numordre";
    echo $row_rs_sujet['codesujet']."\t".$row_rs_sujet['titre_fr'].'\t'.$row_rs_sujet['descr_fr'];
		echo "\t";
		$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
		while($row_rs_dir=mysql_fetch_assoc($rs_dir))
		{ echo $row_rs_dir['prenom']." ".$row_rs_dir['nom']." ( ".$row_rs_dir['codeindividu']." ) ";
		} 
		echo "\t";
		if($row_rs_sujet['autredir1']!="")
		{ echo $row_rs_sujet['autredir1'];
			echo ($row_rs_sujet['autredir2']!=""?', '.$row_rs_sujet['autredir2']:"");
		}
		echo "\t";
		$query_rs="select individu.codeindividu,nom,prenom".
										" from individusujet,individu,individusejour ".
										" where individusujet.codesujet=".GetSQLValueString($row_rs_sujet['codesujet'], "text").
										" and individusujet.codeindividu=individusejour.codeindividu and individusujet.numsejour=individusejour.numsejour".
										" and individusejour.codeindividu=individu.codeindividu";
		$rs=mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ echo $row_rs['prenom'].' '.$row_rs['nom'];
		} 
		echo "\t";
		echo "\n";
  }
}?> 
<?php
if(isset($rs))mysql_free_result($rs);
if(isset($rs_dir))mysql_free_result($rs_dir);
if(isset($rs_sujet)) mysql_free_result($rs_sujet);
?>






