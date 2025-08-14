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
 * Portuguese language strings for block_aipromptgen.
 *
 * @package    block_aipromptgen
 * @category   string
 * @copyright  2025 AI4Teachers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Ferramentas de IA para professores – gerador de prompts';
$string['openpromptbuilder'] = 'Abrir Gerador de Prompts de IA';
$string['notallowed'] = 'Esta ferramenta está disponível apenas para professores e gestores neste curso.';
$string['privacy:metadata'] = 'O bloco Gerador de Prompts de IA não armazena dados pessoais.';

// Form.
$string['form:subjectlabel'] = 'Disciplina';
$string['form:agerangelabel'] = 'Idade/série dos alunos';
$string['form:lessonlabel'] = 'Título da lição';
$string['form:lessonbrowse'] = 'Procurar…';
$string['form:class_typelabel'] = 'Tipo de aula';
$string['form:outcomeslabel'] = 'Resultados / objetivos';
$string['form:topiclabel'] = 'Tema de ensino (área)';
$string['form:topicbrowse'] = 'Procurar…';
$string['form:language'] = 'Idioma do prompt';
$string['form:purpose'] = 'Finalidade do prompt (ex.: plano de aula, quiz, rubrica)';
$string['form:course'] = 'Curso';
$string['form:submit'] = 'Gerar prompt';
$string['form:result'] = 'Prompt de IA gerado';
$string['form:audience'] = 'Público (para professores ou para alunos)';

// Options.
$string['option:lessonplan'] = 'Plano de aula';
$string['option:quiz'] = 'Perguntas de quiz';
$string['option:rubric'] = 'Rubrica de avaliação';
$string['option:worksheet'] = 'Ficha de atividades';
$string['option:teacher'] = 'Para professores';
$string['option:student'] = 'Para alunos';

// Idiomas.
$string['lang:sr'] = 'Sérvio';
$string['lang:en'] = 'Inglês';
$string['lang:pt'] = 'Português';
$string['lang:sk'] = 'Eslovaco';
$string['lang:sr_cr'] = 'Sérvio (cirílico)';

// Rótulos do prompt.
$string['label:purpose'] = 'Finalidade';
$string['label:audience'] = 'Público';
$string['label:language'] = 'Idioma';
$string['label:subject'] = 'Disciplina';
$string['label:agerange'] = 'Idade/série dos alunos';
$string['label:lesson'] = 'Título da lição';
$string['label:classtype'] = 'Tipo de aula';
$string['label:outcomes'] = 'Resultados';
$string['label:topic'] = 'Tema de ensino (área)';

// Modelos de prompt.
$string['prompt:prefix'] = 'Você é um especialista em design instrucional ajudando um professor no curso do Moodle {$a->course}.';

// Opções de tipo de aula.
$string['classtype:lecture'] = 'Aula expositiva';
$string['classtype:discussion'] = 'Discussão';
$string['classtype:groupwork'] = 'Trabalho em grupo';
$string['classtype:lab'] = 'Laboratório/Prática';
$string['classtype:project'] = 'Baseada em projetos';
$string['classtype:review'] = 'Revisão/Recapitulação';
$string['classtype:assessment'] = 'Avaliação/Teste';

// Actions.
$string['form:copy'] = 'Copiar para a área de transferência';
$string['form:copied'] = 'Copiado!';
$string['form:download'] = 'Baixar .txt';
$string['form:reset'] = 'Limpar prompt guardado';
$string['form:backtocourse'] = 'Voltar ao curso';

// Capabilities.
$string['aipromptgen:manage'] = 'Usar o bloco Gerador de Prompts de IA no curso';
$string['aipromptgen:addinstance'] = 'Adicionar um novo bloco Gerador de Prompts de IA';
$string['aipromptgen:myaddinstance'] = 'Adicionar o bloco Gerador de Prompts de IA ao Painel';
