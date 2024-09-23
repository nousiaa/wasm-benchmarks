echo "IMPORTS:"
wasm-dis $1 | grep "\(import \".*\)"
echo "DONE"