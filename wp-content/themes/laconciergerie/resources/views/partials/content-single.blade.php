<article class="row">
    <header>
        <div class="title__container">
            <h1 class="title">{{ html_entity_decode(get_the_title()) }}</h1>
        </div>
        @include('partials/entry-meta')
    </header>

    <div class="single__thumb">
        {!! get_the_post_thumbnail('', 'large') !!}
    </div>
    <div class="single__content">
        @php the_content() @endphp
    </div>
</article>

@if($slider->slider_images)
    @include('components.gallery-slider', $slider->slider_images)
@endif