(function () {
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function syncPanels(form) {
        const select = form.querySelector('[data-qb-tipo-select]');
        const tipo = select?.value || 'texto';
        const hint = form.querySelector('[data-qb-tipo-hint]');
        const option = select?.selectedOptions[0];
        if (hint && option) {
            hint.textContent = option.dataset.descricao || '';
        }

        form.querySelectorAll('[data-qb-panel]').forEach((panel) => {
            const panelId = panel.dataset.qbPanel || '';
            const kinds = panelId.split(',');
            let show;
            if (panelId === 'pergunta') {
                show = tipo !== 'tela_sistema';
            } else {
                show = kinds.includes(tipo);
            }
            panel.classList.toggle('d-none', !show);

            if (panelId === 'pergunta') {
                const enunciado = form.querySelector('[name="enunciado"]');
                if (enunciado) enunciado.required = show;
            }
            if (panelId === 'tela_sistema') {
                const rota = form.querySelector('[name="rota_sistema"]');
                const intro = form.querySelector('[name="texto_intro"]');
                if (rota) rota.required = show;
                if (intro) intro.required = show;
            }
        });

        if (tipo === 'tela_sistema' && typeof window.initRichTextEditors === 'function') {
            window.initRichTextEditors();
        }

        const obrWrap = form.querySelector('[data-qb-obrigatoria-wrap]');
        if (obrWrap) {
            obrWrap.classList.toggle('d-none', tipo === 'tela_sistema');
        }

        updatePreview(form, tipo);
    }

    function updatePreview(form, tipo) {
        const preview = form.querySelector('[data-qb-preview]');
        if (!preview) return;

        const enunciado = form.querySelector('[name="enunciado"]')?.value?.trim() || 'Pergunta sem título';
        const legenda = form.querySelector('[name="legenda"]')?.value?.trim() || '';
        const obrigatoria = form.querySelector('[name="obrigatoria"]')?.checked;
        const min = parseInt(form.querySelector('[name="escala_min"]')?.value || '1', 10);
        const max = parseInt(form.querySelector('[name="escala_max"]')?.value || '5', 10);
        const minRotulo = form.querySelector('[name="escala_min_rotulo"]')?.value?.trim() || '';
        const maxRotulo = form.querySelector('[name="escala_max_rotulo"]')?.value?.trim() || '';

        let html = '';

        if (tipo === 'tela_sistema') {
            const introRaw = form.querySelector('[name="texto_intro"]')?.value?.trim() || '';
            const rotaSelect = form.querySelector('[name="rota_sistema"]');
            const label = rotaSelect?.selectedOptions[0]?.text?.trim() || 'Tela da aplicação';
            html += introRaw
                ? `<div class="rich-text-content text-muted small mb-2">${introRaw}</div>`
                : '<p class="text-muted small mb-2">Texto introdutório</p>';
            html += `<a class="btn btn-sm btn-primary disabled" tabindex="-1">${escapeHtml(label)}</a>`;
        } else {
            html += `<p class="mb-1 fw-medium">${escapeHtml(enunciado)}${obrigatoria ? '<span class="text-danger">*</span>' : ''}</p>`;
            if (legenda) {
                html += `<p class="text-muted small mb-2">${escapeHtml(legenda)}</p>`;
            }

            if (tipo === 'textarea') {
                html += '<div class="small text-muted border rounded p-3" style="min-height:120px">Editor de texto longo com negrito, itálico e listas</div>';
            } else if (tipo === 'escala') {
                html += '<div class="escala-linear">';
                if (minRotulo) {
                    html += `<span class="escala-linear__rotulo escala-linear__rotulo--min">${escapeHtml(minRotulo)}</span>`;
                }
                html += '<div class="escala-linear__opcoes">';
                for (let i = min; i <= max; i++) {
                    html += `<span class="escala-linear__valor escala-linear__valor--preview">${i}</span>`;
                }
                html += '</div>';
                if (maxRotulo) {
                    html += `<span class="escala-linear__rotulo escala-linear__rotulo--max">${escapeHtml(maxRotulo)}</span>`;
                }
                html += '</div>';
            } else if (tipo === 'unica' || tipo === 'multipla') {
                const inputType = tipo === 'multipla' ? 'checkbox' : 'radio';
                const inputs = form.querySelectorAll('[data-qb-opcao-input]');
                if (inputs.length === 0) {
                    html += '<div class="form-check text-muted"><label class="form-check-label">Opção 1</label></div>';
                } else {
                    inputs.forEach((input, idx) => {
                        const val = input.value.trim() || `Opção ${idx + 1}`;
                        html += `<div class="form-check"><input class="form-check-input" type="${inputType}" disabled><label class="form-check-label">${escapeHtml(val)}</label></div>`;
                    });
                }
            } else {
                html += '<input type="text" class="form-control" disabled placeholder="Resposta curta">';
            }
        }

        preview.innerHTML = html;
    }

    function collectOpcoes(form) {
        const lines = [];
        form.querySelectorAll('[data-qb-opcao-input]').forEach((input) => {
            const v = input.value.trim();
            if (v) lines.push(v);
        });
        const hidden = form.querySelector('[data-qb-opcoes-hidden]');
        if (hidden) hidden.value = lines.join('\n');
    }

    function isTipoComOpcoes(form) {
        const tipo = form?.querySelector('[data-qb-tipo-select]')?.value || '';
        return tipo === 'unica' || tipo === 'multipla';
    }

    function bindOpcaoInput(input, list) {
        const form = list.closest('form');

        input.addEventListener('input', () => {
            collectOpcoes(form);
            syncPanels(form);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' || !isTipoComOpcoes(form)) return;

            e.preventDefault();

            const row = input.closest('.input-group');
            const novoInput = addOpcaoRow(list, '', row);
            novoInput?.focus();
            collectOpcoes(form);
            syncPanels(form);
        });
    }

    function addOpcaoRow(list, value = '', afterRow = null) {
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-2';
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.dataset.qbOpcaoInput = '';
        input.placeholder = 'Opção';
        input.value = value;
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'action-link action-link--danger';
        removeBtn.textContent = 'remover';
        removeBtn.dataset.qbRemoveOpcao = '';
        row.append(input, removeBtn);

        if (afterRow?.parentElement === list && afterRow.nextSibling) {
            list.insertBefore(row, afterRow.nextSibling);
        } else if (afterRow?.parentElement === list) {
            list.appendChild(row);
        } else {
            list.appendChild(row);
        }

        bindOpcaoInput(input, list);

        removeBtn.addEventListener('click', () => {
            row.remove();
            collectOpcoes(list.closest('form'));
            syncPanels(list.closest('form'));
        });

        return input;
    }

    function initOpcoesList(form) {
        const list = form.querySelector('[data-qb-opcoes-list]');
        if (!list) return;

        let iniciais = [];
        try {
            iniciais = JSON.parse(form.dataset.qbOpcoesInicial || '[]');
        } catch {
            iniciais = [];
        }

        list.innerHTML = '';
        if (iniciais.length > 0) {
            iniciais.forEach((valor) => addOpcaoRow(list, valor));
        } else {
            addOpcaoRow(list, 'Opção 1');
            addOpcaoRow(list, 'Opção 2');
        }
        collectOpcoes(form);
    }

    function resetNovaPerguntaForm(form) {
        if (!form?.hasAttribute('data-qb-form-add')) return;

        const tipoSelect = form.querySelector('[data-qb-tipo-select]');
        if (tipoSelect) {
            tipoSelect.selectedIndex = 0;
        }

        const rotaSelect = form.querySelector('[name="rota_sistema"]');
        if (rotaSelect) {
            rotaSelect.selectedIndex = 0;
        }

        const escalaMin = form.querySelector('[name="escala_min"]');
        const escalaMax = form.querySelector('[name="escala_max"]');
        if (escalaMin) escalaMin.value = '1';
        if (escalaMax) escalaMax.value = '5';

        const escalaMinRotulo = form.querySelector('[name="escala_min_rotulo"]');
        const escalaMaxRotulo = form.querySelector('[name="escala_max_rotulo"]');
        if (escalaMinRotulo) escalaMinRotulo.value = '';
        if (escalaMaxRotulo) escalaMaxRotulo.value = '';

        const obrigatoria = form.querySelector('[name="obrigatoria"]');
        if (obrigatoria) obrigatoria.checked = true;

        const opcoesHidden = form.querySelector('[data-qb-opcoes-hidden]');
        if (opcoesHidden) opcoesHidden.value = '';

        const legenda = form.querySelector('[name="legenda"]');
        if (legenda) legenda.value = '';

        form.querySelectorAll('[data-rich-text]').forEach((wrapper) => {
            const input = wrapper.querySelector('[data-rich-text-input]');
            if (input) input.value = '';
            wrapper.querySelector('.ql-editor')?.replaceChildren();
            delete wrapper.dataset.richTextReady;
        });
        if (typeof window.initRichTextEditors === 'function') {
            window.initRichTextEditors();
        }

        delete form.dataset.qbOpcoesInicial;
        initOpcoesList(form);
        syncPanels(form);
    }

    document.querySelectorAll('[data-qb-form]').forEach((form) => {
        form.querySelector('[data-qb-tipo-select]')?.addEventListener('change', () => syncPanels(form));
        form.querySelector('[name="rota_sistema"]')?.addEventListener('change', () => syncPanels(form));
        form.querySelector('[name="enunciado"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="legenda"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="texto_intro"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="obrigatoria"]')?.addEventListener('change', () => syncPanels(form));
        form.querySelector('[name="escala_min"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="escala_max"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="escala_min_rotulo"]')?.addEventListener('input', () => syncPanels(form));
        form.querySelector('[name="escala_max_rotulo"]')?.addEventListener('input', () => syncPanels(form));

        form.querySelector('[data-qb-add-opcao]')?.addEventListener('click', () => {
            const list = form.querySelector('[data-qb-opcoes-list]');
            if (list) {
                const input = addOpcaoRow(list);
                input?.focus();
            }
            collectOpcoes(form);
            syncPanels(form);
        });

        form.addEventListener('submit', () => collectOpcoes(form));

        form.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter' || !e.target.matches('[data-qb-opcao-input]')) return;
            if (!isTipoComOpcoes(form)) return;
            e.preventDefault();
        });

        initOpcoesList(form);
        syncPanels(form);
        form.querySelector('[name="rota_sistema"]')?.addEventListener('change', () => syncPanels(form));
    });

    document.querySelectorAll('[id^="nova-pergunta-"]').forEach((collapse) => {
        collapse.addEventListener('show.bs.collapse', () => {
            const form = collapse.querySelector('[data-qb-form-add]');
            if (!form || form.dataset.qbRepopulate === '1') return;
            form.reset();
            resetNovaPerguntaForm(form);
        });
    });

    function syncEtapaForm(form) {
        const tipo = form.querySelector('[data-etapa-tipo]')?.value
            || form.querySelector('input[name="tipo"]')?.value
            || '';
        form.querySelectorAll('[data-etapa-panel]').forEach((panel) => {
            const kinds = (panel.dataset.etapaPanel || '').split(',');
            panel.classList.toggle('d-none', !kinds.includes(tipo));
        });
        form.querySelectorAll('[data-etapa-hint-questionario]').forEach((hint) => {
            hint.classList.toggle('d-none', tipo !== 'questionario');
        });
        form.querySelectorAll('[data-etapa-hint-apresentacao]').forEach((hint) => {
            hint.classList.toggle('d-none', tipo !== 'apresentacao');
        });
        const rotaSelect = form.querySelector('[name="rota_sistema"]');
        if (rotaSelect) {
            rotaSelect.required = tipo === 'instrucao';
        }
        const conteudoLabel = form.querySelector('[data-etapa-label-conteudo]');
        if (conteudoLabel) {
            conteudoLabel.textContent = tipo === 'apresentacao'
                ? 'Introdução'
                : (tipo === 'questionario' ? 'Descrição do bloco' : 'Conteúdo / introdução');
        }
    }

    document.querySelectorAll('[data-etapa-form]').forEach((form) => {
        form.querySelector('[data-etapa-tipo]')?.addEventListener('change', () => syncEtapaForm(form));
        form.querySelector('input[name="tipo"]')?.addEventListener('change', () => syncEtapaForm(form));
        syncEtapaForm(form);
    });
})();
