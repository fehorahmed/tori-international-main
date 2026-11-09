<style>
    /* Custom Thin Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f8f9fa; 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d3e2; 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #b7b9cc; 
    }
    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #d1d3e2 #f8f9fa;
        padding-bottom: 5px; /* Spacing so scrollbar doesn't overlap pills tightly */
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <div class="bg-white p-2 rounded shadow-sm custom-scrollbar" style="overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch;">
            <ul class="nav nav-pills flex-nowrap" style="white-space: nowrap; width: max-content;">
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'divisions' ? 'active' : '' }}" href="{{ route('locations.index', 'divisions') }}"><i class="fas fa-map-marked-alt mr-1"></i> Divisions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'districts' ? 'active' : '' }}" href="{{ route('locations.index', 'districts') }}"><i class="fas fa-map-marker-alt mr-1"></i> Districts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'upazilas' ? 'active' : '' }}" href="{{ route('locations.index', 'upazilas') }}"><i class="fas fa-map-pin mr-1"></i> Upazilas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'pourashavas' ? 'active' : '' }}" href="{{ route('locations.index', 'pourashavas') }}"><i class="fas fa-city mr-1"></i> Pourashavas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'unions' ? 'active' : '' }}" href="{{ route('locations.index', 'unions') }}"><i class="fas fa-vector-square mr-1"></i> Unions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type == 'villages' ? 'active' : '' }}" href="{{ route('locations.index', 'villages') }}"><i class="fas fa-home mr-1"></i> Villages</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll tabs to make the active tab visible with a smooth sliding animation
        setTimeout(function() {
            if (typeof jQuery !== 'undefined') {
                var $container = $('.custom-scrollbar');
                var $active = $('.nav-pills .nav-link.active').parent(); // <li>
                
                if ($active.length && $container.length) {
                    var containerLeft = $container.offset().left;
                    var activeLeft = $active.offset().left;
                    var currentScroll = $container.scrollLeft();
                    
                    // Exact pixel calculation immune to DOM positioning logic
                    var absoluteLeft = currentScroll + (activeLeft - containerLeft);
                    var centerScroll = absoluteLeft - ($container.outerWidth() / 2) + ($active.outerWidth() / 2);
                    
                    $container.animate({ scrollLeft: centerScroll }, 600);
                }
            }
        }, 300);
    });
</script>
