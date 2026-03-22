#!/usr/bin/env python3

def rotate_left(n, d):
    d %= 8
    return ((n << d) & 0xFF) | (n >> (8 - d))

def solve():
    xor_keys = [0x1a, 0x69, 0xe5, 0x02, 0x91, 0xde, 0x66, 0x53, 0x3e, 0x77, 0xd4, 0x6a, 0x89, 0x93, 0x64, 0x54]
    expected = [118, 147, 187, 61, 249, 53, 66, 235, 39, 136, 2, 28, 27, 249, 225, 23, 231, 47, 145, 45, 184, 180, 204, 57, 42, 74, 153, 79, 12, 217, 85, 47, 132]

    flag = ""
    prev = 0x53

    for i, target in enumerate(expected):
        key = xor_keys[i % 16]
        shift = (i % 3) + 1
        found = False

        for c_code in range(256):
            nc = (c_code ^ key ^ prev) & 0xFF
            res = rotate_left(nc, shift)
            res = (res + 0x42) & 0xFF

            if res == target:
                flag += chr(c_code)
                prev = target 
                found = True
                break

        if not found:
            flag += "?"

    print(f"Bulunan Flag: {flag}")

if __name__ == "__main__":
    solve()
