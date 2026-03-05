# Import Export API

API RESTful para importação e exportação massiva de usuários via CSV, com processamento distribuído, tolerância a falhas e monitoramento, construída com Laravel 12.

## Funcionalidades

- Autenticação JWT (registro, login, refresh token, logout, perfil)
- CRUD de usuários com filtros, paginação e ordenação
- Importação massiva via CSV com processamento em chunks por múltiplos workers
- Exportação assíncrona para CSV com filtros e compressão opcional
- Controle de progresso em tempo real com estimativa de tempo restante
- Feature flags para habilitar/desabilitar funcionalidades
- Correlation ID para rastreamento de requisições
- Health check e métricas (Prometheus-compatible)
- Auditoria automática de alterações
- Cache CQRS com Redis para leitura de status
- Soft delete de usuários
- Validação de dados via DTOs com Spatie Laravel Data

## Stack

| Camada         | Tecnologia                             |
|----------------|----------------------------------------|
| Linguagem      | PHP 8.5                                |
| Framework      | Laravel 12                             |
| Banco de Dados | PostgreSQL 16                          |
| Cache / Fila   | Redis 7                                |
| Autenticação   | JWT (`php-open-source-saver/jwt-auth`) |
| DTOs           | Spatie Laravel Data                    |
| CSV            | League CSV                             |
| Containers     | Docker Compose                         |
| Monitoramento  | Prometheus + Grafana                   |
| Code Style     | Laravel Pint (PSR-12 strict)           |
| Análise        | PHPStan / Larastan (nível 5)           |
| Testes         | PHPUnit + Mockery                      |

## Requisitos

- Docker e Docker Compose

## Instalação

```bash
git clone <url-do-repositorio>
cd Import-Export-API-Project

cp .env.example .env

docker compose up -d

docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
docker compose exec app php artisan migrate --seed
```

Após o setup, a API estará disponível em `http://localhost:8080`.

## Serviços

| Serviço    | URL / Porta          | Descrição                           |
|------------|----------------------|-------------------------------------|
| API        | http://localhost:8080 | Aplicação Laravel (via Nginx)       |
| Prometheus | http://localhost:9090 | Coleta e consulta de métricas       |
| Grafana    | http://localhost:3000 | Dashboards de monitoramento         |
| PostgreSQL | localhost:5434       | Banco de dados                      |
| Redis      | localhost:6381       | Cache, filas e CQRS                 |
| Worker     | —                    | Supervisord com 9 processos de fila |

## Dados Iniciais (Seeder)

| Dado          | Detalhe                                               |
|---------------|-------------------------------------------------------|
| Admin         | `admin@example.com` / `password`                      |
| Manager       | `manager@example.com` / `password`                    |
| Usuário       | `user@example.com` / `password`                       |
| Usuários      | ~95 usuários gerados via factory (admin, manager, user)|
| Importações   | Registros em diversos status (completed, processing, queued, failed) |
| Exportações   | Registros em diversos status com compressão variada   |

---

## Endpoints

Todas as rotas (exceto registro, login, health e metrics) requerem o header:

```
Authorization: Bearer {token}
```

### Health Check

```
GET /api/v1/health
```

**Resposta** `200`:

```json
{
  "data": {
    "status": "healthy",
    "status_label": "Saudável",
    "services": {
      "database": { "status": "up", "latency_ms": 2 },
      "redis": { "status": "up", "latency_ms": 1 },
      "storage": { "status": "up", "latency_ms": 0 }
    },
    "timestamp": "2026-02-27T12:00:00+00:00"
  }
}
```

### Métricas (Prometheus)

```
GET /api/v1/metrics
```

**Resposta** `200`:

```json
{
  "data": {
    "imports": {
      "total": 15,
      "queued": 2,
      "processing": 1,
      "completed": 10,
      "failed": 2
    },
    "exports": {
      "total": 8,
      "queued": 1,
      "processing": 0,
      "completed": 6,
      "failed": 1
    }
  }
}
```

---

### Autenticação

#### Registrar usuário

```
POST /api/v1/auth/register
```

**Body:**

```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "12345678",
  "password_confirmation": "12345678"
}
```

**Resposta** `201`:

```json
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 2,
      "name": "João Silva",
      "email": "joao@email.com",
      "role": null,
      "created_at": "2026-02-27T12:00:00+00:00",
      "updated_at": "2026-02-27T12:00:00+00:00"
    }
  }
}
```

