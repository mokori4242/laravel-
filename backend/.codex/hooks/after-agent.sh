#!/usr/bin/env bash
set -o pipefail
set -u

ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
LOG_DIR="$ROOT/backend/.codex/hooks/logs"
LOG_FILE="$LOG_DIR/after-agent-$(date +%Y%m%d-%H%M%S).log"

mkdir -p "$LOG_DIR"
cd "$ROOT" || exit 1

status=0

run_check() {
    echo "==> $*" | tee -a "$LOG_FILE"

    if ! "$@" 2>&1 | tee -a "$LOG_FILE"; then
        status=1
    fi
}

run_check mise run php:larastan
run_check mise run php:test
run_check mise run php:pint

if [ "$status" -ne 0 ]; then
    echo "after-agent checks failed. See $LOG_FILE" >&2
fi

exit "$status"
