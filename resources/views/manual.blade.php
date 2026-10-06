@php
    $img = fn (string $arquivo) => asset('manual-img/'.$arquivo);
    $pdf = file_exists(public_path('arquivos/manual-do-cadastrador.pdf')) ? asset('arquivos/manual-do-cadastrador.pdf') : null;
    $capitulos = [
        'acesso' => 'Acesso ao painel',
        'visao' => 'O painel em um minuto',
        'caminho' => 'Do rascunho ao site',
        'cadastrar' => 'Cadastrar um programa',
        'publicar' => 'Conferir e publicar',
        'escrever' => 'Como escrever para o cidadão',
        'atualizar' => 'Atualizar um programa publicado',
        'fichas' => 'Fichas a completar',
        'virada' => 'Virada de ano',
        'duvidas' => 'Dúvidas frequentes',
    ];
    $n = array_flip(array_keys($capitulos));
    $num = fn (string $id) => $n[$id] + 1;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manual do cadastrador · Programas Municipais</title>
    <meta name="description" content="Guia para quem cadastra, confere e publica os programas municipais de Santa Helena.">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=rubik:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { background: var(--page); color: var(--ink); }
        .m { max-width: 960px; margin: 0 auto; padding: 0 24px 72px; }
        .m p, .m li { font-size: 15px; line-height: 1.6; }
        .m a { color: var(--link); }
        .m h3 { font-size: 17px; font-weight: 600; margin: 26px 0 8px; }
        .m ul { list-style: disc; padding-left: 22px; margin: 8px 0; }
        .m ul li { margin: 4px 0; }
        .m code { font-family: Consolas, "Courier New", monospace; font-size: 13.5px; background: var(--surface-2); padding: 1px 6px; border-radius: 4px; overflow-wrap: anywhere; }
        .botao { display: inline-block; padding: 1px 9px; border: 1px solid #c9d1db; border-radius: 6px; background: #fff; color: #1c1f24; font-size: 13px; font-weight: 600; white-space: nowrap; vertical-align: 1px; }
        .botao.verde { background: #16a34a; border-color: #16a34a; color: #fff; }
        .menu { display: inline-block; padding: 1px 10px; border-radius: 999px; background: var(--brand); color: #fff; font-size: 12.5px; font-weight: 600; vertical-align: 1px; white-space: nowrap; }

        /* topo */
        .topo { position: sticky; top: 0; z-index: 20; background: #fff; border-bottom: 4px solid var(--accent); }
        .topo .in { max-width: 960px; margin: 0 auto; padding: 10px 24px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .marca { display: flex; align-items: center; gap: 14px; text-decoration: none; }
        .marca img { height: 44px; width: auto; }
        .marca div { border-left: 1px solid #d4d7db; padding-left: 14px; line-height: 1.2; }
        .marca strong { display: block; color: var(--brand-2); font-size: 17px; }
        .marca span { color: #4f4f50; font-size: 13px; }
        .acoes { display: flex; flex-wrap: wrap; gap: 6px; }
        .acoes a, .acoes button { font: inherit; font-size: 14px; font-weight: 500; padding: 7px 12px; border-radius: 8px; color: #4f4f50; text-decoration: none; background: none; border: 0; cursor: pointer; }
        .acoes a:hover, .acoes button:hover { background: rgba(27, 162, 232, .1); color: var(--brand-2); }
        .acoes .principal { background: var(--brand); color: #fff; }
        .acoes .principal:hover { background: var(--brand-2); color: #fff; }

        /* capa */
        .capa { margin: 24px 0 28px; padding: 36px 36px 32px; border-radius: 16px; color: #fff;
            background: radial-gradient(60rem 30rem at 85% -10%, rgba(27, 162, 232, .35), transparent 60%), linear-gradient(160deg, var(--brand) 0%, var(--brand-2) 100%); }
        .capa .kicker { color: var(--accent-soft); font-size: 13px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
        .capa h1 { margin: 8px 0 6px; font-size: 34px; font-weight: 700; letter-spacing: -.01em; line-height: 1.15; }
        .capa p { max-width: 640px; font-size: 16px !important; color: rgba(255, 255, 255, .9); }
        .capa .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .capa .meta span { padding: 5px 12px; border-radius: 999px; background: rgba(255, 255, 255, .12); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18); font-size: 13px; font-weight: 500; }

        /* sumário */
        .sumario { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 36px; }
        .sumario a { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); text-decoration: none; color: var(--ink); box-shadow: 0 1px 2px rgba(0, 0, 0, .04); transition: transform .15s; }
        .sumario a:hover { transform: translateY(-2px); border-color: var(--accent-soft); }
        .sumario b { flex: none; width: 28px; height: 28px; display: grid; place-items: center; border-radius: 50%; background: var(--brand); color: #fff; font-size: 13px; font-weight: 700; }
        .sumario span { font-size: 14px; font-weight: 600; line-height: 1.3; }

        /* capítulos */
        section.cap { margin-top: 48px; scroll-margin-top: 90px; }
        .capTitulo { display: flex; align-items: center; gap: 14px; margin-bottom: 6px; }
        .capTitulo b { flex: none; width: 40px; height: 40px; display: grid; place-items: center; border-radius: 12px; background: var(--brand); color: #fff; font-size: 18px; font-weight: 700; }
        .capTitulo h2 { font-size: 26px; font-weight: 600; letter-spacing: -.01em; }
        .capIntro { color: var(--ink-2); margin-bottom: 14px; max-width: 760px; }

        /* passos */
        .passos { list-style: none !important; padding: 0 !important; margin: 10px 0; counter-reset: passo; display: grid; gap: 8px; }
        .passos li { counter-increment: passo; display: grid; grid-template-columns: 34px minmax(0, 1fr); column-gap: 12px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); margin: 0 !important; }
        .passos li::before { content: counter(passo); grid-column: 1; grid-row: 1 / span 3; width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50%; background: var(--accent); color: #fff; font-weight: 700; font-size: 14px; }
        .passos li > * { grid-column: 2; }
        .passos li > strong { display: block; font-weight: 600; }
        .passos li small strong { font-weight: 600; color: var(--ink); }
        .passos li small { display: block; color: var(--ink-2); font-size: 14px; margin-top: 2px; line-height: 1.5; }

        /* figuras */
        figure.fig { margin: 16px 0 22px; border: 1px solid var(--line); border-radius: 14px; background: var(--surface); box-shadow: 0 1px 3px rgba(0, 0, 0, .06); overflow: hidden; }
        figure.fig img { display: block; width: 100%; height: auto; }
        figure.fig figcaption { padding: 10px 14px; font-size: 13.5px; color: var(--ink-2); border-top: 1px solid var(--line); }
        figure.fig figcaption b { color: var(--ink); }
        figure.estreita img { max-width: 520px; margin: 0 auto; }
        figure.celular img { max-width: 300px; margin: 0 auto; }
        .figs2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start; }
        .figs2 figure.fig { margin: 16px 0 6px; }

        /* caixas */
        .dica, .atencao, .nota { display: grid; grid-template-columns: 26px 1fr; gap: 10px; padding: 12px 14px; border-radius: 10px; margin: 14px 0; font-size: 14.5px; line-height: 1.55; }
        .dica { background: #eef7fd; border: 1px solid #cfe8f8; }
        .atencao { background: var(--warn-bg); border: 1px solid #f1e3b4; }
        .nota { background: var(--surface-2); border: 1px solid var(--line); }
        .dica i, .atencao i, .nota i { font-style: normal; width: 24px; height: 24px; display: grid; place-items: center; border-radius: 50%; font-weight: 800; font-size: 13px; color: #fff; }
        .dica i { background: var(--accent); } .atencao i { background: #e08a1e; } .nota i { background: var(--muted); }

        /* tabelas */
        table.tb { width: 100%; border-collapse: separate; border-spacing: 0; margin: 10px 0 16px; font-size: 14px; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: var(--surface); }
        .tb th, .tb td { padding: 9px 12px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--line); }
        .tb tr:last-child td { border-bottom: 0; }
        .tb th { background: var(--surface-2); font-weight: 600; font-size: 12.5px; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); }
        .tb td:first-child { font-weight: 600; }

        /* estados do programa */
        .estados { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 14px 0; }
        .estado { padding: 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); }
        .estado .selo { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12.5px; font-weight: 600; }
        .estado p { margin-top: 8px; font-size: 14px !important; color: var(--ink-2); }
        .diagrama { margin: 16px 0 6px; padding: 16px; border: 1px solid var(--line); border-radius: 14px; background: var(--surface); overflow-x: auto; color: var(--ink); }
        .diagrama svg { display: block; width: 100%; min-width: 560px; height: auto; }

        /* perguntas */
        .faq { display: grid; gap: 8px; }
        .faq details { border: 1px solid var(--line); border-radius: 10px; background: var(--surface); padding: 10px 14px; }
        .faq summary { cursor: pointer; font-weight: 600; }
        .faq p { margin-top: 6px; color: var(--ink-2); }

        .rodapeManual { margin-top: 48px; padding-top: 16px; border-top: 1px solid var(--line); color: var(--muted); font-size: 13px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px; }

        @media print {
            @page { size: A4; margin: 14mm 14mm 16mm; }
            .topo, .semImpressao { display: none !important; }
            body { background: #fff; }
            .m { padding: 0; max-width: none; }
            .capa { margin-top: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sumario a { box-shadow: none; }
            section.cap { break-before: page; margin-top: 0; }
            figure.fig, .dica, .atencao, .nota, .passos li, table.tb, .estado, .diagrama, .faq details { break-inside: avoid; }
            figure.fig { box-shadow: none; }
            .m a { color: inherit; text-decoration: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 760px) {
            .m { padding: 0 14px 48px; }
            .capa { padding: 24px 20px; margin: 14px 0 20px; }
            .capa h1 { font-size: 26px; }
            .sumario { grid-template-columns: 1fr 1fr; }
            .figs2, .estados { grid-template-columns: 1fr; }
            .capTitulo h2 { font-size: 21px; }
            .marca div { display: none; }
        }
    </style>
</head>
<body class="font-sans antialiased">
<header class="topo">
    <div class="in">
        <a class="marca" href="{{ route('inicio') }}" aria-label="Site dos programas municipais">
            <img src="{{ asset('img/logo-prefeitura-santa-helena.png') }}" alt="Prefeitura de Santa Helena" width="819" height="203">
            <div><strong>Programas Municipais</strong><span>Manual do cadastrador</span></div>
        </a>
        <nav class="acoes" aria-label="Ações">
            <a href="{{ route('inicio') }}">Ver o site</a>
            <button type="button" onclick="window.print()">Imprimir</button>
            @if ($pdf)
                <a href="{{ $pdf }}" download>Baixar PDF</a>
            @endif
            <a class="principal" href="{{ url('/admin') }}">Ir para o painel</a>
        </nav>
    </div>
</header>

<main class="m">
    <div class="capa">
        <span class="kicker">Programas municipais · Prefeitura de Santa Helena</span>
        <h1>Manual do cadastrador</h1>
        <p>Guia para o servidor de cada secretaria que cadastra, confere e publica os programas no site. Em dez partes, com as telas do sistema.</p>
        <div class="meta"><span>Versão outubro/2026</span><span>programas.santahelena.pr.gov.br/admin</span><span>Leitura: 15 minutos</span></div>
    </div>

    <nav class="sumario" aria-label="Sumário">
        @foreach ($capitulos as $id => $titulo)
            <a href="#{{ $id }}"><b>{{ $loop->iteration }}</b><span>{{ $titulo }}</span></a>
        @endforeach
    </nav>

    {{-- 1 ================================================================= --}}
    <section class="cap" id="acesso">
        <div class="capTitulo"><b>{{ $num('acesso') }}</b><h2>Acesso ao painel</h2></div>
        <p class="capIntro">O painel é a área restrita onde cada secretaria mantém os seus programas. Cada pessoa tem o próprio usuário, e tudo o que faz fica registrado em seu nome.</p>
        <ol class="passos">
            <li><strong>Abra o endereço do painel</strong><small><code>https://programas.santahelena.pr.gov.br/admin</code> — ou clique em <span class="botao">Acesso restrito das secretarias</span>, no rodapé do site.</small></li>
            <li><strong>Entre com usuário e senha</strong><small>Criados pelo administrador do sistema. O login é pelo <strong>nome de usuário</strong> (ex.: <code>maria.silva</code>), não pelo e-mail; maiúsculas não fazem diferença.</small></li>
            <li><strong>Troque a senha no primeiro acesso</strong><small>Clique no círculo com suas iniciais, no canto superior direito → <span class="botao">Perfil</span>. Mínimo de 8 caracteres; o sistema pede a senha atual para salvar.</small></li>
            <li><strong>Ao terminar, saia</strong><small>Menu das iniciais → <span class="botao">Sair</span>. Indispensável em computador compartilhado.</small></li>
        </ol>
        <figure class="fig estreita"><img src="{{ $img('01-login.png') }}" alt="Tela de login do painel" loading="lazy"><figcaption><b>Tela de entrada.</b> Campo “Usuário” (não e-mail) e senha.</figcaption></figure>
        <div class="dica"><i>i</i><div>Esqueceu a senha? Peça ao administrador para definir uma nova. O link “Esqueceu sua senha?” só funciona se o seu e-mail estiver no perfil e o envio de e-mails estiver configurado.</div></div>
    </section>

    {{-- 2 ================================================================= --}}
    <section class="cap" id="visao">
        <div class="capTitulo"><b>{{ $num('visao') }}</b><h2>O painel em um minuto</h2></div>
        <p class="capIntro">Você só vê os programas da sua secretaria. A tela inicial resume a situação e mostra o que falta fazer.</p>
        <figure class="fig"><img src="{{ $img('02-painel.png') }}" alt="Painel de controle" loading="lazy"><figcaption><b>Painel de Controle.</b> No menu: <b>Programas</b> (o número ao lado é a quantidade de rascunhos), <b>Manual do cadastrador</b> e <b>Ver o site público</b>. No centro, os contadores, o guia “Como funciona” e a lista das fichas a completar.</figcaption></figure>
        <table class="tb">
            <tr><th style="width:220px">Bloco</th><th>O que mostra</th></tr>
            <tr><td>Publicados em 2025</td><td>Quantos programas da secretaria estão no site no ano exibido, e quanto somam.</td></tr>
            <tr><td>Rascunhos</td><td>Programas salvos que ainda não estão no site. Confira e publique (parte {{ $num('publicar') }}).</td></tr>
            <tr><td>Fichas a completar</td><td>Programas com informação faltando para o cidadão (parte {{ $num('fichas') }}).</td></tr>
        </table>
        <figure class="fig"><img src="{{ $img('04-lista.png') }}" alt="Lista de programas" loading="lazy"><figcaption><b>Programas.</b> Cada linha traz o tipo, o valor no ano, a situação da <b>Ficha</b> (“Completa” ou “3 a completar” — passe o mouse para ver o que falta), a <b>Situação</b> (Rascunho ou Publicado) e o <b>Ano</b>. Use a pesquisa e o funil para filtrar.</figcaption></figure>
    </section>

    {{-- 3 ================================================================= --}}
    <section class="cap" id="caminho">
        <div class="capTitulo"><b>{{ $num('caminho') }}</b><h2>Do rascunho ao site</h2></div>
        <p class="capIntro">Nada vai ao site sem o seu clique em <span class="botao verde">Publicar no site</span>. Antes disso, você confere a ficha exatamente como o cidadão vai ver.</p>
        <div class="diagrama">@include('manual.diagrama')</div>
        <div class="estados">
            <div class="estado"><span class="selo" style="background:#eef1f4;color:#4f4f50">Rascunho</span><p>Salvo no painel, invisível no site. Só você e o administrador veem. Salve quantas vezes quiser.</p></div>
            <div class="estado"><span class="selo" style="background:#fff6dc;color:#6b4e00">Pré-visualização</span><p>A ficha como vai ficar, numa nova aba, com uma faixa amarela de aviso. Ninguém de fora vê.</p></div>
            <div class="estado"><span class="selo" style="background:#dcfce7;color:#166534">Publicado</span><p>No site. A partir daqui, <strong>cada alteração salva aparece na hora</strong>.</p></div>
        </div>
    </section>

    {{-- 4 ================================================================= --}}
    <section class="cap" id="cadastrar">
        <div class="capTitulo"><b>{{ $num('cadastrar') }}</b><h2>Cadastrar um programa</h2></div>
        <p class="capIntro">Em <span class="menu">Programas</span>, clique em <span class="botao">Criar programa</span>. O formulário tem seis partes; campos com asterisco (*) são obrigatórios. Ao salvar, o programa fica como <strong>rascunho</strong>.</p>
        <figure class="fig"><img src="{{ $img('05-rascunho-topo.png') }}" alt="Formulário do programa, parte 1" loading="lazy"><figcaption><b>Formulário em rascunho.</b> No topo, <b>Pré-visualizar</b> e <b>Publicar no site</b>; o aviso cinza lembra que o rascunho não aparece no site. Abaixo, a parte 1 — Identificação.</figcaption></figure>

        <h3>Parte 1 — Identificação</h3>
        <table class="tb">
            <tr><th style="width:230px">Campo</th><th>Como preencher</th></tr>
            <tr><td>Ano (exercício)*</td><td>O ano dos valores e dos atendidos, ex.: <code>2026</code>. <strong>Não pode ser mudado depois.</strong></td></tr>
            <tr><td>Nome do programa*</td><td>O nome pelo qual o cidadão conhece. Não pode repetir outro da sua secretaria no mesmo ano.</td></tr>
            <tr><td>Faz parte de um programa maior?</td><td>Só se for parte de outro, ex.: <code>Renda Santa Helena</code>. O site agrupa os dois.</td></tr>
            <tr><td>Como o recurso chega ao cidadão?*</td><td>Um dos quatro tipos: <strong>Transferência de renda e benefícios sociais</strong> · <strong>Bolsas e auxílios individuais</strong> · <strong>Incentivos a produtores, empresas e entidades</strong> · <strong>Serviços e ações públicas</strong>. A explicação de cada um aparece no formulário.</td></tr>
        </table>

        <h3>Parte 2 — Explique para o cidadão</h3>
        <table class="tb">
            <tr><th style="width:230px">Campo</th><th>Como preencher</th></tr>
            <tr><td>O que é o programa?</td><td>Uma ou duas frases simples (veja a parte {{ $num('escrever') }}).</td></tr>
            <tr><td>Quem pode participar e como?</td><td>Quem tem direito, documentos e onde procurar. É o que o cidadão mais procura.</td></tr>
            <tr><td>Para quem é este programa?*</td><td>Marque <strong>todos</strong> os perfis atendidos. É assim que o programa aparece no filtro “Que programas existem para mim?” do site.</td></tr>
        </table>

        <h3>Parte 3 — Base legal</h3>
        <p>Clique em <span class="botao">Adicionar lei ou convênio</span> para cada norma: tipo (Lei Municipal, Decreto, Convênio…), número (ex.: <code>3.339</code>), ano e, se houver, o link para o texto. Em <strong>Ano de criação do programa</strong>, o ano da primeira lei — ele aparece na linha do tempo do site.</p>

        <h3>Parte 4 — Quem foi atendido</h3>
        <figure class="fig"><img src="{{ $img('07-parte-4-atendidos.png') }}" alt="Parte 4 do formulário" loading="lazy"><figcaption><b>Parte 4.</b> Quantidade e unidade (“estudantes”, “famílias”…) dos atendidos; benefícios pagos à parte, quando houver parcelas.</figcaption></figure>
        <div class="dica"><i>i</i><div><strong>Pessoas não são benefícios.</strong> 9 adolescentes que recebem 47 parcelas = 9 atendidos e 47 benefícios pagos. O site mostra os dois separados.</div></div>

        <h3>Parte 5 — Quanto custa</h3>
        <figure class="fig"><img src="{{ $img('06-parte-5-valor.png') }}" alt="Parte 5 do formulário" loading="lazy"><figcaption><b>Parte 5.</b> Num programa de vários anos (valor anualizado), informe o total e a vigência: o sistema mostra o valor anual que vai aparecer no site.</figcaption></figure>
        <table class="tb">
            <tr><th style="width:230px">Tipo de valor</th><th>Quando usar</th></tr>
            <tr><td>Valor do ano</td><td>O caso comum: quanto o programa custa no exercício.</td></tr>
            <tr><td>Valor anualizado</td><td>Programa de vários anos (ex.: subsídio de juros por 8 anos): valor total + vigência.</td></tr>
            <tr><td>Sem custo direto</td><td>Não há gasto do Município (crédito do Estado, atendimento da própria equipe).</td></tr>
        </table>
        <div class="atencao"><i>!</i><div>Digite os valores como no dia a dia: <code>1.234.567,89</code>, sem “R$”. Em <strong>De onde vem o dinheiro?</strong>, evite “Não informado”: a ficha fica pendente.</div></div>

        <h3>Parte 6 — Observações</h3>
        <ul>
            <li><strong>Nota para o cidadão</strong> — aparece no site. Para explicar algo nos números (ex.: “valor dividido por 10 anos”).</li>
            <li><strong>Observação interna</strong> — só o painel vê. Para dúvidas e conferências.</li>
        </ul>
    </section>

    {{-- 5 ================================================================= --}}
    <section class="cap" id="publicar">
        <div class="capTitulo"><b>{{ $num('publicar') }}</b><h2>Conferir e publicar</h2></div>
        <p class="capIntro">Com o rascunho salvo, confira como a ficha vai aparecer e publique. É você quem decide quando o programa entra no site.</p>
        <ol class="passos">
            <li><strong>Clique em Pré-visualizar</strong><small>No topo do formulário. Abre uma nova aba com a ficha exatamente como o cidadão vai ver, com a faixa amarela “Pré-visualização”. Só quem tem acesso ao painel abre esse endereço.</small></li>
            <li><strong>Ajuste o que precisar</strong><small>Volte à aba do painel, corrija e clique em <span class="botao">Salvar alterações</span>. Atualize a aba da pré-visualização para ver de novo.</small></li>
            <li><strong>Publique</strong><small><span class="botao verde">Publicar no site</span> → confirme. A janela lembra o que ainda falta preencher, se houver. A ficha entra no site na hora.</small></li>
        </ol>
        <figure class="fig"><img src="{{ $img('09-previa.png') }}" alt="Pré-visualização da ficha" loading="lazy"><figcaption><b>Pré-visualização.</b> A faixa amarela avisa que a ficha ainda não está no site; “Voltar ao painel” leva de volta ao formulário.</figcaption></figure>
        <figure class="fig estreita"><img src="{{ $img('08-publicar-confirmar.png') }}" alt="Confirmação de publicação" loading="lazy"><figcaption><b>Confirmação.</b> Nada é publicado sem este clique.</figcaption></figure>
        <h3>O resultado, no site</h3>
        <div class="figs2">
            <figure class="fig"><img src="{{ $img('14-site-ficha.png') }}" alt="Ficha publicada no site" loading="lazy"><figcaption><b>Ficha no site.</b> Valor, atendidos, média por atendido e posição entre os maiores programas são calculados automaticamente.</figcaption></figure>
            <figure class="fig celular"><img src="{{ $img('15-celular-ficha.png') }}" alt="Ficha no celular" loading="lazy"><figcaption><b>No celular</b>, a mesma ficha — a maioria dos cidadãos acessa assim.</figcaption></figure>
        </div>
    </section>

    {{-- 6 ================================================================= --}}
    <section class="cap" id="escrever">
        <div class="capTitulo"><b>{{ $num('escrever') }}</b><h2>Como escrever para o cidadão</h2></div>
        <p class="capIntro">Escreva como se explicasse o programa a um vizinho: quem não trabalha na Prefeitura precisa entender na primeira leitura.</p>
        <table class="tb">
            <tr><th>Evite</th><th>Prefira</th></tr>
            <tr><td style="font-weight:400">“Subsídio de juros ordinários do Plano Safra – PRONAF, limitado a 7% a.a.”</td><td>“A Prefeitura paga parte dos juros do financiamento rural do Pronaf, até 7% ao ano.”</td></tr>
            <tr><td style="font-weight:400">“Política pública de fomento à educação com concessão de bolsas”</td><td>“Bolsas para alunos do Ensino Fundamental fazerem esporte, cultura e reforço escolar.”</td></tr>
            <tr><td style="font-weight:400">“Conforme legislação vigente”</td><td>O número da lei, na parte 3 (Base legal).</td></tr>
            <tr><td style="font-weight:400">“Procurar a SMAAR”</td><td>“Procure a Secretaria de Agricultura, Rua Paraguai, 1401, fone (45) 3268-8200.”</td></tr>
        </table>
        <ul>
            <li><strong>Frases curtas</strong>, de até duas linhas.</li>
            <li><strong>Sem siglas</strong> sem explicação: escreva o nome por extenso na primeira vez.</li>
            <li><strong>Diga o benefício concreto:</strong> o que a pessoa recebe, quanto e com que frequência.</li>
            <li><strong>Em “Quem pode participar e como?”</strong>, diga quem tem direito, quais documentos levar e onde ir.</li>
            <li><strong>Informe o valor exato do ano;</strong> o site faz o resumo (ex.: R$ 1,03 milhão).</li>
        </ul>
    </section>

    {{-- 7 ================================================================= --}}
    <section class="cap" id="atualizar">
        <div class="capTitulo"><b>{{ $num('atualizar') }}</b><h2>Atualizar um programa publicado</h2></div>
        <p class="capIntro">Em programa publicado, <strong>o que você salva aparece no site na hora</strong>. Um aviso azul no topo do formulário lembra disso.</p>
        <figure class="fig"><img src="{{ $img('10-publicado-topo.png') }}" alt="Programa publicado no painel" loading="lazy"><figcaption><b>Programa publicado.</b> Aviso azul, botão <b>Ver no site</b> e, no menu ⋮, <b>Copiar para outro exercício</b> e <b>Tirar do site</b>.</figcaption></figure>
        <ol class="passos">
            <li><strong>Abra o programa</strong><small><span class="menu">Programas</span> → <span class="botao">Editar</span> na linha do programa.</small></li>
            <li><strong>Altere e confira antes de salvar</strong><small>Tudo o que estiver no formulário vai ao site no próximo clique em salvar.</small></li>
            <li><strong>Salve</strong><small><span class="botao">Salvar alterações</span> → “Alterações salvas e já publicadas no site”. Para conferir, <span class="botao">Ver no site</span>.</small></li>
        </ol>
        <div class="dica"><i>i</i><div><strong>Mudança grande?</strong> Use o menu ⋮ → <span class="botao">Tirar do site</span>: o programa volta a rascunho, você ajusta com calma, confere na pré-visualização e publica de novo.</div></div>
        @if (file_exists(public_path('manual-img/11-historico.png')))
            <figure class="fig"><img src="{{ $img('11-historico.png') }}" alt="Histórico de alterações" loading="lazy"><figcaption><b>Histórico de alterações</b>, no fim da página do programa: quem mudou o quê e quando. “Detalhes” mostra o antes e o depois.</figcaption></figure>
        @else
            <div class="nota"><i>=</i><div>Tudo fica no <strong>Histórico de alterações</strong>, no fim da página do programa: quem mudou o quê e quando. “Detalhes” mostra o antes e o depois.</div></div>
        @endif
        <div class="atencao"><i>!</i><div><strong>Excluir</strong> só é possível em rascunhos que nunca foram publicados. Um programa que já esteve no site pode ser retirado com “Tirar do site”, mas não excluído, para preservar o histórico.</div></div>
    </section>

    {{-- 8 ================================================================= --}}
    <section class="cap" id="fichas">
        <div class="capTitulo"><b>{{ $num('fichas') }}</b><h2>Fichas a completar</h2></div>
        <p class="capIntro">Uma ficha incompleta pode ser publicada, mas o cidadão vê “A informar” ou “Em atualização pela secretaria” onde falta informação. Cada pendência some assim que o campo é preenchido.</p>
        <figure class="fig"><img src="{{ $img('03-fichas-completar.png') }}" alt="Contadores do painel" loading="lazy"><figcaption><b>Contadores do painel.</b> “Fichas a completar” em vermelho indica trabalho pendente; a lista logo abaixo, no Painel de Controle, mostra o que falta em cada programa, com o botão <b>Completar</b>.</figcaption></figure>
        <table class="tb">
            <tr><th style="width:230px">Pendência</th><th>Onde resolver</th></tr>
            <tr><td>Descrição do programa</td><td>Parte 2 — O que é o programa?</td></tr>
            <tr><td>Como participar</td><td>Parte 2 — Quem pode participar e como?</td></tr>
            <tr><td>Base legal</td><td>Parte 3 — Adicionar lei ou convênio</td></tr>
            <tr><td>Público-alvo</td><td>Parte 2 — Para quem é este programa?</td></tr>
            <tr><td>Quantidade atendida</td><td>Parte 4 — atendidos ou benefícios pagos</td></tr>
            <tr><td>Valor</td><td>Parte 5 — valor no ano (ou total e vigência, se anualizado)</td></tr>
            <tr><td>Fonte do recurso</td><td>Parte 5 — De onde vem o dinheiro? (trocar “Não informado”)</td></tr>
        </table>
    </section>

    {{-- 9 ================================================================= --}}
    <section class="cap" id="virada">
        <div class="capTitulo"><b>{{ $num('virada') }}</b><h2>Virada de ano</h2></div>
        <p class="capIntro">Cada exercício tem seus próprios registros. Para o ano novo, você <strong>copia</strong> os programas do ano anterior e atualiza só os números.</p>
        <ol class="passos">
            <li><strong>Filtre o ano anterior</strong><small><span class="menu">Programas</span> → funil → <strong>Ano = 2025</strong>.</small></li>
            <li><strong>Selecione</strong><small>Marque a caixa no topo da lista (todos) ou só os programas que continuam.</small></li>
            <li><strong>Copie</strong><small><span class="botao">Copiar para outro exercício</span> → confirme o ano (ex.: 2026) → <span class="botao">Copiar</span>. Copiar de novo não duplica.</small></li>
            <li><strong>Complete e publique</strong><small>Filtre <strong>Ano = 2026</strong>. As cópias são rascunhos com descrição, leis, público e tipo já preenchidos; <strong>valor e atendidos vêm em branco</strong>. Informe os números, confira e publique.</small></li>
        </ol>
        <div class="figs2">
            <figure class="fig"><img src="{{ $img('12-lista-selecionada.png') }}" alt="Programas selecionados" loading="lazy"><figcaption><b>Seleção na lista.</b> Com programas marcados, aparece o botão “Copiar para outro exercício”.</figcaption></figure>
            <figure class="fig"><img src="{{ $img('13-copiar-exercicio.png') }}" alt="Copiar para outro exercício" loading="lazy"><figcaption><b>Copiar.</b> Valor e atendidos ficam em branco para os números do novo ano.</figcaption></figure>
        </div>
        <div class="nota"><i>=</i><div><strong>O site não muda de ano sozinho.</strong> Mesmo com programas de 2026 publicados, o cidadão continua vendo 2025 até o administrador trocar o exercício exibido — o que ele faz quando todas as secretarias terminarem. Os anos anteriores ficam no seletor “Exercício”, no topo do site.</div></div>
        <div class="atencao"><i>!</i><div>O <strong>ano de um programa não pode ser alterado</strong>. Errou o ano? Copie para o ano certo e exclua o rascunho errado (ou peça ao administrador, se ele já foi publicado).</div></div>
    </section>

    {{-- 10 ================================================================ --}}
    <section class="cap" id="duvidas">
        <div class="capTitulo"><b>{{ $num('duvidas') }}</b><h2>Dúvidas frequentes</h2></div>
        <div class="faq">
            <details open><summary>“Usuário ou senha incorretos”</summary><p>Confira se digitou o nome de usuário, não o e-mail. Se persistir, peça ao administrador uma nova senha.</p></details>
            <details><summary>Não vejo um programa da minha secretaria</summary><p>Confira a pesquisa e os filtros (funil), inclusive o ano. Você só vê programas da sua secretaria.</p></details>
            <details><summary>Salvei, mas o site não mudou</summary><p>Rascunho não aparece no site: clique em “Publicar no site”. Se o programa já está publicado, recarregue a página do site (Ctrl+F5).</p></details>
            <details><summary>“Esta secretaria já tem um programa com este nome neste exercício”</summary><p>O programa já existe naquele ano. Procure-o na lista e edite, em vez de criar outro.</p></details>
            <details><summary>O campo Ano está cinza</summary><p>É de propósito: o ano não muda depois de criado. Use “Copiar para outro exercício” (parte {{ $num('virada') }}).</p></details>
            <details><summary>O valor não aceita o que digitei</summary><p>Use o formato 1.234,56, sem “R$” e sem espaços.</p></details>
            <details><summary>O programa não aparece no filtro “para mim” do site</summary><p>Marque os perfis em “Para quem é este programa?” (parte 2 do formulário).</p></details>
            <details><summary>Preciso tirar um programa do site</summary><p>Abra o programa, menu ⋮ → “Tirar do site”. Ele volta a rascunho e pode ser publicado de novo.</p></details>
            <details><summary>Quem vê o que eu fiz?</summary><p>Qualquer pessoa com acesso ao programa no painel, no “Histórico de alterações”: cada mudança traz o nome, a data e o valor anterior e o novo.</p></details>
        </div>
    </section>

    <div class="rodapeManual">
        <span>Programas Municipais · Prefeitura Municipal de Santa Helena · PR</span>
        <span class="semImpressao"><a href="{{ url('/admin') }}">Ir para o painel →</a></span>
    </div>
</main>

<script>
    // Ao imprimir ou salvar em PDF, abre todas as perguntas.
    window.addEventListener('beforeprint', () => document.querySelectorAll('.faq details').forEach((d) => d.setAttribute('open', '')));
</script>
</body>
</html>
