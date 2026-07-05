# Interface (Fase 4 + redesign analítico)

## Pilha

**Bootstrap 5 (CDN) + CSS customizado** em `public/css/`, fonte **Inter** (Google Fonts).

| Arquivo | Função |
|---------|--------|
| `tokens.css` | Cores, tipografia, tokens de gráfico (PRÉ/PÓS) |
| `main.css`, `forms.css`, `sidebar.css` | Layout geral |
| `components.css` | Wizard, badges, empty state |
| `charts.css` | Painéis analíticos, KPIs, gráfico CSS comparativo, containers Chart.js |

`charts.css` é carregado na página de **Resultados** (`@push('styles')`).

## Princípios do redesign

1. **Neutro e legível** — fundo `#f8fafc`, texto `#0f172a`, bordas suaves.
2. **PRÉ vs PÓS consistentes** — cinza-ardósia (`--chart-pre`) e verde-petróleo (`--chart-pos`), em CSS e Chart.js.
3. **Hierarquia clara** — KPIs no topo → critérios por cenário + comparação por turma → filtro → detalhe da turma.
4. **Menos ruído** — sem vermelho/verde Bootstrap nos gráficos; cards de comparação com gradiente só quando há ganho/perda.

## Componentes Blade

| Componente | Uso |
|------------|-----|
| `<x-wizard-steps :current="1" />` | Fluxo Cadastro → Cenário → Resultados |
| `<x-empty-state />` | Listas/resultados vazios |
| `<x-cenario-badge />` | Rótulo de cenário |
| `<x-status-badge />` | Status temporal |

A página **Resultados** começa no filtro de turma; não há overview global (KPIs, limiares ou barras por turma no topo).

## Gráficos

### Chart.js (`charts-theme.js`)

Tema global aplicado aos gráficos de **progressão por turma** e **modal por intervenção**:

- Tipografia Inter, grid discreto, barras arredondadas
- Mesmas cores dos tokens CSS
- Legenda alinhada à direita

## Locale e mobile

- Padrão `pt_BR` em `config/app.php`
- Botão **Menu** abre sidebar em telas &lt; 992px

## Página de resultados (detalhe)

- **Filtros** — painel `analytics-filters`
- **Abas** — `analytics-tabs` + tabelas `table-clean` em painel único
- **Modais** — `analytics-modal` com KPIs (`modal-kpi-grid`) e cards de métrica (`metric-analysis-grid`)
- **Botões** — classe `btn-analytics` (substitui `btn-dark` nas tabelas)
- Estilos concentrados em `charts.css` (sem bloco `<style>` inline em `resultados.blade.php`)
