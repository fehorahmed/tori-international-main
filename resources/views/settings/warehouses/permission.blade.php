@extends('layouts.app')
@section('title',$pageTitle)
@section('style')
    <style>
        ul{
            list-style: none;
        }
        label:not(.form-check-label):not(.custom-file-label) {
            font-weight: normal;
        }
        label.child-checkbox-label {
            font-size: 18px;
        }
        label.grandchild-checkbox-label {
            font-size: 16px;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $pageTitle }} Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('warehouses.permission.update',['type'=>request('type')]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <td class="text-left">
                                    <div class="icheck-success d-inline">
                                        <input type="checkbox" id="check-all-checkbox" class="check-all-checkbox">
                                        <label style="font-size: 20px"  for="check-all-checkbox">Check All</label>
                                    </div>
                                </td>
                            </tr>
                            @foreach($permissions as $permission)
                                @php
                                    $checked = false;
                                    if (request('type') == 0){
                                       $checked = $permission->warehouse == request('type') ? true : false;
                                    }elseif (request('type') == 1){
                                       $checked = $permission->showroom == request('type') ? true : false;
                                    }
                                 @endphp
                                <tr>
                                    <td style="font-size: 20px"  class="text-left">
                                        <div class="icheck-success">
                                            <input {{ $checked ? 'checked' : '' }} type="checkbox" id="for_label_{{ $permission->id }}" class="parent-checkbox" name="permission[]"
                                                   value="{{ $permission->id }}">
                                            <label class="parent-checkbox-label" for="for_label_{{ $permission->id }}"> {{ ucwords(str_replace('_',' ',$permission->name)) }}</label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>


        $(document).ready(function() {
            function updateParentCheckboxes() {
                $('.parent-checkbox').each(function() {
                    var $this = $(this);
                    var $childCheckboxes = $this.closest('td').find('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox');
                    var checkedChildCheckboxes = $childCheckboxes.filter(':checked');

                    if (checkedChildCheckboxes.length > 0) {
                        $this.prop('checked', true);
                    }
                });
            }

            $('.check-all-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                $('.parent-checkbox, .child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').prop('checked', isChecked);
                updateParentCheckboxes();
            });

            $('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').change(function() {
                updateParentCheckboxes();
            });

            $('.parent-checkbox').change(function() {
                var $this = $(this);
                $this.siblings('ul').find('.child-checkbox, .grandchild-checkbox, .great-grandchild-checkbox').prop('checked', this.checked);
            });

            updateParentCheckboxes();
        });

    </script>
@endsection
