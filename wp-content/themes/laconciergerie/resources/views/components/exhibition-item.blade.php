<article class="item" style="--color: {{ $color }}">
    <a class="item__link" href="{{ $link }}">
        {!! wp_get_attachment_image($thumbnail['ID'], 'medium_large') !!}

        <div class="item__main">
            <h3 class="item__title">{{ $artist_name }}</h3>
            <h4 class="item__subtitle">"{{ $exhibition_title }}"</h4>
            @if($post->start_date)
                <p>Vernissage le {{ App::formattedDateWithDay($opening_date) }}
                    à {{ $preview_hour }}</p>
                <p>Du {{ App::formattedDateNoYear($post->start_date) }}
                    au {{ App::formattedDate($post->closing_date) }}</p>
            @endif
        </div>
    </a>
    <a href="{{ $link }}" class="button">Voir plus</a>
</article>
