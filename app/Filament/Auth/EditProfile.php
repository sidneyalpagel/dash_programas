<?php

namespace App\Filament\Auth;

use App\Support\NomeUsuario;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

/** Perfil com nome de usuário; e-mail opcional. */
class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                NomeUsuario::campo(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('E-mail (opcional)')
            ->helperText('Usado apenas para recuperar a senha.')
            ->email()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }
}
