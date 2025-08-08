<?php
// Slovak strings for block_ai4teachers (sk)

$string['pluginname'] = 'AI pre učiteľov';
$string['openpromptbuilder'] = 'Otvoriť tvorcu AI promptov';
$string['notallowed'] = 'Tento nástroj je dostupný iba učiteľom a manažérom v tomto kurze.';
$string['privacy:metadata'] = 'Blok AI pre učiteľov neukladá osobné údaje.';

// Form
$string['form:subjectlabel'] = 'Predmet';
$string['form:agerangelabel'] = 'Vek/ročník žiakov';
$string['form:lessonlabel'] = 'Názov lekcie';
$string['form:class_typelabel'] = 'Typ hodiny';
$string['form:outcomeslabel'] = 'Výstupy / ciele';
$string['form:language'] = 'Jazyk promptu';
$string['form:purpose'] = 'Účel promptu (napr. príprava hodiny, kvíz, rubrika)';
$string['form:course'] = 'Kurz';
$string['form:submit'] = 'Generovať prompt';
$string['form:result'] = 'Vygenerovaný AI prompt';
$string['form:audience'] = 'Publikum (pre učiteľov alebo pre žiakov)';

// Options
$string['option:lessonplan'] = 'Príprava hodiny';
$string['option:quiz'] = 'Otázky do kvízu';
$string['option:rubric'] = 'Hodnotiaca rubrika';
$string['option:worksheet'] = 'Pracovný list / aktivity';
$string['option:teacher'] = 'Pre učiteľov';
$string['option:student'] = 'Pre žiakov';
// Jazyky
$string['lang:sr'] = 'Srbčina';
$string['lang:en'] = 'Angličtina';
$string['lang:pt'] = 'Portugalčina';
$string['lang:sk'] = 'Slovenčina';

// Označenia pre prompt
$string['label:purpose'] = 'Účel';
$string['label:audience'] = 'Publikum';
$string['label:language'] = 'Jazyk';
$string['label:subject'] = 'Predmet';
$string['label:agerange'] = 'Vek/ročník žiakov';
$string['label:lesson'] = 'Názov lekcie';
$string['label:classtype'] = 'Typ hodiny';
$string['label:outcomes'] = 'Výstupy';

// Šablóny promptu
$string['prompt:prefix'] = "Ste odborník na inštrukčný dizajn, ktorý pomáha učiteľovi v kurze Moodle '{$a->course}'.";
$string['prompt:instructions'] = 'Vygenerujte výstup v zvolenom jazyku. Dôsledne sa riaďte účelom a výstupmi, na vhodnej úrovni pre uvedený vek/ročník. Uprednostnite súlad s miestnym kurikulom, ak je to vhodné.';

// Možnosti typu hodiny
$string['classtype:lecture'] = 'Prednáška';
$string['classtype:discussion'] = 'Diskusia';
$string['classtype:groupwork'] = 'Skupinová práca';
$string['classtype:lab'] = 'Laboratórna/praktická';
$string['classtype:project'] = 'Projektové vyučovanie';
$string['classtype:review'] = 'Opakovanie/upevnenie';
$string['classtype:assessment'] = 'Test/hodnotenie';

// Actions
$string['form:copy'] = 'Skopírovať do schránky';
$string['form:copied'] = 'Skopírované!';
$string['form:download'] = 'Stiahnuť .txt';
$string['form:reset'] = 'Vymazať uložený prompt';

// Capabilities
$string['ai4teachers:manage'] = 'Používať blok AI pre učiteľov v kurze';
$string['ai4teachers:addinstance'] = 'Pridať nový blok AI pre učiteľov';
$string['ai4teachers:myaddinstance'] = 'Pridať blok AI pre učiteľov na Panel';
