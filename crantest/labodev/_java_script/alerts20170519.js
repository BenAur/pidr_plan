
function discard()
{
return (confirm("Confirmez-vous la suppression ?"))
}
function confirme(action,message)
{ return (confirm(message))
}

function controle_form_fiche_dossier_pers_choix_corps(formulaire)
{ var frm=document.forms[formulaire];
	var erreur="";
	var tab_date={"datedeb_sejour" : {"lib" : "Date de début de séjour","jj" : "","mm" : "","aaaa" : ""},
								"datefin_sejour" : {"lib" : "Date de fin de séjour","jj" : "","mm" : "","aaaa" : ""}}
	//if(frm.elements['codelieu'] && frm.elements['autrelieu'] && frm.elements['codelieu'].value!='' && frm.elements['autrelieu'].value!='') {erreur+="Pas plus d'un lieu de travail pour l'activité de recherche"+"\n";}
	if(frm.elements['codelieu'] && frm.elements['codelieu'].value==''){ erreur+="Lieu de travail pour l'activité de recherche obligatoire"+"\n";}
	
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ if(champ_date=='date_naiss')
				{ frm.elements[champ_date+'_aaaa'].value='19'+frm.elements[champ_date+'_aaaa'].value;
				}
				else
				{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
				}
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}
	datedeb="datedeb_sejour";datefin="datefin_sejour";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj'])//si datedeb et datefin dans le form  
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
	}
	datedeb="datedeb_sejour";	
	if(frm.elements[datedeb+'_jj'])
	{ if(tab_date[datedeb].jj+tab_date[datedeb].mm+tab_date[datedeb].aaaa=="")
		{ erreur+=tab_date[datedeb].lib+' doit etre renseignée'+"\n";
		}
	}
	// si non permanent : La date d'arrivée prévue doit etre renseignée si elle est dans le formulaire, sinon la date d'arrivée doit etre renseignée
	if(frm.elements['codestatutpers'] && frm.elements['codestatutpers'].value=='02')
	{ datefin="datefin_sejour";	
		if(frm.elements[datefin+'_jj'])
		{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa=="")
			{ erreur+='Non permanent : '+tab_date[datefin].lib+' doit etre renseignée'+"\n";
			}
		}
	}
	tab_duree=duree_aaaammjj(tab_date[datedeb].aaaa+"/"+tab_date[datedeb].mm+"/"+tab_date[datedeb].jj,tab_date[datefin].aaaa+"/"+tab_date[datefin].mm+"/"+tab_date[datefin].jj);
	// 20161213 si limitation a sejour de 5 jours max.
	if(frm.elements['limitation5joursmax'])
	{ if(tab_duree["a"]+tab_duree["m"]>=1 || ((tab_duree["a"]+tab_duree["m"]==0) && tab_duree["j"]>5))
		{ erreur+="Vous n'etes pas autorisé a saisir des séjours de plus de 5 jours !"
		}
	}
	
	if(erreur=='')
	{ if(frm.elements['codecorps'].value=='51')//doctorant
		{ if(tab_duree["a"]!=3 || tab_duree["m"]!=0 || tab_duree["j"]!=0)
			{ return confirm("La durée n'est pas exactement de 3 ans."+"\n"+"Valider la création ?")
			}
		}
		return confirm('Valider la création');
	}
	else
	{ alert(erreur);
		return false;
	}
}
// PG fsd 20160120
function controle_form_fiche_dossier_pers(formulaire,bouton_submit)
// PG fsd 20160120
{ var frm=document.forms[formulaire];
	var erreur="";
	var warning="";
	var tab_date={"datedeb_sejour_prevu" : {"lib" : "Date de début de séjour prévue","jj" : "","mm" : "","aaaa" : ""},
								"datefin_sejour_prevu" : {"lib" : "Date de fin de séjour prévue","jj" : "","mm" : "","aaaa" : ""},
								"datedeb_sejour" : {"lib" : "Date de début de séjour","jj" : "","mm" : "","aaaa" : ""},
								"datefin_sejour" : {"lib" : "Date de fin de séjour","jj" : "","mm" : "","aaaa" : ""},
								"date_demande_fsd" : {"lib" : "Date demande acces","jj" : "","mm" : "","aaaa" : ""},
								"date_autorisation" : {"lib" : "Date autorisation","jj" : "","mm" : "","aaaa" : ""},
								"date_demande_modification_fsd" : {"lib" : "Date demande modification fsd","jj" : "","mm" : "","aaaa" : ""},
								"date_naiss" : {"lib" : "Date de naissance","jj" : "","mm" : "","aaaa" : ""},
								"date_preminscr" : {"lib" : "Date de premiere inscription","jj" : "","mm" : "","aaaa" : ""},
								"datedeb_emploi" : {"lib" : "Date de début de contrat/emploi","jj" : "","mm" : "","aaaa" : ""},
								"datefin_emploi" : {"lib" : "Date de fin de contrat/emploi","jj" : "","mm" : "","aaaa" : ""},
								"date_soutenance" : {"lib" : "Date de soutenance","jj" : "","mm" : "","aaaa" : ""},
								"dateemploi_postdoc" : {"lib" : "Date emploi postdoc","jj" : "","mm" : "","aaaa" : ""},
								"date_hdr" : {"lib" : "Date HDR","jj" : "","mm" : "","aaaa" : ""}

								};
	var tab_champs_heure_mn={"heure_soutenance" : {"lib" : "Heure soutenance","hh" : "","mn" : ""},
														"heure_hdr" : {"lib" : "Heure HDR","hh" : "","mn" : ""}};
	var tab_numerique={	"montantfinancement" :  {"lib" : "Montant du financement"},
											"num_inscr" : {"lib" : "numéro d'inscription"},
											"duree_mois_these" : {"lib" : "Durée en mois"},
											"montant_mensuel_charge" : {"lib" : "montant mensuel charge"},
											"montant_mensuel_brut" : {"lib" : "montant mensuel brut"},
											"montant_mensuel_net" : {"lib" : "montant mensuel net"},
											"quotite_admin" : {"lib" : "quotité administrative"},
											"quotite_unite" : {"lib" : "quotité unité"},
											"num_insee" : {"lib" : "n° INSEE"}};
	var file_types_array=new Array('pdf','doc','docx','txt','pptx','xls','xlsx','csv','gif','jpeg','jpg','png');
	
	/*var invite=(frm.elements['codecorps'] && (frm.elements['codecorps'].value=='54' || frm.elements['codecorps'].value=='56'));
	var horsue=(frm.elements['ue'] && frm.elements['ue'].value=="non");*/
	var duree_mois_these=0;

	if(formulaire=='form_fiche_dossier_pers_partiel')//pour passer le controle theme : le theme n'est pas proposé en dossier partiel
	{ themechecked=true;
	}
	else
	{ themechecked=false;
	}
	if(frm.elements['codereferent'] && frm.elements['codereferent'].options[frm.elements['codereferent'].selectedIndex].value=='')
	{ erreur+="Référent obligatoire"+"\n"; 
	}
	if(frm.elements['adresse_pers'] && longueur(frm.elements['adresse_pers'].value)>200) erreur+="'Adresse personnelle' trop long : "+longueur(frm.elements['adresse_pers'].value)+"\n";
	if(frm.elements['titre_prog_rech'] && longueur(frm.elements['titre_prog_rech'].value)>200) erreur+="'Titre du sujet ou programme de recherche' trop long : "+longueur(frm.elements['titre_prog_rech'].value)+"\n";
	if(frm.elements['prog_rech'] && longueur(frm.elements['prog_rech'].value)>6000) erreur+="'Sujet ou programme de recherche' trop long : "+longueur(frm.elements['prog_rech'].value)+"\n";
	if(frm.elements['note'] && longueur(frm.elements['note'].value)>6000) erreur+="'Notes partagées' trop long : "+longueur(frm.elements['note'].value)+"\n";
	if(frm.elements['postit'] && longueur(frm.elements['postit'].value)>200) erreur+="'Postit' trop long : "+longueur(frm.elements['postit'].value)+"\n";
	if(frm.elements['resume_these'] && longueur(frm.elements['resume_these'].value)>6000) erreur+="'Résumé' trop long : "+longueur(frm.elements['resume_these'].value)+"\n";
	if(frm.elements['descriptionmission'] && longueur(frm.elements['descriptionmission'].value)>6000) erreur+="'Description de la mission' trop long : "+longueur(frm.elements['descriptionmission'].value)+"\n";
	if(frm.elements['avis_motive_resp_zrr'] && longueur(frm.elements['avis_motive_resp_zrr'].value)>300) erreur+="Avis motivé : "+longueur(frm.elements['avis_motive_resp_zrr'].value)+"\n";
	
	if(frm.elements['num_insee'] && frm.elements['num_insee'].value!='' && (longueur(frm.elements['num_insee'].value)!=13 || longueur(frm.elements['num_insee'].value)!=15))
	{ erreur+="Le n° INSEE doit comporter 13 chiffres ou 15 avec la clé : il en comporte "+longueur(frm.elements['num_insee'].value)+"\n";  
	}
	
	if(frm.elements['codeciv'] && frm.elements['codeciv'].value=='') erreur+="Civilité obligatoire"+"\n";
	if(frm.elements['nom'] && frm.elements['nom'].value=='') erreur+="Nom obligatoire"+"\n";
	if(frm.elements['prenom'] && frm.elements['prenom'].value=='') erreur+='Prénom obligatoire'+"\n";

	if(frm.elements['date_naiss_jj'] && frm.elements['date_naiss_jj'].value=='')
	{ erreur+="Date de naissance obligatoire"+"\n";
	}
	if(frm.elements['ville_naiss'] && frm.elements['ville_naiss'].value=='')
	{ erreur+="Ville de naissance obligatoire"+"\n";
	}
	if(frm.elements['codepays_naiss'] && frm.elements['codepays_naiss'].value=='')
	{ erreur+="Pays de naissance obligatoire"+"\n";
	}
	if(frm.elements['codenat'] && frm.elements['codenat'].value=='')
	{ erreur+="Nationalité obligatoire"+"\n";
	}
	
	if(frm.elements['tel']) 
	{ if(longueur(frm.elements['tel'].value)>0 && longueur(frm.elements['tel'].value)<10)
		{ erreur+='Le numéro de téléphone doit comporter aucun ou au moins 10 chiffres\n';
		}
	}
	if(frm.elements['fax']) 
	{ if(longueur(frm.elements['fax'].value)>0 && longueur(frm.elements['fax'].value)<10)
		{ erreur+="Le numéro de fax doit comporter aucun ou au moins 10 chiffres"+"\n";
		}
	}
	if(frm.elements['telport']) 
	{ if(longueur(frm.elements['telport'].value)>0 && longueur(frm.elements['telport'].value)<10)
		{ erreur+="Le numéro de téléphone portable doit comporter aucun ou au moins 10 chiffres"+"\n";
		}
	}
	if((frm.elements["email"] && frm.elements["email"].value=='') && (frm.elements["email_parti"] && frm.elements["email_parti"].value=='')) warning+='Mail ou mail parti non renseignes'+"\n";
	if((frm.elements["email"] && !est_mail(frm.elements["email"].value)) || (frm.elements["email_parti"] && !est_mail(frm.elements["email_parti"].value))) erreur+='Mail ou mail parti mal formé'+"\n";

	if(frm.elements['master_prep_annee']){if(!est_champ_annee(frm.elements['master_prep_annee'])) erreur+="Année master erronée : "+frm.elements['master_prep_annee'].value+"\n";}
	if(frm.elements['master_obtenu_annee']){if(!est_champ_annee(frm.elements['master_obtenu_annee'])) erreur+="Année master obtenu erronée : "+frm.elements['master_obtenu_annee'].value+"\n";}
	if(frm.elements['master_obtenu_annee']){if(!est_champ_annee(frm.elements['diplome_dernier_annee'])) erreur+="Année dernier diplome erronée : "+frm.elements['diplome_dernier_annee'].value+"\n";}
	
	//if(frm.elements['codeetab'] && frm.elements['autreetab'] && frm.elements['codeetab'].value!='' && frm.elements['autreetab'].value!='') {erreur+="Pas plus d'un employeur !"+"\n";}
	if(frm.elements['codeetab'] && frm.elements['autreetab'] && frm.elements['codeetab'].value=='' && frm.elements['autreetab'].value=='') { erreur+="Employeur obligatoire"+"\n";}
	//if(frm.elements['codelieu'] && frm.elements['autrelieu'] && frm.elements['codelieu'].value!='' && frm.elements['autrelieu'].value!='') {erreur+="Pas plus d'un lieu de travail pour l'activité de recherche"+"\n";}
	if(frm.elements['codelieu'] && frm.elements['codelieu'].value==''){ erreur+="Lieu de travail pour l'activité de recherche obligatoire"+"\n";}
	if(frm.elements['codediplome_prep'] && frm.elements['codediplome_prep'].value=='' && frm.elements['autrediplome_prep'] && frm.elements['autrediplome_prep'].value==''){ erreur+="Diplome préparé obligatoire"+"\n";}
	if(frm.elements['codediplome_prep'] && frm.elements['autrediplome_prep'] && frm.elements['codediplome_prep'].value!='' && frm.elements['autrediplome_prep'].value!='') { erreur+="Pas plus d'un Diplome préparé"+"\n";}
	if(frm.elements['codecentrecout']){ if(frm.elements['codecentrecout'].value!='' && frm.elements['autrecentrecout'].value!='') erreur+="Pas plus d'un centre de cout !"+"\n";}

	if((frm.elements['cotutelle'] && frm.elements['cotutelle'].checked) 
			|| (frm.elements['cotutelle_etab'] && frm.elements['cotutelle_etab'].value!='') 
			|| (frm.elements['codepays_cotutelle'] && frm.elements['codepays_cotutelle'].value!=''))
	{ if(frm.elements['cotutelle'])
		{ frm.elements['cotutelle'].checked=true;
		}
		if(frm.elements['cotutelle_etab'] && frm.elements['cotutelle_etab'].value=='')
		{ erreur+="Co-tutelle : établissement obligatoire"+"\n";
		}
		if(frm.elements['codepays_cotutelle'] && frm.elements['codepays_cotutelle'].value=='')
		{ erreur+="Co-tutelle : Pays obligatoire"+"\n";
		}
	}
	
	if(frm.elements['codetypeprofession_postdoc'] && frm.elements['codetypeprofession_postdoc'].value==''){ erreur+="Type profession postdoc obligatoire"+"\n";}
		
	if(frm.elements['codesectioncnu'] && frm.elements['codesectioncnrs'])//si codesectioncnu est dans le form alors codesectioncnrs y est aussi
	{ if(frm.elements['codesectioncnu'].value!='' && frm.elements['codesectioncnrs'].value!='')erreur+="Une seule section CNU ou CoNRS"+"\n";
	}
	// champs numeriques avec un . ( , transformee en .)
	for(var champ_numerique in tab_numerique)
	{ if(frm.elements[champ_numerique])
		{ frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(",", "g"), ".");
			frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(" ", "g"), "");
			if(frm.elements[champ_numerique].value!='' && isNaN(frm.elements[champ_numerique].value)) 
			{ erreur+="Champ "+tab_numerique[champ_numerique].lib+" non numérique"+"\n";
			}
		}
	}
	
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ if(champ_date=='date_naiss')
				{ frm.elements[champ_date+'_aaaa'].value='19'+frm.elements[champ_date+'_aaaa'].value;
				}
				else
				{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
				}
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}
	// datedeb doit etre inférieure a datefin 
	datedeb="datedeb_sejour_prevu";datefin="datefin_sejour_prevu";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj']) 
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
	}
	datedeb="datedeb_sejour";datefin="datefin_sejour";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj'])//si datedeb et datefin dans le form  
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
		// PG fsd 20160120
		tab_duree=duree_aaaammjj(tab_date[datedeb].aaaa+"/"+tab_date[datedeb].mm+"/"+tab_date[datedeb].jj,tab_date[datefin].aaaa+"/"+tab_date[datefin].mm+"/"+tab_date[datefin].jj);
		// 20161213 si limitation a sejour de 5 jours max.
		if(tab_duree["a"]+tab_duree["m"]>=1 || ((tab_duree["a"]+tab_duree["m"]==0) && tab_duree["j"]>5))
		{ if(frm.elements['limitation5joursmax'])
			{ erreur+="Vous n'etes pas autorisé a saisir des séjours de plus de 5 jours."
			}
			else
			{	if(frm.elements['adresse_pers'] && frm.elements['adresse_pers'].value=='')
				{ erreur+="Adresse pers. obligatoire"+"\n";
				}
				else
				{ if(frm.elements['ville_pers'] && frm.elements['ville_pers'].value==''){ erreur+="Ville adresse pers. obligatoire"+"\n";}
					if(frm.elements['codepays_pers'] && frm.elements['codepays_pers'].value==''){ erreur+="Pays adresse pers. obligatoire"+"\n";}
				}
			}
		}
	}
	datedeb="datedeb_sejour";datesoutenance="date_soutenance";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datesoutenance+'_jj'])//si datedeb et datesoutenance dans le form  
	{ if(tab_date[datesoutenance].jj+tab_date[datesoutenance].mm+tab_date[datesoutenance].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj > tab_date[datesoutenance].aaaa+tab_date[datesoutenance].mm+tab_date[datesoutenance].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datesoutenance].lib+"\n";
		}
	}
	datedeb="datedeb_emploi";datefin="datefin_emploi";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj'])//si datedeb et datefin dans le form  
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
	}

	// La date d'arrivée prévue doit etre renseignée si elle est dans le formulaire, sinon la date d'arrivée doit etre renseignée
	datedeb_sejour_prevu_vide=false;
	datedeb="datedeb_sejour_prevu";
	if(frm.elements[datedeb+'_jj'])
	{ if(tab_date[datedeb].jj+tab_date[datedeb].mm+tab_date[datedeb].aaaa=="")
		{ datedeb_sejour_prevu_vide=true;
		}
	}
	else
	{ datedeb_sejour_prevu_vide=true;
	}
	datedeb="datedeb_sejour";	
	if(frm.elements[datedeb+'_jj'])
	{ if(tab_date[datedeb].jj+tab_date[datedeb].mm+tab_date[datedeb].aaaa=="" && datedeb_sejour_prevu_vide)
		{ erreur+=tab_date[datedeb+'_prevu'].lib+' ou '+tab_date[datedeb].lib+' doit etre renseignée'+"\n";
		}
	}
	// si non permanent : La date d'arrivée prévue doit etre renseignée si elle est dans le formulaire, sinon la date d'arrivée doit etre renseignée
	if(frm.elements['permanent'] && frm.elements['permanent'].value=='02')
	{ datefin_sejour_prevu_vide=false;
		datefin="datefin_sejour_prevu";
		if(frm.elements[datefin+'_jj'])
		{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa=="")
			{ datefin_sejour_prevu_vide=true;
			}
		}
		else
		{ datefin_sejour_prevu_vide=true;
		}
		datefin="datefin_sejour";	
		if(frm.elements[datefin+'_jj'])
		{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa=="" && datefin_sejour_prevu_vide)
			{ erreur+=tab_date[datefin+'_prevu'].lib+' ou '+tab_date[datefin].lib+' doit etre renseignée'+"\n";
			}
		}
	}
	// champs heure mn
	for( var champ_heure_mn in tab_champs_heure_mn)
	{ if(frm.elements[champ_heure_mn+'_hh'] && frm.elements[champ_heure_mn+'_mn'])
		{	if(longueur(frm.elements[champ_heure_mn+'_hh'].value)==1)
			{ frm.elements[champ_heure_mn+'_hh'].value='0'+frm.elements[champ_heure_mn+'_hh'].value;
			}
			if(longueur(frm.elements[champ_heure_mn+'_mn'].value)==1)
			{ frm.elements[champ_heure_mn+'_mn'].value='0'+frm.elements[champ_heure_mn+'_mn'].value;
			}
			else if(longueur(frm.elements[champ_heure_mn+'_hh'].value)!=0 && longueur(frm.elements[champ_heure_mn+'_mn'].value)==0)
			{ frm.elements[champ_heure_mn+'_mn'].value='00';
			}
			tab_champs_heure_mn[champ_heure_mn]['hh']=frm.elements[champ_heure_mn+'_hh'].value;
			tab_champs_heure_mn[champ_heure_mn]['mn']=frm.elements[champ_heure_mn+'_mn'].value;
			if(!est_heure_mn(tab_champs_heure_mn[champ_heure_mn]['hh'],tab_champs_heure_mn[champ_heure_mn]['mn']))
			{ erreur+=tab_champs_heure_mn[champ_heure_mn]['lib']+' mal formée : '+tab_champs_heure_mn[champ_heure_mn]['hh']+'H'+tab_champs_heure_mn[champ_heure_mn]['mn']+"\n";
			}
		}
	}

	for(i=0;i<frm.elements.length;i++)//on fait un OU logique entre toutes les valeurs des champs codetheme#
	{ if(frm.elements[i].name.substring(0,(new String('codetheme#')).length) =='codetheme#')
		{themechecked=themechecked||frm.elements[i].checked;
		}
	}
	if(!themechecked)
	{ erreur+="Aucun Dept. selectionné"+"\n";
	}
	
	//controle types fichiers joints
	for(i=0;i<frm.elements.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('pj[')).length) =='pj[')
		{ if(frm.elements[i].value!='')
			{ extension=new String(frm.elements[i].value).substr(new String(frm.elements[i].value).lastIndexOf('.')+1);
				if(file_types_array.join(',').indexOf(extension.toLowerCase(),0)==-1)//recherche extension en minuscules dans la chaine formee (join) des elements de file_types_array
				{ erreur+=frm.elements[i].value+" : "+extension+" n'est pas une extension valide ("+file_types_array.join(', ')+")"+"\n";
				}
			}
		}
	}
	if(frm.elements['numdossierzrr'] && frm.elements['numdossierzrr'].value!='')
	{ tab=(new String(frm.elements['numdossierzrr'].value)).split('-');
		if(tab.length!=4)
		{ erreur+="numero zrr mal forme : aaaa-mm-codezrr-numordre"+"\n";
		}
		else
		{ if(isNaN(tab[0]) || isNaN(tab[1]!='') || isNaN(tab[3]) || tab[0]=='' || tab[1]=='' || tab[2]=='' || tab[3]=='')
			{ erreur+="numero zrr mal forme : aaaa-mm-codezrr-numordre"+"\n"
			}
		}
	}/**/
	// PG fsd 20160120
	if(bouton_submit=='submit_generer_classeur_fsd')
	{	//alert(bouton_submit.name)
		//if(frm.elements['codepostal_naiss'] && frm.elements['codepostal_naiss'].value==''){ erreur+="Code postal du lieu de naissance obligatoire"+"\n";}
		if(frm.elements['email'] && frm.elements['email'].value=='' && frm.elements['email_parti'] && frm.elements['email_parti'].value==''){ erreur+="Email obligatoire"+"\n";}
		if(frm.elements['adresse_pers'] && frm.elements['adresse_pers'].value==''){ erreur+="Adresse pers. obligatoire"+"\n";}
		if(frm.elements['ville_pers'] && frm.elements['ville_pers'].value==''){ erreur+="Ville adresse pers. obligatoire"+"\n";}
		if(frm.elements['codepays_pers'] && frm.elements['codepays_pers'].value==''){ erreur+="Pays adresse pers. obligatoire"+"\n";}
		//if(frm.elements['codepostal_pers'] && frm.elements['codepostal_pers'].value==''){ erreur+="Code postal adresse pers. obligatoire"+"\n";}
		if(frm.elements['etab_orig'] && frm.elements['etab_orig'].value==''){ erreur+="Organisme d'appartenance obligatoire"+"\n";}
		if(frm.elements['adresse_etab_orig'] && frm.elements['adresse_etab_orig'].value==''){ erreur+="Adresse organisme d'appartenance obligatoire"+"\n";}
		if(frm.elements['ville_etab_orig'] && frm.elements['ville_etab_orig'].value==''){ erreur+="Ville de l'organisme d'appartenance obligatoire"+"\n";}
		if(frm.elements['codepays_etab_orig'] && frm.elements['codepays_etab_orig'].value==''){ erreur+="Pays de l'établissement d'origine obligatoire"+"\n";}
		
		if(frm.elements['codetypepieceidentite'] && frm.elements['autretypepieceidentite'] && frm.elements['codetypepieceidentite'].value=='' && frm.elements['autretypepieceidentite'].value=='') { erreur+="Piece d'identite obligatoire"+"\n";}
		if(frm.elements['codenat'] && frm.elements['codenat'].value!='079' && frm.elements['codetypepieceidentite'] && frm.elements['codetypepieceidentite'].value!='02')//hors france
		{ erreur+="Passeport obligatoire"+"\n";
		}
		if(frm.elements['numeropieceidentite'] && frm.elements['numeropieceidentite'].value==''){ erreur+="Numero de piece d'identite obligatoire"+"\n";}
		if(frm.elements['codesituationprofessionnelle'] && frm.elements['autresituationprofessionnelle']) { erreur+="Situation professionnelle obligatoire"+"\n";}
		if(frm.elements['codetypeacceszrr'] && frm.elements['codetypeacceszrr'].value==''){ erreur+="Statut au sein de la ZRR obligatoire"+"\n";}
		if(frm.elements['codephysiquevirtuelzrr'] && frm.elements['codephysiquevirtuelzrr'].value==''){ erreur+="Acces physique/virtuel obligatoire"+"\n";}
		if(frm.elements['codeoriginefinancement'] && frm.elements['codeoriginefinancement'].value=='') {erreur+="Origine du financement obligatoire"+"\n";}
		if(frm.elements['montantfinancement'] && frm.elements['montantfinancement'].value=='') {warning+="Montant du financement non renseigné"+"\n";}			
		if(frm.elements['intituleposte'] && frm.elements['intituleposte'].value!='' && frm.elements['codesujet'] && frm.elements['codesujet'].value!='')
		{ erreur+="Soit un poste, soit un sujet mais pas les deux"+"\n";
		}
		if((frm.elements['codesujet'] && frm.elements['codesujet'].value=='') || !frm.elements['codesujet'])
		{ if(frm.elements['intituleposte'] && frm.elements['intituleposte'].value==''){ erreur+="Intitule du poste obligatoire"+"\n";}
			if(frm.elements['descriptionmission'] && frm.elements['descriptionmission'].value==''){ erreur+="Description de la mission obligatoire"+"\n";}
			if(frm.elements['codedomainescientifique1'] && frm.elements['codedomainescientifique1'].value==''){ erreur+="Domaine scientifique obligatoire"+"\n";}
			if(frm.elements['codedisciplinescientifique1'] && frm.elements['codedisciplinescientifique1'].value==''){ erreur+="Discipline scientifique obligatoire"+"\n";}
		}
		
	}


	if(erreur!="")
	{ alert(erreur);
		return false;
	}
	else
	{ // Controle intersection dates séjour et emploi : warning
		datedeb="datedeb_emploi";	
		if(frm.elements[datedeb+'_jj'] && frm.elements[datedeb+'_jj'].value=="")
		{ warning+=tab_date[datedeb].lib+' doit etre renseignée : elle sera initialisée a la date de début de séjour'+"\n";
		}
		if(frm.elements['datedeb_sejour_jj'] && frm.elements['datefin_sejour_jj'] && frm.elements['datedeb_emploi_jj'] && frm.elements['datefin_emploi_jj'])
		{ datedeb_sejour=frm.elements['datedeb_sejour_aaaa'].value+frm.elements['datedeb_sejour_mm'].value+frm.elements['datedeb_sejour_jj'].value;
			datefin_sejour=frm.elements['datefin_sejour_aaaa'].value+frm.elements['datefin_sejour_mm'].value+frm.elements['datefin_sejour_jj'].value;
			datedeb_emploi=frm.elements['datedeb_emploi_aaaa'].value+frm.elements['datedeb_emploi_mm'].value+frm.elements['datedeb_emploi_jj'].value;
			datefin_emploi=frm.elements['datefin_emploi_aaaa'].value+frm.elements['datefin_emploi_mm'].value+frm.elements['datefin_emploi_jj'].value;
			if(!intersectionperiodes(datedeb_sejour,datefin_sejour,datedeb_emploi,datefin_emploi))
			{ warning+="Les périodes de séjour et d'emploi sont disjointes : si vous validez, l'emploi ne sera plus visible dans ce séjour"+"\n";
				warning+="Si vous avez un doute, annulez et lisez les instructions figurant dans la bulle d'information 'Contrat/Emploi'";
			}
		}	
		// date fin sejour = date soutenance si pas vide
		datefin="datefin_sejour";datesoutenance="date_soutenance";
		if(frm.elements[datefin+'_jj'] && frm.elements[datesoutenance+'_jj'])//si datefin et datesoutenance dans le form  
		{ if(tab_date[datesoutenance].jj+tab_date[datesoutenance].mm+tab_date[datesoutenance].aaaa!="" && tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj != tab_date[datesoutenance].aaaa+tab_date[datesoutenance].mm+tab_date[datesoutenance].jj)
			{ warning+=tab_date[datefin].lib+' ('+tab_date[datefin].jj+'/'+tab_date[datefin].mm+'/'+tab_date[datefin].aaaa+') remplacée par '+tab_date[datesoutenance].lib+' ('+tab_date[datesoutenance].jj+'/'+tab_date[datesoutenance].mm+'/'+tab_date[datesoutenance].aaaa+')'+"\n";
				frm.elements[datefin+'_jj'].value=tab_date[datesoutenance].jj;
				frm.elements[datefin+'_mm'].value=tab_date[datesoutenance].mm;
				frm.elements[datefin+'_aaaa'].value=tab_date[datesoutenance].aaaa;
			}
		}
		// calcul duree en mois et message d'information si modif
		if(frm.elements['date_soutenance_jj'] && frm.elements['date_soutenance_mm'].value!='' && frm.elements['date_soutenance_aaaa'].value!=''
			&& frm.elements['datedeb_sejour_jj'] && frm.elements['datedeb_sejour_mm'].value!='' && frm.elements['datedeb_sejour_aaaa'].value!=''
				&& frm.elements['duree_mois_these'] && frm.elements['duree_mois_these'].value=='')
		{	duree_mois_these=(parseInt(frm.elements['date_soutenance_aaaa'].value,10)-parseInt(frm.elements['date_preminscr_aaaa'].value,10)-1)*12+(12-parseInt(frm.elements['date_preminscr_mm'].value,10))+parseInt(frm.elements['date_soutenance_mm'].value,10);
			if(frm.elements['duree_mois_these'].value!=duree_mois_these)
			{ confirmation=confirm("OK pour modifier la durée en mois : "+duree_mois_these);
				if(confirmation)
				{ frm.elements['duree_mois_these'].value=duree_mois_these;
				}
			}
		}
		
		if(warning!='')
		{ return confirm('Pour information :'+"\n"+warning);
		}
		else
		{ return true;
		}
	}
}

