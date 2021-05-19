console.log('Turning on server...');
const middleware = require('./middleware.js');
const axios = require('axios').default;
const HTMLParser = require('node-html-parser');
const arrayColumn = (array, name) => Object.values(array).map(value => value[name]);
const randomNumber = () => Math.floor((Math.random() * 10));
const randomCode = (length) => Array.apply(null, {length: length}).map(() => randomNumber()).join('');
var io = require('socket.io')(3000, {
    cors: {
        origin: '*',
    }
});

var users = {};

console.log('Server running...');

io.use(middleware.main);

io.sockets.on('connection', (socket) => {
	let handshake = socket.handshake.query;
	if (handshake.type === 'viewer') {
		let pairKey = randomCode(4);
		users[socket.id] = {
            socketId: socket.id,
            name: handshake.name,
            pairKey: pairKey,
            type: 'viewer',
            pairedDevice: null,
            pairAttempt: []
        };
		io.to(socket.id).emit('pair-key', pairKey);
	} else if (handshake.type === 'pilot') {
		users[socket.id] = {
            socketId: socket.id,
            name: handshake.name,
            type: 'pilot',
            pairedDevice: null
        };
	} else {
        return false;
    }
	console.log('Connected');

	socket.on('disconnect', () => {
        if (users[socket.id].pairedDevice) {
            io.to(users[socket.id].pairedDevice).emit('connection-status', 'Disconnected');
        }

        delete users[socket.id];
		console.log('Disconnected');
	});

	socket.on('url', async (data) => {
		if (!users[socket.id].pairedDevice) {
            return false;
        }
        if (users[socket.id].type !== 'pilot') {
            return false;
        }

        if (data.rmfon) {
            let audioUrl = await getRmfonAudioUrl(data.url);
            io.to(users[socket.id].pairedDevice).emit('play-data', {
                iframe: 'about:blank',
                audio: audioUrl
            });
        } else {
            let youtubeUrl = getYoutubeUrl(data.url);
            io.to(users[socket.id].pairedDevice).emit('play-data', {
                iframe: youtubeUrl,
                audio: ''
            });
        }
	});

	socket.on('change-volume', (data) => {
		if (users[socket.id].pairedDevice) {
			io.to(users[socket.id].pairedDevice).emit('change-volume', data);
		}
	});

	socket.on('pair-attempt', (data) => {
        if (typeof data !== 'string' || data.trim().length <= 0) {
            return false;
        }
        let pairArrayId = arrayColumn(users, 'pairKey').indexOf(data);
		if (pairArrayId > -1) {
            pairArrayId = Object.keys(users)[pairArrayId];
            users[pairArrayId].pairAttempt.push(socket.id);
			io.to(users[pairArrayId].socketId).emit('pair-attempt', {
                name: users[socket.id].name,
                id: socket.id
            });
		}
	});

	socket.on('pair-result', (data) => {
        if (users[socket.id].pairAttempt.indexOf(data.id) < 0) return false;
        if (typeof users[data.id] === typeof undefined) return false;

        users[socket.id].pairAttempt.splice(users[socket.id].pairAttempt.indexOf(data.id), 1);
		if (data.result) {
			users[socket.id].pairedDevice = data.id;
			users[data.id].pairedDevice = socket.id;
			io.to(data.id).emit('pair-result', {
                result: true,
                name: users[socket.id].name
            });
            io.to(data.id).emit('connection-status', `Success. Paired with ${users[socket.id].name}`);
		} else {
			io.to(data.id).emit('pair-result', {
                result: false
            });
            io.to(data.id).emit('connection-status', 'Conection canceled');
		}
	});
});

var getRmfonAudioUrl = async (stationId) => {
    let data = await axios.request({
        url: `http://www.rmfon.pl/stacje/flash_aac_${stationId}.xml.txt`,
        timeout: 1800
    });

    var root = HTMLParser.parse(data.data);
    return root.querySelector('item_mp3').rawText;
}

var getYoutubeUrl = (url) => {
    let parsed = new URL(url), video;
    if (parsed.hostname === 'youtu.be') {
        video = parsed.pathname.substring(1);
    } else {
        video = parsed.searchParams.get('v');
    }
    return `https://www.youtube.com/embed/${video}?autoplay=1`;
}
