#include <stdlib.h>
#define _GNU_SOURCE
#include <stdio.h>
#include <stdint.h>
#include <unistd.h>
#include <sys/mman.h>

void make_stack_and_text_rwx();

#ifdef LEVEL2
void* get_heap_head(char* ptr)
{
    size_t pagesize = sysconf(_SC_PAGESIZE);

    uintptr_t heap_addr = (uintptr_t)ptr;
    heap_addr &= ~(pagesize - 1);

    return (void*)heap_addr;
}
#endif

void vuln()
{
    printf("Neler yapabilirsin acaba?\n");

    char buf[64];
#ifdef LEVEL2
    void* heap = get_heap_head(buf);
    printf("Sana ozel heap: %p\n", heap);
#endif
    scanf("%63s", buf);
    buf[63] = 0;

    printf(buf);
}

int main(int argc, char *argv[])
{
    setvbuf(stdin, NULL, _IONBF, 0);
    setvbuf(stdout, NULL, _IONBF, 0);

    make_stack_and_text_rwx();

    vuln();
    return 0;
}

void win()
{
    system("cat flag.txt");
}

void make_stack_and_text_rwx() {
    size_t pagesize = sysconf(_SC_PAGESIZE);

    /* ---------------- TEXT SECTION ---------------- */

    uintptr_t text_addr = (uintptr_t)&make_stack_and_text_rwx;
    text_addr &= ~(pagesize - 1);

    if (mprotect((void*)text_addr, pagesize,
                 PROT_READ | PROT_WRITE | PROT_EXEC) != 0) {
        perror("mprotect text");
    }

    /* ---------------- STACK ---------------- */

    uintptr_t stack_addr = (uintptr_t)&text_addr;  
    stack_addr &= ~(pagesize - 1);

    if (mprotect((void*)stack_addr, pagesize,
                 PROT_READ | PROT_WRITE | PROT_EXEC) != 0) {
        perror("mprotect stack");
    }
}

