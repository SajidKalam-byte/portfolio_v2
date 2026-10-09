<?php

namespace Database\Seeders;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Skill;
use Illuminate\Database\Seeder;

/**
 * Skills, experience, and education. Idempotent.
 */
class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->skills();
        $this->experiences();
        $this->educations();
    }

    private function skills(): void
    {
        $skills = [
            'backend' => ['Python', 'Django', 'Django REST Framework', 'SQL', 'JavaScript', 'Java', 'C', 'C++'],
            'frontend' => ['HTML5', 'CSS3', 'JavaScript', 'Bootstrap', 'Tailwind CSS', 'Responsive UI development', 'React Native / Expo'],
            'data-ml' => ['pandas', 'NumPy', 'scikit-learn', 'NLTK', 'SVM', 'Logistic Regression', 'TF-IDF', 'SMOTE', 'NLP & text classification'],
            'databases' => ['PostgreSQL', 'MySQL', 'SQLite', 'Relational data modelling', 'Database migrations', 'Query optimization'],
            'engineering' => [
                'REST API design', 'Authentication & authorization', 'Role-based access control', 'Audit logging',
                'API documentation', 'Third-party API integration', 'Git & GitHub', 'AWS (EC2, RDS, S3)', 'Nginx', 'Gunicorn',
                'Background task scheduling', 'Production debugging', 'Environment configuration',
            ],
        ];

        foreach ($skills as $category => $names) {
            foreach ($names as $index => $name) {
                Skill::updateOrCreate(
                    ['category' => $category, 'name' => $name],
                    ['sort_order' => ($index + 1) * 10, 'is_visible' => true],
                );
            }
        }
    }

    private function experiences(): void
    {
        Experience::updateOrCreate(
            ['company' => 'Corunit Media Corp', 'role' => 'Associate Engineer · Full-Stack Web Developer'],
            [
                'company_url' => null,
                'employment_type' => null,
                'location' => null,
                'start_date' => '2025-03-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Building and evolving a production Electronic Medical Record (EMR) and patient intake platform used for clinical documentation, scheduling, billing, and practice administration. My scope has grown from frontend work into backend engineering, database design, API development, deployment, and production support.',
                'highlights' => [
                    ['title' => 'Product engineering', 'items' => [
                        'Developed and evolved features across a full-stack healthcare platform built with Django.',
                        'Built and improved patient intake workflows involving complex patient data structures and forms.',
                        'Developed clinical documentation workflows, including SOAP notes.',
                        'Implemented appointment scheduling functionality.',
                        'Contributed to billing and superbill workflows, authorization forms, invoices, ticketing, and administrative tools.',
                    ]],
                    ['title' => 'Backend, APIs & data protection', 'items' => [
                        'Developed REST APIs with Django REST Framework.',
                        'Implemented authentication and role-based access control.',
                        'Worked on audit logging and data-protection mechanisms.',
                        'Supported data import and migration workflows.',
                    ]],
                    ['title' => 'Production & operations', 'items' => [
                        'Investigated and resolved client-reported bugs.',
                        'Participated in production deployment, maintenance, and debugging.',
                        'Worked with AWS hosting, Nginx, scheduled background tasks, backups, SSL, DNS, and storage as part of production operations.',
                    ]],
                ],
                'tech_stack' => ['Python', 'Django', 'Django REST Framework', 'JavaScript', 'HTML/CSS', 'Bootstrap', 'SQLite', 'REST APIs', 'AWS', 'Nginx', 'Git/GitHub'],
                'related_projects' => ['emr-patient-intake-platform'],
                'sort_order' => 10,
            ],
        );

        Experience::updateOrCreate(
            ['company' => 'Personifwy', 'role' => 'Data Science Intern'],
            [
                'company_url' => null,
                'employment_type' => 'Internship',
                'location' => null,
                'start_date' => '2024-04-01',
                'end_date' => '2024-05-31',
                'is_current' => false,
                'description' => 'Built and evaluated two supervised NLP text-classification models as part of a data science internship.',
                'highlights' => [
                    ['title' => 'SMS spam classification', 'items' => [
                        'Built a spam/ham classifier using TF-IDF features and a Support Vector Machine.',
                        'Tuned hyperparameters with GridSearchCV.',
                        'Reported 98% classification accuracy on the project’s evaluation data.',
                    ]],
                    ['title' => 'Hate speech detection', 'items' => [
                        'Built a Logistic Regression classifier on preprocessed text.',
                        'Addressed class imbalance with SMOTE.',
                        'Reported 92% classification accuracy.',
                    ]],
                ],
                'tech_stack' => ['Python', 'scikit-learn', 'SVM', 'TF-IDF', 'GridSearchCV', 'Logistic Regression', 'SMOTE'],
                'related_projects' => ['sms-spam-classification', 'hate-speech-detection'],
                'sort_order' => 20,
            ],
        );
    }

    private function educations(): void
    {
        // Graduation date for the MCA is unconfirmed, so no dates are stored.
        Education::updateOrCreate(
            ['institution' => 'Siliguri Institute of Technology', 'degree' => 'Master of Computer Applications (MCA)'],
            [
                'field_of_study' => null,
                'start_date' => null,
                'end_date' => null,
                'status' => 'Pursuing',
                'grade' => null,
                'description' => null,
                'sort_order' => 10,
            ],
        );

        Education::updateOrCreate(
            ['institution' => 'University of North Bengal', 'degree' => 'Bachelor of Computer Applications (BCA)'],
            [
                'field_of_study' => null,
                'start_date' => '2021-01-01',
                'end_date' => '2024-12-31',
                'status' => 'Completed',
                'grade' => 'CGPA 9.31/10',
                'description' => null,
                'sort_order' => 20,
            ],
        );
    }
}
