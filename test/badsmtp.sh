#!/usr/bin/env bash
# Helper script to manage a badsmtp test server instance for the PHPMailer test suite.
# badsmtp is a configurable SMTP server for testing clients and integrations;
# @link https://badsmtp.com
#
# Usage:
#   test/badsmtp.sh start   Download (if needed) and start badsmtp on 127.0.0.1:2525
#   test/badsmtp.sh stop    Stop the badsmtp instance started by this script
#
# Environment variables:
#   BADSMTP_BIN     Path to an existing badsmtp binary. If not set, a binary
#                   is downloaded from the BadSMTP GitHub releases into
#                   .badsmtp/ if one is not already present.
#   BADSMTP_VERSION Version to download. Defaults to the latest release.

set -euo pipefail

PORT="${BADSMTP_PORT:-2525}"
HOST="127.0.0.1"
BIN_DIR=".badsmtp"
PID_FILE="build/badsmtp.pid"
LOG_FILE="build/badsmtp.log"
# badsmtp discards messages by default; set BADSMTP_MAILBOX_DIR to retain
# them in Maildir format under that directory.
MAILBOX_DIR="${BADSMTP_MAILBOX_DIR:-}"

# Work from the project root, no matter where the script is called from.
cd "$(dirname "$0")/.."

download_badsmtp() {
    local os arch asset url
    os="$(uname -s | tr '[:upper:]' '[:lower:]')"
    arch="$(uname -m)"
    case "$arch" in
        x86_64 | amd64) arch="amd64" ;;
        aarch64 | arm64) arch="arm64" ;;
        riscv64) arch="riscv64" ;;
        *)
            echo "Unsupported architecture: $arch" >&2
            exit 1
            ;;
    esac
    asset="badsmtp-${os}-${arch}"

    if [ -n "${BADSMTP_VERSION:-}" ]; then
        url="https://github.com/BadSMTP/BadSMTP/releases/download/${BADSMTP_VERSION}/${asset}"
    else
        url="https://github.com/BadSMTP/BadSMTP/releases/latest/download/${asset}"
    fi

    mkdir -p "$BIN_DIR"
    echo "Downloading badsmtp from ${url}" >&2
    curl -fsSL -o "${BIN_DIR}/badsmtp" "$url"
    chmod +x "${BIN_DIR}/badsmtp"
}

find_badsmtp() {
    if [ -n "${BADSMTP_BIN:-}" ] && [ -x "${BADSMTP_BIN}" ]; then
        echo "${BADSMTP_BIN}"
        return 0
    fi
    if [ -x "${BIN_DIR}/badsmtp" ]; then
        echo "${BIN_DIR}/badsmtp"
        return 0
    fi
    download_badsmtp >&2
    echo "${BIN_DIR}/badsmtp"
}

port_open() {
    nc -z "$HOST" "$PORT" >/dev/null 2>&1
}

start() {
    mkdir -p build

    # Reuse an already-running server on the port (e.g. a developer's own instance).
    if port_open; then
        echo "badsmtp is already listening on ${HOST}:${PORT}; reusing it." >&2
        return 0
    fi

    local bin
    bin="$(find_badsmtp)"

    local mailbox_args=()
    if [ -n "$MAILBOX_DIR" ]; then
        mkdir -p "$MAILBOX_DIR"
        mailbox_args=(--mailbox "$MAILBOX_DIR")
    fi

    "${bin}" ${mailbox_args[@]+"${mailbox_args[@]}"} >"$LOG_FILE" 2>&1 &
    local pid=$!
    echo "$pid" > "$PID_FILE"

    # Wait for the server to accept connections.
    local i
    for i in $(seq 1 50); do
        if port_open; then
            echo "badsmtp started (pid ${pid}) on ${HOST}:${PORT}" >&2
            return 0
        fi
        if ! kill -0 "$pid" 2>/dev/null; then
            echo "badsmtp failed to start; log output:" >&2
            cat "$LOG_FILE" >&2
            exit 1
        fi
        sleep 0.2
    done

    echo "badsmtp did not start listening on ${HOST}:${PORT} in time; log output:" >&2
    cat "$LOG_FILE" >&2
    exit 1
}

stop() {
    if [ -f "$PID_FILE" ]; then
        local pid
        pid="$(cat "$PID_FILE")"
        if kill -0 "$pid" 2>/dev/null; then
            kill "$pid"
            echo "badsmtp (pid ${pid}) stopped" >&2
        fi
        rm -f "$PID_FILE"
    else
        echo "No PID file; nothing to stop." >&2
    fi
}

case "${1:-}" in
    start) start ;;
    stop) stop ;;
    *)
        echo "Usage: $0 {start|stop}" >&2
        exit 1
        ;;
esac
