<script>
var conn = new WebSocket('ws://192.168.1.13:8080');
conn.onopen = function(e) {
    console.log("Connection established!");

};

conn.onmessage = function(e) {
    console.log(e.data);
};

</script>
