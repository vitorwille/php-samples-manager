# MicroLIMS - Gestor de amostras laboratoriais

<img style="display: block; margin: 10px auto" src="frontend/public/img/microlims-logo-white.png" alt="MicroLIMS - logo" width="120" height="120">

Gestor simples de amostras laboratoriais, criado para fins acadêmicos, trabalhando conceitos de programação server-side e desenvolvimento web.

## Stack

- **Backend:** PHP 8.5 + Slim 4, MySQL
- **Frontend:** NextJS 16 + Tailwind CSS
- **Testes:** PHPUnit + Infection
- **Containerização:** Docker

---

## Executar o projeto

```bash
cp .env.example .env
# preencha as variáveis de ambiente
docker compose up
```

- Frontend: `http://localhost:3000`
- API Backend: `http://localhost:8080`

---

## Testes unitários

Executar testes com relatório de cobertura:

```bash
docker exec -it microlims-api composer test:coverage
```

Executar testes com mutações:

```bash
docker exec -it microlims-api composer test:mutation
```

---

## Decisões técnicas

- **Autenticação via Session:** `session` + `middleware`, evitando complexidade desnecessária.
- **Migrations:** execução automática de migrations que ainda não foram executadas via `entrypoint`.
- **Tailwind:** estilização flexível e modular.
- **Infection:** garantia de qualidade dos cenários de teste.
- **Bruno Collections:** coleção com todos os endpoints da API para facilitar testes no backend.
- **Docker:** maior simplicidade para iniciar o projeto.
- **Pipeline:** execução automática dos testes, garantindo que a lógica permaneça intacta mesmo após alterações.

---

## Teste de API via Bruno

O projeto inclui uma collection para o [Bruno](https://www.usebruno.com) em `bruno/MicroLIMS - Slim Framework/` com requests pré-definidos para todos os endpoints disponíveis.

### Como usar

1. Abra o Bruno
2. Importe a collection (`bruno/MicroLIMS - Slim Framework/`)
3. Execute os requests na ordem sugerida:

| Request | Método | Rota | Descrição |
|---|---|---|---|
| Create User | POST | `/api/users` | Criar conta |
| Login | POST | `/api/login` | Autenticar |
| Verify Login Status | GET | `/api/users/verify` | Verificar sessão |
| Get Users | GET | `/api/users` | Listar usuários |
| Create Sample | POST | `/api/samples` | Cadastrar amostra |
| Get All Samples | GET | `/api/samples` | Listar todas |
| Get Sample By Code | GET | `/api/samples?code={code}` | Buscar por código |
| Get Samples By Type | GET | `/api/samples?type={type}` | Filtrar por tipo |
| Search Samples By Code | GET | `/api/samples?search={query}` | Busca parcial |
| Update Sample Status | PATCH | `/api/samples` | Avançar status |
| Logout | POST | `/api/logout` | Encerrar sessão |
