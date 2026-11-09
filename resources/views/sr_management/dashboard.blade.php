@extends('layouts.app')
@section('title', 'SR Management Dashboard')

@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner theme-office d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #4f46e5, #312e81);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">SR Workspace</div>
                <div class="welcome-subtitle text-white-50">Manage Sales Representatives, tracking, and visit reports.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-users-cog fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <!-- Total SRs -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #3b82f6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total SRs</div>
                        <div class="premium-number text-dark">{{ number_format($totalSrs) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #60a5fa, #3b82f6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-user-friends" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sr.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Representatives</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Routes Today -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #8b5cf6;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Active Routes</div>
                        <div class="premium-number text-dark">{{ number_format($activeRoutesCount) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-route" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="#" class="premium-footer d-flex justify-content-between align-items-center">
                <span>Today's Routes</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Visits -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #10b981;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Total Visits</div>
                        <div class="premium-number text-dark">{{ number_format($totalVisits) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #34d399, #10b981);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-map-marked-alt" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sr-visits.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View All Visits</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Today's Visits -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="premium-card border-top-office h-100" style="border-top-color: #f59e0b;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="premium-title mb-2 text-dark">Today's Visits</div>
                        <div class="premium-number text-dark">{{ number_format($todayVisits) }}</div>
                    </div>
                    <div class="premium-icon-box shadow-sm" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <div class="deco-circle"></div>
                        <i class="fas fa-calendar-day" style="z-index: 1;"></i>
                    </div>
                </div>
            </div>
            <a href="{{ route('sr-visits.index') }}" class="premium-footer d-flex justify-content-between align-items-center">
                <span>View Today's Log</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Top SRs Table -->
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="premium-card h-100">
            <div class="card-header bg-white border-bottom pt-3 pb-2">
                <h5 class="mb-0 text-dark"><i class="fas fa-users text-primary"></i> Today's Top Active SRs</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-top-0 px-4">SR Name</th>
                                <th class="text-center border-top-0">Today's Visits</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSrsToday as $srStat)
                            <tr>
                                <td class="px-4 fw-bold text-dark">{{ $srStat->sr->name ?? 'Unknown' }}</td>
                                <td class="text-center"><span class="badge bg-primary px-3 py-2 rounded-pill">{{ $srStat->visit_count }}</span></td>
                                <td><span class="text-success fw-bold"><i class="fas fa-circle fa-xs me-1"></i> Active</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No SR activity logged today yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gauge Chart -->
    <div class="col-lg-4">
        <div class="premium-card h-100">
            <div class="card-header bg-white border-bottom pt-3 pb-2">
                <h5 class="mb-0 text-dark"><i class="fas fa-tachometer-alt text-warning"></i> Route Coverage</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center pt-4">
                <div id="route-coverage-gauge"></div>
                <div class="text-center mt-3">
                    <h6 class="text-dark fw-bold mb-1">Target Achievement</h6>
                    <p class="text-muted mb-0 small">Based on {{ $totalTargetVisits }} target visits today</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Routes & Check-in System -->
<div class="row mb-4">
    <div class="col-12">
        <div class="premium-card h-100">
            <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">
                    <i class="fas fa-map-marked-alt text-success"></i> Today's Assigned Routes
                </h5>
                <span class="badge badge-success px-3 py-2" style="font-size: 14px;">{{ $totalVisitedToday }} / {{ $totalAssignedShops }} Visited</span>
            </div>
            <div class="card-body p-3 bg-light">
                <div class="accordion" id="marketAccordion">
                    @forelse($markets as $index => $market)
                    <div class="card shadow-sm mb-3 border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom-0" id="heading{{ $index }}" data-toggle="collapse" data-target="#collapse{{ $index }}" style="cursor:pointer; padding: 15px 20px;">
                            <h5 class="mb-0 text-dark d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size: 16px;">
                                    <i class="fas fa-map-marker-alt text-danger mr-2"></i> {{ $market->name }}
                                    @if(auth()->user()->role != \App\Enumeration\RoleType::$SR)
                                        <small class="text-muted ml-2">({{ $market->assigned_sr }})</small>
                                    @endif
                                </span>
                                <span class="badge badge-primary badge-pill px-3 py-2">{{ count($market->shops) }} Shops</span>
                            </h5>
                        </div>

                        <div id="collapse{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" data-parent="#marketAccordion">
                            <div class="card-body p-2" style="background-color: #f8fafc;">
                                <div class="row">
                                @forelse($market->shops as $shop)
                                    <div class="col-md-6 mb-2">
                                        <div class="card border-0 shadow-sm shop-card h-100" 
                                             style="border-radius: 10px;"
                                             data-id="{{ $shop->id }}" 
                                             data-lat="{{ $shop->lat }}" 
                                             data-lng="{{ $shop->long }}">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div style="flex: 1;">
                                                        <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 15px;">{{ $shop->name }}</h6>
                                                        <div class="text-muted small mb-1"><i class="fas fa-phone-alt mr-1"></i> {{ $shop->mobile_no ?? 'N/A' }}</div>
                                                        @if($shop->address)
                                                        <div class="text-muted small"><i class="fas fa-map-signs mr-1"></i> {{ Str::limit($shop->address, 30) }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right pl-2" style="min-width: 100px;">
                                                        @if($shop->is_visited_today)
                                                            <div class="badge badge-success px-2 py-2 rounded-pill w-100" style="font-size: 12px;">
                                                                <i class="fas fa-check-circle mr-1"></i> Visited
                                                            </div>
                                                        @else
                                                            <div class="distance-badge badge badge-secondary px-2 py-2 rounded-pill w-100 mb-1" style="font-size: 11px; white-space: normal;">
                                                                <i class="fas fa-location-arrow"></i> <span class="dist-val">Calculating...</span>
                                                            </div>
                                                            @if(auth()->user()->role == \App\Enumeration\RoleType::$SR)
                                                            <button class="btn btn-sm btn-success btn-checkin d-none w-100 shadow-sm" style="border-radius: 8px; font-weight: bold;" data-id="{{ $shop->id }}" data-name="{{ $shop->name }}">
                                                                <i class="fas fa-camera"></i> Check-in
                                                            </button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted p-3">
                                        No shops found in this market.
                                    </div>
                                @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-info border-0 shadow-sm text-center p-4 rounded mb-0" style="background-color: #e0f2fe; color: #0369a1;">
                        <i class="fas fa-route fa-3x mb-3 opacity-50"></i><br>
                        <h6 class="font-weight-bold">No Routes Assigned Today</h6>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Camera Check-in Modal -->
<div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="border-radius: 15px; overflow: hidden;">
            <form id="checkinForm" action="{{ route('sr_management.dashboard.checkin') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="supplier_id" id="checkin_supplier_id">
                <input type="hidden" name="lat" id="checkin_lat">
                <input type="hidden" name="long" id="checkin_long">
                
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i> Shop Check-in</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 text-center bg-light">
                    <h5 id="checkin_shop_name" class="font-weight-bold text-dark mb-4"></h5>
                    
                    <div class="form-group mb-4">
                        <label class="btn btn-white btn-block shadow-sm p-4 text-success" style="border: 2px dashed #10b981; border-radius: 12px; cursor: pointer; background: #fff;">
                            <i class="fas fa-camera fa-3x mb-3 d-block text-success"></i>
                            <span class="font-weight-bold" style="font-size: 16px;">Tap to Open Camera</span>
                            <input type="file" name="image" id="visit_image" accept="image/*" capture="environment" class="d-none" required>
                        </label>
                        
                        <div id="image_preview_container" class="mt-3 d-none position-relative">
                            <img id="image_preview" src="" class="img-fluid rounded shadow-sm" style="max-height: 250px; width: 100%; object-fit: cover; border-radius: 12px;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute" id="btn_retake" style="top: 10px; right: 10px; border-radius: 50%;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group text-left">
                        <label class="font-weight-bold text-muted small text-uppercase">Visit Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Did you collect any order?" style="border-radius: 10px; border: 1px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 8px; font-weight: bold; width: 48%;">Cancel</button>
                    <button type="submit" class="btn btn-success shadow-sm" style="border-radius: 8px; font-weight: bold; width: 48%;"><i class="fas fa-save mr-1"></i> Submit Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var achievement = {{ $targetAchievementPercentage }};
        var color = '#10b981'; // Green
        if (achievement < 50) color = '#ef4444'; // Red
        else if (achievement < 80) color = '#f59e0b'; // Yellow

        var options = {
            series: [achievement],
            chart: {
                type: 'radialBar',
                height: 300,
                offsetY: -10,
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#e7e7e7",
                        strokeWidth: '97%',
                        margin: 5,
                        dropShadow: {
                            enabled: true,
                            top: 2,
                            left: 0,
                            color: '#999',
                            opacity: 0.2,
                            blur: 3
                        }
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            offsetY: -2,
                            fontSize: '28px',
                            fontWeight: 'bold',
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            grid: {
                padding: {
                    top: -10
                }
            },
            fill: {
                type: 'solid',
                colors: [color]
            },
            labels: ['Coverage'],
        };

        var chart = new ApexCharts(document.querySelector("#route-coverage-gauge"), options);
        chart.render();
    });

    // --- Check-in System Scripts ---
    let currentLat = null;
    let currentLng = null;
    const MAX_DISTANCE_METERS = 50; 

    function getDistance(lat1, lon1, lat2, lon2) {
        if (!lat1 || !lon1 || !lat2 || !lon2) return 999999;
        var R = 6371e3; // metres
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(R * c);
    }

    function updateDistances() {
        if (currentLat === null || currentLng === null) return;

        $('.shop-card').each(function() {
            let shopLat = $(this).data('lat');
            let shopLng = $(this).data('lng');
            let distBadge = $(this).find('.dist-val');
            let distBox = $(this).find('.distance-badge');
            let checkinBtn = $(this).find('.btn-checkin');

            if (!shopLat || !shopLng) {
                distBadge.text('Shop GPS Missing');
                distBox.removeClass('badge-secondary badge-success').addClass('badge-warning text-dark');
                return;
            }

            let distance = getDistance(currentLat, currentLng, shopLat, shopLng);
            
            if (distance <= MAX_DISTANCE_METERS) {
                distBox.addClass('d-none');
                checkinBtn.removeClass('d-none');
            } else {
                distBox.removeClass('d-none');
                checkinBtn.addClass('d-none');
                
                let distText = distance > 1000 ? (distance/1000).toFixed(1) + ' km away' : distance + 'm away';
                distBadge.text(distText);
                distBox.removeClass('badge-success badge-warning text-dark').addClass('badge-secondary');
            }
        });
    }

    $(document).ready(function() {
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(function(position) {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;
                updateDistances();
            }, function(error) {
                $('.dist-val').text('Please Enable GPS');
                $('.distance-badge').removeClass('badge-secondary').addClass('badge-danger');
            }, {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            });
        } else {
            $('.dist-val').text('GPS Not Supported');
        }

        $('.btn-checkin').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            
            $('#checkin_supplier_id').val(id);
            $('#checkin_shop_name').text(name);
            $('#checkin_lat').val(currentLat);
            $('#checkin_long').val(currentLng);
            
            $('#visit_image').val('');
            $('#image_preview_container').addClass('d-none');
            $('#image_preview').attr('src', '');
            $('label[for="visit_image"]').parent().removeClass('d-none');
            
            $('#checkinModal').modal('show');
            setTimeout(() => { $('#visit_image').click(); }, 300);
        });

        $('#btn_retake').click(function() {
            $('#visit_image').val('');
            $('#image_preview_container').addClass('d-none');
            $('label[for="visit_image"]').parent().removeClass('d-none');
            $('#visit_image').click();
        });

        $('#visit_image').change(function(e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#image_preview').attr('src', e.target.result);
                    $('#image_preview_container').removeClass('d-none');
                    $('label[for="visit_image"]').parent().addClass('d-none');
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        $('#checkinForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if(response.status) {
                        $('#checkinModal').modal('hide');
                        Swal.fire({
                            icon: 'success', title: 'Success!', text: 'Visit recorded successfully.', timer: 1500, showConfirmButton: false
                        }).then(() => { location.reload(); });
                    } else {
                        Swal.fire('Error', response.message || 'Something went wrong', 'error');
                        btn.html(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to submit visit.';
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire('Error', msg, 'error');
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
