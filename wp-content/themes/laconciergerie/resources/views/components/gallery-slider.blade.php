<div class="gallery__slider swiper-container row">
    <p class="slider__title">{{ $slider->slider_title }}</p>
    <div class="swiper-wrapper">
        @foreach($slider->slider_images as $image)
            <div class="swiper-slide">
                <figure>
                    <div class="image__container">
                        @if($image->type === 'video')
                            <video src="{{ $image->url }}" controls></video>
                        @else
                            <img src="{{ $image->sizes->large }}" alt="{{ $image->alt }}">
                        @endif
                    </div>
                    <figcaption>{{ $image->caption }}</figcaption>
                </figure>
            </div>
        @endforeach
    </div>
    <div class="arrows arrows--big">
        <svg class="arrow arrow--reverse swiper-button-prev" xmlns="http://www.w3.org/2000/svg"
             xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
            <g transform="translate(0,-952.36218)">
                <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"
                      stroke="none" marker="none" visibility="visible"
                      display="inline"
                      overflow="visible"></path>
            </g>
        </svg>
        <svg class="arrow swiper-button-next" xmlns="http://www.w3.org/2000/svg"
             xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 100">
            <g transform="translate(0,-952.36218)">
                <path d="m 49.99997,1018.5184 2.59376,-2.2188 28,-23.99996 -5.1875,-6.0937 -25.40626,21.78116 -25.4062,-21.78116 -5.1875,6.0937 28,23.99996 2.5937,2.2188 z"
                      stroke="none" marker="none" visibility="visible"
                      display="inline"
                      overflow="visible"></path>
            </g>
        </svg>
    </div>
</div>
