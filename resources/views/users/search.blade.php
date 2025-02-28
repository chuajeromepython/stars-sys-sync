 <form method="get" action="/users">
    @csrf
    <div class="row mb-4">
        <div class="col-md-3">
            <small class="text-bold text-primary">Name</small>
            <input type="text" name="keyword" class="form-control" value="{{$request->keyword}}" placeholder="Keyword here...">
        </div>
        <div class="col-md-3">
            <small class="text-bold text-primary">Classification</small>
            <select class="form-control select2bs4" name="classification" id="classification">
                @foreach($classifications as $classification)
                    <option value="{{$classification->classification}}"
                        {{($request->classification == $classification->classification)  ? 'selected' : ''}}>
                        {{$classification->classification}}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <small class="text-bold text-primary">Area</small>
            <input type="hidden" name="" id="request_area" value="{{$request->area}}">
            <select class="form-control select2bs4" name="area" id="select_area"></select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-info btn-block mt-4">Search</button>
        </div>
        <div class="col-md-1">
            <a href="/users" class="btn btn-danger btn-block mt-4">Clear</a>
        </div>
    </div>
</form>