export class KycSystem extends HTMLElement {
    constructor() {
        super();
    
        this.attachShadow({mode: 'open'})
        this.render()
        this._selectors = {
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
        this.shadowRoot.querySelector('.title').innerHTML = this.getAttribute('title')
    }

    render() {
        return this.shadowRoot.innerHTML = this.getTemplate() 
    }

    getTemplate() {
        return `
            <div class="">
                <h1>Notifications center</h1>
                <small>
                    <em class="title"></em> 
                    | ${this.getAttribute('name')}
                </small>
                <p class="js-server-position"></p>
                <p class="js-server-notifications"></p>
                <p class="js-server-clients"></p>
                <p class="js-data-server"></p>
                <p class=""></p>
                <p class=""></p>
                <p class=""></p>
            </div>
        `;

    }
  
    connect() {
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
        var text =  msg.icon + ' ' + msg.msg ;
        this._selectors.connexionTpl.innerHTML =text;
    }

    messageConnexionHandler(msg) {
        console.log(msg)
        var text =  msg.icon + ' ' + msg.msg ;
        this._selectors.connexionTpl.innerHTML =text;
    }

    messageConnexionServer(msg) {
        console.log(msg);
        var text =  msg.icon + ' ' + msg.msg ;
        this._selectors.notificationsTpl.innerHTML =text;
    }

    messageDeconnexion(msg) {
        var text =  msg.icon + ' ' + msg.msg ;
        this._selectors.notificationsTpl.innerHTML =text
    }

    messageScan(msg) {
        if(msg.data.stage === "deconnexion") {
            return this.messageDeconnexion(msg)
        }
        console.log(msg)
        var text =  msg.icon + ' ' + msg.msg ;
        this._selectors.notificationsTpl.innerHTML =text;
    }

    messageDispatcher(msg) {
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
    }
}

customElements.define('kyc-system', KycSystem);