## The challenge

Smart India Hackathon 2024 problem statement **1711** asked teams to enhance **Rail Madad**, Indian Railways' complaint platform, with AI-powered complaint management. Complaints arrive in many forms — text, photos, and videos — and in many languages, which makes them slow to understand and route by hand.

> **Status:** this is a hackathon concept. The approach below describes a proposed design; it was not deployed as a production system.

[[architecture]]

## Proposed approach

- **Multimodal intake.** Accept complaints as text, images, or video.
- **OCR** to extract text from images, such as details visible in a photo.
- **AI / deep learning** to understand and categorize complaint content across these formats.
- **Multilingual support** so passengers can complain in the language they're comfortable with.
- **Cloud scalability** so the system can handle varying complaint volumes.

## What I took from it

Designing for a system at national scale — many languages, many input formats, and high volume — is a very different exercise from building a single application. The hackathon was a useful introduction to thinking about AI components as parts of a larger service rather than as standalone models.
