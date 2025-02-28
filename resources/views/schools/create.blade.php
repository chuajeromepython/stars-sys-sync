@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript">
         $('#dt_school').dataTable({
            'language':{
                'zeroRecords': '<span class="badge text-white bg-danger">No Records Found</span>',
            }
        });
    </script>
@endsection

@section('content')
    @include('layouts.message')

    <form method="post" action="/schools/store" class="form">
        @csrf()
        <div class="card">
            <div class="card-header">
                <label class="text-primary"><i class="fa fa-plus"></i> Add School</label>
            </div>
            <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="text-muted">District</label>
                            <select class="select2bs4 form-control" name="district_id">
                                @foreach($districts as $district)
                                    <option value="{{$district->id}}">{{$district->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted">Category</label>
                            <select class="select2bs4 form-control" name="category_id">
                                <option value="1">Public</option>
                                <option value="2">Private</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted">Type</label>
                            <select class="select2bs4 form-control" name="type_id">
                                @foreach($types as $type)
                                    <option value="{{$type->id}}">{{$type->type}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="text-danger">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="School Code here...">
                        </div>
                        <div class="col-md-10">
                            <label class="text-muted">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="School Name here...">
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted">Address</label>
                            <textarea class="form-control" rows="2" name="address"></textarea>
                        </div>
                    </div>
            </div>
            <div class="card-footer">
                <center>
                    <a href="/schools/create" class="btn btn-danger">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fa fa-check"></i> Save
                    </a>
                </center>
            </div>
        </div>
    </form>
@endsection








