%%
%% Bib. style "alpha-fr" (version francisee de alpha.bst)
%%
%% NM, 2004/02/22
%% markey@lsv.ens-cachan.fr 
%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
 % Original Copyright (Oren Patashnik):
 %
 % This program may be distributed and/or modified under the
 % conditions of the LaTeX Project Public License, either version 1.2
 % of this license or (at your option) any later version.
 % The latest version of this license is in
 %   http://www.latex-project.org/lppl.txt
 % and version 1.2 or later is part of all distributions of LaTeX
 % version 1999/12/01 or later.
 %
 % This program consists of the file alpha.bst
 %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


ENTRY
  { address
    author
    booktitle
    chapter
    edition
    editor
    howpublished
    institution
    journal
    key
    month
    note
    number
    organization
    pages
    publisher
    school
    series
    title
    type
    volume
    year
    equipe
    category
    diffusion
  }
  {}
  { label extra.label sort.label }

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%
%% C'est ici que je definis les "traductions". Normalement, y a 
%% que ca a changer pour franciser le style...
%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

FUNCTION{fr.and}{		%% le "et" entre les deux derniers auteurs
" et "
}

FUNCTION{fr.editeur}{		%% ", \'editeur" au singulier, ou " (\'editeur)"...
", \'editeur"
}

FUNCTION{fr.editeurs}{		%% ", \'editeurs" au pluriel, ou " (\'editeurs)"...
", \'editeurs"
}

FUNCTION{fr.et.al}{		%% " et~al."
" \emph{et~al.}"
}

FUNCTION{fr.in}{		%% "Dans "
"\emph{In} "
}

FUNCTION{fr.in.spc}{		%% " de " (ou " de la s\'erie ")
" de "
}

FUNCTION{fr.of}{		%% " de "
" de "
}

FUNCTION{fr.number}{		%% "num\'ero "
"num\'ero "
}

FUNCTION{fr.number.maj}{	%% "Num\'ero "
"Num\'ero "
}

FUNCTION{fr.volume}{		%% "volume"
"volume"
}

FUNCTION{fr.volume.maj}{	%% "Volume"
"Volume"
}

FUNCTION{fr.edition}{		%% " \'edition"
" \'edition"
}

FUNCTION{fr.pages}{		%% "pages"
"pages"
}

FUNCTION{fr.page}{		%% "page"
"page"
}

FUNCTION{fr.chapter}{		%% "chapitre"
"chapitre"
}

FUNCTION{fr.tech.rep}{		%% "Rapport Technique"
"Rapport technique"
}

FUNCTION{fr.master}{		%% "M\'emoire de D.E.A."
"M\'emoire de D.E.A."
}

FUNCTION{fr.phd}{		%% "Th\`ese de doctorat"
"Th\`ese de doctorat"
}

FUNCTION{fr.auteurs.style}{	
	%% si on veut des small caps sur le LASTNAME de l'auteur
	%% Cette fonction est utilisee dans la definition d'une
	%% fonction LaTeX.
"\scshape"
}

FUNCTION{fr.ponctuation.apres.auteurs}{ %% Comme son nom l'indique...
" : "
}

FUNCTION{fr.deuxpoints}{	%% separateur num. journal/num. pages
"\string:\penalty500\relax "
}

MACRO {jan} {"janvier"}
MACRO {feb} {"f\'evrier"}
MACRO {mar} {"mars"}
MACRO {apr} {"avril"}
MACRO {may} {"mai"}
MACRO {jun} {"juin"}
MACRO {jul} {"juillet"}
MACRO {aug} {"ao\^ut"}
MACRO {sep} {"septembre"}
MACRO {oct} {"octobre"}
MACRO {nov} {"novembre"}
MACRO {dec} {"d\'ecembre"}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%
%% La suite, normalement, y a rien a changer...
%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%





INTEGERS { output.state before.all mid.sentence after.sentence after.block after.authors }

FUNCTION {init.state.consts}
{ #0 'before.all :=
  #1 'mid.sentence :=
  #2 'after.sentence :=
  #3 'after.block :=
  #0 'after.authors :=
}

STRINGS { s t }

