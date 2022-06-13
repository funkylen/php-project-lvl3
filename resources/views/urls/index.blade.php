@extends('layout')

@section('content')
    <h1 class="mt-5 mb-3">Сайты</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover text-nowrap">
            <tbody>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Последняя проверка</th>
                <th>Код ответа</th>
            </tr>
            @foreach($urls as $url)
                <tr>
                    <td>{{ $url->id }}</td>
                    <td><a href="{{ route('urls.show', ['url' => $url->id]) }}">{{ $url->name }}</a></td>
                    <td>{{ $url->latest_check_created_at }}</td>
                    <td>{{ $url->latest_check_status_code }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <nav role="navigation" aria-label="Pagination Navigation">
        <ul class="pagination">
            <li class="page-item @if($urls->onFirstPage()) disabled @endif">
                <a class="page-link" href="{{ $urls->previousPageUrl() }}">Previous</a>
            </li>
            <li class="page-item @unless($urls->hasMorePages()) disabled @endunless">
                <a class="page-link" href="{{ $urls->nextPageUrl() }}">Next</a>
            </li>
        </ul>
    </nav>
@endsection
