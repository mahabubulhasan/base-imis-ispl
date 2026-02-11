<!-- PUBLIC DASHBOARD -->
<div id="dashboard" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="max-w-7xl mx-auto bg-white p-6 md:p-10 rounded-2xl shadow-2xl">
        <h2 class="text-[#1f3b7d] text-2xl md:text-3xl lg:text-4xl mb-8 border-b-4 border-[#0056b3] pb-4 font-bold">Public Dashboard</h2>

        <!-- Loading Spinner -->
        <div id="dashboard-loader" class="flex justify-center items-center py-20">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#0056b3] border-t-transparent"></div>
            <span class="ml-4 text-gray-600 text-lg">Loading Dashboard...</span>
        </div>

        <!-- Dashboard Content Container -->
        <div id="dashboard-content" class="hidden">
            <!-- Content will be loaded via AJAX -->
        </div>

        <!-- Error Message Container -->
        <div id="dashboard-error" class="hidden bg-red-50 border border-red-200 p-4 rounded-lg text-red-700">
            Failed to load dashboard. Please try again later.
        </div>
    </div>
</div>

@push('scripts')
<script>
  // Load Public Dashboard via AJAX
  function loadPublicDashboard() {
    const loader = document.getElementById('dashboard-loader');
    const content = document.getElementById('dashboard-content');
    const error = document.getElementById('dashboard-error');

    // Show loader, hide content and error
    loader.classList.remove('hidden');
    content.classList.add('hidden');
    error.classList.add('hidden');

    $.ajax({
      url: '{{ route("public-dashboard") }}',
      method: 'GET',
      dataType: 'html',
      success: function(response) {
        // Hide loader
        loader.classList.add('hidden');

        // Parse HTML and extract scripts
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = response;

        // Extract all script tags
        var scripts = tempDiv.querySelectorAll('script');
        var scriptsArray = Array.from(scripts);

        // Remove scripts from the HTML temporarily
        scriptsArray.forEach(function(script) {
          script.parentNode.removeChild(script);
        });

        // Insert the HTML without scripts
        content.innerHTML = tempDiv.innerHTML;
        content.classList.remove('hidden');

        // Execute scripts after a brief delay to ensure DOM is ready
        setTimeout(function() {
          scriptsArray.forEach(function(oldScript) {
            var newScript = document.createElement('script');
            if (oldScript.src) {
              newScript.src = oldScript.src;
            } else {
              newScript.textContent = oldScript.textContent;
            }
            document.body.appendChild(newScript);
          });

          console.log('Dashboard loaded with ' + scriptsArray.length + ' scripts executed');
        }, 100);

        // Initialize tooltips
        if ($.fn.tooltip) {
          $('[data-toggle="tooltip"]').tooltip({ html: true });
        }
      },
      error: function() {
        // Hide loader
        loader.classList.add('hidden');

        // Show error
        error.classList.remove('hidden');
        console.error('Failed to load public dashboard');
      }
    });
  }
</script>
@endpush
