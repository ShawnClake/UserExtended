function toggleNotificationsPopover(el) {
    if ($(el).closest('form').toggleClass('active').hasClass('active')) {
        $(el).request('onLoadNotifications', {
            update: { '@notifications-list': '#notificationsContent' }
        })
    }
}

function loadOlderNotifications(el) {
    var $form = $(el).closest('form'),
        height = $('ul.notifications', $form).get(0).scrollHeight

    $(el).request('onLoadOlderNotifications', {
        update: { '@notifications-list': '#notificationsContent' }
    }).done(function() {
        $('ul.notifications', $form).animate({ scrollTop: height }, 200)
    })
}

function markNotificationsAsRead(el) {
    $(el).request('onMarkAllNotificationsAsRead', {
        update: { '@notifications-list': '#notificationsContent' }
    })
}
