<html>
<head>
	<title>Remote player</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
</head>	
<body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
<style>
body {
	background-color: #111;
	font: normal normal 18px/1.4 PT Sans,Arial,Tahoma,Verdana,sans-serif;
	color: #fff;
    text-align: center;
}
#device_name {
	width: 310px!important;
}
.form-control {
    display: inline-block;
    width: auto;
}
.my_btn {
	color: #2196F3;
    background-color: transparent;
    border: 2px solid #2196F3;
    min-width: 150px;
	padding: 0 10px;
	border-radius: 6px;
	display: inline-block;
	margin: 2px;
	text-align: center;
	cursor: pointer;
	transition: all .2s ease-out;
	height: 38px;
	line-height: 34px;
	box-sizing: border-box;
	font-size: 14px;
}
.my_btn:hover {
	color: #fff;
	border: 2px solid #2196F3;
	background-color: #2196F3;
}
.slider {
	-webkit-appearance: none;
	width: 25%;
	height: 25px;
	background: #d3d3d3;
	outline: none;
	opacity: 0.7;
	-webkit-transition: .2s;
	transition: opacity .2s;
}
.slider:hover {
	opacity: 1;
}
.slider::-webkit-slider-thumb {
	-webkit-appearance: none;
	appearance: none;
	width: 25px;
	height: 25px;
	background: #4CAF50;
	cursor: pointer;
}
.slider::-moz-range-thumb {
	width: 25px;
	height: 25px;
	background: #4CAF50;
	cursor: pointer;
}
h4 {
	margin-bottom: 5px;
}
</style>
<script>
function setCookie(cname,cvalue,exdays) {var d=new Date();d.setTime(d.getTime()+(exdays*24*60*60*1000));var expires="expires="+d.toUTCString();document.cookie=cname+"="+cvalue+";"+expires+";path=/";}
function getCookie(cname) {var name=cname+"=";var decodedCookie=decodeURIComponent(document.cookie);var ca=decodedCookie.split(';');for(var i=0;i<ca.length;i++) {var c=ca[i];while (c.charAt(0)==' ') {c=c.substring(1);}if (c.indexOf(name)==0) {return c.substring(name.length,c.length);}}return "";}

var wsConnect = type => {
    var wsUrl = "<?=$_SERVER ["SERVER_ADDR"]?>:3000";
    socket = io(wsUrl, {
        query: "type=" + type + "&name=" + $("#device_name").val(),
        reconnection: false
    });
    socket.on('connection_status',function(data) {
        $("#connection_status").html(data);
    });
}

$(document).ready(function() {
	$("#device_name").val(getCookie("name"));
	$("#viewer").click(function() {
        if ($("#device_name").val().length <= 0) {
            return false;
        }
		$(".select").addClass("d-none");
		setCookie("name",$("#device_name").val(),30);
        $("#viewer_container").removeClass("d-none");
        wsConnect(1);
		socket.on('play-data',function(data) {
			$("#yt-frame").attr("src",data.iframe);
			$("#audio").attr("src",data.audio);
		});
		socket.on('pair_key',function(data) {
			$("#status span").html(data);
		});
		socket.on('pair_attempt',function(data) {
			if (confirm('Do you want to connect with '+data.name+'?')) {
				socket.emit('pair_result',{"result":1,"id":data.id});
				$("#connection_status").html("Connected with "+data.name);
			} else  {
				socket.emit('pair_result',{"result":0,"id":data.id});
			}
		});
		socket.on('change_volume',function(data) {
			$("#audio")[0].volume = data/100;
		});
	});
	$("#pilot").click(function() {
        if ($("#device_name").val().length <= 0) {
            return false;
        }
		$(".select").addClass("d-none");
		setCookie("name",$("#device_name").val(),30);
		$("#pilot_container").removeClass("d-none");
        $("#pair_code").focus();
        wsConnect(2);
		$("#pair_code_form").submit(function() {
			if ($("#pair_code").val()) {
				socket.emit('pair_attempt',$("#pair_code").val());
				$("#connection_status").html("Pairing...");
			} else {
				alert ("Enter pair code");
            }
            return false;
		});
		socket.on('pair_result',function(data) {
			if (data.result==1) {
				$("#pair_code_form").addClass("d-none");
				$("#url_form").removeClass("d-none");
			}
		});
	});
	$("#btn").click(function() {
		socket.emit('url',{"url":$("#url").val(),"rmfon":0});
	});
	$("#rmf_on_play").click(function() {
		socket.emit('url',{"url":$("#stations select").val(),"rmfon":1});
	});
	$("#rmf_on").click(function() {
        $(this).addClass("d-none");
        $.ajax({
            type: "POST",
            url: "rmfon.php",
            contentType: "application/json",
            success: function(result) {
                $("#stations").removeClass("d-none");
                $("#volume-container").removeClass("d-none");
                for (i=0;i<result.length;i++) {
                    $("#stations select").append("<option value=\""+result[i].id+"\">"+result[i].name+"</option>");
                }
            },
            error: function() {
                $("#rmf_on").removeClass("d-none");
                alert("Can't connect with server");
            }
        });
	});
	$("#volume").change(function() {
		socket.emit('change_volume',this.value);
	});
});
</script>
<div id="connection_status"></div>
<div id="viewer_container" class="d-none">
	<div id="status" style="font-size:68px">Pair code: <span></span></div>
	<iframe id="yt-frame" src="about:blank" style="width:80%;height:75%;border:0" allow="autoplay"></iframe>
	<audio style="display:none" autoplay="true" id="audio"></audio>
</div>
<div id="pilot_container" class="d-none">
    <form id="pair_code_form">
        <div><input type="number" placeholder="Type pair code" id="pair_code" class="form-control mt-3"></div>
        <input type="submit" class="my_btn" value="Connect">
    </form>
	<div id="url_form" class="d-none">
		<div class="form-group">
            <input type="text" id="url" class="form-control" placeholder="Youtube link">
            <div class="my_btn" id="btn">Play</div>
        </div>
        <div class="form-group">
            <div class="my_btn" id="rmf_on">Otwórz listę stacji w rmf on</div>
            <span id="stations" class="d-none">
                <select class="form-control"></select>
                <div class="my_btn" id="rmf_on_play">Włącz radio</div>
            </span>
        </div>
		<div class="form-group d-none" id="volume-container">
			<h4 class="mb-2">Radio volume:</h4>
			<input type="range" min="1" max="100" value="100" class="slider" id="volume">
		</div>
	</div>
</div>
<div class="select">
	<div>
        <input type="text" placeholder="Type device name" id="device_name" class="form-control mt-3">
    </div>
	<div>
		<div class="my_btn" id="viewer">Viewer</div>
		<div class="my_btn" id="pilot">Pilot</div>
	</div>
</div>
</body></html>