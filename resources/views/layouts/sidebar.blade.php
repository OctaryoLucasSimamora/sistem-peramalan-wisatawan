<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Peramalan Wisatawan</div>
    </a>

    <hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('dashboard') }}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</li>

<hr class="sidebar-divider">

<div class="sidebar-heading">
    Menu Utama
</div>

<!-- Nav Item - Data Wisatawan -->
<li class="nav-item {{ request()->routeIs('tourist.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('tourist.index') }}">
        <i class="fas fa-fw fa-table"></i>
        <span>Data Wisatawan</span>
    </a>
</li>

<!-- Nav Item - Peramalan -->
<li class="nav-item {{ request()->routeIs('forecast.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('forecast.index') }}">
        <i class="fas fa-fw fa-chart-line"></i>
        <span>Peramalan</span>
    </a>
</li>
{{-- Analisis Menu --}}
@if(request()->is('analysis*') || request()->is('forecast*'))
<li class="nav-item {{ request()->is('analysis*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('analysis.history') }}">
        <i class="fas fa-chart-line"></i>
        <span>History Analisis</span>
    </a>
</li>
@endif

<!-- Nav Item - Evaluasi -->
<li class="nav-item {{ request()->routeIs('evaluation.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('evaluation.index') }}">
        <i class="fas fa-fw fa-chart-bar"></i>
        <span>Evaluasi & Laporan</span>
    </a>
</li>

<hr class="sidebar-divider">

<!-- Nav Item - Logout -->
<li class="nav-item">
    <form action="{{ route('logout') }}" method="POST" class="w-100">
        @csrf
        <button type="submit" class="nav-link btn btn-link text-start w-100" 
                style="text-decoration: none; color: inherit;">
            <i class="fas fa-sign-out-alt fa-fw"></i>
            <span>Logout</span>
        </button>
    </form>
</li>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>