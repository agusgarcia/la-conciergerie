<section class="mediation">
    <div class="title__container">
        <h2 class="title">Médiation</h2>
    </div>
    <div class="mediation__container row mediation__slider js-slider--three slider__three swiper-container">
        <div class="swiper-wrapper">
            @foreach(MediationArchive::lastPosts(3) as $post)
                <div class="swiper-slide">
                    @include('components.mediation-item', $post)
                </div>
            @endforeach
        </div>
        <div class="arrows">
            <svg class="arrow arrow-reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 512 640"
                 enable-background="new 0 0 512 512" xml:space="preserve"><polygon
                        points="169.473,0.014 86.07,83.275 259.109,256.02 86.07,428.739 169.473,511.986 425.93,256.02 "></polygon></svg>
            <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink"
                 version="1.1" x="0px" y="0px" viewBox="0 0 512 640" enable-background="new 0 0 512 512"
                 xml:space="preserve"><polygon
                        points="169.473,0.014 86.07,83.275 259.109,256.02 86.07,428.739 169.473,511.986 425.93,256.02 "></polygon></svg>
        </div>
    </div>
    <div class="row button__container">
        <a class="button button--big" href="{{  get_post_type_archive_link('mediation') }}" title="Mediation">Voir
            toutes les visites</a>
    </div>
</section>