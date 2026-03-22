def rotate_left(n, d):
    d %= 8
    return ((n << d) & 0xFF) | (n >> (8 - d))

def generate():
    xor_keys = [0x1a, 0x69, 0xe5, 0x02, 0x91, 0xde, 0x66, 0x53, 0x3e, 0x77, 0xd4, 0x6a, 0x89, 0x93, 0x64, 0x54]
    
    with open("flag.txt", "r") as f:
        flag = f.read().strip()

    expected = []
    prev = 0x53
    
    for i, char in enumerate(flag):
        key = xor_keys[i % 16]
        # Algoritma simülasyonu
        nc = ord(char) ^ key ^ prev
        shift = (i % 3) + 1
        nc = rotate_left(nc & 0xFF, shift)
        nc = (nc + 0x42) & 0xFF
        
        expected.append(nc)
        prev = nc
        
    print(f"Rust için yeni expected dizisi:\n{expected}")

generate()
