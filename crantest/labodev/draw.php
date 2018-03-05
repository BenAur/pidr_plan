<?php
header("Content-type: image/png");
$im = @imagecreate(203, 203)
    or die("Impossible d'initialiser la bibliotheque GD");
$color = imagecolorallocate($im, 255, 255, 255);
imagefilledrectangle  ( $im, 0, 0, 203, 203, $color );
for($i=0;$i<16;$i++)
{ if($i<=9)
	{ $tab[$i]=$i;
	}
	else
	{ $tab[$i]=-1;
	}
}
shuffle($tab);
$font = 'c:/windows/fonts/arial.ttf';
$bbox=imagettfbbox ( 18, 0, $font, 1 );
/* 0 	Coin inferieur gauche, abscisse
1 	Coin inferieur gauche, ordonnee
2 	Coin inferieur droit, abscisse
3 	Coin inferieur droit, ordonnee
4 	Coin superieur droit, abscisse
5 	Coin superieur droit, ordonnee
6 	Coin superieur gauche, abscisse
7 	Coin superieur gauche, ordonnee */
$largeur=$bbox[2]-$bbox[0];
$hauteur=$bbox[1]-$bbox[7];

for($l=0;$l<=3;$l++)
{ for($c=0;$c<=3;$c++)
	{ $color = imagecolorallocate($im, 0, 0, 255);
		$y=$c*50;
		imagefilledrectangle  ( $im, $l*50+2, $c*50+2, $l*50+50, $c*50+50, $color );
		$color = imagecolorallocate($im, 255, 255, 255);
		if($tab[4*$l+$c]!=-1)
		{ //imagestring($im, 5, $l*50+50/2, $c*50+50/2, $tab[4*$l+$c], $color);
			$bbox=imagettfbbox ( 18, 0, $font, $tab[4*$l+$c] );
			$largeur=$bbox[2]-$bbox[0];
			$hauteur=$bbox[1]-$bbox[7];
			imagettftext($im, 18, 0, $l*50+(50-$largeur)/2, $c*50+(50+$hauteur)/2, $color, $font, $tab[4*$l+$c]);
		}
	}
}

imagepng($im);
imagedestroy($im);
 /* <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<title>Untitled Document</title>
<script>
var dX, dY;
function f(objet,event)
{	event.returnValue = false;
	if( event.preventDefault ) event.preventDefault();	//Coordonnees de la souris
  var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
  var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonnees de l'element
  var eX = 0;
  var eY = 0;
  var element = objet;
	//i=0;
  do
  { //i++;
    eX += element.offsetLeft;
    eY += element.offsetTop;
    element = element.offsetParent;
  } while( element && element.style.position != 'absolute');

	//Calcul du decalage
  dX = x - eX;
  dY = y - eY;
}
</script>
</head>

<body>
</body>
</html> */
?>