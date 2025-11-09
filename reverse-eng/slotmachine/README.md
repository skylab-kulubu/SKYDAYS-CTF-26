# 🎰 Hilekârın Slot Makinesi

Hoş geldin! Bu makinede gerçek şans, zeka ve biraz da "hile" birleşiyor.  

---

## Görev

Başlangıç bakiyen: **100$**  
Hedefin: **1000$'a ulaşmak**  
Ama herkes şansla kazanamaz... Gerçek kazananlar, sistemin sırrını çözenlerdir.

---

## Oynanış

1. `slot.py` dosyasını yerel makinede çalıştır.
2. **Kullanıcı kodu**: İstediğin herhangi bir string gir.
3. Her turda **1-10$** arasında bahis yapabilirsin.
4. Slot makinesi senin girdiğin koda göre tamamen **deterministik** çalışır!
5. Eğer 1000$ bakiyeye ulaşırsan, flag'e de ulaşırsın.

---

## İpuçları & Notlar

- Şansına güvenerek oynayabilir veya kodu inceleyip *gerçek hileyi* keşfedebilirsin.
- Kasanın kilidini açmak için algoritmanın püf noktasını bulman gerekecek...

İyi şanslar!

## Çözüm 
 
### Sorunun Temel Mantığı
Normalde slot makinesi oyunları tamamen şansa dayalıdır.
Ama bu kodda bir detay var:
Oyun başlarken oyuncudan bir "kullanıcı kodu" istiyor.
Kodda şu satır var:
```python
seed_input = sum(ord(char) for char in isim)
seed_degeri = seed_input % 256
random.seed(seed_degeri)
```
Yani kullanıcının girdiği stringin her harfinin ASCII değeri toplanıyor.
Bu toplamın 256'ya bölümünden kalan, random.seed() fonksiyonunda kullanılıyor.
random.seed(seed_degeri): Python’daki "random" sayı üreticiye bir başlangıç noktası veriyor.
Seed aynıysa sonuçlar da aynı oluyor. Bu da algoritmayı tamamen deterministik yapıyor.

### 2. Nasıl çözülür, hangi adımları uygulamalı?

- **Çözüm 1:** Kodda geçen seed algoritmasını anlayan bir script ile (veya elle) bir seed bulabilirsiniz ki, bu seed sayesinde slotta arka arkaya jackpot yapıp kısa sürede $1000'a ulaşırsınız.
- **Çözüm 2:** ASCII toplamı belirli bir seed üretecek şekilde basit bir string oluşturulmalı. Örneğin, bir seed direkt olarak `"A"` (ASCII 65) veya `"AZ"` (65+90=155) gibi.

#### **En hızlı çözüm yöntemi:**
Aşağıdaki örnek kod, slot makinesinin algoritmasına göre kolayca jackpot yapan bir "kullanıcı kodu" bulmak için kullanılabilir:

```python
import random

def test_seed(seed_degeri):
    bakiye = 100
    random.seed(seed_degeri)
    for t in range(100):
        bahis = 10  # maksimum bahis
        bakiye -= bahis
        sonuclar = [random.randint(1, 9) for _ in range(3)]
        if sonuclar == [7, 7, 7]:
            bakiye += bahis * 100
        elif sonuclar[0] == sonuclar[1] or sonuclar[1] == sonuclar[2]:
            bakiye += bahis * 2
        if bakiye >= 1000:
            return True
    return False

for seed in range(256):
    if test_seed(seed):
        print("Kazanan seed:", seed)
        break
```

> Örneğin kodun çıktısı `Kazanan seed: 47` ise, ASCII toplamı **47** olan bir kullanıcı kodu girerek flag alınabilir.  
> Örnek kullanıcı kodu: `'/'` (ASCII 47), `'AB'` (65+66=131; yani seed değeri 131), veya kendi hesapladığın başka kombinasyon.

### 3. Son adımlar

- Kodun seed’i nasıl oluştuğunu görecek (ASCII toplamı % 256)
Kendi bilgisayarında bir Python scripti ile 0'dan 255'e kadar tüm seed değerlerini deneyen oyuncular, her seed ile slot algoritmasını simüle edecek, hangisi ilk birkaç spin’de $1000'a ulaşır bakacak.
Seed’i bulunca, ona karşılık gelen uygun bir string girerek (ör: tek karakterin ASCII’si o seed olsun, veya toplamları seed’i veren bir kelime), her çalıştırışında aynı şekilde kazanacak!

---
