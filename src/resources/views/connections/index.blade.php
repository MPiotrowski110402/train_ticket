@extends('layouts.app')


@section('title','Wyszukaj połączenie')


@section('content')


<section class="connections-page">


    <div class="container">


        @include('connections.partials.search-box')



        <div class="results-header">

            <h1>
                Dostępne połączenia
            </h1>


            <p>
                Kraków Główny → Poznań Główny
            </p>

        </div>




        <div class="connections-list">


            @include('connections.partials.connection-card')

            @include('connections.partials.connection-card')

            @include('connections.partials.connection-card')


        </div>


    </div>


</section>


@endsection