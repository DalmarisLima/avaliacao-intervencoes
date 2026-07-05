@props([
    'action',
    'label' => 'Remover',
    'message' => 'Tem certeza que deseja excluir? Esta ação não pode ser desfeita.',
])

<form method="POST" action="{{ $action }}" class="d-inline m-0 js-delete-form">
    @csrf
    @method('DELETE')
    <button type="button"
            class="action-link action-link--danger js-delete-trigger"
            data-delete-message="{{ $message }}">
        {{ $label }}
    </button>
</form>
