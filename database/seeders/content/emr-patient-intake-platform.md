## The problem

A clinic runs on a chain of connected workflows: a patient completes intake, a provider documents the visit, staff schedule follow-ups, and billing turns the encounter into a superbill. When those steps live in separate tools — or on paper — information gets re-entered, context is lost between roles, and a simple question becomes hard to answer: *who changed this record, and when?*

The platform brings these workflows into one web application, with access scoped to each user's role and an audit trail around sensitive records.

## Who uses it

The platform serves clinic teams with different responsibilities — providers documenting care, front-office staff handling intake and appointments, and billing and administrative users working with superbills, authorization forms, invoices, and support tickets. Each group needs a different slice of the same patient record, which makes access control a core design concern rather than an add-on.

[[highlights]]

[[architecture]]

## Core workflows

- **Patient intake** — building and improving multi-section intake forms backed by complex patient data structures.
- **Clinical documentation** — SOAP-note workflows (Subjective, Objective, Assessment, Plan) that give providers a consistent structure for documenting encounters.
- **Scheduling** — appointment scheduling functionality for managing patient visits.
- **Billing** — contributions to billing and superbill workflows, along with invoices.
- **Administration** — authorization forms, ticketing, and the administrative tooling that keeps a practice running.

## How the backend and frontend fit together

Django provides the application layer — models, business logic, and page rendering — and Django REST Framework exposes REST APIs that the JavaScript frontend uses for interactive workflows such as dynamic forms. Layout and components are built with HTML, CSS, and Bootstrap.

Keeping validation and permission checks on the server means the same rules apply whether a request comes from a rendered page or an API call.

## Access control and auditability

Healthcare data needs more than a login screen. I implemented authentication and role-based access control so users reach only the records and actions their role requires, and worked on audit logging so that changes to sensitive data are traceable. Alongside this, I contributed to the platform's data-protection mechanisms.

> This case study describes the controls I worked on — role-based access, audit logging, and security-focused data handling. It is not a claim of compliance certification.

## Engineering challenges

### Evolving complex patient data

Intake and clinical forms carry deeply structured information, and clinics ask for changes over time. The challenge is extending forms and data structures without breaking existing records or the workflows built on them — which puts a premium on careful, backwards-compatible migrations.

### Importing and migrating data

Supporting data import and migration workflows means handling records that don't arrive in the shape the application expects. Imports need validation and mapping, and their results need checking before they can be trusted.

### Debugging in production

Client-reported bugs rarely arrive with a stack trace. Resolving them means reproducing the user's workflow, tracing it through the frontend, the API, and the database, and shipping a fix without disrupting a clinic that is using the system.

## Deployment and maintenance

I take part in production deployment and ongoing maintenance on AWS. That work has involved Nginx, scheduled background tasks, backups, SSL certificates, DNS, and storage — along with the debugging that comes with running software people depend on every day.

## Technical decisions

- **Permissions enforced on the server.** Role checks live in the backend, so access rules don't depend on what the interface hides.
- **An ORM-first data layer.** The documented project configuration uses SQLite; working through the Django ORM and planned migrations keeps the data layer ready for PostgreSQL.
- **Auditability as a feature.** Audit logging is treated as part of the product, because "who changed this record?" is a question clinics need answered.

## What I took away

Working on healthcare software in production has shaped how I build: understand the workflow before writing the model, enforce rules on the server, make changes reversible, and treat every client-reported issue as a chance to make the system easier to reason about.
