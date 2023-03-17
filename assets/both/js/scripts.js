/**
 * Admin Scripts
 */

(function ($, window, document, pluginObject) {
    "use strict";


    function update_timer_box_clock($element) {

        let timer_area = $element,
            span_distance = timer_area.find('span.distance'),
            distance = parseInt(span_distance.data('distance')),
            span_days = timer_area.find('span.days'),
            span_hours = timer_area.find('span.hours'),
            span_minutes = timer_area.find('span.minutes'),
            span_seconds = timer_area.find('span.seconds'),
            days = 0, hours = 0, minutes = 0, seconds = 0, new_distance = 0;

        if (distance > 0) {
            days = Math.floor(distance / (60 * 60 * 24));
            hours = Math.floor((distance % (60 * 60 * 24)) / (60 * 60));
            minutes = Math.floor((distance % (60 * 60)) / (60));
            seconds = Math.floor((distance % (60)));
        }

        span_days.html(days + 'd');
        span_hours.html(hours + ':');
        span_minutes.html(minutes + ':');
        span_seconds.html(seconds);
        span_distance.data('distance', distance - 1);

        setTimeout(update_timer_box_clock, 1000, $element);
    }


    $(document).on('ready', function () {

        let timer_area = $('.instawp-helper-timer');

        if (timer_area.length > 0 && typeof timer_area !== 'undefined') {
            update_timer_box_clock(timer_area);
        }
    });


})(jQuery, window, document, instawp_helper);

