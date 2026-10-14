# Estética Perto

Diretório público de estéticas automotivas. O visitante encontra estabelecimentos, vê os serviços oferecidos, o que está incluído e o preço de cada um, e entra em contato direto com a estética. Não há cadastro de visitante, pagamento ou agendamento pela plataforma.

Estado atual:

- catálogo público (página inicial, listagem e página de cada estética), exibindo somente estéticas aprovadas e serviços ativos;
- área da estética: conta do responsável, solicitação de cadastro, dados da empresa, horários de funcionamento e gerenciamento de serviços.

## Stack

- PHP 8.4 · Laravel 13 · Blade
- PostgreSQL 17
- Tailwind CSS 4 · Vite
- Docker e Docker Compose

## Requisitos

Somente Docker com Docker Compose v2. PHP, Composer, Node.js e PostgreSQL rodam dentro dos containers, então não precisam estar instalados na máquina.

## Serviços Docker

| Serviço   | Imagem                        | Uso                                                   | Porta  |
|-----------|-------------------------------|-------------------------------------------------------|--------|
| `php`     | `docker/php/Dockerfile.fpm`   | PHP-FPM 8.4 com Composer                              | —      |
| `nginx`   | `nginx:alpine`                | Servidor web da aplicação                             | `8000` |
| `node`    | `node:22-alpine`              | npm e Vite (`npm run dev`)                            | `5173` |
| `db`      | `postgres:17-alpine`          | Banco `estetica` e banco de testes `estetica_testing` | `5432` |
| `pgadmin` | `dpage/pgadmin4`              | Interface web opcional para o banco                   | `5050` |

Os containers `php` e `node` usam o UID/GID `1000` por padrão para que os arquivos gerados pertençam ao seu usuário. Se o seu for diferente, exporte `UID` e `GID` antes do build. As portas podem ser alteradas com `APP_PORT`, `VITE_PORT`, `FORWARD_DB_PORT` e `PGADMIN_PORT`.

## Primeira execução

```bash
cp .env.example .env
docker compose build
docker compose run --rm php composer install
docker compose run --rm node npm install
docker compose run --rm php php artisan key:generate
docker compose up -d
docker compose exec php php artisan migrate --seed
```

Acesse http://localhost:8000.

## `.env`

O `.env.example` já aponta para o PostgreSQL da rede do Compose (`DB_HOST=db`) com credenciais apenas locais. Se alterar `DB_DATABASE`, `DB_USERNAME` ou `DB_PASSWORD`, faça isso antes do primeiro `docker compose up`, porque o container do banco usa esses mesmos valores ao criar o volume.

## Banco de dados

```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
docker compose exec php php artisan migrate:fresh --seed
```

O seeder cria 10 estéticas aprovadas e 1 pendente, todas fictícias e em Campinas (SP), com 4 a 10 serviços cada (alguns inativos) e horários de funcionamento.

Também cria duas contas fictícias de responsável, válidas apenas no ambiente local:

| E-mail                  | Senha      | Situação da estética |
|-------------------------|------------|----------------------|
| `aprovada@exemplo.test` | `password` | Aprovada             |
| `pendente@exemplo.test` | `password` | Aguardando análise   |

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
docker compose exec php composer test
```

## Formatação

```bash
docker compose exec php ./vendor/bin/pint
```

## Comandos úteis

```bash
docker compose up -d
docker compose down
docker compose logs -f php nginx
docker compose exec php php artisan route:list
docker compose exec php composer <comando>
docker compose run --rm node npm <comando>
```

## Rotas públicas

| Rota               | Descrição                                      |
|--------------------|------------------------------------------------|
| `/`                | Página inicial com algumas estéticas           |
| `/esteticas`       | Listagem das estéticas aprovadas               |
| `/esteticas/{slug}`| Página da estética com serviços e preços       |

## Área da estética

O visitante não tem conta. Apenas o responsável por uma estética se cadastra.

1. Em `/cadastro`, o responsável cria a conta com nome, e-mail e senha.
2. Em seguida informa os dados da estética (empresa, contato e endereço). A solicitação fica com status `pending` e não aparece no catálogo.
3. No painel (`/painel`) ele acompanha a situação do cadastro, edita os dados, define os horários de funcionamento e gerencia os serviços.

Cada conta gerencia somente a própria estética. O status (`pending`, `approved`, `rejected`, `inactive`) não pode ser alterado pelo responsável; a análise administrativa ainda não existe, então para testar uma estética aprovada use a conta `aprovada@exemplo.test` do seeder. Estéticas inativas ficam somente para consulta no painel.

| Rota                            | Descrição                                   |
|---------------------------------|---------------------------------------------|
| `/cadastro`                     | Criação da conta do responsável             |
| `/entrar`                       | Login                                       |
| `/painel`                       | Visão geral e situação do cadastro          |
| `/painel/estetica/cadastrar`    | Solicitação de cadastro da estética         |
| `/painel/estetica`              | Edição dos dados da estética                |
| `/painel/horarios`              | Horários de funcionamento                   |
| `/painel/servicos`              | Serviços: criar, editar, ativar/desativar e remover |
