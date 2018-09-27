{{--@dump($post)--}}
<article class="post">
    <a href="{{ $link }}">
        {!! wp_get_attachment_image($thumbnail['ID']) !!}
        <br>
        <h3>{{ $artist_name }}</h3>
        <h4>{{ $exhibition_title }}</h4>
        @php($date = App::formattedDate($opening_date))
        <time class="updated" datetime="{{ $date }}">{{ $date }}</time>
    </a>
</article>
<hr>
