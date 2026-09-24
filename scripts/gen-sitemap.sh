#!/usr/bin/env bash
#
# gen-sitemap.sh
#
# Generates src/sitemap.xml from the source tree. The site had no sitemap and no
# robots.txt at all before Phase 4 -- both returned 404 on the live origin -- so
# this is creation work, not migration work.
#
# STATIC, NOT GENERATED AT REQUEST TIME. A PHP sitemap would need a handler
# mapping for a fifth extension in src/.htaccess, the one file whose failure mode
# is the entire site at once. A static file plus scripts/sitemap-check.sh (which
# asserts it still matches reality) is the cheaper trade.
#
# THE BASE URL IS READ FROM src/includes/site-config.php, NEVER TYPED HERE. That
# entry is the ONLY place the /new/ staging segment appears in src/, and the
# cutover changes exactly it; typing a host here would create a copy to forget.
#
#   ##########################################################################
#   ## RE-RUN THIS SCRIPT AFTER THE CUTOVER. The generated sitemap hard-codes
#   ## absolute URLs built from base_url. When base_url flips from
#   ## https://torin.bg/new/ to https://torin.bg/, every <loc> in sitemap.xml
#   ## is stale until this script regenerates it. A sitemap full of /new/ URLs
#   ## submitted after the swap points Google at a tree that no longer exists.
#   ##########################################################################
#
# Usage:
#   scripts/gen-sitemap.sh              # write src/sitemap.xml
#   scripts/gen-sitemap.sh --stdout     # print, write nothing (for diffing)
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
SRC_DIR="${REPO_ROOT}/src"
CONFIG="${SRC_DIR}/includes/site-config.php"
OUT="${SRC_DIR}/sitemap.xml"

MODE="${1:-}"

if [ ! -f "$CONFIG" ]; then
	echo "ERROR: ${CONFIG} not found" >&2
	exit 1
fi

# Pull base_url out of the PHP array literal. Anchored on the quoted key so a
# comment mentioning base_url cannot match, and the value is taken from the
# single-quoted string after the fat arrow.
BASE_URL="$(sed -n "s/^[[:space:]]*'base_url'[[:space:]]*=>[[:space:]]*'\([^']*\)'.*/\1/p" "$CONFIG" | head -1)"

if [ -z "$BASE_URL" ]; then
	echo "ERROR: could not read 'base_url' from ${CONFIG}" >&2
	echo "       (the key moved or changed quoting style -- fix this script, do not hard-code a host)" >&2
	exit 1
fi

# Exactly one trailing slash, however the config spells it.
BASE_URL="${BASE_URL%/}/"

# ---------------------------------------------------------------------------
# The indexable page set, DERIVED rather than listed.
#
# A hard-coded slug list is how scripts/asset-version-check.sh ended up naming
# six pages this tree no longer has. So: every src/*.html is a candidate, and a
# page is EXCLUDED when it assigns a $torin_robots value containing "noindex".
#
# That is the same fact the <meta name="robots"> emitter in header.php reads, so
# the sitemap and the page's own directive cannot drift apart -- listing a
# no-indexed URL is a contradiction Search Console reports straight back. Today
# that rule excludes exactly one page, msg.html, the post-submit confirmation
# page, which has no search value and would be a jarring entry point from a
# results listing.
# ---------------------------------------------------------------------------
PAGES=()
EXCLUDED=()

