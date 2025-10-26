@extends('layouts.administracao')

@section('title', 'Histórico de Frequência - Teste')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3>Teste do Histórico</h3>
                        <p>Arquivo de teste para verificar sintaxe</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            console.log('Teste OK');
        });
    </script>
@endsection
