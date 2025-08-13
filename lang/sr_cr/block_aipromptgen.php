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
 * Serbian (Cyrillic) language strings for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   string
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'АИ алати за наставнике – генератор промптова';
$string['openpromptbuilder'] = 'Отвори АИ генератор промптова';
$string['notallowed'] = 'Овај алат је доступан само наставницима и менаџерима у овом курсу.';
$string['privacy:metadata'] = 'Блок Генератор АИ промптова не чува личне податке.';

// Form.
$string['form:subjectlabel'] = 'Предмет';
$string['form:agerangelabel'] = 'Узраст/разред ученика';
$string['form:lessonlabel'] = 'Назив лекције (наставне јединице)';
$string['form:lessonbrowse'] = 'Преглед…';
$string['form:class_typelabel'] = 'Тип часа';
$string['form:outcomeslabel'] = 'Исходи / циљеви';
$string['form:topiclabel'] = 'Наставна тема/област';
$string['form:topicbrowse'] = 'Преглед…';
$string['form:language'] = 'Језик промпта';
$string['form:purpose'] = 'Сврха промпта (нпр. припрема часа, квиз, рубрика)';
$string['form:course'] = 'Курс';
$string['form:submit'] = 'Генериши промпт';
$string['form:result'] = 'Генерисани АИ промпт';
$string['form:audience'] = 'Публика (за наставнике или ученике)';

// Options.
$string['option:lessonplan'] = 'Припрема часа';
$string['option:quiz'] = 'Питања за квиз';
$string['option:rubric'] = 'Рубрика за оцењивање';
$string['option:worksheet'] = 'Радни лист / активности';
$string['option:teacher'] = 'За наставнике';
$string['option:student'] = 'За ученике';

// Language names.
$string['lang:sr'] = 'Српски';
$string['lang:en'] = 'Енглески';
$string['lang:pt'] = 'Португалски';
$string['lang:sk'] = 'Словачки';
$string['lang:sr_cr'] = 'Српски (ћирилица)';

// Prompt labels.
$string['label:purpose'] = 'Сврха';
$string['label:audience'] = 'Публика';
$string['label:language'] = 'Језик';
$string['label:subject'] = 'Предмет';
$string['label:agerange'] = 'Узраст/разред ученика';
$string['label:lesson'] = 'Назив лекције (наставне јединице)';
$string['label:classtype'] = 'Тип часа';
$string['label:outcomes'] = 'Исходи';
$string['label:topic'] = 'Наставна тема/област';

// Prompt templates.
$string['prompt:prefix'] = 'Ви сте стручњак за дидактички дизајн који помаже наставнику у Moodle курсу {$a->course}.';
$string['prompt:instructions'] = 'Генеришите излаз на изабраном језику. Строго се држите сврхе и исхода, на нивоу примереном узрасту/разреду. Када је применљиво, преферирајте усклађеност са локалним наставним планом и програмом.';

// Class type options.
$string['classtype:lecture'] = 'Предавање';
$string['classtype:discussion'] = 'Дискусија';
$string['classtype:groupwork'] = 'Групни рад';
$string['classtype:lab'] = 'Лабораторијски/практични рад';
$string['classtype:project'] = 'Пројектна настава';
$string['classtype:review'] = 'Понављање/утврђивање';
$string['classtype:assessment'] = 'Провера/Тест';

// Actions.
$string['form:copy'] = 'Копирај у клипборд';
$string['form:copied'] = 'Копирано!';
$string['form:download'] = 'Преузми .txt';
$string['form:reset'] = 'Обриши сачуван промпт';
$string['form:backtocourse'] = 'Назад на курс';

// Capabilities.
$string['aipromptgen:manage'] = 'Коришћење блока генератора АИ промптова у курсу';
$string['aipromptgen:addinstance'] = 'Додавање новог блока генератора АИ промптова';
$string['aipromptgen:myaddinstance'] = 'Додавање блока генератора АИ промптова на контролну таблу';
