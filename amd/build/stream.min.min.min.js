define(['block_aipromptgen/markdown'], function(Markdown) {

    const setupStreamingUI = function(resp) {
        const statusId = 'ai-response-status';
        let statusEl = document.getElementById(statusId);
        if (!statusEl) {
            statusEl = document.createElement('div');
            statusEl.id = statusId;
            statusEl.className = 'small text-muted';
            if (resp && resp.parentNode) {
                resp.parentNode.insertBefore(statusEl, resp);
            }
        }
        if (statusEl) {
            statusEl.textContent = 'Streaming...';
        }
        const modal = document.getElementById('ai4t-airesponse-modal');
        const backdrop = document.getElementById('ai4t-modal-backdrop');
        if (backdrop) {
            backdrop.style.display = 'block';
        }
        if (modal) {
            modal.style.display = 'block';
        }
        if (resp) {
            resp.textContent = '';
            resp.setAttribute('aria-busy', 'true');
        }
        return statusEl;
    };

    const attachStreamListeners = function(es, resp, statusEl, scrollToResponse) {
        let first = true;
        es.addEventListener('start', function() {
            if (statusEl) {
                statusEl.textContent = 'Started';
            }
            scrollToResponse();
        });
        es.addEventListener('chunk', function(ev) {
            if (resp) {
                resp.textContent += ev.data;
                if (first) {
                    scrollToResponse();
                    first = false;
                }
            }
        });
        es.addEventListener('error', function(ev) {
            if (resp) {
                resp.textContent += '\n[Error] ' + (ev.data || '');
            }
            if (statusEl) {
                statusEl.textContent = 'Error';
            }
            scrollToResponse();
        });
        es.addEventListener('done', function() {
            if (statusEl) {
                statusEl.textContent = 'Done';
            }
            if (resp) {
                resp.removeAttribute('aria-busy');
                resp.textContent = Markdown.autofixMarkdown(resp.textContent);
            }
            scrollToResponse();
            es.close();
        });
    };

    const startStream = function(findForm, gen, hidden, resp, scrollToResponse) {
        if (!window.EventSource) {
            const form = findForm();
            if (form) {
                form.submit();
            }
            return;
        }

        const cidEl = document.querySelector('input[name=courseid]');
        const courseid = (cidEl && cidEl.value) || '';
        hidden.value = 'ollama';

        const statusEl = setupStreamingUI(resp);
        const root = (window.M && window.M.cfg && window.M.cfg.wwwroot) ? window.M.cfg.wwwroot : '';
        const base = root + '/blocks/aipromptgen/stream.php';

        let prompt = gen.value || gen.textContent || '';
        if (!prompt) {
            const form = findForm();
            const fd = new FormData(form || undefined);
            prompt = 'Topic: ' + (fd.get('topic') || '') + '\n' +
                'Lesson: ' + (fd.get('lesson') || '') + '\n' +
                'Outcomes: ' + (fd.get('outcomes') || '');
        }

        const es = new EventSource(base + '?courseid=' + encodeURIComponent(courseid) +
            '&provider=ollama&prompt=' + encodeURIComponent(prompt));

        attachStreamListeners(es, resp, statusEl, scrollToResponse);
    };

    return {
        startStream: startStream
    };
});
