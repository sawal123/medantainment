<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Blog;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BlogResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BlogResource\RelationManagers;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;
    protected static ?string $navigationGroup = 'Blog Posts';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Konten Utama')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Judul')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255),

                                    Select::make('category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('tags')
                                        ->label('Tags')
                                        ->multiple()
                                        ->relationship('tags', 'name')
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255),
                                        ]),

                                    RichEditor::make('content')
                                        ->label('Konten')
                                        ->required()
                                        ->columnSpanFull(),
                                ])->columns(2),

                            Forms\Components\Section::make('SEO Settings')
                                ->description('Pengaturan Meta Tag untuk Optimasi Mesin Pencari (SEO)')
                                ->schema([
                                    TextInput::make('seo_title')
                                        ->label('SEO Title')
                                        ->maxLength(255)
                                        ->hint('Kosongkan untuk otomatisasi dari Judul')
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('generateSeo')
                                                ->icon('heroicon-m-sparkles')
                                                ->color('primary')
                                                ->tooltip('Sugestikan SEO dengan AI')
                                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                                    $title = $get('title');
                                                    $content = $get('content');

                                                    if (empty($title)) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Silakan isi Judul terlebih dahulu')
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }

                                                    $seo = \App\Services\GeminiService::generateSeo($title, $content ?? '');
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
                                        ->hint('Kosongkan untuk otomatisasi dari Konten')
                                        ->maxLength(500),
                                ])->columns(1),
                        ])->columnSpan(2),

                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Media & Publikasi')
                                ->schema([
                                    FileUpload::make('image')
                                        ->label('Gambar Utama')
                                        ->disk('public')
                                        ->directory('blog-images')
                                        ->image()
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([
                                            '16:9',
                                        ])
                                        ->nullable(),

                                    Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'draft' => 'Draft',
                                            'published' => 'Published',
                                            'scheduled' => 'Scheduled',
                                        ])
                                        ->default('draft')
                                        ->live()
                                        ->required(),

                                    Forms\Components\DateTimePicker::make('published_at')
                                        ->label('Tanggal Publikasi')
                                        ->visible(fn (Forms\Get $get) => $get('status') === 'scheduled')
                                        ->required(fn (Forms\Get $get) => $get('status') === 'scheduled'),

                                    Select::make('user_id')
                                        ->relationship('user', 'name')
                                        ->label('Penulis (Author)')
                                        ->default(auth()->id())
                                        ->disabled(fn () => auth()->check() && !auth()->user()->isAdmin())
                                        ->required(),
                                ]),

                            Forms\Components\Section::make('Statistik Artikel')
                                ->schema([
                                    Forms\Components\Placeholder::make('views_count')
                                        ->label('Total Tayangan')
                                        ->content(fn ($record) => $record ? \App\Models\Visitor::where('blog_id', $record->id)->count() . ' kali dibaca' : '0'),
                                ])->visible(fn ($record) => $record !== null),
                        ])->columnSpan(1),
                    ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('visitors_count')
                    ->counts('visitors')
                    ->label('Views')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                ->label('Filter Kategori')
                ->relationship('category', 'name'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->check() && !auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BlogResource\Widgets\BlogStatsOverview::class,
            BlogResource\Widgets\BlogViewsChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
