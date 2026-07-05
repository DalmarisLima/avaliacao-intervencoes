document.addEventListener('DOMContentLoaded', function () {
    const appShell = document.getElementById('appShell');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    const closeSidebar = () => appShell?.classList.remove('is-sidebar-open');
    const openSidebar = () => appShell?.classList.add('is-sidebar-open');

    sidebarToggle?.addEventListener('click', () => {
        if (appShell?.classList.contains('is-sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    sidebarBackdrop?.addEventListener('click', closeSidebar);

    // Inicializa tooltips em todas as páginas (padroniza comportamento igual à tela 'resultados')
    try {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } catch (e) {
        // não bloqueia execução se bootstrap não estiver pronto
    }

    // Ajusta valores das métricas quando o cenário for alterado na página de avaliação
    const cenarioRadios = document.querySelectorAll('input[name="cenario"]');
    if (cenarioRadios.length) {
        const inicioInput = document.querySelector('input[name="temporalidade_inicio"]');
        const fimInput = document.querySelector('input[name="temporalidade_fim"]');
        const adesaoSimInput = document.querySelector('input[name="adesao"][value="sim"]');

        const enforceTemporalidadeOrder = () => {
            if (!inicioInput || !fimInput || !adesaoSimInput || !adesaoSimInput.checked) return;
            const inicio = Number(inicioInput.value || 0);
            let fim = Number(fimInput.value || 0);
            if (fim <= inicio) {
                fim = Math.min(240, inicio + 1);
                fimInput.value = String(fim);
                fimInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const setMetric = (fieldName, value) => {
            // Pode ser um input range (aderencia, temporalidade, desempenho)
            // ou um conjunto de radios (adesao) — detectamos pelo primeiro input encontrado.
            const inputs = document.querySelectorAll(`input[name="${fieldName}"]`);
            if (!inputs || inputs.length === 0) return;

            const first = inputs[0];
            if (first.type === 'range') {
                first.value = value;
                // atualiza o span visível ao lado do slider
                const row = first.closest('.slider-row');
                if (row) {
                    const valueSpan = row.querySelector('.slider-value');
                    if (valueSpan) {
                        const sufixo = fieldName.includes('temporalidade') ? ' min' : '%';
                        valueSpan.textContent = value + sufixo;
                    }
                }
                // dispare evento input para que handlers existentes reajam
                first.dispatchEvent(new Event('input', { bubbles: true }));
            } else if (first.type === 'radio') {
                // marcar o radio cujo value coincide
                inputs.forEach(r => {
                    if (r.value === String(value)) {
                        r.checked = true;
                        r.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            } else {
                // campo único (fallback)
                first.value = value;
                first.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const applyCenario = (valor) => {
            switch ((valor || '').toLowerCase()) {
                case 'flexivel':
                case 'flexível':
                    // Cenário flexível: adesão considerada 'sim'
                    setMetric('adesao', 'sim');
                    setMetric('aderencia', 25);
                    setMetric('temporalidade_inicio', 20);
                    setMetric('temporalidade_fim', 60);
                    setMetric('desempenho', 25);
                    break;
                case 'moderado':
                    // Cenário moderado: adesão parcial com métricas intermediárias
                    setMetric('adesao', 'sim');
                    setMetric('aderencia', 60);
                    setMetric('temporalidade_inicio', 15);
                    setMetric('temporalidade_fim', 45);
                    setMetric('desempenho', 60);
                    break;
                case 'rígido':
                case 'rigido':
                case 'modelo':
                case 'modelado':
                    // Aliases legados mantidos para formulários antigos
                    applyCenario('moderado');
                    break;
                case 'leve':
                    // Alias legado mantido para formulários antigos
                    applyCenario('flexivel');
                    break;
                case 'personalizado':
                    // Cenário personalizado: adesão = não, métricas 0%
                    setMetric('adesao', 'nao');
                    setMetric('aderencia', 0);
                    setMetric('temporalidade_inicio', 0);
                    setMetric('temporalidade_fim', 0);
                    setMetric('desempenho', 0);
                    break;
                default:
                    break;
            }

            enforceTemporalidadeOrder();
        };

        cenarioRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                applyCenario(this.value);
            });
            // Garante atualização mesmo ao clicar novamente no mesmo cenário.
            radio.addEventListener('click', function () {
                applyCenario(this.value);
            });
        });

        if (inicioInput) inicioInput.addEventListener('input', enforceTemporalidadeOrder);
        if (fimInput) fimInput.addEventListener('input', enforceTemporalidadeOrder);
        document.querySelectorAll('input[name="adesao"]').forEach(radio => {
            radio.addEventListener('change', enforceTemporalidadeOrder);
        });

        // aplica estado inicial se já tiver um selecionado
        const checked = document.querySelector('input[name="cenario"]:checked');
        if (checked) applyCenario(checked.value);
        enforceTemporalidadeOrder();
    }

    // Note: adesão agora é um radio (sim/nao) — não há lógica de snap aqui.

    const botaoAdicionar = document.getElementById('addFile');
    const listaUploads = document.getElementById('uploadList');

    if (!botaoAdicionar || !listaUploads) {
        return;
    }

    botaoAdicionar.addEventListener('click', function () {

        const novoCampo = document.createElement('div');
        novoCampo.className = 'upload-item';

        novoCampo.innerHTML = `
            <span>Arquivo:</span>
            <input type="file" name="arquivos[]" class="form-control">
        `;

        listaUploads.appendChild(novoCampo);
    });

});

// --- Range inputs: atualiza o span que mostra o valor ---
// Usa uma busca resiliente pelo span que mostra o valor: procura
// dentro do mesmo .form-group por ".range-value span" e, se não
// encontrar, usa nextElementSibling como fallback.
document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll('.form-range');

    sliders.forEach(slider => {
        const update = () => {
            const group = slider.closest('.form-group');
            // 1) Preferência: procurar por span de exibição em ambos os padrões
            // usados no projeto: .range-value span (antigo) e .slider-value (novo).
            let valueSpan = null;
            if (group) {
                valueSpan = group.querySelector('.range-value span');
                if (!valueSpan) {
                    valueSpan = group.querySelector('.slider-value');
                }
            }

            // 2) Fallback: se o próximo irmão é um container (.range-value), buscar o span dentro dele
            if (!valueSpan && slider.nextElementSibling) {
                const maybeContainer = slider.nextElementSibling;
                if (maybeContainer.matches && maybeContainer.matches('.range-value')) {
                    valueSpan = maybeContainer.querySelector('span');
                } else if (maybeContainer.matches && maybeContainer.matches('.slider-value')) {
                    valueSpan = maybeContainer;
                } else if (maybeContainer.querySelector) {
                    // próximo irmão pode ser um wrapper qualquer com um span
                    valueSpan = maybeContainer.querySelector('span') || maybeContainer.querySelector('.slider-value');
                }
            }

            // 3) Último fallback: se ainda não achou, tentar nextElementSibling direto (pode ser o próprio span)
            if (!valueSpan && slider.nextElementSibling && slider.nextElementSibling.tagName === 'SPAN') {
                valueSpan = slider.nextElementSibling;
            }

            // 4) Atualiza o texto, se foi encontrado
            if (valueSpan) {
                const sufixo = slider.name.includes('temporalidade') ? ' min' : '%';
                valueSpan.textContent = slider.value + sufixo;
            }
        };

        slider.addEventListener('input', update);
        // inicializa o valor ao carregar a página
        update();
    });
});


document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('modalIntervencao');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const row = trigger && trigger.closest ? trigger.closest('tr') : null;
        if (!row) return;

        document.getElementById('modalTitulo').textContent =
            row.getAttribute('data-titulo');

        document.getElementById('modalDescricao').textContent =
            row.getAttribute('data-descricao');

        document.getElementById('modalTipo').textContent =
            row.getAttribute('data-tipo');

        document.getElementById('modalInicio').textContent =
            row.getAttribute('data-inicio');

        document.getElementById('modalFim').textContent =
            row.getAttribute('data-fim');

        const link = row.getAttribute('data-link');
        const linkEl = document.getElementById('modalLink');

        if (link) {
            linkEl.href = link;
            linkEl.textContent = link;
        } else {
            linkEl.textContent = '—';
            linkEl.removeAttribute('href');
        }

        // Preenche o link da turma dentro do modal da intervenção
        const turma = row.getAttribute('data-turma');
        const turmaLink = document.getElementById('modalTurmaLink');
        try {
            // Garantir que o modal de turma não seja exibido automaticamente
            const turmaModalEl = document.getElementById('modalTurma');
            if (turmaModalEl) {
                const inst = bootstrap.Modal.getInstance(turmaModalEl);
                if (inst) inst.hide();
            }
        } catch (e) {
            // não bloquear caso bootstrap não esteja presente
        }

        if (turma) {
            turmaLink.textContent = turma;
            turmaLink.setAttribute('data-turma', turma);
            // evitar navegação acidental quando clicado programaticamente
            turmaLink.href = 'javascript:void(0);';
        } else {
            turmaLink.textContent = '—';
            turmaLink.removeAttribute('data-turma');
            turmaLink.removeAttribute('href');
        }
    });

});


// Abre o modal da turma e popula uma lista fictícia de alunos
document.addEventListener('DOMContentLoaded', function () {
    function populateTurmaModal(turmaName) {
        const titulo = document.getElementById('modalTurmaTitulo');
        const tbody = document.getElementById('modalTurmaAlunosBody');

        titulo.textContent = turmaName || 'Turma';
        tbody.innerHTML = '';

        if (!turmaName) return;

        // Busca dados reais do backend
        fetch(`/api/turma/${encodeURIComponent(turmaName)}/alunos`)
            .then(res => res.json())
            .then(data => {
                const alunos = data.alunos || [];

                if (!alunos.length) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="6">Nenhum aluno encontrado nesta turma.</td>`;
                    tbody.appendChild(tr);
                    return;
                }

                alunos.forEach(aluno => {
                    // Calcular adesão geral do aluno (se aderiu a pelo menos uma intervenção no pós)
                    let posCount = 0;
                    let posAderenciaSum = 0;
                    let posTemporalidadeInicioSum = 0;
                    let posTemporalidadeFimSum = 0;
                    let posDesempenhoSum = 0;

                    let preCount = 0;
                    let preAderenciaSum = 0;
                    let preTemporalidadeInicioSum = 0;
                    let preTemporalidadeFimSum = 0;
                    let preDesempenhoSum = 0;

                    aluno.intervencoes.forEach(iv => {
                        if (iv.pre) {
                            preCount++;
                            preAderenciaSum += Number(iv.pre.aderencia || 0);
                            preTemporalidadeInicioSum += Number(iv.pre.temporalidade_inicio || iv.pre.temporalidade || 0);
                            preTemporalidadeFimSum += Number(iv.pre.temporalidade_fim || iv.pre.temporalidade || 0);
                            preDesempenhoSum += Number(iv.pre.desempenho || 0);
                        }

                        if (iv.pos && (iv.pos.adesao === 'Sim' || iv.pos.adesao === '1' || iv.pos.adesao === 1)) {
                            posCount++;
                            posAderenciaSum += Number(iv.pos.aderencia || 0);
                            posTemporalidadeInicioSum += Number(iv.pos.temporalidade_inicio || iv.pos.temporalidade || 0);
                            posTemporalidadeFimSum += Number(iv.pos.temporalidade_fim || iv.pos.temporalidade || 0);
                            posDesempenhoSum += Number(iv.pos.desempenho || 0);
                        }
                    });

                    const adesao = posCount > 0 ? 'Sim' : 'Não';

                    let aderencia = '--';
                    let temporalidadeInicio = '--';
                    let temporalidadeFim = '--';
                    let desempenho = '--';

                    if (posCount > 0) {
                        aderencia = Math.round(posAderenciaSum / posCount) + '%';
                        temporalidadeInicio = Math.round(posTemporalidadeInicioSum / posCount) + ' min';
                        temporalidadeFim = Math.round(posTemporalidadeFimSum / posCount) + ' min';
                        desempenho = Math.round(posDesempenhoSum / posCount) + '%';
                    } else {
                        // Quando o aluno NÃO aderiu a nenhuma intervenção, mostrar N/A
                        aderencia = 'N/A';
                        temporalidadeInicio = 'N/A';
                        temporalidadeFim = 'N/A';
                        desempenho = 'N/A';
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${aluno.aluno_nome}</td>
                        <td>${adesao}</td>
                        <td>${aderencia}</td>
                        <td>${temporalidadeInicio}</td>
                        <td>${temporalidadeFim}</td>
                        <td>${desempenho}</td>
                    `;

                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="6">Erro ao carregar dados: ${err.message}</td>`;
                tbody.appendChild(tr);
            });
    }

    // Quando o link dentro do modal de intervenção for clicado
    const modalTurmaLink = document.getElementById('modalTurmaLink');
    if (modalTurmaLink) {
        modalTurmaLink.addEventListener('click', function (ev) {
            ev.preventDefault();
            const turmaName = this.getAttribute('data-turma');
            populateTurmaModal(turmaName);
            const turmaModalEl = document.getElementById('modalTurma');
            const turmaModal = bootstrap.Modal.getOrCreateInstance(turmaModalEl);
            turmaModal.show();
        });
    }

    // Delegation: links de turma na tabela (impede que o clique abra o modal da linha)
    document.querySelectorAll('.turma-link').forEach(function (link) {
        link.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation(); // evita que a linha abra o modalIntervencao
            const turmaName = this.getAttribute('data-turma');
            populateTurmaModal(turmaName);
            const turmaModalEl = document.getElementById('modalTurma');
            const turmaModal = bootstrap.Modal.getOrCreateInstance(turmaModalEl);
            turmaModal.show();
        });
    });

    // Botões "Ver intervenções" na tela de turmas
    function formatMetricPair(preValue, posValue, unit = '%') {
        const pre = Number.isFinite(Number(preValue)) ? `${Math.round(Number(preValue))}${unit}` : '—';
        const pos = Number.isFinite(Number(posValue)) ? `${Math.round(Number(posValue))}${unit}` : '—';
        return `${pre} → ${pos}`;
    }

    function openIntervencoesTurmaModal(turmaName) {
        const titulo = document.getElementById('modalIntervencoesTurmaTitulo');
        const tbody = document.getElementById('modalIntervencoesTurmaBody');
        const modalEl = document.getElementById('modalIntervencoesTurma');

        if (!titulo || !tbody || !modalEl) return;

        titulo.textContent = `Intervenções da turma ${turmaName || ''}`.trim();
        tbody.innerHTML = '<tr><td colspan="6">Carregando...</td></tr>';

        fetch(`/api/turma/${encodeURIComponent(turmaName)}/intervencoes`)
            .then(res => res.json())
            .then(data => {
                const intervencoes = data.intervencoes || [];
                tbody.innerHTML = '';

                if (!intervencoes.length) {
                    tbody.innerHTML = '<tr><td colspan="6">Nenhuma intervenção encontrada para esta turma.</td></tr>';
                    return;
                }

                intervencoes.forEach((it) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${it.titulo || '—'}</td>
                        <td>${it.pos?.adesao || '—'}</td>
                        <td>${formatMetricPair(it.pre?.aderencia, it.pos?.aderencia)}</td>
                        <td>${formatMetricPair(it.pre?.temporalidade_inicio, it.pos?.temporalidade_inicio, ' min')}</td>
                        <td>${formatMetricPair(it.pre?.temporalidade_fim, it.pos?.temporalidade_fim, ' min')}</td>
                        <td>${formatMetricPair(it.pre?.desempenho, it.pos?.desempenho)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="6">Erro ao carregar intervenções: ${err.message}</td></tr>`;
            });

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    document.querySelectorAll('.show-intervencoes-btn').forEach(function (btn) {
        btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            const turmaName = this.getAttribute('data-turma');
            if (!turmaName) return;
            openIntervencoesTurmaModal(turmaName);
        });
    });

});

// Busca funcional na tabela da página de intervenções
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('intervencoesSearchInput');
    const searchBtn = document.getElementById('intervencoesSearchBtn');
    const tableBody = document.querySelector('.intervencoes-table tbody');
    const emptyRow = document.getElementById('intervencoesSearchEmptyRow');

    if (!searchInput || !searchBtn || !tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('tr')).filter((row) =>
        row.hasAttribute('data-id')
    );

    const normalize = (value) =>
        (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

    const applySearch = () => {
        const term = normalize(searchInput.value);
        let visibleCount = 0;

        rows.forEach((row) => {
            const haystack = normalize([
                row.getAttribute('data-titulo'),
                row.getAttribute('data-descricao'),
                row.getAttribute('data-tipo'),
                row.getAttribute('data-turma'),
                row.getAttribute('data-status'),
                row.getAttribute('data-inicio'),
                row.getAttribute('data-fim'),
            ].join(' '));

            const match = term === '' || haystack.includes(term);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        if (emptyRow) {
            emptyRow.classList.toggle('d-none', visibleCount > 0);
        }
    };

    searchBtn.addEventListener('click', applySearch);
    searchInput.addEventListener('input', applySearch);
    searchInput.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            applySearch();
        }
    });
});

