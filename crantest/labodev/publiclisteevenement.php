<?php require_once('_const_fonc.php');

    $codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
    ?>
    <div class="titre_page_dept"><?php if($codelangue=="FR"){?>Calendrier de soutenances de th&egrave;ses et HDR (<a href="archives.php">Archives</a>)<?php }else{?>Events/Conferences<?php }?></div>
  
    <div>
      
      <table width="100%" cellpadding="2" cellspacing="2">
        <?php 
			$tab_typeevent=array('These'=>array('EN'=>'Phd Thesis','FR'=>'Th&egrave;se'),'HDR'=>array('EN'=>'HDR','FR'=>'HDR'));
      $tab_themeindividu=array();
      $query_rs="(select individusejour.codeindividu as codeindividu, individusejour.numsejour as numsejour, libcourt_fr as libtheme, structure.date_deb as date_deb".
                " from individusejour, individutheme, structure".
								" where individusejour.codeindividu=individutheme.codeindividu".
                " and individusejour.numsejour=individutheme.numsejour and individutheme.codetheme=structure.codestructure".
                " and individusejour.codecorps='51' and structure.esttheme='oui' and structure.codelib<>'du'".
                " and ".intersectionperiodes('datedeb_sejour','datefin_sejour','structure.date_deb','structure.date_fin').
								" )".
								" union".
								" (select individusejour.codeindividu as codeindividu, individusejour.numsejour as numsejour, libcourt_fr as libtheme, structure.date_deb as date_deb".
								" from individu,individusejour, individutheme, structure".
								" where individu.codeindividu=individusejour.codeindividu and individusejour.codeindividu=individutheme.codeindividu".
                " and individusejour.numsejour=individutheme.numsejour and individutheme.codetheme=structure.codestructure".
                " and individu.hdr='oui' and structure.esttheme='oui' and structure.codelib<>'du'".
                " and ".intersectionperiodes('date_hdr','date_hdr','structure.date_deb','structure.date_fin').
								" )".								
                " order by codeindividu, numsejour, date_deb desc,libtheme asc";
                //echo $query_rs;
      $rs=mysql_query($query_rs) or die(mysql_error());
      while($row_rs=mysql_fetch_assoc($rs))
      { if(isset($tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]))
        { if($row_rs['date_deb']==$date_deb_structure)
          { $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']].=", ".$row_rs['libtheme'];
          }
        }
        else
        { $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs['libtheme'];
          $date_deb_structure=$row_rs['date_deb'];
        }
      }
      $query_rs="(select individu.codeindividu,individuthese.numsejour,nom,prenom,".
                " date_soutenance as date, heure_soutenance as heure, lieu_soutenance as lieu, titre_these as titre, resume_these as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,individuthese,typeactuevenement ".
                " where individu.codeindividu=individuthese.codeindividu".
                " and date_soutenance<>'' and titre_these<>'' and codelibtypeactuevenement='these'".
								" and substring(date_soutenance,1,4)>=".GetSQLValueString(date("Y")-3, "text").
								" )".
								" union".
								" (select individu.codeindividu,individusejour.numsejour as numsejour,nom,prenom,".
                " date_hdr as date, heure_hdr as heure, lieu_hdr as lieu, titre_hdr as titre, resume_hdr as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,individusejour,typeactuevenement".
								" where hdr='oui' and date_hdr<>'' and individu.codeindividu=individusejour.codeindividu and codelibtypeactuevenement='hdr'".
								" and ".intersectionperiodes('date_hdr','date_hdr','datedeb_sejour','datefin_sejour').
								" and substring(date_hdr,1,4)>=".GetSQLValueString(date("Y")-3, "text").
								" )".
								" order by date desc, heure desc";
      $rs=mysql_query($query_rs) or die(mysql_error());
			$class="even";
      while($row_rs=mysql_fetch_assoc($rs))
      { ?>
        <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
          <td align="center"><?php  echo aaaammjj2jjmmaaaa($row_rs['date'],'/') ?>
            </td>
          <td align="center"><?php  echo $row_rs['heure'] ?>
            </td>
          <td align="center"><?php  echo $row_rs['lieu'] ?>
            </td>
          <td><?php  echo $row_rs['libtypeactuevenement'] ?>
            </td>
          <td><?php  echo $row_rs['titre'] ?>
            </td>
          <td align="center"><?php  echo $row_rs['prenom'] ?> <?php  echo $row_rs['nom'] ?>
            </td>
          <td align="center">
            <?php  
            if(isset($tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']]))
            { echo $tab_themeindividu[$row_rs['codeindividu']][$row_rs['numsejour']];
            } ?>
            </td>
          <td align="center"><?php if($row_rs['resume']!='')
            { ?><a href="detailevenementpublic.php?codeindividu=<?php echo $row_rs['codeindividu'] ?>&numsejour=<?php echo $row_rs['numsejour'] ?>&codelangue=<?php echo $codelangue ?>&codelibtypeactuevenement=<?php echo $row_rs['codelibtypeactuevenement'] ?>">R&eacute;sum&eacute;</a>
            <?php 
            }?> 
            </td>
          </tr>
        <?php 
      }?>
        </table>
      <?php 
    if(isset($rs)) {mysql_free_result($rs);}?>
    </div>
