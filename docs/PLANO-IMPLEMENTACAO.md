# Plano de implementação por fases

Projeto: **intervencoes-app** — experimento de avaliação de intervenções pedagógicas.

**Legenda de status:** `[ ]` pendente · `[~]` em andamento · `[x]` concluído

---

## Visão geral

| Fase | Foco | Esforço estimado | Risco |
|------|------|------------------|-------|
| **1** | Correções críticas e base estrutural | 1–2 dias | Baixo |
| **2** | Domínio, dados e regras de negócio | 3–5 dias | Médio |
| **3** | Performance, API e banco | 2–3 dias | Médio |
| **4** | UI/UX e acessibilidade | 2–4 dias | Baixo |
| **5** | Segurança para produção | 1–2 dias | Médio |
| **6** | Qualidade, deploy e documentação contínua | contínuo | Baixo |

---

## Fase 1 — Correções críticas e base estrutural

**Objetivo:** eliminar bugs imediatos, reduzir duplicação e estabelecer pastas/serviços padrão.

| # | Tarefa | Status |
|---|--------|--------|
| 1.1 | Corrigir model `Intervencao` (relação `arquivos`, `avaliacoes`) | [x] |
| 1.2 | Criar `App\Services\CenarioService` e usar nos 3 controllers | [x] |
| 1.3 | Criar `IntervencaoPolicy` + `authorize()` nos fluxos com `{intervencao}` | [x] |
| 1.4 | `throttle` no POST `/acesso` | [x] |
| 1.5 | Registrar rota `/turmas` (`TurmaController`) | [x] |
| 1.6 | Remover `resultados.blade.php.bak` | [x] |
| 1.7 | README em português com setup do experimento | [x] |
| 1.8 | Índices básicos em `avaliacoes` e `intervencoes` | [x] |

**Critério de conclusão:** `php artisan test` passa; fluxo login → intervenção → cenário → resultados sem erro 500.

---

## Fase 2 — Domínio, dados e regras de negócio

**Objetivo:** dados sintéticos e regras testáveis, fora dos controllers.

| # | Tarefa | Status |
|---|--------|--------|
| 2.1 | Extrair datasets para `database/data/turmas/*.json` ou `app/Data/SyntheticDatasets/` | [x] |
| 2.2 | Criar `SyntheticEvaluationGenerator` (geração PRÉ/PÓS) | [x] |
| 2.3 | Enum `Cenario` (`flexivel`, `moderado`, `dificil`) | [x] |
| 2.4 | Form Requests: `StoreIntervencaoRequest`, `SalvarCenarioRequest`, `StoreAvaliacaoRequest` | [x] |
| 2.5 | Flag `dados_gerados_at` ou unique `(intervencao_id, aluno_numero, tipo)` anti-duplicação | [x] |
| 2.6 | Remover alunos duplicados nos datasets (ex.: Ana Beatriz em 1º Ano A) | [x] |
| 2.7 | Alinhar ou descontinuar seeder `DadosTeste` (turmas `1A` vs `1º Ano A`) | [x] |
| 2.8 | Documentar regras de eficácia em `docs/regras-negocio.md` | [x] |
| 2.9 | Unificar fluxo manual (`AvaliacaoController`) vs automático (sintético) | [x] |

**Critério de conclusão:** testes unitários cobrem normalização, classificação e eficácia; controller de intervenção &lt; 150 linhas de lógica HTTP.

---

## Fase 3 — Performance, API e banco de dados

**Objetivo:** consultas escaláveis e schema estável.

| # | Tarefa | Status |
|---|--------|--------|
| 3.1 | Consolidar migrations duplicadas (`avaliacoes`, `turma` vazia) em baseline limpo | [x] |
| 3.2 | Tabelas `turmas` e `alunos` com FK | [x] |
| 3.3 | Eager loading em listagens (`withCount('avaliacoes')`) | [x] |
| 3.4 | Cache de agregações por turma/usuário com invalidação ao salvar cenário | [x] |
| 3.5 | Mover rotas `/api/turma/*` para `routes/api.php` + middleware `auth:sanctum` ou sessão API | [x] |
| 3.6 | Job em fila para geração sintética (se dataset crescer) | [x] |
| 3.7 | Avaliar PostgreSQL/MySQL em produção (substituir SQLite concorrente) | [x] |
| 3.8 | `config:cache`, `route:cache`, `view:cache` no deploy | [x] |

