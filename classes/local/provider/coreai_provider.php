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

// Autoloaded class file; MOODLE_INTERNAL guard intentionally omitted (namespace auto-loaded).
namespace block_aipromptgen\local\provider;
use moodle_exception;
/**
 * Wrapper provider that delegates to the Moodle core AI manager if present.
 * It discovers an appropriate completion-like method and normalises its output to a string.
 * @package   block_aipromptgen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coreai_provider implements provider_interface {
    /** @var string|null Preferred model */
    protected $model;
    /** @var string|null Specific method override */
    protected $methodoverride;

    public function __construct(?string $model = null) {
        $this->model = $model;
        if (function_exists('get_config')) {
            $mo = get_config('block_aipromptgen', 'coreai_method');
            $this->methodoverride = $mo ? trim($mo) : null;
        }
    }

    public function complete(string $prompt): string {
        if (!class_exists('core_ai\\manager') && !class_exists('\\core_ai\\manager')) {
            throw new \Exception('core AI manager not present');
        }
        $managerclass = class_exists('\\core_ai\\manager') ? '\\core_ai\\manager' : 'core_ai\\manager';
        // Instantiate manager.
        if (method_exists($managerclass, 'instance')) {
            $manager = $managerclass::instance();
        } elseif (method_exists($managerclass, 'get')) {
            $manager = $managerclass::get();
        } else {
            $ref = new \ReflectionClass($managerclass);
            $ctor = $ref->getConstructor();
            if ($ctor === null) {
                $manager = new $managerclass();
            } else {
                $args = [];
                foreach ($ctor->getParameters() as $p) {
                    $type = $p->getType();
                    $injected = false;
                    if ($type instanceof \ReflectionNamedType) {
                        $tname = ltrim($type->getName(), '\\');
                        if ($tname === 'moodle_database') {
                            global $DB; $args[] = $DB; $injected = true;
                        } elseif (in_array($tname, ['stdClass','config','object'])) {
                            $args[] = (object)[]; $injected = true;
                        }
                    }
                    if (!$injected) {
                        $args[] = $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
                    }
                }
                try {
                    $manager = $ref->newInstanceArgs($args);
                } catch (\Throwable $e) {
                    throw new \Exception('core AI manager instantiation failed (DI): ' . $e->getMessage());
                }
            }
        }

        $options = [];
        if ($this->model) { $options['model'] = $this->model; }

        // Determine candidate methods.
        $methods = get_class_methods($manager);
        $candidates = [];
        if ($this->methodoverride && in_array($this->methodoverride, $methods)) {
            $candidates[] = $this->methodoverride;
        }
    $priority = ['complete','chat_complete','generate','chat','completion','create_completion','invoke','process','predict','summarise','summarize'];
        foreach ($priority as $m) { if (in_array($m, $methods) && !in_array($m, $candidates)) { $candidates[] = $m; } }
        foreach ($methods as $m) {
            if (preg_match('/(complete|chat|generate|summary|summar|prompt)/i', $m) && !in_array($m, $candidates)) {
                $candidates[] = $m;
            }
        }

        $response = null;
        foreach ($candidates as $m) {
            try {
                // Skip process_action because it requires an action object (core_ai\aiactions\base), not a raw prompt.
                if ($m === 'process_action') { continue; }
                $response = $manager->$m($prompt, $options);
                break;
            } catch (\Throwable $e) {
                $response = null; continue; }
        }
        if ($response === null) {
            throw new \Exception('No suitable core AI manager method (available: '.implode(', ', $methods).')');
        }

        // Interpret response forms.
        if (is_string($response)) { return $response; }
        if (is_object($response)) {
            if (isset($response->text)) return (string)$response->text;
            if (isset($response->content)) return (string)$response->content;
            if (isset($response->choices[0]->message->content)) return (string)$response->choices[0]->message->content;
        }
        if (is_array($response)) {
            if (isset($response['text'])) return (string)$response['text'];
            if (isset($response['content'])) return (string)$response['content'];
            if (isset($response['choices'][0]['message']['content'])) return (string)$response['choices'][0]['message']['content'];
        }
        return is_scalar($response) ? (string)$response : json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
