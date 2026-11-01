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
 * @author     Boban Blagojevic
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['aipromptgen:addinstance'] = 'Dodavanje novog bloka generatora AI promptova';
$string['aipromptgen:manage'] = 'Korišćenje bloka generatora AI promptova u kursu';
$string['aipromptgen:myaddinstance'] = 'Dodavanje bloka generatora AI promptova na kontrolnu tablu';
$string['audience:advanced'] = 'Napredni';
$string['audience:beginners'] = 'Početnici';
$string['audience:intermediate'] = 'Srednji nivo';
$string['audience:mixed'] = 'Mešovite sposobnosti';
$string['audience:professional'] = 'Profesionalci';
$string['audience:specialneeds'] = 'Posebne potrebe';
$string['cachedef_ratelimit'] = 'Broj AI zahteva po korisniku u tekućem satu (ograničenje)';
$string['classtype:assessment'] = 'Provera/Test';
$string['classtype:blended'] = 'Kombinovana nastava';
$string['classtype:discussion'] = 'Diskusija';
$string['classtype:fieldtrip'] = 'Terenska nastava';
$string['classtype:groupwork'] = 'Grupni rad';
$string['classtype:lab'] = 'Laboratorijski/praktični rad';
$string['classtype:lecture'] = 'Predavanje';
$string['classtype:online'] = 'Onlajn/virtuelno';
$string['classtype:project'] = 'Projektna nastava';
$string['classtype:review'] = 'Ponavljanje/utvrđivanje';
$string['classtype:seminar'] = 'Seminar';
$string['classtype:workshop'] = 'Radionica';
$string['error_access'] = 'Greška pristupa: {$a}';
$string['error_api'] = 'Greška AI servisa: {$a}';
$string['error_connection'] = 'Povezivanje sa AI servisom nije uspelo: {$a}';
$string['error_noapikey'] = 'OpenAI API ključ nije podešen u podešavanjima.';
$string['error_noclaudeapikey'] = 'Claude API ključ nije podešen u podešavanjima.';
$string['error_nocourseid'] = 'Obavezni parametar "courseid" nedostaje ili nije ispravan.';
$string['error_nocustom_endpoint'] = 'Adresa prilagođenog API-ja nije podešena u podešavanjima.';
$string['error_nodeepseek_apikey'] = 'DeepSeek API ključ nije podešen u podešavanjima.';
$string['error_noendpoint'] = 'Ollama adresa nije podešena u podešavanjima.';
$string['error_nogeminiapikey'] = 'Gemini API ključ nije podešen u podešavanjima.';
$string['error_ratelimit'] = 'Prekoračili ste najveći dozvoljeni broj AI zahteva po satu. Pokušajte ponovo kasnije.';
$string['error_unknownprovider'] = 'Nepoznat AI provajder: {$a}';
$string['error_unknownresponse'] = 'Neočekivan odgovor AI servisa: {$a}';
$string['form:agerangelabel'] = 'Uzrast/razred učenika';
$string['form:audience'] = 'Publika (za nastavnike ili učenike)';
$string['form:audiencebrowse'] = 'Pregled publike';
$string['form:backtocourse'] = 'Nazad na kurs';
$string['form:class_typelabel'] = 'Tip časa';
$string['form:classtypebrowse'] = 'Pregled tipova časova';
$string['form:copied'] = 'Kopirano!';
$string['form:copy'] = 'Kopiraj u klipbord';
$string['form:course'] = 'Kurs';
$string['form:custom_prompt'] = 'Prilagođeni prompt';
$string['form:download'] = 'Preuzmi .txt';
$string['form:includeimages'] = 'Uključi slike generisane pomoću DALL-E';
$string['form:language'] = 'Jezik prompta';
$string['form:languagebrowse'] = 'Pregled jezika';
$string['form:lessonbrowse'] = 'Pregled…';
$string['form:lessoncount'] = 'Broj časova';
$string['form:lessonduration'] = 'Trajanje časa (u minutima)';
$string['form:lessonlabel'] = 'Naziv lekcije (nastavne jedinice)';
$string['form:outcomesbrowse'] = 'Pregledaj kompetencije/ishode';
$string['form:outcomeslabel'] = 'Ishodi / ciljevi';
$string['form:provider'] = 'Provajder';
$string['form:purpose'] = 'Svrha prompta (npr. priprema časa, kviz, rubrika)';
$string['form:reset'] = 'Obriši sačuvan prompt';
$string['form:response'] = 'AI odgovor';
$string['form:result'] = 'Generisani AI prompt';
$string['form:sendtoai'] = 'Pošalji u AI';
$string['form:sendtochatgpt'] = 'Pošalji u ChatGPT';
$string['form:subjectlabel'] = 'Predmet';
$string['form:submit'] = 'Generiši prompt';
$string['form:templates_hint'] = 'Predefinisani šabloni — polja nisu obavezna.';
$string['form:templates_label'] = 'Brzi šabloni';
$string['form:topicbrowse'] = 'Pregled…';
$string['form:topiclabel'] = 'Nastavna tema/oblast';
$string['help:agerange'] = 'Upišite uzrast ili razred ili kliknite Pregled za izbor tačnog uzrasta ili opsega';
$string['help:audience'] = 'Upišite publiku ili kliknite Pregled za izbor';
$string['help:classtype'] = 'Upišite tip časa ili kliknite Pregled za izbor sa liste';
$string['help:language'] = 'Upišite jezik ili kliknite Pregled za izbor instaliranih jezika';
$string['help:lesson'] = 'Upišite naziv lekcije ili kliknite Pregled za izbor sekcije/aktivnosti';
$string['help:lessonbrowse'] = 'Pregled sekcija i aktivnosti';
$string['help:outcomes'] = 'Navedite ishode/ciljeve (jedan ili više)';
$string['help:outcomesbrowse'] = 'Pregledaj kompetencije/ishode';
$string['help:purpose'] = 'Upišite svrhu ili kliknite Pregled za izbor sa liste';
$string['help:subjectchange'] = 'Izmenite naziv predmeta ako je potrebno';
$string['help:topic'] = 'Upišite temu ili kliknite Pregled za izbor kursnih sekcija';
$string['label:agerange'] = 'Uzrast/razred učenika';
$string['label:audience'] = 'Publika';
$string['label:classtype'] = 'Tip časa';
$string['label:language'] = 'Jezik';
$string['label:lesson'] = 'Naziv lekcije (nastavne jedinice)';
$string['label:lessoncount'] = 'Broj časova';
$string['label:lessonduration'] = 'Trajanje časa';
$string['label:outcomes'] = 'Ishodi';
$string['label:purpose'] = 'Svrha';
$string['label:subject'] = 'Predmet';
$string['label:topic'] = 'Nastavna tema/oblast';
$string['lang:bs'] = 'Bosanski';
$string['lang:de'] = 'Nemački';
$string['lang:en'] = 'Engleski';
$string['lang:es'] = 'Španski';
$string['lang:fr'] = 'Francuski';
$string['lang:hr'] = 'Hrvatski';
$string['lang:it'] = 'Italijanski';
$string['lang:ja'] = 'Japanski';
$string['lang:nl'] = 'Holandski';
$string['lang:pl'] = 'Poljski';
$string['lang:pt'] = 'Portugalski';
$string['lang:ru'] = 'Ruski';
$string['lang:sk'] = 'Slovački';
$string['lang:sr'] = 'Srpski';
$string['lang:sr_cr'] = 'Srpski (ćirilica)';
$string['lang:sr_lt'] = 'Srpski (latinica)';
$string['lang:zh'] = 'Kineski';
$string['notallowed'] = 'Ovaj alat je dostupan samo nastavnicima i menadžerima u ovom kursu.';
$string['openpromptbuilder'] = 'Otvori AI generator promptova';
$string['option:lessonplan'] = 'Priprema časa';
$string['option:quiz'] = 'Pitanja za kviz';
$string['option:rubric'] = 'Rubrika za ocenjivanje';
$string['option:student'] = 'Za učenike';
$string['option:teacher'] = 'Za nastavnike';
$string['option:worksheet'] = 'Radni list / aktivnosti';
$string['placeholder:agerange'] = 'npr. 15 ili 10–12';
$string['pluginname'] = 'AI alati za nastavnike – generator promptova';
$string['privacy:metadata:claude'] = 'Promptovi se šalju kompaniji Anthropic (Claude) kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:custom'] = 'Promptovi se šalju prilagođenom servisu kompatibilnom sa OpenAI API-jem, koji je podesio administrator, kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:deepseek'] = 'Promptovi se šalju servisu DeepSeek kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:gemini'] = 'Promptovi se šalju kompaniji Google (Gemini) kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:ollama'] = 'Promptovi se šalju Ollama serveru, koji je podesio administrator, kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:openai'] = 'Promptovi se šalju kompaniji OpenAI kada nastavnik izabere ovog provajdera.';
$string['privacy:metadata:prompt'] = 'Prompt koji nastavnik sastavi i pošalje. Može da sadrži nazive kursa, teme, lekcije i ishoda i svaki tekst koji nastavnik doda. Podaci koji identifikuju korisnika se ne šalju.';
$string['prompt:instructions'] = 'Generiši izlaz u potpunosti na navedenom jeziku. Uveri se da je sadržaj primeren datom uzrastu/razredu učenika, da se uklapa u lokalni plan i program kada je primenljivo i da direktno podržava navedene ciljeve. Strukturiraj izlaz jasno, koristeći naslove, podnaslove i liste sa tačkama gde je prikladno.';
$string['prompt:prefix'] = 'Vi ste stručnjak za didaktički dizajn koji pomaže nastavniku u Moodle kursu {$a->course}.';
$string['provider:custom'] = 'Prilagođeni API';
$string['provider:notconfigured'] = '{$a} (nije podešeno)';
$string['purpose:activity'] = 'Osmišljavanje aktivnosti';
$string['purpose:explanation'] = 'Objašnjenje';
$string['purpose:lessonplan'] = 'Priprema za čas';
$string['purpose:project'] = 'Plan projekta';
$string['purpose:quiz'] = 'Kviz/provera znanja';
$string['purpose:rubric'] = 'Rubrika za ocenjivanje';
$string['purpose:summary'] = 'Sažetak';
$string['purpose:syllabus'] = 'Plan i program';
$string['setting:apikey'] = 'OpenAI API ključ';
$string['setting:apikey_desc'] = 'API ključ za OpenAI. Čuva se u konfiguraciji Moodle‑a.';
$string['setting:claude_apikey'] = 'Claude API ključ';
$string['setting:claude_apikey_desc'] = 'API ključ za Anthropic Claude. Čuva se u konfiguraciji Moodle‑a.';
$string['setting:claude_model'] = 'Claude model';
$string['setting:claude_model_desc'] = 'Model za Claude (npr. claude-opus-5-5, claude-sonnet-5-5, claude-haiku-5-5). Podrazumevano: claude-opus-5-5. Podešavanje temperature se ne šalje Claude-u, jer ga aktuelni Claude modeli ne prihvataju.';
$string['setting:custom_apikey'] = 'Prilagođeni API ključ (opciono)';
$string['setting:custom_apikey_desc'] = 'API ključ za prilagođenu krajnju tačku. Ostavite prazno ako nije potreban.';
$string['setting:custom_endpoint'] = 'Prilagođena AI krajnja tačka';
$string['setting:custom_endpoint_desc'] = 'Pun URL prilagođene krajnje tačke kompatibilne sa OpenAI-jem (npr. http://localhost:1234/v1/chat/completions).';
$string['setting:custom_model'] = 'Prilagođeni AI model';
$string['setting:custom_model_desc'] = 'Naziv modela koji se šalje prilagođenoj krajnjoj tački.';
$string['setting:deepseek_apikey'] = 'DeepSeek API ključ';
$string['setting:deepseek_apikey_desc'] = 'API ključ za DeepSeek. Možete ga preuzeti na https://platform.deepseek.com/';
$string['setting:deepseek_model'] = 'DeepSeek model';
$string['setting:deepseek_model_desc'] = 'Model koji se koristi za DeepSeek (npr. deepseek-chat, deepseek-reasoner).';
$string['setting:gemini_apikey'] = 'Gemini API ključ';
$string['setting:gemini_apikey_desc'] = 'API ključ za Google Gemini. Čuva se u konfiguraciji Moodle‑a.';
$string['setting:gemini_model'] = 'Gemini model';
$string['setting:gemini_model_desc'] = 'Model za Gemini (npr. gemini-3.8-flash). Podrazumevano: gemini-3.8-flash.';
$string['setting:max_tokens'] = 'Maksimalan broj tokena';
$string['setting:max_tokens_desc'] = 'Najveći broj tokena (reči/znakova) u AI odgovoru. Podrazumevano: 1024. Veće vrednosti omogućavaju duže odgovore, ali koštaju više. Za Gemini ovo određuje maxOutputTokens; za Ollama se koristi kada je Ollama podešavanje za broj tokena prazno. Modeli koji razmišljaju pre odgovora (Claude, Gemini, GPT-5 i o-serija) dobijaju najmanje 16000, jer se razmišljanje računa u ograničenje.';
$string['setting:model'] = 'OpenAI model';
$string['setting:model_desc'] = 'Model za OpenAI (npr. gpt-5-mini, gpt-5, gpt-4.1-mini). Podrazumevano: gpt-5-mini. GPT-5 i o-serija ne prihvataju podešavanje temperature, pa im se ono ne šalje.';
$string['setting:ollama_endpoint'] = 'Ollama krajnja tačka';
$string['setting:ollama_endpoint_desc'] = 'Osnovni URL lokalnog Ollama servera (npr. http://localhost:11434).';
$string['setting:ollama_model'] = 'Ollama model';
$string['setting:ollama_model_desc'] = 'Naziv lokalnog modela učitanog u Ollama (npr. llama3, mistral, codellama).';
$string['setting:ollama_num_predict'] = 'Ollama max tokena (num_predict)';
$string['setting:ollama_num_predict_desc'] = 'Maksimalan broj tokena za generisanje (opcija num_predict). Manje za brže odgovore.';
$string['setting:ollama_schema'] = 'Ollama šema strukturisanog izlaza';
$string['setting:ollama_schema_desc'] = 'Opcioni JSON Schema za ograničavanje Ollama odgovora (ostavite prazno za slobodan tekst).';
$string['setting:ollama_timeout'] = 'Ollama vremenski limit (sekunde)';
$string['setting:ollama_timeout_desc'] = 'Maksimalno vreme čekanja na Ollama odgovor. Povećajte za duže izlaze.';
$string['setting:rate_limit'] = 'Ograničenje protoka (po satu)';
$string['setting:rate_limit_desc'] = 'Broj AI zahteva dozvoljenih po korisniku po satu. Postavite na 0 da biste onemogućili ograničenje. Podrazumevano: 50.';
$string['setting:system_prompt'] = 'Sistemski prompt';
$string['setting:system_prompt_desc'] = 'Prilagođeno uputstvo koje se šalje AI-ju pre svakog zahteva (primenjuje se na sve provajdere). Ostavite prazno da biste koristili ugrađeno podrazumevano uputstvo.';
$string['setting:temperature'] = 'Temperatura';
$string['setting:temperature_desc'] = 'Kontroliše nasumičnost AI odgovora (0.0 = deterministički, 2.0 = veoma kreativan). Podrazumevano: 0.7. Ne šalje se Claude-u ni GPT-5/o-serija modelima, koji prihvataju samo svoju podrazumevanu vrednost.';
$string['setting:templates'] = 'Unapred definisani šabloni (JSON)';
$string['setting:templates_desc'] = 'JSON niz šablona. Svaki šablon treba da bude objekat sa "title" i "prompt". Ako je prazno, koriste se podrazumevani šabloni.<br><br><b>Dostupni markeri:</b> {subject}, {topic}, {lesson}, {audience}, {outcomes}, {style}, {purpose}, {language}.';
$string['status_connecting'] = 'Povezivanje...';
$string['status_done'] = 'Završeno';
$string['status_error'] = 'Greška';
$string['status_error_occurred'] = 'Došlo je do greške';
$string['status_finished'] = 'Završeno';
$string['status_receiving'] = 'Prijem podataka...';
$string['status_started'] = 'Započeto';
$string['status_streaming'] = 'Strimovanje...';
$string['status_timeout'] = 'Vreme je isteklo (nedovršeno)';
$string['system_role'] = 'Vi ste koristan asistent.';
$string['template_bloom_prompt'] = 'Napravi 5 pitanja po Blumovoj taksonomiji za:
Tema: {topic}
Ciljna grupa: {audience}
Ishodi: {outcomes}';
$string['template_bloom_title'] = 'Pitanja po Blumovoj taksonomiji';
$string['template_lessonplan_prompt'] = 'Napravi strukturisanu pripremu za čas od 45 minuta.
Predmet: {subject}
Tema: {topic}
Tip nastave: {style}
Ishodi: {outcomes}';
$string['template_lessonplan_title'] = 'Nacrt pripreme za čas';
$string['template_socratic_prompt'] = 'Ti si sokratovski tutor. Pomozi korisniku da razume {topic} postavljajući pitanja koja ga vode.
Kontekst: {subject}';
$string['template_socratic_title'] = 'Sokratovski tutor';
$string['template_summary_prompt'] = 'Daj kratak sažetak ključnih pojmova za {topic},
prilagođen učenicima uzrasta: {audience}.';
$string['template_summary_title'] = 'Kratak sažetak';
$string['tooltip:editprompt'] = 'Uredite generisani prompt pre slanja';
$string['tooltip:provider_not_configured'] = 'Nijedan AI provajder nije podešen.';
$string['view:html'] = 'HTML';
$string['view:htmlcode'] = 'HTML KOD';
$string['view:raw'] = 'RAW';
$string['view:text'] = 'TEKST';
