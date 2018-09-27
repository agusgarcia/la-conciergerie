<article class="post">
    <a href="{{ $link }}">
        {!! wp_get_attachment_image($thumbnail['ID']) !!}
        <br>
        <h3>{{ $title }}</h3>
        @php($date = App::formattedDateWithDay($opening_date))
        <time class="updated" datetime="{{ $date }}">{{ $date }} {{ $hour }}</time>
        <p>{{ $place }}</p>
    </a>
</article>
<hr>
