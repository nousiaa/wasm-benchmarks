// @ts-ignore

export default class asyncifyThings {
  STACK_ADDR;
  ASYNCIFY_STACK_SIZE;
  wasmExports;
  waiting = false;
  mainFunction;
  params = [];
  constructor(exports, stackSize = 1024) {
    this.ASYNCIFY_STACK_SIZE = stackSize;
    this.wasmExports = exports;
    const view = new Int32Array(exports.memory.buffer);
    this.STACK_ADDR = exports.malloc(12 + this.ASYNCIFY_STACK_SIZE);
    view[this.STACK_ADDR >> 2] = this.STACK_ADDR + 12;
    view[(this.STACK_ADDR + 4) >> 2] =
      this.STACK_ADDR + 12 + this.ASYNCIFY_STACK_SIZE;
    view[(this.STACK_ADDR + 8) >> 2] = 0;
  }
  runWasm(mainFunction, params = []) {
    this.mainFunction = mainFunction;
    this.params = params;
    this.mainFunction(...this.params);
  }
  runAsync(callback) {
    if (!this.waiting) {
      this.waiting = true;
      this.wasmExports.asyncify_start_unwind(this.STACK_ADDR);
      callback();
      setTimeout(() => {
        this.wasmExports.asyncify_stop_unwind();
        this.wasmExports.asyncify_start_rewind(this.STACK_ADDR);
        this.mainFunction(...this.params);
      }, 0);
    } else {
      this.wasmExports.asyncify_stop_rewind();
      this.waiting = false;
    }
  }
}
