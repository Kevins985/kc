var socketJs = socketJs || {};
socketJs = {
    websocket:null,
    url:null,
    socket_status:-1,
    open_callback:null,
    receive_callback:null,
    close_callback:null,
    init: function (options) {
        var opts = {
            url: null,
            open_callback:null,
            receive_callback:null,
            close_callback:null,
        }
        if (options) {
            for (var k in options) {
                opts[k] = options[k];
            }
        }
        this.url = opts.url;
        this.open_callback = opts.open_callback;
        this.receive_callback = opts.receive_callback;
        this.close_callback = opts.close_callback;
        this.initSocket();
    },
    initSocket:function(){
        var client = this;
        if(client.url==null){
            return false;
        }
        if ('WebSocket' in window) {
            client.websocket = new WebSocket(client.url);
        } 
        else if ('MozWebSocket' in window) {
            client.websocket = new MozWebSocket(client.url);
        }
        else{
            alert('浏览器不支持websocket');
        }
        client.websocket.onopen = function(evt) { 
            client.onOpen(evt);
        }; 
        client.websocket.onclose = function(evt) { 
            client.onClose(evt);
        }; 
        client.websocket.onmessage = function(evt) { 
            client.onMessage(evt);
        }; 
        client.websocket.onerror = function(evt) { 
            client.onError(evt);
        }; 
    },
    onOpen:function(evt) { 
        this.writeLog("websocket connect"); 
        if(this.open_callback){
            this.open_callback(evt);
        }
    },
    onClose:function(evt) { 
        this.writeLog("websocket close"); 
        if(this.close_callback){
            this.close_callback(evt);
        }
    },
    onMessage:function(evt) { 
//        this.writeLog('RESPONSE: '+ evt.data);
        if(this.receive_callback){
            this.receive_callback(evt);
        }
        //this.websocket.close(); 
    },
    onError:function(evt){ 
        if(evt.data==undefined){
            this.socket_status = 0;
            this.writeLog("websocket连接失败");
        }
        else{
            this.writeLog('ERROR:'+ evt.data); 
        }
    },
    writeLog:function(msg){
        console.log(msg); 
    },
    send:function(message,callback) {
        var client = this;
        try{
            if(client.socket_status==1){
                if (client.websocket.readyState===1) {
                    var msg = JSON.stringify(message);
                    client.websocket.send(msg);
                    if(callback) callback(true);
                }
                else{
                    //layer.msg('socket还未建立连接',{icon:2});
                    if(callback) callback(false);
                }
            }
            else{
                if(callback) callback(false);
            }
        }
        catch(e){
            client.writeLog(e);
            if(callback) callback(false);
        }
    }
}
