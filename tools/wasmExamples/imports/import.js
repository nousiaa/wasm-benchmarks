"use strict";
const fs = require("node:fs");
let mem = null;

WebAssembly.instantiate(fs.readFileSync("import.wasm"), {
  env: {
    exampleFunc: () => {
      console.log("call from c");
    },
    getStr: (ptr) => {
      const memArr = new Uint8Array(mem.buffer);
      const str = new TextDecoder().decode(memArr.slice(ptr, memArr.indexOf(0,ptr)));
      process.stdout.write(str);
    },
  },
}).then((wasmModule) => {
  mem = wasmModule.instance.exports.memory;
  console.log("Done " + wasmModule.instance.exports.import());
});

//emcc  import.c --no-entry -o import.wasm  -sINITIAL_HEAP=128MB -O1 -sEXPORTED_FUNCTIONS=_import -sERROR_ON_UNDEFINED_SYMBOLS=0