**Validações:**
- `name`: obrigatório, string, máx. 255 caracteres
- `email`: obrigatório, formato e-mail, único na tabela `users`
- `password`: obrigatório, string, mín. 8 caracteres, confirmação obrigatória

#### Login

```
POST /api/v1/auth/login
```

**Body:**

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Resposta** `200`:

```json
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### Refresh Token

```
POST /api/v1/auth/refresh
```

**Resposta** `200`:

```json
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### Logout

```
POST /api/v1/auth/logout
```

**Resposta** `204`: sem corpo

#### Perfil do usuário autenticado

```
GET /api/v1/auth/me
```

**Resposta** `200`:

```json
{
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2026-02-27T12:00:00+00:00",
    "updated_at": "2026-02-27T12:00:00+00:00"
  }
}
```

---

### Usuários

#### Criar usuário

```
POST /api/v1/users
```

**Body:**

```json
{
  "name": "Maria Souza",
  "email": "maria@email.com",
  "password": "12345678",
  "phone": "11999887766",
  "address": "Rua das Flores, 123",
  "city": "São Paulo",
  "state": "SP",
  "zip_code": "01234-567",
  "birth_date": "1990-05-15",
  "role": "user"
}
```

**Resposta** `201`:

```json
{
  "data": {
    "id": 100,
    "name": "Maria Souza",
    "email": "maria@email.com",
    "phone": "11999887766",
    "address": "Rua das Flores, 123",
    "city": "São Paulo",
    "state": "SP",
    "zip_code": "01234-567",
    "birth_date": "1990-05-15",
    "role": "user",
    "role_label": "Usuário",
    "created_at": "2026-02-27T12:00:00+00:00",
    "updated_at": "2026-02-27T12:00:00+00:00"
  }
}
```

**Validações:**
- `name`: obrigatório, string, máx. 255 caracteres
- `email`: obrigatório, formato e-mail, único na tabela `users`
- `password`: obrigatório, string, mín. 8 caracteres
- `phone`: opcional, string, máx. 20 caracteres
- `address`: opcional, string, máx. 255 caracteres
- `city`: opcional, string, máx. 100 caracteres
- `state`: opcional, string, máx. 2 caracteres (UF)
- `zip_code`: opcional, string, máx. 10 caracteres
- `birth_date`: opcional, formato de data válido
- `role`: opcional, um de: `admin`, `manager`, `user` (padrão: `user`)

#### Listar usuários

```
GET /api/v1/users
GET /api/v1/users?search=maria&role=user&state=SP&page=1&per_page=10&sort_by=name&sort_order=asc
```

| Parâmetro    | Tipo   | Padrão       | Descrição                              |
|--------------|--------|--------------|----------------------------------------|
| `search`     | string | —            | Busca por nome ou e-mail               |
| `role`       | string | —            | Filtra por role (`admin`, `manager`, `user`) |
| `state`      | string | —            | Filtra por UF (2 caracteres)           |
| `city`       | string | —            | Filtra por cidade                      |
| `page`       | int    | 1            | Página atual                           |
| `per_page`   | int    | 15           | Itens por página (máx. 100)            |
| `sort_by`    | string | `created_at` | Ordenar por: `name`, `email`, `created_at` |
| `sort_order` | string | `desc`       | Direção: `asc`, `desc`                 |

