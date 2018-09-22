<div>
    {!! $thumbnail !!}

    <br>
    <time class="updated"
    datetime="{{ $date }}">{{  $date }}</time>
    <h2>{{ $title }}</h2>
    <p>{!! $content !!}</p>
    <a href="{{ $link }}">Voir plus</a>
</div>