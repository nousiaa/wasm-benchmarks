"use strict";
const fs = require("node:fs");
const { argv } = require("node:process");

WebAssembly.instantiate(fs.readFileSync(argv[2])).then((wasmModule) => {
  console.log(wasmModule.instance.exports.wasm());
});
