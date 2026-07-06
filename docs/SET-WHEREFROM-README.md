# Setting the "Where from" field on the Account Statement PDF (macOS only)

The Finder **Get Info → More Info → "Where from"** value is **not** stored inside
the PDF. It is a macOS file attribute (`com.apple.metadata:kMDItemWhereFroms`)
that the operating system attaches to the file on disk. Because of that it cannot
be added by the Laravel app when the PDF is generated — it has to be set on the
Mac, after the file is there.

This folder includes `set-wherefrom.command`, which sets it for you.

## What it sets

Two URLs (matching the reference statement):

```
https://unet.ucb.com.bd/eb/dashboard/account/statement/1
https://unet.ucb.com.bd/eb/casa/statement/download?accountNumber=10E27DA741293BA868D4CA0A4ADBB76D362674E7E2323C93B7769591AAA092AD&fromDate=01/04/2026&toDate=30/06/2026&transactionType=1&exportType=pdf&isToday=false
```

## How to use

### Option A — double-click (easiest)
1. Copy `set-wherefrom.command` to your Mac.
2. One time only, make it runnable. In Terminal:
   ```bash
   chmod +x set-wherefrom.command
   ```
3. Put `BankStatement.pdf` in your `~/Downloads` folder.
4. Double-click `set-wherefrom.command` in Finder.
   - For a file elsewhere, run it with the path:
     ```bash
     ./set-wherefrom.command ~/Downloads/BankStatement.pdf
     ```

### Option B — paste into Terminal
```bash
FILE="$HOME/Downloads/BankStatement.pdf"   # change if needed

python3 - "$FILE" \
  "https://unet.ucb.com.bd/eb/dashboard/account/statement/1" \
  "https://unet.ucb.com.bd/eb/casa/statement/download?accountNumber=10E27DA741293BA868D4CA0A4ADBB76D362674E7E2323C93B7769591AAA092AD&fromDate=01/04/2026&toDate=30/06/2026&transactionType=1&exportType=pdf&isToday=false" <<'PY'
import sys, plistlib, subprocess
path, *urls = sys.argv[1:]
blob = plistlib.dumps(urls, fmt=plistlib.FMT_BINARY)
subprocess.run(["xattr","-wx","com.apple.metadata:kMDItemWhereFroms", blob.hex(), path], check=True)
print("Done:", path)
PY
```

## Confirm it worked

Right-click the PDF in Finder → **Get Info**. The "Where from" line should now
show both URLs.

## Notes
- Runs on **macOS only** (uses the `xattr` command).
- If you re-download or copy the file in a way that strips attributes, just run
  it again.
- The other properties (Author, Content Creator = Creator, Encoding software =
  Producer, page size 700×842) **are** embedded in the PDF by the app and need
  no manual step.
