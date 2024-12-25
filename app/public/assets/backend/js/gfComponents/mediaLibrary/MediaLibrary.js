import Initiator from "./Initiator/Initiator.js";
import {initiatorSelectors} from "./Initiator/initiatorSelectors.js";
import Modal from "./Modal/Modal.js";

export default class MediaLibrary {
    initiators = [];
    constructor(eventEmitter, allowDelete = false) {
        this.eventEmitter = eventEmitter;
        this.modal = new Modal(this.eventEmitter, allowDelete);
        this.allowDelete = allowDelete;
    }

    getAllowDelete() {
        return this.allowDelete;
    }

    mount() {
        this.modal.mount();
        this.mountExistingInitiators();
    }

    mountExistingInitiators() {
        document.querySelectorAll(`.${initiatorSelectors.classes.mediaLibraryInitiator}`).forEach((initiatorElement) => {
            let initiator = new Initiator({initiatorElement: initiatorElement, modal: this.modal});
            this.initiators.push(initiator);
            initiator.mount();
        });
    }

    mountSingleInitiator(element) {
        let initiator = new Initiator({initiatorElement: element, modal: this.modal});
        this.initiators.push(initiator);
        initiator.mount();
        return initiator;
    }

    makeAndMountInitiator(
        multiple = false,
        insertable = false,
        className = '',
        text = 'Media Library'
    ) {
        let initiatorButton = document.createElement('div');
        initiatorButton.setAttribute(initiatorSelectors.attributes.multipleSelect, (multiple ?? false).toString());
        initiatorButton.setAttribute(initiatorSelectors.attributes.insertable, (insertable ?? false).toString());
        if(className) {
            initiatorButton.classList.add(className);
        }
        initiatorButton.innerText = text;
        let initiator = new Initiator({initiatorElement: initiatorButton, modal: this.modal});
        this.initiators.push(initiator);
        initiator.mount();
        return initiator;
    }
}