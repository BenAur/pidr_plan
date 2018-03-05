<?php require_once('_const_fonc.php'); ?>
<?php

 $param_login=isset($_GET['login'])?$_GET['login']:"";
  $param_passwd=isset($_GET['passwd'])?$_GET['passwd']:"";
  $param_reconnexion=isset($_GET['reconnexion'])?$_GET['reconnexion']:"faux";
if($_SERVER['REQUEST_METHOD']=='GET')
{ if($param_reconnexion=="stop")
	{ $_SESSION = array();
		if (isset($_COOKIE[session_name($GLOBALS['acronymelabo'])])) 
		{ setcookie(session_name($GLOBALS['acronymelabo']), '', time()-42000, '/');
		}
		session_destroy();
	}
  ?>
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <HTML>
  <head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<title>12+</title>
<link rel="icon" type="image/png" href="images/12plus.ico" >
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<style>
.table_fond_clair {
	border: 1pt solid #D5D5D5;
	-moz-border-radius-topleft: 0.4em;
	-moz-border-radius-topright: 0.4em;
	-moz-border-radius-bottomleft: 0.4em;
	-moz-border-radius-bottomright: 0.4em;
	border-color: #666;
	background-color:#FFFFFF;
}

</style>
</head>
    <?php 
    if($param_reconnexion=="vrai")
    {  $fond="fond_12plus_aurevoir.jpg"?>
    <?php
    }
    else if($param_reconnexion=="stop" || $param_reconnexion=="deconnexion")
    { $fond="fond_12plus_aurevoir.jpg"?>
    <?php
    }
		else
		{ $fond="fond_12plus_bonjour.jpg"?>
    <?php
		}
    ?>
<body>
<br>
<form name="formintranetpasswd" action="formintranetpasswd.php" method="post" target="_self">
<table align="center">
  <tr>
  	<td width="1024" height="768">
      <table border="0" align="center"  background="images/galerie/<?php echo $fond ?>" width="100%" height="100%">
        <tr>
          <td valign="top">
            <table width="100%" border="0" cellpadding="0" cellspacing="1">
              <tr>
                <td width="61" align="left"><img src="<?php echo $GLOBALS['logolabo'] ?>"></td>
                <td align="center">
                  <table border="1" align="center" cellpadding="10" class="table_fond_clair">
                    <tr>
                      <td align="center" nowrap><span class="bleugrascalibri11">Zone priv&eacute;e</span>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="left" valign="top">
            <table align="center" class="table_fond_clair">
            <?php 
            if($GLOBALS['siteouvert'])
            { 
              if($param_reconnexion!="")
              { ?>
              <tr>
                <td colspan="2" align="center">
                <?php 
                if($param_reconnexion=="vrai")
                { ?>
                  <span class="rougegrascalibri11">Erreur login/password</span>
                <?php
                }
                else if($param_reconnexion=="stop" || $param_reconnexion=="deconnexion")
                { ?>
                  <span class="vertgrascalibri11">Au revoir et &agrave; bient&ocirc;t</span>
                <?php
                }
                ?>
                </td>
              </tr>
              <?php 
              }?>
              <tr>
                <td> 
                 <input type="hidden" name="reconnexion" value="<?php echo $param_reconnexion ?>" >
                </td>
              </tr>
              <tr>
                <td>
                  <span class="bleugrascalibri11">login :</span> 
                </td>
                <td>
                  <input name="login" type="text" id="login" class="noircalibri10" value="<?php echo $param_login ?>" size="30">
                </td>
              </tr>
              <tr>
                <td align="left"><span class="bleugrascalibri11">password : </span>
                </td>
                <td>
                  <input type="password" name="passwdlog" class="noircalibri10" size="30">
                </td>
              </tr>
             	<tr>
                <td align="right" colspan="2">
                  <input type="submit" class="noircalibri10" value="Connexion">
                  <input type="hidden" name="heure" value="<?php time()?>">
                </td>
            	</tr>
							<?php 
              } 
              else
              {?> 
              <tr>
                <td>
                <span class="bleugrascalibri11"><?php echo $GLOBALS['sitefermemotif'] ?></span>
                </td>
              </tr>
              <?php
              }?>
              <tr>
                <td align="center" colspan="2">
                  <span class="bleugrascalibri11">Contact : <A HREF="mailto:<?php echo $GLOBALS['webMaster']['email'] ?>"><?php echo $GLOBALS['webMaster']['nom'] ?></span></A>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
		</td>
	</tr>
</table>
</form>
<script language="JavaScript" type="text/javascript">
		document.getElementById("login").focus();
</script>

</body>
</html>
<?php
}
else//POST
{ var_dump($_POST);
if(isset($_POST["reconnexion"]))
	{ $param_reconnexion=$_POST["reconnexion"];
	}
	$OK=false;
  if(!isset($_POST['login']))
  { $rs_tracelogin=mysql_query("select login from tracelogin where sessionid='".session_id().
  								"' and heuredeconnexion IS NULL") or die(mysql_error()) or die(mysql_error());
	  if($row_rs_tracelogin = mysql_fetch_assoc($rs_tracelogin))
	  { $login=$row_rs_tracelogin["login"];
	    $rs_individu=mysql_query("select codeindividu from individu where login=".GetSQLValueString($login, "text")) or die(mysql_error());
	    if($row_rs_individu = mysql_fetch_assoc($rs_individu));
	    { $codeuser=$row_rs_individu["codeindividu"];
	    }
	    $OK=true;
	  }
	  mysql_free_result($rs_tracelogin);
	}
	else
	{ //Verification login/password pour acces zone privee
	  $rs_individu=mysql_query("select codeindividu,login,passwd from individu where login=".GetSQLValueString($_POST['login'], "text").
							   " and passwd=".GetSQLValueString(crypt($_POST['passwdlog']), "text")) or die(mysql_error());
		if($row_rs_individu = mysql_fetch_assoc($rs_individu))
	  { mysql_query("delete from tracelogin where sessionid='".session_id()."'") or die(mysql_error());
	    mysql_query("delete from tracelogin where login=".GetSQLValueString($_POST['login'], "text")."") or die(mysql_error());
			mysql_query( "insert into tracelogin (login,heureconnexion,numip,nommachine,sessionid) ".
									 "values (".GetSQLValueString($_POST['login'], "text").",'".date("F j, Y, g:i a")."','".$_SERVER["REMOTE_ADDR"]."','".
									 "','".session_id()."')") or die(mysql_error());
			$codeuser=$row_rs_individu["codeindividu"];
	    $OK=true;
    }
	}
	if($OK)
	{ $_SESSION['codeuser']=$codeuser;
	  http_redirect("menuprincipal.php");
  }
	else
	{ http_redirect("formintranetpasswdcrypt.php?reconnexion=vrai&login=".(isset($_POST['login'])?$_POST['login']:""));
  }
	if(isset($rs_individu))mysql_free_result($rs_individu);
}

?>





