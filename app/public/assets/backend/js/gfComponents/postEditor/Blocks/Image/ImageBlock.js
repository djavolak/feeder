import Block from "../Block.js";
import {imageSelectors} from "./imageSelectors.js";
import {ImagePreviewInForm} from "../../../imagePreviewInForm/ImagePreviewInForm.js";

export default class ImageBlock extends Block {
    initiator;

    inputName;

    container;

    insertedImageContainer = null;

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][image]`;
    }

    populateWithData() {
        this.addPreviewAndInput(
            ImagePreviewInForm.generate(
                this.getInputName(),
                this.data.imageId,
                `/images${this.data.filename}`
        ));
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(imageSelectors.classes.imageBlockContainer);
        this.initiator = window.mediaLibrary.makeAndMountInitiator(
            false,
            true,
            imageSelectors.classes.insertImageButton,
            'Insert Image');
        this.initiator.getInitiatorElement().blockId = this.getBlockId();
        this.container.appendChild(this.initiator.getInitiatorElement());
    }

    addPreviewAndInput(container) {
        if(this.insertedImageContainer) {
            this.insertedImageContainer.remove();
            this.insertedImageContainer = null;
        }
        this.insertedImageContainer = container;
        this.getViewNode().insertBefore(this.insertedImageContainer, this.initiator.getInitiatorElement());
    }

    getInputName() {
        return this.inputName;
    }

    getViewNode() {
        return this.container;
    }

    handleImageSelection(initiatorElement, media) {
        if (media && media[0]) {
            const mediaData = media[0];
            this.addPreviewAndInput(
                ImagePreviewInForm.generate(
                    this.getInputName(),
                    mediaData.id,
                    `/images${mediaData.filename}`
                ));
        }
    }
}