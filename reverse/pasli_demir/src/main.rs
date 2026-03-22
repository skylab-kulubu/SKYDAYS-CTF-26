use std::io::{self, Write};

fn main() {
    let mut out = io::stdout();

    let xor_keys = vec![0x1a, 0x69, 0xe5, 0x02, 0x91, 0xde, 0x66, 0x53, 0x3e, 0x77, 0xd4, 0x6a, 0x89, 0x93, 0x64, 0x54];
    
    let expected = vec![118, 147, 187, 61, 249, 53, 66, 235, 39, 136, 2, 28, 27, 249, 225, 23, 231, 47, 145, 45, 184, 180, 204, 57, 42, 74, 153, 79, 12, 217, 85, 47, 132];

    write!(out, "Flag'ı giriniz: ").unwrap();
    out.flush().unwrap();

    let mut s = String::new();
    io::stdin().read_line(&mut s).expect("Hata!");
    let inp = s.trim();

    let mut result = Vec::new();
    let mut prev = 0x53; // Başlangıç değeri (IV)

    for (idx, &c) in inp.as_bytes().iter().enumerate() {
        let mut nc = c ^ xor_keys[idx % 16] ^ prev;
        
        let shift = ((idx % 3) + 1) as u32;
        nc = nc.rotate_left(shift);
        
        nc = nc.wrapping_add(0x42);

        result.push(nc);
        prev = nc;
    }

    if expected == result {
        println!("Tebrikler, paslanmamışsınız!");
    } else {
        println!("Hatalı flag, tekrar dene.");
    }
}
