<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManualTest extends TestCase
{
    public function test_manual_do_cadastrador_abre_com_capitulos_e_telas(): void
    {
        $this->get('/manual')
            ->assertOk()
            ->assertSee('Manual do cadastrador')
            ->assertSee('href="#acesso"', false)
            ->assertSee('id="virada"', false)
            ->assertSee('Você confere e publica: rascunho não aparece no site')
            ->assertSee('manual-img/05-rascunho-topo.png', false)
            ->assertSee('window.print()', false)
            ->assertDontSee('Enviar para revisão');
    }

    public function test_login_do_painel_aponta_para_o_manual(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('href="/manual"', false);
    }
}
