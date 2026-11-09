
 <?php
$subSubMenu = ['notices.index', 'notices.create', 'notices.edit',];
?>
@if(auth()->user()->can('notice_view'))
    <li class="nav-item">
        <a href="{{ route('notices.index') }}"
           class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
            <i class="fa-solid fa-bell nav-icon"></i>
            <p>Notices</p>
        </a>
    </li>
@endif
