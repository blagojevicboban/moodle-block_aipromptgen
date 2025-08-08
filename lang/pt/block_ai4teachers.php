<?php
// Portuguese strings for block_ai4teachers (pt)

$string['pluginname'] = 'IA para Professores';
$string['openpromptbuilder'] = 'Abrir Gerador de Prompts de IA';
$string['notallowed'] = 'Esta ferramenta está disponível apenas para professores e gestores neste curso.';
$string['privacy:metadata'] = 'O bloco IA para Professores não armazena dados pessoais.';

// Form
$string['form:subjectlabel'] = 'Disciplina';
$string['form:agerangelabel'] = 'Idade/série dos alunos';
$string['form:lessonlabel'] = 'Título da lição';
$string['form:class_typelabel'] = 'Tipo de aula';
$string['form:outcomeslabel'] = 'Resultados / objetivos';
$string['form:language'] = 'Idioma do prompt';
$string['form:purpose'] = 'Finalidade do prompt (ex.: plano de aula, quiz, rubrica)';
$string['form:course'] = 'Curso';
$string['form:submit'] = 'Gerar prompt';
$string['form:result'] = 'Prompt de IA gerado';
$string['form:audience'] = 'Público (para professores ou para alunos)';

// Options
$string['option:lessonplan'] = 'Plano de aula';
$string['option:quiz'] = 'Perguntas de quiz';
$string['option:rubric'] = 'Rubrica de avaliação';
$string['option:worksheet'] = 'Ficha de atividades';
$string['option:teacher'] = 'Para professores';
$string['option:student'] = 'Para alunos';
// Idiomas
$string['lang:sr'] = 'Sérvio';
$string['lang:en'] = 'Inglês';
$string['lang:pt'] = 'Português';
$string['lang:sk'] = 'Eslovaco';
$string['lang:sr_cr'] = 'Sérvio (cirílico)';

// Rótulos do prompt
$string['label:purpose'] = 'Finalidade';
$string['label:audience'] = 'Público';
$string['label:language'] = 'Idioma';
$string['label:subject'] = 'Disciplina';
$string['label:agerange'] = 'Idade/série dos alunos';
$string['label:lesson'] = 'Título da lição';
$string['label:classtype'] = 'Tipo de aula';
$string['label:outcomes'] = 'Resultados';

// Modelos de prompt
$string['prompt:prefix'] = "Você é um especialista em design instrucional ajudando um professor no curso do Moodle '{$a->course}'.";
$string['prompt:instructions'] = 'Gere a saída no idioma selecionado. Alinhe estritamente à finalidade e aos resultados, no nível apropriado para a idade/série indicada. Prefira o alinhamento ao currículo local quando aplicável.';

// Opções de tipo de aula
$string['classtype:lecture'] = 'Aula expositiva';
$string['classtype:discussion'] = 'Discussão';
$string['classtype:groupwork'] = 'Trabalho em grupo';
$string['classtype:lab'] = 'Laboratório/Prática';
$string['classtype:project'] = 'Baseada em projetos';
$string['classtype:review'] = 'Revisão/Recapitulação';
$string['classtype:assessment'] = 'Avaliação/Teste';

// Actions
$string['form:copy'] = 'Copiar para a área de transferência';
$string['form:copied'] = 'Copiado!';
$string['form:download'] = 'Baixar .txt';
$string['form:reset'] = 'Limpar prompt guardado';
$string['form:backtocourse'] = 'Voltar ao curso';

// Capabilities
$string['ai4teachers:manage'] = 'Usar o bloco IA para Professores no curso';
$string['ai4teachers:addinstance'] = 'Adicionar um novo bloco IA para Professores';
$string['ai4teachers:myaddinstance'] = 'Adicionar o bloco IA para Professores ao Painel';
