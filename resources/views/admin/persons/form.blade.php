@php $p = $person ?? null; @endphp
<div class="grid grid-cols-2 md:grid-cols-4 gap-2">
    <div>
        <label class="text-xs text-slate-600">Display Name *</label>
        <input type="text" name="display_name" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('display_name', $p->display_name ?? '') }}" required>
    </div>
    <div>
        <label class="text-xs text-slate-600">Given Name *</label>
        <input type="text" name="given_name" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('given_name', $p->given_name ?? '') }}" required>
    </div>
    <div>
        <label class="text-xs text-slate-600">Middle Name</label>
        <input type="text" name="middle_name" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('middle_name', $p->middle_name ?? '') }}">
    </div>
    <div>
        <label class="text-xs text-slate-600">Family Name</label>
        <input type="text" name="family_name" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('family_name', $p->family_name ?? '') }}">
    </div>
    <div>
        <label class="text-xs text-slate-600">Gender *</label>
        <select name="gender" class="border rounded-lg px-3 py-2 w-full">
            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
                <option value="{{ $k }}" @selected(old('gender', $p->gender ?? 'unknown') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs text-slate-600">Photo Path</label>
        <input type="text" name="photo_path" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('photo_path', $p->photo_path ?? '') }}" placeholder="e.g. photos/ram.jpg">
    </div>
    <div>
        <label class="text-xs text-slate-600">Birth Date (BS)</label>
        <input type="text" name="birth_date" maxlength="10" placeholder="YYYY-MM-DD"
            class="border rounded-lg px-3 py-2 w-full tracking-widest"
            value="{{ old('birth_date', $p->birth_date ?? '') }}" oninput="formatDateInput(this)">
    </div>

    <div>
        <label class="text-xs text-slate-600">Death Date (BS)</label>
        <input type="text" name="death_date" maxlength="10" placeholder="YYYY-MM-DD"
            class="border rounded-lg px-3 py-2 w-full tracking-widest"
            value="{{ old('death_date', $p->death_date ?? '') }}" oninput="formatDateInput(this)">
    </div>
    <div class="flex items-center gap-1">
        <input type="checkbox" name="is_deceased" value="1" id="is_deceased" @checked(old('is_deceased', $p->is_deceased ?? false))>
        <label for="is_deceased" class="text-sm text-slate-700">Deceased</label>
    </div>
    <div class="flex items-center gap-1">
        <label class="text-xs text-slate-600">पुस्ता</label>
        <input type="text" name="pusta" class="border rounded-lg px-3 py-2 w-full"
            placeholder="पुस्ता" value="{{ old('bio', $p->pusta ?? '') }}">
    </div>

    <div class="md:col-span-2">
        <label class="text-xs text-slate-600">Bio</label>
        <textarea name="bio" rows="4" class="border rounded-lg px-3 py-2 w-full" placeholder="जीवनी...">{{ old('bio', $p->bio ?? '') }}</textarea>
    </div>
</div>
<script>
    function formatDateInput(el) {
        let val = el.value.replace(/[^0-9]/g, ''); // allow only numbers
        if (val.length > 4 && val.length <= 6) {
            val = val.slice(0, 4) + '-' + val.slice(4);
        } else if (val.length > 6) {
            val = val.slice(0, 4) + '-' + val.slice(4, 6) + '-' + val.slice(6, 8);
        }
        el.value = val;
    }
</script>
