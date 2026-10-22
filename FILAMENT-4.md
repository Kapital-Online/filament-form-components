# Filament 4 sürüm hattı

Bu değişiklik `codex/filament-4` dalında hazırlanır ve `3.x` bakım dalına PR ile alınır. İlk hedef etiket `v3.0.0` olur; etiket yalnız PR kabul edildikten sonra oluşturulur.

- PHP: 8.3 ve üzeri; doğrulama ortamı PHP 8.3.
- Filament: 4.x, Laravel: 11.28 ve üzeri 11.x.
- `main` ve mevcut 2.x etiketleri Filament 3 kullanan uygulamalar içindir. Eski etiketler değiştirilmez, geriye uyumluluk bu ayrı sürüm hattıyla korunur.
- Filament 4 uygulamaları PR kabul edilene kadar `dev-codex/filament-4` kullanabilir; Composer kilidi incelenen commit'i sabitler. Etiket sonrası `^3.0` aralığına geçilir.
- CI bu dalda Filament 4 / Laravel 11 / PHP 8.3'ü doğrular. Eski dalın Laravel 10/11 matrisi kendi dalında kalır.
- Laravel 11'in destek süresi bittiğinden çözümlemede kullanılan `--no-blocking` yalnız yerel/CI komutuna aittir. Kalıcı Composer ayarı eklenmez.

## Detay tablosu görünümü

`master-detail-index.blade.php`, kurulu Filament 4 tablo görünümünü temel alır ve her kaydın ardından açılır detay satırı ekler. Seçim, sayfalama ve action çizimi Filament 4 yapısını kullanır. Ayrıntılar düğmesi kayıt altındaki satırdadır. Filament güncellemelerinde bu görünüm upstream ile karşılaştırılmalıdır; KB'deki Livewire regresyonu detay açma/kapama ve seçim korumasını doğrular. Eski görünüm yorumları referans dosyasında saklanır.

Kompakt sütunlu görünümde `masterDetailLivewire(..., showToggleButton: false)` kullanılabilir. Bu durumda açma düğmesi uygulamanın kayıt aksiyonları arasında yer alır; kapalı kaydın altında boş ayrıntı satırı üretilmez. Varsayılan `true` mevcut düğmeli davranışı korur.
