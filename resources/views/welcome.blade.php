@extends('layouts.app')

@section('title', 'RailTicket — strona startowa')
@section('meta-description', 'Demonstracyjny system sprzedaży biletów kolejowych RailTicket.')

@section('content')
    @include('partials.hero')
    @include('partials.stats')
    @include('partials.features')
@endsection
