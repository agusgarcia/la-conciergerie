<article class="mediation-single">
    <div class="mediation__thumb">{!! $thumbnail !!}</div>
    <div class="mediation__text">
        <time class="mediation__date"
              datetime="{{ $date }}">{{  $date }}</time>
        <h3 class="mediation__title"><a href="{{ $link }}">{{ $title }}</a> </h3>
        <div class="mediation__content">{!! $content !!}</div>
        {{--@if(strlen($content) > 750)--}}
            <a class="button mediation__button" href="{{ $link }}">Lire la suite</a>
        {{--@endif--}}
    </div>
</article>
