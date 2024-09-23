"use strict";
const fs = require("node:fs");

WebAssembly.instantiate(fs.readFileSync("sum.wasm")).then((wasmModule) => {
  console.log("1 + 2 = " + wasmModule.instance.exports.sum(1, 2));
});

//emcc  sum.c --no-entry -o sum.wasm  -sINITIAL_HEAP=128MB -O1 -sEXPORTED_FUNCTIONS=_sum
