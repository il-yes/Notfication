$( document ).ready(function() {
    console.log( "ready!" );
    var conn = new WebSocket('ws://localhost:8081');
    var serverBox = $('.js-data-server');
    var user  = {
        'id' : $(serverBox).data('id'),
        'username' : $(serverBox).data('username'),
        'email' : $(serverBox).data('email')
    };
    var room = {
        'id'   : $(serverBox).data('room'),
        'name' : 'kyc'
    }


    var conn = new WebSocket('ws://localhost:8081');
    //var notification = new Notification(conn, user, room);

     conn.onopen = function(e, t) {
         var notification = new Notification(conn, user, room);
         var notificationConnexion = notification.connect();

         //console.log(notificationConnexion);
         conn.send(JSON.stringify(notificationConnexion));
     };


    conn.onmessage = function(e) {
        var notification = new Notification(conn, user, room);
        //console.log(e.data);
        //console.log(e.data);
        //console.log(e);
        notification.messageDispatcher(e.data);
    };

    conn.onclose = function() {
        var notification = new Notification(conn, user, room);
        conn.send(notification.messageDeconnexion);
    };





    /**
     * - Notification Object | consctructor
     */
    Notification = function (conn, user, room) {
        this.conn = conn,
        this.user = user,
        this.room = room

    }

    /**
     * - Notification properties
     */
    $.extend(Notification.prototype, {

        _selectors : {
            connexionTpl : $('.js-server-position'),
            clientsTpl : $('.js-server-clients'),
            notificationsTpl : $('.js-server-notifications')
        },

        connect: function () {
            var msg = {
                'type'       : 'connexion',
                'msg'        : this.user.username + ' has just connected',
                'data'       : {
                    'room' : this.room,
                    'is_handler' : false,
                    'user'       : this.user
                }
            };
            return msg
        },

        deconnect: function () {

        },
        messageConnexion : function(msg) {
            if(msg.icon === 'handler')
            {
                return this.messageConnexionHandler(msg);
            }
            return this.messageConnexionClt(msg) ;
        },

        messageConnexionClt : function (msg) {
            console.log(msg)
            var text =  msg.icon + ' ' + msg.msg ;
            $(this._selectors.connexionTpl).html(text);
        },

        messageConnexionHandler : function (msg) {
            console.log(msg)
            var text =  msg.icon + ' ' + msg.msg ;
            $(this._selectors.connexionTpl).html(text);
        },

        messageConnexionServer : function (msg) {
            console.log(msg);
            var text =  msg.icon + ' ' + msg.msg ;
            $(this._selectors.notificationsTpl).html(text);
        },

        messageDeconnexion : function (msg) {
            var text =  msg.icon + ' ' + msg.msg ;
            $(this._selectors.notificationsTpl).html(text);
        },

        messageScan : function (msg) {
            console.log(msg)
            var text =  msg.icon + ' ' + msg.msg ;
            $(this._selectors.notificationsTpl).html(text);
        },

        messageDispatcher : function (msg) {
            var self = this;
            msg = JSON.parse(msg);

            switch (msg.type)
            {
                case 'position' :
                    return self.messageConnexion(msg) ;
                    break;

                case 'connexion' :
                    return self.messageConnexion(msg) ;
                    break;

                case 'deconnexion' :
                    return self.messageDeconnexion(msg) ;
                    break;

                case 'scan_room' :
                    return self.messageScan(msg) ;
                    break;

                case 'initialisation' :
                    return self.messageScan(msg) ;
                    break;
            }
        },

    });

});







