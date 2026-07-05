# Design system — Calm UI

Padrão visual da plataforma, inspirado em interfaces como **Notion** e **Linear**: hierarquia por espaçamento e tom de cinza, não por contraste agressivo.

## Princípios

1. **Superfícies planas** — fundo off-white, cartões brancos com borda `1px`, sem sombras empilhadas.
2. **Cor de ação suave** — azul-cinza (`--color-primary`), nunca preto puro em botões.
3. **Tipografia leve** — corpo `400`, títulos no máximo `600`, tamanhos moderados.
4. **Ritmo consistente** — escala de espaçamento em `tokens.css`; conteúdo em `.page-shell` (max-width 1120px).
5. **Componentes reutilizáveis** — `x-page-header`, `.surface-card`, `.stat-card`, sidebar com navegação em pílulas.

## Tokens principais

| Token | Uso |
|--------|-----|
| `--color-bg` | Fundo da aplicação |
| `--color-surface` | Cartões e painéis |
| `--color-border` | Divisores e contornos |
| `--color-primary` | Botões primários, links de ação |
| `--color-text` / `--color-text-muted` | Texto principal e secundário |

## Arquivos CSS

| Arquivo | Função |
|---------|--------|
| `tokens.css` | Variáveis de design |
| `main.css` | Reset e base tipográfica |
| `ui.css` | Componentes de layout, overrides Bootstrap |
| `sidebar.css` | Menu lateral |
| `forms.css` / `components.css` / `charts.css` | Domínios específicos |

## Uso em Blade

```blade
<div class="page-shell">
    <x-page-header title="Título" subtitle="Descrição opcional" />
    <div class="surface-card">...</div>
</div>
```

Botões: preferir `btn-primary` (ação principal) e `btn-outline-secondary` (secundária). A classe `btn-dark` é mapeada ao primário suave por compatibilidade.

## Questionário do experimento

- O **fluxo** (`fluxo_etapas`) define a ordem que o participante percorre.
- Etapa do tipo **Bloco de perguntas** cria automaticamente um `questionario_bloco` com o mesmo título e introdução — não cadastre seção à parte.
- As perguntas são adicionadas dentro de cada etapa de questionário na tela de configuração.
