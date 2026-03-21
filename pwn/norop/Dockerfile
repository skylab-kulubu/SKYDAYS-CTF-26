FROM ubuntu:24.04

# 1. Gerekli tüm sistem kütüphanelerini ve socat'i kur
RUN apt update && \
    apt install -y libcapstone4 socat && \
    rm -rf /var/lib/apt/lists/*

# 2. Güvenlik için kullanıcı oluştur (Çakışmayı önlemek için ubuntu kullanıcısını siliyoruz)
RUN userdel -r ubuntu 2>/dev/null || true && \
    /usr/sbin/useradd --no-create-home -u 1000 user

# 3. Dosyaları kopyala (Klasör yapısını koruyarak)
WORKDIR /home/user/
COPY vuln /home/user/chal
COPY tracer /usr/local/bin/tracer
COPY flag.txt /home/user/
# chal dosyasının aradığı özel libc klasörünü de kopyalıyoruz
COPY ./libc /home/user/libc/

# 4. İzinleri ayarla
RUN chmod +x /home/user/chal /usr/local/bin/tracer

# 5. Çalıştırma Komutu
CMD ["socat", "TCP-LISTEN:1339,reuseaddr,fork", \
     "EXEC:/usr/local/bin/tracer /home/user/chal,pty,stderr"]
