<article class="item" style="--color: {{ $color }}">
    <a class="item__link" href="{{ $link }}">
        {!! wp_get_attachment_image($thumbnail['ID'], 'medium') !!}
        <div class="item__main">
            <h3 class="item__title">{{ $title }}</h3>
            @php($date = App::formattedDateWithDay($opening_date))
            <h4 class="item__subtitle">{{ $date }} {{ $hour }}</h4>
            <p>{{ $place }}</p>
        </div>
    </a>
    <a href="{{ $link }}" class="button">Voir plus</a>
</article>

