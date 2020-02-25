import MessageFlash from "../utils/messageFlash";
import Loader from "../utils/loader";

// Requête Ajax pour mettre à jour les informations individuelles
export default class UpdateEvaluation {

    constructor(ajaxRequest) {
        this.ajaxRequest = ajaxRequest;
        this.formElt = document.querySelector("form[name='evaluation_group']");
        this.btnSubmitElts = this.formElt.querySelectorAll("button[type='submit']");
        this.loader = new Loader();
        this.init();
    }

    init() {
        this.btnSubmitElts.forEach(btnSubmitElt => {
            btnSubmitElt.addEventListener("click", function (e) {
                e.preventDefault();
                this.loader.on();
                let formToString = new URLSearchParams(new FormData(this.formElt)).toString();
                this.ajaxRequest.init("POST", btnSubmitElt.getAttribute("data-url"), this.response.bind(this), true, formToString);
            }.bind(this));
        })
    }

    response(response) {
        let data = JSON.parse(response);
        if (data.code === 200 && data.alert === "success") {
            document.getElementById("js-updatedSupport").textContent = "(modifié le " + data.date + " par " + data.user + ")";
        }
        this.loader.off();
        new MessageFlash(data.alert, data.msg);
    }
}