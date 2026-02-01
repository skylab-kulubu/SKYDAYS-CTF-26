import time
import random
import base64
from scapy.all import *
from cryptography.hazmat.primitives.asymmetric import rsa, padding
from cryptography.hazmat.primitives import serialization, hashes
from cryptography.hazmat.backends import default_backend

# ---------------------------------------------------------
# KONFİGÜRASYON VE TEMA AYARLARI
# ---------------------------------------------------------
OUTPUT_FILE = "b99_ytu_heist.pcap"
FLAG = "SKYDAYS{TARIHI_HAMAM_SOYGUNU_BASLADI}"

# IP Adresleri (B99 Temalı)
IP_JAKE = "192.168.99.1"  # Jake Peralta
IP_BOYLE = "192.168.99.2"  # Charles Boyle
IP_TERRY = "192.168.99.3"  # Terry Jeffords
IP_SERVER = "10.0.0.99"  # Evidence Server

# Paket listesi
packets = []


def generate_keys():
    """RSA Key çifti oluşturur."""
    private_key = rsa.generate_private_key(
        public_exponent=65537, key_size=2048, backend=default_backend()
    )
    public_key = private_key.public_key()

    # Private Key'i PEM formatına çevir (String olarak)
    pem_private = private_key.private_bytes(
        encoding=serialization.Encoding.PEM,
        format=serialization.PrivateFormat.PKCS8,
        encryption_algorithm=serialization.NoEncryption(),
    )

    return private_key, public_key, pem_private


def rot13_encode(text):
    """Apply ROT13 encoding to text."""
    result = ""
    for char in text:
        if "a" <= char <= "z":
            result += chr((ord(char) - ord("a") + 13) % 26 + ord("a"))
        elif "A" <= char <= "Z":
            result += chr((ord(char) - ord("A") + 13) % 26 + ord("A"))
        else:
            result += char
    return result


def double_encode_key(pem_key_bytes):
    """Apply ROT13 then base64 encoding to private key."""
    rot13_encoded = rot13_encode(pem_key_bytes.decode())
    base64_encoded = base64.b64encode(rot13_encoded.encode()).decode()
    return base64_encoded


def create_noise():
    """Analizi zorlaştırmak için dummy trafik oluşturur."""
    print("[*] Gürültü (Dummy Traffic) oluşturuluyor...")
    noise_pkts = []

    # Terry yoğurt siparişi veriyor (HTTP Traffic)
    for _ in range(5):
        sport = random.randint(1024, 65535)
        p = (
            IP(src=IP_TERRY, dst="8.8.8.8")
            / UDP(sport=sport, dport=53)
            / DNS(rd=1, qd=DNSQR(qname="www.yogurt-lovers.com"))
        )
        noise_pkts.append(p)

    # Rastgele TCP el sıkışmaları
    for _ in range(10):
        sport = random.randint(1024, 65535)
        syn = IP(src=IP_JAKE, dst="1.1.1.1") / TCP(
            sport=sport, dport=80, flags="S", seq=1000
        )
        syn_ack = IP(src="1.1.1.1", dst=IP_JAKE) / TCP(
            sport=80, dport=sport, flags="SA", seq=2000, ack=1001
        )
        noise_pkts.append(syn)
        noise_pkts.append(syn_ack)

    return noise_pkts


def create_encrypted_flag_traffic(public_key):
    """Flag'i asimetrik şifreleyip ağ trafiğine gömer."""
    print("[*] Flag şifreleniyor ve paketleniyor...")

    encrypted_flag = public_key.encrypt(
        FLAG.encode(),
        padding.OAEP(
            mgf=padding.MGF1(algorithm=hashes.SHA256()),
            algorithm=hashes.SHA256(),
            label=None,
        ),
    )

    # Şifreli veri "Evidence Server"a gönderiliyor gibi yapalım
    # Port 9999 kullanıyoruz, dikkatli gözler için şüpheli port.
    sport = 4444
    dport = 9999

    # 3-Way Handshake
    syn = IP(src=IP_JAKE, dst=IP_SERVER) / TCP(
        sport=sport, dport=dport, flags="S", seq=100
    )
    syn_ack = IP(src=IP_SERVER, dst=IP_JAKE) / TCP(
        sport=dport, dport=sport, flags="SA", seq=500, ack=101
    )
    ack = IP(src=IP_JAKE, dst=IP_SERVER) / TCP(
        sport=sport, dport=dport, flags="A", seq=101, ack=501
    )

    # PSH,ACK - Şifreli veri yükü
    payload_pkt = (
        IP(src=IP_JAKE, dst=IP_SERVER)
        / TCP(sport=sport, dport=dport, flags="PA", seq=101, ack=501)
        / Raw(load=encrypted_flag)
    )

    return [syn, syn_ack, ack, payload_pkt]


