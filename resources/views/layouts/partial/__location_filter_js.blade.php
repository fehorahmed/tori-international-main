<script>
    $(function () {
        $('#division_id').change(function () {
            let divisionId = $(this).val();
            $('#district_id').html('<option value="">All Districts</option>');
            $('#upazila_id').html('<option value="">All Upazilas</option>');
            $('#union_id').html('<option value="">All Unions</option>');
            $('#village_id').html('<option value="">All Villages</option>');

            if (divisionId != '') {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_districts') }}",
                    data: { divisionId: divisionId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#district_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                });
            }
            $(document).trigger('location_filter_changed');
        });

        $('#district_id').change(function () {
            let districtId = $(this).val();
            $('#upazila_id').html('<option value="">All Upazilas</option>');
            $('#union_id').html('<option value="">All Unions</option>');
            $('#village_id').html('<option value="">All Villages</option>');

            if (districtId != '') {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_upazilas') }}",
                    data: { districtId: districtId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#upazila_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                    });
                });

                // Load Pourashavas
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_pourashavas') }}",
                    data: { districtId: districtId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#union_id').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                    });
                });
            }
            $(document).trigger('location_filter_changed');
        });

        $('#upazila_id').change(function () {
            let upazilaId = $(this).val();
            let districtId = $('#district_id').val();
            
            $('#union_id').html('<option value="">All Unions</option>');
            $('#village_id').html('<option value="">All Villages</option>');
            
            if (districtId != '') {
                // Load Pourashavas first
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_pourashavas') }}",
                    data: { districtId: districtId }
                }).done(function( data ) {
                    $.each(data, function( index, item ) {
                        $('#union_id').append('<option value="pourashava_'+item.id+'">'+item.name+' (P)</option>');
                    });
                    
                    // Then load Unions
                    if (upazilaId != '') {
                        $.ajax({
                            method: "GET",
                            url: "{{ route('get_unions') }}",
                            data: { upazilaId: upazilaId }
                        }).done(function( data ) {
                            $.each(data, function( index, item ) {
                                $('#union_id').append('<option value="union_'+item.id+'">'+item.name+' (U)</option>');
                            });
                        });
                    }
                });
            }
            $(document).trigger('location_filter_changed');
        });

        $('#union_id').change(function () {
            let val = $(this).val();
            let targetEl = $('#village_id');
            targetEl.html('<option value="">All Villages</option>');
            
            if (val) {
                let data = {};
                if (val.includes('union_')) {
                    data.unionId = val.replace('union_', '');
                } else if (val.includes('pourashava_')) {
                    data.pourashavaId = val.replace('pourashava_', '');
                }
                
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_villages') }}",
                    data: data
                }).done(function (response) {
                    $.each(response, function (index, item) {
                        targetEl.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                });
            }
            $(document).trigger('location_filter_changed');
        });

        $('#village_id').change(function () {
            $(document).trigger('location_filter_changed');
        });
    });
</script>
