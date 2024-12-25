import Block from "../Block.js";
import {gallerySelectors} from "./gallerySelectors.js";
import {ImagePreviewInForm} from "../../../imagePreviewInForm/ImagePreviewInForm.js";


export default class GalleryBlock extends Block {
    container;

    galleryImagesContainer;

    inputName;

    constructor(id, blockName, handlerEventEmitter, data) {
        super(id, blockName, handlerEventEmitter, data);
        this.generateInputName();
        this.generateView();
    }

    generateInputName() {
        this.inputName = `blocks[${this.getBlockId()}][gallery][]`;
    }

    populateWithData() {
        this.data.images.forEach((image) => {
            this.addPreviewAndInput(
                ImagePreviewInForm.generate(
                    this.getInputName(),
                    image.imageId,
                    `/images${image.filename}`
                ));
        });
    }

    generateView() {
        this.container = document.createElement('div');
        this.container.classList.add(gallerySelectors.classes.galleryBlockContainer);

        this.galleryImagesContainer = document.createElement('div');
        this.galleryImagesContainer.classList.add(gallerySelectors.classes.galleryImagesContainer);

        this.container.appendChild(this.getGalleryImagesContainer());
        this.initiator = window.mediaLibrary.makeAndMountInitiator(
            true,
            true,
            gallerySelectors.classes.insertImagesButton,
            'Insert Images');
        this.initiator.getInitiatorElement().blockId = this.getBlockId();
        this.container.appendChild(this.initiator.getInitiatorElement());
    }

    addPreviewAndInput(container) {
        this.getGalleryImagesContainer().appendChild(container);
    }

    generateImageWithInput(inputValue, src) {
        const container = document.createElement('div');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = this.getInputName();
        input.value = inputValue;
        container.appendChild(input);
        const img = document.createElement('img');
        img.src = src;
        container.appendChild(img);
        this.getGalleryImagesContainer().appendChild(container);
    }

    getInputName() {
        return this.inputName;
    }

    clearOldImages() {
        this.getGalleryImagesContainer().innerHTML = '';
    }

    getGalleryImagesContainer() {
        return this.galleryImagesContainer;
    }

    getViewNode() {
        return this.container;
    }

    handleImageSelection(initiatorElement, media) {
        if (media) {
            Object.values(media).forEach((mediaData) => {
                this.addPreviewAndInput(
                    ImagePreviewInForm.generate(
                        this.getInputName(),
                        mediaData.id,
                        `/images${mediaData.filename}`
                    ));
            });
        }
    }
}