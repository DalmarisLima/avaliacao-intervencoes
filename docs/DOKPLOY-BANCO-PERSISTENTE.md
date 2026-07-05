# Banco SQLite não pode sumir no Rebuild (Dokploy)

## Por que as perguntas sumiram

O **commit no Git** só atualiza o **código**. As perguntas ficam no arquivo **`database/database.sqlite`** dentro do container.

Se o Dokploy faz **Rebuild** usando só o **Dockerfile** **sem volume**, cada deploy cria um **container novo** com banco **zerado** (só o questionário padrão do seed).

Isso não tem relação com o commit — é falta de **armazenamento persistente**.

---

## Solução A — Volume no painel (mantém Dockerfile)

Na sua **Application** no Dokploy:

1. Abra **Advanced** → **Volumes** (ou **Mounts**).
2. Adicione **dois** volume mounts:

| Volume Name | Mount Path (no container) |
|-------------|---------------------------|
| `intervencoes-database` | `/var/www/html/database` |
| `intervencoes-storage` | `/var/www/html/storage` |

3. **Save** e faça **Deploy** uma vez.
4. Cadastre de novo as perguntas em `/pesquisa/questionario`.
5. Faça **Rebuild** de teste: as perguntas **devem continuar**.

> O volume `intervencoes-database` guarda o `database.sqlite`. Sem ele, qualquer rebuild apaga tudo.

---

## Solução B — Docker Compose (recomendado)

1. Em **Build Type**, mude para **Docker Compose**.
2. **Compose file:** `docker-compose.dokploy.yml`
3. Variáveis de ambiente: `APP_URL`, `EXPERIMENTO_ADMIN_EMAILS`
4. Deploy.

O compose já declara os volumes `intervencoes_database` e `intervencoes_storage`.

---

## Backup manual (opcional)

No terminal da VPS, com o container rodando:

```bash
docker ps
docker cp NOME_DO_CONTAINER:/var/www/html/database/database.sqlite ./backup-$(date +%Y%m%d).sqlite
```

### Backup só do questionário (JSON)

Preserva etapas, blocos e perguntas — **sem** respostas de participantes:

```bash
docker exec NOME_DO_CONTAINER php artisan experimento:backup-questionario
docker cp NOME_DO_CONTAINER:/var/www/html/storage/app/backups/questionario-*.json ./
```

### Limpar dados de teste (participantes)

Remove respostas, participantes e intervenções criadas no fluxo de teste. **Não apaga** o questionário cadastrado nem contas de admin:

```bash
# Simular primeiro
docker exec NOME_DO_CONTAINER php artisan experimento:limpar-participacao --dry-run

# Executar (faz backup JSON antes, por segurança)
docker exec NOME_DO_CONTAINER php artisan experimento:backup-questionario
docker exec NOME_DO_CONTAINER php artisan experimento:limpar-participacao --force
```

---

### Regenerar dados sintéticos das intervenções (preserva questionário)

Após deploy com novos datasets (`2-ano-a-flexivel.json`, `2-ano-a-dificil.json`), regenere **só** as avaliações PRÉ/PÓS — o questionário **não** é alterado:

```bash
# Simular
docker exec NOME_DO_CONTAINER php artisan intervencoes:regenerar-dados-sinteticos --turma="2º Ano A" --dry-run

# Executar
docker exec NOME_DO_CONTAINER php artisan intervencoes:regenerar-dados-sinteticos --turma="2º Ano A" --force
```

---

## Checklist deploy seguro (preserva questionário)

- [ ] Volume em `/var/www/html/database` configurado (guarda `database.sqlite` **e** `.app_key`)
- [ ] `APP_URL` no Dokploy = URL real com `https://` (ex.: `https://estudo.46.202.150.148.nip.io`)
- [ ] Perguntas cadastradas **depois** de criar o volume
- [ ] Após deploy, **recarregue a página** (F5) antes de salvar formulários (evita erro 419)
