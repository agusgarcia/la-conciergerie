<article class="news-single row">
    <div class="news__thumb">{!! $thumbnail !!}</div>
    <div class="news__text">
        <h3 class="news__title"><a href="{{ $link }}">{{ $title }}</a></h3>
        <time class="news__date"
              datetime="{{ $date }}">{{  $date }}</time>
        <div class="news__content">{!! $content !!}</div>
        @if(strlen($content) > 750)
            <a class="news__button button" href="{{ $link }}">Lire la suite</a>
        @endif
    </div>
</article>