<html>
<head>
    <title>Remote player</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/app.css">
</head>    
<body>
    <div id="connection-status"></div>
    <div id="viewer-container" class="d-none">
        <div id="status" style="font-size:68px">Pair code: <span></span></div>
        <iframe id="yt-frame" src="about:blank" allow="autoplay"></iframe>
        <audio class="d-none" autoplay="true" id="audio"></audio>
    </div>
    <div id="pilot-container" class="d-none">
        <form id="pair-code-form">
            <div>
                <input type="number" placeholder="Type pair code" id="pair-code" class="form-control mt-3">
            </div>
            <input type="submit" class="transparent-btn" value="Connect">
        </form>
        <div id="url-form" class="d-none">
            <div class="form-group">
                <input type="text" id="url" class="form-control" placeholder="Youtube link">
                <div class="transparent-btn" id="youtube-play">Play</div>
            </div>
            <div class="form-group">
                <div class="transparent-btn" id="rmfon-load-btn">Otwórz listę stacji w rmf on</div>
                <span id="stations" class="d-none">
                    <select class="form-control"></select>
                    <div class="transparent-btn" id="rmfon-play">Włącz radio</div>
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
            <input type="text" placeholder="Type device name" id="device-name" class="form-control mt-3">
        </div>
        <div>
            <div class="transparent-btn" id="viewer-mode">Viewer</div>
            <div class="transparent-btn" id="pilot-mode">Pilot</div>
        </div>
    </div>
    <script>
        const WS_URL = "<?=$_SERVER ["SERVER_ADDR"]?>:3000";
    </script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="js/cookies.polyfill.js"></script>
    <script type="text/javascript" src="js/app.js"></script>
</body></html>