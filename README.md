# Programas Municipais · Santa Helena - PR

Site público que explica ao cidadão os programas da Prefeitura: quanto custam, quem atendem e como participar.
As secretarias cadastram, conferem e publicam os próprios programas num painel com login.

- **Site público:** `/` (panorama), `/programas` (lista com filtros "para mim"), `/programas/{programa}` (ficha), `/entenda` (glossário e metodologia)
- **Painel:** `/admin`
- **Manual do cadastrador:** `/manual` (link no menu do painel e na tela de login; botões Imprimir e Baixar PDF)

### Manual do cadastrador

A página fica em `resources/views/manual.blade.php`. As telas (`public/manual-img`) e o PDF (`public/arquivos/manual-do-cadastrador.pdf`)
são gerados a partir do sistema rodando localmente, com o Chrome instalado:

```bash
php artisan migrate:fresh --seed   # usuários de teste: admin / agricultura, senha "password"
php artisan serve
# no painel local, copie o "Viabiliza Agro" para 2026 e preencha valor e atendidos (rascunho de exemplo)
npm run manual                     # telas + PDF  (ou: npm run manual -- telas | pdf)
```

Depois de mudar telas do painel ou o texto do manual, rode `npm run manual` e faça commit das imagens e do PDF.

Laravel 13 · Filament 5 · Tailwind 4 · MySQL/MariaDB (produção) ou SQLite (desenvolvimento).

## Como funciona o fluxo

| Quem | Pode |
|---|---|
| **Servidor de secretaria** | Ver e editar só os programas da própria secretaria. Programa novo nasce como *rascunho* (não aparece no site). Confere em **Pré-visualizar** (`/previa/programas/{id}`, só para quem pode editar) e clica em **Publicar no site**. Em programa publicado, salvar atualiza o site na hora. Pode **Tirar do site** (volta a rascunho); só exclui rascunhos que nunca foram publicados. |
| **Administrador** | Tudo o que o servidor faz, em todas as secretarias, mais: gerencia secretarias e usuários e escolhe o exercício exibido no site. |

Toda alteração fica registrada no **histórico** do programa (quem, quando, o quê).

O painel mostra as **fichas a completar**: programas sem descrição, sem "como participar", sem base legal, sem público-alvo, sem quantidade atendida ou com fonte de recurso não informada.

## Exercícios (anos)

Cada programa pertence a um exercício. O mesmo programa em anos diferentes são registros separados, com o mesmo endereço no site.

- **Ano exibido no site:** o administrador escolhe em *Programas → botão "No site: 2025"*. O site só muda de ano quando ele trocar,
  mesmo que já existam programas do ano seguinte publicados. Os demais anos ficam acessíveis pelo seletor "Exercício" no topo do site
  e por endereços com o ano: `/2024`, `/2024/programas`, `/2024/programas/educa-mais-santa-helena`.
- **O ano não muda depois de criado** (evita apagar, sem querer, os dados de um ano ao "transformá-lo" em outro).
- **Nome único** por secretaria e exercício.

### Virada de ano (ex.: 2025 → 2026)

1. Em *Programas*, filtre por **Ano = 2025**, selecione todos e use **"Copiar para outro exercício"** → 2026.
   As secretarias também podem fazer isso com os próprios programas. Cada cópia vira **rascunho** com descrição, como participar,
   leis, público e tipo; **valor e quantidades ficam em branco**.
2. Cada secretaria filtra por Ano = 2026, completa valores e atendidos (as fichas aparecem em "a completar"), confere em Pré-visualizar e publica.
3. Enquanto isso o site continua mostrando 2025 (os programas de 2026 publicados ficam em `/2026`).
4. Quando 2026 estiver completo, troque em *Programas → "No site"* para 2026. O 2025 continua disponível em `/2025`.

Anos anteriores (ex.: 2024) são cadastrados normalmente, escolhendo o ano no formulário, ou copiando de 2025 e ajustando os números.

## Modelo de dados (resumo)

Cada programa tem: exercício, secretaria, nome, grupo (programa-mãe, ex.: *Renda Santa Helena*), descrição, como participar,
bases legais (tipo/número/ano/link), ano de criação, **mecanismo** (transferência de renda · bolsa/auxílio · incentivo produtivo · serviço público),
públicos-alvo, fonte do recurso, **quantidade de atendidos + unidade** e **quantidade de benefícios** (separados), detalhamento,
**tipo de valor** (anual · anualizado · sem custo direto), valor, valor total e vigência (para anualizados), nota pública e observação interna.

A carga inicial (`database/seeders/ProgramaSeeder.php`) reproduz os 42 programas do
[demonstrativo consolidado de 2025](docs/consolidacao_programas_2025.pdf). Os totais batem com o PDF (R$ 60.136.552,72).
Pendências encontradas no PDF ficaram registradas como *observação interna* nos programas afetados.

