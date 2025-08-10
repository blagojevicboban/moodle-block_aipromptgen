<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Slovak language strings for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   string
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI nástroje pre učiteľov – generátor promptov';
$string['openpromptbuilder'] = 'Otvoriť generátor AI promptov';
$string['notallowed'] = 'Tento nástroj je dostupný iba pre učiteľov a manažérov v tomto kurze.';
$string['privacy:metadata'] = 'Blok Generátor AI promptov neukladá osobné údaje.';

// Form.
$string['form:subjectlabel'] = 'Predmet';
$string['form:agerangelabel'] = 'Vek/ročník žiakov';
$string['form:lessonlabel'] = 'Názov lekcie';
$string['form:lessonbrowse'] = 'Prehľadávať…';
$string['form:class_typelabel'] = 'Typ hodiny';
$string['form:outcomeslabel'] = 'Výstupy / ciele';
$string['form:topiclabel'] = 'Vyučovacia téma (oblasť)';
$string['form:topicbrowse'] = 'Prehľadávať…';
$string['form:language'] = 'Jazyk promptu';
$string['form:purpose'] = 'Účel promptu (napr. príprava, test, rubrika)';
$string['form:course'] = 'Kurz';
$string['form:submit'] = 'Vygenerovať prompt';
$string['form:result'] = 'Vygenerovaný AI prompt';
$string['form:audience'] = 'Publikum (pre učiteľa alebo pre žiaka)';

// Options.
$string['option:lessonplan'] = 'Príprava na hodinu';
$string['option:quiz'] = 'Testové otázky';
$string['option:rubric'] = 'Hodnotiaca rubrika';
$string['option:worksheet'] = 'Pracovný list / aktivity';
$string['option:teacher'] = 'Pre učiteľa';
$string['option:student'] = 'Pre žiaka';

// Language names.
$string['lang:sr'] = 'Srbčina';
$string['lang:en'] = 'Angličtina';
$string['lang:pt'] = 'Portugalčina';
$string['lang:sk'] = 'Slovenčina';
$string['lang:sr_cr'] = 'Srbčina (cyrilika)';

// Prompt labels.
$string['label:purpose'] = 'Účel';
$string['label:audience'] = 'Publikum';
$string['label:language'] = 'Jazyk';
$string['label:subject'] = 'Predmet';
$string['label:agerange'] = 'Vek/ročník žiakov';
$string['label:lesson'] = 'Názov lekcie';
$string['label:classtype'] = 'Typ hodiny';
$string['label:outcomes'] = 'Výstupy';
$string['label:topic'] = 'Vyučovacia téma (oblasť)';

// Prompt templates.
$string['prompt:prefix'] = "Ste expert na didaktický dizajn a pomáhate učiteľovi v kurze Moodle '{$a->course}'.";
$string['prompt:instructions'] = 'Generujte výstup vo vybranom jazyku. Dôsledne sa držte účelu a cieľov, primerane veku/ročníku. Preferujte súlad s miestnym kurikulom, ak je to vhodné.';

// Class type options.
$string['classtype:lecture'] = 'Výklad';
$string['classtype:discussion'] = 'Diskusia';
$string['classtype:groupwork'] = 'Skupinová práca';
$string['classtype:lab'] = 'Laboratórna/praktická';
$string['classtype:project'] = 'Projektová';
$string['classtype:review'] = 'Opakovanie/Revizia';
$string['classtype:assessment'] = 'Skúška/Test';

// Actions.
$string['form:copy'] = 'Kopírovať do schránky';
$string['form:copied'] = 'Skopírované!';
$string['form:download'] = 'Stiahnuť .txt';
$string['form:reset'] = 'Vymazať uložený prompt';
$string['form:backtocourse'] = 'Späť do kurzu';
