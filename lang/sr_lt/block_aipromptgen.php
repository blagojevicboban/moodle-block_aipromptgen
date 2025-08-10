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
 * Serbian (Latin, legacy) language strings for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   string
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI alati za nastavnike – generator promptova';
$string['openpromptbuilder'] = 'Otvori AI generator promptova';
$string['notallowed'] = 'Ovaj alat je dostupan samo nastavnicima i menadžerima u ovom kursu.';
$string['privacy:metadata'] = 'Blok Generator AI promptova ne čuva lične podatke.';

// Form.
$string['form:subjectlabel'] = 'Predmet';
$string['form:agerangelabel'] = 'Uzrast/razred učenika';
$string['form:lessonlabel'] = 'Naziv lekcije (nastavne jedinice)';
$string['form:lessonbrowse'] = 'Pregled…';
$string['form:class_typelabel'] = 'Tip časa';
$string['form:outcomeslabel'] = 'Ishodi / ciljevi';
$string['form:topiclabel'] = 'Nastavna tema/oblast';
$string['form:topicbrowse'] = 'Pregled…';
$string['form:language'] = 'Jezik prompta';
$string['form:purpose'] = 'Svrha prompta (npr. priprema časa, kviz, rubrika)';
$string['form:course'] = 'Kurs';
$string['form:submit'] = 'Generiši prompt';
$string['form:result'] = 'Generisani AI prompt';
$string['form:audience'] = 'Publika (za nastavnike ili učenike)';

// Options.
$string['option:lessonplan'] = 'Priprema časa';
$string['option:quiz'] = 'Pitanja za kviz';
$string['option:rubric'] = 'Rubrika za ocenjivanje';
$string['option:worksheet'] = 'Radni list / aktivnosti';
$string['option:teacher'] = 'Za nastavnike';
$string['option:student'] = 'Za učenike';

// Language names.
$string['lang:sr'] = 'Srpski';
$string['lang:en'] = 'Engleski';
$string['lang:pt'] = 'Portugalski';
$string['lang:sk'] = 'Slovački';
$string['lang:sr_cr'] = 'Srpski (ćirilica)';

// Prompt labels.
$string['label:purpose'] = 'Svrha';
$string['label:audience'] = 'Publika';
$string['label:language'] = 'Jezik';
$string['label:subject'] = 'Predmet';
$string['label:agerange'] = 'Uzrast/razred učenika';
$string['label:lesson'] = 'Naziv lekcije (nastavne jedinice)';
$string['label:classtype'] = 'Tip časa';
$string['label:outcomes'] = 'Ishodi';
$string['label:topic'] = 'Nastavna tema/oblast';

// Prompt templates.
$string['prompt:prefix'] = "Vi ste stručnjak za didaktički dizajn koji pomaže nastavniku u Moodle kursu '{$a->course}'.";
$string['prompt:instructions'] = 'Generišite izlaz na izabranom jeziku. Strogo se držite svrhe i ishoda, na nivou primerenom uzrastu/razredu. Kada je primenljivo, preferirajte usklađenost sa lokalnim nastavnim planom i programom.';

// Class type options.
$string['classtype:lecture'] = 'Predavanje';
$string['classtype:discussion'] = 'Diskusija';
$string['classtype:groupwork'] = 'Grupni rad';
$string['classtype:lab'] = 'Laboratorijski/praktični rad';
$string['classtype:project'] = 'Projektna nastava';
$string['classtype:review'] = 'Ponavljanje/utvrđivanje';
$string['classtype:assessment'] = 'Provera/Test';

// Actions.
$string['form:copy'] = 'Kopiraj u klipbord';
$string['form:copied'] = 'Kopirano!';
$string['form:download'] = 'Preuzmi .txt';
$string['form:reset'] = 'Obriši sačuvan prompt';
$string['form:backtocourse'] = 'Nazad na kurs';

// Capabilities.
$string['aipromptgen:manage'] = 'Korišćenje bloka generatora AI promptova u kursu';
$string['aipromptgen:addinstance'] = 'Dodavanje novog bloka generatora AI promptova';
$string['aipromptgen:myaddinstance'] = 'Dodavanje bloka generatora AI promptova na kontrolnu tablu';
