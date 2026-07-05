(function () {
    const toolbarContainer = [
        ['bold', 'italic', 'underline'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'email'],
        ['clean'],
    ];

    const EMAIL_TOOLBAR_ICON = [
        '<svg viewBox="0 0 18 18" aria-hidden="true">',
        '<rect class="ql-stroke" height="9" rx="0.5" ry="0.5" width="12" x="3" y="4.5"></rect>',
        '<polyline class="ql-stroke" points="3 5.5 9 10 15 5.5"></polyline>',
        '</svg>',
    ].join('');

    function decorateEmailToolbarButton(wrapper) {
        const button = wrapper.querySelector('.ql-toolbar button.ql-email');
        if (!button || button.dataset.iconReady === '1') {
            return;
        }

        button.dataset.iconReady = '1';
        button.innerHTML = EMAIL_TOOLBAR_ICON;
        button.setAttribute('title', 'Inserir e-mail (mailto)');
        button.setAttribute('aria-label', 'Inserir e-mail');
    }

    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
    }

    function normalizeLinkHref(value) {
        const v = value.trim();
        if (!v) {
            return '';
        }
        if (/^mailto:/i.test(v)) {
            return 'mailto:' + v.replace(/^mailto:/i, '').trim();
        }
        if (/^https?:\/\//i.test(v)) {
            return v;
        }
        if (isValidEmail(v)) {
            return 'mailto:' + v;
        }
        return 'https://' + v.replace(/^\/+/, '');
    }

    function linkHandler(value) {
        const quill = this.quill;
        if (value === true) {
            const current = quill.getFormat().link;
            const promptValue = current
                ? String(current).replace(/^mailto:/i, '')
                : '';
            value = window.prompt(
                'Link (URL https://… ou e-mail — e-mails viram link mailto):',
                promptValue
            );
            if (value === null) {
                return;
            }
        }
        const href = normalizeLinkHref(value || '');
        if (!href) {
            quill.format('link', false);
            return;
        }
        quill.format('link', href);
    }

    function emailHandler() {
        const quill = this.quill;
        const range = quill.getSelection(true);
        if (!range) {
            return;
        }

        const current = quill.getFormat(range).link;
        const initial = current && String(current).startsWith('mailto:')
            ? String(current).replace(/^mailto:/i, '')
            : (range.length > 0 ? quill.getText(range.index, range.length).trim() : '');

        const value = window.prompt('Endereço de e-mail:', initial);
        if (value === null) {
            return;
        }

        const email = value.trim().replace(/^mailto:/i, '');
        if (!isValidEmail(email)) {
            window.alert('Informe um e-mail válido (ex.: pesquisador@universidade.edu.br).');
            return;
        }

        const mailto = 'mailto:' + email;

        if (range.length === 0) {
            quill.insertText(range.index, email, { link: mailto });
            quill.setSelection(range.index + email.length, 0);
        } else {
            quill.formatText(range.index, range.length, 'link', mailto);
        }
    }

    function syncQuillToInput(quill, input) {
        const html = quill.root.innerHTML;
        const empty = quill.getText().trim() === '';
        input.value = empty ? '' : html;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function isInsideHiddenCollapse(wrapper) {
        const collapse = wrapper.closest('.collapse');
        return collapse !== null && !collapse.classList.contains('show');
    }

    function resetRichTextField(wrapper) {
        delete wrapper.dataset.richTextReady;
        const area = wrapper.querySelector('[data-rich-text-area]');
        if (area) {
            area.innerHTML = '';
        }
    }

    function initRichTextField(wrapper) {
        if (wrapper.dataset.richTextReady === '1') {
            return;
        }

        if (isInsideHiddenCollapse(wrapper)) {
            return;
        }

        const area = wrapper.querySelector('[data-rich-text-area]');
        const input = wrapper.querySelector('[data-rich-text-input]');
        if (!area || !input || typeof Quill === 'undefined') {
            return;
        }

        const quill = new Quill(area, {
            theme: 'snow',
            placeholder: wrapper.dataset.placeholder || 'Digite aqui…',
            modules: {
                toolbar: {
                    container: toolbarContainer,
                    handlers: {
                        link: linkHandler,
                        email: emailHandler,
                    },
                },
            },
        });

        const initial = input.value || '';
        if (initial.trim() !== '') {
            quill.clipboard.dangerouslyPasteHTML(initial);
        }

        quill.on('text-change', () => syncQuillToInput(quill, input));

        decorateEmailToolbarButton(wrapper);

        const form = wrapper.closest('form');
        if (form) {
            form.addEventListener('submit', () => syncQuillToInput(quill, input));
        }

        wrapper.dataset.richTextReady = '1';
    }

    function initEditorsIn(root) {
        const scope = root || document;
        scope.querySelectorAll('[data-rich-text]').forEach(initRichTextField);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initEditorsIn(document));
    } else {
        initEditorsIn(document);
    }

    window.initRichTextEditors = () => initEditorsIn(document);

    document.addEventListener('shown.bs.collapse', (event) => {
        const collapse = event.target;
        if (!collapse?.classList?.contains('collapse')) {
            return;
        }
        collapse.querySelectorAll('[data-rich-text]').forEach(resetRichTextField);
        initEditorsIn(collapse);
    });

    window.addEventListener('load', () => {
        initEditorsIn(document.getElementById('apresentacao-estudo') || document);
    });
})();
