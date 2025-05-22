<?php
namespace App\Filament\Pages\Auth;

use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\HtmlString;

class Login extends \Filament\Pages\Auth\Login
{

   public function getHeading():HtmlString
    {
        return new HtmlString('
            <div class="flex justify-center mb-4">
                <img src="' . asset('img/login.jpg') . '" alt="Logo" style="width: 180px"  >
            </div>
        ');
    }
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
//                        GRecaptcha::make('captcha')
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
