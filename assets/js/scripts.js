/**
 * Admin Scripts
 */

(function ($, window, document, plugin_object) {
    "use strict";

    function update_timer_box_clock($element) {

        let timer_area = $element, span_distance = timer_area.find('span.distance'), distance = parseInt(span_distance.data('distance')), span_days = timer_area.find('span.days'), span_hours = timer_area.find('span.hours'), span_minutes = timer_area.find('span.minutes'), span_seconds = timer_area.find('span.seconds'), days = 0, hours = 0, minutes = 0, seconds = 0, new_distance = 0;

        if (distance > 0) {
            days = Math.floor(distance / (60 * 60 * 24));
            hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
            minutes = Math.floor((distance % (60 * 60)) / (60));
            seconds = Math.floor((distance % (60)));
        }

        days = days < 10 ? '0' + days : days;
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        span_days.html(days + 'd');
        span_hours.html(hours + ':');
        span_minutes.html(minutes + ':');
        span_seconds.html(seconds);
        span_distance.data('distance', distance - 1);

        setTimeout(update_timer_box_clock, 1000, $element);
    }

    function iwp_copy_to_clipboard(string) {

        let el_input = document.createElement('input');

        document.body.appendChild(el_input);
        el_input.value = string;
        el_input.select();
        document.execCommand('copy', false);
        el_input.remove();
    }

    $(document).on('ready', function () {

        let timer_area = $('.instawp-helper-timer');

        if (timer_area.length > 0 && typeof timer_area !== 'undefined') {
            update_timer_box_clock(timer_area);
        }
    });

    $(document).on('click', '.iwp-dashboard .iwp-logo', function () {
        window.open('https://instawp.com?utm_source=wa_welcome_msg', '_blank');
    });

    $(document).on('click', '.iwp-mu-install-plugin', function () {
        let el_install_btn = $(this),
            install_nonce = el_install_btn.data('install-nonce'),
            plugin_slug = el_install_btn.data('slug'),
            plugin_zip_url = el_install_btn.data('zip-url'),
            plugin_action = el_install_btn.data('plugin-action'),
            plugin_file = el_install_btn.data('plugin-file'),
            text_installing = el_install_btn.data('text-installing'),
            text_installed = el_install_btn.data('text-installed'),
            text_activating = el_install_btn.data('text-activating'),
            text_activated = el_install_btn.data('text-activated'),
            icon_install = el_install_btn.find('.icon-install'),
            icon_installed = el_install_btn.find('.icon-installed');

        if (el_install_btn.hasClass('loading') || el_install_btn.hasClass('activated')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            beforeSend: function () {
                if (plugin_action === 'activate') {
                    el_install_btn.addClass('loading').find('span').html(text_activating);
                } else {
                    el_install_btn.addClass('loading').find('span').html(text_installing);
                }
                icon_install.addClass('hidden');
            },
            data: {
                'action': 'iwp_install_plugin',
                'plugin_action': plugin_action,
                'plugin_slug': plugin_slug,
                'plugin_file': plugin_file,
                'plugin_zip_url': plugin_zip_url,
                'install_nonce': install_nonce,
            },
            success: function (response) {
                if (response.success) {
                    if (plugin_action === 'activate') {
                        el_install_btn.removeClass('loading').addClass('activated').find('span').html(text_activated);
                    } else {
                        el_install_btn.removeClass('loading').addClass('activated').find('span').html(text_installed);
                    }
                    icon_installed.addClass('hidden');
                }
            }
        });

    });

    $(document).on('click', '.iwp-copy-content', function () {

        let el_copy_field = $(this),
            text_copied = el_copy_field.data('text-copied'),
            copy_content = el_copy_field.data('content');

        iwp_copy_to_clipboard(copy_content);

        el_copy_field.after('<span class="copied">' + text_copied + '</span>');

        setTimeout(function () {
            el_copy_field.parent().find('.copied').fadeOut(300).hide();
        }, 3000);
    });

})(jQuery, window, document, iwp_mu_main);

