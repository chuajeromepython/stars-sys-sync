@if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
@endif

@if (session('success'))
    <div class="alert alert-success" role="alert">
        <button class="close" type="button" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <strong class="text-capitalize">Success!</strong><br>

        {{ session('success') }}
        
    </div>
@endif