def create_leaked_key_conversation(pem_private_key):
    """Jake ve Boyle arasındaki konuşmayı ve Private Key sızıntısını oluşturur."""
    print("[*] Sızdırılmış Private Key senaryosu oluşturuluyor...")

    chat_packets = []
    sport = 33333
    dport = 12345  # Basit bir chat servisi portu gibi

    # El sıkışma
    chat_packets.append(
        IP(src=IP_JAKE, dst=IP_BOYLE)
        / TCP(sport=sport, dport=dport, flags="S", seq=1000)
    )
    chat_packets.append(
        IP(src=IP_BOYLE, dst=IP_JAKE)
        / TCP(sport=dport, dport=sport, flags="SA", seq=5000, ack=1001)
    )
    chat_packets.append(
        IP(src=IP_JAKE, dst=IP_BOYLE)
        / TCP(sport=sport, dport=dport, flags="A", seq=1001, ack=5001)
    )

    # Chat Akışı (Seq/Ack numaralarını basit tutuyoruz)
    messages = [
        (IP_JAKE, IP_BOYLE, "Boyle! Acil durum. Holt beni oldurecek."),
        (IP_BOYLE, IP_JAKE, "Ne oldu Jake? Yine mi Evidence odasini su basti?"),
        (
            IP_JAKE,
            IP_BOYLE,
            "Hayir, YTU Davutpasa kampusu icin hazirladigim verileri sifreledim ama anahtarı kaybettim. Otopsi doktoruyla önemli bir işim vardı.",
        ),
        (
            IP_BOYLE,
            IP_JAKE,
            "Ah Jake... Nikolaj bile anahtarini kaybetmez. Yedegi yok mu?",
        ),
        (
            IP_JAKE,
            IP_BOYLE,
            "Var! Buldum. Sana gonderiyorum, sakin kimseye gosterme. Nine-Nine yemini et!",
        ),
        (IP_BOYLE, IP_JAKE, "Nine-Nine! Gonder gelsin."),
        (IP_JAKE, IP_BOYLE, "Iste:\n" + double_encode_key(pem_private_key)),
        (IP_BOYLE, IP_JAKE, "Aldim Jake. Harikasin. Hadi gidip kibo yiyelim."),
    ]

    seq_j = 1001
    seq_b = 5001

    for src, dst, msg in messages:
        if src == IP_JAKE:
            pkt = (
                IP(src=src, dst=dst)
                / TCP(sport=sport, dport=dport, flags="PA", seq=seq_j, ack=seq_b)
                / Raw(load=msg)
            )
            chat_packets.append(pkt)
            seq_j += len(msg)
        else:
            pkt = (
                IP(src=src, dst=dst)
                / TCP(sport=dport, dport=sport, flags="PA", seq=seq_b, ack=seq_j)
                / Raw(load=msg)
            )
            chat_packets.append(pkt)
            seq_b += len(msg)

    return chat_packets


def main():
    # 1. Anahtarları oluştur
    priv_key_obj, pub_key_obj, priv_pem = generate_keys()

    # 2. Paket gruplarını oluştur
    noise_1 = create_noise()
    encrypted_traffic = create_encrypted_flag_traffic(pub_key_obj)
    noise_2 = create_noise()
    leaked_traffic = create_leaked_key_conversation(priv_pem)
    noise_3 = create_noise()

    # 3. Hepsini birleştir ve karıştır (Sırayı biraz koruyarak gerçekçilik katalım)
    # Şifreli trafik önce, sonra panikleyip key atma, aralarda gürültü.
    final_pcap = noise_1 + encrypted_traffic + noise_2 + leaked_traffic + noise_3

    # 4. Dosyayı yaz
    print(f"[*] Toplam {len(final_pcap)} paket oluşturuldu.")
    wrpcap(OUTPUT_FILE, final_pcap)
    print(f"[+] Başarılı! Dosya kaydedildi: {OUTPUT_FILE}")
    print(f"[+] Flag: {FLAG}")


if __name__ == "__main__":
    main()
