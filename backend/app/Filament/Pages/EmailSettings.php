<?php

namespace App\Filament\Pages;

use App\Services\Email\EmailSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class EmailSettings extends Page
{
    protected string $view = 'filament.pages.email-settings';

    protected static ?string $title = 'Email settings';

    protected static ?string $navigationLabel = 'SMTP settings';

    protected static ?string $slug = 'email-settings';

    protected static string|UnitEnum|null $navigationGroup = 'Email';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(EmailSettingsService $service): void
    {
        $this->data = $service->loadForForm();
        $this->form->fill($this->data);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sender')
                ->columns(2)
                ->schema([
                    TextInput::make('brand_name')
                        ->label('Brand name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('sender_name')
                        ->label('Sender name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('sender_email')
                        ->label('Sender email (from)')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->helperText('SMTP "from" address. Use the same mailbox you configure on your mail server.'),
                    TextInput::make('admin_email')
                        ->label('Admin notification email')
                        ->email()
                        ->maxLength(255)
                        ->helperText('Receives new order alerts (order_new_admin template). Often the same address as the sender email.'),
                    TextInput::make('reply_to')
                        ->label('Reply-to email')
                        ->email()
                        ->maxLength(255)
                        ->helperText('Optional. Replies go here instead of the sender address.'),
                    TextInput::make('support_email')
                        ->label('Support email')
                        ->email()
                        ->maxLength(255)
                        ->helperText('Shown in the email footer as the help contact.'),
                ]),
            Section::make('Branding')
                ->columns(2)
                ->schema([
                    Select::make('logo_mode')
                        ->label('Logo mode')
                        ->options([
                            'text' => 'Text logo (MyTerraBook)',
                            'image' => 'Image logo',
                        ])
                        ->required()
                        ->live(),
                    FileUpload::make('logo_url')
                        ->label('Logo image')
                        ->disk('public')
                        ->directory('email')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/gif',
                            'image/svg+xml',
                        ])
                        ->maxSize(2048)
                        ->visible(fn (Get $get): bool => $get('logo_mode') === 'image')
                        ->helperText('Used when logo mode is "Image". Recommended height ~36px.'),
                    TextInput::make('accent_color')
                        ->label('Accent / button color')
                        ->maxLength(7)
                        ->helperText('Hex color, e.g. #45a06a'),
                    TextInput::make('heading_color')
                        ->label('Heading color')
                        ->maxLength(7)
                        ->helperText('Hex color, e.g. #0f2036'),
                ]),
            Section::make('Footer')
                ->schema([
                    Textarea::make('footer_text')
                        ->label('Footer text')
                        ->rows(3),
                    Textarea::make('company_address')
                        ->label('Company address')
                        ->rows(3),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('email-settings-form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save settings')
                            ->submit('save')
                            ->action(null)
                            ->icon('heroicon-o-check')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(EmailSettingsService $service): void
    {
        $data = $this->form->getState();
        $service->save($data);

        $this->data = $service->loadForForm();
        $this->form->fill($this->data);

        Notification::make()
            ->title('Email settings saved')
            ->success()
            ->send();
    }
}
