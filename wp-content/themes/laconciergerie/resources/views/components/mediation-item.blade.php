<article class="mediation-single {{ $row }}">
    <div class="mediation__thumb">{!! $thumbnail !!}</div>
    <div class="mediation__text">
        <time class="mediation__date"
              datetime="{{ $date }}">{{  $date }}</time>
        <h3 class="mediation__title"><a href="{{ $link }}">{{ html_entity_decode($title) }}</a></h3>
        <div class="mediation__content">
            {!! mb_substr($content,0, 400) !!}
            @if(mb_strlen($content) > 400)
                ...
            @endif
        </div>
        {{--@if(strlen($content) > 750)--}}
        <a class="button mediation__button" href="{{ $link }}">Lire la suite</a>
        {{--@endif--}}
    </div>
</article>
