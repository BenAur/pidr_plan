<?php
header('Content-type:text/html; charset=utf-8');
require_once('_const_fonc.php');

$query = "SELECT * "
  ." FROM  individu "
  ." WHERE num_bureau = " .GetSQLValueString($_POST["room_number"] , "text");

$result=mysql_query($query) or die(mysql_error()) ;

while ($row = mysql_fetch_assoc($result)) {
  /* Informations basiques */
  echo "<b>Nom :</b> ".$row['nom']."<br/>";
  echo "<b>Prenom :</b> ".$row['prenom']."<br/>";
  echo "<b>Tel :</b> ".$row['tel']."<br/>";
  echo "<b>Email :</b> ".$row['email']."<br/>";
  /* Sujet de these */
  $query2 = "SELECT * "
    ." FROM individuthese "
    ." WHERE codeindividu = ".$row['codeindividu'];
  $result2=mysql_query($query2) or die(mysql_error());
  if ($row2 = mysql_fetch_assoc($result2)){
    echo "---<br/>";
    echo "<b>Sujet de these :</b>" .$row2["titre_these"]."<br/>";
    
  }

  echo "================================<br/>";
}

?>
