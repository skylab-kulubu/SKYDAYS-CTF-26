import time
import random
import uuid
from datetime import datetime, timedelta
from elasticsearch import Elasticsearch, helpers

print("Log Üretici Başlatıldı. Elasticsearch aranıyor...")

# EC2'nun kendisinden Docker'a vuracağımız için localhost kullanıyoruz
ES_HOST = "http://localhost:9200"
es = Elasticsearch([ES_HOST])

# Elasticsearch'ün uyanmasını bekleme döngüsü
for i in range(15):
    try:
        if es.ping():
            print("BAŞARILI: Elasticsearch hazır ve bağlantı kuruldu!")
            break
    except Exception:
        pass
    print(f"Elasticsearch henüz hazır değil. Bekleniyor... ({i+1}/15)")
    time.sleep(3)
else:
    print("KRİTİK HATA: Elasticsearch'e ulaşılamadı. İşlem iptal ediliyor.")
    exit(1)

INDEX_NAME = "internal-app-logs-2026"

if es.indices.exists(index=INDEX_NAME):
    es.indices.delete(index=INDEX_NAME)

IP_LIST = ["192.168.1.15", "10.0.5.22", "172.18.0.4", "45.33.22.11", "8.8.8.8", "114.114.114.114"]
ENDPOINTS = ["/api/v1/status", "/login", "/wp-admin", "/metrics", "/.env", "/graphql"]
USER_AGENTS = ["Mozilla/5.0", "curl/7.68.0", "python-requests/2.31.0", "masscan/1.3.2"]
MESSAGES_NOISE = [
    "Database connection pool expanding.",
    "User 'admin' failed to authenticate (Invalid Password).",
    "Memory usage spiked above 80%. Running garbage collection.",
    "Nginx routed request successfully."
]
RABBIT_HOLES = [
    "Attempted to use override code 'ADMIN_BYPASS_1' - INVALID CODE.",
    "Attempted to use override code 'ROOT_OVERRIDE_X' - INVALID CODE.",
    "[SECURITY_WARN] Possible SQL Injection detected in payload: ' OR 1=1 --",
    "Base64 decoded payload: VEVCUklLTEVSLCBCT1NBIFpBTUFOIEhBUkNBRElOIQ=="
]

def generate_random_log(timestamp):
    log_type = random.choices(["NOISE", "ATTACK", "RABBIT_HOLE"], weights=[70, 15, 15])[0]
    ip = random.choice(IP_LIST)
    
    if log_type == "NOISE":
        level = random.choices(["INFO", "WARN", "DEBUG"], weights=[80, 15, 5])[0]
        msg = random.choice(MESSAGES_NOISE)
    elif log_type == "ATTACK":
        level = "WARN"
        msg = f"[WAF_BLOCK] Malicious request blocked on endpoint: {random.choice(ENDPOINTS)} with UA: {random.choice(USER_AGENTS)}"
    else:
        level = "DEBUG"
        msg = random.choice(RABBIT_HOLES)

    return {
        "_index": INDEX_NAME,
        "_source": {
            "@timestamp": timestamp.isoformat(),
            "level": level,
            "service": random.choice(["nginx", "ai-agent", "db-monitor"]),
            "client_ip": ip,
            "message": msg,
            "trace_id": str(uuid.uuid4())[:8]
        }
    }

logs_to_insert = []
now = datetime.utcnow()

print("Kaotik loglar üretiliyor...")
for _ in range(1500):
    random_seconds_ago = random.randint(0, 3 * 24 * 60 * 60)
    log_time = now - timedelta(seconds=random_seconds_ago)
    logs_to_insert.append(generate_random_log(log_time))

# ALTIN İPUÇLARI (Oyuncuların bulması gereken asıl hedefler)
needle_time_1 = now - timedelta(minutes=45)
logs_to_insert.append({
    "_index": INDEX_NAME,
    "_source": {
        "@timestamp": needle_time_1.isoformat(),
        "level": "DEBUG",
        "service": "ai-agent",
        "client_ip": "127.0.0.1",
        "message": "[CORE_SYSTEM] AI Agent successfully resolved internal metadata service at http://aws-metadata.internal/latest/",
        "trace_id": "CTF-GOLD-1"
    }
})

needle_time_2 = now - timedelta(minutes=22)
logs_to_insert.append({
    "_index": INDEX_NAME,
    "_source": {
        "@timestamp": needle_time_2.isoformat(),
        "level": "WARN",
        "service": "ai-agent",
        "client_ip": "127.0.0.1",
        "message": "[DEV_ALERT] System started with DEBUG_MODE_OVERRIDE_77. SQL restrictions and security filters are temporarily DISABLED.",
        "trace_id": "CTF-GOLD-2"
    }
})

logs_to_insert.sort(key=lambda x: x["_source"]["@timestamp"])

print("Elasticsearch'e veri basılıyor (Bulk Insert)...")
helpers.bulk(es, logs_to_insert)
print(f"Başarılı! Toplam {len(logs_to_insert)} adet log {INDEX_NAME} indeksine aktarıldı.")
