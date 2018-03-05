<?php require_once('_const_fonc.php');?>



  <?php		/* foreach($_REQUEST as $key=>$val)
      { echo $key.'=>'.$val;
      } */
		
      $hashLib=Array();
      $clausewhere="";//par defaut la recherche se fera sans critere dans individu
      
      $appel=(isset($_REQUEST['appel'])?$_REQUEST['appel']:"annuaire");
      $codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
      $codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");
      
      // recup des libelles des zones de texte de la page
      $query_rslib= "select nomzone,contenuzone from libpagehtml ".
                    " where (codepagehtml='listeindividupublic' or codepagehtml='TOUTES') and codelangue=".GetSQLValueString(strtolower($codelangue), "text").
                    " order by nomzone";
      $rslib=mysql_query($query_rslib) or die(mysql_error());
      while($row_rslib=mysql_fetch_assoc($rslib))
      { $hashLib[$row_rslib['nomzone']]=$row_rslib['contenuzone'];
      }
			$clausewhere.=" and codetheme like ".GetSQLValueString($codetheme.'%', "text");
			$query_rs="select * from individu,individutheme where individu.codeindividu<>'' and individu.codeindividu=individutheme.codeindividu ".$clausewhere;
			$rs=mysql_query($query_rs) or die(mysql_error());
			if($row_rs=mysql_fetch_assoc($rs))
			{?> 
  <table cellpadding="2">
    <tr class="head">
      <td nowrap align="center" class="bleucalibri11"><?php echo $codelangue=='FR'?'Envoyer un mail':'Send a mail' ?></td>
      <td nowrap align="center" class="bleucalibri11"><?php echo $codelangue=='FR'?'Nom':'Name' ?></td> 
      <td nowrap align="center" class="bleucalibri11"><?php echo $codelangue=='FR'?'Fonction':'Function' ?></td> 
      <td nowrap align="center" class="bleucalibri11"><?php echo $codelangue=='FR'?'T&eacute;l&eacute;phone':'Phone' ?></td>
      <td nowrap align="center" class="bleucalibri11"><?php echo $codelangue=='FR'?'Lieu':'Office' ?></td>
      </tr>
    <?php
				// on affiche un individu que s'il est autorise
				// tous les visas de tous les individus,sejours
				$query_rs="select codeindividu,numsejour,coderole".
									" from individustatutvisa,statutvisa".
									" where  individustatutvisa.codestatutvisa=statutvisa.codestatutvisa order by codeindividu,numsejour";
				$rs= mysql_query($query_rs) or die(mysql_error());
				while($row_rs=mysql_fetch_assoc($rs))
				{ $tab_tout_individustatutvisa[$row_rs['codeindividu']][$row_rs['numsejour']][$row_rs['coderole']]=$row_rs['coderole'];
				}

				// liste ordonnee des individus,sejours dans le temps avec dates de sejour, date autorisation 
				$query_rs="select codeindividu, numsejour,datedeb_sejour_prevu,datefin_sejour_prevu, datedeb_sejour, datefin_sejour, date_autorisation from individusejour order by codeindividu,datedeb_sejour";
				$rs=mysql_query($query_rs) or die(mysql_error());
				while($row_rs=mysql_fetch_assoc($rs))
				{ $tab_dates_sejour[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
				}
 
				$query_rs="select distinct individu.codeindividu,individusejour.numsejour,nom,prenom,titrepers_".strtolower($codelangue)." as titre,".
									" fonction_".strtolower($codelangue)." as fonction,tel,autretel,email,codelibcat,cat.libcat_".strtolower($codelangue)." as libcat, codetypestage,".
									" lieu.codelieu, lieu.libcourtlieu as liblieu,date_autorisation,datedeb_sejour_prevu,datefin_sejour_prevu".
									" from individu,individusejour,individutheme,corps,cat,lieu ".
									" where individu.codeindividu=individusejour.codeindividu and individusejour.codeindividu=individutheme.codeindividu".
									" and individusejour.numsejour=individutheme.numsejour".
									" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
									" and ".periodeencours('individutheme.datedeb_theme','individutheme.datefin_theme').
									" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
									" and individu.codelieu=lieu.codelieu and individu.codeindividu<>'' ".
									" and afficheannuaire<>'non' and (individusejour.codeindividu,individusejour.numsejour) in (select codeindividu, numsejour from individustatutvisa where codestatutvisa='03')".
									 $clausewhere.
									" order by nom,prenom";
				$rs=mysql_query($query_rs) or die(mysql_error());
				$class="even";
				while($row_rs=mysql_fetch_assoc($rs))
				{ $demander_autorisation=false;
					if($row_rs['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$row_rs['codeindividu']]['numsejour']['srhue']))
					{ $tab_demander_autorisation=demander_autorisation($row_rs,$tab_dates_sejour[$row_rs['codeindividu']]);
						$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
					}
					if(!$demander_autorisation)
					{ ?>
    <tr class="<?php ($class=="even"?$class="odd":$class="even"); echo $class; ?>">
      <td align="center">
        <?php if($row_rs['email']!="")
                {?>
        <a href="mailto:<?php echo $row_rs['email'] ?>">
          <img src="images/b_mail.png"  width="16" height="16" border="0" alt="<?php echo $row_rs['email'] ?>"></a>
        <?php }?>
        </td>
      <td>
        <a href="detailindividupublic.php?appel=<?php echo $appel ?>&codetheme=<?php echo $codetheme ?>&codeindividu=<?php echo $row_rs['codeindividu'] ?>&codelangue=<?php echo $codelangue ?>">
          <?php echo $row_rs['nom'] ?> <?php echo $row_rs['prenom'] ?> <?php echo ($row_rs['titre']==''?'':'('.$row_rs['titre'].')') ?>
          </a>
        </td>
      <td>
        <?php echo ($row_rs['fonction']==''?$row_rs['libcat']:$row_rs['fonction']) ?>
        </td>
      <td><?php
							foreach(array('tel','autretel') as $champtel)
							{ $tel=trim(str_replace('.',' ',$row_rs[$champtel]));
								$tel=trim(str_replace('-',' ',$tel));
								if($tel!='')
								{ if(substr($tel,0,3)!='+33')
									{ if(substr($tel,0,1)=='0')
										{ $tel='+33 (0)'.substr($tel,1);
										}
									}
									else if(substr($tel,0,6)=='+33(0)')
									{ $tel='+33 (0)'.substr($tel,6);
									}
									if(substr($tel,0,7)=='+33 (0)')
									{ $fintel=str_replace(' ','',substr($tel,7));
									//echo $fintel;
										$fintel=substr($fintel,0,1).' '.substr($fintel,1,2).' '.substr($fintel,3,2).' '.substr($fintel,5,2).' '.substr($fintel,7,2);
										$tel='+33 (0)'.$fintel;
									}
								}
								if($champtel=='tel')
								{ echo ' '.$tel;
								}
								else
								{ if($row_rs['tel']!='')
									{ echo '<br>'.$tel;
									}
								}
							}
							?>
        </td>
      <td><?php echo ($row_rs['liblieu']==''?' ':$row_rs['liblieu']) ?>
        </td>
      </tr>
    <?php 
					}
				}?>
    </table>
  <?php 
			}//fin tableau du resultat requete
      if(isset($rslib)) {mysql_free_result($rslib);}
      if(isset($rs)) {mysql_free_result($rs);}?>
