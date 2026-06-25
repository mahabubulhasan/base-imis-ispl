{{-- Last Modified: 2026-06-21 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Household information display with collapsible sections --}}

@if($households && $households->count() > 0)
<div class="accordion" id="householdsAccordion">
    @foreach($households as $index => $household)
    <div class="card">
        <div class="card-header p-0">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                    data-target="#collapse{{ $household->id }}" aria-expanded="false"
                    aria-controls="collapse{{ $household->id }}">
                    <strong>Household {{ $household->household_id }}</strong>
                    <span class="badge badge-secondary ml-2">{{ $household->household_owner_name }}</span>
                </button>
            </h2>
        </div>

        <div id="collapse{{ $household->id }}" class="collapse" data-parent="#householdsAccordion">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm mb-0">
                        <tbody>
                            <tr>
                                <td><strong>Household ID</strong></td>
                                <td>{{ $household->household_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Household Owner Name</strong></td>
                                <td>{{ $household->household_owner_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact No.</strong></td>
                                <td>{{ $household->contact_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Father/Husband Name</strong></td>
                                <td>{{ $household->father_or_husband_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>BIN</strong></td>
                                <td>{{ $household->bin }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ward</strong></td>
                                <td>{{ $household->ward }}</td>
                            </tr>
                            <tr>
                                <td><strong>Road No.</strong></td>
                                <td>{{ $household->road_no }}</td>
                            </tr>
                            <tr>
                                <td><strong>Road Name</strong></td>
                                <td>{{ $household->road_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Holding No.</strong></td>
                                <td>{{ $household->holding_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Area/Mohalla</strong></td>
                                <td>{{ $household->area_mohalla_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Location</strong></td>
                                <td>{{ $household->sub_location ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tax ID</strong></td>
                                <td>{{ $household->tax_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Functional Use</strong></td>
                                <td>{{ $household->functional_use }}</td>
                            </tr>
                            <tr>
                                <td><strong>Is Owner</strong></td>
                                <td>{{ $household->is_owner ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Is LIC</strong></td>
                                <td>{{ $household->is_lic ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td><strong>LIC ID</strong></td>
                                <td>{{ $household->lic_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Waste Charge</strong></td>
                                <td>{{ $household->waste_charge }}</td>
                            </tr>
                            <tr>
                                <td><strong>Number of Family Members</strong></td>
                                <td>{{ $household->number_of_family_members }}</td>
                            </tr>
                            <tr>
                                <td><strong>Daily Waste Volume (kg)</strong></td>
                                <td>{{ $household->daily_waste_volume }}</td>
                            </tr>
                            <tr>
                                <td><strong>Using Service Since</strong></td>
                                <td>{{ \Carbon\Carbon::parse($household->using_this_service_since)->format('Y-m-d') }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Segregation Practiced</strong></td>
                                <td>{{ $household->segregation_practiced ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Waste Bin Provided</strong></td>
                                <td>{{ $household->waste_bin_provided ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Van Puller</strong></td>
                                <td>{{ $household->van_puller_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>
                                    <span
                                        class="badge {{ $household->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($household->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Survey Date</strong></td>
                                <td>{{ \Carbon\Carbon::parse($household->survey_date)->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Remarks</strong></td>
                                <td>{{ $household->remarks ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Created At</strong></td>
                                <td>{{ \Carbon\Carbon::parse($household->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Updated At</strong></td>
                                <td>{{ \Carbon\Carbon::parse($household->updated_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle mr-2"></i>
        <div>No household information available for this building.</div>
    </div>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<script>
    // Reinitialize Bootstrap 4 collapse for AJAX-loaded content
    document.addEventListener('shown.bs.collapse', function() {
        console.log('Accordion expanded');
    });

    // Manually handle collapse clicks for dynamically loaded content
    document.querySelectorAll('#householdsAccordion [data-toggle="collapse"]').forEach(function(button) {
        button.addEventListener('click', function(e) {
            var target = this.getAttribute('data-target');
            var targetElement = document.querySelector(target);

            if (targetElement) {
                // Use Bootstrap 4's collapse API
                $(targetElement).collapse('toggle');
            }
        });
    });
</script>