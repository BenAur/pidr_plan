<?php require_once('_const_fonc.php');

$codelangue=(isset($_REQUEST['codelangue'])?$_REQUEST['codelangue'] : "FR");
$racine_site=isset($_REQUEST['racine_site'])?$_REQUEST['racine_site'] : "";
// organisme
$query_rs = "SELECT codeorganisme,liborganisme_long,pays.codepays,codelibpays,libpays_".strtolower($codelangue).",rel_int_continent.codecontinent,libcontinent_".strtolower($codelangue).
						"	FROM rel_int_organisme,pays,rel_int_continent".
						" WHERE rel_int_organisme.codepays=pays.codepays and pays.codecontinent=rel_int_continent.codecontinent and codeorganisme<>''".
						" order by rel_int_continent.numordre,libpays_".strtolower($codelangue).",liborganisme_long";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_organisme=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_organisme[$row_rs['codeorganisme']]=$row_rs;
}

// partenariat
$query_rs = "SELECT codeorganisme,codepartenariat,codetype,detailpartenariat_long_".strtolower($codelangue)." as detailpartenariat_long".
						"	FROM rel_int_partenariat where codepartenariat<>'' order by codetype";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_partenariat=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariat[$row_rs['codeorganisme']][$row_rs['codepartenariat']]=$row_rs;
}

// partenariat contact
$query_rs = "SELECT rel_int_partenariatcontact.*,concat(rel_int_contact.titre,' ',rel_int_contact.prenom,' ',rel_int_contact.nom) as contactprenomnom".
						"	FROM rel_int_partenariatcontact,rel_int_contact".
						" WHERE rel_int_partenariatcontact.codecontact=rel_int_contact.codecontact".
						" ORDER BY rel_int_contact.nom";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_partenariatcontact=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariatcontact[$row_rs['codepartenariat']][$row_rs['codecontact']]['contactprenomnom']=$row_rs['contactprenomnom'];
}

$query_rs="select distinct rel_int_partenariat.*,rel_int_contact.*,rel_int_type.libtype_".strtolower($codelangue)." as libtype,concat(rel_int_contact.titre,' ',rel_int_contact.prenom,' ',rel_int_contact.nom) as contactprenomnom,rel_int_partenariatindividu.*,individu.codeindividu,individu.nom,individu.prenom,titrepers_".strtolower($codelangue)." as titre,".
					" fonction_".strtolower($codelangue)." as fonction,tel,email,fax,cat.libcat_".strtolower($codelangue)." as libcat,".
					" lieu.libcourtlieu as liblieu,lienpghttp,individutheme.codetheme as codetheme".
					" from rel_int_partenariatindividu,rel_int_type,rel_int_partenariat,rel_int_partenariatcontact,rel_int_contact,individu,individusejour,individutheme,structure,corps,cat,lieu ".
					" where rel_int_partenariatindividu.codepartenariat=rel_int_partenariat.codepartenariat".
					" and rel_int_partenariatindividu.codeindividu=individusejour.codeindividu".
					" and rel_int_partenariat.codepartenariat=rel_int_partenariatcontact.codepartenariat".
					" and rel_int_partenariat.codetype=rel_int_type.codetype and rel_int_type.codetype<>''".
					" and rel_int_partenariatcontact.codecontact=rel_int_contact.codecontact".
					" and individu.codeindividu=individusejour.codeindividu".
					" and individusejour.numsejour=individutheme.numsejour".
					" and individusejour.codeindividu=individutheme.codeindividu".
					" and individutheme.codetheme=structure.codestructure".
					" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
					" and ".periodeencours('individutheme.datedeb_theme','individutheme.datefin_theme').
					" and ".periodeencours('structure.date_deb','structure.date_fin').
					" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
					" and individu.codelieu=lieu.codelieu and individu.codeindividu<>'' and individutheme.codetheme<>'00'".
					" order by individu.nom,individu.prenom";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_partenariatindividu=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_partenariatindividu[$row_rs['codepartenariat']][$row_rs['codeindividu']]=$row_rs;
	$tab_themepartenariatindividu[$row_rs['codeorganisme']][$row_rs['codetheme']][$row_rs['codepartenariat']][$row_rs['codeindividu']]=$row_rs;
	if(isset($tab_partenariatcontact[$row_rs['codepartenariat']][$row_rs['codecontact']]))
	{ $tab_partenariatcontact[$row_rs['codepartenariat']][$row_rs['codecontact']][$row_rs['codetheme']]=true;
	}
}
// themes
$tab_couleur=array("#F09","#39F","#990099");
$query_rs = "SELECT codestructure as codetheme,libcourt_".strtolower($codelangue)." as libcourttheme,liblong_".strtolower($codelangue)." as libtheme".
						"	FROM structure".
						" where esttheme='oui' and codestructure<>'00'".
						" and ".periodeencours('structure.date_deb','structure.date_fin');
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_theme=array();
$i=0;
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_theme[$row_rs['codetheme']]['libtheme']=$row_rs['libtheme'];
	$tab_theme[$row_rs['codetheme']]['libcourttheme']=$row_rs['libcourttheme'];
	$tab_theme[$row_rs['codetheme']]['couleur']=isset($tab_couleur[$i])?$tab_couleur[$i]:"#000";
	$i++;
}

// continent
$query_rs = "SELECT codecontinent,libcontinent_".strtolower($codelangue).
						" FROM rel_int_continent where  codecontinent<>'' order by numordre";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_continent=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_continent[$row_rs['codecontinent']]=$row_rs;
}
// pays
$query_rs = "SELECT codepays,codelibpays,libpays_".strtolower($codelangue)." as libpays FROM pays where codepays in (select codepays from rel_int_organisme) and codepays<>'' order by numordre,libpays";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_pays=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_pays[$row_rs['codelibpays']]=$row_rs;
}

// type rel_int
$query_rs = "SELECT codetype,libtype_".strtolower($codelangue)." as libtype FROM rel_int_type";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_type=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_type[$row_rs['codetype']]=$row_rs['libtype'];
}

?>

<script>
var tab_div=new Array();
var div_main
var div_cartemonde
var div_europe
var div_detailpays
var div_organismes
var div_detailpartenariat
var div_detailindividu
var div_debug
var svg_pays_selectionne=new Object()
var div_un_listetousorganismes_selectionne=new Object()
var b_annuler_pays_affiche=false
$(document).ready( function()
{ div_cartemonde=document.getElementById('div_cartemonde');
	div_europe=document.getElementById('div_europe');
  div_main=document.getElementById('main');
	div_detailpays=document.getElementById('div_detailpays')
  div_organismes=document.getElementById('div_organismes');
	div_detailpartenariat=document.getElementById('div_detailpartenariat');
	div_detailindividu=document.getElementById('div_detailindividu')
  div_debug=document.getElementById('div_debug');
	div_un_listetousorganismes_selectionne.id=''
	svg_pays_selectionne.id=''
});
var codelibpays_selectionne=''
var codeorganisme_selectionne=''
var codeindividu_selectionne=''
var numindividu_selectionne=-1
var tab_pays=new Array()
var tab_paysorganisme=new Array()
var tab_un_paysorganisme=new Array()
var tab_organisme=new Array()
<?php 
foreach($tab_pays as $un_codelibpays=>$un_tab_pays)
{?>pays=new Object();
	pays['codepays']="<?php echo $un_tab_pays['codepays']?>"
	pays['libpays']="<?php echo js_tab_val($un_tab_pays['libpays'])?>"
	tab_pays['<?php echo $un_codelibpays ?>']=pays
<?php 
}

$codelibpays_prec='';
$num_un_paysorganisme=0;
foreach($tab_organisme as $codeorganisme=>$un_tab_organisme)
{?>
	organisme=new Object()
	organisme['codeorganisme']="<?php echo $un_tab_organisme['codeorganisme']?>"
	organisme['liborganisme']="<?php echo js_tab_val($un_tab_organisme['liborganisme_long'])?>"
	organisme['codepays']="<?php echo $un_tab_organisme['codepays']?>"
	organisme['codelibpays']="<?php echo $un_tab_organisme['codelibpays']?>"
	<?php 
	$numorganisme=0;
	$codelibpays=$un_tab_organisme['codelibpays'];
	if($codelibpays!=$codelibpays_prec)
	{ ?> 
		tab_paysorganisme['<?php echo $codelibpays ?>']=new Array();
		<?php 
		$num_un_paysorganisme=0;
		$codelibpays_prec=$codelibpays;
	}
	?>
	tab_paysorganisme['<?php echo $codelibpays ?>'][<?php echo $num_un_paysorganisme ?>]="<?php echo $codeorganisme ?>"
	tab_theme=new Array();
	<?php 
	$num_un_paysorganisme++;
	$numtheme=0;
	if(isset($tab_themepartenariatindividu[$codeorganisme]))
	{ foreach($tab_themepartenariatindividu[$codeorganisme] as $codetheme=>$un_tab_theme)
		{	$tab_partenariattypeunique=array();
			$tab_contactunique=array();
			$tab_individuunique=array();
		?>
			theme=new Object()
			theme['codetheme']="<?php echo $codetheme?>"
			theme['libtheme']="<?php echo js_tab_val($tab_theme[$codetheme]['libtheme'])?>"
			theme['libcourttheme']="<?php echo js_tab_val($tab_theme[$codetheme]['libcourttheme'])?>"
			theme['couleur']="<?php echo js_tab_val($tab_theme[$codetheme]['couleur'])?>"
			tab_partenariat=new Array();
			tab_partenariatcontact=new Array();
			tab_partenariatindividu=new Array();

			<?php
			$numpartenariat=0; 
			$numcontact=0;
			$numindividu=0;
			foreach($un_tab_theme as $codepartenariat=>$un_tab_partenariat)
			{ if(isset($tab_partenariat[$codeorganisme][$codepartenariat]))
				{ if(!isset($tab_partenariattypeunique[$tab_partenariat[$codeorganisme][$codepartenariat]['codetype']]))
					{ $tab_partenariattypeunique[$tab_partenariat[$codeorganisme][$codepartenariat]['codetype']]=$tab_partenariat[$codeorganisme][$codepartenariat]['codetype']?>
						partenariat=new Object()
						partenariat["libtype"]="<?php echo js_tab_val($tab_type[$tab_partenariat[$codeorganisme][$codepartenariat]['codetype']])?>"
						partenariat['detailpartenariat_long']="<?php echo js_tab_val($tab_partenariat[$codeorganisme][$codepartenariat]['detailpartenariat_long'])?>"
						tab_partenariat[<?php echo $numpartenariat ?>]=partenariat;
					<?php 
						$numpartenariat++;
					}
					
					if(isset($tab_partenariatcontact[$codepartenariat]))
					{ foreach($tab_partenariatcontact[$codepartenariat] as $codecontact=>$un_tab_contactprenomnom)
						{ if(!isset($tab_contactunique[$codecontact]) && $tab_partenariatcontact[$codepartenariat][$codecontact][$codetheme])
							{ $tab_contactunique[$codecontact]=$codecontact?> 
								tab_partenariatcontact[<?php echo $numcontact ?>]="<?php echo js_tab_val($un_tab_contactprenomnom['contactprenomnom'])?>";
							<?php
								$numcontact++;
							}
						}
					} 
				
					if(isset($tab_partenariatindividu[$codepartenariat]))
					{ foreach($un_tab_partenariat as $codeindividu=>$un_tab_individu)
						{ if(!isset($tab_individuunique[$codeindividu]) && isset($un_tab_partenariat[$codeindividu]))
							{ $tab_individuunique[$codeindividu]=$codeindividu?>
								individu=new Object()
								<?php foreach($un_tab_individu as $key=>$val)
								{?> individu['<?php echo $key ?>']= "<?php echo js_tab_val($un_tab_individu[$key])?>";
									<?php 
								}?>
								tab_partenariatindividu[<?php echo $numindividu ?>]=individu;
								<?php
								$numindividu++;
							}
						}
					}
				}?>
				theme['tab_partenariat']=tab_partenariat
				theme['tab_partenariatcontact']=tab_partenariatcontact;
				theme['tab_partenariatindividu']=tab_partenariatindividu;
			<?php 
			}?>
			tab_theme[<?php echo $numtheme?>]=theme
			<?php 
			$numtheme++;
		}
	}?>
	organisme['tab_theme']=tab_theme
	tab_organisme['<?php echo $codeorganisme?>']=organisme
<?php 
}
?>

function affichedetailpays(event,svg_pays)
{ if(svg_pays.id=='')alert('error : please reload the page');
	codelibpays=svg_pays.id
	div_detailpays.style.display='block';
	pays=tab_pays[codelibpays]
	texte='<table cellpadding="0" cellspacing="0">'+
					'<tr><td>'+
 						'<table>'+
							'<tr class="titre_pays_partenariat" style="font-size:11pt;" onmouseover="if(!estblock(\'b_annule_organisme\')){div_detailpartenariat.style.display=\'none\'}" >'+
								'<td>'+
									'<img id="b_annule_pays" src="images/b_annuler_croix.png" style="display:none" width="12" height="12"'+
									'onclick="mouseclickannule_pays()">'+
								'</td>'+
								'<td>'+pays.libpays+
								'</td>'+
							'</tr>'+
						'</table>'
	texte+='</td></tr>';
  texte+='<tr><td>'
							
	for(numorganisme=0;numorganisme<tab_paysorganisme[codelibpays].length;numorganisme++)
	{ codeorganisme=tab_paysorganisme[codelibpays][numorganisme]
		organisme=tab_organisme[codeorganisme]
		
		texte+='<div id="listepaysorganismes'+codeorganisme+'">'+
									'<table cellpadding="0" cellspacing="0">'+
										'<tr>'+
											'<td valign="top">'+
												'<img src="images/b_fleche_droite.png" />'+
											'</td>'+
											'<td valign="top" class="noircalibri10"'+
												' onmouseover="this.style.cursor=\'pointer\';if(!estblock(\'b_annule_organisme\')){affichedetailorganisme(event,\''+codeorganisme+'\',\'listepaysorganismes\')}"'+
												' onmouseout="this.style.cursor=\'default\';if(!estblock(\'b_annule_organisme\')){div_detailpartenariat.style.display=\'none\'}"'+
												' onClick="mouseclickorganisme(event,\''+codeorganisme+'\',\'listepaysorganismes\')">'+organisme.liborganisme+
											'</td>'+
										'</tr>'+
									'</table>'+
								'</div>'
	}
	texte+='</td></tr></table>'
	
	x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft)-getOffset(div_main).left;
	y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop)-10;
	parent=document.getElementById(codelibpays).parentNode
	bbox_parent=parent.getBBox()
	bbox=(document.getElementById(codelibpays)).getBBox()
	//x=bbox.x+pos_carte.left + (document.documentElement.scrollLeft + document.body.scrollLeft)
	//texte+=bbox.height
	div_detailpays.style.position = 'absolute';
	if(y>400)
	{ y=y-20*(numorganisme-1)
	}
	else
	{ y=y-20
	}
	div_detailpays.style.left =x +'px';//pos_x_svg_pays
	div_detailpays.style.top = y +'px';//
	div_detailpays.innerHTML=texte
}

function affichedetailorganisme(event,codeorganisme,appel)
{ if(estblock('b_annule_organisme'))
	{ return;
	}
	if(appel=='listetousorganismes')
	{ div_detailpays.style.display='none';
	}
	div_detailindividu.style.display='none';
	div_detailpartenariat.style.display='block';
	if(codeorganisme_selectionne!=codeorganisme)
	{ if(document.getElementById('listepaysorganismes'+codeorganisme_selectionne))
		{	document.getElementById('listepaysorganismes'+codeorganisme_selectionne).style.backgroundColor='#ffffff'
		}
		else if(document.getElementById('div_listetousorganismes'+codeorganisme_selectionne))
		{ document.getElementById('div_listetousorganismes'+codeorganisme_selectionne).style.backgroundColor='#ffffff'
		}
		if(document.getElementById('listepaysorganismes'+codeorganisme))
		{	document.getElementById('listepaysorganismes'+codeorganisme).style.backgroundColor='#CCC'
		}
		else if(document.getElementById('listetousorganismes'+codeorganisme))
		{ document.getElementById('listetousorganismes'+codeorganisme).style.backgroundColor='#CCC'
		}
	}
	texte='';
	o=tab_organisme[codeorganisme]
	codelibpays=o.codelibpays
	codelib_pays_selectionne=codelibpays
	o_selectionne=tab_organisme[codeorganisme_selectionne]
	codeorganisme_selectionne=codeorganisme
	texte='<table border="0">'+
					'<tr>'+
						'<td>'+
							'<table border="0">'+
								'<tr>'+
									'<td valign="middle">'+
											'<img id="b_annule_organisme" src="images/b_annuler_croix.png" style="display:none" width="12" height="12"'+
																										' onclick="mouseclickannule_organisme()">'+
									'</td>'+
									'<td valign="top"><span class="titre_organisme_partenariat" style="font-size:12pt;">'+o.liborganisme+'</span>'+
									'</td>'+
								'</tr>'+
							'</table>'+
						'</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+
							'<table border="0" padding="0">'
	tab_theme=o.tab_theme
	for(numtheme=0;numtheme<tab_theme.length;numtheme++)
	{	t=tab_theme[numtheme]
		<?php 
		if(strtolower($codelangue)=='fr')
		{ ?>txt="D�partement : ";
				href='francais/themes_rech/'+t['libcourttheme'].toLowerCase()+'/index.php'
		<?php 
		}
		else
		{ ?>txt="Department: ";
				href='anglais/themes_rech/'+t['libcourttheme'].toLowerCase()+'/index.php'
		<?php 
		}?>
		
		
		c=t.tab_partenariatcontact
		firstcontact=true
		texte+='<tr>'+
						'<td style="border-left:4px solid '+t['couleur']+'; padding-left:5px">'+
						'</td>'+
						'<td>'+
							'<table border="0">'
		style_titre_contact='color:#999900;font-size: 11pt;font-family: Calibri;'
		style_titre_type='color:#666666;font-size: 11pt;font-family: Calibri;'

		for(numcontact=0;numcontact<c.length;numcontact++)
		{ if(firstcontact)
			{ texte+='<tr><td><span class="'+style_titre_contact+'">Contact :</span><span class="bleuclaircalibri11">'
				firstcontact=false
				texte+=' '+c[numcontact];
			}
			else
			{ texte+=', '+c[numcontact];
			}
		}
		if(!firstcontact)
		{ texte+='</span></td></tr>';
		}
		p=t.tab_partenariat
		firstpartenariat=true
		libtype_prec=''
		for(numpartenariat=0;numpartenariat<p.length;numpartenariat++)
		{ if(firstpartenariat)
			{ texte+='<tr><td class="noircalibri10">'
			}
			else
			{ texte+=', '
			}
			souligne=''
			if(p[numpartenariat]['detailpartenariat_long']!='')
			{ souligne=' style="text-decoration:underline"'
			}

			if(p[numpartenariat]["libtype"]!=libtype_prec)
			{ texte+='<span id="div_libtype_'+numpartenariat+'" class="'+style_titre_type+'"'+
									' onmouseover="affichedetailpartenariat_un_type(event,this,\''+codeorganisme+'\','+numtheme+','+numpartenariat+',\''+p[numpartenariat]["libtype"]+'\')"'+
									' onmouseout="div_detailpartenariat_un_type.style.display=\'none\'" '+souligne+'>'+
									p[numpartenariat]["libtype"]+
								'</span>'
				libtype_prec=p[numpartenariat]["libtype"]
			}
			firstpartenariat=false
		}
		if(!firstpartenariat)
		{ texte+='</td></tr>';
		}
		
		i=t.tab_partenariatindividu
		if(i.length>0)
		{ firstindividu=true
			for(numindividu=0;numindividu<i.length;numindividu++)
			{ if(firstindividu)
				{ texte+='<tr><td><div class="div_gauche bleucalibri10"><img src="images/icone_cran.png" width="12" height="14">&nbsp;</div>'
					firstindividu=false;
				}
				virgule=''
				if(numindividu!=i.length-1)
				{ virgule=', '
				}
				un_individu=i[numindividu]
				texte+='<span id="div_individu_'+numindividu+'" class="noircalibri10" style="text-decoration:underline"'+ 
									' onclick="affichedetailindividu(event,\''+codeorganisme+'\','+numtheme+','+numindividu+')"'+
									' onmouseover="this.style.cursor=\'pointer\';this.style.color=\'#990099\'"'+
									' onmouseout="this.style.color=\'#006699\'">'+
									'<img src="images/b_plus.png" width="8" height="8">'+
									 un_individu.prenom+' '+un_individu.nom+
								'</span><span class="noircalibri10">'+virgule+'</span>';
			}
			if(!firstindividu)
			{ texte+='</td></tr>';
			}
		}
		texte+='<tr><td colspan="2"><ul style="margin-left:13px;padding-left:2px; margin-top:0px;padding-top:0px; margin-bottom:0px;padding-bottom:0px;color:'+
																	t['couleur']+'"><li style="color:'+t['couleur']+';list-style-type:square;font-size:20px;">'+
																	'<span><a href="'+href+'" style="text-decoration:underline;color:'+t['couleur']+'"  class="noircalibri11">'+txt+t['libtheme']+'</a></span></li></ul></td></tr>';
		texte+='</table></td>'
		
	}
	texte+='</table></td>'
	pos_carte=getOffset(div_cartemonde)
	div_detailpartenariat.style.overflowX = "auto";
	div_detailpartenariat.style.overflowY = "scroll";
	hauteur=20+tab_theme.length*115 //20=hauteur nom organisme, 120=hauteur pour les lignes d'un theme
	div_detailpartenariat.style.width = "400px";
	div_detailpartenariat.style.height = hauteur+"px";
	if(appel=='listepaysorganismes')
	{ x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft)-getOffset(div_main).left+10;
	  y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop)-hauteur;
	}
	else if(appel=='listetousorganismes')
	{	x=getOffset(document.getElementById('div_organismes')).left-getOffset(div_main).left-411//615
		y=getOffset(document.getElementById('div_listetousorganismes'+codeorganisme)).top-hauteur+20
		//if(y+parseInt(div_detailpartenariat.style.height))
		//texte+='document.documentElement.scrollTop '+document.documentElement.scrollTop + ' document.body.scrollTop '+document.body.scrollTop+' getOffset(div_main).top '+getOffset(div_main).top
		
//alert(div_detailpartenariat.style.width)
		//texte+="getOffset('listetousorganismes'+codeorganisme).left "+x
		/* parent=document.getElementById(codelibpays).parentNode
		bbox_parent=parent.getBBox()
		bbox=(document.getElementById(codelibpays)).getBBox()
		x=bbox.x+pos_carte.left + (document.documentElement.scrollLeft + document.body.scrollLeft)
		y=pos_carte.top+600-bbox.y */
	}
  div_detailpartenariat.innerHTML=texte;
	div_detailpartenariat.style.position = 'absolute';
	div_detailpartenariat.style.left = x+'px';
	div_detailpartenariat.style.top = y+'px';
}

function affichedetailindividu(event,codeorganisme,numtheme,numindividu)
{ //
	//div_detailpartenariat.style.display='block';
	//alert(document.getElementById('div_individu_'+numindividu))
  //pos_div_individu=getOffset(document.getElementById('div_individu_'+numindividu))
	if(codeorganisme_selectionne==codeorganisme &&	numindividu_selectionne==numindividu  && estblock('b_annule_individu'))
	{ setdisplay('b_annule_individu','none');
		div_detailindividu.style.display='none';
	  codeorganisme_selectionne=''
		numindividu_selectionne=-1
	}
	else
	{ tab_theme=tab_organisme[codeorganisme].tab_theme
		individu=tab_theme[numtheme].tab_partenariatindividu[numindividu]
		texte='<table width="100%">'+
					'<tr><td><table width="100%"><tr><td class="mauvegrascalibri9" align="left">'+individu.prenom+' '+individu.nom+'</td>'+
					'<td align="right"><img id="b_annule_individu" src="images/b_annuler_croix.png" style="display:block" width="13" height="13" onclick="mouseclickannule_individu()"></td></tr></table></td></tr>'+
					'<tr><td><table>'
		texte+='<tr><td class="bleucalibri10"><img src="images/b_mail.png"></td><td class="noircalibri10"><a href="mailto:'+individu.email+'">'+individu.email+'</td></tr>'
		texte+='<tr><td class="bleucalibri10"><img src="images/b_phone.png" width="14" height="14"><td class="noircalibri10">'+individu.tel+'</td></tr>'
		texte+=(individu.lienpghttp==''?'':'<tr><td class="bleucalibri10" align="center"><img src="images/b_plus.png" width="10" height="10"></td><td class="noircalibri10"><a href="'+individu.lienpghttp+'" target="_blank">Page personnelle</a></td></tr>')
		texte+='</table></td></tr>'
		texte+='</table>' /* */
		pos_div_detailpartenariat=getOffset(div_detailpartenariat)
		div_detailindividu.style.display='block';
		div_detailindividu.style.position = 'absolute';
		div_detailindividu.style.left =(pos_div_detailpartenariat.left-parseInt(div_detailindividu.style.width)-getOffset(div_main).left)+'px';// (pos_div_detailpartenariat.left-getOffset(div_main).left)+'px';
		div_detailindividu.style.top = (pos_div_detailpartenariat.top+20+numtheme*115)+'px';//pos_div_detailpartenariat.top+'px';
		div_detailindividu.innerHTML=texte
	  codeorganisme_selectionne=codeorganisme
		numindividu_selectionne=numindividu
	}
}

function affichedetailpartenariat_un_type(event,div_libtype,codeorganisme,numtheme,numpartenariat,libtype)
{ o=tab_organisme[codeorganisme]
	t=o.tab_theme[numtheme]
  p=t.tab_partenariat
	if(p.length!=0  && p[numpartenariat]['detailpartenariat_long']!='')
	{ texte='<table width="100%">'
		for(numpartenariat=0;numpartenariat<p.length;numpartenariat++)
		{ if(p[numpartenariat]["libtype"]==libtype && p[numpartenariat]['detailpartenariat_long']!='')
			{ texte+='<tr><td class="noircalibri10">'+p[numpartenariat]['detailpartenariat_long']+'</td></tr>'
			}
		}
		texte+='</table>'
		x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft)-50;
		y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop)+10;
		div_detailpartenariat_un_type.style.left = x+'px'
		div_detailpartenariat_un_type.style.top = y+'px'
		div_detailpartenariat_un_type.style.position = 'absolute';
		div_detailpartenariat_un_type.style.display='block'
		div_detailpartenariat_un_type.innerHTML=texte
	}
	else
	{ div_detailpartenariat_un_type.style.display='none'
	}
}

function mouseclickmonde()
{ setdisplay('b_annule_pays','none');div_detailpays.style.display='none';
	setdisplay('b_annule_organisme','none');div_detailpartenariat.style.display='none';
	setdisplay('b_annule_individu','none');div_detailindividu.style.display='none';
	//svg.style.cursor="default"
}

function mouseovermonde(svg)
{ if(estblock('b_annule_pays') || estblock('b_annule_organisme') ||  estblock('b_annule_individu'))
	{ svg.style.cursor="url('images/b_annuler_croix_petit.png'),auto"
	}
	else
	{ svg.style.cursor="default"
	}
}
function mousemovemonde(svg)
{ if(estblock('b_annule_pays') || estblock('b_annule_organisme') ||  estblock('b_annule_individu'))
	{ svg.style.cursor="url('images/b_annuler_croix_petit.png'),auto"
	}
	else
	{ svg.style.cursor="default"
	}
}

function mouseclickrecteurope(rectangle)
{ svg_monde=document.getElementById('svg_monde')
	svg_europe=document.getElementById('svg_europe')
	if(div_europe.style.display=='none')
	{ pos_svg_monde=getOffset(svg_monde)
		pos_x_div_europe=pos_svg_monde.left+svg_monde.width.baseVal.value-svg_europe.width.baseVal.value
		pos_y_div_europe=pos_svg_monde.top+svg_monde.height.baseVal.value-svg_europe.height.baseVal.value
		div_europe.style.display='block';;
		div_europe.style.position = 'absolute';
		div_europe.style.left = (pos_svg_monde.left-getOffset(div_main).left)+'px';
		div_europe.style.top = pos_svg_monde.top+'px';
	}
	else
	{	div_europe.style.display='none';
	
	}
		setdisplay('b_annule_pays','none');div_detailpays.style.display='none';
		setdisplay('b_annule_organisme','none');div_detailpartenariat.style.display='none';
		setdisplay('b_annule_individu','none');div_detailindividu.style.display='none';

}

function mouseoverrecteurope(rectangle)
{ if(div_europe.style.display=='none')
	{ document.getElementById(rectangle.id).style.cursor="zoom-in";
	}
	else
	{ document.getElementById(rectangle.id).style.cursor="zoom-out";
	}
  document.getElementById(rectangle.id).style.fill = "rgb(0,255,0)"
  document.getElementById(rectangle.id).style.stroke = "rgb(0,0,0)"
	document.getElementById(rectangle.id).style.opacity=0.3

	/*pos_carte=getOffset(carte)
	document.getElementById('div_europe').style.display='block';
	document.getElementById('div_europe').style.position = 'absolute';;
	document.getElementById('div_europe').style.left = pos_carte.left+'px';
	document.getElementById('div_europe').style.top = pos_carte.top+'px';
  */
}
function mouseoutrecteurope(rectangle)
{ document.getElementById(rectangle.id).style.cursor='none';
	document.getElementById(rectangle.id).style.opacity=0.02
}

function mouseoverpays(evt,svg_pays)
{ //div_debug.innerHTML='mouseoverpays'

	if(svg_pays.id!='')
	{ document.getElementById(svg_pays.id).style.cursor="url('images/clic_fige_<?php echo strtolower($codelangue) ?>.png'),auto";
	}
	if(estblock('b_annule_pays'))
	{ return
	}
	div_detailpartenariat.style.display='none';
	div_detailindividu.style.display='none'
	codeorganisme_selectionne=''
	div_detailpartenariat.style.display='none'
	if((svg_pays_selectionne.id!=svg_pays.id) && !estblock('b_annule_pays'))
	{ 
		affichedetailpays(evt,svg_pays)
		svg_pays_selectionne.id=svg_pays.id
		div_un_listetousorganismes_selectionne=''
	}
}

function mouseoutpays()
{ if(!estblock('b_annule_pays'))
	{ div_detailpays.style.display="none"
	  div_detailpartenariat.style.display='none';
		svg_pays_selectionne=new Object()
	} 
}

function mouseclickpays(event,svg_pays)
{ codeorganisme_selectionne=''
	setdisplay('b_annule_organisme','none');div_detailpartenariat.style.display='none';
	setdisplay('b_annule_individu','none');div_detailindividu.style.display='none';
	svg_pays_selectionne.id=svg_pays.id
		//alert(svg_pays.parentNode.id)
	affichedetailpays(event,svg_pays)
  setdisplay('b_annule_pays',"block");
}

function mouseoverdiv_detailpays()
{ 
}

function mouseclickdiv_detailpays()
{ if(estblock('b_annule_pays'))/* */
	{ setdisplay('b_annule_pays','none');
	}
	else
	{ setdisplay('b_annule_pays','block');
	}

}

function mouseoutdiv_detailpays()
{ if(estblock('b_annule_pays'))/* */
	{ 
	div_detailpays.style.display="block"
	//div_detailpartenariat.style.display='block';
	} 
}

function mouseclickannule_pays()
{ setdisplay('b_annule_pays','none');div_detailpays.style.display='none';
	setdisplay('b_annule_organisme','none');div_detailpartenariat.style.display='none';
	setdisplay('b_annule_individu','none');div_detailindividu.style.display='none';
}


function mouseclickorganisme(event,codeorganisme,appel)
{ div_detailindividu.style.display="none"
	setdisplay('b_annule_individu',"none")
	setdisplay('b_annule_organisme',"none")
  affichedetailorganisme(event,codeorganisme,appel)
	setdisplay('b_annule_organisme',"block")
	setdisplay('b_annule_pays',"block")
	if(appel=='listetousorganismes')
	{ div_detailpays.style.display="none"
		setdisplay('b_annule_pays',"none")
	}
	/* else
	{ 
	} */
}

function mouseoutorganisme()
{ if(estblock('b_annule_organisme'))
	{ return
	}
	/*    
	div_detailpays.style.display="block"
	div_detailpartenariat.style.display='block';
	//div_detailindividu.style.display='block'
	 */
}

function mouseclickannule_organisme()
{ div_detailpartenariat.style.display='none'
	div_detailindividu.style.display="none"
	setdisplay('b_annule_individu',"none")
	document.getElementById('b_annule_organisme').style.display="none"
	/* document.getElementById('b_annule_pays').style.display="none"
	div_detailpays.style.display="none" */
}

function mouseoverdetailpartenariat()
{ if(estblock('b_annule_individu'))
	{ return
	}
	div_detailpartenariat.style.display='block';
	if(svg_pays_selectionne.id=='')
	{ div_detailpays.style.display="none"
	}
	else
	{ div_detailpays.style.display="block"
	}
	div_detailindividu.style.display='none' /* */ 
}

function mouseoutdetailpartenariat()
{ //div_detailpartenariat.style.display='none';
	//div_debug.innerHTML=''
	//div_detailindividu.style.display='block'
}

function mouseoverdiv_detailindividu()
{ setdisplay('b_annule_individu',"block")
}

function mouseclickannule_individu()
{ setdisplay('b_annule_individu',"none")
	setdisplay('div_detailindividu',"none")
}
function mouseoutdiv_detailindividu()//necessaire pour que le div individu ne se ferme sur sortie ?
{ //div_detailindividu.style.display="none"
}

function mouseoverorganismelistetousorganismes(evt,div_un_listetousorganismes,codeorganisme)
{ div_un_listetousorganismes.style.cursor="url('images/clic_fige_<?php echo strtolower($codelangue) ?>.png'),auto";
	if(!estblock('b_annule_pays'))
  { setdisplay(div_detailpays,'none')
		setdisplay(div_detailpartenariat,'none')
		svg_pays_selectionne.id=''
		//document.getElementById(div_un_listetousorganismes.id).style.cursor='pointer';
		if(div_un_listetousorganismes_selectionne.id!=div_un_listetousorganismes.id || div_un_listetousorganismes.style.display=='none')
		{ affichedetailorganisme(evt,codeorganisme,'listetousorganismes');
			div_un_listetousorganismes.style.backgroundColor='#bbb'
			if(div_un_listetousorganismes_selectionne)
			{ div_un_listetousorganismes_selectionne.style.backgroundColor='#ffffff'
			}
			div_un_listetousorganismes_selectionne=div_un_listetousorganismes
		}
		div_detailpays.style.display='none';
		codelibpays=''
		codelibpays_selectionne=''
	}
}

function estblock(b_annule)
{ if(document.getElementById(b_annule) && document.getElementById(b_annule).style.display=="block")
  { return true;
	}
	else
	{ return false
	}
}

function setdisplay(element,display)
{ if(document.getElementById(element))
	{ document.getElementById(element).style.display=display
	}
}

function getOffset(elem) 
{	if(elem.getBoundingClientRect)
  { return getOffsetRect(elem)
  }
	else
	{	return getOffsetSum(elem)
	}
}

function getOffsetSum(elem) {
  var top=0, left=0
  while(elem) {
    top = top + parseInt(elem.offsetTop)
    left = left + parseInt(elem.offsetLeft)
    elem = elem.offsetParent        
  }
   
  return {top: top, left: left}
}


function getOffsetRect(elem) {
    var box = elem.getBoundingClientRect()
    
    var body = document.body
    var docElem = document.documentElement
    
    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop
    var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft
    
    var clientTop = docElem.clientTop || body.clientTop || 0
    var clientLeft = docElem.clientLeft || body.clientLeft || 0
    
    var top  = box.top +  scrollTop - clientTop
    var left = box.left + scrollLeft - clientLeft
    
    return { top: Math.round(top), left: Math.round(left) }
}

function position_objet(objet,position,event)//a tester
{	event.returnValue = false;
	//Coordonnees de la souris
	var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
	var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonn�es de l'�l�ment
	var eX = 0;
	var eY = 0;
	var element = objet;
	i=0;
	do
	{ i++;
		eX += element.offsetLeft;
		eY += element.offsetTop;
		element = element.offsetParent;
	} while( element && element.style.position != position);
	return new Array(eX,eY);
}

</script>
<style>
.cadre_rel_int {
	background-color: #FFF;
	border: 2px solid;
	border-radius: 3px;
	border-color:#CCCCCC;
	padding: 4px;
}


.titre_continent_partenariat
{	background-color: #FFCB7C;
	border:1px solid #CCC;
	width:auto;
	color: #2F4E93;
	top: 0px;
	border-radius: 0px;
	padding: 0px;
	font-family: Calibri;
	font-weight: bold;
	text-align: left;
	height: auto;

}
.titre_pays_partenariat
{	background-color: #fbfbfb;
	border:1px solid #CCC;
	width:auto;
	color: #2F4E93;
	top: 0px;
	border-radius: 0px;
	padding: 0px;
	font-family: Calibri;
	text-align: left;
	height: auto;

}
.titre_organisme_partenariat
{	color:#069;
	top: 0px;
	font-family: Calibri;
	font-size:11pt;
	font-weight:500;
	text-align: left;

}

</style>

<!--<div class="div_gauche" style="width:980px;height:20px" ><img src="images/espaceur.gif" border="0" height="20"/>
 -->
<div id="div_cartemonde" class="div_gauche" style="width:700px;height:400px"> 
  <!--<rect
         style="opacity:0.27430556;fill:#27e900;fill-opacity:0.97769518000000000;stroke:#081b1f;stroke-width:0.53940743000000002;stroke-linejoin:round;stroke-miterlimit:7.66129017000000000;stroke-opacity:0.93680297999999995;stroke-dasharray:1.07881486000000000, 2.15762971000000010;stroke-dashoffset:0;filter:url(#filter7043)"
         id="svg_rectmoyenorient"
         width="5.919951"
         height="6.5421205"
         x="318.37244"
         y="-480.11685"
         transform="matrix(1,0,0,-1,-0.20315708,-0.19999984)"
         onmouseover="mouseoverrectmoyenorient(this)"
         onclick="mouseclickrectmoyenorient(this)"
         ry="2.9065664"
         rx="2.7789049"
         onmouseout="mouseoutrectmoyenorient(this)" />
function mouseclickrectmoyenorient(rectangle)
{ svg_monde=document.getElementById('svg_monde')
	pos_svg_monde=getOffset(svg_monde)
	svg_europe=document.getElementById('svg_europe')
	pos_x_div_europe=pos_svg_monde.left+svg_monde.width.baseVal.value-svg_europe.width.baseVal.value
	pos_y_div_europe=pos_svg_monde.top+svg_monde.height.baseVal.value-svg_europe.height.baseVal.value
	div_europe.style.display='block';;
	div_europe.style.position = 'absolute';
	div_europe.style.left = (pos_svg_monde.left-getOffset(div_main).left)+'px';
	div_europe.style.top = pos_svg_monde.top+'px';
	setdisplay('b_annule_pays','none');div_detailpays.style.display='none';
	setdisplay('b_annule_organisme','none');div_detailpartenariat.style.display='none';
	setdisplay('b_annule_individu','none');div_detailindividu.style.display='none';

}

function mouseoverrectmoyenorient(rectangle)
{ document.getElementById(rectangle.id).style.cursor='pointer';
  document.getElementById(rectangle.id).style.fill = "rgb(0,255,0)"
  document.getElementById(rectangle.id).style.stroke = "rgb(0,0,0)"
	document.getElementById(rectangle.id).style.opacity=0.3
}
function mouseoutrectmoyenorient(rectangle)
{ document.getElementById(rectangle.id).style.cursor='none';
	document.getElementById(rectangle.id).style.opacity=0.02
}
 -->
  <svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   version="1.1"
   width="701.02942"
   height="400.99805"
   id="svg_monde"
   xml:space="preserve"
   inkscape:version="0.48.5 r"
   sodipodi:docname="planisph-etats-2-yemen-uni.svg"><sodipodi:namedview
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1"
     objecttolerance="10"
     gridtolerance="10"
     guidetolerance="10"
     inkscape:pageopacity="0"
     inkscape:pageshadow="2"
     inkscape:window-width="1388"
     inkscape:window-height="887"
     id="namedview6238"
     showgrid="false"
     inkscape:zoom="4"
     inkscape:cx="370.55221"
     inkscape:cy="212.44902"
     inkscape:window-x="61"
     inkscape:window-y="43"
     inkscape:window-maximized="0"
     inkscape:current-layer="path30" /><metadata
     id="metadata8"><rdf:rdf><cc:work
         rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" /><cc:license
           rdf:resource="" /><dc:title /></cc:work></rdf:rdf><rdf:RDF><cc:Work
         rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" /></cc:Work></rdf:RDF></metadata><defs
     id="defs6"><clippath
       id="clipPath16"><path
         d="m 0,-0.2 841.8,0 0,595.2 L 0,595 0,-0.2 z"
         inkscape:connector-curvature="0"
         id="path18" /></clippath><mask
       id="mask22"><image
         xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAACS4AAAT+CAAAAADv7nJSAAAAAXNCSVQI5gpbmQAAHB9JREFUeJzt3V1yG0liRtEEjZ6l2A7vfy0Oe3opHnv8IKklUuTFXwGVVXXOW4stIR9vfFkonk6Dj/5z7QMAwGr+Y+0DTOh03FwSRQBwi+OG1GFySRwBwCIOWE37zyWdBABPtveC2m0uqSQAeKU9J9P+ckknAcBq9hlNe8oloQQAU9hbNO0ll6QSAMxnJ920/VwSSgAwsT0U07ZzSSoBwAZsPZm2m0tSCQA2ZMvJtM1ckkoAsEFbTabt5ZJUAoAN22IybS2XxBIA7MC2omlDuaSUAGA/thRMW8klrQQAu7OVZNpCLkklANirTRTT9LmklQBg3+YvprlzSSsBwCHMnUzz5pJUAoAjmbiYZs0lsQQABzRnM82YS1IJAA5rxmCaLpe0EgAc3HTFNFcuaSUAYLpgmimXxBIA8N1MxTRNLmklAOCdaYppjlzSSgDAJ+YopglySSsBAF+ZIZhWzyWxBAC0tZNp3VzSSgDAFdYNpjVzSSwBAFdaM5hWy6X/WulzAYCN+ve1PnilXBJLAMDNVgqmVXJJLAEAd1klmF6fS1oJAHjA64vp1bkklgCAB706mF6bS2IJAFjCS4vplbkklgCAxbyumF6WS1oJAFjUy3rpRbkklgCAxb0omF6SS2IJAHiKlwTTC3JJLAEAT/OCYHp6LoklAOCpnh5MT84lsQQAPN2Tg+mpuSSWAICXeGowPTGXxBIA8DJPDKan5ZJYAgBe6mnB9KRcEksAwOs9p5iekktiCQBYxVN66Qm5JJYAgNU8IZiWzyW1BACsafFgWjqXxBIAsLaFg2nZXBJLAMAElu2lRXNJLQEAc1gymBbMJbEEAMxjuWB6W+xfUksAwESWS5Ol1iWxBADMZqGBaZlcEksAwIwWCaZFckktAQCTWiCYFsglsQQAzOvxXno8l9QSADC1R4Pp0VwSSwDA7B7spQdzSS0BABvwUDA9lEv//cgnAwC8zL898HcfyCWxBABsx/3BdH8uqSUAYFPuDaZ7c0ksAQBbc2cv3ZlLagkA2KC7gumuXBJLAMA23dNL9+SSWgIANuv2YHq7/UPUEgCwXbeXzO25pJYAgC27uWVuvYwTSwDA1t14H3djLqklAGAHbgqm2y7j1BIAsAc3Nc1NuaSWAIB9uKVqbskltQQA7MUNXXNDLqklAGA/ri+b63NJLQEAe3J121z7zTixBADszZXfj7syl9QSALBDVwXTdZdxagkA2KOrGueqXFJLAMA+XVM51+SSWgIA9uqKzrkil9QSALBfl0vnci6pJQBgzy62zsVcUksAwL5dqp1LuaSWAIC9u9A7F3JJLQEA+9fFc8uv2AUAOKDOJeMSAHAE2TyZS2oJADiGqp7KJbUEABxFdE/kkloCAI7j6/L5OpfUEgBwJF+2z+n0xQ/+/qSTAABM6l8//+Ov1iW1BAAczRf980UuqSUA4Hg+LyCvqQQASJ/nknEJADiiTxvo01xSSwDAMX1WQZ/lkloCAI7qkw7y7BIAQPokl4xLAMBx/V5Cv+eSWgIAjuy3Fvotl9QSAHBsH2vIs0sAAOljLhmXAICj+9BDb/lTAIADel9Eb/EzAIBjetdEnl0CAEjvcsm4BAAwxvsqsi4BAKRfc8m4BADwzS9dZF0CAEi/5JJxCQDgh59l9PbJnwEA8FcbuYwDAEh/5ZJxCQDgVz/qyLoEAJB+5JJxCQDgve99ZF0CAEjfc8m4BADw0bdCsi4BAKRvuWRcAgD43d/HsC4BAFwglwAA0nmMMcZp5VMAAEzrbYwx/lz7FAAAU/pzuIwDALhALgEApLfhLg4A4Ct/WpcAAC6QSwAASS4BAKQ3jy4BAHztT+sSAECTSwAA6c1dHABAsS4BACS5BABQ/imXAADSm0eXAACKdQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACCdT2ufAABgatYlAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASOfT2icAAJiadQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS+bT2CQAApmZdAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgHQ+rX0CAICpWZcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgnU9rnwAAYGrWJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACCdT2ufAABgatYlAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASOfT2icAAJiadQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS+bT2CQAApmZdAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgHQ+rX0CAICpWZcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgnU9rnwAAYGrWJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS29/WPgEAwNSsSwAASS4BAJSTXAIASG/Dw0sAAMG6BACQ5BIAQHobbuMAAL70N+sSAECTSwAASS4BAKS34eElAICv/M26BABwgVwCAEin0xhj/M/axwAAmNEfw7oEAHCBXAIASHIJACB9y6U/Vj4FAMCM/hjDugQAcMH3XDIvAQB89K2QrEsAAOlHLpmXAADe+95Hbx/+GwCAMcbPOnIZBwCQfuaSeQkA4Ke/2ujtkz8DADi8n2XkMg4AIP2aS+YlAIBvfuki6xIAQHqXS+YlAIAx3leRdQkAIL3PJfMSAMCHJnqLnwEAHNL7InIZBwCQPuaSeQkAOLoPPfTbuqSXAIBj+1hDv1/G6SUA4Mh+ayHPLgEApE9yybwEABzX7yX02bqklwCAo/qkg1zGAQCkT3PJvAQAHNNnFfT5uqSXAIAj+rSBvriM00sAwPF8XkCeXQIASKfTFz/4x0uPAQCwuvPnf/zluvTF/w8AsFNf1c/Xl3F6CQA4ki/bJ55d0ksAwHF8XT71qLdeAgCOIronvxmnlwCAY6jq6RcJ6CUA4Aiyebx3CQAgXcgl8xIAsH9dPJfWJb0EAOzdhd65eBmnlwCAfbtUO5efXdJLAMCeXWydKx711ksAwH5dLp1rvhmnlwCAvbqic656kYBeAgD26ZrKOZ2u+7f+8dBJAABmdNUmdG0u6SUAYG+uvEC7+q3eLuQAgH25tm6u/yUoegkA2JOr2+bqy7gxXMgBAPtx/RJ0Uy7pJQBgH265Nrv+Mu7WfxkAYFY3Nc1tuaSXAIAduK1obruMG8OFHACwdTfuP7fnkl4CALbs5suyGy/j7voMAIBp3F4yd6xLw8AEAGzVHbvPfbmklwCALbrrkuyOy7i7PwsAYFX3Fcyd69IwMAEAW3Pn3nN/LuklAGBL7r4ceyCXBBMAsB33P0r0UC6N/33kLwMAvMq/PPB3H8slwQQAbMEjtfRwLuklAGB2D8XS3S8SWOzzAQCe7NFaeXhdGgYmAGBmD287S+SSXgIAZrXARdgiuSSYAIA5LfHY0EK5JJgAgPks84z1YrmklwCAuSz1hbTlckkwAQATWe7b+0vmkmACAGax4LuOls0lvQQAzGDRF0MunEuCCQBY3cJv0X74rd4fecs3ALCupWtk8XVpGJgAgBUtP908I5cEEwCwkmfccz0nl/QSALCC5zwU9KRcGooJAHi1Jz1C/bxcEkwAwCs97ftmz8wlwQQAvMoTv5z/3FwSTADAKzz1TUbPziXBBAA825Nf+/j8XBJMAMAzPf0d2a/IJcEEADzLC36hyGtySTABAM/wkt++9qpcEkwAwNJe9KtqX5dLggkAWNCLWmm8NpeGYgIAlvG6WHp5LgkmAOBxr4ylFXJpKCYA4BGvbaWxTi4JJgDgXi+PpbVySTABAPdYIZbWy6Uxxv+t9skAwBa9rfS5K+aSYAIArrdWLK2cS4IJALjKeq00Vs8lwQQAXLJqK40JcmkMyQQAfG3tWJokl4ZiAgA+s34rjXlyaSgmAOCDKWJpqlwSTADAT5O00pgsl4ZiAgDGmKmVxny5NBQTABzdVK00psyloZgA4MBmi6VZc0kwAcAhzZdKY8ybS2MMzQQAxzJnLE2eS0MxAcBBzJpKY8yfS2NIJgDYu5lbaWwjl4ZiAoD9mryVxmZyaSgmANih+VNpjC3l0hiSCQD2ZButNLaWS4IJAHZhM6U0xtheLn2nmgBgs7bVSmOzuTTG0EwAsDmbK6UxxrZzaQzJBABbsc1SGmNsP5fGGJoJAOa24VIaY+wkl4ZiAoAZbb2TvttLLv1FNwHAFHaSSmPsMJe+U00AsJYdhdI3e82lb0QTALzS7kLpm33n0hiSCQCebqeV9Jf959KvpBMALGvvpTTGOFou/SCbAOAxh8ikH46ZS2OMMf659gEAYFOO2wz/D9qv66UeCzqxAAAAAElFTkSuQmCC"
         width="1"
         height="1"
         id="image24" /></mask><radialgradient
       inkscape:collect="always"
       xlink:href="#linearGradient11181"
       id="radialGradient11189"
       cx="284.86761"
       cy="431.18344"
       fx="284.86761"
       fy="431.18344"
       r="284.41425"
       gradientTransform="matrix(1,0,0,0.57508887,0,183.21464)"
       gradientUnits="userSpaceOnUse" /></defs><g
     transform="matrix(1.25,0,0,-1.25,1.3705563,769.4305)"
     id="g10"><g
       id="path30"
       transform="matrix(0.98445987,0,0,1.0000008,0.06635308,-2.2707658e-4)"><g
         id="g28"
         style="fill:url(#radialGradient11189);fill-opacity:1;stroke:#45212a;stroke-width:1.61257826;stroke-miterlimit:1;stroke-opacity:1;stroke-dasharray:none"
         transform="translate(-0.7927779,20.799987)"><path
           style="opacity:0.09027776000000000;fill:#d8ecec;fill-opacity:1;stroke:#45212a;stroke-width:1.61257826;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:1;stroke-opacity:1;stroke-dasharray:none"
           id="svg_paysmonde"
           onclick="mouseclickmonde(this)"
           onmouseover="mouseovermonde(this)"
           onmousemove="mousemovemonde(this)"
           inkscape:connector-curvature="0"
           d="m 1.219242,555.90789 c 0,35.10308 12.715452,37.35784 66.752626,37.35784 l 433.791522,0 c 28.31992,0 45.9563,-1.55052 55.9361,-8.77603 7.66886,-5.55237 10.81653,-14.45579 10.81653,-28.58181 l 0,-243.6561 c 0,-35.79808 -24.46127,-37.46162 -66.75263,-37.46162 l -433.791522,0 c -42.602337,0 -66.752626,-1.21196 -66.752626,37.46162 z"
           sodipodi:nodetypes="ssssssssss" /></g><g
         id="g36"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:1.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:7.66129017;stroke-opacity:1;stroke-dasharray:none"
           id="path38"
           inkscape:connector-curvature="0"
           d="m 565.5,287.1 0,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path40"
         inkscape:connector-curvature="0"
         d="m 292.27474,491.2 0.6,1 0.7,0.5 1.4,-0.9 0,-0.6 0,-0.3 0,-0.6 0.3,-0.5 0.4,-1 -0.7,-1.4 -0.8,-0.6 0,0.2 -0.6,0.7 -1.1,0.6 0.4,0 0,0.9 0.2,0.2 -0.2,0.4 0,0.9 -0.6,0.5 z" /><g
         id="g42"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path44"
           inkscape:connector-curvature="0"
           d="m 293.9,465.6 0.6,1 0.7,0.5 1.4,-0.9 0,-0.6 0,-0.3 0,-0.6 0.3,-0.5 0.4,-1 -0.7,-1.4 -0.8,-0.6 0,0.2 -0.6,0.7 -1.1,0.6 0.4,0 0,0.9 0.2,0.2 -0.2,0.4 0,0.9 -0.6,0.5 z" /></g><path
         style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="BE"
         inkscape:connector-curvature="0"
         d="m 265.27474,509.6 1,0.6 0.5,0 0.1,-0.4 0.4,0.4 0.6,0.2 0.6,0 0.8,-0.2 0.8,-0.4 -0.1,-0.6 0.9,-0.3 0.3,-1 -0.7,-0.2 -0.4,-0.3 0,-1 -0.1,0 -1.1,0.4 -0.4,0.9 -0.2,-0.5 -1.4,1.1 -0.1,0.4 -0.5,0.5 -0.3,-0.3 -0.7,0.3 0,0.4 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g48"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path50"
           inkscape:connector-curvature="0"
           d="m 266.9,484 1,0.6 0.5,0 0.1,-0.4 0.4,0.4 0.6,0.2 0.6,0 0.8,-0.2 0.8,-0.4 -0.1,-0.6 0.9,-0.3 0.3,-1 -0.7,-0.2 -0.4,-0.3 0,-1 -0.1,0 -1.1,0.4 -0.4,0.9 -0.2,-0.5 -1.4,1.1 -0.1,0.4 -0.5,0.5 -0.3,-0.3 -0.7,0.3 0,0.4 z" /></g><path
         style="fill:#d99594;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="BG"
         inkscape:connector-curvature="0"
         d="m 297.87474,495.7 0.6,-0.1 -0.1,-0.4 0,-0.4 1,0.4 0.8,-0.4 0.4,0 1,0 1,-0.2 0.2,0.2 0.8,0.4 1.7,0.5 0.2,-0.1 1.4,0 0.2,-0.4 0.4,0 0,-1 -0.4,0 -0.7,-0.6 0.5,-0.9 -0.6,-0.3 0.8,-0.8 -0.7,0 -0.5,0.2 -1.4,-0.6 -0.3,0 0,-0.9 -1,-0.2 -0.6,0.2 -0.6,0 -0.4,0.4 -2.6,-0.4 0,0.9 -1.1,1 0.5,0.2 -0.5,0.3 0,0.4 0.5,0.2 0.1,0.3 -0.1,0.4 -0.5,0.6 0,1.1 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g54"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path56"
           inkscape:connector-curvature="0"
           d="m 299.5,470.1 0.6,-0.1 -0.1,-0.4 0,-0.4 1,0.4 0.8,-0.4 0.4,0 1,0 1,-0.2 0.2,0.2 0.8,0.4 1.7,0.5 0.2,-0.1 1.4,0 0.2,-0.4 0.4,0 0,-1 -0.4,0 -0.7,-0.6 0.5,-0.9 -0.6,-0.3 0.8,-0.8 -0.7,0 -0.5,0.2 -1.4,-0.6 -0.3,0 0,-0.9 -1,-0.2 -0.6,0.2 -0.6,0 -0.4,0.4 -2.6,-0.4 0,0.9 -1.1,1 0.5,0.2 -0.5,0.3 0,0.4 0.5,0.2 0.1,0.3 -0.1,0.4 -0.5,0.6 0,1.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path58"
         inkscape:connector-curvature="0"
         d="m 274.07474,518.2 0,1.3 -0.4,0.4 -0.1,-0.4 0,1.5 0,0.9 0.5,-0.3 0.3,0.9 0.8,-0.4 0,0.8 1.2,0 0.6,-1 0.9,-0.3 -0.4,-0.6 -0.7,0 0,-0.3 0,-0.3 -0.4,-0.5 -0.6,-0.4 0,-1 0,-0.5 -0.6,0 -1.1,0.2 z" /><g
         id="g60"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path62"
           inkscape:connector-curvature="0"
           d="m 275.7,492.6 0,1.3 -0.4,0.4 -0.1,-0.4 0,1.5 0,0.9 0.5,-0.3 0.3,0.9 0.8,-0.4 0,0.8 1.2,0 0.6,-1 0.9,-0.3 -0.4,-0.6 -0.7,0 0,-0.3 0,-0.3 -0.4,-0.5 -0.6,-0.4 0,-1 0,-0.5 -0.6,0 -1.1,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path64"
         inkscape:connector-curvature="0"
         d="m 279.67474,518.2 -0.2,0 -0.4,0.7 -1.1,1.1 0.4,0 0.3,0.4 0.4,-0.4 0.4,0.4 0.2,0.2 0.4,0 0.2,-0.7 -0.6,-0.4 0.4,-0.4 -0.4,-0.2 0,-0.7 z" /><g
         id="g66"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path68"
           inkscape:connector-curvature="0"
           d="m 281.3,492.6 -0.2,0 -0.4,0.7 -1.1,1.1 0.4,0 0.3,0.4 0.4,-0.4 0.4,0.4 0.2,0.2 0.4,0 0.2,-0.7 -0.6,-0.4 0.4,-0.4 -0.4,-0.2 0,-0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path70"
         inkscape:connector-curvature="0"
         d="m 277.57474,518.5 -0.7,0 -0.4,0.4 -0.5,0.2 1.1,0.4 0.5,-1 z" /><g
         id="g72"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path74"
           inkscape:connector-curvature="0"
           d="m 279.2,492.9 -0.7,0 -0.4,0.4 -0.5,0.2 1.1,0.4 0.5,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path76"
         inkscape:connector-curvature="0"
         d="m 278.77474,517.5 -0.8,0 0.5,0.7 0.3,-0.2 0.4,0 0,-0.5 -0.4,0 z" /><g
         id="g78"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path80"
           inkscape:connector-curvature="0"
           d="m 280.4,491.9 -0.8,0 0.5,0.7 0.3,-0.2 0.4,0 0,-0.5 -0.4,0 z" /></g><path
         style="fill:#00b050;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="DE"
         inkscape:connector-curvature="0"
         d="m 274.17474,518.2 1,-0.2 0.7,0 0.6,-0.4 0,-0.1 0,-0.4 0.4,-0.4 0.6,0 0.6,0 -0.2,-0.9 0.8,0 0.4,0.7 1.3,1 0.4,-0.8 1.4,-0.6 0.2,-0.3 1.1,-0.6 0.4,-0.9 -0.9,-0.6 0,-0.2 1,-0.7 -0.1,-0.2 0.1,-0.9 0.4,0 0,-0.4 0.2,-0.9 0.5,-0.2 -0.5,-0.5 -0.6,0 -0.1,-0.4 -3.1,-1.3 -0.4,0.4 0,-0.4 0,-0.6 0.8,-0.2 0,-0.7 1,-0.6 0.2,-0.1 0.6,-0.8 0,-0.2 0,-0.7 -1.2,-0.2 0,-0.9 0.4,-0.6 -0.4,0.2 -1,0 -0.4,-0.2 -0.7,0 -1,-0.4 -0.2,0.4 -0.6,0 0,-0.4 -1,0.4 -0.4,0.2 -0.5,0 -0.1,0 -0.5,0 0,0.4 -0.2,0 -0.4,0 -0.4,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0 -0.4,0 -0.4,0 0,1 0.8,1.1 0.6,0.9 -1,0.4 -1.7,0.7 0,0.7 -0.6,0.2 0.3,0.6 -0.3,0.9 0,0.8 0.3,0.7 -0.7,0.9 0.7,0.4 0.5,0 -0.2,1.5 0.6,0 0.4,0.9 -0.4,1.1 1.5,0 0.2,-0.5 0.4,0.9 0.6,0 -0.4,0.2 0.4,0.7 -0.4,0 0,0.2 0,0.8 -0.2,0.5 -0.6,0 0.2,0.2 0.4,0 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g84"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path86"
           inkscape:connector-curvature="0"
           d="m 275.8,492.6 1,-0.2 0.7,0 0.6,-0.4 0,-0.1 0,-0.4 0.4,-0.4 0.6,0 0.6,0 -0.2,-0.9 0.8,0 0.4,0.7 1.3,1 0.4,-0.8 1.4,-0.6 0.2,-0.3 1.1,-0.6 0.4,-0.9 -0.9,-0.6 0,-0.2 1,-0.7 -0.1,-0.2 0.1,-0.9 0.4,0 0,-0.4 0.2,-0.9 0.5,-0.2 -0.5,-0.5 -0.6,0 -0.1,-0.4 -3.1,-1.3 -0.4,0.4 0,-0.4 0,-0.6 0.8,-0.2 0,-0.7 1,-0.6 0.2,-0.1 0.6,-0.8 0,-0.2 0,-0.7 -1.2,-0.2 0,-0.9 0.4,-0.6 -0.4,0.2 -1,0 -0.4,-0.2 -0.7,0 -1,-0.4 -0.2,0.4 -0.6,0 0,-0.4 -1,0.4 -0.4,0.2 -0.5,0 -0.1,0 -0.5,0 0,0.4 -0.2,0 -0.4,0 -0.4,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0 -0.4,0 -0.4,0 0,1 0.8,1.1 0.6,0.9 -1,0.4 -1.7,0.7 0,0.7 -0.6,0.2 0.3,0.6 -0.3,0.9 0,0.8 0.3,0.7 -0.7,0.9 0.7,0.4 0.5,0 -0.2,1.5 0.6,0 0.4,0.9 -0.4,1.1 1.5,0 0.2,-0.5 0.4,0.9 0.6,0 -0.4,0.2 0.4,0.7 -0.4,0 0,0.2 0,0.8 -0.2,0.5 -0.6,0 0.2,0.2 0.4,0 z" /></g><path
         style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="FI"
         inkscape:connector-curvature="0"
         d="m 300.57474,553.7 0.4,-0.6 0.2,-0.9 1,-0.5 0.6,-0.6 -0.6,-1.7 2,-3.2 -0.4,-1.8 0,-0.4 0,-0.6 0.7,0 0,-0.5 0,-0.6 0.8,-0.4 -0.4,-1.5 1.4,-0.9 0.6,-0.2 1.1,-0.7 -2.1,-3 -3.3,-3.6 -0.6,-0.3 -1.9,0 -1.2,-0.6 -1.4,-0.3 -1.9,-1 -1,0.8 0,0.2 0.6,0 0,0.7 -0.2,-0.7 -0.4,0.3 0,0.4 -0.6,0.2 -0.8,0 -0.2,0.3 -0.4,1 0.4,0.2 -0.4,0.9 0.4,0 -0.6,1.3 -0.4,1.7 0,0.4 0.6,0.5 0,0.6 0.4,0 0.6,0.4 -0.4,0.3 0.8,0.2 0,0.4 0.6,0.2 0.4,0.9 0.9,1.3 0.4,0.6 0.5,0.5 0.7,0 -0.3,0.6 0,0.9 -0.9,0.6 -0.4,0.4 -0.7,0 -0.2,0.5 -0.4,0.7 0.4,1.5 -0.8,0.6 0,0.2 0.4,0.4 -0.6,0.4 0,1.1 -1,0.9 -1,0 -1.3,0.6 -1,0.9 0,0.4 0.6,-0.4 -0.4,0.6 0.4,0 1,-0.2 1.1,-1 0.8,0 0.8,0 0.2,0.3 0.8,-0.3 0.6,-0.3 0.3,0 0,0.6 0.8,0 0,0.9 0,0.9 0.5,0.9 0.7,0 0.4,0 1,0.4 0.2,-0.4 1.1,-0.3 0.6,-0.6 -0.2,-0.6 -0.4,-0.9 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g90"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path92"
           inkscape:connector-curvature="0"
           d="m 302.2,528.1 0.4,-0.6 0.2,-0.9 1,-0.5 0.6,-0.6 -0.6,-1.7 2,-3.2 -0.4,-1.8 0,-0.4 0,-0.6 0.7,0 0,-0.5 0,-0.6 0.8,-0.4 -0.4,-1.5 1.4,-0.9 0.6,-0.2 1.1,-0.7 -2.1,-3 -3.3,-3.6 -0.6,-0.3 -1.9,0 -1.2,-0.6 -1.4,-0.3 -1.9,-1 -1,0.8 0,0.2 0.6,0 0,0.7 -0.2,-0.7 -0.4,0.3 0,0.4 -0.6,0.2 -0.8,0 -0.2,0.3 -0.4,1 0.4,0.2 -0.4,0.9 0.4,0 -0.6,1.3 -0.4,1.7 0,0.4 0.6,0.5 0,0.6 0.4,0 0.6,0.4 -0.4,0.3 0.8,0.2 0,0.4 0.6,0.2 0.4,0.9 0.9,1.3 0.4,0.6 0.5,0.5 0.7,0 -0.3,0.6 0,0.9 -0.9,0.6 -0.4,0.4 -0.7,0 -0.2,0.5 -0.4,0.7 0.4,1.5 -0.8,0.6 0,0.2 0.4,0.4 -0.6,0.4 0,1.1 -1,0.9 -1,0 -1.3,0.6 -1,0.9 0,0.4 0.6,-0.4 -0.4,0.6 0.4,0 1,-0.2 1.1,-1 0.8,0 0.8,0 0.2,0.3 0.8,-0.3 0.6,-0.3 0.3,0 0,0.6 0.8,0 0,0.9 0,0.9 0.5,0.9 0.7,0 0.4,0 1,0.4 0.2,-0.4 1.1,-0.3 0.6,-0.6 -0.2,-0.6 -0.4,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path94"
         inkscape:connector-curvature="0"
         d="m 265.27474,509.7 0,-0.4 0.7,-0.4 0.4,0.4 0.4,-0.6 0.2,-0.4 1.4,-1.1 0.2,0.6 0.4,-0.9 1.1,-0.4 0.2,0 0.7,0 0.7,0 1.6,-0.7 1,-0.4 -0.6,-1 -0.8,-1.1 0,-0.9 -0.6,0 0,-0.5 -0.6,-0.8 -0.7,-0.9 0,-0.2 0.7,0.2 0.6,-0.6 0.4,-0.5 -0.4,0 0.4,-1.5 -0.8,0 0.4,-0.4 0.4,-1.5 0.6,-0.2 0.6,0 -0.2,-0.3 0,-0.4 -0.4,-0.2 -0.4,0.2 0,-0.2 0,-0.4 -0.6,0 -0.6,-0.2 0,-0.7 -1.4,0 -0.6,0.3 -0.3,0.4 -0.4,0 -0.6,0 0,0.2 -0.8,0 -0.2,0 -0.4,-0.2 -1,-0.7 0,-0.9 -0.7,-0.3 -1,0.3 -1.2,0.3 -1,0.4 0,-0.4 -1.1,0 -2.2,0.6 -0.6,0.9 0.6,2 0.2,0.4 -0.2,0.4 0.2,1.5 0.9,-0.9 -0.5,0.9 -0.6,0.2 0,0.9 -1.4,1.3 0,1 -0.6,0.1 -1.1,0.4 -1.8,0.6 -0.4,0.5 0.4,0 -0.4,0.4 0.7,0 -0.3,0.2 -0.8,0 0.4,0.3 1,0.4 0.6,0 0.6,0 1.1,-0.7 0.4,0.3 0.6,0 0.6,0 -0.2,1.4 -0.4,1.1 0.6,0 0.6,0 0,-0.6 0.9,-0.4 0.8,0 0.3,0.6 0.7,0.4 1.4,0.3 0.2,0.6 0,1.5 2,0.4 z" /><g
         id="g96"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path98"
           inkscape:connector-curvature="0"
           d="m 266.9,484.1 0,-0.4 0.7,-0.4 0.4,0.4 0.4,-0.6 0.2,-0.4 1.4,-1.1 0.2,0.6 0.4,-0.9 1.1,-0.4 0.2,0 0.7,0 0.7,0 1.6,-0.7 1,-0.4 -0.6,-1 -0.8,-1.1 0,-0.9 -0.6,0 0,-0.5 -0.6,-0.8 -0.7,-0.9 0,-0.2 0.7,0.2 0.6,-0.6 0.4,-0.5 -0.4,0 0.4,-1.5 -0.8,0 0.4,-0.4 0.4,-1.5 0.6,-0.2 0.6,0 -0.2,-0.3 0,-0.4 -0.4,-0.2 -0.4,0.2 0,-0.2 0,-0.4 -0.6,0 -0.6,-0.2 0,-0.7 -1.4,0 -0.6,0.3 -0.3,0.4 -0.4,0 -0.6,0 0,0.2 -0.8,0 -0.2,0 -0.4,-0.2 -1,-0.7 0,-0.9 -0.7,-0.3 -1,0.3 -1.2,0.3 -1,0.4 0,-0.4 -1.1,0 -2.2,0.6 -0.6,0.9 0.6,2 0.2,0.4 -0.2,0.4 0.2,1.5 0.9,-0.9 -0.5,0.9 -0.6,0.2 0,0.9 -1.4,1.3 0,1 -0.6,0.1 -1.1,0.4 -1.8,0.6 -0.4,0.5 0.4,0 -0.4,0.4 0.7,0 -0.3,0.2 -0.8,0 0.4,0.3 1,0.4 0.6,0 0.6,0 1.1,-0.7 0.4,0.3 0.6,0 0.6,0 -0.2,1.4 -0.4,1.1 0.6,0 0.6,0 0,-0.6 0.9,-0.4 0.8,0 0.3,0.6 0.7,0.4 1.4,0.3 0.2,0.6 0,1.5 2,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path100"
         inkscape:connector-curvature="0"
         d="m 276.07474,493.4 0.5,0 0,-0.3 0,-0.3 0.4,-0.4 0,-0.5 0,-0.3 -0.4,-0.3 0,-0.4 0,-0.2 -0.5,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0.2 0.2,0 0,0.4 -0.2,0 0,0.2 -0.4,0 0.4,0.4 0,0.3 -0.4,0 0,0.3 0.4,0 -0.4,0.3 0.4,0 0,0.2 0.2,0.4 0.4,0 0.2,0 0,0.3 0,0.3 z" /><g
         id="g97"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path104"
           inkscape:connector-curvature="0"
           d="m 277.7,467.8 0.5,0 0,-0.3 0,-0.3 0.4,-0.4 0,-0.5 0,-0.3 -0.4,-0.3 0,-0.4 0,-0.2 -0.5,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0.2 0.2,0 0,0.4 -0.2,0 0,0.2 -0.4,0 0.4,0.4 0,0.3 -0.4,0 0,0.3 0.4,0 -0.4,0.3 0.4,0 0,0.2 0.2,0.4 0.4,0 0.2,0 0,0.3 0,0.3 z"
           onmouseout="mouseoutpays(this)"
           onclick="mouseclickpays(evt,this)"
           onmouseover="mouseoverpays(evt,this)"><title
             id="title10567" /></path></g><path
         style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path106"
         inkscape:connector-curvature="0"
         d="m 252.37474,507.5 -0.6,0.3 1,0.6 1,1.3 0.6,0.2 1.3,0 0.6,0.9 -0.8,-0.3 -1.5,0.3 -0.2,0.4 -0.8,-0.4 -0.2,0.6 1.6,1.3 0,0.9 -0.6,0 -0.4,0 0.4,0.2 1.3,0.6 1.2,0 -0.2,0.7 0,0.7 0,0.4 -0.4,0 -0.2,1.3 0,0.2 0.6,0.6 -0.6,0 -1.1,-0.2 -1,-0.4 -0.6,0.4 0.6,1.5 0,0.3 0,0.6 -1,0 -0.4,-1.3 0,0.9 0,1.3 0.8,0.8 -0.8,-0.2 -0.6,0.5 0.6,0.6 0.4,0.7 -0.6,0.2 0.2,0.9 0.8,0.4 0,0.6 0.2,0 0.4,1.1 1.2,-0.2 1.5,0.2 0,-1 -1.5,-0.7 0,-0.4 -0.2,-0.5 1.1,0.2 2.2,0 0.4,-0.6 -0.8,-0.9 -1,-1.5 0.4,-0.2 -0.6,-0.4 -0.6,-0.3 1.2,0 1.6,-1.5 1,-1.7 1.3,-0.9 0.3,-1.3 -0.3,0 0.8,-1.2 -0.5,-0.3 0.5,-0.5 0.2,0.3 0.8,0.2 1.2,-0.9 -0.6,-1.5 -0.6,-0.2 -0.4,-0.4 -0.4,-0.3 1.4,-0.2 -0.6,-1 -1,-0.3 -1.5,0.3 -0.6,0 -1.6,-0.3 -1.4,0 -0.8,-0.2 -0.9,-0.8 -0.6,0 -1.6,-0.5 z" /><g
         id="g108"
         transform="translate(-1.6252567,25.599982)"
         style="fill:#00ff00;fill-opacity:1"><path
           style="fill:#00ff00;fill-opacity:1;stroke:#b6dde8;stroke-width:0.24994963;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="GB"
           inkscape:connector-curvature="0"
           d="m 254,481.9 -0.6,0.3 1,0.6 1,1.3 0.6,0.2 1.3,0 0.6,0.9 -0.8,-0.3 -1.5,0.3 -0.2,0.4 -0.8,-0.4 -0.2,0.6 1.6,1.3 0,0.9 -0.6,0 -0.4,0 0.4,0.2 1.3,0.6 1.2,0 -0.2,0.7 0,0.7 0,0.4 -0.4,0 -0.2,1.3 0,0.2 0.6,0.6 -0.6,0 -1.1,-0.2 -1,-0.4 -0.6,0.4 0.6,1.5 0,0.3 0,0.6 -1,0 -0.4,-1.3 0,0.9 0,1.3 0.8,0.8 -0.8,-0.2 -0.6,0.5 0.6,0.6 0.4,0.7 -0.6,0.2 0.2,0.9 0.8,0.4 0,0.6 0.2,0 0.4,1.1 1.2,-0.2 1.5,0.2 0,-1 -1.5,-0.7 0,-0.4 -0.2,-0.5 1.1,0.2 2.2,0 0.4,-0.6 -0.8,-0.9 -1,-1.5 0.4,-0.2 -0.6,-0.4 -0.6,-0.3 1.2,0 1.6,-1.5 1,-1.7 1.3,-0.9 0.3,-1.3 -0.3,0 0.8,-1.2 -0.5,-0.3 0.5,-0.5 0.2,0.3 0.8,0.2 1.2,-0.9 -0.6,-1.5 -0.6,-0.2 -0.4,-0.4 -0.4,-0.3 1.4,-0.2 -0.6,-1 -1,-0.3 -1.5,0.3 -0.6,0 -1.6,-0.3 -1.4,0 -0.8,-0.2 -0.9,-0.8 -0.6,0 -1.6,-0.5 z"
           onmouseover="mouseoverpays(evt,this)"
           onmouseout="mouseoutpays(this)"
           onclick="mouseclickpays(evt,this)" /></g><path
         style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="g110"
         inkscape:connector-curvature="0"
         d="m 249.57474,518.2 0.6,0.4 1,0.4 0.2,0 0.7,-1 -0.3,-0.4 0.3,0 0.4,-0.9 -1.1,-0.6 -0.2,0.4 -0.4,0 -1,1 0,-0.4 0,-0.4 -0.2,-0.2 -0.8,0.2 -0.2,0.8 0.2,0.1 0,0.4 0.8,0.2 z"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g114"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path116"
           inkscape:connector-curvature="0"
           d="m 251.2,492.6 0.6,0.4 1,0.4 0.2,0 0.7,-1 -0.3,-0.4 0.3,0 0.4,-0.9 -1.1,-0.6 -0.2,0.4 -0.4,0 -1,1 0,-0.4 0,-0.4 -0.2,-0.2 -0.8,0.2 -0.2,0.8 0.2,0.1 0,0.4 0.8,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path118"
         inkscape:connector-curvature="0"
         d="m 250.17474,524.9 0,0.4 0,0.2 0,0.3 0.3,0 0,0.2 0.7,0.3 0,-0.3 -0.4,-0.5 0.4,0 -0.4,-0.2 -0.6,-0.4 z" /><g
         id="g120"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path122"
           inkscape:connector-curvature="0"
           d="m 251.8,499.3 0,0.4 0,0.2 0,0.3 0.3,0 0,0.2 0.7,0.3 0,-0.3 -0.4,-0.5 0.4,0 -0.4,-0.2 -0.6,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path124"
         inkscape:connector-curvature="0"
         d="m 251.77474,523.3 -0.4,0 0.4,0 -0.6,0 -0.4,0.7 -0.4,0 0.4,0.6 0.4,-0.3 0,0.3 0.2,0 0,-0.9 0.7,0 -0.3,-0.4 z" /><g
         id="g126"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path128"
           inkscape:connector-curvature="0"
           d="m 253.4,497.7 -0.4,0 0.4,0 -0.6,0 -0.4,0.7 -0.4,0 0.4,0.6 0.4,-0.3 0,0.3 0.2,0 0,-0.9 0.7,0 -0.3,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path130"
         inkscape:connector-curvature="0"
         d="m 258.67474,529.9 0,0.7 -0.4,0.3 0.4,0 -0.4,0.6 0.7,0.3 0,-0.6 0.4,-0.6 -0.7,-0.7 z" /><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path814"
         inkscape:connector-curvature="0"
         d="m 474.07474,436 0,1.7 0,1 0.7,0 0.4,-0.6 0.2,0.4 0.7,-0.4 0,-0.5 -0.3,-0.4 -1.7,-1.2 z" /><g
         id="g132"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path134"
           inkscape:connector-curvature="0"
           d="m 260.3,504.3 0,0.7 -0.4,0.3 0.4,0 -0.4,0.6 0.7,0.3 0,-0.6 0.4,-0.6 -0.7,-0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path136"
         inkscape:connector-curvature="0"
         d="m 250.77474,535.1 -0.6,0.6 0.3,0.3 0.7,-0.5 -0.4,-0.4 z" /><g
         id="g138"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path140"
           inkscape:connector-curvature="0"
           d="m 252.4,509.5 -0.6,0.6 0.3,0.3 0.7,-0.5 -0.4,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path142"
         inkscape:connector-curvature="0"
         d="m 256.77474,527.7 -1,0 0,0.6 0.4,-0.4 0.6,0 0,-0.2 z" /><g
         id="g144"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path146"
           inkscape:connector-curvature="0"
           d="m 258.4,502.1 -1,0 0,0.6 0.4,-0.4 0.6,0 0,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path148"
         inkscape:connector-curvature="0"
         d="m 249.87474,523.3 -0.3,0 0,0.6 0.3,-0.2 0,-0.4 z" /><g
         id="g150"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path152"
           inkscape:connector-curvature="0"
           d="m 251.5,497.7 -0.3,0 0,0.6 0.3,-0.2 0,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path154"
         inkscape:connector-curvature="0"
         d="m 249.87474,524.3 -0.7,0.3 0.7,0 0,-0.3 z" /><g
         id="g156"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path158"
           inkscape:connector-curvature="0"
           d="m 251.5,498.7 -0.7,0.3 0.7,0 0,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path160"
         inkscape:connector-curvature="0"
         d="m 249.57474,518.2 -0.8,-0.2 0,-0.4 -0.3,-0.1 0.3,-0.8 0.8,-0.2 0.2,0.2 0,0.4 0,0.4 1,-1 0.4,0 0.2,-0.3 -0.2,-0.4 -0.4,0.4 0.4,-0.9 0,-0.6 0,-0.4 0,-0.7 -0.4,-0.4 0,-0.4 0,-0.9 -1,0 -1.3,-0.2 -0.8,-0.5 0,0.1 -0.5,-0.1 -1.1,-0.4 -0.6,0 -0.6,0 0.6,0.5 -1,0 0,0.4 0.9,0.2 -1.1,0 0.2,0.4 0.9,0 0,0.3 0.1,0.2 1,0.4 0.4,0 -0.4,0.4 -0.4,-0.4 -0.6,0 0.4,0.4 0.2,0.6 0.4,0.1 -1,0.4 -0.6,0.6 0.6,0.3 0.4,0 -0.4,0.2 -0.1,0.7 -0.5,-0.3 0.5,0.5 0.5,0 0.6,-0.2 0.7,0.2 0.4,0 0.1,0.4 0.5,0.4 0,0.1 -1,0 0.4,0.4 0.1,0.2 1.1,0.8 0.8,0.1 0.6,-0.1 -0.6,-0.8 z" /><g
         id="g162"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path164"
           inkscape:connector-curvature="0"
           d="m 251.2,492.6 -0.8,-0.2 0,-0.4 -0.3,-0.1 0.3,-0.8 0.8,-0.2 0.2,0.2 0,0.4 0,0.4 1,-1 0.4,0 0.2,-0.3 -0.2,-0.4 -0.4,0.4 0.4,-0.9 0,-0.6 0,-0.4 0,-0.7 -0.4,-0.4 0,-0.4 0,-0.9 -1,0 -1.3,-0.2 -0.8,-0.5 0,0.1 -0.5,-0.1 -1.1,-0.4 -0.6,0 -0.6,0 0.6,0.5 -1,0 0,0.4 0.9,0.2 -1.1,0 0.2,0.4 0.9,0 0,0.3 0.1,0.2 1,0.4 0.4,0 -0.4,0.4 -0.4,-0.4 -0.6,0 0.4,0.4 0.2,0.6 0.4,0.1 -1,0.4 -0.6,0.6 0.6,0.3 0.4,0 -0.4,0.2 -0.1,0.7 -0.5,-0.3 0.5,0.5 0.5,0 0.6,-0.2 0.7,0.2 0.4,0 0.1,0.4 0.5,0.4 0,0.1 -1,0 0.4,0.4 0.1,0.2 1.1,0.8 0.8,0.1 0.6,-0.1 -0.6,-0.8 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path166"
         inkscape:connector-curvature="0"
         d="m 234.07474,539.3 -2,0.6 -1,0.6 -2.7,0 0,0.5 0.4,0 1.1,0.6 -0.9,0.8 0,0.9 -1.8,0 -0.4,0.2 1.6,0.3 1.7,0 0.4,0.2 -0.4,0 -0.7,0.4 0.7,0.6 -1.3,0.6 -1.4,-0.6 -1,0.3 0.8,0.6 0.6,-0.3 0.2,0.3 -0.6,0.4 0.4,0.2 0.2,0.7 0.8,-0.7 0.6,0 -0.6,0.9 1.1,0.6 1.2,-1.1 -0.4,-1.3 0.4,-0.8 0.6,0.8 0.4,-0.3 0.4,0 0,1.2 0.2,0.4 0.6,-1.3 0.4,0.3 0,0.6 0.6,0.4 0.7,-0.6 0.7,-0.4 -0.3,1 0.9,-0.6 0.7,0.6 0.6,-0.4 0.4,0.4 0,0.5 0.6,0.3 0.4,-0.8 1.7,0.3 -0.7,-0.7 0.7,0 -0.4,-0.9 0.4,0 1.2,-1.2 0,-0.6 -0.3,-0.3 -0.3,-1 -0.6,-0.1 -0.4,-0.8 -1.3,-0.2 -1.4,-0.5 -0.8,-0.8 -1.4,-0.5 -1.3,-0.3 z" /><g
         id="g168"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path170"
           inkscape:connector-curvature="0"
           d="m 235.7,513.7 -2,0.6 -1,0.6 -2.7,0 0,0.5 0.4,0 1.1,0.6 -0.9,0.8 0,0.9 -1.8,0 -0.4,0.2 1.6,0.3 1.7,0 0.4,0.2 -0.4,0 -0.7,0.4 0.7,0.6 -1.3,0.6 -1.4,-0.6 -1,0.3 0.8,0.6 0.6,-0.3 0.2,0.3 -0.6,0.4 0.4,0.2 0.2,0.7 0.8,-0.7 0.6,0 -0.6,0.9 1.1,0.6 1.2,-1.1 -0.4,-1.3 0.4,-0.8 0.6,0.8 0.4,-0.3 0.4,0 0,1.2 0.2,0.4 0.6,-1.3 0.4,0.3 0,0.6 0.6,0.4 0.7,-0.6 0.7,-0.4 -0.3,1 0.9,-0.6 0.7,0.6 0.6,-0.4 0.4,0.4 0,0.5 0.6,0.3 0.4,-0.8 1.7,0.3 -0.7,-0.7 0.7,0 -0.4,-0.9 0.4,0 1.2,-1.2 0,-0.6 -0.3,-0.3 -0.3,-1 -0.6,-0.1 -0.4,-0.8 -1.3,-0.2 -1.4,-0.5 -0.8,-0.8 -1.4,-0.5 -1.3,-0.3 z" /></g><path
         style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="IT"
         inkscape:connector-curvature="0"
         d="m 278.07474,501 1.1,0 0,0.5 1,0 0.6,0 1,-0.5 1.1,-0.8 0.1,0 -0.1,-0.2 0.9,-0.9 0.2,-0.6 -0.2,-0.3 -0.8,0.3 -0.1,0.2 -0.7,0 0,-0.2 -0.8,-0.3 -0.6,-0.2 0.4,-0.7 -0.4,-0.3 0,-0.7 2.2,-2.1 1.5,-2 2,-0.9 0.6,0 0.2,-0.4 -0.2,-0.2 0,-0.4 1.2,-0.5 1.6,-0.6 1.5,-1.3 0,-0.5 -0.6,0 -0.5,0.3 -0.4,0.6 -1,0.2 -0.2,-0.2 -0.6,-1.3 0.6,-0.2 0.2,0 0.5,-1.3 -1.1,-0.1 0,-0.8 -0.6,-0.8 -0.6,-0.4 0,0.6 0.2,0.4 0,0.5 0.4,0.5 -1,2.3 -0.2,0 -0.8,0.2 0,0.4 -0.2,0.6 -0.9,0 -0.1,0.3 -0.5,0 -0.2,0.2 0,0.7 -1.4,0 -1,0.2 -1,1.4 -0.2,0.1 -1,1 -0.7,0.3 -0.6,0 0.2,0.6 -0.2,0.4 -0.4,0.7 -0.4,0.8 -1,0.1 -0.8,0 -0.8,-0.1 -0.7,-0.8 -0.1,0 0,0.4 0.1,0.4 -0.6,0 -0.6,0.1 -0.4,1.6 -0.4,0.3 0.8,0 -0.4,1.5 0.4,0 1.1,0.4 0.1,-0.4 0.7,0.6 1.4,-0.2 0.2,0.2 0,0.5 0.4,0 0.4,-0.2 0.2,0 0.4,-0.3 0,0.5 0.4,0 0.2,0.8 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g174"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path176"
           inkscape:connector-curvature="0"
           d="m 279.7,475.4 1.1,0 0,0.5 1,0 0.6,0 1,-0.5 1.1,-0.8 0.1,0 -0.1,-0.2 0.9,-0.9 0.2,-0.6 -0.2,-0.3 -0.8,0.3 -0.1,0.2 -0.7,0 0,-0.2 -0.8,-0.3 -0.6,-0.2 0.4,-0.7 -0.4,-0.3 0,-0.7 2.2,-2.1 1.5,-2 2,-0.9 0.6,0 0.2,-0.4 -0.2,-0.2 0,-0.4 1.2,-0.5 1.6,-0.6 1.5,-1.3 0,-0.5 -0.6,0 -0.5,0.3 -0.4,0.6 -1,0.2 -0.2,-0.2 -0.6,-1.3 0.6,-0.2 0.2,0 0.5,-1.3 -1.1,-0.1 0,-0.8 -0.6,-0.8 -0.6,-0.4 0,0.6 0.2,0.4 0,0.5 0.4,0.5 -1,2.3 -0.2,0 -0.8,0.2 0,0.4 -0.2,0.6 -0.9,0 -0.1,0.3 -0.5,0 -0.2,0.2 0,0.7 -1.4,0 -1,0.2 -1,1.4 -0.2,0.1 -1,1 -0.7,0.3 -0.6,0 0.2,0.6 -0.2,0.4 -0.4,0.7 -0.4,0.8 -1,0.1 -0.8,0 -0.8,-0.1 -0.7,-0.8 -0.1,0 0,0.4 0.1,0.4 -0.6,0 -0.6,0.1 -0.4,1.6 -0.4,0.3 0.8,0 -0.4,1.5 0.4,0 1.1,0.4 0.1,-0.4 0.7,0.6 1.4,-0.2 0.2,0.2 0,0.5 0.4,0 0.4,-0.2 0.2,0 0.4,-0.3 0,0.5 0.4,0 0.2,0.8 z" /></g><path
         style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path178"
         inkscape:connector-curvature="0"
         d="m 286.07474,481.4 -1,0.2 -0.4,0.4 -0.8,0.4 -1.4,0.5 -0.7,0.2 0,0.7 0.5,0.3 0.2,-0.3 0.4,0.3 0.1,0 1.1,-0.3 2.4,0.3 0,-0.6 -0.4,-0.6 0.4,-0.9 -0.4,-0.6 z" /><g
         id="g180"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path182"
           inkscape:connector-curvature="0"
           d="m 287.7,455.8 -1,0.2 -0.4,0.4 -0.8,0.4 -1.4,0.5 -0.7,0.2 0,0.7 0.5,0.3 0.2,-0.3 0.4,0.3 0.1,0 1.1,-0.3 2.4,0.3 0,-0.6 -0.4,-0.6 0.4,-0.9 -0.4,-0.6 z" /></g><path
         style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path184"
         inkscape:connector-curvature="0"
         d="m 275.47474,485.4 -0.6,0.4 0,1 0,0.1 0,0.8 0,0.2 -0.7,1 0.3,0.3 0.4,0 1.2,0.6 0.4,0 0.6,-1.3 -0.2,-0.2 0,-0.4 -0.4,-2.1 -0.6,0.4 -0.4,-0.8 z" /><g
         id="g186"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path188"
           inkscape:connector-curvature="0"
           d="m 277.1,459.8 -0.6,0.4 0,1 0,0.1 0,0.8 0,0.2 -0.7,1 0.3,0.3 0.4,0 1.2,0.6 0.4,0 0.6,-1.3 -0.2,-0.2 0,-0.4 -0.4,-2.1 -0.6,0.4 -0.4,-0.8 z" /></g><path
         style="fill:#ffc000;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="LU"
         inkscape:connector-curvature="0"
         d="m 270.17474,506.5 0,1 0.4,0.3 0.6,0.2 -0.2,-0.5 0.7,-0.3 0,-0.7 -0.7,0 -0.8,0 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g192"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path194"
           inkscape:connector-curvature="0"
           d="m 271.8,480.9 0,1 0.4,0.3 0.6,0.2 -0.2,-0.5 0.7,-0.3 0,-0.7 -0.7,0 -0.8,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path196"
         inkscape:connector-curvature="0"
         d="m 285.07474,479.5 -0.5,0.6 0.5,-0.2 0,-0.4 z" /><g
         id="g198"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path200"
           inkscape:connector-curvature="0"
           d="m 286.7,453.9 -0.5,0.6 0.5,-0.2 0,-0.4 z" /></g><path
         style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="NL"
         inkscape:connector-curvature="0"
         d="m 266.67474,510.2 0,0.5 0.6,0.4 1,0 -0.8,0.2 -0.2,0.4 0.6,0.9 0.4,1.5 0.7,-0.9 0.4,-0.9 0.6,-0.5 -0.4,1 0.4,0.7 -0.4,0 0,0.6 1,0.6 1,0.3 0.2,-0.7 0,0.4 0.4,0.5 0.4,-1.1 -0.4,-0.9 -0.6,0 0.2,-1.5 -0.6,0 -0.6,-0.4 0.6,-0.9 -0.2,-0.8 0,-0.7 -1,0.3 0.2,0.6 -0.8,0.4 -0.9,0.2 -0.6,0 -0.6,-0.2 -0.4,-0.4 -0.2,0.4 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g204"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path206"
           inkscape:connector-curvature="0"
           d="m 268.3,484.6 0,0.5 0.6,0.4 1,0 -0.8,0.2 -0.2,0.4 0.6,0.9 0.4,1.5 0.7,-0.9 0.4,-0.9 0.6,-0.5 -0.4,1 0.4,0.7 -0.4,0 0,0.6 1,0.6 1,0.3 0.2,-0.7 0,0.4 0.4,0.5 0.4,-1.1 -0.4,-0.9 -0.6,0 0.2,-1.5 -0.6,0 -0.6,-0.4 0.6,-0.9 -0.2,-0.8 0,-0.7 -1,0.3 0.2,0.6 -0.8,0.4 -0.9,0.2 -0.6,0 -0.6,-0.2 -0.4,-0.4 -0.2,0.4 z" /></g><path
         style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="NO"
         inkscape:connector-curvature="0"
         d="m 302.77474,555.6 -0.6,0 -1.7,-1.9 0.4,0.9 0.3,0.6 -0.7,0.5 -1,0.4 -0.2,0.4 -1,-0.4 -0.5,0 -0.6,0 -0.6,-0.9 0,-0.9 0,-1 -0.7,0 0,-0.5 -0.3,0 -0.6,0.3 -0.8,0.2 -0.2,-0.2 -0.8,0 -0.8,0 -1,1 -1.1,0.2 -0.4,0 0.4,-0.6 -0.6,0.4 0,-0.4 -0.8,0 0,-0.6 0,-0.5 0.4,-0.4 -0.4,-0.4 -1.2,0.4 -1,0.4 -0.3,-0.4 0,-0.5 0,-0.6 -0.9,0.2 -0.9,-0.2 0,-0.8 -0.6,-0.5 0.4,-0.9 -0.4,-0.3 -1,-1.2 0,-0.6 -0.8,-0.6 0,-1.8 -0.5,-1.1 -0.6,-1.3 0.6,-0.4 0,-0.2 0,-0.9 -1.4,0.4 -0.5,-0.4 -1.1,-0.9 0,-1 0.4,-0.5 -0.4,-0.6 0.4,-1.3 0.4,-1.6 0.3,-0.4 0.5,-0.6 -0.2,-0.6 -0.6,-0.4 0.6,-1.1 0,-0.7 -0.3,-0.9 -0.7,0 0,-0.2 -0.4,-0.4 0,-1.7 -0.8,0.3 -0.8,0.5 0,1.3 0,0.2 -0.2,-0.2 0,-0.6 -0.4,0 0.4,-0.7 -0.9,-0.8 -0.6,0.6 -0.2,-0.3 0.2,-0.3 -1,-1.3 -0.8,-0.9 -1.8,-0.2 -1.2,0.6 -1.3,1.1 0,0.7 0.9,-0.3 -0.3,0.6 0.3,0.8 -0.3,0 -0.4,-0.3 -0.6,-0.5 -0.4,0.5 0.8,0.9 0.2,0 0.4,0.4 -0.4,0 0.7,0.5 1,1 -1.3,-0.4 -0.4,-0.6 -0.2,0.6 -0.4,-0.6 0,1 -0.4,0.9 0,0.5 2.1,0 1.2,0 0.4,0.4 -0.4,0.2 0,-0.2 -0.6,0 -0.6,0 -1.7,0 -0.4,0.2 0,0.4 0.4,0.2 -0.6,0.4 0.6,0.4 1.3,0 0.4,0 -1.1,0.5 -1,0 0,0.3 0.8,0 0.6,0.3 1.3,0.3 0,-0.3 0.4,0 -0.4,0.5 -1.3,0 0,0.4 1.3,0.3 0.4,-0.3 1,0.5 -1.4,0 0,0.4 1,-0.4 1,-0.2 -0.6,0.6 0.2,0.2 1,-0.6 -0.2,0.6 -0.4,0.4 0.4,0 0,0.5 1.6,0.8 0.2,-0.8 1.5,0.4 0.6,0.9 0,0.2 0,0.4 -0.6,-0.6 -1,-0.9 -1.1,0.6 1.1,0.5 0,0.4 1,1.1 0.6,-0.2 0.4,0.6 -0.4,0 0.6,0.9 0.4,0.6 0.6,-0.2 0.4,0.5 -0.8,0 0,0.2 -0.2,-0.2 0,1 0.6,-0.4 -0.4,0.4 0.4,0.7 0.7,0 -0.3,0.7 1.6,0.2 0,0.4 -1.6,-0.4 0,1 0.6,0.9 0.6,0.6 2.1,0 0,0.3 -1.7,0 1.1,0.6 0.2,0 0.4,-0.4 0,0.4 -0.4,0.4 0.4,0.1 -0.6,-0.1 -0.5,0.1 1.1,0.4 0.2,0 0,0.4 0.8,0 0,-0.4 0.2,0.4 -0.2,0 0.2,0.7 0.6,0 0.9,0 0,0.4 -0.9,-0.4 -0.6,0 0.4,0.8 0.2,0 0.9,0.5 0,0.2 0.2,0.4 -0.2,0 -0.9,-0.4 -0.2,0.4 0.2,0.6 2.1,0.3 0.6,-0.5 0.4,0 0,0.2 -0.6,0 -0.4,0.7 1,0.6 0.4,-1 0,0.6 0.2,0.4 0.4,-1.3 0.2,0 0.4,0.3 -0.4,0.6 0.8,0.4 0.2,0.1 1,-0.5 0,0.9 -0.6,0 -0.6,0.6 1,0 1.3,0 0.4,-0.2 0.6,0.5 0,1 0.6,-0.8 0.4,0.4 -0.4,0.4 0.4,0.6 1,0 0.6,0 -0.6,-1 0,-0.9 0.2,-0.2 0,0.5 1.4,1.6 0,-0.6 0.2,-0.8 0.4,0.4 0.2,0.4 0.4,0.1 -0.6,0.5 0.2,0.5 1.1,0 0.8,-0.2 0,-0.3 -0.8,-0.5 0.8,-0.1 -0.8,-0.4 0.8,0 0.6,1 0.2,0 0.4,-0.5 0.4,0.5 1.1,-0.6 -0.4,0 0.6,0 0.6,-0.8 -0.8,-0.5 -2.1,0 1,-0.2 0.7,-0.8 0.4,0.4 0.5,0 0.7,-0.4 0.4,-0.1 -0.8,0 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g210"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path212"
           inkscape:connector-curvature="0"
           d="m 304.4,530 -0.6,0 -1.7,-1.9 0.4,0.9 0.3,0.6 -0.7,0.5 -1,0.4 -0.2,0.4 -1,-0.4 -0.5,0 -0.6,0 -0.6,-0.9 0,-0.9 0,-1 -0.7,0 0,-0.5 -0.3,0 -0.6,0.3 -0.8,0.2 -0.2,-0.2 -0.8,0 -0.8,0 -1,1 -1.1,0.2 -0.4,0 0.4,-0.6 -0.6,0.4 0,-0.4 -0.8,0 0,-0.6 0,-0.5 0.4,-0.4 -0.4,-0.4 -1.2,0.4 -1,0.4 -0.3,-0.4 0,-0.5 0,-0.6 -0.9,0.2 -0.9,-0.2 0,-0.8 -0.6,-0.5 0.4,-0.9 -0.4,-0.3 -1,-1.2 0,-0.6 -0.8,-0.6 0,-1.8 -0.5,-1.1 -0.6,-1.3 0.6,-0.4 0,-0.2 0,-0.9 -1.4,0.4 -0.5,-0.4 -1.1,-0.9 0,-1 0.4,-0.5 -0.4,-0.6 0.4,-1.3 0.4,-1.6 0.3,-0.4 0.5,-0.6 -0.2,-0.6 -0.6,-0.4 0.6,-1.1 0,-0.7 -0.3,-0.9 -0.7,0 0,-0.2 -0.4,-0.4 0,-1.7 -0.8,0.3 -0.8,0.5 0,1.3 0,0.2 -0.2,-0.2 0,-0.6 -0.4,0 0.4,-0.7 -0.9,-0.8 -0.6,0.6 -0.2,-0.3 0.2,-0.3 -1,-1.3 -0.8,-0.9 -1.8,-0.2 -1.2,0.6 -1.3,1.1 0,0.7 0.9,-0.3 -0.3,0.6 0.3,0.8 -0.3,0 -0.4,-0.3 -0.6,-0.5 -0.4,0.5 0.8,0.9 0.2,0 0.4,0.4 -0.4,0 0.7,0.5 1,1 -1.3,-0.4 -0.4,-0.6 -0.2,0.6 -0.4,-0.6 0,1 -0.4,0.9 0,0.5 2.1,0 1.2,0 0.4,0.4 -0.4,0.2 0,-0.2 -0.6,0 -0.6,0 -1.7,0 -0.4,0.2 0,0.4 0.4,0.2 -0.6,0.4 0.6,0.4 1.3,0 0.4,0 -1.1,0.5 -1,0 0,0.3 0.8,0 0.6,0.3 1.3,0.3 0,-0.3 0.4,0 -0.4,0.5 -1.3,0 0,0.4 1.3,0.3 0.4,-0.3 1,0.5 -1.4,0 0,0.4 1,-0.4 1,-0.2 -0.6,0.6 0.2,0.2 1,-0.6 -0.2,0.6 -0.4,0.4 0.4,0 0,0.5 1.6,0.8 0.2,-0.8 1.5,0.4 0.6,0.9 0,0.2 0,0.4 -0.6,-0.6 -1,-0.9 -1.1,0.6 1.1,0.5 0,0.4 1,1.1 0.6,-0.2 0.4,0.6 -0.4,0 0.6,0.9 0.4,0.6 0.6,-0.2 0.4,0.5 -0.8,0 0,0.2 -0.2,-0.2 0,1 0.6,-0.4 -0.4,0.4 0.4,0.7 0.7,0 -0.3,0.7 1.6,0.2 0,0.4 -1.6,-0.4 0,1 0.6,0.9 0.6,0.6 2.1,0 0,0.3 -1.7,0 1.1,0.6 0.2,0 0.4,-0.4 0,0.4 -0.4,0.4 0.4,0.1 -0.6,-0.1 -0.5,0.1 1.1,0.4 0.2,0 0,0.4 0.8,0 0,-0.4 0.2,0.4 -0.2,0 0.2,0.7 0.6,0 0.9,0 0,0.4 -0.9,-0.4 -0.6,0 0.4,0.8 0.2,0 0.9,0.5 0,0.2 0.2,0.4 -0.2,0 -0.9,-0.4 -0.2,0.4 0.2,0.6 2.1,0.3 0.6,-0.5 0.4,0 0,0.2 -0.6,0 -0.4,0.7 1,0.6 0.4,-1 0,0.6 0.2,0.4 0.4,-1.3 0.2,0 0.4,0.3 -0.4,0.6 0.8,0.4 0.2,0.1 1,-0.5 0,0.9 -0.6,0 -0.6,0.6 1,0 1.3,0 0.4,-0.2 0.6,0.5 0,1 0.6,-0.8 0.4,0.4 -0.4,0.4 0.4,0.6 1,0 0.6,0 -0.6,-1 0,-0.9 0.2,-0.2 0,0.5 1.4,1.6 0,-0.6 0.2,-0.8 0.4,0.4 0.2,0.4 0.4,0.1 -0.6,0.5 0.2,0.5 1.1,0 0.8,-0.2 0,-0.3 -0.8,-0.5 0.8,-0.1 -0.8,-0.4 0.8,0 0.6,1 0.2,0 0.4,-0.5 0.4,0.5 1.1,-0.6 -0.4,0 0.6,0 0.6,-0.8 -0.8,-0.5 -2.1,0 1,-0.2 0.7,-0.8 0.4,0.4 0.5,0 0.7,-0.4 0.4,-0.1 -0.8,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path214"
         inkscape:connector-curvature="0"
         d="m 282.37474,551.6 -0.2,0.6 0.8,1 0.5,-0.6 0.4,0.6 0.2,-0.6 -1.1,-0.8 -0.1,-0.2 -0.5,0 z" /><g
         id="g216"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path218"
           inkscape:connector-curvature="0"
           d="m 284,526 -0.2,0.6 0.8,1 0.5,-0.6 0.4,0.6 0.2,-0.6 -1.1,-0.8 -0.1,-0.2 -0.5,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path220"
         inkscape:connector-curvature="0"
         d="m 281.77474,552.2 -0.3,0 0.8,0.3 -0.5,0 -0.6,-0.3 0,0.6 0.6,0 0,0.5 0.5,-0.2 0,-0.6 -0.5,-0.3 z" /><g
         id="g222"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path224"
           inkscape:connector-curvature="0"
           d="m 283.4,526.6 -0.3,0 0.8,0.3 -0.5,0 -0.6,-0.3 0,0.6 0.6,0 0,0.5 0.5,-0.2 0,-0.6 -0.5,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path226"
         inkscape:connector-curvature="0"
         d="m 283.07474,504.8 1,-0.4 0.6,0 0.8,1 1,-0.4 1.6,-0.2 0,-0.9 0.7,-0.6 0,-0.3 -0.4,-0.4 -0.6,0 -0.4,0 0.4,-0.2 0,-1.3 -0.6,-0.2 0,-0.4 -0.7,0 -1.8,-0.3 -1.6,0 -0.2,0 -1.1,0.7 -1,0.6 -0.6,0 -1,0 0,-0.6 -1,0 -0.2,0 0,0.2 -0.9,0 0,0.4 -0.1,0 -0.4,0.4 0,0.1 0.4,0.4 1,-0.4 0,0.4 0.6,0 0.2,-0.4 1,0.4 0.6,0 0.4,0.2 1,0 0.5,-0.2 -0.5,0.6 0,0.9 1.3,0.2 0,0.7 z" /><g
         id="g228"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path230"
           inkscape:connector-curvature="0"
           d="m 284.7,479.2 1,-0.4 0.6,0 0.8,1 1,-0.4 1.6,-0.2 0,-0.9 0.7,-0.6 0,-0.3 -0.4,-0.4 -0.6,0 -0.4,0 0.4,-0.2 0,-1.3 -0.6,-0.2 0,-0.4 -0.7,0 -1.8,-0.3 -1.6,0 -0.2,0 -1.1,0.7 -1,0.6 -0.6,0 -1,0 0,-0.6 -1,0 -0.2,0 0,0.2 -0.9,0 0,0.4 -0.1,0 -0.4,0.4 0,0.1 0.4,0.4 1,-0.4 0,0.4 0.6,0 0.2,-0.4 1,0.4 0.6,0 0.4,0.2 1,0 0.5,-0.2 -0.5,0.6 0,0.9 1.3,0.2 0,0.7 z" /></g><path
         style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="PL"
         inkscape:connector-curvature="0"
         d="m 291.47474,517.5 5.3,0 1.1,-1.7 0.5,-1.2 0.5,-1.1 -1.4,-0.7 0.9,-1 0.5,-1.1 0.6,-1.1 -2.3,-2.8 0.3,-0.8 -0.3,-0.3 -0.4,0.3 -1,0.3 -0.8,0.2 -0.4,-0.2 -0.6,0 -1,-0.3 -0.4,0.3 -0.3,0.5 -0.8,-0.3 -0.8,0.7 0,0.2 -1.4,0.4 0,0.2 -0.6,0.3 -0.7,-0.3 -0.4,0 -0.6,0.3 0,0.4 0,0.2 -0.4,0 -1,0.3 0,0.4 -0.6,0 -0.5,0 0.5,0.6 -0.5,0.2 -0.1,0.9 0,0.4 -0.5,0 -0.2,0.9 0.2,0.2 -1,0.7 0,0.2 0.8,0.6 -0.3,0.9 0.3,0.4 0.2,0 0,0.6 2.1,0.5 0.4,0.8 0.6,0.1 0.2,0 2.1,0.4 0.4,-0.5 0.2,-0.4 0.8,0 0.6,-0.4 0.2,0.8 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g234"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path236"
           inkscape:connector-curvature="0"
           d="m 293.1,491.9 5.3,0 1.1,-1.7 0.5,-1.2 0.5,-1.1 -1.4,-0.7 0.9,-1 0.5,-1.1 0.6,-1.1 -2.3,-2.8 0.3,-0.8 -0.3,-0.3 -0.4,0.3 -1,0.3 -0.8,0.2 -0.4,-0.2 -0.6,0 -1,-0.3 -0.4,0.3 -0.3,0.5 -0.8,-0.3 -0.8,0.7 0,0.2 -1.4,0.4 0,0.2 -0.6,0.3 -0.7,-0.3 -0.4,0 -0.6,0.3 0,0.4 0,0.2 -0.4,0 -1,0.3 0,0.4 -0.6,0 -0.5,0 0.5,0.6 -0.5,0.2 -0.1,0.9 0,0.4 -0.5,0 -0.2,0.9 0.2,0.2 -1,0.7 0,0.2 0.8,0.6 -0.3,0.9 0.3,0.4 0.2,0 0,0.6 2.1,0.5 0.4,0.8 0.6,0.1 0.2,0 2.1,0.4 0.4,-0.5 0.2,-0.4 0.8,0 0.6,-0.4 0.2,0.8 z" /></g><path
         style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="PT"
         inkscape:connector-curvature="0"
         d="m 246.07474,491.8 0.4,-0.3 0.6,0.3 0.4,-0.6 0.7,0 0.6,0 0.8,0 0.6,-0.4 0.3,-0.1 -0.9,-1.3 0,-1 -0.4,-1.5 -0.6,0 0.2,-1.1 0,-0.3 0,-0.5 0.4,-0.6 0,-0.4 -0.6,-0.5 0,-1.2 -1.5,-0.3 -0.6,0.3 -1,0 0.4,0.6 0,1.7 0,0.4 -0.4,0 0,0.4 -0.7,0 0.5,1 0.2,0.4 1,2.4 -0.4,2.6 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g240"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path242"
           inkscape:connector-curvature="0"
           d="m 247.7,466.2 0.4,-0.3 0.6,0.3 0.4,-0.6 0.7,0 0.6,0 0.8,0 0.6,-0.4 0.3,-0.1 -0.9,-1.3 0,-1 -0.4,-1.5 -0.6,0 0.2,-1.1 0,-0.3 0,-0.5 0.4,-0.6 0,-0.4 -0.6,-0.5 0,-1.2 -1.5,-0.3 -0.6,0.3 -1,0 0.4,0.6 0,1.7 0,0.4 -0.4,0 0,0.4 -0.7,0 0.5,1 0.2,0.4 1,2.4 -0.4,2.6 z" /></g><path
         style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="RO"
         inkscape:connector-curvature="0"
         d="m 303.17474,503.5 1.2,0.4 1.8,-2.4 0.2,0 0.5,-3 0.6,-0.6 1.6,0.6 0,-1.3 -1,-0.2 0,0.6 -0.2,0 0,-0.6 0,-0.4 0,-0.3 -0.4,-1.1 -0.5,0 -0.1,0.3 -1.5,0 -0.1,0.2 -1.7,-0.5 -0.8,-0.4 -0.2,-0.2 -1.1,0.2 -0.9,0 -0.5,0 -0.8,0.4 -1,-0.4 0,0.4 0.2,0.3 -0.6,0.2 -0.4,0.6 0.4,0.3 -0.4,0 -0.2,-0.3 -0.7,0.3 -0.8,0.4 0,0.2 0,0.7 -1.2,0.6 0,0.6 -0.4,0.5 0.8,0 0,0.4 0.6,0.2 0,0.3 0.2,0.6 0.4,1.3 0.7,0.6 0.6,0.4 0.4,0.1 0.6,0 3,-0.5 1.7,0.5 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g246"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path248"
           inkscape:connector-curvature="0"
           d="m 304.8,477.9 1.2,0.4 1.8,-2.4 0.2,0 0.5,-3 0.6,-0.6 1.6,0.6 0,-1.3 -1,-0.2 0,0.6 -0.2,0 0,-0.6 0,-0.4 0,-0.3 -0.4,-1.1 -0.5,0 -0.1,0.3 -1.5,0 -0.1,0.2 -1.7,-0.5 -0.8,-0.4 -0.2,-0.2 -1.1,0.2 -0.9,0 -0.5,0 -0.8,0.4 -1,-0.4 0,0.4 0.2,0.3 -0.6,0.2 -0.4,0.6 0.4,0.3 -0.4,0 -0.2,-0.3 -0.7,0.3 -0.8,0.4 0,0.2 0,0.7 -1.2,0.6 0,0.6 -0.4,0.5 0.8,0 0,0.4 0.6,0.2 0,0.3 0.2,0.6 0.4,1.3 0.7,0.6 0.6,0.4 0.4,0.1 0.6,0 3,-0.5 1.7,0.5 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="SE"
         inkscape:connector-curvature="0"
         d="m 289.67474,553.6 1,-0.9 1.2,-0.6 1.1,0 1,-0.9 0,-1.1 0.6,-0.4 -0.4,-0.3 0,-0.3 0.8,-0.5 -0.4,-1.5 0.4,-0.7 0.2,-0.5 -1.2,0 0,-0.4 -1.4,0.4 -0.2,-1.2 -0.9,-0.4 0.4,-0.3 -0.4,-0.8 -0.1,-0.7 0.1,-0.2 0.4,-0.4 -0.5,-0.9 -0.5,-0.9 -0.2,-0.2 -1,-0.6 -0.4,0.3 0,-0.3 -0.4,-0.3 -0.2,-0.6 -0.7,0 -0.4,-0.7 0.4,-0.2 -0.4,0 -0.4,0 0,-0.9 -0.2,0 -0.4,0 0,-0.4 0,-0.6 0,-0.9 0,-0.2 -0.2,0 0,-0.7 0,-0.7 0.6,-1.3 0.2,-0.2 0.4,0.2 0.7,-1 0.6,-0.2 0.4,-0.9 -1,-0.9 0.4,-0.6 -0.7,-0.3 0,-0.2 -0.4,0.2 -0.6,-0.6 -0.6,-0.4 -0.8,0 0.8,-0.1 0,-1 0,-0.5 -0.4,-0.4 0.4,-0.4 -0.4,-1.6 -0.6,-1.9 -1.6,0 -0.4,-0.4 -0.6,-0.1 0,-0.4 0.2,-0.2 -0.2,-0.8 -0.6,0 -0.9,0 -0.2,0 0,1 -0.8,0.9 0.4,0.4 -0.4,0.2 0.4,0 0,0.5 -0.6,0.8 -0.6,1.1 -0.8,0.9 0.4,0.5 -0.4,0.6 -0.2,-0.2 -0.4,0.6 -0.2,1.3 0,0.2 0.8,-0.2 0,1.6 0.4,0.4 0,0.2 0.6,0 0.4,0.9 0,0.8 -0.6,1.1 0.6,0.4 0.2,0.5 -0.6,0.5 -0.2,0.4 -0.4,1.7 -0.4,1.3 0.4,0.5 -0.4,0.6 0,1 1,0.8 0.6,0.4 1.4,-0.4 0,1 0,0.2 -0.6,0.3 0.6,1.3 0.5,1.1 0,1.9 0.8,0.6 0,0.5 1,1.3 0.4,0.2 -0.4,0.9 0.6,0.5 0,0.8 0.8,0.2 1,-0.2 0,0.6 0,0.5 0.2,0.4 1,-0.4 1.3,-0.3 0.4,0.3 -0.4,0.4 0,0.6 0,0.5 0.8,0 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g252"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path254"
           inkscape:connector-curvature="0"
           d="m 291.3,528 1,-0.9 1.2,-0.6 1.1,0 1,-0.9 0,-1.1 0.6,-0.4 -0.4,-0.3 0,-0.3 0.8,-0.5 -0.4,-1.5 0.4,-0.7 0.2,-0.5 -1.2,0 0,-0.4 -1.4,0.4 -0.2,-1.2 -0.9,-0.4 0.4,-0.3 -0.4,-0.8 -0.1,-0.7 0.1,-0.2 0.4,-0.4 -0.5,-0.9 -0.5,-0.9 -0.2,-0.2 -1,-0.6 -0.4,0.3 0,-0.3 -0.4,-0.3 -0.2,-0.6 -0.7,0 -0.4,-0.7 0.4,-0.2 -0.4,0 -0.4,0 0,-0.9 -0.2,0 -0.4,0 0,-0.4 0,-0.6 0,-0.9 0,-0.2 -0.2,0 0,-0.7 0,-0.7 0.6,-1.3 0.2,-0.2 0.4,0.2 0.7,-1 0.6,-0.2 0.4,-0.9 -1,-0.9 0.4,-0.6 -0.7,-0.3 0,-0.2 -0.4,0.2 -0.6,-0.6 -0.6,-0.4 -0.8,0 0.8,-0.1 0,-1 0,-0.5 -0.4,-0.4 0.4,-0.4 -0.4,-1.6 -0.6,-1.9 -1.6,0 -0.4,-0.4 -0.6,-0.1 0,-0.4 0.2,-0.2 -0.2,-0.8 -0.6,0 -0.9,0 -0.2,0 0,1 -0.8,0.9 0.4,0.4 -0.4,0.2 0.4,0 0,0.5 -0.6,0.8 -0.6,1.1 -0.8,0.9 0.4,0.5 -0.4,0.6 -0.2,-0.2 -0.4,0.6 -0.2,1.3 0,0.2 0.8,-0.2 0,1.6 0.4,0.4 0,0.2 0.6,0 0.4,0.9 0,0.8 -0.6,1.1 0.6,0.4 0.2,0.5 -0.6,0.5 -0.2,0.4 -0.4,1.7 -0.4,1.3 0.4,0.5 -0.4,0.6 0,1 1,0.8 0.6,0.4 1.4,-0.4 0,1 0,0.2 -0.6,0.3 0.6,1.3 0.5,1.1 0,1.9 0.8,0.6 0,0.5 1,1.3 0.4,0.2 -0.4,0.9 0.6,0.5 0,0.8 0.8,0.2 1,-0.2 0,0.6 0,0.5 0.2,0.4 1,-0.4 1.3,-0.3 0.4,0.3 -0.4,0.4 0,0.6 0,0.5 0.8,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path256"
         inkscape:connector-curvature="0"
         d="m 288.97474,522.9 -0.3,1.6 0.6,0.7 0.6,0 -0.2,-0.3 0,-1 0,-0.1 -0.7,-0.9 z" /><g
         id="g258"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path260"
           inkscape:connector-curvature="0"
           d="m 290.6,497.3 -0.3,1.6 0.6,0.7 0.6,0 -0.2,-0.3 0,-1 0,-0.1 -0.7,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path262"
         inkscape:connector-curvature="0"
         d="m 286.47474,521.3 0,0.8 0.2,0.9 0,0.7 0.5,0.2 -0.5,-1.1 -0.2,-1.5 z" /><g
         id="g264"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path266"
           inkscape:connector-curvature="0"
           d="m 288.1,495.7 0,0.8 0.2,0.9 0,0.7 0.5,0.2 -0.5,-1.1 -0.2,-1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path268"
         inkscape:connector-curvature="0"
         d="m 283.97474,518.2 -0.2,0.4 0,0.4 0.7,-0.4 -0.5,-0.4 z" /><g
         id="g270"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path272"
           inkscape:connector-curvature="0"
           d="m 285.6,492.6 -0.2,0.4 0,0.4 0.7,-0.4 -0.5,-0.4 z" /></g><path
         style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="CH"
         inkscape:connector-curvature="0"
         d="m 272.77474,502.5 0.3,0 0.5,0 0.2,0 0.4,0 0.2,0 0,0.1 0.4,0.4 0.4,0 0.2,0 0,-0.4 0.4,0 0.2,0 0.5,0 0.4,-0.1 -0.4,-0.4 0,-0.2 0.4,-0.4 0.1,0 0,-0.3 0.4,0 0.4,-0.3 0.3,0 -0.3,-0.7 -0.4,0 0,-0.5 -0.4,0.4 -0.1,0 -0.4,0.1 -0.5,0 0,-0.5 -0.2,-0.2 -1.4,0.2 -0.6,-0.6 -0.2,0.4 -1.1,-0.4 -0.4,0.6 -0.5,0.5 -0.7,-0.1 0,0.1 0.7,1 0.5,0.7 0,0.6 0.7,0 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g276"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path278"
           inkscape:connector-curvature="0"
           d="m 274.4,476.9 0.3,0 0.5,0 0.2,0 0.4,0 0.2,0 0,0.1 0.4,0.4 0.4,0 0.2,0 0,-0.4 0.4,0 0.2,0 0.5,0 0.4,-0.1 -0.4,-0.4 0,-0.2 0.4,-0.4 0.1,0 0,-0.3 0.4,0 0.4,-0.3 0.3,0 -0.3,-0.7 -0.4,0 0,-0.5 -0.4,0.4 -0.1,0 -0.4,0.1 -0.5,0 0,-0.5 -0.2,-0.2 -1.4,0.2 -0.6,-0.6 -0.2,0.4 -1.1,-0.4 -0.4,0.6 -0.5,0.5 -0.7,-0.1 0,0.1 0.7,1 0.5,0.7 0,0.6 0.7,0 z" /></g><path
         style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="ES"
         inkscape:connector-curvature="0"
         d="m 258.07474,494.3 0.6,-1 2.3,-0.5 1,0 0,0.3 1,-0.3 0.6,-0.6 0.7,0.2 1,-0.2 0.6,0.2 0.4,-0.2 -0.4,-0.3 0,-0.6 -0.6,-0.6 -1,-0.9 -1.3,0 -0.6,-0.3 -0.4,0 0,-1 -0.6,-0.5 -1.4,-1.2 0.4,-0.5 0.6,-1 0,-0.4 -0.6,-0.3 -0.6,-0.6 -0.5,-0.5 0,-0.4 -0.3,-0.2 -0.9,0 -0.7,-0.6 -0.3,-0.9 -0.4,0.2 -0.4,0 -0.2,-0.2 -0.4,0 -0.6,0 -0.4,0 -0.8,0 -0.8,-0.3 -0.6,0 -1.1,-1 -1.2,0.6 -0.4,0.7 0,0.6 -1,0.3 -0.2,0 0,1.2 0.6,0.5 0,0.4 -0.4,0.5 0,0.6 0,0.4 -0.2,1.1 0.6,0 0.4,1.5 0,1 0.8,1.2 -0.2,0.2 -0.6,0.4 -0.8,0 -0.6,0 -0.6,0 -0.4,0.6 -0.6,-0.3 -0.5,0.3 0.5,0.3 -0.5,0.6 -0.1,0 0,0.3 -0.5,1 0.6,0.2 1.1,0 0,0.4 0.6,0.1 0.8,-0.1 1.6,0 1,0 1.7,-0.4 1.8,0 1.6,0 1.8,0 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g282"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path284"
           inkscape:connector-curvature="0"
           d="m 259.7,468.7 0.6,-1 2.3,-0.5 1,0 0,0.3 1,-0.3 0.6,-0.6 0.7,0.2 1,-0.2 0.6,0.2 0.4,-0.2 -0.4,-0.3 0,-0.6 -0.6,-0.6 -1,-0.9 -1.3,0 -0.6,-0.3 -0.4,0 0,-1 -0.6,-0.5 -1.4,-1.2 0.4,-0.5 0.6,-1 0,-0.4 -0.6,-0.3 -0.6,-0.6 -0.5,-0.5 0,-0.4 -0.3,-0.2 -0.9,0 -0.7,-0.6 -0.3,-0.9 -0.4,0.2 -0.4,0 -0.2,-0.2 -0.4,0 -0.6,0 -0.4,0 -0.8,0 -0.8,-0.3 -0.6,0 -1.1,-1 -1.2,0.6 -0.4,0.7 0,0.6 -1,0.3 -0.2,0 0,1.2 0.6,0.5 0,0.4 -0.4,0.5 0,0.6 0,0.4 -0.2,1.1 0.6,0 0.4,1.5 0,1 0.8,1.2 -0.2,0.2 -0.6,0.4 -0.8,0 -0.6,0 -0.6,0 -0.4,0.6 -0.6,-0.3 -0.5,0.3 0.5,0.3 -0.5,0.6 -0.1,0 0,0.3 -0.5,1 0.6,0.2 1.1,0 0,0.4 0.6,0.1 0.8,-0.1 1.6,0 1,0 1.7,-0.4 1.8,0 1.6,0 1.8,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path286"
         inkscape:connector-curvature="0"
         d="m 265.87474,486.3 -0.2,0.1 0,0.5 -0.8,0 1,0.5 0,-0.4 0.9,0 -0.5,-0.1 -0.4,-0.6 z" /><g
         id="g288"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path290"
           inkscape:connector-curvature="0"
           d="m 267.5,460.7 -0.2,0.1 0,0.5 -0.8,0 1,0.5 0,-0.4 0.9,0 -0.5,-0.1 -0.4,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path292"
         inkscape:connector-curvature="0"
         d="m 267.57474,487.4 -0.6,0.4 0.6,0 0,-0.4 z" /><g
         id="g294"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path296"
           inkscape:connector-curvature="0"
           d="m 269.2,461.8 -0.6,0.4 0.6,0 0,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path298"
         inkscape:connector-curvature="0"
         d="m 263.27474,485.3 -0.6,0.2 0.6,0.3 0,-0.5 z" /><g
         id="g300"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path302"
           inkscape:connector-curvature="0"
           d="m 264.9,459.7 -0.6,0.2 0.6,0.3 0,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path304"
         inkscape:connector-curvature="0"
         d="m 262.97474,492.7 0.7,-0.6 0.6,0.3 -1.3,0.3 z" /><g
         id="g306"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path308"
           inkscape:connector-curvature="0"
           d="m 264.6,467.1 0.7,-0.6 0.6,0.3 -1.3,0.3 z" /></g><path
         style="fill:#d0a5d9;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="TR"
         inkscape:connector-curvature="0"
         d="m 304.37474,491.2 1.4,0.6 0.6,-0.3 0.6,0 -0.2,-0.7 1.9,-0.7 1.8,0 0.2,0 0.6,-0.4 1,0 0.4,0.6 1,0.4 1.1,0.5 1.2,0.3 2.2,0 0,0.3 0.4,0 0,-0.3 1,-0.7 0.6,0 1.1,-0.5 0,0.4 0.2,-0.4 1.4,-0.6 0.4,0.4 0.7,-0.4 1.8,0.4 0.8,-0.4 1.6,0.4 1.2,0.7 2.5,0.4 1.2,-0.9 0.4,-0.9 0,-1.1 1.7,-0.6 -0.8,-0.8 0.8,-1.4 0.2,-1 0,-0.2 0.4,-0.9 0.4,-0.9 -0.8,0 -1.7,0.4 -1,0.2 -0.8,-0.6 -0.4,0.4 -0.6,-0.4 -0.8,0 -0.8,0 -1.5,-0.9 -1.2,0 -1.4,0.3 -1,-0.3 -0.9,0 -0.4,0.3 -0.4,0 0,-0.9 0,-0.4 -0.2,-0.2 -0.8,-0.3 -0.2,0.3 -0.4,0.6 0.4,0.6 0,0.3 -1.1,-0.5 -1.6,0.5 -1,-1.3 -1.6,-0.2 -1,0 -0.2,0.6 -1.9,0.6 -0.8,0 0,-1 -0.4,0 -1,-0.2 -1.1,0.2 -0.1,0.8 -1.1,0.2 -0.4,0 -0.2,-0.2 -1,0 0,0.2 1.2,0.3 -1.6,0 -0.2,0.4 0.6,0 0,0.2 -0.6,0 0,0.6 0,0.7 -0.9,0.5 -0.9,0 0.3,0.2 -0.3,0.4 0.3,0 0.5,-0.4 0.6,0 -0.5,0.4 0.5,0.6 -0.5,0 -0.1,0.7 0.1,0.5 -1.2,0 0,1 1.1,0.6 0.6,0.1 1,-0.1 0,0.1 0.2,0.4 0.4,-0.4 1.3,0 0,0.4 1.4,0.6 -0.6,0 -1.7,0.3 -1.6,-0.3 -0.4,-0.2 -1.1,-0.8 -0.6,-0.5 0,0.5 0.6,0.8 -0.6,0 0.6,0.2 0,0.7 0.5,0.2 0,0.4 -0.8,0.5 z"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)" /><g
         id="g312"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path314"
           inkscape:connector-curvature="0"
           d="m 306,465.6 1.4,0.6 0.6,-0.3 0.6,0 -0.2,-0.7 1.9,-0.7 1.8,0 0.2,0 0.6,-0.4 1,0 0.4,0.6 1,0.4 1.1,0.5 1.2,0.3 2.2,0 0,0.3 0.4,0 0,-0.3 1,-0.7 0.6,0 1.1,-0.5 0,0.4 0.2,-0.4 1.4,-0.6 0.4,0.4 0.7,-0.4 1.8,0.4 0.8,-0.4 1.6,0.4 1.2,0.7 2.5,0.4 1.2,-0.9 0.4,-0.9 0,-1.1 1.7,-0.6 -0.8,-0.8 0.8,-1.4 0.2,-1 0,-0.2 0.4,-0.9 0.4,-0.9 -0.8,0 -1.7,0.4 -1,0.2 -0.8,-0.6 -0.4,0.4 -0.6,-0.4 -0.8,0 -0.8,0 -1.5,-0.9 -1.2,0 -1.4,0.3 -1,-0.3 -0.9,0 -0.4,0.3 -0.4,0 0,-0.9 0,-0.4 -0.2,-0.2 -0.8,-0.3 -0.2,0.3 -0.4,0.6 0.4,0.6 0,0.3 -1.1,-0.5 -1.6,0.5 -1,-1.3 -1.6,-0.2 -1,0 -0.2,0.6 -1.9,0.6 -0.8,0 0,-1 -0.4,0 -1,-0.2 -1.1,0.2 -0.1,0.8 -1.1,0.2 -0.4,0 -0.2,-0.2 -1,0 0,0.2 1.2,0.3 -1.6,0 -0.2,0.4 0.6,0 0,0.2 -0.6,0 0,0.6 0,0.7 -0.9,0.5 -0.9,0 0.3,0.2 -0.3,0.4 0.3,0 0.5,-0.4 0.6,0 -0.5,0.4 0.5,0.6 -0.5,0 -0.1,0.7 0.1,0.5 -1.2,0 0,1 1.1,0.6 0.6,0.1 1,-0.1 0,0.1 0.2,0.4 0.4,-0.4 1.3,0 0,0.4 1.4,0.6 -0.6,0 -1.7,0.3 -1.6,-0.3 -0.4,-0.2 -1.1,-0.8 -0.6,-0.5 0,0.5 0.6,0.8 -0.6,0 0.6,0.2 0,0.7 0.5,0.2 0,0.4 -0.8,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path316"
         inkscape:connector-curvature="0"
         d="m 296.67474,504 1.3,-0.5 -0.4,-0.2 -0.7,-0.4 -0.6,-0.5 -0.4,-1.4 -0.2,-0.5 0,-0.4 -0.6,-0.2 0,-0.4 -0.8,0 -1.3,0 -0.3,-0.2 -1.3,0 -0.4,-0.4 -0.2,-0.3 -1.5,0.3 -1,0.6 -0.2,0 0,0.6 -0.4,0.4 0,0.5 0,1.4 -0.4,0.1 0.4,0 0.6,0 0.4,0.4 0,0.4 0.2,-0.4 1.4,-0.4 1.1,0.8 1.3,0.6 0.3,0 1.3,0.5 0.4,0 1,0 0.2,-0.4 0.8,0 z" /><g
         id="g318"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path320"
           inkscape:connector-curvature="0"
           d="m 298.3,478.4 1.3,-0.5 -0.4,-0.2 -0.7,-0.4 -0.6,-0.5 -0.4,-1.4 -0.2,-0.5 0,-0.4 -0.6,-0.2 0,-0.4 -0.8,0 -1.3,0 -0.3,-0.2 -1.3,0 -0.4,-0.4 -0.2,-0.3 -1.5,0.3 -1,0.6 -0.2,0 0,0.6 -0.4,0.4 0,0.5 0,1.4 -0.4,0.1 0.4,0 0.6,0 0.4,0.4 0,0.4 0.2,-0.4 1.4,-0.4 1.1,0.8 1.3,0.6 0.3,0 1.3,0.5 0.4,0 1,0 0.2,-0.4 0.8,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path322"
         inkscape:connector-curvature="0"
         d="m 316.47474,477.5 -1,0.2 -0.4,0.7 0.4,0 0.2,0.2 0.4,0 0.4,0.4 0.6,0 1.6,0.6 -0.6,-1 0,-0.2 -0.4,0 -0.6,-0.7 -0.6,0 0,-0.2 z" /><g
         id="g324"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path326"
           inkscape:connector-curvature="0"
           d="m 318.1,451.9 -1,0.2 -0.4,0.7 0.4,0 0.2,0.2 0.4,0 0.4,0.4 0.6,0 1.6,0.6 -0.6,-1 0,-0.2 -0.4,0 -0.6,-0.7 -0.6,0 0,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path328"
         inkscape:connector-curvature="0"
         d="m 298.87474,490.3 2.7,0.4 0.4,-0.4 0.6,0 0.6,-0.1 1,0.1 0,1 0.3,0 0.8,-0.6 0,-0.4 -0.4,-0.1 0,-0.7 -0.7,-0.3 0,-0.3 -0.4,0.3 -1.2,0.3 -0.4,0.3 -0.6,-0.3 -0.4,0 -0.2,-0.3 -0.9,-0.3 0.9,-0.9 -0.4,0.3 -0.5,0 0.5,-0.3 0,-0.2 -1,0.2 -1.1,0.5 0.4,0 0,0.4 -0.6,-0.4 0,-0.7 1.3,-1.5 -0.7,-0.8 -0.6,0 1,-0.1 1.3,-1 0.4,-1.3 -1,0.8 -0.7,-0.4 0.7,-0.9 -0.4,0 -1.1,0.5 1.1,-1.5 0,-0.2 0,-0.3 -1.1,0.5 0,-0.2 0,-0.3 -0.2,0 -0.4,0.9 -0.6,0 0.2,-0.6 -0.2,0.2 -0.4,0.8 0,0.5 -1.1,0.6 0.8,0.5 0.9,0.4 1.4,-0.5 0.4,0.1 -1,0.6 -0.8,0 -1.7,-0.2 -0.6,1.1 0.6,0 0,0.4 -0.6,0 -1,0.9 0.8,0.6 0.6,1.5 0.6,0.6 1.1,0 0.2,0.7 0.8,-0.4 0.6,0.5 z" /><g
         id="g330"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path332"
           inkscape:connector-curvature="0"
           d="m 300.5,464.7 2.7,0.4 0.4,-0.4 0.6,0 0.6,-0.1 1,0.1 0,1 0.3,0 0.8,-0.6 0,-0.4 -0.4,-0.1 0,-0.7 -0.7,-0.3 0,-0.3 -0.4,0.3 -1.2,0.3 -0.4,0.3 -0.6,-0.3 -0.4,0 -0.2,-0.3 -0.9,-0.3 0.9,-0.9 -0.4,0.3 -0.5,0 0.5,-0.3 0,-0.2 -1,0.2 -1.1,0.5 0.4,0 0,0.4 -0.6,-0.4 0,-0.7 1.3,-1.5 -0.7,-0.8 -0.6,0 1,-0.1 1.3,-1 0.4,-1.3 -1,0.8 -0.7,-0.4 0.7,-0.9 -0.4,0 -1.1,0.5 1.1,-1.5 0,-0.2 0,-0.3 -1.1,0.5 0,-0.2 0,-0.3 -0.2,0 -0.4,0.9 -0.6,0 0.2,-0.6 -0.2,0.2 -0.4,0.8 0,0.5 -1.1,0.6 0.8,0.5 0.9,0.4 1.4,-0.5 0.4,0.1 -1,0.6 -0.8,0 -1.7,-0.2 -0.6,1.1 0.6,0 0,0.4 -0.6,0 -1,0.9 0.8,0.6 0.6,1.5 0.6,0.6 1.1,0 0.2,0.7 0.8,-0.4 0.6,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path334"
         inkscape:connector-curvature="0"
         d="m 302.57474,478.4 -0.4,0.2 -1.2,0 -0.8,0 0,0.6 0.8,0 0.2,0.4 0.4,-0.6 1,0 0.2,0 1,0 0.4,-0.4 0.6,0 0,-0.2 -2.2,0 z" /><g
         id="g336"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path338"
           inkscape:connector-curvature="0"
           d="m 304.2,452.8 -0.4,0.2 -1.2,0 -0.8,0 0,0.6 0.8,0 0.2,0.4 0.4,-0.6 1,0 0.2,0 1,0 0.4,-0.4 0.6,0 0,-0.2 -2.2,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path340"
         inkscape:connector-curvature="0"
         d="m 300.47474,484.4 -0.5,0.5 -0.7,0.6 0.3,0.3 0.5,-0.5 0.8,0 0.3,-0.9 0.4,-0.3 0,-0.3 -0.4,0 -0.7,0.6 z" /><g
         id="g342"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path344"
           inkscape:connector-curvature="0"
           d="m 302.1,458.8 -0.5,0.5 -0.7,0.6 0.3,0.3 0.5,-0.5 0.8,0 0.3,-0.9 0.4,-0.3 0,-0.3 -0.4,0 -0.7,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path346"
         inkscape:connector-curvature="0"
         d="m 304.77474,485.8 -0.6,0 0,0.4 -0.4,-0.4 -0.2,0.4 0.8,0.2 0.4,-0.2 0,-0.4 z" /><g
         id="g348"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path350"
           inkscape:connector-curvature="0"
           d="m 306.4,460.2 -0.6,0 0,0.4 -0.4,-0.4 -0.2,0.4 0.8,0.2 0.4,-0.2 0,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path352"
         inkscape:connector-curvature="0"
         d="m 307.47474,479.9 0,0.6 0.7,0.1 -0.3,-0.5 -0.4,-0.2 z" /><g
         id="g354"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path356"
           inkscape:connector-curvature="0"
           d="m 309.1,454.3 0,0.6 0.7,0.1 -0.3,-0.5 -0.4,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path358"
         inkscape:connector-curvature="0"
         d="m 304.37474,484.3 -0.6,0 0.4,0 -0.4,0.7 0.4,-0.4 0.2,-0.3 z" /><g
         id="g360"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path362"
           inkscape:connector-curvature="0"
           d="m 306,458.7 -0.6,0 0.4,0 -0.4,0.7 0.4,-0.4 0.2,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path364"
         inkscape:connector-curvature="0"
         d="m 273.17474,494.6 -0.4,0 0,-0.5 0,0.5 0,0.2 0.4,-0.2 z" /><g
         id="g366"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path368"
           inkscape:connector-curvature="0"
           d="m 274.8,469 -0.4,0 0,-0.5 0,0.5 0,0.2 0.4,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path370"
         inkscape:connector-curvature="0"
         d="m 277.87474,500.9 -0.4,0.2 -0.4,0 0.8,0 0,-0.2 z" /><g
         id="g372"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path374"
           inkscape:connector-curvature="0"
           d="m 279.5,475.3 -0.4,0.2 -0.4,0 0.8,0 0,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path376"
         inkscape:connector-curvature="0"
         d="m 336.37474,486.2 -1.2,1.5 -1.7,0.5 0,1.2 -0.4,0.9 5.8,-0.2 0.2,-1.7 1.4,-0.2 -0.8,-0.5 0.4,-1.5 -1.2,-0.4 -2.5,0.4 z" /><g
         id="g378"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path380"
           inkscape:connector-curvature="0"
           d="m 338,460.6 -1.2,1.5 -1.7,0.5 0,1.2 -0.4,0.9 5.8,-0.2 0.2,-1.7 1.4,-0.2 -0.8,-0.5 0.4,-1.5 -1.2,-0.4 -2.5,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path382"
         inkscape:connector-curvature="0"
         d="m 341.27474,491.3 1.4,-1.5 1.7,-0.6 -1.7,-0.7 0,-1.1 0,-0.6 -0.4,0.2 -0.6,-0.2 0.6,-1.5 -2.3,1 -0.4,1.4 0.8,0.6 -1.4,0.2 -0.2,1.7 0,0.7 2.5,0.4 z" /><g
         id="g384"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path386"
           inkscape:connector-curvature="0"
           d="m 342.9,465.7 1.4,-1.5 1.7,-0.6 -1.7,-0.7 0,-1.1 0,-0.6 -0.4,0.2 -0.6,-0.2 0.6,-1.5 -2.3,1 -0.4,1.4 0.8,0.6 -1.4,0.2 -0.2,1.7 0,0.7 2.5,0.4 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="BY"
         inkscape:connector-curvature="0"
         d="m 298.87474,510.7 -0.5,1.2 -1,0.9 1.5,0.7 -0.5,1.2 -0.6,1.1 2.3,0.9 1.4,1.3 0.4,1.9 2.5,0.8 0.8,0.3 4.3,-0.5 1.3,-3.4 1.4,-1.3 0.6,-2.1 -1.4,0.6 -0.6,-0.2 1,-1.8 -0.4,-1.9 -1.2,0 -0.7,-0.6 0,-0.2 -3.1,0.6 0,0.5 -6.3,-0.3 -0.6,0.3 -0.6,0 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g390"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path392"
           inkscape:connector-curvature="0"
           d="m 300.5,485.1 -0.5,1.2 -1,0.9 1.5,0.7 -0.5,1.2 -0.6,1.1 2.3,0.9 1.4,1.3 0.4,1.9 2.5,0.8 0.8,0.3 4.3,-0.5 1.3,-3.4 1.4,-1.3 0.6,-2.1 -1.4,0.6 -0.6,-0.2 1,-1.8 -0.4,-1.9 -1.2,0 -0.7,-0.6 0,-0.2 -3.1,0.6 0,0.5 -6.3,-0.3 -0.6,0.3 -0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path394"
         inkscape:connector-curvature="0"
         d="m 297.87474,525.3 0,1.1 -0.4,-0.4 -0.8,1.3 -0.4,1.1 3.7,1.3 2,-0.3 -0.4,-0.2 0.6,-1.3 0,-1 1,-1.5 -1.6,-0.5 -2.2,0.4 -1.5,0 z" /><g
         id="g396"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path398"
           inkscape:connector-curvature="0"
           d="m 299.5,499.7 0,1.1 -0.4,-0.4 -0.8,1.3 -0.4,1.1 3.7,1.3 2,-0.3 -0.4,-0.2 0.6,-1.3 0,-1 1,-1.5 -1.6,-0.5 -2.2,0.4 -1.5,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path400"
         inkscape:connector-curvature="0"
         d="m 333.07474,490.3 -1.2,0.9 -2.5,-0.4 0,1.4 -1,1.4 -1.8,1 5.9,0 4.9,-2.4 1.4,-1.4 0,-0.7 -5.7,0.2 z" /><g
         id="g402"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path404"
           inkscape:connector-curvature="0"
           d="m 334.7,464.7 -1.2,0.9 -2.5,-0.4 0,1.4 -1,1.4 -1.8,1 5.9,0 4.9,-2.4 1.4,-1.4 0,-0.7 -5.7,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path406"
         inkscape:connector-curvature="0"
         d="m 293.07474,521.5 0.6,2.5 1.7,1.3 1.4,-1.9 0.6,0 0.6,0.4 0,1.5 1.4,0 2.2,-0.4 1.7,0.6 1.2,-0.2 1.4,-1.5 -0.6,-2.8 -0.8,-0.4 -2.5,-0.8 -0.7,0.6 -0.7,0.2 -1,0.7 -2.2,0.6 -1.7,0 -2.6,-0.4 z" /><g
         id="g408"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path410"
           inkscape:connector-curvature="0"
           d="m 294.7,495.9 0.6,2.5 1.7,1.3 1.4,-1.9 0.6,0 0.6,0.4 0,1.5 1.4,0 2.2,-0.4 1.7,0.6 1.2,-0.2 1.4,-1.5 -0.6,-2.8 -0.8,-0.4 -2.5,-0.8 -0.7,0.6 -0.7,0.2 -1,0.7 -2.2,0.6 -1.7,0 -2.6,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path412"
         inkscape:connector-curvature="0"
         d="m 297.87474,515.7 -1,1.7 -5.3,0 1,0.6 -1,-0.4 0.4,0.6 2,0 -1,3.4 2.7,0.3 1.6,0 2.3,-0.5 1,-0.8 0.6,-0.2 0.8,-0.6 -0.4,-1.8 -1.5,-1.3 -2.2,-1 z" /><g
         id="g414"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path416"
           inkscape:connector-curvature="0"
           d="m 299.5,490.1 -1,1.7 -5.3,0 1,0.6 -1,-0.4 0.4,0.6 2,0 -1,3.4 2.7,0.3 1.6,0 2.3,-0.5 1,-0.8 0.6,-0.2 0.8,-0.6 -0.4,-1.8 -1.5,-1.3 -2.2,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path418"
         inkscape:connector-curvature="0"
         d="m 304.37474,503.9 2,-0.5 1.1,0 1,-1.6 0.2,-0.7 1.5,-1 -2.1,-0.2 -0.6,-0.5 -0.7,-1 -0.4,3 -0.1,0 -1.9,2.5 z" /><g
         id="g420"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path422"
           inkscape:connector-curvature="0"
           d="m 306,478.3 2,-0.5 1.1,0 1,-1.6 0.2,-0.7 1.5,-1 -2.1,-0.2 -0.6,-0.5 -0.7,-1 -0.4,3 -0.1,0 -1.9,2.5 z" /></g><path
         style="fill:#ffdd55;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="RU"
         inkscape:connector-curvature="0"
         d="m 302.17474,555.6 0.6,0 0.8,0 -0.4,0.2 3.1,0 -1,-0.2 1.6,-0.4 0,-0.9 0.2,0.7 2.7,0 6.9,-2.4 2.6,-2.2 -1.6,-2.1 -2.2,-0.9 -9.2,2.4 4.5,-3.3 -0.3,-1.5 0.8,-2.4 4.2,-1.5 1,0.4 -0.8,1.1 -0.9,0.4 -1.4,1 0.6,1 2,-1 0,-0.5 3.7,0 -1.6,2.8 1,1.1 1.7,1.5 1.7,0 1.5,-0.9 0,2.2 -1.5,0.9 -0.1,2.6 -1.5,0.6 4.1,-0.2 1.2,-1.3 -2,-0.5 0,-0.6 2.4,-1.5 1.7,0.6 0,1.5 1.2,0.3 -0.6,0.6 4.3,2 0.6,-0.9 0.4,0.7 -0.7,0.2 1.9,1.3 0.8,0 -1,-0.4 0.6,-1.1 -0.6,-0.9 0.2,-0.4 1.4,0.9 0.6,-0.3 0,1.3 1.1,0.2 1.6,0 2.6,1.3 0.7,-1.5 0.5,0 0,0.9 1.1,0.6 -1.6,1.9 0.4,0.5 6.1,-0.5 4.1,-1 2.2,-1.5 0.4,2.1 -2.4,0.5 0,1 -1.9,0 -0.4,0.9 0.4,0 0.2,0.2 -0.8,2.2 -0.8,-0.6 0,1.1 -0.6,-0.3 0.4,0.9 1,0.6 0.2,1.6 -0.6,3 4.9,0 0.6,-1.5 0,-2.2 2,-1.7 2.7,-5.3 2,-0.8 -0.8,-0.6 0.8,-1.5 -1.4,-2 0.4,-0.7 -1.4,-0.6 -1.2,0.9 -1.7,-0.5 2.7,-0.9 3.2,0.3 -0.4,0.8 1.6,1.2 0.4,1.9 -1.6,2.5 2.7,1.1 1.6,-1 0.6,-2 3.1,-0.4 -2.4,0.4 -0.3,1.1 0.3,0.4 -1.3,1.5 -3.3,0.5 -2.4,-0.9 -1.2,2.2 -0.4,2.6 -2.7,1.5 0,1.3 1,0.6 -0.6,2.7 2.1,-1.8 0,-1.3 0.6,-1.2 1.2,-0.3 4.7,-0.6 -5.5,2.5 0.2,0.5 2.7,0 -1.3,0.4 0.6,0.9 1.2,0.2 3.7,-1.1 2.7,0 0,-0.9 -0.6,-0.4 0.2,-0.8 1,-0.9 0,0.5 1,-2 1.4,-0.4 1.9,-0.5 0.8,0.5 -2.7,0.4 -1,0.6 0.6,0.9 -2,1.5 -0.6,1.6 -1.2,0.4 -1.1,0.9 -2.4,0.4 -1.7,1.5 -0.5,1.5 7.5,2.1 0.3,-0.4 -0.3,-0.6 1,0.4 -1.2,1.5 1.2,-0.4 -0.2,0.4 -3,0.6 1.2,0.5 -1.6,0.3 0,0.6 0.8,0 0.2,1 0.6,0 -1.6,0.3 1.6,0 0.4,2.1 0.4,-0.6 1,0.9 2.2,0.2 0,0.6 2.3,0.9 -1.6,0.4 2,0.5 2,0.4 0,-0.9 1.2,0.5 0,-0.5 0.6,0.9 0.5,0 0.2,1.7 0.4,-0.8 1,0.2 -2.7,1.9 1.7,-0.4 1.4,0.8 2.3,-0.4 0,1.5 -1.5,-1 -0.8,0.4 -1.4,1.1 -0.4,3.4 0.8,0.5 1.6,-0.2 0.6,-0.9 -1,-0.9 1.4,-0.4 0.3,0.4 0.6,-0.4 0.3,-0.9 0.7,0.7 1,-0.7 0.4,-1.7 1,0.6 -0.4,0.9 2.7,0.9 1,-0.7 0,0.7 2.2,0.6 3.7,-0.9 0.6,-0.6 0,0.6 2,-1 0.2,-1.5 -1.8,0.6 1,-1.1 0.6,0.4 1.2,-0.8 -0.1,-2 0.1,-0.4 -1,-0.2 -0.2,-1.5 -1.6,-0.3 1,-0.4 -2.6,-2.7 -1,0 0.2,-0.3 -0.2,-2.6 1.8,0.5 -0.8,0.4 1.6,1.2 0.2,-0.4 0,0.7 1.6,0.2 -0.2,0.7 1.6,-0.4 1.7,1.6 -1.7,0 -1.4,-0.6 -0.2,0.6 0.2,0.9 3.1,0.2 0,-0.6 1.6,0 0,0.6 1.2,-0.6 1.5,-1.7 0.6,0.8 -1.3,0.7 1.7,0.6 4.3,0.9 0.2,-0.3 -0.6,-0.4 0.4,-0.6 2.6,0 -0.4,0.4 1,0.2 2.1,0.4 1.2,1.3 -2.2,0.5 0,0.9 0.6,-0.3 0,0.5 1,0.6 1.6,-0.6 6,0.4 0.3,-0.6 -1,-0.7 0.2,-0.6 -1.2,-1.4 1.2,-1.2 0.8,0 -1,1.2 4.3,-1.2 0,0.8 1.2,-0.6 -0.6,-0.5 5.3,-1.9 0.4,0.3 -0.4,1.9 -1.5,2.1 3.1,-1.3 1.2,0.4 1.1,1.3 4,-0.8 -0.7,1.3 0.4,0.6 1.3,-0.6 0.7,0.9 -2,0.6 0.3,0.2 -0.7,0.7 -0.7,-0.4 -0.6,1 1.7,0.5 -1.7,1.3 0,0.2 9.2,0.6 0.8,-0.6 -2.1,0.4 -0.4,-1.3 1.5,0.7 0.6,-0.7 -1,-0.2 1.4,-0.9 0.2,3 3.7,0.3 1.2,-0.7 0,-0.2 -1.7,0.2 0.5,-0.6 3.6,-0.5 0.3,-1 0.4,1 2.8,-0.6 -0.6,-0.4 2,-0.1 3.9,2.6 3.2,0.3 3.5,-0.7 2.3,-2.2 2.6,-0.8 0,0.9 1,-0.9 0,1.3 2.3,1.1 1.6,-0.2 2.7,0.6 0.4,1.1 2.6,-1.1 4.5,-0.6 -1.1,1.5 -1.6,1.1 -1,-0.1 0,0.7 -1.2,1.3 4.7,0 2.8,1.5 1,-0.4 -0.4,0.4 8.6,-1 0.4,0.6 3.1,-1.1 5.1,-0.9 1.6,-1 -0.2,0.6 2.6,-0.7 2.3,-2.3 2,-0.9 0,0.7 0.7,-0.9 -0.7,1.5 -2.7,1.5 1.1,0 1.2,-0.4 0.6,0.4 -0.6,0.4 1.7,0.1 5.9,-1.8 -0.7,-1.5 1.1,-1.1 -2.3,0.9 1.9,-1.9 -3.3,0.4 1.6,-0.7 0.4,-0.6 -0.4,-0.5 1.5,-0.6 -0.6,-1.5 2.4,-1.3 -0.8,0.4 0.2,-1.3 -1.7,1.3 0.7,-1.3 -3.3,1.9 -0.2,-0.6 -1.7,0.6 -1.8,1.8 -1.6,0 -0.2,0.6 0,-1.1 -1.7,-0.4 -2,1.5 -1,2 0.4,-1.1 -1,0.5 0.2,-0.5 -0.2,-0.4 2.6,-2.6 0.2,-3.4 -0.2,-1.2 -0.4,0.4 -1,-0.4 -2.6,1.2 -0.6,-0.5 1.4,0.2 0.2,-0.5 -1.2,0 0.5,-1.9 1.1,1.5 2,-1.5 0.6,0.7 1,-0.9 0,-0.6 0.6,0 1.1,-1.2 -0.4,0 0,-0.6 0.6,0 3.4,-2.4 0.3,-2.1 -3.3,1.1 0.4,-0.9 -2.4,-2 -0.3,-1.9 -0.8,-0.6 -0.6,0.4 0,-1.6 -0.2,0 0.2,-0.2 -0.2,-0.4 0.2,-0.9 -0.6,0 0.4,-0.2 -1,-2.8 0.6,-1.6 -1,0.1 -2.2,1.9 -1.5,-0.4 -1.2,-1.1 -0.4,-2.8 -1,2.8 -0.8,-3 -1,0.6 -1,-0.4 -0.5,-0.6 1.5,-2.4 -0.9,0 0.5,-2.9 1,-1.5 0.6,-0.4 -0.2,0.6 1.6,-0.2 0.6,-3 0.2,-0.8 0.8,0.4 1.1,-1.9 -0.5,-0.5 -0.6,0.4 0,1.5 -0.8,-0.6 0.8,-4.3 1.3,-0.9 -0.2,-1.1 -1.1,0.2 -1,-1.5 0.2,-1.7 1.4,-1.6 -1,0 -1,-0.8 -0.6,0.2 1,-3.3 0,-1.6 -1,-2 -7.5,12.6 -2.1,4.5 0.7,2.4 -0.9,1.9 0.9,-0.4 0,0.9 0.8,0.4 1.1,5.9 0,3.4 2.1,2.1 -1,0.3 0.4,0.8 -2,3.9 1.2,0.7 0.4,0.2 -2,0.7 -1.3,-0.9 1.3,-3 0.6,-0.3 -0.2,-0.6 -0.6,0.6 -0.4,-0.4 -1.1,-3.5 -0.6,-0.6 -0.4,1.1 -0.6,-0.1 -1.2,3.5 -0.9,-1.3 -1.2,0.7 -1,-1.3 -1,0.4 -1.1,-7.6 0.5,-1.6 1.2,0.5 0.8,-1 -2.6,-0.2 -0.4,-1.3 -0.6,0.4 -1.7,-0.9 -0.6,0.5 1.7,1 -2.1,0.5 -0.6,0 0,-0.5 -2.5,0.5 -0.6,-0.5 0,-1 -0.2,-0.3 -0.8,0.3 -0.6,-0.5 -2,0.2 -0.3,-0.8 -0.6,0.6 -4.2,-1 -3.5,-9.3 0,-0.5 0.8,-0.4 -1,-0.3 -2,-5.4 1.4,-0.2 1.6,0.2 0.6,-2.4 0.6,0.7 -0.3,0.5 0.7,0.6 0.3,-2.1 1.4,1.2 0.2,-1.2 0.4,1 -0.6,1.5 1.8,0.2 3.7,-2.7 0,-0.5 -0.6,0 2,-2.4 -0.4,-2.2 2.2,-8.4 -1.2,-3.6 0,-2.9 -2.3,-6.2 -1.9,-2 -1.5,-0.4 -1.2,0.4 -0.4,0.6 -0.8,0 -1.5,-2.4 0,0.3 0,0.6 0.4,1.9 -1.4,3.5 1,1.3 2.1,-0.7 0,5.2 0.2,0.5 0,1.5 0,1.5 -1.2,-0.6 -1.7,-0.5 -0.6,-1 -1.6,-0.3 -0.8,0 -1.7,2.9 -2.6,1.3 -1,0 -1.6,0 -0.6,0.6 -0.7,0.9 -1,1.9 -1.2,1.1 -3.1,3.9 -0.6,0.4 -1,0.2 -2.1,0.3 -0.9,0.4 -0.8,-0.4 -3.1,-1.1 -0.7,-1.5 0.7,0 0.6,-0.7 0.4,-0.6 -0.6,-2.9 -0.4,-1.9 0.6,-0.9 -1.7,-1.5 -1.2,0.4 -0.8,0 -1.8,0 -3.1,0.8 -1,-2.2 -3.5,-1.1 -2.4,0 -1.1,0 -1.1,0.6 -0.5,0.5 -2.8,1 -1.4,0.4 -2.9,-1 -1.6,0 -2.5,2.4 -5.1,1.1 0,-0.5 -0.8,-0.9 -0.2,-0.3 0.6,-1.4 0.4,-0.9 0.7,-1 -3.1,-0.3 -2.2,0 -1.5,0.3 -0.1,1 -2.1,-0.4 -0.6,0.5 -2,-0.1 -1.1,-0.6 -0.2,-0.4 -1,-0.9 0,-0.5 -2.2,-1 -0.5,-0.6 -1,-0.3 -2.8,0.5 -3.1,1.4 -4.7,0.5 -3.5,3.4 -5.7,3.3 -5.2,-0.8 -3.5,2.8 -4.7,-1.1 -4.9,0.2 -7.3,-1.1 -1.2,-6.1 1.2,-2.7 -10.8,1.2 -4.1,0.9 -3.4,-0.5 -2.1,-5.5 1.6,0 2.1,-5.2 1.6,0 -1.4,-1.1 -1.6,-0.3 -1.1,-2.5 1.1,0 -0.7,-0.1 1.1,-0.8 0.3,-0.6 0,-1.4 0.9,-1.2 1.6,-1.8 -2.5,-0.4 -1.4,1.3 -4.8,2.5 -6,0 -1.6,1 -1.4,1 -2.2,1.3 0,0.6 2.2,2.5 1,0 0.4,1.4 -0.8,0.5 -1.7,-0.9 -0.5,0.9 3.8,1.1 -1.2,3.2 -2.6,0 -2.9,2 -4,0.4 -3.8,0.7 0.4,1.9 -0.9,1.9 0.5,0.1 1.5,-0.5 -0.6,2 -1.4,1.3 -1.3,3.4 -4.2,0.5 0.6,2.8 -1.5,1.5 -1.2,0.2 -1,1.4 0,1 -0.6,1.3 0.4,0.2 0.8,0 0,0.6 1.6,0.7 1.9,-0.4 -0.8,0.9 -1.3,0 -0.6,1.5 -1,-0.5 3.3,3.5 2,3 -1,0.7 -0.6,0.2 -1.5,0.9 0.5,1.5 -0.9,0.4 0,0.6 0,0.5 -0.6,0 0,0.5 0,0.4 0.4,1.9 -2,3.2 0.6,1.6 -0.6,0.6 -1,0.6 -0.2,0.9 -0.4,0.5 1.6,1.9 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g426"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path428"
           inkscape:connector-curvature="0"
           d="m 303.8,530 0.6,0 0.8,0 -0.4,0.2 3.1,0 -1,-0.2 1.6,-0.4 0,-0.9 0.2,0.7 2.7,0 6.9,-2.4 2.6,-2.2 -1.6,-2.1 -2.2,-0.9 -9.2,2.4 4.5,-3.3 -0.3,-1.5 0.8,-2.4 4.2,-1.5 1,0.4 -0.8,1.1 -0.9,0.4 -1.4,1 0.6,1 2,-1 0,-0.5 3.7,0 -1.6,2.8 1,1.1 1.7,1.5 1.7,0 1.5,-0.9 0,2.2 -1.5,0.9 -0.1,2.6 -1.5,0.6 4.1,-0.2 1.2,-1.3 -2,-0.5 0,-0.6 2.4,-1.5 1.7,0.6 0,1.5 1.2,0.3 -0.6,0.6 4.3,2 0.6,-0.9 0.4,0.7 -0.7,0.2 1.9,1.3 0.8,0 -1,-0.4 0.6,-1.1 -0.6,-0.9 0.2,-0.4 1.4,0.9 0.6,-0.3 0,1.3 1.1,0.2 1.6,0 2.6,1.3 0.7,-1.5 0.5,0 0,0.9 1.1,0.6 -1.6,1.9 0.4,0.5 6.1,-0.5 4.1,-1 2.2,-1.5 0.4,2.1 -2.4,0.5 0,1 -1.9,0 -0.4,0.9 0.4,0 0.2,0.2 -0.8,2.2 -0.8,-0.6 0,1.1 -0.6,-0.3 0.4,0.9 1,0.6 0.2,1.6 -0.6,3 4.9,0 0.6,-1.5 0,-2.2 2,-1.7 2.7,-5.3 2,-0.8 -0.8,-0.6 0.8,-1.5 -1.4,-2 0.4,-0.7 -1.4,-0.6 -1.2,0.9 -1.7,-0.5 2.7,-0.9 3.2,0.3 -0.4,0.8 1.6,1.2 0.4,1.9 -1.6,2.5 2.7,1.1 1.6,-1 0.6,-2 3.1,-0.4 -2.4,0.4 -0.3,1.1 0.3,0.4 -1.3,1.5 -3.3,0.5 -2.4,-0.9 -1.2,2.2 -0.4,2.6 -2.7,1.5 0,1.3 1,0.6 -0.6,2.7 2.1,-1.8 0,-1.3 0.6,-1.2 1.2,-0.3 4.7,-0.6 -5.5,2.5 0.2,0.5 2.7,0 -1.3,0.4 0.6,0.9 1.2,0.2 3.7,-1.1 2.7,0 0,-0.9 -0.6,-0.4 0.2,-0.8 1,-0.9 0,0.5 1,-2 1.4,-0.4 1.9,-0.5 0.8,0.5 -2.7,0.4 -1,0.6 0.6,0.9 -2,1.5 -0.6,1.6 -1.2,0.4 -1.1,0.9 -2.4,0.4 -1.7,1.5 -0.5,1.5 7.5,2.1 0.3,-0.4 -0.3,-0.6 1,0.4 -1.2,1.5 1.2,-0.4 -0.2,0.4 -3,0.6 1.2,0.5 -1.6,0.3 0,0.6 0.8,0 0.2,1 0.6,0 -1.6,0.3 1.6,0 0.4,2.1 0.4,-0.6 1,0.9 2.2,0.2 0,0.6 2.3,0.9 -1.6,0.4 2,0.5 2,0.4 0,-0.9 1.2,0.5 0,-0.5 0.6,0.9 0.5,0 0.2,1.7 0.4,-0.8 1,0.2 -2.7,1.9 1.7,-0.4 1.4,0.8 2.3,-0.4 0,1.5 -1.5,-1 -0.8,0.4 -1.4,1.1 -0.4,3.4 0.8,0.5 1.6,-0.2 0.6,-0.9 -1,-0.9 1.4,-0.4 0.3,0.4 0.6,-0.4 0.3,-0.9 0.7,0.7 1,-0.7 0.4,-1.7 1,0.6 -0.4,0.9 2.7,0.9 1,-0.7 0,0.7 2.2,0.6 3.7,-0.9 0.6,-0.6 0,0.6 2,-1 0.2,-1.5 -1.8,0.6 1,-1.1 0.6,0.4 1.2,-0.8 -0.1,-2 0.1,-0.4 -1,-0.2 -0.2,-1.5 -1.6,-0.3 1,-0.4 -2.6,-2.7 -1,0 0.2,-0.3 -0.2,-2.6 1.8,0.5 -0.8,0.4 1.6,1.2 0.2,-0.4 0,0.7 1.6,0.2 -0.2,0.7 1.6,-0.4 1.7,1.6 -1.7,0 -1.4,-0.6 -0.2,0.6 0.2,0.9 3.1,0.2 0,-0.6 1.6,0 0,0.6 1.2,-0.6 1.5,-1.7 0.6,0.8 -1.3,0.7 1.7,0.6 4.3,0.9 0.2,-0.3 -0.6,-0.4 0.4,-0.6 2.6,0 -0.4,0.4 1,0.2 2.1,0.4 1.2,1.3 -2.2,0.5 0,0.9 0.6,-0.3 0,0.5 1,0.6 1.6,-0.6 6,0.4 0.3,-0.6 -1,-0.7 0.2,-0.6 -1.2,-1.4 1.2,-1.2 0.8,0 -1,1.2 4.3,-1.2 0,0.8 1.2,-0.6 -0.6,-0.5 5.3,-1.9 0.4,0.3 -0.4,1.9 -1.5,2.1 3.1,-1.3 1.2,0.4 1.1,1.3 4,-0.8 -0.7,1.3 0.4,0.6 1.3,-0.6 0.7,0.9 -2,0.6 0.3,0.2 -0.7,0.7 -0.7,-0.4 -0.6,1 1.7,0.5 -1.7,1.3 0,0.2 9.2,0.6 0.8,-0.6 -2.1,0.4 -0.4,-1.3 1.5,0.7 0.6,-0.7 -1,-0.2 1.4,-0.9 0.2,3 3.7,0.3 1.2,-0.7 0,-0.2 -1.7,0.2 0.5,-0.6 3.6,-0.5 0.3,-1 0.4,1 2.8,-0.6 -0.6,-0.4 2,-0.1 3.9,2.6 3.2,0.3 3.5,-0.7 2.3,-2.2 2.6,-0.8 0,0.9 1,-0.9 0,1.3 2.3,1.1 1.6,-0.2 2.7,0.6 0.4,1.1 2.6,-1.1 4.5,-0.6 -1.1,1.5 -1.6,1.1 -1,-0.1 0,0.7 -1.2,1.3 4.7,0 2.8,1.5 1,-0.4 -0.4,0.4 8.6,-1 0.4,0.6 3.1,-1.1 5.1,-0.9 1.6,-1 -0.2,0.6 2.6,-0.7 2.3,-2.3 2,-0.9 0,0.7 0.7,-0.9 -0.7,1.5 -2.7,1.5 1.1,0 1.2,-0.4 0.6,0.4 -0.6,0.4 1.7,0.1 5.9,-1.8 -0.7,-1.5 1.1,-1.1 -2.3,0.9 1.9,-1.9 -3.3,0.4 1.6,-0.7 0.4,-0.6 -0.4,-0.5 1.5,-0.6 -0.6,-1.5 2.4,-1.3 -0.8,0.4 0.2,-1.3 -1.7,1.3 0.7,-1.3 -3.3,1.9 -0.2,-0.6 -1.7,0.6 -1.8,1.8 -1.6,0 -0.2,0.6 0,-1.1 -1.7,-0.4 -2,1.5 -1,2 0.4,-1.1 -1,0.5 0.2,-0.5 -0.2,-0.4 2.6,-2.6 0.2,-3.4 -0.2,-1.2 -0.4,0.4 -1,-0.4 -2.6,1.2 -0.6,-0.5 1.4,0.2 0.2,-0.5 -1.2,0 0.5,-1.9 1.1,1.5 2,-1.5 0.6,0.7 1,-0.9 0,-0.6 0.6,0 1.1,-1.2 -0.4,0 0,-0.6 0.6,0 3.4,-2.4 0.3,-2.1 -3.3,1.1 0.4,-0.9 -2.4,-2 -0.3,-1.9 -0.8,-0.6 -0.6,0.4 0,-1.6 -0.2,0 0.2,-0.2 -0.2,-0.4 0.2,-0.9 -0.6,0 0.4,-0.2 -1,-2.8 0.6,-1.6 -1,0.1 -2.2,1.9 -1.5,-0.4 -1.2,-1.1 -0.4,-2.8 -1,2.8 -0.8,-3 -1,0.6 -1,-0.4 -0.5,-0.6 1.5,-2.4 -0.9,0 0.5,-2.9 1,-1.5 0.6,-0.4 -0.2,0.6 1.6,-0.2 0.6,-3 0.2,-0.8 0.8,0.4 1.1,-1.9 -0.5,-0.5 -0.6,0.4 0,1.5 -0.8,-0.6 0.8,-4.3 1.3,-0.9 -0.2,-1.1 -1.1,0.2 -1,-1.5 0.2,-1.7 1.4,-1.6 -1,0 -1,-0.8 -0.6,0.2 1,-3.3 0,-1.6 -1,-2 -7.5,12.6 -2.1,4.5 0.7,2.4 -0.9,1.9 0.9,-0.4 0,0.9 0.8,0.4 1.1,5.9 0,3.4 2.1,2.1 -1,0.3 0.4,0.8 -2,3.9 1.2,0.7 0.4,0.2 -2,0.7 -1.3,-0.9 1.3,-3 0.6,-0.3 -0.2,-0.6 -0.6,0.6 -0.4,-0.4 -1.1,-3.5 -0.6,-0.6 -0.4,1.1 -0.6,-0.1 -1.2,3.5 -0.9,-1.3 -1.2,0.7 -1,-1.3 -1,0.4 -1.1,-7.6 0.5,-1.6 1.2,0.5 0.8,-1 -2.6,-0.2 -0.4,-1.3 -0.6,0.4 -1.7,-0.9 -0.6,0.5 1.7,1 -2.1,0.5 -0.6,0 0,-0.5 -2.5,0.5 -0.6,-0.5 0,-1 -0.2,-0.3 -0.8,0.3 -0.6,-0.5 -2,0.2 -0.3,-0.8 -0.6,0.6 -4.2,-1 -3.5,-9.3 0,-0.5 0.8,-0.4 -1,-0.3 -2,-5.4 1.4,-0.2 1.6,0.2 0.6,-2.4 0.6,0.7 -0.3,0.5 0.7,0.6 0.3,-2.1 1.4,1.2 0.2,-1.2 0.4,1 -0.6,1.5 1.8,0.2 3.7,-2.7 0,-0.5 -0.6,0 2,-2.4 -0.4,-2.2 2.2,-8.4 -1.2,-3.6 0,-2.9 -2.3,-6.2 -1.9,-2 -1.5,-0.4 -1.2,0.4 -0.4,0.6 -0.8,0 -1.5,-2.4 0,0.3 0,0.6 0.4,1.9 -1.4,3.5 1,1.3 2.1,-0.7 0,5.2 0.2,0.5 0,1.5 0,1.5 -1.2,-0.6 -1.7,-0.5 -0.6,-1 -1.6,-0.3 -0.8,0 -1.7,2.9 -2.6,1.3 -1,0 -1.6,0 -0.6,0.6 -0.7,0.9 -1,1.9 -1.2,1.1 -3.1,3.9 -0.6,0.4 -1,0.2 -2.1,0.3 -0.9,0.4 -0.8,-0.4 -3.1,-1.1 -0.7,-1.5 0.7,0 0.6,-0.7 0.4,-0.6 -0.6,-2.9 -0.4,-1.9 0.6,-0.9 -1.7,-1.5 -1.2,0.4 -0.8,0 -1.8,0 -3.1,0.8 -1,-2.2 -3.5,-1.1 -2.4,0 -1.1,0 -1.1,0.6 -0.5,0.5 -2.8,1 -1.4,0.4 -2.9,-1 -1.6,0 -2.5,2.4 -5.1,1.1 0,-0.5 -0.8,-0.9 -0.2,-0.3 0.6,-1.4 0.4,-0.9 0.7,-1 -3.1,-0.3 -2.2,0 -1.5,0.3 -0.1,1 -2.1,-0.4 -0.6,0.5 -2,-0.1 -1.1,-0.6 -0.2,-0.4 -1,-0.9 0,-0.5 -2.2,-1 -0.5,-0.6 -1,-0.3 -2.8,0.5 -3.1,1.4 -4.7,0.5 -3.5,3.4 -5.7,3.3 -5.2,-0.8 -3.5,2.8 -4.7,-1.1 -4.9,0.2 -7.3,-1.1 -1.2,-6.1 1.2,-2.7 -10.8,1.2 -4.1,0.9 -3.4,-0.5 -2.1,-5.5 1.6,0 2.1,-5.2 1.6,0 -1.4,-1.1 -1.6,-0.3 -1.1,-2.5 1.1,0 -0.7,-0.1 1.1,-0.8 0.3,-0.6 0,-1.4 0.9,-1.2 1.6,-1.8 -2.5,-0.4 -1.4,1.3 -4.8,2.5 -6,0 -1.6,1 -1.4,1 -2.2,1.3 0,0.6 2.2,2.5 1,0 0.4,1.4 -0.8,0.5 -1.7,-0.9 -0.5,0.9 3.8,1.1 -1.2,3.2 -2.6,0 -2.9,2 -4,0.4 -3.8,0.7 0.4,1.9 -0.9,1.9 0.5,0.1 1.5,-0.5 -0.6,2 -1.4,1.3 -1.3,3.4 -4.2,0.5 0.6,2.8 -1.5,1.5 -1.2,0.2 -1,1.4 0,1 -0.6,1.3 0.4,0.2 0.8,0 0,0.6 1.6,0.7 1.9,-0.4 -0.8,0.9 -1.3,0 -0.6,1.5 -1,-0.5 3.3,3.5 2,3 -1,0.7 -0.6,0.2 -1.5,0.9 0.5,1.5 -0.9,0.4 0,0.6 0,0.5 -0.6,0 0,0.5 0,0.4 0.4,1.9 -2,3.2 0.6,1.6 -0.6,0.6 -1,0.6 -0.2,0.9 -0.4,0.5 1.6,1.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path430"
         inkscape:connector-curvature="0"
         d="m 322.47474,502 -3.4,-1.1 0.2,-1.5 -0.7,1.1 -1.5,-0.9 0.2,-1 3,-0.5 0.4,-0.2 -0.8,0 -0.8,0 -1,-0.7 -1.7,-1 -1,0.4 0.2,0.9 -1.2,0.6 -0.6,0.3 0.6,0.2 1.2,0.8 -0.2,0.2 -2.6,0.4 -0.4,0.5 0.4,0 0.2,0 -0.6,0.9 0,-0.5 -0.2,0.2 -0.5,0 -0.9,-0.2 -1.1,-1.5 -0.6,-1 -1.6,-0.5 -0.6,0.5 0.6,1 0.6,0.6 2,0.1 -1.4,1 -0.2,0.7 -1,1.5 -1.1,0 -2,0.6 -1.2,-0.4 -1.6,-0.5 -3.1,0.5 -0.6,0 -1.2,0.5 0.6,1.7 0.2,0.2 -0.2,1 2.3,2.8 -0.7,1.1 0.7,0 0.5,-0.4 6.3,0.4 0,-0.6 3.1,-0.5 0,0.1 0.6,0.6 1.2,0 3.7,-0.7 4.1,-0.4 2.9,-2.1 2.6,0 1.2,-3.2 -3.8,-1 0.5,-1 z" /><g
         id="g432"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path434"
           inkscape:connector-curvature="0"
           d="m 324.1,476.4 -3.4,-1.1 0.2,-1.5 -0.7,1.1 -1.5,-0.9 0.2,-1 3,-0.5 0.4,-0.2 -0.8,0 -0.8,0 -1,-0.7 -1.7,-1 -1,0.4 0.2,0.9 -1.2,0.6 -0.6,0.3 0.6,0.2 1.2,0.8 -0.2,0.2 -2.6,0.4 -0.4,0.5 0.4,0 0.2,0 -0.6,0.9 0,-0.5 -0.2,0.2 -0.5,0 -0.9,-0.2 -1.1,-1.5 -0.6,-1 -1.6,-0.5 -0.6,0.5 0.6,1 0.6,0.6 2,0.1 -1.4,1 -0.2,0.7 -1,1.5 -1.1,0 -2,0.6 -1.2,-0.4 -1.6,-0.5 -3.1,0.5 -0.6,0 -1.2,0.5 0.6,1.7 0.2,0.2 -0.2,1 2.3,2.8 -0.7,1.1 0.7,0 0.5,-0.4 6.3,0.4 0,-0.6 3.1,-0.5 0,0.1 0.6,0.6 1.2,0 3.7,-0.7 4.1,-0.4 2.9,-2.1 2.6,0 1.2,-3.2 -3.8,-1 0.5,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path718"
         inkscape:connector-curvature="0"
         d="m 483.37474,468.7 -0.4,0.3 1,0.4 -0.3,-0.4 -0.3,-0.3 z" /><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path436"
         inkscape:connector-curvature="0"
         d="m 282.87474,500.2 1.7,0 1.9,0.3 0.6,0 0,0.4 0.6,0.2 0,-0.6 0.5,-0.3 0,-0.6 -1.1,-0.6 -0.6,-0.6 -1.5,0.3 -1,-0.3 -0.2,0.6 -1.1,1 0.2,0.2 z" /><g
         id="g438"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path440"
           inkscape:connector-curvature="0"
           d="m 284.5,474.6 1.7,0 1.9,0.3 0.6,0 0,0.4 0.6,0.2 0,-0.6 0.5,-0.3 0,-0.6 -1.1,-0.6 -0.6,-0.6 -1.5,0.3 -1,-0.3 -0.2,0.6 -1.1,1 0.2,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path442"
         inkscape:connector-curvature="0"
         d="m 287.97474,499.6 0.2,0 1.1,-0.5 1.4,-0.4 0.2,0.4 0.4,0.3 0.2,-0.9 0.5,-1.3 -0.7,0 -2,0 -2,0 0.7,-1.7 1.7,-1.4 0,-1 -1.1,1 -1,0.1 -0.6,-0.1 -1.6,1.4 1,0.2 -1.4,1 0,0.5 -1.1,0.7 -0.5,-0.9 -0.7,1.2 0.2,0.3 0.8,-0.3 0.2,0.3 1.1,0.2 1.4,-0.2 0.6,0.6 1,0.5 z" /><g
         id="g444"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path446"
           inkscape:connector-curvature="0"
           d="m 289.6,474 0.2,0 1.1,-0.5 1.4,-0.4 0.2,0.4 0.4,0.3 0.2,-0.9 0.5,-1.3 -0.7,0 -2,0 -2,0 0.7,-1.7 1.7,-1.4 0,-1 -1.1,1 -1,0.1 -0.6,-0.1 -1.6,1.4 1,0.2 -1.4,1 0,0.5 -1.1,0.7 -0.5,-0.9 -0.7,1.2 0.2,0.3 0.8,-0.3 0.2,0.3 1.1,0.2 1.4,-0.2 0.6,0.6 1,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path448"
         inkscape:connector-curvature="0"
         d="m 291.57474,492.4 -1.2,0.3 -1.5,0.4 0,0.2 0.9,-0.2 0,1 -1.7,1.5 -0.8,1.7 2,0 2.2,0 0.6,0 1,0 -0.4,-1.1 0.4,-1 0,-1 -1,-0.9 -0.5,-0.9 z" /><g
         id="g450"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path452"
           inkscape:connector-curvature="0"
           d="m 293.2,466.8 -1.2,0.3 -1.5,0.4 0,0.2 0.9,-0.2 0,1 -1.7,1.5 -0.8,1.7 2,0 2.2,0 0.6,0 1,0 -0.4,-1.1 0.4,-1 0,-1 -1,-0.9 -0.5,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path454"
         inkscape:connector-curvature="0"
         d="m 291.37474,499.4 1.2,0 0.4,0.2 1.3,0 0.4,-0.5 0,-0.6 1.3,-0.6 0,-0.7 -0.7,0 -1,0.4 -1.3,-0.4 -1,0 -0.5,1.3 -0.1,0.9 z" /><g
         id="g456"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path6240"
           inkscape:connector-curvature="0"
           d="m 293,473.8 1.2,0 0.4,0.2 1.3,0 0.4,-0.5 0,-0.6 1.3,-0.6 0,-0.7 -0.7,0 -1,0.4 -1.3,-0.4 -1,0 -0.5,1.3 -0.1,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path458"
         inkscape:connector-curvature="0"
         d="m 295.77474,497.2 0,-0.2 0.8,-0.4 0.6,-0.4 0.2,0.4 0.4,0 -0.4,-0.4 0.4,-0.5 0,-1.1 0.5,-0.6 0.2,-0.3 -0.2,-0.4 -0.5,-0.2 0,-0.4 0.5,-0.3 -0.5,-0.3 -1.6,-0.3 0.6,0.6 -0.6,0.9 -1,0 -0.3,-0.2 -1,0.9 -1,0.2 0,1 -0.4,0.9 0.4,1.1 1.2,0.4 1.1,-0.4 0.6,0 z" /><g
         id="g462"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:#ff00ff;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="RS"
           inkscape:connector-curvature="0"
           d="m 297.4,471.6 0,-0.2 0.8,-0.4 0.6,-0.4 0.2,0.4 0.4,0 -0.4,-0.4 0.4,-0.5 0,-1.1 0.5,-0.6 0.2,-0.3 -0.2,-0.4 -0.5,-0.2 0,-0.4 0.5,-0.3 -0.5,-0.3 -1.6,-0.3 0.6,0.6 -0.6,0.9 -1,0 -0.3,-0.2 -1,0.9 -1,0.2 0,1 -0.4,0.9 0.4,1.1 1.2,0.4 1.1,-0.4 0.6,0 z"
           onmouseout="mouseoutpays(this)"
           onclick="mouseclickpays(evt,this)"
           onmouseover="mouseoverpays(evt,this)" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path466"
         inkscape:connector-curvature="0"
         d="m 293.57474,492.7 -0.6,-0.6 -0.6,-0.9 -1,1.1 0.1,0 0.5,0.9 1,0.9 1,-0.1 1,-1 -0.4,-0.3 -1,0 z" /><g
         id="g468"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path470"
           inkscape:connector-curvature="0"
           d="m 295.2,467.1 -0.6,-0.6 -0.6,-0.9 -1,1.1 0.1,0 0.5,0.9 1,0.9 1,-0.1 1,-1 -0.4,-0.3 -1,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path472"
         inkscape:connector-curvature="0"
         d="m 296.27474,491.9 -1.2,-1 0,0.4 0,0.6 -1.4,0.9 1,0 0.4,0.3 0.2,0.3 1,0 0.6,-1 -0.6,-0.5 z" /><g
         id="g474"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path476"
           inkscape:connector-curvature="0"
           d="m 297.9,466.3 -1.2,-1 0,0.4 0,0.6 -1.4,0.9 1,0 0.4,0.3 0.2,0.3 1,0 0.6,-1 -0.6,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path478"
         inkscape:connector-curvature="0"
         d="m 297.77474,492.2 1.1,-1 0,-0.9 -0.6,-0.6 -0.9,0.4 -0.2,-0.7 -1,0 -0.6,-0.6 -0.4,0.9 -0.2,0.6 0,0.6 1.2,0.9 1.6,0.4 z" /><g
         id="g480"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path482"
           inkscape:connector-curvature="0"
           d="m 299.4,466.6 1.1,-1 0,-0.9 -0.6,-0.6 -0.9,0.4 -0.2,-0.7 -1,0 -0.6,-0.6 -0.4,0.9 -0.2,0.6 0,0.6 1.2,0.9 1.6,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path484"
         inkscape:connector-curvature="0"
         d="m 290.67474,507.2 0.8,-0.7 0.9,0.4 0.2,-0.6 0.4,-0.4 1,0.4 0.6,0 0.4,0.2 0.9,-0.2 1,-0.4 0.4,-0.2 -0.7,-1.7 -0.7,0 -0.2,0.4 -1.1,0 -0.4,0 -1.2,-0.5 -0.4,0 -1.2,-0.6 -1.1,-0.8 -1.4,0.4 -0.2,0.4 -0.6,0.6 0,0.9 2.6,2.4 z" /><g
         id="g486"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path488"
           inkscape:connector-curvature="0"
           d="m 292.3,481.6 0.8,-0.7 0.9,0.4 0.2,-0.6 0.4,-0.4 1,0.4 0.6,0 0.4,0.2 0.9,-0.2 1,-0.4 0.4,-0.2 -0.7,-1.7 -0.7,0 -0.2,0.4 -1.1,0 -0.4,0 -1.2,-0.5 -0.4,0 -1.2,-0.6 -1.1,-0.8 -1.4,0.4 -0.2,0.4 -0.6,0.6 0,0.9 2.6,2.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path490"
         inkscape:connector-curvature="0"
         d="m 283.97474,509.7 0.6,0 0.4,0 0.7,0 0,-0.4 1,-0.4 0.4,0 0,-0.2 0,-0.4 0.6,-0.3 0.4,0 0.7,0.3 0.6,-0.3 0,-0.2 1.5,-0.4 0,-0.2 -2.8,-2.5 -1.6,0.3 -1.1,0.3 -0.8,-0.9 -0.6,0 -1,0.3 0,0.3 -0.7,0.7 -0.1,0.2 -1.1,0.6 0,0.7 -0.8,0.2 0,0.6 0,0.3 0.4,-0.3 3.1,1.3 0.2,0.4 z" /><g
         id="g492"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path494"
           inkscape:connector-curvature="0"
           d="m 285.6,484.1 0.6,0 0.4,0 0.7,0 0,-0.4 1,-0.4 0.4,0 0,-0.2 0,-0.4 0.6,-0.3 0.4,0 0.7,0.3 0.6,-0.3 0,-0.2 1.5,-0.4 0,-0.2 -2.8,-2.5 -1.6,0.3 -1.1,0.3 -0.8,-0.9 -0.6,0 -1,0.3 0,0.3 -0.7,0.7 -0.1,0.2 -1.1,0.6 0,0.7 -0.8,0.2 0,0.6 0,0.3 0.4,-0.3 3.1,1.3 0.2,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path496"
         inkscape:connector-curvature="0"
         d="m 379.27474,486.3 0.9,-0.8 0.5,-0.2 0.5,-2.8 1.1,0.6 1,0.7 1.3,0.6 1,-0.4 0.3,0 0.7,-0.2 -0.7,0 0.3,-0.3 0.4,-0.4 -2.7,0 -1,-0.2 -1,-0.5 -0.2,-0.8 0.2,-1.5 -0.2,-1.1 -0.5,-0.5 0.5,-1 -1.5,0 -0.6,0 0.5,-0.7 0.1,-0.6 -0.9,-0.5 0,-1 -0.3,-0.5 0.3,-1 -0.7,-0.5 -0.6,0.1 -0.4,0 -0.6,0.4 -0.4,-0.4 -0.2,-0.5 -1,0 -0.5,0 -0.2,-0.2 -0.3,-0.3 0,-1.9 -2.1,-0.6 -1.6,-0.3 -0.6,-0.3 -1.1,0 -1.2,0 -2.4,0.6 0.4,1.3 0.6,0.8 -0.2,0.9 -1.6,0.4 0,1.5 -0.8,1.5 0.3,0.5 -0.3,0.4 -0.3,0.9 0.6,0.2 -0.3,0.4 0.3,1.1 0,1.3 2.1,-0.6 0,-0.7 1.6,1.3 -0.4,0.2 2.7,1.3 0,1.5 1.2,0.3 1.4,-0.3 2.2,-0.6 1.7,0.9 0.4,-0.3 0.4,0.5 1.6,0.6 -0.4,0.3 0.7,1.4 z" /><g
         id="g498"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path500"
           inkscape:connector-curvature="0"
           d="m 380.9,460.7 0.9,-0.8 0.5,-0.2 0.5,-2.8 1.1,0.6 1,0.7 1.3,0.6 1,-0.4 0.3,0 0.7,-0.2 -0.7,0 0.3,-0.3 0.4,-0.4 -2.7,0 -1,-0.2 -1,-0.5 -0.2,-0.8 0.2,-1.5 -0.2,-1.1 -0.5,-0.5 0.5,-1 -1.5,0 -0.6,0 0.5,-0.7 0.1,-0.6 -0.9,-0.5 0,-1 -0.3,-0.5 0.3,-1 -0.7,-0.5 -0.6,0.1 -0.4,0 -0.6,0.4 -0.4,-0.4 -0.2,-0.5 -1,0 -0.5,0 -0.2,-0.2 -0.3,-0.3 0,-1.9 -2.1,-0.6 -1.6,-0.3 -0.6,-0.3 -1.1,0 -1.2,0 -2.4,0.6 0.4,1.3 0.6,0.8 -0.2,0.9 -1.6,0.4 0,1.5 -0.8,1.5 0.3,0.5 -0.3,0.4 -0.3,0.9 0.6,0.2 -0.3,0.4 0.3,1.1 0,1.3 2.1,-0.6 0,-0.7 1.6,1.3 -0.4,0.2 2.7,1.3 0,1.5 1.2,0.3 1.4,-0.3 2.2,-0.6 1.7,0.9 0.4,-0.3 0.4,0.5 1.6,0.6 -0.4,0.3 0.7,1.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path502"
         inkscape:connector-curvature="0"
         d="m 415.47474,455.7 -1.2,3.4 0.2,0.9 -1.1,0.4 -0.5,0.5 0.5,0.3 0,0.7 1.1,0 -0.6,0.6 -0.5,0 -0.5,0.5 0.4,1.3 0.6,0 1,-0.7 1,-0.6 0.2,-0.9 -0.2,-0.2 4,0.2 0.4,-0.6 -0.4,-0.3 0,-0.6 -1.1,-0.2 -0.2,-0.7 -0.5,-0.2 0.5,-1.3 0.2,0 0.4,-0.2 0.2,0 0.9,0.7 0,-0.7 0.5,-0.7 0.4,-0.8 0.3,-1.9 -0.7,0.2 -0.4,-0.2 -0.1,1 -0.4,1.1 -1.1,0.9 -1.1,0.4 -0.2,0.7 -1,0.4 0,-0.6 0.6,0 0.4,-0.9 -0.4,-0.3 0.4,-0.6 -0.4,-1 -0.6,0.8 0,0.2 -0.4,-0.2 0.4,-0.8 -0.6,0 -0.4,0 z" /><g
         id="g504"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path506"
           inkscape:connector-curvature="0"
           d="m 417.1,430.1 -1.2,3.4 0.2,0.9 -1.1,0.4 -0.5,0.5 0.5,0.3 0,0.7 1.1,0 -0.6,0.6 -0.5,0 -0.5,0.5 0.4,1.3 0.6,0 1,-0.7 1,-0.6 0.2,-0.9 -0.2,-0.2 4,0.2 0.4,-0.6 -0.4,-0.3 0,-0.6 -1.1,-0.2 -0.2,-0.7 -0.5,-0.2 0.5,-1.3 0.2,0 0.4,-0.2 0.2,0 0.9,0.7 0,-0.7 0.5,-0.7 0.4,-0.8 0.3,-1.9 -0.7,0.2 -0.4,-0.2 -0.1,1 -0.4,1.1 -1.1,0.9 -1.1,0.4 -0.2,0.7 -1,0.4 0,-0.6 0.6,0 0.4,-0.9 -0.4,-0.3 0.4,-0.6 -0.4,-1 -0.6,0.8 0,0.2 -0.4,-0.2 0.4,-0.8 -0.6,0 -0.4,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path508"
         inkscape:connector-curvature="0"
         d="m 413.37474,465.9 1.1,0.4 0.4,1 1,-0.4 1.2,0 0.4,0.4 0.6,-0.6 0,-0.8 0.7,-0.1 0.4,-1 -2.7,0 0,0.2 -1,-0.2 -1.3,0.2 -0.8,0.4 0,0.5 z" /><g
         id="g510"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path512"
           inkscape:connector-curvature="0"
           d="m 415,440.3 1.1,0.4 0.4,1 1,-0.4 1.2,0 0.4,0.4 0.6,-0.6 0,-0.8 0.7,-0.1 0.4,-1 -2.7,0 0,0.2 -1,-0.2 -1.3,0.2 -0.8,0.4 0,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path514"
         inkscape:connector-curvature="0"
         d="m 421.07474,456.5 0.7,0 -0.4,1.5 0.6,0.2 0,1.5 1,0 0.7,1.3 0,0.9 0,0.6 0.7,0.5 0,1.4 1.3,1.1 1.4,0.3 0.2,-0.3 0.4,0.3 -0.4,1 0.4,0.5 0.2,0 0,0.6 1.1,0 0.8,-1 0,-0.5 0.6,0 0.6,-3 -0.4,-0.7 0,-0.2 -0.2,0 -1,-0.6 0.4,-0.7 -0.8,0 0.4,-0.8 0.4,-0.7 0.6,0.3 0.2,0 1,0 -0.2,-0.3 0.2,-0.2 0.4,-1.3 1,0 0,-0.9 -0.4,-0.8 1.6,-0.3 0,-0.5 0,-0.1 0.5,0 0.6,-0.4 1,0 -0.6,-0.4 -0.4,-0.1 0.4,-0.4 -0.9,-0.2 -0.1,-0.4 -1.1,-0.3 -0.4,-0.6 -0.6,0.4 0.4,-0.6 -1.6,-0.4 -0.4,-0.4 -0.4,-0.5 0,-0.9 -0.2,-0.2 0,-0.4 0.2,0 0.4,-0.6 1.2,-1.8 0.4,-0.6 0.4,-0.9 -0.4,-0.4 0,-0.7 -0.4,-0.8 0,-0.5 1.9,-1.9 0,-0.8 0.6,-1.8 -0.5,-1 -0.6,-1.4 0,-0.6 -0.4,-0.4 -0.2,1 0.2,0.5 0,1.5 -0.2,0 0.2,0.4 0,1.5 -1,1.8 0,-0.7 -0.2,0 -0.4,1.5 -0.2,1.7 -0.4,1.1 0,0.9 -0.6,0 -0.4,0.6 -0.2,0.5 -0.4,-0.1 0,-1 -0.7,-0.5 -1.4,-0.8 -0.2,-0.2 -0.8,0.2 0.4,0.4 -0.4,-0.4 -0.6,0 0,0.4 -0.7,-0.4 0,1.9 0.5,1.5 -0.5,0.9 -1,1.1 0,-0.2 -1,1 0.7,-0.4 0.3,0.4 -1,0.5 -0.2,0.6 -0.4,0.4 -0.6,0.2 -0.4,0 -1.1,1.3 0.5,0.1 0.6,-0.1 -0.3,1.8 z" /><g
         id="g516"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path518"
           inkscape:connector-curvature="0"
           d="m 422.7,430.9 0.7,0 -0.4,1.5 0.6,0.2 0,1.5 1,0 0.7,1.3 0,0.9 0,0.6 0.7,0.5 0,1.4 1.3,1.1 1.4,0.3 0.2,-0.3 0.4,0.3 -0.4,1 0.4,0.5 0.2,0 0,0.6 1.1,0 0.8,-1 0,-0.5 0.6,0 0.6,-3 -0.4,-0.7 0,-0.2 -0.2,0 -1,-0.6 0.4,-0.7 -0.8,0 0.4,-0.8 0.4,-0.7 0.6,0.3 0.2,0 1,0 -0.2,-0.3 0.2,-0.2 0.4,-1.3 1,0 0,-0.9 -0.4,-0.8 1.6,-0.3 0,-0.5 0,-0.1 0.5,0 0.6,-0.4 1,0 -0.6,-0.4 -0.4,-0.1 0.4,-0.4 -0.9,-0.2 -0.1,-0.4 -1.1,-0.3 -0.4,-0.6 -0.6,0.4 0.4,-0.6 -1.6,-0.4 -0.4,-0.4 -0.4,-0.5 0,-0.9 -0.2,-0.2 0,-0.4 0.2,0 0.4,-0.6 1.2,-1.8 0.4,-0.6 0.4,-0.9 -0.4,-0.4 0,-0.7 -0.4,-0.8 0,-0.5 1.9,-1.9 0,-0.8 0.6,-1.8 -0.5,-1 -0.6,-1.4 0,-0.6 -0.4,-0.4 -0.2,1 0.2,0.5 0,1.5 -0.2,0 0.2,0.4 0,1.5 -1,1.8 0,-0.7 -0.2,0 -0.4,1.5 -0.2,1.7 -0.4,1.1 0,0.9 -0.6,0 -0.4,0.6 -0.2,0.5 -0.4,-0.1 0,-1 -0.7,-0.5 -1.4,-0.8 -0.2,-0.2 -0.8,0.2 0.4,0.4 -0.4,-0.4 -0.6,0 0,0.4 -0.7,-0.4 0,1.9 0.5,1.5 -0.5,0.9 -1,1.1 0,-0.2 -1,1 0.7,-0.4 0.3,0.4 -1,0.5 -0.2,0.6 -0.4,0.4 -0.6,0.2 -0.4,0 -1.1,1.3 0.5,0.1 0.6,-0.1 -0.3,1.8 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path520"
         inkscape:connector-curvature="0"
         d="m 461.27474,426.8 1,0.5 0,-0.5 0.6,-0.7 -0.6,0 -0.7,-0.6 -0.3,1.3 z" /><g
         id="g522"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path524"
           inkscape:connector-curvature="0"
           d="m 462.9,401.2 1,0.5 0,-0.5 0.6,-0.7 -0.6,0 -0.7,-0.6 -0.3,1.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path526"
         inkscape:connector-curvature="0"
         d="m 470.07474,456.5 -0.2,0 0,0.6 -0.7,0.5 -0.7,1.2 0,0.9 0.4,0.7 1,2.1 0.6,0.5 1.1,-0.5 0,-1 -0.5,-0.5 -0.2,-2.2 -0.4,-1.2 -0.4,-1.1 z" /><g
         id="g528"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path530"
           inkscape:connector-curvature="0"
           d="m 471.7,430.9 -0.2,0 0,0.6 -0.7,0.5 -0.7,1.2 0,0.9 0.4,0.7 1,2.1 0.6,0.5 1.1,-0.5 0,-1 -0.5,-0.5 -0.2,-2.2 -0.4,-1.2 -0.4,-1.1 z" /></g><path
         style="fill:#d99594;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="CN"
         inkscape:connector-curvature="0"
         d="m 455.47474,526.4 0.8,0.4 1,-0.4 2,-0.4 1,-0.2 0.6,-0.4 3.1,-3.8 1.3,-1.1 1,-1.9 0.6,-1 0.6,-0.6 1.6,0 1,0 2.7,-1.2 1.7,-3 0.7,0 1.6,0.4 0.6,0.9 1.7,0.5 1.2,0.6 0,-1.4 0,-1.5 -0.2,-0.6 0,-5.2 -2,0.7 -1.1,-1.3 1.5,-3.5 -0.4,-1.9 0,-0.6 -0.7,0.6 -0.6,0.4 0,-1 -0.7,-0.5 -0.3,-0.7 -0.6,-0.3 -0.7,-0.3 0,-0.2 0.4,-0.7 -0.7,0 -1,0 -0.6,0.7 -0.8,-0.3 0,-1 -0.2,-0.6 -0.7,-0.5 -1.4,-0.9 -0.6,-1.5 -1.4,-0.4 -0.8,-0.6 -1,-0.9 -1.1,-0.9 -0.4,0.4 0.6,0.5 0,0.4 0.5,0.2 -0.7,0.3 -0.4,0.6 1.1,1.9 -1.3,0.9 -1.4,-0.7 -1,-1.7 -1.1,-1 -0.2,-0.9 -0.6,-0.4 -1.4,0 0.4,-1.5 0.6,-1.1 0.6,0.2 1,-0.5 0.4,-1 0.7,-0.5 0.6,0.4 1,0.9 0.6,0.5 1.5,-0.5 1,0 0.8,0 0,-1.3 -0.8,0.4 -1.9,-1 0.5,-0.4 -1.3,-1.4 -1,-1.5 0,-0.9 1.8,-1 1.5,-2.2 0.6,-0.4 0.4,-1 1,-0.9 0.6,-0.5 -1.6,0.3 -0.6,0.2 -0.4,-0.2 0.6,0 1,-0.9 1.2,-1.2 -0.8,-0.3 -0.4,-0.7 -1,0 -0.2,-0.6 0.6,0.4 0,-0.5 1.2,0.5 1,-0.5 0.4,-0.4 -0.8,-0.6 0.8,0 0,-0.5 -0.8,0 0.4,-0.4 0,-0.6 -0.4,0 0,-0.4 0.8,-0.3 -0.8,-0.2 0,0.2 -0.2,-0.2 -0.6,-1.5 -0.8,-1.5 -0.6,0 0,-0.3 0.4,-0.4 -0.4,-0.6 0,-0.9 0,-0.6 -0.6,-0.9 -0.6,-0.6 -1.1,-0.3 0.2,-0.2 -0.8,-1 -0.8,-0.3 -0.2,0 -0.6,-1.5 -1.5,-0.2 -0.4,-0.4 -0.6,0.4 -0.2,-0.4 -0.4,0.4 -1,-0.7 -1,0.9 0.4,-1.5 -1.7,-0.6 -0.4,0.4 0,-0.4 -0.6,-0.4 -0.6,-0.1 -1,-0.4 -1,-0.4 0,0.4 -0.4,-0.9 0.8,-0.6 -1,-0.4 -0.4,1.4 -0.2,0.5 0.6,0.4 -1,0.5 -0.4,-0.5 -0.7,0.1 -0.6,0.4 -0.4,0 0,-0.5 -0.6,0 -0.4,0.1 -0.8,-0.1 -1.4,1.5 0.4,0.9 -0.4,0 -1.7,0.2 -0.6,0.6 -1,-0.3 0,-0.5 -1,-0.4 -0.4,-0.5 -0.6,0.5 -0.7,0 -0.4,-0.3 -0.6,0 -0.2,0 -0.4,-0.2 -0.6,0 -0.4,-0.4 0.4,-1.9 -1,0 0,0.4 -1,0 -0.6,0.4 -0.4,0 0,0.1 0,0.4 -1.7,0.4 0.4,0.8 0,0.9 -1,0 -0.4,1.3 -0.2,0.2 0.2,0.3 -1,0 -0.3,0 -0.6,-0.3 -0.4,0.7 -0.3,0.8 0.7,0 -0.4,0.7 1,0.6 0.3,0 0,0.2 0.4,0.7 -0.7,3 -0.6,0 0,0.5 -0.7,1 -1.1,0 0,-0.6 -0.6,0.6 -0.6,0 0,0.3 -0.6,-0.3 0.2,0.5 0,0.6 -0.6,0 0.4,0.7 -0.4,0.2 -0.6,-0.2 -0.7,-0.7 -1.6,0.3 -0.8,-0.3 -0.2,-0.6 -1.5,-0.2 0,-0.7 -0.1,-0.2 -0.4,-0.4 -1.1,-0.2 -0.6,0 -0.6,0.6 -0.4,-0.4 -1.2,0 -1,0.4 -0.4,-0.9 -1.1,-0.4 0,0.9 -1,-0.2 -1.8,0 -1,0.2 -0.4,-0.2 -1.7,0.6 -0.6,0 0,0.9 -0.6,-0.3 -1,0.3 -0.4,0.8 -0.7,0 -0.4,-0.2 -0.6,0.9 -2.8,1.1 -1,0.4 -2.3,1.1 -0.4,0.4 -1,-0.4 -0.7,1.6 0,0.7 -0.3,0 -0.2,0.5 0.2,0.2 0.6,-0.6 1,1 -0.6,0.5 -0.7,0.4 -0.3,1.1 -0.2,0.8 -0.4,0.7 -1.3,1.3 -0.4,0 -1.6,1.2 -1,0.9 -2.3,0.9 -1,-0.4 -0.4,0.4 -0.2,0.3 0.6,0 0.6,0.6 -1,2.1 -1,0.4 -0.6,0 -0.6,1.4 0.6,0 -0.2,1.1 1.6,1 0.8,0.4 0.4,-0.4 1.5,0.4 0.2,0.9 2,0.2 0.6,0.9 2.7,1.3 0.4,2.6 -1.7,3.3 -0.8,0 2.7,1.2 1,-0.2 0.6,0.6 -0.6,0.4 0.4,3.1 3.5,0 0.8,0.7 -0.8,2.2 1,0.4 0.8,0.4 0.2,1.1 1,0.4 0,-0.9 0.6,-0.4 0.4,-0.2 0.6,-0.4 1.1,-1.1 0.6,0.7 0.4,-0.1 0.2,-0.6 0.4,0.2 1.2,-1.1 1.1,-1.3 0,-1 0.4,-0.7 -0.4,-0.3 0.4,-1 1.6,-0.5 2.2,-0.4 1.1,0 1.6,-0.9 1.6,-0.6 0.4,0 0.6,-0.6 1.1,-1.8 1,-1 1.6,0 0.4,0.4 3.3,0 0.6,0.4 2.6,0 0.6,-0.8 3.3,-0.7 1.1,0 0.5,-0.3 1.1,0 2,1.4 4.9,0.6 0.6,0.7 0.8,0.5 0.2,1.2 1.1,0.6 0,1.3 -0.7,0.5 0,1 0.3,0.5 0.8,-0.2 1.2,0 1.4,0 0.2,0.2 0.4,1.3 1.1,0 1.2,1.1 0,0.6 0.6,0.7 0.4,0 0.4,-0.4 0.2,0 0,0.6 0.4,0 0.6,0.9 1.5,0.4 1.2,-0.4 0.4,0.4 -0.8,0.9 -0.7,0.6 -1.2,0.9 -1,0.6 -1,-0.4 -0.6,-0.9 -0.6,0.3 -1,0 -0.5,-0.3 -1,0.9 0.4,0.4 0,0.6 0,2.3 0.2,1 0.9,0 1.2,-0.4 1.6,1.5 -0.6,0.9 0.4,1.9 0.6,3 -0.4,0.5 -0.6,0.7 -0.6,0 0.6,1.6 3.1,1.1 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g534"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path536"
           inkscape:connector-curvature="0"
           d="m 457.1,500.8 0.8,0.4 1,-0.4 2,-0.4 1,-0.2 0.6,-0.4 3.1,-3.8 1.3,-1.1 1,-1.9 0.6,-1 0.6,-0.6 1.6,0 1,0 2.7,-1.2 1.7,-3 0.7,0 1.6,0.4 0.6,0.9 1.7,0.5 1.2,0.6 0,-1.4 0,-1.5 -0.2,-0.6 0,-5.2 -2,0.7 -1.1,-1.3 1.5,-3.5 -0.4,-1.9 0,-0.6 -0.7,0.6 -0.6,0.4 0,-1 -0.7,-0.5 -0.3,-0.7 -0.6,-0.3 -0.7,-0.3 0,-0.2 0.4,-0.7 -0.7,0 -1,0 -0.6,0.7 -0.8,-0.3 0,-1 -0.2,-0.6 -0.7,-0.5 -1.4,-0.9 -0.6,-1.5 -1.4,-0.4 -0.8,-0.6 -1,-0.9 -1.1,-0.9 -0.4,0.4 0.6,0.5 0,0.4 0.5,0.2 -0.7,0.3 -0.4,0.6 1.1,1.9 -1.3,0.9 -1.4,-0.7 -1,-1.7 -1.1,-1 -0.2,-0.9 -0.6,-0.4 -1.4,0 0.4,-1.5 0.6,-1.1 0.6,0.2 1,-0.5 0.4,-1 0.7,-0.5 0.6,0.4 1,0.9 0.6,0.5 1.5,-0.5 1,0 0.8,0 0,-1.3 -0.8,0.4 -1.9,-1 0.5,-0.4 -1.3,-1.4 -1,-1.5 0,-0.9 1.8,-1 1.5,-2.2 0.6,-0.4 0.4,-1 1,-0.9 0.6,-0.5 -1.6,0.3 -0.6,0.2 -0.4,-0.2 0.6,0 1,-0.9 1.2,-1.2 -0.8,-0.3 -0.4,-0.7 -1,0 -0.2,-0.6 0.6,0.4 0,-0.5 1.2,0.5 1,-0.5 0.4,-0.4 -0.8,-0.6 0.8,0 0,-0.5 -0.8,0 0.4,-0.4 0,-0.6 -0.4,0 0,-0.4 0.8,-0.3 -0.8,-0.2 0,0.2 -0.2,-0.2 -0.6,-1.5 -0.8,-1.5 -0.6,0 0,-0.3 0.4,-0.4 -0.4,-0.6 0,-0.9 0,-0.6 -0.6,-0.9 -0.6,-0.6 -1.1,-0.3 0.2,-0.2 -0.8,-1 -0.8,-0.3 -0.2,0 -0.6,-1.5 -1.5,-0.2 -0.4,-0.4 -0.6,0.4 -0.2,-0.4 -0.4,0.4 -1,-0.7 -1,0.9 0.4,-1.5 -1.7,-0.6 -0.4,0.4 0,-0.4 -0.6,-0.4 -0.6,-0.1 -1,-0.4 -1,-0.4 0,0.4 -0.4,-0.9 0.8,-0.6 -1,-0.4 -0.4,1.4 -0.2,0.5 0.6,0.4 -1,0.5 -0.4,-0.5 -0.7,0.1 -0.6,0.4 -0.4,0 0,-0.5 -0.6,0 -0.4,0.1 -0.8,-0.1 -1.4,1.5 0.4,0.9 -0.4,0 -1.7,0.2 -0.6,0.6 -1,-0.3 0,-0.5 -1,-0.4 -0.4,-0.5 -0.6,0.5 -0.7,0 -0.4,-0.3 -0.6,0 -0.2,0 -0.4,-0.2 -0.6,0 -0.4,-0.4 0.4,-1.9 -1,0 0,0.4 -1,0 -0.6,0.4 -0.4,0 0,0.1 0,0.4 -1.7,0.4 0.4,0.8 0,0.9 -1,0 -0.4,1.3 -0.2,0.2 0.2,0.3 -1,0 -0.3,0 -0.6,-0.3 -0.4,0.7 -0.3,0.8 0.7,0 -0.4,0.7 1,0.6 0.3,0 0,0.2 0.4,0.7 -0.7,3 -0.6,0 0,0.5 -0.7,1 -1.1,0 0,-0.6 -0.6,0.6 -0.6,0 0,0.3 -0.6,-0.3 0.2,0.5 0,0.6 -0.6,0 0.4,0.7 -0.4,0.2 -0.6,-0.2 -0.7,-0.7 -1.6,0.3 -0.8,-0.3 -0.2,-0.6 -1.5,-0.2 0,-0.7 -0.1,-0.2 -0.4,-0.4 -1.1,-0.2 -0.6,0 -0.6,0.6 -0.4,-0.4 -1.2,0 -1,0.4 -0.4,-0.9 -1.1,-0.4 0,0.9 -1,-0.2 -1.8,0 -1,0.2 -0.4,-0.2 -1.7,0.6 -0.6,0 0,0.9 -0.6,-0.3 -1,0.3 -0.4,0.8 -0.7,0 -0.4,-0.2 -0.6,0.9 -2.8,1.1 -1,0.4 -2.3,1.1 -0.4,0.4 -1,-0.4 -0.7,1.6 0,0.7 -0.3,0 -0.2,0.5 0.2,0.2 0.6,-0.6 1,1 -0.6,0.5 -0.7,0.4 -0.3,1.1 -0.2,0.8 -0.4,0.7 -1.3,1.3 -0.4,0 -1.6,1.2 -1,0.9 -2.3,0.9 -1,-0.4 -0.4,0.4 -0.2,0.3 0.6,0 0.6,0.6 -1,2.1 -1,0.4 -0.6,0 -0.6,1.4 0.6,0 -0.2,1.1 1.6,1 0.8,0.4 0.4,-0.4 1.5,0.4 0.2,0.9 2,0.2 0.6,0.9 2.7,1.3 0.4,2.6 -1.7,3.3 -0.8,0 2.7,1.2 1,-0.2 0.6,0.6 -0.6,0.4 0.4,3.1 3.5,0 0.8,0.7 -0.8,2.2 1,0.4 0.8,0.4 0.2,1.1 1,0.4 0,-0.9 0.6,-0.4 0.4,-0.2 0.6,-0.4 1.1,-1.1 0.6,0.7 0.4,-0.1 0.2,-0.6 0.4,0.2 1.2,-1.1 1.1,-1.3 0,-1 0.4,-0.7 -0.4,-0.3 0.4,-1 1.6,-0.5 2.2,-0.4 1.1,0 1.6,-0.9 1.6,-0.6 0.4,0 0.6,-0.6 1.1,-1.8 1,-1 1.6,0 0.4,0.4 3.3,0 0.6,0.4 2.6,0 0.6,-0.8 3.3,-0.7 1.1,0 0.5,-0.3 1.1,0 2,1.4 4.9,0.6 0.6,0.7 0.8,0.5 0.2,1.2 1.1,0.6 0,1.3 -0.7,0.5 0,1 0.3,0.5 0.8,-0.2 1.2,0 1.4,0 0.2,0.2 0.4,1.3 1.1,0 1.2,1.1 0,0.6 0.6,0.7 0.4,0 0.4,-0.4 0.2,0 0,0.6 0.4,0 0.6,0.9 1.5,0.4 1.2,-0.4 0.4,0.4 -0.8,0.9 -0.7,0.6 -1.2,0.9 -1,0.6 -1,-0.4 -0.6,-0.9 -0.6,0.3 -1,0 -0.5,-0.3 -1,0.9 0.4,0.4 0,0.6 0,2.3 0.2,1 0.9,0 1.2,-0.4 1.6,1.5 -0.6,0.9 0.4,1.9 0.6,3 -0.4,0.5 -0.6,0.7 -0.6,0 0.6,1.6 3.1,1.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path538"
         inkscape:connector-curvature="0"
         d="m 392.37474,480.5 0.4,0 1.2,-1.3 0.4,-0.7 0.3,-0.8 0.3,-1.1 0.7,-0.4 0.6,-0.5 -1,-1 -0.6,0.6 -0.3,-0.2 0.3,-0.5 0.3,0 0,-0.8 0.7,-1.4 1,0.3 0.4,-0.3 2.2,-1.2 -0.6,-1.3 -0.4,-0.6 0.4,-0.5 -0.8,-0.4 0.8,-0.5 1.3,-0.6 1.4,-0.6 1.2,-0.3 2.1,-0.6 1.6,-0.3 0.2,-0.4 1,-0.6 0.6,0.4 0.8,-0.5 0.3,0 1,0 0.6,0 0,-0.4 1.6,0 -0.2,1.8 0.2,0.9 1,0.2 0,-0.9 0,-0.5 0.8,-0.4 1.2,-0.2 1.1,0.2 0,-0.2 2.7,0 -0.5,0.9 -0.6,0.2 0,0.7 0.6,0 1.1,0.2 0.4,0.4 0.2,0.2 0,0.7 1.4,0.2 0.2,0.6 0.8,0.3 1.6,-0.3 0.6,0.7 0.7,0.2 0.4,-0.2 -0.4,-0.7 0.6,0 0,-0.6 -0.2,-0.5 0.6,0.3 0,-0.3 0.6,0 0.6,-0.6 -0.2,0 -0.4,-0.6 0.4,-0.9 -0.4,-0.3 -0.2,0.3 -1.4,-0.3 -1.3,-1.1 0,-1.4 -0.7,-0.5 0,-0.6 0,-1 -0.7,-1.2 -1,0 0,-1.5 -0.6,-0.2 0.4,-1.5 -0.6,0 -0.4,0.8 -0.6,0.7 0,0.8 -0.8,-0.8 -0.2,0 -0.5,0.2 -0.2,0 -0.4,1.3 0.4,0.2 0.2,0.7 1.1,0.2 0,0.6 0.4,0.3 -0.4,0.6 -4,-0.2 0.3,0.2 -0.3,0.9 -0.9,0.6 -1.1,0.8 -0.5,0 -0.4,-1.4 0.5,-0.5 0.4,0 0.7,-0.6 -1.1,0 0,-0.7 -0.5,-0.3 0.5,-0.5 1.1,-0.4 -0.3,-0.9 1.2,-3.3 -0.9,-0.2 0,0.5 -0.7,-0.9 -0.4,0.4 0,0.9 -0.1,0 0,-0.7 -1.5,-0.6 -0.6,-0.4 0,-0.1 0.4,-1 -0.4,-0.6 -0.4,-0.3 -0.2,-0.6 -1.4,-0.3 0,0.3 -0.7,-0.3 0.4,0 -1.6,-2.1 -1.4,-0.9 -1.2,-1.1 -0.9,-0.8 0.4,-0.1 -1.1,-0.6 -0.9,-0.4 -0.6,-0.6 -0.2,0 -0.8,-0.4 -0.2,-1.1 0.2,-0.9 0,-0.5 0,-1 -0.2,0 0.6,-0.9 -0.6,-1.3 -0.4,-0.7 0.4,-2.5 -1.1,0 -0.6,-1.3 0.9,-0.2 -0.3,-0.3 -1.4,0 -0.6,-0.6 0,-0.5 -1,-0.8 -2.3,3 0.4,-0.2 -1.4,2.6 -1,1.5 -1.2,2.5 -0.6,1.8 -1,1.4 -1.7,4.6 -0.4,1.2 -0.4,2.1 0,1.3 -0.2,0.6 0,0.1 0.2,0.4 -0.2,0.4 -0.4,0.5 0.6,0 0,0.4 -0.8,0 0,-0.7 -0.8,-0.2 0.8,-0.4 -0.4,-0.9 -2,-0.6 -1.3,0.6 -2.2,2.2 0.6,0 1.6,0.6 0,0.5 -1.4,-0.5 -1.6,0.9 -0.3,0.8 0.3,0.3 -0.3,0 0,0.4 0.6,0 0,0.2 1.7,0 0.6,0 0.6,0.3 0.4,-0.3 0.4,0.3 0,0.4 -1,2.1 -0.6,0.2 0,1.7 -1.5,0.1 1.1,1.5 0.4,0.4 0.2,0.3 0.4,-0.7 2.1,0.7 0.2,0.2 0.4,0.4 0,0.9 1.6,0.8 0,0.9 0.6,0.8 0.6,1.1 0.8,0.6 -0.8,0.9 0.8,0.5 0.7,0.4 0,0.5 -1.5,0.4 -0.2,0.6 -0.8,0.3 0,1.6 -0.8,0.9 0.6,0.5 1.6,0 1.1,-0.3 1.6,0.5 1.2,1 0.4,0.3 z" /><g
         id="g540"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path542"
           inkscape:connector-curvature="0"
           d="m 394,454.9 0.4,0 1.2,-1.3 0.4,-0.7 0.3,-0.8 0.3,-1.1 0.7,-0.4 0.6,-0.5 -1,-1 -0.6,0.6 -0.3,-0.2 0.3,-0.5 0.3,0 0,-0.8 0.7,-1.4 1,0.3 0.4,-0.3 2.2,-1.2 -0.6,-1.3 -0.4,-0.6 0.4,-0.5 -0.8,-0.4 0.8,-0.5 1.3,-0.6 1.4,-0.6 1.2,-0.3 2.1,-0.6 1.6,-0.3 0.2,-0.4 1,-0.6 0.6,0.4 0.8,-0.5 0.3,0 1,0 0.6,0 0,-0.4 1.6,0 -0.2,1.8 0.2,0.9 1,0.2 0,-0.9 0,-0.5 0.8,-0.4 1.2,-0.2 1.1,0.2 0,-0.2 2.7,0 -0.5,0.9 -0.6,0.2 0,0.7 0.6,0 1.1,0.2 0.4,0.4 0.2,0.2 0,0.7 1.4,0.2 0.2,0.6 0.8,0.3 1.6,-0.3 0.6,0.7 0.7,0.2 0.4,-0.2 -0.4,-0.7 0.6,0 0,-0.6 -0.2,-0.5 0.6,0.3 0,-0.3 0.6,0 0.6,-0.6 -0.2,0 -0.4,-0.6 0.4,-0.9 -0.4,-0.3 -0.2,0.3 -1.4,-0.3 -1.3,-1.1 0,-1.4 -0.7,-0.5 0,-0.6 0,-1 -0.7,-1.2 -1,0 0,-1.5 -0.6,-0.2 0.4,-1.5 -0.6,0 -0.4,0.8 -0.6,0.7 0,0.8 -0.8,-0.8 -0.2,0 -0.5,0.2 -0.2,0 -0.4,1.3 0.4,0.2 0.2,0.7 1.1,0.2 0,0.6 0.4,0.3 -0.4,0.6 -4,-0.2 0.3,0.2 -0.3,0.9 -0.9,0.6 -1.1,0.8 -0.5,0 -0.4,-1.4 0.5,-0.5 0.4,0 0.7,-0.6 -1.1,0 0,-0.7 -0.5,-0.3 0.5,-0.5 1.1,-0.4 -0.3,-0.9 1.2,-3.3 -0.9,-0.2 0,0.5 -0.7,-0.9 -0.4,0.4 0,0.9 -0.1,0 0,-0.7 -1.5,-0.6 -0.6,-0.4 0,-0.1 0.4,-1 -0.4,-0.6 -0.4,-0.3 -0.2,-0.6 -1.4,-0.3 0,0.3 -0.7,-0.3 0.4,0 -1.6,-2.1 -1.4,-0.9 -1.2,-1.1 -0.9,-0.8 0.4,-0.1 -1.1,-0.6 -0.9,-0.4 -0.6,-0.6 -0.2,0 -0.8,-0.4 -0.2,-1.1 0.2,-0.9 0,-0.5 0,-1 -0.2,0 0.6,-0.9 -0.6,-1.3 -0.4,-0.7 0.4,-2.5 -1.1,0 -0.6,-1.3 0.9,-0.2 -0.3,-0.3 -1.4,0 -0.6,-0.6 0,-0.5 -1,-0.8 -2.3,3 0.4,-0.2 -1.4,2.6 -1,1.5 -1.2,2.5 -0.6,1.8 -1,1.4 -1.7,4.6 -0.4,1.2 -0.4,2.1 0,1.3 -0.2,0.6 0,0.1 0.2,0.4 -0.2,0.4 -0.4,0.5 0.6,0 0,0.4 -0.8,0 0,-0.7 -0.8,-0.2 0.8,-0.4 -0.4,-0.9 -2,-0.6 -1.3,0.6 -2.2,2.2 0.6,0 1.6,0.6 0,0.5 -1.4,-0.5 -1.6,0.9 -0.3,0.8 0.3,0.3 -0.3,0 0,0.4 0.6,0 0,0.2 1.7,0 0.6,0 0.6,0.3 0.4,-0.3 0.4,0.3 0,0.4 -1,2.1 -0.6,0.2 0,1.7 -1.5,0.1 1.1,1.5 0.4,0.4 0.2,0.3 0.4,-0.7 2.1,0.7 0.2,0.2 0.4,0.4 0,0.9 1.6,0.8 0,0.9 0.6,0.8 0.6,1.1 0.8,0.6 -0.8,0.9 0.8,0.5 0.7,0.4 0,0.5 -1.5,0.4 -0.2,0.6 -0.8,0.3 0,1.6 -0.8,0.9 0.6,0.5 1.6,0 1.1,-0.3 1.6,0.5 1.2,1 0.4,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path544"
         inkscape:connector-curvature="0"
         d="m 444.17474,409.3 -1,1.5 -0.6,0.4 -0.8,0.5 -1.7,1 0,0.5 -1.2,1 -1,1.5 0,0.1 -1,1.3 0,0.4 -1,1.1 -0.2,0.6 -0.9,0.3 -0.8,2.1 0.2,0.4 -2,0.9 -0.3,0.8 -0.4,0 -1,1.3 -0.2,0 -2.5,1.8 -0.1,0.8 0,0.7 1,0 1.6,-0.7 1.2,0 1.5,-1.2 0,-0.5 2.3,-1.5 1,-0.7 1,-1.2 0.6,0.4 0.4,-0.4 0.2,-0.9 0.6,0 0.4,-0.6 0.6,-0.4 1,-0.5 0.5,-0.3 -0.9,-0.3 0.9,0 0.6,0.3 0.4,-0.3 0.2,-0.5 -0.6,-0.4 0.6,0 -0.6,-0.5 0.6,-0.4 1,-0.2 0.4,-0.9 0.7,-0.9 -0.4,-0.6 0.6,0.2 0.7,0 0.7,-1 0.4,-0.1 -0.8,-1 0.4,-0.4 0,-0.8 -0.4,-2.3 -1,0.6 -0.2,-0.6 -0.7,0.2 0,-0.6 z" /><g
         id="g546"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path548"
           inkscape:connector-curvature="0"
           d="m 445.8,383.7 -1,1.5 -0.6,0.4 -0.8,0.5 -1.7,1 0,0.5 -1.2,1 -1,1.5 0,0.1 -1,1.3 0,0.4 -1,1.1 -0.2,0.6 -0.9,0.3 -0.8,2.1 0.2,0.4 -2,0.9 -0.3,0.8 -0.4,0 -1,1.3 -0.2,0 -2.5,1.8 -0.1,0.8 0,0.7 1,0 1.6,-0.7 1.2,0 1.5,-1.2 0,-0.5 2.3,-1.5 1,-0.7 1,-1.2 0.6,0.4 0.4,-0.4 0.2,-0.9 0.6,0 0.4,-0.6 0.6,-0.4 1,-0.5 0.5,-0.3 -0.9,-0.3 0.9,0 0.6,0.3 0.4,-0.3 0.2,-0.5 -0.6,-0.4 0.6,0 -0.6,-0.5 0.6,-0.4 1,-0.2 0.4,-0.9 0.7,-0.9 -0.4,-0.6 0.6,0.2 0.7,0 0.7,-1 0.4,-0.1 -0.8,-1 0.4,-0.4 0,-0.8 -0.4,-2.3 -1,0.6 -0.2,-0.6 -0.7,0.2 0,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path550"
         inkscape:connector-curvature="0"
         d="m 507.97474,414.4 -0.3,-10.9 -1.7,1.9 -0.9,-0.4 -0.8,0.4 0,0.9 -0.6,1.1 0,0.6 -1.1,1.7 -1.2,0.8 -2.2,0.9 -1.4,0.3 -0.6,0.6 0.2,0 0,0.4 -0.2,0 -1.1,0 0,0.2 -0.4,-0.2 -0.6,0.5 0.4,0.6 0,0.4 -0.4,-0.4 -0.2,-0.6 -0.4,-0.5 -0.6,-0.4 -0.4,0.6 0.4,0.7 -0.4,0.2 -0.4,0.4 -0.9,0 0.7,0.2 0.6,0 1,0.4 0.4,0 0.2,0 0.4,0.3 -0.4,0.2 -1,0 -1.2,-0.2 -0.4,0.8 0,0.4 -0.7,0.3 -1.2,0.2 0,0.4 0.3,0.5 0.9,0.4 1.3,0.6 1,-0.6 1.4,-0.4 0.2,-0.1 0.4,-1 -0.4,-0.7 0.8,-1.5 0,0.4 0.3,0 0.4,-0.4 0,-0.2 0,-0.4 0.6,-0.6 0.6,0.4 1.4,1.5 1.6,0.2 -0.4,0.6 1.5,0.7 2.6,-1.3 1.7,-0.2 1.2,-0.7 z" /><g
         id="g552"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path554"
           inkscape:connector-curvature="0"
           d="m 509.6,388.8 -0.3,-10.9 -1.7,1.9 -0.9,-0.4 -0.8,0.4 0,0.9 -0.6,1.1 0,0.6 -1.1,1.7 -1.2,0.8 -2.2,0.9 -1.4,0.3 -0.6,0.6 0.2,0 0,0.4 -0.2,0 -1.1,0 0,0.2 -0.4,-0.2 -0.6,0.5 0.4,0.6 0,0.4 -0.4,-0.4 -0.2,-0.6 -0.4,-0.5 -0.6,-0.4 -0.4,0.6 0.4,0.7 -0.4,0.2 -0.4,0.4 -0.9,0 0.7,0.2 0.6,0 1,0.4 0.4,0 0.2,0 0.4,0.3 -0.4,0.2 -1,0 -1.2,-0.2 -0.4,0.8 0,0.4 -0.7,0.3 -1.2,0.2 0,0.4 0.3,0.5 0.9,0.4 1.3,0.6 1,-0.6 1.4,-0.4 0.2,-0.1 0.4,-1 -0.4,-0.7 0.8,-1.5 0,0.4 0.3,0 0.4,-0.4 0,-0.2 0,-0.4 0.6,-0.6 0.6,0.4 1.4,1.5 1.6,0.2 -0.4,0.6 1.5,0.7 2.6,-1.3 1.7,-0.2 1.2,-0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path556"
         inkscape:connector-curvature="0"
         d="m 470.87474,409.6 -0.8,0.3 0,0.5 0.4,0.9 -0.4,1.3 0,0.6 -1,0 0,1 -0.2,0.1 0.6,0.4 0.4,0.6 0.2,0.5 -0.2,0.8 0.2,0.9 0.8,0.2 0.2,1.3 0,0.2 0.4,1.3 0.2,0.2 0.4,-0.6 0.5,0.4 0.1,0.6 1.1,0 0,-0.4 1.4,0 1.6,-0.2 1.9,0 1,1 0.4,0.5 0.2,-0.4 -0.2,-0.9 -0.6,-0.6 -0.4,-0.5 -1.7,0 -0.6,0 -1.6,0 -1,0.2 -0.8,0 -0.8,0 -0.9,0 -0.2,-0.8 0,-1.3 0.6,-0.2 0.5,-0.7 0.6,0 0.6,0.9 0.6,-0.2 0.4,0 0.4,0.2 0.9,0 0.4,0.4 0.3,0 0.6,-0.4 -0.4,-0.2 -0.5,0 -1.1,-0.7 -1.2,-1 -0.8,0 2,-1.6 0,-0.6 -0.2,-0.8 0.6,0 0,-0.6 0.7,0 0,-0.5 -1.1,0 -0.6,-0.4 0,-0.2 -1,0 0.4,1.1 -1.5,1 0,0.4 0.5,0.6 -0.5,0.5 -0.1,-0.4 -0.9,-0.1 0,-0.4 0,-1 0,-1.1 0,-0.9 0,-1.2 -0.6,0.3 -0.2,-0.3 z" /><g
         id="g558"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path560"
           inkscape:connector-curvature="0"
           d="m 472.5,384 -0.8,0.3 0,0.5 0.4,0.9 -0.4,1.3 0,0.6 -1,0 0,1 -0.2,0.1 0.6,0.4 0.4,0.6 0.2,0.5 -0.2,0.8 0.2,0.9 0.8,0.2 0.2,1.3 0,0.2 0.4,1.3 0.2,0.2 0.4,-0.6 0.5,0.4 0.1,0.6 1.1,0 0,-0.4 1.4,0 1.6,-0.2 1.9,0 1,1 0.4,0.5 0.2,-0.4 -0.2,-0.9 -0.6,-0.6 -0.4,-0.5 -1.7,0 -0.6,0 -1.6,0 -1,0.2 -0.8,0 -0.8,0 -0.9,0 -0.2,-0.8 0,-1.3 0.6,-0.2 0.5,-0.7 0.6,0 0.6,0.9 0.6,-0.2 0.4,0 0.4,0.2 0.9,0 0.4,0.4 0.3,0 0.6,-0.4 -0.4,-0.2 -0.5,0 -1.1,-0.7 -1.2,-1 -0.8,0 2,-1.6 0,-0.6 -0.2,-0.8 0.6,0 0,-0.6 0.7,0 0,-0.5 -1.1,0 -0.6,-0.4 0,-0.2 -1,0 0.4,1.1 -1.5,1 0,0.4 0.5,0.6 -0.5,0.5 -0.1,-0.4 -0.9,-0.1 0,-0.4 0,-1 0,-1.1 0,-0.9 0,-1.2 -0.6,0.3 -0.2,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path562"
         inkscape:connector-curvature="0"
         d="m 461.37474,404.5 -1.1,0.3 -1.4,0.6 -0.8,-0.4 -1,0.4 -0.9,0 -1.2,0.2 -1,0.4 -2,0.5 -1.2,-0.1 -1.7,0.5 -2.1,0.4 0.4,0.2 -1.4,0.3 -1,0.2 0.4,0 0.4,0 0.6,0.8 0.4,0.5 1.7,-0.3 0,0.3 0.6,0 0.6,-0.3 0.4,-0.2 0.7,0.2 0.4,-1 2.6,-0.2 0.6,0 0.2,0.6 0.8,0 0.2,-0.4 0.4,0 1.1,0 0.6,-0.2 0.6,-0.9 1.1,-0.5 0.4,0.1 0.8,0 0.8,-0.1 -0.4,-0.4 0,-0.6 0.4,-0.6 0,-0.3 z" /><g
         id="g564"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path566"
           inkscape:connector-curvature="0"
           d="m 463,378.9 -1.1,0.3 -1.4,0.6 -0.8,-0.4 -1,0.4 -0.9,0 -1.2,0.2 -1,0.4 -2,0.5 -1.2,-0.1 -1.7,0.5 -2.1,0.4 0.4,0.2 -1.4,0.3 -1,0.2 0.4,0 0.4,0 0.6,0.8 0.4,0.5 1.7,-0.3 0,0.3 0.6,0 0.6,-0.3 0.4,-0.2 0.7,0.2 0.4,-1 2.6,-0.2 0.6,0 0.2,0.6 0.8,0 0.2,-0.4 0.4,0 1.1,0 0.6,-0.2 0.6,-0.9 1.1,-0.5 0.4,0.1 0.8,0 0.8,-0.1 -0.4,-0.4 0,-0.6 0.4,-0.6 0,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path568"
         inkscape:connector-curvature="0"
         d="m 477.57474,401.7 -0.8,0 0.2,0.7 0.4,0.6 1,0.6 1,0.3 0.6,0.6 1.7,0.6 2,0 -0.8,-0.6 -0.3,0 -0.8,-0.4 -2.4,-1.1 -1,-1 -0.4,0 -0.4,-0.3 z" /><g
         id="g570"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path572"
           inkscape:connector-curvature="0"
           d="m 479.2,376.1 -0.8,0 0.2,0.7 0.4,0.6 1,0.6 1,0.3 0.6,0.6 1.7,0.6 2,0 -0.8,-0.6 -0.3,0 -0.8,-0.4 -2.4,-1.1 -1,-1 -0.4,0 -0.4,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path574"
         inkscape:connector-curvature="0"
         d="m 485.57474,417.5 -0.6,0.8 -0.4,0.9 0,0.4 -0.3,0.5 0,0.6 0.3,0.9 0.8,0.9 -0.4,-0.5 0.4,-1 -0.8,-0.3 0.4,-0.2 0.4,0.2 0.2,0 0,0.3 1.1,0.4 0,-0.7 -1.1,-0.6 1.5,-0.9 -1.7,0.4 0,-0.4 0.2,-1.7 z" /><g
         id="g576"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path578"
           inkscape:connector-curvature="0"
           d="m 487.2,391.9 -0.6,0.8 -0.4,0.9 0,0.4 -0.3,0.5 0,0.6 0.3,0.9 0.8,0.9 -0.4,-0.5 0.4,-1 -0.8,-0.3 0.4,-0.2 0.4,0.2 0.2,0 0,0.3 1.1,0.4 0,-0.7 -1.1,-0.6 1.5,-0.9 -1.7,0.4 0,-0.4 0.2,-1.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path580"
         inkscape:connector-curvature="0"
         d="m 490.27474,412.6 -1.6,0.6 -0.7,0.4 0,-0.4 -1,0 0,0.4 -0.8,-0.4 -0.6,0.4 -0.2,-0.4 -0.4,0.4 0.6,0.6 1.4,0 0.7,0 0.3,0 0.7,0 0.5,0 0.7,-0.6 0.4,-0.4 0,-0.6 z" /><g
         id="g582"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path584"
           inkscape:connector-curvature="0"
           d="m 491.9,387 -1.6,0.6 -0.7,0.4 0,-0.4 -1,0 0,0.4 -0.8,-0.4 -0.6,0.4 -0.2,-0.4 -0.4,0.4 0.6,0.6 1.4,0 0.7,0 0.3,0 0.7,0 0.5,0 0.7,-0.6 0.4,-0.4 0,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path586"
         inkscape:connector-curvature="0"
         d="m 473.17474,404 -1.1,0 -0.4,0.4 -1.2,0 0,0.4 0.4,0.2 0.8,0.4 1,-0.4 0.7,-0.2 0.4,0.2 1.4,-0.2 0.2,0.2 0.4,0 0,0.4 0.2,0.2 0.4,-0.2 -0.6,-0.6 -1.4,-0.4 -0.2,-0.4 -0.8,0.4 -0.2,-0.4 z" /><g
         id="g588"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path590"
           inkscape:connector-curvature="0"
           d="m 474.8,378.4 -1.1,0 -0.4,0.4 -1.2,0 0,0.4 0.4,0.2 0.8,0.4 1,-0.4 0.7,-0.2 0.4,0.2 1.4,-0.2 0.2,0.2 0.4,0 0,0.4 0.2,0.2 0.4,-0.2 -0.6,-0.6 -1.4,-0.4 -0.2,-0.4 -0.8,0.4 -0.2,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path592"
         inkscape:connector-curvature="0"
         d="m 465.17474,404 0,0.8 0.5,0.2 0.9,0 0.2,-0.2 0.4,0 0.2,0.2 -0.6,0.4 0.6,0.2 0.4,-0.2 0.4,0 0.6,-0.4 0.3,0.4 0.4,-1 -1.3,0 0,0.4 -0.4,0 -0.4,-0.8 -0.6,0 -1.6,0 z" /><g
         id="g594"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path596"
           inkscape:connector-curvature="0"
           d="m 466.8,378.4 0,0.8 0.5,0.2 0.9,0 0.2,-0.2 0.4,0 0.2,0.2 -0.6,0.4 0.6,0.2 0.4,-0.2 0.4,0 0.6,-0.4 0.3,0.4 0.4,-1 -1.3,0 0,0.4 -0.4,0 -0.4,-0.8 -0.6,0 -1.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path598"
         inkscape:connector-curvature="0"
         d="m 448.07474,414.2 -1.2,0.2 -0.4,0.9 -0.7,0.5 -0.8,0 0.8,0 0,0.8 0.3,0 0,-0.3 0.4,0 0,0.3 0.4,0 0.2,-0.3 0,-1.2 1,-0.3 -0.4,-0.4 0.4,-0.2 z" /><g
         id="g600"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path602"
           inkscape:connector-curvature="0"
           d="m 449.7,388.6 -1.2,0.2 -0.4,0.9 -0.7,0.5 -0.8,0 0.8,0 0,0.8 0.3,0 0,-0.3 0.4,0 0,0.3 0.4,0 0.2,-0.3 0,-1.2 1,-0.3 -0.4,-0.4 0.4,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path604"
         inkscape:connector-curvature="0"
         d="m 501.67474,404.8 0.8,1.1 1.3,0.6 0.4,-0.1 0,-0.8 -0.7,-0.5 -1.8,-0.3 z" /><g
         id="g606"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path608"
           inkscape:connector-curvature="0"
           d="m 503.3,379.2 0.8,1.1 1.3,0.6 0.4,-0.1 0,-0.8 -0.7,-0.5 -1.8,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path610"
         inkscape:connector-curvature="0"
         d="m 471.47474,401.7 -0.6,0.7 -1.1,0.6 -0.8,0 0,0.3 1.1,0 0.8,0.2 0.2,-0.5 0.6,0 0.4,-0.6 -0.4,-0.3 -0.2,-0.4 z" /><g
         id="g612"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path614"
           inkscape:connector-curvature="0"
           d="m 473.1,376.1 -0.6,0.7 -1.1,0.6 -0.8,0 0,0.3 1.1,0 0.8,0.2 0.2,-0.5 0.6,0 0.4,-0.6 -0.4,-0.3 -0.2,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path616"
         inkscape:connector-curvature="0"
         d="m 482.97474,412.6 -1.1,1.1 0,0.1 1.8,0 0.3,-0.9 -1,-0.3 z" /><g
         id="g618"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path620"
           inkscape:connector-curvature="0"
           d="m 484.6,387 -1.1,1.1 0,0.1 1.8,0 0.3,-0.9 -1,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path622"
         inkscape:connector-curvature="0"
         d="m 462.57474,404.8 -0.6,0.3 -0.7,0 0,0.5 0.7,-0.2 0.6,0.2 1,-0.5 -1,0 0,-0.3 z" /><g
         id="g624"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path626"
           inkscape:connector-curvature="0"
           d="m 464.2,379.2 -0.6,0.3 -0.7,0 0,0.5 0.7,-0.2 0.6,0.2 1,-0.5 -1,0 0,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path628"
         inkscape:connector-curvature="0"
         d="m 459.67474,407.3 -1,0.2 0,0.4 1.8,0 -0.2,-0.4 -0.4,0 -0.2,-0.2 z" /><g
         id="g630"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path632"
           inkscape:connector-curvature="0"
           d="m 461.3,381.7 -1,0.2 0,0.4 1.8,0 -0.2,-0.4 -0.4,0 -0.2,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path634"
         inkscape:connector-curvature="0"
         d="m 464.57474,404 -1,0.4 0,0.4 0.6,-0.4 -0.2,0.6 0.2,0.4 0.6,0 -0.2,-1.4 z" /><g
         id="g636"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path638"
           inkscape:connector-curvature="0"
           d="m 466.2,378.4 -1,0.4 0,0.4 0.6,-0.4 -0.2,0.6 0.2,0.4 0.6,0 -0.2,-1.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path640"
         inkscape:connector-curvature="0"
         d="m 449.27474,413.6 0.4,1.1 1.1,0 0,-1.1 -0.6,0.5 -0.9,-0.5 z" /><g
         id="g642"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path644"
           inkscape:connector-curvature="0"
           d="m 450.9,388 0.4,1.1 1.1,0 0,-1.1 -0.6,0.5 -0.9,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path646"
         inkscape:connector-curvature="0"
         d="m 432.17474,419.7 -0.8,1.7 0.2,0.1 1,-0.9 -0.4,-0.9 z" /><g
         id="g648"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path650"
           inkscape:connector-curvature="0"
           d="m 433.8,394.1 -0.8,1.7 0.2,0.1 1,-0.9 -0.4,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path652"
         inkscape:connector-curvature="0"
         d="m 434.57474,416.2 -0.6,1 0,0.3 0.2,0 0.6,-1 -0.2,-0.3 z" /><g
         id="g654"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path656"
           inkscape:connector-curvature="0"
           d="m 436.2,390.6 -0.6,1 0,0.3 0.2,0 0.6,-1 -0.2,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path658"
         inkscape:connector-curvature="0"
         d="m 490.67474,405.6 -0.4,0.3 1,1.4 0.2,-0.8 -0.8,-0.9 z" /><g
         id="g660"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path662"
           inkscape:connector-curvature="0"
           d="m 492.3,380 -0.4,0.3 1,1.4 0.2,-0.8 -0.8,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path664"
         inkscape:connector-curvature="0"
         d="m 429.97474,422.9 -1.3,0.7 0,0.4 1.3,-1 0,-0.1 z" /><g
         id="g666"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path668"
           inkscape:connector-curvature="0"
           d="m 431.6,397.3 -1.3,0.7 0,0.4 1.3,-1 0,-0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path670"
         inkscape:connector-curvature="0"
         d="m 450.27474,424.8 -0.2,0.6 0.2,0.4 0.5,-0.4 0.4,-0.6 -0.9,0 z" /><g
         id="g672"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path674"
           inkscape:connector-curvature="0"
           d="m 451.9,399.2 -0.2,0.6 0.2,0.4 0.5,-0.4 0.4,-0.6 -0.9,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path676"
         inkscape:connector-curvature="0"
         d="m 442.17474,421.4 0.4,-0.4 0.2,0.4 -0.2,-0.4 -0.4,0.4 z" /><g
         id="g678"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path680"
           inkscape:connector-curvature="0"
           d="m 443.8,395.8 0.4,-0.4 0.2,0.4 -0.2,-0.4 -0.4,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path682"
         inkscape:connector-curvature="0"
         d="m 331.87474,482.6 0.8,0.5 1.1,-0.2 1.6,-0.3 0.8,0 0.2,-0.6 0.4,-0.6 0.6,-0.9 0.7,0 0.8,0 0.2,-0.4 -0.6,-0.2 0.4,-0.9 -0.8,-0.4 0,-0.6 -0.3,-0.3 0,-0.9 1.1,-0.8 0.2,-0.7 1.4,-0.8 0.9,-0.4 0.7,-1.3 0,-0.2 0,-0.9 0.6,-0.4 0,-0.7 0.7,-0.9 -0.2,0 -0.9,0 -0.9,0 -0.3,-1 -0.4,-0.6 -3.3,0 -2,0.8 0,1.3 -1,0.4 -0.2,0.2 -1.5,0.5 -3.4,1.9 -1.6,0.4 -1.1,2.1 2.3,1.1 1.1,0.9 0.5,0.9 0,1.3 0,0.8 0,0.7 1,0.6 0.4,0.6 z" /><g
         id="g684"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path686"
           inkscape:connector-curvature="0"
           d="m 333.5,457 0.8,0.5 1.1,-0.2 1.6,-0.3 0.8,0 0.2,-0.6 0.4,-0.6 0.6,-0.9 0.7,0 0.8,0 0.2,-0.4 -0.6,-0.2 0.4,-0.9 -0.8,-0.4 0,-0.6 -0.3,-0.3 0,-0.9 1.1,-0.8 0.2,-0.7 1.4,-0.8 0.9,-0.4 0.7,-1.3 0,-0.2 0,-0.9 0.6,-0.4 0,-0.7 0.7,-0.9 -0.2,0 -0.9,0 -0.9,0 -0.3,-1 -0.4,-0.6 -3.3,0 -2,0.8 0,1.3 -1,0.4 -0.2,0.2 -1.5,0.5 -3.4,1.9 -1.6,0.4 -1.1,2.1 2.3,1.1 1.1,0.9 0.5,0.9 0,1.3 0,0.8 0,0.7 1,0.6 0.4,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path688"
         inkscape:connector-curvature="0"
         d="m 340.07474,486.3 2.2,-0.9 -0.2,-0.4 1.2,-1.5 1.5,-0.4 1.6,-0.7 2.6,-0.4 1.9,0.6 0.4,0 -0.6,-0.2 0,-0.4 1,0.4 -0.4,0.7 1.6,0.4 1,1.5 1.2,0 1.7,0.4 0.3,-0.8 2.3,-0.6 4.3,-1.6 0.4,-1.9 0,-1.3 -0.4,-1.1 0.4,-0.4 -0.6,-0.1 0.2,-1 0.4,-0.4 -0.4,-0.5 0.8,-1.5 0,-1.5 1.7,-0.3 0.1,-1 -0.6,-0.7 -0.4,-1.3 1.4,-1.9 1.1,-0.2 1,-0.9 0.2,-1.3 0.6,0 0,-0.7 -0.2,0 0,-0.4 -0.6,0 -0.8,-0.6 -0.6,0 -0.5,-1.3 0,-0.9 -0.7,0.3 -1.7,0.4 -1.8,0 -1.6,0.2 -1.9,0.4 -0.8,1.5 -0.8,0.9 -1,-0.3 -0.7,-0.2 -0.8,-0.4 -0.6,-0.4 -1.2,0.4 -1.4,0.6 -1,0.5 -0.7,0.7 -1.2,0.2 -1,1.5 -0.4,0.7 -0.6,0.2 -0.6,1.4 -0.4,0.5 -0.6,-0.5 -1,0.5 0,0.4 -0.5,0 0,-0.9 -0.6,0 -0.6,0.9 0,0.7 -0.6,0.4 0,0.9 0,0.2 -0.8,1.3 -0.8,0.3 -1.4,0.8 -0.2,0.8 -1.1,0.7 0,0.9 0.3,0.4 0,0.5 0.8,0.4 -0.4,1 0.6,0.2 -0.2,0.3 -0.8,0 -0.7,0 -0.6,0.9 -0.4,0.6 -0.2,0.6 -0.4,0.9 -0.4,0.9 0,0.2 -0.2,1 -0.8,1.4 0.8,0.8 1.2,-1.5 2.5,-0.4 1.2,0.4 z" /><g
         id="g690"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path692"
           inkscape:connector-curvature="0"
           d="m 341.7,460.7 2.2,-0.9 -0.2,-0.4 1.2,-1.5 1.5,-0.4 1.6,-0.7 2.6,-0.4 1.9,0.6 0.4,0 -0.6,-0.2 0,-0.4 1,0.4 -0.4,0.7 1.6,0.4 1,1.5 1.2,0 1.7,0.4 0.3,-0.8 2.3,-0.6 4.3,-1.6 0.4,-1.9 0,-1.3 -0.4,-1.1 0.4,-0.4 -0.6,-0.1 0.2,-1 0.4,-0.4 -0.4,-0.5 0.8,-1.5 0,-1.5 1.7,-0.3 0.1,-1 -0.6,-0.7 -0.4,-1.3 1.4,-1.9 1.1,-0.2 1,-0.9 0.2,-1.3 0.6,0 0,-0.7 -0.2,0 0,-0.4 -0.6,0 -0.8,-0.6 -0.6,0 -0.5,-1.3 0,-0.9 -0.7,0.3 -1.7,0.4 -1.8,0 -1.6,0.2 -1.9,0.4 -0.8,1.5 -0.8,0.9 -1,-0.3 -0.7,-0.2 -0.8,-0.4 -0.6,-0.4 -1.2,0.4 -1.4,0.6 -1,0.5 -0.7,0.7 -1.2,0.2 -1,1.5 -0.4,0.7 -0.6,0.2 -0.6,1.4 -0.4,0.5 -0.6,-0.5 -1,0.5 0,0.4 -0.5,0 0,-0.9 -0.6,0 -0.6,0.9 0,0.7 -0.6,0.4 0,0.9 0,0.2 -0.8,1.3 -0.8,0.3 -1.4,0.8 -0.2,0.8 -1.1,0.7 0,0.9 0.3,0.4 0,0.5 0.8,0.4 -0.4,1 0.6,0.2 -0.2,0.3 -0.8,0 -0.7,0 -0.6,0.9 -0.4,0.6 -0.2,0.6 -0.4,0.9 -0.4,0.9 0,0.2 -0.2,1 -0.8,1.4 0.8,0.8 1.2,-1.5 2.5,-0.4 1.2,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path694"
         inkscape:connector-curvature="0"
         d="m 320.27474,475.1 0.6,0 0,0.2 0.6,0 0,-0.2 -0.6,-0.9 0,-0.4 -0.1,0 0,-0.6 0.1,-0.5 0,-1.3 0,-1.1 0,-1.4 -0.6,-0.6 0,1.4 -0.3,0.9 -0.7,0.8 0.4,0.7 -0.4,0.6 0.4,0.1 0.3,1 0.3,1.3 z" /><g
         id="g696"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path698"
           inkscape:connector-curvature="0"
           d="m 321.9,449.5 0.6,0 0,0.2 0.6,0 0,-0.2 -0.6,-0.9 0,-0.4 -0.1,0 0,-0.6 0.1,-0.5 0,-1.3 0,-1.1 0,-1.4 -0.6,-0.6 0,1.4 -0.3,0.9 -0.7,0.8 0.4,0.7 -0.4,0.6 0.4,0.1 0.3,1 0.3,1.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path700"
         inkscape:connector-curvature="0"
         d="m 492.27474,480.1 -0.8,0.4 -0.8,0.6 0,0.3 0,0.6 0.2,0.5 -0.2,0.4 -1.1,0 -0.8,-0.4 -1.6,-0.5 -1.2,-0.6 0,0.2 -0.4,0.4 0,-0.4 0,-0.5 0,-0.4 -0.7,0.4 -0.6,0 -1,-0.4 0,0.9 0.6,0.4 0.4,0.3 0.6,0.2 0.4,1 0.7,0.3 -0.4,0.6 1,0.2 0,-0.2 0.4,0 0.2,0 2.4,0.6 0.3,0.4 0.4,-0.4 0.4,-0.4 0.2,0.4 0.6,0.4 -0.6,0.5 0.4,0.5 0.6,1.4 -0.4,1.1 1,0.6 0,-0.6 -0.2,-0.4 0,-0.2 0.2,-0.5 0.4,0 0.2,0.2 1.4,0.5 0.6,1.9 0.5,0 0,1.5 0.1,1.5 -0.1,1.8 0,-0.4 -0.5,0.4 0,0.4 0,1.1 0.5,0.6 -0.5,0.7 0.6,0.2 0.5,-0.9 0.4,0.3 0.2,0 0,0.6 -0.6,0 0,0.9 0.4,-0.3 0.6,0 0.2,-1.5 0.8,-0.6 0.6,-1.1 0.4,-0.9 0.2,0 0,-1.3 -0.6,-0.6 0.6,-1.1 -0.6,0 -0.4,-0.4 0,-0.9 0.8,-2.1 0,-2 0.6,-0.8 -0.6,-0.1 0,-1 -1,-0.6 0.2,1.2 -0.6,0.4 0,-0.8 0,-0.2 -0.4,0.2 -0.2,-0.2 0,-0.9 -0.4,-0.6 -0.6,1.1 -0.5,-0.2 -0.1,-0.9 -0.7,0.2 -1,-0.6 0.2,0.6 -1,0 0,0.7 -0.2,0 0,-0.9 0.6,-0.4 0,-0.5 -1,-0.4 -0.2,-1.5 z" /><g
         id="g702"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path704"
           inkscape:connector-curvature="0"
           d="m 493.9,454.5 -0.8,0.4 -0.8,0.6 0,0.3 0,0.6 0.2,0.5 -0.2,0.4 -1.1,0 -0.8,-0.4 -1.6,-0.5 -1.2,-0.6 0,0.2 -0.4,0.4 0,-0.4 0,-0.5 0,-0.4 -0.7,0.4 -0.6,0 -1,-0.4 0,0.9 0.6,0.4 0.4,0.3 0.6,0.2 0.4,1 0.7,0.3 -0.4,0.6 1,0.2 0,-0.2 0.4,0 0.2,0 2.4,0.6 0.3,0.4 0.4,-0.4 0.4,-0.4 0.2,0.4 0.6,0.4 -0.6,0.5 0.4,0.5 0.6,1.4 -0.4,1.1 1,0.6 0,-0.6 -0.2,-0.4 0,-0.2 0.2,-0.5 0.4,0 0.2,0.2 1.4,0.5 0.6,1.9 0.5,0 0,1.5 0.1,1.5 -0.1,1.8 0,-0.4 -0.5,0.4 0,0.4 0,1.1 0.5,0.6 -0.5,0.7 0.6,0.2 0.5,-0.9 0.4,0.3 0.2,0 0,0.6 -0.6,0 0,0.9 0.4,-0.3 0.6,0 0.2,-1.5 0.8,-0.6 0.6,-1.1 0.4,-0.9 0.2,0 0,-1.3 -0.6,-0.6 0.6,-1.1 -0.6,0 -0.4,-0.4 0,-0.9 0.8,-2.1 0,-2 0.6,-0.8 -0.6,-0.1 0,-1 -1,-0.6 0.2,1.2 -0.6,0.4 0,-0.8 0,-0.2 -0.4,0.2 -0.2,-0.2 0,-0.9 -0.4,-0.6 -0.6,1.1 -0.5,-0.2 -0.1,-0.9 -0.7,0.2 -1,-0.6 0.2,0.6 -1,0 0,0.7 -0.2,0 0,-0.9 0.6,-0.4 0,-0.5 -1,-0.4 -0.2,-1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path706"
         inkscape:connector-curvature="0"
         d="m 494.87474,498.6 -0.3,0.4 0,0.9 -1.1,0.6 0,0.9 0.6,1 -0.6,0.9 0.4,0.2 0.7,-0.2 0.9,0 -0.4,1.1 0.4,0 -0.4,1.3 -0.2,1.7 -0.8,1.3 0.5,0.2 0.9,-0.5 1.7,-1.6 1.6,-0.8 0.6,0 0.6,-0.6 0.9,0 0.2,1.1 0.4,-0.2 0,-1.3 0.5,-1.1 0.7,0.1 0,-0.1 -0.7,-0.4 -0.4,-0.2 -1.2,-0.4 -0.6,-0.5 0,-1.3 0,-0.6 -2.6,0.9 -1.1,0.4 -0.4,-0.4 -0.2,-0.6 -0.4,0.6 -0.5,-0.3 0,-0.3 0.9,-0.3 0.2,-0.6 0.4,0 -0.4,-0.5 -0.2,0 -0.4,-0.8 -0.2,0 z" /><g
         id="g708"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path710"
           inkscape:connector-curvature="0"
           d="m 496.5,473 -0.3,0.4 0,0.9 -1.1,0.6 0,0.9 0.6,1 -0.6,0.9 0.4,0.2 0.7,-0.2 0.9,0 -0.4,1.1 0.4,0 -0.4,1.3 -0.2,1.7 -0.8,1.3 0.5,0.2 0.9,-0.5 1.7,-1.6 1.6,-0.8 0.6,0 0.6,-0.6 0.9,0 0.2,1.1 0.4,-0.2 0,-1.3 0.5,-1.1 0.7,0.1 0,-0.1 -0.7,-0.4 -0.4,-0.2 -1.2,-0.4 -0.6,-0.5 0,-1.3 0,-0.6 -2.6,0.9 -1.1,0.4 -0.4,-0.4 -0.2,-0.6 -0.4,0.6 -0.5,-0.3 0,-0.3 0.9,-0.3 0.2,-0.6 0.4,0 -0.4,-0.5 -0.2,0 -0.4,-0.8 -0.2,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path712"
         inkscape:connector-curvature="0"
         d="m 481.07474,464.5 0,0.3 0.2,0.7 0,0.3 0.5,0 0.2,0.2 -0.7,-1.2 0.5,-0.3 -0.7,0 z" /><g
         id="g714"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path716"
           inkscape:connector-curvature="0"
           d="m 482.7,438.9 0,0.3 0.2,0.7 0,0.3 0.5,0 0.2,0.2 -0.7,-1.2 0.5,-0.3 -0.7,0 z" /></g><g
         id="g720"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path722"
           inkscape:connector-curvature="0"
           d="m 485,443.1 -0.4,0.3 1,0.4 -0.3,-0.4 -0.3,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path724"
         inkscape:connector-curvature="0"
         d="m 320.87474,474.2 1,0 0.4,-0.4 0.2,0 0.8,0 1.7,0.8 1.6,1.1 1.1,-2.1 -1.5,-0.7 -2.3,-0.5 0,-0.3 1.7,-1.5 -0.4,-0.3 -0.2,-0.6 -0.8,-0.4 -0.3,0 -1.4,-1.1 -2.2,0.2 0.6,0.5 0,1.4 0,1.1 0,1.3 -0.2,0.6 0,0.5 0.2,0 0,0.4 z" /><g
         id="g726"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path728"
           inkscape:connector-curvature="0"
           d="m 322.5,448.6 1,0 0.4,-0.4 0.2,0 0.8,0 1.7,0.8 1.6,1.1 1.1,-2.1 -1.5,-0.7 -2.3,-0.5 0,-0.3 1.7,-1.5 -0.4,-0.3 -0.2,-0.6 -0.8,-0.4 -0.3,0 -1.4,-1.1 -2.2,0.2 0.6,0.5 0,1.4 0,1.1 0,1.3 -0.2,0.6 0,0.5 0.2,0 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path730"
         inkscape:connector-curvature="0"
         d="m 444.47474,442.9 0,-0.5 0.9,0 0.4,-0.4 0.3,0.4 0,0.7 0.8,0.4 0.6,-0.6 1,0 0,-0.5 0.2,-1.5 0,-1.3 -0.6,-0.4 -0.6,-0.2 -1,-0.4 -0.7,-0.1 0.3,-0.8 0,-0.5 -1.1,-0.2 -1.6,-0.8 -0.8,0.4 -0.4,0 0,0.6 -0.4,0.3 -0.2,-0.3 -0.9,0.5 0,0.8 -0.8,1.6 -0.4,1 1,0.6 0,1.2 1.3,0.2 1.4,-0.2 0.2,0.2 0.8,-0.2 0.3,0 z" /><g
         id="g732"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path734"
           inkscape:connector-curvature="0"
           d="m 446.1,417.3 0,-0.5 0.9,0 0.4,-0.4 0.3,0.4 0,0.7 0.8,0.4 0.6,-0.6 1,0 0,-0.5 0.2,-1.5 0,-1.3 -0.6,-0.4 -0.6,-0.2 -1,-0.4 -0.7,-0.1 0.3,-0.8 0,-0.5 -1.1,-0.2 -1.6,-0.8 -0.8,0.4 -0.4,0 0,0.6 -0.4,0.3 -0.2,-0.3 -0.9,0.5 0,0.8 -0.8,1.6 -0.4,1 1,0.6 0,1.2 1.3,0.2 1.4,-0.2 0.2,0.2 0.8,-0.2 0.3,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path736"
         inkscape:connector-curvature="0"
         d="m 348.17474,460.3 -0.3,1.6 0.3,0.1 0.4,0.6 0.7,-0.6 0,-0.9 0,-0.8 -0.2,-0.3 -0.5,0.3 -0.4,0 z" /><g
         id="g738"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path740"
           inkscape:connector-curvature="0"
           d="m 349.8,434.7 -0.3,1.6 0.3,0.1 0.4,0.6 0.7,-0.6 0,-0.9 0,-0.8 -0.2,-0.3 -0.5,0.3 -0.4,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path742"
         inkscape:connector-curvature="0"
         d="m 478.07474,500 0,-0.4 -0.4,-0.2 -0.3,-0.9 -0.4,-1.3 0.4,0 0.3,-1.1 -0.7,-0.5 -0.1,-0.4 -0.8,-0.6 0,-0.4 -0.9,-0.2 -0.4,-0.9 0,-0.8 -0.4,0 0.8,-0.5 1.3,-0.9 0.4,0 0,-0.6 -1.7,-0.5 -0.8,-1 -1,-0.4 -0.7,0.8 -0.1,0 0,-0.8 -0.5,0.4 -0.6,0 0.2,0.6 -0.8,0 0.2,0.9 0.4,0.4 0.2,0 -0.6,0.2 0,0.9 0.4,0.5 -0.4,0.4 -0.2,0 -1,0.4 0,-0.4 -0.4,0 -0.6,0.4 0.6,1.5 1.4,1 0.6,0.5 0.2,0.6 0,0.9 0.9,0.3 0.6,-0.7 1,0 0.6,0 -0.4,0.7 0,0.2 0.8,0.4 0.6,0.2 0.3,0.7 0.8,0.6 0,0.9 0.5,-0.3 0.7,-0.6 z" /><g
         id="g744"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path746"
           inkscape:connector-curvature="0"
           d="m 479.7,474.4 0,-0.4 -0.4,-0.2 -0.3,-0.9 -0.4,-1.3 0.4,0 0.3,-1.1 -0.7,-0.5 -0.1,-0.4 -0.8,-0.6 0,-0.4 -0.9,-0.2 -0.4,-0.9 0,-0.8 -0.4,0 0.8,-0.5 1.3,-0.9 0.4,0 0,-0.6 -1.7,-0.5 -0.8,-1 -1,-0.4 -0.7,0.8 -0.1,0 0,-0.8 -0.5,0.4 -0.6,0 0.2,0.6 -0.8,0 0.2,0.9 0.4,0.4 0.2,0 -0.6,0.2 0,0.9 0.4,0.5 -0.4,0.4 -0.2,0 -1,0.4 0,-0.4 -0.4,0 -0.6,0.4 0.6,1.5 1.4,1 0.6,0.5 0.2,0.6 0,0.9 0.9,0.3 0.6,-0.7 1,0 0.6,0 -0.4,0.7 0,0.2 0.8,0.4 0.6,0.2 0.3,0.7 0.8,0.6 0,0.9 0.5,-0.3 0.7,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path748"
         inkscape:connector-curvature="0"
         d="m 474.37474,488.9 0.8,0.9 1.6,0.6 0,0.5 1.7,-2.4 1.3,-1.6 0,-1.5 0.4,-0.4 0,-0.9 -0.4,-0.9 -0.7,0 -0.4,0 0,-0.6 -0.6,0.4 0,0.2 -0.4,-0.6 0,-0.2 -0.2,0.2 -0.4,-0.2 0.4,-0.4 -0.4,-0.3 -0.3,0.3 -0.4,-0.3 -0.6,-0.2 0,1.1 -0.4,-0.2 -0.2,1.1 0.2,0.4 -0.2,0.2 0,0.4 0.2,0.1 0,0.4 -0.2,0.4 -0.8,1.1 -0.3,0.4 0,0.1 1.1,-0.1 -0.4,0.5 -0.4,0.4 -0.3,0.6 -0.4,0.1 0.7,0 0,0.4 z" /><g
         id="g750"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path752"
           inkscape:connector-curvature="0"
           d="m 476,463.3 0.8,0.9 1.6,0.6 0,0.5 1.7,-2.4 1.3,-1.6 0,-1.5 0.4,-0.4 0,-0.9 -0.4,-0.9 -0.7,0 -0.4,0 0,-0.6 -0.6,0.4 0,0.2 -0.4,-0.6 0,-0.2 -0.2,0.2 -0.4,-0.2 0.4,-0.4 -0.4,-0.3 -0.3,0.3 -0.4,-0.3 -0.6,-0.2 0,1.1 -0.4,-0.2 -0.2,1.1 0.2,0.4 -0.2,0.2 0,0.4 0.2,0.1 0,0.4 -0.2,0.4 -0.8,1.1 -0.3,0.4 0,0.1 1.1,-0.1 -0.4,0.5 -0.4,0.4 -0.3,0.6 -0.4,0.1 0.7,0 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path754"
         inkscape:connector-curvature="0"
         d="m 340.67474,468.2 0.4,0.5 0.2,1 1,0 0.4,-0.4 0.5,0 0,-0.3 -0.9,-0.3 0.4,-0.3 0.7,-1.1 -1.1,0 -0.2,0.6 -1.4,0.3 z" /><g
         id="g756"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path758"
           inkscape:connector-curvature="0"
           d="m 342.3,442.6 0.4,0.5 0.2,1 1,0 0.4,-0.4 0.5,0 0,-0.3 -0.9,-0.3 0.4,-0.3 0.7,-1.1 -1.1,0 -0.2,0.6 -1.4,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path760"
         inkscape:connector-curvature="0"
         d="m 436.17474,455.2 0,-0.3 1.1,0 -0.4,1.8 0.4,0.4 0.6,0 0.3,-0.6 0.9,-0.4 0,-0.4 0,-0.8 1.4,-0.6 1.2,0 0.5,-0.6 0.4,0 0.1,-0.5 0,-1 -0.5,0 -0.6,0 0,-0.5 1.8,-1.3 0.4,-0.2 0.6,-0.7 0.6,-0.8 1,-0.9 0.8,-0.9 1.2,-1 0,-1.1 0.5,0 0,-0.7 0,-0.6 0,-0.5 -1.1,0 -0.6,0.5 -0.8,-0.4 0,-0.7 -0.2,-0.4 -0.4,0.4 -1,0 0,0.6 0.4,0.5 0,1.3 0,0.6 -0.4,0.1 0,0.4 -0.6,0.4 -0.4,0.7 -0.2,0.8 0,0.2 -1,1.3 -0.6,0.5 -1.1,0.4 -0.4,-0.7 -0.6,0 -0.4,-0.2 -0.6,0.5 -0.6,-0.5 -0.4,0 -0.6,-0.6 -0.5,0.2 0,1.3 0.5,1.1 -0.5,0.4 -0.2,0.5 -0.6,-0.3 -0.4,0.3 0.4,0.6 -0.8,0.6 -0.2,-0.2 0,0.5 0.2,0.4 0.8,0.2 -0.4,0.4 0.4,0.2 0.6,0.3 z" /><g
         id="g762"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path6238"
           inkscape:connector-curvature="0"
           d="m 437.8,429.6 0,-0.3 1.1,0 -0.4,1.8 0.4,0.4 0.6,0 0.3,-0.6 0.9,-0.4 0,-0.4 0,-0.8 1.4,-0.6 1.2,0 0.5,-0.6 0.4,0 0.1,-0.5 0,-1 -0.5,0 -0.6,0 0,-0.5 1.8,-1.3 0.4,-0.2 0.6,-0.7 0.6,-0.8 1,-0.9 0.8,-0.9 1.2,-1 0,-1.1 0.5,0 0,-0.7 0,-0.6 0,-0.5 -1.1,0 -0.6,0.5 -0.8,-0.4 0,-0.7 -0.2,-0.4 -0.4,0.4 -1,0 0,0.6 0.4,0.5 0,1.3 0,0.6 -0.4,0.1 0,0.4 -0.6,0.4 -0.4,0.7 -0.2,0.8 0,0.2 -1,1.3 -0.6,0.5 -1.1,0.4 -0.4,-0.7 -0.6,0 -0.4,-0.2 -0.6,0.5 -0.6,-0.5 -0.4,0 -0.6,-0.6 -0.5,0.2 0,1.3 0.5,1.1 -0.5,0.4 -0.2,0.5 -0.6,-0.3 -0.4,0.3 0.4,0.6 -0.8,0.6 -0.2,-0.2 0,0.5 0.2,0.4 0.8,0.2 -0.4,0.4 0.4,0.2 0.6,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path764"
         inkscape:connector-curvature="0"
         d="m 321.37474,477.8 1.2,0.3 0,-0.5 0,-0.8 -0.6,-0.6 0,-0.2 -0.5,-0.7 -0.6,0 0,-0.3 -0.6,0 0,0.6 0.4,0.6 0.2,0.9 0.5,0.7 z" /><g
         id="g768"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path770"
           inkscape:connector-curvature="0"
           d="m 323,452.2 1.2,0.3 0,-0.5 0,-0.8 -0.6,-0.6 0,-0.2 -0.5,-0.7 -0.6,0 0,-0.3 -0.6,0 0,0.6 0.4,0.6 0.2,0.9 0.5,0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path772"
         inkscape:connector-curvature="0"
         d="m 436.27474,429.8 0.1,0.1 1.5,-0.7 -0.4,-0.8 0.8,0 0.2,0.8 0.4,-0.4 0.3,0.4 0.4,0 2.2,-2.4 0,-1.5 0.4,-0.9 0,-0.8 0.6,-0.2 0.6,-2 -0.2,0.5 -1,-0.3 0,-0.2 -1,0.7 -1.3,0.8 -1.4,1.1 -0.2,0.5 -1,1 0,0.6 0,0.8 -0.4,0.4 -0.5,1.1 -0.1,1.4 z" /><g
         id="g774"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path776"
           inkscape:connector-curvature="0"
           d="m 437.9,404.2 0.1,0.1 1.5,-0.7 -0.4,-0.8 0.8,0 0.2,0.8 0.4,-0.4 0.3,0.4 0.4,0 2.2,-2.4 0,-1.5 0.4,-0.9 0,-0.8 0.6,-0.2 0.6,-2 -0.2,0.5 -1,-0.3 0,-0.2 -1,0.7 -1.3,0.8 -1.4,1.1 -0.2,0.5 -1,1 0,0.6 0,0.8 -0.4,0.4 -0.5,1.1 -0.1,1.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path778"
         inkscape:connector-curvature="0"
         d="m 418.57474,517.6 0,0.6 5.1,-1.1 2.4,-2.4 1.6,0 2.9,0.9 1.4,-0.4 2.8,-0.9 0.5,-0.6 1.2,-0.5 1,0 2.4,0 3.5,1.1 1.1,2.2 3,-0.9 1.8,0 -0.2,-0.9 0,-2.4 0,-0.6 -0.4,-0.4 1,-0.9 0.5,0.3 0.9,0 0.6,-0.3 0.7,0.9 1,0.4 1,-0.6 1.2,-0.9 0.6,-0.5 0.9,-1 -0.5,-0.3 -1.1,0.3 -1.5,-0.3 -0.6,-1 -0.4,0 0,-0.5 -0.2,0 -0.4,0.3 -0.4,0 -0.6,-0.7 0,-0.6 -1.2,-1.1 -1.1,0 -0.4,-1.3 -0.2,-0.2 -1.4,0 -1.3,0 -0.7,0.2 -0.3,-0.5 0,-1 0.7,-0.5 0,-1.3 -1,-0.6 -0.3,-1.1 -0.8,-0.5 -0.6,-0.8 -4.9,-0.5 -2,-1.5 -1.1,0 -0.6,0.3 -1,0 -3.2,0.8 -0.6,0.7 -2.7,0 -0.6,-0.3 -3.3,0 -0.4,-0.4 -1.6,0 -1,0.9 -1,1.9 -0.7,0.5 -0.4,0 -1.6,0.6 -1.6,0.9 -1,0 -2.3,0.4 -1.6,0.5 -0.4,1 0.4,0.3 -0.4,0.8 0,0.9 -1,1.3 -1.2,1.1 -0.4,-0.2 -0.2,0.6 -0.4,0.2 -0.7,-0.8 -1,1.2 -0.6,0.3 -0.4,0.2 -0.6,0.3 0,1 0.4,0.5 2.2,1 0,0.6 1.1,0.9 0.2,0.3 1,0.6 2,0.2 0.6,-0.6 2.1,0.4 0.1,-0.9 1.5,-0.4 2.2,0 3.1,0.4 -0.6,0.9 -0.5,0.9 -0.6,1.5 0.2,0.2 0.9,0.9 z" /><g
         id="g780"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path782"
           inkscape:connector-curvature="0"
           d="m 420.2,492 0,0.6 5.1,-1.1 2.4,-2.4 1.6,0 2.9,0.9 1.4,-0.4 2.8,-0.9 0.5,-0.6 1.2,-0.5 1,0 2.4,0 3.5,1.1 1.1,2.2 3,-0.9 1.8,0 -0.2,-0.9 0,-2.4 0,-0.6 -0.4,-0.4 1,-0.9 0.5,0.3 0.9,0 0.6,-0.3 0.7,0.9 1,0.4 1,-0.6 1.2,-0.9 0.6,-0.5 0.9,-1 -0.5,-0.3 -1.1,0.3 -1.5,-0.3 -0.6,-1 -0.4,0 0,-0.5 -0.2,0 -0.4,0.3 -0.4,0 -0.6,-0.7 0,-0.6 -1.2,-1.1 -1.1,0 -0.4,-1.3 -0.2,-0.2 -1.4,0 -1.3,0 -0.7,0.2 -0.3,-0.5 0,-1 0.7,-0.5 0,-1.3 -1,-0.6 -0.3,-1.1 -0.8,-0.5 -0.6,-0.8 -4.9,-0.5 -2,-1.5 -1.1,0 -0.6,0.3 -1,0 -3.2,0.8 -0.6,0.7 -2.7,0 -0.6,-0.3 -3.3,0 -0.4,-0.4 -1.6,0 -1,0.9 -1,1.9 -0.7,0.5 -0.4,0 -1.6,0.6 -1.6,0.9 -1,0 -2.3,0.4 -1.6,0.5 -0.4,1 0.4,0.3 -0.4,0.8 0,0.9 -1,1.3 -1.2,1.1 -0.4,-0.2 -0.2,0.6 -0.4,0.2 -0.7,-0.8 -1,1.2 -0.6,0.3 -0.4,0.2 -0.6,0.3 0,1 0.4,0.5 2.2,1 0,0.6 1.1,0.9 0.2,0.3 1,0.6 2,0.2 0.6,-0.6 2.1,0.4 0.1,-0.9 1.5,-0.4 2.2,0 3.1,0.4 -0.6,0.9 -0.5,0.9 -0.6,1.5 0.2,0.2 0.9,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path784"
         inkscape:connector-curvature="0"
         d="m 399.27474,471.2 1,-0.4 2.9,-1.1 0.6,-0.9 0.4,0.2 0.6,0 0.5,-0.8 1,-0.4 0.6,0.4 0,-0.9 0.6,0 1.6,-0.6 0.4,0.3 1,-0.3 1.9,0 -0.2,-0.9 0.2,-1.8 -1.6,0 0,0.3 -0.6,0 -1.1,0 -0.2,0 -0.8,0.6 -0.6,-0.4 -1,0.6 -0.2,0.4 -1.6,0.3 -2.1,0.6 -1.3,0.3 -1.4,0.6 -1.2,0.5 -0.8,0.6 0.8,0.4 -0.4,0.5 0.4,0.6 0.6,1.3 z" /><g
         id="g786"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path788"
           inkscape:connector-curvature="0"
           d="m 400.9,445.6 1,-0.4 2.9,-1.1 0.6,-0.9 0.4,0.2 0.6,0 0.5,-0.8 1,-0.4 0.6,0.4 0,-0.9 0.6,0 1.6,-0.6 0.4,0.3 1,-0.3 1.9,0 -0.2,-0.9 0.2,-1.8 -1.6,0 0,0.3 -0.6,0 -1.1,0 -0.2,0 -0.8,0.6 -0.6,-0.4 -1,0.6 -0.2,0.4 -1.6,0.3 -2.1,0.6 -1.3,0.3 -1.4,0.6 -1.2,0.5 -0.8,0.6 0.8,0.4 -0.4,0.5 0.4,0.6 0.6,1.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path790"
         inkscape:connector-curvature="0"
         d="m 356.57474,456.7 0,1.3 0.3,0.6 0,0.5 0.8,0 0.1,0 -0.1,0.6 -0.5,0 -0.3,1.2 0.3,0.3 0.5,0 1.1,-1.7 2.3,-0.7 1,-0.2 1.4,-1.5 0.6,-0.4 -0.6,-1.8 -1.4,-1.7 -0.2,0 -0.4,0.2 -0.4,-0.7 -0.2,-1 0.2,-0.9 -1.7,-0.4 -0.6,-0.5 0,-0.6 -0.4,-0.4 -1.5,-0.2 0,-0.9 -0.3,-0.4 -0.6,0 -1.8,-0.3 -0.8,-0.2 -2,3.9 5.2,1.9 0.6,3.1 -0.6,0.9 z" /><g
         id="g792"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path794"
           inkscape:connector-curvature="0"
           d="m 358.2,431.1 0,1.3 0.3,0.6 0,0.5 0.8,0 0.1,0 -0.1,0.6 -0.5,0 -0.3,1.2 0.3,0.3 0.5,0 1.1,-1.7 2.3,-0.7 1,-0.2 1.4,-1.5 0.6,-0.4 -0.6,-1.8 -1.4,-1.7 -0.2,0 -0.4,0.2 -0.4,-0.7 -0.2,-1 0.2,-0.9 -1.7,-0.4 -0.6,-0.5 0,-0.6 -0.4,-0.4 -1.5,-0.2 0,-0.9 -0.3,-0.4 -0.6,0 -1.8,-0.3 -0.8,-0.2 -2,3.9 5.2,1.9 0.6,3.1 -0.6,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path796"
         inkscape:connector-curvature="0"
         d="m 386.47474,483.1 1,0.3 2.3,-0.9 1.1,-0.9 1.6,-1.2 -0.4,-0.3 -1.2,-0.9 -1.7,-0.6 -1,0.4 -1.7,0 -0.6,-0.6 0.8,-0.9 0,-1.5 0.8,-0.4 0.3,-0.6 1.4,-0.3 0,-0.6 -0.6,-0.3 -0.8,-0.6 0.8,-0.9 -0.8,-0.6 -0.7,-1.1 -0.6,-0.7 0,-1 -1.6,-0.7 0,-0.9 -0.4,-0.4 -0.2,-0.2 -2,-0.7 -0.5,0.7 -0.1,-0.4 -0.5,-0.3 -1,-1.5 1.5,-0.2 0,-1.7 0.6,-0.1 1,-2.1 0,-0.4 -0.4,-0.3 -0.4,0.3 -0.7,-0.3 -0.6,0 -1.6,0 0,-0.2 -0.6,0 0,-0.4 -0.4,-0.4 -1.6,0.8 -0.4,0.9 -0.2,0.5 -0.9,0 -0.2,1 -0.6,0.2 -0.8,-0.2 -1.9,-0.4 0,-0.4 -0.8,0.4 -0.6,0.4 -1.2,-0.4 -1.4,0 -1.1,-0.4 0,1 0.5,1.3 0.6,0 0.8,0.5 0.6,0 0,0.4 0.2,0 0,0.8 -0.6,0 -0.2,1.2 -1,1 -1,0.1 -1.5,1.9 2.5,-0.6 1.2,0 1,0 0.6,0.2 1.6,0.4 2.1,0.6 0,1.8 0.4,0.4 0.2,0.2 0.4,0 1.1,0 0.2,0.5 0.4,0.4 0.6,-0.4 0.4,0 0.6,-0.1 0.6,0.5 -0.2,0.9 0.2,0.6 0,0.9 1,0.6 -0.2,0.6 -0.4,0.7 0.6,0 1.5,0 -0.5,0.9 0.5,0.6 0.1,1.1 -0.1,1.5 0.1,0.7 1.1,0.6 1,0.2 2.6,0 z" /><g
         id="g798"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path800"
           inkscape:connector-curvature="0"
           d="m 388.1,457.5 1,0.3 2.3,-0.9 1.1,-0.9 1.6,-1.2 -0.4,-0.3 -1.2,-0.9 -1.7,-0.6 -1,0.4 -1.7,0 -0.6,-0.6 0.8,-0.9 0,-1.5 0.8,-0.4 0.3,-0.6 1.4,-0.3 0,-0.6 -0.6,-0.3 -0.8,-0.6 0.8,-0.9 -0.8,-0.6 -0.7,-1.1 -0.6,-0.7 0,-1 -1.6,-0.7 0,-0.9 -0.4,-0.4 -0.2,-0.2 -2,-0.7 -0.5,0.7 -0.1,-0.4 -0.5,-0.3 -1,-1.5 1.5,-0.2 0,-1.7 0.6,-0.1 1,-2.1 0,-0.4 -0.4,-0.3 -0.4,0.3 -0.7,-0.3 -0.6,0 -1.6,0 0,-0.2 -0.6,0 0,-0.4 -0.4,-0.4 -1.6,0.8 -0.4,0.9 -0.2,0.5 -0.9,0 -0.2,1 -0.6,0.2 -0.8,-0.2 -1.9,-0.4 0,-0.4 -0.8,0.4 -0.6,0.4 -1.2,-0.4 -1.4,0 -1.1,-0.4 0,1 0.5,1.3 0.6,0 0.8,0.5 0.6,0 0,0.4 0.2,0 0,0.8 -0.6,0 -0.2,1.2 -1,1 -1,0.1 -1.5,1.9 2.5,-0.6 1.2,0 1,0 0.6,0.2 1.6,0.4 2.1,0.6 0,1.8 0.4,0.4 0.2,0.2 0.4,0 1.1,0 0.2,0.5 0.4,0.4 0.6,-0.4 0.4,0 0.6,-0.1 0.6,0.5 -0.2,0.9 0.2,0.6 0,0.9 1,0.6 -0.2,0.6 -0.4,0.7 0.6,0 1.5,0 -0.5,0.9 0.5,0.6 0.1,1.1 -0.1,1.5 0.1,0.7 1.1,0.6 1,0.2 2.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path802"
         inkscape:connector-curvature="0"
         d="m 477.67474,439.6 -0.3,0.6 -0.6,0.4 -0.3,0.3 -0.7,0.6 -0.4,0.2 -0.2,0.7 -0.4,-0.4 0.6,-1.1 -0.2,0 -0.8,0.8 -1.3,0.3 -0.4,-0.3 -0.1,0 -0.5,0.3 -0.6,0.4 0.2,0.6 0,0.5 -0.2,0 -0.4,-0.5 -0.2,0.2 -0.8,1.8 -0.6,1.3 0.6,-0.4 0.4,-0.4 0.4,0.4 -0.4,1.1 0,1.4 0.4,1.8 0.6,0 1.2,-0.4 0.4,0.4 0.2,-0.4 0,-0.6 0,-0.8 0.9,-0.6 0,-0.8 -0.4,-1.1 -1.1,-0.4 -0.1,-0.7 0.5,-0.9 0,-0.4 0,-0.5 1.1,-0.6 0.2,0.2 0,0.4 1,0 0.4,-1 0.3,0 0,0.4 1.3,-0.4 -0.6,-0.3 0.2,-0.6 0.7,-0.2 0,-1.3 z" /><g
         id="g804"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path806"
           inkscape:connector-curvature="0"
           d="m 479.3,414 -0.3,0.6 -0.6,0.4 -0.3,0.3 -0.7,0.6 -0.4,0.2 -0.2,0.7 -0.4,-0.4 0.6,-1.1 -0.2,0 -0.8,0.8 -1.3,0.3 -0.4,-0.3 -0.1,0 -0.5,0.3 -0.6,0.4 0.2,0.6 0,0.5 -0.2,0 -0.4,-0.5 -0.2,0.2 -0.8,1.8 -0.6,1.3 0.6,-0.4 0.4,-0.4 0.4,0.4 -0.4,1.1 0,1.4 0.4,1.8 0.6,0 1.2,-0.4 0.4,0.4 0.2,-0.4 0,-0.6 0,-0.8 0.9,-0.6 0,-0.8 -0.4,-1.1 -1.1,-0.4 -0.1,-0.7 0.5,-0.9 0,-0.4 0,-0.5 1.1,-0.6 0.2,0.2 0,0.4 1,0 0.4,-1 0.3,0 0,0.4 1.3,-0.4 -0.6,-0.3 0.2,-0.6 0.7,-0.2 0,-1.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path808"
         inkscape:connector-curvature="0"
         d="m 480.67474,427.9 -0.5,0.4 0,0.2 0,0.4 -0.1,-0.4 -1.5,0.4 -0.6,1.4 0.4,0.6 -0.4,0.5 -1,0.4 0.4,-0.9 -0.7,0.4 -0.4,0.1 0,-0.1 -0.4,0 -0.2,0.5 -0.6,-0.5 -0.4,-1 0,0.6 0,1.3 1.2,0.2 0.4,0.7 1.1,0.2 0.2,-0.6 -0.2,-0.3 0,-0.2 1,0.5 0.2,0.6 0.8,-0.2 0,0.7 0.2,-0.1 0.5,0.1 0.6,0 -0.5,1.3 1.1,-0.5 0.6,-0.4 0,-0.9 -0.2,-0.2 0.7,0 0,-1.3 0.4,-0.9 -0.9,-0.2 0.5,-1.3 -0.5,0 -0.6,1.5 -1.1,-0.9 0.9,-0.7 -0.4,-1.4 z" /><g
         id="g810"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path812"
           inkscape:connector-curvature="0"
           d="m 482.3,402.3 -0.5,0.4 0,0.2 0,0.4 -0.1,-0.4 -1.5,0.4 -0.6,1.4 0.4,0.6 -0.4,0.5 -1,0.4 0.4,-0.9 -0.7,0.4 -0.4,0.1 0,-0.1 -0.4,0 -0.2,0.5 -0.6,-0.5 -0.4,-1 0,0.6 0,1.3 1.2,0.2 0.4,0.7 1.1,0.2 0.2,-0.6 -0.2,-0.3 0,-0.2 1,0.5 0.2,0.6 0.8,-0.2 0,0.7 0.2,-0.1 0.5,0.1 0.6,0 -0.5,1.3 1.1,-0.5 0.6,-0.4 0,-0.9 -0.2,-0.2 0.7,0 0,-1.3 0.4,-0.9 -0.9,-0.2 0.5,-1.3 -0.5,0 -0.6,1.5 -1.1,-0.9 0.9,-0.7 -0.4,-1.4 z" /></g><g
         id="g1194"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1196"
           inkscape:connector-curvature="0"
           d="m 305.8,430.2 20,-0.2 0.4,-0.9 0.6,-0.6 -0.2,0 0.9,-3.7 0.8,-0.6 1,-0.5 0.2,-0.4 -0.2,-0.9 -0.8,0.4 -0.6,-0.6 0,-0.4 -0.7,0 -0.4,-0.9 0,-0.9 -0.6,-2.1 -0.4,-2 -0.2,-1 -0.6,-0.4 -0.9,-1 -0.2,-1.5 -0.3,-0.4 -0.6,0 -0.5,-0.6 0,-0.5 -0.1,-0.8 0,-1.6 -0.9,-0.4 -1.2,0 0,-0.9 1.2,-0.6 1,-0.9 1.4,-1 0.2,-0.7 0.5,-0.7 0.6,0 0,-1.2 -0.2,0 -0.4,0.6 -1,-0.6 -1.1,-0.3 -0.1,-0.6 -1.1,-0.6 -1.4,0 -1,0 -0.2,-0.3 -0.7,0.3 -1.4,0.3 -0.2,-0.3 -1.4,1 -0.7,0.5 -0.3,0 0,-0.3 -1,0 0,0.3 -0.3,-0.3 -0.4,-0.2 -0.6,0.2 -0.6,0.3 0,0.6 -0.8,0.4 -0.2,0.9 -1,0.6 -0.5,0.9 -0.1,0.2 -0.5,0 -0.2,0.5 -0.9,0 -0.5,1.3 -0.6,0.6 -1,0 0,0.5 -1,0 -0.2,0.4 0,0.4 -0.4,0.2 0.6,0.3 -0.2,1 -1.1,1.1 -0.4,0.4 0.4,0.5 -0.8,0.4 0,0.7 -0.2,0.8 0,0.1 -0.3,0.4 -0.3,-0.4 0,0.8 0.6,0.6 -0.3,0.9 0.5,0.6 -0.2,0.5 0.2,0.4 0.8,1 -0.4,0.1 0.7,0.4 1.4,0.2 0,6.1 0,0.6 1.6,0 0,3.5 z" /></g><g
         id="g816"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path818"
           inkscape:connector-curvature="0"
           d="m 475.7,410.4 0,1.7 0,1 0.7,0 0.4,-0.6 0.2,0.4 0.7,-0.4 0,-0.5 -0.3,-0.4 -1.7,-1.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path820"
         inkscape:connector-curvature="0"
         d="m 465.77474,433 1.1,0.8 0.6,0.4 0.8,1.1 0.6,0.7 0.6,0.2 0,0.9 0.4,1 0,-0.6 0,-0.4 0.2,-0.9 -0.6,-0.2 0,-0.7 -0.6,-0.2 -0.6,-0.9 -0.8,-0.4 -0.8,-0.5 -0.9,-0.3 z" /><g
         id="g822"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path824"
           inkscape:connector-curvature="0"
           d="m 467.4,407.4 1.1,0.8 0.6,0.4 0.8,1.1 0.6,0.7 0.6,0.2 0,0.9 0.4,1 0,-0.6 0,-0.4 0.2,-0.9 -0.6,-0.2 0,-0.7 -0.6,-0.2 -0.6,-0.9 -0.8,-0.4 -0.8,-0.5 -0.9,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path826"
         inkscape:connector-curvature="0"
         d="m 480.67474,437.2 -0.6,0.3 -0.6,1 -0.8,0.2 -0.6,1 1.6,0.3 0.4,-0.3 0.2,-0.4 -0.2,-0.6 0.6,-1.5 z" /><g
         id="g828"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path830"
           inkscape:connector-curvature="0"
           d="m 482.3,411.6 -0.6,0.3 -0.6,1 -0.8,0.2 -0.6,1 1.6,0.3 0.4,-0.3 0.2,-0.4 -0.2,-0.6 0.6,-1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path832"
         inkscape:connector-curvature="0"
         d="m 476.37474,433.9 -0.6,0.7 -0.4,0 -0.2,0.8 0.6,0.4 0.6,1.3 0.4,0.1 0.3,-0.1 -0.3,-1 -0.4,-1.3 0.4,-0.9 -0.4,0 z" /><g
         id="g834"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path836"
           inkscape:connector-curvature="0"
           d="m 478,408.3 -0.6,0.7 -0.4,0 -0.2,0.8 0.6,0.4 0.6,1.3 0.4,0.1 0.3,-0.1 -0.3,-1 -0.4,-1.3 0.4,-0.9 -0.4,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path838"
         inkscape:connector-curvature="0"
         d="m 479.67474,435.6 -0.6,1.5 -0.4,0 -0.3,1 0.3,-0.4 0.8,0 0,-0.6 0.7,-1.1 -0.5,-0.4 z" /><g
         id="g840"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path842"
           inkscape:connector-curvature="0"
           d="m 481.3,410 -0.6,1.5 -0.4,0 -0.3,1 0.3,-0.4 0.8,0 0,-0.6 0.7,-1.1 -0.5,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path844"
         inkscape:connector-curvature="0"
         d="m 476.97474,434.8 -0.2,0 0.6,1.5 0.3,1.3 0,-1.5 -0.3,-0.3 -0.4,-1 z" /><g
         id="g846"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path848"
           inkscape:connector-curvature="0"
           d="m 478.6,409.2 -0.2,0 0.6,1.5 0.3,1.3 0,-1.5 -0.3,-0.3 -0.4,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path850"
         inkscape:connector-curvature="0"
         d="m 478.67474,434.8 -1,0.3 0.4,0.3 0.6,0.3 0.4,-0.3 -0.4,-0.6 z" /><g
         id="g852"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path854"
           inkscape:connector-curvature="0"
           d="m 480.3,409.2 -1,0.3 0.4,0.3 0.6,0.3 0.4,-0.3 -0.4,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path856"
         inkscape:connector-curvature="0"
         d="m 320.27474,468.4 2.3,-0.2 1.4,1.1 0.2,0 0.8,0.4 0.2,0.6 0.4,0.4 -1.6,1.4 0,0.2 2.3,0.6 1.4,0.7 1.6,-0.3 3.5,-1.9 1.4,-0.6 0.2,-0.1 1,-0.4 0,-1.3 2.1,-0.8 3.2,0 1.4,-0.3 0.2,-0.6 1,0 0.5,-0.9 0.6,-0.4 0,-0.5 0.6,0 0,-0.4 0.6,-0.6 0.8,-0.2 0.2,-0.9 0,-0.4 -0.2,0 0.2,-0.5 0.4,-0.6 0.7,-0.7 0.5,-0.8 0.4,0 0.4,-0.4 0.3,-0.5 0.6,-0.7 0.8,-0.8 0.2,-0.7 5.3,-0.8 0.4,0.2 0.6,-1 -0.6,-3.1 -5.3,-1.9 -5.7,-1.3 -2.9,-3.5 -1,-0.4 0,1.4 -3.2,1.4 -1.5,-0.4 -0.6,0 -0.2,0 0,-1 -1,-0.6 -1,1.6 -1,1 -1.6,2.4 -1.1,1.5 -1.2,0.4 -1,1.5 0,1 0,0.8 -1.7,2.6 -1.4,0.6 -0.6,0.9 0.4,0.4 -1.4,1.5 -1.2,2 -1,1.5 -0.7,0.4 -0.8,0 0.4,1.1 0,0.9 z" /><g
         id="g858"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path860"
           inkscape:connector-curvature="0"
           d="m 321.9,442.8 2.3,-0.2 1.4,1.1 0.2,0 0.8,0.4 0.2,0.6 0.4,0.4 -1.6,1.4 0,0.2 2.3,0.6 1.4,0.7 1.6,-0.3 3.5,-1.9 1.4,-0.6 0.2,-0.1 1,-0.4 0,-1.3 2.1,-0.8 3.2,0 1.4,-0.3 0.2,-0.6 1,0 0.5,-0.9 0.6,-0.4 0,-0.5 0.6,0 0,-0.4 0.6,-0.6 0.8,-0.2 0.2,-0.9 0,-0.4 -0.2,0 0.2,-0.5 0.4,-0.6 0.7,-0.7 0.5,-0.8 0.4,0 0.4,-0.4 0.3,-0.5 0.6,-0.7 0.8,-0.8 0.2,-0.7 5.3,-0.8 0.4,0.2 0.6,-1 -0.6,-3.1 -5.3,-1.9 -5.7,-1.3 -2.9,-3.5 -1,-0.4 0,1.4 -3.2,1.4 -1.5,-0.4 -0.6,0 -0.2,0 0,-1 -1,-0.6 -1,1.6 -1,1 -1.6,2.4 -1.1,1.5 -1.2,0.4 -1,1.5 0,1 0,0.8 -1.7,2.6 -1.4,0.6 -0.6,0.9 0.4,0.4 -1.4,1.5 -1.2,2 -1,1.5 -0.7,0.4 -0.8,0 0.4,1.1 0,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path862"
         inkscape:connector-curvature="0"
         d="m 401.57474,428.8 -0.6,0.9 -0.4,1.5 -0.7,1.2 0.4,0 0.3,1.2 -0.3,0.2 0.3,0.4 0.4,0.6 -0.4,0.3 1.6,-0.9 0,-0.4 0.9,-0.7 0.8,-1.7 -0.3,-1.7 -2,-0.9 z" /><g
         id="g864"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path866"
           inkscape:connector-curvature="0"
           d="m 403.2,403.2 -0.6,0.9 -0.4,1.5 -0.7,1.2 0.4,0 0.3,1.2 -0.3,0.2 0.3,0.4 0.4,0.6 -0.4,0.3 1.6,-0.9 0,-0.4 0.9,-0.7 0.8,-1.7 -0.3,-1.7 -2,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path868"
         inkscape:connector-curvature="0"
         d="m 321.37474,480.5 0.1,-0.4 0.9,0.4 0.1,0.2 0,0.4 0,0.9 0.5,0 0.4,-0.4 0.8,0 1,0.4 1.4,-0.4 1.3,0 1.4,1 0.8,0 0.9,0 0.6,0.3 0.4,-0.3 -0.4,-0.6 -1,-0.6 0,-0.7 0,-0.7 0,-1.4 -0.5,-0.9 -1.2,-0.9 -2.3,-1.2 -1.6,-1.1 -1.6,-0.7 -0.9,0 -0.1,0 -0.4,0.3 -1.1,0 0.6,1 0,0.2 0.5,0.7 0,0.2 0.5,0.6 0,0.8 0,0.4 -1.1,-0.3 0,0.7 0,0.6 -0.5,0.6 0.5,0.9 z" /><g
         id="g870"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path872"
           inkscape:connector-curvature="0"
           d="m 323,454.9 0.1,-0.4 0.9,0.4 0.1,0.2 0,0.4 0,0.9 0.5,0 0.4,-0.4 0.8,0 1,0.4 1.4,-0.4 1.3,0 1.4,1 0.8,0 0.9,0 0.6,0.3 0.4,-0.3 -0.4,-0.6 -1,-0.6 0,-0.7 0,-0.7 0,-1.4 -0.5,-0.9 -1.2,-0.9 -2.3,-1.2 -1.6,-1.1 -1.6,-0.7 -0.9,0 -0.1,0 -0.4,0.3 -1.1,0 0.6,1 0,0.2 0.5,0.7 0,0.2 0.5,0.6 0,0.8 0,0.4 -1.1,-0.3 0,0.7 0,0.6 -0.5,0.6 0.5,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path874"
         inkscape:connector-curvature="0"
         d="m 434.57474,453.7 0,-0.5 0.2,0.2 0.9,-0.6 -0.4,-0.5 0.4,-0.4 0.6,0.4 0.1,-0.6 0.5,-0.4 -0.5,-1.1 0,-1.3 0.5,-0.2 0.6,0.6 0.4,0 0.6,0.5 0.6,-0.5 0.4,0.2 0.7,0 0.4,0.7 1,-0.4 0.6,-0.5 1.1,-1.3 0,-0.2 0.1,-0.8 0.4,-0.7 0.7,-0.4 0,-0.3 0.4,-0.3 0,-0.5 0,-1.3 -0.4,-0.6 -0.3,0 -0.8,0.2 -0.1,-0.2 -1.5,0.2 -1.2,-0.2 0,-1.2 -1.1,-0.6 0.4,-1 0.9,-1.6 -0.6,0.5 -1.7,1 -1.6,0.1 0.4,1.3 -1.6,-0.3 0,-1.1 0,-1 -0.9,-1.2 -0.2,-1.5 -0.4,-0.6 0.4,-1.9 1.1,0.4 0.2,-1.1 0.4,0 0.6,-2.4 1,-0.4 1,0 0.6,-1.1 -0.4,0 -0.2,-0.4 -0.4,0.4 -0.2,-0.8 -0.8,0 0.4,0.8 -1.5,0.7 -0.1,-0.1 -0.6,0.9 -0.9,0.5 -0.8,1.2 -0.4,0.7 -0.4,-0.4 0,-0.5 -0.2,0.2 -0.4,1.2 0.4,1.2 0.2,0.5 0,0.4 0.4,0.4 0,0.5 0.6,1.5 0.4,0.9 -0.6,1.9 0,0.8 -1.8,1.8 0,0.6 0.4,0.7 0,0.8 0.4,0.3 -0.4,1 -0.4,0.5 -1.2,1.9 -0.4,0.5 -0.3,0 0,0.4 0.3,0.2 0,0.9 0.4,0.6 0.3,0.4 1.7,0.3 -0.4,0.6 0.6,-0.4 0.4,0.6 1,0.3 z" /><g
         id="g876"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path878"
           inkscape:connector-curvature="0"
           d="m 436.2,428.1 0,-0.5 0.2,0.2 0.9,-0.6 -0.4,-0.5 0.4,-0.4 0.6,0.4 0.1,-0.6 0.5,-0.4 -0.5,-1.1 0,-1.3 0.5,-0.2 0.6,0.6 0.4,0 0.6,0.5 0.6,-0.5 0.4,0.2 0.7,0 0.4,0.7 1,-0.4 0.6,-0.5 1.1,-1.3 0,-0.2 0.1,-0.8 0.4,-0.7 0.7,-0.4 0,-0.3 0.4,-0.3 0,-0.5 0,-1.3 -0.4,-0.6 -0.3,0 -0.8,0.2 -0.1,-0.2 -1.5,0.2 -1.2,-0.2 0,-1.2 -1.1,-0.6 0.4,-1 0.9,-1.6 -0.6,0.5 -1.7,1 -1.6,0.1 0.4,1.3 -1.6,-0.3 0,-1.1 0,-1 -0.9,-1.2 -0.2,-1.5 -0.4,-0.6 0.4,-1.9 1.1,0.4 0.2,-1.1 0.4,0 0.6,-2.4 1,-0.4 1,0 0.6,-1.1 -0.4,0 -0.2,-0.4 -0.4,0.4 -0.2,-0.8 -0.8,0 0.4,0.8 -1.5,0.7 -0.1,-0.1 -0.6,0.9 -0.9,0.5 -0.8,1.2 -0.4,0.7 -0.4,-0.4 0,-0.5 -0.2,0.2 -0.4,1.2 0.4,1.2 0.2,0.5 0,0.4 0.4,0.4 0,0.5 0.6,1.5 0.4,0.9 -0.6,1.9 0,0.8 -1.8,1.8 0,0.6 0.4,0.7 0,0.8 0.4,0.3 -0.4,1 -0.4,0.5 -1.2,1.9 -0.4,0.5 -0.3,0 0,0.4 0.3,0.2 0,0.9 0.4,0.6 0.3,0.4 1.7,0.3 -0.4,0.6 0.6,-0.4 0.4,0.6 1,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path880"
         inkscape:connector-curvature="0"
         d="m 349.07474,460 0.2,0.4 0,-0.7 0.4,-0.2 0.2,-0.4 1,0 0.4,0.4 2,0 0.9,0.2 0.8,0.7 1,1.1 0.8,1 0.4,0.5 0.4,-0.3 0,-0.6 0,-0.9 -0.4,0 -0.4,-0.2 0.4,-1.3 0.4,0 0.2,-0.6 -0.2,0 -0.8,0 0,-0.5 -0.2,-0.6 0,-1.3 -0.4,-0.2 -5.3,0.7 -0.2,0.8 -0.8,0.8 -0.6,0.7 -0.2,0.5 z" /><g
         id="g882"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path884"
           inkscape:connector-curvature="0"
           d="m 350.7,434.4 0.2,0.4 0,-0.7 0.4,-0.2 0.2,-0.4 1,0 0.4,0.4 2,0 0.9,0.2 0.8,0.7 1,1.1 0.8,1 0.4,0.5 0.4,-0.3 0,-0.6 0,-0.9 -0.4,0 -0.4,-0.2 0.4,-1.3 0.4,0 0.2,-0.6 -0.2,0 -0.8,0 0,-0.5 -0.2,-0.6 0,-1.3 -0.4,-0.2 -5.3,0.7 -0.2,0.8 -0.8,0.8 -0.6,0.7 -0.2,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path886"
         inkscape:connector-curvature="0"
         d="m 437.87474,457 0.4,0.3 0.2,0 0.6,0 0.4,0.3 0.6,0 0.6,-0.6 0.4,0.6 1.1,0.4 0,0.5 1,0.2 0.6,-0.5 1.7,-0.2 0.4,0 -0.4,-1 1.4,-1.5 0.8,0.2 0.4,-0.2 -0.4,-0.3 -0.6,0 0,-0.6 -0.6,0 -0.6,-0.5 0,-1 -0.4,-0.3 -0.7,-0.2 -0.4,-1.7 1.5,-1.5 0.6,-1.1 1.6,-1.5 0.6,-0.4 0.4,-0.2 0.6,-0.7 0.6,-0.7 0.5,0 0,-1 1,-2.4 0,-0.5 0.2,-0.4 -0.2,-0.4 0,-0.2 0.2,-0.3 -0.2,-0.6 0.2,-0.4 -0.6,-0.9 -1.1,-0.6 -0.6,0 -0.4,-0.5 -1.6,-0.6 -0.2,-0.4 -0.4,-0.9 -0.6,0.6 0,-1.1 -1,0 -1.3,-1 0,1.9 0.2,0.2 -0.2,0.7 -0.4,0 -0.4,0.2 1.7,0.8 1,0.1 0,0.6 -0.2,0.7 0.6,0.2 1,0.4 0.6,0.2 0.6,0.4 0,1.3 -0.2,1.4 0,0.6 0,0.5 0,0.6 0,0.8 -0.4,0 0,1 -1.2,1 -0.8,0.9 -1,1 -0.7,0.7 -0.6,0.7 -0.4,0.3 -1.8,1.2 0,0.6 0.6,0 0.6,0 0,0.9 -0.2,0.6 -0.4,0 -0.5,0.5 -1.2,0 -1.4,0.6 0,0.9 0,0.4 -0.8,0.4 -0.4,0.5 z" /><g
         id="g888"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path890"
           inkscape:connector-curvature="0"
           d="m 439.5,431.4 0.4,0.3 0.2,0 0.6,0 0.4,0.3 0.6,0 0.6,-0.6 0.4,0.6 1.1,0.4 0,0.5 1,0.2 0.6,-0.5 1.7,-0.2 0.4,0 -0.4,-1 1.4,-1.5 0.8,0.2 0.4,-0.2 -0.4,-0.3 -0.6,0 0,-0.6 -0.6,0 -0.6,-0.5 0,-1 -0.4,-0.3 -0.7,-0.2 -0.4,-1.7 1.5,-1.5 0.6,-1.1 1.6,-1.5 0.6,-0.4 0.4,-0.2 0.6,-0.7 0.6,-0.7 0.5,0 0,-1 1,-2.4 0,-0.5 0.2,-0.4 -0.2,-0.4 0,-0.2 0.2,-0.3 -0.2,-0.6 0.2,-0.4 -0.6,-0.9 -1.1,-0.6 -0.6,0 -0.4,-0.5 -1.6,-0.6 -0.2,-0.4 -0.4,-0.9 -0.6,0.6 0,-1.1 -1,0 -1.3,-1 0,1.9 0.2,0.2 -0.2,0.7 -0.4,0 -0.4,0.2 1.7,0.8 1,0.1 0,0.6 -0.2,0.7 0.6,0.2 1,0.4 0.6,0.2 0.6,0.4 0,1.3 -0.2,1.4 0,0.6 0,0.5 0,0.6 0,0.8 -0.4,0 0,1 -1.2,1 -0.8,0.9 -1,1 -0.7,0.7 -0.6,0.7 -0.4,0.3 -1.8,1.2 0,0.6 0.6,0 0.6,0 0,0.9 -0.2,0.6 -0.4,0 -0.5,0.5 -1.2,0 -1.4,0.6 0,0.9 0,0.4 -0.8,0.4 -0.4,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path892"
         inkscape:connector-curvature="0"
         d="m 103.87474,443.5 0.7,0.5 0.4,0.8 1.4,-0.4 1.2,-0.6 0.5,-0.9 -0.9,-0.4 -1.8,0.4 -1.5,0.6 z" /><g
         id="g894"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path896"
           inkscape:connector-curvature="0"
           d="m 105.5,417.9 0.7,0.5 0.4,0.8 1.4,-0.4 1.2,-0.6 0.5,-0.9 -0.9,-0.4 -1.8,0.4 -1.5,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path898"
         inkscape:connector-curvature="0"
         d="m 461.27474,426.8 0.2,-1.3 0.8,0.5 0.6,0 -0.6,0.8 0.2,0.2 0.4,0.9 0.6,0.3 0.5,-0.3 0.6,1.4 0.6,1.4 0.4,-0.4 0,-0.6 0.2,0.6 0.4,0 0,-0.6 0.6,0 0,-0.5 0.4,-0.4 0.3,0 0,-0.3 -0.3,-0.3 0.6,0 0.4,0.3 0.9,-0.6 0.8,0 0,-0.6 -0.8,0 -0.7,-0.3 -0.2,0 -0.4,0 1,-1 -1.3,-0.2 -0.3,0.2 -0.4,-0.2 0.4,-0.5 -3.1,0.2 -0.6,-1.9 -1,-0.1 -0.2,-1.9 -1.8,-1 -1.7,-0.5 -2.3,0.3 -1,0 -1.8,0.6 -0.4,1 1,0 0.6,-0.4 0.4,0 0.2,0.9 0.4,1 1.1,0.1 1.8,0.8 2.1,2 0.4,0.4 z" /><g
         id="g900"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path902"
           inkscape:connector-curvature="0"
           d="m 462.9,401.2 0.2,-1.3 0.8,0.5 0.6,0 -0.6,0.8 0.2,0.2 0.4,0.9 0.6,0.3 0.5,-0.3 0.6,1.4 0.6,1.4 0.4,-0.4 0,-0.6 0.2,0.6 0.4,0 0,-0.6 0.6,0 0,-0.5 0.4,-0.4 0.3,0 0,-0.3 -0.3,-0.3 0.6,0 0.4,0.3 0.9,-0.6 0.8,0 0,-0.6 -0.8,0 -0.7,-0.3 -0.2,0 -0.4,0 1,-1 -1.3,-0.2 -0.3,0.2 -0.4,-0.2 0.4,-0.5 -3.1,0.2 -0.6,-1.9 -1,-0.1 -0.2,-1.9 -1.8,-1 -1.7,-0.5 -2.3,0.3 -1,0 -1.8,0.6 -0.4,1 1,0 0.6,-0.4 0.4,0 0.2,0.9 0.4,1 1.1,0.1 1.8,0.8 2.1,2 0.4,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path904"
         inkscape:connector-curvature="0"
         d="m 443.27474,422 -0.5,-0.6 -0.2,-0.4 -0.4,0.4 0,0.2 1.1,0.4 z" /><g
         id="g906"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path908"
           inkscape:connector-curvature="0"
           d="m 444.9,396.4 -0.5,-0.6 -0.2,-0.4 -0.4,0.4 0,0.2 1.1,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path910"
         inkscape:connector-curvature="0"
         d="m 451.17474,452.6 -0.4,0 0,-0.8 -1.1,-0.5 0,-1.2 1.7,-0.7 0.6,0.7 1,0.2 0,1 0.9,0.5 0,0.8 -0.5,0.2 -0.4,-0.2 -1.6,0 -0.2,0 z" /><g
         id="g912"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path914"
           inkscape:connector-curvature="0"
           d="m 452.8,427 -0.4,0 0,-0.8 -1.1,-0.5 0,-1.2 1.7,-0.7 0.6,0.7 1,0.2 0,1 0.9,0.5 0,0.8 -0.5,0.2 -0.4,-0.2 -1.6,0 -0.2,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path916"
         inkscape:connector-curvature="0"
         d="m 391.77474,426.8 0.2,-0.4 -0.2,0 -0.4,0 0,0.6 0.4,0 0,-0.2 z" /><g
         id="g918"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path920"
           inkscape:connector-curvature="0"
           d="m 393.4,401.2 0.2,-0.4 -0.2,0 -0.4,0 0,0.6 0.4,0 0,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path922"
         inkscape:connector-curvature="0"
         d="m 401.57474,509.8 -0.2,-1.1 -0.8,-0.4 -1,-0.3 0.8,-2.3 -0.8,-0.7 -3.4,0 -0.5,-3.2 0.6,-0.3 -0.6,-0.6 -1,0.2 -2.7,-1.1 0.8,0 1.7,-3.4 -0.4,-2.6 -1.3,0.2 0,0.6 -7.3,0.7 0,0.6 -2.2,0.1 -4.7,-2.2 -2.3,-1.7 -1.4,0 -0.2,-0.7 -0.8,-1.3 -1.3,0.9 1.1,0.4 -3.7,0.5 -1.6,0.6 0.6,1.3 -1.6,0 0,1.2 -1.1,0.3 -3.6,0.1 -2.3,1 -1.2,0.4 0,0.9 0.6,1.1 0,1.1 0.4,1.9 -0.4,0.4 -0.4,-0.4 -3.3,-1.1 -2.2,-0.4 -1,-1.5 0,-2.8 0,-2.2 0,-2.4 -2.1,0.5 -1,0.6 -1.6,0.4 -0.8,-1.3 0.8,-1.2 -0.8,1.2 0,1.5 0,0.7 -1,0.2 -1.1,0 -1.4,2.4 -0.8,0.6 0.8,0 1,0 -1,0.9 0.8,0.6 3.3,0.3 -1,0.6 0,2.8 -1.7,-0.4 -0.6,0.6 -1.6,-0.2 -0.8,-0.6 -1.2,-0.7 -1.7,0 -2,5.2 -1.7,0 2.1,5.4 3.4,0.6 4.1,-1 10.8,-1.1 -1.2,2.7 1.2,6.1 7.4,1.1 4.9,-0.2 4.7,1.1 3.4,-2.8 5.3,0.8 5.7,-3.4 3.5,-3.3 4.7,-0.6 3,-1.3 2.9,-0.6 z" /><g
         id="g924"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path926"
           inkscape:connector-curvature="0"
           d="m 403.2,484.2 -0.2,-1.1 -0.8,-0.4 -1,-0.3 0.8,-2.3 -0.8,-0.7 -3.4,0 -0.5,-3.2 0.6,-0.3 -0.6,-0.6 -1,0.2 -2.7,-1.1 0.8,0 1.7,-3.4 -0.4,-2.6 -1.3,0.2 0,0.6 -7.3,0.7 0,0.6 -2.2,0.1 -4.7,-2.2 -2.3,-1.7 -1.4,0 -0.2,-0.7 -0.8,-1.3 -1.3,0.9 1.1,0.4 -3.7,0.5 -1.6,0.6 0.6,1.3 -1.6,0 0,1.2 -1.1,0.3 -3.6,0.1 -2.3,1 -1.2,0.4 0,0.9 0.6,1.1 0,1.1 0.4,1.9 -0.4,0.4 -0.4,-0.4 -3.3,-1.1 -2.2,-0.4 -1,-1.5 0,-2.8 0,-2.2 0,-2.4 -2.1,0.5 -1,0.6 -1.6,0.4 -0.8,-1.3 0.8,-1.2 -0.8,1.2 0,1.5 0,0.7 -1,0.2 -1.1,0 -1.4,2.4 -0.8,0.6 0.8,0 1,0 -1,0.9 0.8,0.6 3.3,0.3 -1,0.6 0,2.8 -1.7,-0.4 -0.6,0.6 -1.6,-0.2 -0.8,-0.6 -1.2,-0.7 -1.7,0 -2,5.2 -1.7,0 2.1,5.4 3.4,0.6 4.1,-1 10.8,-1.1 -1.2,2.7 1.2,6.1 7.4,1.1 4.9,-0.2 4.7,1.1 3.4,-2.8 5.3,0.8 5.7,-3.4 3.5,-3.3 4.7,-0.6 3,-1.3 2.9,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path928"
         inkscape:connector-curvature="0"
         d="m 394.07474,494.1 -2.7,-1.4 -0.6,-0.9 -2,-0.2 -0.2,-0.9 -1.5,-0.4 -0.4,0.4 -0.8,-0.4 -1.6,-0.9 0.2,-1.2 -0.6,0 -5.3,1.5 1.4,0 0.6,0.6 1.7,1.3 -1.1,0 -2,0.7 -1.6,0 1,1.8 4.7,2.2 2.2,-0.2 0,-0.5 7.4,-0.8 0,-0.6 1.2,-0.1 z" /><g
         id="g930"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path932"
           inkscape:connector-curvature="0"
           d="m 395.7,468.5 -2.7,-1.4 -0.6,-0.9 -2,-0.2 -0.2,-0.9 -1.5,-0.4 -0.4,0.4 -0.8,-0.4 -1.6,-0.9 0.2,-1.2 -0.6,0 -5.3,1.5 1.4,0 0.6,0.6 1.7,1.3 -1.1,0 -2,0.7 -1.6,0 1,1.8 4.7,2.2 2.2,-0.2 0,-0.5 7.4,-0.8 0,-0.6 1.2,-0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path934"
         inkscape:connector-curvature="0"
         d="m 372.67474,483.4 -1.4,0.4 0.4,0.8 -0.4,0.7 -1.2,0 -4.7,3.1 -0.7,-0.1 -0.6,0.9 -1.4,1.7 -0.8,-0.2 -0.8,0 -0.2,1.7 -0.7,0.3 -1.8,0.6 -0.2,1.5 -1.4,0 -0.2,0.9 0,1 -1,-1 -0.4,-0.9 0.8,-2.6 -2.1,-0.4 -1,-0.2 0,2.4 0,2.3 0,2.8 1,1.5 2.3,0.3 -0.6,-3.9 1,0.2 0.6,-0.5 1,0 1.1,0.3 1.2,-0.3 2.2,-1 3.6,-0.1 1.1,-0.4 0,-1.2 1.6,0 -0.6,-1.3 1.7,-0.5 3.6,-0.6 -1,-0.4 1.2,-0.8 0.8,1.2 0.2,0.8 1.5,0 2.2,1.6 -1,-1.6 1.6,0 2,-0.8 1.1,0 -1.6,-1.2 -0.6,-0.6 -1.5,0 0,0.9 0.6,0.5 -1,0.6 -1.2,-0.2 -0.6,-2.7 -1.5,0 -1.6,-0.6 0,-0.6 0.6,-0.4 1,0 -1.2,-2 -1,-1.9 z" /><g
         id="g936"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path938"
           inkscape:connector-curvature="0"
           d="m 374.3,457.8 -1.4,0.4 0.4,0.8 -0.4,0.7 -1.2,0 -4.7,3.1 -0.7,-0.1 -0.6,0.9 -1.4,1.7 -0.8,-0.2 -0.8,0 -0.2,1.7 -0.7,0.3 -1.8,0.6 -0.2,1.5 -1.4,0 -0.2,0.9 0,1 -1,-1 -0.4,-0.9 0.8,-2.6 -2.1,-0.4 -1,-0.2 0,2.4 0,2.3 0,2.8 1,1.5 2.3,0.3 -0.6,-3.9 1,0.2 0.6,-0.5 1,0 1.1,0.3 1.2,-0.3 2.2,-1 3.6,-0.1 1.1,-0.4 0,-1.2 1.6,0 -0.6,-1.3 1.7,-0.5 3.6,-0.6 -1,-0.4 1.2,-0.8 0.8,1.2 0.2,0.8 1.5,0 2.2,1.6 -1,-1.6 1.6,0 2,-0.8 1.1,0 -1.6,-1.2 -0.6,-0.6 -1.5,0 0,0.9 0.6,0.5 -1,0.6 -1.2,-0.2 -0.6,-2.7 -1.5,0 -1.6,-0.6 0,-0.6 0.6,-0.4 1,0 -1.2,-2 -1,-1.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path940"
         inkscape:connector-curvature="0"
         d="m 383.87474,488.3 0.6,-1.5 0.7,0 0.9,-0.5 1.1,-2 -0.7,-0.6 -0.5,0.2 -0.4,0 -1.1,0.4 -1.2,-0.6 -1,-0.7 -1.1,-0.6 -0.6,2.8 -0.4,0.2 -1,0.8 -0.6,-1.4 0.4,-0.3 -1.7,-0.6 -0.4,-0.5 -0.4,0.3 -1.6,-0.9 -2.3,0.6 1.1,1.8 1.2,2.1 -1.1,0 -0.5,0.4 0,0.6 1.6,0.5 1.4,0 0.6,2.7 1.3,0.3 1,-0.6 -0.6,-0.5 0,-1 5.3,-1.4 z" /><g
         id="g942"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path944"
           inkscape:connector-curvature="0"
           d="m 385.5,462.7 0.6,-1.5 0.7,0 0.9,-0.5 1.1,-2 -0.7,-0.6 -0.5,0.2 -0.4,0 -1.1,0.4 -1.2,-0.6 -1,-0.7 -1.1,-0.6 -0.6,2.8 -0.4,0.2 -1,0.8 -0.6,-1.4 0.4,-0.3 -1.7,-0.6 -0.4,-0.5 -0.4,0.3 -1.6,-0.9 -2.3,0.6 1.1,1.8 1.2,2.1 -1.1,0 -0.5,0.4 0,0.6 1.6,0.5 1.4,0 0.6,2.7 1.3,0.3 1,-0.6 -0.6,-0.5 0,-1 5.3,-1.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path946"
         inkscape:connector-curvature="0"
         d="m 355.07474,494.8 0.4,0.9 1,0.9 0,-0.9 0.2,-0.9 1.4,0 0.2,-1.5 2.5,-0.9 0.2,-1.7 0.8,0 0.8,0.2 1.4,-1.5 0.7,-0.9 0.6,0 3.7,-2.2 1,-0.9 1.2,0 0.4,-0.8 -0.4,-0.7 -1.2,-0.4 0,-1.5 -2.6,-1.3 0.4,-0.1 -1.7,-1.4 0,0.8 -2.1,0.6 -0.4,1.8 -4.2,1.7 -2.3,0.5 -0.4,0.8 -1.6,-0.4 -1.2,0 -1,-1.5 -1.7,-0.4 -0.4,3.2 -1.6,0.7 0,0.8 1,0 -0.6,0.5 -1.1,-0.4 -0.4,2.2 0.4,0 1.1,0 1.2,-0.3 0.4,0.3 0,0.6 -1,0.6 -0.6,1.1 -1.1,0.3 -0.6,-0.9 0.2,-1.1 -0.8,1.1 0.8,1.3 1.7,-0.4 1,-0.5 2.1,-0.6 1,0.2 2,0.4 -0.8,2.6 z" /><g
         id="g948"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path950"
           inkscape:connector-curvature="0"
           d="m 356.7,469.2 0.4,0.9 1,0.9 0,-0.9 0.2,-0.9 1.4,0 0.2,-1.5 2.5,-0.9 0.2,-1.7 0.8,0 0.8,0.2 1.4,-1.5 0.7,-0.9 0.6,0 3.7,-2.2 1,-0.9 1.2,0 0.4,-0.8 -0.4,-0.7 -1.2,-0.4 0,-1.5 -2.6,-1.3 0.4,-0.1 -1.7,-1.4 0,0.8 -2.1,0.6 -0.4,1.8 -4.2,1.7 -2.3,0.5 -0.4,0.8 -1.6,-0.4 -1.2,0 -1,-1.5 -1.7,-0.4 -0.4,3.2 -1.6,0.7 0,0.8 1,0 -0.6,0.5 -1.1,-0.4 -0.4,2.2 0.4,0 1.1,0 1.2,-0.3 0.4,0.3 0,0.6 -1,0.6 -0.6,1.1 -1.1,0.3 -0.6,-0.9 0.2,-1.1 -0.8,1.1 0.8,1.3 1.7,-0.4 1,-0.5 2.1,-0.6 1,0.2 2,0.4 -0.8,2.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path952"
         inkscape:connector-curvature="0"
         d="m 303.57474,472.1 0.8,0 1.5,0 4.2,-0.9 2.3,0.1 0,0.8 0.6,-0.4 0.4,0.4 1.4,-0.4 0.7,-0.5 0,0.5 0.2,0 0.4,-0.4 1.2,0 1.8,0.4 0.2,1 0.4,-0.6 -0.4,-0.8 0.6,-0.7 0.4,-0.9 0,-1.3 -0.4,0 0,-0.6 -0.2,-1.4 -0.4,-0.6 -1.6,1.1 -0.4,0.9 -0.2,0.4 -0.6,0.8 -0.4,0 0,-0.2 0.4,-0.6 1.6,-2.2 0,-0.6 1,-1.4 0.8,-1.5 2.1,-3.4 0.4,-0.3 -0.4,0 0.6,-1.7 0.8,-0.4 0.2,-0.6 0.6,-0.5 -20,0.2 -0.6,12.4 -0.4,1.5 0.4,0.9 -0.4,1.1 0.4,0.4 z" /><g
         id="g954"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path956"
           inkscape:connector-curvature="0"
           d="m 305.2,446.5 0.8,0 1.5,0 4.2,-0.9 2.3,0.1 0,0.8 0.6,-0.4 0.4,0.4 1.4,-0.4 0.7,-0.5 0,0.5 0.2,0 0.4,-0.4 1.2,0 1.8,0.4 0.2,1 0.4,-0.6 -0.4,-0.8 0.6,-0.7 0.4,-0.9 0,-1.3 -0.4,0 0,-0.6 -0.2,-1.4 -0.4,-0.6 -1.6,1.1 -0.4,0.9 -0.2,0.4 -0.6,0.8 -0.4,0 0,-0.2 0.4,-0.6 1.6,-2.2 0,-0.6 1,-1.4 0.8,-1.5 2.1,-3.4 0.4,-0.3 -0.4,0 0.6,-1.7 0.8,-0.4 0.2,-0.6 0.6,-0.5 -20,0.2 -0.6,12.4 -0.4,1.5 0.4,0.9 -0.4,1.1 0.4,0.4 z" /></g><path
         style="fill:#d99594;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="DZ"
         inkscape:connector-curvature="0"
         d="m 257.07474,478.5 0.6,0 0.7,0.5 0.9,0.2 0.7,0.4 0.6,0.3 1,0.6 2.1,0.5 1.6,0 1,0.4 0.6,0.2 1.5,0 1,-0.6 0.6,0 0.6,0.4 1,0.6 0.2,0 0.8,-0.4 0.2,0.4 1.4,-0.6 1.3,0.2 0,-0.2 -0.6,-0.4 0,-0.4 0,-1 0,-1.5 -1.2,-1.5 0.5,-1.3 0.7,-0.2 0.4,-1.3 1.2,-0.6 0.6,-3.3 0.5,-2.1 0.4,-2.1 -0.4,-0.8 0.4,-1.5 -0.9,-0.8 1.1,-1.5 0,-0.5 0.6,-1 0.8,0.4 1.2,-0.6 1,-1.2 -1.6,-1 -6.3,-3.1 -2.7,-2.3 -2.8,-0.9 -1.7,-0.2 -0.4,0.2 0.4,0.7 -0.4,0.6 -0.6,0.4 -0.8,0.1 -0.8,0.4 -1,0.6 0,0.4 -10.7,6.8 -6.5,4.1 0,2.4 0.6,0.4 1.7,1 1,0 0.6,0.1 0.6,-0.1 1.4,0.1 2.1,1.7 0.6,0.6 0,0.6 0,0.3 1.2,0.2 0,0.5 3.1,0.4 0,0.4 0,0.6 -0.4,0.5 -0.3,1.3 0,0.8 -0.4,0.9 -0.6,0.8 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g960"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path962"
           inkscape:connector-curvature="0"
           d="m 258.7,452.9 0.6,0 0.7,0.5 0.9,0.2 0.7,0.4 0.6,0.3 1,0.6 2.1,0.5 1.6,0 1,0.4 0.6,0.2 1.5,0 1,-0.6 0.6,0 0.6,0.4 1,0.6 0.2,0 0.8,-0.4 0.2,0.4 1.4,-0.6 1.3,0.2 0,-0.2 -0.6,-0.4 0,-0.4 0,-1 0,-1.5 -1.2,-1.5 0.5,-1.3 0.7,-0.2 0.4,-1.3 1.2,-0.6 0.6,-3.3 0.5,-2.1 0.4,-2.1 -0.4,-0.8 0.4,-1.5 -0.9,-0.8 1.1,-1.5 0,-0.5 0.6,-1 0.8,0.4 1.2,-0.6 1,-1.2 -1.6,-1 -6.3,-3.1 -2.7,-2.3 -2.8,-0.9 -1.7,-0.2 -0.4,0.2 0.4,0.7 -0.4,0.6 -0.6,0.4 -0.8,0.1 -0.8,0.4 -1,0.6 0,0.4 -10.7,6.8 -6.5,4.1 0,2.4 0.6,0.4 1.7,1 1,0 0.6,0.1 0.6,-0.1 1.4,0.1 2.1,1.7 0.6,0.6 0,0.6 0,0.3 1.2,0.2 0,0.5 3.1,0.4 0,0.4 0,0.6 -0.4,0.5 -0.3,1.3 0,0.8 -0.4,0.9 -0.6,0.8 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path964"
         inkscape:connector-curvature="0"
         d="m 283.37474,410.5 0.5,0 0.7,-0.3 0.8,0.3 3.3,0 1,0 0.2,-0.8 0,-0.3 0.4,-0.6 0,-0.4 1.1,-1.4 1.6,0 0.1,0.3 1.5,0 0,0.5 0.4,0 0,0.6 0.8,0.4 1.1,0.2 0,-1 1.6,0 0.4,-1 0.4,-1 -0.8,-1.1 0.8,-0.8 0.3,-0.2 0,-1.3 0,-0.5 0.4,-0.4 0.1,0.4 0.9,0 1,0.3 0.6,-0.3 0,0.3 0,-1.8 0,-1.1 0,-0.4 0,-0.4 -3.3,0 -0.4,-5 1.7,-1.3 0.6,-1.1 -3.3,-0.6 -0.6,0 -0.7,0 -1,0 -2.5,0.2 -0.5,0.4 -0.3,0.6 -6.9,-0.2 -0.4,0 -1.1,0.5 -0.5,0.2 -1.1,-0.5 -0.1,0 -0.9,0 -0.2,0 0,1.2 -0.4,0.8 0.6,0.3 0.9,1.6 0,1.5 0.5,1.3 0.2,0.5 0.9,0.6 0.6,0.4 0.2,1.4 -0.2,1.1 -0.6,1.3 -0.4,1.5 0.5,0.6 -1,2.8 -0.7,1.4 0.7,0 0.5,0.3 z" /><g
         id="g966"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path968"
           inkscape:connector-curvature="0"
           d="m 285,384.9 0.5,0 0.7,-0.3 0.8,0.3 3.3,0 1,0 0.2,-0.8 0,-0.3 0.4,-0.6 0,-0.4 1.1,-1.4 1.6,0 0.1,0.3 1.5,0 0,0.5 0.4,0 0,0.6 0.8,0.4 1.1,0.2 0,-1 1.6,0 0.4,-1 0.4,-1 -0.8,-1.1 0.8,-0.8 0.3,-0.2 0,-1.3 0,-0.5 0.4,-0.4 0.1,0.4 0.9,0 1,0.3 0.6,-0.3 0,0.3 0,-1.8 0,-1.1 0,-0.4 0,-0.4 -3.3,0 -0.4,-5 1.7,-1.3 0.6,-1.1 -3.3,-0.6 -0.6,0 -0.7,0 -1,0 -2.5,0.2 -0.5,0.4 -0.3,0.6 -6.9,-0.2 -0.4,0 -1.1,0.5 -0.5,0.2 -1.1,-0.5 -0.1,0 -0.9,0 -0.2,0 0,1.2 -0.4,0.8 0.6,0.3 0.9,1.6 0,1.5 0.5,1.3 0.2,0.5 0.9,0.6 0.6,0.4 0.2,1.4 -0.2,1.1 -0.6,1.3 -0.4,1.5 0.5,0.6 -1,2.8 -0.7,1.4 0.7,0 0.5,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path970"
         inkscape:connector-curvature="0"
         d="m 281.77474,411.7 0.6,0.6 0,0.3 0.7,0.3 0.4,-0.3 0,-0.3 -0.6,-0.6 0,-0.9 -0.6,0 0,0.3 -0.5,0.6 z" /><g
         id="g972"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path974"
           inkscape:connector-curvature="0"
           d="m 283.4,386.1 0.6,0.6 0,0.3 0.7,0.3 0.4,-0.3 0,-0.3 -0.6,-0.6 0,-0.9 -0.6,0 0,0.3 -0.5,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path976"
         inkscape:connector-curvature="0"
         d="m 327.77474,448.9 0.6,-1.8 0.9,-1.8 0.6,-1.2 0.2,0 0,0.7 0.8,-0.9 0.2,0 1,-0.4 1.4,-1.1 0.9,-1.3 0.9,-0.6 0.4,0 0.5,-0.4 -0.9,-0.1 -0.5,-0.4 -0.4,-0.6 -0.7,-0.5 0.4,-1 1.2,0 0.4,-0.4 -0.4,-0.5 1.5,-1.5 1,-0.5 5.4,-1.9 1.6,0 -5.1,-4.8 -0.7,0 -2.2,-0.6 -0.6,-0.9 -1,0 -0.8,-0.4 -1.3,0 -0.6,0.4 -1,0 -0.6,0 -0.8,-1.2 -1.9,0.3 -1.6,0.9 -2,0.5 -1,0.4 0,1.1 -0.7,0 -0.4,0.8 -0.2,0.7 -1.4,0.9 -1.1,1 -1.2,0.5 0,1 1.2,0 0.9,0.3 0,1.7 0.2,0.7 0,0.6 0.4,0.5 0.6,0 0.4,0.4 0.2,1.5 0.8,1.1 0.7,0.4 0.2,1 0.4,2 0.6,2 0,1 0.4,0.9 0.6,0 0,0.4 0.6,0.6 0.8,-0.4 0.2,0.9 z" /><g
         id="g978"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path980"
           inkscape:connector-curvature="0"
           d="m 329.4,423.3 0.6,-1.8 0.9,-1.8 0.6,-1.2 0.2,0 0,0.7 0.8,-0.9 0.2,0 1,-0.4 1.4,-1.1 0.9,-1.3 0.9,-0.6 0.4,0 0.5,-0.4 -0.9,-0.1 -0.5,-0.4 -0.4,-0.6 -0.7,-0.5 0.4,-1 1.2,0 0.4,-0.4 -0.4,-0.5 1.5,-1.5 1,-0.5 5.4,-1.9 1.6,0 -5.1,-4.8 -0.7,0 -2.2,-0.6 -0.6,-0.9 -1,0 -0.8,-0.4 -1.3,0 -0.6,0.4 -1,0 -0.6,0 -0.8,-1.2 -1.9,0.3 -1.6,0.9 -2,0.5 -1,0.4 0,1.1 -0.7,0 -0.4,0.8 -0.2,0.7 -1.4,0.9 -1.1,1 -1.2,0.5 0,1 1.2,0 0.9,0.3 0,1.7 0.2,0.7 0,0.6 0.4,0.5 0.6,0 0.4,0.4 0.2,1.5 0.8,1.1 0.7,0.4 0.2,1 0.4,2 0.6,2 0,1 0.4,0.9 0.6,0 0,0.4 0.6,0.6 0.8,-0.4 0.2,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path982"
         inkscape:connector-curvature="0"
         d="m 264.87474,439.3 0.4,0.7 0.4,0 0.6,-0.7 0.7,-0.6 0,-0.2 0.4,-1.3 -0.4,-0.6 0,-0.5 -0.2,-0.4 -0.5,-0.9 -0.6,-0.2 0,-0.9 0,-1.9 0.2,-1.5 -1,0 -1.3,-0.4 0,0.8 0,0.2 0,3.9 -0.4,0.6 -0.2,0.7 -0.6,0.5 -0.4,1 0.7,0.2 0.5,0.3 1.1,0.4 0.6,0.8 z" /><g
         id="g984"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path986"
           inkscape:connector-curvature="0"
           d="m 266.5,413.7 0.4,0.7 0.4,0 0.6,-0.7 0.7,-0.6 0,-0.2 0.4,-1.3 -0.4,-0.6 0,-0.5 -0.2,-0.4 -0.5,-0.9 -0.6,-0.2 0,-0.9 0,-1.9 0.2,-1.5 -1,0 -1.3,-0.4 0,0.8 0,0.2 0,3.9 -0.4,0.6 -0.2,0.7 -0.6,0.5 -0.4,1 0.7,0.2 0.5,0.3 1.1,0.4 0.6,0.8 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path988"
         inkscape:connector-curvature="0"
         d="m 304.37474,391 0.4,-1 1.1,-1.1 0,-0.6 1,-0.5 1,-0.4 0.2,-0.9 0.4,0 0.3,-1.1 0.4,-0.8 0.5,0 1.1,-0.2 0,-0.9 -0.7,-0.6 -0.6,0 -1,-0.9 -0.4,0 -0.2,-0.4 -0.4,-0.1 -0.6,-0.8 0,-0.8 -0.6,-0.4 -1,-0.3 -0.9,-1.5 -0.8,0 -1,0 -1.1,0 -1,0.6 -1,0 0,-1.2 -1,-0.9 -0.7,0 0,-0.2 -1,0 -1,-0.4 0,1 0.4,0.5 -0.4,0.6 -0.2,0.9 -0.6,0.6 0.2,4.9 1.6,0 0.4,5.9 2.3,0.4 1.4,0.4 0.6,-0.8 1.3,0.8 0.4,0 0.6,0.2 0.6,0 z" /><g
         id="g990"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path992"
           inkscape:connector-curvature="0"
           d="m 306,365.4 0.4,-1 1.1,-1.1 0,-0.6 1,-0.5 1,-0.4 0.2,-0.9 0.4,0 0.3,-1.1 0.4,-0.8 0.5,0 1.1,-0.2 0,-0.9 -0.7,-0.6 -0.6,0 -1,-0.9 -0.4,0 -0.2,-0.4 -0.4,-0.1 -0.6,-0.8 0,-0.8 -0.6,-0.4 -1,-0.3 -0.9,-1.5 -0.8,0 -1,0 -1.1,0 -1,0.6 -1,0 0,-1.2 -1,-0.9 -0.7,0 0,-0.2 -1,0 -1,-0.4 0,1 0.4,0.5 -0.4,0.6 -0.2,0.9 -0.6,0.6 0.2,4.9 1.6,0 0.4,5.9 2.3,0.4 1.4,0.4 0.6,-0.8 1.3,0.8 0.4,0 0.6,0.2 0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path994"
         inkscape:connector-curvature="0"
         d="m 260.97474,444.1 0,-0.6 0.5,-0.6 0.1,-0.5 0.9,-0.8 0.6,0 0,-0.2 -0.6,0 0,-0.3 1.2,-0.6 0.6,0 0.4,-0.4 -0.4,-0.1 0.7,-0.8 -0.7,-0.7 -1,-0.4 -0.6,-0.4 -0.6,-0.2 -0.6,0 -0.5,0 -0.4,0.2 -0.6,0 -0.1,-0.2 -4.2,0.2 -0.2,-0.6 0.2,-1.1 0,-0.9 -1.2,0.9 -1,0 -0.5,-0.7 -0.6,0 -0.6,0.9 -0.6,0.4 0,0.4 0,0.5 0.2,0.6 -0.2,0.9 0.8,0.2 0.4,0 0.6,0.8 0,0.5 0.5,0 -0.5,0.9 0.9,0.2 0.6,-0.5 0.6,0.3 -0.4,0.6 0.8,0 0.2,0.4 1.5,0.5 0.2,1 1.6,0 0.4,0.5 1.6,-0.3 z" /><g
         id="g996"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path998"
           inkscape:connector-curvature="0"
           d="m 262.6,418.5 0,-0.6 0.5,-0.6 0.1,-0.5 0.9,-0.8 0.6,0 0,-0.2 -0.6,0 0,-0.3 1.2,-0.6 0.6,0 0.4,-0.4 -0.4,-0.1 0.7,-0.8 -0.7,-0.7 -1,-0.4 -0.6,-0.4 -0.6,-0.2 -0.6,0 -0.5,0 -0.4,0.2 -0.6,0 -0.1,-0.2 -4.2,0.2 -0.2,-0.6 0.2,-1.1 0,-0.9 -1.2,0.9 -1,0 -0.5,-0.7 -0.6,0 -0.6,0.9 -0.6,0.4 0,0.4 0,0.5 0.2,0.6 -0.2,0.9 0.8,0.2 0.4,0 0.6,0.8 0,0.5 0.5,0 -0.5,0.9 0.9,0.2 0.6,-0.5 0.6,0.3 -0.4,0.6 0.8,0 0.2,0.4 1.5,0.5 0.2,1 1.6,0 0.4,0.5 1.6,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1000"
         inkscape:connector-curvature="0"
         d="m 311.37474,415.7 1,0.2 0,-0.6 0.6,0.4 0.4,0.2 0.6,0.4 0.4,-0.6 0,-0.4 0.7,-0.2 0,-0.7 -0.7,-0.6 -1,-0.9 -1,-0.3 0,0.3 -0.2,0.9 0,0.6 0,0.4 -0.4,0.3 -0.4,0.6 z" /><g
         id="g1002"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1004"
           inkscape:connector-curvature="0"
           d="m 313,390.1 1,0.2 0,-0.6 0.6,0.4 0.4,0.2 0.6,0.4 0.4,-0.6 0,-0.4 0.7,-0.2 0,-0.7 -0.7,-0.6 -1,-0.9 -1,-0.3 0,0.3 -0.2,0.9 0,0.6 0,0.4 -0.4,0.3 -0.4,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1006"
         inkscape:connector-curvature="0"
         d="m 336.17474,440.1 0.2,-1.1 -1.2,-0.5 0,-0.4 1,0.4 0.2,-0.4 -0.2,-0.4 -0.5,-0.6 -0.4,0.4 -1.1,0 -0.4,1 0.6,0.5 0.4,0.6 0.5,0.4 0.9,0.1 z" /><g
         id="g1008"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1010"
           inkscape:connector-curvature="0"
           d="m 337.8,414.5 0.2,-1.1 -1.2,-0.5 0,-0.4 1,0.4 0.2,-0.4 -0.2,-0.4 -0.5,-0.6 -0.4,0.4 -1.1,0 -0.4,1 0.6,0.5 0.4,0.6 0.5,0.4 0.9,0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1012"
         inkscape:connector-curvature="0"
         d="m 246.87474,436.3 0.6,0.8 0.2,-0.4 0.9,-0.4 0,0.4 0.2,0.4 0.4,0.1 0.4,0 0.2,-0.5 0.6,0 0.8,0.4 0,-0.4 0.6,-0.4 0.6,-0.9 0.7,0 0.4,0.7 1,0 1.2,-0.9 0,-0.4 0.4,-1.1 0,-0.6 -0.6,-0.4 0,-0.8 -0.9,-1 0.9,-1.5 0.2,-0.9 0,-0.6 -0.6,0.4 0,0.5 -0.5,-0.5 -0.7,0.2 -0.8,-0.2 -1.9,0 -1.6,-0.4 -2.1,-0.9 -0.4,1.3 0.4,1.1 -0.4,0.4 -0.2,0.1 -0.8,0 -0.2,0.8 0,0.2 0,0.6 0,0.7 0.2,0 0.5,0.9 -0.5,0.2 0.5,0.4 0.5,0 -0.2,0.6 0,0.3 0,0.6 -0.3,0.2 0,0.7 0.3,0.2 z" /><g
         id="g1014"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1016"
           inkscape:connector-curvature="0"
           d="m 248.5,410.7 0.6,0.8 0.2,-0.4 0.9,-0.4 0,0.4 0.2,0.4 0.4,0.1 0.4,0 0.2,-0.5 0.6,0 0.8,0.4 0,-0.4 0.6,-0.4 0.6,-0.9 0.7,0 0.4,0.7 1,0 1.2,-0.9 0,-0.4 0.4,-1.1 0,-0.6 -0.6,-0.4 0,-0.8 -0.9,-1 0.9,-1.5 0.2,-0.9 0,-0.6 -0.6,0.4 0,0.5 -0.5,-0.5 -0.7,0.2 -0.8,-0.2 -1.9,0 -1.6,-0.4 -2.1,-0.9 -0.4,1.3 0.4,1.1 -0.4,0.4 -0.2,0.1 -0.8,0 -0.2,0.8 0,0.2 0,0.6 0,0.7 0.2,0 0.5,0.9 -0.5,0.2 0.5,0.4 0.5,0 -0.2,0.6 0,0.3 0,0.6 -0.3,0.2 0,0.7 0.3,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1018"
         inkscape:connector-curvature="0"
         d="m 280.67474,423.4 0,0.2 1.5,0 1.3,0 0.5,0 0.5,0 -0.5,-0.6 0,-1.1 0.6,0.2 0.9,0 0.2,-0.2 0.4,-0.5 -0.4,-0.4 -0.2,-0.4 0,-0.9 1,-0.6 0,-1.4 -0.4,-0.5 -0.4,-0.6 0,-0.8 -0.2,0 -0.4,0.8 -1.1,-0.4 -1.2,1 -0.4,-1 -0.6,0 -0.4,0.4 -0.3,-1 0.7,-0.3 0,-1.1 -0.4,0 -0.3,0.1 -0.4,-0.1 -0.5,-0.4 -1.7,1.5 -0.6,0.5 0.6,-0.2 0,0.2 -1,0.4 -0.5,0.6 0.5,0 -0.5,0.4 0.5,0.3 -0.5,0.2 -0.1,-0.2 -0.4,0.8 -0.5,0.7 0.9,0 0.1,1.1 0.5,-0.4 0.6,0.4 -1.1,0.3 0,0.6 0.5,0 0,0.5 1.2,0 1.1,0 0.9,0 0,1.9 z" /><g
         id="g1020"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1022"
           inkscape:connector-curvature="0"
           d="m 282.3,397.8 0,0.2 1.5,0 1.3,0 0.5,0 0.5,0 -0.5,-0.6 0,-1.1 0.6,0.2 0.9,0 0.2,-0.2 0.4,-0.5 -0.4,-0.4 -0.2,-0.4 0,-0.9 1,-0.6 0,-1.4 -0.4,-0.5 -0.4,-0.6 0,-0.8 -0.2,0 -0.4,0.8 -1.1,-0.4 -1.2,1 -0.4,-1 -0.6,0 -0.4,0.4 -0.3,-1 0.7,-0.3 0,-1.1 -0.4,0 -0.3,0.1 -0.4,-0.1 -0.5,-0.4 -1.7,1.5 -0.6,0.5 0.6,-0.2 0,0.2 -1,0.4 -0.5,0.6 0.5,0 -0.5,0.4 0.5,0.3 -0.5,0.2 -0.1,-0.2 -0.4,0.8 -0.5,0.7 0.9,0 0.1,1.1 0.5,-0.4 0.6,0.4 -1.1,0.3 0,0.6 0.5,0 0,0.5 1.2,0 1.1,0 0.9,0 0,1.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1024"
         inkscape:connector-curvature="0"
         d="m 231.47474,442 1.8,0.4 1.4,0 1,-0.4 1.1,0 -0.9,-0.6 -1.6,0.6 -0.6,-0.3 -0.6,0 0,-0.3 -2,-0.4 0,0.4 0.4,0.3 0,0.3 z" /><g
         id="g1026"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1028"
           inkscape:connector-curvature="0"
           d="m 233.1,416.4 1.8,0.4 1.4,0 1,-0.4 1.1,0 -0.9,-0.6 -1.6,0.6 -0.6,-0.3 -0.6,0 0,-0.3 -2,-0.4 0,0.4 0.4,0.3 0,0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1030"
         inkscape:connector-curvature="0"
         d="m 255.77474,435.2 0,0.9 -0.3,1.1 0.3,0.6 4,-0.3 0.2,0.3 0.6,0 0.4,-0.3 -0.4,-0.3 0.9,-0.5 0,-0.6 -0.5,-0.7 0.6,-0.2 0,-0.6 0,-0.4 0.5,-0.5 -0.5,-0.6 0,-0.7 0,-0.9 0.5,-1.2 0.6,-0.5 -0.2,-0.4 -1.5,-0.1 -1,-0.4 -1.8,-0.6 -1.1,-0.4 -1.3,0 0,0.6 -0.3,0.9 -0.8,1.5 0.8,1 0,0.9 0.7,0.3 0,0.6 -0.4,1.1 0,0.4 z" /><g
         id="g1032"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1034"
           inkscape:connector-curvature="0"
           d="m 257.4,409.6 0,0.9 -0.3,1.1 0.3,0.6 4,-0.3 0.2,0.3 0.6,0 0.4,-0.3 -0.4,-0.3 0.9,-0.5 0,-0.6 -0.5,-0.7 0.6,-0.2 0,-0.6 0,-0.4 0.5,-0.5 -0.5,-0.6 0,-0.7 0,-0.9 0.5,-1.2 0.6,-0.5 -0.2,-0.4 -1.5,-0.1 -1,-0.4 -1.8,-0.6 -1.1,-0.4 -1.3,0 0,0.6 -0.3,0.9 -0.8,1.5 0.8,1 0,0.9 0.7,0.3 0,0.6 -0.4,1.1 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1036"
         inkscape:connector-curvature="0"
         d="m 236.77474,440.5 1.2,0 0,-0.4 1,-0.1 0.6,0.1 0.4,0 1.1,0 0,-0.1 0.2,-0.4 0.4,0 0.6,-0.6 1,0.6 0.6,0 0.4,0.4 0.2,0.1 0.8,-1.1 0.2,-0.4 0.5,-0.1 0,-0.8 0.1,-0.2 0,-0.5 0.9,-0.8 -0.4,-0.2 0,-0.7 0.4,-0.2 0,-0.5 0,-0.4 0.2,-0.6 -0.6,0 -0.5,-0.3 0.5,-0.2 -0.5,-1 -0.1,0 -0.7,0.3 0,-0.6 -0.8,0 -0.6,0 0,0.9 -0.4,0.9 -0.8,0 -0.8,0 0,1 -0.2,0.2 0,0.5 -0.6,0.9 -1.9,-0.2 -0.2,0 -0.6,-0.9 -0.4,0 -0.6,-0.5 -0.4,0.5 -0.2,0.6 -1.5,0.9 -0.3,0.5 -0.7,0.4 0.7,1 1.3,0.5 0.5,0.6 -0.5,0.4 0.5,0.1 0,0.4 z" /><g
         id="g1038"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1040"
           inkscape:connector-curvature="0"
           d="m 238.4,414.9 1.2,0 0,-0.4 1,-0.1 0.6,0.1 0.4,0 1.1,0 0,-0.1 0.2,-0.4 0.4,0 0.6,-0.6 1,0.6 0.6,0 0.4,0.4 0.2,0.1 0.8,-1.1 0.2,-0.4 0.5,-0.1 0,-0.8 0.1,-0.2 0,-0.5 0.9,-0.8 -0.4,-0.2 0,-0.7 0.4,-0.2 0,-0.5 0,-0.4 0.2,-0.6 -0.6,0 -0.5,-0.3 0.5,-0.2 -0.5,-1 -0.1,0 -0.7,0.3 0,-0.6 -0.8,0 -0.6,0 0,0.9 -0.4,0.9 -0.8,0 -0.8,0 0,1 -0.2,0.2 0,0.5 -0.6,0.9 -1.9,-0.2 -0.2,0 -0.6,-0.9 -0.4,0 -0.6,-0.5 -0.4,0.5 -0.2,0.6 -1.5,0.9 -0.3,0.5 -0.7,0.4 0.7,1 1.3,0.5 0.5,0.6 -0.5,0.4 0.5,0.1 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1042"
         inkscape:connector-curvature="0"
         d="m 231.37474,440 1.2,0.1 0.7,0 0.8,0.4 2.7,0 0,-0.4 -0.5,-0.1 0.5,-0.4 -0.5,-0.6 -1.4,-0.5 -0.6,-1 -0.2,0.6 -0.4,-0.4 0,1.3 -1.1,0 0,0.2 -0.5,0.4 -0.7,0.4 z" /><g
         id="g1044"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1046"
           inkscape:connector-curvature="0"
           d="m 233,414.4 1.2,0.1 0.7,0 0.8,0.4 2.7,0 0,-0.4 -0.5,-0.1 0.5,-0.4 -0.5,-0.6 -1.4,-0.5 -0.6,-1 -0.2,0.6 -0.4,-0.4 0,1.3 -1.1,0 0,0.2 -0.5,0.4 -0.7,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1048"
         inkscape:connector-curvature="0"
         d="m 285.37474,441.1 0.7,0 0,-0.6 0.6,-0.4 0.3,-0.9 0,-0.7 0,-1 0.3,-0.9 0.8,-0.6 -1.6,0 -0.9,0 -0.2,-0.7 1.3,-1.1 0.6,-0.4 0.4,-1.1 0.4,-0.3 0,-0.6 -0.8,-0.4 -0.6,-1.5 -0.2,-0.1 0,-1.4 0.2,-0.2 0,-0.9 0.6,-0.4 0,-0.8 1.4,-1.2 0.2,0 -0.2,-0.9 0.2,-0.4 -2.2,0.4 -2.7,-0.4 -0.6,0 -1.2,0 -1.5,0 0,-0.2 -2.2,0.2 -0.6,0.4 0,1.3 0,0.8 -0.9,0.3 -0.1,0 -0.4,0.5 -0.6,0.4 -0.5,0 0.7,1.1 0.4,1.4 1,0.9 1,0.6 0.6,0 0.4,0.1 0.6,-0.6 0,-0.1 0.6,0 0.6,0.6 0.4,0 -0.4,0.5 1.1,1.8 0.6,0.6 0,0.9 1,0.2 -0.2,0.7 0.6,0.6 0,0.6 0.7,0.9 0.5,-0.4 0.9,0.9 -0.4,1 -0.7,0.4 0,1.1 z" /><g
         id="g1050"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1052"
           inkscape:connector-curvature="0"
           d="m 287,415.5 0.7,0 0,-0.6 0.6,-0.4 0.3,-0.9 0,-0.7 0,-1 0.3,-0.9 0.8,-0.6 -1.6,0 -0.9,0 -0.2,-0.7 1.3,-1.1 0.6,-0.4 0.4,-1.1 0.4,-0.3 0,-0.6 -0.8,-0.4 -0.6,-1.5 -0.2,-0.1 0,-1.4 0.2,-0.2 0,-0.9 0.6,-0.4 0,-0.8 1.4,-1.2 0.2,0 -0.2,-0.9 0.2,-0.4 -2.2,0.4 -2.7,-0.4 -0.6,0 -1.2,0 -1.5,0 0,-0.2 -2.2,0.2 -0.6,0.4 0,1.3 0,0.8 -0.9,0.3 -0.1,0 -0.4,0.5 -0.6,0.4 -0.5,0 0.7,1.1 0.4,1.4 1,0.9 1,0.6 0.6,0 0.4,0.1 0.6,-0.6 0,-0.1 0.6,0 0.6,0.6 0.4,0 -0.4,0.5 1.1,1.8 0.6,0.6 0,0.9 1,0.2 -0.2,0.7 0.6,0.6 0,0.6 0.7,0.9 0.5,-0.4 0.9,0.9 -0.4,1 -0.7,0.4 0,1.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1054"
         inkscape:connector-curvature="0"
         d="m 320.67474,426.5 0.2,0.5 1,0.4 1.1,0.6 0.3,-0.6 0.3,0 1,-0.4 2,-0.5 1.7,-0.9 1.8,-0.2 0.8,1.1 0.6,0 1,0 0.7,-0.4 1.2,0 -1.6,-1.6 0,-5.9 1,-1.1 -0.2,-0.7 -1.1,-0.6 0,-0.4 -0.6,-0.1 -0.8,-0.4 -0.2,-1 -0.8,-1.4 -0.6,-1 -2.3,1.9 0,0.9 -6.3,3.6 -0.6,0 0,1.9 1.2,1.8 0.8,1 -0.8,1.5 -0.1,0.9 0,0.5 -0.7,0.2 0,0.4 z" /><g
         id="g1056"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1058"
           inkscape:connector-curvature="0"
           d="m 322.3,400.9 0.2,0.5 1,0.4 1.1,0.6 0.3,-0.6 0.3,0 1,-0.4 2,-0.5 1.7,-0.9 1.8,-0.2 0.8,1.1 0.6,0 1,0 0.7,-0.4 1.2,0 -1.6,-1.6 0,-5.9 1,-1.1 -0.2,-0.7 -1.1,-0.6 0,-0.4 -0.6,-0.1 -0.8,-0.4 -0.2,-1 -0.8,-1.4 -0.6,-1 -2.3,1.9 0,0.9 -6.3,3.6 -0.6,0 0,1.9 1.2,1.8 0.8,1 -0.8,1.5 -0.1,0.9 0,0.5 -0.7,0.2 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1060"
         inkscape:connector-curvature="0"
         d="m 336.37474,400.2 -0.2,0.4 0,0.3 0,0.3 0.2,0 0,-1 z" /><g
         id="g1062"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1064"
           inkscape:connector-curvature="0"
           d="m 338,374.6 -0.2,0.4 0,0.3 0,0.3 0.2,0 0,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1066"
         inkscape:connector-curvature="0"
         d="m 288.97474,423.6 0.8,0.9 0.2,1.3 1.4,0 1.1,0 0.8,0 0,-0.9 -0.2,-0.6 -0.6,-1.3 0,-0.9 -0.4,-0.7 0,-1 0,-0.7 0,-0.6 -1.1,-0.9 -0.6,0 -0.4,-0.7 -0.2,-0.4 -0.8,-0.6 0,-0.9 0,-0.9 -0.3,-1 -0.6,-0.5 -0.8,-0.4 -0.2,-0.2 -0.6,-0.7 -0.4,0.7 -0.4,0.2 -0.7,0 -0.4,-0.6 -0.6,0 -0.2,0.4 -0.4,0 -0.5,0.2 -0.6,-0.2 0,-0.4 -0.6,-0.5 -0.4,0.5 -1.2,1.5 0.6,0.4 0.4,0.2 0.2,-0.2 0.4,0 0,1.1 -0.6,0.4 0.2,0.9 0.4,-0.3 0.6,0 0.5,0.9 1.2,-0.9 1,0.3 0.4,-0.7 0.3,0 0,0.7 0.4,0.6 0.4,0.5 0,1.5 -1.1,0.6 0,0.9 0.3,0.4 0.4,0.4 -0.4,0.6 -0.3,0.1 -0.8,0 -0.6,-0.1 0,1 0.4,0.6 -0.4,0 2.7,0.4 2.3,-0.4 z" /><g
         id="g1068"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1070"
           inkscape:connector-curvature="0"
           d="m 290.6,398 0.8,0.9 0.2,1.3 1.4,0 1.1,0 0.8,0 0,-0.9 -0.2,-0.6 -0.6,-1.3 0,-0.9 -0.4,-0.7 0,-1 0,-0.7 0,-0.6 -1.1,-0.9 -0.6,0 -0.4,-0.7 -0.2,-0.4 -0.8,-0.6 0,-0.9 0,-0.9 -0.3,-1 -0.6,-0.5 -0.8,-0.4 -0.2,-0.2 -0.6,-0.7 -0.4,0.7 -0.4,0.2 -0.7,0 -0.4,-0.6 -0.6,0 -0.2,0.4 -0.4,0 -0.5,0.2 -0.6,-0.2 0,-0.4 -0.6,-0.5 -0.4,0.5 -1.2,1.5 0.6,0.4 0.4,0.2 0.2,-0.2 0.4,0 0,1.1 -0.6,0.4 0.2,0.9 0.4,-0.3 0.6,0 0.5,0.9 1.2,-0.9 1,0.3 0.4,-0.7 0.3,0 0,0.7 0.4,0.6 0.4,0.5 0,1.5 -1.1,0.6 0,0.9 0.3,0.4 0.4,0.4 -0.4,0.6 -0.3,0.1 -0.8,0 -0.6,-0.1 0,1 0.4,0.6 -0.4,0 2.7,0.4 2.3,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1072"
         inkscape:connector-curvature="0"
         d="m 242.57474,433.7 0.9,0 0.4,-0.9 0,-0.9 0.6,0 0.8,0 0,0.5 0.7,-0.2 0,-0.7 0,-0.6 0,-0.1 0.1,-0.8 0.8,0 0.3,-0.2 0.4,-0.4 -0.4,-1.1 0.4,-1.3 -1.6,0.3 -3.1,2.1 -1.9,1.5 0.7,1 0.5,0.5 0,0.8 0.4,0 0,0.5 z" /><g
         id="g1074"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1076"
           inkscape:connector-curvature="0"
           d="m 244.2,408.1 0.9,0 0.4,-0.9 0,-0.9 0.6,0 0.8,0 0,0.5 0.7,-0.2 0,-0.7 0,-0.6 0,-0.1 0.1,-0.8 0.8,0 0.3,-0.2 0.4,-0.4 -0.4,-1.1 0.4,-1.3 -1.6,0.3 -3.1,2.1 -1.9,1.5 0.7,1 0.5,0.5 0,0.8 0.4,0 0,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1078"
         inkscape:connector-curvature="0"
         d="m 277.07474,469.9 0.4,0 1,0.9 -0.4,1.3 1.4,1.1 1.3,0.4 -0.4,0.6 0,0.5 1.8,-0.2 1.8,-0.3 1,-0.4 1.7,-0.2 0.6,-1.5 2.6,-0.8 3.7,-1.6 1,0.9 0.4,0.7 -0.4,1.6 0.6,0.9 1.4,0.4 0.3,0 0.4,0.3 1.2,0 1,-0.3 0.4,-0.6 1.6,-0.7 2,-0.8 -0.3,-0.3 0.3,-1.2 -0.3,-0.9 0.3,-1.5 0.7,-12.4 0,-3.5 -1.6,0 0,-0.6 -13.9,6.3 -2,-0.7 -1.3,-0.5 -1.4,0.8 -2.2,0.6 -1,1.3 -1.3,0.5 -0.8,-0.3 -0.6,0.9 0,0.6 -1,1.4 0.8,0.8 -0.4,1.5 0.4,0.9 -0.4,2 -0.4,2.1 z" /><g
         id="g1080"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1082"
           inkscape:connector-curvature="0"
           d="m 278.7,444.3 0.4,0 1,0.9 -0.4,1.3 1.4,1.1 1.3,0.4 -0.4,0.6 0,0.5 1.8,-0.2 1.8,-0.3 1,-0.4 1.7,-0.2 0.6,-1.5 2.6,-0.8 3.7,-1.6 1,0.9 0.4,0.7 -0.4,1.6 0.6,0.9 1.4,0.4 0.3,0 0.4,0.3 1.2,0 1,-0.3 0.4,-0.6 1.6,-0.7 2,-0.8 -0.3,-0.3 0.3,-1.2 -0.3,-0.9 0.3,-1.5 0.7,-12.4 0,-3.5 -1.6,0 0,-0.6 -13.9,6.3 -2,-0.7 -1.3,-0.5 -1.4,0.8 -2.2,0.6 -1,1.3 -1.3,0.5 -0.8,-0.3 -0.6,0.9 0,0.6 -1,1.4 0.8,0.8 -0.4,1.5 0.4,0.9 -0.4,2 -0.4,2.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1084"
         inkscape:connector-curvature="0"
         d="m 338.37474,377.2 -0.6,0.5 -1.4,0.4 -0.7,1.1 0,1.9 -0.4,0.5 -0.1,1.9 0.5,0.9 0.5,0.2 1.6,2.2 0,0.2 0,0.7 -0.4,0.6 -0.4,0.6 0,1.5 0.8,2 0.2,0.7 0.8,0.2 0.7,0.4 0.6,-0.4 0,0.4 0.6,0.2 0.6,-0.2 -0.2,0.6 1.2,0.5 0,-0.5 0.9,0.3 -0.5,0.2 0.7,0.9 0,-0.5 0.6,0.5 0.4,0 -0.6,0.4 0.2,0.6 0.4,-0.4 0,0.4 -0.4,0.9 0.8,0 0,-0.4 0.6,0.6 0.2,-0.2 0.5,0.9 -0.5,1 0.9,0.1 0.2,0.6 0,-0.6 0.4,0 0,-0.5 0.4,-0.4 0.8,-2.4 0.4,-1.5 -0.6,-1.1 -0.6,0.6 -0.4,0 0,-0.6 0.4,-0.9 -0.8,-1.1 0,-0.8 -1.3,-3.5 -2,-4.3 -2.1,-4.8 -0.8,-0.6 -1,-0.2 -1.1,-0.3 z" /><g
         id="g1086"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1088"
           inkscape:connector-curvature="0"
           d="m 340,351.6 -0.6,0.5 -1.4,0.4 -0.7,1.1 0,1.9 -0.4,0.5 -0.1,1.9 0.5,0.9 0.5,0.2 1.6,2.2 0,0.2 0,0.7 -0.4,0.6 -0.4,0.6 0,1.5 0.8,2 0.2,0.7 0.8,0.2 0.7,0.4 0.6,-0.4 0,0.4 0.6,0.2 0.6,-0.2 -0.2,0.6 1.2,0.5 0,-0.5 0.9,0.3 -0.5,0.2 0.7,0.9 0,-0.5 0.6,0.5 0.4,0 -0.6,0.4 0.2,0.6 0.4,-0.4 0,0.4 -0.4,0.9 0.8,0 0,-0.4 0.6,0.6 0.2,-0.2 0.5,0.9 -0.5,1 0.9,0.1 0.2,0.6 0,-0.6 0.4,0 0,-0.5 0.4,-0.4 0.8,-2.4 0.4,-1.5 -0.6,-1.1 -0.6,0.6 -0.4,0 0,-0.6 0.4,-0.9 -0.8,-1.1 0,-0.8 -1.3,-3.5 -2,-4.3 -2.1,-4.8 -0.8,-0.6 -1,-0.2 -1.1,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1090"
         inkscape:connector-curvature="0"
         d="m 318.17474,404.9 1,-0.4 0.7,-0.3 1,-2.1 0,-0.9 0.4,-0.2 -0.4,-1 0,-2.2 0.6,0 0.9,-0.6 0.6,-1.1 0.6,-0.4 -0.2,-0.9 -0.4,-1.1 -0.5,-0.4 -0.1,0 0,-0.9 -0.4,-0.6 -0.5,0.6 -0.6,0.9 -0.2,0.6 0.2,0.9 0,1.3 -0.6,0.2 -1.1,-0.2 -0.6,1.1 -0.4,-0.1 -0.2,0.7 0.2,0.8 0,0.9 0.9,0 0.1,0.1 -0.6,0.6 0.5,0.4 0,1.1 0,0.8 0.5,0.2 0,0.3 -0.4,0.6 -0.6,0.6 -0.4,0.7 z" /><g
         id="g1092"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1094"
           inkscape:connector-curvature="0"
           d="m 319.8,379.3 1,-0.4 0.7,-0.3 1,-2.1 0,-0.9 0.4,-0.2 -0.4,-1 0,-2.2 0.6,0 0.9,-0.6 0.6,-1.1 0.6,-0.4 -0.2,-0.9 -0.4,-1.1 -0.5,-0.4 -0.1,0 0,-0.9 -0.4,-0.6 -0.5,0.6 -0.6,0.9 -0.2,0.6 0.2,0.9 0,1.3 -0.6,0.2 -1.1,-0.2 -0.6,1.1 -0.4,-0.1 -0.2,0.7 0.2,0.8 0,0.9 0.9,0 0.1,0.1 -0.6,0.6 0.5,0.4 0,1.1 0,0.8 0.5,0.2 0,0.3 -0.4,0.6 -0.6,0.6 -0.4,0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1096"
         inkscape:connector-curvature="0"
         d="m 252.37474,461 10.6,-6.9 0,-0.4 1,-0.5 0.9,-0.4 0.7,-0.2 0.7,-0.4 0.4,-0.5 -0.4,-0.8 0.4,-0.1 1.6,0.1 0,-3.1 -0.4,-0.4 -0.4,-1.5 -0.7,-1.1 -0.5,0.2 -3.3,-0.2 -0.4,-0.4 -1.7,-0.3 -1.6,0.3 -0.4,-0.5 -1.6,0 -0.2,-1 -1.4,-0.5 -0.3,-0.4 -0.8,0 0.4,-0.6 -0.6,-0.3 -0.6,0.6 -0.8,-0.3 0.4,-0.9 -0.4,0 0,-0.5 -0.6,-0.8 -0.4,0 -0.8,-0.2 0.2,-0.9 -0.2,-0.6 0,-0.5 -0.8,-0.4 -0.6,0 -0.3,0.6 -0.4,0 -0.4,-0.2 -0.2,-0.4 0,-0.4 -0.8,0.4 -0.2,0.4 -0.6,-0.8 -0.8,0.8 0,0.5 -0.2,0.2 0,0.8 -0.5,0.1 -0.1,0.4 -0.8,1.1 -0.2,-0.1 -0.5,-0.4 -0.6,0 -1,-0.6 -0.6,0.6 -0.4,0 -0.2,0.4 0,0.1 0,0.8 -0.8,0.5 -0.2,0 -0.4,0.6 0.4,0.9 -0.4,0.3 0,0.7 0.4,0.2 0,0.7 0.6,0.7 1,-0.7 0.2,0.5 2,-0.3 0.5,0.3 0.2,-0.3 6.3,0 0.6,1.3 -0.6,0.6 -1.3,14.1 2.9,0 z" /><g
         id="g1098"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1100"
           inkscape:connector-curvature="0"
           d="m 254,435.4 10.6,-6.9 0,-0.4 1,-0.5 0.9,-0.4 0.7,-0.2 0.7,-0.4 0.4,-0.5 -0.4,-0.8 0.4,-0.1 1.6,0.1 0,-3.1 -0.4,-0.4 -0.4,-1.5 -0.7,-1.1 -0.5,0.2 -3.3,-0.2 -0.4,-0.4 -1.7,-0.3 -1.6,0.3 -0.4,-0.5 -1.6,0 -0.2,-1 -1.4,-0.5 -0.3,-0.4 -0.8,0 0.4,-0.6 -0.6,-0.3 -0.6,0.6 -0.8,-0.3 0.4,-0.9 -0.4,0 0,-0.5 -0.6,-0.8 -0.4,0 -0.8,-0.2 0.2,-0.9 -0.2,-0.6 0,-0.5 -0.8,-0.4 -0.6,0 -0.3,0.6 -0.4,0 -0.4,-0.2 -0.2,-0.4 0,-0.4 -0.8,0.4 -0.2,0.4 -0.6,-0.8 -0.8,0.8 0,0.5 -0.2,0.2 0,0.8 -0.5,0.1 -0.1,0.4 -0.8,1.1 -0.2,-0.1 -0.5,-0.4 -0.6,0 -1,-0.6 -0.6,0.6 -0.4,0 -0.2,0.4 0,0.1 0,0.8 -0.8,0.5 -0.2,0 -0.4,0.6 0.4,0.9 -0.4,0.3 0,0.7 0.4,0.2 0,0.7 0.6,0.7 1,-0.7 0.2,0.5 2,-0.3 0.5,0.3 0.2,-0.3 6.3,0 0.6,1.3 -0.6,0.6 -1.3,14.1 2.9,0 z" /></g><path
         style="fill:none;stroke:none"
         id="path1106"
         inkscape:connector-curvature="0"
         d="m 257.17474,478.5 0.6,-0.8 0.3,-0.9 0,-0.8 0.3,-1.3 0.4,-0.6 0,-0.5 0,-0.4 -3.1,-0.4 0,-0.5 -1.2,-0.2 0,-0.3 0,-0.6 -0.6,-0.6 -2.1,-1.7 -1.4,-0.2 -0.6,0.2 -0.6,-0.2 -1,0 -1.7,-0.9 -0.5,-0.4 0,-2.4 0,-2.4 -6,-0.2 0,-3.9 -1.6,-0.6 -0.4,-0.3 0,-0.6 0,-2.2 -6.5,0 -0.4,-0.6 0.4,1.5 0.6,0.7 0.4,0.6 0.2,0.6 0.4,0.5 0.6,1 1.2,1.2 0,1.2 0.8,1.1 1.7,1.7 1,1.5 1.8,0.5 1.4,0.6 1.7,1.5 1,1.4 -0.4,0.8 0,1.5 0.6,0.7 0.4,1 1.1,1.3 1.2,0.6 2,0.8 0.6,1.6 0.6,1.5 1.1,0 0.9,-1 1.7,0 1,0 0.6,0.4 0.4,-0.5 1.1,0 z"
/><g
         id="g1104"
         style="fill:#0bdca2;fill-opacity:1"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:#0bdca2;fill-opacity:1;stroke:#b6dde8;stroke-width:0.25000000000000000;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="MA"
           inkscape:connector-curvature="0"
           d="m 258.8,452.9 0.6,-0.8 0.3,-0.9 0,-0.8 0.3,-1.3 0.4,-0.6 0,-0.5 0,-0.4 -3.1,-0.4 0,-0.5 -1.2,-0.2 0,-0.3 0,-0.6 -0.6,-0.6 -2.1,-1.7 -1.4,-0.2 -0.6,0.2 -0.6,-0.2 -1,0 -1.7,-0.9 -0.5,-0.4 0,-2.4 0,-2.4 -6,-0.2 0,-3.9 -1.6,-0.6 -0.4,-0.3 0,-0.6 0,-2.2 -6.5,0 -0.4,-0.6 0.4,1.5 0.6,0.7 0.4,0.6 0.2,0.6 0.4,0.5 0.6,1 1.2,1.2 0,1.2 0.8,1.1 1.7,1.7 1,1.5 1.8,0.5 1.4,0.6 1.7,1.5 1,1.4 -0.4,0.8 0,1.5 0.6,0.7 0.4,1 1.1,1.3 1.2,0.6 2,0.8 0.6,1.6 0.6,1.5 1.1,0 0.9,-1 1.7,0 1,0 0.6,0.4 0.4,-0.5 1.1,0 z"
         onclick="mouseclickpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onmouseover="mouseoverpays(evt,this)"             /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1108"
         inkscape:connector-curvature="0"
         d="m 245.97474,465.1 6.5,-4.1 -2.9,0 1.3,-14.1 0.6,-0.6 -0.6,-1.3 -6.4,0 -0.1,0.3 -0.5,-0.3 -2,0.3 -0.2,-0.5 -1.1,0.7 -0.6,-0.7 0,-0.7 -0.4,-0.3 -1,0.3 -0.2,0.3 -0.4,0.6 -0.4,0 -0.2,0.9 -0.6,0 -0.8,1 -0.7,0.1 -1.6,-0.1 -1.2,0 -0.9,-0.6 0.5,1.1 0.6,1.5 -0.2,1.9 -0.9,0.9 0.9,0 0,0.9 -0.4,1.1 -0.5,-0.4 -0.1,1 -0.4,0 0.4,0.5 6.5,0 0,2.3 0,0.6 0.4,0.3 1.6,0.6 0,3.9 6,0.2 0,2.4 z" /><g
         id="g1110"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1112"
           inkscape:connector-curvature="0"
           d="m 247.6,439.5 6.5,-4.1 -2.9,0 1.3,-14.1 0.6,-0.6 -0.6,-1.3 -6.4,0 -0.1,0.3 -0.5,-0.3 -2,0.3 -0.2,-0.5 -1.1,0.7 -0.6,-0.7 0,-0.7 -0.4,-0.3 -1,0.3 -0.2,0.3 -0.4,0.6 -0.4,0 -0.2,0.9 -0.6,0 -0.8,1 -0.7,0.1 -1.6,-0.1 -1.2,0 -0.9,-0.6 0.5,1.1 0.6,1.5 -0.2,1.9 -0.9,0.9 0.9,0 0,0.9 -0.4,1.1 -0.5,-0.4 -0.1,1 -0.4,0 0.4,0.5 6.5,0 0,2.3 0,0.6 0.4,0.3 1.6,0.6 0,3.9 6,0.2 0,2.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1114"
         inkscape:connector-curvature="0"
         d="m 360.47474,385.5 -0.2,0.4 0,0.2 0.2,0.7 0.5,-0.7 -0.5,-0.6 z" /><g
         id="g1116"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1118"
           inkscape:connector-curvature="0"
           d="m 362.1,359.9 -0.2,0.4 0,0.2 0.2,0.7 0.5,-0.7 -0.5,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1120"
         inkscape:connector-curvature="0"
         d="m 321.27474,401 1.2,0.2 0.4,-0.2 0.7,0.2 0.6,-0.2 0.8,0 0.2,0 1,0 0.6,0.2 0.5,0.4 0.6,0 0,-0.4 0.6,0.4 0.8,0.1 1.3,0.4 0.6,0.6 0.4,-0.6 -0.4,-0.9 0,-1 0,-0.7 0.4,-0.7 -0.4,-0.2 0.4,-0.8 0,-1.5 0.4,-0.2 -0.4,-0.5 -0.4,-0.4 0.4,-0.3 -0.7,-0.6 -0.8,-0.9 -0.1,-0.2 -0.5,-0.4 -1,-0.4 -0.2,-0.5 -2.1,-0.5 -1.2,-0.6 -0.4,-0.4 0,-0.4 -1,-0.7 -1.1,-0.8 -1.6,-1.4 -0.2,-0.6 0.2,-0.9 0.4,-1.1 0.2,-1.4 0,0.4 0.5,-0.4 0,-1.1 -0.5,-0.5 -0.2,-1.3 0.2,0.3 0.5,-0.5 -1.1,-0.8 -2.6,-0.8 -0.7,-0.3 -0.6,-0.5 -0.6,-0.4 0.2,-0.5 0.4,0.1 0,-0.1 0,-1.4 -0.6,0 -0.8,0.4 0.4,1 -0.4,0.5 0,0.4 0,1.1 0,1.3 -0.6,1.5 0.4,0.5 -0.4,0.2 -0.2,0.9 1.8,1.5 0.4,0.8 0,0.5 0.2,0.6 0.8,0.9 -0.4,0.6 0,0.5 0,0.8 0.4,0 -0.4,0.2 0.4,0.3 0,0.2 0,1 0,0.9 -0.4,0.9 0,0.4 -1.2,0 -0.4,0.2 -0.6,0.4 -0.4,0.1 -1.3,-0.1 -0.4,0.5 0,0.6 -0.4,0.7 5.4,1.5 0.4,0.2 0.6,-1.2 1,0.2 0.6,-0.2 0,-1.2 -0.2,-1 0.2,-0.5 0.6,-1 0.5,-0.5 0.4,0.5 0,1 0.1,0 0.4,0.4 0.4,1.1 0.3,0.9 -0.7,0.3 -0.5,1.2 -0.9,0.5 -0.6,0 0,2.3 0.4,0.9 z" /><g
         id="g1122"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1124"
           inkscape:connector-curvature="0"
           d="m 322.9,375.4 1.2,0.2 0.4,-0.2 0.7,0.2 0.6,-0.2 0.8,0 0.2,0 1,0 0.6,0.2 0.5,0.4 0.6,0 0,-0.4 0.6,0.4 0.8,0.1 1.3,0.4 0.6,0.6 0.4,-0.6 -0.4,-0.9 0,-1 0,-0.7 0.4,-0.7 -0.4,-0.2 0.4,-0.8 0,-1.5 0.4,-0.2 -0.4,-0.5 -0.4,-0.4 0.4,-0.3 -0.7,-0.6 -0.8,-0.9 -0.1,-0.2 -0.5,-0.4 -1,-0.4 -0.2,-0.5 -2.1,-0.5 -1.2,-0.6 -0.4,-0.4 0,-0.4 -1,-0.7 -1.1,-0.8 -1.6,-1.4 -0.2,-0.6 0.2,-0.9 0.4,-1.1 0.2,-1.4 0,0.4 0.5,-0.4 0,-1.1 -0.5,-0.5 -0.2,-1.3 0.2,0.3 0.5,-0.5 -1.1,-0.8 -2.6,-0.8 -0.7,-0.3 -0.6,-0.5 -0.6,-0.4 0.2,-0.5 0.4,0.1 0,-0.1 0,-1.4 -0.6,0 -0.8,0.4 0.4,1 -0.4,0.5 0,0.4 0,1.1 0,1.3 -0.6,1.5 0.4,0.5 -0.4,0.2 -0.2,0.9 1.8,1.5 0.4,0.8 0,0.5 0.2,0.6 0.8,0.9 -0.4,0.6 0,0.5 0,0.8 0.4,0 -0.4,0.2 0.4,0.3 0,0.2 0,1 0,0.9 -0.4,0.9 0,0.4 -1.2,0 -0.4,0.2 -0.6,0.4 -0.4,0.1 -1.3,-0.1 -0.4,0.5 0,0.6 -0.4,0.7 5.4,1.5 0.4,0.2 0.6,-1.2 1,0.2 0.6,-0.2 0,-1.2 -0.2,-1 0.2,-0.5 0.6,-1 0.5,-0.5 0.4,0.5 0,1 0.1,0 0.4,0.4 0.4,1.1 0.3,0.9 -0.7,0.3 -0.5,1.2 -0.9,0.5 -0.6,0 0,2.3 0.4,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1126"
         inkscape:connector-curvature="0"
         d="m 301.17474,391.2 1.4,0.4 0.2,0 1,-0.4 0.6,-0.4 -0.6,0 -0.6,-0.1 -0.4,0 -1.2,-0.8 -0.6,0.8 -1.5,-0.4 -2.2,-0.4 -0.4,-5.9 -1.6,0 -0.3,-4.8 -0.3,-6.3 -0.6,0 -0.9,-0.5 -0.2,-0.3 -0.6,0.3 -0.8,0 -0.6,0 -1,0.3 0,0.9 -0.2,0 -0.5,0 -0.4,-0.7 -0.1,-0.2 -1.4,1.1 -0.7,1.5 0,0.7 -0.2,0.6 -0.4,2.1 -0.4,0.8 -0.2,1.6 0,1.1 0,0.8 -0.8,1 -1.2,2.8 -1.2,2 -0.5,0.9 -0.6,1.1 0,1 0.3,0 0.8,0 0.1,0 1.1,0.5 0.5,-0.1 1.1,-0.6 0.4,0 6.9,0.2 0.2,-0.6 0.6,-0.4 2.5,-0.1 1,0 0.6,0 0.6,0 3.3,0.5 z" /><g
         id="g1128"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1130"
           inkscape:connector-curvature="0"
           d="m 302.8,365.6 1.4,0.4 0.2,0 1,-0.4 0.6,-0.4 -0.6,0 -0.6,-0.1 -0.4,0 -1.2,-0.8 -0.6,0.8 -1.5,-0.4 -2.2,-0.4 -0.4,-5.9 -1.6,0 -0.3,-4.8 -0.3,-6.3 -0.6,0 -0.9,-0.5 -0.2,-0.3 -0.6,0.3 -0.8,0 -0.6,0 -1,0.3 0,0.9 -0.2,0 -0.5,0 -0.4,-0.7 -0.1,-0.2 -1.4,1.1 -0.7,1.5 0,0.7 -0.2,0.6 -0.4,2.1 -0.4,0.8 -0.2,1.6 0,1.1 0,0.8 -0.8,1 -1.2,2.8 -1.2,2 -0.5,0.9 -0.6,1.1 0,1 0.3,0 0.8,0 0.1,0 1.1,0.5 0.5,-0.1 1.1,-0.6 0.4,0 6.9,0.2 0.2,-0.6 0.6,-0.4 2.5,-0.1 1,0 0.6,0 0.6,0 3.3,0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1132"
         inkscape:connector-curvature="0"
         d="m 281.87474,458.2 2.2,-0.5 1.4,-1 1.3,0.6 0.4,-1.1 0,-1.4 0.6,-0.5 0,-0.6 0.6,-0.9 -0.2,-0.2 -0.4,-5.2 -2.7,-3 -0.4,-0.5 -0.2,-0.8 0,-0.7 -0.4,-0.7 -0.6,-0.3 -0.6,-0.3 -3.1,0.6 -0.6,-0.3 -0.6,-0.3 -0.6,-0.2 -1,-0.4 -2.1,0.9 -2,-0.3 -0.6,0.6 -1,0.3 -1.1,0.4 0,-0.4 -1.2,0 -0.6,-0.3 -0.5,-0.8 -1,-0.8 0,-1.5 -0.6,0.6 -0.6,0.8 -0.4,0 -0.4,-0.8 -0.6,0.8 0.4,0.1 -0.4,0.4 -0.6,0 -1.3,0.6 0,0.3 0.6,0 0,0.3 -0.6,0 -0.8,0.7 -0.2,0.5 -0.4,0.6 0,0.6 1.6,0.3 0.4,0.4 3.3,0.2 0.6,-0.2 0.6,1.1 0.4,1.5 0.5,0.4 0,3.2 2.9,0.9 2.6,2.2 6.3,3.2 1.7,0.9 z" /><g
         id="g1134"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1136"
           inkscape:connector-curvature="0"
           d="m 283.5,432.6 2.2,-0.5 1.4,-1 1.3,0.6 0.4,-1.1 0,-1.4 0.6,-0.5 0,-0.6 0.6,-0.9 -0.2,-0.2 -0.4,-5.2 -2.7,-3 -0.4,-0.5 -0.2,-0.8 0,-0.7 -0.4,-0.7 -0.6,-0.3 -0.6,-0.3 -3.1,0.6 -0.6,-0.3 -0.6,-0.3 -0.6,-0.2 -1,-0.4 -2.1,0.9 -2,-0.3 -0.6,0.6 -1,0.3 -1.1,0.4 0,-0.4 -1.2,0 -0.6,-0.3 -0.5,-0.8 -1,-0.8 0,-1.5 -0.6,0.6 -0.6,0.8 -0.4,0 -0.4,-0.8 -0.6,0.8 0.4,0.1 -0.4,0.4 -0.6,0 -1.3,0.6 0,0.3 0.6,0 0,0.3 -0.6,0 -0.8,0.7 -0.2,0.5 -0.4,0.6 0,0.6 1.6,0.3 0.4,0.4 3.3,0.2 0.6,-0.2 0.6,1.1 0.4,1.5 0.5,0.4 0,3.2 2.9,0.9 2.6,2.2 6.3,3.2 1.7,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1138"
         inkscape:connector-curvature="0"
         d="m 284.47474,442.4 0.6,-0.9 0.4,-0.4 0,-1.1 0.6,-0.3 0.4,-1 -0.8,-0.9 -0.6,0.3 -0.6,-0.9 0,-0.6 -0.6,-0.5 0.2,-0.7 -1.1,-0.2 0,-0.9 -0.6,-0.6 -1,-1.8 0.4,-0.6 -0.4,0 -0.6,-0.6 -0.6,0 0,0.2 -0.7,0.5 -0.4,-0.1 -0.6,0 -1,-0.6 -1,-0.9 -0.4,-1.3 -0.6,-1.1 -0.2,0.1 0,-0.1 -1.5,-0.4 -1,0.4 0,-0.4 -1.2,-0.2 -0.6,0.2 -1.1,1.3 0.3,0.2 0,0.3 -0.7,0 -0.2,1 -1.4,0.5 -2,0 -0.3,1.6 0,1.8 0,0.9 0.6,0.2 0.5,1 0.1,0.3 0,0.5 0.4,0.6 -0.4,1.3 0,0.2 0,1.5 1.1,0.7 0.4,0.8 0.6,0.4 1.3,0 0,0.3 1,-0.3 1,-0.4 0.6,-0.6 2.1,0.4 2,-0.9 1,0.3 0.6,0.2 0.6,0.4 0.6,0.2 3.1,-0.6 0.7,0.4 0.6,0.2 0.4,0.7 z" /><g
         id="g1140"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1142"
           inkscape:connector-curvature="0"
           d="m 286.1,416.8 0.6,-0.9 0.4,-0.4 0,-1.1 0.6,-0.3 0.4,-1 -0.8,-0.9 -0.6,0.3 -0.6,-0.9 0,-0.6 -0.6,-0.5 0.2,-0.7 -1.1,-0.2 0,-0.9 -0.6,-0.6 -1,-1.8 0.4,-0.6 -0.4,0 -0.6,-0.6 -0.6,0 0,0.2 -0.7,0.5 -0.4,-0.1 -0.6,0 -1,-0.6 -1,-0.9 -0.4,-1.3 -0.6,-1.1 -0.2,0.1 0,-0.1 -1.5,-0.4 -1,0.4 0,-0.4 -1.2,-0.2 -0.6,0.2 -1.1,1.3 0.3,0.2 0,0.3 -0.7,0 -0.2,1 -1.4,0.5 -2,0 -0.3,1.6 0,1.8 0,0.9 0.6,0.2 0.5,1 0.1,0.3 0,0.5 0.4,0.6 -0.4,1.3 0,0.2 0,1.5 1.1,0.7 0.4,0.8 0.6,0.4 1.3,0 0,0.3 1,-0.3 1,-0.4 0.6,-0.6 2.1,0.4 2,-0.9 1,0.3 0.6,0.2 0.6,0.4 0.6,0.2 3.1,-0.6 0.7,0.4 0.6,0.2 0.4,0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1144"
         inkscape:connector-curvature="0"
         d="m 312.77474,417.6 0.6,0 0,-0.2 1.1,0.6 0.4,-0.9 0,-0.9 0,-0.4 -0.4,-0.2 -0.5,0.6 -0.6,-0.4 -0.4,-0.2 -0.6,-0.4 0,0.6 -1,-0.2 0,0.2 0.4,0.4 0.4,0.9 0.6,0.3 0,0.2 z" /><g
         id="g1146"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1148"
           inkscape:connector-curvature="0"
           d="m 314.4,392 0.6,0 0,-0.2 1.1,0.6 0.4,-0.9 0,-0.9 0,-0.4 -0.4,-0.2 -0.5,0.6 -0.6,-0.4 -0.4,-0.2 -0.6,-0.4 0,0.6 -1,-0.2 0,0.2 0.4,0.4 0.4,0.9 0.6,0.3 0,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1150"
         inkscape:connector-curvature="0"
         d="m 314.77474,406.5 0.6,-0.9 0.6,0 0.4,-0.2 0.7,-0.4 0.6,0 0.4,-0.2 0.2,0 0.4,-0.7 0.6,-0.5 0.4,-0.6 0,-0.4 -0.6,-0.2 0,-0.7 0,-1.1 -0.4,-0.4 0.6,-0.6 -0.2,-0.1 -0.8,0 0,-1 -0.2,-0.8 0.2,-0.7 -5.3,-1.5 0.4,-0.7 0,-0.6 -0.4,0 -0.8,0 -1.4,-0.3 0,-0.8 -1.1,-0.4 -1,-0.6 -0.8,-1.4 -0.9,-0.4 -0.1,0.4 -1.7,0 -0.8,0.2 -0.6,0.4 -1,0.3 -0.2,0 -1.4,-0.3 -0.7,1 -1.6,1.4 0.4,5 3.3,0 0,0.4 0,0.4 0,1.1 0,1.8 0.6,-0.4 0.4,-0.8 1.2,0.8 0.4,-0.8 1.2,-0.6 0.6,0 0.9,0 0,0.6 0.2,0 1,-1.2 1,0 1.1,-1.3 0.2,-0.6 1,0.4 0,-0.4 0.4,1.9 -0.6,0 0,-0.4 -0.8,0.4 -0.9,0.6 -0.4,0.3 0.4,0.8 0,0.7 0.3,0.6 0,1.1 -0.3,0.4 0.7,0.5 0.2,1 0,0.4 3.4,0.1 z" /><g
         id="g1152"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1154"
           inkscape:connector-curvature="0"
           d="m 316.4,380.9 0.6,-0.9 0.6,0 0.4,-0.2 0.7,-0.4 0.6,0 0.4,-0.2 0.2,0 0.4,-0.7 0.6,-0.5 0.4,-0.6 0,-0.4 -0.6,-0.2 0,-0.7 0,-1.1 -0.4,-0.4 0.6,-0.6 -0.2,-0.1 -0.8,0 0,-1 -0.2,-0.8 0.2,-0.7 -5.3,-1.5 0.4,-0.7 0,-0.6 -0.4,0 -0.8,0 -1.4,-0.3 0,-0.8 -1.1,-0.4 -1,-0.6 -0.8,-1.4 -0.9,-0.4 -0.1,0.4 -1.7,0 -0.8,0.2 -0.6,0.4 -1,0.3 -0.2,0 -1.4,-0.3 -0.7,1 -1.6,1.4 0.4,5 3.3,0 0,0.4 0,0.4 0,1.1 0,1.8 0.6,-0.4 0.4,-0.8 1.2,0.8 0.4,-0.8 1.2,-0.6 0.6,0 0.9,0 0,0.6 0.2,0 1,-1.2 1,0 1.1,-1.3 0.2,-0.6 1,0.4 0,-0.4 0.4,1.9 -0.6,0 0,-0.4 -0.8,0.4 -0.9,0.6 -0.4,0.3 0.4,0.8 0,0.7 0.3,0.6 0,1.1 -0.3,0.4 0.7,0.5 0.2,1 0,0.4 3.4,0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1156"
         inkscape:connector-curvature="0"
         d="m 231.57474,446.3 0.8,0.6 1.3,0 1.6,0.1 0.6,-0.1 0.9,-1 0.6,0 0.2,-0.9 0.4,0 0.4,-0.6 0.2,-0.3 1,-0.2 0,-0.8 0.4,-0.2 -0.4,-0.9 0.4,-0.5 0.3,0 0.8,-0.6 0,-0.7 -1.1,0 -0.4,0 -0.5,-0.2 -1.1,0.2 0,0.3 -1.2,0 -2.7,0 -0.8,-0.3 -0.6,0 -1.3,-0.2 1.3,0.5 -1.3,0 -0.4,0.6 2.1,0.4 0,0.2 0.6,0 0.6,0.3 1.6,-0.5 0.9,0.5 -1.1,0 -1,0.4 -1.4,0 -1.9,-0.4 0,0.4 0.2,0.2 -0.6,0 -0.4,0.3 -0.2,1 0.2,0.5 0.8,0.9 0.2,1 z" /><g
         id="g1158"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1160"
           inkscape:connector-curvature="0"
           d="m 233.2,420.7 0.8,0.6 1.3,0 1.6,0.1 0.6,-0.1 0.9,-1 0.6,0 0.2,-0.9 0.4,0 0.4,-0.6 0.2,-0.3 1,-0.2 0,-0.8 0.4,-0.2 -0.4,-0.9 0.4,-0.5 0.3,0 0.8,-0.6 0,-0.7 -1.1,0 -0.4,0 -0.5,-0.2 -1.1,0.2 0,0.3 -1.2,0 -2.7,0 -0.8,-0.3 -0.6,0 -1.3,-0.2 1.3,0.5 -1.3,0 -0.4,0.6 2.1,0.4 0,0.2 0.6,0 0.6,0.3 1.6,-0.5 0.9,0.5 -1.1,0 -1,0.4 -1.4,0 -1.9,-0.4 0,0.4 0.2,0.2 -0.6,0 -0.4,0.3 -0.2,1 0.2,0.5 0.8,0.9 0.2,1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1162"
         inkscape:connector-curvature="0"
         d="m 237.27474,434.6 0.6,0.6 0.5,0 0.6,0.8 0.2,0 1.9,0.3 0.6,-1 0,-0.5 0.2,-0.2 0,-1 0.8,0 0,-0.5 -0.4,0 0,-0.7 -0.6,-0.6 -0.6,-1 -1.5,0.6 -1.7,0.8 0,0.5 -0.6,0.9 0,0.6 0,0.4 z" /><g
         id="g1164"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1166"
           inkscape:connector-curvature="0"
           d="m 238.9,409 0.6,0.6 0.5,0 0.6,0.8 0.2,0 1.9,0.3 0.6,-1 0,-0.5 0.2,-0.2 0,-1 0.8,0 0,-0.5 -0.4,0 0,-0.7 -0.6,-0.6 -0.6,-1 -1.5,0.6 -1.7,0.8 0,0.5 -0.6,0.9 0,0.6 0,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1168"
         inkscape:connector-curvature="0"
         d="m 304.37474,390.9 0.8,-0.1 1.6,0 0.2,-0.4 0.8,0.4 0.9,1.4 1,0.6 1,0.4 0,0.7 1.4,0.4 0.9,0 0.4,0 0.4,-0.6 1.2,0.2 0.4,-0.2 0.7,-0.4 0.4,-0.1 1.2,0 0,-0.4 0.4,-0.9 0,-1 0,-0.9 0,-0.2 -0.4,-0.4 0.4,-0.1 -0.4,0 0,-0.8 0,-0.6 0.4,-0.5 -0.8,-0.9 -0.2,-0.6 0,-0.6 -0.4,-0.7 -1.9,-1.5 -0.4,0.4 -1,-0.4 -1.1,0.4 -1,0 -0.6,0 0,0.9 -1,0.2 -0.6,0 -0.4,0.7 -0.2,1.2 -0.4,0 -0.3,0.9 -1,0.3 -1,0.6 0,0.6 -1,1.1 -0.4,0.9 z" /><g
         id="g1170"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1172"
           inkscape:connector-curvature="0"
           d="m 306,365.3 0.8,-0.1 1.6,0 0.2,-0.4 0.8,0.4 0.9,1.4 1,0.6 1,0.4 0,0.7 1.4,0.4 0.9,0 0.4,0 0.4,-0.6 1.2,0.2 0.4,-0.2 0.7,-0.4 0.4,-0.1 1.2,0 0,-0.4 0.4,-0.9 0,-1 0,-0.9 0,-0.2 -0.4,-0.4 0.4,-0.1 -0.4,0 0,-0.8 0,-0.6 0.4,-0.5 -0.8,-0.9 -0.2,-0.6 0,-0.6 -0.4,-0.7 -1.9,-1.5 -0.4,0.4 -1,-0.4 -1.1,0.4 -1,0 -0.6,0 0,0.9 -1,0.2 -0.6,0 -0.4,0.7 -0.2,1.2 -0.4,0 -0.3,0.9 -1,0.3 -1,0.6 0,0.6 -1,1.1 -0.4,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1174"
         inkscape:connector-curvature="0"
         d="m 335.77474,437.2 0.4,0.5 0.2,0.4 1.4,-1.5 1,-0.4 1.9,1 1.4,-0.2 1.7,0.5 0.5,0 1.7,0.2 1,0 1.3,0.4 0.8,0.4 0.2,0.5 1,-0.4 -0.4,-0.9 0,-0.2 0,-1.3 -0.3,-1.4 -1.3,-1.7 -0.3,-0.7 0,-0.6 -1.4,-1.9 0,-0.6 -1.8,-2.5 -3.1,-2.8 -2,-1.5 -3.3,-2.4 -2.7,-2.6 -1,1.1 0,5.8 1.6,1.6 0.9,0.4 1,0 0.6,0.9 2.3,0.6 0.6,0 5.1,4.8 -1.7,0 -5.3,1.8 -1,0.6 -1.4,1.5 0.4,0.6 z" /><g
         id="g1176"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1178"
           inkscape:connector-curvature="0"
           d="m 337.4,411.6 0.4,0.5 0.2,0.4 1.4,-1.5 1,-0.4 1.9,1 1.4,-0.2 1.7,0.5 0.5,0 1.7,0.2 1,0 1.3,0.4 0.8,0.4 0.2,0.5 1,-0.4 -0.4,-0.9 0,-0.2 0,-1.3 -0.3,-1.4 -1.3,-1.7 -0.3,-0.7 0,-0.6 -1.4,-1.9 0,-0.6 -1.8,-2.5 -3.1,-2.8 -2,-1.5 -3.3,-2.4 -2.7,-2.6 -1,1.1 0,5.8 1.6,1.6 0.9,0.4 1,0 0.6,0.9 2.3,0.6 0.6,0 5.1,4.8 -1.7,0 -5.3,1.8 -1,0.6 -1.4,1.5 0.4,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1180"
         inkscape:connector-curvature="0"
         d="m 310.67474,383.5 -0.5,-0.5 -0.7,0 -1,-1 -0.4,0 -0.2,-0.3 -0.4,-0.2 -0.6,-0.8 0,-0.8 -0.6,-0.3 -1,-0.4 -0.9,-1.5 -0.8,0 -1,0 -1,0 -1,0.6 -1.1,0 0,-1.1 -1,-0.9 -0.6,0 0,-0.2 -1,0 -1,-0.4 0,0.9 0.4,0.6 -0.4,0.5 -0.3,1 -0.6,0.5 -0.4,-6.3 -0.6,0 -0.8,-0.6 -0.2,-0.2 -0.6,0.2 -0.8,0 -0.6,0 -1.1,0.4 0,1 -0.1,0 -0.5,0 -0.3,-0.8 -0.3,-0.2 0.3,-1 0.9,-2 1.5,-2.4 0.2,-0.9 -0.2,-1 -0.6,0 0,-0.3 0.8,-1.5 -0.2,-0.9 0.2,0 0,0.3 0.8,0 0,-0.5 0.2,0 0.6,-0.6 1,-0.4 0.4,0.6 0.4,0.4 1.7,0 1.2,0.2 0,0.3 1.6,0 1.1,0 2,-0.3 0.2,0.3 1,0 0.4,0.4 1.2,0 3.3,2 1.8,1.5 1.7,1.3 0,0.6 1.2,1.1 0,0.6 2.3,1.8 0.7,1.5 0.7,1.5 -0.7,0 -0.7,0.4 0,-0.5 -1.3,0 -1,1.1 1,0.9 1.3,0 0,0.4 0,1.1 0,1.2 -0.6,1.6 0.4,0.5 -0.4,0.2 -0.3,0.9 -0.4,0.4 -1,-0.4 -1,0.4 -1,0 -0.7,0 z" /><g
         id="g1182"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1184"
           inkscape:connector-curvature="0"
           d="m 312.3,357.9 -0.5,-0.5 -0.7,0 -1,-1 -0.4,0 -0.2,-0.3 -0.4,-0.2 -0.6,-0.8 0,-0.8 -0.6,-0.3 -1,-0.4 -0.9,-1.5 -0.8,0 -1,0 -1,0 -1,0.6 -1.1,0 0,-1.1 -1,-0.9 -0.6,0 0,-0.2 -1,0 -1,-0.4 0,0.9 0.4,0.6 -0.4,0.5 -0.3,1 -0.6,0.5 -0.4,-6.3 -0.6,0 -0.8,-0.6 -0.2,-0.2 -0.6,0.2 -0.8,0 -0.6,0 -1.1,0.4 0,1 -0.1,0 -0.5,0 -0.3,-0.8 -0.3,-0.2 0.3,-1 0.9,-2 1.5,-2.4 0.2,-0.9 -0.2,-1 -0.6,0 0,-0.3 0.8,-1.5 -0.2,-0.9 0.2,0 0,0.3 0.8,0 0,-0.5 0.2,0 0.6,-0.6 1,-0.4 0.4,0.6 0.4,0.4 1.7,0 1.2,0.2 0,0.3 1.6,0 1.1,0 2,-0.3 0.2,0.3 1,0 0.4,0.4 1.2,0 3.3,2 1.8,1.5 1.7,1.3 0,0.6 1.2,1.1 0,0.6 2.3,1.8 0.7,1.5 0.7,1.5 -0.7,0 -0.7,0.4 0,-0.5 -1.3,0 -1,1.1 1,0.9 1.3,0 0,0.4 0,1.1 0,1.2 -0.6,1.6 0.4,0.5 -0.4,0.2 -0.3,0.9 -0.4,0.4 -1,-0.4 -1,0.4 -1,0 -0.7,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1186"
         inkscape:connector-curvature="0"
         d="m 310.47474,370.2 -1.8,-0.3 -0.2,-1 -1,0.4 -0.5,1.1 -0.2,0.4 0.2,0.4 0.9,0.9 1.6,0.6 0.6,-0.4 0.7,-0.9 -0.3,-0.6 0,-0.6 z" /><g
         id="g1188"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1190"
           inkscape:connector-curvature="0"
           d="m 312.1,344.6 -1.8,-0.3 -0.2,-1 -1,0.4 -0.5,1.1 -0.2,0.4 0.2,0.4 0.9,0.9 1.6,0.6 0.6,-0.4 0.7,-0.9 -0.3,-0.6 0,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1192"
         inkscape:connector-curvature="0"
         d="m 304.17474,455.8 20,-0.2 0.4,-0.9 0.6,-0.6 -0.2,0 0.9,-3.7 0.8,-0.6 1,-0.5 0.2,-0.4 -0.2,-0.9 -0.8,0.4 -0.6,-0.6 0,-0.4 -0.7,0 -0.4,-0.9 0,-0.9 -0.6,-2.1 -0.4,-2 -0.2,-1 -0.6,-0.4 -0.9,-1 -0.2,-1.5 -0.3,-0.4 -0.6,0 -0.5,-0.6 0,-0.5 -0.1,-0.8 0,-1.6 -0.9,-0.4 -1.2,0 0,-0.9 1.2,-0.6 1,-0.9 1.4,-1 0.2,-0.7 0.5,-0.7 0.6,0 0,-1.2 -0.2,0 -0.4,0.6 -1,-0.6 -1.1,-0.3 -0.1,-0.6 -1.1,-0.6 -1.4,0 -1,0 -0.2,-0.3 -0.7,0.3 -1.4,0.3 -0.2,-0.3 -1.4,1 -0.7,0.5 -0.3,0 0,-0.3 -1,0 0,0.3 -0.3,-0.3 -0.4,-0.2 -0.6,0.2 -0.6,0.3 0,0.6 -0.8,0.4 -0.2,0.9 -1,0.6 -0.5,0.9 -0.1,0.2 -0.5,0 -0.2,0.5 -0.9,0 -0.5,1.3 -0.6,0.6 -1,0 0,0.5 -1,0 -0.2,0.4 0,0.4 -0.4,0.2 0.6,0.3 -0.2,1 -1.1,1.1 -0.4,0.4 0.4,0.5 -0.8,0.4 0,0.7 -0.2,0.8 0,0.1 -0.3,0.4 -0.3,-0.4 0,0.8 0.6,0.6 -0.3,0.9 0.5,0.6 -0.2,0.5 0.2,0.4 0.8,1 -0.4,0.1 0.7,0.4 1.4,0.2 0,6.1 0,0.6 1.6,0 0,3.5 z" /><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1198"
         inkscape:connector-curvature="0"
         d="m 315.57474,377.2 0.4,-0.6 -0.4,-0.9 0,-0.6 -1.2,0 -1,1.2 1,0.9 1.2,0 z" /><g
         id="g1200"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1202"
           inkscape:connector-curvature="0"
           d="m 317.2,351.6 0.4,-0.6 -0.4,-0.9 0,-0.6 -1.2,0 -1,1.2 1,0.9 1.2,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1204"
         inkscape:connector-curvature="0"
         d="m 314.47474,418 0.6,0.2 2.6,0 2.7,0 0.5,0 6.4,-3.5 0,-1 2.2,-1.8 -0.6,-1.1 0,-1.2 0.4,-0.7 0.2,-0.1 0.5,-0.4 0,-0.4 -0.5,-0.2 0.5,-0.9 -0.5,-0.9 0.5,-1.2 0.1,-1.3 0.8,-0.2 0.3,-0.3 0,-0.4 -0.6,-0.6 -1.3,-0.3 -0.8,-0.2 -0.6,-0.3 0,0.3 -0.6,0 -0.4,-0.3 -0.6,-0.3 -1.1,0 -0.2,0 -0.8,0 -0.6,0.3 -0.6,-0.3 -0.4,0.3 -1.2,-0.3 -0.5,0.3 0,0.8 -0.9,2.1 -0.7,0.3 -1,0.4 -0.2,0 -0.4,0.2 -0.6,0 -0.6,0.4 -0.4,0.2 -0.6,0 -0.6,0.9 -0.9,1.5 -1,0.9 -0.6,1 0,1.3 0,0.7 0,0.7 1,0.2 1.1,0.9 0.6,0.6 0,0.8 -0.6,0.1 0,0.4 0.4,0.2 0,0.4 0,0.9 -0.4,0.9 z" /><g
         id="g1206"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1208"
           inkscape:connector-curvature="0"
           d="m 316.1,392.4 0.6,0.2 2.6,0 2.7,0 0.5,0 6.4,-3.5 0,-1 2.2,-1.8 -0.6,-1.1 0,-1.2 0.4,-0.7 0.2,-0.1 0.5,-0.4 0,-0.4 -0.5,-0.2 0.5,-0.9 -0.5,-0.9 0.5,-1.2 0.1,-1.3 0.8,-0.2 0.3,-0.3 0,-0.4 -0.6,-0.6 -1.3,-0.3 -0.8,-0.2 -0.6,-0.3 0,0.3 -0.6,0 -0.4,-0.3 -0.6,-0.3 -1.1,0 -0.2,0 -0.8,0 -0.6,0.3 -0.6,-0.3 -0.4,0.3 -1.2,-0.3 -0.5,0.3 0,0.8 -0.9,2.1 -0.7,0.3 -1,0.4 -0.2,0 -0.4,0.2 -0.6,0 -0.6,0.4 -0.4,0.2 -0.6,0 -0.6,0.9 -0.9,1.5 -1,0.9 -0.6,1 0,1.3 0,0.7 0,0.7 1,0.2 1.1,0.9 0.6,0.6 0,0.8 -0.6,0.1 0,0.4 0.4,0.2 0,0.4 0,0.9 -0.4,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1210"
         inkscape:connector-curvature="0"
         d="m 260.87474,437.6 0.5,0 0.6,0 0.4,-1 0.6,-0.5 0.2,-0.7 0.4,-0.6 0,-4 0,-0.2 0,-0.7 -0.4,0 -0.6,-0.2 -0.6,0.5 -0.4,1.2 0,0.9 0,0.8 0.4,0.5 -0.4,0.6 0,0.4 0,0.5 -0.7,0.3 0.5,0.7 0,0.5 -0.9,0.6 0.4,0.4 z" /><g
         id="g1212"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1214"
           inkscape:connector-curvature="0"
           d="m 262.5,412 0.5,0 0.6,0 0.4,-1 0.6,-0.5 0.2,-0.7 0.4,-0.6 0,-4 0,-0.2 0,-0.7 -0.4,0 -0.6,-0.2 -0.6,0.5 -0.4,1.2 0,0.9 0,0.8 0.4,0.5 -0.4,0.6 0,0.4 0,0.5 -0.7,0.3 0.5,0.7 0,0.5 -0.9,0.6 0.4,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1216"
         inkscape:connector-curvature="0"
         d="m 286.67474,457.3 2,0.7 13.9,-6.4 0,-6.1 -1.4,-0.2 -0.6,-0.3 0.4,-0.2 -0.8,-0.9 -0.2,-0.4 0.2,-0.6 -0.6,-0.6 0.4,-0.8 -0.7,-0.6 0,-0.8 0.3,0.4 0.4,-0.4 0,-0.1 0.2,-0.8 0,-0.7 0.8,-0.4 -0.4,-0.6 -0.6,0 -1,-0.4 -0.5,-0.9 -0.6,-0.2 -0.6,-0.7 -0.4,-0.2 -0.6,-0.3 -1.7,-0.2 -0.6,-0.4 0.2,-0.4 -0.2,-0.5 -1,-0.2 -0.4,0 -0.5,-0.4 -0.5,0.4 -0.8,-0.4 -0.8,-0.3 -0.2,0.3 -1.1,-0.6 -0.6,-0.3 0,0.6 -0.4,0.3 -0.4,1.1 -0.6,0.4 -1.2,1.1 0.2,0.7 0.8,0 1.6,0 -0.8,0.6 -0.2,0.9 0,1 0,0.7 -0.4,0.9 -0.6,0.4 0,0.6 -0.6,0 -0.4,0.4 -0.6,0.8 0,0.8 0.1,0.8 0.5,0.5 2.6,3 0.4,5.2 0.2,0.2 -0.6,0.9 0,0.5 -0.6,0.6 0,1.3 -0.4,1.2 z" /><g
         id="g1218"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1220"
           inkscape:connector-curvature="0"
           d="m 288.3,431.7 2,0.7 13.9,-6.4 0,-6.1 -1.4,-0.2 -0.6,-0.3 0.4,-0.2 -0.8,-0.9 -0.2,-0.4 0.2,-0.6 -0.6,-0.6 0.4,-0.8 -0.7,-0.6 0,-0.8 0.3,0.4 0.4,-0.4 0,-0.1 0.2,-0.8 0,-0.7 0.8,-0.4 -0.4,-0.6 -0.6,0 -1,-0.4 -0.5,-0.9 -0.6,-0.2 -0.6,-0.7 -0.4,-0.2 -0.6,-0.3 -1.7,-0.2 -0.6,-0.4 0.2,-0.4 -0.2,-0.5 -1,-0.2 -0.4,0 -0.5,-0.4 -0.5,0.4 -0.8,-0.4 -0.8,-0.3 -0.2,0.3 -1.1,-0.6 -0.6,-0.3 0,0.6 -0.4,0.3 -0.4,1.1 -0.6,0.4 -1.2,1.1 0.2,0.7 0.8,0 1.6,0 -0.8,0.6 -0.2,0.9 0,1 0,0.7 -0.4,0.9 -0.6,0.4 0,0.6 -0.6,0 -0.4,0.4 -0.6,0.8 0,0.8 0.1,0.8 0.5,0.5 2.6,3 0.4,5.2 0.2,0.2 -0.6,0.9 0,0.5 -0.6,0.6 0,1.3 -0.4,1.2 z" /></g><path
         style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="TN"
         inkscape:connector-curvature="0"
         d="m 275.37474,481.1 0.6,0.7 1,0.2 0.8,-0.2 0.3,-0.9 1,0.6 0.4,-0.4 -0.8,-0.6 -0.2,-0.9 0.6,-0.5 0.4,-1 -0.8,-0.9 -0.9,-0.9 0.3,-0.8 0.6,0 0,-0.4 0.8,0 0.2,-0.3 0.6,-0.6 0,-0.5 0.4,-0.6 -1.2,-0.3 -1.4,-1.2 0.4,-1.3 -1.1,-0.9 -0.4,0 -0.5,3.4 -1.3,0.5 -0.4,1.3 -0.6,0.2 -0.6,1.3 1.2,1.4 0,1.6 0,1 0,0.4 0.6,0.4 0,0.2 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g1224"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1226"
           inkscape:connector-curvature="0"
           d="m 277,455.5 0.6,0.7 1,0.2 0.8,-0.2 0.3,-0.9 1,0.6 0.4,-0.4 -0.8,-0.6 -0.2,-0.9 0.6,-0.5 0.4,-1 -0.8,-0.9 -0.9,-0.9 0.3,-0.8 0.6,0 0,-0.4 0.8,0 0.2,-0.3 0.6,-0.6 0,-0.5 0.4,-0.6 -1.2,-0.3 -1.4,-1.2 0.4,-1.3 -1.1,-0.9 -0.4,0 -0.5,3.4 -1.3,0.5 -0.4,1.3 -0.6,0.2 -0.6,1.3 1.2,1.4 0,1.6 0,1 0,0.4 0.6,0.4 0,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1228"
         inkscape:connector-curvature="0"
         d="m 314.77474,425.9 0.3,0.2 1.4,-0.2 0.6,-0.4 0.2,0.4 1,0 1.4,0 1.1,0.5 0,-0.3 0.6,-0.2 0,-0.6 0.2,-0.9 0.8,-1.5 -0.8,-0.9 -1.3,-1.9 0,-1.9 -2.6,0 -2.6,0 -0.7,-0.2 -1,-0.5 0,0.2 -0.6,0 0.2,2 0.4,0.9 0.6,1 1.1,0.9 0.6,0.5 -0.6,1 0,0.6 0,0.7 -0.3,0.6 z" /><g
         id="g1230"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1232"
           inkscape:connector-curvature="0"
           d="m 316.4,400.3 0.3,0.2 1.4,-0.2 0.6,-0.4 0.2,0.4 1,0 1.4,0 1.1,0.5 0,-0.3 0.6,-0.2 0,-0.6 0.2,-0.9 0.8,-1.5 -0.8,-0.9 -1.3,-1.9 0,-1.9 -2.6,0 -2.6,0 -0.7,-0.2 -1,-0.5 0,0.2 -0.6,0 0.2,2 0.4,0.9 0.6,1 1.1,0.9 0.6,0.5 -0.6,1 0,0.6 0,0.7 -0.3,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1234"
         inkscape:connector-curvature="0"
         d="m 293.17474,425.9 0,0.9 0,0.2 0.8,0.5 1,0.7 1.2,-0.8 1,-0.4 1.1,-0.2 1,0 0.6,-0.4 0.7,1 0.4,0 0.9,0 0.7,0.1 1,0.4 0.6,0 0.6,0 0.4,0.3 0.2,0.3 0.8,-0.3 0.7,0 0.1,-0.3 0.9,0 0.2,0.3 0.6,0 0.8,-0.3 0,-0.5 0.6,-0.4 0.6,-0.2 0.4,0.2 0.2,0.4 0,-0.4 1,0 0,0.4 0.4,0 0.6,-0.6 1.5,-0.9 0.2,-0.6 0,-0.7 0,-0.6 0.6,-0.9 -0.6,-0.6 -1,-1 -0.7,-0.8 -0.4,-1 -0.2,-2 0,-0.2 -0.5,-0.4 -0.5,-0.9 -0.4,-0.4 0,-0.2 0.4,-0.5 0.5,-0.4 0,-0.4 0,-0.6 0.1,-0.9 0,-0.2 0,-0.7 0,-0.8 0,-1.2 0.6,-1 1.1,-0.9 0.8,-1.5 -3.5,-0.2 0,-0.4 -0.2,-0.9 -0.6,-0.6 0.2,-0.3 0,-1.1 -0.2,-0.6 0,-0.7 -0.4,-0.8 0.4,-0.4 0.8,-0.5 0.9,-0.4 0,0.4 0.5,0 -0.4,-1.9 0,0.4 -1,-0.4 -0.2,0.6 -1,1.3 -1,0 -1,1.1 -0.2,0 0,-0.6 -0.9,0 -0.6,0 -1.2,0.6 -0.4,0.9 -1.2,-0.9 -0.4,0.9 -0.6,0.4 0,-0.4 -0.7,0.4 -0.9,-0.4 -0.9,0 -0.2,-0.3 -0.4,0.3 0,0.6 0,1.3 -0.2,0.2 -0.8,0.7 0.8,1.1 -0.4,1 -0.4,1.1 -1.6,0 0,0.9 -1.1,-0.2 -0.8,-0.3 0,-0.6 -0.4,0 0,-0.6 -1.4,0 -0.2,-0.3 -1.6,0 -1.1,1.5 0,0.3 -0.4,0.6 0,0.3 -0.1,0.8 -1.1,0 -3.2,0 -0.9,-0.2 -0.6,0.2 -0.6,0 -1,0 -0.2,0.4 0.6,0 0,0.9 0.6,0.6 0,0.3 0.4,0 0.2,-0.3 0.6,0 0.5,0.5 0.6,0 0.4,-0.2 0.4,-0.7 0.6,0.7 0.2,0.2 0.8,0.4 0.6,0.5 0.2,1 0,0.9 0,1 0.9,0.5 0.1,0.4 0.4,0.8 0.7,0 1,0.8 0,0.6 0,0.7 0,1 0.4,0.7 0,1 0.6,1.3 0.2,0.6 0,0.9 z" /><g
         id="g1236"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1238"
           inkscape:connector-curvature="0"
           d="m 294.8,400.3 0,0.9 0,0.2 0.8,0.5 1,0.7 1.2,-0.8 1,-0.4 1.1,-0.2 1,0 0.6,-0.4 0.7,1 0.4,0 0.9,0 0.7,0.1 1,0.4 0.6,0 0.6,0 0.4,0.3 0.2,0.3 0.8,-0.3 0.7,0 0.1,-0.3 0.9,0 0.2,0.3 0.6,0 0.8,-0.3 0,-0.5 0.6,-0.4 0.6,-0.2 0.4,0.2 0.2,0.4 0,-0.4 1,0 0,0.4 0.4,0 0.6,-0.6 1.5,-0.9 0.2,-0.6 0,-0.7 0,-0.6 0.6,-0.9 -0.6,-0.6 -1,-1 -0.7,-0.8 -0.4,-1 -0.2,-2 0,-0.2 -0.5,-0.4 -0.5,-0.9 -0.4,-0.4 0,-0.2 0.4,-0.5 0.5,-0.4 0,-0.4 0,-0.6 0.1,-0.9 0,-0.2 0,-0.7 0,-0.8 0,-1.2 0.6,-1 1.1,-0.9 0.8,-1.5 -3.5,-0.2 0,-0.4 -0.2,-0.9 -0.6,-0.6 0.2,-0.3 0,-1.1 -0.2,-0.6 0,-0.7 -0.4,-0.8 0.4,-0.4 0.8,-0.5 0.9,-0.4 0,0.4 0.5,0 -0.4,-1.9 0,0.4 -1,-0.4 -0.2,0.6 -1,1.3 -1,0 -1,1.1 -0.2,0 0,-0.6 -0.9,0 -0.6,0 -1.2,0.6 -0.4,0.9 -1.2,-0.9 -0.4,0.9 -0.6,0.4 0,-0.4 -0.7,0.4 -0.9,-0.4 -0.9,0 -0.2,-0.3 -0.4,0.3 0,0.6 0,1.3 -0.2,0.2 -0.8,0.7 0.8,1.1 -0.4,1 -0.4,1.1 -1.6,0 0,0.9 -1.1,-0.2 -0.8,-0.3 0,-0.6 -0.4,0 0,-0.6 -1.4,0 -0.2,-0.3 -1.6,0 -1.1,1.5 0,0.3 -0.4,0.6 0,0.3 -0.1,0.8 -1.1,0 -3.2,0 -0.9,-0.2 -0.6,0.2 -0.6,0 -1,0 -0.2,0.4 0.6,0 0,0.9 0.6,0.6 0,0.3 0.4,0 0.2,-0.3 0.6,0 0.5,0.5 0.6,0 0.4,-0.2 0.4,-0.7 0.6,0.7 0.2,0.2 0.8,0.4 0.6,0.5 0.2,1 0,0.9 0,1 0.9,0.5 0.1,0.4 0.4,0.8 0.7,0 1,0.8 0,0.6 0,0.7 0,1 0.4,0.7 0,1 0.6,1.3 0.2,0.6 0,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1240"
         inkscape:connector-curvature="0"
         d="m 288.07474,431.8 0.6,0.4 1.1,0.5 0.1,-0.3 0.9,0.3 0.7,0.4 0.5,-0.4 0.6,0.4 0.4,0 1,0.2 0.2,0.6 -0.2,0.3 0.6,0.4 1.6,0.2 0.6,0.4 0.4,0.2 0.7,0.7 0.6,0.2 0.4,0.9 1,0.4 0.6,0 0.4,-0.4 1,-1.1 0.2,-0.9 -0.6,-0.4 0.4,-0.2 0,-0.4 0.2,-0.3 1.1,0 0,-0.6 1,0 0.6,-0.6 0.4,-1.3 1,0 0.2,-0.5 0.4,0 0.2,-0.2 0.5,-0.9 0.9,-0.6 0.3,-1 -0.7,0 -0.1,-0.3 -0.9,0 -0.2,0.3 -0.6,0 -0.8,0.3 -0.2,-0.3 -0.4,-0.3 -0.6,0 -0.6,0 -1.1,-0.4 -0.6,-0.2 -1,0 -0.4,0 -0.6,-0.9 -0.6,0.4 -1,0 -1.1,0.2 -1,0.3 -1.2,0.9 -1,-0.7 -0.9,-0.5 0,-0.2 0,-1 -0.7,0 -1,0 -1.5,0 -0.1,-1.2 -0.9,-1 -0.2,0.4 0.2,0.9 -0.2,0 -1.4,1.1 0,1 -0.6,0.3 0,0.9 -0.2,0.3 0,1.3 0.2,0.1 0.6,1.5 0.8,0.4 z" /><g
         id="g1242"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1244"
           inkscape:connector-curvature="0"
           d="m 289.7,406.2 0.6,0.4 1.1,0.5 0.1,-0.3 0.9,0.3 0.7,0.4 0.5,-0.4 0.6,0.4 0.4,0 1,0.2 0.2,0.6 -0.2,0.3 0.6,0.4 1.6,0.2 0.6,0.4 0.4,0.2 0.7,0.7 0.6,0.2 0.4,0.9 1,0.4 0.6,0 0.4,-0.4 1,-1.1 0.2,-0.9 -0.6,-0.4 0.4,-0.2 0,-0.4 0.2,-0.3 1.1,0 0,-0.6 1,0 0.6,-0.6 0.4,-1.3 1,0 0.2,-0.5 0.4,0 0.2,-0.2 0.5,-0.9 0.9,-0.6 0.3,-1 -0.7,0 -0.1,-0.3 -0.9,0 -0.2,0.3 -0.6,0 -0.8,0.3 -0.2,-0.3 -0.4,-0.3 -0.6,0 -0.6,0 -1.1,-0.4 -0.6,-0.2 -1,0 -0.4,0 -0.6,-0.9 -0.6,0.4 -1,0 -1.1,0.2 -1,0.3 -1.2,0.9 -1,-0.7 -0.9,-0.5 0,-0.2 0,-1 -0.7,0 -1,0 -1.5,0 -0.1,-1.2 -0.9,-1 -0.2,0.4 0.2,0.9 -0.2,0 -1.4,1.1 0,1 -0.6,0.3 0,0.9 -0.2,0.3 0,1.3 0.2,0.1 0.6,1.5 0.8,0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1246"
         inkscape:connector-curvature="0"
         d="m 307.87474,372.1 1.6,0.6 0.6,-0.4 0.7,-0.9 -0.3,-0.6 0,-0.6 -1.8,-0.3 -0.2,-1 -1,0.4 -0.5,1.1 -0.2,0.4 0.2,0.4 0.9,0.9 z" /><g
         id="g1248"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1250"
           inkscape:connector-curvature="0"
           d="m 309.5,346.5 1.6,0.6 0.6,-0.4 0.7,-0.9 -0.3,-0.6 0,-0.6 -1.8,-0.3 -0.2,-1 -1,0.4 -0.5,1.1 -0.2,0.4 0.2,0.4 0.9,0.9 z" /></g><path
         style="fill:#ccff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="AR"
         inkscape:connector-curvature="0"
         d="m 144.97474,383 0.4,1.2 0.6,0.3 0,0.2 1,-0.5 1.7,0 1,-1 0.6,1.3 1.7,0 2,-1.9 1.6,-1.4 2.1,-0.4 1.6,-0.8 1.9,-0.3 0.8,-0.6 -0.4,-1.3 -0.7,-0.9 -0.6,-1.6 1.9,-0.1 1.6,-0.7 1.7,0.7 1.4,1.1 0.4,1.5 -0.4,1.5 0.4,1.5 0.2,-2.4 1,-1.5 -0.4,-1.6 -1.2,-0.8 -1.4,-1.2 -0.9,-1.1 -1,-1.3 -0.6,-0.9 -0.8,-0.2 0,-1.3 -0.2,-1.5 0,-1.5 0,-0.9 0,-0.2 0,-1.7 0,-0.5 0.2,-0.9 1,-0.3 1,-0.9 0,-0.4 0,-0.9 1.3,-0.6 0,-0.5 -0.7,-1.5 -0.6,-1.5 -2.6,-0.9 -2.7,-0.4 -2.6,0 1,-0.8 0,-0.9 -0.5,-1.3 0.5,-0.6 -0.6,-0.9 -2.3,0 -1.8,0.6 0,-0.9 0.4,-0.8 0.6,-0.9 1.6,0 0.4,-0.4 0,-0.9 -0.6,-0.2 -0.4,0.5 -1,0 0,-0.5 0.8,-0.7 -1,-0.6 0.2,-1.5 0,-1.9 -1.2,0 -1.4,-1.5 -0.3,-1.4 1.9,-1.5 1.8,-0.2 -0.8,-2.2 -1.6,-1.5 0,-1.5 -1.3,-1.5 -0.4,-1.5 0.7,-1.5 1,-1.9 -1.7,0.6 -2,0 -1.7,0 -1.6,1.3 0,1.9 -1.2,0 -1.1,1.1 -0.4,1.8 1.1,1 0.4,1.5 -0.4,0.9 0.4,1.1 0,1.5 0,1.3 0,1.1 -0.4,1.1 -0.7,0.4 1.1,0 0,0.9 -1.1,0 0,1.5 -0.4,0.6 -1,0.6 0,2.2 0.8,-0.4 -1,2.1 0,2.2 0.2,2.4 0.8,0.2 -1,2.2 0,2.5 1,0.5 -0.3,1.9 0.5,2.1 -0.5,2 -0.5,1.9 -0.6,1.4 0.4,1.9 0,2.6 1,1.5 0.6,1.3 0.7,0.6 -0.7,0.9 0.4,0.6 -0.4,1.4 0,1 0.4,0.5 1.7,0.6 0.2,2.2 z"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)" /><g
         id="g1254"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1256"
           inkscape:connector-curvature="0"
           d="m 146.6,357.4 0.4,1.2 0.6,0.3 0,0.2 1,-0.5 1.7,0 1,-1 0.6,1.3 1.7,0 2,-1.9 1.6,-1.4 2.1,-0.4 1.6,-0.8 1.9,-0.3 0.8,-0.6 -0.4,-1.3 -0.7,-0.9 -0.6,-1.6 1.9,-0.1 1.6,-0.7 1.7,0.7 1.4,1.1 0.4,1.5 -0.4,1.5 0.4,1.5 0.2,-2.4 1,-1.5 -0.4,-1.6 -1.2,-0.8 -1.4,-1.2 -0.9,-1.1 -1,-1.3 -0.6,-0.9 -0.8,-0.2 0,-1.3 -0.2,-1.5 0,-1.5 0,-0.9 0,-0.2 0,-1.7 0,-0.5 0.2,-0.9 1,-0.3 1,-0.9 0,-0.4 0,-0.9 1.3,-0.6 0,-0.5 -0.7,-1.5 -0.6,-1.5 -2.6,-0.9 -2.7,-0.4 -2.6,0 1,-0.8 0,-0.9 -0.5,-1.3 0.5,-0.6 -0.6,-0.9 -2.3,0 -1.8,0.6 0,-0.9 0.4,-0.8 0.6,-0.9 1.6,0 0.4,-0.4 0,-0.9 -0.6,-0.2 -0.4,0.5 -1,0 0,-0.5 0.8,-0.7 -1,-0.6 0.2,-1.5 0,-1.9 -1.2,0 -1.4,-1.5 -0.3,-1.4 1.9,-1.5 1.8,-0.2 -0.8,-2.2 -1.6,-1.5 0,-1.5 -1.3,-1.5 -0.4,-1.5 0.7,-1.5 1,-1.9 -1.7,0.6 -2,0 -1.7,0 -1.6,1.3 0,1.9 -1.2,0 -1.1,1.1 -0.4,1.8 1.1,1 0.4,1.5 -0.4,0.9 0.4,1.1 0,1.5 0,1.3 0,1.1 -0.4,1.1 -0.7,0.4 1.1,0 0,0.9 -1.1,0 0,1.5 -0.4,0.6 -1,0.6 0,2.2 0.8,-0.4 -1,2.1 0,2.2 0.2,2.4 0.8,0.2 -1,2.2 0,2.5 1,0.5 -0.3,1.9 0.5,2.1 -0.5,2 -0.5,1.9 -0.6,1.4 0.4,1.9 0,2.6 1,1.5 0.6,1.3 0.7,0.6 -0.7,0.9 0.4,0.6 -0.4,1.4 0,1 0.4,0.5 1.7,0.6 0.2,2.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1258"
         inkscape:connector-curvature="0"
         d="m 153.37474,318.7 -0.8,2.4 -0.7,2.6 1.1,-0.7 0.4,-1 2.3,-1.3 1.6,-1.1 1.7,-0.4 -2.1,-0.9 -1.8,0.4 -1.7,0 z" /><g
         id="g1260"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1262"
           inkscape:connector-curvature="0"
           d="m 155,293.1 -0.8,2.4 -0.7,2.6 1.1,-0.7 0.4,-1 2.3,-1.3 1.6,-1.1 1.7,-0.4 -2.1,-0.9 -1.8,0.4 -1.7,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1264"
         inkscape:connector-curvature="0"
         d="m 128.27474,464 -0.4,0.5 0,0.4 0,0.9 -0.6,0.2 0,0.4 0.6,-0.4 0.7,-1.1 -0.3,-0.9 z" /><g
         id="g1266"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1268"
           inkscape:connector-curvature="0"
           d="m 129.9,438.4 -0.4,0.5 0,0.4 0,0.9 -0.6,0.2 0,0.4 0.6,-0.4 0.7,-1.1 -0.3,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1270"
         inkscape:connector-curvature="0"
         d="m 126.27474,461.5 -0.2,0.4 0.2,0.2 0,0.5 0.7,0 0,-0.7 -0.7,-0.4 z" /><g
         id="g1272"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1274"
           inkscape:connector-curvature="0"
           d="m 127.9,435.9 -0.2,0.4 0.2,0.2 0,0.5 0.7,0 0,-0.7 -0.7,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1276"
         inkscape:connector-curvature="0"
         d="m 130.07474,461.9 -0.7,0.2 0.2,0.5 -1.1,0.8 0.5,0.2 1.1,-1 0,-0.7 z" /><g
         id="g1278"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1280"
           inkscape:connector-curvature="0"
           d="m 131.7,436.3 -0.7,0.2 0.2,0.5 -1.1,0.8 0.5,0.2 1.1,-1 0,-0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1282"
         inkscape:connector-curvature="0"
         d="m 125.67474,465.1 -0.4,0.7 0.8,0 0.9,0 0.4,0 -1.7,-0.7 z" /><g
         id="g1284"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1286"
           inkscape:connector-curvature="0"
           d="m 127.3,439.5 -0.4,0.7 0.8,0 0.9,0 0.4,0 -1.7,-0.7 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1288"
         inkscape:connector-curvature="0"
         d="m 133.57474,455.1 -0.4,0.5 0.7,0.2 0.8,0 -0.4,-0.7 -0.7,0 z" /><g
         id="g1290"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1292"
           inkscape:connector-curvature="0"
           d="m 135.2,429.5 -0.4,0.5 0.7,0.2 0.8,0 -0.4,-0.7 -0.7,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1294"
         inkscape:connector-curvature="0"
         d="m 130.57474,460.9 0,0.3 -0.2,0.7 0.2,0.2 0.6,-1.2 -0.6,0 z" /><g
         id="g1296"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1298"
           inkscape:connector-curvature="0"
           d="m 132.2,435.3 0,0.3 -0.2,0.7 0.2,0.2 0.6,-1.2 -0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1300"
         inkscape:connector-curvature="0"
         d="m 131.57474,458.6 -0.7,1.4 0.7,-1.2 0,-0.2 z" /><g
         id="g1302"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1304"
           inkscape:connector-curvature="0"
           d="m 133.2,433 -0.7,1.4 0.7,-1.2 0,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1306"
         inkscape:connector-curvature="0"
         d="m 126.97474,460.3 0,0.3 0.4,0.4 0,-0.7 -0.4,0 z" /><g
         id="g1308"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1310"
           inkscape:connector-curvature="0"
           d="m 128.6,434.7 0,0.3 0.4,0.4 0,-0.7 -0.4,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1312"
         inkscape:connector-curvature="0"
         d="m 135.27474,457.6 -0.7,0 0,0.4 0.7,-0.4 z" /><g
         id="g1314"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1316"
           inkscape:connector-curvature="0"
           d="m 136.9,432 -0.7,0 0,0.4 0.7,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1318"
         inkscape:connector-curvature="0"
         d="m 156.87474,441 -0.2,1 0.2,0 0.5,-0.6 -0.5,-0.4 z" /><g
         id="g1320"
         transform="translate(-1.6252567,25.599982)"><path

           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1322"
           inkscape:connector-curvature="0"
           d="m 158.5,415.4 -0.2,1 0.2,0 0.5,-0.6 -0.5,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1324"
         inkscape:connector-curvature="0"
         d="m 105.87474,450.4 1.1,0.9 0.6,0 0.5,-0.4 -0.5,-1.5 -0.4,-1.1 -0.7,-0.5 -0.6,-0.8 -0.4,0.4 0.4,3 z" /><g
         id="g1326"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1328"
           inkscape:connector-curvature="0"
           d="m 107.5,424.8 1.1,0.9 0.6,0 0.5,-0.4 -0.5,-1.5 -0.4,-1.1 -0.7,-0.5 -0.6,-0.8 -0.4,0.4 0.4,3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1330"
         inkscape:connector-curvature="0"
         d="m 139.67474,395.6 0.5,1.6 0,1.4 0.5,2 -1.6,2.4 2.1,-0.3 1,0.7 2.3,1.5 2,0 0.2,-2.2 0.8,-1.5 1.6,-1 2.9,-0.7 3.2,-1.3 0.5,-2.1 0,-1.8 3.7,-0.4 1.2,-2.2 0,-2.2 -0.7,-2.1 -1.2,1.1 -5.1,0 0,-0.6 -1,-0.9 0.4,-1.1 -1,-1.5 -1.6,0 -0.6,-1.3 -1.1,1 -1.6,0 -1.1,0.5 0,-0.2 -0.6,-0.3 -0.4,-1.2 -1.2,0 -0.6,1.7 -0.8,1.8 -0.6,2.5 -1.1,1.9 -1,1.1 0.5,1.2 0.4,0.8 0.1,-0.2 0.7,0.6 -1.7,1.3 z" /><g
         id="g1332"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1334"
           inkscape:connector-curvature="0"
           d="m 141.3,370 0.5,1.6 0,1.4 0.5,2 -1.6,2.4 2.1,-0.3 1,0.7 2.3,1.5 2,0 0.2,-2.2 0.8,-1.5 1.6,-1 2.9,-0.7 3.2,-1.3 0.5,-2.1 0,-1.8 3.7,-0.4 1.2,-2.2 0,-2.2 -0.7,-2.1 -1.2,1.1 -5.1,0 0,-0.6 -1,-0.9 0.4,-1.1 -1,-1.5 -1.6,0 -0.6,-1.3 -1.1,1 -1.6,0 -1.1,0.5 0,-0.2 -0.6,-0.3 -0.4,-1.2 -1.2,0 -0.6,1.7 -0.8,1.8 -0.6,2.5 -1.1,1.9 -1,1.1 0.5,1.2 0.4,0.8 0.1,-0.2 0.7,0.6 -1.7,1.3 z" /></g><path
         style="fill:#ffd42a;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="BR"
         inkscape:connector-curvature="0"
         d="m 165.27474,424.4 1.2,-0.8 2,0.4 1,1.5 0.6,1.5 0.5,0.4 0.6,-0.6 1,-3.2 1.2,-0.2 -0.2,-1.2 -2,-1.6 -1.1,-1.1 -1.1,-1.4 2.2,0.5 1.2,1.5 3.6,-0.3 -0.9,-1.7 -2.9,-0.6 2.2,-0.4 2.7,2.4 2,-0.5 2.3,-0.7 1.6,-1.2 -0.4,-1.3 2.5,0.8 2.6,-0.8 2.7,0 2.8,-1.5 2.5,-1.6 2.4,-0.4 1,-1.5 0.9,-1.8 -0.9,-3 -1.2,-1.5 -1.4,-1.8 -1.2,-2.1 -1.5,-0.6 -0.8,-1.3 0,-2 0.2,-1.8 -0.2,-3 -0.8,-1.1 -0.2,-1.9 -1.6,-3 -0.4,-1.4 -1.3,-1.6 -1.8,0 -2.5,-0.7 -2.6,-1.1 -2.4,-1.1 -1.7,-1.7 -0.2,-2.2 0,-2.3 -0.8,-1.1 -1.2,-1.8 -0.7,-1.5 -0.9,-1.5 -1.5,-1.3 -0.6,-1.5 -1.2,-1.5 -0.5,1.3 0.5,1 -2.1,1.5 -1.6,1.1 -1,0 -2.3,1.4 -0.6,0 0.6,1 1.1,1.3 0.8,1.1 1.4,1.1 1.2,0.9 0.4,1.5 -1,1.5 -0.2,2.4 -0.8,0.8 -0.6,-0.4 -1.2,2.8 -3.7,0.2 -1.1,3.3 0.7,2 0,2.3 -1.3,2.2 -3.6,0.4 0,1.8 -0.4,2.1 -3.3,1.3 -2.8,0.7 -1.7,1 -0.8,1.5 -0.2,2.1 -2.1,0 -2.2,-1.4 -1,-0.7 -2.1,0.3 -1,0 -0.6,0 0,2.4 -1,-0.6 -1.6,0 -1.3,0.6 -0.9,1 -1.1,1.5 0,1.8 1.1,0.6 0.1,1.5 0.8,1.1 2.9,1.3 1.4,0 0.2,0 1,4.8 -0.2,1.1 -1,0.5 0.2,1 1,0 0,0.6 -1,0.3 0,0.9 0.8,0.2 2.3,-0.2 0,0.6 1.2,0 0.8,-1.1 0.2,-0.4 1,-0.5 1.3,0.2 2.6,0.8 1.7,1 -0.2,0.6 -1.7,0.3 0.2,1.5 -1.2,1 1.7,0 1.9,-0.6 0.7,0.6 2,0.5 1.2,0.9 0.7,0.4 0.4,-1.3 0.6,-0.9 -0.6,-2.1 0.6,-1.5 2,-0.5 2,0.5 1.2,0.2 1.1,-0.2 0.4,1.2 1.7,0.3 0.6,-0.5 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="gBR"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1340"
           inkscape:connector-curvature="0"
           d="m 166.9,398.8 1.2,-0.8 2,0.4 1,1.5 0.6,1.5 0.5,0.4 0.6,-0.6 1,-3.2 1.2,-0.2 -0.2,-1.2 -2,-1.6 -1.1,-1.1 -1.1,-1.4 2.2,0.5 1.2,1.5 3.6,-0.3 -0.9,-1.7 -2.9,-0.6 2.2,-0.4 2.7,2.4 2,-0.5 2.3,-0.7 1.6,-1.2 -0.4,-1.3 2.5,0.8 2.6,-0.8 2.7,0 2.8,-1.5 2.5,-1.6 2.4,-0.4 1,-1.5 0.9,-1.8 -0.9,-3 -1.2,-1.5 -1.4,-1.8 -1.2,-2.1 -1.5,-0.6 -0.8,-1.3 0,-2 0.2,-1.8 -0.2,-3 -0.8,-1.1 -0.2,-1.9 -1.6,-3 -0.4,-1.4 -1.3,-1.6 -1.8,0 -2.5,-0.7 -2.6,-1.1 -2.4,-1.1 -1.7,-1.7 -0.2,-2.2 0,-2.3 -0.8,-1.1 -1.2,-1.8 -0.7,-1.5 -0.9,-1.5 -1.5,-1.3 -0.6,-1.5 -1.2,-1.5 -0.5,1.3 0.5,1 -2.1,1.5 -1.6,1.1 -1,0 -2.3,1.4 -0.6,0 0.6,1 1.1,1.3 0.8,1.1 1.4,1.1 1.2,0.9 0.4,1.5 -1,1.5 -0.2,2.4 -0.8,0.8 -0.6,-0.4 -1.2,2.8 -3.7,0.2 -1.1,3.3 0.7,2 0,2.3 -1.3,2.2 -3.6,0.4 0,1.8 -0.4,2.1 -3.3,1.3 -2.8,0.7 -1.7,1 -0.8,1.5 -0.2,2.1 -2.1,0 -2.2,-1.4 -1,-0.7 -2.1,0.3 -1,0 -0.6,0 0,2.4 -1,-0.6 -1.6,0 -1.3,0.6 -0.9,1 -1.1,1.5 0,1.8 1.1,0.6 0.1,1.5 0.8,1.1 2.9,1.3 1.4,0 0.2,0 1,4.8 -0.2,1.1 -1,0.5 0.2,1 1,0 0,0.6 -1,0.3 0,0.9 0.8,0.2 2.3,-0.2 0,0.6 1.2,0 0.8,-1.1 0.2,-0.4 1,-0.5 1.3,0.2 2.6,0.8 1.7,1 -0.2,0.6 -1.7,0.3 0.2,1.5 -1.2,1 1.7,0 1.9,-0.6 0.7,0.6 2,0.5 1.2,0.9 0.7,0.4 0.4,-1.3 0.6,-0.9 -0.6,-2.1 0.6,-1.5 2,-0.5 2,0.5 1.2,0.2 1.1,-0.2 0.4,1.2 1.7,0.3 0.6,-0.5 z" /></g><path
         style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="CL"
         inkscape:connector-curvature="0"
         d="m 138.47474,390.3 0.6,0.6 0.6,0.9 1,-1.1 1.1,-1.9 0.6,-2.4 0.8,-1.9 0.6,-1.6 1.2,0 -0.2,-2.2 -1.6,-0.6 -0.4,-0.6 0,-0.9 0.4,-1.5 -0.4,-0.5 0.6,-0.9 -0.6,-0.6 -0.6,-1.3 -1,-1.5 0,-2.6 -0.5,-1.8 0.6,-1.5 0.5,-1.9 0.6,-2 -0.6,-2.1 0.4,-1.8 -1,-0.6 0,-2.4 1,-2.2 -0.9,-0.2 -0.1,-2.4 0,-2.3 1,-2 -0.9,0.3 0,-2.2 1.1,-0.5 0.4,-0.6 0,-1.5 1,0 0,-0.9 -1,0 0.6,-0.4 0.4,-1.1 0,-1.1 0,-1.3 0,-1.5 -0.4,-1.1 0.4,-0.9 -0.4,-1.5 -1,-0.9 0.4,-1.9 1,-1.1 1.2,0 0,-1.9 1.6,-1.3 1.7,0 2.1,0 1.6,-0.5 -1.2,-0.2 -1.5,-0.9 -0.2,-1 0,-1.4 -0.8,-0.6 -0.8,0.2 -0.9,0.9 0.9,0.6 0.8,0.3 -0.8,1.2 -1.9,-0.2 1.4,-1 -0.5,-0.6 0,-0.3 -1.1,0.3 0.2,0.6 -1.2,0.4 0.4,0.8 -0.8,-0.6 0,1.3 1.4,0 0,0.7 -1,0.4 -0.4,-0.6 -0.8,1 -0.8,1.5 0,0.1 -1.2,1.3 0.6,0.2 -0.5,0.9 0.9,0 -1,0.8 -0.5,1.7 0,0.9 1.5,0 -1,1.3 -1.5,0 0.8,1.1 -2,1 -0.6,-0.4 0.2,1.8 1.6,0 0.8,-1.4 0.7,1.4 -0.7,2.1 0.2,0.4 0.6,1.1 -0.8,0.3 0,1.1 0,2.3 0,2 -1.4,0 -0.6,0.4 -0.4,1.5 0.4,1.7 -0.6,2.2 -0.8,2.6 0.8,1.3 0.6,2.4 0.6,2.6 0,2.2 0,0.8 -0.6,2.4 0,1.8 0,2.4 0.4,2.7 0.2,2.4 0,2.4 -0.2,2.4 0.2,0.9 0,1.9 0,3.3 -0.6,2.6 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="gCL"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1346"
           inkscape:connector-curvature="0"
           d="m 140.1,364.7 0.6,0.6 0.6,0.9 1,-1.1 1.1,-1.9 0.6,-2.4 0.8,-1.9 0.6,-1.6 1.2,0 -0.2,-2.2 -1.6,-0.6 -0.4,-0.6 0,-0.9 0.4,-1.5 -0.4,-0.5 0.6,-0.9 -0.6,-0.6 -0.6,-1.3 -1,-1.5 0,-2.6 -0.5,-1.8 0.6,-1.5 0.5,-1.9 0.6,-2 -0.6,-2.1 0.4,-1.8 -1,-0.6 0,-2.4 1,-2.2 -0.9,-0.2 -0.1,-2.4 0,-2.3 1,-2 -0.9,0.3 0,-2.2 1.1,-0.5 0.4,-0.6 0,-1.5 1,0 0,-0.9 -1,0 0.6,-0.4 0.4,-1.1 0,-1.1 0,-1.3 0,-1.5 -0.4,-1.1 0.4,-0.9 -0.4,-1.5 -1,-0.9 0.4,-1.9 1,-1.1 1.2,0 0,-1.9 1.6,-1.3 1.7,0 2.1,0 1.6,-0.5 -1.2,-0.2 -1.5,-0.9 -0.2,-1 0,-1.4 -0.8,-0.6 -0.8,0.2 -0.9,0.9 0.9,0.6 0.8,0.3 -0.8,1.2 -1.9,-0.2 1.4,-1 -0.5,-0.6 0,-0.3 -1.1,0.3 0.2,0.6 -1.2,0.4 0.4,0.8 -0.8,-0.6 0,1.3 1.4,0 0,0.7 -1,0.4 -0.4,-0.6 -0.8,1 -0.8,1.5 0,0.1 -1.2,1.3 0.6,0.2 -0.5,0.9 0.9,0 -1,0.8 -0.5,1.7 0,0.9 1.5,0 -1,1.3 -1.5,0 0.8,1.1 -2,1 -0.6,-0.4 0.2,1.8 1.6,0 0.8,-1.4 0.7,1.4 -0.7,2.1 0.2,0.4 0.6,1.1 -0.8,0.3 0,1.1 0,2.3 0,2 -1.4,0 -0.6,0.4 -0.4,1.5 0.4,1.7 -0.6,2.2 -0.8,2.6 0.8,1.3 0.6,2.4 0.6,2.6 0,2.2 0,0.8 -0.6,2.4 0,1.8 0,2.4 0.4,2.7 0.2,2.4 0,2.4 -0.2,2.4 0.2,0.9 0,1.9 0,3.3 -0.6,2.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1348"
         inkscape:connector-curvature="0"
         d="m 151.97474,323.7 0.7,-2.6 0.8,-2.4 -2.1,-0.4 -3.1,0.9 4.1,0.4 -1.6,1.5 0.6,0.9 -1.4,-0.3 0,1.5 0.8,0.5 1.2,0 z" /><g
         id="g1350"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1352"
           inkscape:connector-curvature="0"
           d="m 153.6,298.1 0.7,-2.6 0.8,-2.4 -2.1,-0.4 -3.1,0.9 4.1,0.4 -1.6,1.5 0.6,0.9 -1.4,-0.3 0,1.5 0.8,0.5 1.2,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1354"
         inkscape:connector-curvature="0"
         d="m 110.77474,439 0.6,0 0.8,0 0.6,0 1,-0.5 1,0 0.7,-1 0.2,-0.5 0.8,-0.4 0.2,-0.4 -0.6,0 -0.4,-1.1 0,-0.4 0.4,-0.5 -0.4,-0.6 -0.2,0.3 -0.4,0.8 -0.3,-0.1 -0.4,0.1 0,1 -1.4,0.5 -1.2,1.3 0.4,-0.9 -1.6,0.9 0.2,0.6 0,0.9 z" /><g
         id="g1356"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1358"
           inkscape:connector-curvature="0"
           d="m 112.4,413.4 0.6,0 0.8,0 0.6,0 1,-0.5 1,0 0.7,-1 0.2,-0.5 0.8,-0.4 0.2,-0.4 -0.6,0 -0.4,-1.1 0,-0.4 0.4,-0.5 -0.4,-0.6 -0.2,0.3 -0.4,0.8 -0.3,-0.1 -0.4,0.1 0,1 -1.4,0.5 -1.2,1.3 0.4,-0.9 -1.6,0.9 0.2,0.6 0,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1360"
         inkscape:connector-curvature="0"
         d="m 136.27474,450.3 0,1.3 0,1.5 0.2,0 1.4,0 1.7,-0.4 0.6,-0.6 2.2,-0.5 -0.6,-0.9 -2.7,0 -1,0 -1.2,-1.3 -0.6,0.9 z" /><g
         id="g1362"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1364"
           inkscape:connector-curvature="0"
           d="m 137.9,424.7 0,1.3 0,1.5 0.2,0 1.4,0 1.7,-0.4 0.6,-0.6 2.2,-0.5 -0.6,-0.9 -2.7,0 -1,0 -1.2,-1.3 -0.6,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1366"
         inkscape:connector-curvature="0"
         d="m 122.57474,423.1 2.1,-1 1,-0.7 1.3,0 1,-0.4 1,-1.4 -1,-1.4 -1.7,-1.6 -2.1,-0.8 -1.2,-1.5 -0.6,-1.5 -1.4,0.8 -1,0.2 0.4,1.4 0.4,0 0.2,1.4 -0.2,0.5 -0.8,-0.5 -1.2,0.5 0.6,0.6 -0.2,1.5 0.6,0.4 0,0.5 0.6,0.9 0,1 0.6,0.1 1.6,1 z" /><g
         id="g1368"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1370"
           inkscape:connector-curvature="0"
           d="m 124.2,397.5 2.1,-1 1,-0.7 1.3,0 1,-0.4 1,-1.4 -1,-1.4 -1.7,-1.6 -2.1,-0.8 -1.2,-1.5 -0.6,-1.5 -1.4,0.8 -1,0.2 0.4,1.4 0.4,0 0.2,1.4 -0.2,0.5 -0.8,-0.5 -1.2,0.5 0.6,0.6 -0.2,1.5 0.6,0.4 0,0.5 0.6,0.9 0,1 0.6,0.1 1.6,1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1372"
         inkscape:connector-curvature="0"
         d="m 105.87474,450.4 -0.4,-3 0.4,-0.4 0.4,-0.1 0.3,0 0,-0.6 -1.1,-0.4 0,-0.6 -0.2,-0.5 -0.4,0 -0.4,-0.8 -0.6,-0.5 -2.3,0.3 -1.599997,1.2 0.599997,0.5 -0.4,0.8 1,1.1 2.1,0 0.4,0.5 -1.4,1.4 0.5,1.1 3.1,0 z" /><g
         id="g1374"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1376"
           inkscape:connector-curvature="0"
           d="m 107.5,424.8 -0.4,-3 0.4,-0.4 0.4,-0.1 0.3,0 0,-0.6 -1.1,-0.4 0,-0.6 -0.2,-0.5 -0.4,0 -0.4,-0.8 -0.6,-0.5 -2.3,0.3 -1.6,1.2 0.6,0.5 -0.4,0.8 1,1.1 2.1,0 0.4,0.5 -1.4,1.4 0.5,1.1 3.1,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1378"
         inkscape:connector-curvature="0"
         d="m 156.27474,433.9 2.4,-1.5 -0.8,-1.5 1,0.6 2,-1.2 -0.4,-1.5 -1.2,-0.5 -0.4,-1.3 1.6,-1 1.1,-2.4 -1.3,-0.2 -2,-0.6 -2,0.6 -0.6,1.5 0.6,2.1 -0.6,0.9 -0.5,1.3 -0.6,-0.4 -1.2,1.2 0.6,0.9 0,0.6 1.2,0.4 -0.6,0.9 1.7,1.1 z" /><g
         id="g1380"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1382"
           inkscape:connector-curvature="0"
           d="m 157.9,408.3 2.4,-1.5 -0.8,-1.5 1,0.6 2,-1.2 -0.4,-1.5 -1.2,-0.5 -0.4,-1.3 1.6,-1 1.1,-2.4 -1.3,-0.2 -2,-0.6 -2,0.6 -0.6,1.5 0.6,2.1 -0.6,0.9 -0.5,1.3 -0.6,-0.4 -1.2,1.2 0.6,0.9 0,0.6 1.2,0.4 -0.6,0.9 1.7,1.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1384"
         inkscape:connector-curvature="0"
         d="m 136.27474,453.1 0,-1.5 0,-1.3 -3.6,0 -1.1,0.4 0.4,0.9 3.5,-0.4 -0.8,0.6 -0.4,0.8 -0.6,0.7 1.6,0 1,-0.2 z" /><g
         id="g1386"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1388"
           inkscape:connector-curvature="0"
           d="m 137.9,427.5 0,-1.5 0,-1.3 -3.6,0 -1.1,0.4 0.4,0.9 3.5,-0.4 -0.8,0.6 -0.4,0.8 -0.6,0.7 1.6,0 1,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1390"
         inkscape:connector-curvature="0"
         d="m 106.47474,446.9 1,0 2.1,0 2.2,0.1 1.3,-0.1 1.4,0 0.4,-0.6 -0.8,-0.4 1.6,-0.4 -2.2,-0.7 -0.6,0.5 -0.5,-0.5 -1,-0.9 -1.2,-0.4 -1.1,-1.1 -0.6,0 -0.4,0.2 -0.1,0.3 -0.5,1 -1.2,0.5 -1.4,0.4 0.4,0 0.2,0.5 0,0.6 1,0.4 0,0.6 z" /><g
         id="g1392"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1394"
           inkscape:connector-curvature="0"
           d="m 108.1,421.3 1,0 2.1,0 2.2,0.1 1.3,-0.1 1.4,0 0.4,-0.6 -0.8,-0.4 1.6,-0.4 -2.2,-0.7 -0.6,0.5 -0.5,-0.5 -1,-0.9 -1.2,-0.4 -1.1,-1.1 -0.6,0 -0.4,0.2 -0.1,0.3 -0.5,1 -1.2,0.5 -1.4,0.4 0.4,0 0.2,0.5 0,0.6 1,0.4 0,0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1396"
         inkscape:connector-curvature="0"
         d="m 126.87474,450.2 -1.2,0 -0.6,0.6 0.6,0.5 2,-0.4 0.6,-0.7 -1.4,0 z" /><g
         id="g1398"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1400"
           inkscape:connector-curvature="0"
           d="m 128.5,424.6 -1.2,0 -0.6,0.6 0.6,0.5 2,-0.4 0.6,-0.7 -1.4,0 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1402"
         inkscape:connector-curvature="0"
         d="m 162.97474,571.3 -1.8,0.7 -1.3,0 -1.5,0 -0.7,0.6 0.7,1 -0.4,0.5 0.8,1 0.8,-0.6 2.6,-0.8 0.8,-2.4 z" /><g
         id="g1404"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1406"
           inkscape:connector-curvature="0"
           d="m 164.6,545.7 -1.8,0.7 -1.3,0 -1.5,0 -0.7,0.6 0.7,1 -0.4,0.5 0.8,1 0.8,-0.6 2.6,-0.8 0.8,-2.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1408"
         inkscape:connector-curvature="0"
         d="m 125.67474,434.8 0.6,-1.2 0,1.2 1.4,1.3 0.9,0.1 0.4,1.5 1.4,0.9 0.6,-0.1 0.2,0.5 2.7,0.6 0.8,0.4 0.2,0.5 1,0.6 0.6,-0.6 -0.6,-0.4 -1,-0.1 -0.2,-1 -1,-0.9 -0.9,-0.9 0,-0.6 0,-0.9 0.5,0 1,-1.9 0,-1.1 1.2,-0.9 2.6,0 1.1,-1.1 3.6,0 -0.9,-1.5 0.4,-1.9 0.5,-0.9 -0.3,-0.9 0.3,-1.1 0.7,-1.9 -0.3,0.4 -0.7,1.1 -1.3,0 0,-0.5 -2.2,0.1 -0.9,-0.1 0,-1 1.1,-0.4 0,-0.5 -1.1,0 -0.2,-0.9 1.1,-0.6 0.2,-1.1 -1.1,-4.8 -0.2,0 -0.8,0.9 0.4,1.5 -1.2,0.6 -1.4,0.3 -1,-0.7 -1.1,0 -0.6,1.3 -1.6,1.1 -1,1.2 -1,0.3 -0.7,0.3 -0.9,0.4 -1.3,0 -1,0.7 -2,0.9 -0.3,0.6 0.6,1.3 2.1,1.2 0.2,0.9 -0.2,0.5 0.2,0.9 -0.2,1.4 0.2,1.1 -0.2,0.5 -0.8,1 0.4,0.7 0.4,-0.4 0.6,0.9 0,1.2 z" /><g
         id="g1410"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1412"
           inkscape:connector-curvature="0"
           d="m 127.3,409.2 0.6,-1.2 0,1.2 1.4,1.3 0.9,0.1 0.4,1.5 1.4,0.9 0.6,-0.1 0.2,0.5 2.7,0.6 0.8,0.4 0.2,0.5 1,0.6 0.6,-0.6 -0.6,-0.4 -1,-0.1 -0.2,-1 -1,-0.9 -0.9,-0.9 0,-0.6 0,-0.9 0.5,0 1,-1.9 0,-1.1 1.2,-0.9 2.6,0 1.1,-1.1 3.6,0 -0.9,-1.5 0.4,-1.9 0.5,-0.9 -0.3,-0.9 0.3,-1.1 0.7,-1.9 -0.3,0.4 -0.7,1.1 -1.3,0 0,-0.5 -2.2,0.1 -0.9,-0.1 0,-1 1.1,-0.4 0,-0.5 -1.1,0 -0.2,-0.9 1.1,-0.6 0.2,-1.1 -1.1,-4.8 -0.2,0 -0.8,0.9 0.4,1.5 -1.2,0.6 -1.4,0.3 -1,-0.7 -1.1,0 -0.6,1.3 -1.6,1.1 -1,1.2 -1,0.3 -0.7,0.3 -0.9,0.4 -1.3,0 -1,0.7 -2,0.9 -0.3,0.6 0.6,1.3 2.1,1.2 0.2,0.9 -0.2,0.5 0.2,0.9 -0.2,1.4 0.2,1.1 -0.2,0.5 -0.8,1 0.4,0.7 0.4,-0.4 0.6,0.9 0,1.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1414"
         inkscape:connector-curvature="0"
         d="m 126.37474,453.2 1,1.4 -2.1,0.5 -0.6,1.6 -2.3,0 -3.2,1.3 0.6,0.2 -2.1,0.4 -1.6,-1.3 -2.2,0 1.2,0.7 1.5,0.8 2.2,0.7 2,0 2.3,-0.7 2.2,-1.2 2.1,-0.9 2.6,-1.1 -0.4,-0.9 1.6,-0.1 1.5,-0.6 -6.3,-0.8 z" /><g
         id="g1416"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1418"
           inkscape:connector-curvature="0"
           d="m 128,427.6 1,1.4 -2.1,0.5 -0.6,1.6 -2.3,0 -3.2,1.3 0.6,0.2 -2.1,0.4 -1.6,-1.3 -2.2,0 1.2,0.7 1.5,0.8 2.2,0.7 2,0 2.3,-0.7 2.2,-1.2 2.1,-0.9 2.6,-1.1 -0.4,-0.9 1.6,-0.1 1.5,-0.6 -6.3,-0.8 z" /></g><path
         style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1420"
         inkscape:connector-curvature="0"
         d="m 61.474743,479.1 3.9,0.1 0,-0.5 6.4,-2.4 4.8,-0.3 0,0.8 3.1,0 2.2,-2.6 0.4,-1.9 2.3,-1.1 1.6,1.5 1.6,-0.5 1.5,-2.8 0.8,-1.2 0.4,-1.9 2.6,-1.2 0.6,0.3 -0.2,-1.1 -0.8,-0.4 -0.2,-1.4 -0.4,-1.9 -0.4,-2.1 0.8,-1.5 -0.8,0.2 0.8,-1.5 1,-1.7 0.7,-1.8 1.6,-0.6 1,-0.7 2.6,0.3 1.699997,0.6 2.3,0.9 0.5,0.9 0.7,1.9 0.7,0.6 1.9,0.6 1.4,0.3 2.1,-0.3 0,-1.2 -1,-1.3 -1.1,-0.9 0.6,-0.2 -0.6,-1.9 -0.6,0.6 -0.4,-0.6 -0.6,0 -1,-0.9 -3.1,0 -0.6,-1.1 1.4,-1.3 -0.3,-0.6 -2.1,0 -1,-1.1 0.4,-0.7 -0.699997,-0.6 -1,1.3 -2.2,1.1 -1.4,0 -2.7,-1 -3.4,2 -3.1,0.9 -2.6,1.5 -2.3,0.5 -2.5,1.5 -1.2,2.1 0.7,0.3 0,1 0.4,0.6 -0.6,1.8 -1.1,2.1 -1.6,2 -3.1,2.8 0.6,0.9 -0.2,1 -1.6,1.1 0,1 -0.8,0 -0.8,1.4 -1.5,2.5 0,2.4 -2.8,1.9 -0.4,-3.4 1.2,-1.5 1,-2 0,-0.9 0.7,-0.8 1,-2.1 0,1 1.4,-4 0.6,-1.3 0.4,-0.7 0.2,-1.5 -0.6,-0.8 -0.6,1.3 -3.1,3 0.5,2.4 -1.5,1.6 -0.6,-0.4 -2,2.3 1.4,-0.4 0.4,1.4 -1.4,1.9 -1.1,1.2 -0.6,2.9 -0.6,2.3 z"
         onmouseout="" /><g
         id="gMX"
         style="fill:#ccff00"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:#ccff00;stroke:#b6dde8;stroke-width:0.24994963;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="MX"
           inkscape:connector-curvature="0"
           d="m 63.1,453.5 3.9,0.1 0,-0.5 6.4,-2.4 4.8,-0.3 0,0.8 3.1,0 2.2,-2.6 0.4,-1.9 2.3,-1.1 1.6,1.5 1.6,-0.5 1.5,-2.8 0.8,-1.2 0.4,-1.9 2.6,-1.2 0.6,0.3 -0.2,-1.1 -0.8,-0.4 -0.2,-1.4 -0.4,-1.9 -0.4,-2.1 0.8,-1.5 -0.8,0.2 0.8,-1.5 1,-1.7 0.7,-1.8 1.6,-0.6 1,-0.7 2.6,0.3 1.7,0.6 2.3,0.9 0.5,0.9 0.7,1.9 0.7,0.6 1.9,0.6 1.4,0.3 2.1,-0.3 0,-1.2 -1,-1.3 -1.1,-0.9 0.6,-0.2 -0.6,-1.9 -0.6,0.6 -0.4,-0.6 -0.6,0 -1,-0.9 -3.1,0 -0.6,-1.1 1.4,-1.3 -0.3,-0.6 -2.1,0 -1,-1.1 0.4,-0.7 -0.7,-0.6 -1,1.3 -2.2,1.1 -1.4,0 -2.7,-1 -3.4,2 -3.1,0.9 -2.6,1.5 -2.3,0.5 -2.5,1.5 -1.2,2.1 0.7,0.3 0,1 0.4,0.6 -0.6,1.8 -1.1,2.1 -1.6,2 -3.1,2.8 0.6,0.9 -0.2,1 -1.6,1.1 0,1 -0.8,0 -0.8,1.4 -1.5,2.5 0,2.4 -2.8,1.9 -0.4,-3.4 1.2,-1.5 1,-2 0,-0.9 0.7,-0.8 1,-2.1 0,1 1.4,-4 0.6,-1.3 0.4,-0.7 0.2,-1.5 -0.6,-0.8 -0.6,1.3 -3.1,3 0.5,2.4 -1.5,1.6 -0.6,-0.4 -2,2.3 1.4,-0.4 0.4,1.4 -1.4,1.9 -1.1,1.2 -0.6,2.9 -0.6,2.3 z"
           onmouseout="mouseoutpays(this)"
           onclick="mouseclickpays(evt,this)"
           onmouseover="mouseoverpays(evt,this)" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1426"
         inkscape:connector-curvature="0"
         d="m 108.47474,442.4 0.6,0 1.1,1.2 1.2,0.3 1.1,1 0.3,0.5 0.6,-0.5 2.3,0.7 0,-0.7 -0.2,-1 -0.4,-1.2 -0.2,-1.6 0,-1.4 0,-1.2 -1.1,0 -1,0.6 -1,1.1 -1,0.4 0.6,-1.5 -0.6,0 -0.6,0.9 -1.1,0.9 -1.2,1.5 0.6,0 z" /><g
         id="g1428"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1430"
           inkscape:connector-curvature="0"
           d="m 110.1,416.8 0.6,0 1.1,1.2 1.2,0.3 1.1,1 0.3,0.5 0.6,-0.5 2.3,0.7 0,-0.7 -0.2,-1 -0.4,-1.2 -0.2,-1.6 0,-1.4 0,-1.2 -1.1,0 -1,0.6 -1,1.1 -1,0.4 0.6,-1.5 -0.6,0 -0.6,0.9 -1.1,0.9 -1.2,1.5 0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1432"
         inkscape:connector-curvature="0"
         d="m 116.67474,436.3 0.5,-0.6 0.5,0 1.1,-0.6 1.6,0.6 1,0.6 2,-0.3 1.6,-0.9 0.7,-0.3 0,-1.2 -0.7,-0.9 -0.4,0.4 -0.4,-0.8 -0.6,1.5 0.5,0.8 -0.7,0 -0.8,0.5 -0.8,0 -1.8,-1.3 0.8,-1.1 -1.4,-0.4 -0.6,1 -0.5,0 -0.7,0.5 -1.5,0.4 -0.4,0.6 0,0.3 0.4,1.2 0.6,0 z" /><g
         id="g1434"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1436"
           inkscape:connector-curvature="0"
           d="m 118.3,410.7 0.5,-0.6 0.5,0 1.1,-0.6 1.6,0.6 1,0.6 2,-0.3 1.6,-0.9 0.7,-0.3 0,-1.2 -0.7,-0.9 -0.4,0.4 -0.4,-0.8 -0.6,1.5 0.5,0.8 -0.7,0 -0.8,0.5 -0.8,0 -1.8,-1.3 0.8,-1.1 -1.4,-0.4 -0.6,1 -0.5,0 -0.7,0.5 -1.5,0.4 -0.4,0.6 0,0.3 0.4,1.2 0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1438"
         inkscape:connector-curvature="0"
         d="m 151.87474,384.4 1,1.5 -0.4,1.1 1,0.9 0,0.6 5.2,0 1.2,-1.1 1,-3.4 3.7,-0.2 1.2,-2.8 0.6,0.4 0.8,-0.7 -0.4,-1.5 0.4,-1.5 -0.4,-1.5 -1.4,-1.1 -1.7,-0.8 -1.6,0.8 -1.8,0.2 0.6,1.5 0.6,0.9 0.4,1.3 -0.8,0.5 -1.9,0.4 -1.6,0.8 -2,0.3 -1.7,1.5 -2,1.9 z" /><g
         id="g1440"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1442"
           inkscape:connector-curvature="0"
           d="m 153.5,358.8 1,1.5 -0.4,1.1 1,0.9 0,0.6 5.2,0 1.2,-1.1 1,-3.4 3.7,-0.2 1.2,-2.8 0.6,0.4 0.8,-0.7 -0.4,-1.5 0.4,-1.5 -0.4,-1.5 -1.4,-1.1 -1.7,-0.8 -1.6,0.8 -1.8,0.2 0.6,1.5 0.6,0.9 0.4,1.3 -0.8,0.5 -1.9,0.4 -1.6,0.8 -2,0.3 -1.7,1.5 -2,1.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1444"
         inkscape:connector-curvature="0"
         d="m 127.97474,421 0.6,-0.4 1,-0.2 1,-1.3 1.6,-1.1 0.6,-1.3 1.1,0 1,0.8 1.4,-0.4 1.2,-0.6 -0.3,-1.5 0.7,-0.9 -1.3,0 -2.9,-1.3 -0.9,-1.1 -0.1,-1.5 -1.1,-0.5 0,-1.9 1.1,-1.5 1,-1 1.2,-0.5 1.7,0 0.9,0.5 0,-2.4 0.7,0 1,0 1.6,-2.4 -0.6,-2 0,-1.3 -0.4,-1.7 -0.8,0.2 0,-0.9 1.2,-0.9 0.4,0 -0.4,-0.8 -0.4,-1.3 -0.6,-0.9 -0.6,-0.6 -1.6,1.3 -1.7,1.1 -3.1,1.5 -2.2,1.3 -2.2,2.2 -0.8,2.4 -1.3,1 -0.5,1.8 -1.5,1.9 -1,2.1 -1.7,2.4 -2.6,2 0,2.4 0.8,1 1.2,0.5 -0.4,-1.5 1,-0.1 1.5,-0.8 0.6,1.5 1.2,1.5 2.1,0.7 1.6,1.7 1,1.3 -1,1.5 z" /><g
         id="g1446"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1448"
           inkscape:connector-curvature="0"
           d="m 129.6,395.4 0.6,-0.4 1,-0.2 1,-1.3 1.6,-1.1 0.6,-1.3 1.1,0 1,0.8 1.4,-0.4 1.2,-0.6 -0.3,-1.5 0.7,-0.9 -1.3,0 -2.9,-1.3 -0.9,-1.1 -0.1,-1.5 -1.1,-0.5 0,-1.9 1.1,-1.5 1,-1 1.2,-0.5 1.7,0 0.9,0.5 0,-2.4 0.7,0 1,0 1.6,-2.4 -0.6,-2 0,-1.3 -0.4,-1.7 -0.8,0.2 0,-0.9 1.2,-0.9 0.4,0 -0.4,-0.8 -0.4,-1.3 -0.6,-0.9 -0.6,-0.6 -1.6,1.3 -1.7,1.1 -3.1,1.5 -2.2,1.3 -2.2,2.2 -0.8,2.4 -1.3,1 -0.5,1.8 -1.5,1.9 -1,2.1 -1.7,2.4 -2.6,2 0,2.4 0.8,1 1.2,0.5 -0.4,-1.5 1,-0.1 1.5,-0.8 0.6,1.5 1.2,1.5 2.1,0.7 1.6,1.7 1,1.3 -1,1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1450"
         inkscape:connector-curvature="0"
         d="m 154.37474,442.5 0,0.7 0.2,0 0,-0.3 -0.2,-0.4 z" /><g
         id="g1452"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1454"
           inkscape:connector-curvature="0"
           d="m 156,416.9 0,0.7 0.2,0 0,-0.3 -0.2,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1456"
         inkscape:connector-curvature="0"
         d="m 160.87474,430.3 2.1,-0.5 1.6,0.5 1.9,-0.5 -1,-1 0.4,-2 0.4,-1 -1,-1.4 -0.7,0.5 -1.6,-0.4 -0.3,-1.1 -1.1,0.2 -1.1,2.5 -1.6,0.9 0.4,1.3 1.2,0.5 0.4,1.5 z" /><g
         id="g1458"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1460"
           inkscape:connector-curvature="0"
           d="m 162.5,404.7 2.1,-0.5 1.6,0.5 1.9,-0.5 -1,-1 0.4,-2 0.4,-1 -1,-1.4 -0.7,0.5 -1.6,-0.4 -0.3,-1.1 -1.1,0.2 -1.1,2.5 -1.6,0.9 0.4,1.3 1.2,0.5 0.4,1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1462"
         inkscape:connector-curvature="0"
         d="m 162.97474,369.9 0.6,0 2.2,-1.5 1,0 1.7,-1.1 2,-1.5 -0.4,-1 0.4,-1.2 -1,-1.5 -1,-0.9 -2.3,-0.3 -1.6,1 -1.4,0.2 -1.2,0.5 0,1.7 0,0.2 0,0.9 0,1.5 0.1,1.5 0,1.3 0.9,0.2 z" /><g
         id="g1464"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1466"
           inkscape:connector-curvature="0"
           d="m 164.6,344.3 0.6,0 2.2,-1.5 1,0 1.7,-1.1 2,-1.5 -0.4,-1 0.4,-1.2 -1,-1.5 -1,-0.9 -2.3,-0.3 -1.6,1 -1.4,0.2 -1.2,0.5 0,1.7 0,0.2 0,0.9 0,1.5 0.1,1.5 0,1.3 0.9,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1468"
         inkscape:connector-curvature="0"
         d="m 136.47474,440.5 -1.3,-1.3 0,-0.5 0,-1.2 -0.6,-0.9 0.6,-1.3 1,0 0.3,1.3 -0.3,1.5 0.7,0.6 1.6,0.3 -0.4,0.6 0.4,0.9 0.6,-1.3 0.6,0 1.5,-0.5 0.1,-1 2.1,0 1.6,0 0.6,-0.6 2.1,-0.1 1.6,0.5 -1,0.2 4.3,0 -1.7,-0.2 0.8,-0.9 1.7,0 1,-0.9 0.2,-1.1 0.6,0 1.1,-0.8 -1.7,-1.1 0.6,-0.9 -1.2,-0.4 0,-0.6 -0.6,-0.8 1.2,-1.2 -1.2,-0.9 -2.1,-0.6 -0.6,-0.5 -2,0.5 -1.6,0 1.2,-0.9 -0.2,-1.4 1.6,-0.4 0.2,-0.6 -1.6,-0.9 -2.7,-0.9 -1.2,-0.2 -1,0.5 -0.6,1.9 -0.4,1.1 0.4,0.9 -0.7,0.9 -0.3,1.9 1,1.5 -3.7,0 -1,1.1 -2.7,0 -1.2,0.9 0,1.1 -1,1.9 -0.4,0 0,0.9 0,0.5 0.8,1 1,0.9 0.2,1 1,0.1 0.7,0.4 z" /><g
         id="g1470"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1472"
           inkscape:connector-curvature="0"
           d="m 138.1,414.9 -1.3,-1.3 0,-0.5 0,-1.2 -0.6,-0.9 0.6,-1.3 1,0 0.3,1.3 -0.3,1.5 0.7,0.6 1.6,0.3 -0.4,0.6 0.4,0.9 0.6,-1.3 0.6,0 1.5,-0.5 0.1,-1 2.1,0 1.6,0 0.6,-0.6 2.1,-0.1 1.6,0.5 -1,0.2 4.3,0 -1.7,-0.2 0.8,-0.9 1.7,0 1,-0.9 0.2,-1.1 0.6,0 1.1,-0.8 -1.7,-1.1 0.6,-0.9 -1.2,-0.4 0,-0.6 -0.6,-0.8 1.2,-1.2 -1.2,-0.9 -2.1,-0.6 -0.6,-0.5 -2,0.5 -1.6,0 1.2,-0.9 -0.2,-1.4 1.6,-0.4 0.2,-0.6 -1.6,-0.9 -2.7,-0.9 -1.2,-0.2 -1,0.5 -0.6,1.9 -0.4,1.1 0.4,0.9 -0.7,0.9 -0.3,1.9 1,1.5 -3.7,0 -1,1.1 -2.7,0 -1.2,0.9 0,1.1 -1,1.9 -0.4,0 0,0.9 0,0.5 0.8,1 1,0.9 0.2,1 1,0.1 0.7,0.4 z" /></g><path
         style="fill:#ffb380;fill-opacity:1;fill-rule:evenodd;stroke:#00f6ae;stroke-width:0.24994963;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:0.03717472;stroke-dasharray:none"
         id="US"
         inkscape:connector-curvature="0"
         d="m 107.07474,512.6 1.4,1.5 1.1,-0.5 0,-1.7 -1.5,0 2.5,-0.7 0,0.7 1.2,-1.5 0.6,-0.6 2.4,-0.1 1.9,-0.4 -2.2,-0.6 -1.7,-0.9 -1.6,-1.3 0.6,0 1.3,0.4 -0.3,-0.6 3.3,0.2 2,1.3 0.7,0 -1.1,-0.5 -0.6,-1 1.2,0 0.5,-0.5 1,-0.4 1,0.4 1.8,0.1 0,-0.9 1,-0.2 0.5,-0.7 -1.1,0 -1.6,0.4 -2,-0.6 -2.7,-2.4 2.1,1.5 -1.7,-2.8 -0.6,-1.7 -0.4,-1.8 0,-1.5 1.4,0 0.8,1 0,1.9 0.5,1.5 0.6,1.5 1.4,0.9 0.6,0.4 0.6,1 0.6,0.1 2,-1.1 0,-1.3 -1,-1.1 -0.6,-1.3 0.6,0.7 1.5,0.2 0,-0.9 0,-1.1 -1,-1.4 -1.5,-1.4 2.5,-0.6 2.9,0 2,1 1.2,0.9 -0.2,0.9 -0.4,0.6 3.1,0 1.2,0.3 2.6,1.2 1,1.8 5.5,0 1,0.2 4.1,4.7 0.3,-0.8 1.3,0.4 0.7,-0.6 -1,-2.8 1,-1.4 -0.4,-1.2 -2.3,0 -1.2,-1.3 -2.1,-1.1 -1.4,-1.8 0.4,-1 1,0 0,-0.5 -2.2,-0.4 -1.8,-0.2 -2.5,-0.7 2.3,0 -2.3,-0.6 -1,-0.2 0.2,-0.3 -0.6,-1.2 -1.2,-1.7 -1,1 0,-0.6 0.2,-0.8 -1.3,-1.7 -0.3,1.4 -0.2,0.7 0.5,1.4 -0.9,-0.4 -0.5,-2 -1.2,0.3 1.7,-1.3 -0.5,-0.5 -0.2,-1.3 1.1,-0.2 0,-1.5 -0.4,0.9 -0.5,-0.7 -0.8,-0.2 1.7,-0.4 -0.9,-1.1 -1.2,0.2 0.6,-1 -3.3,-1.6 -2.2,-1.3 -2.5,-1.5 -1.8,-2.4 0.2,-3 0.4,-2.6 0.6,-2.2 -1,-3 -1.2,0 -1,1.3 -0.6,2.1 0,1.4 -0.6,0.4 0.2,1.1 -0.6,1.5 -1.3,1.3 -2,-0.9 -1.4,1.5 -2.7,0 -0.6,0.1 -2.3,-0.1 -2,0 1.6,-1 0.4,-0.9 -1.6,-0.2 -2.4,0.7 -2.5,0.4 -2.399997,0 -1.8,-1.1 -2.5,-0.9 -1.8,-1.9 0.2,-2.4 -0.7,-0.3 -2.6,1.3 -0.4,1.8 -0.8,1.1 -1.4,2.8 -1.7,0.6 -1.6,-1.5 -2.3,1.1 -0.4,1.9 -2.2,2.6 -3.1,0 0,-0.8 -4.8,0.2 -6.3,2.4 0,0.5 -3.9,-0.1 -0.4,1.6 -1.3,1.4 -1.4,0.9 -1.8,0.2 0,1.8 -1.5,2.5 0.5,0.9 -0.9,0.5 0,1.3 0.9,-0.3 -0.5,1.5 -1,-0.4 -0.6,2.2 0,2.1 -0.4,0.9 1,2.6 0.4,3.3 2.1,3.8 1.2,3.1 0.6,2.4 0.4,4 2.3,-1 0,-1.1 -0.3,-1.3 1.3,0.9 0.4,2.8 -0.4,1.1 44.999997,-4.1 z"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)" /><g
         id="g1476"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1478"
           inkscape:connector-curvature="0"
           d="m 108.7,487 1.4,1.5 1.1,-0.5 0,-1.7 -1.5,0 2.5,-0.7 0,0.7 1.2,-1.5 0.6,-0.6 2.4,-0.1 1.9,-0.4 -2.2,-0.6 -1.7,-0.9 -1.6,-1.3 0.6,0 1.3,0.4 -0.3,-0.6 3.3,0.2 2,1.3 0.7,0 -1.1,-0.5 -0.6,-1 1.2,0 0.5,-0.5 1,-0.4 1,0.4 1.8,0.1 0,-0.9 1,-0.2 0.5,-0.7 -1.1,0 -1.6,0.4 -2,-0.6 -2.7,-2.4 2.1,1.5 -1.7,-2.8 -0.6,-1.7 -0.4,-1.8 0,-1.5 1.4,0 0.8,1 0,1.9 0.5,1.5 0.6,1.5 1.4,0.9 0.6,0.4 0.6,1 0.6,0.1 2,-1.1 0,-1.3 -1,-1.1 -0.6,-1.3 0.6,0.7 1.5,0.2 0,-0.9 0,-1.1 -1,-1.4 -1.5,-1.4 2.5,-0.6 2.9,0 2,1 1.2,0.9 -0.2,0.9 -0.4,0.6 3.1,0 1.2,0.3 2.6,1.2 1,1.8 5.5,0 1,0.2 4.1,4.7 0.3,-0.8 1.3,0.4 0.7,-0.6 -1,-2.8 1,-1.4 -0.4,-1.2 -2.3,0 -1.2,-1.3 -2.1,-1.1 -1.4,-1.8 0.4,-1 1,0 0,-0.5 -2.2,-0.4 -1.8,-0.2 -2.5,-0.7 2.3,0 -2.3,-0.6 -1,-0.2 0.2,-0.3 -0.6,-1.2 -1.2,-1.7 -1,1 0,-0.6 0.2,-0.8 -1.3,-1.7 -0.3,1.4 -0.2,0.7 0.5,1.4 -0.9,-0.4 -0.5,-2 -1.2,0.3 1.7,-1.3 -0.5,-0.5 -0.2,-1.3 1.1,-0.2 0,-1.5 -0.4,0.9 -0.5,-0.7 -0.8,-0.2 1.7,-0.4 -0.9,-1.1 -1.2,0.2 0.6,-1 -3.3,-1.6 -2.2,-1.3 -2.5,-1.5 -1.8,-2.4 0.2,-3 0.4,-2.6 0.6,-2.2 -1,-3 -1.2,0 -1,1.3 -0.6,2.1 0,1.4 -0.6,0.4 0.2,1.1 -0.6,1.5 -1.3,1.3 -2,-0.9 -1.4,1.5 -2.7,0 -0.6,0.1 -2.3,-0.1 -2,0 1.6,-1 0.4,-0.9 -1.6,-0.2 -2.4,0.7 -2.5,0.4 -2.4,0 -1.8,-1.1 -2.5,-0.9 -1.8,-1.9 0.2,-2.4 -0.7,-0.3 -2.6,1.3 -0.4,1.8 -0.8,1.1 -1.4,2.8 -1.7,0.6 -1.6,-1.5 -2.3,1.1 -0.4,1.9 -2.2,2.6 -3.1,0 0,-0.8 -4.8,0.2 -6.3,2.4 0,0.5 -3.9,-0.1 -0.4,1.6 -1.3,1.4 -1.4,0.9 -1.8,0.2 0,1.8 -1.5,2.5 0.5,0.9 -0.9,0.5 0,1.3 0.9,-0.3 -0.5,1.5 -1,-0.4 -0.6,2.2 0,2.1 -0.4,0.9 1,2.6 0.4,3.3 2.1,3.8 1.2,3.1 0.6,2.4 0.4,4 2.3,-1 0,-1.1 -0.3,-1.3 1.3,0.9 0.4,2.8 -0.4,1.1 45,-4.1 z" /></g><path
         style="fill:#ffaaaa;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1480"
         inkscape:connector-curvature="0"
         d="m 68.974743,582.4 -14.9,-19.5 -5.5,-9.8 2.4,-0.4 -0.1,-0.5 0.9,-4.3 2.3,1.4 1.2,0.4 0.8,-0.6 0,-2 0.6,-7.1 1.1,-1.6 1,-1.5 -0.4,-0.8 -1.3,-1.6 -1,-0.8 0,1 1,1.3 -0.4,1.1 -0.6,0 -1,-0.6 -0.4,0 1,1 0.4,1.1 0.2,0.9 -0.6,0 0,0.5 -0.6,1.4 0.6,0.9 0.4,-0.4 -0.4,1.3 -0.6,0.7 0.6,1 -0.4,-0.2 -0.6,0.6 0.4,2.6 -1.4,-3 -0.6,0.6 0.4,1.8 -0.6,-0.6 -0.8,0.9 0.4,-2.1 -1.1,-0.2 -0.5,0.9 -0.7,2 0.2,0.4 -0.6,0 -1,1.5 0.4,0.6 0.2,0.5 0.4,-0.9 0.6,0.7 -0.2,0.6 -1.4,-0.6 -1,0 -0.6,0.6 0.4,0.6 -1.1,-0.4 -1,0.9 -2.2,0.4 -1,1.1 0.6,0.3 0,0.6 -1,-0.6 -0.7,0.6 -1,0.9 0.4,0.6 1.1,0.5 -1.9,-0.2 -0.8,0 -0.4,0.2 -0.4,0 -0.6,-0.7 0.4,-0.7 -2,-1.5 -1.3,0.3 -2.4,-1.2 -1.2,-0.6 -1.4,0 0.4,0.6 1.9,0.9 -1.3,0 0,0.3 1,1 1.6,1.6 1.6,0.8 0.5,-0.4 1,0.4 -0.4,0.2 1,0.7 -1.4,0 0.4,0.7 -1.3,-0.7 -2,-0.9 -2,-1.5 -0.6,0 0.4,-0.7 -0.8,-0.2 -1.7,-1 -2.3,-1.4 0.7,-0.4 0.4,-0.5 -2.6,-1.9 -2.7,-1.2 -2.2,-1.2 -3.1,-1.7 -1.6,-0.9 -1.3,-0.9 -2,-1 0,0.5 -1,-0.1 -2.2999997,-0.6 0.2,0.6 -0.5,-0.4 -0.3,-0.2 0,0.6 0,0.6 0.6,0.5 1.9,0 -0.2,-0.2 0.2,-0.3 0.3999997,0.3 0.4,-0.3 0,0.9 1,0.9 2.2,1.1 1.1,-0.2 0.6,1.1 2,0.9 0.2,0 0,0.2 1,1.9 0.6,0.6 2.1,1.8 -3.1,-0.5 0.9,1.4 -2.1,-2 -0.4,1.7 -0.2,-0.2 -0.4,0.9 -2.3,-0.9 -1,0.2 0.7,0.3 0.3,1.2 1.1,0.9 1,3.3 -1,-1.4 -1.4,0 -1.3,0.3 0,1.5 -0.6,1.1 1.2,0.9 1.3,-0.9 0,-0.5 1,0.9 0.4,1.5 -1,-0.6 -0.7,-0.4 -1,0.6 -0.4,1.5 0.4,0.9 0.4,0.6 0.6,0 0.7,1.3 1.6,1.1 1.6,0.5 -0.6,0 1.2,1.4 1.5,0.3 -0.5,-0.9 1.5,0 1.8,1.1 0.8,-0.2 2.2,0.8 0.7,1.4 0,1 1.4,0.7 0.2,0.8 -1,-0.2 -2.5,-1 -0.7,0.6 -0.7,0.6 -1.6,0 -1.6,1.3 0.6,1.1 0,1.5 0.6,-0.6 0.8,-0.5 0.8,0.3 -0.8,0.2 -1.4,1.3 -0.6,0.6 0.4,0.9 0.2,0 3.7,0.9 0,-0.4 0.9,0 0,0.4 2.3,0.8 1.4,-0.2 -1,-1 -0.4,-0.9 2.7,-1.1 1.2,0.6 -0.2,-1 0.6,1 -0.4,0.5 -0.6,0 0.6,2.1 0.4,0 0,-1.5 0.6,-0.8 1.4,-0.3 1,0.9 -2.4,0.5 -0.3,0.4 1.1,0.8 -2.6,1.3 0.6,2 -1,3 1,0 1.6,1.5 0.6,-0.6 2.3,-0.2 2.4,0.6 1.6,1.1 3.3,1.3 3.2,0.2 -0.6,-1.1 1.3,0.5 0,0.6 1.8,0.7 1.8,-0.9 3.3,0.9 0.4,-0.9 -2,-0.9 2.6,0.4 -0.2,-1.3 0.8,0.3 1,-0.3 1,-1 -1,-0.2 0.8,-0.7 -0.8,0 1.5,-0.6 -1.1,-1.1 2.7,1.1 1,-0.1 0.6,-0.5 0.7,-1.2 0,-0.6 1.5,0 1.1,-1.1 2.2,-0.3 2.5,-2.5 z" /><g
         id="g1482"
         style="fill:#ffb380"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:#ffb380;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1484"
           inkscape:connector-curvature="0"
           d="m 70.6,556.8 -14.9,-19.5 -5.5,-9.8 2.4,-0.4 -0.1,-0.5 0.9,-4.3 2.3,1.4 1.2,0.4 0.8,-0.6 0,-2 0.6,-7.1 1.1,-1.6 1,-1.5 -0.4,-0.8 -1.3,-1.6 -1,-0.8 0,1 1,1.3 -0.4,1.1 -0.6,0 -1,-0.6 -0.4,0 1,1 0.4,1.1 0.2,0.9 -0.6,0 0,0.5 -0.6,1.4 0.6,0.9 0.4,-0.4 -0.4,1.3 -0.6,0.7 0.6,1 -0.4,-0.2 -0.6,0.6 0.4,2.6 -1.4,-3 -0.6,0.6 0.4,1.8 -0.6,-0.6 -0.8,0.9 0.4,-2.1 -1.1,-0.2 -0.5,0.9 -0.7,2 0.2,0.4 -0.6,0 -1,1.5 0.4,0.6 0.2,0.5 0.4,-0.9 0.6,0.7 -0.2,0.6 -1.4,-0.6 -1,0 -0.6,0.6 0.4,0.6 -1.1,-0.4 -1,0.9 -2.2,0.4 -1,1.1 0.6,0.3 0,0.6 -1,-0.6 -0.7,0.6 -1,0.9 0.4,0.6 1.1,0.5 -1.9,-0.2 -0.8,0 -0.4,0.2 -0.4,0 -0.6,-0.7 0.4,-0.7 -2,-1.5 -1.3,0.3 -2.4,-1.2 -1.2,-0.6 -1.4,0 0.4,0.6 1.9,0.9 -1.3,0 0,0.3 1,1 1.6,1.6 1.6,0.8 0.5,-0.4 1,0.4 -0.4,0.2 1,0.7 -1.4,0 0.4,0.7 -1.3,-0.7 -2,-0.9 -2,-1.5 -0.6,0 0.4,-0.7 -0.8,-0.2 -1.7,-1 -2.3,-1.4 0.7,-0.4 0.4,-0.5 -2.6,-1.9 -2.7,-1.2 -2.2,-1.2 -3.1,-1.7 -1.6,-0.9 -1.3,-0.9 -2,-1 0,0.5 -1,-0.1 -2.3,-0.6 0.2,0.6 -0.5,-0.4 -0.3,-0.2 0,0.6 0,0.6 0.6,0.5 1.9,0 -0.2,-0.2 0.2,-0.3 0.4,0.3 0.4,-0.3 0,0.9 1,0.9 2.2,1.1 1.1,-0.2 0.6,1.1 2,0.9 0.2,0 0,0.2 1,1.9 0.6,0.6 2.1,1.8 -3.1,-0.5 0.9,1.4 -2.1,-2 -0.4,1.7 -0.2,-0.2 -0.4,0.9 -2.3,-0.9 -1,0.2 0.7,0.3 0.3,1.2 1.1,0.9 1,3.3 -1,-1.4 -1.4,0 -1.3,0.3 0,1.5 -0.6,1.1 1.2,0.9 1.3,-0.9 0,-0.5 1,0.9 0.4,1.5 -1,-0.6 -0.7,-0.4 -1,0.6 -0.4,1.5 0.4,0.9 0.4,0.6 0.6,0 0.7,1.3 1.6,1.1 1.6,0.5 -0.6,0 1.2,1.4 1.5,0.3 -0.5,-0.9 1.5,0 1.8,1.1 0.8,-0.2 2.2,0.8 0.7,1.4 0,1 1.4,0.7 0.2,0.8 -1,-0.2 -2.5,-1 -0.7,0.6 -0.7,0.6 -1.6,0 -1.6,1.3 0.6,1.1 0,1.5 0.6,-0.6 0.8,-0.5 0.8,0.3 -0.8,0.2 -1.4,1.3 -0.6,0.6 0.4,0.9 0.2,0 3.7,0.9 0,-0.4 0.9,0 0,0.4 2.3,0.8 1.4,-0.2 -1,-1 -0.4,-0.9 2.7,-1.1 1.2,0.6 -0.2,-1 0.6,1 -0.4,0.5 -0.6,0 0.6,2.1 0.4,0 0,-1.5 0.6,-0.8 1.4,-0.3 1,0.9 -2.4,0.5 -0.3,0.4 1.1,0.8 -2.6,1.3 0.6,2 -1,3 1,0 1.6,1.5 0.6,-0.6 2.3,-0.2 2.4,0.6 1.6,1.1 3.3,1.3 3.2,0.2 -0.6,-1.1 1.3,0.5 0,0.6 1.8,0.7 1.8,-0.9 3.3,0.9 0.4,-0.9 -2,-0.9 2.6,0.4 -0.2,-1.3 0.8,0.3 1,-0.3 1,-1 -1,-0.2 0.8,-0.7 -0.8,0 1.5,-0.6 -1.1,-1.1 2.7,1.1 1,-0.1 0.6,-0.5 0.7,-1.2 0,-0.6 1.5,0 1.1,-1.1 2.2,-0.3 2.5,-2.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1486"
         inkscape:connector-curvature="0"
         d="m 166.87474,325.1 -0.6,0.8 0.6,1 0.4,0.7 0.6,0 0.6,0 0.5,-0.6 0.2,-0.1 -1.3,-0.8 -1,-1 z" /><g
         id="g1488"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1490"
           inkscape:connector-curvature="0"
           d="m 168.5,299.5 -0.6,0.8 0.6,1 0.4,0.7 0.6,0 0.6,0 0.5,-0.6 0.2,-0.1 -1.3,-0.8 -1,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1492"
         inkscape:connector-curvature="0"
         d="m 164.87474,325.4 -0.7,0.4 0.4,1.6 1.9,0 -0.6,-1.4 -1,-0.6 z" /><g
         id="g1494"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1496"
           inkscape:connector-curvature="0"
           d="m 166.5,299.8 -0.7,0.4 0.4,1.6 1.9,0 -0.6,-1.4 -1,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1498"
         inkscape:connector-curvature="0"
         d="m 166.47474,429.7 1.6,-0.5 1.6,-1.3 0.4,-1 -0.6,-1.4 -1,-1.5 -2,-0.4 -1.2,0.7 1,1.5 -0.4,0.9 -0.4,2.1 1,0.9 z" /><g
         id="g1500"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1502"
           inkscape:connector-curvature="0"
           d="m 168.1,404.1 1.6,-0.5 1.6,-1.3 0.4,-1 -0.6,-1.4 -1,-1.5 -2,-0.4 -1.2,0.7 1,1.5 -0.4,0.9 -0.4,2.1 1,0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1504"
         inkscape:connector-curvature="0"
         d="m 146.47474,449.8 -2.5,0.4 0.4,0.7 1.7,-0.2 1,-0.3 -0.6,-0.6 z" /><g
         id="g1506"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1508"
           inkscape:connector-curvature="0"
           d="m 148.1,424.2 -2.5,0.4 0.4,0.7 1.7,-0.2 1,-0.3 -0.6,-0.6 z" /></g><path
         style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="AU"
         inkscape:connector-curvature="0"
         d="m 506.77474,343.5 -0.8,1.1 -0.3,0.4 0,0.3 0,0.6 -0.9,-0.6 -0.4,0 0.7,0.6 0,0.6 -1,-0.2 0.3,0 0,-0.8 -2.3,-1.1 -1,0.9 -1.1,0.2 -0.6,0.8 -0.2,-0.4 -1,0.6 -0.8,0.3 -0.2,0.6 -0.6,0.9 0,0.9 0.6,1.1 -1,1.9 0.6,0.4 0,0.2 -1,0 -0.8,-0.2 0.6,0.5 0.2,1 -0.2,1.6 -0.6,-0.5 -0.9,-1.5 -0.6,0 -0.6,-0.4 0.2,0.8 0.8,0 0.2,1.5 1,1.1 0.5,0.9 0.2,0.4 0,1.5 -0.2,-1.4 -0.5,0 -1,-1.1 -1,-0.5 -1.2,-0.9 -0.7,-0.7 0.3,-0.6 -0.7,0 -0.2,0.6 0,0.3 0,0.9 -0.3,0.9 -0.4,1 -0.7,0.1 0,1 0.4,0.4 -0.8,0.1 0.4,0.5 -0.6,0.5 -0.6,-0.2 -0.8,0.5 -0.6,0 -1.7,1.2 -1.6,-0.2 -3,-0.4 -2.9,-0.9 -1.6,0.3 -2.7,-0.8 -1,-0.6 -0.6,-1 -0.4,-0.5 -1,-0.4 -0.3,0 -1,0 -0.6,-0.3 -0.4,0.7 -2,0.1 -1,-0.1 -1.7,-0.7 -0.2,-0.3 -1,-0.3 -1.4,-0.6 -0.8,-0.4 -1.5,0 -1,0.8 -0.6,0.8 -1,0.3 0.4,1.1 0.2,0 0.8,0.4 0.2,1.5 0.4,0 0.6,1.5 -0.2,1.3 -0.8,2.4 0.4,2.1 -0.4,1.1 -0.6,0.7 0.4,1.1 -0.8,1.3 -0.6,1.7 0.4,0 0.2,-0.9 0.4,0 -0.4,0.9 0,0.9 0.4,-0.3 0,-0.6 0.4,0 0.2,-0.6 0,1.2 -0.2,0.7 -0.4,2.1 0,0.5 0.6,1.3 0.4,1.1 0.4,1 0.6,0.8 -0.4,-1.2 0.4,0 0.7,1.2 1,0.3 1.2,0.7 2,1 1.1,0 1,0.5 1,0 0.8,0.2 1,0.4 3.1,1.1 1,1.7 0.8,0.2 0,0.5 0,0.9 1,1.4 0.4,0.5 0.4,0 0.3,-0.5 0.6,-1.4 0,1 0.8,-0.4 -0.4,0.8 -0.4,0.9 0.4,0.2 0.4,-0.6 0.2,0.4 0.4,-0.4 0.6,0 -0.4,0 0,0.6 0,0.5 0.8,0.8 0.2,-0.4 0,0.5 0.4,0.4 0.6,-0.4 -0.6,1 1.1,0.5 0.2,-0.5 1,1.3 0.4,0.2 0.2,-0.6 0.8,0.9 0.2,-0.5 1.7,-1.3 -0.7,-0.6 0.4,-0.5 0.6,0.7 0.6,-0.2 0.7,-0.4 0.4,0.4 0.6,-0.5 -0.2,0.5 0.2,0 -0.6,1.1 0.6,0.8 0.4,0.7 0.6,0 -0.2,0.9 0.8,0.6 0.8,0 0,0.4 0.3,0.4 2.4,0 0.2,0 -0.2,1 -0.4,0 -0.4,0.6 0.8,0 0.6,-0.2 0.2,0 0.4,-0.5 1.5,-0.4 0.1,-0.4 1.1,0 0.6,-0.1 1,0.1 0.6,-0.1 0.4,-0.4 0.4,-0.4 -0.4,0.8 0.6,0.1 0.4,-0.1 0.2,-0.4 -0.6,-0.6 0,-0.4 -0.2,-0.5 0,0.4 -0.8,-0.4 -0.2,-0.6 0,-0.3 0,-0.6 -1,-1.5 1,-0.9 0.2,-0.4 0.8,-0.2 0.2,-0.5 1.7,-1.3 2,-0.4 0,-0.7 1.7,-0.8 0.5,0 0.7,0.4 1,1.9 1,3.1 0,1.7 0.4,0.5 -0.4,0.6 0.6,0.9 -0.2,0.2 0.2,0.8 0.4,0 0.4,1.6 0.8,0.8 0,-0.4 0.5,-0.6 0,-1.3 0.4,0 0,-0.5 0.2,-0.4 0,-0.7 0.4,0 0,-1.5 0.4,-1.7 0.2,0.4 0.6,0.5 0.4,-0.9 1,-0.6 0,-2 0,-0.5 0.6,-1 0.4,-1.5 0,-0.9 0,-0.6 0.2,-0.7 0.9,-0.8 0.8,0 0.4,-0.9 0.4,0 0.8,-0.5 0.4,-0.8 0.5,-0.2 -0.9,-0.4 0.4,-0.6 0.7,-0.8 0,-0.4 0.7,-1.7 0.3,-0.3 0,1.2 0.9,-0.9 0,0.2 0.4,-0.5 0,-1.5 -0.8,0 0.4,-0.6 1.1,-0.6 0.6,-0.7 0.4,-0.9 0.6,-0.6 0.4,-0.6 -0.4,-0.9 0.6,-1.1 -0.6,-2.4 0.4,-1.3 -0.4,-2.1 -1,-2 -0.6,-2.2 -1.7,-2.5 0,-0.2 0,-0.4 -2,-1.4 -0.4,-1 -0.2,-0.8 -1.4,-1.6 -0.7,-1.1 -1.6,-2.8 -1,-2.4 -1.6,-0.7 -2.7,0 -1,-1 -1.8,-0.9 0.2,-0.4 -0.2,-0.5 z"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"
         onmouseover="mouseoverpays(evt,this)" /><g
         id="g1512"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1514"
           inkscape:connector-curvature="0"
           d="m 508.4,317.9 -0.8,1.1 -0.3,0.4 0,0.3 0,0.6 -0.9,-0.6 -0.4,0 0.7,0.6 0,0.6 -1,-0.2 0.3,0 0,-0.8 -2.3,-1.1 -1,0.9 -1.1,0.2 -0.6,0.8 -0.2,-0.4 -1,0.6 -0.8,0.3 -0.2,0.6 -0.6,0.9 0,0.9 0.6,1.1 -1,1.9 0.6,0.4 0,0.2 -1,0 -0.8,-0.2 0.6,0.5 0.2,1 -0.2,1.6 -0.6,-0.5 -0.9,-1.5 -0.6,0 -0.6,-0.4 0.2,0.8 0.8,0 0.2,1.5 1,1.1 0.5,0.9 0.2,0.4 0,1.5 -0.2,-1.4 -0.5,0 -1,-1.1 -1,-0.5 -1.2,-0.9 -0.7,-0.7 0.3,-0.6 -0.7,0 -0.2,0.6 0,0.3 0,0.9 -0.3,0.9 -0.4,1 -0.7,0.1 0,1 0.4,0.4 -0.8,0.1 0.4,0.5 -0.6,0.5 -0.6,-0.2 -0.8,0.5 -0.6,0 -1.7,1.2 -1.6,-0.2 -3,-0.4 -2.9,-0.9 -1.6,0.3 -2.7,-0.8 -1,-0.6 -0.6,-1 -0.4,-0.5 -1,-0.4 -0.3,0 -1,0 -0.6,-0.3 -0.4,0.7 -2,0.1 -1,-0.1 -1.7,-0.7 -0.2,-0.3 -1,-0.3 -1.4,-0.6 -0.8,-0.4 -1.5,0 -1,0.8 -0.6,0.8 -1,0.3 0.4,1.1 0.2,0 0.8,0.4 0.2,1.5 0.4,0 0.6,1.5 -0.2,1.3 -0.8,2.4 0.4,2.1 -0.4,1.1 -0.6,0.7 0.4,1.1 -0.8,1.3 -0.6,1.7 0.4,0 0.2,-0.9 0.4,0 -0.4,0.9 0,0.9 0.4,-0.3 0,-0.6 0.4,0 0.2,-0.6 0,1.2 -0.2,0.7 -0.4,2.1 0,0.5 0.6,1.3 0.4,1.1 0.4,1 0.6,0.8 -0.4,-1.2 0.4,0 0.7,1.2 1,0.3 1.2,0.7 2,1 1.1,0 1,0.5 1,0 0.8,0.2 1,0.4 3.1,1.1 1,1.7 0.8,0.2 0,0.5 0,0.9 1,1.4 0.4,0.5 0.4,0 0.3,-0.5 0.6,-1.4 0,1 0.8,-0.4 -0.4,0.8 -0.4,0.9 0.4,0.2 0.4,-0.6 0.2,0.4 0.4,-0.4 0.6,0 -0.4,0 0,0.6 0,0.5 0.8,0.8 0.2,-0.4 0,0.5 0.4,0.4 0.6,-0.4 -0.6,1 1.1,0.5 0.2,-0.5 1,1.3 0.4,0.2 0.2,-0.6 0.8,0.9 0.2,-0.5 1.7,-1.3 -0.7,-0.6 0.4,-0.5 0.6,0.7 0.6,-0.2 0.7,-0.4 0.4,0.4 0.6,-0.5 -0.2,0.5 0.2,0 -0.6,1.1 0.6,0.8 0.4,0.7 0.6,0 -0.2,0.9 0.8,0.6 0.8,0 0,0.4 0.3,0.4 2.4,0 0.2,0 -0.2,1 -0.4,0 -0.4,0.6 0.8,0 0.6,-0.2 0.2,0 0.4,-0.5 1.5,-0.4 0.1,-0.4 1.1,0 0.6,-0.1 1,0.1 0.6,-0.1 0.4,-0.4 0.4,-0.4 -0.4,0.8 0.6,0.1 0.4,-0.1 0.2,-0.4 -0.6,-0.6 0,-0.4 -0.2,-0.5 0,0.4 -0.8,-0.4 -0.2,-0.6 0,-0.3 0,-0.6 -1,-1.5 1,-0.9 0.2,-0.4 0.8,-0.2 0.2,-0.5 1.7,-1.3 2,-0.4 0,-0.7 1.7,-0.8 0.5,0 0.7,0.4 1,1.9 1,3.1 0,1.7 0.4,0.5 -0.4,0.6 0.6,0.9 -0.2,0.2 0.2,0.8 0.4,0 0.4,1.6 0.8,0.8 0,-0.4 0.5,-0.6 0,-1.3 0.4,0 0,-0.5 0.2,-0.4 0,-0.7 0.4,0 0,-1.5 0.4,-1.7 0.2,0.4 0.6,0.5 0.4,-0.9 1,-0.6 0,-2 0,-0.5 0.6,-1 0.4,-1.5 0,-0.9 0,-0.6 0.2,-0.7 0.9,-0.8 0.8,0 0.4,-0.9 0.4,0 0.8,-0.5 0.4,-0.8 0.5,-0.2 -0.9,-0.4 0.4,-0.6 0.7,-0.8 0,-0.4 0.7,-1.7 0.3,-0.3 0,1.2 0.9,-0.9 0,0.2 0.4,-0.5 0,-1.5 -0.8,0 0.4,-0.6 1.1,-0.6 0.6,-0.7 0.4,-0.9 0.6,-0.6 0.4,-0.6 -0.4,-0.9 0.6,-1.1 -0.6,-2.4 0.4,-1.3 -0.4,-2.1 -1,-2 -0.6,-2.2 -1.7,-2.5 0,-0.2 0,-0.4 -2,-1.4 -0.4,-1 -0.2,-0.8 -1.4,-1.6 -0.7,-1.1 -1.6,-2.8 -1,-2.4 -1.6,-0.7 -2.7,0 -1,-1 -1.8,-0.9 0.2,-0.4 -0.2,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1516"
         inkscape:connector-curvature="0"
         d="m 537.97474,320.1 -0.3,0.4 -0.8,0.2 0,0.6 -0.8,0.3 0,0.4 -0.8,0 -0.6,0.2 0.4,0.7 -0.4,0 0,0.2 0.6,0 0,0.6 0.4,0.7 0.6,-0.4 -0.2,0.6 0.2,0 0,0.3 0.4,0.2 0.2,0 0,0.4 1.1,0.4 0.6,0.9 0,-0.4 0.4,0 0,0.6 0.6,0.6 1,0 1.1,0.7 2,1.1 1.7,1.3 1,1.7 0.2,0.5 0.4,0.4 0.6,0.9 0.5,1.2 0.6,0.9 0.4,-0.2 0,-0.7 0,-0.2 0,-0.6 0,-0.7 0,0.3 0.6,0.4 0,-0.4 0.2,-0.3 -0.2,-0.2 0,-0.9 -0.4,-1 -0.6,-1.1 -0.6,-0.9 -0.9,-0.9 -0.2,-1 0.2,-0.5 -0.6,0.2 -0.2,-0.2 -0.4,0.2 0,-0.2 -1,-0.6 -1.1,-0.9 -0.2,-1.3 -0.8,-0.6 -0.6,-0.9 -0.6,-0.6 0.4,-0.3 -0.4,0 -0.6,-0.6 -1.1,-0.5 -0.4,-0.6 -1.6,-0.4 z" /><g
         id="g1518"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1520"
           inkscape:connector-curvature="0"
           d="m 539.6,294.5 -0.3,0.4 -0.8,0.2 0,0.6 -0.8,0.3 0,0.4 -0.8,0 -0.6,0.2 0.4,0.7 -0.4,0 0,0.2 0.6,0 0,0.6 0.4,0.7 0.6,-0.4 -0.2,0.6 0.2,0 0,0.3 0.4,0.2 0.2,0 0,0.4 1.1,0.4 0.6,0.9 0,-0.4 0.4,0 0,0.6 0.6,0.6 1,0 1.1,0.7 2,1.1 1.7,1.3 1,1.7 0.2,0.5 0.4,0.4 0.6,0.9 0.5,1.2 0.6,0.9 0.4,-0.2 0,-0.7 0,-0.2 0,-0.6 0,-0.7 0,0.3 0.6,0.4 0,-0.4 0.2,-0.3 -0.2,-0.2 0,-0.9 -0.4,-1 -0.6,-1.1 -0.6,-0.9 -0.9,-0.9 -0.2,-1 0.2,-0.5 -0.6,0.2 -0.2,-0.2 -0.4,0.2 0,-0.2 -1,-0.6 -1.1,-0.9 -0.2,-1.3 -0.8,-0.6 -0.6,-0.9 -0.6,-0.6 0.4,-0.3 -0.4,0 -0.6,-0.6 -1.1,-0.5 -0.4,-0.6 -1.6,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1522"
         inkscape:connector-curvature="0"
         d="m 550.57474,332.7 0,1 -0.4,-0.4 0,0.6 0.4,0.7 0.6,1.5 0,1.1 -0.4,0.6 -0.2,0.7 -0.4,0.6 0.4,0.6 0.6,0.3 0.6,2 0.4,-0.3 0,1.3 0,1.5 0.3,0 0.4,0.5 -0.7,0 0,1 0,0.4 0.3,-0.4 0,0.4 0,0.5 -0.3,0 -0.4,-0.4 -0.4,3.4 -0.2,1.5 0.2,0 0,-0.4 0,-0.6 0.4,-0.1 0.7,-0.4 0.4,-0.6 0.4,0 0,-1.3 -0.4,-0.2 0.4,-0.7 0,-0.7 0.2,-1 0.4,-0.9 0.6,0 0.6,1.1 0,0.4 0,0.5 1.1,0 0,-0.9 1,-1 1,0 1,1 0.8,0 0,-1 -0.8,-0.7 -3,-5.4 -2.1,-1.9 -1.2,-1.2 -1.1,-1.2 -1.2,-1.5 z" /><g
         id="g1524"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1526"
           inkscape:connector-curvature="0"
           d="m 552.2,307.1 0,1 -0.4,-0.4 0,0.6 0.4,0.7 0.6,1.5 0,1.1 -0.4,0.6 -0.2,0.7 -0.4,0.6 0.4,0.6 0.6,0.3 0.6,2 0.4,-0.3 0,1.3 0,1.5 0.3,0 0.4,0.5 -0.7,0 0,1 0,0.4 0.3,-0.4 0,0.4 0,0.5 -0.3,0 -0.4,-0.4 -0.4,3.4 -0.2,1.5 0.2,0 0,-0.4 0,-0.6 0.4,-0.1 0.7,-0.4 0.4,-0.6 0.4,0 0,-1.3 -0.4,-0.2 0.4,-0.7 0,-0.7 0.2,-1 0.4,-0.9 0.6,0 0.6,1.1 0,0.4 0,0.5 1.1,0 0,-0.9 1,-1 1,0 1,1 0.8,0 0,-1 -0.8,-0.7 -3,-5.4 -2.1,-1.9 -1.2,-1.2 -1.1,-1.2 -1.2,-1.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1528"
         inkscape:connector-curvature="0"
         d="m 507.67474,403.5 0.3,10.8 1.6,-0.5 2.4,-0.6 1.3,-0.6 0.4,0 1.2,-0.9 0.6,0 0.9,-0.6 0,-1.2 2.8,-0.9 0.6,-1.1 -1.7,-0.5 0.7,-1.1 1.4,-0.9 0.4,-0.4 0.2,-0.5 0,-1 1.5,0 0.2,-0.2 -0.2,-0.3 1.2,-0.4 -0.4,-0.5 0.8,-0.4 0.6,-0.2 -0.6,-0.3 0.6,0 0,-0.3 -1,0 0,0.3 -2.9,0.5 -0.8,0 -1.7,1.3 -0.1,0.5 -0.4,0 -0.4,0.6 -0.8,1.5 -0.5,0 -0.4,0 -1,0 -0.2,0.7 -0.4,0 -0.2,-0.3 -0.4,0 -0.6,0.3 0.2,-0.9 -0.6,0 0,-0.4 -1.7,0 0.6,-0.2 0.8,-0.9 -1.4,-0.6 -2.9,0.2 z" /><g
         id="g1530"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1532"
           inkscape:connector-curvature="0"
           d="m 509.3,377.9 0.3,10.8 1.6,-0.5 2.4,-0.6 1.3,-0.6 0.4,0 1.2,-0.9 0.6,0 0.9,-0.6 0,-1.2 2.8,-0.9 0.6,-1.1 -1.7,-0.5 0.7,-1.1 1.4,-0.9 0.4,-0.4 0.2,-0.5 0,-1 1.5,0 0.2,-0.2 -0.2,-0.3 1.2,-0.4 -0.4,-0.5 0.8,-0.4 0.6,-0.2 -0.6,-0.3 0.6,0 0,-0.3 -1,0 0,0.3 -2.9,0.5 -0.8,0 -1.7,1.3 -0.1,0.5 -0.4,0 -0.4,0.6 -0.8,1.5 -0.5,0 -0.4,0 -1,0 -0.2,0.7 -0.4,0 -0.2,-0.3 -0.4,0 -0.6,0.3 0.2,-0.9 -0.6,0 0,-0.4 -1.7,0 0.6,-0.2 0.8,-0.9 -1.4,-0.6 -2.9,0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1534"
         inkscape:connector-curvature="0"
         d="m 524.47474,408.4 -1.4,0 -0.2,0.4 -0.6,0 -0.8,0.2 -0.6,0.4 0,0.5 0.4,-0.2 0.6,0.2 0.6,-0.2 1,0.2 0.4,0.6 0.2,-0.8 0.4,0.2 0.4,-0.2 0.6,0.2 0.2,0.6 0.9,0 0.2,0.3 -0.2,0.9 1.2,0.2 0.4,-0.2 0,-0.9 -0.6,-0.3 0,-0.6 -0.8,-0.2 -0.2,0 -0.4,-0.3 -1.7,-1 z" /><g
         id="g1536"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1538"
           inkscape:connector-curvature="0"
           d="m 526.1,382.8 -1.4,0 -0.2,0.4 -0.6,0 -0.8,0.2 -0.6,0.4 0,0.5 0.4,-0.2 0.6,0.2 0.6,-0.2 1,0.2 0.4,0.6 0.2,-0.8 0.4,0.2 0.4,-0.2 0.6,0.2 0.2,0.6 0.9,0 0.2,0.3 -0.2,0.9 1.2,0.2 0.4,-0.2 0,-0.9 -0.6,-0.3 0,-0.6 -0.8,-0.2 -0.2,0 -0.4,-0.3 -1.7,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1540"
         inkscape:connector-curvature="0"
         d="m 528.77474,410.8 0,0.5 -0.4,1 -0.8,0.9 -1.9,1 -0.6,0.1 0,0.4 2.7,-1.1 1,-1 0,-0.3 0.4,-1 -0.4,-0.5 z" /><g
         id="g1542"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1544"
           inkscape:connector-curvature="0"
           d="m 530.4,385.2 0,0.5 -0.4,1 -0.8,0.9 -1.9,1 -0.6,0.1 0,0.4 2.7,-1.1 1,-1 0,-0.3 0.4,-1 -0.4,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1546"
         inkscape:connector-curvature="0"
         d="m 534.07474,407.4 -1.1,0 -0.4,1 -0.7,0.4 0,0.9 -0.1,0.2 0,0.6 0.1,-0.6 0.5,-0.2 0.6,-0.4 0.4,-0.5 0.2,-0.4 0.5,-0.6 0,-0.4 z" /><g
         id="g1548"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1550"
           inkscape:connector-curvature="0"
           d="m 535.7,381.8 -1.1,0 -0.4,1 -0.7,0.4 0,0.9 -0.1,0.2 0,0.6 0.1,-0.6 0.5,-0.2 0.6,-0.4 0.4,-0.5 0.2,-0.4 0.5,-0.6 0,-0.4 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1552"
         inkscape:connector-curvature="0"
         d="m 543.67474,402 -0.4,1 -1,0.5 0,1.4 0.4,0 0.2,-0.8 0.4,-0.2 0.4,-0.9 0,-1 z" /><g
         id="g1554"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1556"
           inkscape:connector-curvature="0"
           d="m 545.3,376.4 -0.4,1 -1,0.5 0,1.4 0.4,0 0.2,-0.8 0.4,-0.2 0.4,-0.9 0,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1558"
         inkscape:connector-curvature="0"
         d="m 541.07474,404.5 -0.7,0.3 -1,0.6 -0.8,0.5 0.4,0 0.6,-0.3 0.8,0 0.7,-1.1 z" /><g
         id="g1560"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1562"
           inkscape:connector-curvature="0"
           d="m 542.7,378.9 -0.7,0.3 -1,0.6 -0.8,0.5 0.4,0 0.6,-0.3 0.8,0 0.7,-1.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1564"
         inkscape:connector-curvature="0"
         d="m 542.27474,402.5 -0.6,0 -1.1,0 -0.2,0.5 1.7,0 0.2,-0.5 z" /><g
         id="g1566"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1568"
           inkscape:connector-curvature="0"
           d="m 543.9,376.9 -0.6,0 -1.1,0 -0.2,0.5 1.7,0 0.2,-0.5 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1570"
         inkscape:connector-curvature="0"
         d="m 536.07474,406.5 -0.8,0.8 -0.2,0.6 0.6,-0.4 0.4,-0.2 0.6,-0.8 -0.6,0 z" /><g
         id="g1572"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1574"
           inkscape:connector-curvature="0"
           d="m 537.7,380.9 -0.8,0.8 -0.2,0.6 0.6,-0.4 0.4,-0.2 0.6,-0.8 -0.6,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1576"
         inkscape:connector-curvature="0"
         d="m 195.77474,531.7 -1.2,0.4 -2.1,2.4 -2.2,0 -1.6,2.1 -2,5 -0.7,5.7 -0.4,4.5 1.7,3.3 2.2,0.4 1.8,0.9 0.2,2.6 -2.8,2.5 3.8,-1.5 -2,3 -2.6,0.5 0.8,1.9 1.8,1.3 -0.6,7.8 -1,2.6 -2.7,1.5 -2.2,0.9 -2,-0.6 -0.6,0.9 -1.3,0.3 0.3,-1.5 -2.1,0.9 -0.8,1.4 1.2,0.5 1.4,0.5 -1,0 -1.4,0.4 1.4,0.7 -0.4,0.4 -1.6,-0.6 0.4,1.1 2.6,0 2.9,-0.9 1,1.7 -1.4,-0.4 -1.6,0 -1.7,0.6 1.1,0.4 -1.3,0.2 -1.4,0.9 -0.6,0.9 0.6,1.5 2,0.3 1.7,0 2,0.2 0,0.4 1.8,0.4 1.5,0 0.6,-0.4 2.2,1.1 0.4,0.9 1.4,0.4 -1,0.6 1.7,0.4 0,0.2 -0.7,-0.2 -2,-0.4 -0.4,0.6 -1,0 0.4,0.7 1,0.5 2.2,1.2 1.5,0.9 1.6,0.3 0.2,-1.2 0.4,0 0,1.8 1,0 0.6,-0.9 1,0.3 1.7,2.6 1.2,-0.1 0,-1.4 1.6,-1.6 0.4,0 -1.2,3.5 5.9,0.6 0.2,-2 2.7,-1 0.4,0.4 -0.4,1.5 0.6,1.1 2.6,-2 1.7,-2.5 0.8,0.6 -0.4,1.3 1.6,0.7 1.1,-0.6 0.4,0 -2.1,3 3.3,-0.7 1.6,-1.7 0,1.3 -1.1,1.1 -2.5,0.8 -0.7,1.5 1.1,0 5.9,0 4.6,0.5 4.9,-0.9 2.6,-1.1 -1,-0.8 -2.6,0 -3.3,-0.2 -2.2,-0.3 -1.5,-1 3.3,0.8 4.3,0 3.7,0.2 0,-1.5 0.9,-0.4 2.7,-0.7 -1.6,-1 -0.6,-0.3 -1.4,0 -1,-0.6 -1.9,0.4 -3.5,0 -4.9,-1.6 0.4,-0.3 4.5,1 4.3,0 0.4,-1 -2.6,-1.2 0.6,-0.6 2.7,0.9 0.3,1.5 2.3,0.4 0,-1 -1.1,-2.8 -2.6,-2.6 1.9,1.1 2.2,1.5 1,1.6 0.6,0.3 0,-1.1 2,0 0.9,1.1 2.6,0.6 2.5,-1.1 -0.4,-1.4 -2.3,-1.1 0.2,-0.5 -1.8,-0.8 -2.4,-0.2 -1.9,0 2.6,-0.3 1.7,0 -1.1,-1.5 -3.2,0.6 -1,-1.7 0,-0.4 1.2,0.9 2.4,0 -2.6,-2.8 -2.5,-2 -0.1,-2.4 1.6,-1.5 -1.1,-0.3 1.3,-0.3 0.4,-2.2 -2.8,0.8 -1.1,-1.4 1.7,-0.5 0.1,-3.3 -0.1,-3 -1.7,-2.4 -2.6,-0.6 -2,1 -2.3,-0.8 0.6,-1.5 2,-2.4 1.1,-1.1 0,-2.4 -1.9,-0.4 -0.1,0 -1.5,0.8 0,0.9 -1,0.7 -1.6,1 -2.2,1.4 -0.5,-0.5 2,-1.2 1.7,-0.8 -2.9,-0.5 -1,-0.9 -0.4,-0.5 2.6,0 -3.2,-0.6 4.7,0.2 4.2,-0.8 -1.5,-0.9 -4.3,-2.2 -4.1,-0.6 -3.6,-0.5 -1.3,1.1 0.4,-1.5 -1.4,-0.6 -2.7,-2 -3.2,-2.1 -1.6,0 0.6,1.6 -1.1,0 -0.2,-1.9 -2,-0.4 -1,-0.5 -1.3,-1.9 -0.8,-1.5 -1.6,-2.4 -1.2,-3.2 -3.7,-5.2 z" /><g
         id="g1578"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1580"
           inkscape:connector-curvature="0"
           d="m 197.4,506.1 -1.2,0.4 -2.1,2.4 -2.2,0 -1.6,2.1 -2,5 -0.7,5.7 -0.4,4.5 1.7,3.3 2.2,0.4 1.8,0.9 0.2,2.6 -2.8,2.5 3.8,-1.5 -2,3 -2.6,0.5 0.8,1.9 1.8,1.3 -0.6,7.8 -1,2.6 -2.7,1.5 -2.2,0.9 -2,-0.6 -0.6,0.9 -1.3,0.3 0.3,-1.5 -2.1,0.9 -0.8,1.4 1.2,0.5 1.4,0.5 -1,0 -1.4,0.4 1.4,0.7 -0.4,0.4 -1.6,-0.6 0.4,1.1 2.6,0 2.9,-0.9 1,1.7 -1.4,-0.4 -1.6,0 -1.7,0.6 1.1,0.4 -1.3,0.2 -1.4,0.9 -0.6,0.9 0.6,1.5 2,0.3 1.7,0 2,0.2 0,0.4 1.8,0.4 1.5,0 0.6,-0.4 2.2,1.1 0.4,0.9 1.4,0.4 -1,0.6 1.7,0.4 0,0.2 -0.7,-0.2 -2,-0.4 -0.4,0.6 -1,0 0.4,0.7 1,0.5 2.2,1.2 1.5,0.9 1.6,0.3 0.2,-1.2 0.4,0 0,1.8 1,0 0.6,-0.9 1,0.3 1.7,2.6 1.2,-0.1 0,-1.4 1.6,-1.6 0.4,0 -1.2,3.5 5.9,0.6 0.2,-2 2.7,-1 0.4,0.4 -0.4,1.5 0.6,1.1 2.6,-2 1.7,-2.5 0.8,0.6 -0.4,1.3 1.6,0.7 1.1,-0.6 0.4,0 -2.1,3 3.3,-0.7 1.6,-1.7 0,1.3 -1.1,1.1 -2.5,0.8 -0.7,1.5 1.1,0 5.9,0 4.6,0.5 4.9,-0.9 2.6,-1.1 -1,-0.8 -2.6,0 -3.3,-0.2 -2.2,-0.3 -1.5,-1 3.3,0.8 4.3,0 3.7,0.2 0,-1.5 0.9,-0.4 2.7,-0.7 -1.6,-1 -0.6,-0.3 -1.4,0 -1,-0.6 -1.9,0.4 -3.5,0 -4.9,-1.6 0.4,-0.3 4.5,1 4.3,0 0.4,-1 -2.6,-1.2 0.6,-0.6 2.7,0.9 0.3,1.5 2.3,0.4 0,-1 -1.1,-2.8 -2.6,-2.6 1.9,1.1 2.2,1.5 1,1.6 0.6,0.3 0,-1.1 2,0 0.9,1.1 2.6,0.6 2.5,-1.1 -0.4,-1.4 -2.3,-1.1 0.2,-0.5 -1.8,-0.8 -2.4,-0.2 -1.9,0 2.6,-0.3 1.7,0 -1.1,-1.5 -3.2,0.6 -1,-1.7 0,-0.4 1.2,0.9 2.4,0 -2.6,-2.8 -2.5,-2 -0.1,-2.4 1.6,-1.5 -1.1,-0.3 1.3,-0.3 0.4,-2.2 -2.8,0.8 -1.1,-1.4 1.7,-0.5 0.1,-3.3 -0.1,-3 -1.7,-2.4 -2.6,-0.6 -2,1 -2.3,-0.8 0.6,-1.5 2,-2.4 1.1,-1.1 0,-2.4 -1.9,-0.4 -0.1,0 -1.5,0.8 0,0.9 -1,0.7 -1.6,1 -2.2,1.4 -0.5,-0.5 2,-1.2 1.7,-0.8 -2.9,-0.5 -1,-0.9 -0.4,-0.5 2.6,0 -3.2,-0.6 4.7,0.2 4.2,-0.8 -1.5,-0.9 -4.3,-2.2 -4.1,-0.6 -3.6,-0.5 -1.3,1.1 0.4,-1.5 -1.4,-0.6 -2.7,-2 -3.2,-2.1 -1.6,0 0.6,1.6 -1.1,0 -0.2,-1.9 -2,-0.4 -1,-0.5 -1.3,-1.9 -0.8,-1.5 -1.6,-2.4 -1.2,-3.2 -3.7,-5.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1582"
         inkscape:connector-curvature="0"
         d="m 153.97474,436.9 -1.4,0 0.8,0.3 0,0.6 1.1,0 -0.5,-0.9 z" /><g
         id="g1584"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1586"
           inkscape:connector-curvature="0"
           d="m 155.6,411.3 -1.4,0 0.8,0.3 0,0.6 1.1,0 -0.5,-0.9 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1588"
         inkscape:connector-curvature="0"
         d="m 503.67474,332.7 -0.6,0.2 -0.4,0.4 0.4,0 0,0.4 -0.6,0.1 -0.4,0.9 0.4,1.4 0.2,-0.4 0,0.4 -0.2,3.2 0.2,0.7 0.4,0 0.4,-0.4 0.9,-0.6 1.6,-0.3 1,0.3 0.4,0 1,0.3 0,-0.3 -0.6,-3.3 -0.4,0 0,0.4 -0.4,-0.4 0,-0.6 -0.2,-0.5 -0.4,-0.9 -0.6,-0.4 0,0.5 0.2,0 0,0.8 -0.2,0 -0.6,-0.9 -0.4,0 -0.4,-1 -0.7,0 z" /><g
         id="g1590"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1592"
           inkscape:connector-curvature="0"
           d="m 505.3,307.1 -0.6,0.2 -0.4,0.4 0.4,0 0,0.4 -0.6,0.1 -0.4,0.9 0.4,1.4 0.2,-0.4 0,0.4 -0.2,3.2 0.2,0.7 0.4,0 0.4,-0.4 0.9,-0.6 1.6,-0.3 1,0.3 0.4,0 1,0.3 0,-0.3 -0.6,-3.3 -0.4,0 0,0.4 -0.4,-0.4 0,-0.6 -0.2,-0.5 -0.4,-0.9 -0.6,-0.4 0,0.5 0.2,0 0,0.8 -0.2,0 -0.6,-0.9 -0.4,0 -0.4,-1 -0.7,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1594"
         inkscape:connector-curvature="0"
         d="m 356.77474,384.3 -0.6,0.2 0.4,0.9 0.6,-0.9 -0.4,-0.2 z" /><g
         id="g1596"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1598"
           inkscape:connector-curvature="0"
           d="m 358.4,358.7 -0.6,0.2 0.4,0.9 0.6,-0.9 -0.4,-0.2 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1600"
         inkscape:connector-curvature="0"
         d="m 548.87474,378.2 -0.8,0.4 0,0.6 -1,0.7 -0.8,1.7 -0.5,1.5 0.5,-0.2 0.8,-1.3 0.4,-0.2 0,-0.8 0.6,-0.5 0.8,-1.1 0,-0.8 z" /><g
         id="g1602"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1604"
           inkscape:connector-curvature="0"
           d="m 550.5,352.6 -0.8,0.4 0,0.6 -1,0.7 -0.8,1.7 -0.5,1.5 0.5,-0.2 0.8,-1.3 0.4,-0.2 0,-0.8 0.6,-0.5 0.8,-1.1 0,-0.8 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1606"
         inkscape:connector-curvature="0"
         d="m 154.57474,443.8 -0.2,0 -0.4,1 0.6,-0.4 0,-0.6 z" /><g
         id="g1608"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1610"
           inkscape:connector-curvature="0"
           d="m 156.2,418.2 -0.2,0 -0.4,1 0.6,-0.4 0,-0.6 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1612"
         inkscape:connector-curvature="0"
         d="m 232.07474,474.1 -0.7,0.5 0.2,0 0.9,-0.5 -0.4,0 z" /><g
         id="g1614"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1616"
           inkscape:connector-curvature="0"
           d="m 233.7,448.5 -0.7,0.5 0.2,0 0.9,-0.5 -0.4,0 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1618"
         inkscape:connector-curvature="0"
         d="m 232.07474,466 -0.5,0.7 1.2,0.2 -0.3,-0.6 -0.4,-0.3 z" /><g
         id="g1620"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1622"
           inkscape:connector-curvature="0"
           d="m 233.7,440.4 -0.5,0.7 1.2,0.2 -0.3,-0.6 -0.4,-0.3 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1624"
         inkscape:connector-curvature="0"
         d="m 234.07474,465.7 -0.4,0.3 0.6,0.7 -0.2,-1 z" /><g
         id="g1626"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1628"
           inkscape:connector-curvature="0"
           d="m 235.7,440.1 -0.4,0.3 0.6,0.7 -0.2,-1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1630"
         inkscape:connector-curvature="0"
         d="m 153.57474,446.9 -0.2,0 0,0.1 0.6,0.4 0.5,-0.4 -0.9,0 0,-0.1 z" /><g
         id="g1632"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1634"
           inkscape:connector-curvature="0"
           d="m 155.2,421.3 -0.2,0 0,0.1 0.6,0.4 0.5,-0.4 -0.9,0 0,-0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1636"
         inkscape:connector-curvature="0"
         d="m 453.37474,422 0.4,-1 1.8,-0.5 1.1,0 2.1,-0.4 1.8,0.6 1.7,0.9 0.2,1.9 1.1,0.1 0.6,1.9 3,-0.2 0,-0.3 -0.4,-0.4 0,-0.2 -0.2,0 0,-0.4 0.6,-0.4 0.3,-0.5 0,-0.2 -0.3,-0.8 0.7,-0.1 0.9,-1 0.7,-0.3 -0.4,-0.2 -1.6,-0.4 -0.7,-0.4 -0.2,-1.1 0.2,-0.9 -1.2,-0.9 -1,-0.6 0.2,-0.4 -0.2,-1.5 -0.6,-1.1 -2.5,-0.9 -0.2,1.3 -0.7,0 -0.7,-0.4 0,1 -0.2,-0.4 -0.9,0.4 -0.1,-0.4 -0.6,-0.2 -0.4,0.2 -0.6,-0.6 -0.4,1 -0.7,0.1 -1,-0.1 -0.4,0.1 -0.6,-0.1 0,1.1 0,0.5 -0.2,1.3 -0.8,0.6 -0.6,0 0.4,0.4 -0.8,0.1 0.4,0.8 -0.6,0.6 0,0.9 0.2,0.9 0.4,0.6 0.4,0.1 0.2,0 0.4,-0.1 z" /><g
         id="g1638"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1640"
           inkscape:connector-curvature="0"
           d="m 455,396.4 0.4,-1 1.8,-0.5 1.1,0 2.1,-0.4 1.8,0.6 1.7,0.9 0.2,1.9 1.1,0.1 0.6,1.9 3,-0.2 0,-0.3 -0.4,-0.4 0,-0.2 -0.2,0 0,-0.4 0.6,-0.4 0.3,-0.5 0,-0.2 -0.3,-0.8 0.7,-0.1 0.9,-1 0.7,-0.3 -0.4,-0.2 -1.6,-0.4 -0.7,-0.4 -0.2,-1.1 0.2,-0.9 -1.2,-0.9 -1,-0.6 0.2,-0.4 -0.2,-1.5 -0.6,-1.1 -2.5,-0.9 -0.2,1.3 -0.7,0 -0.7,-0.4 0,1 -0.2,-0.4 -0.9,0.4 -0.1,-0.4 -0.6,-0.2 -0.4,0.2 -0.6,-0.6 -0.4,1 -0.7,0.1 -1,-0.1 -0.4,0.1 -0.6,-0.1 0,1.1 0,0.5 -0.2,1.3 -0.8,0.6 -0.6,0 0.4,0.4 -0.8,0.1 0.4,0.8 -0.6,0.6 0,0.9 0.2,0.9 0.4,0.6 0.4,0.1 0.2,0 0.4,-0.1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1642"
         inkscape:connector-curvature="0"
         d="m 277.87474,424 0.6,-0.4 2.2,-0.2 0,-1.9 -1,0 -1,0 -1.2,0 -0.4,0.4 0.8,1.1 0,1 z" /><g
         id="g1644"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1646"
           inkscape:connector-curvature="0"
           d="m 279.5,398.4 0.6,-0.4 2.2,-0.2 0,-1.9 -1,0 -1,0 -1.2,0 -0.4,0.4 0.8,1.1 0,1 z" /></g><path
         style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1648"
         inkscape:connector-curvature="0"
         d="m 336.17474,448 c 0.1,0 0.1,0 0.2,0 l 0.8,0 1.2,0.4 3.4,-1.5 -0.1,-1.4 0.9,0.4 2.9,3.6 5.8,1.3 2.2,-3.9 -1.5,-0.6 -0.1,-0.9 -0.1,-0.4 -5.5,-2 -0.8,-0.7 -1.1,0 -1.6,-0.8 -3.2,-0.6 -0.2,-0.8 -2.4,0 -0.7,0.4 -0.6,2.4 -0.6,2.1 0,1.4 1,0.5 0.1,1.1 z" /><g
         id="g1650"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1652"
           inkscape:connector-curvature="0"
           d="m 337.8,422.4 c 0.1,0 0.1,0 0.2,0 l 0.8,0 1.2,0.4 3.4,-1.5 -0.1,-1.4 0.9,0.4 2.9,3.6 5.8,1.3 2.2,-3.9 -1.5,-0.6 -0.1,-0.9 -0.1,-0.4 -5.5,-2 -0.8,-0.7 -1.1,0 -1.6,-0.8 -3.2,-0.6 -0.2,-0.8 -2.4,0 -0.7,0.4 -0.6,2.4 -0.6,2.1 0,1.4 1,0.5 0.1,1.1 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="CA"
         inkscape:connector-curvature="0"
         d="m 60.074743,572.8 8.3,9.3 1.6,-0.6 1.3,-2.9 2.7,-1.5 0,0.5 0.6,-0.3 0.4,-1.2 0.6,1.5 2,1 7.6,0.9 -0.2,-0.8 -2.7,-0.5 -3.6,-0.2 -1.7,-1.3 1,-0.2 3.5,1.1 -0.6,-0.5 4.3,1.1 -0.2,-0.2 0.2,-0.7 3,1.4 0,0.8 0.7,0.2 -0.7,-3.6 0.7,-0.9 2,2.1 1.2,0.3 -0.4,-1.5 -1.2,-1.3 2,0 1.1,1 1.6,-0.2 0.8,-1.5 3,-2.4 2.299997,-0.4 1,-1.8 -0.2,-1 -1.5,0.4 -1.199997,-0.6 3.899997,-1.5 3.7,0 1.6,-1.8 0,-1.5 -1,0.4 0.6,-2.4 0,1.1 0.6,0.6 0.4,-0.3 0.6,2.5 0.9,0.9 2.4,0 1.4,0.7 -4.2,-0.2 -0.5,0.2 1.5,0.8 3.6,0.2 0.6,-2.5 0.6,-0.5 1.1,0 0.6,-1.8 0.6,0.3 4.3,-0.9 0.8,0.9 0.6,-0.9 1.1,-0.2 -1.1,1.1 0.2,0.9 1.5,0 1,-0.9 -0.6,-0.6 1.6,0.3 -2,-2.1 0.6,-0.6 -1.1,0 0,-1.5 0.5,1 1,0 0.6,2 0.6,0.9 1,-0.3 2.6,0.9 0.5,1.1 -1.7,-0.6 0.6,1 1.1,0.5 0.6,0.4 -0.4,-0.5 0.6,0.1 0.4,0.8 -1,-0.4 -0.2,0.9 -0.9,0 -1.2,1 0,0.5 1.2,1.2 0.9,0 -0.5,1.3 1.1,0.5 1,0.6 0.2,-0.6 0.8,0.9 -0.8,0.2 1.7,0.4 1.6,-0.6 0,-1.4 -0.7,-1.9 0.4,-1.5 -1,0.4 0.4,-0.8 -1.4,-0.6 0.7,-0.9 0.7,0.4 0.9,-0.5 -0.9,-0.4 -0.7,-3 3.4,2.4 0.8,-1.8 -0.6,-1.1 -1,-0.8 0,-2.2 3.2,1.7 1.9,2.2 1.2,0.2 -0.4,0.6 0.8,1.4 3.9,-1.1 -0.6,-0.3 1.2,-1.2 -1,-0.3 0.8,-0.2 -0.4,-1 -2.1,0 0.4,-2.8 -0.5,-1.1 -4.1,-1.3 0,1.5 -0.6,0.8 -0.6,-0.4 0.6,-2.1 -3.9,1.5 1,-1.3 -3.1,-2 -1,0.4 -2,2 -0.6,0 1,-1.8 2.6,-1 -2.2,-2.6 -1.4,-0.5 -1.3,0.1 -0.4,0.8 -1.2,-1.9 -1.4,0 -3.3,2.1 0,-0.6 1.7,-0.5 2,-1.9 -0.6,-0.9 -2.6,0 0.1,-0.6 -4.2,-3 -2.7,-2.4 -1.6,-4.2 0.8,-0.6 1.6,-0.4 -0.4,-3.9 -0.6,-0.8 3.3,0.3 2.8,-1.5 0.9,-1.5 0,-0.9 2.2,-1 1.2,-1.1 4.3,-0.9 -1.7,-5 0.4,-1.7 -0.6,-0.6 1.9,-0.9 0.4,-1.1 0.6,-0.6 1,0.8 0.4,-0.6 1.2,2.8 -0.2,5.4 6,2.6 1,2.4 -0.4,2.8 -1.6,2 3.2,3.4 -0.4,2.8 1.6,1.6 -0.2,1.9 1.6,0.9 3.7,-1.8 1.8,0.5 2.1,-2.6 -0.2,-0.9 3.3,-0.9 -1.4,-3.9 -1.1,-1 1.1,0 0,-0.5 -1.7,-0.4 0.4,-0.5 2.3,0.5 0.6,-2 3,1.5 3.3,4.3 0.4,-0.4 2.2,-9 -1,-1.4 1.6,-1.9 0,-1.3 2.1,0 2.3,-1.7 -3.8,-1.3 -1.6,-0.9 0,-0.7 4.3,1.6 1.1,-0.5 -0.3,-1 1.2,0.6 1.1,-0.7 -0.4,-1.9 -1,-1.5 -3.5,-1.2 -3.5,-2.1 -8.6,0.5 -2.2,-0.1 -5.5,-3.8 -3.1,-2.6 4,2.6 4.4,1.9 1.9,-0.4 1.6,-0.9 -0.6,-0.8 -1.7,-0.9 -1.6,0.2 1.2,-0.8 1.1,0 -1.1,-1.2 1.6,-2.1 4.7,-2.2 -5.4,-1.2 -0.5,-0.5 -2.2,-1.5 -0.9,0.6 0.4,1.1 1.5,1.3 2.9,0.9 -1.7,0 0.7,0.9 -5,-1.3 -1,1.5 1,2.8 -0.6,0.6 -1.4,-0.4 -0.2,0.7 -4.1,-4.6 -1,-0.2 -5.5,0 -2.7,-1.3 -2,-0.2 0.8,-0.7 -1.4,0.3 -2.3,-0.3 -1,-0.8 0.6,-0.3 0.4,-0.6 -2.6,-0.7 -1.6,-0.2 -3.3,-0.6 0.6,0.8 1.1,1.3 1.6,0.9 1.2,2.4 -0.2,1.1 0.2,0 0.4,-1.1 1.6,-0.4 0.4,1 -0.8,1.8 -1.8,0.6 -3.5,1.1 -0.8,1 0.2,2.7 -1,-0.4 -1.2,2.1 -2.7,0.8 -2,-1.9 -1.9,0.4 -2.4,0.1 -0.6,0.6 -1.2,1.5 0,-0.7 -2.5,0.7 1.5,0 0,1.7 -1.1,0.5 -1.4,-1.5 -45.099997,4.1 -1.2,1.5 -0.8,2.4 -3.3,2.2 -0.2,1 0.7,5.9 1,1.1 -2.1,-1.1 -0.6,3 1.7,1.9 1,2.4 -1,1.4 -1.1,1.7 -0.6,7.1 0,2 -0.8,0.5 -1.3,-0.3 -2.2,-1.5 -1,4.3 0.2,0.6 -2.4,0.4 5.4,9.8 6.6,10.2 z"
         inkscape:label="#CA"
         onmouseover="mouseoverpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onclick="mouseclickpays(evt,this)"><title
           id="title15977">Canada</title></path><g
         id="g1656"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1658"
           inkscape:connector-curvature="0"
           d="m 61.7,547.2 8.3,9.3 1.6,-0.6 1.3,-2.9 2.7,-1.5 0,0.5 0.6,-0.3 0.4,-1.2 0.6,1.5 2,1 7.6,0.9 -0.2,-0.8 -2.7,-0.5 -3.6,-0.2 -1.7,-1.3 1,-0.2 3.5,1.1 -0.6,-0.5 4.3,1.1 -0.2,-0.2 0.2,-0.7 3,1.4 0,0.8 0.7,0.2 -0.7,-3.6 0.7,-0.9 2,2.1 1.2,0.3 -0.4,-1.5 -1.2,-1.3 2,0 1.1,1 1.6,-0.2 0.8,-1.5 3,-2.4 2.3,-0.4 1,-1.8 -0.2,-1 -1.5,0.4 -1.2,-0.6 3.9,-1.5 3.7,0 1.6,-1.8 0,-1.5 -1,0.4 0.6,-2.4 0,1.1 0.6,0.6 0.4,-0.3 0.6,2.5 0.9,0.9 2.4,0 1.4,0.7 -4.2,-0.2 -0.5,0.2 1.5,0.8 3.6,0.2 0.6,-2.5 0.6,-0.5 1.1,0 0.6,-1.8 0.6,0.3 4.3,-0.9 0.8,0.9 0.6,-0.9 1.1,-0.2 -1.1,1.1 0.2,0.9 1.5,0 1,-0.9 -0.6,-0.6 1.6,0.3 -2,-2.1 0.6,-0.6 -1.1,0 0,-1.5 0.5,1 1,0 0.6,2 0.6,0.9 1,-0.3 2.6,0.9 0.5,1.1 -1.7,-0.6 0.6,1 1.1,0.5 0.6,0.4 -0.4,-0.5 0.6,0.1 0.4,0.8 -1,-0.4 -0.2,0.9 -0.9,0 -1.2,1 0,0.5 1.2,1.2 0.9,0 -0.5,1.3 1.1,0.5 1,0.6 0.2,-0.6 0.8,0.9 -0.8,0.2 1.7,0.4 1.6,-0.6 0,-1.4 -0.7,-1.9 0.4,-1.5 -1,0.4 0.4,-0.8 -1.4,-0.6 0.7,-0.9 0.7,0.4 0.9,-0.5 -0.9,-0.4 -0.7,-3 3.4,2.4 0.8,-1.8 -0.6,-1.1 -1,-0.8 0,-2.2 3.2,1.7 1.9,2.2 1.2,0.2 -0.4,0.6 0.8,1.4 3.9,-1.1 -0.6,-0.3 1.2,-1.2 -1,-0.3 0.8,-0.2 -0.4,-1 -2.1,0 0.4,-2.8 -0.5,-1.1 -4.1,-1.3 0,1.5 -0.6,0.8 -0.6,-0.4 0.6,-2.1 -3.9,1.5 1,-1.3 -3.1,-2 -1,0.4 -2,2 -0.6,0 1,-1.8 2.6,-1 -2.2,-2.6 -1.4,-0.5 -1.3,0.1 -0.4,0.8 -1.2,-1.9 -1.4,0 -3.3,2.1 0,-0.6 1.7,-0.5 2,-1.9 -0.6,-0.9 -2.6,0 0.1,-0.6 -4.2,-3 -2.7,-2.4 -1.6,-4.2 0.8,-0.6 1.6,-0.4 -0.4,-3.9 -0.6,-0.8 3.3,0.3 2.8,-1.5 0.9,-1.5 0,-0.9 2.2,-1 1.2,-1.1 4.3,-0.9 -1.7,-5 0.4,-1.7 -0.6,-0.6 1.9,-0.9 0.4,-1.1 0.6,-0.6 1,0.8 0.4,-0.6 1.2,2.8 -0.2,5.4 6,2.6 1,2.4 -0.4,2.8 -1.6,2 3.2,3.4 -0.4,2.8 1.6,1.6 -0.2,1.9 1.6,0.9 3.7,-1.8 1.8,0.5 2.1,-2.6 -0.2,-0.9 3.3,-0.9 -1.4,-3.9 -1.1,-1 1.1,0 0,-0.5 -1.7,-0.4 0.4,-0.5 2.3,0.5 0.6,-2 3,1.5 3.3,4.3 0.4,-0.4 2.2,-9 -1,-1.4 1.6,-1.9 0,-1.3 2.1,0 2.3,-1.7 -3.8,-1.3 -1.6,-0.9 0,-0.7 4.3,1.6 1.1,-0.5 -0.3,-1 1.2,0.6 1.1,-0.7 -0.4,-1.9 -1,-1.5 -3.5,-1.2 -3.5,-2.1 -8.6,0.5 -2.2,-0.1 -5.5,-3.8 -3.1,-2.6 4,2.6 4.4,1.9 1.9,-0.4 1.6,-0.9 -0.6,-0.8 -1.7,-0.9 -1.6,0.2 1.2,-0.8 1.1,0 -1.1,-1.2 1.6,-2.1 4.7,-2.2 -5.4,-1.2 -0.5,-0.5 -2.2,-1.5 -0.9,0.6 0.4,1.1 1.5,1.3 2.9,0.9 -1.7,0 0.7,0.9 -5,-1.3 -1,1.5 1,2.8 -0.6,0.6 -1.4,-0.4 -0.2,0.7 -4.1,-4.6 -1,-0.2 -5.5,0 -2.7,-1.3 -2,-0.2 0.8,-0.7 -1.4,0.3 -2.3,-0.3 -1,-0.8 0.6,-0.3 0.4,-0.6 -2.6,-0.7 -1.6,-0.2 -3.3,-0.6 0.6,0.8 1.1,1.3 1.6,0.9 1.2,2.4 -0.2,1.1 0.2,0 0.4,-1.1 1.6,-0.4 0.4,1 -0.8,1.8 -1.8,0.6 -3.5,1.1 -0.8,1 0.2,2.7 -1,-0.4 -1.2,2.1 -2.7,0.8 -2,-1.9 -1.9,0.4 -2.4,0.1 -0.6,0.6 -1.2,1.5 0,-0.7 -2.5,0.7 1.5,0 0,1.7 -1.1,0.5 -1.4,-1.5 -45.1,4.1 -1.2,1.5 -0.8,2.4 -3.3,2.2 -0.2,1 0.7,5.9 1,1.1 -2.1,-1.1 -0.6,3 1.7,1.9 1,2.4 -1,1.4 -1.1,1.7 -0.6,7.1 0,2 -0.8,0.5 -1.3,-0.3 -2.2,-1.5 -1,4.3 0.2,0.6 -2.4,0.4 5.4,9.8 6.6,10.2 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1660"
         inkscape:connector-curvature="0"
         d="m 163.57474,539.9 -1.4,0 -1.2,0.6 -2.3,2.1 -1.8,0.9 -0.8,1.5 0.2,0.3 0.4,0.6 -1.5,0.9 -0.7,1.5 -0.9,0.6 -0.2,-0.6 -1.4,0.4 -0.6,0 -1.4,-0.4 -1.7,0.9 0.5,1.5 1.2,1.1 0.7,-0.1 2.3,-0.6 1.6,0.6 1.1,-0.4 0.4,0.9 -0.9,1.2 1.1,0.3 1.6,0.9 1.4,1.1 0,1.3 0,1.5 -1,1.1 -0.4,1 -2.2,-0.2 0.4,0.7 1.2,0 -1.2,1.7 0,1.7 -1,1.3 -1.5,0 0.9,-0.8 -0.5,-1.1 -1,0 -2.3,1.1 0.3,-1.1 -1,0.6 -1.8,0.5 -2.5,0.8 -1.7,1.1 -1.8,0.4 -0.8,2 2.4,-0.4 0,1 -2.4,0.7 1,2 1,0.2 -0.2,0.4 1.9,1.5 1.6,0.9 2.4,1.3 3.9,-0.3 -1.2,-0.6 -1.8,-0.9 -1.8,-1.3 0,-1.2 -1.1,-1.5 0.6,-1.4 -2.2,-1.3 2,-0.2 1.6,1.7 -0.8,0.4 0.8,1.4 0.9,0.4 0.4,1.1 1.4,0.9 1.8,1 3.7,0.1 -0.4,-1.1 0.4,-0.5 -0.6,-1.5 -1.6,-2.2 1.6,0.9 0.6,-0.2 1.6,-0.7 -0.2,0.9 1.2,0.6 1.2,-0.3 2.1,-0.5 0.4,-0.9 -1.6,-0.4 1.2,-0.5 -1.2,-0.6 1.8,-0.4 0.4,-1.5 1.3,1.1 1,-0.5 0.4,-0.9 0,-1 1.2,-0.7 1,-0.9 -1,-0.9 1.4,0 0.6,-1.4 -2.2,-0.1 2.7,-1.4 -1.5,0 -1.6,-0.5 1,-1.1 1,-1.5 2,-0.8 0.7,-2.3 1.6,0 1,-0.6 -0.4,-1 -2,-1.5 -1.7,-0.5 -1,-1.8 -1.2,1.2 -1,2.1 -0.6,1.5 -1.4,0 0.4,-1.1 -1.7,-0.4 0.6,-1.9 1.1,-1.4 1.3,-1.5 0.3,-2.5 -1,-1.6 -0.6,-0.8 -1.1,0.8 -1.6,1.4 -2.2,1.7 1.2,-2.5 1.4,-1.9 0,-1.2 z" /><g
         id="g1662"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1664"
           inkscape:connector-curvature="0"
           d="m 165.2,514.3 -1.4,0 -1.2,0.6 -2.3,2.1 -1.8,0.9 -0.8,1.5 0.2,0.3 0.4,0.6 -1.5,0.9 -0.7,1.5 -0.9,0.6 -0.2,-0.6 -1.4,0.4 -0.6,0 -1.4,-0.4 -1.7,0.9 0.5,1.5 1.2,1.1 0.7,-0.1 2.3,-0.6 1.6,0.6 1.1,-0.4 0.4,0.9 -0.9,1.2 1.1,0.3 1.6,0.9 1.4,1.1 0,1.3 0,1.5 -1,1.1 -0.4,1 -2.2,-0.2 0.4,0.7 1.2,0 -1.2,1.7 0,1.7 -1,1.3 -1.5,0 0.9,-0.8 -0.5,-1.1 -1,0 -2.3,1.1 0.3,-1.1 -1,0.6 -1.8,0.5 -2.5,0.8 -1.7,1.1 -1.8,0.4 -0.8,2 2.4,-0.4 0,1 -2.4,0.7 1,2 1,0.2 -0.2,0.4 1.9,1.5 1.6,0.9 2.4,1.3 3.9,-0.3 -1.2,-0.6 -1.8,-0.9 -1.8,-1.3 0,-1.2 -1.1,-1.5 0.6,-1.4 -2.2,-1.3 2,-0.2 1.6,1.7 -0.8,0.4 0.8,1.4 0.9,0.4 0.4,1.1 1.4,0.9 1.8,1 3.7,0.1 -0.4,-1.1 0.4,-0.5 -0.6,-1.5 -1.6,-2.2 1.6,0.9 0.6,-0.2 1.6,-0.7 -0.2,0.9 1.2,0.6 1.2,-0.3 2.1,-0.5 0.4,-0.9 -1.6,-0.4 1.2,-0.5 -1.2,-0.6 1.8,-0.4 0.4,-1.5 1.3,1.1 1,-0.5 0.4,-0.9 0,-1 1.2,-0.7 1,-0.9 -1,-0.9 1.4,0 0.6,-1.4 -2.2,-0.1 2.7,-1.4 -1.5,0 -1.6,-0.5 1,-1.1 1,-1.5 2,-0.8 0.7,-2.3 1.6,0 1,-0.6 -0.4,-1 -2,-1.5 -1.7,-0.5 -1,-1.8 -1.2,1.2 -1,2.1 -0.6,1.5 -1.4,0 0.4,-1.1 -1.7,-0.4 0.6,-1.9 1.1,-1.4 1.3,-1.5 0.3,-2.5 -1,-1.6 -0.6,-0.8 -1.1,0.8 -1.6,1.4 -2.2,1.7 1.2,-2.5 1.4,-1.9 0,-1.2 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1666"
         inkscape:connector-curvature="0"
         d="m 165.37474,582.5 -2.2,0.4 0.5,0.5 -0.1,0.6 -1,0.9 -1.1,-1.1 -3.6,0.2 -2.7,0.9 -1.8,1 0.2,0.5 1,0.9 1.5,1 2.2,-1 0,1.5 0.4,1.3 1.6,0 0.2,-1.4 0.4,-1.4 1.7,0 2.6,1.5 -1,0 -1.6,-0.1 2.2,4.6 1.4,-0.6 1.3,-1.5 0.6,1 1.4,0.9 -1.6,0.2 -0.7,1.3 -0.4,2 0.7,1.9 1.6,0.1 1.4,-0.5 0.6,-2.1 0.4,-1.8 1.9,0.9 -1.3,1.3 -0.4,1.5 1.7,0 3.2,0.2 3.7,0.9 -1.6,0.4 3.2,2 -3.7,-1.1 -3.2,-1.7 -3.5,0.4 -2,1 2,1.1 4.5,0.7 -3.9,0 -3.2,-1.2 -2.1,1.4 6.9,0.6 -7.5,0.3 4.9,1.3 -3.9,0.6 0,0.9 3.5,0.2 2.5,0.6 0.5,-0.6 2.7,-0.6 -1.4,1.5 2,1 1.7,-1 -0.5,-1.5 2.1,-0.9 -0.2,2.1 2.2,1.3 2.8,0.3 1.5,-2.8 0.2,3 3,-0.9 0.4,-2.1 1.1,1.9 0.8,0.2 0.4,-1.1 0.6,0.9 1.5,0 0,-1.3 2.8,-0.6 -3.2,-1.1 3.2,0.2 2.5,0 1.2,-2.6 -1.2,-0.9 -2.5,-0.9 -2.8,-0.4 -3.8,0 1.3,-1 3.4,0.4 -3.6,-1.5 -2.1,-0.5 -2,-1 -1.6,-0.9 -1.8,-1.4 -2.5,-0.6 -2.6,-0.6 -2.3,0.2 -2,0.4 -2.2,-1.5 2.2,0 1.4,-1.3 -1.6,-1.5 -1.1,-0.9 -3,0 -0.6,-0.9 -1.2,-0.6 -2.5,0 -1,1.5 -0.8,-1.5 2.2,-1.5 1.6,0 -1.6,-1.8 -1.4,-0.6 z" /><g
         id="g1668"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1670"
           inkscape:connector-curvature="0"
           d="m 167,556.9 -2.2,0.4 0.5,0.5 -0.1,0.6 -1,0.9 -1.1,-1.1 -3.6,0.2 -2.7,0.9 -1.8,1 0.2,0.5 1,0.9 1.5,1 2.2,-1 0,1.5 0.4,1.3 1.6,0 0.2,-1.4 0.4,-1.4 1.7,0 2.6,1.5 -1,0 -1.6,-0.1 2.2,4.6 1.4,-0.6 1.3,-1.5 0.6,1 1.4,0.9 -1.6,0.2 -0.7,1.3 -0.4,2 0.7,1.9 1.6,0.1 1.4,-0.5 0.6,-2.1 0.4,-1.8 1.9,0.9 -1.3,1.3 -0.4,1.5 1.7,0 3.2,0.2 3.7,0.9 -1.6,0.4 3.2,2 -3.7,-1.1 -3.2,-1.7 -3.5,0.4 -2,1 2,1.1 4.5,0.7 -3.9,0 -3.2,-1.2 -2.1,1.4 6.9,0.6 -7.5,0.3 4.9,1.3 -3.9,0.6 0,0.9 3.5,0.2 2.5,0.6 0.5,-0.6 2.7,-0.6 -1.4,1.5 2,1 1.7,-1 -0.5,-1.5 2.1,-0.9 -0.2,2.1 2.2,1.3 2.8,0.3 1.5,-2.8 0.2,3 3,-0.9 0.4,-2.1 1.1,1.9 0.8,0.2 0.4,-1.1 0.6,0.9 1.5,0 0,-1.3 2.8,-0.6 -3.2,-1.1 3.2,0.2 2.5,0 1.2,-2.6 -1.2,-0.9 -2.5,-0.9 -2.8,-0.4 -3.8,0 1.3,-1 3.4,0.4 -3.6,-1.5 -2.1,-0.5 -2,-1 -1.6,-0.9 -1.8,-1.4 -2.5,-0.6 -2.6,-0.6 -2.3,0.2 -2,0.4 -2.2,-1.5 2.2,0 1.4,-1.3 -1.6,-1.5 -1.1,-0.9 -3,0 -0.6,-0.9 -1.2,-0.6 -2.5,0 -1,1.5 -0.8,-1.5 2.2,-1.5 1.6,0 -1.6,-1.8 -1.4,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1672"
         inkscape:connector-curvature="0"
         d="m 119.77474,567.4 -1.7,0 -2.1,0.7 -0.9,0.8 0,1.3 -1.6,-0.9 -2.1,-0.4 -2.7,-0.2 -2.8,0.6 -2.1,0.3 0,1 0.4,0.7 -1.4,0.9 -1.7,0.6 -0.1,1.3 0.1,0.9 2.7,0 2.5,-0.4 1.6,-0.5 2.3,-0.4 -0.7,0.8 -2.4,1.5 -2.3,0 -2.6,0.5 -0.2,1.1 0.6,0.8 1.8,0 3.5,0 -4.3,0.8 0.6,0.3 -1.4,0.5 0.8,1 1.2,0.4 1.1,0.1 -0.4,0.8 0.8,0.1 1.2,0 1.6,0.6 1.7,0.4 2.7,0.4 -0.4,-1.5 -1.7,-0.9 2.1,0 0.9,0.1 1.2,-0.7 -0.2,-0.8 -1.4,-0.5 1.6,0 1.5,-0.3 0.6,0.8 0,1.4 0.6,-0.4 0.9,-1.1 -0.9,-1.5 -0.6,-1.5 0.6,-0.4 1.1,0.6 0.6,1.5 0.8,1.4 1.2,1.4 1,-0.4 0.6,-1.1 0.5,-1 -0.6,-1.3 -0.8,-1.5 -0.7,-1.1 -0.6,-0.9 0.4,-0.9 0.6,-1.2 0.3,0.2 0.8,-1.1 0.8,-0.9 -0.8,-1.3 -1.1,0.7 -1.4,0 -1,0 -0.6,-1.5 1.6,0.6 0,-0.9 -1.2,-0.4 z" /><g
         id="g1674"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1676"
           inkscape:connector-curvature="0"
           d="m 121.4,541.8 -1.7,0 -2.1,0.7 -0.9,0.8 0,1.3 -1.6,-0.9 -2.1,-0.4 -2.7,-0.2 -2.8,0.6 -2.1,0.3 0,1 0.4,0.7 -1.4,0.9 -1.7,0.6 -0.1,1.3 0.1,0.9 2.7,0 2.5,-0.4 1.6,-0.5 2.3,-0.4 -0.7,0.8 -2.4,1.5 -2.3,0 -2.6,0.5 -0.2,1.1 0.6,0.8 1.8,0 3.5,0 -4.3,0.8 0.6,0.3 -1.4,0.5 0.8,1 1.2,0.4 1.1,0.1 -0.4,0.8 0.8,0.1 1.2,0 1.6,0.6 1.7,0.4 2.7,0.4 -0.4,-1.5 -1.7,-0.9 2.1,0 0.9,0.1 1.2,-0.7 -0.2,-0.8 -1.4,-0.5 1.6,0 1.5,-0.3 0.6,0.8 0,1.4 0.6,-0.4 0.9,-1.1 -0.9,-1.5 -0.6,-1.5 0.6,-0.4 1.1,0.6 0.6,1.5 0.8,1.4 1.2,1.4 1,-0.4 0.6,-1.1 0.5,-1 -0.6,-1.3 -0.8,-1.5 -0.7,-1.1 -0.6,-0.9 0.4,-0.9 0.6,-1.2 0.3,0.2 0.8,-1.1 0.8,-0.9 -0.8,-1.3 -1.1,0.7 -1.4,0 -1,0 -0.6,-1.5 1.6,0.6 0,-0.9 -1.2,-0.4 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1678"
         inkscape:connector-curvature="0"
         d="m 158.67474,591.6 -1.9,0.2 -0.8,0.4 -1.6,0.9 0,1.7 1.6,1.3 3.5,-0.6 -0.2,1.5 -3,0.2 -1.7,0.3 2.2,1.1 -2.2,0 1.4,2.3 1.7,-0.9 1.6,0.9 -1.6,0 -0.9,0.9 1.5,0.8 2.1,-0.5 0.6,0.5 -0.6,0.7 1.2,0.6 2.3,1.5 1.4,-1.1 -0.6,-1.3 0,-1.9 0.6,-0.6 -0.4,-1.5 1,-0.9 -1.2,-2 1.2,0.4 0.7,-1.9 -1.1,0 -1.6,-0.6 -1.9,-0.7 0.7,1.5 -1.5,-0.8 -0.2,-1.3 -1,-0.2 -1.3,-0.9 z" /><g
         id="g1680"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1682"
           inkscape:connector-curvature="0"
           d="m 160.3,566 -1.9,0.2 -0.8,0.4 -1.6,0.9 0,1.7 1.6,1.3 3.5,-0.6 -0.2,1.5 -3,0.2 -1.7,0.3 2.2,1.1 -2.2,0 1.4,2.3 1.7,-0.9 1.6,0.9 -1.6,0 -0.9,0.9 1.5,0.8 2.1,-0.5 0.6,0.5 -0.6,0.7 1.2,0.6 2.3,1.5 1.4,-1.1 -0.6,-1.3 0,-1.9 0.6,-0.6 -0.4,-1.5 1,-0.9 -1.2,-2 1.2,0.4 0.7,-1.9 -1.1,0 -1.6,-0.6 -1.9,-0.7 0.7,1.5 -1.5,-0.8 -0.2,-1.3 -1,-0.2 -1.3,-0.9 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1684"
         inkscape:connector-curvature="0"
         d="m 97.374743,579.9 -1,0 0.4,1.1 0,0.9 -0.8,0.6 -0.8,0.9 1.8,1 1,1.1 1.4,0 0.3,0.3 -0.3,0.6 0.3,0 0.399997,0 2.6,1.5 -0.4,0.7 1,1.7 2.3,-0.2 0.4,-0.4 2.6,-0.4 0,-0.5 0.6,-0.6 -1,-0.5 1.5,0.2 0.1,0 -0.1,-0.9 0.9,0.7 0,0.2 1.3,-0.2 0.6,-0.4 0,-1.8 0.6,-1.1 -1.7,-0.4 -1.7,-0.2 -2.5,-0.4 -1.2,-0.4 -1.4,-0.7 -1.3,0 -0.4,0.2 -1.2,-1.1 -1,-1.3 -1.299997,0.3 -2,-0.5 z" /><g
         id="g1686"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1688"
           inkscape:connector-curvature="0"
           d="m 99,554.3 -1,0 0.4,1.1 0,0.9 -0.8,0.6 -0.8,0.9 1.8,1 1,1.1 1.4,0 0.3,0.3 -0.3,0.6 0.3,0 0.4,0 2.6,1.5 -0.4,0.7 1,1.7 2.3,-0.2 0.4,-0.4 2.6,-0.4 0,-0.5 0.6,-0.6 -1,-0.5 1.5,0.2 0.1,0 -0.1,-0.9 0.9,0.7 0,0.2 1.3,-0.2 0.6,-0.4 0,-1.8 0.6,-1.1 -1.7,-0.4 -1.7,-0.2 -2.5,-0.4 -1.2,-0.4 -1.4,-0.7 -1.3,0 -0.4,0.2 -1.2,-1.1 -1,-1.3 -1.3,0.3 -2,-0.5 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1690"
         inkscape:connector-curvature="0"
         d="m 160.27474,578 -2.4,0 -1.9,0.6 -2.6,0.4 -3.1,0.5 -2.6,0.6 -1.7,1.3 0.6,1.1 0.9,1.4 0.2,1.4 -0.2,1 -1.9,0.5 -0.6,1.1 0.4,1.3 1.6,0 2.1,-1.3 0.8,-0.5 1.9,-1.1 0.6,-1.3 -1,0 -0.4,-0.6 0,-0.5 0.8,-1.4 1.6,-0.2 1.6,-0.7 2.7,0 1.8,0.3 2.5,-0.5 1.2,-1 0,-0.5 -1.2,-0.7 -0.9,-0.8 -0.8,-0.4 z" /><g
         id="g1692"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1694"
           inkscape:connector-curvature="0"
           d="m 161.9,552.4 -2.4,0 -1.9,0.6 -2.6,0.4 -3.1,0.5 -2.6,0.6 -1.7,1.3 0.6,1.1 0.9,1.4 0.2,1.4 -0.2,1 -1.9,0.5 -0.6,1.1 0.4,1.3 1.6,0 2.1,-1.3 0.8,-0.5 1.9,-1.1 0.6,-1.3 -1,0 -0.4,-0.6 0,-0.5 0.8,-1.4 1.6,-0.2 1.6,-0.7 2.7,0 1.8,0.3 2.5,-0.5 1.2,-1 0,-0.5 -1.2,-0.7 -0.9,-0.8 -0.8,-0.4 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1696"
         inkscape:connector-curvature="0"
         d="m 128.57474,585.8 -3.3,0.6 0.4,0.5 -7.4,-0.8 -0.8,1.2 1.9,0.4 2.6,0 1.5,0.9 -3.7,0.2 1.2,0.9 -0.2,0.4 -2.6,-1.3 -1.6,0.3 -1.1,1.6 2.2,0.9 1.5,1.1 1.8,1 2.1,0.3 0,-1.5 1,0 0.6,-0.8 -0.4,-1 -0.2,-1.4 1,-0.2 1.2,-0.5 1,0.7 0.6,1.4 1.1,1.5 2,0.9 -0.6,-1.5 -0.8,-1.9 0.4,-0.9 1.2,0.5 0.4,-0.7 -1.2,-1.9 -1.8,-0.9 z" /><g
         id="g1698"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1700"
           inkscape:connector-curvature="0"
           d="m 130.2,560.2 -3.3,0.6 0.4,0.5 -7.4,-0.8 -0.8,1.2 1.9,0.4 2.6,0 1.5,0.9 -3.7,0.2 1.2,0.9 -0.2,0.4 -2.6,-1.3 -1.6,0.3 -1.1,1.6 2.2,0.9 1.5,1.1 1.8,1 2.1,0.3 0,-1.5 1,0 0.6,-0.8 -0.4,-1 -0.2,-1.4 1,-0.2 1.2,-0.5 1,0.7 0.6,1.4 1.1,1.5 2,0.9 -0.6,-1.5 -0.8,-1.9 0.4,-0.9 1.2,0.5 0.4,-0.7 -1.2,-1.9 -1.8,-0.9 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1702"
         inkscape:connector-curvature="0"
         d="m 174.87474,502.3 -1.4,0.6 0.6,1.5 -0.2,0.4 -1.1,-1 -2.2,-0.5 1.6,1.1 -1.6,0 -5.1,0.4 0,0.9 1.4,0.8 -1,0.3 2,1.2 0,0.7 1.7,1.7 1.2,2.2 2,0.6 1.1,0.4 -1.1,-1.7 -2.7,-2.6 1.5,0.5 0.6,-0.1 -0.6,-1 1.9,0 2,-0.7 -0.6,-0.8 0,-1 1,0.3 0.2,-0.8 0,-0.9 -0.2,-1 -1,-1.5 z" /><g
         id="g1704"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1706"
           inkscape:connector-curvature="0"
           d="m 176.5,476.7 -1.4,0.6 0.6,1.5 -0.2,0.4 -1.1,-1 -2.2,-0.5 1.6,1.1 -1.6,0 -5.1,0.4 0,0.9 1.4,0.8 -1,0.3 2,1.2 0,0.7 1.7,1.7 1.2,2.2 2,0.6 1.1,0.4 -1.1,-1.7 -2.7,-2.6 1.5,0.5 0.6,-0.1 -0.6,-1 1.9,0 2,-0.7 -0.6,-0.8 0,-1 1,0.3 0.2,-0.8 0,-0.9 -0.2,-1 -1,-1.5 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1708"
         inkscape:connector-curvature="0"
         d="m 129.57474,572.2 -0.7,0.4 0.4,1.1 -0.4,1.3 -0.4,1 -0.8,0.9 0,1.5 1.2,0.6 0,-1.5 0.7,-0.6 1,0.8 0.6,0.9 1,0 0,0.6 -1,0.7 1,0.6 1.1,0 1,-1 2,0.4 0.8,-0.7 -0.6,-0.2 -1,0 0.4,-0.4 -0.6,0 -1.6,-0.5 0,-0.6 0.6,0 0.6,0 0.4,-1.9 -1.4,-1.5 -1.3,-0.9 -1.4,0.4 0.4,0.5 -1,-0.5 0,-0.8 -1,-0.6 z" /><g
         id="g1710"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1712"
           inkscape:connector-curvature="0"
           d="m 131.2,546.6 -0.7,0.4 0.4,1.1 -0.4,1.3 -0.4,1 -0.8,0.9 0,1.5 1.2,0.6 0,-1.5 0.7,-0.6 1,0.8 0.6,0.9 1,0 0,0.6 -1,0.7 1,0.6 1.1,0 1,-1 2,0.4 0.8,-0.7 -0.6,-0.2 -1,0 0.4,-0.4 -0.6,0 -1.6,-0.5 0,-0.6 0.6,0 0.6,0 0.4,-1.9 -1.4,-1.5 -1.3,-0.9 -1.4,0.4 0.4,0.5 -1,-0.5 0,-0.8 -1,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1714"
         inkscape:connector-curvature="0"
         d="m 142.77474,547.1 -2.2,0.8 0,1.5 -1.1,-0.2 -1.6,-1.3 -1.4,-0.8 -1.3,0 -0.4,0.6 -1.6,0 0.4,0.6 1.6,0.9 1.3,3 1.4,1.5 1.2,1.3 0,-1.9 1.1,0.2 1.9,-2.2 0.3,-1.9 1.4,-0.6 0.2,-0.9 -1.2,-0.6 z" /><g
         id="g1716"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1718"
           inkscape:connector-curvature="0"
           d="m 144.4,521.5 -2.2,0.8 0,1.5 -1.1,-0.2 -1.6,-1.3 -1.4,-0.8 -1.3,0 -0.4,0.6 -1.6,0 0.4,0.6 1.6,0.9 1.3,3 1.4,1.5 1.2,1.3 0,-1.9 1.1,0.2 1.9,-2.2 0.3,-1.9 1.4,-0.6 0.2,-0.9 -1.2,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1720"
         inkscape:connector-curvature="0"
         d="m 138.87474,583.8 -2.7,0.2 1.9,2.2 -3.2,0.2 -0.3,0.9 0.3,1.8 0.6,1.7 0.7,-0.5 0.3,-1.2 0.6,-2.2 1,2.2 0.8,-2.2 0.2,2.2 1.4,0 1.9,-0.5 -0.2,-1.9 -1.5,-1.8 -1.8,-1.1 z" /><g
         id="g1722"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1724"
           inkscape:connector-curvature="0"
           d="m 140.5,558.2 -2.7,0.2 1.9,2.2 -3.2,0.2 -0.3,0.9 0.3,1.8 0.6,1.7 0.7,-0.5 0.3,-1.2 0.6,-2.2 1,2.2 0.8,-2.2 0.2,2.2 1.4,0 1.9,-0.5 -0.2,-1.9 -1.5,-1.8 -1.8,-1.1 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1726"
         inkscape:connector-curvature="0"
         d="m 114.07474,593.3 1,1.8 -1.7,-0.9 -1.2,0.4 0.3,1.1 2.4,0.4 1.6,0.5 2.7,1 2.2,0.5 2.1,-0.5 0,0.5 0.8,-0.1 0.8,-1 -1.6,-0.5 -0.5,-1 -0.6,0 0,-0.7 -2,-0.2 -0.6,-0.6 -1,0.6 1.6,1.9 -2.2,-1 -0.5,-0.7 -1,-0.6 0.4,0.6 -1.6,-0.8 -1.4,-0.7 z" /><g
         id="g1728"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1730"
           inkscape:connector-curvature="0"
           d="m 115.7,567.7 1,1.8 -1.7,-0.9 -1.2,0.4 0.3,1.1 2.4,0.4 1.6,0.5 2.7,1 2.2,0.5 2.1,-0.5 0,0.5 0.8,-0.1 0.8,-1 -1.6,-0.5 -0.5,-1 -0.6,0 0,-0.7 -2,-0.2 -0.6,-0.6 -1,0.6 1.6,1.9 -2.2,-1 -0.5,-0.7 -1,-0.6 0.4,0.6 -1.6,-0.8 -1.4,-0.7 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1732"
         inkscape:connector-curvature="0"
         d="m 136.97474,573.5 -1.2,0.6 0.6,1.5 1.4,2 1.9,1.5 3.1,0 3.3,-1.5 -2.7,-1.1 -2.2,-1.9 -3.4,1.6 -0.4,-1 1.7,-0.2 -2.1,-1.5 z" /><g
         id="g1734"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1736"
           inkscape:connector-curvature="0"
           d="m 138.6,547.9 -1.2,0.6 0.6,1.5 1.4,2 1.9,1.5 3.1,0 3.3,-1.5 -2.7,-1.1 -2.2,-1.9 -3.4,1.6 -0.4,-1 1.7,-0.2 -2.1,-1.5 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1738"
         inkscape:connector-curvature="0"
         d="m 60.874743,515.6 -2,0.5 -1,1.4 -1.3,0.7 -1.8,2.2 0,1.7 0.8,0.4 3.3,-1.8 0.2,-1.7 0.7,-1.4 1.1,-2 z" /><g
         id="g1740"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1742"
           inkscape:connector-curvature="0"
           d="m 62.5,490 -2,0.5 -1,1.4 -1.3,0.7 -1.8,2.2 0,1.7 0.8,0.4 3.3,-1.8 0.2,-1.7 0.7,-1.4 1.1,-2 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1744"
         inkscape:connector-curvature="0"
         d="m 130.57474,595.5 -2.3,0 0,1 0.3,0.7 1.3,1.3 2.2,0 2.2,-0.9 -0.6,-0.6 -1.5,0.2 0,-0.6 0.5,-0.1 -0.5,-0.8 -1.6,-0.2 z" /><g
         id="g1746"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1748"
           inkscape:connector-curvature="0"
           d="m 132.2,569.9 -2.3,0 0,1 0.3,0.7 1.3,1.3 2.2,0 2.2,-0.9 -0.6,-0.6 -1.5,0.2 0,-0.6 0.5,-0.1 -0.5,-0.8 -1.6,-0.2 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1750"
         inkscape:connector-curvature="0"
         d="m 128.47474,564.8 -1.6,0 -1.2,0.6 -1.6,1.3 1.1,0.2 1.1,0.9 1.6,0.9 1,-1.8 0.4,-1.5 -0.8,-0.6 z" /><g
         id="g1752"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1754"
           inkscape:connector-curvature="0"
           d="m 130.1,539.2 -1.6,0 -1.2,0.6 -1.6,1.3 1.1,0.2 1.1,0.9 1.6,0.9 1,-1.8 0.4,-1.5 -0.8,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1756"
         inkscape:connector-curvature="0"
         d="m 143.37474,581.4 -2.1,0.5 -0.8,1.1 1.9,0.9 2.4,0.8 0.3,-0.8 0,-0.9 -1.7,-1.6 z" /><g
         id="g1758"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1760"
           inkscape:connector-curvature="0"
           d="m 145,555.8 -2.1,0.5 -0.8,1.1 1.9,0.9 2.4,0.8 0.3,-0.8 0,-0.9 -1.7,-1.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1762"
         inkscape:connector-curvature="0"
         d="m 154.37474,556.6 -1.8,0.4 0.4,1 1,0.9 1.7,0.2 1,-0.5 -0.6,-1.6 -1.7,-0.4 z" /><g
         id="g1764"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1766"
           inkscape:connector-curvature="0"
           d="m 156,531 -1.8,0.4 0.4,1 1,0.9 1.7,0.2 1,-0.5 -0.6,-1.6 -1.7,-0.4 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1768"
         inkscape:connector-curvature="0"
         d="m 124.67474,579.1 -0.4,1.9 -0.7,0.6 1.5,0.7 1.2,-0.4 0.7,-0.9 -0.3,-1 -1,-0.6 -1,-0.3 z" /><g
         id="g1770"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1772"
           inkscape:connector-curvature="0"
           d="m 126.3,553.5 -0.4,1.9 -0.7,0.6 1.5,0.7 1.2,-0.4 0.7,-0.9 -0.3,-1 -1,-0.6 -1,-0.3 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1774"
         inkscape:connector-curvature="0"
         d="m 134.87474,598.5 -2.1,0.6 -1.6,0.5 2.1,0.7 3,0 0,-1.2 -1.4,-0.6 z" /><g
         id="g1776"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1778"
           inkscape:connector-curvature="0"
           d="m 136.5,572.9 -2.1,0.6 -1.6,0.5 2.1,0.7 3,0 0,-1.2 -1.4,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1780"
         inkscape:connector-curvature="0"
         d="m 138.07474,543.8 -1,0.2 0,0.7 1.4,1.2 2.3,-0.6 -0.6,-0.6 -2.1,-0.9 z" /><g
         id="g1782"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1784"
           inkscape:connector-curvature="0"
           d="m 139.7,518.2 -1,0.2 0,0.7 1.4,1.2 2.3,-0.6 -0.6,-0.6 -2.1,-0.9 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1786"
         inkscape:connector-curvature="0"
         d="m 162.17474,508.3 -1.7,0 -1.2,1 -1,1.1 0.6,0 2,-0.7 1.1,-0.8 0.2,-0.6 z" /><g
         id="g1788"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1790"
           inkscape:connector-curvature="0"
           d="m 163.8,482.7 -1.7,0 -1.2,1 -1,1.1 0.6,0 2,-0.7 1.1,-0.8 0.2,-0.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1792"
         inkscape:connector-curvature="0"
         d="m 142.37474,541.3 -0.6,0.6 1,1.6 1,0.3 0,-0.9 -1.4,-1.6 z" /><g
         id="g1794"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1796"
           inkscape:connector-curvature="0"
           d="m 144,515.7 -0.6,0.6 1,1.6 1,0.3 0,-0.9 -1.4,-1.6 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1798"
         inkscape:connector-curvature="0"
         d="m 146.57474,545.3 -0.2,1.2 1.1,-0.3 0,-0.7 -0.9,-0.2 z" /><g
         id="g1800"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1802"
           inkscape:connector-curvature="0"
           d="m 148.2,519.7 -0.2,1.2 1.1,-0.3 0,-0.7 -0.9,-0.2 z" /></g><path
         style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
         id="path1804"
         inkscape:connector-curvature="0"
         d="m 148.37474,545.9 -0.2,0.9 0.6,0 0.4,-0.5 -0.8,-0.4 z" /><g
         id="g1806"
         transform="translate(-1.6252567,25.599982)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
           id="path1808"
           inkscape:connector-curvature="0"
           d="m 150,520.3 -0.2,0.9 0.6,0 0.4,-0.5 -0.8,-0.4 z" /></g><g
         id="g32"
         transform="translate(-1.6252567,20.799985)"><path
           style="fill:none;stroke:#b6dde8;stroke-width:1.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:7.66129017;stroke-opacity:1;stroke-dasharray:none"
           id="path34"
           inkscape:connector-curvature="0"
           d="m 1.2,594 0,0 z" /></g><rect
         style="opacity:0.02430558000000000;fill:#000000;fill-opacity:1;stroke:#cf212a;stroke-width:0.93448904;stroke-linejoin:round;stroke-miterlimit:7.66129017;stroke-opacity:1;stroke-dasharray:1.86897807,3.73795615;stroke-dashoffset:0"
         id="svg_recteurope"
         width="67.804062"
         height="44.750328"
         x="243.03886"
         y="-526.32098"
         transform="scale(1,-1)"
         onmouseover="mouseoverrecteurope(this)"
         onclick="mouseclickrecteurope(this)"
         ry="4.7762103"
         rx="5.0715723"
         onmouseout="mouseoutrecteurope(this)" /></g></g></svg>
  
</div>
<div id="div_europe" style="display:none; width:300px; height:207px; opacity:100;">
  <svg
   xmlns:osb="http://www.openswatchbook.org/uri/2009/osb"
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   version="1.1"
   width="300.42123"
   height="207"
   id="svg_europe"
   xml:space="preserve"
   inkscape:version="0.48.5 r"
   sodipodi:docname="europezoom.svg"><sodipodi:namedview
     pagecolor="#3df2ed"
     bordercolor="#666666"
     borderopacity="1"
     objecttolerance="10"
     gridtolerance="10"
     guidetolerance="10"
     inkscape:pageopacity="0.01568627"
     inkscape:pageshadow="2"
     inkscape:window-width="1004"
     inkscape:window-height="687"
     id="namedview7141"
     showgrid="false"
     inkscape:zoom="2"
     inkscape:cx="180.21019"
     inkscape:cy="100.74951"
     inkscape:window-x="-37"
     inkscape:window-y="-22"
     inkscape:window-maximized="0"
     inkscape:current-layer="svg_payseurope"
     inkscape:snap-smooth-nodes="true"
     fit-margin-top="0"
     fit-margin-left="0"
     fit-margin-right="0"
     fit-margin-bottom="0"
     showborder="true"
     inkscape:showpageshadow="false" /><metadata
     id="metadata8"><rdf:rdf><cc:work
         rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" /><dc:title /></cc:work></rdf:rdf></metadata><defs
     id="defs6"><linearGradient
       id="linearGradient11203"><stop
         style="stop-color:#4fe2f2;stop-opacity:0.37323943;"
         offset="0"
         id="stop11205" /><stop
         id="stop11215"
         offset="0.5"
         style="stop-color:#a69ea8;stop-opacity:0.49803922;" /><stop
         style="stop-color:#a69ea8;stop-opacity:0;"
         offset="1"
         id="stop11207" /></linearGradient><linearGradient
       id="linearGradient11181"><stop
         style="stop-color:#c4fdfd;stop-opacity:0.57042253;"
         offset="0"
         id="stop11183" /><stop
         id="stop11191"
         offset="0.5"
         style="stop-color:#127e7e;stop-opacity:0.28235295;" /><stop
         style="stop-color:#000000;stop-opacity:0;"
         offset="1"
         id="stop11185" /></linearGradient><linearGradient
       id="linearGradient11171"
       osb:paint="solid"><stop
         style="stop-color:#ff0000;stop-opacity:1;"
         offset="0"
         id="stop11173" /></linearGradient><clippath
       id="clipPath16"><path
         d="m 0,-0.2 841.8,0 0,595.2 L 0,595 0,-0.2 z"
         inkscape:connector-curvature="0"
         id="path18" /></clippath><mask
       id="mask22"><image
         xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAACS4AAAT+CAAAAADv7nJSAAAAAXNCSVQI5gpbmQAAHB9JREFUeJzt3V1yG0liRtEEjZ6l2A7vfy0Oe3opHnv8IKklUuTFXwGVVXXOW4stIR9vfFkonk6Dj/5z7QMAwGr+Y+0DTOh03FwSRQBwi+OG1GFySRwBwCIOWE37zyWdBABPtveC2m0uqSQAeKU9J9P+ckknAcBq9hlNe8oloQQAU9hbNO0ll6QSAMxnJ920/VwSSgAwsT0U07ZzSSoBwAZsPZm2m0tSCQA2ZMvJtM1ckkoAsEFbTabt5ZJUAoAN22IybS2XxBIA7MC2omlDuaSUAGA/thRMW8klrQQAu7OVZNpCLkklANirTRTT9LmklQBg3+YvprlzSSsBwCHMnUzz5pJUAoAjmbiYZs0lsQQABzRnM82YS1IJAA5rxmCaLpe0EgAc3HTFNFcuaSUAYLpgmimXxBIA8N1MxTRNLmklAOCdaYppjlzSSgDAJ+YopglySSsBAF+ZIZhWzyWxBAC0tZNp3VzSSgDAFdYNpjVzSSwBAFdaM5hWy6X/WulzAYCN+ve1PnilXBJLAMDNVgqmVXJJLAEAd1klmF6fS1oJAHjA64vp1bkklgCAB706mF6bS2IJAFjCS4vplbkklgCAxbyumF6WS1oJAFjUy3rpRbkklgCAxb0omF6SS2IJAHiKlwTTC3JJLAEAT/OCYHp6LoklAOCpnh5MT84lsQQAPN2Tg+mpuSSWAICXeGowPTGXxBIA8DJPDKan5ZJYAgBe6mnB9KRcEksAwOs9p5iekktiCQBYxVN66Qm5JJYAgNU8IZiWzyW1BACsafFgWjqXxBIAsLaFg2nZXBJLAMAElu2lRXNJLQEAc1gymBbMJbEEAMxjuWB6W+xfUksAwESWS5Ol1iWxBADMZqGBaZlcEksAwIwWCaZFckktAQCTWiCYFsglsQQAzOvxXno8l9QSADC1R4Pp0VwSSwDA7B7spQdzSS0BABvwUDA9lEv//cgnAwC8zL898HcfyCWxBABsx/3BdH8uqSUAYFPuDaZ7c0ksAQBbc2cv3ZlLagkA2KC7gumuXBJLAMA23dNL9+SSWgIANuv2YHq7/UPUEgCwXbeXzO25pJYAgC27uWVuvYwTSwDA1t14H3djLqklAGAHbgqm2y7j1BIAsAc3Nc1NuaSWAIB9uKVqbskltQQA7MUNXXNDLqklAGA/ri+b63NJLQEAe3J121z7zTixBADszZXfj7syl9QSALBDVwXTdZdxagkA2KOrGueqXFJLAMA+XVM51+SSWgIA9uqKzrkil9QSALBfl0vnci6pJQBgzy62zsVcUksAwL5dqp1LuaSWAIC9u9A7F3JJLQEA+9fFc8uv2AUAOKDOJeMSAHAE2TyZS2oJADiGqp7KJbUEABxFdE/kkloCAI7j6/L5OpfUEgBwJF+2z+n0xQ/+/qSTAABM6l8//+Ov1iW1BAAczRf980UuqSUA4Hg+LyCvqQQASJ/nknEJADiiTxvo01xSSwDAMX1WQZ/lkloCAI7qkw7y7BIAQPokl4xLAMBx/V5Cv+eSWgIAjuy3Fvotl9QSAHBsH2vIs0sAAOljLhmXAICj+9BDb/lTAIADel9Eb/EzAIBjetdEnl0CAEjvcsm4BAAwxvsqsi4BAKRfc8m4BADwzS9dZF0CAEi/5JJxCQDgh59l9PbJnwEA8FcbuYwDAEh/5ZJxCQDgVz/qyLoEAJB+5JJxCQDgve99ZF0CAEjfc8m4BADw0bdCsi4BAKRvuWRcAgD43d/HsC4BAFwglwAA0nmMMcZp5VMAAEzrbYwx/lz7FAAAU/pzuIwDALhALgEApLfhLg4A4Ct/WpcAAC6QSwAASS4BAKQ3jy4BAHztT+sSAECTSwAA6c1dHABAsS4BACS5BABQ/imXAADSm0eXAACKdQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACCdT2ufAABgatYlAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASOfT2icAAJiadQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS+bT2CQAApmZdAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgHQ+rX0CAICpWZcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgnU9rnwAAYGrWJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACCdT2ufAABgatYlAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASOfT2icAAJiadQkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS+bT2CQAApmZdAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgHQ+rX0CAICpWZcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgnU9rnwAAYGrWJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEjn09onAACYmnUJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAA0vm09gkAAKZmXQIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIB0Pq19AgCAqVmXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIJ1Pa58AAGBq1iUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBI59PaJwAAmJp1CQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAANL5tPYJAACmZl0CAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAdD6tfQIAgKlZlwAAklwCAEhyCQAgySUAgCSXAACSXAIASHIJACDJJQCAJJcAAJJcAgBIcgkAIMklAIAklwAAklwCAEhyCQAgySUAgCSXAADS29/WPgEAwNSsSwAASS4BAJSTXAIASG/Dw0sAAMG6BACQ5BIAQHobbuMAAL70N+sSAECTSwAASS4BAKS34eElAICv/M26BABwgVwCAEin0xhj/M/axwAAmNEfw7oEAHCBXAIASHIJACB9y6U/Vj4FAMCM/hjDugQAcMH3XDIvAQB89K2QrEsAAOlHLpmXAADe+95Hbx/+GwCAMcbPOnIZBwCQfuaSeQkA4Ke/2ujtkz8DADi8n2XkMg4AIP2aS+YlAIBvfuki6xIAQHqXS+YlAIAx3leRdQkAIL3PJfMSAMCHJnqLnwEAHNL7InIZBwCQPuaSeQkAOLoPPfTbuqSXAIBj+1hDv1/G6SUA4Mh+ayHPLgEApE9yybwEABzX7yX02bqklwCAo/qkg1zGAQCkT3PJvAQAHNNnFfT5uqSXAIAj+rSBvriM00sAwPF8XkCeXQIASKfTFz/4x0uPAQCwuvPnf/zluvTF/w8AsFNf1c/Xl3F6CQA4ki/bJ55d0ksAwHF8XT71qLdeAgCOIronvxmnlwCAY6jq6RcJ6CUA4Aiyebx3CQAgXcgl8xIAsH9dPJfWJb0EAOzdhd65eBmnlwCAfbtUO5efXdJLAMCeXWydKx711ksAwH5dLp1rvhmnlwCAvbqic656kYBeAgD26ZrKOZ2u+7f+8dBJAABmdNUmdG0u6SUAYG+uvEC7+q3eLuQAgH25tm6u/yUoegkA2JOr2+bqy7gxXMgBAPtx/RJ0Uy7pJQBgH265Nrv+Mu7WfxkAYFY3Nc1tuaSXAIAduK1obruMG8OFHACwdTfuP7fnkl4CALbs5suyGy/j7voMAIBp3F4yd6xLw8AEAGzVHbvPfbmklwCALbrrkuyOy7i7PwsAYFX3Fcyd69IwMAEAW3Pn3nN/LuklAGBL7r4ceyCXBBMAsB33P0r0UC6N/33kLwMAvMq/PPB3H8slwQQAbMEjtfRwLuklAGB2D8XS3S8SWOzzAQCe7NFaeXhdGgYmAGBmD287S+SSXgIAZrXARdgiuSSYAIA5LfHY0EK5JJgAgPks84z1YrmklwCAuSz1hbTlckkwAQATWe7b+0vmkmACAGax4LuOls0lvQQAzGDRF0MunEuCCQBY3cJv0X74rd4fecs3ALCupWtk8XVpGJgAgBUtP908I5cEEwCwkmfccz0nl/QSALCC5zwU9KRcGooJAHi1Jz1C/bxcEkwAwCs97ftmz8wlwQQAvMoTv5z/3FwSTADAKzz1TUbPziXBBAA825Nf+/j8XBJMAMAzPf0d2a/IJcEEADzLC36hyGtySTABAM/wkt++9qpcEkwAwNJe9KtqX5dLggkAWNCLWmm8NpeGYgIAlvG6WHp5LgkmAOBxr4ylFXJpKCYA4BGvbaWxTi4JJgDgXi+PpbVySTABAPdYIZbWy6Uxxv+t9skAwBa9rfS5K+aSYAIArrdWLK2cS4IJALjKeq00Vs8lwQQAXLJqK40JcmkMyQQAfG3tWJokl4ZiAgA+s34rjXlyaSgmAOCDKWJpqlwSTADAT5O00pgsl4ZiAgDGmKmVxny5NBQTABzdVK00psyloZgA4MBmi6VZc0kwAcAhzZdKY8ybS2MMzQQAxzJnLE2eS0MxAcBBzJpKY8yfS2NIJgDYu5lbaWwjl4ZiAoD9mryVxmZyaSgmANih+VNpjC3l0hiSCQD2ZButNLaWS4IJAHZhM6U0xtheLn2nmgBgs7bVSmOzuTTG0EwAsDmbK6UxxrZzaQzJBABbsc1SGmNsP5fGGJoJAOa24VIaY+wkl4ZiAoAZbb2TvttLLv1FNwHAFHaSSmPsMJe+U00AsJYdhdI3e82lb0QTALzS7kLpm33n0hiSCQCebqeV9Jf959KvpBMALGvvpTTGOFou/SCbAOAxh8ikH46ZS2OMMf659gEAYFOO2wz/D9qv66UeCzqxAAAAAElFTkSuQmCC"
         width="1"
         height="1"
         id="image24" /></mask><radialgradient
       inkscape:collect="always"
       xlink:href="#linearGradient11181"
       id="radialGradient11189"
       cx="284.86761"
       cy="431.18344"
       fx="284.86761"
       fy="431.18344"
       r="284.41425"
       gradientTransform="matrix(1,0,0,0.57508887,0,183.21464)"
       gradientUnits="userSpaceOnUse" /><radialgradient
       inkscape:collect="always"
       xlink:href="#linearGradient11181"
       id="radialGradient8450"
       gradientUnits="userSpaceOnUse"
       gradientTransform="matrix(1,0,0,0.57508887,0,183.21464)"
       cx="284.86761"
       cy="431.18344"
       fx="284.86761"
       fy="431.18344"
       r="284.41425" /><radialgradient
       inkscape:collect="always"
       xlink:href="#linearGradient11181"
       id="radialGradient10560"
       gradientUnits="userSpaceOnUse"
       gradientTransform="matrix(1,0,0,0.57508887,0,183.21464)"
       cx="284.86761"
       cy="431.18344"
       fx="284.86761"
       fy="431.18344"
       r="284.41425" /></defs><g
     transform="matrix(3.6696365,0,0,-3.7769891,1.6639025,2155.8084)"
     id="svg_payseurope"><g
       id="g492"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 285.6,484.1 0.6,0 0.4,0 0.7,0 0,-0.4 1,-0.4 0.4,0 0,-0.2 0,-0.4 0.6,-0.3 0.4,0 0.7,0.3 0.6,-0.3 0,-0.2 1.5,-0.4 0,-0.2 -2.8,-2.5 -1.6,0.3 -1.1,0.3 -0.8,-0.9 -0.6,0 -1,0.3 0,0.3 -0.7,0.7 -0.1,0.2 -1.1,0.6 0,0.7 -0.8,0.2 0,0.6 0,0.3 0.4,-0.3 3.1,1.3 0.2,0.4 z"
         inkscape:connector-curvature="0"
         id="path494"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><rect
       style="fill:#ffffff;fill-opacity:1;stroke:#44ede8;stroke-width:0.31015918;stroke-linejoin:round;stroke-miterlimit:7.66129017;stroke-opacity:0.85130113;stroke-dasharray:none"
       id="rect5518"
       width="81.437302"
       height="54.49984"
       x="-0.28016838"
       y="-570.62146"
       transform="scale(1,-1)"
       ry="3.8928456" /><g
       style="fill:url(#radialGradient10560);fill-opacity:1"
       id="g28" /><path
       d="m 56.153808,528.67344 0.705762,1.13102 0.823389,0.56552 1.646778,-1.01793 0,-0.67861 0,-0.33931 0,-0.67861 0.352881,-0.56552 0.470508,-1.13102 -0.823389,-1.58344 -0.941016,-0.67861 0,0.2262 -0.705762,0.79172 -1.293897,0.67862 0.470508,0 0,1.01792 0.235254,0.2262 -0.235254,0.45241 0,1.01793 -0.705762,0.56551 z"
       inkscape:connector-curvature="0"
       id="path40"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g42"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 293.9,465.6 0.6,1 0.7,0.5 1.4,-0.9 0,-0.6 0,-0.3 0,-0.6 0.3,-0.5 0.4,-1 -0.7,-1.4 -0.8,-0.6 0,0.2 -0.6,0.7 -1.1,0.6 0.4,0 0,0.9 0.2,0.2 -0.2,0.4 0,0.9 -0.6,0.5 z"
         inkscape:connector-curvature="0"
         id="path44"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 24.394515,549.4843 1.17627,0.67862 0.588135,0 0.117627,-0.45241 0.470508,0.45241 0.705762,0.2262 0.705763,0 0.941016,-0.2262 0.941016,-0.45241 -0.117627,-0.67862 1.058643,-0.33931 0.352881,-1.13102 -0.823389,-0.22621 -0.470508,-0.33931 0,-1.13102 -0.117627,0 -1.293897,0.45241 -0.470508,1.01792 -0.235255,-0.56551 -1.646778,1.24413 -0.117627,0.45241 -0.588135,0.56551 -0.352881,-0.33931 -0.823389,0.33931 0,0.45241 z"
       inkscape:connector-curvature="0"
       id="BE"
       style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g48"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 266.9,484 1,0.6 0.5,0 0.1,-0.4 0.4,0.4 0.6,0.2 0.6,0 0.8,-0.2 0.8,-0.4 -0.1,-0.6 0.9,-0.3 0.3,-1 -0.7,-0.2 -0.4,-0.3 0,-1 -0.1,0 -1.1,0.4 -0.4,0.9 -0.2,-0.5 -1.4,1.1 -0.1,0.4 -0.5,0.5 -0.3,-0.3 -0.7,0.3 0,0.4 z"
         inkscape:connector-curvature="0"
         id="path50"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 62.74092,533.76305 0.705762,-0.1131 -0.117627,-0.45241 0,-0.45241 1.17627,0.45241 0.941016,-0.45241 0.470508,0 1.17627,0 1.17627,-0.22621 0.235254,0.22621 0.941016,0.45241 1.99966,0.56551 0.235254,-0.1131 1.646778,0 0.235254,-0.45241 0.470508,0 0,-1.13103 -0.470508,0 -0.823389,-0.67861 0.588135,-1.01792 -0.705762,-0.33931 0.941016,-0.90482 -0.823389,0 -0.588135,0.2262 -1.646778,-0.67861 -0.352881,0 0,-1.01792 -1.176271,-0.22621 -0.705762,0.22621 -0.705762,0 -0.470508,0.45241 -3.058302,-0.45241 0,1.01792 -1.293897,1.13102 0.588135,0.22621 -0.588135,0.33931 0,0.45241 0.588135,0.2262 0.117627,0.33931 -0.117627,0.45241 -0.588135,0.67861 0,1.24413 z"
       inkscape:connector-curvature="0"
       id="BG"
       style="fill:#d99594;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g54"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 299.5,470.1 0.6,-0.1 -0.1,-0.4 0,-0.4 1,0.4 0.8,-0.4 0.4,0 1,0 1,-0.2 0.2,0.2 0.8,0.4 1.7,0.5 0.2,-0.1 1.4,0 0.2,-0.4 0.4,0 0,-1 -0.4,0 -0.7,-0.6 0.5,-0.9 -0.6,-0.3 0.8,-0.8 -0.7,0 -0.5,0.2 -1.4,-0.6 -0.3,0 0,-0.9 -1,-0.2 -0.6,0.2 -0.6,0 -0.4,0.4 -2.6,-0.4 0,0.9 -1.1,1 0.5,0.2 -0.5,0.3 0,0.4 0.5,0.2 0.1,0.3 -0.1,0.4 -0.5,0.6 0,1.1 z"
         inkscape:connector-curvature="0"
         id="path56"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 34.745692,559.21112 0,1.47033 -0.470508,0.45241 -0.117627,-0.45241 0,1.69654 0,1.01792 0.588135,-0.33931 0.352881,1.01792 0.941016,-0.45241 0,0.90482 1.411524,0 0.705762,-1.13102 1.058643,-0.33931 -0.470508,-0.67861 -0.823389,0 0,-0.33931 0,-0.33931 -0.470508,-0.56551 -0.705762,-0.45241 0,-1.13103 0,-0.56551 -0.705762,0 -1.293897,0.22621 z"
       inkscape:connector-curvature="0"
       id="path58"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:#aff2f7;stroke-width:0.30981702;stroke-miterlimit:4;stroke-opacity:0.08550188;stroke-dasharray:none" /><path
       d="m 41.332804,559.21112 -0.235254,0 -0.470508,0.79171 -1.293897,1.24413 0.470508,0 0.352881,0.45241 0.470508,-0.45241 0.470508,0.45241 0.235254,0.22621 0.470508,0 0.235255,-0.79172 -0.705763,-0.45241 0.470508,-0.45241 -0.470508,-0.22621 0,-0.79171 z"
       inkscape:connector-curvature="0"
       id="path64"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g66"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 281.3,492.6 -0.2,0 -0.4,0.7 -1.1,1.1 0.4,0 0.3,0.4 0.4,-0.4 0.4,0.4 0.2,0.2 0.4,0 0.2,-0.7 -0.6,-0.4 0.4,-0.4 -0.4,-0.2 0,-0.7 z"
         inkscape:connector-curvature="0"
         id="path68"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 38.862637,559.55042 -0.823389,0 -0.470508,0.45241 -0.588135,0.22621 1.293897,0.45241 0.588135,-1.13103 z"
       inkscape:connector-curvature="0"
       id="path70"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g72"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 279.2,492.9 -0.7,0 -0.4,0.4 -0.5,0.2 1.1,0.4 0.5,-1 z"
         inkscape:connector-curvature="0"
         id="path74"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 40.274161,558.4194 -0.941016,0 0.588135,0.79172 0.352881,-0.22621 0.470508,0 0,-0.56551 -0.470508,0 z"
       inkscape:connector-curvature="0"
       id="path76"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g78"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 280.4,491.9 -0.8,0 0.5,0.7 0.3,-0.2 0.4,0 0,-0.5 -0.4,0 z"
         inkscape:connector-curvature="0"
         id="path80"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 34.863319,559.21112 1.17627,-0.22621 0.823389,0 0.705762,-0.45241 0,-0.1131 0,-0.45241 0.470508,-0.45241 0.705762,0 0.705762,0 -0.235254,-1.01792 0.941016,0 0.470508,0.79171 1.529152,1.13103 0.470508,-0.90482 1.646778,-0.67862 0.235254,-0.3393 1.293897,-0.67862 0.470508,-1.01792 -1.058643,-0.67862 0,-0.2262 1.17627,-0.79172 -0.117627,-0.2262 0.117627,-1.01793 0.470508,0 0,-0.45241 0.235254,-1.01792 0.588135,-0.2262 -0.588135,-0.56552 -0.705762,0 -0.117627,-0.45241 -3.646437,-1.47033 -0.470508,0.45241 0,-0.45241 0,-0.67862 0.941016,-0.2262 0,-0.79172 1.17627,-0.67861 0.235254,-0.11311 0.705762,-0.90482 0,-0.2262 0,-0.79172 -1.411524,-0.2262 0,-1.01793 0.470508,-0.67861 -0.470508,0.2262 -1.17627,0 -0.470508,-0.2262 -0.82339,0 -1.17627,-0.45241 -0.235254,0.45241 -0.705762,0 0,-0.45241 -1.17627,0.45241 -0.470508,0.2262 -0.588135,0 -0.117627,0 -0.588135,0 0,0.45241 -0.235254,0 -0.470508,0 -0.470508,-0.45241 0,-0.2262 -0.235254,0 -0.470508,0 -0.235254,0 -0.470508,0 -0.470508,0 0,1.13102 0.941016,1.24413 0.705762,1.01792 -1.17627,0.45241 -1.999659,0.79172 0,0.79172 -0.705762,0.2262 0.352881,0.67862 -0.352881,1.01792 0,0.90482 0.352881,0.79172 -0.823389,1.01792 0.823389,0.45241 0.588135,0 -0.235254,1.69654 0.705762,0 0.470508,1.01792 -0.470508,1.24413 1.764405,0 0.235254,-0.56551 0.470508,1.01792 0.705762,0 -0.470508,0.22621 0.470508,0.79171 -0.470508,0 0,0.22621 0,0.90482 -0.235254,0.56551 -0.705762,0 0.235254,0.22621 0.470508,0 z"
       inkscape:connector-curvature="0"
       id="DE"
       style="fill:#00b050;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g84"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 275.8,492.6 1,-0.2 0.7,0 0.6,-0.4 0,-0.1 0,-0.4 0.4,-0.4 0.6,0 0.6,0 -0.2,-0.9 0.8,0 0.4,0.7 1.3,1 0.4,-0.8 1.4,-0.6 0.2,-0.3 1.1,-0.6 0.4,-0.9 -0.9,-0.6 0,-0.2 1,-0.7 -0.1,-0.2 0.1,-0.9 0.4,0 0,-0.4 0.2,-0.9 0.5,-0.2 -0.5,-0.5 -0.6,0 -0.1,-0.4 -3.1,-1.3 -0.4,0.4 0,-0.4 0,-0.6 0.8,-0.2 0,-0.7 1,-0.6 0.2,-0.1 0.6,-0.8 0,-0.2 0,-0.7 -1.2,-0.2 0,-0.9 0.4,-0.6 -0.4,0.2 -1,0 -0.4,-0.2 -0.7,0 -1,-0.4 -0.2,0.4 -0.6,0 0,-0.4 -1,0.4 -0.4,0.2 -0.5,0 -0.1,0 -0.5,0 0,0.4 -0.2,0 -0.4,0 -0.4,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0 -0.4,0 -0.4,0 0,1 0.8,1.1 0.6,0.9 -1,0.4 -1.7,0.7 0,0.7 -0.6,0.2 0.3,0.6 -0.3,0.9 0,0.8 0.3,0.7 -0.7,0.9 0.7,0.4 0.5,0 -0.2,1.5 0.6,0 0.4,0.9 -0.4,1.1 1.5,0 0.2,-0.5 0.4,0.9 0.6,0 -0.4,0.2 0.4,0.7 -0.4,0 0,0.2 0,0.8 -0.2,0.5 -0.6,0 0.2,0.2 0.4,0 z"
         inkscape:connector-curvature="0"
         id="path86"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 24.394515,549.5974 0,-0.45241 0.823389,-0.45241 0.470508,0.45241 0.470508,-0.67861 0.235254,-0.45241 1.646779,-1.24413 0.235254,0.67861 0.470508,-1.01792 1.293897,-0.45241 0.235254,0 0.823389,0 0.823389,0 1.882032,-0.79172 1.17627,-0.45241 -0.705762,-1.13102 -0.941016,-1.24413 0,-1.01792 -0.705762,0 0,-0.56551 -0.705762,-0.90482 -0.823389,-1.01793 0,-0.2262 0.823389,0.2262 0.705762,-0.67861 0.470508,-0.56551 -0.470508,0 0.470508,-1.69654 -0.941016,0 0.470508,-0.45241 0.470508,-1.69654 0.705762,-0.2262 0.705762,0 -0.235254,-0.33931 0,-0.45241 -0.470508,-0.22621 -0.470508,0.22621 0,-0.22621 0,-0.45241 -0.705762,0 -0.705762,-0.2262 0,-0.79172 -1.646778,0 -0.705762,0.33931 -0.352881,0.45241 -0.470508,0 -0.705762,0 0,0.2262 -0.941017,0 -0.235254,0 -0.470508,-0.2262 -1.17627,-0.79172 0,-1.01792 -0.823389,-0.33931 -1.17627,0.33931 -1.411524,0.33931 -1.17627,0.45241 0,-0.45241 -1.293897,0 -2.587794,0.67861 -0.705762,1.01792 0.705762,2.26205 0.235254,0.45241 -0.235254,0.45241 0.235254,1.69654 1.058643,-1.01792 -0.588135,1.01792 -0.705762,0.22621 0,1.01792 -1.646778,1.47033 0,1.13103 -0.705762,0.1131 -1.293898,0.45241 -2.117285,0.67861 -0.470508,0.56552 0.470508,0 -0.470508,0.45241 0.823389,0 -0.352881,0.2262 -0.941016,0 0.470508,0.33931 1.17627,0.45241 0.705761,0 0.705762,0 1.293898,-0.79172 0.470508,0.33931 0.705762,0 0.705762,0 -0.235254,1.58343 -0.470508,1.24413 0.705762,0 0.705762,0 0,-0.67861 1.058643,-0.45241 0.941016,0 0.352881,0.67861 0.823389,0.45241 1.646778,0.33931 0.235254,0.67861 0,1.69654 2.35254,0.45241 z"
       inkscape:connector-curvature="0"
       id="path94"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g96"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 266.9,484.1 0,-0.4 0.7,-0.4 0.4,0.4 0.4,-0.6 0.2,-0.4 1.4,-1.1 0.2,0.6 0.4,-0.9 1.1,-0.4 0.2,0 0.7,0 0.7,0 1.6,-0.7 1,-0.4 -0.6,-1 -0.8,-1.1 0,-0.9 -0.6,0 0,-0.5 -0.6,-0.8 -0.7,-0.9 0,-0.2 0.7,0.2 0.6,-0.6 0.4,-0.5 -0.4,0 0.4,-1.5 -0.8,0 0.4,-0.4 0.4,-1.5 0.6,-0.2 0.6,0 -0.2,-0.3 0,-0.4 -0.4,-0.2 -0.4,0.2 0,-0.2 0,-0.4 -0.6,0 -0.6,-0.2 0,-0.7 -1.4,0 -0.6,0.3 -0.3,0.4 -0.4,0 -0.6,0 0,0.2 -0.8,0 -0.2,0 -0.4,-0.2 -1,-0.7 0,-0.9 -0.7,-0.3 -1,0.3 -1.2,0.3 -1,0.4 0,-0.4 -1.1,0 -2.2,0.6 -0.6,0.9 0.6,2 0.2,0.4 -0.2,0.4 0.2,1.5 0.9,-0.9 -0.5,0.9 -0.6,0.2 0,0.9 -1.4,1.3 0,1 -0.6,0.1 -1.1,0.4 -1.8,0.6 -0.4,0.5 0.4,0 -0.4,0.4 0.7,0 -0.3,0.2 -0.8,0 0.4,0.3 1,0.4 0.6,0 0.6,0 1.1,-0.7 0.4,0.3 0.6,0 0.6,0 -0.2,1.4 -0.4,1.1 0.6,0 0.6,0 0,-0.6 0.9,-0.4 0.8,0 0.3,0.6 0.7,0.4 1.4,0.3 0.2,0.6 0,1.5 2,0.4 z"
         inkscape:connector-curvature="0"
         id="path98"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 37.098232,531.16169 0.588135,0 0,-0.3393 0,-0.33931 0.470508,-0.45241 0,-0.56551 0,-0.33931 -0.470508,-0.33931 0,-0.45241 0,-0.2262 -0.588135,-0.45241 0,-0.22621 -0.235254,0 -0.470508,0 -0.235254,0.22621 0.235254,0 0,0.45241 -0.235254,0 0,0.2262 -0.470508,0 0.470508,0.45241 0,0.33931 -0.470508,0 0,0.33931 0.470508,0 -0.470508,0.3393 0.470508,0 0,0.22621 0.235254,0.45241 0.470508,0 0.235254,0 0,0.33931 0,0.3393 z"
       inkscape:connector-curvature="0"
       id="path100"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g102"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 277.7,467.8 0.5,0 0,-0.3 0,-0.3 0.4,-0.4 0,-0.5 0,-0.3 -0.4,-0.3 0,-0.4 0,-0.2 -0.5,-0.4 0,-0.2 -0.2,0 -0.4,0 -0.2,0.2 0.2,0 0,0.4 -0.2,0 0,0.2 -0.4,0 0.4,0.4 0,0.3 -0.4,0 0,0.3 0.4,0 -0.4,0.3 0.4,0 0,0.2 0.2,0.4 0.4,0 0.2,0 0,0.3 0,0.3 z"
         inkscape:connector-curvature="0"
         id="path104"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 9.2206317,547.10915 -0.705762,0.3393 1.1762701,0.67862 1.1762702,1.47033 0.705762,0.22621 1.52915,0 0.705762,1.01792 -0.941016,-0.33931 -1.764404,0.33931 -0.235254,0.45241 -0.9410162,-0.45241 -0.235254,0.67862 1.8820322,1.47033 0,1.01792 -0.705762,0 -0.470508,0 0.470508,0.22621 1.52915,0.67861 1.411524,0 -0.235254,0.79172 0,0.79172 0,0.45241 -0.470508,0 -0.235254,1.47033 0,0.2262 0.705762,0.67862 -0.705762,0 -1.293896,-0.22621 -1.17627,-0.45241 -0.7057622,0.45241 0.7057622,1.69654 0,0.33931 0,0.67861 -1.1762703,0 -0.470508,-1.47033 0,1.01792 0,1.47034 0.9410161,0.90482 -0.9410161,-0.22621 -0.705762,0.56551 0.705762,0.67862 0.470508,0.79172 -0.705762,0.2262 0.235254,1.01792 0.9410161,0.45241 0,0.67862 0.235254,0 0.4705082,1.24413 1.411524,-0.22621 1.764404,0.22621 0,-1.13103 -1.764404,-0.79172 0,-0.45241 -0.235254,-0.56551 1.293896,0.22621 2.587795,0 0.470508,-0.67862 -0.941016,-1.01792 -1.176271,-1.69654 0.470509,-0.2262 -0.705763,-0.45241 -0.705762,-0.33931 1.411525,0 1.882032,-1.69654 1.17627,-1.92274 1.529151,-1.01792 0.352881,-1.47034 -0.352881,0 0.941016,-1.35723 -0.588135,-0.3393 0.588135,-0.56552 0.235254,0.33931 0.941016,0.22621 1.411524,-1.01793 -0.705762,-1.69653 -0.705762,-0.22621 -0.470508,-0.45241 -0.470508,-0.33931 1.646778,-0.2262 -0.705762,-1.13103 -1.17627,-0.3393 -1.764405,0.3393 -0.705762,0 -1.882032,-0.3393 -1.646779,0 -0.941016,-0.22621 -1.058642,-0.90482 -0.705762,0 -1.8820323,-0.56551 z"
       inkscape:connector-curvature="0"
       id="GB"
       style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
       inkscape:label="GB"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g104"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"
       onmouseover="mouseoverpays(this)"><path
         d="m 254,481.9 -0.6,0.3 1,0.6 1,1.3 0.6,0.2 1.3,0 0.6,0.9 -0.8,-0.3 -1.5,0.3 -0.2,0.4 -0.8,-0.4 -0.2,0.6 1.6,1.3 0,0.9 -0.6,0 -0.4,0 0.4,0.2 1.3,0.6 1.2,0 -0.2,0.7 0,0.7 0,0.4 -0.4,0 -0.2,1.3 0,0.2 0.6,0.6 -0.6,0 -1.1,-0.2 -1,-0.4 -0.6,0.4 0.6,1.5 0,0.3 0,0.6 -1,0 -0.4,-1.3 0,0.9 0,1.3 0.8,0.8 -0.8,-0.2 -0.6,0.5 0.6,0.6 0.4,0.7 -0.6,0.2 0.2,0.9 0.8,0.4 0,0.6 0.2,0 0.4,1.1 1.2,-0.2 1.5,0.2 0,-1 -1.5,-0.7 0,-0.4 -0.2,-0.5 1.1,0.2 2.2,0 0.4,-0.6 -0.8,-0.9 -1,-1.5 0.4,-0.2 -0.6,-0.4 -0.6,-0.3 1.2,0 1.6,-1.5 1,-1.7 1.3,-0.9 0.3,-1.3 -0.3,0 0.8,-1.2 -0.5,-0.3 0.5,-0.5 0.2,0.3 0.8,0.2 1.2,-0.9 -0.6,-1.5 -0.6,-0.2 -0.4,-0.4 -0.4,-0.3 1.4,-0.2 -0.6,-1 -1,-0.3 -1.5,0.3 -0.6,0 -1.6,-0.3 -1.4,0 -0.8,-0.2 -0.9,-0.8 -0.6,0 -1.6,-0.5 z"
         inkscape:connector-curvature="0"
         id="path110"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 5.9270755,559.21112 0.7057621,0.45241 1.17627,0.45241 0.2352541,0 0.823389,-1.13103 -0.352881,-0.45241 0.352881,0 0.4705081,-1.01792 -1.2938971,-0.67862 -0.2352541,0.45241 -0.470508,0 -1.1762701,1.13103 0,-0.45241 0,-0.45241 -0.235254,-0.22621 -0.9410161,0.22621 -0.235254,0.90482 0.235254,0.1131 0,0.45241 0.9410161,0.22621 z"
       inkscape:connector-curvature="0"
       id="path9139"
       style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g114"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 251.2,492.6 0.6,0.4 1,0.4 0.2,0 0.7,-1 -0.3,-0.4 0.3,0 0.4,-0.9 -1.1,-0.6 -0.2,0.4 -0.4,0 -1,1 0,-0.4 0,-0.4 -0.2,-0.2 -0.8,0.2 -0.2,0.8 0.2,0.1 0,0.4 0.8,0.2 z"
         inkscape:connector-curvature="0"
         id="path116"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 6.6328376,566.78898 0,0.45241 0,0.22621 0,0.33931 0.352881,0 0,0.2262 0.823389,0.33931 0,-0.33931 -0.470508,-0.56551 0.470508,0 -0.470508,-0.22621 -0.705762,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path118"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g120"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 251.8,499.3 0,0.4 0,0.2 0,0.3 0.3,0 0,0.2 0.7,0.3 0,-0.3 -0.4,-0.5 0.4,0 -0.4,-0.2 -0.6,-0.4 z"
         inkscape:connector-curvature="0"
         id="path122"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 8.5148697,564.97934 -0.470508,0 0.470508,0 -0.7057621,0 -0.470508,0.79172 -0.470508,0 0.470508,0.67862 0.470508,-0.33931 0,0.33931 0.2352541,0 0,-1.01793 0.823389,0 -0.352881,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path124"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g126"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 253.4,497.7 -0.4,0 0.4,0 -0.6,0 -0.4,0.7 -0.4,0 0.4,0.6 0.4,-0.3 0,0.3 0.2,0 0,-0.9 0.7,0 -0.3,-0.4 z"
         inkscape:connector-curvature="0"
         id="path128"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 14.39622,569.95585 -1.176271,0 0,0.67862 0.470508,-0.45241 0.705763,0 0,-0.22621 z"
       inkscape:connector-curvature="0"
       id="path142"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><path
       d="m 6.2799565,564.97934 -0.352881,0 0,0.67862 0.352881,-0.22621 0,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path148"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g150"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 251.5,497.7 -0.3,0 0,0.6 0.3,-0.2 0,-0.4 z"
         inkscape:connector-curvature="0"
         id="path152"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 6.2799565,566.11037 -0.823389,0.33931 0.823389,0 0,-0.33931 z"
       inkscape:connector-curvature="0"
       id="path154"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g156"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 251.5,498.7 -0.7,0.3 0.7,0 0,-0.3 z"
         inkscape:connector-curvature="0"
         id="path158"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 5.9270755,559.21112 -0.9410161,-0.22621 0,-0.45241 -0.352881,-0.1131 0.352881,-0.90482 0.9410161,-0.22621 0.235254,0.22621 0,0.45241 0,0.45241 1.1762701,-1.13103 0.470508,0 0.2352541,-0.3393 -0.2352541,-0.45241 -0.470508,0.45241 0.470508,-1.01793 0,-0.67861 0,-0.45241 0,-0.79172 -0.470508,-0.45241 0,-0.45241 0,-1.01792 -1.1762701,0 -1.5291511,-0.22621 -0.9410161,-0.56551 0,0.1131 -0.588135,-0.1131 -1.2938971,-0.45241 -0.705762,0 -0.70576207,0 0.70576207,0.56551 -1.1762701,0 0,0.45241 1.05864308,0.22621 -1.2938971,0 0.23525402,0.45241 1.05864308,0 0,0.33931 0.11762702,0.2262 1.17627,0.45241 0.4705081,0 -0.4705081,0.45241 -0.470508,-0.45241 -0.705762,0 0.470508,0.45241 0.235254,0.67862 0.470508,0.1131 -1.17627,0.45241 -0.70576207,0.67861 0.70576207,0.33931 0.470508,0 -0.470508,0.22621 -0.11762702,0.79171 -0.58813505,-0.3393 0.58813505,0.56551 0.58813502,0 0.705762,-0.22621 0.8233891,0.22621 0.470508,0 0.117627,0.45241 0.5881351,0.45241 0,0.1131 -1.1762701,0 0.470508,0.45241 0.117627,0.22621 1.2938971,0.90482 0.9410161,0.1131 0.7057621,-0.1131 -0.7057621,-0.90482 z"
       inkscape:connector-curvature="0"
       id="path160"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g162"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 251.2,492.6 -0.8,-0.2 0,-0.4 -0.3,-0.1 0.3,-0.8 0.8,-0.2 0.2,0.2 0,0.4 0,0.4 1,-1 0.4,0 0.2,-0.3 -0.2,-0.4 -0.4,0.4 0.4,-0.9 0,-0.6 0,-0.4 0,-0.7 -0.4,-0.4 0,-0.4 0,-0.9 -1,0 -1.3,-0.2 -0.8,-0.5 0,0.1 -0.5,-0.1 -1.1,-0.4 -0.6,0 -0.6,0 0.6,0.5 -1,0 0,0.4 0.9,0.2 -1.1,0 0.2,0.4 0.9,0 0,0.3 0.1,0.2 1,0.4 0.4,0 -0.4,0.4 -0.4,-0.4 -0.6,0 0.4,0.4 0.2,0.6 0.4,0.1 -1,0.4 -0.6,0.6 0.6,0.3 0.4,0 -0.4,0.2 -0.1,0.7 -0.5,-0.3 0.5,0.5 0.5,0 0.6,-0.2 0.7,0.2 0.4,0 0.1,0.4 0.5,0.4 0,0.1 -1,0 0.4,0.4 0.1,0.2 1.1,0.8 0.8,0.1 0.6,-0.1 -0.6,-0.8 z"
         inkscape:connector-curvature="0"
         id="path164"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 39.450772,539.75748 1.293897,0 0,0.56552 1.176271,0 0.705762,0 1.17627,-0.56552 1.293897,-0.90482 0.117627,0 -0.117627,-0.2262 1.058643,-1.01792 0.235254,-0.67862 -0.235254,-0.33931 -0.941016,0.33931 -0.117627,0.22621 -0.823389,0 0,-0.22621 -0.941016,-0.33931 -0.705762,-0.2262 0.470508,-0.79172 -0.470508,-0.33931 0,-0.79171 2.587794,-2.37516 1.764405,-2.26205 2.35254,-1.01792 0.705762,0 0.235254,-0.45241 -0.235254,-0.2262 0,-0.45241 1.411524,-0.56552 1.882032,-0.67861 1.764405,-1.47034 0,-0.56551 -0.705762,0 -0.588135,0.33931 -0.470508,0.67862 -1.17627,0.2262 -0.235254,-0.2262 -0.705762,-1.47034 0.705762,-0.2262 0.235254,0 0.588135,-1.47034 -1.293897,-0.1131 0,-0.90482 -0.705762,-0.90482 -0.705762,-0.45241 0,0.67862 0.235254,0.45241 0,0.56551 0.470508,0.56551 -1.17627,2.60136 -0.235254,0 -0.941016,0.2262 0,0.45242 -0.235254,0.67861 -1.058643,0 -0.117627,0.33931 -0.588135,0 -0.235254,0.2262 0,0.79172 -1.646778,0 -1.17627,0.22621 -1.17627,1.58343 -0.235254,0.1131 -1.176271,1.13103 -0.823389,0.33931 -0.705762,0 0.235254,0.67861 -0.235254,0.45241 -0.470508,0.79172 -0.470508,0.90482 -1.17627,0.1131 -0.941016,0 -0.941016,-0.1131 -0.823389,-0.90482 -0.117627,0 0,0.45241 0.117627,0.45241 -0.705762,0 -0.705762,0.1131 -0.470508,1.80964 -0.470508,0.33931 0.941016,0 -0.470508,1.69654 0.470508,0 1.293897,0.45241 0.117627,-0.45241 0.823389,0.67861 1.646778,-0.2262 0.235254,0.2262 0,0.56551 0.470508,0 0.470508,-0.2262 0.235254,0 0.470508,-0.33931 0,0.56551 0.470508,0 0.235254,0.90482 z"
       inkscape:connector-curvature="0"
       id="IT"
       style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g174"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 279.7,475.4 1.1,0 0,0.5 1,0 0.6,0 1,-0.5 1.1,-0.8 0.1,0 -0.1,-0.2 0.9,-0.9 0.2,-0.6 -0.2,-0.3 -0.8,0.3 -0.1,0.2 -0.7,0 0,-0.2 -0.8,-0.3 -0.6,-0.2 0.4,-0.7 -0.4,-0.3 0,-0.7 2.2,-2.1 1.5,-2 2,-0.9 0.6,0 0.2,-0.4 -0.2,-0.2 0,-0.4 1.2,-0.5 1.6,-0.6 1.5,-1.3 0,-0.5 -0.6,0 -0.5,0.3 -0.4,0.6 -1,0.2 -0.2,-0.2 -0.6,-1.3 0.6,-0.2 0.2,0 0.5,-1.3 -1.1,-0.1 0,-0.8 -0.6,-0.8 -0.6,-0.4 0,0.6 0.2,0.4 0,0.5 0.4,0.5 -1,2.3 -0.2,0 -0.8,0.2 0,0.4 -0.2,0.6 -0.9,0 -0.1,0.3 -0.5,0 -0.2,0.2 0,0.7 -1.4,0 -1,0.2 -1,1.4 -0.2,0.1 -1,1 -0.7,0.3 -0.6,0 0.2,0.6 -0.2,0.4 -0.4,0.7 -0.4,0.8 -1,0.1 -0.8,0 -0.8,-0.1 -0.7,-0.8 -0.1,0 0,0.4 0.1,0.4 -0.6,0 -0.6,0.1 -0.4,1.6 -0.4,0.3 0.8,0 -0.4,1.5 0.4,0 1.1,0.4 0.1,-0.4 0.7,0.6 1.4,-0.2 0.2,0.2 0,0.5 0.4,0 0.4,-0.2 0.2,0 0.4,-0.3 0,0.5 0.4,0 0.2,0.8 z"
         inkscape:connector-curvature="0"
         id="path176"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 48.860933,517.58939 -1.17627,0.22621 -0.470508,0.45241 -0.941016,0.45241 -1.646778,0.56551 -0.823389,0.2262 0,0.79172 0.588135,0.33931 0.235254,-0.33931 0.470508,0.33931 0.117627,0 1.293897,-0.33931 2.823048,0.33931 0,-0.67862 -0.470508,-0.67861 0.470508,-1.01792 -0.470508,-0.67862 z"
       inkscape:connector-curvature="0"
       id="path178"
       style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g180"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 287.7,455.8 -1,0.2 -0.4,0.4 -0.8,0.4 -1.4,0.5 -0.7,0.2 0,0.7 0.5,0.3 0.2,-0.3 0.4,0.3 0.1,0 1.1,-0.3 2.4,0.3 0,-0.6 -0.4,-0.6 0.4,-0.9 -0.4,-0.6 z"
         inkscape:connector-curvature="0"
         id="path182"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 36.39247,522.11349 -0.705762,0.45241 0,1.13103 0,0.1131 0,0.90482 0,0.2262 -0.823389,1.13103 0.352881,0.33931 0.470508,0 1.411524,0.67861 0.470508,0 0.705762,-1.47033 -0.235254,-0.2262 0,-0.45242 -0.470508,-2.37515 -0.705762,0.45241 -0.470508,-0.90482 z"
       inkscape:connector-curvature="0"
       id="path184"
       style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g186"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 277.1,459.8 -0.6,0.4 0,1 0,0.1 0,0.8 0,0.2 -0.7,1 0.3,0.3 0.4,0 1.2,0.6 0.4,0 0.6,-1.3 -0.2,-0.2 0,-0.4 -0.4,-2.1 -0.6,0.4 -0.4,-0.8 z"
         inkscape:connector-curvature="0"
         id="path188"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 30.158239,545.97812 0,1.13103 0.470508,0.3393 0.705762,0.22621 -0.235254,-0.56551 0.823389,-0.33931 0,-0.79172 -0.823389,0 -0.941016,0 z"
       inkscape:connector-curvature="0"
       id="LU"
       style="fill:#ffc000;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g192"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 271.8,480.9 0,1 0.4,0.3 0.6,0.2 -0.2,-0.5 0.7,-0.3 0,-0.7 -0.7,0 -0.8,0 z"
         inkscape:connector-curvature="0"
         id="path194"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 26.041293,550.16292 0,0.56551 0.705762,0.45241 1.17627,0 -0.941016,0.2262 -0.235254,0.45241 0.705762,1.01793 0.470508,1.69653 0.82339,-1.01792 0.470508,-1.01792 0.705762,-0.56551 -0.470508,1.13102 0.470508,0.79172 -0.470508,0 0,0.67861 1.17627,0.67862 1.17627,0.33931 0.235254,-0.79172 0,0.45241 0.470508,0.56551 0.470508,-1.24413 -0.470508,-1.01792 -0.705762,0 0.235254,-1.69654 -0.705762,0 -0.705762,-0.45241 0.705762,-1.01792 -0.235254,-0.90482 0,-0.79172 -1.17627,0.33931 0.235254,0.67862 -0.941016,0.45241 -1.058643,0.2262 -0.705763,0 -0.705762,-0.2262 -0.470508,-0.45241 -0.235254,0.45241 z"
       inkscape:connector-curvature="0"
       id="NL"
       style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g204"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 268.3,484.6 0,0.5 0.6,0.4 1,0 -0.8,0.2 -0.2,0.4 0.6,0.9 0.4,1.5 0.7,-0.9 0.4,-0.9 0.6,-0.5 -0.4,1 0.4,0.7 -0.4,0 0,0.6 1,0.6 1,0.3 0.2,-0.7 0,0.4 0.4,0.5 0.4,-1.1 -0.4,-0.9 -0.6,0 0.2,-1.5 -0.6,0 -0.6,-0.4 0.6,-0.9 -0.2,-0.8 0,-0.7 -1,0.3 0.2,0.6 -0.8,0.4 -0.9,0.2 -0.6,0 -0.6,-0.2 -0.4,-0.4 -0.2,0.4 z"
         inkscape:connector-curvature="0"
         id="path206"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><g
       id="g210"
       transform="translate(-246.2264,61.599951)" /><path
       d="m 45.332123,544.05538 1.17627,-0.45241 0.705762,0 0.941016,1.13102 1.17627,-0.45241 1.882032,-0.2262 0,-1.01792 0.823389,-0.67862 0,-0.33931 -0.470508,-0.45241 -0.705762,0 -0.470508,0 0.470508,-0.2262 0,-1.47033 -0.705762,-0.22621 0,-0.45241 -0.823389,0 -2.117286,-0.33931 -1.882032,0 -0.235254,0 -1.293897,0.79172 -1.17627,0.67862 -0.705762,0 -1.176271,0 0,-0.67862 -1.17627,0 -0.235254,0 0,0.22621 -1.058643,0 0,0.45241 -0.117627,0 -0.470508,0.45241 0,0.1131 0.470508,0.45241 1.17627,-0.45241 0,0.45241 0.705762,0 0.235254,-0.45241 1.17627,0.45241 0.705763,0 0.470508,0.2262 1.17627,0 0.588135,-0.2262 -0.588135,0.67861 0,1.01793 1.529151,0.2262 0,0.79172 z"
       inkscape:connector-curvature="0"
       id="path226"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g228"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 284.7,479.2 1,-0.4 0.6,0 0.8,1 1,-0.4 1.6,-0.2 0,-0.9 0.7,-0.6 0,-0.3 -0.4,-0.4 -0.6,0 -0.4,0 0.4,-0.2 0,-1.3 -0.6,-0.2 0,-0.4 -0.7,0 -1.8,-0.3 -1.6,0 -0.2,0 -1.1,0.7 -1,0.6 -0.6,0 -1,0 0,-0.6 -1,0 -0.2,0 0,0.2 -0.9,0 0,0.4 -0.1,0 -0.4,0.4 0,0.1 0.4,0.4 1,-0.4 0,0.4 0.6,0 0.2,-0.4 1,0.4 0.6,0 0.4,0.2 1,0 0.5,-0.2 -0.5,0.6 0,0.9 1.3,0.2 0,0.7 z"
         inkscape:connector-curvature="0"
         id="path230"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 55.212791,558.4194 6.234232,0 1.293897,-1.92274 0.588135,-1.35723 0.588135,-1.24413 -1.646778,-0.79172 1.058643,-1.13102 0.588135,-1.24413 0.705762,-1.24413 -2.705421,-3.16687 0.352881,-0.90482 -0.352881,-0.33931 -0.470508,0.33931 -1.17627,0.33931 -0.941016,0.2262 -0.470508,-0.2262 -0.705762,0 -1.17627,-0.33931 -0.470508,0.33931 -0.352881,0.56551 -0.941017,-0.33931 -0.941016,0.79172 0,0.2262 -1.646778,0.45241 0,0.22621 -0.705762,0.33931 -0.823389,-0.33931 -0.470508,0 -0.705762,0.33931 0,0.45241 0,0.2262 -0.470508,0 -1.17627,0.33931 0,0.45241 -0.705762,0 -0.588135,0 0.588135,0.67862 -0.588135,0.2262 -0.117627,1.01792 0,0.45241 -0.588135,0 -0.235254,1.01793 0.235254,0.2262 -1.17627,0.79172 0,0.2262 0.941016,0.67862 -0.352881,1.01792 0.352881,0.45241 0.235254,0 0,0.67862 2.470167,0.56551 0.470508,0.90482 0.705762,0.1131 0.235254,0 2.470167,0.45241 0.470508,-0.56551 0.235254,-0.45241 0.941016,0 0.705762,-0.45241 0.235254,0.90482 z"
       inkscape:connector-curvature="0"
       id="PL"
       style="fill:#ffff00;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g234"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 293.1,491.9 5.3,0 1.1,-1.7 0.5,-1.2 0.5,-1.1 -1.4,-0.7 0.9,-1 0.5,-1.1 0.6,-1.1 -2.3,-2.8 0.3,-0.8 -0.3,-0.3 -0.4,0.3 -1,0.3 -0.8,0.2 -0.4,-0.2 -0.6,0 -1,-0.3 -0.4,0.3 -0.3,0.5 -0.8,-0.3 -0.8,0.7 0,0.2 -1.4,0.4 0,0.2 -0.6,0.3 -0.7,-0.3 -0.4,0 -0.6,0.3 0,0.4 0,0.2 -0.4,0 -1,0.3 0,0.4 -0.6,0 -0.5,0 0.5,0.6 -0.5,0.2 -0.1,0.9 0,0.4 -0.5,0 -0.2,0.9 0.2,0.2 -1,0.7 0,0.2 0.8,0.6 -0.3,0.9 0.3,0.4 0.2,0 0,0.6 2.1,0.5 0.4,0.8 0.6,0.1 0.2,0 2.1,0.4 0.4,-0.5 0.2,-0.4 0.8,0 0.6,-0.4 0.2,0.8 z"
         inkscape:connector-curvature="0"
         id="path236"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 1.8101302,529.35205 0.470508,-0.3393 0.7057621,0.3393 0.470508,-0.67861 0.8233891,0 0.705762,0 0.9410161,0 0.7057621,-0.45241 0.352881,-0.1131 -1.0586431,-1.47034 0,-1.13102 -0.470508,-1.69654 -0.7057621,0 0.235254,-1.24413 0,-0.33931 0,-0.56551 0.4705081,-0.67861 0,-0.45241 -0.7057621,-0.56552 0,-1.35723 -1.7644051,-0.3393 -0.7057621,0.3393 -1.17627,0 0.470508,0.67862 0,1.92274 0,0.45241 -0.470508,0 0,0.45241 -0.82338908,0 0.58813505,1.13103 0.23525403,0.45241 1.17627,2.71446 -0.470508,2.94066 z"
       inkscape:connector-curvature="0"
       id="PT"
       style="fill:#ff0fff;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g240"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 247.7,466.2 0.4,-0.3 0.6,0.3 0.4,-0.6 0.7,0 0.6,0 0.8,0 0.6,-0.4 0.3,-0.1 -0.9,-1.3 0,-1 -0.4,-1.5 -0.6,0 0.2,-1.1 0,-0.3 0,-0.5 0.4,-0.6 0,-0.4 -0.6,-0.5 0,-1.2 -1.5,-0.3 -0.6,0.3 -1,0 0.4,0.6 0,1.7 0,0.4 -0.4,0 0,0.4 -0.7,0 0.5,1 0.2,0.4 1,2.4 -0.4,2.6 z"
         inkscape:connector-curvature="0"
         id="path242"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 68.975151,542.58505 1.411525,0.45241 2.117286,-2.71446 0.235254,0 0.588135,-3.39308 0.705762,-0.67861 1.882032,0.67861 0,-1.47033 -1.17627,-0.22621 0,0.67862 -0.235254,0 0,-0.67862 0,-0.45241 0,-0.3393 -0.470508,-1.24413 -0.588135,0 -0.117627,0.33931 -1.764405,0 -0.117627,0.2262 -1.99966,-0.56551 -0.941016,-0.45241 -0.235254,-0.22621 -1.293897,0.22621 -1.058643,0 -0.588135,0 -0.941016,0.45241 -1.17627,-0.45241 0,0.45241 0.235254,0.33931 -0.705762,0.2262 -0.470508,0.67862 0.470508,0.3393 -0.470508,0 -0.235254,-0.3393 -0.823389,0.3393 -0.941016,0.45241 0,0.22621 0,0.79172 -1.411524,0.67861 0,0.67862 -0.470508,0.56551 0.941016,0 0,0.45241 0.705762,0.2262 0,0.33931 0.235254,0.67862 0.470508,1.47033 0.823389,0.67861 0.705762,0.45241 0.470508,0.11311 0.705762,0 3.52881,-0.56552 1.999659,0.56552 z"
       inkscape:connector-curvature="0"
       id="RO"
       style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g246"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 304.8,477.9 1.2,0.4 1.8,-2.4 0.2,0 0.5,-3 0.6,-0.6 1.6,0.6 0,-1.3 -1,-0.2 0,0.6 -0.2,0 0,-0.6 0,-0.4 0,-0.3 -0.4,-1.1 -0.5,0 -0.1,0.3 -1.5,0 -0.1,0.2 -1.7,-0.5 -0.8,-0.4 -0.2,-0.2 -1.1,0.2 -0.9,0 -0.5,0 -0.8,0.4 -1,-0.4 0,0.4 0.2,0.3 -0.6,0.2 -0.4,0.6 0.4,0.3 -0.4,0 -0.2,-0.3 -0.7,0.3 -0.8,0.4 0,0.2 0,0.7 -1.2,0.6 0,0.6 -0.4,0.5 0.8,0 0,0.4 0.6,0.2 0,0.3 0.2,0.6 0.4,1.3 0.7,0.6 0.6,0.4 0.4,0.1 0.6,0 3,-0.5 1.7,0.5 z"
         inkscape:connector-curvature="0"
         id="path248"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><g
       id="g252"
       transform="translate(-246.2264,61.599951)" /><path
       d="m 52.272116,564.52693 -0.352881,1.80964 0.705762,0.79172 0.705762,0 -0.235254,-0.33931 0,-1.13102 0,-0.1131 -0.823389,-1.01793 z"
       inkscape:connector-curvature="0"
       id="path256"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><path
       d="m 49.331441,562.71729 0,0.90482 0.235254,1.01793 0,0.79171 0.588135,0.22621 -0.588135,-1.24413 -0.235254,-1.69654 z"
       inkscape:connector-curvature="0"
       id="path262"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g264"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 288.1,495.7 0,0.8 0.2,0.9 0,0.7 0.5,0.2 -0.5,-1.1 -0.2,-1.5 z"
         inkscape:connector-curvature="0"
         id="path266"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 46.390766,559.21112 -0.235254,0.45241 0,0.45241 0.823389,-0.45241 -0.588135,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path268"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g270"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 285.6,492.6 -0.2,0.4 0,0.4 0.7,-0.4 -0.5,-0.4 z"
         inkscape:connector-curvature="0"
         id="path272"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 33.216541,541.45402 0.352881,0 0.588135,0 0.235254,0 0.470508,0 0.235254,0 0,0.1131 0.470508,0.45241 0.470508,0 0.235254,0 0,-0.45241 0.470508,0 0.235254,0 0.588135,0 0.470508,-0.1131 -0.470508,-0.45241 0,-0.2262 0.470508,-0.45241 0.117627,0 0,-0.33931 0.470508,0 0.470508,-0.33931 0.352881,0 -0.352881,-0.79172 -0.470508,0 0,-0.56551 -0.470508,0.45241 -0.117627,0 -0.470508,0.1131 -0.588135,0 0,-0.56551 -0.235254,-0.2262 -1.646778,0.2262 -0.705762,-0.67861 -0.235254,0.45241 -1.293897,-0.45241 -0.470508,0.67861 -0.588135,0.56551 -0.823389,-0.1131 0,0.1131 0.823389,1.13103 0.588135,0.79172 0,0.67861 0.823389,0 z"
       inkscape:connector-curvature="0"
       id="CH"
       style="fill:#02eee8;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g276"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 274.4,476.9 0.3,0 0.5,0 0.2,0 0.4,0 0.2,0 0,0.1 0.4,0.4 0.4,0 0.2,0 0,-0.4 0.4,0 0.2,0 0.5,0 0.4,-0.1 -0.4,-0.4 0,-0.2 0.4,-0.4 0.1,0 0,-0.3 0.4,0 0.4,-0.3 0.3,0 -0.3,-0.7 -0.4,0 0,-0.5 -0.4,0.4 -0.1,0 -0.4,0.1 -0.5,0 0,-0.5 -0.2,-0.2 -1.4,0.2 -0.6,-0.6 -0.2,0.4 -1.1,-0.4 -0.4,0.6 -0.5,0.5 -0.7,-0.1 0,0.1 0.7,1 0.5,0.7 0,0.6 0.7,0 z"
         inkscape:connector-curvature="0"
         id="path278"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 15.925371,532.17962 0.705762,-1.13103 2.705421,-0.56551 1.17627,0 0,0.33931 1.17627,-0.33931 0.705762,-0.67862 0.823389,0.22621 1.17627,-0.22621 0.705762,0.22621 0.470508,-0.22621 -0.470508,-0.3393 0,-0.67862 -0.705762,-0.67861 -1.17627,-1.01793 -1.529151,0 -0.705762,-0.3393 -0.470508,0 0,-1.13103 -0.705762,-0.56551 -1.646778,-1.35723 0.470508,-0.56552 0.705762,-1.13102 0,-0.45241 -0.705762,-0.33931 -0.705762,-0.67861 -0.588135,-0.56552 0,-0.45241 -0.352881,-0.2262 -1.058643,0 -0.823389,-0.67862 -0.352881,-1.01792 -0.470508,0.22621 -0.470509,0 -0.235254,-0.22621 -0.470508,0 -0.705762,0 -0.470508,0 -0.941015,0 -0.941016,-0.33931 -0.7057622,0 -1.2938971,-1.13102 -1.4115241,0.67861 -0.4705081,0.79172 0,0.67862 -1.1762701,0.3393 -0.235254,0 0,1.35723 0.7057621,0.56552 0,0.45241 -0.4705081,0.56551 0,0.67861 0,0.45241 -0.235254,1.24413 0.7057621,0 0.470508,1.69654 0,1.13103 0.9410161,1.35723 -0.235254,0.2262 -0.7057621,0.45241 -0.9410161,0 -0.705762,0 -0.7057621,0 -0.470508,0.67862 -0.705762,-0.33931 -0.5881351,0.33931 0.5881351,0.3393 -0.5881351,0.67862 -0.117627,0 0,0.33931 -0.5881351,1.13102 0.7057621,0.22621 1.2938971,0 0,0.45241 0.7057621,0.1131 0.941016,-0.1131 1.8820322,0 1.17627,0 1.9996592,-0.45241 2.1172852,0 1.882032,0 2.117287,0 z"
       inkscape:connector-curvature="0"
       id="ES"
       style="fill:#7ff018;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g282"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 259.7,468.7 0.6,-1 2.3,-0.5 1,0 0,0.3 1,-0.3 0.6,-0.6 0.7,0.2 1,-0.2 0.6,0.2 0.4,-0.2 -0.4,-0.3 0,-0.6 -0.6,-0.6 -1,-0.9 -1.3,0 -0.6,-0.3 -0.4,0 0,-1 -0.6,-0.5 -1.4,-1.2 0.4,-0.5 0.6,-1 0,-0.4 -0.6,-0.3 -0.6,-0.6 -0.5,-0.5 0,-0.4 -0.3,-0.2 -0.9,0 -0.7,-0.6 -0.3,-0.9 -0.4,0.2 -0.4,0 -0.2,-0.2 -0.4,0 -0.6,0 -0.4,0 -0.8,0 -0.8,-0.3 -0.6,0 -1.1,-1 -1.2,0.6 -0.4,0.7 0,0.6 -1,0.3 -0.2,0 0,1.2 0.6,0.5 0,0.4 -0.4,0.5 0,0.6 0,0.4 -0.2,1.1 0.6,0 0.4,1.5 0,1 0.8,1.2 -0.2,0.2 -0.6,0.4 -0.8,0 -0.6,0 -0.6,0 -0.4,0.6 -0.6,-0.3 -0.5,0.3 0.5,0.3 -0.5,0.6 -0.1,0 0,0.3 -0.5,1 0.6,0.2 1.1,0 0,0.4 0.6,0.1 0.8,-0.1 1.6,0 1,0 1.7,-0.4 1.8,0 1.6,0 1.8,0 z"
         inkscape:connector-curvature="0"
         id="path284"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 25.100277,523.13141 -0.235254,0.11311 0,0.56551 -0.941016,0 1.17627,0.56551 0,-0.45241 1.058643,0 -0.588135,-0.1131 -0.470508,-0.67862 z"
       inkscape:connector-curvature="0"
       id="path286"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g288"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 267.5,460.7 -0.2,0.1 0,0.5 -0.8,0 1,0.5 0,-0.4 0.9,0 -0.5,-0.1 -0.4,-0.6 z"
         inkscape:connector-curvature="0"
         id="path290"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 27.099936,524.37554 -0.705762,0.45241 0.705762,0 0,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path292"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g294"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 269.2,461.8 -0.6,0.4 0.6,0 0,-0.4 z"
         inkscape:connector-curvature="0"
         id="path296"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 22.041975,522.00039 -0.705762,0.2262 0.705762,0.33931 0,-0.56551 z"
       inkscape:connector-curvature="0"
       id="path298"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g300"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 264.9,459.7 -0.6,0.2 0.6,0.3 0,-0.5 z"
         inkscape:connector-curvature="0"
         id="path302"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 21.689094,530.36998 0.823389,-0.67862 0.705762,0.33931 -1.529151,0.33931 z"
       inkscape:connector-curvature="0"
       id="path304"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g306"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 264.6,467.1 0.7,-0.6 0.6,0.3 -1.3,0.3 z"
         inkscape:connector-curvature="0"
         id="path308"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 61.329396,543.15056 1.529151,-0.56551 -0.470508,-0.22621 -0.823389,-0.45241 -0.705762,-0.56551 -0.470508,-1.58344 -0.235254,-0.56551 0,-0.45241 -0.705762,-0.2262 0,-0.45241 -0.941016,0 -1.529151,0 -0.352881,-0.22621 -1.529152,0 -0.470508,-0.45241 -0.235254,-0.33931 -1.764405,0.33931 -1.17627,0.67862 -0.235254,0 0,0.67861 -0.470508,0.45241 0,0.56551 0,1.58344 -0.470508,0.1131 0.470508,0 0.705762,0 0.470508,0.45241 0,0.45241 0.235254,-0.45241 1.646778,-0.45241 1.293897,0.90482 1.529152,0.67862 0.352881,0 1.529151,0.56551 0.470508,0 1.17627,0 0.235254,-0.45241 0.941016,0 z"
       inkscape:connector-curvature="0"
       id="path316"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g318"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 298.3,478.4 1.3,-0.5 -0.4,-0.2 -0.7,-0.4 -0.6,-0.5 -0.4,-1.4 -0.2,-0.5 0,-0.4 -0.6,-0.2 0,-0.4 -0.8,0 -1.3,0 -0.3,-0.2 -1.3,0 -0.4,-0.4 -0.2,-0.3 -1.5,0.3 -1,0.6 -0.2,0 0,0.6 -0.4,0.4 0,0.5 0,1.4 -0.4,0.1 0.4,0 0.6,0 0.4,0.4 0,0.4 0.2,-0.4 1.4,-0.4 1.1,0.8 1.3,0.6 0.3,0 1.3,0.5 0.4,0 1,0 0.2,-0.4 0.8,0 z"
         inkscape:connector-curvature="0"
         id="path320"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 63.91719,527.65552 3.175929,0.45241 0.470508,-0.45241 0.705762,0 0.705762,-0.11311 1.176271,0.11311 0,1.13102 0.352881,0 0.941016,-0.67861 0,-0.45241 -0.470508,-0.11311 0,-0.79171 -0.823389,-0.33931 0,-0.33931 -0.470509,0.33931 -1.411524,0.33931 -0.470508,0.3393 -0.705762,-0.3393 -0.470508,0 -0.235254,-0.33931 -1.058643,-0.33931 1.058643,-1.01792 -0.470508,0.33931 -0.588135,0 0.588135,-0.33931 0,-0.22621 -1.17627,0.22621 -1.293897,0.56551 0.470508,0 0,0.45241 -0.705762,-0.45241 0,-0.79172 1.529151,-1.69654 -0.823389,-0.90482 -0.705762,0 1.17627,-0.1131 1.529151,-1.13102 0.470508,-1.47034 -1.17627,0.90482 -0.823389,-0.45241 0.823389,-1.01792 -0.470508,0 -1.293897,0.56551 1.293897,-1.69653 0,-0.22621 0,-0.33931 -1.293897,0.56552 0,-0.22621 0,-0.33931 -0.235254,0 -0.470508,1.01793 -0.705762,0 0.235254,-0.67862 -0.235254,0.22621 -0.470508,0.90482 0,0.56551 -1.293897,0.67861 0.941016,0.56552 1.058643,0.45241 1.646778,-0.56552 0.470508,0.11311 -1.17627,0.67861 -0.941016,0 -1.999659,-0.2262 -0.705762,1.24412 0.705762,0 0,0.45241 -0.705762,0 -1.17627,1.01793 0.941016,0.67861 0.705762,1.69654 0.705762,0.67862 1.293897,0 0.235254,0.79171 0.941016,-0.45241 0.705762,0.56552 z"
       inkscape:connector-curvature="0"
       id="path328"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g330"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 300.5,464.7 2.7,0.4 0.4,-0.4 0.6,0 0.6,-0.1 1,0.1 0,1 0.3,0 0.8,-0.6 0,-0.4 -0.4,-0.1 0,-0.7 -0.7,-0.3 0,-0.3 -0.4,0.3 -1.2,0.3 -0.4,0.3 -0.6,-0.3 -0.4,0 -0.2,-0.3 -0.9,-0.3 0.9,-0.9 -0.4,0.3 -0.5,0 0.5,-0.3 0,-0.2 -1,0.2 -1.1,0.5 0.4,0 0,0.4 -0.6,-0.4 0,-0.7 1.3,-1.5 -0.7,-0.8 -0.6,0 1,-0.1 1.3,-1 0.4,-1.3 -1,0.8 -0.7,-0.4 0.7,-0.9 -0.4,0 -1.1,0.5 1.1,-1.5 0,-0.2 0,-0.3 -1.1,0.5 0,-0.2 0,-0.3 -0.2,0 -0.4,0.9 -0.6,0 0.2,-0.6 -0.2,0.2 -0.4,0.8 0,0.5 -1.1,0.6 0.8,0.5 0.9,0.4 1.4,-0.5 0.4,0.1 -1,0.6 -0.8,0 -1.7,-0.2 -0.6,1.1 0.6,0 0,0.4 -0.6,0 -1,0.9 0.8,0.6 0.6,1.5 0.6,0.6 1.1,0 0.2,0.7 0.8,-0.4 0.6,0.5 z"
         inkscape:connector-curvature="0"
         id="path332"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 65.799222,520.98247 -0.588135,0.56551 -0.823389,0.67861 0.352881,0.33931 0.588135,-0.56551 0.941016,0 0.352881,-1.01792 0.470508,-0.33931 0,-0.33931 -0.470508,0 -0.823389,0.67862 z"
       inkscape:connector-curvature="0"
       id="path340"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g342"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 302.1,458.8 -0.5,0.5 -0.7,0.6 0.3,0.3 0.5,-0.5 0.8,0 0.3,-0.9 0.4,-0.3 0,-0.3 -0.4,0 -0.7,0.6 z"
         inkscape:connector-curvature="0"
         id="path344"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 33.687049,532.51892 -0.470508,0 0,-0.56551 0,0.56551 0,0.22621 0.470508,-0.22621 z"
       inkscape:connector-curvature="0"
       id="path364"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g366"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 274.8,469 -0.4,0 0,-0.5 0,0.5 0,0.2 0.4,-0.2 z"
         inkscape:connector-curvature="0"
         id="path368"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 39.215518,539.64438 -0.470508,0.22621 -0.470508,0 0.941016,0 0,-0.22621 z"
       inkscape:connector-curvature="0"
       id="path370"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g372"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 279.5,475.3 -0.4,0.2 -0.4,0 0.8,0 0,-0.2 z"
         inkscape:connector-curvature="0"
         id="path374"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 63.91719,550.72843 -0.588135,1.35723 -1.17627,1.01792 1.764405,0.79172 -0.588135,1.35723 -0.705762,1.24413 2.705421,1.01792 1.646778,1.47033 0.470508,2.14895 2.940676,0.90482 0.941016,0.33931 5.057961,-0.56552 1.529151,-3.84548 1.646778,-1.47033 0.705762,-2.37516 -1.646778,0.67862 -0.705762,-0.22621 1.17627,-2.03584 -0.470508,-2.14895 -1.411524,0 -0.823389,-0.67861 0,-0.22621 -3.646437,0.67862 0,0.56551 -7.410502,-0.33931 -0.705762,0.33931 -0.705762,0 z"
       inkscape:connector-curvature="0"
       id="BY"
       style="fill:#b2a1c7;fill-opacity:1;fill-rule:evenodd;stroke:none"
       onmouseover="mouseoverpays(evt,this)"
       onmouseout="mouseoutpays(this)"
       onclick="mouseclickpays(evt,this)" /><g
       id="g390"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 300.5,485.1 -0.5,1.2 -1,0.9 1.5,0.7 -0.5,1.2 -0.6,1.1 2.3,0.9 1.4,1.3 0.4,1.9 2.5,0.8 0.8,0.3 4.3,-0.5 1.3,-3.4 1.4,-1.3 0.6,-2.1 -1.4,0.6 -0.6,-0.2 1,-1.8 -0.4,-1.9 -1.2,0 -0.7,-0.6 0,-0.2 -3.1,0.6 0,0.5 -6.3,-0.3 -0.6,0.3 -0.6,0 z"
         inkscape:connector-curvature="0"
         id="path392"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 57.094824,562.9435 0.705762,2.82756 1.999659,1.47033 1.646778,-2.14894 0.705762,0 0.705762,0.45241 0,1.69653 1.646778,0 2.587794,-0.45241 1.999659,0.67862 1.411525,-0.22621 1.646778,-1.69653 -0.705762,-3.16687 -0.941016,-0.45241 -2.940676,-0.90482 -0.823389,0.67861 -0.823389,0.22621 -1.17627,0.79171 -2.587794,0.67862 -1.999659,0 -3.058302,-0.45241 z"
       inkscape:connector-curvature="0"
       id="path406"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><path
       d="m 62.74092,556.38355 -1.17627,1.92275 -6.234232,0 1.176271,0.67861 -1.176271,-0.45241 0.470509,0.67862 2.35254,0 -1.17627,3.84548 3.175929,0.33931 1.882032,0 2.705421,-0.56551 1.17627,-0.90482 0.705762,-0.22621 0.941016,-0.67861 -0.470508,-2.03585 -1.764405,-1.47033 -2.587794,-1.13103 z"
       inkscape:connector-curvature="0"
       id="path412"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g414"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 299.5,490.1 -1,1.7 -5.3,0 1,0.6 -1,-0.4 0.4,0.6 2,0 -1,3.4 2.7,0.3 1.6,0 2.3,-0.5 1,-0.8 0.6,-0.2 0.8,-0.6 -0.4,-1.8 -1.5,-1.3 -2.2,-1 z"
         inkscape:connector-curvature="0"
         id="path416"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 70.386676,543.03746 2.35254,-0.56552 1.293897,0 1.17627,-1.80964 0.235254,-0.79171 1.764405,-1.13103 -2.470167,-0.2262 -0.705762,-0.56552 -0.823389,-1.13102 -0.470508,3.39307 -0.117627,0 -2.234913,2.82757 z"
       inkscape:connector-curvature="0"
       id="path418"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g420"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 306,478.3 2,-0.5 1.1,0 1,-1.6 0.2,-0.7 1.5,-1 -2.1,-0.2 -0.6,-0.5 -0.7,-1 -0.4,3 -0.1,0 -1.9,2.5 z"
         inkscape:connector-curvature="0"
         id="path422"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 81.500223,543.56623 -0.942948,-2.15407 0,0 0,0 0,0 0,0 -0.05467,-2.22019 -0.941016,0.67862 0,0 0.941016,-0.67862 0,0 1e-5,0 0.997608,4.37426 -0.942942,-2.15407 0,0 0.565798,5.43629 -0.497098,-1.74606 0.497098,1.74606 -0.497098,-1.74606 -0.06869,-3.69023 -0.28993,-2.7857 -0.470508,0.56551 0.470508,0 0.235254,0 -0.705762,1.01792 0,-0.56551 0,0.56551 0,-0.56551 2.667968,1.76778 -5.843898,-3.46432 -0.705762,-1.13102 -1.882032,-0.56551 3.381836,2.94836 -1.4758,1.39325 0.681758,-2.64508 0,0 -0.681758,2.64508 0,0 0,0 -1.139967,2.47349 -4.412506,-0.029 -1.411524,-0.45241 -1.882032,-0.56552 -3.646438,0.56552 -0.705762,0 -1.411524,0.56551 0.705762,1.92274 0.235254,0.22621 -0.235254,1.13102 2.705422,3.16687 -0.82339,1.24413 0.82339,0 0.588135,-0.45241 7.410501,0.45241 0,-0.67861 3.646437,-0.56552 0,0.11311 4.303769,0.7133 -1.514525,-0.0886 1.948166,-3.48678 -0.497085,-1.74606 0,0 0,0 0.874229,-1.53616 z"
       inkscape:connector-curvature="0"
       id="path430"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none"
       sodipodi:nodetypes="ccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc" /><g
       id="g432"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 314,475.8 0.6,-0.9 0,0 -0.2,0 0,0 0.24648,1.96299 L 314.6,474.9 l -0.6,0 0,0 0.6,0 -0.2,-0.5 0,0 0.2,0.5 0,0 -0.6,0.4 0,-0.4 0,0.4 0,0 0.4,-0.9 0,0 -0.4,0.5 0.4,0 0.2,0 0,0 -0.6,0.4 0,0 0.6,-0.4 -2.2,0.4 -1.1,-1.5 -0.6,-1 -1.6,-0.5 -0.6,0.5 0,0 0,0 0,0 0,0 -0.17129,3.00286 -1.40876,0.8381 L 306,478.3 l 0,0 -1.2,-0.4 -1.6,-0.5 -3.1,0.5 -0.6,0 -1.2,0.5 0.6,1.7 0.2,0.2 -0.2,1 2.3,2.8 -0.7,1.1 0.7,0 0.5,-0.4 6.3,0.4 0,-0.6 3.1,-0.5 0,0.1 0.6,0.6 3.38005,0.39714 -0.11505,-2.68571 0.2849,-2.65047 -0.60341,-2.99797 0,0 0,0 L 314.6,474.9 z"
         inkscape:connector-curvature="0"
         id="path434"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
         sodipodi:nodetypes="ccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc" /></g><path
       d="m 45.096869,538.85266 1.999659,0 2.234913,0.33931 0.705762,0 0,0.45241 0.705762,0.22621 0,-0.67862 0.588135,-0.33931 0,-0.67861 -1.293897,-0.67862 -0.705762,-0.67861 -1.764405,0.33931 -1.17627,-0.33931 -0.235254,0.67861 -1.293897,1.13103 0.235254,0.2262 z"
       inkscape:connector-curvature="0"
       id="path436"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g438"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 284.5,474.6 1.7,0 1.9,0.3 0.6,0 0,0.4 0.6,0.2 0,-0.6 0.5,-0.3 0,-0.6 -1.1,-0.6 -0.6,-0.6 -1.5,0.3 -1,-0.3 -0.2,0.6 -1.1,1 0.2,0.2 z"
         inkscape:connector-curvature="0"
         id="path440"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 51.095846,538.17405 0.235254,0 1.293897,-0.56551 1.646778,-0.45241 0.235254,0.45241 0.470508,0.3393 0.235254,-1.01792 0.588136,-1.47033 -0.82339,0 -2.35254,0 -2.35254,0 0.823389,-1.92274 1.999659,-1.58344 0,-1.13102 -1.293897,1.13102 -1.17627,0.1131 -0.705762,-0.1131 -1.882032,1.58344 1.17627,0.2262 -1.646778,1.13103 0,0.56551 -1.293897,0.79172 -0.588135,-1.01793 -0.823389,1.35723 0.235254,0.33931 0.941016,-0.33931 0.235254,0.33931 1.293897,0.22621 1.646778,-0.22621 0.705762,0.67862 1.17627,0.56551 z"
       inkscape:connector-curvature="0"
       id="path442"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g444"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 289.6,474 0.2,0 1.1,-0.5 1.4,-0.4 0.2,0.4 0.4,0.3 0.2,-0.9 0.5,-1.3 -0.7,0 -2,0 -2,0 0.7,-1.7 1.7,-1.4 0,-1 -1.1,1 -1,0.1 -0.6,-0.1 -1.6,1.4 1,0.2 -1.4,1 0,0.5 -1.1,0.7 -0.5,-0.9 -0.7,1.2 0.2,0.3 0.8,-0.3 0.2,0.3 1.1,0.2 1.4,-0.2 0.6,0.6 1,0.5 z"
         inkscape:connector-curvature="0"
         id="path446"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 55.330418,530.03067 -1.411524,0.33931 -1.764405,0.45241 0,0.2262 1.058643,-0.2262 0,1.13102 -1.999659,1.69654 -0.941016,1.92274 2.35254,0 2.587794,0 0.705763,0 1.17627,0 -0.470508,-1.24413 0.470508,-1.13102 0,-1.13103 -1.17627,-1.01792 -0.588136,-1.01792 z"
       inkscape:connector-curvature="0"
       id="path448"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g450"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 293.2,466.8 -1.2,0.3 -1.5,0.4 0,0.2 0.9,-0.2 0,1 -1.7,1.5 -0.8,1.7 2,0 2.2,0 0.6,0 1,0 -0.4,-1.1 0.4,-1 0,-1 -1,-0.9 -0.5,-0.9 z"
         inkscape:connector-curvature="0"
         id="path452"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 55.095164,537.94784 1.411525,0 0.470508,0.22621 1.529151,0 0.470508,-0.56551 0,-0.67862 1.529151,-0.67861 0,-0.79172 -0.823389,0 -1.17627,0.45241 -1.529151,-0.45241 -1.17627,0 -0.588136,1.47033 -0.117627,1.01792 z"
       inkscape:connector-curvature="0"
       id="path454"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g456"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 293,473.8 1.2,0 0.4,0.2 1.3,0 0.4,-0.5 0,-0.6 1.3,-0.6 0,-0.7 -0.7,0 -1,0.4 -1.3,-0.4 -1,0 -0.5,1.3 -0.1,0.9 z"
         inkscape:connector-curvature="0"
         id="path458"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 60.270753,535.45959 0,-0.22621 0.941016,-0.45241 0.705762,-0.45241 0.235254,0.45241 0.470508,0 -0.470508,-0.45241 0.470508,-0.56551 0,-1.24413 0.588135,-0.67861 0.235254,-0.33931 -0.235254,-0.45241 -0.588135,-0.2262 0,-0.45241 0.588135,-0.33931 -0.588135,-0.33931 -1.882032,-0.33931 0.705762,0.67862 -0.705762,1.01792 -1.17627,0 -0.352881,-0.2262 -1.17627,1.01792 -1.17627,0.2262 0,1.13103 -0.470508,1.01792 0.470508,1.24413 1.411524,0.45241 1.293897,-0.45241 0.705762,0 z"
       inkscape:connector-curvature="0"
       id="path6618"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g462"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 297.4,471.6 0,-0.2 0.8,-0.4 0.6,-0.4 0.2,0.4 0.4,0 -0.4,-0.4 0.4,-0.5 0,-1.1 0.5,-0.6 0.2,-0.3 -0.2,-0.4 -0.5,-0.2 0,-0.4 0.5,-0.3 -0.5,-0.3 -1.6,-0.3 0.6,0.6 -0.6,0.9 -1,0 -0.3,-0.2 -1,0.9 -1,0.2 0,1 -0.4,0.9 0.4,1.1 1.2,0.4 1.1,-0.4 0.6,0 z"
         inkscape:connector-curvature="0"
         id="RS"
         style="fill:#ff00ff;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none"
         onclick="mouseclickpays(evt,this)"
         onmouseout="mouseoutpays(this)"
         onmouseover="mouseoverpays(evt,this)" /></g><path
       d="m 57.682959,530.36998 -0.705762,-0.67862 -0.705762,-1.01792 -1.176271,1.24413 0.117627,0 0.588136,1.01792 1.17627,1.01792 1.17627,-0.1131 1.17627,-1.13103 -0.470508,-0.3393 -1.17627,0 z"
       inkscape:connector-curvature="0"
       id="path466"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g468"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 295.2,467.1 -0.6,-0.6 -0.6,-0.9 -1,1.1 0.1,0 0.5,0.9 1,0.9 1,-0.1 1,-1 -0.4,-0.3 -1,0 z"
         inkscape:connector-curvature="0"
         id="path470"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 60.858888,529.46516 -1.411524,-1.13103 0,0.45241 0,0.67862 -1.646778,1.01792 1.17627,0 0.470508,0.33931 0.235254,0.3393 1.17627,0 0.705762,-1.13102 -0.705762,-0.56551 z"
       inkscape:connector-curvature="0"
       id="path472"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g474"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 297.9,466.3 -1.2,-1 0,0.4 0,0.6 -1.4,0.9 1,0 0.4,0.3 0.2,0.3 1,0 0.6,-1 -0.6,-0.5 z"

         inkscape:connector-curvature="0"
         id="path476"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 62.623293,529.80446 1.293897,-1.13102 0,-1.01792 -0.705762,-0.67862 -1.058643,0.45241 -0.235254,-0.79172 -1.17627,0 -0.705762,-0.67861 -0.470508,1.01792 -0.235254,0.67862 0,0.67861 1.411524,1.01792 1.882032,0.45241 z"
       inkscape:connector-curvature="0"
       id="path478"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g480"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 299.4,466.6 1.1,-1 0,-0.9 -0.6,-0.6 -0.9,0.4 -0.2,-0.7 -1,0 -0.6,-0.6 -0.4,0.9 -0.2,0.6 0,0.6 1.2,0.9 1.6,0.4 z"
         inkscape:connector-curvature="0"
         id="path482"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 54.271775,546.76984 0.941016,-0.79172 1.058644,0.45241 0.235254,-0.67861 0.470508,-0.45241 1.17627,0.45241 0.705762,0 0.470508,0.2262 1.058643,-0.2262 1.17627,-0.45241 0.470508,-0.22621 -0.823389,-1.92274 -0.823389,0 -0.235254,0.45241 -1.293897,0 -0.470508,0 -1.411524,-0.56551 -0.470508,0 -1.411525,-0.67862 -1.293897,-0.90482 -1.646778,0.45241 -0.235254,0.45241 -0.705762,0.67862 0,1.01792 3.058302,2.71446 z"
       inkscape:connector-curvature="0"
       id="path484"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><g
       id="g486"
       transform="matrix(1.1762701,0,0,1.1310251,-289.55197,2.068141)"><path
         d="m 292.3,481.6 0.8,-0.7 0.9,0.4 0.2,-0.6 0.4,-0.4 1,0.4 0.6,0 0.4,0.2 0.9,-0.2 1,-0.4 0.4,-0.2 -0.7,-1.7 -0.7,0 -0.2,0.4 -1.1,0 -0.4,0 -1.2,-0.5 -0.4,0 -1.2,-0.6 -1.1,-0.8 -1.4,0.4 -0.2,0.4 -0.6,0.6 0,0.9 2.6,2.4 z"
         inkscape:connector-curvature="0"
         id="path488"
         style="fill:none;stroke:#b6dde8;stroke-width:0.25;stroke-linecap:butt;stroke-linejoin:round;stroke-miterlimit:10;stroke-opacity:1;stroke-dasharray:none" /></g><path
       d="m 46.390766,549.5974 0.705762,0 0.470508,0 0.823389,0 0,-0.45241 1.17627,-0.45241 0.470508,0 0,-0.2262 0,-0.45241 0.705762,-0.33931 0.470508,0 0.823389,0.33931 0.705762,-0.33931 0,-0.22621 1.764405,-0.45241 0,-0.2262 -3.293556,-2.82756 -1.882032,0.3393 -1.293897,0.33931 -0.941016,-1.01792 -0.705762,0 -1.17627,0.33931 0,0.3393 -0.823389,0.79172 -0.117627,0.22621 -1.293897,0.67861 0,0.79172 -0.941016,0.2262 0,0.67862 0,0.33931 0.470508,-0.33931 3.646437,1.47033 0.235254,0.45241 z"
       inkscape:connector-curvature="0"
       id="path490"
       style="fill:#ffffff;fill-opacity:1;fill-rule:evenodd;stroke:none" /><image
       y="-569.78748"
       x="74.572426"
       id="b_annuler"
       xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABHNCSVQICAgIfAhkiAAAA2ZJREFU OI2FlMGrVVUUxn9rn3vOuV2lUJ/el2hk+XxF5KSiiQlFTqIaCBXWHxAZETRr2LxJswiCBkWzojDo hdCobGi+xHqIgRWa+l5P773vnnP22etrcJ4+LK092ZPNb33rW2t/Jon/OopToYAMTAnIsKK0273v 3RJSjaXzi2jpe+zCEtTXMEW0aRvMzqPZveLeRwlbd/4LbP9U6L8viYUPsZMLkKYQHMoS6xeQ91Fe Ql6imd3w+BGYP0DoFTfANylMX3wgPn8PmyyDR6zXgzyDooYsQNHHyhwh7NoyGi/D+R/xp48q5J0N N4D+3THp/bfJ2imWB+gFSA6NQW1gAfoR1T3IMgywq5fQ4leoV8KhoxsKffWK2o/epTcadYoQSo4y ETIgGZhBclIdUFmQA2SGbXb4aQGfP6Bwz37rAejrT2HxB7xnZAjcscw4NxK1i/nNgSwYROdc5eRF w55tDhIICBmcOo523KcAEI99gqUWXMiBdYuji8VR4tTVRBXF4mrD6b8isWphWsFkDcYTmIzg7Ak0 Xula1q9LyCEIYL2qG3ObAknwyyRRCS7Xzt5BYK4PNAksQlZhZQHLf8BdQwKAxmuoFZ4cpQ6GiwzY tykwLAPnp872MjA3yDCpG1iboI4wraEaY3lpHdACcqEWcOEucIitOD1KXKqd3WXgQuWcGSeiA3Jo HWILaxW2VgF0CsOWGbylUxkdtQ4ulsbOzyNnTz/w2J2B+UFgac05O0mdLa1DTFBFNJ52axObRszv hysX8dRCLUzggsLFw4PAvkEgBx64IxCA0qwDyiEaKMLw/g6YF4XZU8+rOnkCW13pZiLHkpgrAuRC rSMzsgAPDkK3Aq2QAUrIHR46iF1vOXviGcKOXXivxFvDk/BGVFOnmTipclLjpOh47XjjqBVEoUb4 YAY7eHjDQ7t7l+VHXsO2bIUiR27IhbvjrZPqdVDdeeyxK+CN462wl95C+x7ZAAJkz71C8ewRNNyJ F33kBmljUCl24FSt343TutCLbxIOv0oousS5Kb40uqr4zWekLz/Gz53BV1ewtgV3QhBmBtb9uLB3 jt7r7xCePHwDdss8BNDlP5VOHCd+ewwu/oaPVmBlBWJFNjskHHqB/OU3sJnh/wfsTeC1ibS6jFLC tg8J/cFto//6+RsrBBAEjTKe+QAAAABJRU5ErkJggg== "
       height="5.0504193"
       width="5.7198009"
       transform="scale(1,-1)"
       onclick="div_europe.style.display='none'" /></g></svg>
  
</div>
<div id='div_debug' style="display:none"></div>
<div id='div_detailpays' class="cadre_rel_int" style="display:none" onmouseover="this.style.display='block'; " onmouseout="this.style.display='none';mouseoutdiv_detailpays()"></div>
<div id='div_detailpartenariat' class="cadre_rel_int" style="display:none; overflow: auto;" onmouseover="mouseoverdetailpartenariat()" onmouseout="mouseoutdetailpartenariat()"></div>
<div id='div_detailindividu' class="cadre_rel_int" style="display:none; overflow: auto; width:250px;height:100px"  onmouseover="mouseoverdiv_detailindividu()" onmouseout="mouseoutdiv_detailindividu()"></div> <!-- -->
<div id='div_detailpartenariat_un_type' class="cadre_rel_int" style="display:none; overflow: auto;"></div> <!--  onmouseover="mouseoverdiv_detailindividu()" onmouseout="mouseoutdiv_detailindividu()" -->
  <!-- liste des rel_int avec scroll vertical -->

  <div id="div_organismes" class="div_gauche" style="width:278px; height:400px; overflow: auto; overflow-x: hidden; border:1px solid #CCC;">
    <?php
	$codecontinent_prec='';
	$codepays_prec='';
	foreach($tab_organisme as $un_codeorganisme=>$un_tab_organisme)
	{ if($un_tab_organisme['codecontinent']!=$codecontinent_prec)
		{ ?><div id="continent<?php echo $un_tab_organisme['codecontinent'] ?>"  class="titre_continent_partenariat" style="text-align:center">
      <?php echo strtoupper($un_tab_organisme['libcontinent_'.strtolower($codelangue)]);
			$codecontinent_prec=$un_tab_organisme['codecontinent']?>
      </div>
    <?php
		}
		if($un_tab_organisme['codepays']!=$codepays_prec)
		{ ?><div id="pays<?php echo $un_tab_organisme['codepays'] ?>"  class="titre_pays_partenariat" style="text-align:center">
      <?php echo $un_tab_organisme['libpays_'.strtolower($codelangue)];
			$codepays_prec=$un_tab_organisme['codepays']?>
      </div>
    <?php
		}
		?> 
    <div id="div_listetousorganismes<?php echo $un_codeorganisme ?>"
     onmouseover="mouseoverorganismelistetousorganismes(event,this,'<?php echo $un_codeorganisme ?>')"
     onmouseout="mouseoutorganisme()"
     onclick="mouseclickorganisme(event,'<?php echo $un_codeorganisme ?>','listetousorganismes')";
     >
      <img src="images/b_fleche_droite.png" width="10"/>
      <?php echo substr($un_tab_organisme['liborganisme_long'],0,30); ?>
      </div>
    <?php
	}?>
  </div>
<div class="div_gauche" style="width:980px"><br /><br /><br /></div>
