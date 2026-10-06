@props([
    'location',
    'maxWidth' => 970,
    'label' => null,
    'ratio' => null,
    'banners' => null,
])

@php
    $items = ($banners[$location] ?? collect());
    $label = $label ?? 'Publicidad';
@endphp

@if($items->count() > 0)
    <div class="ad-slot" data-ad-location="{{ $location }}"
         style="display:flex;flex-direction:column;align-items:center;gap:10px;margin:24px auto;padding:12px;background:var(--color-navy-subtle);border:1px solid var(--color-border);border-radius:4px">
        <span class="ad-slot__label"
              style="font-family:var(--font-title-montserrat,sans-serif);font-size:0.65rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--color-text-muted)">{{ $label }}</span>
        @foreach($items as $banner)
            <a href="{{ $banner->link ?: '#' }}"
               @if($banner->link) target="_blank" rel="noopener sponsored" @endif
               class="ad-slot__link"
               style="display:block;width:100%;max-width:{{ $maxWidth }}px;line-height:0">
                <img src="{{ asset($banner->image_path) }}"
                     alt="{{ $banner->title }}"
                     class="ad-slot__img"
                     style="width:100%;height:auto;max-height:{{ $location === 'sidebar' ? '420' : '200' }}px;@if($ratio) aspect-ratio:{{ $ratio }};object-fit:@if($location === 'sidebar') cover @else contain @endif; @endif border-radius:2px;box-shadow:0 2px 8px rgba(0,0,0,.08)"
                     loading="lazy">
            </a>
        @endforeach
    </div>
@endif
