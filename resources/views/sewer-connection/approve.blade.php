<!-- Modal -->
@push('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true" style="font-family: 'Open Sans', sans-serif;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{__('Sewer Connection')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
              "{{ __('Existing Sanitation of Building will be updated as Sewer Network and corresponding containment connection (if any) will be removed !')}}"
                <br>

                <div style="margin-top:2%; font-weight:bold; text-align:center; font-size:15px">
                    "{{__('Are you sure?')}}"
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">"{{__('No')}}"</button>
                <button type="button" class="btn btn-primary" id="confirmYes">"{{__('Yes')}}"</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var binValue;
    var sewer;

    $('#exampleModalCenter').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        binValue = button.data('id'); // Extract bin from data-id attribute
        sewer = button.data('sewer-code'); // Extract sewer_code from data-sewer-code attribute
    });

    $('#confirmYes').on('click', function() {
        if (binValue) { // Check if binValue is not undefined or null
            // Construct the URL with the fetched bin value
            var url = '/sewerconnection/sewerconnection/data/' + binValue;

            // Make an AJAX request to your backend URL with the bin and sewer_code parameters
            $.ajax({
                url: url,
                method: 'GET',
                data: { sewer: sewer }, // Pass sewer_code data to the backend
                success: function(response) {
                    // Handle success response
                    if (response.status === 'success') {
                        // Display success swal if the response status is success
                        swal({
                            title: "Success!",
                            text: "{{__('Building updated successfully')}}",
                            icon: "success",
                            closeOnClickOutside: false, // Prevent close on outside click
                        }).then((result) => {
                            // Hide the modal
                            $('#exampleModalCenter').modal('hide');
                            // Remove the modal backdrop
                            $('.modal-backdrop').remove();
                            // Reload the page
                            location.reload();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    swal({
                        title: "Warning!",
                        text: "{{__('Building not found.')}}",
                        icon: "warning",
                        closeOnClickOutside: false, // Prevent close on outside click
                    }).then((result) => {
                        // Hide the modal
                        $('#exampleModalCenter').modal('hide');
                        // Remove the modal backdrop
                        $('.modal-backdrop').remove();
                    });
                }
            });
        } else {
            console.error('Bin value is not available.');
        }
    });
});
</script>

@endpush