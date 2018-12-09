@extends('layouts.app')

@section('content')
    @if(get_post_type() !== 'exhibition' && get_post_type() !== 'event')
        <ol class="breadcrumb" vocab="http://schema.org/" typeof="BreadcrumbList">
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage"
                   href="/">
                    <span property="name">La Conciergerie</span></a>
                <meta property="position" content="1">
            </li>
            ›
            <li property="itemListElement" typeof="ListItem">
                <a property="item" typeof="WebPage"
                   href="{{ get_post_type_archive_link(get_post_type()) }}">
                    <span property="name">{{ get_post_type_object(get_post_type())->labels->name }}</span></a>
                <meta property="position" content="2">
            </li>
        </ol>
    @endif
    @while(have_posts()) @php the_post() @endphp
    @include('partials.content-single-'.get_post_type())

    @endwhile
@endsection
