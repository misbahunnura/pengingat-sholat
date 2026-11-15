<div class="menu-adm" role="navigation" aria-label="Menu admin">
    <a class="ch {{ request()->routeIs('admin.saran.*') ? 'a' : '' }}" href="{{ route('admin.saran.index') }}"><i class="ti ti-message-2" aria-hidden="true"></i> Saran @if (($lencanaSaran ?? 0) > 0)<span class="lcn lcn-sebaris">{{ $lencanaSaran > 99 ? '99+' : $lencanaSaran }}</span>@endif</a>
    <a class="ch {{ request()->routeIs('admin.doa.*') ? 'a' : '' }}" href="{{ route('admin.doa.index') }}"><i class="ti ti-book" aria-hidden="true"></i> Doa</a>
    <a class="ch {{ request()->routeIs('admin.dzikir.*') ? 'a' : '' }}" href="{{ route('admin.dzikir.index') }}"><i class="ti ti-sunset-2" aria-hidden="true"></i> Dzikir</a>
    <a class="ch {{ request()->routeIs('admin.asma.*') ? 'a' : '' }}" href="{{ route('admin.asma.index') }}"><i class="ti ti-sparkles" aria-hidden="true"></i> Asmaul Husna</a>
</div>
