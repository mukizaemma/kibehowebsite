@if(session('success'))
    <div class="alert alert-success desk-alert" role="status">{{ session('success') }}</div>
@elseif(session('error'))
    <div class="alert alert-danger desk-alert" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any() && !session('error'))
    <div class="alert alert-danger desk-alert" role="alert">{{ $errors->first() }}</div>
@endif
