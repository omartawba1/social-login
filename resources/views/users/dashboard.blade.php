@extends('master')
@section('content')
    <p><img src="{{ auth()->user()->picture  }}"/></p>
    <p>hi {{ auth()->user()->name }}</p>
    <p><a href="/users/logout">Logout</a></p>
@endsection