<nav class="navbar navbar-expand-md navbar-dark bg-dark px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">Анализатор страниц</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('home')) active @endif" aria-current="page"
                       href="{{ route('home') }}">
                        Главная
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(request()->routeIs('urls.index')) active @endif" aria-current="page"
                       href="{{ route('urls.index') }}">
                        Сайты
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
