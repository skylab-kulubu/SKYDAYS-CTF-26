# Hafif bir Python imajı kullanıyoruz
FROM python:3.9-slim

# Çalışma dizinini ayarlıyoruz
WORKDIR /app

# Önce gereksinimleri kopyalayıp kuruyoruz (Docker cache avantajı için)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Uygulama kodunu ve .env dosyasını kopyalıyoruz
COPY . .
# Flask'ın çalışacağı portu açıyoruz
EXPOSE 8080

# Uygulamayı başlatıyoruz
CMD ["python", "app.py"]

