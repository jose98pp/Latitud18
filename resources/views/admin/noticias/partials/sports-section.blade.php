<div id="sports-section-fields" class="form-group mb-3" hidden>
    <label for="seccion_deportiva" class="form-label fw-bold">Sección de Contra Ataque <span class="text-danger">*</span></label>
    <select id="seccion_deportiva" name="seccion_deportiva" class="form-control @error('seccion_deportiva') is-invalid @enderror">
        <option value="">Selecciona dónde aparecerá la noticia</option>
        @foreach(\App\Support\SportsSections::LABELS as $key => $label)
            <option value="{{ $key }}" @selected(old('seccion_deportiva', $noticia->seccion_deportiva ?? '') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="form-text">Se mostrará en la portada de Contra Ataque y en esta sección cuando esté publicada. El Ojo de Contraataque se gestiona desde Opinión &amp; Columnistas.</div>
    @error('seccion_deportiva')<div class="text-danger">{{ $message }}</div>@enderror
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const category = document.getElementById('category_id');
    const fields = document.getElementById('sports-section-fields');
    const section = document.getElementById('seccion_deportiva');
    function updateSportsSection() {
        const sports = category.selectedOptions[0]?.dataset.sports === '1';
        fields.hidden = !sports;
        section.disabled = !sports;
        section.required = sports;
        if (!sports) section.value = '';
    }
    category.addEventListener('change', updateSportsSection);
    updateSportsSection();
});
</script>
