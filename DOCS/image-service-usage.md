# ImageService Usage Guide

Il servizio [`ImageService`](../app/Services/ImageService.php) fornisce metodi riutilizzabili per la generazione di URL Glide per immagini provenienti sia da Twill Blocks che da FormBuilder standard.

## Architettura

## Metodi Disponibili

### 1. `buildImageData()`

Costruisce i dati completi dell'immagine con URL Glide, dimensioni e alt text.

```php
public function buildImageData(
    object $imageSource,      // Oggetto con metodi image() e imageAltText()
    $media,                   // Media object con pivot data
    string $role,             // Ruolo dell'immagine
    string $crop,             // Nome del crop
    ?string $configPath = 'twill.block_editor.crops'
): array
```

**Esempio con Block:**
```php
use App\Services\ImageService;

class TwillBlockService
{
    public function __construct(
        private ImageService $imageService
    ) {}

    private function formatBlock(Block $block): array
    {
        foreach ($block->medias as $media) {
            $role = $media->pivot->role;
            $crop = $media->pivot->crop;
            
            $imageData = $this->imageService->buildImageData(
                $block, 
                $media, 
                $role, 
                $crop
            );
        }
    }
}
```

### 2. `getImageForRole()`

Ottiene i dati di un'immagine specifica per ruolo e crop da un Model.

```php
public function getImageForRole(
    object $model,            // Model con relazione medias
    string $role,             // Ruolo dell'immagine
    string $crop = 'default', // Nome del crop
    ?string $configPath = 'twill.settings.crops'
): ?array
```

**Esempio con Model standard:**
```php
use App\Services\ImageService;
use App\Repositories\PageRepository;

class PageController extends Controller
{
    public function show(
        string $slug, 
        PageRepository $repository,
        ImageService $imageService
    ) {
        $page = $repository->forSlug($slug);
        
        // Ottieni l'immagine hero della pagina
        $heroImage = $imageService->getImageForRole(
            $page, 
            'hero', 
            'landscape'
        );
        
        // Ottieni l'immagine thumbnail
        $thumbnail = $imageService->getImageForRole(
            $page, 
            'thumbnail', 
            'square'
        );
        
        return Inertia::render('Page', [
            'page' => $page->toArray(),
            'heroImage' => $heroImage,
            'thumbnail' => $thumbnail,
        ]);
    }
}
```

### 3. `getAllImages()`

Ottiene tutte le immagini di un Model, raggruppate per ruolo e crop.

```php
public function getAllImages(
    object $model,
    ?string $configPath = 'twill.settings.crops'
): array
```

**Esempio:**
```php
use App\Services\ImageService;

class ArticleController extends Controller
{
    public function show(
        int $id,
        ImageService $imageService
    ) {
        $article = Article::with('medias')->findOrFail($id);
        
        // Ottieni tutte le immagini dell'articolo
        $images = $imageService->getAllImages($article);
        
        // Struttura risultante:
        // [
        //     'hero' => [
        //         'landscape' => ['src' => '...', 'width' => 1200, 'height' => 675, 'alt' => '...'],
        //         'portrait' => ['src' => '...', 'width' => 800, 'height' => 1200, 'alt' => '...']
        //     ],
        //     'thumbnail' => [
        //         'square' => ['src' => '...', 'width' => 400, 'height' => 400, 'alt' => '...']
        //     ]
        // ]
        
        return Inertia::render('Article', [
            'article' => $article->toArray(),
            'images' => $images,
        ]);
    }
}
```

### 4. `calculateImageDimensions()`

Calcola le dimensioni di output basate sul crop ratio e sui minValues configurati.

```php
public function calculateImageDimensions(
    ?int $originalCropWidth,
    ?int $originalCropHeight,
    ?array $minValues
): array
```

### 5. `buildGlideParams()`

Costruisce i parametri URL per Glide.

```php
public function buildGlideParams(
    ?int $outputWidth,
    ?int $outputHeight,
    ?int $originalCropX,
    ?int $originalCropY,
    ?int $originalCropWidth,
    ?int $originalCropHeight
): array
```

## Configurazione Crops

### Per Blocks (default)

I crops per i blocks sono configurati in [`config/twill.php`](../config/twill.php):

