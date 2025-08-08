<?php
// Srpski (latinica) strings for block_ai4teachers

$string['pluginname'] = 'AI za nastavnike';
$string['openpromptbuilder'] = 'Otvori kreator AI promptova';
$string['notallowed'] = 'Ovaj alat je dostupan samo nastavnicima i menadžerima na kursu.';
$string['privacy:metadata'] = 'Blok AI za nastavnike ne čuva lične podatke.';

// Sposobnosti (capabilities)
$string['ai4teachers:manage'] = 'Korišćenje bloka AI za nastavnike na kursu';
$string['ai4teachers:addinstance'] = 'Dodavanje novog bloka AI za nastavnike';
$string['ai4teachers:myaddinstance'] = 'Dodavanje bloka AI za nastavnike na Kontrolnu tablu';

// Forme
$string['form:subjectlabel'] = 'Predmet';
$string['form:agerangelabel'] = 'Uzrast/razred učenika';
$string['form:topiclabel'] = 'Nastavna tema (oblast)';
$string['form:lessonlabel'] = 'Naziv lekcije (nastavne jedinice)';
$string['form:class_typelabel'] = 'Tip časa';
$string['form:outcomeslabel'] = 'Ishodi / ciljevi';
$string['form:language'] = 'Jezik prompta';
$string['form:purpose'] = 'Namena prompta (npr. priprema časa, kviz, rubrika)';
$string['form:course'] = 'Kurs';
$string['form:submit'] = 'Generiši prompt';
$string['form:result'] = 'Generisani AI prompt';
$string['form:audience'] = 'Publika (za nastavnike ili za učenike)';
// Akcije
$string['form:copy'] = 'Kopiraj u klipbord';
$string['form:copied'] = 'Iskopirano!';
$string['form:download'] = 'Preuzmi .txt';
$string['form:reset'] = 'Obriši sačuvani prompt';
$string['form:backtocourse'] = 'Nazad na kurs';

// Opcije
$string['option:lessonplan'] = 'Priprema časa';
$string['option:quiz'] = 'Pitanja za kviz';
$string['option:rubric'] = 'Rubrika za ocenjivanje';
$string['option:worksheet'] = 'Radni list / aktivnosti';
$string['option:teacher'] = 'Za nastavnike';
$string['option:student'] = 'Za učenike';
// Jezici
$string['lang:sr'] = 'Srpski';
$string['lang:en'] = 'Engleski';
$string['lang:pt'] = 'Portugalski';
$string['lang:sk'] = 'Slovački';
$string['lang:sr_cr'] = 'Srpski (ćirilica)';

// Oznake za prompt
$string['label:purpose'] = 'Namena';
$string['label:audience'] = 'Publika';
$string['label:language'] = 'Jezik';
$string['label:subject'] = 'Predmet';
$string['label:agerange'] = 'Uzrast/razred učenika';
$string['label:topic'] = 'Nastavna tema (oblast)';
$string['label:lesson'] = 'Naziv lekcije';
$string['label:classtype'] = 'Tip časa';
$string['label:outcomes'] = 'Ishodi';

// Šabloni za prompt
$string['prompt:prefix'] = "Vi ste stručnjak za instruktivni dizajn koji pomaže nastavniku na Moodle kursu '{$a->course}'.";
$string['prompt:instructions'] = 'Generišite izlaz na izabranom jeziku. Strogo se uskladite sa namenom i ishodima, prilagodite nivou za navedeni uzrast/razred. Po mogućstvu se oslonite na lokalni kurikulum.';

// Opcije tipa časa
$string['classtype:lecture'] = 'Predavanje';
$string['classtype:discussion'] = 'Diskusija';
$string['classtype:groupwork'] = 'Grupni rad';
$string['classtype:lab'] = 'Laboratorijski/praktični rad';
$string['classtype:project'] = 'Projektna nastava';
$string['classtype:review'] = 'Ponavljanje/utvrđivanje';
$string['classtype:assessment'] = 'Provera znanja/test';
