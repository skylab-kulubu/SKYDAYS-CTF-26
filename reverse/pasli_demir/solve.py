#!/usr/bin/env python3
from itertools import product

xor_keys = [
    0x1a, 0x69, 0xe5, 0x02,
    0x91, 0xde, 0x66, 0x53,
    0x3e, 0x77, 0xd4, 0x6a,
    0x89, 0x93, 0x64, 0x54
]

expected = [
    73, 34, 203, 100, 208, 120, 83, 40,
    117, 64, 184, 89, 240, 111, 10, 11,
    231, 90, 136, 107, 227, 129, 97, 50,
    212, 40, 160, 241, 223, 239, 80, 226,
    118
]

def rotl8(x, r):
    r %= 8
    return ((x << r) | (x >> (8 - r))) & 0xff

def rotr8(x, r):
    r %= 8
    return ((x >> r) | (x << (8 - r))) & 0xff

def forward_transform(byte, idx):
    nc = byte ^ xor_keys[idx % 16]
    shift = nc & 0xfc

    if idx % 2 == 0:
        nc = rotl8(nc, shift)
    else:
        nc = rotr8(nc, shift)

    return nc

# Her pozisyon için tüm mümkün byte'ları bul
candidates = []

for idx, target in enumerate(expected):
    possible = []
    for b in range(256):
        if forward_transform(b, idx) == target:
            possible.append(b)

    if not possible:
        print(f"[!] No solution at index {idx}")
        exit()

    candidates.append(possible)
    print(f"[+] Index {idx}: {len(possible)} candidate(s)")

print("\n[+] Combining candidates...")

# Çok fazla kombinasyon çıkmasını önlemek için limit
MAX_SOLUTIONS = 1000
solutions_found = 0

for combo in product(*candidates):
    try:
        s = bytes(combo).decode()
    except:
        continue  # printable olmayanları at

    print("Possible flag:", s)
    solutions_found += 1

    if solutions_found >= MAX_SOLUTIONS:
        print("[!] Limit reached.")
        break

print(f"\nTotal printed solutions: {solutions_found}")
