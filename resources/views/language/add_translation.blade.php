@extends('layouts.layers')
@section('title', $pageTitle)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<style>
/* Global Styles */
* { box-sizing: border-box; }
button:active, button:focus { outline: none; box-shadow: none; }

/* Layout */
.multitab-form-area {
    background: #eee;
    padding: 20px;
    border-radius: 5px;
    display: flex;
}

.tab-links-area {
    width: 240px;
    padding-right: 20px;
    height: calc(100vh - 40px); /* Adjust height */
    overflow-y: auto; /* Make this area scrollable */
    position: sticky;
    top: 0;
}

.tab-form-area {
    width: calc(100% - 245px);
    background: #fff;
    padding: 16px;
    border-radius: 5px;
    height: auto; /* Remove scroll for the form */
}

/* Typography */
.tab-links-area h1 { margin: 0; font-size: 24px; color: #138496; }

/* Tabs */
.tab-links-area ul { list-style: none; padding: 0; margin: 20px 0 0; }
.tab-links-area ul li { border-left: 1px solid #eee; }
.tab-links-area ul li a { text-decoration: none; font-size: 14px; padding: 10px 15px; display: block; color: #333; transition: 0.3s; }
.tab-links-area ul li a.active { background: #138496; color: #fff; }
.tabs-panels { display: none; animation: fadeIn 0.3s ease; }
.tabs-panels.active { display: block; }

/* Form Layout */
.form-fields-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-field label {
    flex: 0 0 200px;
    font-size: 14px;
    text-align: left;
}

.form-field input {
    flex: 1;
    border: 1px solid #ccc;
    padding: 8px;
}

/* Sticky Header */
.back-to-list {
    position: sticky;
    top: 0; /* Ensure background color to avoid overlap */
    padding: 10px 0;
    background-color: #EEEEEE;
    z-index: 10;
}

/* Buttons */
.next-btn {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
.next-btn .btn-group {
    display: flex;
    gap: 10px;
}
.next-btn button {
    background: #138496;
    cursor: pointer;
    border: 1px solid #138496;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
}
.next-btn button:hover {
    background: transparent;
    color: #138496;
}

/* Scroll to Top Button */
.scroll-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    display: none;
    background: #138496;
    color: white;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
}

@media (max-width: 750px) {
    .tab-links-area { width: 100%; display: block; margin-bottom: 20px; }
    .tab-form-area { width: 100%; }
    .form-field { flex: 1 1 100%; }
}
@keyframes fadeIn { 0% { opacity: 0; } 100% { opacity: 1; } }
</style>

<div class="form-area">
    <div class="form-body">
        <div class="multitab-form-area">
            <!-- Sidebar Navigation -->
            <div class="tab-links-area">
            <div class="back-to-list">
    <a href="{{ action('Language\LanguageController@index') }}" class="btn btn-info">Back to List</a>
</div>


                <hr>
              <ul>
        @foreach($sourceTranslations as $page => $translations)
            <li>
                <a href="#" class="tab-link @if($loop->first) active @endif" data-tab="tab-{{ $loop->index }}">
                    {{ $customNames[$page] ?? Str::title(str_replace('_', ' ', $page)) }}
                </a>
            </li>
        @endforeach
    </ul>

            </div>

            <!-- Form Sections -->
            <div class="tab-form-area">
                <form method="POST" >
                    @csrf
                    @foreach($sourceTranslations as $page => $translations)
                        <div class="tabs-panels @if($loop->first) active @endif" id="tab-{{ $loop->index }}">
                            <h4> {{ $customNames[$page] ?? Str::title(str_replace('_', ' ', $page)) }} Translations</h4>
                            <hr>
                            <div class="form-fields-container">
                                @foreach($translations as $translation)
                                    <div class="form-field">
                                        <label for="{{ $translation->key }}">{{ $translation->text }}</label>
                                        <input type="text" id="{{ $translation->key }}" name="{{ $translation->key }}" value="{{ $existingTranslations[$translation->key] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="next-btn">
                                @if(!$loop->first)
                                    <button type="button" class="prev-tab">Previous</button>
                                @endif
                                <div class="btn-group">
                                    <button type="submit" class="save-continue" id="save-continue">Save and Continue</button>
                                    @if(!$loop->last)
                                        <button type="button" class="next-tab">Next</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>
</div>
<div class="scroll-to-top" onclick="scrollToTop()">↑</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const slug = window.location.pathname.split('/').pop();

// Handle "Save and Continue" button clicks for each tab
const saveContinueButtons = document.querySelectorAll('.save-continue');

saveContinueButtons.forEach(button => {
button.addEventListener('click', function (e) {
e.preventDefault(); // Prevent default form submission

// Get the active tab's form data
const currentTab = this.closest('.tabs-panels');
const formData = new FormData(document.querySelector('form'));

// Extract CSRF token from the form
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Initialize the data structure in the desired format
const translations = {};

// Populate the "translations" object but exclude `_token`
formData.forEach((value, key) => {
    if (key !== '_token') { // Exclude token from data
        translations[key] = value;
    }
});

// Wrap the translations object in the required structure
const jsonData = { translations };
// Send AJAX request with CSRF token
fetch(`/language/save-translation/${slug}`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken // Add CSRF token in headers
    },
    body: JSON.stringify(jsonData)
})
.then(response => response.json().catch(() => ({ status: 'error' }))) // Handle non-JSON responses
.then(data => {
    console.log("Response received:", data);

    if (data.status === 'success') { // ✅ Check the correct response status




        // Redirect to language/setup after a short delay
        setTimeout(() => {
            window.location.href = '/language/setup';
            toastr.success('Translations created successfully!');
        }, 1000); // Slight delay for better user experience
    } else {
        toastr.error(`Error saving translations: ${data.message || 'Unknown error'}`, 'Error');
    }
})
.catch(error => {
    console.error('Fetch Error:', error);
    toastr.error('Something went wrong. Please try again.', 'Error');
});

});
});

    const tabLinks = document.querySelectorAll(".tab-link");
    const tabPanels = document.querySelectorAll(".tabs-panels");
    const scrollToTopBtn = document.querySelector(".scroll-to-top");

    function showTab(index) {
        tabLinks.forEach(tab => tab.classList.remove("active"));
        tabPanels.forEach(panel => panel.classList.remove("active"));
        tabLinks[index].classList.add("active");
        tabPanels[index].classList.add("active");
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    tabLinks.forEach((link, index) => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            showTab(index);
        });
    });

    document.querySelectorAll(".next-tab").forEach((btn, index) => {
        btn.addEventListener("click", function () {
            showTab(index + 1);
        });
    });

    document.querySelectorAll(".prev-tab").forEach((btn, index) => {
        btn.addEventListener("click", function () {
            showTab(index);
        });
    });

    window.addEventListener("scroll", function () {
        scrollToTopBtn.style.display = window.scrollY > 200 ? "block" : "none";
    });

    window.scrollToTop = function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    };
});
</script>

@endsection
