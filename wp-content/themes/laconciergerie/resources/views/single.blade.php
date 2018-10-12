@extends('layouts.app')

@section('content')
    {{--<a href="/">La conciergerie</a> > <a
            href="{{ get_post_type_archive_link(get_post_type()) }}">{{ get_post_type_object(get_post_type())->labels->name }}</a>--}}
    @while(have_posts()) @php the_post() @endphp
    @include('partials.content-single-'.get_post_type())
    @endwhile
@endsection
