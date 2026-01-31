define([], function() {
    return {
        init: function() {
            require([
                'block_aipromptgen/age',
                'block_aipromptgen/pickers',
                'block_aipromptgen/actions',
                'block_aipromptgen/stream',
                'block_aipromptgen/markdown'
            ], function(Age, Pickers, Actions, Stream, Markdown) {

                var initProviderSend = function() {
                    var sendBtn = document.getElementById('ai4t-sendtoai');
                    var select = document.getElementById('ai4t-provider');
                    var gen = document.getElementById('ai4t-generated');
                    var hidden = document.getElementById('ai4t-sendto');

                    if (!hidden && sendBtn) {
                        var form = document.getElementById('mform1') ||
                                   document.getElementById('promptform') ||
                                   sendBtn.closest('form');
                        if (form) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'sendto';
                            hidden.id = 'ai4t-sendto';
                            form.appendChild(hidden);
                        }
                    }

                    if (!sendBtn || !select || !gen) {
                        return;
                    }

                    var refreshState = function() {
                        var opt = select.options[select.selectedIndex];
                        var unconfigured = opt && /✕\s*$/.test(opt.textContent || '');
                        sendBtn.disabled = (!gen.value.trim() || unconfigured);
                    };

                    select.addEventListener('change', refreshState);
                    gen.addEventListener('input', refreshState);

                    sendBtn.addEventListener('click', function(e) {
                        if (sendBtn.disabled) {
                            return;
                        }
                        var provider = select.value;
                        var form = document.getElementById('ai4t-send-form');

                        if (provider === 'ollama') {
                            e.preventDefault();
                            var resp = document.getElementById('ai4t-airesponse-body') ||
                                       document.getElementById('ai4t-airesponse');
                            Stream.startStream(function() {
                                return form;
                            }, gen, hidden, resp, function() {
                                // No-op scroll.
                            });
                            return;
                        }

                        hidden.value = provider;
                        if (form) {
                            form.submit();
                        }
                    });
                    refreshState();
                };

                var initResponseModal = function() {
                    var modal = document.getElementById('ai4t-airesponse-modal');
                    if (!modal) {
                        return;
                    }

                    var bodyRaw = document.getElementById('ai4t-airesponse-body');
                    var bodyText = document.getElementById('ai4t-airesponse-text');
                    var bodyHtml = document.getElementById('ai4t-airesponse-html');
                    var bodyCode = document.getElementById('ai4t-airesponse-code');
                    var backdrop = document.getElementById('ai4t-modal-backdrop');

                    var setView = function(view) {
                        var btns = ['raw', 'text', 'html', 'rich'].map(function(v) {
                            return document.getElementById('ai4t-btn-' + v);
                        });
                        var bodies = [bodyRaw, bodyText, bodyHtml, bodyCode];

                        btns.forEach(function(btn) {
                            if (btn) {
                                btn.classList.remove('btn-secondary');
                                btn.classList.add('btn-outline-secondary');
                            }
                        });
                        bodies.forEach(function(b) {
                            if (b) {
                                b.style.display = 'none';
                            }
                        });

                        applyView(view, btns, bodies, bodyRaw, bodyText, bodyCode, bodyHtml, Markdown);
                    };

                    document.addEventListener('click', function(e) {
                        handleModalClick(e, modal, backdrop, {
                            bodyRaw: bodyRaw, bodyText: bodyText, bodyHtml: bodyHtml, bodyCode: bodyCode,
                            setView: setView, showStatus: showStatus, copyRichText: copyRichText
                        });
                    });

                    if (bodyRaw && bodyRaw.textContent.trim().length > 0) {
                        modal.style.display = 'block';
                        if (backdrop) {
                            backdrop.style.display = 'block';
                        }
                        setView('rich');
                    }
                };

                var applyView = function(view, btns, bodies, bodyRaw, bodyText, bodyCode, bodyHtml, Markdown) {
                    var map = {raw: 0, text: 1, html: 2, rich: 3};
                    var idx = map[view];
                    if (btns[idx]) {
                        btns[idx].classList.remove('btn-outline-secondary');
                        btns[idx].classList.add('btn-secondary');
                    }

                    if (view === 'raw' && bodyRaw) {
                        bodyRaw.style.display = 'block';
                    } else if (view === 'text' && bodyText) {
                        bodyText.style.display = 'block';
                        bodyText.textContent = Markdown.renderText(bodyRaw.textContent);
                    } else if (view === 'html' && bodyCode) {
                        bodyCode.style.display = 'block';
                        bodyCode.textContent = Markdown.renderMarkdown(bodyRaw.textContent);
                    } else if (view === 'rich' && bodyHtml) {
                        bodyHtml.style.display = 'block';
                        try {
                            bodyHtml.innerHTML = Markdown.renderMarkdown(bodyRaw.textContent);
                        } catch (e) {
                            bodyHtml.innerHTML = '<p>Error rendering Markdown.</p>';
                        }
                    }
                };

                var handleModalClick = function(e, modal, backdrop, refs) {
                    var btn = e.target.closest('button');
                    var t = e.target;
                    if (btn) {
                        if (btn.id === 'ai4t-btn-raw') {
                            refs.setView('raw');
                        } else if (btn.id === 'ai4t-btn-text') {
                            refs.setView('text');
                        } else if (btn.id === 'ai4t-btn-html') {
                            refs.setView('html');
                        } else if (btn.id === 'ai4t-btn-rich') {
                            refs.setView('rich');
                        } else if (btn.id === 'ai4t-airesponse-modal-close-btn') {
                            modal.style.display = 'none';
                            if (backdrop) {
                                backdrop.style.display = 'none';
                            }
                        } else if (btn.id === 'ai4t-airesponse-modal-copy-btn') {
                            handleCopy(refs);
                        }
                    }
                    if (t && t.id === 'ai4t-airesponse-modal-close') {
                        modal.style.display = 'none';
                        if (backdrop) {
                            backdrop.style.display = 'none';
                        }
                    }
                };

                var handleCopy = function(refs) {
                    if (refs.bodyHtml && refs.bodyHtml.style.display !== 'none') {
                        if (refs.copyRichText(refs.bodyHtml)) {
                            refs.showStatus('Copied as Rich Text!');
                        } else {
                            refs.showStatus('Copy failed');
                        }
                    } else {
                        var text = '';
                        if (refs.bodyRaw && refs.bodyRaw.style.display !== 'none') {
                            text = refs.bodyRaw.textContent;
                        } else if (refs.bodyText && refs.bodyText.style.display !== 'none') {
                            text = refs.bodyText.textContent;
                        } else if (refs.bodyCode && refs.bodyCode.style.display !== 'none') {
                            text = refs.bodyCode.textContent;
                        }

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(function() {
                                refs.showStatus('Copied to clipboard!');
                                return;
                            }).catch(function() {
                                // Silent fail.
                            });
                        } else {
                            // Fallback
                            var ta = document.createElement('textarea');
                            ta.value = text;
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            document.body.removeChild(ta);
                            refs.showStatus('Copied!');
                        }
                    }
                };

                var showStatus = function(msg) {
                    var status = document.getElementById('ai4t-modal-copy-status');
                    if (status) {
                        status.textContent = msg;
                        status.style.display = 'inline';
                        setTimeout(function() {
                            status.style.display = 'none';
                        }, 2000);
                    } else {
                        window.console.log(msg);
                    }
                };

                var copyRichText = function(el) {
                    try {
                        var range = document.createRange();
                        range.selectNode(el);
                        var selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        var successful = document.execCommand('copy');
                        selection.removeAllRanges();
                        return successful;
                    } catch (e) {
                        return false;
                    }
                };

                // Initialize all modules
                var inits = [
                    function() {
                        Age.initAgeModal();
                    },
                    function() {
                        Pickers.attachPicker({
                            openId: 'ai4t-lesson-browse', modalId: 'ai4t-modal',
                            closeId: 'ai4t-modal-close', cancelId: 'ai4t-modal-cancel',
                            itemSelector: '.ai4t-lesson-item', targetId: 'id_lesson'
                        });
                        Pickers.attachPicker({
                            openId: 'ai4t-topic-browse', modalId: 'ai4t-topic-modal',
                            closeId: 'ai4t-topic-modal-close', cancelId: 'ai4t-topic-modal-cancel',
                            itemSelector: '.ai4t-topic-item', targetId: 'id_topic'
                        });
                        Pickers.attachOutcomesModal();
                        Pickers.initLanguageModal();
                        ['purpose', 'audience', 'classtype'].forEach(function(k) {
                            Pickers.attachPicker({
                                openId: 'ai4t-' + k + '-browse', modalId: 'ai4t-' + k + '-modal',
                                closeId: 'ai4t-' + k + '-modal-close', cancelId: 'ai4t-' + k + '-modal-cancel',
                                itemSelector: '.ai4t-' + k + '-item', targetId: 'id_' + k
                            });
                        });
                    },
                    function() {
                        Actions.attachCopyDownload();
                    },
                    function() {
                        initProviderSend();
                    },
                    function() {
                        initResponseModal();
                    }
                ];

                inits.forEach(function(fn) {
                    try {
                        fn();
                    } catch (e) {
                        /* Silent fail */
                    }
                });
            });
        }
    };
});
