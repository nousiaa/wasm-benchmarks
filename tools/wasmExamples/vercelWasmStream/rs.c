extern void jsSendData(int);
extern void jsAfter(void);
extern void jsBefore(void);
extern void jsWait(int);
void rs(int c)
{
    jsBefore();
    for(int i = 0; i<c; i++) {
        jsSendData(i);
        jsWait(1);
    }
    jsAfter();
}
// emcc  rs.c --no-entry -o rs.wasm  -sINITIAL_HEAP=96MB -O3 -sEXPORTED_FUNCTIONS=_rs -sERROR_ON_UNDEFINED_SYMBOLS=0 -sASYNCIFY -sASYNCIFY_IMPORTS=jsSendData -s ASYNCIFY_STACK_SIZE=290000