<?php require_once('_const_fonc.php');

$hashLib=array();

$appel=(isset($_REQUEST['appel'])?$_REQUEST['appel']:"form_rech");
$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
$codesujet=(isset($_REQUEST['codesujet'])?$_REQUEST['codesujet']:"");
$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");

// recup des libelles des zones de texte de la page
$query_rslib= "select nomzone,contenuzone from libpagehtml ".
							" where (codepagehtml='detailsujetpublic' or codepagehtml='TOUTES') and codelangue=".GetSQLValueString(strtolower($codelangue), "text").
							" order by nomzone";
$rslib=mysql_query($query_rslib) or die(mysql_error());
while($row_rslib=mysql_fetch_assoc($rslib))
{ $hashLib[$row_rslib['nomzone']]=$row_rslib['contenuzone'];
}?>
<?php
// selection de l'enregistrement de ce sujet
$query_rs_sujet= " select typesujet.codetypesujet,codecreateur,titre_".strtolower($codelangue)." as titresujet,codestatutsujet,sujet.codetypesujet,".
								 " typesujet.libcourt_".strtolower($codelangue)." as libtypesujet,".
								 " conditions_".strtolower($codelangue)." as conditions,".
								 " motscles_".strtolower($codelangue)." as motscles,descr_".strtolower($codelangue)." as descr,".
								 " autredir1,autredir1mail,autredir2,autredir2mail,datedeb_sujet,datefin_sujet,".
								 " financement_".strtolower($codelangue)." as financement,ref_publis,ref_publis_ext".
								 " from sujet,typesujet ".
								 " where sujet.codetypesujet=typesujet.codetypesujet and codesujet=".GetSQLValueString($codesujet, "text");
