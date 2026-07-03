@if(count($errors))

    <div class="alert alert-danger" role="alert">
        <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <strong class="text-capitalize">Oops!</strong><br>
        @foreach ($errors->all() as $error)

            {{ $error }}<br>

        @endforeach
    </div>

@else 
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            <button class="close" type="button" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <strong class="text-capitalize">Success!</strong><br>

            {{ session('success') }}
            
        </div>
    @endif

@endif
