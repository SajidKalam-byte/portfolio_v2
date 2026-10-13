<?php

/*
|--------------------------------------------------------------------------
| Portfolio configuration
|--------------------------------------------------------------------------
|
| Site identity and defaults. Anything marked "overridable" can also be
| changed at runtime from the admin Settings screen (stored in the
| `settings` table); the values here are the fallbacks.
|
*/

return [

    'identity' => [
        'name' => env('PORTFOLIO_NAME', 'Sajid Kalam'),
        'short_title' => env('PORTFOLIO_SHORT_TITLE', 'Software Engineer'),
        'job_title' => env('PORTFOLIO_JOB_TITLE', 'Associate Engineer · Full-Stack Web Developer'),
        'tagline' => 'Backend thinking. Full-stack execution.',
        'description' => 'Sajid Kalam is a software engineer in Siliguri, India, building production web systems with Python, Django, REST APIs, and AWS — from a healthcare EMR platform to an end-to-end B2B customer portal.',
        'locality' => 'Siliguri',
        'region' => 'West Bengal',
        'country_code' => 'IN',
        'timezone_label' => 'IST (UTC+05:30)',
        // Optional absolute or public-relative path to a profile photo. Leave empty to omit.
        'photo' => env('PORTFOLIO_PHOTO'),
    ],

    // Overridable defaults (admin → Settings).
    'defaults' => [
        'contact_email' => env('PORTFOLIO_PUBLIC_EMAIL', 'sajid.kalam.dev@gmail.com'),
        'show_email' => (bool) env('PORTFOLIO_SHOW_EMAIL', true),
        'phone' => env('PORTFOLIO_PHONE'),
        'show_phone' => (bool) env('PORTFOLIO_SHOW_PHONE', false),
        'location' => env('PORTFOLIO_LOCATION', 'Siliguri, West Bengal, India'),
        'status_text' => env('PORTFOLIO_STATUS_TEXT', 'Associate Engineer at Corunit Media Corp'),
        'open_to_work' => (bool) env('PORTFOLIO_OPEN_TO_WORK', false),
        'open_to_work_text' => env('PORTFOLIO_OPEN_TO_WORK_TEXT', 'Open to backend & full-stack opportunities'),
        'github_url' => env('PORTFOLIO_GITHUB_URL', 'https://github.com/SajidKalam-byte'),
        'linkedin_url' => env('PORTFOLIO_LINKEDIN_URL', 'https://www.linkedin.com/in/sajid-kalam/'),
        // Path relative to the public directory (public/ or public_html/).
        'resume_path' => env('PORTFOLIO_RESUME_PATH', 'files/sajid-kalam-resume.pdf'),
        // Optional external resume link used only when the local PDF is missing.
        'resume_external_url' => env('PORTFOLIO_RESUME_URL'),
        // Adds `worksFor` to the Person structured data. Enable once confirmed.
        'schema_works_for' => (bool) env('PORTFOLIO_SCHEMA_WORKS_FOR', false),
    ],

    'employer' => [
        'name' => 'Corunit Media Corp',
        'url' => env('PORTFOLIO_EMPLOYER_URL'),
    ],

    'contact' => [
        // Where new-message notifications are sent. Leave empty to only store messages.
        'notify_email' => env('PORTFOLIO_NOTIFY_EMAIL'),
        // Messages allowed per IP per hour before the form asks visitors to wait.
        'max_per_hour' => (int) env('PORTFOLIO_CONTACT_MAX_PER_HOUR', 5),
        // Submissions faster than this (seconds after page load) are treated as bots.
        'min_seconds' => (int) env('PORTFOLIO_CONTACT_MIN_SECONDS', 3),
    ],

    'admin' => [
        'enabled' => (bool) env('PORTFOLIO_ADMIN_ENABLED', true),
        'path' => trim(env('PORTFOLIO_ADMIN_PATH', 'admin'), '/'),
    ],

    'seo' => [
        'default_image' => 'images/og/default.png',
        'locale' => 'en_IN',
        'twitter_handle' => env('PORTFOLIO_TWITTER_HANDLE'),
        // Search Console's HTML-tag verification token — Settings → Ownership verification.
        'google_site_verification' => env('PORTFOLIO_GOOGLE_SITE_VERIFICATION'),
        // Serve `Disallow: /` and noindex headers outside production.
        'block_non_production' => (bool) env('PORTFOLIO_BLOCK_NON_PRODUCTION_INDEXING', true),
    ],

    'security' => [
        'csp' => (bool) env('PORTFOLIO_CSP', true),
    ],

    'analytics' => [
        // Google Analytics 4 measurement ID (G-XXXXXXXXXX). Only loads in production.
        'google_measurement_id' => env('PORTFOLIO_GA_MEASUREMENT_ID'),
    ],

    // Seconds to cache rendered content queries. Admin edits clear the cache immediately.
    'cache_ttl' => (int) env('PORTFOLIO_CACHE_TTL', 3600),

    'uploads' => [
        // Directory inside the public path where admin uploads are written.
        'directory' => 'uploads',
        'max_image_kb' => 2048,
        'max_resume_kb' => 5120,
    ],

    'skill_categories' => [
        'backend' => [
            'label' => 'Backend & programming',
            'description' => 'Server-side logic, APIs, and the languages I work in.',
        ],
        'frontend' => [
            'label' => 'Frontend & mobile',
            'description' => 'Interfaces that make complex workflows usable.',
        ],
        'data-ml' => [
            'label' => 'Data & machine learning',
            'description' => 'Applied NLP and supervised text classification.',
        ],
        'databases' => [
            'label' => 'Databases',
            'description' => 'Relational modelling, migrations, and data access.',
        ],
        'engineering' => [
            'label' => 'Engineering & deployment',
            'description' => 'Security, integrations, and running software in production.',
        ],
    ],

    'project_categories' => [
        'healthcare' => 'Healthcare software',
        'business-platform' => 'Business platform',
        'machine-learning' => 'Machine learning',
        'content-platform' => 'Content platform',
        'web-app' => 'Web application',
        'education' => 'Education',
        'hackathon' => 'Hackathon',
        'client-work' => 'Client work',
    ],

    'project_types' => [
        'professional' => 'Professional',
        'client' => 'Client project',
        'internship' => 'Internship',
        'personal' => 'Personal project',
        'academic' => 'Academic project',
        'hackathon' => 'Hackathon',
    ],

];
