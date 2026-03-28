# My Password Vault

---

**Kategori:** Reverse

**Zorluk:** Easy-Medium

---

## Çözüm

1. **Teknoloji Belirle:**
   
```text
DIE Engine kullanarak .exe dosyasının hangi teknoloji ile yazıldığını bulalım.
```

![Bayrak dnspy_1](images/die.png)

2. **İçeriği Oku:**
   
```text
.NET kullanıldığını öğrendik. DnSpy kullanarak içeriğie bakmayı deneyebiliriz.
```

![Bayrak dnspy_1](images/dnspy_1.png)

3. **Dosyayı Aç:**
   
```text
DnSpy ile içeriği görüntülüyemediğimize göre .exe dosyasını açmamız lazım. Bu işlem için SingleFileExtractor'ı kullanabiliriz.
```

```sh
dotnet tool install -g sfextract
sfextract .\amd-my-password-vault.exe --output tmp
```

![Bayrak dnspy_1](images/sfe.png)

4. **.dll Dosyasını İncele:**

![Bayrak dnspy_1](images/dnspy_2.png)

5. **Şifreyi Çöz:**
   
```text
Kullanıcıdan beklenen input'un base64 ile şifrelenmiş halinin aioyS2EyUC12M20wZHcqOQ== olduğunu gördük. Şifreyi elde edelim.
```

![Bayrak dnspy_1](images/cyber_chef.png)

6. **Flag'i Al:**

![Bayrak dnspy_1](images/flag.png)
