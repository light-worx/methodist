<x-web pageName="Ministry ideas">
    <style>
    #stickyAlert {
        position: sticky;
        top: 0;
        z-index: 1050; /* Make sure it appears above content */
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div id="stickyAlert" class="alert alert-warning alert-dismissible fade show mb-0" role="alert" style="display:none;">
                Our people are doing such creative things around the Connexion, but often our best ideas don't get shared. We're trying to change that by collecting helpful ministry ideas and we would love to hear from you!
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <livewire:ministry-idea-form 
                :prefilledCircuit="$_COOKIE['user_circuit'] ?? null" 
                :prefilledEmail="$_COOKIE['user_email'] ?? null" 
            />
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('stickyAlert');
            const closeBtn = alertBox.querySelector('.btn-close');

            // Show the alert only if not dismissed
            if (localStorage.getItem('alertDismissed') !== 'true') {
                alertBox.style.display = 'block';
            }

            closeBtn.addEventListener('click', function () {
                alertBox.style.display = 'none';
                localStorage.setItem('alertDismissed', 'true');
            });
        });
    </script>
</x-layouts.web>