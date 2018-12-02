<div class="post__navigation">
    @php($prevPost = get_previous_post())
    @php($nextPost = get_next_post())

    @if($prevPost)
        <a class="post__previous"
           href="{{ get_permalink( $prevPost->ID ) }}"> <span>Précédent</span></a>
    @endif

    @if($nextPost)
        <a class="post__next"
           href="{{ get_permalink( $nextPost->ID ) }}"> <span>Suivant</span></a>
    @endif
</div>