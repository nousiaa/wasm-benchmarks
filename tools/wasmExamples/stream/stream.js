"use strict";
const fs = require("node:fs");

WebAssembly.instantiate(fs.readFileSync("stream.wasm"), {
  env: {
    jsExampleFunc: (i) => {
      console.log("call from c: " + i);
    },
    jsSleep: async (i) => {
      const a = Date.now()
      while(Date.now()< a+i*1000);
    },
  },
}).then((wasmModule) => {
  console.log("Done " + wasmModule.instance.exports.stream());
});

//emcc  stream.c --no-entry -o stream.wasm  -sINITIAL_HEAP=128MB -O1 -sEXPORTED_FUNCTIONS=_stream -sERROR_ON_UNDEFINED_SYMBOLS=0
