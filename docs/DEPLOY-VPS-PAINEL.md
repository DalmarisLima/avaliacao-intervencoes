# Subir online pelo painel da VPS (passo a passo)

Seu projeto já tem `docker-compose.prod.yml` com Traefik + Let's Encrypt (Dokploy).

Domínio atual: `intervencoes.46.202.150.148.nip.io`

---

## Opção A — Dokploy (recomendado no seu caso)

### 1) Criar projeto no Dokploy

1. Entre no painel Dokploy.
2. Vá em **Projects** → **Create Project**.
3. Nome sugerido: `intervencoes-app`.

### 2) Conectar GitHub (tela **Deploy Settings** que você está vendo)

Antes de preencher os campos, o GitHub precisa estar ligado ao Dokploy. Se **Repository** fica em *Loading...* ou **Github Account** está vazio, faça isto primeiro:

1. No menu lateral do Dokploy, vá em **Settings** (ou **Git**).
2. Aba **Github** → **Create Github App** → instale no GitHub (**Install & Authorize**).
3. Marque o repositório `intervencoes-app` (ou “All repositories”).
4. Volte na sua Application → **Deploy Settings**.

Agora preencha **cada campo** da sua tela:

| Campo na tela | O que colocar |
|---------------|----------------|
| **Provider** | `Github` (já selecionado) |
| **Github Account** | Sua conta ou organização (aparece depois do passo acima) |
| **Repository** | `seu-usuario/intervencoes-app` (escolha na lista) |
| **Branch** | `main` (ou a branch que você usa) |
| **Build Path** | `/` (raiz do projeto — deixe assim) |
| **Trigger Type** | `On Push` (deploy automático a cada push) |
| **Watch Paths** | Deixe vazio (não é obrigatório) |

Clique em **Save** (se houver) antes de ir ao próximo passo.

### 3) Configurar build Docker (NÃO está na mesma tela do GitHub)

O passo 3 **não aparece** na seção *Provider*. Role a página para baixo **ou** abra a aba **Build** / **General** no menu da Application.

Procure **Build Type** e escolha **Dockerfile**. Depois:

| Campo | Valor |
|-------|--------|
| **Dockerfile Path** | `Dockerfile.prod` |
| **Docker Context Path** | `.` |

> Se não achar “Build Type”, use o menu lateral da Application: **General** → seção de build, ou **Advanced**.

### 4) Variáveis de ambiente (importante)

No Dokploy, em **Environment**, configure:

| Variável | Valor |
|----------|--------|
| `APP_URL` | `https://intervencoes.46.202.150.148.nip.io` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `sqlite` |
| `EXPERIMENTO_ADMIN_EMAILS` | `seu-email@gmail.com` (obrigatório em produção; sem isso todo login vira participante) |

> **Obrigatório:** sem volume, cada **Rebuild apaga** perguntas e participantes. Veja [DOKPLOY-BANCO-PERSISTENTE.md](./DOKPLOY-BANCO-PERSISTENTE.md).

**Volumes no Dokploy (Advanced → Volumes), se usar Dockerfile:**

| Volume Name | Mount Path |
|-------------|------------|
| `intervencoes-database` | `/var/www/html/database` |
| `intervencoes-storage` | `/var/www/html/storage` |

**Ou** use Build Type **Docker Compose** com `docker-compose.dokploy.yml` (volumes já inclusos).

### 5) Domínio e HTTPS

1. Vá em **Domains** do serviço.
2. Adicione domínio: `intervencoes.46.202.150.148.nip.io`.
3. Ative **HTTPS** com Let's Encrypt.
4. Confirme que o container está na rede `dokploy-network` (já previsto no compose).

### 6) Deploy

1. Clique em **Deploy**.
2. Aguarde build + start.
3. Abra: `https://intervencoes.46.202.150.148.nip.io/acesso`

### 7) Login admin

- E-mail: o que estiver em `EXPERIMENTO_ADMIN_EMAILS` (ex.: `dalmaris.lima@gmail.com`).

---

## Opção B — aaPanel / CyberPanel (Docker + domínio)

### 1) Instalar aaPanel (se ainda não tiver)

No painel, instale **aaPanel** com suporte Docker.

### 2) Subir projeto

1. Envie o projeto para `/www/wwwroot/intervencoes-app` (Git ou upload).
2. Crie `.env` de produção (copie de `.env.example` e ajuste `APP_URL`).

### 3) Rodar com Docker

Use o arquivo `docker-compose.vps.yml` (sem labels Dokploy):

```bash
cd /www/wwwroot/intervencoes-app
export APP_URL=https://SEU_DOMINIO
docker compose -f docker-compose.vps.yml up -d --build
```

### 4) Proxy reverso no painel

No aaPanel:
1. **Website** → adicionar site com domínio.
2. Aponte proxy para `http://127.0.0.1:80` (porta do container).
3. Ative SSL Let's Encrypt no painel.

---

## Opção C — Nginx Proxy Manager (visual, fácil)

1. Instale **Nginx Proxy Manager** via Docker no painel (ou Portainer).
2. Crie **Proxy Host**:
   - Domain: seu domínio
   - Forward: `http://IP_DA_VPS:80` (ou IP interno do container)
   - SSL: Let's Encrypt ativado
3. Garanta que o app está rodando na porta 80.

---

## Checklist pós-deploy

- [ ] Site abre com HTTPS
- [ ] `/acesso` funciona
- [ ] Login admin funciona
- [ ] Fluxo do participante (`/experimento`) funciona
- [ ] Banco persiste após restart (`docker compose ps` e volume `app_database`)
- [ ] Logs sem erro crítico (`docker compose logs -f`)

---

## Comandos úteis no painel (terminal da VPS)

```bash
cd /caminho/do/projeto
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f web
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml down
```

---

## Se der erro 502/404

1. Container não subiu → ver logs.
2. `APP_URL` diferente do domínio real.
3. Proxy apontando para porta errada.
4. Rede Docker (`dokploy-network`) não conectada ao serviço.

Se quiser, no próximo passo eu adapto o guia exatamente para o painel que você usa (Hostinger, Contabo, Hetzner, etc.).