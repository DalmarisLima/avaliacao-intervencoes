/**
 * UI da interpretação da turma: render + popovers de ajuda.
 */
(function (global) {
    const AJUDA = global.INTERPRETACAO_AJUDA || {};

    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function helpBtn(chave) {
        const texto = AJUDA[chave];
        if (!texto) return '';
        return `<button type="button" class="btn-interpretacao-ajuda" tabindex="0" data-bs-toggle="popover" data-bs-trigger="click" data-bs-placement="top" data-bs-custom-class="popover-interpretacao" data-bs-content="${escapeHtml(texto)}" aria-label="Explicação">?</button>`;
    }

    function dimAjudaKey(titulo) {
        const t = String(titulo || '').toLowerCase();
        if (t.includes('aprendizagem')) return 'dim_aprendizagem';
        if (t.includes('interven')) return 'dim_intervencoes';
        if (t.includes('processo')) return 'dim_processo';
        if (t.includes('meta')) return 'dim_meta';
        return 'dim_generico';
    }

    function vereditoTagClass(eficaz) {
        if (eficaz === true) return 'ok';
        if (eficaz === false) return 'fail';
        return 'neutral';
    }

    function buildInterpretacaoTurmaHtml(interp) {
        if (!interp || (!interp.turma && !interp.classificacao_rotulo && interp.pre_desempenho === undefined)) {
            return '';
        }

        const sintese = (interp.sintese || '').replace(/\*\*/g, '');
        const insight = (interp.insight_principal || '').trim();
        const textoResumo = escapeHtml(sintese !== '' ? sintese : insight);
        const vTag = vereditoTagClass(interp.eficaz);
        const delta = interp.delta_desempenho ?? 0;
        const ganhoClass = delta > 0 ? 'is-up' : (delta < 0 ? 'is-down' : '');
        const ganhoSinal = delta > 0 ? '+' : '';

        const indiceHtml = interp.indice_eficacia !== undefined && interp.eficaz !== null
            ? `<span class="interpretacao-indice">Índice ${interp.indice_eficacia}/100 ${helpBtn('indice')}</span>`
            : '';

        let kpisHtml = '';
        if (interp.delta_desempenho !== undefined) {
            kpisHtml = `
                <div class="interpretacao-kpis">
                    <div class="interpretacao-kpi">
                        <span class="interpretacao-kpi__label">Pré ${helpBtn('pre')}</span>
                        <span class="interpretacao-kpi__value interpretacao-kpi__value--pre">${interp.pre_desempenho ?? '—'}%</span>
                    </div>
                    <div class="interpretacao-kpi">
                        <span class="interpretacao-kpi__label">Pós ${helpBtn('pos')}</span>
                        <span class="interpretacao-kpi__value interpretacao-kpi__value--pos">${interp.pos_desempenho ?? '—'}%</span>
                    </div>
                    <div class="interpretacao-kpi">
                        <span class="interpretacao-kpi__label">Ganho ${helpBtn('ganho')}</span>
                        <span class="interpretacao-kpi__value ${ganhoClass}">${ganhoSinal}${delta} pts</span>
                    </div>
                </div>
            `;
        }

        let dimensoesHtml = '';
        const camadas = interp.camadas || [];
        if (camadas.length > 0) {
            let cards = '';
            camadas.forEach(camada => {
                cards += `
                    <article class="interpretacao-dim interpretacao-dim--${escapeHtml(camada.status || 'neutro')}">
                        <header class="interpretacao-dim__head">
                            <h4 class="interpretacao-dim__title">${escapeHtml(camada.titulo)}</h4>
                            ${helpBtn(dimAjudaKey(camada.titulo))}
                        </header>
                        <p class="interpretacao-dim__texto">${escapeHtml(camada.texto)}</p>
                    </article>
                `;
            });
            dimensoesHtml = `
                <section class="interpretacao-bloco">
                    <div class="interpretacao-bloco__head">
                        <h3 class="interpretacao-bloco__title">Por dimensão</h3>
                        ${helpBtn('dimensoes')}
                    </div>
                    <div class="interpretacao-dimensoes">${cards}</div>
                </section>
            `;
        }

        let intervencoesHtml = '';
        const lista = interp.intervencoes || [];
        if (lista.length > 0) {
            let items = '';
            lista.forEach(item => {
                const itemTag = vereditoTagClass(item.eficaz);
                items += `
                    <div class="eficacia-intervencao-item">
                        <div class="eficacia-intervencao-item__header">
                            <strong>${escapeHtml(item.titulo)}</strong>
                            <span class="interpretacao-veredito interpretacao-veredito--${itemTag}">${escapeHtml(item.classificacao_rotulo)}</span>
                        </div>
                    </div>
                `;
            });
            intervencoesHtml = `
                <details class="interpretacao-bloco interpretacao-bloco--collapsible">
                    <summary class="interpretacao-bloco__summary">
                        Intervenções desta turma (${lista.length})
                        ${helpBtn('intervencoes_lista')}
                    </summary>
                    <div class="eficacia-interpretacao__lista">${items}</div>
                </details>
            `;
        }

        const resumoTextoHtml = textoResumo
            ? `<p class="interpretacao-resumo-texto">${textoResumo}</p>`
            : '';

        return `
            <div class="interpretacao-layout">
                <section class="interpretacao-bloco interpretacao-bloco--resumo">
                    <div class="interpretacao-bloco__head">
                        <h3 class="interpretacao-bloco__title">Resumo da turma</h3>
                        ${helpBtn('conclusao')}
                    </div>
                    <div class="interpretacao-conclusao">
                        <span class="interpretacao-veredito interpretacao-veredito--${vTag}">${escapeHtml(interp.classificacao_rotulo)}</span>
                        ${indiceHtml}
                    </div>
                    ${resumoTextoHtml}
                    ${kpisHtml}
                </section>
                ${dimensoesHtml}
                ${intervencoesHtml}
            </div>
        `;
    }

    function initPopovers(root) {
        if (typeof bootstrap === 'undefined') return;
        const scope = root || document;
        scope.querySelectorAll('.btn-interpretacao-ajuda[data-bs-toggle="popover"]').forEach(el => {
            const existing = bootstrap.Popover.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Popover(el, {
                container: 'body',
                sanitize: false,
                trigger: 'click',
            });
            if (!el.dataset.popoverBound) {
                el.dataset.popoverBound = '1';
                el.addEventListener('click', e => e.stopPropagation());
            }
        });
    }

    global.InterpretacaoUI = {
        buildInterpretacaoTurmaHtml,
        initPopovers,
    };
})(typeof window !== 'undefined' ? window : globalThis);
