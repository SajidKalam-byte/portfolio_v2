<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    private string $relativePath = 'files/testing-resume.pdf';

    protected function setUp(): void
    {
        parent::setUp();

        config(['portfolio.defaults.resume_path' => $this->relativePath, 'portfolio.defaults.resume_external_url' => null]);
    }

    protected function tearDown(): void
    {
        File::delete(public_path($this->relativePath));

        parent::tearDown();
    }

    public function test_missing_pdf_hides_download_and_offers_request_instead(): void
    {
        $this->get('/resume')
            ->assertOk()
            ->assertDontSee(route('resume.download'))
            ->assertSee('Request a copy');

        $this->get('/')->assertDontSee('Download resume');
    }

    public function test_download_route_redirects_with_notice_when_pdf_is_missing(): void
    {
        $this->get('/resume/download')
            ->assertRedirect('/resume')
            ->assertSessionHas('notice');
    }

    public function test_existing_pdf_is_downloadable_with_a_friendly_filename(): void
    {
        File::ensureDirectoryExists(public_path('files'));
        File::put(public_path($this->relativePath), '%PDF-1.4 test');

        $this->get('/resume')->assertSee(route('resume.download'));
        $this->get('/')->assertSee('Download resume');

        $this->get('/resume/download')
            ->assertOk()
            ->assertDownload('Sajid-Kalam-Resume.pdf');
    }

    public function test_paths_outside_the_public_directory_are_never_served(): void
    {
        config(['portfolio.defaults.resume_path' => '../.env.pdf']);

        $this->get('/resume/download')->assertRedirect('/resume');
    }

    public function test_external_resume_url_is_used_as_fallback(): void
    {
        config(['portfolio.defaults.resume_external_url' => 'https://example.com/resume.pdf']);

        $this->get('/resume/download')->assertRedirect('https://example.com/resume.pdf');
    }
}
