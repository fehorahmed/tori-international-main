<script>
$(function () {
    const error = $('#nominee-location-error');
    function clear(fields) {
        fields.forEach(function (id) {
            const select = $('#'+id);
            select.find('option:not(:first)').remove();
            select.val('').trigger('change.select2');
        });
        error.addClass('d-none');
    }
    function append(id, items, prefix = '', suffix = '') {
        items.forEach(function (item) { $('#'+id).append(new Option(item.name + suffix, prefix + item.id)); });
        $('#'+id).trigger('change.select2');
    }
    $('#nominee_division').on('change', function () {
        const id = $(this).val();
        clear(['nominee_district', 'nominee_upazila', 'nominee_union', 'nominee_village_id']);
        if (!id) return;
        $.getJSON(@json(route('get_districts')), {divisionId: id}).done(function (items) {
            if ($('#nominee_division').val() === id) append('nominee_district', items);
        }).fail(function () { error.removeClass('d-none'); });
    });
    $('#nominee_district').on('change', function () {
        const id = $(this).val();
        clear(['nominee_upazila', 'nominee_union', 'nominee_village_id']);
        if (!id) return;
        $.getJSON(@json(route('get_upazilas')), {districtId: id}).done(function (items) {
            if ($('#nominee_district').val() === id) append('nominee_upazila', items);
        }).fail(function () { error.removeClass('d-none'); });
    });
    $('#nominee_upazila').on('change', function () {
        const id = $(this).val(), district = $('#nominee_district').val();
        clear(['nominee_union', 'nominee_village_id']);
        if (!id || !district) return;
        $.when($.getJSON(@json(route('get_pourashavas')), {districtId: district}), $.getJSON(@json(route('get_unions')), {upazilaId: id}))
            .done(function (municipalities, unions) {
                if ($('#nominee_upazila').val() !== id || $('#nominee_district').val() !== district) return;
                append('nominee_union', municipalities[0], 'pourashava_', ' (P)');
                append('nominee_union', unions[0], 'union_', ' (U)');
            }).fail(function () { error.removeClass('d-none'); });
    });
    $('#nominee_union').on('change', function () {
        const value = $(this).val();
        clear(['nominee_village_id']);
        if (!value || !value.startsWith('union_')) return;
        $.getJSON(@json(route('get_villages')), {unionId: value.slice(6)}).done(function (items) {
            if ($('#nominee_union').val() === value) append('nominee_village_id', items);
        }).fail(function () { error.removeClass('d-none'); });
    });
    $('#btnPreview').on('click', function () {
        ['nominee_name', 'nominee_relation', 'nominee_nid', 'nominee_phone', 'nominee_address'].forEach(function (field) {
            $('#preview_'+field).text($('#'+field).val() || '—');
        });
        $('#preview_nominee_location').text(['nominee_village_id', 'nominee_union', 'nominee_upazila', 'nominee_district', 'nominee_division']
            .filter(function (field) { return $('#'+field).val(); }).map(function (field) { return $('#'+field+' option:selected').text(); }).join(', ') || '—');
    });
});
</script>