FUNCTION{ fr.add.period }{
after.authors 
  { fr.ponctuation.apres.auteurs empty$
      { add.period$ }
      { fr.ponctuation.apres.auteurs * #0 'after.authors := }
    if$}
  { add.period$ }
if$
}

FUNCTION {output.nonnull}
{ 's :=
  output.state mid.sentence =
    { ", " * write$ }
    { output.state after.block =
	{ fr.add.period write$
	  newline$
	  "\newblock " write$
	}
	{ output.state before.all =
	    'write$
	    { add.period$ " " * write$ }
	  if$
	}
      if$
      mid.sentence 'output.state :=
    }
  if$
  s
}

FUNCTION {output}
{ duplicate$ empty$
    'pop$
    'output.nonnull
  if$
}

FUNCTION {output.check}
{ 't :=
  duplicate$ empty$
    { pop$ "empty " t * " in " * cite$ * warning$ }
    'output.nonnull
  if$
}

FUNCTION {not}
{   { #0 }
    { #1 }
  if$
}

FUNCTION {and}
{   'skip$
    { pop$ #0 }
  if$
}

FUNCTION {or}
{   { pop$ #1 }
    'skip$
  if$
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%
%%%%% MODIFICATION PAR Didier Maquin MARS 2011                       %%%%%%%%%%%
%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


% S'appuie sur alpha-fr.bst et crée des étiquette adéquates en fonction des catégories

INTEGERS {nb.su11 nb.su12 nb.su2 nb.su3 nb.su4 nb.su5 nb.su61 nb.su62 nb.su7 nb.su8 nb.su9 nb.su10 nb.su111 nb.su112}
INTEGERS {maxwidth curwidth}
STRINGS {equ labequ lab maxlabel}


%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Détermine la longueur d'une chaîne
INTEGERS {l}
FUNCTION {string.length}
{
  #1 'l :=
  {duplicate$ duplicate$ #1 l substring$ = not}
     {l #1 + 'l :=}
  while$
  pop$ l
}   

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Recherche d'une chaîne de caractère dans une chaîne
STRINGS {chfind text}
INTEGERS {find_length trouve}

FUNCTION {find}
{ 'chfind :=
  'text :=
  chfind string.length 'find_length :=
  #0 'trouve :=
     { text empty$ not}
     { text #1 find_length substring$ chfind =
           {#1 'trouve := } 
           {skip$}
       if$
       text #2 global.max$ substring$ 'text := 
     }
     while$
     trouve
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
FUNCTION {init.val}
{
#1 'nb.su11 :=
#1 'nb.su12 :=
#1 'nb.su2 :=
#1 'nb.su3 :=
#1 'nb.su4 :=
#1 'nb.su5 :=
#1 'nb.su61 :=
#1 'nb.su62 :=
#1 'nb.su7 :=
#1 'nb.su8 :=
#1 'nb.su9 :=
#1 'nb.su10 :=
#1 'nb.su111 :=
#1 'nb.su112 :=
#0 'maxwidth :=
#0 'curwidth :=
"" 'maxlabel :=
"IRIS" 'equ :=
"Ir" 'labequ :=
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
FUNCTION {myand}
{   'skip$
    { pop$ #0 }
  if$
}
%%%% Revue Internationale %%%%%%%%%%%%%%%%%%%
FUNCTION {su11}
{equipe equ find category "1" =  diffusion "INT" = myand myand}

%%%% Revue Nationale %%%%%%%%%%%%%%%%%%%%%
FUNCTION {su12}
{equipe equ find category "1" =  diffusion "NAT" = myand myand}

%%%% Revue sans comité de lecture %%%%%%%%%%%%%%
FUNCTION {su2}
{equipe equ find category "2" =  myand}

%%%% Ouvrage %%%%%%%%%%%%%%
FUNCTION {su3}
{equipe equ find category "3" =  myand}

%%%% Chapitre d'ouvrage %%%%%%%%%%%%%%
FUNCTION {su4}
{equipe equ find category "4" =  myand}

%%%% Direction d'ouvrage %%%%%%%%%%%%%%
FUNCTION {su5}
{equipe equ find category "5" =  myand}

%%%% Conférence Internationale %%%%%%%%%%%%%%
FUNCTION {su61}
{equipe equ find category "6" =   diffusion "INT" = myand myand}

%%%% Conférence Nationale %%%%%%%%%%%%%%
FUNCTION {su62}
{equipe equ find category "6" =   diffusion "NAT" = myand myand}

%%%% Conférence sans Acte %%%%%%%%%%%%%%
FUNCTION {su7}
{equipe equ find category "7" =  myand}

%%%% Habilitation à Diriger des Recherches %%%%%%%%%%%%%%
FUNCTION {su8}
{equipe equ find category "8" =  myand}

%%%% Thèse %%%%%%%%%%%%%%
FUNCTION {su9}
{equipe equ find category "9" =  myand}

%%%% Rapport de Contrat %%%%%%%%%%%%%%
FUNCTION {su10}
{equipe equ find category "10" =  myand}

%%%% Conférence invitée Internationale (traitée comme Conférence Internationale%%%%%%%%%%%%%%%%%%%%%
FUNCTION {su111}
{equipe equ find category "11" =  diffusion "INT" = myand myand}

%%%% Conférence invitée Nationale (traitée comme Conférence Nationale%%%%%%%%%%%%%%%%%%%%%
FUNCTION {su112}
{equipe equ find category "11" =  diffusion "NAT" = myand myand}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Prépare le tri
FUNCTION {sortdm}
{
   su11
   {"A" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su12
   {"B" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su2
   {"C" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su3
   {"D" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su4
   {"E" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su5
   {"F" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su61
   {"G" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su62
   {"H" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su7
   {"I" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su8
   {"J" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su9
   {"K" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su10
   {"L" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su111
   {"G" year * label * 'sort.key$ :=}
   {"" write$}
if$
   su112
   {"H" year * label * 'sort.key$ :=}
   {"" write$}
if$
}

%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Elabore l'étiquette
FUNCTION {etiquette}
{
   su11
   {"[Acl." labequ * "." * nb.su11 int.to.str$ *  write$
   nb.su11 #1 + 'nb.su11 :=
   "]" write$ }
   {"" write$}
if$
   su12
   {"[Acln." labequ * "." * nb.su12 int.to.str$ *  write$ 
   nb.su12 #1 + 'nb.su12 :=
   "]" write$ }
   {"" write$}
if$
   su2
   {"[Ascl." labequ * "." * nb.su2 int.to.str$ * write$ 
   nb.su2 #1 + 'nb.su2 :=
   "]" write$ }
   {"" write$}
if$
   su3
   {"[Os." labequ * "." * nb.su3 int.to.str$ * write$ 
   nb.su3 #1 + 'nb.su3 :=
   "]" write$ }
   {"" write$}
if$
   su4
   {"[Osc." labequ * "." * nb.su4 int.to.str$ * write$ 
   nb.su4 #1 + 'nb.su4 :=
   "]" write$ }
   {"" write$}
if$
   su5
   {"[Do." labequ * "." * nb.su5 int.to.str$ * write$ 
    nb.su5 #1 + 'nb.su5 :=
   "]" write$ }
   {"" write$}
if$
   su61
   {"[Acti." labequ * "." * nb.su61 int.to.str$ * write$ 
   nb.su61 #1 + 'nb.su61 :=
   "]" write$ }
   {"" write$}
if$
   su62
   {"[Actn." labequ * "." * nb.su62 int.to.str$ * write$ 
   nb.su62 #1 + 'nb.su62 :=
   "]" write$ }
   {"" write$}
if$
   su7
   {"[Com." labequ * "." * nb.su7 int.to.str$ *  write$ 
   nb.su7 #1 + 'nb.su7 :=
   "]" write$ }
   {"" write$}
if$
   su8
   {"[Hdr." labequ * "." * nb.su8 int.to.str$ *  write$ 
   nb.su8 #1 + 'nb.su8 :=
   "]" write$ }
   {"" write$}
if$
   su9
   {"[The." labequ * "." * nb.su9 int.to.str$ * write$ 
   nb.su9 #1 + 'nb.su9 :=
   "]" write$ }
   {"" write$}
if$
   su10
   {"[Ap." labequ * "." * nb.su10 int.to.str$ * write$ 
   nb.su10 #1 + 'nb.su10 :=
   "]" write$ }
   {"" write$}
if$
   su111
   {"[Acti." labequ * "." * nb.su61 int.to.str$ *  write$ 
   nb.su61 #1 + 'nb.su61 :=
   "]" write$ }
   {"" write$}
if$
   su112
   {"[Actn." labequ * "." * nb.su62 int.to.str$ * write$ 
   nb.su62 #1 + 'nb.su62 :=
   "]" write$ }
   {"" write$}
if$
}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Détermine le label le plus grand
FUNCTION {longlabel}
{sort.key$ width$ 'curwidth :=
curwidth maxwidth >
{maxwidth 'curwidth := sort.key$ 'maxlabel :=}
{skip$}
if$
}
%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
FUNCTION {output.bibitem}
{ newline$
  "\bibitem" write$
  etiquette
  "{" write$
  cite$ write$
  "}" write$
  newline$
  ""
  before.all 'output.state :=
  #0 'after.authors :=
}

FUNCTION {fin.entry}
{ add.period$
  write$
  newline$
}

FUNCTION {new.block}
{ output.state before.all =
    'skip$
    { after.block 'output.state := }
  if$
}

FUNCTION {new.sentence}
{ output.state after.block =
    'skip$
    { output.state before.all =
	'skip$
	{ after.sentence 'output.state := }
      if$
    }
  if$
}



FUNCTION {new.block.checka}
{ empty$
    'skip$
    'new.block
  if$
}

FUNCTION {new.block.checkb}
{ empty$
  swap$ empty$
  and
    'skip$
    'new.block
  if$
}

FUNCTION {new.sentence.checka}
{ empty$
    'skip$
    'new.sentence
  if$
}

FUNCTION {new.sentence.checkb}
{ empty$
  swap$ empty$
  and
    'skip$
    'new.sentence
  if$
}

FUNCTION {field.or.null}
{ duplicate$ empty$
    { pop$ "" }
    'skip$
  if$
}

FUNCTION {emphasize}
{ duplicate$ empty$
    { pop$ "" }
    { "{\em " swap$ * "}" * }
  if$
}

INTEGERS { nameptr namesleft numnames }

FUNCTION {format.names}
{ 's :=
  #1 'nameptr :=
  s num.names$ 'numnames :=
  numnames 'namesleft :=
    { namesleft #0 > }
    { s nameptr "{ff~}{vv~}\bgroup\fonteauteurs\bgroup {ll}\egroup\egroup{{}}{, jj}" format.name$ 't :=
      nameptr #1 >
	{ namesleft #1 >
	    { ", " * t * }
	    {   %numnames #2 > namesleft #2 > and
		%  { "," * }
		%  'skip$
	        %if$
	      t "\bgroup\fonteauteurs\bgroup others\egroup\egroup{}" =
		{ fr.et.al * }
		{ fr.and * t * }
	      if$
	    }
	  if$
	}
	't
      if$
      nameptr #1 + 'nameptr :=
      namesleft #1 - 'namesleft :=
    }
  while$
}

FUNCTION {format.authors}
{ author empty$
    { "" }
    { author format.names #1 'after.authors := } 
  if$
}

FUNCTION {format.editors}
{ editor empty$
    { "" }
    { editor format.names
      editor num.names$ #1 >
	{ fr.editeurs * }
	{ fr.editeur * }
      if$
    }
  if$
}

FUNCTION {format.title}
{ title empty$
    { "" }
    { title "t" change.case$ }
  if$
}

FUNCTION {n.dashify}
{ 't :=
  ""
    { t empty$ not }
    { t #1 #1 substring$ "-" =
	{ t #1 #2 substring$ "--" = not
	    { "--" *
	      t #2 global.max$ substring$ 't :=
	    }
	    {   { t #1 #1 substring$ "-" = }
		{ "-" *
		  t #2 global.max$ substring$ 't :=
		}
	      while$
	    }
	  if$
	}
	{ t #1 #1 substring$ *
	  t #2 global.max$ substring$ 't :=
	}
      if$
    }
  while$
}

FUNCTION {format.date}
{ year empty$
    { month empty$
	{ "" }
	{ "there's a month but no year in " cite$ * warning$
	  month
	}
      if$
    }
    { month empty$
	'year
	{ month " " * year * }
      if$
    }
  if$
}

FUNCTION {format.btitle}
{ title emphasize
}

FUNCTION {tie.or.space.connect}
{ duplicate$ text.length$ #3 <
    { "~" }
    { " " }
  if$
  swap$ * *
}

FUNCTION {either.or.check}
{ empty$
    'pop$
    { "can't use both " swap$ * " fields in " * cite$ * warning$ }
  if$
}

FUNCTION {format.bvolume}
{ volume empty$
    { "" }
    { fr.volume volume tie.or.space.connect
      series empty$
	'skip$
	{ fr.of * series emphasize * }
      if$
      "volume and number" number either.or.check
    }
  if$
}

FUNCTION {format.number.series}
{ volume empty$
    { number empty$
	{ series field.or.null }
	{ output.state mid.sentence =
	    { fr.number }
	    { fr.number.maj }
	  if$
	  number tie.or.space.connect
	  series empty$
	    { "there's a number but no series in " cite$ * warning$ }
	    { fr.in.spc * series * }
	  if$
	}
      if$
    }
    { "" }
  if$
}

FUNCTION {format.edition}
{ edition empty$
    { "" }
    { output.state mid.sentence =
	{ edition "l" change.case$ fr.edition * }
	{ edition "t" change.case$ fr.edition * }
      if$
    }
  if$
}

INTEGERS { multiresult }

FUNCTION {multi.page.check}
{ 't :=
  #0 'multiresult :=
    { multiresult not
      t empty$ not
      and
    }
    { t #1 #1 substring$
      duplicate$ "-" =
      swap$ duplicate$ "," =
      swap$ "+" =
      or or
	{ #1 'multiresult := }
	{ t #2 global.max$ substring$ 't := }
      if$
    }
  while$
  multiresult
}

FUNCTION {format.pages}
{ pages empty$
    { "" }
    { pages multi.page.check
	{ fr.pages pages n.dashify tie.or.space.connect }
	{ fr.page pages tie.or.space.connect }
      if$
    }
  if$
}

FUNCTION {format.vol.num.pages}
{ volume field.or.null
  number empty$
    'skip$
    { "(" number * ")" * *
      volume empty$
	{ "there's a number but no volume in " cite$ * warning$ }
	'skip$
      if$
    }
  if$
  pages empty$
    'skip$
    { duplicate$ empty$
	{ pop$ format.pages }
	{ fr.deuxpoints * pages n.dashify * }
      if$
    }
  if$
}

FUNCTION {format.chapter.pages}
{ chapter empty$
    'format.pages
    { type empty$
	{ fr.chapter }
	{ type "l" change.case$ }
      if$
      chapter tie.or.space.connect
      pages empty$
	'skip$
	{ ", " * format.pages * }
      if$
    }
  if$
}

FUNCTION {format.in.ed.booktitle}
{ booktitle empty$
    { "" }
    { editor empty$
	{ fr.in booktitle emphasize * }
	{ fr.in format.editors #1 'after.authors := fr.add.period " " * * booktitle emphasize * }
      if$
    }
  if$
}

FUNCTION {empty.misc.check}
{ author empty$ title empty$ howpublished empty$
  month empty$ year empty$ note empty$
  and and and and and
  key empty$ not and
    { "all relevant fields are empty in " cite$ * warning$ }
    'skip$
  if$
}

FUNCTION {format.thesis.type}
{ type empty$
    'skip$
    { pop$
      type "t" change.case$
    }
  if$
}

FUNCTION {format.tr.number}
{ type empty$
    { fr.tech.rep }
    'type
  if$
  number empty$
    { "t" change.case$ }
    { number tie.or.space.connect }
  if$
}

FUNCTION {format.article.crossref}
{ key empty$
    { journal empty$
	{ "need key or journal for " cite$ * " to crossref " * crossref *
	  warning$
	  ""
	}
	{ fr.in "{\em " * journal * "\/}" * }
      if$
    }
    { fr.in key * }
  if$
  " \cite{" * crossref * "}" *
}

FUNCTION {format.crossref.editor}
{ editor #1 "{vv~}\bgroup\fonteauteurs\bgroup {ll}\egroup\egroup{{}}" format.name$
  editor num.names$ duplicate$
  #2 >
    { pop$ fr.et.al * }
    { #2 <
	'skip$
	{ editor #2 "{ff }{vv }{ll}{ jj}" format.name$ "others" =
	    { fr.et.al * }
	    { fr.and * editor #2 "{vv~}\bgroup\fonteauteurs\bgroup {ll}\egroup\egroup{{}}" format.name$ * }
	  if$
	}
      if$
    }
  if$
}

FUNCTION {format.book.crossref}
{ volume empty$
    { "empty volume in " cite$ * "'s crossref of " * crossref * warning$
      fr.in
    }
    { fr.volume.maj volume tie.or.space.connect
      fr.of *
    }
  if$
  editor empty$
  editor field.or.null author field.or.null =
  or
    { key empty$
	{ series empty$
	    { "need editor, key, or series for " cite$ * " to crossref " *
	      crossref * warning$
	      "" *
	    }
	    { "{\em " * series * "\/}" * }
	  if$
	}
	{ key * }
      if$
    }
    { format.crossref.editor * }
  if$
  " \cite{" * crossref * "}" *
}

FUNCTION {format.incoll.inproc.crossref}
{ editor empty$
  editor field.or.null author field.or.null =
  or
    { key empty$
	{ booktitle empty$
	    { "need editor, key, or booktitle for " cite$ * " to crossref " *
	      crossref * warning$
	      ""
	    }
	    { fr.in "{\em " * booktitle * "\/}" * }
	  if$
	}
	{ fr.in key * }
      if$
    }
    { fr.in format.crossref.editor * }
  if$
  " \cite{" * crossref * "}" *
}

FUNCTION {article}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  crossref missing$
    { journal emphasize "journal" output.check
      format.vol.num.pages output
      format.date "year" output.check
    }
    { format.article.crossref output.nonnull
      format.pages output
    }
  if$
  new.block
  note output
  fin.entry
}

FUNCTION {book}
{ output.bibitem
  author empty$
    { format.editors "author and editor" output.check }
    { format.authors output.nonnull
      crossref missing$
	{ "author and editor" editor either.or.check }
	'skip$
      if$
    }
  if$
  new.block
  format.btitle "title" output.check
  crossref missing$
    { format.bvolume output
      new.block
      format.number.series output
      new.sentence
      publisher "publisher" output.check
      address output
    }
    { new.block
      format.book.crossref output.nonnull
    }
  if$
  format.edition output
  format.date "year" output.check
  new.block
  note output
  fin.entry
}

FUNCTION {booklet}
{ output.bibitem
  format.authors output
  new.block
  format.title "title" output.check
  howpublished address new.block.checkb
  howpublished output
  address output
  format.date output
  new.block
  note output
  fin.entry
}

FUNCTION {inbook}
{ output.bibitem
  author empty$
    { format.editors "author and editor" output.check }
    { format.authors output.nonnull
      crossref missing$
	{ "author and editor" editor either.or.check }
	'skip$
      if$
    }
  if$
  new.block
  format.btitle "title" output.check
  crossref missing$
    { format.bvolume output
      format.chapter.pages "chapter and pages" output.check
      new.block
      format.number.series output
      new.sentence
      publisher "publisher" output.check
      address output
    }
    { format.chapter.pages "chapter and pages" output.check
      new.block
      format.book.crossref output.nonnull
    }
  if$
  format.edition output
  format.date "year" output.check
  new.block
  note output
  fin.entry
}

FUNCTION {incollection}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  crossref missing$
    { format.in.ed.booktitle "booktitle" output.check
      format.bvolume output
      format.number.series output
      format.chapter.pages output
      new.sentence
      publisher "publisher" output.check
      address output
      format.edition output
      format.date "year" output.check
    }
    { format.incoll.inproc.crossref output.nonnull
      format.chapter.pages output
    }
  if$
  new.block
  note output
  fin.entry
}

FUNCTION {inproceedings}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  crossref missing$
    { format.in.ed.booktitle "booktitle" output.check
      format.bvolume output
      format.number.series output
      format.pages output
      address empty$
	{ organization publisher new.sentence.checkb
	  organization output
	  publisher output
	  format.date "year" output.check
	}
	{ address output.nonnull
	  format.date "year" output.check
	  new.sentence
	  organization output
	  publisher output
	}
      if$
    }
    { format.incoll.inproc.crossref output.nonnull
      format.pages output
    }
  if$
  new.block
  note output
  fin.entry
}

FUNCTION {conference} { inproceedings }

FUNCTION {manual}
{ output.bibitem
  author empty$
    { organization empty$
	'skip$
	{ organization output.nonnull
	  address output
	}
      if$
    }
    { format.authors output.nonnull }
  if$
  new.block
  format.btitle "title" output.check
  author empty$
    { organization empty$
	{ address new.block.checka
	  address output
	}
	'skip$
      if$
    }
    { organization address new.block.checkb
      organization output
      address output
    }
  if$
  format.edition output
  format.date output
  new.block
  note output
  fin.entry
}

FUNCTION {mastersthesis}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  fr.master format.thesis.type output.nonnull
  school "school" output.check
  address output
  format.date "year" output.check
  new.block
  note output
  fin.entry
}

FUNCTION {misc}
{ output.bibitem
  format.authors output
  title howpublished new.block.checkb
  format.title output
  howpublished new.block.checka
  howpublished output
  format.date output
  new.block
  note output
  fin.entry
  empty.misc.check
}

FUNCTION {phdthesis}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.btitle "title" output.check
  new.block
  fr.phd format.thesis.type output.nonnull
  school "school" output.check
  address output
  format.date "year" output.check
  new.block
  note output
  fin.entry
}

FUNCTION {proceedings}
{ output.bibitem
  editor empty$
    { organization output }
    { format.editors output.nonnull }
  if$
  new.block
  format.btitle "title" output.check
  format.bvolume output
  format.number.series output
  address empty$
    { editor empty$
	{ publisher new.sentence.checka }
	{ organization publisher new.sentence.checkb
	  organization output
	}
      if$
      publisher output
      format.date "year" output.check
    }
    { address output.nonnull
      format.date "year" output.check
      new.sentence
      editor empty$
	'skip$
	{ organization output }
      if$
      publisher output
    }
  if$
  new.block
  note output
  fin.entry
}

FUNCTION {techreport}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  format.tr.number output.nonnull
  institution "institution" output.check
  address output
  format.date "year" output.check
  new.block
  note output
  fin.entry
}

FUNCTION {unpublished}
{ output.bibitem
  format.authors "author" output.check
  new.block
  format.title "title" output.check
  new.block
  note "note" output.check
  format.date output
  fin.entry
}

FUNCTION {default.type} { misc }

MACRO {acmcs} {"ACM Computing Surveys"}
MACRO {acta} {"Acta Informatica"}
MACRO {cacm} {"Communications of the ACM"}
MACRO {ibmjrd} {"IBM Journal of Research and Development"}
MACRO {ibmsj} {"IBM Systems Journal"}
MACRO {ieeese} {"IEEE Transactions on Software Engineering"}
MACRO {ieeetc} {"IEEE Transactions on Computers"}
MACRO {ieeetcad}
 {"IEEE Transactions on Computer-Aided Design of Integrated Circuits"}
MACRO {ipl} {"Information Processing Letters"}
MACRO {jacm} {"Journal of the ACM"}
MACRO {jcss} {"Journal of Computer and System Sciences"}
MACRO {scp} {"Science of Computer Programming"}
MACRO {sicomp} {"SIAM Journal on Computing"}
MACRO {tocs} {"ACM Transactions on Computer Systems"}
MACRO {tods} {"ACM Transactions on Database Systems"}
MACRO {tog} {"ACM Transactions on Graphics"}
MACRO {toms} {"ACM Transactions on Mathematical Software"}
MACRO {toois} {"ACM Transactions on Office Information Systems"}
MACRO {toplas} {"ACM Transactions on Programming Languages and Systems"}
MACRO {tcs} {"Theoretical Computer Science"}

READ

FUNCTION {sortify}
{ purify$
  "l" change.case$
}

INTEGERS { len }

FUNCTION {chop.word}
{ 's :=
  'len :=
  s #1 len substring$ =
    { s len #1 + global.max$ substring$ }
    's
  if$
}

INTEGERS { et.al.char.used }

FUNCTION {initialize.et.al.char.used}
{ #0 'et.al.char.used :=
}

EXECUTE {initialize.et.al.char.used}

FUNCTION {format.lab.names}
{ 's :=
  s num.names$ 'numnames :=
  numnames #1 >
    { numnames #4 >
        { #3 'namesleft := }
        { numnames 'namesleft := }
      if$
      #1 'nameptr :=
      ""
        { namesleft #0 > }
        { nameptr numnames =
            { s nameptr "{ff }{vv }{ll}{ jj}" format.name$ "others" =
                { "{\etalchar{+}}" *
                  #1 'et.al.char.used :=
                }
                { s nameptr "{v{}}{l{}}" format.name$ * }
              if$
            }
            { s nameptr "{v{}}{l{}}" format.name$ * }
          if$
          nameptr #1 + 'nameptr :=
          namesleft #1 - 'namesleft :=
        }
      while$
      numnames #4 >
        { "{\etalchar{+}}" *
          #1 'et.al.char.used :=
        }
        'skip$
      if$
    }
    { s #1 "{v{}}{l{}}" format.name$
      duplicate$ text.length$ #2 <
        { pop$ s #1 "{ll}" format.name$ #3 text.prefix$ }
        'skip$
      if$
    }
  if$
}

FUNCTION {author.key.label}
{ author empty$
    { key empty$
        { cite$ #1 #3 substring$ }
        { key #3 text.prefix$ }
      if$
    }
    { author format.lab.names }
  if$
}

FUNCTION {author.editor.key.label}
{ author empty$
    { editor empty$
        { key empty$
            { cite$ #1 #3 substring$ }
            { key #3 text.prefix$ }
          if$
        }
        { editor format.lab.names }
      if$
    }
    { author format.lab.names }
  if$
}

FUNCTION {author.key.organization.label}
{ author empty$
    { key empty$
        { organization empty$
            { cite$ #1 #3 substring$ }
            { "Le " #3
		"La " #3
		  "Les " #4 
		    "The " #4 organization chop.word 
		  chop.word
		chop.word
	      chop.word #3 text.prefix$ }
          if$
        }
        { key #3 text.prefix$ }
      if$
    }
    { author format.lab.names }
  if$
}

FUNCTION {editor.key.organization.label}
{ editor empty$
    { key empty$
        { organization empty$
            { cite$ #1 #3 substring$ }
            { "Le " #3
		"La " #3
		  "Les " #4 
		    "The " #4 organization chop.word 
		  chop.word
		chop.word
	      chop.word #3 text.prefix$ }
          if$
        }
        { key #3 text.prefix$ }
      if$
    }
    { editor format.lab.names }
  if$
}

FUNCTION {calc.label}
{ type$ "book" =
  type$ "inbook" =
  or
    'author.editor.key.label
    { type$ "proceedings" =
        'editor.key.organization.label
        { type$ "manual" =
            'author.key.organization.label
            'author.key.label
          if$
        }
      if$
    }
  if$
  duplicate$
  year field.or.null purify$ #-1 #2 substring$
  *
  'label :=
  year field.or.null purify$ #-1 #4 substring$
  *
  sortify 'sort.label :=
}

FUNCTION {sort.format.names}
{ 's :=
  #1 'nameptr :=
  ""
  s num.names$ 'numnames :=
  numnames 'namesleft :=
    { namesleft #0 > }
    { nameptr #1 >
        { "   " * }
        'skip$
      if$
      s nameptr "{vv{ } }{ll{ }}{  ff{ }}{  jj{ }}" format.name$ 't :=
      nameptr numnames = t "others" = and
        { "et al" * }
        { t sortify * }
      if$
      nameptr #1 + 'nameptr :=
      namesleft #1 - 'namesleft :=
    }
  while$
}

FUNCTION {sort.format.title}
{ 't :=
  "Le " #3
    "La " #3
      "Les " #4
        "Un " #3
          "Une " #4
            "Des " #4
	      "A " #2
		"An " #3
		  "The " #4 t chop.word
		chop.word
	      chop.word
	    chop.word
	  chop.word
	chop.word
      chop.word
    chop.word
  chop.word
  sortify
  #1 global.max$ substring$
}

FUNCTION {author.sort}
{ author empty$
    { key empty$
        { "to sort, need author or key in " cite$ * warning$
          ""
        }
        { key sortify }
      if$
    }
    { author sort.format.names }
  if$
}

FUNCTION {author.editor.sort}
{ author empty$
    { editor empty$
        { key empty$
            { "to sort, need author, editor, or key in " cite$ * warning$
              ""
            }
            { key sortify }
          if$
        }
        { editor sort.format.names }
      if$
    }
    { author sort.format.names }
  if$
}

FUNCTION {author.organization.sort}
{ author empty$
    { organization empty$
        { key empty$
            { "to sort, need author, organization, or key in " cite$ * warning$
              ""
            }
            { key sortify }
          if$
        }
        { "Le " #3 
	    "La " #3
	      "Les " #4
		"The " #4 organization chop.word  
	      chop.word
	    chop.word
	  chop.word
	  sortify }
      if$
    }
    { author sort.format.names }
  if$
}

FUNCTION {editor.organization.sort}
{ editor empty$
    { organization empty$
        { key empty$
            { "to sort, need editor, organization, or key in " cite$ * warning$
              ""
            }
            { key sortify }
          if$
        }
        { "Le " #3 
	    "La " #3
	      "Les " #4
		"The " #4 organization chop.word 
	      chop.word
	    chop.word
	  chop.word
	  sortify }
      if$
    }
    { editor sort.format.names }
  if$
}

FUNCTION {presort}
{ calc.label
  sort.label
  "    "
  *
  type$ "book" =
  type$ "inbook" =
  or
    'author.editor.sort
    { type$ "proceedings" =
        'editor.organization.sort
        { type$ "manual" =
            'author.organization.sort
            'author.sort
          if$
        }
      if$
    }
  if$
  *
  "    "
  *
  year field.or.null sortify
  *
  "    "
  *
  title field.or.null
  sort.format.title
  *
  #1 entry.max$ substring$
  'sort.key$ :=
}

ITERATE {presort}

SORT

STRINGS { longest.label last.sort.label next.extra }

INTEGERS { longest.label.width last.extra.num }

FUNCTION {initialize.longest.label}
{ "" 'longest.label :=
  #0 int.to.chr$ 'last.sort.label :=
  "" 'next.extra :=
  #0 'longest.label.width :=
  #0 'last.extra.num :=
}

FUNCTION {forward.pass}
{ last.sort.label sort.label =
    { last.extra.num #1 + 'last.extra.num :=
      last.extra.num int.to.chr$ 'extra.label :=
    }
    { "a" chr.to.int$ 'last.extra.num :=
      "" 'extra.label :=
      sort.label 'last.sort.label :=
    }
  if$
}


FUNCTION {reverse.pass}
{ next.extra "b" =
    { "a" 'extra.label := }
    'skip$
  if$
  label extra.label * 'label :=
  label width$ longest.label.width >
    { label 'longest.label :=
      label width$ 'longest.label.width :=
    }
    'skip$
  if$
  extra.label 'next.extra :=
}

EXECUTE {initialize.longest.label}

ITERATE {forward.pass}

REVERSE {reverse.pass}

FUNCTION {begin.bib}
{ et.al.char.used
    { "\newcommand{\etalchar}[1]{$^{#1}$}" write$ newline$ }
    'skip$
  if$
  preamble$ empty$
    'skip$
    { preamble$ write$ newline$ }
  if$
  "\begin{thebibliography}{"  "Actn.Su.888"  * "}" * write$ newline$
  "\expandafter\ifx\csname fonteauteurs\endcsname\relax" 
    write$ newline$
  "\def\fonteauteurs{" fr.auteurs.style * "}\fi" * write$ newline$ 
}

EXECUTE{init.val}

EXECUTE {init.state.consts}

ITERATE{sortdm}

SORT

EXECUTE{init.val}

ITERATE{longlabel}

EXECUTE {begin.bib}

ITERATE {call.type$}

FUNCTION {end.bib}
{ newline$
  "\end{thebibliography}" write$ newline$
}

EXECUTE {end.bib}


%%
%% Changelog : 
%% 1.0 -> 1.1 : les mois ne prennent pas de majuscule
%% 1.1 -> 1.2 : le '.' qui suit la liste des auteurs peut etre 
%% 		change en autre chose (':' notamment).
%%