**Critério de conclusão:** página de resultados &lt; 300 ms em dataset de referência local; migrations rodam em banco vazio sem workarounds.

---

## Fase 4 — Design das telas

**Objetivo:** interface coerente, em português, orientada ao fluxo do experimento.

| # | Tarefa | Status |
|---|--------|--------|
| 4.1 | Escolher uma pilha: Vite+Tailwind **ou** Bootstrap estático (remover a outra) | [x] |
| 4.2 | Tokens de design (cores, tipografia) em arquivo único | [x] |
| 4.3 | Wizard: Nova intervenção → Cenário → Resultados | [x] |
| 4.4 | Componentes Blade: métricas, badges de cenário, empty states | [x] |
| 4.5 | Dashboard de resultados: gráficos + legenda dos limiares | [x] |
| 4.6 | `APP_LOCALE=pt_BR` e traduções de validação | [x] |
| 4.7 | Responsividade (sidebar, tabelas mobile) | [x] |
| 4.8 | Exibir status da intervenção (Novo / Em andamento / Finalizado) na listagem | [x] |

**Critério de conclusão:** revisão com 2 participantes do estudo sem ambiguidade no fluxo.

---

## Fase 5 — Segurança para produção

**Objetivo:** adequar auth e superfície de ataque para ambiente exposto.

| # | Tarefa | Status |
|---|--------|--------|
| 5.1 | Auth: senha, magic link ou lista de e-mails do experimento | [ ] |
| 5.2 | Restringir `trustProxies` a IPs do proxy (Traefik) | [ ] |
| 5.3 | Validação MIME/extensão em uploads | [ ] |
| 5.4 | Download de arquivos via controller autorizado (opcional) | [ ] |
| 5.5 | Headers de segurança (CSP, X-Frame-Options) | [ ] |
| 5.6 | 404 uniforme para intervenções de outros usuários | [ ] |
| 5.7 | Backup automatizado do SQLite (volume Docker) | [ ] |
| 5.8 | Secrets via env no deploy (sem `sed` fixo no Dockerfile) | [ ] |

**Critério de conclusão:** checklist OWASP básico para app de pesquisa aprovado.

---

## Fase 6 — Qualidade, deploy e documentação contínua

**Objetivo:** manutenção sustentável do experimento.

| # | Tarefa | Status |
|---|--------|--------|
| 6.1 | Testes Feature: auth, CRUD intervenção, cenário, resultados, policy | [ ] |
| 6.2 | Testes Unit: `CenarioService`, gerador sintético | [ ] |
| 6.3 | CI (GitHub Actions): pint, test, build frontend | [ ] |
| 6.4 | `docs/arquitetura.md` com ER e fluxo de rotas | [ ] |
| 6.5 | ADRs (auth por e-mail, SQLite, dados sintéticos) | [ ] |
| 6.6 | `docs/troubleshooting.md` | [ ] |
| 6.7 | Monitoramento de logs (`pail` / Sentry opcional) | [ ] |

**Critério de conclusão:** PR não mergeia sem testes verdes.

---

## Ordem de execução recomendada

```
Fase 1 → Fase 2 → Fase 3 → Fase 4
              ↘ Fase 5 (paralelo após Fase 1, antes de abrir produção)
              ↘ Fase 6 (contínuo desde Fase 2)
```

---

## Riscos e mitigação

| Risco | Mitigação |
|-------|-----------|
| `migrate:fresh` apaga dados do experimento | Backup diário do SQLite; nunca fresh em produção |
| Consolidar migrations quebra ambientes antigos | Squash só em branch dedicada + doc de migração |
| Mudar auth bloqueia participantes | Janela de transição com e-mails já cadastrados |
| Datasets JSON dessincronizam com paper | Versionar JSON com tag git por coleta |

---

## Histórico

| Data | Alteração |
|------|-----------|
| 2026-05-20 | Plano criado; Fase 1 concluída |
| 2026-05-20 | Fase 2 concluída (datasets JSON, generator, enum, requests, regras) |
| 2026-05-21 | Fase 3 concluída (turmas/alunos, cache, API, job, deploy docs) |
| 2026-05-21 | Fase 4 concluída (UI Bootstrap, wizard, componentes, pt_BR, responsivo) |
