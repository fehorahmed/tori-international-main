
 <?php
$subSubMenu = ['meetings.index', 'meetings.create', 'meetings.edit', 'meetings.show'];
?>
@if(auth()->user()->can('meeting_view'))
    <li class="nav-item">
        <a href="{{ route('meetings.index') }}"
           class="nav-link {{ in_array(Route::currentRouteName(), $subSubMenu) ? 'active' : '' }}">
            <i class="fa-solid fa-clipboard-list nav-icon"></i>
            <p>Meeting Summary</p>
        </a>
    </li>
@endif
