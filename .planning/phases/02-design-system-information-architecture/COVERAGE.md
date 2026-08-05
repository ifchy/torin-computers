# Phase 2 — External API Coverage Matrix

**Status:** No matrix required — reasoned declaration in lieu of one.

No external API integration: this phase produces hand-authored CSS, PHP 5.2 `include()`
partials, ~50 lines of vanilla JS, and two self-hosted font binaries — there is no SDK, no
API client, no service account, and no runtime network call to any third party.

## Why the near-misses are not API integrations

| Candidate | Why it is not an external API integration |
|---|---|
| Google Fonts (`fonts.gstatic.com`) | Two one-off `curl` downloads on the **dev machine** that produce committed static binaries (`src/fonts/*.woff2`). No runtime request, no key, no versioned client. The deployed site never contacts Google Fonts — that is the point of self-hosting (D-06a, RESEARCH §1d). |
| Google Maps (D-34) | A plain `<a href>` deep link to a `google.com/maps/search/?api=1` URL. No Maps JavaScript API, no embed iframe, no API key — D-34 removed the iframe deliberately. |
| schema.org JSON-LD (D-34) | Hand-authored markup emitted by `includes/jsonld.php`. schema.org is a vocabulary, not a service; nothing is called. |
| Viber / WhatsApp click-to-chat (D-16) | A `viber://` URI scheme handled by the visitor's own OS. No Viber API, no business account, no webhook. The mechanics are Phase 4 (CONTACT-02). |
| `bell.host.bg` FTP/HTTP | The project's own hosting, already established in Phase 1. Deploy transport, not an integrated API. |

## Consequence

There is no capability surface to enumerate and therefore no `INTEGRATE` / `OPT-OUT`
subtraction record to keep. This declaration is what the seal-time `verify:pre` gate reads in
place of a matrix.

If a later phase introduces a genuine API integration (a transactional-email provider for
CONTACT-03, or a live Google Reviews widget for the deferred REVIEWS-01), that phase must
produce a real matrix — this declaration does not carry forward.

---
*Declared during Phase 2 planning, 2026-08-05.*
