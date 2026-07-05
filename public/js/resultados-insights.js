/**
 * Renderização de interpretação da turma e análise por intervenção (professor).
 */
(function (global) {
    function escapeHtml(str) {
        if (str == null) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function vereditoClass(eficaz) {
        if (eficaz === true) return 'is-success';
        if (eficaz === false) return 'is-danger';
        return 'is-neutral';
    }

    function badgeClass(eficaz) {
        if (eficaz === true) return 'bg-success';
        if (eficaz === false) return 'bg-danger';
        return 'bg-secondary';
    }

    function renderInterpretacaoTurmaContent(interp) {
        const build = global.InterpretacaoUI?.buildInterpretacaoTurmaHtml;
        return typeof build === 'function' ? build(interp) : '';
    }

    function renderIntervencaoAnalise(data) {
        if (!data || !data.titulo) {
            return '<p class="text-muted mb-0">Não foi possível carregar a análise desta intervenção.</p>';
        }

        const vereditoCls = vereditoClass(data.eficaz);
        const badgeCls = badgeClass(data.eficaz);

        let metricasHtml = '';
        (data.metricas || []).forEach(m => {
            const ok = m.atende ? 'is-ok' : 'is-fail';
            const icon = m.atende ? '✓' : '✗';
            metricasHtml += `
                <article class="metrica-insight-card metrica-insight-card--${ok}">
                    <header class="metrica-insight-card__header">
                        <h4 class="metrica-insight-card__title">${escapeHtml(m.nome)}</h4>
                        <span class="metrica-insight-card__status" aria-hidden="true">${icon}</span>
                    </header>
                    <p class="metrica-insight-card__valor"><strong>${escapeHtml(m.valor)}</strong></p>
                    <p class="metrica-insight-card__def small text-muted mb-2">${escapeHtml(m.o_que_e)}</p>
                    <p class="metrica-insight-card__limiar small mb-2"><span class="text-muted">Critério:</span> ${escapeHtml(m.limiar)}</p>
                    <p class="metrica-insight-card__insight mb-0">${escapeHtml(m.insight)}</p>
                </article>
            `;
        });

        let recsHtml = '';
        (data.recomendacoes || []).forEach(rec => {
            recsHtml += `<li>${escapeHtml(rec)}</li>`;
        });

        const delta = data.delta_desempenho;
        const ganhoHtml = delta != null
            ? `<span class="analise-hero__ganho ${delta > 0 ? 'text-success' : (delta < 0 ? 'text-danger' : '')}">${delta > 0 ? '+' : ''}${delta} pts</span>`
            : '';

        return `
            <div class="analise-hero analise-hero--${vereditoCls}">
                <div class="analise-hero__top">
                    <span class="badge ${badgeCls}">${escapeHtml(data.veredito)}</span>
                    <span class="analise-hero__cenario">Cenário ${escapeHtml(data.cenario || '—')}</span>
                </div>
                <p class="analise-hero__insight">${escapeHtml(data.insight_principal)}</p>
                ${data.pre_desempenho != null ? `
                    <div class="analise-hero__desempenho">
                        <span>Pré ${data.pre_desempenho}%</span>
                        <span aria-hidden="true">→</span>
                        <span>Pós ${data.pos_desempenho}%</span>
                        ${ganhoHtml}
                    </div>
                ` : ''}
            </div>
            <p class="text-secondary mb-4">${escapeHtml(data.sintese)}</p>
            <h3 class="h6 fw-semibold mb-3">O que cada indicador diz nesta turma</h3>
            <div class="metrica-insight-grid">${metricasHtml}</div>
            ${recsHtml ? `
                <div class="analise-recomendacoes mt-4">
                    <h3 class="h6 fw-semibold mb-2">Próximos passos na sala de aula</h3>
                    <ul class="analise-recomendacoes__list">${recsHtml}</ul>
                </div>
            ` : ''}
        `;
    }

    global.ResultadosInsights = {
        escapeHtml,
        renderInterpretacaoTurmaContent,
        renderIntervencaoAnalise,
    };
})(typeof window !== 'undefined' ? window : globalThis);
