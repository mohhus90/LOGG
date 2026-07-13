/**
 * Shared enhancements for the Add/Edit Employee tabbed form:
 * - Next/Previous navigation buttons inside each tab-pane
 * - Auto-activates the tab containing the first validation error
 * - Shift -> daily work hours autofill
 * - National ID -> birth date autofill (Egyptian NID)
 * - "Generate password" button for the login-credentials tab
 */
(function ($) {
    $(document).ready(function () {
        var $nav = $('#custom-content-below-tab');
        var $tabs = $nav.find('a[data-toggle="pill"]');

        if ($tabs.length) {
            $tabs.each(function (i) {
                var $pane = $($(this).attr('href'));
                var $navButtons = $('<div class="d-flex justify-content-between mt-3 emp-tab-nav-buttons"></div>');

                if (i > 0) {
                    $navButtons.append('<button type="button" class="btn btn-outline-secondary btn-sm emp-tab-prev"><i class="fas fa-arrow-right ml-1"></i> السابق</button>');
                } else {
                    $navButtons.append('<span></span>');
                }

                if (i < $tabs.length - 1) {
                    $navButtons.append('<button type="button" class="btn btn-outline-primary btn-sm emp-tab-next">التالي <i class="fas fa-arrow-left mr-1"></i></button>');
                } else {
                    $navButtons.append('<span></span>');
                }

                $pane.append($navButtons);
            });

            $(document).on('click', '.emp-tab-next', function () {
                var idx = $tabs.index($nav.find('a.active'));
                if (idx > -1 && idx < $tabs.length - 1) {
                    $($tabs.get(idx + 1)).tab('show');
                    $('html, body').animate({ scrollTop: $nav.offset().top - 80 }, 200);
                }
            });

            $(document).on('click', '.emp-tab-prev', function () {
                var idx = $tabs.index($nav.find('a.active'));
                if (idx > 0) {
                    $($tabs.get(idx - 1)).tab('show');
                    $('html, body').animate({ scrollTop: $nav.offset().top - 80 }, 200);
                }
            });

            // Jump to the first tab containing a validation error (after a failed submit)
            var $firstErrorPane = $('.is-invalid').first().closest('.tab-pane');
            if ($firstErrorPane.length) {
                var errorId = $firstErrorPane.attr('id');
                $nav.find('a[href="#' + errorId + '"]').tab('show');
            }
        }

        // Shift -> daily work hours autofill
        $('#shifts_types_id').on('change', function () {
            var hours = $(this).find(':selected').data('hours');
            $('#daily_work_hours').val(hours ? hours : '');
        });

        // National ID -> birth date autofill (Egyptian NID, 14 digits)
        $('#national_id').on('input', function () {
            var nid = $(this).val().trim();
            if (!/^[123]\d{13}$/.test(nid)) return;

            var centuryDigit = nid.charAt(0);
            var centuryPrefix = centuryDigit === '3' ? '20' : (centuryDigit === '2' ? '19' : '18');
            var yy = nid.substr(1, 2);
            var mm = nid.substr(3, 2);
            var dd = nid.substr(5, 2);

            var month = parseInt(mm, 10);
            var day = parseInt(dd, 10);
            if (month < 1 || month > 12 || day < 1 || day > 31) return;

            $('#birth_date').val(centuryPrefix + yy + '-' + mm + '-' + dd);
        });

        // Generate a random human-readable password for the login-credentials tab
        $(document).on('click', '.emp-generate-password', function () {
            var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            var pwd = '';
            for (var i = 0; i < 4; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
            pwd += Math.floor(1000 + Math.random() * 9000);
            $('#login_password').val(pwd);
        });
    });
})(jQuery);
