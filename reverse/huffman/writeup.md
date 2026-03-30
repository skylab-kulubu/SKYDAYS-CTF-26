# Huffman - Writeup

Bu writeup, verilen Rust programının çalışma mantığını ve flag/secret elde etme yöntemini açıklamaktadır.

---

## 1. Kodun Genel Yapısı

Programın amacı bir **fake shell** simülasyonu oluşturmak ve kullanıcı komutlarını Huffman kodlamasıyla okutmak.

* Karakterler için rastgele ağırlıklar atanır (`HufKey::rand`).
* Huffman ağacı `build_huffman` ile oluşturulur.
* Kullanıcı binary input (`0`/`1`) verir.
* `decode_bits` binary stringleri çözümler ve komut olarak shell’e yollar.
* `run_fake_shell` belirli komutları işler (`flag`, `secret`, `whoami`, `echo`, vs.).

---

## 2. Huffman Ağacı ve Binary Input

### Huffman Ağacı

* Her karakterin bir ağırlığı vardır ve Huffman algoritmasıyla ağacın yapısı belirlenir.
* Küçük ağırlıklı karakterler daha kısa kodlara, büyük ağırlıklı karakterler daha uzun kodlara sahip olur.
* Rust’taki öncelik sırası (`Ord` ve tie-break mantığı) node sıralamasını belirler: ağırlık öncelikli, eşit ise Leaf < Node, eşit ise karakter sırası.

### Binary Input

* Kullanıcı sadece `0` ve `1` girebilir.
* `decode_bits` fonksiyonu, binary stringi Huffman ağacı üzerinde gezdirerek karakterleri çözümler.
* `1` → sol, `0` → sağ. Leaf node bulunduğunda karakter output’a eklenir ve köke geri dönülür.
* Çözülemeyen bir binary string girildiğinde işlem hata verir.

---

## 3. Komut İşleme

* Huffman ile çözümlenen stringler shell komutu olarak çalıştırılır.
* Önemli komutlar:

  * `flag` → flag.txt içeriğini döndürür.
  * `secret` → secret dosyası içeriğini döndürür.
  * `whoami` → kullanıcı adı döndürür.
  * `echo ...` → girilen metni geri döndürür.
  * Diğerleri → bilinmeyen komut mesajı gösterir.

---

## 4. Reverse Engineering Stratejisi

1. **Karakter Frekanslarını Kullan**

   * Programın başında verilen karakter frekans tablosunu al.
   * Huffman ağacı, bu frekans tablosuna göre tamamen deterministik şekilde kurulabilir.

2. **Huffman Ağacını Yeniden Kur**

   * Tüm karakterler ve ağırlıkları bilindiğinde, Rust’taki sıralama mantığı ile ağaç aynı şekilde yeniden oluşturulabilir.
   * Bu ağaç sayesinde her karakterin binary karşılığı çıkarılabilir.

3. **Binary Input Oluştur ve Komutları Çalıştır**

   * İlgili komutları (örn. `flag`, `secret`) ağacın kodlarıyla binary’ye çevir.
   * Bu binary inputları shell’e gönder ve çıktıyı al.

---

## 5. Özet

* Program, **Huffman ağacı kullanarak fake shell** simülasyonu yapıyor.
* Binary stringler Huffman ağacı üzerinden çözülerek shell komutlarına dönüştürülüyor.
* Reverse engineering mantığı: karakter frekans tablosunu kullanarak Huffman ağacını yeniden kurmak ve komutların binary karşılıklarını elde etmek.
* Bu yöntemle `flag` ve `secret` gibi komutların çıktısı elde edilebilir.

