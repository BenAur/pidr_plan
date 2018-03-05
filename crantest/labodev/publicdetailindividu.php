<?php require_once('_const_fonc.php');

    $hashLib=array();
		$afficheannuaire='';

		$appel=(isset($_REQUEST['appel'])?$_REQUEST['appel']:"annuaire");
		$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
		$codetheme=(isset($_REQUEST['codetheme'])?$_REQUEST['codetheme']:"");
		$codeindividu=(isset($_REQUEST['codeindividu'])?$_REQUEST['codeindividu']:"");

		$rs_ind=mysql_query("select afficheannuaire from individu where codeindividu=".GetSQLValueString($codeindividu, "text")) or die(mysql_error());
		if($row_rs_ind=mysql_fetch_assoc($rs_ind))
		{ $afficheannuaire=$row_rs_ind['afficheannuaire'];
		}
		// recup des libelles des zones de texte de la page
		$query_rslib= "select nomzone,contenuzone from libpagehtml ".
									" where (codepagehtml='detailindividupublic' or codepagehtml='TOUTES') and codelangue=".GetSQLValueString(strtolower($codelangue), "text").
									" order by nomzone";
		$rslib=mysql_query($query_rslib) or die(mysql_error());
		while($row_rslib=mysql_fetch_assoc($rslib))
		{ $hashLib[$row_rslib['nomzone']]=$row_rslib['contenuzone'];
		} 
		// selection de l'enregistrement de cet individu
		$query_rs_ind="select numsejour,datefin_sejour".
									" from individusejour where codeindividu = ".GetSQLValueString($codeindividu, "text").
									" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour');
		$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
		$personnel_present=true;
		if(!$row_rs_ind=mysql_fetch_assoc($rs_ind))
		{ $clause_where="individusejour.datefin_sejour in (select max(datefin_sejour) from individusejour where codeindividu = ".GetSQLValueString($codeindividu, "text")." )";
			$personnel_present=false;
		}
		else
		{ $clause_where=periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour');
		}
		$query_rs_ind = "SELECT civilite.libcourt_".strtolower($codelangue)." as libciv,individu.*,individusejour.*,".
										" individusujet.codesujet, sujet.titre_".strtolower($codelangue)." as titresujet,sujet.afficher_sujet_web,".
										" corps.liblongcorpsafficheannuaire_".strtolower($codelangue)." as libcorps,cat.codelibcat,libcat_".strtolower($codelangue)." as libcat,".
										" liblonglieu as liblieu,lienlieuhttp,lienlinkedin".
										" FROM civilite,individu,corps,cat,lieu,individusejour ".
										" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
										" left join sujet on individusujet.codesujet=sujet.codesujet and find_in_set(sujet.codetypesujet,'02,03,04')". 
										" WHERE individu.codeindividu=individusejour.codeindividu".
										" and individu.codeciv=civilite.codeciv and individu.codelieu=lieu.codelieu".
										" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
										" and ".$clause_where.
										" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text");
		
		
		$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
		
		if($row_rs_ind=mysql_fetch_assoc($rs_ind))
		{ if($row_rs_ind['afficher_sujet_web']!='oui')
			{ $row_rs_ind['codesujet']='';
				$row_rs_ind['titresujet']='';		
			}
		
			?>
      <div class="div_gauche" style="width:<?php echo file_exists('perso/'.$codeindividu.'/cv.html')?'450':'950' ?>px;">
        <table border="0" >
          <tr>
            <td colspan="2"><a href="JavaScript:history.go(-1)"><img src="images/fleche_haut_page.gif" border="0"></a><img src="images/espaceur.gif" width="10" height="0" border="0"><span class="mauvecalibri12"><?php echo $row_rs_ind['libciv'] ?>&nbsp;<?php echo $row_rs_ind['prenom'] ?>&nbsp;<?php echo $row_rs_ind['nom'] ?></span>
              <span class="noircalibri12"> - <?php echo $row_rs_ind['libcorps'] ?></span>
              <?php 
            if($row_rs_ind['codelibcat']=='EC' && $row_rs_ind['codelibcat']!='CHERCHEUR' && $row_rs_ind['codelibcat']!='PATP' )
            { ?>
              <span class="noircalibri12">&nbsp;(<?php echo $row_rs_ind['libcat'] ?><?php echo (($row_rs_ind['associe']=='oui' && $codelangue=='FR')?' Associ&eacute;':'') ?>)
                </span>
              <?php  
           }?>
              </td>
            </tr>
          <?php if($afficheannuaire!='oui')
    	{?>  
          <tr>
            <td colspan="2" nowrap>
              <span class="grisgrascalibri11">Cette personne n'est pas dans l'annuaire du laboratoire</span>
              </td>
            </tr>
          <?php 
			}
      else
      {?> <tr>
            <td colspan="2" nowrap>
              <?php 
            if(!$personnel_present)
            {?> <span class="griscalibri11">Cette personne ne fait plus partie du laboratoire &agrave; ce jour</span>
              <?php 
            }?>
              </td>
            </tr>
          <tr>
            <td colspan="2">
              
              </td>
            </tr>
          <!-- section cnu ou cnrs -->
          <?php 
        if($row_rs_ind['codesection']!="")
        { $query_rs="select libcourtcommission_".strtolower($codelangue)." as libcommission,numsection, liblongsection_".strtolower($codelangue)." as libsection ".
                    " from commission,commissionsection where commission.codecommission=commissionsection.codecommission and codesection= ".GetSQLValueString($row_rs_ind['codesection'], "text");
          $rs=mysql_query($query_rs) or die(mysql_error());
          if($row_rs=mysql_fetch_assoc($rs))
          { ?> 
          <tr>
            <td nowrap><span class="bleucalibri11">Section : </span></td>
            <td><span class="noircalibri11"><?php echo $row_rs['libcommission'] ?> - <?php echo $row_rs['numsection'] ?> <?php echo $row_rs['libsection'] ?></span>
              </td>
            </tr>
          <?php 
          }
        }
        if($row_rs_ind['codelibcat']=='STAGIAIRE' || $row_rs_ind['codelibcat']=='DOCTORANT' || $row_rs_ind['codelibcat']=='POSTDOC' )
        {?>
          <tr>
            <td nowrap><span class="bleucalibri11">
              <?php 
            if($row_rs_ind['codelibcat']=='DOCTORANT')
            { echo $hashLib['sujetthese'] ; 
            }
            if($row_rs_ind['codelibcat']=='STAGIAIRE')
            { echo strtolower($codelangue)=='fr'?'Sujet de stage : ':'Trainee project:' ;
            }
            else if($row_rs_ind['codelibcat']=='POSTDOC')
            { echo $hashLib['sujetpostdoc'] ; 
            }?>
              </span>
              </td>
            <td>
              <span class="noircalibri11">
                <a href="detailsujetpublic.php?appel=<?php echo $appel ?>&codetheme=<?php echo $codetheme ?>&codelangue=<?php echo $codelangue ?>&codesujet=<?php echo $row_rs_ind['codesujet'] ?>"><?php echo $row_rs_ind['titresujet'] ?>
                </a>
                </span>
              </td>
            </tr>
          <?php   
        }
        if($row_rs_ind['fonction_'.strtolower($codelangue)]!="")
        { ?>
          <tr>
            <td width="14%" nowrap><span class="bleucalibri11"><?php echo $hashLib['fonction'] ?></span></td>
            <td><span class="noircalibri11"><?php echo $row_rs_ind['fonction_'.strtolower($codelangue)] ?></span>
              </td>
            </tr>
          <?php 
        }?>
          <tr>
            <td valign="top" nowrap><span class="bleucalibri11"><?php echo $codelangue=="FR"?'D&eacute;partement':'Department' ?></span></td>
            <td>
              <table border="0" cellspacing="0" cellpadding="0">
                <?php 
            $rs=mysql_query( " select structure.liblong_".strtolower($codelangue)." as libstructure".
                             " from individusejour,individutheme,structure".
                             " where individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour".
                             " and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
                             " and ".periodeencours('individutheme.datedeb_theme','individutheme.datefin_theme').
                             " and ".periodeencours('structure.date_deb','structure.date_fin').
                             " and individusejour.codeindividu = ".GetSQLValueString($codeindividu, "text").
                             " and individusejour.numsejour=.individutheme.numsejour".
                             " and individutheme.codetheme=structure.codestructure") or die(mysql_error());
                             //" group by individusejour.codeindividu")
            while($row_rs=mysql_fetch_assoc($rs))
            { ?>
                <tr>
                  <td><span class="noircalibri11"><?php echo $row_rs['libstructure'] ?></span>
                    </td>
                  </tr>
                <?php 
            } ?>
                </table>
              </td>
            </tr>
          <?php 
        $query_rs = "SELECT libcourtetab_".strtolower($codelangue)." as libetab".
                    " FROM individuemploi, etab".
                    " WHERE individuemploi.codeetab=etab.codeetab".
                    " and ".periodeencours('individuemploi.datedeb_emploi','individuemploi.datefin_emploi').
                    " and individuemploi.codeindividu = ".GetSQLValueString($codeindividu, "text");
        $rs = mysql_query($query_rs) or die(mysql_error());
        if($row_rs=mysql_fetch_assoc($rs))
        {?>
          <tr>
            <td nowrap><span class="bleucalibri11"><?php echo $hashLib['etab'] ?></span></td>
            <td>
              <span class="noircalibri11"><?php echo $row_rs['libetab'] ?></span>
              </td>
            </tr>
          <?php 
        }
				foreach(array('tel','autretel','fax') as $champtel)
				{ $tel=trim(str_replace('.',' ',$row_rs_ind[$champtel]));
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
					$row_rs_ind[$champtel]=$tel;
				}?>
          <tr>
            <td nowrap><img src="images/b_phone.png" border="0">
              </td>
            <td><span class="noircalibri11"><?php echo $row_rs_ind['tel'] ?><?php echo $row_rs_ind['autretel']!=''?(' / '.$row_rs_ind['autretel']):'' ?></span>
              </td>
            </tr>
          <tr>
            <td nowrap><img src="images/b_fax.png" border="0">
              </td>
            <td><span class="noircalibri11"><?php echo $row_rs_ind['fax'] ?></span>
              </td>
            </tr>
          <tr>
            <td nowrap><img src="images/b_mail.png" border="0">
              </td>
            <td><a href="mailto:<?php echo $row_rs_ind['email'] ?>"><span class="noircalibri11"><?php echo $row_rs_ind['email'] ?></span></a>
              </td>
            </tr>
          <tr>
            <td nowrap><span class="bleucalibri11"><?php echo $codelangue=="FR"?'Recherche :':'Research:' ?></span></td>
            <td><span class="noircalibri11">
              <?php 
            if($row_rs_ind['codelieu']!="")
            { if($row_rs_ind['lienlieuhttp']!="")
              { ?><a href="<?php echo $row_rs_ind['lienlieuhttp'] ?>" target="_blank"><span class="noircalibri11"><?php echo $row_rs_ind['liblieu'] ?></span></a>
              <?php 
              }
              else
              { echo $row_rs_ind['liblieu'];
              }
            }
            else
            { echo $row_rs_ind['autrelieu'];
            }?>
              </span>
              </td>
            </tr>
          <?php 
        if($row_rs_ind['lienpghttp']!="")
        { ?>
          <tr>
            <td nowrap><span class="bleucalibri11"><?php echo $hashLib['lienpghttp'] ?></span></td>
            <td>
              <a href="<?php echo $row_rs_ind['lienpghttp'] ?>" target="_blank"><span class="noircalibri11"><?php echo $row_rs_ind['lienpghttp'] ?></span></a>
              </td>
            </tr>
          <?php 
        } ?>
          <?php 
        if($row_rs_ind['lienhttpenseigne']!="")
        { ?>
          <tr>
            <td nowrap><span class="bleucalibri11"><?php echo $codelangue=="FR"?'Enseignement :':'Teaching:' ?></span>
              </td>
            <td>
              <a href="<?php echo $row_rs_ind['lienhttpenseigne'] ?>" target="_blank"><span class="noircalibri11"><?php echo $row_rs_ind['lienhttpenseigne'] ?></span></a>
              </td>
            </tr>
          <?php 
        } 
        if($row_rs_ind['lienlinkedin']!="")
        { ?>
          <tr>
            <td nowrap><div class="div_gauche" >
              <div class="div_gauche noircalibri11">Linked&nbsp;</div>
              <div class="div_gauche blanccalibri10" style="background-color:#0077B5;border: solid 1px #CCC;border-top-right-radius:2px;border-bottom-right-radius:2px; letter-spacing:1px; padding-left:2px; padding-right:1px ">in</div></div>
              </td>
            <td>
              <a href="<?php echo $row_rs_ind['lienlinkedin'] ?>" target="_blank"><span class="noircalibri11"><?php echo $row_rs_ind['lienlinkedin'] ?></span></a>
              </td>
            </tr>
          <?php 
        } 
        if($row_rs_ind['nom_hal']!='')
        { ?>
          <td nowrap><span class="bleucalibri11"><?php echo 'Publications (HAL) :' ?></span>
            </td>
            <td>
              <a href="https://hal.archives-ouvertes.fr/CRAN/search/index/?q=<?php echo $row_rs_ind['prenom_hal'].'+'.$row_rs_ind['nom_hal'] ?>&lang=<?php echo strtolower($codelangue) ?>" target="_blank" class="noircalibri11">Publications</a>
              </td>
            <?php 
        }
        // Encadrement de theses
        $query_rs="select sujet.codesujet ,sujet.titre_".strtolower($codelangue)." as titresujet,".
                  " etudiant.codeindividu as codeetudiant,etudiant.nom as nom,etudiant.prenom as prenom,".
                  " individusejour.datedeb_sejour as datedeb_sujet,individusejour.datefin_sejour as datefin_sujet".
                  " from sujet,sujetdir,individusujet,individu as etudiant,individusejour".
                  " where sujetdir.codedir= ".GetSQLValueString($codeindividu, "text")." and sujet.codesujet=sujetdir.codesujet".
                  " and individusujet.codeindividu=etudiant.codeindividu".
                  " and codetypesujet='03' and individusujet.codesujet=sujet.codesujet".
                  " and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
                  " and individusujet.codeindividu=individusejour.codeindividu and individusujet.numsejour=individusejour.numsejour".
                  " and afficher_sujet_web='oui'".
                  " order by sujet.datedeb_sujet";
        $rs=mysql_query($query_rs) or die(mysql_error());
        ?>
            <tr>
              <td colspan="2" nowrap>
              </td>
            </tr>
          <?php $first=true;
        while($row_rs=mysql_fetch_assoc($rs))
        { if($first)
          { ?>
          <tr>
            <td colspan="2" nowrap><span class="bleucalibri11"><?php echo $hashLib['encadrementthese'] ?> :</span></td>
            </tr>
          <?php 
            $first=false;
          }?>
          <tr>
            <td nowrap class="noircalibri11"><?php echo $row_rs['datedeb_sujet'] ?> -<br />
              <?php echo $row_rs['datefin_sujet'] ?></td>
            <td>
              <span class="noircalibri11">
                <a href="detailsujetpublic.php?appel=&codetheme=&codelangue=<?php echo $codelangue ?>&codesujet=<?php echo $row_rs['codesujet'] ?>"><?php echo $row_rs['titresujet'] ?></a>
                &nbsp;(<a href="detailindividupublic.php?appel=<?php echo $appel ?>&codetheme=&codelangue=<?php echo $codelangue ?>&codeindividu=<?php echo $row_rs['codeetudiant'] ?>"><?php echo $row_rs['prenom'] ?> <?php echo $row_rs['nom'] ?></a>)   
                </span>
              </td>
            </tr>
          <?php 
			}
        }?>
          </table>
      </div>
      <?php if(file_exists('perso/'.$codeindividu.'/cv.html'))
          { ?>
          <div class="div_gauche" style="width:500px; border-style:solid; border-width:2px; border-color:#CCCCCC; border-radius:7px; margin:10px">
          <div class="div_gauche" style="padding: 5px 3px 5px 3px;"><img src="perso/<?php echo $codeindividu ?>/photo_visage.jpg"/></div><div style="text-align:justify;padding: 5px;">
          
            <?php 
            if (!($fp = @fopen('perso/'.$codeindividu.'/cv.html', "r"))) 
            { die("Impossible d'ouvrir le document "); 
            }
            while ($data = fread($fp, 4096)) 
            { echo $data;
            }
            fclose($fp);?>
          </div>
          </div>
    
          <?php 
          }	?>
      <?php 
      if(isset($rslib)) mysql_free_result($rslib);
      if(isset($rs)) mysql_free_result($rs);
      if(isset($rs_ind)) mysql_free_result($rs_ind);
		}
		?>
		<div class="div_gauche" style="width:980px;">&nbsp;</div>