**Resposta** `200`:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "phone": "11999887766",
      "address": "Rua X, 100",
      "city": "São Paulo",
      "state": "SP",
      "zip_code": "01000-000",
      "birth_date": "1985-03-10",
      "role": "admin",
      "role_label": "Administrador",
      "created_at": "2026-02-27T12:00:00+00:00",
      "updated_at": "2026-02-27T12:00:00+00:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 10, "per_page": 15, "total": 95 }
}
```

#### Buscar usuário

```
GET /api/v1/users/{id}
```

**Resposta** `200`: mesmo formato de um item do array `data` da listagem.

#### Atualizar usuário

```
PUT /api/v1/users/{id}
```

**Body (parcial — todos os campos são opcionais):**

```json
{
  "name": "Maria Souza Atualizada",
  "phone": "11988776655",
  "role": "manager"
}
```

**Resposta** `200`: usuário atualizado.

#### Deletar usuário (soft delete)

```
DELETE /api/v1/users/{id}
```

**Resposta** `204`: sem corpo.

---

### Importação Massiva

As rotas de importação estão protegidas pelo feature flag `import`.

#### Criar importação (upload CSV)

```
POST /api/v1/imports
Content-Type: multipart/form-data
```

**Body (form-data):**

| Campo  | Tipo | Descrição                                               |
|--------|------|---------------------------------------------------------|
| `file` | file | Arquivo CSV com colunas: `name`, `email`, `password`    |

**Exemplo de CSV:**

```csv
name,email,password
User 1,user1@csv.com,password123
User 2,user2@csv.com,password123
User 3,user3@csv.com,password123
```

**Resposta** `202`:

```json
{
  "data": {
    "id": "9f1a2b3c-4d5e-6f7a-8b9c-0d1e2f3a4b5c",
    "status": "queued",
    "status_label": "Na fila",
    "progress": 0,
    "total_records": 3,
    "success_count": 0,
    "failure_count": 0,
    "original_filename": "users.csv",
    "started_at": null,
    "finished_at": null,
    "processing_time_seconds": null,
    "estimated_remaining_seconds": null,
    "created_at": "2026-02-27T12:00:00+00:00"
  }
}
```

**Validações:**
- `file`: obrigatório, tipo `text/csv` ou `text/plain`, máx. 50 MB (configurável via `IMPORT_MAX_FILE_SIZE`)

**Fluxo de processamento:**
1. Upload do CSV e criação do registro de importação
2. Armazenamento do arquivo em storage
3. Dispatch do Job principal
4. Divisão em chunks de 1.000 linhas (configurável via `IMPORT_CHUNK_SIZE`)
5. Chunks processados em paralelo por múltiplos workers
6. Bulk upsert no banco (atômico, idempotente por e-mail)
7. Atualização progressiva do status

#### Listar importações

```
GET /api/v1/imports
GET /api/v1/imports?status=completed&page=1&per_page=10
```

| Parâmetro  | Tipo   | Padrão | Descrição                                             |
|------------|--------|--------|-------------------------------------------------------|
| `status`   | string | —      | `queued`, `processing`, `partial`, `completed`, `failed` |
| `page`     | int    | 1      | Página atual                                          |
| `per_page` | int    | 15     | Itens por página                                      |

**Resposta** `200`: array paginado de importações.

#### Ver status da importação

```
GET /api/v1/imports/{id}
```

**Resposta** `200`:

```json
{
  "data": {
    "id": "9f1a2b3c-4d5e-6f7a-8b9c-0d1e2f3a4b5c",
    "status": "processing",
    "status_label": "Processando",
    "progress": 7500,
    "total_records": 10000,
    "success_count": 7480,
    "failure_count": 20,
    "original_filename": "users_big.csv",
    "started_at": "2026-02-27T12:00:05+00:00",
    "finished_at": null,
    "processing_time_seconds": 45,
    "estimated_remaining_seconds": 15,
    "created_at": "2026-02-27T12:00:00+00:00"
  }
}
```

---

### Exportação Assíncrona

As rotas de exportação estão protegidas pelo feature flag `export`.

#### Criar exportação

```
POST /api/v1/exports
```

**Body:**

```json
{
  "filters": {
    "search": "maria",
    "role": "user",
    "state": "SP",
    "city": "São Paulo"
  },
  "compressed": true
}
```

Todos os campos são opcionais. Sem filtros, exporta todos os usuários. Sem `compressed`, gera CSV sem compressão.

| Campo              | Tipo    | Padrão | Descrição                                         |
|--------------------|---------|--------|----------------------------------------------------|
| `filters.search`   | string  | —      | Busca por nome ou e-mail                           |
| `filters.role`     | string  | —      | `admin`, `manager`, `user`                         |
| `filters.state`    | string  | —      | UF (2 caracteres)                                  |
| `filters.city`     | string  | —      | Nome da cidade                                     |
| `compressed`       | boolean | false  | Comprime o arquivo final (gzip)                    |

**Resposta** `202`:

```json
{
  "data": {
    "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "status": "queued",
    "status_label": "Na fila",
    "total_records": 0,
    "compressed": true,
    "file_path": null,
    "download_url": null,
    "expires_at": null,
    "started_at": null,
    "finished_at": null,
    "processing_time_seconds": null,
    "created_at": "2026-02-27T12:00:00+00:00"
  }
}
```

#### Ver status da exportação

```
GET /api/v1/exports/{id}
```

**Resposta** `200`:

```json
{
  "data": {
    "id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "status": "completed",
    "status_label": "Concluído",
    "total_records": 95,
    "compressed": true,
    "file_path": "exports/a1b2c3d4.csv.gz",
    "download_url": "http://localhost:8080/api/v1/exports/a1b2c3d4.../download?signature=...",
    "expires_at": "2026-02-27T13:00:00+00:00",
    "started_at": "2026-02-27T12:00:01+00:00",
    "finished_at": "2026-02-27T12:00:03+00:00",
    "processing_time_seconds": 2,
    "created_at": "2026-02-27T12:00:00+00:00"
  }
}
```

#### Download da exportação

```
GET /api/v1/exports/{id}/download
```

Retorna o arquivo CSV (ou CSV comprimido) para download. A URL assinada expira em 60 minutos (configurável via `EXPORT_URL_EXPIRY_MINUTES`).

---

## Feature Flags

Funcionalidades podem ser habilitadas/desabilitadas via variáveis de ambiente:

| Variável                     | Padrão | Descrição                           |
|------------------------------|--------|-------------------------------------|
| `FEATURE_IMPORT_ENABLED`     | `true` | Habilita rotas de importação        |
| `FEATURE_EXPORT_ENABLED`     | `true` | Habilita rotas de exportação        |
| `FEATURE_METRICS_ENABLED`    | `true` | Habilita endpoint de métricas       |
| `FEATURE_CQRS_CACHE_ENABLED` | `true` | Habilita cache CQRS com Redis      |

## Variáveis de Ambiente

| Variável                  | Descrição                                 | Valor Padrão              |
|---------------------------|-------------------------------------------|---------------------------|
| `DB_HOST`                 | Host do PostgreSQL                        | `postgres`                |
| `DB_DATABASE`             | Nome do banco                             | `import_export_api_project` |
| `DB_USERNAME`             | Usuário do banco                          | `app_user`                |
| `DB_PASSWORD`             | Senha do banco                            | `app_secret`              |
| `REDIS_HOST`              | Host do Redis                             | `redis`                   |
| `QUEUE_CONNECTION`        | Driver da fila                            | `redis`                   |
| `CACHE_STORE`             | Driver de cache                           | `redis`                   |
| `JWT_SECRET`              | Chave secreta do JWT (gerada pelo setup)  | —                         |
| `JWT_TTL`                 | Expiração do token (minutos)              | `60`                      |
| `JWT_REFRESH_TTL`         | Expiração do refresh token (minutos)      | `20160` (14 dias)         |
| `IMPORT_CHUNK_SIZE`       | Linhas por chunk na importação            | `1000`                    |
| `IMPORT_MAX_FILE_SIZE`    | Tamanho máx. do CSV em bytes              | `52428800` (50 MB)        |
| `EXPORT_URL_EXPIRY_MINUTES` | Tempo de expiração da URL de download  | `60`                      |

## Observabilidade (Prometheus + Grafana)

O projeto inclui um stack completo de monitoramento com **Prometheus** para coleta de métricas e **Grafana** para dashboards visuais.

### Como funciona

```
API (porta 8080)                     Prometheus (porta 9090)             Grafana (porta 3000)
┌──────────────────┐  scrape a cada  ┌──────────────────┐  datasource  ┌──────────────────┐
│ GET /api/v1/     │  10 segundos    │                  │              │                  │
│     metrics      │ ◄────────────── │  Armazena        │ ────────────►│  Dashboards      │
│                  │                 │  histórico (7d)  │              │  pré-configurados│
│ Retorna:         │ ───────────────►│                  │              │                  │
│ import_total     │                 │  PromQL queries  │              │  Auto-refresh    │
│ export_total     │                 │                  │              │  a cada 10s      │
│ queue_size       │                 │                  │              │                  │
│ app_up           │                 │  localhost:9090   │              │  localhost:3000   │
└──────────────────┘                 └──────────────────┘              └──────────────────┘
```

### Acessando

| Serviço    | URL                    | Credenciais         |
|------------|------------------------|---------------------|
| Prometheus | http://localhost:9090   | sem autenticação    |
| Grafana    | http://localhost:3000   | `admin` / `admin`   |

### Prometheus — Consultas PromQL

Acesse `http://localhost:9090` e use a aba **Graph** para executar queries:

