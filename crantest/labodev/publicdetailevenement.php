<?php require_once('_const_fonc.php');
?><div class="titre_page_dept">Soutenance</div>
    <div>
      
      <?php 
		$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
		$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");
		$codelibtypeactuevenement=(isset($_REQUEST['codelibtypeactuevenement'])?$_REQUEST['codelibtypeactuevenement']:"");
		$codeindividu=(isset($_REQUEST['codeindividu'])?$_REQUEST['codeindividu']:"");
		$numsejour=(isset($_REQUEST['numsejour'])?$_REQUEST['numsejour']:"");

		if($codelibtypeactuevenement=='these')
		{ $query_rs="select distinct individu.codeindividu,individuthese.numsejour,nom,prenom,".
								" date_soutenance as date, heure_soutenance as heure, lieu_soutenance as lieu, titre_these as titre, resume_these as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement,".
								" jury_rapp1_these, jury_rapp2_these, jury_autres_membres_these".
								" from individu,individuthese,typeactuevenement ".
								" where individu.codeindividu=individuthese.codeindividu".
								" and codelibtypeactuevenement='these' and individu.codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
		}
		else if($codelibtypeactuevenement=='hdr')
		{ $query_rs=" select individu.codeindividu,nom,prenom,".
                " date_hdr as date, heure_hdr as heure, lieu_hdr as lieu, titre_hdr as titre, resume_hdr as resume,".
								" typeactuevenement.codelibtypeactuevenement, typeactuevenement.libcourt_".strtolower($codelangue)." as libtypeactuevenement".
                " from individu,typeactuevenement".
								" where hdr='oui' and date_hdr<>'' and codelibtypeactuevenement='hdr' and codeindividu=".GetSQLValueString($codeindividu, "text");
		}

    $rs=mysql_query($query_rs) or die(mysql_error()); ?>
      <?php if($row_rs=mysql_fetch_assoc($rs))
		{ ?>
      <table width="100%" border="0" >
        <tr>
          <td nowrap><a href="JavaScript:history.go(-1)"><img src="images/fleche_haut_page.gif" border="0"></a> <b>Le</b> <?php  echo aaaammjj2jjmmaaaa($row_rs['date'],'/') ?><?php  echo $row_rs['heure']!=''?' - '.$row_rs['heure']:'' ?><?php  echo $row_rs['lieu']!=''?' - '.$row_rs['lieu']:'' ?>
            </td>
          </tr>
        <tr>
          <td>
            </td>
          </tr>
        <tr>
          <td align="center"><b>"<?php echo $row_rs['titre'] ?>"</b>
            </td>
          </tr>
        <tr>
          <td nowrap align="center">
            (<?php echo $row_rs['libtypeactuevenement'] ?> <?php echo $row_rs['prenom'] ?>&nbsp;<?php echo $row_rs['nom'] ?>)
            </td>
          </tr>
        <?php if($row_rs['resume']!='')
        {?> 
        <tr>
          <td  align="justify"><br/><b>R&eacute;sum&eacute; :</b>
            <br/><?php echo nl2br($row_rs['resume']) ?>
            </td>
          </tr>
        <?php 
				}
        if($codelibtypeactuevenement=='these')
        {?> 
        <tr>
          <td>
            <table>
              <tr>
                <td colspan="2"><b>Jury :</b>
                  </td>
                </tr>
              <tr>
                <td><b>- Rapporteurs : </b>
                  </td>
                <td><?php echo $row_rs['jury_rapp1_these'] ?>
                  </td>
                </tr>
              <tr>
                <td>
                  </td>
                <td><?php echo $row_rs['jury_rapp2_these'] ?>
                  </td>
                </tr>
              <tr>
                <td valign="top" nowrap><b>- Autres membres : </b>
                  </td>
                <td><?php echo nl2br($row_rs['jury_autres_membres_these']) ?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        <?php 
				} /* */
				?>
        </table>
      <?php 
		} 
		if(isset($rs)) mysql_free_result($rs);
		?>
    </div>
