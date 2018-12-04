@if($quotes)
    <div class="row quotes">
        <h2 class="subtitle">Ils ont dit...</h2>
        @foreach($quotes as $quote)
            <div class="quote">
                <q>
                    {{ $quote->quote }}
                </q>
                <cite>— {{ $quote->author }}
                </cite>
            </div>
        @endforeach
    </div>
@endif