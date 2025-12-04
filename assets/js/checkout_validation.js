
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="checkout_handler.php"]');
    if (!form) return;

    const requiredFields = form.querySelectorAll('[required]');

    const validateField = (field) => {
        const errorContainer = field.parentElement.querySelector('.invalid-feedback');
        if (!errorContainer) return;

        let isValid = true;
        let errorMessage = '';

        if (field.value.trim() === '') {
            isValid = false;
            errorMessage = 'این فیلد نمی‌تواند خالی باشد.';
        } else if (field.type === 'email' && field.value.trim() !== '' && !/^[\S]+@[\S]+\.[\S]+$/.test(field.value)) {
            isValid = false;
            errorMessage = 'لطفاً یک ایمیل معتبر وارد کنید.';
        }

        if (!isValid) {
            field.classList.add('is-invalid');
            errorContainer.textContent = errorMessage;
            errorContainer.style.display = 'block';
        } else {
            field.classList.remove('is-invalid');
            errorContainer.style.display = 'none';
        }
        return isValid;
    };

    requiredFields.forEach(field => {
        // Create a container for the error message if it doesn't exist
        let errorContainer = field.parentElement.querySelector('.invalid-feedback');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'invalid-feedback';
            // Insert after the input field
            field.parentNode.insertBefore(errorContainer, field.nextSibling);
        }

        field.addEventListener('blur', () => {
            validateField(field);
        });

        // Also validate on input to give immediate feedback
        field.addEventListener('input', () => {
            // Only remove error, don't show it while typing
            if (field.classList.contains('is-invalid')) {
                validateField(field);
            }
        });
    });

    form.addEventListener('submit', function (event) {
        let isFormValid = true;
        requiredFields.forEach(field => {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            event.preventDefault(); // Stop form submission
            // Find the first invalid field and focus it for better UX
            const firstInvalidField = form.querySelector('.is-invalid');
            if(firstInvalidField) {
                firstInvalidField.focus();
            }
        }
    });

    // Handle address selection logic from the original file
    const savedAddressSelect = document.getElementById('saved_address');
    if (savedAddressSelect) {
        savedAddressSelect.addEventListener('change', function() {
            // Clear all fields first
            document.getElementById('first_name').value = '';
            document.getElementById('last_name').value = '';
            document.getElementById('phone_number').value = '';
            document.getElementById('province').value = '';
            document.getElementById('city').value = '';
            document.getElementById('address_line').value = '';
            document.getElementById('postal_code').value = '';
            
            // Clear validation states
            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
                const errorContainer = field.parentElement.querySelector('.invalid-feedback');
                if(errorContainer) errorContainer.style.display = 'none';
            });

            if (this.value) {
                try {
                    const address = JSON.parse(this.value);
                    document.getElementById('first_name').value = address.first_name || '';
                    document.getElementById('last_name').value = address.last_name || '';
                    document.getElementById('phone_number').value = address.phone_number || '';
                    document.getElementById('province').value = address.province || '';
                    document.getElementById('city').value = address.city || '';
                    document.getElementById('address_line').value = address.address_line || '';
                    document.getElementById('postal_code').value = address.postal_code || '';

                    // Re-validate all fields after filling them
                    requiredFields.forEach(field => validateField(field));

                } catch (e) {
                    console.error("Failed to parse address JSON:", e);
                }
            }
        });
    }
});
