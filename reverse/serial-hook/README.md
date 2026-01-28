### Sorunun Çalışma Şekli

    * GetVolumeSerial fonksiyonu her bilgisayara özel olan bir HWID değeri veriyor, eğer bu değer programda verilen değere uyuyorsa flag veriliyor. Yoksa program kapanıyor.
    * Amaç bu özel HWID değerinin ne olduğunu bulup programa bu değerin verilmesini sağlayıp flaga ulaşmak.

### Çözüm Adımları

    * GetVolumeSerial fonksiyonu incelenir ve volumeSerialNumber değerinin nasıl alındığı öğrenilir (referans olarak MSDN kullanılır).
    * CheckHWID fonksiyonu incelenir ve saved_hwid arrayinin 0x134 ile xor edildiği fark edilip saved_hwid bulunur.
    * Programın genel çalışma şekli anlaşılıp aşağıdaki iki yöntemden biri ile alınan HWID değeri saved_hwid değeri olacak şekilde manipüle edilir.
    * İlk yöntem: GetVolumeInformationA fonksiyonunu hooklayıp return değerini istenen değerle değiştirmek
    * ikinci yöntem: Direkt GetVolumeSerial fonksiyonunu hooklayıp return değerini bulunan değerle değiştirmek
    * Hook DLL'i herhangi bir DLL Injector ile program üzerinde çalıştırılır ve manipüle edilen HWID değeri ile flaga ulaşılır.

### Verilebilecek İpuçları

    * Soru function hooking yapılarak çözülüyor.
    * GetVolumeInformationA hakkında bilgi almak için MSDN'e başvurulmalı.
    * Function hooking libraryleri kullanılması soru çözümünü büyük ölçüde kolaylaştırıyor. Diğer şekilde çok uğraştırır.

### Eksiklikler

    * Hooklamaya bulaşmadan direkt xor mantığı anlaşılıp flag elde edilebilir. Sorunun bu hali bunu engellemiyor. Yöntemini bulunca değiştireceğim.
    * std::stringstream pek iyi durmuyor. Daha basit yol bulursam değiştirebilirim.

### Not

    * solution.cpp'nin çalıştırılması için MinHook librarysi linklenip dosyanın DLL olarak buildlenmesi lazım.