| Query                                            | O que retorna                          |
|--------------------------------------------------|----------------------------------------|
| `app_up`                                         | Se a API está online (1 = sim)         |
| `import_total`                                   | Total de imports por status            |
| `import_total{status="failed"}`                  | Imports com falha                      |
| `export_total{status="completed"}`               | Exports concluídos                     |
| `queue_size{queue="imports"}`                     | Jobs pendentes na fila de imports     |
| `sum(queue_size)`                                | Total de jobs em todas as filas        |
| `changes(import_total{status="completed"}[1h])`  | Variação de imports completos em 1h    |

### Grafana — Dashboard

O Grafana já vem com um **dashboard pré-configurado** que inclui:

- **Visão Geral** — status da API, total de imports/exports, tamanho das filas
- **Importações** — gráfico temporal por status, distribuição em donut, contador de falhas
- **Exportações** — gráfico temporal por status, distribuição em donut, contador de falhas
- **Filas** — histórico empilhado do tamanho das 3 filas (default, imports, exports)

Para acessar: `http://localhost:3000` → login com `admin`/`admin` → o dashboard aparece automaticamente.

### Métricas expostas pela API

| Métrica         | Tipo    | Labels           | Descrição                        |
|-----------------|---------|------------------|----------------------------------|
| `app_up`        | gauge   | —                | Aplicação está rodando (sempre 1)|
| `import_total`  | counter | `status`         | Total de imports por status      |
| `export_total`  | counter | `status`         | Total de exports por status      |
| `queue_size`    | gauge   | `queue`          | Jobs pendentes por fila          |

