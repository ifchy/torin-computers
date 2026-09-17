# Phase 4 — Measured host capabilities (bell.host.bg, public_html/new/)

Every figure below was read out of a live response body. The command that
produced it and the date it was produced are recorded alongside it, following
the evidence discipline 03.5-TRUTH-AUDIT.md established.

**Anything not read out of a response body is an assumption and is labelled
one.** There are no unmarked inferences in this file.

The probe token is redacted from the recorded commands: it gated a file that no
longer exists, but writing a live credential into a committed artefact is a
habit worth not having.


## Probe run — 2026-09-17

Command:

```
curl -s 'https://torin.bg/new/hc-9f15ac99ad6383cfbcf50832aac339db.php?k=<32-hex-token>'
```

Response body, verbatim:

```
version              : 5.2.17
sapi                 : cgi-fcgi
ext:gd               : yes
ext:exif             : yes
ext:fileinfo         : yes
ext:curl             : yes
ext:openssl          : yes
ext:mbstring         : yes
ext:hash             : yes
ext:ctype            : yes
ext:filter           : yes
ini:upload_max_filesize: '2M'
ini:post_max_size    : '8M'
ini:max_file_uploads : '20'
ini:max_input_vars   : '1000'
ini:memory_limit     : '128M'
ini:max_execution_time: '30'
ini:allow_url_fopen  : '1'
ini:user_ini.filename: ''
ini:user_ini.cache_ttl: ''
ini:sendmail_path    : '/usr/sbin/sendmail -t -i'
ini:SMTP             : 'localhost'
ini:smtp_port        : '25'
outbound:curl443     : OK http=401
sendmail binary      : yes
smtp:localhost:25    : FAIL Connection refused
```

## Probe cleanup — 2026-09-17

Fetched WITH the valid token, because a tokenless 404 is what a LIVE
probe returns too and would prove nothing:

```
curl -s -o /dev/null -w '%{http_code}' 'https://torin.bg/new/hc-9f15ac99ad6383cfbcf50832aac339db.php?k=<32-hex-token>'
404
```
