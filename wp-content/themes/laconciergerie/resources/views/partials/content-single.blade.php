<article class="row">
    <header>
        <div class="title__container">
            <h1 class="title">{{ get_the_title() }}</h1>
        </div>
        <div class="single__thumb">
            {!! get_the_post_thumbnail('', 'large') !!}
        </div>
        @include('partials/entry-meta')
    </header>
    <div class="single__content">
        @php the_content() @endphp
    </div>
</article>
