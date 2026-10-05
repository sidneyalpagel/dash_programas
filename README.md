# Programas Municipais · Santa Helena - PR

Site público que explica ao cidadão os programas da Prefeitura: quanto custam, quem atendem e como participar.
As secretarias cadastram e atualizam os programas num painel com login, e o administrador revisa antes de publicar.

- **Site público:** `/` (panorama), `/programas` (lista com filtros "para mim"), `/programas/{programa}` (ficha), `/entenda` (glossário e metodologia)
- **Painel:** `/admin`

Laravel 13 · Filament 5 · Tailwind 4 · MySQL/MariaDB (produção) ou SQLite (desenvolvimento).

## Como funciona o fluxo

| Quem | Pode |
|---|---|
| **Servidor de secretaria** | Ver e editar só os programas da própria secretaria. Programa novo nasce como *rascunho* → "Enviar para revisão". Se o programa já está publicado, a edição vira uma **proposta**: o site continua mostrando a versão publicada até a aprovação. |
| **Administrador** | Tudo. Revisa propostas campo a campo ("Revisar alterações" → aprovar ou descartar), publica, tira do site, gerencia secretarias e usuários. |

Toda alteração fica registrada no **histórico** do programa (quem, quando, o quê).

O painel mostra as **fichas a completar**: programas sem descrição, sem "como participar", sem base legal, sem público-alvo, sem quantidade atendida ou com fonte de recurso não informada.

## Modelo de dados (resumo)

Cada programa tem: exercício, secretaria, nome, grupo (programa-mãe, ex.: *Renda Santa Helena*), descrição, como participar,
bases legais (tipo/número/ano/link), ano de criação, **mecanismo** (transferência de renda · bolsa/auxílio · incentivo produtivo · serviço público),
públicos-alvo, fonte do recurso, **quantidade de atendidos + unidade** e **quantidade de benefícios** (separados), detalhamento,
**tipo de valor** (anual · anualizado · sem custo direto), valor, valor total e vigência (para anualizados), nota pública e observação interna.

A carga inicial (`database/seeders/ProgramaSeeder.php`) reproduz os 42 programas do
[demonstrativo consolidado de 2025](docs/consolidacao_programas_2025.pdf). Os totais batem com o PDF (R$ 60.136.552,72).
Pendências encontradas no PDF ficaram registradas como *observação interna* nos programas afetados.

## Desenvolvimento local

Requisitos: PHP 8.3+ (o do Herd serve), Composer, Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

No ambiente `local`, o seeder cria usuários de teste com senha `password`:
`admin@santahelena.test` (administrador) e `<secretaria>@santahelena.test`
(`agricultura`, `desenvolvimento-economico`, `esportes`, `assistencia-social`, `educacao`).

Testes: `php artisan test`

## Instalação no servidor (HestiaCP)

1. **Domínio:** em *Web*, adicione o domínio (ex.: `programas.santahelena.pr.gov.br`), ative SSL (Let's Encrypt)
   e escolha o template **laravel** (Nginx) com PHP **8.3 ou superior**. Esse template aponta a raiz para `public_html/public`.
   Se a sua versão do Hestia não tiver esse template, peça ao responsável pelo servidor que aponte a raiz do domínio para a pasta `public` do projeto.
2. **Banco:** em *DB*, crie um banco MySQL/MariaDB e anote nome, usuário e senha (o Hestia prefixa com o nome do usuário).
3. **Código** (via SSH, com o usuário do Hestia):
   ```bash
   cd ~/web/programas.santahelena.pr.gov.br
   rm -rf public_html && git clone https://github.com/sidneyalpagel/dash_programas.git public_html
   cd public_html
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
4. **Configuração** — edite o `.env`:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://programas.santahelena.pr.gov.br

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=usuario_programas
   DB_USERNAME=usuario_programas
   DB_PASSWORD=...

   # Necessário para "Esqueceu sua senha?" funcionar
   MAIL_MAILER=smtp
   MAIL_HOST=...
   MAIL_PORT=587
   MAIL_USERNAME=...
   MAIL_PASSWORD=...
   MAIL_FROM_ADDRESS=nao-responda@santahelena.pr.gov.br
   ```
5. **Banco, dados iniciais e primeiro administrador:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force          # secretarias + 42 programas do PDF
   php artisan usuarios:criar --admin   # pede nome, e-mail e senha
   php artisan optimize
   php artisan filament:optimize
   ```
6. Crie os usuários das secretarias pelo painel (*Administração → Usuários*) ou com `php artisan usuarios:criar`.

Os arquivos de `public/build` (CSS/JS do site) já vêm compilados no repositório, então **o servidor não precisa de Node**.

### Atualizar o servidor

```bash
cd ~/web/programas.santahelena.pr.gov.br/public_html
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan filament:optimize
php artisan up
```

Se alterar CSS/JS ou as views do site, rode `npm run build` **na sua máquina** e faça commit de `public/build` antes do `git pull` no servidor.

### Backup

O backup do Hestia (*Backups*) já inclui os arquivos e o banco. Recomenda-se ativar o backup diário do usuário.
