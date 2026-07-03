
@foreach($page['crumb'] as $crumb => $link)
<li class="breadcrumb-item"><a href="{{$link}}">{{$crumb}}</a></li>
@endforeach