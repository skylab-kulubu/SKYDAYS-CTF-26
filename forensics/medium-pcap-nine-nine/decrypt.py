from cryptography.hazmat.primitives import serialization, hashes
from cryptography.hazmat.primitives.asymmetric import padding

# 1. Key'i yükle
with open("key.pem", "rb") as key_file:
    private_key = serialization.load_pem_private_key(key_file.read(), password=None)

# 2. Şifreli veriyi (Wireshark'tan "Show Data as Raw" diyip hex olarak alıp byte'a çevirebilirler)
encrypted_data = bytes.fromhex("...") 

# 3. Decrypt et
plaintext = private_key.decrypt(
    encrypted_data,
    padding.OAEP(
        mgf=padding.MGF1(algorithm=hashes.SHA256()),
        algorithm=hashes.SHA256(),
        label=None
    )
)
print(plaintext)