function controle_form_confirmer_mail_validation_demande_fsd(formulaire)  
{	erreur="";
	frm=document.forms[formulaire];
	var tab_date={"date_demande_fsd" : {"lib" : "Date demande fsd","jj" : "","mm" : "","aaaa" : ""}};
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}	
	if(tab_date["date_demande_fsd"].jj+tab_date["date_demande_fsd"].mm+tab_date["date_demande_fsd"].aaaa=='')
	{	erreur+=tab_date["date_demande_fsd"].lib+' obligatoire';
	}
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ return true;
	}
}

function controle_form_confirmer_mail_validation_autorisation_fsd(formulaire)  
{	erreur="";
	frm=document.forms[formulaire];
	var tab_date={"date_autorisation" : {"lib" : "Date autorisation","jj" : "","mm" : "","aaaa" : ""}};
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}
	if(tab_date["date_autorisation"].jj+tab_date["date_autorisation"].mm+tab_date["date_autorisation"].aaaa=='')
	{	erreur+=tab_date["date_autorisation"].lib+' obligatoire';
	}
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ return true;
	}
}

function controle_form_sujet(formulaire)  
{	erreur="";
	frm=document.forms[formulaire];
	if(frm.elements['titre_fr'] && longueur(frm.elements['titre_fr'].value)>300) erreur+="Titre en francais trop long : "+longueur(frm.elements['titre_fr'].value)+"\n";
	if(frm.elements['titre_en'] && longueur(frm.elements['titre_en'].value)>300) erreur+="Titre en anglais trop long : "+longueur(frm.elements['titre_en'].value)+"\n";
	if(frm.elements['descr_fr'] && longueur(frm.elements['descr_fr'].value)>6400) erreur+="Description en francais trop long : "+longueur(frm.elements['descr_fr'].value)+"\n";
	if(frm.elements['descr_en'] && longueur(frm.elements['descr_en'].value)>6400) erreur+="Titre en anglais trop long : "+longueur(frm.elements['descr_en'].value)+"\n";
	if(frm.elements['conditions_fr'] && longueur(frm.elements['conditions_fr'].value)>2000) erreur+="Conditions en francais trop long : "+longueur(frm.elements['conditions_fr'].value)+"\n";
	if(frm.elements['conditions_en'] && longueur(frm.elements['conditions_en'].value)>2000) erreur+="Conditions en anglais trop long : "+longueur(frm.elements['conditions_en'].value)+"\n";
	if(frm.elements['avis_motive_encadrant_zrr'] && longueur(frm.elements['avis_motive_encadrant_zrr'].value)>300) erreur+="Avis motivé : "+longueur(frm.elements['avis_motive_encadrant_zrr'].value)+"\n";
	
	if(frm.elements['descr_fr'] && longueur(frm.elements['descr_fr'].value)<800)
	{ erreur+='Description : 800 caracteres minimum'+"\n";
	}
	if(frm.elements['codetypestage'] && longueur(frm.elements['codetypestage'].value)=='') erreur+="Type de stage obligatoire"+"\n";
	if(frm.elements['titre_fr'] && longueur(frm.elements['titre_fr'].value)==0) erreur+="Titre en francais obligatoire"+"\n";
	if(frm.elements['financement_fr'] && longueur(frm.elements['financement_fr'].value)==0 && frm.elements['codetypesujet'] && frm.elements['codetypesujet'].value=='03') erreur+="Financement en francais obligatoire"+"\n";
	if(frm.elements['codetypesujet'] && (frm.elements['codetypesujet'].value!='02' || (frm.elements['codetypesujet'].value=='02' && frm.elements['codetypestage'] && frm.elements['codetypestage'].value=='01')))
	{ if(frm.elements['titre_en'] && longueur(frm.elements['titre_en'].value)==0) erreur+="Titre en anglais obligatoire"+"\n";
	}

	var tab_date={"datedeb_sujet" : {"lib" : "Date de début","jj" : "","mm" : "","aaaa" : ""},"datefin_sujet" : {"lib" : "Date de fin","jj" : "","mm" : "","aaaa" : ""}};
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}	
	datedeb="datedeb_sujet";datefin="datefin_sujet";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj'])//si datedeb et datefin dans le form  
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
	}
	
 	if(frm.elements[datedeb+'_jj'])
	{ if(tab_date["datedeb_sujet"].aaaa+tab_date["datedeb_sujet"].mm+tab_date["datedeb_sujet"].jj=='') erreur+=tab_date[datedeb].lib+" obligatoire"+"\n";
	}
	if(frm.elements[datefin+'_jj'])
	{ if(tab_date["datefin_sujet"].aaaa+tab_date["datefin_sujet"].mm+tab_date["datefin_sujet"].jj=='') erreur+=tab_date[datefin].lib+" obligatoire"+"\n";
	}

	if(frm.elements['codedir#1'].value=='') erreur+="1er encadrant obligatoire"+"\n";

	themechecked=false;
	for(i=0;i<frm.elements.length;i++)//on fait un OU logique entre toutes les valeurs des champs codetheme#
	{ if(frm.elements[i].name.substring(0,(new String('codetheme#')).length) =='codetheme#')
		{ themechecked=themechecked||frm.elements[i].checked;
		}
	}
	if(!themechecked)
	{ erreur+="Aucun Dept. selectionné !"+"\n";
	}
	if(frm.elements[datedeb+'_jj'] && frm.elements['datedeb_sujet_aaaa'].value+'/'+frm.elements['datedeb_sujet_mm'].value+'/'+frm.elements['datedeb_sujet_jj'].value>='2014/09/15')
	{	if(frm.elements['codedomainescientifique1'] && frm.elements['codedomainescientifique1'].value=='') erreur+="Domaine scientifique obligatoire"+"\n";
		if(frm.elements['codedisciplinescientifique1'] && frm.elements['codedisciplinescientifique1'].value=='') erreur+="Discipline scientifique obligatoire"+"\n";
	}
	
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ return true;
	}
}

