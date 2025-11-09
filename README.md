# Kapital Filament Form Components

[🇹🇷 Türkçe](#türkçe) | [🇬🇧 English](#english)

---

## Türkçe

Filament form componentleri için geliştirilmiş özellikler: çoklu seçim, devre dışı seçenekler ve daha fazlası.

### Sorun

Filament v2'de `disableOptionWhen()` metodu tekli seçim (dropdown) için mükemmel çalışır, ancak **`multiple()` aktif edildiğinde çalışmaz**. Bunun nedeni:

1. Tekli seçim native HTML `<select>` elementi kullanır ve disabled attribute doğal olarak çalışır
2. Çoklu seçim daha iyi UX için Choices.js kütüphanesi kullanır
3. Filament'in `transformOptionsForJs()` metodu Choices.js'e sadece `label` ve `value` gönderir, **`disabled` durumunu göndermez**

Bu paket bu sorunu çözer ve ek özellikler ekler.

### Çözüm

Bu paket Filament'in Select componentini extend eder ve `transformOptionsForJs()` metodunu override ederek `disabled` özelliğini ekler. Choices.js zaten disabled seçenekleri desteklediği için ek JavaScript gerekmez.

### Kurulum

#### 1. composer.json'a Ekleyin

Bu yerel bir paket olduğu için root `composer.json` dosyasına ekleyin:

```json
{
    "require": {
        "kapital/filament-form-components": "@dev"
    }
}
```

#### 2. Composer'ı Güncelleyin

```bash
composer update
```

Paket Laravel'in package discovery mekanizması ile otomatik olarak yüklenecektir.

### Kullanım

#### Temel Kullanım (Drop-in Replacement)

Filament'in Select componentini Kapital'in Select'i ile değiştirin:

```php
// Önce
use Filament\Forms\Components\Select;

// Sonra
use Kapital\Filament\FormComponents\Components\Select;

// Kullanım tamamen aynı
Select::make('field_name')
    ->options([
        '1' => 'Seçenek 1',
        '2' => 'Seçenek 2',
        '3' => 'Seçenek 3',
    ])
    ->multiple()
    ->disableOptionWhen(fn ($value) => $value === '2')
```

**Artık `disableOptionWhen()` çoklu seçim ile mükemmel çalışıyor!**

### Özellikler

#### 1. Düzeltilmiş `disableOptionWhen()` Çoklu Seçim İle

Ana özellik - devre dışı seçenekler artık çoklu seçimde düzgün çalışıyor:

```php
Select::make('tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->disableOptionWhen(fn ($value) => Tag::find($value)?->is_system ?? false)
```

Devre dışı seçenekler soluk görünür ve seçilemez.

#### 2. Maksimum Seçilebilir Öğe (`maxSelectable`)

Seçilebilecek öğe sayısını sınırlayın:

```php
Select::make('categories')
    ->options([...])
    ->multiple()
    ->maxSelectable(3) // Maksimum 3 seçim yapılabilir
```

Özellikler:
- Backend validasyonu (aşılırsa form gönderilmez)
- Frontend validasyonu Choices.js ile (kullanıcı dostu geri bildirim)
- Closure desteği ile dinamik limitler

```php
->maxSelectable(fn () => auth()->user()->isPremium() ? 10 : 3)
```

#### 3. Minimum Seçilebilir Öğe (`minSelectable`)

Minimum sayıda seçim zorunluluğu:

```php
Select::make('required_tags')
    ->options([...])
    ->multiple()
    ->minSelectable(2) // En az 2 seçim gerekli
```

Özellikler:
- Backend validasyonu
- Closure desteği ile dinamik limitler

```php
->minSelectable(fn () => auth()->user()->isAdmin() ? 0 : 1)
```

#### 4. Tümünü Seç Seçeneği (`selectAllOption`)

"Tümünü Seç" butonu ekler. Tıklandığında disabled olmayan tüm seçenekleri otomatik seçer:

```php
Select::make('permissions')
    ->options([...])
    ->multiple()
    ->selectAllOption() // "Tümünü Seç" butonu ekler
    ->disableOptionWhen(fn($value) => $value === 'dangerous_permission') // Disabled olanlar seçilmez
```

Özellikler:
- Disabled seçenekleri otomatik filtreler
- Form alanının yanında hint action olarak görünür
- Closure desteği ile dinamik gösterim

```php
->selectAllOption(fn () => auth()->user()->isAdmin())
```

#### 5. Tümünü Temizle Seçeneği (`deselectAllOption`)

"Tümünü Temizle" butonu ekler. Tıklandığında tüm seçimleri kaldırır:

```php
Select::make('filters')
    ->options([...])
    ->multiple()
    ->deselectAllOption() // "Tümünü Temizle" butonu ekler
```

Özellikler:
- Tüm seçimleri bir tıkla temizler
- Kırmızı renkte ve X ikonu ile görünür
- Closure desteği ile dinamik gösterim

```php
->deselectAllOption(fn () => auth()->user()->canClearAll())
```

### Gerçek Dünya Örneği

```php
use Kapital\Filament\FormComponents\Components\Select;
use App\Models\Tag;

Select::make('tags')
    ->label('Sipariş Etiketleri')
    ->relationship('tags', 'name')
    ->multiple()
    ->disableOptionWhen(function ($value) {
        // Sistem etiketlerini devre dışı bırak - kaldırılamazlar
        return Tag::find($value)?->is_system ?? false;
    })
    ->selectAllOption() // "Tümünü Seç" butonu (disabled olanları atlar)
    ->deselectAllOption() // "Tümünü Temizle" butonu
    ->maxSelectable(5) // Sipariş başına maksimum 5 etiket
    ->minSelectable(1) // En az 1 etiket gerekli
    ->searchable()
    ->preload()
    ->helperText('Sistem etiketleri devre dışıdır ve değiştirilemez.')
```

### Tüm Özellikler Karşılaştırması

| Özellik | Filament v2 Select | Kapital Enhanced Select |
|---------|-------------------|------------------------|
| Tekli seçim + `disableOptionWhen()` | ✅ Çalışıyor | ✅ Çalışıyor |
| Çoklu seçim + `disableOptionWhen()` | ❌ Bozuk | ✅ Düzeltildi |
| `maxSelectable()` | ❌ Yok | ✅ Var |
| `minSelectable()` | ❌ Yok | ✅ Var |
| `selectAllOption()` | ❌ Yok | ✅ Var |
| `deselectAllOption()` | ❌ Yok | ✅ Var |
| Diğer tüm Filament özellikleri | ✅ | ✅ Tam uyumlu |

### Teknik Detaylar

#### Nasıl Çalışır

Düzeltme oldukça basit. Paket tek bir metodu override eder:

```php
protected function transformOptionsForJs(array $options): array
{
    return collect($options)
        ->map(fn ($label, $value): array => [
            'label' => $label,
            'value' => strval($value),
            'disabled' => $this->isOptionDisabled($value, $label), // Bu satır eklendi
        ])
        ->values()
        ->all();
}
```

Choices.js (Filament'in çoklu seçim için kullandığı kütüphane) `disabled` özelliğini doğal olarak destekler. Bunu options array'ine ekleyerek, devre dışı seçenekler otomatik olarak çalışır.

#### Validasyon

Tüm validasyon güvenlik için backend'de gerçekleşir:

- `maxSelectable()` bir `max:X` validasyon kuralı ekler
- `minSelectable()` bir `min:X` validasyon kuralı ekler
- Devre dışı seçenekler sunucu tarafında validate edilir (client-side bypass edilse bile gönderilemez)

Frontend özellikleri (Choices.js maxItemCount gibi) kullanıcı dostu geri bildirim sağlar ancak güvenlik için bunlara güvenilmez.

### Uyumluluk

- **Laravel**: 9.x, 10.x
- **Filament**: v2.x
- **PHP**: 8.0.2+

### Sorun Giderme

#### Devre dışı seçenekler hala seçilebiliyor

Doğru Select componentini import ettiğinizden emin olun:

```php
// Doğru
use Kapital\Filament\FormComponents\Components\Select;

// Yanlış
use Filament\Forms\Components\Select;
```

#### Composer paketi bulamıyor

Paketin doğru konumda olduğundan emin olun:
- Yol: `lib/filament-form-components/`
- Root `composer.json` dosyasında paket `require` bölümünde var
- `composer update` veya `composer dump-autoload` çalıştırın

#### Max/min validasyon çalışmıyor

Validasyon kuralları otomatik olarak eklenir. Kontrol edin:
1. `->multiple()` kullanıyorsunuz
2. Form validate ediliyor
3. Validasyon hataları için Laravel loglarını kontrol edin

---

## English

Enhanced Filament form components with extended functionality for multiple select, disabled options, and more.

### The Problem

In Filament v2, the `disableOptionWhen()` method works perfectly for single select dropdowns, but **fails to work when `multiple()` is enabled**. This is because:

1. Single select uses native HTML `<select>` elements where disabled attributes work natively
2. Multiple select uses Choices.js library for better UX
3. Filament's `transformOptionsForJs()` method only passes `label` and `value` to Choices.js, **omitting the `disabled` state**

This package fixes this issue and adds additional features for enhanced select components.

### The Solution

This package extends Filament's Select component and overrides the `transformOptionsForJs()` method to include the `disabled` property. Choices.js already supports disabled options natively, so no additional JavaScript is required.

### Installation

#### 1. Add to composer.json

Since this is a local package, add it to your root `composer.json`:

```json
{
    "require": {
        "kapital/filament-form-components": "@dev"
    }
}
```

#### 2. Update Composer

```bash
composer update
```

The package will be auto-discovered via Laravel's package discovery mechanism.

### Usage

#### Basic Usage (Drop-in Replacement)

Simply replace Filament's Select component with Kapital's Select:

```php
// Before
use Filament\Forms\Components\Select;

// After
use Kapital\Filament\FormComponents\Components\Select;

// Usage remains exactly the same
Select::make('field_name')
    ->options([
        '1' => 'Option 1',
        '2' => 'Option 2',
        '3' => 'Option 3',
    ])
    ->multiple()
    ->disableOptionWhen(fn ($value) => $value === '2')
```

**Now `disableOptionWhen()` works perfectly with `multiple()` enabled!**

### Features

#### 1. Fixed `disableOptionWhen()` with Multiple Select

The primary feature - disabled options now work correctly with multiple select:

```php
Select::make('tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->disableOptionWhen(fn ($value) => Tag::find($value)?->is_system ?? false)
```

Disabled options will appear grayed out and cannot be selected.

#### 2. Maximum Selectable Items (`maxSelectable`)

Limit the number of items that can be selected:

```php
Select::make('categories')
    ->options([...])
    ->multiple()
    ->maxSelectable(3) // Maximum 3 selections allowed
```

Features:
- Backend validation (prevents form submission if exceeded)
- Frontend validation via Choices.js (user-friendly feedback)
- Supports closures for dynamic limits

```php
->maxSelectable(fn () => auth()->user()->isPremium() ? 10 : 3)
```

#### 3. Minimum Selectable Items (`minSelectable`)

Require a minimum number of selections:

```php
Select::make('required_tags')
    ->options([...])
    ->multiple()
    ->minSelectable(2) // At least 2 selections required
```

Features:
- Backend validation
- Supports closures for dynamic limits

```php
->minSelectable(fn () => auth()->user()->isAdmin() ? 0 : 1)
```

#### 4. Select All Option (`selectAllOption`)

Adds a "Select All" button. When clicked, automatically selects all non-disabled options:

```php
Select::make('permissions')
    ->options([...])
    ->multiple()
    ->selectAllOption() // Adds "Select All" button
    ->disableOptionWhen(fn($value) => $value === 'dangerous_permission') // Disabled ones won't be selected
```

Features:
- Automatically filters out disabled options
- Appears as a hint action next to the field
- Supports closures for dynamic visibility

```php
->selectAllOption(fn () => auth()->user()->isAdmin())
```

#### 5. Deselect All Option (`deselectAllOption`)

Adds a "Deselect All" button. When clicked, clears all selections:

```php
Select::make('filters')
    ->options([...])
    ->multiple()
    ->deselectAllOption() // Adds "Clear All" button
```

Features:
- Clears all selections with one click
- Appears in red color with X icon
- Supports closures for dynamic visibility

```php
->deselectAllOption(fn () => auth()->user()->canClearAll())
```

### Real-World Example

```php
use Kapital\Filament\FormComponents\Components\Select;
use App\Models\Tag;

Select::make('tags')
    ->label('Order Tags')
    ->relationship('tags', 'name')
    ->multiple()
    ->disableOptionWhen(function ($value) {
        // Disable system tags - they cannot be removed
        return Tag::find($value)?->is_system ?? false;
    })
    ->selectAllOption() // "Select All" button (skips disabled ones)
    ->deselectAllOption() // "Clear All" button
    ->maxSelectable(5) // Maximum 5 tags per order
    ->minSelectable(1) // At least 1 tag required
    ->searchable()
    ->preload()
    ->helperText('System tags are disabled and cannot be changed.')
```

### All Features Comparison

| Feature | Filament v2 Select | Kapital Enhanced Select |
|---------|-------------------|------------------------|
| Single select with `disableOptionWhen()` | ✅ Works | ✅ Works |
| Multiple select with `disableOptionWhen()` | ❌ Broken | ✅ Fixed |
| `maxSelectable()` | ❌ Not available | ✅ Available |
| `minSelectable()` | ❌ Not available | ✅ Available |
| `selectAllOption()` | ❌ Not available | ✅ Available |
| `deselectAllOption()` | ❌ Not available | ✅ Available |
| All other Filament features | ✅ | ✅ Fully compatible |

### Technical Details

#### How It Works

The fix is remarkably simple. The package overrides a single method:

```php
protected function transformOptionsForJs(array $options): array
{
    return collect($options)
        ->map(fn ($label, $value): array => [
            'label' => $label,
            'value' => strval($value),
            'disabled' => $this->isOptionDisabled($value, $label), // Added this line
        ])
        ->values()
        ->all();
}
```

Choices.js (the library Filament uses for multiple selects) natively supports the `disabled` property. By including it in the options array, disabled options automatically work.

#### Validation

All validation happens on the backend for security:

- `maxSelectable()` adds a `max:X` validation rule
- `minSelectable()` adds a `min:X` validation rule
- Disabled options are validated server-side (cannot be submitted even if client-side is bypassed)

Frontend features (like Choices.js maxItemCount) provide user-friendly feedback but aren't relied upon for security.

### Compatibility

- **Laravel**: 9.x, 10.x
- **Filament**: v2.x
- **PHP**: 8.0.2+

### Troubleshooting

#### Disabled options still selectable

Make sure you're importing the correct Select component:

```php
// Correct
use Kapital\Filament\FormComponents\Components\Select;

// Wrong
use Filament\Forms\Components\Select;
```

#### Composer can't find the package

Ensure the package is in the correct location:
- Path: `lib/filament-form-components/`
- Root `composer.json` has the package in `require` section
- Run `composer update` or `composer dump-autoload`

#### Max/min validation not working

The validation rules are automatically added. Check:
1. You're using `->multiple()`
2. The form is being validated
3. Check Laravel logs for validation errors

---

## License

MIT

## Credits

Created by Kapital Online for internal use and open-sourced for the community.

## Contributing

This is an internal package, but suggestions and bug reports are welcome.
