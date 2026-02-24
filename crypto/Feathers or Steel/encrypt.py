import random
import string

def generate_key():
    pt_alphabet = list(string.ascii_uppercase)
    
    ct_alphabet = pt_alphabet.copy()
    random.shuffle(ct_alphabet)
    
    return dict(zip(pt_alphabet, ct_alphabet)), "".join(ct_alphabet)

def encrypt_text(plaintext, key_dict):
    ciphertext = ""
    for char in plaintext.upper():
        if char in key_dict:
            ciphertext += key_dict[char]
        else:
            
            ciphertext += char 
    return ciphertext

mapping_dict, ct_string = generate_key()

print(f"PT Alphabet: {string.ascii_lowercase}")
print(f"CT Alphabet: {ct_string}\n")

f = open("pt.txt")
pt =  f.read()
ct = encrypt_text(pt, mapping_dict)

print(f"Ciphertext:\n{ct}")
