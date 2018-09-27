<article class="post">
    {!! $thumbnail !!}
    <br>
    <h3>{{ $title }}</h3>
    <time class="updated"
          datetime="{{ $date }}">{{  $date }}</time>
    <p>{!! $content !!}</p>
    <a href="{{ $link }}">(Lire la suite)</a>
</article>