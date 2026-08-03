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
});
