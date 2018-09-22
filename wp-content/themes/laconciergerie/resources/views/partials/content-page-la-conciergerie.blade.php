@php the_content() @endphp

@foreach($team as $member)
    <h2>{{ $member->name }}</h2>
    <p>{{ $member->role }}</p>
    <p>{{ $member->email }}</p>
@endforeach

@foreach($partners as $partner)
    <h3><a target="_blank" href="{{ $partner->website }}">{{ $partner->name }}</a></h3>
    <img src="{{ $partner->logo->url }}" alt="{{ $partner->name }}"/>
@endforeach