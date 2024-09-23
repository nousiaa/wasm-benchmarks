extern void jsExampleFunc(int);
extern void jsSleep(int);
int stream()
{
    for (int i = 0; i < 10; i++)
    {
        jsExampleFunc(i);
        jsSleep(1);
    }

    return 0;
}