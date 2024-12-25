import {personListSelectors} from "./personListSelectors.js";
import {blockAssets} from "../blockAssets.js";
import {blockSelectors} from "../blockSelectors.js";
import {ImagePreviewInForm} from "../../../imagePreviewInForm/ImagePreviewInForm.js";

export default class Person {

    container;

    nameAndDescriptionInputContainer;

    fullNameInput;

    descriptionInput;

    deleteButton;

    initiator = null;

    imagePreview = null;

    socials = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube']

    constructor(id, blockId, data, helpers) {
        this.id = id;
        this.blockId = blockId;
        this.data = data;
        this.generateInput = helpers.generateInput;
        this.generateTextarea = helpers.generateTextarea;
        this.generateContainerWithLabel = helpers.generateContainerWithLabel;
        this.inputName = helpers.inputName;
        this.generateView();
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(personListSelectors.classes.personContainer);
        this.container.setAttribute(blockSelectors.attributes.id, this.getBlockId());
        this.generateNameAndDescriptionInputContainer();
        this.generateFullNameInput();
        this.generateDescriptionInput();
        this.generateLinkToInput();
        this.container.appendChild(this.getNameAndDescriptionInputContainer());
        this.generateImage();
        this.container.appendChild(this.getImageContainer());
        this.container.appendChild(this.generateSocialInputs());

        this.generateDeleteButton();
        this.container.appendChild(this.getDeleteButton());
    }

    generateNameAndDescriptionInputContainer() {
        this.nameAndDescriptionInputContainer = document.createElement('div');
        this.nameAndDescriptionInputContainer.classList.add(personListSelectors.classes.nameAndDescriptionInputContainer);
    }

    getNameAndDescriptionInputContainer() {
        return this.nameAndDescriptionInputContainer;
    }

    generateFullNameInput() {
        this.fullNameInput = this.generateInput(this.getFullNameInputName());
        this.fullNameInput.placeholder = 'Full Name';
        if(this.data && this.data.fullName && this.data.fullName.trim() !== '') {
            this.fullNameInput.value = this.data.fullName;
        }
        this.getNameAndDescriptionInputContainer().appendChild(this.fullNameInput);
    }

    getFullNameInput() {
        return this.fullNameInput;
    }

    getFullNameInputName() {
       return this.getInputName() + '[fullName]';
    }

    generateDescriptionInput() {
        this.descriptionInput = this.generateTextarea(this.getDescriptionInputName());
        this.descriptionInput.placeholder = 'Description';
        if(this.data && this.data.description && this.data.description.trim() !== '') {
            this.descriptionInput.value = this.data.description;
        }
        this.getNameAndDescriptionInputContainer().appendChild(this.descriptionInput);
    }

    generateLinkToInput() {
        this.linkToInput = this.generateInput(this.getLinkToInputName());
        this.linkToInput.placeholder = 'Link To';
        if(this.data && this.data.linkTo) {
            this.linkToInput.value = this.data.linkTo;
        }
        this.getNameAndDescriptionInputContainer().appendChild(this.linkToInput);
    }

    getLinkToInputName() {
        return this.getInputName() + '[linkTo]';
    }

    getDescriptionInput() {
        return this.descriptionInput;
    }

    getDescriptionInputName() {
        return this.getInputName() + '[description]';
    }

    generateImage() {
        this.imageContainer = document.createElement('div');
        this.initiator = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            personListSelectors.classes.image,
            'Insert Image');
        this.initiator.getInitiatorElement().blockId = this.getBlockId();
        this.initiator.getInitiatorElement().personId = this.getId();
        this.imageContainer.appendChild(this.initiator.getInitiatorElement());

        if(this.data && this.data.image && this.data.image.imageId && this.data.image.filename
            && (this.data.image.imageId || this.data.image.imageId === 0)) {
            this.addPreviewAndImageInput(ImagePreviewInForm.generate(
                    this.getImageInputName(),
                    this.data.image.imageId,
                    `/images${this.data.image.filename}`)
            );
        }
    }

    getImageContainer() {
        return this.imageContainer;
    }

    getInitiator() {
        return this.initiator;
    }

    getImageInputName() {
        return this.getInputName() + '[image]';
    }

    addPreviewAndImageInput(container) {
        if(this.getImagePreview()) {
            this.getImagePreview().remove();
            this.imagePreview = null;
        }
        this.imagePreview = container;
        this.getImageContainer().insertBefore(this.imagePreview, this.initiator.getInitiatorElement());
    }

    getImagePreview() {
        return this.imagePreview;
    }

    generateDeleteButton() {
        this.deleteButton = document.createElement('div');
        this.deleteButton.innerHTML = blockAssets.deleteIcon;
        this.deleteButton.classList.add(personListSelectors.classes.deleteButton);
        this.deleteButton.title = 'Delete';
        this.deleteButton.addEventListener('click', () => {
           this.getContainer().remove();
        });
    }

    generateSocialInputs() {
        const fragment = document.createDocumentFragment();
        this.getSocials().forEach((socialPlatformName) => {
            const container = this.generateContainerWithLabel(socialPlatformName);
            const input = this.generateInput(`${this.getSocialInputName()}[${socialPlatformName}]`);
            if(this.data && this.data.social && this.data.social[socialPlatformName]) {
                const value = this.data.social[socialPlatformName];
                if(value.trim() !== '') {
                    input.value = value;
                }
            }
            container.appendChild(input);
            fragment.appendChild(container);
        });
        return fragment;
    }

    getSocialInputName() {
        return this.getInputName() + '[social]';
    }

    getDeleteButton() {
        return this.deleteButton;
    }


    getContainer() {
        return this.container;
    }

    getView() {
        return this.getContainer();
    }

    getInputName() {
        return `${this.inputName}[${this.getId()}]`;
    }

    getId() {
        return this.id;
    }

    getBlockId() {
        return this.blockId;
    }

    getData() {
        return this.data;
    }

    getSocials() {
        return this.socials;
    }
}