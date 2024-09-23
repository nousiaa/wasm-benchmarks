FROM ubuntu:22.04
RUN mkdir /install && cd /install
WORKDIR /install
RUN apt update
RUN apt -y install  git curl xz-utils python3 llvm clang
RUN curl https://wasmtime.dev/install.sh -sSf | bash
RUN curl -sSf https://raw.githubusercontent.com/WasmEdge/WasmEdge/master/utils/install.sh | bash
RUN curl https://get.wasmer.io -sSfL | sh
RUN git clone https://github.com/gwsystems/aWsm.git
RUN cd aWsm && ./install_deb.sh && cp -t /usr/bin ./target/release/awsm && cd -
RUN git clone https://github.com/wasm3/wasm3.git
RUN cd wasm3 && gcc -O3 -g0 -s -Isource -Dd_m3HasWASI source/*.c platforms/app/main.c -lm -o wasm3 && cd -
RUN git clone --recursive https://github.com/WebAssembly/wabt && cd wabt && git submodule update --init && mkdir build && cd -
RUN cd wabt/build && cmake .. && cmake --build . && cd -
ARG DEBIAN_FRONTEND=noninteractive
RUN apt -y install sqlite3 php8.1 php8.1-sqlite3
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
ENV NVM_DIR=/root/.nvm
ENV NODE_VERSION=20
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"
RUN git clone https://github.com/emscripten-core/emsdk.git && cd emsdk && ./emsdk install latest && ./emsdk activate latest && cd ..
ENV PATH="/install/emsdk:/install/emsdk/upstream/emscripten:${PATH}"
RUN apt -y install php8.1-curl