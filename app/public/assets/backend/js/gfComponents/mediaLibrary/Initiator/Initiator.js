import {initiatorOptions} from "./initiatorOptions.js";

export default class Initiator {
    options = new Map();

    constructor(props) {
        this.initiatorElement = props.initiatorElement;
        this.modal = props.modal;
    }

    mount() {
        this.parseOptions();
        this.addOpenMediaLibraryListener();
    }

    getInitiatorElement() {
        return this.initiatorElement;
    }

    parseOptions() {
        Object.keys(initiatorOptions).forEach((option) => {
            let attributeValue = this.initiatorElement.getAttribute(`data-${option}`);
            if(attributeValue === 'true' || attributeValue === '1') {
                attributeValue = true;
            }
            if(!attributeValue || attributeValue === 'false' || attributeValue === '0') {
                attributeValue = initiatorOptions[option];
            }
            this.options.set(option, attributeValue);
        });
    }

    addOpenMediaLibraryListener() {
        this.initiatorElement.addEventListener('click', (e) => {
            e.preventDefault();
            this.modal.open(this.getInitiatorElement(), this.options);
        });
    }

}