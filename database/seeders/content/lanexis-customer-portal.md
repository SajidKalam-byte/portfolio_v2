## The problem

Lanexis kept operational information — including order and shipment data — in internal company Google Sheets. Customers and staff needed controlled access to the right slice of that information, together with project management, dashboards, reporting, and notifications, without anyone working directly inside the internal spreadsheets.

The goal was a single business portal that fit how the company already worked, rather than one that forced it to abandon its existing tools.

[[highlights]]

[[architecture]]

## Integrating Google Sheets

The client's source of truth for orders and shipments was a set of internal Google Sheets. I integrated the Google Sheets API into the Django backend and implemented a **manual synchronization** workflow that retrieves order and shipment information and brings it into the portal's business workflows.

The integration is deliberately described as manual synchronization, not real-time: data is brought into the portal when a sync is run, rather than on every spreadsheet edit.

## Access, roles, and audit tracking

The portal serves administrators, staff, customers, and other authorized users, each with access appropriate to their role. Authentication and role-based access control are built into the backend.

Key activity is recorded for traceability — including logins and create, update, and delete operations — so administrators can review what happened and when.

## Notifications

The platform supports multi-channel notification workflows that keep users informed of relevant updates across the portal.

## Deployment and infrastructure

I planned and managed the production deployment of the Django application on AWS:

- **EC2** for application hosting
- **RDS (PostgreSQL)** as the managed relational database
- **S3** for object storage
- **Nginx** as the web server and reverse proxy
- **Gunicorn** as the application server running Django

## Working with the client

I was the primary technical point of contact throughout: clarifying requirements, translating business needs into application workflows, advising on technical options, and planning delivery. Owning both the conversations and the code meant decisions could be made with the whole system in view.

## Engineering takeaways

- **Own the lifecycle.** Carrying a project from the first requirements conversation to the production server changes how you design — deployment, access control, and auditing become part of the plan, not afterthoughts.
- **Fit existing workflows.** Integrating with the spreadsheets the business already relied on, instead of replacing them, let the portal slot into how the company worked.
- **Design traceability in early.** Audit tracking and role-based access are far easier to build in from the start than to retrofit.
