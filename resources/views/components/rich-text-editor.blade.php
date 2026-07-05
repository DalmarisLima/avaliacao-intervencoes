@props([
    'name',
    'id' => null,
    'value' => '',
    'required' => false,
    'placeholder' => 'Digite aqui…',
    'compact' => false,
])

@php
    $inputId = $id ?? 'rt-'.\Illuminate\Support\Str::slug($name, '-');
    $areaId = $inputId.'-editor';
@endphp

@once
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/rich-text.css') }}?v={{ @filemtime(public_path('css/rich-text.css')) ?: time() }}">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
        <script src="{{ asset('js/rich-text-editor.js') }}?v={{ @filemtime(public_path('js/rich-text-editor.js')) ?: time() }}"></script>
    @endpush
@endonce

<div {{ $attributes->merge(['class' => 'rich-text-field'.($compact ? ' rich-text-field--compact' : '')]) }}
     data-rich-text
     data-placeholder="{{ $placeholder }}"
     title="Use o botão @ para inserir e-mail com link mailto">
    <div id="{{ $areaId }}" data-rich-text-area></div>
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $inputId }}"
           data-rich-text-input
           value="{{ $value }}"
           @if($required) required @endif>
</div>
