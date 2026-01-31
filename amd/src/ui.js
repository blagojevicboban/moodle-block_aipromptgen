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
                        var form = document.getElementById('mform1') || document.getElementById('promptform') || sendBtn.closest('form');
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
                            var resp = document.getElementById('ai4t-airesponse-body') || document.getElementById('ai4t-airesponse');
                            Stream.startStream(function() {
                                return form;
                            }, gen, hidden, resp, function() {});
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
                        var btnRaw = document.getElementById('ai4t-btn-raw');
                        var btnText = document.getElementById('ai4t-btn-text');
                        var btnHtml = document.getElementById('ai4t-btn-html');
                        var btnRich = document.getElementById('ai4t-btn-rich');

                        if (btnRaw) {
                            btnRaw.classList.remove('btn-secondary');
                            btnRaw.classList.add('btn-outline-secondary');
                        }
                        if (btnText) {
                            btnText.classList.remove('btn-secondary');
                            btnText.classList.add('btn-outline-secondary');
                        }
                        if (btnHtml) {
                            btnHtml.classList.remove('btn-secondary');
                            btnHtml.classList.add('btn-outline-secondary');
                        }
                        if (btnRich) {
                            btnRich.classList.remove('btn-secondary');
                            btnRich.classList.add('btn-outline-secondary');
                        }

                        if (bodyRaw) {
                            bodyRaw.style.display = 'none';
                        }
                        if (bodyText) {
                            bodyText.style.display = 'none';
                        }
                        if (bodyHtml) {
                            bodyHtml.style.display = 'none';
                        }
                        if (bodyCode) {
                            bodyCode.style.display = 'none';
                        }

                        if (view === 'raw') {
                            if (btnRaw) {
                                btnRaw.classList.remove('btn-outline-secondary');
                                btnRaw.classList.add('btn-secondary');
                            }
                            if (bodyRaw) {
                                bodyRaw.style.display = 'block';
                            }
                        } else if (view === 'text') {
                            if (btnText) {
                                btnText.classList.remove('btn-outline-secondary');
                                btnText.classList.add('btn-secondary');
                            }
                            if (bodyText) {
                                bodyText.style.display = 'block';
                                bodyText.textContent = Markdown.renderText(bodyRaw.textContent);
                            }
                        } else if (view === 'html') {
                            if (btnHtml) {
                                btnHtml.classList.remove('btn-outline-secondary');
                                btnHtml.classList.add('btn-secondary');
                            }
                            if (bodyCode) {
                                bodyCode.style.display = 'block';
                                bodyCode.textContent = Markdown.renderMarkdown(bodyRaw.textContent);
                            }
                        } else if (view === 'rich') {
                            if (btnRich) {
                                btnRich.classList.remove('btn-outline-secondary');
                                btnRich.classList.add('btn-secondary');
                            }
                            if (bodyHtml) {
                                bodyHtml.style.display = 'block';
                                try {
                                    bodyHtml.innerHTML = Markdown.renderMarkdown(bodyRaw.textContent);
                                } catch (e) {
                                    bodyHtml.innerHTML = '<p>Error rendering Markdown.</p>';
                                }
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
                            // Fallback if status element doesn't exist yet
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

                    document.addEventListener('click', function(e) {
                        var btn = e.target.closest('button');
                        var t = e.target;
                        if (btn) {
                            if (btn.id === 'ai4t-btn-raw') {
                                setView('raw');
                            } else if (btn.id === 'ai4t-btn-text') {
                                setView('text');
                            } else if (btn.id === 'ai4t-btn-html') {
                                setView('html');
                            } else if (btn.id === 'ai4t-btn-rich') {
                                setView('rich');
                            } else if (btn.id === 'ai4t-airesponse-modal-close-btn') {
                                modal.style.display = 'none';
                                if (backdrop) {
                                    backdrop.style.display = 'none';
                                }
                            } else if (btn.id === 'ai4t-airesponse-modal-copy-btn') {
                                if (bodyHtml && bodyHtml.style.display !== 'none') {
                                    if (copyRichText(bodyHtml)) {
                                        showStatus('Copied as Rich Text!');
                                    } else {
                                        showStatus('Copy failed');
                                    }
                                } else {
                                    var text = '';
                                    if (bodyRaw && bodyRaw.style.display !== 'none') {
                                        text = bodyRaw.textContent;
                                    } else if (bodyText && bodyText.style.display !== 'none') {
                                        text = bodyText.textContent;
                                    } else if (bodyCode && bodyCode.style.display !== 'none') {
                                        text = bodyCode.textContent;
                                    }

                                    if (navigator.clipboard && navigator.clipboard.writeText) {
                                        navigator.clipboard.writeText(text).then(function() {
                                            showStatus('Copied to clipboard!');
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
                                        showStatus('Copied!');
                                    }
                                }
                            }
                        }
                        if (t && t.id === 'ai4t-airesponse-modal-close') {
                            modal.style.display = 'none';
                            if (backdrop) {
                                backdrop.style.display = 'none';
                            }
                        }
                    });

                    if (bodyRaw && bodyRaw.textContent.trim().length > 0) {
                        modal.style.display = 'block';
                        if (backdrop) {
                            backdrop.style.display = 'block';
                        }
                        setView('rich');
                    }
                };

                // Initialize all modules
                try {
                    Age.initAgeModal();
                } catch (e) {
                    // Silent fail.
                }
                try {
                    Pickers.attachPicker({
                        openId: 'ai4t-lesson-browse',
                        modalId: 'ai4t-modal',
                        closeId: 'ai4t-modal-close',
                        cancelId: 'ai4t-modal-cancel',
                        itemSelector: '.ai4t-lesson-item',
                        targetId: 'id_lesson'
                    });
                    Pickers.attachPicker({
                        openId: 'ai4t-topic-browse',
                        modalId: 'ai4t-topic-modal',
                        closeId: 'ai4t-topic-modal-close',
                        cancelId: 'ai4t-topic-modal-cancel',
                        itemSelector: '.ai4t-topic-item',
                        targetId: 'id_topic'
                    });
                    Pickers.attachOutcomesModal();
                    Pickers.initLanguageModal();
                    Pickers.attachPicker({
                        openId: 'ai4t-purpose-browse',
                        modalId: 'ai4t-purpose-modal',
                        closeId: 'ai4t-purpose-modal-close',
                        cancelId: 'ai4t-purpose-modal-cancel',
                        itemSelector: '.ai4t-purpose-item',
                        targetId: 'id_purpose'
                    });
                    Pickers.attachPicker({
                        openId: 'ai4t-audience-browse',
                        modalId: 'ai4t-audience-modal',
                        closeId: 'ai4t-audience-modal-close',
                        cancelId: 'ai4t-audience-modal-cancel',
                        itemSelector: '.ai4t-audience-item',
                        targetId: 'id_audience'
                    });
                    Pickers.attachPicker({
                        openId: 'ai4t-classtype-browse',
                        modalId: 'ai4t-classtype-modal',
                        closeId: 'ai4t-classtype-modal-close',
                        cancelId: 'ai4t-classtype-modal-cancel',
                        itemSelector: '.ai4t-classtype-item',
                        targetId: 'id_classtype'
                    });
                } catch (e) {
                    // Silent fail.
                }
                try {
                    Actions.attachCopyDownload();
                } catch (e) {
                    // Silent fail.
                }
                try {
                    initProviderSend();
                } catch (e) {
                    // Silent fail.
                }
                try {
                    initResponseModal();
                } catch (e) {
                    // Silent fail.
                }
            });
        }
    };
});
