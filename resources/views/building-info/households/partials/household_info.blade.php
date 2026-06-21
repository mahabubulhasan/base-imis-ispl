{{-- Last Modified: 2026-06-21 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Household information display with collapsible sections --}}

@if($households && $households->count() > 0)
    <div class="accordion" id="householdsAccordion">
        @foreach($households as $index => $household)
            <div class="accordion-item card">
                <h2 class="accordion-header" id="heading{{ $household->id }}">
                    <button class="accordion-button collapsed card-header btn btn-link w-100 text-start" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $household->id }}"
                        aria-expanded="false" aria-controls="collapse{{ $household->id }}">
                        <span class="fw-bold">Household {{ $household->household_id }}</span>
                        <span class="badge bg-secondary ms-2">{{ $household->household_owner_name }}</span>
                    </button>
                </h2>

                <div id="collapse{{ $household->id }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $household->id }}" data-bs-parent="#householdsAccordion">
                    <div class="accordion-body card-body p-0">
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
                                        <td><strong>Contact Number</strong></td>
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
                                        <td><strong>Road Number</strong></td>
                                        <td>{{ $household->road_no }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Road Name</strong></td>
                                        <td>{{ $household->road_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Holding Number</strong></td>
                                        <td>{{ $household->holding_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Area/Mohalla</strong></td>
                                        <td>{{ $household->area_mohalla_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sub Location</strong></td>
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
                                        <td>{{ \Carbon\Carbon::parse($household->using_this_service_since)->format('Y-m-d') }}</td>
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
                                            <span class="badge {{ $household->status === 'active' ? 'bg-success' : 'bg-danger' }}">
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
            <i class="fas fa-info-circle me-2"></i>
            <div>No household information available for this building.</div>
        </div>
    </div>
@endif