### Configuração

| Arquivo                                          | Descrição                          |
|--------------------------------------------------|------------------------------------|
| `.docker/prometheus/prometheus.yml`              | Config do Prometheus (scrape jobs) |
| `.docker/grafana/provisioning/datasources/`      | Datasource Prometheus (auto)       |
| `.docker/grafana/provisioning/dashboards/`       | Provider de dashboards (auto)      |
| `.docker/grafana/dashboards/import-export-api.json` | Dashboard JSON pré-configurado  |

O Prometheus retém dados por **7 dias** e faz scrape a cada **10 segundos**.

## Filas e Workers

O worker utiliza **Supervisord** com 3 filas dedicadas:

| Fila       | Processos | Timeout | Descrição                     |
|------------|-----------|---------|-------------------------------|
| `default`  | 3         | 300s    | Jobs gerais                   |
| `imports`  | 4         | 600s    | Processamento de chunks CSV   |
| `exports`  | 2         | 600s    | Geração de arquivos de export |

Cada fila possui retry automático com backoff progressivo (10s, 30s, 60s) e máximo de 3 tentativas.

## Testes

```bash
# Todos os testes
docker compose exec app php artisan test

# Por módulo
docker compose exec app php artisan test app/Api/Modules/Auth
docker compose exec app php artisan test app/Api/Modules/User
docker compose exec app php artisan test app/Api/Modules/Import
docker compose exec app php artisan test app/Api/Modules/Export
docker compose exec app php artisan test app/Api/Modules/Health
```

## Qualidade de Código

```bash
# Formatação (Laravel Pint — PSR-12 strict)
docker compose exec app ./vendor/bin/pint

# Análise estática (PHPStan nível 5 + Larastan)
docker compose exec app ./vendor/bin/phpstan analyse

# IDE helper (gera autocompletion para models e facades)
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --write-mixin --no-interaction
```

## Postman

O arquivo `Import-Export-API.postman_collection.json` na raiz do projeto contém uma collection completa com 25 requests para testar todas as rotas. Basta importar no Postman via **File > Import**.

A collection salva automaticamente o token JWT nas variáveis ao fazer login/register e captura IDs de recursos criados.

## Arquitetura

O projeto segue uma **arquitetura modular** onde cada domínio é isolado em seu próprio módulo dentro de `app/Api/Modules/`. Cada módulo possui suas camadas:

