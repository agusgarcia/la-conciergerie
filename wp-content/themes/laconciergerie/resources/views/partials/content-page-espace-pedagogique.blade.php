@php the_content() @endphp

{!! $educational_resources->intro !!}

@foreach($educational_resources->resources_list as $resource)
    ↓ <a href="{{ $resource->document->url }}" title="{{ $resource->document->title }}" download>{{ $resource->document->title }}</a>
@endforeach

@foreach($images as $image)
    <figure>
        <img src="{{ $image->sizes->thumbnail }}" alt="{{ $image->alt }}">
        <figcaption>{{ $image->caption }}</figcaption>
    </figure>
@endforeach

<article class="mediation">
    <h2>Mediation</h2>
    @foreach($mediation_posts as $post)
        @php($post_id = $post->ID)
        @php($post_content = (get_post($post_id)))

        <article class="mediation-post">
            {!! get_the_post_thumbnail($post_id) !!}
            <time class="updated"
                  datetime="{{ get_post_time('c', true, $post_id) }}">{{ get_the_date('', $post_id) }}</time>
            <h3>{{ $post->post_title }}</h3>
            <p>{!! $post->post_content !!}</p>
            <a href="{{ get_permalink($post_content) }}">Lire la suite</a>
        </article>
    @endforeach

    Voir toutes les visites
</article>
