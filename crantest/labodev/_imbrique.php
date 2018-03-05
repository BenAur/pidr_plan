<?php require_once('Connections/conn.php'); ?><?php
$maxRows_DetailRS1 = 10;
$pageNum_DetailRS1 = 0;
if (isset($_GET['pageNum_DetailRS1'])) {
  $pageNum_DetailRS1 = $_GET['pageNum_DetailRS1'];
}
$startRow_DetailRS1 = $pageNum_DetailRS1 * $maxRows_DetailRS1;

mysql_select_db($database_conn, $conn);
$recordID = $_GET['recordID'];
$query_DetailRS1 = "SELECT * FROM individu WHERE codeindividu = '$recordID'";
$query_limit_DetailRS1 = sprintf("%s LIMIT %d, %d", $query_DetailRS1, $startRow_DetailRS1, $maxRows_DetailRS1);
$DetailRS1 = mysql_query($query_limit_DetailRS1, $conn) or die(mysql_error());
$row_DetailRS1 = mysql_fetch_assoc($DetailRS1);

if (isset($_GET['totalRows_DetailRS1'])) {
  $totalRows_DetailRS1 = $_GET['totalRows_DetailRS1'];
} else {
  $all_DetailRS1 = mysql_query($query_DetailRS1);
  $totalRows_DetailRS1 = mysql_num_rows($all_DetailRS1);
}
$totalPages_DetailRS1 = ceil($totalRows_DetailRS1/$maxRows_DetailRS1)-1;
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
</head>

<body>
		
<table border="1" align="center">
  
  <tr>
    <td>codeindividu</td>
    <td><?php echo $row_DetailRS1['codeindividu']; ?> </td>
  </tr>
  <tr>
    <td>nom</td>
    <td><?php echo $row_DetailRS1['nom']; ?> </td>
  </tr>
  <tr>
    <td>prenom</td>
    <td><?php echo $row_DetailRS1['prenom']; ?> </td>
  </tr>
  <tr>
    <td>passwd</td>
    <td><?php echo $row_DetailRS1['passwd']; ?> </td>
  </tr>
  <tr>
    <td>fonction_fr</td>
    <td><?php echo $row_DetailRS1['fonction_fr']; ?> </td>
  </tr>
  <tr>
    <td>fonction_en</td>
    <td><?php echo $row_DetailRS1['fonction_en']; ?> </td>
  </tr>
  <tr>
    <td>titre_fr</td>
    <td><?php echo $row_DetailRS1['titre_fr']; ?> </td>
  </tr>
  <tr>
    <td>titre_en</td>
    <td><?php echo $row_DetailRS1['titre_en']; ?> </td>
  </tr>
  <tr>
    <td>codecat</td>
    <td><?php echo $row_DetailRS1['codecat']; ?> </td>
  </tr>
  <tr>
    <td>codeetab</td>
    <td><?php echo $row_DetailRS1['codeetab']; ?> </td>
  </tr>
  <tr>
    <td>ancientel</td>
    <td><?php echo $row_DetailRS1['ancientel']; ?> </td>
  </tr>
  <tr>
    <td>autretel</td>
    <td><?php echo $row_DetailRS1['autretel']; ?> </td>
  </tr>
  <tr>
    <td>fax</td>
    <td><?php echo $row_DetailRS1['fax']; ?> </td>
  </tr>
  <tr>
    <td>email</td>
    <td><?php echo $row_DetailRS1['email']; ?> </td>
  </tr>
  <tr>
    <td>lienpghttp</td>
    <td><?php echo $row_DetailRS1['lienpghttp']; ?> </td>
  </tr>
  <tr>
    <td>lieu</td>
    <td><?php echo $row_DetailRS1['lieu']; ?> </td>
  </tr>
  <tr>
    <td>codelieu</td>
    <td><?php echo $row_DetailRS1['codelieu']; ?> </td>
  </tr>
  <tr>
    <td>lienhttpenseigne</td>
    <td><?php echo $row_DetailRS1['lienhttpenseigne']; ?> </td>
  </tr>
  <tr>
    <td>telint</td>
    <td><?php echo $row_DetailRS1['telint']; ?> </td>
  </tr>
  <tr>
    <td>tel</td>
    <td><?php echo $row_DetailRS1['tel']; ?> </td>
  </tr>
  <tr>
    <td>hdr</td>
    <td><?php echo $row_DetailRS1['hdr']; ?> </td>
  </tr>
  
  
</table>


</body>
</html><?php
mysql_free_result($DetailRS1);
?>
