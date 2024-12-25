import {formSelectors} from "./formSelectors.js";
import {mediaLibrarySelectors} from "../mediaLibrarySelectors.js";
import {formAssets} from "./formAssets.js";
import {formEvents} from "./formEvents.js";
import Loader from "https://skeletor.greenfriends.systems/dtables/1.x/master/src/Loader/Loader.js";

export default class ImageForm {
    container;

    form;

    idInput;

    srcLabel;

    srcInput;

    altLabel;

    altInput;

    labelLabel;

    labelInput;

    authorLabel;

    authorInput;

    submitButton;

    deleteButton;

    previewElement;

    formLoader = new Loader();

    constructor(props) {
        this.cell = props.cell;
        this.allowDelete = props.allowDelete ?? false;
        this.image = this.cell.getImage();
        this.formFieldData = [
            {
                labelFieldName: 'idLabel',
                labelText: 'ID',
                inputFieldName: 'idInput',
                inputValue: this.image.getId(),
                inputType: 'text',
                readOnly :true
            },
            {
                labelFieldName: 'srcLabel',
                labelText: 'Source',
                inputFieldName: 'srcInput',
                inputValue: this.image.getSrc(),
                inputType: 'text',
                readOnly :true
            },
            {
                labelFieldName: 'altLabel',
                labelText: 'Alt',
                inputFieldName: 'altInput',
                inputValue: this.image.getAlt() ?? '',
                inputType: 'text',
                readOnly :false
            },
            {
                labelFieldName: 'labelLabel',
                labelText: 'Label',
                inputFieldName: 'labelInput',
                inputValue: this.image.getLabel() ?? '',
                inputType: 'text',
                readOnly :false
            },
            {
                labelFieldName: 'authorLabel',
                labelText: 'Author',
                inputFieldName: 'authorInput',
                inputValue: this.image.getAuthor() ?? '',
                inputType: 'text',
                readOnly :false
            }
        ];
        this.eventEmitter = props.eventEmitter;
        this.generateFormWithFields();
        this.addSubmitListener();
        this.addDeleteListener();
    }

    getAllowDelete() {
        return this.allowDelete;
    }

    getView() {
        return this.container;
    }

    generateForm() {
        this.form = document.createElement('form');
    }

    generateFormWithFields() {
        this.generateContainer();
        this.generateMessageContainer();
        this.generateForm();
        this.formFieldData.forEach((data) => {
            this.generateLabelAndInput(data);
        });
        this.generateSubmitButton();
        this.generateDeleteButton();
        this.generatePreviewElement();

        this.container.appendChild(this.messageContainer);
        this.container.appendChild(this.previewElement);
        this.form.appendChild(this.submitButton);
        if(this.getAllowDelete()) {
            this.container.appendChild(this.deleteButton);
        }
        this.container.appendChild(this.form);
    }

    generateLabelAndInput(data) {
        this[data.labelFieldName] = document.createElement('label');
        this[data.labelFieldName].innerText = data.labelText;

        this[data.inputFieldName] = document.createElement('input');
        this[data.inputFieldName].classList.add(mediaLibrarySelectors.classes.mediaLibraryInput);
        this[data.inputFieldName].type = data.inputType;
        if(data.inputName) {
            this[data.inputFieldName].name = data.inputName;
        }
        this[data.inputFieldName].value = data.inputValue;
        if(data.readOnly) {
            this[data.inputFieldName].readOnly = true;
        }
        this.form.appendChild(this[data.labelFieldName]);
        this.form.appendChild(this[data.inputFieldName]);
    }

    generateContainer() {
        this.container = document.createElement('div');
        this.container.id = formSelectors.ids.formContainer;
    }

    generateMessageContainer() {
        this.messageContainer = document.createElement('div');
        this.messageContainer.classList.add(formSelectors.classes.mediaLibraryFormMessageContainer);
    }

    generatePreviewElement() {
        this.previewElement = document.createElement('img');
        this.previewElement.src = this.image.getSrc();
    }


    generateSubmitButton() {
        this.submitButton = document.createElement('input');
        this.submitButton.type = 'submit';
        this.submitButton.classList.add(mediaLibrarySelectors.classes.mediaLibraryButton);
    }

    generateDeleteButton() {
        if(!this.getAllowDelete()) {
            return;
        }
        this.deleteButton = document.createElement('div');
        this.deleteButton.id = formSelectors.ids.deleteButton;
        this.deleteButton.innerHTML = formAssets.deleteIcon;
    }

    addSubmitListener() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('submitting data...');
            this.form.classList.add(formSelectors.classes.loading);
            this.formLoader.start(this.container);

            const imageData = this.image.getFormData();
            imageData.append('alt', this.altInput.value);
            imageData.append('label', this.labelInput.value);
            imageData.append('author', this.authorInput.value);
            let req = await fetch (`/image/update/${this.image.getId()}/`, {
                method: 'POST',
                body:imageData
            });
            let res = await req.json();
            if(res.status && res.data) {
                this.cell.update(res.data);
                if(res.message) {
                    this.printMessage(res.message);
                }
            }
            this.form.classList.remove(formSelectors.classes.loading);
            this.formLoader.stop();
        });
    }

    addDeleteListener() {
        if(!this.getAllowDelete()) {
            return;
        }
        this.deleteButton.addEventListener('click', async () => {
            console.log('deleting media...');
            const req = await fetch(`/image/delete/${this.image.getId()}/`);
            const res = await req.json();
            if(res.status) {
                this.cell.toggleSelect();
                this.eventEmitter.emit(formEvents.mediaDeleted, {id:this.image.getId()});
            }
        });
    }

    printMessage(message) {
        this.messageContainer.innerText = message;
    }
}