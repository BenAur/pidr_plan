<?php require_once('_const_fonc.php'); ?>
<?php
foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	}/**/
$form="auto-suggest";
$query_rs= "SELECT codepart,liblongpart FROM cont_part ORDER BY liblongpart";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_part[$row_rs['codepart']]=$row_rs['liblongpart'];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//Dtd HTML 4.01 Transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
<title>auto-suggest</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $charset ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
window.onload = function()
{ var motsClefs=new Array();
	motsClefs=[
	<?php
	foreach($tab_part as $codepart=>$valchamp)
	{?>
		new Array("<?php echo $codepart ?>","<?php echo str_replace(array(chr(10),chr(13),'"'),array('\n','\r','\"'),str_replace(chr(92),chr(92).chr(92),$valchamp));?>"),
	<?php 
	}
	?>
						];
	var frm=document.forms["<?php echo $form ?>"];
  var input = frm.search;
  var list = document.createElement("ul");
	list.className = "suggestions";
	list.style.display = "none";
	frm.appendChild(list);

	input.onkeyup = function()
	{ var txt = this.value;
		if(!txt)
		{ list.style.display = "none";
			return;
		}
		var suggestions = 0;
		var frag = document.createDocumentFragment();
		for(var i = 0, c = motsClefs.length; i < c; i++)
		{ if(new RegExp(txt,"i").test(motsClefs[i][1]))
			{ var word = document.createElement("li");
				frag.appendChild(word);
				word.innerHTML = motsClefs[i][1].replace(new RegExp("^("+txt+")","i"),"<strong>$1</strong>");
				word.mot = motsClefs[i][1];
				word.onmousedown = function()
				{ input.focus();
					input.value = this.mot;
					list.style.display = "none";
					return false;
				};                
				suggestions++;
			}
		}

		if(suggestions)
		{ list.innerHTML = "";
			list.appendChild(frag);
			list.style.display = "block";
		}
		else 
		{ list.style.display = "none";            
		}
	};

	input.onblur = function()
	{ list.style.display = "none";
		if(this.value=="") 
		{ this.value = "Rechercher...";
		}
		else
		{ txt=this.value;
			for(var i = 0, c = motsClefs.length; i < c; i++)
			{ if(new RegExp(txt,"i").test(motsClefs[i][1]))
				{ this.value=motsClefs[i][0];
				}
			}
		}
	};
};
</script>
<style>
html, body {background:url(images/noise.png) left top repeat}
#auto-suggest {width:358px;margin:0 auto}
#auto-suggest .search {
    width:322px;height:33px;margin:4px;padding:0 13px;border:1px solid #cdcdcd;
    color:#ccc;font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;font-size:14px;font-weight:bold;    
    -moz-border-radius:2px;
    -webkit-border-radius:2px;
    border-radius:2px;
    -moz-box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #f1f1f1,-4px 4px 0 #f1f1f1,-4px -4px 0 #f1f1f1,4px -4px 0 #f1f1f1;
    -webkit-box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #f1f1f1,-4px 4px 0 #f1f1f1,-4px -4px 0 #f1f1f1,4px -4px 0 #f1f1f1;
    box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #f1f1f1,-4px 4px 0 #f1f1f1,-4px -4px 0 #f1f1f1,4px -4px 0 #f1f1f1
}

#auto-suggest .search:focus {
    color:#555;border-color:#c8c8c8;    
    -moz-box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #ececec,-4px 4px 0 #ececec,-4px -4px 0 #ececec,4px -4px 0 #ececec;
    -webkit-box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #ececec,-4px 4px 0 #ececec,-4px -4px 0 #ececec,4px -4px 0 #ececec;
    box-shadow:inset 0 1px 4px rgba(0,0,0,0.15), 4px 4px 0 #ececec,-4px 4px 0 #ececec,-4px -4px 0 #ececec,4px -4px 0 #ececec
}

#auto-suggest .suggestions {
	width:342px;
	position:absolute;
	margin:-6px auto 0;
	padding:0;
	list-style-type:none;
	border:1px solid #d5d4d4;
	background:#fff;
	font-family:"Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size:13px;
	color:#555;
	-moz-border-radius:2px;
	-webkit-border-radius:2px;
	border-radius:2px;
	-moz-box-shadow:0 1px 3px rgba(0,0,0,0.1);
	-webkit-box-shadow:0 1px 3px rgba(0,0,0,0.1);
	box-shadow:0 1px 3px rgba(0,0,0,0.1);
	left: auto;
}

#auto-suggest .suggestions li {height:25px;padding:0 10px;line-height:25px;cursor:pointer;border-top:1px solid #f5f5f5}
#auto-suggest .suggestions li:hover {background:url(images/tick.png) no-repeat #fffac2;background-position:320px center;border-top-color:#fffac2}
#auto-suggest .suggestions li:first-child {border:none;}
</style>
</head>
<body>
<form id="<?php echo $form ?>" name="<?php echo $form ?>" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
  <input type="text" class="search" name="search" value="Rechercher..." onfocus="if(this.value=='Rechercher...')this.value=''" onblur="if(this.value=='')this.value='Rechercher...'" autocomplete="off"/>
	<input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer">
</form>
</body>
</html>
    <?php
if(isset($rs))mysql_free_result($rs);
if(isset($rs_fields))mysql_free_result($rs_fields);