for f in "${SRC_DIR}"/*.html; do
	[ -e "$f" ] || continue
	base="$(basename "$f")"
	# NOT EVERY .html IN src/ IS A PAGE. The Search Console verification token
	# (google<hash>.html) is 53 bytes of plain text with no PHP in it -- it must
	# stay fetchable at its own URL, but it is not content and must never appear
	# in a sitemap. It has no $torin_robots line to exclude it by, so the noindex
	# rule below cannot see it.
	#
	# Discriminate on what actually makes a file a rendered page in this tree:
	# every one of them includes includes/header.php. That is DERIVED, like the
	# noindex rule, so the next static drop-in is handled without editing a list
	# -- which is the whole reason this script refuses to carry hard-coded slugs.
	if ! grep -q 'includes/header.php' "$f"; then
		EXCLUDED+=("$base")
		continue
	fi
	if grep -qE '^\$torin_robots[[:space:]]*=.*noindex' "$f"; then
		EXCLUDED+=("$base")
		continue
	fi
	PAGES+=("$base")
done

if [ "${#PAGES[@]}" -eq 0 ]; then
	echo "ERROR: no indexable pages found under ${SRC_DIR}" >&2
	exit 1
fi

# lastmod comes from the last COMMIT that touched the file, not filemtime: a
# fresh clone rewrites every mtime to checkout time, which would publish a
# sitemap claiming the whole site changed today. Falls back to filemtime outside
# a git work tree.
lastmod_for() {
	local path="$1" d=""
	if git -C "$REPO_ROOT" rev-parse --git-dir >/dev/null 2>&1; then
		d="$(git -C "$REPO_ROOT" log -1 --format=%cs -- "$path" 2>/dev/null || true)"
	fi
	if [ -z "$d" ]; then
		d="$(date -u -r "$path" +%Y-%m-%d 2>/dev/null || true)"
	fi
	printf '%s' "$d"
}

# NO "--" MAY APPEAR ANYWHERE IN THE COMMENT BELOW. A double hyphen is illegal
# inside an XML comment, and every sitemap this script has ever produced was
# therefore MALFORMED XML -- caught 2026-09-24, one step before the file was due
# to be submitted to Search Console. sitemap-check.sh could not see it because
# every one of its assertions was a grep; Check F now parses the file.
#
# The two offenders were an em-dash-style aside on the REGENERATE line and
# another on the prose-counting line. Both are rewritten with punctuation that
# is legal in XML. If you add a line here, use a comma or a full stop.
emit() {
	printf '%s\n' '<?xml version="1.0" encoding="UTF-8"?>'
	printf '%s\n' '<!-- Generated by scripts/gen-sitemap.sh. Do not hand-edit: scripts/sitemap-check.sh'
	printf '%s\n' '     asserts this file still equals the set of pages that return 200 and are'
	printf '%s\n' '     indexable, and a hand edit is exactly what that check exists to catch.'
	printf '%s\n' '     REGENERATE AFTER THE CUTOVER. Every location below is built from'
	printf '%s\n' "     site-config.php base_url, currently ${BASE_URL}"
	printf '%s\n' '     NOTE: this comment deliberately avoids the literal loc tag. Counting'
	printf '%s\n' '     URLs with a plain substring grep is a common shortcut, and a tag'
	printf '%s\n' '     mentioned in prose would inflate that count by one, letting a'
	printf '%s\n' '     "how many URLs?" gate pass for a reason that has nothing to do with'
	printf '%s\n' '     the sitemap. -->'
	printf '%s\n' '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
	for base in "${PAGES[@]}"; do
		local_path="${SRC_DIR}/${base}"
		# The homepage is listed at the directory root, not as index.html.
		# Both resolve, but the root is what external links and the domain
		# itself point at, and with no rel=canonical in the tree yet
		# (deferred by RESEARCH OQ-5) the sitemap is the only signal telling
		# Google which of the two duplicates to keep.
		if [ "$base" = "index.html" ]; then
			loc="${BASE_URL}"
		else
			loc="${BASE_URL}${base}"
		fi
		lm="$(lastmod_for "$local_path")"
		printf '\t<url>\n'
		printf '\t\t<loc>%s</loc>\n' "$loc"
		if [ -n "$lm" ]; then
			printf '\t\t<lastmod>%s</lastmod>\n' "$lm"
		fi
		printf '\t</url>\n'
	done
	printf '%s\n' '</urlset>'
}

if [ "$MODE" = "--stdout" ]; then
	emit
	exit 0
fi

emit > "$OUT"

echo "gen-sitemap: wrote ${OUT}"
echo "  base_url : ${BASE_URL}  (read from src/includes/site-config.php)"
echo "  listed   : ${#PAGES[@]} pages"
if [ "${#EXCLUDED[@]}" -gt 0 ]; then
	echo "  excluded : ${#EXCLUDED[@]} no-indexed (${EXCLUDED[*]})"
else
	echo "  excluded : 0 no-indexed"
fi