```
app/Api/Modules/
├── Auth/
│   ├── Controllers/        → Recebe a requisição HTTP e delega ao UseCase
│   ├── Data/               → DTOs com validação (Spatie Laravel Data)
│   ├── UseCases/           → Orquestra a operação (1 ação por classe — SRP)
│   ├── Services/           → Lógica de negócio complexa (JWT guard)
│   ├── Repositories/       → Acesso a dados (abstração do Eloquent)
│   ├── Resources/          → Formatação da resposta JSON
│   └── Tests/              → Unitários, integração, assertables
├── User/
│   ├── Controllers/
│   ├── Data/               → CreateUserData, UpdateUserData, UserQueryData
│   ├── UseCases/
│   ├── Enums/              → RoleEnum (admin, manager, user)
│   ├── Repositories/
│   ├── Resources/
│   └── Tests/
├── Import/
│   ├── Controllers/
│   ├── Data/               → CreateImportData, ImportQueryData
│   ├── UseCases/
│   ├── Services/           → CsvParserService, ImportService (streaming)
│   ├── Jobs/               → ProcessImportJob, ProcessImportChunkJob
│   ├── Events/             → ImportCompleted, ImportFailed
│   ├── Listeners/          → UpdateImportStatus
│   ├── Enums/              → ImportStatusEnum
│   ├── Repositories/       → Queries, bulk upsert, atomic progress
│   ├── Resources/
│   └── Tests/
├── Export/
│   ├── Controllers/
│   ├── Data/               → CreateExportData, ExportFiltersData
│   ├── UseCases/
│   ├── Services/           → ExportService (streaming + compressão)
│   ├── Jobs/               → ProcessExportJob
│   ├── Enums/              → ExportStatusEnum
│   ├── Repositories/
│   ├── Resources/
│   └── Tests/
└── Health/
    ├── Controllers/        → HealthController, MetricsController
    ├── UseCases/           → GetHealthUseCase, GetMetricsUseCase
    ├── Services/           → HealthService (database, redis, storage)
    ├── Enums/              → HealthStatusEnum
    ├── Resources/
    └── Tests/
```

### Fluxo de uma requisição

```
Request → Controller → DTO (validação) → UseCase → [Service] → Repository → Resource
```

O Service é **opcional** — só existe quando há lógica de negócio complexa.

### Padrões Enterprise

| Padrão                  | Implementação                                              |
|-------------------------|------------------------------------------------------------|
| Correlation ID          | Middleware injeta UUID em cada requisição e propaga nos logs |
| Feature Flags           | Middleware bloqueia rotas de features desabilitadas         |
| Rate Limiting           | Throttle por IP e por usuário autenticado                  |
| CQRS (Read Model)       | Cache Redis para status de import/export                  |
| Domain Events           | ImportCompleted, ImportFailed                              |
| Auditoria               | Trait Auditable com log automático de created/updated/deleted |
| Bulk Operations         | Upsert atômico para importação (idempotente por email)    |
| Streaming               | Generators para parsing CSV e geração de export (sem OOM) |
| Atomic Updates           | `DB::raw()` para incrementos concorrentes de progresso   |

## Estrutura Docker

```
.docker/
├── Dockerfile              → PHP 8.5-FPM Alpine com extensões (pdo_pgsql, redis, pcntl, etc.)
├── php/
│   └── php.ini             → Configuração customizada do PHP
├── nginx/
│   └── default.conf        → Virtual host apontando para public/
├── supervisord.conf        → Workers de fila (default, imports, exports)
├── prometheus/
│   └── prometheus.yml      → Config de scrape (targets, intervalos)
└── grafana/
    ├── provisioning/
    │   ├── datasources/    → Datasource Prometheus (auto-provisionado)
    │   └── dashboards/     → Provider de dashboards (auto-provisionado)
    └── dashboards/
        └── import-export-api.json → Dashboard pré-configurado
```

| Container                   | Descrição                                    |
|-----------------------------|----------------------------------------------|
| `import-export-app`         | PHP-FPM — executa a aplicação Laravel        |
| `import-export-nginx`       | Nginx — proxy reverso para o PHP-FPM         |
| `import-export-postgres`    | PostgreSQL 16 — banco de dados com health check |
| `import-export-redis`       | Redis 7 — cache, filas e CQRS                |
| `import-export-worker`      | Supervisord — 9 processos de fila paralelos  |
| `import-export-prometheus`  | Prometheus — coleta e armazena métricas       |
| `import-export-grafana`     | Grafana — dashboards de monitoramento         |
