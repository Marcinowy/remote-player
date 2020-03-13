console.log('Turning on server...');
var io = require('socket.io')(3000),
middleware = require('./middleware.js');
const { exec } = require('child_process');

users=[];

var array_column = (array,name) => array.map(value => value[name]),
random_number = () => Math.floor((Math.random() * 10)),
random_code = length => Array.apply(null, {length: length}).map(() => random_number()).join('');

console.log('Server running...');

io.use(middleware.main);

io.sockets.on('connection', function(socket) {
	var this_params = socket.handshake.query;
	if (this_params.type == 1) {
		let pair_key = random_code(4);
		users.push({
            'socket_id': socket.id,
            'name': this_params.name,
            'pair_key': pair_key,
            'type': 1,
            'paired_device': null
        });
		io.to(socket.id).emit('pair_key', pair_key);
	} else {
		users.push({
            'socket_id': socket.id,
            'name': this_params.name,
            'pair_key': '',
            'type': 2,
            'paired_device': null
        });
	}
	console.log('Connected');
	socket.on('disconnect', function() {
        let userArrayId = array_column(users, 'socket_id').indexOf(socket.id);

		io.to(users[userArrayId].paired_device).emit('dev_disc', 1);
        users.splice(userArrayId, 1);
        
		console.log('Disconnected');
	});
	socket.on('url', function(data) {
        var userArrayId = array_column(users, 'socket_id').indexOf(socket.id);
		if (users[userArrayId].paired_device === null) {
            return false;
        }
        if (users[userArrayId].type !== 2) {
            return false;
        }

        if (data.rmfon == 1) {
            exec('php ' + __dirname + '/get_stream.php -i ' + data.url, (err, data) => {
                if (err) throw err;
                io.to(users[userArrayId].paired_device).emit('yt_url', {
                    iframe: 'about:blank',
                    audio: data
                });
            });
        } else {
            if (data.url.indexOf('youtu.be') != -1) {
                url = data.url.split('youtu.be/');
                url = url[1].split('/');
                url = url[0];
            } else {
                url = data.url.split('v=');
                url = url[1].split('&');
                url = url[0];
            }
            url = 'https://www.youtube.com/embed/' + url + '?autoplay=1';
            io.to(users[userArrayId].paired_device).emit('yt_url', {
                iframe: url,
                audio: ''
            });
        }
	});
	socket.on('change_volume', function(data) {
        var userArrayId = array_column(users, 'socket_id').indexOf(socket.id);
		if (users[userArrayId].paired_device !== null) {
			io.to(users[userArrayId].paired_device).emit('change_volume', data);
		}
	});
	socket.on('pair_attempt', function(data) {
        if (typeof data !== 'string' || data === '') {
            return false;
        }
        var userArrayId = array_column(users, 'socket_id').indexOf(socket.id);
		if (array_column(users, 'pair_key').indexOf(data) > -1) {
			io.to(users[array_column(users, 'pair_key').indexOf(data)].socket_id).emit('pair_attempt', {
                name: users[userArrayId].name,
                id: socket.id
            });
		}
	});
	socket.on('pair_result', function(data) {
		if (data.result == 1) {
			users[array_column(users, 'socket_id').indexOf(socket.id)].paired_device = data.id;
			users[array_column(users, 'socket_id').indexOf(data.id)].paired_device = socket.id;
			io.to(data.id).emit('pair_result', {
                'result': 1,
                'name': users[array_column(users, 'socket_id').indexOf(socket.id)].name
            });
		} else {
			io.to(data.id).emit('pair_result', {'result': 0});
		}
	});
});