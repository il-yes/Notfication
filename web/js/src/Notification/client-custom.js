const url = 'ws://localhost:8081';


export class ClientCustom extends HTMLElement {
    constructor() {
        super();

        this.namespace = 'kyc_user';
        this.conn = new WebSocket(url);    
        this.attachShadow({mode: 'open'})
        this.render()

        this._selectors = {
            icon : this.shadowRoot.querySelector('.icon'),
            connexionTpl : this.shadowRoot.querySelector('.js-server-position'),
            clientsTpl : this.shadowRoot.querySelector('.js-server-clients'),
            notificationsTpl : this.shadowRoot.querySelector('.js-server-notifications')
        }
        this.user  = {
            'id' : this.getAttribute('id'),
            'username' : this.getAttribute('username'),
            'email' : this.getAttribute('email')
        };
        this.room = {
            'id'   : this.getAttribute('room'),
            'name' : this.getAttribute('name')
        }
    }

    connectedCallback() {
        this.onOpen();
        this.onMessage();
        this.onClose();
    }

    render() {
        return this.shadowRoot.innerHTML = this.getTemplate() 
    }

    getTemplate() {
        return `
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
            <div>
                <h1>Notifications center</h1>
                <small>
                    <em>${this.getAttribute('title')}</em> 
                    | ${this.getAttribute('name')}
                </small>
                <p><span class="icon"></span><span class="js-server-position"></span></p>
                <p class="js-server-notifications"></p>
                <p class="js-server-clients"></p>
                <p class="js-data-server"></p>
                <p class=""></p>
                <p class=""></p>
                <p class=""></p>
            </div>
        `;

    }
    
    onOpen() {
        return this.conn.onopen = () => {
            console.log("Connection established!");
            this.conn.send(JSON.stringify(this.connect()));
        }
    }

    onMessage() {
        return this.conn.onmessage = (e) => {
            this.messageDispatcher(e.data);
            console.log("On message", e.data);
        }
    }

    onClose() {
        return this.conn.onclose = () => {
            this.conn.send(this.messageDeconnexion);
            console.log("On messcloseage", this.messageDeconnexion);
        };
    }

    connect() {
        var msg = {
            'type'       : 'connexion',
            'msg'        : this.user.username + ' has just connected',
            'data'       : {
                'room' : this.room,
                'is_handler' : false,
                'user'       : this.user
            },
            'namespace'   : this.namespace,
        };
        return msg
    }

    deconnect() {

    }

    messageConnexion(msg) {
        if(msg.icon === 'handler')
        {
            return this.messageConnexionHandler(msg);
        }
        return this.messageConnexionClt(msg) ;
    }

    messageConnexionClt(msg) {
        console.log(msg)
        var text =  `${this.getIcon(msg.icon)} ${msg.msg}` ;
        this._selectors.connexionTpl.innerHTML =text;
    }

    messageConnexionHandler(msg) {
        console.log({msg})
        var text =  `<i class="fa fa-user-check"></i> ${msg.msg}` ;
        this._selectors.connexionTpl.innerHTML = msg.msg;
        this._selectors.icon.innerHTML = this.getIcon(msg.icon);
    }

    messageConnexionServer(msg) {
        console.log(msg);
        var text =  `<i class="fas fa-user-check"></i> ${msg.msg}` ;
        this._selectors.notificationsTpl.innerHTML =text;
    }

    messageDeconnexion(msg) {
        var text =  `<i class="fas fa-user-check"></i> ${msg.msg}` ;
        this._selectors.notificationsTpl.innerHTML =text
    }

    messageScan(msg) {
        if(msg.data.stage === "deconnexion") {
            return this.messageDeconnexion(msg)
        }
        console.log(msg)
        var text =  `${this.getIcon(msg.icon)} ${msg.msg}` ;
        this._selectors.notificationsTpl.innerHTML =text;
    }

    messageDispatcher(msg) {
        var self = this;
        msg = JSON.parse(msg);
        if(msg.namespace !== this.namespace) {
            
            return false;
        }
        

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
    }

    getIcon(response) {
        return response === 'handler' ? `<i class="fas fa-user-check"></i>` : `<i class="fas fa-user-slash"></i>`;
    }
}

customElements.define('client-custom', ClientCustom);