function controle_form_contrat(formulaire)
{ erreur="";
	frm=document.forms[formulaire];
	// champs numeriques avec un . ( , transformee en .
	var tab_numerique={"duree_mois" : {"lib" : "Durée en mois"},"montant_ht" : {"lib" : "montant"},"permanent_mois" : {"lib" : "permanent.mois"},"personnel_mois" : {"lib" : "personnel.mois"}};
	for(var champ_numerique in tab_numerique)
	{ if(frm.elements[champ_numerique])
		{ frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(",", "g"), ".");
			frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(" ", "g"), "");
			if(frm.elements[champ_numerique].value!='' && isNaN(frm.elements[champ_numerique].value)) erreur+="Champ "+tab_numerique[champ_numerique].lib+" non numérique !"+"\n";
		}
	}
	for(i=0;i<frm.elements.length;i++)//
	{ if(frm.elements[i].name.substring(0,(new String('montant#')).length) =='montant#' || frm.elements[i].name.substring(0,(new String('montantdetail#')).length) =='montantdetail#')
		{ frm.elements[i].value=(frm.elements[i].value).replace(new RegExp(",", "g"), ".");
			frm.elements[i].value=(frm.elements[i].value).replace(new RegExp(" ", "g"), "");
			if(frm.elements[i].value!='' && isNaN(frm.elements[i].value)) erreur+="Champ montant non numérique !"+"\n";
		}
	}

	var tab_date={"datedeb_contrat" : {"lib" : "Date de début","jj" : "","mm" : "","aaaa" : ""},
								"datefin_contrat" : {"lib" : "Date de fin","jj" : "","mm" : "","aaaa" : ""},
								"date_signature_contrat" : {"lib" : "Date de signature du contrat","jj" : "","mm" : "","aaaa" : ""},
								"date_limite_justification" : {"lib" : "Date de justification","jj" : "","mm" : "","aaaa" : ""},
								"date_am2i" : {"lib" : "Date de transmission AM2I","jj" : "","mm" : "","aaaa" : ""}};
	for(var champ_date in tab_date)
	{ if(frm.elements[champ_date+'_jj'])//si le champ champ_date existe dans le formulaire
	  { if(longueur(frm.elements[champ_date+'_jj'].value)==1)
			{ frm.elements[champ_date+'_jj'].value='0'+frm.elements[champ_date+'_jj'].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'].value)==1)
			{ frm.elements[champ_date+'_mm'].value='0'+frm.elements[champ_date+'_mm'].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'].value)==2)
			{ frm.elements[champ_date+'_aaaa'].value='20'+frm.elements[champ_date+'_aaaa'].value;
			}
			tab_date[champ_date].jj=frm.elements[champ_date+'_jj'].value;
			tab_date[champ_date].mm=frm.elements[champ_date+'_mm'].value;
			tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'].value;
			if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
			{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
			}
		}
	}
	
	//champs dates des montants detail
	champ_date='';
	for(i=0;i<frm.elements.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('datemontant_jj')).length) =='datemontant_jj')
	  { champ_date='datemontant';
			suffixe=frm.elements[i].name.substring((new String('datemontant_jj')).length);
			if(longueur(frm.elements[champ_date+'_jj'+suffixe].value)==1)
			{ frm.elements[champ_date+'_jj'+suffixe].value='0'+frm.elements[champ_date+'_jj'+suffixe].value;
			}
			if(longueur(frm.elements[champ_date+'_mm'+suffixe].value)==1)
			{ frm.elements[champ_date+'_mm'+suffixe].value='0'+frm.elements[champ_date+'_mm'+suffixe].value;
			}
			if(longueur(frm.elements[champ_date+'_aaaa'+suffixe].value)==2)
			{ frm.elements[champ_date+'_aaaa'+suffixe].value='20'+frm.elements[champ_date+'_aaaa'+suffixe].value;
			}			
			champ_date_jj=frm.elements[champ_date+'_jj'+suffixe].value;
			champ_date_mm=frm.elements[champ_date+'_mm'+suffixe].value;
			champ_date_aaaa=frm.elements[champ_date+'_aaaa'+suffixe].value;
			if(!est_date(champ_date_jj,champ_date_mm,champ_date_aaaa))
			{ erreur+='Date mal formée : '+champ_date_jj+'/'+champ_date_mm+'/'+champ_date_aaaa+"\n";
			}
			else//date des montants comprise dans les limites des dates du contrat
			{ datemontant=champ_date_aaaa+champ_date_mm+champ_date_jj;
				if((datemontant=='' && frm.elements['montantdetail'+suffixe].value!='') || (datemontant!='' && frm.elements['montantdetail'+suffixe].value==''))
				{ erreur+='Montant ou date vide';
				}
				else if(datemontant!='')
				{ if(frm.elements['datedeb_contrat_jj'])
					{ if(datemontant < frm.elements['datedeb_contrat_aaaa'].value+frm.elements['datedeb_contrat_mm'].value+frm.elements['datedeb_contrat_jj'].value)
						{ erreur+='Date en dehors des dates contrat'+"\n";
						}
					}
					if(frm.elements['datefin_contrat_jj'])
					{ if(datemontant>frm.elements['datefin_contrat_aaaa'].value+frm.elements['datefin_contrat_mm'].value+frm.elements['datefin_contrat_jj'].value)
						{ erreur+='Date hors dates contrat'+"\n";
						}
					}
				}
			}
		}
	}
	
	// date fin > date deb
	datedeb="datedeb_contrat";datefin="datefin_contrat";
	if(frm.elements[datedeb+'_jj'] && frm.elements[datefin+'_jj'])//si datedeb et datefin dans le form  
	{ if(tab_date[datefin].jj+tab_date[datefin].mm+tab_date[datefin].aaaa!="" && tab_date[datedeb].aaaa+tab_date[datedeb].mm+tab_date[datedeb].jj>tab_date[datefin].aaaa+tab_date[datefin].mm+tab_date[datefin].jj)
		{ erreur+=tab_date[datedeb].lib+' postérieure a '+tab_date[datefin].lib+"\n";
		}
	}
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ return true;
	}
}

