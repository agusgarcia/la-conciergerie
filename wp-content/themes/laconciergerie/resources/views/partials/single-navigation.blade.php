@php($prevPost = $posts[0])
@php($nextPost = $posts[1])

<div class="post__navigation">
    @if($prevPost)
        <a class="post__previous" style="--page-color:{{ get_post($prevPost)->color }}"
           href="{{ get_post_permalink($prevPost) }}"> {{ \App\Controllers\SingleExhibition::getTitle($prevPost) }} <span>Avant</span></a>
    @endif

    @if($nextPost)
        <a class="post__next" style="--page-color:{{  get_post($nextPost)->color }}"
           href="{{ get_post_permalink($nextPost) }}">{{  \App\Controllers\SingleExhibition::getTitle($nextPost)  }} <span>Après</span> </a>
    @endif
</div>