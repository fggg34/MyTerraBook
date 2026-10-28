@props(['name', 'size' => 20])

@php
    $file = public_path('admin-redesign/icons/'.$name.'.svg');
    $svg = is_file($file) ? (string) file_get_contents($file) : '';
    if ($svg !== '') {
        $svg = str_replace(['stroke="#5B6B7D"', 'stroke="#041E37"'], 'stroke="currentColor"', $svg);
        $svg = preg_replace('/\swidth="(?:20|24)"/', ' width="'.$size.'"', $svg, 1);
        $svg = preg_replace('/\sheight="(?:20|24)"/', ' height="'.$size.'"', $svg, 1);
    }
@endphp

@if ($svg !== '')
    <span {{ $attributes->class('tb-ico') }}>{!! $svg !!}</span>
@endif
