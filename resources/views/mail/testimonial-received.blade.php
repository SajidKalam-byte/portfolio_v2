{{-- Plain-text email: raw output is intentional (no HTML is rendered, so entities would show literally). --}}
New testimonial received — it is NOT published until you approve it.

From:    {!! $testimonial->author_name !!}{!! $testimonial->author_title ? ', '.$testimonial->author_title : '' !!}{!! $testimonial->author_company ? ' — '.$testimonial->author_company : '' !!}
Name shown publicly: {{ $testimonial->show_name ? 'yes' : 'no (role/company only)' }}

----------------------------------------------------------------------

{!! $testimonial->quote !!}

----------------------------------------------------------------------

@if (Route::has('admin.testimonials.edit'))
Review and publish it here:
{{ route('admin.testimonials.edit', $testimonial) }}
@endif
