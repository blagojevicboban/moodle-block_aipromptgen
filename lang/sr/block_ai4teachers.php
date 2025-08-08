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
 * Srpski (latinica) language strings for block_ai4teachers.
 *
 * @package    block_ai4teachers
 * @category   string
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'AI za nastavnike';
$string['openpromptbuilder'] = 'Otvori kreator AI promptova';
$string['notallowed'] = 'Ovaj alat je dostupan samo nastavnicima i menadžerima na kursu.';
$string['privacy:metadata'] = 'Blok AI za nastavnike ne čuva lične podatke.';

// Sposobnosti (capabilities).
$string['ai4teachers:manage'] = 'Korišćenje bloka AI za nastavnike na kursu';
$string['ai4teachers:addinstance'] = 'Dodavanje novog bloka AI za nastavnike';
$string['ai4teachers:myaddinstance'] = 'Dodavanje bloka AI za nastavnike na Kontrolnu tablu';

// Forme.
$string['form:subjectlabel'] = 'Predmet';
$string['form:agerangelabel'] = 'Uzrast/razred učenika';
$string['form:topiclabel'] = 'Nastavna tema (oblast)';
$string['form:lessonlabel'] = 'Naziv lekcije (nastavne jedinice)';
$string['form:lessonbrowse'] = 'Pregled…';
$string['form:class_typelabel'] = 'Tip časa';
$string['form:outcomeslabel'] = 'Ishodi / ciljevi';
$string['form:language'] = 'Jezik prompta';
$string['form:purpose'] = 'Namena prompta (npr. priprema časa, kviz, rubrika)';
$string['form:course'] = 'Kurs';
$string['form:submit'] = 'Generiši prompt';
$string['form:result'] = 'Generisani AI prompt';
$string['form:audience'] = 'Publika (za nastavnike ili za učenike)';
$string['form:lessonhint'] = 'Savet: izaberite iz liste ili unesite sopstveni naziv (uneseni tekst ima prednost).';

// Akcije.
$string['form:copy'] = 'Kopiraj u klipbord';
$string['form:copied'] = 'Iskopirano!';
$string['form:download'] = 'Preuzmi .txt';
$string['form:reset'] = 'Obriši sačuvani prompt';
$string['form:backtocourse'] = 'Nazad na kurs';

// Opcije.
$string['option:lessonplan'] = 'Priprema časa';
$string['option:quiz'] = 'Pitanja za kviz';
$string['option:rubric'] = 'Rubrika za ocenjivanje';
$string['option:worksheet'] = 'Radni list / aktivnosti';
$string['option:teacher'] = 'Za nastavnike';
$string['option:student'] = 'Za učenike';

// Jezici.
$string['lang:sr'] = 'Srpski';
$string['lang:en'] = 'Engleski';
$string['lang:pt'] = 'Portugalski';
$string['lang:sk'] = 'Slovački';
$string['lang:sr_cr'] = 'Srpski (ćirilica)';

// Oznake za prompt.
$string['label:purpose'] = 'Namena';
$string['label:audience'] = 'Publika';
$string['label:language'] = 'Jezik';
$string['label:subject'] = 'Predmet';
$string['label:agerange'] = 'Uzrast/razred učenika';
$string['label:topic'] = 'Nastavna tema (oblast)';
$string['label:lesson'] = 'Naziv lekcije';
$string['label:classtype'] = 'Tip časa';
$string['label:outcomes'] = 'Ishodi';

// Šabloni za prompt.
$string['prompt:prefix'] = "Vi ste stručnjak za instruktivni dizajn koji pomaže nastavniku na Moodle kursu '{$a->course}'.";
$string['prompt:instructions'] = 'Generišite izlaz na izabranom jeziku. Strogo se uskladite sa namenom i ishodima, prilagodite nivou za navedeni uzrast/razred. Po mogućstvu se oslonite na lokalni kurikulum.';

// Opcije tipa časa.
$string['classtype:lecture'] = 'Predavanje';
$string['classtype:discussion'] = 'Diskusija';
$string['classtype:groupwork'] = 'Grupni rad';
$string['classtype:lab'] = 'Laboratorijski/praktični rad';
$string['classtype:project'] = 'Projektna nastava';
$string['classtype:review'] = 'Ponavljanje/utvrđivanje';
$string['classtype:assessment'] = 'Provera znanja/test';
