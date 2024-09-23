// @ts-ignore
import wasm from "../wasm/allWASM/rs1.wasm?module";
import asyncifyThings from "./asyncifyThings";
export const config = {
  runtime: "edge",
};

export default async function handler(request: Request, event: Event) {
  let asyncifyClass;
  const encoder = new TextEncoder();
  let ctrl;
  const customReadable = new ReadableStream({
    start(controller) {
      ctrl = controller;
    },
  });
  const { exports } = (await WebAssembly.instantiate(wasm, {
    env: {
      jsBefore: () => {
        ctrl.enqueue(encoder.encode("Start"));
      },
      jsAfter: () => {
        ctrl.enqueue(encoder.encode("Done"));
        ctrl.close();
      },
      jsSendData: (i) => {
        asyncifyClass.runAsync(() => {
          ctrl.enqueue(encoder.encode(i + " "));
        });
      },
      jsWait: (i) => {
        const a = Date.now();
        while (Date.now() < a + i * 1000);
      },
    },
  })) as any;

  asyncifyClass = new asyncifyThings(exports);
  asyncifyClass.runWasm(exports.rs, [10]);
  return new Response(customReadable, {
    headers: { "Content-Type": "text/html; charset=utf-8" },
  });
}
