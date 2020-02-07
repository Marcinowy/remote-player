console.log('Turning on server...');
var io = require('socket.io')(3000);
const { exec } = require('child_process');

users=[];

function array_column(array,name) {
	return array.map(function(value,index) {return value[name];});
}
function random_number() {
	return Math.floor((Math.random() * 10))+"";
}

console.log('Server running...');

io.sockets.on('connection', function(socket) {
	this_params=socket.handshake.query;
	if (this_params.type==1) {
		player=socket.id;
		pair_key=random_number()+random_number()+random_number()+random_number();
		users.push({"socket_id":socket.id,"name":this_params.name,"pair_key":pair_key,"type":1,"paired_device":""});
		io.to(socket.id).emit('pair_key',pair_key);
	} else {
		users.push({"socket_id":socket.id,"name":this_params.name,"pair_key":"","type":2,"paired_device":""});
	}
	console.log('Connected');
	socket.on('disconnect',function(data) {
		io.to(users[array_column(users,"socket_id").indexOf(socket.id)].paired_device).emit('dev_disc',1);
		users.splice(array_column(users,"socket_id").indexOf(socket.id),1);
		console.log('Disconnected');
	});
	socket.on('url',function(data) {
		if (users[array_column(users,"socket_id").indexOf(socket.id)].paired_device!="") {
			if (data.rmfon==1) {
				exec('php '+__dirname+'/get_stream.php -i '+data.url, (err, data) => {
					if (err) throw err;
					io.to(users[array_column(users,"socket_id").indexOf(socket.id)].paired_device).emit('yt_url',{"iframe":"about:blank","audio":data});
				});
			} else {
				if (data.url.indexOf("youtu.be")!=-1) {
					url=data.url.split("youtu.be/");
					url=url[1].split("/");
					url=url[0];
				} else {
					url=data.url.split("v=");
					url=url[1].split("&");
					url=url[0];
				}
				url="https://www.youtube.com/embed/"+url+"?autoplay=1";
				io.to(users[array_column(users,"socket_id").indexOf(socket.id)].paired_device).emit('yt_url',{"iframe":url,"audio":""});
			}
		}
	});
	socket.on('change_volume',function(data) {
		if (users[array_column(users,"socket_id").indexOf(socket.id)].paired_device!="") {
			io.to(users[array_column(users,"socket_id").indexOf(socket.id)].paired_device).emit('change_volume',data);
		}
	});
	socket.on('pair_attempt',function(data) {
		if (array_column(users,"pair_key").indexOf(data)>-1&&data!="") {
			io.to(users[array_column(users,"pair_key").indexOf(data)].socket_id).emit('pair_attempt',{"name":users[array_column(users,"socket_id").indexOf(socket.id)].name,"id":socket.id});
		}
	});
	socket.on('pair_result',function(data) {
		if (data.result==1) {
			users[array_column(users,"socket_id").indexOf(socket.id)].paired_device=data.id;
			users[array_column(users,"socket_id").indexOf(data.id)].paired_device=socket.id;
			io.to(data.id).emit('pair_result',{"result":1,"name":users[array_column(users,"socket_id").indexOf(socket.id)].name});
		} else {
			io.to(data.id).emit('pair_result',{"result":0});
		}
	});
});