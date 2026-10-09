## The problem

Writing good quiz questions takes time. Quiz Hub is an academic project that explores whether AI services can take on the first draft — generating quizzes on a topic — while a Django application handles the parts that need to be reliable: accounts, roles, quiz attempts, and dashboards.

[[highlights]]

[[architecture]]

## Roles and access

The application uses a custom user model with three roles — **student**, **teacher**, and **admin** — with separate registration flows for students and teachers. Role-based access is enforced by a custom decorator and middleware, and the admin dashboard is restricted to administrators.

## AI-assisted quiz generation

A quiz request specifies a topic, the number of questions, a difficulty level, question types, and optional source content. Generation follows a fallback chain:

1. **Google Gemini** is tried first.
2. If that fails, the request falls back to **OpenAI**.
3. If neither service is available, a **rule-based generator** produces a basic quiz, so the feature degrades instead of breaking.

The AI response is parsed into a structured JSON quiz — title, description, questions, and total points — that the application can render or store.

## Taking quizzes

Quizzes and user attempts are stored as separate models, so each attempt and its score can be tracked per user and surfaced in role-specific dashboards.

## Limitations

- **AI output needs review.** Generated questions can be inaccurate or ambiguous; in real use a teacher should review them before students see them.
- **External dependencies.** Quality depends on the AI services' availability and behaviour — the fallback chain limits, but doesn't remove, that dependency.
- **Development setup.** The project uses SQLite as its default development database.