function controle_form_commande(formulaire)  
{ erreur="";
	warning="";
	tab=new Array();
	frm=document.forms[formulaire];
	if(frm.elements['objet'] && longueur(frm.elements['objet'].value)>200) erreur+="Objet trop long : "+longueur(frm.elements['objet'].value)+"\n";
	if(frm.elements['description'] && longueur(frm.elements['description'].value)>400) erreur+="Description trop long : "+longueur(frm.elements['description'].value)+"\n";
	if(frm.elements['libfournisseur'] && longueur(frm.elements['libfournisseur'].value)>100) erreur+="Fournisseur trop long : "+longueur(frm.elements['libfournisseur'].value)+"\n";
	if(frm.elements['numcommande'] && longueur(frm.elements['numcommande'].value)>20) erreur+="Numero de commande trop long : "+longueur(frm.elements['numcommande'].value)+"\n";
	if(frm.elements['nummigo'] && longueur(frm.elements['nummigo'].value)>20) erreur+="Numero service fait (MIGO) trop long : "+longueur(frm.elements['nummigo'].value)+"\n";
	if(frm.elements['note'] && longueur(frm.elements['note'].value)>200) erreur+="Note trop long : "+longueur(frm.elements['note'].value)+"\n";
	// champs numeriques avec un . ( , transformee en .
	var tab_numerique={"rubriquecomptable" : {"lib" : "Rubrique comptable"},"montantliquidation" : {"lib" : "Montant liquidation"},
										 "montantengage" : {"lib" : "Montant engagé"}, "montantpaye" : {"lib" : "Montant payé"}
										};
	for(i=0;i<frm.elements.length;i++)
	{ for(var champ_numerique in tab_numerique)
		{ if(frm.elements[i].name.substring(0,(new String(champ_numerique+'#')).length)==champ_numerique+'#' || frm.elements[i].name==champ_numerique)
			{ frm.elements[i].value=(frm.elements[i].value).replace(new RegExp(",", "g"), ".");
				frm.elements[i].value=(frm.elements[i].value).replace(new RegExp(" ", "g"), "");
				if(frm.elements[i].value!='' && isNaN(frm.elements[i].value)) 
				{ erreur+="Champ "+tab_numerique[champ_numerique].lib+" non numérique !"+"\n";
				}
			}
		}
	}
 	//champs obligatoires
	if(frm.elements['codereferent'] && frm.elements['codereferent'].value=='') erreur+="Demandeur obligatoire"+"\n";
	if(frm.elements['codesecrsite'] && frm.elements['codesecrsite'].value=='') erreur+="Secr. site obligatoire"+"\n";
	if(frm.elements['objet'] && frm.elements['objet'].value=='') erreur+="Objet obligatoire"+"\n";
	if(frm.elements['libfournisseur'] && frm.elements['libfournisseur'].value=='') erreur+="Fournisseur obligatoire"+"\n";
	if(frm.elements['codetypecredit#0'] && frm.elements['codetypecredit#0'].value=='') erreur+="Imputation obligatoire"+"\n";
	
	// au moins une imputation virtuelle
	aumoinsuneimputationvirtuelle=true;
	tab_champ_imputation_virtuelle= {'codetypecredit':"", 'codecentrefinancier':"", 'codecentrecout':"", 'codecontrat':""}
	for(champ in tab_champ_imputation_virtuelle)
	{ aumoinsuneimputationvirtuelle=(aumoinsuneimputationvirtuelle && (frm.elements[champ+"#0##01"] && frm.elements[champ+"#0##01"].value!=''));
	}
	if(!aumoinsuneimputationvirtuelle)
	{ erreur+="Pas d'imputation !";
	}
	
	// au moins un champ montant engage ligne virtuel (code #0) obligatoire (non vide)
	nbimputationvirtuel_montantengage=0;
	for(i=0;i<frm.elements.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('montantengage#0')).length)=='montantengage#0' && frm.elements[i].value!='')
		{ nbimputationvirtuel_montantengage++;
		}
	}
	if(nbimputationvirtuel_montantengage==0)
	{ erreur+="Montant engagé obligatoire (la valeur 0 convient)"+"\n";
	}
	var tab_date={"datecommande" : {"lib" : "Date de commande","jj" : "","mm" : "","aaaa" : ""},
								"dateenvoi_etatfrais" : {"lib" : "Date envoi EF","jj" : "","mm" : "","aaaa" : ""},
								"datemigo" : {"lib" : "Date serv.fait (MIGO)","jj" : "","mm" : "","aaaa" : ""},
								"dateliquidation" : {"lib" : "Date liquidation","jj" : "","mm" : "","aaaa" : ""},
								"datefacture" : {"lib" : "Date facture","jj" : "","mm" : "","aaaa" : ""}
							 };
	for(i=0;i<frm.elements.length;i++)
	{ for(var champ_date in tab_date)
		{	if(frm.elements[i].name.substring(0,(new String(champ_date+'_jj')).length)==champ_date+'_jj')//si le champ date existe dans le formulaire
			{ suffixe=frm.elements[i].name.substring((new String(champ_date+'_jj')).length);
				if(longueur(frm.elements[champ_date+'_jj'+suffixe].value)==1)
				{ frm.elements[champ_date+'_jj'+suffixe].value='0'+frm.elements[champ_date+'_jj'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_mm'+suffixe].value)==1)
				{ frm.elements[champ_date+'_mm'+suffixe].value='0'+frm.elements[champ_date+'_mm'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_aaaa'+suffixe].value)==2)
				{ frm.elements[champ_date+'_aaaa'+suffixe].value='20'+frm.elements[champ_date+'_aaaa'+suffixe].value;
				}
				tab_date[champ_date].jj=frm.elements[champ_date+'_jj'+suffixe].value;
				tab_date[champ_date].mm=frm.elements[champ_date+'_mm'+suffixe].value;
				tab_date[champ_date].aaaa=frm.elements[champ_date+'_aaaa'+suffixe].value;
				if(!est_date(tab_date[champ_date].jj,tab_date[champ_date].mm,tab_date[champ_date].aaaa))
				{ erreur+=tab_date[champ_date].lib+' mal formée : '+tab_date[champ_date].jj+'/'+tab_date[champ_date].mm+'/'+tab_date[champ_date].aaaa+"\n";
				}
			}
		}
	}
	// verif si serv fait ou facture = '' que les autres champs sont vides : demande confirmation d'effacement 
	trace='';
	tab_suffixe_date={jj:'jj',mm:'mm',aaaa:'aaaa'};
	for(i=0;i<frm.elements.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('nummigo#')).length)=='nummigo#' && frm.elements[i].value=='')
		{ suffixe1=frm.elements[i].name.substring((new String('nummigo#')).length);
			vide=true;
			for(var suffixe_date in tab_suffixe_date)
			{ if(frm.elements['datemigo_'+suffixe_date+'#'+suffixe1] && frm.elements['datemigo_'+suffixe_date+'#'+suffixe1].value!='')
				{  vide=false;
				}
			}
			if(!vide){warning+='date MIGO pas vide'+'\n';}
			for(j=0;j<frm.elements.length;j++)
			{ if(frm.elements[j].name.substring(0,(new String('numfacture#'+suffixe1)).length)=='numfacture#'+suffixe1)
				{ if(frm.elements[j].value!='')
					{ warning+='num. facture pas vide'+'\n';
					}
					suffixe2=frm.elements[j].name.substring((new String('numfacture#'+suffixe1+'##')).length);
					vide=true;
					for(var suffixe_date in tab_suffixe_date)
					{ if(frm.elements['datefacture'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2] && frm.elements['datefacture'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2].value!='')
						{ vide=false;
						}
					}
					if(!vide){ warning+='date facture pas vide'+'\n';}
					if(frm.elements['numliquidation'+'#'+suffixe1+'##'+suffixe2] && frm.elements['numliquidation'+'#'+suffixe1+'##'+suffixe2].value!='')
					{ warning+='num. liquidation pas vide'+'\n';
					}
					vide=true;
					for(var suffixe_date in tab_suffixe_date)
					{ if(frm.elements['dateliquidation'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2] && frm.elements['dateliquidation'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2].value!='')
						{ vide=false;
						}
					}
					if(!vide){ warning+='date liquidation pas vide'+'\n';}
					if(frm.elements['montantliquidation'+'#'+suffixe1+'##'+suffixe2] && frm.elements['montantliquidation'+'#'+suffixe1+'##'+suffixe2].value!='')
					{ warning+='montant liquidation pas vide'+'\n';
					}
				}
			}
		}
		else if(frm.elements[i].name.substring(0,(new String('nummigo#')).length)=='nummigo#' && frm.elements[i].value!='')
		{	suffixe1=frm.elements[i].name.substring((new String('nummigo#')).length);
			for(j=0;j<frm.elements.length;j++)
			{ if(frm.elements[j].name.substring(0,(new String('numfacture#'+suffixe1)).length)=='numfacture#'+suffixe1)
				{ if(frm.elements[j].value=='')
					{ suffixe2=frm.elements[j].name.substring((new String('numfacture#'+suffixe1+'##')).length);
						vide=true;
						for(var suffixe_date in tab_suffixe_date)
						{ if(frm.elements['datefacture'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2] && frm.elements['datefacture'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2].value!='')
							{ vide=false;
							}
						}
						if(!vide){ warning+='date facture pas vide'+'\n';}
						if(frm.elements['numliquidation'+'#'+suffixe1+'##'+suffixe2] && frm.elements['numliquidation'+'#'+suffixe1+'##'+suffixe2].value!='')
						{ warning+='num. liquidation pas vide'+'\n';
						}
						vide=true;
						for(var suffixe_date in tab_suffixe_date)
						{ if(frm.elements['dateliquidation'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2] && frm.elements['dateliquidation'+'_'+suffixe_date+'#'+suffixe1+'##'+suffixe2].value!='')
							{ vide=false;
							}
						}
						if(!vide){ warning+='date liquidation pas vide'+'\n';}
						if(frm.elements['montantliquidation'+'#'+suffixe1+'##'+suffixe2] && frm.elements['montantliquidation'+'#'+suffixe1+'##'+suffixe2].value!='')
						{ warning+='montant liquidation pas vide'+'\n';
						}
					}
				}
			}
		}
	}
	// verif que date commande dans [datedeb_contrat,datefin_ieb], [datedeb_eotp,datefin_eotp]
	
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ if(warning!="")
		{ return confirm(warning+'\n'+'\n'+'Des champs ne sont pas vides : confirmez-vous la suppression ou non ?');
		}
		else
		{ return true;
		}
	}
}

