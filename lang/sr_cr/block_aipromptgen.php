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
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aipromptgen:addinstance'] = 'Додавање новог блока генератора АИ промптова';
$string['aipromptgen:manage'] = 'Коришћење блока генератора АИ промптова у курсу';
$string['aipromptgen:myaddinstance'] = 'Додавање блока генератора АИ промптова на контролну таблу';
$string['audience:advanced'] = 'Напредни';
$string['audience:beginners'] = 'Почетници';
$string['audience:intermediate'] = 'Средњи ниво';
$string['audience:mixed'] = 'Мешовите способности';
$string['audience:professional'] = 'Професионалци';
$string['audience:specialneeds'] = 'Посебне потребе';
$string['cachedef_ratelimit'] = 'Број АИ захтева по кориснику у текућем сату (ограничење)';
$string['classtype:assessment'] = 'Провера/Тест';
$string['classtype:blended'] = 'Комбинована настава';
$string['classtype:discussion'] = 'Дискусија';
$string['classtype:fieldtrip'] = 'Теренска настава';
$string['classtype:groupwork'] = 'Групни рад';
$string['classtype:lab'] = 'Лабораторијски/практични рад';
$string['classtype:lecture'] = 'Предавање';
$string['classtype:online'] = 'Онлајн/виртуелно';
$string['classtype:project'] = 'Пројектна настава';
$string['classtype:review'] = 'Понављање/утврђивање';
$string['classtype:seminar'] = 'Семинар';
$string['classtype:workshop'] = 'Радионица';
$string['error_access'] = 'Грешка приступа: {$a}';
$string['error_api'] = 'Грешка АИ сервиса: {$a}';
$string['error_connection'] = 'Повезивање са АИ сервисом није успело: {$a}';
$string['error_noapikey'] = 'OpenAI API кључ није подешен у подешавањима.';
$string['error_noclaudeapikey'] = 'Claude API кључ није подешен у подешавањима.';
$string['error_nocourseid'] = 'Обавезни параметар "courseid" недостаје или није исправан.';
$string['error_nocustom_endpoint'] = 'Адреса прилагођеног API-ја није подешена у подешавањима.';
$string['error_nodeepseek_apikey'] = 'DeepSeek API кључ није подешен у подешавањима.';
$string['error_noendpoint'] = 'Ollama адреса није подешена у подешавањима.';
$string['error_nogeminiapikey'] = 'Gemini API кључ није подешен у подешавањима.';
$string['error_ratelimit'] = 'Прекорачили сте највећи дозвољени број АИ захтева по сату. Покушајте поново касније.';
$string['error_unknownprovider'] = 'Непознат АИ провајдер: {$a}';
$string['error_unknownresponse'] = 'Неочекиван одговор АИ сервиса: {$a}';
$string['form:agerangelabel'] = 'Узраст/разред ученика';
$string['form:audience'] = 'Публика (за наставнике или ученике)';
$string['form:audiencebrowse'] = 'Преглед публике';
$string['form:backtocourse'] = 'Назад на курс';
$string['form:class_typelabel'] = 'Тип часа';
$string['form:classtypebrowse'] = 'Преглед типова часова';
$string['form:copied'] = 'Копирано!';
$string['form:copy'] = 'Копирај у клипборд';
$string['form:course'] = 'Курс';
$string['form:custom_prompt'] = 'Прилагођени промпт';
$string['form:download'] = 'Преузми .txt';
$string['form:includeimages'] = 'Укључи слике генерисане помоћу DALL-E';
$string['form:language'] = 'Језик промпта';
$string['form:languagebrowse'] = 'Преглед језика';
$string['form:lessonbrowse'] = 'Преглед…';
$string['form:lessoncount'] = 'Број часова';
$string['form:lessonduration'] = 'Трајање часа (у минутима)';
$string['form:lessonlabel'] = 'Назив лекције (наставне јединице)';
$string['form:outcomesbrowse'] = 'Прегледај компетенције/исходе';
$string['form:outcomeslabel'] = 'Исходи / циљеви';
$string['form:provider'] = 'Провајдер';
$string['form:purpose'] = 'Сврха промпта (нпр. припрема часа, квиз, рубрика)';
$string['form:reset'] = 'Обриши сачуван промпт';
$string['form:response'] = 'АИ одговор';
$string['form:result'] = 'Генерисани АИ промпт';
$string['form:sendtoai'] = 'Пошаљи АИ';
$string['form:sendtochatgpt'] = 'Пошаљи у ChatGPT';
$string['form:subjectlabel'] = 'Предмет';
$string['form:submit'] = 'Генериши промпт';
$string['form:templates_hint'] = 'Предефинисани шаблони — поља нису обавезна.';
$string['form:templates_label'] = 'Брзи шаблони';
$string['form:topicbrowse'] = 'Преглед…';
$string['form:topiclabel'] = 'Наставна тема/област';
$string['help:agerange'] = 'Упишите узраст или разред или кликните Преглед за избор тачног узраста или опсега';
$string['help:audience'] = 'Упишите публику или кликните Преглед за избор';
$string['help:classtype'] = 'Упишите тип часа или кликните Преглед за избор са листе';
$string['help:language'] = 'Упишите језик или кликните Преглед за избор инсталираних језика';
$string['help:lesson'] = 'Упишите назив лекције или кликните Преглед за избор секције/активности';
$string['help:lessonbrowse'] = 'Преглед секција и активности';
$string['help:outcomes'] = 'Наведите исходе/циљеве (један или више)';
$string['help:outcomesbrowse'] = 'Прегледај компетенције/исходе';
$string['help:purpose'] = 'Упишите сврху или кликните Преглед за избор са листе';
$string['help:subjectchange'] = 'Измените назив предмета ако је потребно';
$string['help:topic'] = 'Упишите тему или кликните Преглед за избор курcних секција';
$string['label:agerange'] = 'Узраст/разред ученика';
$string['label:audience'] = 'Публика';
$string['label:classtype'] = 'Тип часа';
$string['label:language'] = 'Језик';
$string['label:lesson'] = 'Назив лекције (наставне јединице)';
$string['label:lessoncount'] = 'Број часова';
$string['label:lessonduration'] = 'Трајање часа';
$string['label:outcomes'] = 'Исходи';
$string['label:purpose'] = 'Сврха';
$string['label:subject'] = 'Предмет';
$string['label:topic'] = 'Наставна тема/област';
$string['lang:bs'] = 'Босански';
$string['lang:de'] = 'Немачки';
$string['lang:en'] = 'Енглески';
$string['lang:es'] = 'Шпански';
$string['lang:fr'] = 'Француски';
$string['lang:hr'] = 'Хрватски';
$string['lang:it'] = 'Италијански';
$string['lang:ja'] = 'Јапански';
$string['lang:nl'] = 'Холандски';
$string['lang:pl'] = 'Пољски';
$string['lang:pt'] = 'Португалски';
$string['lang:ru'] = 'Руски';
$string['lang:sk'] = 'Словачки';
$string['lang:sr'] = 'Српски';
$string['lang:sr_cr'] = 'Српски (ћирилица)';
$string['lang:sr_lt'] = 'Српски (латиница)';
$string['lang:zh'] = 'Кинески';
$string['notallowed'] = 'Овај алат је доступан само наставницима и менаџерима у овом курсу.';
$string['openpromptbuilder'] = 'Отвори АИ генератор промптова';
$string['option:lessonplan'] = 'Припрема часа';
$string['option:quiz'] = 'Питања за квиз';
$string['option:rubric'] = 'Рубрика за оцењивање';
$string['option:student'] = 'За ученике';
$string['option:teacher'] = 'За наставнике';
$string['option:worksheet'] = 'Радни лист / активности';
$string['placeholder:agerange'] = 'нпр. 15 или 10–12';
$string['pluginname'] = 'АИ алати за наставнике – генератор промптова';
$string['privacy:metadata:claude'] = 'Промптови се шаљу компанији Anthropic (Claude) када наставник изабере овог провајдера.';
$string['privacy:metadata:custom'] = 'Промптови се шаљу прилагођеном сервису компатибилном са OpenAI API-јем, који је подесио администратор, када наставник изабере овог провајдера.';
$string['privacy:metadata:deepseek'] = 'Промптови се шаљу сервису DeepSeek када наставник изабере овог провајдера.';
$string['privacy:metadata:gemini'] = 'Промптови се шаљу компанији Google (Gemini) када наставник изабере овог провајдера.';
$string['privacy:metadata:ollama'] = 'Промптови се шаљу Ollama серверу, који је подесио администратор, када наставник изабере овог провајдера.';
$string['privacy:metadata:openai'] = 'Промптови се шаљу компанији OpenAI када наставник изабере овог провајдера.';
$string['privacy:metadata:prompt'] = 'Промпт који наставник састави и пошаље. Може да садржи називе курса, теме, лекције и исхода и сваки текст који наставник дода. Подаци који идентификују корисника се не шаљу.';
$string['prompt:instructions'] = 'Генериши излаз у потпуности на наведеном језику. Увери се да је садржај примерен датом узрасту/разреду ученика, да се уклапа у локални план и програм када је применљиво и да директно подржава наведене циљеве. Структурирај излаз јасно, користећи наслове, поднаслове и листе са тачкама где је прикладно.';
$string['prompt:prefix'] = 'Ви сте стручњак за дидактички дизајн који помаже наставнику у Moodle курсу {$a->course}.';
$string['provider:custom'] = 'Прилагођени API';
$string['provider:notconfigured'] = '{$a} (није подешено)';
$string['purpose:activity'] = 'Осмишљавање активности';
$string['purpose:explanation'] = 'Објашњење';
$string['purpose:lessonplan'] = 'Припрема за час';
$string['purpose:project'] = 'План пројекта';
$string['purpose:quiz'] = 'Квиз/провера знања';
$string['purpose:rubric'] = 'Рубрика за оцењивање';
$string['purpose:summary'] = 'Сажетак';
$string['purpose:syllabus'] = 'План и програм';
$string['setting:apikey'] = 'OpenAI API кључ';
$string['setting:apikey_desc'] = 'API кључ за OpenAI. Чува се у конфигурацији Moodle‑a.';
$string['setting:claude_apikey'] = 'Claude API кључ';
$string['setting:claude_apikey_desc'] = 'API кључ за Anthropic Claude. Чува се у конфигурацији Moodle‑a.';
$string['setting:claude_model'] = 'Claude модел';
$string['setting:claude_model_desc'] = 'Модел за Claude (нпр. claude-opus-5-5, claude-sonnet-5-5, claude-haiku-5-5). Подразумевано: claude-opus-5-5. Подешавање температуре се не шаље Claude-у, јер га актуелни Claude модели не прихватају.';
$string['setting:custom_apikey'] = 'Прилагођени API кључ (опционо)';
$string['setting:custom_apikey_desc'] = 'API кључ за прилагођену крајњу тачку. Оставите празно ако није потребан.';
$string['setting:custom_endpoint'] = 'Прилагођена АИ крајња тачка';
$string['setting:custom_endpoint_desc'] = 'Пун URL прилагођене крајње тачке компатибилне са OpenAI-јем (нпр. http://localhost:1234/v1/chat/completions).';
$string['setting:custom_model'] = 'Прилагођени АИ модел';
$string['setting:custom_model_desc'] = 'Назив модела који се шаље прилагођеној крајњој тачки.';
$string['setting:deepseek_apikey'] = 'DeepSeek API кључ';
$string['setting:deepseek_apikey_desc'] = 'API кључ за DeepSeek. Можете га преузети на https://platform.deepseek.com/';
$string['setting:deepseek_model'] = 'DeepSeek модел';
$string['setting:deepseek_model_desc'] = 'Модел који се користи за DeepSeek (нпр. деепсеек-цхат, деепсеек-реасонер).';
$string['setting:gemini_apikey'] = 'Gemini API кључ';
$string['setting:gemini_apikey_desc'] = 'API кључ за Google Gemini. Чува се у конфигурацији Moodle‑a.';
$string['setting:gemini_model'] = 'Gemini модел';
$string['setting:gemini_model_desc'] = 'Модел за Gemini (нпр. gemini-3.8-flash). Подразумевано: gemini-3.8-flash.';
$string['setting:max_tokens'] = 'Максималан број токена';
$string['setting:max_tokens_desc'] = 'Највећи број токена (речи/знакова) у АИ одговору. Подразумевано: 1024. Веће вредности омогућавају дуже одговоре, али коштају више. За Gemini ово одређује maxOutputTokens; за Ollama се користи када је Ollama подешавање за број токена празно. Модели који размишљају пре одговора (Claude, Gemini, GPT-5 и o-serija) добијају најмање 16000, јер се размишљање рачуна у ограничење.';
$string['setting:model'] = 'OpenAI модел';
$string['setting:model_desc'] = 'Модел за OpenAI (нпр. gpt-5-mini, gpt-5, gpt-4.1-mini). Подразумевано: gpt-5-mini. GPT-5 и o-serija не прихватају подешавање температуре, па им се оно не шаље.';
$string['setting:ollama_endpoint'] = 'Ollama крајња тачка';
$string['setting:ollama_endpoint_desc'] = 'Основни URL локалног Ollama сервера (нпр. http://localhost:11434).';
$string['setting:ollama_model'] = 'Ollama модел';
$string['setting:ollama_model_desc'] = 'Назив локалног модела учитаног у Ollama (нпр. llama3, mistral, codellama).';
$string['setting:ollama_num_predict'] = 'Ollama макс токена (num_predict)';
$string['setting:ollama_num_predict_desc'] = 'Максималан број генерисаних токена (опција num_predict). Мање = бржи одговор.';
$string['setting:ollama_schema'] = 'Ollama шема структуиране излазне форме';
$string['setting:ollama_schema_desc'] = 'Опциони JSON Schema за ограничавање Ollama одговора (празно за слободан текст).';
$string['setting:ollama_timeout'] = 'Ollama временски лимит (секунде)';
$string['setting:ollama_timeout_desc'] = 'Максимално време чекања на Ollama одговор. Повећајте за велики излаз.';
$string['setting:rate_limit'] = 'Ограничење протока (по сату)';
$string['setting:rate_limit_desc'] = 'Број АИ захтева дозвољених по кориснику по сату. Поставите на 0 да бисте онемогућили ограничење. Подразумевано: 50.';
$string['setting:system_prompt'] = 'Системски промпт';
$string['setting:system_prompt_desc'] = 'Прилагођено упутство које се шаље АИ-ју пре сваког захтева (примењује се на све провајдере). Оставите празно да бисте користили уграђено подразумевано упутство.';
$string['setting:temperature'] = 'Температура';
$string['setting:temperature_desc'] = 'Контролише насумичност АИ одговора (0.0 = детерминистички, 2.0 = веома креативан). Подразумевано: 0.7. Не шаље се Claude-у ни GPT-5/o-serija моделима, који прихватају само своју подразумевану вредност.';
$string['setting:templates'] = 'Унапред дефинисани шаблони (JSON)';
$string['setting:templates_desc'] = 'JSON низ шаблона. Сваки шаблон треба да буде објекат са "title" и "prompt". Ако је празно, користе се подразумевани шаблони.<бр><бр><б>Доступни маркери:</б> {subject}, {topic}, {lesson}, {audience}, {outcomes}, {style}, {purpose}, {language}.';
$string['status_connecting'] = 'Повезивање...';
$string['status_done'] = 'Завршено';
$string['status_error'] = 'Грешка';
$string['status_error_occurred'] = 'Дошло је до грешке';
$string['status_finished'] = 'Завршено';
$string['status_receiving'] = 'Пријем података...';
$string['status_started'] = 'Започето';
$string['status_streaming'] = 'Стримовање...';
$string['status_timeout'] = 'Време је истекло (недовршено)';
$string['system_role'] = 'Ви сте користан асистент.';
$string['template_bloom_prompt'] = 'Направи 5 питања по Блумовој таксономији за:
Тема: {topic}
Циљна група: {audience}
Исходи: {outcomes}';
$string['template_bloom_title'] = 'Питања по Блумовој таксономији';
$string['template_lessonplan_prompt'] = 'Направи структурисану припрему за час од 45 минута.
Предмет: {subject}
Тема: {topic}
Тип наставе: {style}
Исходи: {outcomes}';
$string['template_lessonplan_title'] = 'Нацрт припреме за час';
$string['template_socratic_prompt'] = 'Ти си сократовски тутор. Помози кориснику да разуме {topic} постављајући питања која га воде.
Контекст: {subject}';
$string['template_socratic_title'] = 'Сократовски тутор';
$string['template_summary_prompt'] = 'Дај кратак сажетак кључних појмова за {topic},
прилагођен ученицима узраста: {audience}.';
$string['template_summary_title'] = 'Кратак сажетак';
$string['tooltip:editprompt'] = 'Уредите генерисани промпт пре слања';
$string['tooltip:provider_not_configured'] = 'Ниједан АИ провајдер није подешен.';
$string['view:html'] = 'HTML';
$string['view:htmlcode'] = 'HTML КОД';
$string['view:raw'] = 'RAW';
$string['view:text'] = 'ТЕКСТ';
