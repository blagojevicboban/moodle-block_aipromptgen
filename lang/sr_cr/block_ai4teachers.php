<?php
// Српски (ћирилица) — језички пакет за block_ai4teachers

$string['pluginname'] = 'АИ за наставнике';
$string['openpromptbuilder'] = 'Отвори креатор АИ промптова';
$string['notallowed'] = 'Овај алат је доступан само наставницима и менаџерима на курсу.';
$string['privacy:metadata'] = 'Блок АИ за наставнике не чува личне податке.';

// Способности (capabilities)
$string['ai4teachers:manage'] = 'Коришћење блока АИ за наставнике на курсу';
$string['ai4teachers:addinstance'] = 'Додавање новог блока АИ за наставнике';
$string['ai4teachers:myaddinstance'] = 'Додавање блока АИ за наставнике на Контролну таблу';

// Форме
$string['form:subjectlabel'] = 'Предмет';
$string['form:agerangelabel'] = 'Узраст/разред ученика';
$string['form:lessonlabel'] = 'Назив лекције';
$string['form:class_typelabel'] = 'Тип часа';
$string['form:outcomeslabel'] = 'Исходи / циљеви';
$string['form:language'] = 'Језик промпта';
$string['form:purpose'] = 'Намена промпта (нпр. припрема часа, квиз, рубрика)';
$string['form:course'] = 'Курс';
$string['form:submit'] = 'Генериши промпт';
$string['form:result'] = 'Генерисани АИ промпт';
$string['form:audience'] = 'Публика (за наставнике или за ученике)';
// Акције
$string['form:copy'] = 'Копирај у клипборд';
$string['form:copied'] = 'Ископирано!';
$string['form:download'] = 'Преузми .txt';
$string['form:reset'] = 'Обриши сачувани промпт';
$string['form:backtocourse'] = 'Назад на курс';

// Опције
$string['option:lessonplan'] = 'Припрема часа';
$string['option:quiz'] = 'Питања за квиз';
$string['option:rubric'] = 'Рубрика за оцењивање';
$string['option:worksheet'] = 'Радни лист / активности';
$string['option:teacher'] = 'За наставнике';
$string['option:student'] = 'За ученике';

// Језици
$string['lang:sr'] = 'Српски';
$string['lang:sr_cr'] = 'Српски (ћирилица)';
$string['lang:en'] = 'Енглески';
$string['lang:pt'] = 'Португалски';
$string['lang:sk'] = 'Словачки';

// Ознаке за промпт
$string['label:purpose'] = 'Намена';
$string['label:audience'] = 'Публика';
$string['label:language'] = 'Језик';
$string['label:subject'] = 'Предмет';
$string['label:agerange'] = 'Узраст/разред ученика';
$string['label:lesson'] = 'Назив лекције';
$string['label:classtype'] = 'Тип часа';
$string['label:outcomes'] = 'Исходи';

// Шаблони за промпт
$string['prompt:prefix'] = "Ви сте стручњак за инструктивни дизајн који помаже наставнику на Moodle курсу '{$a->course}'.";
$string['prompt:instructions'] = 'Генеришите излаз на изабраном језику. Строго се ускладите са наменом и исходима, прилагодите нивоу за наведени узраст/разред. По могућству се ослоните на локални курикулум.';

// Опције типа часа
$string['classtype:lecture'] = 'Предавање';
$string['classtype:discussion'] = 'Дискусија';
$string['classtype:groupwork'] = 'Групни рад';
$string['classtype:lab'] = 'Лабораторијски/практични рад';
$string['classtype:project'] = 'Пројектна настава';
$string['classtype:review'] = 'Понављање/утврђивање';
$string['classtype:assessment'] = 'Провера знања/тест';
