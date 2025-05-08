<script>
document.addEventListener('DOMContentLoaded', function() {
    const variantsContainer = document.getElementById('variants-container');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Add variant button handler
    document.getElementById('add-variant').addEventListener('click', async function(e) {
        e.preventDefault();
        const variantIndex = document.querySelectorAll('.variant-card').length;

        try {
            // Show loading state
            e.target.disabled = true;
            e.target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

            const response = await fetch(`{{ route('admin.products.variant') }}?index=${variantIndex}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
                    }
            // Ensure form submission isn't prevented
            // form.addEventListener('submit', function(e) {
            //     const submitBtn = form.querySelector('button[type="submit"]');
            //     if (submitBtn) {
            //         submitBtn.disabled = true;
            //         submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            //     }
            // });
                    const data = await response.json();

            // Create new variant card
            const div = document.createElement('div');
            div.innerHTML = data.html;
            const newVariant = div.firstElementChild;
            variantsContainer.appendChild(newVariant);

            // Initialize any plugins for the new variant
            initializeVariantComponents(newVariant);

            // Scroll to the new variant
            newVariant.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (error) {
            console.error('Error adding variant:', error);
            showToast('Failed to add variant. Please try again.', 'error');
        } finally {
            // Reset button state
            e.target.disabled = false;
            e.target.textContent = 'Add Variant';
        }
    });

    // Event delegation for dynamic elements
    variantsContainer.addEventListener('click', async function(e) {
        const target = e.target;

        // Remove variant
        if (target.classList.contains('remove-variant')) {
            e.preventDefault();
            const card = target.closest('.variant-card');
            const variantCards = document.querySelectorAll('.variant-card');

            if (variantCards.length <= 1) {
                showToast('You must have at least one variant.', 'warning');
                return;
            }

            // Confirm deletion
            if (!confirm('Are you sure you want to remove this variant?')) {
                return;
            }

            // Add fade-out animation
            card.style.transition = 'opacity 0.3s ease';
            card.style.opacity = '0';

            // Wait for animation to complete before removing
            setTimeout(() => {
                card.remove();
                reindexVariants();
            }, 300);
        }

        // Add option
        if (target.classList.contains('add-option')) {
            e.preventDefault();
            const variantIndex = target.dataset.variant;
            const optionsContainer = document.getElementById(`options-container-${variantIndex}`);
            const optionIndex = optionsContainer.children.length;

            try {
                // Show loading state
                target.disabled = true;
                target.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';

                const response = await fetch(`{{ route('admin.products.option') }}?variant=${variantIndex}&index=${optionIndex}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                const div = document.createElement('div');
                div.innerHTML = data.html;
                const newOption = div.firstElementChild;
                optionsContainer.appendChild(newOption);

                // Scroll to the new option
                newOption.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            } catch (error) {
                console.error('Error adding option:', error);
                showToast('Failed to add option. Please try again.', 'error');
            } finally {
                // Reset button state
                target.disabled = false;
                target.textContent = 'Add Option';
            }
        }

        // Remove option
        if (target.classList.contains('remove-option')) {
            e.preventDefault();
            const row = target.closest('.option-row');
            const container = row.parentElement;

            if (container.children.length <= 1) {
                showToast('You must have at least one option.', 'warning');
                return;
            }

            // Add fade-out animation
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';

            // Wait for animation to complete before removing
            setTimeout(() => {
                row.remove();
                reindexOptions(container);
            }, 300);
        }
    });

    // Helper function to reindex variants after deletion
    function reindexVariants() {
        document.querySelectorAll('.variant-card').forEach((card, index) => {
            // Update variant number display
            const headerSpan = card.querySelector('.card-header span');
            if (headerSpan) {
                headerSpan.textContent = `Variant #${index + 1}`;
            }

            // Update all variant indices in the form inputs
            const inputs = card.querySelectorAll('[name^="variants["]');
            inputs.forEach(input => {
                input.name = input.name.replace(/variants\[\d+\]/, `variants[${index}]`);
            });

            // Update the variant index in the add option button
            const addOptionBtn = card.querySelector('.add-option');
            if (addOptionBtn) {
                addOptionBtn.dataset.variant = index;
            }

            // Update the options container ID
            const optionsContainer = card.querySelector('.options-container');
            if (optionsContainer) {
                optionsContainer.id = `options-container-${index}`;
            }
        });
    }

    // Helper function to reindex options after deletion
    function reindexOptions(container) {
        container.querySelectorAll('.option-row').forEach((row, index) => {
            const inputs = row.querySelectorAll('[name*="[options]"]');
            inputs.forEach(input => {
                input.name = input.name.replace(/options\]\[\d+\]/, `options][${index}]`);
            });
        });
    }

    // Initialize any plugins/components for variants
    function initializeVariantComponents(element) {
        // Initialize select2 if used
        if (typeof $.fn.select2 !== 'undefined') {
            $(element).find('select').select2();
        }

        // Initialize file input preview if needed
        const fileInputs = element.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const preview = input.closest('.form-group').querySelector('.image-preview');
                        if (preview) {
                            preview.src = event.target.result;
                            preview.style.display = 'block';
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Auto remove after hide
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '11';
        document.body.appendChild(container);
        return container;
    }
});
</script>

<style>
    @media (min-width: 768px) {
    .col-md-3 {
        margin: -19px;
        margin-top: 10px !important;
        margin-left: 1px;
    }
    .trash-btn{
        width: 13% !important;
    }
}
</style>