function controle_form_mission(formulaire)  
{ erreur="";
	warning="";
	frm=document.forms[formulaire];
	if(frm.elements['adresse_pers'] && longueur(frm.elements['adresse_pers'].value)>200) erreur+="Adresse personnelle trop long : "+longueur(frm.elements['adresse_pers'].value)+"\n";
	if(frm.elements['adresse_admin'] && longueur(frm.elements['adresse_admin'].value)>200) erreur+="Adresse administrative trop long : "+longueur(frm.elements['adresse_admin'].value)+"\n";
	if(frm.elements['note'] && longueur(frm.elements['note'].value)>200) erreur+="Note trop long : "+longueur(frm.elements['note'].value)+"\n";

	//champs obligatoires
	if(frm.elements['estfrance'] && !frm.elements['estfrance'].checked && frm.elements['estetranger'] && !frm.elements['estetranger'].checked)
	{ erreur+="En France ou a l'etranger ?!"+"\n";
	}
	if(frm.elements['nom'] && frm.elements['nom'].value==''){ erreur+="Nom obligatoire."+"\n";}
	if(frm.elements['prenom'] && frm.elements['prenom'].value==''){ erreur+="Prenom obligatoire."+"\n";}
	if(frm.elements['motif'] && frm.elements['motif'].value==''){ erreur+="Motif obligatoire."+"\n";}
	if(frm.elements['codecatmissionnaire'] && frm.elements['codecatmissionnaire'].value=='') erreur+="Type d'agent obligatoire"+"\n";
	if(frm.elements['date_naiss_jj'] && frm.elements['date_naiss_jj'].value==''){ erreur+="Date de naissance obligatoire."+"\n";}
	if(frm.elements['codesecrsite'] && frm.elements['codesecrsite'].value==''){ erreur+="Secrétaire obligatoire."+"\n";}	
	if(frm.elements['adresse_pers'] && frm.elements['adresse_pers'].value=='' && frm.elements['adresse_admin'] && frm.elements['adresse_admin'].value=='')
	{ warning+="Aucune adresse."+"\n";
	}
	if(frm.elements['adresse_pers'] && frm.elements['adresse_pers'].value!='')
	{ if(frm.elements['ville_pers'] && frm.elements['ville_pers'].value==''){ erreur+="Ville adresse pers. obligatoire"+"\n";}
	  if(frm.elements['codepays_pers'] && frm.elements['codepays_pers'].value==''){ erreur+="Pays adresse pers. obligatoire"+"\n";}
	}

	nbetape=0;
	for(i=0;i<frm.elements.length;i++)
	{ if(frm.elements[i].name.substring(0,(new String('departlieu#')).length)=='departlieu#')
		{ suffixe=frm.elements[i].name.substring((new String('departlieu')).length);
			departlieu=frm.elements[i].value;
			if(departlieu!='')
			{ nbetape++;
				if(frm.elements['arriveelieu'+suffixe].value=='' || frm.elements['arriveelieu'+suffixe].value=='' || frm.elements['departdate_jj'+suffixe.value]=='' || frm.elements['arriveedate_jj'+suffixe].value=='')
				{ erreur+="Au moins une etape est incomplete : lieu ou date"+"\n";
				}
			}
		}
	}
	if(nbetape<2)
	{ erreur+="Il faut au moins deux lignes depart-arrivee (A/R)."+"\n";
	}
	
	// champs numeriques avec un . ( , transformee en .)
	var tab_numerique={	"nbperstransporte" : {"lib" : "Nombre de personnes transportées"},
											"nbrepascharge" : {"lib" : "Nombre de repas"},"nbnuitshotelcharge" : {"lib" : "Nombre de nuitées"},
											"forfait" : {"lib" : "Forfait"},
											"heuredepartcongres" : {"lib" : "Heure départ congres"},
											"heurearriveecongres" : {"lib" : "Heure arrivée congres"},
											"montantestimemission" : {"lib" : "Montant estime de la mission"}											
										};
	for(var champ_numerique in tab_numerique)
	{ if(frm.elements[champ_numerique])
		{ frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(",", "g"), ".");
			frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(" ", "g"), "");
			if(frm.elements[champ_numerique].value!='' && isNaN(frm.elements[champ_numerique].value)) erreur+="Champ "+tab_numerique[champ_numerique].lib+" non numérique !"+"\n";
		}
	}

	//champs dates
	var tab_champs_date={	"date_naiss" : {"lib" : "Date de naissance","jj" : "","mm" : "","aaaa" : ""},
												"dateabonneairfranceexpire" : {"lib" : "Date d'expiration abonnement Air France","jj" : "","mm" : "","aaaa" : ""},
												"departdate" : {"lib" : "Date depart","jj" : "","mm" : "","aaaa" : ""},
												"arriveedate" : {"lib" : "Date arrivee","jj" : "","mm" : "","aaaa" : ""},
												"datedeb_congres" : {"lib" : "Date debut congres","jj" : "","mm" : "","aaaa" : ""},
												"datefin_congres" : {"lib" : "Date fin congres","jj" : "","mm" : "","aaaa" : ""},
												"datedepartcongres" : {"lib" : "Date depart congres","jj" : "","mm" : "","aaaa" : ""},
												"datearriveecongres" : {"lib" : "Date arrivee congres","jj" : "","mm" : "","aaaa" : ""},
												"dateabonnetrainexpire" : {"lib" : "Date expiration abonnement train","jj" : "","mm" : "","aaaa" : ""},
												"dateabonneavionexpire" : {"lib" : "Date expiration abonnement avion","jj" : "","mm" : "","aaaa" : ""}
												};
	for(champ_date in tab_champs_date)
	{ for(i=0;i<frm.elements.length;i++) 
		{	if(frm.elements[i].name.substring(0,(new String(champ_date+'_jj')).length) ==champ_date+'_jj')
			{ suffixe='';
				chaine=new String(champ_date+'_jj');
				if(frm.elements[i].name.length > chaine.length)
				{ suffixe=frm.elements[i].name.substring(chaine.length);
				}
				if(longueur(frm.elements[champ_date+'_jj'+suffixe].value)==1)
				{ frm.elements[champ_date+'_jj'+suffixe].value='0'+frm.elements[champ_date+'_jj'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_mm'+suffixe].value)==1)
				{ frm.elements[champ_date+'_mm'+suffixe].value='0'+frm.elements[champ_date+'_mm'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_aaaa'+suffixe].value)==2)
				{ frm.elements[champ_date+'_aaaa'+suffixe].value='20'+frm.elements[champ_date+'_aaaa'+suffixe].value;
				}			
				champ_date_jj=frm.elements[champ_date+'_jj'+suffixe].value;
				champ_date_mm=frm.elements[champ_date+'_mm'+suffixe].value;
				champ_date_aaaa=frm.elements[champ_date+'_aaaa'+suffixe].value;
				if(!est_date(champ_date_jj,champ_date_mm,champ_date_aaaa))
				{ erreur+='Date mal formée : '+champ_date_jj+'/'+champ_date_mm+'/'+champ_date_aaaa+"\n";
				}
			}
		}
	}

	// champs heure mn
	var tab_champs_heure_mn={"departheure" : {"lib" : "Heure depart","hh" : "","mn" : ""},
													 "arriveeheure" : {"lib" : "Heure arrivee","hh" : "","mn" : ""},
													 "heuredepartcongres" : {"lib" : "Heure depart congres","hh" : "","mn" : ""},
													 "heurearriveecongres" : {"lib" : "Heure arrivee congres","hh" : "","mn" : ""}};
	for( var champ_heure_mn in tab_champs_heure_mn)
	{ for(i=0;i<frm.elements.length;i++)
		{ if(frm.elements[i].name.substring(0,(new String(champ_heure_mn+'_hh')).length)==champ_heure_mn+'_hh')
			{ suffixe='';
				chaine=new String(champ_heure_mn+'_hh');
				if(frm.elements[i].name.length > chaine.length)
				{ suffixe=frm.elements[i].name.substring(chaine.length);
				}
				if(frm.elements[champ_heure_mn+'_hh'+suffixe] && frm.elements[champ_heure_mn+'_mn'+suffixe])
				{	if(longueur(frm.elements[champ_heure_mn+'_hh'+suffixe].value)==1)
					{ frm.elements[champ_heure_mn+'_hh'+suffixe].value='0'+frm.elements[champ_heure_mn+'_hh'+suffixe].value;
					}
					if(longueur(frm.elements[champ_heure_mn+'_mn'+suffixe].value)==1)
					{ frm.elements[champ_heure_mn+'_mn'+suffixe].value='0'+frm.elements[champ_heure_mn+'_mn'+suffixe].value;
					}
					else if(longueur(frm.elements[champ_heure_mn+'_hh'+suffixe].value)!=0 && longueur(frm.elements[champ_heure_mn+'_mn'+suffixe].value)==0)
					{ frm.elements[champ_heure_mn+'_mn'+suffixe].value='00';
					}
					tab_champs_heure_mn[champ_heure_mn]['hh']=frm.elements[champ_heure_mn+'_hh'+suffixe].value;
					tab_champs_heure_mn[champ_heure_mn]['mn']=frm.elements[champ_heure_mn+'_mn'+suffixe].value;
					if(!est_heure_mn(tab_champs_heure_mn[champ_heure_mn]['hh'],tab_champs_heure_mn[champ_heure_mn]['mn']))
					{ erreur+=tab_champs_heure_mn[champ_heure_mn]['lib']+' mal formée : '+tab_champs_heure_mn[champ_heure_mn]['hh']+'H'+tab_champs_heure_mn[champ_heure_mn]['mn']+"\n";
					}
				}
			}
		}
	}
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ if(warning!='')
		{ return confirm(warning);
		}
		else
		{ return true;
		}
	}
}

function controle_form_dupliquer_commande(formulaire)
{ erreur="";
	warning="";
	frm=document.forms[formulaire];
	if(frm.elements['libfournisseur'] && longueur(frm.elements['libfournisseur'].value)>100) erreur+="Fournisseur trop long : "+longueur(frm.elements['libfournisseur'].value)+"\n";
	
	if(frm.elements['codereferent'] && frm.elements['codereferent'].value==''){ erreur+="Référent obligatoire."+"\n";}
	if(frm.elements['libfournisseur'] && frm.elements['libfournisseur'].value==''){ erreur+="Fournisseur obligatoire."+"\n";}
	//champs dates
	var tab_champs_date={	"datecommande" : {"lib" : "Date de commande","jj" : "","mm" : "","aaaa" : ""}};
	for(champ_date in tab_champs_date)
	{ for(i=0;i<frm.elements.length;i++) 
		{	if(frm.elements[i].name.substring(0,(new String(champ_date+'_jj')).length) ==champ_date+'_jj')
			{ suffixe='';
				chaine=new String(champ_date+'_jj');
				if(frm.elements[i].name.length > chaine.length)
				{ suffixe=frm.elements[i].name.substring(chaine.length);
				}
				if(longueur(frm.elements[champ_date+'_jj'+suffixe].value)==1)
				{ frm.elements[champ_date+'_jj'+suffixe].value='0'+frm.elements[champ_date+'_jj'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_mm'+suffixe].value)==1)
				{ frm.elements[champ_date+'_mm'+suffixe].value='0'+frm.elements[champ_date+'_mm'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_aaaa'+suffixe].value)==2)
				{ frm.elements[champ_date+'_aaaa'+suffixe].value='20'+frm.elements[champ_date+'_aaaa'+suffixe].value;
				}			
				champ_date_jj=frm.elements[champ_date+'_jj'+suffixe].value;
				champ_date_mm=frm.elements[champ_date+'_mm'+suffixe].value;
				champ_date_aaaa=frm.elements[champ_date+'_aaaa'+suffixe].value;
				if(!est_date(champ_date_jj,champ_date_mm,champ_date_aaaa))
				{ erreur+='Date mal formée : '+champ_date_jj+'/'+champ_date_mm+'/'+champ_date_aaaa+"\n";
				}
			}
		}
	}
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ if(warning!='')
		{ return confirm(warning);
		}
		else
		{ return true;
		}
	}
}

function controle_form_contrateotp(formulaire)
{ return "";
}

function controle_form_eotp_source_masse(formulaire)
{	erreur="";
	warning="";
	frm=document.forms[formulaire];
	erreur+=((frm.elements['eotp_ou_source'].value=='source' && frm.elements['libcourt'].value=='' && frm.elements['codetypesource'].value=='')?'Type de source et libelle vides':'');
	if(erreur!="")
	{ alert(erreur); 
		return false;
	}	
	else 
	{ if(warning!='')
		{ return confirm(warning);
		}
		else
		{ return true;
		}
	}
}

function controle_form_actu(formulaire)
{	erreur="";
	frm=document.forms[formulaire];
	if(erreur!=""){ alert(erreur); return false;}	else { return true;}
}	

function controle_form_registre_hs(formulaire)
{	erreur="";
	frm=document.forms[formulaire];
	if(longueur(frm.elements['lieu'].value)>100) erreur+="Lieu trop long : "+longueur(frm.elements['lieu'].value)+"\n";
	if(longueur(frm.elements['lieu'].value)==0) erreur+="Lieu obligatoire"+"\n";
	if(longueur(frm.elements['fait'].value)>100) erreur+="Fait trop long : "+longueur(frm.elements['fait'].value)+"\n";
	if(longueur(frm.elements['fait'].value)==0) erreur+="Fait obligatoire"+"\n";
	if(longueur(frm.elements['nom'].value)==0) erreur+="Nom obligatoire"+"\n";

	if(erreur!=""){ alert(erreur); return false;}	else { return true;}
}	

function controle_form_projet(formulaire)
{ erreur="";
	frm=document.forms[formulaire];
	
	if(frm.elements['titrecourt'] && longueur(frm.elements['titrecourt'].value)>50) erreur+="Description trop long : "+longueur(frm.elements['titrecourt'].value)+"\n";
	if(frm.elements['titre'] && longueur(frm.elements['titre'].value)>200) erreur+="Intitulé trop long : "+longueur(frm.elements['titre'].value)+"\n";
	if(frm.elements['partenaires'] && longueur(frm.elements['partenaires'].value)>500) erreur+="Partenaires trop long : "+longueur(frm.elements['partenaires'].value)+"\n";
	if(frm.elements['descr'] && longueur(frm.elements['descr'].value)>4000) erreur+="Description trop long : "+longueur(frm.elements['descr'].value)+"\n";
	if(frm.elements['specificites'] && longueur(frm.elements['specificites'].value)>1000)  erreur+="Specificites trop long : "+longueur(frm.elements['specificites'].value)+"\n";
	if(frm.elements['note'] && longueur(frm.elements['note'].value)>1000)  erreur+="Note trop long : "+longueur(frm.elements['note'].value)+"\n";
 	//champs obligatoires
	if(frm.elements['titrecourt'] && frm.elements['titrecourt'].value=='') erreur+="Acronyme ou titre court obligatoire"+"\n";
	if(frm.elements['titre'] && frm.elements['titre'].value=='') erreur+="Intitule obligatoire"+"\n";
	if(frm.elements['codeclassif'] && frm.elements['codeclassif'].value=='') erreur+="Contexte obligatoire"+"\n";
	if(frm.elements['codetypeprojet'] && frm.elements['codetypeprojet'].value=='') erreur+="Type obligatoire"+"\n";
	if(frm.elements['codeimplication'] && frm.elements['codeimplication'].value=='') erreur+="Niveau d'implication obligatoire"+"\n";
	if(frm.elements['partenaires'] && frm.elements['partenaires'].value=='') erreur+="Partenaires obligatoire"+"\n";
	
	// nombres
	// champs numeriques avec un . ( , transformee en .)
	var tab_numerique={	"duree_mois" : {"lib" : "Duree mois"},
											"montant_total" : {"lib" : "Montant total"},
											"montant_labo" : {"lib" : "Montant laboratoire"}
										};
	for(var champ_numerique in tab_numerique)
	{ if(frm.elements[champ_numerique])
		{ frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(",", "g"), ".");
			frm.elements[champ_numerique].value=(frm.elements[champ_numerique].value).replace(new RegExp(" ", "g"), "");
			if(frm.elements[champ_numerique].value!='' && isNaN(frm.elements[champ_numerique].value)) erreur+="Champ "+tab_numerique[champ_numerique].lib+" non numerique !"+"\n";
		}
	}

	//champs dates
	var tab_champs_date={	"datedeb_projet" : {"lib" : "Date debut du projet","jj" : "","mm" : "","aaaa" : ""},
											  "datelimite_depot_projet" : {"lib" : "Date limite de depot du projet","jj" : "","mm" : "","aaaa" : ""},
											  "datedepot_projet" : {"lib" : "Date de depot du projet","jj" : "","mm" : "","aaaa" : ""}
												 };
	for(champ_date in tab_champs_date)
	{ for(i=0;i<frm.elements.length;i++) 
		{	if(frm.elements[i].name.substring(0,(new String(champ_date+'_jj')).length) ==champ_date+'_jj')
			{ suffixe='';
				chaine=new String(champ_date+'_jj');
				if(frm.elements[i].name.length > chaine.length)
				{ suffixe=frm.elements[i].name.substring(chaine.length);
				}
				if(longueur(frm.elements[champ_date+'_jj'+suffixe].value)==1)
				{ frm.elements[champ_date+'_jj'+suffixe].value='0'+frm.elements[champ_date+'_jj'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_mm'+suffixe].value)==1)
				{ frm.elements[champ_date+'_mm'+suffixe].value='0'+frm.elements[champ_date+'_mm'+suffixe].value;
				}
				if(longueur(frm.elements[champ_date+'_aaaa'+suffixe].value)==2)
				{ frm.elements[champ_date+'_aaaa'+suffixe].value='20'+frm.elements[champ_date+'_aaaa'+suffixe].value;
				}			
				champ_date_jj=frm.elements[champ_date+'_jj'+suffixe].value;
				champ_date_mm=frm.elements[champ_date+'_mm'+suffixe].value;
				champ_date_aaaa=frm.elements[champ_date+'_aaaa'+suffixe].value;
				if(!est_date(champ_date_jj,champ_date_mm,champ_date_aaaa))
				{ erreur+='Date mal formée : '+champ_date_jj+'/'+champ_date_mm+'/'+champ_date_aaaa+"\n";
				}
			}
		}
	}
	if(erreur!=""){ alert(erreur); return false;}	else { return true;}
}
	
function est_date(jj,mm,aaaa)
{ var tab_nb_jours_du_mois=[31,28,31,30,31,30,31,31,30,31,30,31];
	if(jj+mm+aaaa=='')//tous les champs vides : OK
	{ return true;
	}
	else if(jj=='' || mm=='' || aaaa=='')//un champ vide et jj+mm+aaaa pas vide : pas OK
  { return false;
	}
	else
	{ unjourbissextile=0
		if(parseInt(aaaa,10)%4==0 && parseInt(mm,10)==2)
		{ unjourbissextile=1
		}
		if(!tab_nb_jours_du_mois[parseInt(mm,10)-1])
		{ return false;
		}
/*alert(jj+mm+aaaa+(parseInt(aaaa,10)%4)+'&&'+ parseInt(mm,10)+' '+
	' '+parseInt(jj,10)+' '+tab_nb_jours_du_mois[parseInt(mm,10)-1]+' '+unjourbissextile); */
		if(parseInt(jj,10)<1 || parseInt(jj,10) > tab_nb_jours_du_mois[parseInt(mm,10)-1]+unjourbissextile || parseInt(mm,10) <1 || parseInt(mm,10)>12 || (aaaa.length >=3 && parseInt(aaaa,10) <1900) || isNaN(jj) || isNaN(mm) || isNaN(aaaa))
		{ return false;
		}
		else
		{ return true;
		}
	}
}

function est_champ_jour(champ)
{ if(champ.value!='')
	{ if(isNaN(champ.value) || champ.value<1 || champ.value>31) 
		{
		return false;
		}
		else
		{ return true;
		}
	}
	else
	{ return true;
	}
}

function est_champ_mois(champ)
{ if(champ.value!='')
	{ if(isNaN(champ.value) || champ.value<1 || champ.value>12) 
		{
		return false;
		}
		else
		{ return true;
		}
	}
	else
	{ return true;
	}
}

function est_champ_annee(champ)
{ if(champ.value!='')
	{ if(champ.value.length==2)
		{ champ.value='20'+champ.value;};
		if(isNaN(champ.value) || champ.value.length!=4) 
		{
		return false;
		}
		else
		{ return true;
		}
	}
	else
	{ return true;
	}
}

function est_mail(mail) 
{ if(mail=='') return true;
  if ((mail.indexOf("@")>=0)&&(mail.indexOf(".")>=0))  return true;
	else return false;
}

function est_heure_mn(hh,mn)
{ if(hh+mn=='')//tous les champs vides : OK
	{ return true;
	}
	else
	{ if(hh<0 || hh>23 || mn<0 || mn>60 || isNaN(hh) || isNaN(mn))
		{ return false;
		}
		else
		{ return true;
		}
	}
}
function affiche_longueur(champ,longueurmax,champaffichage,class_si_ok,class_si_pasok)
{	if (document.getElementById(champaffichage)) 
	{ var lg_champ=longueur(champ.value);
	  if(lg_champ > longueurmax)
		{ document.getElementById(champaffichage).className = class_si_pasok;
		}
		else
		{ document.getElementById(champaffichage).className = class_si_ok;
		}
		document.getElementById(champaffichage).innerHTML = lg_champ;
	}
	//if(champ.name=='titre_fr')alert (champ.name+' '+champ.value+' '+lg_champ)
}

function longueur(chaine)
{ var nb_cr=0;//un CR : compte un de plus car php compte 2 caracteres pour un CR
	pos_cr=new String(chaine).indexOf(String.fromCharCode(10),0);
	while(pos_cr!=-1)
	{nb_cr++;
	 pos_cr=new String(chaine).indexOf(String.fromCharCode(10),pos_cr+1);
	}
	return chaine.length + nb_cr;
}

function intersectionperiodes(datedeb_periode1,datefin_periode1,datedeb_periode2,datefin_periode2)
{ return (datedeb_periode1>=datedeb_periode2 && (datedeb_periode1<=datefin_periode2 || datefin_periode2==''))
					 || (datefin_periode1>=datedeb_periode2 && (datefin_periode1<=datefin_periode2 || datefin_periode2==''))
					 || (datedeb_periode2>=datedeb_periode1 && (datedeb_periode2<=datefin_periode1 || datefin_periode1==''))
					 || (datefin_periode2>=datedeb_periode1 && (datefin_periode2<=datefin_periode1 || datefin_periode1==''));
}

function formate_nombre(formulaire,nombre,sep_decimal,nb_decimal,sep_millier) 
{	var frm=document.forms[formulaire];
	var pospoint=0;
	var partieentiere='', partiedecimale='', nombreformate='';
	if(nombre!='')
	{ nombre=nombre.replace(new RegExp(",", "g"), ".");
		nombre=nombre.replace(new RegExp(" ", "g"), "");
		if(nombre!='' && isNaN(nombre))
		{ alert("Champ non numérique !");
		}
		else
		{	if(nombre.indexOf('.')==-1)//nombre='.'
			{ nombre+='.';
			}
			pospoint=nombre.indexOf('.');
			if(pospoint==0)//nombre='0.'
			{ nombre='0'+nombre;
				pospoint++;
			}
			if(nombre.length-pospoint>3)//tronque nombre a 2 chiffres apres le '.'
			{ nombre=nombre.substring(0,pospoint+3);
			}
			partieentiere=nombre.substring(0,nombre.indexOf('.'));
			partiedecimale=nombre.substring(nombre.indexOf('.')+1);
			for(i=partiedecimale.length;i<nb_decimal;i++)
			{ partiedecimale+='0';
			}
			first=true;
			while(partieentiere.length>3)
			{ if(first)
				{ nombreformate=partieentiere.substring(partieentiere.length-3);
				}
				else
				{ nombreformate=partieentiere.substring(partieentiere.length-3)+sep_millier+nombreformate;
				}
				first=false;
				partieentiere=partieentiere.substring(0,partieentiere.length-3);
			}
			nombreformate=partieentiere+sep_millier+nombreformate;
			nombre=nombreformate+'.'+partiedecimale;
		}
	}
	return nombre;
}

function duree_aaaammjj(datedeb, datefin)
{ tab_nbjours_du_mois=[0,31,28,31,30,31,30,31,31,30,31,30,31];//0 en indice 0 pour debuter a l'indice 1
	jourdeb=parseInt(datedeb.substring(8),10);
	jourfin=parseInt(datefin.substring(8),10);
	moisdeb=parseInt(datedeb.substring(5,7),10);
	moisfin=parseInt(datefin.substring(5,7),10);
	anneedeb=parseInt(datedeb.substring(0,4),10);
	anneefin=parseInt(datefin.substring(0,4),10);
	jourdebbissextile=(anneedeb%4==0 && moisdeb==2)?1:0;
	jourfinbissextile=(anneefin%4==0 && moisfin==2)?1:0;
	nbjours=0;
	nbmois=0;
	nbannees=anneefin-anneedeb;
	if(datedeb!='' && datefin!='' && datedeb<=datefin && jourdeb<=tab_nbjours_du_mois[moisdeb]+jourdebbissextile && jourfin<=tab_nbjours_du_mois[moisfin]+jourfinbissextile)
	{ if(moisfin-moisdeb<0)
		{	nbmois=moisfin+12-moisdeb;
			nbannees-=1;
		}
		else
		{ nbmois=moisfin-moisdeb;
		}
		if(jourfin!=jourdeb-1)
		{ nbjours=(tab_nbjours_du_mois[moisdeb]+jourdebbissextile-jourdeb+1)+jourfin;
			if(nbjours>=tab_nbjours_du_mois[moisdeb]+jourdebbissextile)
			{ nbjours=nbjours-(tab_nbjours_du_mois[moisdeb]+jourdebbissextile);
				if(nbjours>=tab_nbjours_du_mois[moisfin]+jourfinbissextile)
				{ nbjours=nbjours-(tab_nbjours_du_mois[moisfin]+jourfinbissextile);
					nbmois++;
				}
			}
			else
			{ nbmois--;
			}
		}
		if(nbmois==12)
		{ nbmois=0;
			nbannees+=1;
		}
		tab_duree={a:nbannees, m:nbmois, j:nbjours};
		return tab_duree;
	}
	return false;
}

function getOffset(e) 
{ var cx = 0;
	var cy = 0;

	while(e && !isNaN(e.offsetLeft) && !isNaN(e.offsetTop)) 
	{ cx += e.offsetLeft - e.scrollLeft;
		cy += e.offsetTop - e.scrollTop;
		e = e.offsetParent;
	}
	return { top: cy, left: cx };
}

function posmouse(event,element)
{ e=event
	if(e.offsetX || e.offsetY) 
	{		x = e.pageX - getOffset(element).left - window.pageXOffset;//
			y = e.pageY - getOffset(element).top - window.pageYOffset;//
	}
	else if(e.layerX || e.layerY) {
			x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft- getOffset(element).left - window.pageXOffset;//
			y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop - getOffset(element).top - window.pageYOffset;//
	}
	return {x:x, y:y }
} /**/  

