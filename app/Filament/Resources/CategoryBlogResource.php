<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryBlogResource\Pages;
use App\Models\Category;
use App\Services\GeminiService;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryBlogResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Blog Posts';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kategori')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('SEO Settings')
                    ->description('Pengaturan Meta Tag untuk Optimasi Mesin Pencari (SEO)')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(255)
                            ->hint('Kosongkan untuk otomatisasi dari Nama')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generateSeo')
                                    ->icon('heroicon-m-sparkles')
                                    ->color('primary')
                                    ->tooltip('Sugestikan SEO dengan AI')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        $name = $get('name');
                                        $description = $get('description');

                                        if (empty($name)) {
                                            Notification::make()
                                                ->title('Silakan isi Nama Kategori terlebih dahulu')
                                                ->warning()
                                                ->send();

                                            return;
                                        }

                                        $seo = GeminiService::generateSeo($name, $description ?? '');
                                        $set('seo_title', $seo['title']);
                                        $set('seo_description', $seo['description']);

                                        Notification::make()
                                            ->title($seo['is_ai'] ? 'SEO berhasil disugestikan via AI!' : 'SEO berhasil disugestikan secara lokal')
                                            ->success()
                                            ->send();
                                    })
                            ),

                        Forms\Components\Textarea::make('seo_description')
                            ->label('Meta Description')
                            ->hint('Kosongkan untuk otomatisasi dari Deskripsi')
                            ->maxLength(500),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryBlogs::route('/'),
            'create' => Pages\CreateCategoryBlog::route('/create'),
            'edit' => Pages\EditCategoryBlog::route('/{record}/edit'),
        ];
    }
}
