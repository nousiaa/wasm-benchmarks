"use strict";
const { WASI } = require("wasi");
const fs = require("node:fs");

const originalEmit = process.emit;

// suppress wasi experimental warning
process.emit = function (name, data, ...args) {
  if (
    name === `warning` &&
    typeof data === `object` &&
    data.name === `ExperimentalWarning`
  ) {
    return false;
  }
  return originalEmit.apply(process, arguments);
};

const wasi = new WASI({
  version: "preview1",
  args: [],
  preopens: {},
});

(async () => {
  const wasm = await WebAssembly.compile(fs.readFileSync("hello.wasm"));
  const module = await WebAssembly.instantiate(wasm, wasi.getImportObject());
  await wasi.start(module);
})();
//emcc  hello.c -o hello.wasm  -sINITIAL_HEAP=128MB -O1
// check imports with ../../checkImports.sh hello.wasm