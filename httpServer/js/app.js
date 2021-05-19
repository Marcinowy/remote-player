var socket;

var wsConnect = function(type) {
    var data = {
        type: type,
        name: $('#device-name').val()
    };

    socket = io(WS_URL, {
        query: $.param(data),
        reconnection: false
    });

    socket.on('connection-status', function(data) {
        $('#connection-status').html(data);
    });

    socket.on('play-data', function(data) {
        $('#yt-frame').attr('src', data.iframe);
        $('#audio').attr('src', data.audio);
    });

    socket.on('pair-key', function(data) {
        $('#status span').html(data);
    });

    socket.on('pair-attempt', function(data) {
        var userResponse = confirm(`Do you want to connect with ${data.name}?`);
        socket.emit('pair-result', {
            result: userResponse,
            id: data.id
        });
        if (userResponse) {
            $('#connection-status').html(`Connected with ${data.name}`);
        }
    });

    socket.on('change-volume', function(data) {
        $('#audio')[0].volume = data / 100;
    });

    socket.on('pair-result', function(data) {
        if (data.result) {
            $('#pair-code-form').addClass('d-none');
            $('#url-form').removeClass('d-none');
        }
    });
}

$(function() {
    $('#device-name').val(getCookie('name'));

    $('#viewer-mode').click(function() {
        var deviceName = $('#device-name').val().trim();
        if (deviceName.length <= 0) {
            return false;
        }
        setCookie('name', deviceName, 30);

        $('.select').addClass('d-none');
        $('#viewer-container').removeClass('d-none');
        wsConnect('viewer');
    });

    $('#pilot-mode').click(function() {
        var deviceName = $('#device-name').val().trim();
        if (deviceName.length <= 0) {
            return false;
        }
        setCookie('name', deviceName, 30);

        $('.select').addClass('d-none');
        $('#pilot-container').removeClass('d-none');
        $('#pair-code').focus();
        wsConnect('pilot');

        $('#pair-code-form').submit(function() {
            var pairCode = $('#pair-code').val().trim();
            if (pairCode.length > 0) {
                socket.emit('pair-attempt', pairCode);
                $('#connection-status').html('Pairing...');
            }
            return false;
        });
    });

    $('#youtube-play').click(function() {
        socket.emit('url', {
            url: $('#url').val(),
            rmfon: false
        });
    });

    $('#rmfon-play').click(function() {
        socket.emit('url', {
            url: $('#stations select').val(),
            rmfon: true
        });
    });

    $('#rmfon-load-btn').click(function() {
        $(this).addClass('d-none');
        $.ajax({
            type: 'POST',
            url: 'rmfonStations.php',
            contentType: 'application/json',
            success: function(stations) {
                $('#stations').removeClass('d-none');
                $('#volume-container').removeClass('d-none');
                for (var station of stations) {
                    $('#stations select').append(`<option value="${station.id}">${station.name}</option>`);
                }
            },
            error: function() {
                $('#rmfon-load-btn').removeClass('d-none');
                alert('Cannot connect with server');
            }
        });
    });

    $('#volume').change(function() {
        socket.emit('change-volume', this.value);
    });
});