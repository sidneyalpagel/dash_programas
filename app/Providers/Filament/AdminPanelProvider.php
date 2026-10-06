<?php

namespace App\Providers\Filament;

use App\Filament\Auth\EditProfile;
use App\Filament\Auth\Login;
use App\Filament\Widgets\FichasIncompletas;
use App\Filament\Widgets\GuiaRapido;
use App\Filament\Widgets\ResumoProgramas;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class)
            ->brandName('Programas Municipais · Painel')
            ->brandLogo(asset('img/logo-prefeitura-santa-helena.png'))
            ->darkModeBrandLogo(asset('img/logo-prefeitura-santa-helena-branco.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => self::paletaPrimaria(),
            ])
            ->maxContentWidth('7xl')
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                NavigationItem::make('Manual do cadastrador')
                    ->url('/manual', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedBookOpen)
                    ->sort(2),
                NavigationItem::make('Ver o site público')
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->sort(99),
            ])
            // Na tela de login, o caminho para o manual (como no painel de obras).
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn () => new HtmlString('<p style="text-align:center;font-size:.875rem;margin-top:1rem"><a href="/manual" style="color:var(--primary-600);font-weight:500">Manual do cadastrador</a></p>'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                ResumoProgramas::class,
                GuiaRapido::class,
                FichasIncompletas::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Paleta gerada a partir do azul do site, deslocada um tom: assim o tom 600,
     * usado nos botões, fica igual ao azul do site e aceita texto branco.
     *
     * @return array<int, string>
     */
    private static function paletaPrimaria(): array
    {
        $gerada = Color::hex('#0e6a9c');
        $tons = array_keys($gerada);
        $paleta = [];

        foreach ($tons as $indice => $tom) {
            $paleta[$tom] = $gerada[$tons[min($indice + 1, count($tons) - 1)]];
        }

        return $paleta;
    }
}
