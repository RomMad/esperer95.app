import AjaxRequest from "../utils/ajaxRequest";
import ListNotes from "./listnotes";
import CheckChangeModal from "../utils/checkChangeModal";

let ajaxRequest = new AjaxRequest();

document.addEventListener("DOMContentLoaded", function () {
    new ListNotes(ajaxRequest);
    // new CheckChangeModal("note"); // form name
});