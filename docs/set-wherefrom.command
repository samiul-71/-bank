#!/bin/bash
#
# Sets the macOS Finder "Where from" field on the Account Statement PDF.
#
# HOW TO USE (two ways):
#   1) Double-click this file in Finder, OR
#   2) In Terminal:  ./set-wherefrom.command  [optional /path/to/BankStatement.pdf]
#
# If no path is given it looks for ~/Downloads/BankStatement.pdf
#

set -e

FILE="${1:-$HOME/Downloads/BankStatement.pdf}"

if [ ! -f "$FILE" ]; then
  echo "File not found: $FILE"
  echo "Pass the PDF path as an argument, e.g.:  ./set-wherefrom.command ~/Downloads/BankStatement.pdf"
  exit 1
fi

URL1="https://unet.ucb.com.bd/eb/dashboard/account/statement/1"
URL2="https://unet.ucb.com.bd/eb/casa/statement/download?accountNumber=10E27DA741293BA868D4CA0A4ADBB76D362674E7E2323C93B7769591AAA092AD&fromDate=01/04/2026&toDate=30/06/2026&transactionType=1&exportType=pdf&isToday=false"

# kMDItemWhereFroms is a binary-plist array of strings; build it and write as hex.
python3 - "$FILE" "$URL1" "$URL2" <<'PY'
import sys, plistlib, subprocess
path, *urls = sys.argv[1:]
blob = plistlib.dumps(urls, fmt=plistlib.FMT_BINARY)
subprocess.run(["xattr", "-wx",
                "com.apple.metadata:kMDItemWhereFroms",
                blob.hex(), path], check=True)
print("'Where from' set on:", path)
PY

echo "Done. Right-click the PDF -> Get Info to confirm."
