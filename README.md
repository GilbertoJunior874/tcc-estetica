# Estética Perto

Diretório público de estéticas automotivas. O visitante encontra estabelecimentos, vê os serviços oferecidos, o que está incluído e o preço de cada um, e entra em contato direto com a estética. Não há cadastro de visitante, pagamento ou agendamento pela plataforma.

Estado atual: catálogo público (página inicial, listagem e página de cada estética) com dados fictícios de desenvolvimento.

## Stack

- PHP 8.4 · Laravel 13 · Blade
- PostgreSQL 17
- Tailwind CSS 4 · Vite
- Docker e Docker Compose

## Requisitos

Somente Docker com Docker Compose v2. PHP, Composer, Node.js e PostgreSQL rodam dentro dos containers, então não precisam estar instalados na máquina.

## Serviços Docker

| Serviço | Imagem                        | Uso                                                   | Porta  |
|---------|-------------------------------|-------------------------------------------------------|--------|
| `app`   | `docker/php/Dockerfile`       | PHP 8.4 com Composer; roda `php artisan serve`        | `8000` |
| `node`  | `node:22-alpine`              | npm e Vite (`npm run dev`)                            | `5173` |
| `db`    | `postgres:17-alpine`          | Banco `estetica` e banco de testes `estetica_testing` | `5432` |

Os containers `app` e `node` usam o UID/GID `1000` por padrão para que os arquivos gerados pertençam ao seu usuário. Se o seu for diferente, exporte `UID` e `GID` antes do build. As portas podem ser alteradas com `APP_PORT`, `VITE_PORT` e `FORWARD_DB_PORT`.

## Primeira execução

```bash
cp .env.example .env
docker compose build
docker compose run --rm app composer install
docker compose run --rm node npm install
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Acesse http://localhost:8000.

## `.env`

O `.env.example` já aponta para o PostgreSQL da rede do Compose (`DB_HOST=db`) com credenciais apenas locais. Se alterar `DB_DATABASE`, `DB_USERNAME` ou `DB_PASSWORD`, faça isso antes do primeiro `docker compose up`, porque o container do banco usa esses mesmos valores ao criar o volume.

## Banco de dados

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan migrate:fresh --seed
```

O seeder cria 10 estéticas fictícias em Campinas (SP), cada uma com 4 a 10 serviços. Todas ficam com status aprovado.

Para acessar o banco pelo terminal:

```bash
docker compose exec db psql -U estetica estetica
```

## Frontend (Vite)

Com `docker compose up -d`, o container `node` já roda o Vite em modo dev, com hot reload em http://localhost:5173.

Build de produção:

```bash
docker compose run --rm node npm run build
```

Quando `public/hot` não existe, o Laravel usa os arquivos gerados em `public/build`.

## Testes

Os testes rodam no banco `estetica_testing`, criado automaticamente na primeira vez que o container `db` sobe.

```bash
docker compose exec app composer test
```

## Formatação

```bash
docker compose exec app ./vendor/bin/pint
```

## Comandos úteis

```bash
docker compose up -d
docker compose down
docker compose logs -f app
docker compose exec app php artisan route:list
docker compose exec app composer <comando>
docker compose run --rm node npm <comando>
```

## Rotas públicas

| Rota               | Descrição                                      |
|--------------------|------------------------------------------------|
| `/`                | Página inicial com algumas estéticas           |
| `/esteticas`       | Listagem das estéticas aprovadas               |
| `/esteticas/{slug}`| Página da estética com serviços e preços       |
