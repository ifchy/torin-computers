# Phase 4 — External API Coverage Matrix

**Detector result:** `api-coverage.cjs --json` over the ROADMAP Phase 4 section returned
`{"detected": false, "signals": []}` (run 2026-09-17). The deterministic detector did **not**
fire, because the ROADMAP prose describes outcomes ("contact path is secure", "goes live")
rather than integration verbs/nouns.

**Planner override, recorded honestly:** the detector's `false` is a miss at the ROADMAP layer,
not a fact about the phase. Reading CONTEXT D4-05 and RESEARCH §Standard Stack, this phase
*does* integrate one genuine external API with an enumerable capability surface — the
**Telegram Bot API**. A matrix is therefore written rather than a no-integration declaration.
Two further external touchpoints are recorded below as reasoned declarations, because they are
transports/hosted scripts with no capability surface to subtract from.

---

## Integration 1 — Telegram Bot API (`api.telegram.org`)

**Need:** deliver an enquiry (text + up to 5 photographs) to the shop owner's phone (D4-05).
**Baseline:** full coverage. Every capability starts `INTEGRATE`; the rows below are the
subtraction record.

| capability | decision | reason |
|---|---|---|
| `sendMessage` | INTEGRATE | The 0-photo enquiry shape and the caption-overflow path for 2–5 photos. |
| `sendPhoto` | INTEGRATE | The 1-photo shape. `sendMediaGroup` refuses fewer than 2 items (P-4) and 1 photo is the single most likely submission. |
| `sendMediaGroup` | INTEGRATE | The 2–5 photo shape, `attach://` multipart. |
| `getMe` | INTEGRATE | Token/connectivity self-check; the D4-06 probe already calls the unauthenticated form. |
| `getUpdates` | OPT-OUT | Inbound polling requires a persistent process; this host runs request-driven FastCGI only (RESEARCH §Runtime State Inventory: no cron, no process manager). |
| `setWebhook` / `deleteWebhook` / `getWebhookInfo` | OPT-OUT | No inbound webhook endpoint is created by design — RESEARCH §Security Domain V13 records the integration as outbound-only to a fixed literal host. An inbound callback would be new public attack surface for no requirement. |
| `sendDocument` / `sendVideo` / `sendAudio` / `sendVoice` / `sendAnimation` | OPT-OUT | CONTACT-05 names photographs only; the upload pipeline hard-rejects anything `getimagesize()` cannot decode (D4-15), so no other media type can ever reach the notifier. |
| `sendLocation` / `sendVenue` / `sendContact` / `sendPoll` / `sendDice` | OPT-OUT | No requirement collects a visitor location, venue, contact card or poll. Collecting one would expand the PII surface D4-07 deliberately minimises. |
| `editMessageText` / `editMessageMedia` / `deleteMessage` | OPT-OUT | D4-07 forbids server-side storage, so no message id is retained and nothing can be addressed for edit or deletion afterwards. |
| `answerCallbackQuery` / inline-keyboard `reply_markup` | OPT-OUT | An actionable button in the notification would need an inbound webhook (opted out above) to service the callback. |
| `forwardMessage` / `copyMessage` | OPT-OUT | Fan-out to a second recipient is done by making the target a Telegram **group** (D4-05 explicitly chose group-capability for multi-recipient), not by re-sending. |
| `banChatMember` / `promoteChatMember` / all chat-administration methods | OPT-OUT | The bot never administers a chat; it only posts into one the owner created. |
| `setMyCommands` / `setChatMenuButton` / `setMyDescription` | OPT-OUT | The bot has no conversational interface — visitors never talk to it. Nothing to describe or command. |
| Payments (`sendInvoice`, `answerPreCheckoutQuery`, Stars) | OPT-OUT | D4-05's "no extra spend" framing and the project-wide rule that **prices are never published** (STATE.md, Phase 4 scope note). No payment flow exists anywhere in this project. |
| Passport / `setPassportDataErrors` | OPT-OUT | No identity documents are collected; collecting them would directly contradict D4-07. |
| Stickers, Games, Business-account methods | OPT-OUT | No requirement touches any of these surfaces. |

---

## Touchpoint 2 — SuperHosting SMTP (`torin.bg:25`, authenticated)

No external API integration: this is a **mail transport protocol hop to the host's own mail
server on the same machine**, driven entirely by PHPMailer 7.1.1 — there is no vendor API, no
endpoint set, and no capability surface to subtract from. Recorded rather than omitted because
it is an outbound network dependency that carries a stored secret.

## Touchpoint 3 — Umami Cloud tracker (`cloud.umami.is/script.js`)

No external API integration: this is a **hosted third-party `<script>` tag plus one
client-side function call (`umami.track`)**, not a server-side API the project calls. The one
surface decision that *is* enumerable is recorded in 04-07 and in UI-SPEC §C-9: the declarative
`data-umami-event` attribute is **forbidden on `tel:`/`mailto:` anchors** (it `preventDefault()`s
and re-navigates in `.finally()`, P-2), and `data-performance` is omitted. No server-side Umami
API (reporting/admin) is called by this project.
