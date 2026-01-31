define([], function() {
    /**
     * Helper to inject newlines before markdown blocks and fix common AI output issues.
     *
     * @param {string} text
     * @returns {string}
     */
    const autofixMarkdown = function(text) {
        if (!text) {
            return text;
        }
        let res = text;

        // 1. Fix clumped numbers like "cars.2. Machine" -> "cars.\n2. Machine"
        res = res.replace(/([\.\!\?])(\s*)(\d+\.\s+)/g, '$1\n$3');

        // 2. Fix bold markers that wrap newlines or have spaces inside like "** Title **" or "**Title\n**"
        res = res.replace(/(\*\*)(?:\s*\n+\s*|\s+)/g, '$1'); // Remove space/newline after opening **
        res = res.replace(/(?:\s*\n+\s*|\s+)(\*\*)/g, '$1'); // Remove space/newline before closing **

        // 3. Fix broken bold markers from AI like "* *" -> "**"
        res = res.replace(/\*\s+\*/g, '**');

        // 4. Force newlines before list items or headers if they are clumped with text
        res = res.replace(/([a-z\u00C0-\u00FF0-9\.\!\?])(\s*)([\*\-\+] |\d+\. |#{1,6} )/g, '$1\n$3');

        // 5. Convert plus-sign lists to standard asterisk lists
        res = res.replace(/^\s*\+\s+/gm, '* ');

        // 4. Existing fixes
        res = res.replace(/([^\n])(\*\*\*\*.*?\*\*\*\*)/g, '$1\n\n$2');
        res = res.replace(/(\*\*\*\*.*?\*\*\*\*)([^\n])/g, '$1\n\n$2');
        res = res.replace(/(:)(\*\*\*)/g, '$1**\n* ');
        res = res.replace(/([^\n])(\*\*\*)(\s)/g, '$1**\n*$3');
        res = res.replace(/([\.\?\!\)])\s*(\* )/g, '$1\n$2');
        res = res.replace(/([^\n])(\*\*\*\*)(?=\S)/g, '**\n\n**');
        res = res.replace(/([^\n])(\*\*|__)(?=[a-zA-Z0-9\u00C0-\u00FF])/g, '$1\n$2');
        res = res.replace(/([^\n])(^|\s)([\*\-\+] )/g, '$1\n$3');
        res = res.replace(/([^\n])(#{1,6} )/g, '$1\n$2');
        res = res.replace(/([^\n])(^|\s)(\*\*\*|---|___)(\s|$)/gm, '$1\n$3');
        res = res.replace(/([^\n])(\s)([IVX]+|[ivx]+)(\.)/g, '$1\n$3$4');

        // Clean up excessive newlines
        res = res.replace(/\n{3,}/g, '\n\n');
        return res;
    };

    /**
     * Process inline elements like images, links, bold, italic.
     *
     * @param {string} text
     * @returns {string}
     */
    const processInline = function(text) {
        if (!text) {
            return '';
        }
        return text
            .replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1" style="max-width:100%;height:auto;">')
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
            .replace(/\*\*\*\*(.*?)\*\*\*\*/g, '<h3>$1</h3>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code style="background:#eee;padding:2px 4px;border-radius:3px;">$1</code>');
    };

    /**
     * Render Markdown to HTML.
     *
     * @param {string} md
     * @returns {string}
     */
    const renderMarkdown = function(md) {
        if (!md) {
            return '';
        }
        md = autofixMarkdown(md);
        md = md.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var lines = md.split(/\r?\n/);
        var html = '';
        var inList = false;
        var inCodeBlock = false;
        var listType = null;

        lines.forEach(function (line) {
            if (line.trim().startsWith('```')) {
                if (inCodeBlock) {
                    inCodeBlock = false;
                    html += '</code></pre>';
                } else {
                    if (inList) {
                        html += (listType === 'ul' ? '</ul>' : '</ol>');
                        inList = false;
                    }
                    html += '<pre><code style="display:block;background:#f4f4f4;padding:10px;border-radius:5px;overflow-x:auto;font-family:monospace;">';
                    inCodeBlock = true;
                }
                return;
            }

            if (inCodeBlock) {
                html += line + '\n';
                return;
            }

            var trimmed = line.trim();
            if (trimmed === '') {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<br>';
                return;
            }

            if (trimmed.startsWith('###### ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h6>' + processInline(trimmed.substring(7)) + '</h6>';
            } else if (trimmed.startsWith('##### ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h5>' + processInline(trimmed.substring(6)) + '</h5>';
            } else if (trimmed.startsWith('#### ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h4>' + processInline(trimmed.substring(5)) + '</h4>';
            } else if (trimmed.startsWith('### ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h3>' + processInline(trimmed.substring(4)) + '</h3>';
            } else if (trimmed.startsWith('## ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h2>' + processInline(trimmed.substring(3)) + '</h2>';
            } else if (trimmed.startsWith('# ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<h1>' + processInline(trimmed.substring(2)) + '</h1>';
            } else if (trimmed.match(/^(\*{3,}|-{3,}|_{3,})$/)) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<hr>';
            } else if (trimmed.match(/^([IVX]+)\.\s+(.*)/)) {
                var content = trimmed.match(/^([IVX]+)\.\s+(.*)/)[2];
                if (!inList || listType !== 'ol_roman') {
                    if (inList) {
                        html += (listType === 'ul' ? '</ul>' : '</ol>');
                    }
                    html += '<ol type="I">';
                    inList = true;
                    listType = 'ol_roman';
                }
                html += '<li>' + processInline(content) + '</li>';
            } else if (trimmed.match(/^[\*\-\+]\s+(.*)/)) {
                var content2 = trimmed.match(/^[\*\-\+]\s+(.*)/)[1];
                if (!inList || listType !== 'ul') {
                    if (inList) {
                        html += (listType === 'ul' ? '</ul>' : '</ol>');
                    }
                    html += '<ul>';
                    inList = true;
                    listType = 'ul';
                }
                html += '<li>' + processInline(content2) + '</li>';
            } else if (trimmed.match(/^\d+\.\s+(.*)/)) {
                var content3 = trimmed.match(/^\d+\.\s+(.*)/)[1];
                if (!inList || listType !== 'ol') {
                    if (inList) {
                        html += (listType === 'ul' ? '</ul>' : '</ol>');
                    }
                    html += '<ol>';
                    inList = true;
                    listType = 'ol';
                }
                html += '<li>' + processInline(content3) + '</li>';
            } else if (trimmed.startsWith('> ')) {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                html += '<blockquote style="border-left:4px solid #ccc;padding-left:10px;color:#666;">' +
                    processInline(trimmed.substring(2)) + '</blockquote>';
            } else {
                if (inList) {
                    html += (listType === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                var cleanTrimmed = trimmed.replace(/^\*(?!\*)\s*/, '');
                html += '<p>' + processInline(cleanTrimmed) + '</p>';
            }
        });
        if (inList) {
            html += (listType === 'ul' ? '</ul>' : '</ol>');
        }
        return html;
    };

    /**
     * Clean markdown for plain text view.
     *
     * @param {string} md
     * @returns {string}
     */
    const renderText = function(md) {
        if (!md) {
            return '';
        }
        var txt = autofixMarkdown(md);
        txt = txt.replace(/\*\*\*\*(.*?)\*\*\*\*/g, '$1');
        var prev = '';
        while (txt !== prev) {
            prev = txt;
            txt = txt.replace(/(\*\*|__)(.*?)\1/g, '$2').replace(/(\*|_)(.*?)\1/g, '$2');
        }
        txt = txt.replace(/^#+\s+(.*)$/gm, '\n$1\n' + '-'.repeat(20));
        txt = txt.replace(/^[\*\-\+]\s+/gm, '- ');
        txt = txt.replace(/```/g, '');
        txt = txt.replace(/^-\s*\*+/gm, '');
        txt = txt.replace(/\n{3,}/g, '\n\n');
        return txt.trim();
    };

    return {
        autofixMarkdown: autofixMarkdown,
        renderMarkdown: renderMarkdown,
        renderText: renderText
    };
});
