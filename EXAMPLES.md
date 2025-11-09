# Kullanım Örnekleri / Usage Examples

[🇹🇷 Türkçe](#türkçe-örnekler) | [🇬🇧 English](#english-examples)

---

## Türkçe Örnekler

### 1. Basit Kullanım

En temel kullanım - sadece `disableOptionWhen` düzeltmesi:

```php
use Kapital\Filament\FormComponents\Components\Select;

Select::make('status')
    ->options([
        'draft' => 'Taslak',
        'published' => 'Yayında',
        'archived' => 'Arşivlendi',
    ])
    ->multiple()
    ->disableOptionWhen(fn ($value) => $value === 'archived')
```

### 2. Sistem Etiketlerini Devre Dışı Bırakma (Mevcut Kullanım Senaryosu)

Sipariş etiketleri - sistem etiketleri seçilemez/kaldırılamaz:

```php
use Kapital\Filament\FormComponents\Components\Select;
use App\Models\Tag;

Forms\Components\Select::make('tags')
    ->label('Etiketler')
    ->options(
        Tag::where('is_system', true)
            ->get()
            ->mapWithKeys(function ($tag) {
                return [$tag->id => $tag->name];
            })
    )
    ->disableOptionWhen(function ($value) {
        // Sistem etiketleri devre dışı - kullanıcı kaldıramaz
        return Tag::find($value)?->is_system ?? false;
    })
    ->multiple()
    ->searchable()
    ->preload()
```

### 3. Maksimum Seçim Limiti

Kullanıcı başına maksimum 3 rol seçilebilir:

```php
Select::make('roles')
    ->label('Roller')
    ->relationship('roles', 'name')
    ->multiple()
    ->maxSelectable(3)
    ->helperText('Maksimum 3 rol seçebilirsiniz.')
```

### 4. Dinamik Maksimum Limit (Closure ile)

Premium kullanıcılar 10 kategori, normal kullanıcılar 3 kategori seçebilir:

```php
Select::make('categories')
    ->label('Kategoriler')
    ->relationship('categories', 'name')
    ->multiple()
    ->maxSelectable(function () {
        return auth()->user()->isPremium() ? 10 : 3;
    })
    ->helperText(function () {
        $max = auth()->user()->isPremium() ? 10 : 3;
        return "Maksimum {$max} kategori seçebilirsiniz.";
    })
```

### 5. Minimum Seçim Zorunluluğu

En az 1 ödeme yöntemi seçimi zorunlu:

```php
Select::make('payment_methods')
    ->label('Ödeme Yöntemleri')
    ->options([
        'credit_card' => 'Kredi Kartı',
        'bank_transfer' => 'Havale/EFT',
        'cash' => 'Nakit',
    ])
    ->multiple()
    ->minSelectable(1)
    ->required()
    ->helperText('En az 1 ödeme yöntemi seçmelisiniz.')
```

### 6. Min ve Max Birlikte

2 ile 5 arası ürün seçimi:

```php
Select::make('products')
    ->label('Ürünler')
    ->relationship('products', 'name')
    ->multiple()
    ->minSelectable(2)
    ->maxSelectable(5)
    ->required()
    ->helperText('2 ile 5 arası ürün seçebilirsiniz.')
```

### 7. Dinamik Disable (Mevcut Seçimlere Göre)

İlk seçime göre bazı seçenekleri devre dışı bırakma:

```php
Select::make('options')
    ->options([
        'option_a' => 'Seçenek A',
        'option_b' => 'Seçenek B (A ile uyumsuz)',
        'option_c' => 'Seçenek C',
    ])
    ->multiple()
    ->reactive() // Önemli: değişiklikleri dinlemek için
    ->disableOptionWhen(function ($value, callable $get) {
        $currentSelections = $get('options') ?? [];

        // Eğer A seçiliyse, B devre dışı
        if (in_array('option_a', $currentSelections) && $value === 'option_b') {
            return true;
        }

        // Eğer B seçiliyse, A devre dışı
        if (in_array('option_b', $currentSelections) && $value === 'option_a') {
            return true;
        }

        return false;
    })
```

### 8. Admin'e Özel Özellikler

Admin kullanıcılar için farklı kurallar:

```php
Select::make('permissions')
    ->label('Yetkiler')
    ->relationship('permissions', 'name')
    ->multiple()
    ->maxSelectable(fn () => auth()->user()->isAdmin() ? null : 5)
    ->minSelectable(fn () => auth()->user()->isAdmin() ? 0 : 1)
    ->disableOptionWhen(function ($value) {
        // Admin'ler tüm yetkileri seçebilir
        if (auth()->user()->isAdmin()) {
            return false;
        }

        // Normal kullanıcılar tehlikeli yetkileri seçemez
        $dangerousPermissions = ['delete_users', 'manage_roles', 'system_settings'];
        return in_array($value, $dangerousPermissions);
    })
```

### 9. İlişkili Veriler ile Kullanım

Ürünlere göre varyantları filtreleme:

```php
Select::make('product_id')
    ->label('Ürün')
    ->options(Product::all()->pluck('name', 'id'))
    ->reactive()
    ->afterStateUpdated(fn (callable $set) => $set('variant_id', null)),

Select::make('variant_id')
    ->label('Varyant')
    ->options(function (callable $get) {
        $productId = $get('product_id');
        if (!$productId) {
            return [];
        }

        return ProductVariant::where('product_id', $productId)
            ->get()
            ->pluck('name', 'id');
    })
    ->multiple()
    ->maxSelectable(3)
    ->disabled(fn (callable $get) => !$get('product_id'))
    ->helperText('Önce bir ürün seçin, sonra maksimum 3 varyant seçebilirsiniz.')
```

### 10. Stok Durumuna Göre Disable

Stokta olmayan ürünleri devre dışı bırakma:

```php
use App\Models\Product;

Select::make('products')
    ->label('Ürünler')
    ->options(
        Product::all()->mapWithKeys(function ($product) {
            $label = $product->name;
            if ($product->stock <= 0) {
                $label .= ' (Stokta Yok)';
            }
            return [$product->id => $label];
        })
    )
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $product = Product::find($value);
        return $product && $product->stock <= 0;
    })
    ->maxSelectable(10)
    ->helperText('Stokta olmayan ürünler seçilemez.')
```

### 11. Tarih Tabanlı Disable

Süresi geçmiş kampanyaları devre dışı bırakma:

```php
use App\Models\Campaign;
use Carbon\Carbon;

Select::make('campaigns')
    ->label('Kampanyalar')
    ->options(
        Campaign::all()->mapWithKeys(function ($campaign) {
            $label = $campaign->name;
            if ($campaign->end_date < Carbon::now()) {
                $label .= ' (Süresi Doldu)';
            }
            return [$campaign->id => $label];
        })
    )
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $campaign = Campaign::find($value);
        return $campaign && $campaign->end_date < Carbon::now();
    })
```

### 12. Kategoriye Göre Gruplama ve Disable

```php
use App\Models\User;

Select::make('users')
    ->label('Kullanıcılar')
    ->options(function () {
        return User::all()->groupBy('role')->mapWithKeys(function ($users, $role) {
            return [
                $role => $users->pluck('name', 'id')->toArray()
            ];
        })->toArray();
    })
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $user = User::find($value);
        // Pasif kullanıcıları devre dışı bırak
        return $user && !$user->is_active;
    })
    ->searchable()
    ->preload()
```

---

## English Examples

### 1. Simple Usage

Most basic usage - just the `disableOptionWhen` fix:

```php
use Kapital\Filament\FormComponents\Components\Select;

Select::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ])
    ->multiple()
    ->disableOptionWhen(fn ($value) => $value === 'archived')
```

### 2. Disable System Tags (Current Use Case)

Order tags - system tags cannot be selected/removed:

```php
use Kapital\Filament\FormComponents\Components\Select;
use App\Models\Tag;

Forms\Components\Select::make('tags')
    ->label('Tags')
    ->options(
        Tag::where('is_system', true)
            ->get()
            ->mapWithKeys(function ($tag) {
                return [$tag->id => $tag->name];
            })
    )
    ->disableOptionWhen(function ($value) {
        // System tags are disabled - user cannot remove them
        return Tag::find($value)?->is_system ?? false;
    })
    ->multiple()
    ->searchable()
    ->preload()
```

### 3. Maximum Selection Limit

Maximum 3 roles per user:

```php
Select::make('roles')
    ->label('Roles')
    ->relationship('roles', 'name')
    ->multiple()
    ->maxSelectable(3)
    ->helperText('You can select up to 3 roles.')
```

### 4. Dynamic Maximum Limit (with Closure)

Premium users can select 10 categories, normal users can select 3:

```php
Select::make('categories')
    ->label('Categories')
    ->relationship('categories', 'name')
    ->multiple()
    ->maxSelectable(function () {
        return auth()->user()->isPremium() ? 10 : 3;
    })
    ->helperText(function () {
        $max = auth()->user()->isPremium() ? 10 : 3;
        return "You can select up to {$max} categories.";
    })
```

### 5. Minimum Selection Requirement

At least 1 payment method required:

```php
Select::make('payment_methods')
    ->label('Payment Methods')
    ->options([
        'credit_card' => 'Credit Card',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
    ])
    ->multiple()
    ->minSelectable(1)
    ->required()
    ->helperText('You must select at least 1 payment method.')
```

### 6. Min and Max Together

Select between 2 and 5 products:

```php
Select::make('products')
    ->label('Products')
    ->relationship('products', 'name')
    ->multiple()
    ->minSelectable(2)
    ->maxSelectable(5)
    ->required()
    ->helperText('Select between 2 and 5 products.')
```

### 7. Dynamic Disable (Based on Current Selections)

Disable options based on first selection:

```php
Select::make('options')
    ->options([
        'option_a' => 'Option A',
        'option_b' => 'Option B (incompatible with A)',
        'option_c' => 'Option C',
    ])
    ->multiple()
    ->reactive() // Important: to listen for changes
    ->disableOptionWhen(function ($value, callable $get) {
        $currentSelections = $get('options') ?? [];

        // If A is selected, disable B
        if (in_array('option_a', $currentSelections) && $value === 'option_b') {
            return true;
        }

        // If B is selected, disable A
        if (in_array('option_b', $currentSelections) && $value === 'option_a') {
            return true;
        }

        return false;
    })
```

### 8. Admin-Specific Features

Different rules for admin users:

```php
Select::make('permissions')
    ->label('Permissions')
    ->relationship('permissions', 'name')
    ->multiple()
    ->maxSelectable(fn () => auth()->user()->isAdmin() ? null : 5)
    ->minSelectable(fn () => auth()->user()->isAdmin() ? 0 : 1)
    ->disableOptionWhen(function ($value) {
        // Admins can select all permissions
        if (auth()->user()->isAdmin()) {
            return false;
        }

        // Normal users cannot select dangerous permissions
        $dangerousPermissions = ['delete_users', 'manage_roles', 'system_settings'];
        return in_array($value, $dangerousPermissions);
    })
```

### 9. Usage with Related Data

Filter variants by products:

```php
Select::make('product_id')
    ->label('Product')
    ->options(Product::all()->pluck('name', 'id'))
    ->reactive()
    ->afterStateUpdated(fn (callable $set) => $set('variant_id', null)),

Select::make('variant_id')
    ->label('Variant')
    ->options(function (callable $get) {
        $productId = $get('product_id');
        if (!$productId) {
            return [];
        }

        return ProductVariant::where('product_id', $productId)
            ->get()
            ->pluck('name', 'id');
    })
    ->multiple()
    ->maxSelectable(3)
    ->disabled(fn (callable $get) => !$get('product_id'))
    ->helperText('First select a product, then you can select up to 3 variants.')
```

### 10. Disable Based on Stock Status

Disable out-of-stock products:

```php
use App\Models\Product;

Select::make('products')
    ->label('Products')
    ->options(
        Product::all()->mapWithKeys(function ($product) {
            $label = $product->name;
            if ($product->stock <= 0) {
                $label .= ' (Out of Stock)';
            }
            return [$product->id => $label];
        })
    )
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $product = Product::find($value);
        return $product && $product->stock <= 0;
    })
    ->maxSelectable(10)
    ->helperText('Out of stock products cannot be selected.')
```

### 11. Date-Based Disable

Disable expired campaigns:

```php
use App\Models\Campaign;
use Carbon\Carbon;

Select::make('campaigns')
    ->label('Campaigns')
    ->options(
        Campaign::all()->mapWithKeys(function ($campaign) {
            $label = $campaign->name;
            if ($campaign->end_date < Carbon::now()) {
                $label .= ' (Expired)';
            }
            return [$campaign->id => $label];
        })
    )
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $campaign = Campaign::find($value);
        return $campaign && $campaign->end_date < Carbon::now();
    })
```

### 12. Grouping by Category and Disable

```php
use App\Models\User;

Select::make('users')
    ->label('Users')
    ->options(function () {
        return User::all()->groupBy('role')->mapWithKeys(function ($users, $role) {
            return [
                $role => $users->pluck('name', 'id')->toArray()
            ];
        })->toArray();
    })
    ->multiple()
    ->disableOptionWhen(function ($value) {
        $user = User::find($value);
        // Disable inactive users
        return $user && !$user->is_active;
    })
    ->searchable()
    ->preload()
```

---

## Notlar / Notes

### Performans İpuçları / Performance Tips

1. **Eager Loading Kullanın / Use Eager Loading**
   ```php
   ->disableOptionWhen(function ($value) {
       // Kötü / Bad: N+1 sorgu problemi / N+1 query problem
       return Tag::find($value)?->is_system;

       // İyi / Good: Önce tüm kayıtları yükle / Load all records first
       static $systemTags = null;
       if ($systemTags === null) {
           $systemTags = Tag::where('is_system', true)->pluck('id')->toArray();
       }
       return in_array($value, $systemTags);
   })
   ```

2. **Cache Kullanın / Use Caching**
   ```php
   ->disableOptionWhen(function ($value) {
       return Cache::remember('system_tags', 3600, function () {
           return Tag::where('is_system', true)->pluck('id')->toArray();
       })->contains($value);
   })
   ```

3. **Reactive Kullanırken Dikkat / Be Careful with Reactive**
   - `reactive()` her değişiklikte form'u yeniden render eder / re-renders form on every change
   - Sadece gerektiğinde kullanın / use only when necessary
   - Performans için `lazy()` kullanmayı düşünün / consider using `lazy()` for performance
