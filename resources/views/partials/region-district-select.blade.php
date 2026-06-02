{{--
    @param string $mkoaName   - form field name for region
    @param string $wilayaName - form field name for district
    @param string $mkoaId     - HTML id for region select
    @param string $wilayaId   - HTML id for district select
    @param string|null $oldMkoa
    @param string|null $oldWilaya
--}}
<div class="col-md-6">
    <div class="form-group">
        <label class="control-label">Mkoa</label>
        <select class="form-control tz-mkoa-select" id="{{ $mkoaId }}" name="{{ $mkoaName }}"
            data-wilaya-target="{{ $wilayaId }}" data-old-wilaya="{{ $oldWilaya ?? '' }}">
            <option value="">-- Chagua Mkoa --</option>
            @foreach($tzRegionNames as $region)
                <option value="{{ $region }}" {{ ($oldMkoa ?? '') === $region ? 'selected' : '' }}>
                    {{ $region }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="control-label">Wilaya</label>
        <select class="form-control tz-wilaya-select" id="{{ $wilayaId }}" name="{{ $wilayaName }}">
            <option value="">-- Chagua Mkoa kwanza --</option>
        </select>
    </div>
</div>
