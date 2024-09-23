#include <stdlib.h>
#include <string.h>
extern void exampleFunc(void);
extern void getStr(char *);
int import()
{
    char *aa = (char *)malloc(10);
    char str[] = "aaaaaaa\n";
    strncpy(aa, str, 9);

    exampleFunc();
    getStr(aa);
    return 0;
}