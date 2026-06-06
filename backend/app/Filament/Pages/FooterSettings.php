<?php

namespace App\Filament\Pages;

use App\Models\HomepageSection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FooterSettings extends Page
{
    protected static ?string $navigationLabel = 'Footer settings';

    protected static ?string $title = 'Footer settings';

    protected static ?string $slug = 'footer-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 3;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $footer = HomepageSection::query()->where('section_key', 'footer')->first();
        $this->form->fill($footer?->content ?? []);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        HomepageSection::query()->updateOrCreate(
            ['section_key' => 'footer'],
            [
                'content' => $data,
                'is_active' => true,
                'sort_order' => 7,
            ],
        );

        Notification::make()
            ->title('Footer saved')
            ->success()
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Branding')
                ->schema([
                    Textarea::make('tagline')->rows(2)->columnSpanFull(),
                    Textarea::make('address')->rows(3)->columnSpanFull(),
                    TextInput::make('copyright'),
                    TextInput::make('locale')->label('Locale label'),
                    TextInput::make('currency')->label('Currency label'),
                ])
                ->columns(2),

            Section::make('Footer columns')
                ->schema([
                    Repeater::make('columns')
                        ->schema([
                            TextInput::make('title')->required(),
                            Repeater::make('links')
                                ->schema([
                                    TextInput::make('label')->required(),
                                    TextInput::make('href')->required()->placeholder('/about'),
                                    TextInput::make('badge')->placeholder('NEW'),
                                ])
                                ->columns(3)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Link')
                                ->defaultItems(0),
                        ])
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Column')
                        ->defaultItems(0),
                ]),

            Section::make('Legal links')
                ->schema([
                    Repeater::make('legal')
                        ->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('href')->required(),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Legal link')
                        ->defaultItems(0),
                ]),

            Section::make('Social links')
                ->schema([
                    Repeater::make('social')
                        ->schema([
                            TextInput::make('label')->required(),
                            TextInput::make('href')->required(),
                            TextInput::make('icon')->placeholder('instagram'),
                        ])
                        ->columns(3)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Social link')
                        ->defaultItems(0),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save footer')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }
}
