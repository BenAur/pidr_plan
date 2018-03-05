<?php

mysql_query("update individu set num_bureau=".GetSQLValueString($_POST['num_bureau'], "text").", "
                                                . "num_cle=".GetSQLValueString($_POST['num_cle'], "text")." where nom=".GetSQLValueString($_POST['nom'], "text")) or die(mysql_error());

?>