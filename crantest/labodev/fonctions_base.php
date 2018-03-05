<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function recup_nbchamps($bdd,$table)
		  {
 
			//récupère tous les champs de la table $table dans la base de données $bdd.
			$champs = mysql_list_fields($bdd,$table);
 
			// Enumère le nombre de champs de la table.
			$nb_champs = mysql_num_fields($champs);
 
			return $nb_champs;
 
		 }
		//fin de la fonction recup_nbchamps($bdd,$table)
 
		function recup_noms_champs($bdd,$table)
		{
 
		   //récupère tous les champs de la table $table dans la base de données $bdd.
		  $champs = mysql_list_fields($bdd,$table);
 
		   // Enumère le nombre de champs de la table.
		  $nb_champs = mysql_num_fields($champs);
 
			// rempli le tableau temporaire des noms de champs.
			for ($i = 0; $i < $nb_champs; $i++)
			{
				$tableau_noms_temp[$i] = mysql_field_name($champs, $i);
			}
 
		   $tableau_noms = array();
 
		  for ($i = 0; $i < $nb_champs; $i++)
		  {
			array_push($tableau_noms,$tableau_noms_temp[$i]);
		  }
		 return $tableau_noms;
 
		}
		//fin de la fonction recup_noms_champs($bdd,$table)
 
	 function recup_type_champs($bdd,$table)
	 {
 
			  //récupère tous les champs de la table $table dans la base de données $bdd.
			   $champs = mysql_list_fields($bdd,$table);
 
			  // Enumère le nombre de champs de la table.
			  $nb_champs = mysql_num_fields($champs);
 
			  // rempli le tableau temporaire des types de champs
			   for ($i = 0; $i < $nb_champs; $i++)
			   {
				  $tableau_types_temp[$i] = mysql_field_type($champs, $i);
			   }
 
			   $tableau_types = array();
 
			  for ($i = 0; $i < $nb_champs; $i++)
			   {
				  array_push($tableau_types,$tableau_types_temp[$i]);
			  }
			 return $tableau_types;
 
	 }
	   //fin de la fonction recup_type_champs($bdd,$table)
?>