## Desenvolvimento local

Requisitos: PHP 8.4+ (o do Herd serve; o composer.lock exige 8.4), Composer, Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

No ambiente `local`, o seeder cria usuários de teste com senha `password`:
`admin` (administrador) e um usuário por secretaria
(`agricultura`, `desenvolvimento-economico`, `esportes`, `assistencia-social`, `educacao`).

Testes: `php artisan test`

## Produção (servidor da Prefeitura)

| Item | Valor |
|---|---|
| Site | https://programas.santahelena.pr.gov.br (painel em `/admin`) |
| Servidor web | HestiaCP em `192.168.0.23`, usuário Hestia **`programas`**, PHP **8.4** (PHP-FPM) |
| Código | `/home/programas/web/programas.santahelena.pr.gov.br/private/dash_programas` |
| Raiz web | `public_html` é um link para `private/dash_programas/public` (veja abaixo) |
| Banco | MySQL 8 em `192.168.0.24`, banco e usuário **`programas`** (acesso só a partir do `.23`). A senha está apenas no `.env` do servidor |
| Certificado | Wildcard `*.santahelena.pr.gov.br`, o mesmo dos demais domínios |
| GitHub | O servidor baixa o código com uma *deploy key* de leitura (`~programas/.ssh/github_dash_programas`) |

**Por que o código fica em `private/`:** o Hestia limita o PHP (`open_basedir`) à raiz do site. Se a raiz apontar para
`public_html/public`, o Laravel não consegue ler `storage/` e `vendor/`. Com o projeto em `private/` (que o PHP pode ler)
e `public_html` apontando para `private/dash_programas/public`, funciona sem alterar os templates do servidor.

### Atualizar o site

1. Na sua máquina: faça as alterações, rode `npm run build` se mexeu em CSS/JS/views do site, rode os testes, faça commit e push.
2. No servidor:
   ```bash
   ssh root@192.168.0.23
   su - programas -c "~/web/programas.santahelena.pr.gov.br/private/dash_programas/deploy.sh"
   ```
   O `deploy.sh` coloca o site em manutenção, faz `git pull`, `composer install`, `migrate`, recria os caches e tira da manutenção.

### Usuários do painel

Os **servidores das secretarias são cadastrados no painel**, pelo administrador: *Administração → Usuários → Novo usuário*
(nome, usuário, secretaria, senha e, opcionalmente, e-mail). O login é feito pelo **nome de usuário**. O servidor só vê e edita os programas da secretaria escolhida.
Para alterar a senha de alguém, edite o usuário e preencha "Nova senha".

O terminal é usado só para criar **administradores** (necessário para o primeiro acesso). Logado como root no `192.168.0.23`:

```bash
su - programas -c "cd ~/web/programas.santahelena.pr.gov.br/private/dash_programas && php8.4 artisan usuarios:criar"
```

### Pontos de atenção

- **E-mail:** o `.env` de produção está com `MAIL_MAILER=log`, então "Esqueceu sua senha?" não envia e-mail. Configure o SMTP no `.env` (`MAIL_*`) e rode o `deploy.sh`.
- **Certificado:** o wildcard foi copiado do domínio `esporte`. Ao renovar o wildcard nos demais domínios, renove também neste (`v-add-web-domain-ssl` / `v-update-web-domain-ssl`).
- **Backup:** o backup do Hestia cobre os arquivos; o banco está no `192.168.0.24` e precisa estar na rotina de backup daquele servidor.

### Instalação do zero (referência)

1. Hestia: `v-add-user programas …`, `v-change-user-shell programas bash`, `v-add-web-domain programas <domínio>`, `v-change-web-domain-backend-tpl programas <domínio> PHP-8_4`.
2. MySQL: `CREATE DATABASE programas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` e um usuário com `GRANT ALL ON programas.*` só para o IP do servidor web.
3. Como `programas`: clonar em `~/web/<domínio>/private/dash_programas`, `php8.4 /usr/bin/composer install --no-dev -o`, copiar `.env.example` para `.env` (APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*), `php8.4 artisan key:generate`.
4. Como root: substituir `public_html` por um link `public_html -> private/dash_programas/public` (`chown -h programas:www-data public_html`).
5. `php8.4 artisan migrate --force`, `php8.4 artisan db:seed --force` (secretarias + 42 programas do PDF), `usuarios:criar` (administrador), `optimize`, `filament:optimize`.
6. SSL: `v-add-web-domain-ssl` com o wildcard e `v-add-web-domain-ssl-force`.

Os arquivos de `public/build` (CSS/JS do site) vêm compilados no repositório, então **o servidor não precisa de Node**.
