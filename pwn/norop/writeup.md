# norop - Writeup

## Overview

Bu challenge klasik bir memory corruption içermese de program bize çok güçlü iki primitive verir:

1. Arbitrary memory read
2. Arbitrary memory write

Programın menüsü şu şekildedir:

```
1) Read memory
2) Write memory
3) Exit
```

`read_mem()` fonksiyonu verilen adresten ham bellek okur:

```c
write(1, addr, size);
```

`write_mem()` fonksiyonu ise verilen adrese veri yazar:

```c
read(0, addr, size);
```

Yani kullanıcı herhangi bir adresten istediği kadar veri okuyabilir ve herhangi bir adrese istediği veriyi yazabilir.

Bu durum **tam anlamıyla arbitrary read / write primitive** sağlar.

Ancak challenge'ın önemli kısmı şudur: **shadow stack kullanıldığı için klasik ROP çalışmaz.**

Return address değiştirildiğinde program çöker. Bu nedenle çözümde ROP kullanılmaz.

Bunun yerine programın normal kapanış mekanizması hedef alınır.

---

# Programın Başlangıç Davranışı

Program başlarken şu bilgiyi verir:

```c
printf("main burada, gerisi sende: %p\n", &main);
```

Bu sayede `main` fonksiyonunun gerçek adresi elde edilir.

Binary PIE ile derlenmiş olduğu için bu bilgi kullanılarak **binary base address** hesaplanabilir:

```
binary_base = main_addr - main_offset
```

Binary base bilindiğinde:

* GOT
* .dynamic
* diğer section adresleri

hesaplanabilir.

---

# libc Leak

Arbitrary read primitive kullanılarak GOT üzerinden libc adresi sızdırılabilir.

Örneğin:

```
puts@GOT
```

Bu adres `puts` fonksiyonunun libc içindeki gerçek adresini içerir.

```
puts_leak = *(puts@got)
libc_base = puts_leak - offset(puts)
```

Bu işlem sonrası:

* libc base
* libc içindeki tüm fonksiyon adresleri

hesaplanabilir.

---

# Dynamic Linker (ld.so) Base Bulma

ROP kullanılamadığı için farklı bir kontrol akışı ele geçirme yöntemi gerekir.

Bu noktada **dynamic linker (ld-linux)** önemli hale gelir.

ELF binary içinde `.dynamic` section bulunur.

Bu section içinde `DT_DEBUG` isimli bir entry vardır.

```
DT_DEBUG -> r_debug
```

`r_debug` yapısı dynamic loader tarafından doldurulur ve process içindeki tüm loaded shared library'leri gösterir.

Bu yapı şu pointer'ı içerir:

```
r_debug->r_map
```

Bu pointer bir **link_map** listesinin başlangıcıdır.

`link_map` listesi şu bilgileri içerir:

* her shared library'nin base address'i
* library ismi
* sonraki library pointer'ı

Liste dolaşılarak `ld-linux` bulunabilir.

Bu sayede:

```
ld_base
```

hesaplanır.

---

# exit Handler Mekanizması

Program `exit()` çağrıldığında libc bazı handler fonksiyonlarını çalıştırır.

Bu handler'lar şu yapı içinde tutulur:

```
__exit_funcs
```

Bu yapı içinde bir fonksiyon listesi bulunur.

Her entry şu bilgileri içerir:

```
struct exit_function {
    long flavor
    void (*func)(void*)
    void *arg
}
```

Program sonlanırken libc bu fonksiyonları çağırır.

Eğer bu listeye yeni bir entry eklenirse program exit sırasında o fonksiyon çalıştırılır.

Bu teknik genellikle **exit handler hijacking** olarak bilinir.

---

# Pointer Mangling

Modern glibc sürümlerinde exit handler fonksiyon pointer'ları doğrudan saklanmaz.

Bunun yerine **pointer mangling** uygulanır.

Fonksiyon pointer şu şekilde encode edilir:

```
encoded = rol(ptr ^ guard, 0x11)
```

Burada:

* `guard` = pointer guard değeri
* `rol` = rotate left

Exit handler listesinde bulunan mevcut bir entry incelenerek:

* encoded pointer
* gerçek fonksiyon adresi

karşılaştırılır.

Dynamic linker içinde bulunan `_dl_fini` fonksiyonu bu hesaplamada kullanılır.

Bu iki değer bilindiğinde:

```
guard = ror(encoded, 0x11) ^ real_function
```

formülü ile pointer guard hesaplanabilir.

---

# Sahte Exit Handler Oluşturma

Guard değeri elde edildikten sonra istediğimiz fonksiyon encode edilebilir.

Hedef fonksiyon:

```
system
```

Argüman:

```
"/bin/sh"
```

Önce `system` pointer'ı encode edilir:

```
encoded_system = rol(system ^ guard, 0x11)
```

Daha sonra exit handler listesine yeni bir entry eklenir.

Entry şu şekilde olur:

```
flavor = 4
func   = encoded_system
arg    = "/bin/sh"
```

Ayrıca listedeki index değeri artırılır.

Böylece libc program exit sırasında bu handler'ı çalıştıracaktır.

---

# Exploit Trigger

Menüden `3) Exit` seçildiğinde program `main` döngüsünden çıkar ve normal şekilde sonlanır.

Program sonlanırken libc şu işlemi yapar:

```
run_exit_handlers()
```

Bu fonksiyon exit handler listesini dolaşır ve her fonksiyonu çağırır.

Eklediğimiz handler çalıştığında şu çağrı gerçekleşir:

```
system("/bin/sh")
```

Böylece shell elde edilir ve flag okunabilir.

---

# Özet

Bu challenge'da kullanılan teknikler:

* arbitrary memory read
* arbitrary memory write
* PIE base hesaplama
* GOT üzerinden libc leak
* `.dynamic` üzerinden `DT_DEBUG` analizi
* `link_map` traversal
* dynamic linker base leak
* glibc pointer mangling bypass
* exit handler hijacking

Shadow stack nedeniyle return address manipülasyonu mümkün değildir. Bu nedenle klasik ROP yerine programın kapanış mekanizması hedef alınarak kontrol akışı ele geçirilmiştir.