```php
'block_editor' => [
    'crops' => [
        'hero_image' => [
            'landscape' => [
                [
                    'name' => 'landscape',
                    'ratio' => 16/9,
                    'minValues' => [
                        'width' => 1200,
                    ],
                ],
            ],
        ],
    ],
],
```

### Per Models standard

Per immagini su Models standard, puoi creare una configurazione separata:

```php
// config/twill.php
'settings' => [
    'crops' => [
        'hero' => [
            'landscape' => [
                [
                    'name' => 'landscape',
                    'ratio' => 16/9,
                    'minValues' => [
                        'width' => 1920,
                    ],
                ],
            ],
        ],
        'thumbnail' => [
            'square' => [
                [
                    'name' => 'square',
                    'ratio' => 1,
                    'minValues' => [
                        'width' => 400,
                    ],
                ],
            ],
        ],
    ],
],
```

## Esempio Completo: Aggiungere Immagini a un Model

### 1. Definire i crops nel FormBuilder

```php
// app/Http/Controllers/Twill/ArticleController.php
use A17\Twill\Services\Forms\Fields\Medias;

public function form(Form $form): Form
{
    $form->add(
        Medias::make()
            ->name('hero')
            ->label('Hero Image')
            ->max(1)
    );
    
    $form->add(
        Medias::make()
            ->name('thumbnail')
            ->label('Thumbnail')
            ->max(1)
    );
    
    return $form;
}

public function getForm(TwillModelContract $model): Form
{
    $form = parent::getForm($model);
    
    $form->addFieldset(
        Fieldset::make()
            ->title('Images')
            ->id('images')
            ->fields([
                Medias::make()
                    ->name('hero')
                    ->label('Hero Image')
                    ->max(1),
            ])
    );
    
    return $form;
}
```

### 2. Configurare i crops

```php
// config/twill.php
'settings' => [
    'crops' => [
        'hero' => [
            'default' => [
                [
                    'name' => 'default',
                    'ratio' => 16/9,
                    'minValues' => [
                        'width' => 1920,
                    ],
                ],
            ],
        ],
    ],
],
```

### 3. Utilizzare ImageService nel Controller

```php
// app/Http/Controllers/ArticleController.php
use App\Services\ImageService;

class ArticleController extends Controller
{
    public function show(
        string $slug,
        ImageService $imageService
    ) {
        $article = Article::with('medias')
            ->whereHas('slugs', function($query) use ($slug) {
                $query->where('slug', $slug)->where('active', true);
            })
            ->firstOrFail();
        
        $heroImage = $imageService->getImageForRole(
            $article,
            'hero',
            'default',
            'twill.settings.crops'
        );
        
        return Inertia::render('Article', [
            'article' => $article->toArray(),
            'heroImage' => $heroImage,
        ]);
    }
}
```

## Output Formato

Tutti i metodi che restituiscono dati immagine forniscono questo formato:

```php
[
    'src' => 'https://example.com/img/uuid?w=1200&h=675&crop=0,0,1920,1080',
    'width' => 1200,
    'height' => 675,
    'alt' => 'Alternative text',
]
```

Questo formato è ottimizzato per il consumo frontend e include:
- **src**: URL Glide completo con parametri di crop e resize
- **width/height**: Dimensioni calcolate basate sui minValues configurati
- **alt**: Testo alternativo dell'immagine

## Best Practices

1. **Dependency Injection**: Inietta sempre [`ImageService`](../app/Services/ImageService.php) tramite constructor injection
2. **Config Path**: Usa `twill.block_editor.crops` per blocks, `twill.settings.crops` per models standard
3. **Eager Loading**: Carica sempre la relazione `medias` con `with('medias')` per evitare N+1 queries
4. **Null Handling**: `getImageForRole()` restituisce `null` se l'immagine non esiste, gestisci questo caso nel frontend

## Riferimenti

- [`ImageService`](../app/Services/ImageService.php) - Servizio principale
- [`TwillBlockService`](../app/Services/TwillBlockService.php) - Esempio di utilizzo con Blocks
- [`PageController`](../app/Http/Controllers/PageController.php) - Esempio di utilizzo nel controller
- [Twill Documentation - Media Library](https://twillcms.com/docs/media-library/)
- [Glide Documentation](http://glide.thephpleague.com/)
