<?php 
$nbvignette=14;
$largeur_vignette=20;
$rep_source='i';
$rep_resultat='i_res';
$img_source=null;
//commandes
$tab_vignette_cm['c']=array(array('o'),array('m','z'),array('z'),array('d','z'),array('c','z'),array('q','r','z'),array('k','j','z'),array('v','y','yv','yyv','yyyv','yyyyv','yyyyy','b','yb','yyb','yyyb','yyyyb',''),array('i','z',''));
//missions
$tab_vignette_cm['m']=array(array('p'),array('m','z'),array('a','z'),array('d','z'),array('c','z'),array('q','r','z'),array('z'),array('u','w',''),array('z'));

// copie de toutes les vignettes de base et rend transparent le noir de certaines vignettes ozzzczyyyyb
foreach($tab_vignette_cm as $tab_vignette)
{	for($i=0;$i<count($tab_vignette);$i++)
	{	$tab=$tab_vignette[$i];
		foreach($tab as $nom_une_vignette)
		{ if($nom_une_vignette!='')
			{	echo 'nom_une_vignette: '.$nom_une_vignette;
				$img_source=imagecreatefrompng($rep_source.'/'.$nom_une_vignette.'.png');
				// pour eviter le fond noir de certaines vignettes cree par imagecreatefrompng
				if(in_array($nom_une_vignette,array('o','d','r','u','v','w','k','j','yv','yyv','yyyv','yyyyv','yyyyy','yb','yyb','yyyb','yyyyb')))
				{ //$transparent= imagecolorallocate ( $img_source, 0, 0,0 );
					imagecolortransparent ($img_source,0);
				}
				echo ' ecriture de '.$rep_resultat.'/'.$nom_une_vignette.'.png<br>';
				imagepng($img_source,$rep_resultat.'/'.$nom_une_vignette.'.png') or die("Impossible de transformer ".$img_source." en ".$rep_resultat.'/'.$nom_une_vignette);
			}
		}
	}
}
$rep_source='i_res';//la source=la destination : elle contient les vignettes de base sans fond noir 
$img_source=null;
// image resultat : rectangle transparent
$img_resultat = @imagecreatetruecolor($nbvignette*$largeur_vignette, 16) or die("Impossible de créer un flux d'image GD");
$transparent= imagecolorallocate ( $img_resultat, 255, 255, 255 );
imagecolortransparent ($img_resultat,$transparent);
imagefilledrectangle ($img_resultat, 0, 0, $nbvignette*$largeur_vignette, 16, $transparent );//
$nom_vignette_resultat='';

$tab=array();
function combine($nom_vignette_resultat,$profondeur)
{ global $tab_vignette;
	global $img_resultat;
	global $img_source;
	global $nbvignette;
	global $largeur_vignette;
	global $rep_source;
	global $rep_resultat;
	global $transparent;
	//global $nom_vignette_resultat;
	if($profondeur>=count($tab_vignette))
	{ echo $rep_resultat.'/'.$nom_vignette_resultat.'.png'.'<br>';
		imagepng($img_resultat,$rep_resultat.'/'.$nom_vignette_resultat.'.png') or die("Impossible d'ecrire l'image");
		return;
	}
	else
	{ $tab=$tab_vignette[$profondeur];
		foreach($tab as $nom_une_vignette)
		{ if($nom_une_vignette!='')
			{ $nom_vignette_resultat.=$nom_une_vignette;
				//$img_source=imagecolortransparent (imagecreatefrompng('i/'.$nom_une_vignette.'.png'),$transparent);
				$img_source=imagecreatefrompng($rep_source.'/'.$nom_une_vignette.'.png') or die("Impossible de creer l'image");
				//
				$x_dest=$largeur_vignette*($profondeur+($profondeur!=8?0:5));//8=derniere vignette tient compte de l'image des 5 visas
				$largeur=($profondeur!=7?$largeur_vignette:6*$largeur_vignette);//7eme vignette=vignette des visas => largeur=5 pour visas +1
				echo $nom_une_vignette.' ('.$x_dest.' '.$largeur.')';
				// imagecopymerge ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h, int pct )
				imagecopymerge ( $img_resultat, $img_source,$x_dest , 0, 0, 0, $largeur, 16, 100 ) or die("Impossible de merger l'image");
				$profondeur++;
				combine($nom_vignette_resultat,$profondeur);
				$profondeur--;
				$x_dest=$largeur_vignette*($profondeur+($profondeur!=8?0:5));
				$largeur=($profondeur!=7?$largeur_vignette:7*$largeur_vignette);
				imagefilledrectangle ($img_resultat, $x_dest, 0, $x_dest+$largeur, 16, $transparent );
				/* $x_dest=20*($profondeur==6?($profondeur):$profondeur+5);
				$largeur=($profondeur==6?20:120);
				imagefilledrectangle ($img_resultat, $x_dest, 0, $largeur, 16, $transparent ); */
				$nom_vignette_resultat=substr($nom_vignette_resultat,0,strlen($nom_vignette_resultat)-strlen($nom_une_vignette));
			}
			else
			{ $profondeur++;
				combine($nom_vignette_resultat,$profondeur);
				$profondeur--;
			}
		}
	}
}
foreach($tab_vignette_cm as $tab_vignette)
{ combine('',0);
}

/*  header('Content-type: image/png');
imagepng($img_resultat); */
?>
