# whatlibc - Writeup

## Overview

Program kullanıcıdan bir girdi alıp bunu doğrudan `printf` fonksiyonuna veriyor:

```c
scanf("%63s", buf);
printf(buf);
```

Bu durum klasik bir **format string vulnerability** oluşturur. Kullanıcı tarafından verilen veri format string olarak yorumlandığı için hem bellek sızıntısı yapılabilir hem de belleğe yazma işlemi gerçekleştirilebilir.

Program içinde ayrıca şu fonksiyon bulunur:

```c
void win()
{
    system("cat flag.txt");
}
```

Amaç program akışını bu fonksiyona yönlendirmektir.

Program başlangıcında ayrıca stack ve text segmenti `mprotect` kullanılarak `RWX` yapılmaktadır. Bu durum exploitation sırasında yazılabilir bir `.text` segmenti elde etmemizi sağlar. Normalde modern binary'lerde `.text` yalnızca `RX` olur.

---

# Vulnerability

Zafiyet doğrudan şu satırdan kaynaklanır:

```c
printf(buf);
```

Kullanıcı girdisi format string olarak işlendiği için şu imkanları sağlar:

* `%p` ile stack üzerindeki adresleri sızdırma
* `%n`, `%hn`, `%hhn` ile belleğe yazma

Format string zafiyetlerinde `printf` fonksiyonunun önemli bir özelliği vardır: yazdırılan karakter sayısını `%n` ailesi ile verilen adrese yazabilir.

Bu sayede program belleğinde istediğimiz yerlere kontrollü değerler yazabiliriz.

---

# Program Akışı

`main` fonksiyonu şu akışı izler:

1. `make_stack_and_text_rwx()` çağrılır
2. `vuln()` çağrılır
3. program sonlanır

Disassembly incelendiğinde `main` içinde `vuln` çağrısı şu şekilde görünür:

```
.text:00000000000011CB    E8 90 01 00 00    call    vuln
```

Buradaki opcode'ları inceleyelim.

### call instruction

`E8` opcode'u x86_64 mimarisinde **relative call** instruction'ıdır.

Instruction formatı:

```
E8 <rel32>
```

`rel32` değeri hedef adresin mevcut instruction pointer'a göre offset'idir.

Yani gerçek hedef adres şu şekilde hesaplanır:

```
target = next_instruction + rel32
```

Bu nedenle yalnızca offset değiştirilerek call'ın hedefi farklı bir fonksiyona yönlendirilebilir.

---

# Binary Patch Mantığı

Amaç `call vuln` instruction'ını değiştirmektir.

Orijinal instruction:

```
E8 90 01 00 00
```

Bu instruction `vuln` fonksiyonuna gider.

Ancak binary içinde zaten istediğimiz fonksiyon olan `win` bulunmaktadır. Eğer bu call instruction'ının offset'i değiştirilirse program `vuln` yerine `win` fonksiyonunu çağıracaktır.

`.text` segmenti yazılabilir olduğu için format string kullanarak bu instruction'ın bazı byte'larını değiştirmek mümkündür.

---

# Format String ile Yazma

Format string exploitlerinde genellikle şu specifierlar kullanılır:

```
%n   -> 4 veya 8 byte yazma
%hn  -> 2 byte yazma
%hhn -> 1 byte yazma
```

Daha hassas kontrol elde etmek için genellikle `%hhn` kullanılır çünkü tek byte yazmayı sağlar.

`printf` şu mantıkla çalışır:

* o ana kadar yazdırılan karakter sayısını hesaplar
* `%n` gördüğünde bu değeri verilen adrese yazar

Örneğin:

```
%100c%10$hhn
```

100 karakter yazdırır ve 100 değerini verilen adrese tek byte olarak yazar.

Bu teknik kullanılarak `.text` segmentindeki instruction byte'ları tek tek değiştirilebilir.

---

# Adres Sızıntısı

Binary PIE ile derlenmiş olduğu için `.text` segmentinin gerçek adresi her çalıştırmada değişir.

Bu nedenle önce bir adres leak edilmelidir.

Format string kullanarak stack'teki pointerlar okunabilir:

```
%17$p
%18$p
%19$p
```

Stack üzerinde genellikle şu tür değerler bulunur:

* return address
* stack pointer
* libc adresleri

Bu değerlerden biri binary içindeki bir adrese işaret eder. Böylece PIE base hesaplanabilir.

PIE base bilindiğinde `.text` içindeki hedef instruction'ın gerçek adresi hesaplanabilir.

---

# Instruction Patch

Hedef instruction:

```
call vuln
```

Bu instruction'ın bulunduğu adres hesaplandıktan sonra format string ile birkaç byte değiştirilir.

Bu değişiklik sonrası program şu şekilde davranır:

```
call win
```

Yani `vuln` yerine `win` çağrılır.

`win` fonksiyonu şu komutu çalıştırır:

```c
system("cat flag.txt");
```

Dolayısıyla program akışı bu noktaya yönlendirildiğinde flag ekrana basılır.

---

# Sonuç

Bu challenge'da kullanılan temel teknikler:

* format string vulnerability
* stack adres sızıntısı
* PIE bypass
* format string ile `.text` segmenti patchleme
* call instruction manipulation

`.text` segmentinin yazılabilir olması exploitation'ı önemli ölçüde kolaylaştırır. Format string kullanılarak doğrudan binary içindeki instruction'lar değiştirilir ve program akışı `win()` fonksiyonuna yönlendirilir.