$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
{ $query_rs= "select individu.codeindividu,nom, prenom,datedeb_sejour,datefin_sejour".
						 " from individusujet,individusejour,individu,individustatutvisa,statutvisa". 
						 " where individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
						 " and individusejour.codeindividu=individu.codeindividu".
						 " and individusujet.codesujet =".GetSQLValueString($codesujet, "text").
						 " and individusejour.codeindividu=individustatutvisa.codeindividu".
						 " and individusejour.numsejour=individustatutvisa.numsejour".
						 " and individustatutvisa.codestatutvisa=statutvisa.codestatutvisa".
						 " and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
						 " and statutvisa.coderole='theme'";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$affecte=false;
	if($row_rs=mysql_fetch_assoc($rs))
	{ $affecte=true;
		$row_rs_sujet=array_merge($row_rs_sujet,$row_rs);
	}
	$libtypesujet=($row_rs_sujet['codetypesujet']=='02'?(strtolower($codelangue)=='fr'?'stage':'Trainee'):$row_rs_sujet['libtypesujet']) ?>
  <div class="titre_page_dept"><?php echo $codelangue=='FR'?'Sujet de '.$libtypesujet:$libtypesujet.' Project' ?> : <?php echo $row_rs_sujet['titresujet'] ?></div>
  <div align="justify" style="border-left: solid 1px #CCC;border-right: solid 1px #CCC;border-bottom: solid 1px #CCC">
    <table width="100%" border="0">
      <tr>
        <td valign="top" nowrap class="bleucalibri11">
          Dates&nbsp;:
          </td>
        <td>
          <?php
            $datedeb_sujet=$row_rs_sujet['datedeb_sujet']; 
						$datefin_sujet=$row_rs_sujet['datefin_sujet'];
						if($affecte)
						{ $datedeb_sejour=$row_rs_sujet['datedeb_sejour'];
							$datefin_sejour=$row_rs_sujet['datefin_sejour'];
							if($row_rs_sujet['codetypesujet']=='04' && $datefin_sujet!='')//postdoc
							{ $duree_sujet=mktime(0,0,0,substr($datefin_sujet,5,2),substr($datefin_sujet,8,2),substr($datefin_sujet,0,4))-mktime(0,0,0,substr($datedeb_sujet,5,2),substr($datedeb_sujet,8,2),substr($datedeb_sujet,0,4));
								$datefin_sujet=min($datefin_sejour,date("Y/m/d",mktime(0,0,0,substr($datedeb_sejour,5,2),substr($datedeb_sejour,8,2),substr($datedeb_sejour,0,4))+$duree_sujet));
							}
							else
							{ $datefin_sujet=$row_rs_sujet['datefin_sejour'];
							}
							$datedeb_sujet=$datedeb_sejour;
						}
				echo $datedeb_sujet?> - <?php echo $datefin_sujet ?>
          </td>
        </tr>
      <?php 		
		if($affecte)
    {
		?>
      <tr>
        <td nowrap class="bleucalibri11">
          <?php echo $hashLib['etudiant'] ?>
          </td>
        <td>
          
          <a href="detailindividupublic.php?appel=<?php echo $appel ?>&codetheme=<?php echo $codetheme ?>&codelangue=<?php echo $codelangue ?>&codeindividu=<?php echo $row_rs_sujet['codeindividu'] ?>">
            <?php echo $row_rs_sujet['prenom'] ?> <?php echo $row_rs_sujet['nom'] ?></a>
          
          </td>
        </tr>
      <?php 
		}
		//Directeurs ou contacts
		$query_rs="select codedir,titrepers_".strtolower($codelangue)." as titredir,nom as nomdir,prenom as prenomdir".
							" from sujetdir,individu where codesujet=".GetSQLValueString($codesujet, "text").
							" and sujetdir.codedir=individu.codeindividu ".
							" order by numordre";
		$rs=mysql_query($query_rs) or die(mysql_error());
		?>
      <tr>
        <td valign="top" nowrap class="bleucalibri11">
          <?php echo ($row_rs_sujet['codetypesujet']=="02" || $row_rs_sujet['codetypesujet']=="03")?$hashLib['directeurscran']:$hashLib['contacts'];?>
          
          </td>
        <td>
          <?php 
					$first=true;
          while($row_rs=mysql_fetch_assoc($rs))
          { echo $first?'':', ';
						$first=false; ?>
          <a href="detailindividupublic.php?appel=<?php echo $appel ?>&codetheme=<?php echo $codetheme ?>&codelangue=<?php echo $codelangue ?>&codeindividu=<?php echo $row_rs['codedir'] ?>">
            <?php echo $row_rs['prenomdir'].' '.$row_rs['nomdir'] ?></a>
          <?php 
          } ?>
          </td>
        </tr>
      <tr>
        <td valign="top" nowrap class="bleucalibri10">
          <?php echo (($row_rs_sujet['autredir1']!="" || $row_rs_sujet['autredir2']!="")?(
															($row_rs_sujet['codetypesujet']=="02"||$row_rs_sujet['codetypesujet']=="03")?
																															$hashLib['directeursautre']:$hashLib['contactsautre']):"");
				?>
          
          </td>
        <td>
          <?php 
        if($row_rs_sujet['autredir1']!="")
        {  echo $row_rs_sujet['autredir1']; 
           if($row_rs_sujet['autredir1mail']!="")
           { ?>&nbsp;(<a href="mailto:<?php echo $row_rs_sujet['autredir1mail'] ?>"><?php echo $row_rs_sujet['autredir1mail'] ?></a>)
          <?php 
           } 
        } 
				if($row_rs_sujet['autredir2']!="")
				{ echo ', '.$row_rs_sujet['autredir2']; 
					if($row_rs_sujet['autredir2mail']!="")
					{ ?>&nbsp;(<a href="mailto:<?php echo $row_rs_sujet['autredir2mail'] ?>"><?php echo $row_rs_sujet['autredir2mail'] ?></a>)
          <?php 
					} 				
				} ?>
          </td>
        </tr>
      <tr>
        <td valign="top" class="bleucalibri10"><?php echo $hashLib['descr'] ?>
          </td>
        <td align="justify"><?php echo nl2br($row_rs_sujet['descr']) ?>
          </td>
        </tr>
      <tr>
        <td valign="top" class="bleucalibri10"><?php echo $hashLib['motscles'] ?>
          </td>
        <td><?php echo $row_rs_sujet['motscles'] ?>
          </td>
        </tr>
      <?php 
		if($row_rs_sujet['conditions']!='')
		{ ?>
      <tr>
        <td valign="top" class="bleucalibri10"><?php echo $hashLib['conditions'] ?>
          </td>
        <td align="justify"><?php echo nl2br($row_rs_sujet['conditions']) ?>
          </td>
        </tr>
      <?php 
		}
		$query_rs ="select liblong_".strtolower($codelangue)." as libtheme".
							 " from sujettheme,structure where codesujet=".GetSQLValueString($codesujet, "text").
							 " and ".periodeencours('structure.date_deb','structure.date_fin').
							 " and sujettheme.codetheme=structure.codestructure";
		$rs=mysql_query($query_rs) or die(mysql_error());
		 ?>
      <tr>
        <td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['themes'] ?>
          </td>
        <td>
          <table border="0" cellspacing="0" cellpadding="0">
            <?php 
          while($row_rs=mysql_fetch_assoc($rs))
          { ?>
            <tr>
              <td><?php echo $row_rs['libtheme'] ?>
                </td>
              </tr>
            <?php
          } ?>
            </table>
          </td>
        </tr>
      <?php 
		if($row_rs_sujet['financement']!="")
    { ?>
      <tr>
        <td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['financement'] ?>
          </td>
        <td align="justify"><?php echo $row_rs_sujet['financement'] ?>
          </td>
        </tr>
      <?php 	
		}
		if($row_rs_sujet['ref_publis']!="")
    { $tab_publis= explode (',',$row_rs_sujet['ref_publis']);
			$row_rs_sujet['ref_publis']="";
			$first=true;
			foreach($tab_publis as $une_ref)
			{ if($first)
				{ $first=false;
				}
				else
				{ $row_rs_sujet['ref_publis'].=',';
				}
				$une_ref_lower=trim(strtolower($une_ref));
				if($une_ref_lower!='')
				{ if(strpos($une_ref_lower,'hal-')!==false || strpos($une_ref_lower,'tel-')!==false || strpos($une_ref_lower,'inria-'))
					{ $row_rs_sujet['ref_publis'].='<a href="http://hal.archives-ouvertes.fr/'.$une_ref_lower.'" target="_blank">'.$une_ref.'</a>';
					}
					else
					{ $row_rs_sujet['ref_publis'].=$une_ref;
					}
				}
			}
		?>
      <tr>
        <td valign="top" nowrap class="bleucalibri10"><?php echo $hashLib['ref_publis'] ?>
          </td>
        <td><?php echo $row_rs_sujet['ref_publis'] ?>&nbsp;&nbsp;&nbsp;&nbsp;+ <a href="http://hal.archives-ouvertes.fr/CRAN/<?php echo strtolower($codelangue) ?>" target="_blank">CRAN - Publications</a>
          </td>
        </tr>
      <?php 	
		}
}
 ?>
  </table>
</div>
<?php 
if(isset($rslib)) mysql_free_result($rslib);
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_sujet)) mysql_free_result($rs_sujet);
?>
