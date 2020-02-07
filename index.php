<html>
<head>
	<title>Remote player</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</head>	
<body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.3/socket.io.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
<style>
body {
	background-color: #111;
	font: normal normal 18px/1.4 PT Sans,Arial,Tahoma,Verdana,sans-serif;
	color: #fff;
}
#viewer_container {
	text-align: center;
}
#pilot_container {
	text-align: center;
}
#device_name {
	width: 310px!important;
}
.shop_btn {
	color: #2196F3;
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
.shop_btn:hover {
	color: #fff;
	border: 2px solid #2196F3;
	background-color: #2196F3;
}
.hide {
	display: none!important;
}
.text {
	background-color: #fff;
	border: 1px solid #ccc;
	-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
	-moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
	box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
	-webkit-transition: border linear .2s,box-shadow linear .2s;
	-moz-transition: border linear .2s,box-shadow linear .2s;
	-o-transition: border linear .2s,box-shadow linear .2s;
	transition: border linear .2s,box-shadow linear .2s;
	font-size: 16px;
	height: auto;
	padding: 7px 9px;
	border-radius: 4px;
	line-height: 20px;
	color: #555;
}
.text:focus {
	border-color:rgba(82,168,236,0.8);
	outline:0;outline:thin dotted \9;
	-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6);
	-moz-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6);
	box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6)
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
.cont {
	margin-top: 20px;
}
h4 {
	margin-bottom: 5px;
}
</style>
<script>
function setCookie(cname,cvalue,exdays) {var d=new Date();d.setTime(d.getTime()+(exdays*24*60*60*1000));var expires="expires="+d.toUTCString();document.cookie=cname+"="+cvalue+";"+expires+";path=/";}
function getCookie(cname) {var name=cname+"=";var decodedCookie=decodeURIComponent(document.cookie);var ca=decodedCookie.split(';');for(var i=0;i<ca.length;i++) {var c=ca[i];while (c.charAt(0)==' ') {c=c.substring(1);}if (c.indexOf(name)==0) {return c.substring(name.length,c.length);}}return "";}
$(document).ready(function() {
	$("#device_name").val(getCookie("name"));
	$(".select .shop_btn").click(function() {
		$(".select").addClass("hide");
	});
	$("#viewer").click(function() {
		setCookie("name",$("#device_name").val(),30);
		$("#viewer_container").removeClass("hide");
		socket = io("<?=$_SERVER ["SERVER_ADDR"]?>:3000",{'query': "type=1&name="+$("#device_name").val(), 'reconnection': false});
		socket.on('yt_url',function(data) {
			$("#yt_url").attr("src",data.iframe);
			$("#audio").attr("src",data.audio);
		});
		socket.on('pair_key',function(data) {
			$("#status span").html(data);
		});
		socket.on('pair_attempt',function(data) {
			if (confirm('Do you want to connect with '+data.name+'?')) {
				socket.emit('pair_result',{"result":1,"id":data.id});
				$("#pair_info").html("Connected with "+data.name);
			} else  {
				socket.emit('pair_result',{"result":0,"id":data.id});
			}
		});
		socket.on('dev_disc',function(data) {
			$("#pair_info").html("Disconnected");
		});
		socket.on('change_volume',function(data) {
			$("#audio")[0].volume=data/100;
		});
	});
	$("#pilot").click(function() {
		setCookie("name",$("#device_name").val(),30);
		$("#pilot_container").removeClass("hide");
		$("#pair_code").focus();
		socket = io("<?=$_SERVER ["SERVER_ADDR"]?>:3000",{'query': "type=2&name="+$("#device_name").val(), 'reconnection': false});
		$("#connect").click(function() {
			if ($("#pair_code").val()) {
				socket.emit('pair_attempt',$("#pair_code").val());
				$("#connection_status").html("Pairing...");
			} else {
				alert ("Enter pair code");
			}
		});
		socket.on('pair_result',function(data) {
			if (data.result==1) {
				$("#connection_status").html("Success. Paired with "+data.name);
				$("#pair_code").addClass("hide");
				$("#connect").addClass("hide");
				$("#url_form").removeClass("hide");
			} else {
				$("#connection_status").html("Conection canceled");
			}
		});
		socket.on('dev_disc',function(data) {
			$("#connection_status").html("Disconnected");
		});
	});
	$("#btn").click(function() {
		socket.emit('url',{"url":$("#url").val(),"rmfon":0});
	});
	$("#rmf_on_play").click(function() {
		socket.emit('url',{"url":$("#stations select").val(),"rmfon":1});
	});
	$("#rmf_on").click(function() {
		$(this).addClass("hide");
		$("#stations").removeClass("hide");
		$.ajax({type: "POST",url: "rmfon.php", contentType: "application/json", success: function(result){
			for (i=0;i<result.length;i++) {
				$("#stations select").append("<option value=\""+result[i].id+"\">"+result[i].name+"</option>");
			}
		},error: function() {
		}});
	});
	$("#volume").change(function() {
		socket.emit('change_volume',this.value);
	});
});
</script>
<div id="viewer_container" class="hide">
	<div id="status" style="font-size: 68px;text-align:center;">Pair code: <span></span></div>
	<div id="pair_info" style="text-align: center;"></div>
	<iframe id="yt_url" src="about:blank" style="width:80%;height:75%;border:0" allow="autoplay"></iframe>
	<audio style="display:none" autoplay="true" id="audio"></audio>
</div>
<div id="pilot_container" class="hide">
	<div><input type="number" placeholder="Type pair code" id="pair_code" class="text"></div>
	<div class="shop_btn" id="connect">Connect</div>
	<div id="connection_status"></div>
	<div id="url_form" class="hide">
		<div><input type="text" id="url" class="text" placeholder="Youtube link"><div class="shop_btn" id="btn">Play</div></div>
		<div class="cont">
			<div class="shop_btn" id="rmf_on">Otwórz listę stacji w rmf on</div>
			<span id="stations" class="hide"><select></select><div class="shop_btn" id="rmf_on_play">Włącz radio</div></span>
		</div>
		<div>
			<h4>Volume:</h4>
			<input type="range" min="1" max="100" value="100" class="slider" id="volume">
		</div>
	</div>
</div>
<div class="select">
	<div style="text-align: center"><input type="text" placeholder="Type device name" id="device_name" class="text"></div>
	<div style="text-align: center">
		<div class="shop_btn" id="viewer">Viewer</div>
		<div class="shop_btn" id="pilot">Pilot</div>
	</div>
</div>
</body></html>