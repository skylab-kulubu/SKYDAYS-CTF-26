# Paslı Demir Writeup - Rust XOR + S-box Challenge

Bu writeup, verilen Rust programının nasıl çalıştığını ve flag'i bulmak için tersine mühendislik mantığını açıklamaktadır.

---

## 1. Kodun Genel Yapısı

Programın temel işleyişi:

1. Kullanıcıdan flag girişi alınır.
2. Her karakter bir dizi işlemden geçirilir:

   * XOR işlemi
   * S-box dönüşümü
   * Bit rotasyonu
   * İndeks bazlı sabit toplama
3. İşlenmiş karakterler bir `result` dizisine eklenir.
4. `result`, önceden belirlenmiş `expected` dizisiyle karşılaştırılır.
5. Eşleşirse doğru flag; aksi halde hata mesajı verilir.

---

## 2. Kullanılan Sabitler

```
let xor_keys = vec![0x1a, 0x69, 0xe5, 0x02, 0x91, 0xde, 0x66, 0x53,
                    0x3e, 0x77, 0xd4, 0x6a, 0x89, 0x93, 0x64, 0x54];

let s_box: [u8; 16] = [0xc, 0x5, 0x6, 0xb, 0x9, 0x0, 0xa, 0xd,
                        0x3, 0xe, 0xf, 0x8, 0x4, 0x7, 0x1, 0x2];

let expected = vec![95, 149, 168, 69, 79, 184, 13, 51, 217, 44, 16, 182,
                    15, 28, 132, 156, 127, 41, 132, 102, 131, 61, 100, 192,
                    21, 157, 11, 246, 183, 253, 24, 18, 157];

let prev = 0xA5; // Başlangıç vektörü
```

* `xor_keys`: her karakter ile XOR yapılacak anahtar dizisi.
* `s_box`: 4-bitlik değerleri başka değerlere dönüştüren küçük bir şifreleme tablosu.
* `expected`: doğru flag işlendiğinde elde edilmesi gereken değerler.
* `prev`: zincirleme XOR için başlangıç vektörü.

---

## 3. Flag İşleme Adımları

Her karakter için işlemler şu sırayla uygulanır:

### a) XOR İşlemi

```
nc = c ^ xor_keys[idx % 16] ^ prev;
```

* Karakter `c`, diziden sıradaki anahtar ve önceki karakter (`prev`) ile XORlanır.
* Bu, basit bir **XOR zinciri** (chaining XOR) oluşturur.

### b) S-box Dönüşümü

```
let high = (nc >> 4) as usize;
let low = (nc & 0x0F) as usize;
nc = (s_box[high] << 4) | s_box[low];
```

* `nc` iki 4-bitlik parçaya ayrılır (high ve low nibble).
* Her parça `s_box` üzerinden dönüştürülür ve tekrar birleştirilir.
* Bu adım, **substitution (yer değiştirme)** sağlar.

### c) Bit Rotasyonu

```
let shift = ((idx % 5) + 1) as u32;
nc = nc.rotate_left(shift);
```

* Karakter 1’den 5’e kadar değişen bir sayıda sola döndürülür.
* Bu, bit seviyesinde **karışıklık** (diffusion) sağlar.

### d) İndeks Bazlı Sabit Toplama

```
nc = nc.wrapping_add(0x37 ^ (idx as u8));
```

* 0x37 ile indeks XOR’lanmış değer karaktere eklenir.
* `wrapping_add` 256 taşmasını modulo 256 olarak işler.

### e) Prev Güncellemesi

```
prev = nc;
```

* Zincirleme XOR için bir sonraki karakterin işlenmesinde kullanılır.

### f) Sonuç Diziye Ekleme

* `result.push(nc)` ile işlenen karakter `result` dizisine eklenir.

---

## 4. Kontrol

```
if expected == result {
    println!("Tebrikler, paslanmamışsınız!");
} else {
    println!("Hatalı flag, tekrar dene.");
}
```

* `result` dizisi ile `expected` dizisi karşılaştırılır.
* Eşleşirse doğru flag girilmiş olur.

---

## 5. Reverse Engineering Mantığı

Flag’i bulmak için yapmanız gerekenler:

1. `expected` dizisini alın.
2. Her işleme tersini uygulayın:

   * `wrapping_add` → `wrapping_sub`
   * `rotate_left` → `rotate_right`
   * S-box → ters S-box (inverse S-box)
   * XOR zincirini tersine çözmek için `prev` değerlerini dikkatlice geri takip edin
3. Bu işlemleri karakter karakter uygulayarak flag’i elde edin.

---

## 6. Özet

* Program, **XOR + S-box + rotasyon + sabit toplama** zinciri kullanarak flag doğruluyor.
* Reverse etmek için **her adımı geri almak** gerekiyor.
* Zincirleme XOR nedeniyle flag’i tersine çözmek biraz dikkat gerektiriyor; önceki karakterler doğru şekilde hesaplanmalı.

