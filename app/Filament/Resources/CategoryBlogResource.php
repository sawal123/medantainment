<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\CategoryBlog;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryBlogResource\Pages;
use App\Filament\Resources\CategoryBlogResource\RelationManagers;
use App\Models\Category;

class CategoryBlogResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationGroup = 'Blog Posts';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
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
                        Forms\Components\TextInput::make('seo_title')
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
                                            \Filament\Notifications\Notification::make()
                                                ->title('Silakan isi Nama Kategori terlebih dahulu')
                                                ->warning()
                                                ->send();
                                            return;
                                        }

                                        $seo = \App\Services\GeminiService::generateSeo($name, $description ?? '');
                                        $set('seo_title', $seo['title']);
                                        $set('seo_description', $seo['description']);

                                        \Filament\Notifications\Notification::make()
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
