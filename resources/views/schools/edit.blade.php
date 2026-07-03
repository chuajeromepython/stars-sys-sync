@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
       $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#division').on('change', function() {
                var division_id =  $(this).val();
                getDistrictsPerDivision(division_id);
                
            });
        });

        function getDistrictsPerDivision(division_id) {
            $.ajax({
                url: '/getDistrictsPerDivision',
                type: "POST",
                data: {
                    "division_id" : division_id
                },
                success: function(data){

                    var options = '<option selected="" value="" > -Select District- </option>';
                    $.each(data, function(i, item) {
                       options += '<option value="'+data[i].id+'">'+data[i].name+'</option>';
                        
                        
                    });
                    $('#district').html(options);    
                    console.log(options)
                }, //end of success getMunicipalitiesByDistrict
            });
        }
    </script>
@endsection

@section('content')
    @include('layouts.message')

    <form method="post" action="/schools/update" class="form">
        @csrf()
        <div class="card">
            <div class="card-header">
                <a href="/schools" class="btn btn-danger"><i class="fa fa-angle-left mr-2"></i> Back</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="text-muted">Division</label>
                        <select class="select2bs4 form-control" name="division_id" id="division">
                            @foreach($divisions as $division)
                                <option value="{{$division->id}}"
                                    {{ ($current_division->id == $division->id) ? 'selected' : '' }}>
                                    {{$division->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-muted">District</label>
                        <select class="select2bs4 form-control" name="district_id" id="district">
                            @foreach($districts as $district)
                                <option value="{{$district->id}}"
                                    {{ ($school->district_id == $district->id) ? 'selected' : '' }}>{{$district->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-muted">Category</label>
                        <select class="select2bs4 form-control" name="category_id">
                            <option value="1" {{ ($school->school_category_id == 1) ? 'selected' : '' }}>
                                Public</option>
                            <option value="2" {{ ($school->school_category_id == 2) ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-muted">Type</label>
                        <select class="select2bs4 form-control" name="type_id">
                            @foreach($types as $type)
                                <option value="{{$type->id}}" 
                                    {{ ($school->school_type_id == $type->id) ? 'selected' : '' }}>{{$type->type}}</option>
                            @endforeach
                        </select>
                    </div>
                     <div class="col-md-2 mb-2">
                        <input type="hidden" name="id" value="{{$school->id}}">
                        <label class="text-danger">Code</label>
                        <input type="text" name="code" class="form-control" placeholder="School Code here..." value="{{$school->code}}">
                    </div>
                    <div class="col-md-10">
                        <label class="text-muted">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="School Name here..." value="{{$school->name}}">
                    </div>
                    <div class="col-md-12">
                        <label class="text-muted">Address</label>
                        <textarea class="form-control" rows="3" name="address">{{$school->address}}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <center>
                    <a href="/schools/create" class="btn btn-danger">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fa fa-check"></i> Save Changes
                    </a>
                </center>
            </div>
        </div>
    </form>
@endsection



