<article class="mediation-post">
    {!! $thumbnail !!}
    <br>
    <time class="updated"
          datetime="{{ $date }}">{{  $date }}</time>
    <h3>{{ $title }}</h3>
    <p>{!! $content !!}</p>
    <a href="{{ $link }}">Lire la suite</a>
</article>
