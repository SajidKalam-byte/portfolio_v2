# Confirm before publishing

The site only states what your brief supports. Where something was missing or ambiguous, I chose the conservative option. Please review each item — most are a one-line change in **admin → Settings / Projects / Experience** or in `.env`.

## Identity & contact

| # | Item | Current setting | Action |
| --- | --- | --- | --- |
| 1 | Public email | `sajid.kalam.dev@gmail.com` (the alternate `sk5102552@gmail.com` is never shown) | Confirm, or change in Settings |
| 2 | Phone | Hidden | Add + tick "Show phone" only if you want it public |
| 3 | Location | Siliguri, West Bengal, India (Madarihat is not used anywhere) | Confirm |
| 4 | Job title | "Associate Engineer · Full-Stack Web Developer", Corunit Media Corp, **Mar 2025 – Present** | Confirm the exact official title and start month |
| 5 | Naming your employer | Home status line: "Associate Engineer at Corunit Media Corp" | Confirm you're comfortable naming Corunit publicly |
| 6 | "Open to opportunities" | Off | Turn on in Settings if you're actively looking |
| 7 | `worksFor` in structured data | Off | Enable in Settings once item 4–5 are confirmed |
| 8 | Employment type / work location | Blank for Corunit; Personifwy marked "Internship" | Add (e.g. Full-time · On-site/Remote) if you want them shown |
| 9 | Domain | `APP_URL` placeholder | Set your final domain |

## Education

| # | Item | Current | Action |
| --- | --- | --- | --- |
| 10 | MCA, Siliguri Institute of Technology | Completed, 2024 — 2026, CGPA 8.63/10 | ~~Resolved 2026-10-08~~ |
| 11 | BCA, University of North Bengal | 2021 — 2024, CGPA 9.31/10 (only years are displayed) | Confirm |

## EMR case study

| # | Item | Notes |
| --- | --- | --- |
| 12 | Product link `https://circle.emrmd.com` | **Not linked.** It returned HTTP 200 on 24 Sep 2026, but it's a client healthcare product; link only with permission |
| 13 | "Who uses it" | Inferred from the modules (providers, front office, billing, admin staff) — confirm |
| 14 | Frontend/backend description | "Django renders pages and business logic; DRF APIs used by the JavaScript frontend" — confirm this matches the architecture |
| 15 | Technical decisions | Server-side permission checks; ORM-first data layer (SQLite documented, PostgreSQL-ready); audit logging as a feature — confirm |
| 16 | AWS operations | Lists Nginx, scheduled background tasks, backups, SSL, DNS, storage — remove any you didn't personally work on |
| 17 | Status label | "In production" | Confirm |
| 18 | Metrics | None shown | Add only verified, shareable numbers |

## Lanexis case study

| # | Item | Notes |
| --- | --- | --- |
| 19 | Client name | "Lanexis" is shown publicly — confirm permission (or tick Confidential and rename) |
| 20 | Context | Unclear whether this was through Corunit or independent/freelance. If independent, it could also become an Experience entry |
| 21 | Timeline | Not shown (unknown) |
| 22 | Notification channels | Described as "multi-channel" — name them (email / in-app / push) once confirmed |
| 23 | Google Sheets sync | Described as manual sync only; add auth method, where data lands (PostgreSQL?), duplicate handling, and the trade-off rationale if you want a deeper section |
| 24 | Mobile app scope | "Worked on the React Native/Expo mobile application" — adjust if you built it end to end |
| 25 | Screenshots / live link | None — add only with client permission |

## Machine-learning projects

| # | Item | Notes |
| --- | --- | --- |
| 26 | Accuracy wording | "Reported 98%" and "reported 92%" on "the project's evaluation data" — confirm, and add precision/recall/F1 or dataset names if you have them |
| 27 | Repositories | Not linked. `Sentiment_Analysis` exists on your GitHub but wasn't clearly one of these — link the right repos if public |

## Other projects

| # | Project | Notes |
| --- | --- | --- |
| 28 | DailyExplainers | Marked as a **personal project**, role "Developer", status "Live" (site returned HTTP 200 on 24 Sep 2026). Feature list is from your brief — confirm, and add a repo link if public |
| 29 | Chess for Two | Verified from the public repo and live demo (PeerJS 1.5.2, custom rules engine, castling, en passant, promotion, checkmate/stalemate, chat) |
| 30 | Quiz Hub | Linked to `AI-Powered_Quiz_Generator` and described from its README (Gemini → OpenAI → rule-based fallback; student/teacher/admin roles). **Leaderboard and performance reports are not mentioned** because the README doesn't document them — confirm and add |
| 31 | SIH 2024 (Rail Madad, PS 1711) | Role shown as "Participant", status "Hackathon concept". Add team role, stage reached, and anything actually built |
| 32 | Private Client Systems | Generic NDA card — keep or unpublish |

## Skills

| # | Item | Notes |
| --- | --- | --- |
| 33 | Full list | Includes C, C++, Java, NLTK, pandas, NumPy, MySQL, query optimization, API documentation, background task scheduling (from your brief) and React Native/Expo, PostgreSQL, Gunicorn, AWS EC2/RDS/S3, Google Sheets API (from the Lanexis material). Hide anything you wouldn't want to be interviewed on |

## Voice & copy

| # | Item | Where |
| --- | --- | --- |
| 34 | Working principles ("Understand the workflow first", etc.) | Home + About (`resources/views/pages/`) |
| 35 | "Where I'm heading" list | Home skills section |
| 36 | About bio & learning-journey timeline | `resources/views/pages/about.blade.php` |
| 37 | Hero line ("…a B2B customer portal I took from requirements to AWS deployment") | Depends on items 19–20 |

## Testimonials

| # | Item |
| --- | --- |
| 41 | No testimonials are seeded — the section stays hidden until you publish a real one. Send request links from *admin → Testimonials*. |
| 42 | For healthcare (EMR) feedback, make sure the person may speak publicly about the project and that the quote names no patients or confidential details. |

## Files to add

| # | Item |
| --- | --- |
| 38 | Resume PDF (admin → Settings). Make sure the email inside matches the public one. |
| 39 | ~~Profile photo added (`PORTFOLIO_PHOTO`) — shown on the About page and in structured data. Resolved 2026-10-08.~~ |
| 40 | Real, publishable screenshots for any project (replace the illustrative covers) |
