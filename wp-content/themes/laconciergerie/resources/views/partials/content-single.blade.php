<article @php post_class() @endphp>
    <header>
        <h1 class="entry-title">{{ get_the_title() }}</h1>
        {!! get_the_post_thumbnail() !!}
        @include('partials/entry-meta')
    </header>
    <div class="entry-content">
        @php the_content() @endphp
    </div>
</article>
