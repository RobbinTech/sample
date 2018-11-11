@extends('layouts.default')

@section('content')
  <div class="jumbotron">
    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif

    <h1>Hello Laravel</h1>
    <p class="lead">
      你现在所看到的是 <a href="#">测试</a> 的项目主页。
    </p>
    <p>
      一切，将从这里开始。
    </p>
    <p>
      <a class="btn btn-lg btn-success" href="{{ route('signup') }}" role="button">现在注册</a>
    </p>
  </div>
@stop