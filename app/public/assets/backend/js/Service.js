import Page from "./Page.js";

export default class Service extends Page {

    form = 'serviceForm';

    constructor() {
        super();
        this.attachSubmitEvent();
    }

}