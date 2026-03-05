<?php

namespace App\Filament\Pages;

use App\Models\Invitation;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InvitationRegister extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.pages.invitation-register';

    protected static ?string $title = 'Welcome! Please complete the form below';

    public ?Invitation $invitation = null;

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(string $token): void
    {
        $this->invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();
        $this->form->fill([
            'email' => $this->invitation->email,
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->confirmed()
                    ->minLength(8),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function register()
    {
        $validated = $this->form->getState();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $this->invitation->email,
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('model_has_roles')->insert([
            'model_id' => $user->id,
            'role_id' => $this->invitation->role,
            'model_type' => User::class
        ]);

        if (! empty($this->invitation->circuits)) {
            $user->circuits = array_map('intval', (array) $this->invitation->circuits);
        }

        if (! empty($this->invitation->districts)) {
            $user->districts = array_map('intval', (array) $this->invitation->districts);
        }

        if (! empty($this->invitation->societies)) {
            $user->societies = array_map('intval', (array) $this->invitation->societies);
        }
        $user->save();

        $this->invitation->update([
            'accepted_at' => now(),
        ]);

        auth()->login($user);

        return redirect()->route('filament.admin.pages.dashboard');
    }
}