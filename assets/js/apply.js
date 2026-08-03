document.addEventListener('DOMContentLoaded', function () {
    // Select elements
    document.querySelectorAll('select.select-field').forEach(function (select) {
        function checkSelect() {
            var wrap = select.parentElement.querySelector('.other-input-wrap');
            if (wrap) {
                wrap.classList.toggle('d-none', select.value !== 'other');
            }
        }
        select.addEventListener('change', checkSelect);
        checkSelect();
    });

    // Radio elements
    document.querySelectorAll('.form-check-input[type="radio"]').forEach(function (radio) {
        var name = radio.name;
        function checkRadio() {
            var checkedRadio = document.querySelector('input[name="' + name + '"]:checked');
            var wrap = radio.closest('.col-md-12, .col-md-6, .col-md-4').querySelector('.other-input-wrap');
            if (wrap) {
                wrap.classList.toggle('d-none', !checkedRadio || checkedRadio.value !== 'other');
            }
        }
        radio.addEventListener('change', function () {
            checkRadio();
        });
        checkRadio();
    });

    // Checkbox elements
    document.querySelectorAll('.form-check-input[type="checkbox"].other-checkbox').forEach(function (checkbox) {
        function checkCheckbox() {
            var wrap = checkbox.closest('.col-md-12, .col-md-6, .col-md-4').querySelector('.other-input-wrap');
            if (wrap) {
                wrap.classList.toggle('d-none', !checkbox.checked);
            }
        }
        checkbox.addEventListener('change', checkCheckbox);
        checkCheckbox();
    });

    // Custom Attachment Field Hybrid System
    document.querySelectorAll('.attachment-field-container').forEach(function (container) {
        var fieldId = container.getAttribute('data-field-id');
        var maxFiles = parseInt(container.getAttribute('data-max-files')) || 5;
        var maxSizeMB = parseFloat(container.getAttribute('data-max-size')) || 5;
        var allowedExtensionsStr = container.getAttribute('data-allowed-extensions') || 'pdf,jpg,png,jpeg';
        var allowedExtensions = allowedExtensionsStr.toLowerCase().split(',').map(function (ext) { return ext.trim(); });

        var msgLimitReached = container.getAttribute('data-msg-limit-reached') || 'Limit reached';
        var msgSizeExceeded = container.getAttribute('data-msg-size-exceeded') || 'Size exceeded';
        var msgInvalidExtension = container.getAttribute('data-msg-invalid-extension') || 'Invalid extension';
        var msgInvalidUrl = container.getAttribute('data-msg-invalid-url') || 'Invalid URL';

        var hiddenInputsContainer = container.querySelector('.hidden-inputs-container');
        var attachmentsList = container.querySelector('.attachments-list');
        var urlInputBox = container.querySelector('.url-input-box');
        var urlInput = container.querySelector('.url-input');
        var urlError = container.querySelector('.url-error');
        var jsValidationError = container.querySelector('.js-validation-error');

        var uploadBtn = container.querySelector('.upload-btn');
        var addUrlBtn = container.querySelector('.add-url-btn');
        var addUrlSubmitBtn = container.querySelector('.add-url-submit-btn');
        var addUrlCancelBtn = container.querySelector('.add-url-cancel-btn');

        // Helper to update the attachments list visibility and content
        function refreshListVisibility() {
            var itemsCount = attachmentsList.children.length;
            if (itemsCount > 0) {
                attachmentsList.classList.remove('d-none');
            } else {
                attachmentsList.classList.add('d-none');
            }
        }

        // Helper to calculate total size of all currently selected files
        function calculateTotalSize() {
            var total = 0;
            hiddenInputsContainer.querySelectorAll('input[type="file"]').forEach(function (fileInput) {
                if (fileInput.files && fileInput.files[0]) {
                    total += fileInput.files[0].size;
                }
            });
            return total; // in bytes
        }

        // Helper to get total number of attachments (files + urls)
        function getAttachmentsCount() {
            return attachmentsList.children.length;
        }

        // Handle File Upload
        if (uploadBtn) {
            uploadBtn.addEventListener('click', function () {
                // Clear errors
                jsValidationError.classList.add('d-none');
                jsValidationError.innerText = '';

                if (getAttachmentsCount() >= maxFiles) {
                    var limitMsg = msgLimitReached.replace(':max', maxFiles);
                    jsValidationError.innerText = limitMsg;
                    jsValidationError.classList.remove('d-none');
                    return;
                }

                // Create a dynamic file input
                var fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'field_' + fieldId + '_files[]';
                fileInput.classList.add('attachment-file-input');

                fileInput.addEventListener('change', function () {
                    if (!fileInput.files || fileInput.files.length === 0) {
                        fileInput.remove();
                        return;
                    }

                    var file = fileInput.files[0];
                    var filename = file.name;
                    var extension = filename.split('.').pop().toLowerCase();

                    // 1. Check extension
                    if (allowedExtensions.indexOf(extension) === -1) {
                        var extMsg = msgInvalidExtension.replace(':extensions', allowedExtensionsStr);
                        jsValidationError.innerText = extMsg;
                        jsValidationError.classList.remove('d-none');
                        fileInput.remove();
                        return;
                    }

                    // 2. Check total size
                    var currentTotalSize = calculateTotalSize() + file.size;
                    var maxSizeInBytes = maxSizeMB * 1024 * 1024;
                    if (currentTotalSize > maxSizeInBytes) {
                        var sizeMsg = msgSizeExceeded.replace(':max', maxSizeMB);
                        jsValidationError.innerText = sizeMsg;
                        jsValidationError.classList.remove('d-none');
                        fileInput.remove();
                        return;
                    }

                    // 3. Check count limit
                    if (getAttachmentsCount() >= maxFiles) {
                        var countMsg = msgLimitReached.replace(':max', maxFiles);
                        jsValidationError.innerText = countMsg;
                        jsValidationError.classList.remove('d-none');
                        fileInput.remove();
                        return;
                    }

                    // All checks passed! Add input to DOM container
                    hiddenInputsContainer.appendChild(fileInput);

                    // Add a list item
                    var listItem = document.createElement('div');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                    var sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    listItem.innerHTML = '<span><i class="bi bi-file-earmark-code"></i> ' + filename + ' <small class="text-muted">(' + sizeInMB + ' MB)</small></span>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="bi bi-trash"></i></button>';

                    listItem.querySelector('.remove-item-btn').addEventListener('click', function () {
                        fileInput.remove();
                        listItem.remove();
                        refreshListVisibility();
                        jsValidationError.classList.add('d-none');
                    });

                    attachmentsList.appendChild(listItem);
                    refreshListVisibility();
                });

                fileInput.click();
            });
        }

        // Handle URL section toggles
        if (addUrlBtn) {
            addUrlBtn.addEventListener('click', function () {
                jsValidationError.classList.add('d-none');
                urlError.classList.add('d-none');
                urlInput.value = '';
                urlInputBox.classList.toggle('d-none');
            });
        }

        if (addUrlCancelBtn) {
            addUrlCancelBtn.addEventListener('click', function () {
                urlInputBox.classList.add('d-none');
                urlError.classList.add('d-none');
            });
        }

        if (addUrlSubmitBtn) {
            addUrlSubmitBtn.addEventListener('click', function () {
                urlError.classList.add('d-none');
                urlError.innerText = '';

                var urlValue = urlInput.value.trim();
                if (!urlValue) {
                    return;
                }

                // 1. Validate URL regex
                var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?.*?$/i;
                if (!urlPattern.test(urlValue)) {
                    urlError.innerText = msgInvalidUrl;
                    urlError.classList.remove('d-none');
                    return;
                }

                // 2. Check count limit
                if (getAttachmentsCount() >= maxFiles) {
                    var countMsg = msgLimitReached.replace(':max', maxFiles);
                    urlError.innerText = countMsg;
                    urlError.classList.remove('d-none');
                    return;
                }

                // Append a hidden URL input
                var hiddenUrlInput = document.createElement('input');
                hiddenUrlInput.type = 'hidden';
                hiddenUrlInput.name = 'field_' + fieldId + '_urls[]';
                hiddenUrlInput.value = urlValue;
                hiddenInputsContainer.appendChild(hiddenUrlInput);

                // Add a list item
                var listItem = document.createElement('div');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

                // Display nicely
                var displayLabel = urlValue;
                if (displayLabel.length > 50) {
                    displayLabel = displayLabel.substring(0, 47) + '...';
                }
                listItem.innerHTML = '<span><i class="bi bi-link-45deg"></i> <span class="text-truncate">' + displayLabel + '</span></span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="bi bi-trash"></i></button>';

                listItem.querySelector('.remove-item-btn').addEventListener('click', function () {
                    hiddenUrlInput.remove();
                    listItem.remove();
                    refreshListVisibility();
                });

                attachmentsList.appendChild(listItem);
                refreshListVisibility();

                // Clear and hide box
                urlInput.value = '';
                urlInputBox.classList.add('d-none');
            });
        }
    });
});
