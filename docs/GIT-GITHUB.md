# Subir o projeto no GitHub

## O que NÃO vai para o Git (proposital)

| Item | Motivo |
|------|--------|
| `.env` | Senhas, `APP_KEY`, e-mails admin |
| `vendor/` | Instalado com `composer install` (ou no Docker) |
| `node_modules/` | Instalado com `npm install` (ou no Docker) |
| `database/*.sqlite` | Banco local; na VPS é criado no deploy |
| `public/build/` | Gerado com `npm run build` (ou no Docker) |
| `storage/logs/` | Logs locais |

O arquivo `.env.example` **sim** vai no Git — é o modelo sem segredos.

---

## Passo 1 — Criar repositório no GitHub

1. Acesse [github.com/new](https://github.com/new)
2. **Repository name:** `intervencoes-app` (ou outro nome)
3. **Private** (recomendado para pesquisa)
4. **Não** marque “Add a README” (o projeto já tem)
5. Clique em **Create repository**

Anote a URL, por exemplo:
`https://github.com/SEU_USUARIO/intervencoes-app.git`

---

## Passo 2 — Primeiro push (no Mac)

No terminal, na pasta do projeto:

```bash
cd /Users/dalmarislima/Desktop/MESTRADO/intervencoes-app

# Se ainda não tiver commit local (já feito pelo assistente, pule git init/add/commit)
git init
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/intervencoes-app.git
git push -u origin main
```

Se o Git pedir login no GitHub, use **Personal Access Token** como senha (não a senha da conta).

Criar token: GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)** → escopo `repo`.

---

## Passo 3 — Conectar no Dokploy

1. Dokploy → **Settings** / **Git** → conectar **Github App**
2. Na Application → **Deploy Settings**:
   - **Repository:** `SEU_USUARIO/intervencoes-app`
   - **Branch:** `main`
3. Aba **Build** → **Dockerfile** → `Dockerfile.prod`
4. **Deploy**

---

## Atualizar o site depois de mudanças

```bash
git add .
git commit -m "Descreva a alteração"
git push
```

Com **Trigger Type: On Push**, o Dokploy faz deploy automático.

---

## Conferir antes do push

```bash
git status
git check-ignore -v .env database/database.sqlite
```

`.env` e `database.sqlite` devem aparecer como ignorados.